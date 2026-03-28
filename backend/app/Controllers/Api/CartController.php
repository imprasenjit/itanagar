<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

class CartController extends ApiBaseController
{
    public function cart()
    {
        $userId = $this->getCartUserId();
        $cart   = $this->cartOrderModel->cart_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total]);
    }

    public function cart_add()
    {
        $body    = $this->getBody();
        $webId   = (int) ($body['web_id']  ?? 0);
        $tickets = $body['tickets'] ?? [];
        $userId  = $this->getCartUserId();

        if (empty($webId) || empty($tickets)) {
            return $this->error('web_id and tickets are required');
        }

        // Fix: null-check range before accessing ->price
        $range = $this->gameModel->getrangeInfo($webId);
        if (!$range) {
            return $this->error('Game range not found', 404);
        }

        $errors = [];
        $toAdd  = [];

        foreach ($tickets as $ticketNo) {
            $ticketNo = (int) $ticketNo;
            if (!$this->getTicketAvailability($ticketNo, $webId)) {
                $errors[] = "Ticket $ticketNo is not available";
                continue;
            }
            $cartRow = [
                'web_id'      => $webId,
                'user_id'     => $userId,
                'ticket_no'   => $ticketNo,
                'total_price' => $range->price,
            ];
            if (!$this->cartOrderModel->checkIfTicketAlreadyPresent($cartRow)) {
                $toAdd[] = $cartRow;
            }
        }

        if (!empty($toAdd)) {
            $this->cartOrderModel->insert_cart($toAdd);
        }

        return $this->json(['added' => count($toAdd), 'errors' => $errors], true, '');
    }

    public function cart_remove(int $cartId)
    {
        $userId = $this->getCartUserId();
        $this->cartOrderModel->delete_cart_item($cartId, $userId);
        return $this->json([], true, 'Removed');
    }

    public function order_confirm()
    {
        if ($r = $this->requireAuth()) return $r;
        $userId = (int) session()->get('userId');
        $cart   = $this->cartOrderModel->order_data($userId);
        $total  = array_reduce($cart, fn($c, $r) => $c + $r->total_price, 0);
        return $this->json(['cart' => $cart, 'total' => $total, 'isGuest' => false]);
    }
}

