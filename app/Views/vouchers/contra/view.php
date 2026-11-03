<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h5 class="fw-bold mb-0">Contra Voucher: <?= esc($voucher['voucher_number']) ?></h5>
                    <small class="text-muted">Recorded: <?= date('d M Y', strtotime($voucher['voucher_date'])) ?></small>
                </div>
                <a href="<?= base_url('vouchers/contra') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info-subtle border border-info-subtle text-dark p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Transfer Amount</small>
                        <h2 class="fw-bold text-primary mb-0"><?= esc($currency_symbol) ?> <?= number_format($voucher['voucher_amount'], 2) ?></h2>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-6">Posted</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Source Account (CR)</small>
                            <h6 class="fw-bold mb-0 text-danger"><?= esc($voucher['from_account']) ?></h6>
                            <small class="text-muted">Code: <?= esc($voucher['from_code']) ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Destination Account (DR)</small>
                            <h6 class="fw-bold mb-0 text-success"><?= esc($voucher['to_account']) ?></h6>
                            <small class="text-muted">Code: <?= esc($voucher['to_code']) ?></small>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Payment Mode</small>
                        <p class="fw-semibold text-dark mb-0"><?= ucwords(str_replace('_', ' ', $voucher['voucher_payment_mode'] ?? 'bank_transfer')) ?></p>
                    </div>
                    <?php if (!empty($voucher['voucher_reference'])): ?>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase fw-semibold">Reference / UTR</small>
                            <p class="fw-semibold text-dark mb-0 font-monospace"><?= esc($voucher['voucher_reference']) ?></p>
                        </div>
                    <?php endif; ?>
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

