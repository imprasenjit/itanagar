<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : User (UserController)
 * User Class to control all user related operations.
 
 * @version : 1.1
 * @since : 15 November 2016
 */
class Web extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model'));
        $this->load->library('form_validation');
        $this->isLoggedIn();
    }

    /**
     * This function used to load the first screen of the user
     */
    public function index()
    {
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        $data['web'] = $this->web_model->get_allweb($searchText);
        $this->global['pageTitle'] = 'Lotetry : Web Listing';
        $this->loadViews("weblist", $this->global, $data, NULL);
    }

    // Game ADD Page
    function addNew()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->load->model('user_model');
            $this->global['pageTitle'] = 'Lotetry : Add New Web';

            $this->loadViews("web/addNew", $this->global, Null, NULL);
        }
    }
    public function confirm_order_by_admin()
    {
        if ($this->isAdmin() == FALSE) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $orderid = $this->input->post("orderid");
            $data = ['order_status' => 1];
            $this->web_model->update_order($orderid, $data);
            echo (json_encode(array('status' => TRUE)));
        }
    }
    public function release_order_by_admin()
    {
        if ($this->isAdmin() == FALSE) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $orderid = $this->input->post("orderid");
            $result = $this->web_model->get_order_by_id($orderid);
            // pre($result);
            $tickets = json_decode($result->tickets);
            $status = 0;
            $data = ['order_status' => 0, 'paid_status' => 'RELEASED'];
            $this->web_model->update_order($orderid, $data);
            foreach ($tickets as $key => $value) {
                $userId = $result->user_id;
                $ticket_no = $value->ticket_no;
                $web_id = $value->web_id;
                $status = $this->web_model->clear_cart_data($userId, $ticket_no, $web_id);
            }
            if ($status)
                echo (json_encode(array('status' => TRUE)));
        }
    }
    // Game add

    function addNewWeb()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {

            $this->form_validation->set_rules('name', 'Name', 'trim|required|max_length[128]');
            if ($this->form_validation->run() == FALSE) {
                $this->addNew();
            } else {
                $name = ucwords(strtolower($this->security->xss_clean($this->input->post('name'))));
                $userInfo = array('name' => $name, 'createdDtm' => date('Y-m-d H:i:s'));

                $result = $this->web_model->addNewWeb($userInfo);

                if ($result > 0) {
                    $insert = array(
                        'web_id' => $result
                    );
                    $this->web_model->insert_date("tbl_ranges", $insert);
                    $this->session->set_flashdata('success', 'New Game created successfully');
                } else {
                    $this->session->set_flashdata('error', 'Game creation failed');
                }

                redirect('web');
            }
        }
    }




    // Webiste Edit

    function edit($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }

            $data['userInfo'] = $this->web_model->getWebInfo($id);
            $this->global['pageTitle'] = 'Lotetry : Edit Game';
            $this->loadViews("web/editOld", $this->global, $data, NULL);
        }
    }

    // Game Update

    function editWeb()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $id = $this->input->post('id');

            $this->form_validation->set_rules('name', 'Name', 'trim|required|max_length[128]');

            if ($this->form_validation->run() == FALSE) {
                $this->editOld($id);
            } else {
                $name = ucwords(strtolower($this->security->xss_clean($this->input->post('name'))));
                $status = $this->input->post('status');
                $userInfo = array();

                $userInfo = array(
                    'status' => $status,
                    'name' => ucwords($name),
                    'updatedDtm' => date('Y-m-d H:i:s')
                );


                $result = $this->web_model->editWebsite($userInfo, $id);

                if ($result == true) {
                    $this->session->set_flashdata('success', 'Game updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Game updation failed');
                }

                redirect('web/edit/' . $id);
            }
        }
    }


    function deleteWeb()
    {
        if ($this->isAdmin() == FALSE) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $userId = $this->input->post('userId');
            $result = $this->web_model->deleteWeb("tbl_webs", $userId);
            if ($result > 0) {
                echo (json_encode(array('status' => TRUE)));
            } else {
                echo (json_encode(array('status' => FALSE)));
            }
        }
    }


    function view($id)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }

            $data['WebInfo'] = $this->web_model->getWebInfo($id);
            if (!$data['WebInfo']) {
                redirect('web');
            }
            $data['RangeInfo'] = $this->web_model->getrangeInfo($id);


            $this->load->library('pagination');
            $count = $this->web_model->count_date($id);
            $returns = $this->paginationCompress("web/view/" . $id . "/", $count, 10, 4);

            $data['userRecords'] = $this->web_model->list_date($id, $returns["page"], $returns["segment"]);
            $this->global['pageTitle'] = 'Lottery : Game View';
            $this->loadViews("web/detail", $this->global, $data, NULL);
        }
    }

    // Webiste Edit

    function rangeEdit($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }
            $data['WebInfo'] = $this->web_model->getWebInfo($id);

            $data['rangeInfo'] = $this->web_model->getrangeInfo($id);
            $this->global['pageTitle'] = 'Lotetry : Edit Lottery Details';
            $this->loadViews("web/rangeedit", $this->global, $data, NULL);
        }
    }

    // Webiste Edit

    function common()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $data['WebInfo'] = $this->web_model->getcommon();
            $this->global['pageTitle'] = 'Lotetry : Edit Common Setting';
            $this->loadViews("web/common", $this->global, $data, NULL);
        }
    }

    // Webiste Edit

    function descriptionEdit($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }
            $data['WebInfo'] = $this->web_model->getWebInfo($id);

            $data['rangeInfo'] = $this->web_model->getrangeInfo($id);
            $this->global['pageTitle'] = 'Lotetry : Edit Range Game';
            $this->loadViews("web/descriptionedit", $this->global, $data, NULL);
        }
    }


    function tier($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }
            $data['WebInfo'] = $this->web_model->getWebInfo($id);
            $data['tier'] = $this->web_model->getTierInfo($id);
            $this->global['pageTitle'] = 'Lotetry : Edit Tier Game';
            $this->loadViews("web/tier", $this->global, $data, NULL);
        }
    }


    function editCommon()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {

            $id = 1;

            $userInfo = array();
            $userInfo = array(
                'wallet_min' => $this->input->post('wallet_min'),
                'wallet_max' => $this->input->post('wallet_max'),
                'refund_min' => $this->input->post('refund_min'),
                'refund_max' => $this->input->post('refund_max'),
                'transfer_min' => $this->input->post('transfer_min'),
                'transfer_max' => $this->input->post('transfer_max'),
                'withdrawl_min' => $this->input->post('withdrawl_min'),
                'withdrawl_max' => $this->input->post('withdrawl_max')
            );

            $result = $this->web_model->editWeb_all("common", $userInfo, $id);
            if ($result == true) {
                $this->session->set_flashdata('success', 'Settings updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Range updation failed');
            }

            redirect('web/common');
        }
    }



    function editRange()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            // pre($this->input->post('result_date'));
            $web_id = $this->input->post('web_id');

            $filename = "";
            $filename2 = "";
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $config['upload_path'] = './public/imglogo/';
                $config['allowed_types'] = 'jpeg|jpg|JPEG|gif|jpg|png|svg';
                $config['max_size'] = 2000;
                $config['max_width'] = 1500;
                $config['max_height'] = 1500;
                $filename = time() . $_FILES["logo"]['name'];
                $config['file_name'] = $filename;
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('logo')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('web/rangeEdit/' . $web_id);
                }
            }
            if (isset($_FILES['logo2']) && $_FILES['logo2']['error'] == 0) {
                $config['upload_path'] = './public/imglogo/';
                $config['allowed_types'] = 'jpeg|jpg|JPEG|gif|jpg|png|svg';
                $config['max_size'] = 2000;
                $config['max_width'] = 1500;
                $config['max_height'] = 1500;
                $filename2 = time() . $_FILES["logo2"]['name'];
                $config['file_name'] = $filename2;
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('logo2')) {
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    redirect('web/rangeEdit/' . $web_id);
                }
            }

            $id = $this->input->post('id');

            $rangeInfo = array();
            $rangeInfo = array(
                'price' => $this->input->post('price'),
                'rangeStart' => $this->input->post('rangeStart'),
                'priority' => $this->input->post('priority'),
                'heading' => $this->input->post('heading'),
                'result_date' => $this->input->post('result_date'),
                'jackpot' => $this->input->post('jackpot')
            );

            if ($filename != "") {
                $rangeInfo['logo'] = $filename;
            }
            if ($filename2 != "") {
                $rangeInfo['logo2'] = $filename2;
            }

            $result = $this->web_model->editWeb_all("tbl_ranges", $rangeInfo, $id);
            if ($result == true) {
                $this->session->set_flashdata('success', 'Range updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Range updation failed');
            }

            redirect('web/rangeEdit/' . $web_id);
        }
    }


    function addtier()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $web_id = $this->input->post('web_id');
            $type = $this->input->post('type');

            $result = $this->web_model->pattern_exist();
            if ($result != 0) {
                $this->session->set_flashdata('error', 'This Pattern already exist in this Game');
            } else {
                $userInfo = array();
                $userInfo = array(
                    'white' => $this->input->post('white'),
                    'per' => $this->input->post('per'),
                    'mega' => $this->input->post('yellow'),
                    'web_id' => $web_id
                );

                if ($type == "Add") {
                    $this->web_model->insert_date("tbl_tier", $userInfo);
                    $this->session->set_flashdata('success', 'Prize Tier added successfully');
                } else {
                    $id = $this->input->post('id');
                    $result = $this->web_model->editWeb_all("tbl_tier", $userInfo, $id);
                    $this->session->set_flashdata('success', 'Prize Tier updated successfully');
                }
            }

            redirect('web/tier/' . $web_id);
        }
    }

    function editdesc()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $id = $this->input->post('id');
            $web_id = $this->input->post('web_id');

            $userInfo = array();
            $userInfo = array(
                'play_description' => $this->input->post('play_description'),
                'when_play' => $this->input->post('when_play')
            );
            $result = $this->web_model->editWeb_all("tbl_ranges", $userInfo, $id);
            if ($result == true) {
                $this->session->set_flashdata('success', 'Description updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Description updation failed');
            }

            redirect('web/descriptionEdit/' . $web_id);
        }
    }



    function addNewWebdate($web_id)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $date = date("Y-m-d", strtotime($this->input->post('date')));
            $count = $this->web_model->date_exist($web_id, $date);
            if ($count > 0) {
                $this->session->set_flashdata('error', 'This date has been already Taken.');
            } else {
                $insert = array(
                    'date' => $date,
                    'date_con' => $dap . " " . TIMEVAL,
                    'web_id' => $web_id
                );
                $this->web_model->insert_date("tbl_dates", $insert);
                $this->session->set_flashdata('success', 'New Date added successfully');
            }
            redirect('web/view/' . $web_id);
        }
    }

    function deleteWebDate()
    {
        if ($this->isAdmin() == FALSE) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $userId = $this->input->post('userId');
            $result = $this->web_model->deleteWeb("tbl_dates", $userId);
            if ($result > 0) {
                echo (json_encode(array('status' => TRUE)));
            } else {
                echo (json_encode(array('status' => FALSE)));
            }
        }
    }

    public function page()
    {
        $data['page'] = $this->web_model->page_list();
        $this->global['pageTitle'] = 'Lottery : Page Listing';
        $this->loadViews("pagelist", $this->global, $data, NULL);
    }

    function pageedit($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }
            $data['userInfo'] = $this->web_model->getallWebInfo("tbl_pages", $id);
            $this->global['pageTitle'] = 'Lotetry : Edit Page';
            $this->loadViews("web/pageedit", $this->global, $data, NULL);
        }
    }

    function editUpadtePage()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $id = $this->input->post('id');

            $userInfo = array();
            $userInfo = array(
                'description' => $this->input->post('description')
            );
            $result = $this->web_model->editWeb_all("tbl_pages", $userInfo, $id);
            if ($result == true) {
                $this->session->set_flashdata('success', 'Page updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Page updation failed');
            }

            redirect('web/pageedit/' . $id);
        }
    }

    function contact_list()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->load->library('pagination');
            $count = $this->web_model->count_contact();
            $returns = $this->paginationCompress("web/contact_list/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->contact_ls($returns["page"], $returns["segment"]);
            $this->global['pageTitle'] = 'Lottery : Contact List';
            $this->loadViews("web/contact", $this->global, $data, NULL);
        }
    }

    private function datearray($web_id)
    {
        $datearray = array();
        if ($web_id == 1) {
            $friday = date("Y-m-d", strtotime('next thursday'));
            $tuesday = date("Y-m-d", strtotime('next sunday'));

            $datearray[] = $friday;
            for ($i = 1; $i <= 4; $i++) {;
                $k = $i * 7;
                $datearray[] = date("Y-m-d", strtotime("+" . $k . " day", strtotime($friday)));
            }

            $datearray[] = $tuesday;
            for ($i = 1; $i <= 4; $i++) {;
                $k = $i * 7;
                $datearray[] = date("Y-m-d", strtotime("+" . $k . " day", strtotime($tuesday)));
            }
        } else if ($web_id == 2 || $web_id == 3  || $web_id == 4  || $web_id == 5) {
            $friday = date("Y-m-d", strtotime('next wednesday'));
            $tuesday = date("Y-m-d", strtotime('next saturday'));

            $datearray[] = $friday;
            for ($i = 1; $i <= 4; $i++) {;
                $k = $i * 7;
                $datearray[] = date("Y-m-d", strtotime("+" . $k . " day", strtotime($friday)));
            }

            $datearray[] = $tuesday;
            for ($i = 1; $i <= 4; $i++) {;
                $k = $i * 7;
                $datearray[] = date("Y-m-d", strtotime("+" . $k . " day", strtotime($tuesday)));
            }
        } else if ($web_id == 6) {
            $tuesday = date("Y-m-d", strtotime('next saturday'));

            $datearray[] = $tuesday;
            for ($i = 1; $i <= 10; $i++) {;
                $k = $i * 7;
                $datearray[] = date("Y-m-d", strtotime("+" . $k . " day", strtotime($tuesday)));
            }
        }

        return $datearray;
    }

    function addtwoWebdate($web_id)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $datearray = $this->datearray($web_id);
            for ($p = 0; $p < 10; $p++) {
                $dap = $datearray[$p];

                $f_count = $this->web_model->date_exist($web_id, $dap);
                if ($f_count == 0) {
                    $insert = array(
                        'date' => $dap,
                        'date_con' => $dap . " " . TIMEVAL,
                        'web_id' => $web_id
                    );
                    $this->web_model->insert_date("tbl_dates", $insert);
                }
            }

            $this->session->set_flashdata('success', 'Date added successfully');
            redirect('web/view/' . $web_id);
        }
    }


    public function order()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_order($searchText);
            $returns = $this->paginationCompress("web/order/", $count, 10, 3);
            $data['orders'] = $this->web_model->order_ls($returns["page"], $returns["segment"], $searchText);

            $this->global['pageTitle'] = 'Lottery : Order History';
            $this->loadViews("web/order", $this->global, $data, NULL);
        }
    }


    public function winner()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_winner($searchText);
            $returns = $this->paginationCompress("web/winner/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->winner_ls($returns["page"], $returns["segment"], $searchText);
            $data['amount'] = $this->web_model->winner_amount($searchText);


            $this->global['pageTitle'] = 'Lottery : Winner History';
            $this->loadViews("web/winner", $this->global, $data, NULL);
        }
    }

    public function wallet()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_wallet($searchText);
            $returns = $this->paginationCompress("web/wallet/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->wallet_ls($returns["page"], $returns["segment"], $searchText);
            $this->global['pageTitle'] = 'Lottery : Order History';
            $this->loadViews("web/wallet", $this->global, $data, NULL);
        }
    }

    public function refund()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_refund($searchText);
            $returns = $this->paginationCompress("web/refund/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->refund_ls($returns["page"], $returns["segment"], $searchText);
            $this->global['pageTitle'] = 'Lottery : Refund History';
            $this->loadViews("web/refund", $this->global, $data, NULL);
        }
    }


    public function withdrawl()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_withdrawl($searchText);
            $returns = $this->paginationCompress("web/withdrawl/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->withdrawl_ls($returns["page"], $returns["segment"], $searchText);
            $this->global['pageTitle'] = 'Lottery : Withdrawl History';
            $this->loadViews("web/withdrawl", $this->global, $data, NULL);
        }
    }

    public function transfer()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            $this->load->library('pagination');
            $count = $this->web_model->count_transfer($searchText);
            $returns = $this->paginationCompress("web/transfer/", $count, 10, 3);
            $data['userRecords'] = $this->web_model->transfer_ls($returns["page"], $returns["segment"], $searchText);
            $this->global['pageTitle'] = 'Lottery : Transfer History';
            $this->loadViews("web/transfer2", $this->global, $data, NULL);
        }
    }
    public function user_wallet($userId)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            // echo $userId;
            // $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $userId;
            $data['userinfo'] = $this->web_model->userInfo($userId);

            $data['money'] = $this->web_model->wallet_user($userId);

            $this->load->library('pagination');
            $count = $this->web_model->count_wallet_single($userId);
            $returns = $this->paginationCompress("web/user_wallet/", $count, 10, 4);
            $data['userRecords'] = $this->web_model->wallet_ls_single($returns["page"], $returns["segment"], $userId);
            $this->global['pageTitle'] = 'Lottery : Order History';
            $this->loadViews("web/user_wallet", $this->global, $data, NULL);
        }
    }

    public function user_order($userId)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            // echo $userId;
            // $searchText = $this->security->xss_clean($this->input->post('searchText'));

            $data['userinfo'] = $this->web_model->userInfo($userId);
            $data['searchText'] = $userId;
            $this->load->library('pagination');
            $count = $this->web_model->count_order_single($userId);
            $returns = $this->paginationCompress("web/user_order/", $count, 10, 4);
            $data['orders'] = $this->web_model->order_ls_single($returns["page"], $returns["segment"], $userId);

            $this->global['pageTitle'] = 'Lottery : Order History';
            $this->loadViews("web/user_order", $this->global, $data, NULL);
        }
    }

    public function faq()
    {
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        $data['web'] = $this->web_model->get_allfaq($searchText);
        $this->global['pageTitle'] = 'Lotetry : Announcement Listing';
        $this->loadViews("faq", $this->global, $data, NULL);
    }


    // FAQ ADD
    function addfaq()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->load->model('user_model');
            $this->global['pageTitle'] = 'Lotetry : Add FAQ';

            $this->loadViews("web/addfaq", $this->global, Null, NULL);
        }
    }

    // FAQ add post

    function addNewfaq()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->form_validation->set_rules('question', 'Question', 'trim|required');
            $this->form_validation->set_rules('answer', 'Answer', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $this->addfaq();
            } else {
                $insert = array(
                    'question' => $this->input->post('question'),
                    'answer' => $this->input->post('answer')
                );
                $result = $this->web_model->insert_date("tbl_faqs", $insert);
                if ($result > 0) {
                    $this->session->set_flashdata('success', 'New FAQ created successfully');
                } else {
                    $this->session->set_flashdata('error', 'FAQ creation failed');
                }
                redirect('web/addfaq');
            }
        }
    }

    function faqedit($id = NULL)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            if ($id == null) {
                redirect('web');
            }

            $data['userInfo'] = $this->web_model->getfaq($id);
            $this->global['pageTitle'] = 'Lotetry : Edit FAQ';
            $this->loadViews("web/editfaq", $this->global, $data, NULL);
        }
    }

    // Game Update

    function faqupdate()
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $id = $this->input->post('id');
            $this->form_validation->set_rules('question', 'Question', 'trim|required');
            $this->form_validation->set_rules('answer', 'Answer', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $this->faqedit($id);
            } else {
                $insert = array(
                    'question' => $this->input->post('question'),
                    'answer' => $this->input->post('answer')
                );

                $result = $this->web_model->editWeb_all("tbl_faqs", $insert, $id);
                if ($result == true) {
                    $this->session->set_flashdata('success', 'FAQ updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'FAQ updation failed');
                }

                redirect('web/faqedit/' . $id);
            }
        }
    }

    function deletefaq()
    {
        if ($this->isAdmin() == FALSE) {
            echo (json_encode(array('status' => 'access')));
        } else {
            $userId = $this->input->post('userId');
            $result = $this->web_model->deleteWeb("tbl_faqs", $userId);
            if ($result > 0) {
                echo (json_encode(array('status' => TRUE)));
            } else {
                echo (json_encode(array('status' => FALSE)));
            }
        }
    }

    function addmoney($userId)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->form_validation->set_rules('money', 'Money', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $this->user_wallet($userId);
            } else {
                $type = $this->input->post('type');
                $money = $this->input->post('money');
                $wallet_money = $this->web_model->wallet_user($userId);
                if ($type == "Credit") {

                    $insertdata = array(
                        "user_id" => $userId,
                        "money" => $money,
                        "trancaction_id" => "Added by Admin",
                        "type" => "Credit",
                        "p_type" => "Admin Add"
                    );
                    if (!$wallet_money) {
                        $insert = array(
                            "user_id" => $userId,
                            "money" => $money
                        );
                        $this->web_model->insert_date("tbl_wallet", $insert);
                    } else {
                        $insert = array(
                            "user_id" => $userId,
                            "money" => ($wallet_money->money + $money)
                        );
                        $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);
                    }

                    $this->web_model->insert_date("tbl_wallet_history", $insertdata);
                    $this->session->set_flashdata('success', 'Money credited successfully');
                } else {
                    if (!$wallet_money || $wallet_money->money < $money) {
                        $this->session->set_flashdata('error', 'Money is greater than wallet money.');
                    } else {

                        $insertdata = array(
                            "user_id" => $userId,
                            "money" => $money,
                            "trancaction_id" => "Debited by Admin",
                            "type" => "Debit",
                            "p_type" => "Admin Debit"
                        );
                        $insert = array(
                            "user_id" => $userId,
                            "money" => ($wallet_money->money - $money)
                        );
                        $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);

                        $this->web_model->insert_date("tbl_wallet_history", $insertdata);
                        $this->session->set_flashdata('success', 'Money debited successfully');
                    }
                }
                $this->user_wallet($userId);
            }
        }
    }


    function refund_req($userId)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->form_validation->set_rules('money', 'Money', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                redirect('web/refund');
            } else {
                $type = $this->input->post('type');
                $money = $this->input->post('money');
                $wallet_money = $this->web_model->wallet_user($userId);


                if ($type == "Refund") {
                    $insertdata = array(
                        "user_id" => $userId,
                        "money" => $money,
                        "trancaction_id" => "Added by Admin",
                        "type" => "Credit",
                        "p_type" => "Admin Add"
                    );
                    if (!$wallet_money) {
                        $insert = array(
                            "user_id" => $userId,
                            "money" => $money
                        );
                        $this->web_model->insert_date("tbl_wallet", $insert);
                    } else {
                        $insert = array(
                            "user_id" => $userId,
                            "money" => ($wallet_money->money + $money)
                        );
                        $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);
                    }

                    $this->web_model->insert_date("tbl_wallet_history", $insertdata);

                    $insertdata = array("status" => 1);

                    $this->web_model->editWeb_all("tbl_refund", $insertdata, $_POST['id']);
                    $this->session->set_flashdata('success', 'Money refunded in wallet successfully');
                } else {
                    $insertdata = array("status" => 2);
                    $this->web_model->editWeb_all("tbl_refund", $insertdata, $_POST['id']);
                    $this->session->set_flashdata('success', 'Request rejected successfully');
                }
                redirect('web/refund');
            }
        }
    }

    public function payout($money, $p_email)
    {
        define('PAYPAL_CLIENT_ID', 'AUY1AcXhGRUZlVdCi6-YAHHf4C_3GJlHKjkwK-l8_Ol7HJU9xFYj6t-ZwtK4mlnjMU-Qc7xKbZIghPwN');
        define('PAYPAL_SECRATE_KEY', 'EJTUV0bUgT4qNxIg1J3nNWihwPuJ37iRC9QhsY_Prpm0uW1PYtjp7JrNB9J1zZzia5knGFTQS7mI80pI');
        // Get access token from PayPal client Id and secrate key
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRATE_KEY);

        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Accept-Language: en_US";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $results = curl_exec($ch);
        $getresult = json_decode($results);


        // PayPal Payout API for Send Payment from PayPal to PayPal account
        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/payments/payouts");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $array = array(
            'sender_batch_header' => array(
                "sender_batch_id" => time(),
                "email_subject" => "You have a payout!",
                "email_message" => "You have received a payout."
            ),
            'items' => array(array(
                "recipient_type" => "EMAIL",
                "amount" => array(
                    "value" => $money,
                    "currency" => "USD"
                ),
                "note" => "Thanks for the payout!",
                "sender_item_id" => time(),
                // "receiver" => "xxxx"
                "receiver" => $p_email
            ))
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer $getresult->access_token";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $payoutResult = curl_exec($ch);
        //print_r($result);
        $getPayoutResult = json_decode($payoutResult);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        if (count($getPayoutResult->links) > 0) {
            return 0;
        } else {
            return 1;
        }
    }



    function with_req($userId)
    {
        if ($this->isAdmin() == FALSE) {
            $this->loadThis();
        } else {
            $this->form_validation->set_rules('money', 'Money', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                redirect('web/withdrawl');
            } else {
                $type = $this->input->post('type');
                $money = $this->input->post('money');
                $wallet_money = $this->web_model->wallet_user($userId);


                if ($type == "Send Via PayPal" || $type == "Send Via Bank") {
                    if ($type == "Send Via PayPal") {
                        $returnres = $this->payout($money, $_POST['p_email']);
                        if ($returnres == 1) {
                            $this->session->set_flashdata('error', 'Something went wrong. Please check paypal email is correct.');

                            redirect('web/withdrawl');
                        }
                    }

                    $insertdata = array(
                        "user_id" => $userId,
                        "money" => $money,
                        "trancaction_id" => "Debited by Admin",
                        "type" => "Debit",
                        "p_type" => "Admin Debit"
                    );
                    $insert = array(
                        "user_id" => $userId,
                        "money" => ($wallet_money->money - $money)
                    );
                    $this->web_model->editWeb_all("tbl_wallet", $insert, $wallet_money->id);

                    $this->web_model->insert_date("tbl_wallet_history", $insertdata);

                    $insertdata = array("status" => 1);

                    $this->web_model->editWeb_all("tbl_withdrawl", $insertdata, $_POST['id']);
                    $this->session->set_flashdata('success', 'Withdrawl done successfully');
                } else {
                    $insertdata = array("status" => 2);
                    $this->web_model->editWeb_all("tbl_withdrawl", $insertdata, $_POST['id']);
                    $this->session->set_flashdata('success', 'Withdrawl Request rejected successfully');
                }

                redirect('web/withdrawl');
            }
        }
    }
}
