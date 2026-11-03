<?php

namespace App\Models;

use App\Core\CSModel;

class RolePermissionModel extends CSModel
{
    protected $table            = 'role_permissions';
    protected $primaryKey       = 'rp_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'rp_role_id',
        'rp_permission_id',
    ];

    protected $useTimestamps = false;
}