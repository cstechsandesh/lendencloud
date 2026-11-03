<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleController extends BaseController
{
    protected RoleModel $roleModel;
    protected PermissionModel $permissionModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->roleModel       = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    protected function enforceSuperAdmin(): void
    {
        $roles = $this->session->get('roles') ?? [];
        if (!in_array('super_admin', $roles, true)) {
            if (ENVIRONMENT === 'testing') {
                throw new \CodeIgniter\Security\Exceptions\SecurityException('Platform Super Admin Access Required');
            }
            $this->session->setFlashdata('error', 'Access to RBAC Roles is restricted to Platform Super Administrators.');
            redirect()->to('/dashboard')->send();
            exit;
        }
    }

    public function index()
    {
        $this->enforceSuperAdmin();
        $roles = $this->roleModel->getRolesWithStats();

        return $this->render('admin/roles/index', [
            'title' => 'RBAC Roles & Access Matrix',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $this->enforceSuperAdmin();
        $groupedPermissions = $this->permissionModel->getAllGroupedByModule();

        return $this->render('admin/roles/create', [
            'title'       => 'Create Custom Role',
            'permissions' => $groupedPermissions,
        ]);
    }

    public function store()
    {
        $this->enforceSuperAdmin();
        $rules = [
            'role_key'  => 'required|alpha_dash|is_unique[roles.role_key]',
            'role_name' => 'required|min_length[2]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = $this->roleModel->insert([
            'role_key'         => strtolower(trim($this->request->getPost('role_key') ?? '')),
            'role_name'        => trim($this->request->getPost('role_name') ?? ''),
            'role_description' => $this->request->getPost('role_description'),
            'role_badge_class' => $this->request->getPost('role_badge_class') ?: 'bg-secondary',
            'role_is_system'   => 0,
        ]);

        $perms = $this->request->getPost('permissions') ?? [];
        $this->roleModel->syncPermissions($roleId, array_map('intval', $perms));

        return redirect()->to('/roles')->with('success', 'Role and privileges created.');
    }

    public function edit(int $id)
    {
        $this->enforceSuperAdmin();
        $role = $this->roleModel->find($id);
        if (!$role) return redirect()->to('/roles')->with('error', 'Role not found.');

        $groupedPermissions = $this->permissionModel->getAllGroupedByModule();
        $assignedPerms = $this->roleModel->getPermissionsForRole($id);
        $assignedIds = array_column($assignedPerms, 'permission_id');

        return $this->render('admin/roles/edit', [
            'title'       => 'Edit Role Privileges: ' . $role['role_name'],
            'role'        => $role,
            'permissions' => $groupedPermissions,
            'assignedIds' => $assignedIds,
        ]);
    }

    public function update(int $id)
    {
        $this->enforceSuperAdmin();
        $role = $this->roleModel->find($id);
        if (!$role) return redirect()->to('/roles')->with('error', 'Role not found.');

        $data = [
            'role_name'        => trim($this->request->getPost('role_name') ?? ''),
            'role_description' => $this->request->getPost('role_description'),
            'role_badge_class' => $this->request->getPost('role_badge_class') ?: 'bg-secondary',
        ];

        $this->roleModel->update($id, $data);

        // Do not alter super_admin full bypass privileges
        if ($role['role_key'] !== 'super_admin') {
            $perms = $this->request->getPost('permissions') ?? [];
            $this->roleModel->syncPermissions($id, array_map('intval', $perms));
        }

        return redirect()->to('/roles')->with('success', 'Role permissions updated.');
    }

    public function delete(int $id)
    {
        $this->enforceSuperAdmin();
        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->to('/roles')->with('error', 'Role not found.');
        }

        if (!empty($role['role_is_system'])) {
            return redirect()->to('/roles')->with('error', "System core role \"{$role['role_name']}\" is system-locked and cannot be deleted.");
        }

        $db = \Config\Database::connect();
        $userCount = $db->table('users')->where('user_role', $role['role_key'])->countAllResults();
        $urCount   = $db->table('user_roles')->where('ur_role_id', $id)->countAllResults();
        $totalAssigned = max($userCount, $urCount);

        if ($totalAssigned > 0) {
            return redirect()->to('/roles')->with('error', "Cannot delete role \"{$role['role_name']}\" because it is currently assigned to {$totalAssigned} user(s). Reassign those operators to another role before deleting.");
        }

        // Clean up role permissions map and delete role
        $db->table('role_permissions')->where('rp_role_id', $id)->delete();
        $this->roleModel->delete($id);

        return redirect()->to('/roles')->with('success', "Role \"{$role['role_name']}\" deleted successfully.");
    }
}
