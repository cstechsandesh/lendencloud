<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-credit-card-2-front text-primary"></i> Platform Subscriptions &amp; Billing
        </h1>
        <p class="page-subtitle text-muted mb-0">Super Admin control over company subscriptions, trial extensions, plan upgrades, and SaaS pricing catalog.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Online Subscriptions Switch Status & 1-Click Toggle -->
        <form action="<?= base_url('subscriptions/toggle-status') ?>" method="POST" class="d-inline m-0">
            <?= csrf_field() ?>
            <?php if (!empty($subscriptionsEnabled)): ?>
                <button type="submit" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2 fw-semibold px-3 py-2 bg-success-subtle text-success border-success-subtle" title="Click to pause public subscriptions and online self-serve signups">
                    <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                    Subscriptions: Active (Click to Pause)
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 fw-semibold px-3 py-2 bg-danger-subtle text-danger border-danger-subtle" title="Click to enable public subscriptions and online self-serve signups">
                    <span class="rounded-circle bg-danger d-inline-block" style="width: 8px; height: 8px;"></span>
                    Subscriptions: Paused (Click to Enable)
                </button>
            <?php endif; ?>
        </form>

        <a href="<?= base_url('subscriptions/export') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" title="Download Subscriptions CSV Report">
            <i class="bi bi-filetype-csv fs-6 text-success"></i> Export CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#assignSubModal">
            <i class="bi bi-plus-circle"></i> Assign Subscription
        </button>
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 fw-semibold rounded-pill">
            <i class="bi bi-star-fill text-warning me-1"></i> Super Admin
        </span>
    </div>
</div>

<?php if (empty($subscriptionsEnabled)): ?>
    <div class="alert alert-warning border border-warning-subtle d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 p-3 rounded-3 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-pause-circle-fill text-warning fs-3"></i>
            <div>
                <strong class="text-dark">Online Subscriptions are currently PAUSED (subscriptions.enabled = false)</strong>
                <div class="small text-muted">Public landing page trial registrations and customer portal automated renewals are temporarily disabled.</div>
            </div>
        </div>
        <form action="<?= base_url('subscriptions/toggle-status') ?>" method="POST" class="m-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-play-circle me-1"></i> Re-Enable Subscriptions Now
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- KPI Summary Stats Grid -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Subscriptions</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= number_format($stats['total_subscriptions'] ?? 0) ?></h3>
                    <small class="text-muted">Companies with active or past tiers</small>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-layers-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Active Paid Accounts</div>
                    <h3 class="fw-bold mb-0 mt-1 text-success"><?= number_format($stats['active_paid'] ?? 0) ?></h3>
                    <small class="text-muted">
                        MRR: <strong><?= esc($currency_symbol ?? '₹') ?><?= number_format($stats['mrr'] ?? 0, 0) ?></strong> &bull; ARR: <?= esc($currency_symbol ?? '₹') ?><?= number_format($stats['arr'] ?? 0, 0) ?>
                    </small>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Active 30-Day Trials</div>
                    <h3 class="fw-bold mb-0 mt-1 text-info"><?= number_format($stats['active_trials'] ?? 0) ?></h3>
                    <small>
                        <?php if (($stats['expiring_soon'] ?? 0) > 0): ?>
                            <span class="text-danger fw-semibold"><i class="bi bi-alarm me-1"></i><?= (int)$stats['expiring_soon'] ?> expiring in &le; 7 days</span>
                        <?php else: ?>
                            <span class="text-success"><i class="bi bi-shield-check me-1"></i>All trials healthy</span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-rocket-takeoff-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Expired / Cancelled</div>
                    <h3 class="fw-bold mb-0 mt-1 text-danger"><?= number_format($stats['expired_or_cancelled'] ?? 0) ?></h3>
                    <small class="text-muted">Actionable retention follow-ups</small>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation Switcher -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a href="<?= base_url('subscriptions?tab=subscriptions') ?>" class="nav-link fw-semibold px-4 py-2 <?= ($activeTab === 'subscriptions') ? 'active shadow-sm' : 'bg-white border text-secondary' ?>">
            <i class="bi bi-list-check me-2"></i>Company Subscriptions (<?= number_format($stats['total_subscriptions'] ?? 0) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a href="<?= base_url('subscriptions?tab=plans') ?>" class="nav-link fw-semibold px-4 py-2 <?= ($activeTab === 'plans') ? 'active shadow-sm' : 'bg-white border text-secondary' ?>">
            <i class="bi bi-grid-fill me-2"></i>SaaS Pricing Plans Master (<?= count($allPlans) ?>)
        </a>
    </li>
</ul>

<?php if ($activeTab === 'subscriptions'): ?>
    <!-- Filters & Search Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= base_url('subscriptions') ?>" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="subscriptions">
                
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search company, code, GSTIN, city, or plan..." value="<?= esc($search) ?>">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="all" <?= ($status === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active (Paid)</option>
                        <option value="trial" <?= ($status === 'trial') ? 'selected' : '' ?>>30-Day Free Trial</option>
                        <option value="expired" <?= ($status === 'expired') ? 'selected' : '' ?>>Expired / Past Due</option>
                        <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="plan_id" class="form-select">
                        <option value="0">All Subscription Tiers</option>
                        <?php foreach ($allPlans as $p): ?>
                            <option value="<?= $p['plan_id'] ?>" <?= ((int)$planId === (int)$p['plan_id']) ? 'selected' : '' ?>>
                                <?= esc($p['plan_name']) ?> (<?= esc($currency_symbol ?? '₹') ?><?= number_format($p['plan_price_per_month'], 0) ?>/mo)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="cycle" class="form-select">
                        <option value="all" <?= ($cycle === 'all') ? 'selected' : '' ?>>All Frequencies</option>
                        <option value="monthly" <?= ($cycle === 'monthly') ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= ($cycle === 'yearly') ? 'selected' : '' ?>>Annual (Yearly)</option>
                    </select>
                </div>

                <div class="col-6 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" title="Apply Filters">
                        <i class="bi bi-filter"></i>
                    </button>
                    <?php if (!empty($search) || ($status !== 'all' && !empty($status)) || $planId > 0 || ($cycle !== 'all' && !empty($cycle))): ?>
                        <a href="<?= base_url('subscriptions?tab=subscriptions') ?>" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscriptions Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-table text-primary"></i> Subscriptions Register (<?= count($subscriptions) ?> records shown)
            </h5>
            <small class="text-muted">Showing active tenant subscriptions across the LenDen platform</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-3">Company &amp; Location</th>
                            <th>Primary Contact</th>
                            <th>Plan Tier</th>
                            <th>Billing &amp; Rate</th>
                            <th>Validity &amp; Expiry</th>
                            <th>Status</th>
                            <th>Payment Mode</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscriptions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No subscriptions found matching the selected filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subscriptions as $s): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-buildings-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block text-dark"><?= esc($s['company_name'] ?? 'Unknown Company') ?></strong>
                                                <small class="text-muted d-block">
                                                    Code: <?= esc($s['company_code'] ?? 'N/A') ?> &bull; <?= esc($s['company_city'] ?? 'Mumbai') ?>
                                                </small>
                                                <?php if (!empty($s['company_gstin'])): ?>
                                                    <small class="text-secondary" style="font-size: 0.75rem;">GSTIN: <?= esc($s['company_gstin']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['admin_name'])): ?>
                                            <div class="fw-semibold text-dark"><?= esc($s['admin_name']) ?></div>
                                            <small class="text-muted d-block"><?= esc($s['admin_email']) ?></small>
                                            <?php if (!empty($s['admin_phone'])): ?>
                                                <small class="text-secondary"><i class="bi bi-telephone me-1"></i><?= esc($s['admin_phone']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong class="text-primary"><?= esc($s['plan_name'] ?? 'Custom Plan') ?></strong>
                                            <?php if (!empty($s['plan_badge'])): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.7rem;"><?= esc($s['plan_badge']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            Cap: <?= ($s['plan_max_users'] > 0) ? esc($s['plan_max_users']) . ' Users' : 'Unlimited Users' ?>
                                            &bull; <?= ($s['plan_max_companies'] == 1) ? '1 Entity' : (($s['plan_max_companies'] > 0) ? "Up to {$s['plan_max_companies']} Entities" : 'Unlimited Entities') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-dark fs-6"><?= esc($currency_symbol ?? '₹') ?><?= number_format((float)($s['subscription_amount'] ?? 0), 2) ?></strong>
                                        <small class="text-muted d-block text-capitalize">
                                            <?= esc($s['subscription_billing_cycle'] ?? 'monthly') ?> Frequency
                                        </small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?= !empty($s['subscription_end_date']) ? date('d M Y', strtotime($s['subscription_end_date'])) : 'No Expiry' ?>
                                        </div>
                                        <small class="text-muted d-block">
                                            Since: <?= !empty($s['subscription_start_date']) ? date('d M Y', strtotime($s['subscription_start_date'])) : 'N/A' ?>
                                        </small>
                                        
                                        <!-- Days Remaining Badge -->
                                        <?php 
                                            $days = (int)($s['days_remaining'] ?? 0);
                                            if ($s['subscription_status'] === 'expired' || $days < 0): 
                                        ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock-history me-1"></i>Expired <?= abs($days) ?>d ago
                                            </span>
                                        <?php elseif ($days <= 7): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill mt-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-hourglass-split me-1"></i>Expires in <?= $days ?>d
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-shield-check me-1"></i><?= $days ?> days left
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $subStatus = $s['subscription_status'] ?? 'trial';
                                            if ($subStatus === 'active') {
                                                echo '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Active</span>';
                                            } elseif ($subStatus === 'trial') {
                                                echo '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1"><i class="bi bi-rocket-takeoff-fill me-1"></i>30-Day Trial</span>';
                                            } elseif ($subStatus === 'expired') {
                                                echo '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>Expired</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">' . esc(ucfirst($subStatus)) . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (str_starts_with($s['subscription_payment_method'] ?? '', 'razorpay')): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small text-capitalize">
                                                <i class="bi bi-credit-card-2-front me-1"></i> <?= esc(str_replace('_', ' ', $s['subscription_payment_method'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border px-2 py-1 small text-capitalize">
                                                <?= esc(str_replace('_', ' ', $s['subscription_payment_method'] ?? 'trial')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Edit Modal Trigger -->
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick='showEditSubModal(<?= json_encode($s) ?>)' title="Edit Subscription Tier &amp; Dates">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Quick Extend +30 Days Button -->
                                            <form action="<?= base_url('subscriptions/quick-action/' . $s['subscription_id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Extend subscription for <?= esc(addslashes($s['company_name'])) ?> by +30 days?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="extend_30">
                                                <button type="submit" class="btn btn-outline-info btn-sm" title="Quick extend +30 days">
                                                    +30d
                                                </button>
                                            </form>

                                            <!-- Quick Activate Paid Button (if not already active) -->
                                            <?php if ($s['subscription_status'] !== 'active'): ?>
                                                <form action="<?= base_url('subscriptions/quick-action/' . $s['subscription_id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Mark subscription for <?= esc(addslashes($s['company_name'])) ?> as Paid &amp; Active?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="activate">
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Mark Paid &amp; Active">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Switch Context to this Company -->
                                            <a href="<?= base_url('companies/switch/' . $s['subscription_company_id']) ?>" class="btn btn-outline-secondary btn-sm" title="Switch Portal Context to this Company">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager)): ?>
                <div class="p-3 border-top d-flex justify-content-end">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($activeTab === 'plans'): ?>
    <!-- SaaS Pricing Plans Master Catalog -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">SaaS Pricing Catalog</h5>
            <small class="text-muted">Master tier rates, subscription quotas, and feature entitlements</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="bi bi-plus-circle"></i> Add New Plan Tier
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($allPlans as $plan): ?>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0 <?= ($plan['plan_is_popular']) ? 'border border-primary border-2' : '' ?>" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white pt-3 pb-3 border-bottom d-block">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold text-dark mb-0"><?= esc($plan['plan_name']) ?></h5>
                                <?php if ($plan['plan_is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 0.65rem;">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.65rem;">Disabled</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($plan['plan_is_popular']): ?>
                                <span class="badge bg-primary px-3 py-1 text-uppercase fw-bold shadow-sm" style="font-size: 0.68rem; border-radius: 20px;">
                                    Most Popular
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-0" style="min-height: 40px; font-size: 0.8rem; line-height: 1.45;"><?= esc($plan['plan_tagline'] ?? '') ?></p>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Pricing Details Box -->
                        <div class="bg-light rounded-3 p-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="text-muted small">Monthly Rate:</span>
                                <span class="fs-5 fw-bold text-primary"><?= esc($currency_symbol ?? '₹') ?><?= number_format((float)$plan['plan_price_per_month'], 0) ?><small class="text-muted fs-6 fw-normal">/mo</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="text-muted small">Annual Rate:</span>
                                <span class="fw-bold text-dark"><?= esc($currency_symbol ?? '₹') ?><?= number_format((float)$plan['plan_price_per_year'], 0) ?><small class="text-muted fs-6 fw-normal">/yr</small></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline pt-2 border-top">
                                <span class="text-muted small">Entities Quota:</span>
                                <span class="fw-bold text-primary small">
                                    <?= ($plan['plan_max_companies'] == 1) ? '1 Entity' : (($plan['plan_max_companies'] > 0) ? "Up to {$plan['plan_max_companies']} Entities" : 'Unlimited Entities') ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1">
                                <span class="text-muted small">User Seats:</span>
                                <span class="fw-bold text-success small">
                                    <?= ($plan['plan_max_users'] == 1) ? '1 User' : (($plan['plan_max_users'] > 0) ? 'Up to ' . esc($plan['plan_max_users']) . ' Users' : 'Unlimited Users') ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1">
                                <span class="text-muted small">Sales Invoices:</span>
                                <span class="fw-bold text-dark small">
                                    <?= ($plan['plan_max_invoices'] > 0) ? number_format($plan['plan_max_invoices']) . '/mo' : 'Unlimited' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1">
                                <span class="text-muted small">Vendor Bills:</span>
                                <span class="fw-bold text-dark small">
                                    <?= ($plan['plan_max_bills'] > 0) ? number_format($plan['plan_max_bills']) . '/mo' : 'Unlimited' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1">
                                <span class="text-muted small">Product Items:</span>
                                <span class="fw-bold text-dark small">
                                    <?= ($plan['plan_max_products'] > 0) ? number_format($plan['plan_max_products']) : 'Unlimited' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1">
                                <span class="text-muted small">Warehouses:</span>
                                <span class="fw-bold text-dark small">
                                    <?= ($plan['plan_max_warehouses'] > 0) ? number_format($plan['plan_max_warehouses']) : 'Unlimited' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline mt-1 pt-1 border-top">
                                <span class="text-muted small">Adv. Reports:</span>
                                <span>
                                    <?= !empty($plan['plan_allows_advanced_reports']) ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size:0.65rem;">Included</span>' : '<span class="badge bg-secondary-subtle text-muted border rounded-pill" style="font-size:0.65rem;">Excluded</span>' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Features List -->
                        <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            Included Capabilities:
                        </div>
                        <ul class="list-unstyled mb-4 small d-flex flex-column gap-2 flex-grow-1">
                            <?php foreach (array_slice($plan['feature_list'] ?? [], 0, 5) as $feat): ?>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0 mt-0"></i>
                                    <span><?= esc($feat) ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (count($plan['feature_list'] ?? []) > 5): ?>
                                <li class="text-muted ps-4" style="font-size: 0.75rem;">
                                    + <?= count($plan['feature_list']) - 5 ?> additional features
                                </li>
                            <?php endif; ?>
                        </ul>

                        <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-semibold mt-auto" onclick='showEditPlanModal(<?= json_encode($plan) ?>)'>
                            <i class="bi bi-sliders me-1"></i> Configure Plan Tier
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ===================================================================
     MODAL 1: Edit / Upgrade Existing Subscription
     =================================================================== -->
<div class="modal fade" id="editSubModal" tabindex="-1" aria-labelledby="editSubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editSubModalLabel">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Company Subscription
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editSubForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <!-- Target Company Summary -->
                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.7rem;">Target Company Entity:</div>
                        <h6 class="fw-bold mb-0 text-dark mt-1" id="editModalCompanyName">LenDen Enterprise</h6>
                        <small class="text-muted d-block" id="editModalCompanyMeta">Code: LENDEN &bull; Mumbai</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubPlan">Plan Tier *</label>
                            <select name="subscription_plan_id" id="editSubPlan" class="form-select" required onchange="onEditPlanChange()">
                                <?php foreach ($allPlans as $p): ?>
                                    <option value="<?= $p['plan_id'] ?>" data-mrate="<?= $p['plan_price_per_month'] ?>" data-yrate="<?= $p['plan_price_per_year'] ?>">
                                        <?= esc($p['plan_name']) ?> (<?= esc($currency_symbol ?? '₹') ?><?= number_format($p['plan_price_per_month'], 0) ?>/mo)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubCycle">Billing Frequency *</label>
                            <select name="subscription_billing_cycle" id="editSubCycle" class="form-select" required onchange="onEditCycleChange()">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Annual (Yearly)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubAmount">Amount (INR) *</label>
                            <input type="number" step="0.01" name="subscription_amount" id="editSubAmount" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubStatus">Subscription Status *</label>
                            <select name="subscription_status" id="editSubStatus" class="form-select" required>
                                <option value="trial">30-Day Free Trial</option>
                                <option value="active">Active (Paid Account)</option>
                                <option value="expired">Expired / Past Due</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubStartDate">Start Date *</label>
                            <input type="date" name="subscription_start_date" id="editSubStartDate" class="form-control" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="editSubEndDate">End Date (Expiry) *</label>
                            <input type="date" name="subscription_end_date" id="editSubEndDate" class="form-control" required>
                        </div>
                    </div>

                    <!-- Fast Date Extension Presets -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <small class="text-muted fw-semibold">Fast Extensions:</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="addDaysToEditEnd(30)">+30 Days</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="addDaysToEditEnd(90)">+90 Days</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="addDaysToEditEnd(365)">+1 Year</button>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold" for="editSubPayMethod">Payment Mode / Reference</label>
                        <input type="text" name="subscription_payment_method" id="editSubPayMethod" class="form-control" placeholder="e.g. bank_transfer_neft, upi_qr, cheque_deposit, complimentary_trial">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODAL 2: Assign Subscription to Company
     =================================================================== -->
<div class="modal fade" id="assignSubModal" tabindex="-1" aria-labelledby="assignSubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="assignSubModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Assign Subscription to Company
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('subscriptions/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="assignCompanyId">Select Target Company *</label>
                        <select name="subscription_company_id" id="assignCompanyId" class="form-select" required>
                            <option value="">-- Choose Company --</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= $comp['company_id'] ?>">
                                    <?= esc($comp['company_name']) ?> (Code: <?= esc($comp['company_code'] ?? 'N/A') ?>, <?= esc($comp['company_city'] ?? 'Mumbai') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignPlanId">Plan Tier *</label>
                            <select name="subscription_plan_id" id="assignPlanId" class="form-select" required onchange="onAssignPlanChange()">
                                <?php foreach ($allPlans as $p): ?>
                                    <option value="<?= $p['plan_id'] ?>" data-mrate="<?= $p['plan_price_per_month'] ?>" data-yrate="<?= $p['plan_price_per_year'] ?>" <?= ($p['plan_id'] == 2) ? 'selected' : '' ?>>
                                        <?= esc($p['plan_name']) ?> (<?= esc($currency_symbol ?? '₹') ?><?= number_format($p['plan_price_per_month'], 0) ?>/mo)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignCycle">Billing Frequency *</label>
                            <select name="subscription_billing_cycle" id="assignCycle" class="form-select" required onchange="onAssignCycleChange()">
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Annual (Yearly)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignAmount">Amount (INR) *</label>
                            <input type="number" step="0.01" name="subscription_amount" id="assignAmount" class="form-control" value="1299.00" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignStatus">Initial Status *</label>
                            <select name="subscription_status" id="assignStatus" class="form-select" required>
                                <option value="trial" selected>30-Day Free Trial</option>
                                <option value="active">Active (Paid)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignStartDate">Start Date *</label>
                            <input type="date" name="subscription_start_date" id="assignStartDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="assignEndDate">End Date *</label>
                            <input type="date" name="subscription_end_date" id="assignEndDate" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold" for="assignPayMethod">Payment Mode / Reference</label>
                        <input type="text" name="subscription_payment_method" id="assignPayMethod" class="form-control" value="complimentary_trial" placeholder="e.g. complimentary_trial, bank_transfer, upi">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Assign Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODAL 3: Configure SaaS Pricing Plan Master Tier
     =================================================================== -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editPlanModalLabel">
                    <i class="bi bi-sliders text-primary me-2"></i>Configure Pricing Plan Tier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editPlanForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="planNameInput">Plan Name *</label>
                        <input type="text" name="plan_name" id="planNameInput" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="planTaglineInput">Plan Tagline / Subtitle</label>
                        <input type="text" name="plan_tagline" id="planTaglineInput" class="form-control">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="planPriceMonthInput">Price / Month (INR) *</label>
                            <input type="number" step="0.01" name="plan_price_per_month" id="planPriceMonthInput" class="form-control" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="planPriceYearInput">Price / Year (INR) *</label>
                            <input type="number" step="0.01" name="plan_price_per_year" id="planPriceYearInput" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="planMaxCompaniesInput">Entities Cap (0 = Unlimited) *</label>
                            <input type="number" name="plan_max_companies" id="planMaxCompaniesInput" class="form-control" required min="0">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="planMaxUsersInput">Max User Seats (0 = Unlimited) *</label>
                            <input type="number" name="plan_max_users" id="planMaxUsersInput" class="form-control" required min="0">
                        </div>
                    </div>

                    <!-- Module Quotas & Caps -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="bi bi-speedometer2 text-primary me-1"></i> Module Resource Quotas &amp; Caps
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="planMaxInvoicesInput">Monthly Invoices Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_invoices" id="planMaxInvoicesInput" class="form-control form-control-sm" required min="0" placeholder="e.g. 50">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="planMaxBillsInput">Monthly Vendor Bills Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_bills" id="planMaxBillsInput" class="form-control form-control-sm" required min="0" placeholder="e.g. 20">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="planMaxProductsInput">Catalog Products Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_products" id="planMaxProductsInput" class="form-control form-control-sm" required min="0" placeholder="e.g. 30">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="planMaxWarehousesInput">Storage Warehouses Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_warehouses" id="planMaxWarehousesInput" class="form-control form-control-sm" required min="0" placeholder="e.g. 1">
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="plan_allows_advanced_reports" id="planAllowsAdvReportsInput" value="1">
                            <label class="form-check-label fw-semibold small text-dark" for="planAllowsAdvReportsInput">
                                <i class="bi bi-file-earmark-bar-graph text-success me-1"></i> Allow Advanced Accounting Statements (Balance Sheet, P&amp;L, Trial Balance)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="planBadgeInput">Marketing Badge</label>
                        <input type="text" name="plan_badge" id="planBadgeInput" class="form-control" placeholder="e.g. Starter, Most Popular, Enterprise">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="planFeaturesInput">Included Features (one feature per line)</label>
                        <textarea name="plan_features" id="planFeaturesInput" class="form-control" rows="5" placeholder="1 Legal Business Entity&#10;Up to 10 Multi-Role User Logins&#10;Full Double-Entry Journals"></textarea>
                    </div>

                    <div class="d-flex align-items-center gap-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="plan_is_popular" id="planIsPopularInput" value="1">
                            <label class="form-check-label fw-semibold small" for="planIsPopularInput">
                                Mark as <strong>Most Popular</strong>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="plan_is_active" id="planIsActiveInput" value="1" checked>
                            <label class="form-check-label fw-semibold small" for="planIsActiveInput">
                                Active (Publicly Visible)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Update Plan Tier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================================
     MODAL 4: Create New SaaS Pricing Plan Tier
     =================================================================== -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createPlanModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Add New Pricing Plan Tier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('subscriptions/plans/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-7">
                            <label class="form-label fw-semibold" for="newPlanName">Plan Name *</label>
                            <input type="text" name="plan_name" id="newPlanName" class="form-control" placeholder="e.g. Professional Cloud" required>
                        </div>
                        <div class="col-12 col-sm-5">
                            <label class="form-label fw-semibold" for="newPlanCode">Plan Code</label>
                            <input type="text" name="plan_code" id="newPlanCode" class="form-control" placeholder="e.g. professional_cloud">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="newPlanTagline">Plan Tagline / Subtitle</label>
                        <input type="text" name="plan_tagline" id="newPlanTagline" class="form-control" placeholder="e.g. High performance accounting suite for growing teams">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="newPlanPriceMonth">Price / Month (INR) *</label>
                            <input type="number" step="0.01" name="plan_price_per_month" id="newPlanPriceMonth" class="form-control" value="999.00" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="newPlanPriceYear">Price / Year (INR) *</label>
                            <input type="number" step="0.01" name="plan_price_per_year" id="newPlanPriceYear" class="form-control" value="9990.00" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="newPlanMaxCompanies">Entities Cap (0 = Unlimited) *</label>
                            <input type="number" name="plan_max_companies" id="newPlanMaxCompanies" class="form-control" value="1" required min="0">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold" for="newPlanMaxUsers">Max User Seats (0 = Unlimited) *</label>
                            <input type="number" name="plan_max_users" id="newPlanMaxUsers" class="form-control" value="5" required min="0">
                        </div>
                    </div>

                    <!-- Module Quotas & Caps -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="bi bi-speedometer2 text-primary me-1"></i> Module Resource Quotas &amp; Caps
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="newPlanMaxInvoices">Monthly Invoices Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_invoices" id="newPlanMaxInvoices" class="form-control form-control-sm" value="50" required min="0">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="newPlanMaxBills">Monthly Vendor Bills Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_bills" id="newPlanMaxBills" class="form-control form-control-sm" value="20" required min="0">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="newPlanMaxProducts">Catalog Products Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_products" id="newPlanMaxProducts" class="form-control form-control-sm" value="30" required min="0">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small fw-semibold" for="newPlanMaxWarehouses">Storage Warehouses Cap (0 = Unlimited) *</label>
                                <input type="number" name="plan_max_warehouses" id="newPlanMaxWarehouses" class="form-control form-control-sm" value="1" required min="0">
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="plan_allows_advanced_reports" id="newPlanAllowsAdvReports" value="1" checked>
                            <label class="form-check-label fw-semibold small text-dark" for="newPlanAllowsAdvReports">
                                <i class="bi bi-file-earmark-bar-graph text-success me-1"></i> Allow Advanced Accounting Statements (Balance Sheet, P&amp;L, Trial Balance)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="newPlanBadge">Marketing Badge</label>
                        <input type="text" name="plan_badge" id="newPlanBadge" class="form-control" placeholder="e.g. Pro, Growth, Business">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="newPlanFeatures">Included Features (one per line)</label>
                        <textarea name="plan_features" id="newPlanFeatures" class="form-control" rows="4" placeholder="1 Legal Entity&#10;Up to 5 Users&#10;Full Double-Entry Accounting"></textarea>
                    </div>

                    <div class="d-flex align-items-center gap-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="plan_is_popular" id="newPlanIsPopular" value="1">
                            <label class="form-check-label fw-semibold small" for="newPlanIsPopular">
                                Mark as <strong>Most Popular</strong>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="plan_is_active" id="newPlanIsActive" value="1" checked>
                            <label class="form-check-label fw-semibold small" for="newPlanIsActive">
                                Active (Publicly Visible)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Create Plan Tier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/subscriptions-admin.js') ?>"></script>
<?= $this->endSection() ?>

