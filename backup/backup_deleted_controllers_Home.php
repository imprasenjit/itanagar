<?php

namespace App\Controllers;

use App\Models\WebModel;

class Home extends BaseController
{
    protected WebModel $webModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->webModel = new WebModel();
    }

    public function index()
    {
        $data = [
            'games' => $this->webModel->home_web(6),
        ];
        return view('frontend/header') . view('frontend/index', $data) . view('frontend/footer');
    }
}
