<div class="page-heading">
    <h3><i class="bi bi-ticket-perforated-fill me-2"></i> Ticket Management</h3>
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
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <a href="<?= base_url('web/tickets') ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-x-lg"></i></a>
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
            <h4 class="card-title mb-0">Issued Tickets <span class="badge bg-secondary ms-2"><?= $total ?></span></h4>
        </div>
        <div class="card-body p-0">
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
                    <tbody>
                    <?php if (!empty($tickets)): foreach ($tickets as $ticket):
                        $tData = json_decode($ticket->tickets ?? '[]', true);
                        $ticketNos = array_column($tData, 'ticket_no');
                        $webName = $ticket->web_name ?? (isset($tData[0]['web_name']) ? $tData[0]['web_name'] : '—');
                        $statusMap = [
                            'PAID'      => ['success', 'Paid'],
                            'RELEASED'  => ['info',    'Released'],
                            'CANCELLED' => ['danger',  'Cancelled'],
                            '0'         => ['warning', 'Unpaid'],
                            '2'         => ['danger',  'Failed'],
                        ];
                        [$badgeColor, $statusLabel] = $statusMap[$ticket->paid_status] ?? ['secondary', $ticket->paid_status];
                        $canCancel = in_array($ticket->paid_status, ['PAID', '1']);
                        $canResend = in_array($ticket->paid_status, ['PAID', '1']);
                    ?>
                    <tr>
                        <td><strong>#<?= $ticket->id ?></strong></td>
                        <td><?= esc($webName) ?></td>
                        <td>
                            <div><?= esc($ticket->user_name ?? '—') ?></div>
                            <small class="text-muted"><?= esc($ticket->user_email ?? '') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($ticketNos)): ?>
                            <small class="text-muted"><?= implode(', ', array_slice($ticketNos, 0, 3)) ?><?= count($ticketNos) > 3 ? '<br><span class="text-muted">+' . (count($ticketNos)-3) . ' more</span>' : '' ?></small>
                            <?php else: ?>
                            <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold">₹<?= number_format($ticket->total_price, 2) ?></td>
                        <td><small><?= date('d M Y', strtotime($ticket->createdAt)) ?></small></td>
                        <td class="text-center"><span class="badge bg-<?= $badgeColor ?>"><?= $statusLabel ?></span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <?php if ($canResend): ?>
                                <button class="btn btn-xs btn-outline-primary" title="Resend Ticket" onclick="resendTicket(<?= $ticket->id ?>)">
                                    <i class="bi bi-envelope-arrow-up-fill"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($canCancel): ?>
                                <button class="btn btn-xs btn-outline-danger" title="Cancel Ticket" onclick="cancelTicket(<?= $ticket->id ?>)">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">No tickets found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($pager)): ?>
        <div class="card-footer">
            <?= $pager->links('default', 'default_full') ?>
        </div>
        <?php endif; ?>
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
$(function () { $('#ticketsTable').DataTable({ paging: false, searching: false, info: false, columnDefs: [{ orderable: false, targets: -1 }] }); });
</script>
