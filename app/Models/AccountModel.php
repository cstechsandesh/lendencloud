<?php

namespace App\Models;

use App\Core\CSModel;

class AccountModel extends CSModel
{
    protected $table            = 'accounts';
    protected $primaryKey       = 'account_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'account_company_id',
        'account_group_id',
        'account_code',
        'account_name',
        'account_type',
        'account_balance_type',
        'account_opening_balance',
        'account_current_balance',
        'account_description',
        'account_is_system',
        'account_is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAccountsGrouped(?int $companyId = null): array
    {
        $db = \Config\Database::connect();
        $builder = $this->select('accounts.*, account_groups.group_name')
            ->join('account_groups', 'account_groups.group_id = accounts.account_group_id', 'left');

        if ($companyId) {
            $builder->where('accounts.account_company_id', $companyId);
        }

        $accounts = $builder->orderBy('accounts.account_code', 'ASC')->findAll();

        $grouped = [
            'asset'     => [],
            'liability' => [],
            'equity'    => [],
            'revenue'   => [],
            'expense'   => [],
        ];

        foreach ($accounts as $acc) {
            $type = strtolower($acc['account_type']);
            if (isset($grouped[$type])) {
                $grouped[$type][] = $acc;
            }
        }
        return $grouped;
    }

    public function recalculateBalance(int $accountId): float
    {
        $db = \Config\Database::connect();
        $account = $this->find($accountId);
        if (!$account) return 0.0;

        $lines = $db->table('journal_lines')
            ->selectSum('jl_debit', 'total_debit')
            ->selectSum('jl_credit', 'total_credit')
            ->where('jl_account_id', $accountId)
            ->get()
            ->getRowArray();

        $totalDebit  = (float)($lines['total_debit'] ?? 0);
        $totalCredit = (float)($lines['total_credit'] ?? 0);
        $opening     = (float)$account['account_opening_balance'];

        if ($account['account_balance_type'] === 'debit') {
            $current = $opening + $totalDebit - $totalCredit;
        } else {
            $current = $opening + $totalCredit - $totalDebit;
        }

        $this->update($accountId, ['account_current_balance' => $current]);
        return $current;
    }

    public function getLedger(int $accountId, ?string $from = null, ?string $to = null): array
    {
        $db = \Config\Database::connect();
        $account = $this->find($accountId);
        if (!$account) return ['account' => null, 'entries' => [], 'summary' => []];

        $builder = $db->table('journal_lines')
            ->select('journal_lines.*, journal_entries.journal_voucher_number, journal_entries.journal_type, journal_entries.journal_date, journal_entries.journal_narration')
            ->join('journal_entries', 'journal_entries.journal_id = journal_lines.jl_journal_id')
            ->where('journal_lines.jl_account_id', $accountId)
            ->orderBy('journal_entries.journal_date', 'ASC')
            ->orderBy('journal_lines.jl_id', 'ASC');

        if ($from) $builder->where('journal_entries.journal_date >=', $from);
        if ($to)   $builder->where('journal_entries.journal_date <=', $to);

        $entries = $builder->get()->getResultArray();

        // Calculate running balance
        $runningBalance = (float)$account['account_opening_balance'];
        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        foreach ($entries as &$entry) {
            $debit  = (float)$entry['jl_debit'];
            $credit = (float)$entry['jl_credit'];
            $totalDebit  += $debit;
            $totalCredit += $credit;

            if ($account['account_balance_type'] === 'debit') {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }
            $entry['running_balance'] = $runningBalance;
        }

        return [
            'account' => $account,
            'entries' => $entries,
            'summary' => [
                'opening_balance' => (float)$account['account_opening_balance'],
                'total_debit'     => $totalDebit,
                'total_credit'    => $totalCredit,
                'closing_balance' => $runningBalance,
            ]
        ];
    }
}