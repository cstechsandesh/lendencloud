<?php

namespace App\Controllers\Parties;

use App\Controllers\BaseController;
use App\Models\PartyModel;
use App\Models\AccountModel;

class PartyController extends BaseController
{
    protected PartyModel $partyModel;
    protected AccountModel $accountModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->partyModel   = new PartyModel();
        $this->accountModel = new AccountModel();
    }

    public function index()
    {
        $this->checkPermission('parties.view');
        $type = $this->request->getGet('type');
        $builder = $this->partyModel->where('party_company_id', $this->companyId)->orderBy('party_name', 'ASC');
        if ($type && in_array($type, ['customer', 'vendor'])) {
            $builder->where('party_type', $type);
        }
        $parties = $builder->findAll();
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_is_active', 1)->findAll();

        return $this->render('parties/index', [
            'title'        => 'Parties Management (Customers & Vendors)',
            'parties'      => $parties,
            'accounts'     => $accounts,
            'current_type' => $type,
        ]);
    }

    public function create()
    {
        $this->checkPermission('parties.create');
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_is_active', 1)->findAll();

        return $this->render('parties/create', [
            'title'    => 'Add New Customer / Vendor',
            'accounts' => $accounts,
        ]);
    }

    public function store()
    {
        $this->checkPermission('parties.create');
        $rules = [
            'party_name' => 'required|min_length[2]|max_length[200]',
            'party_type' => 'required|in_list[customer,vendor,both]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $type = $this->request->getPost('party_type');
        $accId = $this->request->getPost('party_account_id');

        // Auto-assign default account if not provided
        if (empty($accId)) {
            if ($type === 'customer') {
                $acc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['1100', '1020'])->first();
                $accId = $acc ? $acc['account_id'] : null;
            } else {
                $acc = $this->accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['2000', '2010'])->first();
                $accId = $acc ? $acc['account_id'] : null;
            }
        }

        $this->partyModel->insert([
            'party_company_id'      => $this->companyId,
            'party_type'            => $type,
            'party_name'            => trim($this->request->getPost('party_name') ?? ''),
            'party_code'            => trim($this->request->getPost('party_code') ?? '') ?: null,
            'party_email'           => trim($this->request->getPost('party_email') ?? '') ?: null,
            'party_phone'           => trim($this->request->getPost('party_phone') ?? '') ?: null,
            'party_address'         => $this->request->getPost('party_address'),
            'party_gstin'           => trim($this->request->getPost('party_gstin') ?? '') ?: null,
            'party_pan'             => trim($this->request->getPost('party_pan') ?? '') ?: null,
            'party_account_id'      => $accId,
            'party_credit_limit'    => (float)$this->request->getPost('party_credit_limit'),
            'party_credit_days'     => (int)$this->request->getPost('party_credit_days'),
            'party_current_balance' => 0.00,
            'party_status'          => $this->request->getPost('party_status') ?: 'active',
        ]);

        return redirect()->to('/parties')->with('success', 'Party created successfully.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('parties.edit');
        $party = $this->partyModel->find($id);
        if (!$party) return redirect()->to('/parties')->with('error', 'Party not found.');

        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_is_active', 1)->findAll();

        return $this->render('parties/edit', [
            'title'    => 'Edit Party: ' . $party['party_name'],
            'party'    => $party,
            'accounts' => $accounts,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('parties.edit');
        $rules = [
            'party_name' => 'required|min_length[2]|max_length[200]',
            'party_type' => 'required|in_list[customer,vendor,both]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->partyModel->update($id, [
            'party_type'         => $this->request->getPost('party_type'),
            'party_name'         => trim($this->request->getPost('party_name') ?? ''),
            'party_code'         => trim($this->request->getPost('party_code') ?? '') ?: null,
            'party_email'        => trim($this->request->getPost('party_email') ?? '') ?: null,
            'party_phone'        => trim($this->request->getPost('party_phone') ?? '') ?: null,
            'party_address'      => $this->request->getPost('party_address'),
            'party_gstin'        => trim($this->request->getPost('party_gstin') ?? '') ?: null,
            'party_pan'          => trim($this->request->getPost('party_pan') ?? '') ?: null,
            'party_account_id'   => $this->request->getPost('party_account_id') ?: null,
            'party_credit_limit' => (float)$this->request->getPost('party_credit_limit'),
            'party_credit_days'  => (int)$this->request->getPost('party_credit_days'),
            'party_status'       => $this->request->getPost('party_status') ?: 'active',
        ]);

        return redirect()->to('/parties')->with('success', 'Party details updated.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('parties.delete');

        $party = $this->partyModel->where('party_id', $id)
            ->where('party_company_id', $this->companyId)
            ->first();

        if (!$party) {
            return redirect()->to('/parties')->with('error', 'Party record not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();

        // Check if party is used in any financial transactions or orders
        $invCount   = $db->table('invoices')->where('invoice_party_id', $id)->countAllResults();
        $billCount  = $db->table('bills')->where('bill_party_id', $id)->countAllResults();
        $vouchCount = $db->table('vouchers')->where('voucher_party_id', $id)->countAllResults();
        $soCount    = $db->table('sales_orders')->where('so_party_id', $id)->countAllResults();
        $poCount    = $db->table('purchase_orders')->where('po_party_id', $id)->countAllResults();
        $curBalance = (float)($party['party_current_balance'] ?? 0);

        $reasons = [];
        if ($invCount > 0)   $reasons[] = "{$invCount} sales invoice(s)";
        if ($billCount > 0)  $reasons[] = "{$billCount} purchase bill(s)";
        if ($vouchCount > 0) $reasons[] = "{$vouchCount} payment/receipt voucher(s)";
        if ($soCount > 0)    $reasons[] = "{$soCount} sales order(s)";
        if ($poCount > 0)    $reasons[] = "{$poCount} purchase order(s)";
        if (abs($curBalance) > 0.001) $reasons[] = "outstanding ledger balance of " . number_format($curBalance, 2);

        if (!empty($reasons)) {
            return redirect()->to('/parties')->with('error', "Cannot delete party \"{$party['party_name']}\" because it is referenced in: " . implode(', ', $reasons) . ". To prevent accidental use, edit and change their status to Inactive instead.");
        }

        $this->partyModel->delete($id);
        return redirect()->to('/parties')->with('success', "Party \"{$party['party_name']}\" deleted successfully.");
    }

    public function statement(int $id)
    {
        $this->checkPermission('parties.view');
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $data = $this->partyModel->getStatement($id, $from, $to, $this->companyId);
        if (!$data['party']) return redirect()->to('/parties')->with('error', 'Party not found.');

        return $this->render('parties/statement', [
            'title'        => 'Party Account Statement: ' . $data['party']['party_name'],
            'party'        => $data['party'],
            'transactions' => $data['transactions'],
            'closing'      => $data['closing'],
            'date_from'    => $from,
            'date_to'      => $to,
        ]);
    }
}