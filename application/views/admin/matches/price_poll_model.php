<style>
i.image_change{margin-top:9px;font-size:22px;cursor:pointer}.image-radio{cursor:pointer;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;border:4px solid transparent;margin-bottom:0;outline:0}.image-radio input[type=radio]{display:none}.image-radio-checked{border-color:#4783b0}.image-radio .fa{position:absolute;color:#4a79a3;background-color:#fff;padding:10px;top:0;right:15px;opacity:.8}.image-radio-checked .fa{display:block!important;visibility:visible!important}.image-radio img{height:80px!important}
</style>
<div class="row">
<div class="col-lg-12">
	<h4 style="text-align: center;"><b>Price Pool<b></h4>
<div class="col-lg-12">
	<h4 style="text-align: center;">&#8377; <?=$contents['total_price'] ?></h4>
</div>
 
	<div class="row-fluid">
		<?php 
		if( !empty($contents['contest_json']) ){
			$contest_json = json_decode($contents['contest_json']);
			$per_max_p = $contest_json->per_max_p;
			//print_r($per_max_p);
			$per_price = $contest_json->per_price;
			$per_percent = null;
			$per_percentIS = false;
			if(isset($contest_json->per_percent) && !empty($contest_json->per_percent) ){
				$per_percent = $contest_json->per_percent;
				$per_percentIS = true;
			}

			foreach($contest_json->per_min_p as $key=> $val){
		 ?>	
		 <div id="<?=$key?>" class="row chip block_of_player">
			<div class="col-lg-4">
				<p>Rank: <?php echo ($val==$per_max_p[$key]) ? $per_max_p[$key]: $val." - ".$per_max_p[$key];?></p>
			</div>
			<?php if($per_percentIS){ ?>
				<div class="col-lg-2 col-sm-offset-2">
					<p>% <?=$per_percent[$key]?></p>
				</div>
			<?php } ?>
			<div class="<?php echo ($per_percentIS)?"col-lg-2 col-sm-offset-2":"col-lg-2 col-sm-offset-4"; ?>">
				<p>&#8377; <?=$per_price[$key]?></p>
			</div>

		</div>
	<?php 
			}
		}
		?>
	</div>

</div>
</div>