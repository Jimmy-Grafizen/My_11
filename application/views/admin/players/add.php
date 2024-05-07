<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/preview_image_upload/dist/imageuploadify.min.css" />
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add <?=$name ?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><?=$name ?> Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
								<select name="name" class="form-control required" id="player_container">
									<option value="">Player Name </option>
									</select>
                                </div>
								
                            </div>
							
							
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Select <?=$name ?> Images <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                  <input name="images[]" type="file" accept="image/*" multiple>
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
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/preview_image_upload/dist/imageuploadify.js"></script>
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/select2/select2.min.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/select2/js/select2.full.min.js"></script>
<script>
$( "#player_container").select2({        
    ajax: {
        url: function (params) {
            return "<?=ENTITYSPORT_PLAYER_FINDER ;?>" + params.term;
        },
        dataType: 'json',
        delay: 1,
        data: function (params) {
            return params.term;
        },
        processResults: function (data) {
            return {
                results: getresult(data) 
            };
        },
        cache: true
    },
    minimumInputLength: 2
});
function getresult(data)
{
var arr = [];
i = 0;
 $.each(data.data, function(key, value) {	
		arr[i++]={'id':value.pid+'@'+value.name,'text':value.name};				
			});				
 return arr;
} 
$(document).ready(function() {
	$('input[type="file"]').imageuploadify();
})
 </script>