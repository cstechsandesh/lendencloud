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

    <!-- Comprehensive SEO Meta Tags, Canonical, Open Graph, Twitter & PWA Tags -->
    <?= seo_meta(array_merge($seo ?? [], !empty($title) ? ['title' => $title] : [])) ?>

    <!-- Structured Data JSON-LD Schemas -->
    <?= seo_schema('organization') ?>
    <?= seo_schema('website') ?>
    <?= $this->renderSection('schema') ?>

    <!-- Local Bootstrap 5.3.3 CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Local Bootstrap Icons -->
    <link href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Landing Theme CSS -->
    <link href="<?= base_url('assets/css/landing.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- Global Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-secondary border-opacity-25" style="background-color: #0b1120 !important;">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('/') ?>">
            <div class="navbar-brand-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <span class="fw-bold fs-4 text-white">LenDen</span>
                <span class="badge bg-primary ms-1" style="font-size: 0.65rem; vertical-align: top;">Cloud</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavbar" aria-controls="landingNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="landingNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= (($activeNav ?? '') === 'home') ? 'active' : '' ?>" href="<?= base_url('/') ?>">
                        Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= (($activeNav ?? '') === 'features') ? 'active' : '' ?>" href="<?= base_url('features') ?>">
                        Core Modules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= (($activeNav ?? '') === 'multi-company') ? 'active' : '' ?>" href="<?= base_url('multi-company') ?>">
                        Multi-Company
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= (($activeNav ?? '') === 'pricing') ? 'active' : '' ?>" href="<?= base_url('pricing') ?>">
                        Pricing Plans
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 <?= (($activeNav ?? '') === 'faq') ? 'active' : '' ?>" href="<?= base_url('faq') ?>">
                        FAQ &amp; Help
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('login') ?>" class="btn btn-outline-light btn-sm px-3 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </a>
                <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-sm px-3 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-rocket-takeoff me-1"></i> Start Free Trial
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Page Content Section -->
<main>
    <?= $this->renderSection('content') ?>
</main>

<!-- Global Call to Action Banner (if not hidden) -->
<?php if (empty($hideCta)): ?>
<section class="py-5" style="background: var(--hero-bg); color: #fff;">
    <div class="container text-center py-4">
        <span class="hero-badge mb-3">
            <i class="bi bi-shield-check text-primary"></i> 30-Day Unrestricted Access
        </span>
        <h2 class="display-6 fw-bold mb-3">Ready to Modernize Your Business Accounting?</h2>
        <p class="text-white-50 mx-auto mb-4" style="max-width: 620px;">
            Join forward-thinking businesses and accounting teams using LenDen Cloud to maintain double-entry accuracy, multi-company visibility, and tax compliance.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= base_url('subscribe') ?>" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                <i class="bi bi-rocket-takeoff-fill"></i> Start Your 30-Day Free Trial
            </a>
            <a href="<?= base_url('pricing') ?>" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold">
                Explore Pricing Plans
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Global Marketing Footer -->
<footer class="bg-dark text-white-50 py-5 border-top border-secondary border-opacity-25" style="background-color: #0b1120 !important;">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <div class="col-12 col-md-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="navbar-brand-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <span class="fw-bold fs-4 text-white">LenDen Cloud</span>
                </div>
                <p class="small text-white-50 mb-3" style="max-width: 380px;">
                    Modern cloud double-entry accounting, GST invoicing, inventory control, and multi-company ERP suite designed for fast-moving businesses.
                </p>
                <div class="small text-white-50">
                    &copy; <?= date('Y') ?> LenDen Cloud ERP. All rights reserved.
                </div>
            </div>

            <div class="col-6 col-md-3">
                <h6 class="text-white fw-bold mb-3">Navigation</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= base_url('/') ?>" class="text-white-50 text-decoration-none">Overview</a></li>
                    <li><a href="<?= base_url('features') ?>" class="text-white-50 text-decoration-none">Core Modules</a></li>
                    <li><a href="<?= base_url('multi-company') ?>" class="text-white-50 text-decoration-none">Multi-Company Architecture</a></li>
                    <li><a href="<?= base_url('pricing') ?>" class="text-white-50 text-decoration-none">Pricing Catalog</a></li>
                    <li><a href="<?= base_url('faq') ?>" class="text-white-50 text-decoration-none">FAQ &amp; Support</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3">
                <h6 class="text-white fw-bold mb-3">Account &amp; Access</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= base_url('login') ?>" class="text-white-50 text-decoration-none">Sign In to Workspace</a></li>
                    <li><a href="<?= base_url('subscribe') ?>" class="text-white-50 text-decoration-none">Start 30-Day Free Trial</a></li>
                    <li><a href="<?= base_url('subscriptions') ?>" class="text-white-50 text-decoration-none">Super Admin Portal</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<!-- Local Bootstrap 5.3.3 JS -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/landing.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>

