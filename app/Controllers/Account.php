<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WebModel;

class Account extends BaseController
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

        // Block admins from user account area
        if (session()->get('role') == 1) {
            header('Location: ' . base_url('dashboard'));
            exit;
        }
    }

    // ── Profile ───────────────────────────────────────────────────────────────

    public function index()
    {
        $data = [
            'country'  => $this->webModel->getallcountry(),
            'userInfo' => $this->userModel->getUserInfoWithRole($this->vendorId),
        ];
        return view('frontend/header') . view('frontend/profile', $data) . view('frontend/footer');
    }

    public function pUpdate()
    {
        $rules = [
            'fname'  => 'required|max_length[128]',
            'mobile' => 'required|min_length[10]',
            'email'  => 'required|valid_email|max_length[128]',
            'paypal' => 'required|valid_email|max_length[256]',
            'bank'   => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->index();
        }

        $userInfo = [
            'name'       => ucwords(strtolower(esc($this->request->getPost('fname')))),
            'email'      => strtolower(esc($this->request->getPost('email'))),
            'mobile'     => esc($this->request->getPost('mobile')),
            'paypal'     => strtolower(esc($this->request->getPost('paypal'))),
            'bank'       => $this->request->getPost('bank'),
            'phonecode'  => $this->request->getPost('phonecode') ?? '',
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ];

        $result = $this->userModel->editUser($userInfo, $this->vendorId);

        if ($result) {
            session()->set('name', $userInfo['name']);
            session()->setFlashdata('success', 'Profile updated successfully');
        } else {
            session()->setFlashdata('error', 'Profile updation failed');
        }
        return $this->index();
    }

    // ── Change Password ───────────────────────────────────────────────────────

    public function changepassword()
    {
        $data = ['userInfo' => $this->userModel->getUserInfoWithRole($this->vendorId)];
        return view('frontend/header') . view('frontend/changepassword', $data) . view('frontend/footer');
    }

    public function passwordUpdate()
    {
        $rules = [
            'oldPassword'  => 'required|max_length[20]',
            'newPassword'  => 'required|max_length[20]',
            'cNewPassword' => 'required|matches[newPassword]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->changepassword();
        }

        $oldPassword = $this->request->getPost('oldPassword');
        $newPassword = $this->request->getPost('newPassword');
        $resultPas   = $this->userModel->matchOldPassword($this->vendorId, $oldPassword);

        if (empty($resultPas)) {
            session()->setFlashdata('nomatch', 'Your old password is not correct');
            return $this->changepassword();
        }

        $result = $this->userModel->changePassword($this->vendorId, [
            'password'   => getHashedPassword($newPassword),
            'updatedBy'  => $this->vendorId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ]);

        if ($result > 0) {
            session()->setFlashdata('success', 'Password updation successful');
        } else {
            session()->setFlashdata('error', 'Password updation failed');
        }
        return $this->changepassword();
    }

    // ── Wallet ────────────────────────────────────────────────────────────────

    public function wallet()
    {
        $data = [
            'userInfo'     => $this->userModel->getUserInfoWithRole($this->vendorId),
            'money'        => $this->webModel->wallet($this->vendorId),
            'money_history' => $this->webModel->wallet_history($this->vendorId),
            'common'       => $this->webModel->getcommon(),
        ];
        return view('frontend/header') . view('frontend/wallet', $data) . view('frontend/footer');
    }

    public function wupdate()
    {
        $money           = (float) $this->request->getPost('money');
        $transaction_id  = esc($this->request->getPost('transaction_id') ?? '');
        $payment_type    = esc($this->request->getPost('paymet_type') ?? '');

        $insertData = [
            'user_id'        => $this->vendorId,
            'money'          => $money,
            'trancaction_id' => $transaction_id,
            'type'           => 'Credit',
            'p_type'         => $payment_type,
        ];

        $walletMoney = $this->webModel->wallet($this->vendorId);
        if (!$walletMoney) {
            $this->webModel->insert_date('tbl_wallet', [
                'user_id' => $this->vendorId,
                'money'   => $money,
            ]);
        } else {
            $this->webModel->editWeb_all('tbl_wallet', [
                'user_id' => $this->vendorId,
                'money'   => $walletMoney->money + $money,
            ], $walletMoney->id);
        }

        $this->webModel->insert_date('tbl_wallet_history', $insertData);
        session()->setFlashdata('success', '$' . $money . ' added in wallet successfully');
        return redirect()->to('account/wallet');
    }

    // ── Refund ────────────────────────────────────────────────────────────────

    public function refund()
    {
        $data = [
            'userInfo' => $this->userModel->getUserInfoWithRole($this->vendorId),
        ];
        return view('frontend/header') . view('frontend/refund', $data) . view('frontend/footer');
    }
}
