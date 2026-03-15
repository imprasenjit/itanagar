<link rel="stylesheet" href="<?php echo base_url(); ?>/css/flipimage.css">
<div class="quiz_sec pt-4 pb-4">
  <form method="post" action="<?php echo base_url('game/addtocart'); ?>">
    <div class="container">
      <div class="card text-white custom_bg mb-4">
        <div class="row card-body">
          <div class="col-md-4">
            <div class="row">
              <div class="col-12 text-center">
                <h3>
                  <?php echo $website->name; ?>
                </h3>
              </div>
            </div>
            <div class="row text-center">
              <div class="col-12">Ticket Price : INR
                <?php echo $range->price; ?>
              </div>

            </div>
          </div>
          <div class="col-md-4">
            <div class="row quizsec_box quizsec_border">
              <div class="col-5 text-right">Prize Money</div>
              <div class="col-7 text-left">
                <?php echo $range->jackpot; ?>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="row">
              <div class="col-5 text-right">Result Date</div>
              <div class="col-7 text-left">
                <p class=""><?=date('d-m-Y h:i A', strtotime($range->result_date)); ?></p>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="row">

        <div class="col-sm-8 col-xs-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Event Ticket Series</h5>
            </div>
            <div class="card-body">
              <div class="row" id="ticket_area">
                <?php
$i = 0;
foreach ($ticketRange as $key => $value) {
    if (array_key_exists(($i + 1), $ticketRange)) {
        $rangeStart = ($ticketRange[$i]);
        $rangeEnd = ($ticketRange[$i + 1]);
                     echo ' <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6">
                      <div class="our-services-wrapper mb-60">
                        <a href="' . base_url("game/tickets/$range->web_id/$rangeStart/$rangeEnd") . '" > <div class="services-inner">
                          <div class="our-services-text">
                            <h4>' . ($ticketRange[$i]) . '-' . ($ticketRange[$i + 1]) . '</h4>
                    
                          </div>
                        </a>
                        </div>
                      </div>
                    </div>';
    }
    $i += 2;
}
?>

              </div>
            </div>
          </div>

        </div>
        <div class="col-sm-4 col-md-4 col-lg-4 col-xs-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Event Details</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="form-group col-md-12">
                  <div class="flip-box">
                    <div class="flip-box-inner">
                      <div class="flip-box-front">
                        <img class="card-img-top" src="<?php echo base_url('public/imglogo') . "/" . $range->logo; ?>"
                        style="height:200px">
                      </div>
                      <div class="flip-box-back">
                        <img class="card-img-top" src="<?php echo base_url('public/imglogo') . "/" . $range->logo2; ?>"
                        style="height:200px">

                      </div>
                    </div>
                  </div>
                  <!-- <img style="height:100%;" src="<?php echo base_url('public/imglogo') . "/" . $range->logo; ?>">
                  <img style="height:100%;" src="<?php echo base_url('public/imglogo') . "/" . $range->logo2; ?>"> -->

                </div>
                <div class="form-group col-md-12">
                  <?=$range->play_description; ?>
                </div>
              </div>
            </div>
          </div>
          <!-- <input type="submit" value="Add To Cart" id="addclickcart" class="btn btn-warning btn-block"> -->
        </div>
      </div>

    </div>
</div>
</form>

<script>
  $("#totaldraw").change(function () {
    $("#inputState").trigger("change");
  })

  $(document).ready(function () {
    var tickets = [];
    $("#addclickcart").click(function (e) {
      e.preventDefault();
      let data = tickets;
      if (tickets.length == 0) {
        alert("Please select atleast one ticket!")
      } else {
        tickets.forEach((value, index) => {
          $('#form_input').append('<input type="hidden" name="tickets[]" value="' + value + '">');
        })

        $(this).parents("form").submit();
      }
    })
    $(document).on('click', '.ticket-button', function () {
      var ticket_no = parseInt($(this).attr('data-ticket'));
      var classname = '.item-' + ticket_no;
      console.log("classname", classname)
      if ($(this).hasClass('btn-primary')) {
        $(classname).removeClass('btn-primary');
        $(classname).addClass('btn-outline-primary');
      } else {
        $(classname).addClass('btn-primary');
        $(classname).removeClass('btn-outline-primary');
      }
      const found = tickets.find(element => element === ticket_no);
      // console.log("Tickets", tickets)
      // console.log("tickets.indexOf(parseInt(ticket_no)) ", tickets.indexOf(parseInt(ticket_no)))
      if (tickets.indexOf(parseInt(ticket_no)) != -1) {
        tickets = tickets.filter(item => item !== ticket_no)
      } else {
        tickets.push(parseInt(ticket_no));

      }

      let ticketPrice = <?= $range -> price; ?>;
      let totalPrice = ticketPrice * tickets.length;
      $("#pricechange").text(totalPrice);
    })
  })

</script>