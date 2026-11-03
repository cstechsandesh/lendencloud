<?php

chdir(__DIR__ . '/../..');
putenv('CI_TEST_DB_GROUP=default');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

$db = \Config\Database::connect('default');
$session = service('session');

// Get sample IDs
$company = $db->table('companies')->get()->getFirstRow('array');
$user = $db->table('users')->where('user_username', 'admin')->get()->getRowArray();
$perms = (new \App\Models\UserModel($db))->getUserPermissions((int)$user['user_id']);

$account = $db->table('accounts')->where('account_company_id', $company['company_id'])->get()->getFirstRow('array');
$accountGroup = $db->table('account_groups')->get()->getFirstRow('array');
$party = $db->table('parties')->where('party_company_id', $company['company_id'])->get()->getFirstRow('array');
$product = $db->table('products')->where('product_company_id', $company['company_id'])->get()->getFirstRow('array');
$category = $db->table('categories')->where('category_company_id', $company['company_id'])->get()->getFirstRow('array');
$warehouse = $db->table('warehouses')->where('warehouse_company_id', $company['company_id'])->get()->getFirstRow('array');
$journal = $db->table('journal_entries')->where('journal_company_id', $company['company_id'])->get()->getFirstRow('array');
$salesOrder = $db->table('sales_orders')->where('so_company_id', $company['company_id'])->get()->getFirstRow('array');
$invoice = $db->table('invoices')->where('invoice_company_id', $company['company_id'])->get()->getFirstRow('array');
$purchaseOrder = $db->table('purchase_orders')->where('po_company_id', $company['company_id'])->get()->getFirstRow('array');
$bill = $db->table('bills')->where('bill_company_id', $company['company_id'])->get()->getFirstRow('array');
$receiptVoucher = $db->table('vouchers')->where('voucher_type', 'receipt')->get()->getFirstRow('array');
$paymentVoucher = $db->table('vouchers')->where('voucher_type', 'payment')->get()->getFirstRow('array');
$contraVoucher = $db->table('vouchers')->where('voucher_type', 'contra')->get()->getFirstRow('array');
if (!$contraVoucher) {
    // Seed a contra voucher so it can be tested
    $cashAcc = $db->table('accounts')->where('account_company_id', 1)->where('account_code', '1000')->get()->getRowArray();
    $bankAcc = $db->table('accounts')->where('account_company_id', 1)->where('account_code', '1010')->get()->getRowArray();
    if ($cashAcc && $bankAcc) {
        $db->table('vouchers')->insert([
            'voucher_company_id'          => 1,
            'voucher_number'              => 'CNTR-2026-0001',
            'voucher_type'                => 'contra',
            'voucher_account_id'          => $bankAcc['account_id'],
            'voucher_transfer_account_id' => $cashAcc['account_id'],
            'voucher_date'                => date('Y-m-d'),
            'voucher_amount'              => 5000.00,
            'voucher_payment_mode'        => 'bank_transfer',
            'voucher_reference'           => 'ATM-WITHDRAW-01',
            'voucher_narration'           => 'Cash withdrawal for office petty cash',
            'voucher_status'              => 'posted',
            'voucher_created_by'          => 1,
            'created_at'                  => date('Y-m-d H:i:s'),
            'updated_at'                  => date('Y-m-d H:i:s'),
        ]);
        $contraVoucher = $db->table('vouchers')->where('voucher_type', 'contra')->get()->getFirstRow('array');
    }
}
$taxRate = $db->table('tax_rates')->where('tax_company_id', $company['company_id'])->get()->getFirstRow('array');
$role = $db->table('roles')->get()->getFirstRow('array');

// Set super admin session
$session->set([
    'logged_in'    => true,
    'is_logged_in' => true,
    'user_id'      => (int)$user['user_id'],
    'user_role'    => 'super_admin',
    'roles'        => ['super_admin'],
    'permissions'  => $perms,
    'company_id'   => (int)$company['company_id'],
    'user_email'   => $user['user_email'],
    'user_username'=> $user['user_username'],
    'user_name'    => $user['user_name'],
    'is_demo_user' => false,
]);

$pages = [
    // Landing pages (Home)
    'Home Index'               => [\App\Controllers\Home::class, 'index', []],
    'Home Features'            => [\App\Controllers\Home::class, 'features', []],
    'Home MultiCompany'        => [\App\Controllers\Home::class, 'multiCompany', []],
    'Home Pricing'             => [\App\Controllers\Home::class, 'pricing', []],
    'Home FAQ'                 => [\App\Controllers\Home::class, 'faq', []],
    'Auth Login'               => [\App\Controllers\Auth\AuthController::class, 'login', []],
    'Auth Subscribe'           => [\App\Controllers\Auth\AuthController::class, 'subscribe', []],

    // Dashboard & Profile
    'Dashboard'                => [\App\Controllers\Dashboard\DashboardController::class, 'index', []],
    'Profile'                  => [\App\Controllers\Admin\ProfileController::class, 'index', []],

    // Accounts
    'Accounts List'            => [\App\Controllers\Accounts\AccountController::class, 'index', []],
    'Accounts Create'          => [\App\Controllers\Accounts\AccountController::class, 'create', []],
    'Accounts Edit'            => [\App\Controllers\Accounts\AccountController::class, 'edit', [$account['account_id'] ?? 1]],
    'Accounts Ledger'          => [\App\Controllers\Accounts\AccountController::class, 'ledger', [$account['account_id'] ?? 1]],
    'Account Groups List'      => [\App\Controllers\Accounts\AccountGroupController::class, 'index', []],
    'Account Groups Create'    => [\App\Controllers\Accounts\AccountGroupController::class, 'create', []],
    'Account Groups Edit'      => [\App\Controllers\Accounts\AccountGroupController::class, 'edit', [$accountGroup['group_id'] ?? 1]],

    // Journals
    'Journals List'            => [\App\Controllers\Accounts\JournalController::class, 'index', []],
    'Journals Create'          => [\App\Controllers\Accounts\JournalController::class, 'create', []],
    'Journals View'            => [\App\Controllers\Accounts\JournalController::class, 'view', [$journal['journal_id'] ?? 1]],

    // Parties
    'Parties List'             => [\App\Controllers\Parties\PartyController::class, 'index', []],
    'Parties Create'           => [\App\Controllers\Parties\PartyController::class, 'create', []],
    'Parties Edit'             => [\App\Controllers\Parties\PartyController::class, 'edit', [$party['party_id'] ?? 1]],
    'Parties Statement'        => [\App\Controllers\Parties\PartyController::class, 'statement', [$party['party_id'] ?? 1]],

    // Inventory
    'Products List'            => [\App\Controllers\Inventory\ProductController::class, 'index', []],
    'Products Create'          => [\App\Controllers\Inventory\ProductController::class, 'create', []],
    'Products Edit'            => [\App\Controllers\Inventory\ProductController::class, 'edit', [$product['product_id'] ?? 1]],
    'Products Stock View'      => [\App\Controllers\Inventory\ProductController::class, 'stock', [$product['product_id'] ?? 1]],
    'Categories List'          => [\App\Controllers\Inventory\CategoryController::class, 'index', []],
    'Categories Create'        => [\App\Controllers\Inventory\CategoryController::class, 'create', []],
    'Categories Edit'          => [\App\Controllers\Inventory\CategoryController::class, 'edit', [$category['category_id'] ?? 1]],
    'Warehouses List'          => [\App\Controllers\Inventory\WarehouseController::class, 'index', []],
    'Warehouses Create'        => [\App\Controllers\Inventory\WarehouseController::class, 'create', []],
    'Stock Adjustments List'   => [\App\Controllers\Inventory\StockAdjustmentController::class, 'index', []],
    'Stock Adjustments Create' => [\App\Controllers\Inventory\StockAdjustmentController::class, 'create', []],

    // Sales
    'Sales Orders List'        => [\App\Controllers\Sales\SalesOrderController::class, 'index', []],
    'Sales Orders Create'      => [\App\Controllers\Sales\SalesOrderController::class, 'create', []],
    'Sales Orders View'        => [\App\Controllers\Sales\SalesOrderController::class, 'view', [$salesOrder['so_id'] ?? 1]],
    'Sales Orders Edit'        => [\App\Controllers\Sales\SalesOrderController::class, 'edit', [$salesOrder['so_id'] ?? 1]],
    'Sales Invoices List'      => [\App\Controllers\Sales\InvoiceController::class, 'index', []],
    'Sales Invoices Create'    => [\App\Controllers\Sales\InvoiceController::class, 'create', []],
    'Sales Invoices View'      => [\App\Controllers\Sales\InvoiceController::class, 'view', [$invoice['invoice_id'] ?? 1]],
    'Sales Invoices Edit'      => [\App\Controllers\Sales\InvoiceController::class, 'edit', [$invoice['invoice_id'] ?? 1]],
    'Sales Invoices Print'     => [\App\Controllers\Sales\InvoiceController::class, 'printView', [$invoice['invoice_id'] ?? 1]],

    // Purchases
    'Purchase Orders List'     => [\App\Controllers\Purchases\PurchaseOrderController::class, 'index', []],
    'Purchase Orders Create'   => [\App\Controllers\Purchases\PurchaseOrderController::class, 'create', []],
    'Purchase Orders View'     => [\App\Controllers\Purchases\PurchaseOrderController::class, 'view', [$purchaseOrder['po_id'] ?? 1]],
    'Purchase Orders Edit'     => [\App\Controllers\Purchases\PurchaseOrderController::class, 'edit', [$purchaseOrder['po_id'] ?? 1]],
    'Vendor Bills List'        => [\App\Controllers\Purchases\BillController::class, 'index', []],
    'Vendor Bills Create'      => [\App\Controllers\Purchases\BillController::class, 'create', []],
    'Vendor Bills View'        => [\App\Controllers\Purchases\BillController::class, 'view', [$bill['bill_id'] ?? 1]],
    'Vendor Bills Edit'        => [\App\Controllers\Purchases\BillController::class, 'edit', [$bill['bill_id'] ?? 1]],

    // Vouchers
    'Receipts List'            => [\App\Controllers\Payments\ReceiptController::class, 'index', []],
    'Receipts Create'          => [\App\Controllers\Payments\ReceiptController::class, 'create', []],
    'Receipts View'            => [\App\Controllers\Payments\ReceiptController::class, 'view', [$receiptVoucher['voucher_id'] ?? 1]],
    'Payments List'            => [\App\Controllers\Payments\PaymentController::class, 'index', []],
    'Payments Create'          => [\App\Controllers\Payments\PaymentController::class, 'create', []],
    'Payments View'            => [\App\Controllers\Payments\PaymentController::class, 'view', [$paymentVoucher['voucher_id'] ?? 1]],
    'Contra List'              => [\App\Controllers\Payments\ContraController::class, 'index', []],
    'Contra Create'            => [\App\Controllers\Payments\ContraController::class, 'create', []],
    'Contra View'              => [\App\Controllers\Payments\ContraController::class, 'view', [$contraVoucher['voucher_id'] ?? 1]],

    // Reports
    'Reports Overview'         => [\App\Controllers\Reports\ReportController::class, 'index', []],
    'Trial Balance'            => [\App\Controllers\Reports\ReportController::class, 'trialBalance', []],
    'Profit & Loss'            => [\App\Controllers\Reports\ReportController::class, 'profitLoss', []],
    'Balance Sheet'            => [\App\Controllers\Reports\ReportController::class, 'balanceSheet', []],
    'Stock Valuation'          => [\App\Controllers\Reports\ReportController::class, 'stock', []],
    'Day Book'                 => [\App\Controllers\Reports\ReportController::class, 'dayBook', []],

    // Administration
    'Companies List'           => [\App\Controllers\Admin\CompanyController::class, 'index', []],
    'Companies View'           => [\App\Controllers\Admin\CompanyController::class, 'view', [$company['company_id'] ?? 1]],
    'Users List'               => [\App\Controllers\Admin\UserController::class, 'index', []],
    'Users Create'             => [\App\Controllers\Admin\UserController::class, 'create', []],
    'Users Edit'               => [\App\Controllers\Admin\UserController::class, 'edit', [$user['user_id'] ?? 1]],
    'Users View'               => [\App\Controllers\Admin\UserController::class, 'view', [$user['user_id'] ?? 1]],
    'Roles List'               => [\App\Controllers\Admin\RoleController::class, 'index', []],
    'Roles Create'             => [\App\Controllers\Admin\RoleController::class, 'create', []],
    'Roles Edit'               => [\App\Controllers\Admin\RoleController::class, 'edit', [$role['role_id'] ?? 1]],
    'Subscriptions List'       => [\App\Controllers\Admin\SubscriptionController::class, 'index', []],
    'My Subscription'          => [\App\Controllers\Admin\SubscriptionController::class, 'mySubscription', []],

    // Settings
    'Settings Overview'        => [\App\Controllers\Settings\SettingController::class, 'index', []],
    'Tax Rates List'           => [\App\Controllers\Settings\TaxRateController::class, 'index', []],
    'Tax Rates Create'         => [\App\Controllers\Settings\TaxRateController::class, 'create', []],
    'Tax Rates Edit'           => [\App\Controllers\Settings\TaxRateController::class, 'edit', [$taxRate['tax_id'] ?? 1]],
];

echo "================================================================================" . PHP_EOL;
echo "  DEEP AUDIT: EXECUTING EVERY PAGE AND VIEW CONTROLLER IN THE APPLICATION" . PHP_EOL;
echo "================================================================================" . PHP_EOL;

$errors = [];
$request = service('request');
$response = service('response');
$logger = service('logger');

foreach ($pages as $label => [$class, $method, $params]) {
    $session->remove('error');
    $obLevel = ob_get_level();
    try {
        $controller = new $class();
        if (method_exists($controller, 'initController')) {
            $controller->initController($request, $response, $logger);
        }

        ob_start();
        $res = $controller->$method(...$params);
        $output = ob_get_clean();

        // Check if $res is string or View or Response
        if ($res instanceof \CodeIgniter\HTTP\RedirectResponse) {
            echo "  [PASS] {$label} -> RedirectResponse to " . $res->getHeaderLine('Location') . PHP_EOL;
        } elseif (is_string($res) || $res instanceof \CodeIgniter\HTTP\ResponseInterface || $output) {
            $rendered = is_string($res) ? $res : (string)$output;
            // Check for PHP notices/warnings in rendered html
            if (str_contains($rendered, 'Fatal error') || str_contains($rendered, 'Uncaught Exception') || str_contains($rendered, 'An uncaught Exception was encountered')) {
                echo "  [FAIL] {$label} -> Output contains fatal error/exception!" . PHP_EOL;
                $errors[] = "{$label}: Output contains PHP fatal error/exception";
            } else {
                echo "  [PASS] {$label} -> Rendered (" . strlen($rendered) . " bytes)" . PHP_EOL;
            }
        } else {
            echo "  [PASS] {$label} -> OK (Empty/void)" . PHP_EOL;
        }
    } catch (\Throwable $e) {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        $msg = $e->getMessage();
        $file = basename($e->getFile()) . ':' . $e->getLine();
        echo "  [ERROR] {$label} -> {$msg} at {$file}" . PHP_EOL;
        $errors[] = "{$label}: {$msg} at {$file}";
    }
}

echo "================================================================================" . PHP_EOL;
echo "  AUDIT RESULTS: " . (empty($errors) ? "ALL " . count($pages) . " PAGES PASSED WITH ZERO ERRORS!" : count($errors) . " ERRORS FOUND!") . PHP_EOL;
foreach ($errors as $e) {
    echo "    * {$e}" . PHP_EOL;
}
echo "================================================================================" . PHP_EOL;
