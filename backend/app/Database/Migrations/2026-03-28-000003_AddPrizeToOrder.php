<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrizeToOrder extends Migration
{
    public function up()
    {
        // Check if column already exists before adding (compatible with MySQL 5.7+)
        $db     = \Config\Database::connect();
        $exists = $db->query(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_order'
               AND COLUMN_NAME  = 'prize'"
        )->getRow()->cnt;

        if (!$exists) {
            $this->forge->addColumn('tbl_order', [
                'prize' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'order_status',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_order', 'prize');
    }
}
