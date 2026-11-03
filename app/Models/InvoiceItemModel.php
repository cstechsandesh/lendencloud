<?php

namespace App\Models;

use App\Core\CSModel;

class InvoiceItemModel extends CSModel
{
    protected $table            = 'invoice_items';
    protected $primaryKey       = 'ii_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ii_invoice_id',
        'ii_product_id',
        'ii_quantity',
        'ii_unit_price',
        'ii_discount_pct',
        'ii_tax_rate_id',
        'ii_tax_amount',
        'ii_line_total',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}