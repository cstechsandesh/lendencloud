<?php

namespace App\Models;

use App\Core\CSModel;

class BillModel extends CSModel
{
    protected $table            = 'bills';
    protected $primaryKey       = 'bill_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'bill_company_id',
        'bill_number',
        'bill_order_id',
        'bill_party_id',
        'bill_date',
        'bill_due_date',
        'bill_status',
        'bill_subtotal',
        'bill_tax_amount',
        'bill_discount_amount',
        'bill_total',
        'bill_paid_amount',
        'bill_balance',
        'bill_notes',
        'bill_journal_id',
        'bill_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateBillNumber(?int $companyId = null): string
    {
        $year = date('Y');
        $builder = $this->builder();
        if ($companyId) {
            $builder->where('bill_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('BILL-%s-%04d', $year, $count);
    }

    public function getWithDetails(int $billId): array
    {
        $db = \Config\Database::connect();
        $bill = $this->select('bills.*, parties.party_name, parties.party_code, parties.party_phone, parties.party_email, parties.party_gstin, parties.party_address')
            ->join('parties', 'parties.party_id = bills.bill_party_id', 'left')
            ->where('bill_id', $billId)
            ->first();

        if (!$bill) return [];

        $items = $db->table('bill_items')
            ->select('bill_items.*, products.product_code, products.product_name, products.product_unit, products.product_hsn_code')
            ->join('products', 'products.product_id = bill_items.bi_product_id')
            ->where('bi_bill_id', $billId)
            ->get()
            ->getResultArray();

        $bill['items'] = $items;
        return $bill;
    }

    public function updatePaymentStatus(int $billId): void
    {
        $db = \Config\Database::connect();
        $bill = $this->find($billId);
        if (!$bill) return;

        $paid = (float)$db->table('voucher_allocations')
            ->selectSum('va_amount', 'total_paid')
            ->where('va_reference_type', 'bill')
            ->where('va_reference_id', $billId)
            ->get()->getRowArray()['total_paid'] ?? 0;

        $total   = (float)$bill['bill_total'];
        $balance = max(0.0, $total - $paid);

        $status = 'draft';
        if ($paid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } else {
            $status = 'draft';
        }

        $this->update($billId, [
            'bill_paid_amount' => $paid,
            'bill_balance'     => $balance,
            'bill_status'      => $status,
        ]);

        // Recalculate party balance
        $partyModel = new PartyModel();
        $partyModel->recalculatePartyBalance((int)$bill['bill_party_id']);
    }
}