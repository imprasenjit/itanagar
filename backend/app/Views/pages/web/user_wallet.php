<div class="page-heading">
    <h3><i class="bi bi-wallet2 me-2"></i> <?= esc($userinfo->name) ?> &mdash; Wallet</h3>
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
            <h4 class="card-title">Wallet Details &mdash; <?= esc($userinfo->name) ?></h4>
        </div>
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <p class="text-muted mb-1">Wallet Balance</p>
                    <h2 class="text-success fw-bold mb-0">&#8377;<?= $money ? $money->money : '0' ?></h2>
                </div>
                <div class="col-md-8">
                    <form role="form" action="<?= base_url() ?>web/addmoney/<?= $userinfo->userId ?>" method="post">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Amount (&#8377;)</label>
                                <input type="number" class="form-control" name="money" min="1" required placeholder="Enter amount">
                            </div>
                            <div class="col-md-7 d-flex gap-2">
                                <button type="submit" name="type" value="Credit" class="btn btn-success">
                                    <i class="bi bi-plus-circle me-1"></i> Credit
                                </button>
                                <button type="submit" name="type" value="Debit" class="btn btn-warning">
                                    <i class="bi bi-dash-circle me-1"></i> Debit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Wallet History</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?php if (!empty($userRecords)): ?>
                <table id="userWalletTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Money (&#8377;)</th>
                            <th>Payment Info</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $c = 1; foreach ($userRecords as $ms): ?>
                        <tr>
                            <td><?= $c ?></td>
                            <td><?= esc($ms->uname) ?></td>
                            <td><?= $ms->money ?></td>
                            <td><span class="badge bg-secondary"><?= esc($ms->type) ?></span></td>
                            <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)) ?></td>
                        </tr>
                        <?php $c++; endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No wallet history found.</p>
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
var walletUserId = '<?= $userinfo->userId ?>';
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        var link = jQuery(this).get(0).href, value = link.substring(link.lastIndexOf('/') + 1);
        window.location.href = baseURL + 'web/user_wallet/' + walletUserId + '/' + value;
    });
});
$(function () { $('#userWalletTable').DataTable({ paging: false, searching: false, info: false }); });
</script>
