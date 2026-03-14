<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . '/libraries/razorpay-php/Razorpay.php');

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;

/**
 * Api Controller — JSON API for the React frontend
 * All methods return JSON with shape: { "status": bool, "data": mixed, "message": string }
 */
class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['web_model', 'user_model', 'login_model']);
        $this->load->library('form_validation');
        $this->_cors();
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function _cors()
    {
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function json($data = [], $status = true, $message = '', $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'data' => $data, 'message' => $message]);
        exit();
    }

    private function error($message = 'An error occurred', $code = 400, $data = [])
    {
        $this->json($data, false, $message, $code);
    }

    private function getBody()
    {
        $raw = file_get_contents('php://input');
        return $raw ? json_decode($raw, true) : [];
    }

    private function getUserId()
    {
        if ($this->session->userdata('isLoggedIn') == TRUE) {
            return $this->session->userdata('userId');
        }
        return null;
    }

    private function getCartUserId()
    {
        if ($this->session->userdata('isLoggedIn') == TRUE) {
            return $this->session->userdata('userId');
        }
        // Auto-create guest session id
        if (!$this->session->userdata('custom_userId')) {
            $this->session->set_userdata('custom_userId', mt_rand(100000000, 999999999));
        }
        return $this->session->userdata('custom_userId');
    }

    private function requireAuth()
    {
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $this->error('Unauthenticated', 401);
        }
    }

    private function getRandomString($length = 16)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $pieces = [];
        $max = mb_strlen($chars, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $chars[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    private function getTicketAvailability($ticket, $web_id)
    {
        $result = $this->web_model->get_ticket_availability($ticket, $web_id);
        return count($result) === 0;
    }

    // ─── Public: Home & Games ───────────────────────────────────────────────────

    /** GET /api/home */
    public function home()
    {
        $games = $this->web_model->home_web(6);
        $faq   = $this->web_model->faq(1);
        $this->json([
            'games' => $games,
            'faq'   => $faq,
        ]);
    }

    /** GET /api/games */
    public function games()
    {
        $games = $this->web_model->home_web();
        $this->json(['games' => $games]);
    }

    /** GET /api/games/:id */
    public function game_detail($id)
    {
        $website = $this->web_model->getallWebInfo('tbl_webs', $id);
        if (!$website) {
            $this->error('Game not found', 404);
        }
        $range = $this->web_model->getrangeInfo($id);
        $ticketRange = $this->_getFirstAvailableTickets($range);

        $this->json([
            'website'     => $website,
            'range'       => $range,
            'ticketRange' => $ticketRange,
        ]);
    }

    private function _getFirstAvailableTickets($range)
    {
        if (!$range || empty($range->rangeStart)) return [];
        $rangeSplit = explode(',', trim($range->rangeStart));
        $ticketRanges = [];
        foreach ($rangeSplit as $value) {
            $parts = explode('-', trim($value));
            if (count($parts) === 2) {
                $ticketRanges[] = (int) $parts[0];
                $ticketRanges[] = (int) $parts[1];
            }
        }
        return $ticketRanges;
    }

    /** GET /api/games/:web_id/tickets/:start/:end */
    public function game_tickets($web_id, $start, $end)
    {
        $available = [];
        for ($i = (int)$start; $i <= (int)$end; $i++) {
            if ($this->getTicketAvailability($i, $web_id)) {
                $available[] = $i;
            }
        }
        $this->json(['tickets' => $available]);
    }

    /** POST /api/games/:web_id/tickets/search  body: { search: "42" } */
    public function ticket_search($web_id)
    {
        $body   = $this->getBody();
        $search = isset($body['search']) ? intval($body['search']) : 0;

        $range = $this->web_model->getrangeInfo($web_id);
        if (!$range) {
            $this->error('Game not found', 404);
        }

        $checkRange = $this->web_model->getRangeAvailability($search, $web_id);
        if (count($checkRange) > 0 && $this->getTicketAvailability($search, $web_id)) {
            $this->json(['available' => true, 'ticket' => $search]);
        } else {
            $this->json(['available' => false, 'ticket' => $search]);
        }
    }

    // ─── Public: FAQ, Pages, Results, Contact ──────────────────────────────────

    /** GET /api/faq */
    public function faq()
    {
        $this->json(['faqs' => $this->web_model->faq()]);
    }

    /** GET /api/page/:type */
    public function page($type)
    {
        $page = $this->web_model->page_detail($type);
        if (!$page) {
            $this->error('Page not found', 404);
        }
        $this->json(['page' => $page]);
    }

    /** GET /api/results */
    public function results()
    {
        $web_id = $this->input->get('web_id', TRUE);
        $date   = $this->input->get('date', TRUE);

        $results = $this->web_model->result_list($web_id, $date);
        $games   = $this->web_model->home_web();
        $this->json(['results' => $results, 'games' => $games]);
    }

    /** POST /api/contact  body: { name, email, mobile, message } */
    public function contact()
    {
        $body = $this->getBody();
        $name    = $this->security->xss_clean($body['name'] ?? '');
        $email   = $this->security->xss_clean($body['email'] ?? '');
        $mobile  = $this->security->xss_clean($body['mobile'] ?? '');
        $message = $this->security->xss_clean($body['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $this->error('Name, email, and message are required');
        }

        $data = [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'message'    => $message,
            'createdDtm' => date('Y-m-d H:i:s'),
        ];
        $this->web_model->insert_date('tbl_contact', $data);
        $this->json([], true, 'Message sent successfully');
    }

    // ─── Auth ───────────────────────────────────────────────────────────────────

    /** GET /api/auth/me */
    public function me()
    {
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $this->json(['isLoggedIn' => false, 'user' => null]);
        }
        $userId   = $this->session->userdata('userId');
        $userInfo = $this->user_model->getUserInfoWithRole($userId);
        $cartCount = count($this->web_model->cart_data($userId));
        $this->json([
            'isLoggedIn' => true,
            'user'       => [
                'userId'    => $this->session->userdata('userId'),
                'name'      => $this->session->userdata('name'),
                'email'     => $this->session->userdata('email'),
                'mobile'    => $this->session->userdata('mobile'),
                'role'      => $this->session->userdata('role'),
                'roleText'  => $this->session->userdata('roleText'),
                'lastLogin' => $this->session->userdata('lastLogin'),
            ],
            'cartCount' => $cartCount,
        ]);
    }

    /** POST /api/auth/login  body: { email, password } */
    public function login()
    {
        $body     = $this->getBody();
        $email    = strtolower($this->security->xss_clean($body['email'] ?? ''));
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->error('Email and password are required');
        }

        $result = $this->login_model->loginMe($email, $password);

        if (empty($result)) {
            $this->error('Email or password is incorrect', 401);
        }

        $lastLogin = $this->login_model->lastLoginInfo($result->userId);

        // Merge guest cart
        if ($result->roleId != 1) {
            $c_userId = $this->session->userdata('custom_userId');
            if ($c_userId) {
                $this->web_model->up_cart($c_userId, $result->userId);
            }
        }

        $sessionArray = [
            'userId'    => $result->userId,
            'role'      => $result->roleId,
            'roleText'  => $result->role,
            'name'      => $result->name,
            'email'     => $result->email,
            'mobile'    => $result->mobile,
            'lastLogin' => $lastLogin ? $lastLogin->createdDtm : null,
            'isLoggedIn' => TRUE,
        ];
        $this->session->set_userdata($sessionArray);

        $logInfo = [
            'userId'      => $result->userId,
            'sessionData' => json_encode($sessionArray),
            'machineIp'   => $_SERVER['REMOTE_ADDR'],
            'userAgent'   => getBrowserAgent(),
            'agentString' => $this->agent->agent_string(),
            'platform'    => $this->agent->platform(),
        ];
        $this->login_model->lastLogin($logInfo);

        $cartCount = count($this->web_model->order_data($result->userId));

        $this->json([
            'user'      => $sessionArray,
            'cartCount' => $cartCount,
            'redirect'  => $cartCount > 0 ? '/confirm-order' : '/dashboard',
        ], true, 'Login successful');
    }

    /** POST /api/auth/logout */
    public function logout()
    {
        $this->session->sess_destroy();
        $this->json([], true, 'Logged out successfully');
    }

    /** POST /api/auth/register  body: { name, email, mobile, password } */
    public function register()
    {
        $body   = $this->getBody();
        $name   = ucwords(strtolower($this->security->xss_clean($body['name'] ?? '')));
        $email  = strtolower($this->security->xss_clean($body['email'] ?? ''));
        $mobile = $this->security->xss_clean($body['mobile'] ?? '');
        $password = $body['password'] ?? '';

        if (empty($name) || empty($email) || empty($mobile) || empty($password)) {
            $this->error('All fields are required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
        }
        if (strlen($mobile) < 10) {
            $this->error('Mobile must be at least 10 digits');
        }

        // Check uniqueness
        if ($this->user_model->checkEmailExists($email)) {
            $this->error('Email already registered');
        }
        if ($this->login_model->checkMobileExist($mobile)) {
            $this->error('Mobile already registered');
        }

        $userData = [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'password'   => getHashedPassword($password),
            'roleId'     => 2,
            'createdDtm' => date('Y-m-d H:i:s'),
        ];
        $userId = $this->user_model->addNewUser($userData);

        if (!$userId) {
            $this->error('Registration failed, please try again');
        }

        $this->json(['userId' => $userId], true, 'Registration successful');
    }

    /** POST /api/auth/forgot-password  body: { email } */
    public function forgot_password()
    {
        $this->load->helper(['email', 'string']);
        $body  = $this->getBody();
        $email = strtolower($this->security->xss_clean($body['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Valid email is required');
        }

        if (!$this->login_model->checkEmailExist($email)) {
            $this->error('No account found with this email');
        }

        $resetData = [
            'email'         => $email,
            'activation_id' => random_string('alnum', 15),
            'createdDtm'    => date('Y-m-d H:i:s'),
            'agent'         => getBrowserAgent(),
            'client_ip'     => $this->input->ip_address(),
        ];
        $this->login_model->resetPasswordUser($resetData);

        $userInfo = $this->login_model->getCustomerInfoByEmail($email);
        $resetLink = base_url() . 'resetPasswordConfirmUser/' . $resetData['activation_id'] . '/' . urlencode($email);

        $emailData = [
            'reset_link' => $resetLink,
            'name'       => $userInfo->name ?? '',
            'email'      => $email,
            'message'    => 'Reset Your Password',
        ];
        resetPasswordEmail($emailData);

        $this->json([], true, 'Password reset link sent to your email');
    }

    // ─── Cart ───────────────────────────────────────────────────────────────────

    /** GET /api/cart */
    public function cart()
    {
        $userId = $this->getCartUserId();
        $items  = $this->web_model->cart_data($userId);
        $total  = array_sum(array_column(array_map(function ($i) { return (array)$i; }, $items), 'total_price'));
        $this->json(['items' => $items, 'total' => $total, 'count' => count($items)]);
    }

    /** POST /api/cart/add  body: { web_id, tickets: [42, 43] } */
    public function cart_add()
    {
        $body    = $this->getBody();
        $web_id  = intval($body['web_id'] ?? 0);
        $tickets = $body['tickets'] ?? [];
        $userId  = $this->getCartUserId();

        if (!$web_id || empty($tickets)) {
            $this->error('web_id and tickets are required');
        }

        $range = $this->web_model->getrangeInfo($web_id);
        if (!$range) {
            $this->error('Game not found', 404);
        }

        $added  = [];
        $errors = [];

        foreach ($tickets as $ticketNo) {
            $ticketNo = intval($ticketNo);
            if (!$this->getTicketAvailability($ticketNo, $web_id)) {
                $errors[] = "Ticket #$ticketNo is not available";
                continue;
            }
            $cartData = [
                'web_id'      => $web_id,
                'user_id'     => $userId,
                'ticket_no'   => $ticketNo,
                'total_price' => $range->price,
            ];
            if (!$this->web_model->checkIfTicketAlreadyPresent($cartData)) {
                $added[] = $cartData;
            }
        }

        if (!empty($added)) {
            $this->web_model->insert_cart($added);
        }

        $items = $this->web_model->cart_data($userId);
        $this->json([
            'added'  => count($added),
            'errors' => $errors,
            'count'  => count($items),
        ], true, count($added) > 0 ? 'Tickets added to cart' : 'No new tickets added');
    }

    /** DELETE /api/cart/:cartItemId */
    public function cart_remove($cartItemId)
    {
        $userId = $this->getCartUserId();
        $this->web_model->delete_cart_item($cartItemId, $userId);
        $items = $this->web_model->cart_data($userId);
        $this->json(['count' => count($items)], true, 'Item removed');
    }

    // ─── Order / Payment ────────────────────────────────────────────────────────

    /** GET /api/order/confirm */
    public function order_confirm()
    {
        $userId = $this->getCartUserId();
        $items  = $this->web_model->order_data($userId);
        $total  = array_sum(array_column(array_map(function ($i) { return (array)$i; }, $items), 'total_price'));
        $this->json([
            'items'     => $items,
            'total'     => $total,
            'isGuest'   => $this->session->userdata('isLoggedIn') != TRUE,
        ]);
    }

    /**
     * POST /api/payment/create
     * Body (guest): { fname, address, mobile, email }
     * Creates Razorpay order and returns options for Razorpay checkout.js
     */
    public function payment_create()
    {
        $body = $this->getBody();

        if ($this->session->userdata('isLoggedIn') != TRUE) {
            // Guest — register/find user first
            $mobile  = strtolower($this->security->xss_clean($body['mobile'] ?? ''));
            $email   = strtolower($this->security->xss_clean($body['email'] ?? ''));
            $name    = ucwords(strtolower($this->security->xss_clean($body['fname'] ?? '')));
            $address = $this->security->xss_clean($body['address'] ?? '');

            if (empty($mobile)) {
                $this->error('Mobile is required for guest checkout');
            }

            $customUserId = $this->getCartUserId();

            if ($this->login_model->checkMobileExist($mobile)) {
                $userInfo = $this->user_model->getUserInfoByMobile($mobile);
                if (!empty($email)) {
                    $this->user_model->editUserByMobile(['email' => $email, 'address' => $address], $mobile);
                }
                $userId   = $userInfo->userId;
                $userName = $userInfo->name;
                $userMobile = $userInfo->mobile;
            } else {
                $userData = [
                    'name'       => $name,
                    'address'    => $address,
                    'mobile'     => $mobile,
                    'email'      => $email,
                    'roleId'     => 2,
                    'createdDtm' => date('Y-m-d H:i:s'),
                ];
                $userId = $this->user_model->addNewUser($userData);
                $userName   = $name;
                $userMobile = $mobile;
            }

            $cart = $this->web_model->order_data($customUserId);
            $razorpayOrder = $this->_createOrder($userId, $cart, $customUserId);
        } else {
            $userId     = $this->session->userdata('userId');
            $userInfo   = $this->user_model->getUserInfo($userId);
            $userName   = $userInfo->name;
            $userMobile = $userInfo->mobile;
            $cart       = $this->web_model->order_data($userId);
            $razorpayOrder = $this->_createOrder($userId, $cart);
        }

        if (!$razorpayOrder) {
            $this->error('Could not create payment order. Cart may be empty.');
        }

        $this->session->set_userdata('payment_order_id', $razorpayOrder['id']);

        $this->json([
            'key_id'       => $this->config->item('key_id'),
            'amount'       => $razorpayOrder['amount'],
            'currency'     => $razorpayOrder['currency'],
            'name'         => 'ITANAGAR CHOICE',
            'description'  => 'Event Tickets',
            'image'        => base_url() . 'public/images/logo.png',
            'order_id'     => $razorpayOrder['id'],
            'callback_url' => base_url() . 'api/payment/confirm',
            'prefill'      => ['name' => $userName, 'contact' => $userMobile],
        ]);
    }

    private function _createOrder($userId, $cart, $customUserId = null)
    {
        if (empty($cart)) return null;

        $tickets     = [];
        $total_price = 0;

        foreach ($cart as $value) {
            $total_price += $value->total_price;
            $tickets[]    = ['ticket_no' => $value->ticket_no, 'web_id' => $value->web_id];
        }

        if ($total_price == 0) return null;

        $transaction_id = $this->getRandomString();
        $api = new RazorpayApi($this->config->item('key_id'), $this->config->item('key_secret'));

        $razorpayOrder = $api->order->create([
            'receipt'  => $transaction_id,
            'amount'   => intval($total_price) * 100,
            'currency' => 'INR',
            'notes'    => ['tickets' => json_encode($tickets)],
        ]);

        if ($razorpayOrder['id']) {
            $orderData = [
                'tickets'                  => json_encode($tickets),
                'user_id'                  => $userId,
                'custom_user_id'           => $customUserId,
                'total_price'              => $total_price,
                'paid_type'                => 'RAZORPAY',
                'paid_status'              => 'CREATED',
                'transaction_id'           => $transaction_id,
                'razorpay_order_id'        => $razorpayOrder['id'],
                'razorpay_order_response'  => json_encode((array)$razorpayOrder),
            ];
            $this->web_model->insert_order($orderData);

            // Mark cart items as in-progress
            foreach ($cart as $value) {
                $cartUpdate = ['paid_status' => 1];
                if ($customUserId != null) {
                    $this->web_model->update_cart_data($customUserId, $value->web_id, $value->ticket_no, $cartUpdate);
                }
                $this->web_model->update_cart_data($userId, $value->web_id, $value->ticket_no, $cartUpdate);
            }
        }

        return $razorpayOrder;
    }

    /**
     * POST /api/payment/confirm
     * Body: { razorpay_payment_id, razorpay_order_id, razorpay_signature }
     */
    public function payment_confirm()
    {
        $this->load->helper('email');
        $body              = $this->getBody();
        $razorpayOrderId   = $this->security->xss_clean($body['razorpay_order_id'] ?? '');
        $razorPayPaymentId = $this->security->xss_clean($body['razorpay_payment_id'] ?? '');
        $razorpaySignature = $this->security->xss_clean($body['razorpay_signature'] ?? '');

        if (empty($razorpayOrderId) || empty($razorPayPaymentId) || empty($razorpaySignature)) {
            $this->error('Invalid payment response');
        }

        $orderDetails = $this->web_model->get_order_by_orderId($razorpayOrderId);
        if (!$orderDetails) {
            $this->error('Order not found', 404);
        }

        $api = new RazorpayApi($this->config->item('key_id'), $this->config->item('key_secret'));

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $this->session->userdata('payment_order_id') ?: $razorpayOrderId,
                'razorpay_payment_id' => $razorPayPaymentId,
                'razorpay_signature'  => $razorpaySignature,
            ]);

            $this->web_model->update_order_by_orderId($razorpayOrderId, [
                'order_status'     => 1,
                'payment_response' => json_encode($body),
            ]);

            $tickets       = json_decode($orderDetails->tickets);
            $ticketDetails = [];
            foreach ($tickets as $t) {
                $webInfo = $this->web_model->getallWebInfo('tbl_webs', $t->web_id);
                $range   = $this->web_model->getrangeInfo($t->web_id);
                $ticketDetails[] = ['webInfo' => $webInfo, 'range' => $range, 'ticketNo' => $t->ticket_no];
            }

            $userInfo = $this->user_model->getUserInfo($orderDetails->user_id);
            if ($userInfo) {
                $emailData = ['userInfo' => $userInfo, 'ticket_details' => $ticketDetails, 'orderDetails' => $orderDetails];
                $email_body = $this->load->view('frontend/email_ticket', $emailData, TRUE);
                $this->load->helper('email');
                sendmail($userInfo->email, APP_NAME . ' - Your Ticket Confirmation', $email_body, '');
            }

            $this->json([
                'status'         => 'success',
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorPayPaymentId,
                'ticket_details' => $ticketDetails,
            ], true, 'Payment successful!');

        } catch (PaymentError $e) {
            $this->web_model->update_order_by_orderId($razorpayOrderId, [
                'order_status'     => 2,
                'payment_response' => json_encode($body),
            ]);
            $this->error('Payment verification failed: ' . $e->getMessage(), 400);
        }
    }

    /** POST /api/payment/cancel  body: { order_id, payment_id, reason } */
    public function payment_cancel()
    {
        $body    = $this->getBody();
        $orderId = $this->security->xss_clean($body['order_id'] ?? '');
        if ($orderId) {
            $this->web_model->update_order_by_orderId($orderId, ['order_status' => 2]);
        }
        $this->json([], true, 'Payment cancelled');
    }

    // ─── Account (authenticated) ────────────────────────────────────────────────

    /** GET /api/account/profile */
    public function profile()
    {
        $this->requireAuth();
        $userId   = $this->getUserId();
        $userInfo = $this->user_model->getUserInfoWithRole($userId);
        $this->json(['user' => $userInfo]);
    }

    /** POST /api/account/profile  body: { fname, email, mobile, paypal, bank } */
    public function profile_update()
    {
        $this->requireAuth();
        $body   = $this->getBody();
        $userId = $this->getUserId();

        $name   = ucwords(strtolower($this->security->xss_clean($body['fname'] ?? '')));
        $email  = strtolower($this->security->xss_clean($body['email'] ?? ''));
        $mobile = $this->security->xss_clean($body['mobile'] ?? '');
        $paypal = strtolower($this->security->xss_clean($body['paypal'] ?? ''));
        $bank   = $this->security->xss_clean($body['bank'] ?? '');

        if (empty($name) || empty($email) || empty($mobile)) {
            $this->error('Name, email and mobile are required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address');
        }

        // Email uniqueness (exclude current user)
        $existing = $this->user_model->checkEmailExists($email, $userId);
        if (!empty($existing)) {
            $this->error('Email already taken');
        }

        $userInfo = [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'paypal'     => $paypal,
            'bank'       => $bank,
            'updatedBy'  => $userId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ];

        $this->user_model->editUser($userInfo, $userId);
        $this->session->set_userdata('name', $name);
        $this->json([], true, 'Profile updated successfully');
    }

    /** POST /api/account/password  body: { oldPassword, newPassword } */
    public function password_update()
    {
        $this->requireAuth();
        $body        = $this->getBody();
        $userId      = $this->getUserId();
        $oldPassword = $body['oldPassword'] ?? '';
        $newPassword = $body['newPassword'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            $this->error('Old and new passwords are required');
        }
        if (strlen($newPassword) < 6) {
            $this->error('New password must be at least 6 characters');
        }

        $match = $this->user_model->matchOldPassword($userId, $oldPassword);
        if (empty($match)) {
            $this->error('Old password is incorrect', 401);
        }

        $this->user_model->changePassword($userId, [
            'password'   => getHashedPassword($newPassword),
            'updatedBy'  => $userId,
            'updatedDtm' => date('Y-m-d H:i:s'),
        ]);

        $this->json([], true, 'Password updated successfully');
    }

    /** GET /api/account/wallet */
    public function wallet()
    {
        $this->requireAuth();
        $userId  = $this->getUserId();
        $balance = $this->web_model->wallet_user($userId);
        $history = $this->web_model->wallet_history_user($userId);
        $this->json([
            'balance' => $balance ? $balance->money : 0,
            'history' => $history,
        ]);
    }

    /** POST /api/account/wallet/topup  body: { money, transaction_id, paymet_type } */
    public function wallet_topup()
    {
        $this->requireAuth();
        $body   = $this->getBody();
        $userId = $this->getUserId();
        $money  = floatval($body['money'] ?? 0);

        if ($money <= 0) {
            $this->error('Amount must be greater than zero');
        }

        $historyData = [
            'user_id'        => $userId,
            'money'          => $money,
            'trancaction_id' => $this->security->xss_clean($body['transaction_id'] ?? ''),
            'type'           => 'Credit',
            'p_type'         => $this->security->xss_clean($body['paymet_type'] ?? ''),
        ];

        $wallet = $this->web_model->wallet_user($userId);
        if (!$wallet) {
            $this->web_model->insert_date('tbl_wallet', ['user_id' => $userId, 'money' => $money]);
        } else {
            $this->web_model->editWeb_all('tbl_wallet', ['user_id' => $userId, 'money' => $wallet->money + $money], $wallet->id);
        }
        $this->web_model->insert_date('tbl_wallet_history', $historyData);

        $this->json(['balance' => ($wallet ? $wallet->money : 0) + $money], true, "$money added to wallet");
    }

    /** GET /api/account/orders */
    public function orders()
    {
        $this->requireAuth();
        $userId = $this->getUserId();
        $this->json(['orders' => $this->web_model->order_history($userId)]);
    }

    /** GET /api/account/refunds */
    public function refunds()
    {
        $this->requireAuth();
        $userId  = $this->getUserId();
        $balance = $this->web_model->wallet_user($userId);
        $history = $this->web_model->refund_history_user($userId);
        $this->json([
            'balance' => $balance ? $balance->money : 0,
            'history' => $history,
        ]);
    }

    /** POST /api/account/refunds  body: { add_pay, reason } */
    public function refund_request()
    {
        $this->requireAuth();
        $body   = $this->getBody();
        $userId = $this->getUserId();
        $money  = floatval($body['add_pay'] ?? 0);
        $reason = $this->security->xss_clean($body['reason'] ?? '');

        if ($money <= 0 || empty($reason)) {
            $this->error('Amount and reason are required');
        }

        $this->web_model->insert_date('tbl_refund', [
            'user_id' => $userId,
            'money'   => $money,
            'reason'  => $reason,
        ]);
        $this->json([], true, "Refund request of ₹$money submitted");
    }

    /** GET /api/account/withdrawals */
    public function withdrawals()
    {
        $this->requireAuth();
        $userId  = $this->getUserId();
        $balance = $this->web_model->wallet_user($userId);
        $history = $this->web_model->withdrawl_history_user($userId);
        $this->json([
            'balance' => $balance ? $balance->money : 0,
            'history' => $history,
        ]);
    }

    /** POST /api/account/withdrawals  body: { add_pay, type, pay_email, bank_detail } */
    public function withdrawal_request()
    {
        $this->requireAuth();
        $body   = $this->getBody();
        $userId = $this->getUserId();
        $money  = floatval($body['add_pay'] ?? 0);
        $type   = intval($body['type'] ?? 0);

        if ($money <= 0) {
            $this->error('Amount must be greater than zero');
        }

        $wallet = $this->web_model->wallet_user($userId);
        if (!$wallet || $wallet->money < $money) {
            $this->error('Insufficient wallet balance');
        }

        $pay = $type == 1 ? ($body['bank_detail'] ?? '') : ($body['pay_email'] ?? '');
        if (empty($pay)) {
            $this->error('Payment details are required');
        }

        $userUpdate = $type == 1 ? ['bank' => $pay] : ['paypal' => $pay];
        $this->user_model->editUser($userUpdate, $userId);

        $this->web_model->insert_date('tbl_withdrawl', [
            'user_id'      => $userId,
            'type'         => $type,
            'paypal_email' => $pay,
            'money'        => $money,
        ]);

        $this->json(['balance' => $wallet->money], true, "Withdrawal request of ₹$money submitted");
    }

    /** GET /api/account/transfers */
    public function transfers()
    {
        $this->requireAuth();
        $userId  = $this->getUserId();
        $balance = $this->web_model->wallet_user($userId);
        $history = $this->web_model->transfer_history_user($userId);
        $this->json([
            'balance' => $balance ? $balance->money : 0,
            'history' => $history,
        ]);
    }

    /** POST /api/account/transfers  body: { add_pay, pay_email } */
    public function transfer_request()
    {
        $this->requireAuth();
        $body      = $this->getBody();
        $userId    = $this->getUserId();
        $money     = floatval($body['add_pay'] ?? 0);
        $payEmail  = $this->security->xss_clean($body['pay_email'] ?? '');

        if ($money <= 0 || empty($payEmail)) {
            $this->error('Amount and recipient email are required');
        }

        $recipient = $this->user_model->checkuserEmailExists($payEmail);
        if (!$recipient) {
            $this->error('Recipient email not found');
        }
        if ($recipient->userId == $userId) {
            $this->error('Cannot transfer to your own wallet');
        }

        $wallet = $this->web_model->wallet_user($userId);
        if (!$wallet || $wallet->money < $money) {
            $this->error('Insufficient wallet balance');
        }

        // Debit sender
        $this->web_model->editWeb_all('tbl_wallet', ['user_id' => $userId, 'money' => $wallet->money - $money], $wallet->id);
        $this->web_model->insert_date('tbl_wallet_history', ['user_id' => $userId, 'money' => $money, 'trancaction_id' => 'Transfer', 'type' => 'Debit', 'p_type' => 'Transfer']);

        // Credit recipient
        $recipientWallet = $this->web_model->wallet_user($recipient->userId);
        if (!$recipientWallet) {
            $this->web_model->insert_date('tbl_wallet', ['user_id' => $recipient->userId, 'money' => $money]);
        } else {
            $this->web_model->editWeb_all('tbl_wallet', ['user_id' => $recipient->userId, 'money' => $recipientWallet->money + $money], $recipientWallet->id);
        }
        $this->web_model->insert_date('tbl_wallet_history', ['user_id' => $recipient->userId, 'money' => $money, 'trancaction_id' => 'Transfer', 'type' => 'Credit', 'p_type' => 'Transfer']);

        // Log transfer
        $this->web_model->insert_date('tbl_transfer', ['user_id' => $userId, 'reciver_id' => $recipient->userId, 'paypal_email' => $payEmail, 'money' => $money]);

        $this->json(['balance' => $wallet->money - $money], true, "₹$money transferred successfully");
    }

    /** GET /api/account/winners */
    public function winners()
    {
        $this->requireAuth();
        $userId  = $this->getUserId();
        $history = $this->web_model->winner_history($userId);
        $amount  = $this->web_model->winner_amountf($userId);
        $this->json(['history' => $history, 'total_amount' => $amount]);
    }
}
