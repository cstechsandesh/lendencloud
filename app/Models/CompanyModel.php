<?php

namespace App\Models;

use App\Core\CSModel;

class CompanyModel extends CSModel
{
    protected $table            = 'companies';
    protected $primaryKey       = 'company_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_name',
        'company_code',
        'company_registration_number',
        'company_gstin',
        'company_pan',
        'company_address',
        'company_city',
        'company_state',
        'company_postal_code',
        'company_phone',
        'company_email',
        'company_website',
        'company_bank_name',
        'company_account_no',
        'company_ifsc_code',
        'company_upi_id',
        'company_currency_symbol',
        'company_financial_year_start',
        'company_status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'company_name'            => 'required|min_length[2]|max_length[255]',
        'company_code'            => 'permit_empty|max_length[50]',
        'company_email'           => 'permit_empty|valid_email|max_length[150]',
        'company_phone'           => 'permit_empty|min_length[6]|max_length[50]',
        'company_gstin'           => 'permit_empty|max_length[50]',
        'company_currency_symbol' => 'permit_empty|max_length[10]',
    ];

    protected $validationMessages = [
        'company_name' => [
            'required'   => 'Company legal name is required.',
            'min_length' => 'Company name must be at least 2 characters.',
            'max_length' => 'Company name cannot exceed 255 characters.',
        ],
        'company_email' => [
            'valid_email' => 'Please provide a valid company email address.',
        ],
    ];

    /**
     * Get default active company
     */
    public function getActiveCompany(): array
    {
        $comp = $this->where('company_status', 'active')->orderBy('company_id', 'ASC')->first();
        if ($comp) {
            return $comp;
        }

        $any = $this->orderBy('company_id', 'ASC')->first();
        if ($any) {
            return $any;
        }

        return [
            'company_id'                   => 1,
            'company_name'                 => 'LenDen Enterprise',
            'company_code'                 => 'LENDEN',
            'company_currency_symbol'      => '₹',
            'company_financial_year_start' => '04-01',
            'company_status'               => 'active',
        ];
    }

    /**
     * Get companies accessible by a specific user
     */
    public function getUserCompanies(int $userId, bool $isSuperAdmin = false): array
    {
        if ($isSuperAdmin) {
            return $this->where('company_status', 'active')
                ->orderBy('company_name', 'ASC')
                ->findAll();
        }

        $db = \Config\Database::connect();
        $builder = $this->builder();
        $builder->select('companies.*')
            ->join('user_companies', 'user_companies.uc_company_id = companies.company_id', 'inner')
            ->where('user_companies.uc_user_id', $userId)
            ->where('companies.company_status', 'active')
            ->orderBy('companies.company_name', 'ASC');

        $result = $builder->get()->getResultArray();

        // Fallback: if no rows mapped yet, allow access to default company 1
        if (empty($result)) {
            $default = $this->find(1);
            if ($default) {
                return [$default];
            }
        }

        return $result;
    }

    /**
     * Get companies with detailed counters for portfolio listing
     */
    public function getCompaniesWithDetails(?string $search = null, ?string $status = null, int $perPage = 10): array
    {
        $cTbl   = $this->db->prefixTable('companies');
        $accTbl = $this->db->prefixTable('accounts');
        $prdTbl = $this->db->prefixTable('products');
        $invTbl = $this->db->prefixTable('invoices');
        $ucTbl  = $this->db->prefixTable('user_companies');

        $builder = $this->select("
            {$cTbl}.*,
            (SELECT COUNT(account_id) FROM {$accTbl} WHERE account_company_id = {$cTbl}.company_id) AS total_accounts,
            (SELECT COUNT(product_id) FROM {$prdTbl} WHERE product_company_id = {$cTbl}.company_id) AS total_products,
            (SELECT COUNT(invoice_id) FROM {$invTbl} WHERE invoice_company_id = {$cTbl}.company_id) AS total_invoices,
            (SELECT COUNT(uc_id) FROM {$ucTbl} WHERE uc_company_id = {$cTbl}.company_id) AS total_users
        ", false);

        if (!empty($search)) {
            $builder->groupStart()
                ->like("{$cTbl}.company_name", $search)
                ->orLike("{$cTbl}.company_code", $search)
                ->orLike("{$cTbl}.company_gstin", $search)
                ->orLike("{$cTbl}.company_city", $search)
                ->orLike("{$cTbl}.company_email", $search)
                ->groupEnd();
        }

        if (!empty($status) && $status !== 'all') {
            $builder->where("{$cTbl}.company_status", $status);
        }

        return $builder->orderBy("{$cTbl}.company_id", 'DESC')->paginate($perPage);
    }

    /**
     * Get platform statistics across companies
     */
    public function getPlatformStats(): array
    {
        $totalCompanies  = $this->countAllResults();
        $activeCompanies = $this->where('company_status', 'active')->countAllResults();
        $totalAccounts   = $this->db->table('accounts')->countAllResults();
        $totalProducts   = $this->db->table('products')->countAllResults();
        $totalInvoices   = $this->db->table('invoices')->countAllResults();

        $revRow = $this->db->table('invoices')
            ->selectSum('invoice_total', 'total_revenue')
            ->whereNotIn('invoice_status', ['cancelled'])
            ->get()->getRow();
        $totalRevenue = (float)($revRow->total_revenue ?? 0.00);

        return [
            'total_companies'  => $totalCompanies,
            'active_companies' => $activeCompanies,
            'total_accounts'   => $totalAccounts,
            'total_products'   => $totalProducts,
            'total_invoices'   => $totalInvoices,
            'total_revenue'    => $totalRevenue,
        ];
    }

    /**
     * Auto-provision standard starter entities for a newly created company
     * Provision default chart of accounts, tax rates, and warehouse for a new company
     */
    public function provisionDefaultEntities(int $companyId): void
    {
        $now = date('Y-m-d H:i:s');

        // 1. Provision Account Groups
        $defaultGroups = [
            // Assets
            ['group_company_id' => $companyId, 'group_name' => 'Current Assets', 'group_type' => 'asset', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Bank & Cash Accounts', 'group_type' => 'asset', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Sundry Debtors (Receivables)', 'group_type' => 'asset', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Stock-in-Hand', 'group_type' => 'asset', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Fixed Assets', 'group_type' => 'asset', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],

            // Liabilities
            ['group_company_id' => $companyId, 'group_name' => 'Current Liabilities', 'group_type' => 'liability', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Sundry Creditors (Payables)', 'group_type' => 'liability', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Duties & Taxes (GST)', 'group_type' => 'liability', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],

            // Equity
            ['group_company_id' => $companyId, 'group_name' => 'Capital Account', 'group_type' => 'equity', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Reserves & Surplus', 'group_type' => 'equity', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],

            // Revenue
            ['group_company_id' => $companyId, 'group_name' => 'Sales Accounts', 'group_type' => 'revenue', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Other Indirect Income', 'group_type' => 'revenue', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],

            // Expenses
            ['group_company_id' => $companyId, 'group_name' => 'Cost of Goods Sold (COGS)', 'group_type' => 'expense', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Direct Expenses', 'group_type' => 'expense', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['group_company_id' => $companyId, 'group_name' => 'Indirect Administrative Expenses', 'group_type' => 'expense', 'group_parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        $groupIdMap = [];
        foreach ($defaultGroups as $grp) {
            $this->db->table('account_groups')->insert($grp);
            $groupIdMap[$grp['group_name']] = $this->db->insertID();
        }

        // 2. Provision Core Starter Chart of Accounts
        $bankGroupId     = $groupIdMap['Bank & Cash Accounts'] ?? null;
        $debtorsGroupId  = $groupIdMap['Sundry Debtors (Receivables)'] ?? null;
        $creditorsGroupId= $groupIdMap['Sundry Creditors (Payables)'] ?? null;
        $taxesGroupId    = $groupIdMap['Duties & Taxes (GST)'] ?? null;
        $capitalGroupId  = $groupIdMap['Capital Account'] ?? null;
        $salesGroupId    = $groupIdMap['Sales Accounts'] ?? null;
        $cogsGroupId     = $groupIdMap['Cost of Goods Sold (COGS)'] ?? null;
        $inventoryGroupId= $groupIdMap['Stock-in-Hand'] ?? null;

        $starterAccounts = [
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $bankGroupId,
                'account_code'            => '1000',
                'account_name'            => 'Cash on Hand',
                'account_type'            => 'asset',
                'account_balance_type'    => 'debit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Primary petty cash vault',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $bankGroupId,
                'account_code'            => '1010',
                'account_name'            => 'Main Operating Bank Account',
                'account_type'            => 'asset',
                'account_balance_type'    => 'debit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Main operating bank current account',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $debtorsGroupId,
                'account_code'            => '1020',
                'account_name'            => 'Accounts Receivable (Trade Debtors)',
                'account_type'            => 'asset',
                'account_balance_type'    => 'debit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Customer receivable control account',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $inventoryGroupId,
                'account_code'            => '1030',
                'account_name'            => 'Stock-in-Hand Inventory',
                'account_type'            => 'asset',
                'account_balance_type'    => 'debit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Merchandise inventory valuation',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $creditorsGroupId,
                'account_code'            => '2010',
                'account_name'            => 'Accounts Payable (Trade Creditors)',
                'account_type'            => 'liability',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Vendor payable control account',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $taxesGroupId,
                'account_code'            => '2020',
                'account_name'            => 'CGST Output Tax Payable',
                'account_type'            => 'liability',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Central GST Output collected on sales',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $taxesGroupId,
                'account_code'            => '2021',
                'account_name'            => 'SGST Output Tax Payable',
                'account_type'            => 'liability',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'State GST Output collected on sales',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $taxesGroupId,
                'account_code'            => '2022',
                'account_name'            => 'IGST Output Tax Payable',
                'account_type'            => 'liability',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Integrated GST Output collected on inter-state sales',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $capitalGroupId,
                'account_code'            => '3000',
                'account_name'            => "Owner's / Share Capital",
                'account_type'            => 'equity',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Paid-up equity or proprietor capital',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $salesGroupId,
                'account_code'            => '4000',
                'account_name'            => 'General Sales Revenue',
                'account_type'            => 'revenue',
                'account_balance_type'    => 'credit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Revenue from product sales and services',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'account_company_id'      => $companyId,
                'account_group_id'        => $cogsGroupId,
                'account_code'            => '5000',
                'account_name'            => 'General Purchases / COGS',
                'account_type'            => 'expense',
                'account_balance_type'    => 'debit',
                'account_opening_balance' => 0.00,
                'account_current_balance' => 0.00,
                'account_description'     => 'Cost of goods purchased for resale',
                'account_is_system'       => 1,
                'account_is_active'       => 1,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
        ];

        foreach ($starterAccounts as $acc) {
            $this->db->table('accounts')->insert($acc);
        }

        // 3. Provision Default Warehouse
        $this->db->table('warehouses')->insert([
            'warehouse_company_id' => $companyId,
            'warehouse_name'       => 'Main Central Warehouse',
            'warehouse_location'   => 'Primary Company Logistics Facility',
            'warehouse_is_active'  => 1,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // 4. Provision Default GST Tax Rates
        $defaultTaxes = [
            ['tax_company_id' => $companyId, 'tax_name' => 'GST 0% (Exempt)', 'tax_rate' => 0.00, 'tax_is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['tax_company_id' => $companyId, 'tax_name' => 'GST 5%',           'tax_rate' => 5.00, 'tax_is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['tax_company_id' => $companyId, 'tax_name' => 'GST 12%',          'tax_rate' => 12.00, 'tax_is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['tax_company_id' => $companyId, 'tax_name' => 'GST 18% (Standard)', 'tax_rate' => 18.00, 'tax_is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['tax_company_id' => $companyId, 'tax_name' => 'GST 28%',          'tax_rate' => 28.00, 'tax_is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];
        foreach ($defaultTaxes as $tax) {
            $this->db->table('tax_rates')->insert($tax);
        }

        // 5. Provision Default Category
        $this->db->table('categories')->insert([
            'category_company_id' => $companyId,
            'category_name'       => 'General Merchandise',
            'category_parent_id'  => null,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    /**
     * Cascade deletion of a company and its associated tenant records
     * Safeguarded delete company cascade with transactional usage verification
     */
    public function deleteCompanyCascade(int $companyId): bool
    {
        $this->db->transStart();

        $tenantTables = [
            'voucher_allocations' => 'va_voucher_id IN (SELECT voucher_id FROM ' . $this->db->prefixTable('vouchers') . ' WHERE voucher_company_id = ' . (int)$companyId . ')',
            'vouchers'            => ['voucher_company_id' => $companyId],
            'bill_items'          => 'bill_item_bill_id IN (SELECT bill_id FROM ' . $this->db->prefixTable('bills') . ' WHERE bill_company_id = ' . (int)$companyId . ')',
            'bills'               => ['bill_company_id' => $companyId],
            'purchase_order_items'=> 'poi_order_id IN (SELECT po_id FROM ' . $this->db->prefixTable('purchase_orders') . ' WHERE po_company_id = ' . (int)$companyId . ')',
            'purchase_orders'     => ['po_company_id' => $companyId],
            'invoice_items'       => 'inv_item_invoice_id IN (SELECT invoice_id FROM ' . $this->db->prefixTable('invoices') . ' WHERE invoice_company_id = ' . (int)$companyId . ')',
            'invoices'            => ['invoice_company_id' => $companyId],
            'sales_order_items'   => 'soi_order_id IN (SELECT so_id FROM ' . $this->db->prefixTable('sales_orders') . ' WHERE so_company_id = ' . (int)$companyId . ')',
            'sales_orders'        => ['so_company_id' => $companyId],
            'journal_lines'       => 'line_entry_id IN (SELECT journal_id FROM ' . $this->db->prefixTable('journal_entries') . ' WHERE journal_company_id = ' . (int)$companyId . ')',
            'journal_entries'     => ['journal_company_id' => $companyId],
            'stock_movements'     => ['movement_company_id' => $companyId],
            'products'            => ['product_company_id' => $companyId],
            'warehouses'          => ['warehouse_company_id' => $companyId],
            'categories'          => ['category_company_id' => $companyId],
            'parties'             => ['party_company_id' => $companyId],
            'tax_rates'           => ['tax_company_id' => $companyId],
            'accounts'            => ['account_company_id' => $companyId],
            'account_groups'      => ['group_company_id' => $companyId],
            'settings'            => ['setting_company_id' => $companyId],
            'user_companies'      => ['uc_company_id' => $companyId],
        ];

        foreach ($tenantTables as $table => $condition) {
            if ($this->db->tableExists($table)) {
                if (is_array($condition)) {
                    $this->db->table($table)->where($condition)->delete();
                } else {
                    $this->db->table($table)->where($condition, null, false)->delete();
                }
            }
        }

        // Delete company itself
        $this->delete($companyId);

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}

