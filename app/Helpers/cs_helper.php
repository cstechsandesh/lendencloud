<?php

/**
 * Check whether User logged in or not
 */
if (!function_exists('IsLoggedIn')) {
    function IsLoggedIn(): bool {
        if (session()->has('is_logged_in') && session('is_logged_in')) {
            return true;
        }
        return session()->has('isLoggedIn') ? boolval(session('isLoggedIn')) : false;
    }
}

/**
 * Check whether session is locked or not
 */
if (!function_exists('IsLocked')) {
    function IsLocked(): bool {
        return session()->has('isLocked') ? boolval(session('isLocked')) : false;
    }
}

/**
 * Check whether User is SuperAdmin
 */
if (!function_exists('IsAdmin')) {
    function IsAdmin(): bool {
        $roles = session('roles') ?? [];
        if (in_array('super_admin', $roles, true)) {
            return true;
        }
        return session()->has('role_id') && session('role_id') == '1';
    }
}


if (!function_exists('MergeCompanySessData')) {
    function MergeCompanySessData($field, &$data) {
        if (session()->has('company_id')) {
            if (!key_exists($field, $data) || empty($data[$field])) {
                $data = array_merge($data, [$field => session('company_id')]);
            }
        }
    }
}
/**
 * Check whether current logged-in user is a demo user
 */
if (!function_exists('is_demo_user')) {
    function is_demo_user(): bool {
        return boolval(session('is_demo_user') ?? false);
    }
}

/**
 * Check whether current authenticated user is a Super Administrator
 */
if (!function_exists('is_super_admin')) {
    function is_super_admin(): bool {
        if (!function_exists('session')) {
            return false;
        }

        $session = session();
        if (!$session) {
            return false;
        }

        // Demo users are never super admin
        if ($session->get('is_demo_user')) {
            return false;
        }

        if ($session->get('is_super_admin') === true) {
            return true;
        }

        $userRole = (string)($session->get('user_role') ?? '');
        if (strcasecmp($userRole, 'super_admin') === 0 || strcasecmp($userRole, 'superadmin') === 0) {
            return true;
        }

        $roles = (array)($session->get('roles') ?? []);
        if (in_array('super_admin', $roles, true) || in_array('superadmin', $roles, true)) {
            return true;
        }

        if ($session->has('role_id') && (string)$session->get('role_id') === '1') {
            return true;
        }

        if ($session->has('user_role_id') && (string)$session->get('user_role_id') === '1') {
            return true;
        }

        return false;
    }
}
