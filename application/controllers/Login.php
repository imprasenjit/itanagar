<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Login (LoginController)
 * Login class to control to authenticate user credentials and starts user's session.
 * @author : Kishor Mali
 * @version : 1.1
 * @since : 15 November 2016
 */
class Login extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('login_model');
        $this->load->model('user_model');
        $this->load->model('web_model');
    }

    /**
     * Index Page for this controller.
     */
    public function index()
    {
        // $pass = 'Admin';
        // echo getHashedPassword($pass);
        // die;
        $this->isLoggedIn();
    }

    /**
     * This function used to check the user is logged in or not
     */
    function isLoggedIn()
    {
        $isLoggedIn = $this->session->userdata('isLoggedIn');

        if (!isset($isLoggedIn) || $isLoggedIn != TRUE) {
            $this->load->view('frontend/header');
            $this->load->view('login');
            $this->load->view('frontend/footer');
        } else {
            redirect('/dashboard');
        }
    }

    function isLoggedIn_org()
    {
        $isLoggedIn = $this->session->userdata('isLoggedIn');

        if (!isset($isLoggedIn) || $isLoggedIn != TRUE) {
            $this->load->view('login');
            $this->load->view('login');
            $this->load->view('login');
        } else {
            redirect('/dashboard');
        }
    }


    /**
     * This function used to logged in user
     */
    public function loginMe()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('email', 'Email', 'required|max_length[128]|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[32]');

        if ($this->form_validation->run() == FALSE) {
            $this->index();
        } else {
            $email = strtolower($this->security->xss_clean($this->input->post('email')));
            $password = $this->input->post('password');

            $result = $this->login_model->loginMe($email, $password);

            if (!empty($result)) {
                if ($result->roleId != 1) {
                    $lastLogin = $this->login_model->lastLoginInfo($result->userId);
                    $c_userId = $this->session->userdata('custom_userId');
                    $this->web_model->up_cart($c_userId, $result->userId);
                }
                // $CI = setProtocol();
                // $message = $CI->load->view("email/login", array("result" => $result), TRUE);

                // // echo $message;
                // $CI->email->from(EMAIL_FROM, FROM_NAME);
                // $CI->email->subject("Lottery - Login");
                // $CI->email->message($message);
                // $CI->email->to($result->email);
                // $CI->email->send();



                $sessionArray = array(
                    'userId' => $result->userId,
                    'role' => $result->roleId,
                    'roleText' => $result->role,
                    'name' => $result->name,
                    'email' => $result->email,
                    'mobile' => $result->mobile,
                    'lastLogin' => $lastLogin->createdDtm,
                    'isLoggedIn' => TRUE
                );

                $this->session->set_userdata($sessionArray);

                unset($sessionArray['userId'], $sessionArray['isLoggedIn'], $sessionArray['lastLogin']);

                $loginInfo = array("userId" => $result->userId, "sessionData" => json_encode($sessionArray), "machineIp" => $_SERVER['REMOTE_ADDR'], "userAgent" => getBrowserAgent(), "agentString" => $this->agent->agent_string(), "platform" => $this->agent->platform());

                $this->login_model->lastLogin($loginInfo);

                if (count($this->web_model->order_data($result->userId)) > 0 || $result->roleId == 2) {
                    redirect('game/confirm_order');
                }

                redirect('/dashboard');
            } else {
                $this->session->set_flashdata('error', 'Email or password mismatch');

                $this->index();
            }
        }
    }

    /**
     * This function used to load forgot password view
     */
    public function forgotPassword()
    {
        $isLoggedIn = $this->session->userdata('isLoggedIn');

        if (!isset($isLoggedIn) || $isLoggedIn != TRUE) {
            $this->load->view('frontend/header');
            $this->load->view('forgotPassword');
            $this->load->view('frontend/footer');
        } else {
            redirect('/dashboard');
        }
    }

    /**
     * This function used to generate reset password request link
     */
    function resetPasswordUser()
    {
        $this->load->helper('email');

        $status = '';

        $this->load->library('form_validation');

        $this->form_validation->set_rules('login_email', 'Email', 'trim|required|valid_email');

        if ($this->form_validation->run() == FALSE) {
            $this->forgotPassword();
        } else {
            $email = strtolower($this->security->xss_clean($this->input->post('login_email')));

            if ($this->login_model->checkEmailExist($email)) {
                $encoded_email = urlencode($email);

                $this->load->helper('string');
                $data['email'] = $email;
                $data['activation_id'] = random_string('alnum', 15);
                $data['createdDtm'] = date('Y-m-d H:i:s');
                $data['agent'] = getBrowserAgent();
                $data['client_ip'] = $this->input->ip_address();

                $save = $this->login_model->resetPasswordUser($data);

                if ($save) {
                    $data1['reset_link'] = base_url() . "resetPasswordConfirmUser/" . $data['activation_id'] . "/" . $encoded_email;
                    $userInfo = $this->login_model->getCustomerInfoByEmail($email);

                    if (!empty($userInfo)) {
                        $data1["name"] = $userInfo->name;
                        $data1["email"] = $userInfo->email;
                        $data1["message"] = "Reset Your Password";
                    }

                    $sendStatus = resetPasswordEmail($data1);

                    if ($sendStatus) {
                        $status = "send";
                        setFlashData($status, "Reset password link has been sent successfully, please check your email.");
                    } else {
                        $status = "notsend";
                        setFlashData($status, "Email has been failed, try again.");
                    }
                } else {
                    $status = 'unable';
                    setFlashData($status, "It seems an error while sending your details, try again.");
                }
            } else {
                $status = 'invalid';
                setFlashData($status, "This email is not registered with us.");
            }
            redirect('/forgotPassword');
        }
    }

    /**
     * This function used to reset the password 
     * @param string $activation_id : This is unique id
     * @param string $email : This is user email
     */
    function resetPasswordConfirmUser($activation_id, $email)
    {
        // Get email and activation code from URL values at index 3-4
        $email = urldecode($email);

        // Check activation id in database
        $is_correct = $this->login_model->checkActivationDetails($email, $activation_id);

        $data['email'] = $email;
        $data['activation_code'] = $activation_id;

        if ($is_correct == 1) {
            $this->load->view("frontend/header");
            $this->load->view('newPassword', $data);
            $this->load->view("frontend/footer");
        } else {
            redirect('/login');
        }
    }

    /**
     * This function used to create new password for user
     */
    function createPasswordUser()
    {
        $status = '';
        $message = '';
        $email = strtolower($this->input->post("email"));
        $activation_id = $this->input->post("activation_code");

        $this->load->library('form_validation');

        $this->form_validation->set_rules('password', 'Password', 'required|max_length[20]');
        $this->form_validation->set_rules('cpassword', 'Confirm Password', 'trim|required|matches[password]|max_length[20]');

        if ($this->form_validation->run() == FALSE) {
            $this->resetPasswordConfirmUser($activation_id, urlencode($email));
        } else {
            $password = $this->input->post('password');
            $cpassword = $this->input->post('cpassword');

            // Check activation id in database
            $is_correct = $this->login_model->checkActivationDetails($email, $activation_id);

            if ($is_correct == 1) {
                $this->login_model->createPasswordUser($email, $password);

                $status = 'success';
                $message = 'Password reset successfully';
            } else {
                $status = 'error';
                $message = 'Password reset failed';
            }

            setFlashData($status, $message);

            redirect("/login");
        }
    }

    function register()
    {
        $isLoggedIn = $this->session->userdata('isLoggedIn');

        if (!isset($isLoggedIn) || $isLoggedIn != TRUE) {
            $country = $this->web_model->getallcountry();
            $this->load->view('frontend/header');
            $this->load->view('register', array('country' => $country));
            $this->load->view('frontend/footer');
        } else {
            redirect('/dashboard');
        }
    }

    public function registerMe()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('fname', 'Full Name', 'trim|required|max_length[128]');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|max_length[128]');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[20]');
        $this->form_validation->set_rules('mobile', 'Mobile Number', 'required|min_length[10]');

        if ($this->form_validation->run() == FALSE) {
            $this->register();
        } else {

            $email = strtolower($this->security->xss_clean($this->input->post('email')));

            if ($this->login_model->checkEmailExist($email)) {
                $this->session->set_flashdata('error', 'This email is already in use');
                $this->register();
            } else {
                $name = ucwords(strtolower($this->security->xss_clean($this->input->post('fname'))));
                $email = strtolower($this->security->xss_clean($this->input->post('email')));
                $password = $this->input->post('password');
                $roleId = 2;
                $mobile = $this->security->xss_clean($this->input->post('mobile'));

                $userInfo = array(
                    'email' => $email,              'password' => getHashedPassword($password), 'roleId' => $roleId,       'name' => $name,
                    'mobile' => $mobile,
                    // 'phonecode' => $this->input->post('phonecode'),
                    'createdDtm' => date('Y-m-d H:i:s')
                );



                // echo "<pre>";
                // print_r($userInfo);
                // die();

                $this->load->model('user_model');
                $result = $this->user_model->addNewUser($userInfo);

                if ($result > 0) {
                    $this->session->set_flashdata('success', 'Registeration done successfully. Please login with your credentials');
                } else {
                    $this->session->set_flashdata('error', 'Something went wrong! Please try again.');
                }

                $this->index();






                // $result = $this->login_model->loginMe($email, $password);

                // if(!empty($result))
                // {
                //     $lastLogin = $this->login_model->lastLoginInfo($result->userId);

                //     $sessionArray = array('userId'=>$result->userId,                    
                //                             'role'=>$result->roleId,
                //                             'roleText'=>$result->role,
                //                             'name'=>$result->name,
                //                             'lastLogin'=> $lastLogin->createdDtm,
                //                             'isLoggedIn' => TRUE
                //                     );

                //     $this->session->set_userdata($sessionArray);

                //     unset($sessionArray['userId'], $sessionArray['isLoggedIn'], $sessionArray['lastLogin']);

                //     $loginInfo = array("userId"=>$result->userId, "sessionData" => json_encode($sessionArray), "machineIp"=>$_SERVER['REMOTE_ADDR'], "userAgent"=>getBrowserAgent(), "agentString"=>$this->agent->agent_string(), "platform"=>$this->agent->platform());

                //     $this->login_model->lastLogin($loginInfo);

                //     redirect('/home');
                // }
            }
        }
    }
}
