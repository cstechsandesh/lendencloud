<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\WarehouseModel;
use App\Models\StockMovementModel;
use App\Models\JournalEntryModel;
use App\Models\AccountModel;

class StockAdjustmentController extends BaseController
{
    public function index()
    {
        $this->checkPermission('inventory.view');
        $db = \Config\Database::connect();
        $adjustments = $db->table('stock_movements')
            ->select('stock_movements.*, products.product_name, products.product_code, products.product_unit, warehouses.warehouse_name')
            ->join('products', 'products.product_id = stock_movements.stock_product_id')
            ->join('warehouses', 'warehouses.warehouse_id = stock_movements.stock_warehouse_id')
            ->where('stock_type', 'adjustment')
            ->where('stock_movements.movement_company_id', $this->companyId)
            ->orderBy('stock_id', 'DESC')
            ->get()->getResultArray();

        $productModel   = new ProductModel();
        $warehouseModel = new WarehouseModel();

        $products   = $productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $warehouses = $warehouseModel->where('warehouse_company_id', $this->companyId)->where('warehouse_is_active', 1)->findAll();

        return $this->render('inventory/adjustments/index', [
            'title'       => 'Physical Stock Adjustments Log',
            'adjustments' => $adjustments,
            'products'    => $products,
            'warehouses'  => $warehouses,
        ]);
    }

    public function create()
    {
        $this->checkPermission('inventory.adjust');
        $productModel   = new ProductModel();
        $warehouseModel = new WarehouseModel();

        $products   = $productModel->where('product_company_id', $this->companyId)->where('product_is_active', 1)->findAll();
        $warehouses = $warehouseModel->where('warehouse_company_id', $this->companyId)->where('warehouse_is_active', 1)->findAll();

        return $this->render('inventory/adjustments/create', [
            'title'      => 'Post Physical Stock Adjustment',
            'products'   => $products,
            'warehouses' => $warehouses,
        ]);
    }

    public function store()
    {
        $this->checkPermission('inventory.adjust');
        $productId   = (int)$this->request->getPost('stock_product_id');
        $warehouseId = (int)$this->request->getPost('stock_warehouse_id');
        $adjType     = $this->request->getPost('adjustment_direction'); // 'increase' or 'decrease'
        $quantity    = (float)$this->request->getPost('stock_quantity');
        $notes       = $this->request->getPost('stock_notes');

        if ($productId <= 0 || $quantity <= 0) {
            return redirect()->back()->withInput()->with('error', 'Invalid product or quantity specified.');
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) return redirect()->back()->with('error', 'Product not found.');

        $cost = (float)$product['product_purchase_price'];
        $adjTotal = $quantity * $cost;

        $db = \Config\Database::connect();
        $db->transStart();

        $actualQty = ($adjType === 'increase') ? $quantity : -$quantity;

        // Record stock movement
        $smModel = new StockMovementModel();
        $movementId = $smModel->insert([
            'movement_company_id'  => $this->companyId,
            'stock_product_id'     => $productId,
            'stock_warehouse_id'   => $warehouseId,
            'stock_type'           => 'adjustment',
            'stock_quantity'       => $actualQty,
            'stock_unit_cost'      => $cost,
            'stock_reference_type' => 'adjustment',
            'stock_reference_id'   => null,
            'stock_notes'          => $notes ?: 'Physical inventory count adjustment (' . $adjType . ')',
            'stock_date'           => date('Y-m-d H:i:s'),
            'stock_created_by'     => $this->session->get('user_id'),
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        $productModel->recalculateStock($productId);

        // Post Journal Entry for accounting adjustment
        if ($adjTotal > 0) {
            $accountModel = new AccountModel();
            $stockAccount = $accountModel->where('account_company_id', $this->companyId)->where('account_code', '1200')->first()
                ?? $accountModel->where('account_company_id', $this->companyId)->where('account_code', '1030')->first(); // Inventory Asset
            $adjAccount   = $accountModel->where('account_company_id', $this->companyId)->where('account_code', '5200')->first()
                ?? $accountModel->where('account_company_id', $this->companyId)->where('account_code', '5000')->first(); // Stock Adjustment / COGS

            if ($stockAccount && $adjAccount) {
                $journalModel = new JournalEntryModel();
                $entryData = [
                    'journal_company_id'     => $this->companyId,
                    'journal_voucher_number' => $journalModel->generateVoucherNumber('journal'),
                    'journal_type'           => 'journal',
                    'journal_reference_type' => 'stock_adjustment',
                    'journal_reference_id'   => $movementId,
                    'journal_date'           => date('Y-m-d'),
                    'journal_narration'      => 'Stock adjustment for ' . $product['product_name'] . ' (' . $notes . ')',
                    'journal_is_posted'      => 1,
                    'journal_created_by'     => $this->session->get('user_id'),
                ];

                if ($adjType === 'increase') {
                    // Increase: DR Stock Asset, CR Stock Adjustment
                    $lines = [
                        ['jl_account_id' => $stockAccount['account_id'], 'jl_debit' => $adjTotal, 'jl_credit' => 0.00],
                        ['jl_account_id' => $adjAccount['account_id'],   'jl_debit' => 0.00,     'jl_credit' => $adjTotal],
                    ];
                } else {
                    // Decrease: DR Stock Adjustment Expense, CR Stock Asset
                    $lines = [
                        ['jl_account_id' => $adjAccount['account_id'],   'jl_debit' => $adjTotal, 'jl_credit' => 0.00],
                        ['jl_account_id' => $stockAccount['account_id'], 'jl_debit' => 0.00,     'jl_credit' => $adjTotal],
                    ];
                }
                $journalModel->postTransaction($entryData, $lines);
            }
        }

        $db->transComplete();

        return redirect()->to('/inventory/adjustments')->with('success', 'Stock adjustment and financial ledger updated.');
    }
}