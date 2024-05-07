<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Change Your Password
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('admin/home/changePassword', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="opassword" class="control-label col-lg-2">Old Password  <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'name' => 'opassword',
                                        'id' => 'opassword',
                                        'placeholder' => 'Old Password',
                                        'class' => 'form-control required',
                                        'value' => set_value("opassword") ? set_value("opassword") : ""
                                    );
                                    echo form_password($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="password" class="control-label col-lg-2">Password <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'name' => 'password',
                                        'id' => 'password',
                                        'placeholder' => 'Password',
                                        'minlength' => 8,
                                        'class' => 'form-control required',
                                        'value' => set_value("password")?set_value("password"):$this->session->flashdata('pass')
                                    );
                                    echo form_password($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="cpassword" class="control-label col-lg-2">Confirm Password <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'name' => 'cpassword',
                                        'equalTo' => '#password',
                                        'placeholder' => 'Confirm Password',
                                        'class' => 'form-control required',
                                        'value' => set_value("cpassword")?set_value("cpassword"):$this->session->flashdata('cpass')
                                    );
                                    echo form_password($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Change Password</button>
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