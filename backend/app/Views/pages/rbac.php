<div class="page-heading">
    <h3><i class="bi bi-shield-lock-fill me-2"></i> Role Permissions
        <small class="text-muted fs-6 fw-normal ms-2">Access Control</small>
    </h3>
</div>

<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Assign Permissions per Role</h4>
            <small class="text-muted">Super Admin always has full access and is not listed here.</small>
        </div>
        <div class="card-body">

            <ul class="nav nav-tabs mb-4" id="roleTabs" role="tablist">
                <?php $isFirst = true; foreach ($roles as $role): if ($role->role === ROLE_ADMIN) continue; ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $isFirst ? 'active' : '' ?>"
                        id="tab-<?= $role->roleId ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-<?= $role->roleId ?>"
                        type="button" role="tab">
                        <?= esc($role->role) ?>
                    </button>
                </li>
                <?php $isFirst = false; endforeach; ?>
            </ul>

            <div class="tab-content" id="roleTabsContent">
                <?php
                $isFirst = true;
                foreach ($roles as $role):
                    if ($role->role === ROLE_ADMIN) continue;
                    $roleAssigned = $assigned[$role->roleId] ?? [];
                    $grouped = [];
                    foreach ($permissions as $perm) {
                        $grouped[$perm->group_name][] = $perm;
                    }
                ?>
                <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>"
                     id="pane-<?= $role->roleId ?>" role="tabpanel">

                    <form class="rbac-form" data-role-id="<?= $role->roleId ?>">
                        <?php foreach ($grouped as $groupName => $perms): ?>
                        <div class="mb-4">
                            <h6 class="text-muted fw-semibold border-bottom pb-2 mb-3">
                                <i class="bi bi-folder2-open me-1"></i><?= esc($groupName) ?>
                            </h6>
                            <div class="row g-2">
                                <?php foreach ($perms as $perm):
                                    $cbId = 'perm_' . $role->roleId . '_' . str_replace('.', '_', $perm->key);
                                ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="permissions[]"
                                            value="<?= esc($perm->key) ?>"
                                            id="<?= $cbId ?>"
                                            <?= in_array($perm->key, $roleAssigned) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $cbId ?>">
                                            <?= esc($perm->label) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-primary save-rbac">
                                <i class="bi bi-shield-check me-1"></i> Save Permissions
                            </button>
                            <button type="button" class="btn btn-outline-secondary check-all">
                                <i class="bi bi-check2-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary uncheck-all">
                                <i class="bi bi-x-square me-1"></i> Clear All
                            </button>
                        </div>
                    </form>
                </div>
                <?php $isFirst = false; endforeach; ?>
            </div>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.save-rbac').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form   = this.closest('.rbac-form');
            var roleId = form.dataset.roleId;
            var checked = Array.from(form.querySelectorAll('input[type=checkbox]:checked'))
                              .map(function (c) { return c.value; });

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch(baseURL + 'web/rbacSave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'role_id=' + encodeURIComponent(roleId)
                    + (checked.length
                        ? '&' + checked.map(function (k) {
                            return 'permissions[]=' + encodeURIComponent(k);
                          }).join('&')
                        : '')
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                btn.disabled = false;
                if (d.status === 'ok') {
                    btn.classList.replace('btn-primary', 'btn-success');
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Saved!';
                    setTimeout(function () {
                        btn.classList.replace('btn-success', 'btn-primary');
                        btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Save Permissions';
                    }, 2500);
                } else {
                    btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Save Permissions';
                    alert('Save failed — access denied.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Save Permissions';
                alert('Network error. Please try again.');
            });
        });
    });

    document.querySelectorAll('.check-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.rbac-form')
                .querySelectorAll('input[type=checkbox]')
                .forEach(function (c) { c.checked = true; });
        });
    });

    document.querySelectorAll('.uncheck-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.rbac-form')
                .querySelectorAll('input[type=checkbox]')
                .forEach(function (c) { c.checked = false; });
        });
    });

});
</script>
