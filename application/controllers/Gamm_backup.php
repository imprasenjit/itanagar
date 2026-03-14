<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Game extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model'));
    }
    /**
     * Index Page for this controller.
     */
    public function type($id)
    {
        $data['website'] = $this->web_model->getallWebInfo("tbl_webs", $id);
        if (!$data['website']) {
            redirect("/");
        }
        $data['range'] = $this->web_model->getrangeInfo($id);
        $data['ticketRange'] = $this->getFirstAvailabletickets($data['range']->web_id);
        $this->load->view("frontend/header");
        $this->load->view("frontend/game_detail", $data);
        $this->load->view("frontend/footer");
    }
    public function tickets($web_id, $start, $end)
    {
        $i = $start;
        $ticketArray = [];

        while ($i < $end) {
            $ticketAvailability = $this->getTicketAvailability($i, $web_id);
            if ($ticketAvailability) {
                array_push($ticketArray, $i);
            }
            $i++;
        }
        $data['range'] = $this->web_model->getrangeInfo($web_id);
        $data['website'] = $this->web_model->getallWebInfo("tbl_webs", $web_id);
        $data['tickets'] = $ticketArray;
        $this->load->view("frontend/header");
        $this->load->view("frontend/ticket_details", $data);
        $this->load->view("frontend/footer");
    }
    public function getFirstAvailabletickets($web_id)
    {
        $range = $this->web_model->getrangeInfo($web_id);
        if (!$range) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Game Not found')));
        }
        $rangeString = trim($range->rangeStart);
        $rangeSplit = explode(",", $range->rangeStart);
        $ticketRanges = [];
        foreach ($rangeSplit as $key => $value) {
            $rangeValue = explode("-", $value);
            array_push($ticketRanges, $rangeValue[0]);
            array_push($ticketRanges, $rangeValue[1]);
        }
        // pre($ticketRanges);
        // $ticketRange = range($range->rangeStart, $range->rangeEnd, 100);
        return $ticketRanges;
    }
    public function getavailabletickets()
    {
        $web_id = $this->input->post('web_id', TRUE);
        $search = $this->input->post('search', TRUE);
        // pre($search);
        $range = $this->web_model->getrangeInfo($web_id);
        if (!$range) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('error' => 'Game Not found')));
        }
        $ticketArray = [];
        // if ($search == '') {
        //     $ticketStart = $range->rangeStart + 1;
        //     $i = 0;
        //     while ($i < 10) {
        //         $ticketAvailability = $this->getTicketAvailability($ticketStart, $web_id);
        //         if ($ticketAvailability) {
        //             array_push($ticketArray, ['id' => $ticketStart, 'text' => $ticketStart]);
        //             $ticketStart += 1;
        //         } else {
        //             $ticketStart += 1;
        //         }
        //         $i++;
        //     }
        // } else {
        $status = false;
        $checkRange = $this->web_model->getRangeAvailability(intval($search), $web_id);
        if (count($checkRange) > 0) {
            $ticketAvailability = $this->getTicketAvailability(intval($search), $web_id);
            if ($ticketAvailability) {
                array_push($ticketArray, ['id' => intval($search), 'text' => intval($search)]);
                $status = true;
            }
        } else {
            $status = false;
        }
        // }
        // pre($ticketArray);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => $status, 'results' => $ticketArray)));
    }
    public function getTicketAvailability($ticket, $web_id)
    {
        $result = $this->web_model->get_ticket_availability($ticket, $web_id);
        if (count($result) > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function index()
    {
        $data['website'] = $this->web_model->home_web();
        $this->load->view("frontend/header");
        $this->load->view("frontend/game", $data);
        $this->load->view("frontend/footer");
    }
    public function jackpot()
    {
        $data['lottery'] = $this->web_model->lottery_web();
        $this->load->view("frontend/header");
        $this->load->view("frontend/jackpot", $data);
        $this->load->view("frontend/footer");
    }
    public function addtocart()
    {
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
        } else {
            $userId = $this->session->userdata('userId');
        }
        $web_id = $this->input->post('web_id');
        $range = $this->web_model->getrangeInfo($web_id);
        $ticketArray = $this->input->post('tickets');
        $cartArray = [];
        $errors = [];
        foreach ($ticketArray as $key => $value) {
            $ticketAvailability = $this->getTicketAvailability(intval($value), $web_id);
            if (!$ticketAvailability) {
                array_push($errors, ['error' => 'Ticket No : ' . $value . ' is not available']);
                $this->session->set_flashdata('error', $errors);
            } else {
                $cartData = [
                    'web_id' => $web_id,
                    'user_id' => $userId,
                    'ticket_no' => $value,
                    'total_price' => $range->price
                ];
                array_push($cartArray, $cartData);
            }
        }
        if (count($cartArray) > 0)
            $this->web_model->insert_cart($cartArray);
        redirect("game/step2");
    }
    public function step2()
    {
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
        } else {
            $userId = $this->session->userdata('userId');
        }
        $data['data'] = $this->web_model->cart_data($userId);
        $this->load->view("frontend/header");
        $this->load->view("frontend/step2", $data);
        $this->load->view("frontend/footer");
    }
    public function confirm_order()
    {
        // if ($this->session->userdata('isLoggedIn') != TRUE) {
        //     $this->session->set_flashdata('error', 'Please logged In before confirm the order');
        //     redirect("login");
        //     // $userId = $this->session->userdata('custom_userId');
        // } else {
        //     $userId = $this->session->userdata('userId');
        // }
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
        } else {
            $userId = $this->session->userdata('userId');
        }
        $data['data'] = $this->web_model->order_data($userId);
        // pre($data);
        if (count($data['data']) == 0) {
            redirect("game/step2");
        }
        $this->load->view("frontend/header");
        $this->load->view("frontend/confirm_order", $data);
        $this->load->view("frontend/footer");
    }
    /**
     * Uses random_int as core logic and generates a random string
     * random_int is a pseudorandom number generator
     *
     * @param int $length
     * @return string
     */
    function getRandomStringRandomInt($length = 16)
    {
        // $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyz';
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $stringSpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function create_order($userId, $cart)
    {
        $tickets = [];
        $total_price = 0;
        $temp = [];
        $cartArray = [];
        foreach ($cart as $key => $value) {
            $total_price += $value->total_price;
            $temp['ticket_no'] = $value->ticket_no;
            $temp['web_id'] = $value->web_id;
            $cartData = [
                'paid_status' => 1
            ];
            array_push($tickets, $temp);
            $this->web_model->update_cart_data($userId, $value->web_id, $value->ticket_no, $cartData);
        }
        // $user_details = $this->user_model->getUserInfo($userId);
        $order_data = [
            'tickets' => json_encode($tickets),
            'user_id' => $userId,
            'total_price' => $total_price,
            'paid_type' => 'RAZORPAY',
            'paid_status' => 'CREATED',
            'transaction_id' => $this->getRandomStringRandomInt(),

        ];
        $orders = $this->web_model->get_order($userId, json_encode($tickets), $total_price);
        if (count($orders) > 0) {
            $order_id = $orders[0]->id;
        } else {
            $order_id = $this->web_model->insert_order($order_data);
        }
        return $order_id;
    }
    function get_payment_session_id_of_existing_order($order_id)
    {
    }
    function check_status($order_id)
    {
    }
    public function payment()
    {
        echo BASEPATH;
        die;
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect("login");
        }
        $userId = $this->session->userdata('userId');
        $data['cart'] = $this->web_model->order_data($userId);
        $data['payment_session_id'] = $this->create_order($userId, $data['cart']);
        $data['pay'] = $this->web_model->total_pay($userId);
        $data["money"] = $this->web_model->wallet();
        $this->load->view("frontend/header");
        $this->load->view("frontend/payment", $data);
        $this->load->view("frontend/footer");
    }
    public function deletecartdata()
    {
        $userId = $this->input->post('userId');
        $result = $this->web_model->deleteWeb("tbl_cart", $userId);
        redirect("game/step2");
    }
    public function quick_play()
    {
        $web_id = $this->input->post('web_id');
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
        } else {
            $userId = $this->session->userdata('userId');
        }
        // $this->web_model->clear_cart($userId);
        $range = $this->web_model->getrangeInfo($web_id);
        $white_from = $range->white_from;
        $white_to = $range->white_to;
        $yellow_from = $range->yellow_from;
        $yellow_to = $range->yellow_to;
        $price = $range->price;
        $date = $this->input->post('date');
        $select_quick = $this->input->post('select_quick');
        for ($i = 0; $i < $select_quick; $i++) {
            $insert = array();
            $w1 = rand($white_from, $white_to);
            $w2 = rand($white_from, $white_to);
            $w3 = rand($white_from, $white_to);
            $w4 = rand($white_from, $white_to);
            $w5 = rand($white_from, $white_to);
            $y1 = rand($yellow_from, $yellow_to);
            $insert = array(
                "web_id" => $web_id,
                "user_id" => $userId,
                "date" => $date,
                "white1" => $w1,
                "white2" => $w2,
                "white3" => $w3,
                "white4" => $w4,
                "white5" => $w5,
                "yellow1" => $y1,
                "total_price" => $price,
            );
            if ($range->white_ball == 6) {
                $insert['white6'] = rand($white_from, $white_to);
            }
            if ($range->yellow_ball == 2) {
                $insert['yellow2'] = rand($yellow_from, $yellow_to);
            }
            $this->web_model->insert_date("tbl_cart", $insert);
        }
        redirect("game/step2");
    }
    public function result()
    {
        // error_reporting
        $lottery = $this->web_model->lottery_web();
        $error = 0;
        if (isset($_REQUEST['game']) && $_REQUEST['game'] != "") {
            $first_l = $_REQUEST['game'];
            if (isset($_REQUEST['sdate']) && $_REQUEST['sdate'] != "") {
                $d = date("Y-m-d", strtotime($_REQUEST['sdate']));
            } else {
                $error = 1;
            }
        } else {
            $first_l = $lottery[0]->id;
            $date = $this->web_model->getalldates_result($first_l);
            $patterndate = "";
            if (count($date) > 0) {
                $d = $date[0]->date;
            } else {
                $d = date("Y-m-d");
            }
        }
        // // $r_date = $this->web_model->drawing_detail($first_l,$d);
        $winner = $this->web_model->winner_detail();
        // print_r($winner);
        $data = array("w_id" => $first_l, "lottery" => $lottery, "winner" => $winner,  "search_date" => $d);
        // die;
        if ($error == 1 || count($winner) == 0) {
            $this->session->set_flashdata('error', 'Something Went Wrong! Please try again.');
            redirect("game/result", $data);
        }
        $this->load->view("frontend/header", $data);
        $this->load->view("frontend/result");
        $this->load->view("frontend/footer");
    }
    public function order_con()
    {
        $userId = $this->session->userdata('userId');
        $cart = $this->web_model->cart_data($userId);
        foreach ($cart as $c) {
            $insert = array();
            $insert['web_id'] = $c->web_id;
            $insert['user_id'] = $c->user_id;
            $insert['date'] = $c->date;
            $insert['white1'] = $c->white1;
            $insert['white2'] = $c->white2;
            $insert['white3'] = $c->white3;
            $insert['white4'] = $c->white4;
            $insert['white5'] = $c->white5;
            $insert['white6'] = $c->white6;
            $insert['yellow2'] = $c->yellow2;
            $insert['yellow1'] = $c->yellow1;
            $insert['total_price'] = $c->total_price;
            $insert['paid_type'] = $this->input->post('paymet_type');
            $insert['transaction_id'] = $this->input->post('transaction_id');
            $this->web_model->insert_date("tbl_order", $insert);
            $userId = $c->id;
            $result = $this->web_model->deleteWeb("tbl_cart", $userId);
        }
        $this->session->set_flashdata('success', 'order confirmed successfully');
        redirect("account/order_history");
    }
    public function wallet_pay_order()
    {
        $userId = $this->session->userdata('userId');
        $cart = $this->web_model->cart_data($userId);
        $wallet_money = $this->web_model->wallet();
        $pay = $this->web_model->total_pay($userId);
        if ($wallet_money) {
            $insert2 = array(
                "user_id" => $userId,
                "money" => ($wallet_money->money - $pay->sums)
            );
            $this->web_model->editWeb_all("tbl_wallet", $insert2, $wallet_money->id);
            $insertdata = array(
                "user_id" => $userId,
                "money" => $pay->sums,
                "trancaction_id" => "",
                "type" => "Debit"
            );
            $this->web_model->insert_date("tbl_wallet_history", $insertdata);
        }
        foreach ($cart as $c) {
            $insert = array();
            $insert['web_id'] = $c->web_id;
            $insert['user_id'] = $c->user_id;
            $insert['date'] = $c->date;
            $insert['white1'] = $c->white1;
            $insert['white2'] = $c->white2;
            $insert['white3'] = $c->white3;
            $insert['white4'] = $c->white4;
            $insert['white5'] = $c->white5;
            $insert['white6'] = $c->white6;
            $insert['yellow2'] = $c->yellow2;
            $insert['yellow1'] = $c->yellow1;
            $insert['total_price'] = $c->total_price;
            $insert['paid_type'] = "0";
            $insert['transaction_id'] = "";
            $this->web_model->insert_date("tbl_order", $insert);
            $userId = $c->id;
            $result = $this->web_model->deleteWeb("tbl_cart", $userId);
        }
        $this->session->set_flashdata('success', 'order confirmed successfully');
        redirect("account/order_history");
    }
}
