<?php

namespace App\Models;

use App\Core\CSModel;

class InvoiceModel extends CSModel
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'invoice_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_company_id',
        'invoice_number',
        'invoice_order_id',
        'invoice_party_id',
        'invoice_date',
        'invoice_due_date',
        'invoice_status',
        'invoice_subtotal',
        'invoice_tax_amount',
        'invoice_discount_amount',
        'invoice_total',
        'invoice_paid_amount',
        'invoice_balance',
        'invoice_notes',
        'invoice_journal_id',
        'invoice_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateInvoiceNumber(?int $companyId = null): string
    {
        $year = date('Y');
        $builder = $this->builder();
        if ($companyId) {
            $builder->where('invoice_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('INV-%s-%04d', $year, $count);
    }

    public function getWithDetails(int $invoiceId): array
    {
        $db = \Config\Database::connect();
        $inv = $this->select('invoices.*, parties.party_name, parties.party_code, parties.party_phone, parties.party_email, parties.party_gstin, parties.party_address')
            ->join('parties', 'parties.party_id = invoices.invoice_party_id', 'left')
            ->where('invoice_id', $invoiceId)
            ->first();

        if (!$inv) return [];

        $items = $db->table('invoice_items')
            ->select('invoice_items.*, products.product_code, products.product_name, products.product_unit, products.product_hsn_code')
            ->join('products', 'products.product_id = invoice_items.ii_product_id')
            ->where('ii_invoice_id', $invoiceId)
            ->get()
            ->getResultArray();

        $inv['items'] = $items;
        return $inv;
    }

    public function updatePaymentStatus(int $invoiceId): void
    {
        $db = \Config\Database::connect();
        $inv = $this->find($invoiceId);
        if (!$inv) return;

        $paid = (float)$db->table('voucher_allocations')
            ->selectSum('va_amount', 'total_paid')
            ->where('va_reference_type', 'invoice')
            ->where('va_reference_id', $invoiceId)
            ->get()->getRowArray()['total_paid'] ?? 0;

        $total   = (float)$inv['invoice_total'];
        $balance = max(0.0, $total - $paid);

        $status = 'draft';
        if ($paid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } else {
            $status = 'sent';
        }

        $this->update($invoiceId, [
            'invoice_paid_amount' => $paid,
            'invoice_balance'     => $balance,
            'invoice_status'      => $status,
        ]);

        // Recalculate party balance
        $partyModel = new PartyModel();
        $partyModel->recalculatePartyBalance((int)$inv['invoice_party_id']);
    }
}