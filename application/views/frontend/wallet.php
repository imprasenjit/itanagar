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
              <ul class="nav flex-column">
                <li>
                  <a href="<?php echo base_url() ?>account" class="nav-link " ><i class="fas fa-user"></i> Profile</a>
                </li>
                <li>
                  <a href="<?php echo base_url() ?>account/wallet"  class="nav-link active"><i class="fas fa-wallet"></i>Wallet</a>
                </li>

                <li>
                  <a href="<?php echo base_url() ?>account/refund"  class="nav-link"><i class="fas fa-wallet"></i>Refund History</a>
                </li>
                
                <li>
                  <a href="<?php echo base_url() ?>account/withdrawal"  class="nav-link"><i class="fas fa-wallet"></i>Withdrawal History</a>
                </li>
                

                <li>
                  <a href="<?php echo base_url() ?>account/transfer"  class="nav-link"><i class="fas fa-wallet"></i>Transfer History</a>
                </li>

                <li>
            <a href="<?php echo base_url() ?>account/winner_history"  class="nav-link"><i class="fas fa-history"></i>Winner History</a>
            </li>
            
                <li>
                  <a href="<?php echo base_url() ?>account/order_history"  class="nav-link"><i class="fas fa-history"></i>Play History</a>
                </li>
                <li class="nav-item">
                  <a href="<?php echo base_url() ?>account/changepassword"  class="nav-link "><i class="fas fa-cog"></i> Change Password</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-md-9">
            <!-- Tab panes -->
            <div class="tab-content myaccount_content">
              
              <div class="tab-pane active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <h4 class="mb-3"  style="color:green">Wallet Money - $<span><?php echo ($money) ?  $money->money: "0"; ?></span></h4>
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
                        if($noMatch)
                        {
                    ?>
                    <div class="alert alert-warning alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo $this->session->flashdata('nomatch'); ?>
                    </div>
                    <?php } 
                        $success = $this->session->flashdata('success');
                        if($success)
                        {
                            ?>
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?php echo $success; ?>                    
                            </div>
                        <?php } ?>


                      
                        
                              <div class="row mt-3">
                                  <div class="col-md-12">
                                  <h3>Add Money In Wallet</h3>

                                <input type="number" value="" min="<?php echo $common->wallet_min; ?>" max="<?php echo $common->wallet_max; ?>" id="addpay" name="add_pay"  placeholder="Enter Number e.g. 10" class="form-control">
                                <br>
                                <div id="paypal-button-container"></div>
                                  </div>
                                </div>


                                <br>

                                <form>
                                  <button type="button" class="btn btn-primary" style="cursor:pointer;" value="Pay Now with Rave" id="submit">Pay with credit card</button>
                                </form>
                                <br>

                                <form id="form_rk" style="display: hidden" action="<?php echo base_url('account/wupdate'); ?>" method="POST" id="form">
                                  <input type="hidden" id="var1" name="money" value=""/>
                                  <input type="hidden" id="var2" name="transaction_id" value=""/>


                                  <input type="hidden" id="var3" name="paymet_type" value=""/>
                                </form>


    
                      </div>
                    </div>
                      
                  </div>



                  <?php if(count($money_history)>0){
                    ?>
                    
                  <h4 class="mb-3"  style="color:green">Wallet History</h4>

                  <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Money</th>

                            <th>Payment From</th>
                            <th>Payment Info</th>

                            <th>Transaction ID</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php
                      $c = 1;
                        foreach($money_history as $ms){
                          ?>
                          <tr>
                            
                          <td><?= $c ?></td>
                          <td><?= $ms->money?></td>

                          <td><?= $ms->p_type?></td>
                          
                          <td><?= $ms->type?></td>

                          <td><?= $ms->trancaction_id ?></td>
                            <td><?= date("M d, Y h:i a",strtotime($ms->createdAt));?></td>
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

    
<!-- Include the PayPal JavaScript SDK -->
<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>

<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

<script>
var min = parseInt('<?php echo $common->wallet_min; ?>');

var max = parseInt('<?php echo $common->wallet_max; ?>');

    // Render the PayPal button into #paypal-button-container
    paypal.Buttons({
      
        // Set up the transaction
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: $("#addpay").val()
                  }
                }]
            });
        },

        // Finalize the transaction
        onApprove: function(data, actions) {

            return actions.order.capture().then(function(details) {
              console.log(details);
                // Show a success message to the buyer

                $("#var2").val(details.id);
                
                $("#var1").val($("#addpay").val());

                $("#var3").val("Paypal");
                $("#form_rk").submit();
               

            });
            
        },

        onCancel: function(data) {
            // Show a cancel page, or return to cart
            alert("Transaction Cancelled");
        },

        onError: function(err) {
            // Show an error page here, when an error occurs
            alert("Something went wrong. Please check payment price.");
        }


    }).render('#paypal-button-container');

    $(document).ready(function() {
      $('#example').DataTable();
  } );
</script>
    




    <script type="text/javascript" src="http://flw-pms-dev.eu-west-1.elasticbeanstalk.com/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
    document.getElementById("submit").addEventListener("click", function(e) {
    var PBFKey = "FLWPUBK_TEST-caee04bef41ffedfb8951e967f1ffaed-X";
    val =$("#addpay").val();
    
    if(val< min || val>max){
      alert("Add Money should be between "+min+" and "+max);
      return false;
    }


    getpaidSetup({
      PBFPubKey: PBFKey,
      customer_email: "<?php echo $_SESSION['email']; ?>",
      amount: val,
      // customer_phone: "<?php // echo $_SESSION['mobile']?>",
      currency: "USD",
      txref: "rave-122333",
      onclose: function() {},
      callback: function(response) {
        var flw_ref = response.tx.flwRef; // collect flwRef returned and pass to a 					server page to complete status check.
        console.log("This is the response returned after a charge", response);
        if (
          response.tx.chargeResponseCode == "00" ||
          response.tx.chargeResponseCode == "0"
        ) {
          $("#var2").val(flw_ref);
                
          $("#var1").val(response.tx.charged_amount);

          $("#var3").val("Rave");
          $("#form_rk").submit();
          

          console.log(response);
          // redirect to a success page
        } else {
          // redirect to a failure page.
        }
      }
    });
  });
});



</script>
 
