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
        <div class="col-sm-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">Aailable ticket numbers(<?= $range->rangeStart . '-' . $range->rangeEnd ?>)</h5>
            </div>
            <div class="card-body">
              <p class="card-text">Mulitple tickets nos can be seleted. Total Tickets </p>
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
              </div>

              <div class="row" id="ticket_area">
                <?php
                foreach ($available_tickets as $key => $value) {
                  if ($key == 0) {
                    echo '<div class="col-md-12 mt-1">';
                  }
                  echo '<a href="#!" class="btn btn-outline-primary btn-sm ticket-button item-' . $value . '" data-ticket="' . $value . '">' . $value . '</a>&nbsp;';
                  if (($key + 1) % 10 == 0 && $key != 0) {
                    echo '</div><div class="col-md-12 mt-1">';
                  }
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">your TICKET</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-9">
                <img style="height:150px;" src="<?php echo base_url('public/imglogo') . "/" . $range->logo; ?>">
              </div>
              <div class="form-group col-md-3" id="form_input">
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
<script src="<?php echo base_url() ?>public/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
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