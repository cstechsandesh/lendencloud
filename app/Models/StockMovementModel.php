<?php

namespace App\Models;

use App\Core\CSModel;

class StockMovementModel extends CSModel
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'stock_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'movement_company_id',
        'stock_product_id',
        'stock_warehouse_id',
        'stock_type',
        'stock_quantity',
        'stock_unit_cost',
        'stock_reference_type',
        'stock_reference_id',
        'stock_notes',
        'stock_date',
        'stock_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function recordMovement(array $data): int
    {
        $db = \Config\Database::connect();
        $data['created_at'] = date('Y-m-d H:i:s');
        if (empty($data['stock_date'])) {
            $data['stock_date'] = $data['created_at'];
        }
        $this->insert($data);
        $movementId = $this->getInsertID();

        // Update product stock balance
        $productModel = new ProductModel();
        $productModel->recalculateStock((int)$data['stock_product_id']);

        return $movementId;
    }
}