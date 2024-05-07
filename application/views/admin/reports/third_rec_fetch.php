<div class="col-lg-6">
<h4><b>Third party players</b></h4>
<?php 
	foreach($squad as $getTeam){
?>
<div class="new_table table-responsive">
		<table class="table table-striped">
		   <thead>
		      <tr>
		        <th><h4><?=$getTeam->name ?></h4></th>
		    </tr>
		</thead>
<tbody class="">
<?php
	
foreach( $getTeam->players as $player ){
		?>
<tr>
	<td>
  <img src="https://www.w3schools.com/howto/img_avatar.png" alt="Person" class="imagwshow" />
		<?=$player->name?>
<?php if($getres = !$this->main_model->check_match_playe_exists('tbl_cricket_match_players',['player_unique_id'=>$player->pid,'match_unique_id'=>$match_unique_id])) {
?>

  <a href="<?=base_url($prefixUrl.'add_mache_player')?>" title="Add Player" player='<?php echo json_encode(["name"=>$player->name,'pid'=>$player->pid,'unique_id'=>$match_unique_id,"team_name"=>$getTeam->name]);?>' class="btn btn-success btn-xs action-player"><i class="fa fa-plus"></i> Add</a>
 
 <?php } 
 ?>
</td>
</tr>

<?php
	}
}
?>
</tbody>
</table>
</div>
</div>
