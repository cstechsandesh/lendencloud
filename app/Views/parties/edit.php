<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Edit Party: <?= esc($party['party_name']) ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('parties/update/' . $party['party_id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" class="form-select">
                                <option value="customer" <?= $party['party_type'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="vendor" <?= $party['party_type'] === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                                <option value="both" <?= $party['party_type'] === 'both' ? 'selected' : '' ?>>Both</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Party Code</label>
                            <input type="text" name="party_code" class="form-control" value="<?= esc($party['party_code']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Company / Trade Name <span class="text-danger">*</span></label>
                        <input type="text" name="party_name" class="form-control" value="<?= esc($party['party_name']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="party_email" class="form-control" value="<?= esc($party['party_email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" name="party_phone" class="form-control" value="<?= esc($party['party_phone']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">GSTIN</label>
                            <input type="text" name="party_gstin" class="form-control text-uppercase" value="<?= esc($party['party_gstin']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">PAN</label>
                            <input type="text" name="party_pan" class="form-control text-uppercase" value="<?= esc($party['party_pan']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Address</label>
                        <textarea name="party_address" class="form-control" rows="2"><?= esc($party['party_address']) ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Credit Limit (<?= esc($currency_symbol) ?>)</label>
                            <input type="number" step="0.01" name="party_credit_limit" class="form-control" value="<?= esc($party['party_credit_limit']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Credit Days</label>
                            <input type="number" name="party_credit_days" class="form-control" value="<?= esc($party['party_credit_days']) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('parties') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Update Party</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>