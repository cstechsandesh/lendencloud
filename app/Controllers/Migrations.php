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

        $migrationData = [
            'migrations'       => [],
            'total_migrations' => 0,
            'migrated_count'   => 0,
            'pending_count'    => 0,
            'latest_batch'     => 0,
        ];
        try {
            $migrationData = $this->getMigrationDetails();
        } catch (\Throwable $e) {
            log_message('error', 'Migration details error: ' . $e->getMessage());
        }

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

        // Get database tables list safely
        $dbTables = [];
        try {
            $rawTables = $this->db->query('SHOW TABLES')->getResultArray();
            $dbTables = array_map(fn($r) => reset($r), $rawTables);
        } catch (\Throwable $e) {
            $dbTables = [];
        }

        // Seeders list
        $seeders = $this->getSeedersList();

        $dbName = 'lenden_db';
        try {
            $dbName = $this->db->getDatabase();
        } catch (\Throwable $e) {
            $dbName = 'lenden_db';
        }

        $data = [
            'title'               => 'Database Migrations & Schema Runner - Super Admin',
            'migrations'          => array_values($filteredMigrations),
            'stats'               => [
                'total'        => $migrationData['total_migrations'],
                'migrated'     => $migrationData['migrated_count'],
                'pending'      => $migrationData['pending_count'],
                'latest_batch' => $migrationData['latest_batch'],
                'total_tables' => count($dbTables),
                'db_name'      => $dbName,
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

            if (method_exists($migrate, 'getCliMessages')) {
                foreach ($migrate->getCliMessages() as $msg) {
                    $logs[] = "  " . trim(strip_tags($msg));
                }
            }

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

            if (method_exists($migrate, 'getCliMessages')) {
                foreach ($migrate->getCliMessages() as $msg) {
                    $logs[] = "  " . trim(strip_tags($msg));
                }
            }

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

        $seederName = trim($this->request->getPost('seeder') ?? 'InitialSeeder');
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
            if (class_exists('App\Database\Seeds\InitialSeeder')) {
                $seeder->call('InitialSeeder');
                $logs[] = "[" . date('Y-m-d H:i:s') . "] Executed InitialSeeder with baseline data.";
            } elseif (class_exists('App\Database\Seeds\DatabaseSeeder')) {
                $seeder->call('DatabaseSeeder');
                $logs[] = "[" . date('Y-m-d H:i:s') . "] Executed DatabaseSeeder with baseline data.";
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
     * Resilient render method for Migrations dashboard.
     * Prevents multi-company or subscription table missing errors if database is not migrated yet.
     */
    protected function render(string $viewPath, array $data = []): string
    {
        try {
            return parent::render($viewPath, $data);
        } catch (\Throwable $e) {
            // Safe fallback data when database tables are missing
            $data['user_id']              = (int)($this->session->get('user_id') ?? 0);
            $data['is_demo_user']         = false;
            $data['user_name']            = $this->session->get('user_name') ?? 'Super Admin';
            $data['user_role']            = $this->session->get('user_role') ?? 'super_admin';
            $data['user_roles']           = $this->session->get('roles') ?? ['super_admin'];
            $data['user_permissions']     = $this->session->get('permissions') ?? [];
            $data['is_super_admin']       = true;
            $data['company_id']           = 1;
            $data['currentCompany']       = ['company_id' => 1, 'company_name' => 'LenDen Cloud', 'company_currency_symbol' => '₹'];
            $data['current_company']      = ['company_id' => 1, 'company_name' => 'LenDen Cloud', 'company_currency_symbol' => '₹'];
            $data['allCompanies']         = [];
            $data['hasCompanySwitcher']   = false;
            $data['current_subscription'] = null;
            $data['current_plan_code']    = 'enterprise';
            $data['plan_features']        = [
                'sales' => true, 'purchases' => true, 'inventory' => true, 'advanced_reports' => true, 'multi_company' => true,
            ];
            $data['company_name']         = 'LenDen Cloud';
            $data['currency_symbol']      = '₹';
            $data['date_format']          = 'd/m/Y';

            return view($viewPath, $data);
        }
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

            $history = $historyRecords[$version] ?? $historyRecords[$fullClass] ?? $historyRecords[$rawClassName] ?? null;
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
     * Self-authenticating Super Admin check.
     * Supports:
     * 1. Existing authenticated super_admin session.
     * 2. HTTP Basic Authentication (RFC 7617) via headers / browser native auth dialog.
     * 3. GET/POST query parameters (?auth_user=admin&auth_pass=admin123).
     * 4. Auto-provisions and establishes full super_admin session on successful auth.
     */
    protected function checkSuperAdmin(): bool
    {
        // 1. Check existing session
        $roles = $this->session->get('roles') ?? [];
        $userRole = $this->session->get('user_role') ?? '';
        $isSuperAdmin = in_array('super_admin', $roles, true) || $userRole === 'super_admin';

        if ($isSuperAdmin) {
            return true;
        }

        // 2. Extract credentials from HTTP Basic Auth or request parameters
        $credentials = $this->getHttpAuthCredentials();
        if ($credentials) {
            $username = trim($credentials['user']);
            $password = trim($credentials['pass']);

            if ($this->verifySuperAdminCredentials($username, $password)) {
                // Self-authenticate: establish Super Admin session
                $this->session->set([
                    'logged_in'     => true,
                    'is_logged_in'  => true,
                    'is_demo_user'  => false,
                    'user_id'       => 1,
                    'company_id'    => 1,
                    'user_username' => $username,
                    'user_name'     => 'Platform Super Admin',
                    'user_email'    => 'admin@lenden.local',
                    'user_role'     => 'super_admin',
                    'roles'         => ['super_admin'],
                    'permissions'   => ['*'],
                ]);

                return true;
            }
        }

        // 3. Challenge with HTTP 401 WWW-Authenticate for self-authentication
        $this->challengeHttpAuth();
        return false;
    }

    /**
     * Resolve credentials from HTTP Basic Auth headers or request parameters.
     */
    protected function getHttpAuthCredentials(): ?array
    {
        // Check PHP_AUTH_USER / PHP_AUTH_PW
        if (!empty($_SERVER['PHP_AUTH_USER'])) {
            return [
                'user' => (string)$_SERVER['PHP_AUTH_USER'],
                'pass' => (string)($_SERVER['PHP_AUTH_PW'] ?? ''),
            ];
        }

        // Check Authorization header (Apache, FastCGI, Nginx)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] 
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
            ?? $this->request->getHeaderLine('Authorization') 
            ?? '';

        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            $authHeader = $apacheHeaders['Authorization'] ?? $apacheHeaders['authorization'] ?? '';
        }

        if (!empty($authHeader) && stripos($authHeader, 'Basic ') === 0) {
            $decoded = base64_decode(substr($authHeader, 6));
            if ($decoded && str_contains($decoded, ':')) {
                [$u, $p] = explode(':', $decoded, 2);
                return [
                    'user' => $u,
                    'pass' => $p,
                ];
            }
        }

        // Check query / post parameters (?auth_user=admin&auth_pass=admin123)
        $paramUser = $this->request->getGetPost('auth_user');
        $paramPass = $this->request->getGetPost('auth_pass');
        if (!empty($paramUser) && !empty($paramPass)) {
            return [
                'user' => (string)$paramUser,
                'pass' => (string)$paramPass,
            ];
        }

        return null;
    }

    /**
     * Verify credentials against master platform key or database super_admin.
     */
    protected function verifySuperAdminCredentials(string $username, string $password): bool
    {
        $validUsernames = ['admin', 'super_admin'];
        $masterPassword = 'admin123';

        // Master recovery credentials (guarantees access even if database is empty/corrupt/unmigrated)
        if (in_array(strtolower($username), $validUsernames, true) && $password === $masterPassword) {
            return true;
        }

        // Database verification if users table exists
        try {
            if ($this->db && $this->db->tableExists($this->db->prefixTable('users'))) {
                $user = $this->db->table('users')
                    ->where('user_role', 'super_admin')
                    ->groupStart()
                        ->where('user_username', $username)
                        ->orWhere('user_email', $username)
                    ->groupEnd()
                    ->get()
                    ->getRowArray();

                if ($user && !empty($user['user_password_hash'])) {
                    if (password_verify($password, $user['user_password_hash']) || $password === $masterPassword) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // DB table might not exist yet; master credentials already checked above
        }

        return false;
    }

    /**
     * Issue HTTP 401 Basic Auth Challenge.
     */
    protected function challengeHttpAuth(): void
    {
        if ($this->request->isAJAX()) {
            $this->response->setStatusCode(401)->setJSON([
                'error'         => 'Authentication required',
                'auth_required' => true,
                'message'       => 'Please authenticate with Super Admin credentials.',
            ])->send();
            exit;
        }

        header('WWW-Authenticate: Basic realm="Platform Super Admin Database Migrations"');
        header('HTTP/1.0 401 Unauthorized');
        header('Status: 401 Unauthorized');
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized - Super Admin Access Required</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            background: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .auth-card {
            background: #1e293b;
            padding: 36px 32px;
            border-radius: 14px;
            border: 1px solid #334155;
            text-align: center;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
        }
        h2 { margin: 0 0 10px; font-size: 20px; font-weight: 700; color: #f8fafc; }
        p { color: #94a3b8; font-size: 13.5px; line-height: 1.6; margin: 0 0 18px; }
        .cred-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: monospace;
            font-size: 13px;
            color: #38bdf8;
            margin-bottom: 22px;
            text-align: left;
            line-height: 1.6;
        }
        .btn {
            display: block;
            width: 100%;
            background: #3b82f6;
            color: #ffffff;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.15s ease;
            box-sizing: border-box;
        }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="icon">&#128274;</div>
        <h2>Super Admin Authentication Required</h2>
        <p>Access to the Database Migrations &amp; Schema Runner requires Platform Super Administrator credentials.</p>
        <div class="cred-box">
            <strong>Default Super Admin Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
        <a href="" class="btn">Sign In with Browser Dialog</a>
    </div>
</body>
</html>';
        exit;
    }
}
