<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use Config\Services;
use Config\Database;

class Migrations extends BaseController
{
    /**
     * Database connection instance
     *
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        if (!$this->db) {
            $this->db = Database::connect();
        }
        if (!$this->session) {
            $this->session = service('session');
        }
    }

    /**
     * Display Migration Runner Dashboard.
     */
    public function index()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $search = trim($this->request->getGet('search') ?? '');
        $status = trim($this->request->getGet('status') ?? 'all');

        $migrationData = $this->getMigrationDetails();
        $allMigrations = $migrationData['migrations'];

        // Apply filters
        $filteredMigrations = array_filter($allMigrations, function ($m) use ($search, $status) {
            if ($status === 'migrated' && $m['status'] !== 'migrated') {
                return false;
            }
            if ($status === 'pending' && $m['status'] !== 'pending') {
                return false;
            }
            if (!empty($search)) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($m['name']), $searchLower) ||
                       str_contains(strtolower($m['version']), $searchLower) ||
                       str_contains(strtolower($m['filename']), $searchLower);
            }
            return true;
        });

        // Get database tables list
        $rawTables = $this->db->query('SHOW TABLES')->getResultArray();
        $dbTables = array_map(fn($r) => reset($r), $rawTables);

        // Seeders list
        $seeders = $this->getSeedersList();

        $data = [
            'title'               => 'Database Migrations & Schema Runner - Super Admin',
            'migrations'          => array_values($filteredMigrations),
            'stats'               => [
                'total'        => $migrationData['total_migrations'],
                'migrated'     => $migrationData['migrated_count'],
                'pending'      => $migrationData['pending_count'],
                'latest_batch' => $migrationData['latest_batch'],
                'total_tables' => count($dbTables),
                'db_name'      => $this->db->getDatabase(),
            ],
            'dbTables'            => $dbTables,
            'seeders'             => $seeders,
            'search'              => $search,
            'status'              => $status,
            'consoleOutput'       => $this->session->getFlashdata('migration_console'),
            'consoleStatus'       => $this->session->getFlashdata('migration_console_status') ?? 'info',
        ];

        return $this->render('migrations/index', $data);
    }

    /**
     * Run all pending migrations to latest schema version.
     */
    public function runPending()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $startTime = microtime(true);
        $logs = [];
        $logs[] = "[" . date('Y-m-d H:i:s') . "] Starting migration runner: executing pending migrations...";

        try {
            $migrate = Services::migrations(null, $this->db, false);
            $migrate->setNamespace('App');
            $success = $migrate->latest();

            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            $migrationData = $this->getMigrationDetails();
            $newPending = $migrationData['pending_count'];

            $logs[] = "[" . date('Y-m-d H:i:s') . "] Latest batch run completed in {$elapsed}ms.";
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Schema state: {$migrationData['migrated_count']} applied, {$newPending} pending.";

            // Log activity
            ActivityLogModel::recordLog([
                'log_company_id'  => (int)($this->session->get('company_id') ?: 1),
                'log_user_id'     => (int)$this->session->get('user_id'),
                'log_user_name'   => $this->session->get('user_name') ?? 'Super Admin',
                'log_user_email'  => $this->session->get('user_email'),
                'log_user_role'   => 'super_admin',
                'log_action'      => 'MIGRATE_LATEST',
                'log_module'      => 'Database Migrations',
                'log_description' => "Executed pending migrations to latest batch in {$elapsed}ms.",
                'log_method'      => 'POST',
                'log_url'         => 'migrations/run',
                'log_ip_address'  => $this->request->getIPAddress(),
                'log_request_data'=> ['elapsed_ms' => $elapsed, 'status' => 'success'],
            ]);

            $this->session->setFlashdata('success', 'Successfully executed pending database migrations.');
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'success');

        } catch (\Throwable $e) {
            $logs[] = "[ERROR] Migration failed: " . $e->getMessage();
            $this->session->setFlashdata('error', 'Migration failed: ' . $e->getMessage());
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'danger');
        }

        return redirect()->to(site_url('migrations'));
    }

    /**
     * Rollback the latest batch of migrations.
     */
    public function rollback()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $startTime = microtime(true);
        $logs = [];
        $logs[] = "[" . date('Y-m-d H:i:s') . "] Initiating rollback of last migration batch...";

        try {
            $migrate = Services::migrations(null, $this->db, false);
            $migrate->setNamespace('App');
            $migrate->ensureTable();

            // Find current latest batch
            $latestBatch = (int)($this->db->table('migrations')->selectMax('batch')->get()->getRow()->batch ?? 0);

            if ($latestBatch <= 0) {
                $this->session->setFlashdata('error', 'No migration batches available to rollback.');
                return redirect()->to(site_url('migrations'));
            }

            // In CodeIgniter 4, $migrate->regress(-1) regresses exactly the last executed batch relatively
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Rolling back Batch #{$latestBatch}...";

            $migrate->regress(-1);
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            // Fetch new latest batch after rollback
            $newLatestBatch = (int)($this->db->table('migrations')->selectMax('batch')->get()->getRow()->batch ?? 0);
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Rollback completed in {$elapsed}ms. Current latest batch: #{$newLatestBatch}.";

            // Log activity
            ActivityLogModel::recordLog([
                'log_company_id'  => (int)($this->session->get('company_id') ?: 1),
                'log_user_id'     => (int)$this->session->get('user_id'),
                'log_user_name'   => $this->session->get('user_name') ?? 'Super Admin',
                'log_user_email'  => $this->session->get('user_email'),
                'log_user_role'   => 'super_admin',
                'log_action'      => 'MIGRATE_ROLLBACK',
                'log_module'      => 'Database Migrations',
                'log_description' => "Rolled back migration Batch #{$latestBatch} in {$elapsed}ms.",
                'log_method'      => 'POST',
                'log_url'         => 'migrations/rollback',
                'log_ip_address'  => $this->request->getIPAddress(),
                'log_request_data'=> ['rolled_back_batch' => $latestBatch, 'new_latest_batch' => $newLatestBatch],
            ]);

            $this->session->setFlashdata('success', "Successfully rolled back migration Batch #{$latestBatch}.");
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'warning');

        } catch (\Throwable $e) {
            $logs[] = "[ERROR] Rollback failed: " . $e->getMessage();
            $this->session->setFlashdata('error', 'Rollback failed: ' . $e->getMessage());
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'danger');
        }

        return redirect()->to(site_url('migrations'));
    }

    /**
     * Clean Refresh: Drops all tables, re-executes all migrations to latest.
     */
    public function refresh()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $startTime = microtime(true);
        $logs = [];
        $logs[] = "[" . date('Y-m-d H:i:s') . "] Initiating Clean Database Refresh (migrate:refresh)...";

        try {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $rawTables = $this->db->query('SHOW TABLES')->getResultArray();
            $dropped = 0;

            $prefix = $this->db->getPrefix();
            $configTable = config('Migrations')->table ?? 'migrations';
            $migrationTable = $prefix . $configTable;

            foreach ($rawTables as $row) {
                $tbl = reset($row);
                if ($tbl === $migrationTable || $tbl === $configTable) {
                    continue;
                }
                $this->db->query("DROP TABLE IF EXISTS `{$tbl}`");
                $dropped++;
            }

            // Truncate migration history
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$migrationTable}` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `version` VARCHAR(255) NOT NULL,
                `class` VARCHAR(255) NOT NULL,
                `group` VARCHAR(255) NOT NULL,
                `namespace` VARCHAR(255) NOT NULL,
                `time` INT(11) NOT NULL,
                `batch` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $this->db->query("TRUNCATE TABLE `{$migrationTable}`");

            $logs[] = "[" . date('Y-m-d H:i:s') . "] Dropped {$dropped} application tables. Migration log reset to 0.";

            // Run latest
            $migrate = Services::migrations(null, $this->db, false);
            $migrate->setNamespace('App');
            $migrate->latest();

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            $newTables = $this->db->query('SHOW TABLES')->getResultArray();
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Successfully re-created " . count($newTables) . " tables in {$elapsed}ms.";

            // Log activity
            ActivityLogModel::recordLog([
                'log_company_id'  => (int)($this->session->get('company_id') ?: 1),
                'log_user_id'     => (int)$this->session->get('user_id'),
                'log_user_name'   => $this->session->get('user_name') ?? 'Super Admin',
                'log_user_email'  => $this->session->get('user_email'),
                'log_user_role'   => 'super_admin',
                'log_action'      => 'MIGRATE_REFRESH',
                'log_module'      => 'Database Migrations',
                'log_description' => "Executed complete database refresh (recreated " . count($newTables) . " tables) in {$elapsed}ms.",
                'log_method'      => 'POST',
                'log_url'         => 'migrations/refresh',
                'log_ip_address'  => $this->request->getIPAddress(),
                'log_request_data'=> ['tables_created' => count($newTables), 'elapsed_ms' => $elapsed],
            ]);

            $this->session->setFlashdata('success', 'Database refresh successful: all tables rebuilt from latest migrations.');
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'success');

        } catch (\Throwable $e) {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $logs[] = "[ERROR] Refresh failed: " . $e->getMessage();
            $this->session->setFlashdata('error', 'Database refresh failed: ' . $e->getMessage());
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'danger');
        }

        return redirect()->to(site_url('migrations'));
    }

    /**
     * Execute DatabaseSeeder.
     */
    public function seed()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $seederName = trim($this->request->getPost('seeder') ?? 'DatabaseSeeder');
        $startTime = microtime(true);
        $logs = [];
        $logs[] = "[" . date('Y-m-d H:i:s') . "] Starting Seeder: {$seederName}...";

        try {
            $seeder = Database::seeder();
            $seeder->call($seederName);

            $elapsed = round((microtime(true) - $startTime) * 1000, 2);
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Seeder '{$seederName}' executed successfully in {$elapsed}ms.";

            // Log activity
            ActivityLogModel::recordLog([
                'log_company_id'  => (int)($this->session->get('company_id') ?: 1),
                'log_user_id'     => (int)$this->session->get('user_id'),
                'log_user_name'   => $this->session->get('user_name') ?? 'Super Admin',
                'log_user_email'  => $this->session->get('user_email'),
                'log_user_role'   => 'super_admin',
                'log_action'      => 'SEED_DATABASE',
                'log_module'      => 'Database Migrations',
                'log_description' => "Executed seeder '{$seederName}' in {$elapsed}ms.",
                'log_method'      => 'POST',
                'log_url'         => 'migrations/seed',
                'log_ip_address'  => $this->request->getIPAddress(),
                'log_request_data'=> ['seeder' => $seederName, 'elapsed_ms' => $elapsed],
            ]);

            $this->session->setFlashdata('success', "Seeder '{$seederName}' executed successfully.");
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'success');

        } catch (\Throwable $e) {
            $logs[] = "[ERROR] Seeder execution failed: " . $e->getMessage();
            $this->session->setFlashdata('error', 'Seeder execution failed: ' . $e->getMessage());
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'danger');
        }

        return redirect()->to(site_url('migrations'));
    }

    /**
     * 1-Click Master Reset: Refresh Migrations & Seed Database.
     */
    public function refreshAndSeed()
    {
        if (!$this->checkSuperAdmin()) {
            return redirect()->to(site_url('dashboard'));
        }

        $startTime = microtime(true);
        $logs = [];
        $logs[] = "[" . date('Y-m-d H:i:s') . "] Starting Master Refresh & Reseed...";

        try {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $rawTables = $this->db->query('SHOW TABLES')->getResultArray();

            $prefix = $this->db->getPrefix();
            $configTable = config('Migrations')->table ?? 'migrations';
            $migrationTable = $prefix . $configTable;

            foreach ($rawTables as $row) {
                $tbl = reset($row);
                if ($tbl === $migrationTable || $tbl === $configTable) {
                    continue;
                }
                $this->db->query("DROP TABLE IF EXISTS `{$tbl}`");
            }

            $this->db->query("CREATE TABLE IF NOT EXISTS `{$migrationTable}` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `version` VARCHAR(255) NOT NULL,
                `class` VARCHAR(255) NOT NULL,
                `group` VARCHAR(255) NOT NULL,
                `namespace` VARCHAR(255) NOT NULL,
                `time` INT(11) NOT NULL,
                `batch` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $this->db->query("TRUNCATE TABLE `{$migrationTable}`");

            $logs[] = "[" . date('Y-m-d H:i:s') . "] Application tables dropped and migration tracking reset.";

            // Migrate latest
            $migrate = Services::migrations(null, $this->db, false);
            $migrate->setNamespace('App');
            $migrate->latest();
            $logs[] = "[" . date('Y-m-d H:i:s') . "] Executed all migrations to latest batch.";

            // Run seeder
            $seeder = Database::seeder();
            if (class_exists('App\Database\Seeds\DatabaseSeeder')) {
                $seeder->call('DatabaseSeeder');
                $logs[] = "[" . date('Y-m-d H:i:s') . "] Executed DatabaseSeeder with baseline data.";
            } elseif (class_exists('App\Database\Seeds\InitialSeeder')) {
                $seeder->call('InitialSeeder');
                $logs[] = "[" . date('Y-m-d H:i:s') . "] Executed InitialSeeder with baseline data.";
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            $logs[] = "[" . date('Y-m-d H:i:s') . "] Refresh & Reseed successfully completed in {$elapsed}ms.";

            // Log activity
            ActivityLogModel::recordLog([
                'log_company_id'  => (int)($this->session->get('company_id') ?: 1),
                'log_user_id'     => (int)$this->session->get('user_id'),
                'log_user_name'   => $this->session->get('user_name') ?? 'Super Admin',
                'log_user_email'  => $this->session->get('user_email'),
                'log_user_role'   => 'super_admin',
                'log_action'      => 'MIGRATE_REFRESH_SEED',
                'log_module'      => 'Database Migrations',
                'log_description' => "Executed full platform refresh & reseed in {$elapsed}ms.",
                'log_method'      => 'POST',
                'log_url'         => 'migrations/refresh-seed',
                'log_ip_address'  => $this->request->getIPAddress(),
                'log_request_data'=> ['status' => 'success', 'elapsed_ms' => $elapsed],
            ]);

            $this->session->setFlashdata('success', 'Master Reset Complete: Database refreshed and reseeded with base records.');
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'success');

        } catch (\Throwable $e) {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $logs[] = "[ERROR] Master reset failed: " . $e->getMessage();
            $this->session->setFlashdata('error', 'Master reset failed: ' . $e->getMessage());
            $this->session->setFlashdata('migration_console', implode("\n", $logs));
            $this->session->setFlashdata('migration_console_status', 'danger');
        }

        return redirect()->to(site_url('migrations'));
    }

    /**
     * JSON endpoint to inspect the PHP source code of a migration file.
     */
    public function viewCode($filename = null)
    {
        if (!$this->checkSuperAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $filename = $filename ?: $this->request->getGet('file');
        if (empty($filename)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Migration file not specified.']);
        }

        $cleanFilename = basename($filename);
        $filePath = APPPATH . 'Database/Migrations/' . $cleanFilename;

        if (!file_exists($filePath) || !str_ends_with($cleanFilename, '.php')) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Migration file not found.']);
        }

        $code = file_get_contents($filePath);
        $lines = count(explode("\n", $code));
        $fileSize = number_format(filesize($filePath) / 1024, 2) . ' KB';

        return $this->response->setJSON([
            'filename' => $cleanFilename,
            'lines'    => $lines,
            'filesize' => $fileSize,
            'code'     => $code,
        ]);
    }

    /**
     * Scan migrations directory and cross-reference with migrations history.
     */
    protected function getMigrationDetails(): array
    {
        $migrationsDir = APPPATH . 'Database/Migrations/';
        $files = glob($migrationsDir . '*.php') ?: [];
        sort($files);

        // Fetch history records from migrations table
        $historyRecords = [];
        $latestBatch = 0;

        try {
            $runner = Services::migrations(null, $this->db, false);
            $runner->setNamespace('App');
            $runner->ensureTable();

            $history = $this->db->table('migrations')->orderBy('id', 'ASC')->get()->getResultArray();
            foreach ($history as $h) {
                $historyRecords[$h['version']] = $h;
                $historyRecords[$h['class']]   = $h;
                $historyRecords[basename(str_replace('\\', '/', $h['class']))] = $h;
                if ((int)$h['batch'] > $latestBatch) {
                    $latestBatch = (int)$h['batch'];
                }
            }
        } catch (\Throwable $e) {
            // migrations table might not exist yet
        }

        $migrations = [];
        $migratedCount = 0;
        $pendingCount = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            if ($filename === '.gitkeep') {
                continue;
            }

            // Filename format: YYYY-MM-DD-HHMMSS_ClassName.php
            $parts = explode('_', $filename, 2);
            $version = $parts[0] ?? '';
            $rawClassName = isset($parts[1]) ? substr($parts[1], 0, -4) : '';
            $fullClass = 'App\\Database\\Migrations\\' . $rawClassName;

            $history = $historyRecords[$version] ?? $historyRecords[$fullClass] ?? null;
            $isMigrated = ($history !== null);

            if ($isMigrated) {
                $status = 'migrated';
                $batch = (int)$history['batch'];
                $executedAt = !empty($history['time']) ? date('Y-m-d H:i:s', (int)$history['time']) : 'Executed';
                $migratedCount++;
            } else {
                $status = 'pending';
                $batch = null;
                $executedAt = null;
                $pendingCount++;
            }

            // Human-readable friendly name
            $friendlyName = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $rawClassName));

            $migrations[] = [
                'filename'    => $filename,
                'version'     => $version,
                'class'       => $rawClassName,
                'name'        => $friendlyName,
                'status'      => $status,
                'batch'       => $batch,
                'executed_at' => $executedAt,
                'size'        => number_format(filesize($filePath) / 1024, 1) . ' KB',
                'modified_at' => date('Y-m-d H:i', filemtime($filePath)),
            ];
        }

        return [
            'migrations'       => $migrations,
            'total_migrations' => count($migrations),
            'migrated_count'   => $migratedCount,
            'pending_count'    => $pendingCount,
            'latest_batch'     => $latestBatch,
        ];
    }

    /**
     * Get available seeders with metadata.
     */
    protected function getSeedersList(): array
    {
        $seedsDir = APPPATH . 'Database/Seeds/';
        $files = glob($seedsDir . '*.php') ?: [];

        $seeders = [];
        foreach ($files as $filePath) {
            $filename = basename($filePath);
            if ($filename === '.gitkeep') {
                continue;
            }
            $className = substr($filename, 0, -4);
            $seeders[] = [
                'filename'    => $filename,
                'class'       => $className,
                'size'        => number_format(filesize($filePath) / 1024, 1) . ' KB',
                'description' => ($className === 'InitialSeeder' || $className === 'DatabaseSeeder')
                    ? 'Primary platform master seeder: seeds Companies, Roles, Permissions, Admin Users, Chart of Accounts, Tax Rates, and Subscription Plans.'
                    : "Standard seeder class: {$className}",
            ];
        }

        return $seeders;
    }

    /**
     * Restrict execution to super_admin.
     */
    protected function checkSuperAdmin(): bool
    {
        $roles = $this->session->get('roles') ?? [];
        $userRole = $this->session->get('user_role') ?? '';
        $isSuperAdmin = in_array('super_admin', $roles, true) || $userRole === 'super_admin';

        if (!$isSuperAdmin) {
            $this->session->setFlashdata('error', 'Access restricted to Platform Super Administrators.');
            return false;
        }
        return true;
    }
}

