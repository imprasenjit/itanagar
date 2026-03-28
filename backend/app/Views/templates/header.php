<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? esc($pageTitle) : APP_NAME ?> | Admin</title>

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app-dark.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/iconly.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet" crossorigin="anonymous">

  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .error { color: #dc3545; font-weight: normal; font-size: 0.85rem; }
  </style>

  <script src="<?= base_url('assets/js/init-theme.js') ?>"></script>
  <script>var baseURL = "<?= base_url() ?>";</script>
</head>

<body>
<div id="app">

  <!-- ====== SIDEBAR ====== -->
  <div id="sidebar">
    <div class="sidebar-wrapper active">

      <div class="sidebar-header position-relative">
        <div class="d-flex justify-content-between align-items-center">
          <div class="logo">
            <a href="<?= base_url('dashboard') ?>" class="fw-bold fs-5 text-decoration-none">
              <?= APP_NAME ?>
            </a>
          </div>
          <div class="sidebar-toggler x">
            <a href="#" class="sidebar-hide d-xl-none d-block">
              <i class="bi bi-x bi-middle"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="sidebar-menu">
        <ul class="menu">
          <li class="sidebar-title">Navigation</li>

          <li class="sidebar-item <?= (uri_string() === 'dashboard') ? 'active' : '' ?>">
            <a href="<?= base_url('dashboard') ?>" class="sidebar-link">
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard</span>
            </a>
          </li>

          <?php
            $can = $can ?? function (string $k): bool { return false; };
            $showManagement = $can('users.view') || $can('games.view') || $can('games.create') || $can('games.settings');
            $showOrders     = $can('orders.view') || $can('transactions.view') || $can('tickets.view') || $can('winners.view') || $can('reports.view');
            $showContent    = $can('contact.view') || $can('faq.view') || $can('pages.view');
            $showSettings   = $can('rbac.manage');
          ?>

          <?php if ($showManagement): ?>
          <li class="sidebar-title">Management</li>
          <?php endif; ?>

          <?php if ($can('users.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'userListing') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('userListing') ?>" class="sidebar-link">
              <i class="bi bi-people-fill"></i>
              <span>Users</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('games.view') || $can('games.create')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'web') !== false && strpos(uri_string(), 'order') === false && strpos(uri_string(), 'wallet') === false && strpos(uri_string(), 'winner') === false && strpos(uri_string(), 'refund') === false && strpos(uri_string(), 'withdrawl') === false && strpos(uri_string(), 'transfer') === false) ? 'active' : '' ?> has-sub">
            <a href="#" class="sidebar-link">
              <i class="bi bi-ticket-perforated-fill"></i>
              <span>event Games</span>
            </a>
            <ul class="submenu">
              <?php if ($can('games.view')): ?>
              <li class="submenu-item <?= (uri_string() === 'web') ? 'active' : '' ?>">
                <a href="<?= base_url('web') ?>" class="submenu-link">All Games</a>
              </li>
              <?php endif; ?>
              <?php if ($can('games.create')): ?>
              <li class="submenu-item <?= (uri_string() === 'web/addNew') ? 'active' : '' ?>">
                <a href="<?= base_url('web/addNew') ?>" class="submenu-link">Add New Game</a>
              </li>
              <?php endif; ?>
              <?php if ($can('games.view')):
                $web = (new \App\Models\WebModel())->get_allweb();
                foreach ($web as $w):
                  if ($w->status == 0):
              ?>
              <li class="submenu-item">
                <a href="<?= base_url('web/view/' . $w->id) ?>" class="submenu-link">
                  &bull; <?= esc($w->name) ?>
                </a>
              </li>
              <?php   endif;
                endforeach;
              endif; ?>
            </ul>
          </li>
          <?php endif; ?>

          <?php if ($can('games.settings')): ?>
          <li class="sidebar-item <?= (uri_string() === 'web/common') ? 'active' : '' ?>">
            <a href="<?= base_url('web/common') ?>" class="sidebar-link">
              <i class="bi bi-gear-fill"></i>
              <span>Common Settings</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($showOrders): ?>
          <li class="sidebar-title">Orders &amp; Finance</li>
          <?php endif; ?>

          <?php if ($can('orders.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'web/order') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/order') ?>" class="sidebar-link">
              <i class="bi bi-receipt"></i>
              <span>Orders</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('transactions.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'web/transactions') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/transactions') ?>" class="sidebar-link">
              <i class="bi bi-arrow-left-right"></i>
              <span>Transactions</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('tickets.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'web/tickets') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/tickets') ?>" class="sidebar-link">
              <i class="bi bi-ticket-detailed-fill"></i>
              <span>Ticket Management</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('winners.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'winner') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/winner') ?>" class="sidebar-link">
              <i class="bi bi-trophy-fill"></i>
              <span>Winners</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('reports.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'web/reports') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/reports') ?>" class="sidebar-link">
              <i class="bi bi-file-earmark-bar-graph-fill"></i>
              <span>Reports</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($showContent): ?>
          <li class="sidebar-title">Content</li>
          <?php endif; ?>

          <?php if ($can('contact.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'contact') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/contact_list') ?>" class="sidebar-link">
              <i class="bi bi-envelope-fill"></i>
              <span>Contact Requests</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('faq.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'faq') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/faq') ?>" class="sidebar-link">
              <i class="bi bi-megaphone-fill"></i>
              <span>Announcements</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($can('pages.view')): ?>
          <li class="sidebar-item <?= (strpos(uri_string(), 'page') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/page') ?>" class="sidebar-link">
              <i class="bi bi-file-earmark-text-fill"></i>
              <span>Pages</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($showSettings): ?>
          <li class="sidebar-title">Settings</li>
          <li class="sidebar-item <?= (uri_string() === 'web/roles' || strpos(uri_string(), 'web/editRole') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/roles') ?>" class="sidebar-link">
              <i class="bi bi-people-fill"></i>
              <span>Roles</span>
            </a>
          </li>
          <li class="sidebar-item <?= (uri_string() === 'web/rbac') ? 'active' : '' ?>">
            <a href="<?= base_url('web/rbac') ?>" class="sidebar-link">
              <i class="bi bi-shield-lock-fill"></i>
              <span>Role Permissions</span>
            </a>
          </li>
          <li class="sidebar-item <?= (uri_string() === 'web/migrations') ? 'active' : '' ?>">
            <a href="<?= base_url('web/migrations') ?>" class="sidebar-link">
              <i class="bi bi-database-gear"></i>
              <span>Migrations</span>
            </a>
          </li>
          <?php endif; ?>


        </ul>
      </div>
    </div>
  </div>
  <!-- ====== END SIDEBAR ====== -->

  <div id="main">

    <!-- ====== PAGE HEADING (matches reference templates/header.php exactly) ====== -->
    <div class="page-heading">
      <div class="d-flex justify-content-between align-items-center">

        <header class="d-block d-xl-none pb-2">
          <a href="#" class="d-block burger-btn d-xl-none">
            <i class="bi bi-justify fs-3"></i>
          </a>
        </header>

        <h3 class="text-center"><?= APP_NAME ?></h3>

        <div class="dropdown">
          <span class="text-end me-3 d-none d-xl-inline"><?= esc($name) ?></span>
          <a href="#" data-bs-toggle="dropdown" class="dropdown-toggle" style="cursor:pointer;" data-bs-auto-close="outside">
            <div class="avatar bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width:42px;height:42px;font-size:1.1rem;">
              <?= strtoupper(substr($name, 0, 1)) ?>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end mt-2">
            <h6 class="dropdown-header text-center">
              <div>Welcome,</div>
              <h6 class="text-center my-2"><?= esc($name) ?></h6>
              <p class="text-center mb-0">(<?= esc($role_text) ?>)</p>
            </h6>

            <div class="dropdown-item d-flex align-items-center justify-content-between mt-2" for="toggle-dark" style="cursor:pointer;">
              <span>Dark Mode</span>
              <div class="theme-toggle d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                  <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2" opacity=".3"></path>
                    <g transform="translate(-210 -1)">
                      <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                      <circle cx="220.5" cy="11.5" r="4"></circle>
                      <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                    </g>
                  </g>
                </svg>
                <div class="form-check form-switch ms-1 fs-6">
                  <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                  <label class="form-check-label"></label>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                  <path fill="currentColor" d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z"></path>
                </svg>
              </div>
            </div>

            <div class="dropdown-divider"></div>

            <a class="dropdown-item align-self-center" href="<?= base_url('profile') ?>">
              <i class="bi bi-person-circle me-2"></i> Profile
            </a>
            <a class="dropdown-item align-self-center" href="<?= base_url('logout') ?>">
              <i class="bi bi-box-arrow-right me-2"></i> Sign Out
            </a>
          </div>
        </div>

      </div>
    </div>
    <!-- ====== END PAGE HEADING ====== -->

    <div class="page-content">
