<div class="row">
   <div class="col-lg-12">
      <h4 style="text-align: center;" class="hide"><b>Price Pool<b></h4>
      <div class="col-lg-12 hide">
         <h4 style="text-align: center;">&#8377; <?=$contents['total_price'] ?></h4>
      </div>
      <div class="row-fluid">
         <?php 
            if( !empty($contents['contest_json']) ){
            	$contest_json = json_decode($contents['contest_json']);
            	$per_max_p = $contest_json->per_max_p;
            	//print_r($per_max_p);
            	$per_price = $contest_json->per_price;
            	foreach($contest_json->per_min_p as $key=> $val){
             ?>	
         <div id="___<?=$key?>___" class="row chip block_of_player">
            <div class="col-lg-6">
               <p>Rank: <?php echo ($val==$per_max_p[$key]) ? $per_max_p[$key]: $val." - ".$per_max_p[$key];?></p>
            </div>
            <div class="col-lg-2 col-sm-offset-4">
               <p>&#8377; <?=$per_price[$key]?></p>
            </div>
         </div>
         <?php }
            }
            ?>
      </div>
   </div>
</div>

<?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form col-lg-12', 'id' => 'myformConstetst','inputthis'=>"views_player_lisetnow_".$contents['id'],)); ?>

<div class="form-group ">
    <label for="total_team" class="control-label col-lg-2"> Total Team  <span class="red_star">*</span></label>
    <div class="col-lg-10">
        <?php
        $value = set_value("total_team") ? set_value("total_team") : (isset($contents['total_team']) ? $contents['total_team'] : '');
        $data = array(
            'name' => 'total_team',
            'id' => 'total_team',
            'value' => $value,
            'maxlength' => 50,
            'class' => 'form-control required decimalandnumbers number',
            'placeholder' => ' Total team',
            'required' => true,
        );
        echo form_input($data);
        ?>
    </div>
</div>
<div class="form-group ">
    <label for="max_entry_fees" class="control-label col-lg-2">Max Entry Fees  <span class="red_star">*</span></label>
    <div class="col-lg-10">
        <?php
        $value = set_value("max_entry_fees") ? set_value("max_entry_fees") : (isset($contents['max_entry_fees']) ? $contents['max_entry_fees'] : '');
        $data = array(
            'name' => 'max_entry_fees',
            'id' => 'max_entry_fees',
            'value' => $value,
            'maxlength' => 50,
            'class' => 'form-control required decimalandnumbers number',
            'placeholder' => 'Entry fee is less than or equal to max entry fee.',
            'required' => true,
        );
        echo form_input($data);
        ?>
    </div>
</div>

<div class="form-group ">
    <label for="actual_entry_fees" class="control-label col-lg-2">Entry fees <span class="red_star">*</span></label>
    <div class="col-lg-10">
        <?php
            $entry_fees = set_value("entry_fees") ? set_value("entry_fees") : (isset($contents['entry_fees']) ? $contents['entry_fees'] : '');
            $more_entry_fees = set_value("more_entry_fees") ? set_value("more_entry_fees") : (isset($contents['more_entry_fees']) ? $contents['more_entry_fees'] : '');
            $actual_entry_fees = set_value("actual_entry_fees") ? set_value("actual_entry_fees") : (isset($contents['actual_entry_fees']) ? $contents['actual_entry_fees'] : '');

        $data = array(
            'name' => 'actual_entry_fees',
            'id' => 'actual_entry_fees',
            'value' => $actual_entry_fees,
            'class' => 'form-control required decimalandnumbers',
            'placeholder' => 'Entry fees',
            'required' => true,
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
            'required' => true,
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
   <label for="Points" class="control-label col-lg-2">Contest prices<span class="red_star">*</span></label>
   <div class="col-lg-10">
      <div class="col-lg-9">
         <div class="row-fluid">
            <table id="table_I" cellspacing="20" cellpadding="6">
               <tbody>
                  <tr>
                     <th style="margin-left: 78px;">Min</th>
                     <th>Max</th>
                     <th>Entry Fee Multiplier</th>
                     <th>Price</th>
                  </tr>
                  <?php 
                     if( !empty($contents['contest_json']) ){
                     	$contest_json = json_decode($contents['contest_json']);
                     	$per_max_p = $contest_json->per_max_p;
                     	//print_r($per_max_p);
                     	$per_price = $contest_json->per_price;
                     	$endarray = (array) $contest_json->per_min_p;
                     	$endgnd = count($contest_json->per_min_p)-1;

                     	foreach($contest_json->per_min_p as $key=> $val){
                      ?>	
                  <tr id="tr_I_<?=$key?>">
                     <td>
                        <input class="input-medium decimalandnumbers per_min_p form-control required number" alt="<?=$key?>" id="per_km_min_km_I_<?=$key?>" placeholder="Min" name="per_min_p[I][<?=$key?>]" required="required" value="<?=$val?>" readonly="" type="text" >
                     </td>
                     <td>
                        <input class="input-medium decimalandnumbers per_max_p form-control required number" id="per_km_max_km_I_<?=$key?>" placeholder="Max" name="per_max_p[I][<?=$key?>]" required="required" type="text" onfocusout="checkmaxfare(<?=$key?>,this.value,'I')" value="<?=$per_max_p[$key]?>" <?php if($endgnd !=$key){ echo "readonly"; } ?> readonly>
                     </td>

                      <td>
                        <?php
                            $value = set_value("entry_fee_multiplier") ? set_value("entry_fee_multiplier") : (isset($contents['entry_fee_multiplier']) ? $contents['entry_fee_multiplier'] : '');
                            $data = array(
                                'name' => 'entry_fee_multiplier',
                                'id' => 'entry_fee_multiplier',
                                'value' => $value,
                                'maxlength' => 50,
                                'required' => true,
                                'class' => 'form-control required decimalandnumbers number',
                                'placeholder' => 'Enter Entry Fee Multiplier',
                            );
                            echo form_input($data);
                        ?>
                      </td>
                       <td>
                        <input class="input-medium decimalandnumbers per_price form-control required number" id="per_km_km_charge_I_<?=$key?>" placeholder="Price Charge" name="per_price[I][<?=$key?>]" required="required" type="text" value="<?=$per_price[$key]?>" readonly >
                     </td>
                    <?php if($key>0) { ?>
                     <td id="td_I_<?=$key?>">
                     	<a href="javascript:void(0);" onclick="removeKmCharge('I',<?=$key?>);">
                     		<i class="fa fa-trash"></i>
                     	</a>
                     </td>
                    <?php } ?>
                  </tr>
                  <?php } 
                     } else {
                     	?>
                  <tr id="tr_I_0">
                     <td>
                        <input class="input-medium decimalandnumbers per_min_p form-control required number" alt="0" id="per_km_min_km_I_0" placeholder="Min" name="per_min_p[I][0]" required="required" value="1" readonly="" type="text" >
                     </td>
                     <td>
                        <input class="input-medium decimalandnumbers per_max_p form-control required number" id="per_km_max_km_I_0" placeholder="Max" name="per_max_p[I][0]" required="required" type="text" onfocusout="checkmaxfare(0,this.value,'I')">
                     </td>
                     <td>
                        <input class="input-medium decimalandnumbers per_price form-control required number" id="per_km_km_charge_I_0" placeholder="Price Charge" name="per_price[I][0]" required="required" type="text">
                     </td>
                  </tr>
                  <?php }
                     ?>									
               </tbody>
            </table>
         </div>
        <?php /* <a class="btn btn-primary btnAdddmor" id="add_more_I" href="javascript:void(0);">Add More</a> */ ?>
      </div>
      <div class="col-lg-3 hide">
         <div class="form-group ">
            <p><b>Price Pools</b></p>
            <h2><b class="price_get"><?php echo(isset($contents['total_price']) ? $contents['total_price'] : 0)?></b></h2>
         </div>
      </div>
   </div>
</div>

	<div class="form-group hide">
	    <label for="total_price" class="control-label col-lg-2">Price Pool <span class="red_star">*</span></label>
	    <div class="col-lg-10">
	        <?php
	        $value = set_value("total_price") ? set_value("total_price") : (isset($contents['total_price']) ? $contents['total_price'] : '');
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
    <div class="modal-footer">
       <div class="col-xs-12 col-sm-12 col-md-12 text-center">
          <button type="submit" class="btn btn-primary oksave" inputthis="views_player_lisetnow_<?=$contents['id']?>">Ok</button>
          <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
       </div>
    </div>

</form>