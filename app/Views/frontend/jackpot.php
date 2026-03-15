<div class="moregames">
  <div class="container">
    <div class="row">

      <?php
      $counter = 1;
      foreach ($lottery as $l) {
      ?>
        <div class="col-md-4 mt-3">
          <div class="card">
            <img class="card-img-top" height="200px" src="<?php echo base_url('imglogo') . "/" . $l->logo; ?>">
            <div class="card-body">
              <h5 class="card-title"><?= $l->name ?></h5>
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




<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>


<script>
  function repeat_time(ids, countDown) {
    const second = 1000,
      minute = second * 60,
      hour = minute * 60,
      day = hour * 24;

    $ids = ids;
    setInterval(function() {

      $(".total_rk").each(function(index) {
        $this = $(this);
        $ids = $this.data('ids');
        let countDown = new Date($("#date" + $ids).val()).getTime();
        repeat_time($ids, countDown);

        let now = new Date().getTime(),
          distance = countDown - now;
        document.getElementById('days' + $ids).innerText = Math.floor(distance / (day)),
          document.getElementById('hours' + $ids).innerText = Math.floor((distance % (day)) / (hour)),
          document.getElementById('minutes' + $ids).innerText = Math.floor((distance % (hour)) / (minute)),
          document.getElementById('seconds' + $ids).innerText = Math.floor((distance % (minute)) / second);
      });

    }, second)
  }

  repeat_time();
</script>