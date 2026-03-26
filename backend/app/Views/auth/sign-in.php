<div class="mb-4">
  <a href="<?= base_url() ?>" class="text-decoration-none">
    <h3 class="fw-bold"><?= APP_NAME ?></h3>
  </a>
</div>
<h1 class="auth-title">Sign In</h1>
<p class="auth-subtitle mb-4">Welcome back! Sign in to continue.</p>

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

<form action="<?= base_url('loginMe') ?>" method="post">
  <div class="form-group position-relative has-icon-left mb-4">
    <input type="text" class="form-control form-control-xl" name="email"
      placeholder="Email address" required autocomplete="email">
    <div class="form-control-icon"><i class="bi bi-person"></i></div>
  </div>

  <div class="form-group position-relative has-icon-left mb-3">
    <input type="password" class="form-control form-control-xl" name="password" id="loginPwd"
      placeholder="Password" required autocomplete="current-password">
    <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
    <span class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor:pointer"
          onclick="togglePwd('loginPwd', this)">
      <i class="bi bi-eye text-muted"></i>
    </span>
  </div>

  <div class="d-flex justify-content-end mb-4">
    <a href="<?= base_url('forgotPassword') ?>" class="text-muted small">Forgot password?</a>
  </div>

  <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg">Sign In</button>
</form>

<hr class="my-4">
<p class="text-center text-muted">
  Don&apos;t have an account? <a href="<?= base_url('register') ?>" class="fw-semibold">Create one</a>
</p>

<script>
function togglePwd(id, el) {
  var input = document.getElementById(id);
  var icon = el.querySelector('i');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'bi bi-eye text-muted' : 'bi bi-eye-slash text-muted';
}
</script>