<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Day Book</h4>
                <p class="text-muted small mb-0">Daily chronological diary of all financial transactions posted on a specific date.</p>
            </div>
            <form method="get" class="d-flex align-items-center gap-2">
                <label class="small fw-semibold text-muted text-nowrap">Select Day:</label>
                <input type="date" name="date" class="form-control form-control-sm" value="<?= esc($date) ?>">
                <button type="submit" class="btn btn-sm btn-primary">Load Day</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Voucher #</th>
                        <th>Type</th>
                        <th>Narration / Purpose</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No vouchers recorded on <?= date('d M Y', strtotime($date)) ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($entries as $e): ?>
                            <tr>
                                <td><code class="fw-bold fs-6 text-dark"><?= esc($e['journal_voucher_number']) ?></code></td>
                                <td><span class="badge bg-light text-secondary border"><?= strtoupper($e['journal_type']) ?></span></td>
                                <td><?= esc($e['journal_narration'] ?: '—') ?></td>
                                <td class="text-end fw-bold"><?= esc($currency_symbol) ?> <?= number_format($e['total_amount'], 2) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('journals/view/' . $e['journal_id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>