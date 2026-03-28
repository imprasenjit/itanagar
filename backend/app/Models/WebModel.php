<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * WebModel - remaining miscellaneous queries that don't belong to a domain model.
 * Still imported by User.php (getallcountry) and kept for backward compatibility.
 *
 * Domain-specific methods have been moved to:
 *   GameModel       - tbl_webs, tbl_ranges, tbl_dates, tbl_tier
 *   CartOrderModel  - tbl_cart, tbl_order
 *   WalletModel     - tbl_wallet, tbl_wallet_history, tbl_refund, tbl_withdrawl
 *   WinnerModel     - winner queries on tbl_order
 *   ContentModel    - tbl_faqs, tbl_pages, tbl_contact, tbl_emails
 */
class WebModel extends Model
{
    public function getcommon()
    {
        return $this->db->table('common')->get()->getRow();
    }

    public function getallcountry()
    {
        return $this->db->table('tbl_country')->orderBy('name')->get()->getResult();
    }
}