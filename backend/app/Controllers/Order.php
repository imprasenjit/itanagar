<?php

namespace App\Controllers;

use App\Models\WebModel;

class Order extends BaseController
{
    protected WebModel $webModel;

    protected $helpers = ['url', 'cias_helper'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->isLoggedIn();
        $this->webModel = new WebModel();
    }

    /**
     * Release a failed/pending order — admin-only AJAX action.
     * Clears the cart and marks the order as RELEASED.
     */
    public function release_order()
    {
        if ($this->isAdmin() === false) {
            return $this->response->setJSON(['status' => 'access']);
        }

        $orderId = $this->request->getPost('orderid');
        $result  = $this->webModel->get_order_by_id((int)$orderId);

        if (!$result) {
            return $this->response->setJSON(['status' => false]);
        }

        $tickets = json_decode($result->tickets);
        $this->webModel->update_order((int)$orderId, [
            'order_status' => 0,
            'paid_status'  => 'RELEASED',
        ]);

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
}
