<?php

namespace App\Controllers\Purchases;

use App\Controllers\BaseController;
use App\Models\BillModel;
use App\Models\BillItemModel;
use App\Models\PartyModel;
use App\Models\ProductModel;
use App\Models\TaxRateModel;
use App\Models\JournalEntryModel;
use App\Models\AccountModel;
use App\Models\StockMovementModel;

class BillController extends BaseController
{
    protected BillModel $billModel;
    protected BillItemModel $biModel;
    protected PartyModel $partyModel;
    protected ProductModel $productModel;
    protected TaxRateModel $taxModel;
    protected JournalEntryModel $journalModel;
    protected AccountModel $accountModel;
    protected StockMovementModel $stockModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->billModel    = new BillModel();
        $this->biModel      = new BillItemModel();
        $this->partyModel   = new PartyModel();
        $this->productModel = new ProductModel();
        $this->taxModel     = new TaxRateModel();
        $this->journalModel = new JournalEntryModel();
        $this->accountModel = new AccountModel();
        $this->stockModel   = new StockMovementModel();
    }

    public function index()
    {
        $this->checkPermission('purchases.view');
        $bills = $this->billModel->select('bills.*, parties.party_name')
            ->join('parties', 'parties.party_id = bills.bill_party_id')
            ->where('bills.bill_company_id', $this->companyId)
            ->orderBy('bill_id', 'DESC')
            ->findAll();

        return $this->render('purchases/bills/index', [
            'title' => 'Vendor Bills & Inward Purchases',
            'bills' => $bills,
        ]);
    }

    public function create()
    {
        $this->checkPermission('purchases.create');
        $this->checkPlanQuota('bills');
        $parties  = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['vendor', 'both'])->where('party_status', 'active')->findAll();
        $products = $this->productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $taxRates = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('purchases/bills/create', [
            'title'       => 'Record Vendor Bill',
            'bill_number' => $this->billModel->generateBillNumber($this->companyId),
            'parties'     => $parties,
            'products'    => $products,
            'taxRates'    => $taxRates,
        ]);
    }

    public function store()
    {
        $this->checkPermission('purchases.create');
        $this->checkPlanQuota('bills');
        $partyId = (int)$this->request->getPost('bill_party_id');
        if (!$partyId) return redirect()->back()->withInput()->with('error', 'Please select a vendor.');

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
                'bi_product_id'   => $pId,
                'bi_quantity'     => $qty,
                'bi_unit_price'   => $price,
                'bi_discount_pct' => $dPct,
                'bi_tax_rate_id'  => $tId ?: null,
                'bi_tax_amount'   => $lineTax,
                'bi_line_total'   => $lineTotal,
            ];
        }

        $billTotal = $subtotal + $taxTotal;
        $billNumber = $this->request->getPost('bill_number') ?: $this->billModel->generateBillNumber();
        $billDate   = $this->request->getPost('bill_date') ?: date('Y-m-d');

        $billId = $this->billModel->insert([
            'bill_company_id'      => $this->companyId,
            'bill_number'          => $billNumber,
            'bill_party_id'        => $partyId,
            'bill_date'            => $billDate,
            'bill_due_date'        => $this->request->getPost('bill_due_date') ?: null,
            'bill_status'          => 'partial',
            'bill_subtotal'        => $subtotal,
            'bill_tax_amount'      => $taxTotal,
            'bill_discount_amount' => $discTotal,
            'bill_total'           => $billTotal,
            'bill_paid_amount'     => 0.00,
            'bill_balance'         => $billTotal,
            'bill_notes'           => $this->request->getPost('bill_notes'),
            'bill_created_by'      => $this->session->get('user_id'),
        ]);

        $warehouse = (new \App\Models\WarehouseModel())->where('warehouse_company_id', $this->companyId)->first();
        $warehouseId = $warehouse ? (int)$warehouse['warehouse_id'] : 1;

        // Insert bill items and increase inventory stock
        $biTable = $db->table('bill_items');
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            $item['bi_bill_id'] = $billId;
            $item['created_at'] = $now;
            $biTable->insert($item);

            // Record Stock In movement
            $this->stockModel->recordMovement([
                'movement_company_id'  => $this->companyId,
                'stock_product_id'     => $item['bi_product_id'],
                'stock_warehouse_id'   => $warehouseId,
                'stock_type'           => 'in',
                'stock_quantity'       => $item['bi_quantity'],
                'stock_unit_cost'      => $item['bi_unit_price'],
                'stock_reference_type' => 'bill',
                'stock_reference_id'   => $billId,
                'stock_notes'          => 'Purchase Bill #' . $billNumber,
                'stock_created_by'     => $this->session->get('user_id'),
            ]);
        }

        // Post Double-Entry Journal Voucher:
        // DR: Purchase Expense (5100/5000) or Stock (1200/1030) (subtotal)
        // DR: GST Input Tax Credit (1300/1040) (tax total)
        // CR: Accounts Payable (2000/2010) (billTotal)
        $apAcc       = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['2000', '2010'])->first();
        $purchaseAcc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['5100', '5000'])->first();
        $gstInputAcc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['1300', '1040'])->first();

        if ($apAcc && $purchaseAcc) {
            $journalLines = [];
            // Debit Purchases
            $journalLines[] = [
                'jl_account_id' => $purchaseAcc['account_id'],
                'jl_debit'      => $subtotal,
                'jl_credit'     => 0.00,
            ];
            // Debit GST Input if any
            if ($taxTotal > 0 && $gstInputAcc) {
                $journalLines[] = [
                    'jl_account_id' => $gstInputAcc['account_id'],
                    'jl_debit'      => $taxTotal,
                    'jl_credit'     => 0.00,
                ];
            }
            // Credit Accounts Payable
            $journalLines[] = [
                'jl_account_id' => $apAcc['account_id'],
                'jl_debit'      => 0.00,
                'jl_credit'     => $billTotal,
            ];

            $journalEntry = [
                'journal_company_id'     => $this->companyId,
                'journal_voucher_number' => $this->journalModel->generateVoucherNumber('purchase', $this->companyId),
                'journal_type'           => 'purchase',
                'journal_reference_type' => 'bill',
                'journal_reference_id'   => $billId,
                'journal_date'           => $billDate,
                'journal_narration'      => 'Vendor Bill #' . $billNumber,
                'journal_is_posted'      => 1,
                'journal_created_by'     => $this->session->get('user_id'),
            ];

            $journalId = $this->journalModel->postTransaction($journalEntry, $journalLines);
            $this->billModel->update($billId, ['bill_journal_id' => $journalId]);
        }

        // Recalculate vendor balance
        $this->partyModel->recalculatePartyBalance($partyId);

        $db->transComplete();

        return redirect()->to('/purchases/bills/view/' . $billId)->with('success', 'Purchase bill recorded and inventory stock credited.');
    }

    public function view(int $id)
    {
        $this->checkPermission('purchases.view');
        $bill = $this->billModel->getWithDetails($id);
        if (!$bill) return redirect()->to('/purchases/bills')->with('error', 'Bill not found.');

        return $this->render('purchases/bills/view', [
            'title' => 'Bill: ' . $bill['bill_number'],
            'bill'  => $bill,
        ]);
    }

    public function edit(int $id)
    {
        $this->checkPermission('purchases.edit');
        $bill = $this->billModel->getWithDetails($id);
        if (!$bill || (int)$bill['bill_company_id'] !== $this->companyId) {
            return redirect()->to('/purchases/bills')->with('error', 'Bill not found.');
        }
        if ($bill['bill_status'] !== 'draft') {
            return redirect()->to('/purchases/bills/view/' . $id)->with('error', 'Posted vendor bills cannot be modified in place due to audit compliance. You may cancel the bill instead.');
        }
        return $this->view($id);
    }

    public function update(int $id)
    {
        $this->checkPermission('purchases.edit');
        $bill = $this->billModel->where('bill_id', $id)->where('bill_company_id', $this->companyId)->first();
        if (!$bill) {
            return redirect()->to('/purchases/bills')->with('error', 'Bill not found.');
        }
        $notes = trim($this->request->getPost('bill_notes') ?? '');
        $this->billModel->update($id, ['bill_notes' => $notes]);
        return redirect()->to('/purchases/bills/view/' . $id)->with('success', 'Bill updated.');
    }

    public function cancel(int $id)
    {
        $this->checkPermission('purchases.cancel');
        $bill = $this->billModel->where('bill_id', $id)->where('bill_company_id', $this->companyId)->first();
        if (!$bill) {
            return redirect()->to('/purchases/bills')->with('error', 'Bill not found.');
        }
        if ($bill['bill_status'] === 'cancelled') {
            return redirect()->to('/purchases/bills/view/' . $id)->with('error', 'Bill is already cancelled.');
        }
        $this->billModel->update($id, ['bill_status' => 'cancelled']);
        return redirect()->to('/purchases/bills/view/' . $id)->with('success', 'Bill #' . $bill['bill_number'] . ' has been cancelled.');
    }
}