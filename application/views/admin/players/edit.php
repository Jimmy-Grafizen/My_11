<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/preview_image_upload/dist/imageuploadify.min.css" />
<style>
.imageuploadify {
     border: none; 
     min-height: 0; 
   
}
.glyphicon-removenew:before {
    position: absolute;
    bottom: 0px;
    left: 7px;
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
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><?=$name ?> Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                   
									<?php
                               
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['uniqueid']) ?$user_detail['uniqueid'].'@'.$user_detail['name']: '');
								    $optc[$user_detail['uniqueid'].'@'.$user_detail['name']] = $user_detail['name'];
                                    echo form_dropdown('name', $optc, $value, 'class="form-control required" id="player_container"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="position" class="control-label col-lg-2">Playing Role <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php                                     
                                     $opt[''] = "Please Select {$name} position";
                                    if (!empty($positions)) {
                                        foreach ($positions as $key =>$datass) {
                                            $opt[$datass] = str_ireplace("_"," ",$datass);
                                        }
                                    }
                                    $value = set_value("position") ? set_value("position") : (isset($user_detail['position']) ? $user_detail['position'] : '');
                                    echo form_dropdown('position', $opt, $value, 'class="form-control required search_fields" id="position"');
                                    unset($opt); 
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="dob" class="control-label col-lg-2">Date Of Birth<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $dob = set_value("dob") ? set_value("dob") : (isset($user_detail['dob']) ?date(DATE_FORMAT_ADMIN, strtotime( $user_detail['dob'] ) ): '');
                                    $data = array(
                                        'name' => 'dob',
                                        'id' => 'datepicker_DOB',
                                        'value' => $dob,//date('Y-m-d',$dob),
                                        'class' => 'form-control required',
                                        'placeholder' => 'Date Of Birth',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Select <?=$name ?> Images <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                  <input name="images[]" type="file" accept="image/*" multiple>
								  
								<?php
									  $opt_all = $this->main_model->cruid_select_array_order("tbl_cricket_player_galleries", "tbl_cricket_player_galleries.file_name,id", $joins = array(), $cond = array("status" => 'A',"is_deleted" => 'N',"player_id" => $user_detail['id']), $order_by = array(), $limit = '', $order_by_other = array());
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                          ?>
                                            <div class="remove-<?=$datass->id ?> imageuploadify-container" style="margin-left: 0px;margin-right: 5px;position: relative;display: inline-block;">
                                            <button data-id="<?=$datass->id ?>" type="button" class="btn btn-danger glyphicon glyphicon-remove glyphicon-removenew" style="
                                                position: absolute;
                                                width: 20px;
                                                height: 20px;
                                                top: 0px;
                                                left: 74px;
                                                bottom: 2px;
                                            "></button>
                                                      
                                            <img src="<?=PLAYER_IMAGE_THUMB_URL.$datass->file_name ?>" style="
                                                width: 100px;
                                                height: 100px;">
                                            </div>
                                        <?php
                                            }
                                        }
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
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/preview_image_upload/dist/imageuploadify.js"></script>
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/select2/select2.min.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/select2/js/select2.full.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("button.glyphicon-removenew").click(function(){
			$('.remove-'+$(this).data('id')).remove();
// alert($(this).data('id'));return falsel;
            $.ajax({
                type: 'POST',
				data:{id:$(this).data('id')},
                url: '<?=APP_URL?>admin/players/removefileplayer',
                success: function(data) {
                    //alert(data);
                    $("p").text(data);

                }
            });
   });
});

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
function formatRepo(data)
{
var arr = [];
i = 0;
 $.each(data.data, function(key, value) {	
		arr[i++]={'id':123,'text':'virat'};				
			});				
 return arr;
}
$("#player_container").val("<?=$user_detail['uniqueid'].'@'.$user_detail['name']?>").trigger("change");
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('input[type="file"]').imageuploadify();
    })
</script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script>
    $(document).ready(function() {

        $( "#datepicker_DOB" ).datepicker({            
            format: '<?= DATE_FORMAT_ADMIN_JS; ?>',
            endDate: '-16y',
            autoclose: true,
            orientation: "top",

        });
    });
</script>

