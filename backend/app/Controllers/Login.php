<?php

namespace App\Controllers;

use App\Models\LoginModel;
use App\Models\UserModel;
use App\Models\CartOrderModel;

class Login extends BaseController
{
    protected LoginModel $loginModel;
    protected UserModel  $userModel;
    protected CartOrderModel $cartOrderModel;

    protected $helpers = ['url', 'cias_helper', 'email_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->loginModel = new LoginModel();
        $this->userModel  = new UserModel();
        $this->cartOrderModel = new CartOrderModel();
    }

    public function index()
    {
        if (session()->get('isLoggedIn') === true) {
            return redirect()->to('dashboard');
        }
        return view('templates/auth_header') . view('auth/sign-in') . view('templates/auth_footer');
    }

    public function loginMe()
    {
        $rules = [
            'email'    => 'required|max_length[128]|valid_email',
            'password' => 'required|max_length[32]',
        ];

        if (!$this->validate($rules)) {
            return $this->index();
        }

        $email    = strtolower(esc($this->request->getPost('email')));
        $password = $this->request->getPost('password');
        $result   = $this->loginModel->loginMe($email, $password);

        if (empty($result)) {
            session()->setFlashdata('error', 'Email or password mismatch');
            return $this->index();
        }

        $lastLogin = null;
        if ($result->roleId != 1) {
            $lastLogin = $this->loginModel->lastLoginInfo($result->userId);
            $guestId   = session()->get('custom_userId');
            if ($guestId) {
                $this->cartOrderModel->up_cart((int)$guestId, $result->userId);
            }
        }

        session()->set([
            'userId'    => $result->userId,
            'role'      => $result->roleId,
            'roleText'  => $result->role,
            'name'      => $result->name,
            'email'     => $result->email,
            'mobile'    => $result->mobile,
            'lastLogin' => $lastLogin ? $lastLogin->createdDtm : null,
            'isLoggedIn' => true,
        ]);

        $loginInfo = [
            'userId'      => $result->userId,
            'sessionData' => json_encode(['name' => $result->name, 'email' => $result->email]),
            'machineIp'   => $this->request->getIPAddress(),
            'userAgent'   => getBrowserAgent(),
            'agentString' => $this->request->getUserAgent()->getAgentString(),
            'platform'    => $this->request->getUserAgent()->getPlatform(),
        ];
        $this->loginModel->lastLogin($loginInfo);

        // if (count($this->cartOrderModel->order_data($result->userId)) > 0 || $result->roleId == 2) {
        //     return redirect()->to('game/confirm_order');
        // }
        return redirect()->to('dashboard');
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn') === true) {
            return redirect()->to('dashboard');
        }
        return view('templates/auth_header') . view('auth/forgot-password') . view('templates/auth_footer');
    }

    public function resetPasswordUser()
    {
        $rules = ['login_email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return $this->forgotPassword();
        }

        $email = strtolower(esc($this->request->getPost('login_email')));

        if (!$this->loginModel->checkEmailExist($email)) {
            setFlashData('invalid', 'This email is not registered with us.');
            return redirect()->to('forgotPassword');
        }

        $activationId = bin2hex(random_bytes(8));
        $data = [
            'email'         => $email,
            'activation_id' => $activationId,
            'createdDtm'    => date('Y-m-d H:i:s'),
            'agent'         => getBrowserAgent(),
            'client_ip'     => $this->request->getIPAddress(),
        ];

        $save = $this->loginModel->resetPasswordUser($data);

        if ($save) {
            $userInfo = $this->loginModel->getCustomerInfoByEmail($email);
            $data1 = [
                'reset_link' => base_url('resetPasswordConfirmUser/' . $activationId . '/' . urlencode($email)),
                'name'       => $userInfo->name ?? '',
                'email'      => $email,
                'message'    => 'Reset Your Password',
            ];
            $sent = resetPasswordEmail($data1);
            if ($sent) {
                setFlashData('send', 'Reset password link has been sent successfully, please check your email.');
            } else {
                setFlashData('notsend', 'Email has been failed, try again.');
            }
        } else {
            setFlashData('unable', 'It seems an error while sending your details, try again.');
        }

        return redirect()->to('forgotPassword');
    }

    public function resetPasswordConfirmUser(string $activation_id, string $email)
    {
        $email      = urldecode($email);
        $is_correct = $this->loginModel->checkActivationDetails($email, $activation_id);

        if ($is_correct != 1) {
            return redirect()->to('login');
        }

        $data = ['email' => $email, 'activation_code' => $activation_id];
        return view('templates/auth_header') . view('auth/reset-password', $data) . view('templates/auth_footer');
    }

    public function createPasswordUser()
    {
        $email         = strtolower($this->request->getPost('email') ?? '');
        $activationId  = $this->request->getPost('activation_code') ?? '';

        $rules = [
            'password'  => 'required|max_length[20]',
            'cpassword' => 'required|matches[password]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return $this->resetPasswordConfirmUser($activationId, urlencode($email));
        }

        $password = $this->request->getPost('password');
        $result   = $this->loginModel->createPasswordUser([
            'password'   => getHashedPassword($password),
            'email'      => $email,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ], $email, $activationId);

        if ($result > 0) {
            setFlashData('created', 'Your password has been reset successfully. Please login.');
        } else {
            setFlashData('error', 'Something went wrong, please try again.');
        }

        return redirect()->to('login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
