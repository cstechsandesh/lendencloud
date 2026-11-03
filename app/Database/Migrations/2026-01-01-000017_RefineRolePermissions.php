<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefineRolePermissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Get inventory_manager role
        $role = $db->table('roles')->where('role_key', 'inventory_manager')->get()->getRowArray();
        if ($role) {
            $roleId = (int)$role['role_id'];

            // Get permission IDs for sales.view and purchases.view
            $permIds = $db->table('permissions')
                ->whereIn('permission_key', ['sales.view', 'purchases.view'])
                ->get()->getResultArray();

            $idsToRemove = array_map(fn($p) => (int)$p['permission_id'], $permIds);

            if (!empty($idsToRemove)) {
                $db->table('role_permissions')
                    ->where('rp_role_id', $roleId)
                    ->whereIn('rp_permission_id', $idsToRemove)
                    ->delete();
            }
        }
    }

    public function down()
    {
        // Reversible if ever needed
    }
}

