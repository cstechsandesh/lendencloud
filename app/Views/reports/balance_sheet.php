<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Balance Sheet (Financial Position)</h4>
                <p class="text-muted small mb-0">Fundamental Accounting Equation: <strong>Assets = Liabilities + Equity</strong>.</p>
            </div>
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($as_of) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Generate</button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
            </form>
        </div>

        <div class="row g-4">
            <!-- Left: ASSETS -->
            <div class="col-lg-6">
                <div class="card border h-100">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between">
                        <span class="fw-bold">ASSETS</span>
                        <span class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($total_assets, 2) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php foreach ($assets as $a): ?>
                                    <tr>
                                        <td>
                                            <code><?= esc($a['account_code']) ?></code>
                                            <span class="fw-semibold ms-2"><?= esc($a['account_name']) ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-dark"><?= esc($currency_symbol) ?> <?= number_format($a['account_current_balance'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fs-6 fw-bold">
                                <tr>
                                    <td>Total Assets:</td>
                                    <td class="text-end text-primary"><?= esc($currency_symbol) ?> <?= number_format($total_assets, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: LIABILITIES & EQUITY -->
            <div class="col-lg-6">
                <div class="card border mb-3">
                    <div class="card-header bg-warning-subtle text-dark py-2 d-flex justify-content-between">
                        <span class="fw-bold">LIABILITIES</span>
                        <span class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($total_liab, 2) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php foreach ($liabilities as $l): ?>
                                    <tr>
                                        <td>
                                            <code><?= esc($l['account_code']) ?></code>
                                            <span class="fw-semibold ms-2"><?= esc($l['account_name']) ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-dark"><?= esc($currency_symbol) ?> <?= number_format($l['account_current_balance'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card border">
                    <div class="card-header bg-info-subtle text-dark py-2 d-flex justify-content-between">
                        <span class="fw-bold">EQUITY & CAPITAL</span>
                        <span class="fw-bold"><?= esc($currency_symbol) ?> <?= number_format($total_equity, 2) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php foreach ($equity as $eq): ?>
                                    <tr>
                                        <td>
                                            <code><?= esc($eq['account_code']) ?></code>
                                            <span class="fw-semibold ms-2"><?= esc($eq['account_name']) ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-dark"><?= esc($currency_symbol) ?> <?= number_format($eq['account_current_balance'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fs-6 fw-bold">
                                <tr>
                                    <td>Total Liabilities & Equity:</td>
                                    <td class="text-end text-dark"><?= esc($currency_symbol) ?> <?= number_format($total_claims, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>