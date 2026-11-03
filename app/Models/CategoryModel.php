<?php

namespace App\Models;

use App\Core\CSModel;

class CategoryModel extends CSModel
{
    protected $table            = 'categories';
    protected $primaryKey       = 'category_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_company_id',
        'category_name',
        'category_parent_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}