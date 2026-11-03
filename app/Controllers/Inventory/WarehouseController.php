<?php

namespace App\Controllers\Inventory;

use App\Controllers\BaseController;
use App\Models\WarehouseModel;

class WarehouseController extends BaseController
{
    protected WarehouseModel $warehouseModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->warehouseModel = new WarehouseModel();
    }

    public function index()
    {
        $this->checkPermission('inventory.view');
        $warehouses = $this->warehouseModel->where('warehouse_company_id', $this->companyId)->findAll();
        return $this->render('inventory/warehouses/index', [
            'title'      => 'Warehouses & Storage Locations',
            'warehouses' => $warehouses,
        ]);
    }

    public function create()
    {
        $this->checkPermission('inventory.create');
        $this->checkPlanQuota('warehouses');
        return $this->render('inventory/warehouses/create', [
            'title' => 'Register New Warehouse',
        ]);
    }

    public function store()
    {
        $this->checkPermission('inventory.create');
        $this->checkPlanQuota('warehouses');
        $this->warehouseModel->insert([
            'warehouse_company_id' => $this->companyId,
            'warehouse_name'       => trim($this->request->getPost('warehouse_name')),
            'warehouse_location'   => $this->request->getPost('warehouse_location'),
            'warehouse_is_active'  => 1,
        ]);
        return redirect()->to('/inventory/warehouses')->with('success', 'Warehouse registered.');
    }

    public function update(int $id)
    {
        $this->checkPermission('inventory.edit');
        $this->warehouseModel->update($id, [
            'warehouse_name'      => trim($this->request->getPost('warehouse_name') ?? ''),
            'warehouse_location'  => $this->request->getPost('warehouse_location'),
            'warehouse_is_active' => $this->request->getPost('warehouse_is_active') ? 1 : 0,
        ]);
        return redirect()->to('/inventory/warehouses')->with('success', 'Warehouse updated successfully.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('inventory.delete');

        $wh = $this->warehouseModel->where('warehouse_id', $id)
            ->where('warehouse_company_id', $this->companyId)
            ->first();

        if (!$wh) {
            return redirect()->to('/inventory/warehouses')->with('error', 'Warehouse not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();
        $smCount = $db->table('stock_movements')->where('stock_warehouse_id', $id)->countAllResults();

        if ($smCount > 0) {
            return redirect()->to('/inventory/warehouses')->with('error', "Cannot delete warehouse \"{$wh['warehouse_name']}\" because it is referenced in {$smCount} stock movement transaction(s). You can change its status to Inactive instead.");
        }

        $this->warehouseModel->delete($id);
        return redirect()->to('/inventory/warehouses')->with('success', "Warehouse \"{$wh['warehouse_name']}\" deleted successfully.");
    }
}