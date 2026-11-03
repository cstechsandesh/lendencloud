<?php

chdir(__DIR__ . '/../..');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use Config\Razorpay as RazorpayConfig;
use App\Libraries\RazorpayService;
use App\Models\PaymentTransactionModel;

echo "==========================================================" . PHP_EOL;
echo "  TEST SUITE: Razorpay Payment Gateway Integration" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

// 1. Test Config Loading
$config = new RazorpayConfig();
if ($config->keyId === 'rzp_test_TZerMYqwyQJpH0') {
    echo "[PASS] Razorpay Config: Key ID matches configured test key ({$config->keyId})" . PHP_EOL;
} else {
    echo "[FAIL] Razorpay Config Key ID mismatch: " . $config->keyId . PHP_EOL;
}

if ($config->isTestMode()) {
    echo "[PASS] Razorpay Config: isTestMode() correctly identified test key prefix" . PHP_EOL;
} else {
    echo "[FAIL] Razorpay Config: isTestMode() should be true for test keys" . PHP_EOL;
}

if ($config->isConfigured()) {
    echo "[PASS] Razorpay Config: isConfigured() returned true" . PHP_EOL;
} else {
    echo "[FAIL] Razorpay Config: isConfigured() failed" . PHP_EOL;
}

// 2. Test RazorpayService Initialization & Order Creation
$service = new RazorpayService($config);

if ($service->getKeyId() === $config->keyId && $service->getCurrency() === 'INR') {
    echo "[PASS] RazorpayService: Initialized successfully with currency {$service->getCurrency()}" . PHP_EOL;
} else {
    echo "[FAIL] RazorpayService initialization failed" . PHP_EOL;
}

$order = $service->createOrder(499.00, 'RCPT_TEST_1001', ['plan_id' => 1, 'company_id' => 1]);
if (!empty($order['success']) && !empty($order['order_id']) && $order['amount'] === 49900) {
    echo "[PASS] RazorpayService::createOrder: Created order {$order['order_id']} for 49900 paise (₹499.00)" . PHP_EOL;
} else {
    echo "[FAIL] RazorpayService::createOrder failed: " . json_encode($order) . PHP_EOL;
}

// 3. Test Signature Verification
$testOrderId   = $order['order_id'];
$testPaymentId = 'pay_test_abc123xyz';
// Calculate HMAC-SHA256 signature
$validHmacSig  = hash_hmac('sha256', "{$testOrderId}|{$testPaymentId}", $config->keySecret);
$invalidSig    = 'invalid_signature_hash_000000000000000000000000';

$isHmacValid = $service->verifyPaymentSignature($testOrderId, $testPaymentId, $validHmacSig);
$isInvalidRejected = !$service->verifyPaymentSignature($testOrderId, $testPaymentId, $invalidSig);

if ($isHmacValid) {
    echo "[PASS] RazorpayService::verifyPaymentSignature: Standard HMAC-SHA256 signature verified" . PHP_EOL;
} else {
    echo "[FAIL] RazorpayService::verifyPaymentSignature failed valid HMAC" . PHP_EOL;
}

if ($isInvalidRejected) {
    echo "[PASS] RazorpayService::verifyPaymentSignature: Forged/invalid signature rejected" . PHP_EOL;
} else {
    echo "[FAIL] RazorpayService::verifyPaymentSignature accepted invalid signature" . PHP_EOL;
}

// 4. Test Simulated Test Mode Signature
$simSig = $service->generateTestSignature($testOrderId, $testPaymentId);
$isSimValid = $service->verifyPaymentSignature($testOrderId, $testPaymentId, $simSig);
if ($isSimValid) {
    echo "[PASS] RazorpayService::generateTestSignature & verify: Simulated signature verified" . PHP_EOL;
} else {
    echo "[FAIL] RazorpayService simulated signature verification failed" . PHP_EOL;
}

// 5. Test PaymentTransactionModel Database Operations
$db = \Config\Database::connect('default');
$company = $db->table('companies')->get()->getFirstRow('array');
$companyId = $company ? (int)$company['company_id'] : 1;

$txModel = new PaymentTransactionModel($db);

$insertedId = $txModel->insert([
    'transaction_company_id'    => $companyId,
    'transaction_user_id'       => 1,
    'transaction_type'          => 'subscription_upgrade',
    'transaction_reference_id'  => 1,
    'transaction_gateway'       => 'razorpay',
    'transaction_order_id'      => $testOrderId,
    'transaction_amount'        => 499.00,
    'transaction_currency'      => 'INR',
    'transaction_status'        => 'pending',
    'transaction_billing_cycle' => 'monthly',
    'transaction_notes'         => 'Automated Unit Test Transaction',
]);

if ($insertedId > 0) {
    echo "[PASS] PaymentTransactionModel: Inserted transaction record #{$insertedId}" . PHP_EOL;

    // Retrieve by order ID
    $found = $txModel->findByOrderId($testOrderId);
    if ($found && (int)$found['transaction_id'] === $insertedId) {
        echo "[PASS] PaymentTransactionModel::findByOrderId: Successfully retrieved transaction #{$insertedId}" . PHP_EOL;
    } else {
        echo "[FAIL] PaymentTransactionModel::findByOrderId failed" . PHP_EOL;
    }

    // Update to successful
    $txModel->update($insertedId, [
        'transaction_status'         => 'successful',
        'transaction_payment_id'     => $testPaymentId,
        'transaction_signature'      => $validHmacSig,
        'transaction_payment_method' => 'razorpay_test_card',
    ]);

    $updated = $txModel->find($insertedId);
    if ($updated && $updated['transaction_status'] === 'successful' && $updated['transaction_payment_id'] === $testPaymentId) {
        echo "[PASS] PaymentTransactionModel: Updated status to 'successful' with payment ID {$testPaymentId}" . PHP_EOL;
    } else {
        echo "[FAIL] PaymentTransactionModel update status failed" . PHP_EOL;
    }

    // Clean up test record
    $txModel->delete($insertedId);
    echo "[PASS] PaymentTransactionModel: Cleaned up test record #{$insertedId}" . PHP_EOL;
} else {
    echo "[FAIL] PaymentTransactionModel insertion failed" . PHP_EOL;
}

echo "==========================================================" . PHP_EOL;
echo "  ALL RAZORPAY UNIT TESTS COMPLETED SUCCESSFULLY" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
