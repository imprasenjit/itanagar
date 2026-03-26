<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($pageTitle) ? esc($pageTitle) : APP_NAME ?> | Admin</title>
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- AdminLTE 4 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta.3/dist/css/adminlte.min.css">
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap">
  <style>
    .error { color: red; font-weight: normal; }
  </style>
  <!-- jQuery (needed by validate.js) -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script>var baseURL = "<?php echo base_url(); ?>";</script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  <!-- Navbar -->
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="fas fa-bars"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="<?= base_url('dashboard') ?>" class="nav-link">
            <i class="fas fa-home"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <!-- Last login badge -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <i class="fas fa-clock"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-end">
            <span class="dropdown-item-text text-muted small">
              Last Login: <?= empty($last_login) ? 'First Time Login' : esc($last_login) ?>
            </span>
          </div>
        </li>
        <!-- User menu -->
        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
            <img src="<?= base_url('admin/dist/img/avatar.png') ?>" class="user-image rounded-circle shadow" alt="User Image">
            <span class="d-none d-md-inline"><?= esc($name) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header d-flex align-items-center px-3 py-2">
              <img src="<?= base_url('admin/dist/img/avatar.png') ?>" class="rounded-circle me-2" width="40" alt="">
              <div>
                <strong><?= esc($name) ?></strong>
                <small class="d-block text-muted"><?= esc($role_text) ?></small>
              </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a href="<?= base_url('profile') ?>" class="dropdown-item">
                <i class="fas fa-user-circle me-2 text-warning"></i> Profile
              </a>
            </li>
            <li>
              <a href="<?= base_url('logout') ?>" class="dropdown-item">
                <i class="fas fa-right-from-bracket me-2 text-secondary"></i> Sign out
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="app-sidebar bg-dark navbar-dark shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="<?= base_url('dashboard') ?>" class="brand-link">
        <span class="brand-text fw-semibold"><?= APP_NAME ?></span>
      </a>
    </div>
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-gauge-high"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <?php if ($role == ROLE_ADMIN): ?>
          <li class="nav-item">
            <a href="<?= base_url('userListing') ?>" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('web/common') ?>" class="nav-link">
              <i class="nav-icon fas fa-gear"></i>
              <p>Common Settings</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('web') ?>" class="nav-link">
              <i class="nav-icon fas fa-plus"></i>
              <p>Lottery Games</p>
            </a>
          </li>
          <?php
            $web = (new \App\Models\WebModel())->get_allweb();
            foreach ($web as $w) {
              if ($w->status == 0) {
          ?>
          <li class="nav-item">
            <a href="<?= base_url('web/view/' . $w->id) ?>" class="nav-link">
              <i class="nav-icon far fa-circle"></i>
              <p><?= esc($w->name) ?></p>
            </a>
          </li>
          <?php
              }
            }
          endif; ?>

          <li class="nav-item">
            <a href="<?= base_url('web/page') ?>" class="nav-link">
              <i class="nav-icon fas fa-file"></i>
              <p>Pages</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('web/faq') ?>" class="nav-link">
              <i class="nav-icon fas fa-bullhorn"></i>
              <p>Announcements</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('web/contact_list') ?>" class="nav-link">
              <i class="nav-icon fas fa-envelope"></i>
              <p>Contact Requests</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url('web/order') ?>" class="nav-link">
              <i class="nav-icon fas fa-clock-rotate-left"></i>
              <p>Orders</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
  </aside>
                    </li>
                  </ul>
                </li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
              </ul>
            </li>
          --> <?php


              if ($role == ROLE_ADMIN) {
              ?>
            <li>
              <a href="<?php echo base_url(); ?>userListing">
                <i class="fa fa-users"></i>
                <span>Users</span>
              </a>
            </li>



            <li>
              <a href="<?php echo base_url(); ?>web/common">
                <i class="fa fa-cog"></i>
                <span>Common Settings</span>
              </a>
            </li>


            <li>
              <a href="<?php echo base_url(); ?>web">
                <i class="fa fa-plus"></i>
                <span>Lottery Games</span>
              </a>
            </li>

            <?php
                $web = (new \App\Models\WebModel())->get_allweb();
                foreach ($web as $w) {
                  if ($w->status == 0) {
            ?>
                <li>
                  <a href="<?php echo base_url(); ?>web/view/<?= $w->id; ?>">
                    <i class="fa fa-circle-o"></i>
                    <span><?= $w->name ?></span>
                  </a>
                </li>
          <?php
                  }
                }
              }
          ?>

          <li>
            <a href="<?php echo base_url('web/page'); ?>">
              <i class="fa fa-file"></i>
              <span>Pages</span>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?= base_url('web') ?>" class="nav-link">
              <i class="nav-icon fas fa-clock-rotate-left"></i>
              <p>Games</p>
            </a>
          </li>


          <li class="nav-item">
            <a href="<?= base_url('userListing') ?>" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
  </aside>
  <!-- Main content wrapper -->
  <main class="app-main">