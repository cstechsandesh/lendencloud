<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Register New Warehouse Location</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('inventory/warehouses/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Warehouse Title <span class="text-danger">*</span></label>
                        <input type="text" name="warehouse_name" class="form-control" placeholder="e.g. West Coast Distribution Hub" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Location Address</label>
                        <textarea name="warehouse_location" class="form-control" rows="2" placeholder="Physical address"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('inventory/warehouses') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Warehouse</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>