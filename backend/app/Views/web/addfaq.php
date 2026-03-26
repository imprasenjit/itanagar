<div class="page-heading">
    <h3><i class="bi bi-megaphone-fill me-2"></i> Announcement Management <small>Add New</small></h3>
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
                    <h4 class="card-title">Enter Announcement Details</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/addNewfaq" method="post">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control required" name="question" placeholder="Announcement title" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Announcement Content</label>
                            <textarea class="form-control required" name="answer" rows="8" placeholder="Announcement details..."></textarea>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Submit</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
