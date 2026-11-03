<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UserCompanyModel;
use App\Models\UserModel;

class CompanyController extends BaseController
{
    protected CompanyModel $companyModel;
    protected UserCompanyModel $userCompanyModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->companyModel     = new CompanyModel();
        $this->userCompanyModel = new UserCompanyModel();
    }

    /**
     * Check if user is authorized for company operations
     */
    protected function isAuthorizedForMultiCompany(): bool
    {
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true) || in_array('admin', $roles, true)) {
            return true;
        }

        $permissions = $this->session->get('permissions') ?? [];
        return in_array('companies.view', $permissions, true);
    }

    /**
     * Company Directory & Management View
     */
    public function index()
    {
        $this->checkPermission('companies.view');

        $search   = trim($this->request->getGet('search') ?? '');
        $status   = trim($this->request->getGet('status') ?? 'all');
        $perPage  = 15;

        $companies = $this->companyModel->getCompaniesWithDetails($search, $status, $perPage);
        $stats     = $this->companyModel->getPlatformStats();

        return $this->render('admin/companies/index', [
            'title'     => 'Companies & Subsidiaries Portfolio',
            'companies' => $companies,
            'pager'     => $this->companyModel->pager,
            'stats'     => $stats,
            'search'    => $search,
            'status'    => $status,
        ]);
    }

    /**
     * Store new independent company and auto-provision starter accounting & inventory
     */
    public function store()
    {
        $this->checkPermission('companies.create');

        $insertData = [
            'company_name'                 => trim($this->request->getPost('company_name') ?? ''),
            'company_code'                 => strtoupper(trim($this->request->getPost('company_code') ?? '')),
            'company_registration_number'  => trim($this->request->getPost('company_registration_number') ?? ''),
            'company_gstin'                => strtoupper(trim($this->request->getPost('company_gstin') ?? '')),
            'company_pan'                  => strtoupper(trim($this->request->getPost('company_pan') ?? '')),
            'company_address'              => trim($this->request->getPost('company_address') ?? ''),
            'company_city'                 => trim($this->request->getPost('company_city') ?: 'Mumbai'),
            'company_state'                => trim($this->request->getPost('company_state') ?: 'Maharashtra'),
            'company_postal_code'          => trim($this->request->getPost('company_postal_code') ?? ''),
            'company_phone'                => trim($this->request->getPost('company_phone') ?? ''),
            'company_email'                => trim($this->request->getPost('company_email') ?? ''),
            'company_website'              => trim($this->request->getPost('company_website') ?? ''),
            'company_bank_name'            => trim($this->request->getPost('company_bank_name') ?? ''),
            'company_account_no'           => trim($this->request->getPost('company_account_no') ?? ''),
            'company_ifsc_code'            => strtoupper(trim($this->request->getPost('company_ifsc_code') ?? '')),
            'company_upi_id'               => trim($this->request->getPost('company_upi_id') ?? ''),
            'company_currency_symbol'      => trim($this->request->getPost('company_currency_symbol') ?: '₹'),
            'company_financial_year_start' => trim($this->request->getPost('company_financial_year_start') ?: '04-01'),
            'company_status'               => $this->request->getPost('company_status') === 'inactive' ? 'inactive' : 'active',
        ];

        if (!$this->companyModel->validate($insertData)) {
            return redirect()->back()->withInput()->with('errors', $this->companyModel->errors());
        }

        $newCompanyId = $this->companyModel->insert($insertData);
        if (!$newCompanyId) {
            return redirect()->back()->withInput()->with('error', 'Failed to create company.');
        }

        // Link creating user to new company in user_companies
        $userId = (int)($this->session->get('user_id') ?? 0);
        if ($userId > 0) {
            $this->userCompanyModel->ignore(true)->insert([
                'uc_user_id'    => $userId,
                'uc_company_id' => (int)$newCompanyId,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        // Auto-provision standard starter Chart of Accounts, warehouse, and GST tax rates
        $this->companyModel->provisionDefaultEntities((int)$newCompanyId);

        // Auto-provision default 30-day trial subscription
        $subModel = new \App\Models\SubscriptionModel();
        $subModel->insert([
            'subscription_company_id'     => (int)$newCompanyId,
            'subscription_plan_id'        => 2, // Growth Enterprise
            'subscription_billing_cycle'  => 'monthly',
            'subscription_amount'         => 1299.00,
            'subscription_start_date'     => date('Y-m-d'),
            'subscription_end_date'       => date('Y-m-d', strtotime('+30 days')),
            'subscription_status'         => 'trial',
            'subscription_payment_method' => 'complimentary_trial',
        ]);

        // Check if user requested to switch immediately
        $autoSwitch = (bool)$this->request->getPost('auto_switch');
        if ($autoSwitch) {
            $this->session->set('company_id', (int)$newCompanyId);
            return redirect()->to('/companies')->with('success', "Company \"{$insertData['company_name']}\" added successfully and set as active context. Starter chart of accounts, default warehouse, and GST tax slabs have been provisioned!");
        }

        return redirect()->to('/companies')->with('success', "Company \"{$insertData['company_name']}\" added successfully. Starter accounts and master inventory defaults are ready.");
    }

    /**
     * Update existing company details
     */
    public function update(int $companyId)
    {
        $this->checkPermission('companies.edit');

        $company = $this->companyModel->find($companyId);
        if (!$company) {
            return redirect()->to('/companies')->with('error', 'Company not found.');
        }

        $roles = $this->session->get('roles') ?? [];
        $isSuperAdmin = in_array('super_admin', $roles, true);

        if (!$isSuperAdmin) {
            $userId = (int)($this->session->get('user_id') ?? 0);
            $hasAccess = $this->userCompanyModel->where('uc_user_id', $userId)->where('uc_company_id', $companyId)->first();
            if (!$hasAccess) {
                return redirect()->to('/companies')->with('error', 'Access denied to this company.');
            }
        }

        $updateData = [
            'company_name'                 => trim($this->request->getPost('company_name') ?? ''),
            'company_code'                 => strtoupper(trim($this->request->getPost('company_code') ?? '')),
            'company_registration_number'  => trim($this->request->getPost('company_registration_number') ?? ''),
            'company_gstin'                => strtoupper(trim($this->request->getPost('company_gstin') ?? '')),
            'company_pan'                  => strtoupper(trim($this->request->getPost('company_pan') ?? '')),
            'company_address'              => trim($this->request->getPost('company_address') ?? ''),
            'company_city'                 => trim($this->request->getPost('company_city') ?: 'Mumbai'),
            'company_state'                => trim($this->request->getPost('company_state') ?: 'Maharashtra'),
            'company_postal_code'          => trim($this->request->getPost('company_postal_code') ?? ''),
            'company_phone'                => trim($this->request->getPost('company_phone') ?? ''),
            'company_email'                => trim($this->request->getPost('company_email') ?? ''),
            'company_website'              => trim($this->request->getPost('company_website') ?? ''),
            'company_bank_name'            => trim($this->request->getPost('company_bank_name') ?? ''),
            'company_account_no'           => trim($this->request->getPost('company_account_no') ?? ''),
            'company_ifsc_code'            => strtoupper(trim($this->request->getPost('company_ifsc_code') ?? '')),
            'company_upi_id'               => trim($this->request->getPost('company_upi_id') ?? ''),
            'company_currency_symbol'      => trim($this->request->getPost('company_currency_symbol') ?: '₹'),
            'company_financial_year_start' => trim($this->request->getPost('company_financial_year_start') ?: '04-01'),
            'company_status'               => $this->request->getPost('company_status') === 'inactive' ? 'inactive' : 'active',
        ];

        if (!$this->companyModel->validate($updateData)) {
            return redirect()->back()->withInput()->with('errors', $this->companyModel->errors());
        }

        $this->companyModel->update($companyId, $updateData);

        return redirect()->to('/companies')->with('success', "Company \"{$updateData['company_name']}\" details updated successfully.");
    }

    /**
     * Delete company from portfolio (with safety guards)
     */
    public function delete(int $companyId)
    {
        $this->checkPermission('companies.delete');

        $company = $this->companyModel->find($companyId);
        if (!$company) {
            return redirect()->to('/companies')->with('error', 'Company not found.');
        }

        $activeCompanyId = (int)($this->session->get('company_id') ?? 1);
        if ($activeCompanyId === $companyId) {
            return redirect()->to('/companies')->with('error', 'Cannot delete the currently active company. Please switch to another company first.');
        }

        $totalCompanies = $this->companyModel->countAllResults();
        if ($totalCompanies <= 1) {
            return redirect()->to('/companies')->with('error', 'Cannot delete the last remaining company in the system.');
        }

        $companyName = $company['company_name'];
        $db = \Config\Database::connect();

        // Strict accounting check: do not allow deletion if the company has any financial transactions
        $invCount   = $db->table('invoices')->where('invoice_company_id', $companyId)->countAllResults();
        $billCount  = $db->table('bills')->where('bill_company_id', $companyId)->countAllResults();
        $journCount = $db->table('journals')->where('journal_company_id', $companyId)->countAllResults();
        $vouchCount = $db->table('vouchers')->where('voucher_company_id', $companyId)->countAllResults();

        $reasons = [];
        if ($invCount > 0)   $reasons[] = "{$invCount} sales invoice(s)";
        if ($billCount > 0)  $reasons[] = "{$billCount} purchase bill(s)";
        if ($journCount > 0) $reasons[] = "{$journCount} journal entry(ies)";
        if ($vouchCount > 0) $reasons[] = "{$vouchCount} payment voucher(s)";

        if (!empty($reasons)) {
            return redirect()->to('/companies')->with('error', "Cannot delete company \"{$companyName}\" because it contains active financial accounting records (" . implode(', ', $reasons) . "). Companies with transactional history cannot be deleted. You can mark the company as Inactive instead.");
        }

        $this->companyModel->deleteCompanyCascade($companyId);

        return redirect()->to('/companies')->with('success', "Company \"{$companyName}\" and its associated setup records have been removed.");
    }

    /**
     * Switch active company context for current session
     * Switch active company context in session
     */
    public function switch(int $companyId)
    {
        $this->checkPermission('companies.switch');

        $company = $this->companyModel->find($companyId);
        if (!$company || $company['company_status'] !== 'active') {
            return redirect()->back()->with('error', 'Target company is inactive or not found.');
        }

        $roles = $this->session->get('roles') ?? [];
        $isSuperAdmin = in_array('super_admin', $roles, true);

        if (!$isSuperAdmin) {
            $userId = (int)($this->session->get('user_id') ?? 0);
            $hasAccess = $this->userCompanyModel->where('uc_user_id', $userId)->where('uc_company_id', $companyId)->first();
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Access denied. You are not assigned to this company.');
            }
        }

        $this->session->set('company_id', (int)$companyId);

        $referer = $this->request->getServer('HTTP_REFERER');
        if ($referer && !str_contains($referer, 'companies/switch')) {
            return redirect()->to($referer)->with('success', "Switched active company to \"{$company['company_name']}\".");
        }

        return redirect()->to('/dashboard')->with('success', "Switched active company to \"{$company['company_name']}\". You are now viewing this company's operations.");
    }

    /**
     * View company details as JSON for modal preview & edit
     */
    public function view(int $companyId)
    {
        $this->checkPermission('companies.view');

        $company = $this->companyModel->find($companyId);
        if (!$company) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Company not found']);
        }

        $db = \Config\Database::connect();
        $accountCount = $db->table('accounts')->where('account_company_id', $companyId)->countAllResults();
        $productCount = $db->table('products')->where('product_company_id', $companyId)->countAllResults();
        $invoiceCount = $db->table('invoices')->where('invoice_company_id', $companyId)->countAllResults();
        $billCount    = $db->table('bills')->where('bill_company_id', $companyId)->countAllResults();
        $partyCount   = $db->table('parties')->where('party_company_id', $companyId)->countAllResults();
        $userCount    = $db->table('user_companies')->where('uc_company_id', $companyId)->countAllResults();

        return $this->response->setJSON([
            'company' => $company,
            'stats'   => [
                'accounts' => $accountCount,
                'products' => $productCount,
                'invoices' => $invoiceCount,
                'bills'    => $billCount,
                'parties'  => $partyCount,
                'users'    => $userCount,
            ],
        ]);
    }
}

