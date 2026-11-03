<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class SeoController extends BaseController
{
    /**
     * XML Sitemap generator for search engine crawlers
     */
    public function sitemap(): ResponseInterface
    {
        $cfg = seo_config();
        $pages = $cfg->publicPages;
        $now = date('Y-m-d');

        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml[] = '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $xml[] = '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
        $xml[] = '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        foreach ($pages as $p) {
            $loc = !empty($p['path']) ? base_url($p['path']) : rtrim(base_url(), '/');
            $priority = $p['priority'] ?? '0.8';
            $changefreq = $p['changefreq'] ?? 'weekly';

            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . '</loc>';
            $xml[] = '    <lastmod>' . $now . '</lastmod>';
            $xml[] = '    <changefreq>' . $changefreq . '</changefreq>';
            $xml[] = '    <priority>' . $priority . '</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return $this->response
            ->setContentType('application/xml', 'utf-8')
            ->setBody(implode("\n", $xml));
    }

    /**
     * Dynamic robots.txt
     */
    public function robots(): ResponseInterface
    {
        $sitemapUrl = base_url('sitemap.xml');

        $lines = [
            '# ====================================================================',
            '# LenDen Cloud ERP - Robots Exclusion Protocol',
            '# ====================================================================',
            '',
            'User-agent: *',
            '',
            '# Allow Public Landing & Authentication Pages',
            'Allow: /',
            'Allow: /features',
            'Allow: /multi-company',
            'Allow: /pricing',
            'Allow: /faq',
            'Allow: /login',
            'Allow: /subscribe',
            'Allow: /assets/',
            'Allow: /site.webmanifest',
            'Allow: /manifest.json',
            'Allow: /favicon.ico',
            'Allow: /sitemap.xml',
            'Allow: /google*.html',
            '',
            '# Allow Behind-Logged-In Business Modules (Enabled via Demo Mode)',
            'Allow: /dashboard',
            'Allow: /accounts',
            'Allow: /accounts/',
            'Allow: /journals',
            'Allow: /journals/',
            'Allow: /parties',
            'Allow: /parties/',
            'Allow: /inventory',
            'Allow: /inventory/',
            'Allow: /sales',
            'Allow: /sales/',
            'Allow: /purchases',
            'Allow: /purchases/',
            'Allow: /vouchers',
            'Allow: /vouchers/',
            'Allow: /reports',
            'Allow: /reports/',
            'Allow: /companies',
            'Allow: /companies/',
            '',
            '# Strictly Disallow Super Admin & Sensitive Management Areas ("Except super admin")',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /subscriptions',
            'Disallow: /subscriptions/',
            'Disallow: /subscription',
            'Disallow: /users',
            'Disallow: /users/',
            'Disallow: /roles',
            'Disallow: /roles/',
            'Disallow: /settings',
            'Disallow: /settings/',
            'Disallow: /payments',
            'Disallow: /payments/',
            'Disallow: /profile',
            'Disallow: /profile/',
            'Disallow: /ajax/',
            '',
            '# Sitemap Reference',
            'Sitemap: ' . $sitemapUrl,
        ];

        return $this->response
            ->setContentType('text/plain', 'utf-8')
            ->setBody(implode("\n", $lines));
    }

    /**
     * Google Search Console HTML File Verification Handler
     *
     * Serves expected verification string: "google-site-verification: google{code}.html"
     */
    public function googleVerification(string $filename): ResponseInterface
    {
        $cfg = seo_config();

        if (!$cfg->googleSearchConsoleEnabled) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        // If a specific verification file is configured in .env, enforce match
        $expected = trim($cfg->googleVerificationFile);
        if (!empty($expected) && strcasecmp($expected, $filename) !== 0) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        return $this->response
            ->setContentType('text/html', 'utf-8')
            ->setBody('google-site-verification: ' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'));
    }
}

