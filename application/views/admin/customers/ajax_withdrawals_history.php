<?php if ($records) { 
    echo form_open('admin/customers/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Withdrawals List
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/admins/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Admins</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables"  class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th>Transactions Id</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.firstname">First Name</th>
									<th class="<?php echo $this->main_model->getsortclass("customers.lastname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.lastname">Last Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.email">Email Address</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.phone", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.phone">Phone</th>
									<th class="<?php echo $this->main_model->getsortclass("amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="amount">Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("created_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="created_at">Created At</th>
                                    <th class="<?php echo $this->main_model->getsortclass("action_time", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="action_time">Rejected/Approved Date</th>
                                    <th style="width:140px;">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($records as $row) { 
                                    ?> 
                                    <tr>
                                        <td data-title="Select">
                                            <?php
                                            echo $row->transaction_id;
                                            ?>
                                        </td>
                                        <td data-title="First Name">
                                            <?php echo ucfirst($row->firstname); ?>
                                        </td>
										<td data-title="Last Name">
                                            <?php echo ucfirst($row->lastname); ?>
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
										<td data-title="Amount">
                                         <?php
                                            echo $row->amount;
                                            ?>
                                        </td>
                                        <td data-title="Created">
                                            <?php echo $row->created_at ? date(DATE_TIME_FORMAT_ADMIN, ($row->created_at)) : ""; ?>
                                        </td>
                                        <td data-title="Rejected/Approved Date">
                                            <?php echo $row->action_time ? date(DATE_TIME_FORMAT_ADMIN, ($row->action_time)) : ""; ?>
                                        </td>
                                        <td data-title="Action">
                                        
                                        <?php       
                                            if($row->status=="P"){
                                                    if($this->customer_model->IsDonebankdetailnPaincard($row->customer_id)){                                           
                                                    echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$row->id .'" type="withdrawals" status="C">Approve</a>';
                                                    echo "  Or ";
                                                    echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$row->id .'" type="withdrawals" status="R">Reject</a>';
                                                }else{
                                                    echo "Bank or pan card not approved";
                                                }
                                            }else if($row->status=="C"){
                                                echo '<b>Approved</b>';
                                               // echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$row->id .'" type="withdrawals" status="R">Reject</a>';
                                            }else if($row->status=="R"){
                                                //echo 'Rejected <br/>  <a href="javascript:;" title="'.ucfirst($row->reason).'"><i class="fa fa-comment"></i></a>';
                                                echo '<b>Rejected</b> : '. $row->reason;
                                              //  echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$row->id .'" type="withdrawals" status="A">Approve</a>';
                                            }else if($row->status=="X"){
                                                echo '<b>Expired</b>';
                                            }else{
                                                echo 'N/A';     
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
                    Number of Withdrawals <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

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
                    Withdrawals List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Withdrawals added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php } ?>