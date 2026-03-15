# Database Schema — cias

> Driver: MySQLi | Database: `cias`  
> See [copilot-instructions.md](../../../.github/copilot-instructions.md) for general conventions.

---

## tbl_webs — Lottery Games

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | Auto-increment |
| `name` | varchar | Lottery game name |
| `status` | varchar | `"Active"` = visible to front-end |

---

## tbl_ranges — Ticket Ranges per Game

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `web_id` | int FK | → `tbl_webs.id` |
| `price` | decimal | Ticket price |
| `heading` | varchar | Display heading/label |
| `logo` | varchar | Primary logo image path |
| `logo2` | varchar | Secondary logo image path |
| `jackpot` | decimal | Jackpot amount |
| `quantity` | int | Total tickets available |
| `rangeStart` | int | Starting ticket number in range |
| `result_date` | date | Result announcement date |
| `priority` | int | Display sort order |

---

## tbl_dates — Draw Dates per Game

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `web_id` | int FK | → `tbl_webs.id` |
| `date` | date | Draw date (`Y-m-d`) |
| `date_con` | datetime | Full datetime for cutoff comparison (`Y-m-d H:i:s`); filtered with `TIMEVAL` constant |

---

## tbl_tier — Prize Tier Definitions

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `web_id` | int FK | → `tbl_webs.id` |
| `white` | int | Number of matched white balls required |
| `mega` | int | Number of matched mega balls required |

---

## tbl_cart — Shopping Cart

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `web_id` | int FK | → `tbl_webs.id` |
| `user_id` | varchar | User ID or guest `custom_userId` session value |
| `ticket_no` | varchar | Ticket number |
| `total_price` | decimal | Price for this cart item |
| `paid_status` | int | `0` = in cart, `1` = paid, `2` = failed/cancelled |

**Guest cart:** `user_id` stores the `custom_userId` session value. On login, merged via `up_cart($guest_id, $user_id)`.

---

## tbl_order — Orders

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` |
| `web_id` | int FK | → `tbl_webs.id` |
| `tickets` | text | JSON-encoded ticket details |
| `user_id` | varchar(30) | User ID (FK → tbl_users.userId) |
| `web_id` | int | FK → tbl_webs.id (added column) |
| `custom_user_id` | varchar(255) | Guest user ID for pre-login orders |
| `paid_type` | varchar(20) | Payment method type |
| `transaction_id` | varchar(50) | Payment gateway transaction ID |
| `total_price` | int | Order total |
| `razorpay_order_response` | text | Full Razorpay order response JSON |
| `razorpay_order_id` | varchar(255) | Razorpay order reference |
| `paid_status` | varchar(255) | `0`=unpaid, `1`=paid, `2`=failed, `'PAID'`=confirmed, `'RELEASED'`=refunded |
| `order_status` | tinyint | `0` = incomplete/failed |
| `prize` | decimal(10,2) | Prize amount won (NULL if not a winner) |
| `payment_response` | text | Payment confirmation response JSON |
| `createdAt` | timestamp | Auto-set creation timestamp |

> ⚠️ Use `createdAt` (not `createdDtm`) for date filtering on this table.

---

## tbl_users — Registered Users

| Column | Type | Notes |
|---|---|---|
| `userId` | int PK | ⚠️ Not `id` — FK references use `userId` |
| `email` | varchar | Unique |
| `address` | varchar(500) | Optional address |
| `name` | varchar | Full name (stored via `ucwords(strtolower(...))`) |
| `mobile` | varchar | Unique |
| `phonecode` | varchar(10) | Country phone code (e.g. `'91'` for India) |
| `paypal` | varchar(200) | PayPal address for payouts |
| `bank` | varchar(500) | Bank details for withdrawals |
| `password` | varchar | bcrypt hash via `getHashedPassword()` |
| `roleId` | int FK | → `tbl_roles.roleId`; `1` = Admin |
| `isDeleted` | tinyint | `0` = active, `1` = soft-deleted |
| `createdBy` | int | userId of creator |
| `createdDtm` | datetime | Account creation timestamp |
| `updatedBy` | int | userId of last editor |
| `updatedDtm` | datetime | Last update timestamp |

---

## tbl_roles — User Roles

| Column | Type | Notes |
|---|---|---|
| `roleId` | int PK | `1` = Admin |
| `role` | varchar | Role label e.g. `"Admin"`, `"User"` |

---

## tbl_last_login — Login History

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `userId` | int FK | → `tbl_users.userId` |
| `sessionData` | varchar | Session identifier |
| `machineIp` | varchar | Client IP address |
| `userAgent` | varchar | Raw HTTP User-Agent header |
| `agentString` | varchar | Parsed browser/OS string (via `getBrowserAgent()`) |
| `platform` | varchar | Platform label |
| `createdDtm` | datetime | Login timestamp; used for date-range filtering |

---

## tbl_reset_password — Password Reset Tokens

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `email` | varchar | User's email (cleared on successful reset) |
| `activation_id` | varchar | Unique one-time reset token |

---

## tbl_wallet — User Wallet Balances

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` |
| `money` | decimal | Current balance |

---

## tbl_wallet_history — Wallet Transaction Log

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` |
| *(additional transaction columns)* | | Likely: amount, type, description, createdDtm |

---

## tbl_refund — Refund Requests

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` |
| *(additional columns)* | | Order reference, amount, status, createdDtm |

---

## tbl_withdrawl — Withdrawal Requests

> ⚠️ **Intentional typo** in table name — `withdrawl` not `withdrawal`

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` |
| *(additional columns)* | | Amount, status, payment method, createdDtm |

---

## tbl_transfer — Wallet Transfers

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `user_id` | int FK | → `tbl_users.userId` (sender) |
| *(additional columns)* | | Recipient user_id, amount, createdDtm |

---

## tbl_faqs — FAQ Content

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `question` | text | FAQ question |
| `answer` | text | FAQ answer |

---

## tbl_pages — CMS Pages

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `type` | varchar | Page slug/identifier (e.g. `"about"`, `"privacy"`, `"terms"`) |
| *(additional columns)* | | Page title, body/content |

Loaded by `Page.php` controller via `page/(:any)` route.

---

## tbl_emails — Email Templates

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `type` | varchar | Template key (e.g. `"ticket_confirmation"`, `"password_reset"`) |
| *(additional columns)* | | Subject, body |

---

## tbl_contact — Contact Form Submissions

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| *(additional columns)* | | Name, email, message, createdDtm |

---

## tbl_common — Shared App Settings

Loaded via `getcommon()` in `Web_model.php`. Stores site-wide configuration values.

---

## Cron-Only Tables (Megamillions scraper)

### tbl_drawing

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `website_id` | int | Maps to a lottery game |
| `latest_date` | date | Most recent draw date |
| `next_date` | date | Upcoming draw date |
| `next_price` | varchar | Next jackpot value |
| `whiteball1`–`whiteball6` | int | White ball numbers drawn |
| `megaball` | int | Mega ball number |
| `megaball1` | int | Second mega ball (some games) |

### tbl_winner_history

| Column | Type | Notes |
|---|---|---|
| `id` | int PK | |
| `website_id` | int | Maps to a lottery game |
| `whiteball` | int | Matched white balls count |
| `megaball` | int | Matched mega balls count |
| `latest_date` | date | Draw date |
| `is_jackpot` | tinyint | `1` = jackpot |
| `price_amount` | decimal | Payout amount for this match pattern |

---

## Relationship Diagram (simplified)

```
tbl_webs (id)
  ├── tbl_ranges   (web_id)
  ├── tbl_dates    (web_id)
  ├── tbl_tier     (web_id)
  ├── tbl_cart     (web_id)
  └── tbl_order    (web_id)

tbl_users (userId)
  ├── tbl_cart          (user_id)
  ├── tbl_order         (user_id)
  ├── tbl_wallet        (user_id)
  ├── tbl_wallet_history(user_id)
  ├── tbl_refund        (user_id)
  ├── tbl_withdrawl     (user_id)
  ├── tbl_transfer      (user_id)
  └── tbl_last_login    (userId)

tbl_roles (roleId)
  └── tbl_users (roleId)
```
