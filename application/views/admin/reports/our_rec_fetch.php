<style>
i.image_change{
    margin-top: 9px;
    font-size: 22px;
    cursor: pointer;
}

.image-radio {
    cursor: pointer;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    border: 4px solid transparent;
    margin-bottom: 0;
    outline: 0;
}
.image-radio input[type="radio"] {
    display: none;
}
.image-radio-checked {
    border-color: #4783B0;
}
.image-radio .fa {
  position: absolute;
  color: #4A79A3;
  background-color: #fff;
  padding: 10px;
  top: 0;
  right: 15px;
  opacity:0.8;
}
.image-radio-checked .fa {
  display: block !important;
  visibility: visible !important;
}
.image-radio img {
  height: 80px!important;
}

</style>

<div class="col-lg-6">
<h4><b>Our DB<b></h1>
<?php 
	foreach($squad as $getTeam){
?>
<div class="col-lg-12">
<h4><?=$getTeam['name'] ?> <span class="red_star">*</span></h4>
</div>
<div class="col-lg-12">
<?php
	
foreach( $getTeam['players'] as $player ){
		?>
<div class="chip block_of_player" id="main_<?=$player['id']?>">

<?php
if(!empty($player['image'])){
		echo '<img src="'.PLAYER_IMAGE_THUMB_URL . $player['image'].'">';
}
elseif(!empty($player['file_name']) || CHECK_IMAGE_EXISTS){
if(!empty($player['file_name'])){
	$filename = explode(',',$player['file_name']);
	echo '<img src="'.PLAYER_IMAGE_THUMB_URL . current($filename).'">';
}else{
	echo '<img src="'.NO_IMG_URL.'"  width="96" height="96">';
}
}else{
	echo '<img src="'.NO_IMG_URL.'"  width="96" height="96">';
}
?>
		<?=$player['name']?>
<i class="fa fa-file-image-o pull-right image_change"  data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($player);?>'></i>
<?php
if ($player['status']=="D")
	echo anchor("{$prefixUrl}activate/" . $player['id'], '<i class="fa fa-check"></i>', 'data_url="'.base_url().$prefixUrl.'deactivate/' . $player['id'].'" title="Activate" class="btn btn-success btn-xs action-list"');
else
	echo anchor("{$prefixUrl}deactivate/" . $player['id'], '<i class="fa fa-check"></i>', 'data_url="'.base_url().$prefixUrl.'activate/' . $player['id'].'" title="Deactivate" class="btn btn-danger btn-xs action-list"');
 ?>
</div>

<?php
	}
echo "</div>";
}
?>
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

	
    // for actions tab
    $(document).on('click', ".image_change", function(e) {
        //e.preventDefault();
		var imageurl= "<?=PLAYER_IMAGE_THUMB_URL;?>"
		var ele 	= $(this);
		var ielem 	= $.parseJSON(ele.attr("modaldata"));
		
		var imageload ="";
		if(ielem.file_name !=null && ielem.file_name!=''){
			var imagesarr  = ielem.file_name.split(',');
			// Iterate through each value
			for(var i = 0; i < imagesarr.length; i++)
			{
				imageload += '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'+imageurl+imagesarr[i]+'" /><input type="radio" name="image_radio" value="'+ielem.id+'_with_'+imagesarr[i]+'" /><i class="fa fa-check hidden"></i></label></div>';
			}		
		}else{
			imageload ='<div class="col-sm-12">Image not found!</div>';
		}
		
		$(".imageload").html(imageload);
	});
	
	    // add/remove checked class

    // sync the input state
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
			// console.log(findid);
		}
		var url	= $(this).attr("action");
		//alert(url); 
		
		//return false;
		
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
			// return false;
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