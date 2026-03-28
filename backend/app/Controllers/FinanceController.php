<?php

namespace App\Controllers;

use App\Models\WebModel;
use App\Models\UserModel;

/**
 * FinanceController — admin management of orders, tickets, transactions,
 * wallet history, winners, refunds, withdrawals, and transfers.
 * All routes begin with "web/".
 */
class FinanceController extends BaseController
{
    protected WebModel  $webModel;
    protected UserModel $userModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->webModel  = new WebModel();
        $this->userModel = new UserModel();
    }

    // ── Orders ────────────────────────────────────────────────────────────────

    public function order()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'event : Order History';
        return $this->loadViews('pages/web/order', $this->global, [], null);
    }

    public function order_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->webModel->order_list_count('');
        $filtered = $this->webModel->order_list_count($search);
        $orders   = $this->webModel->order_list($search, $length, $start);

        $webCache = [];
        $data     = [];

        foreach ($orders as $o) {
            $rawTickets = json_decode($o->tickets ?? '[]', true) ?: [];
            $enriched   = [];
            foreach ($rawTickets as $t) {
                $webId = (int)($t['web_id'] ?? 0);
                if (!isset($webCache[$webId])) {
                    $webInfo          = $this->webModel->getWebInfo($webId);
                    $webCache[$webId] = $webInfo ? esc($webInfo->name) : 'Unknown';
                }
                $enriched[] = [
                    'game'      => $webCache[$webId],
                    'ticket_no' => esc($t['ticket_no'] ?? ''),
                ];
            }

            $count = count($enriched);
            if ($o->order_status == '1') {
                $badge = '<span class="badge bg-success">Confirmed</span>';
            } elseif ((string)$o->paid_status === '0') {
                $badge = '<span class="badge bg-warning text-dark">Pending</span>';
            } else {
                $badge = '<span class="badge bg-danger">Failed</span>';
            }

            $data[] = [
                'DT_RowId'     => 'order-' . $o->id,
                'raw_id'       => $o->id,
                'paid_status'  => $o->paid_status,
                'order_status' => $o->order_status,
                'order_no'     => '<strong>#' . $o->id . '</strong>',
                'user'         => '<div>' . esc($o->uname ?? '') . '</div><small class="text-muted">' . esc($o->email ?? '') . '</small>',
                'ticket_info'  => '<button class="btn btn-sm btn-outline-primary expand-tickets" type="button"><i class="bi bi-ticket-perforated me-1"></i>' . $count . ' ticket' . ($count !== 1 ? 's' : '') . '</button>',
                'tickets'      => $enriched,
                'amount'       => '₹' . number_format((float)$o->total_price, 2),
                'payment'      => 'UPI',
                'order_id'     => '<small class="text-muted">' . esc($o->razorpay_order_id ?? '') . '</small>',
                'date'         => date('M d, Y h:i a', strtotime($o->createdAt)),
                'status'       => $badge,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function confirm_order_by_admin()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $orderId = (int) $this->request->getPost('orderid');
        $this->webModel->update_order($orderId, ['order_status' => 1]);
        return $this->response->setJSON(['status' => true]);
    }

    public function release_order_by_admin()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('orderid');
        $result  = $this->webModel->get_order_by_id($orderId);

        if (!$result) {
            return $this->response->setJSON(['status' => false]);
        }

        $tickets = json_decode($result->tickets);
        $this->webModel->update_order($orderId, ['order_status' => 0, 'paid_status' => 'RELEASED']);

        $cleared = false;
        foreach ($tickets as $ticket) {
            $cleared = $this->webModel->clear_cart_data(
                (int)$result->user_id,
                $ticket->ticket_no,
                $ticket->web_id
            );
        }
        return $this->response->setJSON(['status' => (bool)$cleared]);
    }

    // ── Tickets ───────────────────────────────────────────────────────────────

    public function tickets()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $search = esc($this->request->getGet('search') ?? '');
        $status = $this->request->getGet('status') ?? '';

        $count  = $this->webModel->ticket_list_count($search, $status !== '' ? $status : null);
        $pgData = $this->paginationCompress('web/tickets/', $count, 20, 3);

        $data = [
            'tickets'  => $this->webModel->ticket_list($search, $status !== '' ? $status : null, $pgData['page'], $pgData['segment']),
            'pager'    => $pgData['pager'],
            'filters'  => compact('search', 'status'),
            'total'    => $count,
        ];
        $this->global['pageTitle'] = 'event : Ticket Management';
        return $this->loadViews('pages/web/tickets', $this->global, $data, null);
    }

    public function ticket_cancel()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $orderId = (int) $this->request->getPost('order_id');
        $this->webModel->cancel_ticket($orderId);
        return $this->response->setJSON(['status' => true, 'message' => 'Ticket cancelled successfully']);
    }

    public function ticket_resend()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $order   = $this->webModel->get_order_by_id($orderId);

        if (!$order) {
            return $this->response->setJSON(['status' => false, 'message' => 'Order not found']);
        }

        $user = $this->userModel->getUserInfoById((int) $order->user_id);

        if (!$user) {
            return $this->response->setJSON(['status' => false, 'message' => 'User not found']);
        }

        helper('email_helper');
        $tickets   = json_decode($order->tickets ?? '[]', true);
        $emailData = [
            'name'           => $user->name,
            'email'          => $user->email,
            'order_id'       => $order->id,
            'transaction_id' => $order->transaction_id,
            'total_price'    => $order->total_price,
            'tickets'        => $tickets,
        ];

        $sent = sendmail($user->email, 'Your Ticket Confirmation', view('email/contact', $emailData, true));
        return $this->response->setJSON(['status' => (bool) $sent, 'message' => $sent ? 'Ticket resent successfully' : 'Failed to resend ticket']);
    }

    public function ticket_verify()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $ticketRef = esc($this->request->getPost('ticket_ref') ?? '');
        if (empty($ticketRef)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ticket reference required']);
        }

        $order = null;
        if (is_numeric($ticketRef)) {
            $order = $this->webModel->get_order_by_id((int) $ticketRef);
        }
        if (!$order) {
            $order = $this->webModel->get_order_by_orderId($ticketRef);
        }

        if (!$order) {
            return $this->response->setJSON(['status' => false, 'message' => 'No ticket found for this reference']);
        }

        $user    = $this->userModel->getUserInfoById((int) $order->user_id);
        $tickets = json_decode($order->tickets ?? '[]', true);

        return $this->response->setJSON([
            'status'  => true,
            'valid'   => $order->paid_status === 'PAID',
            'order'   => [
                'id'             => $order->id,
                'paid_status'    => $order->paid_status,
                'total_price'    => $order->total_price,
                'transaction_id' => $order->transaction_id,
                'createdAt'      => $order->createdAt,
            ],
            'user'    => $user ? ['name' => $user->name, 'email' => $user->email] : null,
            'tickets' => $tickets,
        ]);
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    public function transactions()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $webId    = $this->request->getGet('web_id') ? (int)$this->request->getGet('web_id') : null;
        $dateFrom = esc($this->request->getGet('date_from') ?? '');
        $dateTo   = esc($this->request->getGet('date_to')   ?? '');
        $status   = $this->request->getGet('status') ?? '';
        $search   = esc($this->request->getGet('search') ?? '');

        $data = [
            'games'   => $this->webModel->get_allweb(),
            'filters' => compact('webId', 'dateFrom', 'dateTo', 'status', 'search'),
        ];
        $this->global['pageTitle'] = 'event : Transactions';
        return $this->loadViews('pages/web/transactions', $this->global, $data, null);
    }

    public function transactions_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $webId    = $this->request->getGet('web_id')    ? (int)$this->request->getGet('web_id') : null;
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo   = $this->request->getGet('date_to')   ?? '';
        $status   = $this->request->getGet('status')    ?? '';

        $total    = $this->webModel->txn_count(null, null, null, null, '');
        $filtered = $this->webModel->txn_count($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search);
        $rows     = $this->webModel->txn_list($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search, $length, $start);

        $statusMap = [
            'PAID'      => ['success', 'Paid'],
            'RELEASED'  => ['info',    'Released'],
            'CANCELLED' => ['danger',  'Cancelled'],
            '0'         => ['warning', 'Unpaid'],
            '1'         => ['success', 'Paid'],
            '2'         => ['danger',  'Failed'],
        ];

        $data = [];
        foreach ($rows as $txn) {
            $tickets   = json_decode($txn->tickets ?? '[]', true) ?: [];
            $ticketNos = array_column($tickets, 'ticket_no');
            if (!empty($ticketNos)) {
                $shown      = array_slice($ticketNos, 0, 3);
                $rest       = count($ticketNos) - 3;
                $ticketHtml = esc(implode(', ', $shown));
                if ($rest > 0) {
                    $ticketHtml .= ' <span class="text-muted">+' . $rest . ' more</span>';
                }
            } else {
                $ticketHtml = esc($txn->transaction_id ?? '—');
            }

            [$badgeColor, $statusLabel] = $statusMap[(string)$txn->paid_status] ?? ['secondary', esc($txn->paid_status)];

            $data[] = [
                'order_no' => '<strong>#' . $txn->id . '</strong>',
                'date'     => '<small>' . date('d M Y, H:i', strtotime($txn->createdAt)) . '</small>',
                'event'    => esc($txn->web_name ?? '—'),
                'user'     => '<div>' . esc($txn->user_name ?? '—') . '</div><small class="text-muted">' . esc($txn->user_email ?? '') . '</small>',
                'tickets'  => '<small class="text-muted">' . $ticketHtml . '</small>',
                'amount'   => '₹' . number_format((float)$txn->total_price, 2),
                'status'   => '<span class="badge bg-' . $badgeColor . '">' . $statusLabel . '</span>',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    // ── Wallet History ────────────────────────────────────────────────────────

    public function wallet()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_wallet_count($searchText);
        $pgData = $this->paginationCompress('web/wallet/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_wallet_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Wallet History';
        return $this->loadViews('pages/web/wallet', $this->global, $data, null);
    }

    // ── Winners ───────────────────────────────────────────────────────────────

    public function winner()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_winner_count($searchText);
        $pgData = $this->paginationCompress('web/winner/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'amount'      => $this->webModel->admin_winner_total(),
            'userRecords' => $this->webModel->admin_winner_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Winner History';
        return $this->loadViews('pages/web/winner', $this->global, $data, null);
    }

    // ── Refunds ───────────────────────────────────────────────────────────────

    public function refund()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_refund_count($searchText);
        $pgData = $this->paginationCompress('web/refund/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_refund_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Refund Requests';
        return $this->loadViews('pages/web/refund', $this->global, $data, null);
    }

    public function refund_req(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }
        $id    = (int) $this->request->getPost('id');
        $money = (float) $this->request->getPost('money');
        $type  = $this->request->getPost('type');
        $this->webModel->refund_process($id, $type, $userId, $money);
        return redirect()->to('web/refund')
            ->with('success', $type === 'Refund' ? 'Refund processed successfully.' : 'Refund request rejected.');
    }

    // ── Withdrawals ───────────────────────────────────────────────────────────

    public function withdrawl()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_withdrawl_count($searchText);
        $pgData = $this->paginationCompress('web/withdrawl/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_withdrawl_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Withdrawal Requests';
        return $this->loadViews('pages/web/withdrawl', $this->global, $data, null);
    }

    public function with_req(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }
        $id   = (int) $this->request->getPost('id');
        $type = $this->request->getPost('type');
        $this->webModel->withdrawl_process($id, $type);
        return redirect()->back()->with('success', $type === 'Reject' ? 'Request rejected.' : 'Request processed successfully.');
    }

    // ── PayPal Transfers ──────────────────────────────────────────────────────

    public function transfer()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_transfer_count($searchText);
        $pgData = $this->paginationCompress('web/transfer/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_transfer_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : PayPal Transfer Requests';
        return $this->loadViews('pages/web/transfer', $this->global, $data, null);
    }
}
