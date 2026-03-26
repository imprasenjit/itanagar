<div class="page-heading">
    <h3><i class="bi bi-speedometer2 me-2"></i> Dashboard
        <small class="text-muted fs-6 fw-normal ms-2">Control panel</small>
    </h3>
</div>

<section class="section">

    <!-- ── Stat Cards ─────────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="bi bi-ticket-perforated-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total Events</h6>
                            <h6 class="font-extrabold mb-0"><?= $totalweb ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total Users</h6>
                            <h6 class="font-extrabold mb-0"><?= $totaluser ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2">
                                <i class="bi bi-bag-check-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Tickets Sold</h6>
                            <h6 class="font-extrabold mb-0"><?= number_format($totalTicketsSold) ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total Revenue</h6>
                            <h6 class="font-extrabold mb-0">₹<?= number_format($totalRevenue, 2) ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon yellow mb-2">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Today's Sales</h6>
                            <h6 class="font-extrabold mb-0">₹<?= number_format($todayRevenue, 2) ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Today's Orders</h6>
                            <h6 class="font-extrabold mb-0"><?= $todayOrders ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Recent Transactions + Upcoming Events ──────────────────────── -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h4>
                    <a href="<?= base_url('web/transactions') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="dashTxnTable" class="table table-hover mb-0" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Event</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#dashTxnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseURL + 'web/dashboard_txn_data',
            type: 'GET'
        },
        columns: [
            { data: 'id',     orderable: false },
            { data: 'user',   orderable: false },
            { data: 'event',  orderable: false },
            { data: 'amount', orderable: false },
            { data: 'date',   orderable: false },
        ],
        pageLength: 8,
        lengthChange: false,
        dom: '<"d-flex justify-content-between align-items-center px-3 pt-2"<""f><""p>>t<"d-flex justify-content-between align-items-center px-3 pb-2"<""i>>',
        language: {
            search: '',
            searchPlaceholder: 'Search transactions...',
            emptyTable: 'No transactions yet.',
            processing: '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</div>',
        }
    });
});
</script>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Events</h4>
                    <a href="<?= base_url('web') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($upcomingEvents)): foreach ($upcomingEvents as $ev): ?>
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <div>
                            <div class="fw-semibold"><?= esc($ev->name) ?></div>
                            <small class="text-muted">
                                <?= $ev->result_date ? date('d M Y', strtotime($ev->result_date)) : '—' ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">₹<?= number_format((float)($ev->jackpot ?? 0), 0) ?></span>
                            <div><small class="text-muted">Jackpot</small></div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p class="text-center text-muted py-4 mb-0">No upcoming events.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</section>
