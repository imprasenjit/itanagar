<div class="page-heading">
    <h3><i class="bi bi-envelope-fill me-2"></i> Contact List</h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Contact Submissions</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="contactTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
$(function () {
    $('#contactTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/contact_data', type: 'GET' },
        columns: [
            { data: 'sr' },
            { data: 'name' },
            { data: 'email' },
            { data: 'message', orderable: false },
            { data: 'date' }
        ]
    });
});
</script>
