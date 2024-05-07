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
                        <div class="card-body">
								<ul class="nav nav-tabs">
									<li class="nav-item active"><a href="#basic-tab1" class="nav-link" data-toggle="tab">Setting</a></li>
									<li class="nav-item"><a href="#basic-tab2" class="nav-link" data-toggle="tab">Social</a></li>
									<li class="nav-item"><a href="#basic-tab3" class="nav-link" data-toggle="tab">Promotion</a></li>
									<li class="nav-item"><a href="#basic-tab4" class="nav-link" data-toggle="tab">App Update</a></li>
								</ul>
								<div class=" form">
								    <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')); ?>
    								<div class="tab-content">
    									<div class="tab-pane fade active in" id="basic-tab1">
                                                <?php
                    						//	echo "<pre>";print_r($settings);die;
                    							foreach($settings as $key =>$val){
                    							    if($val['id']==1 || $val['id']==5 || $val['id']==6 || $val['id']==7 || $val['id']==8){
                    							?>
                                                <div class="form-group " id="<?=$val['key'];?>">
                                                    <label for="se<?=$val['key'];?>" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['label']) ; ?>  <span class="red_star">*</span></label>
                                                    <div class="col-lg-10">
                                                        <?php
                                                        if(!in_array($val['key'],array('version_name','version_desc','version_desc','text_share_screen_1','text_share_screen_2','text_share_message','share_image','youtube','instagram','facebook'))){
                                                            $oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');";
                                                        }else{
                                                            $oninput = "";
                                                        }
                                                        $data = array(
                                                            'name' => 'setting['.$val['id'].']',
                                                            'id' => 'se'.$val['key'],
                                                            'value' => $val['value'],
                                                            'class' => 'form-control required',
                                                            'placeholder' => str_ireplace("_", " ", $val['key']),
                                                            "oninput"=>$oninput
                                                        );
                    
                                                        if($val['key'] =="DISCOUNTED_IMAGE"){                                      
                                                           echo'<input value="Y"  name="DISCOUNTED_IMAGE" id="DISCOUNTED_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else
                                                        if($val['key'] =="share_image"){                                      
                                                           echo'<input name="share_image" id="share_image" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else {
                                                            echo form_input($data);
                                                        }
                    
                                                        ?>
                                                    </div>
                                                </div>
                    							<?php } }?>
    									</div>
    									<div class="tab-pane fade" id="basic-tab2">
                                                <?php  
                    							foreach($settings as $key =>$val){
                    							    if($val['id']==14 || $val['id']==15 || $val['id']==16 || $val['id']==20 || $val['id']==21){
                    							?>
                                                <div class="form-group " id="<?=$val['key'];?>">
                                                    <label for="se<?=$val['key'];?>" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['label']) ; ?>  <span class="red_star">*</span></label>
                                                    <div class="col-lg-10">
                                                        <?php
                                                        if(!in_array($val['key'],array('version_name','version_desc','version_desc','text_share_screen_1','text_share_screen_2','text_share_message','share_image','youtube','instagram','facebook','telegram','twitter'))){
                                                            $oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');";
                                                        }else{
                                                            $oninput = "";
                                                        }
                                                        $data = array(
                                                            'name' => 'setting['.$val['id'].']',
                                                            'id' => 'se'.$val['key'],
                                                            'value' => $val['value'],
                                                            'class' => 'form-control required',
                                                            'placeholder' => str_ireplace("_", " ", $val['key']),
                                                            "oninput"=>$oninput
                                                        );
                    
                                                        if($val['key'] =="DISCOUNTED_IMAGE"){                                      
                                                           echo'<input value="Y"  name="DISCOUNTED_IMAGE" id="DISCOUNTED_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else
                                                        if($val['key'] =="share_image"){                                      
                                                           echo'<input name="share_image" id="share_image" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else {
                                                            echo form_input($data);
                                                        }
                    
                                                        ?>
                                                    </div>
                                                </div>
                    							<?php } }?>
    									
    									</div>
    									<div class="tab-pane fade" id="basic-tab3">
                                                <?php 
                    							foreach($settings as $key =>$val){
                    							    if($val['id']==9 || $val['id']==10 || $val['id']==11 || $val['id']==12 || $val['id']==13){
                    							?>
                                                <div class="form-group " id="<?=$val['key'];?>">
                                                    <label for="se<?=$val['key'];?>" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['label']) ; ?>  <span class="red_star">*</span></label>
                                                    <div class="col-lg-10">
                                                        <?php
                                                        if(!in_array($val['key'],array('version_name','version_desc','version_desc','text_share_screen_1','text_share_screen_2','text_share_message','share_image','youtube','instagram','facebook'))){
                                                            $oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');";
                                                        }else{
                                                            $oninput = "";
                                                        }
                                                        $data = array(
                                                            'name' => 'setting['.$val['id'].']',
                                                            'id' => 'se'.$val['key'],
                                                            'value' => $val['value'],
                                                            'class' => 'form-control required',
                                                            'placeholder' => str_ireplace("_", " ", $val['key']),
                                                            "oninput"=>$oninput
                                                        );
                    
                                                        if($val['key'] =="DISCOUNTED_IMAGE"){                                      
                                                           echo'<input value="Y"  name="DISCOUNTED_IMAGE" id="DISCOUNTED_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else
                                                        if($val['key'] =="share_image"){                                      
                                                           echo'<input name="share_image" id="share_image" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else {
                                                            echo form_input($data);
                                                        }
                    
                                                        ?>
                                                    </div>
                                                </div>
                    							<?php } }?>
    									
    									</div>
    									<div class="tab-pane fade" id="basic-tab4">
                                                <?php  
                    							foreach($settings as $key =>$val){
                    							    if($val['id']==17 || $val['id']==18 || $val['id']==19){
                    							?>
                                                <div class="form-group " id="<?=$val['key'];?>">
                                                    <label for="se<?=$val['key'];?>" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['label']) ; ?>  <span class="red_star">*</span></label>
                                                    <div class="col-lg-10">
                                                        <?php
                                                        if(!in_array($val['key'],array('version_name','version_desc','version_desc','text_share_screen_1','text_share_screen_2','text_share_message','share_image','youtube','instagram','facebook'))){
                                                            $oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');";
                                                        }else{
                                                            $oninput = "";
                                                        }
                                                        $data = array(
                                                            'name' => 'setting['.$val['id'].']',
                                                            'id' => 'se'.$val['key'],
                                                            'value' => $val['value'],
                                                            'class' => 'form-control required',
                                                            'placeholder' => str_ireplace("_", " ", $val['key']),
                                                            "oninput"=>$oninput
                                                        );
                    
                                                        if($val['key'] =="DISCOUNTED_IMAGE"){                                      
                                                           echo'<input value="Y"  name="DISCOUNTED_IMAGE" id="DISCOUNTED_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else
                                                        if($val['key'] =="share_image"){                                      
                                                           echo'<input name="share_image" id="share_image" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                                               if(isset($validation_errors)){
                                                                    echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                                                }
                                                            if($val['value']){
                                                                echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                                            }
                                                        }else {
                                                            echo form_input($data);
                                                        }
                    
                                                        ?>
                                                    </div>
                                                </div>
                    							<?php } }?>
    									
    									</div>
    								</div>
    								<div class="form-group">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button class="btn btn-danger" type="submit">Update</button>
                                            <button class="btn btn-default" type="reset">Reset</button>
                                        </div>
                                    </div>
                                <?php echo form_close(); ?>
								</div>
							</div>
                        <!--<div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')); 
							
							
							foreach($settings as $key =>$val){
							?>
                            <div class="form-group " id="<?=$val['key'];?>">
                                <label for="se<?=$val['key'];?>" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['label']) ; ?>  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    if(!in_array($val['key'],array('version_name','version_desc','version_desc','text_share_screen_1','text_share_screen_2','text_share_message','share_image','youtube','instagram','facebook'))){
                                        $oninput = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');";
                                    }else{
                                        $oninput = "";
                                    }
                                    $data = array(
                                        'name' => 'setting['.$val['id'].']',
                                        'id' => 'se'.$val['key'],
                                        'value' => $val['value'],
                                        'class' => 'form-control required',
                                        'placeholder' => str_ireplace("_", " ", $val['key']),
                                        "oninput"=>$oninput
                                    );

                                    if($val['key'] =="DISCOUNTED_IMAGE"){                                      
                                       echo'<input value="Y"  name="DISCOUNTED_IMAGE" id="DISCOUNTED_IMAGE" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                           if(isset($validation_errors)){
                                                echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                            }
                                        if($val['value']){
                                            echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
                                        }
                                    }else
                                    if($val['key'] =="share_image"){                                      
                                       echo'<input name="share_image" id="share_image" class="form-control" type="file"  accept="image/*"><p>Image dimension should be within 400*400. Allowed image extensions jpg,jpeg,png</p>';
                                           if(isset($validation_errors)){
                                                echo '<label for="image" class="error">'.$validation_errors.'</label>';
                                            }
                                        if($val['value']){
                                            echo '<img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$val['value'].'" style="margin: 10px;">';
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
                        </div> -->                   </div>
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
    $("#MAX_WITHDRAWALS").hide();
    $("#CASH_BONUS_PERCENTAGES").hide();
    $("#CONFIRM_WIN_CONTEST_PERCENTAGES").hide();
</script>