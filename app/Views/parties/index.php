<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">Parties Management</h4>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill" style="font-size: 0.72rem;">Directory</span>
        </div>
        <p class="text-muted small mb-0 mt-1">Directory of customers, vendors, and their trade account ledger balances.</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Modern Segmented Filter Control -->
        <div class="segmented-control">
            <a href="<?= base_url('parties') ?>" class="segmented-control-item <?= empty($current_type) ? 'active' : '' ?>">All Parties</a>
            <a href="<?= base_url('parties?type=customer') ?>" class="segmented-control-item <?= $current_type === 'customer' ? 'active' : '' ?>">Customers</a>
            <a href="<?= base_url('parties?type=vendor') ?>" class="segmented-control-item <?= $current_type === 'vendor' ? 'active' : '' ?>">Vendors</a>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addPartyModal">
            <i class="bi bi-person-plus-fill me-1"></i>New Party
        </button>
    </div>
</div>

<div class="card shadow-sm border">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-people-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Party Master Directory</h6>
                <small class="text-muted">Filtered trade accounts &amp; current ledger balances</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace" style="font-size: 0.75rem;">
                <?= count($parties) ?> Parties on Record
            </span>
        </div>
    </div>
    <div class="card-body p-4">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Party Name &amp; Code</th>
                        <th>Type</th>
                        <th style="min-width: 180px;">Contact Info</th>
                        <th>GSTIN / Tax ID</th>
                        <th class="text-end" style="min-width: 160px;">Current Balance</th>
                        <th class="text-center" style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parties as $p): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('parties/statement/' . $p['party_id']) ?>" class="fw-bold text-decoration-none text-primary">
                                    <?= esc($p['party_name']) ?>
                                </a>
                                <?php if (!empty($p['party_code'])): ?>
                                    <span class="badge bg-light text-secondary border ms-1 font-monospace" style="font-size: 0.72rem;"><?= esc($p['party_code']) ?></span>
                                <?php endif; ?>
                                <div class="text-muted small mt-1" style="max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= esc($p['party_address'] ?: 'No address specified') ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($p['party_type'] === 'customer'): ?>
                                    <span class="badge-type badge-type-customer">
                                        <i class="bi bi-person-check-fill me-1"></i>Customer
                                    </span>
                                <?php elseif ($p['party_type'] === 'vendor'): ?>
                                    <span class="badge-type badge-type-vendor">
                                        <i class="bi bi-truck me-1"></i>Vendor
                                    </span>
                                <?php else: ?>
                                    <span class="badge-type badge-type-both">
                                        <i class="bi bi-arrow-left-right me-1"></i>Both
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark small fw-semibold"><i class="bi bi-telephone text-muted me-1"></i><?= esc($p['party_phone'] ?: '—') ?></div>
                                <div class="text-muted small mt-1"><i class="bi bi-envelope text-muted me-1"></i><?= esc($p['party_email'] ?: '—') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($p['party_gstin'])): ?>
                                    <code class="text-uppercase fw-semibold" style="font-size: 0.82rem;"><?= esc($p['party_gstin']) ?></code>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php
                                $bal = (float)$p['party_current_balance'];
                                if ($bal == 0):
                                ?>
                                    <span class="fw-bold text-muted font-monospace"><?= esc($currency_symbol) ?> 0.00</span>
                                <?php elseif ($bal > 0): ?>
                                    <span class="fw-bold text-success font-monospace">+<?= esc($currency_symbol) ?> <?= number_format($bal, 2) ?> <small class="text-muted fw-normal">Dr</small></span>
                                <?php else: ?>
                                    <span class="fw-bold text-danger font-monospace"><?= esc($currency_symbol) ?> <?= number_format(abs($bal), 2) ?> <small class="text-muted fw-normal">Cr</small></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('parties/statement/' . $p['party_id']) ?>" class="btn btn-outline-primary" title="Account Statement">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span>Statement</span>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-edit-modal"
                                        data-bs-toggle="modal" data-bs-target="#editPartyModal"
                                        data-action="<?= base_url('parties/update/' . $p['party_id']) ?>"
                                        data-item='<?= json_encode($p) ?>'
                                        title="Edit Party Details">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= base_url('parties/delete/' . $p['party_id']) ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete party \'<?= esc($p['party_name']) ?>\'?')"
                                        title="Delete Party">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Modal: Add Party -->
<div class="modal fade" id="addPartyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus text-primary me-2"></i>Add Customer / Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('parties/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" class="form-select" required>
                                <option value="customer" <?= $current_type === 'customer' ? 'selected' : '' ?>>Customer (Debtor)</option>
                                <option value="vendor" <?= $current_type === 'vendor' ? 'selected' : '' ?>>Vendor / Supplier (Creditor)</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">Legal / Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="party_name" class="form-control" placeholder="e.g. Acme Infotech Pvt Ltd" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Party Code / Alias</label>
                            <input type="text" name="party_code" class="form-control" placeholder="e.g. CUST-001">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="party_phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="party_email" class="form-control" placeholder="billing@company.com">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">GSTIN</label>
                            <input type="text" name="party_gstin" class="form-control text-uppercase" placeholder="27AAAAA0000A1Z5">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">PAN Number</label>
                            <input type="text" name="party_pan" class="form-control text-uppercase" placeholder="AAAAA0000A">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Registered Billing Address</label>
                            <textarea name="party_address" class="form-control" rows="2" placeholder="Street, Industrial Area, City, State, PIN"></textarea>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                            <input type="number" step="0.01" name="party_credit_limit" class="form-control" value="0.00">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Payment Terms (Days)</label>
                            <input type="number" name="party_credit_days" class="form-control" value="30">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Linked GL Control Account</label>
                            <select name="party_account_id" class="form-select">
                                <option value="">Auto-Assign (AR / AP)</option>
                                <?php foreach ($accounts as $a): ?>
                                    <option value="<?= $a['account_id'] ?>"><?= esc($a['account_code']) ?> - <?= esc($a['account_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Party</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Party -->
<div class="modal fade" id="editPartyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Party Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPartyForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" class="form-select" required>
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">Legal / Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="party_name" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Party Code / Alias</label>
                            <input type="text" name="party_code" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="party_phone" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="party_email" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">GSTIN</label>
                            <input type="text" name="party_gstin" class="form-control text-uppercase">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">PAN Number</label>
                            <input type="text" name="party_pan" class="form-control text-uppercase">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Registered Billing Address</label>
                            <textarea name="party_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                            <input type="number" step="0.01" name="party_credit_limit" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Payment Terms (Days)</label>
                            <input type="number" name="party_credit_days" class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="party_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Update Party</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>