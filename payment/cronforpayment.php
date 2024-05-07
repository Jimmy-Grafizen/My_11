<?php include('../newapi/connection.php'); 




 define('MID_KEY','e8f29bd0-1a6c-44de-9fc6-039c59733471');
 define('KEY_INDEX','1');
define('MID','MY11OPTIONONLINE');
define('API_HOST','https://api.phonepe.com/apis/hermes');


	$saltIndex =1;
	$sel_userQ =  $conn->prepare("Select * FROM tbl_tem_payment where 1 AND (tp_status = 'PAYMENT_PENDING' OR tp_status='0') AND tp_callback_count<=15 ");
	$status ="PAYMENT_PENDING";
    $sel_userQ->execute();
    $trans = $sel_userQ->fetchAll();
    foreach($trans as $row)
    {
		///print_r($row);die;
       $response =  connect($row['tp_id']);
       $data = json_decode($response);
        if($data->code=='PAYMENT_SUCCESS')
        {
            
         $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,tp_status=\'PAYMENT_SUCCESS\',payment_data=\''.$data->transactionId.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
        }else  if($data->code!='PAYMENT_PENDING')
        {
             $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,tp_status=\'PAYMENT_ERROR\',payment_data=\''.$data->transactionId.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
        }else{
		  $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,payment_data=\''.$response.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
		}
    }


$sel_userQ =  $conn->prepare("Select * FROM tbl_tem_payment where 1 AND tp_status = 'PAYMENT_SUCCESS' AND tp_callback_count<=1 ");
	$status ="PAYMENT_PENDING";
    $sel_userQ->execute();
    $trans = $sel_userQ->fetchAll();
    foreach($trans as $row)
    {
		///print_r($row);die;
       $response =  connect($row['tp_id']);
       $data = json_decode($response);
        if($data->code=='PAYMENT_SUCCESS')
        {
            
         $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,tp_status=\'PAYMENT_SUCCESS\',payment_data=\''.$data->transactionId.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
        }else  if($data->code!='PAYMENT_PENDING')
        {
             $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,tp_status=\'PAYMENT_ERROR\',payment_data=\''.$data->transactionId.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
        }else{
		  $sqlSelect = 'UPDATE  tbl_tem_payment SET tp_callback_count=tp_callback_count+1,payment_data=\''.$response.'\' WHERE 1 AND tp_id='.$row['tp_id'];     
        $res = $conn->query($sqlSelect);
		}
    }


    function connect($orderId){
    $ch = curl_init();
             $orderId = $orderId;
  $strDatat = hash('sha256',"/pg/v1/status/".MID."/$orderId" . MID_KEY) . "###" . KEY_INDEX;
    $headers = array(
        "Content-Type: application/json",
        'X-VERIFY: '.$strDatat,
        'X-MERCHANT-ID:'.MID
        
        );
           $url =  API_HOST.'/pg/v1/status/'.MID.'/'.$orderId;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $body = '{}';

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $authToken = curl_exec($ch);

    return $authToken;
}
    
?>