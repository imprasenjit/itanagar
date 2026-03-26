<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? esc($pageTitle) : APP_NAME ?> | Admin</title>

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app-dark.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/iconly.css') ?>">
  <!-- Font Awesome 6 (for existing view icons) -->
  <link rel="stylesheet" href="<?= base_url('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') ?>">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .error { color: #dc3545; font-weight: normal; font-size: 0.85rem; }
    /* Page heading */
    .page-heading { margin: 1.5rem 1.5rem 0.5rem; }
    .page-heading h3 { font-size: 1.3rem; font-weight: 600; color: var(--bs-heading-color); }
    /* Compat shims for old view wrappers */
    .app-content-header h1, .app-content-header h3 { font-size: 1.3rem; font-weight: 600; }
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

          <?php if ($role == ROLE_ADMIN): ?>

          <li class="sidebar-title">Management</li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'userListing') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('userListing') ?>" class="sidebar-link">
              <i class="bi bi-people-fill"></i>
              <span>Users</span>
            </a>
          </li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'web') !== false && strpos(uri_string(), 'order') === false) ? 'active' : '' ?> has-sub">
            <a href="#" class="sidebar-link">
              <i class="bi bi-ticket-perforated-fill"></i>
              <span>Lottery Games</span>
            </a>
            <ul class="submenu">
              <li class="submenu-item <?= (uri_string() === 'web') ? 'active' : '' ?>">
                <a href="<?= base_url('web') ?>" class="submenu-link">All Games</a>
              </li>
              <li class="submenu-item <?= (uri_string() === 'web/addNew') ? 'active' : '' ?>">
                <a href="<?= base_url('web/addNew') ?>" class="submenu-link">Add New Game</a>
              </li>
              <?php
                $web = (new \App\Models\WebModel())->get_allweb();
                foreach ($web as $w):
                  if ($w->status == 0):
              ?>
              <li class="submenu-item">
                <a href="<?= base_url('web/view/' . $w->id) ?>" class="submenu-link">
                  &bull; <?= esc($w->name) ?>
                </a>
              </li>
              <?php
                  endif;
                endforeach;
              ?>
            </ul>
          </li>

          <li class="sidebar-item <?= (uri_string() === 'web/common') ? 'active' : '' ?>">
            <a href="<?= base_url('web/common') ?>" class="sidebar-link">
              <i class="bi bi-gear-fill"></i>
              <span>Common Settings</span>
            </a>
          </li>

          <?php endif; ?>

          <li class="sidebar-title">Orders & Finance</li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'order') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/order') ?>" class="sidebar-link">
              <i class="bi bi-receipt"></i>
              <span>Orders</span>
            </a>
          </li>

          <li class="sidebar-title">Content</li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'contact') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/contact_list') ?>" class="sidebar-link">
              <i class="bi bi-envelope-fill"></i>
              <span>Contact Requests</span>
            </a>
          </li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'faq') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/faq') ?>" class="sidebar-link">
              <i class="bi bi-megaphone-fill"></i>
              <span>Announcements</span>
            </a>
          </li>

          <li class="sidebar-item <?= (strpos(uri_string(), 'page') !== false) ? 'active' : '' ?>">
            <a href="<?= base_url('web/page') ?>" class="sidebar-link">
              <i class="bi bi-file-earmark-text-fill"></i>
              <span>Pages</span>
            </a>
          </li>

        </ul>
      </div>
    </div>
  </div>
  <!-- ====== END SIDEBAR ====== -->

  <div id="main">

    <!-- ====== TOP NAVBAR ====== -->
    <header class="mb-3">
      <nav class="navbar navbar-expand px-3 border-bottom">
        <button class="btn burger-btn d-xl-none" type="button">
          <i class="bi bi-justify fs-3"></i>
        </button>

        <div class="navbar-nav ms-auto">

          <!-- Dark mode toggle -->
          <div class="nav-item dropdown me-2 d-flex align-items-center">
            <a href="#" class="nav-link" id="dark-toggle" title="Toggle dark mode">
              <i class="bi bi-moon-stars-fill fs-5"></i>
            </a>
          </div>

          <!-- Last login -->
          <div class="nav-item dropdown me-2">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-clock-history fs-5"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow px-3 py-2">
              <small class="text-muted">
                Last Login: <?= empty($last_login) ? 'First Login' : esc($last_login) ?>
              </small>
            </div>
          </div>

          <!-- User dropdown -->
          <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
              <div class="avatar me-2 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                <i class="bi bi-person-fill text-primary"></i>
              </div>
              <span class="d-none d-md-inline fw-semibold"><?= esc($name) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li class="dropdown-header px-3 py-2">
                <strong><?= esc($name) ?></strong>
                <small class="d-block text-muted"><?= esc($role_text) ?></small>
              </li>
              <li><hr class="dropdown-divider m-0"></li>
              <li>
                <a href="<?= base_url('profile') ?>" class="dropdown-item">
                  <i class="bi bi-person-circle me-2 text-warning"></i> Profile
                </a>
              </li>
              <li>
                <a href="<?= base_url('logout') ?>" class="dropdown-item">
                  <i class="bi bi-box-arrow-right me-2 text-secondary"></i> Sign Out
                </a>
              </li>
            </ul>
          </div>

        </div>
      </nav>
    </header>
    <!-- ====== END TOP NAVBAR ====== -->

    <div class="page-content">
