# Cron Job Setup — Ticket Reservation Cleanup

## What it does

Every 5 minutes the cron hits a secured endpoint that deletes all `tbl_cart` rows
where `paid_status = 0` AND `reserved_until < NOW()`.

This frees up ticket numbers that users added to cart but never paid for
(their 15-minute hold expired). Without this, expired holds would silently block
other users from buying those ticket numbers forever.

---

## Step 1 — Generate a CRON_SECRET

Pick **one** of the options below to generate a strong random secret.

**PowerShell:**
```powershell
-join ((65..90) + (97..122) + (48..57) | Get-Random -Count 40 | ForEach-Object { [char]$_ })
```

**PHP (CLI or paste into a temp file):**
```php
echo bin2hex(random_bytes(24));
```

Example output: `a7f3Kx92mQpL8nRtZvWcYdJsBe4uG1hN0oCiElA`

---

## Step 2 — Add the secret to `.env`

Open `backend/.env` (local) and `backend/.env_production` (production) and add:

```ini
CRON_SECRET = a7f3Kx92mQpL8nRtZvWcYdJsBe4uG1hN0oCiElA
```

On BigRock, if you cannot edit `.env` via SSH, use **cPanel → File Manager**,
navigate to your app root, and edit `.env` there.

---

## Step 3 — Test the endpoint manually

Before setting up the cron, verify the endpoint works by opening this URL in
your browser or Postman (replace the key with your own secret):

```
https://itanagarchoice.com/api/cron/release-reservations?key=YOUR_SECRET_HERE
```

**Expected response:**
```json
{
  "status": true,
  "data": { "released": 0 },
  "message": "Expired reservations released"
}
```

If you get `403 Unauthorized`, the key in the URL does not match the value
in your `.env` file.

---

## Step 4 — Set up the Cron Job in BigRock cPanel

BigRock shared servers use cPanel's built-in Cron Jobs manager — no SSH needed.

1. Log in to **cPanel** → search for **"Cron Jobs"** → open it.

2. Set the **frequency** fields:

   | Field   | Value |
   |---------|-------|
   | Minute  | `*/5` |
   | Hour    | `*`   |
   | Day     | `*`   |
   | Month   | `*`   |
   | Weekday | `*`   |

3. Paste the following into the **Command** field:

   ```bash
   curl -s "https://itanagarchoice.com/api/cron/release-reservations?key=YOUR_SECRET_HERE" > /dev/null 2>&1
   ```

   - Replace `YOUR_SECRET_HERE` with your actual secret from Step 1.
   - `> /dev/null 2>&1` suppresses output so cPanel does not email you every 5 minutes.

4. Click **Add New Cron Job**.

> **If `curl` is not available** on your plan, use `wget` instead:
> ```bash
> wget -q -O /dev/null "https://itanagarchoice.com/api/cron/release-reservations?key=YOUR_SECRET_HERE"
> ```

---

## How the TTL windows work

| Event | TTL set |
|---|---|
| User adds ticket to cart | `reserved_until = NOW() + 15 minutes` |
| User proceeds to checkout (Razorpay order created) | Extended to `NOW() + 30 minutes` |
| Payment confirmed | Row `paid_status` flipped to `1`; `reserved_until` no longer relevant |
| Cron runs (every 5 min) | Deletes rows where `paid_status = 0 AND reserved_until < NOW()` |

---

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| `403 Unauthorized` | Secret mismatch between URL and `.env` |
| `404 Not Found` | Route not registered or `.htaccess` rewrite not working |
| `released` is always `0` | No expired reservations exist yet — this is normal |
| Cron not running | Check cPanel → Cron Jobs to confirm the entry saved |

---

---

# Ticket Booking Flow & Reservation Logic

## Phase 1 — Browsing (no reservation yet)

```
User opens game page
    → frontend calls GET /api/games/{id}/tickets/{start}/{end}
    → backend queries tbl_cart for tickets where:
          paid_status = 1  (sold)
          OR (paid_status = 0 AND reserved_until > NOW())  ← active hold
    → those numbers are returned as "unavailable" (greyed out in UI)
    → all other numbers in the range are shown as selectable
```

No row is written to the database yet. Looking at tickets costs nothing.

---

## Phase 2 — Add to Cart (15-minute hold created)

```
User selects ticket numbers → clicks "Add to Cart"
    → POST /api/cart/add  { web_id, tickets: [N, N, ...] }

For EACH ticket number, backend checks:
    1. Is it in the defined range?           → if not: error "out of range"
    2. Is it taken by someone else?
           paid_status = 1  (sold/committed)
           OR paid_status = 0 AND reserved_until > NOW() AND user_id != me
                                             → if yes: error "not available"
    3. Is it already in MY cart?             → if yes: skip silently

If all checks pass → INSERT into tbl_cart:
    { web_id, user_id, ticket_no, price, paid_status=0, reserved_until = NOW()+15min }

DB UNIQUE KEY (web_id, ticket_no) ensures that even if two users
pass all checks simultaneously, only one INSERT can succeed.
The loser gets a clean "not available" error, not a server crash.
```

**Result:** Ticket is now held for 15 minutes. No other user can add it.

---

## Phase 3 — Checkout (hold extended to 30 minutes)

```
User clicks "Proceed to Pay"
    → POST /api/payment/create

Backend:
    1. Re-fetches cart from DB
    2. Compares fresh count vs original — if any item was cleaned up
       by the cron between page load and now → error "ticket no longer available"
    3. Extends reserved_until to NOW()+30min for all cart items
       (payment redirects can take several minutes)
    4. Creates Razorpay order
    5. Returns order_id + keys to frontend
    6. Sets cart items paid_status = 1  (committed — now blocks everyone else)
```

**Result:** Ticket is hard-locked. `paid_status=1` blocks all other users unconditionally.

---

## Phase 4 — Payment Outcome

```
SUCCESS → POST /api/payment/confirm
    Backend verifies Razorpay signature
    → Updates tbl_order: paid_status = 'PAID', order_status = 1
    → Sends confirmation email
    → tbl_cart row stays at paid_status=1 permanently (ticket is sold)

FAILURE / CANCEL → POST /api/payment/cancel
    → Updates tbl_order: order_status = 2
    → tbl_cart row stays at paid_status=1
      ⚠ Admin must manually release via "Release All Blocked" button
        (cron will NOT clean these up — they have paid_status=1)
```

---

## Reservation Release Conditions

| Condition | Who releases | When |
|---|---|---|
| User removes item from cart | User (delete button) | Immediately — row deleted |
| 15-min hold expires, user walked away | **Cron** (every 5 min) | When `paid_status=0 AND reserved_until < NOW()` |
| "Release Expired" admin button | Admin | Same as cron — only expired holds |
| "Release All Blocked" admin button | Admin | ALL `paid_status=0` rows — including active holds |
| Payment confirmed (PAID) | Never released | Ticket is permanently sold |
| Payment failed / cancelled | **Admin only** | `paid_status=1` rows are NOT touched by cron — must use admin button or `release_order_by_admin` |

---

## State Machine for a Single `tbl_cart` Row

```
                    ┌─────────────────────────────────────────────┐
                    │                Not in DB                    │
                    │          (ticket appears available)         │
                    └──────────────────┬──────────────────────────┘
                                       │  cart_add()
                                       ▼
                    ┌─────────────────────────────────────────────┐
                    │  paid_status=0, reserved_until=NOW()+15min  │
                    │        (ticket BLOCKED for 15 min)          │
                    └───┬─────────────────┬───────────────────────┘
                        │                 │
          TTL expires   │                 │  payment_create()
          (cron/admin)  │                 │  extends TTL +30min,
                        │                 │  sets paid_status=1
                        ▼                 ▼
              Row DELETED        ┌────────────────────────┐
         (ticket available       │  paid_status=1         │
           again for others)     │  (ticket HARD-LOCKED)  │
                                 └──────┬─────────────────┘
                                        │
                          ┌─────────────┴──────────────┐
                          │                            │
                   payment/confirm            payment/cancel
                          │                            │
                          ▼                            ▼
                  tbl_order: PAID           tbl_order: cancelled
                  Row stays paid_status=1   Row stays paid_status=1
                  (SOLD, permanent)         (stuck — admin must release)
```

---

## Key Rules to Remember

1. **Cron only cleans `paid_status=0`** — failed payment rows (`paid_status=1`) are never auto-released.
2. **The DB unique key is the last line of defence** — if two requests race past the app-level check, MySQL rejects the second INSERT.
3. **Guests get a `custom_userId`** from session — their holds behave exactly the same as logged-in users.
4. **On login, guest cart is merged** — `up_cart(guestId, userId)` reassigns the rows, preserving the holds.
