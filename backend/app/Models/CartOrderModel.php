<?php

namespace App\Models;

use CodeIgniter\Model;

class CartOrderModel extends Model
{
    // ── Cart ────────────────────────────────────────────────────────────────

    public function checkIfTicketAlreadyPresent(array $ticket): bool
    {
        $count = $this->db->table('tbl_cart')
            ->where('web_id',      $ticket['web_id'])
            ->where('user_id',     $ticket['user_id'])
            ->where('ticket_no',   $ticket['ticket_no'])
            ->where('total_price', $ticket['total_price'])
            ->get()->getNumRows();
        return $count > 0;
    }

    public function insert_cart(array $data): int
    {
        $this->db->table('tbl_cart')->insertBatch($data);
        return $this->db->insertID();
    }

    public function cart_data(int $userId)
    {
        return $this->db->table('tbl_cart')
            ->select('tbl_cart.*, tbl_webs.name')
            ->join('tbl_webs', 'tbl_webs.id = tbl_cart.web_id')
            ->where('user_id', $userId)
            ->where('paid_status', 0)
            ->get()->getResult();
    }

    public function count_cart(int $userId): int
    {
        return $this->db->table('tbl_cart')
            ->where('user_id', $userId)
            ->where('paid_status', 0)
            ->countAllResults();
    }

    public function update_cart_data(int $userId, int $web_id, int $ticket_no, array $data): void
    {
        $this->db->table('tbl_cart')
            ->where('user_id',   $userId)
            ->where('web_id',    $web_id)
            ->where('ticket_no', $ticket_no)
            ->update($data);
    }

    /**
     * Check whether a ticket is taken by anyone (optionally excluding one user).
     *
     * A ticket is considered taken if:
     *  - paid_status = 1  (committed / sold), OR
     *  - paid_status = 0 AND reserved_until is set and in the future
     *    (another user is actively holding it in their cart).
     *
     * @param int $excludeUserId  Pass the current user's ID to ignore their own hold.
     */
    public function get_ticket_availability(int $ticket, int $web_id, int $excludeUserId = 0)
    {
        $now     = date('Y-m-d H:i:s');
        $builder = $this->db->table('tbl_cart')
            ->where('web_id',    $web_id)
            ->where('ticket_no', $ticket);

        if ($excludeUserId > 0) {
            $builder->where('user_id !=', $excludeUserId);
        }

        $builder->groupStart()
            ->where('paid_status', 1)
            ->orGroupStart()
                ->where('paid_status', 0)
                ->where('reserved_until IS NOT NULL', null, false)
                ->where('reserved_until >', $now)
            ->groupEnd()
        ->groupEnd();

        return $builder->orderBy('id')->get()->getResult();
    }

    /**
     * Returns ticket numbers that are unavailable for a given game — either
     * permanently sold (paid_status=1) or actively reserved by someone.
     * Used by the ticket-picker UI to grey out taken ticket numbers.
     */
    public function get_sold_tickets(int $web_id): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = $this->db->table('tbl_cart')
            ->select('ticket_no')
            ->where('web_id', $web_id)
            ->groupStart()
                ->where('paid_status', 1)
                ->orGroupStart()
                    ->where('paid_status', 0)
                    ->where('reserved_until IS NOT NULL', null, false)
                    ->where('reserved_until >', $now)
                ->groupEnd()
            ->groupEnd()
            ->get()->getResultArray();
        return array_map(fn($row) => (int) $row['ticket_no'], $rows);
    }

    /**
     * Extend the reservation TTL on all unpaid cart items for a user.
     * Called just before creating a Razorpay order to keep tickets from
     * expiring during the payment window.
     */
    public function extend_reservations(int $userId, string $until): void
    {
        $this->db->table('tbl_cart')
            ->where('user_id',    $userId)
            ->where('paid_status', 0)
            ->update(['reserved_until' => $until]);
    }

    /**
     * Delete expired holds for a specific ticket by OTHER users before a new
     * insert. This prevents the UNIQUE KEY from blocking a valid new hold when
     * a stale expired row still exists (cron hasn't run yet).
     */
    public function release_expired_for_ticket(int $ticketNo, int $webId, int $excludeUserId): void
    {
        $this->db->table('tbl_cart')
            ->where('web_id',        $webId)
            ->where('ticket_no',     $ticketNo)
            ->where('user_id !=',    $excludeUserId)
            ->where('paid_status',   0)
            ->where('reserved_until IS NOT NULL', null, false)
            ->where('reserved_until <', date('Y-m-d H:i:s'))
            ->delete();
    }

    /**
     * Delete all unpaid cart rows whose reservation window has expired.
     * Run by the cron endpoint.  Returns the number of rows released.
     */
    public function release_expired_reservations(): int
    {
        $this->db->table('tbl_cart')
            ->where('paid_status', 0)
            ->where('reserved_until IS NOT NULL', null, false)
            ->where('reserved_until <', date('Y-m-d H:i:s'))
            ->delete();
        return $this->db->affectedRows();
    }

    /**
     * Count all unpaid cart rows that have an active reservation hold
     * (reserved_until is set and still in the future).
     * Used by the admin dashboard to show how many tickets are currently blocked.
     */
    public function count_blocked_tickets(): int
    {
        return $this->db->table('tbl_cart')
            ->where('paid_status', 0)
            ->where('reserved_until IS NOT NULL', null, false)
            ->where('reserved_until >', date('Y-m-d H:i:s'))
            ->countAllResults();
    }

    /**
     * Force-release ALL unpaid holds regardless of expiry.
     * Admin-only action: clears every cart row that is not yet committed.
     * Returns the number of rows deleted.
     */
    public function force_release_all_holds(): int
    {
        $this->db->table('tbl_cart')
            ->where('paid_status', 0)
            ->delete();
        return $this->db->affectedRows();
    }

    public function up_cart(int $guestId, int $userId): bool
    {
        $this->db->table('tbl_cart')->where('user_id', $guestId)->update(['user_id' => $userId]);
        return true;
    }

    public function clear_cart(int $userId): int
    {
        $this->db->table('tbl_cart')->where('user_id', $userId)->delete();
        return $this->db->affectedRows();
    }

    public function delete_cart_item(int $cartId, int $userId): void
    {
        $this->db->table('tbl_cart')
            ->where('id', $cartId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function clear_cart_data(int $userId, int $ticket_no, int $web_id): int
    {
        $this->db->table('tbl_cart')
            ->where('user_id',   $userId)
            ->where('ticket_no', $ticket_no)
            ->where('web_id',    $web_id)
            ->delete();
        return $this->db->affectedRows();
    }

    // ── Orders ────────────────────────────────────────────────────────────────

    public function get_order_by_id(int $id)
    {
        return $this->db->table('tbl_order')->where('id', $id)->get()->getRow();
    }

    public function get_order_by_orderId(string $id)
    {
        return $this->db->table('tbl_order')->where('razorpay_order_id', $id)->get()->getRow();
    }

    public function get_order(int $userId, string $tickets, float $total): array
    {
        return $this->db->table('tbl_order')
            ->select('id')
            ->where('tickets',     $tickets)
            ->where('user_id',     $userId)
            ->where('total_price', $total)
            ->where('paid_status !=', 'RELEASED')
            ->get()->getResult();
    }

    public function update_order(int $id, array $data): void
    {
        $this->db->table('tbl_order')->where('id', $id)->update($data);
    }

    public function update_order_by_orderId(string $id, array $data): void
    {
        $this->db->table('tbl_order')->where('razorpay_order_id', $id)->update($data);
    }

    public function insert_order(array $data): int
    {
        $this->db->table('tbl_order')->insert($data);
        return $this->db->insertID();
    }

    public function order_data(int $userId)
    {
        return $this->db->table('tbl_cart')
            ->select('tbl_cart.total_price, tbl_cart.web_id, tbl_cart.user_id, tbl_webs.name, tbl_cart.ticket_no')
            ->join('tbl_webs', 'tbl_webs.id = tbl_cart.web_id')
            ->where('tbl_cart.user_id',    $userId)
            ->where('tbl_cart.paid_status', 0)
            ->get()->getResult();
    }

    public function total_pay(int $userId)
    {
        return $this->db->table('tbl_cart')
            ->selectSum('total_price', 'sums')
            ->where('user_id', $userId)
            ->get()->getRow();
    }

    public function order_history(int $userId)
    {
        return $this->db->table('tbl_order')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get()->getResult();
    }

    // ── Admin Order Listing (with search by user name/email/mobile) ──────────

    private function _orderListBuilder(string $searchText)
    {
        $builder = $this->db->table('tbl_order')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left');
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('tbl_users.name',   $searchText)
                ->orLike('tbl_users.email',  $searchText)
                ->orLike('tbl_users.mobile', $searchText)
                ->groupEnd();
        }
        return $builder;
    }

    public function order_list_count(string $searchText = ''): int
    {
        return $this->_orderListBuilder($searchText)
            ->select('tbl_order.id')
            ->get()->getNumRows();
    }

    public function order_list(string $searchText, int $limit, int $offset)
    {
        return $this->_orderListBuilder($searchText)
            ->select('tbl_order.*, tbl_users.name as uname, tbl_users.email')
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Results ────────────────────────────────────────────────────────────

    public function result_list(?int $webId = null, ?string $date = null, int $limit = 0)
    {
        $builder = $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_webs.name')
            ->join('tbl_webs', 'tbl_webs.id = CAST(JSON_UNQUOTE(JSON_EXTRACT(tbl_order.tickets, "$[0].web_id")) AS UNSIGNED)', 'left')
            ->where('paid_status', 'PAID')
            ->orderBy('tbl_order.id', 'desc');
        if (!empty($webId)) {
            $builder->where('CAST(JSON_UNQUOTE(JSON_EXTRACT(tbl_order.tickets, "$[0].web_id")) AS UNSIGNED)', $webId);
        }
        if (!empty($date)) {
            $builder->where('DATE(tbl_order.createdAt)', $date);
        }
        if ($limit > 0) {
            $builder->limit($limit);
        }
        return $builder->get()->getResult();
    }

    // ── Dashboard Stats ────────────────────────────────────────────────────

    public function total_tickets_sold(): int
    {
        return $this->db->table('tbl_order')
            ->where('paid_status', 'PAID')
            ->get()->getNumRows();
    }

    public function total_revenue(): float
    {
        $row = $this->db->table('tbl_order')
            ->selectSum('total_price', 'total')
            ->where('paid_status', 'PAID')
            ->get()->getRow();
        return $row ? (float) $row->total : 0.0;
    }

    public function today_revenue(): float
    {
        $row = $this->db->table('tbl_order')
            ->selectSum('total_price', 'total')
            ->where('paid_status', 'PAID')
            ->where('DATE(createdAt)', date('Y-m-d'))
            ->get()->getRow();
        return $row ? (float) $row->total : 0.0;
    }

    public function today_orders(): int
    {
        return $this->db->table('tbl_order')
            ->where('paid_status', 'PAID')
            ->where('DATE(createdAt)', date('Y-m-d'))
            ->get()->getNumRows();
    }

    public function recent_transactions(int $limit = 8)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.id, tbl_order.total_price, tbl_order.paid_status, tbl_order.transaction_id, tbl_order.createdAt, tbl_order.tickets, tbl_users.name AS user_name, tbl_users.email AS user_email')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->where('tbl_order.paid_status', 'PAID')
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit)
            ->get()->getResult();
    }

    // ── Transactions (with filters) ────────────────────────────────────────

    private function _txnBuilder(?int $webId, ?string $dateFrom, ?string $dateTo, ?string $status, string $search = '')
    {
        $builder = $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name AS user_name, tbl_users.email AS user_email, tbl_webs.name AS web_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left');

        if ($webId) {
            $builder->where('tbl_order.web_id', $webId);
        }
        if ($dateFrom) {
            $builder->where('DATE(tbl_order.createdAt) >=', $dateFrom);
        }
        if ($dateTo) {
            $builder->where('DATE(tbl_order.createdAt) <=', $dateTo);
        }
        if ($status !== null && $status !== '') {
            $builder->where('tbl_order.paid_status', $status);
        }
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name',  $search)
                ->orLike('tbl_users.email', $search)
                ->orLike('tbl_order.transaction_id', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function txn_count(?int $webId, ?string $dateFrom, ?string $dateTo, ?string $status, string $search = ''): int
    {
        return $this->_txnBuilder($webId, $dateFrom, $dateTo, $status, $search)
            ->countAllResults();
    }

    public function txn_list(?int $webId, ?string $dateFrom, ?string $dateTo, ?string $status, string $search, int $limit, int $offset)
    {
        return $this->_txnBuilder($webId, $dateFrom, $dateTo, $status, $search)
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Tickets (admin management) ─────────────────────────────────────────

    public function ticket_list(string $search, ?string $status, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name AS user_name, tbl_users.email AS user_email, tbl_webs.name AS web_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_order.transaction_id', $search)
                ->orLike('CAST(tbl_order.id AS CHAR)', $search)
                ->groupEnd();
        }
        if ($status !== null && $status !== '') {
            $builder->where('tbl_order.paid_status', $status);
        }
        return $builder->orderBy('tbl_order.id', 'DESC')->limit($limit, $offset)->get()->getResult();
    }

    public function ticket_list_count(string $search, ?string $status): int
    {
        $builder = $this->db->table('tbl_order')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_order.transaction_id', $search)
                ->orLike('CAST(tbl_order.id AS CHAR)', $search)
                ->groupEnd();
        }
        if ($status !== null && $status !== '') {
            $builder->where('tbl_order.paid_status', $status);
        }
        return $builder->countAllResults();
    }

    public function cancel_ticket(int $orderId): void
    {
        $this->db->table('tbl_order')->where('id', $orderId)->update(['paid_status' => 'CANCELLED']);
    }

    // ── User-specific order listing ───────────────────────────────────────────

    private function _userOrderBuilder(int $userId, string $search)
    {
        $builder = $this->db->table('tbl_order')
            ->where('tbl_order.user_id', $userId);
        if (!empty($search)) {
            $builder->groupStart()
                ->like('CAST(tbl_order.id AS CHAR)', $search)
                ->orLike('tbl_order.transaction_id', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function user_orders_count(int $userId, string $search = ''): int
    {
        return $this->_userOrderBuilder($userId, $search)->select('tbl_order.id')->get()->getNumRows();
    }

    public function user_orders_list(int $userId, string $search, int $limit, int $offset)
    {
        return $this->_userOrderBuilder($userId, $search)
            ->select('tbl_order.*')
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Reports ────────────────────────────────────────────────────────────

    public function report_daily(string $date)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name AS user_name, tbl_users.email AS user_email, tbl_webs.name AS web_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left')
            ->where('tbl_order.paid_status', 'PAID')
            ->where('DATE(tbl_order.createdAt)', $date)
            ->orderBy('tbl_order.id', 'DESC')
            ->get()->getResult();
    }

    public function report_event(int $webId)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name AS user_name, tbl_users.email AS user_email, tbl_webs.name AS web_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left')
            ->where('tbl_order.paid_status', 'PAID')
            ->where('tbl_order.web_id', $webId)
            ->orderBy('tbl_order.id', 'DESC')
            ->get()->getResult();
    }

    public function report_monthly(int $year, int $month)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name AS user_name, tbl_users.email AS user_email, tbl_webs.name AS web_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left')
            ->where('tbl_order.paid_status', 'PAID')
            ->where('YEAR(tbl_order.createdAt)', $year)
            ->where('MONTH(tbl_order.createdAt)', $month)
            ->orderBy('tbl_order.id', 'DESC')
            ->get()->getResult();
    }
}
