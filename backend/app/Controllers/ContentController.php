<?php

namespace App\Controllers;

use App\Models\ContentModel;

/**
 * ContentController — admin management of CMS pages, FAQs/announcements, and contact submissions.
 * All routes begin with "web/".
 */
class ContentController extends BaseController
{
    protected ContentModel $contentModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->contentModel = new ContentModel();
    }

    // ── CMS Pages ─────────────────────────────────────────────────────────────

    public function page()
    {
        $data['page'] = $this->contentModel->page_list();
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
        $data['userInfo'] = $this->contentModel->getallWebInfo('tbl_pages', $id);
        $this->global['pageTitle'] = 'event : Edit Page';
        return $this->loadViews('pages/web/pageedit', $this->global, $data, null);
    }

    public function editUpadtePage()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id     = (int) $this->request->getPost('id');
        $result = $this->contentModel->editWeb_all('tbl_pages', [
            'description' => $this->request->getPost('description'),
        ], $id);

        session()->setFlashdata($result ? 'success' : 'error', $result ? 'Page updated successfully' : 'Page updation failed');
        return redirect()->to("web/pageedit/$id");
    }

    // ── FAQ / Announcements ───────────────────────────────────────────────────

    public function faq()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }
        $searchText         = esc($this->request->getPost('searchText') ?? '');
        $data['searchText'] = $searchText;
        $data['web']        = $this->contentModel->get_allfaq($searchText);
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

        $this->contentModel->insert_date('tbl_faqs', [
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
        $data['userInfo'] = $this->contentModel->getfaq($id);
        $this->global['pageTitle'] = 'event : Edit Announcement';
        return $this->loadViews('pages/web/editfaq', $this->global, $data, null);
    }

    public function faqupdate()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $id = (int) $this->request->getPost('id');
        $this->contentModel->editWeb_all('tbl_faqs', [
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
        $result = $this->contentModel->deleteWeb('tbl_faqs', $id);
        return $this->response->setJSON(['status' => $result > 0]);
    }

    // ── Contact Listing ───────────────────────────────────────────────────────

    public function contact_list()
    {
        if ($this->isAdmin() === false) {
            return $this->loadThis();
        }

        $count  = $this->contentModel->count_contact();
        $pgData = $this->paginationCompress('web/contact_list/', $count, 10, 3);

        $data = [
            'userRecords' => $this->contentModel->contact_ls($pgData['page'], $pgData['segment']),
            'pager'       => $pgData['pager'],
        ];
        $this->global['pageTitle'] = 'event : Contact List';
        return $this->loadViews('pages/web/contact', $this->global, $data, null);
    }
}
