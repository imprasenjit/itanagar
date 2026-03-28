<div class="page-heading">
    <h3><i class="bi bi-people-fill me-2"></i> User Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Users List</h4>
            <div class="card-header-action">
                <a class="btn btn-primary" href="<?php echo base_url(); ?>addNew">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Code</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Created On</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript" src="<?= base_url() ?>public/admin/js/common.js" charset="utf-8"></script>
<script>
$(function () {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'users_data', type: 'GET' },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'phonecode' },
            { data: 'mobile' },
            { data: 'role' },
            { data: 'createdDtm' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});
</script>
