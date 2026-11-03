<?php

chdir(__DIR__ . '/../..');
putenv('CI_TEST_DB_GROUP=default');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Models\UserModel;

$db = \Config\Database::connect('default');
\CodeIgniter\Config\Services::injectMock('db', $db);
$dbConfig = config('Database');
$dbConfig->defaultGroup = 'default';
$session = service('session');

$rolesToTest = [
    'super_admin'       => 'admin',
    'admin'             => 'admin_demo',
    'accountant'        => 'accountant',
    'sales_manager'     => 'sales',
    'purchase_manager'  => 'purchase',
    'inventory_manager' => 'inventory',
    'viewer'            => 'viewer',
];

echo "================================================================================" . PHP_EOL;
echo "  COMPREHENSIVE MULTI-ROLE FLOW & ERROR DETECTION AUDIT" . PHP_EOL;
echo "================================================================================" . PHP_EOL;

$userModel = new UserModel($db);

$modulesToTest = [
    // Dashboard & Profile
    'Dashboard'           => ['class' => \App\Controllers\Dashboard\DashboardController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],
    'Profile'             => ['class' => \App\Controllers\Admin\ProfileController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],

    // Accounts
    'Accounts List'       => ['class' => \App\Controllers\Accounts\AccountController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Accounts Create'     => ['class' => \App\Controllers\Accounts\AccountController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant']],
    'Accounts Groups'     => ['class' => \App\Controllers\Accounts\AccountGroupController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Accounts Groups New' => ['class' => \App\Controllers\Accounts\AccountGroupController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant']],
    'Journals List'       => ['class' => \App\Controllers\Accounts\JournalController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Journals Create'     => ['class' => \App\Controllers\Accounts\JournalController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant']],

    // Parties (Customers & Vendors)
    'Parties List'        => ['class' => \App\Controllers\Parties\PartyController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'viewer']],
    'Parties Create'      => ['class' => \App\Controllers\Parties\PartyController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'sales_manager', 'purchase_manager']],

    // Inventory
    'Products List'       => ['class' => \App\Controllers\Inventory\ProductController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],
    'Products Create'     => ['class' => \App\Controllers\Inventory\ProductController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'inventory_manager']],
    'Categories List'     => ['class' => \App\Controllers\Inventory\CategoryController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],
    'Warehouses List'     => ['class' => \App\Controllers\Inventory\WarehouseController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],
    'Stock Adjustments'   => ['class' => \App\Controllers\Inventory\StockAdjustmentController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],
    'Stock Adjust Create' => ['class' => \App\Controllers\Inventory\StockAdjustmentController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'inventory_manager']],

    // Sales
    'Sales Orders List'   => ['class' => \App\Controllers\Sales\SalesOrderController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'viewer']],
    'Sales Orders Create' => ['class' => \App\Controllers\Sales\SalesOrderController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'sales_manager']],
    'Invoices List'       => ['class' => \App\Controllers\Sales\InvoiceController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'sales_manager', 'accountant', 'viewer']],
    'Invoices Create'     => ['class' => \App\Controllers\Sales\InvoiceController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'sales_manager']],

    // Purchases
    'Purchase Orders'     => ['class' => \App\Controllers\Purchases\PurchaseOrderController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'purchase_manager', 'viewer']],
    'Purchases Create'    => ['class' => \App\Controllers\Purchases\PurchaseOrderController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'purchase_manager']],
    'Vendor Bills List'   => ['class' => \App\Controllers\Purchases\BillController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'purchase_manager', 'accountant', 'viewer']],
    'Vendor Bills Create' => ['class' => \App\Controllers\Purchases\BillController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'purchase_manager']],

    // Vouchers (Receipts, Payments, Contra)
    'Receipts List'       => ['class' => \App\Controllers\Payments\ReceiptController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'viewer']],
    'Receipts Create'     => ['class' => \App\Controllers\Payments\ReceiptController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager']],
    'Payments List'       => ['class' => \App\Controllers\Payments\PaymentController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'purchase_manager', 'viewer']],
    'Payments Create'     => ['class' => \App\Controllers\Payments\PaymentController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'purchase_manager']],
    'Contra List'         => ['class' => \App\Controllers\Payments\ContraController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Contra Create'       => ['class' => \App\Controllers\Payments\ContraController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin', 'accountant']],

    // Reports
    'Reports Overview'    => ['class' => \App\Controllers\Reports\ReportController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Trial Balance'       => ['class' => \App\Controllers\Reports\ReportController::class, 'method' => 'trialBalance', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Profit & Loss'       => ['class' => \App\Controllers\Reports\ReportController::class, 'method' => 'profitLoss', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Balance Sheet'       => ['class' => \App\Controllers\Reports\ReportController::class, 'method' => 'balanceSheet', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],
    'Day Book'            => ['class' => \App\Controllers\Reports\ReportController::class, 'method' => 'dayBook', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'viewer']],

    // Multi-Company
    'Companies Portfolio' => ['class' => \App\Controllers\Admin\CompanyController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'viewer']],

    // Users
    'Users List'          => ['class' => \App\Controllers\Admin\UserController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'viewer']],
    'Users Register'      => ['class' => \App\Controllers\Admin\UserController::class, 'method' => 'create', 'expectedRoles' => ['super_admin', 'admin']],

    // Platform Super Admin Only
    'RBAC Roles'          => ['class' => \App\Controllers\Admin\RoleController::class, 'method' => 'index', 'expectedRoles' => ['super_admin']],
    'Platform Subs'       => ['class' => \App\Controllers\Admin\SubscriptionController::class, 'method' => 'index', 'expectedRoles' => ['super_admin']],

    // Tenant Subscription
    'My Subscription'     => ['class' => \App\Controllers\Admin\SubscriptionController::class, 'method' => 'mySubscription', 'expectedRoles' => ['super_admin', 'admin', 'accountant', 'sales_manager', 'purchase_manager', 'inventory_manager', 'viewer']],

    // Settings
    'Company Settings'    => ['class' => \App\Controllers\Settings\SettingController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'viewer']],
    'GST Tax Rates'       => ['class' => \App\Controllers\Settings\TaxRateController::class, 'method' => 'index', 'expectedRoles' => ['super_admin', 'admin', 'viewer']],
];

$summaryErrors = [];
$testedCount = 0;

foreach ($rolesToTest as $roleKey => $username) {
    echo PHP_EOL . ">>> TESTING ROLE: [{$roleKey}] (Username: {$username}) <<<" . PHP_EOL;

    $user = $db->table('users')->where('user_username', $username)->get()->getRowArray();
    if (!$user) {
        echo "  [ERROR] User {$username} not found in database!" . PHP_EOL;
        $summaryErrors[] = "User {$username} ({$roleKey}) not found!";
        continue;
    }

    $perms = $userModel->getUserPermissions((int)$user['user_id']);

    // Set session for this user
    $session->set([
        'logged_in'    => true,
        'is_logged_in' => true,
        'user_id'      => (int)$user['user_id'],
        'user_role'    => $user['user_role'],
        'roles'        => [$user['user_role']],
        'permissions'  => $perms,
        'company_id'   => (int)$user['user_company_id'],
        'user_email'   => $user['user_email'],
        'user_username'=> $user['user_username'],
        'user_name'    => $user['user_name'],
        'is_demo_user' => (bool)$user['user_is_demo'],
    ]);

    $request = service('request');
    $response = service('response');
    $logger = service('logger');

    foreach ($modulesToTest as $label => $test) {
        $testedCount++;
        $className = $test['class'];
        $methodName = $test['method'];
        $shouldHaveAccess = in_array($roleKey, $test['expectedRoles'], true);

        $session->remove('error');
        $obLevel = ob_get_level();
        try {
            // Instantiate controller
            $controller = new $className();
            $controller->initController($request, $response, $logger);

            // Execute method while buffering output
            ob_start();
            $result = $controller->$methodName();
            $output = ob_get_clean();

            // Analyze result
            if ($result instanceof \CodeIgniter\HTTP\RedirectResponse) {
                // If it redirected
                $flashError = $session->getFlashdata('error');
                if ($shouldHaveAccess && !empty($flashError)) {
                    echo "  [FAIL] {$label}: Unexpected access denied redirect! ({$flashError})" . PHP_EOL;
                    $summaryErrors[] = "Role {$roleKey} blocked on {$label}: {$flashError}";
                } elseif (!$shouldHaveAccess) {
                    echo "  [PASS] {$label}: Access correctly denied/redirected." . PHP_EOL;
                } else {
                    echo "  [PASS] {$label}: Redirected (e.g. index forwarding)." . PHP_EOL;
                }
            } else {
                // Rendered content or response
                if (!$shouldHaveAccess) {
                    echo "  [LEAK] {$label}: Role {$roleKey} accessed unpermitted module!" . PHP_EOL;
                    $summaryErrors[] = "Role {$roleKey} LEAK on {$label} (accessed unpermitted module)";
                } else {
                    echo "  [PASS] {$label}: Successfully rendered." . PHP_EOL;
                }
            }
        } catch (\Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            $msg = $e->getMessage();
            $file = basename($e->getFile()) . ':' . $e->getLine();

            // Check if it was an exit or access denied from BaseController::checkPermission
            if (str_contains($msg, 'Permission Denied') || str_contains($msg, 'Access Denied') || str_contains($msg, 'Access Required') || str_contains($msg, 'restricted')) {
                if ($shouldHaveAccess) {
                    echo "  [FAIL] {$label}: DENIED with {$msg} ({$file})" . PHP_EOL;
                    $summaryErrors[] = "Role {$roleKey} DENIED on {$label}: {$msg}";
                } else {
                    echo "  [PASS] {$label}: Correctly rejected with permission check." . PHP_EOL;
                }
            } else {
                echo "  [EXCEPTION] {$label}: {$msg} at {$file}" . PHP_EOL;
                $summaryErrors[] = "Role {$roleKey} EXCEPTION on {$label}: {$msg} ({$file})";
            }
        }
    }
}

echo PHP_EOL . "================================================================================" . PHP_EOL;
echo "  AUDIT COMPLETE: Tested {$testedCount} role-endpoint combinations." . PHP_EOL;
if (empty($summaryErrors)) {
    echo "  RESULT: ZERO ERRORS OR PERMISSION LEAKS DETECTED! ALL 7 ROLES FULLY VERIFIED." . PHP_EOL;
} else {
    echo "  RESULT: FOUND " . count($summaryErrors) . " ISSUES:" . PHP_EOL;
    foreach ($summaryErrors as $err) {
        echo "    * {$err}" . PHP_EOL;
    }
}
echo "================================================================================" . PHP_EOL;
