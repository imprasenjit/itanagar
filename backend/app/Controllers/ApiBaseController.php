<?php

namespace App\Controllers;

use App\Models\GameModel;
use App\Models\CartOrderModel;
use App\Models\WalletModel;
use App\Models\WinnerModel;
use App\Models\ContentModel;
use App\Models\UserModel;
use App\Models\LoginModel;

/**
 * ApiBaseController — shared infrastructure for all Api sub-controllers.
 * Provides: json(), error(), getBody(), requireAuth(), getCartUserId(),
 *           getTicketAvailability(), _getFirstAvailableTickets(), getRandomString().
 */
class ApiBaseController extends BaseController
{
    protected GameModel      $gameModel;
    protected CartOrderModel $cartOrderModel;
    protected WalletModel    $walletModel;
    protected WinnerModel    $winnerModel;
    protected ContentModel   $contentModel;
    protected UserModel      $userModel;
    protected LoginModel     $loginModel;

    protected $helpers = ['url', 'cias_helper', 'email_helper'];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->gameModel      = new GameModel();
        $this->cartOrderModel = new CartOrderModel();
        $this->walletModel    = new WalletModel();
        $this->winnerModel    = new WinnerModel();
        $this->contentModel   = new ContentModel();
        $this->userModel      = new UserModel();
        $this->loginModel     = new LoginModel();
        $this->_cors();
    }

    // ── CORS ──────────────────────────────────────────────────────────────────

    protected function _cors(): void
    {
        $origin  = $this->request->getHeaderLine('Origin');
        $allowed = ['http://localhost:5173', 'http://localhost:5174', 'https://itanagarchoice.com', 'https://www.itanagarchoice.com'];
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

    /** Handles the OPTIONS preflight catch-all route. */
    public function options()
    {
        return $this->response->setStatusCode(200);
    }

    // ── Response helpers ──────────────────────────────────────────────────────

    protected function json(array $data = [], bool $status = true, string $message = '', int $code = 200)
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setJSON(['status' => $status, 'data' => $data, 'message' => $message]);
    }

    protected function error(string $message = 'An error occurred', int $code = 400, array $data = [])
    {
        return $this->json($data, false, $message, $code);
    }

    // ── Request helpers ───────────────────────────────────────────────────────

    protected function getBody(): array
    {
        $raw = $this->request->getBody();
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    // ── Auth helpers ──────────────────────────────────────────────────────────

    /**
     * Returns an error response (401) if unauthenticated, null if OK.
     * Usage: if ($r = $this->requireAuth()) return $r;
     */
    protected function requireAuth()
    {
        if (session()->get('isLoggedIn') !== true) {
            return $this->error('Unauthenticated', 401);
        }
        return null;
    }

    protected function getCartUserId(): int
    {
        if (session()->get('isLoggedIn') === true) {
            return (int) session()->get('userId');
        }
        if (!session()->has('custom_userId')) {
            session()->set('custom_userId', random_int(100000000, 999999999));
        }
        return (int) session()->get('custom_userId');
    }

    // ── Ticket helpers ────────────────────────────────────────────────────────

    protected function getTicketAvailability(int $ticket, int $web_id): bool
    {
        return count($this->cartOrderModel->get_ticket_availability($ticket, $web_id)) === 0;
    }

    protected function _getFirstAvailableTickets($range): array
    {
        if (!$range || empty($range->rangeStart)) {
            return [];
        }
        $ticketRanges = [];
        foreach (parseTicketRanges($range->rangeStart) as $r) {
            $ticketRanges[] = $r['start'];
            $ticketRanges[] = $r['end'];
        }
        return $ticketRanges;
    }

    // ── String helpers ────────────────────────────────────────────────────────

    protected function getRandomString(int $length = 16): string
    {
        $chars  = '0123456789abcdefghijklmnopqrstuvwxyz';
        $max    = mb_strlen($chars, '8bit') - 1;
        $pieces = [];
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $chars[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
