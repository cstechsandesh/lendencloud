<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Seo extends BaseConfig
{
    /**
     * Brand & Platform Identity
     */
    public string $siteName        = 'LenDen Cloud';
    public string $tagline         = 'Double-Entry Accounting & Multi-Company ERP';
    public string $defaultTitle    = 'LenDen Cloud — Cloud Accounting & Multi-Company ERP';
    public string $titleSeparator  = ' — ';

    /**
     * Global Default Meta Information
     */
    public string $defaultDescription = 'LenDen Cloud is a modern cloud accounting, GST invoicing, multi-warehouse inventory, and multi-company ERP suite designed for fast-growing businesses and accounting teams.';
    public string $defaultKeywords    = 'accounting software, cloud accounting, double entry accounting, GST billing software, ERP software, multi company accounting, inventory management, balance sheet software, cloud accounting India, invoice generator, SaaS ERP, journal voucher, ledger accounting';
    public string $author             = 'LenDen Cloud Solutions';
    public string $publisher          = 'LenDen Cloud';
    public string $themeColor         = '#2563eb';
    public string $robotsDefault      = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    public string $robotsProtected    = 'noindex, nofollow, noarchive, nosnippet';

    /**
     * Google Search Console / Google Site Verification
     * Accepts either a raw verification token string (e.g. '4z8hK_xVnQ3sL7yP2rT1wU9...')
     * or a complete meta tag string (<meta name="google-site-verification" content="..." />).
     */
    public string $googleSiteVerification = '';

    /**
     * Master switch to enable or disable Google Search Console meta tag.
     */
    public bool $googleSearchConsoleEnabled = true;

    /**
     * Optional Google Search Console HTML verification file name
     * (e.g. 'google1234567890abcdef.html')
     */
    public string $googleVerificationFile = '';

    /**
     * Social / Open Graph & Twitter Cards
     */
    public string $ogType             = 'website';
    public string $ogImage            = 'assets/images/og-lenden-preview.png';
    public int $ogImageWidth          = 1200;
    public int $ogImageHeight         = 630;
    public string $ogImageAlt         = 'LenDen Cloud — Cloud Accounting & Multi-Company ERP';
    public string $twitterCard        = 'summary_large_image';
    public string $twitterSite        = '@lenden_cloud';
    public string $twitterCreator     = '@lenden_cloud';
    public string $locale             = 'en_US';

    /**
     * Structured Data Organization & Business Schema
     */
    public string $applicationCategory = 'BusinessApplication, AccountingSoftware';
    public string $operatingSystem     = 'Web, Windows, macOS, Linux, Android, iOS';
    public string $currency            = 'INR';
    public string $currencySymbol      = '₹';
    public string $contactEmail        = 'support@lendencloud.com';
    public string $contactPhone        = '+91-9876543210';
    public array $socialLinks          = [
        'https://twitter.com/lenden_cloud',
        'https://www.linkedin.com/company/lenden-cloud',
        'https://github.com/lenden-cloud'
    ];

    /**
     * Public indexable routes for sitemap and crawling
     */
    public array $publicPages = [
        [
            'path'       => '',
            'priority'   => '1.0',
            'changefreq' => 'daily',
            'title'      => 'Complete Double-Entry Accounting & Business Invoicing Suite',
        ],
        [
            'path'       => 'features',
            'priority'   => '0.9',
            'changefreq' => 'weekly',
            'title'      => 'Core Modules & Cloud Accounting Capabilities',
        ],
        [
            'path'       => 'multi-company',
            'priority'   => '0.9',
            'changefreq' => 'weekly',
            'title'      => 'Multi-Company Architecture & Subsidiary Governance',
        ],
        [
            'path'       => 'pricing',
            'priority'   => '0.9',
            'changefreq' => 'weekly',
            'title'      => 'SaaS Pricing Plans & Feature Quotas',
        ],
        [
            'path'       => 'faq',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Frequently Asked Questions & Help Center',
        ],
        [
            'path'       => 'subscribe',
            'priority'   => '0.8',
            'changefreq' => 'monthly',
            'title'      => 'Start 30-Day Free Trial',
        ],
        [
            'path'       => 'login',
            'priority'   => '0.6',
            'changefreq' => 'monthly',
            'title'      => 'Sign In to Workspace',
        ],

        // Behind-Logged-In Business Modules (Accessible via Demo Mode)
        [
            'path'       => 'dashboard',
            'priority'   => '0.8',
            'changefreq' => 'daily',
            'title'      => 'Live Financial & Accounting Dashboard',
        ],
        [
            'path'       => 'accounts',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Chart of Accounts & General Ledgers',
        ],
        [
            'path'       => 'accounts/groups',
            'priority'   => '0.7',
            'changefreq' => 'monthly',
            'title'      => 'Account Groups & Hierarchy',
        ],
        [
            'path'       => 'journals',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Double-Entry Journal Entries',
        ],
        [
            'path'       => 'parties',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Customer & Vendor Master Management',
        ],
        [
            'path'       => 'inventory',
            'priority'   => '0.8',
            'changefreq' => 'daily',
            'title'      => 'Inventory & Multi-Warehouse Stock Catalog',
        ],
        [
            'path'       => 'inventory/warehouses',
            'priority'   => '0.7',
            'changefreq' => 'monthly',
            'title'      => 'Multi-Warehouse Management',
        ],
        [
            'path'       => 'inventory/adjustments',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Stock Reconciliation & Adjustments',
        ],
        [
            'path'       => 'sales/invoices',
            'priority'   => '0.8',
            'changefreq' => 'daily',
            'title'      => 'GST Tax Invoices & Billing Management',
        ],
        [
            'path'       => 'sales/orders',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Sales Orders & Quotations',
        ],
        [
            'path'       => 'purchases/bills',
            'priority'   => '0.8',
            'changefreq' => 'daily',
            'title'      => 'Vendor Purchase Bills & Input Tax Credit',
        ],
        [
            'path'       => 'purchases/orders',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Purchase Orders & Supplier Procurement',
        ],
        [
            'path'       => 'vouchers/receipts',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Customer Payment Receipt Vouchers',
        ],
        [
            'path'       => 'vouchers/payments',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Vendor Payment Vouchers',
        ],
        [
            'path'       => 'vouchers/contra',
            'priority'   => '0.7',
            'changefreq' => 'monthly',
            'title'      => 'Bank & Cash Contra Vouchers',
        ],
        [
            'path'       => 'reports',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Financial Reports & Audit Statements',
        ],
        [
            'path'       => 'reports/balance-sheet',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Balance Sheet Statement',
        ],
        [
            'path'       => 'reports/profit-loss',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Profit & Loss Statement',
        ],
        [
            'path'       => 'reports/trial-balance',
            'priority'   => '0.8',
            'changefreq' => 'weekly',
            'title'      => 'Trial Balance Statement',
        ],
        [
            'path'       => 'reports/stock',
            'priority'   => '0.7',
            'changefreq' => 'weekly',
            'title'      => 'Stock Valuation & Inventory Report',
        ],
        [
            'path'       => 'reports/day-book',
            'priority'   => '0.7',
            'changefreq' => 'daily',
            'title'      => 'Daily Financial Transactions & Day Book',
        ],
        [
            'path'       => 'companies',
            'priority'   => '0.7',
            'changefreq' => 'monthly',
            'title'      => 'Multi-Company Architecture & Subsidiary Governance',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // 1. Google Site Verification Token / Tag
        $verification = env(
            'GOOGLE_SITE_VERIFICATION',
            env('GOOGLE_SEARCH_CONSOLE_CODE',
            env('GOOGLE_SEARCH_CONSOLE_VERIFICATION',
            env('GOOGLE_SEARCH_CONSOLE_ID',
            env('google.site.verification',
            env('google.search.console.code',
            env('seo.googleSiteVerification', $this->googleSiteVerification))))))
        );

        if ($verification !== null) {
            $raw = trim((string)$verification);
            // If the user pasted the entire <meta ...> tag, extract the content attribute
            if (preg_match('/content=[\'"]([^\'"]+)[\'"]/i', $raw, $matches)) {
                $raw = trim($matches[1]);
            }
            // Strip any wrapping quotes or tags if present
            $raw = trim($raw, " \t\n\r\0\x0B\"'<>");
            $this->googleSiteVerification = $raw;
        }

        // 2. Master Enable / Disable Toggle
        $enabled = env('GOOGLE_SEARCH_CONSOLE_ENABLED', env('google.search.console.enabled', env('seo.googleSearchConsoleEnabled', null)));
        if ($enabled !== null) {
            $this->googleSearchConsoleEnabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        // 3. HTML Verification File Name
        $file = env('GOOGLE_SITE_VERIFICATION_FILE', env('GOOGLE_SEARCH_CONSOLE_FILE', env('google.verification.file', $this->googleVerificationFile)));
        if ($file !== null) {
            $this->googleVerificationFile = trim((string)$file);
        }
    }
}
