<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
				<?=$names?> List
                </header>
                <div class="panel-body">
               </div>
                <div class="panel-body">
                    <section id="no-more-tables" class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.unique_id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.unique_id">Unique id</th>	
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.match_limit", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.match_limit">Customer team Limit</th>	
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.image", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.image">Image</th>	
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.name">Match Name</th>   
									<th class="<?php echo $this->main_model->getsortclass("series.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="series.name">Series Name</th>
                                    <!--th class="<?php echo $this->main_model->getsortclass("game.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="game.name">Game name</th-->
                                    <th class="<?php echo $this->main_model->getsortclass("game_type.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="game_type.name">Game type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_teams1.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams1.name">Team-1</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_teams2.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams2.name">Team-2</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.match_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.match_date">Match date</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.close_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.close_date">Close date</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.created_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.created_at">Created</th>
                                    <th  style="width:140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($records as $row) {
                                    ?> 
                                    <tr>
                                        <td data-title="Select">
                                            <?php
                                            $data = array(
                                                'name' => "check[]",
                                                'id' => "table-selected-1",
                                                'value' => $row->id,
                                            );
                                            echo form_checkbox(array('name' => 'check[]', 'value' => '' . $row->id, 'class' => 'className'));
                                            ?>
                                        </td>
                                        <td data-title="unique_id">
                                            <?php echo ucfirst($row->unique_id); ?>
                                        </td>
                                        <td data-title="match_limit">
                                            <?php echo ucfirst($row->match_limit); ?>
                                        </td>
                                        <td data-title="Image">
                                    
                                            <?php 
                                                if( !empty($row->image) )
                                                    echo '<img src="'.MATCH_IMAGE_THUMB_URL.$row->image.'">';
                                                else
                                                    echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                            ?>
                                        
                                        </td>
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->name); ?>
                                        </td>
                                        <td data-title="Series Name">
                                            <?php
                                         ///   print_r($row);die();
                                            echo ucfirst($row->series_name); ?>
                                        </td>
                                        <!--td data-title="Game Name">
                                            <?php echo ucfirst($row->game_name); ?>
                                        </td-->
                                        <td data-title="Game type Name">
                                            <?php echo ucfirst($row->game_type_name); ?>
                                        </td>
                                        <td data-title="Team-1">
                                            <?php 
                                                echo ucfirst($row->team_1_name)." <b style='font-size: 10px;'>({$row->team_1_sort_name})</b><br>"; 
                                                if( !empty($row->team_1_image) ){
                                                    echo '<img src="'.$row->team_1_image.'" style="width: 32px;">';
                                                }
                                                else{
                                                    echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                                }

                                            echo anchor('admin/team_crickets/edit/' . $row->team_1_id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Team', 'class' => 'btn btn-primary btn-xs m-l-5'));

                                                $files1 = null;
                                                if($row->team_1_file_name){
                                                    $files1 = array_unique ( explode(",", $row->team_1_file_name) );
                                                    $files1 = implode(",", $files1);
                                                }
                                                $team1Array = ["id"=>$row->team_1_id, "name"=>$row->team_1_name, "sort_name"=> $row->team_1_sort_name, "unique_id"=> null,  "logo"=> $row->team_1_image,  "status"=>"A", "is_deleted"=> $row->match_date, "created_at"=>  $row->match_date,  "created_by"=> $this->session->userdata('adminId'),  "updated_at"=> time(),  "updated_by"=> $this->session->userdata('adminId'),  "firstname"=> null,  "file_name"=> $files1];
                                            ?>
                                            <button class="btn btn-primary btn-xs" type="button" title="Default Team Jersey">
                                            <i class="fa fa-file-image-o image_change"  data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($team1Array,JSON_HEX_APOS);?>'></i></button>

                                        </td>
                                        <td data-title="Team-2">
                                            <?php 
                                            echo ucfirst($row->team_2_name)." <b style='font-size: 10px;'>({$row->team_2_sort_name})</b><br>"; 
                                                if( !empty($row->team_2_image) ){
                                                    echo '<img src="'.$row->team_2_image.'" style="width: 32px;">';
                                                }
                                                else{
                                                    echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                                }
                                                
                                            echo anchor('admin/team_crickets/edit/' . $row->team_2_id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Team', 'class' => 'btn btn-primary btn-xs m-l-5 '));

                                                $files2 = null;
                                                if($row->team_2_file_name){
                                                    $files2 = array_unique ( explode(",", $row->team_2_file_name) );
                                                    $files2 = implode(",", $files2);
                                                }
                                            $team2Array = ["id"=>$row->team_2_id, "name"=>$row->team_2_name, "sort_name"=> $row->team_2_sort_name, "unique_id"=> null,  "logo"=> $row->team_2_image,  "status"=>"A", "is_deleted"=> $row->match_date, "created_at"=>  $row->match_date,  "created_by"=> $this->session->userdata('adminId'),  "updated_at"=> time(),  "updated_by"=> $this->session->userdata('adminId'),  "firstname"=> null,  "file_name"=> $files2];
                                            ?>
                                            <button class="btn btn-primary btn-xs" type="button" title="Default Team Jersey">
                                            <i class="fa fa-file-image-o image_change"  data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($team2Array,JSON_HEX_APOS);?>'></i></button>

                                        </td>
                                        <td data-title="Match Date">
                                            <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->match_date); ?>
                                        </td>
                                        <td data-title="Close Date">
                                            <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->close_date); ?>
                                        </td>
                                        
                                        <td data-title="Created">
                                            <?php echo $row->created_at ? date(DATE_TIME_FORMAT_ADMIN, $row->created_at) : "N/A"; ?></td>
                                        <td data-title="Action">
                                            <?php
                                            echo anchor($prefixUrl.'add_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-trophy"></i>', array('title' => 'Add Contests', 'class' => 'btn btn-primary btn-xs m-l-5 m-b-5'));
                                            
                                            // echo anchor($prefixUrl.'beat_the_expert_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-heartbeat"></i>', array('title' => 'Beat The Expert View', 'class' => 'btn btn-primary btn-xs m-l-5 m-b-5'));

                                            // echo anchor($prefixUrl.'private_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-user-secret"></i>', array('title' => 'Private Contests View', 'class' => 'btn btn-primary btn-xs m-l-5 m-b-5'));

											if ($row->status=="D")
												echo anchor("{$prefixUrl}activate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list m-l-5 m-b-5"');
                                            else
                                                echo anchor("{$prefixUrl}deactivate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list m-l-5 m-b-5"');
                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Match', 'class' => 'btn btn-primary btn-xs m-l-5 m-b-5'));
                                            echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list m-l-5 m-b-5'));
                                            ?>
                                        </td>	
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </section>
                </div>
            </section>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <section class="panel">
                <div class="panel-body border-bottom">
                    <?php
                    $start = 1;
                    if ($per_page >= $total_rows) {
                        $till_record = $total_rows;
                    } else {
                        $till_record = $this->uri->segment(4);
                        if ($till_record == '') {
                            $till_record = $per_page;
                        } else {
                            $till_record = $per_page + $this->uri->segment(4);
                            if ($till_record > $total_rows) {
                                $till_record = $total_rows;
                            }
                            $start = $this->uri->segment(4) + 1;
                        }
                    }
                    ?>
                    Number of <?=$names?> <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

                    <div class="dataTables_paginate paging_bootstrap pagination">
                        <ul>
                            <?php echo $this->jquery_pagination->create_links(); ?>  
                        </ul>                      
                    </div>
                </div>
                <div class="panel-body">
                    <button type="button" onclick="return checkedAll()"  class="btn btn-success">Select All</button>
                    <button type="button" onclick="return uncheckedAll()" class="btn btn-success">Unselect All</button>
                    <?php 
					$arr = array(
                        "" => "Action for selected...",
						'Activate' => "Activate",
                        'Deactivate' => "Deactivate",
                        'Delete' => "Delete",
                    );
                    echo form_dropdown("action", $arr, '', "class='small form-control' id='table-action'");
                    ?>
                    <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
                    <button type="submit" class="small btn btn-success btn-cons" id="submit_action">Ok</button>
                </div>
            </section>
        </div>
    </div>
    <?php echo form_close(); ?>

<?php } else {
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    <?=$names?> List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?=$names?> added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

