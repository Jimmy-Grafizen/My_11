<?php
   foreach( $childQuery->result_array() as $keyChild =>$valChild ){
?>
   <tr class="childshow table_more_child_<?php  echo ($valChild['parent_id']);?>" style="display: none;">
      <td></td>
      <td>
         <?php  echo ($valChild['total_price']);?>
      </td>
      <td class="">
         <b type="button" title='View Contest' class="views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($valChild);?>' style="cursor: pointer;">
         <?php  
            if( !empty($valChild['contest_json']) ){
               $contest_json = json_decode($valChild['contest_json']);
               $per_max_p = $contest_json->per_max_p;
               echo end($per_max_p);
            }?>
         <i class="fa fa-eye"></i>
         </b>
      </td>

      <td class="">
         <?php 
            $more_entry_fees   = $valChild['more_entry_fees'];
            $entry_fees        = $valChild['entry_fees'];
            $actual_entry_fees = $valChild['actual_entry_fees'];
            echo $actual_entry_fees; ?>
      </td>
      <td class="">
         <?php echo ($valChild['more_entry_fees']);?>
      </td>
      <td class="">
         <?php echo ($valChild['entry_fees']);?>
      </td>
      
      <td class="">
         <?php
            if (isset($valChild['counter'])  && $valChild['counter'] >0 ){
               echo ( (int)$valChild['total_team'] -(int)$valChild['counter'] );
            }else{
               echo $valChild['total_team'];
            }
            ?>
      </td>

      <td class="">
         <?php
            if (isset($valChild['counter'] ) && $valChild['counter'] > 0 )
            {
               echo'<b style="cursor: pointer;">';
               echo anchor('admin/joined_teams/sets?ccm='.$valChild['id'], $valChild['counter'],array('title' => 'View Teams','dataid' => $valChild['id'], 'class' => 'teamsclsviews')); 
             echo "</b>/";
             echo  $valChild['total_team'];                       
            echo anchor('admin/joined_teams/sets?ccm='.$valChild['id'], '<i class="fa fa-eye"></i>',array('title' => 'View Teams','dataid' => $valChild['id'], 'class' => 'teamsclsviews')); 
            }else{
               echo 0;
               echo "/";
               echo  $valChild['total_team'];                     
            }
            ?>
      </td>
      <td><?php echo ($valChild['confirm_win'] == 'Y') ? "Yes": "No/".$valChild['confirm_win_contest_percentage'];?></td>
      <td><?php echo $valChild['per_user_team_allowed'];?></td>
      <td><?php echo ($valChild['multi_team_allowed']=="Y")? "Yes": "No" ;?></td>
      <td><?php echo ($valChild['is_compression_allow']=="Y")? "Yes": "No" ;?></td>
      <td><?php echo ($valChild['is_duplicate_allow']=="Y")? "Yes": "No" ;?></td>
      <td><?php echo $valChild['duplicate_count'];?></td>
      <td></td>
      <td>
         <a class="btn btn-primary" href="<?php echo base_url($prefixUrl."add_team_customer/".$id); ?>?tccm_id=<?= $valChild['id']; ?>" style="font-size: 12px;padding: 4px;margin-bottom: 4px;">Create Team</a>
      </td>
   </tr>
<?php 
   } 
?>