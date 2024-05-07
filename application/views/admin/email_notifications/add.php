<style>
.panel-body span :not(#cke_ckcontent span) {
    padding: 2px 5px !important;
}
</style>
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

                       <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform12')) ?>



                              <div class="form-group ">

                                <label for="users_ids" class="control-label col-lg-2">Customers <span class="red_star">*</span></label>

                                <div class="col-lg-10">         

                                    <?php

                                      $opt_all = $this->main_model->cruid_select_array_order("$tbl_customers", "firstname,lastname,email,id", $joins = array(), $cond = array("status" => 'A',"is_deleted" => 'N'), $order_by = array("field"=>"email","type"=>"ASC"), $limit = '', $order_by_other = array());

                                      

                                   $opts= [];

                                    if (!empty($opt_all)) {

                                        foreach ($opt_all as $datass) {

                                            $opts[$datass->id] = ucfirst($datass->firstname)." ".ucfirst($datass->lastname)." (".$datass->email.")";

                                        }

                                    }

                                    $value = set_value("users_ids") ? set_value("users_ids") : (isset($users_ids) ? $users_ids : '');

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

                        </form>

                        

                       <div class="row" style="margin-top:20px; "></div>

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

                                <label for="body" class="control-label col-lg-2">Message <span class="red_star">*</span></label>

                                <div class="col-lg-10">

                                    <?php

                                    $value = set_value("body") ? set_value("body") : (isset($user_detail['body']) ? $user_detail['body'] : '');

                                    $data = array(

                                        'name' => 'body',

                                        'id' => 'bodyckcontent',

                                        'value' => $value,                                        

                                        'class' => 'form-control required',

                                        'placeholder' => 'Message Body',

                                    );

                                    echo form_textarea($data);

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

  width: 96.6%;

}

</style>

<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.css" />

<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.js"></script>



<script>





$("select").multipleSelect({

        filter: true,

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



<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/ckeditor/ckeditor.js"></script>

<script>

        CKEDITOR.replace( 'bodyckcontent',

        {

            height: 500,

            width: 900,

            enterMode : CKEDITOR.ENTER_BR,

            allowedContent: true

        });



</script>