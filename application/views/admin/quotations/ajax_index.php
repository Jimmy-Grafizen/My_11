<?php 

if ($records) { ?>
    <?php
    echo form_open('admin/quotations/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Quotations 
                </header>
                <div class="panel-body">
                
                </div>
                <div class="panel-body">
                    <section id="no-more-tables">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>                                   
                                    <th>Game Name</th>
                                    <th>Link</th>
                                    <th>Expiry Date</th>
                                    <th>Image</th>
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
                                        <td data-title="Game Name">
                                            <?php echo $row->game_name; ?>
                                        </td>
                                        <td data-title="Link">
                                            <?php if(!empty($row->link)) echo anchor( $row->link , $row->link,array('title' => 'See Link', 'target' => '_blank')) ; ?>
                                        </td>
                                        <td data-title="Expiry Date">
                                            <?php echo date(DATE_TIME_FORMAT_ADMIN,$row->expiry_date); ?>
                                        </td>
                                        <td data-title="Image">
                                            <?php 
                                                if( !empty($row->image) )
                                                    echo '<a href="'.QUOTATIONS_IMAGE_THUMB_URL.$row->image.'"> <img src="'.QUOTATIONS_IMAGE_THUMB_URL.$row->image.'"></a>';
                                                else
                                                    echo '<img src="'.NO_IMG_URL.'" height="32" width="32">';
                                            ?>
                                    
                                        </td>
                                        <td data-title="Action">
                                        <?php
                                        //if(!in_array($row->id, $notchange)){
                                            if ($row->status=="D"){
                                                echo anchor("{$prefixUrl}activate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Activate" class="btn btn-success btn-xs action-list"');
                                            }
                                            else{
                                                echo anchor("{$prefixUrl}deactivate/" . $row->id . "?return=" . $current_url, '<i class="fa fa-check"></i>', 'title="Deactivate" class="btn btn-danger btn-xs action-list action-list"');
                                            }

                                        //}
                                            echo anchor($prefixUrl.'edit/' . $row->id . "?return=" . $current_url, '<i class="fa fa-pencil"></i>', array('title' => 'Edit Quotations Customize', 'class' => 'btn btn-primary btn-xs '));
                                            //echo anchor($prefixUrl."delete/" . $row->id . "?return=" . $current_url, '<i class="fa fa-trash-o "></i>', array('title' => 'Delete', 'class' => 'btn btn-danger btn-xs action-list delete-list'));
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
            </section>
        </div>
    </div>
        <input type="hidden" name ="current_url" id="current_url" value="<?php echo $current_url; ?>" />
    <?php echo form_close(); ?>

<?php } else {
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
                    Quotations Customize List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no quotations added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }?>

