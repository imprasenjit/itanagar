<div class="page-heading">
    <h3><i class="bi bi-wallet2 me-2"></i> Wallet History</h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Wallet Transactions</h4>
            <div class="card-header-action">
            </div>
        </div>
        <div class="card-body p-5">
            <div class="table-responsive">
                <?php if (true): ?>
                <table id="walletTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Money</th>
                            <th>Payment Info</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="<?= base_url() ?>public/admin/js/common.js"></script>
<script>
$(function () {
    $('#walletTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/wallet_data', type: 'GET' },
        columns: [
            { data: 'sr' },
            { data: 'user' },
            { data: 'money' },
            { data: 'paymentInfo', orderable: false },
            { data: 'transactionId', orderable: false },
            { data: 'date' }
        ]
    });
});
</script>
