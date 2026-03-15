<div class="banner">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="banner_box">
          <h4>Most trusted lottery for Arunachal Pradesh</h4>
        </div>
      </div>
      <div class="col-md-6"><img src="<?php echo base_url(); ?>/images/banner.png"></div>
    </div>
  </div>
</div>
<?php /*
    <div class="lottery_games">
      <div class="container">
        <div class="row">
      <?php
      $counter =0;
        if(count($website)>0){
          foreach($website as $wb){
            switch ($counter) {
              case "0":
                  $class= "orange_color";
                  break;
              case "1":
                $class= "purpal_color";
                  break;
              case "2":
              $class= "yellow_color";
                  break;
              default:
              $class= "pink_color";
          }
            ?>
          <div class="col-md-4">
            <div class="games_box <?php echo $class; ?>">
              <h4><?php echo  $wb->name; ?></h4>
              <p>$<?php echo $wb->price; ?></p>
              <ul>
                <li><a href="<?php echo base_url(); ?>game/type/<?= $wb->id; ?>">Read More</a><!--<span>1day 14h 33m 55s</span>--></li>
              </ul>
            </div>
          </div>
            <?php
$counter++;
          }
        }
      ?>
          <div class="col-md-5">
          </div>
          <div class="col-md-2">
          <a href="<?php echo base_url(); ?>game" class="btn btn-warning">More Games</a>
          </div>
          <div class="col-md-5">
          </div>
        </div>
      </div>
    </div>
 */ ?>
<div class="moregames">
  <div class="container">
    <div class="row">

      <?php
      $counter = 1;
      foreach ($lottery as $l) {
      ?>
        <div class="col-md-4 mt-3">
          <div class="card">
            <img class="card-img-top" height="200px" src="<?php echo base_url('public/imglogo') . "/" . $l->logo; ?>">
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
<div class="strip_drow">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-7">
        <h2>Never Miss a Draw</h2>
        <p>Many lotto winners miss out because they lose their ticket or forget to check their numbers. They might even forget to buy their tickets altogether. With WinTrillions you'll never miss a draw, never lose a ticket. We'll send you the numbers you are playing before each draw and we'll always let you know when you win.</p>
      </div>
      <div class="col-md-5">
        <img src="<?php echo base_url(); ?>/images/lottery_ticket.png">
      </div>
    </div>
  </div>
</div>
<div class="satisfaction">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-5">
        <img src="<?php echo base_url(); ?>/images/100-percent.svg">
      </div>
      <div class="col-md-7">
        <h2>100% Satisfaction <br>Guaranteed</h2>
        <p>Many lotto winners miss out because they lose their ticket or forget to check their numbers. They might even forget to buy their tickets altogether. With WinTrillions you'll never miss a draw, never lose a ticket. We'll send you the numbers you are playing before each draw and we'll always let you know when you win.</p>
      </div>
    </div>
  </div>
</div>
<div class="winners">
  <div class="container">
    <h1 class="text-center">Our Winners</h1>
    <div class="row">
      <div class="col-md-4">
        <div class="winner_box">
          <div class="winimg_box">
            <img src="<?php echo base_url(); ?>/images/price.svg">
          </div>
          <h5>Prizes Paid Out</h5>
          <h2>INR 0</h2>
          <a href="#">Read More</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="winner_box">
          <div class="winimg_box">
            <img src="<?php echo base_url(); ?>/images/winner.svg">
          </div>
          <h5>Our Recent Winners</h5>
          <!-- <p>Sigurdur G. S. - Iceland</p>
          <p>Italy - SuperEnalotto</p>
          <p>26 Jun 2019 / <strong>€ 157.54</strong></p> -->
        </div>
      </div>
      <div class="col-md-4">
        <div class="winner_box">
          <div class="winimg_box">
            <img src="<?php echo base_url(); ?>/images/qote.svg">
          </div>


          <a href="#">Read More</a>
        </div>
      </div>
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