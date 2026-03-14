


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Common Setting
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Range</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    
                   
                   
                   
                   
                    <form role="form" action="<?php echo base_url() ?>web/editCommon" method="post" id="editUser" role="form" enctype="multipart/form-data">


                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">   
                                    


                                    <div class="form-group">
                                        <label for="fname">Wallet Minimum($)</label>
                                        <input class="form-control" type="number" required name="wallet_min" min="0" value="<?php echo $WebInfo->wallet_min; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Wallet Maximum($)</label>
                                        <input class="form-control" type="number" required name="wallet_max" min="0" value="<?php echo $WebInfo->wallet_max; ?>">
                                    </div>


                                    <div class="form-group">
                                        <label for="fname">Refund Minimum($)</label>
                                        <input class="form-control" type="number" required name="refund_min"  min="0" value="<?php echo $WebInfo->refund_min; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Refund Maximum($)</label>
                                        <input class="form-control" type="number" required name="refund_max" min="0" value="<?php echo $WebInfo->refund_max; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Transfer Minimum($)</label>
                                        <input class="form-control" type="number" required name="transfer_min" min="0" value="<?php echo $WebInfo->transfer_min; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Transfer Maximum($)</label>
                                        <input class="form-control" type="number" required name="transfer_max" min="0" value="<?php echo $WebInfo->transfer_max; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Withdrawl Minimum($)</label>
                                        <input class="form-control" type="number" required name="withdrawl_min" min="0" value="<?php echo $WebInfo->withdrawl_min; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">Withdrawl Maximum($)</label>
                                        <input class="form-control" type="number" required name="withdrawl_max" min="0" value="<?php echo $WebInfo->withdrawl_max; ?>">
                                    </div>





                                    
                                </div>
                            </div>
                            
                        </div><!-- /.box-body -->
    
                        <div class="box-footer">
                            <input type="submit" class="btn btn-primary" value="Submit" />
                            <input type="reset" class="btn btn-default" value="Reset" />
                        </div>
                    </form>
                </div>
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
                
                <div class="row">
                    <div class="col-md-12">
                        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                </div>
            </div>
        </div>    
    </section>
</div>

