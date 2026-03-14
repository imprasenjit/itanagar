---
name: db-schema
description: 'Full database schema for the Itanagarchoice CodeIgniter project. Use when writing model queries, adding columns, joining tables, checking column names, understanding paid_status values, soft deletes, FK relationships, or any task involving the cias MySQL database. Covers all tbl_ prefixed tables: tbl_webs, tbl_ranges, tbl_dates, tbl_tier, tbl_cart, tbl_order, tbl_users, tbl_roles, tbl_wallet, tbl_wallet_history, tbl_refund, tbl_withdrawl, tbl_transfer, tbl_last_login, tbl_reset_password, tbl_faqs, tbl_pages, tbl_emails, tbl_contact.'
argument-hint: 'Optional: table name or feature area (e.g. "cart", "wallet", "orders")'
---

# db-schema

This skill provides the full column-level schema for the `cias` MySQL database so queries can be written accurately without guessing column names.

## When to Use

- Writing a new `Web_model.php` or `User_model.php` query
- Checking what columns exist before an insert/update
- Understanding `paid_status` / `isDeleted` / FK conventions
- Joining tables or tracing relationships
- Adding a new column or table following project conventions

## Conventions (apply everywhere)

| Convention | Rule |
|---|---|
| Table prefix | All tables use `tbl_` prefix |
| Primary keys | `id` (int) — except `tbl_users` which uses `userId` |
| Foreign keys | `*_id` suffix (e.g. `user_id`, `web_id`) — note `tbl_users` FK is `userId` not `user_id` |
| Timestamps | `Y-m-d H:i:s` stored as string; field name is usually `createdDtm` or `createdAt` |
| Soft delete | `isDeleted` column: `0` = active, `1` = deleted (only on `tbl_users`) |
| Passwords | Always bcrypt via `getHashedPassword()` / `verifyHashedPassword()` |
| Query builder | Always use CI Query Builder — never raw SQL |
| Typo | `tbl_withdrawl` is intentionally misspelled — match it exactly |

## Full Schema

See [./references/schema.md](./references/schema.md) for every table with columns and value notes.

## Procedure

1. **Identify the table(s)** involved in the task.
2. **Load [schema.md](./references/schema.md)** and find the relevant table section.
3. **Check FK relationships** — note `tbl_users.userId` (not `user_id`) when joining.
4. **Check enum-like values** (e.g. `paid_status`) in the Value Notes column before writing conditionals.
5. **Write the query** using CI Query Builder with the exact column names from the schema.
6. **Place the method** in `Web_model.php` (business logic) or `User_model.php` (user data).
