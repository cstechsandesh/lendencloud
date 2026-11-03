<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Vendor Bill: <?= esc($bill['bill_number']) ?></h5>
                    <small class="text-muted">Recorded: <?= date('d M Y', strtotime($bill['bill_date'])) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary px-3 py-2 fs-6"><?= ucfirst($bill['bill_status']) ?></span>
                    <a href="<?= base_url('purchases/bills') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase fw-bold">Vendor / Supplier:</h6>
                        <h5 class="fw-bold text-dark mb-1"><?= esc($bill['party_name']) ?></h5>
                        <div class="text-muted small">GSTIN: <code><?= esc($bill['party_gstin'] ?: 'Unregistered') ?></code></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted small text-uppercase fw-bold">Payable Total:</h6>
                        <div class="fs-4 fw-bold text-primary"><?= esc($currency_symbol) ?> <?= number_format($bill['bill_total'], 2) ?></div>
                        <div class="text-danger fw-semibold">Balance Due: <?= esc($currency_symbol) ?> <?= number_format($bill['bill_balance'], 2) ?></div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product / Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($bill['items'] as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($item['product_name']) ?></div>
                                        <small class="text-muted">SKU: <?= esc($item['product_code']) ?></small>
                                    </td>
                                    <td class="text-center"><?= number_format($item['bi_quantity'], 2) ?> <?= esc($item['product_unit']) ?></td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['bi_unit_price'], 2) ?></td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['bi_tax_amount'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($item['bi_line_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">Subtotal:</td>
                                <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($bill['bill_subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end">GST Input Tax Credit:</td>
                                <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($bill['bill_tax_amount'], 2) ?></td>
                            </tr>
                            <tr class="fs-6">
                                <td colspan="5" class="text-end">Grand Total:</td>
                                <td class="text-end text-primary"><?= esc($currency_symbol) ?> <?= number_format($bill['bill_total'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>