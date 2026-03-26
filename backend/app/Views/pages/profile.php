<?php
$userId = $userInfo->userId;
$name   = $userInfo->name;
$email  = $userInfo->email;
$mobile = $userInfo->mobile;
$roleId = $userInfo->roleId;
$role   = $userInfo->role;
?>
<div class="page-heading">
    <h3><i class="bi bi-person-circle me-2"></i> My Profile <small>View or modify your information</small></h3>
</div>
<section class="section">
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

    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body py-4">
                    <div class="avatar avatar-xl mb-3">
                        <img src="<?= base_url() ?>public/admin/dist/img/avatar.png" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                    </div>
                    <h5 class="mb-1"><?= esc($name) ?></h5>
                    <p class="text-muted mb-3"><?= esc($role) ?></p>
                    <ul class="list-group list-group-flush text-start">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Email</strong>
                            <span class="text-muted"><?= esc($email) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Mobile</strong>
                            <span class="text-muted"><?= esc($mobile) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTab">
                        <li class="nav-item">
                            <a class="nav-link <?= ($active == 'details') ? 'active' : '' ?>" data-bs-toggle="tab" href="#details">
                                <i class="bi bi-person-fill me-1"></i> Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($active == 'changepass') ? 'active' : '' ?>" data-bs-toggle="tab" href="#changepass">
                                <i class="bi bi-key-fill me-1"></i> Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content pt-2">
                        <div class="tab-pane <?= ($active == 'details') ? 'active' : '' ?>" id="details">
                            <form action="<?= base_url() ?>profileUpdate" method="post" id="editProfile">
                                <input type="hidden" name="userId" value="<?= $userId ?>">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fname" value="<?= set_value('fname', $name) ?>" maxlength="128">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" name="mobile" value="<?= set_value('mobile', $mobile) ?>" maxlength="10">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control" name="email" value="<?= set_value('email', $email) ?>">
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary me-1">Save Changes</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane <?= ($active == 'changepass') ? 'active' : '' ?>" id="changepass">
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
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary me-1">Change Password</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url() ?>public/admin/js/editUser.js" type="text/javascript"></script>
