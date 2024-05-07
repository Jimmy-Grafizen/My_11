<section id="main-content">
   <section class="wrapper">
      <!-- page start-->
      <div class="row">
         <div class="col-lg-12">
            <?php echo $this->breadcrumbs->show(); ?>
			<section class="panel">
	            <div class="panel-body">
	               	  <?php $this->load->view($prefixUrl.'matches_table_view'); ?>
	            </div>
	        </section>

            <section class="panel">
               <header class="panel-heading">
                <span style="float: left;">
                	<?php 
	                	if($this->input->get('v') == 'private'){ echo "View Private Contests";
	                	}else if($this->input->get('v') == 'beat_the_expert') { echo "View Beat The Expert";
	                	}else{ echo "View Contest"; }
                	?>
             	</span> 
             	<?php if($this->input->get('v') != 'beat_the_expert') { ?>
                 <span style="float: initial;">
                 	<form action="" method="get" accept-charset="utf-8" class="form-inline" id="search_form" autocomplete="off">
					   <div class="form-group ">
					      <div class="col-sm-6">
					         <div class="">
					            <select name="is_abondant" class="form-control search_fields" id="is_abondant" onchange="this.form.submit()">
					               <option value="" <?php echo ($this->input->get('is_abondant')=="")?"selected":""; ?> >ALL Contest</option>
					               <option value="Y" <?php echo ($this->input->get('is_abondant')=="Y")?"selected":""; ?>>Cancelled Contest</option>
					               <option value="N" <?php echo ($this->input->get('is_abondant')=="N")?"selected":""; ?>>Completed Contest</option>
					            </select>
					            <input type="hidden" name="v" value="<?php echo $this->input->get('v'); ?>">
					            <input type="hidden" name="return" value="<?php echo $this->input->get('return'); ?>">
					         </div>
					      </div>
					   </div>
					</form>
				</span>
				<?php } else { echo '<span style="float: initial;">&nbsp;</span>'; } ?>
               </header>
               <div class="panel-body fltrht">
                  <?php $this->load->view('element/actionMessage'); ?>               	 
                  <div class=" form">
                     <div class="new_table table-responsive">
                        <table class="table table-striped">
                           <thead>
                              <tr>                                 
                                 <?php if($this->input->get('v') == 'beat_the_expert') { echo "<th>Entry Fee Multiplier</th>"; }
                                    else{
                                       ?>
                                       <th>Price Pool (&#8377;)</th>
                                       <th>Winners</th>
                                       <?php
                                    }
                                  ?>
                                 <th>Entry Fees(&#8377;)</th>
                                 <th>Discount(&#8377;)</th>
                                 <th>Discounted Entry Fees(&#8377;)</th>
                                 <!--th>Spot Left</th-->
                                 <th>Joined/Total Teams</th>
                                 <th>Confirm win/%</th>
                                 <th>Per User Team Allowed</th>
                                 <th>Is Abandoned</th>
                                 <th>Credited Amount</th>
                                 <th>Debited Amount</th>
                                 <th>Refund Amount</th>
                                 <th>Earnings</th>
                                 <th>Tax</th>
                              </tr>
                           </thead>
                           <tbody class="">
                        <?php
                        	$match_id = $id;
                           foreach($contents as $key =>$catval){
                           	?>                               
                              <tr class="contest_matches_kk">
                                 <td colspan="15">
                                    <?= ucfirst( $catval['name'] );?>
                                 </td>
                              </tr>
                        <?php 
                        	$is_abondant =null;
	                		$Chk_is_abondant = $this->input->get('is_abondant');
	                		if( $Chk_is_abondant != "" && $Chk_is_abondant){
	                			$is_abondant = "AND is_abondant= '$Chk_is_abondant'";
	                		}

                           $joinss = [];
                           $table = "tbl_cricket_contest_matches";
                           $cond = "{$table}.category_id ='" . $catval['id'] . "' AND match_id = '$id' AND status= 'A' AND is_deleted= 'N' $is_abondant";
                           $seleted = "$table.*, (SELECT COUNT(id) FROM `tbl_cricket_customer_contests` WHERE `match_contest_id` = $table.id) as counter  ";
                           $contentsdata = $this->main_model->cruid_select_array($table, $seleted, $joinss, $cond);
                        foreach($contentsdata as $key =>$val){
                           ?>
                         <tr class="">
                            <?php if($this->input->get('v') == 'beat_the_expert') { echo "<td>{$val['entry_fee_multiplier']}</td>"; }
                            else{ ?>
                           <td class="">
                              <?php  echo ($val['total_price']);?>
                           </td>
                           <td class="">

                              <b type="button" title='View Contest' class="views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($val);?>' style="cursor: pointer;">
                              <?php  
                                 if( !empty($val['contest_json']) ){
                                 	$contest_json = json_decode($val['contest_json']);
                                 	$per_max_p = $contest_json->per_max_p;
                                 	 if($this->input->get('v') == 'beat_the_expert') {
                                 	 	echo "Ꝏ";
                                 	 }else{
                                 		echo end($per_max_p);
                                 	}
                                 }?>
                                 <i class="fa fa-eye"></i>
                              </b>
                           </td>
                          <?php } ?>
                           <td class="">
                              <?php 
                                    $more_entry_fees  = $val['more_entry_fees'];
                                    $entry_fees       = $val['entry_fees'];
                                    $actual_entry_fees       = $val['actual_entry_fees'];
                              echo $actual_entry_fees; ?>
                           </td>
                           <td class="">
                              <?php echo ($val['more_entry_fees']);?>
                           </td>
                           <td class="">
                              <?php echo ($val['entry_fees']);?>
                           </td>
                           <!--td class="">
                              <?php
                                 if (isset($val['counter'])  && $val['counter'] >0 ){
                                 	echo ( (int)$val['total_team'] -(int)$val['counter'] );
                                 }else{
                                 	echo $val['total_team'];
                                 }
                                 ?>
                           </td--->
                           <td class="">
                              <?php
                                 if (isset($val['counter'] ) && $val['counter'] > 0 )
                                 {
                                 	echo'<b style="cursor: pointer;">';
                                 	echo anchor('admin/joined_teams/sets?ccm='.$val['id'], $val['counter'],array('title' => 'View Teams','dataid' => $val['id'], 'class' => 'teamsclsviews')); 
                                  echo "</b>/";
                                  if($this->input->get('v') == 'beat_the_expert') {
                                 	 	echo "Ꝏ";
                                 	 }else{
                                  		echo ($val['total_team']);	
                                  	}							
                                 	echo anchor('admin/joined_teams/sets?ccm='.$val['id'], '<i class="fa fa-eye"></i>',array('title' => 'View Teams','dataid' => $val['id'], 'class' => 'teamsclsviews')); 
                                 }else{
                                 	echo 0;
                                 	echo "/";
                                 	if($this->input->get('v') == 'beat_the_expert') {
                                 	 	echo "Ꝏ";
                                 	 }else{
                                 		echo ($val['total_team']);
                                 	}								
                                 }
                              ?>
                           </td>
                           <td><?php echo ($val['confirm_win'] == 'Y') ? "Yes": "No/".$val['confirm_win_contest_percentage'];?></td>
                           <td><?php echo $val['per_user_team_allowed'];?></td>
                           <td><?php echo ($val['is_abondant']=="Y")? "Yes": "No" ;?></td>
                           <td>
							<?php  
								$m_contest_id = $val['id'];
								
									$querySpent = $this->db->query("SELECT (tccm.entry_fees*count(tccc.match_contest_id)) as spendamount,sum(win_amount) as winamount, sum(refund_amount) as refund_amount, sum(tax_amount) as tax_amount,((tccm.entry_fees*count(tccc.match_contest_id))-sum(win_amount) - sum(refund_amount)) as earnings FROM `tbl_cricket_customer_contests` tccc left join tbl_cricket_contest_matches tccm on(tccm.id=tccc.match_contest_id) where tccc.match_contest_id = $m_contest_id GROUP BY tccc.match_contest_id ");
									$spenddata = $querySpent->row(); 											
								echo (!empty( $spenddata ) )? number_format($spenddata->spendamount,2):"0.00";
								
							?>
                           </td>
                           <td><?php echo ( !empty( $spenddata ) )? number_format($spenddata->winamount,2):"0.00";?></td>
                           <td><?php echo ( !empty( $spenddata ) )? number_format($spenddata->refund_amount,2):"0.00";?></td>
                           <td><?php echo ( !empty( $spenddata ) )? number_format($spenddata->earnings,2):"0.00";?></td>
                           <td><?php echo ( !empty( $spenddata ) )? number_format($spenddata->tax_amount,2):"0.00";?></td>
                        </tr>
                        
                        <?php 
                           }
                           
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
   <div class="modal-dialog modal-lgkk">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">WINNING BREAKUP</h4>
         </div>
         <div class="modal-body">
            <div class="row our_rec_fetch">	
            </div>
            <div class="row ">
               <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                  <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   $(document).ready(function(){	
       // for actions tab
       $(document).on('click', ".views_player-liset", function(e) {
   		var elecls = $(".our_rec_fetch");
   		var ele 	= $(this);
   		 url = "<?php echo HTTP_PATH .  $prefixUrl."view_price_pool/" ?>";
   		 var datapost = ele.attr("modaldata");
   		 $.post(url,
                   {
                       'datapost':datapost,'t': 't',
                       beforeSend: function() {
   						elecls.addClass("fa-spinner fa-pulse fa-fw");
                       }}, function(data) {
   						elecls.html(data);
   						elecls.removeClass("fa-spinner fa-pulse fa-fw");
   						//alert(data);
   						//$(document).find("#myModal").modal('hide');
   						
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