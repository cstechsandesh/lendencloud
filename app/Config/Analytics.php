<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Analytics extends BaseConfig
{
    /**
     * Google Analytics Measurement ID (GA4 e.g. 'G-XXXXXXXXXX' or Universal Analytics 'UA-XXXXX-Y')
     */
    public string $googleAnalyticsId = '';

    /**
     * Master switch to enable or disable Google Analytics tracking.
     */
    public bool $enabled = true;

    /**
     * Whether to exclude Super Administrators from being tracked.
     * When true (default), super admin traffic, audits, and management actions are excluded.
     */
    public bool $excludeSuperAdmin = true;

    /**
     * Whether to anonymize IP addresses
     */
    public bool $anonymizeIp = false;

    public function __construct()
    {
        parent::__construct();

        // 1. Google Analytics Measurement ID
        $id = env('GOOGLE_ANALYTICS_ID', env('google.analytics.id', env('analytics.googleAnalyticsId', env('GA_MEASUREMENT_ID', $this->googleAnalyticsId))));
        if ($id !== null) {
            $this->googleAnalyticsId = trim((string)$id);
        }

        // 2. Master Enable / Disable
        $envEnabled = env('GOOGLE_ANALYTICS_ENABLED', env('google.analytics.enabled', env('analytics.enabled', null)));
        if ($envEnabled !== null) {
            $this->enabled = filter_var($envEnabled, FILTER_VALIDATE_BOOLEAN);
        }

        // 3. Exclude Super Admin Toggle
        $envExcludeSuper = env('GOOGLE_ANALYTICS_EXCLUDE_SUPERADMIN', env('google.analytics.excludeSuperAdmin', env('analytics.excludeSuperAdmin', null)));
        if ($envExcludeSuper !== null) {
            $this->excludeSuperAdmin = filter_var($envExcludeSuper, FILTER_VALIDATE_BOOLEAN);
        }

        // 4. IP Anonymization Toggle
        $envAnonymize = env('GOOGLE_ANALYTICS_ANONYMIZE_IP', env('google.analytics.anonymizeIp', env('analytics.anonymizeIp', null)));
        if ($envAnonymize !== null) {
            $this->anonymizeIp = filter_var($envAnonymize, FILTER_VALIDATE_BOOLEAN);
        }
    }
}

