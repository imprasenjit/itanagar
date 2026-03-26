<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> | Admin</title>

  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app-dark.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet" crossorigin="anonymous">

  <style>
    body {
      background-color: var(--bs-body-bg);
    }

    #auth {
      height: 100vh;
      overflow-x: hidden;
    }

    #auth #auth-right {
      height: 100%;
      background: linear-gradient(90deg, #2d499d, #3f5491);
    }

    #auth #auth-left {
      padding: 5rem;
    }

    #auth #auth-left .auth-title {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    #auth #auth-left .auth-subtitle {
      font-size: 1.7rem;
      line-height: 2.5rem;
      color: #a8aebb;
    }

    #auth #auth-left .auth-logo {
      margin-bottom: 7rem;
    }

    #auth #auth-left .auth-logo img {
      height: 2rem;
    }

    @media screen and (max-width: 1399.9px) {
      #auth #auth-left { padding: 3rem; }
    }

    @media screen and (max-width: 767px) {
      #auth #auth-left { padding: 5rem; }
    }

    @media screen and (max-width: 576px) {
      #auth #auth-left { padding: 5rem 3rem; }
    }

    html[data-bs-theme="dark"] #auth #auth-right {
      background: linear-gradient(90deg, #2d499d, #3f5491);
    }
  </style>

  <script src="<?= base_url('assets/js/init-theme.js') ?>"></script>
</head>

<body>
<div id="auth">
  <div class="row h-100">
    <div class="col-lg-5 col-12">
      <div id="auth-left">
