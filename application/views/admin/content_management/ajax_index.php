<?php 

    //print_r($records); die;
if ($records) { ?>

    <?php
    echo form_open('admin/content_management/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Content Management
                </header>
                <div class="panel-body">
                <!-- <a href="<?php //echo HTTP_PATH; ?>admin/categories/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Categories</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <!-- <th></th> -->
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_page_contents.page_name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_categories.page_name">Page Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_page_contents.title", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_categories.title">Title</th>
                                    <!-- <th class="<?php echo $this->main_model->getsortclass("tbl_page_contents.app_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_categories.app_type">App Type</th> -->
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_page_contents.platform", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_categories.platform">Platform</th>
                                    <!-- <th class="<?php //echo $this->main_model->getsortclass("tbl_categories.created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_categories.created">Created</th>
                                    <th>Order</th> -->
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $len = count($records);
                                foreach ($records as $row) {
                                    ?> 
                                    <tr>
                                        <td data-title="Page Name">
                                            <?php 
                                            
                                            echo $row->page_name; ?>
                                        </td>
                                        <td data-title="Title">
                                            <?php echo $row->title; ?>
                                        </td>
                                        <!-- <td data-title="App Type">
                                            <?php 
                                            $app_type = array(
                                                'C' => 'Customer',
                                                'D' => 'Delivery',
                                                'B' => 'Branch',
                                                'NA' => 'Not applicable for Web'
                                                );
                                            echo $app_type[$row->app_type]; ?>
                                        </td> -->
                                        <td data-title="Platform">
                                            <?php 
                                            $platform = array(
                                                'M' => 'Mobile',
                                                'W' => 'Web'
                                                );
                                            echo $platform[$row->platform]; ?>
                                        </td>
                                        
                                        <td data-title="Action">
                                            <?php
                                            
                                            echo anchor('/admin/content_management/edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Content', 'class' => 'btn btn-primary btn-xs '));
                                            ?>
                                        </td>	
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                ?>
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
                    Number of Pages <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

                    <div class="dataTables_paginate paging_bootstrap pagination">
                        <ul>
                            <?php echo $this->jquery_pagination->create_links(); ?>  
                        </ul>                      
                    </div>
                </div>
                <!-- <div class="panel-body">
                    <button type="button" onclick="return checkedAll()"  class="btn btn-success">Select All</button>
                    <button type="button" onclick="return uncheckedAll()" class="btn btn-success">Unselect All</button>
                    <?php
                    //$arr = array(
                        //"" => "Action for selected...",
                        //'Activate' => "Activate",
                       // 'Deactivate' => "Deactivate",
                       // 'Delete' => "Delete",
                   // );
                   // echo form_dropdown("action", $arr, '', "class='small form-control' id='table-action'");
                    ?>
                    <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
                    <button type="submit" class="small btn btn-success btn-cons" id="submit_action">Ok</button>
                </div> -->
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
                    Content Pages List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Pages added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }?>

