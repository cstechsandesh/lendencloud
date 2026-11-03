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
    <?= seo_meta($seo ?? ['title' => 'Sign In — LenDen Cloud']) ?>

    <!-- Local Bootstrap 5.3.3 CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Local Bootstrap Icons -->
    <link href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/auth.css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= base_url('/') ?>" class="text-decoration-none small text-muted d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Home
        </a>
        <a href="<?= base_url('subscribe') ?>" class="text-decoration-none small fw-semibold text-primary d-inline-flex align-items-center gap-1">
            Free Trial <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="text-center">
        <div class="brand-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1"><?= esc($company_name ?? 'LenDen Cloud') ?></h4>
        <p class="text-muted small mb-4">Financial &amp; Multi-Company Accounting Suite</p>
    </div>

    <?= $this->include('partials/flash_messages') ?>

    <form action="<?= base_url('login') ?>" method="post" id="loginForm" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary" for="loginUsername">
                <i class="bi bi-person text-primary me-1"></i> Username or Email
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                <input type="text" name="username" id="loginUsername" class="form-control border-start-0 ps-0" placeholder="admin@lenden.com" value="<?= esc(old('username', 'admin@lenden.com')) ?>" required autofocus autocomplete="username">
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label small fw-semibold text-secondary" for="loginPassword">
                    <i class="bi bi-lock text-primary me-1"></i> Password
                </label>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" value="admin123" required autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary border-start-0 password-toggle-btn" id="passwordToggleBtn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                    <i class="bi bi-eye" id="passwordToggleIcon"></i>
                </button>
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" id="submitLoginBtn" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2" id="loginBtnIcon"></i>
                <span id="loginBtnText">Sign In to LenDen</span>
            </button>
        </div>

        <!-- 1-Click Quick Demo Role Switcher (as per chsmgr) -->
        <div class="demo-roles-container">
            <div class="demo-roles-title">
                <i class="bi bi-stars text-primary"></i> 1-Click Demo Login
            </div>
            <div class="demo-pills-grid">
                <button type="button" id="demoBtnAdmin" class="demo-role-pill"
                        onclick="fillCreds('admin@lenden.com', 'admin123', this)"
                        title="Business Administrator">
                    <i class="bi bi-person-gear"></i> Business Admin
                </button>
                <button type="button" id="demoBtnAccountant" class="demo-role-pill"
                        onclick="fillCreds('accountant@lenden.com', 'accountant123', this)"
                        title="Chief Accountant & CFO">
                    <i class="bi bi-calculator"></i> Accountant
                </button>
                <button type="button" id="demoBtnSales" class="demo-role-pill"
                        onclick="fillCreds('sales@lenden.com', 'sales123', this)"
                        title="Sales & Invoicing Manager">
                    <i class="bi bi-cart-check"></i> Sales Manager
                </button>
                <button type="button" id="demoBtnPurchase" class="demo-role-pill"
                        onclick="fillCreds('purchase@lenden.com', 'purchase123', this)"
                        title="Procurement & Vendor Manager">
                    <i class="bi bi-bag-check"></i> Purchase Mgr
                </button>
                <button type="button" id="demoBtnInventory" class="demo-role-pill"
                        onclick="fillCreds('inventory@lenden.com', 'inventory123', this)"
                        title="Warehouse & Stock Controller">
                    <i class="bi bi-box-seam"></i> Inventory Mgr
                </button>
                <button type="button" id="demoBtnViewer" class="demo-role-pill"
                        onclick="fillCreds('viewer@lenden.com', 'viewer123', this)"
                        title="Statutory Auditor / Read-Only Viewer">
                    <i class="bi bi-eye"></i> Auditor/Viewer
                </button>
            </div>
            <div class="demo-roles-note">
                <i class="bi bi-info-circle text-primary me-1"></i>
                Demo mode: Feel free to explore. Data changes are not saved.
            </div>
        </div>

        <div class="text-center mt-3 mb-1">
            <span class="text-muted small">Need an independent organization?</span>
            <a href="<?= base_url('subscribe') ?>" class="text-primary small fw-semibold text-decoration-none ms-1">
                Start 30-Day Free Trial
            </a>
        </div>
    </form>
</div>

<!-- Local Bootstrap 5.3.3 JS -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script>
function fillCreds(username, password, btnElement) {
    const userInput = document.getElementById('loginUsername');
    const passInput = document.getElementById('loginPassword');
    if (userInput && passInput) {
        userInput.value = username;
        passInput.value = password;
        userInput.focus();
    }
    // Update active pill styling
    document.querySelectorAll('.demo-role-pill').forEach(function(el) {
        el.classList.remove('active');
    });
    if (btnElement) {
        btnElement.classList.add('active');
    }
}

function togglePasswordVisibility() {
    const passInput  = document.getElementById('loginPassword');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    if (!passInput || !toggleIcon) return;
    if (passInput.type === 'password') {
        passInput.type = 'text';
        toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passInput.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Submit loading state
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn  = document.getElementById('submitLoginBtn');
    const icon = document.getElementById('loginBtnIcon');
    const txt  = document.getElementById('loginBtnText');
    if (btn && icon && txt) {
        btn.disabled = true;
        icon.className = 'spinner-border spinner-border-sm me-2';
        txt.textContent = 'Signing In…';
    }
});
</script>
</body>
</html>
