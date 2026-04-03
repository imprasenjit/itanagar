<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    public function getAllPermissions(): array
    {
        return $this->db->table('tbl_permissions')
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->get()->getResult();
    }

    /**
     * Returns a flat array of permission keys for a given role ID.
     * Used by BaseController::isLoggedIn() to populate the session cache.
     */
    public function getPermissionsForRole(int $roleId): array
    {
        return array_column(
            $this->db->table('tbl_role_permissions')
                ->select('permission_key')
                ->where('role_id', $roleId)
                ->get()->getResultArray(),
            'permission_key'
        );
    }

    /**
     * Returns all assigned permissions indexed by role_id.
     * [ role_id => ['key1', 'key2', ...] ]
     */
    public function getAllAssigned(): array
    {
        $rows     = $this->db->table('tbl_role_permissions')
            ->select('role_id, permission_key')
            ->get()->getResult();
        $assigned = [];
        foreach ($rows as $row) {
            $assigned[(int) $row->role_id][] = $row->permission_key;
        }
        return $assigned;
    }

    /**
     * Atomically replaces all permissions for a role.
     */
    public function saveRolePermissions(int $roleId, array $keys): void
    {
        $this->db->transBegin();
        $this->db->table('tbl_role_permissions')->where('role_id', $roleId)->delete();
        if (!empty($keys)) {
            $batch = array_map(fn($k) => ['role_id' => $roleId, 'permission_key' => $k], $keys);
            $this->db->table('tbl_role_permissions')->insertBatch($batch);
        }
        $this->db->transCommit();
    }

    public function getAllRoles(): array
    {
        return $this->db->table('tbl_roles')->orderBy('roleId')->get()->getResult();
    }

    public function getRoleById(int $id)
    {
        return $this->db->table('tbl_roles')->where('roleId', $id)->get()->getRow();
    }

    public function addRole(string $name): bool
    {
        return $this->db->table('tbl_roles')->insert(['role' => $name]);
    }

    public function updateRole(int $id, string $name): bool
    {
        return $this->db->table('tbl_roles')->where('roleId', $id)->update(['role' => $name]);
    }

    public function deleteRole(int $id): bool
    {
        if ($id === 1) {
            return false;
        }
        $this->db->table('tbl_role_permissions')->where('role_id', $id)->delete();
        return $this->db->table('tbl_roles')->where('roleId', $id)->delete();
    }
}
