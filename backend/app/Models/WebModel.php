<?php

namespace App\Models;

use CodeIgniter\Model;

class WebModel extends Model
{
    // ── Games / Webs ─────────────────────────────────────────────────────────

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

    // ── Lottery ────────────────────────────────────────────────────────────

    public function lottery_web(string $limit = '')
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
            ->select('tbl_order.*')
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
}
