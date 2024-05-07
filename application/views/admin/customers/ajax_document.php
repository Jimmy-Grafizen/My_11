<?php

$paindocs = PANCARD_IMAGE_THUMB_URL;

$full_doc_paths = PANCARD_IMAGE_LARGE_URL;



$bankthum = BANK_IMAGE_THUMB_URL;

$bank_pulldoc_paths = BANK_IMAGE_LARGE_URL;

?>

<div class="col-sm-12">

<h4 class="pull-left" >Pan Card</h4> <a href="<?php echo HTTP_PATH.'admin/customers/edit_pan_bank/'.$customer_id.'?return='.$return; ?>" class="pull-right" style="font-size: 20px;" title="Edit Pan Card and Bank Detail"><i class="fa fa-edit"></i></a>

<?php if($pain_card) { ?>



<table class="table table-bordered table-striped table-condensed cf table-hover" >

		<tr>

			<td colspan="2">Name : <?php echo $pain_card->name; ?></td>

			<td>Pan number : <?php echo $pain_card->pain_number; ?></td>

		</tr>

		<tr>

			<td>DOB : <?php echo date(DATE_FORMAT_ADMIN,strtotime($pain_card->dob)); ?></td>

			<td>State : <?php echo $pain_card->state; ?></td>

			<td>Submitted Date : <?php echo date(DATE_FORMAT_ADMIN,$pain_card->createdat); ?></td>

		</tr>

		<tr>

			<td>

				<a href="<?php echo $full_doc_paths.$pain_card->image; ?>" target="_blank" title="Pan card View">

				<img src="<?php echo $full_doc_paths.$pain_card->image; ?>" style="width: 100px;height: 100px;" /></a>

			</td>

			<td colspan="2">

			

					<div id="status_<?php echo $paincard_id; ?>" class="col-sm-6">

					Status <br/>

						<?php

								if($pain_card->status=="P")

									echo '<i class="fa fa-clock-o btn btn-primary" title="Pending"></i>';

								else if($pain_card->status=="A")

									echo '<i class="fa fa-check btn btn-success" title="Approved"></i>';

								else if($pain_card->status=="R")

									echo '<i class="fa fa-times btn btn-danger" title="Rejected"></i>';

								else if($pain_card->status=="X")

									echo 'Expired';

								else

									echo 'N/A';			

						?>

					</div>

					<div id="action_<?php echo $paincard_id; ?>" class="col-sm-6">

					Action

					<br/>

						<?php

								

								

								if($pain_card->status=="P"){

									echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="pain_card" status="A">Approve</a>';

									echo "  Or ";

									echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="pain_card" status="R">Reject</a>';

								}else if($pain_card->status=="A"){

									echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="pain_card" status="R">Reject</a>';

								}else if($pain_card->status=="R"){

									echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="pain_card" status="A">Approve</a>';

								}else if($pain_card->status=="X"){

									echo 'Expired';

								}else{

									echo 'N/A';		

								}			

						?>

					</div>

					<div class="col-sm-12">

						<?php

						 if( isset($pain_card->reason) && !empty($pain_card->reason) ){ 

							echo "<b>Reason :</b> ". $pain_card->reason;

							}

						?>

					</div>

			

			</td>

		</tr>

		

</table>

<?php }else{ ?>

	<p>Not found Bank Details</p>

	<?php }?>

</div>



<div class="col-sm-12">

<h4>Bank Detail</h4>

<?php if($bankdetails) { ?>

<table class="table table-bordered table-striped table-condensed cf table-hover" >

		<tr>

			<td>Name : <?php echo $bankdetails->name; ?></td>

			<td>Accout Number : <?php echo $bankdetails->account_number; ?></td>

		</tr>

		<tr>

			<td>IFSC : <?php echo $bankdetails->ifsc; ?></td>

			<td>Submitted Date : <?php echo date(DATE_FORMAT_ADMIN,$bankdetails->createdat); ?></td>

		</tr>

		<tr>

			

			<!-- <td>

				<a href="<?php //echo $bank_pulldoc_paths.$bankdetails->image; ?>" target="_blank" title="Bank Details View">

				<img src="<?php //echo $bankthum.$bankdetails->image; ?>" style="width: 100px;height: 100px;" /></a>

			</td> -->

			<td colspan="2">

					<div id="status_<?php echo $bankdetailId; ?>" class="col-sm-6">

					Status <br/>

						<?php

								if($bankdetails->status=="P")

									echo '<i class="fa fa-clock-o btn btn-primary" title="Pending"></i>';

								else if($bankdetails->status=="A")

									echo '<i class="fa fa-check btn btn-success" title="Approve"></i>';

								else if($bankdetails->status=="R")

									echo '<i class="fa fa-times btn btn-danger" title="Rejected"></i>';

								else if($bankdetails->status=="X")

									echo 'Expired';

								else

									echo 'N/A';			

						?>

					</div>

					<div id="action_<?php echo $bankdetailId; ?>" class="col-sm-6">

					Action

					<br/>

						<?php

								

								if($bankdetails->status=="P"){

									echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="bankdetails" status="A">Approve</a>';

									echo "  Or ";

									echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="bankdetails" status="R">Reject</a>';

								}else if($bankdetails->status=="A"){

									echo '<a href="javascript:void(0)" title="Reject" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="bankdetails" status="R">Reject</a>';

								}else if($bankdetails->status=="R"){

									echo '<a href="javascript:void(0)" title="Approve" class="docnbank__" data-id="'.$paincard_id.",".$bankdetailId .'" type="bankdetails" status="A">Approve</a>';

								}else if($bankdetails->status=="X"){

									echo 'Expired';

								}else{

									echo 'N/A';		

								}	

						?>

					</div>

					<div class="col-sm-12">

						<?php

						 if(isset($bankdetails->reason) && !empty($bankdetails->reason) ){ 

							echo "<b>Reason :</b> ". $bankdetails->reason;

							}

						?>

					</div>

				</td>

		</tr>

		

</table>



<?php }else{ ?>

	<p>Not found Bank Details</p>

	<?php }?>

</div>

