<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
    /**
     * Razorpay API Key ID (Publishable Key)
     */
    public string $keyId = 'rzp_test_TZerMYqwyQJpH0';

    /**
     * Razorpay API Key Secret (Private Secret)
     */
    public string $keySecret = 'ednSIS95YM5lV9GbzP3Z3aX9';

    /**
     * Default payment currency
     */
    public string $currency = 'INR';

    /**
     * Display merchant / company name shown on checkout popup
     */
    public string $merchantName = 'LenDen Enterprise ERP';

    /**
     * Master toggle for Razorpay integration
     */
    public bool $enabled = true;

    /**
     * Webhook secret for incoming webhook event verification
     */
    public string $webhookSecret = '';

    public function __construct()
    {
        parent::__construct();

        // Load values from environment (.env)
        $this->keyId        = env('RAZORPAY_KEY_ID', env('razorpay.keyId', $this->keyId));
        $this->keySecret    = env('RAZORPAY_KEY_SECRET', env('razorpay.keySecret', $this->keySecret));
        $this->currency     = env('RAZORPAY_CURRENCY', env('razorpay.currency', $this->currency));
        $this->merchantName = env('RAZORPAY_MERCHANT_NAME', env('razorpay.merchantName', $this->merchantName));
        $this->webhookSecret= env('RAZORPAY_WEBHOOK_SECRET', env('razorpay.webhookSecret', $this->webhookSecret));

        $envEnabled = env('RAZORPAY_ENABLED', env('razorpay.enabled', null));
        if ($envEnabled !== null) {
            $this->enabled = filter_var($envEnabled, FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Check if Razorpay is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->enabled && !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Check if currently running with a test key
     */
    public function isTestMode(): bool
    {
        return str_starts_with($this->keyId, 'rzp_test_');
    }
}

