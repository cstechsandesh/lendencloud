<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournalEntriesTable extends Migration
{
    public function up()
    {
        // 1. journal_entries
        $this->forge->addField([
            'journal_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'journal_voucher_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'journal_type' => [
                'type'       => 'ENUM',
                'constraint' => ['journal', 'payment', 'receipt', 'contra', 'sales', 'purchase'],
                'default'    => 'journal',
            ],
            'journal_reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'journal_reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'journal_date' => [
                'type' => 'DATE',
            ],
            'journal_narration' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'journal_is_posted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'journal_created_by' => [
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
        $this->forge->addKey('journal_id', true);
        $this->forge->addUniqueKey('journal_voucher_number');
        $this->forge->addKey(['journal_reference_type', 'journal_reference_id']);
        $this->forge->createTable('journal_entries', true);

        // 2. journal_lines
        $this->forge->addField([
            'jl_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jl_journal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jl_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jl_debit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'jl_credit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'jl_narration' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('jl_id', true);
        $this->forge->addKey('jl_journal_id');
        $this->forge->addKey('jl_account_id');
        $this->forge->createTable('journal_lines', true);
    }

    public function down()
    {
        $this->forge->dropTable('journal_lines', true);
        $this->forge->dropTable('journal_entries', true);
    }
}