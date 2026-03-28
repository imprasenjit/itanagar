<div class="page-heading">
    <h3><i class="bi bi-bank me-2"></i> Withdrawal History</h3>
</div>

<section class="section">
    <?php $error = session()->getFlashdata('error'); if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $error ?>
    </div>
    <?php endif; ?>
    <?php $success = session()->getFlashdata('success'); if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $success ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Withdrawal Requests</h4>
            <div class="card-header-action">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (true): ?>
                <table id="withdrawlTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>PayPal Email / Bank Detail</th>
                            <th>Money</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url() ?>public/admin/js/common.js"></script>
<script>
$(function () {
    $('#withdrawlTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/withdrawl_data', type: 'GET' },
        columns: [
            { data: 'sr' },
            { data: 'user' },
            { data: 'type', orderable: false },
            { data: 'paypalEmail', orderable: false },
            { data: 'money' },
            { data: 'status', orderable: false },
            { data: 'date' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});
</script>
