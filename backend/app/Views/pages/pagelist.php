<div class="page-heading">
    <h3><i class="bi bi-file-earmark-text-fill me-2"></i> Pages Management <small>Edit</small></h3>
</div>
<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Pages List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($page)) foreach ($page as $record): ?>
                        <tr>
                            <td><?= $record->title ?></td>
                            <td><?= substr(strip_tags($record->description), 0, 80) ?>...</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-primary" href="<?= base_url('web/pageedit/' . $record->id) ?>" title="Edit">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
