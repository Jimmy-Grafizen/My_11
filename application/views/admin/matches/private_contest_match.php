<section id="main-content">
   <section class="wrapper">
      <!-- page start-->
      <div class="row">
         <div class="col-lg-12">
            <?php echo $this->breadcrumbs->show(); ?>
            <section class="panel">
               <header class="panel-heading">
                  Private Contests                    
               </header>
               <div class="panel-body fltrht">
                  <?php $this->load->view('element/actionMessage'); ?>
                  <div class=" form">

                  <div class="new_table table-responsive">            
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                            <th>Price Pool (&#8377;)</th>
                            <th>Winners</th>
                            <th>Entry Fees(&#8377;)</th>
                            <th>Spot Left</th>
                            <th>Joined/Total Teams</th>
                            <th>User Details</th>
                            <th>Per User Team Allowed</th>
                          </tr>
                        </thead>


                        <tbody class="">
                        <?php
                           foreach($contents as $key =>$catval){
                           ?> 

                        <tr class="">
                            <td colspan="8">
                           <h4><?=$catval['name'] ?></h4>
                        </td>
                     </tr>
                        <?php 
                           $joinss = [];
                           $table = "tbl_cricket_contest_matches";
                           $cond = "{$table}.category_id ='" . $catval['id'] . "' AND is_deleted='N'  AND match_id='$id' AND is_private='Y' AND status='A'";
                           $contentsdata = $this->main_model->cruid_select_array($table, "$table.*", $joinss, $cond);
                     
                           foreach($contentsdata as $key =>$val){
                              $match_id = $id;
                              $contest_id = $val['id'];                           
                           
                           $queryccm = $this->db->query("SELECT * FROM `tbl_customers` WHERE id='".$val['user_id']."' and is_deleted='N' ")->row();
                                    $contest_json        = json_decode( $val['contest_json'] );
                                    $contest_data        = $val ;
                                    $views_player_lisetnow  = $val['id'];
                               
                           ?>

                        <tr class="">
                           <td class="">
                              
                           </td>
                           <td class="">  
                              <?php  echo ($val['total_price'] ) ;?>
                           </td>
                           <td class="">
                              <b type="button" title='View Contest' class="views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($contest_data);?>' style="cursor: pointer;" id="views_player_lisetnow_<?=$views_player_lisetnow;?>">
                              <span><?php  
                                if( !empty($contest_json) ) {
                                    $per_max_p = $contest_json->per_max_p;
                                    echo end($per_max_p);
                                 }?></span>
                                 <input type="hidden" name="winnersdata[<?=$val['id'] ?>]" value='<?=json_encode($contest_data);?>'>
                                  <i class="fa fa-eye"></i>
                              </b>
                           </td>
                           <td class="">
                              <?php
                                    
                                 echo ($val['entry_fees']);
                              ?>
                           </td>
                          
                           <td class="">
                            <?php
                              $contest_id = $val['id'];
                              $match_id = $id;
                              $query = $this->db->query("SELECT tbl_ccm.id, count( tbl_ccc.match_contest_id ) counter FROM tbl_cricket_contest_matches tbl_ccm INNER JOIN tbl_cricket_customer_contests tbl_ccc ON tbl_ccm.id = tbl_ccc.match_contest_id WHERE tbl_ccc.match_contest_id = $contest_id GROUP BY tbl_ccc.match_contest_id ORDER BY counter DESC ");
                              $row = $query->row();
                              ?>
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
                           <td class="" cid='<?=$queryccm->id; ?>'>
                             <b><?= ucfirst( $queryccm->firstname ) . ' '. $queryccm->lastname . '<br> '. $queryccm->email. '<br>'. $queryccm->country_mobile_code. $queryccm->phone; ?>
                           
                              
                           </td> 
                           <td class="">
                              <?php echo $val['per_user_team_allowed']; ?>
                           </td>
                           
                        </tr>
                        <?php }
                           }
                           ?>
                     </tbody>

                     </table>
                  </div>


                     
                     
                    
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
   		var ele 	= $(this);
   		var url = "<?php echo HTTP_PATH . "admin/matches/view_price_pool/" ?>";
   		 var datapost = ele.attr("modaldata");
   		 $.post(url,
                   {
                       'datapost':datapost,'t': 't',
                       beforeSend: function() {
   						elecls.addClass("fa-spinner fa-pulse fa-fw");
                       }}, function(data) {
   						elecls.html(data);
   						elecls.removeClass("fa-spinner fa-pulse fa-fw");   						
   				});
   	});
   });
   
   $(document).ready(function(){	
       // for actions tab
       $(document).on('click', ".teamsclsviews", function(event) {
   		event.preventDefault();
   		var ele 	= $(this);
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