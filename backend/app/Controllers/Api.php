<?php

namespace App\Controllers;

require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;
use App\Models\WebModel;
use App\Models\UserModel;
use App\Models\LoginModel;
use App\Models\GameModel;
use App\Models\CartOrderModel;
use App\Models\WalletModel;
use App\Models\WinnerModel;
use App\Models\ContentModel;

/**
 * Api Controller — JSON API for the React frontend.
 * All responses: { "status": bool, "data": mixed, "message": string }
 */
class Api extends BaseController
{
    protected WebModel       $webModel;
    protected UserModel      $userModel;
    protected LoginModel     $loginModel;
    protected GameModel      $gameModel;
    protected CartOrderModel $cartOrderModel;
    protected WalletModel    $walletModel;
    protected WinnerModel    $winnerModel;
    protected ContentModel   $contentModel;

    protected $helpers = ['url', 'cias_helper', 'email_helper'];

    /**
     * Bootstraps the controller, instantiates models, and applies CORS headers.
     * Called automatically by CodeIgniter before any method is invoked.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->webModel       = new WebModel();
        $this->userModel      = new UserModel();
        $this->loginModel     = new LoginModel();
        $this->gameModel      = new GameModel();
        $this->cartOrderModel = new CartOrderModel();
        $this->walletModel    = new WalletModel();
        $this->winnerModel    = new WinnerModel();
        $this->contentModel   = new ContentModel();
        $this->_cors();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Sets CORS response headers for the React frontend origins.
     * Immediately responds with 200 and exits on OPTIONS preflight requests.
     */
    private function _cors(): void
    {
        $origin  = $this->request->getHeaderLine('Origin');
        $allowed = ['http://localhost:5173', 'http://localhost:5174', 'https://itanagarchoice.com'];
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

    /**
     * Returns a standardised JSON response: { status, data, message }.
     *
     * @param array  $data    Payload to include under the "data" key.
     * @param bool   $status  Operation success flag.
     * @param string $message Human-readable result message.
     * @param int    $code    HTTP status code (default 200).
     */
    private function json(array $data = [], bool $status = true, string $message = '', int $code = 200)
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON(['status' => $status, 'data' => $data, 'message' => $message]);
    }

    /**
     * Returns a standardised JSON error response with status=false.
     *
     * @param string $message Error description sent to the client.
     * @param int    $code    HTTP status code (default 400).
     * @param array  $data    Optional extra payload.
     */
    private function error(string $message = 'An error occurred', int $code = 400, array $data = [])
    {
        return $this->json($data, false, $message, $code);
    }

    /**
     * Decodes the raw JSON request body and returns it as an associative array.
     * Returns an empty array when the body is absent or not valid JSON.
     */
    private function getBody(): array
    {
        $raw = $this->request->getBody();
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    /**
     * Returns the authenticated user's ID from the session, or null for guests.
     */
    private function getUserId(): ?int
    {
        return session()->get('isLoggedIn') === true ? (int) session()->get('userId') : null;
    }

    /**
     * Returns the user ID to use as the cart owner.
     * For logged-in users this is their real userId; for guests a random
     * integer is generated and persisted in the session as custom_userId.
     */
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

    /**
     * Guards an endpoint against unauthenticated access.
     * Returns a 401 JSON error response when the user is not logged in,
     * or null when the session is valid (caller must check for non-null).
     */
    private function requireAuth()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->error('Unauthenticated', 401);
        }
    }

    /**
     * Generates a cryptographically random alphanumeric string.
     * Used for Razorpay receipt IDs and transaction identifiers.
     *
     * @param int $length Number of characters to generate (default 16).
     */
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

    /**
     * Returns true when the given ticket number is available (not in cart or sold) for a game.
     *
     * @param int $ticket  Ticket number to check.
     * @param int $web_id  Game (tbl_webs) ID.
     */
    private function getTicketAvailability(int $ticket, int $web_id): bool
    {
        return count($this->cartOrderModel->get_ticket_availability($ticket, $web_id)) === 0;
    }

    /**
     * Parses the comma-separated range string from a tbl_ranges row
     * and returns a flat array of range boundary integers [start, end, ...].
     *
     * @param object|null $range A range row from tbl_ranges (expects rangeStart property).
     * @return int[] Flat list of start/end boundary values.
     */
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

    /**
     * GET /api/home
     * Returns homepage data: featured games, one FAQ, recent results, and platform stats.
     */
    public function home()
    {
        return $this->json([
            'games'   => $this->gameModel->home_web(6),
            'faq'     => $this->contentModel->faq(1),
            'results' => $this->cartOrderModel->result_list(null, null, 5),
            'stats'   => [
                'games' => count($this->gameModel->home_web()),
                'users' => $this->userModel->userListingCount(''),
            ],
        ]);
    }

    /**
     * GET /api/games
     * Returns all active lottery games.
     */
    public function games()
    {
        return $this->json(['games' => $this->gameModel->home_web()]);
    }

    /**
     * GET /api/game/{id}
     * Returns a single game's details along with its ticket range configuration.
     *
     * @param int $id Game ID (tbl_webs.id).
     */
    public function game_detail(int $id)
    {
        $website = $this->gameModel->getallWebInfo('tbl_webs', $id);
        if (!$website) {
            return $this->error('Game not found', 404);
        }
        $range = $this->gameModel->getrangeInfo($id);
        return $this->json([
            'website'     => $website,
            'range'       => $range,
            'ticketRange' => $this->_getFirstAvailableTickets($range),
        ]);
    }

    /**
     * GET /api/game/{web_id}/tickets/{start}/{end}
     * Returns all available (unsold, not in cart) ticket numbers in the given range.
     *
     * @param int $web_id Game ID.
     * @param int $start  First ticket number to check (inclusive).
     * @param int $end    Last ticket number to check (inclusive).
     */
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

    /**
     * POST /api/game/{web_id}/ticket-search
     * Checks whether a specific ticket number is within the game's valid range
     * and is still available for purchase.
     * Body: { search: int }
     *
     * @param int $web_id Game ID.
     */
    public function ticket_search(int $web_id)
    {
        $body   = $this->getBody();
        $search = isset($body['search']) ? (int) $body['search'] : 0;

        $range = $this->gameModel->getrangeInfo($web_id);
        if (!$range) {
            return $this->error('Game not found', 404);
        }
        $checkRange = $this->gameModel->getRangeAvailability($search, $web_id);
        $available  = count($checkRange) > 0 && $this->getTicketAvailability($search, $web_id);
        return $this->json(['available' => $available, 'ticket' => $search]);
    }

    // ── Public: FAQ, Pages, Results, Contact ─────────────────────────────────

    /**
     * GET /api/faq
     * Returns all FAQ entries ordered by most recent.
     */
    public function faq()
    {
        return $this->json(['faqs' => $this->contentModel->faq()]);
    }

    /**
     * GET /api/page/{type}
     * Returns a CMS page (tbl_pages) by its type slug, e.g. "about", "terms".
     *
     * @param string $type Page type slug.
     */
    public function page(string $type)
    {
        $page = $this->contentModel->page_detail($type);
        if (!$page) {
            return $this->error('Page not found', 404);
        }
        return $this->json(['page' => $page]);
    }

    /**
     * GET /api/results?web_id=&date=
     * Returns draw results, optionally filtered by game ID and/or date.
     * Also returns the full games list for filter dropdowns.
     */
    public function results()
    {
        $webId   = $this->request->getGet('web_id');
        $date    = $this->request->getGet('date');
        $results = $this->cartOrderModel->result_list($webId ? (int)$webId : null, $date ?: null);
        $games   = $this->gameModel->home_web();
        return $this->json(['results' => $results, 'games' => $games]);
    }

    /**
     * POST /api/contact
     * Saves a contact form submission to tbl_contact.
     * Body: { name, email, mobile?, message }
     */
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

        $this->contentModel->insert_date('tbl_contact', [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'message'    => $message,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Message sent successfully');
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/me
     * Returns the current user's session data and cart item count.
     * Returns isLoggedIn=false for unauthenticated requests (no error).
     */
    public function me()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->json(['isLoggedIn' => false, 'user' => null]);
        }
        $userId    = (int) session()->get('userId');
        $cartCount = count($this->cartOrderModel->cart_data($userId));
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

    /**
     * POST /api/login
     * Authenticates a user by email and password, starts a session,
     * and merges any guest cart items into the user's cart.
     * Body: { email, password }
     */
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

    /**
     * POST /api/logout
     * Destroys the current session and logs the user out.
     */
    public function logout()
    {
        session()->destroy();
        return $this->json([], true, 'Logged out');
    }

    /**
     * POST /api/register
     * Creates a new user account with role 2 (regular user).
     * Body: { name, email, password, mobile? }
     */
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

    /**
     * POST /api/forgot-password
     * Generates a password reset token, persists it in tbl_reset_password,
     * and sends the reset link via email.
     * Body: { email }
     */
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

    /**
     * POST /api/reset-password
     * Validates the activation code and updates the user's password.
     * Body: { email, activation_code, password, password_confirmation }
     */
    public function reset_password()
    {
        $body            = $this->getBody();
        $email           = strtolower(trim($body['email'] ?? ''));
        $activationCode  = trim($body['activation_code'] ?? '');
        $password        = $body['password'] ?? '';
        $confirmPassword = $body['password_confirmation'] ?? '';

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

    // ── Cart ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/cart
     * Returns the current user's (or guest's) cart items and the total price.
     */
    public function cart()
    {
        $userId = $this->getCartUserId();
        $cart   = $this->cartOrderModel->cart_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total]);
    }

    /**
     * POST /api/cart/add
     * Adds one or more ticket numbers to the cart for a given game.
     * Skips tickets that are already sold or in another cart.
     * Body: { web_id: int, tickets: int[] }
     */
    public function cart_add()
    {
        $body   = $this->getBody();
        $webId  = (int) ($body['web_id']  ?? 0);
        $tickets = $body['tickets'] ?? [];
        $userId = $this->getCartUserId();

        if (empty($webId) || empty($tickets)) {
            return $this->error('web_id and tickets are required');
        }

        $range  = $this->gameModel->getrangeInfo($webId);
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
            if (!$this->cartOrderModel->checkIfTicketAlreadyPresent($cartRow)) {
                $toAdd[] = $cartRow;
            }
        }

        if (!empty($toAdd)) {
            $this->cartOrderModel->insert_cart($toAdd);
        }

        return $this->json(['added' => count($toAdd), 'errors' => $errors], true, '');
    }

    /**
     * DELETE /api/cart/{cartId}
     * Removes a single cart row belonging to the current user/guest.
     *
     * @param int $cartId Primary key of the tbl_cart row to remove.
     */
    public function cart_remove(int $cartId)
    {
        $userId = $this->getCartUserId();
        $this->cartOrderModel->delete_cart_item($cartId, $userId);
        return $this->json([], true, 'Removed');
    }

    // ── Payment ───────────────────────────────────────────────────────────────

    /**
     * POST /api/payment/create
     * Creates a Razorpay order for the current cart.
     * Handles both logged-in users and guests (auto-registers guests by mobile).
     * Marks cart rows as paid_status=1 and stores the order in tbl_order.
     * Returns Razorpay order details needed by the frontend payment widget.
     */
    public function payment_create()
    {
        $body         = $this->getBody();
        $customUserId = null;

        if (session()->get('isLoggedIn') === true) {
            // ── Logged-in user ────────────────────────────────────────────────
            $userId   = (int) session()->get('userId');
            $cart     = $this->cartOrderModel->order_data($userId);
            $userInfo = $this->userModel->getUserInfo($userId);
        } else {
            // ── Guest / unregistered user ─────────────────────────────────────
            // Expect: mobile (required), name, email, address in request body
            $mobile  = strtolower(esc($body['mobile']  ?? ''));
            $email   = strtolower(esc($body['email']   ?? ''));
            $name    = ucwords(strtolower(esc($body['name'] ?? $body['fname'] ?? '')));
            $address = strtolower(esc($body['address'] ?? ''));

            if (empty($mobile)) {
                return $this->error('Mobile number is required for guest checkout');
            }

            if ($this->loginModel->checkMobileExist($mobile)) {
                // User already registered — fetch their account
                $userInfo = $this->userModel->getUserInfoByMobile($mobile);
                if (!empty($email)) {
                    $this->userModel->editUserByMobile(['email' => $email, 'address' => $address], $mobile);
                }
            } else {
                // New guest — register them
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

            // Cart was built under the guest session key
            $customUserId = session()->get('custom_userId') ? (int) session()->get('custom_userId') : null;
            $cart         = $this->cartOrderModel->order_data($customUserId ?? $userId);
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
        $keyId         = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret     = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
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

        $this->cartOrderModel->insert_order([
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

        // Mark cart rows as paid for both guest and registered user ids
        foreach ($cart as $item) {
            if ($customUserId !== null) {
                $this->cartOrderModel->update_cart_data($customUserId, $item->web_id, $item->ticket_no, ['paid_status' => 1]);
            }
            $this->cartOrderModel->update_cart_data($userId, $item->web_id, $item->ticket_no, ['paid_status' => 1]);
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

    /**
     * POST /api/payment/confirm
     * Verifies the Razorpay payment signature and marks the order as PAID.
     * Also handles wallet top-up confirmations when type=="wallet".
     * Sends the ticket confirmation email on success.
     * Body: { razorpay_order_id, razorpay_payment_id, razorpay_signature, type? }
     */
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

        $keyId     = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
        $api       = new RazorpayApi($keyId, $keySecret);

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPayId,
                'razorpay_signature'  => $razorpaySignature,
            ]);

            if ($type === 'wallet') {
                $userId = (int) session()->get('userId');
                $amount = (float) (session()->get('wallet_amount') ?? 0);
                $wallet = $this->walletModel->wallet($userId);
                if (!$wallet) {
                    $this->walletModel->insert_date('tbl_wallet', ['user_id' => $userId, 'money' => $amount]);
                } else {
                    $this->walletModel->editWeb_all('tbl_wallet', ['money' => $wallet->money + $amount], $wallet->id);
                }
                $this->walletModel->insert_date('tbl_wallet_history', [
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

            $this->cartOrderModel->update_order_by_orderId($razorpayOrderId, [
                'paid_status'      => 'PAID',
                'order_status'     => 1,
                'payment_response' => json_encode($body),
            ]);

            $orderDetails = $this->cartOrderModel->get_order_by_orderId($razorpayOrderId);
            $userInfo     = $this->userModel->getUserInfo($orderDetails->user_id);
            $tickets      = json_decode($orderDetails->tickets, true);
            $ticketDetails = [];
            foreach ($tickets as $t) {
                $ticketDetails[] = [
                    'webInfo'  => $this->gameModel->getallWebInfo('tbl_webs', $t['web_id']),
                    'range'    => $this->gameModel->getrangeInfo($t['web_id']),
                    'ticketNo' => $t['ticket_no'],
                ];
            }

            $emailBody = view('emails/email_ticket', [
                'ticket_details' => $ticketDetails,
                'status'         => 'Payment Successful',
                'details'        => ['razorpay_order_id' => $razorpayOrderId, 'razorpay_payment_id' => $razorpayPayId],
            ]);
            sendmail($userInfo->email, 'Order: ' . $razorpayOrderId, $emailBody);

            return $this->json(['status' => 'PAID'], true, 'Payment verified');
        } catch (PaymentError $e) {
            $this->cartOrderModel->update_order_by_orderId($razorpayOrderId, [
                'order_status'     => 2,
                'payment_response' => json_encode($body),
            ]);
            return $this->error('Signature verification failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * POST /api/payment/cancel
     * Marks a Razorpay order as cancelled (order_status=2).
     * Body: { order_id: string }
     */
    public function payment_cancel()
    {
        $body    = $this->getBody();
        $orderId = esc($body['order_id'] ?? '');
        if (empty($orderId)) {
            return $this->error('order_id required');
        }
        $this->cartOrderModel->update_order_by_orderId($orderId, [
            'order_status'     => 2,
            'payment_response' => json_encode($body),
        ]);
        return $this->json([], true, 'Order cancelled');
    }

    /**
     * GET /api/order/confirm
     * Returns the authenticated user's current cart for pre-checkout review.
     * Requires login (401 if unauthenticated).
     */
    public function order_confirm()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) {
            return $auth;
        }
        $userId = (int) session()->get('userId');
        $cart   = $this->cartOrderModel->order_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total, 'isGuest' => false]);
    }

    // ── Account ───────────────────────────────────────────────────────────────

    /**
     * GET /api/account/profile
     * Returns the authenticated user's profile data from tbl_users.
     */
    public function account_profile()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        $user   = $this->userModel->getUserInfo($userId);
        return $this->json($user ? (array) $user : []);
    }

    /**
     * POST /api/account/profile
     * Updates the authenticated user's name and mobile number.
     * Also syncs the session values immediately.
     * Body: { name, mobile? }
     */
    public function account_profile_update()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
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

    /**
     * POST /api/account/password
     * Changes the authenticated user's password after verifying the old one.
     * Body: { old_password, new_password }
     */
    public function account_password()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
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

    /**
     * GET /api/account/wallet
     * Returns the authenticated user's wallet balance and full transaction history.
     */
    public function account_wallet()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId  = (int) session()->get('userId');
        $wallet  = $this->walletModel->wallet($userId);
        $history = $this->walletModel->wallet_history($userId);
        return $this->json([
            'balance' => $wallet ? (float) $wallet->money : 0.0,
            'history' => $history,
        ]);
    }

    /**
     * POST /api/account/wallet/topup
     * Creates a Razorpay order to add funds to the user's wallet.
     * The actual credit is applied in payment_confirm() when type=="wallet".
     * Body: { amount: float }
     */
    public function account_wallet_topup()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId   = (int) session()->get('userId');
        $userInfo = $this->userModel->getUserInfo($userId);
        $body     = $this->getBody();
        $amount   = (float) ($body['amount'] ?? 0);
        if ($amount < 1) {
            return $this->error('Invalid amount');
        }
        $keyId     = env('RAZORPAY_KEY_ID',    'rzp_live_mIBUSRL7Pn4XUj');
        $keySecret = env('RAZORPAY_KEY_SECRET', 'pRTdkyxSIxXrvQuYcDu9GL4f');
        $api       = new RazorpayApi($keyId, $keySecret);

        $razorpayOrder = $api->order->create([
            'receipt'  => $this->getRandomString(),
            'amount'   => (int) ($amount * 100),
            'currency' => 'INR',
            'notes'    => ['type' => 'wallet', 'user_id' => $userId],
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

    /**
     * GET /api/account/orders
     * Returns all past orders (tbl_order) for the authenticated user.
     */
    public function account_orders()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        return $this->json(['orders' => $this->cartOrderModel->order_history($userId)]);
    }

    /**
     * GET /api/account/refunds
     * Returns the authenticated user's refund request history from tbl_refund.
     */
    public function account_refunds()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->refund_history($userId));
    }

    /**
     * POST /api/account/refunds
     * Submits a new refund request for a given order.
     * Body: { order_id: string, reason: string }
     */
    public function account_refund_create()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId  = (int) session()->get('userId');
        $body    = $this->getBody();
        $orderId = esc($body['order_id'] ?? '');
        $reason  = esc($body['reason']   ?? '');
        if (empty($orderId) || empty($reason)) {
            return $this->error('Order ID and reason are required');
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

    /**
     * GET /api/account/withdrawals
     * Returns the authenticated user's withdrawal request history from tbl_withdrawl.
     */
    public function account_withdrawals()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->withdrawl_history($userId));
    }

    /**
     * POST /api/account/withdrawals
     * Submits a withdrawal request (bank transfer) for a specified amount.
     * Body: { amount, account_number, ifsc, account_name }
     */
    public function account_withdrawal_create()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId        = (int) session()->get('userId');
        $body          = $this->getBody();
        $amount        = (float) ($body['amount']         ?? 0);
        $accountNumber = esc($body['account_number']   ?? '');
        $ifsc          = esc($body['ifsc']             ?? '');
        $accountName   = esc($body['account_name']     ?? '');
        if ($amount < 1 || empty($accountNumber) || empty($ifsc) || empty($accountName)) {
            return $this->error('All fields are required');
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

    /**
     * GET /api/account/transfers
     * Returns the authenticated user's wallet-to-wallet transfer history.
     */
    public function account_transfers()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        return $this->json($this->walletModel->transfer_history($userId));
    }

    /**
     * POST /api/account/transfers
     * Transfers funds from the authenticated user's wallet to another user.
     * Accepts either a userId (numeric) or an email address as the recipient identifier.
     * Body: { to_user: string|int, amount: float, note?: string }
     */
    public function account_transfer_create()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId    = (int) session()->get('userId');
        $body      = $this->getBody();
        $toUser    = trim($body['to_user'] ?? '');
        $amount    = (float) ($body['amount'] ?? 0);
        $note      = esc($body['note'] ?? '');
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
        $this->walletModel->editWeb_all('tbl_wallet', ['money' => $wallet->money - $amount], $wallet->id);
        $this->walletModel->insert_date('tbl_wallet_history', [
            'user_id' => $userId, 'money' => $amount, 'type' => 'Debit', 'p_type' => 'Transfer',
        ]);
        $recipientWallet = $this->walletModel->wallet($recipient->userId);
        if (!$recipientWallet) {
            $this->walletModel->insert_date('tbl_wallet', ['user_id' => $recipient->userId, 'money' => $amount]);
        } else {
            $this->walletModel->editWeb_all('tbl_wallet', ['money' => $recipientWallet->money + $amount], $recipientWallet->id);
        }
        $this->walletModel->insert_date('tbl_wallet_history', [
            'user_id' => $recipient->userId, 'money' => $amount, 'type' => 'Credit', 'p_type' => 'Transfer',
        ]);
        $this->walletModel->insert_date('tbl_transfer', [
            'user_id'    => $userId,
            'to_user_id' => $recipient->userId,
            'amount'     => $amount,
            'note'       => $note,
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);
        return $this->json([], true, 'Transfer successful');
    }

    /**
     * GET /api/account/winners
     * Returns the authenticated user's winning history from tbl_winner_history.
     */
    public function account_winners()
    {
        $auth = $this->requireAuth();
        if ($auth !== null) return $auth;
        $userId = (int) session()->get('userId');
        return $this->json($this->winnerModel->winner_history($userId));
    }
}
