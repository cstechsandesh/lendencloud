<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'LenDen Cloud') ?> — Cloud Accounting &amp; ERP</title>
    <!-- Safeguard private financial and authenticated ERP records from search indexing -->
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="theme-color" content="#2563eb">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/apple-touch-icon.png') ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="currency-symbol" content="<?= esc($currency_symbol ?? '₹') ?>">
    <meta name="base-url" content="<?= rtrim(base_url(), '/') ?>">

    <!-- Local Bootstrap 5.3.3 CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Local Bootstrap Icons -->
    <link href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <!-- Local DataTables Bootstrap 5 -->
    <link href="<?= base_url('assets/css/dataTables.bootstrap5.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/responsive.bootstrap5.min.css') ?>" rel="stylesheet">
    <!-- Local Select2 Bootstrap 5 -->
    <link href="<?= base_url('assets/css/select2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/select2-bootstrap-5-theme.min.css') ?>" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Application Theme CSS -->
    <link href="<?= base_url('assets/css/app.css?v=' . (file_exists(FCPATH . 'assets/css/app.css') ? filemtime(FCPATH . 'assets/css/app.css') : '2.1')) ?>" rel="stylesheet">
</head>
<body>

<!-- Mobile Sidebar Backdrop -->
<div id="sidebarBackdrop" class="sidebar-backdrop"></div>

<!-- Sidebar Navigation -->
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="sidebar-brand-text">
            <h5><?= esc($company_name ?? 'LenDen') ?></h5>
            <small>Double-Entry Suite</small>
        </div>
    </div>

    <?php
    $uri = service('uri');
    $segments = $uri->getSegments();
    $seg1 = $segments[0] ?? 'dashboard';
    $seg2 = $segments[1] ?? '';

    $isSuperAdmin = in_array('super_admin', $user_roles ?? []);
    $hasPerm = function(string $p) use ($isSuperAdmin, $user_permissions) {
        return $isSuperAdmin || in_array($p, $user_permissions ?? []);
    };

    $canAccessPurchases       = $isSuperAdmin || !empty($plan_features['purchases']);
    $canAccessInventory       = $isSuperAdmin || !empty($plan_features['inventory']);
    $canAccessAdvancedReports = $isSuperAdmin || !empty($plan_features['advanced_reports']);
    ?>

    <div class="sidebar-nav">
        <!-- Main -->
        <div class="nav-section-title">Core Management</div>
        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($seg1 === 'dashboard' || $seg1 === '') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Executive Dashboard</span>
        </a>

        <!-- Accounts & Ledgers -->
        <?php if ($hasPerm('accounts.view')): ?>
            <div class="nav-section-title">Finance & Ledgers</div>
            <a href="<?= base_url('accounts') ?>" class="nav-link <?= ($seg1 === 'accounts' && $seg2 !== 'groups' && $seg2 !== 'journals') ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i>
                <span>Chart of Accounts</span>
            </a>
            <a href="<?= base_url('accounts/groups') ?>" class="nav-link <?= ($seg1 === 'accounts' && $seg2 === 'groups') ? 'active' : '' ?>">
                <i class="bi bi-folder2-open"></i>
                <span>Account Groups</span>
            </a>
            <a href="<?= base_url('journals') ?>" class="nav-link <?= ($seg1 === 'journals' || ($seg1 === 'accounts' && $seg2 === 'journals')) ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Journal Entries</span>
            </a>
        <?php endif; ?>

        <!-- Parties -->
        <?php if ($hasPerm('parties.view')): ?>
            <div class="nav-section-title">Parties & Contacts</div>
            <a href="<?= base_url('parties') ?>" class="nav-link <?= ($seg1 === 'parties') ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>
                <span>Customers & Vendors</span>
            </a>
        <?php endif; ?>

        <!-- Inventory -->
        <?php if ($hasPerm('inventory.view')): ?>
            <div class="nav-section-title d-flex justify-content-between align-items-center">
                <span>Inventory & Stock</span>
                <?php if (!$canAccessInventory): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;" title="Upgrade to unlock"><i class="bi bi-lock-fill"></i> Pro</span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('inventory') ?>" class="nav-link <?= ($seg1 === 'inventory' && empty($seg2)) ? 'active' : '' ?>">
                <i class="bi bi-box-seam-fill"></i>
                <span>Products Catalog</span>
                <?php if (!$canAccessInventory): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
            <a href="<?= base_url('inventory/categories') ?>" class="nav-link <?= ($seg1 === 'inventory' && $seg2 === 'categories') ? 'active' : '' ?>">
                <i class="bi bi-tags-fill"></i>
                <span>Categories</span>
                <?php if (!$canAccessInventory): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
            <a href="<?= base_url('inventory/warehouses') ?>" class="nav-link <?= ($seg1 === 'inventory' && $seg2 === 'warehouses') ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                <span>Warehouses</span>
                <?php if (!$canAccessInventory): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
            <a href="<?= base_url('inventory/adjustments') ?>" class="nav-link <?= ($seg1 === 'inventory' && $seg2 === 'adjustments') ? 'active' : '' ?>">
                <i class="bi bi-sliders"></i>
                <span>Stock Adjustments</span>
                <?php if (!$canAccessInventory): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <!-- Sales -->
        <?php if ($hasPerm('sales.view')): ?>
            <div class="nav-section-title">Sales & Billing</div>
            <a href="<?= base_url('sales/orders') ?>" class="nav-link <?= ($seg1 === 'sales' && $seg2 === 'orders') ? 'active' : '' ?>">
                <i class="bi bi-cart-check-fill"></i>
                <span>Sales Orders</span>
            </a>
            <a href="<?= base_url('sales/invoices') ?>" class="nav-link <?= ($seg1 === 'sales' && $seg2 === 'invoices') ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i>
                <span>Tax Invoices</span>
            </a>
        <?php endif; ?>

        <!-- Purchases -->
        <?php if ($hasPerm('purchases.view')): ?>
            <div class="nav-section-title d-flex justify-content-between align-items-center">
                <span>Purchases & Bills</span>
                <?php if (!$canAccessPurchases): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;" title="Upgrade to unlock"><i class="bi bi-lock-fill"></i> Pro</span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('purchases/orders') ?>" class="nav-link <?= ($seg1 === 'purchases' && $seg2 === 'orders') ? 'active' : '' ?>">
                <i class="bi bi-bag-check-fill"></i>
                <span>Purchase Orders</span>
                <?php if (!$canAccessPurchases): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
            <a href="<?= base_url('purchases/bills') ?>" class="nav-link <?= ($seg1 === 'purchases' && $seg2 === 'bills') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text-fill"></i>
                <span>Vendor Bills</span>
                <?php if (!$canAccessPurchases): ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

                <!-- Vouchers -->
        <?php 
        $canReceipts = $isSuperAdmin || ($hasPerm('vouchers.view') && ($hasPerm('sales.view') || $hasPerm('accounts.view')));
        $canPayments = $isSuperAdmin || ($hasPerm('vouchers.view') && ($hasPerm('purchases.view') || $hasPerm('accounts.view')));
        $canContra   = $isSuperAdmin || $hasPerm('vouchers.contra') || ($hasPerm('vouchers.view') && $hasPerm('accounts.view'));
        ?>
        <?php if ($canReceipts || $canPayments || $canContra): ?>
            <div class="nav-section-title">Cash & Bank Vouchers</div>
            <?php if ($canReceipts): ?>
                <a href="<?= base_url('vouchers/receipts') ?>" class="nav-link <?= ($seg1 === 'vouchers' && $seg2 === 'receipts') ? 'active' : '' ?>">
                    <i class="bi bi-arrow-down-left-circle-fill text-success"></i>
                    <span>Customer Receipts</span>
                </a>
            <?php endif; ?>
            <?php if ($canPayments): ?>
                <a href="<?= base_url('vouchers/payments') ?>" class="nav-link <?= ($seg1 === 'vouchers' && $seg2 === 'payments') ? 'active' : '' ?>">
                    <i class="bi bi-arrow-up-right-circle-fill text-danger"></i>
                    <span>Vendor Payments</span>
                </a>
            <?php endif; ?>
            <?php if ($canContra): ?>
                <a href="<?= base_url('vouchers/contra') ?>" class="nav-link <?= ($seg1 === 'vouchers' && $seg2 === 'contra') ? 'active' : '' ?>">
                    <i class="bi bi-arrow-left-right text-info"></i>
                    <span>Bank/Cash Contra</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Reports -->
        <?php 
        $canFinancials = $isSuperAdmin || ($hasPerm('reports.view') && $hasPerm('accounts.view'));
        $canStockRep   = $isSuperAdmin || ($hasPerm('reports.view') && $hasPerm('inventory.view'));
        $canDayBook    = $isSuperAdmin || ($hasPerm('reports.view') && $hasPerm('journals.view'));
        ?>
        <?php if ($canFinancials || $canStockRep || $canDayBook): ?>
            <div class="nav-section-title">Reports & Statements</div>
            <?php if ($canFinancials): ?>
                <a href="<?= base_url('reports/trial-balance') ?>" class="nav-link <?= ($seg1 === 'reports' && $seg2 === 'trial-balance') ? 'active' : '' ?>">
                    <i class="bi bi-calculator"></i>
                    <span>Trial Balance</span>
                    <?php if (!$canAccessAdvancedReports): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                    <?php endif; ?>
                </a>
                <a href="<?= base_url('reports/profit-loss') ?>" class="nav-link <?= ($seg1 === 'reports' && $seg2 === 'profit-loss') ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Profit & Loss (P&L)</span>
                    <?php if (!$canAccessAdvancedReports): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                    <?php endif; ?>
                </a>
                <a href="<?= base_url('reports/balance-sheet') ?>" class="nav-link <?= ($seg1 === 'reports' && $seg2 === 'balance-sheet') ? 'active' : '' ?>">
                    <i class="bi bi-bank"></i>
                    <span>Balance Sheet</span>
                    <?php if (!$canAccessAdvancedReports): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if ($canStockRep): ?>
                <a href="<?= base_url('reports/stock') ?>" class="nav-link <?= ($seg1 === 'reports' && $seg2 === 'stock') ? 'active' : '' ?>">
                    <i class="bi bi-boxes"></i>
                    <span>Stock Valuation</span>
                    <?php if (!$canAccessInventory): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto" style="font-size: 0.6rem;"><i class="bi bi-lock-fill"></i></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if ($canDayBook): ?>
                <a href="<?= base_url('reports/day-book') ?>" class="nav-link <?= ($seg1 === 'reports' && $seg2 === 'day-book') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Day Book</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- RBAC Administration -->
        <?php if ($isSuperAdmin || $hasPerm('companies.view') || $hasPerm('users.view')): ?>
            <div class="nav-section-title">Administration & Multi-Company</div>
            <?php if ($isSuperAdmin): ?>
                <a href="<?= base_url('subscriptions') ?>" class="nav-link <?= ($seg1 === 'subscriptions') ? 'active' : '' ?>">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                    <span>Subscriptions & Plans</span>
                </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin || $hasPerm('companies.view')): ?>
                <a href="<?= base_url('companies') ?>" class="nav-link <?= ($seg1 === 'companies') ? 'active' : '' ?>">
                    <i class="bi bi-buildings-fill"></i>
                    <span>Companies & Branches</span>
                </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin || $hasPerm('users.view')): ?>
                <a href="<?= base_url('users') ?>" class="nav-link <?= ($seg1 === 'users') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Users & Access</span>
                </a>
            <?php endif; ?>
            <?php if ($isSuperAdmin): ?>
                <a href="<?= base_url('roles') ?>" class="nav-link <?= ($seg1 === 'roles') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Roles & Permissions</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Settings -->
        <?php if ($hasPerm('settings.view')): ?>
            <div class="nav-section-title">System Settings</div>
            <a href="<?= base_url('settings') ?>" class="nav-link <?= ($seg1 === 'settings' && $seg2 !== 'tax-rates') ? 'active' : '' ?>">
                <i class="bi bi-gear-fill"></i>
                <span>Organization Settings</span>
            </a>
            <a href="<?= base_url('settings/tax-rates') ?>" class="nav-link <?= ($seg1 === 'settings' && $seg2 === 'tax-rates') ? 'active' : '' ?>">
                <i class="bi bi-percent"></i>
                <span>GST Tax Rates</span>
            </a>
            <a href="<?= base_url('subscription') ?>" class="nav-link <?= ($seg1 === 'subscription') ? 'active' : '' ?>">
                <i class="bi bi-patch-check-fill"></i>
                <span>Company Plan &amp; Billing</span>
            </a>
        <?php endif; ?>
    </div>
</aside>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light border" id="sidebarToggle" type="button" aria-label="Toggle sidebar navigation" title="Toggle Navigation Sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-house-door me-1"></i>LenDen
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($title ?? 'Overview') ?></li>
                </ol>
            </nav>
            <?php if (!empty($is_demo_user) && empty($is_super_admin)): ?>
                <span class="badge d-inline-flex align-items-center gap-1" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px; padding: 4px 9px; font-weight: 700;">
                    <i class="bi bi-exclamation-triangle"></i> Demo Login Mode
                </span>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <!-- Company Switcher Dropdown -->
            <?php if (!empty($hasCompanySwitcher)): ?>
                <div class="dropdown">
                    <button class="company-switcher-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Switch active company workspace context">
                        <span class="status-pulse-dot"></span>
                        <i class="bi bi-buildings-fill text-primary"></i>
                        <span class="d-none d-md-inline text-truncate" style="max-width: 190px;"><?= esc($currentCompany['company_name'] ?? 'Select Company') ?></span>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 0.72rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 290px; max-height: 400px; overflow-y: auto;">
                        <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center rounded-top">
                            <small class="fw-bold text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Switch Active Company</small>
                            <?php if ($isSuperAdmin || $hasPerm('companies.view')): ?>
                                <a href="<?= base_url('companies') ?>" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.75rem;">Manage All &rarr;</a>
                            <?php endif; ?>
                        </li>
                        <?php if (!empty($allCompanies)): ?>
                            <?php foreach ($allCompanies as $comp): ?>
                                <?php $isActive = ((int)$comp['company_id'] === (int)$company_id); ?>
                                <li>
                                    <a class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between <?= $isActive ? 'bg-primary-subtle active-company-item' : '' ?>" href="<?= base_url('companies/switch/' . $comp['company_id']) ?>">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <i class="bi bi-building fs-6 <?= $isActive ? 'text-primary' : 'text-secondary' ?>"></i>
                                            <div class="text-truncate">
                                                <div class="fw-semibold text-truncate small" style="max-width: 180px;"><?= esc($comp['company_name']) ?></div>
                                                <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                    Code: <?= esc($comp['company_code'] ?: 'N/A') ?> &bull; <?= esc($comp['company_city'] ?? 'Mumbai') ?>
                                                </small>
                                            </div>
                                        </div>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-primary rounded-pill small ms-2" style="font-size: 0.65rem;">Active</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($isSuperAdmin || $hasPerm('companies.create')): ?>
                            <li class="border-top p-2 bg-light rounded-bottom text-center">
                                <a href="<?= base_url('companies') ?>" class="btn btn-sm btn-outline-primary w-100 fw-semibold" style="font-size: 0.78rem;">
                                    <i class="bi bi-plus-circle me-1"></i> Register New Company Entity
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Single Company Display Badge -->
                <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 bg-light border rounded-pill">
                    <i class="bi bi-buildings-fill text-primary"></i>
                    <small class="fw-semibold text-dark text-truncate" style="max-width: 170px;"><?= esc($currentCompany['company_name'] ?? 'LenDen') ?></small>
                </div>
            <?php endif; ?>

            <!-- User Menu Dropdown -->
            <div class="dropdown">
                <button class="user-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar-circle">
                        <?= strtoupper(substr($user_name ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="fw-semibold text-dark small" style="line-height: 1.2;"><?= esc($user_name ?? 'Administrator') ?></div>
                        <small class="text-muted text-capitalize" style="font-size: 0.72rem;"><?= esc($user_role ?? 'Super Admin') ?></small>
                    </div>
                    <i class="bi bi-chevron-down text-muted" style="font-size: 0.75rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 220px;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold text-dark"><?= esc($user_name ?? 'Administrator') ?></div>
                        <small class="text-muted d-block text-truncate"><?= esc(session()->get('user_email') ?? 'admin@lenden.local') ?></small>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="<?= base_url('profile') ?>">
                            <i class="bi bi-person me-2 text-muted"></i>My Account Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="<?= base_url('subscription') ?>">
                            <i class="bi bi-patch-check me-2 text-primary"></i>Company Plan &amp; Billing
                        </a>
                    </li>
                    <?php if ($hasPerm('settings.view')): ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('settings') ?>">
                                <i class="bi bi-gear me-2 text-muted"></i>System Settings
                            </a>
                        </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 text-danger" href="<?= base_url('logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="content-area main-content">
        <?php if (!empty($is_demo_user) && empty($is_super_admin)): ?>
            <div class="alert alert-warning border border-warning-subtle shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3" style="background-color: #fffbeb; color: #92400e; border-radius: 8px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle fs-5 text-warning"></i>
                    <div class="small">
                        <strong>Demo Login Mode:</strong> You are browsing with a demonstration account. Data changes (add, edit, delete) are disabled and cannot be saved.
                    </div>
                </div>
                <span class="badge bg-warning text-dark px-2 py-1 fw-bold" style="font-size: 0.72rem;">Read-Only Demo</span>
            </div>
        <?php endif; ?>
        <?= $this->include('partials/flash_messages') ?>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- App Footer -->
    <footer class="app-footer text-muted small border-top bg-white px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            &copy; <?= date('Y') ?> <strong><?= esc($company_name ?? 'LenDen Enterprise') ?></strong> &bull; Double-Entry Accounting &amp; Business Management ERP
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>Fiscal Year: <?= date('Y') ?>-<?= date('y') + 1 ?></span>
            <span class="text-muted">&bull;</span>
            <a href="<?= base_url('subscription') ?>" class="text-decoration-none text-muted">Plan: <?= esc($current_subscription['plan_name'] ?? 'Active') ?></a>
        </div>
    </footer>
</div>

<!-- Local Vendor & Application Scripts -->
<script src="<?= base_url('assets/js/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/js/responsive.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/js/select2.min.js') ?>"></script>
<script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/modal-forms.js') ?>"></script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
