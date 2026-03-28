<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';

use Razorpay\Api\Api as RazorpayApi;

class AccountController extends ApiBaseController
{
    public function account_profile()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        $user   = $this->userModel->getUserInfo($userId);
        return $this->json($user ? (array) $user : []);
    }

    public function account_profile_update()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        $body   = $this->getBody();
        $name   = ucwords(strtolower(esc($body['name']   ?? '')));
        $mobile = esc($body['mobile'] ?? '');
        if (empty($name)) {
            return $this->error('Name is required');
        }
        $this->userModel->editUser([
            'name'       => $name,
            'mobile'     => $mobile,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ], $userId);
        session()->set('name',   $name);
        session()->set('mobile', $mobile);
        return $this->json([], true, 'Profile updated');
    }

    public function account_password()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId      = (int) session()->get('userId');
        $body        = $this->getBody();
        $oldPassword = $body['old_password'] ?? '';
        $newPassword = $body['new_password'] ?? '';
        if (empty($oldPassword) || empty($newPassword)) {
            return $this->error('All fields are required');
        }
        if (strlen($newPassword) < 6) {
            return $this->error('Password must be at least 6 characters');
        }
        if (empty($this->userModel->matchOldPassword($userId, $oldPassword))) {
            return $this->error('Current password is incorrect', 401);
        }
        $this->userModel->changePassword($userId, [
            'password'   => getHashedPassword($newPassword),
            'updatedDtm' => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Password updated');
    }

    public function account_wallet()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId  = (int) session()->get('userId');
        $wallet  = $this->walletModel->wallet($userId);
        $history = $this->walletModel->wallet_history($userId);
        return $this->json([
            'balance' => $wallet ? (float) $wallet->money : 0.0,
            'history' => $history,
        ]);
    }

    public function account_wallet_topup()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId   = (int) session()->get('userId');
        $userInfo = $this->userModel->getUserInfo($userId);
        $body     = $this->getBody();
        $amount   = (float) ($body['amount'] ?? 0);
        if ($amount < 1) {
            return $this->error('Invalid amount');
        }

        // Fix: empty fallback — misconfigured env fails loudly, not silently
        $keyId     = env('RAZORPAY_KEY_ID',    '');
        $keySecret = env('RAZORPAY_KEY_SECRET', '');
        if (empty($keyId) || empty($keySecret)) {
            return $this->error('Payment gateway not configured', 500);
        }
        $api = new RazorpayApi($keyId, $keySecret);

        $razorpayOrder = $api->order->create([
            'receipt'  => $this->getRandomString(),
            'amount'   => (int) ($amount * 100),
            'currency' => 'INR',
            'notes'    => ['type' => 'wallet', 'user_id' => $userId, 'amount' => $amount],
        ]);
        if (!$razorpayOrder['id']) {
            return $this->error('Could not create Razorpay order');
        }
        session()->set('wallet_order_id', $razorpayOrder['id']);
        session()->set('wallet_amount',   $amount);
        return $this->json([
            'order_id'    => $razorpayOrder['id'],
            'amount'      => $razorpayOrder['amount'],
            'currency'    => $razorpayOrder['currency'],
            'key_id'      => $keyId,
            'user_name'   => $userInfo->name   ?? '',
            'user_email'  => $userInfo->email  ?? '',
            'user_mobile' => $userInfo->mobile ?? '',
        ]);
    }

    public function account_orders()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        return $this->json(['orders' => $this->cartOrderModel->order_history($userId)]);
    }

    public function account_refunds()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->refund_history($userId));
    }

    public function account_refund_create()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId  = (int) session()->get('userId');
        $body    = $this->getBody();
        $orderId = (int) ($body['order_id'] ?? 0);
        $reason  = esc($body['reason']   ?? '');
        if (empty($orderId) || empty($reason)) {
            return $this->error('Order ID and reason are required');
        }

        // Fix: verify order exists and belongs to the authenticated user
        $order = $this->cartOrderModel->get_order_by_id($orderId);
        if (!$order || (int) $order->user_id !== $userId) {
            return $this->error('Order not found', 404);
        }

        $this->walletModel->insert_date('tbl_refund', [
            'user_id'    => $userId,
            'order_id'   => $orderId,
            'reason'     => $reason,
            'status'     => 0,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Refund request submitted');
    }

    public function account_withdrawals()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->withdrawl_history($userId));
    }

    public function account_withdrawal_create()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId        = (int) session()->get('userId');
        $body          = $this->getBody();
        $amount        = (float) ($body['amount']         ?? 0);
        $accountNumber = esc($body['account_number']   ?? '');
        $ifsc          = esc($body['ifsc']             ?? '');
        $accountName   = esc($body['account_name']     ?? '');
        if ($amount < 1 || empty($accountNumber) || empty($ifsc) || empty($accountName)) {
            return $this->error('All fields are required');
        }

        // Fix: verify wallet balance before accepting withdrawal request
        $wallet = $this->walletModel->wallet($userId);
        if (!$wallet || (float) $wallet->money < $amount) {
            return $this->error('Insufficient wallet balance');
        }

        $this->walletModel->insert_date('tbl_withdrawl', [
            'user_id'        => $userId,
            'amount'         => $amount,
            'account_number' => $accountNumber,
            'ifsc'           => $ifsc,
            'account_name'   => $accountName,
            'status'         => 0,
            'createdDtm'     => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Withdrawal request submitted');
    }

    public function account_transfers()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->transfer_history($userId));
    }

    public function account_transfer_create()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        $body   = $this->getBody();
        $toUser = trim($body['to_user'] ?? '');
        $amount = (float) ($body['amount'] ?? 0);
        $note   = esc($body['note'] ?? '');

        if (empty($toUser) || $amount < 1) {
            return $this->error('Recipient and amount are required');
        }
        $recipient = is_numeric($toUser)
            ? $this->userModel->getUserInfo((int) $toUser)
            : $this->loginModel->getCustomerInfoByEmail(strtolower($toUser));
        if (!$recipient) {
            return $this->error('Recipient not found', 404);
        }
        $wallet = $this->walletModel->wallet($userId);
        if (!$wallet || (float) $wallet->money < $amount) {
            return $this->error('Insufficient wallet balance');
        }

        // Fix: wrap entire debit + credit + history inserts in a single transaction
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('tbl_wallet')->where('id', $wallet->id)
           ->update(['money' => $wallet->money - $amount]);
        $db->table('tbl_wallet_history')->insert([
            'user_id' => $userId, 'money' => $amount, 'type' => 'Debit', 'p_type' => 'Transfer',
        ]);

        $recipientWallet = $this->walletModel->wallet($recipient->userId);
        if (!$recipientWallet) {
            $db->table('tbl_wallet')->insert(['user_id' => $recipient->userId, 'money' => $amount]);
        } else {
            $db->table('tbl_wallet')->where('id', $recipientWallet->id)
               ->update(['money' => $recipientWallet->money + $amount]);
        }
        $db->table('tbl_wallet_history')->insert([
            'user_id' => $recipient->userId, 'money' => $amount, 'type' => 'Credit', 'p_type' => 'Transfer',
        ]);
        $db->table('tbl_transfer')->insert([
            'user_id'    => $userId,
            'to_user_id' => $recipient->userId,
            'amount'     => $amount,
            'note'       => $note,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->error('Transfer failed, please try again');
        }

        return $this->json([], true, 'Transfer successful');
    }

    public function account_winners()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        return $this->json($this->winnerModel->winner_history($userId));
    }
}

