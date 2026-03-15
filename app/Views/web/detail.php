<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> <?php echo $WebInfo->name; ?>
        </h1>
    </section>

    <section class="content">

        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
                <!-- general form elements -->



                <div class="box box-primary">
                    <!-- form start -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <h3><b>Range Details</b></h2>
                                </div><!-- /.box-header -->
                                <div class="form-group">

                                    <h4><span style="color:green;">Heading - </span> <?php echo $RangeInfo->heading; ?></h4>
                                    <br>


                                    <h4 style="color:green;">White Ball Range</h4>
                                    <br>

                                    <span><strong>Maximum Selection - </strong> <?php echo $RangeInfo->white_ball; ?></span>
                                    <br>
                                    <span><strong>From - </strong> <?php echo $RangeInfo->white_from; ?> &nbsp; &nbsp; <strong>To - </strong> <?php echo $RangeInfo->white_to; ?></span>
                                </div>

                                <div class="form-group">
                                    <h4 style="color:green;">Yellow Ball Range</h4>

                                    <br>
                                    <span><strong>Maximum Selection - </strong> <?php echo $RangeInfo->yellow_ball; ?></span>
                                    <br>
                                    <span><strong>From - </strong> <?php echo $RangeInfo->yellow_from; ?> &nbsp; &nbsp; <strong>To - </strong> <?php echo $RangeInfo->yellow_to; ?></span>
                                </div>


                                <div class="form-group">
                                    <h4 style="color:green;">Logo</h4>

                                    <br>
                                    <img style="height:100px;" src="<?php echo base_url('public/imglogo') . "/" . $RangeInfo->logo; ?>">
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    <h3><b>Lottery Price</b> - $<?php echo $RangeInfo->price; ?></h3>
                                </div><!-- /.box-header -->


                                <div class="form-group">
                                    <h3><b>Jackpot Price</b> - <?php echo $RangeInfo->jackpot; ?></h3>
                                </div><!-- /.box-header -->


                            </div>
                        </div>
                    </div>


                    <div class="box-footer">
                        <a href="<?php echo base_url('web/rangeEdit') . "/" . $WebInfo->id; ?>" class="btn btn-primary">Edit</a>

                        <a href="<?php echo base_url('web/addtwoWebdate') . "/" . $WebInfo->id; ?>" class="btn btn-primary">Add Next Ten Date</a>

                        <a href="<?php echo base_url('web/descriptionEdit') . "/" . $WebInfo->id; ?>" class="btn btn-primary">Edit Play Description</a>

                        <a href="<?php echo base_url('web/tier') . "/" . $WebInfo->id; ?>" class="btn btn-primary">Tier</a>

                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <?php
                $error = session()->getFlashdata('error');
                if ($error) {
                ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo session()->getFlashdata('error'); ?>
                    </div>
                <?php } ?>
                <?php
                $success = session()->getFlashdata('success');
                if ($success) {
                ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?php echo session()->getFlashdata('success'); ?>
                    </div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-12">
                        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">All Result Dates</h3>
                        <div class="box-tools">
                            <form action="<?php echo base_url('web/addNewWebdate/' . $WebInfo->id); ?>" method="POST" id="searchList">
                                <div class="input-group">
                                    <input type="text" name="date" value="" class="form-control input-sm pull-right datepicker" autocomplete="off" required style="width: 150px;" placeholder="Add Date" />
                                    <div class="input-group-btn">
                                        <button class="btn btn-sm btn-success searchList">Add Date</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <?php if (!empty($userRecords)) { ?>
                            <table class="table table-hover">
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Date</th>
                                    <th>Created On</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                <?php

                                $sr = 1;
                                foreach ($userRecords as $record) {
                                ?>
                                    <tr>
                                        <td><?php echo $sr;
                                            $sr++; ?></td>
                                        <td><?php echo date("M d, Y", strtotime($record->date)); ?></td>
                                        <td><?php echo date("M d, Y", strtotime($record->createdAt)) ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-danger deleteWebDate" href="#" data-userid="<?php echo $record->id; ?>" title="Delete"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php
                                }

                                ?>
                            </table>
                        <?php
                        } else {
                        ?>
                            <div class="box-footer clearfix">
                                No Date Availables
                            </div>
                        <?php
                        }
                        ?>
                    </div><!-- /.box-body -->
                    <div class="box-footer clearfix">
                        <?php echo $pager->links(); ?>
                    </div>
                </div><!-- /.box -->
            </div>
        </div>



    </section>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>public/admin/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('ul.pagination li a').click(function(e) {
            e.preventDefault();
            var link = jQuery(this).get(0).href;
            window.location.href = link;
        });
    });

    jQuery(document).on("click", ".deleteWebDate", function(e) {
        e.preventDefault();
        var userId = $(this).data("userid"),
            hitURL = baseURL + "web/deleteWebDate",
            currentRow = $(this);
        var confirmation = confirm("Are you sure to delete this Date?");

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
                    alert("Date successfully deleted");
                } else if (data.status = false) {
                    alert("Date deletion failed");
                } else {
                    alert("Access denied..!");
                }
            });
        }
    });

    $(".datepicker").datepicker({
        minDate: 0
    });
</script>