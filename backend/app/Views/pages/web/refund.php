<div class="page-heading">
    <h3><i class="bi bi-arrow-counterclockwise me-2"></i> Refund History</h3>
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
            <h4 class="card-title">Refund Requests</h4>
            <div class="card-header-action">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (true): ?>
                <table id="refundTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Money</th>
                            <th>Reason</th>
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
    $('#refundTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/refund_data', type: 'GET' },
        columns: [
            { data: 'sr' },
            { data: 'user' },
            { data: 'money' },
            { data: 'reason', orderable: false },
            { data: 'status', orderable: false },
            { data: 'date' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
});
</script>
