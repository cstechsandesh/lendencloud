<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Product Stock Valuation Report</h4>
                <p class="text-muted small mb-0">Financial valuation of all active inventory stock on hand based on acquisition cost.</p>
            </div>
            <div class="text-end">
                <small class="text-muted text-uppercase fw-semibold d-block">Total Inventory Valuation</small>
                <h3 class="fw-bold text-primary mb-0"><?= esc($currency_symbol) ?> <?= number_format($total_valuation, 2) ?></h3>
            </div>
        </div>

            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Product Title</th>
                        <th>Category</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Stock Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><code><?= esc($p['product_code']) ?></code></td>
                            <td class="fw-semibold text-dark"><?= esc($p['product_name']) ?></td>
                            <td><?= esc($p['category_name'] ?? '—') ?></td>
                            <td class="text-center fw-bold"><?= number_format($p['product_current_stock'], 2) ?> <?= esc($p['product_unit']) ?></td>
                            <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($p['product_purchase_price'], 2) ?></td>
                            <td class="text-end fw-bold text-success fs-6"><?= esc($currency_symbol) ?> <?= number_format($p['valuation'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>