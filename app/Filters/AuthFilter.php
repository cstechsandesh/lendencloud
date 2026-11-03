<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login to continue.');
        }

        // Ensure permissions and roles are present in session
        if (! session()->has('permissions') || ! session()->has('roles')) {
            $userId = session()->get('user_id');
            if ($userId) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($userId);
                if ($user) {
                    $permissions = $userModel->getUserPermissions($userId);
                    $roles = [$user['user_role']];
                    session()->set('roles', $roles);
                    session()->set('permissions', $permissions);
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
