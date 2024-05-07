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

                       <?php //echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>



                            <div class="form-group col-lg-12">

                                <label for="is_promotional" class="control-label col-lg-2">Is Promotional  <span class="red_star">*</span></label>

                                <div class="col-lg-1">

                                    <?php

                                    $value = set_value("is_promotional") ? set_value("is_promotional") : (isset($user_detail['is_promotional']) ? $user_detail['is_promotional'] : '1');

                                    $data = array(

                                        'name' => 'is_promotional',

                                        'id' => 'is_promotional',

                                        'value' => $value,

                                        'class' => 'form-control',

                                        'form' => 'myform',

                                        'checked' => ($value =='1')?TRUE:FALSE,

                                    );

                                    echo form_checkbox($data);                                    

                                    ?>

                                </div>

                            </div>



                              <div class="form-group col-lg-12">

                                <label for="users_ids" class="control-label col-lg-2">Customers <span class="red_star">*</span></label>

                                <div class="col-lg-10" style="padding:0">         

                                    <?php

                                    $joins[1] = ['table'=>"tbl_customer_logins", 'condition'=>"{$tbl_customers}.id=tbl_customer_logins.customer_id",'jointype'=>'right'];

                                    $opt_all = $this->main_model->cruid_select_array_order("$tbl_customers", "DISTINCT(`tbl_customer_logins`.`customer_id`),firstname,lastname,email,{$tbl_customers}.id", $joins, $cond = array("status" => 'A',"is_deleted" => 'N'), $order_by = array("field"=>"email","type"=>"ASC"), $limit = '', $order_by_other = array());                                    

                                   $opts= [];

                                    if (!empty($opt_all)) {

                                        foreach ($opt_all as $datass) {

                                            $opts[$datass->id] = ucfirst($datass->firstname)." ".ucfirst($datass->lastname)." (".$datass->email.")";

                                        }

                                    }

                                    $notifi_ids = null;

                                    $notifi_idsYes  = $this->session->userdata('notifi_ids');

                                    if( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'reports/customers') !== false &&$notifi_idsYes){

                                        $notifi_ids = $notifi_idsYes;

                                    }



                                    $value = set_value("users_ids") ? set_value("users_ids") : (isset($users_ids) ? $users_ids : $notifi_ids);

                                    if($notifi_ids){

                                        $value = $notifi_ids;

                                    }

                                    echo form_multiselect('users_ids[]', $opts, $value, 'class="required" id="users_ids"');

                                    ?>

                                </div>                               

                            </div>



                        

                       <div class="row" style="margin-top:20px;"></div>

                        <div class="form">

                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                        

                          

							

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

                                        'placeholder' => $name.' Title',

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

                                        'class' => 'form-control',

                                        'placeholder' => $name.' image',

										"accept"=>"image/*",

                                    );

                                    echo form_upload($data);                                    

                                    if(isset($validation_errors)){

                                        echo '<label for="image" class="error">'.$validation_errors.'</label>';

                                    }

                                    ?>

                                     <?php

                                        if($value){

                                            echo '<img src="'.NOTIFICATION_IMAGE_THUMB_URL.$value.'" style="margin: 5px;width: 150px;height: 100px;">';

                                        }

                                    ?>

                                    <p>Image dimension should be within 800 X 328. Allowed image extensions jpg,jpeg,png</p>

                                </div>

                            </div>

							

							<div class="form-group ">

                                <label for="body" class="control-label col-lg-2">Message <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                   

                                    $value = set_value("body") ? set_value("body") : (isset($user_detail['notification']) ? $user_detail['notification'] : '');

                                    $data = array(

                                        'name' => 'body',

                                        'id' => 'body',

                                        'value' => $value,                                        

                                        'class' => 'form-control required',

                                        'placeholder' => 'Message Body',

                                    );

                                    echo form_textarea($data,$value);

                                    ?>

                                </div>

                            </div>

                            <input type="hidden" id="useraarray" value="" name="users">

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

<style>

select {

  width: 100%;

}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.css" />

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.js"></script>



<script>





$("select").multipleSelect({

        filter: true,

        selectAll: true,

        multiple: true,

        multipleWidth: 400,

        within: window,

        onCheckAll: function () {



             var vall=$('select').multipleSelect('getSelects');

             $('#useraarray').val("");

             $('#useraarray').val(vall);



         },



        onUncheckAll: function () {



             $('#useraarray').val("");



        },



        onClick: function () {



             var vall=$('select').multipleSelect('getSelects');

             $('#useraarray').val("");

             $('#useraarray').val(vall);



        },

    });



    var vall=$('select').multipleSelect('getSelects');

    $('#useraarray').val("");

    $('#useraarray').val(vall);



</script>