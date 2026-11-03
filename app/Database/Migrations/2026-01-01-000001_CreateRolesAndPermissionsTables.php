<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesAndPermissionsTables extends Migration
{
    public function up()
    {
        // 1. roles
        $this->forge->addField([
            'role_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'role_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'role_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'role_badge_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'bg-secondary',
            ],
            'role_is_system' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addKey('role_id', true);
        $this->forge->addUniqueKey('role_key');
        $this->forge->createTable('roles', true);

        // 2. permissions
        $this->forge->addField([
            'permission_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'permission_module' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'permission_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'permission_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'permission_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('permission_id', true);
        $this->forge->addKey('permission_module');
        $this->forge->addUniqueKey('permission_key');
        $this->forge->createTable('permissions', true);

        // 3. role_permissions
        $this->forge->addField([
            'rp_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rp_role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'rp_permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('rp_id', true);
        $this->forge->addKey('rp_role_id');
        $this->forge->addKey('rp_permission_id');
        $this->forge->addUniqueKey(['rp_role_id', 'rp_permission_id']);
        $this->forge->createTable('role_permissions', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
    }
}