<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    <?php echo $names; ?> List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/groups/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Countries</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
									 <th class="<?php echo $this->main_model->getsortclass("{$table}.code", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="code">Code</th>
                                     <th class="<?php echo $this->main_model->getsortclass("{$table}.max_recharge", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="max_recharge">Max Recharge</th>
                                     <th class="<?php echo $this->main_model->getsortclass("{$table}.recharge", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="recharge">Min Recharge</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.rc_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="rc_type">Recharge Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.cash_bonus_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="cash_bonus_type">Cach Bonus Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.cach_bonus", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="cach_bonus">Cach Bonus</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.is_use", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.is_use">Use type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.is_use_max", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.is_use_max">Use Limit</th>
                                    <th class="<?php echo $this->main_model->getsortclass("total_used", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="total_used">Customer Use Count</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.start_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.start_date">Start Date </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.end_date", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.end_date">End Date</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.created_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.created_at">Created</th>
                                    <th>Action</th>
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
                                        <td data-title="Code">
                                            <?php echo $row->code; ?>
                                        </td>
                                        <td data-title="Max recharge">
                                            <?php echo $row->max_recharge; ?>
                                        </td>
                                        <td data-title="Min recharge">
                                            <?php echo $row->recharge; ?>
                                        </td>
                                        <td data-title="Recharge Type">
                                            <?php 
                                               echo ($row->rc_type == 0)?'Recharge':'Redeem';  
                                            ?>
                                        </td>
                                        <td data-title="Cach Bonus Type">
                                            <?php 
                                            $opt_all = unserialize(CASH_BONUS_TYPE);
                                                echo $opt_all[$row->cash_bonus_type]; 
                                            ?>
                                        </td>
                                        <td data-title="Cach bonus">
                                            <?php echo $row->cach_bonus; ?>
                                        </td>
                                        <td data-title="Use recharge">
                                             <?php                               
                                                $opt_all = unserialize(IS_USE_RECHARGE);
                                                echo $opt_all[$row->is_use]; 
                                            ?>
                                        </td>
                                        <td data-title="Use recharge Max">
                                            <?php echo $row->is_use_max; ?>
                                        </td>
                                        
                                        <td data-title="Customer Use Count">
                                            <?php echo $row->total_used; ?>
                                        </td>
                                        
                                        <td data-title="Start Date">
                                            <?php echo $row->start_date ? date(DATE_TIME_FORMAT_ADMIN, $row->start_date) : "N/A"; ?></td>
                                        <td data-title="End Date">
                                            <?php echo $row->end_date ? date(DATE_TIME_FORMAT_ADMIN, $row->end_date) : "N/A"; ?></td>
                                        <td data-title="Created">
                                            <?php echo $row->created_at ? date(DATE_TIME_FORMAT_ADMIN, $row->created_at) : "N/A"; ?></td>
                                        <td data-title="Action">
                                            <?php
											if ($row->status=="D")
												echo anchor("{$prefixUrl}activate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list"');
                                            else
                                                echo anchor("{$prefixUrl}deactivate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list action-list"');
                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Content', 'class' => 'btn btn-primary btn-xs '));
                                            echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
                                            ?>
                                        <?php  
                                            if($row->total_used >0 ){
                                                echo anchor("{$prefixUrl}customers/" . $row->id, '<i class="fa fa-eye"></i>', 'title="Customer View" class="btn btn-success btn-xs"');
                                            }
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
                    Number of <?php echo $names; ?> <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    <?php echo $names; ?> List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?php echo $names; ?> added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

