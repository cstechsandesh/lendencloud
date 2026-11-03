<?php

namespace App\Models;

use App\Core\CSModel;

class WarehouseModel extends CSModel
{
    protected $table            = 'warehouses';
    protected $primaryKey       = 'warehouse_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'warehouse_company_id',
        'warehouse_name',
        'warehouse_location',
        'warehouse_is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}