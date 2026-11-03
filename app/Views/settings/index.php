<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-2"></i>Organization & Financial Accounting Settings</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('settings/save') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Legal Business / Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= esc($settings['company_name'] ?? 'LenDen Enterprise') ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Official Email Address</label>
                            <input type="email" name="company_email" class="form-control" value="<?= esc($settings['company_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Official Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="<?= esc($settings['company_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Registered Physical Address</label>
                        <textarea name="company_address" class="form-control" rows="2"><?= esc($settings['company_address'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Company GSTIN</label>
                            <input type="text" name="company_gstin" class="form-control text-uppercase" value="<?= esc($settings['company_gstin'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control text-center" value="<?= esc($settings['currency_symbol'] ?? '₹') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Currency Code</label>
                            <input type="text" name="currency_code" class="form-control text-center" value="<?= esc($settings['currency_code'] ?? 'INR') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>