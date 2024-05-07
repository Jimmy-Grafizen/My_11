<section id="main-content">

    <section class="wrapper">

        <!-- page start-->

        <div class="row"> 

            <div class="col-lg-12">

                <?php echo $this->breadcrumbs->show(); ?>

                <section class="panel">

                    <header class="panel-heading">

                        Add <?php echo $names; ?>

                    </header>

                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>

                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>

                        <div class=" form">

                             <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">

                                <label for="rc_type" class="control-label col-lg-2">Recharge Type<span class="red_star">*</span></label>

                                <div class="col-lg-10">         

                                    <?php                               

                                        $opt_all = array('Recharge','Redeem');

                                        $value = set_value("rc_type") ? set_value("rc_type") : (isset($user_detail['rc_type']) ? $user_detail['rc_type'] : '');

                                        echo form_dropdown('rc_type', $opt_all, $value, 'class="form-control required" id="Recharge" onchange="showRedeemFields($(this))"');

                                    ?>

                            </div>

                            </div>

                            <div class="form-group ">

                                <label for="amt_type" class="control-label col-lg-2">Amount Type<span class="red_star">*</span></label>

                                <div class="col-lg-10">         

                                    <?php                               

$opt_all_types = array('deposit_wallet'=>'Deposit','bonus_wallet'=>'Cash Bonus');

                                        $value = set_value("amt_type") ? set_value("amt_type") : (isset($user_detail['amt_type']) ? $user_detail['amt_type'] : '');

                                        echo form_dropdown('amt_type', $opt_all_types, $value, 'class="form-control required" id="Recharge amount type" ');

                                    ?>

                            </div>

                            </div>

                            <div class="form-group">

                                <label for="code" class="control-label col-lg-2">Code<span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("code") ? set_value("code") : (isset($user_detail['code']) ? $user_detail['code'] : '');

                                    $data = array(

                                        'name' => 'code',

                                        'id' => 'code',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Code',

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>

                            

                            <div class="form-group" >

                                <label for="amount" class="control-label col-lg-2">Amount<span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <input type="text" class="form-control" name="amount" value="<?php echo (isset($user_detail['amount']))?$user_detail['amount']:0;?>">

                                </div>

                            </div>

                            

                            <div class="form-group ">

                                <label for="max_recharge" class="control-label col-lg-2">Max Recharge  <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("max_recharge") ? set_value("max_recharge") : (isset($user_detail['max_recharge']) ? $user_detail['max_recharge'] : '');

                                    $data = array(

                                        'name' => 'max_recharge',

                                        'id' => 'max_recharge',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Max Recharge',

                                        'oninput'=>"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>

                            <div class="form-group ">

                                <label for="recharge" class="control-label col-lg-2">Min Recharge  <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("recharge") ? set_value("recharge") : (isset($user_detail['recharge']) ? $user_detail['recharge'] : '');

                                    $data = array(

                                        'name' => 'recharge',

                                        'id' => 'recharge',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Min Recharge',

                                        'oninput'=>"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>



                            <div class="form-group">

                                <label for="cash_bonus_type" class="control-label col-lg-2">Cash Bonus Type<span class="red_star">*</span></label>

                                <div class="col-lg-10">         

                                    <?php                               

                                        $opt_all = unserialize(CASH_BONUS_TYPE);

                                        $value = set_value("cash_bonus_type") ? set_value("cash_bonus_type") : (isset($user_detail['cash_bonus_type']) ? $user_detail['cash_bonus_type'] : '');

                                        echo form_dropdown('cash_bonus_type', $opt_all, $value, 'class="form-control required" id="cash_bonus_type"');

                                    ?>

                                </div>

                            </div>

                           <div class="form-group">

                                <label for="cach_bonus" class="control-label col-lg-2">Cash bonus<span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("cach_bonus") ? set_value("cach_bonus") : (isset($user_detail['cach_bonus']) ? $user_detail['cach_bonus'] : '');

                                    $data = array(

                                        'name' => 'cach_bonus',

                                        'id' => 'cach_bonus',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Cash bonus',

                                        'oninput'=>"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>

                            <div class="form-group ">

                                <label for="is_use" class="control-label col-lg-2"> Use Type<span class="red_star">*</span></label>

                                <div class="col-lg-10">         

                                    <?php                               

                                        $opt_all = unserialize(IS_USE_RECHARGE);

                                        $value = set_value("is_use") ? set_value("is_use") : (isset($user_detail['is_use']) ? $user_detail['is_use'] : '');

                                        echo form_dropdown('is_use', $opt_all, $value, 'class="form-control required" id="is_use"');

                                    ?>

                                </div>

                            </div>

                           <div class="form-group" id="row_dim">

                                <label for="is_use_max" class="control-label col-lg-2">Use Limit <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("is_use_max") ? set_value("is_use_max") : (isset($user_detail['is_use_max']) ? $user_detail['is_use_max'] : '');

                                    $data = array(

                                        'name' => 'is_use_max',

                                        'id' => 'is_use_max',

                                        'value' => $value,

                                        'maxlength' => 50,

                                        'class' => 'form-control required',

                                        'placeholder' => 'Use Limit',

                                        'oninput'=>"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"

                                    );

                                    echo form_input($data);

                                    ?>

                                </div>

                            </div>



                            <div class="form-group ">

                                <label for="start_date" class="control-label col-lg-2">Start Date <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $start_date = set_value("start_date") ? date(CLOSE_DATE_TIME_FORMAT_ADMIN,strtotime( str_ireplace("/", "-",set_value("start_date") ) ) ): (isset($user_detail['start_date']) ? date(CLOSE_DATE_TIME_FORMAT_ADMIN,  $user_detail['start_date']  ): '');

                                    $data = array(

                                        'type' => 'text',

                                        'name' => 'start_date',

                                        'id' => 'start_date',

                                        'value' => $start_date,

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

                                <label for="end_date" class="control-label col-lg-2">End Date <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                     $end_date = set_value("end_date") ? date(CLOSE_DATE_TIME_FORMAT_ADMIN,strtotime( str_ireplace("/", "-",set_value("end_date") ) ) ): (isset($user_detail['end_date']) ? date(CLOSE_DATE_TIME_FORMAT_ADMIN,  $user_detail['end_date']  ): '');

                                    $data = array(

                                        'type' => 'text',

                                        'name' => 'end_date',

                                        'id' => 'end_date',

                                        'value' => $end_date,

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

    $(function() {

        <?php 

            if(isset($user_detail['is_use']) && $user_detail['is_use'] =="M"){

                echo "$('#row_dim').show();";

            }else{

             echo "$('#row_dim').hide();";

            }

        ?>    

    $('#is_use').change(function(){

        if($(this).val() == 'M') {

            $('#row_dim').show(); 

        } else {

            $('#row_dim').hide(); 

        } 

    });

});

</script>



<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/css/datetimepicker.css" />

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>

<script>

$(document).ready(function(){



   var startDate;

    $("#start_date").datetimepicker({

            timepicker:true,

            closeOnDateSelect:false,

            closeOnTimeSelect: true,

            initTime: true,

            format: 'dd/mm/yyyy hh:ii',

            showMeridian:true,

            startDate:new Date(),

            autoclose: true,

            todayBtn: true,

            todayHighlight: true,

            minDate: 0,

            roundTime: 'ceil',

            changeDate: function(dp,$input){

                       startDate = $("#start_date").val();

            }

    });



    $("#end_date").datetimepicker({

            timepicker:true,

            closeOnDateSelect:false,

            closeOnTimeSelect: true,

            initTime: true,

            format: 'dd/mm/yyyy hh:ii',

            showMeridian:true,

            startDate:new Date(),

            autoclose: true,

            todayBtn: true,

            todayHighlight: true,

            changeDate: function(current_time, $input){

                    var endDate = $("#end_date").val();

                    if(startDate>endDate){

                           alert('Please select correct date');

                     }

            }

    });

});



$(document).ready(function(){

    $('select[name="rc_type"]').trigger('change');

});

function showRedeemFields(obj){

    var v = obj.val();

    if(v == 1){

        //hide

        $('input[name="is_use_max"]').parents('.form-group').show();

        $('input[name="cach_bonus"]').parents('.form-group').hide();

        $('input[name="max_recharge"]').parents('.form-group').show();

        $('input[name="recharge"]').parents('.form-group').hide();

        $('input[name="amount"]').parents('.form-group').show();

        $('select[name="is_use"]').parents('.form-group').show();

        $('select[name="cash_bonus_type"]').parents('.form-group').hide();

        $('select[name="amt_type"]').parents('.form-group').show();

    }else{

        $('input[name="is_use_max"]').parents('.form-group').show();

        $('input[name="cach_bonus"]').parents('.form-group').show();

        $('input[name="max_recharge"]').parents('.form-group').show();

        $('input[name="recharge"]').parents('.form-group').show();

        $('input[name="amount"]').parents('.form-group').hide();

        $('select[name="is_use"]').parents('.form-group').show();

        $('select[name="cash_bonus_type"]').parents('.form-group').show();

        $('select[name="amt_type"]').parents('.form-group').hide();

        

    }

}



</script>