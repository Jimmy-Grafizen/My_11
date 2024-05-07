<?php echo form_open_multipart('admin/players/add_matche_player', array('method' => 'post', 'class' => 'form-horizontal addplayer__', 'id' => 'myform')) ?>
    <div class="form-group">
      <input type="hidden" name="player_unique_id" value="<?= $player_unique_id; ?>">
      <label class="control-label col-sm-2" for="match_unique_id">Match</label>
      <div class="col-sm-10">
          <select name="match_unique_id" class="form-control required " id="match_unique_id">
            <option value="" selected="selected">Please Select Match</option>
          <?php                     
            
            if (!empty($matches)) {
                foreach ($matches as $key =>$datass) {
                    $teamdata = [['team_id'=>$datass['team_1_id'],'team_name'=>$datass['team1_name'] ],['team_id'=>$datass['team_2_id'],'team_name'=>$datass['team2_name']]];
                    echo "<option value='".$datass['unique_id']."' datateam='".json_encode($teamdata)."'>". $datass['name'].' ('.date(DATE_TIME_FORMAT_ADMIN,$datass['match_date']).')</option>';
                }
            }

          ?>
        </select>
      </div>
    </div>
    <div class="teamsdata"></div>

    <div class="form-group">
      <label class="control-label col-sm-2" for="is_in_playing_squad">Is in playing squad</label>
      <div class="col-sm-10">          
        
         
          <div class="col-sm-3">
            <div class="checkbox">
              <label>Yes <input type="radio" name="is_in_playing_squad" class="form-control required" value="Y"> </label>
            </div>
          </div>
 
             
          <div class="col-sm-3">
            <div class="checkbox">
              <label>No <input type="radio" name="is_in_playing_squad" class="form-control required" value="N"></label>
            </div>
          </div>
  
      </div>
    </div>

    <div class="form-group">        
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>

<script>
      $(function() {

        $("#match_unique_id").change(function(){ 
            var element = $(this).find('option:selected'); 

            var myTag = element.attr("datateam"); 

            var htmlset = '<div class="form-group"><label class="control-label col-sm-2" for="Team">Team </label><div class="col-sm-10">';

            if (myTag !== undefined){
                var myTagarr =  JSON.parse(myTag);
                //console.log(myTagarr);
                $.each(myTagarr,function( index, element ) {
                  htmlset +='<div class="col-sm-4"><div class="checkbox"><label>'+element.team_name+' <input type="radio" name="team_id" class="form-control required" value="'+element.team_id+'"> </label> </div> </div>';
                });
                  htmlset +='</div></div>';
                    $( "div.teamsdata" ).html(htmlset);
            }else{
                $( "div.teamsdata" ).html('');
            }

        }); 

  $("form.addplayer__").submit(function(event){
        // Stop full page load
        event.preventDefault();
        var datastring = $(this).serialize();
        var action = $(this).attr('action');

        var matches = $("#match_unique_id");
        var team_id = $("#match_unique_id");

        if (matches.val() =="") {
            alert('Please select Match');
            return false;
        }
        if ($("input[name='team_id']:checked").val() === undefined) {
            alert('Please select team');
            return false;
        }

        if ($("input[name='is_in_playing_squad']:checked").val()  === undefined) {
            alert('Please select playing squad');
            return false;
        }

        $.ajax({
            type: "POST",
            url: action,
            data: datastring,
            dataType: "json",
            success: function(data) {
              if(data.status == 1){
                alert(data.msg);
                $("#player_match_load").html('');
                $("#Modelplayer_add").modal('hide');
              }else{
                alert(data.msg);
              }
            },error: function() {
                alert('Oops something went wrong, please try again.');
            }
        });

    });

    }); 

</script>