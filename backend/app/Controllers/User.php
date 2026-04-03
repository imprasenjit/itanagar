<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WebModel;

class User extends BaseController
{
    protected UserModel $userModel;
    protected WebModel  $webModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->userModel = new UserModel();
        $this->webModel  = new WebModel();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('home');
        }

        $this->global['pageTitle'] = 'event : Dashboard';
        return $this->loadViews('pages/dashboard', $this->global, [], null);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }

    // ── User Listing ──────────────────────────────────────────────────────────

    public function userListing()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count      = $this->userModel->userListingCount($searchText);
        $pgData     = $this->paginationCompress('userListing/', $count, 10);

        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->userModel->userListing($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : User Listing';
        return $this->loadViews('pages/users', $this->global, $data, null);
    }

    // ── Customer Listing (Customer-role users only) ───────────────────────────

    public function customerListing()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $this->global['pageTitle'] = 'event : Customers';
        return $this->loadViews('pages/customers', $this->global, [], null);
    }

    // ── Add New User ──────────────────────────────────────────────────────────

    public function addNew()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $data = [
            'country' => $this->webModel->getallcountry(),
            'roles'   => $this->userModel->getUserRoles(),
        ];
        $this->global['pageTitle'] = 'event : Add New User';
        return $this->loadViews('pages/addNew', $this->global, $data, null);
    }

    public function checkEmailExists()
    {
        $userId = $this->request->getPost('userId');
        $email  = $this->request->getPost('email') ?? '';
        $result = empty($userId)
            ? $this->userModel->checkEmailExists($email)
            : $this->userModel->checkEmailExists($email, (int)$userId);
        echo empty($result) ? 'true' : 'false';
    }

    public function addNewUser()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $rules = [
            'fname'     => 'required|max_length[128]',
            'email'     => 'required|valid_email|max_length[128]',
            'password'  => 'required|max_length[20]',
            'cpassword' => 'required|matches[password]|max_length[20]',
            'role'      => 'required|numeric',
            'mobile'    => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return $this->addNew();
        }

        $userInfo = [
            'email'      => strtolower(esc($this->request->getPost('email'))),
            'password'   => getHashedPassword($this->request->getPost('password')),
            'roleId'     => $this->request->getPost('role'),
            'name'       => ucwords(strtolower(esc($this->request->getPost('fname')))),
            'mobile'     => esc($this->request->getPost('mobile')),
            'phonecode'  => $this->request->getPost('phonecode') ?? '',
            'createdBy'  => $this->vendorId,
            'createdDtm' => date('Y-m-d H:i:s'),
        ];

        $result = $this->userModel->addNewUser($userInfo);
        if ($result > 0) {
            session()->setFlashdata('success', 'New User created successfully');
        } else {
            session()->setFlashdata('error', 'User creation failed');
        }
        return redirect()->to('addNew');
    }

    // ── Edit User ─────────────────────────────────────────────────────────────

    public function editOld(int $userId = 0)
    {
        if ($this->isAdmin() === false || $userId === 1) {
            return $this->loadThis();
        }
        if ($userId === 0) {
            return redirect()->to('userListing');
        }

        $data = [
            'country'  => $this->webModel->getallcountry(),
            'roles'    => $this->userModel->getUserRoles(),
            'userInfo' => $this->userModel->getUserInfo($userId),
        ];
        $this->global['pageTitle'] = 'event : Edit User';
        return $this->loadViews('pages/editOld', $this->global, $data, null);
    }

    public function editUser()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $userId = (int) $this->request->getPost('userId');
        $rules  = [
            'fname'     => 'required|max_length[128]',
            'email'     => 'required|valid_email|max_length[128]',
            'password'  => 'permit_empty|matches[cpassword]|max_length[20]',
            'cpassword' => 'permit_empty|matches[password]|max_length[20]',
            'role'      => 'required|numeric',
            'mobile'    => 'required|min_length[10]',
            'phonecode' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->editOld($userId);
        }

        $name     = ucwords(strtolower(esc($this->request->getPost('fname'))));
        $email    = strtolower(esc($this->request->getPost('email')));
        $password = $this->request->getPost('password') ?? '';
        $roleId   = $this->request->getPost('role');
        $mobile   = esc($this->request->getPost('mobile'));

        $userInfo = [
            'email'      => $email,
            'roleId'     => $roleId,
            'name'       => $name,
            'mobile'     => $mobile,
            'phonecode'  => $this->request->getPost('phonecode') ?? '',
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ];

        if (!empty($password)) {
            $userInfo['password'] = getHashedPassword($password);
        }

        $result = $this->userModel->editUser($userInfo, $userId);
        if ($result) {
            session()->setFlashdata('success', 'User updated successfully');
        } else {
            session()->setFlashdata('error', 'User updation failed');
        }
        return redirect()->to('userListing');
    }

    public function deleteUser()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $userId   = (int) $this->request->getPost('userId');
        $userInfo = [
            'isDeleted'  => 1,
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ];
        $result = $this->userModel->deleteUser($userId, $userInfo);
        return $this->response->setJSON(['status' => $result > 0]);
    }

    // ── Login History ────────────────────────────────────────────────────────

    public function loginHistoy(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $searchText = $this->request->getPost('searchText') ?? '';
        $fromDate   = $this->request->getPost('fromDate')   ?? '';
        $toDate     = $this->request->getPost('toDate')     ?? '';

        $count  = $this->userModel->loginHistoryCount($userId, $searchText, $fromDate, $toDate);
        $pgData = $this->paginationCompress("login-history/$userId/", $count, 10, 3);

        $data = [
            'userInfo'    => $this->userModel->getUserInfoById($userId),
            'searchText'  => $searchText,
            'fromDate'    => $fromDate,
            'toDate'      => $toDate,
            'userRecords' => $this->userModel->loginHistory($userId, $searchText, $fromDate, $toDate, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : User Login History';
        return $this->loadViews('pages/loginHistory', $this->global, $data, null);
    }

    // ── Admin Profile ────────────────────────────────────────────────────────

    public function profile(string $active = 'details')
    {
        $data = [
            'userInfo' => $this->userModel->getUserInfoWithRole($this->vendorId),
            'active'   => $active,
        ];
        $this->global['pageTitle'] = $active === 'details' ? 'event : My Profile' : 'event : Change Password';
        return $this->loadViews('pages/profile', $this->global, $data, null);
    }

    public function profileUpdate(string $active = 'details')
    {
        $rules = [
            'fname'  => 'required|max_length[128]',
            'mobile' => 'required|min_length[10]',
            'email'  => 'required|valid_email|max_length[128]',
        ];

        if (!$this->validate($rules)) {
            return $this->profile($active);
        }

        $name   = ucwords(strtolower(esc($this->request->getPost('fname'))));
        $mobile = esc($this->request->getPost('mobile'));
        $email  = strtolower(esc($this->request->getPost('email')));

        $result = $this->userModel->editUser([
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ], $this->vendorId);

        if ($result) {
            session()->set('name', $name);
            session()->setFlashdata('success', 'Profile updated successfully');
        } else {
            session()->setFlashdata('error', 'Profile updation failed');
        }
        return redirect()->to("profile/$active");
    }

    public function changePassword(string $active = 'changepass')
    {
        $rules = [
            'oldPassword'  => 'required|max_length[20]',
            'newPassword'  => 'required|max_length[20]',
            'cNewPassword' => 'required|matches[newPassword]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->profile($active);
        }

        $oldPassword = $this->request->getPost('oldPassword');
        $newPassword = $this->request->getPost('newPassword');
        $resultPas   = $this->userModel->matchOldPassword($this->vendorId, $oldPassword);

        if (empty($resultPas)) {
            session()->setFlashdata('nomatch', 'Your old password is not correct');
            return redirect()->to("profile/$active");
        }

        $result = $this->userModel->changePassword($this->vendorId, [
            'password'   => getHashedPassword($newPassword),
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ]);

        if ($result > 0) {
            session()->setFlashdata('success', 'Password updated successfully');
        } else {
            session()->setFlashdata('error', 'Password updation failed');
        }
        return redirect()->to("profile/$active");
    }

    public function pageNotFound()
    {
        $this->global['pageTitle'] = 'event : 404 - Page Not Found';
        return $this->loadViews('error/404', $this->global, null, null);
    }

    // ── DataTables server-side endpoints ──────────────────────────────────────

    public function users_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->userModel->userListingCount('');
        $filtered = $this->userModel->userListingCount($search);
        $rows     = $this->userModel->userListing($search, $length, $start);

        $data = [];
        foreach ($rows as $r) {
            $phone   = $r->phonecode != '' ? '+' . esc($r->phonecode) : '';
            $badge   = $r->role == 'Admin' ? 'bg-danger' : 'bg-primary';
            $actions = '<a class="btn btn-sm btn-light" href="' . base_url('login-history/' . $r->userId) . '" title="Login History"><i class="bi bi-clock-history"></i></a> '
                     . '<a class="btn btn-sm btn-info" href="' . base_url('web/user_order/' . $r->userId) . '" title="Orders"><i class="bi bi-bag-fill"></i></a> '
                     . '<a class="btn btn-sm btn-primary" href="' . base_url('editOld/' . $r->userId) . '" title="Edit"><i class="bi bi-pencil-fill"></i></a> '
                     . '<a class="btn btn-sm btn-danger deleteUser" href="#" data-userid="' . $r->userId . '" title="Delete"><i class="bi bi-trash3-fill"></i></a>';
            $data[] = [
                'name'       => esc($r->name),
                'email'      => esc($r->email),
                'phonecode'  => $phone,
                'mobile'     => esc($r->mobile),
                'role'       => '<span class="badge ' . $badge . '">' . esc($r->role) . '</span>',
                'createdDtm' => date('d-m-Y', strtotime($r->createdDtm)),
                'actions'    => $actions,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function customers_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->userModel->customerListingCount('');
        $filtered = $this->userModel->customerListingCount($search);
        $rows     = $this->userModel->customerListing($search, $length, $start);

        $data = [];
        foreach ($rows as $r) {
            $phone   = $r->phonecode != '' ? '+' . esc($r->phonecode) : '';
            $actions = '<a class="btn btn-sm btn-light" href="' . base_url('login-history/' . $r->userId) . '" title="Login History"><i class="bi bi-clock-history"></i></a> '
                     . '<a class="btn btn-sm btn-info" href="' . base_url('web/user_order/' . $r->userId) . '" title="Orders"><i class="bi bi-bag-fill"></i></a> '
                     . '<a class="btn btn-sm btn-secondary" href="' . base_url('web/user_wallet/' . $r->userId) . '" title="Wallet"><i class="bi bi-wallet2"></i></a>';
            $data[] = [
                'name'       => esc($r->name),
                'email'      => esc($r->email),
                'phonecode'  => $phone,
                'mobile'     => esc($r->mobile),
                'createdDtm' => date('d-m-Y', strtotime($r->createdDtm)),
                'actions'    => $actions,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function login_history_data(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw     = (int)($this->request->getGet('draw') ?? 1);
        $start    = (int)($this->request->getGet('start') ?? 0);
        $length   = (int)($this->request->getGet('length') ?? 10);
        $search   = trim($this->request->getGet('search')['value'] ?? '');
        $fromDate = $this->request->getGet('fromDate') ?? '';
        $toDate   = $this->request->getGet('toDate') ?? '';

        $total    = $this->userModel->loginHistoryCount($userId, '', '', '');
        $filtered = $this->userModel->loginHistoryCount($userId, $search, $fromDate, $toDate);
        $rows     = $this->userModel->loginHistory($userId, $search, $fromDate, $toDate, $length, $start);

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'session'     => esc($r->sessionData),
                'ip'          => esc($r->machineIp),
                'userAgent'   => esc($r->userAgent),
                'agentString' => esc($r->agentString),
                'platform'    => esc($r->platform),
                'date'        => esc($r->createdDtm),
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }
}
