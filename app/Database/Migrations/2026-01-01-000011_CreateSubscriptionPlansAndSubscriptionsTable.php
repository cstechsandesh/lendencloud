<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionPlansAndSubscriptionsTable extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. subscription_plans
        $this->forge->addField([
            'plan_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'plan_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'plan_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'plan_tagline' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'plan_price_per_month' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'plan_price_per_year' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'plan_max_companies' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 1,
            ],
            'plan_max_users' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 5,
            ],
            'plan_features' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'plan_badge' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'plan_is_popular' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'plan_is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('plan_id', true);
        $this->forge->createTable('subscription_plans', true);

        // 2. subscriptions
        $this->forge->addField([
            'subscription_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'subscription_company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'subscription_plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'subscription_billing_cycle' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'yearly'],
                'default'    => 'monthly',
            ],
            'subscription_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'subscription_start_date' => [
                'type' => 'DATE',
            ],
            'subscription_end_date' => [
                'type' => 'DATE',
            ],
            'subscription_status' => [
                'type'       => 'ENUM',
                'constraint' => ['trial', 'active', 'expired', 'cancelled'],
                'default'    => 'trial',
            ],
            'subscription_payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'complimentary_trial',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('subscription_id', true);
        $this->forge->addKey('subscription_company_id');
        $this->forge->addKey('subscription_plan_id');
        $this->forge->createTable('subscriptions', true);

        $db = \Config\Database::connect();

        // 3. Seed Subscription Plans
        $plans = [
            [
                'plan_id'              => 1,
                'plan_name'            => 'Starter Business',
                'plan_code'            => 'starter_business',
                'plan_tagline'         => 'Essential double-entry accounting, invoicing & GST billing for sole proprietors & startups.',
                'plan_price_per_month' => 499.00,
                'plan_price_per_year'  => 4990.00,
                'plan_max_companies'   => 1,
                'plan_max_users'       => 1,
                'plan_features'        => json_encode([
                    '1 Legal Business Entity / Branch',
                    '1 Active User Login',
                    'Sales Invoicing & Quotations',
                    'GST Returns & Tax Rate Engine',
                    'Customer Ledger & Payment Tracking',
                    'Basic Chart of Accounts',
                    'Cash & Bank Vouchers',
                    'Daily Sales & Day Book Reports',
                ]),
                'plan_badge'           => 'Starter',
                'plan_is_popular'      => 0,
                'plan_is_active'       => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'plan_id'              => 2,
                'plan_name'            => 'Growth Enterprise',
                'plan_code'            => 'growth_enterprise',
                'plan_tagline'         => 'Complete multi-user ERP with double-entry ledgers, purchase orders, inventory & financial statements.',
                'plan_price_per_month' => 1299.00,
                'plan_price_per_year'  => 12990.00,
                'plan_max_companies'   => 1,
                'plan_max_users'       => 3,
                'plan_features'        => json_encode([
                    '1 Legal Business Entity',
                    'Up to 3 Multi-Role User Logins',
                    'Everything in Starter Business',
                    'Full Double-Entry Journals & General Ledger',
                    'Purchase Orders & Vendor Bill Matching',
                    'Multi-Warehouse Inventory & Stock Adjustments',
                    'Real-Time Balance Sheet, P&L, Trial Balance',
                    'Automated Customer Aging & Payment Receipts',
                    'Export to Excel, PDF & Audit Formats',
                ]),
                'plan_badge'           => 'Most Popular',
                'plan_is_popular'      => 1,
                'plan_is_active'       => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'plan_id'              => 3,
                'plan_name'            => 'Multi-Company Portfolio',
                'plan_code'            => 'multi_company_portfolio',
                'plan_tagline'         => 'Engineered for holding groups, CA firms, and conglomerates managing multiple subsidiaries.',
                'plan_price_per_month' => 2999.00,
                'plan_price_per_year'  => 29990.00,
                'plan_max_companies'   => 5,
                'plan_max_users'       => 5,
                'plan_features'        => json_encode([
                    'Up to 5 Business Entities & Subsidiaries',
                    'Up to 5 Active Team Members',
                    'Everything in Growth Enterprise',
                    'Multi-Company Central Switcher & Overview',
                    'Consolidated Group Financial Reporting',
                    'Inter-Company Accounts & Contra Vouchers',
                    'Centralized Product Catalog & Pricing',
                    'Role-Based Access Control (RBAC) per Company',
                    'Priority Email & Phone Tech Support',
                ]),
                'plan_badge'           => 'Multi-Company',
                'plan_is_popular'      => 0,
                'plan_is_active'       => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
            [
                'plan_id'              => 4,
                'plan_name'            => 'Corporate Conglomerate',
                'plan_code'            => 'corporate_conglomerate',
                'plan_tagline'         => 'Tailored for large corporate enterprises, apex federations, and high-volume multi-branch networks.',
                'plan_price_per_month' => 5999.00,
                'plan_price_per_year'  => 59990.00,
                'plan_max_companies'   => 0, // unlimited
                'plan_max_users'       => 10,
                'plan_features'        => json_encode([
                    'Unlimited Companies, Branches & Subsidiaries',
                    'Up to 10 User Accounts & Departments',
                    'Full Multi-Entity Central Administration',
                    'Advanced Inventory & Warehouse Matrix',
                    'Custom Financial Periods & Audit Trail',
                    'Automated Bank Reconciliation (Recon)',
                    'Custom Tax Compliances & E-Invoicing Prep',
                    'Dedicated Account Manager & 24/7 SLA',
                ]),
                'plan_badge'           => 'Enterprise',
                'plan_is_popular'      => 0,
                'plan_is_active'       => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ],
        ];

        foreach ($plans as $p) {
            $db->table('subscription_plans')->ignore(true)->insert($p);
        }

        // 4. Seed Permissions
        $newPerms = [
            [
                'permission_module'      => 'Subscriptions',
                'permission_key'         => 'subscriptions.view',
                'permission_name'        => 'View Subscriptions & Plans',
                'permission_description' => 'View company subscription plans, validity, and SaaS pricing catalog',
                'created_at'             => $now,
            ],
            [
                'permission_module'      => 'Subscriptions',
                'permission_key'         => 'subscriptions.manage',
                'permission_name'        => 'Manage Subscriptions & Plans',
                'permission_description' => 'Assign, modify, extend, toggle and configure subscription tiers and pricing plans',
                'created_at'             => $now,
            ],
        ];

        foreach ($newPerms as $perm) {
            $db->table('permissions')->ignore(true)->insert($perm);
        }

        // Assign to super_admin and admin roles
        $roles = $db->table('roles')->whereIn('role_key', ['super_admin', 'admin'])->get()->getResultArray();
        $perms = $db->table('permissions')->whereIn('permission_key', ['subscriptions.view', 'subscriptions.manage'])->get()->getResultArray();

        foreach ($roles as $r) {
            foreach ($perms as $pm) {
                $db->table('role_permissions')->ignore(true)->insert([
                    'rp_role_id'       => $r['role_id'],
                    'rp_permission_id' => $pm['permission_id'],
                    'created_at'       => $now,
                ]);
            }
        }

        // 5. Seed initial subscriptions for existing companies
        $companies = $db->table('companies')->get()->getResultArray();
        foreach ($companies as $comp) {
            $cid = (int)$comp['company_id'];
            $exists = $db->table('subscriptions')->where('subscription_company_id', $cid)->countAllResults();
            if (!$exists) {
                $db->table('subscriptions')->insert([
                    'subscription_company_id'     => $cid,
                    'subscription_plan_id'        => 2, // Growth Enterprise
                    'subscription_billing_cycle'  => 'yearly',
                    'subscription_amount'         => 12990.00,
                    'subscription_start_date'     => date('Y-m-01'),
                    'subscription_end_date'       => date('Y-m-d', strtotime('+1 year')),
                    'subscription_status'         => 'active',
                    'subscription_payment_method' => 'bank_transfer',
                    'created_at'                  => $now,
                    'updated_at'                  => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions', true);
        $this->forge->dropTable('subscription_plans', true);
    }
}

