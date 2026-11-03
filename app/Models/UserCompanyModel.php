<?php

namespace App\Models;

use App\Core\CSModel;

class UserCompanyModel extends CSModel
{
    protected $table            = 'user_companies';
    protected $primaryKey       = 'uc_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'uc_user_id',
        'uc_company_id',
        'created_at',
    ];

    protected $useTimestamps = false;
}

