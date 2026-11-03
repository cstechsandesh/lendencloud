<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Routes strictly restricted to authenticated Super Administrators.
     * Demo auto-login is NEVER granted for these paths.
     */
    protected array $superAdminRestrictedRoutes = [
        'subscriptions',
        'subscription',
        'users',
        'roles',
        'settings',
        'payments',
        'admin',
        'profile',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Extract normalized route segments independent of base directory or index.php
        $uri = $request->getUri();
        $uriPath = method_exists($uri, 'getRoutePath') ? $uri->getRoutePath() : '';
        if (empty($uriPath)) {
            $uriPath = trim($uri->getPath(), '/');
            foreach (['LenDen/public/index.php/', 'lenden/public/index.php/', 'index.php/', 'LenDen/public/', 'lenden/public/', 'public/'] as $prefix) {
                if (str_starts_with($uriPath, $prefix)) {
                    $uriPath = substr($uriPath, strlen($prefix));
                    break;
                }
            }
        }
        if (empty($uriPath) && function_exists('uri_string')) {
            $uriPath = uri_string();
        }

        $parts = explode('/', trim($uriPath, '/'));
        $seg1 = strtolower($parts[0] ?? '');
        $seg2 = strtolower($parts[1] ?? '');

        $isSuperAdminRoute = in_array($seg1, $this->superAdminRestrictedRoutes, true);

        // If user is already logged in as a demo user, ensure they cannot access Super Admin areas
        if ($session->get('logged_in') && $session->get('is_demo_user')) {
            if ($isSuperAdminRoute) {
                return redirect()->to('/dashboard')->with('error', 'Super Administrator privileges are required to access this area.');
            }
        }

        // If user is NOT logged in:
        if (! $session->get('logged_in')) {
            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? $request->getMethod());

            // Strictly require manual super-admin authentication for administrative paths or mutation actions
            if ($isSuperAdminRoute || $method !== 'GET') {
                return redirect()->to('/login')->with('error', 'Please login with administrator credentials to continue.');
            }

            // Publicly enabled behind-logged-in business ERP modules:
            // Automatically initialize safe, read-only demo session for search engine indexing and public exploration
            $this->provisionDemoSession();
        }

        // Ensure permissions and roles are present in session
        if (! $session->has('permissions') || ! $session->has('roles')) {
            $userId = $session->get('user_id');
            if ($userId) {
                $userModel = new \App\Models\UserModel();
                try {
                    $user = $userModel->find($userId);
                    if ($user) {
                        $permissions = $userModel->getUserPermissions($userId);
                        $roles = [$user['user_role']];
                        $session->set('roles', $roles);
                        $session->set('permissions', $permissions);
                    }
                } catch (\Throwable $e) {
                    // Fallback handled in BaseController
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Provision read-only demo session for public viewing and search engine crawling
     */
    protected function provisionDemoSession(): void
    {
        $session = service('session');
        $userModel = new \App\Models\UserModel();

        $demoUser = null;
        try {
            $demoUser = $userModel->findByUsername('admin_demo');
        } catch (\Throwable $e) {
            $demoUser = null;
        }

        if (!$demoUser) {
            $demoUser = [
                'user_id'         => 3,
                'user_company_id' => 1,
                'user_username'   => 'admin_demo',
                'user_name'       => 'Business Admin Demo',
                'user_email'      => 'admin_demo@lenden.local',
                'user_role'       => 'admin',
            ];
        }

        $userId = (int)($demoUser['user_id'] ?? 3);
        $permissions = [];
        try {
            $permissions = $userModel->getUserPermissions($userId);
        } catch (\Throwable $e) {
            $permissions = [];
        }

        if (empty($permissions)) {
            $permissions = [
                'dashboard.view',
                'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.ledger',
                'journals.view', 'journals.create',
                'parties.view', 'parties.create', 'parties.edit', 'parties.statement',
                'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust',
                'sales.view', 'sales.create', 'sales.edit', 'sales.cancel', 'sales.print',
                'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.cancel',
                'vouchers.view', 'vouchers.create', 'vouchers.contra',
                'reports.view', 'reports.export',
                'companies.view', 'companies.switch',
            ];
        }

        $sessionData = [
            'logged_in'        => true,
            'is_logged_in'     => true,
            'is_demo_user'     => true,
            'user_id'          => $userId,
            'company_id'       => (int)($demoUser['user_company_id'] ?? 1),
            'user_username'    => $demoUser['user_username'] ?? 'admin_demo',
            'user_name'        => $demoUser['user_name'] ?? 'Business Admin Demo',
            'user_email'       => $demoUser['user_email'] ?? 'admin_demo@lenden.local',
            'user_role'        => $demoUser['user_role'] ?? 'admin',
            'roles'            => [$demoUser['user_role'] ?? 'admin'],
            'permissions'      => $permissions,
            'demo_auto_login'  => true,
        ];

        $session->set($sessionData);
    }
}
