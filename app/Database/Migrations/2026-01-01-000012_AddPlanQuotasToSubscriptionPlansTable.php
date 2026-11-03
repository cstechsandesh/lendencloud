<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanQuotasToSubscriptionPlansTable extends Migration
{
    public function up()
    {
        $fields = [
            'plan_max_invoices' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 50,
                'after'      => 'plan_max_users',
            ],
            'plan_max_bills' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 20,
                'after'      => 'plan_max_invoices',
            ],
            'plan_max_products' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 50,
                'after'      => 'plan_max_bills',
            ],
            'plan_max_warehouses' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 1,
                'after'      => 'plan_max_products',
            ],
            'plan_allows_advanced_reports' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'plan_max_warehouses',
            ],
        ];

        $this->forge->addColumn('subscription_plans', $fields);

        // Update default quotas for seeded tiers
        $db = \Config\Database::connect();

        // 1. Starter Business
        $db->table('subscription_plans')->where('plan_code', 'starter_business')->update([
            'plan_max_companies'           => 1,
            'plan_max_users'               => 1,
            'plan_max_invoices'            => 50,
            'plan_max_bills'               => 20,
            'plan_max_products'            => 30,
            'plan_max_warehouses'          => 1,
            'plan_allows_advanced_reports' => 0,
        ]);

        // 2. Growth Enterprise
        $db->table('subscription_plans')->where('plan_code', 'growth_enterprise')->update([
            'plan_max_companies'           => 1,
            'plan_max_users'               => 3,
            'plan_max_invoices'            => 500,
            'plan_max_bills'               => 250,
            'plan_max_products'            => 500,
            'plan_max_warehouses'          => 3,
            'plan_allows_advanced_reports' => 1,
        ]);

        // 3. Multi-Company Portfolio
        $db->table('subscription_plans')->where('plan_code', 'multi_company_portfolio')->update([
            'plan_max_companies'           => 5,
            'plan_max_users'               => 5,
            'plan_max_invoices'            => 2000,
            'plan_max_bills'               => 1000,
            'plan_max_products'            => 2000,
            'plan_max_warehouses'          => 10,
            'plan_allows_advanced_reports' => 1,
        ]);

        // 4. Corporate Conglomerate (Enterprise = 10 users)
        $db->table('subscription_plans')->where('plan_code', 'corporate_conglomerate')->update([
            'plan_max_companies'           => 0,
            'plan_max_users'               => 10,
            'plan_max_invoices'            => 0,
            'plan_max_bills'               => 0,
            'plan_max_products'            => 0,
            'plan_max_warehouses'          => 0,
            'plan_allows_advanced_reports' => 1,
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('subscription_plans', [
            'plan_max_invoices',
            'plan_max_bills',
            'plan_max_products',
            'plan_max_warehouses',
            'plan_allows_advanced_reports',
        ]);
    }
}

