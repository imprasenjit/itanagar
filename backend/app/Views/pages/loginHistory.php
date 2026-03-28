<link rel="stylesheet" href="<?= base_url('assets/extensions/flatpickr/flatpickr.min.css') ?>">
<div class="page-heading">
    <h3><i class="bi bi-clock-history me-2"></i> Login History <small>Track login history</small></h3>
</div>
<section class="section">
    <div class="card mb-3">
        <div class="card-body">
            <form action="<?php echo base_url() ?>login-history" method="POST" id="searchList">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label mb-1">From Date</label>
                        <div class="input-group">
                            <input id="fromDate" type="text" name="fromDate" value="<?= $fromDate ?>" class="form-control datepicker" placeholder="From Date" autocomplete="off">
                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label class="form-label mb-1">To Date</label>
                        <div class="input-group">
                            <input id="toDate" type="text" name="toDate" value="<?= $toDate ?>" class="form-control datepicker" placeholder="To Date" autocomplete="off">
                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <label class="form-label mb-1">Search</label>
                        <input type="text" name="searchText" value="<?= $searchText ?>" class="form-control" placeholder="Search by name or email...">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary searchList"><i class="bi bi-search"></i> Search</button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-secondary resetFilters"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?= !empty($userInfo) ? esc($userInfo->name) . " — " . esc($userInfo->email) : "All Users" ?></h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="loginHistoryTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Agent String</th>
                            <th>Platform</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userRecords)) foreach ($userRecords as $record): ?>
                        <tr>
                            <td><?= $record->sessionData ?></td>
                            <td><?= $record->machineIp ?></td>
                            <td><?= $record->userAgent ?></td>
                            <td><?= $record->agentString ?></td>
                            <td><?= $record->platform ?></td>
                            <td><?= $record->createdDtm ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/extensions/flatpickr/flatpickr.min.js') ?>"></script>
<script>
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        jQuery("#searchList").attr("action", jQuery(this).attr("href"));
        jQuery("#searchList").submit();
    });
    flatpickr('.datepicker', { dateFormat: 'd-m-Y', allowInput: true });
    jQuery('.resetFilters').click(function () {
        $(this).closest('form').find("input[type=text]").val("");
    });
});
$(function () { $('#loginHistoryTable').DataTable({ paging: false, searching: false, info: false }); });
</script>
