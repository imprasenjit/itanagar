<div class="page-heading">
    <h3><i class="bi bi-arrow-left-right me-2"></i> Transfer History</h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Transfer History</h4>
            <div class="card-header-action d-flex">
                <form action="<?= base_url() ?>web/transfer2" method="POST" id="searchList" class="d-flex">
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
                            <th>Transfer Email</th>
                            <th>Money</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $c = 1; foreach ($userRecords as $ms): ?>
                        <tr>
                            <td><?= $c ?></td>
                            <td><?= esc($ms->uname) ?></td>
                            <td><?= esc($ms->paypal_email) ?></td>
                            <td><strong>&#8377;<?= $ms->money ?></strong></td>
                            <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)) ?></td>
                        </tr>
                        <?php $c++; endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No transfer records found.</p>
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
        jQuery("#searchList").attr("action", baseURL + "web/transfer/" + value);
        jQuery("#searchList").submit();
    });
});
</script>
