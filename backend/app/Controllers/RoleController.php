<?php

namespace App\Controllers;

/**
 * RoleController — admin role management and RBAC permission assignment.
 * All routes begin with "web/".
 */
class RoleController extends BaseController
{
    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
    }

    // ── Role Listing & CRUD ───────────────────────────────────────────────────

    public function roles()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $model = model(\App\Models\RolePermissionModel::class);
        $data['roles'] = $model->getAllRoles();
        $this->global['pageTitle'] = 'Itanagarchoice : Roles';
        return $this->loadViews('pages/roles', $this->global, $data, null);
    }

    public function addRole()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        if ($this->request->getMethod() === 'get') {
            $this->global['pageTitle'] = 'Itanagarchoice : Add Role';
            return $this->loadViews('pages/addRole', $this->global, [], null);
        }

        if (! $this->validate(['name' => 'required|max_length[64]|is_unique[tbl_roles.role]'])) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->to('web/addRole');
        }
        $name  = esc($this->request->getPost('name'));
        $model = model(\App\Models\RolePermissionModel::class);
        if ($model->addRole($name)) {
            session()->setFlashdata('success', 'Role "' . $name . '" added successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to add role. Please try again.');
        }
        return redirect()->to('web/roles');
    }

    public function editRole(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $model        = model(\App\Models\RolePermissionModel::class);
        $data['role'] = $model->getRoleById($id);
        if (! $data['role']) {
            return redirect()->to('web/roles');
        }
        if ($data['role']->role === ROLE_CUSTOMER) {
            session()->setFlashdata('error', 'The Customer role cannot be edited.');
            return redirect()->to('web/roles');
        }
        $this->global['pageTitle'] = 'Itanagarchoice : Edit Role';
        return $this->loadViews('pages/roles_edit', $this->global, $data, null);
    }

    public function updateRole()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $id    = (int) $this->request->getPost('id');
        $model = model(\App\Models\RolePermissionModel::class);
        $role  = $model->getRoleById($id);
        if ($role && $role->role === ROLE_CUSTOMER) {
            session()->setFlashdata('error', 'The Customer role cannot be edited.');
            return redirect()->to('web/roles');
        }
        if (! $this->validate(['name' => 'required|max_length[64]|is_unique[tbl_roles.role,roleId,' . $id . ']'])) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return redirect()->to("web/editRole/$id");
        }
        $name  = esc($this->request->getPost('name'));
        $model->updateRole($id, $name);
        session()->setFlashdata('success', 'Role updated successfully.');
        return redirect()->to('web/roles');
    }

    public function deleteRole()
    {
        if ($this->isAdmin() === false) {
            echo json_encode(['status' => 'access']);
            return;
        }
        $id    = (int) $this->request->getPost('userId');
        if ($id === 1) {
            echo json_encode(['status' => false, 'msg' => 'Cannot delete the Super Admin role.']);
            return;
        }
        $model = model(\App\Models\RolePermissionModel::class);
        $role  = $model->getRoleById($id);
        if ($role && $role->role === ROLE_CUSTOMER) {
            echo json_encode(['status' => false, 'msg' => 'Cannot delete the Customer role.']);
            return;
        }
        $model  = model(\App\Models\RolePermissionModel::class);
        $result = $model->deleteRole($id);
        echo json_encode(['status' => $result]);
    }

    public function roles_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw  = (int)($this->request->getGet('draw') ?? 1);
        $model = model(\App\Models\RolePermissionModel::class);
        $roles = $model->getAllRoles();
        $data  = [];
        foreach ($roles as $role) {
            $isSuper    = (int)$role->roleId === 1;
            $isCustomer = $role->role === ROLE_CUSTOMER;
            $extraBadge = $isSuper    ? ' <span class="badge bg-warning text-dark ms-1">Super Admin</span>'
                        : ($isCustomer ? ' <span class="badge bg-success ms-1">Customer</span>' : '');
            if (!$isSuper && !$isCustomer) {
                $actions = '<a href="' . base_url('web/editRole/' . $role->roleId) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil-fill"></i> Edit</a> '
                         . '<button class="btn btn-sm btn-danger btn-delete-role" data-id="' . $role->roleId . '"><i class="bi bi-trash3-fill"></i> Delete</button>';
            } else {
                $actions = '<span class="text-muted small">Protected</span>';
            }
            $data[] = ['roleId' => esc($role->roleId), 'role' => esc($role->role) . $extraBadge, 'actions' => $actions];
        }
        $count = count($data);
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $count, 'recordsFiltered' => $count, 'data' => $data]);
    }

    // ── RBAC Permission Assignment ────────────────────────────────────────────

    public function rbac(): string
    {
        if (! $this->isAdmin()) {
            return $this->loadThis();
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('tbl_permissions')) {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
        }

        $model = model(\App\Models\RolePermissionModel::class);
        $permissions = $model->getAllPermissions();
        if (empty($permissions)) {
            \Config\Database::seeder()->call('PermissionsSeeder');
            $permissions = $model->getAllPermissions();
        }

        $data  = [
            'roles'       => $model->getAllRoles(),
            'permissions' => $permissions,
            'assigned'    => $model->getAllAssigned(),
        ];
        $this->global['pageTitle'] = 'Itanagarchoice : Role Permissions';
        return $this->loadViews('pages/rbac', $this->global, $data, null);
    }

    public function rbacSave(): void
    {
        if (! $this->isAdmin()) {
            echo json_encode(['status' => 'access']);
            return;
        }
        $roleId = (int) $this->request->getPost('role_id');
        if ($roleId <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid role']);
            return;
        }
        $model     = model(\App\Models\RolePermissionModel::class);
        $validKeys = array_column($model->getAllPermissions(), 'key');
        $rawKeys   = (array) ($this->request->getPost('permissions') ?? []);
        $cleanKeys = array_values(array_intersect($rawKeys, $validKeys));

        $model->saveRolePermissions($roleId, $cleanKeys);
        session()->remove('permissions');

        echo json_encode(['status' => 'ok']);
    }
}
