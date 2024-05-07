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
$country = set_value("country") ? set_value("country") : (isset($user_detail['country']) ? $user_detail['country'] : '');
$reason = set_value("state") ? set_value("state") : (isset($user_detail['state']) ? $user_detail['state'] : '');
?>
        // get all states from countries 
        $("#country").change(function() {
            $(".reason").load("<?php echo HTTP_PATH . "admin/home/getstate/" ?>" + $("#country").val() + "/" + "<?php echo $reason; ?>");
        });

<?php
if ($reason) {
    ?>
                $(".reason").load("<?php echo HTTP_PATH . "admin/home/getstate/" ?><?php echo $country; ?>/" + "<?php echo $reason; ?>");
    <?php
}
?>
    });
</script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/data-tables/DT_bootstrap.css" />
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add Team
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Team Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Team Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Select Logo <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                  <input name="images" type="file" accept="image/*">
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
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>