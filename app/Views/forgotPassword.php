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
  .auth-card .register-link {
    text-align: center;
    font-size: 0.92rem;
    color: #6c757d;
    margin-top: 20px;
  }
  .auth-card .register-link a {
    color: #1a1a2e;
    font-weight: 600;
    text-decoration: none;
  }
  .auth-card .register-link a:hover { text-decoration: underline; }
</style>

<section class="auth-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <div class="auth-card">

          <a class="auth-logo" href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url(); ?>images/logo.png" alt="Itanagar Choice" style="height:52px;">
          </a>

          <h2>Forgot Password</h2>
          <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>

          <?php echo validation_errors('<div class="alert alert-danger alert-dismissable py-2">', '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>'); ?>

          <?php $error = session()->getFlashdata('error'); if ($error): ?>
            <div class="alert alert-danger alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($error); ?>
            </div>
          <?php endif; ?>

          <?php $send = session()->getFlashdata('send'); if ($send): ?>
            <div class="alert alert-success alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($send); ?>
            </div>
          <?php endif; ?>

          <?php $notsend = session()->getFlashdata('notsend'); if ($notsend): ?>
            <div class="alert alert-danger alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($notsend); ?>
            </div>
          <?php endif; ?>

          <?php $unable = session()->getFlashdata('unable'); if ($unable): ?>
            <div class="alert alert-danger alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($unable); ?>
            </div>
          <?php endif; ?>

          <?php $invalid = session()->getFlashdata('invalid'); if ($invalid): ?>
            <div class="alert alert-warning alert-dismissable py-2">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <?php echo esc($invalid); ?>
            </div>
          <?php endif; ?>

          <form action="<?php echo base_url('resetPasswordUser'); ?>" method="post">
            <div class="input-icon">
              <i class="fas fa-envelope"></i>
              <input type="email" class="form-control" name="login_email" placeholder="Email address" required autocomplete="email" />
            </div>

            <button type="submit" class="btn btn-signin">Send Reset Link</button>
          </form>

          <p class="register-link">
            Remembered it? <a href="<?php echo base_url('login'); ?>">Back to Login</a>
          </p>

        </div>
      </div>
    </div>
  </div>
</section>
