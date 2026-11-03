<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'transaction_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_company_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'transaction_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'transaction_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'subscription_upgrade',
            ],
            'transaction_reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'transaction_gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'razorpay',
            ],
            'transaction_order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'transaction_payment_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'transaction_signature' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'transaction_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'transaction_currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'INR',
            ],
            'transaction_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'successful', 'failed', 'refunded'],
                'default'    => 'pending',
            ],
            'transaction_billing_cycle' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'transaction_payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'transaction_notes' => [
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

        $this->forge->addKey('transaction_id', true);
        $this->forge->addKey('transaction_company_id');
        $this->forge->addKey('transaction_order_id');
        $this->forge->addKey('transaction_payment_id');
        $this->forge->createTable('payment_transactions', true);
    }

    public function down()
    {
        $this->forge->dropTable('payment_transactions', true);
    }
}

