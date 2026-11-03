<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'user_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'staff',
            ],
            'user_username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'user_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'user_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'user_password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'user_designation' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'user_avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'user_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive', 'suspended'],
                'default'    => 'active',
            ],
            'user_last_login' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('user_id', true);
        $this->forge->addKey('user_role_id');
        $this->forge->addUniqueKey('user_username');
        $this->forge->createTable('users', true);

        // user_roles (for multi-role assignments)
        $this->forge->addField([
            'ur_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ur_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ur_role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('ur_id', true);
        $this->forge->addKey('ur_user_id');
        $this->forge->addKey('ur_role_id');
        $this->forge->addUniqueKey(['ur_user_id', 'ur_role_id']);
        $this->forge->createTable('user_roles', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('users', true);
    }
}