<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Add Tax Rate</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('settings/tax-rates/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tax Title</label>
                        <input type="text" name="tax_name" class="form-control" placeholder="e.g. GST 18%" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Rate Percentage (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control" placeholder="18.00" required>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('settings/tax-rates') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Tax Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>