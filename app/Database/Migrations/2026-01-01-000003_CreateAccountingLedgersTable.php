<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountingLedgersTable extends Migration
{
    public function up()
    {
        // 1. account_groups
        $this->forge->addField([
            'group_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'group_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'group_type' => [
                'type'       => 'ENUM',
                'constraint' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
            ],
            'group_parent_id' => [
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
        $this->forge->addKey('group_id', true);
        $this->forge->addKey('group_type');
        $this->forge->createTable('account_groups', true);

        // 2. accounts
        $this->forge->addField([
            'account_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'account_group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'account_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'account_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'account_type' => [
                'type'       => 'ENUM',
                'constraint' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
            ],
            'account_balance_type' => [
                'type'       => 'ENUM',
                'constraint' => ['debit', 'credit'],
                'default'    => 'debit',
            ],
            'account_opening_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'account_current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'account_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'account_is_system' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'account_is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('account_id', true);
        $this->forge->addKey('account_group_id');
        $this->forge->addKey('account_type');
        $this->forge->addUniqueKey('account_code');
        $this->forge->createTable('accounts', true);
    }

    public function down()
    {
        $this->forge->dropTable('accounts', true);
        $this->forge->dropTable('account_groups', true);
    }
}