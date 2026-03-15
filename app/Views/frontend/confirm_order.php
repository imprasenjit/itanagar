<div class="checkoutsec">
  <div class="container">
    <div class="row">
      <div class="col-md-8 m-auto">
        <div class="checkout_box">
          <h3>Order summary</h3>
          <div class="checkout_body">
            <div class="confirm_body">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Event</th>
                    <th scope="col">Ticket No</th>
                    <th scope="col">Ticket Price</th>
                  </tr>
                </thead>
                <tbody>

                  <?php
                  $total = 0;
                  foreach ($data as $d) {
                  ?>

                    <tr>
                      <td><?php echo $d->name; ?></td>
                      <td><?php echo $d->ticket_no; ?></td>
                      <td><?php echo $d->total_price; ?></td>

                    </tr>
                  <?php
                    $total += $d->total_price;
                  }
                  ?>
                  <tr>
                    <td colspan="2"></td>
                    <td class="font-weight-bold"> Total: INR <?php echo $total; ?></td>
                  </tr>
                </tbody>
              </table>
              <?php if (isset($loggedIn) && $loggedIn == false) { ?>
                <div>
                  <form action="<?php echo base_url(); ?>game/payment" method="post">
                    <div class="form-group has-feedback">
                      <input type="text" class="form-control" placeholder="Full Name" name="fname" required value="" />
                      <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                      <input type="text" class="form-control" placeholder="Address (Optional)" name="address" value="" />
                      <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                      <input type="text" class="form-control required digits" required id="mobile" value="" name="mobile" maxlength="10" placeholder="Mobile">
                      <span class="glyphicon glyphicon-phone form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                      <input type="email" class="form-control" placeholder="Email (Optional)" name="email" value="" />
                      <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                      <div class="col-xs-12 text-center">
                        <p class="account_t pt-4">I have an account <a href="<?php echo base_url() ?>login">Sign In</a></p>
                        <button type="submit" class="btn btn-success" value="Register">
                          Continue to payment
                        </button>
                      </div><!-- /.col -->
                    </div>
                  </form>

                </div><!-- /.login-box-body -->
              <?php } else { ?>
                <div class="button_outer mt-3 text-right">
                  <!-- <a href="<?php echo base_url('game'); ?>" class="btn btn-warning">Add more Game</a> -->
                  <a href="<?php echo base_url('game/payment'); ?>" class="btn btn-success">Continue to payment</a>
                </div>
              <?php } ?>
            </div>

            <div class="col-xs-12 text-center">
              <a href="<?php echo base_url('game/jackpot'); ?>" class="btn btn-primary">Add more Ticket</a>
              <a href="<?php echo base_url('game/step2'); ?>" class="btn btn-danger">Edit Order</a>

            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</div>