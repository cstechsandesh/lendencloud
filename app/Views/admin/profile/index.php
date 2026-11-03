<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-person me-2"></i>My Profile Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('profile/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Display Name</label>
                        <input type="text" name="user_name" class="form-control" value="<?= esc($user['user_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username</label>
                        <input type="text" class="form-control" value="<?= esc($user['user_username']) ?>" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" name="user_email" class="form-control" value="<?= esc($user['user_email']) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Phone Number</label>
                        <input type="text" name="user_phone" class="form-control" value="<?= esc($user['user_phone']) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('profile/change-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-key me-1"></i>Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>