<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    // ── Wallet ────────────────────────────────────────────────────────────

    public function wallet(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_wallet')
            ->select('money, id')
            ->where('user_id', $userId)
            ->get()->getRow();
    }

    public function wallet_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_wallet_history')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function refund_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_refund')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function withdrawl_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_withdrawl')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    public function transfer_history(int $userId = 0)
    {
        if ($userId === 0) {
            $userId = (int) session()->get('userId');
        }
        return $this->db->table('tbl_transfer')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()->getResult();
    }

    // ── Admin Wallet History ───────────────────────────────────────────────

    private function _walletListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_wallet_history')
            ->join('tbl_users', 'tbl_users.userId = tbl_wallet_history.user_id', 'left')
            ->select('tbl_wallet_history.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_wallet_count(string $search = ''): int
    {
        return $this->_walletListBuilder($search)->select('tbl_wallet_history.id')->get()->getNumRows();
    }

    public function admin_wallet_list(string $search, int $limit, int $offset)
    {
        return $this->_walletListBuilder($search)
            ->orderBy('tbl_wallet_history.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Admin Refund ───────────────────────────────────────────────────────

    private function _refundListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_refund')
            ->join('tbl_users', 'tbl_users.userId = tbl_refund.user_id', 'left')
            ->select('tbl_refund.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_refund_count(string $search = ''): int
    {
        return $this->_refundListBuilder($search)->select('tbl_refund.id')->get()->getNumRows();
    }

    public function admin_refund_list(string $search, int $limit, int $offset)
    {
        return $this->_refundListBuilder($search)
            ->orderBy('tbl_refund.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function refund_process(int $id, string $type, int $userId, float $money): void
    {
        if ($type === 'Refund') {
            $wallet = $this->wallet($userId);
            if ($wallet) {
                $this->db->table('tbl_wallet')->where('user_id', $userId)->update(['money' => (float)$wallet->money + $money]);
            }
            $this->db->table('tbl_refund')->where('id', $id)->update(['status' => '1']);
        } else {
            $this->db->table('tbl_refund')->where('id', $id)->update(['status' => '2']);
        }
    }

    // ── Admin Withdrawal ───────────────────────────────────────────────────

    private function _withdrawlListBuilder(string $search)
    {
        $builder = $this->db->table('tbl_withdrawl')
            ->join('tbl_users', 'tbl_users.userId = tbl_withdrawl.user_id', 'left')
            ->select('tbl_withdrawl.*, tbl_users.name as uname');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tbl_users.name', $search)
                ->orLike('tbl_users.email', $search)
                ->groupEnd();
        }
        return $builder;
    }

    public function admin_withdrawl_count(string $search = ''): int
    {
        return $this->_withdrawlListBuilder($search)->select('tbl_withdrawl.id')->get()->getNumRows();
    }

    public function admin_withdrawl_list(string $search, int $limit, int $offset)
    {
        return $this->_withdrawlListBuilder($search)
            ->orderBy('tbl_withdrawl.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function withdrawl_process(int $id, string $type): void
    {
        $status = ($type === 'Reject') ? '2' : '1';
        $this->db->table('tbl_withdrawl')->where('id', $id)->update(['status' => $status]);
    }

    // ── User Wallet History (paginated) ──────────────────────────────────────

    private function _userWalletBuilder(int $userId)
    {
        return $this->db->table('tbl_wallet_history')
            ->join('tbl_users', 'tbl_users.userId = tbl_wallet_history.user_id', 'left')
            ->select('tbl_wallet_history.*, tbl_users.name as uname')
            ->where('tbl_wallet_history.user_id', $userId);
    }

    public function user_wallet_count(int $userId): int
    {
        return $this->_userWalletBuilder($userId)->get()->getNumRows();
    }

    public function user_wallet_list(int $userId, int $limit, int $offset)
    {
        return $this->_userWalletBuilder($userId)
            ->orderBy('tbl_wallet_history.id', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    // ── Generic utilities (used for wallet-domain tables) ────────────────────

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
}
