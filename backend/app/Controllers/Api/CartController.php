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

        $errors        = [];
        $toAdd         = [];
        $reservedUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        foreach ($tickets as $ticketNo) {
            $ticketNo = (int) $ticketNo;

            if (!$this->gameModel->getRangeAvailability($ticketNo, $webId)) {
                $errors[] = "Ticket $ticketNo is out of range";
                continue;
            }

            // Exclude the current user so refreshing their own cart doesn't
            // report their own hold as "taken".
            if (!$this->getTicketAvailability($ticketNo, $webId, $userId)) {
                $errors[] = "Ticket $ticketNo is not available";
                continue;
            }

            // Remove any stale expired hold by another user on this exact ticket
            // so the UNIQUE KEY won't block the coming INSERT.
            $this->cartOrderModel->release_expired_for_ticket($ticketNo, $webId, $userId);

            $cartRow = [
                'web_id'         => $webId,
                'user_id'        => $userId,
                'ticket_no'      => $ticketNo,
                'total_price'    => $range->price,
                'reserved_until' => $reservedUntil,   // 15-minute hold
            ];

            // Pre-check prevents a noisy duplicate-key error; with the DB UNIQUE
            // constraint this also acts as a safety net for concurrent requests.
            if (!$this->cartOrderModel->checkIfTicketAlreadyPresent($cartRow)) {
                $toAdd[] = $cartRow;
            }
        }

        if (!empty($toAdd)) {
            try {
                $this->cartOrderModel->insert_cart($toAdd);
            } catch (\Exception $e) {
                // A concurrent INSERT from another session hit the UNIQUE constraint.
                // Report all tickets as unavailable rather than crashing.
                foreach ($toAdd as $row) {
                    $errors[] = "Ticket {$row['ticket_no']} is not available";
                }
                $toAdd = [];
            }
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

