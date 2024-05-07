<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add <?=$name ?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><?=$name ?> Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							 <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><?=$name ?> Description  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("description") ? set_value("description") : (isset($user_detail['description']) ? $user_detail['description'] : '');
                                    $data = array(
                                        'name' => 'description',
                                        'id' => 'description',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' description',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="image" class="control-label col-lg-2"><?=$name ?> Image  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("image") ? set_value("image") : (isset($user_detail['image']) ? $user_detail['image'] : '');
                                    $data = array(
                                        'name' => 'image',
                                        'id' => 'image',
                                        'value' => $value,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' image',
										"accept"=>"image/*",
                                    );
                                    echo form_upload($data);
                                    ?>
                                    <p>Allowed image extensions jpg,jpeg,png</p>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="cash_bonus_used_type" class="control-label col-lg-2">Cash Bonus Type<span class="red_star">*</span></label>
                                <div class="col-lg-10">
            
                                    <?php
                               
                                      $opt_all = unserialize(CONTEST_CASH_BONUS_USED_TYPE);
                                      
                                    $opt[''] = "Please Select";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $key=>$val) {
                                            $opt[$key] = $val;
                                        }
                                    }
                                    $value = set_value("cash_bonus_used_type") ? set_value("cash_bonus_used_type") : (isset($user_detail['cash_bonus_used_type']) ? $user_detail['cash_bonus_used_type'] : '');
                                    echo form_dropdown('cash_bonus_used_type', $opt, $value, 'class="form-control required" id="cash_bonus_used_type"');
                                    ?>
                                </div>
                            </div>

                             <div class="form-group ">
                                <label for="cash_bonus_used_value" class="control-label col-lg-2">Cash Bonus Used<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("cash_bonus_used_value") ? set_value("cash_bonus_used_value") : (isset($user_detail['cash_bonus_used_value']) ? $user_detail['cash_bonus_used_value'] : '');
                                    $data = array(
                                        'name' => 'cash_bonus_used_value',
                                        'id' => 'cash_bonus_used_value',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required number',
                                        'placeholder' => $name.' cash_bonus_used_value',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>

                             <div class="form-group ">
                                <label for="confirm_win" class="control-label col-lg-2">Confirm win <span class="red_star">*</span></label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("confirm_win") ? set_value("confirm_win") : (isset($user_detail['confirm_win']) ? $user_detail['confirm_win'] : '');
                                    $data = array(
                                        'name'      => 'confirm_win',
                                        'id'        => 'confirm_win',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                         
                                <label for="is_discounted" class="control-label col-lg-2">Is Discounted</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("is_discounted") ? set_value("is_discounted") : (isset($user_detail['is_discounted']) ? $user_detail['is_discounted'] : '');
                                    $data = array(
                                        'name'      => 'is_discounted',
                                        'id'        => 'is_discounted',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                            </div>
                             <div class="form-group " id="confirm_win_contest_percentagehide">
                                <label for="confirm_win_contest_percentage" class="control-label col-lg-2">Confirm win contest percentages<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("confirm_win_contest_percentage") ? set_value("confirm_win_contest_percentage") : (isset($user_detail['confirm_win_contest_percentage']) ? $user_detail['confirm_win_contest_percentage'] : '');
                                    $data = array(
                                        'name' => 'confirm_win_contest_percentage',
                                        'id' => 'confirm_win_contest_percentage',
                                        'value' => $value,
                                        'maxlength' => 6,
                                        'max' => 100,
                                        'class' => 'form-control required number',
                                        'placeholder' => 'Confirm win contest percentages',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                             <div class="form-group ">
                                <label for="is_compression_allow" class="control-label col-lg-2">Is Compression Allow</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("is_compression_allow") ? set_value("is_compression_allow") : (isset($user_detail['is_compression_allow']) ? $user_detail['is_compression_allow'] : '');
                                    $data = array(
                                        'name'      => 'is_compression_allow',
                                        'id'        => 'is_compression_allow',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                                <label for="is_duplicate_allow" class="control-label col-lg-2">Is Duplicate Allow</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("is_duplicate_allow") ? set_value("is_duplicate_allow") : (isset($user_detail['is_duplicate_allow']) ? $user_detail['is_duplicate_allow'] : '');
                                    $data = array(
                                        'name'      => 'is_duplicate_allow',
                                        'id'        => 'is_duplicate_allow',
                                        'class'     => 'form-control',
                                        'checked'   =>  in_array($value,['Y']),
                                        'value'     =>  'Y',
                                    );
                                    echo form_checkbox($data);
                                    ?>
                                </div>
                         
                                <label for="duplicate_count" class="control-label col-lg-2">Duplicate count</label>
                                <div class="col-lg-2">
                                    <?php
                                    $value = set_value("duplicate_count") ? set_value("duplicate_count") : (isset($user_detail['duplicate_count']) ? $user_detail['duplicate_count'] : '');
                                    $data = array(
                                        'name'      => 'duplicate_count',
                                        'id'        => 'duplicate_count',
                                        'class'     => 'form-control number',
                                        'value' => $value,
                                        'maxlength' => 4,
                                    );
                                    echo form_input($data);
                                    ?>
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
<script>

    $(document).ready(function(){
        if ($('#confirm_win').is(':checked')) {
            $("#confirm_win_contest_percentagehide").hide();
            $("#confirm_win_contest_percentage").val('0.00');
        } else {
            $("#confirm_win_contest_percentagehide").show();  
        }

        $(document).on('click', '#confirm_win', function() {
            if($(this).is(':checked')){
                $("#confirm_win_contest_percentagehide").hide();
                $("#confirm_win_contest_percentage").val('0.00');
            } else {
                $("#confirm_win_contest_percentagehide").show();
                $("#confirm_win_contest_percentage").val('');
            }
        });

    });

</script>