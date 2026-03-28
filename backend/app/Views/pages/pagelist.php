<div class="page-heading">
    <h3><i class="bi bi-file-earmark-text-fill me-2"></i> Pages Management <small>Edit</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Pages List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="pagelistTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>$(function () {
    $('#pagelistTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/page_data', type: 'GET' },
        columns: [
            { data: 'title' },
            { data: 'description' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});</script>
