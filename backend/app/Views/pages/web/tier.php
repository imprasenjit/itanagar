<div class="page-heading">
    <h3><i class="bi bi-layers-fill me-2"></i> <?= esc($WebInfo->name) ?> &mdash; Prize Tier</h3>
</div>

<section class="section">
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Prize Tier Patterns</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>White Balls</th>
                                    <th>Mega Ball</th>
                                    <th>Prize (%)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <form action="<?= base_url() ?>web/addtier" method="post">
                                        <input type="hidden" name="web_id" value="<?= $WebInfo->id ?>">
                                        <td>
                                            <select name="white" class="form-select form-select-sm" required>
                                                <option value="">Select</option>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="yellow" class="form-select form-select-sm" required>
                                                <option value="">Select</option>
                                                <?php for ($i = 0; $i < 3; $i++): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="per" class="form-control form-control-sm" required placeholder="e.g. 10">
                                        </td>
                                        <td class="text-center">
                                            <button type="submit" name="type" value="Add" class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-lg"></i> Add
                                            </button>
                                        </td>
                                    </form>
                                </tr>

                                <?php if (count($tier) > 0): foreach ($tier as $t): ?>
                                <tr>
                                    <form action="<?= base_url() ?>web/addtier" method="post">
                                        <input type="hidden" name="web_id" value="<?= $WebInfo->id ?>">
                                        <input type="hidden" name="id" value="<?= $t->id ?>">
                                        <td>
                                            <select name="white" class="form-select form-select-sm" required>
                                                <option value="">Select</option>
                                                <?php for ($i = 0; $i < 7; $i++): ?>
                                                <option value="<?= $i ?>" <?= $t->white == $i ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="yellow" class="form-select form-select-sm" required>
                                                <option value="">Select</option>
                                                <?php for ($i = 0; $i < 3; $i++): ?>
                                                <option value="<?= $i ?>" <?= $t->mega == $i ? 'selected' : '' ?>><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="per" class="form-control form-control-sm" required value="<?= $t->per ?>" placeholder="e.g. 10">
                                        </td>
                                        <td class="text-center">
                                            <button type="submit" name="type" value="Update" class="btn btn-sm btn-primary">
                                                <i class="bi bi-check-lg"></i> Update
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('web/rangeEdit') . '/' . $WebInfo->id ?>" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
