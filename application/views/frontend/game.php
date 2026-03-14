



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
if($counter>3){
  $counter=0;
}
          }
        }
      ?>
          
          
        </div>
      </div>
    </div>