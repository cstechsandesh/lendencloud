<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompaniesAndMultiCompanySupport extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Create 'companies' table for multi-company support
        $this->forge->addField([
            'company_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'company_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company_registration_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'company_gstin' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company_pan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'company_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Mumbai',
            ],
            'company_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Maharashtra',
            ],
            'company_postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'company_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'company_website' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'company_bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'company_account_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'company_ifsc_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company_upi_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'company_currency_symbol' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => '₹',
            ],
            'company_financial_year_start' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '04-01',
            ],
            'company_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->addKey('company_id', true);
        $this->forge->addKey('company_status');
        $this->forge->createTable('companies', true);

        // 2. Create 'user_companies' table for multi-company user access mapping
        $this->forge->addField([
            'uc_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uc_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'uc_company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('uc_id', true);
        $this->forge->addKey('uc_user_id');
        $this->forge->addKey('uc_company_id');
        $this->forge->addUniqueKey(['uc_user_id', 'uc_company_id']);
        $this->forge->createTable('user_companies', true);

        // 3. Add user_company_id to users if not present
        if ($this->db->tableExists('users') && !$this->db->fieldExists('user_company_id', 'users')) {
            $this->forge->addColumn('users', [
                'user_company_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 1,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ]);
        }

        // 4. Add company_id columns to tenant business tables
        $tenantColumns = [
            'account_groups'  => 'group_company_id',
            'accounts'        => 'account_company_id',
            'tax_rates'       => 'tax_company_id',
            'parties'         => 'party_company_id',
            'categories'      => 'category_company_id',
            'warehouses'      => 'warehouse_company_id',
            'products'        => 'product_company_id',
            'stock_movements' => 'movement_company_id',
            'journal_entries' => 'journal_company_id',
            'sales_orders'    => 'so_company_id',
            'invoices'        => 'invoice_company_id',
            'purchase_orders' => 'po_company_id',
            'bills'           => 'bill_company_id',
            'vouchers'        => 'voucher_company_id',
            'settings'        => 'setting_company_id',
        ];

        foreach ($tenantColumns as $table => $column) {
            if ($this->db->tableExists($table) && !$this->db->fieldExists($column, $table)) {
                $this->forge->addColumn($table, [
                    $column => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'default'    => 1,
                        'null'       => true,
                    ],
                ]);
            }
        }

        // 5. Seed default company #1 if table is empty
        $existing = $this->db->table('companies')->where('company_id', 1)->get()->getRowArray();
        if (!$existing) {
            $defaultCompanyName = 'LenDen Business Suite';
            $defaultGstin = '27AABCL1234M1Z8';
            $defaultEmail = 'info@lenden.local';
            $defaultPhone = '+91 (022) 2890-4400';
            $defaultAddress = 'Suite 500, FinTech Plaza, Andheri East, Mumbai 400069';

            // Check if settings table has existing company info
            if ($this->db->tableExists('settings')) {
                $settingRows = $this->db->table('settings')->get()->getResultArray();
                $settingMap = [];
                foreach ($settingRows as $sr) {
                    $settingMap[$sr['setting_key']] = $sr['setting_value'];
                }
                if (!empty($settingMap['company_name'])) {
                    $defaultCompanyName = $settingMap['company_name'];
                }
                if (!empty($settingMap['company_gstin'])) {
                    $defaultGstin = $settingMap['company_gstin'];
                }
                if (!empty($settingMap['company_email'])) {
                    $defaultEmail = $settingMap['company_email'];
                }
                if (!empty($settingMap['company_phone'])) {
                    $defaultPhone = $settingMap['company_phone'];
                }
                if (!empty($settingMap['company_address'])) {
                    $defaultAddress = $settingMap['company_address'];
                }
            }

            $this->db->table('companies')->insert([
                'company_id'                   => 1,
                'company_name'                 => $defaultCompanyName,
                'company_code'                 => 'LENDEN-01',
                'company_registration_number'  => 'REG-2026-001',
                'company_gstin'                => $defaultGstin,
                'company_pan'                  => 'AABCL1234M',
                'company_address'              => $defaultAddress,
                'company_city'                 => 'Mumbai',
                'company_state'                => 'Maharashtra',
                'company_postal_code'          => '400069',
                'company_phone'                => $defaultPhone,
                'company_email'                => $defaultEmail,
                'company_website'              => 'https://lenden.local',
                'company_bank_name'            => 'HDFC Bank Ltd',
                'company_account_no'           => '50200012345678',
                'company_ifsc_code'            => 'HDFC0001234',
                'company_upi_id'               => 'lenden@hdfcbank',
                'company_currency_symbol'      => '₹',
                'company_financial_year_start' => '04-01',
                'company_status'               => 'active',
                'created_at'                   => $now,
                'updated_at'                   => $now,
            ]);
        }

        // Map existing users to company #1 in user_companies and user_company_id
        if ($this->db->tableExists('users')) {
            $this->db->table('users')->where('user_company_id IS NULL')->orWhere('user_company_id', 0)->update(['user_company_id' => 1]);
            $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
            foreach ($users as $u) {
                $exists = $this->db->table('user_companies')
                    ->where('uc_user_id', $u['user_id'])
                    ->where('uc_company_id', 1)
                    ->countAllResults();
                if (!$exists) {
                    $this->db->table('user_companies')->insert([
                        'uc_user_id'    => (int)$u['user_id'],
                        'uc_company_id' => 1,
                        'created_at'    => $now,
                    ]);
                }
            }
        }

        // Populate company_id = 1 for all existing records across tenant tables
        foreach ($tenantColumns as $table => $column) {
            if ($this->db->tableExists($table)) {
                $this->db->table($table)->where("{$column} IS NULL")->orWhere($column, 0)->update([$column => 1]);
            }
        }

        // 6. Seed 'companies' permissions
        if ($this->db->tableExists('permissions')) {
            $companyPerms = [
                ['permission_module' => 'companies', 'permission_key' => 'companies.view', 'permission_name' => 'Companies View', 'permission_description' => 'View multi-company portfolio and list', 'created_at' => $now],
                ['permission_module' => 'companies', 'permission_key' => 'companies.create', 'permission_name' => 'Companies Create', 'permission_description' => 'Create new independent company entity', 'created_at' => $now],
                ['permission_module' => 'companies', 'permission_key' => 'companies.edit', 'permission_name' => 'Companies Edit', 'permission_description' => 'Update company details and bank info', 'created_at' => $now],
                ['permission_module' => 'companies', 'permission_key' => 'companies.delete', 'permission_name' => 'Companies Delete', 'permission_description' => 'Delete company from portfolio', 'created_at' => $now],
                ['permission_module' => 'companies', 'permission_key' => 'companies.switch', 'permission_name' => 'Companies Switch', 'permission_description' => 'Switch active company workspace context', 'created_at' => $now],
            ];

            foreach ($companyPerms as $perm) {
                $exists = $this->db->table('permissions')->where('permission_key', $perm['permission_key'])->countAllResults();
                if (!$exists) {
                    $this->db->table('permissions')->insert($perm);
                    $permId = $this->db->insertID();

                    // Assign to super_admin and admin roles
                    $roles = $this->db->table('roles')->whereIn('role_key', ['super_admin', 'admin'])->get()->getResultArray();
                    foreach ($roles as $r) {
                        $this->db->table('role_permissions')->ignore(true)->insert([
                            'rp_role_id'       => $r['role_id'],
                            'rp_permission_id' => $permId,
                            'created_at'       => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $tenantColumns = [
            'account_groups'  => 'group_company_id',
            'accounts'        => 'account_company_id',
            'tax_rates'       => 'tax_company_id',
            'parties'         => 'party_company_id',
            'categories'      => 'category_company_id',
            'warehouses'      => 'warehouse_company_id',
            'products'        => 'product_company_id',
            'stock_movements' => 'movement_company_id',
            'journal_entries' => 'journal_company_id',
            'sales_orders'    => 'so_company_id',
            'invoices'        => 'invoice_company_id',
            'purchase_orders' => 'po_company_id',
            'bills'           => 'bill_company_id',
            'vouchers'        => 'voucher_company_id',
            'settings'        => 'setting_company_id',
        ];

        foreach ($tenantColumns as $table => $column) {
            if ($this->db->tableExists($table) && $this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }

        if ($this->db->tableExists('users') && $this->db->fieldExists('user_company_id', 'users')) {
            $this->forge->dropColumn('users', 'user_company_id');
        }

        $this->forge->dropTable('user_companies', true);
        $this->forge->dropTable('companies', true);
    }
}

