<?php

namespace App\Models;

use App\Core\CSModel;

class ProductModel extends CSModel
{
    protected $table            = 'products';
    protected $primaryKey       = 'product_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_company_id',
        'product_category_id',
        'product_code',
        'product_name',
        'product_description',
        'product_hsn_code',
        'product_unit',
        'product_purchase_price',
        'product_selling_price',
        'product_tax_rate_id',
        'product_current_stock',
        'product_reorder_level',
        'product_is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProductsWithDetails(?int $companyId = null)
    {
        $builder = $this->select('products.*, categories.category_name, tax_rates.tax_name, tax_rates.tax_rate')
            ->join('categories', 'categories.category_id = products.product_category_id', 'left')
            ->join('tax_rates', 'tax_rates.tax_id = products.product_tax_rate_id', 'left');

        if ($companyId) {
            $builder->where('products.product_company_id', $companyId);
        }

        return $builder->orderBy('products.product_name', 'ASC')->findAll();
    }

    public function getLowStockProducts(?int $companyId = null)
    {
        $builder = $this->where('product_current_stock <= product_reorder_level')
            ->where('product_is_active', 1);

        if ($companyId) {
            $builder->where('product_company_id', $companyId);
        }

        return $builder->orderBy('product_current_stock', 'ASC')->findAll();
    }

    public function recalculateStock(int $productId): float
    {
        $db = \Config\Database::connect();
        $inQty = $db->table('stock_movements')
            ->selectSum('stock_quantity', 'total_in')
            ->where('stock_product_id', $productId)
            ->whereIn('stock_type', ['in', 'adjustment', 'transfer_in'])
            ->get()->getRowArray()['total_in'] ?? 0;

        $outQty = $db->table('stock_movements')
            ->selectSum('stock_quantity', 'total_out')
            ->where('stock_product_id', $productId)
            ->whereIn('stock_type', ['out', 'transfer_out'])
            ->get()->getRowArray()['total_out'] ?? 0;

        $stock = (float)$inQty - (float)$outQty;
        $this->update($productId, ['product_current_stock' => $stock]);
        return $stock;
    }
}