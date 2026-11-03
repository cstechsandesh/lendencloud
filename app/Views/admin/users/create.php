<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Register System User</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('users/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="user_name" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                            <input type="text" name="user_username" class="form-control" placeholder="johndoe" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="user_email" class="form-control" placeholder="john@lenden.local">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" name="user_phone" class="form-control" placeholder="9876543210">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                            <input type="password" name="user_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Job Title / Designation</label>
                            <input type="text" name="user_designation" class="form-control" placeholder="e.g. Finance Officer">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">RBAC Access Role <span class="text-danger">*</span></label>
                            <select name="user_role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['role_id'] ?>"><?= esc($r['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status</label>
                            <select name="user_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('users') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Register User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>