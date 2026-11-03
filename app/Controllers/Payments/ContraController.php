<?php

namespace App\Controllers\Payments;

use App\Controllers\BaseController;
use App\Models\VoucherModel;
use App\Models\AccountModel;
use App\Models\JournalEntryModel;

class ContraController extends BaseController
{
    protected VoucherModel $voucherModel;
    protected AccountModel $accountModel;
    protected JournalEntryModel $journalModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->voucherModel = new VoucherModel();
        $this->accountModel = new AccountModel();
        $this->journalModel = new JournalEntryModel();
    }

    protected function authorizeContraAccess(bool $isWrite = false): void
    {
        $this->checkPermission($isWrite ? 'vouchers.contra' : 'vouchers.view');
        $perms = $this->session->get('permissions') ?? [];
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return;
        }
        $canAccess = in_array('accounts.view', $perms, true) || in_array('vouchers.contra', $perms, true);
        if (!$canAccess) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Security\Exceptions\SecurityException('Permission Denied: vouchers.contra');
            }
            $this->session->setFlashdata('error', 'Access Denied: You do not have permission for bank/cash contra transfers.');
            redirect()->to('/dashboard')->send();
            exit;
        }
    }

    public function index()
    {
        $this->authorizeContraAccess(false);
        $contras = $this->voucherModel->select('vouchers.*, from_acc.account_name as from_account, to_acc.account_name as to_account')
            ->join('accounts as from_acc', 'from_acc.account_id = vouchers.voucher_account_id')
            ->join('accounts as to_acc', 'to_acc.account_id = vouchers.voucher_transfer_account_id')
            ->where('voucher_type', 'contra')
            ->where('vouchers.voucher_company_id', $this->companyId)
            ->orderBy('voucher_id', 'DESC')
            ->findAll();

        return $this->render('vouchers/contra/index', [
            'title'   => 'Bank & Cash Contra Transfers',
            'contras' => $contras,
        ]);
    }

    public function create()
    {
        $this->authorizeContraAccess(true);
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'asset')->where('account_is_active', 1)->findAll();

        return $this->render('vouchers/contra/create', [
            'title'          => 'Create Contra Voucher',
            'voucher_number' => $this->voucherModel->generateVoucherNumber('contra', $this->companyId),
            'accounts'       => $accounts,
        ]);
    }

    public function store()
    {
        $this->authorizeContraAccess(true);
        $fromId = (int)$this->request->getPost('from_account_id');
        $toId   = (int)$this->request->getPost('to_account_id');
        $amount = (float)$this->request->getPost('voucher_amount');
        $date   = $this->request->getPost('voucher_date') ?: date('Y-m-d');

        if (!$fromId || !$toId || $fromId === $toId || $amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Select two different bank/cash accounts and a positive transfer amount.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $vNumber = $this->request->getPost('voucher_number') ?: $this->voucherModel->generateVoucherNumber('contra', $this->companyId);

        $voucherId = $this->voucherModel->insert([
            'voucher_company_id'          => $this->companyId,
            'voucher_number'              => $vNumber,
            'voucher_type'                => 'contra',
            'voucher_party_id'            => null,
            'voucher_account_id'          => $fromId,
            'voucher_transfer_account_id' => $toId,
            'voucher_date'                => $date,
            'voucher_amount'              => $amount,
            'voucher_payment_mode'        => $this->request->getPost('voucher_payment_mode') ?: 'bank_transfer',
            'voucher_reference'           => $this->request->getPost('voucher_reference'),
            'voucher_narration'           => $this->request->getPost('voucher_narration') ?: 'Bank/Cash internal fund transfer',
            'voucher_status'              => 'posted',
            'voucher_created_by'          => $this->session->get('user_id'),
        ]);

        // Post Double-Entry:
        // DR: Destination Account ($toId)
        // CR: Source Account ($fromId)
        $lines = [
            ['jl_account_id' => $toId,   'jl_debit' => $amount, 'jl_credit' => 0.00],
            ['jl_account_id' => $fromId, 'jl_debit' => 0.00,   'jl_credit' => $amount],
        ];

        $journalEntry = [
            'journal_company_id'     => $this->companyId,
            'journal_voucher_number' => $this->journalModel->generateVoucherNumber('contra', $this->companyId),
            'journal_type'           => 'contra',
            'journal_reference_type' => 'voucher',
            'journal_reference_id'   => $voucherId,
            'journal_date'           => $date,
            'journal_narration'      => 'Contra Transfer: ' . $vNumber,
            'journal_is_posted'      => 1,
            'journal_created_by'     => $this->session->get('user_id'),
        ];

        $journalId = $this->journalModel->postTransaction($journalEntry, $lines);
        $this->voucherModel->update($voucherId, ['voucher_journal_id' => $journalId]);

        $db->transComplete();

        return redirect()->to('/vouchers/contra')->with('success', 'Contra transfer voucher posted.');
    }

    public function view(int $id)
    {
        $this->authorizeContraAccess(false);
        $voucher = $this->voucherModel->select('vouchers.*, from_acc.account_name as from_account, from_acc.account_code as from_code, to_acc.account_name as to_account, to_acc.account_code as to_code')
            ->join('accounts as from_acc', 'from_acc.account_id = vouchers.voucher_account_id')
            ->join('accounts as to_acc', 'to_acc.account_id = vouchers.voucher_transfer_account_id', 'left')
            ->where('voucher_type', 'contra')
            ->where('vouchers.voucher_company_id', $this->companyId)
            ->find($id);

        if (!$voucher) {
            return redirect()->to('/vouchers/contra')->with('error', 'Contra voucher not found.');
        }

        return $this->render('vouchers/contra/view', [
            'title'   => 'Contra Voucher: ' . $voucher['voucher_number'],
            'voucher' => $voucher,
        ]);
    }
}