<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Execute primary platform seeder (Company, Roles, Permissions, Accounts, Plans & Users)
        $this->call('InitialSeeder');
    }
}

