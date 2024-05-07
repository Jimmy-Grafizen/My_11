<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add <?=$name?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

							
                            <div class="form-group ">
                                <label for="series_id" class="control-label col-lg-2">Series Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("$tbl_cricket_series", "{$tbl_cricket_series}.name,id", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select Series";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("series_id") ? set_value("series_id") : (isset($user_detail['series_id']) ? $user_detail['series_id'] : '');
                                    echo form_dropdown('series_id', $opt, $value, 'class="form-control required" id="country"');
                                    ?>
                                </div>
                            </div>
							
                            <div class="form-group ">
                                <label for="matche_data" class="control-label col-lg-2"><?=$names?>  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<select name="matche_data" class="form-control required valid" id="metch_container">
									<option value="">Please select Match </option>
									</select>
                                   
                                </div>
                            </div>
							
							
                            <div class="form-group">
                                <label for="match_limit" class="control-label col-sm-2">Customer team Limit<span class="red_star">*</span></label>
                                    <input type="hidden" name="permissions" id="permissions">
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("match_limit") ? set_value("match_limit") : (isset($user_detail['match_limit']) ? $user_detail['match_limit'] : '');
                                    $data = array(
										'type' => 'number',
                                        'name' => 'match_limit',
                                        'id' => 'match_limit',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Customer team Limit ',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Save</button>
                                    <button class="btn btn-default" type="reset">Reset</button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>

                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/select2/select2.min.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/select2/js/select2.full.min.js"></script>


<script>
function matchesFunc(){	
		var container = $("#metch_container");
		$.ajax({
		 	type: 'GET', 
		 	url: '<?=base_url($prefixUrl.'/get_matches');?>', 
		 	dataType: 'json',
		 	success: function(data) {
				if(!data.hasOwnProperty("error") ){
		 		$.each(data.matches, function(key, value) {
					if(value.squad== true && value.matchStarted == false){
						container.append($("<option></option>")
						.attr("value",JSON.stringify(value))
						.text(value['team-1'] +' vs ' + value['team-2']));
					}
		 		});				
			 	container.select2();
			  }
		 	}
		});
             
	  }    
$(document).ready(matchesFunc);
</script>