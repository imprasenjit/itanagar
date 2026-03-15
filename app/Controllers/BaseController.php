<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected string $role      = '';
    protected int    $vendorId  = 0;
    protected string $name      = '';
    protected string $roleText  = '';
    protected string $lastLogin = '';
    protected array  $global    = [];

    protected $helpers = ['url', 'cias_helper'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    protected function isLoggedIn(): void
    {
        $session = session();
        if ($session->get('isLoggedIn') !== true) {
            redirect()->to(base_url('login'))->send();
            exit();
        }
        $this->role      = (string) $session->get('role');
        $this->vendorId  = (int)    $session->get('userId');
        $this->name      = (string) $session->get('name');
        $this->roleText  = (string) $session->get('roleText');
        $this->lastLogin = (string) $session->get('lastLogin');

        $this->global['name']       = $this->name;
        $this->global['role']       = $this->role;
        $this->global['role_text']  = $this->roleText;
        $this->global['last_login'] = $this->lastLogin;
    }

    /** Returns TRUE when user IS admin (used as a guard: if isAdmin() == FALSE → deny) */
    protected function isAdmin(): bool
    {
        return $this->roleText === ROLE_ADMIN;
    }

    protected function loadThis(): string
    {
        $this->global['pageTitle'] = 'Itanagarchoice : Access Denied';
        return view('includes/header', $this->global)
             . view('access')
             . view('includes/footer');
    }

    // ── View loader ───────────────────────────────────────────────────────────

    protected function loadViews(string $viewName, array $headerInfo = [], ?array $pageInfo = null, ?array $footerInfo = null): string
    {
        $out  = view('includes/header', $headerInfo);
        $out .= view($viewName, $pageInfo ?? []);
        $out .= view('includes/footer', $footerInfo ?? []);
        return $out;
    }

    // ── JSON response ───────────────────────────────────────────────────────

    protected function response(array $data = []): void
    {
        echo $this->response
            ->setStatusCode(200)
            ->setContentType('application/json', 'utf-8')
            ->setBody(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        exit();
    }

    // ── Pagination ──────────────────────────────────────────────────────────

    protected function paginationCompress(string $link, int $count, int $perPage = 10, int $segment = 0): array
    {
        $pager = service('pager');

        // segment > 0 → page number lives in a URI segment (e.g. /path/2)
        // segment = 0 → fall back to ?page=N query param
        $currentPage = $segment > 0
            ? (int) ($this->request->getUri()->getSegment($segment) ?: 1)
            : (int) ($this->request->getGet('page') ?: 1);

        $offset = ($currentPage - 1) * $perPage;

        // Register total/page data so $pager->links() can render pagination HTML
        $pager->store('default', $currentPage, $perPage, $count);

        return [
            'page'    => $perPage,
            'segment' => $offset,
            'pager'   => $pager,
        ];
    }
}
