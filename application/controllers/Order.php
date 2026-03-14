<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . '/libraries/BaseController.php';

class Order extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model'));
        $this->load->library('form_validation');
        $this->isLoggedIn();
    }

    public function release_order() {
        $getFailedOrders = $this->web_model->getFailedOrders();
        foreach ($getFailedOrders as $key => $value) {
            $this->web_model->release_order(json_decode($value->tickets), $value->user_id, $value->custom_user_id);
        }
        $this->session->set_flashdata('success_message','Tickets Released');
        redirect("web/order");
    }
}
