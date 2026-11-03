<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $user = $userModel->find($this->session->get('user_id'));

        return $this->render('admin/profile/index', [
            'title' => 'My Account Profile',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        $userId = (int)$this->session->get('user_id');
        $userModel = new UserModel();

        $userModel->update($userId, [
            'user_name'  => trim($this->request->getPost('user_name')),
            'user_email' => trim($this->request->getPost('user_email')) ?: null,
            'user_phone' => trim($this->request->getPost('user_phone')) ?: null,
        ]);

        $this->session->set('user_name', trim($this->request->getPost('user_name')));
        return redirect()->to('/profile')->with('success', 'Profile updated.');
    }

    public function changePassword()
    {
        $userId = (int)$this->session->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $current = $this->request->getPost('current_password');
        $new     = $this->request->getPost('new_password');
        $confirm = $this->request->getPost('confirm_password');

        if (!password_verify($current, $user['user_password_hash'])) {
            return redirect()->to('/profile')->with('error', 'Current password is incorrect.');
        }

        if (strlen($new) < 6 || $new !== $confirm) {
            return redirect()->to('/profile')->with('error', 'New password must be at least 6 characters and match confirmation.');
        }

        $userModel->update($userId, [
            'user_password_hash' => password_hash($new, PASSWORD_BCRYPT),
        ]);

        return redirect()->to('/profile')->with('success', 'Password changed successfully.');
    }
}