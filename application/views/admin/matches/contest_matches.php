<style type="text/css">
        .childshow td,.highlight__{ background-color: #e0e0e0 !important;}
        .new_table thead tr th{min-width:120px !important}
</style>
<section id="main-content">
   <section class="wrapper">
      <!-- page start-->
      <div class="row">
         <div class="col-lg-12">
            <?php echo $this->breadcrumbs->show(); ?>
            <section class="panel">
               <header class="panel-heading clearfix">
                  Add Contest with  <?=$name?>
                  <a class="btn btn-primary pull-right" href="<?=base_url('admin/contests/add')?>">Add Contest</a>
               </header>
               <div class="panel-body fltrht">
                  <?php $this->load->view('element/actionMessage'); ?>
                  <div class=" form">
                     <?php echo form_open_multipart('' . $id . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                     <div class="new_table table-responsive" style="width:100%;overflow:scroll;">
                        <table class="table table-striped" >
                           <thead>
                              <tr>
                                 <th style="text-align: center;"><input type="checkbox" id="select_all" />&nbsp;</th>
                                 <th>Price Pool (&#8377;)</th>
                                 <th>Winners</th>
                                 <th>Entry Fees(&#8377;)</th>
                                 <th>Discount(&#8377;)</th>
                                 <th>Discounted Entry Fees(&#8377;)</th>
                                 <th>Spot Left</th>
                                 <th>Joined/Total Teams</th>
                                 <th>Confirm win/Confirm Win Contest Percentages</th>
                                 <th>Per User Team Allowed</th>
                                 <th>Multi Team Allowed</th>
                                 <th>Is Compression Allow </th>
                                 <th>Is Duplicate Allow </th>
                                 <th>Duplicate Count </th>
                                 <th>Cash Bonus Type</th>
                                 <th>Cash Bonus Value</th>
                                 <th></th>
                              </tr>
                           </thead>
                           <tbody class="">
                              <?php
                            foreach($contents as $key =>$catval){   
                                 ?> 
                              <tr class="">
                                 <td colspan="15">
                                    <?=$catval['name'] ?>
                                 </td>
                              </tr>
                              <?php 
                                 $joinss = [];
                                 $table = "tbl_cricket_contests";
                                 $notIn = implode(",", $already);

                                 if(empty($notIn)){
                                  $notIn="0";
                                 }

                                 $cond = "{$table}.category_id ='" . $catval['id'] . "' AND ( (id in($notIn)) OR (is_deleted='N' AND status='A' AND id not in($notIn)) )";
                                 $contentsdata = $this->main_model->cruid_select_array($table, "$table.*", $joinss, $cond);

                                 foreach($contentsdata as $key =>$val){
                                    echo '<tr class=""><td class="check_box">';

                                     $match_id    = $id;
                                     $contest_id  = $val['id'];
                                     $queryccm    = null;

                                     if( in_array($val['id'],$already) ){
                                        $queryccm = $this->db->query("SELECT * FROM `tbl_cricket_contest_matches` WHERE `match_id`=$match_id AND `contest_id`=$contest_id AND `parent_id`=0")->row();
                                        $val['total_team'] = $queryccm->total_team;
                                      }
                                      $is_checked = false;
                                      if(in_array($val['id'],$already) ){
                                          $contest_json = json_decode( $queryccm->contest_json );
                                          $contest_data =  $queryccm ;
                                          $views_player_lisetnow = $queryccm->id;     
                                          if($queryccm->is_deleted == 'N'){
                                            $is_checked = true;
                                          }

                                         $data = array(
                                             'name'     => 'contest_matches_row_id['.$val['id'].']',
                                             'id'       => 'contest_matches_row_id'.$val['id'],
                                             'value'    => $queryccm->id,
                                             'type'     => 'hidden',
                                             'readOnly' => true,
                                         );
                                         echo form_input($data);                              
                                      }else{
                                          $contest_json = json_decode( $val['contest_json'] );
                                          $contest_data =  $val ;
                                          $views_player_lisetnow = $val['id'];
                                      }
                                    ?>
                              
                                    <?php
                                       $data = array(
                                           'name' => 'check[]',
                                           'id' => 'check',
                                           'value' => $val['id'],
                                           'class' => 'form-control',
                                           'checked'=>(in_array($val['id'],$already) and $is_checked ),
                                       
                                       );
                                       echo form_checkbox($data);
                                       ?>
                                 </td>
                                 <td class="">  
                                    <?php  echo ( in_array($val['id'],$already) )?$queryccm->total_price:$val['total_price'];?>
                                 </td>
                                 <td class="">
                                    <b type="button" title='Edit Contest' class="views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($contest_data);?>' style="cursor: pointer;" id="views_player_lisetnow_<?=$views_player_lisetnow;?>">
                                    <span><?php  
                                       if( !empty($contest_json) ) {
                                         $per_max_p = $contest_json->per_max_p;
                                         echo end($per_max_p);
                                        }?></span>
                                    <input type="hidden" name="winnersdata[<?=$val['id'] ?>]" value='<?=json_encode($contest_data);?>'>
                                    <i class="fa fa-edit"></i>
                                    </b>
                                 </td>
                                 <td class="">
                                    <?php
                                      $more_entry_fees = (in_array($val['id'],$already))?$queryccm->more_entry_fees:$val['more_entry_fees'];
                                      $entry_fees = (in_array($val['id'],$already))?$queryccm->entry_fees:$val['entry_fees'];
                                      $actual_entry_fees = (in_array($val['id'],$already))?$queryccm->actual_entry_fees:$val['actual_entry_fees'];

                                       $data = array(
                                           'name'   => 'actual_entry_fees['.$val['id'].']',
                                           'id'   => 'actual_entry_fees'.$val['id'],
                                           'value' => $actual_entry_fees,
                                           'class' => 'Confirm_Win required number decimalandnumbers',
                                           'placeholder' => '',
                                         );
                                       echo form_input($data);
                                       ?>
                                 </td>
                                 <td class="">
                                    <?php
                                       $data = array(
                                           'name'   => 'more_entry_fees['.$val['id'].']',
                                           'id'   => 'more_entry_fees'.$val['id'],
                                           'value' => $more_entry_fees,
                                           'class' => 'Confirm_Win required number decimalandnumbers',
                                           'placeholder' => '',
                                          );
                                       echo form_input($data);
                                       ?>
                                 </td>
                                 <td class="">
                                    <?php
                                       $data = array(
                                           'name'   => 'entry_fees['.$val['id'].']',
                                           'id'   => 'entry_fees'.$val['id'],
                                           'value' => $entry_fees,
                                           'class' => 'Confirm_Win required number decimalandnumbers',
                                           'readOnly' => true,
                                       );
                                       echo form_input($data);
                                       ?>
                                 </td>
                                 <?php
                                    $contest_id = $val['id'];
                                    $match_id = $id;
                                    $query = $this->db->query("SELECT tbl_ccm.id, count( tbl_ccc.match_contest_id ) counter FROM tbl_cricket_contest_matches tbl_ccm INNER JOIN tbl_cricket_customer_contests tbl_ccc ON tbl_ccm.id = tbl_ccc.match_contest_id WHERE tbl_ccc.match_contest_id = (SELECT id FROM `tbl_cricket_contest_matches` WHERE `match_id`=$match_id AND `contest_id`=$contest_id AND `parent_id`=0 ) GROUP BY tbl_ccc.match_contest_id ORDER BY counter DESC ");
                                    $row = $query->row();
                                    ?>
                                 <td class="">
                                    <?php
                                       if (isset($row->counter) ){
                                        echo ( (int)$val['total_team'] -(int)$row->counter );
                                       }else{
                                        echo $val['total_team'];
                                       }
                                       ?>
                                 </td>
                                 <td class="">
                                    <?php
                                       if (isset($row->counter) )
                                       {
                                        echo'<b style="cursor: pointer;">';
                                        echo anchor('admin/joined_teams/sets?ccm='.$row->id, $row->counter,array('title' => 'View teams','dataid' => $row->id, 'class' => 'teamsclsviews')); 
                                        echo "</b>/";
                                        echo (in_array($val['id'],$already) && $queryccm->total_team)?$queryccm->total_team:$val['total_team'];        
                                        echo anchor('admin/joined_teams/sets?ccm='.$row->id, '<i class="fa fa-eye"></i>',array('title' => 'View teams','dataid' => $row->id, 'class' => 'teamsclsviews'));        
                                       }else{
                                        echo "0/";
                                        echo (in_array($val['id'],$already) && $queryccm->total_team)?$queryccm->total_team:$val['total_team'];                
                                       }
                                      ?>
                             
                                 </td>
                                 <td class="">
                                    <div class="check_box checkbox_left">                                    
                                       <?php
                                          $data = array(
                                              'name' => 'confirm_win[]',
                                              'id' => 'confirm_win_'.$val['id'],
                                              'value' => $val['id'],
                                              'class' => 'form-control',
                                          'checked'=>(in_array($val['id'],$confirm_winalready))?true:($val['confirm_win']=='Y' && !in_array($val['id'],$already))?true:false,
                                           );
                                          echo form_checkbox($data);
                                          ?>
                                    </div>
                                    <div class="checked_input">
                                       <?php
                                          $data = array(
                                              'name' => 'confirm_win_contest_percentage['.$val['id'].']',
                                              'id' => 'confirm_win_contest_percentage_'.$val['id'],
                                              'value' => (in_array($val['id'],$already))?$queryccm->confirm_win_contest_percentage:$val['confirm_win_contest_percentage'],
                                              'maxlength' => 6,
                                              'max' => 100,
                                              'class' => 'Confirm_Win required number form-control',
                                              'placeholder' => 'Confirm win contest percentages',
                                          );
                                          echo form_input($data);
                                          ?>
                                    </div>
                                 </td>
                                 <td class=""> 
                                    <?php
                                       $data = array(
                                           'name' => 'per_user_team_allowed['.$val['id'].']',
                                           'id' => 'per_user_team_allowed'.$val['id'],
                                           'puta' => $val['id'],
                                           'value' => (in_array($val['id'],$already))?$queryccm->per_user_team_allowed:$val['per_user_team_allowed'],
                                           'class' => 'Confirm_Win required number per_user_team_allowedCls',
                                           'placeholder' => 'Per User Team Allowed',
                                       );
                                       echo form_input($data);
                                       ?>
                                  </td>
                                  <td>
                                       <?php
                                          $data = array(
                                              'name' => 'multi_team_allowed['.$val['id'].']',
                                              'id' => 'multi_team_allowed'.$val['id'],
                                              'value' =>"Y",
                                              'class' => 'form-control ',
                                              'checked'=>(!in_array($val['id'],$already) && $val['multi_team_allowed'] == 'Y' )?true:( isset($queryccm->multi_team_allowed) && $queryccm->multi_team_allowed == 'Y') ? true : false,
                                            );
                                          echo form_checkbox($data);
                                          ?>
                                    </td>
                                  <td>
                                       <?php
                                          $data = array(
                                              'name' => 'is_compression_allow['.$val['id'].']',
                                              'id' => 'is_compression_allow'.$val['id'],
                                              'value' =>"Y",
                                              'class' => 'form-control ',
                                              'checked'=>(!in_array($val['id'],$already) && $val['is_compression_allow'] == 'Y' )?true:( isset($queryccm->is_compression_allow) && $queryccm->is_compression_allow == 'Y') ? true : false,
                                            );
                                          echo form_checkbox($data);
                                          ?>
                                    </td>
                                  <td>
                                       <?php
                                          $data = array(
                                              'name' => 'is_duplicate_allow['.$val['id'].']',
                                              'id' => 'is_duplicate_allow'.$val['id'],
                                              'value' =>"Y",
                                              'class' => 'form-control ',
                                              'checked'=>(!in_array($val['id'],$already) && $val['is_duplicate_allow'] == 'Y' )?true:( isset($queryccm->is_duplicate_allow) && $queryccm->is_duplicate_allow == 'Y') ? true : false,
                                            );
                                          echo form_checkbox($data);
                                          ?>
                                    </td>
                                    <td class=""> 
                                      <?php
                                        $data = array(
                                           'name' => 'duplicate_count['.$val['id'].']',
                                           'id' => 'duplicate_count'.$val['id'],
                                           'value' => (in_array($val['id'],$already))?$queryccm->duplicate_count:$val['duplicate_count'],
                                           'class' => 'Confirm_Win number duplicate_count_allowedCls',
                                           'maxlength' => 4,
                                        );
                                        echo form_input($data);
                                       ?>
                                    </td>
                                    <td><?php
                               
                                    $opt_all = unserialize(CONTEST_CASH_BONUS_USED_TYPE);
                                    $opt = array();
                                    $opt[''] = "Please Select";
                                    if (!empty($opt_all)) {
                                        foreach ($opt_all as $key=>$valc) {
                                            $opt[$key] = $valc;
                                        }
                                    }
                                    $value = set_value("cash_bonus_used_type") ? set_value("cash_bonus_used_type") : (isset($queryccm->cash_bonus_used_type) ? $queryccm->cash_bonus_used_type : '');
                                    echo form_dropdown('cash_bonus_used_type['.$val['id'].']', $opt, $value, 'class="form-control " id="cash_bonus_used_type"');
                                    ?>
                                    </td>
                                    <td>
                                    <?php
                                    $value = set_value("cash_bonus_used_value") ? set_value("cash_bonus_used_value") : (isset($queryccm->cash_bonus_used_value) ? $queryccm->cash_bonus_used_value : '0');
                                    $data = array(
                                        'name' => 'cash_bonus_used_value['.$val['id'].']',
                                        'id' => 'cash_bonus_used_value',
                                        'value' => $value,
                                        'maxlength' => 50,
                                        'class' => 'form-control required number',
                                        'placeholder' => $name.' cash_bonus_used_value',
                                    );
                                    echo form_input($data);
                                    ?>
                                    </td>
                                    <td>
                                    <?php
                                       if(in_array($val['id'],$already)){
                                        ?>
                                            <a class="btn btn-primary" href="<?php echo base_url($prefixUrl."add_team_customer/$id"); ?>?tccm_id=<?= $queryccm->id; ?>" style="font-size: 12px;padding: 4px;margin-bottom: 4px;">Create Team</a>
                                    <?php } ?>
                                    </td>
                              </tr>
                                <?php 
                                  if( $childQuery && $childQuery->num_rows() > 0 ){
                                    $this->load->view($prefixUrl.'child_contest_matches',['childQuery'=>$childQuery]);
                                  }
                                }
                              }
                                ?>
                           </tbody>
                        </table>
                     </div>
                     <div class="save_btn_cancel">
                        <button class="btn btn-danger" type="submit">Save</button>
                        <a class="btn btn-default" href="<?php echo HTTP_PATH . $prefixUrl ?>">Cancel</a>
                     </div>
                     <?php echo form_close(); ?>
                  </div>
                  <div class="clearfix"></div>
               </div>
            </section>
         </div>
      </div>
      <!-- page end-->
   </section>
</section>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
   <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">WINNING BREAKUP</h4>
         </div>
         <div class="modal-body">
            <div class="row our_rec_fetch"> 
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   var views_player_lisetnow = null;
   $(document).ready(function(){  
        // for actions tab
     $(document).on('click', ".views_player-liset", function(e) {
       var elecls = $(".our_rec_fetch");
        var ele   = $(this);
        var url = "<?php echo HTTP_PATH . $prefixUrl."winning_breakup_edit_modal/" ?>";
         var datapost = ele.attr("modaldata");
         $.post(url,
                    {
                        'datapost':datapost,'t': 't',
                        beforeSend: function() {
                elecls.addClass("fa-spinner fa-pulse fa-fw");
                        }}, function(data) {
                elecls.html(data);
                elecls.removeClass("fa-spinner fa-pulse fa-fw");    

                if(ele.attr('title') =="View Contest"){
                  $("#myformConstetst").hide();
                }else{
                  $("#myformConstetst").show();
                }          
            });
      });
    });
    
    $(document).ready(function(){ 
        // for actions tab
        $(document).on('click', ".teamsclsviews", function(event) {
        event.preventDefault();
        var ele   = $(this);
        var url = ele.attr('href');
         //alert(url);return;
         var datapost = ele.attr("modaldata");
         $.ajax({
          url: url,
          success: function(result) {
            if (result) {
              window.location='<?=base_url('admin/joined_teams/')?>'
            }
    
          }
        });
      });
    });
    
</script>
<script>
   $(document).ready(function(){
       if ($('#confirm_win').is(':checked')) {
           $("#confirm_win_contest_percentagehide").hide();
           $("#confirm_win_contest_percentage").val('0.00');
       } else {
           $("#confirm_win_contest_percentagehide").show();   
       }
   
       $(document).on('click', "input[name='confirm_win[]']", function() {
           var idhide = $(this).val();           
           if($(this).is(':checked')){
          $("#confirm_win_contest_percentage_"+idhide).val('0.00').hide();
           } else {
          $("#confirm_win_contest_percentage_"+idhide).val('').show();
           }
       });
   
   });
   
    $.each($("input[name='confirm_win[]']:checked"), function(){ 
      var idhide = $(this).val();           
        $("#confirm_win_contest_percentage_"+idhide).val('0.00').hide();
    });
   
</script>
<script type="text/javascript">
   $(document).ready(function(){

    /*****************************/

    $(document).on("keyup blur ", "input[id^=actual_entry_fees]", function(){
      var str = $(this).attr('id');
      var Id = str.replace("actual_entry_fees", "");

      var actual_entry_fees     = $("#actual_entry_fees"+Id).val();
      var more_entry_fees       = $("input#more_entry_fees"+Id).val();
      var totalDis              = ( actual_entry_fees - (( actual_entry_fees * more_entry_fees ) / 100) );
      var totalDis = totalDis.toFixed(2);
      $("input#entry_fees"+Id).val(totalDis) ;
    });

    $(document).on("keyup blur ", "input[id^=more_entry_fees]", function(){
      var str = $(this).attr('id');
      var Id = str.replace("more_entry_fees", "");

      var more_entry_fees           =   $("#more_entry_fees"+Id).val();
      
      if( more_entry_fees > 100 ){
            $("#more_entry_fees"+Id).val(100);
      }
      var more_entry_fees           =   $("#more_entry_fees"+Id).val();

      var actual_entry_fees               =   $("input#actual_entry_fees"+Id).val();
      var totalDis = ( actual_entry_fees - (( actual_entry_fees * more_entry_fees ) / 100) );
      var totalDis = totalDis.toFixed(2);
      $("input#entry_fees"+Id).val(totalDis) ;

    });

    /*****************************/

    $("#per_km_max_km_I_0").prop("readOnly", true);
    $("#per_km_km_charge_I_0").prop("readOnly", true);
          
    //var altnum = 1;
    $(document).on("click", "a[id^=add_more_]", function(){
      var id = $(this).attr('id');
      var a_data = id.split('_');
      var thisId = a_data['2'];
      
      var rowCount = document.getElementById('table_'+thisId).rows.length;
      //document.getElementById('td_'+thisId+'_'+altnum).outerHTML='';
      //alert(rowCount);
      altnumminus =rowCount-2;
      altnum =rowCount-1;
      newaltnum = altnum-1;
      if((document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).value)>=0) && (document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value !='' && Number(document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).value)>=0) ){
        //document.getElementById('per_km_max_km_'+thisId+'_'+newaltnum).readOnly = true;
        //document.getElementById('per_km_min_km_'+thisId+'_'+newaltnum).readOnly = true;
        //document.getElementById('per_km_km_charge_'+thisId+'_'+newaltnum).readOnly = true;
        $("input[id^=per_km_max_km_]").prop("readOnly", true);
        $("input[id^=per_km_min_km_]").prop("readOnly", true);
   
      }else{
          alert("Value Requied For MIN ,MAX  and Price");
          return true;
        }
      //alert(newaltnum);
   
         var total_team = document.getElementById('total_team').value;
      if(Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+1 > Number(total_team)){
        //document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
        alert("You want add more than team,  so please increase total teams!");
        return true;
      }
      if(newaltnum>0){
        //document.getElementById('td_'+thisId+'_'+newaltnum).outerHTML='';
        $('#td_'+thisId+'_'+newaltnum).remove();
      }
      var html ='<tr id="tr_'+thisId+'_'+altnum+'"><td><input type="text" class="form-control required input-medium decimalandnumbers per_min_p" alt="'+altnum+'" id="per_km_min_km_'+thisId+'_'+altnum+'" placeholder="Min" name="per_min_p['+thisId+']['+altnum+']" required ></td><td><input type="text" class="form-control required input-medium decimalandnumbers per_max_p" id="per_km_max_km_'+thisId+'_'+altnum+'" placeholder="Max" name="per_max_p['+thisId+']['+altnum+']" onfocusout="checkmaxfare('+altnum+',this.value,\''+thisId+'\')" required/></td><td><input type="text" class=" form-control required input-medium decimalandnumbers per_price" id="per_km_km_charge_'+thisId+'_'+altnum+'" placeholder="Price  Charge" name="per_price['+thisId+']['+altnum+']" required readOnly/></td></tr>';
   
      var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
      
      $('#table_'+thisId).append(html);
      document.getElementById('per_km_min_km_'+thisId+'_'+altnum).value = Number(document.getElementById('per_km_max_km_'+thisId+'_'+altnumminus).value)+1;
        document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = true;
      $('#tr_'+thisId+'_'+altnum).append(htmlremove);
      
    });
    
    $(document).on("keyup", '.per_price', function(){
      CalculetTotalOfPrice();
    });
    $(document).on("keyup", '#total_team', function(){
       var len = $(this).val();
        if (len >0) {
          $("#per_km_max_km_I_0").prop("readOnly", false).prop("readOnly", false);
          //$("#per_km_km_charge_I_0").prop("readOnly", false).prop("readOnly", false);
        }
        else{
          $("#per_km_max_km_I_0").prop("readOnly", true);
          //$("#per_km_km_charge_I_0").prop("readOnly", true);
        }
    });
    
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
   
    if(altnum>0 && $('#tr_'+thisId+'_'+altnum).find('#td_'+thisId+'_'+altnum).length ==0){
    var htmlremove='<td id="td_'+thisId+'_'+altnum+'"><a href="javascript:void(0);" onclick="removeKmCharge(\''+thisId+'\','+altnum+');"><i class="fa fa-trash"></i></a></td>';
    //document.getElementById('tr_'+thisId+'_'+altnum).innerHTML+=htmlremove;
    $('#tr_'+thisId+'_'+altnum).append(htmlremove);
    }
    if(altnum == 0){
      //document.getElementById('per_km_min_km_'+thisId+'_'+altnum).readOnly = false;
      document.getElementById('per_km_max_km_'+thisId+'_'+altnum).readOnly = false;
      
      document.getElementById('per_km_km_charge_'+thisId+'_'+altnum).readOnly = false;
    }
        CalculetTotalOfPrice();
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
      var total_team = document.getElementById('total_team').value;
        if (typeof(element) != 'undefined' && element != null)
        {
          //alert(document.getElementById('per_km_max_km_'+type+'_'+i).value);
          elementvalue = element.value;
          if(Number(value) > Number(total_team))   {
                        alert("Max should be less than total team "+total_team);
            document.getElementById('per_km_max_km_'+type+'_'+id).value = '';
   
            }
            else if(Number(value) >= Number(elementvalue))   {
                       
            } else{
              document.getElementById('per_km_max_km_'+type+'_'+id).value = '';
              alert("Max should be grater than Min ");
              
            }
        }
   
      }
      
   $(document).on("keyup", "input[id^=per_km_max_km_I_]", function(){
      evID = $(this).closest('tr').attr("id");
      var gotTD1 = $('#'+evID).find('td:eq(1) input').val();
      if(gotTD1 !="" && gotTD1 !=null){
        $('#'+evID).find('td:eq(2) input').prop("readOnly", false);
      }else{
        $('#'+evID).find('td:eq(2) input').prop("readOnly", true);
      }
      CalculetTotalOfPrice();
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
          var numberget = parseInt(this.value)*parseInt(getBetVeen);
          // console.log(numberget);
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
   
   $(document).ready(function() {
      $("#myformConstetst").validate();
   });
   
   $(document).on("submit","form#myformConstetst", function(event) {
        event.preventDefault();
        var formData      = $(this).serialize();
        var inputthis     = $(this).attr("inputthis");
        var datapostold   = $("#"+inputthis).attr("modaldata");
        var url = "<?php echo HTTP_PATH . $prefixUrl."winning_breakup_edit_modal_after/" ?>";
        $.post(url,
                   {
                      'newdatapost':formData,'datapostold':datapostold,'t': 't',
                      beforeSend: function() {
                       }}, function(data) {
   
              //alert(data);
              $("#"+inputthis).removeAttr("modaldata");
              $("b#"+inputthis+" input").val("");
              $("#"+inputthis).attr("modaldata",data);
              $("b#"+inputthis+" input").val(data);
              $("b#"+inputthis+" span").text($('#table_I tr:last').find("td").eq(1).find("input").val());
   
              $("#myModal").modal("hide");
          });
    });
   
   function objectifyForm(formArray) {
   
   var returnArray = {};
   for (var i = 0; i < formArray.length; i++){
    returnArray[formArray[i]['name']] = formArray[i]['value'];
   }
   return returnArray;
   }
   
   $(document).ready(function(){
    $('#select_all').on('click',function(){
        if(this.checked){
            $('.check_box input:checkbox[name="check[]"]').each(function(){
                this.checked = true;
            });
        }else{
             $('.check_box input:checkbox[name="check[]"]').each(function(){
                this.checked = false;
            });
        }
    });
    
    $('.check_box input:checkbox[name="check[]"]').on('click',function(){
        if($('.check_box input:checkbox[name="check[]"]:checked').length == $('.check_box input:checkbox[name="check[]"]').length){
            $('#select_all').prop('checked',true);
        }else{
            $('#select_all').prop('checked',false);
        }
    });
    if($('.check_box input:checkbox[name="check[]"]:checked').length == $('.check_box input:checkbox[name="check[]"]').length){
            $('#select_all').prop('checked',true);
        }
   });
   
</script>

<script type="text/javascript">
   $(document).on('click','[id^=show_child_]', function(e){
       e.preventDefault();
        $( this ).parent().toggleClass( "highlight__" );
       var ext_id = $(this).attr('data-id');
       //$("[id^=table_more_child_]").hide();
       $('.table_more_child_'+ext_id).slideToggle('fast');
       
   });
</script>
