<div class="page-heading">
    <h3 class="page-title">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
        <small class="text-muted fs-6 fw-normal ms-2">Control panel</small>
    </h3>
</div>

<section class="section">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body px-4 py-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon blue">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted font-semibold">Lottery Games</h6>
                            <h4 class="font-extrabold mb-0"><?php echo $totalweb; ?></h4>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 px-4">
                    <a href="<?php echo base_url('web'); ?>" class="text-decoration-none text-muted small">
                        View all <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body px-4 py-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon green">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted font-semibold">Total Users</h6>
                            <h4 class="font-extrabold mb-0"><?php echo $totaluser; ?></h4>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 px-4">
                    <a href="<?php echo base_url('userListing'); ?>" class="text-decoration-none text-muted small">
                        View all <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
