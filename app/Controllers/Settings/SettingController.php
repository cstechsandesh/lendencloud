<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class SettingController extends BaseController
{
    public function index()
    {
        $this->checkPermission('settings.view');
        $settingModel = new SettingModel();
        $all = $settingModel->findAll();
        $settings = array_column($all, 'setting_value', 'setting_key');

        return $this->render('settings/index', [
            'title'    => 'Enterprise & Accounting Configuration',
            'settings' => $settings,
        ]);
    }

    public function save()
    {
        $this->checkPermission('settings.edit');
        $fields = [
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'company_gstin',
            'currency_symbol',
            'currency_code',
            'date_format',
            'financial_year_start',
        ];

        foreach ($fields as $field) {
            $val = $this->request->getPost($field);
            if ($val !== null) {
                SettingModel::setSetting($field, trim($val), $this->companyId);
            }
        }

        // Sync with companies table for active company
        $companyModel = new \App\Models\CompanyModel();
        $companyModel->update($this->companyId, [
            'company_name'                 => trim($this->request->getPost('company_name') ?? ''),
            'company_email'                => trim($this->request->getPost('company_email') ?? '') ?: null,
            'company_phone'                => trim($this->request->getPost('company_phone') ?? '') ?: null,
            'company_address'              => trim($this->request->getPost('company_address') ?? '') ?: null,
            'company_gstin'                => trim($this->request->getPost('company_gstin') ?? '') ?: null,
            'company_currency_symbol'      => trim($this->request->getPost('currency_symbol') ?: '₹'),
            'company_financial_year_start' => trim($this->request->getPost('financial_year_start') ?: '04-01'),
        ]);

        return redirect()->to('/settings')->with('success', 'Company and organization settings saved.');
    }
}