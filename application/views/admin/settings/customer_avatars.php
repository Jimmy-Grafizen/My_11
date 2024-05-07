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
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-inline tasi-form form', 'id' => 'myform')); 
							?>
                        
                            <div class="form-group" id="">
                                <label for="image" class="control-label col-lg-8">Choose Image<span class="red_star">*</span></label>
                                <div class="col-lg-8">
                                    <?php
                                        $value ="";
                                        $data = array(
                                            'name'      => 'image',
                                            'id'        => 'image',
                                            'value'     =>  $value,
                                            'class'     =>  'form-control',
                                            "accept"    =>  "image/*",
                                            "onChange" =>  "readURL(this)",
                                        );
                                        echo form_upload($data);
                                    ?>
                                    <sup>Image dimension should be within max width=500px and max height=500px. Allowed image extensions jpg,jpeg,png</sup>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-2">
                                    <button class="btn btn-primary mb-2" type="submit">Save</button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <br>
                            <div class="row imageload">
                                <?php 
                                    foreach ($profile_pictures as $pictures) { ?>
                                        <div class="col-xs-3 col-sm-2 col-md-1 col-xl-1 nopad text-center">
                                            <label class="image-radio image-area" > 
                                                <img class="img-responsive" src="<?php echo CUSTOMER_IMAGE_THUMB_URL.$pictures['image'] ?>" />
                                                    <i class="fa fa-trash remove-image delete-list" href="<?php echo HTTP_PATH.'admin/settings/customer_avatars_delete/'.$pictures['id']; ?>"></i>
                                            </label>
                                        </div>
                                    <?php        
                                        }
                                    ?>          
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

<script>
    $(document).on('click', ".remove-image.delete-list", function(e) {
        e.preventDefault();
        
        if (!confirm("Are you sure you want to delete?")) {
            return false;
        }

        var url = $(this).attr("href");
        $.post(url,
                {
                    't': 't',
                    beforeSend: function() {
                        $('#loading-image').show();
                        
                    }}, function(data) {
                        location.reload(false);
            });
    });
</script> 
<style type="text/css">
   .image-area{position:relative;background:#333}.remove-image{position:absolute;top:-10px;right:-10px;border-radius:10em;padding:2px 6px 3px;text-decoration:none;background:#555;border:3px solid #fff;color:#fff;box-shadow:0 2px 6px rgba(0,0,0,.5),inset 0 2px 4px rgba(0,0,0,.3);text-shadow:0 1px 2px rgba(0,0,0,.5);-webkit-transition:background .5s;transition:background .5s;cursor:pointer}.remove-image:hover{background:#e54e4e;padding:3px 7px 5px;top:-11px;right:-11px}.remove-image:active{background:#e54e4e;top:-10px;right:-11px}
</style>