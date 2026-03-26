<div class="mb-4">
  <a href="<?= base_url() ?>" class="text-decoration-none">
    <h3 class="fw-bold"><?= APP_NAME ?></h3>
  </a>
</div>
<h1 class="auth-title">Register</h1>
<p class="auth-subtitle mb-4">Create a new account to get started.</p>

<?= validation_errors('<div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>', '</div>') ?>
<?php if ($error = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
    <?= esc($error) ?>
  </div>
<?php endif; ?>
<?php if ($success = session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
    <?= esc($success) ?>
  </div>
<?php endif; ?>

<form action="<?= base_url('registerMe') ?>" method="post">
  <div class="form-group position-relative has-icon-left mb-4">
    <input type="text" class="form-control form-control-xl" name="fname"
      placeholder="Full name" required value="<?= set_value('fname') ?>">
    <div class="form-control-icon"><i class="bi bi-person"></i></div>
  </div>

  <div class="form-group position-relative has-icon-left mb-4">
    <input type="text" class="form-control form-control-xl required digits" name="mobile"
      id="mobile" placeholder="Mobile number" maxlength="10" required value="<?= set_value('mobile') ?>">
    <div class="form-control-icon"><i class="bi bi-phone"></i></div>
  </div>

  <div class="form-group position-relative has-icon-left mb-4">
    <input type="email" class="form-control form-control-xl" name="email"
      placeholder="Email address" required value="<?= set_value('email') ?>">
    <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
  </div>

  <div class="form-group position-relative has-icon-left mb-4">
    <input type="password" class="form-control form-control-xl" name="password"
      placeholder="Password" required>
    <div class="form-control-icon"><i class="bi bi-lock"></i></div>
  </div>

  <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg">Create Account</button>
</form>

<hr class="my-4">
<p class="text-center text-muted">
  Already have an account? <a href="<?= base_url('login') ?>" class="fw-semibold">Sign In</a>
</p>