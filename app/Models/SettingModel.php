<?php

namespace App\Models;

use App\Core\CSModel;

class SettingModel extends CSModel
{
    protected $table            = 'settings';
    protected $primaryKey       = 'setting_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'setting_company_id',
        'setting_key',
        'setting_value',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public static function getSetting(string $key, $default = null, ?int $companyId = null)
    {
        $model = new self();
        $targetCompanyId = $companyId ?? (function_exists('session') && session()->has('company_id') ? (int)session()->get('company_id') : 1);

        $row = $model->where('setting_company_id', $targetCompanyId)->where('setting_key', $key)->first();
        if ($row) {
            return $row['setting_value'];
        }

        // Fallback to company 1 or null company setting
        $rowFallback = $model->where('setting_key', $key)->first();
        return $rowFallback ? $rowFallback['setting_value'] : $default;
    }

    public static function setSetting(string $key, $value, ?int $companyId = null): bool
    {
        $model = new self();
        $targetCompanyId = $companyId ?? (function_exists('session') && session()->has('company_id') ? (int)session()->get('company_id') : 1);

        $existing = $model->where('setting_company_id', $targetCompanyId)->where('setting_key', $key)->first();
        if ($existing) {
            return (bool)$model->update($existing['setting_id'], [
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
        return (bool)$model->insert([
            'setting_company_id' => $targetCompanyId,
            'setting_key'        => $key,
            'setting_value'      => $value,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getVal(string $key, $default = null)
    {
        return self::getSetting($key, $default);
    }

    public static function setVal(string $key, $value): bool
    {
        return self::setSetting($key, $value);
    }
}
