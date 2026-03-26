<div class="page-heading">
    <h3><i class="bi bi-megaphone-fill me-2"></i> Announcement Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Announcements List</h4>
            <div class="card-header-action d-flex gap-2">
                <form action="<?php echo base_url() ?>web/faq" method="POST" id="searchList" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-primary searchList" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <a class="btn btn-primary ms-2" href="<?php echo base_url(); ?>web/addfaq">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:35%">Title</th>
                            <th style="width:45%">Announcement</th>
                            <th style="width:10%">Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($web)) foreach ($web as $record): ?>
                        <tr>
                            <td><?= $record->question ?></td>
                            <td><?= $record->answer ?></td>
                            <td><?= date("d-m-Y", strtotime($record->createdat)) ?></td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-primary" href="<?= base_url('web/faqedit/' . $record->id) ?>" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <a class="btn btn-sm btn-danger deletefaq" href="#" data-userid="<?= $record->id ?>" title="Delete"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
jQuery(document).on("click", ".deletefaq", function () {
    var userId = $(this).data("userid"),
        hitURL = baseURL + "web/deletefaq",
        currentRow = $(this);
    if (confirm("Are you sure you want to delete this announcement?")) {
        jQuery.ajax({
            type: "POST", dataType: "json", url: hitURL,
            data: { userId: userId }
        }).done(function (data) {
            currentRow.parents('tr').remove();
            if (data.status == true) { alert("Announcement deleted successfully"); }
            else { alert("Deletion failed"); }
        });
    }
});
</script>
