<?php

namespace App\Controllers;

require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;
use App\Models\WebModel;
use App\Models\UserModel;
use App\Models\LoginModel;

class Game extends BaseController
{
    protected WebModel   $webModel;
    protected UserModel  $userModel;
    protected LoginModel $loginModel;

    protected $helpers = ['url', 'cias_helper', 'email_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->webModel   = new WebModel();
        $this->userModel  = new UserModel();
        $this->loginModel = new LoginModel();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getCartUserId(): int
    {
        if (session()->get('isLoggedIn') === true) {
            return (int) session()->get('userId');
        }
        if (!session()->has('custom_userId')) {
            session()->set('custom_userId', random_int(100000000, 999999999));
        }
        return (int) session()->get('custom_userId');
    }

    private function getTicketAvailability(int $ticket, int $webId): bool
    {
        return count($this->webModel->get_ticket_availability($ticket, $webId)) === 0;
    }

    private function getFirstAvailableTickets(int $webId): array
    {
        $range = $this->webModel->getrangeInfo($webId);
        if (!$range || empty($range->rangeStart)) {
            return [];
        }
        $ticketRanges = [];
        foreach (explode(',', trim($range->rangeStart)) as $part) {
            $parts = explode('-', trim($part));
            if (count($parts) === 2) {
                $ticketRanges[] = (int)$parts[0];
                $ticketRanges[] = (int)$parts[1];
            }
        }
        return $ticketRanges;
    }

    private function getRandomString(int $length = 16): string
    {
        $chars  = '0123456789abcdefghijklmnopqrstuvwxyz';
        $max    = mb_strlen($chars, '8bit') - 1;
        $pieces = [];
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $chars[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    // ── Public Game Pages ─────────────────────────────────────────────────────

    public function index()
    {
        $data['website'] = $this->webModel->home_web();
        return view('frontend/header') . view('frontend/game', $data) . view('frontend/footer');
    }

    public function type(int $id)
    {
        $data['website'] = $this->webModel->getallWebInfo('tbl_webs', $id);
        if (!$data['website']) {
            return redirect()->to(base_url());
        }
        $data['range']       = $this->webModel->getrangeInfo($id);
        $data['ticketRange'] = $this->getFirstAvailableTickets($id);
        return view('frontend/header') . view('frontend/game_detail', $data) . view('frontend/footer');
    }

    public function jackpot()
    {
        $data['lottery'] = $this->webModel->lottery_web();
        return view('frontend/header') . view('frontend/jackpot', $data) . view('frontend/footer');
    }

    public function tickets(int $webId, int $start, int $end)
    {
        $ticketArray = [];
        for ($i = $start; $i <= $end; $i++) {
            if ($this->getTicketAvailability($i, $webId)) {
                $ticketArray[] = $i;
            }
        }
        $data = [
            'range'   => $this->webModel->getrangeInfo($webId),
            'website' => $this->webModel->getallWebInfo('tbl_webs', $webId),
            'tickets' => $ticketArray,
        ];
        return view('frontend/header') . view('frontend/ticket_details', $data) . view('frontend/footer');
    }

    public function getavailabletickets()
    {
        $webId  = (int) $this->request->getPost('web_id', true);
        $search = (int) $this->request->getPost('search', true);

        $range = $this->webModel->getrangeInfo($webId);
        if (!$range) {
            return $this->response->setContentType('application/json')
                ->setJSON(['error' => 'Game Not found']);
        }

        $ticketArray = [];
        $status      = false;
        $checkRange  = $this->webModel->getRangeAvailability($search, $webId);

        if (count($checkRange) > 0 && $this->getTicketAvailability($search, $webId)) {
            $ticketArray[] = ['id' => $search, 'text' => $search];
            $status        = true;
        }

        return $this->response->setContentType('application/json')
            ->setJSON(['status' => $status, 'results' => $ticketArray]);
    }

    public function result()
    {
        $lottery = $this->webModel->lottery_web();
        $error   = 0;

        $first_l = $this->request->getGet('game');
        $sdate   = $this->request->getGet('sdate');

        if (!empty($first_l)) {
            if (!empty($sdate)) {
                $d = date('Y-m-d', strtotime($sdate));
            } else {
                $error = 1;
                $d     = date('Y-m-d');
            }
        } else {
            $first_l = !empty($lottery) ? $lottery[0]->id : 0;
            $dates   = $this->webModel->getalldates_result($first_l);
            $d       = !empty($dates) ? $dates[0]->date : date('Y-m-d');
        }

        $winner = $this->webModel->winner_detail();
        $data   = ['w_id' => $first_l, 'lottery' => $lottery, 'winner' => $winner, 'search_date' => $d];

        if ($error == 1 || empty($winner)) {
            session()->setFlashdata('error', 'Something Went Wrong! Please try again.');
            return redirect()->to('game/result');
        }

        return view('frontend/header', $data) . view('frontend/result') . view('frontend/footer');
    }

    // ── Cart ──────────────────────────────────────────────────────────────────

    public function addtocart()
    {
        $userId      = $this->getCartUserId();
        $webId       = (int) $this->request->getPost('web_id');
        $range       = $this->webModel->getrangeInfo($webId);
        $ticketArray = $this->request->getPost('tickets') ?? [];
        $cartArray   = [];
        $errors      = [];

        foreach ($ticketArray as $value) {
            $ticketNo = (int) $value;
            if (!$this->getTicketAvailability($ticketNo, $webId)) {
                $errors[] = ['error' => "Ticket No : $ticketNo is not available"];
            } else {
                $cartData = [
                    'web_id'      => $webId,
                    'user_id'     => $userId,
                    'ticket_no'   => $ticketNo,
                    'total_price' => $range->price,
                ];
                if (!$this->webModel->checkIfTicketAlreadyPresent($cartData)) {
                    $cartArray[] = $cartData;
                }
            }
        }

        if (!empty($errors)) {
            session()->setFlashdata('error', $errors);
        }
        if (!empty($cartArray)) {
            $this->webModel->insert_cart($cartArray);
        }

        return redirect()->to('game/confirm_order');
    }

    public function step2()
    {
        $userId       = $this->getCartUserId();
        $data['data'] = $this->webModel->cart_data($userId);
        return view('frontend/header') . view('frontend/step2', $data) . view('frontend/footer');
    }

    public function confirm_order()
    {
        $userId = $this->getCartUserId();
        $data   = [
            'data'     => $this->webModel->order_data($userId),
            'loggedIn' => session()->get('isLoggedIn') === true,
        ];

        if (empty($data['data'])) {
            return redirect()->to('game/step2');
        }

        return view('frontend/header') . view('frontend/confirm_order', $data) . view('frontend/footer');
    }

    public function deletecartdata()
    {
        $userId = (int) $this->request->getPost('userId');
        $this->webModel->deleteWeb('tbl_cart', $userId);
        return redirect()->to('game/step2');
    }

    // ── Payment ───────────────────────────────────────────────────────────────

    private function createRazorpayOrder(int $userId, array $cart, ?int $customUserId = null): ?object
    {
        $tickets   = [];
        $total     = 0;
        foreach ($cart as $item) {
            $total      += $item->total_price;
            $tickets[]   = ['ticket_no' => $item->ticket_no, 'web_id' => $item->web_id];
        }

        if ($total === 0) {
            return null;
        }

        $transactionId = $this->getRandomString();
        $keyId         = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret     = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
        $api           = new RazorpayApi($keyId, $keySecret);

        $razorpayOrder = $api->order->create([
            'receipt'  => $transactionId,
            'amount'   => (int)($total * 100),
            'currency' => 'INR',
            'notes'    => ['tickets' => json_encode($tickets)],
        ]);

        if (!$razorpayOrder['id']) {
            return null;
        }

        $this->webModel->insert_order([
            'tickets'                  => json_encode($tickets),
            'user_id'                  => $userId,
            'custom_user_id'           => $customUserId,
            'total_price'              => $total,
            'paid_type'                => 'RAZORPAY',
            'paid_status'              => 'CREATED',
            'transaction_id'           => $transactionId,
            'razorpay_order_id'        => $razorpayOrder['id'],
            'razorpay_order_response'  => json_encode((array)$razorpayOrder),
            'createdDtm'               => date('Y-m-d H:i:s'),
        ]);

        foreach ($cart as $item) {
            $cartData = ['paid_status' => 1];
            if ($customUserId !== null) {
                $this->webModel->update_cart_data($customUserId, $item->web_id, $item->ticket_no, $cartData);
            }
            $this->webModel->update_cart_data($userId, $item->web_id, $item->ticket_no, $cartData);
        }

        return $razorpayOrder;
    }

    private function registerGuestUser(): array
    {
        $mobile  = strtolower(esc($this->request->getPost('mobile') ?? ''));
        $email   = strtolower(esc($this->request->getPost('email')  ?? ''));
        $name    = ucwords(strtolower(esc($this->request->getPost('fname') ?? '')));
        $address = strtolower(esc($this->request->getPost('address') ?? ''));

        if ($this->loginModel->checkMobileExist($mobile)) {
            $userInfo = $this->userModel->getUserInfoByMobile($mobile);
            if (!empty($email)) {
                $this->userModel->editUserByMobile(['email' => $email, 'address' => $address], $mobile);
            }
            return (array) $userInfo;
        }

        $userData = [
            'name'        => $name,
            'address'     => $address,
            'mobile'      => $mobile,
            'email'       => $email,
            'roleId'      => 2,
            'createdDtm'  => date('Y-m-d H:i:s'),
        ];
        $result = $this->userModel->addNewUser($userData);
        if ($result > 0) {
            $userData['userId'] = $result;
            return $userData;
        }
        return [];
    }

    public function payment()
    {
        $keyId = env('RAZORPAY_KEY_ID', 'rzp_live_mIBUSRL7Pn4XUj');

        if (session()->get('isLoggedIn') !== true) {
            $customUserId  = $this->getCartUserId();
            $userDetails   = $this->registerGuestUser();
            $userId        = (int) ($userDetails['userId'] ?? 0);
            $cart          = $this->webModel->order_data($customUserId);
            $razorpayOrder = $this->createRazorpayOrder($userId, $cart, $customUserId);
        } else {
            $userId        = (int) session()->get('userId');
            $userDetails   = (array) $this->userModel->getUserInfo($userId);
            $cart          = $this->webModel->order_data($userId);
            $razorpayOrder = $this->createRazorpayOrder($userId, $cart);
        }

        if (!$razorpayOrder) {
            session()->setFlashdata('error', 'Could not create order. Cart may be empty.');
            return redirect()->to('game/confirm_order');
        }

        session()->set('payment_order_id', $razorpayOrder['id']);

        $data['cart']  = $cart;
        $data['order'] = (object)[
            'key_id'       => $keyId,
            'amount'       => $razorpayOrder['amount'],
            'currency'     => $razorpayOrder['currency'],
            'name'         => APP_NAME,
            'description'  => 'Event Tickets',
            'image'        => base_url('public/images/logo.png'),
            'order_id'     => $razorpayOrder['id'],
            'callback_url' => base_url('game/payment_confirm'),
            'prefill'      => [
                'name'    => $userDetails['name']   ?? '',
                'contact' => $userDetails['mobile'] ?? '',
            ],
        ];

        return view('frontend/header') . view('frontend/payment', $data) . view('frontend/footer');
    }

    public function payment_confirm()
    {
        $razorpayOrderId  = esc($this->request->getPost('razorpay_order_id',  true) ?? '');
        $razorPayPaymentId = esc($this->request->getPost('razorpay_payment_id', true) ?? '');
        $razorpaySignature = esc($this->request->getPost('razorpay_signature',  true) ?? '');

        $paymentResponse = [
            'razorpay_order_id'   => $razorpayOrderId,
            'razorpay_payment_id' => $razorPayPaymentId,
            'razorpay_signature'  => $razorpaySignature,
        ];

        $orderDetails  = $this->webModel->get_order_by_orderId($razorpayOrderId);
        $userInfo      = $this->userModel->getUserInfo($orderDetails->user_id);
        $tickets       = json_decode($orderDetails->tickets);
        $ticketDetails = [];

        foreach ($tickets as $ticket) {
            $ticketDetails[] = [
                'webInfo'  => $this->webModel->getallWebInfo('tbl_webs', $ticket->web_id),
                'range'    => $this->webModel->getrangeInfo($ticket->web_id),
                'ticketNo' => $ticket->ticket_no,
            ];
        }

        $success   = false;
        $orderData = [];

        if (!empty($razorPayPaymentId) && !empty($razorpaySignature)) {
            $keyId     = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
            $keySecret = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
            $api       = new RazorpayApi($keyId, $keySecret);

            try {
                $api->utility->verifyPaymentSignature([
                    'razorpay_order_id'   => session()->get('payment_order_id'),
                    'razorpay_payment_id' => $razorPayPaymentId,
                    'razorpay_signature'  => $razorpaySignature,
                ]);
                $orderData = ['order_status' => 1, 'payment_response' => json_encode($paymentResponse)];
                $success   = true;
            } catch (PaymentError $e) {
                $orderData = ['order_status' => 2, 'payment_response' => json_encode($paymentResponse)];
            }
        }

        $this->webModel->update_order_by_orderId($razorpayOrderId, $orderData);

        $data = ['ticket_details' => $ticketDetails];

        if ($success) {
            $data['status']  = 'Payment Successful';
            $data['details'] = ['razorpay_order_id' => $razorpayOrderId, 'razorpay_payment_id' => $razorPayPaymentId];
            $emailBody = view('frontend/email_ticket', $data);
            sendmail($userInfo->email, 'Order: ' . $razorpayOrderId, $emailBody);
            return view('frontend/header') . view('frontend/payment_confirmation', $data) . view('frontend/footer');
        }

        $data['status']  = 'Payment Failed';
        $data['details'] = ['razorpay_order_id' => $razorpayOrderId, 'razorpay_payment_id' => $razorPayPaymentId];
        return view('frontend/header') . view('frontend/payment_failed', $data) . view('frontend/footer');
    }

    public function payment_cancelled()
    {
        $orderId = esc($this->request->getPost('order_id', true) ?? '');
        $this->webModel->update_order_by_orderId($orderId, [
            'order_status'     => 2,
            'payment_response' => json_encode([
                'razorpay_order_id'   => $orderId,
                'razorpay_payment_id' => $this->request->getPost('payment_id',   true),
                'reason'              => $this->request->getPost('reason',       true),
                'description'        => $this->request->getPost('description',   true),
            ]),
        ]);
    }
}
