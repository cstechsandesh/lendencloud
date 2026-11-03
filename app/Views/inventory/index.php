<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Products Catalog & Stock Inventory</h4>
        <p class="text-muted small mb-0">Master catalog of items, purchase & sale pricing, tax rates, and on-hand stock.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('inventory/adjustments') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-sliders me-1"></i>Stock Adjustments
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-lg me-1"></i>Add Product
        </button>
    </div>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-box-seam-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Products &amp; Stock Master</h6>
                <small class="text-muted">Master SKU inventory items, prices, and stock on hand</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($products) ?> SKUs Registered
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
                <tr>
                    <th>SKU / Code</th>
                    <th>Product Title</th>
                    <th>Category</th>
                    <th class="text-end">Cost Price</th>
                    <th class="text-end">Selling Price</th>
                    <th>Tax Rate</th>
                    <th class="text-center">Current Stock</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <?php $isLow = ($p['product_current_stock'] <= $p['product_reorder_level']); ?>
                    <tr class="<?= $isLow ? 'table-warning' : '' ?>">
                        <td><code><?= esc($p['product_code']) ?></code></td>
                        <td>
                            <div class="fw-bold text-dark"><?= esc($p['product_name']) ?></div>
                            <small class="text-muted">HSN: <?= esc($p['product_hsn_code'] ?: '—') ?> | Unit: <?= esc($p['product_unit']) ?></small>
                        </td>
                        <td><?= esc($p['category_name'] ?? 'Uncategorized') ?></td>
                        <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($p['product_purchase_price'], 2) ?></td>
                        <td class="text-end fw-semibold text-primary"><?= esc($currency_symbol) ?> <?= number_format($p['product_selling_price'], 2) ?></td>
                        <td><?= esc($p['tax_name'] ?: '0%') ?></td>
                        <td class="text-center">
                            <span class="badge <?= $isLow ? 'bg-danger' : 'bg-success' ?> fs-6 px-3 py-1">
                                <?= number_format($p['product_current_stock'], 2) ?> <?= esc($p['product_unit']) ?>
                            </span>
                            <?php if ($isLow): ?>
                                <div class="text-danger small fw-semibold mt-1">Reorder: <?= number_format($p['product_reorder_level'], 2) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('inventory/products/stock/' . $p['product_id']) ?>" class="btn btn-outline-info" title="Stock Movement History">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                    data-bs-toggle="modal" data-bs-target="#editProductModal"
                                    data-action="<?= base_url('inventory/products/update/' . $p['product_id']) ?>"
                                    data-item='<?= json_encode($p) ?>'
                                    title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= base_url('inventory/products/delete/' . $p['product_id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Archive product?');" title="Archive">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Add New Product to Catalog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('inventory/products/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">SKU / Item Code</label>
                            <input type="text" name="product_code" class="form-control" placeholder="Auto-generated if blank">
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">Product Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" placeholder="e.g. ThinkPad X1 Carbon" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="product_category_id" class="form-select">
                                <option value="">— Uncategorized —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= esc($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Unit of Measure</label>
                            <select name="product_unit" class="form-select">
                                <option value="Nos">Numbers (Nos)</option>
                                <option value="Pcs">Pieces (Pcs)</option>
                                <option value="Boxes">Boxes</option>
                                <option value="Kg">Kilograms (Kg)</option>
                                <option value="Ltr">Liters (Ltr)</option>
                                <option value="Mtr">Meters (Mtr)</option>
                                <option value="Set">Sets</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">HSN / SAC Code</label>
                            <input type="text" name="product_hsn_code" class="form-control" placeholder="e.g. 84713010">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Purchase / Cost Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                                <input type="number" step="0.01" name="product_purchase_price" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Selling / MRP Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                                <input type="number" step="0.01" name="product_selling_price" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Default Tax Rate</label>
                            <select name="product_tax_rate_id" class="form-select">
                                <option value="">0% Exempted</option>
                                <?php foreach ($taxRates as $tr): ?>
                                    <option value="<?= $tr['tax_id'] ?>"><?= esc($tr['tax_name']) ?> (<?= $tr['tax_rate'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Opening Stock Quantity</label>
                            <input type="number" step="0.001" name="product_opening_stock" class="form-control" value="0.000">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Reorder Threshold Level</label>
                            <input type="number" step="0.001" name="product_reorder_level" class="form-control" value="5.000">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Specifications</label>
                            <textarea name="product_description" class="form-control" rows="2" placeholder="Item description, technical specs, or warranty terms..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Product Master</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">SKU / Item Code <span class="text-danger">*</span></label>
                            <input type="text" name="product_code" class="form-control" required>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">Product Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="product_category_id" class="form-select">
                                <option value="">— Uncategorized —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= esc($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Unit of Measure</label>
                            <select name="product_unit" class="form-select">
                                <option value="Nos">Numbers (Nos)</option>
                                <option value="Pcs">Pieces (Pcs)</option>
                                <option value="Boxes">Boxes</option>
                                <option value="Kg">Kilograms (Kg)</option>
                                <option value="Ltr">Liters (Ltr)</option>
                                <option value="Mtr">Meters (Mtr)</option>
                                <option value="Set">Sets</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">HSN / SAC Code</label>
                            <input type="text" name="product_hsn_code" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Purchase / Cost Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                                <input type="number" step="0.01" name="product_purchase_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Selling / MRP Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                                <input type="number" step="0.01" name="product_selling_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Default Tax Rate</label>
                            <select name="product_tax_rate_id" class="form-select">
                                <option value="">0% Exempted</option>
                                <?php foreach ($taxRates as $tr): ?>
                                    <option value="<?= $tr['tax_id'] ?>"><?= esc($tr['tax_name']) ?> (<?= $tr['tax_rate'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Reorder Threshold Level</label>
                            <input type="number" step="0.001" name="product_reorder_level" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="product_is_active" id="edit_prod_active" value="1">
                                <label class="form-check-label small fw-semibold" for="edit_prod_active">Active in Catalog</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description / Specifications</label>
                            <textarea name="product_description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
