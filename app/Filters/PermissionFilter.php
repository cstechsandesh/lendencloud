<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * PermissionFilter
 *
 * Checks whether the authenticated user holds a specific permission.
 * Usage in routes: ['filter' => 'permission:module.action']
 *
 * super_admin role bypasses all permission checks.
 */
class PermissionFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments  First element is the required permission string.
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ensure user is authenticated first
        if (! session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Methods prefixed with CSA_ (Controller Service Action) bypass permission check
        $router = service('router');
        $method = $router->methodName();
        if ($method && stripos($method, 'CSA_') === 0) {
            return;
        }

        if ($arguments) {
            $permission  = $arguments[0];
            $roles       = session()->get('roles')       ?? [];
            $permissions = session()->get('permissions') ?? [];

            // super_admin bypasses all permission checks
            if (! in_array('super_admin', $roles) && ! in_array($permission, $permissions)) {
                if ($request->isAJAX()) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setJSON(['error' => 'Forbidden']);
                }

                return redirect()->back()
                    ->with('error', 'You do not have permission to perform this action.');
            }
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}
