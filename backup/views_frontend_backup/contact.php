<div class="contactus_sec">
  <div class="container">
    <h2 class="mb-5 ">
      <center>Contact Us</center>
    </h2>
    <div class="row">
      <div class="col-md-6">

        <div class="maps">

          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28418.214686218093!2d93.59632714185135!3d27.084565710111836!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3744009342853ecd%3A0xdeec075656369e83!2sItanagar!5e0!3m2!1sen!2sin!4v1674896115558!5m2!1sen!2sin" width="600" height="350" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
        </div>
        <address>
        office floor,building no.2, Akashdeep Shopping complex,<br />
        Itanagar(A.P)- 791111,<br />
          itanagarchoice@gmail.com<br />
          +91 76299 08916
        </address>
      </div>
      <div class="col-md-6">


        <div class="contact_form">
          <form method="post" action="">

            <?php $success = session()->getFlashdata('success');
            if ($success) {
            ?>
              <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $success; ?>
              </div>
            <?php } ?>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="exampleFormControlInput1">Your Name</label>
                  <input type="text" class="form-control" name="name" id="" placeholder="Enter your name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="exampleFormControlInput1">Email</label>
                  <input required type="email" class="form-control" id="" placeholder="name@example.com" name="email">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="exampleFormControlTextarea1">Your Message *</label>
              <textarea required class="form-control" id="exampleFormControlTextarea1" rows="5" name="message"></textarea>
            </div>
            <input class="btn btn-warning" type="submit" name="submit" value="Send Message">


          </form>
        </div>
      </div>
    </div>
  </div>
</div>