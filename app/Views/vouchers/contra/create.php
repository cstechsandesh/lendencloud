<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Record Bank / Cash Contra Transfer</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('vouchers/contra/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="voucher_number" value="<?= esc($voucher_number) ?>">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-danger">Transfer From (Credit Source) <span class="text-danger">*</span></label>
                            <select name="from_account_id" class="form-select select2" required>
                                <option value="">Select source account...</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['account_id'] ?>"><?= esc($acc['account_code']) ?> — <?= esc($acc['account_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-success">Transfer To (Debit Destination) <span class="text-danger">*</span></label>
                            <select name="to_account_id" class="form-select select2" required>
                                <option value="">Select destination account...</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc['account_id'] ?>"><?= esc($acc['account_code']) ?> — <?= esc($acc['account_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Transfer Amount (<?= esc($currency_symbol) ?>) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="voucher_amount" class="form-control fw-bold fs-5 text-primary" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Posting Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reference / Deposit Slip #</label>
                        <input type="text" name="voucher_reference" class="form-control" placeholder="e.g. Cash deposit slip #10293">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Narration</label>
                        <textarea name="voucher_narration" class="form-control" rows="2" placeholder="e.g. Cash deposited into bank account"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('vouchers/contra') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i>Post Contra Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>