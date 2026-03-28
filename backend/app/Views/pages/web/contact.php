<div class="page-heading">
    <h3><i class="bi bi-envelope-fill me-2"></i> Contact List</h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Contact Submissions</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="contactTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userRecords)):
                            $sr = 1;
                            foreach ($userRecords as $record): ?>
                        <tr>
                            <td><?= $sr++ ?></td>
                            <td><?= esc($record->name) ?></td>
                            <td><?= esc($record->email) ?></td>
                            <td><?= esc($record->message) ?></td>
                            <td><?= date("M d, Y", strtotime($record->createdAt)) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No contact submissions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    </div>
</section>
<script>
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        window.location.href = jQuery(this).attr('href');
    });
});
$(function () { $('#contactTable').DataTable({ paging: false, searching: false, info: false }); });
</script>
