<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryAndStockTable extends Migration
{
    public function up()
    {
        // 1. categories
        $this->forge->addField([
            'category_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'category_parent_id' => [
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
        $this->forge->addKey('category_id', true);
        $this->forge->createTable('categories', true);

        // 2. warehouses
        $this->forge->addField([
            'warehouse_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'warehouse_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'warehouse_location' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'warehouse_is_active' => [
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
        $this->forge->addKey('warehouse_id', true);
        $this->forge->createTable('warehouses', true);

        // 3. products
        $this->forge->addField([
            'product_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'product_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'product_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'product_hsn_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'product_unit' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'Nos',
            ],
            'product_purchase_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'product_selling_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'product_tax_rate_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'product_current_stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'product_reorder_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'product_is_active' => [
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
        $this->forge->addKey('product_id', true);
        $this->forge->addKey('product_category_id');
        $this->forge->addKey('product_tax_rate_id');
        $this->forge->addUniqueKey('product_code');
        $this->forge->createTable('products', true);

        // 4. stock_movements
        $this->forge->addField([
            'stock_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'stock_product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'stock_warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'stock_type' => [
                'type'       => 'ENUM',
                'constraint' => ['in', 'out', 'adjustment', 'transfer_in', 'transfer_out'],
            ],
            'stock_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,3',
                'default'    => 0.000,
            ],
            'stock_unit_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'stock_reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'stock_reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'stock_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'stock_date' => [
                'type' => 'DATETIME',
            ],
            'stock_created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('stock_id', true);
        $this->forge->addKey('stock_product_id');
        $this->forge->addKey('stock_warehouse_id');
        $this->forge->addKey(['stock_reference_type', 'stock_reference_id']);
        $this->forge->createTable('stock_movements', true);
    }

    public function down()
    {
        $this->forge->dropTable('stock_movements', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('warehouses', true);
        $this->forge->dropTable('categories', true);
    }
}