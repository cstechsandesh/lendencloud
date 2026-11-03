<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0">Create Custom RBAC Role</h5>
    </div>
    <div class="card-body p-4">
        <form action="<?= base_url('roles/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Role Title <span class="text-danger">*</span></label>
                    <input type="text" name="role_name" class="form-control" placeholder="e.g. Procurement Specialist" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Role Slug / Key <span class="text-danger">*</span></label>
                    <input type="text" name="role_key" class="form-control" placeholder="e.g. procurement_specialist" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Description</label>
                    <input type="text" name="role_description" class="form-control" placeholder="Brief outline of duties">
                </div>
            </div>

            <h5 class="fw-bold mb-3 border-bottom pb-2">Module Permissions Matrix</h5>

            <div class="row g-3 mb-4">
                <?php foreach ($permissions as $mod => $perms): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border">
                            <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><?= ucfirst(str_replace('_', ' ', $mod)) ?></span>
                                <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none select-all-mod" style="font-size:0.75rem;">Toggle All</button>
                            </div>
                            <div class="card-body p-3">
                                <?php foreach ($perms as $p): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input perm-check" type="checkbox" name="permissions[]" value="<?= $p['permission_id'] ?>" id="p<?= $p['permission_id'] ?>">
                                        <label class="form-check-label small" for="p<?= $p['permission_id'] ?>">
                                            <strong><?= esc($p['permission_name']) ?></strong>
                                            <div class="text-muted" style="font-size:0.72rem;"><?= esc($p['permission_description']) ?></div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('roles') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Role</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/role-permissions.js') ?>"></script>
<?= $this->endSection() ?>