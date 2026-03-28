<?php

namespace App\Models;

use CodeIgniter\Model;

class WebModel extends Model
{
    // ── Games / Webs ─────────────────────────────────────────────────────────

    public function weblist_count(string $search = ''): int
    {
        $builder = $this->db->table('tbl_webs');
        if (!empty($search)) {
            $builder->like('name', $search);
        }
        return $builder->countAllResults();
    }

    public function weblist_data(string $search = '', int $length = 10, int $start = 0)
    {
        $builder = $this->db->table('tbl_webs')
            ->select('id, name, status, createdDtm')
            ->orderBy('id', 'DESC');
        if (!empty($search)) {
            $builder->like('name', $search);
        }
        return $builder->limit($length, $start)->get()->getResult();
    }

    public function get_allweb(string $searchText = '', string $limit = '')
    {
        $builder = $this->db->table('tbl_webs')->select('tbl_webs.*');
        if (!empty($searchText)) {
            $builder->like('tbl_webs.name', $searchText);
        }
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function get_allfaq(string $searchText = '', string $limit = '')
    {
        $builder = $this->db->table('tbl_faqs')->select('tbl_faqs.*')->orderBy('id', 'DESC');
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('tbl_faqs.question', $searchText)
                ->orLike('tbl_faqs.answer', $searchText)
                ->groupEnd();
        }
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function home_web(string $limit = '')
    {
        $sub = '(SELECT date FROM `tbl_dates` WHERE web_id=tbl_webs.id AND date_con > "' . date('Y-m-d ') . '" ORDER BY date ASC LIMIT 1)';
        $builder = $this->db->table('tbl_webs')
            ->select("tbl_webs.*, tbl_ranges.result_date, tbl_ranges.price, tbl_ranges.heading, tbl_ranges.logo, tbl_ranges.logo2, tbl_ranges.jackpot, $sub as date")
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('status', 'Active')
            ->orderBy('tbl_ranges.priority');
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function addNewWeb(array $data): int
    {
        $this->db->transStart();
        $this->db->table('tbl_webs')->insert($data);
        $insertId = $this->db->insertID();
        $this->db->transComplete();
        return $insertId;
    }

    public function getWebInfo(int $id)
    {
        return $this->db->table('tbl_webs')->where('id', $id)->get()->getRow();
    }

    public function getcommon()
    {
        return $this->db->table('common')->get()->getRow();
    }

    public function getfaq(int $id)
    {
        return $this->db->table('tbl_faqs')->where('id', $id)->get()->getRow();
    }

    public function getRangeAvailability(int $ticket, int $web_id): bool
    {
        $range = $this->getrangeInfo($web_id);
        if (!$range || empty($range->rangeStart)) {
            return false;
        }
        return isTicketInRange($ticket, $range->rangeStart);
    }

    public function getrangeInfo(int $id)
    {
        return $this->db->table('tbl_ranges')->where('web_id', $id)->get()->getRow();
    }

    public function getTierInfo(int $id)
    {
        return $this->db->table('tbl_tier')->where('web_id', $id)->get()->getRow();
    }

    public function tier(int $id)
    {
        return $this->getTierInfo($id);
    }

    public function getdateInfo(int $id)
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $id)
            ->where('date_con >', date('Y-m-d ' . TIMEVAL))
            ->orderBy('date')
            ->get()->getResult();
    }

    public function editWebsite(array $data, int $id): bool
    {
        $this->db->table('tbl_webs')->where('id', $id)->update($data);
        return true;
    }

    public function deleteWeb(string $table, int $id): int
    {
        $this->db->table($table)->where('id', $id)->delete();
        return $this->db->affectedRows();
    }

    // ── Pages / FAQ / Email ──────────────────────────────────────────────────

    public function page_detail(string $type)
    {
        return $this->db->table('tbl_pages')->where('type', $type)->get()->getRow();
    }

    public function page_list()
    {
        return $this->db->table('tbl_pages')->get()->getResult();
    }

    public function faq(int $limit = 0)
    {
        $builder = $this->db->table('tbl_faqs')->orderBy('id', 'DESC');
        if ($limit > 0) {
            $builder->limit($limit);
        }
        return $builder->get()->getResult();
    }

    public function email_found(string $type)
    {
        return $this->db->table('tbl_emails')->where('type', $type)->get()->getRow();
    }

    // ── Cart ────────────────────────────────────────────────────────────────

    public function checkIfTicketAlreadyPresent(array $ticket): bool
    {
        $count = $this->db->table('tbl_cart')
            ->where('web_id',     $ticket['web_id'])
            ->where('user_id',    $ticket['user_id'])
            ->where('ticket_no',  $ticket['ticket_no'])
            ->where('total_price',$ticket['total_price'])
            ->get()->getNumRows();
        return $count > 0;
    }

    public function insert_cart(array $data): int
    {
        $this->db->table('tbl_cart')->insertBatch($data);
        return $this->db->insertID();
    }

    public function insert_date(string $table, array $data): int
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function editWeb_all(string $table, array $data, int $id): bool
    {
        $this->db->table($table)->where('id', $id)->update($data);
        return true;
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
            ->where('user_id',  $userId)
            ->where('web_id',   $web_id)
            ->where('ticket_no', $ticket_no)
            ->update($data);
    }

    public function get_ticket_availability(int $ticket, int $web_id)
    {
        return $this->db->table('tbl_cart')
            ->where('web_id',     $web_id)
            ->where('ticket_no',  $ticket)
            ->where('paid_status', 1)
            ->orderBy('id')
            ->get()->getResult();
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
            ->where('user_id',  $userId)
            ->where('ticket_no', $ticket_no)
            ->where('web_id',   $web_id)
            ->delete();
        return $this->db->affectedRows();
    }

    // ── Dates ────────────────────────────────────────────────────────────────

    public function count_date(int $web_id): int
    {
        return $this->db->table('tbl_dates')->where('web_id', $web_id)->get()->getNumRows();
    }

    public function list_date(int $web_id, int $limit, int $offset)
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $web_id)
            ->orderBy('date', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function date_exist(int $web_id, string $date): int
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $web_id)
            ->where('date', $date)
            ->get()->getNumRows();
    }

    public function getallWebInfo(string $table, int $id)
    {
        return $this->db->table($table)->where('id', $id)->get()->getRow();
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
            ->where('tickets',    $tickets)
            ->where('user_id',    $userId)
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
            ->where('tbl_cart.user_id',   $userId)
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

    public function winner_history(int $userId)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_webs.name')
            ->join('tbl_webs', 'tbl_webs.id = CAST(JSON_UNQUOTE(JSON_EXTRACT(tbl_order.tickets, "$[0].web_id")) AS UNSIGNED)', 'left')
            ->where('user_id', $userId)
            ->where('prize !=', '')
            ->orderBy('id', 'desc')
            ->get()->getResult();
    }

    public function winner_amountf(int $userId)
    {
        return $this->db->table('tbl_order')
            ->selectSum('prize', 'sum')
            ->where('user_id', $userId)
            ->get()->getRow();
    }

    // ── event ────────────────────────────────────────────────────────────

    public function event_web(string $limit = '')
    {
        $sub = '(SELECT date FROM `tbl_dates` WHERE web_id=tbl_webs.id AND date_con > "' . date('Y-m-d ') . '" ORDER BY date ASC LIMIT 1)';
        $builder = $this->db->table('tbl_webs')
            ->select("tbl_webs.*, tbl_ranges.result_date, tbl_ranges.price, tbl_ranges.heading, tbl_ranges.logo, tbl_ranges.logo2, tbl_ranges.jackpot, $sub as date")
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('status', 'Active')
            ->orderBy('tbl_ranges.priority');
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    // ── Wallet ────────────────────────────────────────────────────────────

    public function wallet(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_wallet')
            ->select('money, id')
            ->where('user_id', $userId)
            ->get()->getRow();
    }

    public function wallet_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_wallet_history')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function refund_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_refund')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function withdrawl_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_withdrawl')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function transfer_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_transfer')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
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

    // ── Contact ────────────────────────────────────────────────────────────

    public function count_contact(): int
    {
        return $this->db->table('tbl_contact')->get()->getNumRows();
    }

    public function contact_ls(int $limit, int $offset)
    {
        return $this->db->table('tbl_contact')
            ->orderBy('id', 'DESC')
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

    // ── Country ────────────────────────────────────────────────────────────

    public function getallcountry()
    {
        return $this->db->table('tbl_country')->orderBy('name')->get()->getResult();
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

    public function upcoming_events(int $limit = 5)
    {
        return $this->db->table('tbl_webs')
            ->select('tbl_webs.id, tbl_webs.name, tbl_ranges.price, tbl_ranges.jackpot, tbl_ranges.result_date')
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id', 'left')
            ->where('tbl_webs.status', 'Active')
            ->where('tbl_ranges.result_date >=', date('Y-m-d'))
            ->orderBy('tbl_ranges.result_date', 'ASC')
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

    // ── Admin Wallet History ───────────────────────────────────────────────

    private function _walletListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_wallet_history')
            ->join('tbl_users', 'tbl_users.userId = tbl_wallet_history.user_id', 'left')
            ->select('tbl_wallet_history.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_wallet_count(string $search = ''): int
    {
        return $this->_walletListBuilder($search)->select('tbl_wallet_history.id')->get()->getNumRows();
    }

    public function admin_wallet_list(string $search, int $limit, int $offset)
    {
        return $this->_walletListBuilder($search)
            ->orderBy('tbl_wallet_history.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Admin Winners ──────────────────────────────────────────────────────

    private function _winnerListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name as uname, tbl_webs.name as game_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left')
            ->where('tbl_order.prize !=', '')
            ->where('tbl_order.prize IS NOT NULL', null, false);
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_winner_count(string $search = ''): int
    {
        return $this->_winnerListBuilder($search)->select('tbl_order.id')->get()->getNumRows();
    }

    public function admin_winner_list(string $search, int $limit, int $offset)
    {
        return $this->_winnerListBuilder($search)
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function admin_winner_total(): object
    {
        $row = $this->db->table('tbl_order')
            ->selectSum('prize', 'sum')
            ->where('prize !=', '')
            ->where('prize IS NOT NULL', null, false)
            ->get()->getRow();
        return $row ?? (object)['sum' => 0];
    }

    // ── Admin Refund ───────────────────────────────────────────────────────

    private function _refundListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_refund')
            ->join('tbl_users', 'tbl_users.userId = tbl_refund.user_id', 'left')
            ->select('tbl_refund.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_refund_count(string $search = ''): int
    {
        return $this->_refundListBuilder($search)->select('tbl_refund.id')->get()->getNumRows();
    }

    public function admin_refund_list(string $search, int $limit, int $offset)
    {
        return $this->_refundListBuilder($search)
            ->orderBy('tbl_refund.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function refund_process(int $id, string $type, int $userId, float $money): void
    {
        if ($type === 'Refund') {
            $wallet = $this->wallet($userId);
            if ($wallet) {
                $this->db->table('tbl_wallet')->where('user_id', $userId)->update(['money' => (float)$wallet->money + $money]);
            }
            $this->db->table('tbl_refund')->where('id', $id)->update(['status' => '1']);
        } else {
            $this->db->table('tbl_refund')->where('id', $id)->update(['status' => '2']);
        }
    }

    // ── Admin Withdrawal ───────────────────────────────────────────────────

    private function _withdrawlListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_withdrawl')
            ->join('tbl_users', 'tbl_users.userId = tbl_withdrawl.user_id', 'left')
            ->select('tbl_withdrawl.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_withdrawl_count(string $search = ''): int
    {
        return $this->_withdrawlListBuilder($search)->select('tbl_withdrawl.id')->get()->getNumRows();
    }

    public function admin_withdrawl_list(string $search, int $limit, int $offset)
    {
        return $this->_withdrawlListBuilder($search)
            ->orderBy('tbl_withdrawl.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function withdrawl_process(int $id, string $type): void
    {
        $status = ($type === 'Reject') ? '2' : '1';
        $this->db->table('tbl_withdrawl')->where('id', $id)->update(['status' => $status]);
    }

    // ── Admin Transfer ─────────────────────────────────────────────────────

    private function _transferListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_transfer')
            ->join('tbl_users', 'tbl_users.userId = tbl_transfer.user_id', 'left')
            ->select('tbl_transfer.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_transfer_count(string $search = ''): int
    {
        return $this->_transferListBuilder($search)->select('tbl_transfer.id')->get()->getNumRows();
    }

    public function admin_transfer_list(string $search, int $limit, int $offset)
    {
        return $this->_transferListBuilder($search)
            ->orderBy('tbl_transfer.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function transfer_process(int $id, string $type): void
    {
        $status = ($type === 'Reject') ? '2' : '1';
        $this->db->table('tbl_transfer')->where('id', $id)->update(['status' => $status]);
    }
}
