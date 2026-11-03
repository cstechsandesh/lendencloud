<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Product Categories</h4>
        <p class="text-muted small mb-0">Organize your item inventory into classification groups.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-1"></i>New Category
    </button>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-tags-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Item Categories Register</h6>
                <small class="text-muted">Classification taxonomy for inventory items &amp; stock grouping</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($categories) ?> Categories
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Category ID</th>
                        <th>Category Title</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><code>#<?= $cat['category_id'] ?></code></td>
                            <td class="fw-bold text-dark"><?= esc($cat['category_name']) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                        data-action="<?= base_url('inventory/categories/update/' . $cat['category_id']) ?>"
                                        data-item='<?= json_encode($cat) ?>'
                                        title="Edit Category">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('inventory/categories/delete/' . $cat['category_id']) ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete category \'<?= esc($cat['category_name']) ?>\'?')"
                                        title="Delete Category">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-tags text-primary me-2"></i>Add Product Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('inventory/categories/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Title <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. Hardware Components" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Product Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Title <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
