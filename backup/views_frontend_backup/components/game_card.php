<?php
/**
 * Component: Game Card
 * Expects variable: $l (game object)
 */
?>
<div class="col-md-4 mt-3">
  <div class="card game-card">
    <div class="card-img-top game-card-media">
      <img src="<?php echo base_url('imglogo') . '/' . $l->logo; ?>" alt="<?= htmlspecialchars($l->name) ?>">
    </div>
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($l->name) ?></h5>
      <p class="mb-1">Result On: <?= date('d-m-Y h:i A', strtotime($l->result_date)); ?></p>
      <p class="mb-2">Price (per ticket): INR <?= $l->price ?></p>
      <a href="<?php echo base_url(); ?>game/type/<?= $l->id; ?>" class="btn btn-custom btn-block">Buy Now</a>
    </div>
  </div>
</div>
