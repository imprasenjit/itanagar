<?php

namespace App\Controllers;

use App\Models\WebModel;

class Page extends BaseController
{
    protected WebModel $webModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->webModel = new WebModel();
    }

    public function index(string $type = 'about')
    {
        $data['pageData'] = $this->webModel->page_detail($type);
        return view('frontend/header') . view('frontend/page', $data) . view('frontend/footer');
    }

    public function faq()
    {
        $data['faqs'] = $this->webModel->faq();
        return view('frontend/header') . view('frontend/faq', $data) . view('frontend/footer');
    }

    public function refunds_cacellations()
    {
        $data['pageData'] = $this->webModel->page_detail('refund');
        return view('frontend/header') . view('frontend/page', $data) . view('frontend/footer');
    }

    public function contact()
    {
        return view('frontend/header') . view('frontend/contact') . view('frontend/footer');
    }

    public function contact_save()
    {
        $rules = [
            'name'    => 'required|max_length[128]',
            'email'   => 'required|valid_email|max_length[128]',
            'message' => 'required',
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode(' ', $this->validator->getErrors()));
            return $this->contact();
        }

        $this->webModel->insert_date('tbl_contact', [
            'name'       => esc($this->request->getPost('name')),
            'email'      => esc($this->request->getPost('email')),
            'mobile'     => esc($this->request->getPost('mobile') ?? ''),
            'message'    => esc($this->request->getPost('message')),
            'createdDtm' => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('success', 'Your message has been sent successfully.');
        return redirect()->to('contact');
    }
}
