<link href="<?php echo HTTP_PATH; ?>assets/dashboardassets/bootstrap.min.css" rel="stylesheet" />
<link href="<?php echo HTTP_PATH; ?>assets/dashboardassets/style.css" rel="stylesheet" />
<?php /****Datepicker KK*******/ ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>


<section id="main-content">
    <section class="wrapper">
        <!--state overview start-->
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                      <?php 
                        $loginUser = $this->session->userdata('loginUser');
                        if( $loginUser['parent_id'] =="0" ) { ?>
                      <div class="page-body">
                        
                        <div class="panel-body" style="padding-top: 0;">
                          
                           <?php echo form_open('', array('class' => "", 'id' => 'search_form')); ?>
                           <div class="row">
                           
                           <div class="form-group col-sm-2"  style="padding:0px;">
                            <label for="from_date" class="control-label" style="font-size: 14px;">Date From</label>
                              <?php
                                 $data = array(
                                     'name' => 'from_date',
                                     'id' => 'datepicker12',
                                     'value' => date(DATE_FORMAT_ADMIN, strtotime('-1 month')),
                                     'class' => 'required default-date-picker search_fields form-control datepicker',
                                     'placeholder' => 'Date From',
                                     'readonly' => true,
                                     'style'=>"z-index: inherit;background-color: #fff !important;cursor: pointer;",
                                 );
                                 echo form_input($data);
                                 ?>
                           </div>
                           <div class="form-group col-sm-2 match_datespan">
                              <label for="to_date" class="control-label" style="font-size: 14px;">Date To</label>

                              <?php
                                 $data = array(
                                     'name' => 'to_date',
                                     'id' => 'datepickerto',
                                     'value' => date(DATE_FORMAT_ADMIN, time()),
                                     'class' => 'required default-date-picker search_fields form-control datepicker',
                                     'placeholder' => 'Date To',
                                     'readonly' => true,
                                     'style'=>"z-index: inherit;background-color: #fff !important;cursor: pointer;",
                                 );
                                 echo form_input($data);
                                 ?>
                           </div>
                           <div class="form-group col-sm-2">
                            <label style="margin-top: 40px;"></label>
                             <button type="submit" class="btn btn-success" style="padding-top: 5px;padding-bottom: 8px;">Search</button>
                           <!--button type="reset" class="btn btn-danger">Reset</button-->
                          </div>
                          </div>
                           <?php echo form_close(); 
                                $from_date  =   date('Y-m-d', strtotime('-1 month'));
                                $to_date    =   date('Y-m-d', time());
                           ?>
                        </div>   

                            <div class="row">
                            
                             <div class="col-md-12">
                             <h2>Dashboard</h2>
                             
                             </div>
                                <!-- task, page, download counter  start -->
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color1">
                                        <a target="_blank" href="<?= base_url('/admin/customers/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-group"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="total_customers"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">No. Of User Register</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6" hide>
                                    <div class="card bg-c-yellow update-card color2">
                                        <a target="_blank" href="<?= base_url('/admin/customers/pan_requested'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-credit-card"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="pan_pending_approved"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">No. Of PAN Requested</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color3">
                                        <a target="_blank" href="<?= base_url('/admin/customers/verified_pan'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-files-o"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="pan_approved"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Verified PAN</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color4">
                                        <a target="_blank" href="<?= base_url('/admin/customers/pending_pan'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-id-card"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="pan_pending"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Pending PAN Users</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 hide">
                                    <div class="card bg-c-yellow update-card color5">
                                        <a target="_blank" href="<?= base_url('/admin/customers/bank_requested'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-area-chart"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="bank_pending_approved"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Bank Verification Request</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color6">
                                        <a target="_blank" href="<?= base_url('/admin/customers/verified_bank'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-film"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="bank_approved"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Verified Bank</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color7">
                                        <a target="_blank" href="<?= base_url('/admin/customers/document_approval_pending'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-bars"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="bank_pending"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Pending Bank Users </h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>

                                  <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-green update-card color8">
                                        <a target="_blank" href="<?= base_url('admin/customers/pending_withdrawals'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-exchange"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="withdraw_pending"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0" >Pending Withdrawal Request</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                  <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-green update-card color9">
                                        <a target="_blank" href="<?= base_url('admin/reports/withdrawals'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-table"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="withdraw_approved"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0" >Verified Withdrawal Request</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                  <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-green update-card color10">
                                        <a target="_blank" href="<?= base_url('admin/customers/withdrawals'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-money"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="withdraw_approved_amount"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0" >Total Withdrawal Amount</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                  <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-green update-card color11">
                                        <a target="_blank" href="<?= base_url('admin/reports/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-check-square"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="matche_counters_TNM"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0" >Total Number Of Matches</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-yellow update-card color12">
                                        <a target="_blank" href="<?= base_url('/admin/matches/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                <div class="col-3 createkk">
                                                  <i class="fa fa-rocket"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="matche_counters_F"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom"></i></h2>
                                                    <h6 class="text-white m-b-0">Upcoming Matches</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-green update-card color13">
                                        <a target="_blank" href="<?= base_url('admin/matches/live'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-paste"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="matche_counters_L"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Live Matches</h6>
                                                </div>
                                               
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-pink update-card color14">
                                        <a target="_blank" href="<?= base_url('admin/matches/completed'); ?>?mp=r">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-check-square"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="matche_counters_R"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Completed Matches</h6>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-lite-green update-card color15">
                                        <a target="_blank" href="<?= base_url('admin/matches/completed'); ?>?mp=ab">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-check-square"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="matche_counters_AB"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Abandoned Matches</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <!-- task, page, download counter  end -->
 
                                    <div class="col-xl-3 col-md-6 hide">
                                    <div class="card bg-c-yellow update-card color16">
                                        <a target="_blank" hreff="<?= base_url('/admin/customers/document_approval_pending'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-plus"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner"  id="pending_winner_declare"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Pending Winner Declare</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-pink update-card color17">
                                        <a target="_blank" href="<?= base_url('/admin/team_crickets/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-clock-o"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="total_team"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Total Number Of Teams</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-pink update-card color18">
                                        <a target="_blank" href="<?= base_url('/admin/players/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-user-circle-o"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="total_players"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Total Number Of Players</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-pink update-card color19">
                                        <a target="_blank" href="<?= base_url('/admin/contests/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-signal"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="total_contests"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Global Contest</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card bg-c-pink update-card color20">
                                        <a target="_blank" href="<?= base_url('/admin/reports/index'); ?>?come=dash">
                                        <div class="card-block">
                                            <div class="row align-items-end">
                                                 <div class="col-3 createkk">
                                                  <i class="fa fa-group"></i>
                                                </div>
                                                <div class="col-9">
                                                    <h2 class="text-white loading_spinner" id="ct_earnings"><i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i></h2>
                                                    <h6 class="text-white m-b-0">Total Earning</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div>
                                </div>
                               
                                <!--  sale analytics start -->
                                <div class="col-xl-9 col-md-12" style="display:none;">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Earning Analytics</h5>
                                            <span class="text-muted">Total Matches (<b id="ct_total_matches"></b>)</span>
                                            <div class="card-header-right">
                                            	<table class="show_totals">
                                            		<tr>
                                            			<th>
                                            				<h5>Credited</h5>
                                            			</th>
                                            			<th>
                                            				<h5>Debited</h5>
                                            			</th>
                                                  <th>
                                                    <h5>Refund</h5>
                                                  </th>
                                            			<th>
                                            				<h5>Earnings</h5>
                                            			</th>
                                                  <th>
                                                    <h5>Tax</h5>
                                                  </th>
                                            		</tr>
                                            		<tr>
                                            			<td><i class="fa fa-rupee m-0"></i><b id="ct_total_cr" class="m-r-10"></b></td>
                                            			<td><i class="fa fa-rupee m-0"></i><b id="ct_total_dr" class="m-r-10"></b></td>
                                                  <td><i class="fa fa-rupee m-0"></i><b id="ct_total_ra" class="m-r-10"></b></td>
                                            			<td><i class="fa fa-rupee m-0"></i><b id="ct_earnings" class="m-r-10"></b></td>
                                                  <td><i class="fa fa-rupee m-0"></i><b id="ct_total_ta" class="m-r-10"></b></td>
                                            		</tr>
                                            	</table>   
                                            </div>
                                        </div>
                                        <div class="card-block">
                                            <div id="chartdiv"></div>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <!--  sale analytics end -->

                                <div class="col-xl-6 col-md-12" id="Upcomming-content" style="display:none">
                                    
                                </div>

                                <div class="col-xl-6 col-md-12" id="Live-content" style="display:none">
                                    
                                </div>

                            </div>
                      </div>
                       <?php } else{ ?>
                            <div class="" style="text-align: center;top: 3vh;position: relative; ">
                              <!--<img src="<?php //echo HTTP_PATH ?>img/center-logo.png" />-->
                            </div>
                       <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<!-- Resources -->
<script src="<?php echo HTTP_PATH; ?>assets/dashboard/amcharts/core.js"></script>
<script src="<?php echo HTTP_PATH; ?>assets/dashboard/amcharts/charts.js"></script>
<script src="<?php echo HTTP_PATH; ?>assets/dashboard/amcharts/animated.js"></script>

<script type="text/javascript">

    $(document).ready(function() {
        $('.loading_spinner').html('<i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i>');
       // for search results
        $('#search_form').submit(function(event)
        {
            // Stop full page load
            event.preventDefault();
                ajaxFormfilter();
        });


    });
      window.onload = function(){
            ajaxFormfilter();
    };
    
      //<!-- Chart code -->
      // Themes begin
      am4core.useTheme(am4themes_animated);
      // Themes end
      // Create chart instance
      var chart = am4core.create("chartdiv", am4charts.XYChart);
      // Increase contrast by taking evey second color
      chart.colors.step = 2;
      
  function ajaxFormfilter(){
        var fields = $('.search_fields').serializeArray();
        //alert(JSON.stringify(fields));return false;
        $.post('<?php echo base_url("admin/home/dashboard_counters"); ?>',
                {
                    fields: fields, 't': 't',
                    datatype:"JSON",
                    beforeSend: function() {
                        $('.loading_spinner').html('<i class="fa fa-spinner fa-pulse fa-1x fa-fw margin-bottom "></i>');
                        
                    }}, function(data) {    
                    
                    $.each(data.matche_counters, function( k, v ) {
                       // alert( "Key: " + k + ", Value: " + v );
                        $("#matche_counters_"+ k).text(v);
                    });            
                      
                    $.each(data.customers, function( k, v ) {
                       // alert( "Key: " + k + ", Value: " + v );
                        $("#"+ k).text(v);
                    });            
                      
                 });

        //Upcomming Matches Listing
        /*    $.post('<?php echo base_url("admin/matches/ajax_index"); ?>',
            {
                fields: fields, 't': 't',from_veiw:'dashboard',
                beforeSend: function() {
                    $('#loading-image').show();
                }}, function(data) {
                    $('#Upcomming-content').html(data);
                    $('#loading-image').hide();
            });*/

        //Live  Matches Listing
        /*    $.post('<?php echo base_url("admin/matches/ajax_live"); ?>',
            {
                fields: fields, 't': 't',from_veiw:'dashboard',
                beforeSend: function() {
                    $('#loading-image').show();
                }}, function(data) {
                    $('#Live-content').html(data);
                    $('#loading-image').hide();
            });*/

        //<!-- Chart Data fill using ajax -->
            $.post('<?php echo base_url("admin/reports/ajax_index_earnings"); ?>',
            {
                fields: fields, 't': 't',from_veiw:'dashboard',
                beforeSend: function() {
                    //$('#loading-image').show();
                }}, function(res) {
                    // Add data in chart
                    //chart.data = generateChartData(res.data);

                     $.each(res.totals, function( k, v ) {
                            $("#ct_"+ k).text(v);
                        });      
                    //$('#loading-image').hide();
            });
        $('.loading_spinner').html('');
       
  }

    // Create axes
    var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 50;
    // Create series
    function createAxisAndSeries(field, name, opposite, bullet) {
      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      
      var series = chart.series.push(new am4charts.LineSeries());
      series.dataFields.valueY = field;
      series.dataFields.dateX = "date";
      series.strokeWidth = 2;
      series.yAxis = valueAxis;
      series.name = name;
      series.tooltipText = "{name}: [bold]{valueY}[/]";
      series.tensionX = 0.8;
      
      var interfaceColors = new am4core.InterfaceColorSet();
      
      switch(bullet) {
        case "triangle":
          var bullet = series.bullets.push(new am4charts.Bullet());
          bullet.width = 12;
          bullet.height = 12;
          bullet.horizontalCenter = "middle";
          bullet.verticalCenter = "middle";
          
          var triangle = bullet.createChild(am4core.Triangle);
          triangle.stroke = interfaceColors.getFor("background");
          triangle.strokeWidth = 2;
          triangle.direction = "top";
          triangle.width = 12;
          triangle.height = 12;
          break;
        case "rectangle":
          var bullet = series.bullets.push(new am4charts.Bullet());
          bullet.width = 10;
          bullet.height = 10;
          bullet.horizontalCenter = "middle";
          bullet.verticalCenter = "middle";
          
          var rectangle = bullet.createChild(am4core.Rectangle);
          rectangle.stroke = interfaceColors.getFor("background");
          rectangle.strokeWidth = 2;
          rectangle.width = 10;
          rectangle.height = 10;
          break;
        default:
          var bullet = series.bullets.push(new am4charts.CircleBullet());
          bullet.circle.stroke = interfaceColors.getFor("background");
          bullet.circle.strokeWidth = 2;
          break;
      }
      
      valueAxis.renderer.line.strokeOpacity = 1;
      valueAxis.renderer.line.strokeWidth = 2;
      valueAxis.renderer.line.stroke = series.stroke;
      valueAxis.renderer.labels.template.fill = series.stroke;
      valueAxis.renderer.opposite = opposite;
      valueAxis.renderer.grid.template.disabled = true;
    }

    createAxisAndSeries("Credited", "Credited", false, "circle");
    createAxisAndSeries("Debited", "Debited", true, "triangle");
    createAxisAndSeries("Earnings", "Earnings", true, "rectangle");

    // Add legend
    chart.legend = new am4charts.Legend();

    // Add cursor
    chart.cursor = new am4charts.XYCursor();

    // generate data
    function generateChartData(datas) {
      var chartData = [];
      //var datas = [{"date":"1553351400","Credited":"0","Debited":"0","Earnings":0},{"date":"1553437800","Credited":"1223","Debited":"0","Earnings":1223},{"date":"1553610600","Credited":"5033","Debited":"0","Earnings":5033}];
      for (var i = 0; i < datas.length; i++) {
       
        var newDate = new Date(datas[i]['date']*1000);

        chartData.push({
          date: newDate,
          Credited: datas[i]['Credited'],
          Debited: datas[i]['Debited'],
          Earnings: datas[i]['Earnings']
        });
      }

      return chartData;
    }

</script>

<!-- HTML -->
