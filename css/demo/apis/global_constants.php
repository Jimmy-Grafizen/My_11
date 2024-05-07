<?php
$path = $_SERVER['HTTP_HOST'];

if($_SERVER['SERVER_NAME'] == 'localhost'){
  define('SUBDIR', '/ls/rest_el/');

  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', 'password');
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'rest_el');
} else {
  define('SUBDIR', '/demo/');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', 'MysqlDb#123@');
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'elguero2_52');
}

define('ROOT_DIRECTORY',$_SERVER['DOCUMENT_ROOT'] . SUBDIR);
define('APIURL', 'http://www.'.$_SERVER['HTTP_HOST'].SUBDIR);
define('SUBDIR_IMAGE', '/uploaded/');

define('APP_URL', 'http://'.$_SERVER['HTTP_HOST'] . SUBDIR);
define('WEBSITE_URL', 'http://'.$_SERVER['HTTP_HOST'] .SUBDIR);
define('WEBSITE_URL_IMAGE', 'http://'.$_SERVER['HTTP_HOST'] .SUBDIR_IMAGE);

//PUSHER details
define('PUSHER_KEY','7ebe1204ab2734e4ab02');
define('PUSHER_SECRET','043dd5004ba7b17bb789');
define('PUSHER_APP_ID','443970');

define('GOOGLE_MAP_API_KEY', 'AIzaSyDqmjpfp9WoBkyfMMUmVo39GyGz1q0nB50');

//google api key
define('GOOGLE_API_KEY_DELIVERYMAN','AIzaSyC7pPpglQ76JUi9A01Dr0CdujgGFEG1mX8'); // FOR google APIS
define('GOOGLE_API_KEY_CUSTOMER','AIzaSyC7pPpglQ76JUi9A01Dr0CdujgGFEG1mX8'); // FOR google APIS
define('GOOGLE_API_KEY_ADMIN','AIzaSyC7pPpglQ76JUi9A01Dr0CdujgGFEG1mX8'); // FOR google APIS

//this is global site setting from database table --- global_settings and site_settings
define('GLOBAL_SITE_SETTING_PHP',ROOT_DIRECTORY."apis/global_site_setting.php");

//this is lib paths

define('DOM_PDF_PATH',ROOT_DIRECTORY."apis/lib/dompdf/dompdf_config.inc.php");
define('PAYPAL_LIB_PATH',ROOT_DIRECTORY."apis/lib/Paypal/autoload.php");
define('TWILIO_LIB_PATH',ROOT_DIRECTORY."apis/lib/Twilio/autoload.php");
define('EWAY_LIB_PATH',ROOT_DIRECTORY."apis/lib/eWAY/RapidAPI.php");
define('PHPMAILER_LIB_PATH', ROOT_DIRECTORY."apis/lib/phpmailer/vendor/autoload.php");
define('PUSHER_LIB_PATH', ROOT_DIRECTORY."apis/lib/Pusher/Pusher.php");
define('STRIPE_LIB_PATH',ROOT_DIRECTORY."apis/lib/stripe/init.php");

// Pics URL
define('CATEGORY_PIC_URL',APP_URL."uploaded/category_pic/");
define('CATEGORY_PIC_PATH',ROOT_DIRECTORY."uploaded/category_pic/");

define('MENU_PIC_URL',APP_URL."uploaded/item_pic/");
define('MENU_PIC_PATH',ROOT_DIRECTORY."uploaded/item_pic/");

define('ADMIN_PIC_URL',APP_URL."uploaded/admin_pic/");
define('ADMIN_PIC_PATH',ROOT_DIRECTORY."uploaded/admin_pic/");

define('DELIVERYMAN_PIC_URL',APP_URL."uploaded/deliveryman_pic/");
define('DELIVERYMAN_PIC_PATH',ROOT_DIRECTORY."uploaded/deliveryman_pic/");

define('CUSTOMER_PIC_URL',APP_URL."uploaded/customer_pic/");
define('CUSTOMER_PIC_PATH',ROOT_DIRECTORY."uploaded/customer_pic/");

define('EXTRA_PIC_URL',APP_URL."uploaded/extra_pic/");
define('EXTRA_PIC_PATH',ROOT_DIRECTORY."uploaded/extra_pic/");

define('BRANCH_PIC_URL',APP_URL."uploaded/branch_pic/");
define('BRANCH_PIC_PATH',ROOT_DIRECTORY."uploaded/branch_pic/");

define('PROMOTION_PIC_URL',APP_URL."uploaded/promotion_pic/");
define('PROMOTION_PIC_PATH',ROOT_DIRECTORY."uploaded/promotion_pic/");

define('NOTIFICATION_PIC_URL',APP_URL."uploaded/notification_pic/");
define('NOTIFICATION_PIC_PATH',ROOT_DIRECTORY."uploaded/notification_pic/");

// Pics SIZE
define('WIDTH_LARGE', 400);
define('WIDTH_THUMB', 200);

if (!defined('SETTING_FILE_PATH')) {
    define("SETTING_FILE_PATH", ROOT_DIRECTORY . '/settings.php');
}

define('FILES_UPLOAD_DIR', ROOT_DIRECTORY . '/uploaded/');
define('FILES_UPLOAD_URL', APP_URL . '/uploaded/');

// this is for app current setting
define('SITE_CURRENCY', '$');

// branch search area radius in KM
define('BRANCH_SEARCH_AREA', '30');

// this is for paypal billing currency
define('PAYPAL_CURRENCY_CODE', 'USD');

// PAYMENT GATEWAY CONSTANTS START
define('PAYMENT_GATEWAY_CURRENCY_CODE', 'USD');
define('ACTIVATED_PAYMENT_GATEWAY', 'STRIPE');  // STRIPE / PAYPAL
define('ACTIVATED_PAYMENT_GATEWAY_MODE', 'LIVE');  // LIVE / SANDBOX

define('PAYPAL_CLIENT_ID_SANDBOX','ASFYrGw3ccB910eEVOveV9emEuXk-6KsKDtqciw3haYVD34oLwHU6T-ipQP-Jiqsahkt9ofp9B8C99G3');
define('PAYPAL_SECRET_ID_SANDBOX','EMCHl5SVa2X9q-W8TFnBRyzVUqPAxahgoVI3740fv7hh_adl_wFd1u-yfDbDYqTxXiFaHVNOFXGKO48p');

define('PAYPAL_CLIENT_ID_LIVE','ASFYrGw3ccB910eEVOveV9emEuXk-6KsKDtqciw3haYVD34oLwHU6T-ipQP-Jiqsahkt9ofp9B8C99G3');
define('PAYPAL_SECRET_ID_LIVE','EMCHl5SVa2X9q-W8TFnBRyzVUqPAxahgoVI3740fv7hh_adl_wFd1u-yfDbDYqTxXiFaHVNOFXGKO48p');

define('STRIPE_SECRET_KEY_TEST', 'sk_test_Dmci1tlm38mjcsCyDDcp2nOt');
define('STRIPE_SECRET_KEY_LIVE', 'sk_live_WL7I4D4TBqz0525IxbMCywEq');

if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
  if (ACTIVATED_PAYMENT_GATEWAY_MODE=='SANDBOX') {
    define('STRIPE_SECRET_KEY', STRIPE_SECRET_KEY_TEST);
  } else{
    define('STRIPE_SECRET_KEY', STRIPE_SECRET_KEY_LIVE);
  }
} else {
  if (ACTIVATED_PAYMENT_GATEWAY_MODE=='SANDBOX') {
    define('PAYPAL_CLIENT_ID', PAYPAL_CLIENT_ID_SANDBOX);
    define('PAYPAL_SECRET_ID', PAYPAL_SECRET_ID_SANDBOX);
  } else{
    define('PAYPAL_CLIENT_ID', PAYPAL_CLIENT_ID_LIVE);
    define('PAYPAL_SECRET_ID', PAYPAL_SECRET_ID_LIVE);
  }
}
// PAYMENT GATEWAY CONSTANTS END

//FCM KEYS FOR ALL APPS
define('FIRE_BASE_URL', 'https://fcm.googleapis.com/fcm/send');
define('FCM_KEY_FOR_DELIVERYMAN', 'AIzaSyDBRXWXX6F_yB7m5SJlOBNN1vcVsifP5iw');
define('FCM_KEY_FOR_BRANCH', 'AIzaSyDBRXWXX6F_yB7m5SJlOBNN1vcVsifP5iw');
define('FCM_KEY_FOR_CUSTOMER', 'AIzaSyDBRXWXX6F_yB7m5SJlOBNN1vcVsifP5iw');

// Configurations for BACKEND
define('HTTP_PATH', 'http://' . $path . SUBDIR);
define('FRONT_CSS_PATH', 'css/front/');
define('FRONT_IMG_PATH', 'img/front/');
define('FRONT_JS_PATH', 'js/front/');
define('SITE_TITLE', 'Resta EL'); 
define('TAG_LINE', ' - ');
define('FORM_EMAIL', 'esales@rest_el.com');

//INVOICE ID ADDITIONAL LETTERS AND LENGTH
define('INVOICE_INITIALS', 'EL-');
define('ORDER_CANCELLATION_DURATION', 300); // seconds

define('REFERRAL_CODE_INITIAL', 'EL-');
define('REFERRAL_AMOUNT_REFERRALER', '25');
define('REFERRAL_AMOUNT_APPLIER', '20');

define('AMOUNT_TO_BE_SPENT_FOR_LOYALTY_POINTS', 100); // IN USD
define('LOYALTY_POINTS_EANRNED', 2); // IN USD
define('LOYALTY_POINT_VALUE', 0.5); // IN USD

//TWILIO SETTING (LIVE)
define('GLOBAL_TWILIO_NUMBER', '+16193044377');
define('GLOBAL_TWILIO_SID', 'AC795fd75d5f173b93de81bb5cb3334fdd');
define('GLOBAL_TWILIO_TOKEN', '466e9333fd65f03ae077bb6e49a524de');

//phpmailer SMTP settings
defined('SMTP_SERVER') or define('SMTP_SERVER', 'smtp.office365.com');
defined('SMTP_USERNAME') or define('SMTP_USERNAME', 'sales@elguero2.com');
defined('SMTP_PASSWORD') or define('SMTP_PASSWORD', 'Un68@nou');
defined('SMTP_PORT') or define('SMTP_PORT', 587);
defined('SMTP_SECURE') or define('SMTP_SECURE', 'tls');//ssl or tls
defined('SMTP_EMAIL') or define('SMTP_EMAIL', 'sales@elguero2.com');
defined('SMTP_FROM_NAME') or define('SMTP_FROM_NAME', 'EL-GUERO2 SUPPORT');
defined('SMTP_FROM_EMAIL') or define('SMTP_FROM_EMAIL', 'sales@elguero2.com');

//API RECORDS COUNT
defined('DELIVERY_APP_API_RECORDS_COUNT') or define('DELIVERY_APP_API_RECORDS_COUNT', 10);
defined('BRANCH_APP_API_RECORDS_COUNT') or define('BRANCH_APP_API_RECORDS_COUNT', 10);
?>
