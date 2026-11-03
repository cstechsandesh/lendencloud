<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;

/**
 * PlanFeatureFilter
 *
 * Enforces subscription plan tier restrictions for business modules
 * (purchases, inventory, advanced accounting reports, sales).
 *
 * Super Admin accounts bypass all tier restrictions.
 */
class PlanFeatureFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments  $arguments[0] is the feature key e.g. 'purchases', 'inventory', 'advanced_reports'
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        // Must be authenticated
        if (! $session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $roles = $session->get('roles') ?? [];
        // Super Admin bypasses all plan restrictions
        if (in_array('super_admin', $roles, true)) {
            return;
        }

        $companyId = (int)($session->get('company_id') ?? 1);
        $subModel = new SubscriptionModel();
        $sub = $subModel->getCompanySubscription($companyId);

        $feature = !empty($arguments[0]) ? trim($arguments[0]) : '';
        $featureTitle = SubscriptionPlanModel::getFeatureTitle($feature);
        $requiredPlan = SubscriptionPlanModel::getRequiredPlanForFeature($feature);

        // Check if subscription exists and is active / non-expired
        $status = $sub['subscription_status'] ?? 'trial';
        $endDate = $sub['subscription_end_date'] ?? null;
        $isExpired = ($status === 'expired' || $status === 'cancelled' || (!empty($endDate) && strtotime($endDate) < strtotime('today')));

        if ($isExpired) {
            $msg = "Your subscription has expired. Please renew your plan to continue accessing {$featureTitle}.";
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(403)->setJSON([
                    'error'       => $msg,
                    'upgrade_url' => base_url('subscription'),
                ]);
            }
            $session->setFlashdata('error', $msg);
            return redirect()->to('/subscription');
        }

        // Check tier feature entitlement
        $planCode = $sub['plan_code'] ?? 'starter_business';
        $planName = $sub['plan_name'] ?? 'Starter Business';

        if (!empty($feature) && !SubscriptionPlanModel::planAllowsFeature($sub ?: $planCode, $feature)) {
            $msg = "The {$featureTitle} is restricted on your {$planName} tier. Please upgrade to {$requiredPlan} to unlock this module.";
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(403)->setJSON([
                    'error'       => $msg,
                    'upgrade_url' => base_url('subscription'),
                ]);
            }
            $session->setFlashdata('error', $msg);
            return redirect()->to('/subscription');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}

