<?php if ($records) { 
    echo form_open('admin/customers/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Customers List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/admins/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Admins</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables"  class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th></th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.firstname">First Name</th>
									<th class="<?php echo $this->main_model->getsortclass("customers.lastname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.lastname">Last Name</th>
									<th class="<?php echo $this->main_model->getsortclass("customers.team_name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.team_name">Team Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.email">Email Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.phone", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.phone">Phone</th>
									<th class="<?php echo $this->main_model->getsortclass("customers.addressline1", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.addressline1">Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.created">Created</th>
                                    <th style="width:140px;">Action</th>
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
                                        <td data-title="First Name">
                                            <?php echo ucfirst($row->firstname); ?>
                                        </td>
										<td data-title="Last Name">
                                            <?php echo ucfirst($row->lastname); ?>
                                        </td>
										<td data-title="Team Name">
                                            <?php echo ucfirst($row->team_name); ?>
                                        </td>
                                        <td data-title="Email Address"> 
                                            <?php
                                            echo $row->email;
                                            ?>
                                        </td>
                                        <td data-title="Phone">
                                            <?php
                                            echo $row->country_mobile_code ? $row->country_mobile_code.'-': "N/A";
                                            echo $row->phone ? $row->phone : "N/A";
                                            ?>
                                        </td>
										<td data-title="Address">
                                            <?php
                                            echo $row->addressline1 ? $row->addressline1.',': "";
                                            echo $row->addressline2 ? $row->addressline2.',': "";
                                            echo $row->city ? $row->city.'<br>': "";
                                            echo $row->stateName ? $row->stateName.',': "";
                                            echo $row->countryName ? $row->countryName.'<br>': "";
                                            echo $row->pincode ? $row->pincode : "";
                                            ?>
                                        </td>
                                        <td data-title="Created">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, ($row->created)) : "N/A"; ?></td>
                                        <td data-title="Action">
                                            <?php                                          
                                            echo anchor("admin/customers/view_documet/" . $row->id, '<i class="fa fa-file"></i>', array('title' => 'View Documents', 'class' => 'btn btn-primary btn-xs view-document','data-id'=>$row->id ));
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
                    Number of Customers <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    Customers List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Customers added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php } ?>



  <!-- Modal -->
  <div class="modal fade" id="viewModalForm" role="dialog">
    <div class="modal-dialog modal-lg">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Customers Documents</h4>
        </div>
        <div class="modal-body" style="height: 80vh;overflow-y: scroll;scroll-behavior: smooth;">

		<div class="row our_rec_fetch">	

		</div>
		<div class="row ">
		<div class="col-xs-12 col-sm-12 col-md-12 text-center">
		 <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancel</button>
		</div>
		</div>
	
	</div>
	</div>
      
    </div>
  </div>