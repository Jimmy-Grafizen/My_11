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
                        
							
                            <div class="form-group ">
                                <label for="max_player" class="control-label col-lg-2"><?php echo str_ireplace("_", " ", $val['key']) ; ?>  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    
                                    $data = array(
                                        'name' => 'setting['.$val['id'].']',
                                        'id' => 'max_player',
                                        'value' => $val['value'],
                                        'class' => 'form-control required number',
                                        'placeholder' => str_ireplace("_", " ", $val['key']),
                                    );
                                    echo form_input($data);
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
</script>