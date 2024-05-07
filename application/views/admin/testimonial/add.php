<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/ckeditor/ckeditor.js"></script>

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
                                <label for="image" class="control-label col-lg-2"><?=$name ?> Image  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("image") ? set_value("image") : (isset($user_detail['image']) ? $user_detail['image'] : '');
                                    $data = array(
                                        'name' => 'image',
                                        'id' => 'image',
                                        'value' => $value,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' image',
										"accept"=>"image/*",
                                    );
                                    echo form_upload($data);
									if(isset($validation_errors)){
										echo '<label for="image" class="error">'.$validation_errors.'</label>';
									}
                                    ?><p>Image dimension should be within 800 X 328. Allowed image extensions jpg,jpeg,png</p>
                                </div>
                            </div>
                            <div class="form-group"  id="div_content">
                                <label for="content" class="control-label col-lg-2">Designation</label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("designation") ? set_value("designation") : (isset($user_detail['designation']) ? $user_detail['designation'] : '');
                                    $data = array(
                                        'name' => 'designation',
                                        'value' => $value,
                                        'rows' => 2,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Content',
                                    );
                                    echo form_textarea($data);
                                    ?>
                                </div>
                                
                            </div>
                             <div class="form-group"  id="div_content">
                                <label for="content" class="control-label col-lg-2">Testimonials user</label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'value' => $value,
                                        'rows' => 2,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Content',
                                    );
                                    echo form_textarea($data);
                                    ?>
                                </div>
                                
                            </div>
                            <div class="form-group"  id="div_content">
                                <label for="content" class="control-label col-lg-2">Content </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("content") ? set_value("content") : (isset($user_detail['content']) ? $user_detail['content'] : '');
                                    $data = array(
                                        'name' => 'content',
                                        'id' => 'ckcontent',
                                        'value' => $value,
                                        'rows' => 5,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Content',
                                    );
                                    echo form_textarea($data);
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
<script>

$(document).ready(function(){

if ($('#is_match').is(':checked')) {
    $("#div_match_unique_id").show();
    $("#div_content").hide();
    $("#ckcontent").empty();
    CKEDITOR.instances["ckcontent"].setData('');
} else {
    $("#div_match_unique_id").hide();
    $("#match_unique_id").val('');
    $("#div_content").show();
}
    $(document).on('click', '#is_match', function() {
        if($(this).is(':checked')){
            $("#div_match_unique_id").show();
            $("#div_content").hide();
            $("#ckcontent").empty();
              CKEDITOR.instances["ckcontent"].setData('');
        } else {
            $("#div_match_unique_id").hide();
            $("#div_content").show();
            $("#match_unique_id").val('');
        }
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
    CKEDITOR.replace( 'ckcontent',
        {
            height: 400,
            
            enterMode : CKEDITOR.ENTER_BR,
            allowedContent: true
        });

</script>
<script type="text/javascript">
// <![CDATA[
        
//]]>       
</script>