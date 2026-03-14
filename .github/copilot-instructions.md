# Copilot Instructions — Itanagarchoice

## Project Overview
Online lottery/ticket-purchasing platform built on **CodeIgniter 3.1.6** (PHP MVC), served via XAMPP.
- Local path: `c:\xampp2\htdocs\itanagar`
- Local URL: `http://localhost/itanagar`
- Production: `itanagarchoice.com`

Users browse lottery games, add tickets to cart, and pay via Razorpay (configured with **live keys**). Admins manage games, tickets, orders, wallets, and winners through the same codebase.

## Architecture

### Three Distinct Surfaces
| Surface | Controllers | Views | Auth |
|---|---|---|---|
| **Frontend** (public) | `Home`, `Game`, `Page`, `Login` | `views/frontend/` | None / optional session |
| **User account** (logged-in users) | `Account` | `views/frontend/` | `isLoggedIn()` — blocks `role == 1` (admin) |
| **Admin** (protected) | `Web`, `User`, `Order` | `views/` root + `views/web/` | `isLoggedIn()` + `isAdmin()` guard |

`Megamillions.php` is a **cron-only scraper controller** (fetches Mega Millions, Powerball, EuroJackpot draw data from external sites into `tbl_drawing` / `tbl_winner_history`) — not user-facing.

### Controller Inheritance Rules
- **Admin controllers** must `require APPPATH . '/libraries/BaseController.php'` and extend `BaseController`.
- **User Account controller** (`Account.php`) also extends `BaseController` but is for logged-in regular users — it blocks admins (`role == 1`) at the top of the constructor.
- **Public controllers** extend `CI_Controller` directly — no require needed.

```php
// Admin pattern
require APPPATH . '/libraries/BaseController.php';
class Web extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->load->model(array('web_model', 'user_model'));
        $this->load->library('form_validation');
        $this->isLoggedIn(); // redirects to login if no session
    }
}

// Frontend pattern
class Home extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model(array('web_model'));
    }
}
```

### View Loading Patterns
- **Admin views** — wrap with `includes/header` / `includes/footer`:
  ```php
  $this->global['pageTitle'] = 'Lottery : Page Title';
  $this->loadViews("web/addNew", $this->global, $data, NULL);
  ```
- **Frontend views** — wrap with `frontend/header` / `frontend/footer`:
  ```php
  $this->load->view("frontend/header");
  $this->load->view("frontend/game_detail", $data);
  $this->load->view("frontend/footer");
  ```
- **Email views** — capture as string using third argument `TRUE`:
  ```php
  $email_body = $this->load->view("frontend/email_ticket", $data, TRUE);
  ```
- `$this->global` carries layout data (`pageTitle`, `role`, `name`) to all admin views via `loadViews()`.

## Key Files & Directories
- `application/libraries/BaseController.php` — shared auth, `loadViews()`, `response()`, `paginationCompress()`, `loadThis()` (access denied)
- `application/models/Web_model.php` — all lottery business logic (games, ranges, tickets, cart, orders, wallet, winners); ~1000 lines
- `application/models/User_model.php` — user CRUD and auth queries; manually loaded per-controller (not autoloaded)
- `application/models/Login_model.php` — authentication, last-login tracking, password reset tokens
- `application/models/Drawing_model.php` — external draw data (`tbl_drawing`); used by Megamillions cron only
- `application/models/Winner_history_model.php` — prize/winner history per draw; used by Megamillions cron only
- `application/helpers/cias_helper.php` — auto-loaded globals: `pre($data)` (debug dump+die), `getHashedPassword()`, `verifyHashedPassword()`, `getBrowserAgent()`
- `application/helpers/email_helper.php` — **not autoloaded**, load manually; `sendmail()`, `resetPasswordEmail()`, `setFlashData()`; SMTP via `mail.itanagarchoice.com:587`
- `application/config/constants.php` — `ROLE_ADMIN = "Admin"`, `APP_NAME = "Itanagarchoice"`, `APP_TYPE = "ERP"`, `APP_LOGO = "public/assets/images/jaihind.jpg"`, `TIMEVAL = "00:00"`
- `application/config/routes.php` — all friendly URLs explicitly mapped; **always add new routes here**, never rely on default CI routing
- `application/views/frontend/components/game_card.php` — reusable game card component

## CodeIgniter 3 Patterns Used in This Project

### Form Validation
Load `form_validation` in the constructor, then validate before processing POST. On failure, re-render the form method directly (do not redirect):
```php
$this->load->library('form_validation');
$this->form_validation->set_rules('name', 'Name', 'trim|required|max_length[128]');
if ($this->form_validation->run() == FALSE) {
    $this->addNew(); // re-renders form with validation errors
} else {
    // process valid input
}
```

### Input Handling
- Always XSS-clean POST data before use:
  ```php
  $name = $this->security->xss_clean($this->input->post('name'));
  // OR pass TRUE as second arg (XSS filters inline):
  $id = $this->input->post('id', TRUE);
  ```
- Use `ucwords(strtolower(...))` to normalise name strings before DB insert.

### Flash Messages
Set before redirect; read in the next view via `$this->session->flashdata()`:
```php
$this->session->set_flashdata('success', 'Game created successfully');
$this->session->set_flashdata('error', 'Something went wrong');
redirect('web');
```

### Model Query Conventions
- **Single row** → `->row()` returns an object or `NULL`
- **Multiple rows** → `->result()` returns an array of objects
- **Row count check** → `$result->num_rows()` or `count($result) > 0`
- **Bulk insert** → `$this->db->insert_batch('tbl_cart', $array)`
- **Affected rows** → `$this->db->affected_rows()` after update/delete

```php
// Single row example
function getrangeInfo($id) {
    $this->db->select('*')->from('tbl_ranges')->where('web_id', $id);
    return $this->db->get()->row();
}

// Multiple rows example
function faq($limit = null) {
    $this->db->select('*')->from('tbl_faqs')->order_by('id', 'DESC');
    if ($limit) $this->db->limit($limit);
    return $this->db->get()->result();
}
```

### Pagination
Use `paginationCompress()` from `BaseController`; pass the URI segment number matching the offset:
```php
$data_count = $this->web_model->count_date($web_id);
$pgData = $this->paginationCompress("web/dates/$web_id/", $data_count, 10, 4);
$data['list'] = $this->web_model->list_date($web_id, $pgData['page'], $pgData['segment']);
```

### Config Items
Razorpay keys and other app settings are stored in `application/config/config.php` and read via:
```php
$this->config->item('key_id');
$this->config->item('key_secret');
```

## Session & Auth
- Session keys: `isLoggedIn` (bool), `userId`, `role` (**numeric roleId** — `1` = admin), `roleText`, `name`, `email`, `mobile`, `lastLogin`
- Guest cart uses `custom_userId` when `isLoggedIn !== TRUE`; on login, guest cart is merged: `$this->web_model->up_cart($guest_id, $user_id)`
- `isAdmin()` in `BaseController` has **inverted naming** — it returns `true` when the user is NOT an admin. Guards are written as:
  ```php
  if ($this->isAdmin() == FALSE) { $this->loadThis(); return; }
  // For AJAX admin methods:
  if ($this->isAdmin() == FALSE) { echo json_encode(['status' => 'access']); return; }
  ```

## Database Conventions
- Driver: MySQLi; database: `cias`
- All tables prefixed with `tbl_`:

| Table | Purpose |
|---|---|
| `tbl_webs` | Lottery games |
| `tbl_ranges` | Ticket ranges per game (price, heading, jackpot, logos, result_date) |
| `tbl_dates` | Draw dates per game |
| `tbl_tier` | Prize tier definitions |
| `tbl_cart` | Shopping cart (guest + logged-in) |
| `tbl_order` | Orders — `razorpay_order_id`, tickets JSON, `paid_status` |
| `tbl_users` | Registered users |
| `tbl_roles` | User roles |
| `tbl_last_login` | Login session history |
| `tbl_reset_password` | Password reset tokens |
| `tbl_wallet` | User wallet balances |
| `tbl_wallet_history` | Wallet credit/debit log |
| `tbl_refund` | Refund requests |
| `tbl_withdrawl` | Withdrawal requests *(note: intentional typo in table name)* |
| `tbl_transfer` | Wallet-to-wallet transfers |
| `tbl_faqs` | FAQ content |
| `tbl_pages` | CMS pages |
| `tbl_emails` | Email templates keyed by type |
| `tbl_contact` | Contact form submissions |
| `tbl_drawing` | External draw data (Megamillions cron) |
| `tbl_winner_history` | Prize/winner history per draw (cron) |

- Always use CI Query Builder — no raw SQL
- Timestamps stored as `Y-m-d H:i:s`: `'createdDtm' => date('Y-m-d H:i:s')`
- Date filtering uses the `TIMEVAL` constant: `$this->db->where("date_con > ", date('Y-m-d ' . TIMEVAL))`
- Transactions for multi-step inserts:
  ```php
  $this->db->trans_start();
  $this->db->insert('tbl_webs', $data);
  $id = $this->db->insert_id();
  $this->db->trans_complete();
  ```

## Autoloaded Resources (available everywhere)
- **Libraries**: `database`, `session`
- **Helpers**: `url`, `file`, `cias_helper`
- **Model**: *(none)* — `user_model` is **NOT** autoloaded; load it manually in each controller constructor that needs it

## Payment Integration (Razorpay)
SDK at `application/libraries/razorpay-php/`. Load only in payment-handling controllers:
```php
require_once(APPPATH . '/libraries/razorpay-php/Razorpay.php');
use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;
```
**Full payment flow** (all in `Game.php`):
1. `payment()` — creates Razorpay order, renders `frontend/payment` with order details
2. `payment_confirm()` — verifies signature via `$api->utility->verifyPaymentSignature()`, updates `tbl_orders`, sends email ticket
3. `payment_cancelled()` — updates order status to `2` (failed)

## JSON API Responses
From `BaseController`-extended controllers use `$this->response($array)` (sets 200 + JSON + exits).
From public/frontend controllers use the inline pattern:
```php
return $this->output
    ->set_content_type('application/json')
    ->set_output(json_encode(['status' => true, 'results' => $data]));
```

## Adding New Features — Checklist
1. **New admin route**: add to `application/config/routes.php`
2. **New admin controller method**: apply `isAdmin()` guard, set `$this->global['pageTitle']`, use `loadViews()`
3. **New model method**: add to `Web_model.php` (business logic) or `User_model.php` (user data); use Query Builder only
4. **New frontend page**: extend `CI_Controller`, load `web_model`, wrap views with `frontend/header` + `frontend/footer`
5. **Debug**: use `pre($data)` (dumps + dies) during development; remove before committing

## Local Development Setup
1. XAMPP stack required (Apache + MySQL)
2. Create MySQL database `cias` and import `cias.sql`
3. Place project at `c:\xampp2\htdocs\itanagar`
4. Access at `http://localhost/itanagar`
5. Default admin: `admin@example.com` / `codeinsect`
