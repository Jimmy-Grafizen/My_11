<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$batting_key =  array('Every Run Scored','Every Boundary Hit','Every Six Hit','Thirty Runs','Half Century','Century','Dismiss For A Duck');
        $bowling_key  =  array('Wicket',"Two Wicket","Three Wicket",'Four Wicket','Five Wicket','Maiden Over');
        $fielding_key =  array('Run Out','Run Out Thrower','Run Out Catcher','Catch','Stumping');
        $others_key   =  array('Captain'=>"2x",'Vice-Captain'=>"1.5x",'Being Part Of Eleven');
        $economy_rate =  array('Economy Rate');
        $strike_rate  =  array('Strike Rate');
        
        
        $attack_key =  array('For every goal scored (Forward)','For every goal scored (Midfielder)','For every goal scored (GK/Defender)',"For every assist","Chance Created","For shots on target","For every 5 passes completed");
        $defense_key  =  array('Tackle won',"Interception won","Shots saved (GK)",'Penalty saved (GK)','Clean sheet +55 min. (GK/Defender)');
        $other_key =  array('In Starting 11','Coming on as a substitute','Captain'=>"2x",'Vice-Captain'=>"1.5x");
        $card_key   =  array('Yellow card','Red card','For every own goal',"For goals conceded (GK/Defender)","For every penalty missed");
        
        

        $action_key =  array('Points Scored','Rebounds','Assists',"Steals","Blocks","Turn Overs");


$strPage = $_SERVER['REQUEST_URI'];
$strPage = basename($strPage);



  $query =  $dbref->query("SELECT * FROM `tbl_game_types` WHERE `status` = 'A' AND `id` NOT IN (8,9,10) AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");   
        
        $results = $query->result();
        //echo "<pre>"; print_r($results);die;
        $tabscreate = '';
        foreach ($results as $key => $value) {
			$value =(array)$value;
            $activeCls = ($key === 0)?'active':'';
            $tabscreate .='<li role="presentation" class="'.$activeCls.' existtab"><a href="javascript:void(0)" onclick="showtab($(this))" data-id="cricket_'.str_replace(' ','_',$value['name']).'" data-toggle="tab">'.$value['name'].'</a></li>';
        }
        
        
        
        
        
        

        $query =  $dbref->query("SELECT * FROM `tbl_game_types` WHERE `status` = 'A' AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");   
        $results = $query->result();
        $tabscreatenew = array();
        foreach ($results as $key => $value) {
        			$value =(array)$value;
    $activeCls = ($key === 0)?'active':'';
            $strId = $value['id'];
            $query =  $dbref->query("SELECT * FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_type_id`='.$strId.'");

            $resultsTab = $query->result();
            $arrayCompine =   array();
            foreach ($resultsTab as $keyp => $valuep) {
                      			$valuep =(array)$valuep;
   $arrayCompine[$valuep['meta_key']] = $valuep['meta_value'];
            }
           $tabscreatenew[$value['name']] = $arrayCompine;
        } 
        
        
         $query =  $dbref->query("SELECT * FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_id`=2");
            $resultsTab = $query->result();
            $arrayCompineFootball =   array();
            foreach ($resultsTab as $keyp => $valuep) {
         			$valuep =(array)$valuep;
       $arrayCompineFootball[$valuep['meta_key']] = $valuep['meta_value'];
            }
        
 $query =  $dbref->query("SELECT * FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_id`=3");
            $resultsTab = $query->result();
            $arrayCompineBasket =   array();
            foreach ($resultsTab as $keyp => $valuep) {
                  			$valuep =(array)$valuep;
       $arrayCompineBasket[$valuep['meta_key']] = $valuep['meta_value'];
            }

?>
<style>
	.show
	{
	display:block;
	 }
	.tab-content>.tab-pane {
    display: block;
}
	.hidetabspec
	{
	display:none !important;
	}
	.tab-content > .active {
    display: block  !important;
}
</style>
   <section class="page_title">
        <div class="container">
          <h1>Fantasy Point System</h1>
        </div>
      </section>
<section class="section-grey" >
<div class="container-fluid">

<div class="row margin-bottom-40">
<div class="col-md-1"></div>
<div class="col-md-10">
    
    <div class="ftab">
<ul class="nav nav-tabs" role="tablist" style="margin-bottom:20px">
<li role="presentation" class="active parentcell" onclick="settabsdata('cricket',$(this))"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Cricket</a></li>

<li role="presentation" class="parentcell" onclick="settabsdata('football',$(this))"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Football</a></li>
<li role="presentation" class=" parentcell" onclick="settabsdata('basketball',$(this))"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Basketball</a></li>
</ul>
<div class="ftab  commonhide cricket">
<ul class="nav nav-tabs" role="tablist">
<!--<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">ODI</a></li>-->
<?php echo $tabscreate; ?>
<!--<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Test</a></li>
<li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">T10</a></li>
<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">T20</a></li>
<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Women ODI</a></li>
<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">T20I</a></li>
<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Woman T20</a></li>-->
</ul>
<!-- Tab panes -->
<div class="tab-content">
<?php $cou = 0 ; foreach($tabscreatenew as $key=>$value){ ?>
<div role="tabpanel" class="tab-pane <?php echo ($cou == 0 )?'hidetabspec active':' hidetabspec'; ?>" id="cricket_<?php echo str_replace(' ','_',$key) ; ?>">
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneBATTING<?php echo $cou; ?>">BATTING
</a>
</div>
<div id="collapseOneBATTING<?php echo $cou; ?>" class="panel-collapse collapse show">
<div class="panel-body">
    <ul class="list-group">
    <?php
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$batting_key) && $vv>0){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?> badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneBOWLING<?php echo $cou; ?>">BOWLING
</a>
</div>
<div id="collapseOneBOWLING<?php echo $cou; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$bowling_key) && $vv>0){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?>  badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneFIELDING<?php echo $cou; ?>">FIELDING
</a>
</div>
<div id="collapseOneFIELDING<?php echo $cou; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$fielding_key) && $vv>0){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?>  badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneOTHERS<?php echo $cou; ?>">
OTHERS
</a>
</div>
<div id="collapseOneOTHERS<?php echo $cou; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$others_key) && $vv>0){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?> badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneECONOMY<?php echo $cou; ?>">
ECONOMY RATE
</a>
</div>
<div id="collapseOneECONOMY<?php echo $cou; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$economy_rate)){
        $json = json_decode($vv);
        foreach($json as $j=>$k){ ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo 'BETWEEN '.$k->min.' AND '.$k->max ; ?>
                <span class="badge badge-<?php echo ($k->val>0)?'success':'danger'; ?> badge-pill"><?php echo $k->val ; ?></span>
            </li>
   <?php }}} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneSTRIKE<?php echo $cou; ?>">
STRIKE RATE
</a>
</div>
<div id="collapseOneSTRIKE<?php echo $cou; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    $range = array();
    foreach($value as $keyv=>$vv){ if(in_array($keyv,$strike_rate)){
        $json = json_decode($vv);
        foreach($json as $j=>$k){ ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo 'BETWEEN '.$k->min.' AND '.$k->max ; ?>
                <span class="badge badge-<?php echo ($k->val>0)?'success':'danger'; ?>  badge-pill"><?php echo $k->val ; ?></span>
            </li>
       <?php }}} ?>
   </ul>
</div>
</div>
</div>
</div>
</div>
<?php $cou++;} ?>
</div>
</div>

<div class="ftab commonhide football" style="display:none">

<div class="tab-content">
<div role="tabpanel" class="tab-pane <?php echo (0 == 0 )?'active':''; ?>" >
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneBATTING<?php echo 'attack'; ?>">Attack
</a>
</div>
<div id="collapseOneBATTING<?php echo 'attack'; ?>" class="panel-collapse collapse show">
<div class="panel-body">
    <ul class="list-group">
    <?php
    foreach($arrayCompineFootball as $keyv=>$vv){ if(in_array($keyv,$attack_key) ){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?> badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneBOWLING<?php echo 'defense'; ?>">Defense
</a>
</div>
<div id="collapseOneBOWLING<?php echo 'defense'; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($arrayCompineFootball as $keyv=>$vv){ if(in_array($keyv,$defense_key)){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?>  badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneFIELDING<?php echo 'other'; ?>">Other Points
</a>
</div>
<div id="collapseOneFIELDING<?php echo 'other'; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($arrayCompineFootball as $keyv=>$vv){ if(in_array($keyv,$other_key) ){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?>  badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="collapse" href="#collapseOneOTHERS<?php echo 'card'; ?>">
Cards and Other Penalties
</a>
</div>
<div id="collapseOneOTHERS<?php echo 'card'; ?>" class="panel-collapse collapse">
<div class="panel-body">
<ul class="list-group">
    <?php
    foreach($arrayCompineFootball as $keyv=>$vv){ if(in_array($keyv,$card_key) ){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?> badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>
</div>
</div>

</div>


<div class="ftab commonhide basketball" style="display:none">

<div class="tab-content">
<div role="tabpanel" class="tab-pane <?php echo (0 == 0 )?'active':''; ?>" >
<div class="panel-group" role="tablist">
<div class="panel panel-default">
<div class="panel-heading" role="tab">
<a role="button" data-toggle="" href="#collapseOneBATTING<?php echo 'action'; ?>">Action
</a>
</div>
<div id="collapseOneBATTING<?php echo 'action'; ?>" class="panel-collapse collapse show">
<div class="panel-body">
    <ul class="list-group">
    <?php
    foreach($arrayCompineBasket as $keyv=>$vv){ if(in_array($keyv,$action_key) ){?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo $keyv ; ?>
            <span class="badge badge-<?php echo ($vv>0)?'success':'danger'; ?> badge-pill"><?php echo $vv ; ?></span>
        </li>
   <?php }} ?>
   </ul>
</div>
</div>
</div>
</div>

</div>
</div>

</div>

</div>
</div>
<div class="col-md-1"></div>
</div>
</div>
</section>
<script>
    function showtab(obj)
    {
        var id = obj.attr('data-id');
          $('.tab-pane').removeClass('active');
      $('#'+id).addClass('active');
      
      $('.existtab').removeClass('active');
        obj.parents('li').addClass('active');
  }
	
  function settabsdata(type,object)
  {
      $('.commonhide').hide();
         $('.'+type).show();
   $('.parentcell').removeClass('active');
    object.addClass('active');
 }
</script>