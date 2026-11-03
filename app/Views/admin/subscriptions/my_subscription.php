<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-patch-check-fill text-primary"></i> Company Subscription &amp; Plan
        </h1>
        <p class="page-subtitle text-muted mb-0">Overview of your active software subscription, seat quotas, billing cycles, and SaaS tier upgrades.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= base_url('settings') ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 px-3 py-2 fw-semibold">
            <i class="bi bi-gear-fill"></i> Organization Settings
        </a>
    </div>
</div>

<?php if (empty($subscriptionsEnabled)): ?>
    <div class="alert alert-warning border border-warning-subtle d-flex align-items-center gap-3 mb-4 p-3 rounded-3 shadow-sm" role="alert">
        <i class="bi bi-pause-circle-fill text-warning fs-3"></i>
        <div>
            <strong class="text-dark">Online Renewals &amp; Plan Changes Paused</strong>
            <div class="small text-muted">Self-service online renewals are temporarily paused by the platform administrator. Your existing access remains active. For manual renewals or tier adjustments, please contact platform administration.</div>
        </div>
    </div>
<?php endif; ?>

<?php
$currentPlanId   = (int)($currentSub['subscription_plan_id'] ?? 1);
$currentCycle    = $currentSub['subscription_billing_cycle'] ?? 'monthly';
$status          = $currentSub['subscription_status'] ?? 'trial';
$isExpired       = ($status === 'expired' || ($daysRemaining !== null && $daysRemaining <= 0));
$isExpiringSoon  = (!$isExpired && $daysRemaining !== null && $daysRemaining <= 15);
$maxUsers        = (int)($currentSub['plan_max_users'] ?? 5);
$maxCompanies    = (int)($currentSub['plan_max_companies'] ?? 1);
$userPercent     = ($maxUsers > 0) ? min(100, round(($totalUsers / $maxUsers) * 100)) : 20;
?>

<!-- ===================================================================
     HERO CARD: ACTIVE PLAN DETAILS
     =================================================================== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 text-primary fw-bold small text-uppercase mb-1" style="letter-spacing: 0.5px;">
                    <i class="bi bi-buildings-fill"></i> <?= esc($currentCompany['company_name'] ?? 'Active Entity') ?>
                    <span class="text-muted">&bull;</span>
                    <span class="text-muted fw-normal">Code: <?= esc($currentCompany['company_code'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h2 class="fw-bold mb-0 text-dark"><?= esc($currentSub['plan_name'] ?? 'Starter Business') ?></h2>
                    
                    <?php if ($status === 'active'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i> Active Subscription
                        </span>
                    <?php elseif ($status === 'trial'): ?>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-rocket-takeoff-fill me-1"></i> 30-Day Free Trial
                        </span>
                    <?php elseif ($isExpired): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Subscription Expired
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-semibold">
                            <?= esc(ucfirst($status)) ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($currentSub['plan_badge'])): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                            <?= esc($currentSub['plan_badge']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mt-2 mb-0" style="max-width: 600px;">
                    <?= esc($currentSub['plan_tagline'] ?? 'Standard business billing and double-entry accounting workspace.') ?>
                </p>
            </div>

            <div class="text-end">
                <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.75rem;">Current Billing Rate</div>
                <h3 class="fw-bold text-primary mb-0 mt-1">
                    <?= esc($currency_symbol ?? '₹') ?><?= number_format((float)($currentSub['subscription_amount'] ?? 0), 2) ?>
                    <small class="text-muted fs-6 fw-normal">/ <?= esc($currentCycle) ?></small>
                </h3>
                <small class="text-muted">Mode: <?= esc(ucwords(str_replace('_', ' ', $currentSub['subscription_payment_method'] ?? 'complimentary_trial'))) ?></small>
            </div>
        </div>

        <div class="row g-3 pt-3 border-top">
            <div class="col-12 col-md-4">
                <div class="bg-white rounded-3 p-3 border h-100">
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">Validity &amp; Expiration</div>
                    <div class="fs-5 fw-bold text-dark mt-1">
                        <?= !empty($currentSub['subscription_end_date']) ? date('d M Y', strtotime($currentSub['subscription_end_date'])) : 'No Expiry' ?>
                    </div>
                    <small class="text-muted d-block">
                        Active Since: <?= !empty($currentSub['subscription_start_date']) ? date('d M Y', strtotime($currentSub['subscription_start_date'])) : 'N/A' ?>
                    </small>
                    <div class="mt-2">
                        <?php if ($isExpired): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                <i class="bi bi-clock-history me-1"></i> Expired <?= abs((int)$daysRemaining) ?> days ago
                            </span>
                        <?php elseif ($isExpiringSoon): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                <i class="bi bi-alarm me-1"></i> Expires in <?= (int)$daysRemaining ?> days
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                <i class="bi bi-shield-check me-1"></i> <?= (int)$daysRemaining ?> days remaining
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="bg-white rounded-3 p-3 border h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">User Seats Utilization</span>
                        <span class="badge bg-light text-dark border"><?= $totalUsers ?> / <?= ($maxUsers > 0) ? $maxUsers : '∞' ?> Seats</span>
                    </div>
                    <div class="fs-5 fw-bold text-dark mt-1">
                        <?= $totalUsers ?> <span class="text-muted fs-6 fw-normal">seats active</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar <?= ($userPercent >= 90) ? 'bg-danger' : 'bg-primary' ?>" role="progressbar" style="width: <?= $userPercent ?>%;" aria-valuenow="<?= $userPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <?= ($maxUsers > 0) ? ($maxUsers - $totalUsers) . ' seat(s) remaining in this tier' : 'Unlimited seats allowed' ?>
                    </small>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="bg-white rounded-3 p-3 border h-100">
                    <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">Multi-Entity Quota</div>
                    <div class="fs-5 fw-bold text-dark mt-1">
                        <?= ($maxCompanies == 1) ? '1 Business Entity' : (($maxCompanies > 0) ? "Up to {$maxCompanies} Entities" : 'Unlimited Entities') ?>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Independent ledgers &amp; subsidiary branches
                    </small>
                    <div class="mt-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                            <i class="bi bi-building-check me-1"></i> Multi-Company Ready
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================
     OPERATIONAL RESOURCE QUOTAS & LIVE USAGE
     =================================================================== -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Plan Quotas &amp; Live Usage
            </h5>
            <small class="text-muted">Real-time resource consumption against your subscribed tier limits</small>
        </div>
        <span class="badge bg-light text-dark border">
            Resets Monthly on 1st
        </span>
    </div>

    <div class="row g-3">
        <!-- Invoices -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Monthly Sales Invoices</span>
                        <div class="p-2 rounded-2 bg-primary-subtle text-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-4 fw-bold text-dark"><?= number_format($usageSummary['invoices']['current'] ?? 0) ?></span>
                        <span class="text-muted small">/ <?= !empty($usageSummary['invoices']['unlimited']) ? '∞ Unlimited' : number_format($usageSummary['invoices']['limit'] ?? 50) ?></span>
                    </div>
                    <?php if (empty($usageSummary['invoices']['unlimited'])): ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar <?= (($usageSummary['invoices']['percentage'] ?? 0) >= 90) ? 'bg-danger' : ((($usageSummary['invoices']['percentage'] ?? 0) >= 75) ? 'bg-warning' : 'bg-primary') ?>"
                                 role="progressbar" style="width: <?= $usageSummary['invoices']['percentage'] ?? 0 ?>%;"></div>
                        </div>
                    <?php else: ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <?= esc($usageSummary['invoices']['description'] ?? '') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Vendor Bills -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Monthly Vendor Bills</span>
                        <div class="p-2 rounded-2 bg-info-subtle text-info">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-4 fw-bold text-dark"><?= number_format($usageSummary['bills']['current'] ?? 0) ?></span>
                        <span class="text-muted small">/ <?= !empty($usageSummary['bills']['unlimited']) ? '∞ Unlimited' : number_format($usageSummary['bills']['limit'] ?? 20) ?></span>
                    </div>
                    <?php if (empty($usageSummary['bills']['unlimited'])): ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar <?= (($usageSummary['bills']['percentage'] ?? 0) >= 90) ? 'bg-danger' : ((($usageSummary['bills']['percentage'] ?? 0) >= 75) ? 'bg-warning' : 'bg-info') ?>"
                                 role="progressbar" style="width: <?= $usageSummary['bills']['percentage'] ?? 0 ?>%;"></div>
                        </div>
                    <?php else: ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <?= esc($usageSummary['bills']['description'] ?? '') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Catalog Products -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Catalog Items (SKUs)</span>
                        <div class="p-2 rounded-2 bg-warning-subtle text-warning">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-4 fw-bold text-dark"><?= number_format($usageSummary['products']['current'] ?? 0) ?></span>
                        <span class="text-muted small">/ <?= !empty($usageSummary['products']['unlimited']) ? '∞ Unlimited' : number_format($usageSummary['products']['limit'] ?? 30) ?></span>
                    </div>
                    <?php if (empty($usageSummary['products']['unlimited'])): ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar <?= (($usageSummary['products']['percentage'] ?? 0) >= 90) ? 'bg-danger' : ((($usageSummary['products']['percentage'] ?? 0) >= 75) ? 'bg-warning' : 'bg-warning') ?>"
                                 role="progressbar" style="width: <?= $usageSummary['products']['percentage'] ?? 0 ?>%;"></div>
                        </div>
                    <?php else: ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <?= esc($usageSummary['products']['description'] ?? '') ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Storage Warehouses & Advanced Reports combined -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Storage Warehouses</span>
                        <div class="p-2 rounded-2 bg-success-subtle text-success">
                            <i class="bi bi-building-gear"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-4 fw-bold text-dark"><?= number_format($usageSummary['warehouses']['current'] ?? 0) ?></span>
                        <span class="text-muted small">/ <?= !empty($usageSummary['warehouses']['unlimited']) ? '∞ Unlimited' : number_format($usageSummary['warehouses']['limit'] ?? 1) ?></span>
                    </div>
                    <?php if (empty($usageSummary['warehouses']['unlimited'])): ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar <?= (($usageSummary['warehouses']['percentage'] ?? 0) >= 90) ? 'bg-danger' : 'bg-success' ?>"
                                 role="progressbar" style="width: <?= $usageSummary['warehouses']['percentage'] ?? 0 ?>%;"></div>
                        </div>
                    <?php else: ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="small text-muted" style="font-size: 0.72rem;">Adv. Statements:</span>
                        <?php if (!empty($usageSummary['advanced_reports']['allowed'])): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">
                                <i class="bi bi-check-circle-fill me-1"></i> Unlocked
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-muted border rounded-pill" style="font-size: 0.65rem;">
                                <i class="bi bi-lock-fill me-1"></i> Restricted
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================
     AVAILABLE UPGRADE TIERS CATALOG
     =================================================================== -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Available Software Tiers &amp; Plans</h4>
        <small class="text-muted">Compare features, quotas and upgrade or renew your corporate workspace instantly via Razorpay</small>
    </div>
    <!-- Billing Cycle Switcher -->
    <div class="bg-light p-1 rounded-pill border d-inline-flex align-items-center shadow-sm">
        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold billing-cycle-btn active" data-cycle="monthly" id="cycleBtnMonthly">
            Monthly Billing
        </button>
        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-muted billing-cycle-btn" data-cycle="yearly" id="cycleBtnYearly">
            Annual Billing <span class="badge bg-success text-white ms-1 rounded-pill" style="font-size:0.65rem;">Save 17%</span>
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($allPlans as $plan): ?>
        <?php $isCurrent = ((int)$plan['plan_id'] === $currentPlanId); ?>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm border-0 <?= $isCurrent ? 'border border-primary border-2 shadow' : '' ?>" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white pt-3 pb-3 border-bottom d-block">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold text-dark mb-0"><?= esc($plan['plan_name']) ?></h5>
                            <?php if ($isCurrent): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem;">Current</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($plan['plan_is_popular']): ?>
                            <span class="badge bg-success px-3 py-1 text-uppercase fw-bold shadow-sm" style="font-size: 0.68rem; border-radius: 20px;">
                                Recommended
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-0" style="min-height: 38px; font-size: 0.8rem; line-height: 1.45;"><?= esc($plan['plan_tagline'] ?? '') ?></p>
                </div>

                <div class="card-body d-flex flex-column">
                    <div class="bg-light rounded-3 p-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted small">Price:</span>
                            <span class="fs-4 fw-bold text-primary plan-price-display"
                                  data-monthly="<?= (float)$plan['plan_price_per_month'] ?>"
                                  data-yearly="<?= (float)$plan['plan_price_per_year'] ?>">
                                <?= esc($currency_symbol ?? '₹') ?><span class="price-val"><?= number_format((float)$plan['plan_price_per_month'], 0) ?></span><small class="text-muted fs-6 fw-normal cycle-label">/mo</small>
                            </span>
                        </div>
                        <div class="small text-muted plan-subtext mb-2" style="font-size: 0.72rem;">
                            Billed per month &bull; Cancel or switch anytime
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline pt-2 mt-2 border-top">
                            <span class="text-muted small">Entities:</span>
                            <span class="fw-bold text-dark small"><?= ($plan['plan_max_companies'] == 1) ? '1 Entity' : (($plan['plan_max_companies'] > 0) ? "Up to {$plan['plan_max_companies']} Entities" : 'Unlimited') ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1">
                            <span class="text-muted small">User Seats:</span>
                            <span class="fw-bold text-success small"><?= ($plan['plan_max_users'] == 1) ? '1 User' : (($plan['plan_max_users'] > 0) ? esc($plan['plan_max_users']) . ' Users' : 'Unlimited') ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1">
                            <span class="text-muted small">Invoices:</span>
                            <span class="fw-bold text-dark small"><?= ($plan['plan_max_invoices'] > 0) ? number_format($plan['plan_max_invoices']) . '/mo' : 'Unlimited' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1">
                            <span class="text-muted small">Vendor Bills:</span>
                            <span class="fw-bold text-dark small"><?= ($plan['plan_max_bills'] > 0) ? number_format($plan['plan_max_bills']) . '/mo' : 'Unlimited' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1">
                            <span class="text-muted small">Products:</span>
                            <span class="fw-bold text-dark small"><?= ($plan['plan_max_products'] > 0) ? number_format($plan['plan_max_products']) : 'Unlimited' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1">
                            <span class="text-muted small">Warehouses:</span>
                            <span class="fw-bold text-dark small"><?= ($plan['plan_max_warehouses'] > 0) ? number_format($plan['plan_max_warehouses']) : 'Unlimited' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mt-1 pt-1 border-top">
                            <span class="text-muted small">Adv. Reports:</span>
                            <span><?= !empty($plan['plan_allows_advanced_reports']) ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size:0.65rem;">Included</span>' : '<span class="badge bg-secondary-subtle text-muted border rounded-pill" style="font-size:0.65rem;">Excluded</span>' ?></span>
                        </div>
                    </div>

                    <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        Included Features:
                    </div>
                    <ul class="list-unstyled mb-4 small d-flex flex-column gap-2 flex-grow-1">
                        <?php foreach (array_slice($plan['feature_list'] ?? [], 0, 6) as $feat): ?>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0 mt-0"></i>
                                <span><?= esc($feat) ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($plan['feature_list'] ?? []) > 6): ?>
                            <li class="text-muted ps-4" style="font-size: 0.75rem;">
                                + <?= count($plan['feature_list']) - 6 ?> more features
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($isCurrent && !$isExpired): ?>
                        <div class="d-flex flex-column gap-2 mt-auto">
                            <button type="button" class="btn btn-outline-success btn-sm w-100 fw-semibold" disabled>
                                <i class="bi bi-check2-circle me-1"></i> Active Tier
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-semibold btn-razorpay-pay"
                                    data-plan-id="<?= (int)$plan['plan_id'] ?>"
                                    data-plan-name="<?= esc($plan['plan_name']) ?>"
                                    data-action-label="Renew">
                                <i class="bi bi-arrow-repeat me-1"></i> Renew / Extend
                            </button>
                        </div>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold mt-auto shadow-sm btn-razorpay-pay"
                                data-plan-id="<?= (int)$plan['plan_id'] ?>"
                                data-plan-name="<?= esc($plan['plan_name']) ?>"
                                data-action-label="Upgrade">
                            <i class="bi bi-credit-card-2-front me-1"></i> <?= $isCurrent ? 'Renew via Razorpay' : 'Upgrade via Razorpay' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ===================================================================
     PAYMENT & BILLING TRANSACTION HISTORY
     =================================================================== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Razorpay Payment &amp; Billing History
            </h5>
            <small class="text-muted">Direct digital payments, gateway order IDs, and verification receipts</small>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small">
            Gateway: Razorpay Test Mode
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                    <tr>
                        <th class="ps-3">Txn #</th>
                        <th>Date &amp; Time</th>
                        <th>Tier / Plan</th>
                        <th>Cycle</th>
                        <th>Order ID</th>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-receipt fs-3 d-block text-secondary mb-1"></i>
                                No payment transactions recorded yet. Upgrade your plan above to initialize your first online payment.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td class="ps-3 fw-semibold text-secondary">#<?= (int)$tx['transaction_id'] ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></td>
                                <td class="fw-bold text-dark"><?= esc($tx['plan_name'] ?? 'SaaS Tier') ?></td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill">
                                        <?= ucfirst(esc($tx['transaction_billing_cycle'] ?? 'monthly')) ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="text-primary small"><?= esc($tx['transaction_order_id']) ?></code>
                                </td>
                                <td>
                                    <?php if (!empty($tx['transaction_payment_id'])): ?>
                                        <code class="text-success small"><?= esc($tx['transaction_payment_id']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= esc($tx['transaction_currency'] ?? 'INR') ?> <?= number_format((float)$tx['transaction_amount'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_status'] === 'successful'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i> Successful
                                        </span>
                                    <?php elseif ($tx['transaction_status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-x-circle-fill me-1"></i> <?= ucfirst(esc($tx['transaction_status'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================================================================
     SUBSCRIPTION TEST SIMULATION MODAL (Fallback / Test Mode Assistant)
     =================================================================== -->
<div class="modal fade" id="subscriptionSimModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 14px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front"></i> Razorpay Payment Checkout
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 d-flex align-items-center gap-2 py-2 mb-3">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div class="small">
                        <strong>Test Key Environment:</strong> Testing with Razorpay Test Mode keys (<code id="simKeyIdDisplay"></code>).
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Selected Tier:</span>
                        <strong id="simPlanName" class="text-dark">Starter</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Billing Cycle:</span>
                        <span id="simBillingCycle" class="badge bg-primary-subtle text-primary border rounded-pill">Monthly</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Order ID:</span>
                        <code id="simOrderId" class="text-primary small"></code>
                    </div>
                    <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                        <span class="fw-bold text-dark">Total Payable:</span>
                        <span id="simAmount" class="fs-4 fw-bold text-primary">₹0.00</span>
                    </div>
                </div>

                <p class="small text-muted mb-0">
                    Click <strong>Complete Test Payment</strong> below to simulate a successful payment callback, or let the standard Razorpay Checkout window process your transaction.
                </p>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4 fw-bold shadow-sm" id="btnSimulateSuccess">
                    <i class="bi bi-shield-check me-1"></i> Complete Test Payment
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Razorpay Standard Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
(function() {
    'use strict';

    let currentCycle = 'monthly';
    const currencySymbol = '<?= esc($currency_symbol ?? '₹') ?>';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="csrf_test_name"]')?.value
        || '';
    const baseUrl = '<?= rtrim(base_url(), '/') ?>';

    // Cycle buttons switcher
    const cycleBtnMonthly = document.getElementById('cycleBtnMonthly');
    const cycleBtnYearly = document.getElementById('cycleBtnYearly');

    function updateCycle(cycle) {
        currentCycle = cycle;
        if (cycle === 'monthly') {
            cycleBtnMonthly?.classList.add('active', 'btn-primary');
            cycleBtnMonthly?.classList.remove('text-muted');
            cycleBtnYearly?.classList.remove('active', 'btn-primary');
            cycleBtnYearly?.classList.add('text-muted');
        } else {
            cycleBtnYearly?.classList.add('active', 'btn-primary');
            cycleBtnYearly?.classList.remove('text-muted');
            cycleBtnMonthly?.classList.remove('active', 'btn-primary');
            cycleBtnMonthly?.classList.add('text-muted');
        }

        // Update card pricing displays safely
        document.querySelectorAll('.plan-price-display').forEach(el => {
            const monthly = parseFloat(el.dataset.monthly || 0);
            const yearly = parseFloat(el.dataset.yearly || 0);
            const priceVal = el.querySelector('.price-val');
            const cycleLabel = el.querySelector('.cycle-label');
            const card = el.closest('.card-body');
            const subtext = card ? card.querySelector('.plan-subtext') : null;

            if (cycle === 'yearly') {
                if (priceVal) priceVal.textContent = yearly.toLocaleString('en-IN');
                if (cycleLabel) cycleLabel.textContent = '/yr';
                if (subtext) subtext.textContent = 'Billed annually \u2022 Includes 17% savings';
            } else {
                if (priceVal) priceVal.textContent = monthly.toLocaleString('en-IN');
                if (cycleLabel) cycleLabel.textContent = '/mo';
                if (subtext) subtext.textContent = 'Billed monthly \u2022 Cancel or switch anytime';
            }
        });
    }

    cycleBtnMonthly?.addEventListener('click', () => updateCycle('monthly'));
    cycleBtnYearly?.addEventListener('click', () => updateCycle('yearly'));

    // Simulation modal data cache
    let pendingOrderData = null;

    function getSimModalElement() {
        return document.getElementById('subscriptionSimModal') || document.getElementById('razorpaySimModal');
    }

    function ensureSimModalExists() {
        let modalEl = getSimModalElement();
        if (!modalEl) {
            const div = document.createElement('div');
            div.innerHTML = `
            <div class="modal fade" id="subscriptionSimModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0" style="border-radius: 14px;">
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                <i class="bi bi-credit-card-2-front"></i> Razorpay Payment Checkout
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 d-flex align-items-center gap-2 py-2 mb-3">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div class="small">
                                    <strong>Test Key Environment:</strong> Testing with Razorpay Test Mode keys (<code id="simKeyIdDisplay"></code>).
                                </div>
                            </div>
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Selected Tier:</span>
                                    <strong id="simPlanName" class="text-dark">Starter</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Billing Cycle:</span>
                                    <span id="simBillingCycle" class="badge bg-primary-subtle text-primary border rounded-pill">Monthly</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Order ID:</span>
                                    <code id="simOrderId" class="text-primary small"></code>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                                    <span class="fw-bold text-dark">Total Payable:</span>
                                    <span id="simAmount" class="fs-4 fw-bold text-primary">₹0.00</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-0">
                                Click <strong>Complete Test Payment</strong> below to simulate a successful payment callback, or let the standard Razorpay Checkout window process your transaction.
                            </p>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success btn-sm px-4 fw-bold shadow-sm" id="btnSimulateSuccess">
                                <i class="bi bi-shield-check me-1"></i> Complete Test Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.appendChild(div.firstElementChild);
        }
        bindSimulateSuccessBtn();
    }

    function bindSimulateSuccessBtn() {
        const btn = document.getElementById('btnSimulateSuccess');
        if (btn && !btn.dataset.bound) {
            btn.dataset.bound = 'true';
            btn.addEventListener('click', function() {
                if (!pendingOrderData) return;
                const b = this;
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';

                processVerification(
                    pendingOrderData.order_id,
                    pendingOrderData.test_payment_id,
                    pendingOrderData.test_signature,
                    pendingOrderData.transaction_id,
                    () => {
                        b.disabled = false;
                        b.innerHTML = '<i class="bi bi-shield-check me-1"></i> Complete Test Payment';
                        const mEl = getSimModalElement();
                        if (mEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const inst = bootstrap.Modal.getInstance(mEl);
                            if (inst) inst.hide();
                        }
                    }
                );
            });
        }
    }

    // Checkout button click handler
    document.querySelectorAll('.btn-razorpay-pay').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const planId = this.dataset.planId;
            const planName = this.dataset.planName;
            const originalHtml = this.innerHTML;
            const button = this;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Preparing...';

            const formData = new FormData();
            formData.append('plan_id', planId);
            formData.append('billing_cycle', currentCycle);
            formData.append('csrf_test_name', csrfToken);

            fetch(baseUrl + '/payments/razorpay/create-order', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                button.disabled = false;
                button.innerHTML = originalHtml;

                if (!data.success) {
                    alert('Order Creation Error: ' + (data.error || 'Could not initiate checkout.'));
                    return;
                }

                pendingOrderData = data;

                // If running with standard Razorpay SDK in browser
                if (typeof Razorpay !== 'undefined' && !data.is_simulated) {
                    const rzpOptions = {
                        key: data.key_id,
                        amount: data.amount,
                        currency: data.currency,
                        name: data.merchant_name,
                        description: 'Subscription Upgrade: ' + data.plan_name + ' (' + data.billing_cycle + ')',
                        order_id: data.order_id,
                        prefill: data.prefill || {},
                        theme: { color: '#0d6efd' },
                        handler: function(response) {
                            processVerification(response.razorpay_order_id, response.razorpay_payment_id, response.razorpay_signature, data.transaction_id);
                        },
                        modal: {
                            ondismiss: function() {
                                fetch(baseUrl + '/payments/razorpay/payment-failed', {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                    body: new URLSearchParams({
                                        order_id: data.order_id,
                                        reason: 'Checkout dismissed by user'
                                    })
                                });
                            }
                        }
                    };

                    try {
                        const rzp = new Razorpay(rzpOptions);
                        rzp.on('payment.failed', function(resp) {
                            alert('Payment Failed: ' + (resp.error ? resp.error.description : 'Unknown error'));
                        });
                        rzp.open();
                    } catch (err) {
                        console.warn('Razorpay popup blocked or error, falling back to test modal:', err);
                        openSimModal(data);
                    }
                } else {
                    // Test simulated mode or Razorpay API sandbox mock
                    openSimModal(data);
                }
            })
            .catch(err => {
                button.disabled = false;
                button.innerHTML = originalHtml;
                alert('Connection error: ' + err.message);
            });
        });
    });

    function openSimModal(data) {
        ensureSimModalExists();

        const safeSetText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '';
        };

        safeSetText('simPlanName', data.plan_name);
        safeSetText('simBillingCycle', (data.billing_cycle || '').toUpperCase());
        safeSetText('simOrderId', data.order_id);
        safeSetText('simAmount', data.amount_formatted);
        safeSetText('simKeyIdDisplay', data.key_id);

        const modalEl = getSimModalElement();
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
        }
    }

    bindSimulateSuccessBtn();

    function processVerification(orderId, paymentId, signature, transactionId, callback) {
        const formData = new FormData();
        formData.append('razorpay_order_id', orderId);
        formData.append('razorpay_payment_id', paymentId);
        formData.append('razorpay_signature', signature);
        formData.append('transaction_id', transactionId);
        formData.append('csrf_test_name', csrfToken);

        fetch(baseUrl + '/payments/razorpay/verify-payment', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (callback) callback();
            if (res.success) {
                window.location.reload();
            } else {
                alert('Verification Failed: ' + (res.error || res.message));
            }
        })
        .catch(err => {
            if (callback) callback();
            alert('Verification request failed: ' + err.message);
        });
    }
})();
</script>
<?= $this->endSection() ?>

