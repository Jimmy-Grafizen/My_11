<?php if ($records) { ?>

    <?php
    echo form_open('admin/admins/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Admins List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/admins/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Admins</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_users.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_users.firstname">Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_users.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_users.email">Email Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_users.mobile", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_users.mobile">Mobile</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_users.created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_users.created">Created</th>
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
                                                'value' => $row->slug,
                                            );
                                            echo form_checkbox(array('name' => 'check[]', 'value' => '' . $row->slug, 'class' => 'className'));
                                            ?>
                                        </td>
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->firstname) ." ".  $row->lastname; ?>
                                        </td>
                                        <td data-title="Email Address">
                                            <?php
                                            echo $row->email;
                                            ?>
                                        </td>
                                        <td data-title="Mobile">
                                            <?php
                                            echo $row->mobile ? $row->mobile : "N/A";
                                            ?>
                                        </td>
                                        <td data-title="Created">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, ($row->created)) : "N/A"; ?></td>
                                        <td data-title="Action">
                                            <?php
                                            if ($row->status=="D")
                                                echo anchor("admin/admins/activate/" . $row->slug . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list"');
                                            else
                                                echo anchor("admin/admins/deactivate/" . $row->slug . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list action-list"');
                                            echo anchor('/admin/admins/edit/' . $row->slug . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Content', 'class' => 'btn btn-primary btn-xs '));
                                            echo anchor("admin/admins/delete/" . $row->slug . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
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
                    Number of Admins <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    Admins List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Admins added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

