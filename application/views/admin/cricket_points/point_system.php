<?php if($sports == "0"){ ?>
<div class="tab-pane active fade in" id="faq-cat-1">
   <div class="panel-group" id="accordion-cat-1">
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-1">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-batting.png" alt="icon"></i> BATTING   <span class="pull-right"><i class="fa fa-plus-circle"></i></span>    
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-1" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($batting_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                        <h3><?php echo ( $data_value=="Century" )?'Century Bonus': @$data_value; ?></h3>
                     </div>
                     <?php if(@$results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?php echo ( $data_value=="Century" )?(@$results['Half Century']+$results['Half Century']): @$results[$data_value]; ?>
                        </div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?php echo ( $data_value=="Century" )?(@$results['Half Century']+@$results['Half Century']): @$results[$data_value]; ?>                           
                        </div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-2">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-bowling.png" alt="icon"></i> BOWLING <span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-2" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($bowling_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                        <h3><?= $data_value ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-3">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-catch.png" alt="icon"></i> FIELDING <span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-3" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($fielding_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                        <h3><?= $data_value ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>

      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-4">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-other-penalties.png" alt="icon"></i> OTHERS <span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-4" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($others_key as $keyOthr => $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                        <h3><?= ( isset($results[$data_value]) ) ? $data_value :$keyOthr; ?></h3>
                     </div>
                     <?php if( isset($results[$data_value]) && $results[$data_value]<0 ){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else if( isset($results[$data_value]) ) { ?>
                           <div class="point_type_pt_box">
                              <div class="top_point">+<?= $results[$data_value] ?></div>
                           </div>
                     <?php
                        }else { ?>
                           <div class="point_type_pt_box">
                              <div class="top_point"><?= $data_value ?></div>
                           </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>
      
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-5">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/economy-rate-icon@2x.png" alt="icon"></i> ECONOMY RATE <span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
               <div class="bottom_label">Min <?= @$results['Minimum Overs for Economy Rate']; ?> overs to be bowled</div>
            </a>
         </div>
         <div id="faq-cat-1-sub-5" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     $economy_rate = json_decode($results['Economy Rate'], true) ;
                     $len = count($economy_rate);


                     foreach($economy_rate as $index=> $economy_rate_row){ 
                        if ($index == 0) {
                             $string = "Below ".$economy_rate_row['max'];
                        }elseif( $index == $len - 1 ) {
                             $string = "Above ".$economy_rate_row['min'];                            
                        }else{
                             $string = "Between ".$economy_rate_row['min']." - ".$economy_rate_row['max'];                            
                        }
                        ?>
                        <li class="point_type">
                           <div class="point_type_label_box">
                              <h3><?= $string; ?> runs per over</h3>
                           </div>
                           <?php if($economy_rate_row['val']<0){
                              ?>
                           <div class="point_type_pt_box minus_value">
                              <div class="top_point"><?= $economy_rate_row['val'] ?></div>
                           </div>
                           <?php
                              }else{ ?>
                           <div class="point_type_pt_box">
                              <div class="top_point">+<?= $economy_rate_row['val'] ?></div>
                           </div>
                           <?php
                              } 
                              ?>
                        </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>
      
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-6">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/strike-rate-icon@2x.png" alt="icon"></i> STRIKE RATE (EXCEPT BOWLER) <span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
               <div class="bottom_label">Min <?= @$results['Minimum Balls for Strike Rate']; ?> balls to be played</div>
            </a>
         </div>
         <div id="faq-cat-1-sub-6" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     $strike_rate = json_decode($results['Strike Rate'], true) ;
                     $lens = count($strike_rate);
                     array_multisort($strike_rate, SORT_DESC);

                     foreach($strike_rate as $index=> $strike_rate_row){ 
                        if ($index == $lens - 1) {
                             $string = "Below ".$strike_rate_row['max'];
                        }/*elseif( $index == $len - 1 ) {
                             $string = "Above ".$strike_rate_row['max'];                            
                        }*/else{
                             $string = "Between ".$strike_rate_row['min']." - ".$strike_rate_row['max'];                            
                        }
                        ?>
                        <li class="point_type">
                           <div class="point_type_label_box">
                              <h3><?= $string; ?> runs per 100 balls</h3>
                           </div>
                           <?php if($strike_rate_row['val']<0){
                              ?>
                           <div class="point_type_pt_box minus_value">
                              <div class="top_point"><?= $strike_rate_row['val'] ?></div>
                           </div>
                           <?php
                              }else{ ?>
                           <div class="point_type_pt_box">
                              <div class="top_point">+<?= $strike_rate_row['val'] ?></div>
                           </div>
                           <?php
                              } 
                              ?>
                        </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>

   </div>
</div>

<?php } else if($sports == "1"){ ?>
                                                                            
  <div class="table-responsive">          
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Action</th>
        <th>Points</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $key => $value) { ?>
         <tr>
           <td><?php echo str_ireplace("_", " ", $value->meta_key ); ?></td>
           <td><?php echo $value->meta_value; ?></td>
         </tr>
      <?php } ?>
    </tbody>
  </table>
  </div>
<?php } else if($sports == "2"){ ?>


<div class="tab-pane active fade in" id="faq-cat-1">
   <div class="panel-group" id="accordion-cat-1">
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-1">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-playing.png" alt="icon"></i>PLAYING TIME<span class="pull-right"><i class="fa fa-plus-circle"></i></span>    
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-1" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($batting_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                        <h3><?= str_ireplace("_", " ", $data_value );  ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>
      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-2">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-attack.png" alt="icon"></i>ATTACK<span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-2" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($bowling_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                          <h3><?= str_ireplace("_", " ", $data_value );  ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>

      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-3">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-defense.png" alt="icon"></i>DEFENSE<span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-3" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($fielding_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                          <h3><?= str_ireplace("_", " ", $data_value );  ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>

      <div class="panel panel-default panel-faq">
         <div class="panel-heading">
            <a data-toggle="collapse"  href="#faq-cat-1-sub-4">
               <h4 class="panel-title">
                  <i class="icon-b"><img src="<?php echo APP_URL; ?>frontassets/m/pages/img/icon-other-penalties.png" alt="icon"></i>CARDS & OTHER PENALTIES<span class="pull-right"><i class="fa fa-plus-circle"></i></span> 
               </h4>
            </a>
         </div>
         <div id="faq-cat-1-sub-4" class="collapse" data-parent="#accordion-cat-1">
            <div class="panel-body">
               <ul class="points-list">
                  <?php 
                     foreach($others_key as  $data_value){ ?>
                  <li class="point_type">
                     <div class="point_type_label_box">
                          <h3><?= str_ireplace("_", " ", $data_value );  ?></h3>
                     </div>
                     <?php if($results[$data_value]<0){
                        ?>
                     <div class="point_type_pt_box minus_value">
                        <div class="top_point"><?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        }else{ ?>
                     <div class="point_type_pt_box">
                        <div class="top_point">+<?= $results[$data_value] ?></div>
                     </div>
                     <?php
                        } 
                        ?>
                  </li>
                  <?php }?>
               </ul>
            </div>
         </div>
      </div>

   </div>
</div>

<?php } ?>