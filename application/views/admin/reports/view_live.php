<style>
.form-inline .form-control {width: 100%;}
.chip {
  display: inline-block;
  padding: 0 20px;
  height: 40px;
  font-size: 16px;
  line-height: 40px;
  border-radius: 25px;
  background-color: #f1f1f1;
  margin: 0 0px 5px 0px;
  width: 100%;

}

.chip img {
  float: left;
  margin: 0 10px 0 -25px;
  height: 40px;
  width: 40px;
  border-radius: 50%;
}
.chip a {
    float: right;
    margin-top: 9px;
}
</style>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit <?=$name?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
							<?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
							<div class="form-group col-sm-6">
                                <label for="series_id" class="control-label">Series Name  <span class="red_star">*</span></label>
                                <div class="col-sm-12">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("$tbl_cricket_series", "{$tbl_cricket_series}.name,id", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select Series";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("series_id") ? set_value("series_id") : (isset($user_detail['series_id']) ? $user_detail['series_id'] : '');
                                    echo form_dropdown('series_id', $opt, $value, 'class="form-control required" id="series_id" disabled');
                                    ?>
                                </div>
                            </div>
							
                            <div class="form-group col-sm-6">
                                <label for="name" class="control-label">Match Name  <span class="red_star">*</span></label>
                                    <input type="hidden" name="permissions" id="permissions">
                                <div class="col-sm-12">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($match_detail['name']) ? $match_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Name',
										'disabled' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            
							
                            <div class="col-sm-4">
                                <label for="match_limit" class="control-label ">Customer team Limit  <span class="red_star">*</span></label>
                                    <input type="hidden" name="permissions" id="permissions">
                                <div class="">
                                    <?php
                                    $value = set_value("match_limit") ? set_value("match_limit") : (isset($user_detail['match_limit']) ? $user_detail['match_limit'] : '');
                                    $data = array(
										'type' => 'number',
                                        'name' => 'match_limit',
                                        'id' => 'match_limit',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Customer team Limit',
										'disabled' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                  <?php
                                    $close_date = set_value("close_date") ? set_value("close_date") : (isset($user_detail['close_date']) ? $user_detail['close_date'] : '');
                                ?>
                                <label for="close_date" class="control-label ">Join Date [<?php echo date(DATE_TIME_FORMAT_ADMIN,$close_date); ?>]  <span class="red_star">*</span></label>
                                <div class="">
                                    <?php
                                    $close_date = set_value("close_date") ? set_value("close_date") : (isset($user_detail['close_date']) ? $user_detail['close_date'] : '');
                                    $data = array(
										'type' => 'text',
                                        'name' => 'close_date',
                                        'id' => 'close_date',
                                        'value' => date(CLOSE_DATE_TIME_FORMAT_ADMIN,$close_date),
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Customer team Limit',
                                        'readonly ' => true,
										'disabled' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label for="match_progress" class="control-label ">Match Progress <span class="red_star">*</span></label>
                                <div class="">
								<?php
                               		$opt_all = unserialize(MATCH_PROGRESS);
                                   // $Matchopt[''] = "Please Match Progress";
                                    if (!empty($opt_all)) {
										unset($opt_all['F']);
                                        foreach ($opt_all as $key =>$datass) {
                                            $Matchopt[$key] = $datass;
                                        }
                                    }
                                    $value = set_value("match_progress") ? set_value("match_progress") : (isset($user_detail['match_progress']) ? $user_detail['match_progress'] : '');
                                    echo form_dropdown('match_progress', $Matchopt, $value, 'class="form-control required" id="match_progress"');
                                    ?>
                                </div>
                            </div>
							<div class="form-group col-sm-1">
                                <div class="col-lg-offset-2 col-lg-10" style="margin-top: 17px;">
                                    <button class="btn btn-danger" type="submit">Save</button>
                                    <!--button class="btn btn-default" type="reset">Reset</button-->
                                </div>
                            </div>
							
							
							
                            <?php echo form_close(); ?>
                        </div>
<div class="clearfix"></div>

			<div class="row ">
						
				<div id="our_rec_fetch">
				<div class="col-xs-6 center-block">
					<h4><b></b></h4><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
				</div>
				</div>
				
			</div>
				
				</div>

                </section>
            </div>
        </div>
	
        <!-- page end-->
    </section>
</section>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/css/datetimepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>
<script>
$(document).ready(function(){

        // format: 'yyyy-mm-dd hh:ii P',
    $('#close_date').datetimepicker({
		format: 'dd/mm/yyyy hh:ii',
		showMeridian:true,
		startDate:new Date(),
		autoclose: true,
    });
	
	 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_live_playes/" ?><?=$user_detail['unique_id'] ?>");
	 
	 //$("#third_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_current_playes/" ?><?=$user_detail['unique_id'] ?>");
});

function RefreshFunction() {
 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_live_playes/" ?><?=$user_detail['unique_id'] ?>");
}
</script>
<script>

    // for actions tab
    $(document).on('click', ".action-player", function(e) {
        e.preventDefault();
		var ele = $(this);
		var ielem 	 	= $(this).find('i');
		var url 		= $(this).attr("href");
		var fields = 	$(this).attr("player");
        $.post(url,
                {
                    'fields':fields,'t': 't',
                    beforeSend: function() {
						ielem.addClass("fa-spinner fa-pulse fa-fw");
                    }}, function(data) {
						ele.remove();
						 //$("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
					});
			return false;
	});
	
    // for actions tab
    $(document).on('click', ".action-list", function(e) {
        e.preventDefault();
		
		var ielem 	 	= $(this).find('i');
		var dataurlele  = $(this).attr('data_url');
        var url 		= $(this).attr("href");
		
		if($(this).hasClass("btn-success")) {		
			$(this).attr('data_url',url).attr("href",dataurlele).attr('title',"Deactivate").addClass("btn-danger").removeClass("btn-success");			
		}else{
			$(this).attr('data_url',url).attr("href",dataurlele).attr('title',"Activate").addClass("btn-success").removeClass("btn-danger");
		}
		
        $.post(url,
                {
                    't': 't',
                    beforeSend: function() {
						ielem.removeClass("fa-check");
						ielem.addClass("fa-spinner fa-pulse fa-fw");
                    }}, function(data) {
						ielem.addClass("fa-check");
						ielem.removeClass("fa-spinner fa-pulse fa-fw");
		});
        return false;

    })

</script>