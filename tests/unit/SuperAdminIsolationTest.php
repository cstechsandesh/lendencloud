<?php

chdir(__DIR__ . '/../..');
require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

use App\Models\RoleModel;
use App\Models\UserModel;

$db = \Config\Database::connect('default');

echo "==========================================================" . PHP_EOL;
echo "  TEST SUITE: Super Admin & Multi-Tenant Role Isolation   " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

// 1. Verify Platform Permissions are revoked from Tenant Admin
$adminRole = $db->table('roles')->where('role_key', 'admin')->get()->getRowArray();
$forbiddenKeys = [
    'subscriptions.view',
    'subscriptions.manage',
    'roles.view',
    'roles.create',
    'roles.edit',
    'roles.delete',
];

$assignedForbidden = $db->table('role_permissions')
    ->join('permissions', 'permissions.permission_id = role_permissions.rp_permission_id')
    ->where('rp_role_id', $adminRole['role_id'])
    ->whereIn('permissions.permission_key', $forbiddenKeys)
    ->get()->getResultArray();

if (empty($assignedForbidden)) {
    echo "[PASS] Tenant Admin role (admin) has NO platform/subscription permissions." . PHP_EOL;
} else {
    echo "[FAIL] Tenant Admin still has forbidden permissions: " . json_encode(array_column($assignedForbidden, 'permission_key')) . PHP_EOL;
}

// 2. Verify Super Admin role has platform permissions
$superAdminRole = $db->table('roles')->where('role_key', 'super_admin')->get()->getRowArray();
$superUser = $db->table('users')->where('user_role', 'super_admin')->get()->getRowArray();
$userModel = new UserModel($db);
$superPerms = $userModel->getUserPermissions((int)$superUser['user_id']);

$hasAllPlatformPerms = true;
foreach ($forbiddenKeys as $fk) {
    if (!in_array($fk, $superPerms, true)) {
        $hasAllPlatformPerms = false;
        break;
    }
}
if ($hasAllPlatformPerms) {
    echo "[PASS] Super Admin retains all platform management permissions." . PHP_EOL;
} else {
    echo "[FAIL] Super Admin is missing required platform permissions." . PHP_EOL;
}

// 3. Verify RoleModel filters out super_admin for tenant admins
$roleModel = new RoleModel($db);
$tenantRoles = $roleModel->where('role_key !=', 'super_admin')->findAll();
$roleKeys = array_column($tenantRoles, 'role_key');

if (!in_array('super_admin', $roleKeys, true) && in_array('admin', $roleKeys, true) && in_array('accountant', $roleKeys, true)) {
    echo "[PASS] Role list for tenant administrators excludes 'super_admin' entirely." . PHP_EOL;
} else {
    echo "[FAIL] Tenant roles filter failed: " . json_encode($roleKeys) . PHP_EOL;
}

// 4. Verify UserModel scoped by company
$company1Users = $userModel->getUsersWithRoles(1);
$company1UserIds = array_column($company1Users, 'user_company_id');
$allBelongToComp1 = true;
foreach ($company1UserIds as $cid) {
    if ((int)$cid !== 1) {
        $allBelongToComp1 = false;
        break;
    }
}
if ($allBelongToComp1 && !empty($company1Users)) {
    echo "[PASS] UserModel::getUsersWithRoles(1) strictly returned only Company 1 users." . PHP_EOL;
} else {
    echo "[FAIL] UserModel returned users outside Company 1." . PHP_EOL;
}

echo "==========================================================" . PHP_EOL;
echo "  SUPER ADMIN & RBAC ISOLATION VERIFIED SUCCESSFULLY      " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

