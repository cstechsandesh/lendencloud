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

        // Meta Description & Keywords
        $metaDescription = esc(trim($custom['description'] ?? $custom['meta_description'] ?? $cfg->defaultDescription));
        $metaKeywords = esc(trim($custom['keywords'] ?? $custom['meta_keywords'] ?? $cfg->defaultKeywords));
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

