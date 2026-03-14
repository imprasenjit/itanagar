<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Login (LoginController)
 * Login class to control to authenticate user credentials and starts user's session.

 * @version : 1.1
 * @since : 15 November 2016
 */
class Page extends CI_Controller
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
    public function index($type)
    {
        $data['page'] = $this->web_model->page_detail($type);
        $this->load->view("frontend/header");
        $this->load->view("frontend/page", $data);
        $this->load->view("frontend/footer");
    }

    public function faq()
    {
        $data['faq'] = $this->web_model->faq();
        $this->load->view("frontend/header");
        $this->load->view("frontend/faq", $data);
        $this->load->view("frontend/footer");
    }
    public function refunds_cacellations()
    {
        // $data['faq'] = $this->web_model->faq();
        $this->load->view("frontend/header");
        $this->load->view("frontend/refund");
        $this->load->view("frontend/footer");
    }


    public function contact()
    {

        if (isset($_POST['submit'])) {
            $result = $this->web_model->email_found("contact_us");

            $insert =  array(
                "name" => $this->security->xss_clean($this->input->post('name')),
                "email" => $this->security->xss_clean($this->input->post('email')),
                "message" => $this->security->xss_clean($this->input->post('message'))
            );


            $CI = setProtocol();
            $message = $CI->load->view("email/contact", $insert, TRUE);

            // echo $message;
            $CI->email->from(EMAIL_FROM, FROM_NAME);
            $CI->email->subject("Lottery - Contact Us");
            $CI->email->message($message);
            $CI->email->to($result->email);
            $CI->email->send();



            $this->web_model->insert_date("tbl_contact", $insert);
            $this->session->set_flashdata('success', 'Request submitted successfully! Support team will contact you soon.');
        }
        $data['re'] = "";
        $this->load->view("frontend/header");
        $this->load->view("frontend/contact", $data);
        $this->load->view("frontend/footer");
    }
}
