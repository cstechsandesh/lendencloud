<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Edit Category: <?= esc($category['category_name']) ?></h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('inventory/categories/update/' . $category['category_id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Category Title <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" value="<?= esc($category['category_name']) ?>" required>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('inventory/categories') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>