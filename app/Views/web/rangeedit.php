<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> <?php echo $WebInfo->name ?> Management
            <small>Edit Range</small>
        </h1>
    </section>
    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Details </h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" action="<?php echo base_url() ?>web/editRange" method="post" id="editUser" role="form" enctype="multipart/form-data">
                        <input type="hidden" value="<?php echo $rangeInfo->id; ?>" name="id" id="id" />
                        <input type="hidden" value="<?php echo $rangeInfo->web_id; ?>" name="web_id" id="id" />
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="fname">Heading </label>
                                        <input class="form-control" type="text" required name="heading" value="<?php echo $rangeInfo->heading; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">Price (INR)</label>
                                        <input class="form-control" type="number" required name="price" value="<?php echo $rangeInfo->price; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">Ticket Range</label>
                                        <input class="form-control" type="text" required name="rangeStart" value="<?php echo $rangeInfo->rangeStart; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">Ticket Priority</label>
                                        <input class="form-control" type="number" required name="priority" value="<?php echo $rangeInfo->priority; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">Prize Money(INR)</label>
                                        <textarea required class="form-control" name="jackpot" id="jackpot"><?php echo $rangeInfo->jackpot; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">Result Date</label>
                                        <input class="form-control" type="datetime-local" required name="result_date" value="<?php echo $rangeInfo->result_date; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">TICKET IMAGE 1 (Max size 2MB , dimentions 1500pxX1500px)</label>
                                        <input class="form-control" type="file" name="logo">
                                    </div>
                                    <div class="form-group">
                                        <?php
                                        if ($rangeInfo->logo != '') {
                                            $logo = base_url('imglogo') . "/" . $rangeInfo->logo;
                                            echo '<img height="250" src=' . $logo . '>';
                                        }
                                        ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="fname">TICKET IMAGE 2 (Max size 2MB , dimentions 1500pxX1500px)</label>
                                        <input class="form-control" type="file" name="logo2">
                                    </div>
                                    <div class="form-group">
                                        <?php
                                        if ($rangeInfo->logo2 != '') {
                                            $logo2 = base_url('imglogo') . "/" . $rangeInfo->logo2;
                                            echo '<img height="250" src=' . $logo2 . '>';
                                        }
                                        ?>
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
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $('#jackpot').summernote();
    });
</script>