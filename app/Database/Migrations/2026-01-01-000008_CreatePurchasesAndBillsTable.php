<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchasesAndBillsTable extends Migration
{
    public function up()
    {
        // 1. purchase_orders
        $this->forge->addField([
            'po_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'po_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'po_party_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'po_date' => [
                'type' => 'DATE',
            ],
            'po_expected_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'po_status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'confirmed', 'partially_received', 'received', 'cancelled'],
                'default'    => 'draft',
            ],
            'po_subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'po_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'po_discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'po_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'po_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'po_created_by' => [
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
        $this->forge->addKey('po_id', true);
        $this->forge->addUniqueKey('po_number');
        $this->forge->addKey('po_party_id');
        $this->forge->createTable('purchase_orders', true);

        // 2. purchase_order_items
        $this->forge->addField([
            'poi_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'poi_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'poi_product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'poi_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 1.000,
            ],
            'poi_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'poi_discount_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'poi_tax_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'poi_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'poi_line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'poi_received_qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('poi_id', true);
        $this->forge->addKey('poi_order_id');
        $this->forge->addKey('poi_product_id');
        $this->forge->createTable('purchase_order_items', true);

        // 3. bills
        $this->forge->addField([
            'bill_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'bill_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'bill_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'bill_party_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bill_date' => [
                'type' => 'DATE',
            ],
            'bill_due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'bill_status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'partial', 'paid', 'overdue', 'cancelled'],
                'default'    => 'draft',
            ],
            'bill_subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_paid_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bill_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'bill_journal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'bill_created_by' => [
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
        $this->forge->addKey('bill_id', true);
        $this->forge->addUniqueKey('bill_number');
        $this->forge->addKey('bill_party_id');
        $this->forge->addKey('bill_journal_id');
        $this->forge->createTable('bills', true);

        // 4. bill_items
        $this->forge->addField([
            'bi_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'bi_bill_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bi_product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bi_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 1.000,
            ],
            'bi_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bi_discount_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'bi_tax_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'bi_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'bi_line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('bi_id', true);
        $this->forge->addKey('bi_bill_id');
        $this->forge->addKey('bi_product_id');
        $this->forge->createTable('bill_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('bill_items', true);
        $this->forge->dropTable('bills', true);
        $this->forge->dropTable('purchase_order_items', true);
        $this->forge->dropTable('purchase_orders', true);
    }
}