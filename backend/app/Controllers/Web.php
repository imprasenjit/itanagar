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
        $this->global['pageTitle'] = 'event : Web Listing';
        return $this->loadViews('pages/weblist', $this->global, [], null);
    }

    // ── Game Listing DataTable AJAX source ────────────────────────────────────

    public function weblist_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['data' => []]);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->webModel->weblist_count();
        $filtered = $this->webModel->weblist_count($search);
        $rows     = $this->webModel->weblist_data($search, $length, $start);

        $data = [];
        foreach ($rows as $row) {
            $badge = $row->status === 'Active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">' . esc($row->status) . '</span>';

            $actions =
                '<a class="btn btn-sm btn-primary" href="' . base_url('web/edit/' . $row->id) . '" title="Edit"><i class="bi bi-pencil-fill"></i></a> '
                . '<a class="btn btn-sm btn-info" href="' . base_url('web/rangeEdit/' . $row->id) . '" title="Details"><i class="bi bi-gear-fill"></i> Details</a> '
                . '<a class="btn btn-sm btn-secondary" href="' . base_url('web/descriptionEdit/' . $row->id) . '" title="Description"><i class="bi bi-text-left"></i> Desc</a> '
                . '<a class="btn btn-sm btn-danger deleteWeb" href="#" data-userid="' . $row->id . '" title="Delete"><i class="bi bi-trash3-fill"></i></a>';

            $data[] = [
                'name'       => esc($row->name),
                'status'     => $badge,
                'createdDtm' => date('d-m-Y', strtotime($row->createdDtm)),
                'actions'    => $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    // ── Add Game ──────────────────────────────────────────────────────────────

    public function addNew()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'event : Add New Web';
        return $this->loadViews('pages/web/addNew', $this->global, null, null);
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
        $this->global['pageTitle'] = 'event : Edit Game';
        return $this->loadViews('pages/web/editOld', $this->global, $data, null);
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
        return redirect()->to("web/edit/$id");
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
        $this->global['pageTitle'] = 'event : Game View';
        return $this->loadViews('pages/web/detail', $this->global, $data, null);
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

        $data['WebInfo'] = $this->webModel->getWebInfo($id);
        if (!$data['WebInfo']) {
            return redirect()->to('web');
        }

        $data['rangeInfo'] = $this->webModel->getrangeInfo($id);
        if (!$data['rangeInfo']) {
            session()->setFlashdata('error', 'Range data not found for this game');
            return redirect()->to('web');
        }

        $this->global['pageTitle'] = 'event : Edit event Details';
        return $this->loadViews('pages/web/rangeedit', $this->global, $data, null);
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
        return redirect()->to("web/rangeEdit/$webId");
    }

    // ── Common Settings ───────────────────────────────────────────────────────

    public function common()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $data['WebInfo'] = $this->webModel->getcommon();
        $this->global['pageTitle'] = 'event : Edit Common Setting';
        return $this->loadViews('pages/web/common', $this->global, $data, null);
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

        $data['WebInfo'] = $this->webModel->getWebInfo($id);
        if (!$data['WebInfo']) {
            return redirect()->to('web');
        }

        $data['rangeInfo'] = $this->webModel->getrangeInfo($id);
        if (!$data['rangeInfo']) {
            session()->setFlashdata('error', 'Range data not found for this game');
            return redirect()->to('web');
        }

        $this->global['pageTitle'] = 'event : Edit Range Game';
        return $this->loadViews('pages/web/descriptionedit', $this->global, $data, null);
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
        return redirect()->to("web/descriptionEdit/$webId");
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
        $this->global['pageTitle'] = 'event : Edit Tier Game';
        return $this->loadViews('pages/web/tier', $this->global, $data, null);
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
        return redirect()->to("web/tier/$webId");
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
        return redirect()->to("web/view/$webId");
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
        return redirect()->to("web/view/$webId");
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
        $this->global['pageTitle'] = 'event : Page Listing';
        return $this->loadViews('pages/pagelist', $this->global, $data, null);
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
        $this->global['pageTitle'] = 'event : Edit Page';
        return $this->loadViews('pages/web/pageedit', $this->global, $data, null);
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
        return redirect()->to("web/pageedit/$id");
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
        $this->global['pageTitle'] = 'event : Announcements';
        return $this->loadViews('pages/faq', $this->global, $data, null);
    }

    public function addfaq()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'event : Add Announcement';
        return $this->loadViews('pages/web/addfaq', $this->global, null, null);
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
        $this->global['pageTitle'] = 'event : Edit Announcement';
        return $this->loadViews('pages/web/editfaq', $this->global, $data, null);
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
        $this->global['pageTitle'] = 'event : Contact List';
        return $this->loadViews('pages/web/contact', $this->global, $data, null);
    }

    // ── Admin Order Listing ───────────────────────────────────────────────────

    public function order()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $this->global['pageTitle'] = 'event : Order History';
        return $this->loadViews('pages/web/order', $this->global, [], null);
    }

    public function order_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 10);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $total    = $this->webModel->order_list_count('');
        $filtered = $this->webModel->order_list_count($search);
        $orders   = $this->webModel->order_list($search, $length, $start);

        $webCache = [];
        $data     = [];

        foreach ($orders as $o) {
            $rawTickets = json_decode($o->tickets ?? '[]', true) ?: [];
            $enriched   = [];
            foreach ($rawTickets as $t) {
                $webId = (int)($t['web_id'] ?? 0);
                if (!isset($webCache[$webId])) {
                    $webInfo          = $this->webModel->getWebInfo($webId);
                    $webCache[$webId] = $webInfo ? esc($webInfo->name) : 'Unknown';
                }
                $enriched[] = [
                    'game'      => $webCache[$webId],
                    'ticket_no' => esc($t['ticket_no'] ?? ''),
                ];
            }

            $count = count($enriched);
            if ($o->order_status == '1') {
                $badge = '<span class="badge bg-success">Confirmed</span>';
            } elseif ((string)$o->paid_status === '0') {
                $badge = '<span class="badge bg-warning text-dark">Pending</span>';
            } else {
                $badge = '<span class="badge bg-danger">Failed</span>';
            }

            $data[] = [
                'DT_RowId'     => 'order-' . $o->id,
                'raw_id'       => $o->id,
                'paid_status'  => $o->paid_status,
                'order_status' => $o->order_status,
                'order_no'     => '<strong>#' . $o->id . '</strong>',
                'user'         => '<div>' . esc($o->uname ?? '') . '</div><small class="text-muted">' . esc($o->email ?? '') . '</small>',
                'ticket_info'  => '<button class="btn btn-sm btn-outline-primary expand-tickets" type="button"><i class="bi bi-ticket-perforated me-1"></i>' . $count . ' ticket' . ($count !== 1 ? 's' : '') . '</button>',
                'tickets'      => $enriched,
                'amount'       => '₹' . number_format((float)$o->total_price, 2),
                'payment'      => 'UPI',
                'order_id'     => '<small class="text-muted">' . esc($o->razorpay_order_id ?? '') . '</small>',
                'date'         => date('M d, Y h:i a', strtotime($o->createdAt)),
                'status'       => $badge,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
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

    // ── Transactions ──────────────────────────────────────────────────────────

    // ── Dashboard Stats (AJAX source for stat cards + upcoming events) ────────

    public function dashboard_stats()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $upcoming     = $this->webModel->upcoming_events(5);
        $upcomingData = [];
        foreach ($upcoming as $ev) {
            $upcomingData[] = [
                'name'        => esc($ev->name),
                'result_date' => $ev->result_date ? date('d M Y', strtotime($ev->result_date)) : '—',
                'jackpot'     => number_format((float)($ev->jackpot ?? 0), 0),
            ];
        }

        return $this->response->setJSON([
            'status'           => 'ok',
            'totalweb'         => $this->userModel->count_record('tbl_webs'),
            'totaluser'        => $this->userModel->count_record('tbl_users'),
            'totalTicketsSold' => $this->webModel->total_tickets_sold(),
            'totalRevenue'     => number_format($this->webModel->total_revenue(), 2),
            'todayRevenue'     => number_format($this->webModel->today_revenue(), 2),
            'todayOrders'      => $this->webModel->today_orders(),
            'upcomingEvents'   => $upcomingData,
        ]);
    }

    // ── Dashboard Recent Transactions (DataTables AJAX source) ────────────────

    public function dashboard_txn_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['data' => []]);
        }

        $draw   = (int) ($this->request->getGet('draw') ?? 1);
        $start  = (int) ($this->request->getGet('start') ?? 0);
        $length = (int) ($this->request->getGet('length') ?? 10);
        $search = $this->request->getGet('search')['value'] ?? '';

        $total    = $this->webModel->txn_count(null, null, null, 'PAID', $search);
        $filtered = $total;
        $rows     = $this->webModel->txn_list(null, null, null, 'PAID', $search, $length, $start);

        $data = [];
        foreach ($rows as $txn) {
            $data[] = [
                'id'       => '<a href="' . base_url('web/tickets') . '">#' . $txn->id . '</a>',
                'user'     => '<div class="fw-semibold">' . esc($txn->user_name ?? '—') . '</div><small class="text-muted">' . esc($txn->user_email ?? '') . '</small>',
                'event'    => esc($txn->web_name ?? '—'),
                'amount'   => '₹' . number_format((float)$txn->total_price, 2),
                'date'     => date('d M Y', strtotime($txn->createdAt)),
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function transactions_data()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['error' => 'access']);
        }

        $draw   = (int)($this->request->getGet('draw') ?? 1);
        $start  = (int)($this->request->getGet('start') ?? 0);
        $length = (int)($this->request->getGet('length') ?? 20);
        $search = trim($this->request->getGet('search')['value'] ?? '');

        $webId    = $this->request->getGet('web_id')    ? (int)$this->request->getGet('web_id') : null;
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo   = $this->request->getGet('date_to')   ?? '';
        $status   = $this->request->getGet('status')    ?? '';

        $total    = $this->webModel->txn_count(null, null, null, null, '');
        $filtered = $this->webModel->txn_count($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search);
        $rows     = $this->webModel->txn_list($webId, $dateFrom ?: null, $dateTo ?: null, $status !== '' ? $status : null, $search, $length, $start);

        $statusMap = [
            'PAID'      => ['success', 'Paid'],
            'RELEASED'  => ['info',    'Released'],
            'CANCELLED' => ['danger',  'Cancelled'],
            '0'         => ['warning', 'Unpaid'],
            '1'         => ['success', 'Paid'],
            '2'         => ['danger',  'Failed'],
        ];

        $data = [];
        foreach ($rows as $txn) {
            $tickets   = json_decode($txn->tickets ?? '[]', true) ?: [];
            $ticketNos = array_column($tickets, 'ticket_no');
            if (!empty($ticketNos)) {
                $shown      = array_slice($ticketNos, 0, 3);
                $rest       = count($ticketNos) - 3;
                $ticketHtml = esc(implode(', ', $shown));
                if ($rest > 0) {
                    $ticketHtml .= ' <span class="text-muted">+' . $rest . ' more</span>';
                }
            } else {
                $ticketHtml = esc($txn->transaction_id ?? '—');
            }

            [$badgeColor, $statusLabel] = $statusMap[(string)$txn->paid_status] ?? ['secondary', esc($txn->paid_status)];

            $data[] = [
                'order_no' => '<strong>#' . $txn->id . '</strong>',
                'date'     => '<small>' . date('d M Y, H:i', strtotime($txn->createdAt)) . '</small>',
                'event'    => esc($txn->web_name ?? '—'),
                'user'     => '<div>' . esc($txn->user_name ?? '—') . '</div><small class="text-muted">' . esc($txn->user_email ?? '') . '</small>',
                'tickets'  => '<small class="text-muted">' . $ticketHtml . '</small>',
                'amount'   => '₹' . number_format((float)$txn->total_price, 2),
                'status'   => '<span class="badge bg-' . $badgeColor . '">' . $statusLabel . '</span>',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function transactions()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $webId    = $this->request->getGet('web_id') ? (int)$this->request->getGet('web_id') : null;
        $dateFrom = esc($this->request->getGet('date_from') ?? '');
        $dateTo   = esc($this->request->getGet('date_to')   ?? '');
        $status   = $this->request->getGet('status') ?? '';
        $search   = esc($this->request->getGet('search') ?? '');

        $data = [
            'games'   => $this->webModel->get_allweb(),
            'filters' => compact('webId', 'dateFrom', 'dateTo', 'status', 'search'),
        ];
        $this->global['pageTitle'] = 'event : Transactions';
        return $this->loadViews('pages/web/transactions', $this->global, $data, null);
    }

    // ── Tickets Management ────────────────────────────────────────────────────

    public function tickets()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $search = esc($this->request->getGet('search') ?? '');
        $status = $this->request->getGet('status') ?? '';

        $count  = $this->webModel->ticket_list_count($search, $status !== '' ? $status : null);
        $pgData = $this->paginationCompress('web/tickets/', $count, 20, 3);

        $data = [
            'tickets'  => $this->webModel->ticket_list($search, $status !== '' ? $status : null, $pgData['page'], $pgData['segment']),
            'pager'    => $pgData['pager'],
            'filters'  => compact('search', 'status'),
            'total'    => $count,
        ];
        $this->global['pageTitle'] = 'event : Ticket Management';
        return $this->loadViews('pages/web/tickets', $this->global, $data, null);
    }

    public function ticket_cancel()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }
        $orderId = (int) $this->request->getPost('order_id');
        $this->webModel->cancel_ticket($orderId);
        return $this->response->setJSON(['status' => true, 'message' => 'Ticket cancelled successfully']);
    }

    public function ticket_resend()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $order   = $this->webModel->get_order_by_id($orderId);

        if (!$order) {
            return $this->response->setJSON(['status' => false, 'message' => 'Order not found']);
        }

        $userModel = new \App\Models\UserModel();
        $user      = $userModel->getUserInfoById((int) $order->user_id);

        if (!$user) {
            return $this->response->setJSON(['status' => false, 'message' => 'User not found']);
        }

        helper('email_helper');
        $tickets = json_decode($order->tickets ?? '[]', true);
        $emailData = [
            'name'           => $user->name,
            'email'          => $user->email,
            'order_id'       => $order->id,
            'transaction_id' => $order->transaction_id,
            'total_price'    => $order->total_price,
            'tickets'        => $tickets,
        ];

        $sent = sendmail($user->email, 'Your Ticket Confirmation', view('email/contact', $emailData, true));
        return $this->response->setJSON(['status' => (bool) $sent, 'message' => $sent ? 'Ticket resent successfully' : 'Failed to resend ticket']);
    }

    public function ticket_verify()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $ticketRef = esc($this->request->getPost('ticket_ref') ?? '');
        if (empty($ticketRef)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ticket reference required']);
        }

        // Search by order ID or transaction ID
        $order = null;
        if (is_numeric($ticketRef)) {
            $order = $this->webModel->get_order_by_id((int) $ticketRef);
        }
        if (!$order) {
            $order = $this->webModel->get_order_by_orderId($ticketRef);
        }

        if (!$order) {
            return $this->response->setJSON(['status' => false, 'message' => 'No ticket found for this reference']);
        }

        $userModel = new \App\Models\UserModel();
        $user      = $userModel->getUserInfoById((int) $order->user_id);
        $tickets   = json_decode($order->tickets ?? '[]', true);

        return $this->response->setJSON([
            'status'  => true,
            'valid'   => $order->paid_status === 'PAID',
            'order'   => [
                'id'             => $order->id,
                'paid_status'    => $order->paid_status,
                'total_price'    => $order->total_price,
                'transaction_id' => $order->transaction_id,
                'createdAt'      => $order->createdAt,
            ],
            'user'    => $user ? ['name' => $user->name, 'email' => $user->email] : null,
            'tickets' => $tickets,
        ]);
    }

    // ── Reports ────────────────────────────────────────────────────────────────

    public function reports()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $data['games'] = $this->webModel->get_allweb();
        $this->global['pageTitle'] = 'event : Reports';
        return $this->loadViews('pages/web/reports', $this->global, $data, null);
    }

    public function report_download()
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }

        $type  = $this->request->getGet('type') ?? 'daily';
        $date  = $this->request->getGet('date') ?? date('Y-m-d');
        $webId = $this->request->getGet('web_id') ? (int) $this->request->getGet('web_id') : null;
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));

        switch ($type) {
            case 'event':
                $rows     = $webId ? $this->webModel->report_event($webId) : [];
                $filename = 'report_event_' . $webId . '_' . date('Ymd') . '.csv';
                break;
            case 'monthly':
                $rows     = $this->webModel->report_monthly($year, $month);
                $filename = 'report_monthly_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv';
                break;
            default:
                $rows     = $this->webModel->report_daily($date);
                $filename = 'report_daily_' . $date . '.csv';
                break;
        }

        $totalRevenue = array_sum(array_column((array) $rows, 'total_price'));

        $output  = "Order ID,Date,Event,User Name,User Email,Transaction ID,Amount,Status\n";
        foreach ($rows as $row) {
            $output .= implode(',', [
                '#' . $row->id,
                date('d-m-Y H:i', strtotime($row->createdAt)),
                '"' . str_replace('"', '""', $row->web_name ?? '') . '"',
                '"' . str_replace('"', '""', $row->user_name ?? '') . '"',
                $row->user_email ?? '',
                $row->transaction_id ?? '',
                $row->total_price,
                $row->paid_status,
            ]) . "\n";
        }
        $output .= "\nTotal Revenue,," . $totalRevenue . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $output); // BOM for Excel UTF-8
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    // ── Admin Wallet History ───────────────────────────────────────────────────

    public function wallet()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_wallet_count($searchText);
        $pgData = $this->paginationCompress('web/wallet/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_wallet_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Wallet History';
        return $this->loadViews('pages/web/wallet', $this->global, $data, null);
    }

    // ── Admin Winners ──────────────────────────────────────────────────────────

    public function winner()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_winner_count($searchText);
        $pgData = $this->paginationCompress('web/winner/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'amount'      => $this->webModel->admin_winner_total(),
            'userRecords' => $this->webModel->admin_winner_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Winner History';
        return $this->loadViews('pages/web/winner', $this->global, $data, null);
    }

    // ── Admin Refund ───────────────────────────────────────────────────────────

    public function refund()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_refund_count($searchText);
        $pgData = $this->paginationCompress('web/refund/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_refund_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Refund Requests';
        return $this->loadViews('pages/web/refund', $this->global, $data, null);
    }

    public function refund_req(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }
        $id    = (int) $this->request->getPost('id');
        $money = (float) $this->request->getPost('money');
        $type  = $this->request->getPost('type');
        $this->webModel->refund_process($id, $type, $userId, $money);
        return redirect()->to('web/refund')
            ->with('success', $type === 'Refund' ? 'Refund processed successfully.' : 'Refund request rejected.');
    }

    // ── Admin Withdrawal ───────────────────────────────────────────────────────

    public function withdrawl()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_withdrawl_count($searchText);
        $pgData = $this->paginationCompress('web/withdrawl/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_withdrawl_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Withdrawal Requests';
        return $this->loadViews('pages/web/withdrawl', $this->global, $data, null);
    }

    public function with_req(int $userId = 0)
    {
        if ($this->isAdmin() === false) {
            return redirect()->to('dashboard');
        }
        $id   = (int) $this->request->getPost('id');
        $type = $this->request->getPost('type');
        $this->webModel->withdrawl_process($id, $type);
        return redirect()->back()->with('success', $type === 'Reject' ? 'Request rejected.' : 'Request processed successfully.');
    }

    // ── Admin PayPal Transfer ──────────────────────────────────────────────────

    public function transfer()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText = esc($this->request->getPost('searchText') ?? '');
        $count  = $this->webModel->admin_transfer_count($searchText);
        $pgData = $this->paginationCompress('web/transfer/', $count, 20, 3);
        $data = [
            'searchText'  => $searchText,
            'userRecords' => $this->webModel->admin_transfer_list($searchText, $pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : PayPal Transfer Requests';
        return $this->loadViews('pages/web/transfer', $this->global, $data, null);
    }

    // ── Migrations ────────────────────────────────────────────────────────────

    public function migrations(): string
    {
        if (! $this->isAdmin()) {
            return $this->loadThis();
        }

        $migrate   = \Config\Services::migrations();
        $db        = \Config\Database::connect();

        // All migration files on disk
        $allFiles  = $migrate->findMigrations();

        // History rows already run (from `migrations` table)
        $history   = [];
        $ranByClass = [];
        if ($db->tableExists('migrations')) {
            $rows = $db->table('migrations')->orderBy('batch', 'ASC')->orderBy('time', 'ASC')->get()->getResult();
            foreach ($rows as $row) {
                $history[] = $row;
                // Key by short class name only (strip namespace) so it matches findMigrations()
                $shortKey = preg_replace('/^.*\\\\/', '', ltrim($row->class, '\\'));
                $ranByClass[$shortKey] = $row;
            }
        }

        $migrations = [];
        foreach ($allFiles as $file) {
            // $file is a stdClass with properties: class, path, namespace, version
            $shortClass  = ltrim(preg_replace('/^.*\\\\/', '', $file->class ?? ''), '\\');
            $ran         = isset($ranByClass[$shortClass]);
            $historyRow  = $ranByClass[$shortClass] ?? null;

            // Human-readable description from class name e.g. CreatePermissionsTables → Create Permissions Tables
            $description = trim(preg_replace('/([A-Z])/', ' $1', $shortClass));

            $migrations[] = [
                'file'        => $shortClass,
                'description' => $description,
                'ran'         => $ran,
                'batch'       => $historyRow->batch ?? null,
                'run_at'      => $historyRow ? date('Y-m-d H:i:s', $historyRow->time) : null,
            ];
        }

        $ranCount     = count(array_filter($migrations, fn($m) => $m['ran']));
        $pendingCount = count($migrations) - $ranCount;

        $data = [
            'migrations'   => $migrations,
            'history'      => $history,
            'totalCount'   => count($migrations),
            'ranCount'     => $ranCount,
            'pendingCount' => $pendingCount,
        ];
        $this->global['pageTitle'] = 'Itanagarchoice : Migrations';
        return $this->loadViews('pages/migrations', $this->global, $data, null);
    }

    public function runMigrations(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->isAdmin()) {
            return redirect()->to(base_url('web/migrations'));
        }
        try {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            session()->setFlashdata('success', 'All pending migrations have been run successfully.');
        } catch (\Throwable $e) {
            session()->setFlashdata('error', 'Migration failed: ' . $e->getMessage());
        }
        return redirect()->to(base_url('web/migrations'));
    }

    // ── RBAC ─────────────────────────────────────────────────────────────────

    public function rbac(): string
    {
        if (! $this->isAdmin()) {
            return $this->loadThis();
        }

        // Auto-run migration + seed if tables don't exist yet
        $db = \Config\Database::connect();
        if (! $db->tableExists('tbl_permissions')) {
            $migrate = \Config\Services::migrations();
            $migrate->latest();
            \Config\Database::seeder()->call('PermissionsSeeder');
        }

        $model = model(\App\Models\RolePermissionModel::class);
        $data  = [
            'roles'       => $model->getAllRoles(),
            'permissions' => $model->getAllPermissions(),
            'assigned'    => $model->getAllAssigned(),
        ];
        $this->global['pageTitle'] = 'Itanagarchoice : Role Permissions';
        return $this->loadViews('pages/rbac', $this->global, $data, null);
    }

    public function rbacSave(): void
    {
        if (! $this->isAdmin()) {
            echo json_encode(['status' => 'access']);
            return;
        }
        $roleId = (int) $this->request->getPost('role_id');
        if ($roleId <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid role']);
            return;
        }
        $model       = model(\App\Models\RolePermissionModel::class);
        $validKeys   = array_column($model->getAllPermissions(), 'key');
        $rawKeys     = (array) ($this->request->getPost('permissions') ?? []);
        $cleanKeys   = array_values(array_intersect($rawKeys, $validKeys));

        $model->saveRolePermissions($roleId, $cleanKeys);

        // Bust the cached permissions for any active session of this role
        // (users will get fresh data on their next request)
        session()->remove('permissions');

        echo json_encode(['status' => 'ok']);
    }

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
