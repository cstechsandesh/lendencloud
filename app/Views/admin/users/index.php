<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Users & Access Management</h4>
        <p class="text-muted small mb-0">Manage operator profiles, authentication credentials, and RBAC roles.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-1"></i>New User
    </button>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-people fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">System Users &amp; Operators</h6>
                <small class="text-muted">Active operator accounts, security profiles &amp; role permissions</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($users) ?> Users Registered
            </span>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>User / Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Assigned RBAC Role</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($u['user_name']) ?></div>
                                <small class="text-muted"><?= esc($u['user_designation'] ?: 'Operator') ?></small>
                            </td>
                            <td><code><?= esc($u['user_username']) ?></code></td>
                            <td><?= esc($u['user_email'] ?: '—') ?></td>
                            <td>
                                <span class="badge <?= esc($u['role_badge_class'] ?? 'bg-secondary') ?> px-2 py-1">
                                    <?= esc($u['role_name'] ?? ucfirst($u['user_role'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $u['user_status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($u['user_status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <?php
                                        $canEdit = true;
                                        if ($u['user_role'] === 'super_admin' && empty($isSuperAdmin)) {
                                            $canEdit = false;
                                        }
                                    ?>
                                    <?php if ($canEdit): ?>
                                        <a href="<?= base_url('users/edit/' . $u['user_id']) ?>" class="btn btn-outline-primary" title="Edit User (Full Page)">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-action="<?= base_url('users/update/' . $u['user_id']) ?>"
                                            data-item="<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>"
                                            title="Quick Edit (Modal)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary disabled" title="Protected Super Administrator account" disabled>
                                            <i class="bi bi-shield-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($u['user_id'] != session()->get('user_id') && $canEdit): ?>
                                        <a href="<?= base_url('users/delete/' . $u['user_id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Remove user account?');" title="Delete User">
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
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus text-primary me-2"></i>Register System User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('users/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="user_username" class="form-control" placeholder="johndoe" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="user_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="user_email" class="form-control" placeholder="john@company.com">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="user_phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Job Designation</label>
                            <input type="text" name="user_designation" class="form-control" placeholder="Senior Accountant">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Role & Access <span class="text-danger">*</span></label>
                            <select name="user_role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['role_id'] ?>"><?= esc($r['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quick Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Quick Edit System User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="user_username" class="form-control bg-light font-monospace" readonly disabled>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Full Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Job Designation</label>
                            <input type="text" name="user_designation" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="user_email" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="user_phone" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Role & Access <span class="text-danger">*</span></label>
                            <select name="user_role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['role_id'] ?>"><?= esc($r['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="user_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Reset Password (leave blank to keep current)</label>
                            <input type="password" name="user_password" class="form-control" placeholder="••••••••">
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
