<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhonecodeToUsers extends Migration
{
    public function up(): void
    {
        // Add phonecode column to tbl_users if it doesn't already exist
        $fields = $this->db->getFieldNames('tbl_users');
        if (!in_array('phonecode', $fields)) {
            $this->forge->addColumn('tbl_users', [
                'phonecode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'mobile',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $fields = $this->db->getFieldNames('tbl_users');
        if (in_array('phonecode', $fields)) {
            $this->forge->dropColumn('tbl_users', 'phonecode');
        }
    }
}
