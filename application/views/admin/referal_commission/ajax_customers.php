<style>
td.customer-M a {
    margin: 4px;
}
</style>
<?php if ($records) { 
    echo form_open('admin/customers/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <?php if($export == "no"){ ?>
                <header class="panel-heading">
                    Customers List
                </header>
            <?php } ?>
                <div class="panel-body">
                    <section id="no-more-tables"  class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.id">Unique Id</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.referral_code", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.referral_code">Referral Code</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.firstname">First Name</th>
									<th class="<?php echo $this->main_model->getsortclass("customers.lastname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="customers.lastname">Last Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("customers.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.email">Email ID</th>                                    
                                    <th class="<?php echo $this->main_model->getsortclass("used_referral_count", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="used_referral_count">Total Referred</th>
									<th class="<?php echo $this->main_model->getsortclass("total_earnings", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="total_earnings">Total Earnings</th>                                   
                                    <th class="<?php echo $this->main_model->getsortclass("customers.app_status_updated_at", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="customers.app_status_updated_at">App Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($records as $row) { 
                                    ?> 
                                    <tr>
                                        <td data-title="Unique Id">
                                           <?php echo ($row->id); ?>
                                        </td>
                                        <td data-title="Referral Code">
                                            <?php echo ($row->referral_code); ?>
                                        </td>
                                        <td data-title="First Name">
                                            <?php echo ($row->firstname); ?>
                                        </td>
										<td data-title="Last Name">
                                            <?php echo ($row->lastname); ?>
                                        </td>
                                        <td data-title="Email Id"> 
                                            <?php echo $row->email;?>
                                        </td>
                                        <td data-title="Total Referred">
                                            <?php 
                                            
                                            if($row->used_referral_count > 0){
                                                if($export == "no"){
                                                    echo anchor($prefixUrl."referrals/" . $row->id . "?return=" . $current_url, $row->used_referral_count.' <i class="fa fa-eye "></i>', array('title' => 'View Referrals', 'class' => 'btn btn-success btn-xs'));
                                                }else{ echo $row->used_referral_count; }
                                            }else{
                                                echo ($row->used_referral_count);
                                            }

                                             ?>
                                        </td>
                                        <td data-title="Total Earnings">
                                            <?php 
                                            
                                            if($row->total_earnings > 0){
                                                if($export == "no"){
                                                    echo anchor($prefixUrl."reports/" . $row->id . "?return=" . $current_url, number_format($row->total_earnings, 2).' <i class="fa fa-eye "></i>', array('title' => 'View Detailed Report', 'class' => 'btn btn-success btn-xs'));
                                                }else{ echo number_format($row->total_earnings, 2); }
                                            }else{
                                                echo (number_format($row->total_earnings, 2));
                                            }

                                             ?>
                                        </td>
                                        <td data-title="App Status"> 
                                             <?php 
                                                $app_status_updated_at = $row->app_status_updated_at;
                                                $dif = time() - $app_status_updated_at;

                                            ?>
                                           <span style="color: <?php echo (($dif <= 86400)?"green":"red");?>">
                                                <?php echo (($dif <= 86400)?"Active":"Uninstalled")."<br>";
                                                
                                                ?>
                                           </span>
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
    <?php if($export == "no"){ ?>
    <div class="row">
        <div class="col-lg-12">
            <section class="panel">
                <div class="panel-body border-bottom">
                    <?php
                        $segment = 4;
                        if( $this->uri->segment(3) == "referrals" ){
                            $segment = 5;
                        }
                    $start = 1;
                    if ($per_page >= $total_rows) {
                        $till_record = $total_rows;
                    } else {
                        $till_record = $this->uri->segment($segment);
                        if ($till_record == '') {
                            $till_record = $per_page;
                        } else {
                            $till_record = $per_page + $this->uri->segment($segment);
                            if ($till_record > $total_rows) {
                                $till_record = $total_rows;
                            }
                            $start = $this->uri->segment($segment) + 1;
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
                    <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
            </section>
        </div>
    </div>
    <?php } ?>
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