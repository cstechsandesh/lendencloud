<?php

namespace App\Models;

use App\Core\CSModel;

class PermissionModel extends CSModel
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'permission_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'permission_module',
        'permission_key',
        'permission_name',
        'permission_description',
    ];

    protected $useTimestamps = false;

    public function getAllGroupedByModule(): array
    {
        $all = $this->orderBy('permission_module', 'ASC')->orderBy('permission_key', 'ASC')->findAll();
        $grouped = [];
        foreach ($all as $item) {
            $grouped[$item['permission_module']][] = $item;
        }
        return $grouped;
    }
}