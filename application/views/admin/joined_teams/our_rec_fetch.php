<div class="row">
   <div class="col-lg-12">
      <h4 style="margin-left: 10px;"><b>Team Players</b></h4>
      <?php 
       $sort_rol = ['Allrounder'=>'AR','Batsman'=>'BAT','Bowler'=>'BOWL','Wicketkeeper'=>'WK'];
         foreach($get_teams as $getTeam){
         ?>
      <div class="col-lg-12">
         <h4 style="margin-left: 10px;"><?=$getTeam['name'] ?></h4>
      </div>
        <div class="new_table table-responsive">
          <table class="table table-striped">
             <thead>
                <tr class="" id="main_<?=$getTeam['name']?>" style="width: 100%;margin-left: 0px;background: #eef;color: #000;">                                 
                   <th>PLAYER</th>
                   <th></th>
                   <th>CATEGORY</th>
                   <th>POINT EARNED</th>
                   <th>MULTIPLAYER</th>
                   <th></th>
                   <th></th>
                   <th><p class="text-center">TOTAL</p></th>
                 </tr>
               </thead>
        <tbody class="">
        <?php
         $total_points = [];
         foreach( $getTeam['players'] as $player ){
         ?>
      <tr class=" statsdata" id="main_<?=$player['player_id']?>" data-toggle="modal" data-target="#Modelplayer_statistics" modaldata='<?=json_encode($player);?>' style="cursor: pointer;">
         <td>
            <?php
               if(!empty($player['image'])){
               	echo '<img src="' . $player['image'].'" style="width: 50px;height: 50px;border-radius: 50%;">';
               }
               ?>
         </td>
         <td>
            <?=$player['name']?>
         </td>
         <td>
            <?php echo ( isset($sort_rol[ ucfirst( $player['position'] )] ) )? $sort_rol[ ucfirst( $player['position'] )]:""; ?>
         </td>
         <td>
            <p class="text-center"><?=$player['points']?></p>
         </td>
         <td>
            <p class="text-center">*</p>
         </td>
         <td>
            <p class="text-center"><?=$player['player_multiplier']?></p>
         </td>
         <td>
            <p class="text-center">=</p>
         </td>
         <td>
            <p class="text-center">
              <?php 
                  $total_point= ($player['points'] * $player['player_multiplier'] );
                  $total_points[]= $total_point;
                  echo( $total_point );
                  
              ?>
            </p>
         </td>
      </tr>
      <?php
         }
         ?>
      <tr class="" style="width: 100%;margin-left: 0px;background: #eef;color: #000;">
         <td colspan="6">
            <p class="text-center">TOTAL POINTS</p>
         </td>
         <td>
            <p class="text-center">=</p>
         </td>
         <td>
            <p class="text-center"><?php echo array_sum($total_points); ?></p>
         </td>
      </tr>
      <?php
         }
         ?>
   </tbody>
</table>
</div>

</div>
</div>

<script>

   
   $(document).ready(function(){
	    $("#myModal").on('click', ".statsdata", function(e) {		
	   		var ele 	= $(this);
	   		var ielem 	= $.parseJSON(ele.attr("modaldata"));
	   		var imageload ="";
	   		if(ielem !=null && ielem!=''){
	   	   		var url	= "<?php echo base_url("admin/joined_teams/get_player_statistics");?>";
	         	$.post(url,
	                {
	                     'postjson':ielem,'t': 't',
	                     beforeSend: function() {
	   						//ielem.addClass("fa-spinner fa-pulse fa-fw");
	                     }}, function(data) {
				   				if(data){
				   					//alert(data);
				   					$("#points_breakup_load").html(data);
				   				}else{
				   					$("#points_breakup_load").html('<div class="col-sm-12">Not found!</div>');
				   				}
	   				});
			}
	   	});
   
		$("#myModal").on('click', ".image_change", function(e) {
			//e.preventDefault();
			var imageurl= "<?=PLAYER_IMAGE_THUMB_URL;?>"
			var ele 	= $(this);
			var ielem 	= $.parseJSON(ele.attr("modaldata"));
				//console.log(ele.attr("modaldata"));

			var imageload ="";
			if(ielem.file_name !=null && ielem.file_name!=''){
				var imagesarr  = ielem.file_name.split(',');
				// Iterate through each value
				//console.log(imagesarr);
				for(var i = 0; i < imagesarr.length; i++)
				{
					imageload += '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'+imageurl+imagesarr[i]+'" /><input type="radio" name="image_radio" value="'+ielem.id+'_with_'+imagesarr[i]+'" /><i class="fa fa-check hidden"></i></label></div>';
				}		
			}else{
				imageload ='<div class="col-sm-12">Image not found!</div>';
			}

			$(".imageload").html(imageload);
		});
   
		$("#myModal").on("click",".image-radio", function(e){
			 $(".image-radio").removeClass('image-radio-checked');
			 $(this).addClass('image-radio-checked');
			 var $radio = $(this).find('input[type="radio"]');
			 $radio.prop("checked",!$radio.prop("checked"));

			 e.preventDefault();
		});
 	});
   
   
    $("#myModal").on('submit', "#image_change_save", function(e) {
  		e.preventDefault();
  		var fields = $(this).serializeArray();
  		var findid = "";
  		var valueimag = "";
  		if(fields !=null && typeof fields[0] !== "undefined"){
  		valueimag = fields[0].value;
  		findid = valueimag.split('_with_')[0];
  		// console.log(findid);
  		}
  		var url	= $(this).attr("action");
          $.post(url,
                   {
                       'save_image':valueimag,'t': 't',
                       beforeSend: function() {
     						//ielem.addClass("fa-spinner fa-pulse fa-fw");
                       }}, function(data) {
     						$(document).find("#myModal").modal('hide');
  		   				if(data!=0){
  		   					$("#main_"+findid).find("img").attr("src",data);
  		   				}
     				});
   });
   
   function image_radio(){
     $(".image-radio").each(function(){
         if($(this).find('input[type="radio"]').first().attr("checked")){
             $(this).addClass('image-radio-checked');
         }else{
             $(this).removeClass('image-radio-checked');
         }
     });
   }
</script>