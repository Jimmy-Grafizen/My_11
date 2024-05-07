<?php if ($records) {


 ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    <?php if($export == 'no') { echo $names ?>  List <?php } ?>
                </header>
                <div class="panel-body">
                <!-- <a href="<?php echo HTTP_PATH; ?>admin/groups/export"  ><button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Export <?php echo $names ?> </button></a> -->
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.transaction_id", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.transaction_id">Transaction ID</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.transaction_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.transaction_type">Transaction Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.wallet_type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.wallet_type">Wallet Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.type", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.type">Type</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.description", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.description">Description</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.previous_amount", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.previous_amount">Previous Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.amout", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.amount">Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.current_amount", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.current_amount">Current Amount</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.status", $field, $sort_type); ?> enable-sort" sort_type="<?php echo $sort_type ?>" field="<?=$table?>.status">Status</th>
                                    <th class="<?php echo $this->main_model->getsortclass("{$table}.created", $field, $sort_type); ?>  enable-sort" sort_type="<?php echo $sort_type ?>"  field="<?=$table?>.created">Created</th>
                                    <?php /*<th>Action</th> */ ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($records as $row) {
                                    ?> 
                                    <tr>                                        
                                        <td data-title="Transaction ID">
                                            <?php echo $row->transaction_id; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Transaction Type">
                                            <?php echo $row->transaction_type; ?>
                                        </td>

                                        <td data-title="Wallet Type">
                                            <?php echo $row->wallet_type; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Type">
                                            <?php echo $row->type; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Description">
                                            <?php echo $row->description; ?>
                                        </td>

                                        <td data-title="Previous Amount">
                                            <?php echo $row->previous_amount; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Amount">
                                            <?php echo $row->amount; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Current Amount">
                                            <?php echo $row->current_amount; ?>
                                        </td>
                                        
                                    
                                        <td data-title="Status">
											
                                            <?php
                                            if ($row->status=="S")
												echo '<span style="color: green">Success</span>';
                                            else
                                                echo '<span style="color: red">Failed</span>';
                                               
                                            ?>
                                        </td>
                                        
                                        <td data-title="Created">
                                            <?php echo $row->created ? date(DATE_TIME_FORMAT_ADMIN, $row->created) : "N/A"; ?></td>
                                        <?php /* <td data-title="Action">
                                            <?php
                                            echo anchor($prefixUrl.'/?' . $row->id . "?return=" . $current_url, '<i class="fa fa-eye"></i>', array('title' => 'View tra', 'class' => 'btn btn-primary btn-xs '));
                                            ?>
                                        </td>	*/?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </section>
                </div>
            </section>
        </div>
    </div>
     <?php if($export == 'no') {  ?> 
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
                    Number of <?php echo $names ?> <span class="badge-gray"><?php echo $start; ?></span> - <span class="badge-gray"><?php echo $till_record; ?></span> out of <span class="badge-gray"><?php echo $total_rows; ?></span>

                    <div class="dataTables_paginate paging_bootstrap pagination">
                        <ul>
                            <?php echo $this->jquery_pagination->create_links(); ?>  
                        </ul>                      
                    </div>
                </div>
  
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
                    <?php echo $names ?>  List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?php echo $names ?>  added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

