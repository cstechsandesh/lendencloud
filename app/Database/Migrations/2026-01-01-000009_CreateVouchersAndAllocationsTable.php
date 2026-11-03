<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVouchersAndAllocationsTable extends Migration
{
    public function up()
    {
        // 1. vouchers
        $this->forge->addField([
            'voucher_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'voucher_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'voucher_type' => [
                'type'       => 'ENUM',
                'constraint' => ['payment', 'receipt', 'contra'],
                'default'    => 'payment',
            ],
            'voucher_party_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'voucher_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'voucher_transfer_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'voucher_date' => [
                'type' => 'DATE',
            ],
            'voucher_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'voucher_payment_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'cheque', 'neft', 'upi', 'card', 'bank_transfer'],
                'default'    => 'bank_transfer',
            ],
            'voucher_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'voucher_narration' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'voucher_journal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'voucher_status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'posted', 'cancelled'],
                'default'    => 'posted',
            ],
            'voucher_created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('voucher_id', true);
        $this->forge->addUniqueKey('voucher_number');
        $this->forge->addKey('voucher_party_id');
        $this->forge->addKey('voucher_account_id');
        $this->forge->addKey('voucher_journal_id');
        $this->forge->createTable('vouchers', true);

        // 2. voucher_allocations
        $this->forge->addField([
            'va_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'va_voucher_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'va_reference_type' => [
                'type'       => 'ENUM',
                'constraint' => ['invoice', 'bill'],
            ],
            'va_reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'va_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('va_id', true);
        $this->forge->addKey('va_voucher_id');
        $this->forge->addKey(['va_reference_type', 'va_reference_id']);
        $this->forge->createTable('voucher_allocations', true);
    }

    public function down()
    {
        $this->forge->dropTable('voucher_allocations', true);
        $this->forge->dropTable('vouchers', true);
    }
}