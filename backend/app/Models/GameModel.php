<?php

namespace App\Models;

use CodeIgniter\Model;

class GameModel extends Model
{
    // ── Games / Webs ─────────────────────────────────────────────────────────

    public function weblist_count(string $search = ''): int
    {
        $builder = $this->db->table('tbl_webs');
        if (!empty($search)) {
            $builder->like('name', $search);
        }
        return $builder->countAllResults();
    }

    public function weblist_data(string $search = '', int $length = 10, int $start = 0)
    {
        $builder = $this->db->table('tbl_webs')
            ->select('id, name, status, createdDtm')
            ->orderBy('id', 'DESC');
        if (!empty($search)) {
            $builder->like('name', $search);
        }
        return $builder->limit($length, $start)->get()->getResult();
    }

    public function get_allweb(string $searchText = '', string $limit = '')
    {
        $builder = $this->db->table('tbl_webs')->select('tbl_webs.*');
        if (!empty($searchText)) {
            $builder->like('tbl_webs.name', $searchText);
        }
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function home_web(string $limit = '')
    {
        $sub     = '(SELECT date FROM `tbl_dates` WHERE web_id=tbl_webs.id AND date_con > "' . date('Y-m-d ') . '" ORDER BY date ASC LIMIT 1)';
        $soldSub = '(SELECT COUNT(*) FROM `tbl_cart` WHERE `tbl_cart`.`web_id` = `tbl_webs`.`id` AND `tbl_cart`.`paid_status` = 1)';
        $builder = $this->db->table('tbl_webs')
            ->select("tbl_webs.*, tbl_ranges.result_date, tbl_ranges.price, tbl_ranges.heading, tbl_ranges.logo, tbl_ranges.logo2, tbl_ranges.jackpot, tbl_ranges.quantity as totalTickets, $soldSub as soldTickets, $sub as date")
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('status', 'Active')
            ->orderBy('tbl_ranges.priority');
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function upcoming_games_count(): int
    {
        return $this->db->table('tbl_webs')
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('tbl_webs.status', 'Active')
            ->countAllResults();
    }

    public function upcoming_games_paged(int $limit = 10, int $offset = 0): array
    {
        $dateSub = '(SELECT date FROM `tbl_dates` WHERE web_id=tbl_webs.id AND date_con > "' . date('Y-m-d ') . '" ORDER BY date ASC LIMIT 1)';
        $soldSub = '(SELECT COUNT(*) FROM `tbl_cart` WHERE `tbl_cart`.`web_id` = `tbl_webs`.`id` AND `tbl_cart`.`paid_status` = 1)';
        return $this->db->table('tbl_webs')
            ->select("tbl_webs.*, tbl_ranges.result_date, tbl_ranges.price, tbl_ranges.heading, tbl_ranges.logo, tbl_ranges.logo2, tbl_ranges.jackpot, tbl_ranges.quantity as totalTickets, $soldSub as soldTickets, $dateSub as date")
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('tbl_webs.status', 'Active')
            ->orderBy('ISNULL(`date`) ASC, `date` ASC', '', false)
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    public function addNewWeb(array $data): int
    {
        $this->db->transStart();
        $this->db->table('tbl_webs')->insert($data);
        $insertId = $this->db->insertID();
        $this->db->transComplete();
        return $insertId;
    }

    public function getWebInfo(int $id)
    {
        return $this->db->table('tbl_webs')->where('id', $id)->get()->getRow();
    }

    public function getRangeAvailability(int $ticket, int $web_id): bool
    {
        $range = $this->getrangeInfo($web_id);
        if (!$range || empty($range->rangeStart)) {
            return false;
        }
        return isTicketInRange($ticket, $range->rangeStart);
    }

    public function getrangeInfo(int $id)
    {
        return $this->db->table('tbl_ranges')->where('web_id', $id)->get()->getRow();
    }

    public function getTierInfo(int $id)
    {
        return $this->db->table('tbl_tier')->where('web_id', $id)->get()->getRow();
    }

    public function tier(int $id)
    {
        return $this->getTierInfo($id);
    }

    public function getdateInfo(int $id)
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $id)
            ->where('date_con >', date('Y-m-d ' . TIMEVAL))
            ->orderBy('date')
            ->get()->getResult();
    }

    public function editWebsite(array $data, int $id): bool
    {
        $this->db->table('tbl_webs')->where('id', $id)->update($data);
        return true;
    }

    public function deleteWeb(string $table, int $id): int
    {
        $this->db->table($table)->where('id', $id)->delete();
        return $this->db->affectedRows();
    }

    // ── Dates ────────────────────────────────────────────────────────────────

    public function count_date(int $web_id): int
    {
        return $this->db->table('tbl_dates')->where('web_id', $web_id)->get()->getNumRows();
    }

    public function list_date(int $web_id, int $limit, int $offset)
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $web_id)
            ->orderBy('date', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function date_exist(int $web_id, string $date): int
    {
        return $this->db->table('tbl_dates')
            ->where('web_id', $web_id)
            ->where('date', $date)
            ->get()->getNumRows();
    }

    // ── event ────────────────────────────────────────────────────────────────

    public function event_web(string $limit = '')
    {
        $sub = '(SELECT date FROM `tbl_dates` WHERE web_id=tbl_webs.id AND date_con > "' . date('Y-m-d ') . '" ORDER BY date ASC LIMIT 1)';
        $builder = $this->db->table('tbl_webs')
            ->select("tbl_webs.*, tbl_ranges.result_date, tbl_ranges.price, tbl_ranges.heading, tbl_ranges.logo, tbl_ranges.logo2, tbl_ranges.jackpot, $sub as date")
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id')
            ->where('status', 'Active')
            ->orderBy('tbl_ranges.priority');
        if (!empty($limit)) {
            $builder->limit((int)$limit);
        }
        return $builder->get()->getResult();
    }

    public function upcoming_events(int $limit = 5)
    {
        return $this->db->table('tbl_webs')
            ->select('tbl_webs.id, tbl_webs.name, tbl_ranges.price, tbl_ranges.jackpot, tbl_ranges.result_date')
            ->join('tbl_ranges', 'tbl_ranges.web_id = tbl_webs.id', 'left')
            ->where('tbl_webs.status', 'Active')
            ->where('tbl_ranges.result_date >=', date('Y-m-d'))
            ->orderBy('tbl_ranges.result_date', 'ASC')
            ->limit($limit)
            ->get()->getResult();
    }

    // ── Generic utilities (used for game-domain tables) ───────────────────────

    public function insert_date(string $table, array $data): int
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function editWeb_all(string $table, array $data, int $id): bool
    {
        $this->db->table($table)->where('id', $id)->update($data);
        return true;
    }

    public function getallWebInfo(string $table, int $id)
    {
        return $this->db->table($table)->where('id', $id)->get()->getRow();
    }
}
