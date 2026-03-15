<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="<?php echo base_url(); ?>/css/custom_rk.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">


  <link rel="stylesheet" href="<?php echo base_url(); ?>/css/customstyle.css">

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

  <script>
    var baseURL = "<?php echo base_url(); ?>";
  </script>
  <title>Itanagr Choice</title>
</head>

<body>


  <div class="top_header">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-sm-6 col-3">
          <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url(); ?>/images/logo.png" class="brand_logo"></a>
          <!-- <a href="<?php echo base_url(); ?>">Itanagar Choice</a> -->
        </div>
        <div class="col-sm-6 col-9">
          <div class="right_side d-flex justify-content-end">
            <div class="right_inn">

              <?php
              if (session('isLoggedIn') == TRUE) {

                $u_cs_id = session("userId");

              ?>
                <ul class="user_login">
                  <li><a href="<?php echo base_url('logout'); ?>">Logout</a></li>
                  <?php
                  if (session("role") == 1) {
                  ?>
                    <li><a href="<?php echo base_url('dashboard'); ?>">Dashboard</a></li>
                  <?php
                  } else {
                  ?>
                    <li><a href="<?php echo base_url('account'); ?>">My Account</a></li>
                  <?php
                  }
                  ?>
                </ul>

              <?php
              } ?>
               



            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- <nav class="navbar navbar-expand-lg navbar-dark  custom_bg">
    <div class="container">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link" href="<?php echo base_url() ?>">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('faq'); ?>">Announcements</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url('game/result'); ?>">Notifications</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?php echo base_url('contact'); ?>">Contact Us</a>
          </li>
        </ul>
      </div>
    </div>
  </nav> -->
  