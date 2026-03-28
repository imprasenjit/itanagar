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
        <div class="card-body p-5">
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
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/extensions/flatpickr/flatpickr.min.js') ?>"></script>
<script>
var lhTable;
$(function () {
    flatpickr('.datepicker', { dateFormat: 'd-m-Y', allowInput: true });
    lhTable = $('#loginHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseURL + 'login_history_data/<?= !empty($userInfo) ? $userInfo->userId : 0 ?>',
            type: 'GET',
            data: function (d) {
                d.fromDate = $('#fromDate').val();
                d.toDate   = $('#toDate').val();
            }
        },
        columns: [
            { data: 'session',     orderable: false },
            { data: 'ip',          orderable: false },
            { data: 'userAgent',   orderable: false },
            { data: 'agentString', orderable: false },
            { data: 'platform',    orderable: false },
            { data: 'date',        orderable: false }
        ]
    });
    $('.searchList').on('click', function (e) {
        e.preventDefault();
        lhTable.ajax.reload();
    });
    $('.resetFilters').on('click', function () {
        $('#fromDate, #toDate').val('');
        lhTable.ajax.reload();
    });
});
</script>
