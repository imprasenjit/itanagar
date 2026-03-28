<style>
.numberdegits { margin: 0; padding: 0; display: inline-flex; gap: 5px; list-style: none; }
.numberdegits li { display: inline-flex; align-items: center; justify-content: center; background: #01B623; color: #fff; width: 38px; height: 38px; border-radius: 50%; font-weight: 600; font-size: .8rem; }
.numberdegits li.mega { background: #ffc107; color: #000; }
</style>

<div class="page-heading">
    <h3><i class="bi bi-trophy-fill me-2"></i> Winner History</h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                Winner History
                <span class="badge bg-success ms-2">Total Prize: &#8377;<?= $amount->sum ?></span>
            </h4>
            <div class="card-header-action">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (true): ?>
                <table id="winnerTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Tickets</th>
                            <th>Price</th>
                            <th>Payment Type</th>
                            <th>Winning Prize</th>
                            <th>Transaction ID</th>
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
    $('#winnerTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: baseURL + 'web/winner_data', type: 'GET' },
        columns: [
            { data: 'order_no' },
            { data: 'user' },
            { data: 'event' },
            { data: 'tickets', orderable: false },
            { data: 'price' },
            { data: 'paymentType', orderable: false },
            { data: 'winningPrize' },
            { data: 'transactionId', orderable: false },
            { data: 'date' }
        ]
    });
});
</script>
