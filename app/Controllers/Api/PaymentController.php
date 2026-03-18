<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;

class PaymentController extends ApiBaseController
{
    public function payment_create()
    {
        $body         = $this->getBody();
        $customUserId = null;

        if (session()->get('isLoggedIn') === true) {
            // ── Logged-in user ────────────────────────────────────────────────
            $userId   = (int) session()->get('userId');
            $cart     = $this->webModel->order_data($userId);
            $userInfo = $this->userModel->getUserInfo($userId);
        } else {
            // ── Guest checkout ────────────────────────────────────────────────
            $mobile  = strtolower(esc($body['mobile']  ?? ''));
            $email   = strtolower(esc($body['email']   ?? ''));
            $name    = ucwords(strtolower(esc($body['name'] ?? $body['fname'] ?? '')));
            $address = strtolower(esc($body['address'] ?? ''));

            if (empty($mobile)) {
                return $this->error('Mobile number is required for guest checkout');
            }

            if ($this->loginModel->checkMobileExist($mobile)) {
                $userInfo = $this->userModel->getUserInfoByMobile($mobile);
                if (!empty($email)) {
                    $this->userModel->editUserByMobile(['email' => $email, 'address' => $address], $mobile);
                }
            } else {
                $newUserId = $this->userModel->addNewUser([
                    'name'       => $name,
                    'address'    => $address,
                    'mobile'     => $mobile,
                    'email'      => $email,
                    'roleId'     => 2,
                    'createdDtm' => date('Y-m-d H:i:s'),
                ]);
                if (!$newUserId) {
                    return $this->error('Could not register guest user');
                }
                $userInfo = $this->userModel->getUserInfo($newUserId);
            }

            $userId = (int) ($userInfo->userId ?? $userInfo->id ?? 0);
            if (!$userId) {
                return $this->error('Could not resolve user');
            }

            $customUserId = session()->get('custom_userId') ? (int) session()->get('custom_userId') : null;
            $cart         = $this->webModel->order_data($customUserId ?? $userId);
        }

        if (empty($cart)) {
            return $this->error('Cart is empty');
        }

        $tickets    = [];
        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item->total_price;
            $tickets[]   = ['ticket_no' => $item->ticket_no, 'web_id' => $item->web_id];
        }

        $transactionId = $this->getRandomString();

        // Fix: empty fallback strings — misconfigured env will fail loudly, not silently use live keys
        $keyId     = env('RAZORPAY_KEY_ID',    '');
        $keySecret = env('RAZORPAY_KEY_SECRET', '');
        if (empty($keyId) || empty($keySecret)) {
            return $this->error('Payment gateway not configured', 500);
        }
        $api = new RazorpayApi($keyId, $keySecret);

        $razorpayOrder = $api->order->create([
            'receipt'  => $transactionId,
            'amount'   => (int) ($totalPrice * 100),
            'currency' => 'INR',
            'notes'    => ['tickets' => json_encode($tickets)],
        ]);

        if (!$razorpayOrder['id']) {
            return $this->error('Could not create Razorpay order');
        }

        $this->webModel->insert_order([
            'tickets'                 => json_encode($tickets),
            'user_id'                 => $userId,
            'custom_user_id'          => $customUserId,
            'total_price'             => $totalPrice,
            'paid_type'               => 'RAZORPAY',
            'paid_status'             => 'CREATED',
            'transaction_id'          => $transactionId,
            'razorpay_order_id'       => $razorpayOrder['id'],
            'razorpay_order_response' => json_encode((array) $razorpayOrder),
        ]);

        foreach ($cart as $item) {
            if ($customUserId !== null) {
                $this->webModel->update_cart_data($customUserId, $item->web_id, $item->ticket_no, ['paid_status' => 1]);
            }
            $this->webModel->update_cart_data($userId, $item->web_id, $item->ticket_no, ['paid_status' => 1]);
        }

        session()->set('payment_order_id', $razorpayOrder['id']);

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

    public function payment_confirm()
    {
        helper('email_helper');
        $body              = $this->getBody();
        $razorpayOrderId   = esc($body['razorpay_order_id']  ?? '');
        $razorpayPayId     = esc($body['razorpay_payment_id'] ?? '');
        $razorpaySignature = esc($body['razorpay_signature']  ?? '');
        $type              = $body['type'] ?? 'ticket';

        if (empty($razorpayOrderId) || empty($razorpayPayId) || empty($razorpaySignature)) {
            return $this->error('Payment response incomplete');
        }

        // Fix: empty fallback strings — fails loudly, not silently using live keys
        $keyId     = env('RAZORPAY_KEY_ID',    '');
        $keySecret = env('RAZORPAY_KEY_SECRET', '');
        if (empty($keyId) || empty($keySecret)) {
            return $this->error('Payment gateway not configured', 500);
        }
        $api = new RazorpayApi($keyId, $keySecret);

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPayId,
                'razorpay_signature'  => $razorpaySignature,
            ]);

            if ($type === 'wallet') {
                if ($r = $this->requireAuth()) return $r;
                $userId = (int) session()->get('userId');

                // Fix: validate wallet_amount from session is positive
                $amount = (float) (session()->get('wallet_amount') ?? 0);
                if ($amount <= 0) {
                    return $this->error('Invalid wallet top-up session — please retry', 400);
                }

                $wallet = $this->webModel->wallet($userId);
                if (!$wallet) {
                    $this->webModel->insert_date('tbl_wallet', ['user_id' => $userId, 'money' => $amount]);
                } else {
                    $this->webModel->editWeb_all('tbl_wallet', ['money' => $wallet->money + $amount], $wallet->id);
                }
                $this->webModel->insert_date('tbl_wallet_history', [
                    'user_id'        => $userId,
                    'money'          => $amount,
                    'trancaction_id' => $razorpayPayId,
                    'type'           => 'Credit',
                    'p_type'         => 'RAZORPAY',
                ]);
                session()->remove('wallet_order_id');
                session()->remove('wallet_amount');
                return $this->json(['status' => 'CREDITED'], true, 'Wallet topped up');
            }

            // ── Ticket payment confirmation ────────────────────────────────────

            // Fix: verify order exists and belongs to the authenticated user
            $orderDetails = $this->webModel->get_order_by_orderId($razorpayOrderId);
            if (!$orderDetails) {
                return $this->error('Order not found', 404);
            }
            if (session()->get('isLoggedIn') === true &&
                (int) $orderDetails->user_id !== (int) session()->get('userId')) {
                return $this->error('You are not authorised to confirm this order', 403);
            }

            $this->webModel->update_order_by_orderId($razorpayOrderId, [
                'paid_status'      => 'PAID',
                'order_status'     => 1,
                'payment_response' => json_encode($body),
            ]);

            $userInfo      = $this->userModel->getUserInfo($orderDetails->user_id);
            $tickets       = json_decode($orderDetails->tickets, true);
            $ticketDetails = [];
            foreach ($tickets as $t) {
                $ticketDetails[] = [
                    'webInfo'  => $this->webModel->getallWebInfo('tbl_webs', $t['web_id']),
                    'range'    => $this->webModel->getrangeInfo($t['web_id']),
                    'ticketNo' => $t['ticket_no'],
                ];
            }

            $emailBody = view('frontend/email_ticket', [
                'ticket_details' => $ticketDetails,
                'status'         => 'Payment Successful',
                'details'        => [
                    'razorpay_order_id'   => $razorpayOrderId,
                    'razorpay_payment_id' => $razorpayPayId,
                ],
            ]);
            sendmail($userInfo->email, 'Order: ' . $razorpayOrderId, $emailBody);

            return $this->json(['status' => 'PAID'], true, 'Payment verified');

        } catch (PaymentError $e) {
            $this->webModel->update_order_by_orderId($razorpayOrderId, [
                'order_status'     => 2,
                'payment_response' => json_encode($body),
            ]);
            return $this->error('Signature verification failed: ' . $e->getMessage(), 400);
        }
    }

    public function payment_cancel()
    {
        $body    = $this->getBody();
        $orderId = esc($body['order_id'] ?? '');
        if (empty($orderId)) {
            return $this->error('order_id required');
        }
        $this->webModel->update_order_by_orderId($orderId, [
            'order_status'     => 2,
            'payment_response' => json_encode($body),
        ]);
        return $this->json([], true, 'Order cancelled');
    }
}
