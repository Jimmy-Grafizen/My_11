<style type="text/css">
   form#image_change_save input[type=radio]{
        display: none;
    }
</style>
<script>
    function checkedAll() {
        for (var i = 0; i < document.getElementById('table_form').elements.length; i++) {
            document.getElementById('table_form').elements[i].checked = true;
        }
    }
    function uncheckedAll() {
        for (var i = 0; i < document.getElementById('table_form').elements.length; i++) {
            document.getElementById('table_form').elements[i].checked = false;
        }
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {

        // page date picker feature
        $("#datepicker").datepicker({
            format: 'yyyy-mm-dd',
            endDate: "1d"});

        // for search results
        $('#search_form').submit(function(event)
        {
            // Stop full page load
            event.preventDefault();
            var fields = $('.search_fields').serializeArray();
            $.post('<?php echo $ajax_url; ?>',
                    {
                        fields: fields, 't': 't',
                        beforeSend: function() {
                            $('#loading-image').show();
                            $('#current_url').val('<?php echo $ajax_url; ?>');
                            window.history.pushState('', '', '<?php echo $this_url; ?>');
                        }}, function(data) {
                $('#middle-content').html(data);
                $('#loading-image').hide();
            });
            ;
            return false;

        });

    });

    // for select all and actions
    $(document).on("submit", '.form', function(event)
    {
        // Stop full page load
        event.preventDefault();
        var fields = $('.search_fields').serializeArray();
        var matches = [];
        $(".className:checked").each(function() {
            matches.push(this.value);
        });

        if (!matches.length) {
            alert('Please select atleast one record');
            return false;
        }

        if (!$("#table-action").val()) {
            alert('Please select action');
            return false;
        }
        var current_url = $('#current_url').val();
        current_url = current_url.replace("index", "ajax_index");
        $.post(current_url,
                {
                    fields: fields, check: matches, action: $("#table-action").val(),
                    beforeSend: function() {
                        $('#loading-image').show();
                        window.history.pushState('', '', $('#current_url').val());
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                    }
                },
        function(data) {
            $('#middle-content').html(data);
            $('#loading-image').hide();
        });
        return false;

    });

    // for actions tab
    $(document).on('click', ".action-list", function(e) {
        e.preventDefault();
        if ($(this).hasClass("delete-list")) {
            if (!confirm("Are you sure you want to delete?")) {
                return false;
            }
        }
        var url = $(this).attr("href");
        var fields = $('.search_fields').serializeArray();
        var current_url = $('#current_url').val();
        current_url = current_url.replace("index", "ajax_index");
        $.post(url,
                {
                    't': 't',
                    beforeSend: function() {
                        $('#loading-image').show();
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                    }}, function(data) {
            $.post(current_url,
                    {
                        fields: fields, 't': 't',
                        beforeSend: function() {
                            $('#loading-image').show();
                            $('#current_url').val('<?php echo $ajax_url; ?>');
                        }}, function(data) {
                $('#middle-content').html(data);
                $('#loading-image').hide();
            });

        });
        ;
        return false;

    })

    // for enable sort feature
    $(document).on('click', ".enable-sort", function(e) {
        var field = $(this).attr('field');
        var sort_type = $(this).attr('sort_type') ? $(this).attr('sort_type') : "asc";
        e.preventDefault();
        var fields = $('.search_fields').serializeArray();
        $.post('<?php echo $ajax_url; ?>?sort=' + sort_type + "&field=" + field,
                {
                    fields: fields,
                    beforeSend: function() {
                        $('#loading-image').show();
                        $('#current_url').val('<?php echo $ajax_url; ?>');
                        window.history.pushState('', '', '<?php echo $this_url; ?>');
                    }}, function(data) {
            $('#middle-content').html(data);
            $('#loading-image').hide();
        });
        ;
        return false;
    });
</script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/data-tables/DT_bootstrap.css" />
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Search Teams
                    </header>
                    <div class="panel-body">
                        <?php
                        echo form_open('', array('class' => "form-inline", 'id' => 'search_form'));
                        ?>
                        <div class="form-group">
                            <label class="sr-only" for="search">Your Keyword</label>
                            <?php
                            if ($this->uri->segment(4) == 'search') {
                                $search = urldecode($this->uri->segment(5));
                            } else {
                                $search = urldecode($this->input->post('search'));
                            }
                            $data = array(
                                'name' => 'search',
                                'id' => 'search',
                                'value' => $search,
                                'class' => 'required search_fields form-control',
                                'placeholder' => 'Your Keyword'
                            );
                            echo form_input($data);
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="sr-only" for="date">Search by joining date</label>
                            <?php
                            if ($this->uri->segment(4) == 'search') {
                                $search = urldecode($this->uri->segment(5));
                            } else {
                                $search = urldecode($this->input->post('search'));
                            }
                            $data = array(
                                'name' => 'date',
                                'id' => 'datepicker',
                                'value' => $search,
                                'class' => 'required default-date-picker search_fields form-control',
                                'placeholder' => 'Search By Created Date'
                            );
                            //echo form_input($data);
                            ?>
                        </div>
                        <button type="submit" class="btn btn-success">Search</button>
                        <?php echo form_close(); ?>
                    </div>
                </section>

            </div>
        </div>
        <div id="middle-content">
            <?php echo $ajax_content; ?>
        </div>
    </section>
</section>
<!-- END PAGE --> 

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Team Jersey Upload.</h4>
        </div>
        <div class="modal-body">
            <form action="<?=base_url("{$prefixUrl}image_change_save");?>" method="post" id="image_change_save" enctype="multipart/form-data" accept-charset="utf-8">
                <div class="row imageload"> 
                </div>
                <div class="row ">
                    <br>
                    <div class="col-sm-12 form-group">
                        <label for="player_image" class="control-label">Upload image:</label>
                        <input type="hidden" name="team_id" id="team_id__">
                        <input id="player_image" name="player_image[]" type="file" class="file" data-show-preview="false" accept="image/*"  onchange="readURL(this)" >
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
                        <?php /*<button type="submit" class="btn btn btn-success pull-right" style="margin-right: 10px;">Selected Save As Defualt</button>
                        <button type="button" class="btn btn btn-success pull-right player_image_two" style="margin-right: 10px;" action="upload_save">Upload & Save </button> */ ?>
                        <button type="button" class="btn btn btn-success pull-right player_image_two" style="margin-right: 10px;" action="save">Upload</button>
                    </div>
                </div>
            </form>        
        </div>
    </div>
      
    </div>
  </div>



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
        var baseUrl = "<?=base_url("{$prefixUrl}image_upload_save");?>";
        
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
            url : baseUrl,
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
                         
                         }, 100);
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
        $("input#team_id__").val(ielem.id);
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
                    'save_image':valueimag,'team_id': lFObj.team_id,'t': 't',
                    beforeSend: function() {
                        //ielem.addClass("fa-spinner fa-pulse fa-fw");
                    }}, function(data) {
                        $(document).find("#myModal").modal('hide');
                        
                        if(data!=0){
                            $("#main_"+findid).find("img").attr("src",data);

                            /*if(lFObj.apply_all !== "undefined" && lFObj.apply_all =='on' ){
                               setTimeout(function(){ 
                                 
                                }, 100);
                            }*/
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


    $('#myModal').on('hidden.bs.modal', function () {
       $('#search_form').submit();
    })
  </script>