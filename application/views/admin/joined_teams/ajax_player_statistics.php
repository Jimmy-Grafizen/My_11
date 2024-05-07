<?php 
if(count($unique_data)>0){
?>
<div class="w3-container">
  <h2><?php echo $unique_data['name']; ?></h2>

  <div class="w3-card-4" style="width:100%">
    
    <div class="w3-container" style="margin-bottom: 10px;">
   <header class="w3-container">
      <h3><?php echo $unique_data['player_name']; ?></h3>
    </header>
      <img src="<?php echo $unique_data['image']; ?>" alt="Avatar" class="w3-left w3-circle w3-margin-right" style="width:60px"/>
 <p><span class="w3-button__kk">SELECTED BY <br><?php echo $unique_data['selected']; ?> </span>
 <span class="w3-button__kk" style="margin-left: 20%;">POINTS <br><?php echo $unique_data['points']; ?> </span>
 </p><br>

     </div>
  </div>
</div>
<div class="col-lg-6">EVETN</div>
<div class="col-lg-3">ACTUAL</div>
<div class="col-lg-3">POINTS</div>
   <?php
   //$breckup_points = [];
   if(isset($breckup_points) && !empty($breckup_points)){
      //$breckup_points = @$breckup_points[0];
   }
   ?>
<div class="chip__kk block_of_player" id="main_kkk"  style="padding-right: 8px;overflow: auto;scroll-behavior: smooth;overflow-y: initial;height: 500px;">
   
   <div class="col-lg-6">Starting 11</div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Being_Part_Of_Eleven_Value']==1?"YES":"NO";
   
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Being_Part_Of_Eleven'];
   
   ?>
   </div>
   
   <div class="col-lg-6">Runs</div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Every_Run_Scored_Value'];
   
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Every_Run_Scored'];
   
   ?>
   </div>
   
   <div class="col-lg-6">4's
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Dismiss_For_A_Duck_Value'];
   
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Dismiss_For_A_Duck'];
   
   ?>
   </div>
   
   
   <div class="col-lg-6">6's
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Every_Six_Hit_Value'];
   
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Every_Six_Hit'];
   ?>
   </div>
   <?php if(isset($breckup_points[0]['Century_Value']) && empty($breckup_points[0]['Century_Value']) ) { ?>
   <div class="col-lg-6">50
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Half_Century_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Half_Century'];
   
   ?>
   </div>
   <?php } else{ ?>
   <div class="col-lg-6">100
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Century_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Century'];
   ?>
   </div>
   <?php } ?>
   <div class="col-lg-6">Duck
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
;
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Dismiss_For_A_Duck'];
   ?>
   </div>
   
   <div class="col-lg-6">Wkts
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Wicket_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php

      echo @$breckup_points[0]['Wicket'];
   
   ?>
   </div>
   <?php if(isset($breckup_points[0]['Five_Wicket_Value']) && empty($breckup_points[0]['Five_Wicket_Value']) ) { ?>
   <div class="col-lg-6">4 Wkts
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Four_Wicket_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php

      echo @$breckup_points[0]['Four_Wicket'];
   
   ?>
   </div>
   <?php }else{ ?>
   <div class="col-lg-6">5 Wkts
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Five_Wicket_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php

      echo @$breckup_points[0]['Five_Wicket'];
   
   ?>
   </div>
   <?php } ?>
   
   <div class="col-lg-6">Maiden Over
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Maiden_Over_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Maiden_Over'];
   ?>
   </div>
   
   
   <div class="col-lg-6">Catch
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Catch_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Catch'];
   
   ?>
   </div>
   
   <?php /*
   <div class="col-lg-6">Catch And Bowled
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Catch_And_Bowled_Value'];
   
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Catch_And_Bowled'];
   
   ?>
   </div>
   
   */ ?>
   <div class="col-lg-6">Stumping Value
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Stumping_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Stumping'];
   ?>
   </div>
   
   <div class="col-lg-6">Run Out 
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Run_Out_Value'];
   ?>
   </div>
   <div class="col-lg-3">&nbsp;
   <?php
      echo @$breckup_points[0]['Run_Out'];
   ?>
   </div>
   
   
</div>
<?php
}
   ?>