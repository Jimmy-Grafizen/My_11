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
                                <label for="series_id" class="control-label col-lg-2">Series Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
									<input class="form-control required" type="text" value="<?=$match_detail['series_name']?>"  readonly="readonly" required="required" name="series_name" />    
                                </div>
                            </div>
							
							
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">Match Name  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <?php
                                    $value = set_value("name") ? set_value("name") : (isset($match_detail['name']) ? $match_detail['name'] : '');
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
                            
							<div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-danger" type="submit">Save</button>
                                    <!--button class="btn btn-default" type="reset">Reset</button-->
                                </div>
                            </div>
							
							
							
                            <?php echo form_close(); ?>
                        </div>

<form>
	<div class="form-group row ">
	<div class="col-lg-6">
	div
	<div class="col-lg-6 col-offset-2">
	<h4>Team <span class="red_star">*</span></h4>
	</div>
		<div class="col-lg-10">
			<input class="form-control required" type="text" value="<?=$match_detail['series_name']?>"  readonly="readonly" required="required" name="series_name" />    
		</div>
	</div>
	<div class="col-lg-6">
	<h4>Team <span class="red_star">*</span></h4>
		<!--label for="series_id" class="control-label col-lg-2">Series Name  </label-->
		<div class="col-lg-10">
			<input class="form-control required" type="text" value="<?=$match_detail['series_name']?>"  readonly="readonly" required="required" name="series_name" />    
		</div>
	</div>
</form>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<link rel="stylesheet" href="<?php echo HTTP_PATH; ?>assets/select2/select2.min.css" />
<script type="text/javascript" src="<?php echo HTTP_PATH; ?>assets/select2/js/select2.full.min.js"></script>



<script>
function matcheSeriesFunc(){    
        var container = $("#metch_series_id");
        $.ajax({
            type: 'GET', 
            url: '<?=base_url($prefixUrl.'get_matche_series');?>', 
            dataType: 'json',
            success: function(data) {
                if(!data.hasOwnProperty("error") ){
                $.each(data.matches, function(key, value) {
                    //if(value.squad== true && value.matchStarted == false){
                        container.append($("<option></option>")
                        .attr("value",value['unique_id'])
                        .text(value['title'] ) );
                    //}
                });             
                container.select2();
              }
            }
        });
             
      }
$(document).ready(matcheSeriesFunc);

function matchesFunc(thisdata){ 
    var container = $("#metch_container");
        container.empty();
        container.append( $("<option></option>")
        .attr("value","").text("Please select Match") );
        container.select2();
        
    if(thisdata.value !=""){
        var series_id = thisdata.value;
        $.ajax({
            type: 'GET', 
            url: '<?=base_url($prefixUrl.'get_matches');?>/'+series_id, 
            dataType: 'json',
            success: function(data) {
                if(!data.hasOwnProperty("error") ){
                $.each(data.matches, function(key, value) {
                    if(value.squad== true && value.matchStarted == false){
                        container.append($("<option></option>")
                        .attr("value",JSON.stringify(value))
                        .text(value['title'] +" [" + value['series_data']['title'] + "] ("+ dateTimeFormate(value['dateTimeGMT'])+" )" ));
                    }
                });             
                container.select2();
              }
            }
        });
    }
} 

//$(document).ready(matchesFunc);

function dateTimeFormate(timeStringGet){
    var msec = Date.parse(timeStringGet);
    var d = new Date(msec);
    return d.toLocaleString();
}

</script>