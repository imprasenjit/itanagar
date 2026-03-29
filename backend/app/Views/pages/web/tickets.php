<div class="page-heading d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h3 class="mb-0"><i class="bi bi-ticket-perforated-fill me-2"></i> Ticket Management</h3>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small" id="blockedLabel">Checking blocked tickets...</span>
        <button id="releaseExpiredBtn" class="btn btn-outline-warning btn-sm" onclick="confirmReleaseExpired()" disabled>
            <i class="bi bi-hourglass-bottom me-1"></i> Release Expired
            <span class="badge bg-dark ms-1" id="expiredCount">—</span>
        </button>
        <button id="releaseHoldsBtn" class="btn btn-warning btn-sm" onclick="confirmReleaseHolds()" disabled>
            <i class="bi bi-unlock-fill me-1"></i> Release All Blocked
            <span class="badge bg-dark ms-1" id="blockedCount">—</span>
        </button>
    </div>
</div>

<!-- ── Verify Ticket Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code-scan me-2"></i>Verify Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Enter Order ID or Transaction ID</label>
                    <input type="text" id="ticketRefInput" class="form-control" placeholder="e.g. 123 or pay_XXXXX">
                </div>
                <div id="verifyResult" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="verifyTicket()"><i class="bi bi-search me-1"></i>Verify</button>
            </div>
        </div>
    </div>
</div>

<section class="section">

    <!-- ── Filters ──────────────────────────────────────────────────────── -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= base_url('web/tickets') ?>" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">Search (name / order ID / transaction ID)</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?= esc($filters['search']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="PAID"      <?= ($filters['status'] === 'PAID')      ? 'selected' : '' ?>>Paid</option>
                        <option value="RELEASED"  <?= ($filters['status'] === 'RELEASED')  ? 'selected' : '' ?>>Released</option>
                        <option value="CANCELLED" <?= ($filters['status'] === 'CANCELLED') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="button" class="btn btn-primary btn-sm applyFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm resetFilter" title="Reset"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#verifyModal">
                        <i class="bi bi-qr-code-scan me-1"></i>Verify Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Alert Messages ───────────────────────────────────────────────── -->
    <?php $flash = session()->getFlashdata('success'); if ($flash): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $flash ?>
    </div>
    <?php endif; ?>

    <!-- ── Table ─────────────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Issued Tickets</h4>
        </div>
        <div class="card-body p-5">
            <div class="table-responsive">
                <table id="ticketsTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#Order</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>Tickets</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</section>

<script>
function cancelTicket(orderId) {
    if (!confirm('Are you sure you want to cancel ticket #' + orderId + '? This cannot be undone.')) return;
    fetch(baseURL + 'web/ticket_cancel', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({order_id: orderId, <?= csrf_token() ?>: '<?= csrf_hash() ?>'})
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === true) {
            alert('Ticket cancelled successfully.');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to cancel ticket.');
        }
    });
}

function resendTicket(orderId) {
    if (!confirm('Resend ticket confirmation email for order #' + orderId + '?')) return;
    fetch(baseURL + 'web/ticket_resend', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({order_id: orderId, <?= csrf_token() ?>: '<?= csrf_hash() ?>'})
    })
    .then(r => r.json())
    .then(data => { alert(data.message || (data.status ? 'Email sent!' : 'Failed.')); });
}

function verifyTicket() {
    const ref = document.getElementById('ticketRefInput').value.trim();
    const result = document.getElementById('verifyResult');
    if (!ref) { result.innerHTML = '<div class="alert alert-warning mb-0">Please enter a ticket reference.</div>'; result.classList.remove('d-none'); return; }

    result.innerHTML = '<div class="text-center text-muted py-2"><i class="bi bi-hourglass-split me-1"></i>Verifying...</div>';
    result.classList.remove('d-none');

    fetch(baseURL + 'web/ticket_verify', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({ticket_ref: ref, <?= csrf_token() ?>: '<?= csrf_hash() ?>'})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.status) {
            result.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-x-circle me-1"></i>' + (data.message || 'Ticket not found.') + '</div>';
            return;
        }
        const o = data.order;
        const u = data.user;
        const icon = data.valid ? '<i class="bi bi-check-circle-fill text-success fs-3"></i>' : '<i class="bi bi-x-circle-fill text-danger fs-3"></i>';
        const validity = data.valid ? '<span class="badge bg-success fs-6">VALID TICKET</span>' : '<span class="badge bg-danger fs-6">NOT VALID</span>';
        result.innerHTML = `
          <div class="text-center mb-2">${icon} ${validity}</div>
          <table class="table table-sm mb-0">
            <tr><th>Order #</th><td>${o.id}</td></tr>
            <tr><th>User</th><td>${u ? u.name + ' (' + u.email + ')' : '—'}</td></tr>
            <tr><th>Amount</th><td>₹${parseFloat(o.total_price).toFixed(2)}</td></tr>
            <tr><th>Status</th><td>${o.paid_status}</td></tr>
            <tr><th>Date</th><td>${o.createdAt}</td></tr>
            <tr><th>Transaction ID</th><td>${o.transaction_id || '—'}</td></tr>
          </table>`;
    })
    .catch(() => { result.innerHTML = '<div class="alert alert-danger mb-0">Network error. Please try again.</div>'; });
}
// ── Blocked-ticket counter & release buttons ─────────────────────────────────
function loadBlockedCount() {
    fetch(baseURL + 'web/blocked_tickets_count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        const count  = data.count ?? 0;
        const btn    = document.getElementById('releaseHoldsBtn');
        const expBtn = document.getElementById('releaseExpiredBtn');
        const badge  = document.getElementById('blockedCount');
        const label  = document.getElementById('blockedLabel');
        badge.textContent = count;
        if (count > 0) {
            btn.disabled    = false;
            expBtn.disabled = false;
            label.textContent = count + ' ticket(s) currently blocked by active cart holds';
            label.className = 'text-warning small fw-semibold';
        } else {
            btn.disabled    = true;
            expBtn.disabled = true;
            document.getElementById('expiredCount').textContent = '0';
            label.textContent = 'No tickets are currently blocked';
            label.className = 'text-muted small';
        }
    })
    .catch(() => {
        document.getElementById('blockedLabel').textContent = 'Could not load blocked count';
    });
}

// ── Release EXPIRED holds only ───────────────────────────────────────────────
function confirmReleaseExpired() {
    if (!confirm(
        'This will delete cart holds that have ALREADY EXPIRED.\n\n' +
        'Active holds (users still within their 15-minute window) will NOT be touched.\n\n' +
        'Continue?'
    )) return;

    const btn = document.getElementById('releaseExpiredBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Releasing...';

    fetch(baseURL + 'web/release_expired_holds', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ <?= csrf_token() ?>: '<?= csrf_hash() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        const alertEl = document.createElement('div');
        alertEl.className = 'alert alert-info alert-dismissible fade show';
        alertEl.innerHTML = '<i class="bi bi-hourglass-bottom me-1"></i>' + data.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.querySelector('.section').prepend(alertEl);
        loadBlockedCount();
        btn.innerHTML = '<i class="bi bi-hourglass-bottom me-1"></i> Release Expired <span class="badge bg-dark ms-1" id="expiredCount">0</span>';
    })
    .catch(() => {
        alert('Network error. Please try again.');
        btn.disabled = false;
    });
}

// ── Force-release ALL holds (including active) ───────────────────────────────
function confirmReleaseHolds() {
    const count = document.getElementById('blockedCount').textContent;
    if (!confirm(
        'This will IMMEDIATELY free ' + count + ' ticket hold(s) from all users\' carts.\n\n' +
        'Those users will lose their reserved tickets.\n\n' +
        'Only proceed if the tickets are genuinely stuck. Continue?'
    )) return;

    const btn = document.getElementById('releaseHoldsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Releasing...';

    fetch(baseURL + 'web/force_release_holds', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ <?= csrf_token() ?>: '<?= csrf_hash() ?>' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === true) {
            const alertEl = document.createElement('div');
            alertEl.className = 'alert alert-success alert-dismissible fade show';
            alertEl.innerHTML = '<i class="bi bi-unlock-fill me-1"></i>' + data.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.querySelector('.section').prepend(alertEl);
            loadBlockedCount();
        } else {
            alert('Failed: ' + (data.message || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-unlock-fill me-1"></i> Release All Blocked <span class="badge bg-dark ms-1" id="blockedCount">' + count + '</span>';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        btn.disabled = false;
    });
}

$(function () {
    loadBlockedCount(); // load count on page open
    var tickTable = $('#ticketsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseURL + 'web/tickets_data',
            type: 'GET',
            data: function (d) {
                d.status = $('[name=status]').val();
                d.search.value = $('[name=search]').val();
            }
        },
        columns: [
            { data: 'order' },
            { data: 'event' },
            { data: 'user',    orderable: false },
            { data: 'tickets', orderable: false },
            { data: 'amount',  className: 'text-end' },
            { data: 'date' },
            { data: 'status',  orderable: false, className: 'text-center' },
            { data: 'actions', orderable: false, className: 'text-center' }
        ]
    });
    $('.applyFilter').on('click', function () { tickTable.ajax.reload(); });
    $('.resetFilter').on('click', function () {
        $('[name=search]').val('');
        $('[name=status]').val('');
        tickTable.ajax.reload();
    });
});
</script>
