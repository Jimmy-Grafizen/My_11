<script>
    $(document).ready(function() {
        $.validator.addMethod("pass", function(value, element) {
            return  this.optional(element) || (/.{8,}/.test(value) && /((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20})/.test(value));
        }, "Password minimum length must be 8 characters and combination of 1 special character, 1 lowercase character, 1 uppercase character and 1 number.");

        // page date picker feature
        $( "#datepicker" ).datepicker({            
            format: '<?= DATE_FORMAT_ADMIN_JS; ?>',
            endDate: '-18y',
            autoclose: true,
        });

           
<?php
$country = set_value("country") ? set_value("country") : (isset($user_detail['country']) ? $user_detail['country'] : '');
$country = $country_id;
$reason = set_value("state") ? set_value("state") : (isset($user_detail['state']) ? $user_detail['state'] : '');
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
if ($country>0) {
    ?>
    $.get("<?php echo HTTP_PATH . "admin/states/getstates/" ?>" + $("#country").val(), function(data, status){
        $(".reason").html(data);
            //alert("Data: " + data + "\nStatus: " + status);
        });
    <?php
}
?>
<?php
if ($reason) {
    ?>
                $(".reason").load("<?php echo HTTP_PATH . "admin/states/getstates/" ?><?php echo $country; ?>/" + "<?php echo $reason; ?>");
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
                        Add Customer
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                         <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                            <div class="form-group ">
                                <label for="is_admin" class="control-label col-lg-2">Is Admin  <span class="red_star">*</span></label>
                                <div class="col-lg-1">
                                    <?php
                                    $value = set_value("is_admin") ? set_value("is_admin") : (isset($user_detail['is_admin']) ? $user_detail['is_admin'] : '');
                                    $data = array(
                                        'name' => 'is_admin',
                                        'id' => 'is_admin',
                                        'value' => 1,
                                        'checked'=>($value == '1'),
                                        'class' => 'form-control',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                                <label for="is_fake" class="control-label col-lg-2">Is Fake  <span class="red_star">*</span></label>
                                <div class="col-lg-1">
                                    <?php
                                    $value = set_value("is_fake") ? set_value("is_fake") : (isset($user_detail['is_fake']) ? $user_detail['is_fake'] : '');
                                    $data = array(
                                        'name' => 'is_fake',
                                        'id' => 'is_fake',
                                        'value' => 1,
                                        'checked'=>($value == '1'),
                                        'class' => 'form-control',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="firstname" class="control-label col-lg-2">First Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $firstname = set_value("firstname") ? set_value("firstname") : (isset($user_detail['firstname']) ? $user_detail['firstname'] : '');
                                    $data = array(
                                        'name' => 'firstname',
                                        'id' => 'firstname',
                                        'value' => $firstname,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'First Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							
							<div class="form-group ">
                                <label for="lastname" class="control-label col-lg-2">Last Name<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $lastname = set_value("lastname") ? set_value("lastname") : (isset($user_detail['lastname']) ? $user_detail['lastname'] : '');
                                    $data = array(
                                        'name' => 'lastname',
                                        'id' => 'lastname',
                                        'value' => $lastname,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Last Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="email" class="control-label col-lg-2">Email Address   <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $email = set_value("email") ? set_value("email") : (isset($user_detail['email']) ? $user_detail['email'] : '');
                                    $data = array(
                                        'id' => 'email',
                                        'name' => 'email',
                                        'value' => $email,
                                        'class' => 'form-control required email',
                                        'maxlength' => 255,
                                        'placeholder' => 'Email Address',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="dob" class="control-label col-lg-2">Date Of Birth  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $dob = set_value("dob") ? set_value("dob") : (isset($user_detail['dob']) ? $user_detail['dob'] : '');
                                    $data = array(
                                        'name' => 'dob',
                                        'id' => 'datepicker',
                                        'value' => $dob,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Date Of Birth',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							
							<div class="form-group ">
                                <label for="addressline1" class="control-label col-lg-2">Address line 1</label>
                                <div class="col-lg-10">
                                    <?php
                                    $addressline1 = set_value("addressline1") ? set_value("addressline1") : (isset($user_detail['addressline1']) ? $user_detail['addressline1'] : '');
                                    $data = array(
                                        'name' => 'addressline1',
                                        'id' => 'addressline1',
                                        'value' => $addressline1,
                                        'class' => 'form-control',
                                        'placeholder' => 'Address line 1',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="addressline2" class="control-label col-lg-2">Address line 2</label>
                                <div class="col-lg-10">
                                    <?php
                                    $addressline2 = set_value("addressline2") ? set_value("addressline2") : (isset($user_detail['addressline2']) ? $user_detail['addressline2'] : '');
                                    $data = array(
                                        'name' => 'addressline2',
                                        'id' => 'addressline2',
                                        'value' => $addressline2,
                                        'class' => 'form-control',
                                        'placeholder' => 'Address line 2',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group " style="display: none;">
                                <label for="country" class="control-label col-lg-2">Countries  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("tbl_countries", "tbl_countries.name,id", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select Country";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("country") ? set_value("country") : (isset($user_detail['country']) ? $user_detail['country'] : '');
                                    echo form_dropdown('country', $opt, $country_id, 'class="form-control required" id="country"');
                                    ?>
                                </div>
                            </div>
							 <div class="form-group ">
                                <label for="country_id" class="control-label col-lg-2">States  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<select name="state" class="reason form-control required valid" id="state_id">
									
									</select>
                                   
                                </div>
                            </div>
							
							<div class="form-group ">
                                <label for="city" class="control-label col-lg-2">City <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $city = set_value("city") ? set_value("city") : (isset($user_detail['city']) ? $user_detail['city'] : '');
                                    $data = array(
                                        'name' => 'city',
                                        'id' => 'city',
                                        'value' => $city,
                                        'class' => 'form-control required',
                                        'placeholder' => 'City',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="pincode" class="control-label col-lg-2">Pin Code <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $pincode = set_value("pincode") ? set_value("pincode") : (isset($user_detail['pincode']) ? $user_detail['pincode'] : '');
                                    $data = array(
                                        'name' => 'pincode',
                                        'id' => 'pincode',
                                        'value' => $pincode,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Pin Code',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							
							
							<div class="form-group ">
                                <label for="country_mobile_code" class="control-label col-lg-2">Country Code <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $country_mobile_code = set_value("country_mobile_code") ? set_value("country_mobile_code") : (isset($user_detail['country_mobile_code']) ? $user_detail['country_mobile_code'] : '');
                                    $data = array(
                                        'name' => 'country_mobile_code',
                                        'id' => 'country_mobile_code',
                                        'value' => $country_mobile_code,
                                        'maxlength' => 20,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Country Code',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="phone" class="control-label col-lg-2">Phone Number  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $phone = set_value("phone") ? set_value("phone") : (isset($user_detail['phone']) ? $user_detail['phone'] : '');
                                    $data = array(
                                        'name' => 'phone',
                                        'id' => 'phone',
                                        'value' => $phone,
                                        'maxlength' => 20,
                                        'class' => 'form-control number required',
                                        'placeholder' => 'Phone Number',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							
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
                             <div class="form-group ">
                                <label for="image" class="control-label col-lg-2">Choose Image</label>
                                <div class="col-lg-10">
                                    <?php /*
                                    $image = set_value("image") ? set_value("image") : (isset($user_detail['image']) ? $user_detail['image'] : '');
                                    $data = array(
                                        'name' => 'image',
                                        'id' => 'image',
                                        'value' => $image,
                                        'class' => 'form-control',
                                        'placeholder' => 'image',
										"accept"=>"image/*",
                                    );
                                    echo form_upload($data);  */
                                    ?>
									
<div class="row imageload">
<?php 
	foreach ($profile_pictures as $pictures) { ?>
			<div class="col-xs-3 col-sm-2 col-md-1 col-xl-1 nopad text-center">
				<label class="image-radio">
					<img class="img-responsive" src="<?php echo CUSTOMER_IMAGE_THUMB_URL.$pictures['image'] ?>">
						<input type="radio" name="image_radio" value="<?php echo $pictures['image'];?>">
							<i class="fa fa-check hidden"></i>
				</label>
			</div>
<?php        
	}
?>			
</div>
									
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

<script type="text/javascript">

    // sync the input state
    $(document).on("click",".image-radio", function(e){
        $(".image-radio").removeClass('image-radio-checked');
        $(this).addClass('image-radio-checked');
        var $radio = $(this).find('input[type="radio"]');
        $radio.prop("checked",!$radio.prop("checked"));

        e.preventDefault();
    });
	
	
</script>

<style>
i.image_change{
    margin-top: 9px;
    font-size: 22px;
    cursor: pointer;
}

.image-radio {
    cursor: pointer;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    border: 4px solid transparent;
    margin-bottom: 0;
    outline: 0;
}
.image-radio input[type="radio"] {
   /* display: none;*/
}
.image-radio-checked {
    border-color: #4783B0;
}
.image-radio .fa {
  position: absolute;
  color: #4A79A3;
  background-color: #fff;
  padding: 10px;
  top: 0;
  right: 15px;
  opacity:0.8;
}
.image-radio-checked .fa {
  display: block !important;
  visibility: visible !important;
}

</style>