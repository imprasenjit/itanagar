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
            <h4 class="card-title mb-0">Transactions <span class="badge bg-secondary ms-2"><?= $total ?></span></h4>
            <a href="<?= base_url('web/report_download?type=daily&date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-success">
                <i class="bi bi-download me-1"></i> Export Today CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                    <tbody>
                    <?php if (!empty($transactions)): foreach ($transactions as $txn):
                        $tickets = json_decode($txn->tickets ?? '[]', true);
                        $ticketNos = array_column($tickets, 'ticket_no');
                        $webName = $txn->web_name ?? (isset($tickets[0]['web_name']) ? $tickets[0]['web_name'] : '—');
                        $statusMap = [
                            'PAID'      => ['success', 'Paid'],
                            'RELEASED'  => ['info',    'Released'],
                            'CANCELLED' => ['danger',  'Cancelled'],
                            '0'         => ['warning', 'Unpaid'],
                            '1'         => ['success', 'Paid'],
                            '2'         => ['danger',  'Failed'],
                        ];
                        [$badgeColor, $statusLabel] = $statusMap[$txn->paid_status] ?? ['secondary', $txn->paid_status];
                    ?>
                    <tr>
                        <td><strong>#<?= $txn->id ?></strong></td>
                        <td><small><?= date('d M Y, H:i', strtotime($txn->createdAt)) ?></small></td>
                        <td><?= esc($webName) ?></td>
                        <td>
                            <div><?= esc($txn->user_name ?? '—') ?></div>
                            <small class="text-muted"><?= esc($txn->user_email ?? '') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($ticketNos)): ?>
                            <small class="text-muted"><?= implode(', ', array_slice($ticketNos, 0, 3)) ?><?= count($ticketNos) > 3 ? ' +' . (count($ticketNos)-3) . ' more' : '' ?></small>
                            <?php else: ?>
                            <small class="text-muted"><?= esc($txn->transaction_id ?? '—') ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold">₹<?= number_format($txn->total_price, 2) ?></td>
                        <td class="text-center"><span class="badge bg-<?= $badgeColor ?>"><?= $statusLabel ?></span></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No transactions found.</td></tr>
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
