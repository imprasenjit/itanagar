<div class="prize_sec">
  <div class="container">
    <div class="prizeheader">
      <div class="row">
        <div class="col-md-6">
          <h4>Notifications</h4>
        </div>
        <div class="col-sm-6">
          <div class="resultfilter d-flex justify-content-end">
            <form class="form-inline" action="" method="get">

              <div class="form-group">
                <div class="selectbox">
                  <select id="dateSelect" name="game">

                    <?php
                    foreach ($lottery as $l) {
                      if ($w_id == $l->id) {
                        echo "<option selected value='" . $l->id . "'>" . $l->name . "</option>";
                      } else {

                        echo "<option value='" . $l->id . "'>" . $l->name . "</option>";
                      }
                    }
                    ?>
                  </select>


                  <?php
                  $error = session()->getFlashdata('error');
                  if ($error) {
                    $search_date = "";
                  }
                  ?>


                  <select name="sdate" id="selectDate" required>
                    <!-- 
<option value="">Please Select Date</option> -->
                    <?php


                    foreach ($lottery as $l) {
                      $date = (new \App\Models\WebModel())->getalldates_result($l->id);
                    ?>

                      <option value="" <?php echo ($w_id != $l->id) ? "style='display:none'" : ""; ?> data-id="<?php echo $l->id; ?>">Please Select Date</option>
                      <?php
                      foreach ($date as $dk) {

                      ?>

                        <option <?php // echo ($search_date==$dk->date) ? "selected" : ""; 
                                ?> <?php echo ($w_id != $dk->website_id) ? "style='display:none'" : ""; ?> data-id="<?php echo $dk->website_id; ?>" value="<?php echo date("m/d/Y", strtotime($dk->date)); ?>"><?php echo date("M d, Y", strtotime($dk->date)); ?></option>

                    <?php
                      }
                    }
                    ?>



                  </select>
                  <!-- 
                  <input type="text" id="datepicker" name="sdate" value="<?php // echo date("m/d/Y",strtotime($search_date));
                                                                          ?>"> -->

                </div>
              </div>
              &nbsp;&nbsp;&nbsp;
              <input class="btn btn-primary" type="submit" value="Filter">

            </form>

          </div>
        </div>
      </div>
    </div>



    <?php



    if ($error) {
    ?>
      <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo session()->getFlashdata('error'); ?>
      </div>
    <?php } else {
    ?>

      <!-- <div class="resultlist">
        <div class="resultlist_header">


          <h6>Results For <span style="color:green"><?php echo date("M d, Y", strtotime($search_date)); ?></span></h6>

        </div>
        <div class="table-responsive">

          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">SL NO</th>
                <th scope="col">GAME</th>
                <th scope="col">WINER No</th>
                <th scope="col">Amount</th>
              </tr>
            </thead>
            <tbody>

              <?php
              $sr = 1;
              foreach ($winner as $w) {
              ?>
                <tr>
                  <td><?php echo $sr; ?></td>
                  <td> </td>
                  <td> </td>

                  <td><?php if ($w->is_jackpot == 1) {
                        echo "Grand Prize";
                      } else {
                        echo $w->price_amount;
                      } ?></td>

                </tr>
              <?php
                $sr++;
              }
              ?>
            </tbody>
          </table>
        </div>
      </div> -->
    <?php
    }
    ?>

  </div>
</div>


<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  // $( function() {
  //   $( "#datepicker" ).datepicker();
  // } );


  $("#dateSelect").change(function() {
    $val = $(this).val();
    $("#selectDate").children("option").hide()
    $("#selectDate").children("option[data-id=" + $val + "]").show()

  })
</script>