<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Account Groups Hierarchy</h4>
        <p class="text-muted small mb-0">Structural categorization for balance sheet and profit & loss statements.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('accounts') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Chart
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
            <i class="bi bi-plus-lg me-1"></i>Add Account Group
        </button>
    </div>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-diagram-3-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Account Classification Groups</h6>
                <small class="text-muted">Parent-child account hierarchies for Balance Sheet and P&amp;L reporting</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($groups) ?> Groups Configured
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Group ID</th>
                        <th>Group Name</th>
                        <th>Accounting Nature</th>
                        <th>Parent Hierarchy</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $g): ?>
                        <tr>
                            <td><code>#<?= $g['group_id'] ?></code></td>
                            <td class="fw-semibold text-dark"><?= esc($g['group_name']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary border"><?= strtoupper($g['group_type']) ?></span></td>
                            <td><?= $g['group_parent_id'] ? 'Sub-group of #' . $g['group_parent_id'] : '<span class="text-muted">Primary Root</span>' ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                        data-bs-toggle="modal" data-bs-target="#editGroupModal"
                                        data-action="<?= base_url('accounts/groups/update/' . $g['group_id']) ?>"
                                        data-item='<?= json_encode($g) ?>'
                                        title="Edit Group">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('accounts/groups/delete/' . $g['group_id']) ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete account group \'<?= esc($g['group_name']) ?>\'?')"
                                        title="Delete Group">
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

<!-- Modal: Add Group -->
<div class="modal fade" id="addGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Add Account Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('accounts/groups/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control" placeholder="e.g. Current Assets, Operating Expenses" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Accounting Nature <span class="text-danger">*</span></label>
                        <select name="group_type" class="form-select" required>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parent Group</label>
                        <select name="group_parent_id" class="form-select">
                            <option value="">— Primary Root Group —</option>
                            <?php foreach ($groups as $p): ?>
                                <option value="<?= $p['group_id'] ?>"><?= esc($p['group_name']) ?> (<?= ucfirst($p['group_type']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Group -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Account Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGroupForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Group Name <span class="text-danger">*</span></label>
                        <input type="text" name="group_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Accounting Nature <span class="text-danger">*</span></label>
                        <select name="group_type" class="form-select" required>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="revenue">Revenue</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parent Group</label>
                        <select name="group_parent_id" class="form-select">
                            <option value="">— Primary Root Group —</option>
                            <?php foreach ($groups as $p): ?>
                                <option value="<?= $p['group_id'] ?>"><?= esc($p['group_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
