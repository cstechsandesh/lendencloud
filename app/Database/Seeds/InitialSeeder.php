<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        // =====================================================================
        // 1. Seed Default Primary Company (Tenant Entity 1)
        // =====================================================================
        $existingCompany = $db->table('companies')->where('company_id', 1)->get()->getRowArray();
        if (!$existingCompany) {
            $companyData = [
                'company_id'                   => 1,
                'company_name'                 => 'LenDen Enterprise Solutions Pvt Ltd',
                'company_code'                 => 'LENDEN',
                'company_gstin'                => '27AABCL1234M1Z8',
                'company_pan'                  => 'AABCL1234M',
                'company_email'                => 'info@lenden.local',
                'company_phone'                => '+91 (022) 2890-4400',
                'company_address'              => 'Suite 500, FinTech Plaza, Andheri East, Mumbai 400069',
                'company_city'                 => 'Mumbai',
                'company_state'                => 'Maharashtra',
                'company_postal_code'          => '400069',
                'company_bank_name'            => 'HDFC Bank Ltd',
                'company_account_no'           => '50200012345678',
                'company_ifsc_code'            => 'HDFC0000123',
                'company_currency_symbol'      => '₹',
                'company_financial_year_start' => '04-01',
                'company_status'               => 'active',
                'created_at'                   => $now,
                'updated_at'                   => $now,
            ];
            $db->table('companies')->insert($companyData);
        }
        $companyId = 1;

        // =====================================================================
        // 2. Seed System & Business Roles
        // =====================================================================
        $roles = [
            [
                'role_key'         => 'super_admin',
                'role_name'        => 'Super Administrator',
                'role_description' => 'Unrestricted master platform access: cross-tenant management, pricing tiers, platform settings, and system RBAC control.',
                'role_badge_class' => 'bg-danger',
                'role_is_system'   => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'admin',
                'role_name'        => 'Business Administrator',
                'role_description' => 'Full administrative access across company sales, purchases, inventory, accounts, and company operators.',
                'role_badge_class' => 'bg-primary',
                'role_is_system'   => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'accountant',
                'role_name'        => 'Chief Accountant',
                'role_description' => 'Access to Chart of Accounts, Journal Vouchers, Payments, Receipts, and Financial Reports.',
                'role_badge_class' => 'bg-success',
                'role_is_system'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'sales_manager',
                'role_name'        => 'Sales Manager',
                'role_description' => 'Manages customers, quotations, sales orders, invoices, and customer receipts.',
                'role_badge_class' => 'bg-info text-dark',
                'role_is_system'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'purchase_manager',
                'role_name'        => 'Purchase Manager',
                'role_description' => 'Manages vendors, purchase orders, vendor bills, and vendor payment vouchers.',
                'role_badge_class' => 'bg-warning text-dark',
                'role_is_system'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'inventory_manager',
                'role_name'        => 'Inventory & Stock Manager',
                'role_description' => 'Controls product catalog, warehouses, stock transfers, and physical stock adjustments.',
                'role_badge_class' => 'bg-dark',
                'role_is_system'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'role_key'         => 'viewer',
                'role_name'        => 'Auditor / Viewer',
                'role_description' => 'Read-only access for auditing ledgers, invoices, stock, and general company reports.',
                'role_badge_class' => 'bg-secondary',
                'role_is_system'   => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($roles as $r) {
            $existing = $db->table('roles')->where('role_key', $r['role_key'])->get()->getRowArray();
            if (!$existing) {
                $db->table('roles')->insert($r);
            }
        }

        // =====================================================================
        // 3. Seed Permissions Catalog (Separating Platform vs Tenant Permissions)
        // =====================================================================
        $modules = [
            'dashboard' => [
                'view' => 'Access executive dashboard and key metrics',
            ],
            'accounts' => [
                'view'   => 'View chart of accounts and balances',
                'create' => 'Create new ledger accounts and groups',
                'edit'   => 'Modify existing accounts',
                'delete' => 'Delete non-system accounts',
                'ledger' => 'View detailed account ledger statements',
            ],
            'journals' => [
                'view'   => 'View journal entries and audit trails',
                'create' => 'Post new double-entry journal vouchers',
            ],
            'parties' => [
                'view'   => 'View customer and vendor profiles',
                'create' => 'Register new customers or vendors',
                'edit'   => 'Update party contact and billing info',
                'delete' => 'Remove or deactivate parties',
            ],
            'inventory' => [
                'view'   => 'View products, categories and stock levels',
                'create' => 'Add new products and warehouses',
                'edit'   => 'Modify product catalog and prices',
                'delete' => 'Remove product listings',
                'adjust' => 'Post stock physical count adjustments',
            ],
            'sales' => [
                'view'   => 'View sales orders and customer invoices',
                'create' => 'Create sales orders and generate invoices',
                'edit'   => 'Edit draft sales orders and invoices',
                'cancel' => 'Cancel and reverse invoices',
                'print'  => 'Print and export invoice PDFs',
            ],
            'purchases' => [
                'view'   => 'View purchase orders and vendor bills',
                'create' => 'Create purchase orders and record bills',
                'edit'   => 'Edit draft purchase orders and bills',
                'cancel' => 'Cancel and reverse bills',
            ],
            'vouchers' => [
                'view'   => 'View payment, receipt, and contra vouchers',
                'create' => 'Record customer receipts and vendor payments',
                'contra' => 'Record internal bank/cash contra transfers',
            ],
            'reports' => [
                'view'   => 'View trial balance, P&L, balance sheet, and day book',
                'export' => 'Export financial statements to Excel/PDF',
            ],
            'users' => [
                'view'   => 'View company user accounts',
                'create' => 'Create new company users',
                'edit'   => 'Update user profiles and roles',
                'delete' => 'Deactivate or remove company users',
            ],
            'companies' => [
                'view'   => 'View multi-company portfolio and list',
                'create' => 'Create new independent company entity',
                'edit'   => 'Update company details and bank info',
                'delete' => 'Platform: Delete company from portfolio',
                'switch' => 'Switch active company workspace context',
            ],
            'settings' => [
                'view' => 'View organization settings and tax rates',
                'edit' => 'Configure company details and tax rates',
            ],
            // Platform Super Admin Exclusive Permissions
            'roles' => [
                'view'   => 'Platform: View system RBAC roles and permissions',
                'create' => 'Platform: Create custom access roles',
                'edit'   => 'Platform: Edit role privileges and assignments',
                'delete' => 'Platform: Delete custom roles',
            ],
            'subscriptions' => [
                'view'   => 'Platform: View all tenant subscriptions and billing status',
                'manage' => 'Platform: Manage SaaS pricing plans and subscription overrides',
            ],
        ];

        foreach ($modules as $mod => $actions) {
            foreach ($actions as $act => $desc) {
                $pKey = "{$mod}.{$act}";
                $existing = $db->table('permissions')->where('permission_key', $pKey)->get()->getRowArray();
                if (!$existing) {
                    $db->table('permissions')->insert([
                        'permission_module'      => $mod,
                        'permission_key'         => $pKey,
                        'permission_name'        => ucwords(str_replace('_', ' ', $mod)) . ' ' . ucfirst($act),
                        'permission_description' => $desc,
                        'created_at'             => $now,
                    ]);
                }
            }
        }

        // =====================================================================
        // 4. Assign Strict Multi-Tenant Role Permissions
        // =====================================================================
        $allPerms = $db->table('permissions')->get()->getResultArray();
        $permMap  = [];
        foreach ($allPerms as $p) {
            $permMap[$p['permission_key']] = (int)$p['permission_id'];
        }

        $allRoles = $db->table('roles')->get()->getResultArray();
        $roleMap  = [];
        foreach ($allRoles as $r) {
            $roleMap[$r['role_key']] = (int)$r['role_id'];
        }

        // Platform-only permissions strictly reserved for super_admin
        $platformOnlyPerms = [
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'subscriptions.view',
            'subscriptions.manage',
            'companies.delete',
        ];

        // All non-platform permissions for tenant admin
        $tenantAdminPerms = array_values(array_diff(array_keys($permMap), $platformOnlyPerms));

        $rolePermRules = [
            // Super Admin gets EVERYTHING
            'super_admin' => array_keys($permMap),

            // Tenant Business Administrator: Full company management, NO platform controls
            'admin' => $tenantAdminPerms,

            // Chief Accountant: Ledger, journals, payments, receipts, and reporting
            'accountant' => [
                'dashboard.view',
                'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.ledger',
                'journals.view', 'journals.create',
                'vouchers.view', 'vouchers.create', 'vouchers.contra',
                'reports.view', 'reports.export',
                'parties.view',
                'sales.view', 'sales.print',
                'purchases.view',
                'inventory.view',
            ],

            // Sales Manager
            'sales_manager' => [
                'dashboard.view',
                'sales.view', 'sales.create', 'sales.edit', 'sales.cancel', 'sales.print',
                'parties.view', 'parties.create', 'parties.edit',
                'vouchers.view', 'vouchers.create',
                'inventory.view',
                'reports.view',
            ],

            // Purchase Manager
            'purchase_manager' => [
                'dashboard.view',
                'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.cancel',
                'parties.view', 'parties.create', 'parties.edit',
                'vouchers.view', 'vouchers.create',
                'inventory.view',
                'reports.view',
            ],

            // Inventory Manager
            'inventory_manager' => [
                'dashboard.view',
                'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust',
                'reports.view',
            ],

            // Viewer / Auditor (Strictly company read-only, NO platform permissions)
            'viewer' => [
                'dashboard.view',
                'accounts.view', 'accounts.ledger',
                'journals.view',
                'parties.view',
                'inventory.view',
                'sales.view', 'sales.print',
                'purchases.view',
                'vouchers.view',
                'reports.view', 'reports.export',
                'companies.view',
                'settings.view',
            ],
        ];

        foreach ($rolePermRules as $roleKey => $permKeys) {
            if (!isset($roleMap[$roleKey])) continue;
            $rId = $roleMap[$roleKey];

            foreach ($permKeys as $pk) {
                if (!isset($permMap[$pk])) continue;
                $pId = $permMap[$pk];

                $exists = $db->table('role_permissions')
                    ->where('rp_role_id', $rId)
                    ->where('rp_permission_id', $pId)
                    ->countAllResults();

                if (!$exists) {
                    $db->table('role_permissions')->insert([
                        'rp_role_id'       => $rId,
                        'rp_permission_id' => $pId,
                        'created_at'       => $now,
                    ]);
                }
            }
        }

        // =====================================================================
        // 5. Seed Users & Multi-Tenant Company Linkages
        // =====================================================================
        $seedUsers = [
            [
                'role_key'    => 'super_admin',
                'username'    => 'admin',
                'email'       => 'admin@lenden.local',
                'name'        => 'Super Administrator',
                'phone'       => '9876543210',
                'password'    => 'admin123',
                'designation' => 'Platform Managing Director',
                'is_demo'     => 0,
            ],
            [
                'role_key'    => 'admin',
                'username'    => 'admin_demo',
                'email'       => 'admin@lenden.com',
                'name'        => 'Demo Business Administrator',
                'phone'       => '9876543202',
                'password'    => 'admin123',
                'designation' => 'General Business Manager',
                'is_demo'     => 1,
            ],
            [
                'role_key'    => 'accountant',
                'username'    => 'accountant',
                'email'       => 'accountant@lenden.com',
                'name'        => 'Demo Chief Accountant',
                'phone'       => '9876543203',
                'password'    => 'accountant123',
                'designation' => 'Chief Accountant & Head of Finance',
                'is_demo'     => 1,
            ],
            [
                'role_key'    => 'sales_manager',
                'username'    => 'sales',
                'email'       => 'sales@lenden.com',
                'name'        => 'Demo Sales Manager',
                'phone'       => '9876543204',
                'password'    => 'sales123',
                'designation' => 'Head of Sales & Invoicing',
                'is_demo'     => 1,
            ],
            [
                'role_key'    => 'purchase_manager',
                'username'    => 'purchase',
                'email'       => 'purchase@lenden.com',
                'name'        => 'Demo Purchase Manager',
                'phone'       => '9876543205',
                'password'    => 'purchase123',
                'designation' => 'Procurement & Supply Chain Lead',
                'is_demo'     => 1,
            ],
            [
                'role_key'    => 'inventory_manager',
                'username'    => 'inventory',
                'email'       => 'inventory@lenden.com',
                'name'        => 'Demo Inventory Manager',
                'phone'       => '9876543206',
                'password'    => 'inventory123',
                'designation' => 'Warehouse & Stock Controller',
                'is_demo'     => 1,
            ],
            [
                'role_key'    => 'viewer',
                'username'    => 'viewer',
                'email'       => 'viewer@lenden.com',
                'name'        => 'Demo Auditor / Viewer',
                'phone'       => '9876543207',
                'password'    => 'viewer123',
                'designation' => 'Statutory Auditor',
                'is_demo'     => 1,
            ],
        ];

        $superAdminUserId = 1;

        foreach ($seedUsers as $su) {
            $rId = $roleMap[$su['role_key']] ?? $roleMap['admin'];
            $existing = $db->table('users')->where('user_username', $su['username'])->get()->getRowArray();

            if (!$existing) {
                $db->table('users')->insert([
                    'user_company_id'    => $companyId,
                    'user_role_id'       => $rId,
                    'user_role'          => $su['role_key'],
                    'user_username'      => $su['username'],
                    'user_email'         => $su['email'],
                    'user_name'          => $su['name'],
                    'user_phone'         => $su['phone'],
                    'user_password_hash' => password_hash($su['password'], PASSWORD_BCRYPT),
                    'user_designation'   => $su['designation'],
                    'user_status'        => 'active',
                    'user_is_demo'       => $su['is_demo'],
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $uId = $db->insertID();
            } else {
                $uId = (int)$existing['user_id'];
            }

            if ($su['role_key'] === 'super_admin') {
                $superAdminUserId = $uId;
            }

            // Link in user_roles
            $urExists = $db->table('user_roles')
                ->where('ur_user_id', $uId)
                ->where('ur_role_id', $rId)
                ->countAllResults();
            if (!$urExists) {
                $db->table('user_roles')->insert([
                    'ur_user_id' => $uId,
                    'ur_role_id' => $rId,
                    'created_at' => $now,
                ]);
            }

            // Link in user_companies
            $ucExists = $db->table('user_companies')
                ->where('uc_user_id', $uId)
                ->where('uc_company_id', $companyId)
                ->countAllResults();
            if (!$ucExists) {
                $db->table('user_companies')->insert([
                    'uc_user_id'    => $uId,
                    'uc_company_id' => $companyId,
                    'created_at'    => $now,
                ]);
            }
        }

        // =====================================================================
        // 6. Seed Account Groups (Company Scoped)
        // =====================================================================
        $groupDefs = [
            // Assets
            ['name' => 'Current Assets',                    'type' => 'asset',     'parent' => null],
            ['name' => 'Bank & Cash Accounts',              'type' => 'asset',     'parent' => 'Current Assets'],
            ['name' => 'Sundry Debtors (Receivables)',      'type' => 'asset',     'parent' => 'Current Assets'],
            ['name' => 'Stock-in-Hand',                     'type' => 'asset',     'parent' => 'Current Assets'],
            ['name' => 'Fixed Assets',                      'type' => 'asset',     'parent' => null],

            // Liabilities
            ['name' => 'Current Liabilities',               'type' => 'liability', 'parent' => null],
            ['name' => 'Sundry Creditors (Payables)',       'type' => 'liability', 'parent' => 'Current Liabilities'],
            ['name' => 'Duties & Taxes (GST)',              'type' => 'liability', 'parent' => 'Current Liabilities'],

            // Equity
            ['name' => 'Capital Account',                   'type' => 'equity',    'parent' => null],
            ['name' => 'Reserves & Surplus',                'type' => 'equity',    'parent' => null],

            // Revenue
            ['name' => 'Sales Accounts',                    'type' => 'revenue',   'parent' => null],
            ['name' => 'Other Indirect Income',             'type' => 'revenue',   'parent' => null],

            // Expenses
            ['name' => 'Cost of Goods Sold (COGS)',         'type' => 'expense',   'parent' => null],
            ['name' => 'Direct Expenses',                   'type' => 'expense',   'parent' => null],
            ['name' => 'Indirect Administrative Expenses',  'type' => 'expense',   'parent' => null],
        ];

        $groupMap = [];
        foreach ($groupDefs as $gd) {
            $existing = $db->table('account_groups')
                ->where('group_company_id', $companyId)
                ->where('group_name', $gd['name'])
                ->get()->getRowArray();

            if (!$existing) {
                $parentId = (!empty($gd['parent']) && isset($groupMap[$gd['parent']])) ? $groupMap[$gd['parent']] : null;
                $db->table('account_groups')->insert([
                    'group_company_id' => $companyId,
                    'group_name'       => $gd['name'],
                    'group_type'       => $gd['type'],
                    'group_parent_id'  => $parentId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $groupMap[$gd['name']] = (int)$db->insertID();
            } else {
                $groupMap[$gd['name']] = (int)$existing['group_id'];
            }
        }

        // =====================================================================
        // 7. Seed Chart of Accounts (Company Scoped & Dynamically Linked)
        // =====================================================================
        $accountDefs = [
            [
                'group'        => 'Bank & Cash Accounts',
                'code'         => '1000',
                'name'         => 'Cash on Hand',
                'type'         => 'asset',
                'balance_type' => 'debit',
                'op_bal'       => 25000.00,
                'desc'         => 'Main petty cash vault',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Bank & Cash Accounts',
                'code'         => '1010',
                'name'         => 'Main Operating Bank Account',
                'type'         => 'asset',
                'balance_type' => 'debit',
                'op_bal'       => 150000.00,
                'desc'         => 'HDFC Bank Current Account',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Sundry Debtors (Receivables)',
                'code'         => '1100',
                'name'         => 'Accounts Receivable (Debtors Control)',
                'type'         => 'asset',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Master control account for trade customer receivables',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Stock-in-Hand',
                'code'         => '1200',
                'name'         => 'Stock-in-Hand (Inventory Asset)',
                'type'         => 'asset',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Valuation of on-hand inventory',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Duties & Taxes (GST)',
                'code'         => '1300',
                'name'         => 'GST Input Tax Credit (ITC)',
                'type'         => 'asset',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Input tax paid on purchases to be set off against output GST',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Sundry Creditors (Payables)',
                'code'         => '2000',
                'name'         => 'Accounts Payable (Creditors Control)',
                'type'         => 'liability',
                'balance_type' => 'credit',
                'op_bal'       => 0.00,
                'desc'         => 'Master control account for trade vendor payables',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Duties & Taxes (GST)',
                'code'         => '2100',
                'name'         => 'GST Output Tax Payable',
                'type'         => 'liability',
                'balance_type' => 'credit',
                'op_bal'       => 0.00,
                'desc'         => 'Tax collected on sales to be remitted to government',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Capital Account',
                'code'         => '3000',
                'name'         => "Owner's Capital",
                'type'         => 'equity',
                'balance_type' => 'credit',
                'op_bal'       => 175000.00,
                'desc'         => 'Initial equity investment into business',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Reserves & Surplus',
                'code'         => '3100',
                'name'         => 'Retained Earnings',
                'type'         => 'equity',
                'balance_type' => 'credit',
                'op_bal'       => 0.00,
                'desc'         => 'Cumulative net profit or loss carried forward',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Sales Accounts',
                'code'         => '4000',
                'name'         => 'Sales Revenue',
                'type'         => 'revenue',
                'balance_type' => 'credit',
                'op_bal'       => 0.00,
                'desc'         => 'Income derived from goods and services sales',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Cost of Goods Sold (COGS)',
                'code'         => '5000',
                'name'         => 'Cost of Goods Sold (COGS)',
                'type'         => 'expense',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Direct cost attributable to the production of goods sold',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Direct Expenses',
                'code'         => '5100',
                'name'         => 'Purchase Expenses',
                'type'         => 'expense',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Direct purchases of goods and raw materials',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Indirect Administrative Expenses',
                'code'         => '5200',
                'name'         => 'Stock Adjustment / Loss Expense',
                'type'         => 'expense',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Variance expense from physical stock counts and write-offs',
                'is_system'    => 1,
            ],
            [
                'group'        => 'Indirect Administrative Expenses',
                'code'         => '5300',
                'name'         => 'General & Administrative Expenses',
                'type'         => 'expense',
                'balance_type' => 'debit',
                'op_bal'       => 0.00,
                'desc'         => 'Office rent, utilities, internet, and office expenses',
                'is_system'    => 0,
            ],
        ];

        $accountMap = [];
        foreach ($accountDefs as $ad) {
            $existing = $db->table('accounts')
                ->where('account_company_id', $companyId)
                ->where('account_code', $ad['code'])
                ->get()->getRowArray();

            $gId = $groupMap[$ad['group']] ?? null;

            if (!$existing) {
                $db->table('accounts')->insert([
                    'account_company_id'      => $companyId,
                    'account_group_id'        => $gId,
                    'account_code'            => $ad['code'],
                    'account_name'            => $ad['name'],
                    'account_type'            => $ad['type'],
                    'account_balance_type'    => $ad['balance_type'],
                    'account_opening_balance' => $ad['op_bal'],
                    'account_current_balance' => $ad['op_bal'],
                    'account_description'     => $ad['desc'],
                    'account_is_system'       => $ad['is_system'],
                    'account_is_active'       => 1,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);
                $accountMap[$ad['code']] = (int)$db->insertID();
            } else {
                $accountMap[$ad['code']] = (int)$existing['account_id'];
            }
        }

        // =====================================================================
        // 8. Seed GST Tax Rates (Company Scoped)
        // =====================================================================
        $taxDefs = [
            ['name' => 'GST 0% (Exempt)', 'rate' => 0.00],
            ['name' => 'GST 5%',          'rate' => 5.00],
            ['name' => 'GST 12%',         'rate' => 12.00],
            ['name' => 'GST 18%',         'rate' => 18.00],
            ['name' => 'GST 28%',         'rate' => 28.00],
        ];

        $taxMap = [];
        foreach ($taxDefs as $td) {
            $existing = $db->table('tax_rates')
                ->where('tax_company_id', $companyId)
                ->where('tax_name', $td['name'])
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('tax_rates')->insert([
                    'tax_company_id' => $companyId,
                    'tax_name'       => $td['name'],
                    'tax_rate'       => $td['rate'],
                    'tax_is_active'  => 1,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $taxMap[$td['name']] = (int)$db->insertID();
            } else {
                $taxMap[$td['name']] = (int)$existing['tax_id'];
            }
        }

        // =====================================================================
        // 9. Seed Product Categories (Company Scoped)
        // =====================================================================
        $catDefs = [
            'Electronics & Hardware',
            'Packaging Materials',
            'Finished Goods',
            'Professional Services',
        ];

        $catMap = [];
        foreach ($catDefs as $cName) {
            $existing = $db->table('categories')
                ->where('category_company_id', $companyId)
                ->where('category_name', $cName)
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('categories')->insert([
                    'category_company_id' => $companyId,
                    'category_name'       => $cName,
                    'category_parent_id'  => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
                $catMap[$cName] = (int)$db->insertID();
            } else {
                $catMap[$cName] = (int)$existing['category_id'];
            }
        }

        // =====================================================================
        // 10. Seed Warehouses (Company Scoped)
        // =====================================================================
        $warehouseDefs = [
            ['name' => 'Main Central Warehouse', 'loc' => 'Ground Floor, Plot 42, Industrial Zone'],
            ['name' => 'Retail Shop Outlet',     'loc' => 'Shop #12, Market Avenue'],
        ];

        $warehouseMap = [];
        foreach ($warehouseDefs as $wd) {
            $existing = $db->table('warehouses')
                ->where('warehouse_company_id', $companyId)
                ->where('warehouse_name', $wd['name'])
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('warehouses')->insert([
                    'warehouse_company_id' => $companyId,
                    'warehouse_name'       => $wd['name'],
                    'warehouse_location'   => $wd['loc'],
                    'warehouse_is_active'  => 1,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
                $warehouseMap[$wd['name']] = (int)$db->insertID();
            } else {
                $warehouseMap[$wd['name']] = (int)$existing['warehouse_id'];
            }
        }

        // =====================================================================
        // 11. Seed Products & Opening Stock (Company Scoped & Dynamically Linked)
        // =====================================================================
        $mainWarehouseId = $warehouseMap['Main Central Warehouse'] ?? 1;
        $tax18Id         = $taxMap['GST 18%'] ?? ($taxMap['GST 12%'] ?? null);
        $tax12Id         = $taxMap['GST 12%'] ?? null;
        $catElectronics  = $catMap['Electronics & Hardware'] ?? null;
        $catPackaging    = $catMap['Packaging Materials'] ?? null;

        $productDefs = [
            [
                'code'        => 'PRD-001',
                'name'        => 'Wireless Barcode Scanner',
                'desc'        => '2.4GHz handheld laser barcode reader with USB cradle',
                'hsn'         => '847160',
                'unit'        => 'Nos',
                'cost'        => 1800.00,
                'price'       => 2999.00,
                'stock'       => 50.000,
                'reorder'     => 10.000,
                'category_id' => $catElectronics,
                'tax_rate_id' => $tax18Id,
            ],
            [
                'code'        => 'PRD-002',
                'name'        => 'Thermal Receipt Printer (80mm)',
                'desc'        => 'High-speed POS thermal printer with Auto-Cutter and Ethernet',
                'hsn'         => '844332',
                'unit'        => 'Nos',
                'cost'        => 3200.00,
                'price'       => 5499.00,
                'stock'       => 30.000,
                'reorder'     => 5.000,
                'category_id' => $catElectronics,
                'tax_rate_id' => $tax18Id,
            ],
            [
                'code'        => 'PRD-003',
                'name'        => 'Thermal Paper Roll 80mm (Pack of 50)',
                'desc'        => 'BPA-free high-sensitivity thermal paper rolls',
                'hsn'         => '481190',
                'unit'        => 'Pack',
                'cost'        => 850.00,
                'price'       => 1450.00,
                'stock'       => 100.000,
                'reorder'     => 20.000,
                'category_id' => $catPackaging,
                'tax_rate_id' => $tax12Id,
            ],
        ];

        foreach ($productDefs as $pd) {
            $existing = $db->table('products')
                ->where('product_company_id', $companyId)
                ->where('product_code', $pd['code'])
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('products')->insert([
                    'product_company_id'     => $companyId,
                    'product_category_id'    => $pd['category_id'],
                    'product_code'           => $pd['code'],
                    'product_name'           => $pd['name'],
                    'product_description'    => $pd['desc'],
                    'product_hsn_code'       => $pd['hsn'],
                    'product_unit'           => $pd['unit'],
                    'product_purchase_price' => $pd['cost'],
                    'product_selling_price'  => $pd['price'],
                    'product_tax_rate_id'    => $pd['tax_rate_id'],
                    'product_current_stock'  => $pd['stock'],
                    'product_reorder_level'  => $pd['reorder'],
                    'product_is_active'      => 1,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);
                $pId = (int)$db->insertID();

                // Record opening stock movement with super admin user id
                $db->table('stock_movements')->insert([
                    'movement_company_id'  => $companyId,
                    'stock_product_id'     => $pId,
                    'stock_warehouse_id'   => $mainWarehouseId,
                    'stock_type'           => 'in',
                    'stock_quantity'       => $pd['stock'],
                    'stock_unit_cost'      => $pd['cost'],
                    'stock_reference_type' => 'opening_stock',
                    'stock_reference_id'   => null,
                    'stock_notes'          => 'Opening stock inventory batch',
                    'stock_date'           => $now,
                    'stock_created_by'     => $superAdminUserId,
                    'created_at'           => $now,
                ]);
            }
        }

        // =====================================================================
        // 12. Seed Customer & Vendor Parties (Company Scoped & Dynamically Linked)
        // =====================================================================
        $debtorsAccId   = $accountMap['1100'] ?? null;
        $creditorsAccId = $accountMap['2000'] ?? null;

        $partyDefs = [
            [
                'party_company_id'      => $companyId,
                'party_type'            => 'customer',
                'party_name'            => 'Acme Retail Solutions Pvt Ltd',
                'party_code'            => 'CUST-001',
                'party_email'           => 'accounts@acmeretail.com',
                'party_phone'           => '9820012345',
                'party_address'         => '101 Horizon Tower, Commercial Hub, Mumbai - 400051',
                'party_gstin'           => '27AAACA1234A1Z5',
                'party_pan'             => 'AAACA1234A',
                'party_account_id'      => $debtorsAccId,
                'party_credit_limit'    => 500000.00,
                'party_credit_days'     => 30,
                'party_current_balance' => 0.00,
                'party_status'          => 'active',
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'party_company_id'      => $companyId,
                'party_type'            => 'vendor',
                'party_name'            => 'NexGen Peripherals & Imports',
                'party_code'            => 'VEND-001',
                'party_email'           => 'sales@nexgenperipherals.com',
                'party_phone'           => '9811198765',
                'party_address'         => 'G-5 Logistics Park, Airport Road, New Delhi - 110037',
                'party_gstin'           => '07AAACN5678B1Z2',
                'party_pan'             => 'AAACN5678B',
                'party_account_id'      => $creditorsAccId,
                'party_credit_limit'    => 1000000.00,
                'party_credit_days'     => 45,
                'party_current_balance' => 0.00,
                'party_status'          => 'active',
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
        ];

        foreach ($partyDefs as $pt) {
            $existing = $db->table('parties')
                ->where('party_company_id', $companyId)
                ->where('party_code', $pt['party_code'])
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('parties')->insert($pt);
            }
        }

        // =====================================================================
        // 13. Seed Company Business Settings (Company Scoped)
        // =====================================================================
        $settingsDefs = [
            'company_name'         => 'LenDen Enterprise Solutions Pvt Ltd',
            'company_email'        => 'info@lenden.local',
            'company_phone'        => '+91 (022) 2890-4400',
            'company_address'      => 'Suite 500, FinTech Plaza, Andheri East, Mumbai 400069',
            'company_gstin'        => '27AABCL1234M1Z8',
            'currency_symbol'      => '₹',
            'currency_code'        => 'INR',
            'date_format'          => 'd/m/Y',
            'financial_year_start' => '04-01',
        ];

        foreach ($settingsDefs as $k => $v) {
            $existing = $db->table('settings')
                ->where('setting_company_id', $companyId)
                ->where('setting_key', $k)
                ->get()->getRowArray();

            if (!$existing) {
                $db->table('settings')->insert([
                    'setting_company_id' => $companyId,
                    'setting_key'        => $k,
                    'setting_value'      => $v,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }

        echo "INITIAL SEEDING COMPLETED WITH MULTI-TENANT ISOLATION, RBAC & ENTERPRISE ACCOUNTING!\n";
    }
}
