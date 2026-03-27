<div class="page-heading">
    <h3><i class="bi bi-arrow-left-right me-2"></i> Transaction Management</h3>
</div>

<section class="section">

    <!-- ── Filters ──────────────────────────────────────────────────────── -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= base_url('web/transactions') ?>" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Event</label>
                    <select name="web_id" class="form-select form-select-sm">
                        <option value="">All Events</option>
                        <?php foreach ($games as $g): ?>
                        <option value="<?= $g->id ?>" <?= ($filters['webId'] == $g->id) ? 'selected' : '' ?>><?= esc($g->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['dateFrom']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['dateTo']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="PAID"      <?= ($filters['status'] === 'PAID')      ? 'selected' : '' ?>>Paid</option>
                        <option value="RELEASED"  <?= ($filters['status'] === 'RELEASED')  ? 'selected' : '' ?>>Released</option>
                        <option value="CANCELLED" <?= ($filters['status'] === 'CANCELLED') ? 'selected' : '' ?>>Cancelled</option>
                        <option value="0"         <?= ($filters['status'] === '0')         ? 'selected' : '' ?>>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / email / TXN" value="<?= esc($filters['search']) ?>">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i></button>
                    <a href="<?= base_url('web/transactions') ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Table ─────────────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Transactions <span id="txnTotalBadge" class="badge bg-secondary ms-2">...</span></h4>
            <a href="<?= base_url('web/report_download?type=daily&date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-success">
                <i class="bi bi-download me-1"></i> Export Today CSV
            </a>
        </div>
        <div class="card-body p-5">
            <div class="table-responsive">
                <table id="txnTable" class="table table-hover mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>#Order</th>
                            <th>Date</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>Ticket ID</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var $ = window.jQuery;

    var filters = {
        web_id:    <?= json_encode((string)($filters['webId'] ?? '')) ?>,
        date_from: <?= json_encode($filters['dateFrom'] ?? '') ?>,
        date_to:   <?= json_encode($filters['dateTo'] ?? '') ?>,
        status:    <?= json_encode($filters['status'] ?? '') ?>,
        search:    <?= json_encode($filters['search'] ?? '') ?>,
    };

    $('#txnTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: baseURL + 'web/transactions_data',
            type: 'GET',
            data: function (d) {
                if (filters.web_id)    d.web_id    = filters.web_id;
                if (filters.date_from) d.date_from = filters.date_from;
                if (filters.date_to)   d.date_to   = filters.date_to;
                if (filters.status)    d.status    = filters.status;
                if (!d.search.value && filters.search) {
                    d.search = { value: filters.search, regex: false };
                }
                return d;
            }
        },
        columns: [
            { data: 'order_no' },
            { data: 'date', orderable: false },
            { data: 'event', orderable: false },
            { data: 'user', orderable: false },
            { data: 'tickets', orderable: false, searchable: false },
            { data: 'amount', orderable: false, className: 'text-end fw-semibold' },
            { data: 'status', orderable: false, searchable: false, className: 'text-center' },
        ],
        pageLength: 20,
        order: [[0, 'desc']],
        dom: 'lrtip',
        language: { lengthMenu: 'Show _MENU_ entries' },
        drawCallback: function () {
            $('#txnTotalBadge').text(this.api().page.info().recordsTotal);
        }
    });
});
</script>
