<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\ProductModel;
use App\Models\PartyModel;
use App\Models\JournalEntryModel;

class ReportController extends BaseController
{
    protected AccountModel $accountModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->accountModel = new AccountModel();
    }

    public function index()
    {
        $roles = $this->session->get('roles') ?? [];
        $isSuperAdmin = in_array('super_admin', $roles, true);

        $subModel = new \App\Models\SubscriptionModel();
        $sub = $subModel->getCompanySubscription($this->companyId);
        $planCode = $sub['plan_code'] ?? 'starter_business';

        if ($isSuperAdmin || \App\Models\SubscriptionPlanModel::planAllowsFeature($planCode, 'advanced_reports')) {
            return redirect()->to('/reports/trial-balance');
        }

        return redirect()->to('/reports/day-book');
    }

    public function trialBalance()
    {
        $this->checkPermission('reports.view');
        $this->checkPermission('accounts.view');
        $asOf = $this->request->getGet('as_of') ?: date('Y-m-d');
        $accounts = $this->accountModel->where('account_company_id', $this->companyId)->orderBy('account_code', 'ASC')->findAll();

        $rows = [];
        $grandDebit  = 0.0;
        $grandCredit = 0.0;

        foreach ($accounts as $acc) {
            $ledger = $this->accountModel->getLedger($acc['account_id'], null, $asOf);
            $bal = $ledger['summary']['closing_balance'];
            $debit = 0.0;
            $credit = 0.0;

            if ($acc['account_balance_type'] === 'debit') {
                if ($bal >= 0) $debit = $bal;
                else $credit = abs($bal);
            } else {
                if ($bal >= 0) $credit = $bal;
                else $debit = abs($bal);
            }

            if ($debit > 0 || $credit > 0) {
                $rows[] = [
                    'code'   => $acc['account_code'],
                    'name'   => $acc['account_name'],
                    'type'   => ucfirst($acc['account_type']),
                    'debit'  => $debit,
                    'credit' => $credit,
                ];
                $grandDebit  += $debit;
                $grandCredit += $credit;
            }
        }

        return $this->render('reports/trial_balance', [
            'title'        => 'Trial Balance Statement',
            'as_of'        => $asOf,
            'rows'         => $rows,
            'grand_debit'  => $grandDebit,
            'grand_credit' => $grandCredit,
            'is_balanced'  => (abs($grandDebit - $grandCredit) < 0.01),
        ]);
    }

    public function profitLoss()
    {
        $this->checkPermission('reports.view');
        $this->checkPermission('accounts.view');
        $from = $this->request->getGet('from') ?: date('Y-01-01');
        $to   = $this->request->getGet('to')   ?: date('Y-12-31');

        $revAccounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'revenue')->findAll();
        $expAccounts = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'expense')->findAll();

        $revenues = [];
        $totalRev = 0.0;
        foreach ($revAccounts as $r) {
            $bal = abs($r['account_current_balance']);
            if ($bal > 0) {
                $revenues[] = ['name' => $r['account_name'], 'amount' => $bal];
                $totalRev += $bal;
            }
        }

        $expenses = [];
        $totalExp = 0.0;
        foreach ($expAccounts as $e) {
            $bal = abs($e['account_current_balance']);
            if ($bal > 0) {
                $expenses[] = ['name' => $e['account_name'], 'amount' => $bal];
                $totalExp += $bal;
            }
        }

        $netProfit = $totalRev - $totalExp;

        return $this->render('reports/profit_loss', [
            'title'     => 'Profit & Loss Statement (Income Statement)',
            'from'      => $from,
            'to'        => $to,
            'revenues'  => $revenues,
            'expenses'  => $expenses,
            'total_rev' => $totalRev,
            'total_exp' => $totalExp,
            'net_profit'=> $netProfit,
        ]);
    }

    public function balanceSheet()
    {
        $this->checkPermission('reports.view');
        $this->checkPermission('accounts.view');
        $asOf = $this->request->getGet('as_of') ?: date('Y-m-d');

        $assets      = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'asset')->findAll();
        $liabilities = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'liability')->findAll();
        $equity      = $this->accountModel->where('account_company_id', $this->companyId)->where('account_type', 'equity')->findAll();

        $totalAssets = 0.0;
        foreach ($assets as &$a) {
            $totalAssets += (float)$a['account_current_balance'];
        }

        $totalLiab = 0.0;
        foreach ($liabilities as &$l) {
            $totalLiab += (float)$l['account_current_balance'];
        }

        $totalEquity = 0.0;
        foreach ($equity as &$eq) {
            $totalEquity += (float)$eq['account_current_balance'];
        }

        return $this->render('reports/balance_sheet', [
            'title'        => 'Balance Sheet Statement',
            'as_of'        => $asOf,
            'assets'       => $assets,
            'liabilities'  => $liabilities,
            'equity'       => $equity,
            'total_assets' => $totalAssets,
            'total_liab'   => $totalLiab,
            'total_equity' => $totalEquity,
            'total_claims' => $totalLiab + $totalEquity,
        ]);
    }

    public function dayBook()
    {
        $this->checkPermission('reports.view');
        $this->checkPermission('journals.view');
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $db = \Config\Database::connect();

        $linesTable = $db->prefixTable('journal_lines');
        $entries = $db->table('journal_entries je')
            ->select('je.*,
                (SELECT SUM(jl_debit) FROM ' . $linesTable . ' WHERE jl_journal_id = je.journal_id) as total_amount')
            ->where('je.journal_company_id', $this->companyId)
            ->where('je.journal_date', $date)
            ->orderBy('je.journal_id', 'ASC')
            ->get()->getResultArray();

        return $this->render('reports/day_book', [
            'title'   => 'Day Book Transactions Log',
            'date'    => $date,
            'entries' => $entries,
        ]);
    }

    public function stock()
    {
        $this->checkPermission('reports.view');
        $this->checkPermission('inventory.view');
        $productModel = new ProductModel();
        $products = $productModel->getProductsWithDetails($this->companyId);

        $totalValuation = 0.0;
        foreach ($products as &$p) {
            $p['valuation'] = (float)$p['product_current_stock'] * (float)$p['product_purchase_price'];
            $totalValuation += $p['valuation'];
        }

        return $this->render('reports/stock', [
            'title'           => 'Product Stock Valuation Report',
            'products'        => $products,
            'total_valuation' => $totalValuation,
        ]);
    }
}