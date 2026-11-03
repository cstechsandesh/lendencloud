<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Edit Account: <?= esc($account['account_name']) ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('accounts/update/' . $account['account_id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Account Code</label>
                            <input type="text" name="account_code" class="form-control" value="<?= esc($account['account_code']) ?>" <?= $account['account_is_system'] ? 'readonly' : '' ?> required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Account Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" value="<?= esc($account['account_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Accounting Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <?php foreach (['asset', 'liability', 'equity', 'revenue', 'expense'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $account['account_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Normal Balance <span class="text-danger">*</span></label>
                            <select name="account_balance_type" class="form-select" required>
                                <option value="debit" <?= $account['account_balance_type'] === 'debit' ? 'selected' : '' ?>>Debit (DR)</option>
                                <option value="credit" <?= $account['account_balance_type'] === 'credit' ? 'selected' : '' ?>>Credit (CR)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Parent Sub-Group</label>
                            <select name="account_group_id" class="form-select select2">
                                <option value="">None</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['group_id'] ?>" <?= $account['account_group_id'] == $g['group_id'] ? 'selected' : '' ?>>
                                        <?= esc($g['group_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (!$account['account_is_system']): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Opening Balance</label>
                                <input type="number" step="0.01" name="account_opening_balance" class="form-control" value="<?= esc($account['account_opening_balance']) ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="account_description" class="form-control" rows="2"><?= esc($account['account_description']) ?></textarea>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="account_is_active" value="1" <?= $account['account_is_active'] ? 'checked' : '' ?> id="actSw">
                        <label class="form-check-label" for="actSw">Account is Active</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('accounts') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>