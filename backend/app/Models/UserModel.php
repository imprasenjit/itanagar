<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public function userListingCount(string $searchText = ''): int
    {
        $builder = $this->db->table('tbl_users as BaseTbl')
            ->select('BaseTbl.userId')
            ->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left')
            ->where('BaseTbl.isDeleted', 0)
            ->where('BaseTbl.roleId !=', 1)
            ->where('Role.role !=', ROLE_CUSTOMER);
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('BaseTbl.email',  $searchText)
                ->orLike('BaseTbl.name', $searchText)
                ->orLike('BaseTbl.mobile', $searchText)
                ->groupEnd();
        }
        return $builder->get()->getNumRows();
    }

    public function userListing(string $searchText, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_users as BaseTbl')
            ->select('BaseTbl.userId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.phonecode, BaseTbl.createdDtm, Role.role')
            ->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left')
            ->where('BaseTbl.isDeleted', 0)
            ->where('BaseTbl.roleId !=', 1)
            ->where('Role.role !=', ROLE_CUSTOMER)
            ->orderBy('BaseTbl.userId', 'DESC')
            ->limit($limit, $offset);
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('BaseTbl.email',   $searchText)
                ->orLike('BaseTbl.name',  $searchText)
                ->orLike('BaseTbl.mobile', $searchText)
                ->groupEnd();
        }
        return $builder->get()->getResult();
    }

    // ── Customer-specific listing (Customer role only) ────────────────────────

    public function customerListingCount(string $searchText = ''): int
    {
        $builder = $this->db->table('tbl_users as BaseTbl')
            ->select('BaseTbl.userId')
            ->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left')
            ->where('BaseTbl.isDeleted', 0)
            ->where('Role.role', ROLE_CUSTOMER);
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('BaseTbl.email',  $searchText)
                ->orLike('BaseTbl.name', $searchText)
                ->orLike('BaseTbl.mobile', $searchText)
                ->groupEnd();
        }
        return $builder->get()->getNumRows();
    }

    public function customerListing(string $searchText, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_users as BaseTbl')
            ->select('BaseTbl.userId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.phonecode, BaseTbl.createdDtm')
            ->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId', 'left')
            ->where('BaseTbl.isDeleted', 0)
            ->where('Role.role', ROLE_CUSTOMER)
            ->orderBy('BaseTbl.userId', 'DESC')
            ->limit($limit, $offset);
        if (!empty($searchText)) {
            $builder->groupStart()
                ->like('BaseTbl.email',   $searchText)
                ->orLike('BaseTbl.name',  $searchText)
                ->orLike('BaseTbl.mobile', $searchText)
                ->groupEnd();
        }
        return $builder->get()->getResult();
    }

    public function getUserRoles()
    {
        return $this->db->table('tbl_roles')
            ->select('roleId, role')
            ->where('roleId !=', 1)
            ->get()->getResult();
    }

    public function checkEmailExists(string $email, int $userId = 0)
    {
        $builder = $this->db->table('tbl_users')
            ->select('email')
            ->where('email',     $email)
            ->where('isDeleted', 0);
        if ($userId !== 0) {
            $builder->where('userId !=', $userId);
        }
        return $builder->get()->getResult();
    }

    public function addNewUser(array $data): int
    {
        $this->db->transStart();
        $this->db->table('tbl_users')->insert($data);
        $insertId = $this->db->insertID();
        $this->db->transComplete();
        return $insertId;
    }

    public function getUserInfo(int $userId)
    {
        return $this->db->table('tbl_users')
            ->select('userId, name, email, mobile, roleId')
            ->where('isDeleted', 0)
            ->where('roleId !=', 1)
            ->where('userId',    $userId)
            ->get()->getRow();
    }

    public function getUserInfoByMobile(string $mobile)
    {
        return $this->db->table('tbl_users')
            ->select('userId, name, email, mobile, roleId')
            ->where('isDeleted', 0)
            ->where('roleId !=', 1)
            ->where('mobile',    $mobile)
            ->get()->getRow();
    }

    public function getUserInfoWithRole(int $userId)
    {
        return $this->db->table('tbl_users as u')
            ->select('u.userId, u.name, u.email, u.mobile, u.roleId, u.bank, u.paypal, u.phonecode, r.role')
            ->join('tbl_roles as r', 'r.roleId = u.roleId', 'left')
            ->where('u.userId', $userId)
            ->get()->getRow();
    }

    public function getUserInfoById(int $userId)
    {
        return $this->db->table('tbl_users')
            ->select('userId, name, email, mobile, roleId')
            ->where('isDeleted', 0)
            ->where('userId',    $userId)
            ->get()->getRow();
    }

    public function editUser(array $data, int $userId): bool
    {
        $this->db->table('tbl_users')->where('userId', $userId)->update($data);
        return true;
    }

    public function editUserByMobile(array $data, string $mobile): bool
    {
        $this->db->table('tbl_users')->where('mobile', $mobile)->update($data);
        return true;
    }

    public function deleteUser(int $userId, array $data): int
    {
        $this->db->table('tbl_users')->where('userId', $userId)->update($data);
        return $this->db->affectedRows();
    }

    public function matchOldPassword(int $userId, string $oldPassword)
    {
        $result = $this->db->table('tbl_users')
            ->select('userId, password')
            ->where('userId',    $userId)
            ->where('isDeleted', 0)
            ->get()->getResult();

        if (!empty($result) && verifyHashedPassword($oldPassword, $result[0]->password)) {
            return $result;
        }
        return [];
    }

    public function changePassword(int $userId, array $data): int
    {
        $this->db->table('tbl_users')
            ->where('userId',    $userId)
            ->where('isDeleted', 0)
            ->update($data);
        return $this->db->affectedRows();
    }

    public function changePasswordByEmail(string $email, string $hashedPassword): int
    {
        $this->db->table('tbl_users')
            ->where('email',     $email)
            ->where('isDeleted', 0)
            ->update(['password' => $hashedPassword, 'updatedDtm' => date('Y-m-d H:i:s')]);
        return $this->db->affectedRows();
    }

    public function loginHistoryCount(int $userId, string $searchText, string $fromDate, string $toDate): int
    {
        $builder = $this->db->table('tbl_last_login as BaseTbl')
            ->select('BaseTbl.userId');
        $this->_applyLoginHistoryWhere($builder, $userId, $searchText, $fromDate, $toDate);
        return $builder->get()->getNumRows();
    }

    public function loginHistory(int $userId, string $searchText, string $fromDate, string $toDate, int $limit, int $offset)
    {
        $builder = $this->db->table('tbl_last_login as BaseTbl')
            ->select('BaseTbl.userId, BaseTbl.sessionData, BaseTbl.machineIp, BaseTbl.userAgent, BaseTbl.agentString, BaseTbl.platform, BaseTbl.createdDtm')
            ->orderBy('BaseTbl.id', 'DESC')
            ->limit($limit, $offset);
        $this->_applyLoginHistoryWhere($builder, $userId, $searchText, $fromDate, $toDate);
        return $builder->get()->getResult();
    }

    private function _applyLoginHistoryWhere($builder, int $userId, string $searchText, string $fromDate, string $toDate): void
    {
        if (!empty($searchText)) {
            $builder->like('BaseTbl.sessionData', $searchText);
        }
        if (!empty($fromDate)) {
            $builder->where("DATE_FORMAT(BaseTbl.createdDtm,'%Y-%m-%d') >=", date('Y-m-d', strtotime($fromDate)));
        }
        if (!empty($toDate)) {
            $builder->where("DATE_FORMAT(BaseTbl.createdDtm,'%Y-%m-%d') <=", date('Y-m-d', strtotime($toDate)));
        }
        if ($userId >= 1) {
            $builder->where('BaseTbl.userId', $userId);
        }
    }

    public function count_record(string $table): int
    {
        return $this->db->table($table)->get()->getNumRows();
    }
}
