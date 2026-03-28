<?php $can = $can ?? function() { return true; }; ?>

<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3>Roles</h3>
        <p class="text-subtitle text-muted">Manage user roles</p>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Roles</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <section class="section">
    <div class="row">

      <!-- Add Role Card -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Add New Role</h4>
          </div>
          <div class="card-body">
            <?php if (session()->getFlashdata('success')): ?>
              <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
              <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <form action="<?= base_url('web/addRole') ?>" method="POST">
              <?= csrf_field() ?>
              <div class="mb-3">
                <label class="form-label">Role Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Manager" maxlength="64" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Add Role</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Roles List Card -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">All Roles</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($roles)): ?>
                  <tr><td colspan="3" class="text-center text-muted">No roles found.</td></tr>
                  <?php else: ?>
                  <?php foreach ($roles as $role): ?>
                  <tr>
                    <td><?= esc($role->roleId) ?></td>
                    <td>
                      <?= esc($role->role) ?>
                      <?php if ((int) $role->roleId === 1): ?>
                        <span class="badge bg-warning text-dark ms-1">Super Admin</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ((int) $role->roleId !== 1): ?>
                      <a href="<?= base_url('web/editRole/' . $role->roleId) ?>" class="btn btn-sm btn-primary" title="Edit">
                        <i class="bi bi-pencil-fill"></i> Edit
                      </a>
                      <button class="btn btn-sm btn-danger btn-delete-role" data-id="<?= $role->roleId ?>" title="Delete">
                        <i class="bi bi-trash3-fill"></i> Delete
                      </button>
                      <?php else: ?>
                      <span class="text-muted small">Protected</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<script>
document.querySelectorAll('.btn-delete-role').forEach(btn => {
  btn.addEventListener('click', function () {
    if (!confirm('Delete this role? All permissions assigned to it will also be removed.')) return;
    const id = this.dataset.id;
    fetch('<?= base_url('web/deleteRole') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ userId: id, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === true) {
        this.closest('tr').remove();
      } else {
        alert(res.msg ?? 'Could not delete role.');
      }
    });
  });
});
</script>
