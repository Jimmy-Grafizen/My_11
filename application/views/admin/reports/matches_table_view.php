 <section id="no-more-tables" class="table responsive">
    <table class="table table-bordered table-striped table-condensed cf">
        <thead class="cf">
            <tr>
                <th class="<?php echo $this->main_model->getsortclass("{$table}.unique_id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.unique_id">Unique id</th>		
                <th class="<?php echo $this->main_model->getsortclass("{$table}.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.name">Match Name</th>	
				<th class="<?php echo $this->main_model->getsortclass("series.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="series.name">Series Name</th>
                <th class="<?php echo $this->main_model->getsortclass("game_type.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="game_type.name">Game type</th>
                <th class="<?php echo $this->main_model->getsortclass("tbl_teams1.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams1.name">Team-1</th>
                <th class="<?php echo $this->main_model->getsortclass("tbl_teams2.name", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_teams2.name">Team-2</th> 
				<th class="<?php echo $this->main_model->getsortclass("contest_count", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="contest_count">Contest counts</th>
				<th class="<?php echo $this->main_model->getsortclass("joined_total_teams", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="joined_total_teams">Joined Teams</th>
				<th class="<?php echo $this->main_model->getsortclass("spendamount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="spendamount">Credited Amount</th>
				<th class="<?php echo $this->main_model->getsortclass("winamount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="winamount">Debited Amount</th>
                <th class="<?php echo $this->main_model->getsortclass("refund_amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="refund_amount">Refund Amount</th>
				<th class="<?php echo $this->main_model->getsortclass("earnings", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="earnings">Earnings</th>
                <th class="<?php echo $this->main_model->getsortclass("tax_amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tax_amount">Tax Amount</th>
                <th class="<?php echo $this->main_model->getsortclass("{$table}.match_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.match_date">Match date</th>
                <th class="<?php echo $this->main_model->getsortclass("{$table}.close_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.close_date">Close date</th>      
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
                    <td data-title="Name">
                        <?php echo ucfirst($row->name); ?>
                    </td>
                    <td data-title="Series Name">
                        <?php echo ucfirst($row->series_name); ?>
                    </td>
                    <td data-title="Game type Name">
                        <?php echo ucfirst($row->game_type_name); ?>
                    </td>
                    <td data-title="Team-1">
                        <?php echo ucfirst($row->team_1_name); ?>
                    </td>
                    <td data-title="Team-2">
                        <?php echo ucfirst($row->team_2_name); ?>
                    </td>
                    <td data-title="Contest total Teams">
                        <?php echo ucfirst($row->contest_count); ?>
                    </td>
                    <td data-title="Joined total Teams">
                        <?php echo ucfirst($row->joined_total_teams); ?>
                    </td>
                    <td data-title="Credited Amount"> 
                        <?php echo number_format($row->spendamount,2); ?>
                    </td>
                    <td data-title="Debited Amount"> 
                        <?php echo number_format($row->winamount,2); ?>
					</td>
                    <td data-title="Refund Amount"> 
                        <?php echo number_format($row->refund_amount,2); ?>
                    </td>
                    
                    <td data-title="Earnings"> 
                        <?php echo number_format($row->earnings,2);?>
                    </td>
                    <td data-title="Tax Amount"> 
                        <?php echo number_format($row->tax_amount,2); ?>
                    </td>
                    <td data-title="Match Date">
                        <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->match_date); ?>
                    </td>
                    <td data-title="Close Date">
                        <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->close_date); ?>
                    </td>
                    <td data-title="Action">
                        <?php
                        
                        if( $this->uri->segment(3) =="customers_matches" || $this->uri->segment(3) =="customer_contest_matches" ){
                        	$changeReport = "customer_contest_matches/";
                        }else{
                        	$changeReport ="contest_matches_completed/";
                        }

						echo anchor($prefixUrl.$changeReport . $row->id . "?return=" . $current_url, '<i class="fa fa-trophy"></i>', array('title' => 'View Contests', 'class' => 'btn btn-primary btn-xs ','style' => 'margin-left: 5px;'));
						echo anchor($prefixUrl.$changeReport . $row->id . "?v=private&return=" . $current_url, '<i class="fa fa-user-secret"></i>', array('title' => 'Private Contests View', 'class' => 'btn btn-primary btn-xs m-5'));
						echo anchor($prefixUrl.$changeReport . $row->id . "?v=beat_the_expert&return=" . $current_url, '<i class="fa fa-heartbeat"></i>', array('title' => 'Beat The Expert View', 'class' => 'btn btn-primary btn-xs m-5'));
                        echo anchor($prefixUrl.'view_completed/' . $row->id . "?return=" . $current_url, '<i class="fa fa-eye"></i>', array('title' => 'View Teams', 'class' => 'btn btn-primary btn-xs ','style' => 'margin-left: 5px;'));
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>