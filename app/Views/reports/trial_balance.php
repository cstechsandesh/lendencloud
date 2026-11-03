<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Trial Balance Statement</h4>
                <p class="text-muted small mb-0">List of all active general ledger account balances verifying double-entry mathematical balance.</p>
            </div>
            <form method="get" class="d-flex align-items-center gap-2">
                <label class="small fw-semibold text-muted text-nowrap">As of Date:</label>
                <input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($as_of) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Generate</button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
            </form>
        </div>

        <div class="alert <?= $is_balanced ? 'alert-success-subtle border-success-subtle text-success' : 'alert-danger-subtle border-danger-subtle text-danger' ?> d-flex align-items-center p-3 mb-4 rounded-3">
            <i class="bi <?= $is_balanced ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> fs-4 me-3"></i>
            <div>
                <h6 class="fw-bold mb-0"><?= $is_balanced ? 'Double-Entry Trial Balance is Balanced!' : 'Out of Balance Warning!' ?></h6>
                <small>Total Debit equals Total Credit across all ledger accounts (<?= esc($currency_symbol) ?> <?= number_format($grand_debit, 2) ?>).</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Account Code</th>
                        <th>Account Name</th>
                        <th style="width: 150px;">Category</th>
                        <th class="text-end" style="width: 180px;">Debit (DR)</th>
                        <th class="text-end" style="width: 180px;">Credit (CR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><code><?= esc($r['code']) ?></code></td>
                            <td class="fw-semibold text-dark"><?= esc($r['name']) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= esc($r['type']) ?></span></td>
                            <td class="text-end text-success fw-bold">
                                <?= $r['debit'] > 0 ? number_format($r['debit'], 2) : '—' ?>
                            </td>
                            <td class="text-end text-danger fw-bold">
                                <?= $r['credit'] > 0 ? number_format($r['credit'], 2) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fs-5 fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Grand Total:</td>
                        <td class="text-end text-success"><?= esc($currency_symbol) ?> <?= number_format($grand_debit, 2) ?></td>
                        <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($grand_credit, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>