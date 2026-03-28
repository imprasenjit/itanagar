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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">Migration Files</h4>
                <p class="text-muted small mb-0 mt-1">
                    <?= $ranCount ?> of <?= $totalCount ?> migrations applied.
                    <?php if ($pendingCount > 0): ?>
                        <span class="text-warning fw-semibold"><?= $pendingCount ?> pending.</span>
                    <?php else: ?>
                        <span class="text-success fw-semibold">All up to date.</span>
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($pendingCount > 0): ?>
            <form method="post" action="<?= base_url('web/runMigrations') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('Run all <?= $pendingCount ?> pending migration(s)?')">
                    <i class="bi bi-play-fill me-1"></i> Run All Pending Migrations
                </button>
            </form>
            <?php else: ?>
            <span class="badge bg-success fs-6"><i class="bi bi-check-all me-1"></i>All up to date</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="migrationsTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Migration File</th>
                            <th style="width:210px">Version</th>
                            <th style="width:110px" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($migrationList)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No migration files found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($migrationList as $i => $m): ?>
                        <tr>
                            <td class="text-muted small"><?= $i + 1 ?></td>
                            <td><code class="text-dark"><?= esc($m['filename']) ?></code></td>
                            <td class="small text-muted font-monospace"><?= esc($m['version']) ?></td>
                            <td class="text-center">
                                <?php if ($m['status'] === 'Applied'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Applied</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</section>
<script>$(function () { $('#migrationsTable').DataTable({ paging: false }); });</script>
