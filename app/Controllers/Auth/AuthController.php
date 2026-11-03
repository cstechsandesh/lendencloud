<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\SubscriptionModel;
use App\Models\SubscriptionPlanModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', [
            'title'        => 'Sign In — LenDen Cloud',
            'company_name' => \App\Models\SettingModel::getSetting('company_name', 'LenDen Cloud'),
            'seo'          => [
                'title'       => 'Sign In to Workspace — LenDen Cloud ERP',
                'description' => 'Log in to your secure LenDen Cloud accounting workspace. Access financial ledgers, GST invoices, inventory management, and business reports.',
                'canonical'   => seo_canonical_url('login'),
                'robots'      => 'index, follow',
            ],
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');

        $user = $this->userModel->findByUsername($username);

        if (! $user || $user['user_status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Invalid username or account is inactive.');
        }

        if (! password_verify($password, $user['user_password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }

        // Get permissions from UserModel
        $permissions = $this->userModel->getUserPermissions((int)$user['user_id']);
        $roles = [$user['user_role']];

        $userCompanyId = (int)($user['user_company_id'] ?? 1);
        if ($userCompanyId <= 0) {
            $userCompanyId = 1;
        }

        // Detect demonstration accounts (Super Admin & trial companies are NEVER demo accounts)
        $isSuperAdmin = ($user['user_role'] === 'super_admin' || in_array('super_admin', $roles, true));

        $demoEmails = [
            'admin@lenden.com',
            'accountant@lenden.com',
            'sales@lenden.com',
            'purchase@lenden.com',
            'inventory@lenden.com',
            'viewer@lenden.com',
        ];
        $demoUsernames = [
            'admin_demo',
            'accountant',
            'sales',
            'purchase',
            'inventory',
            'viewer',
        ];

        $userEmail    = strtolower($user['user_email'] ?? '');
        $userUsername = strtolower($user['user_username'] ?? '');

        $isTrialCompany = false;
        try {
            $subModel = new \App\Models\SubscriptionModel();
            $sub = $subModel->getCompanySubscription($userCompanyId);
            if ($sub && ($sub['subscription_status'] ?? '') === 'trial' && $userCompanyId > 1) {
                $isTrialCompany = true;
            }
        } catch (\Throwable $e) {
            $isTrialCompany = false;
        }

        $isDemoUser = !$isSuperAdmin && !$isTrialCompany && (
            !empty($user['user_is_demo']) ||
            ($userCompanyId === 1 && (in_array($userEmail, $demoEmails, true) || in_array($userUsername, $demoUsernames, true)))
        );

        $sessionData = [
            'logged_in'        => true,
            'is_logged_in'     => true,
            'is_demo_user'     => $isDemoUser,
            'user_id'          => (int)$user['user_id'],
            'company_id'       => $userCompanyId,
            'user_username'    => $user['user_username'],
            'user_name'        => $user['user_name'],
            'user_email'       => $user['user_email'] ?? '',
            'user_role'        => $user['user_role'],
            'roles'            => $roles,
            'permissions'      => $permissions,
        ];

        $this->session->set($sessionData);
        $this->session->regenerate(true);

        if (!$isDemoUser) {
            $this->userModel->update($user['user_id'], ['user_last_login' => date('Y-m-d H:i:s')]);
        }

        if ($isDemoUser) {
            $this->session->setFlashdata('success', 'Welcome to Demo Mode, ' . esc($user['user_name']) . '! You can explore all LenDen modules freely. Notice: Data changes cannot be saved in demo mode.');
        } else {
            $this->session->setFlashdata('success', 'Welcome back, ' . esc($user['user_name']) . '!');
        }

        return redirect()->to('/dashboard');
    }

    /**
     * Dedicated Subscription / Free Trial Registration Page
     */
    public function subscribe()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $planModel = new SubscriptionPlanModel();
        $plans     = $planModel->getActivePlans();

        $selectedPlanCode = trim($this->request->getGet('plan') ?? 'growth_enterprise');
        $selectedCycle    = trim($this->request->getGet('cycle') ?? 'monthly');
        if (!in_array($selectedCycle, ['monthly', 'yearly'], true)) {
            $selectedCycle = 'monthly';
        }

        $selectedPlanId = 2; // Default: Growth Enterprise
        foreach ($plans as $p) {
            if ($p['plan_code'] === $selectedPlanCode || (string)$p['plan_id'] === $selectedPlanCode) {
                $selectedPlanId = (int)$p['plan_id'];
                break;
            }
        }

        $subscriptionsConfig  = config('Subscriptions');
        $subscriptionsEnabled = (bool)($subscriptionsConfig->enabled ?? true);

        return view('auth/subscribe', [
            'title'                => 'Start Your 30-Day Free Trial — LenDen Cloud',
            'plans'                => $plans,
            'selectedPlanId'       => $selectedPlanId,
            'selectedCycle'        => $selectedCycle,
            'subscriptionsEnabled' => $subscriptionsEnabled,
            'seo'                  => [
                'title'       => 'Start Your 30-Day Free Trial — LenDen Cloud ERP',
                'description' => 'Provision your company workspace instantly with a 30-day unrestricted free trial. Experience complete double-entry accounting, GST invoicing, and multi-company management in the cloud.',
                'canonical'   => seo_canonical_url('subscribe'),
                'robots'      => 'index, follow',
            ],
        ]);
    }

    /**
     * Process Self-Serve Subscription & Company Onboarding
     */
    public function processSubscribe()
    {
        $subscriptionsConfig = config('Subscriptions');
        if (empty($subscriptionsConfig->enabled)) {
            return redirect()->to('/subscribe')->with('error', 'Online self-serve registrations are temporarily paused. Please contact platform support.');
        }

        $companyName = trim($this->request->getPost('company_name') ?? '');
        $companyCode = strtoupper(trim($this->request->getPost('company_code') ?? ''));
        $gstin       = strtoupper(trim($this->request->getPost('company_gstin') ?? ''));
        $city        = trim($this->request->getPost('company_city') ?: 'Mumbai');
        $state       = trim($this->request->getPost('company_state') ?: 'Maharashtra');
        $adminName   = trim($this->request->getPost('admin_name') ?? '');
        $adminEmail  = strtolower(trim($this->request->getPost('admin_email') ?? ''));
        $adminPhone  = trim($this->request->getPost('admin_phone') ?? '');
        $username    = trim($this->request->getPost('username') ?? '');
        $password    = $this->request->getPost('password') ?? '';
        $planId      = (int)($this->request->getPost('plan_id') ?? 2);
        $cycle       = ($this->request->getPost('billing_cycle') === 'yearly') ? 'yearly' : 'monthly';

        // Validation
        if (empty($companyName) || empty($adminName) || empty($adminEmail) || empty($username) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Please fill all required company and administrator details.');
        }

        if (strlen($password) < 6) {
            return redirect()->back()->withInput()->with('error', 'Password must be at least 6 characters long.');
        }

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid business email address.');
        }

        // Generate company code if not provided
        if (empty($companyCode)) {
            $words = preg_split('/[\s_-]+/', $companyName);
            $initials = '';
            foreach ($words as $w) {
                if (!empty($w)) $initials .= strtoupper($w[0]);
            }
            $companyCode = substr($initials ?: 'COMP', 0, 6) . rand(10, 99);
        }

        // Check for existing user with same username or email
        $db = \Config\Database::connect();
        $existing = $db->table('users')
            ->where('user_username', $username)
            ->orWhere('user_email', $adminEmail)
            ->get()->getRowArray();

        if ($existing) {
            if ($existing['user_username'] === $username) {
                return redirect()->back()->withInput()->with('error', "Username '{$username}' is already taken. Please choose another username.");
            }
            return redirect()->back()->withInput()->with('error', "An account with email '{$adminEmail}' already exists. Please sign in directly.");
        }

        $planModel = new SubscriptionPlanModel();
        $plan = $planModel->find($planId);
        if (!$plan) {
            $plan = $planModel->first();
            $planId = (int)$plan['plan_id'];
        }

        $amount = ($cycle === 'yearly') ? (float)$plan['plan_price_per_year'] : (float)$plan['plan_price_per_month'];
        $now = date('Y-m-d H:i:s');

        try {
            $db->transException(true)->transStart();

            // 1. Create Company
            $companyModel = new CompanyModel();
            $companyId = $companyModel->insert([
                'company_name'                 => $companyName,
                'company_code'                 => $companyCode,
                'company_gstin'                => $gstin,
                'company_city'                 => $city,
                'company_state'                => $state,
                'company_phone'                => $adminPhone,
                'company_email'                => $adminEmail,
                'company_currency_symbol'      => '₹',
                'company_financial_year_start' => '04-01',
                'company_status'               => 'active',
            ]);

            // 2. Provision starter Chart of Accounts, warehouse, and GST tax slabs
            $companyModel->provisionDefaultEntities((int)$companyId);

            // 3. Create Subscription (30-Day Free Trial)
            $subModel = new SubscriptionModel();
            $subModel->insert([
                'subscription_company_id'     => (int)$companyId,
                'subscription_plan_id'        => (int)$planId,
                'subscription_billing_cycle'  => $cycle,
                'subscription_amount'         => $amount,
                'subscription_start_date'     => date('Y-m-d'),
                'subscription_end_date'       => date('Y-m-d', strtotime('+30 days')),
                'subscription_status'         => 'trial',
                'subscription_payment_method' => 'complimentary_trial',
            ]);

            // 4. Resolve 'admin' role
            $adminRole = $db->table('roles')->where('role_key', 'admin')->get()->getRowArray();
            $roleId = $adminRole ? (int)$adminRole['role_id'] : 2;

            // 5. Create Administrator User
            $userId = $this->userModel->insert([
                'user_company_id'    => (int)$companyId,
                'user_role_id'       => $roleId,
                'user_role'          => 'admin',
                'user_username'      => $username,
                'user_name'          => $adminName,
                'user_email'         => $adminEmail,
                'user_phone'         => $adminPhone,
                'user_password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'user_designation'   => 'Business Administrator',
                'user_status'        => 'active',
            ]);

            // 6. Link user in user_companies and user_roles
            $db->table('user_companies')->insert([
                'uc_user_id'    => (int)$userId,
                'uc_company_id' => (int)$companyId,
                'created_at'    => $now,
            ]);

            $db->table('user_roles')->ignore(true)->insert([
                'ur_user_id' => (int)$userId,
                'ur_role_id' => $roleId,
                'created_at' => $now,
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            log_message('error', 'Self-Serve Subscription Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }

        // Fetch permissions for the new admin
        $permissions = $this->userModel->getUserPermissions((int)$userId);

        // Auto-login newly created admin
        $sessionData = [
            'logged_in'        => true,
            'user_id'          => (int)$userId,
            'company_id'       => (int)$companyId,
            'user_username'    => $username,
            'user_name'        => $adminName,
            'user_email'       => $adminEmail,
            'user_role'        => 'admin',
            'roles'            => ['admin'],
            'permissions'      => $permissions,
        ];
        $this->session->set($sessionData);
        $this->session->regenerate(true);

        return redirect()->to('/dashboard')->with(
            'success',
            "Congratulations! \"{$companyName}\" has been successfully provisioned on the {$plan['plan_name']} (30-Day Free Trial). Welcome to LenDen Enterprise!"
        );
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }
}
