<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <i class="fa fa-users"></i> Lottery Games Management
      <small>Add, Edit, Delete</small>
    </h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-xs-12 text-right">
        <div class="form-group">
          <a class="btn btn-primary" href="<?php echo base_url(); ?>web/addNew"><i class="fa fa-plus"></i> Add New</a>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Lottery Games List</h3>
            <div class="box-tools">
              <form action="<?php echo base_url() ?>web" method="POST" id="searchList">
                <div class="input-group">
                  <!-- <select name="status" class="form-control select-sm pull-right">
          <option value="">Filter By Status</option>
          <option value="Active">Active</option>
          <option value="Deactive">Deactive</option>
        </select> -->

                  <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search" />
                  <div class="input-group-btn">
                    <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div><!-- /.box-header -->




          <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
              <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Created On</th>
                <th class="text-center">Actions</th>
              </tr>
              <?php
              if (!empty($web)) {
                foreach ($web as $record) {
              ?>
                  <tr>
                    <td><?php echo $record->name ?></td>
                    <td><?php echo $record->status ?></td>
                    <td><?php echo date("d-m-Y", strtotime($record->createdDtm)) ?></td>
                    <td class="text-center">
                      <a class="btn btn-sm btn-info" href="<?php echo base_url() . 'web/edit/' . $record->id; ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                      <a class="btn btn-sm btn-info" href="<?php echo base_url() . 'web/rangeEdit/' . $record->id; ?>" title="Edit"><i class="fa fa-pencil"></i>Details</a>
                      <a class="btn btn-sm btn-info" href="<?php echo base_url() . 'web/descriptionEdit/' . $record->id; ?>" title="Edit"><i class="fa fa-pencil"></i>Description</a>
                      <a class="btn btn-sm btn-danger deleteWeb" href="#" data-userid="<?php echo $record->id; ?>" title="Delete"><i class="fa fa-trash"></i></a>
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