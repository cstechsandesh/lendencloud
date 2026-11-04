<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DemoFilter implements FilterInterface
{
    /**
     * Exempt routes that should never be blocked by demo restriction.
     * e.g. authentication, company switching, and tests.
     */
    protected array $exemptRoutes = [
        'login',
        'logout',
        'subscribe',
        'companies/switch',
        'test/demo-restriction',
        'migrations',
    ];

    /**
     * URL patterns for GET requests that trigger data mutations (delete, cancel, toggle, etc.)
     */
    protected array $getMutationPatterns = [
        '/\/delete(-|\/|$)/i',
        '/\/cancel(-|\/|$)/i',
        '/\/toggle(-|\/|$)/i',
    ];

    /**
     * Public demo sandbox accounts strictly pre-seeded credentials in Company 1
     */
    protected array $demoEmails = [
        'admin@lenden.com',
        'accountant@lenden.com',
        'sales@lenden.com',
        'purchase@lenden.com',
        'inventory@lenden.com',
        'viewer@lenden.com',
    ];

    protected array $demoUsernames = [
        'admin_demo',
        'accountant',
        'sales',
        'purchase',
        'inventory',
        'viewer',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('logged_in') && !$session->get('is_logged_in')) {
            return;
        }

        $userRole  = $session->get('user_role');
        $userRoles = $session->get('roles') ?? [];
        if ($userRole === 'super_admin' || in_array('super_admin', $userRoles, true)) {
            $session->set('is_demo_user', false);
            return;
        }

        $companyId     = (int)($session->get('company_id') ?? 1);
        $userEmail     = strtolower($session->get('user_email') ?? '');
        $username      = strtolower($session->get('user_username') ?? '');
        $sessionIsDemo = (bool)$session->get('is_demo_user');

        // 1. Trial companies registered by users have 100% full platform functionality
        $isTrialCompany = false;
        try {
            $db = \Config\Database::connect();
            $sub = $db->table('subscriptions')
                ->where('subscription_company_id', $companyId)
                ->orderBy('subscription_id', 'DESC')
                ->get()
                ->getFirstRow('array');
            if ($sub && ($sub['subscription_status'] ?? '') === 'trial' && $companyId > 1) {
                $isTrialCompany = true;
            }
        } catch (\Throwable $e) {
            $isTrialCompany = false;
        }

        if ($isTrialCompany) {
            $session->set('is_demo_user', false);
            return;
        }

        // 2. Identify if demo account
        $isDemo = $sessionIsDemo;
        if (!$isDemo && $companyId === 1 && (
            in_array($userEmail, $this->demoEmails, true) ||
            in_array($username, $this->demoUsernames, true)
        )) {
            $isDemo = true;
            $session->set('is_demo_user', true);
        }

        // If not a demo user, allow all operations
        if (!$isDemo) {
            return;
        }

        // Normalize URI path from the incoming request
        $uriPath = trim($request->getUri()->getPath(), '/');
        if (str_starts_with($uriPath, 'LenDen/public/')) {
            $uriPath = substr($uriPath, strlen('LenDen/public/'));
        } elseif (str_starts_with($uriPath, 'lenden/public/')) {
            $uriPath = substr($uriPath, strlen('lenden/public/'));
        } elseif (str_starts_with($uriPath, 'public/')) {
            $uriPath = substr($uriPath, strlen('public/'));
        }
        if (empty($uriPath) && function_exists('uri_string')) {
            $uriPath = uri_string();
        }

        // Check exempt routes
        foreach ($this->exemptRoutes as $exempt) {
            if ($uriPath === $exempt || str_starts_with($uriPath, $exempt . '/')) {
                return;
            }
        }

        $method = strtoupper($request->getMethod());
        $isMutation = false;

        // 1. Any mutating HTTP method (POST, PUT, DELETE, PATCH)
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $isMutation = true;
        }

        // 2. Any GET request matching mutating patterns (delete, cancel, toggle)
        if ($method === 'GET') {
            foreach ($this->getMutationPatterns as $pattern) {
                if (preg_match($pattern, '/' . $uriPath)) {
                    $isMutation = true;
                    break;
                }
            }
        }

        if ($isMutation) {
            $isPaymentRoute = (
                str_contains($uriPath, 'create-order') ||
                str_contains($uriPath, 'verify-payment') ||
                str_contains($uriPath, 'payments/') ||
                str_contains($uriPath, 'payment') ||
                str_contains($uriPath, 'subscription')
            );
            $message = $isPaymentRoute
                ? 'Demo Mode Notice: Online payments and subscription renewals are disabled for demo accounts.'
                : 'Demo Mode Notice: Data modifications are disabled for demo accounts. Changes were not saved.';

            $isJsonExpected = (
                $request->isAJAX() ||
                str_contains($request->getHeaderLine('Accept'), 'application/json') ||
                str_contains($request->getHeaderLine('Content-Type'), 'application/json') ||
                str_starts_with($uriPath, 'api/') ||
                str_starts_with($uriPath, 'ajax/')
            );

            if ($isJsonExpected) {
                $response = service('response');
                return $response->setStatusCode(403)->setJSON([
                    'status'  => 'error',
                    'demo'    => true,
                    'message' => $message,
                ]);
            }

            $session->setFlashdata('error', $message);

            $referer = $request->getServer('HTTP_REFERER');
            if ($referer && !str_contains($referer, 'login')) {
                return redirect()->to($referer)->withInput();
            }

            return redirect()->to(base_url('dashboard'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}

