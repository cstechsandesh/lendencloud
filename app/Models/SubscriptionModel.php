<?php

namespace App\Models;

use App\Core\CSModel;

class SubscriptionModel extends CSModel
{
    protected $table            = 'subscriptions';
    protected $primaryKey       = 'subscription_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'subscription_company_id',
        'subscription_plan_id',
        'subscription_billing_cycle',
        'subscription_amount',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_status',
        'subscription_payment_method',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active/latest subscription for a specific company
     */
    public function getCompanySubscription(int $companyId): ?array
    {
        $sub = $this->select('subscriptions.*, subscription_plans.plan_name, subscription_plans.plan_code,
                             subscription_plans.plan_tagline, subscription_plans.plan_features,
                             subscription_plans.plan_max_companies, subscription_plans.plan_max_users,
                             subscription_plans.plan_max_invoices, subscription_plans.plan_max_bills,
                             subscription_plans.plan_max_products, subscription_plans.plan_max_warehouses,
                             subscription_plans.plan_allows_advanced_reports,
                             subscription_plans.plan_badge')
            ->join('subscription_plans', 'subscription_plans.plan_id = subscriptions.subscription_plan_id', 'left')
            ->where('subscriptions.subscription_company_id', $companyId)
            ->orderBy('subscriptions.subscription_id', 'DESC')
            ->first();

        if ($sub && !empty($sub['plan_features'])) {
            $decoded = json_decode($sub['plan_features'], true);
            $sub['feature_list'] = is_array($decoded) ? $decoded : [];
        }

        return $sub;
    }

    /**
     * Fetch paginated subscriptions with company, plan, primary contact, and days remaining.
     */
    public function getAllSubscriptionsWithDetails(?string $search = null, ?string $status = null, ?int $planId = null, ?string $cycle = null, int $perPage = 15): array
    {
        $subTbl  = $this->db->prefixTable('subscriptions');
        $compTbl = $this->db->prefixTable('companies');
        $planTbl = $this->db->prefixTable('subscription_plans');
        $userTbl = $this->db->prefixTable('users');
        $ucTbl   = $this->db->prefixTable('user_companies');

        $builder = $this->select("
            {$subTbl}.*,
            {$compTbl}.company_name,
            {$compTbl}.company_code,
            {$compTbl}.company_registration_number,
            {$compTbl}.company_gstin,
            {$compTbl}.company_city,
            {$compTbl}.company_state,
            {$compTbl}.company_email,
            {$compTbl}.company_phone,
            {$planTbl}.plan_name,
            {$planTbl}.plan_code,
            {$planTbl}.plan_badge,
            {$planTbl}.plan_max_companies,
            {$planTbl}.plan_max_users,
            {$planTbl}.plan_max_invoices,
            {$planTbl}.plan_max_bills,
            {$planTbl}.plan_max_products,
            {$planTbl}.plan_max_warehouses,
            {$planTbl}.plan_allows_advanced_reports,
            (SELECT COUNT(uc_id) FROM {$ucTbl} WHERE uc_company_id = {$subTbl}.subscription_company_id) AS total_users,
            (SELECT user_name FROM {$userTbl} WHERE user_company_id = {$subTbl}.subscription_company_id ORDER BY user_id ASC LIMIT 1) AS admin_name,
            (SELECT user_email FROM {$userTbl} WHERE user_company_id = {$subTbl}.subscription_company_id ORDER BY user_id ASC LIMIT 1) AS admin_email,
            (SELECT user_phone FROM {$userTbl} WHERE user_company_id = {$subTbl}.subscription_company_id ORDER BY user_id ASC LIMIT 1) AS admin_phone,
            DATEDIFF({$subTbl}.subscription_end_date, CURRENT_DATE()) AS days_remaining
        ", false)
        ->join("{$compTbl}", "{$compTbl}.company_id = {$subTbl}.subscription_company_id", 'left')
        ->join("{$planTbl}", "{$planTbl}.plan_id = {$subTbl}.subscription_plan_id", 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like("{$compTbl}.company_name", $search)
                ->orLike("{$compTbl}.company_code", $search)
                ->orLike("{$compTbl}.company_registration_number", $search)
                ->orLike("{$compTbl}.company_gstin", $search)
                ->orLike("{$compTbl}.company_city", $search)
                ->orLike("{$planTbl}.plan_name", $search)
                ->groupEnd();
        }

        if (!empty($status) && $status !== 'all') {
            $builder->where("{$subTbl}.subscription_status", $status);
        }

        if (!empty($planId) && $planId > 0) {
            $builder->where("{$subTbl}.subscription_plan_id", $planId);
        }

        if (!empty($cycle) && $cycle !== 'all') {
            $builder->where("{$subTbl}.subscription_billing_cycle", $cycle);
        }

        return $builder->orderBy("{$subTbl}.subscription_id", 'DESC')->paginate($perPage);
    }

    /**
     * Get aggregate KPI metrics for Super Admin subscriptions dashboard
     */
    public function getDetailedSubscriptionStats(): array
    {
        $subTbl = $this->db->prefixTable('subscriptions');

        $total = $this->countAllResults();

        $activePaid = $this->where('subscription_status', 'active')->countAllResults();
        $activeTrials = $this->where('subscription_status', 'trial')->countAllResults();
        $expiredOrCancelled = $this->whereIn('subscription_status', ['expired', 'cancelled'])->countAllResults();

        // Expiring in next 7 days
        $expiringSoon = (int)($this->db->query("
            SELECT COUNT(subscription_id) AS cnt 
            FROM {$subTbl} 
            WHERE subscription_status IN ('trial', 'active') 
              AND subscription_end_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
        ")->getRow()->cnt ?? 0);

        // MRR Calculation: Monthly subscriptions amount + (Yearly subscriptions amount / 12)
        $mrrRow = $this->db->query("
            SELECT 
                COALESCE(SUM(CASE WHEN subscription_billing_cycle = 'monthly' THEN subscription_amount ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN subscription_billing_cycle = 'yearly' THEN subscription_amount / 12 ELSE 0 END), 0) AS mrr
            FROM {$subTbl}
            WHERE subscription_status = 'active'
        ")->getRow();

        $mrr = (float)($mrrRow->mrr ?? 0.0);
        $arr = $mrr * 12;

        return [
            'total_subscriptions'  => $total,
            'active_paid'          => $activePaid,
            'active_trials'        => $activeTrials,
            'expiring_soon'        => $expiringSoon,
            'expired_or_cancelled' => $expiredOrCancelled,
            'mrr'                  => $mrr,
            'arr'                  => $arr,
        ];
    }

    /**
     * Extend subscription end date by X days and optionally update status
     */
    public function extendSubscription(int $id, int $days, ?string $newStatus = null): bool
    {
        $sub = $this->find($id);
        if (!$sub) {
            return false;
        }

        $currentEnd = !empty($sub['subscription_end_date']) ? strtotime($sub['subscription_end_date']) : 0;
        $baseTime   = ($currentEnd > time()) ? $currentEnd : time();
        $newEndDate = date('Y-m-d', strtotime("+{$days} days", $baseTime));

        $data = ['subscription_end_date' => $newEndDate];
        if ($newStatus !== null) {
            $data['subscription_status'] = $newStatus;
        }

        return $this->update($id, $data);
    }

    /**
     * Get all registered companies for assigning subscriptions
     */
    public function getAssignableCompanies(): array
    {
        $compModel = new \App\Models\CompanyModel();
        return $compModel->select('company_id, company_name, company_code, company_registration_number, company_city')
            ->orderBy('company_name', 'ASC')
            ->findAll();
    }

    /**
     * Check if active company exceeds numeric resource quota
     *
     * @param int $companyId
     * @param string $resource 'invoices'|'bills'|'products'|'warehouses'|'advanced_reports'
     * @return array ['allowed' => bool, 'current' => int, 'limit' => int, 'message' => string]
     */
    public function checkQuota(int $companyId, string $resource): array
    {
        $sub = $this->getCompanySubscription($companyId);
        if (!$sub) {
            $planModel = new SubscriptionPlanModel();
            $defaultPlan = $planModel->where('plan_code', 'starter_business')->first();
            $sub = $defaultPlan ?: [
                'plan_max_invoices'            => 50,
                'plan_max_bills'               => 20,
                'plan_max_products'            => 30,
                'plan_max_warehouses'          => 1,
                'plan_allows_advanced_reports' => 0,
            ];
        }

        $db = \Config\Database::connect();
        $startOfMonth = date('Y-m-01 00:00:00');

        switch ($resource) {
            case 'invoices':
                $limit = isset($sub['plan_max_invoices']) ? (int)$sub['plan_max_invoices'] : 50;
                $invTable = $db->prefixTable('invoices');
                $count = (int)($db->query("SELECT COUNT(*) AS cnt FROM {$invTable} WHERE invoice_company_id = ? AND created_at >= ?", [$companyId, $startOfMonth])->getRow()->cnt ?? 0);
                $allowed = ($limit === 0 || $count < $limit);
                return [
                    'allowed' => $allowed,
                    'current' => $count,
                    'limit'   => $limit,
                    'message' => $allowed ? '' : "Monthly invoice quota reached ({$count}/{$limit} used this month). Please upgrade your plan to generate additional sales invoices.",
                ];

            case 'bills':
                $limit = isset($sub['plan_max_bills']) ? (int)$sub['plan_max_bills'] : 20;
                $billTable = $db->prefixTable('bills');
                $count = (int)($db->query("SELECT COUNT(*) AS cnt FROM {$billTable} WHERE bill_company_id = ? AND created_at >= ?", [$companyId, $startOfMonth])->getRow()->cnt ?? 0);
                $allowed = ($limit === 0 || $count < $limit);
                return [
                    'allowed' => $allowed,
                    'current' => $count,
                    'limit'   => $limit,
                    'message' => $allowed ? '' : "Monthly vendor bill quota reached ({$count}/{$limit} used this month). Please upgrade your plan to record additional purchase bills.",
                ];

            case 'products':
                $limit = isset($sub['plan_max_products']) ? (int)$sub['plan_max_products'] : 30;
                $prodTable = $db->prefixTable('products');
                $count = (int)($db->query("SELECT COUNT(*) AS cnt FROM {$prodTable} WHERE product_company_id = ?", [$companyId])->getRow()->cnt ?? 0);
                $allowed = ($limit === 0 || $count < $limit);
                return [
                    'allowed' => $allowed,
                    'current' => $count,
                    'limit'   => $limit,
                    'message' => $allowed ? '' : "Product catalog quota reached ({$count}/{$limit} items cataloged). Please upgrade your plan to add more inventory items.",
                ];

            case 'warehouses':
                $limit = isset($sub['plan_max_warehouses']) ? (int)$sub['plan_max_warehouses'] : 1;
                $whTable = $db->prefixTable('warehouses');
                $count = (int)($db->query("SELECT COUNT(*) AS cnt FROM {$whTable} WHERE warehouse_company_id = ?", [$companyId])->getRow()->cnt ?? 0);
                $allowed = ($limit === 0 || $count < $limit);
                return [
                    'allowed' => $allowed,
                    'current' => $count,
                    'limit'   => $limit,
                    'message' => $allowed ? '' : "Storage warehouse quota reached ({$count}/{$limit} active locations). Please upgrade your plan to manage additional warehouses.",
                ];

            case 'advanced_reports':
                $allowed = !empty($sub['plan_allows_advanced_reports']);
                return [
                    'allowed' => $allowed,
                    'current' => $allowed ? 1 : 0,
                    'limit'   => 1,
                    'message' => $allowed ? '' : "Advanced financial reports (Balance Sheet, Profit & Loss, Trial Balance) are restricted on your current plan. Please upgrade to Growth Enterprise or higher.",
                ];

            default:
                return ['allowed' => true, 'current' => 0, 'limit' => 0, 'message' => ''];
        }
    }

    /**
     * Get real-time usage metrics and quota percentages for a company
     */
    public function getCompanyUsageSummary(int $companyId): array
    {
        $invQuota  = $this->checkQuota($companyId, 'invoices');
        $billQuota = $this->checkQuota($companyId, 'bills');
        $prodQuota = $this->checkQuota($companyId, 'products');
        $whQuota   = $this->checkQuota($companyId, 'warehouses');
        $repQuota  = $this->checkQuota($companyId, 'advanced_reports');

        $calcPct = function(int $current, int $limit) {
            if ($limit <= 0) return 0;
            return min(100, round(($current / $limit) * 100));
        };

        return [
            'invoices' => [
                'title'       => 'Monthly Invoices',
                'current'     => $invQuota['current'],
                'limit'       => $invQuota['limit'],
                'unlimited'   => ($invQuota['limit'] === 0),
                'percentage'  => $calcPct($invQuota['current'], $invQuota['limit']),
                'allowed'     => $invQuota['allowed'],
                'unit'        => 'invoices / month',
                'description' => ($invQuota['limit'] > 0) ? max(0, $invQuota['limit'] - $invQuota['current']) . ' remaining this month' : 'Unlimited invoicing',
                'icon'        => 'bi-receipt',
            ],
            'bills' => [
                'title'       => 'Monthly Purchase Bills',
                'current'     => $billQuota['current'],
                'limit'       => $billQuota['limit'],
                'unlimited'   => ($billQuota['limit'] === 0),
                'percentage'  => $calcPct($billQuota['current'], $billQuota['limit']),
                'allowed'     => $billQuota['allowed'],
                'unit'        => 'bills / month',
                'description' => ($billQuota['limit'] > 0) ? max(0, $billQuota['limit'] - $billQuota['current']) . ' remaining this month' : 'Unlimited vendor bills',
                'icon'        => 'bi-receipt-cutoff',
            ],
            'products' => [
                'title'       => 'Catalog Products',
                'current'     => $prodQuota['current'],
                'limit'       => $prodQuota['limit'],
                'unlimited'   => ($prodQuota['limit'] === 0),
                'percentage'  => $calcPct($prodQuota['current'], $prodQuota['limit']),
                'allowed'     => $prodQuota['allowed'],
                'unit'        => 'SKU items',
                'description' => ($prodQuota['limit'] > 0) ? max(0, $prodQuota['limit'] - $prodQuota['current']) . ' slots remaining' : 'Unlimited product catalog',
                'icon'        => 'bi-box-seam',
            ],
            'warehouses' => [
                'title'       => 'Storage Warehouses',
                'current'     => $whQuota['current'],
                'limit'       => $whQuota['limit'],
                'unlimited'   => ($whQuota['limit'] === 0),
                'percentage'  => $calcPct($whQuota['current'], $whQuota['limit']),
                'allowed'     => $whQuota['allowed'],
                'unit'        => 'locations',
                'description' => ($whQuota['limit'] > 0) ? max(0, $whQuota['limit'] - $whQuota['current']) . ' warehouse slots remaining' : 'Unlimited storage locations',
                'icon'        => 'bi-building-gear',
            ],
            'advanced_reports' => [
                'title'       => 'Advanced Statements',
                'allowed'     => $repQuota['allowed'],
                'status'      => $repQuota['allowed'] ? 'Unlocked' : 'Locked',
                'unit'        => 'Balance Sheet & P&L',
                'description' => $repQuota['allowed'] ? 'Full audit statements enabled' : 'Upgrade tier required',
                'icon'        => 'bi-file-earmark-bar-graph',
            ],
        ];
    }
}

