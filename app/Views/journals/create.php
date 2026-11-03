<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">New Double-Entry Journal Voucher</h5>
        <span class="badge bg-primary-subtle text-primary fs-6 px-3"><?= esc($voucher_number) ?></span>
    </div>
    <div class="card-body p-4">
        <form action="<?= base_url('journals/store') ?>" method="post" id="journalForm">
            <?= csrf_field() ?>
            <input type="hidden" name="journal_type" value="journal">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Posting Date <span class="text-danger">*</span></label>
                    <input type="date" name="journal_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">General Narration / Purpose</label>
                    <input type="text" name="journal_narration" class="form-control" placeholder="Brief narrative explaining this transaction">
                </div>
            </div>

            <!-- Dynamic Lines Table -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle" id="linesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 250px;">Account <span class="text-danger">*</span></th>
                            <th style="width: 180px;">Debit (DR)</th>
                            <th style="width: 180px;">Credit (CR)</th>
                            <th>Line Memo</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1 -->
                        <tr class="line-row">
                            <td>
                                <select name="account_id[]" class="form-select line-account select2" required>
                                    <option value="">Select Account...</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['account_id'] ?>"><?= esc($acc['account_code']) ?> — <?= esc($acc['account_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="debit[]" class="form-control text-end line-debit" value="0.00"></td>
                            <td><input type="number" step="0.01" name="credit[]" class="form-control text-end line-credit" value="0.00"></td>
                            <td><input type="text" name="line_narration[]" class="form-control form-control-sm" placeholder="Memo"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="line-row">
                            <td>
                                <select name="account_id[]" class="form-select line-account select2" required>
                                    <option value="">Select Account...</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['account_id'] ?>"><?= esc($acc['account_code']) ?> — <?= esc($acc['account_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="debit[]" class="form-control text-end line-debit" value="0.00"></td>
                            <td><input type="number" step="0.01" name="credit[]" class="form-control text-end line-credit" value="0.00"></td>
                            <td><input type="text" name="line_narration[]" class="form-control form-control-sm" placeholder="Memo"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                                    <i class="bi bi-plus-circle me-1"></i>Add Row
                                </button>
                            </td>
                            <td class="text-end text-success fs-6" id="totalDebitDisplay">0.00</td>
                            <td class="text-end text-danger fs-6" id="totalCreditDisplay">0.00</td>
                            <td colspan="2">
                                <span id="balanceStatusBadge" class="badge bg-danger">Out of Balance</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('journals') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4" id="submitBtn" disabled>
                    <i class="bi bi-check2-circle me-1"></i>Post Journal Voucher
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/journal-entry.js') ?>"></script>
<?= $this->endSection() ?>