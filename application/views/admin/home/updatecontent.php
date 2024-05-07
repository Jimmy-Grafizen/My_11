<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Manage Home Page Contents
                    </header>
                    <div class="panel-body">
                        <?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('admin/home/updatecontent', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="top" class="control-label col-lg-2">Top Text <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("top") ? set_value("top") : (isset($record['top']) ? $record['top'] : '');
                                    $data = array(
                                        'id' => 'top',
                                        'name' => 'top',
                                        'placeholder' => 'Top Text',
                                        'value' => $value,
                                        'class' => 'required form-control'
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="bottom" class="control-label col-lg-2">Bottom Text <span class="require">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("bottom") ? set_value("usebottomname") : (isset($record['bottom']) ? $record['bottom'] : '');
                                    $data = array(
                                        'name' => 'bottom',
                                        'id' => 'bottom',
                                        'placeholder' => 'Bottom Text',
                                        'value' => $this->input->post('bottom') ? $this->input->post('bottom') : $value,
                                        'class' => 'required form-control'
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Update Content</button>
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