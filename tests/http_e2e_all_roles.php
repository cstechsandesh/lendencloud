<?php

$baseUrl = 'http://localhost/LenDen/public/index.php';

$roles = [
    'super_admin'       => ['username' => 'admin',         'password' => 'admin123'],
    'admin'             => ['username' => 'admin_demo',    'password' => 'admin123'],
    'accountant'        => ['username' => 'accountant',    'password' => 'accountant123'],
    'sales_manager'     => ['username' => 'sales',         'password' => 'sales123'],
    'purchase_manager'  => ['username' => 'purchase',      'password' => 'purchase123'],
    'inventory_manager' => ['username' => 'inventory',     'password' => 'inventory123'],
    'viewer'            => ['username' => 'viewer',        'password' => 'viewer123'],
];

$urlsToTest = [
    '/dashboard',
    '/profile',
    '/accounts',
    '/accounts/create',
    '/accounts/groups',
    '/journals',
    '/parties',
    '/parties/create',
    '/inventory/products',
    '/inventory/categories',
    '/inventory/warehouses',
    '/inventory/adjustments',
    '/sales/orders',
    '/sales/invoices',
    '/purchases/orders',
    '/purchases/bills',
    '/vouchers/receipts',
    '/vouchers/payments',
    '/vouchers/contra',
    '/reports/trial-balance',
    '/reports/profit-loss',
    '/reports/balance-sheet',
    '/reports/stock',
    '/reports/day-book',
    '/companies',
    '/users',
    '/roles',
    '/subscriptions',
    '/subscription',
    '/settings',
    '/settings/tax-rates',
];

echo "================================================================================" . PHP_EOL;
echo "  HTTP E2E MULTI-ROLE REAL BROWSER SIMULATION" . PHP_EOL;
echo "================================================================================" . PHP_EOL;

$cookieJar = tempnam(sys_get_temp_dir(), 'cookie_');

foreach ($roles as $roleKey => $cred) {
    echo PHP_EOL . ">>> HTTP TESTING FOR ROLE: [{$roleKey}] (user: {$cred['username']}) <<<" . PHP_EOL;
    @unlink($cookieJar);

    // 1. Get CSRF from login page
    $ch = curl_init("{$baseUrl}/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $html = curl_exec($ch);
    curl_close($ch);

    // Extract csrf token if present
    preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $html, $m1);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m2);
    $csrfToken = $m1[1] ?? ($m2[1] ?? '');

    // 2. Perform Login POST
    $postFields = [
        'username' => $cred['username'],
        'password' => $cred['password'],
    ];
    if ($csrfToken) {
        $postFields['csrf_test_name'] = $csrfToken;
    }

    $ch = curl_init("{$baseUrl}/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $loginRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  Login response HTTP {$httpCode}" . PHP_EOL;
    if (str_contains($loginRes, 'Invalid username') || str_contains($loginRes, 'Login failed')) {
        echo "  [ERROR] Login failed for {$cred['username']}!" . PHP_EOL;
        continue;
    }

    // 3. Test URLs
    foreach ($urlsToTest as $path) {
        $ch = curl_init("{$baseUrl}{$path}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $hasException = str_contains($content, 'An uncaught Exception was encountered')
                     || str_contains($content, 'Fatal error')
                     || str_contains($content, 'CodeIgniter\Exceptions')
                     || str_contains($content, 'DatabaseException');

        if ($hasException) {
            echo "  [FAIL] {$path} -> CRITICAL EXCEPTION IN BROWSER! HTTP {$code}" . PHP_EOL;
            // extract error message from html
            preg_match('/<p class="error">([^<]+)<\/p>/', $content, $errMatch);
            if (!empty($errMatch[1])) echo "         Reason: {$errMatch[1]}" . PHP_EOL;
        } elseif ($code >= 500) {
            echo "  [FAIL] {$path} -> HTTP {$code} SERVER ERROR" . PHP_EOL;
        } else {
            echo "  [PASS] {$path} -> HTTP {$code} (" . strlen($content) . " bytes)" . PHP_EOL;
        }
    }
}

@unlink($cookieJar);
echo "================================================================================" . PHP_EOL;
echo "  HTTP E2E AUDIT COMPLETE" . PHP_EOL;
echo "================================================================================" . PHP_EOL;
