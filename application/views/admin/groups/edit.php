
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
                        Edit Group
                    </header>
                    <div class="panel-body"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open_multipart('' . $user_detail['id'] . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => 'name',
                                        'id' => 'name',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required',
                                        'placeholder' => 'Name',
                                    );
                                    echo form_input($data);
                                    ?>
                                    <input type="hidden" name="permissions" id="permissions">
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"  style="margin-top: 20px;">Permissions </label>
                                <div class="col-lg-10">
                                <!-- <ul class="nav nav-pills labels-info inbox-divider"> -->
                                        <?php
                                        foreach ($groups as $key => $val) {?>
                                            <div class="col-lg-12 heading" style="margin-top: 15px;padding:5px; border-bottom: 1px solid #eee "> <b><?php echo $key; ?></b> </div>
                                            <div class="col-lg-12" style="margin-left: 20px">
                                                <?php
                                                // get sub items
                                                if(!empty($val)) {
                                                    foreach ($val as $id => $label) {
                                                        # code...
                                                        ?>
                                                        <div class="col-lg-4 links" style="padding: 5px"> <a href="javascript:void(0)" data-node="<?php echo "['".$key."'][$id].level"; ?>"  data-key="<?php echo $key."_".$id; ?>"  data-level="0"  class="change-groups fa"> <?php echo $label['label'] ?> </a> </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                <!-- </ul> -->
                                </div>
                            </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-danger" type="submit">Save</button>
                                        <a class="btn btn-default" href="<?php echo HTTP_PATH . "admin/groups" ?>">Cancel</a>
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
    $(document).ready(function(){
        var json_data =  <?php echo json_encode($groups);?>;
        var old_json_data =  $.parseJSON(<?php echo json_encode($user_detail['permissions']);?>)

        // change old json data to new json
        $.each(old_json_data, function(key, val) {
            $.each(val, function(k, v) { //console.log(typeof eval("json_data['"+key+"']"))
                if(typeof eval("json_data['"+key+"']") != 'undefined')
                    if(typeof eval("json_data['"+key+"']"+ "["+k+"]") != 'undefined')
                        if(typeof eval("json_data['"+key+"']" + "["+k+"].level") != 'undefined'){
                            eval("json_data['"+key+"']" + "["+k+"].level=" + v.level); 
                            $("[data-key='"+key + "_"+k+"']").attr('data-level', v.level);
                        }
            })  
        })


        $("#permissions").val(JSON.stringify(json_data));
        $(".change-groups").click(function(){
            var level = $(this).attr('data-level');
            var node = $(this).attr('data-node');

            if(level == '0'){
                $(this).attr('data-level', '1');
                eval("json_data"+node + "=" + 1); 
            }
            else {
                $(this).attr('data-level', '0');
                eval("json_data"+node + "=" + 0);
            }
            $("#permissions").val(JSON.stringify(json_data));
        })
    })
</script>