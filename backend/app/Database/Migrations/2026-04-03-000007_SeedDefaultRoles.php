<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedDefaultRoles extends Migration
{
    public function up(): void
    {
        // Ensure the Admin role (roleId = 1) exists
        $existing = $this->db->table('tbl_roles')->where('roleId', 1)->get()->getRow();
        if (! $existing) {
            $this->db->table('tbl_roles')->insert(['roleId' => 1, 'role' => 'Admin']);
        }

        // Ensure the Customer role (roleId = 2) exists — used by all self-registered users
        $existing = $this->db->table('tbl_roles')->where('roleId', 2)->get()->getRow();
        if (! $existing) {
            $this->db->table('tbl_roles')->insert(['roleId' => 2, 'role' => 'Customer']);
        } else {
            // If it exists but has a different name, update it to 'Customer'
            if ($existing->role !== 'Customer') {
                $this->db->table('tbl_roles')->where('roleId', 2)->update(['role' => 'Customer']);
            }
        }
    }

    public function down(): void
    {
        // Remove only if we inserted them (safe — only deletes if no users are assigned)
        $this->db->table('tbl_roles')->whereIn('roleId', [1, 2])->delete();
    }
}
