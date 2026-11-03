<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Record Vendor Purchase Bill</h5>
        <span class="badge bg-danger-subtle text-danger fs-6 px-3 border border-danger-subtle"><?= esc($bill_number) ?></span>
    </div>
    <div class="card-body p-4">
        <form action="<?= base_url('purchases/bills/store') ?>" method="post" id="billForm">
            <?= csrf_field() ?>
            <input type="hidden" name="bill_number" value="<?= esc($bill_number) ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Vendor / Supplier <span class="text-danger">*</span></label>
                    <select name="bill_party_id" class="form-select select2" required>
                        <option value="">Select Vendor...</option>
                        <?php foreach ($parties as $v): ?>
                            <option value="<?= $v['party_id'] ?>"><?= esc($v['party_name']) ?> (GSTIN: <?= esc($v['party_gstin'] ?: 'None') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Due Date</label>
                    <input type="date" name="bill_due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
            </div>

            <!-- Dynamic Line Items -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle" id="billItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 250px;">Item / Raw Material <span class="text-danger">*</span></th>
                            <th style="width: 110px;">Qty</th>
                            <th style="width: 140px;">Unit Cost (<?= esc($currency_symbol) ?>)</th>
                            <th style="width: 110px;">Disc %</th>
                            <th style="width: 130px;">GST Rate</th>
                            <th style="width: 140px;" class="text-end">Line Total (<?= esc($currency_symbol) ?>)</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="product_id[]" class="form-select select-product select2" required>
                                    <option value="">Choose item...</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['product_id'] ?>"><?= esc($p['product_code']) ?> — <?= esc($p['product_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" step="0.001" name="quantity[]" class="form-control row-qty text-center" value="1.000" min="0.001" required></td>
                            <td><input type="number" step="0.01" name="unit_price[]" class="form-control row-price text-end" value="0.00" required></td>
                            <td><input type="number" step="0.01" name="discount_pct[]" class="form-control row-disc text-center" value="0.00"></td>
                            <td>
                                <select name="tax_rate_id[]" class="form-select row-tax">
                                    <option value="0" data-rate="0">0%</option>
                                    <?php foreach ($taxRates as $tr): ?>
                                        <option value="<?= $tr['tax_id'] ?>" data-rate="<?= $tr['tax_rate'] ?>" <?= $tr['tax_rate'] == 18 ? 'selected' : '' ?>>
                                            <?= esc($tr['tax_name']) ?> (<?= $tr['tax_rate'] ?>%)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-end fw-bold row-total">0.00</td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-row"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="7">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addBillRowBtn">
                                    <i class="bi bi-plus-circle me-1"></i>Add Another Line
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <label class="form-label fw-semibold small">Vendor Invoice Memo / Terms</label>
                    <textarea name="bill_notes" class="form-control" rows="3" placeholder="Vendor invoice number, notes..."></textarea>
                </div>
                <div class="col-lg-5">
                    <div class="card bg-light border-0 p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal (Purchase Cost):</span>
                            <span class="fw-semibold" id="dispSubtotal"><?= esc($currency_symbol) ?> 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Input GST Tax Credit:</span>
                            <span class="fw-semibold text-danger" id="dispTax"><?= esc($currency_symbol) ?> 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="fw-bold fs-5 text-dark">Bill Grand Total:</span>
                            <span class="fw-bold fs-5 text-primary" id="dispGrandTotal"><?= esc($currency_symbol) ?> 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('purchases/bills') ?>" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i>Post Vendor Bill</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/order-invoice-calc.js') ?>"></script>
<?= $this->endSection() ?>