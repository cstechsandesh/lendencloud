<?php

namespace App\Models;

use App\Core\CSModel;

class UserModel extends CSModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_company_id',
        'user_role_id',
        'user_role',
        'user_username',
        'user_email',
        'user_name',
        'user_phone',
        'user_password_hash',
        'user_designation',
        'user_avatar',
        'user_status',
        'user_is_demo',
        'user_last_login',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUsername(string $username)
    {
        return $this->where('user_username', $username)
            ->orWhere('user_email', $username)
            ->first();
    }

    public function getUserPermissions(int $userId): array
    {
        $db = $this->db;
        $user = $this->find($userId);
        if (!$user) return [];

        // Check if super_admin
        if ($user['user_role'] === 'super_admin') {
            $perms = $db->table('permissions')->select('permission_key')->get()->getResultArray();
            return array_column($perms, 'permission_key');
        }

        // Get roles via user_role_id + user_roles table
        $roleIds = [];
        if (!empty($user['user_role_id'])) {
            $roleIds[] = (int)$user['user_role_id'];
        }
        $mapped = $db->table('user_roles')->where('ur_user_id', $userId)->get()->getResultArray();
        foreach ($mapped as $m) {
            $roleIds[] = (int)$m['ur_role_id'];
        }
        $roleIds = array_unique(array_filter($roleIds));
        if (empty($roleIds)) return [];

        // If any assigned role has role_key = 'super_admin'
        $superCheck = $db->table('roles')
            ->whereIn('role_id', $roleIds)
            ->where('role_key', 'super_admin')
            ->countAllResults();
        if ($superCheck > 0) {
            $perms = $db->table('permissions')->select('permission_key')->get()->getResultArray();
            return array_column($perms, 'permission_key');
        }

        $res = $db->table('role_permissions')
            ->select('permissions.permission_key')
            ->join('permissions', 'permissions.permission_id = role_permissions.rp_permission_id')
            ->whereIn('role_permissions.rp_role_id', $roleIds)
            ->get()
            ->getResultArray();

        return array_unique(array_column($res, 'permission_key'));
    }

    public function getUsersWithRoles(?int $companyId = null)
    {
        $builder = $this->select("users.*, roles.role_name, roles.role_badge_class, roles.role_key")
            ->join('roles', 'roles.role_id = users.user_role_id', 'left')
            ->orderBy('users.user_id', 'ASC');

        if ($companyId !== null) {
            $builder->where('users.user_company_id', $companyId);
        }

        return $builder->findAll();
    }
}
