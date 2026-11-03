<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Subscriptions extends BaseConfig
{
    /**
     * Master switch to enable or disable online subscriptions and tenant onboarding.
     * When false:
     * - Landing page / registration blocks self-serve onboarding
     * - Customer portal displays a paused notification and pauses online checkout
     * - Super Admin portal displays a clear toggle status and provides a 1-click button to resume
     */
    public bool $enabled = true;

    public function __construct()
    {
        parent::__construct();

        $envVal = env('SUBSCRIPTIONS_ENABLED', env('subscriptions.enabled', null));
        if ($envVal !== null) {
            $this->enabled = filter_var($envVal, FILTER_VALIDATE_BOOLEAN);
        }
    }
}

