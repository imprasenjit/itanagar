<div class="page-heading">
    <h3><i class="bi bi-dice-5-fill me-2"></i> event Games Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">event Games List</h4>
            <a class="btn btn-primary" href="<?= base_url('web/addNew') ?>">
                <i class="bi bi-plus"></i> Add New
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="weblistTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#weblistTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseURL + 'web/weblist_data',
            type: 'GET'
        },
        columns: [
            { data: 'name' },
            { data: 'status', orderable: false },
            { data: 'createdDtm' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ],
        order: [[2, 'desc']],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search games...',
            emptyTable: 'No games found.',
            processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</div>',
        }
    });

    // Delete handler — re-attach after every DataTables draw
    $('#weblistTable').on('click', '.deleteWeb', function (e) {
        e.preventDefault();
        var id = $(this).data('userid');
        if (!confirm('Are you sure you want to delete this game?')) return;
        $.post(baseURL + 'web/deleteWeb', { id: id, [csrfTokenName]: Cookies.get(csrfCookieName) }, function (res) {
            if (res.status === 'ok' || res.status === true) {
                $('#weblistTable').DataTable().ajax.reload(null, false);
            } else {
                alert(res.message || 'Delete failed.');
            }
        }, 'json');
    });
});
</script>

