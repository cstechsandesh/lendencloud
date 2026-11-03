<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\TaxRateModel;
use App\Models\StockMovementModel;

class ProductController extends BaseController
{
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected TaxRateModel $taxModel;
    protected StockMovementModel $stockModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->productModel  = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->taxModel      = new TaxRateModel();
        $this->stockModel    = new StockMovementModel();
    }

    public function index()
    {
        $this->checkPermission('inventory.view');
        $products   = $this->productModel->getProductsWithDetails($this->companyId);
        $categories = $this->categoryModel->where('category_company_id', $this->companyId)->findAll();
        $taxRates   = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('inventory/index', [
            'title'      => 'Products Catalog & Inventory Stock',
            'products'   => $products,
            'categories' => $categories,
            'taxRates'   => $taxRates,
        ]);
    }

    public function create()
    {
        $this->checkPermission('inventory.create');
        $this->checkPlanQuota('products');
        $categories = $this->categoryModel->where('category_company_id', $this->companyId)->findAll();
        $taxRates   = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('inventory/products/create', [
            'title'      => 'Add New Product to Catalog',
            'categories' => $categories,
            'taxRates'   => $taxRates,
        ]);
    }

    public function store()
    {
        $this->checkPermission('inventory.create');
        $this->checkPlanQuota('products');
        $rules = [
            'product_name'           => 'required|min_length[2]|max_length[200]',
            'product_selling_price'  => 'required|decimal',
            'product_purchase_price' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = trim($this->request->getPost('product_code') ?? '');
        if (empty($code)) {
            $count = $this->productModel->where('product_company_id', $this->companyId)->countAllResults() + 1;
            $code = sprintf('PRD-%04d', $count);
        }

        $openingStock = (float)$this->request->getPost('product_opening_stock');

        $productId = $this->productModel->insert([
            'product_company_id'     => $this->companyId,
            'product_category_id'    => $this->request->getPost('product_category_id') ?: null,
            'product_code'           => $code,
            'product_name'           => trim($this->request->getPost('product_name') ?? ''),
            'product_description'    => $this->request->getPost('product_description'),
            'product_hsn_code'       => trim($this->request->getPost('product_hsn_code') ?? '') ?: null,
            'product_unit'           => $this->request->getPost('product_unit') ?: 'Nos',
            'product_purchase_price' => (float)$this->request->getPost('product_purchase_price'),
            'product_selling_price'  => (float)$this->request->getPost('product_selling_price'),
            'product_tax_rate_id'    => $this->request->getPost('product_tax_rate_id') ?: null,
            'product_current_stock'  => $openingStock,
            'product_reorder_level'  => (float)$this->request->getPost('product_reorder_level'),
            'product_is_active'      => 1,
        ]);

        if ($openingStock > 0) {
            $warehouse = (new \App\Models\WarehouseModel())->where('warehouse_company_id', $this->companyId)->first();
            $warehouseId = $warehouse ? (int)$warehouse['warehouse_id'] : 1;

            $this->stockModel->recordMovement([
                'movement_company_id'  => $this->companyId,
                'stock_product_id'     => $productId,
                'stock_warehouse_id'   => $warehouseId,
                'stock_type'           => 'in',
                'stock_quantity'       => $openingStock,
                'stock_unit_cost'      => (float)$this->request->getPost('product_purchase_price'),
                'stock_reference_type' => 'opening_stock',
                'stock_reference_id'   => null,
                'stock_notes'          => 'Initial stock on product creation',
                'stock_created_by'     => $this->session->get('user_id'),
            ]);
        }

        return redirect()->to('/inventory')->with('success', 'Product registered successfully.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('inventory.edit');
        $product = $this->productModel->find($id);
        if (!$product) return redirect()->to('/inventory')->with('error', 'Product not found.');

        $categories = $this->categoryModel->where('category_company_id', $this->companyId)->findAll();
        $taxRates   = $this->taxModel->where('tax_company_id', $this->companyId)->where('tax_is_active', 1)->findAll();

        return $this->render('inventory/products/edit', [
            'title'      => 'Edit Product: ' . $product['product_name'],
            'product'    => $product,
            'categories' => $categories,
            'taxRates'   => $taxRates,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('inventory.edit');
        $rules = [
            'product_name'          => 'required|min_length[2]|max_length[200]',
            'product_selling_price' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->productModel->update($id, [
            'product_category_id'    => $this->request->getPost('product_category_id') ?: null,
            'product_code'           => trim($this->request->getPost('product_code') ?? ''),
            'product_name'           => trim($this->request->getPost('product_name') ?? ''),
            'product_description'    => $this->request->getPost('product_description'),
            'product_hsn_code'       => trim($this->request->getPost('product_hsn_code') ?? '') ?: null,
            'product_unit'           => $this->request->getPost('product_unit') ?: 'Nos',
            'product_purchase_price' => (float)$this->request->getPost('product_purchase_price'),
            'product_selling_price'  => (float)$this->request->getPost('product_selling_price'),
            'product_tax_rate_id'    => $this->request->getPost('product_tax_rate_id') ?: null,
            'product_reorder_level'  => (float)$this->request->getPost('product_reorder_level'),
            'product_is_active'      => $this->request->getPost('product_is_active') ? 1 : 0,
        ]);

        return redirect()->to('/inventory')->with('success', 'Product updated.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('inventory.delete');

        $product = $this->productModel->where('product_id', $id)
            ->where('product_company_id', $this->companyId)
            ->first();

        if (!$product) {
            return redirect()->to('/inventory')->with('error', 'Product not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();

        // Check if product is used in billing, procurement, orders, or stock ledger
        $invCount   = $db->table('invoice_items')->where('ii_product_id', $id)->countAllResults();
        $billCount  = $db->table('bill_items')->where('bi_product_id', $id)->countAllResults();
        $soCount    = $db->table('so_items')->where('soi_product_id', $id)->countAllResults();
        $poCount    = $db->table('po_items')->where('poi_product_id', $id)->countAllResults();
        $stockCount = $db->table('stock_movements')->where('stock_product_id', $id)->countAllResults();
        $curStock   = (float)($product['product_current_stock'] ?? 0);

        $reasons = [];
        if ($invCount > 0)   $reasons[] = "{$invCount} sales invoice item(s)";
        if ($billCount > 0)  $reasons[] = "{$billCount} purchase bill item(s)";
        if ($soCount > 0)    $reasons[] = "{$soCount} sales order item(s)";
        if ($poCount > 0)    $reasons[] = "{$poCount} purchase order item(s)";
        if ($stockCount > 0) $reasons[] = "{$stockCount} stock ledger movement(s)";
        if (abs($curStock) > 0.001) $reasons[] = "current on-hand stock of " . number_format($curStock, 2) . " " . esc($product['product_unit']);

        if (!empty($reasons)) {
            return redirect()->to('/inventory')->with('error', "Cannot delete product \"{$product['product_name']}\" because it is used in: " . implode(', ', $reasons) . ". You may mark it as inactive to prevent further selection.");
        }

        $this->productModel->delete($id);
        return redirect()->to('/inventory')->with('success', "Product \"{$product['product_name']}\" deleted successfully.");
    }

    public function stock(int $id)
    {
        $this->checkPermission('inventory.view');
        $product = $this->productModel->find($id);
        if (!$product) return redirect()->to('/inventory')->with('error', 'Product not found.');

        $movements = $this->stockModel->select('stock_movements.*, warehouses.warehouse_name')
            ->join('warehouses', 'warehouses.warehouse_id = stock_movements.stock_warehouse_id', 'left')
            ->where('stock_product_id', $id)
            ->orderBy('stock_id', 'DESC')
            ->findAll();

        return $this->render('inventory/products/stock', [
            'title'     => 'Stock Movement Ledger: ' . $product['product_name'],
            'product'   => $product,
            'movements' => $movements,
        ]);
    }
}