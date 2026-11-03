<?php

namespace App\Models;

use App\Core\CSModel;

class VoucherModel extends CSModel
{
    protected $table            = 'vouchers';
    protected $primaryKey       = 'voucher_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'voucher_company_id',
        'voucher_number',
        'voucher_type',
        'voucher_party_id',
        'voucher_account_id',
        'voucher_transfer_account_id',
        'voucher_date',
        'voucher_amount',
        'voucher_payment_mode',
        'voucher_reference',
        'voucher_narration',
        'voucher_journal_id',
        'voucher_status',
        'voucher_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateVoucherNumber(string $type = 'payment', ?int $companyId = null): string
    {
        $prefixes = [
            'payment' => 'PAY',
            'receipt' => 'REC',
            'contra'  => 'CON',
        ];
        $p = $prefixes[$type] ?? 'VCH';
        $year = date('Y');
        $builder = $this->where('voucher_type', $type);
        if ($companyId) {
            $builder->where('voucher_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('%s-%s-%04d', $p, $year, $count);
    }

    public function getWithDetails(int $voucherId): array
    {
        $db = \Config\Database::connect();
        $voucher = $this->select('vouchers.*, parties.party_name, parties.party_code, accounts.account_name, accounts.account_code, xfer.account_name as transfer_account_name')
            ->join('parties', 'parties.party_id = vouchers.voucher_party_id', 'left')
            ->join('accounts', 'accounts.account_id = vouchers.voucher_account_id')
            ->join('accounts as xfer', 'xfer.account_id = vouchers.voucher_transfer_account_id', 'left')
            ->where('voucher_id', $voucherId)
            ->first();

        if (!$voucher) return [];

        $allocations = $db->table('voucher_allocations')
            ->where('va_voucher_id', $voucherId)
            ->get()
            ->getResultArray();

        $voucher['allocations'] = $allocations;
        return $voucher;
    }
}