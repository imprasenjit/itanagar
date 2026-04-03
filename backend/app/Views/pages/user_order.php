<div class="page-heading">
    <h3><i class="bi bi-bag-fill me-2"></i>Orders
        <small class="text-muted fs-6 fw-normal ms-2"><?= esc($userInfo->name) ?></small>
    </h3>
</div>

<section class="section">

    <div class="card mb-3">
        <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
            <span><i class="bi bi-person-fill me-1 text-muted"></i><strong><?= esc($userInfo->name) ?></strong></span>
            <span><i class="bi bi-envelope-fill me-1 text-muted"></i><?= esc($userInfo->email) ?></span>
            <span><i class="bi bi-phone-fill me-1 text-muted"></i><?= esc($userInfo->mobile) ?></span>
            <a href="<?= base_url('userListing') ?>" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Order History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="userOrderTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Tickets</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</section>

<script>
$(function () {
    $('#userOrderTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('web/user_order_data/' . $userId) ?>',
            type: 'GET'
        },
        columns: [
            { data: 'order_no',      orderable: false },
            { data: 'tickets',       orderable: false },
            { data: 'price',         orderable: false },
            { data: 'transactionId', orderable: false },
            { data: 'date',          orderable: false },
            { data: 'actions',       orderable: false }
        ],
        pageLength: 20,
        language: { emptyTable: 'No orders found for this user.' }
    });
});
</script>
