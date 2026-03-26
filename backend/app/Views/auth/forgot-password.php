<div class="mb-4">
  <a href="<?= base_url() ?>" class="text-decoration-none">
    <h3 class="fw-bold"><?= APP_NAME ?></h3>
  </a>
</div>
<h1 class="auth-title">Forgot Password</h1>
<p class="auth-subtitle mb-4">Enter your email and we will send you a reset link.</p>

<?= validation_errors('<div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>', '</div>') ?>
<?php foreach (['error'=>'danger','send'=>'success','notsend'=>'danger','unable'=>'danger','invalid'=>'warning'] as $key=>$type):
  if ($msg = session()->getFlashdata($key)): ?>
  <div class="alert alert-<?= $type ?> alert-dismissible">
    <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
    <?= esc($msg) ?>
  </div>
<?php endif; endforeach; ?>

<form action="<?= base_url('resetPasswordUser') ?>" method="post">
  <div class="form-group position-relative has-icon-left mb-4">
    <input type="email" class="form-control form-control-xl" name="login_email"
      placeholder="Email address" required autocomplete="email">
    <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
  </div>
  <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg">Send Reset Link</button>
</form>

<hr class="my-4">
<p class="text-center text-muted">
  Remembered it? <a href="<?= base_url('login') ?>" class="fw-semibold">Back to Sign In</a>
</p>