<?php
$path = $_SERVER['HTTP_HOST'];
// $server_scheme=$_SERVER['REQUEST_SCHEME'];
date_default_timezone_set('Asia/Kolkata');
 if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $server_scheme = 'https';
 }else {
    $server_scheme = 'https';
 }

  define('SUBDIR', str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__)).'/');

  define('DB_USERNAME', 'my11option');
  define('DB_PASSWORD', 'mcb96J0*2');
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'my11option1');

define('APP_NAME',"my11option");
define('SITE_TITLE', APP_NAME); 

define('ROOT_DIRECTORY',$_SERVER['DOCUMENT_ROOT'] . SUBDIR);

define('APIURL', $server_scheme.'://'.$path.SUBDIR."apis/mobile/v1/");
define('APIURL_KB', $server_scheme.'://'.$path.SUBDIR."apis/mobile_kb/v1/");
define('APIURL_SOCCER', $server_scheme.'://'.$path.SUBDIR."apis/mobile_soccer/v1/");

define('CRON_SERVER', APIURL);
define('CRON_SERVER_KB', APIURL_KB);
define('CRON_SERVER_SOCCER', APIURL_SOCCER);
define('SUBDIR_IMAGE', '/uploaded/');
define('APKURL', "https://play.google.com/store/apps/details?id=my11option.com");
define('IOSAPKURL', "https://apps.apple.com/in/app/my11option-fantasy-app/id6446179891");
define('APK_DOWNLOAD_URL', "https://play.google.com/store/apps/details?id=my11option.com");



define('APP_URL', $server_scheme.'://'.$path . SUBDIR);
define('WEBSITE_URL', $server_scheme.'://'.$path .SUBDIR);
define('WEBSITE_URL_SHOW', $server_scheme.'://'.$path.dirname(SUBDIR));
define('WEBSITE_URL_IMAGE', $server_scheme.'://'.$path .SUBDIR_IMAGE);

define('NO_IMG_URL', APP_URL.'img/noimage.png');
define('NO_IMG_URL_TEAM', APP_URL.'img/no_image_team.png');
define('NO_IMG_URL_PLAYER', APP_URL.'img/no_image_player.png');

define('GOOGLE_MAP_API_KEY', '');


define('GOOGLE_API_KEY_ADMIN','AIzaSyC7pPpglQ76JUi9A01Dr0CdujgGFEG1mX8'); // FOR google APIS



define('WK_PDF_PATH',ROOT_DIRECTORY."lib/wkhtmltopdf/vendor/autoload.php");
define('CASHFREE_PAYOUT_FILES',ROOT_DIRECTORY."lib/cashfreepayout/cfpayout.inc.php");
define('RAZORPAY_PAYOUT_FILES',ROOT_DIRECTORY."lib/razorpaypayout/rppayout.inc.php");
define('WK_BINARY_PATH',ROOT_DIRECTORY."lib/wkhtmltopdf/binary/bin/wkhtmltopdf");
define('DOM_PDF_PATH',ROOT_DIRECTORY."lib/dompdf/dompdf_config.inc.php");
define('PAYPAL_LIB_PATH',ROOT_DIRECTORY."lib/Paypal/autoload.php");
define('TWILIO_LIB_PATH',ROOT_DIRECTORY."lib/Twilio/autoload.php");
define('EWAY_LIB_PATH',ROOT_DIRECTORY."lib/eWAY/RapidAPI.php");
define('PHPMAILER_LIB_PATH', ROOT_DIRECTORY."lib/phpmailer/vendor/autoload.php");
define('PHPMAILER_LIB_PATH_URL', ROOT_DIRECTORY."lib/phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php");
define('PUSHER_LIB_PATH', ROOT_DIRECTORY."lib/Pusher/Pusher.php");
define('STRIPE_LIB_PATH',ROOT_DIRECTORY."lib/stripe/init.php");
define('FACEBOOK_LIB_PATH',ROOT_DIRECTORY."lib/facebook/vendor/autoload.php");
define('GOOGLE_LIB_PATH',ROOT_DIRECTORY."lib/google/google-login-api.php");

// Pics URL
define('CATEGORY_PIC_URL',APP_URL."uploaded/category_pic/");
define('CATEGORY_PIC_PATH',ROOT_DIRECTORY."uploaded/category_pic/");



// Pics SIZE
define('WIDTH_LARGE', 400);
define('WIDTH_LARGE_ITEM', 1024);
define('WIDTH_LARGE_BANNER', 2500);
define('WIDTH_THUMB', 200);

define('DS', '/');
// define('FILES_UPLOAD_DIR', ROOT_DIRECTORY . '/uploaded/');
// define('FILES_UPLOAD_URL', APP_URL . '/uploaded/');



//FCM KEYS FOR ALL APPS
define('FIRE_BASE_URL', 'https://fcm.googleapis.com/fcm/send');

//define('FCM_KEY', 'AIzaSyDTQLt9XtRPGhRnGNXO_D8NNMWAQgG4abk');
define('FCM_KEY', 'AAAA5qbLisc:APA91bFgWSpLMzNkWM-Sc-HTLH1sVyx-7V-s_NSLRLWwwENs5h2MJtyQbFoqgOhB1s9SBf_aY5wC7QKukTzX4GIV6aSFnj6dDhVhHjYVCh4pYH6-9_n0rfmjsUNJUwXa0PSytC0GG4aN');
// Configurations for BACKEND
define('HTTP_PATH', $server_scheme.'://' . $path . SUBDIR);
define('FRONT_CSS_PATH', 'css/front/');
define('FRONT_IMG_PATH', 'img/front/');
define('FRONT_JS_PATH', 'js/front/');
define('TAG_LINE', ' - ');
define('FORM_EMAIL', 'noreply@my11option.com');



define('REFERRAL_CODE_INITIAL', 'BNB-');
define('REFERRAL_AMOUNT_REFERRALER', '25');
define('REFERRAL_AMOUNT_APPLIER', '20');


define('GLOBAL_TWILIO_NUMBER', '');
define('GLOBAL_TWILIO_SID', '');
define('GLOBAL_TWILIO_TOKEN', '');
define('REFERRAL_INITIAL', 'TT11-');

//phpmailer SMTP settings
defined('SMTP_SERVER') or define('SMTP_SERVER', 'smtp.sendgrid.net');
defined('SMTP_USERNAME') or define('SMTP_USERNAME', 'apikey');
defined('SMTP_PASSWORD') or define('SMTP_PASSWORD', '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');
defined('SMTP_PORT') or define('SMTP_PORT', 587);
defined('SMTP_SECURE') or define('SMTP_SECURE', 'tls');//ssl or tls
defined('SMTP_EMAIL') or define('SMTP_EMAIL', 'noreply@my11option.com');
defined('SMTP_FROM_NAME') or define('SMTP_FROM_NAME', SITE_TITLE.' SUPPORT');
defined('SMTP_FROM_EMAIL') or define('SMTP_FROM_EMAIL', 'noreply@my11option.com');

//withdraw amount setting
defined('WITHDRAW_AMOUNT_MIN') or define('WITHDRAW_AMOUNT_MIN', '200');
defined('WITHDRAW_AMOUNT_MAX') or define('WITHDRAW_AMOUNT_MAX', '200000');


define('SMS_USERNAME', 'my11option@gmail.com');
define('SMS_PASSWORD', '@my11option');

define('SMS_SENDER_NAME', "my11option");
define('SMS_KEY', '');




//some allowed file types and size
defined("ALLOWED_IMAGE_TYPES") or define("ALLOWED_IMAGE_TYPES", "gif|jpg|png|jpeg");
defined("ALLOWED_DOC_TYPES") or define("ALLOWED_DOC_TYPES", "doc|docx|pdf");
defined("ALLOWED_FILE_SIZE") or define("ALLOWED_FILE_SIZE", 2048);//in KB


//IMAGE upload type
define('AWS_LIB', ROOT_DIRECTORY."lib/s3/vendor/autoload.php");

// 1.LOCAL 2.BUCKET
defined("IMAGE_UPLOAD_TYPE") or define("IMAGE_UPLOAD_TYPE", "LOCAL");
//to be used when you have to check if file exists or not and bucket is the type. because in this case we won't check if remote file is available.
$check_image_exists = IMAGE_UPLOAD_TYPE=="BUCKET";
defined("CHECK_IMAGE_EXISTS") or define("CHECK_IMAGE_EXISTS", $check_image_exists);


//amazon s3 aws settings
defined('AWS_KEY') or define('AWS_KEY', 'AKIATOSAZXBKCIX2RWVO');
defined('AWS_SECRET') or define('AWS_SECRET', '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');
defined('AWS_REGION') or define('AWS_REGION', 'ap-south-1');
defined('AWS_BUCKET') or define('AWS_BUCKET', 'my11option');
//AMAZON S3 BUCKET FILE SETTINGS
defined('AWS_URL') or define('AWS_URL', 'https://'.AWS_BUCKET.'.s3.amazonaws.com/');



include_once(ROOT_DIRECTORY."file_upload_constants.php");


//Add by KK
define("CRICAPI_APIKEY", ""); //KK
//define("CRICAPI_APIKEY", "BJrrWfpPz9b4G6z4rtJDXxtXWe72"); //AV
define("CRICAPI_MATCHES", "https://cricapi.com/api/matches?apikey=".CRICAPI_APIKEY);
define("CRICAPI_PLAYERS", "https://cricapi.com/api/playerFinder?apikey=".CRICAPI_APIKEY);
define("CRICAPI_MATCHE_PLAYER", "https://cricapi.com/api/fantasySquad?apikey=".CRICAPI_APIKEY);
define("CRICAPI_PLAYER_STATISTICS", "https://cricapi.com/api/playerStats?apikey=".CRICAPI_APIKEY);

define("CRICAPI_MATCH_SUMMARY", "https://cricapi.com/api/fantasySummary?apikey=".CRICAPI_APIKEY);
define("CRICAPI_MATCH_SCORE", "https://cricapi.com/api/cricketScore?apikey=".CRICAPI_APIKEY);



define("ENTITYSPORT_APIKEY", "95800   1ad5a13747223eaa3caeebf02cd"); //KK
define("ENTITYSPORT_MATCHE_PLAYER", "https://rest.entitysport.com/v2/matches/");
define("ENTITYSPORT_MATCHE_PLAYER_SQUAD", "https://rest.entitysport.com/v2/competitions/{SERIES_ID}/squads/{MATCH_ID}?token=".ENTITYSPORT_APIKEY);
define("ENTITYSPORT_PLAYER", "https://rest.entitysport.com/v2/players/");

define("ENTITYSPORT_MATCHES", APIURL."get_upcoming_matches_en_sport");
define("ENTITYSPORT_MATCHES_SERIES", APIURL."get_upcoming_matches_series_en_sport");
define("ENTITYSPORT_MATCHE_PLAYERS", APIURL."match_squade_en_sport/");
define("ENTITYSPORT_PLAYER_DETAIL", APIURL."getplayer_detail_en_sport/");
define("ENTITYSPORT_PLAYER_FINDER", APIURL."player_finder_en_sport/");
define("CUSTOMER_WITHDRAW_REQUEST_URL", APIURL."customer_withdraw_amount_from_bank");



define("ENTITYSPORT_APIKEY_KB", ""); //KK
define("ENTITYSPORT_MATCHES_KB", "https://rest.entitysport.com/kabaddi/matches/?status=1&token=".ENTITYSPORT_APIKEY_KB);/*status=1&*/
define("ENTITYSPORT_MATCH_INFO_KB", "https://rest.entitysport.com/kabaddi/matches/{MATCH_ID}/info?token=".ENTITYSPORT_APIKEY_KB);
define("ENTITYSPORT_PLAYER_INFO_KB", "https://rest.entitysport.com/kabaddi/player/{PLAYER_ID}/profile?token=".ENTITYSPORT_APIKEY_KB);
define("ENTITYSPORT_PLAYER_KB", "https://rest.entitysport.com/kabaddi/players?search={PLAYER_NAME}&token=".ENTITYSPORT_APIKEY_KB);
define("ENTITYSPORT_FANTASY_SUMMARY_KB", "https://rest.entitysport.com/kabaddi/matches/{MATCH_ID}/stats?token=".ENTITYSPORT_APIKEY_KB);



define("ENTITYSPORT_MATCHES_API_KB", APIURL_KB."get_upcoming_matches_en_sport_kb");
define("ENTITYSPORT_MATCHES_API_SERIES_KB", APIURL_KB."get_upcoming_matches_series_en_sport_kb");
define("ENTITYSPORT_MATCHE_PLAYERS_KB", APIURL_KB."match_squade_en_sport_kb/");
define("ENTITYSPORT_PLAYER_DETAIL_KB", APIURL_KB."getplayer_detail_en_sport_kb/");
define("ENTITYSPORT_PLAYER_FINDER_KB", APIURL_KB."player_finder_en_sport_kb/");
define("CREATE_ADMIN_CUSTOMER_TEAM_KB", APIURL_KB."create_admin_customer_team_join_contest");



define("ENTITYSPORT_APIKEY_SOCCER", ""); //KK
define("ENTITYSPORT_MATCHES_SOCCER", "https://rest.entitysport.com/soccer/matches/?order=asc&status=1&token=".ENTITYSPORT_APIKEY_SOCCER);/*status=1&*/
define("ENTITYSPORT_MATCH_INFO_SOCCER", "https://rest.entitysport.com/soccer/matches/{MATCH_ID}/info?token=".ENTITYSPORT_APIKEY_SOCCER);
define("ENTITYSPORT_MATCH_FANTASY_INFO_SOCCER", "https://rest.entitysport.com/soccer/matches/{MATCH_ID}/fantasy?token=".ENTITYSPORT_APIKEY_SOCCER);



define("ENTITYSPORT_PLAYER_INFO_SOCCER", "https://rest.entitysport.com/soccer/player/{PLAYER_ID}/profile?token=".ENTITYSPORT_APIKEY_SOCCER);
define("ENTITYSPORT_PLAYER_SOCCER", "https://rest.entitysport.com/soccer/players?search={PLAYER_NAME}&token=".ENTITYSPORT_APIKEY_SOCCER);
define("ENTITYSPORT_FANTASY_SUMMARY_SOCCER", "https://rest.entitysport.com/soccer/matches/{MATCH_ID}/statsv2?token=".ENTITYSPORT_APIKEY_SOCCER);



define("ENTITYSPORT_MATCHES_API_SOCCER", APIURL_SOCCER."get_upcoming_matches_en_sport_soccer");
define("ENTITYSPORT_MATCHES_API_SERIES_SOCCER", APIURL_SOCCER."get_upcoming_matches_series_en_sport_soccer");
define("ENTITYSPORT_MATCHE_PLAYERS_SOCCER", APIURL_SOCCER."match_squade_en_sport_soccer/");
define("ENTITYSPORT_PLAYER_DETAIL_SOCCER", APIURL_SOCCER."getplayer_detail_en_sport_soccer/");
define("ENTITYSPORT_PLAYER_FINDER_SOCCER", APIURL_SOCCER."player_finder_en_sport_soccer/");
define("CREATE_ADMIN_CUSTOMER_TEAM_SOCCER", APIURL_SOCCER."create_admin_customer_team_join_contest");








defined("WALLET_TYPE") or define("WALLET_TYPE", serialize(array("deposit_wallet"=>"Deposit Wallet", "winning_wallet"=>"Winning Wallet","bonus_wallet"=>"Bonus Wallet")));

defined("MULTIPLIER_ARRAY") or define("MULTIPLIER_ARRAY", serialize(array(2,1.5,1,1,1,1,1,1,1,1,1)));
defined("MULTIPLIER_ARRAY") or define("MULTIPLIER_ARRAY_KB", serialize(array(2,1.5,1,1,1,1,1)));

defined("PLAYER_POSITIONS") or define("PLAYER_POSITIONS", serialize(['Allrounder','Batsman','Bowler','Wicketkeeper']));
defined("PLAYER_POSITIONS_KB") or define("PLAYER_POSITIONS_KB", serialize(['Allrounder','Raider','Defender']));
defined("PLAYER_POSITIONS_SOCCER") or define("PLAYER_POSITIONS_SOCCER", serialize(['Midfielder','Defender','Forward','Goalkeeper']));

defined("PROFILE_PICTURES") or define("PROFILE_PICTURES", serialize(['dp1.png','dp2.png','dp3.png','dp4.png','dp5.png','dp6.png','dp7.png','dp8.png','dp9.png','dp10.png','dp11.png','dp12.png']));


defined("PLAYER_BETS") or define("PLAYER_BETS", serialize(['Left Handed Bet','Right Handed Bet']));
defined("PLAYER_BOWLS") or define("PLAYER_BOWLS", serialize(['Right-arm','Left-arm']));


defined('UNABLE_TO_PROCEED') or define('UNABLE_TO_PROCEED', 0);
defined('NO_RECORD') or define('NO_RECORD', 1);
defined('STATUS_DEACTIVATED') or define('STATUS_DEACTIVATED', 2);

defined('PHONE_ALREADY_EXISTED') or define('PHONE_ALREADY_EXISTED', 3);
defined('INVALID_REFERRAL') or define('INVALID_REFERRAL', 4);
defined('EMAIL_ALREADY_EXISTED') or define('EMAIL_ALREADY_EXISTED', 5);
defined('INVALID_OTP') or define('INVALID_OTP', 6);
defined('INVALID_MOBILE') or define('INVALID_MOBILE', 7);

defined('USER_ACCOUNT_DEACTVATED') or define('USER_ACCOUNT_DEACTVATED', 8);

defined('INVALID_USERNAME_PASSWORD') or define('INVALID_USERNAME_PASSWORD', 9);

defined('INVALID_USER_ACCESS') or define('INVALID_USER_ACCESS', 10);

defined('NO_MATCH_FOUND') or define('NO_MATCH_FOUND', 11);



defined('TEAM_CREATION_LIMIT_EXEED') or define('TEAM_CREATION_LIMIT_EXEED', 12);

defined('NO_CONTEST_FOUND') or define('NO_CONTEST_FOUND', 13);

defined('NO_TEAM_FOUND') or define('NO_TEAM_FOUND', 14);

defined('INVALID_USERNAME') or define('INVALID_USERNAME', 15);

defined('INVALID_OLD_PASSWORD') or define('INVALID_OLD_PASSWORD', 16);
defined('TEAM_NAME_ALREADY_EXISTED') or define('TEAM_NAME_ALREADY_EXISTED', 17);

defined('INVALID_MATCH') or define('INVALID_MATCH', 111);

defined('BONUS_WALLET_PER') or define('BONUS_WALLET_PER', 10);

defined("MATCH_PROGRESS") or define("MATCH_PROGRESS", serialize(array('F'=>'Fixture','L'=>'Live','IR'=> "In Review",'R'=>'Result','AB'=>'Abandoned')));

defined("WITHRAWALS_STATUS") or define("WITHRAWALS_STATUS", serialize(array('P'=>'Pending','C'=>'Confirm','R'=>'Rejected','H'=>'Proceessed','RP'=>'Processing')));
defined("IS_USE_RECHARGE") or define("IS_USE_RECHARGE", serialize(array("S"=>"Single", "M"=>"Multiple")));
defined("CASH_BONUS_TYPE") or define("CASH_BONUS_TYPE", serialize(array("F"=>"Fixed", "P"=>"Percentages")));


defined("MIME_TYPES") or define("MIME_TYPES", serialize(array("pdf"=>"application/pdf","jpg"=>"image/jpeg", "jpeg"=>"image/jpeg", "gif"=>"image/gif", "png"=>"image/png", "doc"=>"application/msword", "docx"=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document", "pdf"=>"application/pdf")));

define('PAYMENT_GETWAY', 'PAYTM');
define('CURRENCY_SYMBOL', '₹');


define('PAYMENT_GETWAY_CURRENCY', 'INR');
define('PAYMENT_GETWAY_RETURN_URL', APIURL.'wallet_callback');


define('PAYTM_PAYMENT_GETWAY_RETURN_URL', APIURL.'paytm_wallet_callback');
define('KB_PAYTM_PAYMENT_GETWAY_RETURN_URL', APIURL_KB.'paytm_wallet_callback');
define('SOCCER_PAYTM_PAYMENT_GETWAY_RETURN_URL', APIURL_SOCCER.'paytm_wallet_callback');
define('RAZORPAY_PAYMENT_GETWAY_RETURN_URL', APIURL.'razorpay_wallet_callback');
define('KB_RAZORPAY_PAYMENT_GETWAY_RETURN_URL', APIURL_KB.'razorpay_wallet_callback');
define('SOCCER_RAZORPAY_PAYMENT_GETWAY_RETURN_URL', APIURL_SOCCER.'razorpay_wallet_callback');


//define('PAYMENT_GETWAY_NOTIFY_URL', APIURL.'wallet_callback');

define('PAYMENT_GETWAY_MODE', "LIVE"); //TEST,LIVE

if(PAYMENT_GETWAY_MODE=="TEST"){
  define('PAYMENT_GETWAY_URL', "https://test.cashfree.com/billpay/checkout/post/submit");
  define('PAYMENT_GETWAY_SECRET_KEY', '');
  define('PAYMENT_GETWAY_APP_ID', '');
}else{
  define('PAYMENT_GETWAY_URL', "https://www.cashfree.com/checkout/post/submit");
  define('PAYMENT_GETWAY_SECRET_KEY', '996001945802d7c9c8db25f8000699');
  define('PAYMENT_GETWAY_APP_ID', 'c5d7b6e446d32eff6ad783e56656c4b8172d1769');
}


//this is for paytm files
define('PAYTM_FILES_FIRST',ROOT_DIRECTORY."lib/paytm/lib/config_paytm.php");
define('PAYTM_FILES_SECOND',ROOT_DIRECTORY."lib/paytm/lib/encdec_paytm.php");



define("CONTEST_CASH_BONUS_USED_TYPE", serialize(array('P'=>'Percentage','F'=>'Fixed')));

define('RAZORPAY_FILES',ROOT_DIRECTORY."lib/razorpay/vendor/autoload.php");

if(PAYMENT_GETWAY_MODE=="TEST"){
define('RAZORPAY_KEY', 'ads');
define('RAZORPAY_SECRET', 'dsa');
}else{
define('RAZORPAY_KEY', 'das');
define('RAZORPAY_SECRET', 'das');
}

define('DEFAULT_CURRENCY', "INR");



define('CASHFREE_PAYOUT_CLIENT_ID', 'CF191604CBOLJN7CM6H5GVH11E1G');
define('CASHFREE_PAYOUT_CLIENT_SECRET', '8753394d926a322175eb694ec4da092667480a01');
define('CASHFREE_PAYOUT_STAGE', 'TEST');

define('RAZORPAY_PAYOUT_CLIENT_ID', 'xyz');
define('RAZORPAY_PAYOUT_CLIENT_SECRET', 'dpm');
//define('RAZORPAY_PAYOUT_ACCOUNT_NUMBER', 'dsdsds');
define('RAZORPAY_PAYOUT_ACCOUNT_NUMBER', 'dsdsd');
define('RAZORPAY_PAYOUT_MODE', 'IMPS');

//define('PAYOUT_GETWAY', 'RAZORPAY');
define('PAYOUT_GETWAY', 'DIRECT');



 define('MID_KEY','e8f29bd0-1a6c-44de-9fc6-039c59733471');
 define('KEY_INDEX','1');
define('MID','MY11OPTIONONLINE');
define('API_HOST','https://api.phonepe.com/apis/hermes');




?>
