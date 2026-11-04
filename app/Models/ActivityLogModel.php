<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'log_company_id',
        'log_society_id',
        'log_user_id',
        'log_user_name',
        'log_user_email',
        'log_user_role',
        'log_action',
        'log_module',
        'log_description',
        'log_method',
        'log_url',
        'log_ip_address',
        'log_request_data',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Failsafe record log method.
     * Safely inserts an activity entry into the audit trail or application log without disrupting execution.
     */
    public static function recordLog(array $data): bool
    {
        try {
            $db = \Config\Database::connect();
            $tableName = 'activity_logs';
            $fullTable = $db->prefixTable($tableName);

            $action = strtoupper((string)($data['log_action'] ?? 'ACTION'));
            $module = (string)($data['log_module'] ?? 'System');
            $desc   = (string)($data['log_description'] ?? '');

            // Fallback log to CodeIgniter logger
            log_message('info', "[AUDIT] [{$module}] {$action}: {$desc}");

            // If table does not exist, do not fail
            if (!$db->tableExists($fullTable)) {
                return true;
            }

            $companyId = !empty($data['log_company_id']) ? (int)$data['log_company_id'] : (!empty($data['log_society_id']) ? (int)$data['log_society_id'] : 1);
            $requestData = $data['log_request_data'] ?? null;
            if (is_array($requestData)) {
                $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $entry = [
                'log_company_id'  => $companyId,
                'log_society_id'  => $companyId,
                'log_user_id'     => !empty($data['log_user_id']) ? (int)$data['log_user_id'] : null,
                'log_user_name'   => isset($data['log_user_name']) ? mb_substr((string)$data['log_user_name'], 0, 150) : 'System',
                'log_user_email'  => isset($data['log_user_email']) ? mb_substr((string)$data['log_user_email'], 0, 150) : null,
                'log_user_role'   => isset($data['log_user_role']) ? mb_substr((string)$data['log_user_role'], 0, 50) : 'super_admin',
                'log_action'      => mb_substr($action, 0, 50),
                'log_module'      => mb_substr($module, 0, 50),
                'log_description' => $desc,
                'log_method'      => isset($data['log_method']) ? strtoupper(mb_substr((string)$data['log_method'], 0, 10)) : 'POST',
                'log_url'         => isset($data['log_url']) ? mb_substr((string)$data['log_url'], 0, 255) : '',
                'log_ip_address'  => isset($data['log_ip_address']) ? mb_substr((string)$data['log_ip_address'], 0, 45) : '127.0.0.1',
                'log_request_data'=> $requestData,
                'created_at'      => date('Y-m-d H:i:s'),
            ];

            $db->table($tableName)->insert($entry);
            return true;
        } catch (\Throwable $e) {
            log_message('notice', 'ActivityLogModel exception: ' . $e->getMessage());
            return false;
        }
    }
}

