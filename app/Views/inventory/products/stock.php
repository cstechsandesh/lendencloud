<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= base_url('inventory') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i>Back to Products
                </a>
                <h4 class="fw-bold mb-0">Stock Movements Ledger: <?= esc($product['product_name']) ?></h4>
                <p class="text-muted small mb-0">SKU: <code><?= esc($product['product_code']) ?></code> | Unit: <?= esc($product['product_unit']) ?></p>
            </div>
            <div class="text-end">
                <small class="text-muted text-uppercase fw-semibold d-block">On-Hand Stock</small>
                <h3 class="fw-bold text-success mb-0"><?= number_format($product['product_current_stock'], 2) ?> <?= esc($product['product_unit']) ?></h3>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Movement Type</th>
                        <th>Warehouse</th>
                        <th class="text-end">Qty In (+)</th>
                        <th class="text-end">Qty Out (-)</th>
                        <th>Reference Source</th>
                        <th>Notes / Memo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No stock ledger entries recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movements as $sm): ?>
                            <tr>
                                <td><?= date('d-m-Y H:i', strtotime($sm['stock_date'])) ?></td>
                                <td>
                                    <?php
                                    $mBadge = match($sm['stock_type']) {
                                        'in'         => 'bg-success',
                                        'out'        => 'bg-danger',
                                        'adjustment' => 'bg-warning text-dark',
                                        default      => 'bg-info text-dark',
                                    };
                                    ?>
                                    <span class="badge <?= $mBadge ?>"><?= strtoupper($sm['stock_type']) ?></span>
                                </td>
                                <td><?= esc($sm['warehouse_name'] ?? 'Main Warehouse') ?></td>
                                <td class="text-end text-success fw-bold">
                                    <?= in_array($sm['stock_type'], ['in', 'transfer_in']) || ($sm['stock_type'] === 'adjustment' && $sm['stock_quantity'] > 0)
                                        ? '+' . number_format(abs($sm['stock_quantity']), 2) : '—' ?>
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    <?= in_array($sm['stock_type'], ['out', 'transfer_out']) || ($sm['stock_type'] === 'adjustment' && $sm['stock_quantity'] < 0)
                                        ? '-' . number_format(abs($sm['stock_quantity']), 2) : '—' ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($sm['stock_reference_type'] ?: 'Manual') ?></span></td>
                                <td><?= esc($sm['stock_notes'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>