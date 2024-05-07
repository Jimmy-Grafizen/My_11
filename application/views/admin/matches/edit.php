<style>.chip a{float:right;margin-top:9px}</style>
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
                            <div class="form-group col-sm-4">
                                <label for="series_id" class="control-label editlabel">Series Name  <span class="red_star">*</span></label>
                                <div class="col-sm-12">
            
                                    <?php
                                    $series_uniqueid = null;
                                    $value = set_value("series_id") ? set_value("series_id") : (isset($user_detail['series_id']) ? $user_detail['series_id'] : '');
                                    $series_id = $value;

                                      $opt_all = $this->main_model->cruid_select_array_order("$tbl_cricket_series", "{$tbl_cricket_series}.name,id,uniqueid", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
                                      
                                    $opt[''] = "Please Select Series";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                            if($series_id == $datass->id){
                                                $series_uniqueid = $datass->uniqueid;
                                            }
                                        }
                                    }
                                    echo form_dropdown('series_id', $opt, $value, 'class="form-control required" id="series_id"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                            <label for="name" class="control-label editlabel">Match Name<span class="red_star">*</span></label>
                                    
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
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="match_limit" class="control-label editlabel">Customer team Limit  <span class="red_star">*</span></label>
                                <div class="col-sm-12">
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
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <?php
                                    $match_date = set_value("match_date") ? set_value("match_date") : (isset($user_detail['match_date']) ? $user_detail['match_date'] : '');
                                ?>
                                <label for="match_date" class="control-label editlabel">Match Date [<?php echo date(DATE_TIME_FORMAT_ADMIN,$match_date); ?>]  <span class="red_star">*</span></label>
                                <div class="col-sm-12">
                                    <?php
                                    $match_date = set_value("match_date") ? set_value("match_date") : (isset($user_detail['match_date']) ? $user_detail['match_date'] : '');
                                    $data = array(
										'type' => 'text',
                                        'name' => 'match_date',
                                        'id' => 'match_date',
                                        'value' => date(CLOSE_DATE_TIME_FORMAT_ADMIN,$match_date),
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'readonly ' => true,
                                        'style'=>"cursor: unset;background-color: #fff;"
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-3">
                                <?php
                                    $close_date = set_value("close_date") ? set_value("close_date") : (isset($user_detail['close_date']) ? $user_detail['close_date'] : '');
                                ?>
                                <label for="close_date" class="control-label editlabel">Join Date [<?php echo date(DATE_TIME_FORMAT_ADMIN,$close_date); ?>]  <span class="red_star">*</span></label>
                                <div class="col-sm-12">
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
                                        'style'=>"cursor: unset;background-color: #fff;"
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="match_progress" class="control-label editlabel">Match Progress <span class="red_star">*</span></label>
                                <div class="col-sm-12">
								<?php
                               		$opt_all = unserialize(MATCH_PROGRESS);
                                    $Matchopt[''] = "Please Match Progress";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $key =>$datass) {
                                            $Matchopt[$key] = $datass;
                                        }
                                    }
                                     unset($Matchopt['R']);
                                    $value = set_value("match_progress") ? set_value("match_progress") : (isset($user_detail['match_progress']) ? $user_detail['match_progress'] : '');
                                    echo form_dropdown('match_progress', $Matchopt, $value, 'class="form-control required" id="match_progress"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="image" class="control-label">Image<sub> 800*328. jpg,jpeg,png</sub></label>
                                <div class="col-sm-12">
                                    <?php
                                    $value = set_value("image") ? set_value("image") : (isset($user_detail['image']) ? $user_detail['image'] : '');
                                    $data = array(
                                        'name' => 'image',
                                        'id' => 'image',
                                        'value' => $value,
                                        'class' => 'form-control',
                                        'placeholder' => $name.' image',
                                        "accept"=>"image/*",
                                        "onchange"=>"readURL(this)",
                                    );
                                    echo form_upload($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="image" class="control-label">&nbsp;</label>
                                <div class="col-sm-12">
                                    <?php 
                                        if( !empty($user_detail['image']) )
                                            echo '<a href="'.MATCH_IMAGE_LARGE_URL.$user_detail['image'].'"> <img src="'.MATCH_IMAGE_THUMB_URL.$user_detail['image'].'"  style="width: 83px;"></a>';
                                        else
                                            echo '<img src="'.NO_IMG_URL.'" style="width: 83px;">';
                                    ?>
                                </div>                                
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="match_limit" class="control-label editlabel">Highest Winning</label>
                                <div class="col-sm-12">
                                    <?php
                                    $value = set_value("highest_winning") ? set_value("highest_winning") : (isset($user_detail['highest_winning']) ? $user_detail['highest_winning'] : '');
                                    $data = array(
										'type' => 'text',
                                        'name' => 'highest_winning',
                                        'id' => 'highest_winning',
                                        'value' => $value,
                                        'class' => 'form-control',
                                        'placeholder' => 'Match Highest Winning',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label for="match_limit" class="control-label editlabel">Tag Category</label>
                                <div class="col-sm-12">
                                    <?php
                                    $value = set_value("tag_category") ? set_value("tag_category") : (isset($user_detail['tag_category']) ? $user_detail['tag_category'] : '');
                                    $data = array(
										'type' => 'text',
                                        'name' => 'tag_category',
                                        'id' => 'tag_category',
                                        'value' => $value,
                                        'class' => 'form-control',
                                        'placeholder' => 'Match Tag Category',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="match_limit" class="control-label editlabel">&nbsp;</label>
                                <div class="col-sm-12">
                                    <input type="checkbox" name="lineup_expected" value="1" <?php echo (isset($user_detail['lineup_expected']) && $user_detail['lineup_expected']=='1')?'checked':'';  ?>>Lineup Expected
                                </div>
                            </div>
        
							<div class="form-group col-sm-1">
                                <div class="col-lg-offset-8 col-lg-4" style="margin-top: 17px;">
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
					<h4><b>Our DB players<b></h1><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
				</div>
				</div>

				<div id="third_rec_fetch">
				<div class="col-xs-6 center-block">
				<h4><b>Third party players<b></h1><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
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
    $('#match_date').datetimepicker({
		format: 'dd/mm/yyyy hh:ii',
		showMeridian:true,
		startDate:new Date(),
		autoclose: true,
        todayBtn: true,
        todayHighlight: true,
    });
    $('#close_date').datetimepicker({
        format: 'dd/mm/yyyy hh:ii',
        showMeridian:true,
        startDate:new Date(),
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
    });
	
	 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
     $("#third_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_current_playes/" ?><?=$user_detail['unique_id'] ?>/<?=$series_uniqueid ?>/<?=$user_detail['team_1_id']; ?>");
});
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
						 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
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


<script>

   function readURL(input) {
        imageValidation(input);
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#img_prev')
                .attr('src', e.target.result)
                .width(200);
            };

            reader.readAsDataURL(input.files[0]);
        }
        else {
            var img = input.value;
            $('#img_prev').attr('src', img).width(200);
        }
        $("#x").show().css("margin-right", "10px");
    }

    function in_array(needle, haystack) {
        for (var i = 0, j = haystack.length; i < j; i++) {
            if (needle == haystack[i])
                return true;
        }
        return false;
    }
    
    function getExt(filename) {
        var dot_pos = filename.lastIndexOf(".");
        if (dot_pos == -1)
            return "";
        return filename.substr(dot_pos + 1).toLowerCase();
    }



    function imageValidation(input) {


        var filename = $(input).val();
                     var idget =  $(input).attr('id');

        if (filename != '') {
            var filetype = ['jpeg', 'png', 'jpg', 'gif'];
            if (filename != '') {
                var ext = getExt(filename);
                ext = ext.toLowerCase();
                var checktype = in_array(ext, filetype);
                if (!checktype) {
                    alert(ext + " file not allowed for image.");
                    $('.imgpreview').hide();
                    $("#x").hide();
                     $("#"+idget).val('');
                    return false;

                } else {
                    var fi = document.getElementById(idget);
                    var filesize = fi.files[0].size;//check uploaded file size
                    if (filesize > 2097152) {
                        alert('Maximum 2MB file size allowed for image.');
                        $('.imgpreview').hide();
                        $("#x").hide();
                        $("#"+idget).val('');
                        return false;

                    } else {
                        $('.imgpreview').show();
                        $("#x").show();
                    }
                }
            }
            return true;
        }
    }

</script>           

<script>

$(document).ready(function(){
    /*********************************/
    $(document ).on("click",".player_image_two",function(e) {
    if (typeof FormData !== 'undefined') {
        var thisEvent = $(this);
        var baseUrl = "<?=base_url("admin/match_players/image_upload_save");?>";
        
        // send the formData
        var formData = new FormData( $("#image_change_save")[0] );
        
        var vidFileLength = $("#player_image")[0].files.length;
        if(vidFileLength === 0){
            alert("Please select image.");
            return true;
        }
        thisEvent.html('Uploading... <i class="fa fa-spinner fa-pulse  fa-fw"></i>');
        var imageaction = $(this).attr('action');
        $.ajax({
            url : baseUrl,  // Controller URL
            type : 'POST',
            data : formData,
            async : false,
            cache : false,
            contentType : false,
            processData : false,
            dataType:'html',
            success : function(data) {
                $(".imageload").append(data);
                if(imageaction =="upload_save"){
                    $('input[name$="image_radio"]:last').parent().trigger("click");
                    $('input[name$="image_radio"]:last').closest("form").submit();

                    $('#myModal').modal('hide');
                    setTimeout(function(){ 
                         $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
                         }, 300);
                    thisEvent.html('Upload & Save');
                }else{
                    thisEvent.html('Upload');
                    $("#player_image").val("");
                }
                //console.log(data);
                
            }
        });

    } else {
       alert("Your Browser Don't support FormData API! Use IE 10 or Above!");
    }   
});

    $('#myModal').on('hidden.bs.modal', function () {
        $("#player_image").val("");
    })
    /*********************************/


        $(document).on("click", ".savecredits button[type='button']", function() {
            var ielem = $(this);
            var id = ielem.attr('creditsin');
            var player_unique_id = ielem.attr('player_unique_id');
            // alert($("input#credits_"+ielem.attr('creditsin')).val());return false;
            var credits = $("input#credits_"+ielem.attr('creditsin')).val();
            var position = $("select#position_"+ielem.attr('creditsin')).val();
            var url = "<?php echo base_url("/admin/matches/credits_save");?>";
            $.post(url,
                {
                    'id':id,'credits':credits,'position':position,'player_unique_id':player_unique_id,'t': 't',
                    beforeSend: function() {
                        ielem.append('<i class="fa fa-spinner fa-pulse fa-fw"></i>');
                }}, function(data) {                        
                        if(data!=0){
                            if(position!=null){
                                 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
                            }
                        }else{
                            alert("Something wrong please try again!");
                        }
                    ielem.find('i').remove();
                });
            // return false;
    });
        $(document).on("blur",".savecredits input[type='text']",function() {
            $(this).next("button[type='button']").trigger("click");
        }); 

        $(document).on("change","select[name='position']",function() {
            $thisChange     = $(this);
            $_save_btn_id_  = $thisChange.attr('pid');
            $("#_save_btn_credits_"+$_save_btn_id_).trigger("click");
        });
});

  
$(document).ready(function(){

    
    // for actions tab
    $(document).on('click', ".image_change", function(e) {
        //e.preventDefault();
        var imageurl= "<?=PLAYER_IMAGE_THUMB_URL;?>"
        var ele     = $(this);
        var ielem   = $.parseJSON(ele.attr("modaldata"));
        // console.log(ielem);
        var player_unique_id = ielem.player_unique_id;
        $("input#player_unique_idmetake").val(player_unique_id);
        $("input#match_players_id_idmetake").val(ielem.id);
        $("input#match_players_team_id").val(ielem.team_id);
        $("input#match_unique_id___").val(ielem.match_unique_id);
        $("b#team_nameshoi__").text(ielem.team_name);
        var imageload ="";
        if(ielem.file_name !=null && ielem.file_name!=''){
            var imagesarr  = ielem.file_name.split(',');
            // Iterate through each value
            for(var i = 0; i < imagesarr.length; i++)
            {
                imageload += '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'+imageurl+imagesarr[i]+'" /><input type="radio" name="image_radio" value="'+ielem.id+'_with_'+imagesarr[i]+'" /><i class="fa fa-check hidden"></i></label></div>';
            }       
        }else{
            imageload ='<div class="col-sm-12">Image not found!</div>';
        }
        
        $(".imageload").html(imageload);
    });
    
        // add/remove checked class

    // sync the input state
    $(document).on("click",".image-radio", function(e){
        $(".image-radio").removeClass('image-radio-checked');
        $(this).addClass('image-radio-checked');
        var $radio = $(this).find('input[type="radio"]');
        $radio.prop("checked",!$radio.prop("checked"));

        e.preventDefault();
    });
    
    

    
});


    $(document).on('submit', "#image_change_save", function(e) {
        e.preventDefault();
        var fields = $(this).serializeArray();
        var findid = "";
        var valueimag = "";
        if(fields !=null && typeof fields[0] !== "undefined"){
            valueimag = fields[0].value;
            findid = valueimag.split('_with_')[0];
            // console.log(findid);
        }

        if($('input[name=image_radio]:checked', '#image_change_save').val() == undefined || $('input[name=image_radio]:checked', '#image_change_save').val() == ""){
                alert("Please select image Or Upload.");
                return true;
        }

        var url = $(this).attr("action");
            
        var formfields = $(this).serialize();

        var lFObj = {};
        $.each(fields,
                function(i, v) {
                lFObj[v.name] = v.value;
        });
        valueimag = lFObj.image_radio;
        findid = valueimag.split('_with_')[0];
       
        // alert(valueimag);         return false;
        $.post(url,
                {
                    'save_image':valueimag,'match_unique_id': lFObj.match_unique_id,'player_unique_id': lFObj.player_unique_id,'match_players_id': lFObj.match_players_id,'team_id': lFObj.team_id,'apply_all': lFObj.apply_all,'t': 't',
                    beforeSend: function() {
                        //ielem.addClass("fa-spinner fa-pulse fa-fw");
                    }}, function(data) {
                        $(document).find("#myModal").modal('hide');
                        
                        if(data!=0){
                            $("#main_"+findid).find("img").attr("src",data);

                            if(lFObj.apply_all !== "undefined" && lFObj.apply_all =='on' ){
                               setTimeout(function(){ 
                                 $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
                                }, 300);
                            }
                        }

                        $('#image_change_save')[0].reset();
                });
            // return false;
    });
    
    function image_radio(){
    $(".image-radio").each(function(){
        if($(this).find('input[type="radio"]').first().attr("checked")){
            $(this).addClass('image-radio-checked');
        }else{
            $(this).removeClass('image-radio-checked');
        }
    });
    }
  </script>
