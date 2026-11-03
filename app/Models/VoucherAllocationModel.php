<?php

namespace App\Models;

use App\Core\CSModel;

class VoucherAllocationModel extends CSModel
{
    protected $table            = 'voucher_allocations';
    protected $primaryKey       = 'va_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'va_voucher_id',
        'va_reference_type',
        'va_reference_id',
        'va_amount',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}