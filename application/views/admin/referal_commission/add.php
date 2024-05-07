<script>
    $(document).ready(function() {
    <?php
        $game_id = set_value("game_id") ? set_value("game_id") : (isset($user_detail['game_id']) ? $user_detail['game_id'] : '');
        $reason = set_value("name") ? set_value("name") : (isset($user_detail['name']) ? $user_detail['name'] : '');
    ?>
        // get all states from Sports3 
        $("#game_id").change(function() {
           $.get("<?php echo HTTP_PATH . "admin/referal_commission/game_type/" ?>" + $("#game_id").val(), function(data, status){
        	$(".reason").html(data);
                //alert("Data: " + data + "\nStatus: " + status);
            });
        });

<?php
if ($reason) {
    ?>
        $(".reason").load("<?php echo HTTP_PATH . "admin/states/getstates/" ?><?php echo $game_id; ?>/" + "<?php echo $reason; ?>");
    <?php
}
?>
    });
</script>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add <?=$name?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>

							
                            <div class="form-group ">
                                <label for="game_id" class="control-label col-lg-2">Sports  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
			
                                    <?php
                               
									  $opt_all = $this->main_model->cruid_select_array_order("tbl_games", "name,id", $joins = array(), $cond = array("is_deleted" => 'N'), $order_by = array(), $limit = '', $order_by_other = array());
									  
                                    $opt[''] = "Please Select Sport";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $opt[$datass->id] = $datass->name;
                                        }
                                    }
                                    $value = set_value("game_id") ? set_value("game_id") : (isset($user_detail['game_id']) ? $user_detail['game_id'] : '');
                                    echo form_dropdown('game_id', $opt, $value, 'class="form-control required" id="game_id"');
                                    ?>
                                </div>
                            </div>
							
                            <div class="form-group ">
                                <label for="game_type_id" class="control-label col-lg-2">Game Type<span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<select name="game_type_id" class="reason form-control required valid" id="game_type_id">
									
									</select>
                                   
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="commission" class="control-label col-lg-2">Commission % Percent <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("commission") ? set_value("commission") : (isset($user_detail['commission']) ? $user_detail['commission'] : '');
                                    $data = array(
                                        'name' => 'commission',
                                        'id' => 'commission',
                                        'value' => $value,
                                        'max' => 100,
                                        'class' => 'form-control required number',
                                        'placeholder' => 'Enter Commission',
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