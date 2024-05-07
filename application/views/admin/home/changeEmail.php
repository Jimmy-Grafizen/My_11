<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Change Your Email
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('admin/home/changeEmail', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                            
                            <div class="form-group ">
                                <label class="control-label col-lg-2">Old Email </label>
                                <div class="col-lg-10">
                                    <div><?php echo $old_email; ?> </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="email" class="control-label col-lg-2">New Email <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'name' => 'email',
                                        'id' => 'email',
                                        'placeholder' => 'New Email',
                                        'class' => 'form-control required email',
                                        'value' => set_value("email")
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="cemail" class="control-label col-lg-2">Confirm Email <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $data = array(
                                        'name' => 'cemail',
                                        'id' => 'cemail',
                                        'equalTo' => '#email',
                                        'class' => 'form-control required email',
                                        'placeholder' => 'Confirm Email Address',
                                        'value' => set_value("cemail")
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Change Email</button>
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