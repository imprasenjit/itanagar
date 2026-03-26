<?php
$id          = $userInfo->id;
$title       = $userInfo->title;
$description = $userInfo->description;
?>
<div class="page-heading">
    <h3><i class="bi bi-file-earmark-text-fill me-2"></i> Page Management <small>Edit</small></h3>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-9">
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
                    <h4 class="card-title">Edit Page: <span class="text-muted fw-normal"><?= esc($title) ?></span></h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/editUpadtePage" method="post">
                        <input type="hidden" name="name" value="<?= esc($title) ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="14"><?= $description ?></textarea>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Save Page</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
