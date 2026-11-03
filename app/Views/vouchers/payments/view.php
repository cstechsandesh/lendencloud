<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Payment Voucher: <?= esc($voucher['voucher_number']) ?></h5>
                    <small class="text-muted">Recorded: <?= date('d M Y', strtotime($voucher['voucher_date'])) ?></small>
                </div>
                <a href="<?= base_url('vouchers/payments') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-danger-subtle border border-danger-subtle text-dark p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Amount Disbursed</small>
                        <h2 class="fw-bold text-danger mb-0"><?= esc($currency_symbol) ?> <?= number_format($voucher['voucher_amount'], 2) ?></h2>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-6">Posted</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Vendor Paid</small>
                        <h6 class="fw-bold mb-0"><?= esc($voucher['party_name']) ?></h6>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Paid From Account</small>
                        <h6 class="fw-bold mb-0"><?= esc($voucher['account_name']) ?></h6>
                    </div>
                </div>

                <?php if (!empty($voucher['voucher_narration'])): ?>
                    <div class="alert alert-light border mb-0">
                        <strong>Narration:</strong> <?= esc($voucher['voucher_narration']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>