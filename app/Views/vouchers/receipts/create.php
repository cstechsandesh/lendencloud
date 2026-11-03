<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Record Customer Receipt Voucher</h5>
        <span class="badge bg-success-subtle text-success fs-6 px-3 border border-success-subtle"><?= esc($voucher_number) ?></span>
    </div>
    <div class="card-body p-4">
        <form action="<?= base_url('vouchers/receipts/store') ?>" method="post" id="receiptForm">
            <?= csrf_field() ?>
            <input type="hidden" name="voucher_number" value="<?= esc($voucher_number) ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Customer <span class="text-danger">*</span></label>
                    <select name="voucher_party_id" id="receiptCustomerSelect" class="form-select select2" required>
                        <option value="">Select Customer...</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['party_id'] ?>"><?= esc($c['party_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Deposit To (Bank / Cash Account) <span class="text-danger">*</span></label>
                    <select name="voucher_account_id" class="form-select select2" required>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc['account_id'] ?>"><?= esc($acc['account_code']) ?> — <?= esc($acc['account_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Receipt Date <span class="text-danger">*</span></label>
                    <input type="date" name="voucher_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Total Amount Received (<?= esc($currency_symbol) ?>) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="voucher_amount" id="receiptTotalAmount" class="form-control fw-bold fs-5 text-success" placeholder="0.00" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Payment Mode</label>
                    <select name="voucher_payment_mode" class="form-select">
                        <option value="bank_transfer">Bank Transfer / NEFT / RTGS</option>
                        <option value="upi">UPI / Instant Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                        <option value="card">Debit / Credit Card</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Transaction Ref / Cheque #</label>
                    <input type="text" name="voucher_reference" class="form-control" placeholder="UTR # / Cheque No">
                </div>
            </div>

            <!-- Invoices Settlement Allocation Table (via AJAX) -->
            <div class="card border mb-4">
                <div class="card-header bg-light py-2">
                    <span class="fw-bold small text-dark"><i class="bi bi-link-45deg me-1"></i>Settle Unpaid Invoices (Optional / Auto-allocated)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="invoicesAllocTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th class="text-end">Invoice Total</th>
                                    <th class="text-end">Current Balance</th>
                                    <th style="width: 200px;" class="text-end">Amount to Settle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5" class="text-center text-muted py-3">Select a customer above to view outstanding invoices.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Narration / Internal Remarks</label>
                <textarea name="voucher_narration" class="form-control" rows="2" placeholder="e.g. Payment received against invoice #..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('vouchers/receipts') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i>Post Receipt Voucher</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/voucher-calc.js') ?>"></script>
<?= $this->endSection() ?>