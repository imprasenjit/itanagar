<link rel="stylesheet" href="<?php echo base_url(); ?>/css/flipimage.css">
<div class="moregames">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <?php
        $counter = 0;
        foreach ($faq as $f) {
        ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Announcement!</strong> <?php echo $f->question; ?>
            <p><?php echo $f->answer; ?></p>
          </div>

        <?php
          $counter++;
        }
        ?>
      </div>
      <?php
      $counter = 1;

      foreach ($game as $l) {
      ?>
        <div class="col-md-4 mt-3">
          <div class="card">
            <div class="flip-box">
              <div class="flip-box-inner">
                <div class="flip-box-front">
                  <img class="card-img-top" src="<?php echo base_url('public/imglogo') . "/" . $l->logo; ?>" style="height:200px">
                </div>
                <div class="flip-box-back">
                  <img class="card-img-top" src="<?php echo base_url('public/imglogo') . "/" . $l->logo2; ?>" style="height:200px">

                </div>
              </div>
            </div>

            <div class="card-body home-card-body">
              <h5 class="card-title"><?= $l->name ?><?php
                                                    $futDate = strtotime($l->result_date);
                                                    $today = time();
                                                    $timeleft = $futDate - $today;
                                                    $daysleft = round((($timeleft / 24) / 60) / 60);
                                                    ?></h5>
              <h6 class="card-title">Result On: <?= date('d-m-Y h:i A', strtotime($l->result_date)); ?></h5>

                <h6 class="card-title">Price(per ticket): INR <?= $l->price ?></h6>
                <div class="days-Left">
                  <h6 class="card-title"><?= $daysleft ?> Days Left</h5>
                </div>
            </div>

            <small><a href="<?php echo base_url(); ?>game/type/<?= $l->id; ?>" class="btn btn-block btn-light">Buy Now!</a></small>

          </div>
        </div>
      <?php
        $counter++;
      }
      ?>
    </div>
  </div>
</div>
</div>
</div>
</div>
</div>