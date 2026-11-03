<?php

namespace App\Models;

use App\Core\CSModel;

class PurchaseOrderModel extends CSModel
{
    protected $table            = 'purchase_orders';
    protected $primaryKey       = 'po_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'po_company_id',
        'po_number',
        'po_party_id',
        'po_date',
        'po_expected_date',
        'po_status',
        'po_subtotal',
        'po_tax_amount',
        'po_discount_amount',
        'po_total',
        'po_notes',
        'po_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateOrderNumber(?int $companyId = null): string
    {
        $year = date('Y');
        $builder = $this->builder();
        if ($companyId) {
            $builder->where('po_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('PO-%s-%04d', $year, $count);
    }

    public function getWithItems(int $orderId): array
    {
        $db = \Config\Database::connect();
        $order = $this->select('purchase_orders.*, parties.party_name, parties.party_phone, parties.party_email, parties.party_gstin')
            ->join('parties', 'parties.party_id = purchase_orders.po_party_id', 'left')
            ->where('po_id', $orderId)
            ->first();

        if (!$order) return [];

        $items = $db->table('purchase_order_items')
            ->select('purchase_order_items.*, products.product_code, products.product_name, products.product_unit')
            ->join('products', 'products.product_id = purchase_order_items.poi_product_id')
            ->where('poi_order_id', $orderId)
            ->get()
            ->getResultArray();

        $order['items'] = $items;
        return $order;
    }
}