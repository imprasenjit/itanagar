<style>
.numberdegits { margin: 0; padding: 0; display: inline-flex; gap: 4px; }
.numberdegits li { display: inline-flex; align-items: center; justify-content: center; background: #01B623; color: #fff; width: 34px; height: 34px; border-radius: 50%; font-weight: 600; font-size: .8rem; list-style: none; }
.numberdegits li.mega { background: #ffc107; color: #000; }
</style>
<div class="page-heading">
    <h3><i class="bi bi-bag-fill me-2"></i> Order History</h3>
</div>
<section class="section">
    <?php $success_message = session()->getFlashdata('success_message'); if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $success_message ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Orders</h4>
            <div class="card-header-action d-flex gap-2">
                <form action="<?= base_url() ?>web/order" method="POST" id="searchList" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="searchText" value="<?= $searchText ?>" class="form-control" placeholder="Search by user...">
                        <button class="btn btn-primary searchList" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <a class="btn btn-success ms-2" href="<?= base_url('order/release_order') ?>">
                    <i class="bi bi-send-fill me-1"></i> Release Orders
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (count($orders) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>User</th>
                            <th>Tickets</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order):
                            $userDetails = (new \App\Models\UserModel())->getUserInfoById($order->user_id);
                            $tickets = json_decode($order->tickets);
                        ?>
                        <tr>
                            <td><strong>#<?= $order->id ?></strong></td>
                            <td>
                                <div><?= esc($userDetails->name) ?></div>
                                <small class="text-muted"><?= esc($userDetails->email ?? '') ?></small>
                            </td>
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
                            <td><small><?= esc($order->razorpay_order_id) ?></small></td>
                            <td><?= date("M d, Y h:i a", strtotime($order->createdAt)) ?></td>
                            <td class="text-center">
                                <?php if ($order->order_status == '1'): ?>
                                    <span class="badge bg-success">Confirmed</span>
                                <?php elseif ($order->paid_status == '0'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-muted py-4">No orders found.</p>
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
        var link = jQuery(this).attr('href');
        var pageMatch = link.match(/[?&]page=(\d+)/);
        var value = pageMatch ? pageMatch[1] : '1';
        jQuery("#searchList").attr("action", baseURL + "web/order/" + value);
        jQuery("#searchList").submit();
    });
    jQuery(document).on("click", ".confirmOrder", function () {
        var orderid = $(this).data("orderid"), hitURL = baseURL + "/web/confirm_order_by_admin", currentRow = $(this);
        if (confirm("Are you sure to confirm this order?")) {
            jQuery.ajax({ type: "POST", dataType: "json", url: hitURL, data: { orderid: orderid } })
                .done(function (data) {
                    if (data.status) {
                        currentRow.replaceWith('<span class="badge bg-success">Confirmed</span>');
                        alert("Order confirmed successfully.");
                    } else { alert("Confirmation failed."); }
                });
        }
    });
});
</script>
