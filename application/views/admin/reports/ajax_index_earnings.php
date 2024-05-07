<?php if ($records) { ?>

    <?php
    echo form_open($prefixUrl.'/action', array('id' => 'table_form', 'class' => 'form-inline form'));
    ?>
    <div class="row">
        <div class="col-lg-12">
            <?php $this->load->view('element/actionMessage'); ?>
            <section class="panel">
                <header class="panel-heading">
				 <?php if($export == 'no') { ?><?=$names?> List<?php } ?>
                </header>
                <div class="panel-body">
               </div>
                <div class="panel-body">
                    <section id="no-more-tables" class="table responsive">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                                <tr>                              
									<th>Matches date</th>
									<th>Total Matches</th>
									<th>Credited Amount</th>
									<th>Debited Amount</th>
                                    <th>Refund Amount</th>
									<th>Earnings</th>
                                    <th>Tax Amounts</th>
                                    <?php if($export == 'no') { ?><th style="width:140px;">Action</th><?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
								$total_matches = array();
								$total_cr = array();
								$total_dr = array();
                                $total_ra = array();
                                $total_ta = array();
								$total_earnings = array();
                                foreach ($records as $row) { 
                                    ?> <tr>
                                        <td data-title="Matches Date"> 
                                            <?php										
                                            echo $row['group_match_date'];
                                          ?>
                                        </td>
                                        <td data-title="Total Matches"> 
                                            <?php										
                                            echo $row['total_matches'];
                                              $total_matches[] = $row['total_matches'];
                                            ?>
                                        </td>
                                        <td data-title="Credited Amount"> 
                                            <?php											
									           $spendamount  = $row['spendamount'];
									           $total_cr[] = $spendamount;
									           echo  number_format( $spendamount, 2) ;
                                            ?>
                                        </td>
                                        <td data-title="Debited Amount"> 
                                            <?php
                                            	$winamountTotal =  $row['final_win'];
                                            	$total_dr[] = $winamountTotal;
                                                echo number_format( $winamountTotal , 2); 
                                            ?>
										</td>
                                        <td data-title="Refund Amount"> 
                                            <?php
                                                $refund_amounts =  $row['refund_amounts'];
                                                $total_ra[] = $refund_amounts;
                                                echo number_format( $refund_amounts , 2);
                                            ?>
                                        </td>
                                        <td data-title="Earnings"> 
                                            <?php
                                                $earning =  $row['earnings'];
                                                $total_earnings[] = $earning;
                                                echo number_format( $earning , 2);
                                            ?>
										</td>
                                        <td data-title="Tax Amounts"> 
                                            <?php
                                                $tax_amounts =    $row['tax_amounts'];
                                                $total_ta[] = $tax_amounts;
                                                 echo number_format( $tax_amounts  , 2);
                                            ?>
                                        </td>
                                        <?php if($export == 'no') { ?>
                                        <td data-title="Action">
                                            <?php
                                            echo anchor($prefixUrl."from_eraning_match_set/".str_ireplace(",","-",$row['matchesids']), '<i class="fa fa-eye"></i>', array('modaldata'=>'','title' => 'View Matches', 'class' => 'btn btn-primary btn-xs teamsclsviews','style' => 'margin-left: 5px;'));
                                            ?>
                                        </td>
                                        <?php } ?>	
                                    </tr>
                           <?php } ?>
                           <tr>
							<td>
								Total
                            </td>
                            
							<td>
							<?php	echo  number_format( array_sum($total_matches), 2); ?>
                            </td>
							<td>
							<?php	echo  number_format( array_sum($total_cr), 2); ?>
                            </td>
							<td>
							<?php	echo  number_format( array_sum($total_dr), 2); ?>
                            </td>
                            <td>
                            <?php   echo  number_format( array_sum($total_ra), 2); ?>
                            </td>
							<td>
							<?php	echo  number_format( array_sum($total_earnings), 2); ?>
                            </td>	
                            <td>
                            <?php   echo  number_format( array_sum($total_ta), 2); ?>
                            </td>   
                                    </tr>
                            </tbody>
                        </table>
                    </section>
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
                    <?=$names?> List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no <?=$names?> added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

