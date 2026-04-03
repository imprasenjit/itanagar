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
                        <th style="width:90px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($migrationList)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No migration files found.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($migrationList as $i => $m): ?>
                        <tr id="row-<?= $i ?>">
                            <td class="text-muted small"><?= $i + 1 ?></td>
                            <td><code class="text-dark"><?= esc($m['filename']) ?></code></td>
                            <td class="small text-muted font-monospace"><?= esc($m['version']) ?></td>
                            <td class="text-center">
                                <?php if ($m['status'] === 'Applied'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Applied</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark" id="badge-<?= $i ?>"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($m['status'] !== 'Applied'): ?>
                                <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                    onclick="runSingle('<?= esc($m['version']) ?>', <?= $i ?>)">
                                    <i class="bi bi-play-fill"></i> Run
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr id="err-<?= $i ?>" class="d-none">
                            <td colspan="5" class="p-0">
                                <div class="alert alert-danger mb-0 rounded-0 small" id="errmsg-<?= $i ?>"></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.card migrations -->

    <!-- ── Seeders ─────────────────────────────────────────────────────── -->
    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title mb-0"><i class="bi bi-seedling me-2"></i>Database Seeders</h4>
            <p class="text-muted small mb-0 mt-1">Run seeders to populate reference / lookup data.</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Seeder</th>
                            <th>Description</th>
                            <th style="width:120px" class="text-center">Rows in Table</th>
                            <th style="width:90px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seederList as $si => $s): ?>
                        <tr id="srow-<?= $si ?>">
                            <td class="text-muted small"><?= $si + 1 ?></td>
                            <td><code class="text-dark"><?= esc($s['name']) ?></code></td>
                            <td class="small text-muted"><?= esc($s['description']) ?></td>
                            <td class="text-center">
                                <?php if ($s['rowCount'] > 0): ?>
                                <span class="badge bg-success" id="sbadge-<?= $si ?>">
                                    <i class="bi bi-check-lg me-1"></i><?= $s['rowCount'] ?> rows
                                </span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark" id="sbadge-<?= $si ?>">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Empty
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-success py-0 px-2"
                                    id="sbtn-<?= $si ?>"
                                    onclick="runSeeder('<?= esc($s['name']) ?>', <?= $si ?>)">
                                    <i class="bi bi-play-fill"></i> Run
                                </button>
                            </td>
                        </tr>
                        <tr id="serr-<?= $si ?>" class="d-none">
                            <td colspan="5" class="p-0">
                                <div class="alert alert-danger mb-0 rounded-0 small" id="serrmsg-<?= $si ?>"></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.card seeders -->

</section>
<script>
$(function () { /* DataTable removed — colspan error rows are incompatible with DataTables */ });

function runSingle(version, idx) {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';
    $('#err-' + idx).addClass('d-none');

    $.ajax({
        url: '<?= base_url('web/runSingleMigration') ?>',
        method: 'POST',
        data: { version: version, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#badge-' + idx).removeClass('bg-warning text-dark').addClass('bg-success').html('<i class="bi bi-check-lg me-1"></i>Applied');
                btn.closest('td').html('');
            } else {
                let html = '<strong>Error:</strong> ' + res.message;
                if (res.db_error_msg) html += '<br><strong>DB:</strong> [' + res.db_error_code + '] ' + res.db_error_msg;
                if (res.file)        html += '<br><strong>File:</strong> ' + res.file;
                if (res.trace)       html += '<br><strong>Trace:</strong><br>' + res.trace.join('<br>');
                $('#errmsg-' + idx).html(html);
                $('#err-' + idx).removeClass('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-play-fill"></i> Retry';
            }
        },
        error: function(xhr) {
            $('#errmsg-' + idx).text('HTTP ' + xhr.status + ': ' + xhr.responseText.substring(0, 300));
            $('#err-' + idx).removeClass('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Retry';
        }
    });
}

function runSeeder(seederName, idx) {
    const btn = document.getElementById('sbtn-' + idx);
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';
    document.getElementById('serr-' + idx).classList.add('d-none');

    $.ajax({
        url: '<?= base_url('web/runSeeder') ?>',
        method: 'POST',
        data: { seeder: seederName, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                const badge = document.getElementById('sbadge-' + idx);
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="bi bi-check-lg me-1"></i>Seeded';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Re-run';
            } else {
                let html = '<strong>Error:</strong> ' + res.message;
                if (res.file) html += '<br><strong>File:</strong> ' + res.file;
                document.getElementById('serrmsg-' + idx).innerHTML = html;
                document.getElementById('serr-' + idx).classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-play-fill"></i> Retry';
            }
        },
        error: function(xhr) {
            document.getElementById('serrmsg-' + idx).textContent = 'HTTP ' + xhr.status + ': ' + xhr.responseText.substring(0, 300);
            document.getElementById('serr-' + idx).classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Retry';
        }
    });
}
</script>
