<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- External FontAwesome 6 for iconography compatibility -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* Dashboard KPI Stat Grid */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    width: 100%;
}
@media (min-width: 992px) {
    .stat-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}
.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    transition: all 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}
.stat-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.stat-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 4px;
}
.stat-value {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.stat-subtext {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 4px;
}
.stat-icon-wrapper {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-icon-indigo { background: #e0e7ff; color: #4338ca; }
.stat-icon-emerald { background: #dcfce7; color: #15803d; }
.stat-icon-amber { background: #fef3c7; color: #b45309; }
.stat-icon-sky { background: #e0f2fe; color: #0369a1; }

.badge-super-admin {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    font-weight: 700;
    border-radius: 20px;
}
.badge-neutral {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 3px 8px;
    border-radius: 6px;
}

/* Modals Overlay */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 10500;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
}
.modal-backdrop-custom.show {
    display: flex !important;
}

/* Responsive Migrations Layout */
.migrations-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1024px) {
    .migrations-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-database text-primary"></i> Database Migrations &amp; Schema Runner
        </h1>
        <p class="text-muted mb-0 small">Platform Super Admin engine to inspect schema migrations, execute pending batches, rollback changes, and seed baseline data.</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- Run Pending Migrations -->
        <form action="<?= site_url('migrations/run') ?>" method="POST" class="m-0" onsubmit="return showExecuting('Running pending migrations...');">
            <?= csrf_field() ?>
            <button type="submit" id="btnRunPending" class="btn btn-success btn-sm d-flex align-items-center gap-2 fw-semibold">
                <i class="fa-solid fa-play"></i> Run Pending
                <?php if (($stats['pending'] ?? 0) > 0): ?>
                    <span class="badge bg-white text-success rounded-pill px-2"><?= (int)$stats['pending'] ?></span>
                <?php endif; ?>
            </button>
        </form>

        <!-- Rollback Last Batch -->
        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" onclick="openRollbackModal()" title="Rollback the latest executed migration batch">
            <i class="fa-solid fa-rotate-left"></i> Rollback Batch
            <?php if (($stats['latest_batch'] ?? 0) > 0): ?>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill">#<?= (int)$stats['latest_batch'] ?></span>
            <?php endif; ?>
        </button>

        <!-- Execute Seeder -->
        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" onclick="openModal('seedModal')">
            <i class="fa-solid fa-seedling text-success"></i> Run Seeder
        </button>

        <!-- Full Refresh & Reseed Danger Action -->
        <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-2" onclick="openModal('refreshModal')">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh &amp; Reseed
        </button>

        <span class="badge badge-super-admin px-3 py-2 small d-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-crown text-warning"></i> Super Admin
        </span>
    </div>
</div>

<!-- KPI Summary Metrics Grid -->
<div class="stat-grid stat-grid-4">
    <!-- Total Migrations -->
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Migrations</div>
            <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="stat-subtext">Discovered in <code>App\Database\Migrations</code></div>
        </div>
        <div class="stat-icon-wrapper stat-icon-indigo">
            <i class="fa-solid fa-layer-group"></i>
        </div>
    </div>

    <!-- Applied Migrations -->
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Applied Migrations</div>
            <div class="stat-value text-success"><?= number_format($stats['migrated'] ?? 0) ?></div>
            <div class="stat-subtext">
                Current Batch: <strong>#<?= (int)($stats['latest_batch'] ?? 0) ?></strong> &bull; Recorded in tracking
            </div>
        </div>
        <div class="stat-icon-wrapper stat-icon-emerald">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <!-- Pending Migrations -->
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Pending Migrations</div>
            <div class="stat-value <?= (($stats['pending'] ?? 0) > 0) ? 'text-warning' : 'text-success' ?>">
                <?= number_format($stats['pending'] ?? 0) ?>
            </div>
            <div class="stat-subtext">
                <?php if (($stats['pending'] ?? 0) > 0): ?>
                    <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Action required to update DB</span>
                <?php else: ?>
                    <span class="text-success fw-semibold"><i class="fa-solid fa-check-double"></i> Schema is 100% up to date</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="stat-icon-wrapper <?= (($stats['pending'] ?? 0) > 0) ? 'stat-icon-amber' : 'stat-icon-emerald' ?>">
            <i class="fa-solid <?= (($stats['pending'] ?? 0) > 0) ? 'fa-clock' : 'fa-shield-halved' ?>"></i>
        </div>
    </div>

    <!-- Database Tables -->
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Database Schema</div>
            <div class="stat-value text-primary"><?= number_format($stats['total_tables'] ?? 0) ?> <span class="fs-6 fw-semibold text-muted">tables</span></div>
            <div class="stat-subtext">Database: <strong><?= esc($stats['db_name'] ?? 'lenden_db') ?></strong></div>
        </div>
        <div class="stat-icon-wrapper stat-icon-sky">
            <i class="fa-solid fa-table-cells"></i>
        </div>
    </div>
</div>

<!-- Execution Console / Terminal Output Panel -->
<?php if (!empty($consoleOutput)): ?>
    <div id="terminalPanel" class="card mb-4 border-0 shadow-lg" style="background: #0f172a; border-radius: 12px; overflow: hidden;">
        <div style="background: #1e293b; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155;">
            <div class="d-flex align-items-center gap-2">
                <span style="width: 11px; height: 11px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                <span style="width: 11px; height: 11px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                <span style="width: 11px; height: 11px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                <span style="color: #94a3b8; font-size: 12px; font-family: monospace; font-weight: 600; margin-left: 8px;">
                    <i class="fa-solid fa-terminal text-info me-1"></i> Execution Terminal Log Output
                </span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" onclick="copyConsoleOutput()" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size: 11.5px;">
                    <i class="fa-solid fa-copy me-1"></i> Copy Log
                </button>
                <button type="button" onclick="document.getElementById('terminalPanel').style.display='none'" class="btn btn-sm text-secondary border-0 fs-6 p-0 ms-1">
                    <i class="fa-solid fa-xmark text-white"></i>
                </button>
            </div>
        </div>
        <div style="padding: 16px; font-family: 'Consolas', 'Courier New', monospace; font-size: 13px; line-height: 1.6; color: #38bdf8; max-height: 240px; overflow-y: auto; white-space: pre-wrap;" id="terminalContent"><?= esc($consoleOutput) ?></div>
    </div>
<?php endif; ?>

<!-- Main Content Grid: Migrations Table & Sidebar -->
<div class="migrations-grid">
    <!-- Left Column: Migrations Register -->
    <div class="card border shadow-sm rounded-3 overflow-hidden p-0">
        <!-- Table Toolbar -->
        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary"></i> Schema Migrations Register
                </h3>
                <span class="badge badge-neutral"><?= count($migrations) ?> visible</span>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="<?= site_url('migrations') ?>" class="d-flex gap-2 align-items-center m-0">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control form-control-sm ps-4" placeholder="Search migrations..." value="<?= esc($search) ?>" style="width: 190px; font-size: 13px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute text-muted" style="left: 10px; top: 9px; font-size: 12px;"></i>
                </div>

                <select name="status" class="form-select form-select-sm" style="width: 130px; font-size: 13px;" onchange="this.form.submit()">
                    <option value="all" <?= ($status === 'all') ? 'selected' : '' ?>>All Status</option>
                    <option value="migrated" <?= ($status === 'migrated') ? 'selected' : '' ?>>Migrated</option>
                    <option value="pending" <?= ($status === 'pending') ? 'selected' : '' ?>>Pending</option>
                </select>

                <?php if (!empty($search) || $status !== 'all'): ?>
                    <a href="<?= site_url('migrations') ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Migrations Table -->
        <div class="table-responsive m-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small text-uppercase text-muted fw-bold ps-3" style="width: 110px;">Status</th>
                        <th class="small text-uppercase text-muted fw-bold">Migration Class &amp; Details</th>
                        <th class="small text-uppercase text-muted fw-bold" style="width: 100px;">Batch</th>
                        <th class="small text-uppercase text-muted fw-bold" style="width: 160px;">Executed At</th>
                        <th class="small text-uppercase text-muted fw-bold text-center pe-3" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($migrations)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-2 text-secondary d-block mb-2"></i>
                                <strong>No migrations match your search criteria.</strong>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($migrations as $m): ?>
                            <tr>
                                <!-- Status Badge -->
                                <td class="ps-3">
                                    <?php if ($m['status'] === 'migrated'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold small px-2 py-1 rounded">
                                            <i class="fa-solid fa-circle-check me-1"></i> Migrated
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold small px-2 py-1 rounded">
                                            <i class="fa-solid fa-clock me-1"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Migration Details -->
                                <td>
                                    <div class="fw-bold text-dark small">
                                        <?= esc($m['name']) ?>
                                    </div>
                                    <div class="text-muted small font-monospace" style="font-size: 11.5px;">
                                        <i class="fa-regular fa-file-code me-1"></i> <?= esc($m['filename']) ?>
                                        <span class="ms-2 text-muted">&bull; <?= esc($m['size']) ?></span>
                                    </div>
                                </td>

                                <!-- Batch -->
                                <td>
                                    <?php if ($m['batch'] !== null): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                            #<?= (int)$m['batch'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Executed Timestamp -->
                                <td class="small text-secondary">
                                    <?php if (!empty($m['executed_at'])): ?>
                                        <div><i class="fa-regular fa-calendar text-muted me-1"></i> <?= esc($m['executed_at']) ?></div>
                                    <?php else: ?>
                                        <span class="text-warning-emphasis fw-semibold small">Not executed</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-center pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="inspectMigrationCode('<?= esc($m['filename']) ?>')" title="View PHP Migration Code">
                                        <i class="fa-solid fa-code"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Database Seeders & Quick Tools -->
    <div class="d-flex flex-column gap-3">
        <!-- Available Seeders Card -->
        <div class="card border shadow-sm rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <h3 class="h6 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-seedling text-success"></i> Platform Seeders
                </h3>
                <span class="badge badge-neutral"><?= count($seeders) ?> available</span>
            </div>

            <p class="text-muted small mb-3">
                Seeders populate default master data, multi-company records, chart of accounts, SaaS pricing tiers, and system administrator roles.
            </p>

            <?php foreach ($seeders as $s): ?>
                <div class="bg-light border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="fw-bold text-dark small"><?= esc($s['class']) ?></div>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 10px;"><?= esc($s['size']) ?></span>
                    </div>
                    <div class="text-muted small mb-2" style="font-size: 11.5px; line-height: 1.4;">
                        <?= esc($s['description']) ?>
                    </div>
                    <form action="<?= site_url('migrations/seed') ?>" method="POST" class="m-0" onsubmit="return showExecuting('Executing seeder <?= esc($s['class']) ?>...');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="seeder" value="<?= esc($s['class']) ?>">
                        <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-2" style="font-size: 12px;">
                            <i class="fa-solid fa-play"></i> Execute <?= esc($s['class']) ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Database Engine Information Card -->
        <div class="card border shadow-sm rounded-3 p-3">
            <h3 class="h6 mb-3 fw-bold text-dark d-flex align-items-center gap-2 border-bottom pb-2">
                <i class="fa-solid fa-server text-primary"></i> Environment &amp; DB Engine
            </h3>

            <div class="d-flex flex-column gap-2 small">
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="text-muted">Framework:</span>
                    <span class="fw-semibold text-dark">CodeIgniter <?= \CodeIgniter\CodeIgniter::CI_VERSION ?></span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="text-muted">DB Driver:</span>
                    <span class="fw-semibold text-dark">MySQLi (InnoDB)</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="text-muted">Active Database:</span>
                    <span class="fw-semibold font-monospace text-primary"><?= esc($stats['db_name']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-1">
                    <span class="text-muted">Prefix:</span>
                    <span class="fw-semibold font-monospace text-dark"><?= esc(config('Database')->default['DBPrefix'] ?? 'ld_') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Migration Table:</span>
                    <span class="fw-semibold font-monospace text-dark"><?= esc((config('Database')->default['DBPrefix'] ?? 'ld_') . (config('Migrations')->table ?? 'migrations')) ?></span>
                </div>
            </div>
        </div>

        <!-- CLI Command Reference Guide -->
        <div class="card border shadow-sm rounded-3 p-3 bg-light">
            <h4 class="h6 mb-2 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-terminal text-primary"></i> CLI Equivalent Commands
            </h4>
            <div class="p-2 rounded bg-dark text-info font-monospace small" style="font-size: 11.5px; line-height: 1.6;">
                php spark migrate<br>
                php spark migrate:rollback<br>
                php spark migrate:refresh<br>
                php spark db:seed DatabaseSeeder
            </div>
            <div class="text-muted small mt-2" style="font-size: 11px;">
                <i class="fa-solid fa-circle-info text-primary me-1"></i> The web dashboard executes these native migrations with complete session audit logging &amp; transaction safety.
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: Code Inspector Modal                                             -->
<!-- ========================================================================= -->
<div id="codeInspectorModal" class="modal-backdrop-custom">
    <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; width: 90%; max-width: 900px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
        <!-- Modal Header -->
        <div style="padding: 14px 20px; background: #1e293b; border-bottom: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-code text-info fs-5"></i>
                <div>
                    <h4 id="codeModalTitle" style="margin: 0; font-size: 14px; color: #f8fafc; font-family: monospace;">Migration Source Code</h4>
                    <span id="codeModalMeta" style="font-size: 11.5px; color: #94a3b8;">Loading file details...</span>
                </div>
            </div>
            <button type="button" onclick="closeModal('codeInspectorModal')" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">
                <i class="fa-solid fa-xmark text-white"></i>
            </button>
        </div>

        <!-- Modal Body (Code View) -->
        <div style="padding: 16px; overflow-y: auto; flex: 1;">
            <pre style="margin: 0; font-family: 'Consolas', 'Courier New', monospace; font-size: 12.5px; line-height: 1.6; color: #e2e8f0; white-space: pre-wrap;" id="codeModalContent">Loading source code...</pre>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 12px 20px; background: #1e293b; border-top: 1px solid #334155; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="copyInspectedCode()" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-copy me-1"></i> Copy Code
            </button>
            <button type="button" onclick="closeModal('codeInspectorModal')" class="btn btn-primary btn-sm">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: Rollback Batch Confirmation Modal                                -->
<!-- ========================================================================= -->
<div id="rollbackModal" class="modal-backdrop-custom">
    <div class="bg-white rounded-3 shadow-lg p-4" style="width: 90%; max-width: 500px;">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
                <h3 class="h5 fw-bold text-dark mb-0">Rollback Last Batch</h3>
                <span class="text-muted small">Revert recent schema changes</span>
            </div>
        </div>

        <?php if (($stats['latest_batch'] ?? 0) > 0): ?>
            <p class="text-secondary small mb-3">
                Are you sure you want to roll back <strong>Batch #<?= (int)$stats['latest_batch'] ?></strong>? 
                This will execute the <code>down()</code> method of all migrations registered under this batch.
            </p>

            <form action="<?= site_url('migrations/rollback') ?>" method="POST" onsubmit="return showExecuting('Executing batch rollback...');">
                <?= csrf_field() ?>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('rollbackModal')">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold text-dark">
                        <i class="fa-solid fa-rotate-left me-1"></i> Confirm Rollback Batch #<?= (int)$stats['latest_batch'] ?>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-muted small mb-3">
                No migration batches are currently applied to roll back.
            </p>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('rollbackModal')">Close</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: Seed Database Modal                                              -->
<!-- ========================================================================= -->
<div id="seedModal" class="modal-backdrop-custom">
    <div class="bg-white rounded-3 shadow-lg p-4" style="width: 90%; max-width: 500px;">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <div>
                <h3 class="h5 fw-bold text-dark mb-0">Run Platform Database Seeder</h3>
                <span class="text-muted small">Populate baseline tables</span>
            </div>
        </div>

        <form action="<?= site_url('migrations/seed') ?>" method="POST" onsubmit="return showExecuting('Executing selected seeder...');">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark" for="seederSelect">Select Seeder Class</label>
                <select name="seeder" id="seederSelect" class="form-select form-select-sm">
                    <?php foreach ($seeders as $s): ?>
                        <option value="<?= esc($s['class']) ?>" <?= ($s['class'] === 'InitialSeeder' || $s['class'] === 'DatabaseSeeder') ? 'selected' : '' ?>>
                            <?= esc($s['class']) ?> (<?= esc($s['size']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small text-muted">
                    <code>InitialSeeder</code> inserts primary enterprise companies, users, demo credentials, chart of accounts, and SaaS subscription tiers.
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('seedModal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                    <i class="fa-solid fa-play me-1"></i> Run Seeder
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: Refresh & Reseed Danger Confirmation Modal                       -->
<!-- ========================================================================= -->
<div id="refreshModal" class="modal-backdrop-custom">
    <div class="bg-white rounded-3 shadow-lg p-4 border-top border-4 border-danger" style="width: 90%; max-width: 520px;">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="h5 fw-bold text-danger mb-0">CAUTION: Database Refresh &amp; Reseed</h3>
                <span class="text-muted small">Complete clean slate rebuild</span>
            </div>
        </div>

        <div class="alert alert-danger py-2 px-3 small mb-3">
            <strong>Warning:</strong> This operation drops all application tables in <code><?= esc($stats['db_name']) ?></code>, re-runs all <strong><?= count($migrations) ?></strong> migrations from batch 1, and reseeds initial platform data. Any ad-hoc changes will be reset.
        </div>

        <form action="<?= site_url('migrations/refresh-seed') ?>" method="POST" id="refreshForm" onsubmit="return validateRefreshSafety();">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-bold text-dark" for="confirmResetInput">
                    Type <code class="text-danger bg-danger-subtle px-1 rounded">CONFIRM</code> to unlock:
                </label>
                <input type="text" id="confirmResetInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="Type CONFIRM here" oninput="checkSafetyUnlock(this)">
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('refreshModal')">Cancel</button>
                <button type="submit" id="btnSubmitRefresh" class="btn btn-danger btn-sm fw-bold opacity-50" disabled>
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Drop, Migrate &amp; Reseed
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Busy / Executing Overlay Modal -->
<div id="executingOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.85); z-index: 99999; align-items: center; justify-content: center; flex-direction: column; gap: 16px;">
    <div style="width: 48px; height: 48px; border: 4px solid rgba(255,255,255,0.2); border-top-color: #38bdf8; border-radius: 50%; animation: spin 1s linear infinite;"></div>
    <div id="executingMsg" style="color: #f8fafc; font-size: 16px; font-weight: 700; font-family: sans-serif;">Executing database operation...</div>
    <div style="color: #94a3b8; font-size: 12.5px;">Please do not refresh or close the browser.</div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('show');
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('show');
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function openRollbackModal() {
    openModal('rollbackModal');
}

function checkSafetyUnlock(input) {
    const btn = document.getElementById('btnSubmitRefresh');
    if (!btn) return;
    if (input.value.trim() === 'CONFIRM') {
        btn.disabled = false;
        btn.classList.remove('opacity-50');
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50');
        btn.style.cursor = 'not-allowed';
    }
}

function validateRefreshSafety() {
    const input = document.getElementById('confirmResetInput');
    if (!input || input.value.trim() !== 'CONFIRM') {
        alert('Please type CONFIRM to proceed.');
        return false;
    }
    return showExecuting('Rebuilding database from scratch...');
}

function showExecuting(message) {
    const overlay = document.getElementById('executingOverlay');
    const msg = document.getElementById('executingMsg');
    if (overlay && msg) {
        msg.textContent = message || 'Executing database operation...';
        overlay.style.display = 'flex';
    }
    return true;
}

function copyConsoleOutput() {
    const content = document.getElementById('terminalContent');
    if (!content) return;
    navigator.clipboard.writeText(content.textContent).then(() => {
        alert('Terminal log copied to clipboard!');
    }).catch(err => {
        alert('Could not copy log text.');
    });
}

function copyInspectedCode() {
    const content = document.getElementById('codeModalContent');
    if (!content) return;
    navigator.clipboard.writeText(content.textContent).then(() => {
        alert('Migration source code copied to clipboard!');
    });
}

function inspectMigrationCode(filename) {
    openModal('codeInspectorModal');
    const titleEl = document.getElementById('codeModalTitle');
    const metaEl = document.getElementById('codeModalMeta');
    const contentEl = document.getElementById('codeModalContent');

    titleEl.textContent = filename;
    metaEl.textContent = 'Loading source code from server...';
    contentEl.textContent = 'Loading...';

    fetch('<?= site_url('migrations/view') ?>/' + encodeURIComponent(filename), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            metaEl.textContent = 'Error loading file';
            contentEl.textContent = data.error;
        } else {
            metaEl.textContent = `${data.lines} lines • Size: ${data.filesize}`;
            contentEl.textContent = data.code;
        }
    })
    .catch(err => {
        metaEl.textContent = 'Network Error';
        contentEl.textContent = 'Failed to load source code: ' + err.message;
    });
}

// Close modals when clicking outside modal body
window.addEventListener('click', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('modal-backdrop-custom')) {
        closeModal(e.target.id);
    }
});
</script>
<?= $this->endSection() ?>

