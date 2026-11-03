<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\InvoiceModel;
use App\Models\BillModel;
use App\Models\ProductModel;
use App\Models\PartyModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $this->checkPermission('dashboard.view');

        $db = \Config\Database::connect();
        $startOfMonth = date('Y-m-01');
        $endOfMonth   = date('Y-m-t');

        // Revenue (sum of posted invoices this month)
        $revenue = (float)($db->table('invoices')
            ->selectSum('invoice_total', 'rev')
            ->where('invoice_company_id', $this->companyId)
            ->where('invoice_date >=', $startOfMonth)
            ->where('invoice_date <=', $endOfMonth)
            ->whereNotIn('invoice_status', ['cancelled'])
            ->get()->getRowArray()['rev'] ?? 0);

        // Purchases (sum of bills this month)
        $expenses = (float)($db->table('bills')
            ->selectSum('bill_total', 'exp')
            ->where('bill_company_id', $this->companyId)
            ->where('bill_date >=', $startOfMonth)
            ->where('bill_date <=', $endOfMonth)
            ->whereNotIn('bill_status', ['cancelled'])
            ->get()->getRowArray()['exp'] ?? 0);

        // Receivables balance (account 1100 or 1020)
        $accountModel = new AccountModel();
        $arAccount = $accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['1100', '1020'])->first();
        $arBalance = $arAccount ? (float)$arAccount['account_current_balance'] : 0.0;

        // Payables balance (account 2000 or 2010)
        $apAccount = $accountModel->where('account_company_id', $this->companyId)->whereIn('account_code', ['2000', '2010'])->first();
        $apBalance = $apAccount ? (float)$apAccount['account_current_balance'] : 0.0;

        // Cash & Bank balances
        $cashAcc = $accountModel->where('account_company_id', $this->companyId)->where('account_code', '1000')->first();
        $cashBalance = $cashAcc ? (float)$cashAcc['account_current_balance'] : 0.0;

        $bankAcc = $accountModel->where('account_company_id', $this->companyId)->where('account_code', '1010')->first();
        $bankBalance = $bankAcc ? (float)$bankAcc['account_current_balance'] : 0.0;

        // Total stock valuation
        $productModel = new ProductModel();
        $products = $productModel->where('product_company_id', $this->companyId)->findAll();
        $totalStockValue = 0.0;
        foreach ($products as $p) {
            $totalStockValue += ((float)$p['product_current_stock'] * (float)$p['product_purchase_price']);
        }

        // Low stock products
        $lowStockProducts = $productModel->getLowStockProducts($this->companyId);

        // Recent Invoices (5)
        $invoiceModel = new InvoiceModel();
        $recentInvoices = $invoiceModel->select('invoices.*, parties.party_name')
            ->join('parties', 'parties.party_id = invoices.invoice_party_id')
            ->where('invoices.invoice_company_id', $this->companyId)
            ->orderBy('invoice_id', 'DESC')
            ->limit(5)
            ->findAll();

        // Recent Bills (5)
        $billModel = new BillModel();
        $recentBills = $billModel->select('bills.*, parties.party_name')
            ->join('parties', 'parties.party_id = bills.bill_party_id')
            ->where('bills.bill_company_id', $this->companyId)
            ->orderBy('bill_id', 'DESC')
            ->limit(5)
            ->findAll();

        // Monthly revenue & expense chart data (last 6 months)
        $chartMonths = [];
        $revenueData = [];
        $expenseData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = date('Y-m-01', strtotime("-$i months"));
            $mEnd   = date('Y-m-t', strtotime("-$i months"));
            $mLabel = date('M Y', strtotime("-$i months"));
            $chartMonths[] = $mLabel;

            $mRev = (float)($db->table('invoices')
                ->selectSum('invoice_total', 'total')
                ->where('invoice_company_id', $this->companyId)
                ->where('invoice_date >=', $mStart)
                ->where('invoice_date <=', $mEnd)
                ->whereNotIn('invoice_status', ['cancelled'])
                ->get()->getRowArray()['total'] ?? 0);
            $revenueData[] = $mRev;

            $mExp = (float)($db->table('bills')
                ->selectSum('bill_total', 'total')
                ->where('bill_company_id', $this->companyId)
                ->where('bill_date >=', $mStart)
                ->where('bill_date <=', $mEnd)
                ->whereNotIn('bill_status', ['cancelled'])
                ->get()->getRowArray()['total'] ?? 0);
            $expenseData[] = $mExp;
        }

        return $this->render('dashboard/index', [
            'title'              => 'Executive Financial Dashboard',
            'totalRevenue'       => $revenue,
            'totalExpenses'      => $expenses,
            'accountsReceivable' => $arBalance,
            'accountsPayable'    => $apBalance,
            'cashBalance'        => $cashBalance,
            'bankBalance'        => $bankBalance,
            'totalStockValue'    => $totalStockValue,
            'lowStockProducts'   => $lowStockProducts,
            'recentInvoices'     => $recentInvoices,
            'recentBills'        => $recentBills,
            'chartMonths'        => $chartMonths,
            'revenueData'        => $revenueData,
            'expenseData'        => $expenseData,
        ]);
    }
}
