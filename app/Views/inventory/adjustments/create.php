<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Record Stock Physical Count Adjustment</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('inventory/adjustments/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Product <span class="text-danger">*</span></label>
                        <select name="stock_product_id" id="adjProductSelect" class="form-select select2" required>
                            <option value="">Select Item...</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['product_id'] ?>" data-stock="<?= $p['product_current_stock'] ?>" data-cost="<?= $p['product_purchase_price'] ?>" data-unit="<?= esc($p['product_unit']) ?>">
                                    <?= esc($p['product_code']) ?> — <?= esc($p['product_name']) ?> (On Hand: <?= number_format($p['product_current_stock'], 2) ?> <?= esc($p['product_unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Warehouse <span class="text-danger">*</span></label>
                            <select name="stock_warehouse_id" class="form-select" required>
                                <?php foreach ($warehouses as $wh): ?>
                                    <option value="<?= $wh['warehouse_id'] ?>"><?= esc($wh['warehouse_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Adjustment Direction <span class="text-danger">*</span></label>
                            <select name="adjustment_direction" class="form-select" required>
                                <option value="increase">Increase Stock (+ Inward)</option>
                                <option value="decrease">Decrease Stock (- Outward Write-off)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Adjustment Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" name="stock_quantity" class="form-control" placeholder="0.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Current Stock Valuation Effect</label>
                            <div class="p-2 bg-light border rounded text-muted small" id="valuationPreview">
                                Select product to preview financial impact.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="stock_notes" class="form-control" rows="2" placeholder="e.g. Annual audit count reconciliation / transit breakage" required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('inventory/adjustments') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Post Stock Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>