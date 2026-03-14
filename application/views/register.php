<link href="<?php echo base_url(); ?>assets/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
<div class="login-box">
  <div class="login-box-body" style="background:#dcd7d7">
    <p class="login-box-msg">Register</p>
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
    <form action="<?php echo base_url(); ?>registerMe" method="post">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Full Name" name="fname" required value="<?php echo set_value('fname'); ?>" />
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="text" class="form-control required digits" required id="mobile" value="<?php echo set_value('mobile'); ?>" name="mobile" maxlength="10" placeholder="Mobile">
        <span class="glyphicon glyphicon-phone form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" required />
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="Email (Optional)" name="email" required value="<?php echo set_value('email'); ?>" />
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <input type="submit" class="btn btn-dark btn-block custombtn " value="Register" />
        </div><!-- /.col -->
      </div>
    </form>
    <p class="account_t pt-4">I have an account <a href="<?php echo base_url() ?>login">Sign In</a></p>
  </div><!-- /.login-box-body -->
</div><!-- /.login-box -->
<script src="<?php echo base_url(); ?>assets/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>