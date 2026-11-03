<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Profit & Loss Statement (Income Statement)</h4>
                <p class="text-muted small mb-0">Operational performance summarizing all revenues, sales, and operating expenses.</p>
            </div>
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>">
                <span class="text-muted">to</span>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
            </form>
        </div>

        <!-- Net Profit Card -->
        <div class="alert <?= $net_profit >= 0 ? 'alert-success-subtle border-success-subtle' : 'alert-danger-subtle border-danger-subtle' ?> p-4 rounded-4 mb-4 text-center">
            <small class="text-muted text-uppercase fw-bold">Calculated Net Operating Result</small>
            <h2 class="fw-bold mb-0 <?= $net_profit >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= $net_profit >= 0 ? 'Net Profit: ' : 'Net Loss: ' ?>
                <?= esc($currency_symbol) ?> <?= number_format(abs($net_profit), 2) ?>
            </h2>
        </div>

        <div class="row g-4">
            <!-- Revenues Column -->
            <div class="col-lg-6">
                <div class="card h-100 border">
                    <div class="card-header bg-success-subtle text-success py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-6"><i class="bi bi-graph-up me-2"></i>Operating Revenues</span>
                        <span class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($total_rev, 2) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php if (empty($revenues)): ?>
                                    <tr><td colspan="2" class="text-center text-muted py-3">No revenue accounts recorded.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($revenues as $r): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= esc($r['name']) ?></td>
                                            <td class="text-end fw-bold text-success"><?= esc($currency_symbol) ?> <?= number_format($r['amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expenses Column -->
            <div class="col-lg-6">
                <div class="card h-100 border">
                    <div class="card-header bg-danger-subtle text-danger py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-6"><i class="bi bi-graph-down me-2"></i>Operating Expenses & COGS</span>
                        <span class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($total_exp, 2) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php if (empty($expenses)): ?>
                                    <tr><td colspan="2" class="text-center text-muted py-3">No expense accounts recorded.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($expenses as $e): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= esc($e['name']) ?></td>
                                            <td class="text-end fw-bold text-danger"><?= esc($currency_symbol) ?> <?= number_format($e['amount'], 2) ?></td>
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
</div>
<?= $this->endSection() ?>