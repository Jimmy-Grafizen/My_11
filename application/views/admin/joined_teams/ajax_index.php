<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    <?=$name ?> List
                </header>
                <div class="panel-body">
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th class="<?php echo $this->main_model->getsortclass("team_name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="team_name">Team Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.firstname">Customer Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("entry_fees", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="entry_fees">Entry Fee</th>
                                    <th class="<?php echo $this->main_model->getsortclass("new_rank", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="new_rank">Rank</th>
                                    <th class="<?php echo $this->main_model->getsortclass("new_points", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="new_points">Point</th>
                                    <th class="<?php echo $this->main_model->getsortclass("win_amount", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="win_amount">Win Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("refund_amount", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="refund_amount">Refund Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tax_amount", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tax_amount">Tax Amount</th>
                                   <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($records as $row) {
                                    ?> 
                                    <tr>
                                        <td data-title="Name team_name">
                                            <?php echo ucfirst($row->customer_team_name)."(".$row->team_name.")"; 

                                            echo (!empty($row->team_more_name))?"[".$row->customer_team_name_in_tcct."(".$row->team_more_name.")]":'';
                                            ?>

                                        </td>
                                        
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->firstname)." ".ucfirst($row->lastname); ?>
                                        </td>
                                        <td data-title="Entry Fee">
                                            <?php echo ($row->entry_fees)?($row->entry_fees):"-"; ?>
                                        </td>
                                        <td data-title="new_rank">
                                            <?php echo ($row->new_rank)?($row->new_rank):"-"; ?>
                                        </td>
                                        <td data-title="new_points">
                                            <?php echo ($row->new_points)?($row->new_points):"0"; ?>
                                        </td>
                                        <td data-title="win_amount">
                                           &#8377; <?php echo ($row->win_amount)?($row->win_amount):"0"; ?>
                                        </td>
                                        <td data-title="Refund amount">
                                           &#8377; <?php echo ($row->refund_amount)?($row->refund_amount):"0"; ?>
                                        </td>
                                        <td data-title="tax amount">
                                           &#8377; <?php echo ($row->tax_amount)?($row->tax_amount):"0"; ?>
                                        </td>
                                        
                                         <td data-title="Action">
										   <button type="button" title='View Team Player' class="btn btn-info btn-sm views_player-liset" data-toggle="modal" data-target="#myModal" modaldata='<?=json_encode($row);?>'><i class="fa fa-eye" ></i></button>

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
                    Number of <?=$names ?> <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

                    <div class="dataTables_paginate paging_bootstrap pagination">
                        <ul>
                            <?php echo $this->jquery_pagination->create_links(); ?>  
                        </ul>                      
                    </div>
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
                    <?=$names ?> List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?=$name ?> added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>