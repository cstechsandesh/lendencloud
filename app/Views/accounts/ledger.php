<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <a href="<?= base_url('accounts') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Chart of Accounts
                </a>
                <h4 class="fw-bold mb-0">General Ledger: <?= esc($account['account_name']) ?> (<?= esc($account['account_code']) ?>)</h4>
                <p class="text-muted small mb-0">
                    Type: <span class="badge bg-light text-dark border"><?= ucfirst($account['account_type']) ?></span> |
                    Normal Balance: <span class="badge bg-light text-dark border"><?= strtoupper($account['account_balance_type']) ?></span>
                </p>
            </div>

            <!-- Date Filter Form -->
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($date_from) ?>">
                <span class="text-muted">to</span>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($date_to) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>

        <!-- Ledger Financial Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted text-uppercase fw-semibold">Opening Balance</small>
                    <h5 class="fw-bold mb-0"><?= esc($currency_symbol) ?> <?= number_format($summary['opening_balance'], 2) ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted text-uppercase fw-semibold">Total Debit (DR)</small>
                    <h5 class="fw-bold text-success mb-0"><?= esc($currency_symbol) ?> <?= number_format($summary['total_debit'], 2) ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted text-uppercase fw-semibold">Total Credit (CR)</small>
                    <h5 class="fw-bold text-danger mb-0"><?= esc($currency_symbol) ?> <?= number_format($summary['total_credit'], 2) ?></h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-3 text-center border">
                    <small class="text-muted text-uppercase fw-semibold">Closing Balance</small>
                    <h5 class="fw-bold text-primary mb-0"><?= esc($currency_symbol) ?> <?= number_format($summary['closing_balance'], 2) ?></h5>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Date</th>
                        <th style="width: 140px;">Voucher #</th>
                        <th>Type</th>
                        <th>Particulars / Narration</th>
                        <th class="text-end" style="width: 140px;">Debit (DR)</th>
                        <th class="text-end" style="width: 140px;">Credit (CR)</th>
                        <th class="text-end" style="width: 150px;">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light text-muted">
                        <td colspan="4"><em>Opening Balance Forward</em></td>
                        <td class="text-end">—</td>
                        <td class="text-end">—</td>
                        <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($summary['opening_balance'], 2) ?></td>
                    </tr>
                    <?php if (empty($entries)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No journal transactions recorded in this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($entries as $e): ?>
                            <tr>
                                <td><?= date('d-m-Y', strtotime($e['journal_date'])) ?></td>
                                <td>
                                    <a href="<?= base_url('journals/view/' . $e['jl_journal_id']) ?>" class="fw-bold text-decoration-none">
                                        <?= esc($e['journal_voucher_number']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?= ucfirst($e['journal_type']) ?></span></td>
                                <td><?= esc($e['jl_narration'] ?: $e['journal_narration']) ?></td>
                                <td class="text-end text-success fw-semibold">
                                    <?= $e['jl_debit'] > 0 ? number_format($e['jl_debit'], 2) : '—' ?>
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= $e['jl_credit'] > 0 ? number_format($e['jl_credit'], 2) : '—' ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= esc($currency_symbol) ?> <?= number_format($e['running_balance'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>