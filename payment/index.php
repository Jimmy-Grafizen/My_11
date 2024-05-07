<?php include('../newapi/connection.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Cashfree - Signature Generator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body onload="document.frm1.submit()">


<?php
$orderId = 0;
$orderAmount = $_GET['amount'];
$orderCurrency = 'INR';
$orderNote = "Payemnt for contest";
$customerName = "";
$customerPhone = "";
$customerEmail = "";
$returnUrl = "https://deep11fantasy.com/portal/payment/response.php";
$notifyUrl = "https://deep11fantasy.com/portal/payment/response.php";
if(isset($_GET['amount']) && isset($_GET['user_id'])){
    
    $sel_userQ =  $this->conn->prepare("Select * FROM tbl_customers where id = ? ");
    $sel_userQ->bindParam(1, $_GET['user_id']);
    $sel_userQ->execute();
    $userData = $sel_userQ->fetch();
    $customerName = $userData['firstname'];
    $customerPhone = $userData['phone'];
    $customerEmail = $userData['email'];

    $sel_user_query = "INSERT INTO tbl_tem_payment (tp_user_id,tp_amount,tp_status) VALUES (?,?,?)";
    $status = 0;
    $amt = $_GET['amount'];
    $user = $_GET['user_id'];
    $sel_user = $this->conn->prepare($sel_user_query);
    $sel_user->bindParam(1, $user);
    $sel_user->bindParam(2, $amt);
    $sel_user->bindParam(3, $status);
    $sel_user->execute(); 
    $orderId = $conn->lastInsertId();
}

 $mode = "PROD"; //<------------ Change to TEST for test server, PROD for production
//$mode = "TEST"; //<------------ Change to TEST for test server, PROD for production


  $secretKey = "1667bd96707ea765054e83287a9f853470f4acbd";
  $postData = array( 
  "appId" => '239960589f190c7eb188a5cb6c069932', 
  "orderId" => $orderId, 
  "orderAmount" => $orderAmount, 
  "orderCurrency" => $orderCurrency, 
  "orderNote" => $orderNote, 
  "customerName" => $customerName, 
  "customerPhone" => $customerPhone, 
  "customerEmail" => $customerEmail,
  "returnUrl" => $returnUrl, 
  "notifyUrl" => $notifyUrl,
);
ksort($postData);
$signatureData = "";
foreach ($postData as $key => $value){
    $signatureData .= $key.$value;
}
$signature = hash_hmac('sha256', $signatureData, $secretKey,true);
$signature = base64_encode($signature);

if ($mode == "PROD") {
  $url = "https://www.cashfree.com/checkout/post/submit";
} else {
  $url = "https://test.cashfree.com/billpay/checkout/post/submit";
}

?>
  <form action="<?php echo $url; ?>" name="frm1" method="post">
      <p>Please wait.......</p>
      <input type="hidden" name="signature" value='<?php echo $signature; ?>'/>
      <input type="hidden" name="orderNote" value='<?php echo $orderNote; ?>'/>
      <input type="hidden" name="orderCurrency" value='<?php echo $orderCurrency; ?>'/>
      <input type="hidden" name="customerName" value='<?php echo $customerName; ?>'/>
      <input type="hidden" name="customerEmail" value='<?php echo $customerEmail; ?>'/>
      <input type="hidden" name="customerPhone" value='<?php echo $customerPhone; ?>'/>
      <input type="hidden" name="orderAmount" value='<?php echo $orderAmount; ?>'/>
      <input type ="hidden" name="notifyUrl" value='<?php echo $notifyUrl; ?>'/>
      <input type ="hidden" name="returnUrl" value='<?php echo $returnUrl; ?>'/>
      <input type="hidden" name="appId" value='<?php echo $appId; ?>'/>
      <input type="hidden" name="orderId" value='<?php echo $orderId; ?>'/>
  </form>
</body>
</html>
