<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once '../include/DbConnect.php';
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
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

$app->get('/testmail', function() use ($app){ 
	$db = new DbHandler(); 
	$res = $db->sendSMTPMail('test message', 'test message', 'manoj.sharma.guy@gmail.com', "manoj.sharma.guy@gmail.com", 'elguero 2', 'sales@elguero2.com'); 
});

$app->get('/testnotifications', function() use ($app) {
	$db = new DbHandler(); 
	$message='test message';
	$device_tokens=array("cdg_Do3gpUg:APA91bEqXrokPEWj1HPoGezOahx94UH-sdpTqrPpvgjuqZULsArvrYQmosRw1C40bXm7s4X-1mjB-hUYjwZ9ElecK1d9wILSlS5XVrHOWtY5fDA3bu_zhSfE6ncg_4d8XVW9C_jxEOCA");
	$noti_type="NEW_ORDER";
	$db->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'ADMIN', 186);
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
		$response["message"] = "Login Successfully."; 
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
            $signature_profile_pic = $user_id.'_'.time() . "_profile_pic." . $extentation_profile_pic;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], ADMIN_PIC_PATH.'large/' . $signature_profile_pic)) {
                if (in_array($extentation_profile_pic, $db->image_extensions)) {
                    resize($signature_profile_pic, 150, ADMIN_PIC_PATH.'large/', ADMIN_PIC_PATH.'thumb');
                }
				$signaturedb = $db->updateImage($user_id, $signature_profile_pic);
				if ($signaturedb == 'UNABLE_TO_PROCEED') {
					$response["code"] = 2;
					$response["error"] = true;
					$response["message"] = "Unable to proceed.";
					echoRespnse(201, $response);
				} else {
					$response["code"] = 0;
					$response["error"] = false;
					$response["message"] = "Profile Pic successfully updated";
					$response['data'] = $signaturedb;
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
 * Get Orders
 * url - /orders/:status/:type
 * status -> N->NEW, A->APPROVED, D->DECLINED, CB->CANCELLED BY BRANCH, CC->CANCELLED BY CUSTOMER, P->PREPARED, AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED, PU->PICKEDUP BY CUSTOMER
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
 * Get Orders with post method
 * url - /orders
 * params : status(mandatory), type(mandatory), delivery(mandatory), 
 * textsearch(mandatory), orderfromdate(mandatory), ordertodate(mandatory), createdfromdate(mandatory), createdtodate(mandatory), pageno(mandatory)
 * status -> N->NEW, A->APPROVED, D->DECLINED, CB->CANCELLED BY BRANCH, CC->CANCELLED BY CUSTOMER, P->PREPARED, AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED, PU->PICKEDUP BY CUSTOMER
 * type -> S->SCHEDULED, C->CURRENT, A->ALL
 * delivery -> P->PICK UP, D->DELIVERY, A->ALL
 * textsearch, orderfromdate, ordertodate, createdfromdate, createdtodate, pageno = DEFAULT VALUE 0
 * IF pageno = then all records will be returned
 * orderfromdate = selected date with 00:00:01 (timestamp) 
 * ordertodate = selected date with 23:59:59 (timestamp)
 * createdfromdate = selected date with 00:00:01 (timestamp) 
 * createdtodate = selected date with 23:59:59 (timestamp)
 * method - POST
 * Header Params - username(mandatory), password(mandatory)
 */
$app->post('/orders', 'authenticate', function() use ($app){
	global $user_id;
	global $branch_id;
	verifyRequiredParams(array('status', 'type', 'delivery', 'textsearch', 'orderfromdate', 'ordertodate', 'createdfromdate', 'createdtodate', 'pageno'));
	$status = $app->request()->post('status');
    $type = $app->request()->post('type');
    $delivery = $app->request()->post('delivery');
    $textsearch = $app->request()->post('textsearch');
    $orderfromdate = $app->request()->post('orderfromdate');
    $ordertodate = $app->request()->post('ordertodate');
    $createdfromdate = $app->request()->post('createdfromdate');
    $createdtodate = $app->request()->post('createdtodate');
    $pageno = $app->request()->post('pageno');
	$response = array();
	$db = new DbHandler();
	$res=$db->getOrders($user_id, $branch_id, $status, $type, $delivery, $textsearch, $orderfromdate, $ordertodate, $createdfromdate, $createdtodate, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		$response['pages'] = 0;
		echoRespnse(200, $response);
	} else {
		$pages=$db->getOrdersPages($user_id, $branch_id, $status, $type, $delivery, $textsearch, $orderfromdate, $ordertodate, $createdfromdate, $createdtodate);
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
 * Header Params - username(mandatory), password(mandatory)
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
		$response['message'] = "Order Data";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Delivery men List
 * url - /deliverymen/:status
 * status -> FREE, ALL, BUZY
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/deliverymen/:status', 'authenticate', function($status) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getDeliveryMen($branch_id, $status);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Deliver men List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Delivery man Assigned Orders
 * url - /delivermanorders/:delivery_man_id
 * method - GET
 * Header Params - username (mandatory), password (mandatory)
 */
$app->get('/delivermanorders/:delivery_man_id', 'authenticate', function($delivery_man_id) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getDeliverManOrders($user_id, $branch_id, $delivery_man_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Delivery men Orders List";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Update Order Status
 * url - /changestatus
 * method - POST
 * params - order_id(mandatory), invoice_id(mandatory), customer_id(mandatory), status(mandatory), delivery_man_id(mandatory), device_id(mandatory), device_type(mandatory), ip_address(mandatory)
 * status :- A->APPROVED, D->DECLINED, P->PREPARED, AD->ASSIGNED TO DRIVER, PU->PICKEDUP BY CUSTOMER
 * device_type -> I / A / W
 * delivery_man_id default 0
 * (RE-ASSIGN WILL BE HANDELLED WITH THIS API , JUST CALL ASSIGNED TO DRIVER (AD))
 * header Params - username(mandatory), password(mandatory)
 */
$app->post('/changestatus', 'authenticate', function () use ($app) {
    global $user_id;
    verifyRequiredParams(array('order_id', 'invoice_id', 'customer_id', 'status', 'delivery_man_id', 'device_id', 'device_type', 'ip_address'));
    $order_id = $app->request()->post('order_id');
    $invoice_id = $app->request()->post('invoice_id');
    $customer_id = $app->request()->post('customer_id');
    $status = $app->request()->post('status');
    $delivery_man_id = $app->request()->post('delivery_man_id');
    $device_id = $app->request()->post('device_id');
    $device_type = $app->request()->post('device_type');
    $ip_address = $app->request()->post('ip_address');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->changeStatus($user_id, $order_id, $invoice_id, $customer_id, $status, $delivery_man_id, $device_id, $device_type, $ip_address);
    if ($user == 'UNABLE_TO_PROCEED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "wrong action taken";
        echoRespnse(201, $response);
    } else if ($user == 'PAYMENT_FAILED') {
        $response['code'] = 2;
        $response['error'] = true;
        $response['message'] = "Sorry this order has been cancelled";
        echoRespnse(201, $response);
    } else if ($user == 'FAILED_TO_REFUND') {
        $response['code'] = 3;
        $response['error'] = true;
        $response['message'] = "Sorry, unbale to cancle this order, Pleae try again";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Order status successfully changed";
        $response['data'] = $user;
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
 * Logout branch
 * url - /logout
 * method - POST
 * params - devcieid(mandatory),
 * Header Params - username(mandatory), password(mandatory)
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
 * Get Dashboard Data
 * url - /dashboard
 * method - GET
 * Header Params - username (mandatory), password (mandatory)
 */
$app->get('/dashboard', 'authenticate', function() use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getDashboardData($branch_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		echoRespnse(200, $response);
	} else {
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Dashboard Data";
		$response['data'] = $res;
		echoRespnse(200, $response);
	}
});

/**
 * Get Event List
 * url - /events/:textsearch/:status/:eventfromdate/:eventtodate/:postedfromdate/:postedtodate/:pageno
 * method - POST
 * textsearch (mandatory), status(mandatory), eventfromdate(mandatory), eventtodate(mandatory), postedfromdate(mandatory), postedtodate(mandatory), pageno (mandatory)
 * textsearch, status = 0 (zero)
 * status == N (NEW), R (RESPONDED)
 * eventfromdate, eventtodate, postedfromdate, postedtodate = DEFAULT 0
 * eventfromdate = selected date with 00:00:01 (timestamp) 
 * eventtodate = selected date with 23:59:59 (timestamp)
 * postedfromdate = selected date with 00:00:01 (timestamp) 
 * postedtodate = selected date with 23:59:59 (timestamp)
 * pageno = DEFAULT 0 then all recods will return
 * Header Params - username (mandatory), password (mandatory)
 */
$app->post('/events', 'authenticate', function() use ($app){
	global $user_id;
	global $branch_id;
	 verifyRequiredParams(array('textsearch', 'status', 'eventfromdate', 'eventtodate', 'postedfromdate', 'postedtodate', 'pageno'));
	$textsearch = $app->request()->post('textsearch');
    $status = $app->request()->post('status');
    $eventfromdate = $app->request()->post('eventfromdate');
    $eventtodate = $app->request()->post('eventtodate');
    $postedfromdate = $app->request()->post('postedfromdate');
    $postedtodate = $app->request()->post('postedtodate');
    $pageno = $app->request()->post('pageno');
	$response = array();
	$db = new DbHandler();
	$res=$db->getEvents($branch_id, $textsearch, $status, $eventfromdate, $eventtodate, $postedfromdate, $postedtodate, $pageno);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
		$response['pages'] = 0;
		echoRespnse(200, $response);
	} else {
		$pages=$db->getEventsPages($branch_id, $textsearch, $status, $eventfromdate, $eventtodate, $postedfromdate, $postedtodate);
		$response['code'] = 0;
		$response['error'] = false;
		$response['message'] = "Events List";
		$response['data'] = $res;
		$response['pages'] = $pages;
		echoRespnse(200, $response);
	}
});

/**
 * Get Event Details
 * url - /event/:event_id
 * method - GET
 * Header Params - username(mandatory), password(mandatory)
 */
$app->get('/event/:event_id', 'authenticate', function($event_id) use ($app){
	global $user_id;
	global $branch_id;
	$response = array();
	$db = new DbHandler();
	$res=$db->getEventDetails($event_id);
	if ($res=='NO_RECORD_FOUND') {
		$response['code'] = 1;
		$response['error'] = false;
		$response['message'] = "No Record Found";
		$response['data'] = array();
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
 * send reply on customer's event request
 * url - /eventreply
 * method - POST
 * params - event_id(mandatory), reply(mandatory), comment(mandatory)
 * header Params - username(mandatory), password(mandatory)
 */
$app->post('/eventreply', 'authenticate', function () use ($app) {
    global $user_id;
    verifyRequiredParams(array('event_id', 'reply', 'comment'));
    $event_id = $app->request()->post('event_id');
    $reply = $app->request()->post('reply');
    $comment = $app->request()->post('comment');
    
    $response = array();
    $db = new DbHandler();
    $user = $db->eventReply($user_id, $event_id, $reply, $comment);
    if ($user == 'ALREADY_REPLIED') {
        $response['code'] = 1;
        $response['error'] = true;
        $response['message'] = "Already Replied";
        echoRespnse(201, $response);
    } else {
        $response['code'] = 0;
        $response['error'] = false;
        $response['message'] = "Reply sucessfully saved";
        echoRespnse(200, $response);
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
            global $branch_id;
            $user_id = $userid["id"];
            $branch_id = $userid["info_id"];
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
