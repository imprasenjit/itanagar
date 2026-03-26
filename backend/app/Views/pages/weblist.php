<div class="page-heading">
    <h3><i class="bi bi-dice-5-fill me-2"></i> event Games Management <small>Add, Edit, Delete</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">event Games List</h4>
            <div class="card-header-action d-flex gap-2">
                <form action="<?php echo base_url() ?>web" method="POST" id="searchList" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control" placeholder="Search...">
                        <button class="btn btn-primary searchList" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <a class="btn btn-primary ms-2" href="<?php echo base_url(); ?>web/addNew">
                    <i class="bi bi-plus"></i> Add New
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created On</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($web)) foreach ($web as $record): ?>
                        <tr>
                            <td><?= $record->name ?></td>
                            <td>
                                <span class="badge <?= $record->status == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $record->status ?>
                                </span>
                            </td>
                            <td><?= date("d-m-Y", strtotime($record->createdDtm)) ?></td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-primary" href="<?= base_url('web/edit/' . $record->id) ?>" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <a class="btn btn-sm btn-info" href="<?= base_url('web/rangeEdit/' . $record->id) ?>" title="Details"><i class="bi bi-gear-fill"></i> Details</a>
                                <a class="btn btn-sm btn-secondary" href="<?= base_url('web/descriptionEdit/' . $record->id) ?>" title="Description"><i class="bi bi-text-left"></i> Desc</a>
                                <a class="btn btn-sm btn-danger deleteWeb" href="#" data-userid="<?= $record->id ?>" title="Delete"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url() ?>public/admin/js/common.js"></script>
