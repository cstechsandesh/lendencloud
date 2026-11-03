<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Edit Account Group: <?= esc($group['group_name']) ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('accounts/groups/update/' . $group['group_id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Group Title</label>
                        <input type="text" name="group_name" class="form-control" value="<?= esc($group['group_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Type</label>
                        <select name="group_type" class="form-select" required>
                            <?php foreach (['asset', 'liability', 'equity', 'revenue', 'expense'] as $t): ?>
                                <option value="<?= $t ?>" <?= $group['group_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Parent Group</label>
                        <select name="group_parent_id" class="form-select select2">
                            <option value="">None (Top-Level)</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= $p['group_id'] ?>" <?= $group['group_parent_id'] == $p['group_id'] ? 'selected' : '' ?>><?= esc($p['group_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('accounts/groups') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>