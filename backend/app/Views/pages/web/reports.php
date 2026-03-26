<div class="page-heading">
    <h3><i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Reports</h3>
</div>

<section class="section">
    <div class="row g-3">

        <!-- ── Daily Sales Report ─────────────────────────────────────────── -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="bi bi-calendar-day me-2 text-primary"></i>Daily Sales Report</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download all paid orders for a specific date as a CSV file.</p>
                    <form method="GET" action="<?= base_url('web/report_download') ?>">
                        <input type="hidden" name="type" value="daily">
                        <div class="mb-3">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-download me-1"></i> Download CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Event Revenue Report ───────────────────────────────────────── -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="bi bi-ticket-perforated me-2 text-success"></i>Event Revenue Report</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download all paid orders for a specific event event as a CSV file.</p>
                    <form method="GET" action="<?= base_url('web/report_download') ?>">
                        <input type="hidden" name="type" value="event">
                        <div class="mb-3">
                            <label class="form-label">Select Event</label>
                            <select name="web_id" class="form-select" required>
                                <option value="">— Choose an event —</option>
                                <?php foreach ($games as $g): ?>
                                <option value="<?= $g->id ?>"><?= esc($g->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download me-1"></i> Download CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── Monthly Revenue Report ─────────────────────────────────────── -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="bi bi-calendar-month me-2 text-warning"></i>Monthly Revenue Report</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download all paid orders for a selected month and year.</p>
                    <form method="GET" action="<?= base_url('web/report_download') ?>">
                        <input type="hidden" name="type" value="monthly">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Month</label>
                                <select name="month" class="form-select">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= ($m == date('n')) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($y == date('Y')) ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-download me-1"></i> Download CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>
