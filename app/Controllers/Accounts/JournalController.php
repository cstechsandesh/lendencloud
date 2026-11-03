<?php

namespace App\Controllers\Accounts;

use App\Controllers\BaseController;
use App\Models\JournalEntryModel;
use App\Models\AccountModel;

class JournalController extends BaseController
{
    protected JournalEntryModel $journalModel;
    protected AccountModel $accountModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->journalModel = new JournalEntryModel();
        $this->accountModel = new AccountModel();
    }

    public function index()
    {
        $this->checkPermission('journals.view');
        $db = \Config\Database::connect();
        $pJournal = $db->prefixTable('journal_entries');
        $pLines   = $db->prefixTable('journal_lines');
        $entries = $this->journalModel->select("{$pJournal}.*,
            (SELECT SUM(jl_debit) FROM {$pLines} WHERE jl_journal_id = {$pJournal}.journal_id) as total_amount")
            ->where("{$pJournal}.journal_company_id", $this->companyId)
            ->orderBy("{$pJournal}.journal_id", 'DESC')
            ->findAll();

        return $this->render('journals/index', [
            'title'   => 'Double-Entry Journal Entries',
            'entries' => $entries,
        ]);
    }

    public function create()
    {
        $this->checkPermission('journals.create');
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_is_active', 1)->orderBy('account_code', 'ASC')->findAll();

        return $this->render('journals/create', [
            'title'          => 'New Journal Voucher (Double-Entry)',
            'accounts'       => $accounts,
            'voucher_number' => $this->journalModel->generateVoucherNumber('journal', $this->companyId),
        ]);
    }

    public function store()
    {
        $this->checkPermission('journals.create');
        $date      = $this->request->getPost('journal_date') ?: date('Y-m-d');
        $narration = $this->request->getPost('journal_narration');
        $type      = $this->request->getPost('journal_type') ?: 'journal';

        $accountIds = $this->request->getPost('account_id') ?? [];
        $debits     = $this->request->getPost('debit') ?? [];
        $credits    = $this->request->getPost('credit') ?? [];
        $lineNotes  = $this->request->getPost('line_narration') ?? [];

        $lines = [];
        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        for ($i = 0; $i < count($accountIds); $i++) {
            $accId = (int)$accountIds[$i];
            if (!$accId) continue;

            $d = (float)($debits[$i] ?? 0);
            $c = (float)($credits[$i] ?? 0);
            if ($d <= 0 && $c <= 0) continue;

            $totalDebit  += $d;
            $totalCredit += $c;

            $lines[] = [
                'jl_account_id' => $accId,
                'jl_debit'      => $d,
                'jl_credit'     => $c,
                'jl_narration'  => $lineNotes[$i] ?? $narration,
            ];
        }

        if (count($lines) < 2) {
            return redirect()->back()->withInput()->with('error', 'A double-entry journal voucher requires at least two account lines.');
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return redirect()->back()->withInput()->with('error', sprintf(
                'Accounting Mismatch! Total Debits (%.2f) must strictly equal Total Credits (%.2f).',
                $totalDebit,
                $totalCredit
            ));
        }

        try {
            $entryData = [
                'journal_company_id'     => $this->companyId,
                'journal_voucher_number' => $this->journalModel->generateVoucherNumber($type, $this->companyId),
                'journal_type'           => $type,
                'journal_date'           => $date,
                'journal_narration'      => $narration,
                'journal_is_posted'      => 1,
                'journal_created_by'     => $this->session->get('user_id'),
            ];

            $journalId = $this->journalModel->postTransaction($entryData, $lines);
            return redirect()->to('/journals/view/' . $journalId)->with('success', 'Journal entry posted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function view(int $id)
    {
        $this->checkPermission('journals.view');
        $entry = $this->journalModel->getWithLines($id);
        if (!$entry) return redirect()->to('/journals')->with('error', 'Journal entry not found.');

        return $this->render('journals/view', [
            'title' => 'Journal Voucher: ' . $entry['journal_voucher_number'],
            'entry' => $entry,
        ]);
    }
}