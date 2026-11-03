<?php

chdir(__DIR__ . '/../..');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Ensure helpers are loaded
helper(['url', 'form', 'cs', 'seo']);

$session = service('session');

echo "==========================================================" . PHP_EOL;
echo "  TEST SUITE: Google Analytics & Super Admin Exclusion   " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}" . PHP_EOL;
    } else {
        $failed++;
        echo "  [FAIL] {$description}" . ($details ? " - {$details}" : '') . PHP_EOL;
    }
}

// -------------------------------------------------------------------------
// 1. Config Object Tests
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 1. Analytics Config Tests ---" . PHP_EOL;
$analyticsConfig = new \Config\Analytics();
assertTest(is_object($analyticsConfig), 'Analytics config instantiates properly');
assertTest(property_exists($analyticsConfig, 'googleAnalyticsId'), 'googleAnalyticsId property exists');
assertTest(property_exists($analyticsConfig, 'enabled'), 'enabled property exists');
assertTest(property_exists($analyticsConfig, 'excludeSuperAdmin'), 'excludeSuperAdmin property exists');
assertTest($analyticsConfig->excludeSuperAdmin === true, 'excludeSuperAdmin defaults to true');
assertTest(is_string($analyticsConfig->googleAnalyticsId), 'googleAnalyticsId is a string loaded from environment');

// -------------------------------------------------------------------------
// 2. is_super_admin() Helper Tests
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 2. is_super_admin() Detection Tests ---" . PHP_EOL;

// 2a. Guest user (no session)
$session->destroy();
$_SESSION = [];
assertTest(is_super_admin() === false, 'Guest / Anonymous visitor is NOT super admin');

// 2b. Demo user
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => true,
    'user_role'    => 'admin',
    'roles'        => ['admin'],
];
assertTest(is_super_admin() === false, 'Demo user (is_demo_user = true) is NOT super admin');

// 2c. Regular tenant admin user
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => false,
    'user_role'    => 'admin',
    'roles'        => ['admin'],
];
assertTest(is_super_admin() === false, 'Regular tenant Admin (user_role = admin) is NOT super admin');

// 2d. Regular staff / accountant
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => false,
    'user_role'    => 'accountant',
    'roles'        => ['accountant'],
];
assertTest(is_super_admin() === false, 'Accountant user is NOT super admin');

// 2e. Genuine Super Admin via user_role
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => false,
    'user_role'    => 'super_admin',
    'roles'        => ['super_admin'],
];
assertTest(is_super_admin() === true, 'Super Admin via user_role = super_admin is detected');

// 2f. Super Admin via roles array
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => false,
    'user_role'    => 'admin',
    'roles'        => ['super_admin'],
];
assertTest(is_super_admin() === true, 'Super Admin via roles array containing super_admin is detected');

// 2g. Super Admin via is_super_admin boolean flag
$_SESSION = [
    'logged_in'      => true,
    'is_demo_user'   => false,
    'is_super_admin' => true,
];
assertTest(is_super_admin() === true, 'Super Admin via is_super_admin flag is detected');

// 2h. Super Admin via role_id = 1
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => false,
    'role_id'      => '1',
];
assertTest(is_super_admin() === true, 'Super Admin via role_id = 1 is detected');

// -------------------------------------------------------------------------
// 3. google_analytics() Helper Tests
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 3. google_analytics() Rendering Tests ---" . PHP_EOL;

$testMeasurementId = 'G-TEST999999';

// 3a. When ID is empty
google_analytics(['reset' => true]);
$output = google_analytics(['id' => '']);
assertTest($output === '', 'Empty measurement ID outputs empty string');

// 3b. Guest user with ID
google_analytics(['reset' => true]);
$_SESSION = [];
$output = google_analytics(['id' => $testMeasurementId, 'force' => true]);
assertTest(str_contains($output, 'googletagmanager.com/gtag/js?id=G-TEST999999'), 'Guest receives GA gtag.js library script');
assertTest(str_contains($output, "gtag('config', 'G-TEST999999')"), 'Guest receives gtag config initialization');

// 3c. Regular tenant user with ID
google_analytics(['reset' => true]);
$_SESSION = [
    'logged_in' => true,
    'user_role' => 'admin',
    'roles'     => ['admin'],
];
$output = google_analytics(['id' => $testMeasurementId, 'force' => true]);
assertTest(str_contains($output, 'googletagmanager.com/gtag/js?id=G-TEST999999'), 'Tenant Admin user receives GA script');

// 3d. Demo user with ID
google_analytics(['reset' => true]);
$_SESSION = [
    'logged_in'    => true,
    'is_demo_user' => true,
    'user_role'    => 'admin',
    'roles'        => ['admin'],
];
$output = google_analytics(['id' => $testMeasurementId, 'force' => true]);
assertTest(str_contains($output, 'googletagmanager.com/gtag/js?id=G-TEST999999'), 'Demo explorer user receives GA script');

// 3e. Super Admin with ID -> MUST BE EXCLUDED
google_analytics(['reset' => true]);
$_SESSION = [
    'logged_in' => true,
    'user_role' => 'super_admin',
    'roles'     => ['super_admin'],
];
$output = google_analytics(['id' => $testMeasurementId, 'force' => true]);
assertTest(!str_contains($output, 'googletagmanager.com/gtag/js'), 'Super Admin does NOT receive GA library script');
assertTest(!str_contains($output, "gtag('config'"), 'Super Admin does NOT receive GA config initialization');
assertTest(str_contains($output, 'excluded for Super Administrator'), 'Super Admin receives exclusion notice comment');

// 3f. Super Admin with exclude_super_admin disabled -> receives GA script
google_analytics(['reset' => true]);
$_SESSION = [
    'logged_in' => true,
    'user_role' => 'super_admin',
    'roles'     => ['super_admin'],
];
$output = google_analytics(['id' => $testMeasurementId, 'exclude_super_admin' => false, 'force' => true]);
assertTest(str_contains($output, 'googletagmanager.com/gtag/js?id=G-TEST999999'), 'Super Admin receives script when exclude_super_admin is disabled');

// 3g. Duplicate suppression on the same page
google_analytics(['reset' => true]);
$_SESSION = [];
$first = google_analytics(['id' => $testMeasurementId]);
$second = google_analytics(['id' => $testMeasurementId]);
assertTest(!empty($first), 'First call on page renders GA script');
assertTest(empty($second), 'Second call on same page is suppressed to prevent duplicate tracking');

// -------------------------------------------------------------------------
// 4. View Files Integration Tests
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 4. View Layout Integration Tests ---" . PHP_EOL;

$viewFiles = [
    'app/Views/layouts/main.php',
    'app/Views/landing/layout.php',
    'app/Views/auth/login.php',
    'app/Views/auth/subscribe.php',
];

foreach ($viewFiles as $vf) {
    $content = file_get_contents($vf);
    assertTest(str_contains($content, 'google_analytics()'), "View {$vf} invokes google_analytics() in <head>");
}

// -------------------------------------------------------------------------
// 5. Summary
// -------------------------------------------------------------------------
echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "  SUMMARY: {$passed} Passed, {$failed} Failed" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

exit($failed > 0 ? 1 : 0);
