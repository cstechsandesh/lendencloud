<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Tax Invoice: <?= esc($invoice['invoice_number']) ?></h5>
                    <small class="text-muted">Issued: <?= date('d M Y', strtotime($invoice['invoice_date'])) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('sales/invoices/print/' . $invoice['invoice_id']) ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-printer me-1"></i>Print Invoice
                    </a>
                    <a href="<?= base_url('sales/invoices') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase fw-bold">Customer Billed To:</h6>
                        <h5 class="fw-bold text-dark mb-1"><?= esc($invoice['party_name']) ?></h5>
                        <div class="text-muted small"><?= esc($invoice['party_address']) ?></div>
                        <div class="text-muted small">GSTIN: <code><?= esc($invoice['party_gstin'] ?: 'Unregistered') ?></code></div>
                        <div class="text-muted small">Phone: <?= esc($invoice['party_phone'] ?: '—') ?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted small text-uppercase fw-bold">Payment Status:</h6>
                        <div>
                            <?php
                            $stClass = match($invoice['invoice_status']) {
                                'paid'    => 'bg-success',
                                'partial' => 'bg-warning text-dark',
                                default   => 'bg-info text-dark',
                            };
                            ?>
                            <span class="badge <?= $stClass ?> px-3 py-1 fs-6"><?= ucfirst($invoice['invoice_status']) ?></span>
                        </div>
                        <div class="mt-2 text-muted small">Due Date: <strong><?= $invoice['invoice_due_date'] ? date('d M Y', strtotime($invoice['invoice_due_date'])) : 'On Receipt' ?></strong></div>
                        <div class="mt-1">
                            Balance Due: <span class="fw-bold fs-5 text-danger"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_balance'], 2) ?></span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th class="text-center">HSN</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Disc %</th>
                                <th class="text-end">GST Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($invoice['items'] as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($item['product_name']) ?></div>
                                        <small class="text-muted">SKU: <?= esc($item['product_code']) ?></small>
                                    </td>
                                    <td class="text-center"><code><?= esc($item['product_hsn_code'] ?: '—') ?></code></td>
                                    <td class="text-center"><?= number_format($item['ii_quantity'], 2) ?> <?= esc($item['product_unit']) ?></td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['ii_unit_price'], 2) ?></td>
                                    <td class="text-center"><?= number_format($item['ii_discount_pct'], 1) ?>%</td>
                                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['ii_tax_amount'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($item['ii_line_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7" class="text-end">Taxable Subtotal:</td>
                                <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end">GST Output Tax:</td>
                                <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_tax_amount'], 2) ?></td>
                            </tr>
                            <tr class="fs-6">
                                <td colspan="7" class="text-end text-dark">Invoice Grand Total:</td>
                                <td class="text-end text-primary"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_total'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end text-success">Amount Paid:</td>
                                <td class="text-end text-success"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_paid_amount'], 2) ?></td>
                            </tr>
                            <tr class="table-warning fs-6">
                                <td colspan="7" class="text-end text-danger">Balance Outstanding:</td>
                                <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_balance'], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($invoice['invoice_notes'])): ?>
                    <div class="alert alert-light border mb-0">
                        <strong>Terms & Conditions / Bank Details:</strong><br>
                        <?= nl2br(esc($invoice['invoice_notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>