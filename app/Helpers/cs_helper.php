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
