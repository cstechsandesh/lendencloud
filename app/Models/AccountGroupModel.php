<?php

namespace App\Models;

use App\Core\CSModel;

class AccountGroupModel extends CSModel
{
    protected $table            = 'account_groups';
    protected $primaryKey       = 'group_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'group_company_id',
        'group_name',
        'group_type',
        'group_parent_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getTree(?int $companyId = null): array
    {
        $builder = $this->orderBy('group_type', 'ASC')->orderBy('group_name', 'ASC');
        if ($companyId) {
            $builder->where('group_company_id', $companyId);
        }
        $all = $builder->findAll();
        $grouped = [];
        foreach ($all as $item) {
            $grouped[$item['group_type']][] = $item;
        }
        return $grouped;
    }
}