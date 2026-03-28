<div class="page-heading">
    <h3><i class="bi bi-database-gear me-2"></i> Database Migrations
        <small class="text-muted fs-6 fw-normal ms-2">Schema Version Tracker</small>
    </h3>
</div>

<section class="section">

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Summary cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-primary"><?= $totalCount ?></div>
                <div class="text-muted small">Total Migrations</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-success"><?= $ranCount ?></div>
                <div class="text-muted small">Migrations Run</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold <?= $pendingCount > 0 ? 'text-warning' : 'text-secondary' ?>"><?= $pendingCount ?></div>
                <div class="text-muted small">Pending</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Migration Files</h4>
            <?php if ($pendingCount > 0): ?>
            <form method="post" action="<?= base_url('web/runMigrations') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('Run all <?= $pendingCount ?> pending migration(s)?')">
                    <i class="bi bi-play-fill me-1"></i> Run All Pending
                </button>
            </form>
            <?php else: ?>
            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>All up to date</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Migration File</th>
                            <th style="width:100px">Batch</th>
                            <th style="width:180px">Run At</th>
                            <th style="width:100px" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($migrations as $i => $m): ?>
                        <tr>
                            <td class="text-muted small"><?= $i + 1 ?></td>
                            <td>
                                <code class="text-dark"><?= esc($m['file']) ?></code>
                                <?php if ($m['description']): ?>
                                <br><small class="text-muted"><?= esc($m['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($m['ran']): ?>
                                <span class="badge bg-light text-dark border">Batch <?= esc($m['batch']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= $m['ran'] ? esc($m['run_at']) : '—' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($m['ran']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Done</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($migrations)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No migration files found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Migration history log -->
    <?php if (! empty($history)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Run History <small class="text-muted fw-normal">(from <code>migrations</code> table)</small></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Class</th>
                            <th>Namespace</th>
                            <th>Group</th>
                            <th style="width:80px" class="text-center">Batch</th>
                            <th style="width:180px">Run At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td><code class="small"><?= esc($h->class) ?></code></td>
                            <td class="small text-muted"><?= esc($h->namespace) ?></td>
                            <td class="small text-muted"><?= esc($h->group) ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= esc($h->batch) ?></span></td>
                            <td class="small text-muted"><?= date('Y-m-d H:i:s', $h->time) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</section>
