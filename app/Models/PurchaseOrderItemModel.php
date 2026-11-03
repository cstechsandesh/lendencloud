<?php

namespace App\Models;

use App\Core\CSModel;

class PurchaseOrderItemModel extends CSModel
{
    protected $table            = 'purchase_order_items';
    protected $primaryKey       = 'poi_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'poi_order_id',
        'poi_product_id',
        'poi_quantity',
        'poi_unit_price',
        'poi_discount_pct',
        'poi_tax_rate_id',
        'poi_tax_amount',
        'poi_line_total',
        'poi_received_qty',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}