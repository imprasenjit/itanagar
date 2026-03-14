<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Web_model extends CI_Model
{
    /**
     * This function is used to get the user listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */

    public function get_allweb($searchText = '', $limit = "")
    {
        $this->db->select('tbl_webs.*');
        $this->db->from('tbl_webs');

        if (!empty($searchText)) {
            $likeCriteria = "tbl_webs.name  LIKE '%" . $searchText . "%'";
            $this->db->where($likeCriteria);
        }

        if (!empty($limit)) {
            $this->db->limit($limit);
        }
        $result = $this->db->get()->result();
        return $result;
    }

    public function get_allfaq($searchText = '', $limit = "")
    {
        $this->db->select('tbl_faqs.*');
        $this->db->from('tbl_faqs')->order_by("id", "DESC");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_faqs.question  LIKE '%" . $searchText . "%' OR tbl_faqs.answer  LIKE '%" . $searchText . "%' )";
            $this->db->where($likeCriteria);
        }

        if (!empty($limit)) {
            $this->db->limit($limit);
        }
        $result = $this->db->get()->result();
        return $result;
    }


    public function home_web($limit = "")
    {
        $this->db->select('tbl_webs.*,tbl_ranges.result_date,tbl_ranges.price,tbl_ranges.heading,tbl_ranges.logo,tbl_ranges.logo2,tbl_ranges.jackpot,(SELECT date FROM `tbl_dates` where web_id=tbl_webs.id and date_con > "' . date('Y-m-d ') . '" order by date asc limit 1) as date');
        $this->db->from('tbl_webs');
        $this->db->where('status', "Active");
        $this->db->join('tbl_ranges', "tbl_ranges.web_id=tbl_webs.id");
        $this->db->order_by("tbl_ranges.priority");
        if (!empty($limit)) {
            $this->db->limit($limit);
        }
        $result = $this->db->get()->result();
        return $result;
    }



    function addNewWeb($userInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_webs', $userInfo);

        $insert_id = $this->db->insert_id();

        $this->db->trans_complete();

        return $insert_id;
    }

    function getWebInfo($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_webs');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    function getcommon()
    {
        $this->db->select('*');
        $this->db->from('common');
        $query = $this->db->get();
        return $query->row();
    }

    function getfaq($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_faqs');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    function getRangeAvailability($ticket, $web_id)
    {
        $this->db->select('*');
        $this->db->from('tbl_ranges');
        $this->db->where('rangeStart <', $ticket);
        // $this->db->where('rangeEnd >', $ticket);
        $this->db->where('web_id', $web_id);
        $query = $this->db->get();
        return $query->result();
    }
    function getrangeInfo($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_ranges');
        $this->db->where('web_id', $id);
        $query = $this->db->get();
        return $query->row();
    }


    function tier($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_tier');
        $this->db->where('web_id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    function getdateInfo($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_dates');
        $this->db->where('web_id', $id);
        $this->db->where("date_con > ", date('Y-m-d ' . TIMEVAL));
        $this->db->order_by("date");
        $query = $this->db->get();
        return $query->result();
    }

    function editWebsite($userInfo, $userId)
    {
        $this->db->where('id', $userId);
        $this->db->update('tbl_webs', $userInfo);
        return TRUE;
    }


    function deleteWeb($table, $userId)
    {
        $this->db->where('id', $userId);
        $this->db->delete($table);
        return $this->db->affected_rows();
    }

    function page_detail($type)
    {
        $this->db->select('*');
        $this->db->from('tbl_pages');
        $this->db->where('type', $type);
        $query = $this->db->get();
        return $query->row();
    }

    function page_list()
    {
        $this->db->select('*');
        $this->db->from('tbl_pages');
        $query = $this->db->get();
        return $query->result();
    }

    function faq($limit = null)
    {
        $this->db->select('*');
        $this->db->from('tbl_faqs');
        $this->db->order_by('id', 'DESC');
        if ($limit != null) {
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query->result();
    }

    function email_found($type)
    {
        $this->db->select('*');
        $this->db->from('tbl_emails')->where("type", $type);
        $query = $this->db->get();
        return $query->row();
    }
    function checkIfTicketAlreadyPresent($ticket)
    {
        $this->db->select("*");
        $this->db->from("tbl_cart");
        $this->db->where("web_id", $ticket["web_id"]); // will check row by column name in database
        $this->db->where("user_id", $ticket["user_id"]); // will check row by column name in database
        $this->db->where("ticket_no", $ticket["ticket_no"]); // will check row by column name in database
        $this->db->where("total_price", $ticket["total_price"]); // will check row by column name in database
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    function insert_cart($data)
    {
        $this->db->insert_batch('tbl_cart', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    function insert_date($tbl_name, $data)
    {
        $this->db->insert($tbl_name, $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function editWeb_all($table, $userInfo, $userId)
    {
        $this->db->where('id', $userId);
        $this->db->update($table, $userInfo);
        return TRUE;
    }

    function count_date($web_id)
    {
        $this->db->select('*');
        $this->db->from('tbl_dates');
        $this->db->where('tbl_dates.web_id', $web_id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    function list_date($web_id, $page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_dates');
        $this->db->where('tbl_dates.web_id', $web_id);
        $this->db->order_by('tbl_dates.date', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function date_exist($web_id, $date)
    {
        $this->db->select('*');
        $this->db->from('tbl_dates');
        $this->db->where('tbl_dates.web_id', $web_id);
        $this->db->where('tbl_dates.date', $date);
        $query = $this->db->get();
        $result = $query->num_rows();
        return $result;
    }

    function getallWebInfo($table, $id)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    function count_contact()
    {
        $this->db->select('*');
        $this->db->from('tbl_contact');
        $query = $this->db->get();
        return $query->num_rows();
    }

    function contact_ls($page, $segment)
    {
        $this->db->select('*');
        $this->db->from('tbl_contact');
        $this->db->order_by('tbl_contact.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    function get_ticket_availability($ticket, $web_id)
    {
        $this->db->select('*');
        $this->db->from('tbl_cart');
        $this->db->where('web_id', $web_id);
        $this->db->where('ticket_no', $ticket);
        $this->db->where('paid_status', 1);
        $this->db->order_by('id');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    function cart_data($user_id)
    {
        $this->db->select('tbl_cart.*,tbl_webs.name');
        $this->db->from('tbl_cart');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_cart.web_id");
        $this->db->where('user_id', $user_id);
        $this->db->where('paid_status', 0);
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    function update_cart_data($user_id, $web_id, $ticket_no, $data)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('web_id', $web_id);
        $this->db->where('ticket_no', $ticket_no);
        $this->db->update('tbl_cart', $data);
    }
    function get_order_by_id($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_order');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
    function get_order_by_orderId($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_order');
        $this->db->where('razorpay_order_id', $id);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
    function get_order($user_id, $tickets, $total_price)
    {
        $this->db->select('id');
        $this->db->from('tbl_order');
        $this->db->where('tickets', $tickets);
        $this->db->where('user_id', $user_id);
        $this->db->where('total_price', $total_price);
        $this->db->where('paid_status!=', 'RELEASED');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }
    function update_order($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('tbl_order', $data);
    }
    function update_order_by_orderId($id, $data)
    {
        $this->db->where('razorpay_order_id', $id);
        $this->db->update('tbl_order', $data);
    }

    function insert_order($data)
    {
        $this->db->insert("tbl_order", $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    function order_data($user_id)
    {
        $this->db->select('tbl_cart.total_price,tbl_cart.web_id,tbl_cart.user_id,tbl_webs.name,tbl_cart.ticket_no');
        $this->db->from('tbl_cart');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_cart.web_id");
        $this->db->where('tbl_cart.user_id', $user_id);
        $this->db->where('tbl_cart.paid_status', 0);
        // $this->db->group_by('tbl_cart.web_id');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function total_pay($user_id)
    {
        $this->db->select('sum(tbl_cart.total_price) as sums');
        $this->db->from('tbl_cart');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    function clear_cart($userId)
    {
        $this->db->where('user_id', $userId);
        $this->db->delete("tbl_cart");
        return $this->db->affected_rows();
    }
    function clear_cart_data($userId, $ticket_no, $web_id)
    {
        $this->db->where('user_id', $userId);
        $this->db->where('ticket_no', $ticket_no);
        $this->db->where('web_id', $web_id);
        $this->db->delete("tbl_cart");
        return $this->db->affected_rows();
    }


    public function lottery_web($limit = "")
    {
        // echo date('Y-m-d '.TIMEVAL). "  ".date("Y-m-d H:i:s"); 
        // $this->db->select('tbl_webs.*,tbl_ranges.price,tbl_ranges.heading,tbl_ranges.logo,tbl_ranges.quantity,tbl_ranges.jackpot,(SELECT date FROM `tbl_dates` where web_id=tbl_webs.id and date_con > "'.date('Y-m-d '.TIMEVAL).'" order by date asc limit 1) as date');
        $this->db->select('tbl_webs.*,tbl_ranges.result_date,tbl_ranges.price,tbl_ranges.heading,tbl_ranges.logo,tbl_ranges.logo2,tbl_ranges.jackpot,(SELECT date FROM `tbl_dates` where web_id=tbl_webs.id and date_con > "' . date('Y-m-d ') . '" order by date asc limit 1) as date');
        $this->db->from('tbl_webs');
        $this->db->where('status', "Active");
        $this->db->join('tbl_ranges', "tbl_ranges.web_id=tbl_webs.id");
        $this->db->order_by("tbl_ranges.priority");
        if (!empty($limit)) {
            $this->db->limit($limit);
        }
        $result = $this->db->get()->result();

        // echo TIMEVAL;

        // print_r($this->db->last_query());
        // die();
        return $result;
    }


    function wallet()
    {
        $this->db->select('money,id');
        $this->db->from('tbl_wallet');
        $this->db->where('user_id', $this->session->userdata('userId'));
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }
    function wallet_history()
    {
        $this->db->select('*');
        $this->db->from('tbl_wallet_history');
        $this->db->where('user_id', $this->session->userdata('userId'));
        $this->db->order_by("id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }


    function refund_history()
    {
        $this->db->select('*');
        $this->db->from('tbl_refund');
        $this->db->where('user_id', $this->session->userdata('userId'));
        $this->db->order_by("id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }


    function withdrawl_history()
    {
        $this->db->select('*');
        $this->db->from('tbl_withdrawl');
        $this->db->where('user_id', $this->session->userdata('userId'));
        $this->db->order_by("id", "DESC");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }


    function up_cart($c_id, $user_id)
    {
        $update = array("user_id" => $user_id);
        $this->db->where('user_id', $c_id);
        $this->db->update('tbl_cart', $update);
        return TRUE;
    }
    function order_history($user_id)
    {
        $this->db->select('tbl_order.*');
        $this->db->from('tbl_order');
        $this->db->where('user_id', $user_id)->order_by('id', "desc");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function winner_history($user_id)
    {
        $this->db->select('tbl_order.*,tbl_webs.name');
        $this->db->from('tbl_order');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->where('user_id', $user_id)->where("prize!=", "")->order_by('id', "desc");
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    function winner_amountf($user_id)
    {
        $this->db->select('sum(prize) as sum');
        $this->db->from('tbl_order');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->where('user_id', $user_id)->where("prize!=", "");
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    function count_order($searchText)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname');
        $this->db->from('tbl_order');
        // $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.mobile  LIKE '%" . $searchText . "%' OR tbl_order.razorpay_order_id LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }

        $query = $this->db->get();
        return $query->num_rows();
    }

    function count_winner($searchText)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname,tbl_webs.name as name');
        $this->db->from('tbl_order');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");
        $this->db->where("prize!=", "");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }

        $query = $this->db->get();
        return $query->num_rows();
    }

    function winner_amount($searchText)
    {
        $this->db->select('SUM(prize) as sum');
        $this->db->from('tbl_order');
        $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");
        $this->db->where("prize!=", "");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }

        $query = $this->db->get();
        return $query->row();
    }

    function winner_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname,tbl_webs.name as name');
        $this->db->from('tbl_order');

        $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");
        $this->db->where("prize!=", "");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_order.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    function getFailedOrders()
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname');
        $this->db->from('tbl_order');
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");
        $this->db->where('tbl_order.order_status', 0);
        $this->db->order_by('tbl_order.id', 'DESC');
        $this->db->where('tbl_order.createdAt < date_sub(now(), interval 30 minute) ');
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }
    function release_order($tickes, $user_id, $custom_user_id)
    {
        foreach ($tickes as $key => $value) {
            $data = array(
                'paid_status' => 0
            );
            $this->db->where('user_id', $user_id);
            $this->db->or_where('user_id', $custom_user_id);
            $this->db->where('web_id', $value->web_id);
            $this->db->where('ticket_no', $value->ticket_no);
            $this->db->update('tbl_cart', $data);
        }
    }
    function order_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname');
        $this->db->from('tbl_order');

        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");

        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.mobile  LIKE '%" . $searchText . "%' OR tbl_order.razorpay_order_id LIKE '%" . $searchText . "%')";

            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_order.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }
    function count_wallet($searchText)
    {
        $this->db->select('tbl_wallet_history.*,tbl_users.name as uname');
        $this->db->from('tbl_wallet_history');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_wallet_history.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    function count_refund($searchText)
    {
        $this->db->select('tbl_refund.*,tbl_users.name as uname');
        $this->db->from('tbl_refund');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_refund.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    function count_withdrawl($searchText)
    {
        $this->db->select('tbl_withdrawl.*,tbl_users.name as uname');
        $this->db->from('tbl_withdrawl');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_withdrawl.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }


    function count_transfer($searchText)
    {
        $this->db->select('tbl_transfer.*,tbl_users.name as uname');
        $this->db->from('tbl_transfer');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_transfer.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    function wallet_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_wallet_history.*,tbl_users.name as uname');
        $this->db->from('tbl_wallet_history');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_wallet_history.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_wallet_history.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    function refund_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_refund.*,tbl_users.name as uname');
        $this->db->from('tbl_refund');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_refund.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_refund.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }


    function withdrawl_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_withdrawl.*,tbl_users.name as uname,tbl_users.paypal as paypale');
        $this->db->from('tbl_withdrawl');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_withdrawl.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_withdrawl.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }



    function transfer_ls($page, $segment, $searchText)
    {
        $this->db->select('tbl_transfer.*,tbl_users.name as uname,tbl_users.paypal as paypale');
        $this->db->from('tbl_transfer');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_transfer.user_id");
        if (!empty($searchText)) {
            $likeCriteria = "(tbl_users.name  LIKE '%" . $searchText . "%')";
            $this->db->where($likeCriteria);
        }
        $this->db->order_by('tbl_transfer.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    //Winner Code Rk
    function winner_last($id, $data)
    {
        $this->db->order_by('drawing.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get_where("drawing", array("website_id" => $id));
        $result = $query->row();

        // Next Date Add
        $next_date = $result->next_date;

        $datearray = array("web_id" => $id, "date" => $next_date);
        $re2 = $this->db->select("*")->get_where("tbl_dates", $datearray)->result();
        if (count($re2) == 0) {
            $datearray['date_con'] = $next_date . " " . TIMEVAL;
            $this->db->insert("tbl_dates", $datearray);
        }
        $next_price = $result->next_price;
        $this->db->where("web_id", $id)->update("tbl_ranges", array("jackpot" => $next_price));

        // Winner 
        $white_ballar = array($result->whiteball1, $result->whiteball2, $result->whiteball3, $result->whiteball4, $result->whiteball5);

        $yellow_ballar = array($result->megaball);

        if ($result->whiteball6 != 0) {
            $white_ballar[] = $result->whiteball6;
        }
        if ($result->megaball1 != 0) {
            $yellow_ballar[] = $result->megaball1;
        }

        $latest_date = $result->latest_date;
        $findresult = array("web_id" => $id, "date" => $latest_date);

        $order = $this->db->select("tbl_order.*,tbl_users.name,tbl_users.email")->join("tbl_users", "tbl_users.userId= tbl_order.user_id")->get_where("tbl_order", $findresult)->result();


        $white_count = count($white_ballar);
        $yellow_count = count($yellow_ballar);

        echo "<pre>" . $latest_date;

        print_r($white_ballar);
        print_r($yellow_ballar);

        if (count($order) > 0) {
            foreach ($order as $or) {
                $wc = 0;
                if (in_array($or->white1, $white_ballar)) {
                    $wc++;
                }
                if (in_array($or->white2, $white_ballar)) {
                    $wc++;
                }

                if (in_array($or->white3, $white_ballar)) {
                    $wc++;
                }
                if (in_array($or->white4, $white_ballar)) {
                    $wc++;
                }
                if (in_array($or->white5, $white_ballar)) {
                    $wc++;
                }
                if ($white_count == 6  && in_array($or->white5, $white_ballar)) {
                    $wc++;
                }
                $yc = 0;
                if (in_array($or->yellow1, $yellow_ballar)) {
                    $yc++;
                }
                if ($yellow_count == 2 && in_array($or->yellow2, $yellow_ballar)) {
                    $yc++;
                }


                if ($yc > 0 || $wc > 0) {
                    $findp = array(
                        "website_id" => $id,
                        "whiteball" => $wc,
                        "megaball" => $yc,
                        "latest_date" => $latest_date
                    );
                    $prize = $this->db->select("is_jackpot,price_amount")->get_where("winner_history", $findp)->row();
                    if ($prize) {
                        $this->db->where("id", $or->id)->update("tbl_order", array(
                            "is_jackpot" => $prize->is_jackpot,
                            "pattern" => $yc . " " . $wc,
                            "prize" => $prize->price_amount,
                        ));

                        // Email and post api code here
                    }
                }
            }
        }
    }

    public function getalldates($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_dates');
        $this->db->where('web_id', $id);
        $this->db->where('date <', date("Y-m-d"));
        $this->db->order_by("date");
        $query = $this->db->get();
        return $query->result();
    }


    public function getalldates_result($id)
    {
        $this->db->select('latest_date as date,website_id');
        $this->db->from('winner_history');
        $this->db->where('website_id', $id);
        $this->db->where('latest_date <', date("Y-m-d"));
        $this->db->group_by("latest_date")->order_by("latest_date", "DESC");
        $query = $this->db->get();
        // echo "<pre>";
        // print_r($query);
        // die();
        return $query->result();
    }


    public function drawing_detail($web_id, $date)
    {
        $this->db->select('*');
        $this->db->from('drawing');
        $this->db->where('website_id', $web_id);
        $this->db->where('latest_date', $date);
        $query = $this->db->get();
        return $query->row();
    }

    public function winner_detail()
    {
        $this->db->select('*');
        $this->db->from('winner_history');
        // $this->db->where('website_id', $web_id);
        // $this->db->where('latest_date', $date);
        $this->db->order_by("latest_date DESC");
        $query = $this->db->get();

        return $query->result();
    }


    function pattern_exist()
    {
        $this->db->select('*');
        $this->db->from('tbl_tier');
        $this->db->where('tbl_tier.web_id', $this->input->post('web_id'));
        $this->db->where('tbl_tier.white', $this->input->post('white'));
        $this->db->where('tbl_tier.mega', $this->input->post('yellow'));
        $query = $this->db->get();
        $result = $query->num_rows();
        return $result;
    }

    function getTierInfo($id)
    {
        $this->db->select('*');
        $this->db->from('tbl_tier');
        $this->db->where('web_id', $id);
        $this->db->order_by('white DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function getallcountry()
    {
        $this->db->select('*');
        $this->db->from('country');

        $this->db->order_by('name ASC');
        $query = $this->db->get();
        return $query->result();
    }


    function count_wallet_single($userId)
    {
        $this->db->select('tbl_wallet_history.*,tbl_users.name as uname');
        $this->db->from('tbl_wallet_history');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_wallet_history.user_id");
        $likeCriteria = "(tbl_wallet_history.user_id = '" . $userId . "')";
        $this->db->where($likeCriteria);
        $query = $this->db->get();


        return $query->num_rows();
    }

    function wallet_ls_single($page, $segment, $userId)
    {
        $this->db->select('tbl_wallet_history.*,tbl_users.name as uname');
        $this->db->from('tbl_wallet_history');
        $this->db->join("tbl_users", "tbl_users.userId = tbl_wallet_history.user_id");

        $likeCriteria = "(tbl_wallet_history.user_id = '" . $userId . "')";
        $this->db->where($likeCriteria);
        $this->db->order_by('tbl_wallet_history.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();
        //    print_r($this->db->last_query());
        return $result;
    }

    function userInfo($id)
    {
        $this->db->select('tbl_users.*');
        $this->db->from('tbl_users');
        $likeCriteria = "(tbl_users.userId = '" . $id . "')";
        $this->db->where($likeCriteria);
        $query = $this->db->get();
        $result = $query->row();


        //    print_r($this->db->last_query());
        return $result;
    }

    function count_order_single($userId)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname');
        $this->db->from('tbl_order');
        // $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");

        $likeCriteria = "(tbl_order.user_id = '" . $userId . "')";
        $this->db->where($likeCriteria);
        $query = $this->db->get();
        return $query->num_rows();
    }

    function order_ls_single($page, $segment, $userId)
    {
        $this->db->select('tbl_order.*,tbl_users.name as uname');
        $this->db->from('tbl_order');

        // $this->db->join("tbl_webs", "tbl_webs.id=tbl_order.web_id");
        $this->db->join("tbl_users", "tbl_users.userId=tbl_order.user_id");

        $likeCriteria = "(tbl_order.user_id = '" . $userId . "')";
        $this->db->where($likeCriteria);
        $this->db->order_by('tbl_order.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    function wallet_user($userid)
    {
        $this->db->select('money,id');
        $this->db->from('tbl_wallet');
        $this->db->where('user_id', $userid);
        $query = $this->db->get();
        $result = $query->row();
        return $result;
    }

    // ── Per-user history methods (for API controller) ──────────────────────────

    function wallet_history_user($userid)
    {
        $this->db->select('*');
        $this->db->from('tbl_wallet_history');
        $this->db->where('user_id', $userid);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function refund_history_user($userid)
    {
        $this->db->select('*');
        $this->db->from('tbl_refund');
        $this->db->where('user_id', $userid);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function withdrawl_history_user($userid)
    {
        $this->db->select('*');
        $this->db->from('tbl_withdrawl');
        $this->db->where('user_id', $userid);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function transfer_history_user($userid)
    {
        $this->db->select('*');
        $this->db->from('tbl_transfer');
        $this->db->where('user_id', $userid);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function result_list($web_id = null, $date = null)
    {
        $this->db->select('tbl_order.*,tbl_webs.name as game_name,tbl_ranges.logo,tbl_ranges.logo2');
        $this->db->from('tbl_order');
        $this->db->join('tbl_webs', 'tbl_webs.id=tbl_order.web_id');
        $this->db->join('tbl_ranges', 'tbl_ranges.web_id=tbl_order.web_id');
        $this->db->where('tbl_order.order_status', 1);
        $this->db->where("prize!=", "");
        if ($web_id) {
            $this->db->where('tbl_order.web_id', $web_id);
        }
        if ($date) {
            $this->db->where('DATE(tbl_order.createdAt)', $date);
        }
        $this->db->order_by('tbl_order.id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function delete_cart_item($cart_id, $user_id)
    {
        $this->db->where('id', $cart_id);
        $this->db->where('user_id', $user_id);
        $this->db->delete('tbl_cart');
        return $this->db->affected_rows();
    }
}
