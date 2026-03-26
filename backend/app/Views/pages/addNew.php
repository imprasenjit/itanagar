<div class="page-heading">
    <h3><i class="bi bi-person-plus-fill me-2"></i> User Management <small>Add New User</small></h3>
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
                    <h4 class="card-title">Enter User Details</h4>
                </div>
                <div class="card-body">
                    <form id="addUser" action="<?= base_url() ?>addNewUser" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control required" value="<?= set_value('fname') ?>" name="fname" maxlength="128">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="text" class="form-control required email" value="<?= set_value('email') ?>" name="email" maxlength="128">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control required" id="password" name="password" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control required equalTo" id="cpassword" name="cpassword" maxlength="20">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control required digits" value="<?= set_value('mobile') ?>" name="mobile" maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Code</label>
                                    <select class="form-select" name="phonecode" id="phonecode" required>
                                        <?php foreach ($country as $c): ?>
                                        <option value="<?= $c->phonecode ?>"><?= $c->name . " (+" . $c->phonecode . ")" ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <select class="form-select required" id="role" name="role">
                                        <option value="0">Select Role</option>
                                        <?php if (!empty($roles)) foreach ($roles as $rl): ?>
                                        <option value="<?= $rl->roleId ?>" <?= $rl->roleId == set_value('role') ? 'selected' : '' ?>><?= $rl->role ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
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
<script src="<?= base_url() ?>public/admin/js/addUser.js" type="text/javascript"></script>
