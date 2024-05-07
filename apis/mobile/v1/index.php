<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
$dir="include";
require_once '../'.$dir.'/DbHandler.php';
require_once '../'.$dir.'/Entitysport.php';

require '../../../lib/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
// User id from db - Global Variable
$user_id = NULL;


$app->get('/init_s3_bucket',function() use ($app){
     $db = new DbHandler();
     $output=array();
     $folders=array(GAME_IMAGE_THUMB_PATH,
        GAME_IMAGE_LARGE_PATH,
        CUSTOMER_IMAGE_THUMB_PATH,
        CUSTOMER_IMAGE_LARGE_PATH,
        PLAYER_IMAGE_THUMB_PATH,
        PLAYER_IMAGE_LARGE_PATH,
        TEAMCRICKET_IMAGE_THUMB_PATH,
        TEAMCRICKET_IMAGE_LARGE_PATH,
        SLIDER_IMAGE_THUMB_PATH,
        SLIDER_IMAGE_LARGE_PATH,
        CONTEXTCATEGORY_IMAGE_THUMB_PATH,
        CONTEXTCATEGORY_IMAGE_LARGE_PATH,
        PANCARD_IMAGE_THUMB_PATH,
        PANCARD_IMAGE_LARGE_PATH,
        BANK_IMAGE_THUMB_PATH,
        BANK_IMAGE_LARGE_PATH,
        NOTIFICATION_IMAGE_THUMB_PATH,
        NOTIFICATION_IMAGE_LARGE_PATH,
        EMAILS_NOTIFICATION_IMAGE_THUMB_PATH,
        EMAILS_NOTIFICATION_IMAGE_LARGE_PATH,
        REFER_EARN_IMAGE_THUMB_PATH,
        REFER_EARN_IMAGE_LARGE_PATH,
        MATCH_IMAGE_THUMB_PATH,
        MATCH_IMAGE_LARGE_PATH,
        REACTION_IMAGE_THUMB_PATH,
        REACTION_IMAGE_LARGE_PATH,
        APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH,
        APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH,
        QUOTATIONS_IMAGE_THUMB_PATH,
        QUOTATIONS_IMAGE_LARGE_PATH,
        PDF_PATH);

     foreach ($folders as $key => $value) {
         $output[]=$db->createFolderOnS3Bucket($value);
     }
     
     print_r($output);
});

$app->get('/entity_fantasy_squade/:match_unique_id',  function($match_unique_id) use ($app) {
     $db = new Entitysport();
     //$db->send_sms("Welcome to BetnBall! Verification PIN for verifying your account is 5678.",$phone);
     $result=$db->fantasy_squade($match_unique_id);
     echo "<pre>";
     print_r($result);

});

$app->get('/entity_fantasy_summary/:match_unique_id',  function($match_unique_id) use ($app) {
     $db = new Entitysport();
     //$db->send_sms("Welcome to BetnBall! Verification PIN for verifying your account is 5678.",$phone);
     $result=$db->fantasy_summary($match_unique_id,array());
     echo "<pre>";
     print_r($result);

});


$app->get('/test_sms/:phone',  function($phone) use ($app) {
     $db = new DbHandler();
     $res=$db->send_sms("Welcome to Deep11fantasy! Verification PIN for verifying your account is 1234.",$phone);
     //$db->sendSMTPMail("test email", "hello this is test", "manoj.sharma.guy@gmail.com");

});

$app->get('/test_email/:toemail/:toname',  function($toemail,$toname) use ($app) {
     $db = new DbHandler();
     $subject="Deep11fantasy TEST SUBJECT";
     $message="Deep11fantasy test mail message.";
    print_r( $db->sendSMTPMail($subject, $message, $toemail, $toname, SMTP_FROM_NAME, SMTP_FROM_EMAIL) );

});



$app->get('/get_player_detail_cron', function () use ($app) {       
    $response = array();
    $db = new DbHandler();
    $res = $db->get_player_detail_cron();
    $db->closeDbConnection();
    echoRespnse(200, $res);
});

$app->get('/get_player_detail_finder_cron', function () use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->get_player_detail_finder_cron();
    $db->closeDbConnection();
    echoRespnse(200, $res);
});


$app->get('/match_progress_cron', function () use ($app) {       
    $response = array();
    $db = new DbHandler();
    $res = $db->match_progress_cron();
    $db->closeDbConnection();
    echoRespnse(200, $res);
});


$app->get('/now_playing_cron/:match_unique_id', function ($match_unique_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->now_playing_cron($match_unique_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);
});

$app->get('/abondant_live_match_contest_cron/:match_unique_id', function ($match_unique_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->abondant_live_match_contest_cron($match_unique_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);
});



$app->get('/live_match_cron', function () use ($app) {       
   
    $response = array();
    $db = new DbHandler();
    $res = $db->live_match_cron(0);
    $db->closeDbConnection();
    echoRespnse(200, $res);
    
});

$app->get('/live_match_cron/:match_unique_id', function ($match_unique_id) use ($app) {

    $response = array();
    $db = new DbHandler();
    $res = $db->live_match_cron($match_unique_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);

});


$app->get('/update_point_and_rank/:match_unique_id/:match_id', function ($match_unique_id,$match_id) use ($app) {

    $response = array();
    $db = new DbHandler();
    $res = $db->update_point_and_rank($match_unique_id,$match_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);

});


$app->get('/update_match_score/:match_unique_id', function ($match_unique_id) use ($app) {

    $response = array();
    $db = new DbHandler();
    $res = $db->update_match_score($match_unique_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);

});


$app->get('/send_email_cron', function () use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->send_email_cron();
    echoRespnse(200, $res);
});


$app->get('/generate_match_leaderboard/:match_unique_id', function ($match_unique_id) use ($app){   
    $response = array();
    $db = new DbHandler();
    $res = $db->generate_match_leaderboard($match_unique_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);    
});


$app->get('/generate_series_leaderboard/:series_id', function ($series_id) use ($app) {   
    $response = array();
    $db = new DbHandler();
    $res = $db->generate_series_leaderboard($series_id);
    $db->closeDbConnection();
    echoRespnse(200, $res);    
});


$app->get('/generate_dream_team/:match_id/:match_unique_id', function ($match_id,$match_unique_id) use ($app) {


    $response = array();

    $db = new DbHandler();
    $res = $db->generate_dream_team($match_id,$match_unique_id);

    echoRespnse(200, $res);

});

$app->get('/generate_declare_match_result/:match_id/:match_unique_id', function ($match_id,$match_unique_id) use ($app) {

    $response = array();

    $db = new DbHandler();
    $res = $db->generate_declare_match_result($match_id,$match_unique_id);
    $db->closeDbConnection();

    echoRespnse(200, $res);

});

$app->get('/set_match_result/:match_id/:match_unique_id', function ($match_id,$match_unique_id) use ($app) {

    $response = array();

    $db = new DbHandler();
    $res = $db->setMatchResult($match_id,$match_unique_id);
    $db->closeDbConnection();

    echoRespnse(200, $res);

});


$app->get('/declare_match_result/:match_id/:match_unique_id', function ($match_id,$match_unique_id) use ($app) {
    

    $response = array();

    $db = new DbHandler();
    $res = $db->declare_match_result($match_id,$match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Invalid Match.";
        
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Result Declare successfully..";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
});


$app->get('/abodent_match/:match_id/:match_unique_id', function ($match_id,$match_unique_id) use ($app) {

    $response = array();

    $db = new DbHandler();
    $res = $db->abodent_match($match_id,$match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "match abodant successfully..";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
});


$app->get('/generate_pdf_cron', function () use ($app) {

    $response = array();
    $db = new DbHandler();
    $res = $db->generate_pdf_cron();
    $db->closeDbConnection();
     //echoRespnse(200, $res);

});


$app->get('/generate_pdf_cron/:match_unique_id/:match_contest_id', function ($match_unique_id,$match_contest_id) use ($app) {

    $response = array();
    $db = new DbHandler();
    $res = $db->generate_pdf_cron($match_unique_id,$match_contest_id);
    $db->closeDbConnection();
    //echoRespnse(200, $res);

});


$app->post('/send_notification_to_customer', function () use ($app) {


    verifyRequiredParams(array('noti_type', 'alert_message', 'customer_id', 'dbsave'));


    $noti_type    = $app->request()->post('noti_type');
    $alert_message    = $app->request()->post('alert_message');
    $customer_id    = $app->request()->post('customer_id');
    $customer_id    = $app->request()->post('customer_id');
    $dbsave    = $app->request()->post('dbsave');


    $notification_data=array();
    $notification_data['noti_type']=$noti_type;

    $db = new DbHandler();
    $db->send_notification_and_save($notification_data,$customer_id,$alert_message,$dbsave);
    $db->closeDbConnection();

});

$app->get('/update_new_available_match_count',  function() use ($app) {
    
   
     $db = new DbHandler();
     $result=$db->update_new_available_match_count();
     $db->closeDbConnection();
     
     echoRespnse(200,$result);
});


$app->post('/create_admin_customer_team_join_contest', function() use ($app) {


    verifyRequiredParams(array('match_unique_id','player_json','user_id','customer_team_name','team_name','match_contest_id','is_update'));
    
    $user_id = $app->request()->post('user_id');
    $db = new DbHandler();

    $fakeUserDetail=$db->getMiniUpdatedProfileData($user_id);
    if(empty($fakeUserDetail)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Customer not found.";
        echoRespnse(201, $response);
       return;
    }else if($fakeUserDetail['is_fake']=="0"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Invalid Customer";
        echoRespnse(201, $response);
        return;
    }

    $contestEntryFee=0;
    $is_update = $app->request()->post('is_update');
    $match_contest_id = $app->request()->post('match_contest_id');

    $matchContestDetail=$db->get_match_contest_detail_mini($match_contest_id);
    if(empty($matchContestDetail)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Contest detail not found.";
        echoRespnse(201, $response);
       return;
    }else if(($matchContestDetail['is_beat_the_expert']=='Y') && ($matchContestDetail['team_id']>0)
     && ($fakeUserDetail['is_admin']=="1") && ($is_update=="N")){

        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Expert Team already created you can update team only.";
        echoRespnse(201, $response);
       return;
    }

    if($matchContestDetail['is_beat_the_expert']== 'Y'){
        $contestEntryFee=$matchContestDetail['entry_fees'];
    }


    $match_unique_id = $app->request()->post('match_unique_id');
    $player_json = $app->request()->post('player_json');

     try{
		$players=json_decode($player_json,true);
		if(!is_array($players) || empty($players)){
				$response["code"] = UNABLE_TO_PROCEED;
				$response["error"] = true;
				$response["message"] = "Team can't creat without players.";
				echoRespnse(201, $response);
			   return;
		}else if(count($players)>11){
			$response["code"] = UNABLE_TO_PROCEED;
				$response["error"] = true;
				$response["message"] = "Invalid players data.";
				echoRespnse(201, $response);
			   return;
		}else{

            $player_ids=array();
            $player_positions=array();
            $player_miltiplers=array();

            foreach ($players as $key => $value) {
                if(in_array($value['player_id'], $player_ids)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }

                if(in_array($value['player_pos'], $player_positions)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }

                /*if(in_array($value['player_multiplier'], $player_miltiplers)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }*/

                $player_ids[]=$value['player_id'];
                $player_positions[]=$value['player_pos'];
                $player_miltiplers[]=$value['player_multiplier'];

            }
        }

    }catch(Exception $e) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "player_json parsing Exception.";
        echoRespnse(201, $response);
        return;

    }
    
    $customer_team_id = $app->request()->post('customer_team_id','0');
    if($is_update=='Y' && $customer_team_id==0){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "customer_team_id required for is_update=Y";
        echoRespnse(201, $response);
        return;
    }

    
    $customer_team_name = $app->request()->post('customer_team_name');
    $team_name = $app->request()->post('team_name');

	$fromadmin=1;

    $response = array();
    
    if($is_update=='Y'){
        $res=$db->update_customer_team($user_id,$match_unique_id,$customer_team_id,$players);
        $db->closeDbConnection();
        if ($res == 'UNABLE_TO_PROCEED') {
            $response["code"] = UNABLE_TO_PROCEED;
            $response["error"] = true;
            $response["message"] = "Unable to proceed your request.";
            echoRespnse(201, $response);
        }else if ($res == 'NO_MATCH_FOUND') {
            $response["code"] = NO_MATCH_FOUND;
            $response["error"] = true;
            $response["message"] = "Invalid match.";
            echoRespnse(201, $response);
        }else if ($res == 'INVALID_MATCH') {
            $response["code"] = INVALID_MATCH;
            $response["error"] = true;
            $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
            echoRespnse(201, $response);
        }else if ($res == 'TEAM_CREATION_LIMIT_EXEED') {
            $response["code"] = TEAM_CREATION_LIMIT_EXEED;
            $response["error"] = true;
            $response["message"] = "Team creation limit exeed.";
            echoRespnse(201, $response);
        }else if ($res == 'NO_RECORD') {
            $response["code"] = NO_RECORD;
            $response["error"] = true;
            $response["message"] = "No Team Found.";
            echoRespnse(201, $response);
        }else if ($res == 'TEAM_ALREADY_EXIST') {
            $response["code"] = TEAM_CREATION_LIMIT_EXEED;
            $response["error"] = true;
            $response["message"] = "Same Team Already Exist.";
            echoRespnse(201, $response);
        }else {
            $response["code"] = 0;
            $response["error"] = false;
            $response["message"] = "Team updated successfully.";
            echoRespnse(200, $response);
        }
    }else{

        if($customer_team_id==0){
            $res = $db->create_customer_team($user_id,$match_unique_id, $players,$customer_team_name,$team_name,$fromadmin);
            if ($res == 'UNABLE_TO_PROCEED') {
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Unable to proceed your request.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'NO_MATCH_FOUND') {
                $response["code"] = NO_MATCH_FOUND;
                $response["error"] = true;
                $response["message"] = "Invalid match.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'INVALID_MATCH') {
                $response["code"] = INVALID_MATCH;
                $response["error"] = true;
                $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'TEAM_CREATION_LIMIT_EXEED') {
                $response["code"] = TEAM_CREATION_LIMIT_EXEED;
                $response["error"] = true;
                $response["message"] = "Team creation limit exeed.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'TEAM_ALREADY_EXIST') {
                $response["code"] = TEAM_CREATION_LIMIT_EXEED;
                $response["error"] = true;
                $response["message"] = "Same Team Already Exist.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'CUSTOMER_TEAM_NAME_ALREADY_EXIST') {
                $response["code"] = TEAM_CREATION_LIMIT_EXEED;
                $response["error"] = true;
                $response["message"] = "Customer Team name Already Exist.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else if ($res == 'TEAM_NAME_ALREADY_EXIST') {
                $response["code"] = TEAM_CREATION_LIMIT_EXEED;
                $response["error"] = true;
                $response["message"] = "Team name Already Exist.";
                echoRespnse(201, $response);
                $db->closeDbConnection();
                return;
            }else {
                $response["code"] = 0;
                $response["error"] = false;
                $response["message"] = "Team created successfully.";
                $customer_team_id=$res['id'];
            }
        }

        $res = $db->customer_join_contest($user_id,$match_unique_id,$match_contest_id,$customer_team_id,$contestEntryFee);
        
            if ($res == 'UNABLE_TO_PROCEED') {
                $db->closeDbConnection();
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Unable to proceed your request in join contest.";
                echoRespnse(201, $response);
            }else if ($res == 'NO_MATCH_FOUND') {
                $db->closeDbConnection();
                $response["code"] = NO_MATCH_FOUND;
                $response["error"] = true;
                $response["message"] = "Invalid match  in join contest.";
                echoRespnse(201, $response);
            }else if ($res == 'INVALID_MATCH') {
                $db->closeDbConnection();
                $response["code"] = INVALID_MATCH;
                $response["error"] = true;
                $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
                echoRespnse(201, $response);
            }else if ($res == 'NO_CONTEST_FOUND') {
                $db->closeDbConnection();
                $response["code"] = NO_CONTEST_FOUND;
                $response["error"] = true;
                $response["message"] = "Invalid Contest.";
                echoRespnse(201, $response);
            }else if ($res == 'NO_TEAM_FOUND') {
                $db->closeDbConnection();
                $response["code"] = NO_TEAM_FOUND;
                $response["error"] = true;
                $response["message"] = "Invalid Team.";
                echoRespnse(201, $response);
            }else if ($res == 'TEAM_ALREADY_JOINED') {
                $db->closeDbConnection();
                $response["code"] = NO_TEAM_FOUND;
                $response["error"] = true;
                $response["message"] = "Team already joined.";
                echoRespnse(201, $response);
            }else if ($res == 'PER_USER_TEAM_ALLOWED_LIMIT') {
                $db->closeDbConnection();
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Team allowed limit exceed.";
                echoRespnse(201, $response);
            }else if ($res == 'CONTEST_FULL') {
                $db->closeDbConnection();
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Oops! Contest already full.";
                echoRespnse(201, $response);
            }else if ($res == 'LOW_BALANCE') {
                $db->closeDbConnection();
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Balance low, please recharge wallet.";
                echoRespnse(201, $response);
            }else {

                $isFakeAdminUser=$fakeUserDetail['is_admin'];
                $is_beat_the_expert=$matchContestDetail['is_beat_the_expert'];

                if($isFakeAdminUser=='1' && $is_beat_the_expert=='Y'){
                    $db->update_beat_the_expert_team($match_contest_id,$customer_team_id);
                }
                
                $db->closeDbConnection();
                $response["code"] = 0;
                $response["error"] = false;
                $response["message"] = "Contest joined successfully.";
                $response["data"] = $res['customer_detail'];
                $response["match_contest_id"] = $res['match_contest_id'];
                echoRespnse(200, $response);
            }
    }
});



/**
 * name - Get App Version Name from playstore
 * url - /get_playstore_app_version/:app_id
 * method - GET, POST
 */

$app->map('/get_playstore_app_version/:app_id',function($app_id) use ($app) {   
    try{
     $received_str= file_get_contents("https://play.google.com/store/apps/details?id=".$app_id."&hl=en");  
    }catch(Exception $e){
     $response["code"] = 1;
     $response["error"] = true;
     $response["message"] = "Play store app not found.";
     echoRespnse(201, $response);
     return;
    }  
      preg_match('/<div class="hAyfc"><div class="BgcNfc">Current Version<\/div><span class="htlgb"><div><span class="htlgb">(.*?)<\/span>/', $received_str, $match);
      $version_name=$match[1];
      $version_name=str_replace('.','',$version_name);
  
   try{  
        $version_name= number_format($version_name);
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Play store app version.";
        $response["data"] = $version_name;
        echoRespnse(200, $response);
  
    }catch(Exception $e){
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "Play store app version not found.";
        $response["data"] = $version_name;
         echoRespnse(201, $response);
    }
    
})->via('GET', 'POST');

/**
 * name - Check App Version
 * url - /check_app_version
 * method - POST
 * params - version_code(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/check_app_version', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('version_code'));
    $response = array();

    $version_code    = $app->request()->post('version_code');

    $db = new DbHandler();
    $res = $db->check_app_version($version_code,$header_device_type);
    $db->closeDbConnection();
    if ($res == 'APP_ALREADY_UPDATED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "APP ALREADY UPDATED";
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "App Detail.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});



$app->get('/get_upcoming_matches_series_en_sport', function () use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->upcoming_matches_series();

    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Series Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Series list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});


$app->get('/get_upcoming_matches_en_sport/:series_id', function ($series_id) use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->upcoming_matches($series_id);

    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});

$app->get('/get_upcoming_matches_en_sport', function () use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->upcoming_matches(0);

    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});

$app->get('/match_squade_en_sport/:series_unique_id/:match_unique_id',  function($series_unique_id,$match_unique_id) use ($app) {
    
    $response = array();

    $db = new Entitysport();
    $res = $db->match_squade_roster($series_unique_id,$match_unique_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }

});


$app->get('/match_squade_en_sport/:match_unique_id', function ($match_unique_id) use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->match_squade($match_unique_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});



$app->get('/player_finder_en_sport/:player_name', function ($player_name) use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->player_finder($player_name);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});



$app->get('/getplayer_detail_en_sport/:player_id', function ($player_id) use ($app) {


    $response = array();

    $db = new Entitysport();
    $res = $db->player_detail($player_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Matches list.";
        $response["data"] =$res;
        echoRespnse(200, $res);
    }
});


/**
 * name - Get State List
 * url - /states/:country_id
 * method - GET, POST
 * params - country_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/states/:country_id', 'getheaders',function ($country_id) use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
    $db = new DbHandler();
    $res = $db->getStates($country_id);
    $db->closeDbConnection();
    if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No states available.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "States List.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');




/**
 * name - Social login
 * url - /social_login
 * method - POST
 * params - email(mandatory), firstname(mandatory), lastname(optional), social_type(mandatory)(F=>Facebook,G=>Gplus), social_id(optional)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/social_login', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    if(!empty($email)){
		verifyRequiredParams(array('firstname', 'social_type'));
    }else{
		verifyRequiredParams(array('social_id', 'firstname', 'social_type'));
    }
    
    $response = array();
    
   $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
        $email    =$aryPostData->email;

    $firstname    =$aryPostData->firstname;
    $lastname    =$aryPostData->lastname;
    $social_type    = $aryPostData->social_type;
    $social_id    = $aryPostData->social_id;
    
    $valid_social_type=array('F','G');   

	if(!in_array($social_type, $valid_social_type)){
		$response["code"] = 8;
		$response["error"] = true;
		$response["message"] = "Invalid social_type.";
		echoRespnse(201, $response);
		return;
	}
         
    if(!empty($email)){  
    	validateEmail($email);
	}
    

    $db = new DbHandler();
    $res = $db->social_login($email,$firstname,$lastname,$social_type,$social_id,$device_id, $header_device_type,$header_device_info,$header_app_info);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'STATUS_DEACTIVATED') {
        $response["code"] = STATUS_DEACTIVATED;
        $response["error"] = true;
        $response["message"] = "User account is deactivated Please contact to admin.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Login successfully.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - Check user
 * url - /check_user
 * method - POST
 * params - username(mandatory), type(mandatory)(E=>Email,M=>Mobile)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/check_user', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;



    verifyRequiredParams(array('username', 'type'));
    $response = array();

   $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $username    = $aryPostData->username;
    $type = $aryPostData->type;


    if($type=="E"){
    validateEmail($username);
    }

    $db = new DbHandler();
    $res = $db->check_user($username,$type);
     $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "User not found.";
        echoRespnse(201, $response);
    }else if ($res == 'STATUS_DEACTIVATED') {
        $response["code"] = STATUS_DEACTIVATED;
        $response["error"] = true;
        $response["message"] = "User account is deactivated Please contact to admin.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "User Detail.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});


/**
 * name - New user
 * url - /newuser
 * method - POST
 * params - firstname(mandatory), country_mobile_code(mandatory), phone(mandatory), email(mandatory), password(mandatory), referral_code(optional)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/newuser', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('firstname','country_mobile_code', 'phone', 'password'));
    $response = array();
         $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $country_mobile_code    = $app->request()->post('country_mobile_code', '+91');
    $firstname = $aryPostData->firstname;
    $phone = $aryPostData->phone;
      $email = ($aryPostData->email)?$aryPostData->email:"";    
    $password = $aryPostData->password;    
    $referral_code  = $aryPostData->reffercode;
        $socialid  = isset($aryPostData->id)?$aryPostData->id:0;

    ///validateEmail($email);
    validatePhone($phone);

    $jsonarray['country_mobile_code']   = $country_mobile_code;
    $jsonarray['firstname']      = $firstname;   
    $jsonarray['phone']      = $phone;   
    $jsonarray['email']         = $email;
    $jsonarray['password']      = $password;    
    $jsonarray['referral_code'] = $referral_code;
   
     

    $jsonstring = json_encode($jsonarray);
   
    $db = new DbHandler();
    $res = $db->newUser($country_mobile_code, $phone, $jsonstring,$socialid);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else if ($res == 'PHONE_ALREADY_EXISTED') {
        $response["code"] = PHONE_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Phone number already exists.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_REFERRAL') {
        $response["code"] = INVALID_REFERRAL;
        $response["error"] = true;
        $response["message"] = "Referral code is invalid.";
        echoRespnse(201, $response);
    } else if ($res == 'EMAIL_ALREADY_EXISTED') {
        $response["code"] = EMAIL_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Email is already exist.";
        echoRespnse(201, $response);
    }   else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Verification code sent to ".$country_mobile_code.$phone.".";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - Verify OTP
 * url - /verifyotp
 * method - POST
 * params - otp(mandatory), type(mandatory)(V=verification, F=Forgot password, FE=Forgot password email, L=login), (If type=F then country_mobile_code(mandatory), phone(mandatory), password(mandatory)), (If type=FE then email(mandatory), password(mandatory)),  else(country_mobile_code(mandatory), phone(mandatory))
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/verifyotp', 'getheaders', function() use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();
    verifyRequiredParams(array('otp', 'type'));
 
         $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $type =$aryPostData->type;
    $otp =$aryPostData->otp;

    if($type=='F') {
        verifyRequiredParams(array('country_mobile_code', 'phone','password'));
    }if($type=='FE') {
        verifyRequiredParams(array('email','password'));
    }else{
        verifyRequiredParams(array('country_mobile_code', 'phone'));
    }

    $country_mobile_code = $app->request()->post('country_mobile_code', '+91');
      $phone = $aryPostData->phone;
    $password = $aryPostData->password;
    $email =isset($aryPostData->email)?$aryPostData->email:'';
    $device_token = isset($aryPostData->device_token)?$aryPostData->device_token:'';
    if($type=='FE'){
        validateEmail($email);
        $country_mobile_code='';
    }else{
        validatePhone($phone);
    }


    $db = new DbHandler();
    $res = $db->verifyOtp($otp, $type, $country_mobile_code, $phone, $password,$email, $device_id, $header_device_type,$header_device_info,$header_app_info,$device_token);
    $db->closeDbConnection();
    if ($res == 'INVALID_OTP') {
        $response['code'] = INVALID_OTP;
        $response['error'] = true;
        $response['message'] = "Invalid verification code.";
        echoRespnse(201, $response);
    } else if ($res == 'VERIFIED') {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Password has successfully changed.";
        echoRespnse(200, $response);
    } else if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = false;
        $response['message'] = "Unable to proceed.";
        echoRespnse(200, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer has successfully registered.";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});




$app->post('/verifyotpnew', 'getheaders', function() use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();
    verifyRequiredParams(array('otp', 'type'));
 
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $otp =$aryPostData->otp;
    $country_mobile_code = $app->request()->post('country_mobile_code', '+91');
    $phone = isset($aryPostData->phone)?$aryPostData->phone:'';
    $db = new DbHandler();

   if(isset($aryPostData->email) && $aryPostData->email!='')
   {
        $e = $aryPostData->email;
           $res = $db->getsinglerow("tbl_tempcustomers","  AND mobileno='$e' AND otp=$otp");
        
   } else{
       $res = $db->getsinglerow("tbl_tempcustomers"," AND mobileno='$phone'  AND otp=$otp");
   }
        
    if (!isset($res['id'])) {
        $response['code'] = INVALID_OTP;
        $response['error'] = true;
        $response['message'] = "Invalid verification code.";
        echoRespnse(201, $response);
    } else  {
        $response['code'] = 0;
        
        $response['error'] = false;
        if(isset($aryPostData->email) && $aryPostData->email!=''){
            $resUser = $db->getsinglerow("tbl_customers"," AND is_deleted='N' AND email='".$aryPostData->email."'");
        }else{
            $resUser = $db->getsinglerow("tbl_customers","  AND is_deleted='N'  AND phone='".$phone."'");
        }
         $response['data'] = $db->getUpdatedProfileData($resUser['id']);

    $db->closeDbConnection();
        
        $response['message'] = "OTP Verify Sccessfully";
        
        echoRespnse(200, $response);
    } 
});

/**
 * name - Forgot Password
 * url - /forgotpassword
 * method - POST
 * params - country_mobile_code(mandatory), phone(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/forgotpassword', 'getheaders', function() use ($app) {

    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();
    verifyRequiredParams(array('country_mobile_code', 'phone'));
    $country_mobile_code = $app->request()->post('country_mobile_code', '+91');
    
      $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    
    $phone = $aryPostData->phone;
    
    validatePhone($phone);

    $db = new DbHandler();
    $res = $db->forgotPassword($country_mobile_code, $phone);
  //  print_r($res);
    $db->closeDbConnection();
    if ($res == 'INVALID_MOBILE') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Mobile number does not exist.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Verification code sent to ".$country_mobile_code.$phone.".";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * name - Forgot Password Email
 * url - /forgotpassword_email
 * method - POST
 * params - email(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/forgotpassword_email', 'getheaders', function() use ($app) {

    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();
    verifyRequiredParams(array('email'));
    //die($email);
    
      $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
        $email = $aryPostData->email;

    validateEmail($email);

    $db = new DbHandler();
    $res = $db->forgotPasswordEmail($email);
    $db->closeDbConnection();
    if ($res == 'INVALID_EMAIL') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Email address does not exist.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Verification code sent to ".$email.".";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - User Login
 * url - /login
 * method - POST
 * params - email (mandatory), password (mandatory) 
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/login', 'getheaders',  function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
      $email =$aryPostData->email;
      $password = $aryPostData->password;
     verifyRequiredParams(array('password'));
   
    $response = array();
    //validateEmail($email);
    $db = new DbHandler();
    $user = $db->login($email, $password, $device_id, $header_device_type,$header_device_info,$header_app_info);
    $db->closeDbConnection();
    if ($user == 'USER_ACCOUNT_DEACTVATED') {
        $response['code'] = USER_ACCOUNT_DEACTVATED;
        $response['error'] = true;
        $response['message'] = "Customer Account Deactivated.";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_USERNAME_PASSWORD') {
        $response['code'] = INVALID_USERNAME_PASSWORD;
        $response['error'] = true;
        $response['message'] = "Invalid Email Address OR Password.";
        echoRespnse(201, $response);
    }else {
        $response['code'] = 0;
        $response["error"] = false;
        $response['message'] = 'Login successfully.';
        $response['data']= $user;
        echoRespnse(200, $response);
       
    }
});


/**
 * name - Logout
 * url - /logout
 * method - GET
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/logout', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
   
    $response = array();
    $db = new DbHandler();
    $res = $db->logout($user_id, $device_id, $header_device_type);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_USER_ACCESS') {
        $response["code"] = INVALID_USER_ACCESS;
        $response["error"] = false;
        $response["message"] = "You have loggedout successfully.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "You have loggedout successfully.";
        echoRespnse(200, $response);
    }
});


/**
 * name - Update Notification Token
 * url - /update_token
 * method - POST
 * params - device_token(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->post('/update_token', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    verifyRequiredParams(array('device_token'));    
    $device_token = $app->request()->post('device_token');
    
    $response = array();

    $db = new DbHandler();
    $user = $db->updateToken($device_id,$header_device_type,$device_token,$header_device_info,$header_app_info);
    $db->closeDbConnection();
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer Token updated successfully.";
        echoRespnse(200, $response);
    }
});


/**
 * name - Get profile
 * url - /get_profile
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_profile', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->getUpdatedProfileData($user_id);
    
    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $db->closeDbConnection();
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No profile found.";
        echoRespnse(201, $response);
    } else {
        if($header_device_type=='A' || $header_device_type=='I'){
            $db->updateCustomerAppStatus($user_id);
        }
        $db->closeDbConnection();
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "User profile.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Update Profile
 * url - /update_profile
 * method - POST
 * params - firstname(mandatory), phone(mandatory), country_mobile_code(mandatory), email(mandatory), lastname(optional), dob(optional), addressline1(optional), addressline2(optional), country(optional), state(optional), city(optional), pincode(optional)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->post('/deleteaccount', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
   
    $response = array();
    $db = new DbHandler();
    $res = $db->deleteaccount($user_id, $device_id, $header_device_type);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_USER_ACCESS') {
        $response["code"] = INVALID_USER_ACCESS;
        $response["error"] = false;
        $response["message"] = "You have Deleted successfully.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Account Deleted successfully.";
        echoRespnse(200, $response);
    }
});

$app->post('/update_profile', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('firstname','country_mobile_code', 'phone', 'email'));


    $country_mobile_code    = $app->request()->post('country_mobile_code', '+91');
    
    
    
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    
    
    $firstname = $aryPostData->firstname;
    $lastname = $aryPostData->lastname;
    $dob = $aryPostData->dob;
    $addressline1 = $aryPostData->addressline1;
    $addressline2 = '';
    $country ='IN';
    $state = $aryPostData->state;
    $city = $aryPostData->cityname;
    $phone = $aryPostData->phone;
    $email = $aryPostData->email;
    $pincode = $aryPostData->pincode;
    $gender = $aryPostData->gender;



    validateEmail($email);
    validatePhone($phone);


    $response = array();
    $db = new DbHandler();
    $res = $db->update_profile($user_id, $firstname, $lastname, $email, $phone, $country_mobile_code, $dob, $country, $state, $city, $addressline1, $addressline2,$pincode,$gender);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(200, $response);
    } else if ($res == 'EMAIL_ALREADY_EXISTED') {
        $response["code"] = EMAIL_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Email already exist.";
        echoRespnse(201, $response);
    } else if ($res == 'PHONE_ALREADY_EXISTED') {
        $response['code'] = PHONE_ALREADY_EXISTED;
        $response['error'] = true;
        $response['message'] = "Phone already exist.";
        echoRespnse(200, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer successfully updated.";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});


/**
 * name - Update And Verify Email
 * url - /update_verify_email
 * method - POST
 * params - email(mandatory), is_social(mandatory)(Y=>YES, N=>NO), social_type(optional)(F=>Facebook, G=>Gplus)(If is_social=Y then social_type mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/update_verify_email', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    verifyRequiredParams(array('email','is_social'));

    $email = $aryPostData->email;
    validateEmail($email);

    $is_social = $aryPostData->is_social;
    $social_type="";
    if($is_social=="Y"){
		verifyRequiredParams(array('social_type'));
		$social_type = $aryPostData->social_type;

		$valid_social_type=array('F','G');

		if(!in_array($social_type, $valid_social_type)){
			$response["code"] = UNABLE_TO_PROCEED;
			$response["error"] = true;
			$response["message"] = "Invalid social_type.";
			echoRespnse(201, $response);
			return;
		}
	}

    $response = array();
    $db = new DbHandler();
    $res = $db->update_verify_email($user_id, $email, $is_social, $social_type);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(200, $response);
    } else if ($res == 'EMAIL_ALREADY_EXISTED') {
        $response["code"] = EMAIL_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Email already exist with another account.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Verification link send successfully.";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});




/**
 * name - Send otp mobile
 * url - /send_otp_mobile
 * method - POST
 * params - country_mobile_code(mandatory), phone (mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/send_otp_mobile', 'getheaders', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('country_mobile_code', 'phone'));
    $response = array();

    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $country_mobile_code    = $app->request()->post('country_mobile_code', '+91');
    $phone = $aryPostData->phone;



    validatePhone($phone);
    $db = new DbHandler();
    $res = $db->send_otp_mobile($country_mobile_code, $phone, $user_id);
    $db->closeDbConnection();
    if ($res == 'PHONE_ALREADY_EXISTED') {
        $response["code"] = PHONE_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Phone number already exists with another account.";
        echoRespnse(201, $response);
    }  else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Verification code sent to ".$country_mobile_code.$phone.".";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - Update verify mobile
 * url - /update_verify_mobile
 * method - POST
 * params - otp(mandatory), type(mandatory)(SP), country_mobile_code(mandatory), phone(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/update_verify_mobile', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();
    verifyRequiredParams(array('otp', 'type', 'country_mobile_code', 'phone'));
    $otp = $app->request()->post('otp');
    $type = $app->request()->post('type');


    $country_mobile_code = $app->request()->post('country_mobile_code', '+91');
    $phone = $app->request()->post('phone');


    validatePhone($phone);

    $db = new DbHandler();
    $res = $db->update_verify_mobile($otp, $type, $country_mobile_code, $phone,$user_id);
    $db->closeDbConnection();
    if ($res == 'INVALID_OTP') {
        $response['code'] = INVALID_OTP;
        $response['error'] = true;
        $response['message'] = "Invalid verification code.";
        echoRespnse(201, $response);
    }else if ($res == 'PHONE_ALREADY_EXISTED') {
        $response["code"] = PHONE_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Phone number already exists with another account.";
        echoRespnse(201, $response);
    }else if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = false;
        $response['message'] = "Unable to proceed.";
        echoRespnse(200, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Mobile No. verified successfully.";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});


/**
 * name - Get Profile Pictures
 * url - /get_profile_pictures
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_profile_pictures', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
    $db = new DbHandler();
    $res = $db->get_profile_pictures();
    $db->closeDbConnection();


    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    } else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No picture available.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Profile picture  List.";
        $response['data']=$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Change profile picture
 * url - /change_profile_picture
 * method - POST
 * params - image(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/uploadimage', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    //verifyRequiredParams(array('image'));
     $data=file_get_contents('php://input');
    $data=json_decode($data,true);
   // print_r($data);die();
    $image = $data['image_data'];
    $fb_image ='';
    $g_image = '';
    $strFileName = time().rand().'.jpeg';
    $path = CUSTOMER_IMAGE_LARGE_PATH .$strFileName;
    $path2 = CUSTOMER_IMAGE_THUMB_PATH .$strFileName;
    file_put_contents($path,base64_decode($image));  
    file_put_contents($path2,base64_decode($image));  
    $image=$strFileName;
      
      
    if(empty($image) && empty($fb_image) && empty($g_image)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "image,fb_image,g_image atleast send one param";
        echoRespnse(201, $response);
        return;
    }
     if(!empty($g_image)){
        $fb_image=$g_image;
    }

    $response = array();
      
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Profile Picture Changed successfully.";
        $response['data'] = $image;
        $response['url'] = CUSTOMER_IMAGE_LARGE_URL;
        echoRespnse(200, $response);
    
});
 
$app->post('/change_profile_picture', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    //verifyRequiredParams(array('image'));
     $data=file_get_contents('php://input');
    $data=json_decode($data,true);
   // print_r($data);die();
    $image = $data['image_data'];
    $fb_image ='';
    $g_image = '';
$strFileName = time().rand().'.jpeg';
$path = CUSTOMER_IMAGE_LARGE_PATH .$strFileName;
$path2 = CUSTOMER_IMAGE_THUMB_PATH .$strFileName;
file_put_contents($path,base64_decode($image));  
file_put_contents($path2,base64_decode($image));  
      $image=$strFileName;
      
      
    if(empty($image) && empty($fb_image) && empty($g_image)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "image,fb_image,g_image atleast send one param";
        echoRespnse(201, $response);
        return;
    }
    $image = basename($image);
    if(!empty($g_image)){
        $fb_image=$g_image;
    }

    $response = array();
    $db = new DbHandler();
    $res = $db->change_profile_picture($user_id,$image,$fb_image);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Profile Picture Changed successfully.";
        $response['data'] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - Change Password
 * url - /change_password
 * method - POST
 * params - old_password (mandatory), password (mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/change_password', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    verifyRequiredParams(array('old_password', 'password'));
    
    
    //verifyRequiredParams(array('image'));
     $data=file_get_contents('php://input');
    $data=json_decode($data,true);
    
   
    $oldpassword = $data['old_password'];
    $password = $data['password'];
    $response = array();

    $db = new DbHandler();
    $user = $db->changePassword($user_id, $oldpassword, $password);
    $db->closeDbConnection();
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_USERNAME') {
        $response['code'] = INVALID_USERNAME;
        $response['error'] = true;
        $response['message'] = "Invalid user access.";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_OLD_PASSWORD') {
        $response['code'] = INVALID_OLD_PASSWORD;
        $response['error'] = true;
        $response['message'] = "Invalid old password.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Password successfully changed.";
        echoRespnse(200, $response);
    }
});



/**
 * name - Get refer earn
 * url - /get_refer_earn
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_refer_earn', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_refer_earn($user_id);
    $db->closeDbConnection();

	$response['code'] = 0;
	$response['error'] = false;
	$response['message'] = "Refer earn data.";
	$response["data"] =$res;
	echoRespnse(200, $response);

})->via('GET', 'POST');

/**
 * name - Get refer earn detail
 * url - /get_refer_earn_detail
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_refer_earn_detail', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_refer_earn_detail($user_id);
    $db->closeDbConnection();

	$response['code'] = 0;
	$response['error'] = false;
	$response['message'] = "Refer earn detail.";
	$response["data"] =$res;
	echoRespnse(200, $response);

})->via('GET', 'POST');


/**
 * name - Get refer earn detail cash
 * url - /get_refer_earn_detail_cash
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_refer_earn_detail_cash', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_refer_earn_detail_cash($user_id);
    $db->closeDbConnection();

    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "Refer earn cash detail.";
    $response["data"] =$res;
    echoRespnse(200, $response);

})->via('GET', 'POST');



/**
 * name - Customer Deposit amount
 * url - /customer_deposit_amount
 * method - POST
 * params - amount(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_deposit_amount', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    verifyRequiredParams(array('amount'));
    
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $amount = $aryPostData->amount;

$tempid = $aryPostData->payment_id;
    $response = array();

    $db = new DbHandler();
    $res = $db->customer_deposit_amount($user_id, $amount,$tempid);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Amount successfully Deposited.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
});


/**
 * name - Customer wallet recharge
 * url - /wallet_recharge
 * params - amount(mandatory),paymentmethod(optional)(PAYTM,RAZORPAY)(default is PAYTM), promocode(optional)
 * method - GET
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/wallet_recharge', 'authenticate' ,function() use ($app) {

    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

   // verifyRequiredParams(array('amount','paymentmethod'));
    verifyRequiredParams(array('amount'));
    $response = array();

    $amount = $app->request()->post('amount');
    $promocode = $app->request()->post('promocode','');
    $paymentmethod = $app->request()->post('paymentmethod','PAYTM');
    $paymentmethod="RAZORPAY";
    $referrer="";
    $db = new DbHandler();
    $res = $db->wallet_recharge($user_id, $amount,$paymentmethod,$referrer,$promocode);

 	$db->closeDbConnection();
    if(!empty($res['message'])){
	    $response["code"] = $res['code'];
	    $response["error"] = true;
	    $response["message"] =$res['message'];
	    echoRespnse(201, $response);
	}else{
	    echo $res['data'];
	}
});


$app->post('/apply_promo', 'authenticate' ,function() use ($app) {

    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();

    $amount = $app->request()->post('amount');
    $promocode = $app->request()->post('promocode');
    $db = new DbHandler();
    $res = $db->apply_promocode($user_id,$promocode,$amount);
 	$db->closeDbConnection();
    if(!empty($res['message'])){
	    $response["code"] = $res['code'];
	    $response["error"] = true;
	    $response["message"] =$res['message'];
	    echoRespnse(201, $response);
	}else{
	    echo $res['data'];
	}
});


/**
 * name - Customer wallet recharge razorpay
 * url - /wallet_recharge_razorpay
 * params - amount(mandatory), promocode(optional)
 * method - GET
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/wallet_recharge_razorpay', 'authenticate' ,function() use ($app) {

    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

   // verifyRequiredParams(array('amount','paymentmethod'));
    verifyRequiredParams(array('amount'));
    $response = array();

    $amount = $app->request()->post('amount');
    $promocode = $app->request()->post('promocode','');
   
   
    $referrer="";
    $db = new DbHandler();
    $res = $db->walletRechargerazorpay_new($user_id, $amount,$promocode);

    $db->closeDbConnection();
    if(!empty($res['message'])){
        $response["code"] = $res['code'];
        $response["error"] = true;
        $response["message"] =$res['message'];
        echoRespnse(201, $response);
    }else{
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] ="wallet recharge data.";
        $response["data"] =$res;
        echoRespnse(201, $response);
    }

  

   
});

/**
 * name - Customer wallet recharge web
 * url - /wallet_recharge_web?amount=10&user_id=1&paymentmethod=PAYTM&promocode=TEST123
 * params - amount(mandatory),user_id(mandatory),paymentmethod(mandatory)(PAYTM,RAZORPAY),promocode(optional)
 * method - GET, POST
 * Header Params -
 */
$app->map('/wallet_recharge_web' ,function() use ($app) {
    //echo $_SERVER['HTTP_REFERER'];
    //die;

   /* global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info; */

   // verifyRequiredParams(array('amount'));
    $response = array();

    $amount = $app->request()->get('amount');
    $user_id = $app->request()->get('user_id');
    $promocode = $app->request()->get('promocode','');
    $paymentmethod = $app->request()->get('paymentmethod','PAYTM');
    $referrer="";
    if(isset($_SERVER['HTTP_REFERER'])){
    $referrer=$_SERVER['HTTP_REFERER'];
    }
    $db = new DbHandler();
    $res = $db->wallet_recharge($user_id, $amount,$paymentmethod,$referrer,$promocode);    
    $db->closeDbConnection();
    if(!empty($res['message'])){
        $response["code"] = $res['code'];
        $response["error"] = true;
        $response["message"] =$res['message'];
        echoRespnse(201, $response);
    }else{
       echo $res['data'];
    }
})->via('GET', 'POST');



/**
 * name - Get Customer wallet detail
 * url - /get_customer_wallet_detail
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_wallet_detail', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_wallet_detail($user_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No history Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer wallet detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get Customer wallet history
 * url - /get_customer_wallet_history
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_wallet_history', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_wallet_history($user_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No wallet transaction found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer wallet history.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Customer wallet history Pages
 * url - /get_customer_wallet_history/:page_no
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_wallet_history/:page_no', 'authenticate', function ($page_no) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_wallet_history($user_id,$page_no);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No wallet transaction found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer wallet history.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get Customer wallet history filter
 * url - /get_customer_wallet_history_filter/:page_no/:type
 * method - GET, POST
 * params - :type(All, Join, Win, Refund, Deposit, Bonus, Withdraw)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_wallet_history_filter/:page_no/:type', 'authenticate', function ($page_no,$type) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
     $res = $db->get_customer_wallet_history_filter($user_id,$page_no,$type);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No wallet transaction found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer wallet history.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');




/**
 * name - Customer Withdraw amount
 * url - /customer_withdraw_amount
 * method - POST
 * params - amount(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_withdraw_amount', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('amount'));
    $response = array();
  $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $amount = $aryPostData->amount;

    $db = new DbHandler();
    $res = $db->customer_withdraw_amount($user_id,$amount);
    
    if ($res == 'UNABLE_TO_PROCEED') {
    	$db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'MIN_MAX_FAILED') {
		$settingData=$db->get_setting_data();
		$db->closeDbConnection();
		$WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
		$WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];

        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "min ".$WITHDRAW_AMOUNT_MIN." & max ".$WITHDRAW_AMOUNT_MAX." allowed per day";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_AMOUNT') {
    	$db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Invalid amount.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_DOCUMENT') {
    	$db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Documents not uploaded or approved.";
        echoRespnse(201, $response);
    } else if ($res == 'INSUFFICIENT_AMOUNT') {
    	$db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Insufficient amount.";
        echoRespnse(201, $response);
    } else {
    	$db->closeDbConnection();
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Withdraw Request submitted successfully.";

        echoRespnse(200, $response);
    }
});

$app->post('/customer_withdraw_amount_from_bank', function () use ($app) {
    
    //verifyRequiredParams(array('entry_id','action'));
    $response = array();

    $entry_id = $app->request()->post('entry_id');
    $action = $app->request()->post('action');
    $reason = $app->request()->post('reason');

    $db = new DbHandler();
    $res = $db->customer_withdraw_amount_from_bank($entry_id,$action,$reason);
    
    if ($res['error_code'] != '') {
        if ($res['error_code'] != '' && $res['error_code'] != 'NO_RECORD') {
           $db->update_entry_status_by_entry_id($entry_id,"P"); 
        }
        $db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['error_message'];
        echoRespnse(201, $response);
    }else {
        $db->closeDbConnection();
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = $res['error_message'];

        echoRespnse(200, $response);
    }
});



$app->post('/razorpay_payout_hook', function () use ($app) {


    $data=file_get_contents('php://input');
    $data=json_decode($data,true);
    $response = array();
    
    $db = new DbHandler();
    $res = $db->payout_hook($data);
    $db->closeDbConnection();
    
});



/**
 * name - Get Customer withdraw history
 * url - /get_customer_withdraw_history
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_withdraw_history', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_withdraw_history($user_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No withdraw found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer withdraw history.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');

/**
 * name - Get Customer withdraw history pages
 * url - /get_customer_withdraw_history/:page_no
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_withdraw_history/:page_no', 'authenticate', function ($page_no) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_withdraw_history($user_id,$page_no);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No withdraw found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer withdraw history.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');




/**
 * name - Customer Team Name Update
 * url - /customer_team_name_update
 * method - POST
 * params - team_name(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_team_name_update', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    verifyRequiredParams(array('teamname'));
    
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $team_name = $aryPostData->teamname;
    $state = $aryPostData->state;
    $dob = $aryPostData->dob;

    $response = array();

    $db = new DbHandler();
    $res = $db->customer_team_name_update($user_id, $team_name,$state,$dob);
    $db->closeDbConnection();
    if ($res['status'] == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res['status'] == 'TEAM_NAME_CANT_CHANGE') {
        $response["code"] = TEAM_NAME_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Team name can't change now.";
        echoRespnse(201, $response);
    }else if ($res['status'] == 'TEAM_NAME_ALREADY_EXISTED') {
        $response["code"] = TEAM_NAME_ALREADY_EXISTED;
        $response["error"] = true;
        $response["message"] = "Team name already exists.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team Name successfully updated.";
        $response["data"] =$res['data'];
        echoRespnse(200, $response);
    }
});



/**
 * name - Add PanCard
 * url - /add_pancard
 * method - POST
 * params - image(mandatory), number(mandatory), name(mandatory), dob(mandatory)(Y-m-d), state(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->post('/add_pancard', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    verifyRequiredParams(array('image','number','name','dob','state'));
    $image = (isset($aryPostData->image) && $aryPostData->image!='')?$aryPostData->image:"";
    /*$fullPath=explode("/",$image);
    $image=end($fullPath);
*/

	if($image==''){
		$response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = 'Pan Card Attachment Required';
        echoRespnse(200, $response);
        return;
	}
    $number = $aryPostData->number;
    $name = $aryPostData->name;
    $dob = $aryPostData->dob;
    $state = $aryPostData->state;

    $validDate=true;
    $date = DateTime::createFromFormat('Y-m-d', $dob);
	if ($date) {
	   $converteddate=($date -> format('Y-m-d'));
		if($dob!=$converteddate){
		 $validDate=false;
		}
	}else{
	 $validDate=false;
	}

	if(!$validDate){
		$response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = 'Invalid dob. dob formate is Y-m-d';
        echoRespnse(400, $response);
        return;
	}



    $response = array();

    $db = new DbHandler();
    $user = $db->add_pancard($user_id,$image,$number,$name,$dob,$state);
    $db->closeDbConnection();
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }elseif ($user == 'ALREADY_EXIST') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Pan Number already exist with another account.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Pan card added successfully.";
        $response['data']=$user;
        echoRespnse(200, $response);
    }
});




/**
 * name - Add Bank Detail
 * url - /add_bankdetail
 * method - POST
 * params - account_number(mandatory), name(mandatory), ifsc(mandatory), image(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->post('/add_bankdetail', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    verifyRequiredParams(array('account_number','ifsc','name'));
    
    
    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $account_number = $aryPostData->account_number;
    $ifsc = $aryPostData->ifsc;
    $name =$aryPostData->name;

   /* $image = $app->request()->post('image');
    $fullPath=explode("/",$image);
    $image=end($fullPath)*/;
$image='';
    $response = array();

    $db = new DbHandler();
    $user = $db->add_bankdetail($user_id,$account_number,$ifsc,$name,$image);
    $db->closeDbConnection();
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }elseif ($user == 'ALREADY_EXIST') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Account Number already exist with another account.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Bank detail added successfully.";
        $response['data']=$user;
        echoRespnse(200, $response);
    }
});



/**
 * name - Get notifications
 * url - /get_notifications
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_notifications', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_notifications($user_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No notification found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "notification list.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');




/**
 * name - Get notifications pages
 * url - /get_notifications/:page_no
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_notifications/:page_no', 'authenticate', function ($page_no) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_notifications($user_id,$page_no);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No notification found";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "notification list.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');

/**
 * name - Get playing history
 * url - /get_playing_history
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_playing_history', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_playing_history($user_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "playing history data.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Customer recent series leaderboard
 * url - /get_customer_recent_series_leaderboard
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_recent_series_leaderboard', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_recent_series_leaderboard($user_id);
    
    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "Recent Series List";
    $response["data"] =$res;
    echoRespnse(200, $response);
    
})->via('GET', 'POST');



/**
 * name - Get Referral Settings
 * url - /get_referral_settings
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_referral_settings', 'getheaders', function () use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_refer_cashbonus();
    $db->closeDbConnection();

    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "Settings.";
    $response["data"] =$res;
    echoRespnse(200, $response);

})->via('GET', 'POST');

/**
 * name - Get Slider
 * url - /get_slider
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_slider', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_slider();
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "Stay Tunned";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Slider list.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get upcoming matches
 * url - /get_upcoming_matches
 * method - GET, POST
 */

$app->map('/get_upcoming_matches', function () use ($app) {
  
    $response = array();

    $db = new DbHandler();
    $res = $db->get_matches("F");
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No matches Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "match list.";
        $response["data"] =$res;
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get matches
 * url - /get_matches/:match_progress
 * method - GET, POST
 * params - match_progress(mandatory)(F=>Upcoming matches, L=>Live matches, R=>Completed and Aboundent matches)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_matches/:match_progress', 'authenticate', function ($match_progress) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
    
    
    $match_progress_array=array('F','L','R');   


    if(!in_array($match_progress, $match_progress_array)){

        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Invalid Match progress flag.";
        echoRespnse(201, $response);
        return;
    }
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_matches($match_progress);
    $db->closeDbConnection();
    // if($user_id==1 || $user_id==2 || $user_id==14 || $user_id==17 || $user_id==2862){

    // }else{
    //     $res='NO_RECORD';
    // }
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No matches Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "match list.";
        $response["data"] =$res;
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match score
 * url - /get_match_score/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_match_score/:match_unique_id', function ($match_unique_id) use ($app) {

    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_score($match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Match Not Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Match score.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match players
 * url - /get_match_players/:match_id
 * method - GET, POST
 * params - match_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_players/:match_id', 'authenticate', function ($match_id) use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_players($match_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No matches Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "match Detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match Contest
 * url - /get_match_contest/:match_id/:match_unique_id
 * method - GET, POST
 * params - match_id(mandatory), match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_contest/:match_id/:match_unique_id', 'authenticate', function ($match_id,$match_unique_id) use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
   
   $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $response = array();

    $intMatchType =0;
    if(isset($aryPostData->match_type))
    {
      $intMatchType =   $aryPostData->match_type;
        
    }
    $db = new DbHandler();
    $res = $db->get_match_contest($match_id,$match_unique_id,$user_id,$intMatchType,$intMatchType);
//     echo '<pre>';
// print_r($res);


    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        $response["customerteams"] =$db->get_customer_match_teams($user_id,$match_unique_id,0);
 
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $detail=array();
        $detail['total_teams']=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
        $detail['total_joined_contest']=$db->get_match_customer_contest_count_by_match_unique_id($user_id,$match_unique_id);
        $response["beat_the_expert"] =$db->get_match_beat_the_expert_contest($match_id,$match_unique_id,$user_id);
                $response["customerteams"] =$db->get_customer_match_teams($user_id,$match_unique_id,0);
        $response["customercontest"] =$db->get_customer_match_contest($match_id,$match_unique_id,$user_id);

        $db->closeDbConnection();
        
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Contest Found.";
        $response["data"] =array();
        $response["practice"] =array();
      
        $response["detail"] =$detail;
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
		$detail=array();
		$detail['total_teams']=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
		$detail['total_joined_contest']=$db->get_match_customer_contest_count_by_match_unique_id($user_id,$match_unique_id);
        $response["beat_the_expert"] =$db->get_match_beat_the_expert_contest($match_id,$match_unique_id,$user_id);
	//	echo $match_unique_id;
		///echo  $user_id;die();
        $response["customerteams"] =$db->get_customer_match_teams($user_id,$match_unique_id,0);
        $response["customercontest"] =$db->get_customer_match_contest($match_id,$match_unique_id,$user_id);
		$db->closeDbConnection();

        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Listing.";
        $response["data"] =$res['cash'];
        $response["practice"] =$res['practice'];
           $response["team_settings"] =$res['team_settings'];
   
        $response["detail"] =$detail;
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Already Created Team Count
 * url - /get_already_created_team_count/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_already_created_team_count/:match_unique_id', 'authenticate', function ($match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "Match Team Count";
    $response["data"] =$res;
    echoRespnse(200, $response);
})->via('GET', 'POST');



/**
 * name - Get Contest Winner Breakup
 * url - /get_contest_winner_breakup/:contest_id
 * method - GET, POST
 * params - contest_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_contest_winner_breakup/:contest_id', 'authenticate', function ($contest_id) use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_contest_winner_breakup($contest_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Contest Found.".$contest_id;
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Winner Breakup Listing.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get match Contest Detail
 * url - /get_match_contest_detail/:match_contest_id/:match_unique_id
 * method - GET, POST
 * params - match_contest_id(mandatory), match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_contest_detail/:match_contest_id/:match_unique_id', 'authenticate', function ($match_contest_id,$match_unique_id) use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_contest_detail($match_contest_id,$user_id);

    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $db->closeDbConnection();
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Contest Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
		$detail=array();
		$detail['total_teams']=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
		$detail['total_joined_contest']=$db->get_match_customer_contest_count_by_match_unique_id($user_id,$match_unique_id);
        $detail['total_teams_array']=$db->get_contest_teams($user_id, $match_unique_id,$match_contest_id,$page_no=0);
 $db->closeDbConnection();

        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Detail.";
        $response["data"] =$res;
        $response["detail"] =$detail;
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match private Contest Detail
 * url - /get_match_private_contest_detail/:slug
 * method - GET, POST
 * params - slug(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_private_contest_detail/:slug', 'authenticate', function ($slug) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_private_contest_detail($slug,$user_id);

    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $db->closeDbConnection();
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($res == 'CONTEST_FULL') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Oops! Contest already full.";
        echoRespnse(201, $response);
    } else if ($res == 'NO_RECORD') {
        $db->closeDbConnection();
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Contest Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
     
        $db->closeDbConnection();
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Detail.";
        $response["data"] =$res['output'];        
        $response["match_detail"] =$res['match_detail'];        
        $response["winner_breakup"] =$res['winner_breakup'];        
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match private Contest Detail
 * url - /get_match_private_contest_detail/:slug/:match_unique_id
 * method - GET, POST
 * params - slug(mandatory), match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_private_contest_detail/:slug/:match_unique_id', 'authenticate', function ($slug,$match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_private_contest_detail($slug,$user_id,$match_unique_id);

    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $db->closeDbConnection();
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($res == 'CONTEST_FULL') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Oops! Contest already full.";
        echoRespnse(201, $response);
    } else if ($res == 'NO_RECORD') {
        $db->closeDbConnection();
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Contest Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
     
        $db->closeDbConnection();
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Detail.";
        $response["data"] =$res['output'];        
        $response["match_detail"] =$res['match_detail'];        
        $response["winner_breakup"] =$res['winner_breakup'];        
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get match Contest share Detail
 * url - /get_match_contest_share_detail/:slug
 * method - GET, POST
 * params - slug(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_contest_share_detail/:slug', 'authenticate', function ($slug) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_contest_share_detail($slug,$user_id);

   

    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $db->closeDbConnection();
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Contest Found.";
        $response["data"] =NULL;
        $response["image"] =NULL;
         $response["message"] =NULL;
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
     
        $db->closeDbConnection();
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Detail.";
        $response["data"] =$res['slug'];
        $response["image"] =$res['image'];
        $response["message"] =$res['message'];
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');

/**
 * name - Get match Contest Pdf
 * url - /get_match_contest_pdf/:match_contest_id/:match_unique_id
 * method - GET, POST
 * params - match_contest_id(mandatory), match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_contest_pdf/:match_contest_id/:match_unique_id', 'authenticate', function ($match_contest_id,$match_unique_id) use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_contest_pdf($match_contest_id,$match_unique_id,$user_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Hang on! File is in processing. Try after some time.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Pdf.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Contest Teams
 * url - /get_contest_teams/:match_unique_id/:match_contest_id
 * method - GET, POST
 * params - match_unique_id(mandatory), match_contest_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_contest_teams/:match_unique_id/:match_contest_id', 'authenticate', function ($match_unique_id,$match_contest_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_contest_teams($user_id,$match_unique_id,$match_contest_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Teams Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team list.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get Contest Teams Pages
 * url - /get_contest_teams/:match_unique_id/:match_contest_id/:page_no
 * method - GET, POST
 * params - match_unique_id(mandatory), match_contest_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_contest_teams/:match_unique_id/:match_contest_id/:page_no', 'authenticate', function ($match_unique_id,$match_contest_id,$page_no) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_contest_teams($user_id,$match_unique_id,$match_contest_id,$page_no);

    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Teams Found.";
        $response["data"] =array();
        $response["total_teams"] =$db->get_contest_teams_count($match_unique_id,$match_contest_id);
        if($page_no==1){
        	$response["admin_data"] =$db->get_beat_expert_admin_team($match_unique_id,$match_contest_id);
    	}
        $db->closeDbConnection();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team list.";
        $response["data"] =$res;
        $response["total_teams"] =$db->get_contest_teams_count($match_unique_id,$match_contest_id);
        if($page_no==1){
        	$response["admin_data"] =$db->get_beat_expert_admin_team($match_unique_id,$match_contest_id);
    	}
        $db->closeDbConnection();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');




/**
 * name - Create Customer Team
 * url - /create_customer_team
 * method - POST
 * params - match_unique_id(mandatory),player_json(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/create_customer_team', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
  ///  die('test');

    verifyRequiredParams(array('match_unique_id','player_json'));
     $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    
    $match_unique_id =$aryPostData->match_unique_id;
    $player_json = json_encode($aryPostData->player_json);

     try{
		$players=json_decode($player_json,true);
		///print_r($players);die();
		if(!is_array($players) || empty($players)){
				$response["code"] = UNABLE_TO_PROCEED;
				$response["error"] = true;
				$response["message"] = "Team can't creat without players.";
				echoRespnse(201, $response);
			   return;
		}else if(count($players)>11){
			$response["code"] = UNABLE_TO_PROCEED;
				$response["error"] = true;
				$response["message"] = "Invalid players data.";
				echoRespnse(201, $response);
			   return;
		}else{

            $player_ids=array();
            $player_positions=array();
            $player_miltiplers=array();

            foreach ($players as $key => $value) {
                if(in_array($value['player_id'], $player_ids)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }
                 if(in_array($value['player_pos'], $player_positions)) {
                   /* $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;*/
                }

                /*if(in_array($value['player_multiplier'], $player_miltiplers)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }*/

                $player_ids[]=$value['player_id'];
                $player_positions[]=$value['player_pos'];
                $player_miltiplers[]=$value['player_multiplier'];

            }
        }

    }catch(Exception $e) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "player_json parsing Exception.";
        echoRespnse(201, $response);
        return;

    }

	$customer_team_name="";
	$team_name="0";
	$fromadmin=0;

    $response = array();
    $db = new DbHandler();
    $res = $db->create_customer_team($user_id,$match_unique_id, $players,$customer_team_name,$team_name,$fromadmin);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid match.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($res == 'TEAM_CREATION_LIMIT_EXEED') {
        $response["code"] = TEAM_CREATION_LIMIT_EXEED;
        $response["error"] = true;
        $response["message"] = "Team creation limit exeed.";
        echoRespnse(201, $response);
    }else if ($res == 'TEAM_ALREADY_EXIST') {
        $response["code"] = TEAM_CREATION_LIMIT_EXEED;
        $response["error"] = true;
        $response["message"] = "Same Team Already Exist.";
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Team created successfully.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});




/**
 * name - Update Customer Team
 * url - /update_customer_team
 * method - POST
 * params - match_unique_id(mandatory), customer_team_id(mandatory), player_json(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/update_customer_team', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('match_unique_id','customer_team_id','player_json'));
  
      $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    
    $match_unique_id =$aryPostData->match_unique_id;
    $player_json = json_encode($aryPostData->player_json);
    
     $customer_team_id = $aryPostData->customer_team_id;

 
     try{
        $players=json_decode($player_json,true);
        if(!is_array($players) || empty($players)){
                $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Team can't creat without players.";
                echoRespnse(201, $response);
               return;
        }else if(count($players)>11){
            $response["code"] = UNABLE_TO_PROCEED;
                $response["error"] = true;
                $response["message"] = "Invalid players data.";
                echoRespnse(201, $response);
               return;
        }else{

            $player_ids=array();
            $player_positions=array();
            $player_miltiplers=array();

            foreach ($players as $key => $value) {
                if(in_array($value['player_id'], $player_ids)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }

              /*  if(in_array($value['player_pos'], $player_positions)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }*/

                /*if(in_array($value['player_multiplier'], $player_miltiplers)) {
                    $response["code"] = UNABLE_TO_PROCEED;
                    $response["error"] = true;
                    $response["message"] = "Invalid players data.";
                    echoRespnse(201, $response);
                   return;
                }*/

                $player_ids[]=$value['player_id'];
                $player_positions[]=$value['player_pos'];
                $player_miltiplers[]=$value['player_multiplier'];

            }
        }

    }catch(Exception $e) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "player_json parsing Exception.";
        echoRespnse(201, $response);
        return;

    }



    $response = array();
    $db = new DbHandler();
    $res = $db->update_customer_team($user_id,$match_unique_id,$customer_team_id,$players);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid match.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($res == 'TEAM_CREATION_LIMIT_EXEED') {
        $response["code"] = TEAM_CREATION_LIMIT_EXEED;
        $response["error"] = true;
        $response["message"] = "Team creation limit exeed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Team Found.";
        echoRespnse(201, $response);
    }else if ($res == 'TEAM_ALREADY_EXIST') {
        $response["code"] = TEAM_CREATION_LIMIT_EXEED;
        $response["error"] = true;
        $response["message"] = "Same Team Already Exist.";
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Team updated successfully.";
        echoRespnse(200, $response);
    }
});



/**
 * name - Get Customer match teams
 * url - /get_customer_match_teams/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_match_teams/:match_unique_id', 'authenticate', function ($match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $response = array();

    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);


    $db = new DbHandler();
    $resJoinedTeam = $db->get_customer_match_teams_joined($user_id,$match_unique_id,$aryPostData->match_contest_id);
    $res = $db->get_customer_match_teams_not_joined($user_id,$match_unique_id,$aryPostData->match_contest_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        $response["datajoined"] = $resJoinedTeam;
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Teams Found.";
         $response["data"] =array();
         $response["datajoined"] = $resJoinedTeam;
      echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Teams Listing.";
       $response["data"] =$res;
       $response["datajoined"] = $resJoinedTeam;

        echoRespnse(200, $response);
    }
    $db->closeDbConnection();

})->via('GET', 'POST');


/**
 * name - Get Customer match team detail
 * url - /get_customer_match_team_detail/:customer_team_id
 * method - GET, POST
 * params - customer_team_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_match_team_detail/:customer_team_id', 'authenticate', function ($customer_team_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_match_team_detail($customer_team_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Teams Found.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get Customer match team Stats
 * url - /get_customer_match_team_stats/:customer_team_id
 * method - GET, POST
 * params - customer_team_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_match_team_stats/:customer_team_id', 'authenticate', function ($customer_team_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_match_team_stats($customer_team_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Teams Found.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team Stats Detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Customer Pre Join contest
 * url - /customer_pre_join_contest
 * method - POST
 * params - match_unique_id(mandatory), match_contest_id(mandatory), entry_fees(optional), customer_team_ids(optional)(comma seperated)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_pre_join_contest', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('match_unique_id','match_contest_id'));


      $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $match_unique_id = $aryPostData->match_unique_id;
    $match_contest_id = $aryPostData->match_contest_id;
    $entry_fees = $aryPostData->entry_fees;
    $customer_team_ids =  $aryPostData->customer_team_ids;


    $response = array();
    $db = new DbHandler();
    $res = $db->customer_pre_join_contest($user_id,$match_unique_id,$match_contest_id,$entry_fees,$customer_team_ids);
    $db->closeDbConnection();
    if ($res['code'] == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_ENTRY_FEE') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_WALLET') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'MULTI_TEAM_NOT_ALLOWED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_CONTEST_FOUND') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'CONTEST_FULL') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'PER_USER_TEAM_ALLOWED_LIMIT') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'TEAM_ALREADY_JOINED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_TEAM_FOUND') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Contest joined detail.";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});



/**
 * name - Customer Join contest
 * url - /customer_join_contest
 * method - POST
 * params - match_unique_id(mandatory), match_contest_id(mandatory), customer_team_id(mandatory), entry_fees(optional)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_join_contest', 'authenticate', function() use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('match_unique_id','match_contest_id','customer_team_id'));
 

      $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    $match_unique_id = $aryPostData->match_unique_id;
    $match_contest_id = $aryPostData->match_contest_id;
    $entry_fees = $aryPostData->entry_fees;
    $customer_team_id =  $aryPostData->customer_team_id;
    
    
    $response = array();
    $db = new DbHandler();
    $res = $db->customer_join_contest_multi($user_id,$match_unique_id,$match_contest_id,$customer_team_id,$entry_fees);
  ///  print_r($res);die();
    $db->closeDbConnection();
    if ($res['code'] == 'UNABLE_TO_PROCEED') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_ENTRY_FEE') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_CONTEST_FOUND') {
        $response["code"] = NO_CONTEST_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'NO_TEAM_FOUND') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'TEAM_ALREADY_JOINED') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'PER_USER_TEAM_ALLOWED_LIMIT') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'CONTEST_FULL') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] =$res['msg'];
        echoRespnse(201, $response);
    }else if ($res['code'] == 'LOW_BALANCE') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = $res['msg'];
        echoRespnse(201, $response);
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] =$res['msg'];
        $response["data"] = $res['customer_detail'];
        $response["match_contest_id"] = $res['match_contest_id'];
        echoRespnse(200, $response);
    }
});


/**
 * name - Customer Switch Team
 * url - /customer_switch_team
 * method - POST
 * params - match_unique_id(mandatory), match_contest_id(mandatory), customer_team_id_old(mandatory), customer_team_id_new(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/customer_switch_team', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    
    verifyRequiredParams(array('match_unique_id', 'match_contest_id','customer_team_id_old','customer_team_id_new'));

    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);

    $match_unique_id = $aryPostData->match_unique_id;
    $match_contest_id = $aryPostData->match_contest_id;
    $customer_team_id_old = $aryPostData->customer_team_id_old;
    $customer_team_id_new = $aryPostData->customer_team_id_new;

    $response = array();

    $db = new DbHandler();
    $res = $db->customer_switch_team($user_id, $match_unique_id, $match_contest_id, $customer_team_id_old, $customer_team_id_new);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($res == 'TEAM_ALREADY_JOINED') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = "Team already joined.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team successfully Switched.";
        echoRespnse(200, $response);
    }
});



/**
 * name - Get Customer matches
 * url - /get_customer_matches/:match_progress
 * method - GET, POST
 * params - match_progress (mandatory)(F=>Upcoming matches, L=>Live matches, R=>Completed and Aboundent matches)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_matches/:match_progress', 'authenticate', function ($match_progress) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    $match_progress_array=array('F','L','R');


    if(!in_array($match_progress, $match_progress_array)){

        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Invalid Match progress flag.";
        echoRespnse(201, $response);
        return;
    }


    $response = array();


    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $activetype = 1;
    if(isset($aryPostData->active_tabs))
    {
        
      $activetype =  $aryPostData->active_tabs;
    }
    
    $db = new DbHandler();
    $res = $db->get_customer_matches($user_id,$match_progress,$activetype);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No matches Found.";
        $response["data"] =array();
        $response["server_date"] =time();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "match list.";
        $response["data"] =$res;
        $response["server_date"] =time();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get customer match Contest
 * url - /get_customer_match_contest/:match_id/:match_unique_id
 * method - GET, POST
 * params - match_id(mandatory), match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_customer_match_contest/:match_id/:match_unique_id', 'authenticate', function ($match_id,$match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;    
   
    
    $response = array();

    $db = new DbHandler();
    $res = $db->get_customer_match_contest($match_id,$match_unique_id,$user_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $db->closeDbConnection();
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {

    	$detail=array();
        $detail['total_teams']=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
        $detail['total_joined_contest']=$db->get_match_customer_contest_count_by_match_unique_id($user_id,$match_unique_id);
        
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Contest Found.";
        $response["data"] =array();
        $response["detail"] =$detail;
        $response["beat_the_expert"] =$db->get_customer_match_contest_beat_the_expret($match_id,$match_unique_id,$user_id);
        $db->closeDbConnection();
        echoRespnse(201, $response);
    } else {
        $detail=array();
        $detail['total_teams']=$db->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
        $detail['total_joined_contest']=$db->get_match_customer_contest_count_by_match_unique_id($user_id,$match_unique_id);
       

        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Contest Listing.";
        $response["data"] =$res;      
        $response["detail"] =$detail;
         $response["beat_the_expert"] =$db->get_customer_match_contest_beat_the_expret($match_id,$match_unique_id,$user_id);
        $db->closeDbConnection();
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get series by Player Statistics
 * url - /get_series_by_player_statistics/:match_unique_id/:player_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory), player_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_series_by_player_statistics/:match_unique_id/:player_unique_id', 'authenticate', function ($match_unique_id,$player_unique_id) use ($app) {
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_series_by_player_statistics($match_unique_id,$player_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
         $response["error"] = false;
        $response["message"] = "No Statistics Found.";
         $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Player Statistics.";
        $response["data"] =$res['self'];
        $response["inof"] =$res['info'];
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - Get Match Dream team detail
 * url - /get_match_dream_team_detail/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_dream_team_detail/:match_unique_id', 'authenticate', function ($match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_dream_team_detail($match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Teams Found.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');





/**
 * name - Get  match dream team Stats
 * url - /get_match_dream_team_stats/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_dream_team_stats/:match_unique_id', 'authenticate', function ($match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_dream_team_stats($match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No Teams Found.";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Team Stats Detail.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Match players stats
 * url - /get_match_players_stats/:match_unique_id
 * method - GET, POST
 * params - match_unique_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */

$app->map('/get_match_players_stats/:match_unique_id', 'authenticate', function ($match_unique_id) use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();

    $db = new DbHandler();
    $res = $db->get_match_players_stats($user_id,$match_unique_id);
    $db->closeDbConnection();
    if ($res == 'UNABLE_TO_PROCEED') {
        $response['code'] = UNABLE_TO_PROCEED;
        $response['error'] = true;
        $response['message'] = "Unable to proceed.";
        echoRespnse(201, $response);
    }else if ($res == 'NO_RECORD') {
        $response["code"] = NO_RECORD;
        $response["error"] = false;
        $response["message"] = "No Player Found.";
        $response["data"] =array();
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Player list.";
        $response["data"] =$res;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



$app->get('/paytm_status_api',function() use ($app){

$db = new DbHandler();
$paytmData=["TXNID"=>"20200302111212800110168622502736208","BANKTXNID"=>"777001508705391","ORDERID"=>"15_customer_wallet_1583129548","TXNAMOUNT"=>"100.00","STATUS"=>"TXN_SUCCESS","TXNTYPE"=>"SALE","GATEWAYNAME"=>"HDFC","RESPCODE"=>"01","RESPMSG"=>"Txn Success","BANKNAME"=>"BBK","MID"=>"UoLFyy67707338397531","PAYMENTMODE"=>"DC","REFUNDAMT"=>"0.00","TXNDATE"=>"2020-03-02 11:42:32.0","MERC_UNQ_REF"=>["return_data"=>"","promocode"=>""],"referrer"=>"","promocode"=>""];

$res = $db->paytm_status_api($paytmData);

print_r($res);die;

});

/**
 * name - paytm Payment gateway return and notify
 * url - /paytm_wallet_callback
 * method - POST
 */

$app->post('/paytm_wallet_callback', function() use ($app) {
    $data=$app->request()->post();


    $db = new DbHandler();
    $res = $db->paytm_wallet_callback($data);
    $db->closeDbConnection();



    if (isset($res['STATUS']) && ($res['STATUS'] != 'TXN_SUCCESS')) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] =$res['RESPMSG'] ;
        $response["data"] = $res;
        if(empty($res['referrer'])){
        echoRespnse(201, $response);        	
        }else{
        	formsubmit($res['referrer'],$response);
        }
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Wallet reharge successfully with amount ₹ ".$res['TXNAMOUNT'].".";
        $response["data"] = $res;
         if(empty($res['referrer'])){
        echoRespnse(200, $response);        	
        }else{
        	formsubmit($res['referrer'],$response);
        }
    }
});


/**
 * name - razorpay Payment gateway return and notify
 * url - /wallet_callback
 * method - POST
 */

$app->post('/razorpay_wallet_callback', function() use ($app) {
    $data=$app->request()->post();

    $db = new DbHandler();
    $res = $db->razorpay_wallet_callback($data);
    $db->closeDbConnection();

    if (isset($res['STATUS']) && ($res['STATUS'] != 'TXN_SUCCESS')) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] =$res['RESPMSG'] ;
        $response["data"] = $res;
        if(empty($res['referrer'])){
        echoRespnse(201, $response);        	
        }else{
        	formsubmit($res['referrer'],$response);
        }
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Wallet reharge successfully with amount ₹ ".$res['TXNAMOUNT'].".";
        $response["data"] = $res;
         if(empty($res['referrer'])){
        echoRespnse(200, $response);        	
        }else{
        	formsubmit($res['referrer'],$response);
        }
    }
});



/**
 * name - razorpay Payment gateway return and notify from app
 * url - /razorpay_wallet_callback_from_app
 * method - POST
 */

$app->post('/razorpay_wallet_callback_from_app', 'authenticate', function() use ($app) {

    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $data=$app->request()->post();
    $data['user_id']=$user_id;

    $db = new DbHandler();
    $res = $db->razorpay_wallet_callback($data);
    $db->closeDbConnection();

    if (isset($res['STATUS']) && ($res['STATUS'] != 'TXN_SUCCESS')) {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] =$res['RESPMSG'] ;
        $response["data"] = $res;
        if(empty($res['referrer'])){
        echoRespnse(201, $response);            
        }else{
            formsubmit($res['referrer'],$response);
        }
    }else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Wallet reharge successfully with amount ₹ ".$res['TXNAMOUNT'].".";
        $response["data"] = $res;
         if(empty($res['referrer'])){
        echoRespnse(200, $response);            
        }else{
            formsubmit($res['referrer'],$response);
        }
    }
});


/**
 * name - Payment gateway return and notify
 * url - /wallet_callback
 * method - POST
 */

$app->post('/wallet_callback_addcash', function() use ($app) {
    $data=$app->request()->post();
    print_r($data);
    die;

    $db = new DbHandler();
    $res = $db->wallet_callback($data);
    $db->closeDbConnection();

    if (isset($res['STATUS']) && ($res['STATUS'] != 'TXN_SUCCESS')) {

    	echo "<script> window.location.href = 'http://139.162.17.178:4600/add-cash?type=error&message=".$res['RESPMSG']."';</script>";
    	die;


    }else {
    	echo "<script> window.location.href = 'http://139.162.17.178:4600/add-cash?type=success&message=Wallet reharge successfully with amount ₹ ".$res['TXNAMOUNT']."';</script>";
    	die;

    }
});


/**
 * name - Payment gateway return and notify
 * url - /wallet_callback
 * method - POST
 */

$app->post('/wallet_callback_lowaddcash', function() use ($app) {
    $data=$app->request()->post();
    print_r($data);
    die;

    $db = new DbHandler();
    $res = $db->wallet_callback($data);
    $db->closeDbConnection();

    if (isset($res['STATUS']) && ($res['STATUS'] != 'TXN_SUCCESS')) {

        echo "<script> window.location.href = 'http://139.162.17.178:4600/Lowbalance?type=error&message=".$res['RESPMSG']."';</script>";
        die;


    }else {
        echo "<script> window.location.href = 'http://139.162.17.178:4600/Lowbalance?type=success&message=Wallet reharge successfully with amount ₹ ".$res['TXNAMOUNT']."';</script>";
        die;

    }
});


/**
 * name - get private contest setttings
 * url - /get_private_contest_settings
 * method - POST
 * params - 
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_private_contest_settings', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
   

    $response = array();

    $db = new DbHandler();
    $settingData=$db->get_setting_data();
    $PRIVATE_CONTEST_MAX_CONTEST_SIZE=$settingData['PRIVATE_CONTEST_MAX_CONTEST_SIZE'];
    $PRIVATE_CONTEST_MAX_PRIZE_POOL=$settingData['PRIVATE_CONTEST_MAX_PRIZE_POOL'];
    
    
    $db->closeDbConnection();
   
    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "settings.";
    $response['data'] = array("PRIVATE_CONTEST_MAX_CONTEST_SIZE"=>$PRIVATE_CONTEST_MAX_CONTEST_SIZE,"PRIVATE_CONTEST_MAX_PRIZE_POOL"=>$PRIVATE_CONTEST_MAX_PRIZE_POOL);
    echoRespnse(200, $response);
    
});



/**
 * name - get private contest entry fee
 * url - /get_private_contest_entry_fee
 * method - POST
 * params - contest_size(mandatory),prize_pool(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_private_contest_entry_fee', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    verifyRequiredParams(array('contest_size', 'prize_pool'));
    $contest_size = $app->request()->post('contest_size');
    $prize_pool = $app->request()->post('prize_pool');
   

    $response = array();

    $db = new DbHandler();
    $result=$db->get_private_contest_entry_fee($contest_size,$prize_pool);
    $db->closeDbConnection();

    if($result=="INVALID_PRIZE_POOL"){
     $response["code"] = UNABLE_TO_PROCEED;
    $response["error"] = true;
    $response["message"] ="Invalid prize pool." ;    
    echoRespnse(201, $response);

    }else if($result=="INVALID_CONTEST_SIZE"){
     $response["code"] = UNABLE_TO_PROCEED;
    $response["error"] = true;
    $response["message"] ="Invalid Contest size." ;   
    echoRespnse(201, $response);
    }else if($result=="INVALID_ENTRY_FEE"){
     $response["code"] = UNABLE_TO_PROCEED;
    $response["error"] = true;
    $response["message"] ="Invalid entry fee.";   
    echoRespnse(201, $response);
    }else{
    $response['code'] = 0;
    $response['error'] = false;
    $response['message'] = "Entry fees.";
    $response['data'] = $result;
    echoRespnse(200, $response);

    }
    
    
    
    
   
    
});



/**
 * name - choose winning breakup
 * url - /choose_winning_breakup
 * method - POST
 * params - contest_size(mandatory),prize_pool(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_private_contest_winning_breakup', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    verifyRequiredParams(array('contest_size', 'prize_pool'));
    $contest_size = $app->request()->post('contest_size');
    $prize_pool = $app->request()->post('prize_pool');
   

    $response = array();

    $db = new DbHandler();
    $result=$db->get_private_contest_winning_breakup($contest_size,$prize_pool);
    
    $db->closeDbConnection();

    if($result=="INVALID_PRIZE_POOL"){
         $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="Invalid prize pool." ;    
        echoRespnse(201, $response);

    }else if($result=="INVALID_CONTEST_SIZE"){
         $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="Invalid Contest size." ;   
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "winning breakups.";
        $response['data'] = $result;
        echoRespnse(200, $response);

    }
    
    
    
    
   
    
});


/**
 * name - Create private contest
 * url - /create_private_contest
 * method - POST
 * params - contest_size(mandatory),prize_pool(mandatory),match_id(mandatory),winning_breakup_id(mandatory),match_unique_id(mandatory),is_multiple(mandatory)(Y,N),team_id(mandatory),pre_join(mandatory)(Y,N)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/create_private_contest', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;


    verifyRequiredParams(array('contest_size', 'prize_pool','winning_breakup_id','match_id','match_unique_id','is_multiple','team_id','pre_join'));
    $contest_size = $app->request()->post('contest_size');
    $prize_pool = $app->request()->post('prize_pool');
    $winning_breakup_id = $app->request()->post('winning_breakup_id');
    $match_id = $app->request()->post('match_id');
    $match_unique_id = $app->request()->post('match_unique_id');
    $is_multiple = $app->request()->post('is_multiple');
    $team_id = $app->request()->post('team_id');
    $pre_join = $app->request()->post('pre_join');
   

    $response = array();

    $db = new DbHandler();
    $result=$db->create_private_contest($contest_size,$prize_pool,$winning_breakup_id,$match_id,$match_unique_id,$is_multiple,$user_id,$team_id,$pre_join);
    $db->closeDbConnection();
    
    if ($result == 'UNABLE_TO_PROCEED') {
	    $response['code'] = UNABLE_TO_PROCEED;
	    $response['error'] = true;
	    $response['message'] = "Unable to proceed.";
	    echoRespnse(201, $response);
    }else if($result=="INVALID_PRIZE_POOL"){
	     $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Invalid prize pool." ;    
	    echoRespnse(201, $response);
    }else if($result=="INVALID_CONTEST_SIZE"){
	     $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Invalid Contest size." ;   
	    echoRespnse(201, $response);
    }else if($result=="INVALID_ENTRY_FEE"){
	     $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Invalid entry fee.";   
	    echoRespnse(201, $response);
    }else if($result=="INVALID_CAT_ID"){
	     $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Invalid Private contest category id.";   
	    echoRespnse(201, $response);
    }else if($result=="INVALID_BREAKUP_ID"){
	     $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Invalid Winning breakup id.";   
	    echoRespnse(201, $response);
    }else if ($result == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid match.";
        echoRespnse(201, $response);
    }else if ($result == 'INVALID_MATCH') {
        $response["code"] = INVALID_MATCH;
        $response["error"] = true;
        $response["message"] = "The deadline has passed! Check out the contests you've joined for this match.";
        echoRespnse(201, $response);
    }else if ($result == 'INVALID_TEAM_COUNT') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "No team created.";
        echoRespnse(201, $response);
    }else if ($result == 'INVALID_WALLET') {
        $response["code"] = NO_RECORD;
        $response["error"] = true;
        $response["message"] = "Invalid Wallet.";
        echoRespnse(201, $response);
    }else if ($result == 'NO_CONTEST_FOUND') {
        $response["code"] = NO_CONTEST_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid Contest.";
        echoRespnse(201, $response);
    }else if ($result == 'NO_TEAM_FOUND') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid Team.";
        echoRespnse(201, $response);
    }else if ($result == 'TEAM_ALREADY_JOINED') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = "Team already joined.";
        echoRespnse(201, $response);
    }else if ($result == 'PER_USER_TEAM_ALLOWED_LIMIT') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Team allowed limit exceed.";
        echoRespnse(201, $response);
    }else if ($result == 'CONTEST_FULL') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Oops! Contest already full.";
        echoRespnse(201, $response);
    }else if ($result == 'LOW_BALANCE') {
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] = "Balance low, please recharge wallet.";
        echoRespnse(201, $response);
    }else{
	    $response['code'] = 0;
	    $response['error'] = false;
	    if($pre_join=='Y'){
	    	 $response['message'] = "Contest joined detail.";
             $response['data'] = $result;
	    }else{
	    	 $response['message'] = "Contest joined successfully.";
             $response['data'] = $result['customer_detail'];
             $response['match_contest_id'] = $result['match_contest_id'];
	    }
	    
	    echoRespnse(200, $response);
    }
});


/**
 * name - follow unfollow Customer
 * url - /follow_unfollow_customer
 * method - POST
 * params - following_id(mandatory),type(mandatory)(FOLLOW,UNFOLLOW)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/follow_unfollow_customer', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
     $response = array();
    $type_array=array('FOLLOW','UNFOLLOW');


    verifyRequiredParams(array('following_id','type'));
    $following_id = $app->request()->post('following_id');
    $type = $app->request()->post('type');

    if(!in_array($type, $type_array)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="type must be in FOLLOW,UNFOLLOW." ;    
        echoRespnse(201, $response);
        return;
    }
   

   

    $db = new DbHandler();
    $result=$db->follow_unfollow_customer($user_id,$following_id,$type);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        if($type=="FOLLOW"){
            $response['message'] = "Follow successfully.";
        }else{
            $response['message'] = "Unfollow successfully.";
        }
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});


/**
 * name - Get customer profile
 * url - /get_customer_profile
 * method - POST
 * params - customer_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_customer_profile', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
     $response = array();
    


    verifyRequiredParams(array('customer_id'));
    $customer_id = $app->request()->post('customer_id');  

    $db = new DbHandler();
    $result=$db->get_customer_profile($user_id,$customer_id);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Customer profile.";        
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});



/**
 * name - Get customers
 * url - /get_customers
 * method - POST
 * params - customer_id(mandatory),type(mandatory)(FL,FI), page_no(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_customers', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
    $type_array=array('FL','FI');


    verifyRequiredParams(array('customer_id','type','page_no'));
    $customer_id = $app->request()->post('customer_id');
    $page_no = $app->request()->post('page_no');
    $type = $app->request()->post('type');

    if(!in_array($type, $type_array)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="type must be in FL,FI." ;    
        echoRespnse(201, $response);
        return;
    }
   

   

    $db = new DbHandler();
    $result=$db->get_customers($user_id,$customer_id,$type,$page_no);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        if($type=="FL"){
            $response['message'] = "Followers List.";
        }else{
            $response['message'] = "Following List.";
        }
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});



/**
 * name - Create Post
 * url - /create_post
 * method - POST
 * params - team_id(mandatory),post_type(mandatory),description(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/create_post', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   


    verifyRequiredParams(array('team_id','post_type','description'));
    $team_id = $app->request()->post('team_id');
    $post_type = $app->request()->post('post_type');
    $description = $app->request()->post('description');
    $db = new DbHandler();
    $result=$db->create_post($user_id,$team_id,$post_type,$description);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else if ($result == 'NO_MATCH_FOUND') {
        $response["code"] = NO_MATCH_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid match.";
        echoRespnse(201, $response);
    }else if ($result == 'NO_TEAM_FOUND') {
        $response["code"] = NO_TEAM_FOUND;
        $response["error"] = true;
        $response["message"] = "Invalid Team.";
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Post created successfully.";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});



/**
 * name - Get reactions
 * url - /get_reactions
 * method - GET, POST
 * params - 
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_reactions', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
    $db = new DbHandler();
    $result=$db->get_reactions();
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Reaction List.";        
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');



/**
 * name - React Post
 * url - /react_post
 * method - POST
 * params - post_id(mandatory),reaction_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/react_post', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   


    verifyRequiredParams(array('post_id','reaction_id'));
    $post_id = $app->request()->post('post_id');
    $reaction_id = $app->request()->post('reaction_id');
    
    $db = new DbHandler();
    $result=$db->react_post($user_id,$post_id,$reaction_id);
   
    if($result=="UNABLE_TO_PROCEED"){
         $db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        if($result=="INSERTED"){
        	$response['message'] = "Reaction Added successfully.";
        }else if($result=="DELETED"){
        	$response['message'] = "Reaction Deleted successfully.";
        }
        
        $response['data'] = $db->get_customer_posts(0,$post_id,$user_id);
         $db->closeDbConnection();
        echoRespnse(200, $response);
    }
});



/**
 * name - Get customer posts
 * url - /get_customer_posts
 * method - POST
 * params - post_id(optional),customer_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_customer_posts', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   


    verifyRequiredParams(array('customer_id'));
    $post_id = $app->request()->post('post_id','');
    $customer_id = $app->request()->post('customer_id');
    
    $db = new DbHandler();
    $result=$db->get_customer_posts($customer_id,$post_id,$user_id);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Post Listing";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});


/**
 * name - Get customer posts user reaction
 * url - /get_customer_posts_user_reaction
 * method - POST
 * params - post_id(mandatory),reaction_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_customer_posts_user_reaction', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   


    verifyRequiredParams(array('post_id','reaction_id'));
    $post_id = $app->request()->post('post_id','');
    $reaction_id = $app->request()->post('reaction_id','-1');
    
    
    $db = new DbHandler();
    $result=$db->get_customer_posts_user_reaction($user_id,$post_id,$reaction_id);
    if($result=="UNABLE_TO_PROCEED"){
        $db->closeDbConnection();
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Post Listing";
        $response['data'] = $result;
        $response['reactions'] = $db->get_reactions_by_post_reaction($post_id);
        $db->closeDbConnection();
        echoRespnse(200, $response);
    }
});


/**
 * name - Get customer feeds
 * url - /get_customer_feeds
 * method - POST
 * params - page_no
 * Header Params - lang(mandatory), device-id(mandatory), token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_customer_feeds', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   
    verifyRequiredParams(array('page_no'));
   
    $page_no = $app->request()->post('page_no');
    
    
    
    $db = new DbHandler();
    $result=$db->get_customer_feeds($user_id,$page_no);
    $db->closeDbConnection();
    if($result=="UNABLE_TO_PROCEED"){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="unable to proceed." ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = empty($result)?"No feed found":"Post Listing";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
});

/**
 * name - Get Series
 * url - /get_series
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory),token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_series', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_series();
    $db->closeDbConnection();
    if(empty($result)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = false;
        $response["message"] ="No series" ;    
        $response['data'] = array();
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Series list";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Series Leaderboard
 * url - /get_series_leaderboard
 * method - POST
 * params - page_no(mandatory), series_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory),token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_series_leaderboard', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('page_no','series_id'));

    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $page_no = $aryPostData->page_no;
    $series_id = $aryPostData->series_id;

    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_series_leaderboard($page_no,$series_id,$user_id);
    $result_self=$db->get_series_leaderboard_self($series_id,$user_id);
    $db->closeDbConnection();

    $response["code"] = UNABLE_TO_PROCEED;
    $response["error"] = false;
    $response["message"] ="Series Leaderboard list" ;    
    $response['data'] = $result;
    $response['data_self'] = $result_self;
    echoRespnse(200, $response);
    
});

/**
 * name - Get Series Leaderboard Customer Matches
 * url - /get_series_leaderboard_customer_matches
 * method - POST
 * params - series_id(mandatory), customer_id(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory),token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/get_series_leaderboard_customer_matches', 'authenticate', function () use ($app) {
	global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('series_id','customer_id'));

    $rwws = file_get_contents('php://input');
    $aryPostData =json_decode($rwws);
    
    $series_id = $aryPostData->series_id;
    $customer_id = $aryPostData->customer_id;


    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_series_leaderboard_customer_matches($series_id,$customer_id);
    $db->closeDbConnection();

    $response["code"] = UNABLE_TO_PROCEED;
    $response["error"] = false;
    $response["message"] ="Series Leaderboard Customer Matches list" ;    
    $response['data'] = $result;
    echoRespnse(200, $response);
    
});



/**
 * name - Get App Custom Icon
 * url - /get_app_custom_icons
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_app_custom_icons', 'getheaders', function () use ($app) {

    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_app_custom_icons();
    $db->closeDbConnection();
    if(empty($result)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="No custom icon found" ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Custom icons";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Quotations
 * url - /get_quotations
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_quotations', 'getheaders', function () use ($app) {

    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_quotations();
    $db->closeDbConnection();
    if(empty($result)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="No Quotations found" ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Quotations images";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');


/**
 * name - Get Games
 * url - /get_games
 * method - GET, POST
 * Header Params - lang(mandatory), device-id(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->map('/get_games', 'getheaders', function () use ($app) {

    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    $response = array();
   
    $db = new DbHandler();
    $result=$db->get_games();
    $db->closeDbConnection();
    if(empty($result)){
        $response["code"] = UNABLE_TO_PROCEED;
        $response["error"] = true;
        $response["message"] ="No Games found" ;    
        echoRespnse(201, $response);
    }else{
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Games list";
        $response['data'] = $result;
        echoRespnse(200, $response);
    }
})->via('GET', 'POST');













/**
 * name - Apply promocode
 * url - /apply_promocode
 * method - POST
 * params - promocode(mandatory),amount(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory),token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/apply_promocode', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('promocode','amount'));
   
    $promocode = $app->request()->post('promocode');
    $amount = $app->request()->post('amount');
   


    $response = array();
   
    $db = new DbHandler();
    $result=$db->apply_promocode($user_id,$promocode,$amount);
    $db->closeDbConnection();
    if(!empty($result['message'])){
    $response["code"] = $result['code'];
    $response["error"] = true;
    $response["message"] =$result['message'];
    echoRespnse(201, $response);
    }else{
    $response["code"] = $result['code'];
    $response["error"] = false;
    $response["message"] ="Promocode applied successfully." ;    
    $response['data'] = $result['data'];
    echoRespnse(200, $response);

    }

    
});



/**
 * name - Create Customer Enquiry
 * url - /create_customer_enquiry
 * method - POST
 * params - subject(mandatory),message(mandatory)
 * Header Params - lang(mandatory), device-id(mandatory),token(mandatory), devicetype(mandatory), deviceinfo(mandatory), appinfo(mandatory)
 */
$app->post('/create_customer_enquiry', 'authenticate', function () use ($app) {
    global $user_id;
    global $lang;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;

    verifyRequiredParams(array('subject','message'));
   
    $subject = $app->request()->post('subject');
    $message = $app->request()->post('message');
   


    $response = array();
   
    $db = new DbHandler();
    $result=$db->create_customer_enquiry($user_id,$subject,$message);
    $db->closeDbConnection();
    if($result=='UNABLE_TO_PROCEED'){
	    $response["code"] = UNABLE_TO_PROCEED;
	    $response["error"] = true;
	    $response["message"] ="Unable to procced.";
	    echoRespnse(201, $response);
    }else{
	    $response["code"] = 0;
	    $response["error"] = false;
	    $response["message"] ="Query submitted successfully. Ticket No is ".$result['ticket_id'];    
	    $response['data'] = $result;
	    echoRespnse(200, $response);
    }

    
});











############# COMMON METHODS START ############################
/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
     $rwws = file_get_contents('php://input');
     $aryPostData =json_decode($rwws);
    // print_r($aryPostData);
    $request_params = (array)$aryPostData;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if(is_array($request_params[$field]) && $field=='player_json')
        {
            $request_params[$field] =json_encode($request_params[$field]);
            
        }
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }               
    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["code"] = 10;
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["code"] = 11;
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating password
 */
function validateEqualPassword($password, $confirm_password) {
    $app = \Slim\Slim::getInstance();
    if ($password != $confirm_password) {
        $response["code"] = 11;
        $response["error"] = true;
        $response["message"] = 'Password and confirm password must be same.';
        echoRespnse(400, $response);
        $app->stop();
    }    
}

/**
 * Validating phone
 */
function validatePhone($mobileno) {
    $app = \Slim\Slim::getInstance();
    $pattern = "/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i";
    if (!preg_match($pattern, $mobileno)) {
        $response["code"] = 11;
        $response["error"] = false;
        $response["message"] = 'Please enter a valid phone number.';
        echoRespnse(400, $response);
        $app->stop();
    } else {
       return true;
    }    
}

/**
 * Validating lat lng
 */
function validateValidLatLng($latitude, $longitude) {
    $app = \Slim\Slim::getInstance();
    if (($latitude < -90 || $latitude > 90)  || ($longitude < -180 || $longitude > 180)) {
        $response["code"] = INVALID_LAT_LNG;
        $response["error"] = true;
        $response["message"] = 'Please provide correct latitude & longitude.';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echo json response with status_code and response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
    $app->stop();
}

function formsubmit($action, $data) {
	$message=$data['message'];
	if($data['error']){
	$error="true";

	}else{
		$error="false";
	}
	$html="";


        $html.='<form method="get" action="'.$action.'" name="f1">
        <table border="1">
            <tbody>';       
            $html.="<input type='hidden' name='error' value='$error' id='error'>";       
            $html.="<input type='hidden' name='message' value='$message' id='message'>";       
        $html.='</tbody></table><script type="text/javascript"> document.f1.submit(); </script></form>';
        echo $html;
    
}

/**
 * Get Needed headers from here
 */
function getheaders() {
   
    $app = \Slim\Slim::getInstance();
    
    global $lang;
    global $token;
    global $device_id;
    global $header_device_type;
    global $header_device_info;
    global $header_app_info;
    if (isset($_SERVER['HTTP_TOKEN'])) {
        $token = $_SERVER['HTTP_TOKEN'];
    }

    if (isset($_SERVER['HTTP_DEVICE_ID'])) {
        $device_id = $_SERVER['HTTP_DEVICE_ID'];
    }

    if (isset($_SERVER['HTTP_LANG'])) {
        $lang = $_SERVER['HTTP_LANG'];
    }
    if (isset($_SERVER['HTTP_DEVICETYPE'])) {
        $header_device_type = $_SERVER['HTTP_DEVICETYPE'];
    }
    if (isset($_SERVER['HTTP_DEVICEINFO'])) {
        $header_device_info = $_SERVER['HTTP_DEVICEINFO'];
    }
    if (isset($_SERVER['HTTP_APPINFO'])) {
        $header_app_info = $_SERVER['HTTP_APPINFO'];
    }
    

    if (empty($lang)) {
     /*   $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header lang is missing.';
        echoRespnse(411, $response);
        $app->stop();*/
    }

    if (empty($device_id)) {
        $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header device-id is missing.';
        echoRespnse(412, $response);
        $app->stop();
    }

    if (empty($header_device_type)) {
       /* $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header devicetype is missing.';
        echoRespnse(412, $response);
        $app->stop();*/
    }

    if (empty($header_device_info)) {
        $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header deviceinfo is missing.';
        echoRespnse(412, $response);
        $app->stop();
    }

    if (empty($header_app_info)) {
        $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header appinfo is missing.';
        echoRespnse(412, $response);
        $app->stop();
    }
}
/**
 * Get needed headers and authenticate based on token
 */
function authenticate(\Slim\Route $route) {
	
    getheaders();
     global $token;
	$app = \Slim\Slim::getInstance();
    if (empty($token)) {
        $response["code"] = 412;
        $response["error"] = true;
        $response["message"] = 'Header token is missing.';
        echoRespnse(412, $response);
        $app->stop();
    }

    $db = new DbHandler();
    $user = $db->validateUser($token);
    $db->closeDbConnection();



	if (!empty($user)) {
		global $user_id;
		$user_id = $user["id"];                        
		$status = $user["status"];  
		$is_deleted = $user["is_deleted"];  
		if ($status != 'A') {
			$response["code"] = 412;
			$response["error"] = true;
			$response["message"] = 'Account is deactivated.';
			echoRespnse(412, $response);
			$app->stop();
		} else if ($is_deleted == 'Y') {
			$response["code"] = 412;
			$response["error"] = true;
			$response["message"] = 'Account is deleted by admin.';
			echoRespnse(412, $response);
			$app->stop();
		}                   
	} else {
		//$res->header('WWW-Authenticate', sprintf('Basic="%s"', $realm));
		$response["code"] = 401;
		$response["error"] = true;
		$response["message"] = 'User not found.';
		echoRespnse(401, $response);
		$app->stop();
	}
   
}

############# COMMON METHODS END ############################
$app->run();
?>
