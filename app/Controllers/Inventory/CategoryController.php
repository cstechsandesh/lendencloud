<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected CategoryModel $catModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->catModel = new CategoryModel();
    }

    public function index()
    {
        $this->checkPermission('inventory.view');
        $categories = $this->catModel->where('category_company_id', $this->companyId)->findAll();
        return $this->render('inventory/categories/index', [
            'title'      => 'Product Categories',
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->checkPermission('inventory.create');
        return $this->render('inventory/categories/create', [
            'title' => 'Create Category',
        ]);
    }

    public function store()
    {
        $this->checkPermission('inventory.create');
        $this->catModel->insert([
            'category_company_id' => $this->companyId,
            'category_name'       => trim($this->request->getPost('category_name')),
        ]);
        return redirect()->to('/inventory/categories')->with('success', 'Category saved.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('inventory.edit');
        $cat = $this->catModel->find($id);
        return $this->render('inventory/categories/edit', [
            'title'    => 'Edit Category',
            'category' => $cat,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('inventory.edit');
        $this->catModel->update($id, [
            'category_name' => trim($this->request->getPost('category_name')),
        ]);
        return redirect()->to('/inventory/categories')->with('success', 'Category updated.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('inventory.delete');

        $cat = $this->catModel->where('category_id', $id)
            ->where('category_company_id', $this->companyId)
            ->first();

        if (!$cat) {
            return redirect()->to('/inventory/categories')->with('error', 'Category not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();
        $prodCount = $db->table('products')->where('product_category_id', $id)->countAllResults();

        if ($prodCount > 0) {
            return redirect()->to('/inventory/categories')->with('error', "Cannot delete category \"{$cat['category_name']}\" because it is currently assigned to {$prodCount} product(s). Reassign those products to another category first.");
        }

        $this->catModel->delete($id);
        return redirect()->to('/inventory/categories')->with('success', "Category \"{$cat['category_name']}\" deleted successfully.");
    }
}