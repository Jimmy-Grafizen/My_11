<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>css/add_team_customer_players.css" />
<style type="text/css">.cls_Playing{
   color: #3dba62;
   font-size: 12px;
   }
   .cls_notPlaying {
   color: #ed1c24;
   font-size: 12px;
   }
</style>

<div class="col-lg-12">
   <h4></h4>
   <?php 
      foreach($squad as $index => $getTeam){
      ?>
   <div class="col-lg-6">
      <div class="<?php echo ($index==0)?"col-lg-8 col-sm-offset-3":"col-lg-11"; ?>">
         <h4><?=$getTeam['name'] ?> </h4>
      </div>
      <?php echo ($index==0)?'<div class="col-lg-1"><h4>Vs </h4></div>':''; ?>
      <?php echo ($index==1)?'<div class="col-lg-1" style="cursor: pointer;" onClick="RefreshFunction();"><i class="fa fa-refresh" style="font-size:36px;"></i></div>':'';?>
      <div class="new_table table-responsive">
         <table class="table table-striped">
            <thead>
               <tr>
                  <th>PLAYERS</th>
                  <th>SELECTED</th>
                  <th>POINTS</th>
               </tr>
            </thead>
            <tbody class="">
               <?php
                  foreach( $getTeam['players'] as $player ){
                  $meARr = [];	
                  $meARr['player_name']=$player['name'];	
                  $meARr['name']=$player['name'];	
                  $meARr['player_unique_id']=$player['player_unique_id'];	
                  $meARr['unique_id']=$player['match_unique_id'];	
           		?>
               <tr class="" id="main_<?=$player['id']?>">
                  <td class="col-lg-6">
                     <?php
                        if(!empty($player['image'])){
                        	$meARr['image'] = PLAYER_IMAGE_THUMB_URL . $player['image'];
                        		echo '<img src="'.PLAYER_IMAGE_THUMB_URL . $player['image'].'" class="imagwshow">';
                        		echo ($player['is_in_playing_squad']=='Y')?'<i class="kk-dot"></i>':'';
                        }
                        elseif(!empty($player['file_name']) || CHECK_IMAGE_EXISTS){
                        if(!empty($player['file_name'])){
                        	$filename = explode(',',$player['file_name']);
                        	echo '<img src="'.PLAYER_IMAGE_THUMB_URL . current($filename).'"  class="imagwshow">';
                        		echo ($player['is_in_playing_squad']=='Y')?'<i class="kk-dot"></i>':'';
                        	$meARr['image'] = PLAYER_IMAGE_THUMB_URL . current($filename);
                        }else{
                        	echo '<img src="'.NO_IMG_URL.'"   class="imagwshow">';
                        		echo ($player['is_in_playing_squad']=='Y')?'<i class="kk-dot"></i>':'';
                        	$meARr['image'] = NO_IMG_URL;
                        }
                        }else{
                        	echo '<img src="'.NO_IMG_URL.'"   class="imagwshow">';
                        		echo ($player['is_in_playing_squad']=='Y')?'<i class="kk-dot"></i>':'';
                        	$meARr['image'] = NO_IMG_URL;
                        }
                        ?>
                     <?=$player['name']?> 
                     <?php if($this->uri->segment(3) == "get_our_db_live_playes") {?>
                      <i class="<?php echo ($player['is_in_playing_squad']=='Y')?'cls_Playing':'cls_notPlaying' ?>"><?php echo ($player['is_in_playing_squad']=='Y')?'Playing':'Not Playing' ?></i>
                    <?php } ?>
                  </td>
                  <td class="col-lg-3">
                     <?php
                        $meARr['selected'] = $player['selected_by']. "%";
                        echo $player['selected_by']. "%";
                        ?>
                  </td>
                  <td class="col-lg-3" style="padding-right: 0px;">
                     <?php 
                        echo $player['points'];   
                        $meARr['points'] = $player['points'];
                        ?>
                     <i class="fa fa-eye pull-right css_change statsdata" data-toggle="modal" data-target="#Modelplayer_statistics" modaldata='<?=json_encode(array_merge($meARr));?>'></i>
                    <?php if($this->uri->segment(3) == "get_our_db_live_playes") {?>
                     <i class="fa fa-file-image-o pull-right image_change css_change"  data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($player);?>'></i>
                   <?php } ?>
                  </td>
               </tr>
               <?php
                  }
                  ?>
            </tbody>
         </table>
      </div>
   </div>
   <?php
      }
      ?>
</div>
<!-- Modal -->
<div class="modal fade" id="Modelplayer_statistics" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
         <div class="modal-header" style="background: #412f2f;color: #fff;">
            <button type="button" class="close" data-dismiss="modal" style="color: #f5f3f3;">&times;</button>
            <h4 class="modal-title">POINTS BREAKUP</h4>
         </div>
         <div class="modal-body" id="points_breakup_load" style="background-color: #6db36d;color: #fff;">
         </div>
      </div>
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
            <form action="<?=base_url("{$prefixUrl}image_change_save");?>" method="post" id="image_change_save">
               <div class="row imageload">	
               </div>
               <div class="row ">
                  <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                     <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
                     <button type="submit" class="btn btn btn-success pull-right" style="margin-right: 10px;">Save</button>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<script>
$(document).ready(function(){
    $(document).on('click', ".statsdata", function(e) {		
   		var ele 	= $(this);
   		var ielem 	= $.parseJSON(ele.attr("modaldata"));
   		var imageload ="";
   		if(ielem !=null && ielem!=''){
   			
   		var url	= "<?php echo base_url("admin/matches/get_player_statistics");?>";
           $.post(url,
                   {
                       'postjson':ielem,'t': 't',
                       beforeSend: function() {
                       }}, function(data) {
   						if(data){
   							$("#points_breakup_load").html(data);
   						}else{
   							$("#points_breakup_load").html('<div class="col-sm-12">Not found!</div>');
   						}
   				});
   		}
   	});
   	
    $(document).on('click', ".image_change", function(e) {
   		
   		var imageurl= "<?=PLAYER_IMAGE_THUMB_URL;?>"
   		var ele 	= $(this);
   		var ielem 	= $.parseJSON(ele.attr("modaldata"));
  		
   		var imageload ="";
   		if(ielem.file_name !=null && ielem.file_name!=''){
   			var imagesarr  = ielem.file_name.split(',');
   			for(var i = 0; i < imagesarr.length; i++)
   			{
   				imageload += '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'+imageurl+imagesarr[i]+'" /><input type="radio" name="image_radio" value="'+ielem.id+'_with_'+imagesarr[i]+'" /><i class="fa fa-check hidden"></i></label></div>';
   			}		
   		}else{
   			imageload ='<div class="col-sm-12">Image not found!</div>';
   		}
   		
   		$(".imageload").html(imageload);
   	});
   	
   
    $(document).on("click",".image-radio", function(e){
           $(".image-radio").removeClass('image-radio-checked');
           $(this).addClass('image-radio-checked');
           var $radio = $(this).find('input[type="radio"]');
           $radio.prop("checked",!$radio.prop("checked"));
   
           e.preventDefault();
    });   	
});
   
  $(document).on('submit', "#image_change_save", function(e) {
      e.preventDefault();
      var fields = $(this).serializeArray();
   		var findid = "";
   		var valueimag = "";
   		if(fields !=null && typeof fields[0] !== "undefined"){
   			valueimag = fields[0].value;
   			findid = valueimag.split('_with_')[0];
   		}
   		var url	= $(this).attr("action");   		   		
           $.post(url,
                   {
                       'save_image':valueimag,'t': 't',
                       beforeSend: function() {
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