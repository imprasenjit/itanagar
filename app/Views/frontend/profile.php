<?php
$userId = $userInfo->userId;
$name = $userInfo->name;
$email = $userInfo->email;
$mobile = $userInfo->mobile;
// $bank = $userInfo->bank;
// $paypal = $userInfo->paypal;
$roleId = $userInfo->roleId;
$role = $userInfo->role;
?>
<div class="myaccount">
  <div class="container">
    <h4 class="mb-4">My Accounts</h4>
    <div class="row">
      <div class="col-md-3">
        <div class="account_menu">
          <?php echo view('frontend/profile_menu'); ?>
        </div>
      </div>
      <div class="col-md-9">
        <!-- Tab panes -->
        <div class="tab-content myaccount_content">
          <div class="tab-pane active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <h4 class="mb-3">Profile</h4>
            <div class="profilebox_sec">
              <div class="row">
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-12">
                      <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                  </div>
                  <?php
                  $error = session()->getFlashdata('error');
                  if ($error) {
                  ?>
                    <div class="alert alert-danger alert-dismissable">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <?php echo $error; ?>
                    </div>
                  <?php }
                  $success = session()->getFlashdata('success');
                  if ($success) {
                  ?>
                    <div class="alert alert-success alert-dismissable">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <?php echo $success; ?>
                    </div>
                  <?php } ?>
                  <form action="<?php echo base_url() ?>account/pUpdate" method="post">
                    <div class="row">
                      <div class="col-12 mb-2">
                        <label for="fname"><b>Full Name</b></label>
                      </div>
                      <div class="col-md-12">
                        <input type="text" class="form-control" id="fname" name="fname" placeholder="<?php echo $name; ?>" value="<?php echo set_value('fname', $name); ?>" maxlength="128" />
                        <input type="hidden" value="<?php echo $userId; ?>" name="userId" id="userId" />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12 mb-1 mt-3">
                        <label for="email"><b>Email</b></label>
                      </div>
                      <div class="col-md-12">
                        <input type="text" class="form-control" id="email" name="email" placeholder="<?php echo $email; ?>" value="<?php echo set_value('email', $email); ?>">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 mb-1 mt-3">
                        <label for="mobile"><b>Mobile Number</b></label>
                      </div>
                      <div class="col-md-12">
                        <input type="text" class="form-control" id="mobile" name="mobile" placeholder="<?php echo $mobile; ?>" value="<?php echo set_value('mobile', $mobile); ?>" maxlength="10">
                      </div>
                    </div>
                    <div class="row mt-3">
                      <div class="col-md-12">
                        <input type="submit" class="btn btn-success btn-block" value="Update">
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>