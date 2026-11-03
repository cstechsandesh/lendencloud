<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">GST & Tax Rates Master</h4>
        <p class="text-muted small mb-0">Tax percentages applied across sales invoices, purchase bills, and product masters.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaxModal">
        <i class="bi bi-plus-lg me-1"></i>Add Tax Rate
    </button>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-percent fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">GST &amp; Tax Slabs Register</h6>
                <small class="text-muted">Standard GST tax percentage brackets applied to billing &amp; procurement</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($taxes) ?> Tax Rates Active
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Tax ID</th>
                        <th>Tax Title</th>
                        <th class="text-end">Percentage Rate</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taxes as $t): ?>
                        <tr>
                            <td><code>#<?= $t['tax_id'] ?></code></td>
                            <td class="fw-bold text-dark"><?= esc($t['tax_name']) ?></td>
                            <td class="text-end fw-bold text-primary fs-6 font-monospace"><?= number_format($t['tax_rate'], 2) ?>%</td>
                            <td>
                                <span class="badge <?= $t['tax_is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $t['tax_is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                        data-bs-toggle="modal" data-bs-target="#editTaxModal"
                                        data-action="<?= base_url('settings/tax-rates/update/' . $t['tax_id']) ?>"
                                        data-item='<?= json_encode($t) ?>'
                                        title="Edit Tax Rate">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('settings/tax-rates/delete/' . $t['tax_id']) ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete tax rate \'<?= esc($t['tax_name']) ?>\'?')"
                                        title="Delete Tax Rate">
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

<!-- Modal: Add Tax Rate -->
<div class="modal fade" id="addTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-percent text-primary me-2"></i>Add GST / Tax Rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('settings/tax-rates/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="tax_name" class="form-control" placeholder="e.g. GST 18% (CGST 9% + SGST 9%)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Percentage Rate (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="tax_rate" class="form-control" placeholder="18.00" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Tax Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Tax Rate -->
<div class="modal fade" id="editTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Tax Rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTaxForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="tax_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Percentage Rate (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="tax_rate" class="form-control" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tax_is_active" id="edit_tax_active" value="1">
                        <label class="form-check-label small fw-semibold" for="edit_tax_active">Active Rate</label>
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
