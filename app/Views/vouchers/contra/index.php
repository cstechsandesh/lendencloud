<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bank & Cash Contra Vouchers</h4>
        <p class="text-muted small mb-0">Internal transfers between cash vault and operating bank accounts.</p>
    </div>
    <a href="<?= base_url('vouchers/contra/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Contra Transfer
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-info-subtle text-info p-2 rounded-3">
                <i class="bi bi-arrow-left-right fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Bank &amp; Cash Contra Register</h6>
                <small class="text-muted">Inter-account fund transfers between physical cash vault and business bank accounts</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($contras) ?> Transfers Recorded
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Source Account (CR)</th>
                        <th>Destination Account (DR)</th>
                        <th class="text-end">Transferred Amount</th>
                        <th>Reference</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contras as $c): ?>
                        <tr>
                            <td><a href="<?= base_url('vouchers/contra/view/' . $c['voucher_id']) ?>" class="fw-bold fs-6 text-primary text-decoration-none"><code class="text-primary"><?= esc($c['voucher_number']) ?></code></a></td>
                            <td><?= date('d-m-Y', strtotime($c['voucher_date'])) ?></td>
                            <td class="text-danger fw-semibold"><i class="bi bi-arrow-up-right me-1"></i><?= esc($c['from_account']) ?></td>
                            <td class="text-success fw-semibold"><i class="bi bi-arrow-down-left me-1"></i><?= esc($c['to_account']) ?></td>
                            <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($c['voucher_amount'], 2) ?></td>
                            <td><?= esc($c['voucher_reference'] ?: '—') ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('vouchers/contra/view/' . $c['voucher_id']) ?>" class="btn btn-sm btn-outline-primary" title="View Voucher">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>