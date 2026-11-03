<?php

namespace App\Models;

use App\Core\CSModel;

class UserRoleModel extends CSModel
{
    protected $table            = 'user_roles';
    protected $primaryKey       = 'ur_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ur_user_id',
        'ur_role_id',
    ];

    protected $useTimestamps = false;
}