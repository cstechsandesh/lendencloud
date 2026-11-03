<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Register New Product to Catalog</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('inventory/products/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">SKU / Item Code</label>
                            <input type="text" name="product_code" class="form-control" placeholder="Auto-generated if empty">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Product Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" placeholder="e.g. Wireless Laser Barcode Scanner" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Category</label>
                            <select name="product_category_id" class="form-select select2">
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= esc($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">HSN / SAC Code</label>
                            <input type="text" name="product_hsn_code" class="form-control" placeholder="e.g. 847160">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Unit of Measure</label>
                            <input type="text" name="product_unit" class="form-control" value="Nos" placeholder="Nos, Pcs, Kg, Box">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Purchase Cost (<?= esc($currency_symbol) ?>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="product_purchase_price" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Selling Price (<?= esc($currency_symbol) ?>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="product_selling_price" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">GST Tax Rate</label>
                            <select name="product_tax_rate_id" class="form-select">
                                <option value="">0% (Exempt)</option>
                                <?php foreach ($taxRates as $tr): ?>
                                    <option value="<?= $tr['tax_id'] ?>" <?= $tr['tax_rate'] == 18 ? 'selected' : '' ?>>
                                        <?= esc($tr['tax_name']) ?> (<?= $tr['tax_rate'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Initial Opening Stock Quantity</label>
                            <input type="number" step="0.001" name="product_opening_stock" class="form-control" value="0.000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Low Stock Alert Level</label>
                            <input type="number" step="0.001" name="product_reorder_level" class="form-control" value="5.000">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Detailed Description</label>
                        <textarea name="product_description" class="form-control" rows="2" placeholder="Specifications, model details, or notes"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('inventory') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>