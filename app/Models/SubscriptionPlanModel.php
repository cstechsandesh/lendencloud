<?php

namespace App\Models;

use App\Core\CSModel;

class SubscriptionPlanModel extends CSModel
{
    protected $table            = 'subscription_plans';
    protected $primaryKey       = 'plan_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'plan_name',
        'plan_code',
        'plan_tagline',
        'plan_price_per_month',
        'plan_price_per_year',
        'plan_max_companies',
        'plan_max_users',
        'plan_max_invoices',
        'plan_max_bills',
        'plan_max_products',
        'plan_max_warehouses',
        'plan_allows_advanced_reports',
        'plan_features',
        'plan_badge',
        'plan_is_popular',
        'plan_is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const FEATURE_SALES            = 'sales';
    public const FEATURE_PURCHASES        = 'purchases';
    public const FEATURE_INVENTORY        = 'inventory';
    public const FEATURE_ADVANCED_REPORTS = 'advanced_reports';
    public const FEATURE_MULTI_COMPANY    = 'multi_company';

    /**
     * Fallback feature entitlement matrix per plan code
     */
    protected static array $planEntitlements = [
        'starter_business' => [
            'sales'            => true,
            'purchases'        => true, // basic quota: 20 bills
            'inventory'        => true, // basic quota: 30 items
            'advanced_reports' => false,
            'multi_company'    => false,
        ],
        'growth_enterprise' => [
            'sales'            => true,
            'purchases'        => true,
            'inventory'        => true,
            'advanced_reports' => true,
            'multi_company'    => false,
        ],
        'multi_company_portfolio' => [
            'sales'            => true,
            'purchases'        => true,
            'inventory'        => true,
            'advanced_reports' => true,
            'multi_company'    => true,
        ],
        'corporate_conglomerate' => [
            'sales'            => true,
            'purchases'        => true,
            'inventory'        => true,
            'advanced_reports' => true,
            'multi_company'    => true,
        ],
    ];

    /**
     * Check if a specific plan allows a feature based on dynamic DB row or plan code
     *
     * @param string|array|null $plan Plan code or plan array from database
     * @param string $feature
     * @return bool
     */
    public static function planAllowsFeature($plan, string $feature): bool
    {
        if (is_array($plan)) {
            if ($feature === 'advanced_reports') {
                return !empty($plan['plan_allows_advanced_reports']);
            }
            if ($feature === 'multi_company') {
                return isset($plan['plan_max_companies']) && ((int)$plan['plan_max_companies'] > 1 || (int)$plan['plan_max_companies'] === 0);
            }
            if ($feature === 'inventory') {
                return !isset($plan['plan_max_products']) || (int)$plan['plan_max_products'] > 0 || (int)$plan['plan_max_products'] === 0;
            }
            if ($feature === 'purchases') {
                return !isset($plan['plan_max_bills']) || (int)$plan['plan_max_bills'] > 0 || (int)$plan['plan_max_bills'] === 0;
            }
            if ($feature === 'sales') {
                return true;
            }
            $planCode = $plan['plan_code'] ?? 'starter_business';
        } else {
            $planCode = (string)($plan ?: 'starter_business');
        }

        return !empty(self::$planEntitlements[$planCode][$feature]);
    }

    /**
     * Get user-facing title for a feature
     */
    public static function getFeatureTitle(string $feature): string
    {
        $titles = [
            'sales'            => 'Sales & Invoicing Module',
            'purchases'        => 'Purchases & Vendor Bills Module',
            'inventory'        => 'Multi-Warehouse Inventory & Products Module',
            'advanced_reports' => 'Advanced Accounting Statements (Balance Sheet, P&L, Trial Balance)',
            'multi_company'    => 'Multi-Company Subsidiaries & Holding Network',
        ];

        return $titles[$feature] ?? ucfirst(str_replace('_', ' ', $feature));
    }

    /**
     * Get recommended minimum tier required for a feature
     */
    public static function getRequiredPlanForFeature(string $feature): string
    {
        $plans = [
            'purchases'        => 'Growth Enterprise',
            'inventory'        => 'Growth Enterprise',
            'advanced_reports' => 'Growth Enterprise',
            'multi_company'    => 'Multi-Company Portfolio',
        ];

        return $plans[$feature] ?? 'Growth Enterprise';
    }

    /**
     * Get all active pricing plans for presentation and pricing tables
     */
    public function getActivePlans(): array
    {
        try {
            $plans = $this->where('plan_is_active', 1)
                ->orderBy('plan_price_per_month', 'ASC')
                ->findAll();
        } catch (\Throwable $e) {
            $plans = [];
        }

        if (empty($plans)) {
            return [
                [
                    'plan_id'                      => 1,
                    'plan_name'                    => 'Starter Business',
                    'plan_code'                    => 'starter_business',
                    'plan_tagline'                 => 'Essential double-entry accounting, invoicing & GST billing for sole proprietors & startups.',
                    'plan_price_per_month'         => 499.00,
                    'plan_price_per_year'          => 4990.00,
                    'plan_max_companies'           => 1,
                    'plan_max_users'               => 3,
                    'plan_max_invoices'            => 50,
                    'plan_max_bills'               => 20,
                    'plan_max_products'            => 30,
                    'plan_max_warehouses'          => 1,
                    'plan_allows_advanced_reports' => 0,
                    'feature_list'                 => [
                        '1 Legal Business Entity / Branch',
                        'Up to 3 Active User Seats',
                        'Up to 50 Sales Invoices / month',
                        'Up to 20 Purchase Bills / month',
                        'Up to 30 Inventory Products',
                        'GST Returns & Tax Rate Engine',
                        'Customer & Vendor Ledgers',
                        'Cash & Bank Day Book Reports',
                    ],
                    'plan_badge'                   => 'Starter',
                    'plan_is_popular'              => 0,
                    'plan_is_active'               => 1,
                ],
                [
                    'plan_id'                      => 2,
                    'plan_name'                    => 'Growth Enterprise',
                    'plan_code'                    => 'growth_enterprise',
                    'plan_tagline'                 => 'Complete multi-user ERP with double-entry ledgers, purchase orders, inventory & financial statements.',
                    'plan_price_per_month'         => 1299.00,
                    'plan_price_per_year'          => 12990.00,
                    'plan_max_companies'           => 1,
                    'plan_max_users'               => 10,
                    'plan_max_invoices'            => 500,
                    'plan_max_bills'               => 250,
                    'plan_max_products'            => 500,
                    'plan_max_warehouses'          => 3,
                    'plan_allows_advanced_reports' => 1,
                    'feature_list'                 => [
                        '1 Legal Business Entity',
                        'Up to 10 Multi-Role User Seats',
                        'Up to 500 Sales Invoices / month',
                        'Up to 250 Purchase Bills / month',
                        'Up to 500 Inventory Products & 3 Warehouses',
                        'Full Double-Entry Journals & General Ledger',
                        'Real-Time Balance Sheet, P&L, Trial Balance',
                        'Stock Adjustments & Valuation',
                        'Automated Customer Aging & Receipts',
                    ],
                    'plan_badge'                   => 'Most Popular',
                    'plan_is_popular'              => 1,
                    'plan_is_active'               => 1,
                ],
            ];
        }

        foreach ($plans as &$plan) {
            $decoded = json_decode($plan['plan_features'] ?? '', true);
            $plan['feature_list'] = is_array($decoded) ? $decoded : [];
        }

        return $plans;
    }

    /**
     * Get all plans for Super Admin management (including inactive tiers)
     */
    public function getAllPlansAdmin(): array
    {
        $plans = $this->orderBy('plan_price_per_month', 'ASC')->findAll();

        foreach ($plans as &$plan) {
            $decoded = json_decode($plan['plan_features'] ?? '', true);
            $plan['feature_list'] = is_array($decoded) ? $decoded : [];
        }

        return $plans;
    }
}
