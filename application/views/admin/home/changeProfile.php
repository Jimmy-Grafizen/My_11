<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Manage Profile
                    </header>
                    <div class="panel-body">
                        <?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('admin/home/updateprofile', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                             <div class="form-group ">
                                <label for="firstname" class="control-label col-lg-2">First Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("firstname") ? set_value("firstname") : (isset($record['firstname']) ? $record['firstname'] : '');
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
                                <label for="lastname" class="control-label col-lg-2">Last Name </label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("lastname") ? set_value("lastname") : (isset($record['lastname']) ? $record['lastname'] : '');
                                    $data = array(
                                        'name' => 'lastname',
                                        'id' => 'lastname',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control',
                                        'placeholder' => 'Last Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="mobile" class="control-label col-lg-2">Mobile <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("mobile") ? set_value("mobile") : (isset($record['mobile']) ? $record['mobile'] : '');
                                    $data = array(
                                        'name' => 'mobile',
                                        'value' => $value,
                                        'placeholder' => 'Mobile',
                                        'id' => "mobile",
                                        'class' => 'required form-control',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <!-- <div class="form-group ">
                                <label for="contactus_email" class="control-label col-lg-2">Contact Us Email <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("contactus_email") ? set_value("contactus_email") : (isset($record['contactus_email']) ? $record['contactus_email'] : '');
                                    $data = array(
                                        'name' => 'contactus_email',
                                        'value' => $value,
                                        'placeholder' => 'Contact Us Email',
                                        'id' => "contactus_email",
                                        'class' => 'required form-control email',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="address" class="control-label col-lg-2">Address <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("address") ? set_value("address") : (isset($record['address']) ? $record['address'] : '');
                                    $data = array(
                                        'name' => 'address',
                                        'id' => 'address',
                                        'value' => $value,
                                        'placeholder' => 'Address',
                                        'class' => 'required form-control',
                                    );
                                    echo form_textarea($data);
                                    ?>
                                </div>
                            </div> -->

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Update Profile</button>
                                    <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin" ?>">Cancel</a>
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