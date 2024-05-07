<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    <?=$names ?> List
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
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.name">Name</th>
									<th class="<?php echo $this->main_model->getsortclass("{$table}.description", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.name">Description</th>
									<th class="<?php echo $this->main_model->getsortclass("{$table}.image", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.image">Image</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.cash_bonus_used_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.cash_bonus_used_type">Cash Bonus Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.cash_bonus_used_value", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.cash_bonus_used_value">Cash Bonus Used</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.confirm_win", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.confirm_win">Confirm Win </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.is_discounted", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.is_discounted">Is Discounted </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.confirm_win_contest_percentage", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.confirm_win_contest_percentage">Confirm win contest percentages</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.is_compression_allow", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.is_compression_allow">Is Compression Allow </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.is_duplicate_allow", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.is_duplicate_allow">Is Duplicate Allow </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.duplicate_count", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.duplicate_count">Duplicate Count </th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.created_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.created_at">Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="row_position">
                                <?php
                                foreach ($records as $row) {
                                    ?> 
                                    <tr  id="<?php echo $row->id; ?>">
                                        <td data-title="Select" style="cursor: move;font-size: 30px;text-align: center;">
										<i class="fa fa-arrows" aria-hidden="true"></i>
                                            <?php
                                            $data = array(
                                                'name' => "check[]",
                                                'id' => "table-selected-1",
                                                'value' => $row->id,
                                            );
                                            echo form_checkbox(array('name' => 'check[]', 'value' => '' . $row->id, 'class' => 'className'));
                                            ?>
                                        </td>
                                        <td data-title="Name">
                                            <?php echo $row->name; ?>
                                        </td>
										<td data-title="Description">
                                            <?php echo $row->description; ?>
                                        </td>
                                        
                                        <td data-title="Image">
									
											<?php 
												if( !empty($row->image) )
													echo '<img src="'.CONTEXTCATEGORY_IMAGE_THUMB_URL.$row->image.'">';
												else
													echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
											?>
										
                                        </td>
                                        
                                        <td data-title="Cash Bonus Type">
                                            <?php echo unserialize(CONTEST_CASH_BONUS_USED_TYPE)[$row->cash_bonus_used_type]; ?>
                                        </td>

                                        <td data-title="Cash Bonus Used">
                                            <?php echo $row->cash_bonus_used_value; ?>
                                        </td>

                                        <td data-title="Confirm win ">
                                            <?php echo ($row->confirm_win =="Y")? "YES": "NO"; ?>
                                        </td>

                                        <td data-title="Is Discounted">
                                            <?php echo ($row->is_discounted =="Y")? "YES": "NO"; ?>
                                        </td>

                                        <td data-title="Confirm win contest percentages">
                                            <?php echo $row->confirm_win_contest_percentage; ?>
                                        </td>
                                        <td data-title="Is Compression Allow">
                                            <?php echo ($row->is_compression_allow =="Y")? "YES": "NO"; ?>
                                        </td>
                                        <td data-title="Is Duplicate Allow">
                                            <?php echo ($row->is_duplicate_allow =="Y")? "YES": "NO"; ?>
                                        </td>

                                        <td data-title="Duplicate Count">
                                            <?php echo $row->duplicate_count; ?>
                                        </td>

                                        <td data-title="Created">
                                            <?php echo $row->created_at ? date(DATE_TIME_FORMAT_ADMIN, $row->created_at) : "N/A"; ?>
                                        </td>

                                        <td data-title="Action">
                                            <?php
                                            if($row->is_private == "N" && $row->is_beat_the_expert =="N"){
                                                if ($row->status=="D"){
    												echo anchor("{$prefixUrl}activate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list"');
                                                }
                                                else{
                                                    echo anchor("{$prefixUrl}deactivate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list action-list"');
                                                }
                                                echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
                                                }

                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Content', 'class' => 'btn btn-primary btn-xs '));
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
                    Number of <?=$names ?> <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    <?=$names ?> List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?=$names ?> added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">

    $( ".row_position" ).sortable({

        delay: 150,

        stop: function() {

            var selectedData = new Array();

            $('.row_position>tr').each(function() {

                selectedData.push($(this).attr("id"));

            });

            updateOrder(selectedData);

        }

    });


    function updateOrder(data) {

        $.ajax({

            url:"<?php echo base_url($prefixUrl."position_order")?>",

            type:'post',

            data:{position:data},

            success:function(data){

                console.log('your change successfully saved');

            }

        })

    }

</script>