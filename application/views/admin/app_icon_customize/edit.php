<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit App Icon Customize
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                        
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Name </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control',
                                        'placeholder' => 'Mata Title',
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
                                        "accept"=>"image/png",
                                        "onchange"=>"readURL(this);"
                                    );
                                    echo form_upload($data);
                                    ?>
                                    <sup>Image dimension should be within max width=300px and max height=300px. Allowed image extensions png</sup>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="image" class="control-label col-lg-2">&nbsp;</label>
                                <div class="col-sm-10">
                                    <?php 
                                        if( !empty($user_detail['image']) )
                                            echo '<a href="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$user_detail['image'].'" style="background-color: #f1f0f0;width: 100px;display: block;height: 100px;"> <img src="'.APP_ICON_CUSTOMIZE_IMAGE_THUMB_URL.$user_detail['image'].'"  height="32" width="32" style="margin-top: 30px;margin-left: 30px;"></a>';
                                        else
                                            echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                    ?>
                                </div>                                
                            </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-danger" type="submit">Save</button>
                                        <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin/app_icon_customize" ?>">Cancel</a>
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
            var filetype = ['jpeg', 'png', 'jpg'];
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