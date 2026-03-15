<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style>
  .numberdegits {
    margin: 0;
    padding: 0;
    display: inline-flex;
  }

  .numberdegits li {
    display: inline-block;
    margin-right: 5px;
    background: #01B623;
    color: #fff;
    width: 40px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    font-weight: 500;
  }
</style>
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <i class="fa fa-users"></i> Order History
    </h1>
  </section>
  
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
      <?php $success_message = session()->getFlashdata('success_message');
                if ($success_message) {
                ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo session()->getFlashdata('success_message'); ?>
                    </div>
                <?php } ?>
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">All Order</h3>
            <div class="box-tools">
              <form action="<?php echo base_url() ?>web/order" method="POST" id="searchList">
                <div class="input-group">
                  <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search by User" />
                  <div class="input-group-btn">
                    <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-sm btn-success " href="<?= base_url("order/release_order") ?>">Release Orders</a>
                  </div>
                </div>
              </form>
            </div>
          </div><!-- /.box-header -->
          <div class="box-body table-responsive no-padding">

            <?php if (count($orders) > 0) {
            ?>
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Order No.</th>
                    <th>User</th>
                    <th>Tickets</th>
                    <th>Amount</th>
                    <th>Payment Type</th>
                    <th>Order Id</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $c = 1;
                  foreach ($orders as $order) {
                    $userDetails = (new \App\Models\UserModel())->getUserInfoById($order->user_id);
                    $tickets = json_decode($order->tickets);
                  ?>
                    <tr>
                      <td><?= $order->id ?></td>
                      <td><?= $userDetails->name ?>(<?php if (isset($userDetails->email)) {
                                                      echo $userDetails->email;
                                                    } ?>|<?php if (isset($userDetails->mobile)) {
                                                            echo $userDetails->mobile;
                                                          } ?>)</td>
                      <td>
                        <table class="table table-bordered">
                          <thead>
                            <tr>
                              <th>Name </th>
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
                      <td><?= $order->razorpay_order_id; ?></td>
                      <td><?= date("M d, Y h:i a", strtotime($order->createdAt)); ?></td>
                      <td class="text-center">
                        <?php
                        if ($order->order_status == '1') { ?>
                          <a class="btn btn-sm btn-success" href="#!">Order Confirmed</a>
                        <?php } else if ($order->paid_status == '0') { ?>
                          <a class="btn btn-sm btn-warning" href="#!">Order Pending</a>
                        <?php } else { ?>
                          <a class="btn btn-sm btn-danger" href="#!">Order Failed</a>
                        <?php } ?>
                      </td>
                    </tr>
                  <?php
                    $c++;
                  }
                  ?>
                </tbody>
              </table>
            <?php
            } else {
            ?>
              <div class="box-footer clearfix">
                No Date Availables
              </div>
            <?php
            }
            ?>
          </div><!-- /.box-body -->
          <div class="box-footer clearfix">
            <?php echo $pager->links(); ?>
          </div>
        </div><!-- /.box -->
      </div>
    </div>
  </section>
</div>
<script type="text/javascript" src="<?php echo base_url(); ?>public/admin/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery('ul.pagination li a').click(function(e) {
      e.preventDefault();
      var link = jQuery(this).get(0).href;
      var value = link.substring(link.lastIndexOf('/') + 1);
      // alert(link+value );
      jQuery("#searchList").attr("action", baseURL + "web/order/" + value);
      jQuery("#searchList").submit();
    });
    jQuery(document).on("click", ".confirmOrder", function() {
      var orderid = $(this).data("orderid"),
        hitURL = baseURL + "/web/confirm_order_by_admin",
        currentRow = $(this);
      var confirmation = confirm("Are you sure to confirm the order ?");
      if (confirmation) {
        jQuery
          .ajax({
            type: "POST",
            dataType: "json",
            url: hitURL,
            data: {
              orderid: orderid
            },
          })
          .done(function(data) {
            console.log(data);
            currentRow.empty().text("Order Confirmed!").removeClass('btn-info').addClass("btn-success");
            if ((data.status = true)) {
              alert("Order successfully Confirmed");
              currentRow.siblings(".releaseOrder").remove();
              currentRow.parent().append('<a class="btn btn-sm btn-success" href="#!">Order Confirmed</a>')
              currentRow.remove();
            } else if ((data.status = false)) {
              alert("Order Confirmation failed");
            } else {
              alert("Access denied..!");
            }
          });
      }
    });
    jQuery(document).on("click", ".releaseOrder", function() {
      var orderid = $(this).data("orderid"),
        hitURL = baseURL + "/web/release_order_by_admin",
        currentRow = $(this);
      var confirmation = confirm("Are you sure to Release the Tickets ?");
      if (confirmation) {
        jQuery
          .ajax({
            type: "POST",
            dataType: "json",
            url: hitURL,
            data: {
              orderid: orderid
            },
          })
          .done(function(data) {
            console.log(data);
            if ((data.status = true)) {
              alert("Tickets successfully Removed");
              currentRow.siblings(".confirmOrder").remove();
              currentRow.parent().append('<a class="btn btn-sm btn-warning" href="#!">Order Relased</a>')
              currentRow.remove();
            } else if ((data.status = false)) {
              alert("Tickets Not Removed");
            } else {
              alert("Access denied..!");
            }
          });
      }
    });
  });
</script>