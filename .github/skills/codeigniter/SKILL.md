---
name: codeigniter
description: 'CodeIgniter 4 patterns, conventions, and rules for the Itanagarchoice project. Use when writing or editing controllers, models, routes, helpers, filters, or any backend PHP file in backend/app/. Covers: controller inheritance, model query conventions, route registration, session & auth guards, JSON API responses, form validation, XSS-clean input, flash messages, view loading, helper loading, config items, Razorpay integration, and CI4 transaction patterns.'
argument-hint: 'Optional: the feature area (e.g. "auth", "payment", "cart", "model query", "API endpoint")'
model: GROK CODE FAST 1
---

# CodeIgniter 4 — Project Skill

This skill encodes every CI4 pattern and rule used in the Itanagarchoice `backend/` application so that generated code is correct and consistent on the first attempt.

---

## 1. Controller Inheritance

### API / JSON controllers (extends `BaseController`)
```php
namespace App\Controllers;

class Api extends BaseController
{
    protected WebModel  $webModel;
    protected UserModel $userModel;

    protected $helpers = ['url', 'cias_helper', 'email_helper'];

    public function initController(...): void
    {
        parent::initController($request, $response, $logger);
        $this->webModel  = new WebModel();
        $this->userModel = new UserModel();
    }
}
```
- Always call `parent::initController()` first.
- Load helpers via the `$helpers` property, **not** `helper()` inside `initController`.
- `email_helper` must still be loaded with `helper('email_helper')` inside the individual method that first uses it if it was not declared in `$helpers`.

### Public / frontend controllers (extends `CI_Controller` — CI3 legacy, not used in `backend/`)
> `backend/` is pure CI4. All controllers extend `BaseController` or `CodeIgniter\Controller`.

---

## 2. Auth Guards

`BaseController` provides `requireAuth()`. **Always check the return value** — it returns a response object on failure, `null` on success:

```php
public function account_profile()
{
    $auth = $this->requireAuth();
    if ($auth !== null) return $auth;   // ← must return, not just call
    // ... proceed
}
```

For AJAX/JSON-only endpoints that need admin:
```php
if ($this->isAdmin() === false) {
    return $this->error('Forbidden', 403);
}
```

---

## 3. Standard JSON Response Helpers (from `BaseController`)

```php
// 200 success
return $this->json(['key' => $value], true, 'Optional message');

// Error
return $this->error('Something went wrong', 400);
return $this->error('Not found', 404);
return $this->error('Unauthenticated', 401);
```

Shape is always: `{ "status": bool, "data": {}, "message": "" }`

---

## 4. Reading the Request Body

```php
// JSON body (React frontend sends JSON)
private function getBody(): array
{
    $raw = $this->request->getBody();
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

// Query string
$webId = $this->request->getGet('web_id');

// POST form field (with XSS clean)
$name = $this->request->getPost('name', true); // true = XSS clean
```

---

## 5. Input Handling & XSS

**All user-supplied strings** must be sanitised before use or storage:

```php
$name    = esc($body['name']    ?? '');          // CI4 esc() — preferred
$email   = strtolower(esc($body['email'] ?? ''));
$mobile  = esc($body['mobile'] ?? '');
// Normalise name casing before DB insert:
$name    = ucwords(strtolower($name));
```

Never use values from `$_POST`, `$_GET`, or `$_REQUEST` directly.

---

## 6. Model Query Conventions

All models use CI4 Query Builder (`$this->db`). **Never write raw SQL.**

```php
// Single row → returns object or null
$row = $this->db->table('tbl_webs')->where('id', $id)->get()->getRow();

// Multiple rows → returns array of objects
$rows = $this->db->table('tbl_webs')->orderBy('id', 'DESC')->get()->getResult();

// Insert — returns insert id
$this->db->table('tbl_contact')->insert($data);
$id = $this->db->insertID();

// Batch insert
$this->db->table('tbl_cart')->insertBatch($array);

// Update
$this->db->table('tbl_webs')->where('id', $id)->update($data);

// Affected rows
$this->db->affectedRows();

// Count
$count = $this->db->table('tbl_order')->where('user_id', $userId)->countAllResults();
```

### Timestamps
```php
'createdDtm' => date('Y-m-d H:i:s')
```

### Multi-step inserts — use transactions
```php
$this->db->transStart();
$this->db->table('tbl_webs')->insert($data);
$id = $this->db->insertID();
$this->db->table('tbl_ranges')->insert(['web_id' => $id, ...]);
$this->db->transComplete();
```

---

## 7. Session & Auth Values

| Session key | Type | Notes |
|---|---|---|
| `isLoggedIn` | `bool` | `true` when logged in |
| `userId` | `int` | Authenticated user's ID |
| `role` | `int` | `1` = admin, `2` = regular user |
| `roleText` | `string` | `"Admin"` or `"User"` |
| `name` | `string` | Display name |
| `email` | `string` | |
| `mobile` | `string` | |
| `lastLogin` | `string` | `Y-m-d H:i:s` of previous session |
| `custom_userId` | `int` | Guest cart ID (random int, set when not logged in) |

```php
// Read
$userId = (int) session()->get('userId');
$isLoggedIn = session()->get('isLoggedIn') === true;

// Write
session()->set(['userId' => 1, 'isLoggedIn' => true]);

// Guest cart pattern
if (!session()->has('custom_userId')) {
    session()->set('custom_userId', random_int(100000000, 999999999));
}

// Merge guest cart on login
$guestId = session()->get('custom_userId');
if ($guestId) {
    $this->webModel->up_cart((int)$guestId, $userId);
}
```

---

## 8. Route Registration

**Every** endpoint must be explicitly registered in `backend/app/Config/Routes.php`. CI4 auto-routing is disabled.

```php
// GET
$routes->get('api/games',           'Api::games');
$routes->get('api/game/(:num)',      'Api::game_detail/$1');

// POST
$routes->post('api/login',           'Api::login');
$routes->post('api/cart/add',        'Api::cart_add');

// DELETE
$routes->delete('api/cart/(:num)',   'Api::cart_remove/$1');

// With segment parameters
$routes->get('api/game/(:num)/tickets/(:num)/(:num)', 'Api::game_tickets/$1/$2/$3');
```

---

## 9. Helper Loading

| Helper | Auto-loaded? | How to use |
|---|---|---|
| `cias_helper` | Yes (global) | `pre($data)` for debug dump+die, `getHashedPassword()`, `verifyHashedPassword()`, `getBrowserAgent()` |
| `url` | Yes | `base_url()`, `site_url()`, `redirect()` |
| `email_helper` | **No** | `helper('email_helper')` inside the method before first use; provides `sendmail()`, `resetPasswordEmail()` |

---

## 10. Config Items

```php
// Reading from .env / Config/App.php
$keyId     = env('RAZORPAY_KEY_ID',    'rzp_live_fallback');
$keySecret = env('RAZORPAY_KEY_SECRET', 'fallback_secret');
```

---

## 11. Razorpay Payment Integration

SDK location: `backend/app/ThirdParty/razorpay-php/Razorpay.php`

```php
require_once APPPATH . 'ThirdParty/razorpay-php/Razorpay.php';
use Razorpay\Api\Api as RazorpayApi;
use Razorpay\Api\Errors\SignatureVerificationError as PaymentError;

$api = new RazorpayApi(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

// Create order
$razorpayOrder = $api->order->create([
    'receipt'  => $transactionId,
    'amount'   => (int)($totalPrice * 100), // paise
    'currency' => 'INR',
]);

// Verify payment (in confirm handler)
try {
    $api->utility->verifyPaymentSignature([
        'razorpay_order_id'   => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature'  => $signature,
    ]);
} catch (PaymentError $e) {
    return $this->error('Signature verification failed: ' . $e->getMessage(), 400);
}
```

### `paid_status` values in `tbl_order`

| Value | Meaning |
|---|---|
| `'CREATED'` | Razorpay order created, payment not yet attempted |
| `'PAID'` | Payment verified successfully |
| `'FAILED'` | Payment failed or cancelled |

`order_status`: `0` = pending, `1` = paid, `2` = failed/cancelled.

---

## 12. CORS (API controller only)

CORS headers are set in `Api::initController()` via the private `_cors()` method. Allowed origins:
- `http://localhost:5173`
- `http://localhost:5174`
- `https://itanagarchoice.com`

OPTIONS preflight requests are responded to with 200 and `exit()`.

---

## 13. Email Sending

```php
helper('email_helper');

// Ticket confirmation
$emailBody = view('frontend/email_ticket', [
    'ticket_details' => $ticketDetails,
    'status'         => 'Payment Successful',
]);
sendmail($userInfo->email, 'Order: ' . $orderId, $emailBody);

// Password reset
resetPasswordEmail([
    'name'       => $userInfo->name,
    'email'      => $email,
    'reset_link' => base_url('ui/reset-password?code=' . $code . '&email=' . urlencode($email)),
    'message'    => 'Reset Your Password',
]);
```

---

## 14. Security Checklist

Before finalising any backend change, verify:
- [ ] All user input is `esc()`-cleaned before use
- [ ] Auth guard (`requireAuth()`) applied on protected endpoints
- [ ] No raw SQL — Query Builder only
- [ ] No hardcoded credentials — use `env()`
- [ ] New route added to `Routes.php`
- [ ] Transactions used for multi-step DB writes
- [ ] Response uses `$this->json()` or `$this->error()`, never `echo`
