<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


require APPPATH . '/libraries/BaseController.php';
/**
 * Class : Login (LoginController)
 * Login class to control to authenticate user credentials and starts user's session.
 * @version : 1.1
 * @since : 15 November 2016
 */
class Account extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model'));
        $this->isLoggedIn();
        $this->load->library('form_validation');
        if ($_SESSION['role'] == 1) {
            redirect("dashboard");
        }
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        $data = array();

        $data['country'] = $this->web_model->getallcountry();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $this->load->view("frontend/header");
        $this->load->view("frontend/profile", $data);
        $this->load->view("frontend/footer");
    }


    function pUpdate()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('fname', 'Full Name', 'trim|required|max_length[128]');
        $this->form_validation->set_rules('mobile', 'Mobile Number', 'required|min_length[10]');
        // $this->form_validation->set_rules('phonecode','Phone Code','required');

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[128]|callback_emailExists');


        $this->form_validation->set_rules('paypal', 'Paypal', 'trim|required|valid_email|max_length[256]');

        $this->form_validation->set_rules('bank', 'Bank', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $this->index();
        } else {
            $name = ucwords(strtolower($this->security->xss_clean($this->input->post('fname'))));
            $mobile = $this->security->xss_clean($this->input->post('mobile'));
            $email = strtolower($this->security->xss_clean($this->input->post('email')));
            $paypal = strtolower($this->security->xss_clean($this->input->post('paypal')));


            $bank = $this->input->post('bank');


            $userInfo = array('name' => $name, 'email' => $email, 'bank' => $bank, 'paypal' => $paypal, 'phonecode' => $this->input->post('phonecode'), 'mobile' => $mobile, 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s'));

            $result = $this->user_model->editUser($userInfo, $this->vendorId);

            if ($result == true) {
                $this->session->set_userdata('name', $name);
                $this->session->set_flashdata('success', 'Profile updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Profile updation failed');
            }

            $this->index();
        }
    }

    function emailExists($email)
    {
        $userId = $this->vendorId;
        $return = false;

        if (empty($userId)) {
            $result = $this->user_model->checkEmailExists($email);
        } else {
            $result = $this->user_model->checkEmailExists($email, $userId);
        }

        if (empty($result)) {
            $return = true;
        } else {
            $this->form_validation->set_message('emailExists', 'The {field} has been already taken');
            $return = false;
        }

        return $return;
    }

    public function changepassword()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));
        $this->load->view("frontend/header");
        $this->load->view("frontend/changepassword", $data);
        $this->load->view("frontend/footer");
    }

    function passwordUpdate()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('oldPassword', 'Old password', 'required|max_length[20]');
        $this->form_validation->set_rules('newPassword', 'New password', 'required|max_length[20]');
        $this->form_validation->set_rules('cNewPassword', 'Confirm new password', 'required|matches[newPassword]|max_length[20]');
        if ($this->form_validation->run() == FALSE) {
            $this->changepassword();
        } else {
            $oldPassword = $this->input->post('oldPassword');
            $newPassword = $this->input->post('newPassword');

            $resultPas = $this->user_model->matchOldPassword($this->vendorId, $oldPassword);

            if (empty($resultPas)) {
                $this->session->set_flashdata('nomatch', 'Your old password is not correct');
                $this->changepassword();
            } else {
                $usersData = array(
                    'password' => getHashedPassword($newPassword), 'updatedBy' => $this->vendorId,
                    'updatedDtm' => date('Y-m-d H:i:s')
                );

                $result = $this->user_model->changePassword($this->vendorId, $usersData);

                if ($result > 0) {
                    $this->session->set_flashdata('success', 'Password updation successful');
                } else {
                    $this->session->set_flashdata('error', 'Password updation failed');
                }

                $this->changepassword();
            }
        }
    }

    public function wallet()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["money"] = $this->web_model->wallet();
        $data["money_history"] = $this->web_model->wallet_history();
        $data['common'] = $this->web_model->getcommon();


        $this->load->view("frontend/header");
        $this->load->view("frontend/wallet", $data);
        $this->load->view("frontend/footer");
    }

    public function wupdate()
    {
        $insertdata = array(
            "user_id" => $this->vendorId,
            "money" => $this->input->post('money'),
            "trancaction_id" => $this->input->post('transaction_id'),
            "type" => "Credit",
            "p_type" => $this->input->post('paymet_type')
        );
        $wallet_money = $this->web_model->wallet();
        if (!$wallet_money) {
            $insert = array(
                "user_id" => $this->vendorId,
                "money" => $this->input->post('money')
            );
            $this->web_model->insert_date("tbl_wallet", $insert);
        } else {
            $insert = array(
                "user_id" => $this->vendorId,
                "money" => ($wallet_money->money + $this->input->post('money'))
            );


            $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);
        }
        $this->web_model->insert_date("tbl_wallet_history", $insertdata);
        $this->session->set_flashdata('success', '$' . $this->input->post('money') . ' added in wallet successfully');
        redirect('account/wallet');
    }


    public function refund()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["money"] = $this->web_model->wallet();
        $data["money_history"] = $this->web_model->refund_history();
        $data['common'] = $this->web_model->getcommon();

        $this->load->view("frontend/header");
        $this->load->view("frontend/refund_request", $data);
        $this->load->view("frontend/footer");
    }

    public function withdrawal()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["money"] = $this->web_model->wallet();
        $data["money_history"] = $this->web_model->withdrawl_history();
        $data['common'] = $this->web_model->getcommon();

        $this->load->view("frontend/header");
        $this->load->view("frontend/withdrawl", $data);
        $this->load->view("frontend/footer");
    }

    public function transfer()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["money"] = $this->web_model->wallet();
        $data["money_history"] = $this->web_model->withdrawl_history();
        $data['common'] = $this->web_model->getcommon();

        $this->load->view("frontend/header");
        $this->load->view("frontend/tranfer", $data);
        $this->load->view("frontend/footer");
    }

    public function r_request()
    {

        $insertdata = array(
            "user_id" => $this->vendorId,
            "money" => $this->input->post('add_pay'),
            "reason" => $this->input->post('reason'),
        );
        $this->web_model->insert_date("tbl_refund", $insertdata);
        $this->session->set_flashdata('success', '$' . $this->input->post('add_pay') . ' request sent successfully.');
        redirect('account/refund');
    }

    public function w_request()
    {
        $pay = "";
        if ($_POST['type'] == "1") {
            $userInfo = array('bank' => $_POST['bank_detail']);
            $pay = $_POST['bank_detail'];
        } else {
            $userInfo = array('paypal' => $_POST['pay_email']);
            $pay  = $_POST['pay_email'];
        }

        $wallet_money = $this->web_model->wallet_user($this->vendorId);

        $money = $this->input->post('add_pay');

        if (!$wallet_money || $wallet_money->money < $money) {
            $this->session->set_flashdata('nomatch', 'Withdrawl Money is greater than wallet money.');
            redirect('account/withdrawal');
        }

        $result = $this->user_model->editUser($userInfo, $this->vendorId);

        $insertdata = array(
            "user_id" => $this->vendorId,
            "type" => $this->input->post('type'),
            "paypal_email" => $pay,
            "money" => $this->input->post('add_pay')
        );

        $this->web_model->insert_date("tbl_withdrawl", $insertdata);
        $this->session->set_flashdata('success', '$' . $this->input->post('add_pay') . ' request sent successfully.');
        redirect('account/withdrawal');
    }


    public function t_request()
    {
        $userInfo = array('paypal' => $_POST['pay_email']);

        $wallet_money = $this->web_model->wallet_user($this->vendorId);

        $money = $this->input->post('add_pay');

        $result = $this->user_model->checkuserEmailExists($_POST['pay_email']);

        if (!($result)) {
            $this->session->set_flashdata('nomatch', 'User email is not found in our Database');
            redirect('account/transfer');
        }
        if ($result->userId == $this->vendorId) {
            $this->session->set_flashdata('nomatch', "You Can't transfer money in your wallet");
            redirect('account/transfer');
        }


        if (!$wallet_money || $wallet_money->money < $money) {
            $this->session->set_flashdata('nomatch', 'Transfer Money is greater than wallet money.');
            redirect('account/transfer');
        }


        $insertdata = array(
            "user_id" => $this->vendorId,
            "money" => $money,
            "trancaction_id" => "Transfer",
            "type" => "Debit",
            "p_type" => "Transfer"
        );
        $insert = array(
            "user_id" => $this->vendorId,
            "money" => ($wallet_money->money - $money)
        );
        $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);

        $this->web_model->insert_date("tbl_wallet_history", $insertdata);


        $wallet_money = $this->web_model->wallet_user($result->userId);


        $insertdata = array(
            "user_id" => $result->userId,
            "money" => $money,
            "trancaction_id" => "Transfer",
            "type" => "Credit",
            "p_type" => "Transfer"
        );
        if (!$wallet_money) {
            $insert = array(
                "user_id" => $result->userId,
                "money" => $money
            );
            $this->web_model->insert_date("tbl_wallet", $insert);
        } else {
            $insert = array(
                "user_id" => $result->userId,
                "money" => ($wallet_money->money + $money)
            );
            $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);
        }

        $this->web_model->insert_date("tbl_wallet_history", $insertdata);

        $insertdata = array(
            "user_id" => $this->vendorId,
            "reciver_id" => $result->userId,
            "paypal_email" => $this->input->post('pay_email'),
            "money" => $this->input->post('add_pay')
        );

        $this->web_model->insert_date("tbl_transfer", $insertdata);
        $this->session->set_flashdata('success', '$' . $this->input->post('add_pay') . ' transfer successfully.');
        redirect('account/transfer');
    }


    public function order_history()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["orders"] = $this->web_model->order_history($this->session->userdata('userId'));

        $this->load->view("frontend/header");
        $this->load->view("frontend/order", $data);
        $this->load->view("frontend/footer");
    }


    public function winner_history()
    {
        $data = array();
        $data["userInfo"] = $this->user_model->getUserInfoWithRole($this->session->userdata('userId'));

        $data["money_history"] = $this->web_model->winner_history($this->session->userdata('userId'));

        $data['amount'] = $this->web_model->winner_amountf($this->session->userdata('userId'));

        $this->load->view("frontend/header");
        $this->load->view("frontend/winner", $data);
        $this->load->view("frontend/footer");
    }
}
