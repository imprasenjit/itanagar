<div class="checkoutsec">
  <div class="container">
    <div class="row">
      <div class="col-md-8 m-auto">
        <div class="checkout_box">
          <h3>Payment</h3>
          <div class="checkout_body">
            <div class="payment_body ">
              <h5 class="text-center mt-4 mb-0" style="color:green;">Order ID- <?= $order->order_id ?> </h5>
              <h5 class="text-center mt-4 mb-0" style="color:green;">Total Amount (INR)- <?= $order->amount / 100 ?> </h5>
              <!-- <button id="rzp-button1">Pay</button> -->
              <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
              <script>
                var options = {
                  "key": "<?= $order->key_id ?>", // Enter the Key ID generated from the Dashboard
                  "amount": "<?= $order->amount ?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
                  "currency": "<?= $order->currency ?>",
                  "name": "<?= $order->name ?> ", //your business name
                  "description": "<?= $order->description ?>",
                  "image": "<?= $order->image ?>",
                  "order_id": "<?= $order->order_id ?>", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                  "callback_url": "<?= $order->callback_url ?>",
                  "prefill": { //We recommend using the prefill parameter to auto-fill customer's contact information especially their phone number
                    "name": "<?= $order->prefill['name'] ?>", //your customer's name
                    "contact": "<?= $order->prefill['contact'] ?>" //Provide the customer's phone number for better conversion rates 
                  },
                  "notes": {
                    "address": "<?= $order->address ?>"
                  },
                  "theme": {
                    "color": "#3399cc"
                  }
                };
                var rzp1 = new Razorpay(options);
                $(document).ready(function() {
                  rzp1.open();
                  rzp1.on('payment.failed', function(response) {
                    console.log("response", response)
                    $.post("<?= base_url() . "game/payment_cancelled"; ?>", {
                        order_id: response.error.metadata.order_id,
                        payment_id: response.error.metadata.payment_id,
                        reason: response.error.reason,
                        description: response.error.description,
                        code: response.error.code,
                      })
                      .done(function(data) {});
                  });
                });
              </script>
              <aside class="">
                <article class="card">
                  <div class="card-body p-0 p-lg-5">
                    <div class="tab-content">
                      <div class="tab-pane fade show active" id="nav-tab-card">
                        <div class="row">
                          <div class="col-md-4">
                          </div>
                          <div class="col-md-4">
                            <div id="paypal-button-container">
                              <a href="<?= base_url(); ?>">Home</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div> <!-- tab-content .// -->
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