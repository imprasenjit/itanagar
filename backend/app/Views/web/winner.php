<style>
.numberdegits { margin: 0; padding: 0; display: inline-flex; gap: 5px; list-style: none; }
.numberdegits li { display: inline-flex; align-items: center; justify-content: center; background: #01B623; color: #fff; width: 38px; height: 38px; border-radius: 50%; font-weight: 600; font-size: .8rem; }
.numberdegits li.mega { background: #ffc107; color: #000; }
</style>

<div class="page-heading">
    <h3><i class="bi bi-trophy-fill me-2"></i> Winner History</h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                Winner History
                <span class="badge bg-success ms-2">Total Prize: &#8377;<?= $amount->sum ?></span>
            </h4>
            <div class="card-header-action d-flex">
                <form action="<?= base_url() ?>web/winner" method="POST" id="searchList" class="d-flex">
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
                            <th>Order No.</th>
                            <th>User</th>
                            <th>Lottery Date</th>
                            <th>Game</th>
                            <th>Ball Combination</th>
                            <th>Price</th>
                            <th>Payment Type</th>
                            <th>Winning Prize</th>
                            <th>Transaction ID</th>
                            <th>Confirm Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userRecords as $ms): ?>
                        <tr>
                            <td><strong>#<?= $ms->id ?></strong></td>
                            <td><?= esc($ms->uname) ?></td>
                            <td><?= date("M d, Y", strtotime($ms->date)) ?></td>
                            <td><?= esc($ms->name) ?></td>
                            <td>
                                <ul class="numberdegits">
                                    <li><?= $ms->white1 ?></li>
                                    <li><?= $ms->white2 ?></li>
                                    <li><?= $ms->white3 ?></li>
                                    <li><?= $ms->white4 ?></li>
                                    <li><?= $ms->white5 ?></li>
                                    <?php if ($ms->white6 != ""): ?>
                                        <li><?= $ms->white6 ?></li>
                                    <?php endif; ?>
                                    <li class="mega"><?= $ms->yellow1 ?></li>
                                    <?php if ($ms->yellow2 != ""): ?>
                                        <li class="mega"><?= $ms->yellow2 ?></li>
                                    <?php endif; ?>
                                </ul>
                            </td>
                            <td>&#8377;<?= $ms->total_price ?></td>
                            <td><?= $ms->paid_type == "0" ? "Wallet" : esc($ms->paid_type) ?></td>
                            <td><strong class="text-success">&#8377;<?= $ms->prize ?></strong></td>
                            <td><small><?= esc($ms->transaction_id) ?></small></td>
                            <td><?= date("M d, Y h:i a", strtotime($ms->createdAt)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No winner records found.</p>
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
        jQuery("#searchList").attr("action", baseURL + "web/winner/" + value);
        jQuery("#searchList").submit();
    });
});
</script>
