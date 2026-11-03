<?php

namespace App\Models;

use App\Core\CSModel;

class TaxRateModel extends CSModel
{
    protected $table            = 'tax_rates';
    protected $primaryKey       = 'tax_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tax_company_id',
        'tax_name',
        'tax_rate',
        'tax_is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}