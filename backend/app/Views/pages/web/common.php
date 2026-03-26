<div class="page-heading">
    <h3><i class="bi bi-sliders me-2"></i> Common Settings</h3>
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
                    <h4 class="card-title">Transaction Limits Configuration</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url() ?>web/editCommon" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Wallet Minimum ($)</label>
                                    <input class="form-control" type="number" required name="wallet_min" min="0" value="<?= $WebInfo->wallet_min ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Wallet Maximum ($)</label>
                                    <input class="form-control" type="number" required name="wallet_max" min="0" value="<?= $WebInfo->wallet_max ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Refund Minimum ($)</label>
                                    <input class="form-control" type="number" required name="refund_min" min="0" value="<?= $WebInfo->refund_min ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Refund Maximum ($)</label>
                                    <input class="form-control" type="number" required name="refund_max" min="0" value="<?= $WebInfo->refund_max ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transfer Minimum ($)</label>
                                    <input class="form-control" type="number" required name="transfer_min" min="0" value="<?= $WebInfo->transfer_min ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transfer Maximum ($)</label>
                                    <input class="form-control" type="number" required name="transfer_max" min="0" value="<?= $WebInfo->transfer_max ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Withdrawal Minimum ($)</label>
                                    <input class="form-control" type="number" required name="withdrawl_min" min="0" value="<?= $WebInfo->withdrawl_min ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Withdrawal Maximum ($)</label>
                                    <input class="form-control" type="number" required name="withdrawl_max" min="0" value="<?= $WebInfo->withdrawl_max ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1">Save Settings</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
