<style>
    /* Container holding the image and the text */
    .container {
        position: relative;
        text-align: center;
    }

    /* Top right text */
    .top-right {
        position: absolute;
        top: 9px;
        right: 104px;
        font-weight: bold;
        font-size: 28px;
    }
</style>
<div class="checkoutsec">

    <div class="container">
        <div class="row">
            <div class="col-md-8 m-auto">
                <div class="checkout_box">
                    <h3><?= $status ?></h3>
                    <div class="checkout_body">
                        <div class="col-xs-12 text-center mb-2">
                            <a href="#!" id="downloadTicket" class="btn btn-small btn-success">Download Ticket</a>
                        </div>
                        <div class="payment_body" id="paymentDetails">
                            <p>Order ID : <?= $details['razorpay_payment_id']; ?></p>
                            <p>Payment ID : <?= $details['razorpay_order_id']; ?></p>
                            <?php foreach ($ticket_details as $key => $value) { ?>
                                <div>
                                    <div class="container">
                                        <img src="<?php echo base_url('assets/imglogo') . "/" . $value["range"]->logo; ?>" alt="ticket" style="width:100%;">
                                        <div class="top-right"><?= $value["ticketNo"]; ?></div>
                                    </div>
                                </div><br />
                            <?php } ?>
                        </div> <!-- card-body.// -->
                        </article> <!-- card.// -->
                        </aside> <!-- col.// -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?= base_url() . "assets/js/html2canvas.js" ?>"></script>
<script>
    function PrintDiv() {
        html2canvas(document.querySelector("#paymentDetails")).then(canvas => {
            var myImage = canvas.toDataURL();
            downloadURI(myImage, "ticket.png");
        });
    }

    function downloadURI(uri, name) {
        var link = document.createElement("a");

        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        //after creating link you should delete dynamic link
        //clearDynamicLink(link); 
    }
    $(document).ready(function() {
        $('#downloadTicket').on('click', () => {
            PrintDiv();
        })
    })
</script>