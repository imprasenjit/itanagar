<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Withdrawl History
      </h1>
    </section>


  <div class="row">

  <div class="col-md-8">
  </div>
    <div class="col-md-4">
                <?php
                    $this->load->helper('form');
                    $error = $this->session->flashdata('error');
                    if($error)
                    {
                ?>
                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('error'); ?>                    
                </div>
                <?php } ?>
                <?php  
                    $success = $this->session->flashdata('success');
                    if($success)
                    {
                ?>
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
                <?php } ?>
                </div>
                </div>
    
    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
              <div class="box">


              <div class="box-header">
                    <h3 class="box-title"> Withdrawl History</h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>web/withdrawl" method="POST" id="searchList">
                            <div class="input-group">
                              <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search by User"/>
                              <div class="input-group-btn">
                                <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                              </div>
                            </div>
                        </form>
                    </div>
                </div><!-- /.box-header -->


                <div class="box-body table-responsive no-padding">
                <?php if(!empty($userRecords))
                    { ?>
                  <table class="table table-hover">
                    <tr>
                          <th>Sr. No.</th>
                          <th>User</th>

                          <th>Withdrawl Type</th>
                              <th>Paypal Email/Bank Detail</th>

                          <th>Money</th>
                          

                          <th>Status</th>
                          <th>Date</th>

                          <th>Action</th>
                    </tr>
                    <?php
                    
                        
                      $c = 1;
                        foreach($userRecords as $ms)
                        {
                    ?>
                    <tr>
                      
                    <td><?= $c ?></td>
                    <td><?= $ms->uname?></a></td>
                    <td><?php
                          if($ms->type==1){
                            echo "Bank";
                          }
                          else{
                            echo "Paypal";
                          }
                        ?>
                    </td>
                    <td><?= $ms->paypal_email?></a></td>
                  
                    <td><?= $ms->money?></td>
                    
                          
                          <td><?php 
                                  if( $ms->status=="0"){
                                    $st = "Pending";
                                  }elseif($ms->status=="1"){
                                    $st = "Withdrawled";
                                  }
                                  else{
                                    $st = "Rejected";
                                  }
                                  echo $st;
                                ?></td>

                            <td><?= date("M d, Y h:i a",strtotime($ms->createdAt));?></td>

                            <td>
                            <?php          
                            if( $ms->status=="0"){?>
                              <form onsubmit="return confirm('Do you really want to submit the form?');" action="<?php echo base_url('web/with_req')."/".$ms->user_id; ?>" method="post">

                              <input type="hidden" class="form-control required" required value="<?php echo $ms->id; ?>" id="money" name="id">

                              <input type="hidden" class="form-control required" required value="<?php echo $ms->paypal_email; ?>" id="money" name="p_email">
                              
                                
                              <input type="hidden" class="form-control required" required value="<?php echo $ms->money; ?>" id="money" name="money">

                              <?php
                          if($ms->type==1){
                            ?>
                             <input type="submit" name="type" class="btn btn-primary" value="Send Via Bank" />
                             <?php
                          }
                          else{
                            ?>
                             <input type="submit" name="type" class="btn btn-primary" value="Send Via PayPal" />
                            <?php
                          }
                        ?>
                              
                             

                                <input type="submit" name="type" class="btn btn-warning" value="Reject" />
                              </form>
                            <?php }else{
                              echo $st;
                            } ?>
                            </td>
                        
                    </tr>
                    <?php
                    $c++;
                        }
                    
                    ?>
                  </table>
                  <?php
                  
                    }
                    else{
                        ?>
                        <div class="box-footer clearfix">
                    No Date Availables
                </div>
                        <?php
                    }
                  ?>
                </div><!-- /.box-body -->
                <div class="box-footer clearfix">
                    <?php echo $this->pagination->create_links(); ?>
                </div>
              </div><!-- /.box -->
            </div>
        </div>



</section>
</div>


<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = jQuery(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
            // alert(link+value );
            jQuery("#searchList").attr("action", baseURL + "web/withdrawl/" + value);
            jQuery("#searchList").submit();
        });
    });
</script>