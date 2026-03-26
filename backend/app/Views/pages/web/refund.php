<div class="page-heading">
    <h3><i class="bi bi-arrow-counterclockwise me-2"></i> Refund History</h3>
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

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Refund Requests</h4>
            <div class="card-header-action d-flex">
                <form action="<?= base_url() ?>web/refund" method="POST" id="searchList" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="searchText" value="<?= $searchText ?>" class="form-control" placeholder="Search by user...">
                        <button class="btn btn-primary searchList" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($userRecords)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Money</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $c = 1; foreach ($userRecords as $ms):
                            if ($ms->status == "0") $st = "Pending";
                            elseif ($ms->status == "1") $st = "Refunded";
                            else $st = "Rejected";
                        ?>
                        <tr>
                            <td><?= $c ?></td>
                            <td><?= esc($ms->uname) ?></td>
                            <td><strong>&#8377;<?= $ms->money ?></strong></td>
                            <td><?= esc($ms->reason) ?></td>
                            <td>
                                <span class="badge <?= $ms->status == '0' ? 'bg-warning text-dark' : ($ms->status == '1' ? 'bg-success' : 'bg-danger') ?>">
                                    <?= $st ?>
                                </span>
                            </td>
                            <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)) ?></td>
                            <td class="text-center">
                                <?php if ($ms->status == "0"): ?>
                                <form onsubmit="return confirm('Are you sure?');" action="<?= base_url('web/refund_req') . '/' . $ms->user_id ?>" method="post" class="d-inline-flex gap-1">
                                    <input type="hidden" name="id" value="<?= $ms->id ?>">
                                    <input type="hidden" name="money" value="<?= $ms->money ?>">
                                    <button type="submit" name="type" value="Refund" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> Refund
                                    </button>
                                    <button type="submit" name="type" value="Reject" class="btn btn-sm btn-danger">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="badge <?= $ms->status == '1' ? 'bg-success' : 'bg-danger' ?>"><?= $st ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $c++; endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No refund records found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    </div>
</section>

<script src="<?= base_url() ?>public/admin/js/common.js"></script>
<script>
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        var link = jQuery(this).get(0).href, value = link.substring(link.lastIndexOf('/') + 1);
        jQuery("#searchList").attr("action", baseURL + "web/refund/" + value);
        jQuery("#searchList").submit();
    });
});
</script>
