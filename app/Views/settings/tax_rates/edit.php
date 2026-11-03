<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0">Edit Tax Rate: <?= esc($tax['tax_name']) ?></h5>
                <a href="<?= base_url('settings/tax-rates') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <form action="<?= base_url('settings/tax-rates/update/' . $tax['tax_id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="tax_name" class="form-control" value="<?= esc($tax['tax_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Percentage Rate (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= esc($tax['tax_rate']) ?>" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tax_is_active" id="taxStatusSwitch" value="1" <?= $tax['tax_is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-semibold" for="taxStatusSwitch">Active Status</label>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <a href="<?= base_url('settings/tax-rates') ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

