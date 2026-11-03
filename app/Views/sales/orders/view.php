<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Sales Order: <?= esc($order['so_number']) ?></h5>
                    <small class="text-muted">Dated: <?= date('d M Y', strtotime($order['so_date'])) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary px-3 py-2 fs-6"><?= ucfirst($order['so_status']) ?></span>
                    <a href="<?= base_url('sales/orders') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase fw-bold">Customer Details:</h6>
                        <h5 class="fw-bold text-dark mb-1"><?= esc($order['party_name']) ?></h5>
                        <div class="text-muted small"><?= esc($order['party_address']) ?></div>
                        <div class="text-muted small">GSTIN: <code><?= esc($order['party_gstin'] ?: 'Unregistered') ?></code></div>
                        <div class="text-muted small">Phone: <?= esc($order['party_phone'] ?: '—') ?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted small text-uppercase fw-bold">Order Summary:</h6>
                        <div>Expected Delivery: <strong><?= $order['so_expected_delivery'] ? date('d M Y', strtotime($order['so_expected_delivery'])) : 'Immediate' ?></strong></div>
                        <div class="fs-4 fw-bold text-primary mt-2"><?= esc($currency_symbol) ?> <?= number_format($order['so_total'], 2) ?></div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Disc %</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Line Total</th>
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
                                    <td class="text-center"><?= number_format($item['soi_quantity'], 2) ?> <?= esc($item['product_unit']) ?></td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['soi_unit_price'], 2) ?></td>
                                    <td class="text-center"><?= number_format($item['soi_discount_pct'], 1) ?>%</td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['soi_tax_amount'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($item['soi_line_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">Subtotal:</td>
                                <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($order['so_subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end">GST Tax:</td>
                                <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($order['so_tax_amount'], 2) ?></td>
                            </tr>
                            <tr class="fs-6">
                                <td colspan="6" class="text-end text-dark">Grand Total:</td>
                                <td class="text-end text-primary"><?= esc($currency_symbol) ?> <?= number_format($order['so_total'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($order['so_notes'])): ?>
                    <div class="alert alert-light border mb-4">
                        <strong>Order Notes:</strong> <?= esc($order['so_notes']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>