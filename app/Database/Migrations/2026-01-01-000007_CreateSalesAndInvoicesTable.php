<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesAndInvoicesTable extends Migration
{
    public function up()
    {
        // 1. sales_orders
        $this->forge->addField([
            'so_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'so_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'so_party_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'so_date' => [
                'type' => 'DATE',
            ],
            'so_expected_delivery' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'so_status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'confirmed', 'partially_invoiced', 'invoiced', 'cancelled'],
                'default'    => 'draft',
            ],
            'so_subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'so_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'so_discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'so_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'so_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'so_created_by' => [
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
        $this->forge->addKey('so_id', true);
        $this->forge->addUniqueKey('so_number');
        $this->forge->addKey('so_party_id');
        $this->forge->createTable('sales_orders', true);

        // 2. sales_order_items
        $this->forge->addField([
            'soi_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'soi_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'soi_product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'soi_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 1.000,
            ],
            'soi_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'soi_discount_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'soi_tax_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'soi_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'soi_line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'soi_delivered_qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('soi_id', true);
        $this->forge->addKey('soi_order_id');
        $this->forge->addKey('soi_product_id');
        $this->forge->createTable('sales_order_items', true);

        // 3. invoices
        $this->forge->addField([
            'invoice_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'invoice_order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'invoice_party_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'invoice_date' => [
                'type' => 'DATE',
            ],
            'invoice_due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'invoice_status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'],
                'default'    => 'draft',
            ],
            'invoice_subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_paid_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'invoice_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'invoice_journal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'invoice_created_by' => [
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
        $this->forge->addKey('invoice_id', true);
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->addKey('invoice_party_id');
        $this->forge->addKey('invoice_journal_id');
        $this->forge->createTable('invoices', true);

        // 4. invoice_items
        $this->forge->addField([
            'ii_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ii_invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ii_product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ii_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 1.000,
            ],
            'ii_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'ii_discount_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'ii_tax_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ii_tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'ii_line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('ii_id', true);
        $this->forge->addKey('ii_invoice_id');
        $this->forge->addKey('ii_product_id');
        $this->forge->createTable('invoice_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('invoice_items', true);
        $this->forge->dropTable('invoices', true);
        $this->forge->dropTable('sales_order_items', true);
        $this->forge->dropTable('sales_orders', true);
    }
}