<?php if ($records) { ?>

<table class="table table-bordered table-striped table-condensed cf">
    <thead class="cf">
        <?php /*<tr>
        <th align="center" colspan="7" style="border:1px solid #000;"><h2>Pending Withdrawals list</h2></th>
       </tr>--*/?>
        <tr>
            <th>Name</th>
            <th>State</th>
            <!--th>Phone</th-->
            <th>Account Number</th>
            <th>IFSC</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($records as $row) {
            if($this->customer_model->IsDonebankdetailnPaincard($row->customer_id)){ 
            ?> 
            <tr>
                <td data-title="Name">
                    <?php echo $row->name; ?>
                </td>
                <td data-title="State">
                    <?php echo $row->stateName; ?>
                </td>
                 <?php /*<!--td data-title="phone">
                    <?php echo $row->country_mobile_code.$row->phone; ?>
                </td--->*/?>
                <td data-title="Account Number">
                    <?php echo $row->account_number; ?>
                </td>
                <td data-title="Ifsc">
                    <?php echo $row->ifsc; ?>
                </td>
                <td data-title="Amount">
                    <?php echo $row->amount; ?>
                </td>
            </tr>
        <?php 
            }
        } ?>
    </tbody>
</table>
                    

<?php } else {
    ?>
    <div class="row">
        <div class="col-lg-12">
            <section class="panel">
                <header class="panel-heading">
                    Pending Withdrawals List
                </header>
                <div class="panel-body">
                    <section id="no-more-tables">There are no Pending Withdrawals added on site yet.</section>
                </div>
            </section>
        </div>
    </div>  

<?php }
?>

