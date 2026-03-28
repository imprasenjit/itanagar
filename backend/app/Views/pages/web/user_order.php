<style>
.numberdegits { margin: 0; padding: 0; display: inline-flex; gap: 4px; }
.numberdegits li { display: inline-flex; align-items: center; justify-content: center; background: #01B623; color: #fff; width: 34px; height: 34px; border-radius: 50%; font-weight: 600; font-size: .8rem; list-style: none; }
.numberdegits li.mega { background: #ffc107; color: #000; }
</style>
<div class="page-heading">
    <h3><i class="bi bi-bag-fill me-2"></i> <?= esc($userinfo->name) ?> — Order History</h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Orders for <?= esc($userinfo->name) ?></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (true): ?>
                <table id="userOrderTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Tickets</th>
                            <th>Price</th>
                            <th>Payment Type</th>
                            <th>Transaction ID</th>
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
    $('#userOrderTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/user_order_data/<?= $userinfo->userId ?>', type: 'GET' },
        columns: [
            { data: 'order_no' },
            { data: 'tickets',       orderable: false },
            { data: 'price' },
            { data: 'paymentType',   orderable: false },
            { data: 'transactionId', orderable: false },
            { data: 'date' },
            { data: 'actions',       orderable: false, className: 'text-center' }
        ]
    });
});
jQuery(document).on('click', '.confirmOrder', function () {
    var orderid = $(this).data('orderid'), hitURL = baseURL + 'web/confirm_order_by_admin', btn = $(this);
    if (confirm('Confirm this order?')) {
        jQuery.ajax({ type: 'POST', dataType: 'json', url: hitURL, data: { orderid: orderid } })
            .done(function (data) {
                if (data.status) { btn.replaceWith('<span class="badge bg-success">Confirmed</span>'); }
                else { alert('Confirmation failed.'); }
            });
    }
});
</script>
