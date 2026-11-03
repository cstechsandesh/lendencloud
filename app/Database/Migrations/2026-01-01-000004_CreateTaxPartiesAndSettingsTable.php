<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaxPartiesAndSettingsTable extends Migration
{
    public function up()
    {
        // 1. tax_rates
        $this->forge->addField([
            'tax_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tax_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tax_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'tax_is_active' => [
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
        $this->forge->addKey('tax_id', true);
        $this->forge->createTable('tax_rates', true);

        // 2. parties (Customers & Vendors)
        $this->forge->addField([
            'party_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'party_type' => [
                'type'       => 'ENUM',
                'constraint' => ['customer', 'vendor', 'both'],
                'default'    => 'customer',
            ],
            'party_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'party_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'party_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'party_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'party_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'party_gstin' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'party_pan' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'party_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'party_credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'party_credit_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'party_current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'party_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->addKey('party_id', true);
        $this->forge->addKey('party_type');
        $this->forge->addKey('party_account_id');
        $this->forge->createTable('parties', true);

        // 3. settings
        $this->forge->addField([
            'setting_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('setting_id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('settings', true);
        $this->forge->dropTable('parties', true);
        $this->forge->dropTable('tax_rates', true);
    }
}