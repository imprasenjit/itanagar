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
          <?php echo view('frontend/profile_menu'); ?>
        </div>
      </div>
      <div class="col-md-9">
        <!-- Tab panes -->
        <div class="tab-content myaccount_content">
          <div class="tab-pane active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <h4 class="mb-3" style="color:green">My Orders</span></h4>
            <small>Order status PENDING meaning that the order is in process .</small>
            <div class="profilebox_sec">
              <div class="row">
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-12">
                      <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                  </div>
                  <?php
                  $noMatch = session()->getFlashdata('nomatch');
                  if ($noMatch) {
                  ?>
                    <div class="alert alert-warning alert-dismissable">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      <?php echo session()->getFlashdata('nomatch'); ?>
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
                </div>
              </div>
            </div>
            <?php if (count($orders) > 0) {
            ?>
              <table id="example" class="display" style="width:100%">
                <thead>
                  <tr>
                    <th>Order No.</th>
                    <th>Tickets</th>
                    <th>Price</th>
                    <th>Payment Type</th>
                    <th>Trancaction Id</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $c = 1;
                  foreach ($orders as $order) {
                    $tickets = json_decode($order->tickets);

                  ?>
                    <tr>
                      <td><?= $order->id ?></td>
                      <td>
                        <table class="table table-bordered">
                          <thead>
                            <tr>
                              <th>Game </th>
                              <th>Ticket No</th>
                            </tr>
                          </thead>
                          <tbody>

                            <?php
                            foreach ($tickets as $key => $value) {
                              $web_details = (new \App\Models\WebModel())->getWebInfo($value->web_id);
                              echo '<tr>';
                              echo '<td>' . $web_details->name . '</td>';
                              echo '<td>' . $value->ticket_no . '</td>';
                              echo '</tr>';
                            }
                            ?>
                          </tbody>
                        </table>
                      </td>
                      <td><?= $order->total_price ?></td>
                      <td>UPI</td>
                      <td><?= $order->transaction_id; ?></td>
                      <td><?= date("M d, Y h:i a", strtotime($order->createdAt)); ?></td>
                      <td><?php if ($order->order_status == 1) {
                            echo 'Confirmed';
                          } else if ($order->order_status == 2) {
                            echo 'Payment Failed';
                          } else {
                            echo 'pending';
                          } ?></td>
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