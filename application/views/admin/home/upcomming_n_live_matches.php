<div class="card table-card">
    <div class="card-header">
        <h5><?=$names?> Matches</h5>
        <div class="card-header-right">
        </div>
    </div>
    <div class="card-block">
        <div class="table-responsive">
            <table class="table table-hover  table-borderless">
                <thead>
                    <tr>
                        <th>Unique id</th>
                        <!--th>Match Name</th-->
                        <th>Series/Teams</th>                        
                        <th>Match date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        foreach ($records as $row) {
                    ?> 
                                    <tr>
                                        <td data-title="unique_id">
                                            <?php echo ucfirst($row->unique_id); ?>
                                        </td>
                                        <!--td data-title="Name">
                                            <?php echo ucfirst($row->name); ?>
                                        </td-->
                                        <td data-title="Series Name">
                                            <div class="d-inline-block align-middle">
                                                <h6><?php echo ucfirst($row->series_name); ?></h6>
                                                <h6><?php echo ucfirst($row->team_1_sort_name); ?> vs <?php echo ucfirst($row->team_2_sort_name); ?> </h6>                                                
                                            </div>
                                        </td>
                                        <td data-title="Match Date">
                                            <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->match_date); ?>
                                        </td>
                                        <td data-title="Action">
                                            <?php
                                            if($name =='Live'){
                                            echo anchor($prefixUrl.'live_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-trophy"></i>', array('title' => 'Add Contests', 'class' => 'btn btn-primary btn-xs m-r-5'));                                            
                                            echo anchor($prefixUrl.'view_live/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Match Team', 'class' => 'btn btn-primary btn-xs '));
                                            
                                            }else{
                                            echo anchor($prefixUrl.'add_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-trophy"></i>', array('title' => 'Add Contests', 'class' => 'btn btn-primary btn-xs m-r-5'));                                            
                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Match Team', 'class' => 'btn btn-primary btn-xs '));
                                            }
                                            ?>
                                        </td>   
                                    </tr>
                                <?php } ?>
                    
                </tbody>
            </table>
            <div class="text-center">
                <?php if($name =='Live'){  ?>
                    <a href="<?= base_url('/admin/matches/live'); ?>" class=" b-b-primary text-primary">View <?=$names?></a>
               <?php } else{ ?>
                <a href="<?= base_url('/admin/matches/index'); ?>" class=" b-b-primary text-primary">View <?=$names?></a>
            <?php }  ?>
            </div>
        </div>
    </div>
</div>