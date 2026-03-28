<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->db->table('tbl_permissions')->countAll() > 0) {
            return; // already seeded — safe to re-run
        }

        $this->db->table('tbl_permissions')->insertBatch([
            // ── Management ────────────────────────────────────────────────
            ['key' => 'users.view',        'label' => 'View Users',                       'group_name' => 'Management',       'sort_order' => 1],
            ['key' => 'users.create',      'label' => 'Add Users',                        'group_name' => 'Management',       'sort_order' => 2],
            ['key' => 'users.edit',        'label' => 'Edit Users',                       'group_name' => 'Management',       'sort_order' => 3],
            ['key' => 'users.delete',      'label' => 'Delete Users',                     'group_name' => 'Management',       'sort_order' => 4],
            ['key' => 'games.view',        'label' => 'View Games',                       'group_name' => 'Management',       'sort_order' => 5],
            ['key' => 'games.create',      'label' => 'Add Games',                        'group_name' => 'Management',       'sort_order' => 6],
            ['key' => 'games.edit',        'label' => 'Edit / Configure Games',           'group_name' => 'Management',       'sort_order' => 7],
            ['key' => 'games.delete',      'label' => 'Delete Games',                     'group_name' => 'Management',       'sort_order' => 8],
            ['key' => 'games.settings',    'label' => 'Common Settings',                  'group_name' => 'Management',       'sort_order' => 9],
            // ── Orders & Finance ──────────────────────────────────────────
            ['key' => 'orders.view',       'label' => 'View Orders',                      'group_name' => 'Orders & Finance', 'sort_order' => 1],
            ['key' => 'orders.confirm',    'label' => 'Confirm / Release Orders',         'group_name' => 'Orders & Finance', 'sort_order' => 2],
            ['key' => 'transactions.view', 'label' => 'View Transactions',                'group_name' => 'Orders & Finance', 'sort_order' => 3],
            ['key' => 'tickets.view',      'label' => 'View Tickets',                     'group_name' => 'Orders & Finance', 'sort_order' => 4],
            ['key' => 'tickets.manage',    'label' => 'Cancel / Resend / Verify Tickets', 'group_name' => 'Orders & Finance', 'sort_order' => 5],
            ['key' => 'winners.view',      'label' => 'View Winners',                     'group_name' => 'Orders & Finance', 'sort_order' => 6],
            ['key' => 'reports.view',      'label' => 'Download Reports',                 'group_name' => 'Orders & Finance', 'sort_order' => 7],
            // ── Content ───────────────────────────────────────────────────
            ['key' => 'contact.view',      'label' => 'Contact Requests',                 'group_name' => 'Content',          'sort_order' => 1],
            ['key' => 'faq.view',          'label' => 'View Announcements',               'group_name' => 'Content',          'sort_order' => 2],
            ['key' => 'faq.manage',        'label' => 'Add / Edit / Delete Announcements','group_name' => 'Content',          'sort_order' => 3],
            ['key' => 'pages.view',        'label' => 'CMS Pages',                        'group_name' => 'Content',          'sort_order' => 4],
            // ── Settings ──────────────────────────────────────────────────
            ['key' => 'rbac.manage',       'label' => 'Role Permissions',                 'group_name' => 'Settings',         'sort_order' => 1],
        ]);
    }
}
