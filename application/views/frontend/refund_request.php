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
            <h4 class="mb-3" style="color:green">Refund Request</span></h4>
            <div class="profilebox_sec">
              <div class="row">
                <div class="col-md-12">
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




                  <div class="row" style="margin-top:20px;">
                    <div class="col-md-12">
                      <h4>Refund Detail</h4>
                      <form id="form_rk" action="<?php echo base_url('account/r_request'); ?>" method="POST" id="form">


                        <div class="row">


                          <label class="col-md-3"><b>Request Money</b></label>

                          <div class="col-md-9">
                            <input type="number" value="" min="<?php echo $common->refund_min; ?>" max="<?php echo  $common->refund_max; ?>" id="addpay" name="add_pay" required placeholder="Enter Number e.g. 10" class="form-control">
                          </div>

                          <label class="col-md-3"><b>Reason Refund</b></label>

                          <div class="col-md-9">
                            <textarea value="" required class="form-control" name="reason"></textarea>

                          </div>


                          <div class="col-md-12">
                            <input type="submit" class="btn btn-success" value="Send Request">
                          </div>
                        </div>



                      </form>



                    </div>



                    <div class="col-md-12" style="margin-top:30px;">


                      <?php if (count($money_history) > 0) {
                      ?>

                        <h4 class="mb-3" style="color:green">Refund History</h4>

                        <table id="example" class="display" style="width:100%">
                          <thead>
                            <tr>
                              <th>Sr. No.</th>
                              <th>Money</th>
                              <th>Reason</th>
                              <th>Status</th>
                              <th>Date</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $c = 1;
                            foreach ($money_history as $ms) {
                            ?>
                              <tr>

                                <td><?= $c ?></td>
                                <td><?= $ms->money ?></td>

                                <td><?= $ms->reason ?></td>

                                <td><?php
                                    if ($ms->status == "0") {
                                      echo "Pending";
                                    } elseif ($ms->status == "1") {
                                      echo "Refunded";
                                    } else {
                                      echo "Rejected";
                                    }
                                    ?></td>
                                <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)); ?></td>
                              </tr>
                            <?php
                              $c++;
                            }
                            ?>
                          </tbody>
                        </table>


                      <?php
                      }
                      ?>





                    </div>

                  </div>


                </div>
              </div>
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