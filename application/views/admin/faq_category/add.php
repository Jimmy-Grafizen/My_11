<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add FQA
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Title<span class="red_star">*</span></label>
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
                                <label for="name" class="control-label col-lg-2">Description  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("description") ? set_value("description") : (isset($user_detail['description']) ? $user_detail['description'] : '');
                                    $data = array(
                                        'name' => 'description',
                                        'id' => 'description',
                                        'value' => $value,
                                        'maxlength' => 250,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' description',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="image" class="control-label col-lg-2"> Image  <span class="red_star">*</span></label>
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