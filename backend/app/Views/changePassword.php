<div class="page-heading">
    <h3><i class="bi bi-key-fill me-2"></i> Change Password <small>Set a new password for your account</small></h3>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-5">
            <?php $error = session()->getFlashdata('error'); if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?= $error ?>
            </div>
            <?php endif; ?>
            <?php $success = session()->getFlashdata('success'); if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?= $success ?>
            </div>
            <?php endif; ?>
            <?php $noMatch = session()->getFlashdata('nomatch'); if ($noMatch): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?= $noMatch ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Change Password</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>changePassword" method="post">
                        <div class="form-group">
                            <label class="form-label">Old Password</label>
                            <input type="password" class="form-control" name="oldPassword" placeholder="Old password" maxlength="20" required>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" id="inputPassword1" name="newPassword" placeholder="New password" maxlength="20" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="inputPassword2" name="cNewPassword" placeholder="Confirm new password" maxlength="20" required>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Submit</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
