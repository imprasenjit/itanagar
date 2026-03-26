<div class="page-heading">
    <h3><i class="bi bi-dice-5-fill me-2"></i> Lottery Games Management <small>Add New Game</small></h3>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-7">
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
                    <h4 class="card-title">Enter Lottery Game Details</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/addNewWeb" method="post">
                        <div class="form-group">
                            <label class="form-label">Game Name</label>
                            <input type="text" class="form-control required" value="<?= set_value('name') ?>" name="name" maxlength="128" placeholder="Enter lottery game name">
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
