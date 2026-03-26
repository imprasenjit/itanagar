<div class="mb-4">
  <a href="<?= base_url() ?>" class="text-decoration-none">
    <h3 class="fw-bold"><?= APP_NAME ?></h3>
  </a>
</div>
<h1 class="auth-title">Reset Password</h1>
<p class="auth-subtitle mb-4">Create a new secure password for your account.</p>

<?= validation_errors('<div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>', '</div>') ?>
<?php if ($error = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
    <?= esc($error) ?>
  </div>
<?php endif; ?>

<form action="<?= base_url('createPasswordUser') ?>" method="post">
  <div class="form-group position-relative has-icon-left mb-4">
    <input type="email" class="form-control form-control-xl" name="email"
      value="<?= esc($email) ?>" readonly required>
    <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
  </div>
  <input type="hidden" name="activation_code" value="<?= esc($activation_code) ?>">

  <div class="form-group position-relative has-icon-left mb-4">
    <input type="password" class="form-control form-control-xl" name="password"
      placeholder="New password" required>
    <div class="form-control-icon"><i class="bi bi-lock"></i></div>
  </div>

  <div class="form-group position-relative has-icon-left mb-4">
    <input type="password" class="form-control form-control-xl" name="cpassword"
      placeholder="Confirm new password" required>
    <div class="form-control-icon"><i class="bi bi-lock-fill"></i></div>
  </div>

  <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg">Set New Password</button>
</form>