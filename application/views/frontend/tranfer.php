<?php
$userId = $userInfo->userId;
$name = $userInfo->name;
$email = $userInfo->email;
$mobile = $userInfo->mobile;

$paypal = $userInfo->paypal;
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
              <a href="<?php echo base_url() ?>account/wallet"  class="nav-link "><i class="fas fa-wallet"></i>Wallet</a>
            </li>

            <li>
              <a href="<?php echo base_url() ?>account/refund"  class="nav-link "><i class="fas fa-wallet"></i>Refund History</a>
            </li>
            
            <li>
              <a href="<?php echo base_url() ?>account/withdrawal"  class="nav-link"><i class="fas fa-wallet"></i>Withdrawal History</a>
            </li>

            <li>
                  <a href="<?php echo base_url() ?>account/transfer"  class="nav-link active"><i class="fas fa-wallet"></i>Transfer History</a>
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


                  
                  
                  <div class="row" style="margin-top:20px;">
                    <div class="col-md-12">
                      <h4>Transfer money From Wallet</h4>
                      <form id="form_rk"  action="<?php echo base_url('account/t_request'); ?>" method="POST" id="form">


                  <div class="row">


                  <label class="col-md-2"><b>Transfer Money</b></label>
                  
                    <div class="col-md-10">
                      <input type="number" value="" min="<?php  echo $common->transfer_min ;?>" max="<?php echo  $common->transfer_max ;?>" id="addpay" name="add_pay" required  placeholder="Enter Number e.g. 10" class="form-control">
                      </div>


                    <label class="col-md-2"><b>User Email</b></label>
                  
                    <div class="col-md-10">
                      <input type="email" value="" min="1" id="pay_email" name="pay_email" required  placeholder="example@example.com" class="form-control">
                      </div>
                      
                    <br>
                    <br>
                    
                    <div class="col-md-12">
                      <input type="submit" class="btn btn-success" value="Transfer">
                    </div>
                    </div>



                      </form>


                      
                    </div>
                    
                    

                    <div class="col-md-12" style="margin-top:30px;">


                      <?php if(count($money_history)>0){
                        ?>
                        
                        <h4 class="mb-3"  style="color:green">Transfer History</h4>

                        <table id="example" class="display" style="width:100%">
                          <thead>
                            <tr>
                              <th>Sr. No.</th>
                              <th>Money</th>
                              <th>Transfer Email</th>
                              
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

                                <td><?= $ms->paypal_email?></td>

                                
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
    } );
  </script>
  


