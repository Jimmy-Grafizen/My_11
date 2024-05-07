<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/data-tables/DT_bootstrap.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/ckeditor/ckeditor.js"></script>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit Page Content
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                            <?php if($user_detail["platform"] == 'W'){ ?>
                            <div class="form-group ">
                                <label for="meta_title" class="control-label col-lg-2">Meta Title </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("meta_title") ? set_value("meta_title") : (isset($user_detail['meta_title']) ? $user_detail['meta_title'] : '');
                                    $data = array(
                                        'name' => 'meta_title',
                                        'id' => 'meta_title',
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
                                <label for="meta_keywords" class="control-label col-lg-2">Meta Keywords </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("meta_keywords") ? set_value("meta_keywords") : (isset($user_detail['meta_keywords']) ? $user_detail['meta_keywords'] : '');
                                    $data = array(
                                        'name' => 'meta_keywords',
                                        'id' => 'meta_keywords',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control',
                                        'placeholder' => 'Meta Keywords',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="meta_description" class="control-label col-lg-2">Meta Description </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("meta_description") ? set_value("meta_description") : (isset($user_detail['meta_description']) ? $user_detail['meta_description'] : '');
                                    $data = array(
                                        'name' => 'meta_description',
                                        'id' => 'meta_description',
                                        'value' => $value,
                                        'rows' => 5,
                                        'class' => 'form-control',
                                        'placeholder' => 'Meta Description',
                                    );
                                    echo form_textarea($data);
                                    ?>
                                </div>
                            </div>
                             <?php  } ?>

                            <div class="form-group ">
                                <label for="title" class="control-label col-lg-2">Title  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("title") ? set_value("title") : (isset($user_detail['title']) ? $user_detail['title'] : '');
                                    $data = array(
                                        'name' => 'title',
                                        'id' => 'title',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Title',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div> 
                            <div class="form-group ">
                                <label for="page_url" class="control-label col-lg-2">URL</label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("page_url") ? set_value("page_url") : (isset($user_detail['page_url']) ? $user_detail['page_url'] : '');
                                    $data = array(
                                        'name' => 'page_url',
                                        'id' => 'page_url',
                                        'value' => $value,
                                        'maxlength' => 200,
                                        'class' => 'form-control',
                                        'placeholder' => 'URL',
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
                                        'class' => 'form-control required',
                                        'placeholder' => ' image',
										"accept"=>"image/*",
                                    );
                                    echo form_upload($data);
                                    ?>
                                    <p>Allowed image extensions jpg,jpeg,png</p>
                                </div>
                                <div class="col-lg-2">
								 <?php 
									if((file_exists(CONTEXTCATEGORY_IMAGE_THUMB_PATH.$value) && !empty($value)) || CHECK_IMAGE_EXISTS)
										echo '<img src="'.CONTEXTCATEGORY_IMAGE_THUMB_URL.$value.'" height="50" width="50" style="float: right;">';
									else
										echo '<img src="'.NO_IMG_URL.'" height="50" width="50">';
								?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="content" class="control-label col-lg-2">Content  <span class="red_star">*</span></label>
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
                                        <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin/content_management" ?>">Cancel</a>
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
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

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
            height: 500,
            width: 900,
            enterMode : CKEDITOR.ENTER_BR,
            allowedContent: true
        });

</script>
<script type="text/javascript">
// <![CDATA[
        
//]]>       
</script>