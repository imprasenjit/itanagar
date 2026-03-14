<link rel="stylesheet" href="<?php echo base_url(); ?>public/css/owl.carousel.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>public/css/owl.theme.default.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="quiz_sec pt-4 pb-4">
  <form method="post" action="<?php echo base_url('game/addtocart'); ?>">
    <div class="container">
      <div class="quizsec_inn text-white bg-dark card text-center mb-4">
        <div class="row align-items-center">
          <div class="col-md-4">
            <div class="quizsec_box">
              <h3><?php echo $website->name; ?></h3>
            </div>
          </div>
          <div class="col-md-4">
            <div class="quizsec_box quizsec_border">
              <p>Prize Money</p>
              <h3><?php echo $range->jackpot; ?></h3>
              <p><small>Cost per Ticket - INR <?php echo $range->price; ?></small></p>
            </div>
          </div>
          <div class="col-md-4">


            <h4>Result Date</h3>
              <p><?= date('d-m-Y h:i A', strtotime($range->result_date)); ?></p>

          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-5">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Aailable ticket numbers</h5>
            </div>
            <div class="card-body">
              <p class="card-text">You can select mulitple tickets nos</p>
              <p class="card-text">First 10 available nos will be displayed.
                <br />For searching a specific no please type the no . if the no is available to select it means ticket is available
              </p>
              <div class="form-group">
                <select name="tickets[]" class="select-tickets form-control" multiple="multiple">
                  <?php
                  foreach ($availableTickets as $key => $value) {
                    echo '<option value="' . $value . '" selected="selected">' . $value . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-7">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">your TICKET</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="form-group col-md-9">
                  <img style="height:250px;" src="<?php echo base_url('assets/imglogo') . "/" . $range->logo; ?>">
                </div>
                <div class="form-group col-md-3">
                  <label for="inputState">Total Price</label><br />
                  <b>INR <span id="pricechange">0</b>
                  <input type="hidden" name="web_id" value="<?php echo $range->web_id; ?>">
                </div>
              </div>
            </div>
          </div>
          <input type="submit" value="Add To Cart" id="addclickcart" class="btn btn-warning btn-block">
        </div>
      </div>
    </div>
  </form>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"></script>

  <script src="<?php echo base_url() ?>public/js/owl.carousel.min.js"></script>
  <script src="<?php echo base_url() ?>public/js/custom.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $("#totaldraw").change(function() {
      $("#inputState").trigger("change");
    })
    $("#inputState").change(function(e) {
      e.preventDefault();
      $val = $(this).val();
      $price = $(this).data('price');
      $totaldraw = $("#totaldraw").val();
      $(".item_rk").hide();
      $p = $price * $val * $totaldraw;
      $(".item_rk").each(function() {
        if ($(this).data("check") <= $val) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
      $("#pricechange").text($p);
      $("#total_price").val($p);
    })

    $('.select-tickets').select2({
      ajax: {
        url: '<?= base_url('game/getavailabletickets') ?>',
        minimumInputLength: 3,
        minimumResultsForSearch: 20,
        //processResults: function(data) {
        // Transforms the top-level key of the response object from 'items' to 'results'
        //return {
        //  results: data.items
        // };
        //},
        data: function(params) {
          var query = {
            search: params.term,
            web_id: <?= $range->web_id ?>
          }
          // Query parameters will be ?search=[term]&type=public
          return query;
        }
      }
    }).on('select2:close', function() {
      let select = $(this);
      let data = select.select2('data');
      let ticketPrice = <?= $range->price ?>;
      let totalPrice = ticketPrice * data.length;
      $("#pricechange").text(totalPrice);
    });
    $("#addclickcart").click(function(e) {
      e.preventDefault();
      let data = $('.select-tickets').select2('data');
      if (data.length == 0) {
        alert("Please select atleast one ticket!")
      } else {
        $(this).parents("form").submit();
      }

    })
  </script>