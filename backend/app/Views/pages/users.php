<div class="page-heading">
    <h3><i class="bi bi-people-fill me-2"></i> User Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Users List</h4>
            <div class="card-header-action d-flex gap-2">
                <form action="<?php echo base_url() ?>userListing" method="POST" id="searchList" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-primary searchList" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <a class="btn btn-primary ms-2" href="<?php echo base_url(); ?>addNew">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Code</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Created On</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userRecords)) foreach ($userRecords as $record): ?>
                        <tr>
                            <td><?= $record->name ?></td>
                            <td><?= $record->email ?></td>
                            <td><?= $record->phonecode != "" ? "+" . $record->phonecode : "" ?></td>
                            <td><?= $record->mobile ?></td>
                            <td><span class="badge <?= $record->role == 'Admin' ? 'bg-danger' : 'bg-primary' ?>"><?= $record->role ?></span></td>
                            <td><?= date("d-m-Y", strtotime($record->createdDtm)) ?></td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-light" href="<?= base_url('login-history/' . $record->userId) ?>" title="Login History"><i class="bi bi-clock-history"></i></a>
                                <a class="btn btn-sm btn-info" href="<?= base_url('web/user_order/' . $record->userId) ?>" title="Orders"><i class="bi bi-bag-fill"></i></a>
                                <a class="btn btn-sm btn-primary" href="<?= base_url('editOld/' . $record->userId) ?>" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <a class="btn btn-sm btn-danger deleteUser" href="#" data-userid="<?= $record->userId ?>" title="Delete"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?php echo $pager->links(); ?>
        </div>
    </div>
</section>
<script type="text/javascript" src="<?= base_url() ?>public/admin/js/common.js" charset="utf-8"></script>
<script>
jQuery(document).ready(function () {
    jQuery('ul.pagination li a').click(function (e) {
        e.preventDefault();
        var link = jQuery(this).get(0).href;
        var value = link.substring(link.lastIndexOf('/') + 1);
        jQuery("#searchList").attr("action", baseURL + "userListing/" + value);
        jQuery("#searchList").submit();
    });
});
$(function () { $('#usersTable').DataTable({ paging: false, searching: false, info: false, columnDefs: [{ orderable: false, targets: -1 }] }); });
</script>
