<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <a href="<?= base_url('users') ?>" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Back to Users & Operators
                </a>
                <h4 class="fw-bold mb-0 mt-1">
                    <?= !empty($readOnly) ? 'User Profile: ' : 'Edit User: ' ?><?= esc($user['user_name']) ?>
                </h4>
            </div>
            <div>
                <span class="badge <?= $user['user_status'] === 'active' ? 'bg-success' : 'bg-danger' ?> px-3 py-2">
                    <?= ucfirst($user['user_status']) ?> Account
                </span>
            </div>
        </div>

        <?php if ($user['user_role'] === 'super_admin'): ?>
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-shield-check fs-4 text-primary"></i>
                <div class="small">
                    <strong>Master Super Administrator Account:</strong> This user holds root privileges across all companies and branches.
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary p-2 rounded-3">
                        <i class="bi bi-person-gear fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= !empty($readOnly) ? 'Operator Profile' : 'Edit Account Details' ?></h6>
                        <small class="text-muted">Manage profile identity, security credentials &amp; RBAC privileges</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('users/update/' . $user['user_id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Legal Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" value="<?= esc($user['user_name']) ?>" required <?= !empty($readOnly) ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" class="form-control bg-light font-monospace" value="<?= esc($user['user_username']) ?>" readonly disabled>
                            <div class="form-text small text-muted">Usernames are immutable for audit log integrity.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="user_email" class="form-control" value="<?= esc($user['user_email']) ?>" <?= !empty($readOnly) ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" name="user_phone" class="form-control" value="<?= esc($user['user_phone']) ?>" <?= !empty($readOnly) ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Job Designation</label>
                            <input type="text" name="user_designation" class="form-control" value="<?= esc($user['user_designation']) ?>" <?= !empty($readOnly) ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Reset Password</label>
                            <input type="password" name="user_password" class="form-control" placeholder="••••••••" <?= !empty($readOnly) ? 'disabled' : '' ?>>
                            <div class="form-text small text-muted">Leave blank to retain current active password.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Assigned RBAC Role <span class="text-danger">*</span></label>
                            <select name="user_role_id" class="form-select" required <?= !empty($readOnly) ? 'disabled' : '' ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['role_id'] ?>" <?= $user['user_role_id'] == $r['role_id'] ? 'selected' : '' ?>>
                                        <?= esc($r['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Account Status</label>
                            <select name="user_status" class="form-select" <?= !empty($readOnly) ? 'disabled' : '' ?>>
                                <option value="active" <?= $user['user_status'] === 'active' ? 'selected' : '' ?>>Active (Can Login)</option>
                                <option value="inactive" <?= $user['user_status'] === 'inactive' ? 'selected' : '' ?>>Inactive (Suspended)</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="<?= base_url('users') ?>" class="btn btn-light border">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <?php if (empty($readOnly)): ?>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check2 me-1"></i>Save Changes
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
