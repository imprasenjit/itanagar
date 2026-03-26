<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> — Admin</title>
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app-dark.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bs-body-bg); }
    #auth { height: 100vh; overflow-x: hidden; }
    #auth #auth-left { padding: 5rem; }
    #auth #auth-left .auth-title { font-size: 3rem; margin-bottom: 0.5rem; }
    #auth #auth-right {
      height: 100%;
      background: linear-gradient(135deg, #1a3a6b 0%, #2d499d 100%);
    }
    @media (max-width: 1399.9px) { #auth #auth-left { padding: 3rem; } }
    @media (max-width: 767px) { #auth #auth-left { padding: 3rem 1.5rem; } }
  </style>
  <script src="<?= base_url('assets/js/init-theme.js') ?>"></script>
</head>
<body>
<div id="auth">
  <div class="row h-100">
    <div class="col-lg-5 col-12">
      <div id="auth-left">
