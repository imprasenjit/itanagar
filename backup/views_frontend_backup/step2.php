<div class="cartsummary">
  <div class="container">

    <?php
if (count($data) == 0) {
    ?>
      Cart is empty. Please proceed with <a href="<?php echo base_url('game/jackpot'); ?>" class="btn btn-link">Games</a>
    <?php
} else {
    ?>
      <h3 class="text-center">Confirm your tickets</h3>
      <div class="cartsummary_box">
        <div class="cartsummery_body">
          <div class="numbertable table-responsive">

            <?php $error = session()->getFlashdata('error');
    if ($error) {
        foreach ($error as $key => $msg) { ?>
                <div class="alert alert-danger alert-dismissable">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <?php echo $msg['error']; ?>
                </div>
            <?php }
    } ?>
            <table class="table table-striped">
              <thead class="thead-dark">
                <tr>
                  <th>Ticket</th>
                  <th>Ticket No</th>
                  <th>Price</th>
                  <th>Action</th>

                </tr>
              </thead>
              <tbody>

                <?php
$total = 0;
    foreach ($data as $d) {
        ?>
                  <tr>
                    <form action="<?php echo base_url(); ?>game/deletecartdata" method="post" onsubmit="return confirm('Do you really want to Delete the entry?');">
                      <td><?php echo $d->name; ?></td>
                      <td><?=$d->ticket_no; ?></td>
                      <td><?=$d->total_price; ?></td>
                      <td>
                        <input type="hidden" value="<?php echo $d->id; ?>" name="userId">
                        <input type="submit" class="btn btn-sm btn-danger" data-userid="<?php echo $d->id; ?>" value="Remove">
                      </td>
                    </form>
                  </tr>

                <?php
$total += $d->total_price;
    }

    ?>

                <tr>
                  <td colspan="3" style="text-align:right">
                    <b> Total Price (INR)</b>
                  </td>
                  <td colspan="2" style="text-align:left">
                    <b><?=$total; ?></b>
                  </td>
                </tr>

              </tbody>
            </table>


            <center>
              <a href="<?php echo base_url(); ?>game/confirm_order" class="btn btn-custom btn-lg" role="button" aria-pressed="true">Confirm Order</a>
            </center>
          </div>

        </div>
      </div>


    <?php
}
?>
  </div>
</div>