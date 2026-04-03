<?php $can = $can ?? function() { return true; }; ?>

<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3>Add Role</h3>
        <p class="text-subtitle text-muted">Create a new user role</p>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="web/roles">Roles</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Role</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-md-6 offset-md-3">
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
            <form action="web/addRole" method="POST">
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
    </div>
  </section>
</div>