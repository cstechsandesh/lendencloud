<?php

namespace App\Models;

use App\Core\CSModel;

class SalesOrderModel extends CSModel
{
    protected $table            = 'sales_orders';
    protected $primaryKey       = 'so_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'so_company_id',
        'so_number',
        'so_party_id',
        'so_date',
        'so_expected_delivery',
        'so_status',
        'so_subtotal',
        'so_tax_amount',
        'so_discount_amount',
        'so_total',
        'so_notes',
        'so_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateOrderNumber(?int $companyId = null): string
    {
        $year = date('Y');
        $builder = $this->builder();
        if ($companyId) {
            $builder->where('so_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('SO-%s-%04d', $year, $count);
    }

    public function getWithItems(int $orderId): array
    {
        $db = \Config\Database::connect();
        $order = $this->select('sales_orders.*, parties.party_name, parties.party_phone, parties.party_email, parties.party_gstin, parties.party_address')
            ->join('parties', 'parties.party_id = sales_orders.so_party_id', 'left')
            ->where('so_id', $orderId)
            ->first();

        if (!$order) return [];

        $items = $db->table('sales_order_items')
            ->select('sales_order_items.*, products.product_code, products.product_name, products.product_unit')
            ->join('products', 'products.product_id = sales_order_items.soi_product_id')
            ->where('soi_order_id', $orderId)
            ->get()
            ->getResultArray();

        $order['items'] = $items;
        return $order;
    }
}