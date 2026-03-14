<div class="faq_sec">
  <div class="container">
    <div class="row">
      <div class="col-md-8 m-auto">
        <div class="faqbox">
          <h4>Announcements</h4>
          <div class="accordion" id="accordion-item">
            <?php
            $counter = 0;
            foreach ($faq as $f) {
            ?>

              <div class="accordioncard">
                <div class="accordioncard-header" id="heading<?php echo $counter; ?>">
                  <h2 class="mb-0">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#item<?php echo $counter; ?>" aria-expanded="true" aria-controls="item<?php echo $counter; ?>">
                      <?php echo $f->question; ?>
                      <span class="arrowdown"><i class="fas fa-chevron-down"></i></span>
                    </button>
                  </h2>
                </div>
                <div id="item<?php echo $counter; ?>" class="collapse <?php echo ($counter == 0) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $counter; ?>" data-parent="#accordion-item">
                  <div class="accordioncard-body">
                    <?php echo $f->answer; ?>
                  </div>
                </div>
              </div>
            <?php
              $counter++;
            }
            ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>