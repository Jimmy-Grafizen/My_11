<style>.chip img{float:left;margin:0 0px 0 -45px;height:40px;width:40px;border-radius:50%}.chip a{float:right;margin-top:9px}</style>
<section id="main-content">
   <section class="wrapper">
      <!-- page start-->
      <div class="row">
         <div class="col-lg-12">
            <?php echo $this->breadcrumbs->show(); ?>
            <section class="panel">
               <header class="panel-heading">
                  Edit <?=$name?>
               </header>
               <div class="panel-body fltrht">
                  <?php $this->load->view('element/actionMessage'); ?>
                  <span class="require_sign">Please note that all fields that have an asterisk (*) are required. </span>
                  <div class=" form">
                     <?php echo form_open_multipart(base_url("admin/matches/add_players_with_customer"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myform')) ?>
                     <input type="hidden" name="match_unique_id" value="<?=$user_detail['unique_id'] ?>">
                     <input type="hidden" name="match_contest_id" value="<?= $this->input->get("tccm_id"); ?>">
                     <input type="hidden" name="customer_team_id" value="0" id="customer_team_id">
                     <div class="form-group col-sm-6">
                        <label for="user_id" class="control-label">Select Customer<span class="red_star">*</span></label>
                        <div class="col-sm-12">
                          <?php 
                          $Expert = "";
                           $is_adminA = ["is_admin" => '0'];
                              $opt[''] = "Please Select Admin";                               
                            if($this->input->get('bc') == 'y'){
                              $is_adminA = ["is_admin" => '1'];
                              if($beattheis_admin>0){
                                $is_adminA=array_merge( $is_adminA, ["id" => $beattheis_admin] );
                              }
                              //$opt = [];
                            }
                              $opt_all = $this->main_model->cruid_select_array_order("tbl_customers", "firstname,lastname,id", $joins = array(), $cond = array("is_deleted" => 'N',"is_fake" => '1')+$is_adminA, $order_by = array(), $limit = '', $order_by_other = array());
                              //$beattheis_admin="";
                            
                               if (!empty($opt_all)) {
                                   foreach ($opt_all as $datass) {
                                       $opt[$datass->id] = $datass->firstname ." ".$datass->lastname;
                                       $Expert = $datass->firstname ." ".$datass->lastname;
                                       //$beattheis_admin = $datass->id;
                                   }
                               }
                               $value = set_value("user_id") ? set_value("user_id") : (isset($user_detail['user_id']) ? $user_detail['user_id'] : "" );
                                if($beatthe){
                                  $data = array(
                                    'name'      => 'Expert_name',
                                    'id'        => 'Expert_name',
                                    'value'     =>  $Expert,
                                    'readonly'  =>  true,
                                    'class'     =>  'form-control required',
                                    'placeholder'=> 'Expert Name',
                                  );
                                  echo form_input($data);
                                  echo form_dropdown('user_id', $opt, $beattheis_admin, 'class="form-control required hide" id="user_id"');
                               }else{
                                  echo form_dropdown('user_id', $opt, $value, 'class="form-control required" id="user_id" ');
                              }
                               ?>
                        </div>
                     </div>
                     <div id="selectedct" class="form-group col-sm-6" style="display: none;"></div>
                     <div class="form-group col-sm-6">
                        <label for="customer_team_name" class="control-label">Customer Team Name <span class="red_star">*</span></label>
                        <input type="hidden" name="permissions" id="permissions">
                        <div class="col-sm-12">
                           <?php
                              $value = set_value("customer_team_name") ? set_value("customer_team_name") : (isset($match_detail['customer_team_name']) ? $match_detail['customer_team_name'] : '');
                              $data = array(
                                  'name' => 'customer_team_name',
                                  'id' => 'customer_team_name',
                                  'value' => "",
                                  'maxlength' => 50,
                                  'class' => 'form-control required',
                                  'placeholder' => 'Customer Team Name',
                                  'oninput'=>"this.value = this.value.replace(/[^a-z0-9\s]/gi, '');"
                              );
                              echo form_input($data);
                              ?>
                        </div>
                     </div>
                     <div class="col-sm-3">
                        <label for="team_name" class="control-label ">Team Number<span class="red_star">*</span></label>
                        <input type="hidden" name="permissions" id="permissions">
                        <div class="form-group col-sm-12">
                           <?php
                              $value = set_value("team_name") ? set_value("team_name") : (isset($user_detail['team_name']) ? $user_detail['team_name'] : '');
                              $data = array(
                                  'name' => 'team_name',
                                  'id' => 'team_name',
                                  'value' => "",
                                  'maxlength' => 50,
                                  'class' => 'form-control required',
                                  'placeholder' => 'Team Number',
                                  'oninput'=>"this.value = this.value.replace(/[^0-9]/g, '');"
                              );
                              echo form_input($data);
                              ?>
                        </div>
                     </div>
                     <div class="form-group col-sm-3">
                        <div style="margin-top: 17px;">
                          <?php if( $beatthe == 0 ) {?>
                           <button class="btn btn-danger" type="submit" name="is_update" value="N" id="submitbtn_t">Save And Join</button>
                            <?php } ?>
                           <button class="btn btn-danger" type="submit" name="is_update" value="Y" id="Updatesubmitbtn_t" style="display: none;">Update Team</button>
                        </div>
                     </div>
                     <?php echo form_close(); ?>
                  </div>
                  <div class="clearfix"></div>
                  <div class="row ">
                     <div id="our_rec_fetch">
                        <div class="col-xs-4 center-block">
                           <h4><b></b></h4>
                           <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        </div>
                     </div>
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
   
     $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/add_team_customer_playerslist/" ?><?=$user_detail['unique_id'] ?>?beatthe=<?=$beatthe ?>");
     
       });
   
   function RefreshFunction() {
    $("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/add_team_customer_playerslist/" ?><?=$user_detail['unique_id'] ?>");
   }
</script>
<script>
   // for actions tab
   var beattheis_admin = '<?=$beattheis_admin;?>';
   $(document).on('click', ".action-player", function(e) {
       e.preventDefault();
         var ele    = $(this);
         var ielem  = $(this).find('i');
         var url    = $(this).attr("href");
         var fields = $(this).attr("player");
        $.post(url,
               {
                   'fields':fields,'t': 't',
                   beforeSend: function() {
                      ielem.addClass("fa-spinner fa-pulse fa-fw");
              }}, function(data) {
            ele.remove();
         //$("#our_rec_fetch").load("<?php echo HTTP_PATH . "admin/matches/get_our_db_playes/" ?><?=$user_detail['unique_id'] ?>");
    });
   return false;
   });
   
   // for actions tab
   $(document).on('click', ".action-list", function(e) {
       e.preventDefault();
   
   var ielem       = $(this).find('i');
   var dataurlele  = $(this).attr('data_url');
   var url         = $(this).attr("href");
   
   if($(this).hasClass("btn-success")) {        
   $(this).attr('data_url',url).attr("href",dataurlele).attr('title',"Deactivate").addClass("btn-danger").removeClass("btn-success");           
   }else{
   $(this).attr('data_url',url).attr("href",dataurlele).attr('title',"Activate").addClass("btn-success").removeClass("btn-danger");
   }
   
       $.post(url,
               {
                   't': 't',
                   beforeSend: function() {
        ielem.removeClass("fa-check");
        ielem.addClass("fa-spinner fa-pulse fa-fw");
                   }}, function(data) {
        ielem.addClass("fa-check");
        ielem.removeClass("fa-spinner fa-pulse fa-fw");
   });
       return false;
   
   })
   


</script>