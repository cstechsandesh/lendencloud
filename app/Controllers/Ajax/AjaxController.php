<?php

namespace App\Controllers\Ajax;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\InvoiceModel;
use App\Models\BillModel;
use App\Models\AccountModel;

class AjaxController extends BaseController
{
    public function getProduct(int $id)
    {
        $productModel = new ProductModel();
        $product = $productModel->select('products.*, tax_rates.tax_rate')
            ->join('tax_rates', 'tax_rates.tax_id = products.product_tax_rate_id', 'left')
            ->where('product_id', $id)
            ->first();

        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
        }

        return $this->response->setJSON([
            'product_id'     => (int)$product['product_id'],
            'product_code'   => $product['product_code'],
            'product_name'   => $product['product_name'],
            'product_unit'   => $product['product_unit'],
            'selling_price'  => (float)$product['product_selling_price'],
            'purchase_price' => (float)$product['product_purchase_price'],
            'tax_rate_id'    => (int)$product['product_tax_rate_id'],
            'tax_rate'       => (float)($product['tax_rate'] ?? 0),
            'current_stock'  => (float)$product['product_current_stock'],
        ]);
    }

    public function getPartyInvoices(int $partyId)
    {
        $invoiceModel = new InvoiceModel();
        $invoices = $invoiceModel->where('invoice_party_id', $partyId)
            ->where('invoice_balance >', 0)
            ->whereNotIn('invoice_status', ['cancelled'])
            ->orderBy('invoice_date', 'ASC')
            ->findAll();

        return $this->response->setJSON($invoices);
    }

    public function getPartyBills(int $partyId)
    {
        $billModel = new BillModel();
        $bills = $billModel->where('bill_party_id', $partyId)
            ->where('bill_balance >', 0)
            ->whereNotIn('bill_status', ['cancelled'])
            ->orderBy('bill_date', 'ASC')
            ->findAll();

        return $this->response->setJSON($bills);
    }

    public function getAccountBalance(int $accountId)
    {
        $accountModel = new AccountModel();
        $account = $accountModel->where('account_id', $accountId)
            ->where('account_company_id', $this->companyId)
            ->first();

        if (!$account) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Account not found']);
        }

        return $this->response->setJSON([
            'account_id'      => (int)$account['account_id'],
            'account_code'    => $account['account_code'],
            'account_name'    => $account['account_name'],
            'balance_type'    => $account['account_balance_type'],
            'current_balance' => (float)$account['account_current_balance'],
        ]);
    }
}