<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-0">

    <!-- KPI Metric Cards Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Monthly Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">Revenue (This Month)</span>
                        <div class="bg-success-subtle text-success p-2 rounded-3">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= esc($currency_symbol) ?> <?= number_format($totalRevenue, 2) ?></h3>
                    <small class="text-muted">Invoiced sales billing</small>
                </div>
            </div>
        </div>

        <!-- Monthly Expenses / Purchases -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">Purchases / Expenses</span>
                        <div class="bg-danger-subtle text-danger p-2 rounded-3">
                            <i class="bi bi-graph-down-arrow fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= esc($currency_symbol) ?> <?= number_format($totalExpenses, 2) ?></h3>
                    <small class="text-muted">Billed inward procurement</small>
                </div>
            </div>
        </div>

        <!-- Accounts Receivable -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">Accounts Receivable</span>
                        <div class="bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= esc($currency_symbol) ?> <?= number_format($accountsReceivable, 2) ?></h3>
                    <small class="text-muted">Debtors due collections</small>
                </div>
            </div>
        </div>

        <!-- Accounts Payable -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">Accounts Payable</span>
                        <div class="bg-warning-subtle text-warning p-2 rounded-3">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= esc($currency_symbol) ?> <?= number_format($accountsPayable, 2) ?></h3>
                    <small class="text-muted">Vendor pending liabilities</small>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row 2: Liquidity & Stock -->
    <div class="row g-3 mb-4">
        <!-- Cash on Hand -->
        <div class="col-md-4">
            <div class="card shadow-sm border p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info-subtle text-info p-3 rounded-4">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Cash in Hand Vault</small>
                        <h4 class="fw-bold text-dark mb-0"><?= esc($currency_symbol) ?> <?= number_format($cashBalance, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="col-md-4">
            <div class="card shadow-sm border p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4">
                        <i class="bi bi-bank fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Operating Bank Balance</small>
                        <h4 class="fw-bold text-dark mb-0"><?= esc($currency_symbol) ?> <?= number_format($bankBalance, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Stock Valuation -->
        <div class="col-md-4">
            <div class="card shadow-sm border p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-secondary-subtle text-dark p-3 rounded-4">
                        <i class="bi bi-boxes fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Stock Valuation</small>
                        <h4 class="fw-bold text-dark mb-0"><?= esc($currency_symbol) ?> <?= number_format($totalStockValue, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Banner (if any) -->
    <?php if (!empty($lowStockProducts)): ?>
        <div class="card border-warning bg-warning-subtle mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="text-dark fw-bold mb-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2"></i>
                        Critical Stock Alerts (<?= count($lowStockProducts) ?> products below reorder threshold)
                    </h6>
                    <a href="<?= base_url('inventory') ?>" class="btn btn-sm btn-outline-dark">View Catalog</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $lp): ?>
                                <tr>
                                    <td><code><?= esc($lp['product_code']) ?></code></td>
                                    <td class="fw-semibold"><?= esc($lp['product_name']) ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($lp['product_current_stock'], 2) ?> <?= esc($lp['product_unit']) ?></td>
                                    <td><?= number_format($lp['product_reorder_level'], 2) ?></td>
                                    <td>
                                        <a href="<?= base_url('purchases/bills/create') ?>" class="btn btn-xs btn-primary py-0 px-2" style="font-size:0.75rem;">Create Bill</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Revenue vs Purchases (Last 6 Months)</span>
                    <span class="badge bg-light text-secondary border">Financial Performance</span>
                </div>
                <div class="card-body">
                    <canvas id="revExpChart" height="120"
                        data-labels='<?= json_encode($chartMonths) ?>'
                        data-revenue='<?= json_encode($revenueData) ?>'
                        data-expense='<?= json_encode($expenseData) ?>'></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Quick Financial Health</span>
                    <i class="bi bi-pie-chart text-muted"></i>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <canvas id="balanceChart" height="200"
                        data-cash="<?= (float)$cashBalance ?>"
                        data-bank="<?= (float)$bankBalance ?>"
                        data-ar="<?= (float)$accountsReceivable ?>"
                        data-ap="<?= (float)$accountsPayable ?>"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Row -->
    <div class="row g-4">
        <!-- Recent Invoices -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-receipt me-2"></i>Recent Sales Invoices</span>
                    <a href="<?= base_url('sales/invoices') ?>" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice#</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentInvoices)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No sales invoices recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentInvoices as $inv): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('sales/invoices/view/' . $inv['invoice_id']) ?>" class="fw-bold text-decoration-none">
                                                <?= esc($inv['invoice_number']) ?>
                                            </a>
                                            <div class="text-muted small"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                                        </td>
                                        <td class="fw-semibold"><?= esc($inv['party_name']) ?></td>
                                        <td class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($inv['invoice_total'], 2) ?></td>
                                        <td>
                                            <?php
                                            $stClass = match($inv['invoice_status']) {
                                                'paid'    => 'bg-success',
                                                'partial' => 'bg-warning text-dark',
                                                'sent'    => 'bg-info text-dark',
                                                default   => 'bg-secondary',
                                            };
                                            ?>
                                            <span class="badge <?= $stClass ?>"><?= ucfirst($inv['invoice_status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Bills -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-file-earmark-text me-2"></i>Recent Vendor Bills</span>
                    <a href="<?= base_url('purchases/bills') ?>" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bill#</th>
                                <th>Vendor</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBills)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No vendor bills recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentBills as $bill): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('purchases/bills/view/' . $bill['bill_id']) ?>" class="fw-bold text-decoration-none">
                                                <?= esc($bill['bill_number']) ?>
                                            </a>
                                            <div class="text-muted small"><?= date('d M Y', strtotime($bill['bill_date'])) ?></div>
                                        </td>
                                        <td class="fw-semibold"><?= esc($bill['party_name']) ?></td>
                                        <td class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($bill['bill_total'], 2) ?></td>
                                        <td>
                                            <?php
                                            $stClass = match($bill['bill_status']) {
                                                'paid'    => 'bg-success',
                                                'partial' => 'bg-warning text-dark',
                                                default   => 'bg-secondary',
                                            };
                                            ?>
                                            <span class="badge <?= $stClass ?>"><?= ucfirst($bill['bill_status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/dashboard.js') ?>"></script>
<?= $this->endSection() ?>

