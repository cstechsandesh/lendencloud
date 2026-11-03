<?php

chdir(__DIR__ . '/../..');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Ensure helpers are loaded
helper(['url', 'form', 'cs', 'seo']);

$session = service('session');

echo "==========================================================" . PHP_EOL;
echo "  TEST SUITE: Google Search Console Implementation       " . PHP_EOL;
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
echo PHP_EOL . "--- 1. SEO & Search Console Config Tests ---" . PHP_EOL;
$seoConfig = new \Config\Seo();
assertTest(is_object($seoConfig), 'SEO config instantiates properly');
assertTest(property_exists($seoConfig, 'googleSiteVerification'), 'googleSiteVerification property exists');
assertTest(property_exists($seoConfig, 'googleSearchConsoleEnabled'), 'googleSearchConsoleEnabled property exists');
assertTest(property_exists($seoConfig, 'googleVerificationFile'), 'googleVerificationFile property exists');
assertTest($seoConfig->googleSearchConsoleEnabled === true, 'googleSearchConsoleEnabled defaults to true');
assertTest(is_string($seoConfig->googleSiteVerification), 'googleSiteVerification is a string');

// -------------------------------------------------------------------------
// 2. google_search_console() & google_site_verification() Helper Tests
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 2. Helper Rendering & Options Tests ---" . PHP_EOL;

// 2a. Reset static de-duplicator
google_search_console(['reset' => true]);

// 2b. Empty verification code
$outputEmpty = google_search_console(['force' => true, 'token' => '']);
assertTest($outputEmpty === '', 'Empty verification token returns empty string');

// 2c. Valid token rendering
google_search_console(['reset' => true]);
$outputValid = google_search_console(['token' => 'mock_google_token_abc123']);
assertTest(str_contains($outputValid, 'meta name="google-site-verification"'), 'Outputs meta name="google-site-verification"');
assertTest(str_contains($outputValid, 'content="mock_google_token_abc123"'), 'Outputs content attribute with verification token');
assertTest(str_contains($outputValid, '<!-- Google Search Console Verification -->'), 'Outputs descriptive HTML comment');

// 2d. De-duplication on the same page
$outputDuplicate = google_search_console(['token' => 'mock_google_token_abc123']);
assertTest($outputDuplicate === '', 'Subsequent call on same page returns empty string (preventing duplicate tags)');

// 2e. Force option bypasses de-duplication
$outputForced = google_search_console(['force' => true, 'token' => 'mock_forced_token']);
assertTest(str_contains($outputForced, 'mock_forced_token'), 'force => true bypasses de-duplication cache');

// 2f. Raw <meta ...> tag parsing in token argument
google_search_console(['reset' => true]);
$outputMetaTag = google_search_console([
    'force' => true,
    'token' => '<meta name="google-site-verification" content="extracted_from_tag_999" />'
]);
assertTest(str_contains($outputMetaTag, 'content="extracted_from_tag_999"'), 'Automatically extracts verification token if raw meta tag is supplied');
assertTest(!str_contains($outputMetaTag, '<meta name="google-site-verification" content="<meta'), 'Does not nest broken meta tag syntax');

// 2g. Disabled via options
google_search_console(['reset' => true]);
$outputDisabled = google_search_console(['enabled' => false, 'token' => 'token_should_not_show']);
assertTest($outputDisabled === '', 'Disabled toggle returns empty string even with token supplied');

// 2h. Alias google_site_verification() behaves identically
google_search_console(['reset' => true]);
$outputAlias = google_site_verification(['token' => 'alias_token_456']);
assertTest(str_contains($outputAlias, 'alias_token_456'), 'google_site_verification() alias renders properly');

// -------------------------------------------------------------------------
// 3. Integration in seo_meta()
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 3. Integration with seo_meta() ---" . PHP_EOL;

google_search_console(['reset' => true]);
$seoMetaOutput = seo_meta([
    'google_site_verification' => 'seo_meta_injected_token_777',
]);
assertTest(str_contains($seoMetaOutput, 'content="seo_meta_injected_token_777"'), 'seo_meta() automatically includes verification meta tag');

// Calling google_search_console after seo_meta should not duplicate
$secondCall = google_search_console(['token' => 'seo_meta_injected_token_777']);
assertTest($secondCall === '', 'google_search_console() does not duplicate tag already injected by seo_meta()');

// -------------------------------------------------------------------------
// 4. View Layout Integration ("Everywhere")
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 4. View Template Presence Audits ---" . PHP_EOL;

$viewsToAudit = [
    'app/Views/layouts/main.php'             => 'App Main Layout (All internal ERP modules & Super Admin)',
    'app/Views/landing/layout.php'           => 'Landing Page Layout (Homepage, Features, Pricing, FAQ)',
    'app/Views/auth/login.php'               => 'Authentication Login View',
    'app/Views/auth/subscribe.php'           => 'SaaS Checkout / Subscription View',
    'app/Views/welcome_message.php'          => 'Welcome Message View',
    'app/Views/errors/html/error_404.php'    => 'Error 404 View',
    'app/Views/errors/html/error_400.php'    => 'Error 400 View',
    'app/Views/errors/html/production.php'   => 'Production Error View',
];

foreach ($viewsToAudit as $viewPath => $viewDescription) {
    $fullPath = __DIR__ . '/../../' . $viewPath;
    assertTest(file_exists($fullPath), "View file exists: {$viewPath}");
    $content = file_get_contents($fullPath);
    $hasHelper = str_contains($content, 'google_search_console') || str_contains($content, 'google_site_verification');
    assertTest($hasHelper, "{$viewDescription} invokes google_search_console() in <head>");
}

// -------------------------------------------------------------------------
// 5. HTML File Verification Handler & robots.txt
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 5. HTML Verification File & Robots Tests ---" . PHP_EOL;

$controller = new \App\Controllers\SeoController();
$request = service('request');
$response = service('response');
$logger = service('logger');
$controller->initController($request, $response, $logger);

// 5a. File verification response
$fileResponse = $controller->googleVerification('google1234567890abcdef.html');
assertTest($fileResponse->getStatusCode() === 200, 'HTML file verification returns HTTP 200');
assertTest(
    $fileResponse->getBody() === 'google-site-verification: google1234567890abcdef.html',
    'HTML file verification body matches exact Google Search Console verification format'
);

// 5b. robots.txt allows verification file
$robotsResponse = $controller->robots();
$robotsBody = $robotsResponse->getBody();
assertTest(str_contains($robotsBody, 'Allow: /google*.html'), 'robots.txt explicitly allows Google verification files');

// -------------------------------------------------------------------------
// 6. Environment Configuration (.env & env)
// -------------------------------------------------------------------------
echo PHP_EOL . "--- 6. Environment Configuration (.env & env) Tests ---" . PHP_EOL;

$envContent = file_get_contents(__DIR__ . '/../../.env');
assertTest(str_contains($envContent, 'GOOGLE_SITE_VERIFICATION'), '.env contains GOOGLE_SITE_VERIFICATION setting');
assertTest(str_contains($envContent, 'GOOGLE_SEARCH_CONSOLE_ENABLED'), '.env contains GOOGLE_SEARCH_CONSOLE_ENABLED setting');
assertTest(str_contains($envContent, 'GOOGLE_SITE_VERIFICATION_FILE'), '.env contains GOOGLE_SITE_VERIFICATION_FILE setting');

$envTemplate = file_get_contents(__DIR__ . '/../../env');
assertTest(str_contains($envTemplate, 'GOOGLE_SITE_VERIFICATION'), 'env template contains GOOGLE_SITE_VERIFICATION');

// Summary
echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "  SUMMARY: {$passed} Passed, {$failed} Failed" . PHP_EOL;
echo "==========================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
