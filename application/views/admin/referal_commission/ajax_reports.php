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
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/admins/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export Admins</button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables"  class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th class="<?php echo $this->main_model->getsortclass("tc.id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tc.id">Unique Id</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tc.firstname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tc.firstname">First Name</th>
									<th class="<?php echo $this->main_model->getsortclass("tc.lastname", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="tc.lastname">Last Name</th>
                                    <th class="<?php echo $this->main_model->getsortclass("tc.email", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="tc.email">Email ID</th>                                    
                                    <th class="<?php echo $this->main_model->getsortclass("stateName", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="stateName">State</th>
                                    <th class="<?php echo $this->main_model->getsortclass("sports_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="sports_type">Sports Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("game_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="game_type">Game Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("contest_category", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="contest_category">Contest Category</th>
                                    <th class="<?php echo $this->main_model->getsortclass("match_name", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="match_name">Match</th>
                                    <th class="<?php echo $this->main_model->getsortclass("match_date", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="match_date">Match Date</th>

									<th class="<?php echo $this->main_model->getsortclass("entry_fees", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="entry_fees">Entry Fee</th>           
                                    <th class="<?php echo $this->main_model->getsortclass("commission", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="commission">% Commission</th>      
                                    <th class="<?php echo $this->main_model->getsortclass("amount", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="amount">Commission Amount</th>
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
                                        <td data-title="First Name">
                                            <?php echo ($row->firstname); ?>
                                        </td>
										<td data-title="Last Name">
                                            <?php echo ($row->lastname); ?>
                                        </td>
                                        <td data-title="Email Id"> 
                                            <?php echo $row->email;?>
                                        </td>
                                        <td data-title="State"> 
                                            <?php echo $row->stateName;?>
                                        </td>
                                        <td data-title="Sports Type"> 
                                            <?php echo $row->sports_type;?>
                                        </td>
                                        <td data-title="Game Type"> 
                                            <?php echo $row->game_type;?>
                                        </td>
                                        <td data-title="Contest Category"> 
                                            <?php echo $row->contest_category;?>
                                        </td>
                                        <td data-title="Match"> 
                                            <?php echo $row->match_name;?>
                                        </td>
                                        <td data-title="Match Date"> 
                                        <?php echo $row->match_date ? date(DATE_TIME_FORMAT_ADMIN, $row->match_date) : "N/A"; ?>
                                        </td>
                                        <td data-title="Entry Fee"> 
                                           <?php echo $row->entry_fees;?>
                                        </td>
                                        <td data-title="% Commission"> 
                                           <?php echo $row->commission;?>
                                        </td>
                                        <td data-title="Commission Amount"> 
                                           <?php echo $row->amount;?>
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
                    $start = 1;
                    if ($per_page >= $total_rows) {
                        $till_record = $total_rows;
                    } else {
                        $till_record = $this->uri->segment(5);
                        if ($till_record == '') {
                            $till_record = $per_page;
                        } else {
                            $till_record = $per_page + $this->uri->segment(5);
                            if ($till_record > $total_rows) {
                                $till_record = $total_rows;
                            }
                            $start = $this->uri->segment(5) + 1;
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