<div class="page-heading">
    <h3><i class="bi bi-person-hearts me-2"></i> Customers <small>Ticket buyers</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Customers List</h4>
        </div>
        <div class="card-body p-5">
            <div class="table-responsive">
                <table id="customersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Code</th>
                            <th>Mobile</th>
                            <th>Registered On</th>
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
$(function () {
    $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'customers_data', type: 'GET' },
        columns: [
            { data: 'name' },
            { data: 'email' },
            { data: 'phonecode' },
            { data: 'mobile' },
            { data: 'createdDtm' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});
</script>
