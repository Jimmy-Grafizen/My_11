<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Restaurant List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>company/customers/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Restaurant</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.restaurant_name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_register_restaurant.restaurant_name">Restaurant Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.restaurant_address", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_register_restaurant.restaurant_address">Restaurant Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tbl_register_restaurant.firstname">Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_register_restaurant.email">Email Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.city", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_register_restaurant.city">City</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.phone", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_register_restaurant.phone">Mobile</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tbl_register_restaurant.created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tbl_register_restaurant.created">Created</th>
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
                                            echo form_checkbox(array('name' => 'check[]', 'value' => '' . $row->id, 'class' => 'className'));
                                            ?>
                                        </td>
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->restaurant_name)  ?>
                                        </td>
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->restaurant_address); ?>
                                        </td>
                                        <td data-title="Name">
                                            <?php echo ucfirst($row->firstname) ." ".  $row->lastname; ?>
                                        </td>
                                        <td data-title="Email Address">
                                            <?php
                                            echo $row->email;
                                            ?>
                                        </td>
                                        <td data-title="city Address">
                                            <?php
                                            echo $row->city;
                                            ?>
                                        </td>
                                        <td data-title="Mobile">
                                            <?php
                                            echo $row->phone ? $row->country_mobile_code .$row->phone : "N/A";
                                            ?>
                                        </td>
                                        <td data-title="Created">
                                            <?php echo $row->created ? date("d M, Y h:i A", ($row->created)) : "N/A"; ?>
                                                
                                        </td>
                                        <td>
                                            <?php
                                                if(CheckPermission("/admin/enquiry/delete", "Enquiry Management")){
                                                   echo anchor('/admin/enquiry/delete/'.$row->id.'?act='.$act.'&return='.$current_url, '<i class="fa fa-trash-o"></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
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
                    Number of Restaurant <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                        'Delete' => "Delete",
                    );
                    echo form_dropdown("action", $arr, '', "class='small form-control' id='table-action'");
                    ?>
                    <button type="submit" class="small btn btn-success btn-cons" id="submit_action">Ok</button>
                </div>
                 
                    <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
                    <input type="hidden" name ="act" id="act" value="<?php echo $act; ?>" />
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
                    Restaurant List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Restaurant added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

