<?php
$id       = $userInfo->id;
$question = $userInfo->question;
$answer   = $userInfo->answer;
?>
<div class="page-heading">
    <h3><i class="bi bi-megaphone-fill me-2"></i> Announcement Management <small>Edit</small></h3>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-8">
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
                    <h4 class="card-title">Edit Announcement Details</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/faqupdate" method="post">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="question" value="<?= esc($question) ?>" maxlength="128">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Announcement Content</label>
                            <textarea class="form-control" name="answer" rows="8"><?= esc($answer) ?></textarea>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Update</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
