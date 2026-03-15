
<style>
  .auth-section {
    min-height: calc(100vh - 220px);
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    padding: 40px 0;
  }
  .auth-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.10);
    padding: 48px 40px 36px;
    max-width: 440px;
    width: 100%;
    margin: 0 auto;
  }
  .auth-card .auth-logo {
    display: block;
    text-align: center;
    margin-bottom: 8px;
  }
  .auth-card h2 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a1a2e;
    text-align: center;
    margin-bottom: 6px;
  }
  .auth-card .auth-subtitle {
    text-align: center;
    color: #6c757d;
    font-size: 0.92rem;
    margin-bottom: 28px;
  }
  .auth-card .form-control {
    border-radius: 8px;
    height: 48px;
    border: 1.5px solid #dee2e6;
    padding-left: 44px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
  }
  .auth-card .form-control:focus {
    border-color: #343a40;
    box-shadow: 0 0 0 3px rgba(52,58,64,0.1);
  }
  .auth-card .input-icon {
    position: relative;
    margin-bottom: 16px;
  }
  .auth-card .input-icon i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 1rem;
  }
  .auth-card .btn-signin {
    background: #1a1a2e;
    color: #fff;
    border-radius: 8px;
    height: 48px;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    width: 100%;
    margin-top: 8px;
    transition: background 0.2s, transform 0.1s;
  }
  .auth-card .btn-signin:hover {
    background: #16213e;
    transform: translateY(-1px);
  }
  .auth-card .forgot-link {
    display: block;
    text-align: right;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: -8px;
    margin-bottom: 20px;
    text-decoration: none;
  }
  .auth-card .forgot-link:hover { color: #343a40; }
  .auth-card .auth-divider {
    text-align: center;
    color: #adb5bd;
    font-size: 0.85rem;
    margin: 20px 0;
    position: relative;
  }
  .auth-card .auth-divider::before,
  .auth-card .auth-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: #dee2e6;
  }
  .auth-card .auth-divider::before { left: 0; }
  .auth-card .auth-divider::after  { right: 0; }
  .auth-card .register-link {
    text-align: center;
    font-size: 0.92rem;
    color: #6c757d;
    margin-top: 4px;
  }
  .auth-card .register-link a {
    color: #1a1a2e;
    font-weight: 600;
    text-decoration: none;
  }
  .auth-card .register-link a:hover { text-decoration: underline; }
  .toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #adb5bd;
    font-size: 0.95rem;
  }
  .toggle-password:hover { color: #343a40; }
</style>

<section class="auth-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="auth-card">

          <a class="auth-logo" href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url(); ?>images/logo.png" alt="Itanagar Choice" style="height:52px;">
          </a>

          <h2>Welcome back</h2>
          <p class="auth-subtitle">Sign in to your account to continue</p>

          <?php echo validation_errors('<div class="alert alert-danger alert-dismissable py-2">', '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>'); ?>

          <?php $error = session()->getFlashdata('error'); if ($error): ?>
            <div class="alert alert-danger alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($error); ?>
            </div>
          <?php endif; ?>

          <?php $success = session()->getFlashdata('success'); if ($success): ?>
            <div class="alert alert-success alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($success); ?>
            </div>
          <?php endif; ?>

          <form action="<?php echo base_url('loginMe'); ?>" method="post">

            <div class="input-icon">
              <i class="fas fa-envelope"></i>
              <input type="text" class="form-control" name="email" placeholder="Email address" required autocomplete="email" />
            </div>

            <div class="input-icon" style="position:relative;">
              <i class="fas fa-lock"></i>
              <input type="password" class="form-control" name="password" id="loginPassword" placeholder="Password" required autocomplete="current-password" />
              <span class="toggle-password" onclick="togglePassword('loginPassword', this)">
                <i class="fas fa-eye"></i>
              </span>
            </div>

            <a class="forgot-link" href="<?php echo base_url('forgotPassword'); ?>">Forgot password?</a>

            <button type="submit" class="btn btn-signin">Sign In</button>
          </form>

          <div class="auth-divider">or</div>

          <p class="register-link">
            Don't have an account? <a href="<?php echo base_url('register'); ?>">Create one</a>
          </p>

        </div>
      </div>
    </div>
  </div>
</section>

<script>
function togglePassword(id, icon) {
  var input = document.getElementById(id);
  var i = icon.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    i.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    i.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>
