<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice — <?= esc($invoice['invoice_number']) ?></title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/print-invoice.css') ?>" rel="stylesheet">
</head>
<body>
<div class="invoice-box">
    <div class="no-print d-flex justify-content-end mb-4">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-primary"><?= esc($company_name) ?></h3>
            <div class="text-muted"><?= esc($company_address) ?></div>
            <div class="text-muted">GSTIN: <strong><?= esc($company_gstin) ?></strong> | Phone: <?= esc($company_phone) ?></div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold text-dark mb-0">TAX INVOICE</h4>
            <div class="fs-5 fw-bold text-secondary"><?= esc($invoice['invoice_number']) ?></div>
            <div class="text-muted">Date: <strong><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></strong></div>
            <div class="text-muted">Due: <strong><?= $invoice['invoice_due_date'] ? date('d-m-Y', strtotime($invoice['invoice_due_date'])) : 'Immediate' ?></strong></div>
        </div>
    </div>

    <!-- Bill To -->
    <div class="row mb-4">
        <div class="col-6">
            <h6 class="text-muted small fw-bold text-uppercase">Billed To:</h6>
            <h5 class="fw-bold mb-1"><?= esc($invoice['party_name']) ?></h5>
            <div class="text-muted"><?= esc($invoice['party_address']) ?></div>
            <div class="text-muted">GSTIN: <strong><?= esc($invoice['party_gstin'] ?: 'Unregistered') ?></strong></div>
            <div class="text-muted">Phone: <?= esc($invoice['party_phone']) ?></div>
        </div>
    </div>

    <!-- Line items -->
    <table class="table table-bordered mb-4">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Item Description</th>
                <th class="text-center">HSN</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Rate</th>
                <th class="text-end">GST</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($invoice['items'] as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($item['product_name']) ?></td>
                    <td class="text-center"><?= esc($item['product_hsn_code'] ?: '—') ?></td>
                    <td class="text-center"><?= number_format($item['ii_quantity'], 2) ?> <?= esc($item['product_unit']) ?></td>
                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['ii_unit_price'], 2) ?></td>
                    <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($item['ii_tax_amount'], 2) ?></td>
                    <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($item['ii_line_total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="fw-bold">
            <tr>
                <td colspan="6" class="text-end">Taxable Subtotal:</td>
                <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_subtotal'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end">Total GST:</td>
                <td class="text-end"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_tax_amount'], 2) ?></td>
            </tr>
            <tr class="table-light fs-6">
                <td colspan="6" class="text-end">Grand Total:</td>
                <td class="text-end text-primary"><?= esc($currency_symbol) ?> <?= number_format($invoice['invoice_total'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="border-top pt-4 text-center text-muted small">
        Thank you for your business! This is a computer generated invoice and requires no physical signature.
    </div>
</div>
</body>
</html>