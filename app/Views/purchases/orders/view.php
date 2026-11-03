<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Purchase Order: <?= esc($order['po_number']) ?></h5>
                    <small class="text-muted">Issued: <?= date('d M Y', strtotime($order['po_date'])) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6"><?= ucfirst($order['po_status']) ?></span>
                    <a href="<?= base_url('purchases/orders') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase fw-bold">Vendor Details:</h6>
                        <h5 class="fw-bold text-dark mb-1"><?= esc($order['party_name']) ?></h5>
                        <div class="text-muted small">GSTIN: <code><?= esc($order['party_gstin'] ?: 'Unregistered') ?></code></div>
                        <div class="text-muted small">Phone: <?= esc($order['party_phone'] ?: '—') ?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted small text-uppercase fw-bold">Order Total:</h6>
                        <div class="fs-4 fw-bold text-primary"><?= esc($currency_symbol) ?> <?= number_format($order['po_total'], 2) ?></div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($item['product_name']) ?></div>
                                        <small class="text-muted">SKU: <?= esc($item['product_code']) ?></small>
                                    </td>
                                    <td class="text-center"><?= number_format($item['poi_quantity'], 2) ?> <?= esc($item['product_unit']) ?></td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['poi_unit_price'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($item['poi_line_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>