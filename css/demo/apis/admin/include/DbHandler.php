<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class DbHandler
{
    private $conn;
    function __construct()
	{
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
		$userUqery = "select id, info_id from tbl_users where (email=? OR mobile=?) AND (password=? OR password=?) AND status='A' AND app_login='Y' AND usertype='RESTAURANT'";
		$login_admin = $this->conn->prepare($userUqery);
        $login_admin->bindParam(1, $username);
        $login_admin->bindParam(2, $username);
        $login_admin->bindParam(3, $password);
        $login_admin->bindParam(4, $md5password);
	    $login_admin->execute();
	    if($login_admin->rowCount()>0) {
			$adminData = $login_admin->fetch(PDO::FETCH_ASSOC);
	        return $adminData;
		}
    }
    
    public function login($username, $password, $device_id, $device_token, $device_type, $ipaddress) {            
		$md5password = md5($password);				
		$userUqery = "select tu.*, tb.name as br_name, tb.mobile as br_mobile, tb.mobile_code as br_mobile_code,  tb.address_landmark br_address_landmark, tb.address as br_address, tb.latitude as br_latitude, tb.longitude as br_longitude, tb.logo as br_logo from tbl_users tu JOIN tbl_branches tb ON tb.id=tu.info_id where (tu.email=? OR tu.mobile=?) AND tu.app_login='Y' AND tu.usertype='RESTAURANT'";
		$login_admin = $this->conn->prepare($userUqery);
        $login_admin->bindParam(1, $username);
        $login_admin->bindParam(2, $username);
	    $login_admin->execute();
	    //print_r($login_admin->errorInfo());
	    if($login_admin->rowCount()>0) {
			$adminData = $login_admin->fetch(PDO::FETCH_ASSOC);
			if(($adminData['password']!=$password) && ($adminData['password']!=$md5password)) {
				return 'INVALID_USERNAME_PASSWORD';
			}
			if($adminData['status'] == 'D') { 
				return 'ACCOUNT_DEACTVATED'; 
			} else {
				$time=time();
				$updateQuery = "UPDATE tbl_users set device_id=?, device_type=?, device_token=?, lastlogin=? where id=?";
				$admin_update = $this->conn->prepare($updateQuery);
				$admin_update->bindParam(1, $device_id);
				$admin_update->bindParam(2, $device_type);
				$admin_update->bindParam(3, $device_token);
				$admin_update->bindParam(4, $time);
				$admin_update->bindParam(5, $adminData['id']);
				$admin_update->execute();
				
				$responseArr=array();
				$responseArr['id'] = $adminData['id'];
				$responseArr['firstname'] = $adminData['firstname'];
				$responseArr['lastname'] = $adminData['lastname'];
				$responseArr['address'] = $adminData['address'];
				$responseArr['mobile_code'] = $adminData['mobile_code'];
				$responseArr['mobile'] = $adminData['mobile'];
				$responseArr['email'] = $adminData['email'];
				$responseArr['gender'] = $adminData['gender'];
				$responseArr['profile_pic'] = $adminData['profile_pic']=='' || $adminData['profile_pic']===NULL ? '' : ADMIN_PIC_URL.'thumb/'.$adminData['profile_pic'];
				$responseArr['password'] = $adminData['password'];
				
				//branch details
				$responseArr['branch']['id'] = $adminData['info_id'];
				$responseArr['branch']['name'] = $adminData['br_name'];
				$responseArr['branch']['mobile'] = $adminData['br_mobile_code'];
				$responseArr['branch']['mobile'] = $adminData['br_mobile'];
				$responseArr['branch']['address_landmark'] = $adminData['br_address_landmark'];
				$responseArr['branch']['address'] = $adminData['br_address'];
				$responseArr['branch']['latitude'] = $adminData['br_latitude'];
				$responseArr['branch']['longitude'] = $adminData['br_longitude'];
				$responseArr['branch']['logo'] = $adminData['br_logo']!='' ? BRANCH_PIC_URL.'thumb/'.$adminData['br_logo'] : '';
				
				return $responseArr; 
			}
		} else {
			return 'USERNAME_NOT_EXIST';
		}		
    }
    
    public function changePassword($userid, $oldpassword, $newpassword) {
		$oldpassword=md5($oldpassword);
		$userUqery = "select id from tbl_users where id=? and password=?";
		$login_admin = $this->conn->prepare($userUqery);
        $login_admin->bindParam(1, $userid);
        $login_admin->bindParam(2, $oldpassword);
	    $login_admin->execute();

		if($login_admin->rowCount()>0) {
			$newpassword=md5($newpassword);
			$updateQuery = "UPDATE tbl_users set password=? where id=?";
			$admin_update = $this->conn->prepare($updateQuery);
			$admin_update->bindParam(1, $newpassword);
			$admin_update->bindParam(2, $userid);
			if($admin_update->execute()) {
				return 'PASSWORD_UPDATED';
			} else {
				return 'UNABLE_TO_PROCEED';
			}			
		} else {
			return 'INVALID_OLD_PASSWORD';
		}
	}
 
	public function updateImage($userid, $picname) {
		$userUqery = "select profile_pic from tbl_users where id=?";
		$login_admin = $this->conn->prepare($userUqery);
        $login_admin->bindParam(1, $userid);
	    $login_admin->execute();
	    $adminData = $login_admin->fetch(PDO::FETCH_ASSOC);
	    
        if ($adminData['profile_pic'] != '') {
            @unlink(ADMIN_PIC_PATH . 'large/' . $adminData['profile_pic']);
            @unlink(ADMIN_PIC_PATH . 'thumb/' . $adminData['profile_pic']);
        }
        
        $updateQuery = "UPDATE tbl_users set profile_pic=? where id=?";
		$admin_update = $this->conn->prepare($updateQuery);
		$admin_update->bindParam(1, $picname);
		$admin_update->bindParam(2, $userid);
		if($admin_update->execute()) {
			return ADMIN_PIC_URL.'thumb/'.$picname;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function getOrders($user_id, $branch_id, $status, $type, $delivery=0, $textsearch='0', $orderfromdate=0, $ordertodate=0, $createdfromdate=0, $createdtodate=0, $pageno=0) {
		$offset = BRANCH_APP_API_RECORDS_COUNT;
        $limit = ($pageno - 1) * $offset;
		$orderQuery = "select o.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, todm.firstname as del_firstname, todm. lastname as del_lastname, todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, tb.latitude as blatitude, tb.longitude as blongitude from tbl_orders o LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=o.id LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id AND todm.delivery_man_id =o.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=o.branch_id where o.branch_id=? AND o.status=?";
		if($type!='A')
			$orderQuery .= " AND o.order_type=?";
		if($delivery!='A')
			$orderQuery .= " AND o.type=?";
		if ($textsearch!='0')
			$orderQuery .=" AND (o.invoice_id LIKE CONCAT('%', ?, '%') OR toca.firstname LIKE CONCAT('%', ?, '%') OR toca.mobile LIKE CONCAT('%', ?, '%'))";
		if ($orderfromdate>0)
			$orderQuery .=" AND o.order_delivery_time>=?";
		if ($ordertodate>0)
			$orderQuery .=" AND o.order_delivery_time<=?";
		if ($createdfromdate>0)
			$orderQuery .=" AND o.created>=?";
		if ($createdtodate>0)
			$orderQuery .=" AND o.created<=?";
		$orderQuery .=" ORDER BY o.order_delivery_time DESC";
		if($pageno>0)
			$orderQuery .= " limit ?, ?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $branch_id);
		$order_data->bindParam(2, $status);
		$j=3;
		if($type!='A') {
			$order_data->bindParam($j, $type);
			$j=$j+1;
		}
		if($delivery!='A') {
			$order_data->bindParam($j, $delivery);
			$j=$j+1;
		}
		if ($textsearch!='0') {
			$order_data->bindParam($j, $textsearch);
			$order_data->bindParam($j+1, $textsearch);
			$order_data->bindParam($j+2, $textsearch);
			$j=$j+3;
		}
		if ($orderfromdate>0) {
			$order_data->bindParam($j, $orderfromdate);
			$j=$j+1;
		}
		if ($ordertodate>0) {
			$order_data->bindParam($j, $ordertodate);
			$j=$j+1;
		}
		if ($createdfromdate>0) {
			$order_data->bindParam($j, $createdfromdate);
			$j=$j+1;
		}
		if ($createdtodate>0) {
			$order_data->bindParam($j, $createdtodate);
			$j=$j+1;
		}
		if($pageno>0) {
			$order_data->bindParam($j, $limit, PDO::PARAM_INT);
			$order_data->bindParam($j+1, $offset, PDO::PARAM_INT);
		}
		//print_r($order_data->errorInfo());
	    $order_data->execute();
	    if($order_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $order_data->fetch(PDO::FETCH_ASSOC)) {
				//order table data
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['invoice_id'] = $data['invoice_id'];
				$responseArr[$i]['delivery_man_id'] = $data['delivery_man_id'];
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
				
				//delivery man details
				if($data['delivery_man_id']==0) {
					$responseArr[$i]['delivery_man']=NULL;
				} else {
					$responseArr[$i]['delivery_man']['id'] = $data['delivery_man_id'];
					$responseArr[$i]['delivery_man']['firstname'] = $data['del_firstname'];
					$responseArr[$i]['delivery_man']['lastname'] = $data['del_lastname'];
					$responseArr[$i]['delivery_man']['mobile_code'] = $data['del_mobile_code'];
					$responseArr[$i]['delivery_man']['mobile'] = $data['del_mobile'];
					$responseArr[$i]['delivery_man']['profile_pic'] = $data['del_profile_pic']!='' ? DELIVERYMAN_PIC_URL.'thumb/'.$data['del_profile_pic'] : '';
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getOrdersPages($user_id, $branch_id, $status, $type, $delivery=0, $textsearch='0', $orderfromdate=0, $ordertodate=0, $createdfromdate=0, $createdtodate=0) {
		$orderQuery = "select o.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, todm.firstname as del_firstname, todm. lastname as del_lastname, todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic from tbl_orders o LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=o.id LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id AND todm.delivery_man_id =o.delivery_man_id where o.branch_id=? AND o.status=?";
		if($type!='A')
			$orderQuery .= " AND o.order_type=?";
		if($delivery!='A')
			$orderQuery .= " AND o.type=?";
		if ($textsearch!='0')
			$orderQuery .=" AND (o.invoice_id LIKE CONCAT('%', ?, '%') OR toca.firstname LIKE CONCAT('%', ?, '%') OR toca.mobile LIKE CONCAT('%', ?, '%'))";
		if ($orderfromdate>0)
			$orderQuery .=" AND o.order_delivery_time>=?";
		if ($ordertodate>0)
			$orderQuery .=" AND o.order_delivery_time<=?";
		if ($createdfromdate>0)
			$orderQuery .=" AND o.created>=?";
		if ($createdtodate>0)
			$orderQuery .=" AND o.created<=?";
		$orderQuery .=" ORDER BY o.order_delivery_time DESC";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $branch_id);
		$order_data->bindParam(2, $status);
		$j=3;
		if($type!='A') {
			$order_data->bindParam($j, $type);
			$j=$j+1;
		}
		if($delivery!='A') {
			$order_data->bindParam($j, $delivery);
			$j=$j+1;
		}
		if ($textsearch!='0') {
			$order_data->bindParam($j, $textsearch);
			$order_data->bindParam($j+1, $textsearch);
			$order_data->bindParam($j+2, $textsearch);
			$j=$j+3;
		}
		if ($orderfromdate>0) {
			$order_data->bindParam($j, $orderfromdate);
			$j=$j+1;
		}
		if ($ordertodate>0) {
			$order_data->bindParam($j, $ordertodate);
			$j=$j+1;
		}
		if ($createdfromdate>0) {
			$order_data->bindParam($j, $createdfromdate);
			$j=$j+1;
		}
		if ($createdtodate>0) {
			$order_data->bindParam($j, $createdtodate);
			$j=$j+1;
		}
		$order_data->execute();
	    //print_r($order_data->errorInfo());
        $num_rows = $order_data->rowCount();
        $totpages = ceil($num_rows / BRANCH_APP_API_RECORDS_COUNT);
        return $totpages;
    }
    
    public function getOrdersDetails($order_id) {	
		$orderQuery = "select tos.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, todm.firstname as del_firstname, todm.lastname as del_lastname, todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, tb.latitude as blatitude, tb.longitude as blongitude from tbl_orders as tos LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=tos.id LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=tos.id AND todm.delivery_man_id=tos.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=tos.branch_id where tos.id=?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $order_id);
		//print_r($order_data->errorInfo());
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
				
				//delivery man details
				if($data['delivery_man_id']==0) {
					$responseArr[$i]['delivery_man']=NULL;
				} else {
					$responseArr[$i]['delivery_man']['id'] = $data['delivery_man_id'];
					$responseArr[$i]['delivery_man']['firstname'] = $data['del_firstname'];
					$responseArr[$i]['delivery_man']['lastname'] = $data['del_lastname'];
					$responseArr[$i]['delivery_man']['mobile_code'] = $data['del_mobile_code'];
					$responseArr[$i]['delivery_man']['mobile'] = $data['del_mobile'];
					$responseArr[$i]['delivery_man']['profile_pic'] = $data['del_profile_pic']!='' ? DELIVERYMAN_PIC_URL.'thumb/'.$data['del_profile_pic'] : '';
				}
				
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
    
    public function getDeliveryMen($branch_id, $status) {	
		$deliveryQuery ="SELECT tdm.id, tdm.firstname, tdm.lastname, tdm.mobile_code, tdm.mobile, tdm.email, tdm.address, tdm.gender, tdm.profile_pic FROM tbl_delivery_men tdm where tdm.branch_id=? AND status='A'";
		if($status!='ALL')
			$deliveryQuery .=" AND islogin='Y' AND onduty='Y' AND duty_status=?";
		$delivery_data = $this->conn->prepare($deliveryQuery);
		$delivery_data->bindParam(1, $branch_id);
		if($status!='ALL')
			$delivery_data->bindParam(2, $status);
		//print_r($branch_data->errorInfo());
	    $delivery_data->execute();
	    if($delivery_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $delivery_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['firstname'] = $data['firstname'];
				$responseArr[$i]['lastname'] = $data['lastname'];
				$responseArr[$i]['mobile_code'] = $data['mobile_code'];
				$responseArr[$i]['mobile'] = $data['mobile'];
				$responseArr[$i]['email'] = $data['email'];
				$responseArr[$i]['address'] = $data['address'];
				$responseArr[$i]['gender'] = $data['gender'];
				$responseArr[$i]['thumb'] = $data['profile_pic']!='' ? DELIVERYMAN_PIC_URL.'thumb/'.$data['profile_pic'] : '';
				$responseArr[$i]['big'] = $data['profile_pic']!='' ? DELIVERYMAN_PIC_URL.'big/'.$data['profile_pic'] : '';
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }


     public function getDeliveryMen_admin($branch_id, $status) {	
		$deliveryQuery ="SELECT tdm.id, tdm.firstname, tdm.lastname, tdm.mobile_code, tdm.mobile, tdm.email, tdm.address, tdm.gender, tdm.profile_pic FROM tbl_delivery_men tdm where tdm.branch_id=? AND status='A'";
		if($status!='ALL')
			$deliveryQuery .=" AND islogin='Y' AND onduty='Y' AND duty_status=?";
		$delivery_data = $this->conn->prepare($deliveryQuery);
		$delivery_data->bindParam(1, $branch_id);
		if($status!='ALL')
			$delivery_data->bindParam(2, $status);
		//print_r($branch_data->errorInfo());
	    $delivery_data->execute();
	    if($delivery_data->rowCount()>0) {
			$responseArr=array();
			$html="";
			$html.="<option value=''>Select Driver</option>";

	        while ($data = $delivery_data->fetch(PDO::FETCH_ASSOC)) {
				
				$html.="<option value='".$data['id']."'>".$data['firstname']." ".$data['lastname']."</option>";
				
			 }
			 return $html;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getDeliverManOrders($user_id, $branch_id, $status) {	
		$orderQuery = "SELECT * FROM tbl_orders to JOIN tbl_order_delivery_men todm ON to.id=todm.id where todm.delivery_man_id=? AND to.status='AD'";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $delivery_man_id);
		//print_r($branch_data->errorInfo());
	    $order_data->execute();
	    if($order_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $order_data->fetch(PDO::FETCH_ASSOC)) {
				//order table data
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['invoice_id'] = $data['invoice_id'];
				$responseArr[$i]['delivery_man_id'] = $data['delivery_man_id'];
				$responseArr[$i]['distance'] = $data['distance'];
				$responseArr[$i]['amount'] = $data['amount'];
				$responseArr[$i]['tax_amount'] = $data['tax_amount'];
				$responseArr[$i]['type'] = $data['type'];
				$responseArr[$i]['order_type'] = $data['order_type'];
				$responseArr[$i]['orderedat'] = $data['created'];
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function changeStatus($user_id, $order_id, $invoice_id, $customer_id, $status, $delivery_man_id, $device_id, $device_type, $ip_address) {
		if ($status=='A') {
			/* Taking payment when admin accepting the order -- shifted to customer section
			 * $makePayment=$this->makePayment($order_id, $device_id, $device_type, $ip_address);
			if ($makePayment=='FAILED') {
				return 'PAYMENT_FAILED';
			}*/
			
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND (status='N' OR status='D')";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$noti_type = "ORDER_ACCEPTED";
			$cust_message="$invoice_id - Order has been Confirmed";
			$admin_message="$invoice_id - Order has been Confirmed";
		} else if ($status=='D') {
			$refundPayment=$this->refundPayment($order_id, $device_id, $device_type, $ip_address, $user_id);
			if ($refundPayment=='FAILED_TO_REFUND') {
				return 'FAILED_TO_REFUND';
			}
			
			$orderMobileData=$this->getOrderMobileData($order_id);
			$this->sendTemplatesInSMS('order_cancelled_by_branch_admin', '', $orderMobileData['mobile'], $orderMobileData['mobile_code'], $invoice_id);
			$this->sendTemplatesInMail('order_cancelled_by_branch_admin_mail', $orderMobileData['firstname'], $orderMobileData['email'], $invoice_id);
			
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND (status='N' OR status='A')";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$noti_type = "ORDER_DECLINED";
			$cust_message="$invoice_id - Order has been Cancelled";
			$admin_message="$invoice_id - Order has been Cancelled";
		} else if ($status=='P') {
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND status='A'";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$noti_type = "ORDER_PREPARED";
			$cust_message="$invoice_id - Order has been Prepared";
			$admin_message="$invoice_id - Order has been Prepared";
		} else if ($status=='AD') {
			$this->freePreviousAssignedDeliveryman($order_id, $invoice_id, $delivery_man_id);
			
			$updateQuery = "UPDATE tbl_orders SET status=?, delivery_man_id=? where id=? AND (status='P' OR status='AD')";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $delivery_man_id);
			$update_status->bindParam(3, $order_id);
			
			$updateDQuery = "UPDATE tbl_delivery_men SET duty_status='BUZY' where id=?";
			$update_deliveryman = $this->conn->prepare($updateDQuery);
			$update_deliveryman->bindParam(1, $delivery_man_id);
			$update_deliveryman->execute();
		}  else if ($status=='PU') {
			$updateQuery = "UPDATE tbl_orders SET status=? where id=? AND status='P'";
			$update_status = $this->conn->prepare($updateQuery);
			$update_status->bindParam(1, $status);
			$update_status->bindParam(2, $order_id);
			$noti_type = "ORDER_PICKUP";
			$cust_message="$invoice_id - Order has been Picked UP";
			$admin_message="$invoice_id - Order has been Picked UP";
		}
		$update_status->execute();
		//print_r($update_status->errorInfo());
		if($update_status->rowCount()>0) {
			if ($delivery_man_id>0) {
				$this->saveDeliveryManData($order_id, $delivery_man_id);
				$cust_message="$invoice_id - Order has been assigned to delivery man";
				$admin_message="$invoice_id - Order has been assigned to delivery man";
				$noti_type = "ORDER_ASSIGNED";
			}
				
			$description = "invoice_id-$invoice_id status updated to $status";
			if ($delivery_man_id>0)
				$description .= " AND assigned to deliverymanid - delivery_man_id";
			$this->saveTransactionData($order_id, $user_id, $status, 'UPDATE_ORDER_STATUS', $device_id, $device_type, $ip_address, $description);
						
			$this->SendPushToCustomer($customer_id, $cust_message, $noti_type, $order_id);
			$this->SendPushToBranchAdmin($user_id, $order_id, $admin_message, $noti_type, $order_id);
			
			$deliveryManData=$this->getDeliveryManByOrderId($order_id);
			$responseArr['status'] = $status;
			$responseArr['delivery_man'] = $deliveryManData;
			return $responseArr;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
	}
	
	public function getPageContent($pagename) {
		$pageQuery = "SELECT title, content FROM tbl_page_contents WHERE page_name=? AND app_type='B' AND platform='M'";
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
		$updateQuery = "update tbl_users set device_id=NULL, device_token=NULL, device_type=NULL where id=? AND device_id=?";
        $admin_update = $this->conn->prepare($updateQuery);
		$admin_update->bindParam(1, $userid);
		$admin_update->bindParam(2, $devcie_id);
		$admin_update->execute();
		//print_r($admin_update->errorInfo());
		return 'SUCCESSFULLY_LOGGEDOUT';
    }
    
    public function getDashboardData($branch_id) {
		//$dashboardQuery = "SELECT ifnull(count(*),0) as tot, status from tbl_orders where branch_id=? group by if (status='N',order_type ,status)";
		$dashboardQuery = "SELECT ifnull(count(*),0) as tot, if (status='N',order_type ,status) as status from tbl_orders where branch_id=? AND status!='PP' group by if (status='N', order_type, status)";
		$dashboard_data = $this->conn->prepare($dashboardQuery);
		$dashboard_data->bindParam(1, $branch_id);
		$dashboard_data->execute();
		//print_r($dashboard_data->errorInfo());
		if($dashboard_data->rowCount()>0) {
			$output = array();
			$i=0;
			while ($data = $dashboard_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$data['status']] = $data['tot'];
				$i++;
			}
			//print_r($output);
			$output=$this->addMissingKeys($output);
			$output=$this->reOrderArray($output);
			return $output;
		} else {
			return 'NO_RECORD';
		}
	}
	
	

	public function getEvents($branch_id, $textsearch='0', $status='0', $eventfromdate=0, $eventtodate=0, $postedfromdate=0, $postedtodate=0, $pageno=0) {
		$offset = BRANCH_APP_API_RECORDS_COUNT;
        $limit = ($pageno - 1) * $offset;
		$eventQuery ="SELECT tce.*, tc.firstname, tc.lastname, tc.mobile_code, tc.mobile, tc.profile_pic FROM tbl_customer_events tce LEFT JOIN tbl_customers tc ON tc.id=tce.customer_id where tce.branch_id=?";
		if ($textsearch!='0')
			$eventQuery .=" AND (tce.name LIKE CONCAT('%', ?, '%') OR tce.mobile LIKE CONCAT('%', ?, '%') OR tce.occasion LIKE CONCAT('%', ?, '%') OR tce.event LIKE CONCAT('%', ?, '%'))";
		if ($status!='0')
			$eventQuery .=" AND tce.status=?";
		if ($eventfromdate>0)
			$eventQuery .=" AND tce.event_date>=?";
		if ($eventtodate>0)
			$eventQuery .=" AND tce.event_date<=?";
		if ($postedfromdate>0)
			$eventQuery .=" AND tce.created>=?";
		if ($postedtodate>0)
			$eventQuery .=" AND tce.created<=?";
		$eventQuery .=" order by tce.status asc, tce.created desc, tce.updated desc";
		if($pageno>0)
			$eventQuery .= " limit ?, ?";
		$event_data = $this->conn->prepare($eventQuery);
		$event_data->bindParam(1, $branch_id);
		$j=2;
		if ($textsearch!='0') {
			$event_data->bindParam($j, $textsearch);
			$event_data->bindParam($j+1, $textsearch);
			$event_data->bindParam($j+2, $textsearch);
			$event_data->bindParam($j+3, $textsearch);
			$j=$j+1;
		}
		if ($status!='0') {
			$event_data->bindParam($j, $status);
			$j=$j+1;
		}
		if ($eventfromdate>0) {
			$event_data->bindParam($j, $eventfromdate);
			$j=$j+1;
		}
		if ($eventtodate>0) {
			$event_data->bindParam($j, $eventtodate);
			$j=$j+1;
		}
		if ($postedfromdate>0) {
			$event_data->bindParam($j, $postedfromdate);
			$j=$j+1;
		}
		if ($postedtodate>0) {
			$event_data->bindParam($j, $postedtodate);
			$j=$j+1;
		}
		if($pageno>0) {
			$event_data->bindParam($j, $limit, PDO::PARAM_INT);
			$event_data->bindParam($j+1, $offset, PDO::PARAM_INT);
		}
		//print_r($event_data->errorInfo());
	    $event_data->execute();
	    if($event_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $event_data->fetch(PDO::FETCH_ASSOC)) {
				//print_r($data);
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['mobile'] = $data['mobile'];
				$responseArr[$i]['no_of_guest'] = $data['no_of_guest'];
				$responseArr[$i]['occasion'] = $data['occasion'];
				$responseArr[$i]['event'] = $data['event'];
				$responseArr[$i]['event_date'] = $data['event_date'];
				$responseArr[$i]['status'] = $data['status'];
				$responseArr[$i]['created'] = $data['created'];
				
				//customer details
				$responseArr[$i]["customer"]['id'] = $data['customer_id'];
				$responseArr[$i]["customer"]['firstname'] = $data['firstname'];
				$responseArr[$i]["customer"]['lastname'] = $data['lastname'];
				$responseArr[$i]["customer"]['mobile_code'] = $data['mobile_code'];
				$responseArr[$i]["customer"]['mobile'] = $data['mobile'];
				$responseArr[$i]["customer"]['thumb'] = $data['profile_pic']!='' ? CUSTOMER_PIC_URL.'thumb/'.$data['profile_pic'] : '';
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getEventsPages($branch_id, $textsearch='0', $status='0', $eventfromdate=0, $eventtodate=0, $postedfromdate=0, $postedtodate=0) {
		$eventQuery ="SELECT tce.*, tc.firstname, tc.lastname, tc.mobile_code, tc.mobile, tc.profile_pic FROM tbl_customer_events tce LEFT JOIN tbl_customers tc ON tc.id=tce.customer_id where tce.branch_id=?";
		if ($textsearch!='0')
			$eventQuery .=" AND (tce.name LIKE CONCAT('%', ?, '%') OR tce.mobile LIKE CONCAT('%', ?, '%') OR tce.occasion LIKE CONCAT('%', ?, '%') OR tce.event LIKE CONCAT('%', ?, '%'))";
		if ($status!='0')
			$eventQuery .=" AND tce.status=?";
		if ($eventfromdate>0)
			$eventQuery .=" AND tce.event_date>=?";
		if ($eventtodate>0)
			$eventQuery .=" AND tce.event_date<=?";
		if ($postedfromdate>0)
			$eventQuery .=" AND tce.created>=?";
		if ($postedtodate>0)
			$eventQuery .=" AND tce.created<=?";
		$eventQuery .=" order by tce.status asc, tce.created desc, tce.updated desc";
		$event_data = $this->conn->prepare($eventQuery);
		$event_data->bindParam(1, $branch_id);
		$j=2;
		if ($textsearch!='0') {
			$event_data->bindParam($j, $textsearch);
			$event_data->bindParam($j+1, $textsearch);
			$event_data->bindParam($j+2, $textsearch);
			$event_data->bindParam($j+3, $textsearch);
			$j=$j+1;
		}
		if ($status!='0') {
			$event_data->bindParam($j, $status);
			$j=$j+1;
		}
		if ($eventfromdate>0) {
			$event_data->bindParam($j, $eventfromdate);
			$j=$j+1;
		}
		if ($eventtodate>0) {
			$event_data->bindParam($j, $eventtodate);
			$j=$j+1;
		}
		if ($postedfromdate>0) {
			$event_data->bindParam($j, $postedfromdate);
			$j=$j+1;
		}
		if ($postedtodate>0) {
			$event_data->bindParam($j, $postedtodate);
			$j=$j+1;
		}
        $event_data->execute();
	    //print_r($event_data->errorInfo());
        $num_rows = $event_data->rowCount();
        $totpages = ceil($num_rows / BRANCH_APP_API_RECORDS_COUNT);
        return $totpages;
    }
    
    public function getEventDetails($event_id) {	
		$eventQuery ="SELECT tce.*, tc.firstname, tc.lastname, tc.mobile_code, tc.mobile, tc.email, tc.profile_pic, tu.firstname as rfirstname, tu.lastname as rlastname, tu.mobile_code as rmobile_code, tu.mobile as rmobile, tu.profile_pic as rprofile_pic FROM tbl_customer_events tce LEFT JOIN tbl_customers tc ON tc.id=tce.customer_id LEFT JOIN tbl_users tu ON tu.id=tce.reply_by where tce.id=? order by tce.status asc, tce.created desc, tce.updated desc";
		$event_data = $this->conn->prepare($eventQuery);
		$event_data->bindParam(1, $event_id);
		//print_r($event_data->errorInfo());
	    $event_data->execute();
	    if($event_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $event_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['mobile'] = $data['mobile'];
				$responseArr[$i]['no_of_guest'] = $data['no_of_guest'];
				$responseArr[$i]['address'] = $data['address'];
				$responseArr[$i]['occasion'] = $data['occasion'];
				$responseArr[$i]['event'] = $data['event'];
				$responseArr[$i]['event_date'] = $data['event_date'];
				$responseArr[$i]['comment'] = $data['comment'];
				$responseArr[$i]['status'] = $data['status'];
				$responseArr[$i]['created'] = $data['created'];
				$responseArr[$i]['reply'] = $data['reply'];
				$responseArr[$i]['reply_at'] = $data['reply_at'];
				
				//customer details
				$responseArr[$i]["customer"]['id'] = $data['customer_id'];
				$responseArr[$i]["customer"]['firstname'] = $data['firstname'];
				$responseArr[$i]["customer"]['lastname'] = $data['lastname'];
				$responseArr[$i]["customer"]['mobile_code'] = $data['mobile_code'];
				$responseArr[$i]["customer"]['mobile'] = $data['mobile'];
				$responseArr[$i]["customer"]['email'] = $data['email'];
				$responseArr[$i]["customer"]['thumb'] = $data['profile_pic']!='' ? CUSTOMER_PIC_URL.'thumb/'.$data['profile_pic'] : '';
				
				//branch admin details
				$responseArr[$i]["admin"]['id'] = $data['reply_by'];
				$responseArr[$i]["admin"]['firstname'] = $data['rfirstname'];
				$responseArr[$i]["admin"]['lastname'] = $data['rlastname'];
				$responseArr[$i]["admin"]['mobile_code'] = $data['rmobile_code'];
				$responseArr[$i]["admin"]['mobile'] = $data['rmobile'];
				$responseArr[$i]["admin"]['thumb'] = $data['rprofile_pic']!='' ? BRANCH_PIC_URL.'thumb/'.$data['rprofile_pic'] : '';
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function eventReply($user_id, $event_id, $reply, $comment) {
		$replyat=time();
		$updateQuery = "UPDATE tbl_customer_events SET comment=?, status='R', reply_by=?, reply=?, reply_at=? where id=? AND status='N' AND reply_by=0";
		$update_status = $this->conn->prepare($updateQuery);
		$update_status->bindParam(1, $comment);
		$update_status->bindParam(2, $user_id);
		$update_status->bindParam(3, $reply);
		$update_status->bindParam(4, $replyat);
		$update_status->bindParam(5, $event_id);
		$update_status->execute();
		//print_r($update_status->errorInfo());
		if($update_status->rowCount()>0) {
			return 'SUCCESSFULLY_UPDATED';
		} else {
			return 'ALREADY_REPLIED';
		}
	}
    
    /* =========================== CALLED FUNCTIONS START =====================*/
    public function refundPaymentPaypal($order_id, $device_id, $device_type, $ip_address, $admin_id) {
		$selectQuery = "SELECT too.customer_id, too.invoice_id, too.amount, top.sale_id FROM tbl_orders too JOIN tbl_order_payments top ON top.order_id=too.id where too.id=?";
        $amount_select = $this->conn->prepare($selectQuery);
		$amount_select->bindParam(1, $order_id);
		$amount_select->execute();
       
        if($amount_select->rowCount()==0) {
            return 'INVALID_CARD';
        }
        $cardData = $amount_select->fetch(PDO::FETCH_ASSOC);
        $saleId=$cardData['sale_id'];
        $totamount=$cardData['amount'];
        $description="MAKE REFUND -  $order_id - $totamount - $saleId - $order_id";
        
        $this->includePaypalLib();
        $apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID,     // ClientID
				PAYPAL_SECRET_ID      // ClientSecret
			)
		);
		
		$amt = new \PayPal\Api\Amount; 
		$refund = new \PayPal\Api\Refund; 
		$sale = new \PayPal\Api\Sale;
		
		$amt->setCurrency(PAYPAL_CURRENCY_CODE) 
			->setTotal($totamount);
			
		$refund->setAmount($amt);
		
		$sale->setId($saleId);
		
        try {
			$refundedSale = $sale->refund($refund, $apiContext);
			//print_r($refundedSale);
			$refundedSale->id;
			
            $time=time();
            $output['errors']='';
            $output['amount']=$totamount;
            $refundId=$refundedSale->id;
            $time=time();
            
			$this->saveTransactionData($order_id, $admin_id, 'PAYMENT_REFUNDED', 'PAYMENT_REFUND_STATUS', $device_id, $device_type, $ip_address, $description);
 
			$refunded_at=time();
            $updatePayment="UPDATE tbl_order_payments set refund_id =?, refunded_by=?, refunded_at=? where order_id=?";
            $update_payment = $this->conn->prepare($updatePayment);
			$update_payment->bindParam(1, $refundId);
			$update_payment->bindParam(2, $admin_id);
			$update_payment->bindParam(3, $refunded_at);
			$update_payment->bindParam(4, $order_id);
			$update_payment->execute();
            return "DONE";
        }  catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$output['errors']=$ex->getData();
			$this->saveTransactionData($order_id, $admin_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $output['errors']);
            return 'FAILED_TO_REFUND';
		}   
	}
	
	public function refundPaymentStripe($order_id, $device_id, $device_type, $ip_address, $admin_id) {
		$selectQuery = "SELECT too.customer_id, too.invoice_id, too.amount, top.sale_id, top.transaction_id FROM tbl_orders too JOIN tbl_order_payments top ON top.order_id=too.id where too.id=?";
        $amount_select = $this->conn->prepare($selectQuery);
		$amount_select->bindParam(1, $order_id);
		$amount_select->execute();
       
        if($amount_select->rowCount()==0) {
            return 'INVALID_CARD';
        }
        $cardData = $amount_select->fetch(PDO::FETCH_ASSOC);
        $saleId=$cardData['transaction_id'];
        $totamount=$cardData['amount'];
        $description="MAKE REFUND ADMIN -  $order_id - $totamount - $saleId - $admin_id";
        
        $this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
		
        try {
			$result = \Stripe\Refund::create(array(
			  "charge" => $saleId,
			  "amount" => $totamount*100,
			));
            $time=time();
            $output['errors']='';
            $output['amount']=$totamount;
            $refundId=$result->id;
            $time=time();
            
			$this->saveTransactionData($order_id, $admin_id, 'PAYMENT_REFUNDED', 'PAYMENT_REFUND_STATUS', $device_id, $device_type, $ip_address, $description);
 
			$refunded_at=time();
            $updatePayment="UPDATE tbl_order_payments set refund_id =?, refunded_by=?, refunded_at=? where order_id=?";
            $update_payment = $this->conn->prepare($updatePayment);
			$update_payment->bindParam(1, $refundId);
			$update_payment->bindParam(2, $admin_id);
			$update_payment->bindParam(3, $refunded_at);
			$update_payment->bindParam(4, $order_id);
			$update_payment->execute();
            return "DONE";
        } catch (Stripe\Error\Base $e) {
			$output[$i]['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\ApiConnection $e) {
			$output[$i]['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\InvalidRequest $e) {
			$output[$i]['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\Api $e) {
			$output[$i]['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\Card $e) {
			$output[$i]['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		}
	}
	
    public function getDeliveryManByOrderId($order_id) {
		//deliveryman details
		$deliveryManQuery = "select * from tbl_order_delivery_men where order_id=?";
		$delivery_man_data = $this->conn->prepare($deliveryManQuery);
		$delivery_man_data->bindParam(1, $order_id);
		$delivery_man_data->execute();
		if($delivery_man_data->rowCount()>0) {
			$deliveryManData = $delivery_man_data->fetch(PDO::FETCH_ASSOC);
			$responseArr['id'] = $deliveryManData['delivery_man_id'];
			$responseArr['firstname'] = $deliveryManData['firstname'];
			$responseArr['lastname'] = $deliveryManData['lastname'];
			$responseArr['mobile_code'] = $deliveryManData['mobile_code'];
			$responseArr['mobile'] = $deliveryManData['mobile'];
			$responseArr['profile_pic'] = $deliveryManData['profile_pic']!='' ? DELIVERYMAN_PIC_URL.'thumb/'.$deliveryManData['profile_pic'] : '';
		} else {
			$responseArr=NULL;	
		}
		return $responseArr;
	}
    
    public function addMissingKeys($array) {
		$array=$this->addKeysInArray($array, 'C');
		$array=$this->addKeysInArray($array, 'S');
		$array=$this->addKeysInArray($array, 'A');
		$array=$this->addKeysInArray($array, 'P');
		$array=$this->addKeysInArray($array, 'AD');
		$array=$this->addKeysInArray($array, 'OD');
		$array=$this->addKeysInArray($array, 'DL');
		$array=$this->addKeysInArray($array, 'PU');
		$array=$this->addKeysInArray($array, 'D');
		$array=$this->addKeysInArray($array, 'CC');	
		return $array;
	}
	
	public function reOrderArray($array) {
		$array= array('D' => $array['D']) + $array;
		$array= array('CC' => $array['CC']) + $array;
		$array= array('PU' => $array['PU']) + $array;
		$array= array('DL' => $array['DL']) + $array;
		$array= array('OD' => $array['OD']) + $array;
		$array= array('AD' => $array['AD']) + $array;
		$array= array('P' => $array['P']) + $array;
		$array= array('A' => $array['A']) + $array;
		$array= array('S' => $array['S']) + $array;
		$array= array('C' => $array['C']) + $array;
		return $array;
	}
	
	public function addKeysInArray($array, $key) {
		if (!array_key_exists($key, $array)) {
			$array[$key] = "0";
		}
		return $array;
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
    
    public function refundPayment($order_id, $device_id, $device_type, $ip_address, $admin_id) {
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->refundPaymentStripe($order_id, $device_id, $device_type, $ip_address, $admin_id);
		} else {
			$result=$this->refundPaymentPaypal($order_id, $device_id, $device_type, $ip_address, $admin_id);
		}
		return $result;     
    }
    
    public function SendPushToCustomer($customer_id, $message, $noti_type, $order_id) {
		$customerQuery = "SELECT GROUP_CONCAT(device_token) as device_tokens FROM tbl_customers where id=?";
		$customer_data = $this->conn->prepare($customerQuery);
		$customer_data->bindParam(1, $customer_id);
		$customer_data->execute();
		$data = $customer_data->fetch(PDO::FETCH_ASSOC);
		$device_tokens=explode(',',$data['device_tokens']);
		if ($device_tokens!=NULL) {
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'CUSTOMER', $order_id);
		}
	}
	
	public function SendPushToBranchAdmin($user_id, $order_id, $message, $noti_type, $order_id) {
		$customerQuery = "SELECT GROUP_CONCAT(tu.device_token) as device_tokens FROM tbl_orders o JOIN tbl_users tu ON tu.info_id=o.branch_id AND tu.usertype='RESTAURANT' AND tu.app_login='Y' AND tu.device_token!='' where o.id=? and tu.id!=$user_id";
		$customer_data = $this->conn->prepare($customerQuery);
		$customer_data->bindParam(1, $order_id);
		$customer_data->execute();
		$data = $customer_data->fetch(PDO::FETCH_ASSOC);
		$device_tokens=explode(',',$data['device_tokens']);
		if ($device_tokens!=NULL) {
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'BRANCH', $order_id);
		}
	}
    
    public function SendPushToDeliveryMan($delivery_man_id, $message, $noti_type, $order_id) {
		$deliverymanQuery = "SELECT GROUP_CONCAT(device_token) as device_tokens FROM tbl_delivery_men where id=?";
		$deliveryman_data = $this->conn->prepare($deliverymanQuery);
		$deliveryman_data->bindParam(1, $delivery_man_id);
	    $deliveryman_data->execute();
	    $data = $deliveryman_data->fetch(PDO::FETCH_ASSOC);
	    $device_tokens=explode(',',$data['device_tokens']);
	    if ($device_tokens!=NULL) {
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'DELIVERYMAN', $order_id);
		}
	}
	
    public function makePayment($order_id, $device_id, $device_type, $ip_address) {
        $selectQuery = "SELECT too.customer_id, too.amount, tocc.* FROM tbl_orders too JOIN tbl_order_credit_cards tocc ON tocc.order_id=too.id where too.id=?";
        $amount_select = $this->conn->prepare($selectQuery);
		$amount_select->bindParam(1, $order_id);
		$amount_select->execute();
       
        if($amount_select->rowCount()>0) {
			$cardData = $amount_select->fetch(PDO::FETCH_ASSOC);
            return 'INVALID_CARD';
        }
        $customer_id=$cardData['customer_id'];
        $totamount=$cardData['amount'];
        $cardnumber=$cardData['card_number'];
        $description="MAKE ORDER - $customer_id - $totamount - $cardnumber - $order_id";
        
        $this->includePaypalLib();
        $apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID,     // ClientID
				PAYPAL_SECRET_ID      // ClientSecret
			)
		);
		
		$creditCardToken = new \PayPal\Api\CreditCardToken();
		$creditCardToken->setCreditCardId($cardData['card_token']);
		
		$fi = new \PayPal\Api\FundingInstrument();
		$fi->setCreditCardToken($creditCardToken);
		
		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod("credit_card")
			->setFundingInstruments(array($fi));
		
		$amount = new \PayPal\Api\Amount();
		$amount->setCurrency(PAYPAL_CURRENCY_CODE)
			->setTotal($totamount);
			
		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount($amount)
			->setDescription($description)
			->setInvoiceNumber(uniqid());
			
		$payment = new \PayPal\Api\Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setTransactions(array($transaction));

        try {
			$payment->create($apiContext);
			//print_r($payment);
			
            $time=time();
            $output['errors']='';
            $output['amount']=$totamount;
            $transactionId=$payment->id;
            $userId=$customer_id;
            $time=time();
            
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_SUCCESS', 'PAYMENT_SUCCESS_STATUS', $device_id, $device_type, $ip_address, $description);
 
			$created=time();
            $insertPayment="insert into tbl_order_payments set credit_card_id =?, order_id=?, transaction_id=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, created=?";
            $insert_payment = $this->conn->prepare($insertPayment);
			$insert_payment->bindParam(1, $cardData['credit_card_id']);
			$insert_payment->bindParam(2, $order_id);
			$insert_payment->bindParam(3, $transactionId);
			$insert_payment->bindParam(4, $cardData['card_token']);
			$insert_payment->bindParam(5, $cardData['card_number']);
			$insert_payment->bindParam(6, $cardData['name']);
			$insert_payment->bindParam(7, $cardData['expiry_month']);
			$insert_payment->bindParam(8, $cardData['expiry_year']);
			$insert_payment->bindParam(9, $cardData['card_type']);
			$insert_payment->bindParam(10, $created);
			$insert_payment->execute();
            return "DONE";
        }  catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$output['errors']=$ex->getData();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $output['errors']);
            return 'FAILED';
		}        
    }
    
    public function freePreviousAssignedDeliveryman($order_id, $invoice_id, $delivery_man_id) {
		$orderQuery = "SELECT delivery_man_id FROM tbl_orders where id=? AND delivery_man_id!=?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $order_id);
		$order_data->bindParam(2, $delivery_man_id);
	    $order_data->execute();
	    if ($order_data->rowCount()>0) {
			$data = $order_data->fetch(PDO::FETCH_ASSOC);
			$old_delivery_man_id=$data['delivery_man_id'];
			
			$del_message="$invoice_id - A new order assigned to you for delivery";
			$noti_type = "ORDER_ASSIGNED";
			$this->SendPushToDeliveryMan($delivery_man_id, $del_message, $noti_type, $order_id);
			
			$del_message2="$invoice_id - Order has been assigned to another delivery man";
			$noti_type2 = "ORDER_REASSIGNED";
			$this->SendPushToDeliveryMan($old_delivery_man_id, $del_message2, $noti_type2, $order_id);
			
			$updateQuery = "UPDATE tbl_delivery_men SET duty_status='FREE' where id=?";
			$update_data = $this->conn->prepare($updateQuery);
			$update_data->bindParam(1, $old_delivery_man_id);
			$update_data->execute();
			
			$deleteQuery = "DELETE FROM tbl_order_delivery_men where order_id=? and delivery_man_id=?";
			$delete_data = $this->conn->prepare($deleteQuery);
			$delete_data->bindParam(1, $order_id);
			$delete_data->bindParam(2, $old_delivery_man_id);
			$delete_data->execute();
		}
	}
    
    public function saveDeliveryManData($order_id, $delivery_man_id) {
		$deliverymanQuery = "SELECT * FROM tbl_delivery_men where id=?";
		$deliveryman_data = $this->conn->prepare($deliverymanQuery);
		$deliveryman_data->bindParam(1, $delivery_man_id);
	    $deliveryman_data->execute();
	    if($deliveryman_data->rowCount()>0) {
	        $data = $deliveryman_data->fetch(PDO::FETCH_ASSOC);
			$created=time();
			
			$insertQuery = "INSERT INTO tbl_order_delivery_men SET order_id=?, delivery_man_id=?, firstname=?, lastname=?, email=?, mobile=?, mobile_code=?, address=?, gender=?, dob=?, profile_pic=?, ip=?, device_id=?, device_token=?, device_type=?, created=?";
			$insert_dmen = $this->conn->prepare($insertQuery);
			$insert_dmen->bindParam(1, $order_id);
			$insert_dmen->bindParam(2, $data['id']);
			$insert_dmen->bindParam(3, $data['firstname']);
			$insert_dmen->bindParam(4, $data['lastname']);
			$insert_dmen->bindParam(5, $data['email']);
			$insert_dmen->bindParam(6, $data['mobile']);
			$insert_dmen->bindParam(7, $data['mobile_code']);
			$insert_dmen->bindParam(8, $data['address']);
			$insert_dmen->bindParam(9, $data['gender']);
			$insert_dmen->bindParam(10, $data['dob']);
			$insert_dmen->bindParam(11, $data['profile_pic']);
			$insert_dmen->bindParam(12, $data['ip']);
			$insert_dmen->bindParam(13, $data['device_id']);
			$insert_dmen->bindParam(14, $data['device_token']);
			$insert_dmen->bindParam(15, $data['device_type']);
			$insert_dmen->bindParam(16, $data['created']);
			$insert_dmen->execute();
		} else {
			return 'UNABLE_TO_PROCEED';
		}
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
	
	public function send_sms($text='', $to='', $issos='NO', $country_code='+91'){
		$this->includeGlobalSiteSetting();
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
		}  catch(Twilio\Exceptions\RestException $e) {
			//print_r($e); die;
		}
    }
    
    public function includeGlobalSiteSetting() {
		$filesArr=get_required_files();
	    $searchString=GLOBAL_SITE_SETTING_PHP;
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
	
	public function includeStripeLib() {
		$filesArr=get_required_files();
		$searchString=STRIPE_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require STRIPE_LIB_PATH;
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
	
	public function includeSMTPMailerLib() {
		$filesArr=get_required_files();
		$searchString=PHPMAILER_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require PHPMAILER_LIB_PATH;
		}
	}
	
	public function sendTemplatesInMail($mailTitle, $toName, $toEmail, $invoice_id=''){
		$this->includeGlobalSiteSetting();
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
			//print_r($e->xdebug_message);
			return false;
		}
		return true;
	}
    
    public function sendFCMPushNotification($message, $registration_ids, $alert_message='EL-GUERO2 NOTIFICATIONS', $noti_type, $send_to, $order_id=0) {
		/* $send_to=DELIVERYMAN / BRANCH / CUSTOMER */
		//print_r($registration_ids);
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
                'order_id' => $order_id
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
        //print_r($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        //print_r($result);
        //exit;
        curl_close($ch);
        return $result;
    }
    /* =========================== CALLED FUNCTIONS END =====================*/
}
?>
