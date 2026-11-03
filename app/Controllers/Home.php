<?php

namespace App\Controllers;

use App\Models\SubscriptionPlanModel;

class Home extends BaseController
{
    /**
     * Homepage Overview
     */
    public function index(): string
    {
        $planModel = new SubscriptionPlanModel();
        $subscriptionsConfig = config('Subscriptions');

        try {
            $plans = $planModel->getActivePlans();
        } catch (\Throwable $e) {
            $plans = [];
        }
        $subscriptionsEnabled = (bool)($subscriptionsConfig->enabled ?? true);

        return view('landing/index', [
            'title'                => 'LenDen Cloud — Cloud Accounting & Multi-Company ERP',
            'activeNav'            => 'home',
            'plans'                => $plans,
            'subscriptionsEnabled' => $subscriptionsEnabled,
            'seo'                  => [
                'title'            => 'LenDen Cloud — Cloud Accounting & Multi-Company ERP',
                'description'      => 'Modern cloud double-entry accounting, GST invoicing, multi-warehouse inventory, and multi-company ERP platform. Start your 30-day free trial today.',
                'keywords'         => 'accounting software, cloud accounting, double entry accounting, GST billing software, multi company ERP, business accounting suite, inventory management, balance sheet software',
                'canonical'        => seo_canonical_url('/'),
                'og_type'          => 'website',
            ],
            'breadcrumbs'          => [
                ['name' => 'Home', 'url' => '/'],
            ],
        ]);
    }

    /**
     * Core Modules & Capabilities
     */
    public function features(): string
    {
        return view('landing/features', [
            'title'     => 'Core Modules & Accounting Capabilities — LenDen Cloud',
            'activeNav' => 'features',
            'seo'       => [
                'title'            => 'Core Modules & Accounting Capabilities — Invoicing, Ledgers & Inventory',
                'description'      => 'Explore LenDen Cloud\'s core modules: automated GST tax invoicing, formal double-entry general ledgers, multi-warehouse stock management, and real-time financial reporting.',
                'keywords'         => 'cloud accounting modules, GST invoice generator, general ledger software, stock inventory ERP, profit and loss balance sheet, accounts receivable, purchase bills',
                'canonical'        => seo_canonical_url('features'),
                'og_type'          => 'website',
            ],
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Core Modules', 'url' => 'features'],
            ],
        ]);
    }

    /**
     * Multi-Company & Holding Portfolio Architecture
     */
    public function multiCompany(): string
    {
        return view('landing/multi_company', [
            'title'     => 'Multi-Company Architecture & Subsidiary Governance — LenDen Cloud',
            'activeNav' => 'multi-company',
            'seo'       => [
                'title'            => 'Multi-Company Architecture & Subsidiary Governance',
                'description'      => 'Operate multiple distinct legal entities, holding groups, and subsidiary branch locations from a single unified cloud portal with 1-click workspace switching and ledger segregation.',
                'keywords'         => 'multi company accounting software, cloud ERP, subsidiary management, multi branch ledgers, group consolidation, conglomerate accounting',
                'canonical'        => seo_canonical_url('multi-company'),
                'og_type'          => 'website',
            ],
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Multi-Company Architecture', 'url' => 'multi-company'],
            ],
        ]);
    }

    /**
     * SaaS Pricing Plans & Quotas Matrix
     */
    public function pricing(): string
    {
        $planModel = new SubscriptionPlanModel();
        $subscriptionsConfig = config('Subscriptions');

        try {
            $plans = $planModel->getActivePlans();
        } catch (\Throwable $e) {
            $plans = [];
        }
        $subscriptionsEnabled = (bool)($subscriptionsConfig->enabled ?? true);

        return view('landing/pricing', [
            'title'                => 'Cloud Pricing Plans & Feature Quotas — LenDen Cloud',
            'activeNav'            => 'pricing',
            'plans'                => $plans,
            'subscriptionsEnabled' => $subscriptionsEnabled,
            'seo'                  => [
                'title'            => 'Transparent Cloud Pricing Plans & Feature Quotas',
                'description'      => 'Transparent, scalable cloud pricing plans for single businesses, growing startups, and multi-company operations. 30-day unrestricted free trial with no credit card required.',
                'keywords'         => 'cloud ERP pricing, accounting software plans, business software subscription, affordable cloud accounting India, multi company subscription',
                'canonical'        => seo_canonical_url('pricing'),
                'og_type'          => 'website',
            ],
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Pricing Plans', 'url' => 'pricing'],
            ],
        ]);
    }

    /**
     * FAQ & Knowledge Support
     */
    public function faq(): string
    {
        $faqItems = [
            [
                'category' => 'Free Trial & Getting Started',
                'question' => 'How does the 30-Day Free Trial work?',
                'answer'   => 'When you register your business on LenDen Cloud, your workspace is provisioned instantly with a 30-day unrestricted trial of your chosen plan. No credit card is required. You get full access to all double-entry ledgers, invoicing, inventory, and reports from day one.',
            ],
            [
                'category' => 'Free Trial & Getting Started',
                'question' => 'What happens after the 30-day trial ends?',
                'answer'   => 'Your data remains safely stored and intact in the cloud. You can activate your plan subscription anytime via monthly or annual billing. If you need additional evaluation time, our administration team can extend your trial.',
            ],
            [
                'category' => 'Free Trial & Getting Started',
                'question' => 'Can I import my existing customer, vendor, and product lists?',
                'answer'   => 'Yes. You can import parties (Customers & Vendors) and inventory items (SKUs, opening stock, purchase/sales rates) via structured Excel/CSV templates during setup.',
            ],
            [
                'category' => 'Multi-Company Architecture',
                'question' => 'Can I manage multiple companies under one subscription?',
                'answer'   => 'Yes! With our Multi-Company Portfolio and Corporate Conglomerate tiers, you can manage up to 5 or unlimited distinct legal business entities and branch subsidiaries with complete ledger isolation.',
            ],
            [
                'category' => 'Multi-Company Architecture',
                'question' => 'How does workspace context switching work?',
                'answer'   => 'The top header displays an active company selector. Clicking any subsidiary switches the system context immediately. All menus, invoices, balances, and reports seamlessly filter to the selected company.',
            ],
            [
                'category' => 'Multi-Company Architecture',
                'question' => 'Can different users have different permissions per company?',
                'answer'   => 'Yes. LenDen Cloud uses granular Role-Based Access Control (RBAC). A user can be assigned as an administrator in one company and an order entry clerk in another company without cross-polluting permissions.',
            ],
            [
                'category' => 'GST Compliance & Accounting Standards',
                'question' => 'Is LenDen Cloud compliant with Indian GST tax laws?',
                'answer'   => 'Yes. LenDen Cloud comes pre-configured with standard GST slabs (0%, 5%, 12%, 18%, 28%), supports IGST, CGST, and SGST breakdowns, tracks HSN/SAC codes, and prints formal GST tax invoices with tax breakdown tables.',
            ],
            [
                'category' => 'GST Compliance & Accounting Standards',
                'question' => 'How does double-entry bookkeeping work in LenDen Cloud?',
                'answer'   => 'Every financial action (sales invoices, vendor bills, receipt vouchers, payment vouchers) automatically records double-entry journal lines. Debits always equal credits, ensuring your Balance Sheet and Trial Balance remain in mathematical harmony.',
            ],
        ];

        return view('landing/faq', [
            'title'       => 'Frequently Asked Questions & Help Center — LenDen Cloud',
            'activeNav'   => 'faq',
            'faqItems'    => $faqItems,
            'seo'         => [
                'title'       => 'Frequently Asked Questions & Knowledge Help Center',
                'description' => 'Get immediate answers to common questions regarding LenDen Cloud double-entry accounting, 30-day free trial onboarding, multi-company architecture, GST tax invoicing, and data security.',
                'keywords'    => 'LenDen Cloud FAQ, accounting software questions, trial setup, GST invoicing help, cloud ERP multi company guide, double-entry tutorial',
                'canonical'   => seo_canonical_url('faq'),
                'og_type'     => 'website',
            ],
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'FAQ & Knowledge Center', 'url' => 'faq'],
            ],
        ]);
    }
}
