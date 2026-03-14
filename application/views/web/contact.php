<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Contact List
      </h1>
    </section>
    
    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-body table-responsive no-padding">
                <?php if(!empty($userRecords))
                    { ?>
                  <table class="table table-hover">
                    <tr>
                        <th>Sr. No.</th>
                        <th>Name</th>
                        <th>Email</th><th>Message</th>
                        <th>Created On</th>
                    </tr>
                    <?php
                    
                        $sr =1;
                        foreach($userRecords as $record)
                        {
                    ?>
                    <tr>
                        <td><?php echo $sr; $sr++; ?></td>
                        <td><?php echo $record->name; ?></td>
                        <td><?php echo $record->email; ?></td>
                        <td><?php echo $record->message; ?></td>
                        <td><?php echo date("M d, Y", strtotime($record->createdAt)) ?></td>
                        
                    </tr>
                    <?php
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
            window.location.href=link; 
        });
    });

    jQuery(document).on("click", ".deleteWebDate", function(e){
		e.preventDefault();
		var userId = $(this).data("userid"),
			hitURL = baseURL + "web/deleteWebDate",
			currentRow = $(this);
		var confirmation = confirm("Are you sure to delete this Date?");
		
		if(confirmation)
		{
			jQuery.ajax({
			type : "POST",
			dataType : "json",
			url : hitURL,
			data : { userId : userId } 
			}).done(function(data){
				console.log(data);
				currentRow.parents('tr').remove();
				if(data.status = true) { alert("Date successfully deleted"); }
				else if(data.status = false) { alert("Date deletion failed"); }
				else { alert("Access denied..!"); }
			});
		}
	});
    
$( ".datepicker" ).datepicker({minDate:0});
</script>