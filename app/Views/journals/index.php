<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Double-Entry Journal Entries</h4>
        <p class="text-muted small mb-0">Audited double-entry journal vouchers ensuring Debits = Credits at all times.</p>
    </div>
    <a href="<?= base_url('journals/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Journal Voucher
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-file-earmark-diff-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Journal Entries Register</h6>
                <small class="text-muted">Balanced general ledger journal postings with audited dual-sided vouchers</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($entries) ?> Journal Vouchers
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Narration</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $je): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('journals/view/' . $je['journal_id']) ?>" class="fw-bold text-decoration-none">
                                    <?= esc($je['journal_voucher_number']) ?>
                                </a>
                            </td>
                            <td><?= date('d-m-Y', strtotime($je['journal_date'])) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= strtoupper($je['journal_type']) ?></span></td>
                            <td><?= esc($je['journal_narration'] ?: '—') ?></td>
                            <td class="text-end fw-bold text-dark">
                                <?= esc($currency_symbol) ?> <?= number_format($je['total_amount'], 2) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('journals/view/' . $je['journal_id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>