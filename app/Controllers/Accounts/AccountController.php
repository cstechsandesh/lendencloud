<?php

namespace App\Controllers\Accounts;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\AccountGroupModel;

class AccountController extends BaseController
{
    protected AccountModel $accountModel;
    protected AccountGroupModel $groupModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->accountModel = new AccountModel();
        $this->groupModel   = new AccountGroupModel();
    }

    public function index()
    {
        $this->checkPermission('accounts.view');
        $groupedAccounts = $this->accountModel->getAccountsGrouped($this->companyId);
        $groups = $this->groupModel->where('group_company_id', $this->companyId)->findAll();

        return $this->render('accounts/index', [
            'title'           => 'Chart of Accounts',
            'groupedAccounts' => $groupedAccounts,
            'groups'          => $groups,
        ]);
    }

    public function create()
    {
        $this->checkPermission('accounts.create');
        $groups = $this->groupModel->where('group_company_id', $this->companyId)->orderBy('group_name', 'ASC')->findAll();

        return $this->render('accounts/create', [
            'title'  => 'Create New Ledger Account',
            'groups' => $groups,
        ]);
    }

    public function store()
    {
        $this->checkPermission('accounts.create');
        $rules = [
            'account_code'         => 'required',
            'account_name'         => 'required|min_length[2]|max_length[150]',
            'account_type'         => 'required|in_list[asset,liability,equity,revenue,expense]',
            'account_balance_type' => 'required|in_list[debit,credit]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = trim($this->request->getPost('account_code'));
        $existing = $this->accountModel->where('account_company_id', $this->companyId)->where('account_code', $code)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Account code already exists for this company.');
        }

        $opening = (float)$this->request->getPost('account_opening_balance');

        $this->accountModel->insert([
            'account_company_id'      => $this->companyId,
            'account_group_id'        => $this->request->getPost('account_group_id') ?: null,
            'account_code'            => $code,
            'account_name'            => trim($this->request->getPost('account_name')),
            'account_type'            => $this->request->getPost('account_type'),
            'account_balance_type'    => $this->request->getPost('account_balance_type'),
            'account_opening_balance' => $opening,
            'account_current_balance' => $opening,
            'account_description'     => $this->request->getPost('account_description'),
            'account_is_active'       => $this->request->getPost('account_is_active') ? 1 : 0,
            'account_is_system'       => 0,
        ]);

        return redirect()->to('/accounts')->with('success', 'Ledger account created successfully.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('accounts.edit');
        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->to('/accounts')->with('error', 'Account not found.');
        }

        $groups = $this->groupModel->where('group_company_id', $this->companyId)->orderBy('group_name', 'ASC')->findAll();

        return $this->render('accounts/edit', [
            'title'   => 'Edit Account: ' . $account['account_name'],
            'account' => $account,
            'groups'  => $groups,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('accounts.edit');
        $account = $this->accountModel->find($id);
        if (!$account) {
            return redirect()->to('/accounts')->with('error', 'Account not found.');
        }

        $rules = [
            'account_name'         => 'required|min_length[2]|max_length[150]',
            'account_type'         => 'required|in_list[asset,liability,equity,revenue,expense]',
            'account_balance_type' => 'required|in_list[debit,credit]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'account_group_id'     => $this->request->getPost('account_group_id') ?: null,
            'account_name'         => trim($this->request->getPost('account_name')),
            'account_type'         => $this->request->getPost('account_type'),
            'account_balance_type' => $this->request->getPost('account_balance_type'),
            'account_description'  => $this->request->getPost('account_description'),
            'account_is_active'    => $this->request->getPost('account_is_active') ? 1 : 0,
        ];

        // Non-system accounts can also update opening balance and code
        if (!$account['account_is_system']) {
            $data['account_code'] = trim($this->request->getPost('account_code'));
            $data['account_opening_balance'] = (float)$this->request->getPost('account_opening_balance');
        }

        $this->accountModel->update($id, $data);
        $this->accountModel->recalculateBalance($id);

        return redirect()->to('/accounts')->with('success', 'Account updated successfully.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('accounts.delete');
        $account = $this->accountModel->where('account_id', $id)
            ->where('account_company_id', $this->companyId)
            ->first();

        if (!$account) {
            return redirect()->to('/accounts')->with('error', 'Account not found or does not belong to active company.');
        }

        if ($account['account_is_system']) {
            return redirect()->to('/accounts')->with('error', "System core account \"{$account['account_name']}\" is protected and cannot be deleted.");
        }

        $db = \Config\Database::connect();

        // Check if account has postings or is referenced anywhere
        $jlCount   = $db->table('journal_lines')->where('jl_account_id', $id)->countAllResults();
        $vCount    = $db->table('vouchers')->where('voucher_account_id', $id)->countAllResults();
        $pCount    = $db->table('parties')->where('party_account_id', $id)->countAllResults();
        $balance   = (float)($account['account_current_balance'] ?? 0);

        $reasons = [];
        if ($jlCount > 0)  $reasons[] = "{$jlCount} general ledger journal line(s)";
        if ($vCount > 0)   $reasons[] = "{$vCount} bank/cash voucher(s)";
        if ($pCount > 0)   $reasons[] = "{$pCount} linked customer/vendor master(s)";
        if (abs($balance) > 0.001) $reasons[] = "a non-zero balance of " . number_format($balance, 2);

        if (!empty($reasons)) {
            return redirect()->to('/accounts')->with('error', "Cannot delete account \"{$account['account_name']} ({$account['account_code']})\" because it has: " . implode(', ', $reasons) . ". Accounts with historical double-entry data must be preserved.");
        }

        $this->accountModel->delete($id);
        return redirect()->to('/accounts')->with('success', "Account \"{$account['account_name']}\" deleted successfully.");
    }

    public function ledger(int $id)
    {
        $this->checkPermission('accounts.ledger');
        $from = $this->request->getGet('from') ?: date('Y-01-01');
        $to   = $this->request->getGet('to')   ?: date('Y-12-31');

        $ledgerData = $this->accountModel->getLedger($id, $from, $to);
        if (!$ledgerData['account']) {
            return redirect()->to('/accounts')->with('error', 'Account not found.');
        }

        return $this->render('accounts/ledger', [
            'title'      => 'General Ledger Statement: ' . $ledgerData['account']['account_name'],
            'account'    => $ledgerData['account'],
            'entries'    => $ledgerData['entries'],
            'summary'    => $ledgerData['summary'],
            'date_from'  => $from,
            'date_to'    => $to,
        ]);
    }
}