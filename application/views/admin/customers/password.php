<script>
    $(document).ready(function() {
        $.validator.addMethod("pass", function(value, element) {
            return  this.optional(element) || (/.{8,}/.test(value) && /((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20})/.test(value));
        }, "Password minimum length must be 8 characters and combination of 1 special character, 1 lowercase character, 1 uppercase character and 1 number.");

        // page date picker feature
        $( "#datepicker" ).datepicker({            
            format: 'yyyy-mm-dd',
            endDate: "1d"});

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
                       Customer Change Password
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                          <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                           <div class="form-group ">
                                <label for="password" class="control-label col-lg-2">Password  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'class' => 'required form-control pass',
                                        'name' => 'password',
                                        'id' => 'password',
                                        'minlength' => 8,
                                        'placeholder' => "Password",
                                    );
                                    echo form_password($data);
                                    ?>
                                    <span class="help-block">Password must be 8 to 15 characters and contain at least one special character, one uppercase, one lowercase and one number</span>
                                </div>
                            </div><div class="form-group ">
                                <label for="cpassword" class="control-label col-lg-2">Confirm Password   <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'class' => 'required form-control',
                                        'name' => 'cpassword',
                                        'id' => 'cpassword',
                                        'placeholder' => "Confirm password",
                                        'equalTo' => '#password'
                                    );
                                    echo form_password($data);
                                    ?> 
                                </div>
                            </div>
							
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Save</button>
                                   <a class="btn btn-default" href="<?php echo HTTP_PATH .'admin/customers'; ?>">Cancel</a>
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