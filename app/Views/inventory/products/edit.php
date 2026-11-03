<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Edit Product: <?= esc($product['product_name']) ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('inventory/products/update/' . $product['product_id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">SKU / Code</label>
                            <input type="text" name="product_code" class="form-control" value="<?= esc($product['product_code']) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" value="<?= esc($product['product_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Category</label>
                            <select name="product_category_id" class="form-select select2">
                                <option value="">None</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $product['product_category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= esc($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">HSN Code</label>
                            <input type="text" name="product_hsn_code" class="form-control" value="<?= esc($product['product_hsn_code']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Unit</label>
                            <input type="text" name="product_unit" class="form-control" value="<?= esc($product['product_unit']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Purchase Cost</label>
                            <input type="number" step="0.01" name="product_purchase_price" class="form-control" value="<?= esc($product['product_purchase_price']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Selling Price</label>
                            <input type="number" step="0.01" name="product_selling_price" class="form-control" value="<?= esc($product['product_selling_price']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">GST Tax Rate</label>
                            <select name="product_tax_rate_id" class="form-select">
                                <option value="">0% (Exempt)</option>
                                <?php foreach ($taxRates as $tr): ?>
                                    <option value="<?= $tr['tax_id'] ?>" <?= $product['product_tax_rate_id'] == $tr['tax_id'] ? 'selected' : '' ?>>
                                        <?= esc($tr['tax_name']) ?> (<?= $tr['tax_rate'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Reorder Level Threshold</label>
                            <input type="number" step="0.001" name="product_reorder_level" class="form-control" value="<?= esc($product['product_reorder_level']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="product_is_active" value="1" <?= $product['product_is_active'] ? 'checked' : '' ?> id="pAct">
                                <label class="form-check-label" for="pAct">Active in catalog</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="product_description" class="form-control" rows="2"><?= esc($product['product_description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('inventory') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>