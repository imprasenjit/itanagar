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
                <?php if (count($orders) > 0): ?>
                <table class="table table-striped">
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
                    <tbody>
                        <?php foreach ($orders as $order):
                            $tickets = json_decode($order->tickets);
                        ?>
                        <tr>
                            <td><strong>#<?= $order->id ?></strong></td>
                            <td>
                                <table class="table table-bordered table-sm mb-0">
                                    <thead><tr><th>Game</th><th>Ticket No.</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($tickets as $value):
                                            $web_details = (new \App\Models\WebModel())->getWebInfo($value->web_id); ?>
                                        <tr>
                                            <td><?= esc($web_details->name) ?></td>
                                            <td><code><?= esc($value->ticket_no) ?></code></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                            <td><strong>₹<?= $order->total_price ?></strong></td>
                            <td>UPI</td>
                            <td><small><?= esc($order->transaction_id) ?></small></td>
                            <td><?= date("M d, Y h:i a", strtotime($order->createdAt)) ?></td>
                            <td class="text-center">
                                <?php if ($order->order_status == 0): ?>
                                    <a class="btn btn-sm btn-warning confirmOrder" href="#!" data-orderid="<?= $order->id ?>">
                                        <i class="bi bi-check-circle-fill me-1"></i> Confirm
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-success">Confirmed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-muted py-4">No orders yet for this user.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    </div>
</section>
<script src="<?= base_url() ?>public/admin/js/common.js"></script>
<script>
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        window.location.href = jQuery(this).attr('href');
    });
    jQuery(document).on("click", ".confirmOrder", function () {
        var orderid = $(this).data("orderid"), hitURL = baseURL + "/web/confirm_order_by_admin", btn = $(this);
        if (confirm("Confirm this order?")) {
            jQuery.ajax({ type: "POST", dataType: "json", url: hitURL, data: { orderid: orderid } })
                .done(function (data) {
                    if (data.status) { btn.replaceWith('<span class="badge bg-success">Confirmed</span>'); }
                    else { alert("Confirmation failed."); }
                });
        }
    });
});
</script>
