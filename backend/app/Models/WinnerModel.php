<?php

namespace App\Models;

use CodeIgniter\Model;

class WinnerModel extends Model
{
    // ── User winner history ───────────────────────────────────────────────────

    public function winner_history(int $userId)
    {
        return $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_webs.name')
            ->join('tbl_webs', 'tbl_webs.id = CAST(JSON_UNQUOTE(JSON_EXTRACT(tbl_order.tickets, "$[0].web_id")) AS UNSIGNED)', 'left')
            ->where('user_id', $userId)
            ->where('prize !=', '')
            ->orderBy('id', 'desc')
            ->get()->getResult();
    }

    public function winner_amountf(int $userId)
    {
        return $this->db->table('tbl_order')
            ->selectSum('prize', 'sum')
            ->where('user_id', $userId)
            ->get()->getRow();
    }

    // ── Admin Winners ──────────────────────────────────────────────────────

    private function _winnerListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_order')
            ->select('tbl_order.*, tbl_users.name as uname, tbl_webs.name as game_name')
            ->join('tbl_users', 'tbl_users.userId = tbl_order.user_id', 'left')
            ->join('tbl_webs', 'tbl_webs.id = tbl_order.web_id', 'left')
            ->where('tbl_order.prize !=', '')
            ->where('tbl_order.prize IS NOT NULL', null, false);
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_winner_count(string $search = ''): int
    {
        return $this->_winnerListBuilder($search)->select('tbl_order.id')->get()->getNumRows();
    }

    public function admin_winner_list(string $search, int $limit, int $offset)
    {
        return $this->_winnerListBuilder($search)
            ->orderBy('tbl_order.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function admin_winner_total(): object
    {
        $row = $this->db->table('tbl_order')
            ->selectSum('prize', 'sum')
            ->where('prize !=', '')
            ->where('prize IS NOT NULL', null, false)
            ->get()->getRow();
        return $row ?? (object)['sum' => 0];
    }
}
