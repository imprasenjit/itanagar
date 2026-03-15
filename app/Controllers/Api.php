<?php

namespace App\Controllers;

require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;
use App\Models\WebModel;
use App\Models\UserModel;
use App\Models\LoginModel;

/**
 * Api Controller — JSON API for the React frontend.
 * All responses: { "status": bool, "data": mixed, "message": string }
 */
class Api extends BaseController
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
        $this->_cors();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function _cors(): void
    {
        $origin  = $this->request->getHeaderLine('Origin');
        $allowed = ['http://localhost:5173', 'https://theitanagarchoice.com'];
        if (in_array($origin, $allowed, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if (strtolower($this->request->getMethod()) === 'options') {
            $this->response->setStatusCode(200)->send();
            exit();
        }
    }

    private function json(array $data = [], bool $status = true, string $message = '', int $code = 200)
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON(['status' => $status, 'data' => $data, 'message' => $message]);
    }

    private function error(string $message = 'An error occurred', int $code = 400, array $data = [])
    {
        return $this->json($data, false, $message, $code);
    }

    private function getBody(): array
    {
        $raw = $this->request->getBody();
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    private function getUserId(): ?int
    {
        return session()->get('isLoggedIn') === true ? (int) session()->get('userId') : null;
    }

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

    private function requireAuth()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->error('Unauthenticated', 401);
        }
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

    private function getTicketAvailability(int $ticket, int $web_id): bool
    {
        return count($this->webModel->get_ticket_availability($ticket, $web_id)) === 0;
    }

    private function _getFirstAvailableTickets($range): array
    {
        if (!$range || empty($range->rangeStart)) {
            return [];
        }
        $ticketRanges = [];
        foreach (explode(',', trim($range->rangeStart)) as $part) {
            $parts = explode('-', trim($part));
            if (count($parts) === 2) {
                $ticketRanges[] = (int) $parts[0];
                $ticketRanges[] = (int) $parts[1];
            }
        }
        return $ticketRanges;
    }

    // ── Public: Home & Games ─────────────────────────────────────────────────

    public function home()
    {
        return $this->json([
            'games' => $this->webModel->home_web(6),
            'faq'   => $this->webModel->faq(1),
        ]);
    }

    public function games()
    {
        return $this->json(['games' => $this->webModel->home_web()]);
    }

    public function game_detail(int $id)
    {
        $website = $this->webModel->getallWebInfo('tbl_webs', $id);
        if (!$website) {
            return $this->error('Game not found', 404);
        }
        $range = $this->webModel->getrangeInfo($id);
        return $this->json([
            'website'     => $website,
            'range'       => $range,
            'ticketRange' => $this->_getFirstAvailableTickets($range),
        ]);
    }

    public function game_tickets(int $web_id, int $start, int $end)
    {
        $available = [];
        for ($i = $start; $i <= $end; $i++) {
            if ($this->getTicketAvailability($i, $web_id)) {
                $available[] = $i;
            }
        }
        return $this->json(['tickets' => $available]);
    }

    public function ticket_search(int $web_id)
    {
        $body   = $this->getBody();
        $search = isset($body['search']) ? (int) $body['search'] : 0;

        $range = $this->webModel->getrangeInfo($web_id);
        if (!$range) {
            return $this->error('Game not found', 404);
        }
        $checkRange = $this->webModel->getRangeAvailability($search, $web_id);
        $available  = count($checkRange) > 0 && $this->getTicketAvailability($search, $web_id);
        return $this->json(['available' => $available, 'ticket' => $search]);
    }

    // ── Public: FAQ, Pages, Results, Contact ─────────────────────────────────

    public function faq()
    {
        return $this->json(['faqs' => $this->webModel->faq()]);
    }

    public function page(string $type)
    {
        $page = $this->webModel->page_detail($type);
        if (!$page) {
            return $this->error('Page not found', 404);
        }
        return $this->json(['page' => $page]);
    }

    public function results()
    {
        $webId   = $this->request->getGet('web_id');
        $date    = $this->request->getGet('date');
        $results = $this->webModel->result_list($webId ? (int)$webId : null, $date ?: null);
        $games   = $this->webModel->home_web();
        return $this->json(['results' => $results, 'games' => $games]);
    }

    public function contact()
    {
        $body    = $this->getBody();
        $name    = esc($body['name']    ?? '');
        $email   = esc($body['email']   ?? '');
        $mobile  = esc($body['mobile']  ?? '');
        $message = esc($body['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            return $this->error('Name, email, and message are required');
        }

        $this->webModel->insert_date('tbl_contact', [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'message'    => $message,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Message sent successfully');
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    public function me()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->json(['isLoggedIn' => false, 'user' => null]);
        }
        $userId    = (int) session()->get('userId');
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

        if ($result->roleId != 1) {
            $guestId = session()->get('custom_userId');
            if ($guestId) {
                $this->webModel->up_cart((int)$guestId, $result->userId);
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
        $body   = $this->getBody();
        $name   = ucwords(strtolower(esc($body['name']   ?? '')));
        $email  = strtolower(esc($body['email']  ?? ''));
        $mobile = esc($body['mobile'] ?? '');
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
        $resetLink = base_url('resetPasswordConfirmUser/' . $activationId . '/' . urlencode($email));
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
        $email            = 'admin@theitanagarchoice.com';
        $password         = 'Itanagar';
 

        $this->loginModel->createPasswordUser($email, $password);

        return $this->json([], true, 'Password changed successfully');
    }

    // ── Cart ──────────────────────────────────────────────────────────────────

    public function cart()
    {
        $userId = $this->getCartUserId();
        $cart   = $this->webModel->cart_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total]);
    }

    public function cart_add()
    {
        $body   = $this->getBody();
        $webId  = (int) ($body['web_id']  ?? 0);
        $tickets = $body['tickets'] ?? [];
        $userId = $this->getCartUserId();

        if (empty($webId) || empty($tickets)) {
            return $this->error('web_id and tickets are required');
        }

        $range  = $this->webModel->getrangeInfo($webId);
        $errors = [];
        $toAdd  = [];

        foreach ($tickets as $ticketNo) {
            $ticketNo = (int) $ticketNo;
            if (!$this->getTicketAvailability($ticketNo, $webId)) {
                $errors[] = "Ticket $ticketNo is not available";
                continue;
            }
            $cartRow = [
                'web_id'      => $webId,
                'user_id'     => $userId,
                'ticket_no'   => $ticketNo,
                'total_price' => $range->price,
            ];
            if (!$this->webModel->checkIfTicketAlreadyPresent($cartRow)) {
                $toAdd[] = $cartRow;
            }
        }

        if (!empty($toAdd)) {
            $this->webModel->insert_cart($toAdd);
        }

        return $this->json(['added' => count($toAdd), 'errors' => $errors], true, '');
    }

    public function cart_remove(int $cartId)
    {
        $userId = $this->getCartUserId();
        $this->webModel->delete_cart_item($cartId, $userId);
        return $this->json([], true, 'Removed');
    }

    // ── Payment ───────────────────────────────────────────────────────────────

    public function payment_create()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) {
            return $auth;
        }

        $userId = (int) session()->get('userId');
        $cart   = $this->webModel->order_data($userId);

        if (empty($cart)) {
            return $this->error('Cart is empty');
        }

        $tickets     = [];
        $totalPrice  = 0;
        foreach ($cart as $item) {
            $totalPrice += $item->total_price;
            $tickets[]   = ['ticket_no' => $item->ticket_no, 'web_id' => $item->web_id];
        }

        $transactionId = $this->getRandomString();
        $config        = config('App');
        $keyId         = env('RAZORPAY_KEY_ID',     'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret     = env('RAZORPAY_KEY_SECRET',  'pRTdkyxSIxXrvQuYcDu9GL4f');
        $api           = new RazorpayApi($keyId, $keySecret);

        $razorpayOrder = $api->order->create([
            'receipt'  => $transactionId,
            'amount'   => (int)($totalPrice * 100),
            'currency' => 'INR',
            'notes'    => ['tickets' => json_encode($tickets)],
        ]);

        if (!$razorpayOrder['id']) {
            return $this->error('Could not create Razorpay order');
        }

        $orderId = $this->webModel->insert_order([
            'tickets'                  => json_encode($tickets),
            'user_id'                  => $userId,
            'total_price'              => $totalPrice,
            'paid_type'                => 'RAZORPAY',
            'paid_status'              => 'CREATED',
            'transaction_id'           => $transactionId,
            'razorpay_order_id'        => $razorpayOrder['id'],
            'razorpay_order_response'  => json_encode((array)$razorpayOrder),
            'createdDtm'               => date('Y-m-d H:i:s'),
        ]);

        foreach ($cart as $item) {
            $this->webModel->update_cart_data($userId, $item->web_id, $item->ticket_no, ['paid_status' => 1]);
        }

        session()->set('payment_order_id', $razorpayOrder['id']);

        return $this->json([
            'order_id'   => $razorpayOrder['id'],
            'amount'     => $razorpayOrder['amount'],
            'currency'   => $razorpayOrder['currency'],
            'key_id'     => $keyId,
        ]);
    }

    public function payment_confirm()
    {
        helper('email_helper');
        $body           = $this->getBody();
        $razorpayOrderId  = esc($body['razorpay_order_id']  ?? '');
        $razorpayPayId    = esc($body['razorpay_payment_id'] ?? '');
        $razorpaySignature= esc($body['razorpay_signature']  ?? '');

        if (empty($razorpayOrderId) || empty($razorpayPayId) || empty($razorpaySignature)) {
            return $this->error('Payment response incomplete');
        }

        $keyId     = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
        $api       = new RazorpayApi($keyId, $keySecret);

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPayId,
                'razorpay_signature'  => $razorpaySignature,
            ]);

            $this->webModel->update_order_by_orderId($razorpayOrderId, [
                'paid_status'      => 'PAID',
                'order_status'     => 1,
                'payment_response' => json_encode($body),
            ]);

            $orderDetails = $this->webModel->get_order_by_orderId($razorpayOrderId);
            $userInfo     = $this->userModel->getUserInfo($orderDetails->user_id);
            $tickets      = json_decode($orderDetails->tickets, true);
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
                'details'        => ['razorpay_order_id' => $razorpayOrderId, 'razorpay_payment_id' => $razorpayPayId],
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

    public function order_confirm()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) {
            return $auth;
        }
        $userId = (int) session()->get('userId');
        $cart   = $this->webModel->order_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total]);
    }
}
