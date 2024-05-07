<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class DbHandler {
    private $conn;
    function __construct() {
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
    
    public function getBranches($lat, $lng) {
		$todaysDate=strtotime(Date('Y-m-d').' '.'00:00:00');
		$todaysDay=strtoupper(Date('D'));
		$latLongData = $this->getSearchAreaLatLong($lat, $lng, BRANCH_SEARCH_AREA);
		$latQuery='';
		$lngQuery='';
		
		//if($latLongData['maxLat']<0) {
        //    $latQuery=" AND b.latitude < ". $latLongData['minLat']." AND b.latitude > ". $latLongData['maxLat'];
      //  } else {
            $latQuery=" AND b.latitude > ". $latLongData['minLat']." AND b.latitude < ". $latLongData['maxLat'];
       // }
		
		//if ($latLongData['maxLng']<0) {
		//	$lngQuery=" AND b.longitude < ". $latLongData['minLng']." AND b.longitude > ". $latLongData['maxLng'];
		//} else {
			$lngQuery=" AND b.longitude > ". $latLongData['minLng']." AND b.longitude < ". $latLongData['maxLng'];
		//}
		
		$branchQuery = "select b.id, b.company_id, b.name, b.address, b.mobile_code, b.mobile, b.address_landmark, b.latitude, b.longitude, b.logo, tbst.timings as special_timings, tbst.is_holiday as special_is_holiday, tbt.timings, tbt.is_holiday from tbl_branches b LEFT JOIN tbl_branch_special_timings tbst ON tbst.branch_id=b.id AND tbst.date=? LEFT JOIN tbl_branch_timings tbt ON tbt.branch_id=b.id AND tbt.weekday=? where status='A' $latQuery $lngQuery";
		$branch_data = $this->conn->prepare($branchQuery);
		$branch_data->bindParam(1, $todaysDate);
		$branch_data->bindParam(2, $todaysDay);
		//print_r($branch_data->errorInfo());
	    $branch_data->execute();
	    if($branch_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
			function cmp($a, $b) {
                $sortby = 'distance'; //define here the field by which you want to sort
                if ($a[$sortby] < $b[$sortby]) {
                    return -1;
                } else if ($a[$sortby] > $b[$sortby]) {
                    return 1;
                } else {
                    return 0;
                }
            }
	         while ($data = $branch_data->fetch(PDO::FETCH_ASSOC)) {
				//print_r($data);
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['company_id'] = $data['company_id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['mobile_code'] = $data['mobile_code'];
				$responseArr[$i]['mobile'] = $data['mobile'];
				$responseArr[$i]['address'] = $data['address'];
				$responseArr[$i]['latitude'] = $data['latitude'];
				$responseArr[$i]['longitude'] = $data['longitude'];
				$responseArr[$i]['logo'] = ($data['logo']=='' || $data['logo']===NULL) ? '' : BRANCH_PIC_URL.'thumb/'.$data['logo'];
				$responseArr[$i]['address_landmark'] = $data['address_landmark'];
			    $responseArr[$i]['distance'] = (float)$this->getDistance($lat, $lng, $data['latitude'], $data['longitude']);
			    
			    $responseArr[$i]['timings'] = $data['special_timings'];
				$responseArr[$i]['is_holiday'] = $data['special_is_holiday'];
			    if($data['special_is_holiday']=='') {
					$responseArr[$i]['timings'] = $data['timings'];
					$responseArr[$i]['is_holiday'] = $data['is_holiday'];
				}
				++$i;
			 }
			 usort($responseArr, "cmp");
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getCategories($branch_id) {	
		$categoryQuery = "select tbc.category_id as b_cat_id , tbc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' AND tbc.branch_id=$branch_id order by tc.is_catering, tbc.category_id asc";
		$category_data = $this->conn->prepare($categoryQuery);
	    $category_data->execute();
	    // print_r($category_data->errorInfo());
	    if($category_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $category_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['b_cat_id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['description'] = $data['description'];
				if($data['logo']!='') {
					$responseArr[$i]['image_thumb'] = CATEGORY_PIC_URL.'thumb/'.$data['logo'];
					$responseArr[$i]['image_large'] = CATEGORY_PIC_URL.'large/'.$data['logo'];
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getCountries() {		
		$categoryQuery = "select id, name, short_name, mobile_code from tbl_geo_countries where status='A'";
		$category_data = $this->conn->prepare($categoryQuery);
	    $category_data->execute();
	    // print_r($category_data->errorInfo());
	    if($category_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $category_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['short_name'] = $data['short_name'];
				$responseArr[$i]['mobile_code'] = $data['mobile_code'];
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getFeatured($branch_id, $customer_id) {		
		$featuredUqery = "select ti.id, tc.id as catid, tc.name as catname, tc.description as catdesc, tc.logo as catimage, ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, (select group_concat(image SEPARATOR '-++-') from tbl_item_images tii where tii.item_id=ti.id AND tii.status='A' AND tii.isdefault='Y') as images, (select group_concat(concat(name, '--', price) SEPARATOR '-++-') from tbl_item_prices tip where tip.item_id=ti.id AND tip.status='A' AND tip.isdefault='Y') as prices, if((select id from tbl_favourite_items tfi where tfi.customer_id=? AND item_id=ti.id) >0, 'Y', 'N') as is_fav from tbl_items ti JOIN tbl_categories tc ON tc.id=ti.category_id where ti.branch_id=? AND ti.is_featured='Y' AND ti.status='A'";
		$featured_data = $this->conn->prepare($featuredUqery);
		$featured_data->bindParam(1, $customer_id);
		$featured_data->bindParam(2, $branch_id);
	    $featured_data->execute();
	    //print_r($featured_data->errorInfo());
	    if($featured_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $featured_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['catid'];
				$responseArr[$i]['name'] = $data['catname'];
				$responseArr[$i]['description'] = $data['catdesc'];
				if($data['catimage']!='') {
					$responseArr[$i]['image_thumb'] = CATEGORY_PIC_URL.'thumb/'.$data['catimage'];
					$responseArr[$i]['image_large'] = CATEGORY_PIC_URL.'large/'.$data['catimage'];
				}
				$responseArr[$i]['itemDataList'][0]['id'] = $data['id'];
				$responseArr[$i]['itemDataList'][0]['category_id'] = $data['catid'];
				$responseArr[$i]['itemDataList'][0]['name'] = $data['name'];
				$responseArr[$i]['itemDataList'][0]['avg_rating'] = $data['avg_rating'];
				$responseArr[$i]['itemDataList'][0]['is_nonveg'] = $data['is_nonveg'];
				$responseArr[$i]['itemDataList'][0]['is_new'] = $data['is_new'];
				$responseArr[$i]['itemDataList'][0]['is_featured'] = 'Y';
				$responseArr[$i]['itemDataList'][0]['is_fav'] = $data['is_fav'];
				
				if ($data['images']!=NULL) {
    			    $imageArrayFinal=explode('-++-', $data['images']);
    		        for($j=0; $j<count($imageArrayFinal); $j++) {
    		            //$imageArrayFinal=explode(',', $imageArray[$j]);
    		            $responseArr[$i]['itemDataList'][0]['images'][$j]['thumb'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'thumb/'.$imageArrayFinal[$j] : '';
    		            $responseArr[$i]['itemDataList'][0]['images'][$j]['large'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'large/'.$imageArrayFinal[$j] : '';
    		        }
    		    }
    		    
    		    if ($data['prices']!=NULL) {
    			    $priceArray=explode('-++-', $data['prices']);
    		        for($k=0; $k<count($priceArray); $k++) {
    		            $priceArrayFinal=explode('--', $priceArray[$k]);
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['name'] = $priceArrayFinal[0];
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['price'] = $priceArrayFinal[1];
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['currency'] = SITE_CURRENCY;
    		        }
    		    }
    		    
    		    $attr=$this->getMenuAttributes($data['id'], 'Y');
    		    if ($attr=='NO_RECORD_FOUND') {
					$responseArr[$i]['itemDataList'][0]['default_attributes']=array();
				} else {
					$responseArr[$i]['itemDataList'][0]['default_attributes']=$attr;
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getMenus($customer_id=0, $branch_id=0, $catid=0, $textsearch, $is_new, $is_nonveg, $pageno=0) {
		$offset = 10;
        $limit = ($pageno - 1) * 10;
		$menuUqery = "select ti.id, ti.category_id, ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, ti.is_featured, (select group_concat(image SEPARATOR '-++-') from tbl_item_images tii where tii.item_id=ti.id AND tii.status='A' AND tii.isdefault='Y') as images, (select group_concat(concat(name, '--', price) SEPARATOR '-++-') from tbl_item_prices tip where tip.item_id=ti.id AND tip.status='A' AND tip.isdefault='Y') as prices, if((select id from tbl_favourite_items tfi where tfi.customer_id=? AND item_id=ti.id) >0, 'Y', 'N') as is_fav from tbl_items ti where ti.status='A'";
		if($branch_id>0)
			$menuUqery .= " AND ti.branch_id=?";
		if($catid>0)
			$menuUqery .= " AND ti.category_id=?";
		if($textsearch!='0')
			$menuUqery .= " AND ti.name like CONCAT('%', ?, '%')";
		if($is_new!='0')
			$menuUqery .= " AND ti.is_new=?";
		if($is_nonveg!='0')
			$menuUqery .= " AND ti.is_nonveg=?";
		if($pageno>0)
			$menuUqery .= " limit ?, ?";
	
		$menu_data = $this->conn->prepare($menuUqery);
		$j=2;

		$menu_data->bindParam(1, $customer_id);
		if($branch_id>0) {
			$menu_data->bindParam($j, $branch_id);
			$j=$j+1;
		}
		if($catid>0) {
			$menu_data->bindParam($j, $catid);
			$j=$j+1;
		}
		if($textsearch!='0') {
			$menu_data->bindParam($j, $textsearch);
			$j=$j+1;
		}
		if($is_new!='0') {
			$menu_data->bindParam($j, $is_new);
			$j=$j+1;
		}
		if($is_nonveg!='0') {
			$menu_data->bindParam($j, $is_nonveg);
			$j=$j+1;
		}
		if($pageno>0) {
			$menu_data->bindParam($j, $limit, PDO::PARAM_INT);
			$menu_data->bindParam($j+1, $offset, PDO::PARAM_INT);
		}
			
	    $menu_data->execute();
	    //print_r($menu_data->errorInfo());
	    if($menu_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $menu_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['category_id'] = $data['category_id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['avg_rating'] = $data['avg_rating'];
				$responseArr[$i]['is_nonveg'] = $data['is_nonveg'];
				$responseArr[$i]['is_new'] = $data['is_new'];
				$responseArr[$i]['is_featured'] = $data['is_featured'];
				$responseArr[$i]['is_fav'] = $data['is_fav'];
				
				if ($data['images']!=NULL) {
    			    $imageArrayFinal=explode('-++-', $data['images']);
    		        for($j=0; $j<count($imageArrayFinal); $j++) {
    		            //$imageArrayFinal=explode(',', $imageArray[$j]);
    		            $responseArr[$i]['images'][$j]['thumb'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'thumb/'.$imageArrayFinal[$j] : '';
    		            $responseArr[$i]['images'][$j]['large'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'large/'.$imageArrayFinal[$j] : '';
    		        }
    		    }
    		    
    		    if ($data['prices']!=NULL) {
    			    $priceArray=explode('-++-', $data['prices']);
    		        for($k=0; $k<count($priceArray); $k++) {
    		            $priceArrayFinal=explode('--', $priceArray[$k]);
    		            $responseArr[$i]['prices'][$k]['name'] = $priceArrayFinal[0];
    		            $responseArr[$i]['prices'][$k]['price'] = $priceArrayFinal[1];
    		            $responseArr[$i]['prices'][$k]['currency'] = SITE_CURRENCY;
    		        }
    		    }
    		    $attr=$this->getMenuAttributes($data['id'], 'Y');
    		    if ($attr=='NO_RECORD_FOUND') {
					$responseArr[$i]['default_attributes']=array();
				} else {
					$responseArr[$i]['default_attributes']=$attr;
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getMenuPages($catid, $textsearch, $is_new, $is_nonveg) {
		$menuUqery = "select ti.id from tbl_items ti where ti.status='A'";
		if($catid>0)
			$menuUqery .= " AND ti.category_id=?";
		if($textsearch!='0')
			$menuUqery .= " AND ti.name like % ? %";
		if($is_new!='0')
			$menuUqery .= " AND ti.is_new=?";
		if($is_nonveg!='0')
			$menuUqery .= " AND ti.is_nonveg=?";	
		$menu_data = $this->conn->prepare($menuUqery);
		$j=1;
		if($catid>0) {
			$menu_data->bindParam($j, $catid);
			$j=$j+1;
		}
		if($textsearch!='0') {
			$menu_data->bindParam($j, $textsearch);
			$j=$j+1;
		}
		if($is_new!='0') {
			$menu_data->bindParam($j, $is_new);
			$j=$j+1;
		}
		if($is_nonveg!='0') {
			$menu_data->bindParam($j, $is_nonveg);
		}		
	    $menu_data->execute();
	    //print_r($menu_data->errorInfo());
        $num_rows = $menu_data->rowCount();
        $totpages = ceil($num_rows / 10);
        return $totpages;
    }
    
    public function getMenuDetails($menuid, $customerid=0) {
		$menuUqery = "select ti.id, ti.category_id, ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, ti.description, ti.is_featured, (select group_concat(concat(image,'--',isdefault) SEPARATOR '-++-') from tbl_item_images tii where tii.item_id=ti.id AND tii.status='A') as images, (select group_concat(concat(name, '--', price, '--', isdefault) SEPARATOR '-++-') from tbl_item_prices tip where tip.item_id=ti.id AND tip.status='A') as prices, if((select id from tbl_favourite_items tfi where tfi.customer_id=? AND item_id=ti.id) >0, 'Y', 'N') as is_fav from tbl_items ti where ti.status='A' AND ti.id=?";
		$menu_data = $this->conn->prepare($menuUqery);
		$menu_data->bindParam(1, $customerid);
		$menu_data->bindParam(2, $menuid);
	    $menu_data->execute();
	    // print_r($menu_data->errorInfo());
	    if($menu_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        $data = $menu_data->fetch(PDO::FETCH_ASSOC);
			$responseArr['id'] = $data['id'];
			$responseArr['category_id'] = $data['category_id'];
			$responseArr['name'] = $data['name'];
			$responseArr['avg_rating'] = $data['avg_rating'];
			$responseArr['is_nonveg'] = $data['is_nonveg'];
			$responseArr['is_new'] = $data['is_new'];
			$responseArr['description'] = $data['description'];
			$responseArr['is_featured'] = $data['is_featured'];
			$responseArr['is_fav'] = $data['is_fav'];
			if ($data['images']!=NULL) {
				$imageArray=explode('-++-', $data['images']);
				for($j=0; $j<count($imageArray); $j++) {
					$imageArrayFinal=explode('--', $imageArray[$j]);
					$responseArr['images'][$j]['thumb'] = $imageArrayFinal[0]!='' ? MENU_PIC_URL.'thumb/'.$imageArrayFinal[0] : '';
					$responseArr['images'][$j]['large'] = $imageArrayFinal[0]!='' ? MENU_PIC_URL.'large/'.$imageArrayFinal[0] : '';
					$responseArr['images'][$j]['isdefault'] = $imageArrayFinal[1];
				}
			}
			if ($data['prices']!=NULL) {
				$priceArray=explode('-++-', $data['prices']);
				for($k=0; $k<count($priceArray); $k++) {
					$priceArrayFinal=explode('--', $priceArray[$k]);
					$responseArr['prices'][$k]['name'] = $priceArrayFinal[0];
					$responseArr['prices'][$k]['price'] = $priceArrayFinal[1];
					$responseArr['prices'][$k]['currency'] = SITE_CURRENCY;
					$responseArr['prices'][$k]['isdefault'] = $priceArrayFinal[2];
				}
			}
			
			$extras=$this->getItemExtras($menuid);
			if ($extras) {
				$e=0;
				while ($data = $extras->fetch(PDO::FETCH_ASSOC)) {
					$responseArr['extras'][$e]['id'] = $data['id'];
					$responseArr['extras'][$e]['name'] = $data['name'];
					$responseArr['extras'][$e]['thumb'] = $data['image']!='' ? EXTRA_PIC_URL.'thumb/'.$data['image'] : '';
					$responseArr['extras'][$e]['large'] = $data['image']!='' ? EXTRA_PIC_URL.'large/'.$data['image'] : '';
					$responseArr['extras'][$e]['price'] = $data['price'];
					$responseArr['extras'][$e]['currency'] = SITE_CURRENCY;
					$responseArr['extras'][$e]['description'] = $data['description'];
					++$e;
				}
			} else {
				$responseArr['extras']=array();
			}
			
			return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getMenuAttributes($menuid, $isDefault='') {		
		$attributeQuery = "select tia.id, ta.name, ta.description, ta.type from tbl_item_attributes tia JOIN tbl_attributes ta ON ta.id=tia.attribute_id where tia.status='A' and ta.status='A' and tia.item_id=? order by priority asc";
		$attribute_data = $this->conn->prepare($attributeQuery);
		$attribute_data->bindParam(1, $menuid);
	    $attribute_data->execute();
	    // print_r($attribute_data->errorInfo());
	    if($attribute_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $attribute_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['name'] = $data['name'];
				$responseArr[$i]['description'] = $data['description'];
				$responseArr[$i]['type'] = $data['type'];
				
				$options=$this->getAttributeOptions($data['id'], $isDefault);
				if ($options) {
					$e=0;
					while ($data = $options->fetch(PDO::FETCH_ASSOC)) {
						$responseArr[$i]['options'][$e]['id'] = $data['id'];
						$responseArr[$i]['options'][$e]['name'] = $data['name'];
						$responseArr[$i]['options'][$e]['price'] = $data['price'];
						$responseArr[$i]['options'][$e]['currency'] = SITE_CURRENCY;
						$responseArr[$i]['options'][$e]['default_selected'] = $data['default_selected'];
						++$e;
					}
				} else {
					$responseArr[$i]['options']=array();
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
 
	public function newUser($mobile, $mobile_code, $email='') {
        $response = array();
        if ($this->isMobileExists($mobile, null, $mobile_code) == 0) {
			if($email!='' && $this->isEmailExists($email)) {
				return 'EMAIL_ALREADY_EXISTED';
			} else {
				$otpCode=$this->sendotp($mobile, 'V', $mobile_code);
			}
            if($otpCode>0) {
                return $otpCode;
            } else {
                return 'UNABLE_TO_PROCEED';
            }
        } else {
            return 'PHONE_ALREADY_EXISTED';
        }
    }

    public function verifyOtp($mobile, $otp, $type, $newpassword = '', $mobile_code) {
        $verifyOtp = "update tbl_temp_customers SET isverified='Y' where type=? and otp=? and mobile=? and mobile_code=?";
		$otp_data = $this->conn->prepare($verifyOtp);
		$otp_data->bindParam(1, $type);
		$otp_data->bindParam(2, $otp);
		$otp_data->bindParam(3, $mobile);
		$otp_data->bindParam(4, $mobile_code);
		$otp_data->execute();
		// print_r($otp_data->errorInfo());
		if($otp_data->rowCount()) {
            if($type=='F') {
                //$newpassword=md5($newpassword);
                $newpassword=$this->generatePassword($newpassword);
                $updateQuery = "update tbl_customers set password=? where mobile=? AND mobile_code=?";
				$password_update = $this->conn->prepare($updateQuery);
				$password_update->bindParam(1, $newpassword);
				$password_update->bindParam(2, $mobile);
				$password_update->bindParam(3, $mobile_code);
				//print_r($password_update->errorInfo());
				if ($password_update->execute()) {
					$userData=$this->validateUser($mobile, $newpassword);
					$this->sendTemplatesInMail('customer_password_changed', $userData['firstname'], $userData['email']);
					return 'VERIFIED';
				} else {
					return 'USER_NOT_EXIST';
				}
            } /*else {
                $updateQuery = "update tbl_customers set is_verified='Y', otp_verified='Y' where mobile=? AND mobile_code=?";
                $password_update = $this->conn->prepare($updateQuery);
				$password_update->bindParam(1, $mobile);
				$password_update->bindParam(2, $mobile_code);
				$password_update->execute();
            }*/
            return 'VERIFIED';
        } else {
            return 'INVALID_OTP';  
        }
    }

    public function resendOtp($mobile, $type, $mobile_code) {
        $otpCode=$this->sendotp($mobile, $type, $mobile_code);
        if($otpCode>0) {
            return $otpCode;
        } else {
            return 'UNABLE_TO_PROCEED';
        }
    }

    public function forgotPassword($mobile, $mobile_code) {
        $userdata=$this->getRiderIdByMobileNo($mobile, $mobile_code);
        if (!empty($userdata)) {
            $otpCode=$this->sendotp($mobile, 'F', $mobile_code);
            return $otpCode;
        } else {
            return 'INVALID_MOBILE';
        }
    }
    
    public function newuserstep2($firstname, $lastname, $email, $password, $mobile, $mobile_code, $gender, $dob, $device_id, $device_type, $device_token, $ipaddress, $applied_referral_code='') {
        $response = array();
        $apply_referral_code=0;
        if ($this->isMobileVerifiedInTemp($mobile, $mobile_code)) {
			if (!$this->isEmailExists($email)) {
				if (!$this->isMobileExists($mobile, null, $mobile_code)) {
					$realpassword = $password;
					$created=time();
					//$password = md5($password);
					$password=$this->generatePassword($password);
					
					$referraler_id=0;
					$referral_code = $this->generateReferralCode(4, $firstname);
					if($applied_referral_code!='' || $applied_referral_code!=NULL) {
						$isReferralAllowed=$this->isReferralAllowed();
						if ($isReferralAllowed=='DEACTIVATED')
							return 'REFERRAL_DEACTIVATED';
						
						$referraler_id=$this->getReferralerId($applied_referral_code);
						if ($referraler_id=='' || $referraler_id===NULL || $referraler_id==0) {
							return 'INVALID_REFERRAL_CODE';
						}
					}
					$slug=strtolower($referral_code);
					// insert query
					$saveQuery = "INSERT INTO tbl_customers SET slug=?, firstname=?, lastname=?, email=?, password=?, mobile=?, mobile_code=?, gender=?, dob=?, device_id=?, device_token=?, device_type=?, mobile_verified='Y', lastlogin=?, created=?, createdby=0, ip=?, referral_code=?";
					$insert_user = $this->conn->prepare($saveQuery);
					$insert_user->bindParam(1, $slug);
					$insert_user->bindParam(2, $firstname);
					$insert_user->bindParam(3, $lastname);
					$insert_user->bindParam(4, $email);
					$insert_user->bindParam(5, $password);
					$insert_user->bindParam(6, $mobile);
					$insert_user->bindParam(7, $mobile_code);
					$insert_user->bindParam(8, $gender);
					$insert_user->bindParam(9, $dob);
					$insert_user->bindParam(10, $device_id);
					$insert_user->bindParam(11, $device_token);
					$insert_user->bindParam(12, $device_type);
					$insert_user->bindParam(13, $created);
					$insert_user->bindParam(14, $created);
					$insert_user->bindParam(15, $ipaddress);
					$insert_user->bindParam(16, $referral_code);
					$insert_user->execute();
					//print_r($insert_user->errorInfo());
					if($insert_user->rowCount()>0) {
						$inserted_id = $this->conn->lastInsertId();
						$lastlogin = date("Y-m-d h:i:s");
						if ($referraler_id!='' || $referraler_id!=NULL || $referraler_id!=0)
							$apply_referral_code = $this->applyReferralCode($inserted_id, $referraler_id, $applied_referral_code);

						$updateQuery = "UPDATE tbl_customers SET lastlogin = ? WHERE id = ?";
						$update_user = $this->conn->prepare($updateQuery);
						$update_user->bindParam(1, $lastlogin);
						$update_user->bindParam(2, $inserted_id);
						$update_user->execute();
						$response['id'] = $inserted_id;
						$response['firstname'] = $firstname;
						$response['lastname'] = $lastname;
						$response['mobile'] = $mobile;
						$response['mobile_code'] = $mobile_code;
						$response['email'] = $email;
						$response['password'] = $password;
						$response['gender'] = $gender;
						$response['dob'] = $dob;
						$response['profile_pic'] ='';
						$response['wallet_points'] =$apply_referral_code;
						$response['referral_code'] =$referral_code;
						$response['loyalty_point_value']=LOYALTY_POINT_VALUE;
						
						$cardData=$this->getDeaultCard($inserted_id);
						$addressData=$this->getDefaultAddress($inserted_id);
						
						$response['default_card'] = $cardData;
						$response['default_address'] = $addressData;
						if($email!='')
							$this->sendTemplatesInMail('customer_welcome', $firstname, $email);
						return $response;
					} else {
						return 'UNABLE_TO_PROCEED';
					}
				} else {
					return 'PHONE_ALREADY_EXISTED';
				}
			} else {
				return 'EMAIL_ALREADY_EXISTED';
			}
        } else {
			return 'NOT_VERIFIED_IN_TEMP';
		}
        return $response;
    }
    
    public function validateUser($username, $password) {
		$md5password = md5($password);
		//AND (password=? OR password=?)
		$userUqery = "select id, password, firstname, email from tbl_customers where (email=? OR mobile=?) AND status='A' AND customer_deactivated='N'";
		$login_user = $this->conn->prepare($userUqery);
        $login_user->bindParam(1, $username);
        $login_user->bindParam(2, $username);
        //$login_user->bindParam(3, $password);
        //$login_user->bindParam(4, $md5password);
	    $login_user->execute();
	    //print_r($login_user->errorInfo());
	    if($login_user->rowCount()>0) {
			$userData = $login_user->fetch(PDO::FETCH_ASSOC);
			if(($userData['password']!=$password) && ($userData['password']!=$md5password) && !$this->matchHashPassword($password, $userData['password'])) {
				return false;
			}
			//print_r($userData);
	        return $userData;
		}
    }
    
    public function login($username, $password, $device_id, $device_token, $device_type, $ipaddress) {     
		$md5password = md5($password);				
		$userUqery = "select * from tbl_customers where (email=? OR mobile=?)";
		$login_user = $this->conn->prepare($userUqery);
        $login_user->bindParam(1, $username);
        $login_user->bindParam(2, $username);
	    $login_user->execute();
	    // print_r($login_user->errorInfo());
	    if($login_user->rowCount()>0) {
			$userData = $login_user->fetch(PDO::FETCH_ASSOC);
			if(($userData['password']!=$password) && ($userData['password']!=$md5password) && !$this->matchHashPassword($password, $userData['password'])) {
				return 'INVALID_USERNAME_PASSWORD';
			}
			if($userData['status'] !='A') { 
				return 'ACCOUNT_DEACTVATED'; 
			} else if($userData['customer_deactivated'] == 'Y') { 
				return 'ACCOUNT_DEACTVATED_BY_CUSTOMER'; 
			} else {
				$time=time();
				$updateQuery = "UPDATE tbl_customers set device_id=?, device_type=?, device_token=?, lastlogin=?, ip=? where id=?";
				$user_update = $this->conn->prepare($updateQuery);
				$user_update->bindParam(1, $device_id);
				$user_update->bindParam(2, $device_type);
				$user_update->bindParam(3, $device_token);
				$user_update->bindParam(4, $time);
				$user_update->bindParam(5, $ipaddress);
				$user_update->bindParam(6, $userData['id']);
				$user_update->execute();
			   // print_r($user_update->errorInfo());
				
				$responseArr=array();
				$responseArr['id'] = $userData['id'];
				$responseArr['firstname'] = $userData['firstname'];
				$responseArr['lastname'] = $userData['lastname'];
				$responseArr['mobile'] = $userData['mobile'];
				$responseArr['mobile_code'] = $userData['mobile_code'];
				$responseArr['email'] = $userData['email'];
				$responseArr['password'] = $password;
				$responseArr['gender'] = $userData['gender'];
				$responseArr['dob'] = $userData['dob'];
				$responseArr['profile_pic'] = $userData['profile_pic']=='' || $userData['profile_pic']===NULL ? '' : CUSTOMER_PIC_URL.'thumb/'.$userData['profile_pic'];
				$responseArr['wallet_points'] = $userData['wallet_points'];
				$responseArr['loyalty_point_value']=LOYALTY_POINT_VALUE;
				$responseArr['referral_code'] = $userData['referral_code'];
				
				$cardData=$this->getDeaultCard($userData['id']);
				$addressData=$this->getDefaultAddress($userData['id']);
				
				$responseArr['default_card'] = $cardData;
				$responseArr['default_address'] = $addressData;
				
				return $responseArr; 
			}
		} else {
			return 'USERNAME_NOT_EXIST';
		}		
    }
    
    public function socialLogin($email, $firstname, $lastname, $device_id, $device_token, $device_type, $loginfrom, $ipaddress) {
        $userUqery = "select * from tbl_customers where (email=? OR mobile=?)";
        $login_user = $this->conn->prepare($userUqery);
        $login_user->bindParam(1, $email);
        $login_user->bindParam(2, $email);
	    $login_user->execute();
	    // print_r($login_user->errorInfo());
	    if($login_user->rowCount()>0) {
			$user = $login_user->fetch(PDO::FETCH_ASSOC);
            if ($user['status'] != 'A') {
                $response['firstname'] = $user['firstname'];
                $response['lastname'] = $user['lastname'];
                $response['mobile'] = $user['mobile'];
                $response['mobile_code'] = $user['mobile_code'];
                $response['email'] = $user['email'];
                $response['password'] = $user['password'];
                $response['wallet_points'] = $user['wallet_points'];
                $response['loyalty_point_value']=LOYALTY_POINT_VALUE;
                $response['referral_code'] = $user['referral_code'];
                $response['status'] = 'USER_ACCOUNT_DEACTVATED';
                return $response;
            } else if ($user['customer_deactivated'] == 'Y') {
                $response['firstname'] = $user['firstname'];
                $response['lastname'] = $user['lastname'];
                $response['mobile'] = $user['mobile'];
                $response['mobile_code'] = $user['mobile_code'];
                $response['email'] = $user['email'];
                $response['password'] = $user['password'];
                $response['wallet_points'] = $user['wallet_points'];
                $response['loyalty_point_value']=LOYALTY_POINT_VALUE;
                $response['referral_code'] = $user['referral_code'];
                $response['status'] = 'ACCOUNT_DEACTVATED_BY_CUSTOMER';
                return $response;
            } else if ($user['email_verified'] == 'N') {
				$lastlogin=time();
				//verify email address
				$updateQuery = "UPDATE tbl_customers SET email_verified='Y', device_id=?, device_type=?, device_token=?, lastlogin=?, ip=? where id=?";
				$upadte_cust = $this->conn->prepare($updateQuery);
				$upadte_cust->bindParam(1, $device_id);
				$upadte_cust->bindParam(2, $device_type);
				$upadte_cust->bindParam(3, $device_token);
				$upadte_cust->bindParam(4, $lastlogin);
				$upadte_cust->bindParam(5, $ipaddress);
				$upadte_cust->bindParam(6, $user['id']);
				$upadte_cust->execute();
				// print_r($select_card->errorInfo());
				
				$response['id'] = $user['id'];
                $response['firstname'] = $user['firstname'];
                $response['lastname'] = $user['lastname'];
                $response['mobile'] = $user['mobile'];
                $response['mobile_code'] = $user['mobile_code'];
                $response['email'] = $user['email'];
                $response['password'] = $user['password'];
                $response['wallet_points'] = $user['wallet_points'];
                $response['loyalty_point_value']=LOYALTY_POINT_VALUE;
                $response['referral_code'] = $user['referral_code'];
                $response['profile_pic'] = $user['profile_pic']=='' || $user['profile_pic']===NULL ? '' : CUSTOMER_PIC_URL.'thumb/'.$user['profile_pic'];
                
                $cardData=$this->getDeaultCard($user['id']);
				$addressData=$this->getDefaultAddress($user['id']);
				$response['default_card'] = $cardData;
				$response['default_address'] = $addressData;
				$response['status']='SUCCESS';
                return $response;
            } else {
                $lastlogin=time();
				//verify email address
				$updateQuery = "UPDATE tbl_customers SET device_id=?, device_type=?, device_token=?, lastlogin=?, ip=? where id=?";
				$upadte_cust = $this->conn->prepare($updateQuery);
				$upadte_cust->bindParam(1, $device_id);
				$upadte_cust->bindParam(2, $device_type);
				$upadte_cust->bindParam(3, $device_token);
				$upadte_cust->bindParam(4, $lastlogin);
				$upadte_cust->bindParam(5, $ipaddress);
				$upadte_cust->bindParam(6, $user['id']);
				$upadte_cust->execute();
				// print_r($select_card->errorInfo());

                $response['id'] = $user['id'];
                $response['firstname'] = $user['firstname'];
                $response['lastname'] = $user['lastname'];
                $response['mobile'] = $user['mobile'];
                $response['mobile_code'] = $user['mobile_code'];
                $response['password'] = $user['password'];
                $response['email'] = $user['email'];
                $response['wallet_points'] = $user['wallet_points'];
                $response['loyalty_point_value']=LOYALTY_POINT_VALUE;
                $response['referral_code'] = $user['referral_code'];
                $response['profile_pic'] = $user['profile_pic']=='' || $user['profile_pic']===NULL ? '' : CUSTOMER_PIC_URL.'thumb/'.$user['profile_pic'];
                
                $cardData=$this->getDeaultCard($user['id']);
				$addressData=$this->getDefaultAddress($user['id']);
				$response['default_card'] = $cardData;
				$response['default_address'] = $addressData;
				$response['status']='SUCCESS';
                return $response;
            }
        } else {
            return 'USER_NOT_FOUND';
        }
    }
    
    public function changePassword($userid, $oldpassword, $newpassword) {
		$md5password=md5($oldpassword);
		$userUqery = "select id, firstname, email, password from tbl_customers where id=?";
		$login_customer = $this->conn->prepare($userUqery);
        $login_customer->bindParam(1, $userid);
	    $login_customer->execute();

		if($login_customer->rowCount()>0) {
			$customerData = $login_customer->fetch(PDO::FETCH_ASSOC);
			if(($customerData['password']!=$oldpassword) && ($customerData['password']!=$md5password) && !$this->matchHashPassword($oldpassword, $customerData['password'])) {
				return 'INVALID_OLD_PASSWORD';
			} else {
				$newpassword=$this->generatePassword($newpassword);
				$updateQuery = "UPDATE tbl_customers set password=? where id=?";
				$customer_update = $this->conn->prepare($updateQuery);
				$customer_update->bindParam(1, $newpassword);
				$customer_update->bindParam(2, $userid);
				if($customer_update->execute()) {
					$this->sendTemplatesInMail('customer_password_changed', $customerData['firstname'], $customerData['email']);
					return 'PASSWORD_UPDATED';
				} else {
					return 'UNABLE_TO_PROCEED';
				}
			}	
		} else {
			return 'INVALID_OLD_PASSWORD';
		}
	}
 
	public function updateImage($userid, $picname) {
		$userUqery = "select profile_pic from tbl_customers where id=?";
		$login_customer = $this->conn->prepare($userUqery);
        $login_customer->bindParam(1, $userid);
	    $login_customer->execute();
	    // print_r($login_customer->errorInfo());
	    $customerData = $login_customer->fetch(PDO::FETCH_ASSOC);
	    
        if ($customerData['profile_pic'] != '') {
            @unlink(CUSTOMER_PIC_PATH . 'large/' . $customerData['profile_pic']);
            @unlink(CUSTOMER_PIC_PATH . 'thumb/' . $customerData['profile_pic']);
        }
        
        $updateQuery = "UPDATE tbl_customers set profile_pic=? where id=?";
		$customer_update = $this->conn->prepare($updateQuery);
		$customer_update->bindParam(1, $picname);
		$customer_update->bindParam(2, $userid);
		if($customer_update->execute()) {
			$response = array();
			$response['profile_pic'] = CUSTOMER_PIC_URL.'thumb/'.$picname;
			return $response;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function updateProfile($user_id, $firstname, $lastname, $gender, $dob, $email) {
	    if (!$this->isEmailExists($email, $user_id)) {
			$created=time();
			$updateQuery = "UPDATE tbl_customers SET firstname=?, lastname=?, email=? WHERE id=?";
			$update_user = $this->conn->prepare($updateQuery);
			$update_user->bindParam(1, $firstname);
			$update_user->bindParam(2, $lastname);
			$update_user->bindParam(3, $email);
			//$update_user->bindParam(4, $dob);
			$update_user->bindParam(4, $user_id);
			$update_user->execute();
			// print_r($update_user->errorInfo());

			$response = array();
			$custData=$this->getCustomerInfo($user_id);
			$response['id'] = $user_id;
			$response['firstname'] = $custData['firstname'];
			$response['lastname'] = $custData['lastname'];
			$response['mobile'] = $custData['mobile'];
			$response['mobile_code'] = $custData['mobile_code'];
			$response['email'] = $custData['email'];
			$response['password'] = $custData['password'];
			//$response['gender'] = $custData['gender'];
			//$response['dob'] = $custData['dob'];
			$response['profile_pic'] = $custData['profile_pic']=='' || $custData['profile_pic']===NULL ? '' : CUSTOMER_PIC_URL.'thumb/'.$custData['profile_pic'];
			return $response;
			
		} else {
			return 'DUPLICATE_EMAIL';
		}
    }
    
    public function addAddress($user_id, $firstname, $lastname, $mobile_code, $mobile, $address, $latitude, $longitude, $address_id) {
		$addressQuery = "select email from tbl_customers where id=?";
		$address_data = $this->conn->prepare($addressQuery);
		$address_data->bindParam(1, $user_id);
	    $address_data->execute();
	    $data = $address_data->fetch(PDO::FETCH_ASSOC);
	    $email= $data['email'];
		
		$created=time();
		if ($address_id>0) {
			$query = "UPDATE tbl_customer_addresses SET customer_id=?, firstname=?, lastname=?, mobile_code=?, mobile=?, address=?, latitude=?, longitude=?, updated=?, email=? WHERE id=?";
		} else {
			$query = "INSERT INTO tbl_customer_addresses SET customer_id=?, firstname=?, lastname=?, mobile_code=?, mobile=?, address=?, latitude=?, longitude=?, created=?, email=?";
		}
		$prepare_user = $this->conn->prepare($query);
		$prepare_user->bindParam(1, $user_id);
		$prepare_user->bindParam(2, $firstname);
		$prepare_user->bindParam(3, $lastname);
		$prepare_user->bindParam(4, $mobile_code);
		$prepare_user->bindParam(5, $mobile);
		$prepare_user->bindParam(6, $address);
		$prepare_user->bindParam(7, $latitude);
		$prepare_user->bindParam(8, $longitude);
		$prepare_user->bindParam(9, $created);
		$prepare_user->bindParam(10, $email);
		if($address_id>0)
			$prepare_user->bindParam(11, $address_id);
			
		$prepare_user->execute();
		//print_r($prepare_user->errorInfo());
		if($prepare_user->rowCount()>0) {
			if($address_id==0) {
				$address_id = $this->conn->lastInsertId();
				$this->makeAddressDefault($user_id, $address_id, 'NEW');
			}
			$response = array();
			$addressData=$this->getAddressData($address_id);
			$response['id'] = $addressData['id'];
			$response['firstname'] = $addressData['firstname'];
			$response['lastname'] = $addressData['lastname'];
			$response['mobile'] = $addressData['mobile'];
			$response['mobile_code'] = $addressData['mobile_code'];
			$response['address'] = $addressData['address'];
			$response['latitude'] = $addressData['latitude'];
			$response['longitude'] = $addressData['longitude'];
			$response['isdefault'] = $addressData['isdefault'];
			return $response;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function getAddresses($customer_id) {		
		$addressQuery = "select * from tbl_customer_addresses where customer_id=?";
		$address_data = $this->conn->prepare($addressQuery);
		$address_data->bindParam(1, $customer_id);
	    $address_data->execute();
	    // print_r($address_data->errorInfo());
	    if($address_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $address_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['id'];
				$responseArr[$i]['firstname'] = $data['firstname'];
				$responseArr[$i]['lastname'] = $data['lastname'];
				$responseArr[$i]['mobile'] = $data['mobile'];
				$responseArr[$i]['mobile_code'] = $data['mobile_code'];
				$responseArr[$i]['address'] = $data['address'];
				$responseArr[$i]['latitude'] = $data['latitude'];
				$responseArr[$i]['longitude'] = $data['longitude'];
				$responseArr[$i]['isdefault'] = $data['isdefault'];
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function deleteAddress($customer_id, $address_id) {		
		$query = "DELETE FROM tbl_customer_addresses WHERE customer_id=? AND id=?";
		$prepare_user = $this->conn->prepare($query);
		$prepare_user->bindParam(1, $customer_id);
		$prepare_user->bindParam(2, $address_id);
		//if($address_id>0)
		//	$prepare_user->bindParam(3, $address_id);
			
		$prepare_user->execute();
		// print_r($prepare_user->errorInfo());
		if($prepare_user->rowCount()>0) {
			$queryUpdate = "UPDATE tbl_customer_addresses SET isdefault='Y' WHERE customer_id=? order by id desc limit 1";
			$prepare_update = $this->conn->prepare($queryUpdate);
			$prepare_update->bindParam(1, $customer_id);				
			$prepare_update->execute();
			return 'SUCCESSFULLY_DELTED';
		} else {
			return 'UNABLE_TO_DELETE';
		}
    }
    
    public function getAddressesById($user_id, $address_id) {		
		$addressQuery = "select * from tbl_customer_addresses where id=?";
		$address_data = $this->conn->prepare($addressQuery);
		$address_data->bindParam(1, $address_id);
	    $address_data->execute();
	    // print_r($address_data->errorInfo());
	    if($address_data->rowCount()>0) {
			$responseArr=array();
	        $data = $address_data->fetch(PDO::FETCH_ASSOC);
			$responseArr['id'] = $data['id'];
			$responseArr['firstname'] = $data['firstname'];
			$responseArr['lastname'] = $data['lastname'];
			$responseArr['mobile'] = $data['mobile'];
			$responseArr['mobile_code'] = $data['mobile_code'];
			$responseArr['address'] = $data['address'];
			$responseArr['latitude'] = $data['latitude'];
			$responseArr['longitude'] = $data['longitude'];
			return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function logout($user_id, $device_id) {
		$time=time();
		$updateQuery = "UPDATE tbl_customers set device_id=NULL, device_token=NULL, device_type=NULL where id=? AND device_id=?";
		$updateCust = $this->conn->prepare($updateQuery);
		$updateCust->bindParam(1, $user_id);
		$updateCust->bindParam(2, $device_id);
		$updateCust->execute();
		//print_r($updateCust->errorInfo());
		if($updateCust->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
	
	public function addCard($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault='NO') {
		$check=$this->isCardAlreadyAdded($user_id, $card_number);
		if($check)
			return 'ALREADY_SAVED';
			
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->addCardStripe($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault);
		} else {
			$result=$this->addCardPaypal($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault);
		}
		return $result;   
    }
    
    public function deleteCard($user_id, $cardid) {
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->deleteCardStripe($user_id, $cardid);
		} else {
			$result=$this->deleteCardPaypal($user_id, $cardid);
		}
		return $result;
    }
    
    public function editCard($user_id, $first_name, $expiry_month, $expiry_year, $cardid, $hitPaypal=1, $isdefault, $card_number, $card_type) {
		$check=$this->isCardAlreadyAdded($user_id, $card_number, $cardid);
		if($check)
			return 'ALREADY_SAVED';
			
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->editCardStripe($user_id, $first_name, $expiry_month, $expiry_year, $cardid, $hitPaypal, $isdefault, $card_number, $card_type);
		} else {
			$result=$this->editCardPaypal($user_id, $first_name, $expiry_month, $expiry_year, $cardid, $hitPaypal, $isdefault, $card_number, $card_type);
		}
		return $result;  
    }

    public function cardList($userid) {
        $cardQuery = "SELECT id, card_number, name, expiry_month, isdefault, expiry_year, card_type, created FROM tbl_credit_cards WHERE customer_id=? order by created desc";
        $card_data = $this->conn->prepare($cardQuery);
		$card_data->bindParam(1, $userid);
		$card_data->execute();
		//print_r($card_data->errorInfo());
		if($card_data->rowCount()>0) {
			//echo $card_data->rowCount();
			$output = array();
			$counter = 0;
			while ($array = $card_data->fetch(PDO::FETCH_ASSOC)) {
				//print_r($array);
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['card_number'] = $array['card_number'];
				$output[$counter]['firstname'] = $array['name'];
				$output[$counter]['expiry_month'] = $array['expiry_month'];
				$output[$counter]['expiry_year'] = $array['expiry_year'];
				$output[$counter]['card_type'] = $array['card_type'];
				$output[$counter]['isdefault'] = $array['isdefault'];
				$output[$counter]['created'] = $array['created'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD';
		}
	}
		
	public function cartData($userid, $branch_id) {
		$cartQuery = "SELECT tc.id, tc.branch_id, tc.data, tb.name from tbl_carts tc JOIN tbl_branches tb ON tb.id=tc.branch_id WHERE tc.customer_id=?";
		if ($branch_id>0)
			$cartQuery .= " AND branch_id=?";
		$card_data = $this->conn->prepare($cartQuery);
		$card_data->bindParam(1, $userid);
		if ($branch_id>0)
			$card_data->bindParam(2, $branch_id);
		$card_data->execute();
		// print_r($card_data->errorInfo());
		if($card_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $card_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['branch_id'] = $array['branch_id'];
				$output[$counter]['branch_name'] = $array['name'];
				$output[$counter]['data'] = $array['data'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD';
		}
	}
		
	public function saveCartData($user_id, $branch_id, $data) {
		$created=time();
		$cartQuery = "INSERT INTO tbl_carts SET customer_id=?, branch_id=?, data=?, created=?";
		$insert_cart = $this->conn->prepare($cartQuery);
		$insert_cart->bindParam(1, $user_id);
		$insert_cart->bindParam(2, $branch_id);
		$insert_cart->bindParam(3, $data);
		$insert_cart->bindParam(4, $created);
		$insert_cart->execute();
		// print_r($insert_cart->errorInfo());
		if($insert_cart->rowCount()>0) {
			return 'SUCCESSFULLY_SAVED';
		} else {
			$decodedData=json_decode($data);
			if (count($decodedData)==0) {
				$cartDeleteQuery = "DELETE FROM tbl_carts where customer_id=? AND branch_id=?";
				$delete_cart = $this->conn->prepare($cartDeleteQuery);
				$delete_cart->bindParam(1, $user_id);
				$delete_cart->bindParam(2, $branch_id);
				$delete_cart->execute();
				if($delete_cart->rowCount()>0) {
					return 'SUCCESSFULLY_DELETED';
				} else {
					return 'NO_RECORD';
				}
			} else {
				$cartUpdateQuery = "UPDATE tbl_carts SET data=?, updated=? where customer_id=? AND branch_id=?";
				$update_cart = $this->conn->prepare($cartUpdateQuery);
				$update_cart->bindParam(1, $data);
				$update_cart->bindParam(2, $created);
				$update_cart->bindParam(3, $user_id);
				$update_cart->bindParam(4, $branch_id);
				$update_cart->execute();
				// print_r($update_cart->errorInfo());
				if($update_cart->rowCount()>0) {
					return 'SUCCESSFULLY_UPDATED';
				} else {
					return 'NO_RECORD';
				}
			}
		}
	}
	
	public function addEvent($customer_id, $name, $mobile, $address, $no_of_guest, $event_date, $occasion, $event, $event_id, $device_id, $device_type, $ip, $branch_id) {
		$created=time();
		if ($event_id>0) {
			$eventQuery = "UPDATE tbl_customer_events SET customer_id=?, name=?, mobile=?, address=?, no_of_guest=?, occasion=?, event=?, event_date=?, device_id=?, device_type=?, ip=?, updated=?, branch_id=? WHERE id=?";
		} else {
			$eventQuery = "INSERT INTO tbl_customer_events SET customer_id=?, name=?, mobile=?, address=?, no_of_guest=?, occasion=?, event=?, event_date=?, device_id=?, device_type=?, ip=?, created=?, branch_id=?";
		}
		$prepare_event = $this->conn->prepare($eventQuery);
		$prepare_event->bindParam(1, $customer_id);
		$prepare_event->bindParam(2, $name);
		$prepare_event->bindParam(3, $mobile);
		$prepare_event->bindParam(4, $address);
		$prepare_event->bindParam(5, $no_of_guest);
		$prepare_event->bindParam(6, $occasion);
		$prepare_event->bindParam(7, $event);
		$prepare_event->bindParam(8, $event_date);
		$prepare_event->bindParam(9, $device_id);
		$prepare_event->bindParam(10, $device_type);
		$prepare_event->bindParam(11, $ip);
		$prepare_event->bindParam(12, $created);
		$prepare_event->bindParam(13, $branch_id);
		if($event_id>0)
			$prepare_event->bindParam(14, $event_id);
		$prepare_event->execute();
		//print_r($prepare_event->errorInfo());
		if($prepare_event->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function getEvents($userid) {
		$eventQuery = "SELECT * FROM tbl_customer_events WHERE customer_id=? order by event_date desc";
		$event_data = $this->conn->prepare($eventQuery);
		$event_data->bindParam(1, $userid);
		$event_data->execute();
		// print_r($event_data->errorInfo());
		if($event_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $event_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['name'] = $array['name'];
				$output[$counter]['mobile'] = $array['mobile'];
				$output[$counter]['no_of_guest'] = $array['no_of_guest'];
				$output[$counter]['address'] = $array['address'];
				$output[$counter]['occasion'] = $array['occasion'];
				$output[$counter]['event'] = $array['event'];
				$output[$counter]['event_date'] = $array['event_date'];
				$output[$counter]['reply'] = $array['reply'];
				$output[$counter]['reply_at'] = $array['reply_at'];
				$output[$counter]['created'] = $array['created'];
				$output[$counter]['status'] = $array['status'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD';
		}
	}
	
	public function getPageContent($pagename) {
		$pageQuery = "SELECT title, content FROM tbl_page_contents WHERE page_name=? AND app_type='C' AND platform='M'";
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
	
	public function makeOrder($user_id, $branch_id, $data, $address_id, $card_id, $distance, $amount, $type, $order_type, $order_delivery_time=0, $comment, $tax, $device_id, $device_type, $ipaddress, $loyalty_points, $loyalty_point_value, $delivery_charges=0, $promocode, $order_amount) {
		$ipaddress='12.12.12.12';
		if ($loyalty_points>0){
			$wallet_points_validate=$this->validate_available_wallet_points($user_id, $loyalty_points);
			if ($wallet_points_validate=='WALLET_POINTS_LOW_THEN_APPPLIED')
				return $wallet_points_validate;
		}
		
		if ($promocode!='' && $promocode!='0' && $promocode!=NULL) {
			$promocode_validate=$this->validate_promocode_make_order($user_id, $promocode);
			if ($promocode_validate!='SUCCESSFULLY_APPLIED') 
				return $promocode_validate;
		}
		
		if($order_type=='C') {
			$order_delivery_time=time();
		}
		
		//$item_amount = $amount - ($tax + $delivery_charges);
		$earned_points = floor($amount / AMOUNT_TO_BE_SPENT_FOR_LOYALTY_POINTS);
		$invoice_id=0;
		$created = time();
		$orderQuery = "INSERT INTO tbl_orders SET customer_id=?, branch_id=?, distance=?, amount=?, tax_amount=?, type=?, order_type=?, data=?, special_comment=?, device_id=?, device_type=?, ipaddress=?, created=?, order_delivery_time=?, invoice_id=?, status='N', loyalty_points=?, per_loyalty_points=?, delivery_charges=?, earned_loyalty_points=?";
		if ($promocode!='' && $promocode!='0' && $promocode!=NULL)
			$orderQuery .= ", applied_promocode=?";
		
		$insert_order = $this->conn->prepare($orderQuery);
		$insert_order->bindParam(1, $user_id);
		$insert_order->bindParam(2, $branch_id);
		$insert_order->bindParam(3, $distance);
		$insert_order->bindParam(4, $amount);
		$insert_order->bindParam(5, $tax);
		$insert_order->bindParam(6, $type);
		$insert_order->bindParam(7, $order_type);
		$insert_order->bindParam(8, $data);
		$insert_order->bindParam(9, $comment);
		$insert_order->bindParam(10, $device_id);
		$insert_order->bindParam(11, $device_type);
		$insert_order->bindParam(12, $ipaddress);
		$insert_order->bindParam(13, $created);
		$insert_order->bindParam(14, $order_delivery_time);
		$insert_order->bindParam(15, $invoice_id);
		$insert_order->bindParam(16, $loyalty_points);
		$insert_order->bindParam(17, $loyalty_point_value);
		$insert_order->bindParam(18, $delivery_charges);
		$insert_order->bindParam(19, $earned_points);
		if ($promocode!='' && $promocode!='0' && $promocode!=NULL)
			$insert_order->bindParam(20, $promocode);
		$insert_order->execute();
		//print_r($insert_order->errorInfo());
		if ($insert_order->rowCount()>0) {
			$order_id = $this->conn->lastInsertId();
			
			$invoice_id=$this->invoiceIDGenerateAndSave($order_id, $user_id, $branch_id);
			$this->saveOrderAddress($user_id, $address_id, $order_id);
			$this->saveCreditCard($user_id, $card_id, $order_id);
			$this->saveTaxes($branch_id, $order_id, $order_amount);
			
			if ($promocode!='' && $promocode!='0' && $promocode!=NULL) {
				$this->savePromocode($order_id, $promocode);
			}
			$items=json_decode($data);
			for ($i=0; $i<count($items); $i++) {
				$this->saveItems($order_id, $items[$i]);
			}
			$output["invoice_id"] = $invoice_id;
			$output["branch_id"] = $branch_id;
			
			$loyaltyPoints = $this->getLoyaltyPoints($user_id);
			$output["wallet_points"] = $loyaltyPoints["wallet_points"];
			$output["loyalty_point_value"] = $loyaltyPoints["loyalty_point_value"];
			
			$referralerData=$this->getReferralerData();
			$output["referral"] = $referralerData;
			
			$this->makeAddressDefault($user_id, $address_id, 'ORDER');
			$description = "NEW ORDER - $invoice_id BY CUSTOMER ID $user_id";
			$this->saveTransactionData($order_id, $user_id, 'ORDER_CREATED', 'NEW_ORDER', $device_id, $device_type, $ipaddress, $description);
			
			$makePayment = $this->makePayment($order_id, $device_id, $device_type, $ipaddress);
			if ($makePayment=='FAILED' || $makePayment=='INVALID_CARD') {
				$this->updateOrderStatus($order_id);
				return 'PAYMENT_FAILED';
			}
			$message = "$invoice_id - A New Order Request has been received";
			$this->SendPushToAdmin($order_id, $message, 'NEW_ORDER');

			$orderMobileData = $this->getOrderMobileData($order_id);
			$this->sendTemplatesInSMS('new_order', '', $orderMobileData['mobile'], $orderMobileData['mobile_code'], $invoice_id);
			$this->sendTemplatesInMail('new_order_mail', $orderMobileData['firstname'], $orderMobileData['email'], $invoice_id);
			return $output;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    //A->APPROVED, D->DECLINED, CB->CANCELLED BY BRANCH, CC->CANCELLED BY CUSTOMER, P->PREPARED, AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED, PU->PICKEDUP BY CUSTOMER
    public function getOrders($user_id, $branch_id, $status, $type) {
		$this->includeGlobalSiteSetting();
		if($status=='R') {
			$status="N,A,P,AD,OD";
		} else {
			$status="DL,PU,CC";
		};
		$current_time=time();
		//echo $status;
		$orderQuery = "select o.*, todm.firstname as del_firstname, todm. lastname as del_lastname, todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, tb.latitude as blatitude, tb.longitude as blongitude from tbl_orders o LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id AND todm.delivery_man_id =o.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=o.branch_id where o.customer_id=? AND find_in_set(cast(o.status as char), ?)";
		if ($type!='A')
			$orderQuery .="  AND o.order_type=?";
		
		$orderQuery .=" ORDER BY o.id desc";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $user_id);
		$order_data->bindParam(2, $status);
		if ($type!='A')
			$order_data->bindParam(3, $type);
	    $order_data->execute();
	   //print_r($order_data->errorInfo());
	    //exit;
	    //echo $order_data->rowCount();
	    //exit;
	    if($order_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $order_data->fetch(PDO::FETCH_ASSOC)) {
				//print_r($data);
				//exit;
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
				$order_calcellation_duration=ORDER_CANCELLATION_DURATION;
				$can_cancelled='Y';
				if ($current_time >= ($data['created'] + $order_calcellation_duration) || ($data['status']=='P' || $data['status']=='AD' || $data['status']=='OD'))
					$can_cancelled='N';
				$responseArr[$i]['can_cancelled'] = $can_cancelled;
				
				//branch details
				$responseArr[$i]['branch']['id'] = $data['branch_id'];
				$responseArr[$i]['branch']['name'] = $data['bname'];
				$responseArr[$i]['branch']['mobile_code'] = $data['bmobile_code'];
				$responseArr[$i]['branch']['mobile'] = $data['bmobile'];
				$responseArr[$i]['branch']['address'] = $data['baddress'];
				$responseArr[$i]['branch']['latitude'] = $data['blatitude'];
				$responseArr[$i]['branch']['longitude'] = $data['blongitude'];
				
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
    
    public function getOrdersDetails($user_id, $order_id) {
		$this->includeGlobalSiteSetting();
		$current_time=time();
		$orderQuery = "select tos.*, toca.firstname as cust_firstname, toca.lastname as cust_lastname, toca.mobile_code as cust_mobile_code, toca.mobile as cust_mobile, toca.address as cust_address, toca.latitude as cust_latitude, toca.longitude as cust_longitude, toca.profile_pic as cust_profile_pic, todm.firstname as del_firstname, todm. lastname as del_lastname, todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, tb.name as br_name, tb.mobile_code as br_mobile_code, tb.mobile as br_mobile, tb.address as br_address, tb.latitude as br_latitude, tb.longitude as br_longitude from tbl_orders as tos LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=tos.id LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=tos.id AND todm.delivery_man_id=tos.delivery_man_id JOIN tbl_branches tb ON tb.id=tos.branch_id where tos.id=?";
		$order_data = $this->conn->prepare($orderQuery);
		$order_data->bindParam(1, $order_id);
		//print_r($order_data->errorInfo());
	    $order_data->execute();
	    if($order_data->rowCount()>0) {
			$responseArr=array();
	        $data = $order_data->fetch(PDO::FETCH_ASSOC);
			//order table data
			$responseArr['id'] = $data['id'];
			$responseArr['invoice_id'] = $data['invoice_id'];
			$responseArr['distance'] = $data['distance'];
			$responseArr['amount'] = $data['amount'];
			$responseArr['tax_amount'] = $data['tax_amount'];
			$responseArr['type'] = $data['type'];
			$responseArr['order_type'] = $data['order_type'];
			$responseArr['status'] = $data['status'];
			$responseArr['orderedat'] = $data['created'];
			$order_calcellation_duration=ORDER_CANCELLATION_DURATION;
			$can_cancelled='Y';
			if ($current_time >= ($data['created'] + $order_calcellation_duration) || ($data['status']=='P' || $data['status']=='AD' || $data['status']=='OD'))
				$can_cancelled='N';
			$responseArr['can_cancelled'] = $can_cancelled;
			
			//branch details
			$responseArr['branch']['name'] = $data['br_name'];
			$responseArr['branch']['mobile_code'] = $data['br_mobile_code'];
			$responseArr['branch']['mobile'] = $data['br_mobile'];
			$responseArr['branch']['address'] = $data['br_address'];
			$responseArr['branch']['latitude'] = $data['br_latitude'];
			$responseArr['branch']['longitude'] = $data['br_longitude'];
			
			//customer address details
			$responseArr['customer']['id'] = $data['customer_id'];
			$responseArr['customer']['firstname'] = $data['cust_firstname'];
			$responseArr['customer']['lastname'] = $data['cust_lastname'];
			$responseArr['customer']['mobile_code'] = $data['cust_mobile_code'];
			$responseArr['customer']['mobile'] = $data['cust_mobile'];
			$responseArr['customer']['address'] = $data['cust_address'];
			$responseArr['customer']['latitude'] = $data['cust_latitude'];
			$responseArr['customer']['longitude'] = $data['cust_longitude'];
			$responseArr['customer']['profile_pic'] = $data['cust_profile_pic']!='' ? CUSTOMER_PIC_URL.'thumb/'.$data['cust_profile_pic'] : '';
			
			//delivery man details
			if($data['delivery_man_id']==0) {
				$responseArr['delivery_man']=NULL;
			} else {
				$responseArr['delivery_man']['id'] = $data['delivery_man_id'];
				$responseArr['delivery_man']['firstname'] = $data['del_firstname'];
				$responseArr['delivery_man']['lastname'] = $data['del_lastname'];
				$responseArr['delivery_man']['mobile_code'] = $data['del_mobile_code'];
				$responseArr['delivery_man']['mobile'] = $data['del_mobile'];
				$responseArr['delivery_man']['profile_pic'] = $data['del_profile_pic']!='' ? DELIVERYMAN_PIC_URL.'thumb/'.$data['del_profile_pic'] : '';
			}
			
			//taxes details
			$taxQuery = "select title, tax, tax_amount from tbl_order_taxes where order_id=?";
			$tax_data = $this->conn->prepare($taxQuery);
			$tax_data->bindParam(1, $data['id']);
			$tax_data->execute();
			$k=0;
			while ($taxData = $tax_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr['tax'][$k]['title'] = $taxData['title'];
				$responseArr['tax'][$k]['tax'] = $taxData['tax'];
				$responseArr['tax'][$k]['tax_amount'] = $taxData['tax_amount'];
				++$k;
			}
			
			// order items
			$itemQuery = "select toi.id as rowid, toi.item_id, toi.avg_rating, toi.is_nonveg, toi.is_new, toi.is_featured, toi.name, toi.image, toi.price_name, toi.unit_price, toi.extra_price, toi.attribute_price, toi.quantity, toi.total_price, toi.data, (select IF(count(*)>0,concat(rating, '----', ifnull(comment, '')), 'N') from tbl_item_rating_log tirl where tirl.item_id=toi.item_id AND tirl.customer_id=? AND tirl.order_id=toi.order_id) as is_rated from tbl_order_items toi where toi.order_id=?";
			$item_data = $this->conn->prepare($itemQuery);
			$item_data->bindParam(1, $user_id);
			$item_data->bindParam(2, $data['id']);
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
				$responseArr['items'][$j]['rowid'] = $itemData['rowid'];
				$responseArr['items'][$j]['item_id'] = $itemData['item_id'];
				$responseArr['items'][$j]['avg_rating'] = $itemData['avg_rating'];
				$responseArr['items'][$j]['is_nonveg'] = $itemData['is_nonveg'];
				$responseArr['items'][$j]['is_new'] = $itemData['is_new'];
				$responseArr['items'][$j]['is_featured'] = $itemData['is_featured'];
				$responseArr['items'][$j]['item_name'] = $itemData['name'];
				$responseArr['items'][$j]['thumb'] = $itemData['image']!='' ? MENU_PIC_URL.'thumb/'.$itemData['image'] : '';
				$responseArr['items'][$j]['price_name'] = $itemData['price_name'];
				$responseArr['items'][$j]['unit_price'] = $itemData['unit_price'];
				$responseArr['items'][$j]['extra_price'] = $itemData['extra_price'];
				$responseArr['items'][$j]['attribute_price'] = $itemData['attribute_price'];
				$responseArr['items'][$j]['quantity'] = $itemData['quantity'];
				$responseArr['items'][$j]['total_price'] = $itemData['total_price'];
				$responseArr['items'][$j]['data'] = $itemData['data'];
				$responseArr['items'][$j]['is_rated'] = $is_rated;
				
				if ($rating>0) {
					$responseArr['items'][$j]['ratings']['id'] = $itemData['rowid'];
					$responseArr['items'][$j]['ratings']['name'] = $itemData['name'];
					$responseArr['items'][$j]['ratings']['image'] = $itemData['image']!='' ? MENU_PIC_URL.'thumb/'.$itemData['image'] : '';
					$responseArr['items'][$j]['ratings']['rating'] = $rating;
					$responseArr['items'][$j]['ratings']['comment'] = $comment;
				} else {
					$responseArr['items'][$j]['ratings']=NULL;
				}
				++$j;
			}
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function orderCancel($user_id, $order_id, $invoice_id, $orderedat, $device_id, $device_type, $ip_address) {
		$this->includeGlobalSiteSetting();
		$current_time=time();
		$order_calcellation_duration=ORDER_CANCELLATION_DURATION;

		if ($current_time >= ($orderedat + $order_calcellation_duration))
			return 'CANCELLATION_DURATION_EXPIRED';
			
	    $refundPayment=$this->refundPayment($order_id, $device_id, $device_type, $ip_address, 0);
		if ($refundPayment=='FAILED_TO_REFUND')
			return 'FAILED_TO_REFUND';
		
		$updateQuery = "UPDATE tbl_orders SET status='CC' where id=? AND (status='A' OR status='N') AND customer_id=?";
		$update_status = $this->conn->prepare($updateQuery);
		$update_status->bindParam(1, $order_id);
		$update_status->bindParam(2, $user_id);
		$update_status->execute();
		//print_r($update_status->errorInfo());
		if($update_status->rowCount()>0) {
			$description = "ORDER CANCELLED - $invoice_id order cancelled by customer - $user_id";
			$this->saveTransactionData($order_id, $user_id, 'CC', 'ORDER_CANCEL_BY_CUSTOMER', $device_id, $device_type, $ip_address, $description);
			
			$message="$invoice_id - Order has been Cancelled by customer";
			$this->SendPushToAdmin($order_id, $message, 'CANCEL_ORDER');
			
			$orderMobileData=$this->getOrderMobileData($order_id);
			$this->sendTemplatesInSMS('order_cacelled_by_customer', '', $orderMobileData['mobile'], $orderMobileData['mobile_code'], $invoice_id);
			$this->sendTemplatesInMail('order_cacelled_by_customer_mail', $orderMobileData['firstname'], $orderMobileData['email'], $invoice_id);
			return 'SUCCESSFULLY_UPDATED';
		} else {
			return 'UNABLE_TO_PROCEED';
		}
	}
	
	public function makeMenuFavourite($user_id, $item_id) {
        $created=time();
		$insertQuery = "INSERT INTO tbl_favourite_items SET customer_id=?, item_id=?, created=?";
		$insert_fav = $this->conn->prepare($insertQuery);
		$insert_fav->bindParam(1, $user_id);
		$insert_fav->bindParam(2, $item_id);
		$insert_fav->bindParam(3, $created);
		$insert_fav->execute();
		//print_r($insert_fav->errorInfo());
		if($insert_fav->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'ALREADY_ADDED';
		}
    }
    
    public function getFavouriteMenus($user_id, $branch_id) {		
		$featuredUqery = "select ti.id, ti.is_featured, tc.id as catid, tc.name as catname, tc.description as catdesc, tc.logo as catimage, ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, (select group_concat(image SEPARATOR '-++-') from tbl_item_images tii where tii.item_id=ti.id AND tii.status='A' AND tii.isdefault='Y') as images, (select group_concat(concat(name, '--', price) SEPARATOR '-++-') from tbl_item_prices tip where tip.item_id=ti.id AND tip.status='A' AND tip.isdefault='Y') as prices from tbl_favourite_items tfi JOIN tbl_items ti ON ti.id=tfi.item_id JOIN tbl_categories tc ON tc.id=ti.category_id where ti.status='A' AND ti.branch_id=? AND tfi.customer_id=?";
		$featured_data = $this->conn->prepare($featuredUqery);
		$featured_data->bindParam(1, $branch_id);
		$featured_data->bindParam(2, $user_id);
	    $featured_data->execute();
	    //print_r($featured_data->errorInfo());
	    if($featured_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	        while ($data = $featured_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['catid'];
				$responseArr[$i]['name'] = $data['catname'];
				$responseArr[$i]['description'] = $data['catdesc'];
				if($data['catimage']!='') {
					$responseArr[$i]['image_thumb'] = CATEGORY_PIC_URL.'thumb/'.$data['catimage'];
					$responseArr[$i]['image_large'] = CATEGORY_PIC_URL.'large/'.$data['catimage'];
				}
				$responseArr[$i]['itemDataList'][0]['id'] = $data['id'];
				$responseArr[$i]['itemDataList'][0]['category_id'] = $data['catid'];
				$responseArr[$i]['itemDataList'][0]['name'] = $data['name'];
				$responseArr[$i]['itemDataList'][0]['avg_rating'] = $data['avg_rating'];
				$responseArr[$i]['itemDataList'][0]['is_nonveg'] = $data['is_nonveg'];
				$responseArr[$i]['itemDataList'][0]['is_new'] = $data['is_new'];
				$responseArr[$i]['itemDataList'][0]['is_featured'] = $data['is_featured'];
				$responseArr[$i]['itemDataList'][0]['is_fav'] = 'Y';
				
				if ($data['images']!=NULL) {
    			    $imageArrayFinal=explode('-++-', $data['images']);
    		        for($j=0; $j<count($imageArrayFinal); $j++) {
    		            //$imageArrayFinal=explode(',', $imageArray[$j]);
    		            $responseArr[$i]['itemDataList'][0]['images'][$j]['thumb'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'thumb/'.$imageArrayFinal[$j] : '';
    		            $responseArr[$i]['itemDataList'][0]['images'][$j]['large'] = $imageArrayFinal[$j]!='' ? MENU_PIC_URL.'large/'.$imageArrayFinal[$j] : '';
    		        }
    		    }
    		    
    		    if ($data['prices']!=NULL) {
    			    $priceArray=explode('-++-', $data['prices']);
    		        for($k=0; $k<count($priceArray); $k++) {
    		            $priceArrayFinal=explode('--', $priceArray[$k]);
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['name'] = $priceArrayFinal[0];
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['price'] = $priceArrayFinal[1];
    		            $responseArr[$i]['itemDataList'][0]['prices'][$k]['currency'] = SITE_CURRENCY;
    		        }
    		    }
    		    
    		    $attr=$this->getMenuAttributes($data['id'], 'Y');
    		    if ($attr=='NO_RECORD_FOUND') {
					$responseArr[$i]['itemDataList'][0]['default_attributes']=array();
				} else {
					$responseArr[$i]['itemDataList'][0]['default_attributes']=$attr;
				}
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function deleteFavouriteMenu($user_id, $item_id) {
		$deleteQuery = "delete from tbl_favourite_items where customer_id=? and item_id=?";
		$delete_fav = $this->conn->prepare($deleteQuery);
		$delete_fav->bindParam(1, $user_id);
		$delete_fav->bindParam(2, $item_id);
		$delete_fav->execute();
		//print_r($delete_fav->errorInfo());
		if($delete_fav->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'NOT_FOUND';
		}
    }
    
    public function getFavouriteMenuStatus($user_id, $item_ids) {
		$menuUqery = "select IF(id>0, 'Y', 'N') as is_fav, item_id from tbl_favourite_items tfi where tfi.customer_id=? AND item_id IN ($item_ids)";
		$menu_data = $this->conn->prepare($menuUqery);
		$menu_data->bindParam(1, $user_id);
		//$menu_data->bindParam(2, $item_ids);
	    $menu_data->execute();
	    //print_r($menu_data->errorInfo());
	    if($menu_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $menu_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['id'] = $data['item_id'];
				$responseArr[$i]['is_fav'] = $data['is_fav'];
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function postRatingOnItem($user_id, $item_id, $order_id, $rating, $comment, $row_id) {
        $created=time();
        if ($row_id==0) {
			 $insertQuery = "INSERT INTO tbl_item_rating_log SET item_id=?, customer_id=?, order_id=?, rating=?, comment=?, created=?";
		} else {
			$insertQuery = "UPDATE tbl_item_rating_log SET rating=?, comment=?, updated=? where id=?";
		}
		$insert_rating = $this->conn->prepare($insertQuery);
		if ($row_id==0) {
			$insert_rating->bindParam(1, $item_id);
			$insert_rating->bindParam(2, $user_id);
			$insert_rating->bindParam(3, $order_id);
			$insert_rating->bindParam(4, $rating);
			$insert_rating->bindParam(5, $comment);
			$insert_rating->bindParam(6, $created);
		} else {
			$insert_rating->bindParam(1, $rating);
			$insert_rating->bindParam(2, $comment);
			$insert_rating->bindParam(3, $created);
			$insert_rating->bindParam(4, $row_id);
		}
		$insert_rating->execute();
		//print_r($insert_rating->errorInfo());
		//exit;
		if($insert_rating->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'ALREADY_ADDED';
		}
    }
    
    public function getItemRatings($user_id, $item_id) {
		$ratingUqery = "SELECT tirs.*, ti.avg_rating, ti.tot_rating_count from tbl_item_rating_summary tirs JOIN tbl_items ti ON ti.id=tirs.item_id where tirs.item_id=?";
		$rating_data = $this->conn->prepare($ratingUqery);
		$rating_data->bindParam(1, $item_id);
	    $rating_data->execute();
	    $responseArr=array();
	    //print_r($rating_data->errorInfo());
	    if($rating_data->rowCount()>0) {
	        $data = $rating_data->fetch(PDO::FETCH_ASSOC);
			$responseArr['one_star_count'] = $data['one_star_count'];
			$responseArr['two_star_count'] = $data['two_star_count'];
			$responseArr['three_star_count'] = $data['three_star_count'];
			$responseArr['four_star_count'] = $data['four_star_count'];
			$responseArr['five_star_count'] = $data['five_star_count'];
			$responseArr['avg_rating'] = $data['avg_rating'];
			$responseArr['tot_rating_count'] = $data['tot_rating_count'];
		} else {
			$responseArr['one_star_count'] = 0;
			$responseArr['two_star_count'] = 0;
			$responseArr['three_star_count'] = 0;
			$responseArr['four_star_count'] = 0;
			$responseArr['five_star_count'] = 0;
			$responseArr['avg_rating'] = 0;
			$responseArr['tot_rating_count'] = 0;
		}
		return $responseArr;
    }
    
    public function getComments($item_id, $pageno=0) {
		$offset = 10;
        $limit = ($pageno - 1) * 10;
		$ratingUqery = "select tirl.rating, tirl.comment, tirl.created, tc.firstname, tc.lastname, tc.profile_pic from tbl_item_rating_log tirl JOIN tbl_customers tc ON tc.id=tirl.customer_id where item_id=? AND (comment!='' || comment IS NOT NULL) order by tirl.id desc";
		if($pageno>0)
			$ratingUqery .= " limit ?, ?";
		$rating_data = $this->conn->prepare($ratingUqery);
		$rating_data->bindParam(1, $item_id);
		if($pageno>0) {
			$rating_data->bindParam(2, $limit, PDO::PARAM_INT);
			$rating_data->bindParam(3, $offset, PDO::PARAM_INT);
		}
	    $rating_data->execute();
	    //print_r($rating_data->errorInfo());
	    if($rating_data->rowCount()>0) {
			$responseArr=array();
			$i=0;
	         while ($data = $rating_data->fetch(PDO::FETCH_ASSOC)) {
				$responseArr[$i]['rating'] = $data['rating'];
				$responseArr[$i]['comment'] = $data['comment'];
				$responseArr[$i]['created'] = $data['created'];
				$responseArr[$i]['customer']['firstname'] = $data['firstname'];
				$responseArr[$i]['customer']['lastname'] = $data['lastname'];
				$responseArr[$i]['customer']['profile_pic'] =  $data['profile_pic']=='' || $data['profile_pic']===NULL ? '' : CUSTOMER_PIC_URL.'thumb/'.$data['profile_pic'];
				++$i;
			 }
			 return $responseArr;
		} else {
			return 'NO_RECORD_FOUND';
		}
    }
    
    public function getCommentsPages($item_id) {
		$ratingUqery = "select tirl.rating, tirl.comment, tirl.created, tc.firstname, tc.lastname, tc.profile_pic from tbl_item_rating_log tirl JOIN tbl_customers tc ON tc.id=tirl.customer_id where item_id=? AND (comment!='' || comment IS NOT NULL)";	
		$rating_data = $this->conn->prepare($ratingUqery);
		$rating_data->bindParam(1, $item_id);
	    $rating_data->execute();
	    //print_r($rating_data->errorInfo());
        $num_rows = $rating_data->rowCount();
        $totpages = ceil($num_rows / 10);
        return $totpages;
    }
    
    public function getEventOptions() {
		$eventQuery = "SELECT * FROM tbl_event_options WHERE status='A'";
		$event_data = $this->conn->prepare($eventQuery);
		$event_data->execute();
		if($event_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $event_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['name'] = $array['name'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function getPromotions($branch_id) {
		$currenttime=time();
		$promotionQuery = "SELECT id, title, promotion_code, image FROM tbl_promotions WHERE status='A' AND start_date<= IF(start_date=0, 0, ?) AND end_date>= IF(end_date=0, 0, ?) AND FIND_IN_SET(if(branch_ids=0, 0, ?), branch_ids)";
		$promotion_data = $this->conn->prepare($promotionQuery);
		$promotion_data->bindParam(1, $currenttime);
        $promotion_data->bindParam(2, $currenttime);
        $promotion_data->bindParam(3, $branch_id);
		$promotion_data->execute();
		if($promotion_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $promotion_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['promotion_code'] = $array['promotion_code'];
				$output[$counter]['title'] = $array['title'];
				$output[$counter]['image'] = PROMOTION_PIC_URL.'thumb/'.$array['image'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function getPromotionDetails($promotion_id) {
		$currenttime=time();
		$promotionQuery = "SELECT * FROM tbl_promotions WHERE id=?";
		$promotion_data = $this->conn->prepare($promotionQuery);
		$promotion_data->bindParam(1, $promotion_id);
		$promotion_data->execute();
		if($promotion_data->rowCount()>0) {
			$output = array();
			$array = $promotion_data->fetch(PDO::FETCH_ASSOC);
			$output['id'] = $array['id'];
			$output['promotion_code'] = $array['promotion_code'];
			$output['title'] = $array['title'];
			$output['image'] = PROMOTION_PIC_URL.'thumb/'.$array['image'];
			$output['description'] = $array['description'];
			$output['no_of_attempts_user'] = $array['no_of_attempts_user'];
			$output['total_promocode_attempts'] = $array['total_promocode_attempts'];
			$output['tot_user_applied'] = $array['tot_user_applied'];
			$output['promotion_applicable_for'] = $array['promotion_applicable_for'];
			$output['start_date'] = $array['start_date'];
			$output['end_date'] = $array['end_date'];
			$output['order_type'] = $array['order_type'];
			$output['delivery_options'] = $array['delivery_options'];
			$output['promotion_type'] = $array['promotion_type'];
			$output['loyalty_points'] = $array['loyalty_points'];
			$output['loyalty_amount_spent'] = $array['loyalty_amount_spent'];
			$output['discount_type'] = $array['discount_type'];
			$output['discount'] = $array['discount'];
			$output['min_purchase'] = $array['min_purchase'];
			$output['max_discount'] = $array['max_discount'];
			$output['quantity'] = $array['quantity'];
			$output['loyalty_amount_spent'] = $array['loyalty_amount_spent'];		
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function deactivateAccount($userid, $oldpassword) {
		$time=time();
		$md5password=md5($oldpassword);
		$userUqery = "select id, password from tbl_customers where id=?";
		$login_customer = $this->conn->prepare($userUqery);
        $login_customer->bindParam(1, $userid);
        //$login_customer->bindParam(2, $oldpassword);
        //$login_customer->bindParam(3, $md5password);
	    $login_customer->execute();
		if($login_customer->rowCount()>0) {
			$customerData = $login_customer->fetch(PDO::FETCH_ASSOC);
			if(($customerData['password']!=$oldpassword) && ($customerData['password']!=$md5password) && !$this->matchHashPassword($oldpassword, $customerData['password'])) {
				return 'INVALID_OLD_PASSWORD';
			} else {
				$updateQuery = "UPDATE tbl_customers set customer_deactivated='Y', customer_deactivation_time=? where id=?";
				$customer_update = $this->conn->prepare($updateQuery);
				$customer_update->bindParam(1, $time);
				$customer_update->bindParam(2, $userid);
				//print_r($customer_update->errorInfo());
				if($customer_update->execute()) {
					return 'ACCOUNT_DEACTIVATED';
				} else {
					return 'UNABLE_TO_PROCEED';
				}			
			}
		} else {
			return 'INVALID_USER_ID';
		}
	}
	
	public function getLoyaltyPoints($user_id) {
        $created=time();
		$selectQuery = "SELECT wallet_points from tbl_customers where id=?";
		$customer_data = $this->conn->prepare($selectQuery);
		$customer_data->bindParam(1, $user_id);
		$customer_data->execute();
	    //print_r($customer_data->errorInfo());
		$data = $customer_data->fetch(PDO::FETCH_ASSOC);
		//print_r($data);
		$res=array();
		$res['wallet_points']=$data['wallet_points'];
		$res['loyalty_point_value']=LOYALTY_POINT_VALUE;
		return $res;
    }
    
    public function applyPromocode($user_id, $company_id, $branch_id, $category_ids, $item_ids, $amount, $type, $order_type, $promocode) {
		$responseArr=array();
		$promocode_data=$this->is_valid_promocode($promocode);
		if (!$promocode_data) {
			return 'PRMOCODE_NOT_EXIST';
		}
		if ($promocode_data['total_promocode_attempts']>0) {
			$check_max_uses_limit=$this->check_max_uses_limit($promocode_data['total_promocode_attempts'], $promocode_data['tot_user_applied']);
			if($check_max_uses_limit)
				return 'PRMOCODE_USES_LIMIT_EXPIRED';
		}
		
		if ($promocode_data['no_of_attempts_user']>0) {
			$check_max_uses_limit_per_user=$this->check_max_uses_limit_per_user($user_id, $promocode_data['no_of_attempts_user'], $promocode);
			if($check_max_uses_limit_per_user)
				return 'MAX_LIMIT_REACHED';
		}
		
		if ($promocode_data['company_ids']!='0' && $promocode_data['company_ids']!='' && $promocode_data['company_ids']!=NULL) {
			$verify_company=$this->verify_company($company_id, $promocode_data['company_ids']);
			if($verify_company)
				return 'INVALID_COMPANY_ID';
		}
		
		if ($promocode_data['branch_ids']!='0' && $promocode_data['branch_ids']!='' && $promocode_data['branch_ids']!=NULL) {
			$verify_branch=$this->verify_branch($branch_id, $promocode_data['branch_ids']);
			if($verify_branch)
				return 'INVALID_BRANCH_ID';
		}
			
		if($promocode_data['start_date']>0 || $promocode_data['end_date']>0) {
			$verify_expiry=$this->verify_expiry($promocode_data['start_date'], $promocode_data['end_date']);
			if($verify_expiry)
				return 'PROMOCODE_EXPIRED';
		}
		
		if($promocode_data['customer_ids']!='0' && $promocode_data['customer_ids']!='' && $promocode_data['customer_ids']!=NULL) {
			$verify_customer=$this->is_customer_valid_for_promocode($user_id, $promocode_data['customer_ids']);
			if($verify_customer)
				return 'PROMOCODE_NOT_FOR_USER';
		}
				
		if($promocode_data['category_ids']!=0 && $promocode_data['category_ids']!='' && $promocode_data['category_ids']!=NULL) {
			$verify_categories=$this->check_categories($category_ids, $promocode_data['category_ids']);
			if($verify_categories) {
				$responseArr['category_ids'] = $verify_categories;
			} else {
				return 'CATEGORY_NOT_MATCHED';
			}
		}

		if($promocode_data['item_ids']!='0' && $promocode_data['item_ids']!='' && $promocode_data['item_ids']!=NULL) {
			$verify_items=$this->check_items($item_ids, $promocode_data['item_ids']);
			if($verify_items) {
				$responseArr['item_ids'] = $verify_items;
			} else {
				return 'ITEM_NOT_MATCHED';
			}
		}

		$verify_promotion_applicable_for=$this->promotion_applicable_for($user_id, $promocode_data['promotion_applicable_for']);
		if($verify_promotion_applicable_for)
			return 'ONLY_FOR_NEW_USER';
			
		$min_order_amount=$this->min_order_amount($amount, $promocode_data['min_purchase']);
		if($min_order_amount)
			return 'MINIMUM_ORDER_AMOUNT_FAILED';
			
		if ($promocode_data['order_type']!='A') {
			$order_type=$this->check_order_type($order_type, $promocode_data['order_type']);
			if($order_type)
				return 'ORDER_TYPE_NOT_MATCHED';
		}
		
		if ($promocode_data['delivery_options']!='A') {
			$delivery_options=$this->check_delivery_options($type, $promocode_data['delivery_options']);
			if($delivery_options)
				return 'DELIVERY_OPTIONS_NOT_MATCHED';
		}
		$responseArr['id'] = $promocode_data['id'];
		$responseArr['title'] = $promocode_data['title'];
		$responseArr['description'] = $promocode_data['description'];
		$responseArr['promotion_type'] = $promocode_data['promotion_type'];
		$responseArr['loyalty_points'] = $promocode_data['loyalty_points'];
		$responseArr['discount_type'] = $promocode_data['discount_type'];
		$responseArr['discount'] = $promocode_data['discount'];
		$responseArr['delivery_options'] = $promocode_data['delivery_options'];
		$responseArr['order_type'] = $promocode_data['order_type'];
		$responseArr['max_discount'] = $promocode_data['max_discount'];
		return $responseArr;
    }
    
    public function getFaqs() {
		$faqQuery = "SELECT id, question, answer FROM tbl_faqs where (for_user='C' OR for_user='ALL') AND platform='BOTH' OR platform='APP' AND status='A' order by priority asc";
		$faq_data = $this->conn->prepare($faqQuery);
		$faq_data->execute();
		if($faq_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $faq_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['question'] = $array['question'];
				$output[$counter]['answer'] = $array['answer'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function getMyRatings($user_id) {
		$ratingQuery = "SELECT tirl.id, tirl.rating, tirl.comment, toi.name, toi.image FROM tbl_item_rating_log tirl JOIN tbl_order_items toi ON toi.id=tirl.item_id where tirl.customer_id=?";
		$rating_data = $this->conn->prepare($ratingQuery);
		$rating_data->bindParam(1, $user_id);
		$rating_data->execute();
		if($rating_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $rating_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['name'] = $array['name'];
				$output[$counter]['image'] = MENU_PIC_URL.$array['image'];
				$output[$counter]['rating'] = $array['rating'];
				$output[$counter]['comment'] = $array['comment'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function getNotifications($user_id) {
		$time=time();
		$notificationQuery = "SELECT id, notification_text, notification_image FROM tbl_notifications where target_user_type='C' AND start_date<=? AND end_date>=? AND status='A' AND (selected_users='' OR selected_users IS NULL OR FIND_IN_SET(?, selected_users)) AND (target_customer_type='ALL' OR target_customer_type=(select if(is_new_user='N', 'O', if(is_new_user='Y' ,'N', 'O')) from tbl_customers where id=?))";
		$notification_data = $this->conn->prepare($notificationQuery);
		$notification_data->bindParam(1, $time);
		$notification_data->bindParam(2, $time);
		$notification_data->bindParam(3, $user_id);
		$notification_data->bindParam(4, $user_id);
		$notification_data->execute();
		//print_r($notification_data->errorInfo());
		if($notification_data->rowCount()>0) {
			$output = array();
			$counter = 0;
			while ($array = $notification_data->fetch(PDO::FETCH_ASSOC)) {
				$output[$counter]['id'] = $array['id'];
				$output[$counter]['notification_text'] = $array['notification_text'];
				$output[$counter]['image_thumb'] = $array['notification_image']='' ? '' : NOTIFICATION_PIC_URL.'thumb/'.$array['notification_image'];
				$output[$counter]['image_large'] = $array['notification_image']='' ? '' : NOTIFICATION_PIC_URL.'large/'.$array['notification_image'];
				$counter++;
			}
			return $output;
		} else {
			return 'NO_RECORD_FOUND';
		}
	}
	
	public function contactus($name, $email, $mobile_code, $mobile, $message, $loggedin_userid, $ip_address) {
		$created=time();
		$contactQuery = "INSERT INTO tbl_contact_us SET name=?, email=?, mobile_code=?, mobile=?, message=?, loggedin_userid=?, ip_address=?, created=?";
		$prepare_contact = $this->conn->prepare($contactQuery);
		$prepare_contact->bindParam(1, $name);
		$prepare_contact->bindParam(2, $email);
		$prepare_contact->bindParam(3, $mobile_code);
		$prepare_contact->bindParam(4, $mobile);
		$prepare_contact->bindParam(5, $message);
		$prepare_contact->bindParam(6, $loggedin_userid);
		$prepare_contact->bindParam(7, $ip_address);
		$prepare_contact->bindParam(8, $created);
		$prepare_contact->execute();
		//print_r($prepare_contact->errorInfo());
		if($prepare_contact->rowCount()>0) {
			return 'SUCCESS';
		} else {
			return 'UNABLE_TO_PROCEED';
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
		$amt->setCurrency(PAYMENT_GATEWAY_CURRENCY_CODE) 
			->setTotal($totamount);
		
		$refund = new \PayPal\Api\Refund; 
		$refund->setAmount($amt);
		
		$sale = new \PayPal\Api\Sale;
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
			//print_r($ex->getData());
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
        $description="MAKE REFUND -  $order_id - $totamount - $saleId - $order_id";
        
        $this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
			
        try {
			$result = \Stripe\Refund::create(array(
			  "charge" => $saleId,
			  "amount" => $totamount*100,
			));
			//print_r($refundedSale);
			//$result->id;
			
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
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\ApiConnection $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\InvalidRequest $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\Api $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		} catch (\Stripe\Error\Card $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_REFUND_FAILED', 'PAYMENT_REFUND_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED_TO_REFUND';
		}
	}
	
    public function makePaymentPaypal($order_id, $device_id, $device_type, $ip_address) {
		$selectQuery = "SELECT tc.stripe_customer_id, too.customer_id, too.amount, tocc.* FROM tbl_orders too JOIN tbl_order_credit_cards tocc ON tocc.order_id=too.id JOIN tbl_customers tc ON tc.id=too.customer_id where too.id=?";
        $amount_select = $this->conn->prepare($selectQuery);
		$amount_select->bindParam(1, $order_id);
		$amount_select->execute();
		//print_r($amount_select->errorInfo());
       
        if($amount_select->rowCount()==0) {
			return 'INVALID_CARD';
		}
		$cardData = $amount_select->fetch(PDO::FETCH_ASSOC);
        //print_r($cardData);
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
		$amount->setCurrency(PAYMENT_GATEWAY_CURRENCY_CODE)
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
            
            $transactions = $payment->getTransactions();
			$relatedResources = $transactions[0]->getRelatedResources();
			$sale = $relatedResources[0]->getSale();
			$saleId = $sale->getId();
            
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_SUCCESS', 'PAYMENT_SUCCESS_STATUS', $device_id, $device_type, $ip_address, $description);
 
			$created=time();
            $insertPayment="insert into tbl_order_payments set credit_card_id =?, order_id=?, transaction_id=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, created=?, sale_id=?";
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
			$insert_payment->bindParam(11, $saleId);
			$insert_payment->execute();
			//print_r($insert_payment->errorInfo());
            return "DONE";
        }  catch (\PayPal\Exception\PayPalConnectionException $ex) {
			//print_r($ex->getData());
			$errors=$ex->getData();
			$output['errors']=$ex->getData();
			
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		}
	}
    
    public function makePaymentStripe($order_id, $device_id, $device_type, $ip_address) {
		$selectQuery = "SELECT tc.stripe_customer_id, too.customer_id, too.amount, tocc.* FROM tbl_orders too JOIN tbl_order_credit_cards tocc ON tocc.order_id=too.id JOIN tbl_customers tc ON tc.id=too.customer_id where too.id=?";
        $amount_select = $this->conn->prepare($selectQuery);
		$amount_select->bindParam(1, $order_id);
		$amount_select->execute();
		//print_r($amount_select->errorInfo());
       
        if($amount_select->rowCount()==0) {
			return 'INVALID_CARD';
		}
		$cardData = $amount_select->fetch(PDO::FETCH_ASSOC);
        //print_r($cardData);
        $customer_id=$cardData['customer_id'];
        $stripe_customer_id=$cardData['stripe_customer_id'];
        $totamount=$cardData['amount'];
        $cardnumber=$cardData['card_number'];
        $cardtoken=$cardData['card_token'];
        $description="MAKE ORDER - $customer_id - $totamount - $cardnumber - $order_id";
        
        $this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
		
        try {
			$result = \Stripe\Charge::create(array(
			  "amount" => $totamount*100,
			  "currency" => PAYMENT_GATEWAY_CURRENCY_CODE,
			  "card" => $cardtoken,
			  "customer"=> $stripe_customer_id,
			  "description" => $description
			));
			
            $time=time();
            $output['errors']='';
            $output['amount']=$totamount;
            $transactionId=$result->id;
            $userId=$result->id;
            
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_SUCCESS', 'PAYMENT_SUCCESS_STATUS', $device_id, $device_type, $ip_address, $description);
 
			$created=time();
            $insertPayment="insert into tbl_order_payments set credit_card_id =?, order_id=?, transaction_id=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, created=?, sale_id=?";
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
			$insert_payment->bindParam(11, $transactionId);
			$insert_payment->execute();
			//print_r($insert_payment->errorInfo());
            return "DONE";
        } catch (Stripe\Error\Base $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		}
		catch (\Stripe\Error\ApiConnection $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		} catch (\Stripe\Error\InvalidRequest $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		} catch (\Stripe\Error\Api $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		} catch (\Stripe\Error\Card $e) {
			$output['errors']=$e->getMessage();
			$errors=$e->getMessage();
			$this->saveTransactionData($order_id, $customer_id, 'PAYMENT_FAILED', 'PAYMENT_FAILED_STATUS', $device_id, $device_type, $ip_address, $errors);
            return 'FAILED';
		}
	}
	
    public function deleteCardPaypal($user_id, $cardid) {
		$this->includePaypalLib();
        $apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID,     // ClientID
				PAYPAL_SECRET_ID      // ClientSecret
			)
		);
		$creditCard = new \PayPal\Api\CreditCard();
		//use PayPal\Api\CreditCard;
		$error_name='';
		
		$getCardData=$this->getCardData($cardid);
		if(!$getCardData) { 
			return 'INAVLID_CARD';
		}
		if($getCardData['customer_id']!=$user_id) { 
			return 'NOT_AUTHORIZED';
		}
		$i=0;
		try {
			$creditCard = $creditCard->get($getCardData['card_token'], $apiContext);
			$creditCard->delete($apiContext);
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$daya=$ex->getData();
			$data=json_decode($daya);
			$error_name=$data->name;
			$output['errors']=$ex->getData();
		    if($error_name!='INVALID_RESOURCE_ID') {
				return $output;
			}
		}
		/* SAVED for future reference
		if($error_name!='INVALID_RESOURCE_ID') {
			$cardQuery = "SELECT count(b.id) as runningbooking, bf.total_fare, bf.transactionid from bookings b LEFT JOIN booking_fares bf ON bf.bookingid=b.id where credit_card_id='$cardid' and status>0 and status<5";
			
			
				$cardResult = mysqli_query($this->conn, $cardQuery);
				$booking = mysqli_fetch_assoc($cardResult);
		   
			if ($booking['total_fare']>0 && (is_null($booking['transactionid']) || $booking['transactionid']=='')) {
				return 'PAYMENT_PENDING';
			}
			
			if ($booking['runningbooking']>0) {
				return 'PAYMENT_PENDING';
			} 
		}*/
	 
		$deleteQuery = "delete from tbl_credit_cards where id=? and isdefault='NO'";
		$delete_card = $this->conn->prepare($deleteQuery);
		$delete_card->bindParam(1, $cardid);
		$delete_card->execute();
		// print_r($delete_card->errorInfo());
		if($delete_card->rowCount()>0) {
			$output['errors']='';
			$output['id']=$cardid;
			return $output;
		} else {
			return 'DEFAULT_CARD';
		}
	}
	
	public function deleteCardStripe($user_id, $cardid) {
		$this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $i=0;
		//use PayPal\Api\CreditCard;
		$error_name='';
		$getCardData=$this->getCardData($cardid);
		if(!$getCardData) { 
			return 'INAVLID_CARD';
		}
		if($getCardData['customer_id']!=$user_id) { 
			return 'NOT_AUTHORIZED';
		}
		try {
            $customer = \Stripe\Customer::retrieve($getCardData['stripe_customer_id']);
			$customer->sources->retrieve($getCardData['card_token'])->delete();
		   // return $customer;
		} catch (Stripe\Error\Base $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\ApiConnection $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\InvalidRequest $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\Api $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\Card $e) {
			$output['errors']=$e->getMessage();
			return $output;
		}
		$deleteQuery = "delete from tbl_credit_cards where id=? and isdefault='NO'";
		$delete_card = $this->conn->prepare($deleteQuery);
		$delete_card->bindParam(1, $cardid);
		$delete_card->execute();
	    //print_r($delete_card->errorInfo());
		if($delete_card->rowCount()>0) {
			$output['errors']='';
			$output['id']=$cardid;
			return $output;
		} else {
			return 'DEFAULT_CARD';
		}
	}
	
    public function editCardPaypal($user_id, $first_name, $expiry_month, $expiry_year, $cardid, $hitPaypal, $isdefault, $card_number, $card_type) {
		$this->includePaypalLib();
        $apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID,     // ClientID
				PAYPAL_SECRET_ID      // ClientSecret
			)
		);
		$time=time();
		$i=0;
		if($hitPaypal==0) {
			/*$cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==0) {
	        	$isdefault='YES';
	        }*/

			$updateQuery = "update tbl_credit_cards set modified=?, isdefault=? where id=?";
			if ($isdefault=='NO')
				$updateQuery .= " and isdefault!='YES'";
			$update_card = $this->conn->prepare($updateQuery);
			$update_card->bindParam(1, $time);
			$update_card->bindParam(2, $isdefault);
			$update_card->bindParam(3, $cardid);
			$update_card->execute();
			// print_r($update_card->errorInfo());
			if($update_card->rowCount()>0) {
				if($isdefault=='YES') {
					$otherQuery = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and customer_id=?";
					$other_card = $this->conn->prepare($otherQuery);
					$other_card->bindParam(1, $time);
					$other_card->bindParam(2, $cardid);
					$other_card->bindParam(3, $user_id);
					$other_card->execute();
					//print_r($other_card->errorInfo());
				}
					
				$output['errors']='';
				$output['card_number']=$card_number;
				$output['firstname']=$first_name;
				$output['expiry_month']=$expiry_month;
				$output['expiry_year']=$expiry_year;
				$output['modified'] = $time ;
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				$output['card_type'] = $card_type;
				return $output;
			} else {
				return 'DEFAULT_CARD';
			}
		}
				
		$getCardData=$this->getCardData($cardid);
		if(!$getCardData) { 
			return 'INAVLID_CARD';
		}
		
		if($getCardData['customer_id']!=$user_id) { 
			return 'NOT_AUTHORIZED';
		}
		
		$creditCard = new \PayPal\Api\CreditCard();
		$patchCard1 = new \PayPal\Api\Patch();
		$patchCard1->setOp('replace')
			->setPath('/first_name')
			->setValue($first_name);
			
		/*$patchCard2 = new \PayPal\Api\Patch;
		$patchCard2->setOp('replace')
			->setPath('/last_name')
			->setValue($LastName);*/
			
		$patchCard3 = new \PayPal\Api\Patch();
		$patchCard3->setOp('replace')
			->setPath('/expire_month')
			->setValue($expiry_month);
			
		$patchCard4 = new \PayPal\Api\Patch();	
		$patchCard4->setOp('replace')
			->setPath('/expire_year')
			->setValue($expiry_year);

		$pathRequest = new \PayPal\Api\PatchRequest();
		$pathRequest->addPatch($patchCard1)
			//->addPatch($patchCard2)
			->addPatch($patchCard3)
			->addPatch($patchCard4);

		try {
			$creditCard = $creditCard->get($getCardData['card_token'], $apiContext);
			$creditCard->update($pathRequest, $apiContext);
			//print_r($creditCard);
            $output['errors']='';
            $output['card_number']=$card_number;
            $output['firstname']=$creditCard->first_name;
            $output['expiry_month']=$creditCard->expire_month;
            $output['expiry_year']=$creditCard->expire_year;
            $output['modified'] = $time ;
            $output['card_type'] = $card_type;
            
            $cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==1) {
	        	$isdefault='YES';
	        }
			
		    $name=$first_name;
		    $update_Query2 = "update tbl_credit_cards SET name=?, expiry_month=?, expiry_year=?, modified=?, isdefault=? where id=?";
            if ($isdefault=='NO')
				$update_Query2 .= " and isdefault!='YES'";
			$update_card2 = $this->conn->prepare($update_Query2);
			$update_card2->bindParam(1, $name);
			$update_card2->bindParam(2, $expiry_month);
			$update_card2->bindParam(3, $expiry_year);
			$update_card2->bindParam(4, $time);
			$update_card2->bindParam(5, $isdefault);
			$update_card2->bindParam(6, $cardid);
			$update_card2->execute();
			//print_r($update_card2->errorInfo());
			if($update_card2->rowCount()>0) {
				$otherQuery2 = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and user_id=?";
				$other_card2 = $this->conn->prepare($otherQuery2);
				$other_card2->bindParam(1, $time);
				$other_card2->bindParam(2, $cardid);
				$other_card2->bindParam(3, $user_id);
				$other_card2->execute();
				//print_r($other_card2->errorInfo());
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				return $output;
			} else {
				return 'DEFAULT_CARD';
			}
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$output['errors']=$ex->getData();
            return $output;
		}  
	}
	
	public function editCardStripe($user_id, $first_name, $expiry_month, $expiry_year, $cardid, $hitPaypal, $isdefault, $card_number, $card_type) {
		$this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
		$time=time();
		$i=0;
		if($hitPaypal==0) {
			/*$cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==1) {
	        	$isdefault='YES';
	        }*/

			$updateQuery = "update tbl_credit_cards set modified=?, isdefault=? where id=?";
			if ($isdefault=='NO')
				$updateQuery .= " and isdefault!='YES'";
			$update_card = $this->conn->prepare($updateQuery);
			$update_card->bindParam(1, $time);
			$update_card->bindParam(2, $isdefault);
			$update_card->bindParam(3, $cardid);
			$update_card->execute();
			// print_r($update_card->errorInfo());
			if($update_card->rowCount()>0) {
				if($isdefault=='YES') {
					$otherQuery = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and customer_id=?";
					$other_card = $this->conn->prepare($otherQuery);
					$other_card->bindParam(1, $time);
					$other_card->bindParam(2, $cardid);
					$other_card->bindParam(3, $user_id);
					$other_card->execute();
					//print_r($other_card->errorInfo());
				}
					
				$output['errors']='';
				$output['card_number']=$card_number;
				$output['firstname']=$first_name;
				$output['expiry_month']=$expiry_month;
				$output['expiry_year']=$expiry_year;
				$output['modified'] = $time ;
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				$output['card_type'] = $card_type;
				return $output;
			} else {
				return 'DEFAULT_CARD';
			}
		}
				
		$getCardData=$this->getCardData($cardid);
		if(!$getCardData) { 
			return 'INAVLID_CARD';
		}
		
		if($getCardData['customer_id']!=$user_id) { 
			return 'NOT_AUTHORIZED';
		}
		
		$customer = \Stripe\Customer::retrieve($getCardData['stripe_customer_id']);
		$card = $customer->sources->retrieve($getCardData['card_token']);
		$card->name = $first_name;
		$card->exp_month = $expiry_month;
		$card->exp_year = $expiry_year;

		try {
			$creditCard = $card->save();
			//print_r($creditCard);
            $output['errors']='';
            $output['card_number']=$card_number;
            $output['firstname']=$first_name;
            $output['expiry_month']=$expiry_month;
            $output['expiry_year']=$expiry_year;
            $output['modified'] = $time;
            $output['card_type'] = $card_type;
            
            $cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==1) {
	        	$isdefault='YES';
	        }
			
		    $name=$first_name;
		    $update_Query2 = "update tbl_credit_cards SET name=?, expiry_month=?, expiry_year=?, modified=?, isdefault=? where id=?";
            if ($isdefault=='NO')
				$update_Query2 .= " and isdefault!='YES'";
			$update_card2 = $this->conn->prepare($update_Query2);
			$update_card2->bindParam(1, $name);
			$update_card2->bindParam(2, $expiry_month);
			$update_card2->bindParam(3, $expiry_year);
			$update_card2->bindParam(4, $time);
			$update_card2->bindParam(5, $isdefault);
			$update_card2->bindParam(6, $cardid);
			$update_card2->execute();
			//print_r($update_card2->errorInfo());
			if($update_card2->rowCount()>0) {
				$otherQuery2 = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and user_id=?";
				$other_card2 = $this->conn->prepare($otherQuery2);
				$other_card2->bindParam(1, $time);
				$other_card2->bindParam(2, $cardid);
				$other_card2->bindParam(3, $user_id);
				$other_card2->execute();
				//print_r($other_card2->errorInfo());
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				return $output;
			} else {
				return 'DEFAULT_CARD';
			}
		} catch (Stripe\Error\Base $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\ApiConnection $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\InvalidRequest $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\Api $e) {
			$output['errors']=$e->getMessage();
			return $output;
		} catch (\Stripe\Error\Card $e) {
			$output['errors']=$e->getMessage();
			return $output;
		}
	}
    
    public function addCardPaypal($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault) {
		$time=time();
        $this->includePaypalLib();
        $apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				PAYPAL_CLIENT_ID,     // ClientID
				PAYPAL_SECRET_ID      // ClientSecret
			)
		);
		$i=0;
		$creditCard = new \PayPal\Api\CreditCard();
		$creditCard->setType($card_type)
			->setNumber($card_number)
			->setExpireMonth($expiry_month)
			->setExpireYear($expiry_year)
			->setCvv2($cvn)
			->setFirstName($firstname);
			//->setLastName($lastname);
    
		$creditCard->setMerchantId($user_id);
		try {
			$creditCard->create($apiContext);
			//print_r($creditCard);
			$time=time();
            $output['errors']='';
            $output['card_number']=$creditCard->number;
            $output['firstname']=$creditCard->first_name;
            $output['expiry_month']=$creditCard->expire_month;
            $output['expiry_year']=$creditCard->expire_year;
            $output['card_type'] = $card_type ;
            $output['created'] = $time;
            
            $cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==0) {
	        	$isdefault='YES';
	        }
			$card_id=$creditCard->id;
			$card_number=$creditCard->number;
            $name=$creditCard->first_name;
            $expiry_month=$creditCard->expire_month;
            $expiry_year=$creditCard->expire_year;
	        
            $ccQuery = "INSERT INTO tbl_credit_cards SET customer_id=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, isdefault=?, created=?";
			$prepare_card = $this->conn->prepare($ccQuery);
			$prepare_card->bindParam(1, $user_id);
			$prepare_card->bindParam(2, $card_id);
			$prepare_card->bindParam(3, $card_number);
			$prepare_card->bindParam(4, $name);
			$prepare_card->bindParam(5, $expiry_month);
			$prepare_card->bindParam(6, $expiry_year);
			$prepare_card->bindParam(7, $card_type);
			$prepare_card->bindParam(8, $isdefault);
			$prepare_card->bindParam(9, $time);
			$prepare_card->execute();
		    //print_r($prepare_card->errorInfo());
			if($prepare_card->rowCount()>0) {
				$cardid = $this->conn->lastInsertId();
                if($isdefault=='YES') {
					$otherQuery = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and customer_id=? and isdefault='YES'";
					$update_card = $this->conn->prepare($otherQuery);
					$update_card->bindParam(1, $time);
					$update_card->bindParam(2, $cardid);
					$update_card->bindParam(3, $user_id);
					//print_r($update_card->errorInfo());
					$update_card->execute();
				}
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				$output['errors']='';
				return $output;
			} else {
				return 'UNABLE_TO_PROCEED';
			}
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$output['errors']=$ex->getData();
            return $output;
		}
	}
	
	public function createCustomer($customer_array) {
		$this->includeStripeLib();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $result =  \Stripe\Customer::create($customer_array);
        $response = array('success'=> 1, 'customer_id'=>@$result->id);
        return $response;
	}
	
	public function addCardStripe($user_id, $firstname, $card_number, $expiry_month, $expiry_year, $cvn, $card_type, $isdefault) {
		$time=time();
		$this->includeStripeLib();
		$userData=$this->getCustomerInfo($user_id);
		if ($userData['stripe_customer_id']!='' || $userData['stripe_customer_id']!=NULL) {
			 $customer_id = $userData['stripe_customer_id'];
		} else {
			$customer_array = array(
				'description' => "card added by $firstname",
				'email' => $userData['email']
			 );
			 $server_output = $this->createCustomer($customer_array);
			 $response = $server_output;
			 $customer_id = $response['customer_id'];
			 
			 $stripe_customer_query = "UPDATE tbl_customers SET stripe_customer_id=? where id=?";
			 $stripe_customer_id_update = $this->conn->prepare($stripe_customer_query);
			 $stripe_customer_id_update->bindParam(1, $customer_id);
			 $stripe_customer_id_update->bindParam(2, $user_id);
			 $stripe_customer_id_update->execute();
		}
		 
		 \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
         $arr = array(
			'name' => $firstname,
			'cvc' => $cvn,
			'exp_month' => $expiry_month,
			'exp_year' => $expiry_year,
			'number' => $card_number
		);
		  $i=0;
		try {
			$result = \Stripe\Token::create( ["card" => $arr]);
            $token = $result->id;

            // save card to stripe
            $customer = \Stripe\Customer::retrieve($customer_id);
            $customer->sources->create(array("source" => $token));
            //print_r($customer);
          
			$time=time();
            $output['errors']='';
            $output['Number']="************".$result->card->last4;
            $output['Name']=$firstname;
            $output['ExpiryMonth']=$expiry_month;
            $output['ExpiryYear']=$expiry_year;
            $output['card_type'] = $card_type ;
            $output['created'] = $time;
            
            $cardCount=$this->isDefaultCard($user_id);
	        if($cardCount==0) {
	        	$isdefault='YES';
	        }
	        
	        $card_id=$result->card->id;
			$card_number="************".$result->card->last4;
            $name=$firstname;
            $expiry_month=$expiry_month;
            $expiry_year=$expiry_year;
	        
            $ccQuery = "INSERT INTO tbl_credit_cards SET customer_id=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, isdefault=?, created=?";
			$prepare_card = $this->conn->prepare($ccQuery);
			$prepare_card->bindParam(1, $user_id);
			$prepare_card->bindParam(2, $card_id);
			$prepare_card->bindParam(3, $card_number);
			$prepare_card->bindParam(4, $name);
			$prepare_card->bindParam(5, $expiry_month);
			$prepare_card->bindParam(6, $expiry_year);
			$prepare_card->bindParam(7, $card_type);
			$prepare_card->bindParam(8, $isdefault);
			$prepare_card->bindParam(9, $time);
			$prepare_card->execute();
		    //print_r($prepare_card->errorInfo());
			if($prepare_card->rowCount()>0) {
				$cardid = $this->conn->lastInsertId();
                if($isdefault=='YES') {
					$otherQuery = "update tbl_credit_cards set modified=?, isdefault='NO' where id!=? and customer_id=? and isdefault='YES'";
					$update_card = $this->conn->prepare($otherQuery);
					$update_card->bindParam(1, $time);
					$update_card->bindParam(2, $cardid);
					$update_card->bindParam(3, $user_id);
					//print_r($update_card->errorInfo());
					$update_card->execute();
				}
				$output['id']=$cardid;
				$output['isdefault'] = $isdefault;
				$output['errors']='';
				return $output;
			} else {
				return 'UNABLE_TO_PROCEED';
			}		
		} catch (Stripe\Error\Base $e) {
            // Code to do something with the $e exception object when an error occurs
            $output['errors']=$e->getMessage();
            return $output;
        }
        catch (\Stripe\Error\ApiConnection $e) {
            // Network problem, perhaps try again.
            $output['errors']=$e->getMessage();
            return $output;
        } catch (\Stripe\Error\InvalidRequest $e) {
            // You screwed up in your programming. Shouldn't happen!
            $output['errors']=$e->getMessage();
            return $output;
        } catch (\Stripe\Error\Api $e) {
            // Stripe's servers are down!
            $output['errors']=$e->getMessage();
            return $output;
        } catch (\Stripe\Error\Card $e) {
            // Card was declined.
            $output['errors']=$e->getMessage();
            return $output;
        }
	}
    
    public function isReferralAllowed() {
		$referralQuery = "SELECT referraler_amount, applier_amount, description FROM tbl_referrals where id=1 AND status='A'";
		$referral_data = $this->conn->prepare($referralQuery);
	    $referral_data->execute();
	    if($referral_data->rowCount()==0) 
			return 'DEACTiVATED';
	}
    
    public function getReferralerData() {
		$referralQuery = "SELECT referraler_amount, applier_amount, description FROM tbl_referrals where id=1 AND status='A'";
		$referral_data = $this->conn->prepare($referralQuery);
	    $referral_data->execute();
	    if($referral_data->rowCount()>0) {
			$referralData = $referral_data->fetch(PDO::FETCH_ASSOC);
			$responseArr['referraler_amount'] = $referralData['referraler_amount'];
			$responseArr['applier_amount'] = $referralData['applier_amount'];
			$responseArr['description'] = $referralData['description'];
		} else {
			$responseArr=NULL;
		}
		return $responseArr;
	}
    
    public function updateOrderStatus($order_id) {
		$updateQuery = "UPDATE tbl_orders SET status='PP' where id=?";
		$update_status = $this->conn->prepare($updateQuery);
		$update_status->bindParam(1, $order_id);
		$update_status->execute();
	}
    
    public function refundPayment($order_id, $device_id, $device_type, $ip_address, $admin_id) {
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->refundPaymentStripe($order_id, $device_id, $device_type, $ip_address, $admin_id);
		} else {
			$result=$this->refundPaymentPaypal($order_id, $device_id, $device_type, $ip_address, $admin_id);
		}
		return $result;     
    }
    
    public function makePayment($order_id, $device_id, $device_type, $ip_address) {
		if (ACTIVATED_PAYMENT_GATEWAY=='STRIPE') {
			$result=$this->makePaymentStripe($order_id, $device_id, $device_type, $ip_address);
		} else {
			$result=$this->makePaymentPaypal($order_id, $device_id, $device_type, $ip_address);
		}
		return $result;
    }
     
    public function getOrderMobileData($orderid) {
		$mobileQuery = "SELECT mobile_code, mobile, firstname, email FROM tbl_order_customer_addresses WHERE order_id=?";
		$mobile_data = $this->conn->prepare($mobileQuery);
        $mobile_data->bindParam(1, $orderid);
	    $mobile_data->execute();
	    //print_r($mobile_data->errorInfo());
		if($mobile_data->rowCount()>0) {
			$data = $mobile_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
	}
    
    public function validate_available_wallet_points($user_id, $loyalty_points) {
		$customerQuery = "SELECT wallet_points FROM tbl_customers WHERE id=?";
		$customer_data = $this->conn->prepare($customerQuery);
        $customer_data->bindParam(1, $user_id);
	    $customer_data->execute();
		$customerData = $customer_data->fetch(PDO::FETCH_ASSOC);
		if ($customerData['wallet_points']<$loyalty_points)
			return 'WALLET_POINTS_LOW_THEN_APPPLIED';
	}
    
    public function validate_promocode_make_order($user_id, $promocode) {
		$promocode_data=$this->is_valid_promocode($promocode);
		if (!$promocode_data) {
			return 'PRMOCODE_NOT_EXIST';
		}
		if ($promocode_data['total_promocode_attempts']>0) {
			$check_max_uses_limit=$this->check_max_uses_limit($promocode_data['total_promocode_attempts'], $promocode_data['tot_user_applied']);
			if($check_max_uses_limit)
				return 'PRMOCODE_USES_LIMIT_EXPIRED';
		}
		
		if ($promocode_data['no_of_attempts_user']>0) {
			$check_max_uses_limit_per_user=$this->check_max_uses_limit_per_user($user_id, $promocode_data['no_of_attempts_user'], $promocode);
			if($check_max_uses_limit_per_user)
				return 'MAX_LIMIT_REACHED';
		}
		
		if($promocode_data['customer_ids']!='0' && $promocode_data['customer_ids']!='' && $promocode_data['customer_ids']!=NULL) {
			$verify_customer=$this->is_customer_valid_for_promocode($user_id, $promocode_data['customer_ids']);
			if($verify_customer)
				return 'PROMOCODE_NOT_FOR_USER';
		}

		$verify_promotion_applicable_for=$this->promotion_applicable_for($user_id, $promocode_data['promotion_applicable_for']);
		if($verify_promotion_applicable_for)
			return 'ONLY_FOR_NEW_USER';			
			
		return 'SUCCESSFULLY_APPLIED';
    }
    
    public function check_delivery_options($type, $delivery_options) {
		if ($type!=$delivery_options) { 
			return true;
		}
	}
    
    public function check_order_type($order_type, $promo_order_type) {
		if ($order_type!=$promo_order_type) { 
			return true;
		}
	}
    
    public function min_order_amount($order_amount, $min_purchase) {
		if ($order_amount<$min_purchase) { 
			return true;
		}
	}
    
    public function promotion_applicable_for($user_id, $promotion_applicable_for) {
		$customerQuery = "SELECT is_new_user FROM tbl_customers WHERE id=?";
		if($promotion_applicable_for!='A')
			$customerQuery .= " AND is_new_user='Y'";
		$customer_data = $this->conn->prepare($customerQuery);
        $customer_data->bindParam(1, $user_id);
	    $customer_data->execute();
	    //print_r($promotion_data->errorInfo());
		if($customer_data->rowCount()==0) {
			return true;
		}
	}
	
	public function check_items($item_ids, $promo_item_ids) {
		$item_ids_arr=explode(',',$item_ids);
		$promo_item_ids_arr=explode(',',$promo_item_ids);
		$result=array_intersect($promo_item_ids_arr, $item_ids_arr);
		$resultArr = array_values($result);
		if (count($resultArr)>0) {
			$str = implode (",", $resultArr);
			return $str;
		}
	}
	
	public function check_categories($category_ids, $promo_category_ids) {
		$category_ids_arr=explode(',',$category_ids);
		$promo_category_ids_arr=explode(',',$promo_category_ids);
		$result=array_intersect($promo_category_ids_arr, $category_ids_arr);
		$resultArr = array_values($result);
		if (count($resultArr)>0) {
			$str = implode (",", $resultArr);
			return $str;
		}
	}
    
    public function check_max_uses_limit_per_user($user_id, $no_of_attempts_user, $promocode) {
		$promotionQuery = "SELECT no_of_used FROM tbl_customer_promocodes WHERE promocode=? AND customer_id=? AND no_of_used>=?";
		$promotion_data = $this->conn->prepare($promotionQuery);
        $promotion_data->bindParam(1, $promocode);
        $promotion_data->bindParam(2, $user_id);
        $promotion_data->bindParam(3, $no_of_attempts_user);
	    $promotion_data->execute();
	    //print_r($promotion_data->errorInfo());
		if($promotion_data->rowCount()>0) {
			return true;
		}
	}
	
    public function check_max_uses_limit($total_promocode_attempts, $tot_user_applied) {
		if ($total_promocode_attempts<=$tot_user_applied) { 
			return true;
		}
	}
    
    public function is_customer_valid_for_promocode($user_id, $customer_ids) {
		if (!preg_match('/\b' . $user_id . '\b/', $customer_ids)) { 
			return true;
		}
	}
    
    public function verify_expiry($promo_start_date, $promo_end_date) {
		$current_time=time();
		if ($current_time<=$promo_start_date || $current_time>=$promo_end_date) {
			return TRUE;
		}
	}
	
    public function verify_branch($branch_id, $promo_branch_id) {
		if (!preg_match('/\b' . $branch_id . '\b/', $promo_branch_id)) { 
			return true;
		}
	}
	
	public function verify_company($company_id, $promo_company_id) {
		if (!preg_match('/\b' . $company_id . '\b/', $promo_company_id)) { 
			return true;
		}
	}
    
    public function is_valid_promocode($promocode) {
		$promotionQuery = "SELECT * FROM tbl_promotions WHERE BINARY promotion_code=? AND status='A'";
		$promotion_data = $this->conn->prepare($promotionQuery);
        $promotion_data->bindParam(1, $promocode);
	    $promotion_data->execute();
		if($promotion_data->rowCount()>0) {
			$data = $promotion_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
	}
    
    public function applyReferralCode($applier_id, $referraler_id, $referral_code) {
		$referralQuery = "SELECT referraler_amount, applier_amount FROM tbl_referrals where id=1 AND status='A'";
		$referral_data = $this->conn->prepare($referralQuery);
	    $referral_data->execute();
	    
		$referralData = $referral_data->fetch(PDO::FETCH_ASSOC);
		$referral_amount_referraler=$referralData['referraler_amount'];;
		$referral_amount_applier=$referralData['applier_amount'];
		$created=time();
		$insertQuery = "INSERT INTO tbl_customer_referrals set used_referral_code=?, referraler_id=?, referraler_amount=?, applier_id=?, applier_amount=?, created=?";
		$referral_insert = $this->conn->prepare($insertQuery);
		$referral_insert->bindParam(1, $referral_code);
		$referral_insert->bindParam(2, $referraler_id);
		$referral_insert->bindParam(3, $referral_amount_referraler);
		$referral_insert->bindParam(4, $applier_id);
		$referral_insert->bindParam(5, $referral_amount_applier);
		$referral_insert->bindParam(6, $created);
		return $referral_amount_applier;
	}
	
	public function getReferralerId($referral_code) {
		$customerQuery = "SELECT id FROM tbl_customers WHERE referral_code=?";
		$customer_data = $this->conn->prepare($customerQuery);
        $customer_data->bindParam(1, $referral_code);
	    $customer_data->execute();
		if($customer_data->rowCount()>0) {
			$data = $customer_data->fetch(PDO::FETCH_ASSOC);
			return $data['id'];
		}
	}
	
    //$actiontype -> ORDER / NEW
    public function makeAddressDefault($user_id, $address_id, $actiontype) {
        $created=time();
        if ($actiontype=='ORDER') {
			$updateQuery = "UPDATE tbl_customer_addresses SET isdefault='Y' where id=?";
			$update_default = $this->conn->prepare($updateQuery);
			$update_default->bindParam(1, $address_id);
			$update_default->execute();
			//print_r($update_default->errorInfo());
			if($update_default->rowCount()>0) {
				$updateQuery2 = "UPDATE tbl_customer_addresses SET isdefault='N' where customer_id=? AND id!=? AND isdefault='Y'";
				$update_default2 = $this->conn->prepare($updateQuery2);
				$update_default2->bindParam(1, $user_id);
				$update_default2->bindParam(2, $address_id);
				$update_default2->execute();
				//print_r($update_default2->errorInfo());
				return 'SUCCESS';
			} else {
				return 'UNABLE_TO_PROCEED';
			}
		} else {
			$updateQuery = "UPDATE tbl_customer_addresses SET isdefault=(select * from (select IF(count(*)>0,'N','Y') as cnt from tbl_customer_addresses tca where tca.customer_id=? AND tca.isdefault='Y') as tt) where customer_id=? AND id=?";
			$update_default = $this->conn->prepare($updateQuery);
			$update_default->bindParam(1, $user_id);
			$update_default->bindParam(2, $user_id);
			$update_default->bindParam(3, $address_id);
			$update_default->execute();
			//print_r($update_default->errorInfo());
			if($update_default->rowCount()>0) {
				return 'SUCCESS';
			} else {
				return 'UNABLE_TO_PROCEED';
			}
		}	
    }
    
    public function getDeaultCard($user_id) {
		$result=array();
		$cardQuery = "SELECT * FROM tbl_credit_cards WHERE customer_id=? AND isdefault='YES'";
		$card_data = $this->conn->prepare($cardQuery);
        $card_data->bindParam(1, $user_id);
	    $card_data->execute();
		if($card_data->rowCount()>0) {
			$data = $card_data->fetch(PDO::FETCH_ASSOC);
			$result['id'] = $data['id'];
			$result['card_number'] = $data['card_number'];
			$result['firstname'] = $data['name'];
			$result['expiry_month'] = $data['expiry_month'];
			$result['expiry_year'] = $data['expiry_year'];
			$result['card_type'] = $data['card_type'];
			$result['isdefault'] = $data['isdefault'];
			return $result;
		}
	}
	
	public function getDefaultAddress($user_id) {
		$result=array();
		$cardQuery = "SELECT * FROM tbl_customer_addresses WHERE customer_id=? AND isdefault='Y'";
		$card_data = $this->conn->prepare($cardQuery);
        $card_data->bindParam(1, $user_id);
	    $card_data->execute();
		if($card_data->rowCount()>0) {
			$data = $card_data->fetch(PDO::FETCH_ASSOC);
			$result['id'] = $data['id'];
			$result['firstname'] = $data['firstname'];
			$result['lastname'] = $data['lastname'];
			$result['mobile_code'] = $data['mobile_code'];
			$result['mobile'] = $data['mobile'];
			$result['address'] = $data['address'];
			$result['latitude'] = $data['latitude'];
			$result['longitude'] = $data['longitude'];
			return $result;
		}
	}
    
    public function getTaxes($branch_id) {
		$result=array();
		$taxQuery = "SELECT * FROM tbl_taxes WHERE branch_id=? AND status='A'";
		$tax_data = $this->conn->prepare($taxQuery);
        $tax_data->bindParam(1, $branch_id);
	    $tax_data->execute();
		if($tax_data->rowCount()>0) {
			$i=0;
			while ($data = $tax_data->fetch(PDO::FETCH_ASSOC)) {
				$result[$i]['id'] = $data['id'];
				$result[$i]['title'] = $data['title'];
				$result[$i]['tax'] = $data['tax'];
				++$i;
			}
			return $result;
		}
    }
    public function getSettings($branch_id) {
		$result=array();
		$settingQuery = "SELECT * FROM tbl_branch_settings WHERE branch_id=?";
		$setting_data = $this->conn->prepare($settingQuery);
        $setting_data->bindParam(1, $branch_id);
	    $setting_data->execute();
	    // print_r($setting_data->errorInfo());
		if($setting_data->rowCount()>0) {
			$i=0;
			while ($data = $setting_data->fetch(PDO::FETCH_ASSOC)) {
				//$result[$i]['key'] = $data['key'];
				$result[$data['key']] = $data['value'];
				++$i;
			}
			return $result;
		}
    }
    
    public function getCatering($branch_id) {
		$categoryQuery = "select tc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' AND tbc.branch_id=$branch_id AND tc.is_catering='Y'";
		$category_data = $this->conn->prepare($categoryQuery);
	    $category_data->execute();
	    // print_r($category_data->errorInfo());
	    if($category_data->rowCount()>0) {
	        $data = $category_data->fetch(PDO::FETCH_ASSOC);
			$response['id'] = $data['id'];
			$response['name'] = $data['name'];
			/*$response['description'] = $data['description'];
			if($data['logo']!='') {
				$response['image_thumb'] = CATEGORY_PIC_URL.'thumb/'.$data['logo'];
				$response['image_large'] = CATEGORY_PIC_URL.'large/'.$data['logo'];
			}*/
			return $response;
		} 
    }
    
    public function maskCreditCard($cc){
	    $cc_length = strlen($cc);
	    for($i=0; $i<$cc_length-4; $i++){
	        if($cc[$i] == '-'){continue;}
	        $cc[$i] = 'X';
	    }
	    return $cc;
	}
    
    public function isCardAlreadyAdded($user_id, $cardnumber, $cardid=0) {
		$card_number= $this->maskCreditCard($cardnumber);
		$cardQuery = "select id from tbl_credit_cards where customer_id=? and card_number=?";
		if ($cardid>0)
			$cardQuery .= " AND id!=?";
		$select_card = $this->conn->prepare($cardQuery);
		$select_card->bindParam(1, $user_id);
		$select_card->bindParam(2, $card_number);
		if ($cardid>0)
			$select_card->bindParam(3, $cardid);
		$select_card->execute();
	    // print_r($select_card->errorInfo());
	    $data=$select_card->fetch(PDO::FETCH_ASSOC);
		if($select_card->rowCount()>0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
    
    public function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1)
            return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    public function generateReferralCode($length, $firstname) {
		$strLength=strlen($firstname);
		if ($strLength>=4) {
			$strLength=4;
		}
		$str1 = trim(substr($firstname, 0, $strLength));
		
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        //$codeAlphabet = "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }
        
        $referral_code=strtoupper(REFERRAL_CODE_INITIAL.$str1.$token);

        $isDuplicate = $this->isReferCodeDuplicate($referral_code);
        if ($isDuplicate) {
            $this->generateReferralCode(4, $firstname);
        }
        return $referral_code;
    }

    private function isReferCodeDuplicate($token) {
        $customerQuery = "SELECT id from tbl_customers WHERE referral_code=?";
        $customer_data = $this->conn->prepare($customerQuery);
		$customer_data->bindParam(1, $token);
	    $customer_data->execute();
	    // print_r($customer_data->errorInfo());
        if($customer_data->rowCount()>0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function SendPushToAdmin($order_id, $message, $noti_type) {
		$branchQuery = "SELECT GROUP_CONCAT(tu.device_token) as device_tokens FROM tbl_orders o JOIN tbl_users tu ON tu.info_id=o.branch_id AND tu.usertype='RESTAURANT' AND tu.app_login='Y' AND tu.device_token!='' where o.id=?";
		$branch_data = $this->conn->prepare($branchQuery);
		$branch_data->bindParam(1, $order_id);
	    $branch_data->execute();
	    //print_r($branch_data->errorInfo());
	    $data = $branch_data->fetch(PDO::FETCH_ASSOC);
	    $device_tokens=explode(',', $data['device_tokens']);
	    // print_r($device_tokens);
	    if ($device_tokens!=NULL)
			$this->sendFCMPushNotification($message, $device_tokens, $message, $noti_type, 'BRANCH', $order_id);
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
		//print_r($insert_transaction->errorInfo());
		if($insert_transaction->rowCount()>0) {
			return TRUE;
		} else {
			return 'UNABLE_TO_PROCEED';
		}
    }
    
    public function saveItems($order_id, $itemData) {
		$data['attributes']=$itemData->attributes;
		$data['extras']=$itemData->extras;
		$data=json_encode($data);
				
	    $item_total= ($itemData->prices[0]->price + $itemData->extra_price + $itemData->attribute_price) * $itemData->quantity;
	    $image=substr($itemData->images[0]->thumb, strrpos($itemData->images[0]->thumb, '/') + 1);
	    $created=time();
		$insertQuery = "INSERT INTO tbl_order_items SET order_id=?, item_id=?, avg_rating=?, is_nonveg=?, is_new=?, is_featured=?, name=?, image=?, price_name=?, unit_price=?, extra_price=?, attribute_price=?, quantity=?, total_price=?, data=?, created=?";
		$insert_data = $this->conn->prepare($insertQuery);
		$insert_data->bindParam(1, $order_id);
		$insert_data->bindParam(2, $itemData->id);
		$insert_data->bindParam(3, $itemData->avg_rating);
		$insert_data->bindParam(4, $itemData->is_nonveg);
		$insert_data->bindParam(5, $itemData->is_new);
		$insert_data->bindParam(6, $itemData->is_featured);
		$insert_data->bindParam(7, $itemData->name);
		$insert_data->bindParam(8, $image);
		$insert_data->bindParam(9, $itemData->prices[0]->name);
		$insert_data->bindParam(10, $itemData->prices[0]->price);
		$insert_data->bindParam(11, $itemData->extra_price);
		$insert_data->bindParam(12, $itemData->attribute_price);
		$insert_data->bindParam(13, $itemData->quantity);
		$insert_data->bindParam(14, $item_total);
		$insert_data->bindParam(15, $data);
		$insert_data->bindParam(16, $created);
		$insert_data->execute();
		//print_r($insert_data->errorInfo());
	  /*if($insert_data->execute())  {
			return 'SAVED';
		} else {
			return 'UNABLE_TO_PROCEED';
		}*/
	}
    
    public function saveTaxes($branch_id, $order_id, $amount) {
		$taxQuery = "select *, (($amount * tax) / 100) as tax_amount from tbl_taxes where branch_id=? AND status='A'";
		$tax_data = $this->conn->prepare($taxQuery);
        $tax_data->bindParam(1, $branch_id);
	    $tax_data->execute();
	    // print_r($tax_data->errorInfo());
		if($tax_data->rowCount()) {
			$tax=0;
			$created=time();
			while($card = $tax_data->fetch(PDO::FETCH_ASSOC)) {
				$insertQuery = "INSERT INTO tbl_order_taxes SET tax_id=?, order_id=?, title=?, tax=?, tax_amount=?, created=?";
				$tax_insert = $this->conn->prepare($insertQuery);
				$tax_insert->bindParam(1, $card['id']);
				$tax_insert->bindParam(2, $order_id);
				$tax_insert->bindParam(3, $card['title']);
				$tax_insert->bindParam(4, $card['tax']);
				$tax_insert->bindParam(5, $card['tax_amount']);
				$tax_insert->bindParam(6, $created);
				$tax_insert->execute();
				//print_r($tax_insert->errorInfo());
				$tax=+$card['tax'];
			}
			return $tax; 
		} else {
			return 'NO_TAXES_FOUND';
		}
	}
		
	public function savePromocode($order_id, $promocode) {
		$insertQuery = "INSERT INTO tbl_order_promotions (order_id, company_ids, branch_ids, category_ids, item_ids, title, description, no_of_attempts_user, total_promocode_attempts, tot_user_applied, promotion_applicable_for, start_date, end_date, order_type, delivery_options, promotion_type, loyalty_points, discount_type, discount, min_purchase, max_discount, quantity, customer_ids) SELECT ?, company_ids, branch_ids, category_ids, item_ids, title, description, no_of_attempts_user, total_promocode_attempts, tot_user_applied, promotion_applicable_for, start_date, end_date, order_type, delivery_options, promotion_type, loyalty_points, discount_type, discount, min_purchase, max_discount, quantity, customer_ids FROM tbl_promotions where BINARY promotion_code=?";
		$promotion_insert = $this->conn->prepare($insertQuery);
		$promotion_insert->bindParam(1, $order_id);
		$promotion_insert->bindParam(2, $promocode);
		$promotion_insert->execute();
	}
    
    public function saveCreditCard($user_id, $card_id, $order_id) {
		$cardQuery = "select * from tbl_credit_cards where customer_id=? AND id=?";
		$card_data = $this->conn->prepare($cardQuery);
        $card_data->bindParam(1, $user_id);
        $card_data->bindParam(2, $card_id);
	    $card_data->execute();
	    // print_r($card_data->errorInfo());
		if($card_data->rowCount()) {
			$card = $card_data->fetch(PDO::FETCH_ASSOC);
			$created=time();
			$insertQuery = "INSERT INTO tbl_order_credit_cards SET credit_card_id=?, order_id=?, isdefault=?, card_token=?, card_number=?, name=?, expiry_month=?, expiry_year=?, card_type=?, created=?";
			$card_insert = $this->conn->prepare($insertQuery);
			$card_insert->bindParam(1, $card_id);
			$card_insert->bindParam(2, $order_id);
			$card_insert->bindParam(3, $card['isdefault']);
			$card_insert->bindParam(4, $card['card_token']);
			$card_insert->bindParam(5, $card['card_number']);
			$card_insert->bindParam(6, $card['name']);
			$card_insert->bindParam(7, $card['expiry_month']);
			$card_insert->bindParam(8, $card['expiry_year']);
			$card_insert->bindParam(9, $card['card_type']);
			$card_insert->bindParam(10, $created);
			$card_insert->execute();
			return 'SAVED'; 
		} else {
			return 'INVALID_CARD_ID';
		}
	}
	
    public function saveOrderAddress($user_id, $address_id, $order_id) {
		if ($address_id>0) {
			$addressQuery = "select tca.*, tc.profile_pic from tbl_customer_addresses tca JOIN tbl_customers tc ON tc.id=tca.customer_id where tca.customer_id=? AND tca.id=?";
		} else {
			$addressQuery = "select * from tbl_customers where id=?";
		}
		$address_data = $this->conn->prepare($addressQuery);
        $address_data->bindParam(1, $user_id);
        if($address_id>0)
			$address_data->bindParam(2, $address_id);
	    $address_data->execute();

		if($address_data->rowCount()) {
			$address = $address_data->fetch(PDO::FETCH_ASSOC);
			//print_r($address);
			$created=time();
			$insertQuery = "INSERT INTO tbl_order_customer_addresses SET address_id=?, order_id=?, firstname=?, lastname=?, profile_pic=?, mobile_code=?, mobile=?, created=?, email=?";
			if ($address_id>0)
				$insertQuery .= ", address=?, latitude=?, longitude=?";
			$address_insert = $this->conn->prepare($insertQuery);
			$address_insert->bindParam(1, $address_id);
			$address_insert->bindParam(2, $order_id);
			$address_insert->bindParam(3, $address['firstname']);
			$address_insert->bindParam(4, $address['lastname']);
			$address_insert->bindParam(5, $address['profile_pic']);
			$address_insert->bindParam(6, $address['mobile_code']);
			$address_insert->bindParam(7, $address['mobile']);
			$address_insert->bindParam(8, $created);
			$address_insert->bindParam(9, $address['email']);
			if ($address_id>0) {
				$address_insert->bindParam(10, $address['address']);
				$address_insert->bindParam(11, $address['latitude']);
				$address_insert->bindParam(12, $address['longitude']);
			}
			
			$address_insert->execute();
			//print_r($address_insert->errorInfo());
			return 'SAVED'; 
		} else {
			return 'INVALID_ADDRESS_ID';
		}
	}
    
    public function invoiceIDGenerateAndSave($order_id, $user_id, $branch_id) {
		$this->includeGlobalSiteSetting();
		$invoice_id=INVOICE_INITIALS.$branch_id.'-'.$user_id.'-'.$order_id;
		$orderQuery = "UPDATE tbl_orders SET invoice_id=? WHERE id=?";
		$update_order = $this->conn->prepare($orderQuery);
		$update_order->bindParam(1, $invoice_id);
		$update_order->bindParam(2, $order_id);
		// print_r($update_order->errorInfo());
		$update_order->execute();
		return $invoice_id;
	}
    
	public function getCardData($cardid) {
		$cardQuery = "SELECT tc.stripe_customer_id, tcc.id, tcc.customer_id, tcc.card_token, tcc.card_number, tcc.name, tcc.expiry_month, tcc.expiry_year, tcc.card_type, tcc.isdefault FROM tbl_credit_cards tcc JOIN tbl_customers tc ON tc.id=tcc.customer_id WHERE tcc.id=?";
		$card_data = $this->conn->prepare($cardQuery);
        $card_data->bindParam(1, $cardid);
	    $card_data->execute();
	    // print_r($card_data->errorInfo());
		if($card_data->rowCount()>0) {
			return $card_data->fetch(PDO::FETCH_ASSOC);
		}
    }
	
	public function isDefaultCard($userid) {
		$cardQuery = "SELECT id FROM tbl_credit_cards WHERE customer_id =?";
		$card_data = $this->conn->prepare($cardQuery);
        $card_data->bindParam(1, $userid);
	    $card_data->execute();
	    // print_r($card_data->errorInfo());
		return $card_data->rowCount();
	}
    
    public function decimal_format($value = 0) {
		return number_format($value, 2, '.', '');
	}
    
    function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +  cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance_meters= $angle * $earthRadius;
        return $this->decimal_format($distance_meters);
    }
    
    public function getAttributeOptions($itemId, $isDefault) {
		$optionQuery = "SELECT tao.id, tao.name, tiao.price, tiao.default_selected FROM tbl_item_attribute_options tiao JOIN tbl_attribute_options tao ON tao.id=tiao.attribute_option_id WHERE tiao.item_attribute_id=? AND tiao.status='A' AND tao.status='A'";
		if($isDefault!='')
			$optionQuery .= " AND default_selected=?";
		$optionQuery .= "order by tiao.priority asc";
        $option_data = $this->conn->prepare($optionQuery);
        $option_data->bindParam(1, $itemId);
        if($isDefault!='')
			$option_data->bindParam(2, $isDefault);
	    $option_data->execute();
	    // print_r($option_data->errorInfo());
		if($option_data->rowCount()) {
			return $option_data;
		}
	}
	
    public function getItemExtras($itemId) {
		$extraQuery = "SELECT tie.id, te.name, te.image, te.description, tie.price FROM tbl_item_extras tie JOIN tbl_extras te ON te.id=tie.extra_id WHERE tie.item_id=? AND tie.status='A' AND te.status='A' order by tie.priority asc";
        $extra_data = $this->conn->prepare($extraQuery);
        $extra_data->bindParam(1, $itemId);
	    $extra_data->execute();
	    // print_r($extra_data->errorInfo());
		if($extra_data->rowCount()) {
			return $extra_data;
		}
	}
    
    public function getSearchAreaLatLong($lat, $lng, $distance = 50, $unit = 'km') {
        // radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'
        if ($unit == 'km')
            $radius = 6371.009; // in kilometers
        elseif ($unit == 'mi')
            $radius = 3958.761; // in miles

        // latitude boundaries
        $maxLat = (float) $lat + rad2deg($distance / $radius);
        $minLat = (float) $lat - rad2deg($distance / $radius);

        // longitude boundaries (longitude gets smaller when latitude increases)
        $maxLng = (float) $lng + rad2deg($distance / $radius / cos(deg2rad((float) $lat)));
        $minLng = (float) $lng - rad2deg($distance / $radius / cos(deg2rad((float) $lat)));

    	$res=array('minLat'=> $minLat, 'maxLat'=> $maxLat, 'minLng'=> $minLng, 'maxLng'=> $maxLng);
    	return $res;
    }
    
    public function getAddressData($address_id) {
        $addressQuery = "SELECT * FROM tbl_customer_addresses WHERE id=?";
        $address_data = $this->conn->prepare($addressQuery);
        $address_data->bindParam(1, $address_id);
	    $address_data->execute();
	    // print_r($address_data->errorInfo());
		if($address_data->rowCount()) {
			$data = $address_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
    }
    
    public function getCustomerInfo($customerid) {
        $userQuery = "SELECT * FROM tbl_customers WHERE id=?";
        $user_data = $this->conn->prepare($userQuery);
        $user_data->bindParam(1, $customerid);
	    $user_data->execute();
	    //print_r($user_data->errorInfo());
		if($user_data->rowCount()) {
			$data = $user_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
    }
      
    private function isEmailExists($email, $id = null) {
        $userQuery = "SELECT id from tbl_customers WHERE email=?";
        if ($id != NULL)
            $userQuery.= " AND id != ?";
        $user_data = $this->conn->prepare($userQuery);
		$user_data->bindParam(1, $email);
		if ($id != NULL)
			$user_data->bindParam(2, $id);
		$user_data->execute();
		// print_r($user_data->errorInfo());
        return $user_data->rowCount() > 0;
    }

    private function isMobileExists($mobile, $id = null, $mobile_code='') {
        $userQuery = "SELECT id from tbl_customers WHERE mobile =? AND mobile_code=?";
        if ($id != NULL)
            $userQuery.= " AND id !=?";
        $user_data = $this->conn->prepare($userQuery);
		$user_data->bindParam(1, $mobile);
		$user_data->bindParam(2, $mobile_code);
		if ($id != NULL)
			$user_data->bindParam(3, $id);
		$user_data->execute();
		// print_r($user_data->errorInfo());
        return $user_data->rowCount() > 0;
    }
    
    private function isMobileVerifiedInTemp($mobile, $mobile_code='+91') {
        $userQuery = "SELECT id from tbl_temp_customers WHERE mobile =? AND mobile_code=? AND type='V' AND isverified='Y'";
        $user_data = $this->conn->prepare($userQuery);
		$user_data->bindParam(1, $mobile);
		$user_data->bindParam(2, $mobile_code);
		$user_data->execute(); 
		//print_r($user_data->errorInfo());
        return $user_data->rowCount() > 0;
    }
    
    public function removeMobileFromTemp($mobile, $mobile_code='') {
        $userQuery = "Delete FROM tbl_temp_customers WHERE mobile =? AND mobile_code =?";
        $user_data = $this->conn->prepare($userQuery);
        $user_data->bindParam(1, $mobile);
		$user_data->bindParam(2, $mobile_code);
	    $user_data->execute();
	    // print_r($user_data->errorInfo());
    }

    public function sendotp($mobile, $type, $mobile_code) {
        $this->removeMobileFromTemp($mobile, $mobile_code);
        $otp = rand(1000, 9999);
        if ($type=='V') {
            $template="customer_verification";
        }
        if ($type=='F') {
            $template="customer_forgot_password";
        }
		$addedat=time();
        $insertQuery = "INSERT INTO tbl_temp_customers SET mobile_code=?, mobile=?, otp=?, type=?, created=?";
        $user_insert = $this->conn->prepare($insertQuery);
        $user_insert->bindParam(1, $mobile_code);
        $user_insert->bindParam(2, $mobile);
		$user_insert->bindParam(3, $otp);
		$user_insert->bindParam(4, $type);
		$user_insert->bindParam(5, $addedat);
	    $user_insert->execute();
	    //print_r($user_insert->errorInfo());
        if($user_insert->rowCount()>0) {
            $this->sendTemplatesInSMS($template, $otp, $mobile, $mobile_code, '');
            return $otp;
        } else {
            return 0;
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
            $message= str_replace("{ORDER_ID}", $orderid, (str_replace("{OTP}", $otp, $mailTemplate['content'])));
            //echo $message;
			$this->send_sms($message, $mobile, $mobile_code);
		}
	}
    
    public function getRiderIdByMobileNo($mobile, $mobile_code) {
        $userQuery = "SELECT id FROM tbl_customers WHERE mobile = ? AND mobile_code=?";
        $user_data = $this->conn->prepare($userQuery);
        $user_data->bindParam(1, $mobile);
        $user_data->bindParam(2, $mobile_code);
	    $user_data->execute();
	    //print_r($user_data->errorInfo());
		if($user_data->rowCount()) {
			$data = $user_data->fetch(PDO::FETCH_ASSOC);
			return $data;
		}
    }
    
    public function sendTemplatesInMail($mailTitle, $toName, $toEmail, $invoice_id=''){
		$this->includeGlobalSiteSetting();
		$templateQuery = "select subject, content from tbl_templates where type='E' AND title=? AND status='A'"; 
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
			//print_r($mail);
		} catch (Exception $e) {
			//print_r($e);
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
    
    public function send_sms($text='', $to='', $country_code='+91') {
		$this->includeGlobalSiteSetting();
		$this->includeTwilioLib();
		
		$to = $country_code.$to;
		$sender = GLOBAL_TWILIO_NUMBER;
        $sid = GLOBAL_TWILIO_SID;
        $token = GLOBAL_TWILIO_TOKEN;

        $client = new Twilio\Rest\Client($sid, $token);
		try {
			@$resp = $client->messages->create($to, array( 'from' => "$sender", 'body' => "$text" ));
			//print_r($resp);
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
	
	public function includeSMTPMailerLib() {
		$filesArr=get_required_files();
		$searchString=PHPMAILER_LIB_PATH;
		if(!in_array($searchString, $filesArr)) {
			require PHPMAILER_LIB_PATH;
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
	
	public function sendFCMPushNotification($message, $registration_ids, $alert_message='EL-GUERO2 NOTIFICATIONS', $noti_type, $send_to, $order_id=0) {
		/* $send_to=DELIVERYMAN / BRANCH / CUSTOMER */
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
    
    public function matchHashPassword($password, $dbPassword) {
		$hashPassword = crypt($password, $dbPassword);
		return $res=$this->hashEquals($dbPassword, $hashPassword);
	}
    
    public function hashEquals($known_string, $user_string)
	{
		// For CI3 or PHP >= 5.6
		if (function_exists('hash_equals'))
		{
			return hash_equals($known_string, $user_string);
		}

		// For CI2 with PHP < 5.6
		// Code from CI3 https://github.com/bcit-ci/CodeIgniter/blob/develop/system/core/compat/hash.php
		if (!is_string($known_string))
		{
			trigger_error('hash_equals(): Expected known_string to be a string, ' . strtolower(gettype($known_string)) . ' given', E_USER_WARNING);
			return FALSE;
		}
		else if (!is_string($user_string))
		{
			trigger_error('hash_equals(): Expected user_string to be a string, ' . strtolower(gettype($user_string)) . ' given', E_USER_WARNING);
			return FALSE;
		}
		else if (($length = strlen($known_string)) !== strlen($user_string))
		{
			return FALSE;
		}

		$diff = 0;
		for ($i = 0; $i < $length; $i++)
		{
			$diff |= ord($known_string[$i]) ^ ord($user_string[$i]);
		}

		return ($diff === 0);
	}
	
	public function generatePassword($password) {
		$hash = crypt($password, ''); // second params is salt

		if (strlen($hash) > 13) {
			return $hash;
		}
		return FALSE;
	}
    /* =========================== CALLED FUNCTIONS END   =====================*/
}
 
?>
