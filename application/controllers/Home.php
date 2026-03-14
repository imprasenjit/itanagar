<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Login (LoginController)
 * Login class to control to authenticate user credentials and starts user's session.
 * @version : 1.1
 * @since : 15 November 2016
 */
class Home extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('web_model'));
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        // $data['game'] = $this->web_model->home_web();
        $data['game'] = $this->web_model->lottery_web(6);
        $data['faq'] = $this->web_model->faq(1);

        $this->load->view("frontend/header");
        $this->load->view("frontend/home", $data);
        $this->load->view("frontend/footer");
    }


    public function test_mail()
    {
        echo "Yes";
        die();
    }
}
