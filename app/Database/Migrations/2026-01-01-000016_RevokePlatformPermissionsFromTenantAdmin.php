<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RevokePlatformPermissionsFromTenantAdmin extends Migration
{
    public function up()
    {
        $db = $this->db;

        // Platform-only permissions that belong strictly to super_admin
        $platformPermissions = [
            'subscriptions.view',
            'subscriptions.manage',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'companies.delete',
        ];

        // 1. Fetch permission IDs for platform permissions
        $perms = $db->table('permissions')
            ->whereIn('permission_key', $platformPermissions)
            ->get()->getResultArray();

        $permIds = array_column($perms, 'permission_id');
        if (empty($permIds)) {
            return;
        }

        // 2. Fetch all roles except 'super_admin'
        $nonSuperRoles = $db->table('roles')
            ->where('role_key !=', 'super_admin')
            ->get()->getResultArray();

        $roleIds = array_column($nonSuperRoles, 'role_id');
        if (empty($roleIds)) {
            return;
        }

        // 3. Delete any assignment of platform permissions to non-superadmin roles
        $db->table('role_permissions')
            ->whereIn('rp_role_id', $roleIds)
            ->whereIn('rp_permission_id', $permIds)
            ->delete();
    }

    public function down()
    {
        // Platform permissions should remain strictly super_admin
    }
}

