<?php

namespace App\Models;

use App\Core\CSModel;

class JournalLineModel extends CSModel
{
    protected $table            = 'journal_lines';
    protected $primaryKey       = 'jl_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'jl_journal_id',
        'jl_account_id',
        'jl_debit',
        'jl_credit',
        'jl_narration',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}