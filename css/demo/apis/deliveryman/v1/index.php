<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once '../include/DbConnect.php';
require_once '../include/DbHandler.php';
require '../../../../lib/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$user_id = NULL;
$branch_id = NULL;



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


/**
 * Delivery man Login
 * url - /login
 * method - POST
 * params - username (mandatory), password (optional), device_id(mandatory), device_token(mandatory), device_type(mandatory), ipaddress(mandatory)
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
		$response["message"] = "Username / Password is invalid";
		echoRespnse(201, $response);
	} else if ($res == 'ACCOUNT_DEACTVATED') {  
		$response["code"] = 2;
		$response["error"] = true;
		$response["message"] = "Account deactivated, Please conatct to admin";
		echoRespnse(201, $response);
	} else if ($res == 'USERNAME_NOT_EXIST') {  
		$response["code"] = 3;
		$response["error"] = true;
		$response["message"] = "User not exist";
		echoRespnse(201, $response);   
	} else { 
		$response["code"] = 0;
		$response["error"] = false;
		$response["message"] = "User data"; 
		$response["data"] = $res;
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
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], DELIVERYMAN_PIC_PATH.'large/' . $signature_profile_pic)) {
               
                   resize($signature_profile_pic, WIDTH_THUMB, DELIVERYMAN_PIC_PATH.'large/', DELIVERYMAN_PIC_PATH.'thumb');
                
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
 * Update deliveryman Locations
 * url - /updatelocation
 * method - POST
 * params - latitude(mandatory), longitude(mandatory)
 * Header params - username(mandatory),password(mandatory)
 */
$app->post('/updatelocation', 'authenticate', function () use ($app) {
    global $user_id;
    // check for required params
    verifyRequiredParams(array('latitude', 'longitude'));
    $latitude = $app->request->post('latitude');
    $longitude = $app->request->post('longitude');

    $response = array();
    $db = new DbHandler();
    $response = array();
    $res = $db->updateLocation($user_id, $latitude, $longitude);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Driver location successfully updated.";
        echoRespnse(200, $response);
    }
});

/**
 * Update Duty status
 * url - /onlineoffline/:status
 * method - GET
 * status :- N - offline, Y- online
 * Header Params - username (mandatory), password (mandatory)
 */
$app->get('/onlineoffline/:status', 'authenticate', function ($status) use ($app) {
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $res = $db->onlineOffline($user_id, $status);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
		$newStatus='Offline';
		if($status=='Y')
			$newStatus='Online';
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "Duty status updated to $newStatus";
       	$response["data"] = $status;
        echoRespnse(200, $response);
    }
});

/**
 * Get Orders
 * url - /orders/:status
 * status -> AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED BY THIS DRIVER
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/orders/:status', 'authenticate', function($status) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getOrders($user_id, $branch_id, $status);
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
 * Get Orders with post method
 * url - /orders
 * params: status(mandatory), textsearch(mandatory), fromdate(mandatory), todate(mandatory), pageno(mandatory)
 * status -> AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED BY THIS DRIVER
 * ALL DEFAULTVALUES = 0
 * fromdate = selected date with 00:00:01 (timestamp) 
 * todate = selected date with 23:59:59 (timestamp)
 * IF pageno==0 then all records will be return
 * method - POST
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/orders', 'authenticate', function() use ($app){
	global $user_id;
	global $branch_id;
	// check for required params
    verifyRequiredParams(array('status', 'textsearch', 'fromdate', 'todate', 'pageno'));
    $status = $app->request->post('status');
    $textsearch = $app->request->post('textsearch');
    $fromdate = $app->request->post('fromdate');
    $todate = $app->request->post('todate');
    $pageno = $app->request->post('pageno');
    
	$response = array();
	$db = new DbHandler();
	$res=$db->getOrders($user_id, $branch_id, $status, $textsearch, $fromdate, $todate, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		$response['pages'] = 0;
		echoRespnse(200, $response);
	} else {
		$pages=$db->getOrdersPages($user_id, $branch_id, $status, $textsearch, $fromdate, $todate);
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Orders List";
		$response['data'] = $res;
		$response['pages'] = $pages;
		echoRespnse(200, $response);
	}
});

/**
 * Get Order Details
 * url - /order/:order_id
 * method - GET
 * Header Params - username (mandatory), password (mandatory)
 */
$app->get('/order/:order_id', 'authenticate', function($order_id) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getOrdersDetails($order_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Order Details";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Update Order Status
 * url - /changestatus
 * method - POST
 * params - order_id(mandatory), invoice_id(mandatory), customer_id(mandatory), status(mandatory), device_id(mandatory), device_type(mandatory), ip_address(mandatory)
 * device_type -> I / A / W
 * header Params - username (mandatory), password (mandatory)
 */
$app->post('/changestatus', 'authenticate', function () use ($app) {
    global $user_id;
    global $branch_id;
    verifyRequiredParams(array('order_id', 'invoice_id', 'customer_id', 'status', 'device_id', 'device_type', 'ip_address'));
    $order_id = $app->request()->post('order_id');
    $invoice_id = $app->request()->post('invoice_id');
    $customer_id = $app->request()->post('customer_id');
    $status = $app->request()->post('status');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $ip_address = $app->request()->post('ip_address');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->changeStatus($user_id, $branch_id, $order_id, $invoice_id, $customer_id, $status, $device_id, $device_type, $ip_address);
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Wrong action taken";
        echoRespnse(201, $response);
    }else if ($user == 'INVALID_STATUS') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Invalid status update only OD and DL is available to update";
        echoRespnse(201, $response);
    }else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Order Status successfully updated";
        echoRespnse(200, $response);
    }
});

/**
 * Get page content
 * url - /page_content/:pagename
 * method - GET
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
 * Logout Delivery man
 * url - /logout
 * method - POST
 * params - devcieid(mandatory),
 * Header Params - username (mandatory), password (mandatory)
 */
$app->post('/logout', 'authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array('devcieid'));
    $devcieid = $app->request->post('devcieid');

    $response = array();
    $db = new DbHandler();
    $res = $db->logout($user_id, $devcieid);
    if ($res == 'UNABLE_TO_PROCEED') {
        $response["code"] = 1;
        $response["error"] = true;
        $response["message"] = "Unable to proceed.";
        echoRespnse(201, $response);
    } else {
        $response["code"] = 0;
        $response["error"] = false;
        $response["message"] = "You have successfully signed out.";
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
            global $branch_id;
            $user_id = $userid["id"];
            $branch_id = $userid["branch_id"];
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

$app->run();
?>
