<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionsTables extends Migration
{
    public function up(): void
    {
        // ── tbl_permissions: master registry of every gated feature ──────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'group_name' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => false],
            'sort_order' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('tbl_permissions', true);

        // ── tbl_role_permissions: which role has which permission ─────────────
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'permission_key' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['role_id', 'permission_key']);
        $this->forge->createTable('tbl_role_permissions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('tbl_role_permissions', true);
        $this->forge->dropTable('tbl_permissions', true);
    }
}
