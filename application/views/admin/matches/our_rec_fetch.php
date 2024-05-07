<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>css/add_team_customer_players.css" />
<div class="col-lg-6">
	<h4> <b>Our DB</b></h4>
	<?php //echo json_encode($squad);die;
	foreach($squad as $getTeam){
	?>
	<div class="new_table table-responsive">
		<table class="table table-striped">
		   <thead>
		      <tr>
		        <th><h4><?=$getTeam['name'] ?></h4></th>
				<th></th>
				<th><h4>Credits Points</h4></th>
			</tr>
		</thead>
		<tbody class="">

	<?php	
	foreach( $getTeam['players'] as $player ){

		?>
	<tr class="" id="main_<?=$player['id']?>" style="padding: 0;">
		<td class="<?php echo (trim($player['position']) =='')?'col-sm3-4':'col-sm3-7'; ?>">
			<?php
			if(!empty($player['image'])){
					echo '<img src="'.PLAYER_IMAGE_THUMB_URL . $player['image'].'" class="imagwshow">';
			}
			elseif(!empty($player['file_name']) || CHECK_IMAGE_EXISTS){
			if(!empty($player['file_name'])){
				$filename = explode(',',$player['file_name']);
				echo '<img src="'.NO_IMG_URL.'" class="imagwshow">';
			}else{
				echo '<img src="'.NO_IMG_URL.'"  class="imagwshow">';
			}
			}else{
				echo '<img src="'.NO_IMG_URL.'"  class="imagwshow">';
			}
			?>
			<span class="max-lines" title="<?php echo $player['name']; ?>">
				<?php echo (trim($player['position']) =='')?mb_strimwidth($player['name'], 0, 15, "..."):$player['name']; ?>
				<span class="text_potison">
					<?=$player['position']?>
				</span>
			</span>	    
		</td>

		<td class="<?php echo (trim($player['position']) =='')?'col-sm3-4':'col-sm3-1'; ?>">
		    <?php       
		     // if(trim($player['position']) ==''){
		    	$positions = unserialize(PLAYER_POSITIONS);                          
		     	$opt[''] = "Please Select Playing Role";
			    if (!empty($positions)) {
			        foreach ($positions as $key =>$datass) {
			            $opt[$datass] = $datass;
			        }
			    }
		    	$value = ucfirst( $player['position'] );
		    	echo form_dropdown('position', $opt, $value, 'class="form-control required search_fields" id="position_'.$player['id'].'" pid="'.$player['id'].'"');
		    	unset($opt); 
		    // }
		    ?>
		</td>
		<td class="col-sm3-4">
		<span class="savecredits">
			<input id="credits_<?=$player['id']?>" type="text" value="<?php echo $player['credits']?>" class="form-control creditscss" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
			<button type="button" value="save" class="btn btn-primary" creditsin="<?=$player['id']?>" player_unique_id="<?=$player['player_unique_id']?>" id="_save_btn_credits_<?=$player['id']?>">Save</button>
		</span>
		<i class="fa fa-file-image-o pull-right image_change"  data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($player,JSON_HEX_APOS);?>'></i>
		<?php
		if ($player['status']=="D")
			echo anchor("{$prefixUrl}activate/" . $player['id'], '<i class="fa fa-check"></i>', 'data_url="'.base_url().$prefixUrl.'deactivate/' . $player['id'].'" title="Activate" class="btn btn-success btn-xs action-list"');
		else
			echo anchor("{$prefixUrl}deactivate/" . $player['id'], '<i class="fa fa-check"></i>', 'data_url="'.base_url().$prefixUrl.'activate/' . $player['id'].'" title="Deactivate" class="btn btn-danger btn-xs action-list"');
		 ?>
		</td>

	</tr>

<?php
	}
?>

<?php
}
?>
			</tbody>
		</table>
	</div>
</div>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Choose Image</h4>
        </div>
        <div class="modal-body">

	<form action="<?=base_url("{$prefixUrl}image_change_save");?>" method="post" id="image_change_save" enctype="multipart/form-data" accept-charset="utf-8">
		<label>
			<input type="checkbox" name="apply_all" > Apply all player 
			<b id="team_nameshoi__"></b> 
		</label>
		<div class="row imageload">	

		</div>
		<div class="row ">
			<br>

			<div class="col-sm-12 form-group">
		        <label for="player_image" class="control-label">Upload image:</label>
		        <input type="hidden" name="player_unique_id" id="player_unique_idmetake">
		        <input type="hidden" name="match_players_id" id="match_players_id_idmetake">
		        <input type="hidden" name="team_id" id="match_players_team_id">
		        <input type="hidden" name="match_unique_id" id="match_unique_id___">
		        <input id="player_image" name="player_image[]" type="file" class="file" data-show-preview="false" accept="image/*"  onchange="readURL(this)" >
		    </div>
			<div class="col-xs-12 col-sm-12 col-md-12 text-center">
				<button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn btn-success pull-right" style="margin-right: 10px;">Selected Save</button>
			 	<button type="button" class="btn btn btn-success pull-right player_image_two" style="margin-right: 10px;" action="upload_save">Upload & Save </button>
			 	<button type="button" class="btn btn btn-success pull-right player_image_two" style="margin-right: 10px;" action="save">Upload</button>
			</div>
		</div>
	</form>
	
	</div>
	</div>
      
    </div>
  </div>
