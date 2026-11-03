<?= $this->extend('landing/layout') ?>

<?= $this->section('content') ?>
<!-- Subpage Hero -->
<section class="subpage-hero text-center text-lg-start">
    <div class="container">
        <?= seo_breadcrumbs_html($breadcrumbs ?? []) ?>
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <span class="hero-badge mb-3">
                    <i class="bi bi-tag-fill text-primary"></i> Transparent SaaS Pricing
                </span>
                <h1 class="display-5 fw-extrabold text-white mb-3" style="font-weight: 800;">
                    Predictable Software Plans for Any Growth Phase
                </h1>
                <p class="lead text-white-50 mb-0" style="max-width: 680px;">
                    Every plan includes full double-entry ledgers, GST invoicing, and an unrestricted 30-day free trial. Scale seamlessly as your operations expand.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <div class="cycle-toggle-wrapper bg-dark bg-opacity-50 border border-secondary border-opacity-50 p-1">
                    <button type="button" class="cycle-toggle-btn active" id="btnMonthly" onclick="togglePricing('monthly')">
                        Monthly
                    </button>
                    <button type="button" class="cycle-toggle-btn text-white-50" id="btnYearly" onclick="togglePricing('yearly')">
                        Annual <span class="badge bg-success ms-1" style="font-size: 0.65rem;">Save 17%</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Cards Grid -->
<section class="py-5">
    <div class="container py-4">
        
        <?php if (empty($subscriptionsEnabled)): ?>
            <div class="alert alert-warning border border-warning-subtle text-center p-4 rounded-3 mx-auto mb-5" style="max-width: 650px;">
                <i class="bi bi-pause-circle-fill text-warning fs-2 d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Online Subscriptions Are Currently Paused</h5>
                <p class="text-muted small mb-0">Self-serve online registrations are temporarily paused. For manual onboarding, enterprise licenses, or assisted setup, please contact administration.</p>
            </div>
        <?php endif; ?>

        <div class="row g-4 justify-content-center mb-5">
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
                                <?= ($plan['plan_max_users'] == 1) ? '1 User Seat' : (($plan['plan_max_users'] > 0) ? $plan['plan_max_users'] . ' User Seats' : 'Unlimited User Seats') ?>
                            </small>
                            <div class="d-flex justify-content-between pt-2 border-top mt-2" style="font-size: 0.72rem;">
                                <span class="text-muted">Invoices:</span>
                                <span class="fw-bold text-dark"><?= ($plan['plan_max_invoices'] > 0) ? number_format($plan['plan_max_invoices']) . '/mo' : 'Unlimited' ?></span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 0.72rem;">
                                <span class="text-muted">Vendor Bills:</span>
                                <span class="fw-bold text-dark"><?= ($plan['plan_max_bills'] > 0) ? number_format($plan['plan_max_bills']) . '/mo' : 'Unlimited' ?></span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 0.72rem;">
                                <span class="text-muted">Products:</span>
                                <span class="fw-bold text-dark"><?= ($plan['plan_max_products'] > 0) ? number_format($plan['plan_max_products']) : 'Unlimited' ?></span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 0.72rem;">
                                <span class="text-muted">Warehouses:</span>
                                <span class="fw-bold text-dark"><?= ($plan['plan_max_warehouses'] > 0) ? number_format($plan['plan_max_warehouses']) : 'Unlimited' ?></span>
                            </div>
                        </div>

                        <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            Included Capabilities:
                        </div>
                        <ul class="list-unstyled mb-4 small d-flex flex-column gap-2 flex-grow-1">
                            <?php foreach (array_slice($plan['feature_list'] ?? [], 0, 7) as $feat): ?>
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

        <!-- Comprehensive Plan Comparison Table -->
        <div class="text-center mb-4 pt-3">
            <h3 class="fw-bold text-dark">Plan Feature Matrix &amp; Quotas</h3>
            <p class="text-muted small">Compare all entitlements, system limits, and module access across tiers</p>
        </div>

        <div class="comparison-table-wrapper shadow-sm mb-5">
            <div class="table-responsive">
                <table class="table comparison-table">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Feature / Capability</th>
                            <th class="text-center" style="width: 17%;">Starter Business</th>
                            <th class="text-center text-primary" style="width: 17%;">Growth Enterprise</th>
                            <th class="text-center" style="width: 17%;">Multi-Company</th>
                            <th class="text-center" style="width: 17%;">Corporate Conglomerate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Category: Entities & Users -->
                        <tr class="category-row">
                            <td colspan="5">Entities &amp; User Seats</td>
                        </tr>
                        <tr>
                            <td>Legal Entities / Branches</td>
                            <td class="text-center">1 Entity</td>
                            <td class="text-center fw-bold text-primary">1 Entity</td>
                            <td class="text-center">Up to 5 Entities</td>
                            <td class="text-center fw-bold text-success">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Active User Logins</td>
                            <td class="text-center">1 User</td>
                            <td class="text-center fw-bold text-primary">3 Users</td>
                            <td class="text-center">5 Users</td>
                            <td class="text-center fw-bold text-success">10 Users</td>
                        </tr>
                        <tr>
                            <td>Multi-Company Workspace Switcher</td>
                            <td class="text-center text-muted"><i class="bi bi-dash"></i></td>
                            <td class="text-center text-muted"><i class="bi bi-dash"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        </tr>

                        <!-- Category: Monthly Transaction Quotas -->
                        <tr class="category-row">
                            <td colspan="5">Monthly Quotas &amp; Master Records</td>
                        </tr>
                        <tr>
                            <td>Sales Tax Invoices</td>
                            <td class="text-center">50 / month</td>
                            <td class="text-center fw-bold text-primary">500 / month</td>
                            <td class="text-center">2,000 / month</td>
                            <td class="text-center fw-bold text-success">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Vendor Bills &amp; Purchases</td>
                            <td class="text-center">20 / month</td>
                            <td class="text-center fw-bold text-primary">250 / month</td>
                            <td class="text-center">1,000 / month</td>
                            <td class="text-center fw-bold text-success">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Catalog Product Items (SKUs)</td>
                            <td class="text-center">30 Items</td>
                            <td class="text-center fw-bold text-primary">500 Items</td>
                            <td class="text-center">2,000 Items</td>
                            <td class="text-center fw-bold text-success">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Warehouse Locations</td>
                            <td class="text-center">1 Warehouse</td>
                            <td class="text-center fw-bold text-primary">3 Warehouses</td>
                            <td class="text-center">10 Warehouses</td>
                            <td class="text-center fw-bold text-success">Unlimited</td>
                        </tr>

                        <!-- Category: Accounting & Financial Reports -->
                        <tr class="category-row">
                            <td colspan="5">Accounting, Compliance &amp; Reports</td>
                        </tr>
                        <tr>
                            <td>Full Double-Entry General Ledger</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        </tr>
                        <tr>
                            <td>Automated GST Invoicing &amp; HSN Rates</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        </tr>
                        <tr>
                            <td>Advanced Financial Statements (P&amp;L, Balance Sheet)</td>
                            <td class="text-center text-muted">Basic Day Book</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> Included</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> Included</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> Included</td>
                        </tr>
                        <tr>
                            <td>Consolidated Group Reports</td>
                            <td class="text-center text-muted"><i class="bi bi-dash"></i></td>
                            <td class="text-center text-muted"><i class="bi bi-dash"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        </tr>
                        <tr>
                            <td>Data Export (Excel, CSV, PDF)</td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                            <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('schema') ?>
<?= seo_schema('breadcrumbs', ['items' => $breadcrumbs ?? []]) ?>
<?= seo_schema('pricing', ['plans' => $plans ?? []]) ?>
<?= seo_schema('webpage', [
    'title'       => 'Transparent Cloud Pricing Plans & Feature Quotas — LenDen Cloud',
    'description' => 'Transparent, scalable cloud pricing plans for single businesses, growing startups, and multi-company operations. 30-day unrestricted free trial with no credit card required.',
    'url'         => seo_canonical_url('pricing'),
]) ?>
<?= $this->endSection() ?>

