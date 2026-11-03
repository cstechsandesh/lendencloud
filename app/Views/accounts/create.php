<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Create New Ledger Account</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('accounts/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="account_code" class="form-control" placeholder="e.g. 1020" value="<?= old('account_code') ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Account Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" placeholder="e.g. ICICI Bank Current A/C" value="<?= old('account_name') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Accounting Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Normal Balance <span class="text-danger">*</span></label>
                            <select name="account_balance_type" class="form-select" required>
                                <option value="debit">Debit (DR)</option>
                                <option value="credit">Credit (CR)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Parent Sub-Group</label>
                            <select name="account_group_id" class="form-select select2">
                                <option value="">None / Standalone</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['group_id'] ?>"><?= esc($g['group_name']) ?> (<?= ucfirst($g['group_type']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Opening Balance</label>
                            <input type="number" step="0.01" name="account_opening_balance" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="account_is_active" value="1" checked id="activeCheck">
                                <label class="form-check-label" for="activeCheck">Account is Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Description / Notes</label>
                        <textarea name="account_description" class="form-control" rows="2" placeholder="Optional notes about this account"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('accounts') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>