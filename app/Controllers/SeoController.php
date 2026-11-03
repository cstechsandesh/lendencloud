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
            '# LenDen Enterprise ERP - Robots Exclusion Protocol',
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
            '',
            '# Disallow Private Authenticated Financial & ERP Application Workspaces',
            'Disallow: /dashboard',
            'Disallow: /dashboard/',
            'Disallow: /accounts',
            'Disallow: /accounts/',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /companies',
            'Disallow: /companies/',
            'Disallow: /users',
            'Disallow: /users/',
            'Disallow: /roles',
            'Disallow: /roles/',
            'Disallow: /subscriptions',
            'Disallow: /subscriptions/',
            'Disallow: /subscription',
            'Disallow: /payments',
            'Disallow: /payments/',
            'Disallow: /inventory',
            'Disallow: /inventory/',
            'Disallow: /purchases',
            'Disallow: /purchases/',
            'Disallow: /sales',
            'Disallow: /sales/',
            'Disallow: /reports',
            'Disallow: /reports/',
            'Disallow: /vouchers',
            'Disallow: /vouchers/',
            'Disallow: /settings',
            'Disallow: /settings/',
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
}

