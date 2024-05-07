<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit <b><?php echo $user_detail['game_name']; ?></b> Quotations
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                           
                            <div class="form-group ">
                            
                                <?php
                                    $expiry_date = set_value("expiry_date") ? set_value("expiry_date") : (isset($user_detail['expiry_date']) ? $user_detail['expiry_date'] : '');
                                ?>
                                <label for="expiry_date" class="control-label col-lg-2">Expiry Date [<?php echo ($expiry_date>0)? date(DATE_TIME_FORMAT_ADMIN,$expiry_date) : ""; ?>]  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $expiry_date = set_value("expiry_date") ? set_value("expiry_date") : (isset($user_detail['expiry_date']) ? $user_detail['expiry_date'] : '');
                                    $data = array(
                                        'type' => 'text',
                                        'name' => 'expiry_date',
                                        'id' => 'expiry_date',
                                        'value' => ($expiry_date>0)? date(CLOSE_DATE_TIME_FORMAT_ADMIN,$expiry_date):"",
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Expiry Date',
                                        'readonly ' => true,
                                        'style'=>"cursor: unset;background-color: #fff;"
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                               
                            </div>
                            
                            <div class="form-group ">
                            
                                <label for="link" class="control-label col-lg-2">Link<span class="red_star">*</span></label>
                                <div class=" col-lg-10">
                                    <?php
                                    $value = set_value("link") ? set_value("link") : (isset($user_detail['link']) ? $user_detail['link'] : '');
                                    $data = array(
                                        'type' => 'url',
                                        'name' => 'link',
                                        'id' => 'link',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control',
                                        'placeholder' => 'For Example: http://example.com',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                           
                            </div>

                            <div class="form-group ">
                                <label for="image" class="control-label col-lg-2"> Image  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("image") ? set_value("image") : (isset($user_detail['image']) ? $user_detail['image'] : '');
                                    $data = array(
                                        'name' => 'image',
                                        'id' => 'image',
                                        'value' => $value,
                                        'class' => 'form-control',
                                        'placeholder' => ' image',
                                        "accept"=>"image/*",
                                    );
                                    echo form_upload($data);
                                    ?>
                                    <sup>Allowed image extensions jpg,jpeg,png</sup>
                                    <!--p>Image dimension should be within 800 X 328.</p>-->
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="image" class="control-label col-lg-2">&nbsp;</label>
                                <div class="col-sm-10">
                                    <?php 
                                        if( !empty($user_detail['image']) )
                                            echo '<a href="'.QUOTATIONS_IMAGE_THUMB_URL.$user_detail['image'].'"> <img src="'.QUOTATIONS_IMAGE_THUMB_URL.$user_detail['image'].'"></a>';
                                        else
                                            echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                    ?>
                                </div>                                
                            </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-danger" type="submit">Save</button>
                                        <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin/quotations" ?>">Cancel</a>
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

<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/css/datetimepicker.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>
<script>
$(document).ready(function(){

    $('#expiry_date').datetimepicker({
        format: 'dd/mm/yyyy hh:ii',
        showMeridian:true,
        startDate:new Date(),
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
    });
    
});
</script>

<script>
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



    function imageValidation() {


        var filename = document.getElementById("fileimage").value;

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
                    $("#fileimage").val('');
                    return false;

                } else {
                    var fi = document.getElementById('fileimage');
                    var filesize = fi.files[0].size;//check uploaded file size
                    if (filesize > 2097152) {
                        alert('Maximum 2MB file size allowed for image.');
                        $('.imgpreview').hide();
                        $("#x").hide();
                        $("#fileimage").val('');
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
