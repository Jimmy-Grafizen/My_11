<?php  
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }

require_once  'api.php';

$rwws = file_get_contents('php://input');
$aryPostData =(array)json_decode($rwws);
//login reuqest
 
if(isset($_POST) && count($_POST)>0)
{
///  $aryPostData =$_POST;
}
//  if(isset($_GET) && count($_GET)>0)
// {
//   $aryPostData =$_GET;
// }  
if(isset($aryPostData['type']) && $aryPostData['type'] == 'login'){
    $api = new api();
    $mobileno = $aryPostData['mobile'];
    $jsonstring = json_encode($aryPostData);
    $api->login($mobileno,$jsonstring);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'totalcommission'){
    $api = new api();
    $aryPostData['message'] ="ok";
          $intUserId = $aryPostData['user_id'];

      
             

 ///   $api->login($mobileno,$jsonstring);
   echo json_encode($api->getlevels($intUserId));
   exit;
}





if(isset($aryPostData['type']) && $aryPostData['type'] == 'sharetext'){
    $api = new api();
    $userslug = $aryPostData['slug'];
    $api->getsharetext($userslug);
} 


if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'redeem_amount'){
    $api = new api();
    $playerid = $_REQUEST['user_id'];
    $teamid = $_REQUEST['code'];
    $api->setRAmount($playerid,$teamid);
}
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'sms'){
    $api = new api();
    $api->send_sms('Otp 404',$to='7610022611',$issos='NO', $country_code=+91);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'redeem_amount'){
    $api = new api();
    $playerid = $aryPostData['user_id'];
    $teamid = $aryPostData['code'];
    $api->setRAmount($playerid,$teamid);
}


if(isset($aryPostData['type']) && $aryPostData['type'] == 'apply_coupon'){
    $api = new api();
    $playerid = $aryPostData['user_id'];
    $teamid = $aryPostData['code'];
    $api->setRAmount($playerid,$teamid);
}
if(isset($aryPostData['type']) && $aryPostData['type'] == 'change_password'){
    $api = new api();
    $playerid = $aryPostData['id'];
    $password = $aryPostData['password'];
   $api->setpassword($playerid,$password);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'player_info'){
    $api = new api();
    $playerid = $aryPostData['id'];
    $teamid = (isset($aryPostData['team_id']))?$aryPostData['team_id']:0;
    $api->getPlayerInfo($playerid,$teamid);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'ref_leaderboard'){
    $api = new api();
    $type = $aryPostData['ref_type'] ;
    $atype = $aryPostData['api_type'] ;
    $api->getRefLeaderboard($type,$atype);
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'ref_leaderboard'){
    $api = new api();
    $type = $_REQUEST['ref_type'] ;
    $atype = $_REQUEST['api_type'] ;
    $api->getRefLeaderboard($type,$atype);
}


if(isset($aryPostData['type']) && $aryPostData['type'] == 'apply_promo'){
    $api = new api();
    $api->applyPromo($aryPostData);
}


if(isset($aryPostData['type']) && $aryPostData['type'] == 'transfermoney'){
    $api = new api();
    $userslug = $aryPostData;
    $api->getDepositAmount($userslug);
}
if(isset($aryPostData['type']) && $aryPostData['type'] == 'getbanklist'){
    $api = new api();
    $userslug = $aryPostData['user_id'];
    $api->getBankList($userslug);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'getFantasyLeaderBoard'){
    $api = new api();
    $api->getWeeklyLeaderboard();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'getFantasyLeaderBoard'){
    $api = new api();
    $api->getWeeklyLeaderboard();
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'deletebankdetails'){
    $api = new api();
    $userslug = $aryPostData;
    $api->deleteBankList($userslug);
}
if(isset($aryPostData['type']) && $aryPostData['type'] == 'checkpaymentstatus'){
    $api = new api();
    $api->checkpaymentstatus($aryPostData);
  ///  $api->sendEmailVerificationLink($userslug);
}
if(isset($aryPostData['type']) && $aryPostData['type'] == 'sendemailverificationlink'){
    $api = new api();
    $userslug = $aryPostData['user_id'];
    $api->sendEmailVerificationLink($userslug);
}
if(isset($aryPostData['type']) && $aryPostData['type'] == 'gethomdata'){
    $api = new api();
    $userslug = $aryPostData;
    $api->getHomedata($userslug);
}

if(isset($aryPostData['type']) && $aryPostData['type'] == 'getteamsetting'){
    $api = new api();
    $userslug = $aryPostData;
    $respos = $api->getteamsettingdata($userslug);
    $aryRespose = array();
    $aryRespose['message'] ='ok';
    $aryRespose['result'] =$respos;
    echo json_encode($aryRespose);
    die;
}



if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'gethomdata'){
    $api = new api();
    $userslug = '284';
    echo "<pre>";
    $api->getHomedata($userslug);
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'sendLineupsNoti'){
    $api = new api();
    $api->sendNotificationForLineUps(array("series_name"=>'England tour of India, 2021','match_name'=>'India Vs England'));
    //$api->sendGCM(array("Hello"));
}

 
if(isset($aryPostData['type']) && $aryPostData['type'] == 'transfermoneyconfirm'){
    $api = new api();
    $rowUserInfo = $api->getUser($aryPostData['user_id']);
    $rowUserInfoReciepent = $api->getUser($aryPostData['user_id_to']);
    $strRemark = 'Paid To '.$rowUserInfoReciepent['firstname'].' '.$rowUserInfoReciepent['lastname'].' '.$rowUserInfoReciepent['phone'];
    $respo = array();
    $userWalletData = $api->getUpdatedWalletData($aryPostData['user_id']);

    if($userWalletData['wallet']['winning_wallet']>0 && $userWalletData['wallet']['winning_wallet']>=$aryPostData['transfer_amount']){
        if($api->customer_deposit_amount($aryPostData['user_id'],$aryPostData['transfer_amount'],'DEBIT',$strRemark,'winning_wallet','TRANSFER_MONEY')){
            
            $strRemark = 'Received From '.$rowUserInfo['firstname'].' '.$rowUserInfo['lastname'].' '.$rowUserInfo['phone'];
            if($api->customer_deposit_amount($aryPostData['user_id_to'],$aryPostData['transfer_amount'],'CREDIT',$strRemark,'deposit_wallet','RECEIVED_MONEY')){
                $respo['message'] = 'Amount Transfer Successfully';
                $respo['error'] = false;
           }else{
                 $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
            }
        }else{
           $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
        }
    }else{
        $remainingTransferAmount = $aryPostData['transfer_amount'] - $userWalletData['wallet']['winning_wallet'];
        if($userWalletData['wallet']['winning_wallet']>0)
        {
            $api->customer_deposit_amount($aryPostData['user_id'],$userWalletData['wallet']['winning_wallet'],'DEBIT',$strRemark,'winning_wallet','TRANSFER_MONEY');
        }
        if($api->customer_deposit_amount($aryPostData['user_id'],$remainingTransferAmount,'DEBIT',$strRemark,'deposit_wallet','TRANSFER_MONEY')){
            $strRemark = 'Received From '.$rowUserInfo['firstname'].' '.$rowUserInfo['lastname'].' '.$rowUserInfo['phone'];
            if($api->customer_deposit_amount($aryPostData['user_id_to'],$aryPostData['transfer_amount'],'CREDIT',$strRemark,'deposit_wallet','RECEIVED_MONEY')){
                $respo['message'] = 'Amount Transfer Successfully';
                 $respo['error'] = false;
           }else{
                 $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
            }
        }else{
           $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
        }
    }
    echo json_encode($respo);
    exit;
}


/* if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'transfermoneyconfirm'){
    $api = new api();
     $rowUserInfo = $api->getUser($aryPostData['user_id']);
    $strRemark = 'Paid To '.$rowUserInfo['firstname'].' '.$rowUserInfo['lastname'].' '.$rowUserInfo['phone'];
    $respo = array();
    $userWalletData = $api->getUpdatedWalletData($aryPostData['user_id']);

    print_r($userWalletData);
    die;

    if($userWalletData['winning_wallet']>0 && $userWalletData['winning_wallet']>=$aryPostData['transfer_amount']){
        if($api->customer_deposit_amount($aryPostData['user_id'],$aryPostData['transfer_amount'],'DEBIT',$strRemark,'winning_wallet','TRANSFER_MONEY')){
            $rowUserInfoReciepent = $api->getUser($aryPostData['user_id_to']);
            $strRemark = 'Received From '.$rowUserInfoReciepent['firstname'].' '.$rowUserInfoReciepent['lastname'].' '.$rowUserInfoReciepent['phone'];
            if($api->customer_deposit_amount($aryPostData['user_id_to'],$aryPostData['transfer_amount'],'CREDIT',$strRemark,'deposit_wallet','RECEIVED_MONEY')){
                $respo['message'] = 'Amount Transfer Successfully';
                $respo['error'] = false;
           }else{
                 $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
            }
        }else{
           $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
        }
    }else{
        $remainingTransferAmount = $aryPostData['transfer_amount'] - $userWalletData['winning_wallet'];
        $api->customer_deposit_amount($aryPostData['user_id'],$userWalletData['winning_wallet'],'DEBIT',$strRemark,'winning_wallet','TRANSFER_MONEY');
        if($api->customer_deposit_amount($aryPostData['user_id'],$remainingTransferAmount,'DEBIT',$strRemark,'deposit_wallet','TRANSFER_MONEY')){
            $rowUserInfoReciepent = $api->getUser($aryPostData['user_id_to']);
            $strRemark = 'Received From '.$rowUserInfoReciepent['firstname'].' '.$rowUserInfoReciepent['lastname'].' '.$rowUserInfoReciepent['phone'];
            if($api->customer_deposit_amount($aryPostData['user_id_to'],$aryPostData['transfer_amount'],'CREDIT',$strRemark,'deposit_wallet','RECEIVED_MONEY')){
                $respo['message'] = 'Amount Transfer Successfully';
                 $respo['error'] = false;
           }else{
                 $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
            }
        }else{
           $respo['message'] = 'Some Error Occur';
                 $respo['error'] = true;
        }
    }
    echo json_encode($respo);
    exit;
} */

if(isset($aryPostData['type']) && $aryPostData['type'] == 'get_upcoming_series'){
  $api = new api();
    $en = new Entitysport();
    $userslug = $en->upcoming_matches_series();
  ///  print_r($userslug);
   $api->pushUpcomingSeries($userslug);
} 

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get_upcoming_match'){
    $api = new api();
    $en = new Entitysport();
    $userslug = $en->upcoming_matches(0);
    $api->pushUpcomingMatch($userslug,1);
    exit;
} 
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get_upcoming_match_football'){
    $api = new api();
    $en = new Entitysport();
    $userslug = $en->upcoming_matches_football(0);
//   echo '<pre>';
//     print_r($userslug);
  $api->pushUpcomingMatch($userslug,2);
    exit;
} 


if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get_upcoming_match_basketball'){
    $api = new api();
    $en = new Entitysport();
    $userslug = $en->upcoming_matches_basketball(0);
//   echo '<pre>';
//     print_r($userslug);
 $api->pushUpcomingMatch($userslug,3);
    exit;
} 


// if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get_upcoming_match_football'){
//     $api = new api();
//     $en = new Entitysport();
//     $userslug = $en->upcoming_matches(1);
//     $api->pushUpcomingMatch($userslug);
//     exit;
// } 



if(isset($aryPostData['type']) && $aryPostData['type'] == 'paymenttoken'){
    $api = new api();
    $api->paymenttoken($aryPostData);
  ///  $api->sendEmailVerificationLink($userslug);
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'check_live_matches'){
    $api = new api();
    $api->setStatus();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'check_live_matches_football'){
    $api = new api();
    $api->setStatusfootball();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'pdf_cron'){
    $api = new api();
    print_r($api->generate_pdf_cron());
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'check_completed_matches'){
    $api = new api();
    $api->setCompletedStatus();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'check_completed_matches_football'){
    $api = new api();
    $api->setCompletedStatusfootball();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'live_match_cron'){
    $api = new api();
    $api->live_match_cron();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'live_match_cron_football'){
    $api = new api();
    $api->live_match_cron_football();
}

if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'updatepaymentstatus'){

	$api = new api();
	$api->updatepaymentstatus();
}