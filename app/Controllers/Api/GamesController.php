<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

class GamesController extends ApiBaseController
{
    // ── Public: Home & Games ─────────────────────────────────────────────────

    public function home()
    {
        // Fix: call home_web() once to avoid double query
        $allGames = $this->webModel->home_web();
        return $this->json([
            'games'   => array_slice($allGames, 0, 6),
            'faq'     => $this->webModel->faq(1),
            'results' => $this->webModel->result_list(null, null, 5),
            'stats'   => [
                'games' => count($allGames),
                'users' => $this->userModel->userListingCount(''),
            ],
        ]);
    }

    public function games()
    {
        return $this->json(['games' => $this->webModel->home_web()]);
    }

    public function game_detail(int $id)
    {
        $website = $this->webModel->getallWebInfo('tbl_webs', $id);
        if (!$website) {
            return $this->error('Game not found', 404);
        }
        $range = $this->webModel->getrangeInfo($id);
        return $this->json([
            'website'     => $website,
            'range'       => $range,
            'ticketRange' => $this->_getFirstAvailableTickets($range),
        ]);
    }

    public function game_tickets(int $web_id, int $start, int $end)
    {
        $available = [];
        for ($i = $start; $i <= $end; $i++) {
            if ($this->getTicketAvailability($i, $web_id)) {
                $available[] = $i;
            }
        }
        return $this->json(['tickets' => $available]);
    }

    public function ticket_search(int $web_id)
    {
        $body   = $this->getBody();
        $search = isset($body['search']) ? (int) $body['search'] : 0;

        $range = $this->webModel->getrangeInfo($web_id);
        if (!$range) {
            return $this->error('Game not found', 404);
        }
        $checkRange = $this->webModel->getRangeAvailability($search, $web_id);
        $available  = count($checkRange) > 0 && $this->getTicketAvailability($search, $web_id);
        return $this->json(['available' => $available, 'ticket' => $search]);
    }

    // ── Public: FAQ, Pages, Results, Contact ─────────────────────────────────

    public function faq()
    {
        return $this->json(['faqs' => $this->webModel->faq()]);
    }

    public function page(string $type)
    {
        $page = $this->webModel->page_detail($type);
        if (!$page) {
            return $this->error('Page not found', 404);
        }
        return $this->json(['page' => $page]);
    }

    public function results()
    {
        $webId   = $this->request->getGet('web_id');
        $date    = $this->request->getGet('date');
        $results = $this->webModel->result_list($webId ? (int) $webId : null, $date ?: null);
        $games   = $this->webModel->home_web();
        return $this->json(['results' => $results, 'games' => $games]);
    }

    public function contact()
    {
        $body    = $this->getBody();
        $name    = esc($body['name']    ?? '');
        $email   = esc($body['email']   ?? '');
        $mobile  = esc($body['mobile']  ?? '');
        $message = esc($body['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            return $this->error('Name, email, and message are required');
        }

        $this->webModel->insert_date('tbl_contact', [
            'name'    => $name,
            'email'   => $email,
            'mobile'  => $mobile,
            'message' => $message,
        ]);
        return $this->json([], true, 'Message sent successfully');
    }
}
