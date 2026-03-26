<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

class AuthController extends ApiBaseController
{
    public function me()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->json(['isLoggedIn' => false, 'user' => null]);
        }
        // Fix: use getCartUserId() so cart count is accurate even mid-session
        $userId    = $this->getCartUserId();
        $cartCount = count($this->webModel->cart_data($userId));
        return $this->json([
            'isLoggedIn' => true,
            'user'       => [
                'userId'    => session()->get('userId'),
                'name'      => session()->get('name'),
                'email'     => session()->get('email'),
                'mobile'    => session()->get('mobile'),
                'role'      => session()->get('role'),
                'roleText'  => session()->get('roleText'),
                'lastLogin' => session()->get('lastLogin'),
            ],
            'cartCount' => $cartCount,
        ]);
    }

    public function login()
    {
        $body     = $this->getBody();
        $email    = strtolower(esc($body['email']    ?? ''));
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->error('Email and password are required');
        }

        $result = $this->loginModel->loginMe($email, $password);
        if (empty($result)) {
            return $this->error('Email or password is incorrect', 401);
        }

        $lastLogin = $this->loginModel->lastLoginInfo($result->userId);

        // Merge guest cart on login
        if ($result->roleId != 1) {
            $guestId = session()->get('custom_userId');
            if ($guestId) {
                $this->webModel->up_cart((int) $guestId, $result->userId);
            }
        }

        session()->set([
            'userId'     => $result->userId,
            'role'       => $result->roleId,
            'roleText'   => $result->role,
            'name'       => $result->name,
            'email'      => $result->email,
            'mobile'     => $result->mobile,
            'lastLogin'  => $lastLogin ? $lastLogin->createdDtm : null,
            'isLoggedIn' => true,
        ]);

        $this->loginModel->lastLogin([
            'userId'      => $result->userId,
            'sessionData' => json_encode(['name' => $result->name, 'email' => $result->email]),
            'machineIp'   => $this->request->getIPAddress(),
            'userAgent'   => getBrowserAgent(),
            'agentString' => $this->request->getUserAgent()->getAgentString(),
            'platform'    => $this->request->getUserAgent()->getPlatform(),
        ]);

        return $this->json([
            'user' => [
                'userId'   => $result->userId,
                'name'     => $result->name,
                'email'    => $result->email,
                'mobile'   => $result->mobile,
                'role'     => $result->roleId,
                'roleText' => $result->role,
            ],
        ], true, 'Login successful');
    }

    public function logout()
    {
        session()->destroy();
        return $this->json([], true, 'Logged out');
    }

    public function register()
    {
        $body     = $this->getBody();
        $name     = ucwords(strtolower(esc($body['name']     ?? '')));
        $email    = strtolower(esc($body['email']    ?? ''));
        $mobile   = esc($body['mobile']   ?? '');
        $password = $body['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            return $this->error('Name, email, and password are required');
        }

        if (!empty($this->userModel->checkEmailExists($email))) {
            return $this->error('Email already registered', 409);
        }

        $userId = $this->userModel->addNewUser([
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'password'   => getHashedPassword($password),
            'roleId'     => 2,
            'isDeleted'  => 0,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);

        return $this->json(['userId' => $userId], true, 'Registered successfully');
    }

    public function forgot_password()
    {
        helper('email_helper');
        $body  = $this->getBody();
        $email = strtolower(esc($body['email'] ?? ''));

        if (empty($email)) {
            return $this->error('Email is required');
        }
        if (!$this->loginModel->checkEmailExist($email)) {
            return $this->error('Email not found', 404);
        }

        $activationId = bin2hex(random_bytes(8));
        $this->loginModel->resetPasswordUser([
            'email'         => $email,
            'activation_id' => $activationId,
            'createdDtm'    => date('Y-m-d H:i:s'),
            'agent'         => getBrowserAgent(),
            'client_ip'     => $this->request->getIPAddress(),
        ]);

        $userInfo  = $this->loginModel->getCustomerInfoByEmail($email);
        $resetLink = base_url('ui/reset-password?code=' . $activationId . '&email=' . urlencode($email));
        resetPasswordEmail([
            'name'       => $userInfo->name ?? '',
            'email'      => $email,
            'reset_link' => $resetLink,
            'message'    => 'Reset Your Password',
        ]);

        return $this->json([], true, 'Reset link sent to your email');
    }

    public function reset_password()
    {
        $body            = $this->getBody();
        $email           = strtolower(trim($body['email']                ?? ''));
        $activationCode  = trim($body['activation_code']                 ?? '');
        $password        = $body['password']                             ?? '';
        $confirmPassword = $body['password_confirmation']                ?? '';

        if (empty($email) || empty($activationCode) || empty($password) || empty($confirmPassword)) {
            return $this->error('All fields are required');
        }
        if ($password !== $confirmPassword) {
            return $this->error('Passwords do not match');
        }
        if (strlen($password) < 6) {
            return $this->error('Password must be at least 6 characters');
        }
        if (!$this->loginModel->checkActivationDetails($email, $activationCode)) {
            return $this->error('Invalid or expired reset link', 400);
        }

        $this->loginModel->createPasswordUser($email, $password);
        return $this->json([], true, 'Password changed successfully');
    }
}
