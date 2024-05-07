<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add Email Template
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

							
                            <div class="form-group ">
                                <label for="country_id" class="control-label col-lg-2">Template Type<span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<select name="type" class="reason form-control search_fields required valid" id="state_id">
										<option value="">Please Select</option>
										<?php $points = unserialize(EMAILTEMPLATE);
											foreach($points as $key=>$value)
											{
											?> 
										<option value="<?=$key?>"><?=$value?></option>
											<?php } ?>
									</select>
                                   
                                </div>
                            </div>
							<!--<div class="form-group ">
                                <label for="country_id" class="control-label col-lg-2">Template Default<span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<select name="is_default" class="reason form-control search_fields required valid" id="state_id">
										<option value="">Please Select</option>
										<?php/*  $points = unserialize(EMAILTEMPLATEDEFAULT);
											foreach($points as $key=>$value)
											{ */
											?> 
										<option value="<?//=$key?>"><?//=$value?></option>
											<?php //} ?>
									</select>
                                   
                                </div>
                            </div>--->
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Template Title <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("title") ? set_value("title") : (isset($user_detail['title']) ? $user_detail['title'] : '');
                                    $data = array(
                                        'name' => 'title',
                                        'id' => 'title',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Template Title',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Template Subject</label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("subject") ? set_value("subject") : (isset($user_detail['subject']) ? $user_detail['subject'] : '');
                                    $data = array(
                                        'name' => 'subject',
                                        'id' => 'subject',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control',
                                        'placeholder' => 'Template Subject',
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
							<div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Template Content <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("content") ? set_value("content") : (isset($user_detail['content']) ? $user_detail['content'] : '');
                                    $data = array(
                                        'name' => 'content',
                                        'id' => 'ckcontent',
                                        'value' => $value,                                        
                                        'class' => 'form-control required',
                                        'placeholder' => 'Template Content',
                                    );
                                    echo form_textarea($data);
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
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
        CKEDITOR.replace( 'ckcontent',
        {
            height: 500,
            width: 900,
            enterMode : CKEDITOR.ENTER_BR,
            allowedContent: true
        });

</script>