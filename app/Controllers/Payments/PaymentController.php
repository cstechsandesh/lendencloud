<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Models\VoucherModel;
use App\Models\VoucherAllocationModel;
use App\Models\PartyModel;
use App\Models\AccountModel;
use App\Models\BillModel;
use App\Models\JournalEntryModel;

class PaymentController extends BaseController
{
    protected VoucherModel $voucherModel;
    protected PartyModel $partyModel;
    protected AccountModel $accountModel;
    protected BillModel $billModel;
    protected JournalEntryModel $journalModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->voucherModel = new VoucherModel();
        $this->partyModel   = new PartyModel();
        $this->accountModel = new AccountModel();
        $this->billModel    = new BillModel();
        $this->journalModel = new JournalEntryModel();
    }

    protected function authorizePaymentAccess(bool $isWrite = false): void
    {
        $this->checkPermission($isWrite ? 'vouchers.create' : 'vouchers.view');
        $perms = $this->session->get('permissions') ?? [];
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return;
        }
        $canAccess = in_array('purchases.view', $perms, true) || in_array('accounts.view', $perms, true);
        if ($isWrite) {
            $canAccess = in_array('purchases.create', $perms, true) || in_array('accounts.create', $perms, true);
        }
        if (!$canAccess) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Security\Exceptions\SecurityException('Permission Denied: vouchers.payment');
            }
            $this->session->setFlashdata('error', 'Access Denied: You do not have permission for vendor payments.');
            redirect()->to('/dashboard')->send();
            exit;
        }
    }

    public function index()
    {
        $this->authorizePaymentAccess(false);
        $payments = $this->voucherModel->select('vouchers.*, parties.party_name, accounts.account_name')
            ->join('parties', 'parties.party_id = vouchers.voucher_party_id', 'left')
            ->join('accounts', 'accounts.account_id = vouchers.voucher_account_id')
            ->where('voucher_type', 'payment')
            ->where('vouchers.voucher_company_id', $this->companyId)
            ->orderBy('voucher_id', 'DESC')
            ->findAll();

        return $this->render('vouchers/payments/index', [
            'title'    => 'Vendor Payment Vouchers',
            'payments' => $payments,
        ]);
    }

    public function create()
    {
        $this->authorizePaymentAccess(true);
        $vendors  = $this->partyModel->where('party_company_id', $this->companyId)->whereIn('party_type', ['vendor', 'both'])->where('party_status', 'active')->findAll();
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'asset')->where('account_is_active', 1)->findAll();

        return $this->render('vouchers/payments/create', [
            'title'          => 'Create Payment Voucher',
            'voucher_number' => $this->voucherModel->generateVoucherNumber('payment', $this->companyId),
            'vendors'        => $vendors,
            'accounts'       => $accounts,
        ]);
    }

    public function store()
    {
        $this->authorizePaymentAccess(true);
        $partyId   = (int)$this->request->getPost('voucher_party_id');
        $accountId = (int)$this->request->getPost('voucher_account_id');
        $amount    = (float)$this->request->getPost('voucher_amount');
        $date      = $this->request->getPost('voucher_date') ?: date('Y-m-d');

        if (!$partyId || !$accountId || $amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Please provide valid vendor, payment account, and amount.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $vNumber = $this->request->getPost('voucher_number') ?: $this->voucherModel->generateVoucherNumber('payment');

        $voucherId = $this->voucherModel->insert([
            'voucher_company_id'   => $this->companyId,
            'voucher_number'       => $vNumber,
            'voucher_type'         => 'payment',
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

        // Bill allocations
        $billIds  = $this->request->getPost('allocated_bill_id') ?? [];
        $billAmts = $this->request->getPost('allocated_amount') ?? [];

        $vaTable = $db->table('voucher_allocations');
        $now = date('Y-m-d H:i:s');

        for ($i = 0; $i < count($billIds); $i++) {
            $billId = (int)$billIds[$i];
            $allocA = (float)($billAmts[$i] ?? 0);
            if ($billId && $allocA > 0) {
                $vaTable->insert([
                    'va_voucher_id'     => $voucherId,
                    'va_reference_type' => 'bill',
                    'va_reference_id'   => $billId,
                    'va_amount'         => $allocA,
                    'created_at'        => $now,
                ]);
                $this->billModel->updatePaymentStatus($billId);
            }
        }

        // Post Double-Entry Journal:
        // DR: Accounts Payable (2000/2010) (amount)
        // CR: Bank / Cash Account (amount)
        $apAcc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['2000', '2010'])->first();
        if ($apAcc) {
            $lines = [
                ['jl_account_id' => $apAcc['account_id'], 'jl_debit' => $amount, 'jl_credit' => 0.00],
                ['jl_account_id' => $accountId,           'jl_debit' => 0.00,   'jl_credit' => $amount],
            ];

            $journalEntry = [
                'journal_company_id'     => $this->companyId,
                'journal_voucher_number' => $this->journalModel->generateVoucherNumber('payment', $this->companyId),
                'journal_type'           => 'payment',
                'journal_reference_type' => 'voucher',
                'journal_reference_id'   => $voucherId,
                'journal_date'           => $date,
                'journal_narration'      => 'Payment Voucher #' . $vNumber,
                'journal_is_posted'      => 1,
                'journal_created_by'     => $this->session->get('user_id'),
            ];

            $journalId = $this->journalModel->postTransaction($journalEntry, $lines);
            $this->voucherModel->update($voucherId, ['voucher_journal_id' => $journalId]);
        }

        $this->partyModel->recalculatePartyBalance($partyId);

        $db->transComplete();

        return redirect()->to('/vouchers/payments/view/' . $voucherId)->with('success', 'Payment voucher recorded.');
    }

    public function view(int $id)
    {
        $this->checkPermission('vouchers.view');
        $voucher = $this->voucherModel->getWithDetails($id);
        if (!$voucher) return redirect()->to('/vouchers/payments')->with('error', 'Payment not found.');

        return $this->render('vouchers/payments/view', [
            'title'   => 'Payment Voucher: ' . $voucher['voucher_number'],
            'voucher' => $voucher,
        ]);
    }
}