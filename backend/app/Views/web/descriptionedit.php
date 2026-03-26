<div class="page-heading">
    <h3><i class="bi bi-text-paragraph me-2"></i> <?= esc($WebInfo->name) ?> Management <small>Edit Description</small></h3>
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
                    <h4 class="card-title">Edit Play Description</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/editdesc" method="post">
                        <input type="hidden" name="id" value="<?= $rangeInfo->id ?>">
                        <input type="hidden" name="web_id" value="<?= $rangeInfo->web_id ?>">
                        <div class="form-group">
                            <label class="form-label">How To Play</label>
                            <textarea required class="form-control" name="play_description" rows="10"><?= esc($rangeInfo->play_description) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">When To Play</label>
                            <textarea required class="form-control" name="when_play" rows="10"><?= esc($rangeInfo->when_play) ?></textarea>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Save Description</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
