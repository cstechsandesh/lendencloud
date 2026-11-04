<?php

namespace App\Controllers;

use App\Core\CSController;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\CompanyModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;

abstract class BaseController extends CSController
{
    /**
     * Helpers that will be automatically loaded on class instantiation.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'number', 'text', 'cs', 'const', 'ini', 'seo'];

    /**
     * Session instance
     */
    protected $session;

    /**
     * Database connection instance
     *
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Active company context
     */
    protected int $companyId = 1;
    protected ?array $currentCompany = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = service('session');
        $this->db      = \Config\Database::connect();

        // Resolve active company from session or default multi-company context
        $this->companyId = (int)($this->session->get('company_id') ?? 1);
        $companyModel    = new CompanyModel();

        try {
            if ($this->companyId > 0) {
                $this->currentCompany = $companyModel->find($this->companyId);
            }

            if (!$this->currentCompany || ($this->currentCompany['company_status'] ?? '') !== 'active') {
                $this->currentCompany = $companyModel->getActiveCompany();
                $this->companyId      = (int)($this->currentCompany['company_id'] ?? 1);
                $this->session->set('company_id', $this->companyId);
            }
        } catch (\Throwable $e) {
            $this->currentCompany = [
                'company_id'     => 1,
                'company_name'   => 'LenDen Cloud',
                'company_status' => 'active',
            ];
        }
    }

    /**
     * Return active company ID
     */
    protected function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * Permission verification helper
     */
    protected function checkPermission(string $permission): void
    {
        // Bypass if current action is a CSA_ (Controller Service Action) method
        $router = service('router');
        $method = $router->methodName();
        if ($method && stripos($method, 'CSA_') === 0) {
            return;
        }

        $roles       = $this->session->get('roles')       ?? [];
        $permissions = $this->session->get('permissions') ?? [];

        if (in_array('super_admin', $roles, true)) {
            return; // Super admin has absolute access
        }

        if (!in_array($permission, $permissions, true)) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Security\Exceptions\SecurityException('Permission Denied: ' . $permission);
            }
            if ($this->request->isAJAX()) {
                $response = service('response');
                $response->setStatusCode(403)->setJSON(['error' => 'Permission Denied: ' . $permission])->send();
                exit;
            }
            $this->session->setFlashdata('error', 'Access Denied: You do not have permission for [' . $permission . ']');
            redirect()->to('/dashboard')->send();
            exit;
        }
    }

    /**
     * Plan feature verification helper
     */
    protected function checkPlanFeature(string $feature): void
    {
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return; // Super Admin has unrestricted access
        }

        $subModel = new SubscriptionModel();
        $sub = $subModel->getCompanySubscription($this->companyId);
        $planCode = $sub['plan_code'] ?? 'starter_business';
        $planName = $sub['plan_name'] ?? 'Starter Business';

        $status = $sub['subscription_status'] ?? 'trial';
        $endDate = $sub['subscription_end_date'] ?? null;
        $isExpired = ($status === 'expired' || $status === 'cancelled' || (!empty($endDate) && strtotime($endDate) < strtotime('today')));

        $featureTitle = SubscriptionPlanModel::getFeatureTitle($feature);
        $requiredPlan = SubscriptionPlanModel::getRequiredPlanForFeature($feature);

        if ($isExpired) {
            $msg = "Your subscription has expired. Please renew your plan to continue accessing {$featureTitle}.";
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Exceptions\SecurityException($msg);
            }
            if ($this->request->isAJAX()) {
                $res = service('response');
                $res->setStatusCode(403)->setJSON(['error' => $msg, 'upgrade_url' => base_url('subscription')])->send();
                exit;
            }
            $this->session->setFlashdata('error', $msg);
            redirect()->to('/subscription')->send();
            exit;
        }

        if (!SubscriptionPlanModel::planAllowsFeature($sub ?: $planCode, $feature)) {
            $msg = "The {$featureTitle} is restricted on your {$planName} tier. Please upgrade to {$requiredPlan} to unlock this module.";
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Exceptions\SecurityException($msg);
            }
            if ($this->request->isAJAX()) {
                $res = service('response');
                $res->setStatusCode(403)->setJSON(['error' => $msg, 'upgrade_url' => base_url('subscription')])->send();
                exit;
            }
            $this->session->setFlashdata('error', $msg);
            redirect()->to('/subscription')->send();
            exit;
        }
    }

    /**
     * Check if active company exceeds numeric resource quota (e.g. invoices, bills, products, warehouses)
     */
    protected function checkPlanQuota(string $resource): ?array
    {
        $roles = $this->session->get('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return null; // Super Admin has unrestricted quotas
        }

        $subModel = new SubscriptionModel();
        $quota = $subModel->checkQuota($this->companyId, $resource);

        if (!$quota['allowed']) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Exceptions\SecurityException($quota['message']);
            }
            if ($this->request->isAJAX()) {
                $res = service('response');
                $res->setStatusCode(403)->setJSON([
                    'error'       => $quota['message'],
                    'quota'       => $quota,
                    'upgrade_url' => base_url('subscription'),
                ])->send();
                exit;
            }

            $this->session->setFlashdata('error', $quota['message']);
            redirect()->to('/subscription')->send();
            exit;
        }

        return $quota;
    }

    /**
     * Standard view renderer with global application & multi-company context
     */
    protected function render(string $viewPath, array $data = []): string
    {
        $userId       = (int)($this->session->get('user_id') ?? 0);
        $roles        = $this->session->get('roles') ?? [];
        $isSuperAdmin = in_array('super_admin', $roles, true);

        // Fetch companies accessible by current user for switcher
        $companyModel = new CompanyModel();
        $allCompanies = $companyModel->getUserCompanies($userId, $isSuperAdmin);

        $hasCompanySwitcher = count($allCompanies) > 1 || $isSuperAdmin;

        // Fetch active company subscription & plan feature flags
        $subModel = new SubscriptionModel();
        $currentSub = $subModel->getCompanySubscription($this->companyId);
        $currentPlanCode = $currentSub['plan_code'] ?? 'starter_business';

        $planFeatures = [
            'sales'            => $isSuperAdmin || SubscriptionPlanModel::planAllowsFeature($currentSub ?: $currentPlanCode, 'sales'),
            'purchases'        => $isSuperAdmin || SubscriptionPlanModel::planAllowsFeature($currentSub ?: $currentPlanCode, 'purchases'),
            'inventory'        => $isSuperAdmin || SubscriptionPlanModel::planAllowsFeature($currentSub ?: $currentPlanCode, 'inventory'),
            'advanced_reports' => $isSuperAdmin || SubscriptionPlanModel::planAllowsFeature($currentSub ?: $currentPlanCode, 'advanced_reports'),
            'multi_company'    => $isSuperAdmin || SubscriptionPlanModel::planAllowsFeature($currentSub ?: $currentPlanCode, 'multi_company'),
        ];

        $isDemoUser = (bool)($this->session->get('is_demo_user') ?? false);

        // Inject global context
        $data['user_id']              = $userId;
        $data['is_demo_user']         = $isDemoUser;
        $data['user_name']            = $this->session->get('user_name') ?? 'User';
        $data['user_role']            = $this->session->get('user_role') ?? 'staff';
        $data['user_roles']           = $roles;
        $data['user_permissions']     = $this->session->get('permissions') ?? [];
        $data['is_super_admin']       = $isSuperAdmin;

        // Multi-company context
        $data['company_id']           = $this->companyId;
        $data['currentCompany']       = $this->currentCompany;
        $data['current_company']      = $this->currentCompany;
        $data['allCompanies']         = $allCompanies;
        $data['hasCompanySwitcher']   = $hasCompanySwitcher;

        // Subscription & Plan feature entitlements
        $data['current_subscription'] = $currentSub;
        $data['current_plan_code']    = $currentPlanCode;
        $data['plan_features']        = $planFeatures;

        $data['company_name']         = $this->currentCompany['company_name'] ?? \App\Models\SettingModel::getSetting('company_name', 'LenDen Enterprise');
        $data['currency_symbol']      = $this->currentCompany['company_currency_symbol'] ?? \App\Models\SettingModel::getSetting('currency_symbol', '₹');
        $data['date_format']          = \App\Models\SettingModel::getSetting('date_format', 'd/m/Y');

        return view($viewPath, $data);
    }
}
