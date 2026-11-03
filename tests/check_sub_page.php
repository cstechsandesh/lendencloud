<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_');
$ch = curl_init('http://localhost/LenDen/public/index.php/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginPage = curl_exec($ch);
preg_match('/name="csrf_test_name" value="([^"]+)"/', $loginPage, $m);
$csrf = $m[1] ?? '';

curl_setopt($ch, CURLOPT_URL, 'http://localhost/LenDen/public/index.php/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_test_name' => $csrf,
    'username' => 'admin',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, 'http://localhost/LenDen/public/index.php/subscription');
curl_setopt($ch, CURLOPT_HTTPGET, true);
$html = curl_exec($ch);

echo "Contains razorpaySimModal: " . (str_contains($html, 'razorpaySimModal') ? 'YES' : 'NO') . "\n";
echo "Contains simPlanName: " . (str_contains($html, 'simPlanName') ? 'YES' : 'NO') . "\n";
echo "Contains simBillingCycle: " . (str_contains($html, 'simBillingCycle') ? 'YES' : 'NO') . "\n";
echo "Contains simOrderId: " . (str_contains($html, 'simOrderId') ? 'YES' : 'NO') . "\n";
echo "Contains simAmount: " . (str_contains($html, 'simAmount') ? 'YES' : 'NO') . "\n";
echo "Contains simKeyIdDisplay: " . (str_contains($html, 'simKeyIdDisplay') ? 'YES' : 'NO') . "\n";

// Now test calling create-order as the page does
preg_match('/meta name="csrf-token" content="([^"]+)"/', $html, $m2);
$csrfToken = $m2[1] ?? '';
echo "CSRF token from meta: " . ($csrfToken ? 'FOUND' : 'MISSING') . "\n";

curl_setopt($ch, CURLOPT_URL, 'http://localhost/LenDen/public/index.php/payments/razorpay/create-order');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest',
    'X-CSRF-TOKEN: ' . $csrfToken
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'plan_id' => 2,
    'billing_cycle' => 'monthly',
    'csrf_test_name' => $csrfToken
]));
$orderRes = curl_exec($ch);
echo "Create order response:\n" . $orderRes . "\n";

curl_close($ch);
@unlink($cookieFile);

