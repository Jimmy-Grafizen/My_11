<?php  if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Email Templates List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/groups/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Cities</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
                                   	
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.title", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.title">Title</th>
									 <th class="<?php echo $this->main_model->getsortclass("{$table}.type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.type">Template Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.content", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.content">Content</th>
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
                                        <td data-title="Template Title">
                                            <?php echo ucfirst($row->title); ?>
                                        </td>
										<td data-title="Template type">
                                            <?php echo $row->type=="S"?"SMS":"Email"; ?>
                                        </td>
                                        <td data-title="Template Content">
                                            <?php echo ucfirst($row->content); ?>
                                        </td>
										
                                        
                                        <td data-title="Created">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, $row->created) : "N/A"; ?></td>
                                        <td data-title="Action">
                                            <?php
											//if ($row->status=="D")
											//	echo anchor("{$prefixUrl}activate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list"');
                                           // else
                                             //   echo anchor("{$prefixUrl}deactivate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list action-list"');
                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Content', 'class' => 'btn btn-primary btn-xs '));
											if ($row->is_default=="N"){
                                            echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));}
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
                    Number of Templates <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                        //'Delete' => "Delete",
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
                    Email Templates List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Email Templates added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

