<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Warehouses & Storage Hubs</h4>
        <p class="text-muted small mb-0">Locations and fulfillment centers where items are stocked.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
        <i class="bi bi-plus-lg me-1"></i>Add Warehouse
    </button>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-building-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Warehouses &amp; Fulfillment Hubs</h6>
                <small class="text-muted">Storage facilities, physical stock sites &amp; regional depots</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($warehouses) ?> Hubs Configured
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Warehouse ID</th>
                        <th>Warehouse Name</th>
                        <th>Location Address</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($warehouses as $wh): ?>
                        <tr>
                            <td><code>#<?= $wh['warehouse_id'] ?></code></td>
                            <td class="fw-bold text-dark"><?= esc($wh['warehouse_name']) ?></td>
                            <td><?= esc($wh['warehouse_location'] ?: '—') ?></td>
                            <td>
                                <span class="badge <?= $wh['warehouse_is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $wh['warehouse_is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                        data-bs-toggle="modal" data-bs-target="#editWarehouseModal"
                                        data-action="<?= base_url('inventory/warehouses/update/' . $wh['warehouse_id']) ?>"
                                        data-item='<?= json_encode($wh) ?>'
                                        title="Edit Warehouse">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('inventory/warehouses/delete/' . $wh['warehouse_id']) ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete warehouse \'<?= esc($wh['warehouse_name']) ?>\'?')"
                                        title="Delete Warehouse">
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

<!-- Modal: Add Warehouse -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-building text-primary me-2"></i>Add Warehouse Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('inventory/warehouses/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Warehouse Name <span class="text-danger">*</span></label>
                        <input type="text" name="warehouse_name" class="form-control" placeholder="e.g. Central Distribution Hub" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Location / Address</label>
                        <textarea name="warehouse_location" class="form-control" rows="2" placeholder="Facility address, city, state..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Warehouse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Warehouse -->
<div class="modal fade" id="editWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editWarehouseForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Warehouse Name <span class="text-danger">*</span></label>
                        <input type="text" name="warehouse_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Location / Address</label>
                        <textarea name="warehouse_location" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="warehouse_is_active" id="edit_wh_active" value="1">
                        <label class="form-check-label small fw-semibold" for="edit_wh_active">Active Facility</label>
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
