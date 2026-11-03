<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDemoLoginsAndDemoSupport extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // 1. Add user_is_demo column to users table if not exists
        if (!$db->fieldExists('user_is_demo', 'users')) {
            $this->forge->addColumn('users', [
                'user_is_demo' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'user_status',
                ],
            ]);
        }

        // 2. Fetch all permissions indexed by key
        $allPermissions = $db->table('permissions')->get()->getResultArray();
        $permMap = [];
        foreach ($allPermissions as $p) {
            $permMap[$p['permission_key']] = (int)$p['permission_id'];
        }

        // 3. Fetch all roles
        $roles = $db->table('roles')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $r) {
            $roleMap[$r['role_key']] = (int)$r['role_id'];
        }

        // Define role permission matrices for non-superadmin roles
        $rolePermRules = [
            'admin' => array_keys($permMap), // Business Admin gets all modules
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
            'sales_manager' => [
                'dashboard.view',
                'sales.view', 'sales.create', 'sales.edit', 'sales.cancel', 'sales.print',
                'parties.view', 'parties.create', 'parties.edit',
                'vouchers.view', 'vouchers.create',
                'inventory.view',
                'reports.view',
            ],
            'purchase_manager' => [
                'dashboard.view',
                'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.cancel',
                'parties.view', 'parties.create', 'parties.edit',
                'vouchers.view', 'vouchers.create',
                'inventory.view',
                'reports.view',
            ],
            'inventory_manager' => [
                'dashboard.view',
                'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust',
                'purchases.view',
                'sales.view',
                'reports.view',
            ],
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
                'users.view', 'roles.view', 'companies.view', 'settings.view', 'subscriptions.view',
            ],
        ];

        // Pre-fetch ALL existing role-permission pairs
        $existingRpRows = $db->table('role_permissions')->select('rp_role_id, rp_permission_id')->get()->getResultArray();
        $existingRpSet = [];
        foreach ($existingRpRows as $erp) {
            $existingRpSet[$erp['rp_role_id'] . '-' . $erp['rp_permission_id']] = true;
        }

        // Seed role permissions for each role if not already assigned
        foreach ($rolePermRules as $roleKey => $permKeys) {
            if (!isset($roleMap[$roleKey])) continue;
            $roleId = $roleMap[$roleKey];

            $batch = [];
            foreach ($permKeys as $k) {
                if (isset($permMap[$k])) {
                    $pid = $permMap[$k];
                    $key = $roleId . '-' . $pid;
                    if (!isset($existingRpSet[$key])) {
                        $batch[] = [
                            'rp_role_id'       => $roleId,
                            'rp_permission_id' => $pid,
                            'created_at'       => $now,
                        ];
                        $existingRpSet[$key] = true;
                    }
                }
            }
            if (!empty($batch)) {
                $db->table('role_permissions')->insertBatch($batch);
            }
        }

        // 4. Seed / Synchronize Demo Accounts for Business Roles (Super Admin is strictly non-demo)
        $demoUsers = [
            [
                'role_key'         => 'admin',
                'username'         => 'admin_demo',
                'email'            => 'admin@lenden.com',
                'name'             => 'Demo Business Administrator',
                'password'         => 'admin123',
                'phone'            => '9876543202',
                'designation'      => 'General Manager',
            ],
            [
                'role_key'         => 'accountant',
                'username'         => 'accountant',
                'email'            => 'accountant@lenden.com',
                'name'             => 'Demo Chief Accountant',
                'password'         => 'accountant123',
                'phone'            => '9876543203',
                'designation'      => 'Chief Accountant & Head of Finance',
            ],
            [
                'role_key'         => 'sales_manager',
                'username'         => 'sales',
                'email'            => 'sales@lenden.com',
                'name'             => 'Demo Sales Manager',
                'password'         => 'sales123',
                'phone'            => '9876543204',
                'designation'      => 'Head of Sales & Invoicing',
            ],
            [
                'role_key'         => 'purchase_manager',
                'username'         => 'purchase',
                'email'            => 'purchase@lenden.com',
                'name'             => 'Demo Purchase Manager',
                'password'         => 'purchase123',
                'phone'            => '9876543205',
                'designation'      => 'Procurement & Supply Chain Lead',
            ],
            [
                'role_key'         => 'inventory_manager',
                'username'         => 'inventory',
                'email'            => 'inventory@lenden.com',
                'name'             => 'Demo Inventory Manager',
                'password'         => 'inventory123',
                'phone'            => '9876543206',
                'designation'      => 'Warehouse & Stock Controller',
            ],
            [
                'role_key'         => 'viewer',
                'username'         => 'viewer',
                'email'            => 'viewer@lenden.com',
                'name'             => 'Demo Auditor / Viewer',
                'password'         => 'viewer123',
                'phone'            => '9876543207',
                'designation'      => 'Statutory Auditor',
            ],
        ];

        foreach ($demoUsers as $du) {
            $roleId = $roleMap[$du['role_key']] ?? 1;

            $existing = $db->table('users')
                ->where('user_email', $du['email'])
                ->orWhere('user_username', $du['username'])
                ->get()
                ->getFirstRow('array');

            $userData = [
                'user_company_id'    => 1,
                'user_role_id'       => $roleId,
                'user_role'          => $du['role_key'],
                'user_username'      => $du['username'],
                'user_email'         => $du['email'],
                'user_name'          => $du['name'],
                'user_phone'         => $du['phone'],
                'user_password_hash' => password_hash($du['password'], PASSWORD_BCRYPT),
                'user_designation'   => $du['designation'],
                'user_status'        => 'active',
                'user_is_demo'       => 1,
                'updated_at'         => $now,
            ];

            if ($existing) {
                $db->table('users')->where('user_id', $existing['user_id'])->update($userData);
                $userId = (int)$existing['user_id'];
            } else {
                $userData['created_at'] = $now;
                $db->table('users')->insert($userData);
                $userId = (int)$db->insertID();
            }

            // Sync user_roles
            $hasRole = $db->table('user_roles')
                ->where('ur_user_id', $userId)
                ->where('ur_role_id', $roleId)
                ->countAllResults();
            if ($hasRole === 0) {
                $db->table('user_roles')->insert([
                    'ur_user_id' => $userId,
                    'ur_role_id' => $roleId,
                    'created_at' => $now,
                ]);
            }

            // Sync user_companies
            $hasCompany = $db->table('user_companies')
                ->where('uc_user_id', $userId)
                ->where('uc_company_id', 1)
                ->countAllResults();
            if ($hasCompany === 0) {
                $db->table('user_companies')->insert([
                    'uc_user_id'    => $userId,
                    'uc_company_id' => 1,
                    'created_at'    => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $demoEmails = [
                        'admin@lenden.com',
            'accountant@lenden.com',
            'sales@lenden.com',
            'purchase@lenden.com',
            'inventory@lenden.com',
            'viewer@lenden.com',
        ];

        $users = $db->table('users')->whereIn('user_email', $demoEmails)->get()->getResultArray();
        $userIds = array_column($users, 'user_id');

        if (!empty($userIds)) {
            $db->table('user_roles')->whereIn('ur_user_id', $userIds)->delete();
            $db->table('user_companies')->whereIn('uc_user_id', $userIds)->delete();
            $db->table('users')->whereIn('user_id', $userIds)->delete();
        }

        if ($db->fieldExists('user_is_demo', 'users')) {
            $this->forge->dropColumn('users', 'user_is_demo');
        }
    }
}
