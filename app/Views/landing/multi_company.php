<?= $this->extend('landing/layout') ?>

<?= $this->section('content') ?>
<!-- Subpage Hero -->
<section class="subpage-hero text-center text-lg-start">
    <div class="container">
        <?= seo_breadcrumbs_html($breadcrumbs ?? []) ?>
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <span class="hero-badge mb-3">
                    <i class="bi bi-buildings-fill text-primary"></i> Multi-Tenant Portfolio Architecture
                </span>
                <h1 class="display-5 fw-extrabold text-white mb-3" style="font-weight: 800;">
                    Centralized Governance for Holding Groups &amp; Subsidiaries
                </h1>
                <p class="lead text-white-50 mb-0" style="max-width: 680px;">
                    Operate multiple distinct commercial entities, branch locations, and subsidiary balance sheets from a single unified portal with complete ledger isolation.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg">
                    <i class="bi bi-rocket-takeoff-fill me-1"></i> Provision Multi-Company
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Core Pillars of Multi-Company Architecture -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                Why Multi-Company Matters
            </span>
            <h2 class="fw-bold text-dark mt-2">Built-In Segregation Without Multiple Logins</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Traditional accounting tools force you to maintain separate software licenses and repeatedly log in and out. LenDen Cloud unifies your businesses into one seamless interface.
            </p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-md-4">
                <div class="architecture-card h-100">
                    <div class="feature-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">1-Click Workspace Switcher</h5>
                    <p class="text-muted small mb-0">
                        The top navigation bar features an intelligent switcher. Change active legal company context instantly without losing your user session, bookmarks, or security permissions.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="architecture-card h-100">
                    <div class="feature-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Total Ledger Isolation</h5>
                    <p class="text-muted small mb-0">
                        Every company entity operates on its own segregated database tenancy. Chart of accounts, tax rates, vendor bills, customer lists, and financial records never mix.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="architecture-card h-100">
                    <div class="feature-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Granular Role Assignment</h5>
                    <p class="text-muted small mb-0">
                        Assign users distinct roles per company. A manager might be an Admin in Subsidiary A, an Accountant in Subsidiary B, and have zero access to Subsidiary C.
                    </p>
                </div>
            </div>
        </div>

        <!-- Visual Flow Comparison -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-light border mb-5">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-5">
                    <h3 class="fw-bold text-dark mb-3">One Central Login, Unlimited Scalability</h3>
                    <p class="text-muted mb-4">
                        Whether you are a corporate group with 5 subsidiaries or a CA firm managing 30 client businesses, LenDen scales without friction.
                    </p>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-4">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Unique GSTIN, PAN, and Bank Accounts per entity</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Independent Fiscal Year settings and Voucher numbering</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Consolidated group performance metrics in Super Admin</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Inter-company journal adjustments &amp; contra transfers</li>
                    </ul>
                    <a href="<?= base_url('subscribe') ?>" class="btn btn-primary fw-semibold">
                        Get Started with Multi-Company &rarr;
                    </a>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow bg-white p-4 rounded-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grid-fill text-primary"></i>
                                <span class="fw-bold text-dark">Enterprise Group Hierarchy</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Consolidated Central RBAC</span>
                        </div>

                        <!-- Holding Group Box -->
                        <div class="p-3 bg-primary-subtle text-primary rounded-3 text-center mb-3 fw-bold">
                            <i class="bi bi-shield-check me-1"></i> Apex Holding Group / Super Administrator
                        </div>

                        <!-- Subsidiary Grid -->
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-dark small">Entity 1: LenDen Tech Ltd</div>
                                    <small class="text-muted d-block">Software &amp; Subscriptions</small>
                                    <span class="badge bg-success mt-1" style="font-size: 0.65rem;">Active Context</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-dark small">Entity 2: Apex Warehousing</div>
                                    <small class="text-muted d-block">Logistics &amp; Fulfillment</small>
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;">Subsidiary</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-dark small">Entity 3: Coastal Traders</div>
                                    <small class="text-muted d-block">Wholesale FMCG &amp; Retail</small>
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;">Subsidiary</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="fw-bold text-dark small">Entity 4: Metro Financials</div>
                                    <small class="text-muted d-block">Advisory &amp; CA Practice</small>
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;">Subsidiary</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Target Audiences -->
        <div class="row g-4 text-center">
            <div class="col-12 col-md-4">
                <div class="p-4 border rounded-4 h-100">
                    <i class="bi bi-briefcase-fill text-primary fs-2 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">CA &amp; Accounting Firms</h5>
                    <p class="text-muted small mb-0">Manage multiple client corporate books with segregated privacy, independent journals, and effortless auditing.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-4 border rounded-4 h-100">
                    <i class="bi bi-diagram-3-fill text-success fs-2 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Franchise &amp; Branch Networks</h5>
                    <p class="text-muted small mb-0">Delegate billing and stock management to branch managers while keeping central ownership of books.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-4 border rounded-4 h-100">
                    <i class="bi bi-buildings-fill text-purple fs-2 d-block mb-3" style="color: #7c3aed;"></i>
                    <h5 class="fw-bold text-dark">Conglomerates &amp; Holdings</h5>
                    <p class="text-muted small mb-0">Consolidate different business lines under one SaaS subscription tier without paying per-seat surcharges.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('schema') ?>
<?= seo_schema('breadcrumbs', ['items' => $breadcrumbs ?? []]) ?>
<?= seo_schema('webpage', [
    'title'       => 'Multi-Company Architecture & Subsidiary Governance — LenDen Cloud',
    'description' => 'Operate multiple distinct legal entities, holding groups, and subsidiary branch locations from a single unified cloud portal with 1-click workspace switching and ledger segregation.',
    'url'         => seo_canonical_url('multi-company'),
]) ?>
<?= $this->endSection() ?>

