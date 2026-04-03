<?php
$userId = $userInfo->userId;
$name   = $userInfo->name;
$email  = $userInfo->email;
$mobile = $userInfo->mobile;
$roleId = $userInfo->roleId;
?>
<div class="page-heading">
    <h3><i class="bi bi-person-gear me-2"></i> User Management <small>Edit User</small></h3>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-8">
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
            <?php if (function_exists('validation_errors')): ?>
                <?= validation_errors('<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>', '</div>') ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit User Details</h4>
                </div>
                <div class="card-body">
                    <form id="editUser" action="<?= base_url() ?>editUser" method="post">
                        <input type="hidden" name="userId" value="<?= $userId ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="fname" value="<?= $name ?>" maxlength="128">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?= $email ?>" maxlength="128">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                                    <input type="password" class="form-control" id="password" name="password" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="cpassword" name="cpassword" maxlength="20">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Code</label>
                                    <select class="form-select" name="phonecode" required>
                                        <option value="">Choose Country Phone Code</option>
                                        <?php foreach ($country as $c): ?>
                                        <?php $code = $c->phonecode ?? ''; $saved = $userInfo->phonecode ?? '91'; ?>
                                        <option value="<?= $code ?>" <?= $saved == $code ? 'selected' : '' ?>><?= esc($c->name) . ($code !== '' ? " (+" . $code . ")" : '') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" name="mobile" value="<?= $mobile ?>" maxlength="10">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="0">Select Role</option>
                                        <?php if (!empty($roles)) foreach ($roles as $rl): ?>
                                        <option value="<?= $rl->roleId ?>" <?= $rl->roleId == $roleId ? 'selected' : '' ?>><?= $rl->role ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Update</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url() ?>public/admin/js/editUser.js" type="text/javascript"></script>
