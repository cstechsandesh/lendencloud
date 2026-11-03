<?php

namespace App\Controllers\Purchases;

use App\Controllers\BaseController;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderItemModel;
use App\Models\PartyModel;
use App\Models\ProductModel;
use App\Models\TaxRateModel;

class PurchaseOrderController extends BaseController
{
    protected PurchaseOrderModel $poModel;
    protected PurchaseOrderItemModel $poiModel;
    protected PartyModel $partyModel;
    protected ProductModel $productModel;
    protected TaxRateModel $taxModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->poModel      = new PurchaseOrderModel();
        $this->poiModel     = new PurchaseOrderItemModel();
        $this->partyModel   = new PartyModel();
        $this->productModel = new ProductModel();
        $this->taxModel     = new TaxRateModel();
    }

    public function index()
    {
        $this->checkPermission('purchases.view');
        $orders = $this->poModel->select('purchase_orders.*, parties.party_name')
            ->join('parties', 'parties.party_id = purchase_orders.po_party_id')
            ->where('purchase_orders.po_company_id', $this->companyId)
            ->orderBy('po_id', 'DESC')
            ->findAll();

        return $this->render('purchases/orders/index', [
            'title'  => 'Purchase Orders Master',
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        $this->checkPermission('purchases.create');
        $parties  = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['vendor', 'both'])->where('party_status', 'active')->findAll();
        $products = $this->productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $taxRates = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('purchases/orders/create', [
            'title'        => 'Create Purchase Order (PO)',
            'order_number' => $this->poModel->generateOrderNumber($this->companyId),
            'parties'      => $parties,
            'products'     => $products,
            'taxRates'     => $taxRates,
        ]);
    }

    public function store()
    {
        $this->checkPermission('purchases.create');
        $partyId = (int)$this->request->getPost('po_party_id');
        if (!$partyId) return redirect()->back()->withInput()->with('error', 'Please select a vendor.');

        $productIds = $this->request->getPost('product_id') ?? [];
        $quantities = $this->request->getPost('quantity') ?? [];
        $prices     = $this->request->getPost('unit_price') ?? [];
        $discounts  = $this->request->getPost('discount_pct') ?? [];
        $taxRateIds = $this->request->getPost('tax_rate_id') ?? [];

        if (empty($productIds)) {
            return redirect()->back()->withInput()->with('error', 'Add at least one item line.');
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
                'poi_product_id'   => $pId,
                'poi_quantity'     => $qty,
                'poi_unit_price'   => $price,
                'poi_discount_pct' => $dPct,
                'poi_tax_rate_id'  => $tId ?: null,
                'poi_tax_amount'   => $lineTax,
                'poi_line_total'   => $lineTotal,
            ];
        }

        $orderTotal = $subtotal + $taxTotal;

        $poId = $this->poModel->insert([
            'po_company_id'      => $this->companyId,
            'po_number'          => $this->request->getPost('po_number') ?: $this->poModel->generateOrderNumber($this->companyId),
            'po_party_id'        => $partyId,
            'po_date'            => $this->request->getPost('po_date') ?: date('Y-m-d'),
            'po_expected_date'   => $this->request->getPost('po_expected_date') ?: null,
            'po_status'          => 'confirmed',
            'po_subtotal'        => $subtotal,
            'po_tax_amount'      => $taxTotal,
            'po_discount_amount' => $discTotal,
            'po_total'           => $orderTotal,
            'po_notes'           => $this->request->getPost('po_notes'),
            'po_created_by'      => $this->session->get('user_id'),
        ]);

        $poiTable = $db->table('purchase_order_items');
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            $item['poi_order_id'] = $poId;
            $item['created_at']   = $now;
            $poiTable->insert($item);
        }

        $db->transComplete();

        return redirect()->to('/purchases/orders/view/' . $poId)->with('success', 'Purchase order created.');
    }

    public function view(int $id)
    {
        $this->checkPermission('purchases.view');
        $order = $this->poModel->getWithItems($id);
        if (!$order) return redirect()->to('/purchases/orders')->with('error', 'Purchase order not found.');

        return $this->render('purchases/orders/view', [
            'title' => 'Purchase Order: ' . $order['po_number'],
            'order' => $order,
        ]);
    }

    public function edit(int $id)
    {
        $this->checkPermission('purchases.edit');
        $order = $this->poModel->getWithItems($id);
        if (!$order || (int)$order['po_company_id'] !== $this->companyId) {
            return redirect()->to('/purchases/orders')->with('error', 'Purchase order not found.');
        }
        if ($order['po_status'] === 'billed' || $order['po_status'] === 'cancelled') {
            return redirect()->to('/purchases/orders/view/' . $id)->with('error', 'Cannot modify an order with status: ' . ucfirst($order['po_status']));
        }
        return $this->view($id);
    }

    public function update(int $id)
    {
        $this->checkPermission('purchases.edit');
        $order = $this->poModel->where('po_id', $id)->where('po_company_id', $this->companyId)->first();
        if (!$order) {
            return redirect()->to('/purchases/orders')->with('error', 'Purchase order not found.');
        }
        $notes    = trim($this->request->getPost('po_notes') ?? '');
        $delivery = $this->request->getPost('po_expected_date') ?: null;
        $this->poModel->update($id, [
            'po_notes'         => $notes,
            'po_expected_date' => $delivery,
        ]);
        return redirect()->to('/purchases/orders/view/' . $id)->with('success', 'Purchase order updated.');
    }

    public function cancel(int $id)
    {
        $this->checkPermission('purchases.cancel');
        $order = $this->poModel->where('po_id', $id)->where('po_company_id', $this->companyId)->first();
        if (!$order) {
            return redirect()->to('/purchases/orders')->with('error', 'Purchase order not found.');
        }
        if ($order['po_status'] === 'billed') {
            return redirect()->to('/purchases/orders/view/' . $id)->with('error', 'Cannot cancel an order that has already been billed.');
        }
        $this->poModel->update($id, ['po_status' => 'cancelled']);
        return redirect()->to('/purchases/orders/view/' . $id)->with('success', 'Purchase Order #' . $order['po_number'] . ' cancelled.');
    }

    public function createBill(int $id)
    {
        $this->checkPermission('purchases.create');
        $this->checkPlanQuota('bills');
        $order = $this->poModel->getWithItems($id);
        if (!$order || (int)$order['po_company_id'] !== $this->companyId) {
            return redirect()->to('/purchases/orders')->with('error', 'Purchase order not found.');
        }
        if ($order['po_status'] === 'billed') {
            return redirect()->to('/purchases/orders/view/' . $id)->with('error', 'This purchase order has already been converted to a vendor bill.');
        }
        if ($order['po_status'] === 'cancelled') {
            return redirect()->to('/purchases/orders/view/' . $id)->with('error', 'Cannot bill a cancelled purchase order.');
        }

        return redirect()->to('/purchases/bills/create?order_id=' . $id)->with('info', 'Converting Purchase Order ' . $order['po_number'] . ' to Vendor Bill.');
    }
}