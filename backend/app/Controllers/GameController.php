<?php

namespace App\Controllers;

use App\Models\WebModel;

/**
 * GameController — admin CRUD for lottery games, ranges, descriptions, tiers and draw dates.
 * All routes begin with "web/".
 */
class GameController extends BaseController
{
    protected WebModel $webModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->webModel = new WebModel();
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
