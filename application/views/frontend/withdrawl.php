<?php
$userId = $userInfo->userId;
$name = $userInfo->name;
$email = $userInfo->email;
$mobile = $userInfo->mobile;

$bank = $userInfo->bank;

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
              <a href="<?php echo base_url() ?>account/withdrawal"  class="nav-link active"><i class="fas fa-wallet"></i>Withdrawal History</a>
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
                      <h4>Withdrawl From Wallet</h4>
                      <br>
                      <form id="form_rk"  action="<?php echo base_url('account/w_request'); ?>" method="POST" id="form">


                  <div class="row">


                  <label class="col-md-3"><b>Request Money</b></label>
                  
                    <div class="col-md-7">
                      <input type="number" value="" min="<?php  echo $common->withdrawl_min ;?>" max="<?php echo  $common->withdrawl_max ;?>" id="addpay" name="add_pay" required  placeholder="Enter Number e.g. 10" class="form-control">
                      </div>
                    </div>
                    <br>

                    <div class="row">
                <label class="col-md-3"><b>Choose Type</b></label>
                  
                  <div class="col-md-7">
                    <select name="type" class="c_type form-control" >
                      <option selected value="0">Paypal</option>
                      <option  value="1">Bank</option>
                    </select>
                </div>
                </div>
                <br>

                <div class="row paypal">
                    <label class="col-md-3"><b>Paypal Email</b></label>
                  
                    <div class="col-md-7">
                      <input type="email" value="<?php echo $paypal ?>" min="1" id="paypal_x" name="pay_email" required  placeholder="e.g. example@example.com" class="form-control">
                      

                      <span style="color:red; font-size:13px;">
                       Note : This paypal email used for your profile and also  next withdrawls
                      </span>
                      </div>
                  </div>


                  <div class="row bank" style="display:none;">
                    <label class="col-md-3"><b>Bank Details</b></label>
                  
                    <div class="col-md-7">
                    

                      <textarea id="bank_x" name="bank_detail" class="form-control" placeholder="Complete Bank Details e.g. Bank Name, Account number, Branch Code, Bank code etc."><?php echo $bank ?></textarea>

                      <span style="color:red; font-size:13px;">
                       Note : This Bank Details used for your profile and also  next withdrawls
                      </span>
                      </div>
                  </div>

                    <div class="row">
                    <div class="col-md-12">
                      <input type="submit" class="btn btn-success" value="Withdrawl Request">
                    </div>
                    </div>



                      </form>


                      
                    </div>
                    
                    

                    <div class="col-md-12" style="margin-top:30px;">


                      <?php if(count($money_history)>0){
                        ?>
                        
                        <h4 class="mb-3"  style="color:green">Withdrawl History</h4>

                        <table id="example" class="display" style="width:100%">
                          <thead>
                            <tr>
                              <th>Sr. No.</th>
                              <th>Money</th>
                              <th>Withdrawl Type</th>
                              <th>Paypal Email/Bank Detail</th>
                              
                              <th>Status</th>
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

                                <td><?php
                                  if($ms->type==1){
                                    echo "Bank";
                                  }
                                  else{
                                    echo "Paypal";
                                  }
                                ?></td>

                                <td><?= $ms->paypal_email?></td>

                                
                                <td><?php 
                                  if( $ms->status=="0"){
                                    echo "Pending";
                                  }elseif($ms->status=="1"){
                                    echo "Refunded";
                                  }
                                  else{
                                    echo "Rejected";
                                  }
                                ?></td>
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
    });


    $(".c_type").change(function(){
      $thisval = $(this).val();
      if($thisval=="0"){
        $(".paypal").show();
        $(".bank").hide();
        $("#paypal_x").show();
        $("#bank_x").show();
      }
      else{
        $(".bank").show();
        $(".paypal").hide();
      }
    })
  </script>
  


