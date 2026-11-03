<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceItemModel;
use App\Models\PartyModel;
use App\Models\ProductModel;
use App\Models\TaxRateModel;
use App\Models\JournalEntryModel;
use App\Models\AccountModel;
use App\Models\StockMovementModel;

class InvoiceController extends BaseController
{
    protected InvoiceModel $invoiceModel;
    protected InvoiceItemModel $iiModel;
    protected PartyModel $partyModel;
    protected ProductModel $productModel;
    protected TaxRateModel $taxModel;
    protected JournalEntryModel $journalModel;
    protected AccountModel $accountModel;
    protected StockMovementModel $stockModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->invoiceModel = new InvoiceModel();
        $this->iiModel      = new InvoiceItemModel();
        $this->partyModel   = new PartyModel();
        $this->productModel = new ProductModel();
        $this->taxModel     = new TaxRateModel();
        $this->journalModel = new JournalEntryModel();
        $this->accountModel = new AccountModel();
        $this->stockModel   = new StockMovementModel();
    }

    public function index()
    {
        $this->checkPermission('sales.view');
        $invoices = $this->invoiceModel->select('invoices.*, parties.party_name')
            ->join('parties', 'parties.party_id = invoices.invoice_party_id')
            ->where('invoices.invoice_company_id', $this->companyId)
            ->orderBy('invoice_id', 'DESC')
            ->findAll();

        return $this->render('sales/invoices/index', [
            'title'    => 'Sales Tax Invoices',
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        $this->checkPermission('sales.create');
        $this->checkPlanQuota('invoices');
        $parties  = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['customer', 'both'])->where('party_status', 'active')->findAll();
        $products = $this->productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $taxRates = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('sales/invoices/create', [
            'title'          => 'Generate Tax Invoice',
            'invoice_number' => $this->invoiceModel->generateInvoiceNumber($this->companyId),
            'parties'        => $parties,
            'products'       => $products,
            'taxRates'       => $taxRates,
        ]);
    }

    public function store()
    {
        $this->checkPermission('sales.create');
        $this->checkPlanQuota('invoices');
        $partyId = (int)$this->request->getPost('invoice_party_id');
        if (!$partyId) return redirect()->back()->withInput()->with('error', 'Please select a customer.');

        $productIds = $this->request->getPost('product_id') ?? [];
        $quantities = $this->request->getPost('quantity') ?? [];
        $prices     = $this->request->getPost('unit_price') ?? [];
        $discounts  = $this->request->getPost('discount_pct') ?? [];
        $taxRateIds = $this->request->getPost('tax_rate_id') ?? [];

        if (empty($productIds)) {
            return redirect()->back()->withInput()->with('error', 'Add at least one product line.');
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
                'ii_product_id'   => $pId,
                'ii_quantity'     => $qty,
                'ii_unit_price'   => $price,
                'ii_discount_pct' => $dPct,
                'ii_tax_rate_id'  => $tId ?: null,
                'ii_tax_amount'   => $lineTax,
                'ii_line_total'   => $lineTotal,
            ];
        }

        $invTotal = $subtotal + $taxTotal;
        $invNumber = $this->request->getPost('invoice_number') ?: $this->invoiceModel->generateInvoiceNumber();
        $invDate   = $this->request->getPost('invoice_date') ?: date('Y-m-d');

        $invoiceId = $this->invoiceModel->insert([
            'invoice_company_id'      => $this->companyId,
            'invoice_number'          => $invNumber,
            'invoice_party_id'        => $partyId,
            'invoice_date'            => $invDate,
            'invoice_due_date'        => $this->request->getPost('invoice_due_date') ?: null,
            'invoice_status'          => 'sent',
            'invoice_subtotal'        => $subtotal,
            'invoice_tax_amount'      => $taxTotal,
            'invoice_discount_amount' => $discTotal,
            'invoice_total'           => $invTotal,
            'invoice_paid_amount'     => 0.00,
            'invoice_balance'         => $invTotal,
            'invoice_notes'           => $this->request->getPost('invoice_notes'),
            'invoice_created_by'      => $this->session->get('user_id'),
        ]);

        $warehouse = (new \App\Models\WarehouseModel())->where('warehouse_company_id', $this->companyId)->first();
        $warehouseId = $warehouse ? (int)$warehouse['warehouse_id'] : 1;

        // Insert items and reduce inventory stock
        $iiTable = $db->table('invoice_items');
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            $item['ii_invoice_id'] = $invoiceId;
            $item['created_at']    = $now;
            $iiTable->insert($item);

            // Record Stock Out movement
            $this->stockModel->recordMovement([
                'movement_company_id'  => $this->companyId,
                'stock_product_id'     => $item['ii_product_id'],
                'stock_warehouse_id'   => $warehouseId,
                'stock_type'           => 'out',
                'stock_quantity'       => $item['ii_quantity'],
                'stock_unit_cost'      => $item['ii_unit_price'],
                'stock_reference_type' => 'invoice',
                'stock_reference_id'   => $invoiceId,
                'stock_notes'          => 'Invoice #' . $invNumber,
                'stock_created_by'     => $this->session->get('user_id'),
            ]);
        }

        // Post Double-Entry Journal Voucher:
        // DR: Accounts Receivable (total)
        // CR: Sales Revenue (subtotal)
        // CR: GST Output Tax (tax total)
        $arAcc    = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['1100', '1020'])->first();
        $salesAcc = $this->accountModel->where('account_company_id', $this->companyId)->where('account_code', '4000')->first();
        $gstAcc   = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['2100', '2020'])->first();

        if ($arAcc && $salesAcc) {
            $journalLines = [];
            // Debit AR
            $journalLines[] = [
                'jl_account_id' => $arAcc['account_id'],
                'jl_debit'      => $invTotal,
                'jl_credit'     => 0.00,
            ];
            // Credit Sales
            $journalLines[] = [
                'jl_account_id' => $salesAcc['account_id'],
                'jl_debit'      => 0.00,
                'jl_credit'     => $subtotal,
            ];
            // Credit GST if any
            if ($taxTotal > 0 && $gstAcc) {
                $journalLines[] = [
                    'jl_account_id' => $gstAcc['account_id'],
                    'jl_debit'      => 0.00,
                    'jl_credit'     => $taxTotal,
                ];
            }

            $journalEntry = [
                'journal_company_id'     => $this->companyId,
                'journal_voucher_number' => $this->journalModel->generateVoucherNumber('sales', $this->companyId),
                'journal_type'           => 'sales',
                'journal_reference_type' => 'invoice',
                'journal_reference_id'   => $invoiceId,
                'journal_date'           => $invDate,
                'journal_narration'      => 'Sales Invoice #' . $invNumber,
                'journal_is_posted'      => 1,
                'journal_created_by'     => $this->session->get('user_id'),
            ];

            $journalId = $this->journalModel->postTransaction($journalEntry, $journalLines);
            $this->invoiceModel->update($invoiceId, ['invoice_journal_id' => $journalId]);
        }

        // Recalculate party outstanding balance
        $this->partyModel->recalculatePartyBalance($partyId);

        $db->transComplete();

        return redirect()->to('/sales/invoices/view/' . $invoiceId)->with('success', 'Tax invoice generated and ledger posted.');
    }

    public function view(int $id)
    {
        $this->checkPermission('sales.view');
        $invoice = $this->invoiceModel->getWithDetails($id);
        if (!$invoice) return redirect()->to('/sales/invoices')->with('error', 'Invoice not found.');

        return $this->render('sales/invoices/view', [
            'title'   => 'Invoice: ' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ]);
    }

    public function printView(int $id)
    {
        $this->checkPermission('sales.print');
        $invoice = $this->invoiceModel->getWithDetails($id);
        if (!$invoice) return redirect()->to('/sales/invoices')->with('error', 'Invoice not found.');

        return view('sales/invoices/print', [
            'title'           => 'Invoice ' . $invoice['invoice_number'],
            'invoice'         => $invoice,
            'company_name'    => \App\Models\SettingModel::getSetting('company_name', 'LenDen Enterprise'),
            'company_address' => \App\Models\SettingModel::getSetting('company_address'),
            'company_phone'   => \App\Models\SettingModel::getSetting('company_phone'),
            'company_email'   => \App\Models\SettingModel::getSetting('company_email'),
            'company_gstin'   => \App\Models\SettingModel::getSetting('company_gstin'),
            'currency_symbol' => \App\Models\SettingModel::getSetting('currency_symbol', '₹'),
        ]);
    }

    public function edit(int $id)
    {
        $this->checkPermission('sales.edit');
        $invoice = $this->invoiceModel->getWithDetails($id);
        if (!$invoice || (int)$invoice['invoice_company_id'] !== $this->companyId) {
            return redirect()->to('/sales/invoices')->with('error', 'Invoice not found.');
        }
        if ($invoice['invoice_status'] !== 'draft') {
            return redirect()->to('/sales/invoices/view/' . $id)->with('error', 'Posted tax invoices cannot be modified in place due to GST audit compliance. You may cancel the invoice instead.');
        }
        return $this->view($id);
    }

    public function update(int $id)
    {
        $this->checkPermission('sales.edit');
        $invoice = $this->invoiceModel->where('invoice_id', $id)->where('invoice_company_id', $this->companyId)->first();
        if (!$invoice) {
            return redirect()->to('/sales/invoices')->with('error', 'Invoice not found.');
        }
        $notes = trim($this->request->getPost('invoice_notes') ?? '');
        $this->invoiceModel->update($id, ['invoice_notes' => $notes]);
        return redirect()->to('/sales/invoices/view/' . $id)->with('success', 'Invoice updated.');
    }

    public function cancel(int $id)
    {
        $this->checkPermission('sales.cancel');
        $invoice = $this->invoiceModel->where('invoice_id', $id)->where('invoice_company_id', $this->companyId)->first();
        if (!$invoice) {
            return redirect()->to('/sales/invoices')->with('error', 'Invoice not found.');
        }
        if ($invoice['invoice_status'] === 'cancelled') {
            return redirect()->to('/sales/invoices/view/' . $id)->with('error', 'Invoice is already cancelled.');
        }
        $this->invoiceModel->update($id, ['invoice_status' => 'cancelled']);
        return redirect()->to('/sales/invoices/view/' . $id)->with('success', 'Invoice #' . $invoice['invoice_number'] . ' has been cancelled.');
    }
}