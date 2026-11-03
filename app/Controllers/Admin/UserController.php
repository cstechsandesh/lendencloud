<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    protected function isSuperAdmin(): bool
    {
        $roles = $this->session->get('roles') ?? [];
        return in_array('super_admin', $roles, true);
    }

    public function index()
    {
        $this->checkPermission('users.view');
        $isSuperAdmin = $this->isSuperAdmin();

        $users = $this->userModel->getUsersWithRoles($isSuperAdmin ? null : $this->companyId);
        $roles = $isSuperAdmin 
            ? $this->roleModel->findAll() 
            : $this->roleModel->where('role_key !=', 'super_admin')->findAll();

        return $this->render('admin/users/index', [
            'title'        => 'Users & Operators Management',
            'users'        => $users,
            'roles'        => $roles,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function create()
    {
        $this->checkPermission('users.create');
        $isSuperAdmin = $this->isSuperAdmin();

        $roles = $isSuperAdmin 
            ? $this->roleModel->findAll() 
            : $this->roleModel->where('role_key !=', 'super_admin')->findAll();

        return $this->render('admin/users/create', [
            'title'        => 'Register System User',
            'roles'        => $roles,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function store()
    {
        $this->checkPermission('users.create');
        $isSuperAdmin = $this->isSuperAdmin();

        $rules = [
            'user_username' => 'required|alpha_dash|min_length[3]|max_length[50]|is_unique[users.user_username]',
            'user_name'     => 'required|min_length[2]|max_length[150]',
            'user_password' => 'required|min_length[6]',
            'user_role_id'  => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = (int)$this->request->getPost('user_role_id');
        $role   = $this->roleModel->find($roleId);

        // Security: Non-superadmins CANNOT assign the super_admin role
        if (!$isSuperAdmin && $role && $role['role_key'] === 'super_admin') {
            return redirect()->back()->withInput()->with('error', 'Unauthorized: Only Platform Super Administrators can assign the Super Admin role.');
        }

        // Lock company ID for non-superadmins
        $userCompId = $isSuperAdmin 
            ? (int)($this->request->getPost('user_company_id') ?: $this->companyId) 
            : $this->companyId;

        $userId = $this->userModel->insert([
            'user_company_id'    => $userCompId,
            'user_role_id'       => $roleId,
            'user_role'          => $role ? $role['role_key'] : 'staff',
            'user_username'      => trim($this->request->getPost('user_username') ?? ''),
            'user_email'         => trim($this->request->getPost('user_email') ?? '') ?: null,
            'user_name'          => trim($this->request->getPost('user_name') ?? ''),
            'user_phone'         => trim($this->request->getPost('user_phone') ?? '') ?: null,
            'user_designation'   => trim($this->request->getPost('user_designation') ?? '') ?: null,
            'user_password_hash' => password_hash($this->request->getPost('user_password'), PASSWORD_BCRYPT),
            'user_status'        => $this->request->getPost('user_status') ?: 'active',
        ]);

        if ($userId) {
            $userCompanyModel = new \App\Models\UserCompanyModel();
            $userCompanyModel->ignore(true)->insert([
                'uc_user_id'    => $userId,
                'uc_company_id' => $userCompId,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            $db = \Config\Database::connect();
            $db->table('user_roles')->ignore(true)->insert([
                'ur_user_id' => $userId,
                'ur_role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/users')->with('success', 'User account created successfully.');
    }

    public function view(int $id)
    {
        $this->checkPermission('users.view');
        $isSuperAdmin = $this->isSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        if (!$isSuperAdmin && (int)$user['user_company_id'] !== $this->companyId) {
            return redirect()->to('/users')->with('error', 'Access denied. You can only view users within your company.');
        }

        $perms = $this->session->get('permissions') ?? [];
        if ($isSuperAdmin || in_array('users.edit', $perms, true)) {
            return redirect()->to('/users/edit/' . $id);
        }

        $roles = $this->roleModel->findAll();
        return $this->render('admin/users/edit', [
            'title'        => 'User Details: ' . $user['user_name'],
            'user'         => $user,
            'roles'        => $roles,
            'isSuperAdmin' => $isSuperAdmin,
            'readOnly'     => true,
        ]);
    }

    public function edit(int $id)
    {
        $this->checkPermission('users.edit');
        $isSuperAdmin = $this->isSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        // Security: Non-superadmins cannot edit users of other companies or Super Admins
        if (!$isSuperAdmin) {
            if ((int)$user['user_company_id'] !== $this->companyId) {
                return redirect()->to('/users')->with('error', 'Access denied. You can only manage users within your company.');
            }
            if ($user['user_role'] === 'super_admin') {
                return redirect()->to('/users')->with('error', 'Unauthorized: Cannot edit a Super Administrator account.');
            }
        }

        $roles = $isSuperAdmin 
            ? $this->roleModel->findAll() 
            : $this->roleModel->where('role_key !=', 'super_admin')->findAll();

        return $this->render('admin/users/edit', [
            'title'        => 'Edit User: ' . $user['user_name'],
            'user'         => $user,
            'roles'        => $roles,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('users.edit');
        $isSuperAdmin = $this->isSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        // Security check
        if (!$isSuperAdmin) {
            if ((int)$user['user_company_id'] !== $this->companyId) {
                return redirect()->to('/users')->with('error', 'Access denied.');
            }
            if ($user['user_role'] === 'super_admin') {
                return redirect()->to('/users')->with('error', 'Unauthorized: Cannot modify a Super Administrator account.');
            }
        }

        $rules = [
            'user_name'    => 'required|min_length[2]|max_length[150]',
            'user_role_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleId = (int)$this->request->getPost('user_role_id');
        $role   = $this->roleModel->find($roleId);

        // Security: Non-superadmins cannot promote any user to super_admin
        if (!$isSuperAdmin && $role && $role['role_key'] === 'super_admin') {
            return redirect()->back()->withInput()->with('error', 'Unauthorized: Cannot grant Super Administrator privileges.');
        }

        $data = [
            'user_role_id'     => $roleId,
            'user_role'        => $role ? $role['role_key'] : 'staff',
            'user_email'       => trim($this->request->getPost('user_email') ?? '') ?: null,
            'user_name'        => trim($this->request->getPost('user_name') ?? ''),
            'user_phone'       => trim($this->request->getPost('user_phone') ?? '') ?: null,
            'user_designation' => trim($this->request->getPost('user_designation') ?? '') ?: null,
            'user_status'      => $this->request->getPost('user_status') ?: 'active',
        ];

        $newPass = $this->request->getPost('user_password');
        if (!empty($newPass)) {
            $data['user_password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        // Sync user_roles pivot mapping
        $db = \Config\Database::connect();
        $db->table('user_roles')->where('ur_user_id', $id)->delete();
        $db->table('user_roles')->insert([
            'ur_user_id' => $id,
            'ur_role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/users')->with('success', 'User updated successfully.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('users.delete');
        $isSuperAdmin = $this->isSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        if ($id === (int)$this->session->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Cannot delete your own active session account.');
        }

        // Security check for company boundaries
        if (!$isSuperAdmin) {
            if ((int)$user['user_company_id'] !== $this->companyId) {
                return redirect()->to('/users')->with('error', 'Access denied.');
            }
            if ($user['user_role'] === 'super_admin') {
                return redirect()->to('/users')->with('error', 'Unauthorized: Cannot delete a Super Administrator account.');
            }
        }

        // Protect last active super admin
        if ($user['user_role'] === 'super_admin') {
            $activeSuperAdmins = $this->userModel->where('user_role', 'super_admin')
                ->where('user_status', 'active')
                ->countAllResults();
            if ($activeSuperAdmins <= 1) {
                return redirect()->to('/users')->with('error', 'Cannot delete the last remaining active Super Administrator account.');
            }
        }

        $db = \Config\Database::connect();

        // Check if user has authored any financial audit transactions
        $invCount   = $db->table('invoices')->where('invoice_created_by', $id)->countAllResults();
        $billCount  = $db->table('bills')->where('bill_created_by', $id)->countAllResults();
        $journCount = $db->table('journal_entries')->where('journal_created_by', $id)->countAllResults();
        $vouchCount = $db->table('vouchers')->where('voucher_created_by', $id)->countAllResults();

        $reasons = [];
        if ($invCount > 0)   $reasons[] = "{$invCount} invoice(s)";
        if ($billCount > 0)  $reasons[] = "{$billCount} bill(s)";
        if ($journCount > 0) $reasons[] = "{$journCount} journal entry(ies)";
        if ($vouchCount > 0) $reasons[] = "{$vouchCount} payment voucher(s)";

        if (!empty($reasons)) {
            return redirect()->to('/users')->with('error', "Cannot delete user \"{$user['user_name']}\" because they have authored: " . implode(', ', $reasons) . ". To preserve mandatory double-entry audit history, deactivate the user's status instead.");
        }

        // Clean up user role assignments and delete
        $db->table('user_roles')->where('ur_user_id', $id)->delete();
        $this->userModel->delete($id);

        return redirect()->to('/users')->with('success', "User \"{$user['user_name']}\" deleted successfully.");
    }
}
