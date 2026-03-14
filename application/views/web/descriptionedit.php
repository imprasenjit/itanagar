


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> <?php echo $WebInfo->name ?> Management
        <small>Edit Description</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Description</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    
                    <form role="form" action="<?php echo base_url() ?>web/editdesc" method="post" id="editUser" role="form">

                    <input type="hidden" value="<?php echo $rangeInfo->id; ?>" name="id" id="id" /> 

                    <input type="hidden" value="<?php echo $rangeInfo->web_id; ?>" name="web_id" id="id" /> 
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">   
                                    
                                    <div class="form-group">
                                        <label for="fname">How To Play</label>
                                        <textarea required class="form-control" name="play_description" style="height:300px;"><?php echo $rangeInfo->play_description; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">When Play</label>
                                        <textarea required class="form-control" name="when_play" style="height:300px;"><?php echo $rangeInfo->when_play; ?></textarea>
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

