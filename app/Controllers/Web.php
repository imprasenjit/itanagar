<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\WebModel;

class Web extends BaseController
{
    protected WebModel  $webModel;
    protected UserModel $userModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->webModel  = new WebModel();
        $this->userModel = new UserModel();
    }

    // ── Game Listing ──────────────────────────────────────────────────────────

    public function index()
    {
        $searchText         = esc($this->request->getPost('searchText') ?? '');
        $data['searchText'] = $searchText;
        $data['web']        = $this->webModel->get_allweb($searchText);
        $this->global['pageTitle'] = 'Lottery : Web Listing';
        return $this->loadViews('weblist', $this->global, $data, null);
    }

    // ── Add Game ──────────────────────────────────────────────────────────────

    public function addNew()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'Lottery : Add New Web';
        return $this->loadViews('web/addNew', $this->global, null, null);
    }

    public function addNewWeb()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        if (!$this->validate(['name' => 'required|max_length[128]'])) {
            return $this->addNew();
        }

        $name   = ucwords(strtolower(esc($this->request->getPost('name'))));
        $result = $this->webModel->addNewWeb(['name' => $name, 'createdDtm' => date('Y-m-d H:i:s')]);

        if ($result > 0) {
            $this->webModel->insert_date('tbl_ranges', ['web_id' => $result]);
            session()->setFlashdata('success', 'New Game created successfully');
        } else {
            session()->setFlashdata('error', 'Game creation failed');
        }
        return redirect()->to('web');
    }

    // ── Edit Game ─────────────────────────────────────────────────────────────

    public function edit(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }
        $data['userInfo'] = $this->webModel->getWebInfo($id);
        $this->global['pageTitle'] = 'Lottery : Edit Game';
        return $this->loadViews('web/editOld', $this->global, $data, null);
    }

    public function editWeb()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id = (int) $this->request->getPost('id');
        if (!$this->validate(['name' => 'required|max_length[128]'])) {
            return $this->edit($id);
        }

        $result = $this->webModel->editWebsite([
            'status'     => $this->request->getPost('status'),
            'name'       => ucwords(strtolower(esc($this->request->getPost('name')))),
            'updatedDtm' => date('Y-m-d H:i:s'),
        ], $id);

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Game updated successfully' : 'Game updation failed');
        return redirect()->to("ci/web/edit/$id");
    }

    public function deleteWeb()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $userId = (int) $this->request->getPost('userId');
        $result = $this->webModel->deleteWeb('tbl_webs', $userId);
        return $this->response->setJSON(['status' => $result > 0]);
    }

    // ── Game View ─────────────────────────────────────────────────────────────

    public function view(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }

        $data['WebInfo'] = $this->webModel->getWebInfo($id);
        if (!$data['WebInfo']) {
            return redirect()->to('web');
        }

        $data['RangeInfo'] = $this->webModel->getrangeInfo($id);
        $count  = $this->webModel->count_date($id);
        $pgData = $this->paginationCompress("web/view/$id/", $count, 10, 4);

        $data['userRecords'] = $this->webModel->list_date($id, $pgData['page'], $pgData['segment']);
        $data['pager']       = $pgData['pager'];
        $this->global['pageTitle'] = 'Lottery : Game View';
        return $this->loadViews('web/detail', $this->global, $data, null);
    }

    // ── Range Edit ────────────────────────────────────────────────────────────

    public function rangeEdit(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }
        $data = [
            'WebInfo'   => $this->webModel->getWebInfo($id),
            'rangeInfo' => $this->webModel->getrangeInfo($id),
        ];
        $this->global['pageTitle'] = 'Lottery : Edit Lottery Details';
        return $this->loadViews('web/rangeedit', $this->global, $data, null);
    }

    public function editRange()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $webId = (int) $this->request->getPost('web_id');
        $id    = (int) $this->request->getPost('id');

        $rangeInfo = [
            'price'       => $this->request->getPost('price'),
            'rangeStart'  => $this->request->getPost('rangeStart'),
            'priority'    => $this->request->getPost('priority'),
            'heading'     => $this->request->getPost('heading'),
            'result_date' => $this->request->getPost('result_date'),
            'jackpot'     => $this->request->getPost('jackpot'),
        ];

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $filename = time() . $logo->getClientName();
            $logo->move(FCPATH . '../public/imglogo/', $filename);
            $rangeInfo['logo'] = $filename;
        }

        $logo2 = $this->request->getFile('logo2');
        if ($logo2 && $logo2->isValid() && !$logo2->hasMoved()) {
            $filename2 = time() . $logo2->getClientName();
            $logo2->move(FCPATH . '../public/imglogo/', $filename2);
            $rangeInfo['logo2'] = $filename2;
        }

        $result = $this->webModel->editWeb_all('tbl_ranges', $rangeInfo, $id);
        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Range updated successfully' : 'Range updation failed');
        return redirect()->to("ci/web/rangeEdit/$webId");
    }

    // ── Common Settings ───────────────────────────────────────────────────────

    public function common()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $data['WebInfo'] = $this->webModel->getcommon();
        $this->global['pageTitle'] = 'Lottery : Edit Common Setting';
        return $this->loadViews('web/common', $this->global, $data, null);
    }

    public function editCommon()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $fields   = ['wallet_min', 'wallet_max', 'refund_min', 'refund_max', 'transfer_min', 'transfer_max', 'withdrawl_min', 'withdrawl_max'];
        $userInfo = [];
        foreach ($fields as $field) {
            $userInfo[$field] = $this->request->getPost($field);
        }

        $result = $this->webModel->editWeb_all('common', $userInfo, 1);
        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Settings updated successfully' : 'Range updation failed');
        return redirect()->to('web/common');
    }

    // ── Description Edit ─────────────────────────────────────────────────────

    public function descriptionEdit(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }
        $data = [
            'WebInfo'   => $this->webModel->getWebInfo($id),
            'rangeInfo' => $this->webModel->getrangeInfo($id),
        ];
        $this->global['pageTitle'] = 'Lottery : Edit Range Game';
        return $this->loadViews('web/descriptionedit', $this->global, $data, null);
    }

    public function editdesc()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id    = (int) $this->request->getPost('id');
        $webId = (int) $this->request->getPost('web_id');

        $result = $this->webModel->editWeb_all('tbl_ranges', [
            'play_description' => $this->request->getPost('play_description'),
            'when_play'        => $this->request->getPost('when_play'),
        ], $id);

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Description updated successfully' : 'Description updation failed');
        return redirect()->to("ci/web/descriptionEdit/$webId");
    }

    // ── Tier ─────────────────────────────────────────────────────────────────

    public function tier(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }
        $data = [
            'WebInfo' => $this->webModel->getWebInfo($id),
            'tier'    => $this->webModel->getTierInfo($id),
        ];
        $this->global['pageTitle'] = 'Lottery : Edit Tier Game';
        return $this->loadViews('web/tier', $this->global, $data, null);
    }

    public function addtier()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $webId = (int) $this->request->getPost('web_id');
        $type  = $this->request->getPost('type');

        $patternExist = $this->webModel->pattern_exist();
        if ($patternExist != 0) {
            session()->setFlashdata('error', 'This Pattern already exist in this Game');
        } else {
            $tierInfo = [
                'white'  => $this->request->getPost('white'),
                'per'    => $this->request->getPost('per'),
                'mega'   => $this->request->getPost('yellow'),
                'web_id' => $webId,
            ];

            if ($type === 'Add') {
                $this->webModel->insert_date('tbl_tier', $tierInfo);
                session()->setFlashdata('success', 'Prize Tier added successfully');
            } else {
                $id = (int) $this->request->getPost('id');
                $this->webModel->editWeb_all('tbl_tier', $tierInfo, $id);
                session()->setFlashdata('success', 'Prize Tier updated successfully');
            }
        }
        return redirect()->to("ci/web/tier/$webId");
    }

    // ── Draw Dates ────────────────────────────────────────────────────────────

    public function addNewWebdate(int $webId)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $dap   = date('Y-m-d', strtotime($this->request->getPost('date')));
        $count = $this->webModel->date_exist($webId, $dap);

        if ($count > 0) {
            session()->setFlashdata('error', 'This date has been already Taken.');
        } else {
            $this->webModel->insert_date('tbl_dates', [
                'date'     => $dap,
                'date_con' => $dap . ' ' . TIMEVAL,
                'web_id'   => $webId,
            ]);
            session()->setFlashdata('success', 'New Date added successfully');
        }
        return redirect()->to("ci/web/view/$webId");
    }

    public function addtwoWebdate(int $webId)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $dateArray = $this->_datearray($webId);
        $added     = 0;
        foreach (array_slice($dateArray, 0, 10) as $dap) {
            if ($this->webModel->date_exist($webId, $dap) == 0) {
                $this->webModel->insert_date('tbl_dates', [
                    'date'     => $dap,
                    'date_con' => $dap . ' ' . TIMEVAL,
                    'web_id'   => $webId,
                ]);
                $added++;
            }
        }
        session()->setFlashdata('success', "$added dates added successfully");
        return redirect()->to("ci/web/view/$webId");
    }

    public function deleteWebDate()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $userId = (int) $this->request->getPost('userId');
        $result = $this->webModel->deleteWeb('tbl_dates', $userId);
        return $this->response->setJSON(['status' => $result > 0]);
    }

    // ── CMS Pages ─────────────────────────────────────────────────────────────

    public function page()
    {
        $data['page'] = $this->webModel->page_list();
        $this->global['pageTitle'] = 'Lottery : Page Listing';
        return $this->loadViews('pagelist', $this->global, $data, null);
    }

    public function pageedit(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web');
        }
        $data['userInfo'] = $this->webModel->getallWebInfo('tbl_pages', $id);
        $this->global['pageTitle'] = 'Lottery : Edit Page';
        return $this->loadViews('web/pageedit', $this->global, $data, null);
    }

    public function editUpadtePage()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id     = (int) $this->request->getPost('id');
        $result = $this->webModel->editWeb_all('tbl_pages', [
            'description' => $this->request->getPost('description'),
        ], $id);

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Page updated successfully' : 'Page updation failed');
        return redirect()->to("ci/web/pageedit/$id");
    }
    // ── FAQ / Announcements ──────────────────────────────────────────────────

    public function faq()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText         = esc($this->request->getPost('searchText') ?? '');
        $data['searchText'] = $searchText;
        $data['web']        = $this->webModel->get_allfaq($searchText);
        $this->global['pageTitle'] = 'Lottery : Announcements';
        return $this->loadViews('faq', $this->global, $data, null);
    }

    public function addfaq()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'Lottery : Add Announcement';
        return $this->loadViews('web/addfaq', $this->global, null, null);
    }

    public function addNewfaq()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        if (!$this->validate(['question' => 'required', 'answer' => 'required'])) {
            return $this->addfaq();
        }

        $this->webModel->insert_date('tbl_faqs', [
            'question' => esc($this->request->getPost('question')),
            'answer'   => $this->request->getPost('answer'),
        ]);
        session()->setFlashdata('success', 'Announcement added successfully');
        return redirect()->to('web/faq');
    }

    public function faqedit(int $id = 0)
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        if ($id === 0) {
            return redirect()->to('web/faq');
        }
        $data['userInfo'] = $this->webModel->getfaq($id);
        $this->global['pageTitle'] = 'Lottery : Edit Announcement';
        return $this->loadViews('web/editfaq', $this->global, $data, null);
    }

    public function faqupdate()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id = (int) $this->request->getPost('id');
        $this->webModel->editWeb_all('tbl_faqs', [
            'question' => esc($this->request->getPost('question')),
            'answer'   => $this->request->getPost('answer'),
        ], $id);
        session()->setFlashdata('success', 'Announcement updated successfully');
        return redirect()->to('web/faq');
    }

    public function deletefaq()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $id     = (int) $this->request->getPost('userId');
        $result = $this->webModel->deleteWeb('tbl_faqs', $id);
        return $this->response->setJSON(['status' => $result > 0]);
    }
    // ── Contact Listing ───────────────────────────────────────────────────────

    public function contact_list()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $count  = $this->webModel->count_contact();
        $pgData = $this->paginationCompress('web/contact_list/', $count, 10, 3);

        $data = [
            'userRecords' => $this->webModel->contact_ls($pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'Lottery : Contact List';
        return $this->loadViews('web/contact', $this->global, $data, null);
    }

    // ── Admin Order Listing ───────────────────────────────────────────────────

    public function order()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count      = $this->webModel->order_list_count($searchText);
        $pgData     = $this->paginationCompress('web/order/', $count, 10, 3);

        $data = [
            'searchText' => $searchText,
            'orders'     => $this->webModel->order_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'      => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'Lottery : Order History';
        return $this->loadViews('web/order', $this->global, $data, null);
    }

    // ── Admin Order Management ────────────────────────────────────────────────

    public function confirm_order_by_admin()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $orderId = (int) $this->request->getPost('orderid');
        $this->webModel->update_order($orderId, ['order_status' => 1]);
        return $this->response->setJSON(['status' => true]);
    }

    public function release_order_by_admin()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('orderid');
        $result  = $this->webModel->get_order_by_id($orderId);

        if (!$result) {
            return $this->response->setJSON(['status' => false]);
        }

        $tickets = json_decode($result->tickets);
        $this->webModel->update_order($orderId, ['order_status' => 0, 'paid_status' => 'RELEASED']);

        $cleared = false;
        foreach ($tickets as $ticket) {
            $cleared = $this->webModel->clear_cart_data(
                (int)$result->user_id,
                $ticket->ticket_no,
                $ticket->web_id
            );
        }
        return $this->response->setJSON(['status' => (bool)$cleared]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function _datearray(int $webId): array
    {
        $dateArray = [];

        if ($webId === 1) {
            $friday  = date('Y-m-d', strtotime('next thursday'));
            $tuesday = date('Y-m-d', strtotime('next sunday'));
            $dateArray[] = $friday;
            for ($i = 1; $i <= 4; $i++) {
                $dateArray[] = date('Y-m-d', strtotime('+' . ($i * 7) . ' day', strtotime($friday)));
            }
            $dateArray[] = $tuesday;
            for ($i = 1; $i <= 4; $i++) {
                $dateArray[] = date('Y-m-d', strtotime('+' . ($i * 7) . ' day', strtotime($tuesday)));
            }
        } elseif (in_array($webId, [2, 3, 4, 5], true)) {
            $friday  = date('Y-m-d', strtotime('next wednesday'));
            $tuesday = date('Y-m-d', strtotime('next saturday'));
            $dateArray[] = $friday;
            for ($i = 1; $i <= 4; $i++) {
                $dateArray[] = date('Y-m-d', strtotime('+' . ($i * 7) . ' day', strtotime($friday)));
            }
            $dateArray[] = $tuesday;
            for ($i = 1; $i <= 4; $i++) {
                $dateArray[] = date('Y-m-d', strtotime('+' . ($i * 7) . ' day', strtotime($tuesday)));
            }
        } elseif ($webId === 6) {
            $tuesday = date('Y-m-d', strtotime('next saturday'));
            $dateArray[] = $tuesday;
            for ($i = 1; $i <= 10; $i++) {
                $dateArray[] = date('Y-m-d', strtotime('+' . ($i * 7) . ' day', strtotime($tuesday)));
            }
        }

        return $dateArray;
    }
}
