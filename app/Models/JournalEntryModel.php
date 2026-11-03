<?php

namespace App\Models;

use App\Core\CSModel;

class JournalEntryModel extends CSModel
{
    protected $table            = 'journal_entries';
    protected $primaryKey       = 'journal_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'journal_company_id',
        'journal_voucher_number',
        'journal_type',
        'journal_reference_type',
        'journal_reference_id',
        'journal_date',
        'journal_narration',
        'journal_is_posted',
        'journal_created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateVoucherNumber(string $type = 'journal', ?int $companyId = null): string
    {
        $prefixes = [
            'journal'  => 'JV',
            'payment'  => 'PV',
            'receipt'  => 'RV',
            'contra'   => 'CV',
            'sales'    => 'SJ',
            'purchase' => 'PJ',
        ];
        $p = $prefixes[$type] ?? 'JV';
        $year = date('Y');
        $builder = $this->where('journal_type', $type);
        if ($companyId) {
            $builder->where('journal_company_id', $companyId);
        }
        $count = $builder->countAllResults() + 1;
        return sprintf('%s-%s-%04d', $p, $year, $count);
    }

    /**
     * Post a double-entry transaction. Guarantees debits equal credits!
     */
    public function postTransaction(array $entryData, array $lines): int
    {
        $db = \Config\Database::connect();

        // Validate debits == credits
        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        foreach ($lines as $line) {
            $totalDebit  += (float)($line['jl_debit'] ?? 0);
            $totalCredit += (float)($line['jl_credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new \RuntimeException(sprintf(
                'Accounting error: Journal entry debits (%.2f) do not equal credits (%.2f)!',
                $totalDebit,
                $totalCredit
            ));
        }

        $db->transStart();

        if (empty($entryData['journal_voucher_number'])) {
            $entryData['journal_voucher_number'] = $this->generateVoucherNumber($entryData['journal_type'] ?? 'journal');
        }

        $this->insert($entryData);
        $journalId = $this->getInsertID();

        $accountModel = new AccountModel();
        $now = date('Y-m-d H:i:s');
        $jlTable = $db->table('journal_lines');

        foreach ($lines as $l) {
            $jlTable->insert([
                'jl_journal_id' => $journalId,
                'jl_account_id' => (int)$l['jl_account_id'],
                'jl_debit'      => (float)($l['jl_debit'] ?? 0),
                'jl_credit'     => (float)($l['jl_credit'] ?? 0),
                'jl_narration'  => $l['jl_narration'] ?? ($entryData['journal_narration'] ?? null),
                'created_at'    => $now,
            ]);
            // Recalculate account balance
            $accountModel->recalculateBalance((int)$l['jl_account_id']);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Failed to post journal entry transaction.');
        }

        return $journalId;
    }

    public function getWithLines(int $journalId): array
    {
        $db = \Config\Database::connect();
        $entry = $this->find($journalId);
        if (!$entry) return [];

        $lines = $db->table('journal_lines')
            ->select('journal_lines.*, accounts.account_code, accounts.account_name, accounts.account_type')
            ->join('accounts', 'accounts.account_id = journal_lines.jl_account_id')
            ->where('jl_journal_id', $journalId)
            ->get()
            ->getResultArray();

        $entry['lines'] = $lines;
        return $entry;
    }
}