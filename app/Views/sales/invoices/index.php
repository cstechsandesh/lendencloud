<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sales Tax Invoices</h4>
        <p class="text-muted small mb-0">Customer billing with automated double-entry ledger postings and inventory stock adjustments.</p>
    </div>
    <a href="<?= base_url('sales/invoices/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Generate Tax Invoice
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-receipt fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Tax Invoices Register</h6>
                <small class="text-muted">Customer billing, payment settlements &amp; balance receivables</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($invoices) ?> Invoices Issued
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-end">Paid Amount</th>
                    <th class="text-end">Balance Due</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td>
                            <a href="<?= base_url('sales/invoices/view/' . $inv['invoice_id']) ?>" class="fw-bold text-decoration-none">
                                <?= esc($inv['invoice_number']) ?>
                            </a>
                        </td>
                        <td class="fw-semibold text-dark"><?= esc($inv['party_name']) ?></td>
                        <td><?= date('d-m-Y', strtotime($inv['invoice_date'])) ?></td>
                        <td><?= $inv['invoice_due_date'] ? date('d-m-Y', strtotime($inv['invoice_due_date'])) : '—' ?></td>
                        <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($inv['invoice_total'], 2) ?></td>
                        <td class="text-end text-success fw-semibold"><?= esc($currency_symbol) ?> <?= number_format($inv['invoice_paid_amount'], 2) ?></td>
                        <td class="text-end fw-bold <?= $inv['invoice_balance'] > 0 ? 'text-danger' : 'text-muted' ?>">
                            <?= esc($currency_symbol) ?> <?= number_format($inv['invoice_balance'], 2) ?>
                        </td>
                        <td>
                            <?php
                            $stClass = match($inv['invoice_status']) {
                                'paid'    => 'bg-success',
                                'partial' => 'bg-warning text-dark',
                                'sent'    => 'bg-info text-dark',
                                default   => 'bg-secondary',
                            };
                            ?>
                            <span class="badge <?= $stClass ?>"><?= ucfirst($inv['invoice_status']) ?></span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url('sales/invoices/view/' . $inv['invoice_id']) ?>" class="btn btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url('sales/invoices/print/' . $inv['invoice_id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>