---
name: tailwind-migration
description: 'Migration plan and execution guide for replacing Bootstrap 3/4 + AdminLTE CSS with Tailwind CSS across all CI4 PHP views. Use when migrating admin panel views (AdminLTE → Tailwind), frontend views (Bootstrap 4 → Tailwind), auth pages, setting up the Tailwind build pipeline for PHP, or mapping Bootstrap utility classes to Tailwind equivalents. Triggers: tailwind migration, replace bootstrap, update css views, admin panel css, frontend css, tailwind php, AdminLTE replace.'
argument-hint: 'Optional: surface to migrate (e.g. "admin panel", "frontend", "auth pages", "build pipeline")'
---

# Tailwind CSS Migration — CI4 PHP Views

Complete plan for migrating all `app/Views/**/*.php` from Bootstrap 3/4 + AdminLTE 2 to Tailwind CSS.

## When to Use

- Setting up the Tailwind build pipeline for PHP views
- Migrating admin panel views (`app/Views/`, `app/Views/web/`, `app/Views/includes/`)
- Migrating frontend views (`app/Views/frontend/`)
- Migrating auth/standalone pages (`login.php`, `register.php`, etc.)
- Mapping Bootstrap/AdminLTE class names to Tailwind equivalents
- Deciding on icons, sidebar strategy, or migration approach

---

## Current State (as of March 2026)

| Surface | Layout wrapper | CSS stack | View count |
|---|---|---|---|
| **Admin panel** | `includes/header.php` + `includes/footer.php` | Bootstrap 3.3 (local bower) + AdminLTE 2 + Font Awesome 4 + Ionicons 2 | ~30 views |
| **Frontend (public)** | `frontend/header.php` + `frontend/footer.php` | Bootstrap 4.3 (CDN) + `custom_rk.css` + `customstyle.css` + `flipimage.css` | ~22 views |
| **Auth / standalone** | Loaded via admin `loadViews()` — inherits admin layout | Same as admin | ~5 views |

Tailwind **already exists** in `react-app/tailwind.config.js` with project brand colors, but its `content` glob only covers `./src/**/*.{js,tsx}`. PHP views are excluded and need a separate build.

---

## Phase 1 — Build Pipeline Setup

Create a standalone Tailwind build at the project root (separate from `react-app/`):

### Files to create

**`tailwind.php.config.js`** (project root)
```js
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./app/Views/**/*.php'],
  theme: {
    extend: {
      // Copy brand/dark tokens from react-app/tailwind.config.js
      colors: {
        brand: {
          50: '#fff1f2', 100: '#ffd6d8', 200: '#ffadb1', 300: '#ff7b82',
          400: '#f94d57', 500: '#e11d26', 600: '#ba141c', 700: '#8b0f14',
          800: '#6e0c10', 900: '#520a0d',
        },
        dark: {
          900: '#0b0808', 800: '#130d0d', 700: '#1c1212',
          600: '#221515', 500: '#2e1a1a',
        },
      },
      fontFamily: { sans: ['Source Sans Pro', 'sans-serif'] },
    },
  },
  plugins: [],
}
```

**`public/css/src/php.css`** (Tailwind input)
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom component classes for PHP views */
@layer components {
  /* e.g. .btn-primary, .form-input, .card — see Phase 2/3 */
}
```

**Root `package.json`** scripts (or add to existing if present):
```json
{
  "scripts": {
    "tw:build": "tailwindcss -c tailwind.php.config.js -i public/css/src/php.css -o public/css/tailwind.css --minify",
    "tw:watch": "tailwindcss -c tailwind.php.config.js -i public/css/src/php.css -o public/css/tailwind.css --watch"
  },
  "devDependencies": {
    "tailwindcss": "^3.4.0"
  }
}
```

**Output**: `public/css/tailwind.css` — single file, no CDN dependency.

### Add to header views
```html
<!-- replaces all Bootstrap/AdminLTE <link> tags -->
<link rel="stylesheet" href="<?php echo base_url(); ?>css/tailwind.css">
```

---

## Phase 2 — Migrate Frontend Surface (~22 views)

### Header changes (`app/Views/frontend/header.php`)
- **Remove**: Bootstrap 4.3 CDN `<link>`, `custom_rk.css`, `customstyle.css`
- **Remove**: Bootstrap JS, Popper.js CDN `<script>` tags
- **Add**: `<link rel="stylesheet" href="...css/tailwind.css">`
- **Add**: Alpine.js CDN (`<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">`) for dropdown/toggle behavior
- **Keep**: jQuery (used for AJAX `api/` calls throughout)
- **Keep**: Font Awesome CDN for icons (or upgrade to FA6)

### Custom CSS absorption
Move custom classes from `custom_rk.css` / `customstyle.css` / `flipimage.css` into `@layer components` in `public/css/src/php.css`:
- `.top_header`, `.brand_logo`, `.right_side`, `.user_login` → layout utilities
- `.flip-box`, `.flip-box-inner`, `.flip-box-front`, `.flip-box-back` → 3D card flip
- `.home-card-body`, `.days-Left` → card content
- `.account_menu`, `.myaccount`, `.myaccount_content` → account layout

### Bootstrap → Tailwind class mapping

| Bootstrap 4 | Tailwind CSS |
|---|---|
| `container` | `container mx-auto px-4` |
| `row` | `flex flex-wrap -mx-2` or `grid grid-cols-12 gap-4` |
| `col-md-4` | `w-full md:w-1/3 px-2` |
| `col-md-3` / `col-md-9` | `md:w-1/4` / `md:w-3/4` |
| `col-sm-6 col-3` | `w-1/4 sm:w-1/2` |
| `btn btn-primary` | `bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded font-medium` |
| `btn btn-block btn-light` | `w-full bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded` |
| `btn btn-danger btn-sm` | `bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded` |
| `form-control` | `w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500` |
| `form-group` | `mb-4` |
| `card` | `bg-white rounded-lg shadow` |
| `card-body` | `p-4` |
| `card-img-top` | `w-full rounded-t-lg object-cover` |
| `alert alert-danger` | `bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded` |
| `alert alert-success` | `bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded` |
| `alert alert-warning` | `bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded` |
| `table table-striped table-bordered` | `w-full text-left border-collapse` with `[&_th]:bg-gray-100 [&_td]:border [&_tr:nth-child(even)]:bg-gray-50` |
| `d-flex justify-content-end` | `flex justify-end` |
| `d-flex align-items-center` | `flex items-center` |
| `mb-4` / `mt-3` / `px-4` | Same names exist in Tailwind — direct swap |
| `text-center` / `text-right` | Same in Tailwind |

### Frontend migration order
1. `home.php` — highest visibility, proof-of-concept
2. `game.php` + `game_detail.php` + `components/game_card.php`
3. Account pages: `profile.php`, `order.php`, `wallet.php`, `withdrawl.php`, `tranfer.php`, `refund.php`, `view_tickets.php`, `ticket_details.php`
4. Payment pages: `payment.php`, `payment_confirmation.php`, `confirm_order.php`, `step2.php`
5. Info pages: `contact.php`, `faq.php`, `winner.php`, `result.php`, `jackpot.php`, `page.php`

---

## Phase 3 — Migrate Admin Panel Surface (~30 views)

This is the most complex phase. AdminLTE provides both CSS layout and JS behavior (sidebar push-menu). Both must be replaced.

### Sidebar Strategy Decision (choose one)

**Option A — Keep AdminLTE JS only** (lower effort): Remove AdminLTE CSS, keep `app.min.js` for sidebar toggle. Build sidebar HTML with Tailwind classes that match AdminLTE's DOM structure. Risk: JS depends on specific class names.

**Option B — Rebuild sidebar with Alpine.js** (recommended): Remove all AdminLTE. Replace sidebar JS with 15-line Alpine.js `x-data` pattern. Full control, no dependency.

```html
<!-- Alpine.js sidebar example -->
<div x-data="{ open: true }">
  <aside :class="open ? 'w-64' : 'w-16'" class="transition-all duration-200 bg-gray-900 text-white min-h-screen">
    ...
  </aside>
  <main :class="open ? 'ml-64' : 'ml-16'" class="transition-all duration-200 p-6">
    ...
  </main>
</div>
```

### Header changes (`app/Views/includes/header.php`)
- **Remove**: All AdminLTE, Bootstrap 3, Font Awesome 4, Ionicons `<link>` tags
- **Remove**: `class="hold-transition skin-blue sidebar-mini"` from `<body>`
- **Remove**: `<div class="wrapper">` wrapper (replace with Alpine.js `<div x-data>`)
- **Add**: `tailwind.css` link
- **Add**: Font Awesome 6 free CDN (or local)
- **Add**: Alpine.js CDN

### AdminLTE → Tailwind class mapping

| AdminLTE / Bootstrap 3 | Tailwind replacement |
|---|---|
| `content-wrapper` | `ml-64 p-6 min-h-screen bg-gray-100` |
| `content-header` | `mb-6 flex items-center justify-between` |
| `section.content` | `space-y-6` |
| `box box-primary` | `bg-white rounded-lg shadow` |
| `box-header` | `px-4 py-3 border-b border-gray-200 font-semibold` |
| `box-body` | `p-4` |
| `box-footer` | `px-4 py-3 border-t border-gray-200 flex gap-2` |
| `small-box bg-aqua` | `bg-cyan-500 text-white rounded-lg p-4 flex justify-between items-center` |
| `small-box bg-green` | `bg-green-500 text-white rounded-lg p-4 flex justify-between items-center` |
| `small-box bg-yellow` | `bg-yellow-500 text-white rounded-lg p-4 flex justify-between items-center` |
| `small-box bg-red` | `bg-red-500 text-white rounded-lg p-4 flex justify-between items-center` |
| `small-box-footer` | `text-sm underline  opacity-80 hover:opacity-100` |
| `col-lg-3 col-xs-6` | `w-full sm:w-1/2 lg:w-1/4 px-2` |
| `table table-hover table-bordered table-striped` | `w-full text-sm border-collapse [&_th]:bg-gray-50 [&_th]:px-3 [&_th]:py-2 [&_td]:px-3 [&_td]:py-2 [&_td]:border-b` |
| `btn btn-default` | `bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded text-gray-700` |
| `btn btn-danger btn-xs` | `bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700` |
| `btn btn-success btn-xs` | `bg-green-600 text-white text-xs px-2 py-1 rounded hover:bg-green-700` |
| `input-group` | `flex` |
| `input-group-btn` | `flex-shrink-0` |
| `label label-success` | `inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full` |
| `label label-danger` | `inline-block bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full` |

### Admin migration order
1. `dashboard.php` — layout proof-of-concept, stat cards
2. Listing views: `weblist.php`, `users.php`, `faq.php`, `pagelist.php`, `loginHistory.php`
3. Form views: `addNew.php`, `editOld.php`, `profile.php`, `changePassword.php`
4. Web management: `web/addNew.php`, `web/editOld.php`, `web/rangeedit.php`, `web/descriptionedit.php`, `web/tier.php`, `web/detail.php`
5. Transaction views: `web/order.php`, `web/wallet.php`, `web/user_wallet.php`, `web/user_order.php`, `web/withdrawl.php`, `web/refund.php`, `web/transfer.php`, `web/transfer2.php`, `web/winner.php`
6. Content views: `web/addfaq.php`, `web/editfaq.php`, `web/pageedit.php`, `web/contact.php`

---

## Phase 4 — Auth / Standalone Pages

5 full-page centered-card layouts (rendered without `includes/header` wrapper):

- `login.php` — centered login card
- `register.php` — multi-field registration card
- `forgotPassword.php` + `newPassword.php` — password reset
- `changePassword.php` (admin version)

Pattern:
```html
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-8">
    ...
  </div>
</body>
```

---

## Phase 5 — Cleanup

After all views are migrated and tested:

1. Delete custom CSS files no longer needed:
   - `public/css/custom_rk.css`
   - `public/css/customstyle.css`
   - `public/css/flipimage.css`
   - `public/css/redesign.css`
   - `public/css/admin_redesign.css`

2. The `public/admin/bower_components/` directory (Bootstrap 3, FA4, Ionicons local copies) can also be removed, saving significant disk space.

3. Update `tailwind.php.config.js` `safelist` to include any dynamically-built class strings (e.g. classes set via PHP `$class = 'bg-' . $color . '-500'`).

---

## Key Decisions & Recommendations

| Decision | Recommendation |
|---|---|
| **Admin sidebar JS** | Option B — Alpine.js rebuild. Removes the last AdminLTE JS dependency |
| **Icons** | Keep Font Awesome (FA6 free) — 50+ `fa-*` icon references in views, too many to swap out |
| **Migration strategy** | Surface-by-surface with `preflight: false` on the first pass so Bootstrap coexists temporarily |
| **Admin color scheme** | Dark sidebar (`bg-gray-900`), white content area — matches current AdminLTE feel |
| **Preflight** | Disable (`corePlugins: { preflight: false }`) while Bootstrap still loads on a surface, re-enable after Bootstrap is fully removed |

---

## Total Scope

| Phase | Files touched | Effort |
|---|---|---|
| Phase 1 — Build pipeline | 2–3 config/CSS files | Low |
| Phase 2 — Frontend | 22 views + header/footer | Medium |
| Phase 3 — Admin panel | 30 views + header/footer + sidebar | High |
| Phase 4 — Auth pages | 5 views | Low |
| Phase 5 — Cleanup | delete 5 CSS files | Trivial |

**~60 PHP view files total.** The highest-leverage edits are the two header.php files — each one changes the CSS for an entire surface.
