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
        <div class="card-body p-5">
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
<script type="text/javascript" src="<?= base_url('admin/js/common.js') ?>" charset="utf-8"></script>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="deleteUserModalLabel"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn"><i class="bi bi-trash3-fill me-1"></i>Delete</button>
            </div>
        </div>
    </div>
</div>

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

    var pendingDeleteRow = null;
    var pendingDeleteId  = null;

    $(document).on('click', '.deleteUser', function (e) {
        e.preventDefault();
        pendingDeleteId  = $(this).data('userid');
        pendingDeleteRow = $(this).closest('tr');
        $('#deleteUserModal').modal('show');
    });

    $('#confirmDeleteUserBtn').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

        $.ajax({
            type: 'POST',
            url: baseURL + 'deleteUser',
            dataType: 'json',
            data: { userId: pendingDeleteId, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
            success: function (res) {
                $('#deleteUserModal').modal('hide');
                btn.prop('disabled', false).html('<i class="bi bi-trash3-fill me-1"></i>Delete');
                if (res.status === true) {
                    $('#usersTable').DataTable().ajax.reload(null, false);
                    showToast('User deleted successfully.', 'success');
                } else {
                    showToast('Failed to delete user.', 'danger');
                }
            },
            error: function () {
                $('#deleteUserModal').modal('hide');
                btn.prop('disabled', false).html('<i class="bi bi-trash3-fill me-1"></i>Delete');
                showToast('Request failed. Please try again.', 'danger');
            }
        });
    });

    function showToast(message, type) {
        var id = 'toast_' + Date.now();
        var html = '<div id="' + id + '" class="toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-3" role="alert" style="z-index:9999">'
            + '<div class="d-flex"><div class="toast-body">' + message + '</div>'
            + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
        $('body').append(html);
        var el = document.getElementById(id);
        new bootstrap.Toast(el, { delay: 3000 }).show();
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    }
});
</script>
