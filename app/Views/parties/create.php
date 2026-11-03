<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Register New Customer / Vendor</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('parties/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Party Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="party_type" id="typeCust" value="customer" checked>
                                    <label class="form-check-label" for="typeCust">Customer</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="party_type" id="typeVend" value="vendor">
                                    <label class="form-check-label" for="typeVend">Vendor / Supplier</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="party_type" id="typeBoth" value="both">
                                    <label class="form-check-label" for="typeBoth">Both</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Party Code / Identifier</label>
                            <input type="text" name="party_code" class="form-control" placeholder="e.g. CUST-002">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Company / Trade Name <span class="text-danger">*</span></label>
                            <input type="text" name="party_name" class="form-control" placeholder="e.g. ABC Enterprises Pvt Ltd" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address</label>
                            <input type="email" name="party_email" class="form-control" placeholder="billing@client.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Mobile / Phone Number</label>
                            <input type="text" name="party_phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">GSTIN (Tax ID)</label>
                            <input type="text" name="party_gstin" class="form-control text-uppercase" placeholder="27AAAAA0000A1Z5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">PAN Number</label>
                            <input type="text" name="party_pan" class="form-control text-uppercase" placeholder="AAAAA0000A">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Billing & Delivery Address</label>
                        <textarea name="party_address" class="form-control" rows="2" placeholder="Full address, City, State, PIN Code"></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Credit Limit (<?= esc($currency_symbol) ?>)</label>
                            <input type="number" step="0.01" name="party_credit_limit" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Credit Period (Days)</label>
                            <input type="number" name="party_credit_days" class="form-control" value="30">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= base_url('parties') ?>" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Party</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>