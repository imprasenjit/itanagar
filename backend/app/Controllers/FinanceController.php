<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GameModel;
use App\Models\CartOrderModel;
use App\Models\WalletModel;
use App\Models\WinnerModel;

/**
 * FinanceController — admin management of orders, tickets, transactions,
 * wallet history, winners, refunds, withdrawals, and transfers.
 * All routes begin with "web/".
 */
class FinanceController extends BaseController
{
    protected GameModel      $gameModel;
    protected CartOrderModel $cartOrderModel;
    protected WalletModel    $walletModel;
    protected WinnerModel    $winnerModel;
    protected UserModel      $userModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->gameModel      = new GameModel();
        $this->cartOrderModel = new CartOrderModel();
        $this->walletModel    = new WalletModel();
        $this->winnerModel    = new WinnerModel();
        $this->userModel      = new UserModel();
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

        $total    = $this->cartOrderModel->order_list_count('');
        $filtered = $this->cartOrderModel->order_list_count($search);
        $orders   = $this->cartOrderModel->order_list($search, $length, $start);

        $webCache = [];
        $data     = [];

        foreach ($orders as $o) {
            $rawTickets = json_decode($o->tickets ?? '[]', true) ?: [];
            $enriched   = [];
            foreach ($rawTickets as $t) {
                $webId = (int)($t['web_id'] ?? 0);
                if (!isset($webCache[$webId])) {
                    $webInfo          = $this->gameModel->getWebInfo($webId);
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
        $this->cartOrderModel->update_order($orderId, ['order_status' => 1]);
        return $this->response->setJSON(['status' => true]);
    }

    public function release_order_by_admin()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('orderid');
        $result  = $this->cartOrderModel->get_order_by_id($orderId);

        if (!$result) {
            return $this->response->setJSON(['status' => false]);
        }

        $tickets = json_decode($result->tickets);
        $this->cartOrderModel->update_order($orderId, ['order_status' => 0, 'paid_status' => 'RELEASED']);

        $cleared = false;
        foreach ($tickets as $ticket) {
            $cleared = $this->cartOrderModel->clear_cart_data(
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

        $count  = $this->cartOrderModel->ticket_list_count($search, $status !== '' ? $status : null);
        $pgData = $this->paginationCompress('web/tickets/', $count, 20, 3);

        $data = [
            'tickets'  => $this->cartOrderModel->ticket_list($search, $status !== '' ? $status : null, $pgData['page'], $pgData['segment']),
            'pager'    => $pgData['pager'],
            'filters'  => compact('search', 'status'),
            'total'    => $count,
        ];
        $this->global['pageTitle'] = 'event : Ticket Management';
        return $this->loadViews('pages/web/tickets', $this->global, $data, null);
    }

    /**
     * Returns the current count of actively-blocked (reserved but unpaid) tickets.
     * Called by the admin tickets page to populate the counter badge.
     */
    public function blocked_tickets_count()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $count = $this->cartOrderModel->count_blocked_tickets();
        return $this->response->setJSON(['status' => true, 'count' => $count]);
    }

    /**
     * Release only expired holds (reserved_until < NOW()).
     * Safe to run at any time — does not affect active carts.
     */
    public function release_expired_holds()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $released = $this->cartOrderModel->release_expired_reservations();
        return $this->response->setJSON([
            'status'   => true,
            'released' => $released,
            'message'  => $released . ' expired hold(s) have been released.',
        ]);
    }

    /**
     * Force-release ALL unpaid cart holds, including ones that haven't expired yet.
     * This is a destructive admin action — use only when tickets are genuinely stuck.
     */
    public function force_release_holds()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $released = $this->cartOrderModel->force_release_all_holds();
        return $this->response->setJSON([
            'status'   => true,
            'released' => $released,
            'message'  => $released . ' blocked ticket hold(s) have been released.',
        ]);
    }

    public function ticket_cancel()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $orderId = (int) $this->request->getPost('order_id');
        $this->cartOrderModel->cancel_ticket($orderId);
        return $this->response->setJSON(['status' => true, 'message' => 'Ticket cancelled successfully']);
    }

    public function ticket_resend()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $order   = $this->cartOrderModel->get_order_by_id($orderId);

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
            $order = $this->cartOrderModel->get_order_by_id((int) $ticketRef);
        }
        if (!$order) {
            $order = $this->cartOrderModel->get_order_by_orderId($ticketRef);
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
            'games'   => $this->gameModel->get_allweb(),
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

        $total    = $this->cartOrderModel->txn_count(null, null, null, null, '');
        $filtered = $this->cartOrderModel->txn_count($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search);
        $rows     = $this->cartOrderModel->txn_list($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search, $length, $start);

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
        $count  = $this->walletModel->admin_wallet_count($searchText);
        $pgData = $this->paginationCompress('web/wallet/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->walletModel->admin_wallet_list($searchText, $pgData['page'], $pgData['segment']),
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
        $count  = $this->winnerModel->admin_winner_count($searchText);
        $pgData = $this->paginationCompress('web/winner/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'amount'      => $this->winnerModel->admin_winner_total(),
            'userRecords' => $this->winnerModel->admin_winner_list($searchText, $pgData['page'], $pgData['segment']),
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
        $count  = $this->walletModel->admin_refund_count($searchText);
        $pgData = $this->paginationCompress('web/refund/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->walletModel->admin_refund_list($searchText, $pgData['page'], $pgData['segment']),
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
        $this->walletModel->refund_process($id, $type, $userId, $money);
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
        $count  = $this->walletModel->admin_withdrawl_count($searchText);
        $pgData = $this->paginationCompress('web/withdrawl/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->walletModel->admin_withdrawl_list($searchText, $pgData['page'], $pgData['segment']),
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
        $this->walletModel->withdrawl_process($id, $type);
        return redirect()->back()->with('success', $type === 'Reject' ? 'Request rejected.' : 'Request processed successfully.');
    }

    // ── DataTables server-side endpoints ──────────────────────────────────────

    public function wallet_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->walletModel->admin_wallet_count('');
        $filtered = $this->walletModel->admin_wallet_count($search);
        $rows     = $this->walletModel->admin_wallet_list($search, $length, $start);

        $data = [];
        $sr = $start + 1;
        foreach ($rows as $ms) {
            $data[] = [
                'sr'            => $sr++,
                'user'          => esc($ms->uname),
                'money'         => '<strong>&#8377;' . esc($ms->money) . '</strong>',
                'paymentInfo'   => '<span class="badge bg-secondary">' . esc($ms->type) . '</span>',
                'transactionId' => '<small>' . esc($ms->trancaction_id) . '</small>',
                'date'          => date('M d, Y h:i a', strtotime($ms->createdAt)),
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function winner_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->winnerModel->admin_winner_count('');
        $filtered = $this->winnerModel->admin_winner_count($search);
        $rows     = $this->winnerModel->admin_winner_list($search, $length, $start);

        $data = [];
        foreach ($rows as $ms) {
            $tickets   = json_decode($ms->tickets ?? '[]', true) ?: [];
            $ticketNos = array_column($tickets, 'ticket_no');
            $ticketHtml = '';
            foreach (array_slice($ticketNos, 0, 5) as $tn) {
                $ticketHtml .= '<code class="me-1">' . esc($tn) . '</code>';
            }
            if (count($ticketNos) > 5) {
                $ticketHtml .= '<small class="text-muted">+' . (count($ticketNos) - 5) . ' more</small>';
            }
            $data[] = [
                'order_no'       => '<strong>#' . $ms->id . '</strong>',
                'user'           => esc($ms->uname),
                'event'          => esc($ms->game_name),
                'tickets'        => $ticketHtml,
                'price'          => '&#8377;' . esc($ms->total_price),
                'paymentType'    => $ms->paid_type == '0' ? 'Wallet' : esc($ms->paid_type),
                'winningPrize'   => '<strong class="text-success">&#8377;' . esc($ms->prize) . '</strong>',
                'transactionId'  => '<small>' . esc($ms->transaction_id) . '</small>',
                'date'           => date('M d, Y h:i a', strtotime($ms->createdAt)),
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function withdrawl_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->walletModel->admin_withdrawl_count('');
        $filtered = $this->walletModel->admin_withdrawl_count($search);
        $rows     = $this->walletModel->admin_withdrawl_list($search, $length, $start);

        $data = [];
        $sr = $start + 1;
        foreach ($rows as $ms) {
            if ($ms->status == '0') { $st = 'Pending'; }
            elseif ($ms->status == '1') { $st = 'Processed'; }
            else { $st = 'Rejected'; }

            $typeBadge = '<span class="badge ' . ($ms->type == 1 ? 'bg-primary' : 'bg-info') . '">' . ($ms->type == 1 ? 'Bank' : 'PayPal') . '</span>';
            $statusBadge = '<span class="badge ' . ($ms->status == '0' ? 'bg-warning text-dark' : ($ms->status == '1' ? 'bg-success' : 'bg-danger')) . '">' . $st . '</span>';

            if ($ms->status == '0') {
                $btnSend = $ms->type == 1 ? 'Send Via Bank' : 'Send Via PayPal';
                $actions = '<form onsubmit="return confirm(\'Are you sure?\');" action="' . base_url('web/with_req/' . $ms->user_id) . '" method="post" class="d-inline-flex gap-1">'
                         . '<input type="hidden" name="id" value="' . $ms->id . '">'
                         . '<input type="hidden" name="p_email" value="' . esc($ms->paypal_email) . '">'
                         . '<input type="hidden" name="money" value="' . $ms->money . '">'
                         . '<button type="submit" name="type" value="' . $btnSend . '" class="btn btn-sm btn-success"><i class="bi bi-send"></i> ' . $btnSend . '</button>'
                         . '<button type="submit" name="type" value="Reject" class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i> Reject</button>'
                         . '</form>';
            } else {
                $actions = '<span class="badge ' . ($ms->status == '1' ? 'bg-success' : 'bg-danger') . '">' . $st . '</span>';
            }

            $data[] = [
                'sr'          => $sr++,
                'user'        => esc($ms->uname),
                'type'        => $typeBadge,
                'paypalEmail' => '<small>' . esc($ms->paypal_email) . '</small>',
                'money'       => '<strong>&#8377;' . esc($ms->money) . '</strong>',
                'status'      => $statusBadge,
                'date'        => date('M d, Y h:i a', strtotime($ms->createdAt)),
                'actions'     => $actions,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function tickets_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');
        $status = $this->request->getGet('status') ?? '';

        $total    = $this->cartOrderModel->ticket_list_count('', null);
        $filtered = $this->cartOrderModel->ticket_list_count($search, $status !== '' ? $status : null);
        $rows     = $this->cartOrderModel->ticket_list($search, $status !== '' ? $status : null, $length, $start);

        $statusMap = ['PAID' => ['success', 'Paid'], 'RELEASED' => ['info', 'Released'], 'CANCELLED' => ['danger', 'Cancelled'], '0' => ['warning', 'Unpaid'], '2' => ['danger', 'Failed']];

        $data = [];
        foreach ($rows as $ticket) {
            $tData     = json_decode($ticket->tickets ?? '[]', true);
            $ticketNos = array_column($tData, 'ticket_no');
            $webName   = $ticket->web_name ?? (isset($tData[0]['web_name']) ? $tData[0]['web_name'] : '—');
            [$badgeColor, $statusLabel] = $statusMap[$ticket->paid_status] ?? ['secondary', $ticket->paid_status];
            $canCancel = in_array($ticket->paid_status, ['PAID', '1']);
            $canResend = in_array($ticket->paid_status, ['PAID', '1']);

            $ticketHtml = !empty($ticketNos)
                ? '<small class="text-muted">' . implode(', ', array_slice($ticketNos, 0, 3)) . (count($ticketNos) > 3 ? ' <span class="text-muted">+' . (count($ticketNos)-3) . ' more</span>' : '') . '</small>'
                : '<small class="text-muted">—</small>';

            $userHtml = '<div>' . esc($ticket->user_name ?? '—') . '</div><small class="text-muted">' . esc($ticket->user_email ?? '') . '</small>';

            $actBtns = '<div class="d-flex justify-content-center gap-1">';
            if ($canResend) $actBtns .= '<button class="btn btn-xs btn-outline-primary" title="Resend Ticket" onclick="resendTicket(' . $ticket->id . ')"><i class="bi bi-envelope-arrow-up-fill"></i></button>';
            if ($canCancel) $actBtns .= '<button class="btn btn-xs btn-outline-danger" title="Cancel Ticket" onclick="cancelTicket(' . $ticket->id . ')"><i class="bi bi-x-circle-fill"></i></button>';
            $actBtns .= '</div>';

            $data[] = [
                'order'   => '<strong>#' . $ticket->id . '</strong>',
                'event'   => esc($webName),
                'user'    => $userHtml,
                'tickets' => $ticketHtml,
                'amount'  => '&#8377;' . number_format((float)$ticket->total_price, 2),
                'date'    => '<small>' . date('d M Y', strtotime($ticket->createdAt)) . '</small>',
                'status'  => '<span class="badge bg-' . $badgeColor . '">' . $statusLabel . '</span>',
                'actions' => $actBtns,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function refund_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->walletModel->admin_refund_count('');
        $filtered = $this->walletModel->admin_refund_count($search);
        $rows     = $this->walletModel->admin_refund_list($search, $length, $start);

        $data = [];
        $sr = $start + 1;
        foreach ($rows as $ms) {
            if ($ms->status == '0') { $st = 'Pending'; }
            elseif ($ms->status == '1') { $st = 'Refunded'; }
            else { $st = 'Rejected'; }

            $statusBadge = '<span class="badge ' . ($ms->status == '0' ? 'bg-warning text-dark' : ($ms->status == '1' ? 'bg-success' : 'bg-danger')) . '">' . $st . '</span>';

            if ($ms->status == '0') {
                $actions = '<form onsubmit="return confirm(\'Are you sure?\');" action="' . base_url('web/refund_req/' . $ms->user_id) . '" method="post" class="d-inline-flex gap-1">'
                         . '<input type="hidden" name="id" value="' . $ms->id . '">'
                         . '<input type="hidden" name="money" value="' . $ms->money . '">'
                         . '<button type="submit" name="type" value="Refund" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Refund</button>'
                         . '<button type="submit" name="type" value="Reject" class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i> Reject</button>'
                         . '</form>';
            } else {
                $actions = '<span class="badge ' . ($ms->status == '1' ? 'bg-success' : 'bg-danger') . '">' . $st . '</span>';
            }

            $data[] = [
                'sr'      => $sr++,
                'user'    => esc($ms->uname),
                'money'   => '<strong>&#8377;' . esc($ms->money) . '</strong>',
                'reason'  => esc($ms->reason),
                'status'  => $statusBadge,
                'date'    => date('M d, Y h:i a', strtotime($ms->createdAt)),
                'actions' => $actions,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function user_wallet_data(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);

        $total    = $this->walletModel->user_wallet_count($userId);
        $filtered = $total;
        $rows     = $this->walletModel->user_wallet_list($userId, $length, $start);

        $data = [];
        $sr = $start + 1;
        foreach ($rows as $ms) {
            $data[] = [
                'sr'          => $sr++,
                'user'        => esc($ms->uname),
                'money'       => esc($ms->money),
                'paymentInfo' => '<span class="badge bg-secondary">' . esc($ms->type) . '</span>',
                'date'        => date('M d, Y h:i a', strtotime($ms->createdAt)),
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }

    public function user_order_data(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }
        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->cartOrderModel->user_orders_count($userId, '');
        $filtered = $this->cartOrderModel->user_orders_count($userId, $search);
        $orders   = $this->cartOrderModel->user_orders_list($userId, $search, $length, $start);

        $webCache = [];
        $data     = [];
        foreach ($orders as $order) {
            $tickets = json_decode($order->tickets ?? '[]', true) ?: [];
            $ticketHtml = '<table class="table table-bordered table-sm mb-0"><thead><tr><th>Event</th><th>Ticket No.</th></tr></thead><tbody>';
            foreach ($tickets as $t) {
                $webId = (int)($t['web_id'] ?? 0);
                if (!isset($webCache[$webId])) {
                    $info = $this->gameModel->getWebInfo($webId);
                    $webCache[$webId] = $info ? esc($info->name) : 'Unknown';
                }
                $ticketHtml .= '<tr><td>' . $webCache[$webId] . '</td><td><code>' . esc($t['ticket_no'] ?? '') . '</code></td></tr>';
            }
            $ticketHtml .= '</tbody></table>';

            $actionBtn = $order->order_status == 0
                ? '<a class="btn btn-sm btn-warning confirmOrder" href="#!" data-orderid="' . $order->id . '"><i class="bi bi-check-circle-fill me-1"></i> Confirm</a>'
                : '<span class="badge bg-success">Confirmed</span>';

            $data[] = [
                'order_no'      => '<strong>#' . $order->id . '</strong>',
                'tickets'       => $ticketHtml,
                'price'         => '<strong>&#8377;' . esc($order->total_price) . '</strong>',
                'paymentType'   => 'UPI',
                'transactionId' => '<small>' . esc($order->transaction_id) . '</small>',
                'date'          => date('M d, Y h:i a', strtotime($order->createdAt)),
                'actions'       => $actionBtn,
            ];
        }
        return $this->response->setJSON(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);
    }
}

