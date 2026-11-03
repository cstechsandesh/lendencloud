<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Models\VoucherModel;
use App\Models\VoucherAllocationModel;
use App\Models\PartyModel;
use App\Models\AccountModel;
use App\Models\InvoiceModel;
use App\Models\JournalEntryModel;

class ReceiptController extends BaseController
{
    protected VoucherModel $voucherModel;
    protected PartyModel $partyModel;
    protected AccountModel $accountModel;
    protected InvoiceModel $invoiceModel;
    protected JournalEntryModel $journalModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->voucherModel = new VoucherModel();
        $this->partyModel   = new PartyModel();
        $this->accountModel = new AccountModel();
        $this->invoiceModel = new InvoiceModel();
        $this->journalModel = new JournalEntryModel();
    }

    protected function authorizeReceiptAccess(bool $isWrite = false): void
    {
        $this->checkPermission($isWrite ? 'vouchers.create' : 'vouchers.view');
        $perms = $this->session->get('permissions') ?? [];
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return;
        }
        $canAccess = in_array('sales.view', $perms, true) || in_array('accounts.view', $perms, true);
        if ($isWrite) {
            $canAccess = in_array('sales.create', $perms, true) || in_array('accounts.create', $perms, true);
        }
        if (!$canAccess) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Security\Exceptions\SecurityException('Permission Denied: vouchers.receipt');
            }
            $this->session->setFlashdata('error', 'Access Denied: You do not have permission for customer receipts.');
            redirect()->to('/dashboard')->send();
            exit;
        }
    }

    public function index()
    {
        $this->authorizeReceiptAccess(false);
        $receipts = $this->voucherModel->select('vouchers.*, parties.party_name, accounts.account_name')
            ->join('parties', 'parties.party_id = vouchers.voucher_party_id', 'left')
            ->join('accounts', 'accounts.account_id = vouchers.voucher_account_id')
            ->where('voucher_type', 'receipt')
            ->where('vouchers.voucher_company_id', $this->companyId)
            ->orderBy('voucher_id', 'DESC')
            ->findAll();

        return $this->render('vouchers/receipts/index', [
            'title'    => 'Customer Receipt Vouchers',
            'receipts' => $receipts,
        ]);
    }

    public function create()
    {
        $this->authorizeReceiptAccess(true);
        $customers = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['customer', 'both'])->where('party_status', 'active')->findAll();
        // Bank & cash accounts (type = asset, group_id = 2)
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'asset')->where('account_is_active', 1)->findAll();

        return $this->render('vouchers/receipts/create', [
            'title'          => 'Create Receipt Voucher',
            'voucher_number' => $this->voucherModel->generateVoucherNumber('receipt', $this->companyId),
            'customers'      => $customers,
            'accounts'       => $accounts,
        ]);
    }

    public function store()
    {
        $this->authorizeReceiptAccess(true);
        $partyId   = (int)$this->request->getPost('voucher_party_id');
        $accountId = (int)$this->request->getPost('voucher_account_id');
        $amount    = (float)$this->request->getPost('voucher_amount');
        $date      = $this->request->getPost('voucher_date') ?: date('Y-m-d');

        if (!$partyId || !$accountId || $amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please provide valid customer, bank/cash account, and amount.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $vNumber = $this->request->getPost('voucher_number') ?: $this->voucherModel->generateVoucherNumber('receipt');

        $voucherId = $this->voucherModel->insert([
            'voucher_company_id'   => $this->companyId,
            'voucher_number'       => $vNumber,
            'voucher_type'         => 'receipt',
            'voucher_party_id'     => $partyId,
            'voucher_account_id'   => $accountId,
            'voucher_date'         => $date,
            'voucher_amount'       => $amount,
            'voucher_payment_mode' => $this->request->getPost('voucher_payment_mode') ?: 'bank_transfer',
            'voucher_reference'    => $this->request->getPost('voucher_reference'),
            'voucher_narration'    => $this->request->getPost('voucher_narration'),
            'voucher_status'       => 'posted',
            'voucher_created_by'   => $this->session->get('user_id'),
        ]);

        // Invoice allocations
        $invIds  = $this->request->getPost('allocated_invoice_id') ?? [];
        $invAmts = $this->request->getPost('allocated_amount') ?? [];

        $vaTable = $db->table('voucher_allocations');
        $now = date('Y-m-d H:i:s');

        for ($i = 0; $i < count($invIds); $i++) {
            $invId  = (int)$invIds[$i];
            $allocA = (float)($invAmts[$i] ?? 0);
            if ($invId && $allocA > 0) {
                $vaTable->insert([
                    'va_voucher_id'     => $voucherId,
                    'va_reference_type' => 'invoice',
                    'va_reference_id'   => $invId,
                    'va_amount'         => $allocA,
                    'created_at'        => $now,
                ]);
                $this->invoiceModel->updatePaymentStatus($invId);
            }
        }

        // Post Double-Entry Journal:
        // DR: Bank / Cash Account (amount)
        // CR: Accounts Receivable (1100/1020) (amount)
        $arAcc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['1100', '1020'])->first();
        if ($arAcc) {
            $lines = [
                ['jl_account_id' => $accountId,           'jl_debit' => $amount, 'jl_credit' => 0.00],
                ['jl_account_id' => $arAcc['account_id'], 'jl_debit' => 0.00,   'jl_credit' => $amount],
            ];

            $journalEntry = [
                'journal_company_id'     => $this->companyId,
                'journal_voucher_number' => $this->journalModel->generateVoucherNumber('receipt', $this->companyId),
                'journal_type'           => 'receipt',
                'journal_reference_type' => 'voucher',
                'journal_reference_id'   => $voucherId,
                'journal_date'           => $date,
                'journal_narration'      => 'Receipt Voucher #' . $vNumber,
                'journal_is_posted'      => 1,
                'journal_created_by'     => $this->session->get('user_id'),
            ];

            $journalId = $this->journalModel->postTransaction($journalEntry, $lines);
            $this->voucherModel->update($voucherId, ['voucher_journal_id' => $journalId]);
        }

        $this->partyModel->recalculatePartyBalance($partyId);

        $db->transComplete();

        return redirect()->to('/vouchers/receipts/view/' . $voucherId)->with('success', 'Receipt voucher recorded and ledger updated.');
    }

    public function view(int $id)
    {
        $this->checkPermission('vouchers.view');
        $voucher = $this->voucherModel->getWithDetails($id);
        if (!$voucher) return redirect()->to('/vouchers/receipts')->with('error', 'Receipt not found.');

        return $this->render('vouchers/receipts/view', [
            'title'   => 'Receipt Voucher: ' . $voucher['voucher_number'],
            'voucher' => $voucher,
        ]);
    }
}