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
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
    <?php foreach($result['list'] as $label)
    { ?>
                           <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Level <?=$label->c_level; ?>   <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    
                                    $data = array(
                                        'name' => 'c_commssion[]',
                                        'id' => 'name',
                                        'value' => $label->c_commssion,
                                        'maxlength' => 3,
                                        'class' => 'form-control required',
                                        'placeholder' => $name.' Name',

                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
        <?php } ?>
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