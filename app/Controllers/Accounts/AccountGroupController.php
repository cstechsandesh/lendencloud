<?php

namespace App\Controllers\Accounts;

use App\Controllers\BaseController;
use App\Models\AccountGroupModel;

class AccountGroupController extends BaseController
{
    protected AccountGroupModel $groupModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->groupModel = new AccountGroupModel();
    }

    public function index()
    {
        $this->checkPermission('accounts.view');
        $groups = $this->groupModel->where('group_company_id', $this->companyId)
            ->orderBy('group_type', 'ASC')->orderBy('group_name', 'ASC')->findAll();

        return $this->render('accounts/groups/index', [
            'title'  => 'Account Groups Master',
            'groups' => $groups,
        ]);
    }

    public function create()
    {
        $this->checkPermission('accounts.create');
        $parents = $this->groupModel->where('group_company_id', $this->companyId)->findAll();

        return $this->render('accounts/groups/create', [
            'title'   => 'Add Account Group',
            'parents' => $parents,
        ]);
    }

    public function store()
    {
        $this->checkPermission('accounts.create');
        $rules = [
            'group_name' => 'required|min_length[2]|max_length[100]',
            'group_type' => 'required|in_list[asset,liability,equity,revenue,expense]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->groupModel->insert([
            'group_company_id' => $this->companyId,
            'group_name'       => trim($this->request->getPost('group_name')),
            'group_type'       => $this->request->getPost('group_type'),
            'group_parent_id'  => $this->request->getPost('group_parent_id') ?: null,
        ]);

        return redirect()->to('/accounts/groups')->with('success', 'Account group saved.');
    }

    public function edit(int $id)
    {
        $this->checkPermission('accounts.edit');
        $group = $this->groupModel->find($id);
        if (!$group) return redirect()->to('/accounts/groups')->with('error', 'Group not found.');

        $parents = $this->groupModel->where('group_id !=', $id)->findAll();

        return $this->render('accounts/groups/edit', [
            'title'   => 'Edit Account Group',
            'group'   => $group,
            'parents' => $parents,
        ]);
    }

    public function update(int $id)
    {
        $this->checkPermission('accounts.edit');
        $this->groupModel->update($id, [
            'group_name'      => trim($this->request->getPost('group_name')),
            'group_type'      => $this->request->getPost('group_type'),
            'group_parent_id' => $this->request->getPost('group_parent_id') ?: null,
        ]);

        return redirect()->to('/accounts/groups')->with('success', 'Group updated.');
    }

    public function delete(int $id)
    {
        $this->checkPermission('accounts.delete');

        $group = $this->groupModel->where('group_id', $id)
            ->where('group_company_id', $this->companyId)
            ->first();

        if (!$group) {
            return redirect()->to('/accounts/groups')->with('error', 'Account group not found or does not belong to active company.');
        }

        $db = \Config\Database::connect();
        $accCount = $db->table('accounts')->where('account_group_id', $id)->countAllResults();
        $subCount = $db->table('account_groups')->where('group_parent_id', $id)->countAllResults();

        $reasons = [];
        if ($accCount > 0) $reasons[] = "{$accCount} chart of account(s)";
        if ($subCount > 0) $reasons[] = "{$subCount} child sub-group(s)";

        if (!empty($reasons)) {
            return redirect()->to('/accounts/groups')->with('error', "Cannot delete account group \"{$group['group_name']}\" because it currently has: " . implode(', ', $reasons) . ". Please reassign them first.");
        }

        $this->groupModel->delete($id);
        return redirect()->to('/accounts/groups')->with('success', "Account group \"{$group['group_name']}\" deleted successfully.");
    }
}