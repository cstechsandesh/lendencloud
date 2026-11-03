<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">RBAC Roles & Permissions Matrix</h4>
        <p class="text-muted small mb-0">Role-Based Access Control matrix configuring modular view, create, edit and delete permissions.</p>
    </div>
    <a href="<?= base_url('roles/create') ?>" class="btn btn-primary">
        <i class="bi bi-shield-plus me-1"></i>New Role
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Access Roles Registry</h6>
                <small class="text-muted">Security privileges, functional module actions &amp; user role assignments</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($roles) ?> Roles Configured
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Role Title</th>
                        <th>Key Identifier</th>
                        <th>Privileges Count</th>
                        <th>Assigned Users</th>
                        <th>System Role</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $r): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($r['role_name']) ?></div>
                                <small class="text-muted"><?= esc($r['role_description']) ?></small>
                            </td>
                            <td><code><?= esc($r['role_key']) ?></code></td>
                            <td><span class="badge bg-light text-primary border"><?= $r['role_key'] === 'super_admin' ? 'ALL (Master)' : $r['permission_count'] . ' Permissions' ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= $r['user_count'] ?> Users</span></td>
                            <td>
                                <?php if ($r['role_is_system']): ?>
                                    <span class="badge bg-danger-subtle text-danger border">System Locked</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Custom</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('roles/edit/' . $r['role_id']) ?>" class="btn btn-outline-secondary" title="Edit Privileges">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if (!$r['role_is_system']): ?>
                                        <a href="<?= base_url('roles/delete/' . $r['role_id']) ?>"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete role \'<?= esc($r['role_name']) ?>\'?')"
                                            title="Delete Role">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>