<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sales Orders</h4>
        <p class="text-muted small mb-0">Track customer quotations and confirmed sales commitments.</p>
    </div>
    <a href="<?= base_url('sales/orders/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Sales Order
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-cart-check-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Sales Orders Register</h6>
                <small class="text-muted">Customer purchase requests, confirmed commitments &amp; expected delivery dates</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($orders) ?> Orders Placed
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Expected Delivery</th>
                        <th class="text-end">Total Amount</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $so): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('sales/orders/view/' . $so['so_id']) ?>" class="fw-bold text-decoration-none">
                                    <?= esc($so['so_number']) ?>
                                </a>
                            </td>
                            <td class="fw-semibold text-dark"><?= esc($so['party_name']) ?></td>
                            <td><?= date('d-m-Y', strtotime($so['so_date'])) ?></td>
                            <td><?= $so['so_expected_delivery'] ? date('d-m-Y', strtotime($so['so_expected_delivery'])) : '—' ?></td>
                            <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($so['so_total'], 2) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary border"><?= ucfirst(str_replace('_', ' ', $so['so_status'])) ?></span></td>
                            <td class="text-center">
                                <a href="<?= base_url('sales/orders/view/' . $so['so_id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<?= $this->endSection() ?>