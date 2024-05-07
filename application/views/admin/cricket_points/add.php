<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        Add Points
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                        <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                        <div class=" form">
                            <?php echo form_open('', array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                            <div class="form-group ">
                                <label for="game_type_id" class="control-label col-lg-2">Game Type  <span class="red_star">*</span></label>
                                <div class="col-lg-10">
            
                                    <?php
                               
                                      $opt_all = $this->main_model->cruid_select_array_order("tbl_game_types", "tbl_game_types.name,id", $joins = array(), $cond = "is_deleted = 'N' AND id NOT IN(SELECT game_type_id FROM `tbl_cricket_points` GROUP BY game_type_id)", $order_by = array(), $limit = '', $order_by_other = array());
                                      
                                    $optc[''] = "Please Select";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $datass) {
                                            $optc[$datass->id] = $datass->name;
                                        }
                                    }
                                       $value = set_value("game_type_id") ? set_value("game_type_id") : (isset($user_detail['game_type_id']) ? $user_detail['game_type_id'] : ($this->input->get('gt') ) ? $this->input->get('gt') : '');
                                
                                    echo form_dropdown('game_type_id', $optc, $value, 'class="form-control required" id="game_type_id"');
                                    ?>
                                </div>
                            </div>
                            
                            <?php $points = unserialize(CRICKETPOINTS);
                            foreach($points as $key=>$value)
                            {
                                if(in_array($key, ["strike_rate","economy_rate"])){
                                    continue;
                                }
                            ?>                          
                            <div class="form-group" id="<?= $key; ?>">
                                <label for="<?= $key.'_value'; ?>" class="control-label col-lg-2"><?=$value;?> <span class="red_star">*</span></label>
                                <input type="hidden" value="<?=$value;?>" name="<?=$key.'_key'?>">
                                <div class="col-lg-10">
                                    <?php
                                    $valuez = set_value($key.'_value') ? set_value($key.'_value') : (isset($user_detail['name']) ? $user_detail['name'] : '');
                                    $data = array(
                                        'name' => $key.'_value',
                                        'id' => $key.'_value',
                                        'value' => $valuez ,
                                        'maxlength' => 50,
                                        'class' => 'form-control required number',
                                        'placeholder' => $value,
                                    );
                                    echo form_input($data);
                                    ?>
                                </div>
                            </div>
                            <?php } ?>


                            <div class="form-group ">
                                <label for="Points" class="control-label col-lg-2">Strike Rate<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <div class="col-lg-9">
                                    <div class="row-fluid">
                                        <table id="table_I" cellspacing="20" cellpadding="6">
                                            <tr>
                                                <th style="margin-left: 78px;">Min</th>
                                                <th>Max</th>
                                                <th>Point</th>
                                            </tr>
                                            <tr id="tr_I_0">
                                                <td>
                                                    <input class="input-medium decimalandnumbers per_min_p form-control required" alt="0" id="per_km_min_km_I_0" placeholder="Min" name="per_min_p[I][0]" required="required"  type="text" >
                                                </td>
                                                <td>
                                                    <input class="input-medium decimalandnumbers form-control required" id="per_km_max_km_I_0" placeholder="Max" name="per_max_p[I][0]" required="required" type="text" onfocusout="checkmaxfare(0,this.value,'I')">
                                                </td>
                                                <td>
                                                    <input class="input-medium number per_price form-control required" id="per_km_km_charge_I_0" placeholder="Point Charge" name="per_price[I][0]" required="required" type="text">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                        <a class="btn btn-primary btnAdddmor" id="add_more_I" href="javascript:void(0);" style="margin-top: 5px;">Add More</a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="Points" class="control-label col-lg-2">Economy Rate<span class="red_star">*</span></label>
                                <div class="col-lg-10">
                                    <div class="col-lg-9">
                                    <div class="row-fluid">
                                        <table id="table_K" cellspacing="20" cellpadding="6">
                                            <tr>
                                                <th style="margin-left: 78px;">Min</th>
                                                <th>Max</th>
                                                <th>Point</th>
                                            </tr>
                                            <tr id="tr_K_0">
                                                <td>
                                                    <input class="input-medium decimalandnumbers per_min_p form-control required" alt="0" id="per_km_min_km_K_0" placeholder="Min" name="per_min_p[K][0]" required="required"  type="text" >
                                                </td>
                                                <td>
                                                    <input class="input-medium decimalandnumbers form-control required" id="per_km_max_km_K_0" placeholder="Max" name="per_max_p[K][0]" required="required" type="text" onfocusout="checkmaxfare(0,this.value,'K')">
                                                </td>
                                                <td>
                                                    <input class="input-medium number per_price form-control required" id="per_km_km_charge_K_0" placeholder="Point Charge" name="per_price[K][0]" required="required" type="text">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                        <a class="btn btn-primary btnAdddmor" id="add_more_K" href="javascript:void(0);" style="margin-top: 5px;">Add More</a>
                                    </div>
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
    $("#century").hide();
    $("#century").find("#century_value").val('0');

    $("#catch_and_bowled").hide();
    $("#catch_and_bowled").find("#catch_and_bowled_value").val('0');
});
</script>


<script type="text/javascript">
$(document).ready(function(){


    // $("#per_km_max_km_I_0").prop("readOnly", true);
    // $("#per_km_km_charge_I_0").prop("readOnly", true);
                
    //var altnum = 1;
    $(document).on("click", "a[id^=add_more_]", function(){
        var id = $(this).attr('id');
        var a_data = id.split('_');
        var thisId = a_data['2'];
        //var total_team = document.getElementById('total_team').value;

        
        
        var rowCount = document.getElementById('table_'+thisId).rows.length;
        //document.getElementById('td_'+thisId+'_'+altnum).outerHTML='';
        //alert(rowCount);
        altnumminus =rowCount-2;
        altnum =rowCount-1;
        newaltnum = altnum-1;
        if((document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value)>=0) ){
            document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).readOnly = true;
            document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).readOnly = true;
            //document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).readOnly = true;
            /*if(Number(document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value)==total_team ) {
                document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).readOnly = false;
            }*/
            

        }else{
            //alert("Value Requied For MIN ,MAX  and Point");
            //return false;

        }
        //alert(newaltnum);
        
        /*if(Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+1 > Number(total_team)){
            //document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
            alert("You want add more than team,  so please increase total teams!");
            return false;
        }*/
        if(newaltnum>0){
            //document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
            $('#td_'+thisId+'_'+newaltnum).remove();
        }
        
        var html ='<tr id="tr_'+thisId+'_'+altnum+'"><td><input type="text" class="form-control required input-medium decimalandnumbers per_min_p" alt="'+altnum+'" id="per_km_min_km_'+thisId+'_'+altnum+'" placeholder="Min" name="per_min_p['+thisId+']['+altnum+']" required ></td><td><input type="text" class="form-control required input-medium decimalandnumbers" id="per_km_max_km_'+thisId+'_'+altnum+'" placeholder="Max" name="per_max_p['+thisId+']['+altnum+']" onfocusout="checkmaxfare('+altnum+',this.value,\''+thisId+'\')" required/></td><td><input type="text" class=" form-control required input-medium number per_price" id="per_km_km_charge_'+thisId+'_'+altnum+'" placeholder="Point  Charge" name="per_price['+thisId+']['+altnum+']" required readOnly/></td></tr>';

        var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
        
        $('#table_'+thisId).append(html);
        var numb = 0.01;
        var numbSet =  Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+ numb;
        document.getElementById('per_km_min_km_'+thisId+'_'+altnum).value = numbSet.toFixed(2);
            //document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = true;
        $('#tr_'+thisId+'_'+altnum).append(htmlremove);
        
    });
    
    $(document).on("keyup", '.per_price', function(){
        //CalculetTotalOfPrice();
    });

/*    $(document).on("keyup", '#total_team', function(){
         var len = $(this).val();
            if (len >0) {
                $("#per_km_max_km_I_0").prop("readOnly", false).prop("readOnly", false);
                //$("#per_km_km_charge_I_0").prop("readOnly", false).prop("readOnly", false);
            }
            else{
                $("#per_km_max_km_I_0").prop("readOnly", true);
                //$("#per_km_km_charge_I_0").prop("readOnly", true);
            }
    });*/
    
    $(document).on("keyup", '.decimalandnumbers', function(){
        var val = $(this).val();
        if(isNaN(val)){
         val = val.replace(/[^0-9]/g,'');
         if(val.split('.').length>2) 
             val =val.replace(/\.+$/,"");
        }
        $(this).val(val); 
    });
    
    
});
    

    

function removeKmCharge(thisId,altnum){
    //$("#per_km_charge_add_div_"+divid).remove();
    $('#tr_'+thisId+'_'+altnum).remove();
    altnum = altnum-1;
    if(altnum>0){
    var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
    //document.getElementById('tr_'+thisId+'_'+altnum).innerHTML+=htmlremove;
    $('#tr_'+thisId+'_'+altnum).append(htmlremove);
    }
    //if(altnum == 0){
        document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = false;
        document.getElementById('per_km_max_km_'+thisId+'_'+altnum).readOnly = false;
        document.getElementById('per_km_km_charge_'+thisId+'_'+altnum).readOnly = false;
    //}
           // CalculetTotalOfPrice();
}

function validateRange(km_range){
    var range_error = 0;
    var tempArr = km_range;
    $.each(tempArr, function(i, item){
        var newArr = km_range.splice(i, 1);
        //console.log(newArr);
    });
}

function checkmaxfare(id,value,type) {
    
        element = document.getElementById('per_km_min_km_'+type+'_'+id);
        //var total_team = document.getElementById('total_team').value;
            if (typeof(element) != 'undefined' && element != null)
            {
                //alert(document.getElementById('per_km_max_km_'+type+'_'+i).value);
                elementvalue = element.value;
                /*if(Number(value) > Number(total_team))   {
                     alert("Max should be less than total team "+total_team);
                    document.getElementById('per_km_max_km_'+type+'_'+id).value = '';

                }
                else*/ if(Number(value) >= Number(elementvalue))   {
                    
                } else{
                    document.getElementById('per_km_max_km_'+type+'_'+id).value = '';
                    alert("Max should be grater than Min ");
                    
                }
            }

        }
        
$(document).on("blur", "input[id^=per_km_min_km_]", function(){
        var id = $(this).attr('id');
        var a_data = id.split('_');
        var prifix = a_data['4'];
        var thisId = a_data['5'];
        var altnum = parseInt(thisId)-1;

        var elementvalue = $('#per_km_max_km_'+prifix+'_'+altnum).val();
        var value = $(this).val();

        if(Number(value) <= Number(elementvalue)){
            alert("Min should be grater than last Max "+thisId);
            var numbSet = parseFloat(elementvalue)+0.01;
            $(this).val(numbSet.toFixed(2));
            return true;
        }
});

$(document).on("keyup", "input[id^=per_km_max_km_]", function(){
        evID = $(this).closest('tr').attr("id");
        var gotTD1 = $('#'+evID).find('td:eq(1) input').val();
        if(gotTD1 !="" && gotTD1 !=null){
            $('#'+evID).find('td:eq(2) input').prop("readOnly", false);
        }else{
            $('#'+evID).find('td:eq(2) input').prop("readOnly", true);
        }
        //CalculetTotalOfPrice();
        //console.log($(this).closest('tr'));
    });

function CalculetTotalOfPrice(){
     var totalCls =  $(".price_get");
     var total_price =  $("#total_price");
         var valueArray = [];
            $('.per_price').each(function(){
                evID = $(this).closest('tr').attr("id");
                var gotTD1 = $('#'+evID).find('td:eq(0) input').val();
                var gotTD2 = $('#'+evID).find('td:eq(1) input').val();
                var getBetVeen = (parseInt(gotTD2)-parseInt(gotTD1))+1;
                //console.log(getBetVeen);
                var numberget = parseInt(this.value)*parseInt(getBetVeen);
                if (isNaN(numberget))
                {}else{
                    valueArray.push(numberget);
                }
            });
        if(valueArray.length > 0){
            isNaNnum = valueArray.reduce(getSum);
            if (isNaN(isNaNnum))
            {
                totalCls.text(0);
                total_price.val(0);
             }else{
                totalCls.text(isNaNnum);
                total_price.val(isNaNnum);
             }
        }else{
            totalCls.text(0);
            total_price.val(0);
        }
}
function getSum(total, num) {
  return total + num;
}


</script>