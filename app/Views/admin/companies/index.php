<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-buildings text-primary"></i> Multi-Company Portfolio Directory
        </h1>
        <p class="page-subtitle text-muted mb-0">Centralized oversight of all legal business entities, branches, and independent accounting ledgers.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold rounded-pill">
            <i class="bi bi-shield-check me-1"></i> Multi-Tenant Workspace
        </span>
        <?php if ($is_super_admin || in_array('companies.create', $user_permissions ?? [])): ?>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" onclick="openAddCompanyModal()">
                <i class="bi bi-plus-circle"></i> Add New Company
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Platform Stats Grid -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Companies</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($stats['total_companies'] ?? 0) ?></h3>
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i><?= number_format($stats['active_companies'] ?? 0) ?> Active Entities</small>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-buildings fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Ledger Accounts</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($stats['total_accounts'] ?? 0) ?></h3>
                    <small class="text-muted">Across all portfolios</small>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-journal-bookmark fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Products Catalog</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($stats['total_products'] ?? 0) ?></h3>
                    <small class="text-muted">SKU items configured</small>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Billing Volume</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= esc($currency_symbol) ?><?= number_format($stats['total_revenue'] ?? 0, 2) ?></h3>
                    <small class="text-muted"><?= number_format($stats['total_invoices'] ?? 0) ?> invoices recorded</small>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="<?= base_url('companies') ?>" method="get" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by name, GSTIN, code, city, email..." value="<?= esc($search ?? '') ?>">
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <select name="status" class="form-select">
                    <option value="all" <?= ($status ?? 'all') === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active Only</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold"><i class="bi bi-filter me-1"></i>Filter</button>
                <?php if (!empty($search) || ($status ?? 'all') !== 'all'): ?>
                    <a href="<?= base_url('companies') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-x-circle me-1"></i>Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Companies List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Configured Business Entities</h6>
        <span class="text-muted small">Showing <?= count($companies) ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Company Legal Entity</th>
                    <th>GSTIN / PAN</th>
                    <th>Location & Contact</th>
                    <th>Entity Metrics</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($companies)): ?>
                    <?php foreach ($companies as $comp): ?>
                        <?php $isCurrent = ((int)($currentCompany['company_id'] ?? 0) === (int)$comp['company_id']); ?>
                        <tr class="<?= $isCurrent ? 'table-primary-subtle' : '' ?>">
                            <td class="text-muted fw-bold"><?= $comp['company_id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded bg-light border p-2 text-center" style="width: 42px; height: 42px;">
                                        <i class="bi bi-building fs-5 <?= $isCurrent ? 'text-primary' : 'text-secondary' ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            <?= esc($comp['company_name']) ?>
                                            <?php if ($isCurrent): ?>
                                                <span class="badge bg-success ms-1 small" style="font-size: 0.7rem;">Active Context</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border"><?= esc($comp['company_code'] ?: 'ID: ' . $comp['company_id']) ?></span>
                                            <?php if (!empty($comp['company_bank_name'])): ?>
                                                &bull; <?= esc($comp['company_bank_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($comp['company_gstin'])): ?>
                                    <div class="fw-semibold text-dark small"><i class="bi bi-file-earmark-check me-1 text-primary"></i><?= esc($comp['company_gstin']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">No GSTIN</span>
                                <?php endif; ?>
                                <?php if (!empty($comp['company_pan'])): ?>
                                    <div class="text-muted small">PAN: <?= esc($comp['company_pan']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><?= esc($comp['company_city'] ?? 'Mumbai') ?><?= !empty($comp['company_state']) ? ', ' . esc($comp['company_state']) : '' ?></div>
                                <div class="text-muted small">
                                    <?php if (!empty($comp['company_email'])): ?>
                                        <span><i class="bi bi-envelope me-1"></i><?= esc($comp['company_email']) ?></span><br>
                                    <?php endif; ?>
                                    <?php if (!empty($comp['company_phone'])): ?>
                                        <span><i class="bi bi-telephone me-1"></i><?= esc($comp['company_phone']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark border" title="Ledger Accounts">
                                        <i class="bi bi-journal me-1 text-primary"></i><?= (int)($comp['total_accounts'] ?? 0) ?> Accs
                                    </span>
                                    <span class="badge bg-light text-dark border" title="Catalog Products">
                                        <i class="bi bi-box me-1 text-info"></i><?= (int)($comp['total_products'] ?? 0) ?> Items
                                    </span>
                                    <span class="badge bg-light text-dark border" title="Tax Invoices">
                                        <i class="bi bi-receipt me-1 text-warning"></i><?= (int)($comp['total_invoices'] ?? 0) ?> Invoices
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if ($comp['company_status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if ($isCurrent): ?>
                                        <button class="btn btn-outline-success btn-sm disabled fw-semibold" title="Currently viewing this company">
                                            <i class="bi bi-check2-circle me-1"></i>Current
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= base_url('companies/switch/' . $comp['company_id']) ?>" class="btn btn-primary btn-sm fw-semibold" title="Switch active workspace context to this company">
                                            <i class="bi bi-arrow-left-right me-1"></i>Switch
                                        </a>
                                    <?php endif; ?>

                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="viewCompanyDetails(<?= $comp['company_id'] ?>)" title="View Company Profile">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <?php if ($is_super_admin || in_array('companies.edit', $user_permissions ?? [])): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="editCompany(<?= $comp['company_id'] ?>)" title="Edit Company Settings">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (($is_super_admin || in_array('companies.delete', $user_permissions ?? [])) && !$isCurrent && count($companies) > 1): ?>
                                        <a href="<?= base_url('companies/delete/' . $comp['company_id']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete company \'<?= esc($comp['company_name']) ?>\' and all its associated accounting records? This action cannot be undone.')" title="Delete Company">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-buildings fs-1 d-block mb-2 text-secondary"></i>
                            No companies found matching the criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<!-- ================= ADD COMPANY MODAL ================= -->
<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('companies/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="addCompanyModalLabel">
                        <i class="bi bi-building-add text-primary me-2"></i>Register New Company Entity
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Multi-Company Auto-Provisioning:</strong> Starter Chart of Accounts (Cash, Bank, Sales, Purchases, Debtors, Creditors), default Warehouse, and standard GST Tax Rates will be auto-generated for this new entity.
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Company Identity</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Legal Company / Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Acme Tech Solutions Pvt Ltd" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Short Code</label>
                            <input type="text" name="company_code" class="form-control" placeholder="e.g. ACME">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">GSTIN</label>
                            <input type="text" name="company_gstin" class="form-control" placeholder="e.g. 27AABCL1234M1Z8">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PAN</label>
                            <input type="text" name="company_pan" class="form-control" placeholder="e.g. AABCL1234M">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Registration / CIN</label>
                            <input type="text" name="company_registration_number" class="form-control" placeholder="e.g. U72200MH2026PTC001">
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact & Location</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Official Email</label>
                            <input type="email" name="company_email" class="form-control" placeholder="e.g. accounts@acme.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Phone</label>
                            <input type="text" name="company_phone" class="form-control" placeholder="e.g. +91 98200 12345">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Registered Office Address</label>
                            <textarea name="company_address" class="form-control" rows="2" placeholder="Full street address, suite or plot number"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="company_city" class="form-control" value="Mumbai">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State</label>
                            <input type="text" name="company_state" class="form-control" value="Maharashtra">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Postal Code</label>
                            <input type="text" name="company_postal_code" class="form-control" placeholder="e.g. 400069">
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Banking & Financial Defaults</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank Name</label>
                            <input type="text" name="company_bank_name" class="form-control" placeholder="e.g. HDFC Bank Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Account Number</label>
                            <input type="text" name="company_account_no" class="form-control" placeholder="Bank Current Account No">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">IFSC Code</label>
                            <input type="text" name="company_ifsc_code" class="form-control" placeholder="e.g. HDFC0001234">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">UPI ID</label>
                            <input type="text" name="company_upi_id" class="form-control" placeholder="e.g. company@upi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Currency Symbol</label>
                            <input type="text" name="company_currency_symbol" class="form-control" value="₹">
                        </div>
                    </div>

                    <div class="form-check form-switch p-3 bg-light rounded mt-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="auto_switch" id="autoSwitchCheck" value="1" checked>
                        <label class="form-check-label fw-semibold text-dark" for="autoSwitchCheck">
                            Automatically switch active workspace to this company after creation
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i>Create & Provision Company</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= EDIT COMPANY MODAL ================= -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editCompanyForm" method="post">
                <?= csrf_field() ?>
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="editCompanyModalLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Company Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Company Identity</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Legal Company / Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="edit_company_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Short Code</label>
                            <input type="text" name="company_code" id="edit_company_code" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">GSTIN</label>
                            <input type="text" name="company_gstin" id="edit_company_gstin" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PAN</label>
                            <input type="text" name="company_pan" id="edit_company_pan" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="company_status" id="edit_company_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact & Location</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Official Email</label>
                            <input type="email" name="company_email" id="edit_company_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Phone</label>
                            <input type="text" name="company_phone" id="edit_company_phone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Registered Office Address</label>
                            <textarea name="company_address" id="edit_company_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="company_city" id="edit_company_city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State</label>
                            <input type="text" name="company_state" id="edit_company_state" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Postal Code</label>
                            <input type="text" name="company_postal_code" id="edit_company_postal_code" class="form-control">
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Banking & Financial Defaults</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank Name</label>
                            <input type="text" name="company_bank_name" id="edit_company_bank_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Account Number</label>
                            <input type="text" name="company_account_no" id="edit_company_account_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">IFSC Code</label>
                            <input type="text" name="company_ifsc_code" id="edit_company_ifsc_code" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">UPI ID</label>
                            <input type="text" name="company_upi_id" id="edit_company_upi_id" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Currency Symbol</label>
                            <input type="text" name="company_currency_symbol" id="edit_company_currency_symbol" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="bi bi-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= VIEW COMPANY DETAILS MODAL ================= -->
<div class="modal fade" id="viewCompanyModal" tabindex="-1" aria-labelledby="viewCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="viewCompanyModalLabel">
                    <i class="bi bi-building text-primary me-2"></i>Company Entity Overview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary-subtle text-primary mx-auto p-3 d-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px;">
                        <i class="bi bi-buildings fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1" id="view_company_name">-</h5>
                    <span class="badge bg-light text-dark border me-1" id="view_company_code">-</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" id="view_company_status">Active</span>
                </div>

                <div class="card bg-light border-0 mb-3">
                    <div class="card-body p-3">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="fw-bold fs-5 text-dark" id="view_stat_accounts">0</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Accounts</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-5 text-dark" id="view_stat_products">0</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Products</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-5 text-dark" id="view_stat_invoices">0</div>
                                <small class="text-muted" style="font-size: 0.72rem;">Invoices</small>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">GSTIN:</span>
                        <strong class="text-dark" id="view_company_gstin">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">PAN:</span>
                        <strong class="text-dark" id="view_company_pan">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Location:</span>
                        <strong class="text-dark" id="view_company_city_state">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Official Email:</span>
                        <strong class="text-dark" id="view_company_email">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Phone:</span>
                        <strong class="text-dark" id="view_company_phone">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Bank Name:</span>
                        <strong class="text-dark" id="view_company_bank">-</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">IFSC / UPI:</span>
                        <strong class="text-dark" id="view_company_ifsc_upi">-</strong>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <a id="modalSwitchBtn" href="#" class="btn btn-primary btn-sm px-3 fw-semibold">
                    <i class="bi bi-arrow-left-right me-1"></i>Switch to this Company
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/companies-admin.js') ?>"></script>
<?= $this->endSection() ?>

