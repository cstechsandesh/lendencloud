<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= rtrim(base_url(), '/') ?>">

    <!-- Google Analytics (gtag.js) -->
    <?= google_analytics() ?>
    <!-- Google Search Console Verification -->
    <?= google_search_console() ?>
    <!-- SEO Meta Tags, Canonical & Mobile Tags -->
    <?= seo_meta($seo ?? ['title' => 'Start 30-Day Free Trial — LenDen Cloud']) ?>

    <!-- Local Bootstrap 5.3.3 CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Local Bootstrap Icons -->
    <link href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/auth.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/subscribe.css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="subscribe-container">
    <!-- Header Bar -->
    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
        <a href="<?= base_url('/') ?>" class="text-decoration-none d-flex align-items-center gap-2">
            <div class="rounded-3 bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-wallet2 fs-5"></i>
            </div>
            <span class="fw-bold fs-5 text-dark">LenDen <span class="text-primary">Cloud</span></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline">Already registered?</span>
            <a href="<?= base_url('login') ?>" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        </div>
    </div>

    <div class="p-4 p-md-5">
        <?php if (empty($subscriptionsEnabled)): ?>
            <!-- Paused Notice -->
            <div class="text-center py-5">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-pause-circle-fill fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Online Registrations Temporarily Paused</h3>
                <p class="text-muted mx-auto" style="max-width: 520px;">
                    Self-serve company trial registrations are currently paused by system administration. Please contact our support desk for manual provisioning.
                </p>
                <div class="mt-4 d-flex justify-content-center gap-2">
                    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary px-4 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Back to Home
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-primary px-4 fw-semibold">
                        Sign In to Existing Account
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                    <i class="bi bi-rocket-takeoff-fill me-1"></i> 30-Day Unrestricted Free Trial
                </span>
                <h2 class="fw-bold text-dark mt-2 mb-1">Set Up Your Business Workspace</h2>
                <p class="text-muted small mb-0">No credit card required. Full double-entry ledgers, invoicing, and inventory will be automatically provisioned.</p>
            </div>

            <?= $this->include('partials/flash_messages') ?>

            <form action="<?= base_url('subscribe') ?>" method="POST" id="subscribeForm">
                <?= csrf_field() ?>

                <!-- Step 1: Select Plan Tier -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <label class="form-label fw-bold mb-0 text-dark">
                            <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
                            Choose Your Software Tier
                        </label>
                        <!-- Billing Cycle Toggle -->
                        <div class="btn-group bg-light p-1 border rounded-pill" role="group">
                            <button type="button" class="btn cycle-toggle-btn <?= ($selectedCycle === 'monthly') ? 'btn-primary' : 'btn-light text-muted' ?>" onclick="setCycle('monthly')" id="btnMonthly">
                                Monthly
                            </button>
                            <button type="button" class="btn cycle-toggle-btn <?= ($selectedCycle === 'yearly') ? 'btn-primary' : 'btn-light text-muted' ?>" onclick="setCycle('yearly')" id="btnYearly">
                                Annual <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Save 17%</span>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="plan_id" id="selectedPlanIdInput" value="<?= esc($selectedPlanId) ?>">
                    <input type="hidden" name="billing_cycle" id="selectedCycleInput" value="<?= esc($selectedCycle) ?>">

                    <div class="row g-3">
                        <?php foreach ($plans as $p): ?>
                            <?php $isSel = ((int)$p['plan_id'] === (int)$selectedPlanId); ?>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="plan-select-card h-100 <?= $isSel ? 'selected' : '' ?>" id="planCard_<?= $p['plan_id'] ?>" onclick="selectPlan(<?= $p['plan_id'] ?>)">
                                    <?php if ($p['plan_is_popular']): ?>
                                        <span class="badge bg-primary position-absolute top-0 end-0 translate-middle-y me-2 px-2 py-1" style="font-size: 0.65rem; border-radius: 12px;">POPULAR</span>
                                    <?php endif; ?>
                                    <div class="fw-bold text-dark fs-6 mb-1"><?= esc($p['plan_name']) ?></div>
                                    <div class="mb-2">
                                        <span class="fs-5 fw-bold text-primary rate-display" data-mrate="<?= $p['plan_price_per_month'] ?>" data-yrate="<?= $p['plan_price_per_year'] ?>">
                                            ₹<?= number_format(($selectedCycle === 'yearly') ? $p['plan_price_per_year'] : $p['plan_price_per_month'], 0) ?>
                                        </span>
                                        <small class="text-muted cycle-label">/<?= ($selectedCycle === 'yearly') ? 'yr' : 'mo' ?></small>
                                    </div>
                                    <div class="small text-muted mb-2" style="font-size: 0.75rem;">
                                        <?= ($p['plan_max_companies'] == 1) ? '1 Entity' : (($p['plan_max_companies'] > 0) ? "Up to {$p['plan_max_companies']} Entities" : 'Unlimited Entities') ?> &bull; <?= ($p['plan_max_users'] == 1) ? '1 Seat' : (($p['plan_max_users'] > 0) ? $p['plan_max_users'] . ' Seats' : 'Unlimited Seats') ?>
                                    </div>
                                    <div class="text-success small fw-semibold" style="font-size: 0.7rem;">
                                        <i class="bi bi-check2-circle me-1"></i>30-Day Free Trial
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2: Company Information -->
                <div class="mb-4 pt-3 border-top">
                    <label class="form-label fw-bold mb-3 text-dark">
                        <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
                        Company &amp; Legal Entity Information
                    </label>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Company Legal Name *</label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Acme Technologies Pvt Ltd" value="<?= old('company_name') ?>" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Company Code</label>
                            <input type="text" name="company_code" class="form-control" placeholder="e.g. ACMETEC" value="<?= old('company_code') ?>">
                            <small class="text-muted" style="font-size: 0.7rem;">Leave blank to auto-generate</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary">GSTIN Number</label>
                            <input type="text" name="company_gstin" class="form-control" placeholder="27AAAAA0000A1Z5" value="<?= old('company_gstin') ?>">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">City / Registered Office *</label>
                            <input type="text" name="company_city" class="form-control" placeholder="Mumbai" value="<?= old('company_city', 'Mumbai') ?>" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">State / Province *</label>
                            <input type="text" name="company_state" class="form-control" placeholder="Maharashtra" value="<?= old('company_state', 'Maharashtra') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Administrator Account -->
                <div class="mb-4 pt-3 border-top">
                    <label class="form-label fw-bold mb-3 text-dark">
                        <span class="badge bg-primary rounded-circle me-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">3</span>
                        Administrator Login Account
                    </label>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Your Full Name *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="admin_name" class="form-control border-start-0 ps-0" placeholder="John Doe" value="<?= old('admin_name') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Business Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="admin_email" class="form-control border-start-0 ps-0" placeholder="admin@company.com" value="<?= old('admin_email') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold text-secondary">Choose Username *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-at text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="john_admin" value="<?= old('username') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold text-secondary">Choose Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required minlength="6">
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Minimum 6 characters</small>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold text-secondary">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="tel" name="admin_phone" class="form-control border-start-0 ps-0" placeholder="+91 98765 43210" value="<?= old('admin_phone') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guarantee & Submission -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="d-flex align-items-center gap-3 flex-wrap small text-muted">
                                <span><i class="bi bi-check-circle-fill text-success me-1"></i> 30 Days Free</span>
                                <span><i class="bi bi-check-circle-fill text-success me-1"></i> No Credit Card</span>
                                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Full Double-Entry</span>
                                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Ready In 10 Seconds</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold w-100 shadow-sm">
                                <i class="bi bi-rocket-takeoff me-1"></i> Start 30-Day Free Trial
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center small text-muted">
                    By signing up, you agree to our standard terms of service. Already have an account?
                    <a href="<?= base_url('login') ?>" class="text-primary fw-semibold text-decoration-none ms-1">
                        Sign In Here &rarr;
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Local Bootstrap 5.3.3 JS -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/subscribe.js') ?>"></script>
</body>
</html>

