<?= $this->extend('landing/layout') ?>

<?= $this->section('content') ?>
<!-- Hero Section -->
<section class="hero-section text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-12 col-lg-7">
                <div class="hero-badge mb-3">
                    <i class="bi bi-shield-check text-primary"></i> Multi-Company SaaS ERP Platform
                </div>
                <h1 class="display-4 fw-extrabold text-white mb-3" style="line-height: 1.15; font-weight: 800;">
                    Complete Double-Entry Accounting &amp; Business Invoicing Suite
                </h1>
                <p class="lead text-white-50 mb-4" style="max-width: 620px;">
                    Built for growing businesses, multi-branch conglomerates, and accounting teams. Seamlessly manage sales orders, GST invoices, vendor bills, multi-warehouse inventory, and instant financial balance sheets.
                </p>

                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-5">
                    <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                        <i class="bi bi-rocket-takeoff-fill"></i> Start 30-Day Free Trial
                    </a>
                    <a href="<?= base_url('pricing') ?>" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold">
                        View Pricing Plans
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-link text-white-50 text-decoration-none d-inline-flex align-items-center gap-1 ps-2">
                        Demo Login &rarr;
                    </a>
                </div>

                <!-- Live Metrics -->
                <div class="row g-3 text-start">
                    <div class="col-6 col-sm-3">
                        <div class="stat-badge">
                            <h4 class="fw-bold text-white mb-0">100%</h4>
                            <small class="text-white-50">Audit Ready</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-badge">
                            <h4 class="fw-bold text-white mb-0">Double</h4>
                            <small class="text-white-50">Entry Ledgers</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-badge">
                            <h4 class="fw-bold text-white mb-0">Multi</h4>
                            <small class="text-white-50">Company Switch</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="stat-badge">
                            <h4 class="fw-bold text-white mb-0">GST</h4>
                            <small class="text-white-50">Tax Compliant</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5 text-center">
                <div class="card border-0 shadow-lg text-start overflow-hidden" style="border-radius: 20px; background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1) !important;">
                    <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-danger d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="small text-white-50 ms-2">Dashboard Executive Overview</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active Ledger</span>
                    </div>
                    <div class="p-4 text-white">
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="text-white-50 small">Current Active Company</span>
                            <strong class="text-white">LenDen Cloud</strong>
                        </div>
                        <div class="bg-black bg-opacity-25 p-3 rounded-3 mb-3 border border-secondary border-opacity-25">
                            <div class="small text-white-50">Real-Time Net Profit (FY 2026-27)</div>
                            <div class="fs-4 fw-bold text-success mt-1">₹14,82,500.00</div>
                            <small class="text-white-50">Trial Balance reconciled automatically</small>
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small text-white-50">
                            <li><i class="bi bi-check2 text-primary me-2"></i>Automated GST Invoicing &amp; HSN Codes</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Multi-Warehouse Stock Adjustments</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>1-Click Consolidated Subsidiaries Switching</li>
                            <li><i class="bi bi-check2 text-primary me-2"></i>Role-Based Granular Permissions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modules Overview Teaser -->
<section class="py-5">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                    Modular SaaS ERP
                </span>
                <h2 class="fw-bold text-dark mt-2 mb-0">Engineered for Operational Excellence</h2>
            </div>
            <a href="<?= base_url('features') ?>" class="btn btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1">
                Explore All Modules &amp; Capabilities &rarr;
            </a>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Sales &amp; GST Invoicing</h5>
                    <p class="text-muted small mb-3">Create sales orders, convert to tax invoices, apply GST rates (5%, 12%, 18%, 28%), and generate print-ready PDF invoices.</p>
                    <a href="<?= base_url('features#sales') ?>" class="small fw-semibold text-primary text-decoration-none mt-auto">Learn more &rarr;</a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-journal-bookmark"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Double-Entry Accounting</h5>
                    <p class="text-muted small mb-3">Audit-proof general ledgers, customizable Chart of Accounts, contra entries, journal vouchers, and automated debit/credit balancing.</p>
                    <a href="<?= base_url('features#accounting') ?>" class="small fw-semibold text-success text-decoration-none mt-auto">Learn more &rarr;</a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Multi-Warehouse Inventory</h5>
                    <p class="text-muted small mb-3">Track product SKUs across multiple storage locations, monitor minimum stock levels, record adjustments, and value stock automatically.</p>
                    <a href="<?= base_url('features#inventory') ?>" class="small fw-semibold text-info text-decoration-none mt-auto">Learn more &rarr;</a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Purchases &amp; Vendor Bills</h5>
                    <p class="text-muted small mb-3">Manage supplier directories, create purchase orders, match vendor bills with stock receipts, and disburse payment vouchers.</p>
                    <a href="<?= base_url('features#purchases') ?>" class="small fw-semibold text-warning text-decoration-none mt-auto">Learn more &rarr;</a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper bg-danger-subtle text-danger">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Real-Time Financial Statements</h5>
                    <p class="text-muted small mb-3">Instant Balance Sheet, Profit &amp; Loss statements, Trial Balance, Customer Aging, and Day Book statements ready for audit.</p>
                    <a href="<?= base_url('features#reports') ?>" class="small fw-semibold text-danger text-decoration-none mt-auto">Learn more &rarr;</a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon-wrapper" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed;">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Multi-Company Architecture</h5>
                    <p class="text-muted small mb-3">Manage independent legal entities and branch subsidiaries from a single login with instantaneous context switching.</p>
                    <a href="<?= base_url('multi-company') ?>" class="small fw-semibold text-purple text-decoration-none mt-auto" style="color: #7c3aed;">Learn more &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Multi-Company Spotlight Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-12 col-lg-6">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                    Multi-Company Cockpit
                </span>
                <h2 class="fw-bold text-dark mt-2 mb-3">Scale Holding Groups &amp; Subsidiary Networks</h2>
                <p class="text-muted mb-4">
                    LenDen is engineered natively for multi-tenant and multi-company portfolios. Whether you operate multiple branch offices, holding groups, or offer CA consultancy services to distinct clients, you can effortlessly manage them from one centralized cockpit.
                </p>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Instant Header Workspace Switcher</strong>
                            <small class="text-muted">Switch legal entities and accounting books in one click without re-logging in.</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Independent Ledgers &amp; Complete Isolation</strong>
                            <small class="text-muted">Each company maintains segregated chart of accounts, tax rates, inventory, and users.</small>
                        </div>
                    </div>
                </div>

                <a href="<?= base_url('multi-company') ?>" class="btn btn-primary fw-semibold px-4 py-2">
                    Learn How Multi-Company Works &rarr;
                </a>
            </div>

            <div class="col-12 col-lg-6 text-center">
                <div class="card border-0 shadow-lg p-4 text-start" style="border-radius: 20px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-buildings-fill text-primary fs-4"></i>
                            <div>
                                <strong class="d-block text-dark">Active Holding Portfolio</strong>
                                <small class="text-muted">Connected Subsidiary Entities</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Consolidated</span>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark d-block">LenDen Business Suite Ltd.</strong>
                                <small class="text-muted">Mumbai Headquarters &bull; GSTIN: 27AAAAA0000A1Z5</small>
                            </div>
                            <span class="badge bg-success">Active Context</span>
                        </div>
                        <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark d-block">Apex Logistics &amp; Warehousing</strong>
                                <small class="text-muted">Pune Branch &bull; 4,200 Stock SKUs</small>
                            </div>
                            <span class="badge bg-primary">Subsidiary</span>
                        </div>
                        <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark d-block">Coastal Traders &amp; Distribution</strong>
                                <small class="text-muted">Goa Branch &bull; Wholesale Sales</small>
                            </div>
                            <span class="badge bg-primary">Subsidiary</span>
                        </div>
                    </div>

                    <a href="<?= base_url('subscribe') ?>" class="btn btn-outline-primary w-100 fw-semibold">
                        Provision Your Multi-Company Workspace Now &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Teaser Section -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                Pricing Preview
            </span>
            <h2 class="fw-bold text-dark mt-2">Transparent Plans for Every Business Scale</h2>
            <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
                Choose the right software tier for your operations. All tiers include a 30-day unrestricted free trial.
            </p>

            <!-- Monthly / Annual Toggle -->
            <div class="cycle-toggle-wrapper">
                <button type="button" class="cycle-toggle-btn active" id="btnMonthly" onclick="togglePricing('monthly')">
                    Monthly Billing
                </button>
                <button type="button" class="cycle-toggle-btn" id="btnYearly" onclick="togglePricing('yearly')">
                    Annual Billing <span class="badge bg-success ms-1" style="font-size: 0.65rem;">Save 17%</span>
                </button>
            </div>
        </div>

        <!-- Pricing Cards Grid -->
        <div class="row g-4 justify-content-center mb-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="pricing-card <?= ($plan['plan_is_popular']) ? 'popular' : '' ?>">
                        <?php if ($plan['plan_is_popular']): ?>
                            <div class="popular-badge">
                                Most Popular
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <h4 class="fw-bold text-dark mb-1"><?= esc($plan['plan_name']) ?></h4>
                            <p class="text-muted small mb-0" style="min-height: 42px; font-size: 0.8rem;"><?= esc($plan['plan_tagline'] ?? '') ?></p>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                                <span class="fs-2 fw-bold text-primary rate-num" data-mrate="<?= $plan['plan_price_per_month'] ?>" data-yrate="<?= $plan['plan_price_per_year'] ?>">
                                    ₹<?= number_format((float)$plan['plan_price_per_month'], 0) ?>
                                </span>
                                <span class="text-muted small rate-cycle">/month</span>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                <?= ($plan['plan_max_companies'] == 1) ? '1 Legal Business Entity' : (($plan['plan_max_companies'] > 0) ? "Up to {$plan['plan_max_companies']} Companies" : 'Unlimited Companies') ?>
                            </small>
                            <small class="text-success d-block fw-semibold" style="font-size: 0.75rem;">
                                <?= ($plan['plan_max_users'] == 1) ? '1 User Seat' : (($plan['plan_max_users'] > 0) ? $plan['plan_max_users'] . ' User Seats Allowed' : 'Unlimited User Seats') ?>
                            </small>
                        </div>

                        <ul class="list-unstyled mb-4 small d-flex flex-column gap-2 flex-grow-1">
                            <?php foreach (array_slice($plan['feature_list'] ?? [], 0, 5) as $feat): ?>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0 mt-0"></i>
                                    <span><?= esc($feat) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (!empty($subscriptionsEnabled)): ?>
                            <a href="<?= base_url('subscribe?plan=' . esc($plan['plan_code']) . '&cycle=monthly') ?>" class="btn <?= ($plan['plan_is_popular']) ? 'btn-primary' : 'btn-outline-primary' ?> w-100 fw-bold py-2 shadow-sm plan-subscribe-link" data-code="<?= esc($plan['plan_code']) ?>">
                                Start 30-Day Free Trial
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary w-100 fw-bold py-2" disabled>
                                Paused
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center">
            <a href="<?= base_url('pricing') ?>" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                View Full Tier Comparison &amp; Detailed Quotas &rarr;
            </a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('schema') ?>
<?= seo_schema('software_application', [
    'description' => 'LenDen Cloud is a modern cloud accounting and multi-company ERP suite featuring double-entry ledgers, automated GST invoicing, multi-warehouse inventory, and financial statements.',
]) ?>
<?= $this->endSection() ?>
