<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once '../include/DbConnect.php';
require_once '../include/DbHandler.php';
require '../../../../lib/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$user_id = NULL;

/**
 * Pusher android
 * url - /pusher_auth_private
 * method - POST
 */
$app->post('/pusher_auth_private', function() use ($app) {
    verifyRequiredParams(array('channel_name'));
    $db = new DbHandler();
    $db->includePusherLib();
    
    $channel_name = $app->request()->post('channel_name');
    $socket_id = $app->request()->post('socket_id');
    $pusher = new Pusher(PUSHER_KEY, PUSHER_SECRET, PUSHER_APP_ID);
    $reply = $pusher->socket_auth($channel_name, $socket_id);
    echo $reply;
    die;
});

$app->get('/testmail', function() use ($app) {
	$response = array();	
	$db = new DbHandler();
	$res=$db->sendSMTPMail('test subject', 'test message', 'manoj.sharma.guy@gmail.com', 'manoj sharma', 'EL GUERO2', 'sales@elguero2.com');
	print_r($res);
	exit;
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Branch List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Nearby Branches List
 * url - /branches/:lat/:lng
 * method - GET
 */
$app->get('/branches/:lat/:lng', function($lat, $lng) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getBranches($lat, $lng);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Branch List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get categories List
 * url - /categories/:branch_id
 * method - GET
 */
$app->get('/categories/:branch_id', function($branch_id) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getCategories($branch_id);	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$taxes=$db->getTaxes($branch_id);
		$settings=$db->getSettings($branch_id);
		$catering=$db->getCatering($branch_id);
		if(!$taxes)
			$taxes=array();
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Categories List";
		$response['data'] = $res;
		$response['taxes']=$taxes;
		$response['settings']=$settings;
		$response['catering']=$catering;
		echoRespnse(200, $response);
	}
});

/**
 * Get countries List
 * url - /countries
 * method - GET
 */
$app->get('/countries', function() use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getCountries();	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Countries List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get featured List -- I
 * url - /featured/:branch_id
 * method - GET
 */
$app->get('/featured/:branch_id', function($branch_id) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getFeatured($branch_id, 0);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Featured Menu List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get featured List -- II
 * url - /featured/:branch_id/:customer_id
 * method - GET
 * customer_id : default 0
 */
$app->get('/featured/:branch_id/:customer_id', function($branch_id, $customer_id) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getFeatured($branch_id, $customer_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Featured Menu List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/** 
 * Get menu item List  -- I
 * url - /menus/:catid/:textsearch/:is_new/:is_nonveg/:pageno
 * method - GET
 * DEFAULT VALUES => catid=0; textsearch=0; is_new=0; is_nonveg=0; pageno=0;
 * is_new => Y / N
 * is_nonveg => Y / N
 */
$app->get('/menus/:catid/:textsearch/:is_new/:is_nonveg/:pageno', function($catid, $textsearch, $is_new, $is_nonveg, $pageno) use ($app) {
	$response = array();	
	$db = new DbHandler();
	$res=$db->getMenus(0, $catid, $textsearch, $is_new, $is_nonveg, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['pages'] = 0;
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$total_pages = $db->getMenuPages($catid, $textsearch, $is_new, $is_nonveg);
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Menu List";
		$response['pages'] = $total_pages;
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/** 
 * Get menu item List -- II
 * url - /menus/:customer_id/:branch_id/:catid/:textsearch/:is_new/:is_nonveg/:pageno
 * method - GET
 * DEFAULT VALUES => catid=0; branch_id=0; textsearch=0; is_new=0; is_nonveg=0; pageno=0; customer_id=0 default
 * is_new => Y / N
 * is_nonveg => Y / N
 */
$app->get('/menus/:customer_id/:branch_id/:catid/:textsearch/:is_new/:is_nonveg/:opageno', function($customer_id, $branch_id, $catid, $textsearch, $is_new, $is_nonveg, $pageno) use ($app) {
	$response = array();	
	$db = new DbHandler();
	$res=$db->getMenus($customer_id, $branch_id, $catid, $textsearch, $is_new, $is_nonveg, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['pages'] = 0;
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$total_pages = $db->getMenuPages($catid, $textsearch, $is_new, $is_nonveg);
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Menu List";
		$response['pages'] = $total_pages;
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get menu item details -- I
 * url - /menu/:menuid
 * method - GET
 */
$app->get('/menu/:menuid', function($menuid) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getMenuDetails($menuid, 0);	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Item details";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get menu item details -- II
 * url - /menu/:menuid/:customerid
 * method - GET
 * default : 0
 */
$app->get('/menu/:menuid/:customerid', function($menuid, $customerid) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getMenuDetails($menuid, $customerid);	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Item details";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});


/**
 * Get menu item attributes
 * url - /menuattributes/:menuid
 * method - GET
 */
$app->get('/menuattributes/:menuid', function($menuid) use ($app) {
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getMenuAttributes($menuid, '');	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Menu attributes List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});
 
/**
 * New Customer Registration
 * url - /newuser
 * method - POST
 * params - mobile (mandatory), mobile_code(mandatory), email(optional)
 */
$app->post('/newuser', function () use ($app) {
    verifyRequiredParams(array('mobile', 'mobile_code'));
    $response = array();
    $mobile = $app->request->post('mobile');
    $mobile_code = ($app->request()->post('mobile_code')=='') ? '+91' : $app->request()->post('mobile_code');
    $email = $app->request->post('email');
    
    $db = new DbHandler();
    $res = $db->newUser($mobile, $mobile_code, $email);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed";
        echoRespnse(201, $response);
    } else if ($res == 'PHONE_ALREADY_EXISTED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "Phone no already exist";
        echoRespnse(201, $response);
    } else if ($res == 'EMAIL_ALREADY_EXISTED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Email already exist";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response['message'] = "OTP sent to $mobile_code-$mobile";
        $response['data']['mobile_code'] = $mobile_code;
        $response['data']['mobile'] = $mobile;
        $response['data']['otp'] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Verify OTP
 * url - /verifyotp
 * method - POST
 * params - otp(mandatory), type(mandatory), mobile(mandatory), password(optional), mobile_code(optional)
 * type -> V / F : V -> Customer verificatin, F -> Forgot Password
 */
$app->post('/verifyotp', function() use ($app) {
    verifyRequiredParams(array('otp', 'type', 'mobile', 'mobile_code'));
    $otp = $app->request()->post('otp');
    $type = $app->request()->post('type');
    $mobile = $app->request()->post('mobile');
    $password = $app->request()->post('password');
    $mobile_code = ($app->request()->post('mobile_code')=='') ? '+91' : $app->request()->post('mobile_code');
    if($type=='F') {
        verifyRequiredParams(array('password'));
    }

    $response = array();
    $db = new DbHandler();
    $user = $db->verifyOtp($mobile, $otp, $type, $password, $mobile_code);
    if ($user == 'INVALID_OTP') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Entered OTP is invalid";
        echoRespnse(201, $response);
    } else if ($user == 'USER_NOT_EXIST') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Mobile No. is not registered";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Successfully Verified";
        echoRespnse(200, $response);
    }
});

/**
 * Resend OTP
 * url - /resendotp
 * method - POST
 * params - mobile(mandatory), type(mandatory), mobile_code(mandatory)
 */
$app->post('/resendotp', function() use ($app) {
    verifyRequiredParams(array('mobile', 'type', 'mobile_code'));
    $mobile = $app->request()->post('mobile');
    $type = $app->request()->post('type');
    $mobile_code = ($app->request()->post('mobile_code')=='') ? '+91' : $app->request()->post('mobile_code');
    
    $response = array();
    $db = new DbHandler();
    $otp = $db->resendOtp($mobile, $type, $mobile_code);
    if ($otp == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Invalid mobile no";
        echoRespnse(201, $response);
    }  else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "OTP sent to $mobile_code-$mobile";
        $response['data']['mobile_code'] = $mobile_code;
        $response['data']['mobile'] = $mobile;
        $response['data']['otp'] = $otp;
        echoRespnse(200, $response);
    }
});

/**
 * Forgot Password
 * url - /forgotpassword
 * method - POST
 * params - mobile(mandatory), mobile_code(optional)
 */
$app->post('/forgotpassword', function() use ($app) {
    verifyRequiredParams(array('mobile', 'mobile_code'));
    $mobile = $app->request()->post('mobile');
    $mobile_code = ($app->request()->post('mobile_code')=='') ? '+91' : $app->request()->post('mobile_code');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->forgotPassword($mobile, $mobile_code);
    if ($user == 'VERIFICATION_PENDING') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Verification is pending";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_MOBILE') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Mobile No. not exist";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "OTP sent to $mobile_code-$mobile";
        $response['data']['mobile_code'] = $mobile_code;
        $response['data']['mobile'] = $mobile;
        $response['data']['otp'] = $user;
        echoRespnse(200, $response);
    }
});

/**
 * User Registration final step
 * url - /newuserstep2
 * method - POST
 * params - firstname(mandatory), lastname(mandatory), email(mandatory), password(mandatory), mobile(mandatory), mobile_code(mandatory) gender(optional), dob(optional), device_id(mandatory), device_token(mandatory), device_type(A-> Android;I->Iphone mandatory), ipaddress(mandatory), referral_code(optional)
 * gender -> M / F
 * dob -> yyyy/mm/dd
 */
$app->post('/newuserstep2', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('firstname', 'lastname', 'email', 'password', 'mobile', 'mobile_code', 'device_id', 'device_token', 'device_type', 'ipaddress'));
    // reading post params
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $email = $app->request->post('email');
    $password = $app->request->post('password');
    $mobile = $app->request->post('mobile');
    $mobile_code = ($app->request()->post('mobile_code')=='') ? '+91' : $app->request()->post('mobile_code');
    $gender = $app->request()->post('gender');
    $dob = $app->request()->post('dob');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $device_token = $app->request()->post('device_token');
    $ipaddress = $app->request()->post('ipaddress');
    $referral_code = $app->request()->post('referral_code');

    validateEmail($email);
    $response = array();
    $db = new DbHandler();
    $res = $db->newUserStep2($firstname, $lastname, $email, $password, $mobile, $mobile_code, $gender, $dob, $device_id, $device_type, $device_token, $ipaddress, $referral_code);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Unable to proceed";
        echoRespnse(201, $response);
    } else if ($res == 'EMAIL_ALREADY_EXISTED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "Email is already exist";
        echoRespnse(201, $response);
    } else if ($res == 'PHONE_ALREADY_EXISTED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Mobile No is already exist";
        echoRespnse(201, $response);
    } else if ($res == 'NOT_VERIFIED_IN_TEMP') {
        $response["code"] = 4;
        $response["error"] = true;
        $response["message"] = "Mobile No. is not Verified";
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_REFERRAL_CODE') {
        $response["code"] = 5;
        $response["error"] = true;
        $response["message"] = "Please enter valid referral code";
        echoRespnse(201, $response);
    }  else if ($res == 'REFERRAL_DEACTIVATED') {
        $response["code"] = 6;
        $response["error"] = true;
        $response["message"] = "Referral program is deactived now";
        echoRespnse(201, $response);
    } else {
        $referralData=$db->getReferralerData();
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Customer successfully registered";
        $response["data"] = $res;
        $response["data"]["referral"] = $referralData;
        echoRespnse(200, $response);
    }
});

/**
 * Customer Login
 * url - /login
 * method - POST
 * params - username (mandatory), password (mandatory), device_id(mandatory), device_token(mandatory), device_type(mandatory), ipaddress(mandatory)
 * username  -  email / mobile
 * devide_type -> A / I -> A -> Android, I -> Iphone
 */
$app->post('/login', function() use ($app) {                          
	verifyRequiredParams(array('username', 'password', 'device_id', 'device_token', 'device_type', 'ipaddress'));     	
	// reading post params
    $username = $app->request->post('username');   
    $password = $app->request->post('password');   
	$device_id = $app->request->post('device_id');
	$device_token = $app->request->post('device_token');
	$device_type = $app->request->post('device_type');
	$ipaddress = $app->request->post('ipaddress');

	$response = array();
	$db = new DbHandler(); 
	$res = $db->login($username, $password, $device_id, $device_token, $device_type, $ipaddress);
	if ($res == 'INVALID_USERNAME_PASSWORD') { 
		$response["code"] = 1;
		$response["error"] = true;
		$response["message"] = "Username OR Password is invalid";
		echoRespnse(201, $response);
	} else if ($res == 'ACCOUNT_DEACTVATED' || $res == 'ACCOUNT_DEACTVATED_BY_CUSTOMER') {  
		$response["code"] = 2;
		$response["error"] = true;
		$response["message"] = "Account deactivated, Please contact to admin";
		echoRespnse(201, $response);
	} else if ($res == 'USERNAME_NOT_EXIST') {  
		$response["code"] = 3;
		$response["error"] = true;
		$response["message"] = "User not exist";
		echoRespnse(201, $response);   
	} else {
		$referralData=$db->getReferralerData();
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Login Successfully."; 
		$response["data"] = $res;
		$response["data"]["referral"] = $referralData;
		echoRespnse(200, $response);
	}
});

/**
 * Check email -- when social media
 * url - /sociallogin
 * method - POST
 * params - email (mandatory), firstname (mandatory), lastname (optional), device_id(mandatory), device_token(mandatory), device_type(A-> Android;I->Iphone mandatory), loginfrom(mandatory) -- (G, F), ipaddress(mandatory)
 */
$app->post('/sociallogin', function () use ($app) {
    verifyRequiredParams(array('email', 'firstname', 'device_id', 'device_token', 'device_type', 'loginfrom', 'ipaddress'));
    $email = $app->request()->post('email');
    $firstname = $app->request()->post('firstname');
    $lastname = $app->request()->post('lastname');
    $device_id = $app->request()->post('device_id');
    $device_token = $app->request()->post('device_token');
    $device_type = $app->request()->post('device_type');
    $loginfrom = $app->request()->post('loginfrom');
    $ipaddress = $app->request()->post('ipaddress');

    $response = array();
    $db = new DbHandler();
    $user = $db->socialLogin($email, $firstname, $lastname, $device_id, $device_token, $device_type, $loginfrom, $ipaddress);
    $referralData=$db->getReferralerData();
    if ($user == 'USER_NOT_FOUND') {
        $data['firstname'] = $firstname;
        $data['lastname'] = $lastname;
        $data['email'] = $email;
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "User not registered";
        $response['data']= $data;
        $response['data']["referral"] = $referralData;
        echoRespnse(201, $response);
    } else if ($user['status'] == 'USER_ACCOUNT_DEACTVATED' || $user['status'] == 'ACCOUNT_DEACTVATED_BY_CUSTOMER') {
       $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Account deactivated, Please conatct to admin";
        $response['data'] = $user;
        $response['data']["referral"] = $referralData;
        echoRespnse(201, $response);
    } else {
		$response['code'] = 0;
		$response["error"] = false;
		$response['message'] = 'Login Successfully.';
		$response['data']= $user;
		$response['data']["referral"] = $referralData;
		echoRespnse(200, $response);
    }
});

/**
 * Change Password
 * url - /changepassword
 * method - POST
 * params - oldpassword (mandatory), newpassword (mandatory)
 * header Params - username (mandatory), password (mandatory)
 * username -> email / mobile
 */
$app->post('/changepassword', 'authenticate', function () use ($app) {
    global $user_id;
    verifyRequiredParams(array('oldpassword', 'newpassword'));
    $oldpassword = $app->request()->post('oldpassword');
    $newpassword = $app->request()->post('newpassword');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->changePassword($user_id, $oldpassword, $newpassword);
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Unable to proceed your request";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_OLD_PASSWORD') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Invalid old password";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Password successfully changed";
        echoRespnse(200, $response);
    }
});

/**
 * Update Profile Pic
 * url - /updateprofilepic
 * method - POST
 * params - profile_pic(mandatory)
 * Header Params - username (mandatory), password (mandatory)
 */
$app->post('/updateprofilepic', 'authenticate', function () use ($app) {
    global $user_id;
    $db = new DbHandler();
    if (isset($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['name'] != '') {
        $ex = explode(".", $_FILES['profile_pic']['name']);
        $extentation_profile_pic = strtolower(end($ex));
        if (!in_array($extentation_profile_pic, $db->image_extensions)) {
            $response["code"] = 1;
            $response["error"] = true;
            $response["message"] = "Please upload a " . implode("or ", $db->image_extensions) . " file";
            echoRespnse(201, $response);
            exit;
        } else {
            $signature_profile_pic = $user_id.'_'.time() . '_profile_pic.' . $extentation_profile_pic;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], CUSTOMER_PIC_PATH.'large/' . $signature_profile_pic)) {
                
                resize($signature_profile_pic, WIDTH_THUMB, CUSTOMER_PIC_PATH.'large/', CUSTOMER_PIC_PATH.'thumb');
                
				$res = $db->updateImage($user_id, $signature_profile_pic);
				if ($res == 'UNABLE_TO_PROCEED') {
					$response["code"] = 2;
					$response["error"] = true;
					$response["message"] = "Unable to proceed.";
					echoRespnse(201, $response);
				} else {
					$response["code"] = 0;
					$response["error"] = false;
					$response["message"] = "Profile Pic successfully updated";
					$response['data'] = $res;
					echoRespnse(200, $response);
				}
            }
        }
    } else {
		$response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Please select Profile pic to upload.";
        echoRespnse(201, $response);
	}
});

/**
 * Update customer profile
 * url - /updateprofile
 * method - POST
 * params - firstname(mandatory), lastname(optional), gender(optional), dob(optional), email(mandatory)
 * gender -> M / F
 * dob -> yyyy/mm/dd
 * Header params - username (mandatory), password (mandatory)
 */
$app->post('/updateprofile', 'authenticate', function () use ($app) {
	global $user_id;
    verifyRequiredParams(array('firstname', 'email'));
    // reading post params
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $gender = $app->request()->post('gender');
    $dob = $app->request()->post('dob');
    $email = $app->request()->post('email');

    $response = array();
    $db = new DbHandler();
    $res = $db->updateProfile($user_id, $firstname, $lastname, $gender, $dob, $email);
    
    if ($res == 'DUPLICATE_EMAIL') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Email Already exist with another customer";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Profile successfully Updated";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Add / Update customer Address
 * url - /address
 * method - POST
 * params - firstname(mandatory), lastname(optional), mobile_code(mandatory), mobile(mandatory), address(mandatory), latitude(mandatory), longitude(mandatory), address_id(mandatory)
 * addreess_id deafult 0
 * Header params - username (mandatory), password (mandatory)
 */
$app->post('/address', 'authenticate', function () use ($app) {
	global $user_id;
    verifyRequiredParams(array('firstname', 'mobile_code', 'mobile', 'address', 'latitude', 'longitude', 'address_id'));
    // reading post params
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $mobile_code = $app->request()->post('mobile_code');
    $mobile = $app->request()->post('mobile');
    $address = $app->request->post('address');
    $latitude = $app->request()->post('latitude');
    $longitude = $app->request()->post('longitude');
    $address_id = $app->request()->post('address_id');

    $response = array();
    $db = new DbHandler();
    $res = $db->addAddress($user_id, $firstname, $lastname, $mobile_code, $mobile, $address, $latitude, $longitude, $address_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        if($address_id>0) {
			$response["message"] = "Address successfully Updated";
		} else {
			$response["message"] = "Address successfully Added";
		}
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get address List
 * url - /address
 * method - GET
 * Header params - username (mandatory), password (mandatory)
 */
$app->get('/address', 'authenticate', function() use ($app){
	global $user_id;
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getAddresses($user_id);	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Categories List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Delete customer address
 * url - /address/:address_id
 * method - DELETE
 * Header params - username (mandatory), password (mandatory)
 */
$app->delete('/address/:address_id', 'authenticate', function($address_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    global $user_id;
    $res = $db->deleteAddress($user_id, $address_id);
    if ($res=='UNABLE_TO_DELETE') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] ='Unable to delete selected address';
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Address successfully deleted";
        echoRespnse(200, $response);
    }
});

/**
 * Get address details
 * url - /address/:address_id
 * method - GET
 * Header params - username (mandatory), password (mandatory)
 */
$app->get('/address/:address_id', 'authenticate', function($address_id) use ($app){
	global $user_id;
	$response = array();	
	$db = new DbHandler();	
	$res=$db->getAddressesById($user_id, $address_id);	
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = true;
		$response['message'] = "No Record Found";
		echoRespnse(201, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Address List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Add credit card
 * url - /card
 * method - POST
 * params - firstname(mandatory), card_number(mandatory), expiry_month(mandatory), expiry_year(mandatory), cvn(mandatory), card_type(mandatory), isdefault(mandatory)
 * card_type -> VISA / MASTER (NOT DEFINED any string value up to 20 characters)
 * isdefault -> YES  / NO, deafult 'NO'
 * Header Params - username (mandatory), password (mandatory)
 */
$app->post('/card', 'authenticate', function () use ($app) {
    global $user_id;
    // check for required params
    verifyRequiredParams(array('firstname',  'card_number',  'expiry_month',  'expiry_year',  'cvn',  'card_type',  'isdefault'));
    $response = array();

    // reading post params
    $firstname = $app->request->put('firstname');
    $card_number = $app->request->put('card_number');
    $expiry_month = $app->request->put('expiry_month');
    $expiry_year = $app->request->put('expiry_year');
    $cvn = $app->request->put('cvn');
    $card_type = $app->request->put('card_type');
    $isdefault = $app->request->put('isdefault');

    $db = new DbHandler();
    $res = $db->addCard($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault);
    //print_r($res);
    //exit;
    
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request";
        echoRespnse(201, $response);
    } else if ($res == 'ALREADY_SAVED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "This card is already saved";
        echoRespnse(201, $response);
    } else if ($res[0]['errors']!='') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = $res[0]['errors'];
        $response["data"] = $res;
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Credit card details saved successfully";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
* Delete credit card
* url - /card/:cardid
* method - DELETE
* Header params - username(mandatory), password(mandatory)
**/
$app->delete('/card/:cardid', 'authenticate', function($cardid) use ($app) {
    $response = array();
    $db = new DbHandler();
    global $user_id;
    $res = $db->deleteCard($user_id, $cardid);
    //print_r($res);
   // exit;
    if ($res=='INAVLID_CARD') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] ='Invalid card';
        echoRespnse(201, $response);
    } else if ($res=='NOT_AUTHORIZED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] ='You are not authorize to delete this card';
        echoRespnse(201, $response);
    } else if ($res=='UNABLE_TO_PROCEED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] ='Unable to proceed your request';
        echoRespnse(201, $response);
    } else if ($res=='PAYMENT_PENDING') {
        $response["code"] = 5;
        $response["error"] = true;
        $response["message"] ='Payment pending with selected card';
        echoRespnse(201, $response);
    } else if ($res=='DEFAULT_CARD') {
        $response["code"] = 6;
        $response["error"] = true;
        $response["message"] ='Sorry, You can\'t delete default card';
        echoRespnse(201, $response);
    }else if ($res[0]['errors']!='') {
        $response["code"] = 4;
        $response["error"] = true;
        $response["message"] = $res[0]['errors'];
        $response["data"] = $res;
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Credit card successfully removed";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Edit credit card
 * url - /editcard
 * method - POST
 * params - firstname(mandatory), expiry_month(mandatory), expiry_year(mandatory), cardid(mandatory), hitpaypal(mandatory), isdefault(optional), card_number(mandatory), card_type(mandatory)
 * hitPaypal = 0 / 1
 * isdefault -> YES  / NO, deafult 'NO'
 * Header params - username(mandatory), password(mandatory)
 */
$app->post('/editcard', 'authenticate', function () use ($app) {
    global $user_id;
    // check for required params
    verifyRequiredParams(array('firstname', 'expiry_month', 'expiry_year', 'cardid', 'hitpaypal', 'card_number', 'card_type'));
    $response = array();

    // reading post params
    $firstname = $app->request->put('firstname');
    $expiry_month = $app->request->put('expiry_month');
    $expiry_year = $app->request->put('expiry_year');
    $cardid = $app->request->put('cardid');
    $hitpaypal = $app->request->put('hitpaypal');
    $isdefault = $app->request->put('isdefault');
    $card_number = $app->request->put('card_number');
    $card_type = $app->request->put('card_type');
    $db = new DbHandler();
    $res = $db->editCard($user_id, $firstname, $expiry_month, $expiry_year, $cardid, $hitpaypal, $isdefault, $card_number, $card_type);
    //print_r($res);
    //exit;
    if ($res == 'INAVLID_CARD') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] ='Invalid card';
        echoRespnse(201, $response);
    } else if ($res=='NOT_AUTHORIZED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] ='You are not authorize to delete this card';
        echoRespnse(201, $response);
    } else if ($res=='UNABLE_TO_PROCEED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] ='Unable to proceed your request';
        echoRespnse(201, $response);
    } else if ($res=='DEFAULT_CARD') {
        $response["code"] = 5;
        $response["error"] = true;
        $response["message"] ='There must be a default card in your profile';
        echoRespnse(201, $response);
    } else if ($res[0]['errors']!='') {
        $response["code"] = 4;
        $response["error"] = true;
        $response["message"] = $res[0]['errors'];
        $response["data"] = $res;
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Credit card details successfully updated";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get Users Credit Card List 
 * url - /cards
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/cards', 'authenticate', function () use ($app) {
	global $user_id;
	$response = array();
	$db = new DbHandler();
	$res = $db->cardList($user_id);
	if ($res == 'NO_RECORD') {
		$response["code"] = 1;
		$response["error"] = false;
		$response["message"] = "No Credit Cards available";
		$response["data"] = array();
		echoRespnse(200, $response);
	} else {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Credit Card List";
		$response["data"] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Cart Data 
 * url - /cart/:branch_id
 * DEFAULT branch_id = 0 (If need whole data)
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/cart/:branch_id', 'authenticate', function ($branch_id) use ($app) {
	global $user_id;
	$response = array();
	$db = new DbHandler();
	$res = $db->cartData($user_id, $branch_id);
	if ($res == 'NO_RECORD') {
		$response["code"] = 1;
		$response["error"] = false;
		$response["message"] = "No Cart Data available";
		$response["data"] = array();
		echoRespnse(200, $response);
	} else {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Cart Data";
		$response["data"] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * SAVE / UPDATE / DELETE Cart Data 
 * url - /cart
 * params :- branch_id(mandatory), data(mandatory)
 * method - POST
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/cart', 'authenticate', function () use ($app) {
	global $user_id;
    // check for required params
    verifyRequiredParams(array('branch_id', 'data'));
    $response = array();
    // reading post params
    $branch_id = $app->request->put('branch_id');
	$data = $app->request->put('data');
	$response = array();
	$db = new DbHandler();
	$res = $db->saveCartData($user_id, $branch_id, $data);
	if ($res == 'NO_RECORD') {
		$response["code"] = 1;
		$response["error"] = false;
		$response["message"] = "No Cart Data available";
		echoRespnse(200, $response);
	} else if ($res == 'SUCCESSFULLY_DELETED') {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Successfully Cart Data deleted";
		echoRespnse(200, $response);
	} else if ($res == 'SUCCESSFULLY_UPDATED') {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Successfully Cart Data Updated";
		echoRespnse(200, $response);
	} else {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Successfully Cart Data Saved";
		echoRespnse(200, $response);
	}
});

/**
 * Add / Update Event Request
 * url - /event
 * method - POST
 * params - customer_id(mandatory), name(mandatory), mobile(mandatory), address(mandatory), no_of_guest(mandatory), event_date(mandatory), event(mandatory), event_id(mandatory), device_id(mandatory), device_type(mandatory), ipaddress(mandatory), occasion(mandatory), branch_id(mandatory)
 * customer_id deafult 0
 * event_id default 0
 * event_date IN UTC TIMESTAMP
 * device_type -> 'A-> ANDROID','I->IPHONE'
 * event -> If user select multiple event then comma separated events name should be there
 */
$app->post('/event', function () use ($app) {
	global $user_id;
    verifyRequiredParams(array('customer_id', 'name', 'mobile', 'address', 'no_of_guest', 'event_date', 'occasion', 'event', 'event_id', 'device_id', 'device_type', 'ipaddress', 'branch_id'));
    // reading post params
    $customer_id = $app->request->post('customer_id');
    $name = $app->request()->post('name');
    $mobile = $app->request()->post('mobile');
    $address = $app->request->post('address');
    $no_of_guest = $app->request()->post('no_of_guest');
    $event_date = $app->request()->post('event_date');
    $occasion = $app->request()->post('occasion');
    $event = $app->request()->post('event');
    $event_id = $app->request()->post('event_id');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $ipaddress = $app->request()->post('ipaddress');
    $branch_id = $app->request()->post('branch_id');

    $response = array();
    $db = new DbHandler();
    $res = $db->addEvent($customer_id, $name, $mobile, $address, $no_of_guest, $event_date, $occasion, $event, $event_id, $device_id, $device_type, $ipaddress, $branch_id);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        if($event_id>0) {
			$response["message"] = "Event details successfully Updated";
		} else {
			$response["message"] = "Event details successfully Saved";
		}
        echoRespnse(200, $response);
    }
});

/**
 * Events List
 * url - /events
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/events', 'authenticate', function () use ($app) {
	global $user_id;
	$response = array();
	$db = new DbHandler();
	$res = $db->getEvents($user_id);
	if ($res == 'NO_RECORD') {
		$response["code"] = 1;
		$response["error"] = false;
		$response["message"] = "No Events Found";
		$response["data"] = array();
		echoRespnse(200, $response);
	} else {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Events Data";
		$response["data"] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get page content
 * url - /page_content/:pagename
 * method - GET
 * pagename : TERMS_AND_CONDITIONS, PRIVACY_POLICY, ABOUT_US
 */
$app->get('/page_content/:pagename', function ($pagename) use ($app) {
	global $user_id;
	$response = array();
	$db = new DbHandler();
	$res = $db->getPageContent($pagename);
	if ($res == 'NO_RECORD') {
		$response["code"] = 1;
		$response["error"] = true;
		$response["message"] = "Page Not Found";
		$response["data"] = array();
		echoRespnse(201, $response);
	} else {
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "Page Content";
		$response["data"] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Customer Logout
 * url - /logout
 * method - POST
 * params - device_id(mandatory),
 * Header Params - username (mandatory), password (mandatory)
 */
$app->post('/logout', 'authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array('device_id'));
    $device_id = $app->request->post('device_id');
    $response = array();
    $db = new DbHandler();
    $res = $db->logout($user_id, $device_id);
    if ($res) {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "You have successfully signed out.";
        echoRespnse(200, $response);
    }
});

/**
 * url - /order
 * method - POST
 * params - branch_id(mandatory), data(mandatory), address_id(mandatory), card_id(mandatory), distance(mandatory),  amount(mandatory), type(mandatory), order_type(mandatory), order_delivery_time(mandatory), comment(optional), tax(mandatory), device_id(mandatory), device_type(mandatory), ipaddress(mandatory), loyalty_points(optional), loyalty_point_value(optional), delivery_charges(mandatory), promocode(optional)
 * type -> P->PICK UP, D->DELIVERY
 * order_type -> S->SCHEDULED, C->CURRENT
 * order_delivery_time -> datetime in UTC timestamp -- If scheduled
 * device_type -> 'A-> ANDROID','I->IPHONE'
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/order', 'authenticate', function () use ($app) {
	global $user_id;
    verifyRequiredParams(array('branch_id', 'data', 'address_id', 'card_id', 'distance', 'amount', 'type', 'order_type', 'order_delivery_time', 'tax', 'device_id', 'device_type', 'ipaddress'));
    // reading post params
    $branch_id = $app->request->post('branch_id');
    $data = $app->request()->post('data');
    $address_id = $app->request()->post('address_id');
    $card_id = $app->request->post('card_id');
    $distance = $app->request()->post('distance');
    $amount = $app->request()->post('amount');
    $type = $app->request()->post('type');
    $order_type = $app->request->post('order_type');
    $order_delivery_time = $app->request()->post('order_delivery_time');
    $comment = $app->request()->post('comment');
    $tax = $app->request()->post('tax');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $ipaddress = $app->request()->post('ipaddress');
    $loyalty_points = $app->request()->post('loyalty_points');
    $loyalty_point_value = $app->request()->post('loyalty_point_value');
    $delivery_charges = $app->request()->post('delivery_charges');
    $promocode = $app->request()->post('promocode');
	$order_amount = $app->request()->post('order_amount');
	
    $response = array();
    $db = new DbHandler();
    $res = $db->makeOrder($user_id, $branch_id, $data, $address_id, $card_id, $distance, $amount, $type, $order_type, $order_delivery_time, $comment, $tax, $device_id, $device_type, $ipaddress, $loyalty_points, $loyalty_point_value, $delivery_charges, $promocode,$order_amount);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["data"] = NULL;
        $response["message"] = "Unable to proceed";
        echoRespnse(201, $response);
    } else if ($res == 'PRMOCODE_NOT_EXIST') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "Please enter valid Promocode";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PRMOCODE_USES_LIMIT_EXPIRED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Promocode has been expired";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'MAX_LIMIT_REACHED') {
        $response["code"] = 4;
        $response["error"] = true;
        $response["message"] = "Maximum uses limit exceeded";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PROMOCODE_EXPIRED') {
        $response["code"] = 5;
        $response["error"] = true;
        $response["message"] = "Promocode has been expired";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PROMOCODE_NOT_FOR_USER') {
        $response["code"] = 6;
        $response["error"] = true;
        $response["message"] = "You can not apply this promocode";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'ONLY_FOR_NEW_USER') {
        $response["code"] = 7;
        $response["error"] = true;
        $response["message"] = "This promocode is for new user only";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'WALLET_POINTS_LOW_THEN_APPPLIED') {
        $response["code"] = 8;
        $response["error"] = true;
        $response["message"] = "Applied loyalty points are more than available in wallet";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    }  else if ($res == 'PAYMENT_FAILED') {
        $response['code'] = 9;
        $response['error'] = true;
        $response['message'] = "Payment failed";
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_CARD') {
        $response['code'] = 10;
        $response['error'] = true;
        $response['message'] = "Payment failed";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["data"]=$res;
		$response["message"] = "Order successfully placed";
        echoRespnse(200, $response);
    }
    
});

/**
 * Get Orders
 * url - /orders/:status/:type
 * status -> R->RUNNUNG (N,A,P,AD,OD), H->HISTORY (DL,PU,CC)
 * type -> S->SCHEDULED, C->CURRENT, A->ALL
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/orders/:status/:type', 'authenticate', function($status, $type) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getOrders($user_id, $branch_id, $status, $type);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Orders List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Order Details
 * url - /order/:order_id
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/order/:order_id', 'authenticate', function($order_id) use ($app){
	global $user_id;
	global $branch_id;
	$db = new DbHandler();
	$res=$db->getOrdersDetails($user_id, $order_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = NULL;
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Order Data";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Cancel Order
 * url - /ordercancel
 * method - POST
 * params - order_id(mandatory), invoice_id(mandatory), device_id(mandatory), device_type(mandatory), ip_address(mandatory)
 * device_type -> I / A / W
 * header Params - username(mandatory), password(mandatory)
 */
$app->post('/ordercancel', 'authenticate', function () use ($app) {
    global $user_id;
    verifyRequiredParams(array('order_id', 'invoice_id','orderedat', 'device_id', 'device_type', 'ip_address'));
    $order_id = $app->request()->post('order_id');
    $invoice_id = $app->request()->post('invoice_id');
        $orderedat = $app->request()->post('orderedat');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $ip_address = $app->request()->post('ip_address');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->orderCancel($user_id, $order_id, $invoice_id,$orderedat, $device_id, $device_type, $ip_address);
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "wrong action taken";
        echoRespnse(201, $response);
    } else if ($user == 'CANCELLATION_DURATION_EXPIRED') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Cancellation duration expired";
        echoRespnse(201, $response);
    } else if ($user == 'FAILED_TO_REFUND') {
        $response['code'] = 3;
        $response['error'] = true;
        $response['message'] = "Sorry, unbale to cancle this order, Pleae try again";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Order has been cancelled";
        echoRespnse(200, $response);
    }
});

/**
 * Make Item Favourite
 * url - /favourite
 * method - POST
 * params - item_id(mandatory)
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/favourite', 'authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array('item_id'));
    $item_id = $app->request->post('item_id');

    $response = array();
    $db = new DbHandler();
    $res = $db->makeMenuFavourite($user_id, $item_id);
    if ($res=='ALREADY_ADDED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = 'Already added in Favourite list';
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Successfully Added in Favourite list";
        echoRespnse(200, $response);
    }
});

/**
 * List of favourite items
 * url - /favourite/:branch_id
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/favourite/:branch_id', 'authenticate',function($branch_id) use ($app) {
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getFavouriteMenus($user_id, $branch_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Favourite Items list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
* Remove item from favourite items list
* url - /favourite/:item_id
* method - DELETE
* Header params - username(mandatory),password (mandatory)
**/
$app->delete('/favourite/:item_id', 'authenticate', function($item_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    global $user_id;
    $res = $db->deleteFavouriteMenu($user_id, $item_id);
    if ($res=='NOT_FOUND') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] ='Item Successsfully removed from your favourite list';
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] ='Item Successsfully removed from your favourite list';
    }
    echoRespnse(200, $response);
});

/**
 * Favorite status of sent item ids 
 * url - /favourite/:item_ids
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/favourite/:item_ids', 'authenticate',function($item_ids) use ($app) {
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getFavouriteMenuStatus($user_id, $item_ids);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Favourite items list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * SAVE + UPDATE Rating AND Comment on ordered food item
 * url - /rating
 * method - POST
 * params - item_id(mandatory), order_id(mandatory), rating(mandatory), comment(optional), row_id(mandatory)
 * row_id deafult 0
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/rating', 'authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array('item_id', 'order_id', 'rating', 'row_id'));
    $item_id = $app->request->post('item_id');
    $order_id = $app->request->post('order_id');
    $rating = $app->request->post('rating');
    $comment = $app->request->post('comment');
    $row_id = $app->request->post('row_id');

    $response = array();
    $db = new DbHandler();
    $res = $db->postRatingOnItem($user_id, $item_id, $order_id, $rating, $comment, $row_id);
    if ($res=='ALREADY_ADDED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = 'Already Rate the Item';
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        if ($row_id>0) {
			$response["message"] = "Rating Successfully Updated";
		} else {
			$response["message"] = "Rating Successfully Saved";
		}
        echoRespnse(200, $response);
    }
});

/**
 * Rating of item
 * url - /rating/:item_id
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/rating/:item_id',function($item_id) use ($app) {
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getItemRatings($user_id, $item_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = NULL;
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Rating list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/** 
 * Get item Comments List
 * url - /comments/:item_id/:pageno
 * method - GET
 * pageno = 0 then all records
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/comments/:item_id/:pageno', function($item_id, $pageno) use ($app){
	$response = array();	
	$db = new DbHandler();
	$res=$db->getComments($item_id, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['pages'] = 0;
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] =array();
		echoRespnse(200, $response);
	} else {
		$total_pages = $db->getCommentsPages($item_id);
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Menu List";
		$response['pages'] = $total_pages;
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * List of Event page options
 * url - /event_options
 * method - GET
 */
$app->get('/event_options',function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->getEventOptions();
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Favourite Items list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * List of Promotions
 * url - /promotions/:branch_id
 * method - GET
 */
$app->get('/promotions/:branch_id',function($branch_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->getPromotions($branch_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Promotions list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get Promotion details
 * url - /promotion/:promotion_id
 * method - GET
 */
$app->get('/promotion/:branch_id',function($promotion_id) use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->getPromotionDetails($promotion_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Promotions list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Deactivate Own Account with Password
 * url - /deactivate_account
 * method - POST
 * params - oldpassword (mandatory)
 * header Params - username (mandatory), password (mandatory)
 * username -> email / mobile
 */
$app->post('/deactivate_account', 'authenticate', function () use ($app) {
    global $user_id;
    verifyRequiredParams(array('oldpassword'));
    $oldpassword = $app->request()->post('oldpassword');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->deactivateAccount($user_id, $oldpassword);
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Unable to proceed your request";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_OLD_PASSWORD') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Invalid Password";
        echoRespnse(201, $response);
    } else if ($user == 'INVALID_USER_ID') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "User is not valid";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Your Account has been deactivated, For activation please contact to app admin";
        echoRespnse(200, $response);
    }
});

/**
 * get loyalty points
 * url - /loyalty_points
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/loyalty_points', 'authenticate', function() use ($app) {
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getLoyaltyPoints($user_id);
    $referralData=$db->getReferralerData();
	$response["code"] = 0;
	$response["error"] = false;
	$response["message"] = 'Available loyalty points';
	$response["data"] = $res;
	$response["data"]['referral'] = $referralData;
	echoRespnse(200, $response);
});

/**
 * url - /apply_promocode
 * method - POST
 * params - company_id(mandatory), branch_id(mandatory), category_ids(mandatory), item_ids(mandatory), amount(mandatory), type(mandatory), order_type(mandatory), promocode(mandatory)
 * branch_id, category_ids, item_ids -> DEFAULT 0
 * type -> P->PICK UP, D->DELIVERY, A->ALL
 * order_type -> S->SCHEDULED, C->CURRENT, A->ALL
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/apply_promocode', 'authenticate', function () use ($app) {	
	global $user_id;
    verifyRequiredParams(array('company_id', 'branch_id', 'category_ids', 'item_ids', 'amount', 'type', 'order_type', 'promocode'));
    // reading post params
    $company_id = $app->request->post('company_id');
    $branch_id = $app->request->post('branch_id');
    $category_ids = $app->request->post('category_ids');
    $item_ids = $app->request->post('item_ids');
    $amount = $app->request()->post('amount');
    $type = $app->request()->post('type');
    $order_type = $app->request->post('order_type');
    $promocode = $app->request->post('promocode');

    $response = array();
    $db = new DbHandler();
    $res = $db->applyPromocode($user_id, $company_id, $branch_id, $category_ids, $item_ids, $amount, $type, $order_type, $promocode);
    if ($res == 'PRMOCODE_NOT_EXIST') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Please enter valid Promocode";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PRMOCODE_USES_LIMIT_EXPIRED') {
        $response["code"] = 2;
        $response["error"] = true;
        $response["message"] = "Promocode has been expired";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'MAX_LIMIT_REACHED') {
        $response["code"] = 3;
        $response["error"] = true;
        $response["message"] = "Maximum uses limit exceeded";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_COMPANY_ID') {
        $response["code"] = 4;
        $response["error"] = true;
        $response["message"] = "Promocode is not applicable for this Restaurant";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'INVALID_BRANCH_ID') {
        $response["code"] = 5;
        $response["error"] = true;
        $response["message"] = "Promocode is not applicable for this Restaurant";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PROMOCODE_EXPIRED') {
        $response["code"] = 6;
        $response["error"] = true;
        $response["message"] = "Promocode has been expired";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'PROMOCODE_NOT_FOR_USER') {
        $response["code"] = 7;
        $response["error"] = true;
        $response["message"] = "You can not apply this promocode";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    }  else if ($res == 'CATEGORY_NOT_MATCHED') {
        $response["code"] = 8;
        $response["error"] = true;
        $response["message"] = "This promocode is not applicable for selected category items";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'ITEM_NOT_MATCHED') {
        $response["code"] = 9;
        $response["error"] = true;
        $response["message"] = "This promocode is not applicable for selected menu items";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'ONLY_FOR_NEW_USER') {
        $response["code"] = 10;
        $response["error"] = true;
        $response["message"] = "This promocode is for new user only";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'MINIMUM_ORDER_AMOUNT_FAILED') {
        $response["code"] = 11;
        $response["error"] = true;
        $response["message"] = "Please check order minimum amount";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'ORDER_TYPE_NOT_MATCHED') {
        $response["code"] = 12;
        $response["error"] = true;
        $response["message"] = "Promocode is not applicable for selected order type";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else if ($res == 'DELIVERY_OPTIONS_NOT_MATCHED') {
        $response["code"] = 13;
        $response["error"] = true;
        $response["message"] = "Promocode is not applicable for selected delivery option";
        $response["data"]=NULL;
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["data"]=$res;
		$response["message"] = "Promocode successfully applied";
        echoRespnse(200, $response);
    }
});

/**
 * Get FAQs
 * url - /faqs
 * method - GET
 */
$app->get('/faqs', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $res = $db->getFaqs();
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Faq list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get Notifications List
 * url - /notifications
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/notifications', 'authenticate', function() use ($app) {
	global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getNotifications($user_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Notifications list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get My Rating + Reviews
 * url - /myratings
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/myratings', 'authenticate', function() use ($app) {
	global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->getMyRatings($user_id);
    if ($res=='NO_RECORD_FOUND') {
        $response["code"] = 1;
        $response["error"] = false;
        $response["message"] = 'No record found.';
        $response["data"] = array();
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Rating And Comment list";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Post contact us form
 * url - /contactus
 * method - POST
 * params - name(mandatory), email(mandatory), mobile_code(mandatory), mobile(mandatory), message(mandatory), loggedin_userid(mandatory), ipaddress (mandatory)
 * loggedin_userid -> default 0
 */
$app->post('/contactus', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('name',  'email',  'mobile_code',  'mobile',  'message',  'loggedin_userid', 'ipaddress'));
    $response = array();

    // reading post params
    $name = $app->request->put('name');
    $email = $app->request->put('email');
    $mobile_code = $app->request->put('mobile_code');
    $mobile = $app->request->put('mobile');
    $message = $app->request->put('message');
    $loggedin_userid = $app->request->put('loggedin_userid');
    $ip_address = $app->request->put('ipaddress');

    $db = new DbHandler();
    $res = $db->contactus($name, $email, $mobile_code, $mobile, $message, $loggedin_userid, $ip_address);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed your request";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Your request has been submitted to admin";
        $response["data"] = $res;
        echoRespnse(200, $response);
    }
});

/**
 * Get App version from playstore
 */
$app->get('/get_playstore_app_version/:app_id',function($app_id) use ($app) {   
	try {
		$received_str= file_get_contents("https://play.google.com/store/apps/details?id=".$app_id."&hl=en");  
	} catch (Exception $e){
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
});

// functions of image upload
function resize($image_name, $size, $folder_name, $thumbnail) {
    $file_extension = getFileExtension($image_name);
    switch ($file_extension) {
        case 'jpg':
        case 'jpeg':
            $image_src = imagecreatefromjpeg($folder_name . $image_name);
            break;
        case 'png':
            $image_src = imagecreatefrompng($folder_name . $image_name);
            break;
        case 'gif':
            $image_src = imagecreatefromgif($folder_name . $image_name);
            break;
    }
    $true_width = imagesx($image_src);
    $true_height = imagesy($image_src);

    $width = $size;
    $height = ($width / $true_width) * $true_height;

    $image_des = imagecreatetruecolor($width, $height);

    imagecopyresampled($image_des, $image_src, 0, 0, 0, 0, $width, $height, $true_width, $true_height);

    switch ($file_extension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($image_des, $thumbnail . '/' . $image_name, 100);
            break;
        case 'png':
            imagepng($image_des, $thumbnail . '/' . $image_name, 8);
            break;
        case 'gif':
            imagegif($image_des, $thumbnail . '/' . $image_name, 100);
            break;
    }
    return $image_des;
}

function getFileExtension($file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    return $extension;
}

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    $realm = 'Protected APIS';

    $req = $app->request();
    $res = $app->response();
    $username = $req->headers('PHP_AUTH_USER');
    $password = $req->headers('PHP_AUTH_PW');
    if (isset($username) && $username != '' && isset($password) && $password != '') {
		$dbconn = new DbConnect();
        $db = new DbHandler();
        if ($userid = $db->validateUser($username, $password)) {
            global $user_id;
            $user_id = $userid["id"];
            return true;
        } else {
            $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $realm));
            $res = $app->response();
            $res->status(401);
            $app->stop();
        }
    } else {
        $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $realm));
        $res = $app->response();
        $res->status(401);
        $app->stop();
    }
}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
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
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
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
        echoRespnse(200, $response);
        $app->stop();
    }
}
$app->run();
?>
