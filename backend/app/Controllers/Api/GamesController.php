<?php

namespace App\Controllers\Api;

use App\Controllers\ApiBaseController;

class GamesController extends ApiBaseController
{
    // ── Public: Home & Games ─────────────────────────────────────────────────

    public function home()
    {
        $total = $this->gameModel->upcoming_games_count();
        return $this->json([
            'games'   => $this->gameModel->upcoming_games_paged(10, 0),
            'total'   => $total,
            'faq'     => $this->contentModel->faq(1),
            'results' => $this->cartOrderModel->result_list(null, null, 5),
            'stats'   => [
                'games' => $total,
                'users' => $this->userModel->userListingCount(''),
            ],
        ]);
    }

    public function upcoming_games()
    {
        $limit  = (int) ($this->request->getGet('limit')  ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $limit  = max(1, min($limit, 50)); // clamp to prevent abuse
        $total  = $this->gameModel->upcoming_games_count();
        return $this->json([
            'games'  => $this->gameModel->upcoming_games_paged($limit, $offset),
            'total'  => $total,
            'offset' => $offset,
            'limit'  => $limit,
        ]);
    }

    public function games()
    {
        return $this->json(['games' => $this->gameModel->home_web()]);
    }

    public function game_detail(int $id)
    {
        $website = $this->gameModel->getallWebInfo('tbl_webs', $id);
        if (!$website) {
            return $this->error('Game not found', 404);
        }
        $range = $this->gameModel->getrangeInfo($id);
        return $this->json([
            'website'     => $website,
            'range'       => $range,
            'ticketRange' => $this->_getFirstAvailableTickets($range),
        ]);
    }

    public function game_tickets(int $web_id, int $start, int $end)
    {
        $sold = $this->cartOrderModel->get_sold_tickets($web_id);
        $soldMap = array_flip($sold);
        $available = [];
        for ($i = $start; $i <= $end; $i++) {
            if (!isset($soldMap[$i])) {
                $available[] = $i;
            }
        }
        return $this->json(['tickets' => $available]);
    }

    public function ticket_search(int $web_id)
    {

        $body   = $this->getBody();
        $search = isset($body['ticket']) ? (int) $body['ticket'] : 0;

        $range = $this->gameModel->getrangeInfo($web_id);
        if (!$range) {
            return $this->error('Game not found', 404);
        }
        $checkRange = $this->gameModel->getRangeAvailability($search, $web_id);
        $available = $checkRange && $this->getTicketAvailability($search, $web_id);
        return $this->json(['available' => $available, 'ticket' => $search]);
    }

    // ── Public: FAQ, Pages, Results, Contact ─────────────────────────────────

    public function faq()
    {
        return $this->json(['faqs' => $this->contentModel->faq()]);
    }

    public function page(string $type)
    {
        $page = $this->contentModel->page_detail($type);
        if (!$page) {
            return $this->error('Page not found', 404);
        }
        return $this->json(['page' => $page]);
    }

    public function results()
    {
        $webId   = $this->request->getGet('web_id');
        $date    = $this->request->getGet('date');
        $results = $this->cartOrderModel->result_list($webId ? (int) $webId : null, $date ?: null);
        $games   = $this->gameModel->home_web();
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

        $this->contentModel->insert_date('tbl_contact', [
            'name'    => $name,
            'email'   => $email,
            'mobile'  => $mobile,
            'message' => $message,
        ]);
        return $this->json([], true, 'Message sent successfully');
    }
}

