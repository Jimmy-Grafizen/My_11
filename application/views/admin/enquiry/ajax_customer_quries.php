<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Customer Query List
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
                                    <th class="<?php echo $this->main_model->getsortclass("ticket_id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="ticket_id">Ticket Id</th>
                                    <th class="<?php echo $this->main_model->getsortclass("subject", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="subject">Subject</th>
                                    <th class="<?php echo $this->main_model->getsortclass("message", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="message">Message</th>
                                    <th class="<?php echo $this->main_model->getsortclass("response_message", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="response_message">Last Message</th>
                                    <th class="<?php echo $this->main_model->getsortclass("response_time", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="response_time">Last Message Time</th>
                                    <th>Customer Details</th>                        
                                    <th class="<?php echo $this->main_model->getsortclass("created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="created">Created</th>
                                    <th class="<?php echo $this->main_model->getsortclass(".status", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="status">Current Status</th>
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
                                        <td data-title="Ticket Id">
                                            <?php echo ucfirst($row->ticket_id)  ?>
                                        </td>
                                        <td data-title="Subject">
                                            <?php echo ucfirst($row->subject); ?>
                                        </td>
                                        <td data-title="message">
                                            <?php echo ucfirst($row->message); ?>
                                        </td>
                                        <td data-title="Last Message">
                                            <?php echo ucfirst($row->response_message); ?>
                                        </td>

                                        <td data-title="Last Message Time">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, $row->created) : "N/A"; ?>
                                                
                                        </td>
                                        <td data-title="Customer Details">
                                            <?php echo ucfirst($row->firstname) ." ".  $row->lastname; ?>
                                        
                                        <br>
                                            <?php
                                            echo $row->email;
                                            ?>
                                        <br>
                                            <?php
                                            echo $row->phone ? $row->country_mobile_code .$row->phone : "N/A";
                                            ?>
                                        </td>
                                        <td data-title="Created">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, $row->created) : "N/A"; ?>
                                                
                                        </td>
                                        <td data-title="Current Status">
                                            <?php 
                                                $statusArray = array( "P"=>"Pending", "IP"=>"InProgress", "R"=>"Resolved" ); 
                                                echo $statusArray[$row->status];  
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                               // if(CheckPermission("/admin/enquiry/delete", "Enquiry Management")){
                                                   echo anchor('/admin/enquiry/delete/'.$row->id.'?act='.$act.'&return='.$current_url, '<i class="fa fa-trash-o"></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
                                               // }
                                                echo form_dropdown('statuschnage', $statusArray, $row->status, 'class="form-control statuschnage" id="'.$row->id.'"');
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
                    Number of Customer Query <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    Customer Query List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Customer Query added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

