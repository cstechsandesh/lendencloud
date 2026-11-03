<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ScopeUniqueKeysByCompany extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $tablesAndKeys = [
            'accounts' => [
                'old_index' => 'account_code',
                'new_index' => 'uq_company_account_code',
                'columns'   => ['account_company_id', 'account_code'],
            ],
            'products' => [
                'old_index' => 'product_code',
                'new_index' => 'uq_company_product_code',
                'columns'   => ['product_company_id', 'product_code'],
            ],
            'journal_entries' => [
                'old_index' => 'journal_voucher_number',
                'new_index' => 'uq_company_journal_voucher_num',
                'columns'   => ['journal_company_id', 'journal_voucher_number'],
            ],
            'sales_orders' => [
                'old_index' => 'so_number',
                'new_index' => 'uq_company_so_number',
                'columns'   => ['so_company_id', 'so_number'],
            ],
            'invoices' => [
                'old_index' => 'invoice_number',
                'new_index' => 'uq_company_invoice_number',
                'columns'   => ['invoice_company_id', 'invoice_number'],
            ],
            'purchase_orders' => [
                'old_index' => 'po_number',
                'new_index' => 'uq_company_po_number',
                'columns'   => ['po_company_id', 'po_number'],
            ],
            'bills' => [
                'old_index' => 'bill_number',
                'new_index' => 'uq_company_bill_number',
                'columns'   => ['bill_company_id', 'bill_number'],
            ],
            'vouchers' => [
                'old_index' => 'voucher_number',
                'new_index' => 'uq_company_voucher_number',
                'columns'   => ['voucher_company_id', 'voucher_number'],
            ],
            'settings' => [
                'old_index' => 'setting_key',
                'new_index' => 'uq_company_setting_key',
                'columns'   => ['setting_company_id', 'setting_key'],
            ],
        ];

        foreach ($tablesAndKeys as $table => $cfg) {
            $realTable = $db->prefixTable($table);
            if (!$db->tableExists($table)) {
                continue;
            }

            $indexes = $db->query("SHOW INDEX FROM `{$realTable}`")->getResultArray();
            $indexNames = array_column($indexes, 'Key_name');

            if (in_array($cfg['old_index'], $indexNames, true)) {
                $db->query("ALTER TABLE `{$realTable}` DROP INDEX `{$cfg['old_index']}`");
            }

            if (!in_array($cfg['new_index'], $indexNames, true)) {
                $colList = implode('`, `', $cfg['columns']);
                $db->query("ALTER TABLE `{$realTable}` ADD UNIQUE KEY `{$cfg['new_index']}` (`{$colList}`)");
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        // Down can revert if ever needed
    }
}

