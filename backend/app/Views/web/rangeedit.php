<link href="<?= base_url('assets/extensions/summernote/summernote-bs5.min.css') ?>" rel="stylesheet">
<div class="page-heading">
    <h3><i class="bi bi-ticket-perforated-fill me-2"></i> <?= esc($WebInfo->name) ?> Management <small>Edit Ticket Range</small></h3>
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
            <?php if (function_exists('validation_errors')): ?>
                <?= validation_errors('<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button>', '</div>') ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Ticket Range Details</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/editRange" method="post" id="editUser" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $rangeInfo->id ?>">
                        <input type="hidden" name="web_id" value="<?= $rangeInfo->web_id ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Heading</label>
                                    <input class="form-control" type="text" required name="heading" value="<?= esc($rangeInfo->heading) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Price (INR)</label>
                                    <input class="form-control" type="number" required name="price" value="<?= $rangeInfo->price ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Ticket Range</label>
                                    <input class="form-control" type="text" required name="rangeStart" value="<?= esc($rangeInfo->rangeStart) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Ticket Priority</label>
                                    <input class="form-control" type="number" required name="priority" value="<?= $rangeInfo->priority ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prize Money (INR)</label>
                            <textarea required class="form-control" name="jackpot" id="jackpot"><?= $rangeInfo->jackpot ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Result Date &amp; Time</label>
                            <input class="form-control" type="datetime-local" required name="result_date" value="<?= $rangeInfo->result_date ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Ticket Image 1 <small class="text-muted">(Max 2MB, 1500×1500px)</small></label>
                                    <input class="form-control" type="file" name="logo" accept="image/*">
                                    <?php if ($rangeInfo->logo != ''): ?>
                                    <div class="mt-2">
                                        <img src="<?= base_url('imglogo') . '/' . $rangeInfo->logo ?>" class="img-thumbnail" style="max-height:150px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Ticket Image 2 <small class="text-muted">(Max 2MB, 1500×1500px)</small></label>
                                    <input class="form-control" type="file" name="logo2" accept="image/*">
                                    <?php if ($rangeInfo->logo2 != ''): ?>
                                    <div class="mt-2">
                                        <img src="<?= base_url('imglogo') . '/' . $rangeInfo->logo2 ?>" class="img-thumbnail" style="max-height:150px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Save Changes</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/extensions/summernote/summernote-bs5.min.js') ?>"></script>
<script>
$(document).ready(function () {
    $('#jackpot').summernote({ height: 200 });
});
</script>
