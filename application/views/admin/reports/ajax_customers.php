<?php if ($records) { 
    echo form_open('admin/customers/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Customers List 
                <div class="pull-right">
                    <button type="button" onclick="return checkedAll()"  class="btn btn-success">Select All</button>
                    <!--button type="button" onclick="return uncheckedAll()" class="btn btn-success">Unselect All</button-->
                    <button type="button" onclick="return"  class="btn btn-success snedmailnotfi" hrerdata="email_notifications">Send Email</button>
                    <button type="button" onclick="return" class="btn btn-success snedmailnotfi" hrerdata="notifications">Send Notification</button>
                </div>
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
                                    <th class="<?php echo $this->main_model->getsortclass("customers.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.email">Email/Phone</th>
                                    <th class="<?php echo $this->main_model->getsortclass("played_series_counts", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="played_series_counts">Played series</th>
									<th class="<?php echo $this->main_model->getsortclass("played_match_counts", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="played_match_counts">Played Match</th>
									<th class="<?php echo $this->main_model->getsortclass("customer_contests", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customer_contests">Played Contest</th>
									<th class="<?php echo $this->main_model->getsortclass("spendamount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="spendamount">Credited Amount</th>
									<th class="<?php echo $this->main_model->getsortclass("winamount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="winamount">Debited Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("refund_amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="refund_amount">Refund Amount</th>
									<th class="<?php echo $this->main_model->getsortclass("earnings", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="earnings">Earnings</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tax_amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tax_amount">Tax Amount </th>
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
                                            echo $row->email."<br />";
											
                                            echo $row->country_mobile_code ? $row->country_mobile_code.'-': "N/A";
                                            echo $row->phone ? $row->phone : "N/A";
                                            
                                            ?>
                                        </td>
										<td data-title="Played Series"> 
                                            <?php
												echo $row->played_series_counts; 
											?>
                                        </td>
										<td data-title="Played Match">
										<?php
											echo $row->played_match_counts; 
										?>
                                        </td>
                                        <td data-title="Customers total contests"> 
                                            <?php
                                            echo $row->customer_contests;
                                            ?>
                                        </td>
                                        <td data-title="Credited Amount"> 
                                            <?php											
                                                echo number_format( $row->spendamount, 2 );
                                            ?>
                                        </td>
                                        <td data-title="Debited Amount"> 
                                            <?php
                                                echo number_format( $row->winamount, 2);
                                            ?>
										</td>
                                        <td data-title="Refund Amount"> 
                                            <?php
                                                echo number_format( $row->refund_amount, 2);
                                            ?>
                                        </td>
                                        <td data-title="Earnings"> 
                                            <?php
                                                echo number_format( $row->earnings, 2);
                                            ?>
										</td>
                                        <td data-title="Tax Amount"> 
                                            <?php
                                                echo number_format($row->tax_amount, 2);
                                            ?>
                                        </td>
										<td data-title="Action">
                                        <?php
                                            echo anchor("admin/reports/customers_set/" . $row->id, '<i class="fa fa-eye "></i>', array('title' => 'View Matches', 'class' => 'btn btn-primary btn-xs teamsclsviews'));
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
                     <!--div class="panel-body">
                    <button type="button" onclick="return checkedAll()"  class="btn btn-success">Select All</button>
                    <button type="button" onclick="return uncheckedAll()" class="btn btn-success">Unselect All</button>
                    <?php
                    $arr = array(
                        "" => "Action for selected...",
                        'Activate' => "Activate",
                        'Deactivate' => "Deactivate",
                    );
                    //echo form_dropdown("action", $arr, '', "class='small form-control' id='table-action'");
                    ?>
                    <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
                    <button type="submit" class="small btn btn-success btn-cons" id="submit_action">Ok</button>
                </div--->
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