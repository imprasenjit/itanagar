<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '/libraries/razorpay-php/Razorpay.php');

use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;

class Game extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model', 'login_model'));
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
    public function getTicketAvailability($ticket, $web_id)
    {
        $result = $this->web_model->get_ticket_availability($ticket, $web_id);
        if (count($result) > 0) {
            return false;
        } else {
            return true;
        }
    }
    public function tickets($web_id, $start, $end)
    {
        $i = $start;
        $ticketArray = [];
        while ($i <= $end) {
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
                $result = $this->web_model->checkIfTicketAlreadyPresent($cartData);
                if (!$result) {
                    array_push($cartArray, $cartData);
                }
            }
        }
        if (count($cartArray) > 0)
            $this->web_model->insert_cart($cartArray);
        redirect("game/confirm_order");
        // redirect("game/step2");
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
        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
            $data['loggedIn'] = false;
        } else {
            $userId = $this->session->userdata('userId');
        }
        $data['data'] = $this->web_model->order_data($userId);
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
    public function create_order($userId, $cart, $customUserId = null)
    {
        $tickets = [];
        $total_price = 0;
        $temp = [];
        if (isset($cart)) {
            foreach ($cart as $key => $value) {
                $total_price += $value->total_price;
                $temp['ticket_no'] = $value->ticket_no;
                $temp['web_id'] = $value->web_id;
                array_push($tickets, $temp);
            }
            if ($total_price == 0) {
                redirect(base_url());
            }
            $transaction_id = $this->getRandomStringRandomInt();
            $api = new RazorpayApi($this->config->item('key_id'), $this->config->item('key_secret'));
            $orderData = [
                'receipt'         => $transaction_id,
                'amount'          => intval($total_price) * 100,
                'currency'        => 'INR',
                'notes' => array("tickets" => json_encode($tickets))
            ];
            $razorpayOrder = $api->order->create($orderData);
            $order_object = (array)$razorpayOrder;
            if ($razorpayOrder['id']) {
                $order_data = [
                    'tickets' => json_encode($tickets),
                    'user_id' => $userId,
                    'custom_user_id' => $customUserId,
                    'total_price' => $total_price,
                    'paid_type' => 'RAZORPAY',
                    'paid_status' => 'CREATED',
                    'transaction_id' => $transaction_id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'razorpay_order_response' => json_encode($order_object)
                ];
                $this->web_model->insert_order($order_data);
                foreach ($cart as $key => $value) {
                    $cartData = [
                        'paid_status' => 1
                    ];
                    if ($customUserId != null) {
                        $this->web_model->update_cart_data($customUserId, $value->web_id, $value->ticket_no, $cartData);
                    }
                    $this->web_model->update_cart_data($userId, $value->web_id, $value->ticket_no, $cartData);
                }
                return $razorpayOrder;
            }
        }
    }
    function get_payment_session_id_of_existing_order($order_id)
    {
    }
    function check_status($order_id)
    {
    }
    public function registerUser()
    {
        $mobile = strtolower($this->security->xss_clean($this->input->post('mobile')));
        $email = strtolower($this->security->xss_clean($this->input->post('email')));
        $name = ucwords(strtolower($this->security->xss_clean($this->input->post('fname'))));
        $address = strtolower($this->security->xss_clean($this->input->post('address')));
        $roleId = 2;

        if ($this->login_model->checkMobileExist($mobile)) {
            $userInfo = $this->user_model->getUserInfoByMobile($mobile);
            if (!empty($email)) {
                $userData = array(
                    'email' => $email,
                    'address'=>$address,
                );
                $this->user_model->editUserByMobile($userData, $mobile);
            }
            return (array) $userInfo;
        } else {
            $userData = array(
                'name' => $name,
                'address'=>$address,
                'mobile' => $mobile,
                'email' => $email,
                'roleId' => $roleId,
                'createdDtm' => date('Y-m-d H:i:s')
            );
            $result = $this->user_model->addNewUser($userData);
            if ($result > 0) {
                return $userData;
            } else {
                redirect("game/confirm_order");
            }
        }
    }
    public function payment()
    {

        if ($this->session->userdata('isLoggedIn') != TRUE) {
            $userId = $this->session->userdata('custom_userId');
            $user_details = $this->registerUser();
            $data['cart'] = $this->web_model->order_data($userId);
            $order_details = $this->create_order($user_details["userId"], $data['cart'], $userId);
        } else {
            $userId = $this->session->userdata('userId');
            $user_details = (array) $this->user_model->getUserInfo($userId);
            $data['cart'] = $this->web_model->order_data($userId);
            $order_details = $this->create_order($userId, $data['cart']);
        }


        $data['order'] = (object) [
            'key_id' => $this->config->item('key_id'),
            'amount' => $order_details['amount'],
            'currency' => $order_details['currency'],
            "name" => "ITANAGAR CHOICE",
            "description" => "Event Tickets",
            "image" => base_url() . "public/images/logo.png",
            "order_id" => $order_details['id'],
            "callback_url" => base_url() . "game/payment_confirm",
            "prefill" => array(
                "name" => $user_details['name'], //your customer's name
                "contact" => $user_details['mobile'] //Provide the customer's phone number for better conversion rates 
            ),
            "address" => "Razorpay",
        ];
        // pre($data);
        $this->session->set_userdata('payment_order_id', $order_details['id']);
        $this->load->view("frontend/header");
        $this->load->view("frontend/payment", $data);
        $this->load->view("frontend/footer");
    }
    public function payment_confirm()
    {
        $success = false;
        $error = "Payment Failed";
        $this->load->helper('email');
        $razorpayOrderId = $this->input->post('razorpay_order_id', TRUE);
        $razorPayPaymentId = $this->input->post('razorpay_payment_id', TRUE);
        $paymentResposnse = array(
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorPayPaymentId,
            'razorpay_signature' => $this->input->post('razorpay_signature', TRUE)
        );

        $orderDetails = $this->web_model->get_order_by_orderId($razorpayOrderId);
        $userInfo = $this->user_model->getUserInfo($orderDetails->user_id);
        $tickets = json_decode($orderDetails->tickets);
        $ticketDetails = [];
        foreach ($tickets as $key => $value) {
            $webInfo = $this->web_model->getallWebInfo("tbl_webs", $value->web_id);
            $range = $this->web_model->getrangeInfo($value->web_id);
            array_push($ticketDetails, array("webInfo" => $webInfo, "range" => $range, "ticketNo" => $value->ticket_no));
        }
        if (empty($_POST['razorpay_payment_id']) === false && empty($_POST['razorpay_signature']) === false) {
            $api = new RazorpayApi($this->config->item('key_id'), $this->config->item('key_secret'));
            try {
                $attributes = array(
                    'razorpay_order_id' => $this->session->payment_order_id,
                    'razorpay_payment_id' => $this->input->post('razorpay_payment_id', TRUE),
                    'razorpay_signature' => $this->input->post('razorpay_signature', TRUE)
                );
                $api->utility->verifyPaymentSignature($attributes);
                $orderData = array(
                    "order_status" => 1,
                    "payment_response" => json_encode($paymentResposnse)
                );
                $success = true;
            } catch (PaymentError $e) {
                $error = 'Razorpay Error : ' . $e->getMessage();
                $orderData = array(
                    "order_status" => 2,
                    "payment_response" => json_encode($paymentResposnse)
                );
            }
        }
        $this->web_model->update_order_by_orderId($razorpayOrderId, $orderData);


        $data["ticket_details"] = $ticketDetails;
        if ($success === true) {

            $data["status"] = "Payment Successful";
            $data["details"] = array(
                "razorpay_order_id" => $razorpayOrderId,
                "razorpay_payment_id" => $razorPayPaymentId,
            );
            $email_body = $this->load->view("frontend/email_ticket", $data, TRUE);
            // echo $email_body;
            sendmail($userInfo->email, "Order :" . $razorpayOrderId, $email_body);
            $this->load->view("frontend/header");
            $this->load->view("frontend/payment_confirmation", $data);
            $this->load->view("frontend/footer");
        } else {
            $data["status"] = "Payment Failed";
            $data["details"] = array(
                "razorpay_order_id" => $razorpayOrderId,
                "razorpay_payment_id" => $razorPayPaymentId,
            );
            $this->load->view("frontend/header");
            $this->load->view("frontend/payment_failed", $data);
            $this->load->view("frontend/footer");
        }
    }
    public function payment_cancelled()
    {
        $orderId = $this->input->post('order_id', TRUE);
        // $orderDetails = $this->web_model->get_order_by_orderId($orderId);
        // $tickets = json_decode($orderDetails->tickets);
        // if ($orderDetails->custom_user_id != null) {
        //     $cartUserId = $orderDetails->custom_user_id;
        // } else {
        //     $cartUserId = $orderDetails->user_id;
        // }
        // foreach ($tickets as $key => $value) {
        //     $temp = array(
        //         "paid_status" => 0
        //     );
        //     $this->web_model->update_cart_data($cartUserId, $value->web_id, $value->ticket_no, $temp);
        // }
        $paymentResposnse = array(
            'razorpay_order_id' => $this->input->post('order_id', TRUE),
            'razorpay_payment_id' => $this->input->post('payment_id', TRUE),
            'reason' => $this->input->post('reason', TRUE),
            'description' => $this->input->post('description', TRUE),
            'description' => $this->input->post('description', TRUE)
        );
        $updateOrderData = array(
            "payment_response" => json_encode($paymentResposnse),
            "order_status" => 2
        );
        $this->web_model->update_order_by_orderId($orderId, $updateOrderData);
    }
    public function deletecartdata()
    {
        $userId = $this->input->post('userId');
        $result = $this->web_model->deleteWeb("tbl_cart", $userId);
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
