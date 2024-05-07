<section id="main-content">

    <section class="wrapper">

        <!-- page start-->

        <div class="row">

            <div class="col-lg-12">

                <?php echo $this->breadcrumbs->show(); ?>

                <section class="panel">

                    <header class="panel-heading">

                        Add <?=$name?>

                    </header>

                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>

                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>

                        <div class=" form">

                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>



                            

                            <div class="form-group ">

                                <label for="series_id" class="control-label col-lg-2">Series Name<span class="red_star">*</span></label>

                                <div class="col-lg-10 spinnerSeries" style="position: relative;" >  <b style="position: absolute;bottom: 3px;left: 20%;font-size: 20px;"><i class="fa fa-spinner fa-pulse  fa-fw"></i></b>      

                                   <select name="series_id" class="form-control required valid" id="metch_series_id" onchange="matchesFunc(this)" >

                                       <option value="">Please Select Series</option>

                                    </select>

                                </div>

                            </div>

                            

                            <div class="form-group ">

                                <label for="matche_data" class="control-label col-lg-2"><?=$names?>  <span class="red_star">*</span></label>

                                <div class="col-lg-10 spinnerSeriesMatch" style="position: relative;" >

                                    <b style="position: absolute;bottom: 3px;left: 20%;font-size: 20px; display: none; z-index:99;"><i class="fa fa-spinner fa-pulse  fa-fw"></i></b>

                                    <select name="matche_data" class="form-control required valid" id="metch_container">

                                       <option value="">Please select Match </option>

                                    </select>

                                </div>

                            </div>

                            

                            

                            <div class="form-group">

                                <label for="match_limit" class="control-label col-sm-2">Customer team Limit<span class="red_star">*</span></label>

                                    <input type="hidden" name="permissions" id="permissions">

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("match_limit") ? set_value("match_limit") : (isset($user_detail['match_limit']) ? $user_detail['match_limit'] : '');

                                    $data = array(

                                        'type' => 'number',

                                        'name' => 'match_limit',

                                        'id' => 'match_limit',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Customer team Limit ',

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>

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

                                        "onchange"=>"readURL(this)",

                                    );

                                    echo form_upload($data);

                                    ?>

                                    <p>Image dimension should be within 800 X 328. Allowed image extensions jpg,jpeg,png</p>

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

<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/select2/select2.min.css" />

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/select2/js/select2.full.min.js"></script>

<style type="text/css">

    .select2-container--default .select2-results__option[aria-disabled="true"] {color: #8e7f7f;background-color:    #dde6db;}

</style>



<script>

function matcheSeriesFunc(){    

        var container = $("#metch_series_id");

        $.ajax({

            type: 'GET', 

            url: '<?=base_url($prefixUrl.'get_matche_series');?>', 

            dataType: 'json',

            success: function(data) {

                if(!data.hasOwnProperty("error") ){

                $.each(data.matches, function(key, value) {

                    //if(value.squad== true && value.matchStarted == false){

                        container.append($("<option></option>")

                        .attr("value",value['unique_id'])

                        .text(value['title'] ) );

                    //}

                });             

                //container.select2();

                $(".spinnerSeries b").remove();

                

              }

            }

        });

             

    }

$(document).ready(matcheSeriesFunc);



function matchesFunc(thisdata){ 

    var container = $("#metch_container");

        container.empty();

        container.append( $("<option></option>")

        .attr("value","").text("Please select Match") );

       // container.select2();



    if(thisdata.value !=""){

        $(".spinnerSeriesMatch b").show();

        var series_id = thisdata.value;

        $.ajax({

            type: 'GET', 

            url: '<?=base_url($prefixUrl.'get_matches');?>/'+series_id, 

            dataType: 'json',

            success: function(data) {

                if(!data.hasOwnProperty("error") ){

                $.each(data.matches, function(key, value) {

                    var addedM = <?php echo json_encode($added_ids)?>;

                    var strAdd= "";

                    if(addedM.indexOf(value.unique_id.toString()) >=0 ){

                        strAdd = "Added";

                    }



                    if(value.squad== true && value.matchStarted == false){

                        container.append($("<option></option>")

                        .attr("value",JSON.stringify(value))

                        .attr('disabled', addedM.includes(value.unique_id.toString()) )

                        .text(value['title'] +" [" + value['series_data']['title'] + "] ("+ dateTimeFormate(value['dateTimeGMT'])+" ) " +strAdd ));



                    }

                });             

                //container.select2();

                $(".spinnerSeriesMatch b").hide();

              }

            }

        });

    }

} 



//$(document).ready(matchesFunc);



function dateTimeFormate(timeStringGet){

    var msec = Date.parse(timeStringGet);

    var d = new Date(msec);

    return d.toLocaleString('en-GB',{hour12:true});

}



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

