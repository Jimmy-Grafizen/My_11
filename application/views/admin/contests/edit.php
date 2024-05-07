<style>
.btnAdddmor{margin-top: 10px;}
th {
    display: inline-block;
    margin-left: 147px;
}
td {
    display: inline-table;
	margin-bottom:10px;    margin-left: 10px;
}
#table_I .fa-trash{
	font-size:30px;
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
                        Edit <?=$name ?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

							
                            <div class="form-group ">
                                <label for="category_id" class="control-label col-lg-2">Category name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("$tccc", "name,id", $joins = array(), $cond = array("status" => 'A',"is_deleted" => 'N',"is_beat_the_expert" => 'N',"is_private" => 'N'), $order_by = array("field"=>"name","type"=>"ASC"), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select Category";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("category_id") ? set_value("category_id") : (isset($user_detail['category_id']) ? $user_detail['category_id'] : '');
                                    echo form_dropdown('category_id', $opt, $value, 'class="form-control required" id="category_id"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="confirm_win" class="control-label col-lg-2">Confirm win <span class="red_star">*</span></label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("confirm_win") ? set_value("confirm_win") : (isset($user_detail['confirm_win']) ? $user_detail['confirm_win'] : '');
                                    $data = array(
                                        'name'      => 'confirm_win',
                                        'id'        => 'confirm_win',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                           
                            <div id="confirm_win_contest_percentagehide">
                                <label for="confirm_win_contest_percentage" class="control-label col-lg-2">Confirm win contest percentages<span class="red_star">*</span></label>
                                <div class="col-lg-5">
                                    <?php
                                    $value = set_value("confirm_win_contest_percentage") ? set_value("confirm_win_contest_percentage") : (isset($user_detail['confirm_win_contest_percentage']) ? $user_detail['confirm_win_contest_percentage'] : '');
                                    $data = array(
                                        'name' => 'confirm_win_contest_percentage',
                                        'id' => 'confirm_win_contest_percentage',
                                        'value' => $value,
                                        'maxlength' => 6,
                                        'max' => 100,
                                        'class' => 'form-control required number',
                                        'placeholder' => 'Confirm win contest percentages',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                             </div>
                             <div class="form-group ">
                                <label for="is_compression_allow" class="control-label col-lg-2">Is Compression Allow</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("is_compression_allow") ? set_value("is_compression_allow") : (isset($user_detail['is_compression_allow']) ? $user_detail['is_compression_allow'] : '');
                                    $data = array(
                                        'name'      => 'is_compression_allow',
                                        'id'        => 'is_compression_allow',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                                <label for="is_duplicate_allow" class="control-label col-lg-2">Is Duplicate Allow</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("is_duplicate_allow") ? set_value("is_duplicate_allow") : (isset($user_detail['is_duplicate_allow']) ? $user_detail['is_duplicate_allow'] : '');
                                    $data = array(
                                        'name'      => 'is_duplicate_allow',
                                        'id'        => 'is_duplicate_allow',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                         
                                <label for="duplicate_count" class="control-label col-lg-2">Duplicate count</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("duplicate_count") ? set_value("duplicate_count") : (isset($user_detail['duplicate_count']) ? $user_detail['duplicate_count'] : '');
                                    $data = array(
                                        'name'      => 'duplicate_count',
                                        'id'        => 'duplicate_count',
                                        'class'     => 'form-control number',
                                        'value' => $value,
                                        'maxlength' => 4,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="total_team" class="control-label col-lg-2"> Total Team  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = $total_team = set_value("total_team") ? set_value("total_team") : (isset($user_detail['total_team']) ? $user_detail['total_team'] : '');
                                    $data = array(
                                        'name' => 'total_team',
                                        'id' => 'total_team',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => ' Total team',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							
                            <div class="form-group ">
                                <label for="Points" class="control-label col-lg-2">Contest prices<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                <div class="col-lg-9">
                                   
								<div class="row-fluid">
									<table id="table_I" cellspacing="20" cellpadding="6">
									<tbody>
									<tr>
									<th style="margin-left: 78px;">Min</th>
									<th>Max</th>
									<th>Price</th>
									</tr>
								
									<?php 
                                    
									$contest_json = json_decode($user_detail['contest_json']);
									$per_max_p = $contest_json->per_max_p;
                               
									if( !empty($user_detail['contest_json']) && $per_max_p ){
										//print_r($per_max_p);
										$per_price = (array)$contest_json->per_price;
                                        $per_min_p = (array)$contest_json->per_min_p;
                                        $next = 0;
										foreach( $per_min_p as $key=> $val ){
									 ?>	
									 <tr id="tr_I_<?=$key?>">
										<td>
											<input class="input-medium decimalandnumbers per_min_p form-control required" alt="<?=$key?>" id="per_km_min_km_I_<?=$key?>" placeholder="Min" name="per_min_p[I][<?=$key?>]" required="required" value="<?=$val?>" readonly="" type="text" >
										</td>
										<td>
											<input class="input-medium decimalandnumbers per_max_p form-control required" id="per_km_max_km_I_<?=$key?>" placeholder="Max" name="per_max_p[I][<?=$key?>]" required="required" type="text" onfocusout="checkmaxfare(<?=$key?>,this.value,'I')" value="<?=$per_max_p[$key]?>">
										</td>
										<td>
											<input class="input-medium decimalandnumbers per_price form-control required" id="per_km_km_charge_I_<?=$key?>" placeholder="Price Charge" name="per_price[I][<?=$key?>]" required="required" type="text" value="<?=$per_price[$key]?>">
										</td>
                                        <?php $next++; if( count($per_price)>1 && count( $per_min_p ) == $next ) { ?>
                                        <td id="td_I_<?=$key?>"><a href="javascript:void(0);" onclick="removeKmCharge('I',<?=$key?>);"><i class="fa fa-trash"></i></a></td>
                                    <?php } ?>
									</tr>
									<?php } 
									} else {
										?>
									<tr id="tr_I_0">
										<td>
											<input class="input-medium decimalandnumbers per_min_p form-control required" alt="0" id="per_km_min_km_I_0" placeholder="Min" name="per_min_p[I][0]" required="required" value="1" readonly="" type="text" >
										</td>
										<td>
											<input class="input-medium decimalandnumbers per_max_p form-control required" id="per_km_max_km_I_0" placeholder="Max" name="per_max_p[I][0]" required="required" type="text" onfocusout="checkmaxfare(0,this.value,'I')">
										</td>
										<td>
											<input class="input-medium decimalandnumbers per_price form-control required" id="per_km_km_charge_I_0" placeholder="Price Charge" name="per_price[I][0]" required="required" type="text">
										</td>
										</tr>
									<?php }
										?>									
										</tbody>
										
										</table>
									
								</div>
								<a class="btn btn-primary btnAdddmor" id="add_more_I" href="javascript:void(0);">Add More</a>
                                </div>
								<div class="col-lg-3"><div class="form-group ">
                                <p><b>Price Pools</b></p>
                                <h2><b class="price_get"><?php echo(isset($user_detail['total_price']) ? $user_detail['total_price'] : 0)?></b></h2>
								
								</div></div>
                                </div>
                            </div>
                             <div class="form-group ">
                                <label for="total_price" class="control-label col-lg-2">Price Pool <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("total_price") ? set_value("total_price") : (isset($user_detail['total_price']) ? $user_detail['total_price'] : '');
                                    $data = array(
                                        'name' => 'total_price',
                                        'id' => 'total_price',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => ' Price Pool',
                                        'readOnly' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="actual_entry_fees" class="control-label col-lg-2">Entry fees <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                        $entry_fees = set_value("entry_fees") ? set_value("entry_fees") : (isset($user_detail['entry_fees']) ? $user_detail['entry_fees'] : '');
                                        $more_entry_fees = set_value("more_entry_fees") ? set_value("more_entry_fees") : (isset($user_detail['more_entry_fees']) ? $user_detail['more_entry_fees'] : '');
                                        $actual_entry_fees = set_value("actual_entry_fees") ? set_value("actual_entry_fees") : (isset($user_detail['actual_entry_fees']) ? $user_detail['actual_entry_fees'] : '');
                                    $data = array(
                                        'name' => 'actual_entry_fees',
                                        'id' => 'actual_entry_fees',
                                        'value' => $actual_entry_fees,
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => 'Entry fees',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="cash_bonus_used_type" class="control-label col-lg-2">Cash Bonus Type<span class="red_star">*</span></label>
                                <div class="col-lg-10">
            
                                    <?php
                               
                                    $opt_all = unserialize(CONTEST_CASH_BONUS_USED_TYPE);
                                    $opt = array();
                                    $opt[''] = "Please Select";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $key=>$val) {
                                            $opt[$key] = $val;
                                        }
                                    }
                                    $value = set_value("cash_bonus_used_type") ? set_value("cash_bonus_used_type") : (isset($user_detail['cash_bonus_used_type']) ? $user_detail['cash_bonus_used_type'] : '');
                                    echo form_dropdown('cash_bonus_used_type', $opt, $value, 'class="form-control required" id="cash_bonus_used_type"');
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="cash_bonus_used_value" class="control-label col-lg-2">Cash Bonus Used<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("cash_bonus_used_value") ? set_value("cash_bonus_used_value") : (isset($user_detail['cash_bonus_used_value']) ? $user_detail['cash_bonus_used_value'] : '');
                                    $data = array(
                                        'name' => 'cash_bonus_used_value',
                                        'id' => 'cash_bonus_used_value',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required number',
                                        'placeholder' => $name.' cash_bonus_used_value',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="more_entry_fees" class="control-label col-lg-2">Discount(%)<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                
                                    $data = array(
                                        'name' => 'more_entry_fees',
                                        'id' => 'more_entry_fees',
                                        'value' => $more_entry_fees,
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => 'Discount',
                                        'max' => '100',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="entry_fees" class="control-label col-lg-2">Discounted Entry fees<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php                                   
                                    $data = array(
                                        'type' => 'number',
                                        'name' => 'entry_fees',
                                        'id' => 'entry_fees',
                                        'value' => $entry_fees,
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => 'Discounted Entry fees',
                                        'readOnly' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="actual_total" class="control-label col-lg-2">Actual Total<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                 
                                    $data = array(
                                        'name' => 'actual_total',
                                        'id' => 'actual_total',
                                        'value' => ($entry_fees *$total_team ),
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => 'Actual Total',
                                        'readOnly' => true,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            
							<div class="form-group ">
                                <label for="per_user_team_allowed" class="control-label col-lg-2">Per User Team Allowed<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("per_user_team_allowed") ? set_value("per_user_team_allowed") : (isset($user_detail['per_user_team_allowed']) ? $user_detail['per_user_team_allowed'] : '');
                                    $data = array(
                                        'name' => 'per_user_team_allowed',
                                        'id' => 'per_user_team_allowed',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required decimalandnumbers',
                                        'placeholder' => 'Per User Team Allowed',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group " id="multi_team_allowed_div">
                                <label for="multi_team_allowed" class="control-label col-lg-2">Multi Team Allowed</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("multi_team_allowed") ? set_value("multi_team_allowed") : (isset($user_detail['multi_team_allowed']) ? $user_detail['multi_team_allowed'] : '');
                                    $data = array(
                                        'name'      => 'multi_team_allowed',
                                        'id'        => 'multi_team_allowed',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Save</button>
									<a class="btn btn-default" href="<?php echo HTTP_PATH . $prefixUrl; ?>">Cancel</a>
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




<script type="text/javascript">
$(document).ready(function(){

    /*****************************/

    $(document).on("keyup blur ", "input#actual_entry_fees", function(){
      var actual_entry_fees     = $(this).val();
      var more_entry_fees       = $("input#more_entry_fees").val();
      var totalDis              = ( actual_entry_fees - (( actual_entry_fees * more_entry_fees ) / 100) );
      var totalDis = totalDis.toFixed(2);
      $("input#entry_fees").val(totalDis) ;
       actualTotal();
    });


    $(document).on("keyup blur ", "input#more_entry_fees", function(){

      var more_entry_fees           =   $(this).val();
      
      if( more_entry_fees > 100 ){
            $(this).val(100);
      }
      var more_entry_fees           =   $(this).val();

      var actual_entry_fees               =   $("input#actual_entry_fees").val();
      var totalDis = ( actual_entry_fees - (( actual_entry_fees * more_entry_fees ) / 100) );
      var totalDis = totalDis.toFixed(2);
      $("input#entry_fees").val(totalDis) ;
       actualTotal();

    });

    /*****************************/

	$("#per_km_max_km_I_0").prop("readOnly", true);
	$("#per_km_km_charge_I_0").prop("readOnly", true);
				
	//var altnum = 1;
	$(document).on("click", "a[id^=add_more_]", function(){
		var id = $(this).attr('id');
		var a_data = id.split('_');
		var thisId = a_data['2'];

		
		
		var rowCount = document.getElementById('table_'+thisId).rows.length;
		//document.getElementById('td_'+thisId+'_'+altnum).outerHTML='';
		//alert(rowCount);
		altnumminus =rowCount-2;
		altnum =rowCount-1;
		newaltnum = altnum-1;
		if((document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value)>=0) ){
			document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).readOnly = true;
			document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).readOnly = true;
			//document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).readOnly = true;
			 if(Number(document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value)==total_team ) {
                document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).readOnly = false;
            }

		}else{
				alert("Value Requied For MIN ,MAX  and Price");
				return false;

		}
		//alert(newaltnum);
		
		var total_team = document.getElementById('total_team').value;
		if(Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+1 > Number(total_team)){
			//document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
			alert("You want add more than team,  so please increase total teams!");
			return false;
		}
        if(newaltnum>0){
            //document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
            $('#td_'+thisId+'_'+newaltnum).remove();
        }
		
		var html ='<tr id="tr_'+thisId+'_'+altnum+'"><td><input type="text" class="form-control required input-medium decimalandnumbers per_min_p" alt="'+altnum+'" id="per_km_min_km_'+thisId+'_'+altnum+'" placeholder="Min" name="per_min_p['+thisId+']['+altnum+']" required ></td><td><input type="text" class="form-control required input-medium decimalandnumbers per_max_p" id="per_km_max_km_'+thisId+'_'+altnum+'" placeholder="Max" name="per_max_p['+thisId+']['+altnum+']" onfocusout="checkmaxfare('+altnum+',this.value,\''+thisId+'\')" required/></td><td><input type="text" class=" form-control required input-medium decimalandnumbers per_price" id="per_km_km_charge_'+thisId+'_'+altnum+'" placeholder="Price  Charge" name="per_price['+thisId+']['+altnum+']" required readOnly/></td></tr>';

		var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
		
		$('#table_'+thisId).append(html);
		document.getElementById('per_km_min_km_'+thisId+'_'+altnum).value = Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+1;
			document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = true;
		$('#tr_'+thisId+'_'+altnum).append(htmlremove);
		
	});
	
	$(document).on("keyup", '.per_price', function(){
		CalculetTotalOfPrice();
	});
	$(document).on("keyup", '#total_team', function(){
		 var len = $(this).val();
			if (len >0) {
				$("#per_km_max_km_I_0").prop("readOnly", false).prop("readOnly", false);
				//$("#per_km_km_charge_I_0").prop("readOnly", false).prop("readOnly", false);
			}
			else{
				$("#per_km_max_km_I_0").prop("readOnly", true);
				//$("#per_km_km_charge_I_0").prop("readOnly", true);
			}
	});
	
	$(document).on("keyup", '.decimalandnumbers', function(){
		var val = $(this).val();
		if(isNaN(val)){
		 val = val.replace(/[^0-9]/g,'');
		 if(val.split('.').length>2) 
			 val =val.replace(/\.+$/,"");
		}
		$(this).val(val); 
	});
	
	
});
	

	

function removeKmCharge(thisId,altnum){
	//$("#per_km_charge_add_div_"+divid).remove();
	$('#tr_'+thisId+'_'+altnum).remove();
	altnum = altnum-1;
	if(altnum>0){
	var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
	//document.getElementById('tr_'+thisId+'_'+altnum).innerHTML+=htmlremove;
	$('#tr_'+thisId+'_'+altnum).append(htmlremove);
	}
	if(altnum == 0){
		//document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = false;
		document.getElementById('per_km_max_km_'+thisId+'_'+altnum).readOnly = false;
		
		document.getElementById('per_km_km_charge_'+thisId+'_'+altnum).readOnly = false;
	}
			CalculetTotalOfPrice();
}

function validateRange(km_range){
	var range_error = 0;
	var tempArr = km_range;
	$.each(tempArr, function(i, item){
		var newArr = km_range.splice(i, 1);
		//console.log(newArr);
	});
}

function checkmaxfare(id,value,type) {
	
		element = document.getElementById('per_km_min_km_'+type+'_'+id);
		var total_team = document.getElementById('total_team').value;
			if (typeof(element) != 'undefined' && element != null)
			{
				//alert(document.getElementById('per_km_max_km_'+type+'_'+i).value);
				elementvalue = element.value;
				if(Number(value) > Number(total_team))   {
                     alert("Max should be less than total team "+total_team);
					document.getElementById('per_km_max_km_'+type+'_'+id).value = '';

 				}
 				else if(Number(value) >= Number(elementvalue))   {
                    
 				} else{
 					document.getElementById('per_km_max_km_'+type+'_'+id).value = '';
 					alert("Max should be grater than Min ");
 					
 				}
			}

		}
		
$(document).on("keyup", "input[id^=per_km_max_km_I_]", function(){
		evID = $(this).closest('tr').attr("id");
		var gotTD1 = $('#'+evID).find('td:eq(1) input').val();
		if(gotTD1 !="" && gotTD1 !=null){
			$('#'+evID).find('td:eq(2) input').prop("readOnly", false);
		}else{
			$('#'+evID).find('td:eq(2) input').prop("readOnly", true);
		}
		CalculetTotalOfPrice();
		//console.log($(this).closest('tr'));
	});
function CalculetTotalOfPrice(){
	 var totalCls =  $(".price_get");
	 var total_price =  $("#total_price");
		 var valueArray = [];
			$('.per_price').each(function(){
				evID = $(this).closest('tr').attr("id");
				var gotTD1 = $('#'+evID).find('td:eq(0) input').val();
				var gotTD2 = $('#'+evID).find('td:eq(1) input').val();
				var getBetVeen = (parseInt(gotTD2)-parseInt(gotTD1))+1;
				//console.log(getBetVeen);
				var numberget = parseInt(this.value)*parseInt(getBetVeen);
				if (isNaN(numberget))
				{}else{
					valueArray.push(numberget);
				}
			});
		if(valueArray.length > 0){
			isNaNnum = valueArray.reduce(getSum);
			if (isNaN(isNaNnum))
			{
				totalCls.text(0);
				total_price.val(0);
			 }else{
				totalCls.text(isNaNnum);
				total_price.val(isNaNnum);
			 }
		}else{
			totalCls.text(0);
			total_price.val(0);
		}
}
function getSum(total, num) {
  return total + num;
}

function actualTotal() {
  var total_team =  $("input#total_team").val();
  var entry_fees =  $("input#entry_fees").val();
   var totalDis = (entry_fees * total_team);
   var totalDis = totalDis.toFixed(2);
  $("#actual_total").val( totalDis );
}
</script>

<script>
    $(document).ready(function(){
        if ($('#confirm_win').is(':checked')) {
            $("#confirm_win_contest_percentagehide").hide();
            $("#confirm_win_contest_percentage").val('0.00');
        } else {
            $("#confirm_win_contest_percentagehide").show();   
        }

        $(document).on('click', '#confirm_win', function() {
            if($(this).is(':checked')){
                $("#confirm_win_contest_percentagehide").hide();
                $("#confirm_win_contest_percentage").val('0.00');
            } else {
                $("#confirm_win_contest_percentagehide").show();
                $("#confirm_win_contest_percentage").val('');
            }
        });

    });
</script>