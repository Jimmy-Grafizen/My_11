
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
                                <label for="name" class="control-label col-lg-2"><?=$name ?> Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                           
                            <div class="form-group " style="display:none">
                                <label for="team_ids" class="control-label col-lg-2">Add Series Teams<span class="red_star">*</span></label>
                                <div class="col-lg-10">			
                                    <?php
									  $opt_all = $this->main_model->cruid_select_array_order("$tbl_cricket_teams", "$tbl_cricket_teams.name,id", $joins = array(), $cond = array("status" => 'A',"is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                   $opts= [];
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opts[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("team_ids") ? set_value("team_ids") : (isset($team_ids) ? $team_ids : '');
                                    echo form_multiselect('team_ids[]', $opts, $value, 'class="required" id="team_ids"');
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
<style>
select {
  width: 96.6%;
}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/multiselect/multiple-select.js"></script>

<script>
$(function () { 
    $('select').multipleSelect({
	  filter: true,
	  isOpen: true,
 
     })
});
</script>