<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Receipt Voucher: <?= esc($voucher['voucher_number']) ?></h5>
                    <small class="text-muted">Recorded: <?= date('d M Y', strtotime($voucher['voucher_date'])) ?></small>
                </div>
                <a href="<?= base_url('vouchers/receipts') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-success-subtle border border-success-subtle text-dark p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Amount Received</small>
                        <h2 class="fw-bold text-success mb-0"><?= esc($currency_symbol) ?> <?= number_format($voucher['voucher_amount'], 2) ?></h2>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-6">Posted & Reconciled</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Customer</small>
                        <h6 class="fw-bold mb-0"><?= esc($voucher['party_name']) ?></h6>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Deposited To Account</small>
                        <h6 class="fw-bold mb-0"><?= esc($voucher['account_name']) ?> (<?= esc($voucher['account_code']) ?>)</h6>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Payment Mode</small>
                        <div><span class="badge bg-light text-dark border"><?= strtoupper($voucher['voucher_payment_mode']) ?></span></div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted text-uppercase fw-semibold">Reference #</small>
                        <div><?= esc($voucher['voucher_reference'] ?: 'None') ?></div>
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