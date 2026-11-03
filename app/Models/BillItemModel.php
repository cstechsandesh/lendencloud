<?php

namespace App\Models;

use App\Core\CSModel;

class BillItemModel extends CSModel
{
    protected $table            = 'bill_items';
    protected $primaryKey       = 'bi_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bi_bill_id',
        'bi_product_id',
        'bi_quantity',
        'bi_unit_price',
        'bi_discount_pct',
        'bi_tax_rate_id',
        'bi_tax_amount',
        'bi_line_total',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}