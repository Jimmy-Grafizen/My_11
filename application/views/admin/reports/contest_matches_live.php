<style>
.form-inline .form-control {width: 100%;}
.chip {
  display: inline-block;
  padding: 0 20px;
  height: 40px;
  font-size: 16px;
  line-height: 40px;
  border-radius: 25px;
  background-color: #f1f1f1;
  margin: 0 0px 5px 0px;
  width: 100%;

}

.chip img {
  float: left;
  margin: 0 10px 0 -25px;
  height: 40px;
  width: 40px;
  border-radius: 50%;
}
.chip a {
    *float: right;
    *margin-top: 9px;
}
</style>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->breadcrumbs->show(); ?>
                <section class="panel">
                    <header class="panel-heading">
                        View Contest with  <?=$name?>
                    </header>
                    <div class="panel-body fltrht"><?php $this->load->view('element/actionMessage'); ?>
                  
                        <div class=" form">
							<?php //echo form_open_multipart('' . $id . "?return=" . $this->input->get("return"), array('method' => 'post', 'class' => 'cmxform form-horizontal tasi-form form', 'id' => 'myformno')) ?>
							
							
                        <div class="row">
						<div class="col-sm-2"> 
							<h4>Price Pool (&#8377;)</h4>
						</div>
						<div class="col-sm-2"> 
							<h4>Winners</h4>
						</div>
						<div class="col-sm-2"> 
							<h4>Entry Fees(&#8377;)</h4>
						</div>
						<div class="col-sm-2"> 
							<h4>Spot Left</h4>
						</div>
						<div class="col-sm-3"> 
							<h4>Joined/Total Teams</h4>
						</div>
						<?php
						foreach($contents as $key =>$catval){
							
							$queryTccc = $this->db->query("SELECT `tbl_cricket_contest_categories`.`name` FROM (`tbl_cricket_contest_categories`) LEFT JOIN `tbl_cricket_contests` tcc ON `tcc`.`category_id` = `tbl_cricket_contest_categories`.`id` LEFT JOIN `tbl_cricket_contest_matches` tccm ON `tccm`.`contest_id` = `tcc`.`id` WHERE `tccm`.`match_id`='$id' AND `tbl_cricket_contest_categories`.`id`='".$catval['id']."' GROUP BY `tbl_cricket_contest_categories`.`id` ORDER BY `tbl_cricket_contest_categories`.`id` DESC ");
							$row = $queryTccc->row();
						if(isset($row->name) && $catval['name']==$row->name){
						?> 
						<div class="col-sm-12">
							<h4><?=$catval['name'] ?></h4>
						</div>
							<?php 
									$joinss = [];
									$table = "tbl_cricket_contests";
									$cond = "{$table}.category_id ='" . $catval['id'] . "'";;
									$contentsdata = $this->main_model->cruid_select_array($table, "$table.*", $joinss, $cond);
								foreach($contentsdata as $key =>$val){
									if(in_array($val['id'],$already)){
							?>
                            <div class="chip col-sm-12">
                              
								<div class="col-sm-2">  
									<?php  echo ($val['total_price']);?>
                                </div>
								<div class="col-sm-2">
									<b type="button" title='View Contest' class="views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($val);?>' style="cursor: pointer;">
									<?php  
									if( !empty($val['contest_json']) ){
										$contest_json = json_decode($val['contest_json']);
										$per_max_p = $contest_json->per_max_p;
										echo end($per_max_p);
									}?>
									</b>
                                </div>
								<div class="col-sm-2">
                                    <?php
                                    
                                    echo ($val['entry_fees']);
                                    ?>
                                </div>
								
								<?php
									$contest_id = $val['id'];
									$match_id = $id;
									$query = $this->db->query("SELECT tbl_ccm.id, count( tbl_ccc.match_contest_id ) counter FROM tbl_cricket_contest_matches tbl_ccm INNER JOIN tbl_cricket_customer_contests tbl_ccc ON tbl_ccm.id = tbl_ccc.match_contest_id WHERE tbl_ccc.match_contest_id = (SELECT id FROM `tbl_cricket_contest_matches` WHERE `match_id`=$match_id AND `contest_id`=$contest_id ) GROUP BY tbl_ccc.match_contest_id ORDER BY counter DESC ");
									$row = $query->row();
								?>
								<div class="col-sm-1">
								<?php
								if (isset($row->counter) ){
									echo ( (int)$val['total_team'] -(int)$row->counter );
								}else{
									echo $val['total_team'];
								}
								?>
								</div>
								<div class="col-sm-1 col-sm-offset-2">
									<?php
										if (isset($row->counter) )
										{
											echo'<b style="cursor: pointer;">';
											echo anchor('admin/joined_teams/sets?ccm='.$row->id, $row->counter,array('title' => 'View teams','dataid' => $row->id, 'class' => 'teamsclsviews')); 
										 echo "</b>/";
										 echo ($val['total_team']);								
										}else{
											echo 0;
											echo "/";
											echo ($val['total_team']);								
										}
                                    ?>
									
                                </div>
                            </div>
							
						<?php }
						}
						}
						}
						
						?>
							
                            </div>
                            
							
							<div class="form-group col-sm-12">
                                <div class="col-lg-offset-2 col-lg-10" style="margin-top: 17px;">
                                    <!--button class="btn btn-danger" type="submit">Save</button-->
								<a class="btn btn-default" href="<?php echo HTTP_PATH . $prefixUrl ?>live">Cancel</a></div>
                            </div>
							
							
							
                            <?php //echo form_close(); ?>
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
		 url = "<?php echo HTTP_PATH . "admin/matches/view_price_pool/" ?>";
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