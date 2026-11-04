<?php

if (!function_exists('seo_config')) {
    /**
     * Get instance of SEO configuration
     */
    function seo_config(): \Config\Seo {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = config('Seo') ?? new \Config\Seo();
        }
        return $cfg;
    }
}

if (!function_exists('seo_canonical_url')) {
    /**
     * Build clean, canonical URL without unwanted query strings
     */
    function seo_canonical_url(?string $path = null): string {
        if ($path !== null && $path !== '') {
            return (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                ? $path
                : rtrim(base_url(ltrim($path, '/')), '/');
        }

        $uri = service('uri');
        $pathOnly = trim($uri->getPath(), '/');
        return rtrim(base_url($pathOnly), '/');
    }
}

if (!function_exists('seo_meta')) {
    /**
     * Generate HTML Head SEO Meta Tags, Canonical Link, Open Graph & Twitter Card Tags
     *
     * @param array $custom Overrides for title, description, keywords, canonical, image, robots, etc.
     * @return string Rendered HTML tags
     */
    function seo_meta(?array $custom = []): string {
        $cfg = seo_config();
        $custom = $custom ?? [];

        // Title resolution
        $rawTitle = $custom['title'] ?? $custom['seo_title'] ?? $cfg->defaultTitle;
        if (!empty($rawTitle) && !str_contains($rawTitle, $cfg->siteName)) {
            $metaTitle = esc($rawTitle) . $cfg->titleSeparator . esc($cfg->siteName);
        } else {
            $metaTitle = esc($rawTitle ?: $cfg->defaultTitle);
        }

        // Resolve Module-specific default description & keywords if not explicitly supplied
        $uri = service('uri');
        $segments = $uri->getSegments();
        $moduleKey = strtolower($segments[0] ?? '');
        $subKey    = strtolower($segments[1] ?? '');

        $moduleDescriptions = [
            'dashboard'  => 'Live accounting & business dashboard: real-time profit & loss, cash balances, sales velocity, customer receivables, and vendor payables in LenDen Cloud ERP.',
            'accounts'   => 'Double-entry Chart of Accounts, debit & credit general ledgers, trial balance reconciliation, and group hierarchies in LenDen Cloud ERP.',
            'journals'   => 'Double-entry journal vouchers with mathematical debit/credit balance validation in LenDen Cloud ERP.',
            'parties'    => 'Customer and supplier master directories, GSTIN registration status, ledger statements, and contact profiles in LenDen Cloud ERP.',
            'inventory'  => 'Multi-warehouse inventory catalog, SKU tracking, reorder levels, stock adjustments, and stock valuation in LenDen Cloud ERP.',
            'sales'      => 'GST-compliant sales tax invoices (CGST, SGST, IGST), quotations, sales order lifecycle, and customer payments in LenDen Cloud ERP.',
            'purchases'  => 'Vendor procurement orders, input tax credit (ITC) purchase bills, and accounts payable management in LenDen Cloud ERP.',
            'vouchers'   => 'Double-entry cash & bank payment vouchers, customer receipt vouchers, and contra vouchers in LenDen Cloud ERP.',
            'reports'    => 'Financial audit statements: real-time Balance Sheet, Profit & Loss, Trial Balance, Stock Ledger, and Day Book in LenDen Cloud ERP.',
            'companies'  => 'Multi-company portfolio management: 1-click workspace switcher, subsidiary ledgers, and branch governance in LenDen Cloud ERP.',
        ];

        $moduleKeywords = [
            'dashboard'  => 'accounting dashboard, business metrics, financial KPIs, cloud ERP, cash flow tracking',
            'accounts'   => 'chart of accounts, general ledger, double entry ledgers, debit credit accounting, trial balance',
            'journals'   => 'journal voucher, journal entry, double entry bookkeeping, accounting adjustment',
            'parties'    => 'customer directory, vendor master, party ledger, GST accounts receivable',
            'inventory'  => 'warehouse inventory, stock management, stock adjustments, inventory valuation, SKU tracking',
            'sales'      => 'GST invoice, sales order, tax invoice generator, CGST SGST IGST, billing software',
            'purchases'  => 'purchase bills, vendor bills, procurement ERP, accounts payable, input tax credit',
            'vouchers'   => 'payment voucher, receipt voucher, contra voucher, bank cash voucher',
            'reports'    => 'balance sheet, profit and loss, trial balance, stock report, day book, financial statements',
            'companies'  => 'multi company ERP, branch management, holding subsidiaries, conglomerate accounting',
        ];

        $fallbackDesc = $moduleDescriptions[$moduleKey] ?? $cfg->defaultDescription;
        $fallbackKeywords = $moduleKeywords[$moduleKey] ?? $cfg->defaultKeywords;

        // Meta Description & Keywords
        $metaDescription = esc(trim($custom['description'] ?? $custom['meta_description'] ?? $fallbackDesc));
        $metaKeywords = esc(trim($custom['keywords'] ?? $custom['meta_keywords'] ?? $fallbackKeywords));
        $author = esc($custom['author'] ?? $cfg->author);

        // Canonical & Robots
        $canonical = $custom['canonical'] ?? seo_canonical_url($custom['path'] ?? null);
        $robots = $custom['robots'] ?? ($custom['is_protected'] ?? false ? $cfg->robotsProtected : $cfg->robotsDefault);

        // Social / Open Graph & Twitter
        $ogType = esc($custom['og_type'] ?? $cfg->ogType);
        $ogTitle = esc($custom['og_title'] ?? $rawTitle ?? $cfg->defaultTitle);
        $ogDesc = esc($custom['og_description'] ?? $metaDescription);
        $ogImageRel = $custom['og_image'] ?? $cfg->ogImage;
        $ogImageUrl = (str_starts_with($ogImageRel, 'http://') || str_starts_with($ogImageRel, 'https://'))
            ? $ogImageRel
            : base_url(ltrim($ogImageRel, '/'));
        $ogImageAlt = esc($custom['og_image_alt'] ?? $cfg->ogImageAlt);
        $twitterCard = esc($custom['twitter_card'] ?? $cfg->twitterCard);
        $twitterSite = esc($custom['twitter_site'] ?? $cfg->twitterSite);
        $locale = esc($custom['locale'] ?? $cfg->locale);

        $html = [];
        $html[] = '<!-- Primary Meta Tags -->';
        $html[] = '<title>' . $metaTitle . '</title>';
        $html[] = '<meta name="title" content="' . $metaTitle . '">';
        $html[] = '<meta name="description" content="' . $metaDescription . '">';
        if (!empty($metaKeywords)) {
            $html[] = '<meta name="keywords" content="' . $metaKeywords . '">';
        }
        $html[] = '<meta name="author" content="' . $author . '">';
        $html[] = '<meta name="robots" content="' . esc($robots) . '">';
        $html[] = '<meta name="googlebot" content="' . esc($robots) . '">';
        $html[] = '<link rel="canonical" href="' . esc($canonical) . '">';

        // Google Search Console Verification (injected if configured and not already rendered)
        $gscTag = google_search_console([
            'token'   => $custom['google_site_verification'] ?? $custom['google_search_console'] ?? null,
            'enabled' => $custom['google_search_console_enabled'] ?? null,
        ]);
        if (!empty($gscTag)) {
            foreach (explode("\n", trim($gscTag)) as $line) {
                if (trim($line) !== '') {
                    $html[] = trim($line);
                }
            }
        }

        $html[] = '';
        $html[] = '<!-- Open Graph / Facebook / LinkedIn / WhatsApp -->';
        $html[] = '<meta property="og:type" content="' . $ogType . '">';
        $html[] = '<meta property="og:site_name" content="' . esc($cfg->siteName) . '">';
        $html[] = '<meta property="og:url" content="' . esc($canonical) . '">';
        $html[] = '<meta property="og:title" content="' . $ogTitle . '">';
        $html[] = '<meta property="og:description" content="' . $ogDesc . '">';
        $html[] = '<meta property="og:image" content="' . esc($ogImageUrl) . '">';
        $html[] = '<meta property="og:image:secure_url" content="' . esc($ogImageUrl) . '">';
        $html[] = '<meta property="og:image:width" content="' . (int)$cfg->ogImageWidth . '">';
        $html[] = '<meta property="og:image:height" content="' . (int)$cfg->ogImageHeight . '">';
        $html[] = '<meta property="og:image:alt" content="' . $ogImageAlt . '">';
        $html[] = '<meta property="og:locale" content="' . $locale . '">';

        $html[] = '';
        $html[] = '<!-- Twitter Cards -->';
        $html[] = '<meta name="twitter:card" content="' . $twitterCard . '">';
        $html[] = '<meta name="twitter:site" content="' . $twitterSite . '">';
        $html[] = '<meta name="twitter:creator" content="' . $twitterSite . '">';
        $html[] = '<meta name="twitter:url" content="' . esc($canonical) . '">';
        $html[] = '<meta name="twitter:title" content="' . $ogTitle . '">';
        $html[] = '<meta name="twitter:description" content="' . $ogDesc . '">';
        $html[] = '<meta name="twitter:image" content="' . esc($ogImageUrl) . '">';
        $html[] = '<meta name="twitter:image:alt" content="' . $ogImageAlt . '">';

        $html[] = '';
        $html[] = '<!-- Mobile & Progressive Web App Tags -->';
        $html[] = '<meta name="theme-color" content="' . esc($cfg->themeColor) . '">';
        $html[] = '<meta name="application-name" content="' . esc($cfg->siteName) . '">';
        $html[] = '<meta name="apple-mobile-web-app-title" content="' . esc($cfg->siteName) . '">';
        $html[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $html[] = '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">';
        $html[] = '<link rel="manifest" href="' . base_url('site.webmanifest') . '">';
        $html[] = '<link rel="icon" type="image/x-icon" href="' . base_url('favicon.ico') . '">';
        $html[] = '<link rel="apple-touch-icon" href="' . base_url('assets/images/apple-touch-icon.png') . '">';

        return implode("\n    ", $html);
    }
}

if (!function_exists('seo_schema')) {
    /**
     * Generate Schema.org JSON-LD structured data script
     *
     * @param string $type Schema type: 'organization', 'website', 'software_application', 'faq', 'pricing', 'breadcrumbs'
     * @param array $data Contextual payload for schema
     * @return string JSON-LD HTML script tag
     */
    function seo_schema(string $type, array $data = []): string {
        $cfg = seo_config();
        $payload = [];

        switch (strtolower($type)) {
            case 'organization':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type'    => 'Organization',
                    'name'     => $cfg->siteName,
                    'legalName'=> $cfg->siteName . ' Solutions Private Limited',
                    'url'      => rtrim(base_url(), '/'),
                    'logo'     => [
                        '@type'  => 'ImageObject',
                        'url'    => base_url('assets/images/logo.png'),
                        'width'  => 512,
                        'height' => 512,
                    ],
                    'description' => $cfg->defaultDescription,
                    'email'       => $cfg->contactEmail,
                    'telephone'   => $cfg->contactPhone,
                    'sameAs'      => $cfg->socialLinks,
                    'contactPoint' => [
                        [
                            '@type'             => 'ContactPoint',
                            'telephone'         => $cfg->contactPhone,
                            'contactType'       => 'customer service',
                            'email'             => $cfg->contactEmail,
                            'areaServed'        => 'IN',
                            'availableLanguage' => ['English', 'Hindi'],
                        ],
                    ],
                ];
                break;

            case 'website':
                $payload = [
                    '@context'      => 'https://schema.org',
                    '@type'         => 'WebSite',
                    'name'          => $cfg->siteName,
                    'url'           => rtrim(base_url(), '/'),
                    'description'   => $cfg->defaultDescription,
                    'inLanguage'    => 'en',
                    'publisher'     => [
                        '@type' => 'Organization',
                        'name'  => $cfg->siteName,
                    ],
                ];
                break;

            case 'software_application':
            case 'software':
                $payload = [
                    '@context'            => 'https://schema.org',
                    '@type'               => 'SoftwareApplication',
                    'name'                => $cfg->siteName,
                    'operatingSystem'     => $cfg->operatingSystem,
                    'applicationCategory' => $cfg->applicationCategory,
                    'softwareVersion'     => '2.4.0',
                    'description'         => $data['description'] ?? $cfg->defaultDescription,
                    'url'                 => rtrim(base_url(), '/'),
                    'author'              => [
                        '@type' => 'Organization',
                        'name'  => $cfg->siteName,
                    ],
                    'aggregateRating'     => [
                        '@type'       => 'AggregateRating',
                        'ratingValue' => '4.9',
                        'bestRating'  => '5',
                        'ratingCount' => '248',
                        'reviewCount' => '186',
                    ],
                    'offers'              => [
                        '@type'         => 'AggregateOffer',
                        'priceCurrency' => $cfg->currency,
                        'lowPrice'      => '0',
                        'highPrice'     => '2999',
                        'offerCount'    => '4',
                    ],
                    'featureList'         => [
                        'Double-Entry General Ledgers',
                        'GST Tax Invoicing (CGST, SGST, IGST)',
                        'Multi-Company Holding Portfolio Switching',
                        'Multi-Warehouse Inventory Control',
                        'Accounts Payable & Receivable Management',
                        'Instant Profit & Loss and Balance Sheets',
                        'Payment Receipts & Contra Vouchers',
                        'Razorpay Online Subscription Checkout',
                    ],
                ];
                break;

            case 'faq':
                $items = $data['items'] ?? [];
                $mainEntity = [];
                foreach ($items as $item) {
                    if (!empty($item['question']) && !empty($item['answer'])) {
                        $mainEntity[] = [
                            '@type'          => 'Question',
                            'name'           => strip_tags($item['question']),
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text'  => strip_tags($item['answer']),
                            ],
                        ];
                    }
                }

                $payload = [
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $mainEntity,
                ];
                break;

            case 'pricing':
            case 'offers':
                $plans = $data['plans'] ?? [];
                $offers = [];
                foreach ($plans as $p) {
                    $monthlyPrice = (float)($p['plan_monthly_price'] ?? 0);
                    $planName = $p['plan_name'] ?? 'Plan';
                    $offers[] = [
                        '@type'         => 'Offer',
                        'name'          => $planName,
                        'price'         => number_format($monthlyPrice, 2, '.', ''),
                        'priceCurrency' => $cfg->currency,
                        'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                        'availability'  => 'https://schema.org/InStock',
                        'description'   => $p['plan_description'] ?? ($planName . ' subscription plan for LenDen ERP'),
                        'url'           => base_url('pricing'),
                    ];
                }

                $payload = [
                    '@context' => 'https://schema.org',
                    '@type'    => 'Product',
                    'name'     => $cfg->siteName . ' Subscription Plans',
                    'description' => 'Scalable SaaS subscriptions for LenDen double-entry accounting and ERP suite.',
                    'brand'    => [
                        '@type' => 'Brand',
                        'name'  => $cfg->siteName,
                    ],
                    'offers'   => $offers,
                ];
                break;

            case 'breadcrumbs':
                $crumbs = $data['items'] ?? [];
                $itemList = [];
                $pos = 1;
                foreach ($crumbs as $crumb) {
                    $itemList[] = [
                        '@type'    => 'ListItem',
                        'position' => $pos++,
                        'name'     => $crumb['name'],
                        'item'     => (str_starts_with($crumb['url'], 'http://') || str_starts_with($crumb['url'], 'https://'))
                            ? $crumb['url']
                            : base_url(ltrim($crumb['url'], '/')),
                    ];
                }

                $payload = [
                    '@context'        => 'https://schema.org',
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => $itemList,
                ];
                break;

            case 'webpage':
                $payload = [
                    '@context'   => 'https://schema.org',
                    '@type'      => 'WebPage',
                    'name'       => $data['title'] ?? $cfg->defaultTitle,
                    'description'=> $data['description'] ?? $cfg->defaultDescription,
                    'url'        => $data['url'] ?? seo_canonical_url(),
                    'inLanguage' => 'en',
                    'isPartOf'   => [
                        '@type' => 'WebSite',
                        'name'  => $cfg->siteName,
                        'url'   => rtrim(base_url(), '/'),
                    ],
                ];
                break;
        }

        if (empty($payload)) {
            return '';
        }

        return '<script type="application/ld+json">' . "\n" . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n" . '</script>';
    }
}

if (!function_exists('seo_breadcrumbs_html')) {
    /**
     * Render semantic, accessible breadcrumb navigation bar
     *
     * @param array $crumbs Array of ['name' => '...', 'url' => '...']
     * @param bool $darkTheme Whether to use light text for dark hero backgrounds
     * @return string HTML Breadcrumb markup
     */
    function seo_breadcrumbs_html(array $crumbs, bool $darkTheme = true): string {
        if (empty($crumbs)) {
            return '';
        }

        $textClass = $darkTheme ? 'text-white-50' : 'text-muted';
        $activeClass = $darkTheme ? 'text-white active' : 'text-dark active';
        $dividerColor = $darkTheme ? 'rgba(255,255,255,0.4)' : 'rgba(0,0,0,0.4)';

        $html = [];
        $html[] = '<nav aria-label="breadcrumb" class="mb-3">';
        $html[] = '  <ol class="breadcrumb mb-0 small" style="--bs-breadcrumb-divider-color: ' . $dividerColor . ';">';

        $total = count($crumbs);
        $i = 1;
        foreach ($crumbs as $crumb) {
            $isLast = ($i === $total);
            $name = esc($crumb['name']);
            if ($isLast || empty($crumb['url'])) {
                $html[] = '    <li class="breadcrumb-item ' . $activeClass . '" aria-current="page">' . $name . '</li>';
            } else {
                $url = (str_starts_with($crumb['url'], 'http://') || str_starts_with($crumb['url'], 'https://'))
                    ? $crumb['url']
                    : base_url(ltrim($crumb['url'], '/'));
                $html[] = '    <li class="breadcrumb-item"><a href="' . esc($url) . '" class="' . $textClass . ' text-decoration-none">' . $name . '</a></li>';
            }
            $i++;
        }

        $html[] = '  </ol>';
        $html[] = '</nav>';

        return implode("\n", $html);
    }
}

if (!function_exists('analytics_config')) {
    /**
     * Get instance of Analytics configuration
     */
    function analytics_config(): \Config\Analytics {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = config('Analytics') ?? new \Config\Analytics();
        }
        return $cfg;
    }
}

if (!function_exists('google_analytics')) {
    /**
     * Render Google Analytics gtag.js script snippet
     *
     * Injected for all users (guests, prospects, demo users, tenant admins, accountants, staff)
     * EXCEPT Super Administrators (to keep developer/audit/management traffic out of production metrics).
     * Configurable in .env via GOOGLE_ANALYTICS_ID and GOOGLE_ANALYTICS_ENABLED.
     *
     * @param array $customOptions Optional overrides (id, exclude_super_admin, anonymize_ip, force, reset)
     * @return string Rendered HTML script tag or empty string
     */
    function google_analytics(array $customOptions = []): string {
        static $alreadyRendered = false;

        if (!empty($customOptions['reset'])) {
            $alreadyRendered = false;
            return '';
        }

        $force = (bool)($customOptions['force'] ?? false);
        if ($alreadyRendered && !$force) {
            return '';
        }

        $cfg = analytics_config();

        // 1. Verify tracking is enabled
        if (!$cfg->enabled) {
            return '';
        }

        // 2. Resolve Measurement ID
        $measurementId = trim((string)($customOptions['id'] ?? $cfg->googleAnalyticsId));
        if (empty($measurementId)) {
            return '';
        }

        // 3. Exclude Super Administrator if configured
        $excludeSuper = (bool)($customOptions['exclude_super_admin'] ?? $cfg->excludeSuperAdmin);
        if ($excludeSuper) {
            $isSuper = false;
            if (function_exists('is_super_admin')) {
                $isSuper = is_super_admin();
            } elseif (function_exists('session')) {
                $sess = session();
                if ($sess) {
                    $role = (string)($sess->get('user_role') ?? '');
                    $roles = (array)($sess->get('roles') ?? []);
                    $isSuper = ($sess->get('is_super_admin') === true)
                        || strcasecmp($role, 'super_admin') === 0
                        || in_array('super_admin', $roles, true);
                }
            }

            if ($isSuper) {
                return "<!-- Google Analytics: excluded for Super Administrator session -->\n";
            }
        }

        $alreadyRendered = true;
        $escapedId = esc($measurementId);

        $configOptions = [];
        if ($cfg->anonymizeIp || !empty($customOptions['anonymize_ip'])) {
            $configOptions['anonymize_ip'] = true;
        }

        $cookieDomain = isset($customOptions['cookie_domain'])
            ? trim((string)$customOptions['cookie_domain'])
            : trim((string)($cfg->cookieDomain ?? ''));
        if ($cookieDomain !== '') {
            $configOptions['cookie_domain'] = $cookieDomain;
        }

        $cookieFlags = isset($customOptions['cookie_flags'])
            ? trim((string)$customOptions['cookie_flags'])
            : trim((string)($cfg->cookieFlags ?? ''));
        if ($cookieFlags !== '') {
            $configOptions['cookie_flags'] = $cookieFlags;
        }

        $html = [];
        $html[] = '<!-- Google Analytics (gtag.js) -->';
        $html[] = '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $escapedId . '"></script>';
        $html[] = '<script>';
        $html[] = '  window.dataLayer = window.dataLayer || [];';
        $html[] = '  function gtag(){dataLayer.push(arguments);}';
        $html[] = "  gtag('js', new Date());";
        if (!empty($configOptions)) {
            $html[] = "  gtag('config', '{$escapedId}', " . json_encode($configOptions) . ");";
        } else {
            $html[] = "  gtag('config', '{$escapedId}');";
        }
        $html[] = '</script>';

        return implode("\n", $html) . "\n";
    }
}

if (!function_exists('google_search_console')) {
    /**
     * Render Google Search Console verification meta tag
     *
     * Configurable in .env via GOOGLE_SITE_VERIFICATION, GOOGLE_SEARCH_CONSOLE_CODE, etc.
     * and enabled via GOOGLE_SEARCH_CONSOLE_ENABLED (default true).
     *
     * Injected across all layouts, views, landing pages, authentication pages,
     * and demo / super admin dashboard areas.
     *
     * @param array $customOptions Optional overrides (token, code, enabled, force, reset)
     * @return string Rendered HTML meta tag or empty string
     */
    function google_search_console(array $customOptions = []): string {
        static $alreadyRendered = false;

        if (!empty($customOptions['reset'])) {
            $alreadyRendered = false;
            return '';
        }

        $force = (bool)($customOptions['force'] ?? false);
        if ($alreadyRendered && !$force) {
            return '';
        }

        $cfg = seo_config();

        // 1. Verify enabled
        $isEnabled = isset($customOptions['enabled'])
            ? (bool)$customOptions['enabled']
            : (bool)($cfg->googleSearchConsoleEnabled ?? true);

        if (!$isEnabled) {
            return '';
        }

        // 2. Resolve verification token
        $rawToken = null;
        if (!empty($customOptions['token'])) {
            $rawToken = $customOptions['token'];
        } elseif (!empty($customOptions['code'])) {
            $rawToken = $customOptions['code'];
        } elseif (!empty($customOptions['verification'])) {
            $rawToken = $customOptions['verification'];
        } else {
            $rawToken = $cfg->googleSiteVerification ?? '';
        }

        $token = trim((string)$rawToken);
        if (empty($token)) {
            return '';
        }

        // If a full tag was passed, extract content attribute value
        if (preg_match('/content=[\'"]([^\'"]+)[\'"]/i', $token, $matches)) {
            $token = trim($matches[1]);
        }
        $token = trim($token, " \t\n\r\0\x0B\"'<>");

        if (empty($token)) {
            return '';
        }

        $alreadyRendered = true;
        $escapedToken = esc($token);

        $html = [];
        $html[] = '<!-- Google Search Console Verification -->';
        $html[] = '<meta name="google-site-verification" content="' . $escapedToken . '">';

        return implode("\n", $html) . "\n";
    }
}

if (!function_exists('google_site_verification')) {
    /**
     * Alias for google_search_console()
     */
    function google_site_verification(array $customOptions = []): string {
        return google_search_console($customOptions);
    }
}



