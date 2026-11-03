<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\CompanyModel;
use App\Models\UserModel;

class SubscriptionController extends BaseController
{
    protected SubscriptionModel $subModel;
    protected SubscriptionPlanModel $planModel;
    protected CompanyModel $companyModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->subModel     = new SubscriptionModel();
        $this->planModel    = new SubscriptionPlanModel();
        $this->companyModel = new CompanyModel();
    }

    /**
     * Check if user is authorized as Platform Super Admin
     */
    protected function authorizeSuperAdmin(): bool
    {
        $roles = $this->session->get('roles') ?? [];
        return in_array('super_admin', $roles, true);
    }

    /**
     * Primary Super Admin Subscriptions & Plans Dashboard
     */
    public function index()
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/subscription')->with('error', 'Access to Platform SaaS Subscriptions & Tier Catalog is restricted to Platform Super Administrators.');
        }

        $roles = $this->session->get('roles') ?? [];
        $isSuperAdmin = true;

        $search  = trim($this->request->getGet('search') ?? '');
        $status  = trim($this->request->getGet('status') ?? 'all');
        $planId  = (int)($this->request->getGet('plan_id') ?? 0);
        $cycle   = trim($this->request->getGet('cycle') ?? 'all');
        $tab     = trim($this->request->getGet('tab') ?? 'subscriptions');

        $subscriptions = $this->subModel->getAllSubscriptionsWithDetails($search, $status, $planId, $cycle, 15);
        $stats         = $this->subModel->getDetailedSubscriptionStats();
        $allPlans      = $this->planModel->getAllPlansAdmin();
        $companies     = $this->subModel->getAssignableCompanies();

        $subscriptionsEnabled = (bool)(config('Subscriptions')->enabled ?? true);

        return $this->render('admin/subscriptions/index', [
            'title'                => 'Platform Subscriptions & Plans',
            'subscriptions'        => $subscriptions,
            'pager'                => $this->subModel->pager,
            'stats'                => $stats,
            'allPlans'             => $allPlans,
            'companies'            => $companies,
            'search'               => $search,
            'status'               => $status,
            'planId'               => $planId,
            'cycle'                => $cycle,
            'activeTab'            => $tab,
            'subscriptionsEnabled' => $subscriptionsEnabled,
            'isSuperAdmin'         => $isSuperAdmin,
        ]);
    }

    /**
     * Create / Assign a subscription to a company
     */
    public function store()
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $companyId = (int)$this->request->getPost('subscription_company_id');
        $planId    = (int)$this->request->getPost('subscription_plan_id');
        $cycle     = ($this->request->getPost('subscription_billing_cycle') === 'yearly') ? 'yearly' : 'monthly';
        $amount    = (float)($this->request->getPost('subscription_amount') ?? 0);
        $startDate = $this->request->getPost('subscription_start_date') ?: date('Y-m-d');
        $endDate   = $this->request->getPost('subscription_end_date') ?: date('Y-m-d', strtotime('+30 days'));
        $status    = trim($this->request->getPost('subscription_status') ?? 'trial');
        $payMethod = trim($this->request->getPost('subscription_payment_method') ?? 'complimentary_trial');

        if (!$companyId || !$planId) {
            return redirect()->back()->withInput()->with('error', 'Please select both a Company and a Subscription Plan.');
        }

        $validStatuses = ['trial', 'active', 'expired', 'cancelled'];
        $status = in_array($status, $validStatuses, true) ? $status : 'trial';

        $insertedId = $this->subModel->insert([
            'subscription_company_id'     => $companyId,
            'subscription_plan_id'        => $planId,
            'subscription_billing_cycle'  => $cycle,
            'subscription_amount'         => $amount,
            'subscription_start_date'     => $startDate,
            'subscription_end_date'       => $endDate,
            'subscription_status'         => $status,
            'subscription_payment_method' => $payMethod,
        ]);

        if ($insertedId) {
            $company = $this->companyModel->find($companyId);
            $compName = $company['company_name'] ?? "Company #{$companyId}";
            return redirect()->to('/subscriptions')->with('success', "Subscription successfully assigned to {$compName}.");
        }

        return redirect()->to('/subscriptions')->with('error', 'Failed to assign subscription record.');
    }

    /**
     * Update an existing subscription
     */
    public function update(int $id)
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $sub = $this->subModel->find($id);
        if (!$sub) {
            return redirect()->to('/subscriptions')->with('error', 'Subscription not found.');
        }

        $planId    = (int)$this->request->getPost('subscription_plan_id');
        $cycle     = ($this->request->getPost('subscription_billing_cycle') === 'yearly') ? 'yearly' : 'monthly';
        $amount    = (float)($this->request->getPost('subscription_amount') ?? 0);
        $startDate = $this->request->getPost('subscription_start_date');
        $endDate   = $this->request->getPost('subscription_end_date');
        $status    = trim($this->request->getPost('subscription_status') ?? $sub['subscription_status']);
        $payMethod = trim($this->request->getPost('subscription_payment_method') ?? $sub['subscription_payment_method']);

        $validStatuses = ['trial', 'active', 'expired', 'cancelled'];

        $updateData = [
            'subscription_plan_id'        => $planId ?: $sub['subscription_plan_id'],
            'subscription_billing_cycle'  => $cycle,
            'subscription_amount'         => $amount,
            'subscription_start_date'     => $startDate ?: $sub['subscription_start_date'],
            'subscription_end_date'       => $endDate ?: $sub['subscription_end_date'],
            'subscription_status'         => in_array($status, $validStatuses, true) ? $status : $sub['subscription_status'],
            'subscription_payment_method' => $payMethod,
        ];

        $this->subModel->update($id, $updateData);

        $company = $this->companyModel->find($sub['subscription_company_id']);
        $compName = $company['company_name'] ?? "Company #{$sub['subscription_company_id']}";

        return redirect()->to('/subscriptions')->with('success', "Subscription for {$compName} updated successfully.");
    }

    /**
     * 1-Click quick actions: extend_30, extend_365, activate, expire, cancel
     */
    public function quickAction(int $id)
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $sub = $this->subModel->find($id);
        if (!$sub) {
            return redirect()->to('/subscriptions')->with('error', 'Subscription not found.');
        }

        $company = $this->companyModel->find($sub['subscription_company_id']);
        $compName = $company['company_name'] ?? "Company #{$sub['subscription_company_id']}";

        $action = trim($this->request->getPost('action') ?? '');

        switch ($action) {
            case 'extend_30':
                $this->subModel->extendSubscription($id, 30);
                $msg = "Extended subscription for {$compName} by +30 days.";
                break;

            case 'extend_365':
                $this->subModel->extendSubscription($id, 365, 'active');
                $msg = "Extended subscription for {$compName} by 1 year and marked Active.";
                break;

            case 'activate':
                $cycle = $sub['subscription_billing_cycle'] ?? 'monthly';
                $days = ($cycle === 'yearly') ? 365 : 30;
                $newEnd = date('Y-m-d', strtotime("+{$days} days"));
                $this->subModel->update($id, [
                    'subscription_status'   => 'active',
                    'subscription_end_date' => $newEnd,
                ]);
                $msg = "Activated paid subscription for {$compName} (Valid until {$newEnd}).";
                break;

            case 'expire':
                $this->subModel->update($id, ['subscription_status' => 'expired']);
                $msg = "Marked subscription for {$compName} as Expired.";
                break;

            case 'cancel':
                $this->subModel->update($id, ['subscription_status' => 'cancelled']);
                $msg = "Cancelled subscription for {$compName}.";
                break;

            default:
                return redirect()->back()->with('error', 'Unknown quick action requested.');
        }

        return redirect()->to('/subscriptions')->with('success', $msg);
    }

    /**
     * Super Admin: 1-Click Toggle for Online Subscriptions (subscriptions.enabled)
     */
    public function toggleStatus()
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $currentConfig = config('Subscriptions');
        $newStatus = !(bool)($currentConfig->enabled ?? true);
        $valString = $newStatus ? 'true' : 'false';

        $envPath = ROOTPATH . '.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            $updateEnv = function (string $key, string $val, string &$content) {
                $pattern = "/^{$key}\s*=.*$/m";
                $line = "{$key} = {$val}";
                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $line, $content);
                } else {
                    $content .= "\n{$line}";
                }
            };

            $updateEnv('subscriptions.enabled', $valString, $envContent);
            $updateEnv('SUBSCRIPTIONS_ENABLED', $valString, $envContent);

            file_put_contents($envPath, $envContent);
        }

        $statusLabel = $newStatus ? 'Active (Enabled)' : 'Paused (Disabled)';
        return redirect()->to('/subscriptions')->with('success', "Online subscriptions have been successfully set to: {$statusLabel}.");
    }

    /**
     * Store a new pricing plan tier
     */
    public function storePlan()
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $name = trim($this->request->getPost('plan_name') ?? '');
        $code = trim($this->request->getPost('plan_code') ?? '');
        if (empty($code)) {
            $code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
        }

        $featuresRaw = $this->request->getPost('plan_features');
        $featureList = [];
        if (is_string($featuresRaw)) {
            $lines = array_filter(array_map('trim', explode("\n", $featuresRaw)));
            $featureList = array_values($lines);
        }

        $data = [
            'plan_name'            => $name,
            'plan_code'            => $code,
            'plan_tagline'         => trim($this->request->getPost('plan_tagline') ?? ''),
            'plan_price_per_month' => (float)($this->request->getPost('plan_price_per_month') ?? 0),
            'plan_price_per_year'          => (float)($this->request->getPost('plan_price_per_year') ?? 0),
            'plan_max_companies'           => (int)($this->request->getPost('plan_max_companies') ?? 1),
            'plan_max_users'               => (int)($this->request->getPost('plan_max_users') ?? 5),
            'plan_max_invoices'            => (int)($this->request->getPost('plan_max_invoices') ?? 50),
            'plan_max_bills'               => (int)($this->request->getPost('plan_max_bills') ?? 20),
            'plan_max_products'            => (int)($this->request->getPost('plan_max_products') ?? 30),
            'plan_max_warehouses'          => (int)($this->request->getPost('plan_max_warehouses') ?? 1),
            'plan_allows_advanced_reports' => $this->request->getPost('plan_allows_advanced_reports') ? 1 : 0,
            'plan_badge'                   => trim($this->request->getPost('plan_badge') ?? ''),
            'plan_is_popular'      => (int)($this->request->getPost('plan_is_popular') ?? 0),
            'plan_is_active'       => (int)($this->request->getPost('plan_is_active') ?? 1),
            'plan_features'        => json_encode($featureList),
        ];

        $this->planModel->insert($data);

        return redirect()->to('/subscriptions?tab=plans')->with('success', "Pricing tier '{$name}' created successfully.");
    }

    /**
     * Update SaaS pricing plan master catalog item
     */
    public function updatePlan(int $id)
    {
        if (!$this->authorizeSuperAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access restricted to Platform Administrators.');
        }

        $plan = $this->planModel->find($id);
        if (!$plan) {
            return redirect()->to('/subscriptions?tab=plans')->with('error', 'Subscription plan tier not found.');
        }

        $featuresRaw = $this->request->getPost('plan_features');
        $featuresJson = $plan['plan_features'];
        if ($featuresRaw !== null) {
            $lines = array_filter(array_map('trim', explode("\n", $featuresRaw)));
            $featuresJson = json_encode(array_values($lines));
        }

        $data = [
            'plan_name'            => trim($this->request->getPost('plan_name') ?? $plan['plan_name']),
            'plan_tagline'         => trim($this->request->getPost('plan_tagline') ?? $plan['plan_tagline']),
            'plan_price_per_month' => (float)($this->request->getPost('plan_price_per_month') ?? $plan['plan_price_per_month']),
            'plan_price_per_year'          => (float)($this->request->getPost('plan_price_per_year') ?? $plan['plan_price_per_year']),
            'plan_max_companies'           => (int)($this->request->getPost('plan_max_companies') ?? ($plan['plan_max_companies'] ?? 1)),
            'plan_max_users'               => (int)($this->request->getPost('plan_max_users') ?? ($plan['plan_max_users'] ?? 5)),
            'plan_max_invoices'            => (int)($this->request->getPost('plan_max_invoices') ?? ($plan['plan_max_invoices'] ?? 50)),
            'plan_max_bills'               => (int)($this->request->getPost('plan_max_bills') ?? ($plan['plan_max_bills'] ?? 20)),
            'plan_max_products'            => (int)($this->request->getPost('plan_max_products') ?? ($plan['plan_max_products'] ?? 30)),
            'plan_max_warehouses'          => (int)($this->request->getPost('plan_max_warehouses') ?? ($plan['plan_max_warehouses'] ?? 1)),
            'plan_allows_advanced_reports' => $this->request->getPost('plan_allows_advanced_reports') !== null ? ($this->request->getPost('plan_allows_advanced_reports') ? 1 : 0) : ($plan['plan_allows_advanced_reports'] ?? 0),
            'plan_badge'                   => trim($this->request->getPost('plan_badge') ?? ''),
            'plan_is_popular'      => (int)($this->request->getPost('plan_is_popular') ?? 0),
            'plan_is_active'       => (int)($this->request->getPost('plan_is_active') ?? 1),
            'plan_features'        => $featuresJson,
        ];

        $this->planModel->update($id, $data);

        return redirect()->to('/subscriptions?tab=plans')->with('success', "Plan '{$data['plan_name']}' updated successfully.");
    }

    /**
     * Export all subscriptions as CSV
     */
    public function exportCsv()
    {
        $roles = $this->session->get('roles') ?? [];
        if (!in_array('super_admin', $roles, true)) {
            $this->checkPermission('subscriptions.view');
        }

        $subscriptions = $this->subModel->getAllSubscriptionsWithDetails(null, null, null, null, 10000);
        $filename = 'lenden_subscriptions_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'Subscription ID',
            'Company Legal Name',
            'Company Code',
            'Registration No',
            'GSTIN',
            'City',
            'State',
            'Primary Contact Name',
            'Contact Email',
            'Contact Phone',
            'Plan Tier',
            'Billing Frequency',
            'Amount (INR)',
            'Start Date',
            'End Date',
            'Days Remaining',
            'Status',
            'Payment Mode',
            'Active Users',
        ]);

        foreach ($subscriptions as $s) {
            fputcsv($output, [
                $s['subscription_id'],
                $s['company_name'] ?? 'N/A',
                $s['company_code'] ?? 'N/A',
                $s['company_registration_number'] ?? 'N/A',
                $s['company_gstin'] ?? 'N/A',
                $s['company_city'] ?? 'Mumbai',
                $s['company_state'] ?? 'Maharashtra',
                $s['admin_name'] ?? 'Unassigned',
                $s['admin_email'] ?? 'N/A',
                $s['admin_phone'] ?? 'N/A',
                $s['plan_name'] ?? 'Custom',
                ucfirst($s['subscription_billing_cycle'] ?? 'monthly'),
                number_format((float)($s['subscription_amount'] ?? 0), 2, '.', ''),
                $s['subscription_start_date'] ?? 'N/A',
                $s['subscription_end_date'] ?? 'N/A',
                (int)($s['days_remaining'] ?? 0),
                ucfirst($s['subscription_status'] ?? 'trial'),
                ucwords(str_replace('_', ' ', $s['subscription_payment_method'] ?? 'complimentary_trial')),
                (int)($s['total_users'] ?? 0),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Customer / Company Portal: My Subscription & Upgrade Catalog
     */
    public function mySubscription()
    {
        $companyId = $this->getCompanyId();
        $currentSub = $this->subModel->getCompanySubscription($companyId);
        $allPlans = $this->planModel->getActivePlans();

        $daysRemaining = null;
        if (!empty($currentSub['subscription_end_date'])) {
            $end = new \DateTime($currentSub['subscription_end_date']);
            $today = new \DateTime(date('Y-m-d'));
            $diff = $today->diff($end);
            $daysRemaining = $end < $today ? -$diff->days : $diff->days;
        }

        $db = \Config\Database::connect();
        $totalUsers = $db->table('user_companies')->where('uc_company_id', $companyId)->countAllResults();
        $usageSummary = $this->subModel->getCompanyUsageSummary($companyId);

        $txModel = new \App\Models\PaymentTransactionModel();
        $recentTransactions = $txModel->getCompanyTransactions($companyId, 10);
        $razorpayConfig = config('Razorpay');

        return $this->render('admin/subscriptions/my_subscription', [
            'title'                => 'Company Subscription & Plan',
            'currentSub'           => $currentSub,
            'allPlans'             => $allPlans,
            'daysRemaining'        => $daysRemaining,
            'totalUsers'           => $totalUsers,
            'usageSummary'         => $usageSummary,
            'subscriptionsEnabled' => (bool)(config('Subscriptions')->enabled ?? true),
            'razorpayConfig'       => $razorpayConfig,
            'recentTransactions'   => $recentTransactions,
        ]);
    }
}

