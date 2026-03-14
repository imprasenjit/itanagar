


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> <?php echo $WebInfo->name ?> Prize Tier
        
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Pattern</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    

                    <div class="box-body table-responsive no-padding">
                                  <table class="table table-hover">
                    <tbody>
                    <tr>
                        <th>White Ball</th>
                        <th>Mega Ball</th>
                        <th>Percentage</th>
                        <th class="text-center">Action</th>
                    </tr>



                    <tr>
                        <form role="form" action="<?php echo base_url() ?>web/addtier" method="post" id="editUser" role="form">
                        <td>


                    <input type="hidden" value="<?php echo $WebInfo->id; ?>" name="web_id" id="id" /> 


                            <select required name="white" class="form-control">
                                <option value="">Select Number</option>
                                <?php
                                    for($i=0;$i<7;$i++){
                                        echo "<option value='".$i."'>".$i."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select required name="yellow" class="form-control">
                                <option value="">Select Number</option>
                                <?php
                                    for($i=0;$i<3;$i++){
                                        echo "<option value='".$i."'>".$i."</option>";
                                    }
                                ?>
                            </select>
                        </td>

                        <td>
                            <input required type="text" name="per" class="form-control" placeholder="e.g. 10">
                        </td>
                        
                        <td class="text-center">
                            <input type="submit" name="type" class="btn btn-primary" value="Add">
                        </td>

                        </form>
                    </tr>
                    
                    <?php
                        if(count($tier)>0){
                            foreach($tier as $t){
                                ?>


                    <tr>
                        <form role="form" action="<?php echo base_url() ?>web/addtier" method="post" id="editUser" role="form">
                        <td>


                    <input type="hidden" value="<?php echo $WebInfo->id; ?>" name="web_id"/> 


                    <input type="hidden" value="<?php echo $t->id; ?>" name="id"/> 


                            <select required name="white" class="form-control">
                                <option value="">Select Number</option>
                                <?php
                                    for($i=0;$i<7;$i++){

                                        if($t->white==$i){

                                            echo "<option selected value='".$i."'>".$i."</option>";
                                        }
                                        else{
                                            echo "<option value='".$i."'>".$i."</option>";

                                        }
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select required name="yellow" class="form-control">
                                <option value="">Select Number</option>
                                <?php
                                    for($i=0;$i<3;$i++){
                                        
                                        if($t->mega==$i){

                                            echo "<option selected value='".$i."'>".$i."</option>";
                                        }
                                        else{
                                            echo "<option value='".$i."'>".$i."</option>";

                                        }
                                    }
                                ?>
                            </select>
                        </td>

                        <td>
                            <input required type="text" name="per" class="form-control" value="<?php echo $t->per; ?>" placeholder="e.g. 10">
                        </td>
                        
                        <td class="text-center">
                            <input type="submit" name="type" class="btn btn-primary" value="Update">
                        </td>

                        </form>
                    </tr>


                                <?php
                            }
                        }
                    ?>


                    </tbody>
                                      
                    </table>
                </div>
    
    
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

