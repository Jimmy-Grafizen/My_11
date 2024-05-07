<script>
    $(document).ready(function() {
        $.validator.addMethod("pass", function(value, element) {
            return  this.optional(element) || (/.{8,}/.test(value) && /((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20})/.test(value));
        }, "Password minimum length must be 8 characters and combination of 1 special character, 1 lowercase character, 1 uppercase character and 1 number.");

        // page date picker feature
        $( "#datepicker" ).datepicker({            
            format: 'yyyy-mm-dd',
            endDate: "1d"});
           
<?php
$country = set_value("country_id") ? set_value("country_id") : (isset($user_detail['country_id']) ? $user_detail['country_id'] : '');
$reason = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
?>
        // get all states from countries 
        $("#country").change(function() {
			// alert($("#country").val());
           // $(".reason").load("<?php echo HTTP_PATH . "admin/states/getstates/" ?>" + $("#country").val());
   $.get("<?php echo HTTP_PATH . "admin/states/getstates/" ?>" + $("#country").val(), function(data, status){
	$(".reason").html(data);
        //alert("Data: " + data + "\nStatus: " + status);
    });
        });

<?php
if ($reason) {
    ?>
              //  $(".reason").load("<?php echo HTTP_PATH . "admin/states/getstates/" ?><?php echo $country; ?>/" + "<?php echo $reason; ?>");
    <?php
}
?>
    });
</script>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit city
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

							<div class="form-group ">
                                <label for="country_id" class="control-label col-lg-2">Countries  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("tbl_countries", "tbl_countries.name,id", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $optc[''] = "Please Select Country";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $optc[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("country_id") ? set_value("country_id") : (isset($user_detail['country_id']) ? $user_detail['country_id'] : '');
                                    echo form_dropdown('country_id', $optc, $value, 'class="form-control required" id="country"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="country_id" class="control-label col-lg-2">States  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               $opt_all =null;
									  $opt_all = $this->main_model->cruid_select_array_order("tbl_states", "tbl_states.name,id", $joins = array(), $cond = array("is_deleted" => 'N',"country_id" => $value), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select State";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("state_id") ? set_value("state_id") : (isset($user_detail['state_id']) ? $user_detail['state_id'] : '');
                                    echo form_dropdown('state_id', $opt, $value, 'class="reason form-control required" id="state_id"');
                                    ?>
                                </div>
                            </div>
							
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                    <input type="hidden" name="permissions" id="permissions">
                                </div>
                            </div>

                            
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-danger" type="submit">Save</button>
                                        <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin/countries" ?>">Cancel</a>
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