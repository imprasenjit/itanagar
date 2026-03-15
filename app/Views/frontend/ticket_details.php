<div class="quiz_sec pt-4 pb-4">

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
                <!-- <div class="col-md-4">
                    <div class="row quizsec_box quizsec_border">
                        <div class="col-5 text-right">Prize Money</div>
                        <div class="col-7 text-left">
                            <?php echo $range->jackpot; ?>
                        </div>
                    </div>
                </div> -->
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
            <div class="col-sm-8 col-xs-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Aailable Tickets <small>( Select Tickets )</small></h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="ticket_area">
                            <?php
                            foreach ($tickets as $key => $value) {
                                echo '<a href="#!" class="btn btn-custom-primary btn-sm float-right ticket-button item-' . $value . '" data-ticket="' . $value . '">' . $value . ' </a>&nbsp;';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-4">
                <div class="card ticket-details-card">
                    <div class="card-header ticket-details-card-header">
                        <h5 class="card-title card-title-ticket-details">Selected Ticket </h5>
                    </div>
                    <div class="card-body  ticket-details-card-body">
                        <form method="post" action="<?php echo base_url('game/addtocart'); ?>">
                            <div class="row">
                                <div class="form-group col-md-12 col-6 ticket-details-mobile">
                                    <img class="ticket-image-ticket-details" src="<?php echo base_url('public/imglogo') . "/" . $range->logo; ?>">
                                </div>
                                <div class="form-group col-md-12 col-12" id="form_input">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-7 ticket-price">No of Tickets </div>
                                            <div class="col-5 ticket-price"> <b><span class="no_of_tickets">0</span></b></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-7 ticket-price">Total (<span class="no_of_tickets">0</span></b> x INR <?php echo $range->price; ?>)</div>
                                            <div class="col-5 ticket-price"> <b>INR <span id="pricechange">0</b></div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="web_id" value="<?php echo $range->web_id; ?>">
                                </div>
                                <div class="col-md-12 col-12">
                                    <input type="submit" value="Add To Cart" id="addclickcart" class="btn btn-custom btn-block">

                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
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

            $(document).on('click', '.ticket-button', function() {
                var ticket_no = parseInt($(this).attr('data-ticket'));
                var classname = '.item-' + ticket_no;
                console.log("classname", classname)
                if ($(this).hasClass('btn-custom-primary-selected')) {
                    $(classname).removeClass('btn-custom-primary-selected');
                    $(classname).addClass('btn-custom-primary');
                } else {
                    $(classname).addClass('btn-custom-primary-selected');
                    $(classname).removeClass('btn-custom-primary');
                }
                const found = tickets.find(element => element === ticket_no);
                // console.log("Tickets", tickets)
                // console.log("tickets.indexOf(parseInt(ticket_no)) ", tickets.indexOf(parseInt(ticket_no)))
                if (tickets.indexOf(parseInt(ticket_no)) != -1) {
                    tickets = tickets.filter(item => item !== ticket_no)
                } else {
                    tickets.push(parseInt(ticket_no));
                }
                let ticketPrice = <?= $range->price; ?>;
                let totalPrice = ticketPrice * tickets.length;
                $(".no_of_tickets").text(tickets.length);
                $("#pricechange").text(totalPrice);
            })
        })
    </script>