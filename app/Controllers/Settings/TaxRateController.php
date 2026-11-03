<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use App\Models\TaxRateModel;

class TaxRateController extends BaseController
{
    protected TaxRateModel $taxModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->taxModel = new TaxRateModel();
    }

    public function index()
    {
        $this->checkPermission('settings.view');
        $taxes = $this->taxModel->where('tax_company_id', $this->companyId)->findAll();
        return $this->render('settings/tax_rates/index', [
            'title' => 'GST & Tax Rates Master',
            'taxes' => $taxes,
        ]);
    }

    public function create()
    {
        $this->checkPermission('settings.edit');
        return $this->render('settings/tax_rates/create', ['title' => 'Add Tax Rate']);
    }

    public function store()
    {
        $this->checkPermission('settings.edit');
        $this->taxModel->insert([
            'tax_company_id' => $this->companyId,
            'tax_name'       => trim($this->request->getPost('tax_name')),
            'tax_rate'       => (float)$this->request->getPost('tax_rate'),
            'tax_is_active'  => 1,
        ]);
        return redirect()->to('/settings/tax-rates')->with('success', 'Tax rate registered.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('settings.edit');
        $tax = $this->taxModel->where('tax_id', $id)->where('tax_company_id', $this->companyId)->first();
        if (!$tax) {
            return redirect()->to('/settings/tax-rates')->with('error', 'Tax rate not found.');
        }

        return $this->render('settings/tax_rates/edit', [
            'title' => 'Edit Tax Rate: ' . $tax['tax_name'],
            'tax'   => $tax,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('settings.edit');
        $this->taxModel->update($id, [
            'tax_name'      => trim($this->request->getPost('tax_name') ?? ''),
            'tax_rate'      => (float)$this->request->getPost('tax_rate'),
            'tax_is_active' => $this->request->getPost('tax_is_active') ? 1 : 0,
        ]);
        return redirect()->to('/settings/tax-rates')->with('success', 'Tax rate updated successfully.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('settings.edit');

        $tax = $this->taxModel->where('tax_id', $id)
            ->where('tax_company_id', $this->companyId)
            ->first();

        if (!$tax) {
            return redirect()->to('/settings/tax-rates')->with('error', 'Tax rate not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();
        $prodCount = $db->table('products')->where('product_tax_rate_id', $id)->countAllResults();
        $invCount  = $db->table('invoice_items')->where('ii_tax_rate_id', $id)->countAllResults();
        $billCount = $db->table('bill_items')->where('bi_tax_rate_id', $id)->countAllResults();

        $reasons = [];
        if ($prodCount > 0) $reasons[] = "{$prodCount} product(s)";
        if ($invCount > 0)  $reasons[] = "{$invCount} sales invoice item(s)";
        if ($billCount > 0) $reasons[] = "{$billCount} purchase bill item(s)";

        if (!empty($reasons)) {
            return redirect()->to('/settings/tax-rates')->with('error', "Cannot delete tax rate \"{$tax['tax_name']} ({$tax['tax_rate']}%)\" because it is linked to: " . implode(', ', $reasons) . ". You may mark it as Inactive instead.");
        }

        $this->taxModel->delete($id);
        return redirect()->to('/settings/tax-rates')->with('success', "Tax rate \"{$tax['tax_name']}\" deleted successfully.");
    }
}