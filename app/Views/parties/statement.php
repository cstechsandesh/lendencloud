<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$totalDebit = 0;
$totalCredit = 0;
foreach ($transactions as $tx) {
    $totalDebit += (float)$tx['debit'];
    $totalCredit += (float)$tx['credit'];
}
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="<?= base_url('parties') ?>" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i>Back to Parties Directory
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark"><?= esc($party['party_name']) ?></h4>
            <span class="badge badge-type badge-type-<?= esc($party['party_type']) ?>">
                <?= ucfirst($party['party_type']) ?>
            </span>
            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                <?= esc($party['party_code'] ?: 'ID #' . $party['party_id']) ?>
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">Detailed chronological transaction statement &amp; running balance ledger.</p>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
        <form method="get" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="from" class="form-control form-control-sm border-start-0" value="<?= esc($date_from) ?>" title="Start Date">
            </div>
            <span class="text-muted small">to</span>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
                <input type="date" name="to" class="form-control form-control-sm border-start-0" value="<?= esc($date_to) ?>" title="End Date">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </form>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print Statement
        </button>
    </div>
</div>

<!-- Statement Financial KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-4">
                    <i class="bi bi-person-badge fs-3"></i>
                </div>
                <div class="overflow-hidden">
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Party Info</small>
                    <div class="fw-bold text-dark text-truncate" title="<?= esc($party['party_name']) ?>"><?= esc($party['party_name']) ?></div>
                    <small class="text-muted text-truncate d-block">GST: <code><?= esc($party['party_gstin'] ?: 'Unregistered') ?></code></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border p-3 bg-white" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success p-3 rounded-4">
                    <i class="bi bi-arrow-up-right-circle fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Invoiced (DR)</small>
                    <h4 class="fw-bold text-dark mb-0"><?= esc($currency_symbol) ?> <?= number_format($totalDebit, 2) ?></h4>
                    <small class="text-muted">Total billing charges</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border p-3 bg-white" style="border-left: 4px solid #6366f1 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info-subtle text-info p-3 rounded-4">
                    <i class="bi bi-arrow-down-left-circle fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Settled (CR)</small>
                    <h4 class="fw-bold text-dark mb-0"><?= esc($currency_symbol) ?> <?= number_format($totalCredit, 2) ?></h4>
                    <small class="text-muted">Payments &amp; credits</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border p-3 bg-white" style="border-left: 4px solid <?= $closing >= 0 ? '#10b981' : '#ef4444' ?> !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="<?= $closing >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> p-3 rounded-4">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Net Closing Balance</small>
                    <h4 class="fw-bold <?= $closing >= 0 ? 'text-success' : 'text-danger' ?> mb-0"><?= esc($currency_symbol) ?> <?= number_format(abs($closing), 2) ?></h4>
                    <small class="<?= $closing >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                        <?= $closing >= 0 ? 'Receivable (DR)' : 'Payable (CR)' ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statement Transactions Table Card -->
<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-journal-text fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Statement of Account Ledger</h6>
                <small class="text-muted">Invoices, bills, receipts, payments &amp; running balances</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($transactions) ?> Postings on Record
            </span>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Date</th>
                        <th>Reference #</th>
                        <th>Transaction Type</th>
                        <th>Narration / Memo</th>
                        <th class="text-end" style="width: 150px;">Debit (DR)</th>
                        <th class="text-end" style="width: 150px;">Credit (CR)</th>
                        <th class="text-end" style="width: 160px;">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No billing or voucher transactions on record.</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('d M Y', strtotime($tx['tx_date'])) ?></td>
                                <td class="fw-bold text-dark"><code><?= esc($tx['ref_no']) ?></code></td>
                                <td><span class="badge bg-light text-secondary border"><?= esc($tx['tx_type']) ?></span></td>
                                <td class="text-muted"><?= esc($tx['narration'] ?: '—') ?></td>
                                <td class="text-end text-success fw-semibold">
                                    <?= $tx['debit'] > 0 ? number_format($tx['debit'], 2) : '—' ?>
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    <?= $tx['credit'] > 0 ? number_format($tx['credit'], 2) : '—' ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= esc($currency_symbol) ?> <?= number_format($tx['running_balance'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold fs-6 border-top-2">
                    <tr>
                        <td colspan="4" class="text-end text-muted">Period Totals:</td>
                        <td class="text-end text-success"><?= esc($currency_symbol) ?> <?= number_format($totalDebit, 2) ?></td>
                        <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($totalCredit, 2) ?></td>
                        <td class="text-end <?= $closing >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= esc($currency_symbol) ?> <?= number_format($closing, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
