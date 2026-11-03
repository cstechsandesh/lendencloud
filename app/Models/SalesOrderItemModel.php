<?php

namespace App\Models;

use App\Core\CSModel;

class SalesOrderItemModel extends CSModel
{
    protected $table            = 'sales_order_items';
    protected $primaryKey       = 'soi_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'soi_order_id',
        'soi_product_id',
        'soi_quantity',
        'soi_unit_price',
        'soi_discount_pct',
        'soi_tax_rate_id',
        'soi_tax_amount',
        'soi_line_total',
        'soi_delivered_qty',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}