<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <i class="fa fa-users"></i> Announcement Management
      <small>Add, Edit, Delete</small>
    </h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-xs-12 text-right">
        <div class="form-group">
          <a class="btn btn-primary" href="<?php echo base_url(); ?>web/addfaq"><i class="fa fa-plus"></i> Add New</a>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Announcements List</h3>
            <div class="box-tools">
              <form action="<?php echo base_url() ?>web/faq" method="POST" id="searchList">
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
                <th style="width:40%">Title</th>
                <th style="width:40%">Announcement</th>
                <th style="width:20%">Created</th>

                <th class="text-center">Actions</th>
              </tr>
              <?php
              if (!empty($web)) {
                foreach ($web as $record) {
              ?>
                  <tr>
                    <td><?php echo $record->question ?></td>
                    <td><?php echo $record->answer ?></td>
                    <td><?php echo date("d-m-Y", strtotime($record->createdat)) ?></td>
                    <td class="text-center">
                      <a class="btn btn-sm btn-info" href="<?php echo base_url() . 'web/faqedit/' . $record->id; ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                      <a class="btn btn-sm btn-danger deletefaq" href="#" data-userid="<?php echo $record->id; ?>" title="Delete"><i class="fa fa-trash"></i></a>
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



<script>
  jQuery(document).on("click", ".deletefaq", function() {
    var userId = $(this).data("userid"),
      hitURL = baseURL + "web/deletefaq",
      currentRow = $(this);


    var confirmation = confirm("Are you sure to delete this FAQ ?");

    if (confirmation) {
      jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: hitURL,
        data: {
          userId: userId
        }
      }).done(function(data) {
        console.log(data);
        currentRow.parents('tr').remove();
        if (data.status = true) {
          alert("FAQ successfully deleted");
        } else if (data.status = false) {
          alert("FAQ deletion failed");
        } else {
          alert("Access denied..!");
        }
      });
    }
  });
</script>