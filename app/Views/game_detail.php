<link rel="stylesheet" href="<?php echo base_url(); ?>public/css/owl.carousel.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>public/css/owl.theme.default.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>/css/flipimage.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="quiz_sec pt-4 pb-4">
  <form method="post" action="<?php echo base_url('game/addtocart'); ?>">
    <div class="container">
      <div class="card text-white custom_bg mb-4">
        <div class="row card-body">
          <div class="col-md-4">
            <div class="row">
              <div class="col-12 text-center">
                <h3><?php echo $website->name; ?></h3>
              </div>
            </div>
            <div class="row text-center">
              <div class="col-12">Ticket Price : INR <?php echo $range->price; ?>
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
                <p class=""><?= date('d-m-Y h:i A', strtotime($range->result_date)); ?></p>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-4 col-md-4 col-lg-4 col-xs-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Event</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="form-group col-md-12">
                  <div class="flip-box">
                    <div class="flip-box-inner">
                      <div class="flip-box-front">
                        <img class="card-img-top" src="<?php echo base_url('imglogo') . "/" . $range->logo; ?>" style="height:200px">
                      </div>
                      <div class="flip-box-back">
                        <img class="card-img-top" src="<?php echo base_url('imglogo') . "/" . $range->logo2; ?>" style="height:200px">

                      </div>
                    </div>
                  </div>
                  <!-- <img style="height:100%;" src="<?php echo base_url('imglogo') . "/" . $range->logo; ?>">
                  <img style="height:100%;" src="<?php echo base_url('imglogo') . "/" . $range->logo2; ?>"> -->

                </div>
                <div class="form-group col-md-12">
                  <?= $range->play_description; ?> </div>
              </div>
            </div>
          </div>
          <!-- <input type="submit" value="Add To Cart" id="addclickcart" class="btn btn-warning btn-block"> -->
        </div>
        <div class="col-sm-8 col-xs-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Event Ticket Series</h5>
            </div>
            <div class="card-body">
              <!-- <p class="card-text">Mulitple tickets nos can be seleted. Total Tickets </p>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="input-group">
                      <input type="text" class="form-control" placeholder="Search ticket no" name="search" id="search">
                      <div class="input-group-append">
                        <a class="btn btn-primary" id="searchButton" href="#!">Search</a>
                      </div>
                    </div>
                    <small id="searchHelp" class="form-text text-muted">If you want to search for a specific no please use the search.</small>

                  </div>
                </div>
              </div> -->

              <div class="row" id="ticket_area">
                <div class="list-group list-group-lottery">
                  <?php
                  // base_url("game/tickets/'. $range->web_id.'/'.($ticketRange[$key] + 1).'/'.($ticketRange[$key + 1]).'
                  // print_r($ticketRange);
                  $i = 0;
                  foreach ($ticketRange as $key => $value) {
                    if (array_key_exists(($i + 1), $ticketRange)) {
                      $rangeStart = ($ticketRange[$i]);
                      $rangeEnd = ($ticketRange[$i + 1]);
                      echo '<a href="' . base_url("game/tickets/$range->web_id/$rangeStart/$rangeEnd") . '" class="list-group-item list-group-item-action ">SERIES 
                    ' . ($ticketRange[$i]) . '-' . ($ticketRange[$i + 1]) . '
                      </a>';
                    }
                    $i += 2;
                  }
                  // foreach ($available_tickets as $key => $value) {
                  //   if ($key == 0) {
                  //     echo '<div class="col-md-12 mt-1">';
                  //   }
                  //   echo '<a href="#!" class="btn btn-outline-primary btn-sm ticket-button item-' . $value . '" data-ticket="' . $value . '">' . $value . '</a>&nbsp;';
                  //   if (($key + 1) % 10 == 0 && $key != 0) {
                  //     echo '</div><div class="col-md-12 mt-1">';
                  //   }
                  // }
                  ?>
                </div>
              </div>
            </div>
          </div>
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

    $(document).ready(function() {
      var tickets = [];
      $("#addclickcart").click(function(e) {
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
      $('#searchButton').on('click', () => {
        $('#searchButton').empty().append("Searching....");
        var searchText = $('#search').val();
        if (searchText == '') {
          alert("Please type in the search field");
          return;
        }
        $.ajax({
            method: "POST",
            url: "<?= base_url('game/getavailabletickets') ?>",
            data: {
              search: searchText,
              web_id: <?= $range->web_id ?>
            }
          })
          .done(function(res) {
            $('#searchButton').empty().append("Search");
            if (res.status) {
              $('#ticket_area').prepend('<div class="col-md-12 mb-4"><a href="#!" class="btn btn-outline-primary btn-sm ticket-button item-' + searchText + '" data-ticket="' + searchText + '">' + searchText + '</a>&nbsp;</div>');

            } else {
              alert("Ticket No:" + searchText + " is Not Available ");
            }

          });
      })

      $(document).on('click', '.ticket-button', function() {
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

        let ticketPrice = <?= $range->price ?>;
        let totalPrice = ticketPrice * tickets.length;
        $("#pricechange").text(totalPrice);
      })
    })
    // $("#inputState").change(function(e) {
    //   e.preventDefault();
    //   $val = $(this).val();
    //   $price = $(this).data('price');
    //   $totaldraw = $("#totaldraw").val();
    //   $(".item_rk").hide();
    //   $p = $price * $val * $totaldraw;
    //   $(".item_rk").each(function() {
    //     if ($(this).data("check") <= $val) {
    //       $(this).show();
    //     } else {
    //       $(this).hide();
    //     }
    //   });
    //   $("#pricechange").text($p);
    //   $("#total_price").val($p);
    // })
    // $('.select-tickets').select2({
    //   ajax: {
    //     url: '<?= base_url('game/getavailabletickets') ?>',
    //     minimumInputLength: 3,
    //     minimumResultsForSearch: 20,
    //     //processResults: function(data) {
    //     // Transforms the top-level key of the response object from 'items' to 'results'
    //     //return {
    //     //  results: data.items
    //     // };
    //     //},
    //     data: function(params) {
    //       var query = {
    //         search: params.term,
    //         web_id: <?= $range->web_id ?>
    //       }
    //       // Query parameters will be ?search=[term]&type=public
    //       return query;
    //     }
    //   }
    // }).on('select2:close', function() {
    //   let select = $(this);
    //   let data = select.select2('data');
    //   let ticketPrice = <?= $range->price ?>;
    //   let totalPrice = ticketPrice * data.length;
    //   $("#pricechange").text(totalPrice);
    // });
  </script>