<?php

namespace App\Models;

use App\Core\CSModel;

class RoleModel extends CSModel
{
    protected $table            = 'roles';
    protected $primaryKey       = 'role_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_key',
        'role_name',
        'role_description',
        'role_badge_class',
        'role_is_system',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getRolesWithStats()
    {
        $db = \Config\Database::connect();
        $rTbl  = $db->prefixTable('roles');
        $uTbl  = $db->prefixTable('users');
        $rpTbl = $db->prefixTable('role_permissions');

        return $this->select("{$rTbl}.*,
            (SELECT COUNT(*) FROM {$uTbl} WHERE user_role_id = {$rTbl}.role_id OR (user_role_id IS NULL AND user_role = {$rTbl}.role_key)) as user_count,
            (SELECT COUNT(*) FROM {$rpTbl} WHERE rp_role_id = {$rTbl}.role_id) as permission_count")
            ->orderBy('role_id', 'ASC')
            ->findAll();
    }

    public function getPermissionsForRole(int $roleId): array
    {
        $db = \Config\Database::connect();
        return $db->table('role_permissions')
            ->join('permissions', 'permissions.permission_id = role_permissions.rp_permission_id')
            ->where('rp_role_id', $roleId)
            ->get()
            ->getResultArray();
    }

    public function syncPermissions(int $roleId, array $permissionIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('role_permissions')->where('rp_role_id', $roleId)->delete();
        if (!empty($permissionIds)) {
            $batch = [];
            $now = date('Y-m-d H:i:s');
            foreach ($permissionIds as $pId) {
                $batch[] = [
                    'rp_role_id'       => $roleId,
                    'rp_permission_id' => (int)$pId,
                    'created_at'       => $now,
                ];
            }
            $db->table('role_permissions')->insertBatch($batch);
        }
        $db->transComplete();
        return $db->transStatus();
    }
}