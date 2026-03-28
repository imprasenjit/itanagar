<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQuantityToRanges extends Migration
{
    public function up()
    {
        $db     = \Config\Database::connect();
        $exists = $db->query(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_ranges'
               AND COLUMN_NAME  = 'quantity'"
        )->getRow()->cnt;

        if (!$exists) {
            $this->forge->addColumn('tbl_ranges', [
                'quantity' => [
                    'type'    => 'INT',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'price',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_ranges', 'quantity');
    }
}
