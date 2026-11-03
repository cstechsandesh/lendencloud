<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Vendor Payment Vouchers</h4>
        <p class="text-muted small mb-0">Record bank or cash payments made to suppliers and settle inward bills.</p>
    </div>
    <a href="<?= base_url('vouchers/payments/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Payment Voucher
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-danger-subtle text-danger p-2 rounded-3">
                <i class="bi bi-arrow-up-right-circle-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Vendor Payments Register</h6>
                <small class="text-muted">Outward disbursements, cheque/bank transfers &amp; supplier settlements</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($payments) ?> Payments Disbursed
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Vendor Name</th>
                        <th>Disbursed From Account</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('vouchers/payments/view/' . $p['voucher_id']) ?>" class="fw-bold text-decoration-none">
                                    <?= esc($p['voucher_number']) ?>
                                </a>
                            </td>
                            <td><?= date('d-m-Y', strtotime($p['voucher_date'])) ?></td>
                            <td class="fw-semibold text-dark"><?= esc($p['party_name']) ?></td>
                            <td><?= esc($p['account_name']) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= strtoupper($p['voucher_payment_mode']) ?></span></td>
                            <td class="text-end fw-bold text-danger fs-6">
                                <?= esc($currency_symbol) ?> <?= number_format($p['voucher_amount'], 2) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('vouchers/payments/view/' . $p['voucher_id']) ?>" class="btn btn-sm btn-outline-primary">
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