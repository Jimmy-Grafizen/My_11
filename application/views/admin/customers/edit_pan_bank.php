<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Edit Customer PAN & BANK Details
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $customer_id . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                            <div class="form-group ">
                                <label for="is_admin" class="control-label col-lg-2">PAN Card Detail</label>
                            </div>

                            <div class="form-group ">
                                <label for="pain_number" class="control-label col-lg-2">PAN card Number<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("pain_number") ? set_value("pain_number") : (isset($detail['pan']['pain_number']) ? $detail['pan']['pain_number'] : '');
                                    $data = array(
                                        'name' => 'pan[pain_number]',
                                        'id' => 'pain_number',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'PAN Card Number',
                                    );
                                    echo form_input($data);

                                    ?>
                                <input type="hidden" name="pan[id]" value="<?php echo $detail['pan']['id']; ?>">

                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Name<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($detail['pan']['name']) ? $detail['pan']['name'] : '');
                                    $data = array(
                                        'name' => 'pan[name]',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="dob" class="control-label col-lg-2">Date Of Birth  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $dob = set_value("dob") ? set_value("dob") : (isset($detail['pan']['dob']) ? date( DATE_FORMAT_ADMIN,strtotime($detail['pan']['dob'] ) ): '');
                                    $data = array(
                                        'name' => 'pan[dob]',
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
                                <label for="image" class="control-label col-lg-2">Choose Image  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php 
                                    $OldVelImg = set_value("image") ? set_value("image") : (isset($detail['pan']['image']) ? $detail['pan']['image'] : '');
                                    $data = array(
                                        'name' => 'pan_image',
                                        'id' => 'image',
                                        'value' => $OldVelImg,
                                        'class' => 'form-control',
                                        'placeholder' => 'image',
                                        "accept"=>"image/*",
                                    );
                                    echo form_upload($data);
                                    ?>
                                                                        
                                    <p>Allowed image extensions jpg,jpeg,png</p>
                                </div>
                                 <div class="col-lg-2"></div>
                                 <div class="col-lg-10">
                                 <?php 
                                    if(isset($detail['pan']['image'])  && !empty($detail['pan']['image']) )
                                        echo '<a href="'.PANCARD_IMAGE_LARGE_URL.$detail['pan']['image'].'" target="_blank" title="Pan card View"><img src="'.PANCARD_IMAGE_LARGE_URL.$detail['pan']['image'].'" style="width: 150px;height: 150PX;"></a>';
                                    else
                                        echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                ?>
                                </div>
                            </div>

                        <?php if($detail['bank']){ ?>
                            <div class="form-group ">
                                <label for="is_admin" class="control-label col-lg-2">Bank Detail</label>
                            </div>

                            <div class="form-group ">
                                <label for="account_number" class="control-label col-lg-2">Account Number<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("account_number") ? set_value("account_number") : (isset($detail['bank']['account_number']) ? $detail['bank']['account_number'] : '');
                                    $data = array(
                                        'name' => 'bank[account_number]',
                                        'id' => 'account_number',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Account Number',
                                    );
                                    echo form_input($data);
                                    ?>
                                    <input type="hidden" name="bank[id]" value="<?php echo $detail['bank']['id']; ?>">
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="ifsc" class="control-label col-lg-2">IFSC <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("ifsc") ? set_value("ifsc") : (isset($detail['bank']['ifsc']) ? $detail['bank']['ifsc'] : '');
                                    $data = array(
                                        'name' => 'bank[ifsc]',
                                        'id' => 'ifsc',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'IFSC Number',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Name<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($detail['bank']['name']) ? $detail['bank']['name'] : '');
                                    $data = array(
                                        'name' => 'bank[name]',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="image" class="control-label col-lg-2">Choose Image  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php 
                                    $OldVelImg = set_value("image") ? set_value("image") : (isset($detail['bank']['image']) ? $detail['bank']['image'] : '');
                                    $data = array(
                                        'name' => 'bank_image',
                                        'id' => 'image',
                                        'value' => $OldVelImg,
                                        'class' => 'form-control',
                                        'placeholder' => 'image',
                                        "accept"=>"image/*",
                                    );
                                    echo form_upload($data);
                                    ?>
                                            <p>Allowed image extensions jpg,jpeg,png</p>                             

                                </div>
                                 <div class="col-lg-2"></div>
                                 <div class="col-lg-10">
                                 <?php 
                                    if(isset($detail['bank']['image'])  && !empty($detail['bank']['image']) )
                                        echo '<a href="'.BANK_IMAGE_LARGE_URL.$detail['bank']['image'].'" target="_blank" title="Bank Detail View"><img src="'.BANK_IMAGE_THUMB_URL.$detail['bank']['image'].'" style="width: 150px;height: 150PX;"></a>';
                                    else
                                        echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                ?>
                                </div>
                            </div>
                        <?php } ?>

                            
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


<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/css/datepicker.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script type="text/javascript">
    
        $( "#datepicker" ).datepicker({            
            format: '<?= DATE_FORMAT_ADMIN_JS; ?>',
            endDate: '-18y',
            autoclose: true,

        });

</script>