<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Physical Stock Adjustments</h4>
        <p class="text-muted small mb-0">Audited inventory count variance write-offs with automatic general ledger postings.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdjustmentModal">
        <i class="bi bi-plus-lg me-1"></i>New Stock Adjustment
    </button>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-sliders fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Stock Adjustments Registry</h6>
                <small class="text-muted">Physical audit reconciliation, write-offs &amp; inventory valuation adjustments</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($adjustments) ?> Adjustments Posted
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th class="text-center">Adjusted Qty</th>
                        <th class="text-end">Cost per Unit</th>
                        <th>Reason / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adjustments as $adj): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i', strtotime($adj['stock_date'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($adj['product_name']) ?></div>
                                <code><?= esc($adj['product_code']) ?></code>
                            </td>
                            <td><?= esc($adj['warehouse_name']) ?></td>
                            <td class="text-center fw-bold <?= $adj['stock_quantity'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $adj['stock_quantity'] >= 0 ? '+' : '' ?><?= number_format($adj['stock_quantity'], 2) ?> <?= esc($adj['product_unit']) ?>
                            </td>
                            <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($adj['stock_unit_cost'], 2) ?></td>
                            <td><?= esc($adj['stock_notes'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Modal: Post Stock Adjustment -->
<div class="modal fade" id="addAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-sliders text-primary me-2"></i>Post Physical Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('inventory/adjustments/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <select name="stock_product_id" class="form-select select2" required>
                            <option value="">— Choose Product —</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['product_id'] ?>"><?= esc($p['product_code']) ?> - <?= esc($p['product_name']) ?> (Current: <?= number_format($p['product_current_stock'], 2) ?> <?= esc($p['product_unit']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Warehouse Location <span class="text-danger">*</span></label>
                        <select name="stock_warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['warehouse_id'] ?>"><?= esc($wh['warehouse_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Adjustment Nature <span class="text-danger">*</span></label>
                            <select name="adjustment_type" class="form-select" required>
                                <option value="increase">Stock In (+) Surplus Found</option>
                                <option value="decrease">Stock Out (-) Damaged / Lost</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" name="stock_quantity" class="form-control" placeholder="1.000" min="0.001" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Cost per Unit</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= esc($currency_symbol) ?></span>
                            <input type="number" step="0.01" name="stock_unit_cost" class="form-control" placeholder="0.00">
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">Leave empty to use item master cost price automatically.</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Audit Reason / Remarks <span class="text-danger">*</span></label>
                        <textarea name="stock_notes" class="form-control" rows="2" placeholder="e.g. Annual physical count variance, damaged during handling..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Post Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
