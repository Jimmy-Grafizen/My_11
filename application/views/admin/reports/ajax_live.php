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
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.name">Match Name</th>	
									<th class="<?php echo $this->main_model->getsortclass("series.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="series.name">Series Name</th>
                                    <!--th class="<?php echo $this->main_model->getsortclass("game.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="game.name">Game name</th-->
                                    <th class="<?php echo $this->main_model->getsortclass("game_type.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="game_type.name">Game type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_teams1.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams1.name">Team-1</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_teams2.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams2.name">Team-2</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.match_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.match_date">Match date</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.close_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.close_date">Close date</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.created_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.created_at">Created</th>
                                    <th  style="width:80px;">Action</th>
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
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->name); ?>
                                        </td>
                                        <td data-title="Series Name">
                                            <?php echo ucfirst($row->series_name); ?>
                                        </td>
                                        <!--td data-title="Game Name">
                                            <?php echo ucfirst($row->game_name); ?>
                                        </td-->
                                        <td data-title="Game type Name">
                                            <?php echo ucfirst($row->game_type_name); ?>
                                        </td>
                                        <td data-title="Team-1">
                                            <?php echo ucfirst($row->team_1_name); ?>
                                        </td>
                                        <td data-title="Team-2">
                                            <?php echo ucfirst($row->team_2_name); ?>
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
                                            echo anchor($prefixUrl.'live_contest_match/' . $row->id . "?return=" . $current_url, '<i class="fa fa-trophy"></i>', array('title' => 'View Contests', 'class' => 'btn btn-primary btn-xs '));
											echo anchor($prefixUrl.'view_live/' . $row->id . "?return=" . $current_url, '<i class="fa fa-eye"></i>', array('title' => 'View Content', 'class' => 'btn btn-primary btn-xs ','style' => 'margin-left: 5px;'));
                                            //echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list','style' => 'margin-left: 5px;'));
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

