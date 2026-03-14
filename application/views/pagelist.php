<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Pages Management
        <small>Edit</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Pages List</h3>
                    <div class="box-tools">
                        
                    </div>
                </div><!-- /.box-header -->


                

                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
                    <tr>
                        <th>Title</th> 
                        <th>Descripiton</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    <?php
                    if(!empty($page))
                    {
                      foreach($page as $record)
                      {
                        ?>
                        <tr>
                            <td><?php echo $record->title ?></td> 
                            <td><?php echo $record->description ?></td> 
                            <td class="text-center">
                                <a class="btn btn-sm btn-info" href="<?php echo base_url().'web/pageedit/'.$record->id; ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                            </td>
                        </tr>
                        <?php
                      }
                    }
                    ?>
                  </table>
                  
                </div>


              </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>


<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
