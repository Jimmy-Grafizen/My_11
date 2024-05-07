<link rel="stylesheet" type="text/css" href="<?php echo HTTP_PATH; ?>css/add_team_customer_players.css" />
<?php
    $sort_rol = ['Allrounder'=>'AR','Batsman'=>'BAT','Bowler'=>'BOWL','Wicketkeeper'=>'WK'];
    $player_positions = unserialize(PLAYER_POSITIONS);
    //print_r($player_positions);die;
   function addOrdinalNumberSuffix($num) {
       if (!in_array(($num % 100),array(11,12,13))){
         switch ($num % 10) {
           // Handle 1st, 2nd, 3rd
           case 1:  return 'st';
           case 2:  return 'nd';
           case 3:  return 'rd';
         }
       }
       return 'th';
     }
   ?>

<h4></h4>
<div class="col-lg-12 col-xs-12">
    <div class="col-lg-3 col-xs-2 scor-bx">
        <h4>PLAYERS</h4>
        <p><span id="teamselectPlayer">0</span>/<?=$team_seting['MAX_PLAYERS']?></p>
    </div>
    <div class="col-lg-7 col-xs-8  scor-bx">
    <div class="wrapperkk">
      <div class="logokk" style="width: 35px;">
            <img src="<?php echo $team_1logo ; ?>" style="width: 100px;height: 100px;">
      </div>
      <div>
        <div><?php echo $team_1name; ?></div>
        <span id="teamselect_<?php echo $team_1; ?>">0</span>
      </div>
      <div>
        <div>VS</div>
      </div>
      <div>
        <div><?php echo $team_2name; ?></div>
        <span id="teamselect_<?php echo $team_2; ?>">0</span>
      </div>
      <div class="logokk">
        <img src="<?php echo $team_2logo ; ?>"  style="width: 100px;height: 100px;">
      </div>
    </div>
    </div>
    <div class="col-lg-2 col-xs-2 scor-bx">
        <h4>CREDITS LEFT</h4>
        <p><span id="teamselectPlayerCreadit"><?=$team_seting['MAX_CREDITS']?></span>/<?=$team_seting['MAX_CREDITS']?></p>
    </div>
</div>

<div class="col-lg-12">
<h4></h4>
  <div class="table-responsive">          
  <table class="table">
      <tr>
<?php 
   foreach($teamdata as $index => $getTeam){
   ?>
    
         <td>
          <table class="table">
                <th>
        <div>
          <h4><?=$index ?><sub>[min-<?php echo $team_seting['MIN_'.strtoupper($index)] ; ?>,max-<?php echo $team_seting['MAX_'.strtoupper($index)] ; ?>]</sub></h4>
       </div>
        <div class="row">
           <div class="col-lg-6  col-xs-6">PLAYERS</div>
           <div class="col-lg-3  col-xs-3">POINTS</div>
           <div class="col-lg-3  col-xs-3">CREDITS</div>
        </div>
        </th> 
       <?php
          foreach( $getTeam as $player ){
            $meARr = [];    
            $meARr['player_name']=$player['name']; 
           ?>
           <tr>
                <td>
        <label for='<?=$player['player_unique_id']?>' style="display: block;">
            <div class="chip block_of_player checkboxlist row" id="main_<?=$player['player_unique_id']?>" style="padding-right: 8px;">
            <div class="col-lg-6 col-xs-6 p_name">
                 <?php
                    if(!empty($player['image'])){
                        $meARr['image'] = PLAYER_IMAGE_THUMB_URL . $player['image'];
                            echo '<img src="'.PLAYER_IMAGE_THUMB_URL . $player['image'].'">';
                    }
                    elseif(!empty($player['file_name']) || CHECK_IMAGE_EXISTS){
                        if(!empty($player['file_name'])){
                            $filename = explode(',',$player['file_name']);
                            echo '<img src="'.PLAYER_IMAGE_THUMB_URL . current($filename).'">';
                            $meARr['image'] = PLAYER_IMAGE_THUMB_URL . current($filename);
                        }else{
                            echo '<img src="'.NO_IMG_URL.'"  width="96" height="96">';
                            $meARr['image'] = NO_IMG_URL;
                        }
                    }else{
                        echo '<img src="'.NO_IMG_URL.'"  width="96" height="96">';
                        $meARr['image'] = NO_IMG_URL;
                    }
                    ?>
             
                 <?=$player['name']?><sup>[<?=$player['team_sort_name']?>]</sup>
             </div>
             <div class="col-lg-3 col-xs-3 p_points" style="padding-left: 7%;"><?=$player['points'];?> </div>
             <div class="col-lg-3 col-xs-3 p_credits" style="padding-left: 7%;"><?=$player['credits'];?> </div>

             <input name="players[]" type="checkbox" id='<?=$player['player_unique_id']?>' class='chk-btn' value="<?=$player['player_unique_id'];?>__<?=$player['team_id']?>__<?=$player['position']?>__<?=$player['points']?>__<?=$player['credits']?>" />
          </div>
            <div class="group_c_vc" id="group_<?=$player['player_unique_id']?>">
              <span class="c_text">
                <input id="c_<?=$player['player_unique_id']?>" next="vc" type="checkbox" value="<?=$player['player_unique_id']?>" name="C">
                <label for="c_<?=$player['player_unique_id']?>">&nbsp;</label>
              </span>
              <span class="vc_text">
                <input id="vc_<?=$player['player_unique_id']?>" next="c"  type="checkbox" value="<?=$player['player_unique_id']?>" name="VC">
                <label for="vc_<?=$player['player_unique_id']?>">&nbsp;</label>
              </span>
            </div>
        </label>
    </td>
</tr>


       <?php      }    ?>
   </table>
</td>

    <?php      }      ?>
</tr>
</table>

</div>
</div>



<script>
var checkedPlr = [];
var team_seting = <?php echo json_encode($team_seting); ?> ;
var beatthe = "<?php echo($beatthe); ?>";
var team1 = '<?php echo $team_1; ?>';
var team2 = '<?php echo $team_2; ?>';

var bastman = [];
var allRounder = [];
var bolwer = []
var wicketkeeper = [];
var selectePlayer = 0;
var selectedteam = [];
var selectedWicketkeepers = [];
var selectedbastmans = [];
var selectedbolwers = [];
var selectedAllroundes = [];
var teamSetting = team_seting;
var totalSelectedPlayerPoints = 0;
var totalSelectedWckitKeeper = 0;
var totalSelectedBetsman = 0;
var totalSelectedAllrounder = 0;
var totalSelectedBowler = 0;
var totalTeam1Players = 0;
var totalTeam2Players = 0;
var totalSelectedPlayer = 0;

/**********Edit Functionality */

var customer_team_name = $("input#customer_team_name");
var team_name = $("input#team_name");
var submitbtn_t = $("button#submitbtn_t");
var Updatesubmitbtn_t = $("button#Updatesubmitbtn_t");
var customer_team_id = $("input#customer_team_id");

function oldDataRemove() {
    /*********************/
    checkedPlr = [];

    $('input[name="players[]"]').each(function() {
        $(this).parent().removeClass('highlight');
        $(this).attr('checked', false);
        // console.log($(this).attr('id'));
    });
    $("div.group_c_vc").hide();
    $("#our_rec_fetch").find('input[type=checkbox][name=C]').prop("checked", false).trigger('change');
    $("#our_rec_fetch").find('input[type=checkbox][name=VC]').prop("checked", false).trigger('change');
      bastman = [];
      allRounder = [];
      bolwer = []
      wicketkeeper = [];
      selectePlayer = 0;
      selectedteam = [];
      selectedWicketkeepers = [];
      selectedbastmans = [];
      selectedbolwers = [];
      selectedAllroundes = [];
      teamSetting = team_seting;
      totalSelectedPlayerPoints = 0;
      totalSelectedWckitKeeper = 0;
      totalSelectedBetsman = 0;
      totalSelectedAllrounder = 0;
      totalSelectedBowler = 0;
      totalTeam1Players = 0;
      totalTeam2Players = 0;
      totalSelectedPlayer = 0;
    /*********************/

        $("#teamselect_" + team1).text(0);
        $("#teamselect_" + team2).text(0);
        var leftCredit = teamSetting.MAX_CREDITS - totalSelectedPlayerPoints;
        $("#teamselectPlayerCreadit").text(leftCredit);
        $("#teamselectPlayer").text(checkedPlr.length);
}

    if (beatthe > 0) {
       // alert(beattheis_admin);
        setTimeout(function(){ 
            $('select#user_id').val(beattheis_admin).trigger('change');
         }, 100);
    }

$("#selectedct").hide();
$("select#user_id").on("change", function() {
    oldDataRemove();
    var thisSele = $(this);
    var $selectedct = $("#selectedct");
    var thisSeleVal = $(this).val();
    var url = '<?php echo base_url("admin/matches/GetJoinedContest_teams_with_customer/".$unique_id."__");?>' + thisSeleVal+"?beatthe=<?=$beatthe ?>";
    //return false;
    $.post(url, {
        't': 't',
        beforeSend: function() {
            customer_team_name.prop('readonly', false).val("");
            team_name.prop('readonly', false).val("");
            customer_team_id.val("0");
            Updatesubmitbtn_t.hide();
        }
    }, function(data) {

        if (data) {
            $selectedct.show();
            $selectedct.html(data);
        } else {
            $selectedct.html("");
            $selectedct.hide();
        }

     if (beatthe >0) {
            setTimeout(function(){ 
                $('select#customer_teams_id').val($('select#customer_teams_id option:first').val()).trigger('change');
             }, 100);
        }
    });
});

$(document).on("change", "select#customer_teams_id", function() {

    oldDataRemove();

    var thisSele = $(this);
    var $selectedct = $("#selectedct");
    var thisSeleVal = $("select#user_id").val();
    var SelValTea = thisSele.val();
    var url = '<?php echo base_url("admin/matches/GetJoinedContest_teams_with_customer_players/".$unique_id."__");?>' + thisSeleVal + "__" + SelValTea;
    //return false;
    $.post(url, {
        't': 't',
        beforeSend: function() {
            customer_team_name.prop('readonly', false).val("");
            team_name.prop('readonly', false).val("");
            customer_team_id.val("0");
            Updatesubmitbtn_t.hide();
        }
    }, function(data) {
        if (data) {
            var player_unique_ids = data.player_unique_ids;
            var Array_pu_ids = player_unique_ids.split(',');
            var time = 10;
            // console.log(Array_pu_ids); 

            customer_team_name.prop('readonly', true).val(data.customer_team_name);
            team_name.prop('readonly', true).val(data.more_name);
            customer_team_id.val(data.id);
            Updatesubmitbtn_t.show();

            var playersdata = data.playersdata;

            $(Array_pu_ids).each(function(k, v) {
                //setTimeout(function(){ $('#'+v).prop("checked", true).trigger('change'); }, time);
                $('#' + v).prop("checked", true).trigger('change');

                if (playersdata[v] == "2") {
                    $('#c_' + v).prop("checked", true).trigger('change');
                }
                if (playersdata[v] == "1.5") {
                    $('#vc_' + v).prop("checked", true).trigger('change');
                }

                time += 10;
            });

        } else {
            //alert(data);
        }
    });
});
/**********Edit Functionality End*/

$(function() {
    $("div.group_c_vc").hide();
    // listen for changes on the checkboxes
    $('input[name="players[]"]').change(function() {
        //$("div.group_c_vc").hide();
        /******************************/
        var playersdata = $(this).val().split("__");
        var position = playersdata[2];
        var points = parseFloat(playersdata[3]);
        var credits = parseFloat(playersdata[4]);

        var betdata = {
            "player_id": playersdata[0],
            "player_multiplier": '1',
            "player_pos": "",
            "position": position,
            "points": points,
            "credits": credits,
            "team_id": playersdata[1]
        };
        const result = checkedPlr.find(item => item.player_id === betdata.player_id);
        if (!result) {
            var selectPlayers = onPlayerSelected(betdata)
            if (selectPlayers == "") {
                checkedPlr.push(betdata)
                getWicketkeapers(betdata);
                getBatsmans(betdata);
                getAllrounders(betdata);
                getBowlers(betdata);
                $(this).parent().toggleClass('highlight', this.checked);
            } else {
                alert(selectPlayers);
                $(this).attr('checked', false);
                return false

            }

        } else {
            const index = checkedPlr.findIndex(m => m.player_id === betdata.player_id);
            checkedPlr.splice(index, 1);
            getWicketkeapersR(betdata);
            getBatsmansR(betdata);
            getAllroundersR(betdata);
            getBowlersR(betdata);
            $(this).parent().toggleClass('highlight', this.checked);
        }

        var teamselectPlayerCreadit = teamSetting.MAX_CREDITS;
        var team1count = 0;
        var team2count = 0;
        $.each(checkedPlr, function(key, val) {
            teamselectPlayerCreadit -= val.credits
            if (team1 == val.team_id) {
                ++team1count;
            } else {
                ++team2count;
            }
            // console.log(val.player_id);
            //console.log(team_seting.MAX_PLAYERS == checkedPlr.length );
            if (team_seting.MAX_PLAYERS == checkedPlr.length) {
                $("div#group_" + val.player_id).show();
            } else {
                $("div.group_c_vc").hide();
            }
        });
        $("#teamselect_" + team1).text(team1count);
        $("#teamselect_" + team2).text(team2count);
        $("#teamselectPlayerCreadit").text(teamselectPlayerCreadit);
        $("#teamselectPlayer").text(checkedPlr.length);

        //console.log(checkedPlr);
    });

});




$(document).on('submit', "#myform", function(e) {
    e.preventDefault();
    var fields = getFormData($(this));
    var is_update = $(document.activeElement).attr('value');
    fields['user_id'] = $("select#user_id").val();
    fields['is_update'] = is_update;
    //console.log(fields); 
    //return false;
    if (checkedPlr[0] == null && typeof checkedPlr[0] == "undefined") {
        alert("Please selecte team player");
        return false;
    }
    //if (checkedPlr.length < team_seting.MAX_PLAYERS) {
    if (getTotalSelectedPlayers() < team_seting.MAX_PLAYERS) {
        alert("Please add maximum " + team_seting.MAX_PLAYERS + "th players in the team.");
        return false
    }
    var c_checked = $("#our_rec_fetch").find('input[type=checkbox][name=C]:checked');
    var vc_checked = $("#our_rec_fetch").find('input[type=checkbox][name=VC]:checked');
    if (c_checked.length == 0) {
        alert("Please select any one captain in team.");
        return false
    }
    if (c_checked.length > 1) {
        alert("Please select only one captain in team.");
        return false
    }
    if (vc_checked.length == 0) {
        alert("Please select any one vice captain in team.");
        return false
    }
    if (vc_checked.length > 1) {
        alert("Please select only one vice captain in team.");
        return false
    }
    var player_json = [];
    var player_posset = 1;
    $.each(checkedPlr, function(p_key, p_val) {
        if (c_checked.val() == p_val.player_id) {
            p_val.player_multiplier = "2";
        } else if (vc_checked.val() == p_val.player_id) {
            p_val.player_multiplier = "1.5";
        } else {
            p_val.player_multiplier = "1";
        }
        p_val.player_pos = player_posset;
        player_json.push(p_val);
        player_posset++;
    });


    //console.log(player_json ); 
    //console.log(vc_checked.length ); 
    //return false;

    var url = $(this).attr("action");
    //return false;
    $.post(url, {
        'fields': fields,
        'player_json': player_json,
        't': 't',
        beforeSend: function() {}
    }, function(data) {
        var obj = JSON.parse(data);
        if (obj.code == 0) {
            window.location.href = window.location.href;
        } else {
            alert(obj.message);
        }
    });
    // return false;
});

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}
/*****************************************create team validation**************************************************/
//console.log(team_seting);

function getWicketkeapers(betdata) {
    if (getPalyerType(betdata.position) == 1) {
        selectedWicketkeepers.push(betdata);
        totalSelectedWckitKeeper++;
        totalSelectedPlayerPoints += parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players++;
        } else {
            totalTeam2Players++;
        }
    }
}

function getBatsmans(betdata) {
    if (getPalyerType(betdata.position) == 2) {
        selectedbastmans.push(betdata);
        totalSelectedBetsman++;
        totalSelectedPlayerPoints += parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players++;
        } else {
            totalTeam2Players++;
        }
    }
}

function getAllrounders(betdata) {
    if (getPalyerType(betdata.position) == 3) {
        selectedAllroundes.push(betdata);
        totalSelectedAllrounder++;
        totalSelectedPlayerPoints += parseFloat(betdata.credits);
        if (betdata.team_id === team1) {
            totalTeam1Players++;
        } else {
            totalTeam2Players++;
        }
    }
}

function getBowlers(betdata) {
    if (getPalyerType(betdata.position) == 4) {
        selectedbolwers.push(betdata);
        totalSelectedBowler++;
        totalSelectedPlayerPoints += parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players++;
        } else {
            totalTeam2Players++;
        }
    }
}

function getWicketkeapersR(betdata) {
    if (getPalyerType(betdata.position) == 1) {
        const index = selectedWicketkeepers.findIndex(m => m.player_id === betdata.player_id);
        selectedWicketkeepers.splice(index, 1);
        totalSelectedWckitKeeper--;
        totalSelectedPlayerPoints -= parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players--;
        } else {
            totalTeam2Players--;
        }
    }
}

function getBatsmansR(betdata) {
    if (getPalyerType(betdata.position) == 2) {
        const index = selectedbastmans.findIndex(m => m.player_id === betdata.player_id);
        selectedbastmans.splice(index, 1);
        totalSelectedBetsman--;
        totalSelectedPlayerPoints -= parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players--;
        } else {
            totalTeam2Players--;
        }
    }
}

function getAllroundersR(betdata) {
    if (getPalyerType(betdata.position) == 3) {
        const index = selectedAllroundes.findIndex(m => m.player_id === betdata.player_id);
        selectedAllroundes.splice(index, 1);
        totalSelectedAllrounder--;
        totalSelectedPlayerPoints -= parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players--;
        } else {
            totalTeam2Players--;
        }
    }
}

function getBowlersR(betdata) {
    if (getPalyerType(betdata.position) == 4) {
        const index = selectedbolwers.findIndex(m => m.player_id === betdata.player_id);
        selectedbolwers.splice(index, 1);
        totalSelectedBowler--
        totalSelectedPlayerPoints -= parseFloat(betdata.credits);
        if (betdata.team_id == team1) {
            totalTeam1Players--;
        } else {
            totalTeam2Players--;
        }
    }
}

function getPlayerCount(type) {
    if (type == 1) {
        return totalSelectedWckitKeeper;
    } else if (type == 2) {
        return totalSelectedBetsman;
    } else if (type == 3) {
        return totalSelectedAllrounder;
    } else if (type == 4) {
        return totalSelectedBowler;
    }
    return 0;
}


function getMaxAllowedPlayerCount(type) {
    if (type == 1) {
        return teamSetting.MAX_WICKETKEEPER;
    } else if (type == 2) {
        return teamSetting.MAX_BATSMAN;
    } else if (type == 3) {
        return teamSetting.MAX_ALLROUNDER;
    } else if (type == 4) {
        return teamSetting.MAX_BOWLER;
    }
    return 0;
}

function getMinRequiredPlayerCount(type) {
    if (type == 1) {
        return teamSetting.MIN_WICKETKEEPER;
    } else if (type == 2) {
        return teamSetting.MIN_BATSMAN;
    } else if (type == 3) {
        return teamSetting.MIN_ALLROUNDER;
    } else if (type == 4) {
        return teamSetting.MIN_BOWLER;
    }
    return 0;
}

function getTotalSelectedPlayers() {
    return totalSelectedWckitKeeper + totalSelectedBetsman + totalSelectedAllrounder + totalSelectedBowler;
}

/**
 * isMaxFrom1Team
 */
function isMaxFrom1Team(teamId) {

    var playerCount = 0;
    if (team1 == teamId) {
        playerCount = totalTeam1Players;
    } else {
        playerCount = totalTeam2Players;
    }

    return playerCount < teamSetting.MAX_PLAYERS_PER_TEAM;
}
/**
 * isMaxPlayerType    
 */
function isMaxPlayerType(type) {
    var selectedPlayerTypeCount = getPlayerCount(type);
    var maxAllowlledPlayerTypeCount = getMaxAllowedPlayerCount(type);
    return selectedPlayerTypeCount < maxAllowlledPlayerTypeCount;
}
/**
 * isMinPlayerFailed
 */
function isMinPlayerFailed(type) {

    var previousSelectedPlayer = getPlayerCount(type);
    previousSelectedPlayer++;
    var requiredSelectedPlayer = 0;
    for (var i = 1; i < 5; i++) {
        var playerCount1 = getPlayerCount(i);
        var minPlayer = getMinRequiredPlayerCount(i);
        if (i != type) {
            requiredSelectedPlayer += Math.max(playerCount1, minPlayer);
        }
    }
    if (requiredSelectedPlayer + previousSelectedPlayer > teamSetting.MAX_PLAYERS) {
        return true;
    }
    return false;
}
/**
 * getPlayerTypeFullName():any
 */
function getPlayerTypeFullName(type) {

    switch (type) {
        case 1:
            return "Wicketkeeper"
        case 2:
            return "Bastman"
        case 3:
            return "Allrounder"
        case 4:
            return "Bolwer"
        default:
            return ""
    }
}
/**
 * getPalyerType
 */
function getPalyerType(type) {

    if (type.toLowerCase() == "wicketkeeper") {
        return 1
    }
    if (type.toLowerCase() == "batsman") {
        return 2
    }
    if (type.toLowerCase() == "allrounder") {
        return 3
    }
    if (type.toLowerCase() == "bowler") {
        return 4
    }
}



function isCreditExceed(playerCredit) {
    var totalCredit = totalSelectedPlayerPoints + playerCredit;
    if (totalCredit > teamSetting.MAX_CREDITS) {
        return true;
    }
    return false;
}
/**
 * name
 */

function onPlayerSelected(betdata) {
    var errorMessage = "";
    if (getTotalSelectedPlayers() < teamSetting.MAX_PLAYERS) {
        if (isMaxFrom1Team(betdata.team_id)) {
            if (isMaxPlayerType(getPalyerType(betdata.position))) {
                //
                if (isMinPlayerFailed(getPalyerType(betdata.position))) {
                    for (var i = 1; i < 5; i++) {
                        var playerCount1 = getPlayerCount(i);
                        if (playerCount1 < getMinRequiredPlayerCount(i)) {
                            errorMessage = "Minimum " + getMinRequiredPlayerCount(i) + '  ' + getPlayerTypeFullName(i) + ' required';
                            return errorMessage;
                        }
                    }
                }
                if (!isCreditExceed(betdata.credits)) {
                    errorMessage = "";
                    return errorMessage;
                } else {
                    var leftCredit = teamSetting.MAX_CREDITS - totalSelectedPlayerPoints;

                    errorMessage = "Only " + leftCredit + " credit Left", "Error";
                }
            } else {
                //
                errorMessage = "you can select only " + getMaxAllowedPlayerCount(getPalyerType(betdata.position)) + '  ' + getPlayerTypeFullName(getPalyerType(betdata.position));
            }
        } else {
            errorMessage = "You can select Max " + teamSetting.MAX_PLAYERS_PER_TEAM + " players from 1 team", 'error';
        }
    } else {
        errorMessage = "you already selected " + teamSetting.MAX_PLAYERS + "players", 'error';
    }
    return errorMessage;
}

$("div.group_c_vc input:checkbox").on('click', function() {
    var $box = $(this);
    if ($box.is(":checked")) {
        var group = "input:checkbox[name='" + $box.attr("name") + "']";

        var ischeckchck = $box.val();
        var isnextck = $box.attr("next");
        var ifcodition = $("input#" + isnextck + "_" + ischeckchck);
        if (ifcodition.is(":checked")) {
            $(ifcodition).prop("checked", false);
        }
        $(group).prop("checked", false);
        $box.prop("checked", true);
    } else {
        $box.prop("checked", false);
    }
});

</script>