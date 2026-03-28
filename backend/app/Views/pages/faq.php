<div class="page-heading">
    <h3><i class="bi bi-megaphone-fill me-2"></i> Announcement Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Announcements List</h4>
            <div class="card-header-action">
                <a class="btn btn-primary" href="<?php echo base_url(); ?>web/addfaq">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="card-body p-5">
            <div class="table-responsive">
                <table id="faqTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:35%">Title</th>
                            <th style="width:45%">Announcement</th>
                            <th style="width:10%">Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
$(function () {
    $('#faqTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/faq_data', type: 'GET' },
        columns: [
            { data: 'title' },
            { data: 'content' },
            { data: 'created' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});
</script>
