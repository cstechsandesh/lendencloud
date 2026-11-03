<?php

namespace App\Models;

use App\Core\CSModel;

class PartyModel extends CSModel
{
    protected $table            = 'parties';
    protected $primaryKey       = 'party_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'party_company_id',
        'party_type',
        'party_name',
        'party_code',
        'party_email',
        'party_phone',
        'party_address',
        'party_gstin',
        'party_pan',
        'party_account_id',
        'party_credit_limit',
        'party_credit_days',
        'party_current_balance',
        'party_status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function recalculatePartyBalance(int $partyId): float
    {
        $db = \Config\Database::connect();
        $party = $this->find($partyId);
        if (!$party) return 0.0;

        // Invoices total balance for customers
        $invoiceBal = $db->table('invoices')
            ->selectSum('invoice_balance', 'total_inv_bal')
            ->where('invoice_party_id', $partyId)
            ->whereNotIn('invoice_status', ['cancelled', 'draft'])
            ->get()->getRowArray()['total_inv_bal'] ?? 0;

        // Bills total balance for vendors
        $billBal = $db->table('bills')
            ->selectSum('bill_balance', 'total_bill_bal')
            ->where('bill_party_id', $partyId)
            ->whereNotIn('bill_status', ['cancelled', 'draft'])
            ->get()->getRowArray()['total_bill_bal'] ?? 0;

        $net = (float)$invoiceBal - (float)$billBal;
        $this->update($partyId, ['party_current_balance' => $net]);
        return $net;
    }

    public function getStatement(int $partyId, ?string $from = null, ?string $to = null, ?int $companyId = null): array
    {
        $db = \Config\Database::connect();
        $partyQuery = $this->where('party_id', $partyId);
        if ($companyId) {
            $partyQuery->where('party_company_id', $companyId);
        }
        $party = $partyQuery->first();
        if (!$party) return ['party' => null, 'transactions' => [], 'closing' => 0.0];

        $transactions = [];

        // 1. Invoices (Debit for customer)
        $invBuilder = $db->table('invoices')
            ->select('invoice_date as tx_date, invoice_number as ref_no, invoice_total, invoice_notes as narration')
            ->where('invoice_party_id', $partyId)
            ->whereNotIn('invoice_status', ['cancelled']);
        if ($companyId) {
            $invBuilder->where('invoice_company_id', $companyId);
        }
        if ($from) $invBuilder->where('invoice_date >=', $from);
        if ($to)   $invBuilder->where('invoice_date <=', $to);
        $invoices = $invBuilder->get()->getResultArray();
        foreach ($invoices as $inv) {
            $transactions[] = [
                'tx_date'   => $inv['tx_date'],
                'ref_no'    => $inv['ref_no'],
                'tx_type'   => 'Invoice',
                'debit'     => (float)$inv['invoice_total'],
                'credit'    => 0.00,
                'narration' => $inv['narration'],
            ];
        }

        // 2. Bills (Credit for vendor)
        $billBuilder = $db->table('bills')
            ->select('bill_date as tx_date, bill_number as ref_no, bill_total, bill_notes as narration')
            ->where('bill_party_id', $partyId)
            ->whereNotIn('bill_status', ['cancelled']);
        if ($companyId) {
            $billBuilder->where('bill_company_id', $companyId);
        }
        if ($from) $billBuilder->where('bill_date >=', $from);
        if ($to)   $billBuilder->where('bill_date <=', $to);
        $bills = $billBuilder->get()->getResultArray();
        foreach ($bills as $bill) {
            $transactions[] = [
                'tx_date'   => $bill['tx_date'],
                'ref_no'    => $bill['ref_no'],
                'tx_type'   => 'Vendor Bill',
                'debit'     => 0.00,
                'credit'    => (float)$bill['bill_total'],
                'narration' => $bill['narration'],
            ];
        }

        // 3. Vouchers (Receipts = Credit, Payments = Debit)
        $vBuilder = $db->table('vouchers')
            ->select('voucher_date as tx_date, voucher_number as ref_no, voucher_type, voucher_amount, voucher_narration as narration')
            ->where('voucher_party_id', $partyId)
            ->whereNotIn('voucher_status', ['cancelled']);
        if ($companyId) {
            $vBuilder->where('voucher_company_id', $companyId);
        }
        if ($from) $vBuilder->where('voucher_date >=', $from);
        if ($to)   $vBuilder->where('voucher_date <=', $to);
        $vouchers = $vBuilder->get()->getResultArray();

        foreach ($vouchers as $v) {
            $isReceipt = ($v['voucher_type'] === 'receipt');
            $transactions[] = [
                'tx_date'   => $v['tx_date'],
                'ref_no'    => $v['ref_no'],
                'tx_type'   => ucfirst($v['voucher_type']) . ' Voucher',
                'debit'     => $isReceipt ? 0.00 : (float)$v['voucher_amount'],
                'credit'    => $isReceipt ? (float)$v['voucher_amount'] : 0.00,
                'narration' => $v['narration'],
            ];
        }

        // Sort by date
        usort($transactions, function($a, $b) {
            return strcmp($a['tx_date'], $b['tx_date']);
        });

        // Running balance
        $running = 0.0;
        foreach ($transactions as &$tx) {
            $running += ((float)$tx['debit'] - (float)$tx['credit']);
            $tx['running_balance'] = $running;
        }

        return [
            'party'        => $party,
            'transactions' => $transactions,
            'closing'      => $running,
        ];
    }
}