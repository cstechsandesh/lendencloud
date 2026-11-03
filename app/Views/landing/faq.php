<?= $this->extend('landing/layout') ?>

<?= $this->section('content') ?>
<!-- Subpage Hero -->
<section class="subpage-hero text-center text-lg-start">
    <div class="container">
        <?= seo_breadcrumbs_html($breadcrumbs ?? []) ?>
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <span class="hero-badge mb-3">
                    <i class="bi bi-question-circle-fill text-primary"></i> Help &amp; Knowledge Base
                </span>
                <h1 class="display-5 fw-extrabold text-white mb-3" style="font-weight: 800;">
                    Frequently Asked Questions
                </h1>
                <p class="lead text-white-50 mb-0" style="max-width: 680px;">
                    Find clear answers on multi-company management, billing quotas, GST tax calculation, double-entry ledgers, and trial onboarding.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg">
                    <i class="bi bi-rocket-takeoff-fill me-1"></i> Start 30-Day Free Trial
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Accordion Section -->
<section class="py-5">
    <div class="container py-4" style="max-width: 920px;">
        
        <!-- Category 1: Free Trial & Getting Started -->
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-rocket-takeoff"></i>
                </div>
                <h4 class="fw-bold text-dark mb-0">Free Trial &amp; Getting Started</h4>
            </div>

            <div class="accordion shadow-sm" id="faqTrialAccordion">
                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingTrial1">
                        <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrial1">
                            How does the 30-Day Free Trial work?
                        </button>
                    </h2>
                    <div id="collapseTrial1" class="accordion-collapse collapse show" data-bs-parent="#faqTrialAccordion">
                        <div class="accordion-body text-muted small">
                            When you register your business on LenDen, your workspace is provisioned instantly with a 30-day unrestricted trial of your chosen plan. No credit card is required. You get full access to all double-entry ledgers, invoicing, inventory, and reports from day one.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingTrial2">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrial2">
                            What happens after the 30-day trial ends?
                        </button>
                    </h2>
                    <div id="collapseTrial2" class="accordion-collapse collapse" data-bs-parent="#faqTrialAccordion">
                        <div class="accordion-body text-muted small">
                            Your data remains safely stored and intact. You can activate your plan subscription anytime via monthly or annual billing. If you need additional evaluation time, our administration team can extend your trial.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingTrial3">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTrial3">
                            Can I import my existing customer, vendor, and product lists?
                        </button>
                    </h2>
                    <div id="collapseTrial3" class="accordion-collapse collapse" data-bs-parent="#faqTrialAccordion">
                        <div class="accordion-body text-muted small">
                            Yes. You can import parties (Customers &amp; Vendors) and inventory items (SKUs, opening stock, purchase/sales rates) via structured Excel/CSV templates during setup.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category 2: Multi-Company Architecture -->
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-buildings"></i>
                </div>
                <h4 class="fw-bold text-dark mb-0">Multi-Company &amp; Subsidiaries</h4>
            </div>

            <div class="accordion shadow-sm" id="faqCompanyAccordion">
                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingComp1">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComp1">
                            Can I manage multiple companies under one subscription?
                        </button>
                    </h2>
                    <div id="collapseComp1" class="accordion-collapse collapse" data-bs-parent="#faqCompanyAccordion">
                        <div class="accordion-body text-muted small">
                            Yes! With our <strong>Multi-Company Portfolio</strong> and <strong>Corporate Conglomerate</strong> tiers, you can manage up to 5 or unlimited distinct legal business entities and branch subsidiaries with complete ledger isolation.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingComp2">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComp2">
                            How does workspace context switching work?
                        </button>
                    </h2>
                    <div id="collapseComp2" class="accordion-collapse collapse" data-bs-parent="#faqCompanyAccordion">
                        <div class="accordion-body text-muted small">
                            The top header displays an active company selector. Clicking any subsidiary switches the system context immediately. All menus, invoices, balances, and reports seamlessly filter to the selected company.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingComp3">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComp3">
                            Can different users have different permissions per company?
                        </button>
                    </h2>
                    <div id="collapseComp3" class="accordion-collapse collapse" data-bs-parent="#faqCompanyAccordion">
                        <div class="accordion-body text-muted small">
                            Yes. LenDen uses granular Role-Based Access Control (RBAC). A user can be assigned as an administrator in one company and an order entry clerk in another company without cross-polluting permissions.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category 3: GST Compliance & Accounting -->
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-journal-text"></i>
                </div>
                <h4 class="fw-bold text-dark mb-0">GST Compliance &amp; Accounting Standards</h4>
            </div>

            <div class="accordion shadow-sm" id="faqGstAccordion">
                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingGst1">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGst1">
                            Is LenDen compliant with Indian GST tax laws?
                        </button>
                    </h2>
                    <div id="collapseGst1" class="accordion-collapse collapse" data-bs-parent="#faqGstAccordion">
                        <div class="accordion-body text-muted small">
                            Yes. LenDen comes pre-configured with standard GST slabs (0%, 5%, 12%, 18%, 28%), supports IGST, CGST, and SGST breakdowns, tracks HSN/SAC codes, and prints formal GST tax invoices with tax breakdown tables.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-0 mb-3 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingGst2">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGst2">
                            How does double-entry bookkeeping work in LenDen Cloud?
                        </button>
                    </h2>
                    <div id="collapseGst2" class="accordion-collapse collapse" data-bs-parent="#faqGstAccordion">
                        <div class="accordion-body text-muted small">
                            Every financial action (sales invoices, vendor bills, receipt vouchers, payment vouchers) automatically records double-entry journal lines. Debits always equal credits, ensuring your Balance Sheet and Trial Balance remain in mathematical harmony.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Support Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-light border text-center">
            <h4 class="fw-bold text-dark mb-2">Still Have Questions or Need Custom Onboarding?</h4>
            <p class="text-muted small mx-auto mb-4" style="max-width: 540px;">
                Our implementation specialists are available to walk through your accounting structure, migration from legacy software, or multi-company deployment.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="mailto:support@lendencloud.com?subject=LenDen%20Cloud%20Inquiry" class="btn btn-primary fw-semibold px-4 py-2">
                    <i class="bi bi-envelope-fill me-1"></i> Contact Cloud Support
                </a>
                <a href="<?= base_url('subscribe') ?>" class="btn btn-outline-secondary fw-semibold px-4 py-2">
                    Start Self-Serve Trial
                </a>
            </div>
        </div>

    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('schema') ?>
<?= seo_schema('breadcrumbs', ['items' => $breadcrumbs ?? []]) ?>
<?= seo_schema('faq', ['items' => $faqItems ?? []]) ?>
<?= seo_schema('webpage', [
    'title'       => 'Frequently Asked Questions & Help Center — LenDen Cloud',
    'description' => 'Get immediate answers to common questions regarding LenDen Cloud double-entry accounting, 30-day free trial onboarding, multi-company architecture, GST tax invoicing, and data security.',
    'url'         => seo_canonical_url('faq'),
]) ?>
<?= $this->endSection() ?>

