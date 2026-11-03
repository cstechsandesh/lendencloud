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
    ];
}
