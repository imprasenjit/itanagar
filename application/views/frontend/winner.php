<?php
$userId = $userInfo->userId;
$name = $userInfo->name;
$email = $userInfo->email;
$mobile = $userInfo->mobile;
$roleId = $userInfo->roleId;
$role = $userInfo->role;
?>
<div class="myaccount">
  <div class="container">
    <h4 class="mb-4">My Accounts</h4>
    <div class="row">
      <div class="col-md-3">
        <div class="account_menu">
          <?php $this->load->view('frontend/profile_menu'); ?>
        </div>
      </div>
      <div class="col-md-9">
        <!-- Tab panes -->
        <div class="tab-content myaccount_content">

          <div class="tab-pane active" id="profile" role="tabpanel" aria-labelledby="profile-tab">

            <h4 class="mb-3" style="color:green">Winner List</h4>

            <div class="profilebox_sec">
              <div class="row">
                <div class="col-md-6">




                  <?php
                  $this->load->helper('form');
                  ?>

                  <div class="row">
                    <div class="col-md-12">
                      <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                  </div>

                  <?php


                  $noMatch = $this->session->flashdata('nomatch');
                  if ($noMatch) {
                  ?>
                    <div class="alert alert-warning alert-dismissable">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <?php echo $this->session->flashdata('nomatch'); ?>
                    </div>
                  <?php }
                  $success = $this->session->flashdata('success');
                  if ($success) {
                  ?>
                    <div class="alert alert-success alert-dismissable">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <?php echo $success; ?>
                    </div>
                  <?php } ?>



                </div>
              </div>

            </div>

            <h5 class="mb-3" style="color:black">Winner List</span></h5>


            <?php if (count($money_history) > 0) {
            ?>


              <table id="example" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th>Order No.</th>
                    <th>Date</th>
                    <th>Game</th>
                    <th width="20%">Ball Combination</th>
                    <th>Prize</th>
                    <th>Confirm Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $c = 1;
                  foreach ($money_history as $ms) {
                  ?>
                    <tr>

                      <td><?= $ms->id ?></td>
                      <td><?= date("M d, Y", strtotime($ms->date)); ?></td>
                      <td><?= $ms->name ?></td>

                      <td>
                        <ul class="numberdegits">
                          <li><?php echo $ms->white1; ?></li>
                          <li><?php echo $ms->white2; ?></li>
                          <li><?php echo $ms->white3; ?></li>
                          <li><?php echo $ms->white4; ?></li>
                          <li><?php echo $ms->white5; ?></li>
                          <?php
                          if ($ms->white6 != "") {
                          ?>
                            <li><?php echo $ms->white6; ?></li>
                          <?php
                          }
                          ?>
                          <li style="background:yellow;color:black"><?php echo $ms->yellow1; ?></li>

                          <?php
                          if ($ms->yellow2 != "") {
                          ?>
                            <li style="background:yellow;color:black"><?php echo $ms->yellow2; ?></li>
                          <?php
                          }
                          ?>
                        </ul>
                      </td>

                      <td><?php echo $ms->prize; ?></td>



                      <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)); ?></td>
                    </tr>
                  <?php
                    $c++;
                  }
                  ?>
                </tbody>
              </table>


            <?php
            } else {
              echo "No Order Yet! ";
            }
            ?>





          </div>

        </div>
      </div>
    </div>
  </div>
</div>



<!-- Include the PayPal JavaScript SDK -->
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">


<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function() {
    $('#example').DataTable();
  });
</script>