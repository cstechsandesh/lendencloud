<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Vendor Bills & Purchases</h4>
        <p class="text-muted small mb-0">Record inward inventory purchases with accounts payable double-entry postings.</p>
    </div>
    <a href="<?= base_url('purchases/bills/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Record Vendor Bill
    </a>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-file-earmark-text-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Vendor Bills Registry</h6>
                <small class="text-muted">Procurement billing, supplier payments &amp; accounts payable liabilities</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($bills) ?> Bills Recorded
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Bill #</th>
                        <th>Vendor Name</th>
                        <th>Date</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Balance Due</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $b): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('purchases/bills/view/' . $b['bill_id']) ?>" class="fw-bold text-decoration-none">
                                    <?= esc($b['bill_number']) ?>
                                </a>
                            </td>
                            <td class="fw-semibold text-dark"><?= esc($b['party_name']) ?></td>
                            <td><?= date('d-m-Y', strtotime($b['bill_date'])) ?></td>
                            <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($b['bill_total'], 2) ?></td>
                            <td class="text-end text-success"><?= esc($currency_symbol) ?> <?= number_format($b['bill_paid_amount'], 2) ?></td>
                            <td class="text-end fw-bold <?= $b['bill_balance'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                <?= esc($currency_symbol) ?> <?= number_format($b['bill_balance'], 2) ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= ucfirst($b['bill_status']) ?></span></td>
                            <td class="text-center">
                                <a href="<?= base_url('purchases/bills/view/' . $b['bill_id']) ?>" class="btn btn-sm btn-outline-primary">
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