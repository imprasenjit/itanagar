<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds composite indexes that speed up the most expensive queries:
 *   - home_web / upcoming_games_paged  →  tbl_cart, tbl_dates, tbl_webs, tbl_ranges
 *   - payment confirmation lookup      →  tbl_order (razorpay_order_id)
 *
 * Each ALTER TABLE is wrapped in a try/catch so the migration is safe to run
 * even if an index already exists (e.g. re-run after a partial failure).
 */
class AddPerformanceIndexes extends Migration
{
    private array $indexes = [
        ['tbl_cart',   'idx_cart_web_paid',  '(web_id, paid_status)'],
        ['tbl_dates',  'idx_dates_web_con',  '(web_id, date_con)'],
        ['tbl_webs',   'idx_webs_status',    '(status)'],
        ['tbl_ranges', 'idx_ranges_web_pri', '(web_id, priority)'],
        ['tbl_order',  'idx_order_razorpay', '(razorpay_order_id)'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$table, $name, $cols]) {
            try {
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$cols}");
            } catch (\Throwable) {
                // Index likely already exists — safe to continue.
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$table, $name]) {
            try {
                $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            } catch (\Throwable) {
                // Index may not exist — safe to continue.
            }
        }
    }
}
