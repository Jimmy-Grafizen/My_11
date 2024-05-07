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
                        Add Admin
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="usergroup" class="control-label col-lg-2">Group  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $opt_all = $this->main_model->cruid_select_array_order("tbl_groups", "tbl_groups.name,id", $joins = array(), $cond = array("tbl_groups.user_id" => $this->session->userdata('adminId')), $order_by = array(), $limit = '', $order_by_other = array());
                                    $opt[''] = "Select Group";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("usergroup") ? set_value("usergroup") : (isset($user_detail['usergroup']) ? $user_detail['usergroup'] : '');
                                    echo form_dropdown('usergroup', $opt, $value, 'class="form-control required" id="usergroup"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="firstname" class="control-label col-lg-2">First Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("firstname") ? set_value("firstname") : (isset($user_detail['firstname']) ? $user_detail['firstname'] : '');
                                    $data = array(
                                        'name' => 'firstname',
                                        'id' => 'firstname',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'First Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div><div class="form-group ">
                                <label for="lastname" class="control-label col-lg-2">Last Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("lastname") ? set_value("lastname") : (isset($user_detail['lastname']) ? $user_detail['lastname'] : '');
                                    $data = array(
                                        'name' => 'lastname',
                                        'id' => 'lastname',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Last Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div><div class="form-group ">
                                <label for="email" class="control-label col-lg-2">Email Address   <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("email") ? set_value("email") : (isset($user_detail['email']) ? $user_detail['email'] : '');
                                    $data = array(
                                        'id' => 'email',
                                        'name' => 'email',
                                        'value' => $value,
                                        'class' => 'form-control required email',
                                        'maxlength' => 255,
                                        'placeholder' => 'Email Address',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div><div class="form-group ">
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
                            </div><div class="form-group ">
                                <label for="mobile" class="control-label col-lg-2">Phone Number  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("mobile") ? set_value("mobile") : (isset($user_detail['mobile']) ? $user_detail['mobile'] : '');
                                    $data = array(
                                        'name' => 'mobile',
                                        'id' => 'mobile',
                                        'value' => $value,
                                        'maxlength' => 20,
                                        'class' => 'form-control number required',
                                        'placeholder' => 'Phone Number',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <!-- <div class="form-group ">
                                <label for="gender" class="control-label col-lg-2">Gender  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <span class="for-male">
                                        <label for="male">Male </label>
                                        <?php
                                        $value = set_value("gender") ? set_value("gender") : (isset($user_detail['gender']) ? $user_detail['gender'] : '');
                                        $data = array(
                                            'name' => 'gender',
                                            'id' => 'male',
                                            'value' => 'Male',
                                            'class' => 'required',
                                            'checked' => ($value == 'Male' ? TRUE : FALSE)
                                        );
                                        echo form_radio($data);
                                        ?>
                                    </span>
                                    <span class="for-female">
                                        <label for="female">Female </label>
                                        <?php
                                        $value = set_value("gender") ? set_value("gender") : (isset($user_detail['gender']) ? $user_detail['gender'] : '');
                                        $data = array(
                                            'name' => 'gender',
                                            'id' => 'female',
                                            'value' => 'Female',
                                            'class' => 'required',
                                            'checked' => ($value == 'Female' ? TRUE : FALSE)
                                        );
                                        echo form_radio($data);
                                        ?>
                                    </span>
                                </div>
                            </div><div class="form-group ">
                                <label for="dob" class="control-label col-lg-2">Date Of Birth <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("dob") ? set_value("dob") : (isset($user_detail['dob']) ? $user_detail['dob'] : '');
                                    $data = array(
                                        'name' => 'dob',
                                        'id' => 'datepicker',
                                        'value' => $value,
                                        'class' => 'required datepicker search_fields form-control',
                                        'placeholder' => 'Select Date',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="country" class="control-label col-lg-2">Country  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $opt_all = $this->main_model->cruid_select_array_order("tbl_geo_countries", "tbl_geo_countries.name,id", $joins = array(), $cond = array("status" => 'A'), $order_by = array(), $limit = '', $order_by_other = array());
                                    $opt[''] = "Select Country";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $country_val = $this->session->userdata("country");
                                    $value = set_value("country") ? set_value("country") : (isset($user_detail['country']) ? $user_detail['country'] : '');
                                    echo form_dropdown('country', $opt, $value, 'class="form-control required" id="country"');
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="state" class="control-label col-lg-2">State/Province  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("state") ? set_value("state") : (isset($user_detail['state']) ? $user_detail['state'] : '');
                                    $data = array(
                                        '' => 'Please Select',
                                    );
                                    echo form_dropdown('state', $data, $value, 'class="form-control reason required "');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="city" class="control-label col-lg-2">City  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("city") ? set_value("city") : (isset($user_detail['city']) ? $user_detail['city'] : '');
                                    $data = array(
                                        'name' => 'city',
                                        'id' => 'city',
                                        'value' => $value,
                                        'maxlength' => 255,
                                        'class' => 'form-control required',
                                        'placeholder' => 'City',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div> -->
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