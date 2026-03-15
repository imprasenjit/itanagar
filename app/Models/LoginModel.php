<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    public function loginMe(string $email, string $password)
    {
        $result = $this->db->table('tbl_users as BaseTbl')
            ->select('BaseTbl.userId, BaseTbl.email, BaseTbl.mobile, BaseTbl.password, BaseTbl.name, BaseTbl.roleId, Roles.role')
            ->join('tbl_roles as Roles', 'Roles.roleId = BaseTbl.roleId')
            ->groupStart()
            ->where('BaseTbl.email', $email)
            ->orWhere('BaseTbl.mobile', $email)
            ->groupEnd()
            ->where('BaseTbl.isDeleted', 0)
            ->get()->getRow();

        if (!empty($result) && verifyHashedPassword($password, $result->password)) {
            return $result;
        }
        return [];
    }

    public function checkEmailExist(string $email): bool
    {
        return $this->db->table('tbl_users')
            ->select('userId')
            ->where('email',     $email)
            ->where('isDeleted', 0)
            ->get()->getNumRows() > 0;
    }

    public function checkMobileExist(string $mobile): bool
    {
        return $this->db->table('tbl_users')
            ->select('userId')
            ->where('mobile',    $mobile)
            ->where('isDeleted', 0)
            ->get()->getNumRows() > 0;
    }

    public function resetPasswordUser(array $data): bool
    {
        return $this->db->table('tbl_reset_password')->insert($data);
    }

    public function getCustomerInfoByEmail(string $email)
    {
        return $this->db->table('tbl_users')
            ->select('userId, email, name')
            ->where('isDeleted', 0)
            ->where('email',     $email)
            ->get()->getRow();
    }

    public function checkActivationDetails(string $email, string $activationId): int
    {
        return $this->db->table('tbl_reset_password')
            ->where('email',         $email)
            ->where('activation_id', $activationId)
            ->get()->getNumRows();
    }

    public function createPasswordUser(string $email, string $password): void
    {
        $this->db->table('tbl_users')
            ->where('email',     $email)
            ->where('isDeleted', 0)
            ->update(['password' => getHashedPassword($password)]);
        $this->db->table('tbl_reset_password')->where('email', $email)->delete();
    }

    public function lastLogin(array $loginInfo): void
    {
        $this->db->transStart();
        $this->db->table('tbl_last_login')->insert($loginInfo);
        $this->db->transComplete();
    }

    public function lastLoginInfo(int $userId)
    {
        return $this->db->table('tbl_last_login as BaseTbl')
            ->select('BaseTbl.createdDtm')
            ->where('BaseTbl.userId', $userId)
            ->orderBy('BaseTbl.id', 'DESC')
            ->limit(1)
            ->get()->getRow();
    }
}
