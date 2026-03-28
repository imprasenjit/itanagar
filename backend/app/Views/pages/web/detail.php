<link rel="stylesheet" href="<?= base_url() ?>public/assets/extensions/flatpickr/flatpickr.min.css">

<div class="page-heading">
    <h3><i class="bi bi-grid-3x3-gap-fill me-2"></i> <?= esc($WebInfo->name) ?></h3>
</div>

<section class="section">
    <?php $error = session()->getFlashdata('error'); if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $error ?>
    </div>
    <?php endif; ?>
    <?php $success = session()->getFlashdata('success'); if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $success ?>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">Range &amp; Event Details</h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="text-primary fw-bold"><?= esc($RangeInfo->heading) ?></h5>
                    <hr>
                    <h6 class="text-success">White Ball Range</h6>
                    <p class="mb-1"><strong>Max Selection:</strong> <?= $RangeInfo->white_ball ?></p>
                    <p class="mb-1"><strong>Range:</strong> <?= $RangeInfo->white_from ?> &mdash; <?= $RangeInfo->white_to ?></p>
                    <hr>
                    <h6 class="text-warning">Yellow Ball Range</h6>
                    <p class="mb-1"><strong>Max Selection:</strong> <?= $RangeInfo->yellow_ball ?></p>
                    <p class="mb-1"><strong>Range:</strong> <?= $RangeInfo->yellow_from ?> &mdash; <?= $RangeInfo->yellow_to ?></p>
                    <hr>
                    <h6 class="text-muted">Logo</h6>
                    <img style="height: 80px;" src="<?= base_url('imglogo') . '/' . $RangeInfo->logo ?>" alt="Logo" class="rounded">
                </div>
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h5 class="card-title text-muted">event Price</h5>
                            <h3 class="text-primary fw-bold">&#8377;<?= $RangeInfo->price ?></h3>
                        </div>
                    </div>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Jackpot Price</h5>
                            <h3 class="text-warning fw-bold"><?= $RangeInfo->jackpot ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2 flex-wrap">
            <a href="<?= base_url('web/rangeEdit') . '/' . $WebInfo->id ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Range
            </a>
            <a href="<?= base_url('web/addtwoWebdate') . '/' . $WebInfo->id ?>" class="btn btn-secondary">
                <i class="bi bi-calendar-plus me-1"></i> Add Next 10 Dates
            </a>
            <a href="<?= base_url('web/descriptionEdit') . '/' . $WebInfo->id ?>" class="btn btn-info">
                <i class="bi bi-file-text me-1"></i> Edit Description
            </a>
            <a href="<?= base_url('web/tier') . '/' . $WebInfo->id ?>" class="btn btn-warning">
                <i class="bi bi-layers me-1"></i> Prize Tiers
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Result Dates</h4>
            <div class="card-header-action">
                <form action="<?= base_url('web/addNewWebdate/' . $WebInfo->id) ?>" method="POST" id="addDateForm" class="d-flex">
                    <div class="input-group" style="width: 260px;">
                        <input type="text" name="date" class="form-control datepicker" autocomplete="off" required placeholder="Pick a date (dd-mm-yyyy)">
                        <button class="btn btn-success" type="submit">
                            <i class="bi bi-plus-lg me-1"></i> Add Date
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($userRecords)): ?>
                <table id="detailTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Created On</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; foreach ($userRecords as $record): ?>
                        <tr>
                            <td><?= $sr++ ?></td>
                            <td><?= date("M d, Y", strtotime($record->date)) ?></td>
                            <td><?= date("M d, Y", strtotime($record->createdAt)) ?></td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-danger deleteWebDate" href="#!" data-userid="<?= $record->id ?>" title="Delete">
                                    <i class="bi bi-trash3-fill"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No result dates added yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    </div>
</section>

<script src="<?= base_url() ?>public/assets/extensions/flatpickr/flatpickr.js"></script>
<script src="<?= base_url() ?>public/admin/js/common.js"></script>
<script>
flatpickr(".datepicker", { minDate: "today", dateFormat: "d-m-Y" });

jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        window.location.href = jQuery(this).attr('href');
    });
});

jQuery(document).on("click", ".deleteWebDate", function (e) {
    e.preventDefault();
    var userId = $(this).data("userid"), hitURL = baseURL + "web/deleteWebDate", currentRow = $(this);
    if (confirm("Are you sure you want to delete this date?")) {
        jQuery.ajax({ type: "POST", dataType: "json", url: hitURL, data: { userId: userId } })
            .done(function (data) {
                currentRow.closest('tr').remove();
                if (data.status) { alert("Date successfully deleted."); }
                else { alert("Date deletion failed."); }
            });
    }
});
$(function () { $('#detailTable').DataTable({ paging: false, searching: false, info: false, columnDefs: [{ orderable: false, targets: -1 }] }); });
</script>
