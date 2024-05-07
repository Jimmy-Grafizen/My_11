<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Update <?=$name ?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')); 
							
							
							foreach($settings as $key =>$val){
							?>
                        
							
                            <div class="form-group" id="fix_<?php echo $val['key']; ?>">
                                <label for="<?php echo $val['key'] ; ?>" class="control-label col-lg-3"><?php echo str_ireplace("_", " ", $val['key']) ; ?>  <span class="red_star">*</span></label>
                                <div class="col-lg-9">
                                    <?php
                                    
                                    $data = array(
                                        'name'  => 'setting['.$val['id'].']',
                                        'id'    => $val['key'],
                                        'value' => $val['value'],
                                        'class' => 'form-control required',
                                        'placeholder' => str_ireplace("_", " ", $val['key']),
                                        'oninput'=>"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                                    );
                                    if($val['key'] =="CONTEST_BASED"){
                                        $checked = ($val['value'] =='Y')?'checked':'';
                                       echo' <div class="col-lg-1" style="margin-left: -28px;">
                                                <input value="Y" type="checkbox" name="CONTEST_BASED" id="CONTEST_BASED" class="form-control" '.$checked.'> 
                                       </div>';
                                    } elseif($val['key'] =="REFERRAL_EARN_IMAGE"){
                                      
                                       echo'<input value="Y"  name="REFERRAL_EARN_IMAGE" id="REFERRAL_EARN_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 800 X 328. Allowed image extensions jpg,jpeg,png</p>';
                                           if(isset($validation_errors)){
                                                echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                            }
                                        if($val['value']){
                                            echo '<img src="'.REFER_EARN_IMAGE_LARGE_URL.$val['value'].'" style="margin: 10px;">';
                                        }
                                      
                                    }else {
                                        echo form_input($data);
                                    }
                                    ?>
                                </div>
                            </div>
							<?php } ?>
							
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Update</button>
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
<style>
select {
  width: 96.6%;
}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.js"></script>

<script>
$(function () { 
    $('select').multipleSelect({
	  filter: true,
	  isOpen: true,
 
     })
});


$(document).ready(function(){

if ($('#CONTEST_BASED').is(':checked')) {
    $("#fix_PERCENTAGE_OF_ENTRY_FEES").show();
} else {
    $("#fix_PERCENTAGE_OF_ENTRY_FEES").val('').hide();
    $("#PERCENTAGE_OF_ENTRY_FEES").val('');
}
    $(document).on('click', '#CONTEST_BASED', function() {
        if($(this).is(':checked')){
              $("#fix_PERCENTAGE_OF_ENTRY_FEES").show();
        } else {
            $("#fix_PERCENTAGE_OF_ENTRY_FEES").val('').hide();
            $("#PERCENTAGE_OF_ENTRY_FEES").val('');
        }
    });

});
</script>
