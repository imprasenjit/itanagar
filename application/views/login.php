<link href="<?php echo base_url(); ?>assets/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />




<div class="login-box">

  <div class="login-box-body" style="background:#dcd7d7">
    <p class="login-box-msg">Sign In</p>
    <?php $this->load->helper('form'); ?>
    <div class="row">
      <div class="col-md-12">
        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
      </div>
    </div>
    <?php
    $this->load->helper('form');
    $error = $this->session->flashdata('error');
    if ($error) {
    ?>
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo $error; ?>
      </div>
    <?php }
    $success = $this->session->flashdata('success');
    if ($success) {
    ?>
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo $success; ?>
      </div>
    <?php } ?>
    <form action="<?php echo base_url(); ?>loginMe" method="post">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Mobile OR Email " name="email" required />
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" required />
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">

        <div class="col-xs-12">
          <input type="submit" class="btn btn-dark btn-block custombtn" value="Sign In" />
        </div><!-- /.col -->
      </div>
    </form>

    <a class="forgotpass" href="<?php echo base_url() ?>forgotPassword">Forgot Password</a>
    <p class="account_t">Don't have an account <a href="<?php echo base_url() ?>register">Register</a></p>

  </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<script src="<?php echo base_url(); ?>assets/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>