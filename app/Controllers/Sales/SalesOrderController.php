<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SalesOrderModel;
use App\Models\SalesOrderItemModel;
use App\Models\PartyModel;
use App\Models\ProductModel;
use App\Models\TaxRateModel;

class SalesOrderController extends BaseController
{
    protected SalesOrderModel $soModel;
    protected SalesOrderItemModel $soiModel;
    protected PartyModel $partyModel;
    protected ProductModel $productModel;
    protected TaxRateModel $taxModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->soModel      = new SalesOrderModel();
        $this->soiModel     = new SalesOrderItemModel();
        $this->partyModel   = new PartyModel();
        $this->productModel = new ProductModel();
        $this->taxModel     = new TaxRateModel();
    }

    public function index()
    {
        $this->checkPermission('sales.view');
        $orders = $this->soModel->select('sales_orders.*, parties.party_name')
            ->join('parties', 'parties.party_id = sales_orders.so_party_id')
            ->where('sales_orders.so_company_id', $this->companyId)
            ->orderBy('so_id', 'DESC')
            ->findAll();

        return $this->render('sales/orders/index', [
            'title'  => 'Sales Orders Master',
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        $this->checkPermission('sales.create');
        $parties  = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['customer', 'both'])->where('party_status', 'active')->findAll();
        $products = $this->productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $taxRates = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('sales/orders/create', [
            'title'        => 'Create New Sales Order',
            'order_number' => $this->soModel->generateOrderNumber($this->companyId),
            'parties'      => $parties,
            'products'     => $products,
            'taxRates'     => $taxRates,
        ]);
    }

    public function store()
    {
        $this->checkPermission('sales.create');
        $partyId = (int)$this->request->getPost('so_party_id');
        if (!$partyId) return redirect()->back()->withInput()->with('error', 'Please select a customer.');

        $productIds = $this->request->getPost('product_id') ?? [];
        $quantities = $this->request->getPost('quantity') ?? [];
        $prices     = $this->request->getPost('unit_price') ?? [];
        $discounts  = $this->request->getPost('discount_pct') ?? [];
        $taxRateIds = $this->request->getPost('tax_rate_id') ?? [];

        if (empty($productIds)) {
            return redirect()->back()->withInput()->with('error', 'Add at least one item line to the order.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $discTotal = 0.0;

        $items = [];
        $taxLookup = array_column($this->taxModel->findAll(), 'tax_rate', 'tax_id');

        for ($i = 0; $i < count($productIds); $i++) {
            $pId = (int)$productIds[$i];
            if (!$pId) continue;

            $qty   = max(0.001, (float)($quantities[$i] ?? 1));
            $price = (float)($prices[$i] ?? 0);
            $dPct  = (float)($discounts[$i] ?? 0);
            $tId   = (int)($taxRateIds[$i] ?? 0);
            $tPct  = (float)($taxLookup[$tId] ?? 0);

            $lineBase = $qty * $price;
            $lineDisc = $lineBase * ($dPct / 100);
            $taxable  = $lineBase - $lineDisc;
            $lineTax  = $taxable * ($tPct / 100);
            $lineTotal = $taxable + $lineTax;

            $subtotal  += $taxable;
            $taxTotal  += $lineTax;
            $discTotal += $lineDisc;

            $items[] = [
                'soi_product_id'   => $pId,
                'soi_quantity'     => $qty,
                'soi_unit_price'   => $price,
                'soi_discount_pct' => $dPct,
                'soi_tax_rate_id'  => $tId ?: null,
                'soi_tax_amount'   => $lineTax,
                'soi_line_total'   => $lineTotal,
            ];
        }

        $orderTotal = $subtotal + $taxTotal;

        $soId = $this->soModel->insert([
            'so_company_id'        => $this->companyId,
            'so_number'            => $this->request->getPost('so_number') ?: $this->soModel->generateOrderNumber($this->companyId),
            'so_party_id'          => $partyId,
            'so_date'              => $this->request->getPost('so_date') ?: date('Y-m-d'),
            'so_expected_delivery' => $this->request->getPost('so_expected_delivery') ?: null,
            'so_status'            => 'confirmed',
            'so_subtotal'          => $subtotal,
            'so_tax_amount'        => $taxTotal,
            'so_discount_amount'   => $discTotal,
            'so_total'             => $orderTotal,
            'so_notes'             => $this->request->getPost('so_notes'),
            'so_created_by'        => $this->session->get('user_id'),
        ]);

        $soiTable = $db->table('sales_order_items');
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            $item['soi_order_id'] = $soId;
            $item['created_at']   = $now;
            $soiTable->insert($item);
        }

        $db->transComplete();

        return redirect()->to('/sales/orders/view/' . $soId)->with('success', 'Sales order created.');
    }

    public function view(int $id)
    {
        $this->checkPermission('sales.view');
        $order = $this->soModel->getWithItems($id);
        if (!$order) return redirect()->to('/sales/orders')->with('error', 'Sales order not found.');

        return $this->render('sales/orders/view', [
            'title' => 'Sales Order: ' . $order['so_number'],
            'order' => $order,
        ]);
    }

    public function edit(int $id)
    {
        $this->checkPermission('sales.edit');
        $order = $this->soModel->getWithItems($id);
        if (!$order || (int)$order['so_company_id'] !== $this->companyId) {
            return redirect()->to('/sales/orders')->with('error', 'Sales order not found.');
        }
        if ($order['so_status'] === 'invoiced' || $order['so_status'] === 'cancelled') {
            return redirect()->to('/sales/orders/view/' . $id)->with('error', 'Cannot modify an order with status: ' . ucfirst($order['so_status']));
        }
        return $this->view($id);
    }

    public function update(int $id)
    {
        $this->checkPermission('sales.edit');
        $order = $this->soModel->where('so_id', $id)->where('so_company_id', $this->companyId)->first();
        if (!$order) {
            return redirect()->to('/sales/orders')->with('error', 'Sales order not found.');
        }
        $notes    = trim($this->request->getPost('so_notes') ?? '');
        $delivery = $this->request->getPost('so_expected_delivery') ?: null;
        $this->soModel->update($id, [
            'so_notes'             => $notes,
            'so_expected_delivery' => $delivery,
        ]);
        return redirect()->to('/sales/orders/view/' . $id)->with('success', 'Sales order updated.');
    }

    public function cancel(int $id)
    {
        $this->checkPermission('sales.cancel');
        $order = $this->soModel->where('so_id', $id)->where('so_company_id', $this->companyId)->first();
        if (!$order) {
            return redirect()->to('/sales/orders')->with('error', 'Sales order not found.');
        }
        if ($order['so_status'] === 'invoiced') {
            return redirect()->to('/sales/orders/view/' . $id)->with('error', 'Cannot cancel an order that has already been invoiced.');
        }
        $this->soModel->update($id, ['so_status' => 'cancelled']);
        return redirect()->to('/sales/orders/view/' . $id)->with('success', 'Sales Order #' . $order['so_number'] . ' cancelled.');
    }

    public function createInvoice(int $id)
    {
        $this->checkPermission('sales.create');
        $this->checkPlanQuota('invoices');
        $order = $this->soModel->getWithItems($id);
        if (!$order || (int)$order['so_company_id'] !== $this->companyId) {
            return redirect()->to('/sales/orders')->with('error', 'Sales order not found.');
        }
        if ($order['so_status'] === 'invoiced') {
            return redirect()->to('/sales/orders/view/' . $id)->with('error', 'This sales order has already been converted to an invoice.');
        }
        if ($order['so_status'] === 'cancelled') {
            return redirect()->to('/sales/orders/view/' . $id)->with('error', 'Cannot invoice a cancelled sales order.');
        }

        return redirect()->to('/sales/invoices/create?order_id=' . $id)->with('info', 'Converting Sales Order ' . $order['so_number'] . ' to Tax Invoice.');
    }
}