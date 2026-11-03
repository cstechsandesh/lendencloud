<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ====================================================================
// Public (Unauthenticated) Routes
// ====================================================================

$routes->get('/', 'Home::index');
$routes->get('features', 'Home::features');
$routes->get('multi-company', 'Home::multiCompany');
$routes->get('pricing', 'Home::pricing');
$routes->get('faq', 'Home::faq');
$routes->get('sitemap.xml', 'SeoController::sitemap');
$routes->get('robots.txt', 'SeoController::robots');

$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->get('logout', 'Auth\AuthController::logout');
$routes->get('subscribe', 'Auth\AuthController::subscribe');
$routes->post('subscribe', 'Auth\AuthController::processSubscribe');

// ====================================================================
// Protected Routes (Require authentication via AuthFilter)
// ====================================================================

$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard\DashboardController::index');

    // ----------------------------------------------------------------
    // Profile (any authenticated user)
    // ----------------------------------------------------------------
    $routes->get('profile', 'Admin\ProfileController::index');
    $routes->post('profile/update', 'Admin\ProfileController::update');
    $routes->post('profile/change-password', 'Admin\ProfileController::changePassword');

    // ----------------------------------------------------------------
    // Subscriptions & SaaS Pricing Plans Management
    // ----------------------------------------------------------------
    $routes->group('subscriptions', function ($routes) {
        $routes->get('/', 'Admin\SubscriptionController::index', ['filter' => 'permission:subscriptions.view']);
        $routes->post('store', 'Admin\SubscriptionController::store', ['filter' => 'permission:subscriptions.manage']);
        $routes->post('update/(:num)', 'Admin\SubscriptionController::update/$1', ['filter' => 'permission:subscriptions.manage']);
        $routes->post('quick-action/(:num)', 'Admin\SubscriptionController::quickAction/$1', ['filter' => 'permission:subscriptions.manage']);
        $routes->post('toggle-status', 'Admin\SubscriptionController::toggleStatus', ['filter' => 'permission:subscriptions.manage']);
        $routes->post('plans/store', 'Admin\SubscriptionController::storePlan', ['filter' => 'permission:subscriptions.manage']);
        $routes->post('plans/update/(:num)', 'Admin\SubscriptionController::updatePlan/$1', ['filter' => 'permission:subscriptions.manage']);
        $routes->get('export', 'Admin\SubscriptionController::exportCsv', ['filter' => 'permission:subscriptions.view']);
    });
    $routes->get('subscription', 'Admin\SubscriptionController::mySubscription');

    // Razorpay Online Payments & Checkout
    $routes->group('payments/razorpay', function ($routes) {
        $routes->post('create-order', 'Payments\RazorpayController::createSubscriptionOrder');
        $routes->post('verify-payment', 'Payments\RazorpayController::verifySubscriptionPayment');
        $routes->post('payment-failed', 'Payments\RazorpayController::paymentFailed');
    });

    // ----------------------------------------------------------------
    // Multi-Company Management
    // ----------------------------------------------------------------
    $routes->group('companies', function ($routes) {
        $routes->get('/', 'Admin\CompanyController::index', ['filter' => 'permission:companies.view']);
        $routes->post('store', 'Admin\CompanyController::store', ['filter' => 'permission:companies.create']);
        $routes->post('update/(:num)', 'Admin\CompanyController::update/$1', ['filter' => 'permission:companies.edit']);
        $routes->get('delete/(:num)', 'Admin\CompanyController::delete/$1', ['filter' => 'permission:companies.delete']);
        $routes->get('switch/(:num)', 'Admin\CompanyController::switch/$1', ['filter' => 'permission:companies.switch']);
        $routes->get('view/(:num)', 'Admin\CompanyController::view/$1', ['filter' => 'permission:companies.view']);
    });

    // ----------------------------------------------------------------
    // User Management
    // ----------------------------------------------------------------
    $routes->group('users', ['filter' => 'permission:users.view'], function ($routes) {
        $routes->get('/', 'Admin\UserController::index');
        $routes->get('view/(:num)', 'Admin\UserController::view/$1');
        $routes->get('create', 'Admin\UserController::create', ['filter' => 'permission:users.create']);
        $routes->post('store', 'Admin\UserController::store', ['filter' => 'permission:users.create']);
        $routes->get('edit/(:num)', 'Admin\UserController::edit/$1', ['filter' => 'permission:users.edit']);
        $routes->post('update/(:num)', 'Admin\UserController::update/$1', ['filter' => 'permission:users.edit']);
        $routes->get('delete/(:num)', 'Admin\UserController::delete/$1', ['filter' => 'permission:users.delete']);
    });
    $routes->get('admin/users', 'Admin\UserController::index', ['filter' => 'permission:users.view']);

    // ----------------------------------------------------------------
    // Role Management
    // ----------------------------------------------------------------
    $routes->group('roles', ['filter' => 'permission:roles.view'], function ($routes) {
        $routes->get('/', 'Admin\RoleController::index');
        $routes->get('create', 'Admin\RoleController::create', ['filter' => 'permission:roles.create']);
        $routes->post('store', 'Admin\RoleController::store', ['filter' => 'permission:roles.create']);
        $routes->get('edit/(:num)', 'Admin\RoleController::edit/$1', ['filter' => 'permission:roles.edit']);
        $routes->post('update/(:num)', 'Admin\RoleController::update/$1', ['filter' => 'permission:roles.edit']);
        $routes->get('delete/(:num)', 'Admin\RoleController::delete/$1', ['filter' => 'permission:roles.delete']);
    });
    $routes->get('admin/roles', 'Admin\RoleController::index', ['filter' => 'permission:roles.view']);

    // ----------------------------------------------------------------
    // Chart of Accounts
    // ----------------------------------------------------------------
    $routes->group('accounts', ['filter' => 'permission:accounts.view'], function ($routes) {
        $routes->get('/', 'Accounts\AccountController::index');
        $routes->get('create', 'Accounts\AccountController::create');
        $routes->post('store', 'Accounts\AccountController::store');
        $routes->get('edit/(:num)', 'Accounts\AccountController::edit/$1');
        $routes->post('update/(:num)', 'Accounts\AccountController::update/$1');
        $routes->get('delete/(:num)', 'Accounts\AccountController::delete/$1');
        $routes->get('ledger/(:num)', 'Accounts\AccountController::ledger/$1');

        $routes->get('groups', 'Accounts\AccountGroupController::index');
        $routes->get('groups/create', 'Accounts\AccountGroupController::create');
        $routes->post('groups/store', 'Accounts\AccountGroupController::store');
        $routes->get('groups/edit/(:num)', 'Accounts\AccountGroupController::edit/$1');
        $routes->post('groups/update/(:num)', 'Accounts\AccountGroupController::update/$1');
        $routes->get('groups/delete/(:num)', 'Accounts\AccountGroupController::delete/$1');
        $routes->get('journals', 'Accounts\JournalController::index');
    });

    // ----------------------------------------------------------------
    // Journal Entries
    // ----------------------------------------------------------------
    $routes->group('journals', function ($routes) {
        $routes->get('/', 'Accounts\JournalController::index');
        $routes->get('create', 'Accounts\JournalController::create');
        $routes->post('store', 'Accounts\JournalController::store');
        $routes->get('view/(:num)', 'Accounts\JournalController::view/$1');
    });

    // ----------------------------------------------------------------
    // Parties
    // ----------------------------------------------------------------
    $routes->group('parties', function ($routes) {
        $routes->get('/', 'Parties\PartyController::index');
        $routes->get('create', 'Parties\PartyController::create');
        $routes->post('store', 'Parties\PartyController::store');
        $routes->get('edit/(:num)', 'Parties\PartyController::edit/$1');
        $routes->post('update/(:num)', 'Parties\PartyController::update/$1');
        $routes->get('delete/(:num)', 'Parties\PartyController::delete/$1');
        $routes->get('statement/(:num)', 'Parties\PartyController::statement/$1');
    });

    // ----------------------------------------------------------------
    // Inventory
    // ----------------------------------------------------------------
    $routes->group('inventory', ['filter' => ['permission:inventory.view', 'plan_feature:inventory']], function ($routes) {
        $routes->get('/', 'Inventory\ProductController::index');
        $routes->get('products', 'Inventory\ProductController::index');
        $routes->get('products/create', 'Inventory\ProductController::create');
        $routes->post('products/store', 'Inventory\ProductController::store');
        $routes->get('products/edit/(:num)', 'Inventory\ProductController::edit/$1');
        $routes->post('products/update/(:num)', 'Inventory\ProductController::update/$1');
        $routes->get('products/delete/(:num)', 'Inventory\ProductController::delete/$1');
        $routes->get('products/stock/(:num)', 'Inventory\ProductController::stock/$1');

        $routes->get('categories', 'Inventory\CategoryController::index');
        $routes->get('categories/create', 'Inventory\CategoryController::create');
        $routes->post('categories/store', 'Inventory\CategoryController::store');
        $routes->get('categories/edit/(:num)', 'Inventory\CategoryController::edit/$1');
        $routes->post('categories/update/(:num)', 'Inventory\CategoryController::update/$1');
        $routes->get('categories/delete/(:num)', 'Inventory\CategoryController::delete/$1');

        $routes->get('warehouses', 'Inventory\WarehouseController::index');
        $routes->get('warehouses/create', 'Inventory\WarehouseController::create');
        $routes->post('warehouses/store', 'Inventory\WarehouseController::store');
        $routes->post('warehouses/update/(:num)', 'Inventory\WarehouseController::update/$1');
        $routes->get('warehouses/delete/(:num)', 'Inventory\WarehouseController::delete/$1');

        $routes->get('adjustments', 'Inventory\StockAdjustmentController::index');
        $routes->get('adjustments/create', 'Inventory\StockAdjustmentController::create');
        $routes->post('adjustments/store', 'Inventory\StockAdjustmentController::store');
    });

    // ----------------------------------------------------------------
    // Sales
    // ----------------------------------------------------------------
    $routes->group('sales', function ($routes) {
        $routes->group('orders', ['filter' => ['permission:sales.view', 'plan_feature:sales']], function ($routes) {
            $routes->get('/', 'Sales\SalesOrderController::index');
            $routes->get('create', 'Sales\SalesOrderController::create');
            $routes->post('store', 'Sales\SalesOrderController::store');
            $routes->get('view/(:num)', 'Sales\SalesOrderController::view/$1');
            $routes->get('edit/(:num)', 'Sales\SalesOrderController::edit/$1');
            $routes->post('update/(:num)', 'Sales\SalesOrderController::update/$1');
            $routes->get('cancel/(:num)', 'Sales\SalesOrderController::cancel/$1');
            $routes->get('invoice/(:num)', 'Sales\SalesOrderController::createInvoice/$1');
        });

        $routes->group('invoices', ['filter' => ['permission:sales.view', 'plan_feature:sales']], function ($routes) {
            $routes->get('/', 'Sales\InvoiceController::index');
            $routes->get('create', 'Sales\InvoiceController::create');
            $routes->post('store', 'Sales\InvoiceController::store');
            $routes->get('view/(:num)', 'Sales\InvoiceController::view/$1');
            $routes->get('edit/(:num)', 'Sales\InvoiceController::edit/$1');
            $routes->post('update/(:num)', 'Sales\InvoiceController::update/$1');
            $routes->get('cancel/(:num)', 'Sales\InvoiceController::cancel/$1');
            $routes->get('print/(:num)', 'Sales\InvoiceController::printView/$1');
        });
    });

    // ----------------------------------------------------------------
    // Purchases
    // ----------------------------------------------------------------
    $routes->group('purchases', function ($routes) {
        $routes->group('orders', ['filter' => ['permission:purchases.view', 'plan_feature:purchases']], function ($routes) {
            $routes->get('/', 'Purchases\PurchaseOrderController::index');
            $routes->get('create', 'Purchases\PurchaseOrderController::create');
            $routes->post('store', 'Purchases\PurchaseOrderController::store');
            $routes->get('view/(:num)', 'Purchases\PurchaseOrderController::view/$1');
            $routes->get('edit/(:num)', 'Purchases\PurchaseOrderController::edit/$1');
            $routes->post('update/(:num)', 'Purchases\PurchaseOrderController::update/$1');
            $routes->get('cancel/(:num)', 'Purchases\PurchaseOrderController::cancel/$1');
            $routes->get('bill/(:num)', 'Purchases\PurchaseOrderController::createBill/$1');
        });

        $routes->group('bills', ['filter' => ['permission:purchases.view', 'plan_feature:purchases']], function ($routes) {
            $routes->get('/', 'Purchases\BillController::index');
            $routes->get('create', 'Purchases\BillController::create');
            $routes->post('store', 'Purchases\BillController::store');
            $routes->get('view/(:num)', 'Purchases\BillController::view/$1');
            $routes->get('edit/(:num)', 'Purchases\BillController::edit/$1');
            $routes->post('update/(:num)', 'Purchases\BillController::update/$1');
            $routes->get('cancel/(:num)', 'Purchases\BillController::cancel/$1');
        });
    });

    // ----------------------------------------------------------------
    // Payments & Receipts (Vouchers)
    // ----------------------------------------------------------------
    $routes->group('vouchers', ['filter' => 'permission:vouchers.view'], function ($routes) {
        $routes->get('receipts', 'Payments\ReceiptController::index');
        $routes->get('receipts/create', 'Payments\ReceiptController::create');
        $routes->post('receipts/store', 'Payments\ReceiptController::store');
        $routes->get('receipts/view/(:num)', 'Payments\ReceiptController::view/$1');

        $routes->get('payments', 'Payments\PaymentController::index');
        $routes->get('payments/create', 'Payments\PaymentController::create');
        $routes->post('payments/store', 'Payments\PaymentController::store');
        $routes->get('payments/view/(:num)', 'Payments\PaymentController::view/$1');

        $routes->get('contra', 'Payments\ContraController::index');
        $routes->get('contra/create', 'Payments\ContraController::create');
        $routes->post('contra/store', 'Payments\ContraController::store');
        $routes->get('contra/view/(:num)', 'Payments\ContraController::view/$1');
    });

    // ----------------------------------------------------------------
    // Reports
    // ----------------------------------------------------------------
    $routes->group('reports', ['filter' => 'permission:reports.view'], function ($routes) {
        $routes->get('/', 'Reports\ReportController::index');
        $routes->get('trial-balance', 'Reports\ReportController::trialBalance', ['filter' => 'plan_feature:advanced_reports']);
        $routes->get('profit-loss', 'Reports\ReportController::profitLoss', ['filter' => 'plan_feature:advanced_reports']);
        $routes->get('balance-sheet', 'Reports\ReportController::balanceSheet', ['filter' => 'plan_feature:advanced_reports']);
        $routes->get('stock', 'Reports\ReportController::stock', ['filter' => 'plan_feature:inventory']);
        $routes->get('day-book', 'Reports\ReportController::dayBook');
    });

    // ----------------------------------------------------------------
    // Settings
    // ----------------------------------------------------------------
    $routes->group('settings', ['filter' => 'permission:settings.view'], function ($routes) {
        $routes->get('/', 'Settings\SettingController::index');
        $routes->post('save', 'Settings\SettingController::save');

        $routes->get('tax-rates', 'Settings\TaxRateController::index');
        $routes->get('tax-rates/create', 'Settings\TaxRateController::create');
        $routes->post('tax-rates/store', 'Settings\TaxRateController::store');
        $routes->get('tax-rates/edit/(:num)', 'Settings\TaxRateController::edit/$1');
        $routes->post('tax-rates/update/(:num)', 'Settings\TaxRateController::update/$1');
        $routes->get('tax-rates/delete/(:num)', 'Settings\TaxRateController::delete/$1');
    });

    // ----------------------------------------------------------------
    // AJAX Endpoints
    // ----------------------------------------------------------------
    $routes->group('ajax', function ($routes) {
        $routes->get('product/(:num)', 'Ajax\AjaxController::getProduct/$1');
        $routes->get('get-product/(:num)', 'Ajax\AjaxController::getProduct/$1');
        $routes->get('party-invoices/(:num)', 'Ajax\AjaxController::getPartyInvoices/$1');
        $routes->get('get-party-invoices/(:num)', 'Ajax\AjaxController::getPartyInvoices/$1');
        $routes->get('party-bills/(:num)', 'Ajax\AjaxController::getPartyBills/$1');
        $routes->get('get-party-bills/(:num)', 'Ajax\AjaxController::getPartyBills/$1');
        $routes->get('account-balance/(:num)', 'Ajax\AjaxController::getAccountBalance/$1');
    });

});
