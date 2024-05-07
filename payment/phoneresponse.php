<?php include('../newapi/connection.php'); ?>
	<?php  
	$json = file_get_contents('php://input');
 $data = json_decode($json);
 
	if(isset($data->response))
	{
  

  
 
 $bas64encode = base64_decode($data->response);
 $rowFinalData = json_decode($bas64encode);
 
      
    $secretkey = "";
     $orderId = $rowFinalData->data->merchantTransactionId;
   $reforderId = $rowFinalData->data->transactionId;
    $orderAmount = $rowFinalData->data->amount;
     $txStatus = $rowFinalData->code;
    $norderId = str_replace('MT7','',$orderId);
         
	$saltIndex =1;
	
	$sel_userQ =  $conn->prepare("Select * FROM tbl_tem_payment where tp_id = ? ");
    $sel_userQ->bindParam(1, $norderId);
    $sel_userQ->execute();
    $trans = $sel_userQ->fetch();
    
     


	if ($txStatus=='PAYMENT_SUCCESS') {
		  //   echo "/pg/v1/status/".MID."/$orderId" . MID_KEY;
    //   $strDatat = hash('sha256',"/pg/v1/status/".MID."/$orderId" . MID_KEY) . "###" . KEY_INDEX;
    //   $url =  'https://api-preprod.phonepe.com/apis/merchant-simulator/pg/v1/status/'.MID.'/'.$orderId;

    //     $curl = curl_init($url);
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
    //     $headers = array(
    //     "Content-Type: application/json",
    //     'X-VERIFY: '.$strDatat,
    //     'X-MERCHANT-ID:'.MID
        
    //     );
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($curl, CURLOPT_HEADER, true);
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     echo $response;
    //     die;
     
    //     $datajson = json_decode($responsenew);
       
       
          $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_status=\''.$txStatus.'\',payment_data=\''.$reforderId.'\' WHERE 1 AND tp_id='.$norderId;     
        $res = $conn->query($sqlSelect);
        
        echo 'success';
      
  
	  	}else {
         $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_status=\''.$txStatus.'\',payment_data=\''.$reforderId.'\' WHERE 1 AND tp_id='.$norderId;     
        $res = $conn->query($sqlSelect);
            echo 'failed';
    
        }
	}
	else{
	    echo 'failed';
	}
	  	?>