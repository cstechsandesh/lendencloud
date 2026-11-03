<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Core;

/**
 * Description of CSMigrations
 *
 * @author Sandesh
 */
class CSMigration extends \CodeIgniter\Database\Migration {
    protected $table = '';
    public function __construct(\CodeIgniter\Database\Forge $forge = null) {
        parent::__construct($forge);
        
    }
    public function up() {
        //
    }
    public function down() {
        //
    }
    
    public function insert(string $table, array $data) {
        foreach ($data as $row) {
            $this->db->table($table)->insert($row);
        }
    }
}
