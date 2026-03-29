<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * AddTicketReservation
 *
 * 1. Adds `reserved_until` (DATETIME NULL) to tbl_cart so each cart item
 *    carries its own 15-minute hold expiry.
 * 2. Deduplicates any existing rows that share (web_id, ticket_no), keeping
 *    the highest-id row, then adds a UNIQUE constraint so the database itself
 *    enforces one-row-per-ticket-per-game — eliminating the race condition
 *    where two users could simultaneously add the same ticket.
 */
class AddTicketReservation extends Migration
{
    public function up(): void
    {
        // 1. Add reserved_until column only if it doesn't already exist
        $columnExists = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_cart'
               AND COLUMN_NAME  = 'reserved_until'"
        )->getRow()->cnt;

        if (! $columnExists) {
            $this->forge->addColumn('tbl_cart', [
                'reserved_until' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'paid_status',
                ],
            ]);
        }

        // 2. Remove duplicate (web_id, ticket_no) rows; keep the newest (highest id)
        $this->db->query(
            "DELETE c1 FROM tbl_cart c1
             INNER JOIN tbl_cart c2
               ON c1.web_id = c2.web_id
              AND c1.ticket_no = c2.ticket_no
              AND c1.id < c2.id"
        );

        // 3. Add UNIQUE KEY only if it doesn't already exist
        $keyExists = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_cart'
               AND INDEX_NAME   = 'uq_cart_ticket'"
        )->getRow()->cnt;

        if (! $keyExists) {
            $this->db->query(
                'ALTER TABLE tbl_cart ADD UNIQUE KEY uq_cart_ticket (web_id, ticket_no)'
            );
        }
    }

    public function down(): void
    {
        $keyExists = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_cart'
               AND INDEX_NAME   = 'uq_cart_ticket'"
        )->getRow()->cnt;

        if ($keyExists) {
            $this->db->query('ALTER TABLE tbl_cart DROP INDEX uq_cart_ticket');
        }

        $columnExists = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = 'tbl_cart'
               AND COLUMN_NAME  = 'reserved_until'"
        )->getRow()->cnt;

        if ($columnExists) {
            $this->forge->dropColumn('tbl_cart', 'reserved_until');
        }
    }
}
