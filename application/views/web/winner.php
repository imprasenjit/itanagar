<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


  <style>

.numberdegits {
    margin: 0;
    padding: 0;
    display: inline-flex;
}
.numberdegits li {
    display: inline-block;
    margin-right: 5px;
    background: #01B623;
    color: #fff;
    width: 40px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border-radius: 50%;
    font-weight: 500;
}
  </style>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Winner History
      </h1>
    </section>
    
    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
              <div class="box">


              <div class="box-header">
                    <h3 class="box-title">Winner Amount - $ <?php echo $amount->sum; ?></h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>web/winner" method="POST" id="searchList">
                            <div class="input-group">
                              <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search by User"/>
                              <div class="input-group-btn">
                                <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                              </div>
                            </div>
                        </form>
                    </div>
                </div><!-- /.box-header -->


                <div class="box-body table-responsive no-padding">
                <?php if(!empty($userRecords))
                    { ?>
                  <table class="table table-hover">
                    <tr>
                          <th>Order No.</th>
                          <th>User</th>
                          <th>Lottery Date</th>
                          <th>Game</th>
                          <th width="20%">Ball Combination</th>
                          <th>Price</th>
                          <th>Payment Type</th>
                          <th>Winning Prize</th>
                          
                          <th>Trancaction Id</th>
                          <th>Confirm Date</th>


                    </tr>
                    <?php
                    
                        
                      $c = 1;
                        foreach($userRecords as $ms)
                        {
                    ?>
                    <tr>
                      
                    <td><?= $ms->id ?></td>
                    <td><?= $ms->uname?></a></td>
                          <td><?= date("M d, Y",strtotime($ms->date));?></td>
                          <td><?= $ms->name?></td>
                          
                          <td>
                          <ul class="numberdegits">
                            <li><?php echo $ms->white1; ?></li>
                            <li ><?php echo $ms->white2; ?></li>
                            <li><?php echo $ms->white3; ?></li>
                            <li><?php echo $ms->white4; ?></li>
                            <li><?php echo $ms->white5; ?></li>
                            <?php
                            if($ms->white6!=""){
                              ?>
                            <li><?php echo $ms->white6; ?></li>
                              <?php
                            }
                            ?>
                            <li style="background:yellow;color:black"><?php echo $ms->yellow1; ?></li>

                            <?php
                            if($ms->yellow2!=""){
                              ?>
                            <li style="background:yellow;color:black"><?php echo $ms->yellow2; ?></li>
                              <?php
                            }
                            ?>
                          </ul></td>
                          
                          <td><?= $ms->total_price?></td>
                          
                          
                          <td><?php echo ($ms->paid_type=="0") ? "Wallet" :  $ms->paid_type ;?></td>


                          <td><?= $ms->prize; ?></td>
                          <td><?= $ms->transaction_id; ?></td>
                          
                          
                          
                          <td><?= date("M d, Y h:i a",strtotime($ms->createdAt));?></td>

                        
                    </tr>
                    <?php
                    $c++;
                        }
                    
                    ?>
                  </table>
                  <?php 
                    }
                    else{
                        ?>
                        <div class="box-footer clearfix">
                    No Date Availables
                </div>
                        <?php
                    }
                  ?>
                </div><!-- /.box-body -->
                <div class="box-footer clearfix">
                    <?php echo $this->pagination->create_links(); ?>
                </div>
              </div><!-- /.box -->
            </div>
        </div>



</section>
</div>


<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('ul.pagination li a').click(function (e) {
            e.preventDefault();            
            var link = jQuery(this).get(0).href;            
            var value = link.substring(link.lastIndexOf('/') + 1);
            // alert(link+value );
            jQuery("#searchList").attr("action", baseURL + "web/winner/" + value);
            jQuery("#searchList").submit();
        });
    });
</script>