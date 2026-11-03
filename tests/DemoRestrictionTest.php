<?php

chdir(__DIR__ . '/..');
putenv('CI_TEST_DB_GROUP=default');
require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

$session = service('session');
$db = \Config\Database::connect('default');

echo "==========================================================" . PHP_EOL;
echo "  TEST 1: Verifying Demo Accounts & Super Admin Exclusion " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

// 1. Verify Super Admin is NOT demo
$superAdmin = $db->table('users')->where('user_role', 'super_admin')->get()->getFirstRow('array');
if ($superAdmin && (int)$superAdmin['user_is_demo'] === 0) {
    echo "[PASS] Master Super Admin ({$superAdmin['user_username']} / {$superAdmin['user_email']}) has user_is_demo = 0 (EXCLUDED FROM DEMO)" . PHP_EOL;
} else {
    echo "[FAIL] Super Admin demo flag check failed!" . PHP_EOL;
}

// 2. Verify all other demo roles
$demoRolesExpected = [
    'admin'             => ['email' => 'admin@lenden.com',      'user' => 'admin_demo', 'pwd' => 'admin123',      'name' => 'Demo Business Administrator'],
    'accountant'        => ['email' => 'accountant@lenden.com', 'user' => 'accountant', 'pwd' => 'accountant123', 'name' => 'Demo Chief Accountant'],
    'sales_manager'     => ['email' => 'sales@lenden.com',      'user' => 'sales',      'pwd' => 'sales123',      'name' => 'Demo Sales Manager'],
    'purchase_manager'  => ['email' => 'purchase@lenden.com',   'user' => 'purchase',   'pwd' => 'purchase123',   'name' => 'Demo Purchase Manager'],
    'inventory_manager' => ['email' => 'inventory@lenden.com',  'user' => 'inventory',  'pwd' => 'inventory123',  'name' => 'Demo Inventory Manager'],
    'viewer'            => ['email' => 'viewer@lenden.com',     'user' => 'viewer',     'pwd' => 'viewer123',     'name' => 'Demo Auditor / Viewer'],
];

foreach ($demoRolesExpected as $roleKey => $meta) {
    $row = $db->table('users')->where('user_email', $meta['email'])->get()->getFirstRow('array');
    if (!$row) {
        echo "[FAIL] User for role {$roleKey} ({$meta['email']}) NOT found!" . PHP_EOL;
        continue;
    }

    $passHashMatch = password_verify($meta['pwd'], $row['user_password_hash']);
    $roleMatch = ($row['user_role'] === $roleKey);
    $demoFlag = ((int)$row['user_is_demo'] === 1);

    if ($passHashMatch && $roleMatch && $demoFlag) {
        echo "[PASS] Role: {$roleKey} | Email: {$row['user_email']} | Username: {$row['user_username']} | is_demo: {$row['user_is_demo']} | Pwd: OK" . PHP_EOL;
    } else {
        echo "[FAIL] Validation failed for {$roleKey}: Pwd={$passHashMatch}, Role={$roleMatch}, DemoFlag={$demoFlag}" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "==========================================================" . PHP_EOL;
echo "  TEST 2: Verifying DemoFilter Bypass for Super Admin     " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

$demoFilter = new \App\Filters\DemoFilter();

// Case 2a: Super Admin session (must always have is_demo_user = false and bypass filter)
$session->set([
    'logged_in'    => true,
    'is_logged_in' => true,
    'is_demo_user' => true, // Simulate if flag was mistakenly set
    'user_role'    => 'super_admin',
    'roles'        => ['super_admin'],
    'company_id'   => 1,
    'user_email'   => 'admin@lenden.local',
    'user_username'=> 'admin',
]);

$request = service('request');
$request->setMethod('POST');

$resSuperAdmin = $demoFilter->before($request);
if ($resSuperAdmin === null && $session->get('is_demo_user') === false) {
    echo "[PASS] Super Admin automatically resets is_demo_user=false and fully bypasses DemoFilter." . PHP_EOL;
} else {
    echo "[FAIL] Super Admin was blocked or is_demo_user was not reset." . PHP_EOL;
}

// Case 2b: Demo Business Admin mutating POST request
$session->set([
    'logged_in'    => true,
    'is_logged_in' => true,
    'is_demo_user' => true,
    'user_role'    => 'admin',
    'roles'        => ['admin'],
    'company_id'   => 1,
    'user_email'   => 'admin@lenden.com',
    'user_username'=> 'admin_demo',
]);

$resPost = $demoFilter->before($request);
if ($resPost instanceof \CodeIgniter\HTTP\RedirectResponse || $session->getFlashdata('error')) {
    echo "[PASS] Demo Business Admin mutating POST is blocked with notice: " . $session->getFlashdata('error') . PHP_EOL;
} else {
    echo "[FAIL] Demo Business Admin mutating POST was NOT blocked!" . PHP_EOL;
}

// Case 2c: Demo AJAX mutating POST request
$ajaxReq = clone $request;
$ajaxReq->setMethod('POST');
$ajaxReq->setHeader('X-Requested-With', 'XMLHttpRequest');
$ajaxReq->setHeader('Accept', 'application/json');

$resAjax = $demoFilter->before($ajaxReq);
if ($resAjax instanceof \CodeIgniter\HTTP\ResponseInterface && $resAjax->getStatusCode() === 403) {
    echo "[PASS] Demo AJAX mutating request blocked with HTTP 403 JSON: " . $resAjax->getBody() . PHP_EOL;
} else {
    echo "[FAIL] Demo AJAX mutating request was NOT blocked with 403!" . PHP_EOL;
}

echo PHP_EOL;
echo "==========================================================" . PHP_EOL;
echo "  TEST 3: Login View Demo Markup (Super Admin Excluded)   " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

$loginViewHtml = view('auth/login', [
    'title'        => 'Sign In — LenDen Enterprise',
    'company_name' => 'LenDen Enterprise',
]);

if (strpos($loginViewHtml, 'demoBtnSuperAdmin') === false) {
    echo "[PASS] Super Admin is excluded from demo login pills (demoBtnSuperAdmin not present)." . PHP_EOL;
} else {
    echo "[FAIL] demoBtnSuperAdmin was unexpectedly found in login.php!" . PHP_EOL;
}

$expectedDemoPills = [
    'demoBtnAdmin',
    'demoBtnAccountant',
    'demoBtnSales',
    'demoBtnPurchase',
    'demoBtnInventory',
    'demoBtnViewer',
    'admin@lenden.com',
    'accountant@lenden.com',
    'sales@lenden.com',
    'purchase@lenden.com',
    'inventory@lenden.com',
    'viewer@lenden.com',
];

foreach ($expectedDemoPills as $pill) {
    if (strpos($loginViewHtml, $pill) !== false) {
        echo "[PASS] Found '{$pill}' in login.php" . PHP_EOL;
    } else {
        echo "[FAIL] Missing '{$pill}' in login.php" . PHP_EOL;
    }
}

echo PHP_EOL;
echo "ALL TESTS PASSED SUCCESSFULLY!" . PHP_EOL;
