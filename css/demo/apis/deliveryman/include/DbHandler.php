<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class DbHandler {
    private $conn;
    function __construct() 	{
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    public $image_extensions = array(
        'jpg', 'jpeg', 'png', 'gif'
    );
    public $doc_extensions = array(
        'doc', 'docx', 'xls', 'xlsx', 'pdf'
    );
    public $file_extensions = array(
        'doc', 'docx', 'xls', 'xlsx', 'pdf', 'jpg', 'jpeg', 'png', 'gif'
    );
    
    public function validateUser($username, $password) {
		$md5password = md5($password);				
		$userUqery = "select id, branch_id from tbl_delivery_men where (email=? OR mobile=?) AND (password=? OR password=?) and status='A'";
		$login_driver = $this->conn->prepare($userUqery);
        $login_driver->bindParam(1, $username);
        $login_driver->bindParam(2, $username);
        $login_driver->bindParam(3, $password);
        $login_driver->bindParam(4, $md5password);
	    $login_driver->execute();
	    if($login_driver->rowCount()>0) {
			$driverData = $login_driver->fetch(PDO::FETCH_ASSOC);
	        return $driverData;
		}
    }
    
    public function login($username, $password, $device_id, $device_token, $device_type, $ipaddress) {            
		$md5password = md5($password);				
		$userUqery = "select tdm.*, tb.name as br_name, concat(tb.mobile_code,'',tb.mobile) as br_mobile, tb.address_landmark br_address_landmark, tb.address as br_address, tb.latitude as br_latitude, tb.longitude as br_longitude, tb.logo as br_logo from tbl_delivery_men tdm JOIN tbl_branches tb ON tb.id=tdm.branch_id where (tdm.email=? OR concat(tdm.mobile_code,'',tdm.mobile)=?)";
		$login_driver = $this->conn->prepare($userUqery);
        $login_driver->bindParam(1, $username);
        $login_driver->bindParam(2, $username);
	    $login_driver->execute();
	    //print_r($login_driver->errorInfo());
	    
	    if($login_driver->rowCount()>0) {
			$driverData = $login_driver->fetch(PDO::FETCH_ASSOC);
			if(($driverData['password']!=$password) && ($driverData['password']!=$md5password)) {
				return 'INVALID_USERNAME_PASSWORD';
			}
			if($driverData['status'] == 'D') { 
				return 'ACCOUNT_DEACTVATED'; 
			} else {
				$time=time();
				$updateQuery = "UPDATE tbl_delivery_men set device_id=?, device_type=?, device_token=?, lastlogin=? where id=?";
				$driver_update = $this->conn->prepare($updateQuery);
				$driver_update->bindParam(1, $device_id);
				$driver_update->bindParam(2, $device_type);
				$driver_update->bindParam(3, $device_token);
				$driver_update->bindParam(4, $time);
				$driver_update->bindParam(5, $driverData['id']);
				$driver_update->execute();
				
				$responseArr=array();
				$responseArr['id'] = $driverData['id'];
				$responseArr['firstname'] = $driverData['firstname'];
				$responseArr['lastname'] = $driverData['lastname'];
				$responseArr['address'] = $driverData['address'];
				$responseArr['mobile'] = $driverData['mobile_code'].$driverData['mobile'];
				$responseArr['email'] = $driverData['email'];
				$responseArr['profile_pic'] = $driverData['profile_pic']=='' || $driverData['profile_pic']===NULL ? '' : DELIVERYMAN_PIC_URL.'thumb/'.$driverData['profile_pic'];
				$responseArr['duty_status'] = $driverData['duty_status'];
				$responseArr['onduty'] = $driverData['onduty'];
				$responseArr['password'] = $driverData['password'];
				
				//branch details
				$responseArr['branch']['id'] = $driverData['branch_id'];
				$responseArr['branch']['name'] = $driverData['br_name'];
				$responseArr['branch']['mobile'] = $driverData['br_mobile'];
				$responseArr['branch']['address_landmark'] = $driverData['br_address_landmark'];
				$responseArr['branch']['address'] = $driverData['br_address'];
				$responseArr['branch']['latitude'] = $driverData['br_latitude'];
				$responseArr['branch']['longitude'] = $driverData['br_longitude'];
				$responseArr['branch']['logo'] = $driverData['br_logo']!='' ? BRANCH_PIC_URL.'thumb/'.$driverData['br_logo'] : '';
				
				
				return $responseArr; 
			}
		} else {
			return 'USERNAME_NOT_EXIST';
		}		
    }
    
    public function changePassword($userid, $oldpassword, $newpassword) {
		$oldpassword=md5($oldpassword);
		$userUqery = "select id from tbl_delivery_men where id=? and password=?";
		$login_driver = $this->conn->prepare($userUqery);
        $login_driver->bindParam(1, $userid);
        $login_driver->bindParam(2, $oldpassword);
	    $login_driver->execute();

		if($login_driver->rowCount()>0) {
			$newpassword=md5($newpassword);
			$updateQuery = "UPDATE tbl_delivery_men set password=? where id=?";
			$driver_update = $this->conn->prepare($updateQuery);
			$driver_update->bindParam(1, $newpassword);
			$driver_update->bindParam(2, $userid);
			if($driver_update->execute()) {
				return 'PASSWORD_UPDATED';
			} else {
				return 'UNABLE_TO_PROCEED';
			}			
		} else {
			return 'INVALID_OLD_PASSWORD';
		}
	}
 
	public function updateImage($userid, $picname) {
		$userUqery = "select profile_pic from tbl_delivery_men where id=?";
		$login_driver = $this->conn->prepare($userUqery);
        $login_driver->bindParam(1, $userid);
	    $login_driver->execute();
	    $driverData = $login_driver->fetch(PDO::FETCH_ASSOC);
	    
        if ($driverData['profile_pic'] != '') {
            @unlink(DELIVERY_PIC_PATH . 'large/' . $driverData['profile_pic']);
            @unlink(DELIVERY_PIC_PATH . 'thumb/' . $driverData['profile_pic']);
        }
        
        $updateQuery = "UPDATE tbl_delivery_men set profile_pic=? where id=?";
		$driver_update = $this->conn->prepare($updateQuery);
		$driver_update->bindParam(1, $picname);
		$driver_update->bindParam(2, $userid);
		if($driver_update->execute()) {
			$response = array();
			$response['profile_pic'] = DELIVERYMAN_PIC_URL.'thumb/'.$picname;
			return $response;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function updateLocation($userid, $latitude, $longitude) {
	    $time=time();
        $updateQuery = "update tbl_delivery_men set latitude=?, longitude=?, location_updated_at=? where id=?";
        $driver_update = $this->conn->prepare($updateQuery);
	    $driver_update->bindParam(1, $latitude);
	    $driver_update->bindParam(2, $longitude);
	    $driver_update->bindParam(3, $time);
		$driver_update->bindParam(4, $userid);
		$driver_update->execute();
		//print_r($driver_update->errorInfo());
		if($driver_update->rowCount()) {
			return 'SUCCESSFULLY_UPDATED';
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
 
    public function onlineOffline($userid, $status) {
		$updateQuery = "update tbl_delivery_men set onduty=? where id=?";
        $driver_update = $this->conn->prepare($updateQuery);
	    $driver_update->bindParam(1, $status);
		$driver_update->bindParam(2, $userid);
		$driver_update->execute();
		//print_r($driver_update->errorInfo());
		if($driver_update->rowCount()) {
			return 'SUCCESSFULLY_UPDATED';
		} else {
			return 'SUCCESSFULLY_UPDATED';
		}
    }
    
    public function getOrders($user_id, $branch_id, $status, $textsearch='0', $fromdate=0, $todate=0, $pageno=0) {
		$offset = DELIVERY_APP_API_RECORDS_COUNT;
        $limit = ($pageno - 1) * $offset;
		$orderQuery = "select too.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, tb.latitude as blatitude, tb.longitude as blongitude from tbl_orders too LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=too.id LEFT JOIN tbl_branches tb ON tb.id=too.branch_id where too.status=? AND delivery_man_id=?";
		if ($textsearch!='0')
			$orderQuery .=" AND too.invoice_id LIKE CONCAT('%', ?, '%')";
		if ($fromdate>0)
			$orderQuery .=" AND too.order_delivery_time>=?";
		if ($todate>0)
			$orderQuery .=" AND too.order_delivery_time<=?";
		$orderQuery .=" ORDER BY too.order_delivery_time DESC";
		if($pageno>0)
			$orderQuery .= " limit ?, ?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $status);
		$order_data->bindParam(2, $user_id);
		$j=3;
		if ($textsearch!='0') {
			$order_data->bindParam($j, $textsearch);
			$j=$j+1;
		}
		if ($fromdate>0) {
			$order_data->bindParam($j, $fromdate);
			$j=$j+1;
		}
		if ($todate>0) {
			$order_data->bindParam($j, $todate);
			$j=$j+1;
		}
		if($pageno>0) {
			$order_data->bindParam($j, $limit, PDO::PARAM_INT);
			$order_data->bindParam($j+1, $offset, PDO::PARAM_INT);
		}
		
		//print_r($order_data->errorInfo());
	    $order_data->execute();
	    if($order_data->rowCount()>0) {
			//print_r($order_data);
			$responseArr=array();
			$i=0;
	        while ($data = $order_data->fetch(PDO::FETCH_ASSOC)) {
				//print_r($data);
				//order table data
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['invoice_id'] = $data['invoice_id'];
				$responseArr[$i]['delivery_man_id'] = $data['delivery_man_id'];
				$responseArr[$i]['distance'] = $data['distance'];
				$responseArr[$i]['amount'] = $data['amount'];
				$responseArr[$i]['tax_amount'] = $data['tax_amount'];
				$responseArr[$i]['type'] = $data['type'];
				$responseArr[$i]['orderedat'] = $data['created'];
				
				//branch details
				$responseArr[$i]['branch']['id'] = $data['branch_id'];
				$responseArr[$i]['branch']['name'] = $data['bname'];
				$responseArr[$i]['branch']['mobile_code'] = $data['bmobile_code'];
				$responseArr[$i]['branch']['mobile'] = $data['bmobile'];
				$responseArr[$i]['branch']['address'] = $data['baddress'];
				$responseArr[$i]['branch']['latitude'] = $data['blatitude'];
				$responseArr[$i]['branch']['longitude'] = $data['blongitude'];
			
				//customer address details
				$responseArr[$i]['customer']['id'] = $data['customer_id'];
				$responseArr[$i]['customer']['firstname'] = $data['cust_firstname'];
				$responseArr[$i]['customer']['lastname'] = $data['cust_lastname'];
				$responseArr[$i]['customer']['mobile_code'] = $data['cust_mobile_code'];
				$responseArr[$i]['customer']['mobile'] = $data['cust_mobile'];
				$responseArr[$i]['customer']['address'] = $data['cust_address'];
				$responseArr[$i]['customer']['latitude'] = $data['cust_latitude'];
				$responseArr[$i]['customer']['longitude'] = $data['cust_longitude'];
				$responseArr[$i]['customer']['profile_pic'] = $data['cust_profile_pic']!='' ? CUSTOMER_PIC_URL.'thumb/'.$data['cust_profile_pic'] : '';
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getOrdersPages($user_id, $branch_id, $status, $textsearch='0', $fromdate=0, $todate=0) {
		$orderQuery = "select too.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic from tbl_orders too LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=too.id where too.status=? AND delivery_man_id=?";
		if ($textsearch!='0')
			$orderQuery .=" AND too.invoice_id LIKE CONCAT('%', ?, '%')";
		if ($fromdate>0)
			$orderQuery .=" AND too.order_delivery_time>=?";
		if ($todate>0)
			$orderQuery .=" AND too.order_delivery_time<=?";
		$orderQuery .=" ORDER BY too.order_delivery_time DESC";
		
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $status);
		$order_data->bindParam(2, $user_id);
		$j=3;
		if ($textsearch!='0') {
			$order_data->bindParam($j, $textsearch);
			$j=$j+1;
		}
		if ($fromdate>0) {
			$order_data->bindParam($j, $fromdate);
			$j=$j+1;
		}
		if ($todate>0) {
			$order_data->bindParam($j, $todate);
			$j=$j+1;
		}
				
		$order_data->execute();
	    //print_r($order_data->errorInfo());
        $num_rows = $order_data->rowCount();
        $totpages = ceil($num_rows / DELIVERY_APP_API_RECORDS_COUNT);
        return $totpages;
    }
    
    public function getOrdersDetails($order_id) {	
		$orderQuery = "select too.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, tb.latitude as blatitude, tb.longitude as blongitude from tbl_orders too LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=too.id LEFT JOIN tbl_branches tb ON tb.id=too.branch_id where too.id=?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $order_id);
		//print_r($branch_data->errorInfo());
	    $order_data->execute();
	    if($order_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $order_data->fetch(PDO::FETCH_ASSOC)) {
				//order table data
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['invoice_id'] = $data['invoice_id'];
				$responseArr[$i]['distance'] = $data['distance'];
				$responseArr[$i]['amount'] = $data['amount'];
				$responseArr[$i]['tax_amount'] = $data['tax_amount'];
				$responseArr[$i]['type'] = $data['type'];
				$responseArr[$i]['order_type'] = $data['order_type'];
				$responseArr[$i]['status'] = $data['status'];
				$responseArr[$i]['orderedat'] = $data['created'];
				
				//branch details
				$responseArr[$i]['branch']['id'] = $data['branch_id'];
				$responseArr[$i]['branch']['name'] = $data['bname'];
				$responseArr[$i]['branch']['mobile_code'] = $data['bmobile_code'];
				$responseArr[$i]['branch']['mobile'] = $data['bmobile'];
				$responseArr[$i]['branch']['address'] = $data['baddress'];
				$responseArr[$i]['branch']['latitude'] = $data['blatitude'];
				$responseArr[$i]['branch']['longitude'] = $data['blongitude'];
				
				//customer address details
				$responseArr[$i]['customer']['id'] = $data['customer_id'];
				$responseArr[$i]['customer']['firstname'] = $data['cust_firstname'];
				$responseArr[$i]['customer']['lastname'] = $data['cust_lastname'];
				$responseArr[$i]['customer']['mobile_code'] = $data['cust_mobile_code'];
				$responseArr[$i]['customer']['mobile'] = $data['cust_mobile'];
				$responseArr[$i]['customer']['address'] = $data['cust_address'];
				$responseArr[$i]['customer']['latitude'] = $data['cust_latitude'];
				$responseArr[$i]['customer']['longitude'] = $data['cust_longitude'];
				$responseArr[$i]['customer']['profile_pic'] = $data['cust_profile_pic']!='' ? CUSTOMER_PIC_URL.'thumb/'.$data['cust_profile_pic'] : '';
				
				//taxes details
				$taxQuery = "select title, tax, tax_amount from tbl_order_taxes where order_id=?";
				$tax_data = $this->conn->prepare($taxQuery);
				$tax_data->bindParam(1, $data['id']);
				$tax_data->execute();
				$k=0;
				while ($taxData = $tax_data->fetch(PDO::FETCH_ASSOC)) {
					$responseArr[$i]['tax'][$k]['title'] = $taxData['title'];
					$responseArr[$i]['tax'][$k]['tax'] = $taxData['tax'];
					$responseArr[$i]['tax'][$k]['tax_amount'] = $taxData['tax_amount'];
					++$k;
				}
				
				// order items
				// order items
				$itemQuery = "select toi.id as rowid, toi.item_id, toi.avg_rating, toi.is_nonveg, toi.is_new, toi.is_featured, toi.name, toi.image, toi.price_name, toi.unit_price, toi.extra_price, toi.attribute_price, toi.quantity, toi.total_price, toi.data, (select IF(count(*)>0,concat(rating, '----', ifnull(comment, '')), 'N') from tbl_item_rating_log tirl where tirl.item_id=toi.item_id AND tirl.order_id=toi.order_id) as is_rated from tbl_order_items toi where toi.order_id=?";
				$item_data = $this->conn->prepare($itemQuery);
				$item_data->bindParam(1, $data['id']);
				$item_data->execute();
				$j=0;
				while ($itemData = $item_data->fetch(PDO::FETCH_ASSOC)) {
					$rating=0;
					$comment='';
					$is_rated= $itemData['is_rated'];
					if ($itemData['is_rated']!=='N') {
						$rateArr=explode('----', $itemData['is_rated']);
						$rating = $rateArr[0];
						if(count($rateArr)>1) {
							$comment = $rateArr[1];
						}
						$is_rated='Y';
					}
					$responseArr[$i]['items'][$j]['rowid'] = $itemData['rowid'];
					$responseArr[$i]['items'][$j]['item_id'] = $itemData['item_id'];
					$responseArr[$i]['items'][$j]['avg_rating'] = $itemData['avg_rating'];
					$responseArr[$i]['items'][$j]['is_nonveg'] = $itemData['is_nonveg'];
					$responseArr[$i]['items'][$j]['is_new'] = $itemData['is_new'];
					$responseArr[$i]['items'][$j]['is_featured'] = $itemData['is_featured'];
					$responseArr[$i]['items'][$j]['item_name'] = $itemData['name'];
					$responseArr[$i]['items'][$j]['thumb'] = $itemData['image']!='' ? MENU_PIC_URL.'thumb/'.$itemData['image'] : '';
					$responseArr[$i]['items'][$j]['price_name'] = $itemData['price_name'];
					$responseArr[$i]['items'][$j]['unit_price'] = $itemData['unit_price'];
					$responseArr[$i]['items'][$j]['extra_price'] = $itemData['extra_price'];
					$responseArr[$i]['items'][$j]['attribute_price'] = $itemData['attribute_price'];
					$responseArr[$i]['items'][$j]['quantity'] = $itemData['quantity'];
					$responseArr[$i]['items'][$j]['total_price'] = $itemData['total_price'];
					$responseArr[$i]['items'][$j]['data'] = $itemData['data'];
					$responseArr[$i]['items'][$j]['is_rated'] = $is_rated;
					
					if ($rating>0) {
						$responseArr[$i]['items'][$j]['ratings']['id'] = $itemData['rowid'];
						$responseArr[$i]['items'][$j]['ratings']['name'] = $itemData['name'];
						$responseArr[$i]['items'][$j]['ratings']['image'] = $itemData['image']!='' ? MENU_PIC_URL.'thumb/'.$itemData['image'] : '';
						$responseArr[$i]['items'][$j]['ratings']['rating'] = $rating;
						$responseArr[$i]['items'][$j]['ratings']['comment'] = $comment;
					} else {
						$responseArr[$i]['items'][$j]['ratings']=NULL;
					}
					++$j;
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function changeStatus($user_id, $branch_id, $order_id, $invoice_id, $customer_id, $status, $device_id, $device_type, $ip_address) {
		if ($status=='OD') {
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND status='AD' and delivery_man_id=?";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$update_status->bindParam(3, $user_id);
		} else if($status=='DL') {
			//echo $status.'----'.$order_id.'----'.$user_id;
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND status='OD' and delivery_man_id=?";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$update_status->bindParam(3, $user_id);
		} else {
			return 'INVALID_STATUS';
		}
		$update_status->execute();
		//print_r($update_status->errorInfo());
		if($update_status->rowCount()>0) {
			$description = "Orderid = $order_id status updated to $status by driver $user_id";
			$this->saveTransactionData($order_id, $user_id, $status, 'UPDATE_ORDER_STATUS', $device_id, $device_type, $ip_address, $description);
			if($status=='DL') {
				$this->updateDriverDutyStatus($user_id, 'FREE');
				
				$orderMobileData=$this->getOrderMobileData($order_id);
				$this->sendTemplatesInSMS('deliver_order', '', $orderMobileData['mobile'], $orderMobileData['mobile_code'], $invoice_id);
				$this->sendTemplatesInMail('deliver_order_mail', $orderMobileData['firstname'], $orderMobileData['email'], $invoice_id);
			}
			$this->SendPushToBranchAdmin($order_id, $status, $invoice_id, $branch_id);
			$this->SendPushToCustomer($order_id, $user_id, $status, $invoice_id, $customer_id);
			return 'SUCCESSFULLY_UPDATED';
		} else {
			return 'UNABLE_TO_PROCEED';
		}
	}
	
	public function getPageContent($pagename) {
		$pageQuery = "SELECT title, content FROM tbl_page_contents WHERE page_name=? AND app_type='D' AND platform='M'";
		$page_data = $this->conn->prepare($pageQuery);
		$page_data->bindParam(1, $pagename);
		$page_data->execute();
		//print_r($page_data->errorInfo());
		if($page_data->rowCount()>0) {
			$output = array();
			$data = $page_data->fetch(PDO::FETCH_ASSOC);
			$output['title'] = $data['title'];
			$output['content'] = $data['content'];
			return $output;
		} else {
			return 'NO_RECORD';
		}
	}
    
    public function logout($userid, $devcie_id) {
		$onduty='N';
		$updateQuery = "update tbl_delivery_men set onduty=?, device_id=NULL, device_token=NULL, device_type=NULL, latitude=NULL, longitude=NULL where id=? AND device_id=?";
        $delivery_update = $this->conn->prepare($updateQuery);
	    $delivery_update->bindParam(1, $onduty);
		$delivery_update->bindParam(2, $userid);
		$delivery_update->bindParam(3, $devcieid);
		$delivery_update->execute();
		return 'SUCCESSFULLY_LOGGEDOUT';
    }
 
   // ====================  INTERNAL FUNCTIONS START =========================== //
   
   public function sendTemplatesInSMS($templateTitle, $otp, $mobile, $mobile_code, $orderid='') {
		$templateQuery = "select content from tbl_templates where type='S' AND title=? AND status='A' AND for_user='C'";
		$template_data = $this->conn->prepare($templateQuery);
        $template_data->bindParam(1, $templateTitle);
	    $template_data->execute();
	    // print_r($template_data->errorInfo());
		if($template_data->rowCount()) {
			$mailTemplate = $template_data->fetch(PDO::FETCH_ASSOC);

            $message= str_replace("{ORDERID}", $orderid, (str_replace("{OTP}", $otp, $mailTemplate['content'])));
			@$this->send_sms($message, $mobile, 'NO', $mobile_code);
		}
	}
   
   public function getOrderMobileData($orderid) {
		$mobileQuery = "SELECT mobile_code, mobile, firstname, email FROM tbl_order_customer_addresses WHERE order_id=?";
		$mobile_data = $this->conn->prepare($mobileQuery);
        $mobile_data->bindParam(1, $orderid);
	    $mobile_data->execute();
		if($mobile_data->rowCount()>0) {
			$data = $mobile_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
	}
   
   public function SendPushToBranchAdmin($order_id, $status, $invoice_id, $branch_id) {
		if ($status=='OD') {
			$noti_type="OUT_OF_DELIVERY";
			$message="$invoice_id - Order is out for delivery";
		} else {
			$noti_type="ORDER_DELIVERED";
			$message="$invoice_id - Order has been delivered";
		}
		$branchQuery ="SELECT GROUP_CONCAT(tu.device_token) as device_tokens FROM tbl_orders o JOIN tbl_users tu ON tu.info_id=o.branch_id AND tu.usertype='RESTAURANT' AND tu.app_login='Y' AND tu.device_token!='' where o.id=?";
		$branch_data = $this->conn->prepare($branchQuery);
		$branch_data->bindParam(1, $order_id);
	    $branch_data->execute();
	    $data = $branch_data->fetch(PDO::FETCH_ASSOC);
	    $device_tokens=explode(',', $data['device_tokens']);
	    if ($device_tokens!=NULL) {
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'BRANCH', $order_id);
		}
	}
	
	public function SendPushToCustomer($order_id, $delivery_man_id, $status, $invoice_id, $customer_id) {
		if ($status=='OD') {
			$noti_type="OUT_OF_DELIVERY";
			$message="$invoice_id - Order is out for delivery";
		} else {
			$noti_type="ORDER_DELIVERED";
			$message="$invoice_id - Order has been delivered";
		}
		$customerQuery = "SELECT GROUP_CONCAT(device_token) as device_tokens FROM tbl_customers where id=?";
		$customer_data = $this->conn->prepare($customerQuery);
		$customer_data->bindParam(1, $customer_id);
	    $customer_data->execute();
	    $data = $customer_data->fetch(PDO::FETCH_ASSOC);
	    $device_tokens=explode(',', $data['device_tokens']);
	    if ($device_tokens!=NULL) {
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'CUSTOMER', $order_id);
		}
	}
   
   public function updateDriverDutyStatus($userid, $duty_status) {
		$updateQuery = "update tbl_delivery_men set duty_status=? where id=?";
        $delivery_update = $this->conn->prepare($updateQuery);
        $delivery_update->bindParam(1, $duty_status);
	    $delivery_update->bindParam(2, $userid);
		$delivery_update->execute();
		return 'SUCCESSFULLY_UPDATED';
    }
   
   public function saveTransactionData($order_id, $user_id, $status, $action, $device_id, $device_type, $ip_address, $description) {
		$created=time();
		$insertQuery = "INSERT INTO tbl_order_transactions SET order_id=?, action=?, status=?, description=?, device_id=?, device_type=?, ip_address=?, created=?, createdby=?";
		$insert_transaction = $this->conn->prepare($insertQuery);
		$insert_transaction->bindParam(1, $order_id);
		$insert_transaction->bindParam(2, $action);
		$insert_transaction->bindParam(3, $status);
		$insert_transaction->bindParam(4, $description);
		$insert_transaction->bindParam(5, $device_id);
		$insert_transaction->bindParam(6, $device_type);
		$insert_transaction->bindParam(7, $ip_address);
		$insert_transaction->bindParam(8, $created);
		$insert_transaction->bindParam(9, $user_id);
		$insert_transaction->execute();
		//print_r($prepare_user->errorInfo());
		if($insert_transaction->rowCount()>0) {
			return TRUE;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
   
	public function getCustomersDeviceToken($order_id) {
	    $save_user = "SELECT id from tbl_customers WHERE email ='$email'";
		$user_select = $this->conn->prepare($selectQuery);
	    $user_select->bindParam(1, $order_id);
		$user_select->execute();
        return $user_select->rowCount()>0;
	}
    
    public function generateRandomPassword($length) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for ($i = 0; $i <= $length; $i++) {
            $num = rand(0, strlen($characters) - 1);
            $output[] = $characters[$num];
        }
        return implode($output);
    }
    
    public function send_sms($text='', $to='', $issos='NO', $country_code='+91'){
		//$this->includeGlobalSiteSetting();
		$this->includeTwilioLib();
		if($issos=='YES') {
			$to = $to;
		} else {
			$to = $country_code.$to;
		}
		$sender = GLOBAL_TWILIO_NUMBER;
        $sid = GLOBAL_TWILIO_SID;
        $token = GLOBAL_TWILIO_TOKEN;

        $client = new Twilio\Rest\Client($sid, $token);
		try {
			$resp = $client->messages->create($to, array( 'from' => "$sender", 'body' => "$text" ));
			//print_r($resp);
		}  catch(Twilio\Exceptions\RestException $e) {
			//print_r($e); die;
		}
    }
    
    public function includeGlobalSiteSetting() {
		$filesArr=get_required_files();
	    $searchString = GLOBAL_SITE_SETTING_PHP;
	    if(!in_array($searchString, $filesArr)) {
		    require GLOBAL_SITE_SETTING_PHP; 
		}
	}
	
	public function includeDomPdfLib() {
		$filesArr=get_required_files();
		$searchString=DOM_PDF_PATH;
		if(!in_array($searchString, $filesArr)) {
			require DOM_PDF_PATH;
		}
	}
	
	public function includePaypalLib() {
		$filesArr=get_required_files();
		$searchString=PAYPAL_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require PAYPAL_LIB_PATH;
		}
	}
	
	public function includePusherLib() {
		$filesArr=get_required_files();
		$searchString=PUSHER_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require PUSHER_LIB_PATH;
		}
	}
	
    public function includeTwilioLib() {
		$filesArr=get_required_files();
		$searchString=TWILIO_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require TWILIO_LIB_PATH;
		}
	}
	
	public function includeSMTPMailerLib() {
		$filesArr=get_required_files();
		$searchString=PHPMAILER_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require PHPMAILER_LIB_PATH;
		}
	}
	
	public function sendTemplatesInMail($mailTitle, $toName, $toEmail, $invoice_id=''){
		//$this->includeGlobalSiteSetting();
		$templateQuery = "select subject, content from templates where type='E' AND title=? AND status='A'"; 
		$template_data = $this->conn->prepare($templateQuery);
        $template_data->bindParam(1, $mailTitle);
	    $template_data->execute();
	    // print_r($template_data->errorInfo());
		if($template_data->rowCount()) {
			$mailTemplate = $template_data->fetch(PDO::FETCH_ASSOC);
			$subject= str_replace("{ORDER_ID}", $invoice_id, str_replace("{CUSTOMER_FIRST_NAME}", $toName, $mailTemplate['subject']));
            $message= str_replace("{ORDER_ID}", $invoice_id, str_replace("{CUSTOMER_FIRST_NAME}", $toName, $mailTemplate['content']));
            $this->sendSMTPMail($subject, $message, $toEmail, $toName, SMTP_FROM_NAME, SMTP_FROM_EMAIL);
		}
	}
	
	public function sendSMTPMail($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
		$this->includeSMTPMailerLib();
		$mail = new PHPMailer(true);                    // Passing `true` enables exceptions
		try {
			$mail->SMTPDebug = 0;                       // Enable verbose debug output
			$mail->isSMTP();                            // Set mailer to use SMTP
			$mail->Host = SMTP_SERVER;  				// Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                     // Enable SMTP authentication
			$mail->Username = SMTP_USERNAME;            // SMTP username
			$mail->Password = SMTP_PASSWORD;            // SMTP password
			$mail->SMTPSecure = SMTP_SECURE;            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = SMTP_PORT;                    // TCP port to connect to
			//Recipients
			$mail->setFrom($sender_from_email, $sender_from_name);
			$mail->addAddress($receiver_email, $receiver_name);     // Add a recipient
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $message;
			$mail->AltBody = $message;
			$mail->send();
		} catch (Exception $e) {
			return false;
		}
	}
	
	public function sendSMTPMailOld($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
		//require_once PHPMAILER_LIB;
		$this->includeSMTPMailerLib();
		$mail = new PHPMailer(true);                    // Passing `true` enables exceptions
		try {
			$mail->SMTPDebug = 0;                       // Enable verbose debug output
			$mail->isSMTP();                            // Set mailer to use SMTP
			$mail->Host = SMTP_SERVER;  				// Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                     // Enable SMTP authentication
			$mail->Username = SMTP_USERNAME;            // SMTP username
			$mail->Password = SMTP_PASSWORD;            // SMTP password
			$mail->SMTPSecure = SMTP_SECURE;            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = SMTP_PORT;                    // TCP port to connect to

			//Recipients
			$mail->setFrom($sender_from_email, $sender_from_name);
			$mail->addAddress($receiver_name, $receiver_email);     // Add a recipient
			
			//Attachments
			if(count($filepath)>0){
				foreach($filepath as $attachment){
					$mail->addAttachment($attachment);
				}
			}
			
			//Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $message;
			$mail->AltBody = $message;
			$mail->send();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	public function selectFcmKey($send_to) {
		if ($send_to=='DELIVERYMAN') {
			$fcmKey=FCM_KEY_FOR_DELIVERYMAN;
		} else if ($send_to=='CUSTOMER') {
			$fcmKey=FCM_KEY_FOR_CUSTOMER;
		} else {
			$fcmKey=FCM_KEY_FOR_BRANCH;
		}
		return $fcmKey;
	}
	
    public function sendFCMPushNotification($message, $registration_ids, $alert_message='EL-GUERO2 NOTIFICATIONS', $noti_type, $send_to, $order_id=0) {
		/* $send_to=DELIVERYMAN / BRANCH / CUSTOMER */
		//$noti_message=json_encode(array('message'=>$message, 'noti_type'=>$noti_type));
		$url = FIRE_BASE_URL;       
        $API_KEY = $this->selectFcmKey($send_to);
        $fcmurl = FIRE_BASE_URL;
        $sound = "default";
        //$message = json_decode($message, TRUE);
        $fields = array(
            "registration_ids" => $registration_ids,
            "data" => array(
                "title" => "EL-GUERO2",
                "body" => $message,
                "noti_type" => $noti_type,
                'order_id' =>$order_id
            ),
            "notification" => array(
                "title" => "EL-GUERO2",
                "body" => $message,
                "sound" => $sound,
                "priority" => "high"
            ),
            "priority" => "high"
        );
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
   // ====================  INTERNAL FUNCTIONS END =========================== //
}
 
?>
