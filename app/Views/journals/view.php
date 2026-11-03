<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Journal Voucher: <?= esc($entry['journal_voucher_number']) ?></h5>
                    <small class="text-muted">Recorded on <?= date('d M Y', strtotime($entry['journal_date'])) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle px-3 py-2">Posted & Reconciled</span>
                    <a href="<?= base_url('journals') ?>" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($entry['journal_narration'])): ?>
                    <div class="alert alert-light border mb-4">
                        <strong>Narration / Remarks:</strong> <?= esc($entry['journal_narration']) ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Nature</th>
                                <th class="text-end" style="width: 160px;">Debit (DR)</th>
                                <th class="text-end" style="width: 160px;">Credit (CR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sumD = 0;
                            $sumC = 0;
                            foreach ($entry['lines'] as $line):
                                $sumD += (float)$line['jl_debit'];
                                $sumC += (float)$line['jl_credit'];
                            ?>
                                <tr>
                                    <td><code><?= esc($line['account_code']) ?></code></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($line['account_name']) ?></div>
                                        <?php if (!empty($line['jl_narration'])): ?>
                                            <small class="text-muted"><?= esc($line['jl_narration']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-secondary border"><?= ucfirst($line['account_type']) ?></span></td>
                                    <td class="text-end text-success fw-bold">
                                        <?= $line['jl_debit'] > 0 ? number_format($line['jl_debit'], 2) : '—' ?>
                                    </td>
                                    <td class="text-end text-danger fw-bold">
                                        <?= $line['jl_credit'] > 0 ? number_format($line['jl_credit'], 2) : '—' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold fs-6">
                            <tr>
                                <td colspan="3" class="text-end">Balanced Totals:</td>
                                <td class="text-end text-success"><?= esc($currency_symbol) ?> <?= number_format($sumD, 2) ?></td>
                                <td class="text-end text-danger"><?= esc($currency_symbol) ?> <?= number_format($sumC, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>