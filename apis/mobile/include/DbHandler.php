<?php
//phpinfo();
error_reporting(E_ALL);
ini_set('display_errors', 1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use mikehaertl\wkhtmlto\Pdf;
use Razorpay\Api\Api;

class DbHandler {
    private $conn;
    public $image_extensions = array(
        'jpg', 'jpeg', 'png', 'gif'
    );

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/Entitysport.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function closeDbConnection(){
        if(!empty($this->conn)){
            $this->conn=null;
        }
    }

    public function closeStatement($statement){
        if(!empty($statement)){
            $statement=null;
        }
    }

  public function  getsinglerow($table,$condition)
    {
        
    $user_query = "SELECT * FROM $table WHERE 1 $condition";
    $user_result = $this->conn->prepare($user_query);
    $user_result->execute();
    $num_rows = $user_result->rowCount();
    $user = $user_result->fetch(PDO::FETCH_ASSOC);
    $this->closeStatement($user_result);
    return $user; 
    }


    public function createFolderOnS3Bucket($folderPath){
    
        require_once AWS_LIB;
        $result = array();
        // Set Amazon s3 credentials
        $client =  S3Client::factory(
                    array(
                        'credentials'=>array(
                            'key'    => AWS_KEY,
                            'secret' => AWS_SECRET
                        ),
                        'region' => AWS_REGION,
                        'version' => "latest"
                    )
                );
        $bucket = AWS_BUCKET;
        $key = $folderPath;
        try {
                $res = $client->putObject(array(
                    'Bucket'=>$bucket,
                    'Key' => $key,
                    'ACL'    => 'public-read',
                ));
            $result=$res->toArray();
            $result=$result['ObjectURL'];
        } catch (S3Exception $e) {
          // Catch an S3 specific exception.
            $result = array("status"=>"error", "data"=>"Something went wrong while uploading file! Please try again later!");
        }
        return $result;
    }

    public function check_app_version($version_code,$header_device_type){
        $user_query = "SELECT * FROM tbl_app_version WHERE version_code >? AND device_type=?";
        $user_result = $this->conn->prepare($user_query);
        $user_result->bindParam(1, $version_code);
        $user_result->bindParam(2, $header_device_type);
        $user_result->execute();
        $user = $user_result->fetch(PDO::FETCH_ASSOC);

        $this->closeStatement($user_result);

        if (empty($user)) {
            return 'APP_ALREADY_UPDATED';
        }
        
        $latestApp=array();
        $latestApp['title']="Update Is Available";
        $latestApp['app_link']=APK_DOWNLOAD_URL;
        $latestApp['app_download_link']=APKURL;
        $latestApp['version_name']=$user['version_name'];
        $latestApp['version_code']=$user['version_code'];
        $latestApp['version_desc']=$user['version_desc'];
        $latestApp['update_type']=$user['update_type'];
        
        if($latestApp['update_type']=='F'){
            return $latestApp;
        }

        $user_query = "SELECT IFNULL(count(id),0) as data FROM tbl_app_versions_logs WHERE version_code >? AND device_type=? AND update_type='F'";
        $user_result = $this->conn->prepare($user_query);
        $user_result->bindParam(1, $version_code);
        $user_result->bindParam(2, $header_device_type);
        $user_result->execute();
        
        $user = $user_result->fetch(PDO::FETCH_ASSOC);
        
        $this->closeStatement($user_result);
        
        if($user['data']>0) {
            $latestApp['update_type']="F";
        }
        
        return $latestApp;
    }

    public function social_login($email,$firstname,$lastname,$social_type,$social_id,$device_id, $header_device_type,$header_device_info,$header_app_info){
        
        $user=array();
        if(!empty($social_id)){
        $sel_user_query = "SELECT id, status,email,is_email_verified,social_id FROM tbl_customers WHERE (social_id = ? OR email = ?) AND is_deleted='N'";
            $user_result = $this->conn->prepare($sel_user_query);
            $user_result->bindParam(1, $social_id);
            $user_result->bindParam(2, $email);
            $user_result->execute();
            $num_rows = $user_result->rowCount();
              
            if ($num_rows > 0) {
                $user = $user_result->fetch(PDO::FETCH_ASSOC);
            }
            $this->closeStatement($user_result);
        }
        
        

        if(empty($user) && !empty($email)){
            $user_query = "SELECT id,status,email,is_email_verified,social_id FROM tbl_customers WHERE email = ? AND is_deleted='N'";
            $user_result = $this->conn->prepare($user_query);
            $user_result->bindParam(1, $email);
            $user_result->execute();
            $num_rows = $user_result->rowCount();
              
            if ($num_rows > 0) {
                $user = $user_result->fetch(PDO::FETCH_ASSOC);
            }
            
            $this->closeStatement($user_result);
        }


        


        

        if (empty($user)) {
            $current_time = time();
            $country_mobile_code =  "";
            $phone =  "";
            $used_referral_code =  '';
            $real_password = $email.$firstname;

            $secure_password = md5($real_password);
            
            $saved_customer_id =$this->saveSocialCustomer($firstname,$lastname,$email, $secure_password, $country_mobile_code, $phone,$social_type,$social_id);
            if ($saved_customer_id > 0) {   
                $customer_logins_flag = $this->saveCustomerDetailsToCustomerLogins($saved_customer_id, $device_id, $header_device_type);  
                $customer_logs_flag = $this->saveCustomerDetailsToCustomerLogs($saved_customer_id, $device_id, $header_device_type,$header_device_info,$header_app_info);
                if ($customer_logins_flag = 1 && $customer_logs_flag == 1) {                                
                            
                            
                            //$my_referral_code = REFERRAL_INITIAL.$saved_customer_id;
                            $my_referral_code = strtoupper($this->generateRandomString(8)).$saved_customer_id;
                            $my_team_name = (strlen($firstname)>5?strtoupper(substr($firstname,0,5)):strtoupper($firstname)).$saved_customer_id;
                            $used_referral_customer_id = $this->getUsedReferralCustomerIdAndAmount($used_referral_code);
                            $used_referral_customer_id = !empty($used_referral_customer_id) ? $used_referral_customer_id : 0;
                            
                            $this->updateReferralToCustomer($my_referral_code, $used_referral_code, $used_referral_customer_id, $saved_customer_id,$my_team_name);
                            
                            if (!empty($email)){
                                $this->sendTemplatesInMail('customer_welcome', $firstname, $email);
                            }
                                
                } else {
                         return 'UNABLE_TO_PROCEED';
                }
            } else {
                return 'UNABLE_TO_PROCEED';
            } 
        }else{
            if($user['status']=='D'){
                return 'STATUS_DEACTIVATED';
            }
            
       ///     print_r($user);
            $saved_customer_id=$user['id'];
            $saved_email=$user['email'];
            $saved_social_id=$user['social_id'];
            $is_email_verified=$user['is_email_verified'];
            if($is_email_verified=="N" && !empty($email)){
                $time = time();
                if((!empty($saved_email) && $saved_email==$email) || empty($saved_email)){
                    $select_user_query = "UPDATE tbl_customers SET is_social='Y', social_type=?, is_email_verified='Y', modified=?, social_id=?, email=?  WHERE id=?";
                    $select_user  = $this->conn->prepare($select_user_query);
                    $select_user->bindParam(1,$social_type);
                    $select_user->bindParam(2,$time);
                    $select_user->bindParam(3,$social_id);
                    $select_user->bindParam(4,$email);
                    $select_user->bindParam(5,$saved_customer_id);
                    if (!$select_user->execute()) {
                        return 'UNABLE_TO_PROCEED';
                    }
                    $this->closeStatement($select_user);

                }
                
            }else if(empty($saved_social_id) && !empty($social_id)){
                $time = time();
                $select_user_query = "UPDATE tbl_customers SET is_social='Y', social_type=?, modified=?, social_id=? WHERE id=?";
                $select_user  = $this->conn->prepare($select_user_query);
                $select_user->bindParam(1,$social_type);
                $select_user->bindParam(2,$time);
                $select_user->bindParam(3,$social_id);
                $select_user->bindParam(4,$saved_customer_id);
                if (!$select_user->execute()) {
                    return 'UNABLE_TO_PROCEED';
                }
                $this->closeStatement($select_user);

            }
            
            $customer_logins_flag = $this->saveCustomerDetailsToCustomerLogins($saved_customer_id, $device_id, $header_device_type);  
            $customer_logs_flag = $this->saveCustomerDetailsToCustomerLogs($saved_customer_id, $device_id, $header_device_type,$header_device_info,$header_app_info);
        }
        //echo $saved_customer_id;die;
        return $this->getUpdatedProfileData($saved_customer_id);
        
    }

    
    public function deleteaccount($user_id, $devcieid='',$device_type) {
        $check_valid_user_query = "SELECT COUNT(id) AS cnt FROM tbl_customer_logs WHERE device_id = ? AND device_type = ? AND customer_id = ?";
        $check_valid_user  = $this->conn->prepare($check_valid_user_query);
        $check_valid_user->bindParam(1,$devcieid);
        $check_valid_user->bindParam(2,$device_type);
        $check_valid_user->bindParam(3,$user_id);
        $check_valid_user->execute();     
        $check_valid_user_arr = $check_valid_user->fetch();
        $this->closeStatement($check_valid_user);
        if ($check_valid_user_arr['cnt'] > 0) {
            $time = time();
            $update_user_query = "UPDATE tbl_customer_logs SET logout_time = '$time' WHERE device_id = ?  AND device_type = ? AND customer_id = ?";
            $select_user  = $this->conn->prepare($update_user_query);
            $select_user->bindParam(1,$devcieid);
            $select_user->bindParam(2,$device_type);
            $select_user->bindParam(3,$user_id);           

            if ($select_user->execute()) {
                $this->closeStatement($select_user);
                $remove_login_logs_query = "UPDATE tbl_customer_logins SET customer_id='0' WHERE device_id = ?  AND device_type = ? AND customer_id = ?";
                $remove_login_logs  = $this->conn->prepare($remove_login_logs_query);
                $remove_login_logs->bindParam(1,$devcieid);
                $remove_login_logs->bindParam(2,$device_type);
                $remove_login_logs->bindParam(3,$user_id);
                $remove_login_logs->execute(); 
                $this->closeStatement($remove_login_logs);
                
                   $remove_login_logs_query = "UPDATE tbl_customers SET is_deleted='Y' WHERE  id = ?";
                $remove_login_logs  = $this->conn->prepare($remove_login_logs_query);
                $remove_login_logs->bindParam(1,$user_id);
                $remove_login_logs->execute(); 
                $this->closeStatement($remove_login_logs);
                
                
                
                
                return 'SUCCESS';
            } else {
                $this->closeStatement($select_user);
                return 'UNABLE_TO_PROCEED';
            }
        } else {
            return 'INVALID_USER_ACCESS';
        }
    }

    public function check_user($username,$type) {

        $response = array();
        if($type=="M"){
              $user_query = "SELECT slug,status,phone,country_mobile_code FROM tbl_customers WHERE phone = ? AND is_deleted='N'";
        }else if($type=="E"){
             $user_query = "SELECT slug,status FROM tbl_customers WHERE email = ? AND is_deleted='N'";
        }

        $user_result = $this->conn->prepare($user_query);
        $user_result->bindParam(1, $username);
        $user_result->execute();
        $user = $user_result->fetch(PDO::FETCH_ASSOC);
         $this->closeStatement($user_result);

        if (!empty($user)) {

                if($user['status']=='D'){
                    return 'STATUS_DEACTIVATED';
                }else{
                    $response['type']=$type;
                    $response['slug']=$this->base64_encode($user['slug']);

                    if($type=="M"){
                        $otpCode=$this->sendotp($user['phone'], 'L', $user['country_mobile_code'], "");
                        $response['otp']=$otpCode;
                        $response['phone']=$user['phone'];
                        $response['country_mobile_code']=$user['country_mobile_code'];
                    }else{
                        $response['email']=$username;
                    }
                    return $response;
                }

        }else{
                return 'NO_RECORD';
        }

    }    


    public function newUser($country_mobile_code, $phone, $jsonstring,$id=0) {
        $response = array();
        $email = $this->checkEmail($jsonstring);
        if ($this->isEmailExists($email,$id)) { 
            return 'EMAIL_ALREADY_EXISTED';
        }
        if ($this->isPhoneExists($phone, null, $country_mobile_code) == 0) {
                $referral_code  =  $this->checkReferral($jsonstring);
                if (!empty($referral_code)) {
                    $is_referral_exist  =   $this->getUsedReferralcustomerIdAndAmount($referral_code);
                    if (empty($is_referral_exist)) {
                        return 'INVALID_REFERRAL';
                    }
                }
                $otpCode=$this->sendotp($phone, 'V', $country_mobile_code, $jsonstring);
                if ($otpCode>0) {
                     $response['otp']=$otpCode;
                     $response['phone']=$phone;
                     $response['country_mobile_code']=$country_mobile_code;
                    return $response;
                } else {
                    return 'UNABLE_TO_PROCEED';
                }       
        } else {
            return 'PHONE_ALREADY_EXISTED';
        }
    }


    public function verifyOtp($otp, $type, $country_mobile_code, $phone, $newpassword,$email,$device_id, $header_device_type,$header_device_info,$header_app_info) {
        //$verify_otp_query = "UPDATE tbl_tempcustomers SET isverified='YES' WHERE otp=? AND type=? AND country_mobile_code=? AND mobileno=?";
        $verify_otp_query = "UPDATE tbl_tempcustomers SET isverified='YES' 
        WHERE otp='".$otp."' AND type='".$type."' AND country_mobile_code='".$country_mobile_code."'";
        if($type=='FE'){
           $verify_otp_query .= " AND mobileno='".$email."'";
        }else{
           $verify_otp_query .= ' AND mobileno="'.$phone.'"';
        }
        $verify_otp = $this->conn->prepare($verify_otp_query);
        /*$verify_otp->bindParam(1, $otp);
        $verify_otp->bindParam(2, $type);
        $verify_otp->bindParam(3, $country_mobile_code);
        if($type=='FE'){
           $verify_otp->bindParam(4, $email);
        }else{
           $verify_otp->bindParam(4, $phone);
        }*/
        if(!$verify_otp->execute()){
            $this->sql_error($verify_otp);
        }
        if ($verify_otp->rowCount()) {
            $this->closeStatement($verify_otp);
            if ($type=='F') {
                $newpassword=md5($newpassword);
                $update_pass_query = "UPDATE tbl_customers SET password=? WHERE country_mobile_code=? AND phone=?";
                $updatePass = $this->conn->prepare($update_pass_query);
                $updatePass->bindParam(1, $newpassword);
                $updatePass->bindParam(2, $country_mobile_code);
                $updatePass->bindParam(3, $phone);
                $updatePass->execute();
                $this->closeStatement($updatePass);
                   return 'VERIFIED';
            }else if ($type=='FE') {
                $newpassword=md5($newpassword);
                $update_pass_query = "UPDATE tbl_customers SET password=? WHERE email=?";
                $updatePass = $this->conn->prepare($update_pass_query);
                $updatePass->bindParam(1, $newpassword);
                $updatePass->bindParam(2, $email);
                $updatePass->execute();
                $this->closeStatement($updatePass);
                   return 'VERIFIED';
            }if ($type=='L') {
                $userdata=$this->getCustomerIdByMobileNo($phone, $country_mobile_code);
                 $customer_logins_flag = $this->saveCustomerDetailsToCustomerLogins($userdata['id'], $device_id, $header_device_type);  
                        $customer_logs_flag = $this->saveCustomerDetailsToCustomerLogs($userdata['id'], $device_id, $header_device_type,$header_device_info,$header_app_info);

                if ($customer_logins_flag = 1 && $customer_logs_flag == 1) { 
                    return $this->getUpdatedProfileData($userdata['id']);
                }else{
                    return 'UNABLE_TO_PROCEED';
                }
            } else if ($type=='V') {
                $get_from_temp_query = "SELECT * FROM tbl_tempcustomers WHERE country_mobile_code=? AND mobileno=?";
                $query  = $this->conn->prepare($get_from_temp_query);
                $query->bindParam(1, $country_mobile_code);
                $query->bindParam(2, $phone);
                $query->execute();
                $num_rows = $query->rowCount();
                ///echo $num_rows; die;
                if ($num_rows > 0) {
                    $current_time = time();
                    $array = $query->fetch();
                    $this->closeStatement($query);
                    $decoded_array = json_decode($array['customer_data'], true);
                    $used_referral_code =  $decoded_array['referral_code'];
                    $real_password = $decoded_array['password'];
                    $firstname = $decoded_array['firstname'];

                    $secure_password = md5($real_password);

                   $get_from_temp_query = "SELECT * FROM tbl_customers WHERE email=? AND is_deleted='N'";
                $query  = $this->conn->prepare($get_from_temp_query);
                $query->bindParam(1, $email);
                $query->execute();
              $checkgoogle = $query->fetch();
                if(!isset($checkgoogle['id']))
                {
                    $saved_customer_id = $this->saveCustomer($firstname, $decoded_array['email'], $secure_password, $decoded_array['country_mobile_code'], $decoded_array['phone']);             
                    if ($saved_customer_id > 0) {                       
                        $customer_logins_flag = $this->saveCustomerDetailsToCustomerLogins($saved_customer_id, $device_id, $header_device_type);  
                        $customer_logs_flag = $this->saveCustomerDetailsToCustomerLogs($saved_customer_id, $device_id, $header_device_type,$header_device_info,$header_app_info);

                        if ($customer_logins_flag = 1 && $customer_logs_flag == 1) {                                
                            
                            $lastlogin    = date("Y-m-d h:i:s");
                            $my_referral_code = strtoupper($this->generateRandomString(8)).$saved_customer_id;
                            $my_team_name = (strlen($firstname)>5?strtoupper(substr($firstname,0,5)):strtoupper($firstname)).$saved_customer_id;
                            $used_referral_customer_id = $this->getUsedReferralCustomerIdAndAmount($used_referral_code);
                            $used_referral_customer_id = !empty($used_referral_customer_id) ? $used_referral_customer_id : 0;

                            $this->updateReferralToCustomer($my_referral_code, $used_referral_code, $used_referral_customer_id, $saved_customer_id,$my_team_name);

                            if ($decoded_array['email']!='')
                                $this->sendTemplatesInMail('customer_welcome', $firstname, $decoded_array['email']);
                                
                            return $this->getUpdatedProfileData($saved_customer_id);
                        } else {

                                 return 'UNABLE_TO_PROCEED';
                        }
                     } else {

                            return 'UNABLE_TO_PROCEED';
                     }
                }else{
                    
                    $saved_customer_id = $checkgoogle['id'];
                    
                 $update_pass_query = "UPDATE tbl_customers SET password=?, phone=?,country_mobile_code=?,is_phone_verified=? WHERE id=?  ";
                 $ph = $decoded_array['phone'];
                 $cpc = $decoded_array['country_mobile_code'];
                 $st = 'Y';
                $updatePass = $this->conn->prepare($update_pass_query);
                $updatePass->bindParam(1, $secure_password);
                $updatePass->bindParam(2, $ph);
                  $updatePass->bindParam(3, $cpc);
                   $updatePass->bindParam(4, $st);
                    $updatePass->bindParam(5, $saved_customer_id);
            $updatePass->execute();
                $this->closeStatement($updatePass);
                
                
                     $lastlogin    = date("Y-m-d h:i:s");
                            $my_referral_code = strtoupper($this->generateRandomString(8)).$saved_customer_id;
                            $my_team_name = (strlen($firstname)>5?strtoupper(substr($firstname,0,5)):strtoupper($firstname)).$saved_customer_id;
                            $used_referral_customer_id = $this->getUsedReferralCustomerIdAndAmount($used_referral_code);
                            $used_referral_customer_id = !empty($used_referral_customer_id) ? $used_referral_customer_id : 0;

                          ///  $this->updateReferralToCustomer($my_referral_code, $used_referral_code, $used_referral_customer_id, $saved_customer_id,$my_team_name);

                            if ($decoded_array['email']!='')
                                $this->sendTemplatesInMail('customer_welcome', $firstname, $decoded_array['email']);
                                
                            return $this->getUpdatedProfileData($saved_customer_id);
                }
                }   
            }
         
        } else {
            return 'INVALID_OTP';  
        }
    }

    public function get_refer_cashbonus() {
         $sel_user_query = "SELECT * FROM tbl_referral_cash_bonus";
         $sel_user = $this->conn->prepare($sel_user_query);
         $sel_user->execute();
         $output=array();
         while($cash_bonus = $sel_user->fetch(PDO::FETCH_ASSOC)){

            $output[$cash_bonus['key']]=$cash_bonus['value'];

         }
         $this->closeStatement($sel_user);
         return $output;
    }
    public function get_private_contest_entry_fee($contest_size,$prize_pool){


        $settingData=$this->get_setting_data();
        if($settingData['PRIVATE_CONTEST_MAX_PRIZE_POOL']<$prize_pool || $prize_pool<0){

            return "INVALID_PRIZE_POOL";
        }

        if($settingData['PRIVATE_CONTEST_MAX_CONTEST_SIZE']<$contest_size || $contest_size<0){

            return "INVALID_CONTEST_SIZE";
        }

        $PRIVATE_CONTEST_COMMISSION=$settingData['PRIVATE_CONTEST_COMMISSION'];
        $PRIVATE_CONTEST_MIN_FEE=$settingData['PRIVATE_CONTEST_MIN_FEE'];
        $entry_fee=($PRIVATE_CONTEST_COMMISSION/100)*$prize_pool;
        $entry_fee=($entry_fee+$prize_pool)/$contest_size;
        $entry_fee=$this->format_number($entry_fee);
        if($entry_fee<$PRIVATE_CONTEST_MIN_FEE){

            return "INVALID_ENTRY_FEE";
        }


        $output=array();
        $output['entry_fees']=$entry_fee;
        return $output;

    }

    public function get_private_contest_winning_breakup($contest_size,$prize_pool){

        $settingData=$this->get_setting_data();
        if($settingData['PRIVATE_CONTEST_MAX_PRIZE_POOL']<$prize_pool || $prize_pool<0){

            return "INVALID_PRIZE_POOL";
        }

        if($settingData['PRIVATE_CONTEST_MAX_CONTEST_SIZE']<$contest_size || $contest_size<0){

            return "INVALID_CONTEST_SIZE";
        }

        if($contest_size>100){
            $sel_query = "SELECT * FROM tbl_private_contest_breakups where is_deleted='N' AND total_winners='1'";


        }else{
            $inner_query="select breakup_ids from tbl_private_contest_breakup_rules where min_contest_size<=$contest_size AND max_contest_size>=$contest_size AND is_deleted='N'";
            $inner_query_res = $this->conn->prepare($inner_query);
            $inner_query_res->execute();
            $inner_query_res_res = $inner_query_res->fetch();
            $breakup_ids=$inner_query_res_res['breakup_ids'];

            $sel_query = "SELECT * FROM tbl_private_contest_breakups where is_deleted='N' AND id IN ($breakup_ids) ORDER BY total_winners DESC";
        }
       


         $sel_user = $this->conn->prepare($sel_query);
         $sel_user->execute();
         $output=array();
         $breakups_array=array();
         $counter=0;

         while($breakups = $sel_user->fetch(PDO::FETCH_ASSOC)){

            $breakups_array[$counter]['id']=$breakups['id'];
            $breakups_array[$counter]['total_winners']=$breakups['total_winners'];
            $contest_json=json_decode($breakups['contest_json'],true);


            $per_price=array();
            $per_percent=array();
            foreach($contest_json['per_price'] as $contest_json_per_price_value){
                $per_price[]=($contest_json_per_price_value/100)*$prize_pool;
                $per_percent[]=$contest_json_per_price_value;
            }
            $contest_json['per_price']=$per_price;
            $contest_json['per_percent']=$per_percent;


            $breakups_array[$counter]['contest_json']=$contest_json;


            $counter++;

         }
         $cat_array=null;


         $cat_query="select * from tbl_cricket_contest_categories where is_private='Y' AND is_deleted='N' limit 1";
         $cat_query_res = $this->conn->prepare($cat_query);
         $cat_query_res->execute();
         $cat_query_res_res = $cat_query_res->fetch();
         if(!empty($cat_query_res_res)){
            $cat_array['id']=$cat_query_res_res['id'];
            $cat_array['name']=$cat_query_res_res['name'];

         }


         $output['winning_breakups']=$breakups_array;
         $output['private_contest_category']=$cat_array;
         return $output;

    }

    public function create_private_contest($contest_size,$prize_pool,$winning_breakup_id,$match_id,$match_unique_id,$is_multiple,$user_id,$team_id, $pre_join){

        $settingData=$this->get_setting_data();
        if($settingData['PRIVATE_CONTEST_MAX_PRIZE_POOL']<$prize_pool || $prize_pool<0){

            return "INVALID_PRIZE_POOL";
        }

        if($settingData['PRIVATE_CONTEST_MAX_CONTEST_SIZE']<$contest_size || $contest_size<0){

            return "INVALID_CONTEST_SIZE";
        }

        $PRIVATE_CONTEST_COMMISSION=$settingData['PRIVATE_CONTEST_COMMISSION'];
        $PRIVATE_CONTEST_MIN_FEE=$settingData['PRIVATE_CONTEST_MIN_FEE'];
        $entry_fee=($PRIVATE_CONTEST_COMMISSION/100)*$prize_pool;
        $entry_fee=($entry_fee+$prize_pool)/$contest_size;
        $entry_fee=$this->format_number($entry_fee);
        if($entry_fee<$PRIVATE_CONTEST_MIN_FEE){

            return "INVALID_ENTRY_FEE";
        }

        $cat_query="select * from tbl_cricket_contest_categories where is_private='Y' AND is_deleted='N' limit 1";
        $cat_query_res = $this->conn->prepare($cat_query);
        $cat_query_res->execute();
        $cat_query_res_res = $cat_query_res->fetch();
        if(empty($cat_query_res_res)){
            
            return "INVALID_CAT_ID";

        }
        $cat_id=$cat_query_res_res['id'];
        $confirm_win_contest_percentage=$cat_query_res_res['confirm_win_contest_percentage'];
        $confirm_win=$cat_query_res_res['confirm_win'];
        $cash_bonus_used_value=$cat_query_res_res['cash_bonus_used_value'];
        $cash_bonus_used_type=$cat_query_res_res['cash_bonus_used_type'];



        $sel_query = "SELECT * FROM tbl_private_contest_breakups where is_deleted='N' AND id='$winning_breakup_id'";
        $sel_user = $this->conn->prepare($sel_query);
        $sel_user->execute();
        $breakups = $sel_user->fetch();
        if(empty($breakups)){

             return "INVALID_BREAKUP_ID";

        }

//check balance data

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        
        if(empty($get_match_data)){
            return "NO_MATCH_FOUND";
        }
        
        if($get_match_data['match_progress']!='F'){
            return "INVALID_MATCH";
        }


        $alreadey_created_team_count=$this->get_match_customer_team_count_by_match_unique_id($user_id,$match_unique_id);
        if($alreadey_created_team_count==0){
            return "INVALID_TEAM_COUNT";
        }


        $customerWalletData=$this->get_customer_wallet_detail($user_id);
        if(empty($customerWalletData)){
             return "INVALID_WALLET";
        }


        $depositWallet=$customerWalletData['wallet']['deposit_wallet'];
        $winningWallet=$customerWalletData['wallet']['winning_wallet'];
        $bonusWallet=$customerWalletData['wallet']['bonus_wallet'];

        $used_bonus=0;
        $used_deposit=0;
        $used_winning=0;

        $BONUS_WALLET_PER=$cash_bonus_used_value;
        $BONUS_WALLET_PER_TYPE=$cash_bonus_used_type;

        $need_pay=$entry_fee;

        if($BONUS_WALLET_PER_TYPE=="F" && $BONUS_WALLET_PER>$need_pay){
            $BONUS_WALLET_PER=$need_pay;
        }

        if($need_pay>0){
            if($bonusWallet>0){
                $used_bonus=$entry_fee*($BONUS_WALLET_PER/100);
                if($BONUS_WALLET_PER_TYPE=="F"){
                    $used_bonus=$BONUS_WALLET_PER;
                }
                $used_bonus=round($used_bonus,2);
                if($used_bonus>$bonusWallet){
                    $used_bonus=$bonusWallet;
                }
                $need_pay-=$used_bonus;
            }
            if($need_pay>0){
                if($depositWallet>0){
                    $used_deposit=$need_pay;
                    if($used_deposit>$depositWallet){
                        $used_deposit=$depositWallet;
                    }
                    $need_pay-=$used_deposit;
                }
            }
            if($need_pay>0){
                if($winningWallet>0){
                    $used_winning=$need_pay;
                    if($used_winning>$winningWallet){
                        $used_winning=$winningWallet;
                    }
                    $need_pay-=$used_winning;
                }
            }
        }

        $output=array();
        $output['wallet']=$customerWalletData['wallet'];
        $output['used_bonus']=$used_bonus;
        $output['used_deposit']=$used_deposit;
        $output['used_winning']=$used_winning;
        $output['need_pay']=$need_pay;
        $output['entry_fees']=$entry_fee;
        $output['to_pay']=$entry_fee-$used_bonus;
        
        $amount_suggest=array();
        if($need_pay>0){
            $a = ((int)($need_pay / 10)) * 10; 
            if($a<$need_pay){
                $a = $a + 10; 
            }
            
            $aa=$a*2;
            $aaa=($aa*2)+$a;
            
            $amount_suggest[0]=$a;
            $amount_suggest[1]=$aa;
            $amount_suggest[2]=$aaa;
            
        }
        $output['amount_suggest']=$amount_suggest;
        if($pre_join=='Y'){
            return $output;
        }else{
            if($need_pay>0){
                return "LOW_BALANCE";
            }
        }


//check balance data end
        $contest_json=json_decode($breakups['contest_json'],true);

        $per_price=array();
        foreach($contest_json['per_price'] as $contest_json_per_price_value){
            $prize_before_formate=($contest_json_per_price_value/100)*$prize_pool;
            $per_price[]=$this->format_number($prize_before_formate);
        }
        $contest_json['per_price']=$per_price;
        $contest_json=json_encode($contest_json);

        $per_user_team_allowed=1;
        if($is_multiple=="Y"){
            $per_user_team_allowed=10000;
        }

        $is_private="Y";
        $time=time();


        $insert_private_contest_query = "INSERT INTO tbl_cricket_contest_matches SET category_id = ?,  match_id = ?, match_unique_id = ?, total_team=?, total_price=?, entry_fees=?, per_user_team_allowed=?, contest_json=?, confirm_win=?, user_id=?, is_private=?,created_at=?,confirm_win_contest_percentage=?";
        $update_customer_logins = $this->conn->prepare($insert_private_contest_query);
        $update_customer_logins->bindParam(1, $cat_id);
        $update_customer_logins->bindParam(2, $match_id);       
        $update_customer_logins->bindParam(3, $match_unique_id);
        $update_customer_logins->bindParam(4, $contest_size);
        $update_customer_logins->bindParam(5, $prize_pool);
        $update_customer_logins->bindParam(6, $entry_fee);
        $update_customer_logins->bindParam(7, $per_user_team_allowed);
        $update_customer_logins->bindParam(8, $contest_json);
        $update_customer_logins->bindParam(9, $confirm_win);
        $update_customer_logins->bindParam(10, $user_id);
        $update_customer_logins->bindParam(11, $is_private);
        $update_customer_logins->bindParam(12, $time);
        $update_customer_logins->bindParam(13, $confirm_win_contest_percentage);
        //$update_customer_logins->execute();
         
        if($update_customer_logins->execute()){
            $id=$this->conn->lastInsertId();
            $slug=$this->generateRandomString(12).$id."_";

            $save_user_query = "UPDATE tbl_cricket_contest_matches SET slug=? WHERE id=?";                
            $save_user  = $this->conn->prepare($save_user_query);       
            $save_user->bindParam(1,$slug);
            $save_user->bindParam(2,$id); 
            $save_user->execute();
            $this->closeStatement($update_customer_logins);

            $output=$this->customer_join_contest($user_id,$match_unique_id,$id,$team_id);
            return $output;
        } else {
             $this->sql_error($update_customer_logins);
             return 'UNABLE_TO_PROCEED'; 
        }
    }



     public function get_setting_data() {
         $sel_user_query = "SELECT * FROM tbl_settings";
         $sel_user = $this->conn->prepare($sel_user_query);
         $sel_user->execute();
         $output=array();
         while($cash_bonus = $sel_user->fetch(PDO::FETCH_ASSOC)){

            $output[$cash_bonus['key']]=$cash_bonus['value'];

         }
         $this->closeStatement($sel_user);
         return $output;
    }


      public function forgotPassword($country_mobile_code, $phone) {
         /* echo $country_mobile_code;
          echo $phone;*/
        $userdata=$this->getCustomerIdByMobileNo($phone, $country_mobile_code);
         if (!empty($userdata)) {
            $otpCode=$this->sendotp($phone, 'F', $country_mobile_code,'');
            $response['otp']=$otpCode;
            $response['phone']=$phone;
            $response['country_mobile_code']=$country_mobile_code;
            return $response;
        } else {
            return 'INVALID_MOBILE';
        }
    }  

    public function forgotPasswordEmail($email) {
        $userdata=$this->getCustomerIdByEmail($email);
        if (!empty($userdata)) {
            $phone=$userdata['phone'];
            $country_mobile_code=$userdata['country_mobile_code'];
            $otpCode=$this->sendotpEmail($email, 'FE','');
            $response['otp']=$otpCode;
            $response['email']=$email;
                    $response['phone']=$phone;
    return $response;
        } else {
            return 'INVALID_EMAIL';
        }
    }

    public function login($email, $password, $device_id, $device_type,$device_info,$app_info) {
        $md5password = md5($password);         
        $sel_user_query = "SELECT id, status FROM tbl_customers WHERE (email = ? OR phone = ?) 
        AND (password=? OR password=?) AND is_deleted='N'";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $email);
        $sel_user->bindParam(2, $email);
        $sel_user->bindParam(3, $password);
        $sel_user->bindParam(4, $md5password);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($sel_user);
        $response = array();
        if (!empty($user)) {
            if ($user['status'] != 'A') {
                return 'USER_ACCOUNT_DEACTVATED';
            } else {

         ///       $customer_logins_flag = $this->saveCustomerDetailsToCustomerLogins($user['id'], $device_id, $device_type);  
           ///     $customer_logs_flag = $this->saveCustomerDetailsToCustomerLogs($user['id'], $device_id, $device_type,$device_info,$app_info);               
                   
                   
                    $response = $this->getUpdatedProfileData($user['id']);                       
                    return $response;
            }
        } else {
            return 'INVALID_USERNAME_PASSWORD';
        }
    }

    public function logout($user_id, $devcieid='',$device_type) {
        $check_valid_user_query = "SELECT COUNT(id) AS cnt FROM tbl_customer_logs WHERE device_id = ? AND device_type = ? AND customer_id = ?";
        $check_valid_user  = $this->conn->prepare($check_valid_user_query);
        $check_valid_user->bindParam(1,$devcieid);
        $check_valid_user->bindParam(2,$device_type);
        $check_valid_user->bindParam(3,$user_id);
        $check_valid_user->execute();     
        $check_valid_user_arr = $check_valid_user->fetch();
        $this->closeStatement($check_valid_user);
        if ($check_valid_user_arr['cnt'] > 0) {
            $time = time();
            $update_user_query = "UPDATE tbl_customer_logs SET logout_time = '$time' WHERE device_id = ?  AND device_type = ? AND customer_id = ?";
            $select_user  = $this->conn->prepare($update_user_query);
            $select_user->bindParam(1,$devcieid);
            $select_user->bindParam(2,$device_type);
            $select_user->bindParam(3,$user_id);           

            if ($select_user->execute()) {
                $this->closeStatement($select_user);
                $remove_login_logs_query = "UPDATE tbl_customer_logins SET customer_id='0' WHERE device_id = ?  AND device_type = ? AND customer_id = ?";
                $remove_login_logs  = $this->conn->prepare($remove_login_logs_query);
                $remove_login_logs->bindParam(1,$devcieid);
                $remove_login_logs->bindParam(2,$device_type);
                $remove_login_logs->bindParam(3,$user_id);
                $remove_login_logs->execute(); 
                $this->closeStatement($remove_login_logs);
                return 'SUCCESS';
            } else {
                $this->closeStatement($select_user);
                return 'UNABLE_TO_PROCEED';
            }
        } else {
            return 'INVALID_USER_ACCESS';
        }
    }


    public function updateToken($device_id,$device_type,$device_token,$header_device_info,$header_app_info){
         
        $current_time =time();
        $update_customer_logins_query = "INSERT INTO tbl_customer_logins SET device_token = ?,  device_id = ?, device_type = ?, created=?, device_info=?, app_info=? ON DUPLICATE KEY UPDATE  device_token=?, device_info=?, app_info=?, created=?";
        $update_customer_logins = $this->conn->prepare($update_customer_logins_query);
        $update_customer_logins->bindParam(1, $device_token);
        $update_customer_logins->bindParam(2, $device_id);       
        $update_customer_logins->bindParam(3, $device_type);
        $update_customer_logins->bindParam(4, $current_time);
        $update_customer_logins->bindParam(5, $header_device_info);
        $update_customer_logins->bindParam(6, $header_app_info);
        $update_customer_logins->bindParam(7, $device_token);
        $update_customer_logins->bindParam(8, $header_device_info);
        $update_customer_logins->bindParam(9, $header_app_info);
        $update_customer_logins->bindParam(10, $current_time);
        //$update_customer_logins->execute();
         
        if($update_customer_logins->execute()){
            $this->closeStatement($update_customer_logins);
             return 'SUCCESS';
        } else {
             $this->sql_error($update_customer_logins);
             return 'UNABLE_TO_PROCEED'; 
        }
    }




    public function get_matches($match_progress) {
       $query = "SELECT tcm.playing_squad_updated as playing_squad_updated, tcm.match_progress as match_progress, tcm.unique_id as match_unique_id, tcm.name as match_name, tcm.match_date as match_date,tcm.close_date as close_date, tcm.match_limit as match_limit, tcm.id as id, tcs.name as series_name, tcs.id as series_id, tgt.name as gametype_name, tgt.id as gametype_id, tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two,tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two, (SELECT IFNULL(COUNT(id),0) from tbl_cricket_contest_matches tccm where tccm.match_id=tcm.id AND tccm.status='A' AND tccm.is_deleted='N') as active_contest_count FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON (tcm.series_id=tcs.id) LEFT JOIN tbl_game_types tgt ON (tcm.game_type_id=tgt.id) LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE  tcm.status='A' AND tcm.is_deleted='N'";

       if($match_progress=="R"){

        $query.=" AND tcm.match_progress IN ('R','AB')";


       }elseif($match_progress=="L"){

        $query.=" AND tcm.match_progress IN ('L','IR')";


       }else{
          $query.=" AND tcm.match_progress IN ('$match_progress')";

       }
       if($match_progress=="F"){
         $query.=" ORDER BY tcm.close_date ASC";
       }elseif($match_progress=="R"){
         $query.=" ORDER BY tcm.points_updated_at DESC";
       }else{
         $query.=" ORDER BY tcm.close_date DESC";
       }
        $query_res = $this->conn->prepare($query);    
        //$query_res->bindParam(1, $match_progress);
        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    $current_time=time();
                    while($matchdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $match=array();

                            $match['id'] = $matchdata['id'];
                            $match['match_id'] = $matchdata['match_unique_id'];
                            $match['name'] = $matchdata['match_name'];
                            $match['match_date'] = $matchdata['match_date'];
                            $match['close_date'] = $matchdata['close_date'];
                            $match['match_progress'] = $matchdata['match_progress'];
                            $match['server_date'] = $current_time;
                            $match['match_limit'] = $matchdata['match_limit'];
                            $match['contest_count'] = $matchdata['active_contest_count'];
                            $match['playing_squad_updated'] = $matchdata['playing_squad_updated'];

                            $series=array();
                            $series['id']=$matchdata['series_id'];
                            $series['name']=$matchdata['series_name'];
                            $match['series'] = $series;


                            $gametype=array();
                            $gametype['id']=$matchdata['gametype_id'];
                            $gametype['name']=$matchdata['gametype_name'];
                            $match['gametype'] = $gametype;

                            
                            $team1=array();
                            $team1['id']=$matchdata['team_id_one'];
                            $team1['name']=$matchdata['team_name_one'];
                            $team1['sort_name']=$matchdata['team_sort_name_one'];
                            $team1['image']=!empty($matchdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$matchdata['team_image_one'] : NO_IMG_URL_TEAM;
                            $match['team1'] = $team1;



                            $team2=array();
                            $team2['id']=$matchdata['team_id_two'];
                            $team2['name']=$matchdata['team_name_two'];
                            $team2['sort_name']=$matchdata['team_sort_name_two'];
                            $team2['image']=!empty($matchdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$matchdata['team_image_two'] : NO_IMG_URL_TEAM;
                            $match['team2'] = $team2;

                            




                            $output[$i]=$match;
                            $i++;
                    }
                    $this->closeStatement($query_res);
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
                $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_match_data($match_unique_id) {
       $query = "SELECT tcm.playing_squad_updated as playing_squad_updated,tcm.match_progress as match_progress, tcm.unique_id as match_unique_id, tcm.name as match_name, tcm.match_date as match_date,tcm.close_date as close_date, tcm.match_limit as match_limit, tcm.id as id, tcs.name as series_name, tcs.id as series_id, tgt.name as gametype_name, tgt.id as gametype_id, tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two,tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two,tcm.game_id as game_id FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON (tcm.series_id=tcs.id) LEFT JOIN tbl_game_types tgt ON (tcm.game_type_id=tgt.id) LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE  tcm.unique_id=?";


        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);
        $output = NULL;
        if($query_res->execute()){

                if ($query_res->rowCount() > 0) {
                    $current_time=time();
                    $matchdata = $query_res->fetch(PDO::FETCH_ASSOC);
                    $this->closeStatement($query_res);

                    $match=array();

                    $match['id'] = $matchdata['id'];
                    $match['match_id'] = $matchdata['match_unique_id'];
                    $match['name'] = $matchdata['match_name'];
                    $match['match_date'] = $matchdata['match_date'];
                    $match['close_date'] = $matchdata['close_date'];
                    $match['match_progress'] = $matchdata['match_progress'];
                    $match['server_date'] = $current_time;
                    $match['match_limit'] = $matchdata['match_limit'];
                    $match['playing_squad_updated'] = $matchdata['playing_squad_updated'];
                            $match['match_type'] = $matchdata['game_id'];

                    $series=array();
                    $series['id']=$matchdata['series_id'];
                    $series['name']=$matchdata['series_name'];
                    $match['series'] = $series;


                    $gametype=array();
                    $gametype['id']=$matchdata['gametype_id'];
                    $gametype['name']=$matchdata['gametype_name'];
                    $match['gametype'] = $gametype;


                    $team1=array();
                    $team1['id']=$matchdata['team_id_one'];
                    $team1['name']=$matchdata['team_name_one'];
                    $team1['sort_name']=$matchdata['team_sort_name_one'];
                    $team1['image']=!empty($matchdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$matchdata['team_image_one'] : NO_IMG_URL_TEAM;
                    $match['team1'] = $team1;



                    $team2=array();
                    $team2['id']=$matchdata['team_id_two'];
                    $team2['name']=$matchdata['team_name_two'];
                    $team2['sort_name']=$matchdata['team_sort_name_two'];
                    $team2['image']=!empty($matchdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$matchdata['team_image_two'] : NO_IMG_URL_TEAM;
                    $match['team2'] = $team2;






                    $output=$match;
                }else{
                    $this->closeStatement($query_res);
                }
        }else{
            $this->closeStatement($query_res);
        }

        return $output;

    }




    public function get_match_players($match_id) {
       $query = "SELECT tcm.match_progress as match_progress, tcm.unique_id as match_unique_id, tcm.name as match_name, tcm.match_date as match_date, tcm.close_date as close_date, tcm.match_limit as match_limit, tcm.id as id, tcs.name as series_name, tcs.id as series_id, tgt.name as gametype_name, tgt.id as gametype_id, tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two,tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two,tcm.game_id as game_id FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON (tcm.series_id=tcs.id) LEFT JOIN tbl_game_types tgt ON (tcm.game_type_id=tgt.id) LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE tcm.status='A' AND tcm.is_deleted='N' AND tcm.id=?";
        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $match_id);      
        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $current_time=time();

                            $matchdata = $query_res->fetch(PDO::FETCH_ASSOC);
                            $this->closeStatement($query_res);

                            $match=array();

                            $match['id'] = $matchdata['id'];
                            $match['match_id'] = $matchdata['match_unique_id'];
                            $match['name'] = $matchdata['match_name'];
                            $match['match_date'] = $matchdata['match_date'];
                            $match['close_date'] = $matchdata['close_date'];
                            $match['match_progress'] = $matchdata['match_progress'];
                            $match['server_date'] = $current_time;
                            $match['match_limit'] = $matchdata['match_limit'];
                            $match['match_type'] = $matchdata['game_id'];

                            $series=array();
                            $series['id']=$matchdata['series_id'];
                            $series['name']=$matchdata['series_name'];
                            $match['series'] = $series;


                            $gametype=array();
                            $gametype['id']=$matchdata['gametype_id'];
                            $gametype['name']=$matchdata['gametype_name'];
                            $match['gametype'] = $gametype;

                            
                            $team1=array();
                            $team1['id']=$matchdata['team_id_one'];
                            $team1['name']=$matchdata['team_name_one'];
                            $team1['sort_name']=$matchdata['team_sort_name_one'];
                            $team1['image']=!empty($matchdata['team_image_one']) ?$matchdata['team_image_one'] : NO_IMG_URL_TEAM;
                            
                            $match['team1'] = $team1;



                            $team2=array();
                            $team2['id']=$matchdata['team_id_two'];
                            $team2['name']=$matchdata['team_name_two'];
                            $team2['sort_name']=$matchdata['team_sort_name_two'];
                            $team2['image']=!empty($matchdata['team_image_two']) ? $matchdata['team_image_two'] : NO_IMG_URL_TEAM;
                            
                            $match['team2'] = $team2;


                            $team_ids=$matchdata['team_id_one'].",".$matchdata['team_id_two'];

                            $batsmans=array();
                            $bowlers=array();
                            $wicketkeapers=array();
                            $allrounders=array();

                            $teams_players=$this->getPlayersByMatchandTeam($matchdata['match_unique_id'],$team_ids);

                            foreach ($teams_players as $player_data) {
                                $position=$player_data['position'];
                                if(!empty($position)){
                                    $position=strtolower($position);
                                    if (strpos($position, 'wicketkeeper') !== false) {
                                        $wicketkeapers[]=$player_data;
                                        continue;
                                    }
                                    if (strpos($position, 'batsman') !== false) {
                                        $batsmans[]=$player_data;
                                        continue;
                                    }

                                    if (strpos($position, 'allrounder') !== false) {
                                        $allrounders[]=$player_data;
                                        continue;
                                    }

                                    if (strpos($position, 'bowler') !== false) {
                                        $bowlers[]=$player_data;
                                        continue;
                                    }


                                }
                                
                            }
                            
                            $match['batsmans'] = $batsmans;
                            $match['bowlers'] = $bowlers;
                            $match['wicketkeapers'] = $wicketkeapers;
                            $match['allrounders'] = $allrounders;
                            $match['team_settings'] = $this->get_team_settings($match['match_type']);
                    return $match;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_team_settings($intgametype=1){

          $query = "SELECT * from tbl_cricket_team_setting where 1 AND game_id= $intgametype order by id asc";

        $query_res = $this->conn->prepare($query);  
            

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    while ($playerdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                        
                        
                     $output[$playerdata['key']] =$playerdata['value'];                    
                        if($playerdata['key']=='MIN_WICKETKEEPER' || $playerdata['key']=='MIN_BATSMAN' || $playerdata['key']=='MIN_ALLROUNDER' || $playerdata['key']=='MIN_BOWLER') 
                        {
                              $output[$playerdata['key']."_FULL"] =$playerdata['full_label'];               $output[$playerdata['key']."_SHORT"] =$playerdata['short_label'];                    
                        }
                    }
                    return $output;
                }else{
                    return "NO_RECORD";
                    
                }
        }else{
               return "UNABLE_TO_PROCEED";
            
        }        
    }



    public function get_match_contest($match_id,$match_unique_id,$customer_id,$intMatchType) {
        
        
       
        $this->setGroupConcatLimit();

       /*$query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.order_pos, (SELECT GROUP_CONCAT(CONCAT(tcc.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from tbl_cricket_contests tcc LEFT JOIN tbl_cricket_contest_matches tccf ON (tccf.contest_id=tcc.id AND tccf.match_id=? AND tccf.status='A' AND tccf.is_deleted='N') where tcc.category_id= tccc.id AND tcc.status='A' AND tcc.is_deleted='N') as contest_data from tbl_cricket_contests tccs LEFT JOIN tbl_cricket_contest_categories tccc ON (tccs.category_id=tccc.id) where tccs.id IN (select contest_id from tbl_cricket_contest_matches tccm where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N') GROUP BY tccs.category_id ORDER BY order_pos ASC";*/


      
          
     
      
      
      
        

        $query = "SELECT tccc.id as cat_id,
                tccc.cash_bonus_used_type,
                tccc.cash_bonus_used_value,
                tccc.name,
                tccc.description,
                tccc.image,
                tccc.is_discounted,
                tccc.order_pos,
                ts.value as discount_image,
                ts.width as discount_image_width,
                ts.height as discount_image_height,
                (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccf.id),'----',tccf.slug,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees,'----',tccf.first_prize,'----',tccf.cash_bonus_used_type,'----',tccf.cash_bonus_used_value) SEPARATOR '--++--' ) from  tbl_cricket_contest_matches tccf where  tccf.match_id=? AND tccf.status='A' AND tccf.is_deleted='N' AND  tccf.category_id= tccc.id AND tccf.is_private='N' AND tccf.is_beat_the_expert='N') as contest_data,tccm.first_prize from tbl_cricket_contest_matches tccm  LEFT JOIN tbl_settings ts ON(ts.key='DISCOUNTED_IMAGE') LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id ) where  tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccc.is_private='N' AND tccc.is_beat_the_expert='N' GROUP BY tccm.category_id ORDER BY order_pos ASC";
       
       

        $query_res = $this->conn->prepare($query);          
        $query_res->bindParam(1, $customer_id);  
        $query_res->bindParam(2, $match_id);
        $query_res->bindParam(3, $match_id);   

        if($query_res->execute()){
                $output = array();
                $output_practice = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                            $m=0;
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['cash_bonus_used_type']=$contestdata['cash_bonus_used_type'];
                                    $contestCategory['cash_bonus_used_value']=$contestdata['cash_bonus_used_value'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['is_discounted']=$contestdata['is_discounted'];
                                    $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;

                                    if(empty($contestdata['discount_image'])){
                                        $contestCategory['discount_image']="";
                                        $contestCategory['discount_image_width']="0";
                                        $contestCategory['discount_image_height']="0";
                                    }else{
                                        $contestCategory['discount_image']=APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$contestdata['discount_image'];
                                        $contestCategory['discount_image_width']=$contestdata['discount_image_width'];
                                        $contestCategory['discount_image_height']=$contestdata['discount_image_height'];
                                    }

                                    $contests_array=explode("--++--",$contestdata['contest_data']);

                                    $j=0;
                                    $l=0;
                                    $contests=array();
                                    $practice=array();
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);

                                        if($per_contest[1]-$per_contest[9]<=0){
                                        	continue;
                                        }
                                        
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        
                                          $per_contest_s['prize_breakup']=$winnerBreakup;



                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                        
                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];
                                            }
                                        }
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);
                                        
                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];
                                        $per_contest_s['slug']=$per_contest[10];
                                        $per_contest_s['more_entry_fees']=$per_contest[11];
                                        $per_contest_s['multi_team_allowed']=$per_contest[12];
                                        $per_contest_s['actual_entry_fees']=$per_contest[13];
                                        $per_contest_s['first_prize']=$per_contest[14];


                                        $per_contest_s['discount_image']=$contestCategory['discount_image'];
                                        $per_contest_s['discount_image_width']=$contestCategory['discount_image_width'];
                                        $per_contest_s['discount_image_height']=$contestCategory['discount_image_height'];
                                        $per_contest_s['is_beat_the_expert']="N";

                                        $per_contest_s['cash_bonus_used_type']=$per_contest[15];
                                    	$per_contest_s['cash_bonus_used_value']=$per_contest[16];
                                        
                                        
                                        
                                        ///print_r($per_contest_s);
                                        if($per_contest_s['total_price']==0){
                                            $practice[$l]=$per_contest_s;
                                            $l++;
                                        }else{
                                            $contests[$j]=$per_contest_s;
                                            $j++;
                                        }
                                        
                                        

                                    }

                                    if(!empty($contests)){
                                        $contestCategory['contests']=$contests;
                                        $output[$i]=$contestCategory;
                                        $i++;
                                    }

                                    if(!empty($practice)){
                                        $contestCategory['contests']=$practice;
                                        $output[$i]=$contestCategory;
                                        $m++;

                                    }

                                    
                    }           
                       
                    $this->closeStatement($query_res);
                    $outputt=array();
                    $outputt['cash']=$output;
                    $outputt['practice']=$output_practice;
                     $outputt['team_settings'] = $this->get_team_settings($intMatchType);
                    return $outputt;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_match_beat_the_expert_contest($match_id,$match_unique_id,$customer_id) {
        
        
       
        $this->setGroupConcatLimit();

       /*$query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.order_pos, (SELECT GROUP_CONCAT(CONCAT(tcc.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from tbl_cricket_contests tcc LEFT JOIN tbl_cricket_contest_matches tccf ON (tccf.contest_id=tcc.id AND tccf.match_id=? AND tccf.status='A' AND tccf.is_deleted='N') where tcc.category_id= tccc.id AND tcc.status='A' AND tcc.is_deleted='N') as contest_data from tbl_cricket_contests tccs LEFT JOIN tbl_cricket_contest_categories tccc ON (tccs.category_id=tccc.id) where tccs.id IN (select contest_id from tbl_cricket_contest_matches tccm where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N') GROUP BY tccs.category_id ORDER BY order_pos ASC";*/


        $query = "SELECT tccc.id as cat_id,tccc.cash_bonus_used_type,tccc.cash_bonus_used_value,tccc.name,tccc.description,tccc.image,tccc.is_discounted,tccc.order_pos,ts.value as discount_image,ts.width as discount_image_width, ts.height as discount_image_height, (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccf.id),'----',tccf.slug,'----',tccf.entry_fee_multiplier,'----',tccf.max_entry_fees,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees) SEPARATOR '--++--' ) from  tbl_cricket_contest_matches tccf where  tccf.match_id=? AND tccf.status='A' AND tccf.is_deleted='N' AND  tccf.category_id= tccc.id AND is_beat_the_expert='Y') as contest_data from tbl_cricket_contest_matches tccm  LEFT JOIN tbl_settings ts ON(ts.key='DISCOUNTED_IMAGE')  LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id ) where  tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccc.is_beat_the_expert='Y' GROUP BY tccm.category_id ORDER BY order_pos ASC";
       
       

        $query_res = $this->conn->prepare($query);          
        $query_res->bindParam(1, $customer_id);  
        $query_res->bindParam(2, $match_id);
        $query_res->bindParam(3, $match_id);   

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                            
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['cash_bonus_used_type']=$contestdata['cash_bonus_used_type'];
                                    $contestCategory['cash_bonus_used_value']=$contestdata['cash_bonus_used_value'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['is_discounted']=$contestdata['is_discounted'];
                                    $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;


                                    if(empty($contestdata['discount_image'])){
                                        $contestCategory['discount_image']="";
                                        $contestCategory['discount_image_width']="0";
                                        $contestCategory['discount_image_height']="0";
                                    }else{
                                        $contestCategory['discount_image']=APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$contestdata['discount_image'];
                                        $contestCategory['discount_image_width']=$contestdata['discount_image_width'];
                                        $contestCategory['discount_image_height']=$contestdata['discount_image_height'];
                                    }

                                    $contests_array=explode("--++--",$contestdata['contest_data']);

                                    $j=0;
                                   
                                    $contests=array();
                                   
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);
                                        
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['entry_fees_suggest']=array();

                                        $amount_suggest=array();
                                        
                                            
                                            
                                        $amount_suggest[0]=$per_contest[3];
                                        $amount_suggest[1]=round($per_contest[12]*0.75);
                                        $amount_suggest[2]=$per_contest[12];
                                            
                                       
                                        $per_contest_s['entry_fees_suggest']=$amount_suggest;
                                        $per_contest_s['max_entry_fees']=$per_contest[12];

                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                        
                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];
                                            }
                                        }
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);
                                        
                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];

                                        $per_contest_s['slug']=$per_contest[10];
                                        $per_contest_s['entry_fee_multiplier']=$per_contest[11];
                                        $per_contest_s['more_entry_fees']=$per_contest[13];
                                        $per_contest_s['multi_team_allowed']=$per_contest[14];
                                        $per_contest_s['actual_entry_fees']=$per_contest[15];


                                        $per_contest_s['discount_image']=$contestCategory['discount_image'];
                                        $per_contest_s['discount_image_width']=$contestCategory['discount_image_width'];
                                        $per_contest_s['discount_image_height']=$contestCategory['discount_image_height'];
                                        $per_contest_s['is_beat_the_expert']="Y";


                                        $per_contest_s['cash_bonus_used_type']=$contestCategory['cash_bonus_used_type'];
                                    	$per_contest_s['cash_bonus_used_value']=$contestCategory['cash_bonus_used_value'];

                                        $contests[$j]=$per_contest_s;
                                        $j++;

                                    }

                                    
                                        $contestCategory['contests']=$contests;
                                        $output[$i]=$contestCategory;
                                        $i++;       
                    }           
                       
                    $this->closeStatement($query_res);
                    
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return array();
                    
                }
        }else{
            $this->closeStatement($query_res);
               return array();
            
        }        
       
    }

    public function get_match_contest_detail_mini($match_contest_id){

        $query="SELECT * from tbl_cricket_contest_matches tccm where  tccm.id=? AND tccm.status='A' AND tccm.is_deleted='N'";
        $query_res = $this->conn->prepare($query);

        $query_res->bindParam(1, $match_contest_id);
        if($query_res->execute()){
            return $query_res->fetch(PDO::FETCH_ASSOC);
        }

        return array();
    }
    
    
    public function get_match_contest_detail($match_contest_id,$customer_id) {
       
        $this->setGroupConcatLimit();
        

      /* $query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image, (SELECT GROUP_CONCAT(CONCAT(tcc.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE  tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from tbl_cricket_contests tcc LEFT JOIN tbl_cricket_contest_matches tccf ON (tccf.contest_id=tcc.id AND tccf.id=?) where tcc.category_id= tccc.id AND tcc.status='A' AND tcc.is_deleted='N') as contest_data from tbl_cricket_contests tccs LEFT JOIN tbl_cricket_contest_categories tccc ON (tccs.category_id=tccc.id) where tccs.id IN (select contest_id from tbl_cricket_contest_matches tccm where tccm.id=? AND tccm.status='A' AND tccm.is_deleted='N') GROUP BY tccs.category_id";*/


      $query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.is_discounted,ts.value as discount_image,ts.width as discount_image_width, ts.height as discount_image_height, (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE  tccc_contest.match_contest_id=tccf.id),'----',tccf.slug,'----',tccf.is_beat_the_expert,'----',tccf.entry_fee_multiplier,'----',tccf.max_entry_fees,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees,'----',tccf.first_prize ,'----',tccf.cash_bonus_used_type,'----',tccf.cash_bonus_used_value) SEPARATOR '--++--' )  from tbl_cricket_contest_matches tccf where tccf.id=? AND tccf.category_id= tccc.id AND tccf.status='A' AND tccf.is_deleted='N') as contest_data from tbl_cricket_contest_matches tccm  LEFT JOIN tbl_settings ts ON(ts.key='DISCOUNTED_IMAGE') LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id) where  tccm.id=? AND tccm.status='A' AND tccm.is_deleted='N' GROUP BY tccm.category_id";
       
 
        $query_res = $this->conn->prepare($query);  
         
         $query_res->bindParam(1, $customer_id); 
         $query_res->bindParam(2, $match_contest_id);
         $query_res->bindParam(3, $match_contest_id);
        

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                       
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['is_discounted']=$contestdata['is_discounted'];
                         $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;

                                    if(empty($contestdata['discount_image'])){
                                        $contestCategory['discount_image']="";
                                        $contestCategory['discount_image_width']="0";
                                        $contestCategory['discount_image_height']="0";
                                    }else{
                                        $contestCategory['discount_image']=APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$contestdata['discount_image'];
                                        $contestCategory['discount_image_width']=$contestdata['discount_image_width'];
                                        $contestCategory['discount_image_height']=$contestdata['discount_image_height'];
                                    }

                                    $contests_array=explode("--++--",$contestdata['contest_data']);
                                ///    print_r($contests_array);
                                    $j=0;
                                    $contests=array();
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);
                                        
                                     ///   print_r($per_contest);
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                                                                $per_contest_s['prize_breakup']= $this->get_contest_winner_breakup($per_contest[0]);

                                        
                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];
                                            }
                                        }
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);
                                        
                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];
                                        $per_contest_s['slug']=$per_contest[10];
                                        $per_contest_s['is_beat_the_expert']=$per_contest[11];
                                        $per_contest_s['more_entry_fees']=$per_contest[14];
                                        $per_contest_s['multi_team_allowed']=$per_contest[15];
                                        $per_contest_s['actual_entry_fees']=$per_contest[16];
                                        if($per_contest[11]=="Y"){

                                            $per_contest_s['entry_fee_multiplier']=$per_contest[12];
                                            $per_contest_s['max_entry_fees']=$per_contest[13];

                                            $per_contest_s['entry_fees_suggest']=array();

                                            $amount_suggest=array();
                                            $amount_suggest[0]=$per_contest[3];
                                            $amount_suggest[1]=round($per_contest[13]*0.75);
                                            $amount_suggest[2]=$per_contest[13];                                            
                                        
                                            $per_contest_s['entry_fees_suggest']=$amount_suggest;


                                        }


                                        $per_contest_s['discount_image']=$contestCategory['discount_image'];
                                        $per_contest_s['discount_image_width']=$contestCategory['discount_image_width'];
                                        $per_contest_s['discount_image_height']=$contestCategory['discount_image_height'];
                                                                                       $per_contest_s['first_prize']=$per_contest[17];
                                        $per_contest_s['cash_bonus_used_type']=$per_contest[18];
                                        $per_contest_s['cash_bonus_used_value']=$per_contest[19];
                                        //$winnerBreakup=$per_contest[5];
                                       
                                        
                                        //$per_contest_s['contest_json']=;
                                        $contests[$j]=$per_contest_s;
                                        
                                        $j++;

                                    }

                                    $contestCategory['contests']=$contests;

                                    $output[$i]=$contestCategory;
                                    $i++;
                    }           

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

            $this->closeStatement($query_res);

               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_match_private_contest_detail($slug,$customer_id,$match_unique_id='0') {
       
        $this->setGroupConcatLimit();
        $winnerBreakup=NULL;
        

      $innerQuery='';
      if(!empty($match_unique_id)){
        $innerQuery=" AND tccf.match_unique_id='$match_unique_id'";
      }

      $outerQuery='';
      if(!empty($match_unique_id)){
        $outerQuery=" AND tccm.match_unique_id='$match_unique_id'";
      }


      $query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccm.id,tccm.match_unique_id, (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE  tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE  tccc_contest.match_contest_id=tccf.id),'----',tccf.slug,'----',tccf.is_beat_the_expert,'----',tccf.entry_fee_multiplier,'----',tccf.max_entry_fees,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees) SEPARATOR '--++--' ) from  tbl_cricket_contest_matches tccf where tccf.slug=? $innerQuery AND tccf.category_id= tccc.id AND tccf.status='A' AND tccf.is_deleted='N') as contest_data from tbl_cricket_contest_matches tccm LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id) where  tccm.slug=? $outerQuery AND tccm.status='A' AND tccm.is_deleted='N' GROUP BY tccm.category_id";
       
       

        $query_res = $this->conn->prepare($query);  
         
         $query_res->bindParam(1, $customer_id); 
         $query_res->bindParam(2, $slug);
         $query_res->bindParam(3, $slug);

         $match=NULL;
        

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                                    $match_unique_id=$contestdata['match_unique_id'];

                                    $match=$this->get_match_data($match_unique_id);

                                    if($match['match_progress']!='F'){
                                        return 'INVALID_MATCH';
                                    }

                       
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;

                                    $contests_array=explode("--++--",$contestdata['contest_data']);

                                    $j=0;
                                    $contests=array();
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                      
                                        
                                        
                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];
                                            }
                                        }
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);
                                        
                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];

                                        if($per_contest_s['total_team_left']<=0){
                                            return 'CONTEST_FULL';
                                        }

                                        $per_contest_s['slug']=$per_contest[10];
                                        $per_contest_s['is_beat_the_expert']=$per_contest[11];
                                        $per_contest_s['more_entry_fees']=$per_contest[14];
                                        $per_contest_s['multi_team_allowed']=$per_contest[15];
                                        $per_contest_s['actual_entry_fees']=$per_contest[16];

                                        if($per_contest[11]=="Y"){
                                            $per_contest_s['entry_fee_multiplier']=$per_contest[12];
                                            $per_contest_s['max_entry_fees']=$per_contest[13];

                                            $per_contest_s['entry_fees_suggest']=array();

                                            $amount_suggest=array();
                                            $amount_suggest[0]=$per_contest[3];
                                            $amount_suggest[1]=round($per_contest[13]*0.75);
                                            $amount_suggest[2]=$per_contest[13];                                            
                                        
                                            $per_contest_s['entry_fees_suggest']=$amount_suggest;


                                        }
                                        
                                        //$winnerBreakup=$per_contest[5];
                                       
                                        
                                        //$per_contest_s['contest_json']=;
                                        $contests[$j]=$per_contest_s;
                                        
                                        $j++;

                                    }

                                    $contestCategory['contests']=$contests;

                                    $output[$i]=$contestCategory;
                                    $i++;
                    }           

                    $this->closeStatement($query_res);
                    $outputt=array();
                    $outputt['output']=$output;
                    
                    $outputt['match_detail']=$match;
                    $outputt['winner_breakup']=$winnerBreakup;
                    return $outputt;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

            $this->closeStatement($query_res);

               return "UNABLE_TO_PROCEED";
            
        }        
       
    }



    public function get_match_contest_share_detail($slugg,$customer_id) {
       
        
        $image=NO_IMG_URL;
        $slug=NULL;
        $message=NULL;
        

      


      $query = "SELECT tccm.slug,tccm.is_beat_the_expert,tcm.image,tcm.name from tbl_cricket_contest_matches tccm LEFT JOIN tbl_cricket_matches tcm ON (tccm.match_id=tcm.id) where  (tccm.slug=? OR tccm.id=?) AND tccm.status='A' AND tccm.is_deleted='N' AND tcm.status='A' AND tcm.is_deleted='N'";       

        $query_res = $this->conn->prepare($query);  
         
        
         $query_res->bindParam(1, $slugg);
         $query_res->bindParam(2, $slugg);
        

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                        $name=$contestdata['name'];
                        $slug=$contestdata['slug'];
                        $is_beat_the_expert=$contestdata['is_beat_the_expert'];                        
                        $image=!empty($contestdata['image']) ? MATCH_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;
                        $message="Youve been challenged!\n\nThink you can beat me? Join the contest on ".APP_NAME." for the ".$name." match and prove it!\n\nUse Contest Code ".$slug." & join the action NOW! Or download app from ".WEBSITE_URL_SHOW;
                    }           

                    $this->closeStatement($query_res);
                    $outputt=array();
                    $outputt['image']=$image;
                    $outputt['slug']=$slug;
                    $outputt['is_beat_the_expert']=$is_beat_the_expert;
                    $outputt['message']=$message;
                    return $outputt;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

            $this->closeStatement($query_res);

               return "UNABLE_TO_PROCEED";
            
        }        
       
    }
    
    public function get_match_contest_pdf($match_contest_id,$match_unique_id,$user_id){

        $query="SELECT IFNULL(pdf,'') as contest_pdf from tbl_cricket_contest_matches where id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_contest_id);
        if(!$query_res->execute()){
            $this->closeStatement($query_res);
            return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount()==0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        $contestData = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);
        if (empty($contestData['contest_pdf'])) {
            return "NO_RECORD";
        }
        $contest=array();
        $contest['match_contest_id']=$match_contest_id;
        $contest['contest_pdf']=APP_URL.'cpdfs/'.$contestData['contest_pdf'];
        return $contest;
    }

    public function setGroupConcatLimit(){
     /*   $query="SET GLOBAL  group_concat_max_len = 5555555555555555";
        $query_res = $this->conn->prepare($query);  
        $query_res->execute();
        $this->closeStatement($query_res);*/
    }


    public function get_customer_match_contest($match_id,$match_unique_id,$customer_id) {
        
        
       $this->setGroupConcatLimit();
       

       /*$query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.order_pos, (SELECT GROUP_CONCAT(CONCAT(tcc.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name,'---',tccc_contest.new_rank,'---',tccc_contest.new_points,'---',tccc_contest.win_amount,'---',tccc_contest.refund_amount,'---',tccc_contest.tax_amount) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE tccc_contest.match_unique_id=? AND tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_unique_id=? AND tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from tbl_cricket_contests tcc LEFT JOIN tbl_cricket_contest_matches tccf ON (tccf.contest_id=tcc.id AND tccf.match_id=?)  where tcc.category_id= tccc.id AND tcc.status='A' AND tcc.is_deleted='N' AND tccf.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) as contest_data from tbl_cricket_contests tccs LEFT JOIN tbl_cricket_contest_categories tccc ON (tccs.category_id=tccc.id) where tccs.id IN (select contest_id from tbl_cricket_contest_matches tccm where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccm.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) GROUP BY tccs.category_id ORDER BY order_pos ASC";*/



      $query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.is_discounted,tccc.order_pos,ts.value as discount_image,ts.width as discount_image_width, ts.height as discount_image_height, (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name,'---',tccc_contest.new_rank,'---',tccc_contest.new_points,'---',tccc_contest.win_amount,'---',tccc_contest.refund_amount,'---',tccc_contest.tax_amount,'---',tccc_contest.old_rank) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE tccc_contest.match_unique_id=? AND tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_unique_id=? AND tccc_contest.match_contest_id=tccf.id),'----',tccf.slug,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees,'----',tccf.first_prize) SEPARATOR '--++--' ) from tbl_cricket_contest_matches tccf where tccf.match_id=? AND tccf.category_id= tccc.id AND tccf.status='A' AND tccf.is_deleted='N' AND tccf.is_beat_the_expert='N' AND tccf.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) as contest_data from tbl_cricket_contest_matches tccm LEFT JOIN tbl_settings ts ON(ts.key='DISCOUNTED_IMAGE') LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id) where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccm.is_beat_the_expert='N' AND tccm.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?) GROUP BY tccm.category_id ORDER BY order_pos ASC";
       
       

        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $match_unique_id);  
        $query_res->bindParam(2, $customer_id); 
        $query_res->bindParam(3, $match_unique_id);         
        $query_res->bindParam(4, $match_id);
        $query_res->bindParam(5, $match_unique_id);
        $query_res->bindParam(6, $customer_id);   

        $query_res->bindParam(7, $match_id);
        $query_res->bindParam(8, $match_unique_id);
        $query_res->bindParam(9, $customer_id);   


        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['is_discounted']=$contestdata['is_discounted'];
                                    $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;

                                    if(empty($contestdata['discount_image'])){
                                        $contestCategory['discount_image']="";
                                        $contestCategory['discount_image_width']="0";
                                        $contestCategory['discount_image_height']="0";
                                    }else{
                                        $contestCategory['discount_image']=APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$contestdata['discount_image'];
                                        $contestCategory['discount_image_width']=$contestdata['discount_image_width'];
                                        $contestCategory['discount_image_height']=$contestdata['discount_image_height'];
                                    }

                                    $contests_array=explode("--++--",$contestdata['contest_data']);

                                    $j=0;
                                    $contests=array();
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                       // $per_contest_s['joined_teams']=(($per_contest[8]=="0")?"":$per_contest[8]);

                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        $my_teams=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                            $k=0;
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];

                                                $my_teams[$k]['team_id']=$per_team[0];
                                                $my_teams[$k]['team_name']=$per_team[1];
                                                $my_teams[$k]['new_rank']=$per_team[2];
                                                $my_teams[$k]['total_points']=$per_team[3];
                                                $my_teams[$k]['win_amount']=$per_team[4]+$per_team[6];
                                                $my_teams[$k]['refund_amount']=$per_team[5];
                                                $my_teams[$k]['tax_amount']=$per_team[6];
                                                $my_teams[$k]['old_rank']=$per_team[7];


                                                $k++;
                                            }
                                        }
                                        $per_contest_s['myteams']=$my_teams;
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);

                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];
                                        $per_contest_s['slug']=$per_contest[10];
                                        $per_contest_s['more_entry_fees']=$per_contest[11];
                                        $per_contest_s['multi_team_allowed']=$per_contest[12];
                                        $per_contest_s['actual_entry_fees']=$per_contest[13];
                                        $per_contest_s['first_prize']=$per_contest[14];

                                        $per_contest_s['discount_image']=$contestCategory['discount_image'];
                                        $per_contest_s['discount_image_width']=$contestCategory['discount_image_width'];
                                        $per_contest_s['discount_image_height']=$contestCategory['discount_image_height'];

                                        
                                        //$winnerBreakup=$per_contest[5];
                                       
                                        
                                        //$per_contest_s['contest_json']=;
                                        $contests[$j]=$per_contest_s;
                                        
                                        $j++;

                                    }

                                    $contestCategory['contests']=$contests;

                                    $output[$i]=$contestCategory;
                                    $i++;
                    }

                    $this->closeStatement($query_res);
                       
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_customer_match_contest_beat_the_expret($match_id,$match_unique_id,$customer_id) {
        
        
       $this->setGroupConcatLimit();
       

       /*$query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.order_pos, (SELECT GROUP_CONCAT(CONCAT(tcc.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name,'---',tccc_contest.new_rank,'---',tccc_contest.new_points,'---',tccc_contest.win_amount,'---',tccc_contest.refund_amount,'---',tccc_contest.tax_amount) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE tccc_contest.match_unique_id=? AND tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_unique_id=? AND tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from tbl_cricket_contests tcc LEFT JOIN tbl_cricket_contest_matches tccf ON (tccf.contest_id=tcc.id AND tccf.match_id=?)  where tcc.category_id= tccc.id AND tcc.status='A' AND tcc.is_deleted='N' AND tccf.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) as contest_data from tbl_cricket_contests tccs LEFT JOIN tbl_cricket_contest_categories tccc ON (tccs.category_id=tccc.id) where tccs.id IN (select contest_id from tbl_cricket_contest_matches tccm where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccm.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) GROUP BY tccs.category_id ORDER BY order_pos ASC";*/



      $query = "SELECT tccc.id as cat_id,tccc.name,tccc.description,tccc.image,tccc.is_discounted,tccc.order_pos,ts.value as discount_image,ts.width as discount_image_width, ts.height as discount_image_height, (SELECT GROUP_CONCAT(CONCAT(tccf.id,'----',tccf.total_team,'----',tccf.total_price,'----',tccf.entry_fees,'----',tccf.per_user_team_allowed, '----',tccf.contest_json,'----',tccf.id,'----',tccf.confirm_win,'----',IFNULL((SELECT GROUP_CONCAT(CONCAT(tccc_contest.customer_team_id,'---',tcct_co.name,'---',tccc_contest.new_rank,'---',tccc_contest.new_points,'---',tccc_contest.win_amount,'---',tccc_contest.refund_amount,'---',tccc_contest.tax_amount,'---',tccc_contest.entry_fees) SEPARATOR '--+++--') FROM tbl_cricket_customer_contests tccc_contest LEFT JOIN tbl_cricket_customer_teams tcct_co ON(tcct_co.id=tccc_contest.customer_team_id) WHERE tccc_contest.match_unique_id=? AND tccc_contest.customer_id=? AND tccc_contest.match_contest_id=tccf.id),0),'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_unique_id=? AND tccc_contest.match_contest_id=tccf.id),'----',tccf.entry_fee_multiplier,'----',tccf.slug,'----',tccf.max_entry_fees,'----',tccf.more_entry_fees,'----',tccf.multi_team_allowed,'----',tccf.actual_entry_fees) SEPARATOR '--++--' ) from tbl_cricket_contest_matches tccf where tccf.match_id=? AND tccf.category_id= tccc.id AND tccf.status='A' AND tccf.is_deleted='N' AND tccf.is_beat_the_expert='Y' AND tccf.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?)) as contest_data from tbl_cricket_contest_matches tccm LEFT JOIN tbl_settings ts ON(ts.key='DISCOUNTED_IMAGE') LEFT JOIN tbl_cricket_contest_categories tccc ON (tccm.category_id=tccc.id) where tccm.match_id=? AND tccm.status='A' AND tccm.is_deleted='N' AND tccm.is_beat_the_expert='Y' AND tccm.id IN (SELECT DISTINCT tccc_custo.match_contest_id from tbl_cricket_customer_contests tccc_custo where tccc_custo.match_unique_id=? AND tccc_custo.customer_id=?) GROUP BY tccm.category_id ORDER BY order_pos ASC";
       
       

        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $match_unique_id);  
        $query_res->bindParam(2, $customer_id); 
        $query_res->bindParam(3, $match_unique_id);         
        $query_res->bindParam(4, $match_id);
        $query_res->bindParam(5, $match_unique_id);
        $query_res->bindParam(6, $customer_id);   

        $query_res->bindParam(7, $match_id);
        $query_res->bindParam(8, $match_unique_id);
        $query_res->bindParam(9, $customer_id);   


        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                            $i=0;
                      while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                                    $contestCategory=array();

                                    $contestCategory['id']=$contestdata['cat_id'];
                                    $contestCategory['name']=$contestdata['name'];
                                    $contestCategory['description']=$contestdata['description'];
                                    $contestCategory['is_discounted']=$contestdata['is_discounted'];
                                    $contestCategory['image']=!empty($contestdata['image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$contestdata['image'] : NO_IMG_URL;

                                    if(empty($contestdata['discount_image'])){
                                        $contestCategory['discount_image']="";
                                        $contestCategory['discount_image_width']="0";
                                        $contestCategory['discount_image_height']="0";
                                    }else{
                                        $contestCategory['discount_image']=APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$contestdata['discount_image'];
                                        $contestCategory['discount_image_width']=$contestdata['discount_image_width'];
                                        $contestCategory['discount_image_height']=$contestdata['discount_image_height'];
                                    }

                                    $contests_array=explode("--++--",$contestdata['contest_data']);

                                    $j=0;
                                    $contests=array();
                                    foreach($contests_array as $contests_array_s){

                                        $per_contest=explode("----", $contests_array_s);
                                        $per_contest_s=array();
                                        $per_contest_s['id']=$per_contest[0];
                                        $per_contest_s['total_team']=$per_contest[1];
                                        $per_contest_s['total_price']=$per_contest[2];
                                        $per_contest_s['entry_fees']=$per_contest[3];
                                        $per_contest_s['per_user_team_allowed']=$per_contest[4];
                                        $winnerBreakup=json_decode($per_contest[5],true);
                                        $total_winners=end($winnerBreakup['per_max_p']);
                                        $per_contest_s['total_winners']=$total_winners;
                                        $per_contest_s['match_contest_id']=$per_contest[6];
                                        $per_contest_s['confirm_win']=$per_contest[7];
                                       // $per_contest_s['joined_teams']=(($per_contest[8]=="0")?"":$per_contest[8]);

                                        $joined_teams=array();
                                        $joined_teams_name=array();
                                        $my_teams=array();
                                        if($per_contest[8]!="0"){
                                            $joined_teams_array=explode("--+++--",$per_contest[8]);
                                            $k=0;
                                             foreach($joined_teams_array as $joined_teams_array_s){
                                                $per_team=explode("---", $joined_teams_array_s);
                                                $joined_teams[]=$per_team[0];
                                                $joined_teams_name[]=$per_team[1];

                                                $my_teams[$k]['team_id']=$per_team[0];
                                                $my_teams[$k]['team_name']=$per_team[1];
                                                $my_teams[$k]['new_rank']=$per_team[2];
                                                $my_teams[$k]['total_points']=$per_team[3];
                                                $my_teams[$k]['win_amount']=$per_team[4]+$per_team[6];
                                                $my_teams[$k]['refund_amount']=$per_team[5];
                                                $my_teams[$k]['tax_amount']=$per_team[6];
                                                $my_teams[$k]['user_entry_fees']=$per_team[7];


                                                $k++;
                                            }
                                        }
                                        $per_contest_s['myteams']=$my_teams;
                                        $per_contest_s['joined_teams']=implode(',',$joined_teams);
                                        $per_contest_s['joined_teams_name']=implode(',',$joined_teams_name);

                                        $per_contest_s['total_team_left']=$per_contest[1]-$per_contest[9];
                                        $per_contest_s['entry_fee_multiplier']=$per_contest[10];
                                        $per_contest_s['slug']=$per_contest[11];
                                        $per_contest_s['max_entry_fees']=$per_contest[12];
                                        $per_contest_s['more_entry_fees']=$per_contest[13];
                                        $per_contest_s['multi_team_allowed']=$per_contest[14];
                                        $per_contest_s['actual_entry_fees']=$per_contest[15];
                                        $per_contest_s['entry_fees_suggest']=array();

                                        $amount_suggest=array();
                                        
                                            
                                            
                                            $amount_suggest[0]=$per_contest[3];
                                            $amount_suggest[1]=round($per_contest[12]*0.75);
                                            $amount_suggest[2]=$per_contest[12];
                                            
                                       
                                        $per_contest_s['entry_fees_suggest']=$amount_suggest;


                                        $per_contest_s['discount_image']=$contestCategory['discount_image'];
                                        $per_contest_s['discount_image_width']=$contestCategory['discount_image_width'];
                                        $per_contest_s['discount_image_height']=$contestCategory['discount_image_height'];

                                        
                                        //$winnerBreakup=$per_contest[5];
                                       
                                        
                                        //$per_contest_s['contest_json']=;
                                        $contests[$j]=$per_contest_s;
                                        
                                        $j++;

                                    }

                                    $contestCategory['contests']=$contests;

                                    $output[$i]=$contestCategory;
                                    $i++;
                    }

                    $this->closeStatement($query_res);
                       
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return array();
                    
                }
        }else{

            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }



    public function get_contest_winner_breakup($contest_id) {
        $query = "SELECT id, contest_json from tbl_cricket_contest_matches where status='A' AND is_deleted='N' AND id=?";
        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $contest_id);  
 
        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $contestdata = $query_res->fetch(PDO::FETCH_ASSOC); 
                    $this->closeStatement($query_res);

                    $output['winner_breakup'] =json_decode($contestdata['contest_json'],true);

                    $taxData=$this->get_total_tax_percent();
                    $totalTax=$taxData['total_tax'];

                    $WINNING_BREAKUP_MESSAGE="Note: The actual prize money may be different than the prize money mentionaed above if there is a tie for any of the winning positions. Check FAQs for further details. As per government regulations, a tax of ".$totalTax."% will be deducted if an individual wins more than Rs. 10,000";


                    $output['winner_breakup_message'] =$WINNING_BREAKUP_MESSAGE;
                            
                     
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }



    public function get_player_statics($player_id) {

       $query = "SELECT tcp.*,tc.name as country_name from tbl_cricket_players tcp LEFT JOIN tbl_countries tc ON(tcp.country_id=tc.id) where tcp.status='A' AND tcp.is_deleted='N' AND uniqueid=?";

        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $player_id);      

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $playerdata = $query_res->fetch(PDO::FETCH_ASSOC);
                    $this->closeStatement($query_res);

                     $output['id'] =$playerdata['id'];
                     $output['name'] =$playerdata['name'];
                     $output['player_uniqueid'] =$playerdata['uniqueid'];
                     $output['bat_type'] =$playerdata['bets'];
                     $output['bowl_type'] =$playerdata['bowls'];
                     $output['country'] =$playerdata['country_name'];
                     $output['dob'] =(!empty($playerdata['dob']))? date_format( date_create($playerdata['dob']) ,"d-m-Y" ):"";

                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }
    
    public function checkTeamAlreadyExist($customer_id,$match_unique_id, $players){
        
        function sortByPlayerId($a, $b) {
            if($b['player_id'] < $a['player_id']){
                return -1;
            }else if($b['player_id'] > $a['player_id']){
                return 1;
            }
            else{
                return 0;
            }
        }

        usort($players, 'sortByPlayerId');
        
        
        $newPlayerData="";
        foreach($players as $player){
                    $player_unique_id=$player['player_id'];
                    $player_multiplier=$player['player_multiplier'];
                    if(empty($newPlayerData)){
                        $newPlayerData=$player_unique_id.'----'.$player_multiplier;
                    }else{
                        $newPlayerData.='++'.$player_unique_id.'----'.$player_multiplier;
                    }
        }
        
        $query="SELECT tcct.id,(SELECT GROUP_CONCAT(CONCAT(tcctp.player_unique_id,'----',tcctp.multiplier) ORDER BY tcctp.player_unique_id DESC SEPARATOR '++') FROM tbl_cricket_customer_team_plyers as tcctp WHERE tcctp.customer_team_id=tcct.id )as player_data FROM `tbl_cricket_customer_teams` as tcct where tcct.customer_id='$customer_id' AND tcct.match_unique_id='$match_unique_id'";
        
        $queryFinal="SELECT d.id as team_id, d.player_data from ($query) as d where d.player_data='$newPlayerData' limit 1";

        $queryFinal_Res = $this->conn->prepare($queryFinal);
        $queryFinal_Res->execute();
        $num_rows =$queryFinal_Res->rowCount();
        $team_id=0;
        if($num_rows>0){
            $teamsdata = $queryFinal_Res->fetch(PDO::FETCH_ASSOC);
            $team_id=$teamsdata['team_id'];
        }
        $this->closeStatement($queryFinal_Res);
        return $team_id;
    }

    public function create_customer_team($customer_id,$match_unique_id, $players,$customer_team_name,$team_name,$fromadmin=0){

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        
        if(empty($get_match_data)){
            return "NO_MATCH_FOUND";
        }
        if($get_match_data['match_progress']!='F'){
            return "INVALID_MATCH";
        }

        $customer_teams=$this->get_match_customer_team_count_by_match_unique_id($customer_id,$match_unique_id);

        if($customer_teams >= $get_match_data['match_limit']){
            return "TEAM_CREATION_LIMIT_EXEED";
        }
        
        
        

            $alreadyExist=$this->checkTeamAlreadyExist($customer_id,$match_unique_id, $players);
            if($alreadyExist>0){
                return "TEAM_ALREADY_EXIST";
            }

        if($fromadmin==1){
            $isExist=$this->isTeamNameExists($customer_team_name, $customer_id);
            if($isExist){
                return "CUSTOMER_TEAM_NAME_ALREADY_EXIST";
            }
            $isExist=$this->isCustomerTeamNameExistsInCustomerTeams($customer_team_name,$match_unique_id, $customer_id);
            if($isExist){
                return "CUSTOMER_TEAM_NAME_ALREADY_EXIST";
            }

            $isExist=$this->isMoreNameExistsInCustomerTeams($customer_team_name,$match_unique_id,$team_name, $customer_id);
            if($isExist){
                return "TEAM_NAME_ALREADY_EXIST";
            }

        }
        

        $current_time = time();                        
        $create_team_query = "INSERT INTO tbl_cricket_customer_teams SET customer_id = ?, match_unique_id=?, customer_team_name=?, more_name=?, created=?, updated=?, name=(IFNULL((SELECT name from (select name,match_unique_id,customer_id,id from tbl_cricket_customer_teams) as tcct where tcct.match_unique_id='$match_unique_id' AND tcct.customer_id='$customer_id' ORDER BY tcct.id desc limit 1),0)+1)";
        $create_team_query_res = $this->conn->prepare($create_team_query);
        $create_team_query_res->bindParam(1, $customer_id);
        $create_team_query_res->bindParam(2, $match_unique_id);
        $create_team_query_res->bindParam(3, $customer_team_name);
        $create_team_query_res->bindParam(4, $team_name);
        $create_team_query_res->bindParam(5, $current_time);
        $create_team_query_res->bindParam(6, $current_time);

         if ($create_team_query_res->execute()) {
                $customer_team_id=$this->conn->lastInsertId();

                $this->closeStatement($create_team_query_res);


                    // $update_team_name_query = "UPDATE tbl_cricket_customer_teams SET name=(IFNULL((SELECT name from (select name,match_unique_id,customer_id,id from tbl_cricket_customer_teams) as tcct where tcct.match_unique_id='$match_unique_id' AND tcct.customer_id='$customer_id' ORDER BY tcct.id desc limit 1,1),0)+1) WHERE id='$customer_team_id'";
                    // $update_team_name_query_res  = $this->conn->prepare($update_team_name_query);
                    // $update_team_name_query_res->execute();

            
                
                $query = "INSERT INTO tbl_cricket_customer_team_plyers (customer_id, match_unique_id, customer_team_id, team_id, player_unique_id, position, multiplier, created)";
                $i=0;
                $isvalidQuery=false;
                $selectedPlayers=array();
                foreach($players as $player){
                    $player_team_id=$player['team_id'];
                    $player_unique_id=$player['player_id'];
                    $selectedPlayers[]=$player_unique_id;
                    $player_pos=$player['player_pos'];
                    $player_multiplier=$player['player_multiplier'];
                        $isvalidQuery=true;
                        if($i!=0){
                            $query.=" UNION ";
                        }                    
                        $query .= "SELECT $customer_id, $match_unique_id, $customer_team_id, $player_team_id, $player_unique_id, $player_pos, $player_multiplier, $current_time";
                    $i++;
                }
                
                if($isvalidQuery){
                    $query  = $this->conn->prepare($query);
                    if($query->execute()) {
                        $this->closeStatement($query);
                        $team=array();
                        $team['id']=$customer_team_id;

                        $this->updatePlayerSelectedByCount($match_unique_id,$selectedPlayers);

                        return $team;
                    }else{
                        $this->closeStatement($query);
                         return "UNABLE_TO_PROCEED";
                    }
                }else{
                     return "UNABLE_TO_PROCEED";
                }
                
         }else{
            $this->closeStatement($create_team_query_res);
            return "UNABLE_TO_PROCEED";
         }
  
    }




    public function update_customer_team($customer_id,$match_unique_id,$customer_team_id,$players){

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        
        if(empty($get_match_data)){
            return "NO_MATCH_FOUND";
        }
        
        if($get_match_data['match_progress']!='F'){
            return "INVALID_MATCH";
        }
         
        $alreadyExist=$this->checkTeamAlreadyExist($customer_id,$match_unique_id, $players);
        if($alreadyExist>0){

            if($alreadyExist!=$customer_team_id){
                return "TEAM_ALREADY_EXIST";
            }

            $team_data=$this->get_customer_match_teams($customer_id,$match_unique_id,$customer_team_id);

            if(empty($team_data)){
                 return "NO_RECORD";
            }

            return $team_data[0];
        }

        

        $current_time = time();                        
        $delete_query = "DELETE FROM tbl_cricket_customer_team_plyers where  customer_team_id=?";
        $delete_query_res = $this->conn->prepare($delete_query);
        $delete_query_res->bindParam(1, $customer_team_id);
       

         if ($delete_query_res->execute()) {
            $this->closeStatement($delete_query_res);

                $query = "INSERT INTO tbl_cricket_customer_team_plyers (customer_id, match_unique_id, customer_team_id, team_id, player_unique_id, position, multiplier, created)";
                $i=0;
                $isvalidQuery=false;
                $selectedPlayers=array();
                foreach($players as $player){
                    $player_team_id=$player['team_id'];
                    $player_unique_id=$player['player_id'];
                    $selectedPlayers[]=$player_unique_id;

                    $player_pos=$player['player_pos'];
                    $player_multiplier=$player['player_multiplier'];
                        $isvalidQuery=true;
                        if($i!=0){
                            $query.=" UNION ";
                        }                    
                        $query .= "SELECT $customer_id, $match_unique_id, $customer_team_id, $player_team_id, $player_unique_id, $player_pos, $player_multiplier, $current_time";
                    $i++;
                }
                
                if($isvalidQuery){
                    $query  = $this->conn->prepare($query);
                    if($query->execute()) {
                        $this->closeStatement($query);

                        $this->updatePlayerSelectedByCount($match_unique_id,$selectedPlayers);

                        $team_data=$this->get_customer_match_teams($customer_id,$match_unique_id,$customer_team_id);

                        if(empty($team_data)){
                             return "NO_RECORD";
                        }

                        return $team_data[0];


                    }else{
                        $this->closeStatement($query);
                         return "UNABLE_TO_PROCEED";
                    }
                }else{
                     return "UNABLE_TO_PROCEED";
                }
                
         }else{
            $this->closeStatement($delete_query_res);
            return "UNABLE_TO_PROCEED";
         }
  
    }

    function get_customer_match_teams_not_joined($customer_id,$match_unique_id,$match_contest_id=0){

         $this->setGroupConcatLimit();
           $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id='$match_unique_id')";
          $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);

        $match_ids=$array_series['match_ids'];

        
        $teamsQuery="select team_1_id,team_2_id, playing_squad_updated from tbl_cricket_matches where  unique_id='$match_unique_id'";
        $teamsQuery_res = $this->conn->prepare($teamsQuery);
        $teamsQuery_res->execute();
        $array_teams = $teamsQuery_res->fetch(PDO::FETCH_ASSOC);

        $team1Id=$array_teams['team_1_id'];
        $team2Id=$array_teams['team_2_id'];
        $playing_squad_updated=$array_teams['playing_squad_updated'];
       // echo "SELECT team_id from tbl_cricket_contest_matches where id='$match_contest_id'";
          $query="SELECT tcct.id,tcct.name as team_name, tct_one.name as team_name_one, tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two, tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two FROM `tbl_cricket_customer_teams` tcct LEFT JOIN tbl_cricket_teams tct_one ON ($team1Id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON ($team2Id=tct_two.id) WHERE tcct.customer_id=? AND tcct.match_unique_id= ? AND tcct.id NOT IN(SELECT customer_team_id from tbl_cricket_customer_contests where match_contest_id='$match_contest_id' AND customer_id=$customer_id)";
        
        if(!empty($customer_team_id)){
            $query.=" AND tcct.id= ?";
        }
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_unique_id);
        if(!empty($customer_team_id)){
            $query_res->bindParam(3, $customer_team_id);
        }
        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        $output = array();
        $i=0;
        while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                     $team=array();
                    $team['id']=$teamsdata['id'];
                    $team['name']=$teamsdata['team_name'];
                    
                    $team1=array();
                    $team1['id']=$teamsdata['team_id_one'];
                    $team1['name']=$teamsdata['team_name_one'];
                    $team1['sort_name']=$teamsdata['team_sort_name_one'];
                    $team1['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
                    $team['team1'] = $team1;
                    
                    $team2=array();
                    $team2['id']=$teamsdata['team_id_two'];
                    $team2['name']=$teamsdata['team_name_two'];
                    $team2['sort_name']=$teamsdata['team_sort_name_two'];
                    $team2['image']=!empty($teamsdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_two'] : NO_IMG_URL_TEAM;
                    $team['team2'] = $team2;
                    
                $sqlPlayerData = 'SELECT * from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcctp.customer_team_id ='.$teamsdata['id'];
                    $resData = $this->conn->prepare($sqlPlayerData);
                    $resData->execute();
                    $players_array  =array();


                    $j=0;
                    $batsmans=array();
                    $bowlers=array();
                    $wicketkeapers=array();
                    $allrounders=array();
                    $captain=NULL;
                    $vise_captain=NULL;
                    $team_total_points=0;
                    $team1count =0;
                     $team2count =0;
                     $allplayer=[];
                     $per_player =array();
                   while($players_array_s = $resData->fetch(PDO::FETCH_ASSOC)){ 

                        $player=array();
                        $player['player_id']=$players_array_s['player_unique_id'];
                        $player['player_pos']=$players_array_s['position'];
                        $player['player_multiplier']=$players_array_s['multiplier'];
                        $player['image']=!empty($players_array_s['image']) ? PLAYER_IMAGE_THUMB_URL.$players_array_s['image'] : NO_IMG_URL_PLAYER;
                        $player['name']=$players_array_s['short_name'];
                        $player['team_id']=$players_array_s['team_id'];
                        $player['points']=$players_array_s['points'];
                        $player['bat_type']=$players_array_s['bets'];
                        $player['bowl_type']=$players_array_s['bowls'];
                        $player['dob']=(!empty($players_array_s['dob']))? date_format( date_create(rtrim($players_array_s['dob'],',')) ,"d-m-Y" ):"";
                        $player['country']=isset($players_array_s['country_id'])?$players_array_s['country_id']:'';
                        $player['credits']=isset($players_array_s['credits'])?$players_array_s['credits']:'';;
                        $player['is_in_playing_squad']=isset($players_array_s['is_in_playing_squad'])?$players_array_s['is_in_playing_squad']:''; 
                        $player['playing_squad_updated']=$playing_squad_updated;
                        $player['position']=isset($players_array_s['playing_role'])?$players_array_s['playing_role']:''; 
                        $player['total_points']=isset($players_array_s['points'])?$players_array_s['points']:'';  
                        $team_total_points+=($player['player_multiplier']*$player['points']);

                          if($player['team_id']==$team1Id)
                          {
                              $team1count++;
                              
                          }
                        if($player['team_id']==$team2Id)
                          {
                              $team2count++;
                              
                          }
                          
                          $allplayer[] = $player;
                        $position=$player['position'];
                        if(!empty($position)){

                            if($player['player_multiplier']==2){
                              $captain  =$player;
                            }else if($player['player_multiplier']==1.5){

                             $vise_captain=$player;
                            }
                            $position=strtolower($position);
                            if (strpos($position, 'wicketkeeper') !== false) {
                                $wicketkeapers[]=$player;
                                continue;
                            }
                            if (strpos($position, 'batsman') !== false) {
                                $batsmans[]=$player;
                                continue;
                            }

                            if (strpos($position, 'allrounder') !== false) {
                                $allrounders[]=$player;
                                continue;
                            }

                            if (strpos($position, 'bowler') !== false) {
                                $bowlers[]=$player;
                                continue;
                            }


                        }



                        
                       
                        $j++;

                    }

                   
                    $team['team_total_points'] = $team_total_points;
                    $team['captain'] = $captain;
                    $team['vise_captain'] = $vise_captain;
                    

                    $team['team1count'] = $team1count;
                    $team['team2count'] = $team2count;
                    $team['batsmans'] = $batsmans;
                    $team['bowlers'] = $bowlers;
                    $team['wicketkeapers'] = $wicketkeapers;
                    $team['allrounders'] = $allrounders;
                    $team['allplayer'] = $allplayer;


                    $output[$i]=$team;
                    $i++;
         }
        /*$output['joined_teams'] = array();
        if($contest_id>0){
            $query1 = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND match_contest_id=?";
            $query_res1 = $this->conn->prepare($query1);
            $query_res1->bindParam(1, $customer_id);
            $query_res1->bindParam(2, $contest_id);
            while($joineddata = $query_res1->fetch(PDO::FETCH_ASSOC)){
                $output['joined_teams'][] = $joineddata;
            }
        }*/

        $this->closeStatement($query_res);
        return $output;
    }

     public function get_customer_match_teams($customer_id,$match_unique_id,$customer_team_id=0,$contest_id=0){

        $this->setGroupConcatLimit();
           $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id='$match_unique_id')";
          $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);

        $match_ids=$array_series['match_ids'];

        
        $teamsQuery="select team_1_id,team_2_id, playing_squad_updated from tbl_cricket_matches where  unique_id='$match_unique_id'";
        $teamsQuery_res = $this->conn->prepare($teamsQuery);
        $teamsQuery_res->execute();
        $array_teams = $teamsQuery_res->fetch(PDO::FETCH_ASSOC);

        $team1Id=$array_teams['team_1_id'];
        $team2Id=$array_teams['team_2_id'];
        $playing_squad_updated=$array_teams['playing_squad_updated'];
        
          $query="SELECT tcct.id,tcct.name as team_name, tct_one.name as team_name_one, tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two, tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two FROM `tbl_cricket_customer_teams` tcct LEFT JOIN tbl_cricket_teams tct_one ON ($team1Id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON ($team2Id=tct_two.id) WHERE tcct.customer_id=? AND tcct.match_unique_id= ?";
        
        if(!empty($customer_team_id)){
            $query.=" AND tcct.id= ?";
        }
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_unique_id);
        if(!empty($customer_team_id)){
            $query_res->bindParam(3, $customer_team_id);
        }
        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        $output = array();
        $i=0;
        while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                    $team=array();

                    $team['id']=$teamsdata['id'];
                    $team['name']=$teamsdata['team_name'];
                    
                    $team1=array();
                    $team1['id']=$teamsdata['team_id_one'];
                    $team1['name']=$teamsdata['team_name_one'];
                    $team1['sort_name']=$teamsdata['team_sort_name_one'];
                    $team1['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
                    $team['team1'] = $team1;
                    
                    $team2=array();
                    $team2['id']=$teamsdata['team_id_two'];
                    $team2['name']=$teamsdata['team_name_two'];
                    $team2['sort_name']=$teamsdata['team_sort_name_two'];
                    $team2['image']=!empty($teamsdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_two'] : NO_IMG_URL_TEAM;
                    $team['team2'] = $team2;
                    
                $sqlPlayerData = 'SELECT * from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcctp.customer_team_id ='.$teamsdata['id'];
                    $resData = $this->conn->prepare($sqlPlayerData);
                    $resData->execute();
                    $players_array  =array();


                    $j=0;
                    $batsmans=array();
                    $bowlers=array();
                    $wicketkeapers=array();
                    $allrounders=array();
                    $captain=NULL;
                    $vise_captain=NULL;
                    $team_total_points=0;
                    $team1count =0;
                     $team2count =0;
                     $allplayer=[];
                     $per_player =array();
                   while($players_array_s = $resData->fetch(PDO::FETCH_ASSOC)){ 

                        $player=array();
                        $player['player_id']=$players_array_s['player_unique_id'];
                        $player['player_pos']=$players_array_s['position'];
                        $player['player_multiplier']=$players_array_s['multiplier'];
                        $player['image']=!empty($players_array_s['image']) ? PLAYER_IMAGE_THUMB_URL.$players_array_s['image'] : NO_IMG_URL_PLAYER;
                        $player['name']=$players_array_s['short_name'];
                        $player['team_id']=$players_array_s['team_id'];
                        $player['points']=$players_array_s['points'];
                        $player['bat_type']=$players_array_s['bets'];
                        $player['bowl_type']=$players_array_s['bowls'];
                        $player['dob']=(!empty($players_array_s['dob']))? date_format( date_create(rtrim($players_array_s['dob'],',')) ,"d-m-Y" ):"";
                        $player['country']=isset($players_array_s['country_id'])?$players_array_s['country_id']:'';
                        $player['credits']=isset($players_array_s['credits'])?$players_array_s['credits']:'';;
                        $player['is_in_playing_squad']=isset($players_array_s['is_in_playing_squad'])?$players_array_s['is_in_playing_squad']:''; 
                        $player['playing_squad_updated']=$playing_squad_updated;
                        $player['position']=isset($players_array_s['playing_role'])?$players_array_s['playing_role']:''; 
                        $player['total_points']=isset($players_array_s['points'])?$players_array_s['points']:'';  
                        $team_total_points+=($player['player_multiplier']*$player['points']);

                          if($player['team_id']==$team1Id)
                          {
                              $team1count++;
                              
                          }
                        if($player['team_id']==$team2Id)
                          {
                              $team2count++;
                              
                          }
                          
                          $allplayer[] = $player;
                        $position=$player['position'];
                        if(!empty($position)){

                            if($player['player_multiplier']==2){
                              $captain  =$player;
                            }else if($player['player_multiplier']==1.5){

                             $vise_captain=$player;
                            }
                            $position=strtolower($position);
                            if (strpos($position, 'wicketkeeper') !== false) {
                                $wicketkeapers[]=$player;
                                continue;
                            }
                            if (strpos($position, 'batsman') !== false) {
                                $batsmans[]=$player;
                                continue;
                            }

                            if (strpos($position, 'allrounder') !== false) {
                                $allrounders[]=$player;
                                continue;
                            }

                            if (strpos($position, 'bowler') !== false) {
                                $bowlers[]=$player;
                                continue;
                            }


                        }



                        
                       
                        $j++;

                    }

                   
                    $team['team_total_points'] = $team_total_points;
                    $team['captain'] = $captain;
                    $team['vise_captain'] = $vise_captain;
                    

                    $team['team1count'] = $team1count;
                    $team['team2count'] = $team2count;
                    $team['batsmans'] = $batsmans;
                    $team['bowlers'] = $bowlers;
                    $team['wicketkeapers'] = $wicketkeapers;
                    $team['allrounders'] = $allrounders;
                    $team['allplayer'] = $allplayer;


                    $output[$i]=$team;
                    $i++;
        }  
        
        /*$output['joined_teams'] = array();
        if($contest_id>0){
            $query1 = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND match_contest_id=?";
            $query_res1 = $this->conn->prepare($query1);
            $query_res1->bindParam(1, $customer_id);
            $query_res1->bindParam(2, $contest_id);
            while($joineddata = $query_res1->fetch(PDO::FETCH_ASSOC)){
                $output['joined_teams'][] = $joineddata;
            }
        }*/

        $this->closeStatement($query_res);
        return $output;
     
    }
    

    public function get_customer_match_teams_joined($customer_id,$match_unique_id,$match_contest_id=0,$contest_id=0){
         $this->setGroupConcatLimit();
           $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id='$match_unique_id')";
          $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);

        $match_ids=$array_series['match_ids'];

        
        $teamsQuery="select team_1_id,team_2_id, playing_squad_updated from tbl_cricket_matches where  unique_id='$match_unique_id'";
        $teamsQuery_res = $this->conn->prepare($teamsQuery);
        $teamsQuery_res->execute();
        $array_teams = $teamsQuery_res->fetch(PDO::FETCH_ASSOC);

        $team1Id=$array_teams['team_1_id'];
        $team2Id=$array_teams['team_2_id'];
        $playing_squad_updated=$array_teams['playing_squad_updated'];
        
          $query="SELECT tcct.id,tcct.name as team_name, tct_one.name as team_name_one, tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two, tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two FROM `tbl_cricket_customer_teams` tcct LEFT JOIN tbl_cricket_teams tct_one ON ($team1Id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON ($team2Id=tct_two.id) WHERE tcct.customer_id=? AND tcct.match_unique_id= ? AND tcct.id  IN(SELECT customer_team_id from tbl_cricket_customer_contests where match_contest_id='$match_contest_id' AND customer_id=$customer_id)";
        
        if(!empty($customer_team_id)){
            $query.=" AND tcct.id= ?";
        }
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_unique_id);
        if(!empty($customer_team_id)){
            $query_res->bindParam(3, $customer_team_id);
        }
        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        $output = array();
        $i=0;
        while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                     $team=array();

                    $team['id']=$teamsdata['id'];
                    $team['name']=$teamsdata['team_name'];
                    
                    $team1=array();
                    $team1['id']=$teamsdata['team_id_one'];
                    $team1['name']=$teamsdata['team_name_one'];
                    $team1['sort_name']=$teamsdata['team_sort_name_one'];
                    $team1['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
                    $team['team1'] = $team1;
                    
                    $team2=array();
                    $team2['id']=$teamsdata['team_id_two'];
                    $team2['name']=$teamsdata['team_name_two'];
                    $team2['sort_name']=$teamsdata['team_sort_name_two'];
                    $team2['image']=!empty($teamsdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_two'] : NO_IMG_URL_TEAM;
                    $team['team2'] = $team2;
                    
                $sqlPlayerData = 'SELECT * from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcctp.customer_team_id ='.$teamsdata['id'];
                    $resData = $this->conn->prepare($sqlPlayerData);
                    $resData->execute();
                    $players_array  =array();


                    $j=0;
                    $batsmans=array();
                    $bowlers=array();
                    $wicketkeapers=array();
                    $allrounders=array();
                    $captain=NULL;
                    $vise_captain=NULL;
                    $team_total_points=0;
                    $team1count =0;
                     $team2count =0;
                     $allplayer=[];
                     $per_player =array();
                   while($players_array_s = $resData->fetch(PDO::FETCH_ASSOC)){ 

                        $player=array();
                        $player['player_id']=$players_array_s['player_unique_id'];
                        $player['player_pos']=$players_array_s['position'];
                        $player['player_multiplier']=$players_array_s['multiplier'];
                        $player['image']=!empty($players_array_s['image']) ? PLAYER_IMAGE_THUMB_URL.$players_array_s['image'] : NO_IMG_URL_PLAYER;
                        $player['name']=$players_array_s['short_name'];
                        $player['team_id']=$players_array_s['team_id'];
                        $player['points']=$players_array_s['points'];
                        $player['bat_type']=$players_array_s['bets'];
                        $player['bowl_type']=$players_array_s['bowls'];
                        $player['dob']=(!empty($players_array_s['dob']))? date_format( date_create(rtrim($players_array_s['dob'],',')) ,"d-m-Y" ):"";
                        $player['country']=isset($players_array_s['country_id'])?$players_array_s['country_id']:'';
                        $player['credits']=isset($players_array_s['credits'])?$players_array_s['credits']:'';;
                        $player['is_in_playing_squad']=isset($players_array_s['is_in_playing_squad'])?$players_array_s['is_in_playing_squad']:''; 
                        $player['playing_squad_updated']=$playing_squad_updated;
                        $player['position']=isset($players_array_s['playing_role'])?$players_array_s['playing_role']:''; 
                        $player['total_points']=isset($players_array_s['points'])?$players_array_s['points']:'';  
                        $team_total_points+=($player['player_multiplier']*$player['points']);

                          if($player['team_id']==$team1Id)
                          {
                              $team1count++;
                              
                          }
                        if($player['team_id']==$team2Id)
                          {
                              $team2count++;
                              
                          }
                          
                          $allplayer[] = $player;
                        $position=$player['position'];
                        if(!empty($position)){

                            if($player['player_multiplier']==2){
                              $captain  =$player;
                            }else if($player['player_multiplier']==1.5){

                             $vise_captain=$player;
                            }
                            $position=strtolower($position);
                            if (strpos($position, 'wicketkeeper') !== false) {
                                $wicketkeapers[]=$player;
                                continue;
                            }
                            if (strpos($position, 'batsman') !== false) {
                                $batsmans[]=$player;
                                continue;
                            }

                            if (strpos($position, 'allrounder') !== false) {
                                $allrounders[]=$player;
                                continue;
                            }

                            if (strpos($position, 'bowler') !== false) {
                                $bowlers[]=$player;
                                continue;
                            }


                        }



                        
                       
                        $j++;

                    }

                   
                    $team['team_total_points'] = $team_total_points;
                    $team['captain'] = $captain;
                    $team['vise_captain'] = $vise_captain;
                    

                    $team['team1count'] = $team1count;
                    $team['team2count'] = $team2count;
                    $team['batsmans'] = $batsmans;
                    $team['bowlers'] = $bowlers;
                    $team['wicketkeapers'] = $wicketkeapers;
                    $team['allrounders'] = $allrounders;
                    $team['allplayer'] = $allplayer;


                    $output[$i]=$team;
                    $i++;
        }
        
        /*$output['joined_teams'] = array();
        if($contest_id>0){
            $query1 = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND match_contest_id=?";
            $query_res1 = $this->conn->prepare($query1);
            $query_res1->bindParam(1, $customer_id);
            $query_res1->bindParam(2, $contest_id);
            while($joineddata = $query_res1->fetch(PDO::FETCH_ASSOC)){
                $output['joined_teams'][] = $joineddata;
            }
        }*/

        $this->closeStatement($query_res);
        return $output;
     
    }
    public function getPlayerFormattedName($name){
    	$playerRealName=explode(" ",$name);
		if(count($playerRealName)>1){
			$name=substr($playerRealName[0],0,1)." ".$playerRealName[1];
		}
		return $name;
    }


    public function get_customer_match_team_detail($customer_team_id){

        
        $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id=(select match_unique_id from tbl_cricket_customer_teams where id='$customer_team_id'))";
        $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);
        
        $teamsQuery="select team_1_id,team_2_id, playing_squad_updated from tbl_cricket_matches where  unique_id=(select match_unique_id from tbl_cricket_customer_teams where id='$customer_team_id')";
        $teamsQuery_res = $this->conn->prepare($teamsQuery);
        $teamsQuery_res->execute();
        $array_teams = $teamsQuery_res->fetch(PDO::FETCH_ASSOC);

        $team1Id=$array_teams['team_1_id'];
        $team2Id=$array_teams['team_2_id'];
        $playing_squad_updated=$array_teams['playing_squad_updated'];

          $sql  ='SELECT *,tc.name as country,tcp.name as player_name,tcmp.playing_role as playing_role FROM tbl_cricket_customer_team_plyers tcctp 
          LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) 
          LEFT JOIN tbl_cricket_match_players tcmp ON 
          (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) 
          LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) Where tcctp.customer_team_id = '.$customer_team_id;
            
            $query_ress = $this->conn->prepare($sql);
             $query_ress->execute();
    
        $players_array = $query_ress->fetchAll(PDO::FETCH_ASSOC);
    //// print_r($players_array);die();

     
       
        if(empty($array_series['match_ids'])){
         return "NO_RECORD";       
        }

        $match_ids=$array_series['match_ids'];
       
        
        $query="SELECT tcct.customer_id,tcct.id,tcct.name as team_name, tct_one.name as team_name_one, tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two, tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two, (SELECT GROUP_CONCAT(CONCAT(tcctp.player_unique_id,'----',tcctp.position,'----',tcctp.multiplier,'----',IFNULL(tcmp.image, '0'),'----',IFNULL(tcp.name,'0'),'----',tcctp.team_id,'----',tcmp.points,'----',IF((IFNULL(tcp.bets,''))='',' ',tcp.bets), '----',IF((IFNULL(tcp.bowls,''))='',' ',tcp.bowls) ,'----',IF((IFNULL(tcp.dob,''))='',' ',tcp.dob), '----',IFNULL(tc.name,''),'----',tcmp.credits,'----',tcmp.is_in_playing_squad,'----',tcmp.dream_team_player,'----',tcp.position,'----',(select sum(points) from tbl_cricket_match_players where player_unique_id=tcctp.player_unique_id AND match_unique_id IN ($match_ids)),'----',tcmp.selected_by,'----',tcmp.selected_as_caption,'----',tcmp.selected_as_vccaption,'----',tcmp.playing_role) ORDER BY tcctp.position ASC SEPARATOR '--++--' ) from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcctp.customer_team_id = tcct.id ) as players_data FROM `tbl_cricket_customer_teams` tcct LEFT JOIN tbl_cricket_teams tct_one ON ($team1Id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON ($team2Id=tct_two.id) WHERE tcct.id=?";

        


         
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_team_id);

        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        
        $teamsdata = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);

        $team=array();

        $team['customer_id']=$teamsdata['customer_id'];
        $team['id']=$teamsdata['id'];
        $team['name']=$teamsdata['team_name'];
        
        $team1=array();
        $team1['id']=$teamsdata['team_id_one'];
        $team1['name']=$teamsdata['team_name_one'];
        $team1['sort_name']=$teamsdata['team_sort_name_one'];
        $team1['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
        $team['team1'] = $team1;
        
        $team2=array();
        $team2['id']=$teamsdata['team_id_two'];
        $team2['name']=$teamsdata['team_name_two'];
        $team2['sort_name']=$teamsdata['team_sort_name_two'];
        $team2['image']=!empty($teamsdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_two'] : NO_IMG_URL_TEAM;
        $team['team2'] = $team2;
        
    ///    $players_array=$players_array;

        $j=0;
         $batsmans=array();
        $bowlers=array();
        $wicketkeapers=array();
        $allrounders=array();
        $captain=NULL;
        $vise_captain=NULL;
        $allplayer =[];
  foreach($players_array as $players_array_s){

            $per_player=$players_array_s;
             $player=array();
            $player['player_id']=$per_player['player_unique_id'];
            $player['player_pos']=$per_player['playing_role'];
            $player['player_multiplier']=$per_player['multiplier'];
            $player['image']=!empty($per_player['image']) ? PLAYER_IMAGE_THUMB_URL.$per_player['image'] : NO_IMG_URL_PLAYER;
            $player['name']=$per_player['player_name'];
            $player['team_id']=$per_player['team_id'];
            $player['points']=$per_player['points'];
            $player['bat_type']=$per_player['bets'];
            $player['bowl_type']=$per_player['bowls'];
            $player['dob']=(!empty($per_player['dob']))? date_format( date_create($per_player['dob']) ,"d-m-Y" ):"";
            $player['country']=$per_player['country'];
            $player['credits']=$per_player['credits'];
            $player['is_in_playing_squad']=$per_player['is_in_playing_squad'];
            $player['playing_squad_updated']=$playing_squad_updated;
            $player['dream_team_player']=$per_player['dream_team_player'];
            $player['position']=$per_player['playing_role'];
           /// $player['total_points']=$per_player[15];

            $player['selected_by'] =$per_player['selected_by'];
            $player['selected_as_caption'] =$per_player['selected_as_caption'];
            $player['selected_as_vccaption'] =$per_player['selected_as_vccaption'];
        ///    $player['position'] =$per_player['position'];
            
            $position=$player['player_pos'];
                    $allplayer[] = $player;
                    
                     /*echo '<pre>';
            print_r($allplayer);
            echo '</pre>';*/
   if(!empty($position)){

                if($player['player_multiplier']==2){
                  $captain  =$player;
                }else if($player['player_multiplier']==1.5){

                 $vise_captain=$player;
                }
                $position=strtolower($position);
                if (strpos($position, 'wicketkeeper') !== false) {
                    $wicketkeapers[]=$player;
                    continue;
                }
                if (strpos($position, 'batsman') !== false) {
                    $batsmans[]=$player;
                    continue;
                }

                if (strpos($position, 'allrounder') !== false) {
                    $allrounders[]=$player;
                    continue;
                }

                if (strpos($position, 'bowler') !== false) {
                    $bowlers[]=$player;
                    continue;
                }


            }





            $j++;

        }


        $team['captain'] = $captain;
        $team['vise_captain'] = $vise_captain; 

        $team['batsmans'] = $batsmans;
        $team['bowlers'] = $bowlers;
        $team['wicketkeapers'] = $wicketkeapers;
        $team['allrounders'] = $allrounders;                   
               $team['allplayer'] = $allplayer;                   

        return $team;
     
    }


    public function get_match_dream_team_detail($match_unique_id){


        // $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id='$match_unique_id')";
        // $query_series_res = $this->conn->prepare($query_series);
        // $query_series_res->execute();
        // $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);

       
        // if(empty($array_series['match_ids'])){
        //  return "NO_RECORD";       
        // }

        // $match_ids=$array_series['match_ids'];


        $query="SELECT tcm.playing_squad_updated, 0 as id,'dream team' as team_name, tct_one.name as team_name_one, tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two, tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two, (SELECT GROUP_CONCAT(CONCAT(tcmp.player_unique_id,'----','0','----','0','----',IFNULL(tcmp.image, '0'),'----',IFNULL(tcp.name,'0'),'----',tcmp.team_id,'----',tcmp.points,'----',IF((IFNULL(tcp.bets,''))='',' ',tcp.bets), '----',IF((IFNULL(tcp.bowls,''))='',' ',tcp.bowls) ,'----',IF((IFNULL(tcp.dob,''))='',' ',tcp.dob), '----',IFNULL(tc.name,'') ,'----', tcmp.credits, '----', tcmp.is_in_playing_squad, '----', tcmp.dream_team_player, '----', tcp.position, '----', tcmp.playing_role) ORDER BY tcmp.points DESC SEPARATOR '--++--' ) from tbl_cricket_match_players tcmp  LEFT JOIN tbl_cricket_players tcp ON (tcmp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcmp.match_unique_id = ?  AND tcmp.dream_team_player='Y') as players_data FROM `tbl_cricket_matches` tcm LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE tcm.unique_id=?";

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);
        $query_res->bindParam(2, $match_unique_id);


        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }

        $teamsdata = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);

        $playing_squad_updated=$teamsdata['playing_squad_updated'];

        $team=array();

        $team['customer_id']=0;
        $team['id']=$teamsdata['id'];
        $team['name']=$teamsdata['team_name'];

        $team1=array();
        $team1['id']=$teamsdata['team_id_one'];
        $team1['name']=$teamsdata['team_name_one'];
        $team1['sort_name']=$teamsdata['team_sort_name_one'];
        $team1['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
        $team['team1'] = $team1;

        $team2=array();
        $team2['id']=$teamsdata['team_id_two'];
        $team2['name']=$teamsdata['team_name_two'];
        $team2['sort_name']=$teamsdata['team_sort_name_two'];
        $team2['image']=!empty($teamsdata['team_image_two']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_two'] : NO_IMG_URL_TEAM;
        $team['team2'] = $team2;

        $players_array=explode("--++--",$teamsdata['players_data']);

        $j=0;
        $batsmans=array();
        $bowlers=array();
        $wicketkeapers=array();
        $allrounders=array();
        $captain=NULL;
        $vise_captain=NULL;
        $multiplier_array=unserialize(MULTIPLIER_ARRAY);
        //print_r($multiplier_array);die;
        foreach($players_array as $players_array_s){
            
            $per_player=explode("----", $players_array_s);

            $player=array();
            $player['player_id']=$per_player[0];
            $player['player_pos']=$j+1;
            $player['player_multiplier']=$multiplier_array[$j];

            $player['image']=!empty($per_player[3]) ? PLAYER_IMAGE_THUMB_URL.$per_player[3] : NO_IMG_URL_PLAYER;
            $player['name']=$per_player[4];
            $player['team_id']=$per_player[5];
            $player['points']=$per_player[6];
            $player['bat_type']=$per_player[7];
            $player['bowl_type']=$per_player[8];
            $player['dob']=(!empty($per_player[9]))? date_format( date_create($per_player[9]) ,"d-m-Y" ):"";
            $player['country']=$per_player[10];
            $player['credits']=$per_player[11];
            $player['is_in_playing_squad']=$per_player[12];
            $player['playing_squad_updated']=$playing_squad_updated;
            $player['dream_team_player']=$per_player[13];
            $player['position']=$per_player[14];
            $player['position']=$per_player[15];

            

            $position=$player['position'];
            if(!empty($position)){

                if($player['player_multiplier']==2){
                  $captain  =$player;
                }else if($player['player_multiplier']==1.5){

                 $vise_captain=$player;
                }

                $position=strtolower($position);
                if (strpos($position, 'wicketkeeper') !== false) {
                    $wicketkeapers[]=$player;
                }elseif (strpos($position, 'batsman') !== false) {
                    $batsmans[]=$player;
                }elseif (strpos($position, 'allrounder') !== false) {
                    $allrounders[]=$player;
                }elseif (strpos($position, 'bowler') !== false) {
                    $bowlers[]=$player;
                }else{
                    continue;
                }

                $j++;
            }

        }

        $team['captain'] = $captain;
        $team['vise_captain'] = $vise_captain; 

        $team['batsmans'] = $batsmans;
        $team['bowlers'] = $bowlers;
        $team['wicketkeapers'] = $wicketkeapers;
        $team['allrounders'] = $allrounders;                   
       
        return $team;
    }

    public function updatePlayerSelectedByCount($match_unique_id,$player_unique_ids){

        $player_unique_ids=implode(",", $player_unique_ids);

        $query = "Select (Select count(id) from tbl_cricket_customer_teams tcct where tcct.match_unique_id=?) as match_team_count, count(id) as player_team_count, tcctp.player_unique_id,SUM(CASE WHEN tcctp.multiplier = '1.5' THEN 1 ELSE 0 END) AS vice_captain_count,SUM(CASE WHEN tcctp.multiplier = '2' THEN 1 ELSE 0 END) AS captain_count from tbl_cricket_customer_team_plyers tcctp where tcctp.match_unique_id=? AND tcctp.player_unique_id IN ($player_unique_ids) group by tcctp.player_unique_id";

        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $match_unique_id);      
        $query_res->bindParam(2, $match_unique_id);  

        if(!$query_res->execute()){
            return;
        }


        $output = array();
        if ($query_res->rowCount() > 0) {
            
            $updateCases="";
            $updateCases1="";
            $updateCases2="";
            while($statsdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                
                $match_team_count=$statsdata['match_team_count'];
                $player_team_count=$statsdata['player_team_count'];
                $vice_captain_count=$statsdata['vice_captain_count'];
                $captain_count=$statsdata['captain_count'];
                $selected_by=0.00;
                $vice_captain_selected_by=0.00;
                $captain_selected_by=0.00;

                if($match_team_count>0){
                    $selected_by =$this->format_number(($player_team_count/$match_team_count)*100);
                    $vice_captain_selected_by =$this->format_number(($vice_captain_count/$match_team_count)*100);
                    $captain_selected_by =$this->format_number(($captain_count/$match_team_count)*100);
                }

                $updateCases.=" WHEN player_unique_id='".$statsdata['player_unique_id']."' THEN '".$selected_by."'";
                $updateCases1.=" WHEN player_unique_id='".$statsdata['player_unique_id']."' THEN '".$vice_captain_selected_by."'";
                $updateCases2.=" WHEN player_unique_id='".$statsdata['player_unique_id']."' THEN '".$captain_selected_by."'";
                
            }
             
            $this->closeStatement($query_res);


           $query = "UPDATE tbl_cricket_match_players SET selected_by = CASE ".$updateCases."  END,selected_as_vccaption = CASE ".$updateCases1."  END,selected_as_caption = CASE ".$updateCases2."  END WHERE player_unique_id IN ($player_unique_ids) AND match_unique_id = '$match_unique_id'";

           $query_res = $this->conn->prepare($query);  
           
           if(!$query_res->execute()){
                return;
           }

           $this->closeStatement($query_res);

        }

    }


    public function get_series_by_player_statistics($match_unique_id,$player_unique_id) {

        $query = "Select tcm.short_title,tcm.unique_id,tcm.name as match_name,tcm.match_date,tcmp.player_unique_id,tcmp.credits,tcmp.points, 
        tcmp.selected_by,tcmp.selected_as_vccaption,tcmp.selected_as_caption,
        tcmp.playing_role as playing_role,tcp.name,IF(tcp.cp_image!='',tcp.cp_image,'".NO_IMG_URL_PLAYER."') 
         as image from tbl_cricket_match_players 
        tcmp INNER JOIN tbl_cricket_players as tcp ON tcp.uniqueid=tcmp.player_unique_id LEFT JOIN tbl_cricket_matches tcm ON tcm.unique_id=tcmp.match_unique_id where tcmp.player_unique_id=? 
        AND tcmp.match_unique_id IN (Select tcm.unique_id from tbl_cricket_matches tcm where 
        tcm.series_id=(Select tcm.series_id from tbl_cricket_matches tcm where tcm.unique_id=?)
         AND tcm.status='A' AND tcm.is_deleted='N')";

        $query_res = $this->conn->prepare($query);  
        $query_res->bindParam(1, $player_unique_id);      
        $query_res->bindParam(2, $match_unique_id);      
        $artExtra =[];
        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    
                    $i=0;
                    while($statsdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                        
                        $playerStats=array();
                        $playerStats['match_unique_id'] =$statsdata['unique_id'];
                        $playerStats['match_name'] =$statsdata['match_name'];
                        $playerStats['match_sname'] =$statsdata['short_title'];
                        $playerStats['match_date'] =date('d,M Y',$statsdata['match_date']);
                        $playerStats['player_unique_id'] =$statsdata['player_unique_id'];
                        $playerStats['position'] =$statsdata['playing_role'];
                        $playerStats['points'] =$statsdata['points'];
                        $playerStats['credits'] =$statsdata['credits'];
                        $playerStats['name'] =$statsdata['name'];
                        $playerStats['image'] =$statsdata['image'];
                        $playerStats['match_team_count'] =0;
                        $playerStats['player_team_count'] =0;
                        $playerStats['selected_by'] =$statsdata['selected_by'];
                        $playerStats['selected_as_vccaption'] =$statsdata['selected_as_vccaption'];
                        $playerStats['selected_as_caption'] =$statsdata['selected_as_caption'];
                        
                        $output[$i]=$playerStats;
                        if($match_unique_id==$playerStats['match_unique_id'])
                        {
                            $artExtra = $playerStats;
                        }
                        $i++;
                    }
                     
                    $this->closeStatement($query_res);

                    $aryResponse=[];
                    $aryResponse['info'] = $output;
                    $aryResponse['self'] = $artExtra;
      
                    return $aryResponse;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
        }        
       
    }

    public function get_beat_the_expert_team_rank($match_unique_id,$match_contest_id,$customer_team_id){
        $query = "SELECT new_rank FROM tbl_cricket_customer_contests WHERE match_unique_id = ? AND match_contest_id=? AND customer_team_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);
        $query_res->bindParam(2, $match_contest_id);
        $query_res->bindParam(3, $customer_team_id);
        $query_res->execute();
        
        $num_rows =$query_res->rowCount();
        if ($num_rows > 0) {
           $array = $query_res->fetch();
           $this->closeStatement($query);
           return $array['new_rank'];
        }else{
            $this->closeStatement($query);
        }
        return 1;
    }

    public function update_beat_the_expert_team($match_contest_id,$customer_team_id){

        $select_user_query = "UPDATE tbl_cricket_contest_matches SET team_id=? WHERE id=?";
        $select_user  = $this->conn->prepare($select_user_query);
        $select_user->bindParam(1,$customer_team_id);
        $select_user->bindParam(2,$match_contest_id);
        if ($select_user->execute()) {
            $this->closeStatement($select_user);
           return 'SUCCESS';
        } else {
            $this->closeStatement($select_user);
            return 'UNABLE_TO_PROCEED';
        }

    }


    public function select_contest_team_count($match_contest_id){
        $query = "SELECT id FROM tbl_cricket_customer_contests WHERE match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_contest_id);
        $query_res->execute();
        
        $num_rows =$query_res->rowCount();  
        $this->closeStatement($query_res);      
        return $num_rows;
    }


    public function customer_join_contest_multi($customer_id,$match_unique_id,$match_contest_id,$customer_team_ids,$entry_fees=0){
        $customer_team_id_explode=explode(',',$customer_team_ids);
        $customer_team_ids_count=count($customer_team_id_explode);

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
         $output=array();
        $output['code']="";
        $output['msg']="";

        if(empty($get_match_data)){
           $output['code']="NO_MATCH_FOUND";
            $output['msg']="Invalid match.";
            return $output;
        }
        
        if($get_match_data['match_progress']!='F'){
            $output['code']="INVALID_MATCH";
            $output['msg']="The deadline has passed! Check out the contests you've joined for this match.";
            return $output;
        }

          $query = "SELECT id,name FROM tbl_cricket_customer_teams WHERE id IN ($customer_team_ids) AND customer_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        

        if($num_rows!=$customer_team_ids_count){
            $this->closeStatement($query_res);
           
             $output['code']="NO_TEAM_FOUND";
             $output['msg']="Invalid teams.";
            return $output;
        }
        $team_names=array();

        while($teamsData = $query_res->fetch()){
            $team_names[$teamsData['id']]=$teamsData['name'];

        }
        $this->closeStatement($query_res);

        $query = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND customer_team_id IN ($customer_team_ids) AND match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_contest_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows>0){
           

            $output['code']="TEAM_ALREADY_JOINED";
            $output['msg']="Invalid teams.";
            return $output;
        }


        /*$query_mc = "SELECT  tcm.match_date as match_date,tcm.name as match_name,tcm.unique_id,tccm.id,tccm.entry_fees, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type, tccm.total_team,tccm.per_user_team_allowed,(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccm.id) as total_joined_teams_count, (SELECT count(tccc_contestt.id) FROM tbl_cricket_customer_contests tccc_contestt WHERE tccc_contestt.match_contest_id=tccm.id AND tccc_contestt.customer_id=?) as customer_joined_teams_count FROM tbl_cricket_contest_matches tccm INNER JOIN tbl_cricket_contests tcc ON tcc.id=tccm.contest_id LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tcc.category_id LEFT JOIN tbl_cricket_matches tcm on (tcm.id=tccm.match_id) WHERE tccm.id =? AND tccm.status='A' AND tccm.is_deleted='N' AND tcc.status='A' AND tcc.is_deleted='N'";*/


        $query_mc = "SELECT  tcm.match_date as match_date,tcm.name as match_name,tcm.unique_id,tccm.id,tccm.entry_fees, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type, tccm.total_team,tccm.per_user_team_allowed,tccm.max_entry_fees,tccm.is_beat_the_expert,(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccm.id) as total_joined_teams_count, (SELECT count(tccc_contestt.id) FROM tbl_cricket_customer_contests tccc_contestt WHERE tccc_contestt.match_contest_id=tccm.id AND tccc_contestt.customer_id=?) as customer_joined_teams_count FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tccm.category_id LEFT JOIN tbl_cricket_matches tcm on (tcm.id=tccm.match_id) WHERE tccm.id =? AND tccm.status='A' AND tccm.is_deleted='N'";


        $query_mcres = $this->conn->prepare($query_mc);
        $query_mcres->bindParam(1, $customer_id);
        $query_mcres->bindParam(2, $match_contest_id);
        $query_mcres->execute();
        $num_rows_mc =$query_mcres->rowCount();

        if($num_rows_mc==0){
            $this->closeStatement($query_mcres);
           $output['code']="NO_CONTEST_FOUND";
            $output['msg']="No contest found.";
            return $output;
        }
        $contestData = $query_mcres->fetch();
        $this->closeStatement($query_mcres);

        if($contestData['is_beat_the_expert']=="Y"){

            if($contestData['entry_fees']<=$entry_fees && $contestData['max_entry_fees']>=$entry_fees){

                $entry_fees=$entry_fees;
            }else{

                $output['code']="INVALID_ENTRY_FEE";
                $output['msg']="Invalid Entry Fees.";
                return $output;
            }


        }else{

            $entry_fees=$contestData['entry_fees'];
        }

        $unique_id=$contestData['unique_id'];
        $match_name=$contestData['match_name'];
        $match_date=date('j F Y',$contestData['match_date']);
        if($match_unique_id!=$unique_id){
            $output['code']="NO_CONTEST_FOUND";
            $output['msg']="No contest found.";
            return $output;
        }
        $non_joined_teams=array();


            $customerWalletData=$this->get_customer_wallet_detail($customer_id);
            $depositWallet=$customerWalletData['wallet']['deposit_wallet'];
            $winningWallet=$customerWalletData['wallet']['winning_wallet'];
            $bonusWallet=$customerWalletData['wallet']['bonus_wallet'];
            $real_entry_fees=$entry_fees;
            $entry_fees=$entry_fees*$customer_team_ids_count;
            $used_bonus=0;
            $used_deposit=0;
            $used_winning=0;
            $need_pay=$entry_fees;
            

            //$settingData=$this->get_setting_data();
            $BONUS_WALLET_PER=0;
            $BONUS_WALLET_PER_TYPE="P";
            //       if(!empty($settingData['CASH_BONUS_PERCENTAGES'])){
            //  $BONUS_WALLET_PER=$settingData['CASH_BONUS_PERCENTAGES'];
            // }

            $cash_bonus_used_value=$contestData['cash_bonus_used_value'];
            $cash_bonus_used_type=$contestData['cash_bonus_used_type'];

            $BONUS_WALLET_PER=$cash_bonus_used_value;
            $BONUS_WALLET_PER_TYPE=$cash_bonus_used_type;

            if($BONUS_WALLET_PER_TYPE=="F" && $BONUS_WALLET_PER>$need_pay){
                $BONUS_WALLET_PER=$need_pay;
            }

            if($need_pay>0){
                if($bonusWallet>0){
                    $used_bonus=$entry_fees*($BONUS_WALLET_PER/100);
                    if($BONUS_WALLET_PER_TYPE=="F"){
                        $used_bonus=$BONUS_WALLET_PER;
                    }
                    $used_bonus=round($used_bonus,2);
                    if($used_bonus>$bonusWallet){
                        $used_bonus=$bonusWallet;
                    }
                    $need_pay-=$used_bonus;
                }
                if($need_pay>0){
                    if($depositWallet>0){
                        $used_deposit=$need_pay;
                        if($used_deposit>$depositWallet){
                            $used_deposit=$depositWallet;
                        }
                        $need_pay-=$used_deposit;
                    }
                }
                if($need_pay>0){
                    if($winningWallet>0){
                        $used_winning=$need_pay;
                        if($used_winning>$winningWallet){
                            $used_winning=$winningWallet;
                        }
                        $need_pay-=$used_winning;
                    }
                }
            }
            
            if($need_pay>0){
                
                $output['code']="LOW_BALANCE";
                $output['msg']="low balance.";
                return $output;
            }

            $entry_fees=$real_entry_fees;
            $total_team=$contestData['total_team'];
            $main_total_joined_teams_count=$contestData['total_joined_teams_count'];

        foreach($team_names as $customer_team_id => $customer_team_id_name){


            
            $total_joined_teams_count=$this->select_contest_team_count($match_contest_id);
            if($total_joined_teams_count>=$total_team){
                $this->create_duplicate_match_contest($match_unique_id,$match_contest_id);
                $non_joined_teams[]=$customer_team_id_name;
                continue;
            }

            //$entry_fees=$contestData['entry_fees'];

            
            $customerWalletData=$this->get_customer_wallet_detail($customer_id);
            $depositWallet=$customerWalletData['wallet']['deposit_wallet'];
            $winningWallet=$customerWalletData['wallet']['winning_wallet'];
            $bonusWallet=$customerWalletData['wallet']['bonus_wallet'];
            $used_bonus=0;
            $used_deposit=0;
            $used_winning=0;
            $need_pay=$entry_fees;
            

            //$settingData=$this->get_setting_data();
            $BONUS_WALLET_PER=0;
            $BONUS_WALLET_PER_TYPE="P";
            //       if(!empty($settingData['CASH_BONUS_PERCENTAGES'])){
            //  $BONUS_WALLET_PER=$settingData['CASH_BONUS_PERCENTAGES'];
            // }

            $cash_bonus_used_value=$contestData['cash_bonus_used_value'];
            $cash_bonus_used_type=$contestData['cash_bonus_used_type'];

            $BONUS_WALLET_PER=$cash_bonus_used_value;
            $BONUS_WALLET_PER_TYPE=$cash_bonus_used_type;

            if($BONUS_WALLET_PER_TYPE=="F" && $BONUS_WALLET_PER>$need_pay){
                $BONUS_WALLET_PER=$need_pay;
            }

            if($need_pay>0){
                if($bonusWallet>0){
                    $used_bonus=$entry_fees*($BONUS_WALLET_PER/100);
                    if($BONUS_WALLET_PER_TYPE=="F"){
                        $used_bonus=$BONUS_WALLET_PER;
                    }
                    $used_bonus=round($used_bonus,2);
                    if($used_bonus>$bonusWallet){
                        $used_bonus=$bonusWallet;
                    }
                    $need_pay-=$used_bonus;
                }
                if($need_pay>0){
                    if($depositWallet>0){
                        $used_deposit=$need_pay;
                        if($used_deposit>$depositWallet){
                            $used_deposit=$depositWallet;
                        }
                        $need_pay-=$used_deposit;
                    }
                }
                if($need_pay>0){
                    if($winningWallet>0){
                        $used_winning=$need_pay;
                        if($used_winning>$winningWallet){
                            $used_winning=$winningWallet;
                        }
                        $need_pay-=$used_winning;
                    }
                }
            }
            
            if($need_pay>0){
                $non_joined_teams[]=$customer_team_id_name;
                continue;
            }

            

            $current_time = time();                        
            $create_customer_contest_query = "INSERT INTO tbl_cricket_customer_contests SET customer_id = ?, match_unique_id=?, match_contest_id=?, customer_team_id=?, created=?, updated=?,entry_fees=?";
            $create_customer_contest_query_res = $this->conn->prepare($create_customer_contest_query);
            $create_customer_contest_query_res->bindParam(1, $customer_id);
            $create_customer_contest_query_res->bindParam(2, $match_unique_id);
            $create_customer_contest_query_res->bindParam(3, $match_contest_id);       
            $create_customer_contest_query_res->bindParam(4, $customer_team_id);
            $create_customer_contest_query_res->bindParam(5, $current_time);
            $create_customer_contest_query_res->bindParam(6, $current_time);
            $create_customer_contest_query_res->bindParam(7, $entry_fees);


            if ($create_customer_contest_query_res->execute()) {

                $lastUserJoinedRowId=$this->conn->lastInsertId();
                $this->closeStatement($create_customer_contest_query_res);

                $total_joined_teams_count=$this->select_contest_team_count($match_contest_id);
                if($total_joined_teams_count>$total_team){
                    //$this->create_duplicate_match_contest($match_unique_id,$match_contest_id);
                    $non_joined_teams[]=$customer_team_id_name;
                    $this->deleteExtraJoinedUserFromMatchContest($lastUserJoinedRowId);
                }else{

                    $description=$customer_id." Join contest match_contest_id ".$match_contest_id." with customer_team_id ".$customer_team_id.".";
                    $transaction_id="JCWALL".time().$customer_id."_".$match_contest_id."_".$customer_team_id;


                    if($used_bonus>0){

                        $wallet_type="bonus_wallet";
                        $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_bonus,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);

                    }

                    if($used_deposit>0){

                        $wallet_type="deposit_wallet";         
                        $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_deposit,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);
                        
                    }
                    if($used_winning>0){


                        $wallet_type="winning_wallet";         
                        $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_winning,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);
                        
                    }
                 ///   echo $entry_fees;
if($entry_fees>0)
                    {
                    $this->sendcommission($customer_id,$entry_fees);
                    }
                    
                    
                    $customer_detail=$this->getUpdatedProfileData($customer_id); 


                    $data=array();              

                    $data['message']="Welcome to the contest. You have successfully joined the contest with an entry fee of ".CURRENCY_SYMBOL.$entry_fees." for ".$match_date." ".$match_name.".";
                    $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                    $email=$customer_detail['email'];
                    $this->sendTemplatesInMail('join_contest', trim($full_name), $email,$data);

                    $main_total_joined_teams_count++;

                }

                   
                   

             }else{
                $this->closeStatement($create_customer_contest_query_res);
                $non_joined_teams[]=$customer_team_id_name;
                continue;
             }
        }

        $customer_detail=$this->getUpdatedProfileData($customer_id); 
        $ouptputt=array();
        $ouptputt['code']="";
        $ouptputt['msg']="Contest joined successfully.";
        if(!empty($non_joined_teams)){
            $non_joined_teams_string=implode(',',$non_joined_teams);
            $ouptputt['msg']="Team ".$non_joined_teams_string." not joined due to some error.";
        }
        $ouptputt['customer_detail']=$customer_detail;
        $ouptputt['match_contest_id']=$match_contest_id;

        if($main_total_joined_teams_count>=$total_team){
            $this->create_duplicate_match_contest($match_unique_id,$match_contest_id);
        }

        return $ouptputt;

  
    }


    public function sendcommission($intUserId,$intEntryFees)
    {
          $query = "SELECT * FROM tbl_customers WHERE id= ($intUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $row = $query_res->fetch();

        if(isset($row['id']) && $row['used_referral_user_id']>0)
        {
            
         $querysdata = "SELECT * FROM tbl_commission WHERE c_id= 1";
        $query_ress = $this->conn->prepare($querysdata);
        $query_ress->execute();
        $rowCommission = $query_ress->fetch();
     
        if(isset($rowCommission["c_id"]) && $rowCommission["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommission["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
$this->update_customer_wallet($row['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
///   print_r($row);die;

        }
        
        $intRefferedUserId = $row['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowSecoondLevel = $query_res->fetch();
        
        
        if(isset($rowSecoondLevel['id']) && $rowSecoondLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 2";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionSecondLevel = $query_res->fetch();
        
        if(isset($rowCommissionSecondLevel["c_id"]) && $rowCommissionSecondLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionSecondLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowSecoondLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
         $intRefferedUserId = $rowSecoondLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowThirdLevel = $query_res->fetch();
        
        
        if(isset($rowThirdLevel['id']) && $rowThirdLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 3";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionThirdLevel = $query_res->fetch();
        
        if(isset($rowCommissionThirdLevel["c_id"]) && $rowCommissionThirdLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionThirdLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowThirdLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
        
         $intRefferedUserId = $rowThirdLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowFourthLevel = $query_res->fetch();
        
        
        if(isset($rowFourthLevel['id']) && $rowFourthLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 4";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionForthLevel = $query_res->fetch();
        
        if(isset($rowCommissionForthLevel["c_id"]) && $rowCommissionForthLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionForthLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowFourthLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
        
        
         $intRefferedUserId = $rowFourthLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowFifthLevel = $query_res->fetch();
        
        
        if(isset($rowFifthLevel['id']) && $rowFifthLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 5";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionFifthLevel = $query_res->fetch();
        
        if(isset($rowCommissionFifthLevel["c_id"]) && $rowCommissionFifthLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionFifthLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowFifthLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
         $intRefferedUserId = $rowFifthLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowSixLevel = $query_res->fetch();
        
        
        if(isset($rowSixLevel['id']) && $rowSixLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 6";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionSixLevel = $query_res->fetch();
        
        if(isset($rowCommissionSixLevel["c_id"]) && $rowCommissionSixLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionSixLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowSixLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
          $intRefferedUserId = $rowSixLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowSevenLevel = $query_res->fetch();
        
        
        if(isset($rowSevenLevel['id']) && $rowSevenLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 7";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionSevenLevel = $query_res->fetch();
        
        if(isset($rowCommissionSevenLevel["c_id"]) && $rowCommissionSevenLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionSevenLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowSevenLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
            $intRefferedUserId = $rowSevenLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowEightLevel = $query_res->fetch();
        
        
        if(isset($rowEightLevel['id']) && $rowEightLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id=8";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionEightLevel = $query_res->fetch();
        
        if(isset($rowCommissionEightLevel["c_id"]) && $rowCommissionEightLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionEightLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowEightLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
               $intRefferedUserId = $rowEightLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rownineLevel = $query_res->fetch();
        
        
        if(isset($rownineLevel['id']) && $rownineLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 9";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionNineLevel = $query_res->fetch();
        
        if(isset($rowCommissionNineLevel["c_id"]) && $rowCommissionNineLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionNineLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rownineLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
            $intRefferedUserId = $rownineLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowtenLevel = $query_res->fetch();
        
        
        if(isset($rowtenLevel['id']) && $rowtenLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 10";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionTenLevel = $query_res->fetch();
        
        if(isset($rowCommissionTenLevel["c_id"]) && $rowCommissionTenLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionTenLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowtenLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
            $intRefferedUserId = $rowtenLevel['used_referral_user_id'];
         $query = "SELECT * FROM tbl_customers WHERE id= ($intRefferedUserId)";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowElevenLevel = $query_res->fetch();
        
        
        if(isset($rowElevenLevel['id']) && $rowElevenLevel['used_referral_user_id']>0)
        {
            
         $query = "SELECT * FROM tbl_commission WHERE c_id= 11";
        $query_res = $this->conn->prepare($query);
        $query_res->execute();
        $rowCommissionElevenLevel = $query_res->fetch();
        
        if(isset($rowCommissionElevenLevel["c_id"]) && $rowCommissionElevenLevel["c_commssion"]>0)
        {
            $intCustomerName = $row['firstname'];
             $wallet_type="winning_wallet";   
             $IntTotalCommission = ($intEntryFees*$rowCommissionElevenLevel["c_commssion"])/100;
             $description = "Referral Commission received from $intCustomerName on successfully received";
                        $this->update_customer_wallet($rowElevenLevel['used_referral_user_id'],0,$wallet_type,$IntTotalCommission,"CREDIT","CUSTOMER_REFFER_COMMISSION",0,$description,0,0,false,0,$row['id'],null);
        }
        
        
        }
        
        }
        
        }
        
        }
        
        }
        
        }
        
        }
        
        }
        
        
        
        
        
        }
        
        
        }
        
        
        
        }
        
        return true;
    }
    public function deleteExtraJoinedUserFromMatchContest($joineduserRowId){
        $query = "DELETE FROM tbl_cricket_customer_contests WHERE id = ?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $joineduserRowId);
        $query_res->execute();
        $this->closeStatement($query_res);
    }


    public function customer_join_contest($customer_id,$match_unique_id,$match_contest_id,$customer_team_id,$entry_fees=0){
       

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        
        if(empty($get_match_data)){
            return "NO_MATCH_FOUND";
        }
        
        if($get_match_data['match_progress']!='F'){
            return "INVALID_MATCH";
        }

        $query = "SELECT id FROM tbl_cricket_customer_teams WHERE id = ?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_team_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows==0){
            return "NO_TEAM_FOUND";
        }

        $query = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND customer_team_id=? AND match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $customer_team_id);
        $query_res->bindParam(3, $match_contest_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows>0){
            return "TEAM_ALREADY_JOINED";
        }


        /*$query_mc = "SELECT  tcm.match_date as match_date,tcm.name as match_name,tcm.unique_id,tccm.id,tccm.entry_fees, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type, tccm.total_team,tccm.per_user_team_allowed,(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccm.id) as total_joined_teams_count, (SELECT count(tccc_contestt.id) FROM tbl_cricket_customer_contests tccc_contestt WHERE tccc_contestt.match_contest_id=tccm.id AND tccc_contestt.customer_id=?) as customer_joined_teams_count FROM tbl_cricket_contest_matches tccm INNER JOIN tbl_cricket_contests tcc ON tcc.id=tccm.contest_id LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tcc.category_id LEFT JOIN tbl_cricket_matches tcm on (tcm.id=tccm.match_id) WHERE tccm.id =? AND tccm.status='A' AND tccm.is_deleted='N' AND tcc.status='A' AND tcc.is_deleted='N'";*/


        $query_mc = "SELECT  tcm.match_date as match_date,tcm.name as match_name,tcm.unique_id,tccm.id,tccm.entry_fees, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type, tccm.total_team,tccm.per_user_team_allowed,tccm.max_entry_fees,tccm.is_beat_the_expert,(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccm.id) as total_joined_teams_count, (SELECT count(tccc_contestt.id) FROM tbl_cricket_customer_contests tccc_contestt WHERE tccc_contestt.match_contest_id=tccm.id AND tccc_contestt.customer_id=?) as customer_joined_teams_count FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tccm.category_id LEFT JOIN tbl_cricket_matches tcm on (tcm.id=tccm.match_id) WHERE tccm.id =? AND tccm.status='A' AND tccm.is_deleted='N'";


        $query_mcres = $this->conn->prepare($query_mc);
        $query_mcres->bindParam(1, $customer_id);
        $query_mcres->bindParam(2, $match_contest_id);
        $query_mcres->execute();
        $num_rows_mc =$query_mcres->rowCount();

        if($num_rows_mc==0){
            $this->closeStatement($query_mcres);
            return "NO_CONTEST_FOUND";
        }
        $contestData = $query_mcres->fetch();
        $this->closeStatement($query_mcres);

        if($contestData['is_beat_the_expert']=="Y"){

            if($contestData['entry_fees']<=$entry_fees && $contestData['max_entry_fees']>=$entry_fees){

                $entry_fees=$entry_fees;
            }else{

                return "INVALID_ENTRY_FEE";
            }


        }else{

            $entry_fees=$contestData['entry_fees'];
        }

        $unique_id=$contestData['unique_id'];
        $match_name=$contestData['match_name'];
        $match_date=date('j F Y',$contestData['match_date']);
        if($match_unique_id!=$unique_id){
             return "NO_CONTEST_FOUND";
        }
        $total_team=$contestData['total_team'];
        $per_user_team_allowed=$contestData['per_user_team_allowed'];
        $total_joined_teams_count=$contestData['total_joined_teams_count'];
        $customer_joined_teams_count=$contestData['customer_joined_teams_count'];

        if($customer_joined_teams_count>=$per_user_team_allowed){
            return "PER_USER_TEAM_ALLOWED_LIMIT";
        }

        if($total_joined_teams_count>=$total_team){


            $this->create_duplicate_match_contest($match_unique_id,$match_contest_id);
                
           
            return "CONTEST_FULL";
        }

        //$entry_fees=$contestData['entry_fees'];

        
        $customerWalletData=$this->get_customer_wallet_detail($customer_id);
        $depositWallet=$customerWalletData['wallet']['deposit_wallet'];
        $winningWallet=$customerWalletData['wallet']['winning_wallet'];
        $bonusWallet=$customerWalletData['wallet']['bonus_wallet'];
        $used_bonus=0;
        $used_deposit=0;
        $used_winning=0;
        $need_pay=$entry_fees;
        

        //$settingData=$this->get_setting_data();
        $BONUS_WALLET_PER=0;
        $BONUS_WALLET_PER_TYPE="P";
        //       if(!empty($settingData['CASH_BONUS_PERCENTAGES'])){
        //  $BONUS_WALLET_PER=$settingData['CASH_BONUS_PERCENTAGES'];
        // }

        $cash_bonus_used_value=$contestData['cash_bonus_used_value'];
        $cash_bonus_used_type=$contestData['cash_bonus_used_type'];

        $BONUS_WALLET_PER=$cash_bonus_used_value;
        $BONUS_WALLET_PER_TYPE=$cash_bonus_used_type;

        if($BONUS_WALLET_PER_TYPE=="F" && $BONUS_WALLET_PER>$need_pay){
            $BONUS_WALLET_PER=$need_pay;
        }

        if($need_pay>0){
            if($bonusWallet>0){
                $used_bonus=$entry_fees*($BONUS_WALLET_PER/100);
                if($BONUS_WALLET_PER_TYPE=="F"){
                    $used_bonus=$BONUS_WALLET_PER;
                }
                $used_bonus=round($used_bonus,2);
                if($used_bonus>$bonusWallet){
                    $used_bonus=$bonusWallet;
                }
                $need_pay-=$used_bonus;
            }
            if($need_pay>0){
                if($depositWallet>0){
                    $used_deposit=$need_pay;
                    if($used_deposit>$depositWallet){
                        $used_deposit=$depositWallet;
                    }
                    $need_pay-=$used_deposit;
                }
            }
            if($need_pay>0){
                if($winningWallet>0){
                    $used_winning=$need_pay;
                    if($used_winning>$winningWallet){
                        $used_winning=$winningWallet;
                    }
                    $need_pay-=$used_winning;
                }
            }
        }
        
        if($need_pay>0){
            return "LOW_BALANCE";
        }

        

        $current_time = time();                        
        $create_customer_contest_query = "INSERT INTO tbl_cricket_customer_contests SET customer_id = ?, match_unique_id=?, match_contest_id=?, customer_team_id=?, created=?, updated=?,entry_fees=?";
        $create_customer_contest_query_res = $this->conn->prepare($create_customer_contest_query);
        $create_customer_contest_query_res->bindParam(1, $customer_id);
        $create_customer_contest_query_res->bindParam(2, $match_unique_id);
        $create_customer_contest_query_res->bindParam(3, $match_contest_id);       
        $create_customer_contest_query_res->bindParam(4, $customer_team_id);
        $create_customer_contest_query_res->bindParam(5, $current_time);
        $create_customer_contest_query_res->bindParam(6, $current_time);
        $create_customer_contest_query_res->bindParam(7, $entry_fees);


        if ($create_customer_contest_query_res->execute()) {

                $lastUserJoinedRowId=$this->conn->lastInsertId();
                $this->closeStatement($create_customer_contest_query_res);

                $total_joined_teams_count=$this->select_contest_team_count($match_contest_id);
                if($total_joined_teams_count>$total_team){

                    $this->deleteExtraJoinedUserFromMatchContest($lastUserJoinedRowId);
                    return "CONTEST_FULL";

                }else{

                

                        $description=$customer_id." Join contest match_contest_id ".$match_contest_id." with customer_team_id ".$customer_team_id.".";
                        $transaction_id="JCWALL".time().$customer_id."_".$match_contest_id."_".$customer_team_id;

                  
                 

                        if($used_bonus>0){

                            $wallet_type="bonus_wallet";
                            $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_bonus,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);

                        }

                        if($used_deposit>0){

                            $wallet_type="deposit_wallet";         
                            $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_deposit,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);
                            
                        }
                        if($used_winning>0){

                  

                            $wallet_type="winning_wallet";         
                            $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$used_winning,"DEBIT","CUSTOMER_JOIN_CONTEST",$transaction_id,$description,0,0,false,$customer_team_id,null,null);
                            
                        }
              


                        $customer_detail=$this->getUpdatedProfileData($customer_id); 


                        $data=array();              

                        $data['message']="Welcome to the contest. You have successfully joined the contest with an entry fee of ".CURRENCY_SYMBOL.$entry_fees." for ".$match_date." ".$match_name.".";
                        $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                        $email=$customer_detail['email'];
                        $this->sendTemplatesInMail('join_contest', trim($full_name), $email,$data);

                       
                        $ouptputt=array();
                        $ouptputt['customer_detail']=$customer_detail;
                        $ouptputt['match_contest_id']=$match_contest_id;


                        $total_joined_teams_count++;
                        if($total_joined_teams_count>=$total_team){
                             $this->create_duplicate_match_contest($match_unique_id,$match_contest_id);
                        }
                        return $ouptputt;
                }

         }else{
            $this->closeStatement($create_customer_contest_query_res);
            return "UNABLE_TO_PROCEED";
         }
  
    }

    public function create_duplicate_match_contest($match_unique_id,$match_contest_id){

   
        $parent_match_contest_id=$match_contest_id;
        $get_contest_detail_query = "SELECT parent_id FROM tbl_cricket_contest_matches WHERE id = ?";
        $query_res = $this->conn->prepare($get_contest_detail_query);
        $query_res->bindParam(1, $match_contest_id);
        $query_res->execute();
        $array = $query_res->fetch();
        if (!empty($array)) {
            if($array['parent_id']!=0){
                $parent_match_contest_id=$array['parent_id'];
            }
            
        }

   
        $group_inner_query="";
        if(!empty($match_contest_id)){
            $group_inner_query.=" AND tccf.id='$match_contest_id'";
        }

        $parent_group_inner_query="";
       
        $parent_group_inner_query.=" AND tccf1.id='$parent_match_contest_id'";
        

        $inner_query="";
        if(!empty($match_unique_id)){
            $inner_query.=" AND tcm.unique_id='$match_unique_id'";
        }



        $query = "SELECT tcm.unique_id as match_unique_id, (SELECT GROUP_CONCAT(CONCAT(tccf.total_team,'----',(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccf.id)) SEPARATOR '--++--' ) from  tbl_cricket_contest_matches tccf where  tccf.match_id=tcm.id AND tccf.status='A' AND tccf.is_deleted='N' AND tccf.is_duplicated_created='N' AND tccf.is_private='N' AND tccf.is_beat_the_expert='N' $group_inner_query) as contest_data , (SELECT id from  tbl_cricket_contest_matches tccf1 where  tccf1.match_id=tcm.id AND tccf1.status='A' AND tccf1.is_deleted='N' AND tccf1.is_duplicate_allow='Y' AND tccf1.is_private='N' AND tccf1.is_beat_the_expert='N' AND tccf1.duplicate_count>(select count(id) from tbl_cricket_contest_matches where parent_id=tccf1.id) $parent_group_inner_query) as parent_contest_data from tbl_cricket_matches tcm where tcm.status='A' AND tcm.is_deleted='N' AND tcm.match_progress='F' $inner_query";

         $query_res = $this->conn->prepare($query); 


         if($query_res->execute()){

            while($contestdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                $match_unique_id=$contestdata['match_unique_id'];
                $parent_contest_data=$contestdata['parent_contest_data'];
                if(!empty($parent_contest_data) && !empty($contestdata['contest_data'])){

                        $contests_array=explode("--++--",$contestdata['contest_data']);
                        foreach($contests_array as $contests_array_s){

                             $per_contest=explode("----", $contests_array_s);
                             $total_team=$per_contest[0];
                             $join_team=$per_contest[1];

                             if(($total_team-$join_team)<=0){
                             $this->saveDuplicateContest($parent_match_contest_id,$match_contest_id);

                             }
                        }

                }
            }   

            $this->closeStatement($query_res);

         }else{
            $this->sql_error($query_res);
            $this->closeStatement($query_res);
         } 
}


public function saveDuplicateContest($parent_match_contest_id, $match_contest_id) {
        $get_contest_detail_query = "SELECT * FROM tbl_cricket_contest_matches WHERE id = ?";
        $query_res = $this->conn->prepare($get_contest_detail_query);
        $query_res->bindParam(1, $parent_match_contest_id);
        $query_res->execute();
        $array = $query_res->fetch();
        if (!empty($array)) {
            $parent_id=$array['id'];
            
            $time = time();
            $insert_match_contest_query = "INSERT INTO tbl_cricket_contest_matches SET contest_id = ?, category_id=?, match_id = ?, match_unique_id = ?, total_team = ?, total_price=?, entry_fees=?, actual_entry_fees=?, more_entry_fees=?, max_entry_fees=?, per_user_team_allowed=?, contest_json=?, confirm_win=?, multi_team_allowed=?, confirm_win_contest_percentage=?, pdf=?, pdf_process=?, status=?, user_id=?, is_private=?, is_beat_the_expert=?, team_id=?, entry_fee_multiplier=?, is_duplicate_allow=?, duplicate_count=?, slug=?, is_duplicated_created=?, parent_id=?, is_deleted=?, is_abondant=?, created_at=?, created_by=?, updated_at=?, updated_by=?";
            $booking_taxi_query_res = $this->conn->prepare($insert_match_contest_query);

             $pdo_index=0;
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['contest_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['category_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['match_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['match_unique_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['total_team']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['total_price']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['entry_fees']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['actual_entry_fees']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['more_entry_fees']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['max_entry_fees']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['per_user_team_allowed']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['contest_json']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['confirm_win']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['multi_team_allowed']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['confirm_win_contest_percentage']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['pdf']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['pdf_process']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['status']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['user_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['is_private']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['is_beat_the_expert']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['team_id']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['entry_fee_multiplier']);
             $is_duplicate_allow="N";
             $is_duplicated_created="N";
             $duplicate_count="0";
             $slug="";
             $booking_taxi_query_res->bindParam(++$pdo_index,$is_duplicate_allow);
             $booking_taxi_query_res->bindParam(++$pdo_index,$duplicate_count);
             $booking_taxi_query_res->bindParam(++$pdo_index,$slug);
             $booking_taxi_query_res->bindParam(++$pdo_index,$is_duplicated_created);
             $booking_taxi_query_res->bindParam(++$pdo_index,$parent_id);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['is_deleted']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['is_abondant']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$time);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['created_by']);
             $booking_taxi_query_res->bindParam(++$pdo_index,$time);
             $booking_taxi_query_res->bindParam(++$pdo_index,$array['updated_by']);
            
           
                      
            if ($booking_taxi_query_res->execute()) {

                $id=$this->conn->lastInsertId();

                $update_query = "UPDATE  tbl_cricket_contest_matches SET is_duplicated_created='Y' where id='$match_contest_id'";
                $update_query_res  = $this->conn->prepare($update_query);               
                $update_query_res->execute();   
                $this->closeStatement($update_query_res);


                

                $slug=$this->generateRandomString(12).$id."_";

                $save_user_query = "UPDATE tbl_cricket_contest_matches SET slug=? WHERE id=?";                
                $save_user  = $this->conn->prepare($save_user_query);       
                $save_user->bindParam(1,$slug);
                $save_user->bindParam(2,$id); 
                $save_user->execute();
                $this->closeStatement($save_user);


                            
                return $id;
            }
        }
        return 0;
}
    
    public function customer_pre_join_contest($customer_id,$match_unique_id,$match_contest_id,$entry_fees=0,$customer_team_ids){

        $customer_team_ids_explode=explode(',', $customer_team_ids);
        $customer_team_ids_count=count($customer_team_ids_explode);

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        $output=array();
        $output['code']="";
        $output['msg']="";
        
        if(empty($get_match_data)){
            $output['code']="NO_MATCH_FOUND";
            $output['msg']="Invalid match.";
            return $output;
        }
        
        if($get_match_data['match_progress']!='F'){
            $output['code']="INVALID_MATCH";
            $output['msg']="The deadline has passed! Check out the contests you've joined for this match.";
            return $output;

            
        }


        $customerWalletData=$this->get_customer_wallet_detail($customer_id);
        if(empty($customerWalletData)){
             $output['code']="INVALID_WALLET";
             $output['msg']="Invalid Wallet.";
            return $output;

        }


          $query = "SELECT id FROM tbl_cricket_customer_teams WHERE id IN ($customer_team_ids) AND customer_id=$customer_id";
        $query_res = $this->conn->prepare($query);
       // $query_res->bindParam(1, $customer_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows!=$customer_team_ids_count){
           
             $output['code']="NO_TEAM_FOUND";
             $output['msg']="No Team Found";
            return $output;
        }


        $query = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND customer_team_id IN ($customer_team_ids) AND match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_contest_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows>0){
            $output['code']="TEAM_ALREADY_JOINED";
             $output['msg']="Team Already Joined.";
            return $output;
        }


        $depositWallet=$customerWalletData['wallet']['deposit_wallet'];
        $winningWallet=$customerWalletData['wallet']['winning_wallet'];
        $bonusWallet=$customerWalletData['wallet']['bonus_wallet'];



        /*$query_mc = "SELECT tccm.id,tccm.entry_fees, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type FROM tbl_cricket_contest_matches tccm INNER JOIN tbl_cricket_contests tcc ON tcc.id=tccm.contest_id LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tcc.category_id WHERE tccm.id = ? AND tccm.status='A' AND tccm.is_deleted='N' AND tcc.status='A' AND tcc.is_deleted='N'";*/

        $query_mc = "SELECT tccm.id,tccm.multi_team_allowed,tccm.entry_fees,tccm.max_entry_fees,tccm.is_beat_the_expert, tccc.cash_bonus_used_value, tccc.cash_bonus_used_type, tccm.total_team,tccm.per_user_team_allowed,(SELECT count(tccc_contest.id) FROM tbl_cricket_customer_contests tccc_contest WHERE tccc_contest.match_contest_id=tccm.id) as total_joined_teams_count, (SELECT count(tccc_contestt.id) FROM tbl_cricket_customer_contests tccc_contestt WHERE tccc_contestt.match_contest_id=tccm.id AND tccc_contestt.customer_id=?) as customer_joined_teams_count FROM tbl_cricket_contest_matches tccm LEFT JOIN tbl_cricket_contest_categories tccc ON tccc.id=tccm.category_id WHERE tccm.id = ? AND tccm.status='A' AND tccm.is_deleted='N'";


       

        $query_mcres = $this->conn->prepare($query_mc);
        $query_mcres->bindParam(1, $customer_id);
        $query_mcres->bindParam(2, $match_contest_id);
        $query_mcres->execute();
        $num_rows_mc =$query_mcres->rowCount();

        if($num_rows_mc==0){
            $this->closeStatement($query_mcres);


            $output['code']="NO_CONTEST_FOUND";
            $output['msg']="No contest found.";
            return $output;
        }


        $used_bonus=0;
        $used_deposit=0;
        $used_winning=0;
        
        //$settingData=$this->get_setting_data();
        $BONUS_WALLET_PER=0;
        $BONUS_WALLET_PER_TYPE="P";
        //       if(!empty($settingData['CASH_BONUS_PERCENTAGES'])){
        //  $BONUS_WALLET_PER=$settingData['CASH_BONUS_PERCENTAGES'];
        // }


        $contestData = $query_mcres->fetch();

        $total_team=$contestData['total_team'];
        $per_user_team_allowed=$contestData['per_user_team_allowed'];
        $total_joined_teams_count=$contestData['total_joined_teams_count'];
        $customer_joined_teams_count=$contestData['customer_joined_teams_count'];

        if($contestData['multi_team_allowed']=="N" && $customer_team_ids_count>1){

            $output['code']="MULTI_TEAM_NOT_ALLOWED";
            $output['msg']="Multi team not allowed.";
            return $output;

        }

        if(($total_joined_teams_count+$customer_team_ids_count)>$total_team){
            if(($total_team-$total_joined_teams_count)<=0){
            $this->create_duplicate_match_contest($match_unique_id,$match_contest_id);

            }


            $output['code']="CONTEST_FULL";
            $output['msg']="Oops! Only ".($total_team-$total_joined_teams_count)." spot Left.";
            return $output;
           
        }

        if(($customer_joined_teams_count+$customer_team_ids_count)>$per_user_team_allowed){

            $output['code']="PER_USER_TEAM_ALLOWED_LIMIT";
            $output['msg']="Oops! You can select Only ".($per_user_team_allowed-$customer_joined_teams_count)." teams.";
            return $output;

            
        }


        $this->closeStatement($query_mcres);

        if($contestData['is_beat_the_expert']=="Y"){

            if($contestData['entry_fees']<=$entry_fees && $contestData['max_entry_fees']>=$entry_fees){

                $entry_fees=$entry_fees;
            }else{


                $output['code']="INVALID_ENTRY_FEE";
                $output['msg']="Invalid Entry Fees.";
                return $output;
            }


        }else{

            $entry_fees=$contestData['entry_fees'];
        }

        $cash_bonus_used_value=$contestData['cash_bonus_used_value'];
        $cash_bonus_used_type=$contestData['cash_bonus_used_type'];

        $BONUS_WALLET_PER=$cash_bonus_used_value;
        $BONUS_WALLET_PER_TYPE=$cash_bonus_used_type;

        $match_contest_id=$contestData['id'];
        //$entry_fees=$contestData['entry_fees'];
        $entry_fees=$entry_fees*$customer_team_ids_count;
        
        $need_pay=$entry_fees;

        if($BONUS_WALLET_PER_TYPE=="F" && $BONUS_WALLET_PER>$need_pay){
            $BONUS_WALLET_PER=$need_pay;
        }

        if($need_pay>0){
            if($bonusWallet>0){
                $used_bonus=$entry_fees*($BONUS_WALLET_PER/100);
                if($BONUS_WALLET_PER_TYPE=="F"){
                    $used_bonus=$BONUS_WALLET_PER;
                }
                $used_bonus=round($used_bonus,2);
                if($used_bonus>$bonusWallet){
                    $used_bonus=$bonusWallet;
                }
                $need_pay-=$used_bonus;
            }
            if($need_pay>0){
                if($depositWallet>0){
                    $used_deposit=$need_pay;
                    if($used_deposit>$depositWallet){
                        $used_deposit=$depositWallet;
                    }
                    $need_pay-=$used_deposit;
                }
            }
            if($need_pay>0){
                if($winningWallet>0){
                    $used_winning=$need_pay;
                    if($used_winning>$winningWallet){
                        $used_winning=$winningWallet;
                    }
                    $need_pay-=$used_winning;
                }
            }
        }
        
        
        $output['wallet']=$customerWalletData['wallet'];
        $output['used_bonus']=$used_bonus;
        $output['used_deposit']=$used_deposit;
        $output['used_winning']=$used_winning;
        $output['need_pay']=$need_pay;
        $output['entry_fees']=$entry_fees;
        $output['to_pay']=$entry_fees-$used_bonus;
        
        $amount_suggest=array();
        if($need_pay>0){
            $a = ((int)($need_pay / 10)) * 10; 
            if($a<$need_pay){
                $a = $a + 10; 
            }
            
            $aa=$a*2;
            $aaa=($aa*2)+$a;
            
            $amount_suggest[0]=$a;
            $amount_suggest[1]=$aa;
            $amount_suggest[2]=$aaa;
            
        }
        $output['amount_suggest']=$amount_suggest;
        
        return $output;

    }


   
    public function getStateDetail($stateid){
            $get_state_query = "SELECT id, name FROM tbl_states WHERE id=?";
            $query  = $this->conn->prepare($get_state_query);
           
            $query->bindParam(1,$stateid);
            
            $query->execute();
            $num_rows =$query->rowCount();
            $output = array();
            if ($num_rows > 0) {
               $array = $query->fetch();
               $this->closeStatement($query);

               $output['id'] = $array['id'];
               $output['name'] = $array['name'];
            }else{
                $this->closeStatement($query);
            }
            return $output;
    }

    public function getStates($countryid=0) {
        $get_state_query = "SELECT id, name FROM tbl_states WHERE status  = 'A' AND is_deleted='N'";
        if ($countryid!=0) {
            $get_state_query .= " AND country_id=?";
        }
        $get_state_query .= " ORDER BY name ASC";
        $query  = $this->conn->prepare($get_state_query);
        if ($countryid!=0) {
             $query->bindParam(1,$countryid);
        }
        $query->execute();
        $num_rows =$query->rowCount();
        if ($num_rows > 0) {
            $output = array();
            $counter = 0;
            while ($array = $query->fetch()) {
                $output[$counter]['id'] = $array['id'];
                $output[$counter]['name'] = $array['name'];
                $counter++;
            }
            $this->closeStatement($query);
            return $output;
        } else {
            $this->closeStatement($query);
            return 'NO_RECORD';
        }
    }


     public function getCities($state_id) {
        $response = array();
        $get_city_query = "SELECT id,name FROM tbl_cities WHERE city_boundaries.status='A' AND is_deleted='N'";
        
        if ($state_id > 0) {
            $get_city_query .= " AND state_id =?";
        }
        
        $get_city_query .= " ORDER BY name ASC";
        $query = $this->conn->prepare($get_city_query);
        
        if ($state_id > 0) {
            $query->bindParam(1,$state_id);
        }        
        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                $i=0;
                while ($array = $query->fetch()) {
                    $response[$i]['id'] = $array['id'];
                    $response[$i]['name'] = $array['name'];                    
                    $i++;
                }
                $this->closeStatement($query);
            } else {
                $this->closeStatement($query);
              return 'NO_RECORD';
            }
        } else {
            $this->closeStatement($query);
            return 'UNABLE_TO_PROCEED';
        }
        return $response;
    }


    public function update_profile($customer_id, $firstname, $lastname, $email, $phone, $country_mobile_code, $dob, $country, $state, $city, $addressline1, $addressline2,$pincode,$gender) {
        
        if ($this->isPhoneExists($phone,$customer_id,$country_mobile_code) > 0) {
           return 'PHONE_ALREADY_EXISTED';
        }   
        if ($this->isEmailExists($email, $customer_id) == 0) {
            $time = time();
            $select_user_query = "UPDATE tbl_customers SET firstname=?, lastname=?, email=?, modified=?, phone=?, country_mobile_code=?, dob=?, country=?, state=?, city=?, addressline1=?, addressline2=?, pincode=? ,gender=?";
           
            $select_user_query .= " WHERE id=?";

            $select_user  = $this->conn->prepare($select_user_query);
            $select_user->bindParam(1,$firstname);
            $select_user->bindParam(2,$lastname);
            $select_user->bindParam(3,$email);
            $select_user->bindParam(4,$time);
            $select_user->bindParam(5,$phone);
            $select_user->bindParam(6,$country_mobile_code);
            $select_user->bindParam(7,$dob);
            $select_user->bindParam(8,$country);
            $select_user->bindParam(9,$state);
            $select_user->bindParam(10,$city);
            $select_user->bindParam(11,$addressline1);
            $select_user->bindParam(12,$addressline2);
            $select_user->bindParam(13,$pincode); 
                   $select_user->bindParam(14,$gender); 
     $select_user->bindParam(15,$customer_id); 
            if ($select_user->execute()) {
                $this->closeStatement($select_user);
               return $this->getUpdatedProfileData($customer_id);
            } else {
                $this->closeStatement($select_user);
                return 'UNABLE_TO_PROCEED';
            }
        } else {
            return 'EMAIL_ALREADY_EXISTED';
        }
    }



    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public function update_verify_email($customer_id, $email, $is_social, $social_type) {
        
        if ($this->isEmailExists($email, $customer_id) == 0) {
            $is_email_verified="N";
            if($is_social=="Y"){
                $is_email_verified="Y";
            }

            $token=$this->generateRandomString().md5($customer_id);
            $time = time();
            $select_user_query = "UPDATE tbl_customers SET email=?, is_social=?, social_type=?, is_email_verified=?, modified=?,email_token=?,email_token_at=?  WHERE id=?";

            $select_user  = $this->conn->prepare($select_user_query);
            $select_user->bindParam(1,$email);
            $select_user->bindParam(2,$is_social);
            $select_user->bindParam(3,$social_type);
            $select_user->bindParam(4,$is_email_verified);
            $select_user->bindParam(5,$time);
            $select_user->bindParam(6,$token);
            $select_user->bindParam(7,$time);

            $select_user->bindParam(8,$customer_id);
            if ($select_user->execute()) {

                $this->closeStatement($select_user);


                $customer_detail=$this->getUpdatedProfileData($customer_id); 

                if($is_email_verified=="N"){

                $data=array();
                $data['link']=APP_URL."email_verification/index/".$token;
                $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                $this->sendTemplatesInMail('email_verification_link', trim($full_name), $email,$data);
                    
                }


               return $customer_detail;
            }

            $this->closeStatement($select_user);
            return 'UNABLE_TO_PROCEED';
            
        }
        
        return 'EMAIL_ALREADY_EXISTED';
        
    }


    public function send_otp_mobile($country_mobile_code, $phone,$customer_id) {


        if ($this->isPhoneExists($phone,$customer_id,$country_mobile_code) > 0) {
           return 'PHONE_ALREADY_EXISTED';
        }


        $otpCode=$this->sendotp($phone, 'SP', $country_mobile_code, "");
        $response['otp']=$otpCode;
        $response['phone']=$phone;
        $response['country_mobile_code']=$country_mobile_code;        
      
        
        return $response;
        
    }



    public function update_verify_mobile($otp, $type, $country_mobile_code, $phone, $customer_id) {

        if ($this->isPhoneExists($phone,$customer_id,$country_mobile_code) > 0) {
           return 'PHONE_ALREADY_EXISTED';
        }

        $verify_otp_query = "UPDATE tbl_tempcustomers SET isverified='YES' WHERE otp=? AND type=? AND country_mobile_code=? AND mobileno=?";
        $verify_otp = $this->conn->prepare($verify_otp_query);
        $verify_otp->bindParam(1, $otp);
        $verify_otp->bindParam(2, $type);
        $verify_otp->bindParam(3, $country_mobile_code);
        $verify_otp->bindParam(4, $phone);
        $verify_otp->execute();
        if ($verify_otp->rowCount()) {
            $this->closeStatement($verify_otp);

                $current_time=time();
                $save_user_query = "UPDATE tbl_customers SET country_mobile_code=?, phone=?, is_phone_verified='Y',modified=?  WHERE id=?";                
                $save_user  = $this->conn->prepare($save_user_query);       
                $save_user->bindParam(1,$country_mobile_code);
                $save_user->bindParam(2,$phone); 
                $save_user->bindParam(3,$current_time);
                $save_user->bindParam(4,$customer_id);

               
                if ($save_user->execute()) {
                    $this->closeStatement($save_user);
                    return $this->getUpdatedProfileData($customer_id);
                }else{
                    $this->closeStatement($save_user);
                    return 'UNABLE_TO_PROCEED';
                }
           
           
        } else {
            $this->closeStatement($verify_otp);
            return 'INVALID_OTP';
        }
    }


    public function customer_withdraw_amount($customer_id,$amount) {
		
		$settingData=$this->get_setting_data();

		$WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
		$WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];
		
		if ($amount < $WITHDRAW_AMOUNT_MIN || $amount > $WITHDRAW_AMOUNT_MAX) {
                return 'MIN_MAX_FAILED';
        }

        $customer_info=$this->getUpdatedProfileData($customer_id);
        if(empty($customer_info['pancard']) || empty($customer_info['bankdetail'])
			|| $customer_info['pancard']['status']!="A" || $customer_info['bankdetail']['status']!="A"){
			return 'INVALID_DOCUMENT';
		}

        // $query="SELECT sum(amount) as pending_amount from tbl_withdraw_requests where status='P' AND customer_id=?";
        // $query_res  = $this->conn->prepare($query);
        // $query_res->bindParam(1,$customer_id);
        // $query_res->execute();
        // $pending = $query_res->fetch(PDO::FETCH_ASSOC);
        // $this->closeStatement($query_res);

        if ($amount<=0) {
           return 'INVALID_AMOUNT';
        }

        if ($amount > (str_replace(',','',$customer_info['wallet']['winning_wallet']))) {
           return 'INSUFFICIENT_AMOUNT';
        }
$customer_info['wallet']['winning_wallet'] = str_replace(',','',$customer_info['wallet']['winning_wallet']);
        $current_time=time();
        $transaction_id="WIDWWALL-".$current_time.$customer_id;

        $save_user_query = "INSERT INTO tbl_withdraw_requests SET customer_id=?, amount=?,transaction_id=?, created_at =?";
        $save_user  = $this->conn->prepare($save_user_query);
        $save_user->bindParam(1,$customer_id);
        $save_user->bindParam(2,$amount);
        $save_user->bindParam(3,$transaction_id);
        $save_user->bindParam(4,$current_time);


        if (!$save_user->execute()) {
            $this->closeStatement($save_user);
             return 'UNABLE_TO_PROCEED';
        }
        $this->closeStatement($save_user);


    }



    public function get_withdraw_entry_id($entry_id) {

       $query = "SELECT tbr.*,tc.rz_contact_id,tc.rz_fund_account_id,tc.winning_wallet, tc.slug, tc.firstname, tc.lastname, tc.email, tc.country_mobile_code, tc.phone,tcp.id as paincard_id, tcp.status as pan_status,tcbd.id as bankdetail_id,tcbd.account_number as bank_account_number, tcbd.name as account_holder_name, tcbd.ifsc as bank_ifsc, tcbd.status as bank_status from tbl_withdraw_requests tbr  LEFT JOIN tbl_customers tc ON tc.id=tbr.customer_id LEFT JOIN tbl_customer_paincard tcp ON tcp.id=tc.paincard_id LEFT JOIN tbl_customer_bankdetail tcbd ON tcbd.id=tc.bankdetail_id WHERE tbr.status='P' AND tbr.id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $entry_id);

        $output = array();
        if ($query_res->execute()) {
            $profiledata = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);
            if(empty($profiledata)){
                return $output;
            }
            $output['slug'] = $this->base64_encode($profiledata['slug']);
            $output['amount'] = $profiledata['amount'];
            $output['id'] = $profiledata['customer_id'];
            $output['firstname'] = $profiledata['firstname'];
            $output['lastname'] = $profiledata['lastname'];
            $output['email'] = $profiledata['email'];
            $output['country_mobile_code'] = $profiledata['country_mobile_code'];
            $output['phone'] = $profiledata['phone'];
            $output['referenceId'] = $profiledata['referenceId'];
            $output['utr'] = $profiledata['utr'];
            $output['rz_contact_id'] = $profiledata['rz_contact_id'];
            $output['rz_fund_account_id'] = $profiledata['rz_fund_account_id'];







            $output['pancard']=NULL;
            if(!empty($profiledata['paincard_id'])){
                $panDetail=array();
                $panDetail['id']=$profiledata['paincard_id'];
                $panDetail['status']=$profiledata['pan_status'];


                $output['pancard'] = $panDetail;
            }

            $output['bankdetail']=NULL;
            if(!empty($profiledata['bankdetail_id'])){
                $bankDetail=array();
                $bankDetail['id']=$profiledata['bankdetail_id'];
                $bankDetail['account_number']=$profiledata['bank_account_number'];
                $bankDetail['account_holder_name']=$profiledata['account_holder_name'];
                $bankDetail['ifsc']=$profiledata['bank_ifsc'];
                $bankDetail['status']=$profiledata['bank_status'];
                $output['bankdetail'] = $bankDetail;
            }





            $actual_winning_balance=$profiledata['winning_wallet'];
            if($actual_winning_balance<0){
                $actual_winning_balance=0;
            }

            $output['wallet']  = array('winning_wallet'=>$actual_winning_balance);
        }else{
            $this->sql_error($query_res);
        }
        return $output;
    }

    public function customer_withdraw_amount_from_bank($entry_id,$action,$reason) {
        $this->withdraw_request_approve_reject_logs(1,$entry_id,$action);
        if(PAYOUT_GETWAY=="RAZORPAY"){
            $output=$this->customer_withdraw_amount_from_bank_razorpay($entry_id,$action,$reason);

        }else if(PAYOUT_GETWAY=="CASHFREE"){
            $output=$this->customer_withdraw_amount_from_bank_cashfree($entry_id,$action,$reason);

        }else if(PAYOUT_GETWAY=="DIRECT"){
             $output=$this->customer_withdraw_amount_from_bank_direct($entry_id,$action,$reason);

        }

        return $output;

    } 
    public function withdraw_request_approve_reject_logs($admin_id,$entry_id,$action){
            $time=time();
            $ip_address=$_SERVER['REMOTE_ADDR'];
            $update_user_query = "INSERT INTO tbl_withdraw_request_approve_reject_logs SET admin_id = ?,entry_id = ?,action = ?,ip_address = ?, created_at=? ";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$admin_id);
            $update_user_query_res->bindParam(2,$entry_id);
            $update_user_query_res->bindParam(3,$action);
            $update_user_query_res->bindParam(4,$ip_address);
            $update_user_query_res->bindParam(5,$time);
            $update_user_query_res->execute();
    }

    public function update_entry_status_by_entry_id($entry_id,$status){

        $update_user_query = "UPDATE tbl_withdraw_requests SET status = ? WHERE id = ?";
        $update_user_query_res  = $this->conn->prepare($update_user_query);
        $update_user_query_res->bindParam(1,$status);
        $update_user_query_res->bindParam(2,$entry_id);        
        $update_user_query_res->execute();

    }

    public function customer_withdraw_amount_from_bank_direct($entry_id,$action,$reason) {

        $entry_detail=$this->get_withdraw_entry_id($entry_id);
        $action_time=time();

        $output=array();
        $output['error_code']="";
        $output['error_message']="";

        if(empty($entry_detail)){
            $output['error_code']="NO_RECORD";
            $output['error_message']="Entry detail not found.";
            return $output;
        }
        $this->update_entry_status_by_entry_id($entry_id,"RP");
        if($action=="A"){
            $settingData=$this->get_setting_data();

            $amount=$entry_detail['amount'];
            $WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
            $WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];

            if ($amount<1) {
                    $output['error_code']="INVALID_AMOUNT";
                    $output['error_message']="Invalid withdraw amount.";
                    return $output;
            }

            if ($amount > ($entry_detail['wallet']['winning_wallet'])) {

                $output['error_code']="INSUFFICIENT_AMOUNT";
                $output['error_message']="Insufficient amount in customer wallet.";
                return $output;

            }

          

            if(empty($entry_detail['pancard']) || empty($entry_detail['bankdetail'])
            || $entry_detail['pancard']['status']!="A" || $entry_detail['bankdetail']['status']!="A"){

                $output['error_code']="INVALID_DOCUMENT";
                $output['error_message']="Pan or Bank detail not proper or not approved.";
                return $output;

            }



            
            $new_status="C";
            $return_message="Transaction approved successfully.";             

            $referenceId="direct";
            $utr="";
            $json_data=json_encode(array());
            $this->insert_payout_log($entry_detail['id'],$entry_id,$referenceId,$json_data);            

            $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,json_data = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$referenceId);
            $update_user_query_res->bindParam(3,$utr);
            $update_user_query_res->bindParam(4,$json_data);
            $update_user_query_res->bindParam(5,$action_time);
            $update_user_query_res->bindParam(6,$entry_id);

            if ($update_user_query_res->execute()) {

                if($new_status=="C"){

                    $this->update_customer_wallet($entry_detail['id'],0,"winning_wallet",$amount,"DEBIT","WALLET_WITHDRAW_ADMIN","ADMIN-C-".time(),"Paid By Admin",0,0,true,0,$referenceId,$json_data);

                    $alert_message  = "Congratulations! Your withdraw request has been approved";
                    $noti_type      = "withdraw_request_approved";
                    $notification_data=array();
                    $notification_data['noti_type']=$noti_type;
                    $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                }



                $output['error_code']="";
                $output['error_message']=$return_message;
                return $output;

            }else{

            $output['error_code']="UNABLE_TO_PROCEED";
            $output['error_message']="quey execution error.";
            return $output;

            }



        }elseif($action=="R"){

            $new_status="R";            
            $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,reason = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$reason);
            $update_user_query_res->bindParam(3,$action_time);
            $update_user_query_res->bindParam(4,$entry_id);
            if ($update_user_query_res->execute()) {

                $alert_message  = $reason;
                $noti_type      = "withdraw_request_rejected";
                $notification_data=array();
                $notification_data['noti_type']=$noti_type;
                $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                $output['error_code']="";
                $output['error_message']="Transaction rejected successfully.";
                return $output;

            }else{

            $output['error_code']="UNABLE_TO_PROCEED";
            $output['error_message']="quey execution error.";
            return $output;

            }

        }else{

            $output['error_code']="INVALID_ACTION";
            $output['error_message']="Action must be A or R.";
            return $output;

        }

    }


    public function customer_withdraw_amount_from_bank_cashfree($entry_id,$action,$reason) {
        $entry_detail=$this->get_withdraw_entry_id($entry_id);
        $action_time=time();
        $output=array();
        $output['error_code']="";
        $output['error_message']="";

        if(empty($entry_detail)){
            $output['error_code']="NO_RECORD";
            $output['error_message']="Entry detail not found.";
            return $output;
        }
        $this->update_entry_status_by_entry_id($entry_id,"RP");
        if($action=="A"){
            $settingData=$this->get_setting_data();
            $amount=$entry_detail['amount'];
            $WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
            $WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];

            if ($amount<=1) {
                    $output['error_code']="INVALID_AMOUNT";
                    $output['error_message']="Invalid withdraw amount.";
                    return $output;
            }

            if ($amount > ($entry_detail['wallet']['winning_wallet'])) {

                $output['error_code']="INSUFFICIENT_AMOUNT";
                $output['error_message']="Insufficient amount in customer wallet.";
                return $output;

            }

            if ($amount < $WITHDRAW_AMOUNT_MIN || $amount > $WITHDRAW_AMOUNT_MAX) {
                $output['error_code']="MIN_MAX_FAILED";
                $output['error_message']="min ".$WITHDRAW_AMOUNT_MIN." & max ".$WITHDRAW_AMOUNT_MAX." allowed";
                return $output;

            }

            if(empty($entry_detail['pancard']) || empty($entry_detail['bankdetail'])
            || $entry_detail['pancard']['status']!="A" || $entry_detail['bankdetail']['status']!="A"){

                $output['error_code']="INVALID_DOCUMENT";
                $output['error_message']="Pan or Bank detail not proper or not approved.";
                return $output;

            }


            $this->includecashfreepayout();

            $clientId = CASHFREE_PAYOUT_CLIENT_ID;
            $clientSecret = CASHFREE_PAYOUT_CLIENT_SECRET;
            $stage = CASHFREE_PAYOUT_STAGE;

            $authParams["clientId"] = $clientId;
            $authParams["clientSecret"] = $clientSecret;
            $authParams["stage"] = $stage;

            try {
              $payout = new CfPayout($authParams);
            } catch (Exception $e) {
            $output['error_code']="UNABLE_TO_PROCEED";
            $output['error_message']=$e->getMessage();
            return $output;

            }

           /* if(!empty($entry_detail['referenceId'])){
                $transfer_responce=$payout->getTransferStatus($entry_detail['referenceId']);



            }*/


            $beneficiary = [];
            $beneficiary["beneId"] = $entry_detail['id'].'_'.$entry_detail['bankdetail']['account_number'];
            $beneficiary["name"] = $entry_detail['bankdetail']['account_holder_name'];
            $beneficiary["email"] = $entry_detail['email'];
            $beneficiary["phone"] = $entry_detail['phone'];
            $beneficiary["bankAccount"] = $entry_detail['bankdetail']['account_number'];
            $beneficiary["ifsc"] = $entry_detail['bankdetail']['ifsc'];


            $beneficiaryDetail=$payout->isBeneficiaryExist($beneficiary["beneId"]);
            if($beneficiaryDetail['status']=="FAILED"){
                $output['error_code']="UNABLE_TO_PROCEED";
                $output['error_message']=$beneficiaryDetail['message'];
                return $output;
            }

            if($beneficiaryDetail['status']=="ERROR" && $beneficiaryDetail['subCode']=="404"){
                $response = $payout->addBeneficiary($beneficiary);
                if ($response["status"] != "SUCCESS") {
                    $output['error_code']="UNABLE_TO_PROCEED";
                    $output['error_message']=$response["message"];
                    return $output;
                }
                $beneficiaryDetail['status']=="SUCCESS";
            }

            if($beneficiaryDetail['status']!="SUCCESS"){
                $output['error_code']="UNABLE_TO_PROCEED";
                $output['error_message']=$beneficiaryDetail['message'];
                return $output;
            }



            $transfer = [];
            $transfer["beneId"] = $beneficiary["beneId"];
            $transfer["amount"] = $amount;
            $transfer["transferId"] = "WID_".rand(1, 1000)."_".time();
            $transfer["remarks"] = "Transfer request from ".$entry_detail['account_holder_name'];
            $responseTransfer = $payout->requestTransfer($transfer);

            if($responseTransfer['status']!="SUCCESS" && $responseTransfer['status']!="PENDING"){
                $output['error_code']="UNABLE_TO_PROCEED";
                $output['error_message']=$responseTransfer['message'];
                return $output;
            }

            $new_status="C";
            $return_message="Transaction success.";
            if($responseTransfer['status']=="PENDING"){
            $new_status="H";
            $return_message="Transaction pending.";
            }

            $referenceId=$responseTransfer['data']['referenceId'];
            $utr=$responseTransfer['data']['utr'];
            $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$referenceId);
            $update_user_query_res->bindParam(3,$utr);
            $update_user_query_res->bindParam(4,$action_time);
            $update_user_query_res->bindParam(5,$entry_id);
            if ($update_user_query_res->execute()) {

                if($new_status=="C"){

                $this->update_customer_wallet($entry_detail['id'],0,"winning_wallet",$amount,"DEBIT","WALLET_WITHDRAW_ADMIN","ADMIN-C-".time(),"Paid By Admin",0,0,true,0,$referenceId,null);

                $alert_message  = "Congratulations! Your withdraw request has been approved";
                $noti_type      = "withdraw_request_approved";
                $notification_data=array();
                $notification_data['noti_type']=$noti_type;
                $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                }



                $output['error_code']="";
                $output['error_message']=$return_message;
                return $output;

            }



        }elseif($action=="R"){

            $new_status="R";

             $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,reason = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$reason);
            $update_user_query_res->bindParam(3,$action_time);
            $update_user_query_res->bindParam(4,$entry_id);
            if ($update_user_query_res->execute()) {

                $alert_message  = $reason;
                $noti_type      = "withdraw_request_rejected";
                $notification_data=array();
                $notification_data['noti_type']=$noti_type;
                $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                $output['error_code']="";
                $output['error_message']="Transaction rejected successfully.";
                return $output;

            }

        }else{

            $output['error_code']="INVALID_ACTION";
            $output['error_message']="Action must be A or R.";
            return $output;

        }





        /*$settingData=$this->get_setting_data();

        $WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
        $WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];

        if ($amount < $WITHDRAW_AMOUNT_MIN || $amount > $WITHDRAW_AMOUNT_MAX) {
                return 'MIN_MAX_FAILED';
        }

        $customer_info=$this->getUpdatedProfileData($customer_id);
        if(empty($customer_info['pancard']) || empty($customer_info['bankdetail'])
            || $customer_info['pancard']['status']!="A" || $customer_info['bankdetail']['status']!="A"){
            return 'INVALID_DOCUMENT';
        }

        // $query="SELECT sum(amount) as pending_amount from tbl_withdraw_requests where status='P' AND customer_id=?";
        // $query_res  = $this->conn->prepare($query);
        // $query_res->bindParam(1,$customer_id);
        // $query_res->execute();
        // $pending = $query_res->fetch(PDO::FETCH_ASSOC);
        // $this->closeStatement($query_res);

        if ($amount<=0) {
           return 'INVALID_AMOUNT';
        }

        if ($amount > ($customer_info['wallet']['winning_wallet'])) {
           return 'INSUFFICIENT_AMOUNT';
        }

        $current_time=time();
        $transaction_id="WIDWWALL-".$current_time.$customer_id;

        $save_user_query = "INSERT INTO tbl_withdraw_requests SET customer_id=?, amount=?,transaction_id=?, created_at =?";
        $save_user  = $this->conn->prepare($save_user_query);
        $save_user->bindParam(1,$customer_id);
        $save_user->bindParam(2,$amount);
        $save_user->bindParam(3,$transaction_id);
        $save_user->bindParam(4,$current_time);


        if (!$save_user->execute()) {
            $this->closeStatement($save_user);
             return 'UNABLE_TO_PROCEED';
        }
        $this->closeStatement($save_user);*/

       
    }

    public function customer_withdraw_amount_from_bank_razorpay($entry_id,$action,$reason) {
        $entry_detail=$this->get_withdraw_entry_id($entry_id);
        $action_time=time();
        $output=array();
        $output['error_code']="";
        $output['error_message']="";

        if(empty($entry_detail)){
            $output['error_code']="NO_RECORD";
            $output['error_message']="Entry detail not found.";
            return $output;
        }
        $this->update_entry_status_by_entry_id($entry_id,"RP");
        if($action=="A"){
            $settingData=$this->get_setting_data();
            $amount=$entry_detail['amount'];
            $WITHDRAW_AMOUNT_MIN=$settingData['MIN_WITHDRAWALS'];
            $WITHDRAW_AMOUNT_MAX=$settingData['MAX_WITHDRAWALS'];

            if ($amount<1) {
                    $output['error_code']="INVALID_AMOUNT";
                    $output['error_message']="Invalid withdraw amount.";
                    return $output;
            }

            if ($amount > ($entry_detail['wallet']['winning_wallet'])) {

                $output['error_code']="INSUFFICIENT_AMOUNT";
                $output['error_message']="Insufficient amount in customer wallet.";
                return $output;

            }

            /*if ($amount < $WITHDRAW_AMOUNT_MIN || $amount > $WITHDRAW_AMOUNT_MAX) {
                $output['error_code']="MIN_MAX_FAILED";
                $output['error_message']="min ".$WITHDRAW_AMOUNT_MIN." & max ".$WITHDRAW_AMOUNT_MAX." allowed";
                return $output;

            }*/

            if(empty($entry_detail['pancard']) || empty($entry_detail['bankdetail'])
            || $entry_detail['pancard']['status']!="A" || $entry_detail['bankdetail']['status']!="A"){

                $output['error_code']="INVALID_DOCUMENT";
                $output['error_message']="Pan or Bank detail not proper or not approved.";
                return $output;

            }


            $this->includerazorpaypayout();
             
            $clientId = RAZORPAY_PAYOUT_CLIENT_ID;
            $clientSecret = RAZORPAY_PAYOUT_CLIENT_SECRET;
            

            $authParams["clientId"] = $clientId;
            $authParams["clientSecret"] = $clientSecret;
           

            try {
              $payout = new RfPayout($authParams);
            } catch (Exception $e) {
            $output['error_code']="UNABLE_TO_PROCEED";
            $output['error_message']=$e->getMessage();
            return $output;

            }

           /* if(!empty($entry_detail['referenceId'])){
                $transfer_responce=$payout->getTransferStatus($entry_detail['referenceId']);



            }*/

           

            if(empty($entry_detail['rz_contact_id'])){
                $contact=array();
                $contact['name']=$entry_detail['firstname']." ".$entry_detail['lastname'];
                if(!empty($entry_detail['email'])){
                   $contact['email']=$entry_detail['email'];
                }
                if(!empty($entry_detail['phone'])){
                   $contact['contact']=$entry_detail['phone'];
                }
                $contact['type']="customer";
                $contact['reference_id']=$entry_detail['id'];
                $contactDetail=$payout->addContact($contact);
                if(isset($contactDetail['error'])){
                    $output['error_code']=$contactDetail['error']['code'];
                    $output['error_message']=$contactDetail['error']['description'];
                    return $output;

                }else{
                    $entry_detail['rz_contact_id']=$contactDetail['id'];

                    $update_user_query1 = "UPDATE tbl_customers SET rz_contact_id = ? WHERE id = ?";
                    $update_user_query_res1  = $this->conn->prepare($update_user_query1);
                    $update_user_query_res1->bindParam(1,$entry_detail['rz_contact_id']);
                    $update_user_query_res1->bindParam(2,$entry_detail['id']);
                    $update_user_query_res1->execute();                  

                }

            }



            if(empty($entry_detail['rz_fund_account_id'])){

                $fund_account=array();
                $fund_account['contact_id']=$entry_detail['rz_contact_id'];
                $fund_account['account_type']= "bank_account";
                $fund_account['bank_account']['name']=$entry_detail['bankdetail']['account_holder_name'];
                $fund_account['bank_account']['ifsc']=$entry_detail['bankdetail']['ifsc'];
                $fund_account['bank_account']['account_number']=$entry_detail['bankdetail']['account_number'];

                $FundaccountDetail=$payout->addFundAccount($fund_account);
                if(isset($FundaccountDetail['error'])){
                    $output['error_code']=$FundaccountDetail['error']['code'];
                    $output['error_message']=$FundaccountDetail['error']['description'];
                    return $output;

                }else{
                    $entry_detail['rz_fund_account_id']=$FundaccountDetail['id'];

                    $update_user_query1 = "UPDATE tbl_customers SET rz_fund_account_id = ? WHERE id = ?";
                    $update_user_query_res1  = $this->conn->prepare($update_user_query1);
                    $update_user_query_res1->bindParam(1,$entry_detail['rz_fund_account_id']);
                    $update_user_query_res1->bindParam(2,$entry_detail['id']);
                    $update_user_query_res1->execute();                  

                }


            }




            

           



            $transfer = [];
            $transfer["account_number"] = RAZORPAY_PAYOUT_ACCOUNT_NUMBER;
            $transfer["fund_account_id"] = $entry_detail['rz_fund_account_id'];
            $transfer["amount"] = $amount*100;
            $transfer["currency"] = DEFAULT_CURRENCY;
            $transfer["mode"] = RAZORPAY_PAYOUT_MODE;
            $transfer["purpose"] = "payout";
            $transfer["queue_if_low_balance"] = false;
            $transfer["reference_id"] = $entry_detail['id'].'_'.$entry_detail['bankdetail']['account_number'];
            $transfer["narration"] = "req from ".$entry_detail['firstname']." ".$entry_detail['lastname'];
            $transfer["notes"]["entry_id"] = $entry_id;
            $transfer["notes"]["customer_id"] = $entry_detail['id'];
            $responseTransfer = $payout->CreatePayout($transfer);

            if(isset($responseTransfer['error'])){
                    $output['error_code']=$responseTransfer['error']['code'];
                    $output['error_message']=$responseTransfer['error']['description'];
                    return $output;

            }
            $new_status="C";
            $return_message="Transaction success.";

            if($responseTransfer['status']!="processed"){
                
                if($responseTransfer['status']=="failed" || $responseTransfer['status']=="cancelled"){
                    $output['error_code']="UNABLE_TO_PROCEED";
                    $output['error_message']="Payout failed or cancelled.";
                    return $output;
                }else{
                    $new_status="H";
                    $return_message="Transaction Initiated successfully.";
                }
                
            }           

            $referenceId=$responseTransfer['id'];
            $utr=$responseTransfer['utr'];
            $json_data=json_encode($responseTransfer);
            $this->insert_payout_log($entry_detail['id'],$entry_id,$referenceId,$json_data);
            $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,json_data = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$referenceId);
            $update_user_query_res->bindParam(3,$utr);
            $update_user_query_res->bindParam(4,$json_data);
            $update_user_query_res->bindParam(5,$action_time);
            $update_user_query_res->bindParam(6,$entry_id);
            if ($update_user_query_res->execute()) {

                if($new_status=="C"){

                    $this->update_customer_wallet($entry_detail['id'],0,"winning_wallet",$amount,"DEBIT","WALLET_WITHDRAW_ADMIN","ADMIN-C-".time(),"Paid By Admin",0,0,true,0,$referenceId,$json_data);

                    $alert_message  = "Congratulations! Your withdraw request has been approved";
                    $noti_type      = "withdraw_request_approved";
                    $notification_data=array();
                    $notification_data['noti_type']=$noti_type;
                    $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                }



                $output['error_code']="";
                $output['error_message']=$return_message;
                return $output;

            }



        }elseif($action=="R"){

            $new_status="R";

            $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,reason = ?,action_time=? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$new_status);
            $update_user_query_res->bindParam(2,$reason);
            $update_user_query_res->bindParam(3,$action_time);
            $update_user_query_res->bindParam(4,$entry_id);
            if ($update_user_query_res->execute()) {

                $alert_message  = $reason;
                $noti_type      = "withdraw_request_rejected";
                $notification_data=array();
                $notification_data['noti_type']=$noti_type;
                $this->send_notification_and_save($notification_data,$entry_detail['id'],$alert_message,true);

                $output['error_code']="";
                $output['error_message']="Transaction rejected successfully.";
                return $output;

            }

        }else{

            $output['error_code']="INVALID_ACTION";
            $output['error_message']="Action must be A or R.";
            return $output;

        }
    }

    public function insert_payout_log($customer_id,$entry_id,$payout_id,$json){
        $time=time();

         $update_user_query = "INSERT INTO tbl_withdraw_requests_logs SET payout_id = ?,entry_id = ?,customer_id = ?,json = ?, created=? ";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$payout_id);
            $update_user_query_res->bindParam(2,$entry_id);
            $update_user_query_res->bindParam(3,$customer_id);
            $update_user_query_res->bindParam(4,$json);
            $update_user_query_res->bindParam(5,$time);
            $update_user_query_res->execute();
    }

    public function payout_hook($data){
         $txt = "user id date";
         //$myfile = file_put_contents('logs.txt', print_r($data, true) , FILE_APPEND | LOCK_EX);

        if(!empty($data)){
            $main_array=$data['payload']['payout']['entity'];
            $payout_id=$main_array['id'];
            $status=$main_array['status'];
            $entry_id=$main_array['notes']['entry_id'];
            $customer_id=$main_array['notes']['customer_id'];
            $utr=$main_array['utr'];
            $amount=$main_array['amount']/100;
            $json=json_encode($data);
            $this->insert_payout_log($customer_id,$entry_id,$payout_id,$json);

            if($status=="reversed" || $status=="failed" || $status=="cancelled"){
                if(!empty($main_array['failure_reason'])){
                    $reason=$main_array['failure_reason'];
                }else{
                     $reason="Auto rejected";
                }

                $new_status="R";
                $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,json_data = ?,reason=? WHERE id = ?";
                $update_user_query_res  = $this->conn->prepare($update_user_query);
                $update_user_query_res->bindParam(1,$new_status);
                $update_user_query_res->bindParam(2,$payout_id);
                $update_user_query_res->bindParam(3,$utr);
                $update_user_query_res->bindParam(4,$json);
                $update_user_query_res->bindParam(5,$reason);
                $update_user_query_res->bindParam(6,$entry_id);
                if ($update_user_query_res->execute()) {

                    $alert_message  = $reason;
                    $noti_type      = "withdraw_request_rejected";
                    $notification_data=array();
                    $notification_data['noti_type']=$noti_type;
                    $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);

                }

            }else if($status=="processed"){

                $new_status="C";

                $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,json_data = ? WHERE id = ?";
                $update_user_query_res  = $this->conn->prepare($update_user_query);
                $update_user_query_res->bindParam(1,$new_status);
                $update_user_query_res->bindParam(2,$payout_id);
                $update_user_query_res->bindParam(3,$utr);
                $update_user_query_res->bindParam(4,$json);               
                $update_user_query_res->bindParam(5,$entry_id);
                if ($update_user_query_res->execute()) {

                    $this->update_customer_wallet($customer_id,0,"winning_wallet",$amount,"DEBIT","WALLET_WITHDRAW_ADMIN","ADMIN-C-".time(),"Paid By Admin",0,0,true,0,$payout_id,$json);

                    $alert_message  = "Congratulations! Your withdraw request has been approved";
                    $noti_type      = "withdraw_request_approved";
                    $notification_data=array();
                    $notification_data['noti_type']=$noti_type;
                    $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);
                }
            }else{
                $new_status="H";
                if(!empty($main_array['failure_reason'])){
                    $reason=$main_array['failure_reason'];
                }else{
                     $reason="Auto Hold";
                }

                
                $update_user_query = "UPDATE tbl_withdraw_requests SET status = ?,referenceId = ?,utr = ?,json_data = ?,reason=? WHERE id = ?";
                $update_user_query_res  = $this->conn->prepare($update_user_query);
                $update_user_query_res->bindParam(1,$new_status);
                $update_user_query_res->bindParam(2,$payout_id);
                $update_user_query_res->bindParam(3,$utr);
                $update_user_query_res->bindParam(4,$json);
                $update_user_query_res->bindParam(5,$reason);
                $update_user_query_res->bindParam(6,$entry_id);
                $update_user_query_res->execute();
            }

        }

    }


    /*public function change_profile_picture($customer_id,$image) {
       
            $update_user_query = "UPDATE tbl_customers SET image = ? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$image);
            $update_user_query_res->bindParam(2,$customer_id);                     

            if ($update_user_query_res->execute()) {
                $this->closeStatement($update_user_query_res);
                return $this->getUpdatedProfileData($customer_id);
            } else {
                $this->closeStatement($update_user_query_res);
                return 'UNABLE_TO_PROCEED';
            }
    }*/


    public function change_profile_picture($customer_id,$image,$fb_image) {
       
            $update_user_query = "UPDATE tbl_customers SET image = ?, external_image = ? WHERE id = ?";
            $update_user_query_res  = $this->conn->prepare($update_user_query);
            $update_user_query_res->bindParam(1,$image);
            $update_user_query_res->bindParam(2,$fb_image);
            $update_user_query_res->bindParam(3,$customer_id);                     

            if ($update_user_query_res->execute()) {
                $this->closeStatement($update_user_query_res);
                return $this->getUpdatedProfileData($customer_id);
            } else {
                $this->closeStatement($update_user_query_res);
                return 'UNABLE_TO_PROCEED';
            }
    }


     public function get_profile_pictures() {

            $sel_user_query = "SELECT image FROM tbl_customer_avatars WHERE status='A' AND is_deleted='N'";
            $sel_user  = $this->conn->prepare($sel_user_query);
            $sel_user->execute();

            $output=array();
            while($matchdata = $sel_user->fetch(PDO::FETCH_ASSOC)){
                $output[]=CUSTOMER_IMAGE_THUMB_URL.$matchdata['image'];
            }
            $this->closeStatement($sel_user);

            // $profile_pictures=unserialize(PROFILE_PICTURES);        
            // foreach ($profile_pictures as $pictures) {
            //     $output[] = CUSTOMER_IMAGE_THUMB_URL.$pictures;             
               
            // }
            return $output;


        
    }


    public function changePassword($customer_id, $oldpassword, $newpassword) {
        $sel_user_query = "SELECT password FROM tbl_customers WHERE id = ? AND status='A' AND is_deleted='N'";
        $sel_user  = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1,$customer_id);
        $sel_user->execute();
        $user =  $sel_user->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($sel_user);
        if (!empty($user)) {
            $oldpassword = md5($oldpassword);
            if ($user['password'] == $oldpassword) {
                $newpassword = md5($newpassword);
                $update_user = "UPDATE tbl_customers SET password=? WHERE id = ?";
                $update_user = $this->conn->prepare($update_user);
                $update_user->bindParam(1,$newpassword);
                $update_user->bindParam(2,$customer_id);
                
                if ($update_user->execute()) {
                    $this->closeStatement($update_user);
                    return 'SUCCESSFULLY_DONE';
                } else {
                    $this->closeStatement($update_user);
                    return 'UNABLE_TO_PROCEED';
                }
            } else {
                return 'INVALID_OLD_PASSWORD';
            }
        } else {
            return 'INVALID_USERNAME';
        }
    } 


    
 public function gettimercdata($dates)
  {
       $date1 = time();
 $date2 = strtotime($dates);
$mins = ($date2 - $date1) / 60;
return $mins;
  }

    public function get_customer_matches($customer_id,$match_progress,$type=1) {


        if($match_progress=="R"){

            $string=" AND tcm.match_progress IN ('R','AB','IR')";


        }elseif($match_progress=="L"){

            $string=" AND tcm.match_progress IN ('L')";

        }else{
              $string=" AND tcm.match_progress IN ('$match_progress')";

        }


       $query = "SELECT tcm.playing_squad_updated as playing_squad_updated,tcm.match_progress as match_progress, tcm.unique_id as match_unique_id, tcm.name as match_name, tcm.match_date as match_date,tcm.game_id as game_id, tcm.close_date as close_date, tcm.match_limit as match_limit, tcm.id as id, tcs.name as series_name, tcs.id as series_id, tgt.name as gametype_name, tgt.id as gametype_id, tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two,tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two,(SELECT count(DISTINCT match_contest_id) FROM tbl_cricket_customer_contests where match_unique_id=tcm.unique_id AND customer_id=?) as contest_count FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON (tcm.series_id=tcs.id) LEFT JOIN tbl_game_types tgt ON (tcm.game_type_id=tgt.id) LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE tcm.status='A'  AND tcm.is_deleted='N' $string AND tcm.unique_id IN (select match_unique_id FROM tbl_cricket_customer_contests WHERE customer_id=?) AND tcm.game_id=?";
       if($match_progress=="F"){
         $query.=" ORDER BY tcm.close_date ASC";  
       }elseif($match_progress=="R"){
         $query.=" ORDER BY tcm.points_updated_at DESC";
       }else{
         $query.=" ORDER BY tcm.close_date DESC";  
       }
       
       
        $query_res = $this->conn->prepare($query); 
        $query_res->bindParam(1,$customer_id); 
        //$query_res->bindParam(2,$match_progress);
        $query_res->bindParam(2,$customer_id);
        $query_res->bindParam(3,$type);

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    $current_time=time();
                    while($matchdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $match=array();

                            $match['id'] = $matchdata['id'];
                            $match['match_id'] = $matchdata['match_unique_id'];
                            $match['name'] = $matchdata['match_name'];
                            $match['match_date'] = $matchdata['match_date'];
                                $match['match_type'] = $matchdata['game_id'];
                        $match['close_date'] = $matchdata['close_date'];
                            $match['match_progress'] = $matchdata['match_progress'];
                            $match['server_date'] = $current_time;
                            $match['match_limit'] = $matchdata['match_limit'];
                            $match['contest_count'] = $matchdata['contest_count'];
                            $match['playing_squad_updated'] = $matchdata['playing_squad_updated'];
                            
                            $match['minute'] = self::gettimercdata(date('Y-m-d H:i:s',$match['match_date']));
                            $match['seconds'] = self::gettimercdata(date('Y-m-d H:i:s',$match['match_date']))*60;

                            $series=array();
                            $series['id']=$matchdata['series_id'];
                            $series['name']=$matchdata['series_name'];
                            $match['series'] = $series;


                            $gametype=array();
                            $gametype['id']=$matchdata['gametype_id'];
                            $gametype['name']=$matchdata['gametype_name'];
                            $match['gametype'] = $gametype;

                            
                            $team1=array();
                            $team1['id']=$matchdata['team_id_one'];
                            $team1['name']=substr($matchdata['team_name_one'],0,10);
                            $team1['sort_name']=$matchdata['team_sort_name_one'];
                            $team1['image']=!empty($matchdata['team_image_one']) ? $matchdata['team_image_one'] : NO_IMG_URL_TEAM;
                            $match['team1'] = $team1;



                            $team2=array();
                            $team2['id']=$matchdata['team_id_two'];
                            $team2['name']=substr($matchdata['team_name_two'],0,10);
                            $team2['sort_name']=$matchdata['team_sort_name_two'];
                            $team2['image']=!empty($matchdata['team_image_two']) ? $matchdata['team_image_two'] : NO_IMG_URL_TEAM;
                            $match['team2'] = $team2;

                            




                            $output[$i]=$match;
                            $i++;
                    }
                    $this->closeStatement($query_res);
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
        }        
       
    }


    public function customer_switch_team($customer_id, $match_unique_id, $match_contest_id, $customer_team_id_old, $customer_team_id_new) {   

        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);

        if(empty($match_detail) || $match_detail['match_progress']!='F'){
            return "INVALID_MATCH";
        }     

        $query = "SELECT id FROM tbl_cricket_customer_contests WHERE customer_id =? AND customer_team_id=? AND match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $customer_team_id_new);
        $query_res->bindParam(3, $match_contest_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows>0){
            return "TEAM_ALREADY_JOINED";
        }

        $update_user = "UPDATE tbl_cricket_customer_contests SET customer_team_id=? WHERE customer_id = ? AND match_unique_id = ? AND match_contest_id = ? AND customer_team_id = ?";
        $update_user = $this->conn->prepare($update_user);
        $update_user->bindParam(1,$customer_team_id_new);
        $update_user->bindParam(2,$customer_id);
        $update_user->bindParam(3,$match_unique_id);
        $update_user->bindParam(4,$match_contest_id);
        $update_user->bindParam(5,$customer_team_id_old);
        if ($update_user->execute()) {
            $this->closeStatement($update_user);

            $description=$customer_id." Join contest match_contest_id ".$match_contest_id." with customer_team_id ".$customer_team_id_new.".";
            $transaction_id="JCWALL".time().$customer_id."_".$match_contest_id."_".$customer_team_id_new;


            $query = "UPDATE tbl_customer_wallet_histories SET description=?, transaction_id=?, team_id=? WHERE sport_id='0' AND type='CUSTOMER_JOIN_CONTEST' AND customer_id =? AND team_id=? AND match_contest_id=?";
            $query_res = $this->conn->prepare($query);
            $query_res->bindParam(1, $description);
            $query_res->bindParam(2, $transaction_id);
            $query_res->bindParam(3, $customer_team_id_new);
            $query_res->bindParam(4, $customer_id);
            $query_res->bindParam(5, $customer_team_id_old);
            $query_res->bindParam(6, $match_contest_id);
            $query_res->execute();
            $this->closeStatement($query_res);

            return 'SUCCESSFULLY_DONE';
        } else {
            $this->closeStatement($update_user);
            return 'UNABLE_TO_PROCEED';
        }
    }

    public function get_contest_teams_count($match_unique_id,$match_contest_id){
        $query = "SELECT IFNULL(count(id),0) as total_teams from tbl_cricket_customer_contests tccc where tccc.match_unique_id=? AND tccc.match_contest_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
        $query_res->bindParam(2,$match_contest_id);
        if($query_res->execute()){
            $teamsdata = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);
            return $teamsdata['total_teams'];
        }else{
            $this->closeStatement($query_res);
        }
        return 0;
    }


    public function get_beat_expert_admin_team($match_unique_id,$match_contest_id){
          
        $teams=NULL;

        $query1 = "SELECT tcccat.image as cat_image, tcct.name, tcct.more_name, tcct.customer_team_name, tcct.customer_id, tc.team_name, tc.firstname, tc.lastname, tc.image, tccc.customer_team_id, tccc.customer_id, tccc.old_rank, tccc.new_rank, tccc.new_points, tccc.win_amount, tccc.refund_amount, tccc.tax_amount, tccc.entry_fees from tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id) LEFT JOIN tbl_cricket_contest_matches tccm ON(tccm.id='$match_contest_id') LEFT JOIN tbl_cricket_contest_categories tcccat ON(tcccat.id=tccm.category_id) where tccc.match_unique_id=? AND tccc.match_contest_id=? AND tccc.customer_team_id=tccm.team_id";


        $query_res1 = $this->conn->prepare($query1);
        $query_res1->bindParam(1,$match_unique_id);
        $query_res1->bindParam(2,$match_contest_id);

        if($query_res1->execute()){

                if ($query_res1->rowCount() > 0) {

                    $current_time=time();
                    $teamsdata = $query_res1->fetch(PDO::FETCH_ASSOC);

                    
                    $teams['customer_id'] = $teamsdata['customer_id'];
                    $teams['firstname'] = $teamsdata['firstname'];
                    $teams['lastname'] = $teamsdata['lastname'];
                    $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                    $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                    $teams['team_id'] = $teamsdata['customer_team_id'];
                    $teams['image'] = !empty($teamsdata['cat_image']) ? CONTEXTCATEGORY_IMAGE_THUMB_URL.$teamsdata['cat_image'] : NO_IMG_URL;
                    $teams['old_rank'] = $teamsdata['old_rank'];
                    $teams['new_rank'] = $teamsdata['new_rank'];
                    $teams['total_points'] = $teamsdata['new_points'];
                    $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                    $teams['refund_amount'] = $teamsdata['refund_amount'];
                    $teams['tax_amount'] = $teamsdata['tax_amount'];
                    $teams['user_entry_fees'] = $teamsdata['entry_fees'];

                    $this->closeStatement($query_res1);
                }else{
                    $this->closeStatement($query_res1);
                }
        }else{
            $this->closeStatement($query_res1);
        }

        return $teams;
    
    }

    public function get_contest_joined_teams($user_id, $match_unique_id,$match_contest_id){
        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        $output = array();
        $beatExpertTeamId=0;
        $query="SELECT team_id from tbl_cricket_contest_matches where id='$match_contest_id'";
        $query_res = $this->conn->prepare($query);
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {
                $teamsdata=$query_res->fetch(PDO::FETCH_ASSOC);
                $beatExpertTeamId=$teamsdata['team_id'];
            }
            $this->closeStatement($query_res);
        }
        

        $query = "SELECT tcct.name,
                tcct.more_name, 
                tcct.customer_team_name, 
                tcct.customer_id, 
                tc.team_name, 
                tc.firstname, 
                tc.lastname, 
                tc.image, 
                tc.external_image, 
                tccc.customer_team_id, 
                tccc.customer_id, 
                tccc.old_rank, 
                tccc.new_rank, 
                tccc.new_points, 
                tccc.win_amount, 
                tccc.refund_amount, 
                tccc.tax_amount 
                from tbl_cricket_customer_contests tccc 
                LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) 
                LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  
                where tccc.match_unique_id=? AND tccc.match_contest_id=? AND tccc.customer_id=?" ;

       if($match_detail['match_progress']!="F"){
           $query.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query.=" ORDER BY tcct.id ASC";
       }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
        $query_res->bindParam(2,$match_contest_id);
        $query_res->bindParam(3,$user_id);

        if($query_res->execute()){

                if ($query_res->rowCount() > 0) {

                    $current_time=time();
                    while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $teams=array();
                            $teams['customer_id'] = $teamsdata['customer_id'];
                            $teams['firstname'] = $teamsdata['firstname'];
                            $teams['lastname'] = $teamsdata['lastname'];
                            $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                            $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                            $teams['team_id'] = $teamsdata['customer_team_id'];
                            $teams['image'] = !empty($teamsdata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL;

                            if(!empty($teamsdata['external_image'])){
                                $teams['image']=$teamsdata['external_image'];
                            }


                            $teams['old_rank'] = $teamsdata['old_rank'];
                            $teams['new_rank'] = $teamsdata['new_rank'];
                            $teams['total_points'] = $teamsdata['new_points'];
                            $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                            $teams['refund_amount'] = $teamsdata['refund_amount'];
                            $teams['tax_amount'] = $teamsdata['tax_amount'];

                            $output[]=$teams;

                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                     $this->closeStatement($query_res);

                    if(count($output)==0){
                        return "NO_RECORD";
                    }
                    return $output;
                }
        }else{

            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
        }
    }


    public function get_contest_joined_teams_array($user_id, $match_unique_id,$match_contest_id){
        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        $output = array();
        $beatExpertTeamId=0;
        $query="SELECT team_id from tbl_cricket_contest_matches where id='$match_contest_id'";
        $query_res = $this->conn->prepare($query);
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {
                $teamsdata=$query_res->fetch(PDO::FETCH_ASSOC);
                $beatExpertTeamId=$teamsdata['team_id'];
            }
            $this->closeStatement($query_res);
        }
        

        $query = "SELECT tcct.name,
                tcct.more_name, 
                tcct.customer_team_name, 
                tcct.customer_id, 
                tc.team_name, 
                tc.firstname, 
                tc.lastname, 
                tc.image, 
                tc.external_image, 
                tccc.customer_team_id, 
                tccc.customer_id, 
                tccc.old_rank, 
                tccc.new_rank, 
                tccc.new_points, 
                tccc.win_amount, 
                tccc.refund_amount, 
                tccc.tax_amount 
                from tbl_cricket_customer_contests tccc 
                LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) 
                LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  
                where tccc.match_unique_id=? AND tccc.match_contest_id=? AND tccc.customer_id=?" ;

       if($match_detail['match_progress']!="F"){
           $query.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query.=" ORDER BY tcct.id ASC";
       }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
        $query_res->bindParam(2,$match_contest_id);
        $query_res->bindParam(3,$user_id);

        if($query_res->execute()){
                if ($query_res->rowCount() > 0) {
                    $current_time=time();
                    while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                            $output[] =  $teamsdata['customer_team_id'];
                    }
                    $this->closeStatement($query_res);
                    return $output;
                }else{
                    $this->closeStatement($query_res);
                    return $output;
                }
        }else{
            $this->closeStatement($query_res);
            return $output;
        }
    }

  public function get_contest_teams($user_id, $match_unique_id,$match_contest_id,$page_no=0) {
        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        $output = array();
        $beatExpertTeamId=0;
        $query="SELECT team_id from tbl_cricket_contest_matches where id='$match_contest_id'";
        $query_res = $this->conn->prepare($query);
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {
                $teamsdata=$query_res->fetch(PDO::FETCH_ASSOC);
                $beatExpertTeamId=$teamsdata['team_id'];
            }
            $this->closeStatement($query_res);
        }
        

    if($page_no==1){

        $query1 = "SELECT tcct.name, tcct.more_name, tcct.customer_team_name, tcct.customer_id, tc.team_name, tc.firstname, tc.lastname, tc.image, tc.external_image, tccc.customer_team_id, tccc.customer_id, tccc.old_rank, tccc.new_rank, tccc.new_points, tccc.win_amount, tccc.refund_amount, tccc.tax_amount, tccc.entry_fees from tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  where tccc.match_unique_id=? AND tccc.match_contest_id=? AND tccc.customer_id=$user_id";


       if($match_detail['match_progress']!="F"){
           $query1.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query1.=" ORDER BY tcct.id ASC";
       }


       // echo $query;



        $query_res1 = $this->conn->prepare($query1);
        $query_res1->bindParam(1,$match_unique_id);
        $query_res1->bindParam(2,$match_contest_id);

        if($query_res1->execute()){

                if ($query_res1->rowCount() > 0) {

                    $current_time=time();
                    while($teamsdata = $query_res1->fetch(PDO::FETCH_ASSOC)){

                            $teams=array();
                            $teams['customer_id'] = $teamsdata['customer_id'];
                            $teams['firstname'] = $teamsdata['firstname'];
                            $teams['lastname'] = $teamsdata['lastname'];
                            $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                            $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                            $teams['team_id'] = $teamsdata['customer_team_id'];
                            $teams['image'] = !empty($teamsdata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL;

                            if(!empty($teamsdata['external_image'])){
                                $teams['image']=$teamsdata['external_image'];
                            }

                            $teams['old_rank'] = $teamsdata['old_rank'];
                            $teams['new_rank'] = $teamsdata['new_rank'];
                            $teams['total_points'] = $teamsdata['new_points'];
                            $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                            $teams['refund_amount'] = $teamsdata['refund_amount'];
                            $teams['tax_amount'] = $teamsdata['tax_amount'];
                            $teams['user_entry_fees'] = $teamsdata['entry_fees'];



                            $output[]=$teams;

                    }
                    $this->closeStatement($query_res1);
                }else{
                    $this->closeStatement($query_res1);
                }
        }else{
            $this->closeStatement($query_res1);
               return "UNABLE_TO_PROCEED";
        }
    }

       /*$query = "SELECT tcct.name, tcct.more_name, tcct.customer_team_name, tcct.customer_id, tc.team_name, tc.firstname, tc.lastname, tc.image, tc.external_image, tccc.customer_team_id, tccc.customer_id, tccc.old_rank, tccc.new_rank, tccc.new_points, tccc.win_amount, tccc.refund_amount, tccc.tax_amount from tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  where tccc.match_unique_id=? AND tccc.match_contest_id=? AND  tccc.customer_id != '$user_id' AND tccc.customer_team_id!= '$beatExpertTeamId'";*/
        
        $query = "SELECT tcct.name,
                tcct.more_name, 
                tcct.customer_team_name, 
                tcct.customer_id, 
                tc.team_name, 
                tc.firstname, 
                tc.lastname, 
                tc.image, 
                tc.external_image, 
                tccc.customer_team_id, 
                tccc.customer_id, 
                tccc.old_rank, 
                tccc.new_rank, 
                tccc.new_points, 
                tccc.win_amount, 
                tccc.refund_amount, 
                tccc.tax_amount 
                from tbl_cricket_customer_contests tccc 
                LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) 
                LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  
                where tccc.match_unique_id=? AND tccc.match_contest_id=? " ;

       if($match_detail['match_progress']!="F"){
           $query.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query.=" ORDER BY tcct.id ASC";
       }

        if($page_no>0){
            $limit=70;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }
       // echo $query;



        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
        $query_res->bindParam(2,$match_contest_id);


        if($query_res->execute()){

                if ($query_res->rowCount() > 0) {

                    $current_time=time();
                    while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $teams=array();
                            $teams['customer_id'] = $teamsdata['customer_id'];
                            $teams['firstname'] = $teamsdata['firstname'];
                            $teams['lastname'] = $teamsdata['lastname'];
                            $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                            $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                            $teams['team_id'] = $teamsdata['customer_team_id'];
                            $teams['image'] = !empty($teamsdata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL;

                            if(!empty($teamsdata['external_image'])){
                                $teams['image']=$teamsdata['external_image'];
                            }


                            $teams['old_rank'] = $teamsdata['old_rank'];
                            $teams['new_rank'] = $teamsdata['new_rank'];
                            $teams['total_points'] = $teamsdata['new_points'];
                            $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                            $teams['refund_amount'] = $teamsdata['refund_amount'];
                            $teams['tax_amount'] = $teamsdata['tax_amount'];

                            $output[]=$teams;

                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                     $this->closeStatement($query_res);

                    if(count($output)==0){
                        return "NO_RECORD";
                    }
                    return $output;
                }
        }else{

            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";

        }

    }

 /*   public function get_contest_teams($user_id, $match_unique_id,$match_contest_id,$page_no=0) {
        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        $output = array();

        $beatExpertTeamId=0;

        $query="SELECT team_id from tbl_cricket_contest_matches where id='$match_contest_id'";
        $query_res = $this->conn->prepare($query);
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {
                $teamsdata=$query_res->fetch(PDO::FETCH_ASSOC);
                $beatExpertTeamId=$teamsdata['team_id'];
            }
            $this->closeStatement($query_res);
        }
        

    if($page_no==1){

        $query1 = "SELECT tcct.name, tcct.more_name, tcct.customer_team_name, tcct.customer_id, tc.team_name, tc.firstname, tc.lastname, tc.image, tc.external_image, tccc.customer_team_id, tccc.customer_id, tccc.old_rank, tccc.new_rank, tccc.new_points, tccc.win_amount, tccc.refund_amount, tccc.tax_amount, tccc.entry_fees from tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  where tccc.match_unique_id=? AND tccc.match_contest_id=? AND tccc.customer_id=$user_id";


       if($match_detail['match_progress']!="F"){
           $query1.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query1.=" ORDER BY tcct.id ASC";
       }


       // echo $query;



        $query_res1 = $this->conn->prepare($query1);
        $query_res1->bindParam(1,$match_unique_id);
        $query_res1->bindParam(2,$match_contest_id);

        if($query_res1->execute()){

                if ($query_res1->rowCount() > 0) {

                    $current_time=time();
                    while($teamsdata = $query_res1->fetch(PDO::FETCH_ASSOC)){

                            $teams=array();
                            $teams['customer_id'] = $teamsdata['customer_id'];
                            $teams['firstname'] = $teamsdata['firstname'];
                            $teams['lastname'] = $teamsdata['lastname'];
                            $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                            $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                            $teams['team_id'] = $teamsdata['customer_team_id'];
                            $teams['image'] = !empty($teamsdata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL;

                            if(!empty($teamsdata['external_image'])){
                                $teams['image']=$teamsdata['external_image'];
                            }

                            $teams['old_rank'] = $teamsdata['old_rank'];
                            $teams['new_rank'] = $teamsdata['new_rank'];
                            $teams['total_points'] = $teamsdata['new_points'];
                            $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                            $teams['refund_amount'] = $teamsdata['refund_amount'];
                            $teams['tax_amount'] = $teamsdata['tax_amount'];
                            $teams['user_entry_fees'] = $teamsdata['entry_fees'];



                            $output[]=$teams;

                    }
                    $this->closeStatement($query_res1);
                }else{
                    $this->closeStatement($query_res1);
                }
        }else{
            $this->closeStatement($query_res1);
               return "UNABLE_TO_PROCEED";
        }
    }



       $query = "SELECT tcct.name, tcct.more_name, tcct.customer_team_name, tcct.customer_id, tc.team_name, tc.firstname, tc.lastname, tc.image, tc.external_image, tccc.customer_team_id, tccc.customer_id, tccc.old_rank, tccc.new_rank, tccc.new_points, tccc.win_amount, tccc.refund_amount, tccc.tax_amount from tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON(tccc.customer_id=tc.id) LEFT JOIN tbl_cricket_customer_teams tcct ON(tcct.id=tccc.customer_team_id)  where tccc.match_unique_id=? AND tccc.match_contest_id=? AND  tccc.customer_id != '$user_id' AND tccc.customer_team_id!= '$beatExpertTeamId'";


       if($match_detail['match_progress']!="F"){
           $query.=" ORDER BY tccc.new_rank ASC";
       }else{
           $query.=" ORDER BY tcct.id ASC";
       }

        if($page_no>0){
            $limit=70;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }
       // echo $query;



        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
        $query_res->bindParam(2,$match_contest_id);


        if($query_res->execute()){

                if ($query_res->rowCount() > 0) {

                    $current_time=time();
                    while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $teams=array();
                            $teams['customer_id'] = $teamsdata['customer_id'];
                            $teams['firstname'] = $teamsdata['firstname'];
                            $teams['lastname'] = $teamsdata['lastname'];
                            $teams['customer_team_name'] = empty($teamsdata['customer_team_name'])?$teamsdata['team_name']:$teamsdata['customer_team_name'];
                            $teams['team_name'] = ($teamsdata['more_name']=='0')?$teamsdata['name']:$teamsdata['more_name'];
                            $teams['team_id'] = $teamsdata['customer_team_id'];
                            $teams['image'] = !empty($teamsdata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL;

                            if(!empty($teamsdata['external_image'])){
                                $teams['image']=$teamsdata['external_image'];
                            }


                            $teams['old_rank'] = $teamsdata['old_rank'];
                            $teams['new_rank'] = $teamsdata['new_rank'];
                            $teams['total_points'] = $teamsdata['new_points'];
                            $teams['win_amount'] = $teamsdata['win_amount']+$teamsdata['tax_amount'];
                            $teams['refund_amount'] = $teamsdata['refund_amount'];
                            $teams['tax_amount'] = $teamsdata['tax_amount'];



                            $output[]=$teams;

                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                     $this->closeStatement($query_res);

                    if(count($output)==0){
                        return "NO_RECORD";
                    }
                    return $output;
                }
        }else{

            $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";

        }

    }*/
    


    public function customer_deposit_amount($customer_id, $amount,$tempid) {
        $sqlUpdate = 'Select *  FROM tbl_tem_payment WHERE 1 AND tp_id='.$tempid;
        $payment = $this->conn->query($sqlUpdate);
        $payment->execute(); 
        $rowPaymentInfo = $payment->fetch(PDO::FETCH_ASSOC);
        if($rowPaymentInfo['te_credit_status']==0)
        {
         $sqlUpdate = 'UPDATE tbl_tem_payment SET te_credit_status=1 WHERE 1 AND tp_id='.$tempid;
        $this->conn->query($sqlUpdate);
        $amount=round($amount,2);
         $description=$customer_id." Recharge his wallet.";
         $transaction_id="WALL".time();
         $wallet_type="deposit_wallet";
         $match_contest_id=0;
         $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,"CREDIT","CUSTOMER_WALLET_RECHARGE",$transaction_id,$description,0,0,true,0,null,null);
                }
         return $this->getUpdatedWalletData($customer_id);         
    }
    
    public function get_customer_wallet_detail($customer_id) {     
         return $this->getUpdatedWalletData($customer_id);         
    }



    public function get_customer_wallet_history($customer_id, $page_no=0) {
        $query = "select * FROM ((SELECT id, customer_id,match_contest_id,wallet_type,transaction_type,transaction_id,type,previous_amount,amount ,current_amount,description,status,rcb_id,ref_cwh_id,created_by,created,created_date from tbl_customer_wallet_histories where customer_id=? AND match_contest_id='0')  UNION (SELECT id, customer_id,match_contest_id,wallet_type,transaction_type,transaction_id,type,sum(previous_amount) as previous_amount,sum(amount) as  amount ,sum(current_amount) as  current_amount,description,status,rcb_id,ref_cwh_id,created_by,created,created_date from tbl_customer_wallet_histories where customer_id=? AND match_contest_id>0 GROUP BY transaction_id)) t3 ORDER BY t3.id desc";

        if($page_no>0){
            $limit=20;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$customer_id); 
        $query_res->bindParam(2,$customer_id); 

       

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    $current_time=time();
                    while($historydata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $history=array();

                            
                            $history['wallet_type'] = $historydata['wallet_type'];                            
                            $history['transaction_type'] = $historydata['transaction_type'];                            
                            $history['transaction_id'] = $historydata['transaction_id'];                            
                            $history['type'] = $historydata['type'];                            
                            $history['previous_amount'] = $historydata['previous_amount'];                            
                            $history['amount'] = $historydata['amount'];                            
                            $history['current_amount'] = $historydata['current_amount'];                            
                            $history['description'] = $historydata['description'];                            
                            $history['status'] = $historydata['status'];                            
                            $history['created'] = $historydata['created']; 
                            $history['created_date'] = $historydata['created_date']; 


                            switch ($history['type']) {

                                case "CUSTOMER_WALLET_RECHARGE":

                                $history['title_text'] = "Deposit Cash"; 
                                $history['description'] = "Deposit wallet recharged.";
                                break;
                                case "WALLET_RECHARGE_ADMIN":{
                                    if($history['wallet_type'] == "Winning Wallet"){
                                        $history['title_text'] = "Admin Recharge Winning Wallet"; 
                                    }else if($history['wallet_type'] == "Deposit Wallet"){
                                        $history['title_text'] = "Admin Recharge Deposit Wallet";
                                    }else if($history['wallet_type'] == "Bonus Wallet"){
                                       $history['title_text'] =  "Admin Recharge Bonus Wallet";
                                    }else{
                                        $history['title_text'] = "Deposit Cash (Admin)";
                                    }
                                }
                                break;
                                case "WALLET_WITHDRAW_ADMIN":
                                {
                                    if($history['wallet_type'] == "Winning Wallet"){
                                        $history['title_text'] = "Admin Withdraw Winning Wallet";
                                    }else if($history['wallet_type'] == "Deposit Wallet"){
                                        $history['title_text'] = "Admin Withdraw Deposit Wallet";
                                    }else if($history['wallet_type'] == "Bonus Wallet"){
                                        $history['title_text'] = "Admin Withdraw Bonus Wallet";
                                    }else{
                                        $history['title_text'] = "Withdraw Cash (Admin)";
                                    }
                                }
                                break;
                                case "CUSTOMER_WIN_CONTEST":
                                    $history['title_text'] =  "Won A Contest";
                                    $history['description'] = "Fantasy cash prize payout";
                                    break;
                                case "CUSTOMER_JOIN_CONTEST":
                                    $history['title_text'] =  "Joined A Contest";
                                    $history['description'] = "Join fantasy contest with cash";
                                    break;
                                case "CUSTOMER_RECEIVED_RCB":
                                    $history['title_text'] =  "Cash Bonus Received";
                                    $history['description'] = "Recharge cash bonus received";
                                    break;
                                case "CUSTOMER_RECEIVED_REFCB":
                                    $history['title_text'] =  "Referral Cash Bonus";
                                    $history['description'] = "Referral cash bonus received";
                                    break;
                                case "CUSTOMER_RECEIVED_REFCCB":
                                    $history['title_text'] =  "Cash Earning";
                                    $history['description'] = "Cash contest earning payout";
                                    break;
                                case "REGISTER_CASH_BONUS":
                                    $history['title_text'] =  "Register Cash Bonus";
                                    $history['description'] = "Register cash bonus received";
                                    break;
                                case "CUSTOMER_REFUND_CONTEST":
                                    $history['title_text'] =  "Refund Contest";
                                    $history['description'] = "Contest entry fee refund";
                                    break;
                                case "CUSTOMER_REFUND_ABCONTEST":
                                    $history['title_text'] =  "Refund Contest";
                                    $history['description'] = "Contest entry fee refund";
                                    break;
                            }

                            

                            $output[$i]=$history;
                            $i++;
                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                     $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
             $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function get_customer_wallet_history_filter($customer_id, $page_no=0,$type="All") {

        if($type=="All"){
            $array=array('CUSTOMER_JOIN_CONTEST','CUSTOMER_WIN_CONTEST','CUSTOMER_REFUND_ABCONTEST','CUSTOMER_REFUND_CONTEST','CUSTOMER_WALLET_RECHARGE','CUSTOMER_RECEIVED_REFCB','CUSTOMER_RECEIVED_RCB','REGISTER_CASH_BONUS','WALLET_WITHDRAW_ADMIN','WALLET_RECHARGE_ADMIN');
        }else if($type=="Join"){
            $array=array('CUSTOMER_JOIN_CONTEST');
        }else if($type=="Win"){
            $array=array('CUSTOMER_WIN_CONTEST','WALLET_WITHDRAW_ADMIN','WALLET_RECHARGE_ADMIN');
        }else if($type=="Refund"){
            $array=array('CUSTOMER_REFUND_ABCONTEST','CUSTOMER_REFUND_CONTEST');
        }else if($type=="Deposit"){
            $array=array('CUSTOMER_WALLET_RECHARGE','WALLET_WITHDRAW_ADMIN','WALLET_RECHARGE_ADMIN');
        }else if($type=="Bonus"){
             $array=array('CUSTOMER_RECEIVED_REFCB','CUSTOMER_RECEIVED_RCB','REGISTER_CASH_BONUS','WALLET_WITHDRAW_ADMIN','WALLET_RECHARGE_ADMIN');
        }else if($type=="Withdraw"){
              $array=array('WALLET_WITHDRAW_ADMIN');
        }else{
             $array=array('CUSTOMER_JOIN_CONTEST','CUSTOMER_WIN_CONTEST','CUSTOMER_REFUND_ABCONTEST','CUSTOMER_REFUND_CONTEST','CUSTOMER_WALLET_RECHARGE','CUSTOMER_RECEIVED_REFCB','CUSTOMER_RECEIVED_RCB','REGISTER_CASH_BONUS','WALLET_WITHDRAW_ADMIN','WALLET_RECHARGE_ADMIN');
        }
        $inarray=implode(',',$array);
        $query = "select * FROM ((SELECT id, customer_id,match_contest_id,wallet_type,transaction_type,transaction_id,type,previous_amount,amount ,current_amount,description,status,rcb_id,ref_cwh_id,created_by,created from tbl_customer_wallet_histories where customer_id=? AND match_contest_id='0' AND FIND_IN_SET(type,'$inarray'))  UNION (SELECT id, customer_id,match_contest_id,wallet_type,transaction_type,transaction_id,type,sum(previous_amount) as previous_amount,sum(amount) as  amount ,sum(current_amount) as  current_amount,description,status,rcb_id,ref_cwh_id,created_by,created from tbl_customer_wallet_histories where customer_id=? AND match_contest_id>0  AND FIND_IN_SET(type,'$inarray') GROUP BY transaction_id)) t3 ORDER BY t3.id desc";
        //echo $query;die;

        if($page_no>0){
            $limit=20;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$customer_id); 
        $query_res->bindParam(2,$customer_id); 

       

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    $current_time=time();
                    while($historydata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $history=array();

                            
                            $history['wallet_type'] = $historydata['wallet_type']; 
                            $history['type'] = $historydata['type'];     
                            if($type=='Win'){
                                if($history['type']=='WALLET_WITHDRAW_ADMIN' || $history['type']=='WALLET_RECHARGE_ADMIN'){
                                    if($history['wallet_type']!='Winning Wallet'){
                                        continue;
                                    }
                                }

                            }else if($type=='Deposit'){
                                if($history['type']=='WALLET_WITHDRAW_ADMIN' || $history['type']=='WALLET_RECHARGE_ADMIN'){
                                    if($history['wallet_type']!='Deposit Wallet'){
                                        continue;
                                    }
                                }

                            }else if($type=='Bonus'){
                                if($history['type']=='WALLET_WITHDRAW_ADMIN' || $history['type']=='WALLET_RECHARGE_ADMIN'){
                                    if($history['wallet_type']!='Bonus Wallet'){
                                        continue;
                                    }
                                }

                            }

                            $history['transaction_type'] = $historydata['transaction_type'];                            
                            $history['transaction_id'] = $historydata['transaction_id'];                                             
                            $history['previous_amount'] = $historydata['previous_amount'];                            
                            $history['amount'] = $historydata['amount'];                            
                            $history['current_amount'] = $historydata['current_amount'];                            
                            $history['description'] = $historydata['description'];                            
                            $history['status'] = $historydata['status'];                            
                            $history['created'] = $historydata['created']; 

                            switch ($history['type']) {

                                case "CUSTOMER_WALLET_RECHARGE":

                                $history['title_text'] = "Deposit Cash"; 
                                $history['description'] = "Deposit wallet recharged.";
                                break;
                                case "WALLET_RECHARGE_ADMIN":{
                                    if($history['wallet_type'] == "Winning Wallet"){
                                        $history['title_text'] = "Admin Recharge Winning Wallet"; 
                                    }else if($history['wallet_type'] == "Deposit Wallet"){
                                        $history['title_text'] = "Admin Recharge Deposit Wallet";
                                    }else if($history['wallet_type'] == "Bonus Wallet"){
                                       $history['title_text'] =  "Admin Recharge Bonus Wallet";
                                    }else{
                                        $history['title_text'] = "Deposit Cash (Admin)";
                                    }
                                }
                                break;
                                case "WALLET_WITHDRAW_ADMIN":
                                {
                                    if($history['wallet_type'] == "Winning Wallet"){
                                        $history['title_text'] = "Admin Withdraw Winning Wallet";
                                    }else if($history['wallet_type'] == "Deposit Wallet"){
                                        $history['title_text'] = "Admin Withdraw Deposit Wallet";
                                    }else if($history['wallet_type'] == "Bonus Wallet"){
                                        $history['title_text'] = "Admin Withdraw Bonus Wallet";
                                    }else{
                                        $history['title_text'] = "Withdraw Cash (Admin)";
                                    }
                                }
                                break;
                                case "CUSTOMER_WIN_CONTEST":
                                    $history['title_text'] =  "Won A Contest";
                                    $history['description'] = "Fantasy cash prize payout";
                                    break;
                                case "CUSTOMER_JOIN_CONTEST":
                                    $history['title_text'] =  "Joined A Contest";
                                    $history['description'] = "Join fantasy contest with cash";
                                    break;
                                case "CUSTOMER_RECEIVED_RCB":
                                    $history['title_text'] =  "Cash Bonus Received";
                                    $history['description'] = "Recharge cash bonus received";
                                    break;
                                case "CUSTOMER_RECEIVED_REFCB":
                                    $history['title_text'] =  "Referral Cash Bonus";
                                    $history['description'] = "Referral cash bonus received";
                                    break;

                                case "CUSTOMER_RECEIVED_REFCCB":
                                    $history['title_text'] =  "Cash Earning";
                                    $history['description'] = "Cash contest earning payout";
                                    break;

                                case "REGISTER_CASH_BONUS":
                                    $history['title_text'] =  "Register Cash Bonus";
                                    $history['description'] = "Register cash bonus received";
                                    break;
                                case "CUSTOMER_REFUND_CONTEST":
                                    $history['title_text'] =  "Refund Contest";
                                    $history['description'] = "Contest entry fee refund";
                                    break;
                                case "CUSTOMER_REFUND_ABCONTEST":
                                    $history['title_text'] =  "Refund Contest";
                                    $history['description'] = "Contest entry fee refund";
                                    break;
                            }

                            

                            $output[$i]=$history;
                            $i++;
                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{
                     $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{
             $this->closeStatement($query_res);
               return "UNABLE_TO_PROCEED";
            
        }        
       
    }



    public function get_customer_withdraw_history($customer_id, $page_no=0) {
        $query = "SELECT * from tbl_withdraw_requests where customer_id=? ORDER BY id desc";

        if($page_no>0){
            $limit=20;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1,$customer_id); 
       

       

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    $current_time=time();
                    while($historydata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $history=array();

                            $history=$historydata;
                             

                            $output[$i]=$history;
                            $i++;
                    }

                    $this->closeStatement($query_res);
                    return $output;
                }else{

                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

             $this->closeStatement($query_res);

               return "UNABLE_TO_PROCEED";
            
        }        
       
    }


    public function customer_team_name_update($customer_id, $team_name,$state='',$dob='') { 

        if ($this->isTeamNameExists($team_name,$customer_id)) { 
     $ary =[];
            $ary['status'] = 'TEAM_NAME_ALREADY_EXISTED';
            $ary['data'] = $this->getUpdatedProfileData($customer_id);
            return $ary;
            }
        
        

        $teamname_update  = "UPDATE tbl_customers set team_name=?,team_change='Y',state=? ,date_of_birth=? WHERE id=? AND team_change='N'";
        $query_res = $this->conn->prepare($teamname_update); 
        $query_res->bindParam(1,$team_name);
                    $query_res->bindParam(2,$state);
                     $query_res->bindParam(3,$dob);
 $query_res->bindParam(4,$customer_id);
   if($query_res->execute()){
             $count = $query_res->rowCount();

            $this->closeStatement($query_res);
            $ary =[];
            $ary['status'] = $count>0?"SUCCESS":"TEAM_NAME_CANT_CHANGE";
            $ary['data'] = $this->getUpdatedProfileData($customer_id);
            
        ///   return $count>0?"SUCCESS":"TEAM_NAME_CANT_CHANGE";
                     return $ary;
    }else{

          $this->closeStatement($query_res);
          return "UNABLE_TO_PROCEED";
         }    

         

    }

    public function get_player_detail_finder_cron(){
        $query  = "SELECT id, name FROM tbl_cricket_players WHERE (summary= '' OR summary IS NULL) AND status='A' AND is_deleted='N' ORDER BY id DESC limit 100";
        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows==0){

            $this->closeStatement($query_res);

            return "No Player found.";
        }

        $i=0;
        while($array = $query_res->fetch()){

            $Entity_object=new Entitysport();
            $api_data_array=$Entity_object->player_finder($array['name']);

            $api_data_array=$api_data_array['data'];

            if(!empty($api_data_array) && count($api_data_array)==1){
                $time=time();
                $player_data=$api_data_array[0];

                $country_id=0;
                if(!empty($player_data['country'])){

                        $country_query  = "SELECT id FROM tbl_countries WHERE name= ?";
                        $country_query_res  = $this->conn->prepare($country_query);
                        $country_query_res->bindParam(1,$player_data['country']);
                        $country_query_res->execute();
                        $country_array = $country_query_res->fetch();

                        $this->closeStatement($country_query_res);

                        if(empty($country_array)){
                            $insert_query = "INSERT INTO tbl_countries SET name=?, created_at=?, updated_at=?";
                            $insert_query_res  = $this->conn->prepare($insert_query);
                            $insert_query_res->bindParam(1,$player_data['country']);
                            $insert_query_res->bindParam(2,$time);
                            $insert_query_res->bindParam(3,$time);
                            $insert_query_res->execute();
                            $country_id=$this->conn->lastInsertId();

                            $this->closeStatement($insert_query_res);
                        }else{
                            $country_id=$country_array['id'];
                        }

                }

                $bets= !empty($player_data['battingStyle'])? $player_data['battingStyle']:" ";
                $bowls= !empty($player_data['bowlingStyle'])? $player_data['bowlingStyle']:" ";
                $position= !empty($player_data['playingRole'])? $player_data['playingRole']:" ";
                if($position!=" "){

                    $updatedposition=strtolower($position);
                    if (strpos($updatedposition, 'wicketkeeper') !== false) {
                        $position="Wicketkeeper";
                    }else if (strpos($updatedposition, 'batsman') !== false) {
                        $position="Batsman";
                    }else if (strpos($updatedposition, 'allrounder') !== false) {
                        $position="Allrounder";
                    }else if (strpos($updatedposition, 'bowler') !== false) {
                        $position="Bowler";
                    }
                }
                $dob= !empty($player_data['born'])? $player_data['born']:"";

                $dob=explode(',',$dob);

                if(count($dob)>=2){
                    $date=date_create(trim($dob[0])." ".trim($dob[1]));
                    $dob= date_format($date,"Y-m-d");
                }else{
                    $dob="";
                }

                $summary="1";
                $update_player_query = "UPDATE  tbl_cricket_players SET uniqueid=?, bets=?, bowls=?, position=?, dob=?, country_id=?, updated_at=?, summary=?, name=? where id=?";
                $update_player_query_res = $this->conn->prepare($update_player_query);
                $update_player_query_res->bindParam(1, $player_data['pid']);
                $update_player_query_res->bindParam(2, $bets);
                $update_player_query_res->bindParam(3, $bowls);
                $update_player_query_res->bindParam(4, $position);
                $update_player_query_res->bindParam(5, $dob);
                $update_player_query_res->bindParam(6, $country_id);
                $update_player_query_res->bindParam(7, $time);
                $update_player_query_res->bindParam(8, $summary);
                $update_player_query_res->bindParam(9, $player_data['name']);
                $update_player_query_res->bindParam(10, $array['id']);
                $exception_message="";
                try{
                    $update_player_query_res->execute();
                    $this->closeStatement($update_player_query_res);
                }catch (PDOException $e){
                    $exception_message=$e->getMessage();
                    $this->closeStatement($update_player_query_res);
                }
                //$this->sql_error($update_player_query_res);
                $player_data['exception_message']=$exception_message;


                $all_player_data[$i]=$player_data;

                $i++;

            }
        }

        $this->closeStatement($query_res);

        return $all_player_data;
    }


    public function get_player_detail_cron() {
        $query  = "SELECT uniqueid FROM tbl_cricket_players WHERE (summary= '' OR summary IS NULL) AND status='A' AND is_deleted='N' ORDER BY id DESC limit 10";
        $query_res  = $this->conn->prepare($query);       
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows==0){

           $this->closeStatement($query_res);

            return "No Player found.";
        }

        $i=0;
        while($array = $query_res->fetch()){


            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => ENTITYSPORT_PLAYER_DETAIL.$array['uniqueid'], /*CRICAPI_PLAYER_STATISTICS."&pid=".$array['uniqueid']*/
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Postman-Token: e8439a8d-4242-457f-b187-56adf512fbe6",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
              echo "cURL Error #:" . $err;
            } else {
              $time=time();
              $player_data=json_decode($response,true);
              $country_id=0;
              if(!empty($player_data['country'])){

                        $country_query  = "SELECT id FROM tbl_countries WHERE name= ?";
                        $country_query_res  = $this->conn->prepare($country_query);
                        $country_query_res->bindParam(1,$player_data['country']);       
                        $country_query_res->execute();
                        $country_array = $country_query_res->fetch();
                        $this->closeStatement($country_query_res);

                        if(empty($country_array)){


                            $insert_query = "INSERT INTO tbl_countries SET name=?, created_at=?, updated_at=?";
                            $insert_query_res  = $this->conn->prepare($insert_query);
                            $insert_query_res->bindParam(1,$player_data['country']);
                            $insert_query_res->bindParam(2,$time);
                            $insert_query_res->bindParam(3,$time);
                            $insert_query_res->execute();
                            $country_id=$this->conn->lastInsertId();

                            $this->closeStatement($insert_query_res);

                        }else{

                            $country_id=$country_array['id'];


                        }
                
              }

           
            $bets= !empty($player_data['battingStyle'])? $player_data['battingStyle']:" ";
            $bowls= !empty($player_data['bowlingStyle'])? $player_data['bowlingStyle']:" ";             
            $position= !empty($player_data['playingRole'])? $player_data['playingRole']:" ";
            if($position!=" "){

                $updatedposition=strtolower($position);
                if (strpos($updatedposition, 'wicketkeeper') !== false) {
                    $position="Wicketkeeper";
                }else if (strpos($updatedposition, 'batsman') !== false) {
                    $position="Batsman";
                }else if (strpos($updatedposition, 'allrounder') !== false) {
                    $position="Allrounder";
                }else if (strpos($updatedposition, 'bowler') !== false) {
                    $position="Bowler";
                }
            }
            $dob= !empty($player_data['born'])? $player_data['born']:"";

            $dob=explode(',',$dob);

            if(count($dob)>=2){

             $date=date_create(trim($dob[0])." ".trim($dob[1]));
             $dob= date_format($date,"Y-m-d");
            } else{

            $dob="";
            }          

            $summary="1";
            $update_player_query = "UPDATE  tbl_cricket_players SET bets = ?, bowls=?, position=?, dob=?, country_id=?, updated_at=?,summary=? where uniqueid=?";
            $update_player_query_res = $this->conn->prepare($update_player_query);
            $update_player_query_res->bindParam(1, $bets);
            $update_player_query_res->bindParam(2, $bowls);
            $update_player_query_res->bindParam(3, $position);       
            $update_player_query_res->bindParam(4, $dob);
            $update_player_query_res->bindParam(5, $country_id);
            $update_player_query_res->bindParam(6, $time);
            $update_player_query_res->bindParam(7, $summary);
            $update_player_query_res->bindParam(8, $array['uniqueid']);

            $update_player_query_res->execute();

            $this->closeStatement($update_player_query_res);

            //$this->sql_error($update_player_query_res);


            $all_player_data[$i]=$player_data;

            $i++;



            }

        }

        $this->closeStatement($query_res);

        return $all_player_data;

    }


    public function match_progress_cron() {
        $time=time();
        $query  = "SELECT unique_id FROM tbl_cricket_matches WHERE match_progress= 'F' AND close_date<=$time AND status='A' AND is_deleted='N'";
        $query_res  = $this->conn->prepare($query);       
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows==0){

           $this->closeStatement($query_res);
            return "No match found.";
        }

        $i=0;
        $output=array();
        while($array = $query_res->fetch()){

           
          $update_match_query = "UPDATE  tbl_cricket_matches SET match_progress = 'L' where unique_id=?";
          $update_match_query_res = $this->conn->prepare($update_match_query);
          $update_match_query_res->bindParam(1, $array['unique_id']);
          $update_match_query_res->execute();
          $this->closeStatement($update_match_query_res);

          $output[$i]=$array['unique_id'];

          $i++;
        }

        $this->closeStatement($query_res);

        return $output;

       
    }

    public function get_our_system_running_matches() {
       $query = "SELECT tcm.id, tcm.unique_id, tcm.match_date, tcm.close_date FROM tbl_cricket_matches tcm WHERE tcm.is_deleted='N' AND (tcm.match_progress='F' OR tcm.match_progress='L' OR tcm.match_progress='AB')";

       $query_res = $this->conn->prepare($query);    
       
       if($query_res->execute()){
            $output = array();
            if ($query_res->rowCount() > 0) {
               
                while($matchdata = $query_res->fetch(PDO::FETCH_ASSOC)){
                        $match=array();
                        $match['id'] = $matchdata['id'];
                        $match['unique_id'] = $matchdata['unique_id'];
                        $match['match_date'] = $matchdata['match_date'];
                        $match['close_date'] = $matchdata['close_date'];

                        $output[$match['unique_id']]=$match;
                        
                }
                $this->closeStatement($query_res);
                return $output;
            }else{
                $this->closeStatement($query_res);
                return $output;;
            }
        }else{
            $this->closeStatement($query_res);
            return "UNABLE_TO_PROCEED";
        }        
       
    }

       

    public function update_new_available_match_count(){

        $output=array();

      
        $entity = new Entitysport();

        $result=$entity->upcoming_matches("0");
        $latestMatches=$result['matches'];

        if(empty($latestMatches)){

            $output['message']="No latest match found from entity";
            return $output;

        }

              

        $ourMatches=$this->get_our_system_running_matches();

        $newMatchAvailable=array();
        $matchTimeChanged=array();

        foreach ($latestMatches as $key => $value) {

            $isExist=array_key_exists($value['unique_id'], $ourMatches);

            if($isExist){
                $matchData=$ourMatches[$value['unique_id']];
                $updatedMatchTime = strtotime($value['dateTimeGMT']);
                if($matchData['match_date']!=$updatedMatchTime){
                    $matchTimeChanged[]=$value;
                }
            }else{
                $newMatchAvailable[]=$value;
            }
            
        }

        $newMatchesCount=count($newMatchAvailable);
        $updatedMatchesCount=count($matchTimeChanged);


        $query="UPDATE tbl_games set new_match_count=$newMatchesCount where id=0";
        $query_res = $this->conn->prepare($query);   
        $query_res->execute(); 
    
        $updatedRowCount = $query_res->rowCount();
        $this->closeStatement($query_res);

          

        $output['message']="latest match found";
        $output['updatedRowCount']=$updatedRowCount;
        $output['newMatchAvailable']=$newMatchAvailable;
        $output['matchTimeChanged']=$matchTimeChanged;

        if(($updatedRowCount>0 && $newMatchesCount>0) || $updatedMatchesCount>0){

            $reciverEmail=array();
        //    $reciverEmail['manish.kumar.bluestk@gmail.com']="Developer";
        ///    $reciverEmail['ayushi.synarionit@gmail.com']="Developer";
        ///    $reciverEmail['prashant.sharma@synarionit.com']="Developer";
          ///  $reciverEmail['manoj.sharma.guy@gmail.com']="Developer";


           // $this->sendSMTPMail(APP_NAME." MATCH ALERT!!!",json_encode($output),$reciverEmail,SMTP_FROM_NAME,SMTP_FROM_EMAIL);

        }


        if($updatedMatchesCount>0){

            foreach ($matchTimeChanged as $key => $value) {

                $updatedMatchTime = strtotime($value['dateTimeGMT']);
                $matchUniqueId=$value['unique_id'];

                $query="UPDATE tbl_cricket_matches set match_date='$updatedMatchTime', close_date='$updatedMatchTime' where unique_id='$matchUniqueId'";
                $query_res = $this->conn->prepare($query);
                $query_res->execute();

                $updatedRowCount = $query_res->rowCount();
                $this->closeStatement($query_res);
                
            }

        }


        

        return $output;

    }


    public function now_playing_cron($match_unique_id) {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $time=time();

        $query  = "SELECT tcm.playing_squad_notification_at, tcm.unique_id, tcm.game_type_id, tcm.id, tcm.name as match_name, tcs.name as series_name  FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON(tcm.series_id=tcs.id) WHERE tcm.status='A' AND tcm.is_deleted='N'";
        if($match_unique_id>0){
            $query.=" AND tcm.unique_id='$match_unique_id'";
        }else{
            $query.=" AND tcm.match_progress= 'F' AND ((tcm.close_date-'$time')<=(60*60) AND (tcm.close_date-'$time')>=(1*60)) ORDER BY tcm.playing_squad_updated_at, tcm.close_date asc limit 1";
        }



        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows==0){

            $this->closeStatement($query_res);
            return "NO_RECORD";
        }


        $i=0;
        $output=array();

        while($array = $query_res->fetch(PDO::FETCH_ASSOC)){

            $output[$i]['unique_id']= $array['unique_id'];
            $output[$i]['match_name']= $array['match_name'];
            $output[$i]['series_name']= $array['series_name'];
            $output[$i]['playing_squad_notification_at']= $array['playing_squad_notification_at'];


            $Entity_object=new Entitysport();
            $palyers=$Entity_object->fantasy_squade($array['unique_id']);


            if(!empty($palyers) && count($palyers)>=14){

                  $allPlayingPlayers=implode(',', $palyers);

                  $update_playing_squad_players="UPDATE tbl_cricket_match_players set is_in_playing_squad='Y' where match_unique_id=? AND player_unique_id IN($allPlayingPlayers)";

                  $update_playing_squad_players_res  = $this->conn->prepare($update_playing_squad_players);
                  $update_playing_squad_players_res->bindParam(1,$array['unique_id']);

                  if(!$update_playing_squad_players_res->execute()){
                    $this->sql_error($update_playing_squad_players_res);
                  }

                  $this->closeStatement($update_playing_squad_players_res);



                  $update_playing_squad_players="UPDATE tbl_cricket_match_players set is_in_playing_squad='N' where match_unique_id=? AND player_unique_id NOT IN($allPlayingPlayers)";

                  $update_playing_squad_players_res  = $this->conn->prepare($update_playing_squad_players);
                  $update_playing_squad_players_res->bindParam(1,$array['unique_id']);

                  if(!$update_playing_squad_players_res->execute()){
                    $this->sql_error($update_playing_squad_players_res);
                  }

                  $this->closeStatement($update_playing_squad_players_res);


                  $notiTimeUpdate="";
                  if($array['playing_squad_notification_at']==0){
                    $notiTimeUpdate=", playing_squad_notification_at=$time";

                    $output[$i]['playing_squad_notification_at']= $time;
                  }

                  $query_match  = "UPDATE tbl_cricket_matches set playing_squad_updated='Y', playing_squad_updated_at=? $notiTimeUpdate WHERE unique_id=?";

                  $query_match_res  = $this->conn->prepare($query_match);
                  $query_match_res->bindParam(1,$time);
                  $query_match_res->bindParam(2,$array['unique_id']);

                  if(!$query_match_res->execute()){
                      $this->sql_error($query_match_res);
                  }

                  $this->closeStatement($query_match_res);


                  if($array['playing_squad_notification_at']==0){

                      $notifyToAllQuery="SELECT GROUP_CONCAT(DISTINCT customer_id) as users FROM `tbl_customer_logins` where customer_id>0"; 
                  
                      $notifyToAllQueryRes  = $this->conn->prepare($notifyToAllQuery);
                      if(!$notifyToAllQueryRes->execute()){
                          $this->sql_error($notifyToAllQueryRes);
                      }
                      $notifyToAllQueryResCount = $notifyToAllQueryRes->rowCount();

                      if($notifyToAllQueryResCount>0){

                        $notifyToAllQueryResData = $notifyToAllQueryRes->fetch(PDO::FETCH_ASSOC);
                        $usersData=$notifyToAllQueryResData['users'];
                        $this->closeStatement($notifyToAllQueryRes);
                        if(!empty($usersData)){
                            $notification_data=array();
                            $notification_data['noti_type']='lineup_out';
                            $notification_data['title']=$array['series_name'];
                            $alert_message = "Here are the Line ups for ".$array['match_name']."!";

                            $this->send_notification_and_save($notification_data,$usersData,$alert_message,false);

                        }

                      }else{
                        $this->closeStatement($notifyToAllQueryRes);
                      }
                  }
                  

            }else{

                  $query_match  = "UPDATE tbl_cricket_matches set playing_squad_updated_at=? WHERE unique_id=?";

                  $query_match_res  = $this->conn->prepare($query_match);
                  $query_match_res->bindParam(1,$time);
                  $query_match_res->bindParam(2,$array['unique_id']);

                  if(!$query_match_res->execute()){
                      $this->sql_error($query_match_res);
                  }

                  $this->closeStatement($query_match_res);

            }

            $output[$i]['players']= $palyers;

            $i++;
        }

         $this->closeStatement($query_res);

        return $output;
    }



    public function live_match_cron($match_unique_id) {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $time=time();
        $query  = "SELECT unique_id,game_type_id,id FROM tbl_cricket_matches WHERE match_progress= 'L' AND status='A' AND is_deleted='N'";
        if($match_unique_id>0){
            $query.=" AND unique_id='$match_unique_id'";
        }else{
            $query.=" ORDER BY points_updated_at asc limit 1";
        }

        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows==0){

            $this->closeStatement($query_res);

            return "NO_RECORD";
        }

        $query_points  = "SELECT * FROM tbl_cricket_points where status='A' AND is_deleted='N'";
        $query_points_res  = $this->conn->prepare($query_points);       
        $query_points_res->execute();

        $points=array();
        while($array_points = $query_points_res->fetch(PDO::FETCH_ASSOC)){
           $points[$array_points['game_type_id']][str_replace(" ","_",$array_points['meta_key'])]= $array_points['meta_value'];

        }
        $this->closeStatement($query_points_res);

        $i=0;
        $output=array();


       

        $i=0;
        $output=array();
        while($array = $query_res->fetch(PDO::FETCH_ASSOC)){

            $output[$i]['unique_id']= $array['unique_id'];
            if (!array_key_exists($array['game_type_id'],$points)){
                $output[$i]['game_type_id']= "game_type_id not exist";  
                $i++; 

                $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=? WHERE unique_id=?";

                $query_match_res  = $this->conn->prepare($query_match);
                $query_match_res->bindParam(1,$time);
                $query_match_res->bindParam(2,$array['unique_id']);

                if(!$query_match_res->execute()){
                    $this->sql_error($query_match_res);
                }

                $this->closeStatement($query_match_res);
                continue;
            }





          $game_type_point=$points[$array['game_type_id']];


          

                    if(empty($game_type_point)){
                            $output[$i]['game_type_point']="game_type_point is empty";
                            $i++;


                            $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=? WHERE unique_id=?";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$time);
                            $query_match_res->bindParam(2,$array['unique_id']);

                            if(!$query_match_res->execute()){
                                $this->sql_error($query_match_res);
                            }

                            $this->closeStatement($query_match_res);

                            continue;
                    }


                          $Entity_object=new Entitysport();
                          $api_data_array=$Entity_object->fantasy_summary($array['unique_id'],$game_type_point);

                          //print_r($api_data_array);die;

                          $palyers=$api_data_array['players'];

                          //$api_data_array=json_decode($api_data,true);
                         // $api_data_array=$api_data_array['data'];

                          if(empty($palyers)){

                            $output[$i]['players']="players is empty";
                            $output[$i]['api_data_array']=$api_data_array;
                            $i++; 


                            $match_progress_update= '';
                            if(!empty($api_data_array['man-of-the-match']) && is_array($api_data_array['man-of-the-match'])){
                                $match_progress_update=", match_progress='IR'";
                            }


                            $score_board_update='';
                            if(!empty($api_data_array['scorecard_data'])){
                                $team1_run=$api_data_array['scorecard_data']['team1_run'];
                                $team1_wicket=$api_data_array['scorecard_data']['team1_wicket'];
                                $team1_overs=$api_data_array['scorecard_data']['team1_overs'];
                                $team2_run=$api_data_array['scorecard_data']['team2_run'];
                                $team2_wicket=$api_data_array['scorecard_data']['team2_wicket'];
                                $team2_overs=$api_data_array['scorecard_data']['team2_overs'];
                                $score_board_notes=$api_data_array['scorecard_data']['score_board_notes'];

                                $score_board_update=", team1_run='$team1_run', team1_wicket='$team1_wicket', team1_overs='$team1_overs', team2_run='$team2_run', team2_wicket='$team2_wicket', team2_overs='$team2_overs', score_board_notes='$score_board_notes'";
                            }


                            $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=?".$match_progress_update.$score_board_update."  WHERE unique_id=? ";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$time);
                            $query_match_res->bindParam(2,$array['unique_id']);

                            if(!$query_match_res->execute()){
                                $this->sql_error($query_match_res);
                            }

                            $this->closeStatement($query_match_res);


                            continue;
                          }

                            if(!empty($palyers)){
                                
                                foreach($palyers as $player_key=>$palyers_insert){

			                         $update_match_player_stats_query = "INSERT INTO tbl_cricket_match_players_stats SET match_unique_id = ?, player_unique_id=?, Being_Part_Of_Eleven = ?, Being_Part_Of_Eleven_Value = ?, Every_Run_Scored=?, Every_Run_Scored_Value=?, Dismiss_For_A_Duck=?, Dismiss_For_A_Duck_Value=?, Every_Boundary_Hit=?, Every_Boundary_Hit_Value=?, Every_Six_Hit=?, Every_Six_Hit_Value=?, Half_Century=?, Half_Century_Value=?, Century=?, Century_Value=?, Wicket=?, Wicket_Value=?, Maiden_Over=?, Maiden_Over_Value=?, Four_Wicket=?, Four_Wicket_Value=?, Five_Wicket=?, Five_Wicket_Value=?, Catch=?, Catch_Value=?, Catch_And_Bowled=?, Catch_And_Bowled_Value=?, Stumping=?, Stumping_Value=?, Run_Out=?, Run_Out_Value=?, Strike_Rate=?, Strike_Rate_Value=?, Economy_Rate=?, Economy_Rate_Value=?, Thirty_Runs=?, Thirty_Runs_Value=?, Three_Wicket=?, Three_Wicket_Value=?, Two_Wicket=?, Two_Wicket_Value=?, Run_Out_Catcher=?, Run_Out_Catcher_Value=?, Run_Out_Thrower=?, Run_Out_Thrower_Value=?, updated=? ON DUPLICATE KEY UPDATE Being_Part_Of_Eleven = ?, Being_Part_Of_Eleven_Value = ?, Every_Run_Scored=?, Every_Run_Scored_Value=?, Dismiss_For_A_Duck=?, Dismiss_For_A_Duck_Value=?, Every_Boundary_Hit=?, Every_Boundary_Hit_Value=?, Every_Six_Hit=?, Every_Six_Hit_Value=?, Half_Century=?, Half_Century_Value=?, Century=?, Century_Value=?, Wicket=?, Wicket_Value=?, Maiden_Over=?, Maiden_Over_Value=?, Four_Wicket=?, Four_Wicket_Value=?, Five_Wicket=?, Five_Wicket_Value=?, Catch=?, Catch_Value=?, Catch_And_Bowled=?, Catch_And_Bowled_Value=?, Stumping=?, Stumping_Value=?, Run_Out=?, Run_Out_Value=?, Strike_Rate=?, Strike_Rate_Value=?, Economy_Rate=?, Economy_Rate_Value=?, Thirty_Runs=?, Thirty_Runs_Value=?, Three_Wicket=?, Three_Wicket_Value=?, Two_Wicket=?, Two_Wicket_Value=?, Run_Out_Catcher=?, Run_Out_Catcher_Value=?, Run_Out_Thrower=?, Run_Out_Thrower_Value=?, updated=?";


                                     
                                        $update_match_player_stats_query_res = $this->conn->prepare($update_match_player_stats_query);

                                        $pdocount=0;
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $array['unique_id']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $player_key);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Being_Part_Of_Eleven']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Being_Part_Of_Eleven_Value']);


                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Run_Scored']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Run_Scored_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Dismiss_For_A_Duck']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Dismiss_For_A_Duck_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Boundary_Hit']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Boundary_Hit_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Six_Hit']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Six_Hit_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Half_Century']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Half_Century_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Century']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Century_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Maiden_Over']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Maiden_Over_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Four_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Four_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Five_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Five_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_And_Bowled']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_And_Bowled_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Stumping']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Stumping_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Strike_Rate']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Strike_Rate_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Economy_Rate']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Economy_Rate_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Thirty_Runs']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Thirty_Runs_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Three_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Three_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Two_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Two_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Catcher']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Catcher_Value']);

								        
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Thrower']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Thrower_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $time);

								       
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Being_Part_Of_Eleven']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Being_Part_Of_Eleven_Value']);


                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Run_Scored']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Run_Scored_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Dismiss_For_A_Duck']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Dismiss_For_A_Duck_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Boundary_Hit']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Boundary_Hit_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Six_Hit']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Every_Six_Hit_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Half_Century']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Half_Century_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Century']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Century_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Maiden_Over']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Maiden_Over_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Four_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Four_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Five_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Five_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_And_Bowled']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Catch_And_Bowled_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Stumping']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Stumping_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Strike_Rate']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Strike_Rate_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Economy_Rate']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Economy_Rate_Value']);


                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Thirty_Runs']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Thirty_Runs_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Three_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Three_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Two_Wicket']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Two_Wicket_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Catcher']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Catcher_Value']);


                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Thrower']);
                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $palyers_insert['Run_Out_Thrower_Value']);

                                        $update_match_player_stats_query_res->bindParam(++$pdocount, $time);

                                               
                                        if(!$update_match_player_stats_query_res->execute()){
                                            $this->sql_error($update_match_player_stats_query_res);
                                        }

                                        $this->closeStatement($update_match_player_stats_query_res);


                                        $update_data_query  = "UPDATE tbl_cricket_match_players set points=?  WHERE match_unique_id=? AND player_unique_id=?";

                                        $update_data_query_res  = $this->conn->prepare($update_data_query);
                                        $update_data_query_res->bindParam(1,$palyers_insert['total_points']);
                                        $update_data_query_res->bindParam(2,$array['unique_id']);
                                        $update_data_query_res->bindParam(3,$player_key);
                                        
                                        if(!$update_data_query_res->execute()){
                                            $this->sql_error($update_data_query_res);
                                        }

                                        $this->closeStatement($update_data_query_res);



                                }

                                $match_progress_update= '';
                                if(!empty($api_data_array['man-of-the-match']) && is_array($api_data_array['man-of-the-match'])){
                                    $match_progress_update=", match_progress='IR'";
                                }

                                $score_board_update='';
                                if(!empty($api_data_array['scorecard_data'])){
                                    $team1_run=$api_data_array['scorecard_data']['team1_run'];
                                    $team1_wicket=$api_data_array['scorecard_data']['team1_wicket'];
                                    $team1_overs=$api_data_array['scorecard_data']['team1_overs'];
                                    $team2_run=$api_data_array['scorecard_data']['team2_run'];
                                    $team2_wicket=$api_data_array['scorecard_data']['team2_wicket'];
                                    $team2_overs=$api_data_array['scorecard_data']['team2_overs'];
                                    $score_board_notes=$api_data_array['scorecard_data']['score_board_notes'];

                                    $score_board_update=", team1_run='$team1_run', team1_wicket='$team1_wicket', team1_overs='$team1_overs', team2_run='$team2_run', team2_wicket='$team2_wicket', team2_overs='$team2_overs', score_board_notes='$score_board_notes'";
                                }

                                $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=?".$match_progress_update.$score_board_update."  WHERE unique_id=? ";

                                $query_match_res  = $this->conn->prepare($query_match);
                                $query_match_res->bindParam(1,$time);
                                $query_match_res->bindParam(2,$array['unique_id']);

                                if(!$query_match_res->execute()){
                                    $this->sql_error($query_match_res);
                                }

                                $this->closeStatement($query_match_res);

                                $this->update_point_and_rank($array['unique_id'],$array['id']);

                            }

                            $output[$i]['api_data_array']= $api_data_array;

                            $i++;    


                     
            
                    }
                
         

        $this->closeStatement($query_res);

        return $output;

       
    }

    public function update_point_and_rank($match_unique_id,$match_id){

        $query_active_contest="SELECT id FROM `tbl_cricket_contest_matches` WHERE `match_id` = '$match_id' AND `status` = 'A' AND `is_deleted` = 'N'";
        $query_active_contest_res  = $this->conn->prepare($query_active_contest);
        if(!$query_active_contest_res->execute()){
            $this->sql_error($query_active_contest_res);

        }
        $query_num_rows = $query_active_contest_res->rowCount();
        if($query_num_rows != 0){
            while($contests = $query_active_contest_res->fetch(PDO::FETCH_ASSOC)){
                $match_contest_id=$contests['id'];

                    $query  = "SELECT tccc.id,tccc.customer_team_id,((SELECT sum(tcmp.points*tcctp.multiplier) FROM tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON((tcctp.player_unique_id=tcmp.player_unique_id) AND (tcctp.match_unique_id =tcmp.match_unique_id)) where tcctp.customer_team_id =tccc.customer_team_id)) as points FROM tbl_cricket_customer_contests tccc where tccc.match_unique_id='$match_unique_id' AND tccc.match_contest_id='$match_contest_id' ORDER BY points DESC ";
                    $query_res  = $this->conn->prepare($query);
                    if(!$query_res->execute()){
                        $this->sql_error($query_res);

                    }

                    $num_rows = $query_res->rowCount();
                    if($num_rows != 0){
                        $i=1;

                        $rank_array=array();
                        while($array_points = $query_res->fetch(PDO::FETCH_ASSOC)){
                            $value_team_new_points=$array_points['points'];
                            $id=$array_points['id'];
                            $value_team_new_rank=$i;
                            if(array_key_exists($value_team_new_points,$rank_array)){
                                $value_team_new_rank=$rank_array[$value_team_new_points];

                            }else{
                                $rank_array[$value_team_new_points]= $value_team_new_rank;

                            }

                                $update_query="UPDATE tbl_cricket_customer_contests set old_rank=IF(old_rank=0,$value_team_new_rank,IF(new_rank!=$value_team_new_rank,new_rank,old_rank)),new_rank=$value_team_new_rank, old_points=IF(old_points=0,$value_team_new_points,IF(new_points!=$value_team_new_points,new_points,old_points)),new_points=$value_team_new_points where id ='$id'";
                                        $update_query_res  = $this->conn->prepare($update_query);
                                        //$update_query_res->execute();
                                        if(!$update_query_res->execute()){
                                            $this->sql_error($update_query_res);

                                        }

                                        $this->closeStatement($update_query_res);

                                 $i++;



                        }
                        $this->closeStatement($query_res);

                    }else{
                        $this->closeStatement($query_res);
                    }
            }
            $this->closeStatement($query_active_contest_res);
        }else{
            $this->closeStatement($query_active_contest_res);
        }


        return "SUCCESS";

        }




    public function update_match_score($match_unique_id) {
        $time=time();
        $where_query="(tcm.match_progress= 'L' OR tcm.match_progress= 'IR')";
        if($match_unique_id>0){
            $where_query="tcm.unique_id='".$match_unique_id."'";
        }

        $query  = "SELECT tcm.unique_id,tct.name as team_1_name,tct1.name as team_2_name FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_teams tct ON (tcm.team_1_id=tct.id) LEFT JOIN tbl_cricket_teams tct1 ON (tcm.team_2_id=tct1.id) WHERE $where_query AND tcm.status='A' AND tcm.is_deleted='N'";

        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $output=array();
        $i=0;
        while($array = $query_res->fetch(PDO::FETCH_ASSOC)){
            $unique_id=$array['unique_id'];
            $output[$i]['unique_id']=$array['unique_id'];
            $output[$i]['team_1_name']=$array['team_1_name'];
            $output[$i]['team_2_name']=$array['team_2_name'];


            $Entity_object=new Entitysport();
            $api_data_array=$Entity_object->fantasy_summary($unique_id,array());

            if(!empty($api_data_array['scorecard_data'])){
                $team1_run=$api_data_array['scorecard_data']['team1_run'];
                $team1_wicket=$api_data_array['scorecard_data']['team1_wicket'];
                $team1_overs=$api_data_array['scorecard_data']['team1_overs'];
                $team2_run=$api_data_array['scorecard_data']['team2_run'];
                $team2_wicket=$api_data_array['scorecard_data']['team2_wicket'];
                $team2_overs=$api_data_array['scorecard_data']['team2_overs'];
                $score_board_notes=$api_data_array['scorecard_data']['score_board_notes'];


                $output[$i]['team1_run']=$team1_run;
                $output[$i]['team1_wicket']=$team1_wicket;
                $output[$i]['team1_overs']=$team1_overs;
                $output[$i]['team2_run']=$team2_run;
                $output[$i]['team2_wicket']=$team2_wicket;
                $output[$i]['team2_overs']=$team2_overs;
                $output[$i]['score_board_notes']=$score_board_notes;


                $query_up_pdf  = "UPDATE tbl_cricket_matches set team1_run='$team1_run',team1_wicket='$team1_wicket',team1_overs='$team1_overs',team2_run='$team2_run',team2_wicket='$team2_wicket', team2_overs='$team2_overs',score_board_notes='$score_board_notes'  WHERE unique_id='$unique_id'";
                $query_up_pdf_res  = $this->conn->prepare($query_up_pdf);
                $query_up_pdf_res->execute();

                $this->closeStatement($query_up_pdf_res);

            }

            $i++;

        }

        $this->closeStatement($query_res);

        return $output;
    }


    public function get_match_score($match_unique_id) {
        $time=time();
        $query  = "SELECT tcm.unique_id,tcm.team_1_id,tcm.team_2_id,tcm.team1_run,tcm.team1_wicket,tcm.team1_overs,tcm.team2_run,tcm.team2_wicket,tcm.team2_overs,tcm.score_board_notes,tct.name as team_1_name,tct.sort_name as team_1_sort_name,tct1.name as team_2_name,tct1.sort_name as team_2_sort_name,tct.logo as team1_logo,tct1.logo as team2_logo,tcm.match_scorecard as score_card ,tcm.game_id as game_id FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_teams tct ON (tcm.team_1_id=tct.id) LEFT JOIN tbl_cricket_teams tct1 ON (tcm.team_2_id=tct1.id) WHERE tcm.unique_id='$match_unique_id'";
        
        $query_res  = $this->conn->prepare($query);
        $output=array();
        if($query_res->execute()){

            $num_rows = $query_res->rowCount();
               if($num_rows>0){

                    $i=0;
                    while($array = $query_res->fetch(PDO::FETCH_ASSOC)){


                            $output['scorecard'] = json_decode($array['score_card']);
                            $team1=array();
                            $team1['id']=$array['team_1_id'];
                            $team1['name']=$array['team_1_name'];
                            $team1['sort_name']=$array['team_1_sort_name'];
                            $team1['team_run']=$array['team1_run'];
                            $team1['team_wicket']=$array['team1_wicket'];
                            $team1['team_overs']=$array['team1_overs'];
                            $team1['team1_logo']=$array['team1_logo'];
                            $output['team1'] = $team1;
                            $team2=array();
                            $team2['id']=$array['team_2_id'];
                            $team2['name']=$array['team_2_name'];
                            $team2['sort_name']=$array['team_2_sort_name'];
                            $team2['team_run']=$array['team2_run'];
                            $team2['team_wicket']=$array['team2_wicket'];
                            $team2['team_overs']=$array['team2_overs'];
                            $team2['team2_logo']=$array['team2_logo'];
                            
                            $output['team2'] = $team2;

                            $output['match_type'] = $array['game_id'];

                            $output['score_board_notes'] = $array['score_board_notes'];

                    $output['team_settings'] = $this->get_team_settings($array['game_id']);


                        $i++;

                    }
                    
                    $this->closeStatement($query_res);

               }else{

                  $this->closeStatement($query_res);
                  return "NO_RECORD";
               }

        }else{

            $this->closeStatement($query_res);
            return "UNABLE_TO_PROCEED";
        }


        return $output;
    }

    public function send_email_cron() {
            $time=time();
            $query  = "SELECT * FROM tbl_email_cron WHERE is_send='N' limit 10";
            $query_res  = $this->conn->prepare($query);
            $query_res->execute();
            $num_rows = $query_res->rowCount();

            if($num_rows==0){

                return "No row found.";
            }

            $i=0;
            while($array = $query_res->fetch()){


                $this->sendSMTPMail($array['subject'],$array['message'],$array['toemail'],$array['toname'],$array['from_name'],$array['from_email']);
                $is_send='Y';
                $update_player_query = "UPDATE  tbl_email_cron SET is_send = ?, updatedat=? where id=?";
                $update_player_query_res = $this->conn->prepare($update_player_query);
                $update_player_query_res->bindParam(1, $is_send);
                $update_player_query_res->bindParam(2, $time);

                $update_player_query_res->bindParam(3, $array['id']);
                $update_player_query_res->execute();

            }

            return "SUCCESS";





    }


    public function generate_pdf_cron($match_unique_id=0, $match_contest_id=0) {


        //$this->includeDomPdfLib();
         $this->includeWKPdfLib();

        $this->includepdfTemplate();
        $ContestPdf=new contest_pdf();

        $query="SELECT id,unique_id,name FROM tbl_cricket_matches";
        if(empty($match_unique_id)){
            $query.=" where match_progress='L'";
        }else{
            $query.=" where unique_id='$match_unique_id'";
        }


         
        $query_res = $this->conn->prepare($query);
        $output=array();
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {

                while($matches = $query_res->fetch(PDO::FETCH_ASSOC)){
                        $match_id=$matches['id'];

                        

                        $query_contest="SELECT tccm.id,tccm.total_team,tccm.entry_fees,tccm.total_price,tccm.slug  FROM tbl_cricket_contest_matches tccm  where tccm.match_id='$match_id' ";

                        if(empty($match_contest_id)){
                            $query_contest.=" AND tccm.pdf_process='N' LIMIT 1";
                        }else{
                            $query_contest.=" AND tccm.id='$match_contest_id'";
                        }

                        $query_contest_res = $this->conn->prepare($query_contest);
                        $query_contest_res->execute();


                        $contests=array();
                         $i=0;

                        while($contestss = $query_contest_res->fetch(PDO::FETCH_ASSOC)){
                            $match_contest_id=$contestss['id'];

                            $query_up_pdf_process  = "UPDATE tbl_cricket_contest_matches set pdf_process='Y'  WHERE id=?";
                            $query_up_pdf_process_res  = $this->conn->prepare($query_up_pdf_process);
                            $query_up_pdf_process_res->bindParam(1,$match_contest_id);
                            if(!$query_up_pdf_process_res->execute()) {
                                $this->sql_error($query_up_pdf_process_res);
                            }

                            $this->closeStatement($query_up_pdf_process_res);




                            $contests[$i]['match_name']=$matches['name'];
                            $contests[$i]['match_contest_id']=$contestss['id'];
                            $contests[$i]['total_team']=$contestss['total_team'];
                            $contests[$i]['entry_fees']=$contestss['entry_fees'];
                            $contests[$i]['total_price']=$contestss['total_price'];
                            $contests[$i]['slug']=$contestss['slug'];


                            $query_teams="SELECT tc.team_name,tcct.name,tcct.more_name,tcct.customer_team_name,(SELECT GROUP_CONCAT(CONCAT(tcctp.position,'----',tcctp.multiplier,'----',tcp.name) ORDER BY tcctp.multiplier DESC SEPARATOR '--++--' ) FROM tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_players tcp ON(tcp.uniqueid=tcctp.player_unique_id) where tcctp.customer_team_id=tccc.customer_team_id ) as players FROM tbl_cricket_customer_contests tccc LEFT JOIN tbl_customers tc ON (tc.id=tccc.customer_id)  LEFT JOIN tbl_cricket_customer_teams tcct ON (tcct.id=tccc.customer_team_id) where tccc.match_contest_id='$match_contest_id'";
                            
                            echo $query_teams;
                            
                            
                            $query_teams_res = $this->conn->prepare($query_teams);
                            $query_teams_res->execute();
                            $contest_teams=array();
                            $j=0;
                            while($teams = $query_teams_res->fetch(PDO::FETCH_ASSOC)){

                                $contest_teams[$j]['team_name']=empty($teams['customer_team_name'])?$teams['team_name']:$teams['customer_team_name'];
                                $contest_teams[$j]['name']=($teams['more_name']=='0')?$teams['name']:$teams['more_name'];


                                $players_array=explode("--++--",$teams['players']);
                                $players=array();

                                $k=0;
                                foreach($players_array as $player_value){
                                    $player=explode("----", $player_value);
                                    $players[$k]['position']=$player[0];
                                    $players[$k]['multiplier']=$player[1];
                                    $players[$k]['name']=$player[2];

                                    $k++;
                                }
                                $contest_teams[$j]['players']=$players;

                                $j++;
                            }

                            $this->closeStatement($query_teams_res);

                            $contests[$i]['teams']=$contest_teams;

                            $html=$ContestPdf->genrate_pdf($contests[$i]);
                            //echo $html;die;


                            try {

                                ini_set("memory_limit", "2048M");
                                ini_set("max_execution_time", "1800");

                                $pdfname=$matches['unique_id']."_".$contests[$i]['match_contest_id']."_cricket.pdf";
                                $path=ROOT_DIRECTORY."uploads/gully11/pdf/".$pdfname;

                                $pdf = new Pdf();
                                $globaloptions = array(
                                        'no-outline',
                                        'encoding' => 'UTF-8',
                                        'orientation' => 'Landscape',
                                        'margin-left'=>'1mm',
                                        'margin-right'=>'1mm',
                                        'margin-top'=>'1mm',
                                        'margin-bottom'=>'1mm',
                                        'enable-javascript');
                                $pdf->setOptions($globaloptions);
                                $pdf->binary = WK_BINARY_PATH;
                                $pdf->addPage($html, array(), Pdf::TYPE_HTML );
                                if($pdf->saveAs($path)){
                                    $params=array();
                                    $params['upload_path']=PDF_PATH;
                                    $params['file_name']=$pdfname;
                                    $this->uploadOnAWS($params,$path);

                                    $key = $params['upload_path'].$params['file_name'];

                                    $query_up_pdf  = "UPDATE tbl_cricket_contest_matches set pdf='$pdfname', pdf_process='S'  WHERE id=?";
                                    $query_up_pdf_res  = $this->conn->prepare($query_up_pdf);
                                    $query_up_pdf_res->bindParam(1,$contests[$i]['match_contest_id']);


                                    if(!$query_up_pdf_res->execute()) {
                                        $this->sql_error($query_up_pdf_res);
                                    }
                                    $this->closeStatement($query_up_pdf_res);

                                    unlink($path);
                                }

                            } catch(Exception $e) {
                                echo "<pre>";
                                print_r($e);

                            }

                            $i++;
                        }

                        $this->closeStatement($query_contest_res);

                      $output[$match_id]=$contests;
                }

                $this->closeStatement($query_res);

            }else{
                $this->closeStatement($query_res);
            }

        }else{
            $this->closeStatement($query_res);
        }


       return $output;


    }




     public function includepdfTemplate() {
        $filesArr=get_required_files();
        $searchString='pdf.php';
        if(!in_array($searchString, $filesArr)) {
            require '../include/pdf.php';
        }
    }

     public function includeDomPdfLib() {
        $filesArr=get_required_files();
        $searchString=DOM_PDF_PATH;
        if(!in_array($searchString, $filesArr)) {
            require DOM_PDF_PATH;
        }
    }

    public function includeWKPdfLib() {
        $filesArr=get_required_files();
        $searchString=WK_PDF_PATH;
        if(!in_array($searchString, $filesArr)) {
            require WK_PDF_PATH;
        }
    }


    public function includecashfreepayout() {
        $filesArr=get_required_files();
        $searchString=CASHFREE_PAYOUT_FILES;
        if(!in_array($searchString, $filesArr)) {
            require CASHFREE_PAYOUT_FILES;
        }
    }

    public function includerazorpaypayout() {
        $filesArr=get_required_files();
        $searchString=RAZORPAY_PAYOUT_FILES;
        if(!in_array($searchString, $filesArr)) {
            require RAZORPAY_PAYOUT_FILES;
        }
    }

    


    public function get_match_players_stats($user_id,$match_unique_id) {

   

       $query = "SELECT tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one,
        tct_one.logo as team_image_one, tcmps.player_unique_id,tcmp.image,tcp.name,tcp.position,
        tcmp.points,tcmp.credits,tcmp.dream_team_player,tcmp.selected_by,tcmp.selected_as_caption,tcmp.selected_as_vccaption,
        tcmp.playing_role,
        IF((Select count(id) from tbl_cricket_customer_team_plyers tcctp where tcctp.match_unique_id=? AND tcctp.player_unique_id=tcmps.player_unique_id AND tcctp.customer_id='$user_id' )>0, 'Y', 'N') as is_my_player, (SELECT CONCAT(tcmps.Being_Part_Of_Eleven,'----',tcmps.Being_Part_Of_Eleven_Value,'----',tcmps.Every_Run_Scored,'----',tcmps.Every_Run_Scored_Value,'----',tcmps.Every_Boundary_Hit, '----',tcmps.Every_Boundary_Hit_Value,'----',tcmps.Every_Six_Hit,'----',tcmps.Every_Six_Hit_Value,'----',tcmps.Half_Century,'----',tcmps.Half_Century_Value,'----',tcmps.Century,'----',tcmps.Century_Value,'----',tcmps.Dismiss_For_A_Duck,'----',tcmps.Dismiss_For_A_Duck_Value,'----',tcmps.Wicket,'----',tcmps.Wicket_Value,'----',tcmps.Five_Wicket,'----',tcmps.Five_Wicket_Value,'----',tcmps.Four_Wicket,'----',tcmps.Four_Wicket_Value,'----',tcmps.Maiden_Over,'----',tcmps.Maiden_Over_Value,'----',tcmps.Catch,'----',tcmps.Catch_Value,'----',tcmps.Catch_And_Bowled,'----',tcmps.Catch_And_Bowled_Value,'----',tcmps.Stumping,'----',tcmps.Stumping_Value,'----',tcmps.Run_Out,'----',tcmps.Run_Out_Value,'----',tcmps.Strike_Rate,'----',tcmps.Strike_Rate_Value,'----',tcmps.Economy_Rate,'----',tcmps.Economy_Rate_Value,'----',tcmps.Thirty_Runs,'----',tcmps.Thirty_Runs_Value,'----',tcmps.Three_Wicket,'----',tcmps.Three_Wicket_Value,'----',tcmps.Two_Wicket,'----',tcmps.Two_Wicket_Value,'----',tcmps.Run_Out_Catcher,'----',tcmps.Run_Out_Catcher_Value,'----',tcmps.Run_Out_Thrower,'----',tcmps.Run_Out_Thrower_Value)) as player_events 
        from tbl_cricket_match_players_stats tcmps 
        INNER JOIN tbl_cricket_match_players tcmp ON(tcmps.player_unique_id=tcmp.player_unique_id AND tcmp.match_unique_id=?) LEFT JOIN tbl_cricket_players tcp ON(tcmp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_cricket_teams tct_one ON (tcmp.team_id=tct_one.id)  where tcmps.match_unique_id=?";
       
       
        $query_res = $this->conn->prepare($query); 
        $query_res->bindParam(1,$match_unique_id); 
        $query_res->bindParam(2,$match_unique_id); 
        $query_res->bindParam(3,$match_unique_id);
        // $query_res->bindParam(4,$match_unique_id);
        // $query_res->bindParam(5,$match_unique_id); 

               $query2 = 'select * FROM tbl_cricket_matches where unique_id=?';
             $query_res2 = $this->conn->prepare($query2); 
        $query_res2->bindParam(1,$match_unique_id); 
          $query_res2->execute();
         $row =    $query_res2->fetch(PDO::FETCH_ASSOC);

        if($query_res->execute()){
                $output = array();
                if ($query_res->rowCount() > 0) {
                    $i=0;
                    while($teamsdata = $query_res->fetch(PDO::FETCH_ASSOC)){

                            $playerTeam=array();
                            $playerTeam['id']=$teamsdata['team_id_one'];
                            $playerTeam['name']=$teamsdata['team_name_one'];
                            $playerTeam['sort_name']=$teamsdata['team_sort_name_one'];
                            $playerTeam['image']=!empty($teamsdata['team_image_one']) ? TEAMCRICKET_IMAGE_THUMB_URL.$teamsdata['team_image_one'] : NO_IMG_URL_TEAM;
                            

                            $teams=array();

                            $teams['player_unique_id'] = $teamsdata['player_unique_id'];
                            $teams['name'] = $teamsdata['name'];
                            $teams['position'] = $teamsdata['position'];
                            $teams['position'] = $teamsdata['playing_role'];
                            $teams['image'] = !empty($teamsdata['image']) ? PLAYER_IMAGE_THUMB_URL.$teamsdata['image'] : NO_IMG_URL_PLAYER;
                            $teams['points'] = $teamsdata['points'];
                            $teams['dream_team_player'] = $teamsdata['dream_team_player'];
                            $teams['credits'] = $teamsdata['credits'];

                            $teams['is_my_player'] = $teamsdata['is_my_player'];
                            $teams['selected_by'] = $teamsdata['selected_by'];
                            $teams['selected_as_caption'] = $teamsdata['selected_as_caption'];
                            $teams['selected_as_vccaption'] = $teamsdata['selected_as_vccaption'];
                            $teams['match_team_count']=0;
                            $teams['player_team_count']=0;
                            $teams['team_data']=$playerTeam;

                            $player_events=array();
                            $player_events_data=$teamsdata['player_events'];
                            if(!empty($player_events_data)){

                                $player_events_array=explode("----", $player_events_data);

                                $player_events_array_data=array();

                                $j=0;

                                $player_events_array_data['Being_Part_Of_Eleven']=$player_events_array[$j];$j++;
                                $player_events_array_data['Being_Part_Of_Eleven_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Run_Scored']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Run_Scored_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Boundary_Hit']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Boundary_Hit_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Six_Hit']=$player_events_array[$j];$j++;
                                $player_events_array_data['Every_Six_Hit_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Half_Century']=$player_events_array[$j];$j++;
                                $player_events_array_data['Half_Century_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Century']=$player_events_array[$j];$j++;
                                $player_events_array_data['Century_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Dismiss_For_A_Duck']=$player_events_array[$j];$j++;
                                $player_events_array_data['Dismiss_For_A_Duck_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Wicket']=$player_events_array[$j];$j++;
                                $player_events_array_data['Wicket_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Five_Wicket']=$player_events_array[$j];$j++;
                                $player_events_array_data['Five_Wicket_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Four_Wicket']=$player_events_array[$j];$j++;
                                $player_events_array_data['Four_Wicket_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Maiden_Over']=$player_events_array[$j];$j++;
                                $player_events_array_data['Maiden_Over_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Catch']=$player_events_array[$j];$j++;
                                $player_events_array_data['Catch_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Catch_And_Bowled']=$player_events_array[$j];$j++;
                                $player_events_array_data['Catch_And_Bowled_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Stumping']=$player_events_array[$j];$j++;
                                $player_events_array_data['Stumping_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Strike_Rate']=$player_events_array[$j];$j++;
                                $player_events_array_data['Strike_Rate_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Economy_Rate']=$player_events_array[$j];$j++;
                                $player_events_array_data['Economy_Rate_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Thirty_Runs']=$player_events_array[$j];$j++;
                                $player_events_array_data['Thirty_Runs_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Three_Wicket']=$player_events_array[$j];$j++;
                                $player_events_array_data['Three_Wicket_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Two_Wicket']=$player_events_array[$j];$j++;
                                $player_events_array_data['Two_Wicket_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out_Catcher']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out_Catcher_Value']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out_Thrower']=$player_events_array[$j];$j++;
                                $player_events_array_data['Run_Out_Thrower_Value']=$player_events_array[$j];$j++;


                                $l=0;


                                $player_events[$l]['key']="Starting 11";
                                $player_events[$l]['points']=$player_events_array_data['Being_Part_Of_Eleven'];
                                $player_events[$l]['value']=$player_events_array_data['Being_Part_Of_Eleven_Value']==1?"YES":"NO";
                                $l++;
                                
                                if($row['game_id']==1)
                                {
                                $player_events[$l]['key']="Runs";
                                $player_events[$l]['points']=$player_events_array_data['Every_Run_Scored'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Run_Scored_Value'];
                                $l++;
                                
                                $player_events[$l]['key']="4's";
                                $player_events[$l]['points']=$player_events_array_data['Every_Boundary_Hit'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Boundary_Hit_Value'];
                                $l++;
                                
                                $player_events[$l]['key']="6's";
                                $player_events[$l]['points']=$player_events_array_data['Every_Six_Hit'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Six_Hit_Value'];
                                $l++;

                                $halfCenturyValue=$player_events_array_data['Half_Century'];
                                $centuryValue=$player_events_array_data['Century'];
                                $thirtyRunsValue=$player_events_array_data['Thirty_Runs'];

                                $runBonusGiven=0;
                                if($thirtyRunsValue>0){
                                    $player_events[$l]['key']="30";
                                    $player_events[$l]['points']=$player_events_array_data['Thirty_Runs'];
                                    $player_events[$l]['value']=$player_events_array_data['Thirty_Runs_Value'];
                                    $runBonusGiven=1;
                                }


                                if($halfCenturyValue>0){
                                    $player_events[$l]['key']="50";
                                    $player_events[$l]['points']=$player_events_array_data['Half_Century'];
                                    $player_events[$l]['value']=$player_events_array_data['Half_Century_Value'];
                                    $runBonusGiven=1;
                                }


                                if($centuryValue>0){
                                    $player_events[$l]['key']="100";
                                    $player_events[$l]['points']=$player_events_array_data['Century'];
                                    $player_events[$l]['value']=$player_events_array_data['Century_Value'];
                                    $runBonusGiven=1;
                                }

                                if($runBonusGiven==0){
                                    $player_events[$l]['key']="50";
                                    $player_events[$l]['points']=0;
                                    $player_events[$l]['value']=0;
                                    
                                }

                                $l++;

                                $player_events[$l]['key']="Duck";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                $l++;


                                $player_events[$l]['key']="Wkts";
                                $player_events[$l]['points']=$player_events_array_data['Wicket'];
                                $player_events[$l]['value']=$player_events_array_data['Wicket_Value'];
                                $l++;


                                $TwoWktValue=$player_events_array_data['Two_Wicket'];
                                $ThreeWktValue=$player_events_array_data['Three_Wicket'];
                                $FourWktValue=$player_events_array_data['Four_Wicket'];
                                $FiveWktValue=$player_events_array_data['Five_Wicket'];

                                $wktBonusGiven=0;
                                if($TwoWktValue>0){
                                    $player_events[$l]['key']="2 Wkts";
                                    $player_events[$l]['points']=$player_events_array_data['Two_Wicket'];
                                    $player_events[$l]['value']=$player_events_array_data['Two_Wicket_Value'];
                                    $wktBonusGiven=1;
                                }

                                

                                if($ThreeWktValue>0){
                                    $player_events[$l]['key']="3 Wkts";
                                    $player_events[$l]['points']=$player_events_array_data['Three_Wicket'];
                                    $player_events[$l]['value']=$player_events_array_data['Three_Wicket_Value'];
                                    $wktBonusGiven=1;
                                }


                                if($FourWktValue>0){
                                    $player_events[$l]['key']="4 Wkts";
                                    $player_events[$l]['points']=$player_events_array_data['Four_Wicket'];
                                    $player_events[$l]['value']=$player_events_array_data['Four_Wicket_Value'];
                                    $wktBonusGiven=1;
                                }

                                if($FiveWktValue>0){
                                    $player_events[$l]['key']="5 Wkts";
                                    $player_events[$l]['points']=$player_events_array_data['Five_Wicket'];
                                    $player_events[$l]['value']=$player_events_array_data['Five_Wicket_Value'];
                                    $wktBonusGiven=1;
                                }

                                if($wktBonusGiven==0){
                                    $player_events[$l]['key']="4 Wkts";
                                    $player_events[$l]['points']=0;
                                    $player_events[$l]['value']=0;
                                    
                                }

                                $l++;

                                $player_events[$l]['key']="Maiden Over";
                                $player_events[$l]['points']=$player_events_array_data['Maiden_Over'];
                                $player_events[$l]['value']=$player_events_array_data['Maiden_Over_Value'];
                                $l++;

                                $player_events[$l]['key']="Catch";
                                $player_events[$l]['points']=$player_events_array_data['Catch'];
                                $player_events[$l]['value']=$player_events_array_data['Catch_Value'];
                                $l++;

                                $player_events[$l]['key']="Stumping";
                                $player_events[$l]['points']=$player_events_array_data['Stumping'];
                                $player_events[$l]['value']=$player_events_array_data['Stumping_Value'];
                                $l++;

                                $player_events[$l]['key']="Run Out";
                                $player_events[$l]['points']=$player_events_array_data['Run_Out']+$player_events_array_data['Run_Out_Thrower']+$player_events_array_data['Run_Out_Catcher'];
                                $player_events[$l]['value']=$player_events_array_data['Run_Out_Value']+$player_events_array_data['Run_Out_Thrower_Value']+$player_events_array_data['Run_Out_Catcher_Value'];
                                $l++;

                                $player_events[$l]['key']="Strike Rate";
                                $player_events[$l]['points']=$player_events_array_data['Strike_Rate'];
                                $player_events[$l]['value']=$player_events_array_data['Strike_Rate_Value'];
                                $l++;

                                $player_events[$l]['key']="Economy Rate";
                                $player_events[$l]['points']=$player_events_array_data['Economy_Rate'];
                                $player_events[$l]['value']=$player_events_array_data['Economy_Rate_Value'];
                                $l++;
                                
                                }
                                
                                if($row['game_id']==2)
                                {
                                     $player_events[$l]['key']="Goals";
                                $player_events[$l]['points']=$player_events_array_data['Every_Run_Scored'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Run_Scored_Value'];
                                $l++;
                                
                                 $player_events[$l]['key']="Assist";
                                $player_events[$l]['points']=$player_events_array_data['Every_Boundary_Hit'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Boundary_Hit_Value'];
                                $l++;
                                    
                                    
                                      $player_events[$l]['key']="shots on target";
                                $player_events[$l]['points']=$player_events_array_data['Every_Boundary_Hit'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Boundary_Hit_Value'];
                                $l++;
                                
                                  $player_events[$l]['key']="Chance Created";
                                $player_events[$l]['points']=$player_events_array_data['Every_Six_Hit'];
                                $player_events[$l]['value']=$player_events_array_data['Every_Six_Hit_Value'];
                                $l++;
                                
                                 $player_events[$l]['key']="Passes completed";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                $l++;
                                
                                  $player_events[$l]['key']="Tackle won";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                       $l++;
         
                                 $player_events[$l]['key']="Interception won";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                       $l++;
         
                                
                                   $player_events[$l]['key']="Saves";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                 $l++;
               
                                 $player_events[$l]['key']="Penalty saved";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                $l++;
                                  $player_events[$l]['key']="Clean sheet";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                           $player_events[$l]['key']="Substitute";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                      
                                                      $player_events[$l]['key']="Yellow card";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                       $player_events[$l]['key']="Red card";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                          
                                       
                                                       $player_events[$l]['key']="own goal";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                      
                                                           $player_events[$l]['key']="goals conceded";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                     
                                                      $player_events[$l]['key']="penalty missed";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                      
                                }
                                 if($row['game_id']==3)
                                {
                                             $player_events[$l]['key']="Points Scored";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                               $player_events[$l]['key']="Rebounds";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                               $player_events[$l]['key']="Assists";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                               $player_events[$l]['key']="Steals";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                               $player_events[$l]['key']="Blocks";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                                      
                                                                                $player_events[$l]['key']="Turn Overs";
                                $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
                                $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
                                                      $l++;
                                }
                            }
                            $teams['player_events']=$player_events;
                            $output[$i]=$teams;
                            $i++;
                    }
                    $this->closeStatement($query_res);
                    return $output;
                }else{

                    $this->closeStatement($query_res);
                    return "NO_RECORD";
                    
                }
        }else{

            $this->closeStatement($query_res);
            return "UNABLE_TO_PROCEED";
            
        }        
       
    }
    
    public function get_customer_match_team_stats($customer_team_id){
        $this->setGroupConcatLimit();
        $query="SELECT tcct.id,tcct.name as team_name,(SELECT GROUP_CONCAT(CONCAT(IFNULL(tct_one.name ,' '),'----',IFNULL(tct_one.sort_name,' '),'----',IFNULL(tct_one.id,' '),'----',IFNULL(tct_one.logo,' '),'----',IFNULL(tcp.position,' '),'----',IFNULL(tcmp.playing_role,' '),'----',tcmp.selected_by,'----',tcmp.selected_as_caption,'----',tcmp.selected_as_vccaption ,'----',tcctp.player_unique_id,'----',IFNULL(tcmp.image, '0'),'----',IFNULL(tcp.name,'0'),'----',tcmp.points,'----',tcmp.dream_team_player,'----',IFNULL(tcmps.Being_Part_Of_Eleven,' '),'----',IFNULL(tcmps.Being_Part_Of_Eleven_Value,' '),'----',IFNULL(tcmps.Every_Run_Scored,' '),'----',IFNULL(tcmps.Every_Run_Scored_Value,' '),'----',IFNULL(tcmps.Every_Boundary_Hit,' '), '----',IFNULL(tcmps.Every_Boundary_Hit_Value,' '),'----',IFNULL(tcmps.Every_Six_Hit,' '),'----',IFNULL(tcmps.Every_Six_Hit_Value,' '),'----',IFNULL(tcmps.Half_Century,' '),'----',IFNULL(tcmps.Half_Century_Value,' '),'----',IFNULL(tcmps.Century,' '),'----',IFNULL(tcmps.Century_Value,' '),'----',IFNULL(tcmps.Dismiss_For_A_Duck,' '),'----',IFNULL(tcmps.Dismiss_For_A_Duck_Value,' '),'----',IFNULL(tcmps.Wicket,' '),'----',IFNULL(tcmps.Wicket_Value,' '),'----',IFNULL(tcmps.Five_Wicket,' '),'----',IFNULL(tcmps.Five_Wicket_Value,' '),'----',IFNULL(tcmps.Four_Wicket,' '),'----',IFNULL(tcmps.Four_Wicket_Value,' '),'----',IFNULL(tcmps.Maiden_Over,' '),'----',IFNULL(tcmps.Maiden_Over_Value,' '),'----',IFNULL(tcmps.Catch,' '),'----',IFNULL(tcmps.Catch_Value,' '),'----',IFNULL(tcmps.Catch_And_Bowled,' '),'----',IFNULL(tcmps.Catch_And_Bowled_Value,' '),'----',IFNULL(tcmps.Stumping,' '),'----',IFNULL(tcmps.Stumping_Value,' '),'----',IFNULL(tcmps.Run_Out,' '),'----',IFNULL(tcmps.Run_Out_Value,' '),'----',IFNULL(tcmps.Strike_Rate,' '),'----',IFNULL(tcmps.Strike_Rate_Value,' '),'----',IFNULL(tcmps.Economy_Rate,' '),'----',IFNULL(tcmps.Economy_Rate_Value,' '),'----',IFNULL(tcmps.Thirty_Runs,' '),'----',IFNULL(tcmps.Thirty_Runs_Value,' '),'----',IFNULL(tcmps.Three_Wicket,' '),'----',IFNULL(tcmps.Three_Wicket_Value,' '),'----',IFNULL(tcmps.Two_Wicket,' '),'----',IFNULL(tcmps.Two_Wicket_Value,' '),'----',IFNULL(tcmps.Run_Out_Catcher,' '),'----',IFNULL(tcmps.Run_Out_Catcher_Value,' '),'----',IFNULL(tcmps.Run_Out_Thrower,' '),'----',IFNULL(tcmps.Run_Out_Thrower_Value,' ')) ORDER BY tcctp.position ASC SEPARATOR '--++--' ) from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_match_players_stats tcmps ON (tcctp.player_unique_id=tcmps.player_unique_id AND tcctp.match_unique_id=tcmps.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_cricket_teams tct_one ON (tcmp.team_id=tct_one.id) where tcctp.customer_team_id = tcct.id ) as players_data FROM `tbl_cricket_customer_teams` tcct WHERE tcct.id=?";
        
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_team_id);

        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
        if ($query_res->rowCount() == 0) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        
        $teamsdata = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);

        $team=array();

        $team['id']=$teamsdata['id'];
        $team['name']=$teamsdata['team_name'];
        
        
        $players_array=explode("--++--",$teamsdata['players_data']);

        $j=0;
        $players=array();
        foreach($players_array as $players_array_s){


             $per_player=explode("----", $players_array_s);

             $k=0;

             $player_events_array_data['tct_one_name']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_sort_name']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_id']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_logo']=$per_player[$k];$k++;
             $player_events_array_data['position']=$per_player[$k];$k++;
             $player_events_array_data['playing_role']=$per_player[$k];$k++;
             $player_events_array_data['selected_by']=$per_player[$k];$k++;
             $player_events_array_data['selected_as_caption']=$per_player[$k];$k++;
             $player_events_array_data['selected_as_vccaption']=$per_player[$k];$k++;
             $player_events_array_data['player_unique_id']=$per_player[$k];$k++;
             $player_events_array_data['tcmp_image']=$per_player[$k];$k++;
             $player_events_array_data['tcp_name']=$per_player[$k];$k++;
             $player_events_array_data['tcmp_points']=$per_player[$k];$k++;
             $player_events_array_data['dream_team_player']=$per_player[$k];$k++;

             $player_events_array_data['Being_Part_Of_Eleven']=$per_player[$k];$k++;
             $player_events_array_data['Being_Part_Of_Eleven_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Run_Scored']=$per_player[$k];$k++;
             $player_events_array_data['Every_Run_Scored_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Boundary_Hit']=$per_player[$k];$k++;
             $player_events_array_data['Every_Boundary_Hit_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Six_Hit']=$per_player[$k];$k++;
             $player_events_array_data['Every_Six_Hit_Value']=$per_player[$k];$k++;
             $player_events_array_data['Half_Century']=$per_player[$k];$k++;
             $player_events_array_data['Half_Century_Value']=$per_player[$k];$k++;
             $player_events_array_data['Century']=$per_player[$k];$k++;
             $player_events_array_data['Century_Value']=$per_player[$k];$k++;
             $player_events_array_data['Dismiss_For_A_Duck']=$per_player[$k];$k++;
             $player_events_array_data['Dismiss_For_A_Duck_Value']=$per_player[$k];$k++;
             $player_events_array_data['Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Five_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Five_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Four_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Four_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Maiden_Over']=$per_player[$k];$k++;
             $player_events_array_data['Maiden_Over_Value']=$per_player[$k];$k++;
             $player_events_array_data['Catch']=$per_player[$k];$k++;
             $player_events_array_data['Catch_Value']=$per_player[$k];$k++;
             $player_events_array_data['Catch_And_Bowled']=$per_player[$k];$k++;
             $player_events_array_data['Catch_And_Bowled_Value']=$per_player[$k];$k++;
             $player_events_array_data['Stumping']=$per_player[$k];$k++;
             $player_events_array_data['Stumping_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Value']=$per_player[$k];$k++;
             $player_events_array_data['Strike_Rate']=$per_player[$k];$k++;
             $player_events_array_data['Strike_Rate_Value']=$per_player[$k];$k++;
             $player_events_array_data['Economy_Rate']=$per_player[$k];$k++;
             $player_events_array_data['Economy_Rate_Value']=$per_player[$k];$k++;
             $player_events_array_data['Thirty_Runs']=$per_player[$k];$k++;
             $player_events_array_data['Thirty_Runs_Value']=$per_player[$k];$k++;
             $player_events_array_data['Three_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Three_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Two_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Two_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Catcher']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Catcher_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Thrower']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Thrower_Value']=$per_player[$k];$k++;


             $player=array();

             $player['match_team_count']=0;
             $player['player_team_count']=0;

             $playerTeam=array();
             $playerTeam['name']=$player_events_array_data['tct_one_name'];
             $playerTeam['sort_name']=$player_events_array_data['tct_one_sort_name'];
             $playerTeam['id']=$player_events_array_data['tct_one_id'];
             $playerTeam['image']=!empty($player_events_array_data['tct_one_logo']) ? TEAMCRICKET_IMAGE_THUMB_URL.$player_events_array_data['tct_one_logo'] : NO_IMG_URL_TEAM;
             $player['team_data']=$playerTeam;
            
             $player['position'] = $player_events_array_data['playing_role'];
             $player['selected_by'] =$player_events_array_data['selected_by'];
             $player['selected_as_caption'] =$player_events_array_data['selected_as_caption'];
             $player['selected_as_vccaption'] = $player_events_array_data['selected_as_vccaption'];
             $player['player_unique_id'] = $player_events_array_data['player_unique_id'];
             $player['image'] = !empty($player_events_array_data['tcmp_image']) ? PLAYER_IMAGE_THUMB_URL.$player_events_array_data['tcmp_image'] : NO_IMG_URL_PLAYER;
             $player['name'] = $player_events_array_data['tcp_name'];
             $player['points'] = $player_events_array_data['tcmp_points'];
             $player['dream_team_player'] = $player_events_array_data['dream_team_player'];

            

             $player_events=array();
             $l=0;

            if($player_events_array_data['Being_Part_Of_Eleven_Value']==1){
             $player_events[$l]['key']="Starting 11";
             $player_events[$l]['points']=$player_events_array_data['Being_Part_Of_Eleven'];
             $player_events[$l]['value']=$player_events_array_data['Being_Part_Of_Eleven_Value']==1?"YES":"NO";
             $l++;

             $player_events[$l]['key']="Runs";
             $player_events[$l]['points']=$player_events_array_data['Every_Run_Scored'];
             $player_events[$l]['value']=$player_events_array_data['Every_Run_Scored_Value'];
             $l++;

             $player_events[$l]['key']="4's";
             $player_events[$l]['points']=$player_events_array_data['Every_Boundary_Hit'];
             $player_events[$l]['value']=$player_events_array_data['Every_Boundary_Hit_Value'];
             $l++;

             $player_events[$l]['key']="6's";
             $player_events[$l]['points']=$player_events_array_data['Every_Six_Hit'];
             $player_events[$l]['value']=$player_events_array_data['Every_Six_Hit_Value'];
             $l++;

             $halfCenturyValue=$player_events_array_data['Half_Century'];
             $centuryValue=$player_events_array_data['Century'];
             $thirtyRunsValue=$player_events_array_data['Thirty_Runs'];

             $runBonusGiven=0;
             if($thirtyRunsValue>0){
                 $player_events[$l]['key']="30";
                 $player_events[$l]['points']=$player_events_array_data['Thirty_Runs'];
                 $player_events[$l]['value']=$player_events_array_data['Thirty_Runs_Value'];
                 $runBonusGiven=1;
             }

			

             if($halfCenturyValue>0){
                 $player_events[$l]['key']="50";
                 $player_events[$l]['points']=$player_events_array_data['Half_Century'];
                 $player_events[$l]['value']=$player_events_array_data['Half_Century_Value'];
                 $runBonusGiven=1;
             }

		

             if($centuryValue>0){
                 $player_events[$l]['key']="100";
                 $player_events[$l]['points']=$player_events_array_data['Century'];
                 $player_events[$l]['value']=$player_events_array_data['Century_Value'];
                 $runBonusGiven=1;
             }

             if($runBonusGiven==0){
                 $player_events[$l]['key']="50";
                 $player_events[$l]['points']=0;
                 $player_events[$l]['value']=0;
             }

             $l++;

             $player_events[$l]['key']="Duck";
             $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
             $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
             $l++;

		
             $player_events[$l]['key']="Wkts";
             $player_events[$l]['points']=$player_events_array_data['Wicket'];
             $player_events[$l]['value']=$player_events_array_data['Wicket_Value'];
             $l++;


             $TwoWktValue=$player_events_array_data['Two_Wicket'];
             $ThreeWktValue=$player_events_array_data['Three_Wicket'];
             $FourWktValue=$player_events_array_data['Four_Wicket'];
             $FiveWktValue=$player_events_array_data['Five_Wicket'];

             $wktBonusGiven=0;
             if($TwoWktValue>0){
                 $player_events[$l]['key']="2 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Two_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Two_Wicket_Value'];
                 $wktBonusGiven=1;
             }


             if($ThreeWktValue>0){
                 $player_events[$l]['key']="3 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Three_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Three_Wicket_Value'];
                 $wktBonusGiven=1;
             }


             if($FourWktValue>0){
                 $player_events[$l]['key']="4 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Four_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Four_Wicket_Value'];
                 $wktBonusGiven=1;
             }

             if($FiveWktValue>0){
                 $player_events[$l]['key']="5 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Five_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Five_Wicket_Value'];
                 $wktBonusGiven=1;
             }

             if($wktBonusGiven==0){
                 $player_events[$l]['key']="4 Wkts";
                 $player_events[$l]['points']=0;
                 $player_events[$l]['value']=0;
             }

             $l++;

             $player_events[$l]['key']="Maiden Over";
             $player_events[$l]['points']=$player_events_array_data['Maiden_Over'];
             $player_events[$l]['value']=$player_events_array_data['Maiden_Over_Value'];
             $l++;

             $player_events[$l]['key']="Catch";
             $player_events[$l]['points']=$player_events_array_data['Catch'];
             $player_events[$l]['value']=$player_events_array_data['Catch_Value'];
             $l++;

             $player_events[$l]['key']="Stumping";
             $player_events[$l]['points']=$player_events_array_data['Stumping'];
             $player_events[$l]['value']=$player_events_array_data['Stumping_Value'];
             $l++;

             $player_events[$l]['key']="Run Out";
             $player_events[$l]['points']=$player_events_array_data['Run_Out']+$player_events_array_data['Run_Out_Thrower']+$player_events_array_data['Run_Out_Catcher'];
             $player_events[$l]['value']=$player_events_array_data['Run_Out_Value']+$player_events_array_data['Run_Out_Thrower_Value']+$player_events_array_data['Run_Out_Catcher_Value'];
             $l++;

             $player_events[$l]['key']="Strike Rate";
             $player_events[$l]['points']=$player_events_array_data['Strike_Rate'];
             $player_events[$l]['value']=$player_events_array_data['Strike_Rate_Value'];
             $l++;

             $player_events[$l]['key']="Economy Rate";
             $player_events[$l]['points']=$player_events_array_data['Economy_Rate'];
             $player_events[$l]['value']=$player_events_array_data['Economy_Rate_Value'];
             $l++;
             }

             $player['player_events']=$player_events;

             $players[$j]=$player;
             $j++;

        }

        $team['players_stats']=$players;
                      
       
        return $team;
     
    }



    public function get_match_dream_team_stats($match_unique_id){
        $this->setGroupConcatLimit();

        $query="SELECT 0 as id,'dream team' as team_name,(SELECT GROUP_CONCAT(CONCAT(IFNULL(tct_one.name ,' '),'----',IFNULL(tct_one.sort_name,' '),'----',IFNULL(tct_one.id,' '),'----',IFNULL(tct_one.logo,' '),'----',IFNULL(tcp.position,'0'),'----',IFNULL(tcmp.playing_role,' '),'----',tcmp.selected_by,'----',tcmp.selected_as_caption,'----',tcmp.selected_as_vccaption ,'----',tcmp.player_unique_id,'----',IFNULL(tcmp.image, '0'),'----',IFNULL(tcp.name,'0'),'----',tcmp.points,'----',tcmp.dream_team_player,'----',IFNULL(tcmps.Being_Part_Of_Eleven,' '),'----',IFNULL(tcmps.Being_Part_Of_Eleven_Value,' '),'----',IFNULL(tcmps.Every_Run_Scored,' '),'----',IFNULL(tcmps.Every_Run_Scored_Value,' '),'----',IFNULL(tcmps.Every_Boundary_Hit,' '), '----',IFNULL(tcmps.Every_Boundary_Hit_Value,' '),'----',IFNULL(tcmps.Every_Six_Hit,' '),'----',IFNULL(tcmps.Every_Six_Hit_Value,' '),'----',IFNULL(tcmps.Half_Century,' '),'----',IFNULL(tcmps.Half_Century_Value,' '),'----',IFNULL(tcmps.Century,' '),'----',IFNULL(tcmps.Century_Value,' '),'----',IFNULL(tcmps.Dismiss_For_A_Duck,' '),'----',IFNULL(tcmps.Dismiss_For_A_Duck_Value,' '),'----',IFNULL(tcmps.Wicket,' '),'----',IFNULL(tcmps.Wicket_Value,' '),'----',IFNULL(tcmps.Five_Wicket,' '),'----',IFNULL(tcmps.Five_Wicket_Value,' '),'----',IFNULL(tcmps.Four_Wicket,' '),'----',IFNULL(tcmps.Four_Wicket_Value,' '),'----',IFNULL(tcmps.Maiden_Over,' '),'----',IFNULL(tcmps.Maiden_Over_Value,' '),'----',IFNULL(tcmps.Catch,' '),'----',IFNULL(tcmps.Catch_Value,' '),'----',IFNULL(tcmps.Catch_And_Bowled,' '),'----',IFNULL(tcmps.Catch_And_Bowled_Value,' '),'----',IFNULL(tcmps.Stumping,' '),'----',IFNULL(tcmps.Stumping_Value,' '),'----',IFNULL(tcmps.Run_Out,' '),'----',IFNULL(tcmps.Run_Out_Value,' '),'----',IFNULL(tcmps.Strike_Rate,' '),'----',IFNULL(tcmps.Strike_Rate_Value,' '),'----',IFNULL(tcmps.Economy_Rate,' '),'----',IFNULL(tcmps.Economy_Rate_Value,' '),'----',IFNULL(tcmps.Thirty_Runs,' '),'----',IFNULL(tcmps.Thirty_Runs_Value,' '),'----',IFNULL(tcmps.Three_Wicket,' '),'----',IFNULL(tcmps.Three_Wicket_Value,' '),'----',IFNULL(tcmps.Two_Wicket,' '),'----',IFNULL(tcmps.Two_Wicket_Value,' '),'----',IFNULL(tcmps.Run_Out_Catcher,' '),'----',IFNULL(tcmps.Run_Out_Catcher_Value,' '),'----',IFNULL(tcmps.Run_Out_Thrower,' '),'----',IFNULL(tcmps.Run_Out_Thrower_Value,' ')) ORDER BY tcmp.points DESC SEPARATOR '--++--' ) from  tbl_cricket_match_players tcmp  LEFT JOIN tbl_cricket_match_players_stats tcmps ON (tcmp.player_unique_id=tcmps.player_unique_id AND tcmp.match_unique_id=tcmps.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcmp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_cricket_teams tct_one ON (tcmp.team_id=tct_one.id) where tcmp.match_unique_id = ? AND tcmp.dream_team_player='Y') as players_data";


        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);
        // $query_res->bindParam(2, $match_unique_id);


        if (!$query_res->execute()) {
            $this->closeStatement($query_res);
             return "UNABLE_TO_PROCEED";
        }
            $teamsdata = $query_res->fetch(PDO::FETCH_ASSOC);

        if ($query_res->rowCount() == 0 || empty($teamsdata['players_data'])) {
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }

        $this->closeStatement($query_res);



        $team=array();

        $team['id']=$teamsdata['id'];
        $team['name']=$teamsdata['team_name'];
        

        $players_array=explode("--++--",$teamsdata['players_data']);


        $j=0;
        $players=array();
        foreach($players_array as $players_array_s){


             $per_player=explode("----", $players_array_s);

             $k=0;

             $player_events_array_data['tct_one_name']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_sort_name']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_id']=$per_player[$k];$k++;
             $player_events_array_data['tct_one_logo']=$per_player[$k];$k++;
             $player_events_array_data['position']=$per_player[$k];$k++;
             $player_events_array_data['playing_role']=$per_player[$k];$k++;
             $player_events_array_data['selected_by']=$per_player[$k];$k++;
             $player_events_array_data['selected_as_caption']=$per_player[$k];$k++;
             $player_events_array_data['selected_as_vccaption']=$per_player[$k];$k++;
             $player_events_array_data['player_unique_id']=$per_player[$k];$k++;
             $player_events_array_data['tcmp_image']=$per_player[$k];$k++;
             $player_events_array_data['tcp_name']=$per_player[$k];$k++;
             $player_events_array_data['tcmp_points']=$per_player[$k];$k++;
             $player_events_array_data['dream_team_player']=$per_player[$k];$k++;

             $player_events_array_data['Being_Part_Of_Eleven']=$per_player[$k];$k++;
             $player_events_array_data['Being_Part_Of_Eleven_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Run_Scored']=$per_player[$k];$k++;
             $player_events_array_data['Every_Run_Scored_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Boundary_Hit']=$per_player[$k];$k++;
             $player_events_array_data['Every_Boundary_Hit_Value']=$per_player[$k];$k++;
             $player_events_array_data['Every_Six_Hit']=$per_player[$k];$k++;
             $player_events_array_data['Every_Six_Hit_Value']=$per_player[$k];$k++;
             $player_events_array_data['Half_Century']=$per_player[$k];$k++;
             $player_events_array_data['Half_Century_Value']=$per_player[$k];$k++;
             $player_events_array_data['Century']=$per_player[$k];$k++;
             $player_events_array_data['Century_Value']=$per_player[$k];$k++;
             $player_events_array_data['Dismiss_For_A_Duck']=$per_player[$k];$k++;
             $player_events_array_data['Dismiss_For_A_Duck_Value']=$per_player[$k];$k++;
             $player_events_array_data['Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Five_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Five_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Four_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Four_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Maiden_Over']=$per_player[$k];$k++;
             $player_events_array_data['Maiden_Over_Value']=$per_player[$k];$k++;
             $player_events_array_data['Catch']=$per_player[$k];$k++;
             $player_events_array_data['Catch_Value']=$per_player[$k];$k++;
             $player_events_array_data['Catch_And_Bowled']=$per_player[$k];$k++;
             $player_events_array_data['Catch_And_Bowled_Value']=$per_player[$k];$k++;
             $player_events_array_data['Stumping']=$per_player[$k];$k++;
             $player_events_array_data['Stumping_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Value']=$per_player[$k];$k++;
             $player_events_array_data['Strike_Rate']=$per_player[$k];$k++;
             $player_events_array_data['Strike_Rate_Value']=$per_player[$k];$k++;
             $player_events_array_data['Economy_Rate']=$per_player[$k];$k++;
             $player_events_array_data['Economy_Rate_Value']=$per_player[$k];$k++;
             $player_events_array_data['Thirty_Runs']=$per_player[$k];$k++;
             $player_events_array_data['Thirty_Runs_Value']=$per_player[$k];$k++;
             $player_events_array_data['Three_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Three_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Two_Wicket']=$per_player[$k];$k++;
             $player_events_array_data['Two_Wicket_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Catcher']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Catcher_Value']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Thrower']=$per_player[$k];$k++;
             $player_events_array_data['Run_Out_Thrower_Value']=$per_player[$k];$k++;


            $player=array();

            $k=0;

             $player['match_team_count']=0;
             $player['player_team_count']=0;

            $playerTeam=array();
            $playerTeam['name']=$per_player[$k];$k++;
            $playerTeam['sort_name']=$per_player[$k];$k++;
            $playerTeam['id']=$per_player[$k];$k++;           
            $playerTeam['image']=!empty($per_player[$k]) ? TEAMCRICKET_IMAGE_THUMB_URL.$per_player[$k] : NO_IMG_URL_TEAM;$k++;
            $player['team_data']=$playerTeam;

            $player['position'] = $per_player[$k];$k++;
            $player['position'] = $per_player[$k];$k++;
            $player['selected_by'] = $per_player[$k];$k++;
            $player['selected_as_caption'] = $per_player[$k];$k++;
            $player['selected_as_vccaption'] = $per_player[$k];$k++;
            $player['player_unique_id'] = $per_player[$k];$k++;
            $player['image'] = !empty($per_player[$k]) ? PLAYER_IMAGE_THUMB_URL.$per_player[$k] : NO_IMG_URL_PLAYER;$k++;
            $player['name'] = $per_player[$k];$k++;
            $player['points'] = $per_player[$k];$k++;
            $player['dream_team_player'] = $per_player[$k];$k++;

             $player_events=array();
             $l=0;
             if($player_events_array_data['Being_Part_Of_Eleven_Value']==1){
             $player_events[$l]['key']="Starting 11";
             $player_events[$l]['points']=$player_events_array_data['Being_Part_Of_Eleven'];
             $player_events[$l]['value']=$player_events_array_data['Being_Part_Of_Eleven_Value']==1?"YES":"NO";
             $l++;

             $player_events[$l]['key']="Runs";
             $player_events[$l]['points']=$player_events_array_data['Every_Run_Scored'];
             $player_events[$l]['value']=$player_events_array_data['Every_Run_Scored_Value'];
             $l++;

             $player_events[$l]['key']="4's";
             $player_events[$l]['points']=$player_events_array_data['Every_Boundary_Hit'];
             $player_events[$l]['value']=$player_events_array_data['Every_Boundary_Hit_Value'];
             $l++;

             $player_events[$l]['key']="6's";
             $player_events[$l]['points']=$player_events_array_data['Every_Six_Hit'];
             $player_events[$l]['value']=$player_events_array_data['Every_Six_Hit_Value'];
             $l++;

             $halfCenturyValue=$player_events_array_data['Half_Century'];
             $centuryValue=$player_events_array_data['Century'];
             $thirtyRunsValue=$player_events_array_data['Thirty_Runs'];

             $runBonusGiven=0;
             if($thirtyRunsValue>0){
                 $player_events[$l]['key']="30";
                 $player_events[$l]['points']=$player_events_array_data['Thirty_Runs'];
                 $player_events[$l]['value']=$player_events_array_data['Thirty_Runs_Value'];
                 $runBonusGiven=1;
             }

           

             if($halfCenturyValue>0){
                 $player_events[$l]['key']="50";
                 $player_events[$l]['points']=$player_events_array_data['Half_Century'];
                 $player_events[$l]['value']=$player_events_array_data['Half_Century_Value'];
                 $runBonusGiven=1;
             }

           

             if($centuryValue>0){
                 $player_events[$l]['key']="100";
                 $player_events[$l]['points']=$player_events_array_data['Century'];
                 $player_events[$l]['value']=$player_events_array_data['Century_Value'];
                 $runBonusGiven=1;
             }

             if($runBonusGiven==0){
                 $player_events[$l]['key']="50";
                 $player_events[$l]['points']=0;
                 $player_events[$l]['value']=0;
             }

             $l++;

             $player_events[$l]['key']="Duck";
             $player_events[$l]['points']=$player_events_array_data['Dismiss_For_A_Duck'];
             $player_events[$l]['value']=$player_events_array_data['Dismiss_For_A_Duck_Value']==1?"YES":"NO";
             $l++;

          

             $player_events[$l]['key']="Wkts";
             $player_events[$l]['points']=$player_events_array_data['Wicket'];
             $player_events[$l]['value']=$player_events_array_data['Wicket_Value'];
             $l++;


             $TwoWktValue=$player_events_array_data['Two_Wicket'];
             $ThreeWktValue=$player_events_array_data['Three_Wicket'];
             $FourWktValue=$player_events_array_data['Four_Wicket'];
             $FiveWktValue=$player_events_array_data['Five_Wicket'];

             $wktBonusGiven=0;
             if($TwoWktValue>0){
                 $player_events[$l]['key']="2 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Two_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Two_Wicket_Value'];
                 $wktBonusGiven=1;
             }

		

             if($ThreeWktValue>0){
                 $player_events[$l]['key']="3 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Three_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Three_Wicket_Value'];
                 $wktBonusGiven=1;
             }

			

             if($FourWktValue>0){
                 $player_events[$l]['key']="4 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Four_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Four_Wicket_Value'];
                 $wktBonusGiven=1;
             }

             if($FiveWktValue>0){
                 $player_events[$l]['key']="5 Wkts";
                 $player_events[$l]['points']=$player_events_array_data['Five_Wicket'];
                 $player_events[$l]['value']=$player_events_array_data['Five_Wicket_Value'];
                 $wktBonusGiven=1;
             }

             if($wktBonusGiven==0){
                 $player_events[$l]['key']="4 Wkts";
                 $player_events[$l]['points']=0;
                 $player_events[$l]['value']=0;
             }

             $l++;

             $player_events[$l]['key']="Maiden Over";
             $player_events[$l]['points']=$player_events_array_data['Maiden_Over'];
             $player_events[$l]['value']=$player_events_array_data['Maiden_Over_Value'];
             $l++;

             $player_events[$l]['key']="Catch";
             $player_events[$l]['points']=$player_events_array_data['Catch'];
             $player_events[$l]['value']=$player_events_array_data['Catch_Value'];
             $l++;

             $player_events[$l]['key']="Stumping";
             $player_events[$l]['points']=$player_events_array_data['Stumping'];
             $player_events[$l]['value']=$player_events_array_data['Stumping_Value'];
             $l++;

             $player_events[$l]['key']="Run Out";
             $player_events[$l]['points']=$player_events_array_data['Run_Out']+$player_events_array_data['Run_Out_Thrower']+$player_events_array_data['Run_Out_Catcher'];
             $player_events[$l]['value']=$player_events_array_data['Run_Out_Value']+$player_events_array_data['Run_Out_Thrower_Value']+$player_events_array_data['Run_Out_Catcher_Value'];
             $l++;

             $player_events[$l]['key']="Strike Rate";
             $player_events[$l]['points']=$player_events_array_data['Strike_Rate'];
             $player_events[$l]['value']=$player_events_array_data['Strike_Rate_Value'];
             $l++;

             $player_events[$l]['key']="Economy Rate";
             $player_events[$l]['points']=$player_events_array_data['Economy_Rate'];
             $player_events[$l]['value']=$player_events_array_data['Economy_Rate_Value'];
             $l++;
             }




            $player['player_events']=$player_events;

            $players[$j]=$player;
            $j++;

        }

        $team['players_stats']=$players;


        return $team;

    }
    
    
    public function get_slider(){
        
        $query_slider  = "SELECT ts.id,ts.image,ts.match_unique_id,ts.content FROM tbl_sliders ts WHERE ts.is_deleted='N' AND ts.status='A' ORDER BY ts.slider_order ASC";
        $query_slider_res  = $this->conn->prepare($query_slider);
        $query_slider_res->execute();
        $num_rows = $query_slider_res->rowCount();
        if($num_rows==0){
            $this->closeStatement($query_slider_res);
            return "NO_RECORD";
        }
        $i=0;
        $output=array();
        while($data = $query_slider_res->fetch()){
            $slider=array();
            $slider['id']=$data['id'];
            $slider['match']=NULL;
            if($data['match_unique_id']>0){
                $match=$this->get_match_data($data['match_unique_id']);
                if(!empty($match)){
                    $slider['match']=$match;
                }
            }
            $slider['content']=$data['content'];
            $slider['image_thumb']=!empty($data['image']) ? SLIDER_IMAGE_THUMB_URL.$data['image'] : NO_IMG_URL;
            $slider['image_large']=!empty($data['image']) ? SLIDER_IMAGE_LARGE_URL.$data['image'] : NO_IMG_URL;
            $output[$i]=$slider;
            $i++;
        }
        $this->closeStatement($query_slider_res);

        return $output;
        
    }

    public function update_notification_time($user_id){

        $time=time();
        $update_last_login_query = "UPDATE tbl_customers SET noti_seen_time=? WHERE id=?";
        $update_last_login  = $this->conn->prepare($update_last_login_query);
        $update_last_login->bindParam(1,$time);
        $update_last_login->bindParam(2,$user_id);
        if(!$update_last_login->execute()){
            $this->sql_error($update_last_login);
        }

        $time=time();
        $update_last_login_query = "UPDATE tbl_notifications SET is_seen=? WHERE users_id=?";
        $update_last_login  = $this->conn->prepare($update_last_login_query);
        $intStatus =1;
        $update_last_login->bindParam(1,$intStatus);
        $update_last_login->bindParam(2,$user_id);
        if(!$update_last_login->execute()){
            $this->sql_error($update_last_login);
        }


        $this->closeStatement($update_last_login);

    }


    public function get_notification_counter($user_id){

        $query = "SELECT IFNULL(count(tn.id),0) as noticount FROM tbl_notifications tn WHERE tn.is_promotional='0' AND FIND_IN_SET(?, tn.users_id) AND tn.created>(SELECT noti_seen_time from tbl_customers where id=?)";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);

        if(!$query_res->execute()){
            $this->sql_error($query_res);
        }
        $data = $query_res->fetch();
        $this->closeStatement($query_res);
        return $data['noticount'];
    }



    public function get_notifications($user_id,$page_no=0){

        $query = "SELECT tn.title,tn.notification,tn.image,tn.sender_type,tn.created FROM tbl_notifications tn WHERE tn.is_promotional='0' AND FIND_IN_SET(?, tn.users_id) ORDER BY tn.created DESC";
        if($page_no>0){
            $limit=20;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        if(!$query_res->execute()){
            $this->sql_error($query_res);
        }
        $num_rows = $query_res->rowCount();
        if($num_rows==0){
            $this->closeStatement($query_res);
            return "NO_RECORD";
        }
        $i=0;
        $output=array();
        while($data = $query_res->fetch()){
            $noti=array();
            $noti['title']=$data['title'];
            $noti['notification']=$data['notification'];
            $noti['sender_type']=$data['sender_type'];
            $noti['created']=$data['created'];
            $noti['image_thumb']=!empty($data['image']) ? NOTIFICATION_IMAGE_THUMB_URL.$data['image'] :'';
            $noti['image_large']=!empty($data['image']) ? NOTIFICATION_IMAGE_LARGE_URL.$data['image'] : '';
            $output[$i]=$noti;
            $i++;
        }

        $this->closeStatement($query_res);

        $this->update_notification_time($user_id);

        return $output;

    }


    public function get_playing_history($user_id){
        
        /* $query_tax  = "SELECT IFNULL(COUNT(DISTINCT(match_contest_id)),0) AS contests, IFNULL(COUNT(DISTINCT(match_unique_id)),0) AS matches,sum(case win_amount when 0 then 0  ELSE 1 end)AS wins,(SELECT IFNULL(Count(DISTINCT(series_id)),0) FROM tbl_cricket_matches WHERE unique_id IN(SELECT DISTINCT(match_unique_id) FROM tbl_cricket_customer_contests WHERE customer_id='$user_id')) AS series FROM tbl_cricket_customer_contests WHERE customer_id='$user_id'";
        $query_tax_res  = $this->conn->prepare($query_tax);
            if(!$query_tax_res->execute()){
              $this->sql_error($query_tax_res); 
            }
        
        $array = $query_tax_res->fetch();*/
        $cricket_data=$this->get_cricket_playing_history($user_id);
        //$kabaddi_data=$this->get_kabaddi_playing_history($user_id);
        //$soccer_data=$this->get_soccer_playing_history($user_id);
        $playing=array();

            $playing['contests']=$cricket_data['contests']/*+$kabaddi_data['contests']+$soccer_data['contests']*/;
            $playing['matches']=$cricket_data['matches']/*+$kabaddi_data['matches']+$soccer_data['matches']*/;
            $playing['series']=$cricket_data['series']/*+$kabaddi_data['series']+$soccer_data['series']*/;
            $playing['wins']=$cricket_data['wins']/*+$kabaddi_data['wins']+$soccer_data['wins']*/;

        
        return $playing;

    }


    public function get_cricket_playing_history($user_id){
        
        $query_tax  = "SELECT IFNULL(COUNT(DISTINCT(match_contest_id)),0) AS contests,
         IFNULL(COUNT(DISTINCT(match_unique_id)),0) AS matches,IFNULL(sum(case win_amount when 0 then 0  ELSE 1 end),0) AS wins,IFNULL((case win_amount when 0 then 0  ELSE win_amount end),0) AS wins_amt,
         (SELECT IFNULL(Count(DISTINCT(series_id)),0) 
         FROM tbl_cricket_matches WHERE unique_id IN(SELECT DISTINCT(match_unique_id)
          FROM tbl_cricket_customer_contests WHERE customer_id='$user_id')) AS series
           FROM tbl_cricket_customer_contests WHERE customer_id='$user_id'";
        $query_tax_res  = $this->conn->prepare($query_tax);
            if(!$query_tax_res->execute()){
              $this->sql_error($query_tax_res); 
            }
        
        $array = $query_tax_res->fetch();
            $playing['contests']=$array['contests'];
            $playing['matches']=$array['matches'];
            $playing['series']=$array['series'];
            $playing['wins']=$array['wins'];
             $playing['wins_amt']=$array['wins_amt'];
           
        return $playing;

    }

    public function get_kabaddi_playing_history($user_id){
        
        $query_tax  = "SELECT IFNULL(COUNT(DISTINCT(match_contest_id)),0) AS contests, IFNULL(COUNT(DISTINCT(match_unique_id)),0) AS matches,sum(case win_amount when 0 then 0  ELSE 1 end)AS wins,(SELECT IFNULL(Count(DISTINCT(series_id)),0) FROM tbl_kabaddi_matches WHERE unique_id IN(SELECT DISTINCT(match_unique_id) FROM tbl_kabaddi_customer_contests WHERE customer_id='$user_id')) AS series FROM tbl_kabaddi_customer_contests WHERE customer_id='$user_id'";
        $query_tax_res  = $this->conn->prepare($query_tax);
            if(!$query_tax_res->execute()){
              $this->sql_error($query_tax_res); 
            }
        
        $array = $query_tax_res->fetch();

            $playing['contests']=$array['contests'];
            $playing['matches']=$array['matches'];
            $playing['series']=$array['series'];
            $playing['wins']=$array['wins'];

        
        return $playing;

    }

    public function get_soccer_playing_history($user_id){
        
        $query_tax  = "SELECT IFNULL(COUNT(DISTINCT(match_contest_id)),0) AS contests, IFNULL(COUNT(DISTINCT(match_unique_id)),0) AS matches,sum(case win_amount when 0 then 0  ELSE 1 end)AS wins,(SELECT IFNULL(Count(DISTINCT(series_id)),0) FROM tbl_soccer_matches WHERE unique_id IN(SELECT DISTINCT(match_unique_id) FROM tbl_soccer_customer_contests WHERE customer_id='$user_id')) AS series FROM tbl_soccer_customer_contests WHERE customer_id='$user_id'";
        $query_tax_res  = $this->conn->prepare($query_tax);
            if(!$query_tax_res->execute()){
              $this->sql_error($query_tax_res); 
            }
        
        $array = $query_tax_res->fetch();

            $playing['contests']=$array['contests'];
            $playing['matches']=$array['matches'];
            $playing['series']=$array['series'];
            $playing['wins']=$array['wins'];

        
        return $playing;

    }

    public function get_total_tax_percent(){

        $query_tax  = "SELECT name,value FROM tbl_taxes where status='A' AND is_deleted='N'";
        $query_tax_res  = $this->conn->prepare($query_tax);
        $query_tax_res->execute();

        $responce=array();

        $i=0;
        $total_tax=0;

        while($array = $query_tax_res->fetch()){
             $responce[$i]['name']=$array['name'];
             $responce[$i]['value']=$array['value'];
             $total_tax+=$array['value'];
             $i++;
        }

        $this->closeStatement($query_tax_res);

        $return_array=array();
        $return_array['total_tax']=$total_tax;
        $return_array['taxes']=$responce;
        return $return_array;





    }

    public function generate_dream_team($match_id,$match_unique_id){

        //die("hello");

        $teamSetting=$this->get_team_settings();
        $MAX_PLAYERS_PER_TEAM=$teamSetting['MAX_PLAYERS_PER_TEAM'];
        $MAX_CREDITS=$teamSetting['MAX_CREDITS'];
        $MIN_WICKETKEEPER=$teamSetting['MIN_WICKETKEEPER'];
        $MAX_WICKETKEEPER=$teamSetting['MAX_WICKETKEEPER'];
        $MIN_BATSMAN=$teamSetting['MIN_BATSMAN'];
        $MAX_BATSMAN=$teamSetting['MAX_BATSMAN'];
        $MIN_ALLROUNDER=$teamSetting['MIN_ALLROUNDER'];
        $MAX_ALLROUNDER=$teamSetting['MAX_ALLROUNDER'];
        $MIN_BOWLER=$teamSetting['MIN_BOWLER'];
        $MAX_BOWLER=$teamSetting['MAX_BOWLER'];


       

        
       

        $query = "SELECT tcmp.points,tcmp.credits,tcmp.playing_role as position,tcmp.team_id,tcp.uniqueid,tcp.name,tcm.team_1_id,tcm.team_2_id FROM tbl_cricket_match_players_stats tcmps  LEFT JOIN tbl_cricket_match_players tcmp ON (tcmps.player_unique_id=tcmp.player_unique_id AND tcmp.match_unique_id=tcmps.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcmps.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_cricket_matches tcm ON (tcmps.match_unique_id=tcm.unique_id) WHERE tcmps.match_unique_id = ? ORDER BY tcmp.points DESC";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);                            
        $query_res->execute();

        $wicketkeapers=array();
        $batsmans=array();
        $allrounders=array();
        $bowlers=array();
        
        $team1Id=NULL;
        $team2Id=NULL;

        //usort($remainPlayer, 'sortByPlayerPoints');

         $selectedBatsmanCount=0;
         $selectedBowlerCount=0;
         $selectedAllrounderCount=0;
         $selectedWicketkeapersCount=0;

         $team1SelectedPlayerCount=0;
         $team2SelectedPlayerCount=0;
         $dream_team_globle_credit_points=0;
         $dream_team_globle=array();
         $unselectedPlayers=array();
         $team1unselectedPlayers=array();
         $team2unselectedPlayers=array();
          
         while($player_data = $query_res->fetch(PDO::FETCH_ASSOC)){

                if(empty($team1Id) || empty($team2Id)){
                    $team1Id=$player_data['team_1_id'];
                    $team2Id=$player_data['team_2_id'];
                }
             
                $position=$player_data['position'];
                if(!empty($position)){
                    $position=strtolower($position);
                    if (strpos($position, 'wicketkeeper') !== false) {
                        $wicketkeapers[$player_data['uniqueid']]=$player_data;
                       
                        if($selectedWicketkeapersCount<$MAX_WICKETKEEPER){

                            if($player_data['team_id']==$team1Id){
                                if($team1SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team1SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedWicketkeapersCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                                
                            }else{
                                if($team2SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team2SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedWicketkeapersCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                            }

                        }else{
                            $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            if($player_data['team_id']==$team1Id){
                                $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }else{
                                $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }
                        }
                        continue;
                    }
                    if (strpos($position, 'batsman') !== false) {
                        $batsmans[$player_data['uniqueid']]=$player_data;
                         if($selectedBatsmanCount<$MAX_BATSMAN){

                            if($player_data['team_id']==$team1Id){
                                if($team1SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team1SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedBatsmanCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                                
                            }else{
                                if($team2SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team2SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedBatsmanCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                            }

                          
                        }else{
                            $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            if($player_data['team_id']==$team1Id){
                                $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }else{
                                $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }
                        }
                        continue;
                    }

                    if (strpos($position, 'allrounder') !== false) {
                        $allrounders[$player_data['uniqueid']]=$player_data;
                        if($selectedAllrounderCount<$MAX_ALLROUNDER){


                            if($player_data['team_id']==$team1Id){
                                if($team1SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team1SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedAllrounderCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                                
                            }else{
                                if($team2SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team2SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedAllrounderCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                            }

                        }else{
                            $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            if($player_data['team_id']==$team1Id){
                                $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }else{
                                $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }
                        }
                        continue;
                    }

                    if (strpos($position, 'bowler') !== false) {
                        $bowlers[$player_data['uniqueid']]=$player_data;
                        if($selectedBowlerCount<$MAX_BOWLER){

                            if($player_data['team_id']==$team1Id){
                                if($team1SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team1SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedBowlerCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                                
                            }else{
                                if($team2SelectedPlayerCount<$MAX_PLAYERS_PER_TEAM){
                                    $team2SelectedPlayerCount++;
                                    $dream_team_globle[$player_data['uniqueid']]=$player_data;
                                    $dream_team_globle_credit_points+=$player_data['credits'];
                                    $selectedBowlerCount++;
                                }else{
                                    $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                                    
                                }
                            }

                        }else{
                            $unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            if($player_data['team_id']==$team1Id){
                                $team1unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }else{
                                $team2unselectedPlayers[$player_data['uniqueid']]=$player_data;
                            }
                        }
                        continue;
                    }


                }
             
         }
        


         function sortByPointsAsyc($a, $b) {
                if ($a['points'] == $b['points']) {
                    return 0;
                }
                return ($a['points'] < $b['points']) ? -1 : 1;
          }

          uasort($dream_team_globle, 'sortByPointsAsyc');

          $sortaed_dream_team_globle=$dream_team_globle;

          //echo "team1SelectedPlayerCount=".$team1SelectedPlayerCount.PHP_EOL;
         // echo "team2SelectedPlayerCount=".$team2SelectedPlayerCount.PHP_EOL;
          //echo "dream_team_globle_credit_points=".$dream_team_globle_credit_points.PHP_EOL;

          
          while(count($sortaed_dream_team_globle)>11){
                foreach ($sortaed_dream_team_globle as $key => $value) {
                        unset($dream_team_globle[$key]);
                        $position=strtolower($value['position']);
                        $minCount=0;
                        if (strpos($position, 'wicketkeeper') !== false) {
                           $minCount=$MIN_WICKETKEEPER;
                        }else if (strpos($position, 'batsman') !== false) {
                           $minCount=$MIN_BATSMAN;
                        }else if (strpos($position, 'allrounder') !== false) {
                           $minCount=$MIN_ALLROUNDER;
                        }else if (strpos($position, 'bowler') !== false) {
                           $minCount=$MIN_BOWLER;
                        }
                        $isRight=$this->checkMinConditionForTeam($dream_team_globle,$value['position'],$minCount);
                        if($isRight){
                            unset($sortaed_dream_team_globle[$key]);
                            $unselectedPlayers[$key]=$value;
                            if($value['team_id']==$team1Id){
                                $team1SelectedPlayerCount--;
                                $team1unselectedPlayers[$key]=$value;
                            }else{
                                $team2SelectedPlayerCount--;
                                $team2unselectedPlayers[$key]=$value;
                            }
                            $dream_team_globle_credit_points-=$value['credits'];
                            break;
                        }else{
                            $dream_team_globle[$key]=$value;
                        }
                 }
          }

         /* echo "team1SelectedPlayerCount=".$team1SelectedPlayerCount.PHP_EOL;
          //echo "team2SelectedPlayerCount=".$team2SelectedPlayerCount.PHP_EOL;
         // echo "dream_team_globle_credit_points=".$dream_team_globle_credit_points.PHP_EOL;
          

          if($team1SelectedPlayerCount<=$MAX_PLAYERS_PER_TEAM && $team2SelectedPlayerCount<=$MAX_PLAYERS_PER_TEAM && $dream_team_globle_credit_points<=$MAX_CREDITS){

                echo "dream team ready <pre>";
                print_r($sortaed_dream_team_globle);
                die;

          }else{

            if($team1SelectedPlayerCount>$MAX_PLAYERS_PER_TEAM){

            }else if($team2SelectedPlayerCount>$MAX_PLAYERS_PER_TEAM){

            }


          }




          //echo "<pre>";
          //print_r($sortaed_dream_team_globle);
          
         // die;





       

         $output=array();
         $output['dream_wicketkeapers']=$dream_wicketkeapers;
         $output['dream_batsmans']=$dream_batsmans;
         $output['dream_allrounders']=$dream_allrounders;
         $output['dream_bowlers']=$dream_bowlers;
         $output['sortaed_dream_team_globle']=$sortaed_dream_team_globle;*/
         $dream_players=array();
         foreach($sortaed_dream_team_globle as $key => $value){
             
             $dream_players[]=$key;
             
             
             }

         return implode(',',$dream_players);


    }

    public function checkMinConditionForTeam($data,$playertype,$minCount){

        $playertype=strtolower($playertype);
         $existPlayerType=0;
        foreach ($data as $key => $value) {
               
                $position=strtolower($value['position']);
                if (strpos($position, $playertype) !== false) {
                    $existPlayerType++;
                }
        }
        return $existPlayerType>=$minCount;

    }


    public function generate_declare_match_result($match_id,$match_unique_id) {

        $time=time();

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $result=array();


        $tax_array=$this->get_total_tax_percent();


        $query_contest  = "SELECT tccm.is_beat_the_expert,tccm.team_id,tccm.entry_fee_multiplier, tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.total_team,tccm.confirm_win_contest_percentage,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N'";

        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        if($num_rows>0){

            while($array = $query_contest_res->fetch()){

                $CONFIRM_WIN_PER=0;
                if(!empty($array['confirm_win_contest_percentage'])){
                    $CONFIRM_WIN_PER=$array['confirm_win_contest_percentage'];
                }

                $is_beat_the_expert=$array['is_beat_the_expert'];
                $admin_team_id=$array['team_id'];
                $BeatExpertMultiplier=$array['entry_fee_multiplier'];
                if(empty($BeatExpertMultiplier)){
                    $BeatExpertMultiplier="1";
                }

                $match_contest_id=$array['id'];

                $confirm_win=$array['confirm_win'];
                $total_team=$array['total_team'];
                $joined_teams=$array['joined_teams'];
                $joinedTeamPer=($joined_teams/$total_team)*100;

                if($confirm_win=='N' && $joinedTeamPer<$CONFIRM_WIN_PER){
                    
                }else{

	                $winning_breakup=json_decode($array['contest_json'],true);
	                $limit=end($winning_breakup['per_max_p']);
	                if($is_beat_the_expert=='Y'){
	                    if($admin_team_id>0){
	                        $limit=$this->get_beat_the_expert_team_rank($match_unique_id,$match_contest_id,$admin_team_id);
	                        $limit--;
	                    }else{
	                        $limit=0;
	                    }
	                }

	                if($limit<0){
	                    $limit=0;
	                }


	                $team_query  = "SELECT id,new_rank,customer_id,entry_fees  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='$match_contest_id' AND new_rank<=$limit ORDER BY new_rank ASC";
	                $team_query_res  = $this->conn->prepare($team_query);

	                $team_query_res->execute();
	                $num_rows = $team_query_res->rowCount();

	                if($num_rows>0){
	                    $blank_array=array();
	                    $current_total_winners=0;

	                    while($array1 = $team_query_res->fetch()){

	                        $customer_id=$array1['customer_id'];
	                        $tccc_id=$array1['id'];

	                        if($is_beat_the_expert=='Y'){

	                            $update_amount=$array1['entry_fees']*$BeatExpertMultiplier;
	                            $tax_amount=0;
	                            $tax_percent=0;
	                            $tax_json_data="[]";


	                            if($update_amount>10000){
	                                if(!empty($tax_array['taxes'])){
	                                    $tax_json=array();
	                                    $j=0;
	                                    foreach($tax_array['taxes'] as $tax_value){
	                                        $tax_json[$j]['name']=$tax_value['name'];
	                                        $tax_json[$j]['value']=$tax_value['value'];
	                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
	                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
	                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

	                                        $tax_amount+=$calculatedTaxAmount;
	                                        $tax_percent+=$tax_value['value'];
	                                        $j++;
	                                    }
	                                    $tax_json_data=json_encode($tax_json);
	                                    $update_amount= $update_amount-$tax_amount;
	                                }
	                            }



	                            $c_ids=$array1['customer_id'];
	                            $main_key=$array1['new_rank'];

	                            $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
	                            if($tax_amount>0){
	                                $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
	                            }
	                            $transaction_id="WINWALL".time().$c_ids;
	                            $wallet_type="winning_wallet";

	                            $result[$match_contest_id][$c_ids][]=$update_amount. " rank ".$main_key;

	                        }
	                        else{
	                            $new_rank=$array1['new_rank'];
	                            $new_rank_for_amount=$new_rank;

	                            if($current_total_winners >= $limit){
	                                end($blank_array);
	                                $blank_array_last_rank=key($blank_array);
	                                if($new_rank_for_amount>$blank_array_last_rank){
	                                    break;
	                                }
	                            }

	                            if (array_key_exists($new_rank,$blank_array)){

	                                $new_rank_for_amount=$new_rank+count($blank_array[$new_rank]['amount']);

	                            }

	                            $i=0;
	                            $amount=0;
	                            foreach($winning_breakup['per_min_p'] as $winning_breakup_value){
	                                $min=$winning_breakup['per_min_p'][$i];
	                                $max=$winning_breakup['per_max_p'][$i];
	                                $amountt=$winning_breakup['per_price'][$i];

	                                if($new_rank_for_amount>=$min && $new_rank_for_amount<=$max ){

	                                    $amount=$amountt;
	                                    break;
	                                }
	                                $i++;
	                            }
	                            $blank_array[$new_rank]['amount'][]=$amount;
	                            $blank_array[$new_rank]['customer_ids'][]=$customer_id;

	                            $current_total_winners++;
	                        }
	                    }

	                    foreach($blank_array as $main_key=>$main_data){

	                            $count=count($main_data['amount']);
	                            $total_amount=array_sum($main_data['amount']);
	                            $update_amount=$total_amount/$count;
	                            $update_amount=round($update_amount,2);
	                            $tax_amount=0;
	                            $tax_percent=0;
	                            $tax_json_data="[]";

	                            if($update_amount>10000){
	                                if(!empty($tax_array['taxes'])){
	                                    $tax_json=array();
	                                    $j=0;
	                                    foreach($tax_array['taxes'] as $tax_value){
	                                        $tax_json[$j]['name']=$tax_value['name'];
	                                        $tax_json[$j]['value']=$tax_value['value'];
	                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
	                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
	                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

	                                        $tax_amount+=$calculatedTaxAmount;
	                                        $tax_percent+=$tax_value['value'];
	                                        $j++;
	                                    }
	                                    $tax_json_data=json_encode($tax_json);
	                                    $update_amount= $update_amount-$tax_amount;
	                                }
	                            }


	                            foreach($main_data['customer_ids'] as $c_ids){

	                                 $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
	                                 if($tax_amount>0){
	                                    $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
	                                 }
	                                 $transaction_id="WINWALL".time().$c_ids;
	                                 $wallet_type="winning_wallet";


	                                 $result[$match_contest_id][$c_ids][]=$update_amount. " rank ".$main_key;

	                            }
	                    }   


	                }else{
	                    $this->closeStatement($team_query_res);
	                }
	            }
            }
        }

        $this->closeStatement($query_contest_res);


        return $result;
    }

    public function declare_match_result_old($match_id,$match_unique_id) {

    	$match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        if(empty($match_detail) || $match_detail['match_progress']=='R'){
            return "INVALID_MATCH";
        }

        $time=time();

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $result="SUCCESS";
       // $result=$this->live_match_cron($match_unique_id);

       // if($result=="NO_RECORD"){

       //     return $result;

       // }

        //$match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);



        $tax_array=$this->get_total_tax_percent();




       
        $query  = "UPDATE tbl_cricket_matches set match_progress='R', points_updated_at=$time  WHERE unique_id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
           
             
        if(!$query_res->execute()) {
         $this->sql_error($query_res);
        }
        $this->closeStatement($query_res);

        $dream_players=$this->generate_dream_team($match_id,$match_unique_id);

        if(!empty($dream_players)){
            $query_dream  = "UPDATE tbl_cricket_match_players set dream_team_player='Y'  WHERE match_unique_id=? AND player_unique_id IN($dream_players)";
            $query_res_dream  = $this->conn->prepare($query_dream);
            $query_res_dream->bindParam(1,$match_unique_id);


            if(!$query_res_dream->execute()) {
             $this->sql_error($query_res_dream);
            }
            $this->closeStatement($query_res_dream);

        }

        //$settingData=$this->get_setting_data();
        /*$CONFIRM_WIN_PER=0;
        if(!empty($settingData['CONFIRM_WIN_CONTEST_PERCENTAGES'])){
            $CONFIRM_WIN_PER=$settingData['CONFIRM_WIN_CONTEST_PERCENTAGES'];
        }*/

        /*$query_contest  = "SELECT tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.total_team,tccm.confirm_win_contest_percentage,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm LEFT JOIN tbl_cricket_contests tcc ON(tccm.contest_id=tcc.id) LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N' AND tcc.is_deleted='N' AND tcc.status='A'";*/

        $query_contest  = "SELECT tccm.is_beat_the_expert,tccm.team_id,tccm.entry_fee_multiplier, tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.total_team,tccm.confirm_win_contest_percentage,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N'";

        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        if($num_rows>0){

            while($array = $query_contest_res->fetch()){

                $CONFIRM_WIN_PER=0;
                if(!empty($array['confirm_win_contest_percentage'])){
                    $CONFIRM_WIN_PER=$array['confirm_win_contest_percentage'];
                }

                $is_beat_the_expert=$array['is_beat_the_expert'];
                $admin_team_id=$array['team_id'];
                $BeatExpertMultiplier=$array['entry_fee_multiplier'];
                if(empty($BeatExpertMultiplier)){
                    $BeatExpertMultiplier="1";
                }

                $match_contest_id=$array['id'];

                $confirm_win=$array['confirm_win'];
                $total_team=$array['total_team'];
                $joined_teams=$array['joined_teams'];
                $joinedTeamPer=($joined_teams/$total_team)*100;

                if($confirm_win=='N' && $joinedTeamPer<$CONFIRM_WIN_PER){
                    $this->abodent_contest($match_contest_id);
                }else{

                $winning_breakup=json_decode($array['contest_json'],true);
                $limit=end($winning_breakup['per_max_p']);
                if($is_beat_the_expert=='Y'){
                    if($admin_team_id>0){
                        $limit=$this->get_beat_the_expert_team_rank($match_unique_id,$match_contest_id,$admin_team_id);
                        $limit--;
                    }else{
                        $limit=0;
                    }
                }

                if($limit<0){
                    $limit=0;
                }


                // $team_query  = "SELECT id,new_rank,customer_id  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='$match_contest_id' ORDER BY new_rank ASC LIMIT $limit";
                $team_query  = "SELECT id,new_rank,customer_id,entry_fees  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='$match_contest_id' AND new_rank<=$limit ORDER BY new_rank ASC";
                $team_query_res  = $this->conn->prepare($team_query);

                 $team_query_res->execute();
                 $num_rows = $team_query_res->rowCount();

                if($num_rows>0){
                    $blank_array=array();
                    $current_total_winners=0;

                    while($array1 = $team_query_res->fetch()){

                        $customer_id=$array1['customer_id'];
                        $tccc_id=$array1['id'];

                        if($is_beat_the_expert=='Y'){

                            $update_amount=$array1['entry_fees']*$BeatExpertMultiplier;
                            $tax_amount=0;
                            $tax_percent=0;
                            $tax_json_data="[]";


                            if($update_amount>10000){
                                if(!empty($tax_array['taxes'])){
                                    $tax_json=array();
                                    $j=0;
                                    foreach($tax_array['taxes'] as $tax_value){
                                        $tax_json[$j]['name']=$tax_value['name'];
                                        $tax_json[$j]['value']=$tax_value['value'];
                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

                                        $tax_amount+=$calculatedTaxAmount;
                                        $tax_percent+=$tax_value['value'];
                                        $j++;
                                    }
                                    $tax_json_data=json_encode($tax_json);
                                    $update_amount= $update_amount-$tax_amount;
                                }
                            }


                            $update_query  = "UPDATE tbl_cricket_customer_contests set win_amount='$update_amount',tax_amount='$tax_amount',tax_percent='$tax_percent',tax_json='$tax_json_data'  WHERE id='$tccc_id' AND match_contest_id='$match_contest_id'";
                            $update_query_res  = $this->conn->prepare($update_query);
                            $update_query_res->execute();

                            $this->closeStatement($update_query_res);

                            $c_ids=$array1['customer_id'];
                            $main_key=$array1['new_rank'];

                            $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                            if($tax_amount>0){
                                $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                            }
                            $transaction_id="WINWALL".time().$c_ids;
                            $wallet_type="winning_wallet";

                            $this->update_customer_wallet($c_ids,$match_contest_id,$wallet_type,$update_amount,"CREDIT","CUSTOMER_WIN_CONTEST",$transaction_id,$description,0,0,true,0,null,null);


                            $notification_data=array();
                            $notification_data['noti_type']='win_contest';
                            $alert_message = "Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount." in the ".$array['name'];
                            if($tax_amount>0){
                                $alert_message="Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount.", after deducting ".$tax_percent."% tax from your winning amount in the ".$array['name']." TNC apply.";
                            }
                            $this->send_notification_and_save($notification_data,$c_ids,$alert_message,true);
                              
                              
                              
                            $customer_detail=$this->getMiniUpdatedProfileData($c_ids); 


                            $dataaaaa=array();              

                            $dataaaaa['message']=$alert_message;
                            $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                            $email=$customer_detail['email'];
                            $this->sendTemplatesInMail('win_contest', trim($full_name), $email,$dataaaaa);

                        }
                        else{
                            $new_rank=$array1['new_rank'];
                            $new_rank_for_amount=$new_rank;

                            if($current_total_winners >= $limit){
                                end($blank_array);
                                $blank_array_last_rank=key($blank_array);
                                if($new_rank_for_amount>$blank_array_last_rank){
                                    break;
                                }
                            }

                            if (array_key_exists($new_rank,$blank_array)){

                                $new_rank_for_amount=$new_rank+count($blank_array[$new_rank]['amount']);

                            }

                            $i=0;
                            $amount=0;
                            foreach($winning_breakup['per_min_p'] as $winning_breakup_value){
                                $min=$winning_breakup['per_min_p'][$i];
                                $max=$winning_breakup['per_max_p'][$i];
                                $amountt=$winning_breakup['per_price'][$i];

                                if($new_rank_for_amount>=$min && $new_rank_for_amount<=$max ){

                                    $amount=$amountt;
                                    break;
                                }
                                $i++;
                            }
                            $blank_array[$new_rank]['amount'][]=$amount;
                            $blank_array[$new_rank]['customer_ids'][]=$customer_id;

                            $current_total_winners++;
                        }

                    }

                    foreach($blank_array as $main_key=>$main_data){

                            $count=count($main_data['amount']);
                            $total_amount=array_sum($main_data['amount']);
                            $update_amount=$total_amount/$count;
                            $update_amount=round($update_amount,2);
                            $tax_amount=0;
                            $tax_percent=0;
                            $tax_json_data="[]";

                            if($update_amount>10000){
                                if(!empty($tax_array['taxes'])){
                                    $tax_json=array();
                                    $j=0;
                                    foreach($tax_array['taxes'] as $tax_value){
                                        $tax_json[$j]['name']=$tax_value['name'];
                                        $tax_json[$j]['value']=$tax_value['value'];
                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

                                        $tax_amount+=$calculatedTaxAmount;
                                        $tax_percent+=$tax_value['value'];
                                        $j++;
                                    }
                                    $tax_json_data=json_encode($tax_json);
                                    $update_amount= $update_amount-$tax_amount;
                                }
                            }


                            $update_query  = "UPDATE tbl_cricket_customer_contests set win_amount='$update_amount',tax_amount='$tax_amount',tax_percent='$tax_percent',tax_json='$tax_json_data'  WHERE new_rank='$main_key' AND match_contest_id='$match_contest_id'";
                            $update_query_res  = $this->conn->prepare($update_query);
                            $update_query_res->execute();

                             $this->closeStatement($update_query_res);

                            foreach($main_data['customer_ids'] as $c_ids){

                                 $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                                 if($tax_amount>0){
                                    $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                                 }
                                 $transaction_id="WINWALL".time().$c_ids;
                                 $wallet_type="winning_wallet";

                                 $this->update_customer_wallet($c_ids,$match_contest_id,$wallet_type,$update_amount,"CREDIT","CUSTOMER_WIN_CONTEST",$transaction_id,$description,0,0,true,0,null,null);


                                  $notification_data=array();
                                  $notification_data['noti_type']='win_contest';
                                  $alert_message = "Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount." in the ".$array['name'];
                                  if($tax_amount>0){
                                    $alert_message="Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount.", after deducting ".$tax_percent."% tax from your winning amount in the ".$array['name']." TNC apply.";
                                  }
                                  $this->send_notification_and_save($notification_data,$c_ids,$alert_message,true);
                                  
                                  
                                  
                                  $customer_detail=$this->getMiniUpdatedProfileData($c_ids); 


                                    $dataaaaa=array();              

                                    $dataaaaa['message']=$alert_message;
                                    $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                                    $email=$customer_detail['email'];
                                    $this->sendTemplatesInMail('win_contest', trim($full_name), $email,$dataaaaa);
                            }
                    }   


                }else{
                    $this->closeStatement($team_query_res);
                }
            }
        }

        $this->closeStatement($query_contest_res);

        }else{
            $this->closeStatement($query_contest_res);
        }

        $this->distribute_referral_cash_bonus($match_unique_id);
        
        return $result;
    }
    
    function setMatchResult($match_id,$match_unique_id){
        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        if(empty($match_detail) || $match_detail['match_progress']=='R'){
            return "INVALID_MATCH";
        }
        $time=time();
        $query_contest  = "SELECT *,(SELECT count(*) FROM tbl_cricket_customer_contests as tccc where tccc.match_unique_id = '".$match_unique_id."' and tccc.match_contest_id=tccm.id) as joined_teams from tbl_cricket_contest_matches tccm INNER JOIN tbl_cricket_matches as tcm ON tcm.id = tccm.match_id WHERE tccm.match_id ='".$match_id."'";
        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        if($num_rows>0){
            while($array = $query_contest_res->fetch()){
                $joined_teams=$array['joined_teams'];
                $contest_entry_fees=$array['entry_fees'];
                    
                
                if($joined_teams>0 && $contest_entry_fees>0){
                    $is_compression_allow = $array['is_compression_allow'];
                    $confirm_win = $array['confirm_win'];
                    $total_team=$array['total_team'];
                    $joinedTeamPer=($joined_teams/$total_team)*100;
                    $total_price=$array['total_price'];
                    
                    $CONFIRM_WIN_PER=0;
                    if(!empty($array['confirm_win_contest_percentage'])){
                        $CONFIRM_WIN_PER=$array['confirm_win_contest_percentage'];
                    }
                    
                    $is_beat_the_expert=$array['is_beat_the_expert'];
                    $admin_team_id = $array['team_id'];
                    $match_contest_id = $array['id'];
                    $BeatExpertMultiplier=$array['entry_fee_multiplier'];
                    
                    if(empty($BeatExpertMultiplier)){
                        $BeatExpertMultiplier="1";
                    }
                    
                    if($is_compression_allow=='N' && $confirm_win=='N' && $joinedTeamPer<$CONFIRM_WIN_PER){
                        //$this->abodent_contest($match_contest_id);
                    }else{
    
                    $winning_breakup=json_decode($array['contest_json'],true);
                    
                    if($is_compression_allow=="Y"){
    
                        $need_earning=$total_team*$contest_entry_fees;
                        $prozepool_per=0;
                        if($need_earning>0){
                        	$prozepool_per=($total_price/$need_earning);
                        }
                        
                        $prozepool_per=$this->format_number($prozepool_per);
                        $total_distribute=$joined_teams*$contest_entry_fees;
                        //$updated_prize_pool=$total_distribute*$prozepool_per;
                        $updated_prize_pool=$total_distribute*($CONFIRM_WIN_PER/100);
                        
                        //echo $joined_teams."<br>".$contest_entry_fees."<br>";
                        //echo $updated_prize_pool."<br>";
                        
                        $updated_prize_pool=$this->format_number($updated_prize_pool);
                        $new_winning_breakup=array();
                        
                        $p=0;
                        foreach($winning_breakup['per_min_p'] as $winning_breakup_value){
                                    $min=$winning_breakup['per_min_p'][$p];
                                    $max=$winning_breakup['per_max_p'][$p];
                                    $amountt=$winning_breakup['per_price'][$p];
                                    $amountt=(($max-$min)+1)*$amountt;
    
                                    $amountt_per=0;
    			                    if($total_price>0){
    			                    	$amountt_per=($amountt/$total_price);
    			                    }
                                    
                                    $amountt_per=$this->format_number($amountt_per);
    
                                   
                                    $new_amountt=$updated_prize_pool*$amountt_per;                                
                                    $new_amountt=$new_amountt/(($max-$min)+1);
                                    $new_amountt=$this->format_number($new_amountt);
    
                                    $new_winning_breakup['per_min_p'][]=$min;
                                    $new_winning_breakup['per_max_p'][]=$max;
                                    $new_winning_breakup['per_price'][]=$new_amountt;
    
                                    $p++;
                        }
    
                        $winning_breakup=$new_winning_breakup;
    
                    }
                  /*  echo "<pre>";
                    print_r($winning_breakup);
                    echo "<br>";*/
                    
                    $limit=end($winning_breakup['per_max_p']);
                    if($is_beat_the_expert=='Y'){
                        if($admin_team_id>0){
                            $limit=$this->get_beat_the_expert_team_rank($match_unique_id,$match_contest_id,$admin_team_id);
                            $limit--;
                        }else{
                            $limit=0;
                        }
                    }
    
                    if($limit<0){
                        $limit=0;
                    }
    
                    $team_query  = "SELECT id,new_rank,customer_id,entry_fees  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='".$match_contest_id."' AND new_rank<=$limit ORDER BY new_rank ASC";
                    
               ////     echo $team_query."<br>";
                    $team_query_res  = $this->conn->prepare($team_query);
                    $team_query_res->execute();
                    $num_rows = $team_query_res->rowCount();
              //////      echo $num_rows."<br>";
                }
            }
        }
        die();
        
    }
    
    }
    
    public function declare_match_result($match_id,$match_unique_id) {

        $match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);
        if(empty($match_detail) || $match_detail['match_progress']=='R'){
            return "INVALID_MATCH";
        }

        $time=time();

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $result="SUCCESS";
       // $result=$this->live_match_cron($match_unique_id);

       // if($result=="NO_RECORD"){

       //     return $result;

       // }

        //$match_detail=$this->get_match_progress_by_match_unique_id($match_unique_id);



        $tax_array=$this->get_total_tax_percent();




       
        $query  = "UPDATE tbl_cricket_matches set match_progress='R', points_updated_at=$time  WHERE unique_id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);
           
             
        if(!$query_res->execute()) {
         $this->sql_error($query_res);
        }
        $this->closeStatement($query_res);

        $dream_players=$this->generate_dream_team($match_id,$match_unique_id);

        if(!empty($dream_players)){
            $query_dream  = "UPDATE tbl_cricket_match_players set dream_team_player='Y'  WHERE match_unique_id=? AND player_unique_id IN($dream_players)";
            $query_res_dream  = $this->conn->prepare($query_dream);
            $query_res_dream->bindParam(1,$match_unique_id);


            if(!$query_res_dream->execute()) {
             $this->sql_error($query_res_dream);
            }
            $this->closeStatement($query_res_dream);

        }

        $query_contest  = "SELECT tccm.is_beat_the_expert,tccm.team_id,tccm.entry_fee_multiplier, tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.is_compression_allow,tccm.total_team,tccm.confirm_win_contest_percentage,tccm.total_price,tccm.entry_fees,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N'";

        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        if($num_rows>0){

            while($array = $query_contest_res->fetch()){

                $CONFIRM_WIN_PER=0;
                if(!empty($array['confirm_win_contest_percentage'])){
                    $CONFIRM_WIN_PER=$array['confirm_win_contest_percentage'];
                }

                $is_beat_the_expert=$array['is_beat_the_expert'];
                $admin_team_id=$array['team_id'];
                $BeatExpertMultiplier=$array['entry_fee_multiplier'];
                if(empty($BeatExpertMultiplier)){
                    $BeatExpertMultiplier="1";
                }

                $match_contest_id=$array['id'];

                $confirm_win=$array['confirm_win'];
                $is_compression_allow=$array['is_compression_allow'];
                $total_team=$array['total_team'];
                $total_price=$array['total_price'];
                $contest_entry_fees=$array['entry_fees'];
                $joined_teams=$array['joined_teams'];
                $joinedTeamPer=($joined_teams/$total_team)*100;

                if($is_compression_allow=='N' && $confirm_win=='N' && $joinedTeamPer<$CONFIRM_WIN_PER){
                    $this->abodent_contest($match_contest_id);
                }else{

                $winning_breakup=json_decode($array['contest_json'],true);

                if($is_compression_allow=="Y"){

                    $need_earning=$total_team*$contest_entry_fees;
                    $prozepool_per=0;
                    if($need_earning>0){
                    	$prozepool_per=($total_price/$need_earning);
                    }
                    
                    $prozepool_per=$this->format_number($prozepool_per);
                    $total_distribute=$joined_teams*$contest_entry_fees;
                    $updated_prize_pool=$total_distribute*$prozepool_per;
                    $updated_prize_pool=$this->format_number($updated_prize_pool);
                    $new_winning_breakup=array();
                    
                    $p=0;
                    foreach($winning_breakup['per_min_p'] as $winning_breakup_value){
                                $min=$winning_breakup['per_min_p'][$p];
                                $max=$winning_breakup['per_max_p'][$p];
                                $amountt=$winning_breakup['per_price'][$p];
                                $amountt=(($max-$min)+1)*$amountt;


                                $amountt_per=0;
			                    if($total_price>0){
			                    	$amountt_per=($amountt/$total_price);
			                    }
                                
                                $amountt_per=$this->format_number($amountt_per);

                               
                                $new_amountt=$updated_prize_pool*$amountt_per;                                
                                $new_amountt=$new_amountt/(($max-$min)+1);
                                $new_amountt=$this->format_number($new_amountt);

                                $new_winning_breakup['per_min_p'][]=$min;
                                $new_winning_breakup['per_max_p'][]=$max;
                                $new_winning_breakup['per_price'][]=$new_amountt;

                                $p++;
                    }

                    $winning_breakup=$new_winning_breakup;

                }

                $limit=end($winning_breakup['per_max_p']);
                if($is_beat_the_expert=='Y'){
                    if($admin_team_id>0){
                        $limit=$this->get_beat_the_expert_team_rank($match_unique_id,$match_contest_id,$admin_team_id);
                        $limit--;
                    }else{
                        $limit=0;
                    }
                }

                if($limit<0){
                    $limit=0;
                }


                // $team_query  = "SELECT id,new_rank,customer_id  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='$match_contest_id' ORDER BY new_rank ASC LIMIT $limit";
                $team_query  = "SELECT id,new_rank,customer_id,entry_fees  FROM tbl_cricket_customer_contests  WHERE match_unique_id='$match_unique_id' AND match_contest_id='$match_contest_id' AND new_rank<=$limit ORDER BY new_rank ASC";
                $team_query_res  = $this->conn->prepare($team_query);

                 $team_query_res->execute();
                 $num_rows = $team_query_res->rowCount();

                if($num_rows>0){
                    $blank_array=array();
                    $current_total_winners=0;

                    while($array1 = $team_query_res->fetch()){

                        $customer_id=$array1['customer_id'];
                        $tccc_id=$array1['id'];

                        if($is_beat_the_expert=='Y'){

                            $update_amount=$array1['entry_fees']*$BeatExpertMultiplier;
                            $tax_amount=0;
                            $tax_percent=0;
                            $tax_json_data="[]";


                            if($update_amount>10000){
                                if(!empty($tax_array['taxes'])){
                                    $tax_json=array();
                                    $j=0;
                                    foreach($tax_array['taxes'] as $tax_value){
                                        $tax_json[$j]['name']=$tax_value['name'];
                                        $tax_json[$j]['value']=$tax_value['value'];
                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

                                        $tax_amount+=$calculatedTaxAmount;
                                        $tax_percent+=$tax_value['value'];
                                        $j++;
                                    }
                                    $tax_json_data=json_encode($tax_json);
                                    $update_amount= $update_amount-$tax_amount;
                                }
                            }


                            $update_query  = "UPDATE tbl_cricket_customer_contests set win_amount='$update_amount',tax_amount='$tax_amount',tax_percent='$tax_percent',tax_json='$tax_json_data'  WHERE id='$tccc_id' AND match_contest_id='$match_contest_id'";
                            $update_query_res  = $this->conn->prepare($update_query);
                            $update_query_res->execute();

                            $this->closeStatement($update_query_res);

                            $c_ids=$array1['customer_id'];
                            $main_key=$array1['new_rank'];

                            $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                            if($tax_amount>0){
                                $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                            }
                            $transaction_id="WINWALL".time().$c_ids;
                            $wallet_type="winning_wallet";

                            $this->update_customer_wallet($c_ids,$match_contest_id,$wallet_type,$update_amount,"CREDIT","CUSTOMER_WIN_CONTEST",$transaction_id,$description,0,0,true,0,null,null);


                            $notification_data=array();
                            $notification_data['noti_type']='win_contest';
                            $alert_message = "Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount." in the ".$array['name'];
                            if($tax_amount>0){
                                $alert_message="Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount.", after deducting ".$tax_percent."% tax from your winning amount in the ".$array['name']." TNC apply.";
                            }
                            $this->send_notification_and_save($notification_data,$c_ids,$alert_message,true);
                              
                            $customer_detail=$this->getMiniUpdatedProfileData($c_ids); 


                            $dataaaaa=array();              

                            $dataaaaa['message']=$alert_message;
                            $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                            $email=$customer_detail['email'];
                            $this->sendTemplatesInMail('win_contest', trim($full_name), $email,$dataaaaa);

                        }
                        else{
                            $new_rank=$array1['new_rank'];
                            $new_rank_for_amount=$new_rank;

                            if($current_total_winners >= $limit){
                                end($blank_array);
                                $blank_array_last_rank=key($blank_array);
                                if($new_rank_for_amount>$blank_array_last_rank){
                                    break;
                                }
                            }

                            if (array_key_exists($new_rank,$blank_array)){

                                $new_rank_for_amount=$new_rank+count($blank_array[$new_rank]['amount']);

                            }

                            $i=0;
                            $amount=0;
                            foreach($winning_breakup['per_min_p'] as $winning_breakup_value){
                                $min=$winning_breakup['per_min_p'][$i];
                                $max=$winning_breakup['per_max_p'][$i];
                                $amountt=$winning_breakup['per_price'][$i];

                                if($new_rank_for_amount>=$min && $new_rank_for_amount<=$max ){

                                    $amount=$amountt;
                                    break;
                                }
                                $i++;
                            }
                            $blank_array[$new_rank]['amount'][]=$amount;
                            $blank_array[$new_rank]['customer_ids'][]=$customer_id;

                            $current_total_winners++;
                        }

                    }
                    foreach($blank_array as $main_key=>$main_data){

                            $count=count($main_data['amount']);
                            $total_amount=array_sum($main_data['amount']);
                            $update_amount=$total_amount/$count;
                            $update_amount=round($update_amount,2);
                            $tax_amount=0;
                            $tax_percent=0;
                            $tax_json_data="[]";
                            if($update_amount>10000){
                                if(!empty($tax_array['taxes'])){
                                    $tax_json=array();
                                    $j=0;
                                    foreach($tax_array['taxes'] as $tax_value){
                                        $tax_json[$j]['name']=$tax_value['name'];
                                        $tax_json[$j]['value']=$tax_value['value'];
                                        $calculatedTaxAmount=($tax_value['value'] / 100) * $update_amount;
                                        $calculatedTaxAmount=round($calculatedTaxAmount,2);
                                        $tax_json[$j]['amount']=$calculatedTaxAmount;

                                        $tax_amount+=$calculatedTaxAmount;
                                        $tax_percent+=$tax_value['value'];
                                        $j++;
                                    }
                                    $tax_json_data=json_encode($tax_json);
                                    $update_amount= $update_amount-$tax_amount;
                                }
                            }


                            $update_query  = "UPDATE tbl_cricket_customer_contests set win_amount='$update_amount',tax_amount='$tax_amount',tax_percent='$tax_percent',tax_json='$tax_json_data'  WHERE new_rank='$main_key' AND match_contest_id='$match_contest_id'";
                            $update_query_res  = $this->conn->prepare($update_query);
                            $update_query_res->execute();

                            $this->closeStatement($update_query_res);
                             
                            //send Notification
                        foreach($main_data['customer_ids'] as $key=>$c_ids){
                                 $description=$c_ids." Win amount ".$update_amount." on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                                 if($tax_amount>0){
                                    $description=$c_ids." Win amount ".$update_amount." after deducting ".$tax_percent."% tax on match_contest_id ".$match_contest_id." With Rank ".$main_key;
                                 }
                                 $transaction_id="WINWALL".time().$c_ids;
                                 $wallet_type="winning_wallet";

                                 $this->update_customer_wallet($c_ids,$match_contest_id,$wallet_type,$update_amount,"CREDIT","CUSTOMER_WIN_CONTEST",$transaction_id,$description,0,0,true,0,null,null);


                                  $notification_data=array();
                                  $notification_data['noti_type']='win_contest';
                                  $alert_message = "Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount." in the ".$array['name'];
                                  if($tax_amount>0){
                                    $alert_message="Congratulations! You have won ".CURRENCY_SYMBOL.$update_amount.", after deducting ".$tax_percent."% tax from your winning amount in the ".$array['name']." TNC apply.";
                                  }
                                $this->send_notification_and_save($notification_data,$c_ids,$alert_message,true);
                                  
                                  
                                  
                                  $customer_detail=$this->getMiniUpdatedProfileData($c_ids); 


                                    $dataaaaa=array();              

                                    $dataaaaa['message']=$alert_message;
                                    $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
                                    $email=$customer_detail['email'];
                                    $this->sendTemplatesInMail('win_contest', trim($full_name), $email,$dataaaaa);
                            }
                    }   
                }else{
                    $this->closeStatement($team_query_res);
                }
            }
        }

        $this->closeStatement($query_contest_res);

        }else{
            $this->closeStatement($query_contest_res);
        }

        $this->distribute_referral_cash_bonus($match_unique_id);

        return $result;
    }


    public function generate_match_leaderboard($match_unique_id) {

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");
       
        $match_select_query="SELECT id, series_id, unique_id FROM tbl_cricket_matches where";

        if($match_unique_id>0){
            $match_select_query.=" unique_id=?";
        }else{
             $match_select_query.=" is_leaderboard_created='N' AND match_progress='R'";
        }
        
        $match_select_query_res  = $this->conn->prepare($match_select_query);
        if($match_unique_id>0){
            $match_select_query_res->bindParam(1,$match_unique_id);
        }
        if(!$match_select_query_res->execute()) { 
             $this->sql_error($match_select_query_res);
        }  

        $output=array();
        $series_id=0;

        while($array = $match_select_query_res->fetch(PDO::FETCH_ASSOC)){

            $match_unique_id=$array['unique_id'];

            $output[$match_unique_id]['match_unique_id']=$match_unique_id;

            $match_id=$array['id'];
            $series_id=$array['series_id'];

            $query  = "UPDATE tbl_cricket_matches set is_leaderboard_created='Y' WHERE unique_id=?";
            $query_res  = $this->conn->prepare($query);
            $query_res->bindParam(1,$match_unique_id);          
                 
            if(!$query_res->execute()) {
             $this->sql_error($query_res);
            }
            $this->closeStatement($query_res);


            $select_customer_contest_query="SELECT * FROM tbl_cricket_customer_contests where match_unique_id=? AND match_contest_id NOT IN(SELECT id FROM tbl_cricket_contest_matches WHERE match_unique_id='$match_unique_id' AND is_abondant='Y') ORDER BY new_points DESC";
            
            $select_customer_contest_query_res  = $this->conn->prepare($select_customer_contest_query);
            $select_customer_contest_query_res->bindParam(1,$match_unique_id);
            if(!$select_customer_contest_query_res->execute()){ 
                 $this->sql_error($select_customer_contest_query_res);
            } 

            $new_rank=1;
            $customerIds=array();
            while($data_array = $select_customer_contest_query_res->fetch(PDO::FETCH_ASSOC)){

                $customer_id=$data_array['customer_id'];
                $customer_team_id=$data_array['customer_team_id'];
                $new_points=$data_array['new_points'];
                if(in_array($customer_id, $customerIds)){
                    continue;
                }

                $created=time();
                $insert_query = "INSERT INTO tbl_cricket_leaderboard_matches SET match_id=?, match_unique_id=?, customer_id=?, customer_team_id=?, new_rank=?, new_point=?, created_at=?, updated_at=? ON DUPLICATE KEY UPDATE customer_team_id=?, new_rank=?, new_point=?, updated_at=?";

                $insert_query_res  = $this->conn->prepare($insert_query);
                $insert_query_res->bindParam(1,$match_id);
                $insert_query_res->bindParam(2,$match_unique_id);
                $insert_query_res->bindParam(3,$customer_id);
                $insert_query_res->bindParam(4,$customer_team_id);
                $insert_query_res->bindParam(5,$new_rank);
                $insert_query_res->bindParam(6,$new_points);
                $insert_query_res->bindParam(7,$created);
                $insert_query_res->bindParam(8,$created);
                $insert_query_res->bindParam(9,$customer_team_id);
                $insert_query_res->bindParam(10,$new_rank);
                $insert_query_res->bindParam(11,$new_points);
                $insert_query_res->bindParam(12,$created);
                if(!$insert_query_res->execute()){
                    $this->sql_error($insert_query_res);
                }
                $customerIds[]=$customer_id;
                

                $output[$match_unique_id]['customers'][$new_rank-1]['customer_id']=$customer_id;
                $output[$match_unique_id]['customers'][$new_rank-1]['customer_team_id']=$customer_team_id;
                $output[$match_unique_id]['customers'][$new_rank-1]['new_rank']=$new_rank;
                $output[$match_unique_id]['customers'][$new_rank-1]['new_points']=$new_points;

                $new_rank++;
            }   

        } 

        $seriesOutput=$this->generate_series_leaderboard($series_id);

        $output['series']=$seriesOutput;

        return $output;  
    }


    public function generate_series_leaderboard($series_id) {


        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");
       
        $match_select_query="SELECT customer_id, SUM(new_point) as total_points  FROM tbl_cricket_leaderboard_matches where match_unique_id IN (SELECT unique_id from tbl_cricket_matches where series_id='$series_id') GROUP BY customer_id ORDER BY SUM(new_point) DESC";

        $match_select_query_res  = $this->conn->prepare($match_select_query);

        if(!$match_select_query_res->execute()) { 
             $this->sql_error($match_select_query_res);
        }  

        $new_rank=1;

        $output=array();
        $output[$series_id]['series_id']=$series_id;

        while($array = $match_select_query_res->fetch(PDO::FETCH_ASSOC)){

            $customer_id=$array['customer_id'];
            $total_points=$array['total_points'];

            $created=time();
            $insert_query = "INSERT INTO tbl_cricket_leaderboard_series SET series_id=?, customer_id=?, old_rank=?, new_rank=?, old_point=?, new_point=?, created_at=?, updated_at=? ON DUPLICATE KEY UPDATE old_rank=IF(old_rank=0,$new_rank,IF(new_rank!=$new_rank,new_rank,old_rank)),new_rank=$new_rank, old_point=IF(old_point=0,$total_points,IF(new_point!=$total_points,new_point,old_point)),new_point=$total_points, updated_at=?";


            


            $insert_query_res  = $this->conn->prepare($insert_query);
            $insert_query_res->bindParam(1,$series_id);
            $insert_query_res->bindParam(2,$customer_id);
            $insert_query_res->bindParam(3,$new_rank);
            $insert_query_res->bindParam(4,$new_rank);
            $insert_query_res->bindParam(5,$total_points);
            $insert_query_res->bindParam(6,$total_points);
            $insert_query_res->bindParam(7,$created);
            $insert_query_res->bindParam(8,$created);

            $insert_query_res->bindParam(9,$created);
            if(!$insert_query_res->execute()){
                $this->sql_error($insert_query_res);
            }

            $output[$series_id]['customers'][$new_rank-1]['customer_id']=$customer_id;
            $output[$series_id]['customers'][$new_rank-1]['new_rank']=$new_rank;
            $output[$series_id]['customers'][$new_rank-1]['new_point']=$total_points;

            $new_rank++;
        }

        return $output;
    }


    public function get_customer_recent_series_leaderboard($customer_id){

        $match_select_query="SELECT tcls.series_id,tcs.name, tcls.new_point, tcls.new_rank FROM tbl_cricket_leaderboard_series tcls LEFT JOIN tbl_cricket_series tcs ON(tcs.id=tcls.series_id) where tcls.customer_id=? ORDER BY tcls.updated_at desc limit 4";

        $match_select_query_res  = $this->conn->prepare($match_select_query);
        $match_select_query_res->bindParam(1,$customer_id);

        if(!$match_select_query_res->execute()) { 
             $this->sql_error($match_select_query_res);
        }  

        $output=array();
        
        while($array = $match_select_query_res->fetch(PDO::FETCH_ASSOC)){

            $data=array();
            $data['id']=$array['series_id'];
            $data['name']=$array['name'];
            $data['new_point']=$array['new_point'];
            $data['new_rank']=$array['new_rank'];

            $output[]=$data;
        }

        return $output;
    }


    public function distribute_referral_cash_bonus($match_unique_id){
        $cash_bonus_data=$this->get_refer_cashbonus();

        /*$query  = "SELECT tccc.customer_id,tccm.entry_fees,tc.used_referral_user_id,tc.used_refferal_amount,tccc.match_contest_id FROM tbl_cricket_customer_contests tccc LEFT JOIN tbl_cricket_contest_matches tccm ON (tccc.match_contest_id=tccm.id) LEFT JOIN  tbl_cricket_contests tcc ON(tccm.contest_id=tcc.id) LEFT JOIN tbl_customers tc ON (tccc.customer_id=tc.id)  WHERE match_unique_id='$match_unique_id'";*/

        $query  = "SELECT tccc.customer_id,tccm.entry_fees,tc.used_referral_user_id,tc.used_refferal_amount,tccc.match_contest_id FROM tbl_cricket_customer_contests tccc LEFT JOIN tbl_cricket_contest_matches tccm ON (tccc.match_contest_id=tccm.id)  LEFT JOIN tbl_customers tc ON (tccc.customer_id=tc.id)  WHERE tccc.match_unique_id='$match_unique_id' AND tccm.is_abondant='N'";
    
        $query_res  = $this->conn->prepare($query);
         if(!$query_res->execute()) {
         $this->sql_error($query_res);
        }

        while($array = $query_res->fetch(PDO::FETCH_ASSOC)){

            
            $row_customer_id=$array['customer_id'];
            $row_used_referral_user_id=$array['used_referral_user_id'];

            $alreadygivenQuery="SELECT IFNULL(sum(amount),0) as total_amount_given from tbl_customer_wallet_histories tcwh where tcwh.customer_id='$row_used_referral_user_id' AND tcwh.refrence_id='$row_customer_id' AND tcwh.type='CUSTOMER_RECEIVED_REFCB'";
         /////   echo $alreadygivenQuery."<br>";
            $alreadygivenQuery_res  = $this->conn->prepare($alreadygivenQuery);
            if(!$alreadygivenQuery_res->execute()) {
             $this->sql_error($alreadygivenQuery_res);
            }
            $alreadyGivenArray = $alreadygivenQuery_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($alreadygivenQuery_res);
/*echo "<pre>";
print_r($array);
echo "<br>";
print_r($alreadyGivenArray);
            echo "<br>";
die;*/
            if($array['used_referral_user_id']>0 && ($alreadyGivenArray['total_amount_given']<$array['used_refferal_amount'])){
                
                $amount=0;
                
                if($cash_bonus_data['CONTEST_BASED']=="N"){

                    $amount=$array['used_refferal_amount']-$alreadyGivenArray['total_amount_given'];

                }else{

                    $remaining_amount=$array['used_refferal_amount']-$alreadyGivenArray['total_amount_given'];
                    $percent_amount = ($cash_bonus_data['PERCENTAGE_OF_ENTRY_FEES'] / 100) * $array['entry_fees'];

                    if($percent_amount>$remaining_amount){
                        $amount=$remaining_amount;
                    }else{

                        $amount=$percent_amount;
                    }
                }
            
                  if($amount>0){

                     $amount=round($amount,2);

                     $transaction_id="REFCBWALL".time().$array['used_referral_user_id'].$array['customer_id'].$array['match_contest_id'];
                     $description=$array['used_referral_user_id']." Received Referral cash bonus amount ".$amount.".";
                     $this->update_customer_wallet($array['used_referral_user_id'],$array['match_contest_id'],"bonus_wallet",$amount,"CREDIT","CUSTOMER_RECEIVED_REFCB",$transaction_id,$description,0,0,false,0,$array['customer_id'],null);



                      $notification_data=array();
                      $notification_data['noti_type']='referral_cash_bonus';
                      $alert_message = "Woohoo! Go for glory with your ".CURRENCY_SYMBOL.$amount." Cash Bonus.";
                      $this->send_notification_and_save($notification_data,$array['used_referral_user_id'],$alert_message,true);





                  }


            }



           

        }

        $this->closeStatement($query_res);


    }


    public function abondant_live_match_contest_cron($match_unique_id) {



        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $result="SUCCESS";

        $query_matches  = "SELECT tcm.id, tcm.unique_id FROM tbl_cricket_matches tcm  WHERE tcm.is_contest_abondant_complete='N' AND tcm.match_progress='L' AND tcm.is_deleted='N' AND tcm.status='A'";
        if($match_unique_id>0){
            $query_matches.=" AND tcm.unique_id=".$match_unique_id;
        }
        $query_matches.=" LIMIT 1";
        $query_matches_res  = $this->conn->prepare($query_matches);
        $query_matches_res->execute();
        $num_rows = $query_matches_res->rowCount();
        if($num_rows==0){
            $this->closeStatement($query_matches_res);
            return "NO MATCH FOUND FOR CONTEST CANCEL";
        }

        $match_array = $query_matches_res->fetch();

        $this->closeStatement($query_matches_res);

        $match_unique_id=$match_array['unique_id'];
        $match_id=$match_array['id'];


        $query  = "UPDATE tbl_cricket_matches set is_contest_abondant_complete='Y'  WHERE unique_id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);


        if(!$query_res->execute()) {
            $this->sql_error($query_res);
        }
        $this->closeStatement($query_res);



        //$settingData=$this->get_setting_data();
       /* $CONFIRM_WIN_PER=0;
        if(!empty($settingData['CONFIRM_WIN_CONTEST_PERCENTAGES'])){
            $CONFIRM_WIN_PER=$settingData['CONFIRM_WIN_CONTEST_PERCENTAGES'];
        }*/

      /*  $query_contest  = "SELECT tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.total_team,tccm.confirm_win_contest_percentage,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm LEFT JOIN tbl_cricket_contests tcc ON(tccm.contest_id=tcc.id) LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N' AND tcc.is_deleted='N' AND tcc.status='A'";*/

        $query_contest  = "SELECT tccm.id,tccm.contest_json,tcm.name,tccm.confirm_win,tccm.total_team,tccm.confirm_win_contest_percentage,(SELECT IFNULL(count(tccc_c.id),0) from tbl_cricket_customer_contests tccc_c where tccc_c.match_contest_id=tccm.id) as joined_teams FROM tbl_cricket_contest_matches tccm  LEFT JOIN tbl_cricket_matches tcm ON(tccm.match_id=tcm.id) WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N'";


        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();

        if($num_rows>0){

            while($array = $query_contest_res->fetch()){

                $CONFIRM_WIN_PER=0;
                if(!empty($array['confirm_win_contest_percentage'])){
                    $CONFIRM_WIN_PER=$array['confirm_win_contest_percentage'];
                }

                $match_contest_id=$array['id'];

                $confirm_win=$array['confirm_win'];
                $total_team=$array['total_team'];
                $joined_teams=$array['joined_teams'];
                $joinedTeamPer=($joined_teams/$total_team)*100;

                if($confirm_win=='N' && $joinedTeamPer<$CONFIRM_WIN_PER){
                    $this->abodent_contest($match_contest_id);
                }

            }

            $this->closeStatement($query_contest_res);
        }else{
            $this->closeStatement($query_contest_res);
        }



        return $result;
    }


    public function abodent_contest($match_contest_id) {


        $query  = "UPDATE tbl_cricket_contest_matches set is_abondant='Y'  WHERE id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_contest_id);


        if(!$query_res->execute()) {
            $this->sql_error($query_res);
        }
        $this->closeStatement($query_res);

        $query_contest  = "SELECT * FROM `tbl_customer_wallet_histories` WHERE match_contest_id='$match_contest_id' and type='CUSTOMER_JOIN_CONTEST' AND sport_id=0";
        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        $output=array();
        if($num_rows>0){
            $time=time();
            $refundDataArray=array();
            while($array = $query_contest_res->fetch(PDO::FETCH_ASSOC)){

                $wallet_types=unserialize(WALLET_TYPE);
                $wallet_type=array_search($array['wallet_type'],$wallet_types);


                $description=$array['customer_id']." Abodent contest refund ".$array['amount']." on match_contest_id ".$array['match_contest_id'];
                $transaction_id="RABCWALL".$time.$array['customer_id']."_".$array['match_contest_id']."_".$array['team_id'];

                if (array_key_exists($transaction_id,$refundDataArray)){
                   $refundDataArray[$transaction_id]['amount']=$refundDataArray[$transaction_id]['amount']+$array['amount'];
                }else{
                    $refundDataArray[$transaction_id]=array('customer_id'=>$array['customer_id'], 'match_contest_id'=>$array['match_contest_id'], 'team_id'=>$array['team_id'], 'amount'=>$array['amount']);
                }


                $this->update_customer_wallet($array['customer_id'],$array['match_contest_id'],$wallet_type,$array['amount'],"CREDIT","CUSTOMER_REFUND_ABCONTEST",$transaction_id,$description,0,0,false,$array['team_id'],null,null);

               $output[]=$array;


            }
            $this->closeStatement($query_contest_res);
            $this->update_contest_refund_amount($refundDataArray,0);

        }else{
             $this->closeStatement($query_contest_res);
        }

        return $output;
    }


    public function abodent_match($match_id,$match_unique_id) {

        //$result=$this->live_match_cron($match_unique_id);

        //if($result=="NO_RECORD"){

        //    return $result;

       // }


        $time=time();

        $query  = "UPDATE tbl_cricket_matches set match_progress='AB', points_updated_at=$time  WHERE unique_id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$match_unique_id);


        if(!$query_res->execute()) {
         $this->sql_error($query_res);
        }

        $this->closeStatement($query_res);


        $query_contest  = "SELECT tcwh.*,tcm.name as match_name FROM `tbl_customer_wallet_histories` tcwh LEFT JOIN tbl_cricket_matches tcm ON(tcm.id='$match_id') WHERE FIND_IN_SET (`match_contest_id`,(SELECT GROUP_CONCAT(tccm.id) FROM tbl_cricket_contest_matches tccm WHERE tccm.match_id='$match_id' AND tccm.is_deleted='N' AND tccm.status='A' AND tccm.is_abondant='N'))  AND sport_id=0 AND type='CUSTOMER_JOIN_CONTEST'";
        $query_contest_res  = $this->conn->prepare($query_contest);
        $query_contest_res->execute();
        $num_rows = $query_contest_res->rowCount();
        $output=array();
        if($num_rows>0){
            $time=time();
             $refundDataArray=array();
            while($array = $query_contest_res->fetch(PDO::FETCH_ASSOC)){

                $query_abondant  = "UPDATE tbl_cricket_contest_matches set is_abondant='Y'  WHERE id=?";
                $query_abondant_res  = $this->conn->prepare($query_abondant);
                $query_abondant_res->bindParam(1,$array['match_contest_id']);
                $query_abondant_res->execute();

                $this->closeStatement($query_abondant_res);



                $wallet_types=unserialize(WALLET_TYPE);
                $wallet_type=array_search($array['wallet_type'],$wallet_types);


                $description=$array['customer_id']." Abodent match refund ".$array['amount']." on match_contest_id ".$array['match_contest_id'];
                $transaction_id="RCWALL".$time.$array['customer_id']."_".$array['match_contest_id']."_".$array['team_id'];

                if (array_key_exists($transaction_id,$refundDataArray)){
                   $refundDataArray[$transaction_id]['amount']=$refundDataArray[$transaction_id]['amount']+$array['amount'];
                }else{
                    $refundDataArray[$transaction_id]=array('customer_id'=>$array['customer_id'], 'match_contest_id'=>$array['match_contest_id'], 'team_id'=>$array['team_id'], 'amount'=>$array['amount'], 'match_name'=>$array['match_name']);
                }


                 $this->update_customer_wallet($array['customer_id'],$array['match_contest_id'],$wallet_type,$array['amount'],"CREDIT","CUSTOMER_REFUND_CONTEST",$transaction_id,$description,0,0,false,$array['team_id'],null,null);

                $output[]=$array;


            }
            $this->closeStatement($query_contest_res);
            $this->update_contest_refund_amount($refundDataArray,1);

        }else{
            $this->closeStatement($query_contest_res);
        }

        return $output;
    }

    public function update_contest_refund_amount($refundDataArray,$match_or_contest_abondent){
        if(empty($refundDataArray))return;

        //print_r($refundDataArray);die;



         foreach($refundDataArray as $value){


             $customer_id=$value['customer_id'];
             $match_contest_id=$value['match_contest_id'];
             $customer_team_id=$value['team_id'];
             $update_amount=$value['amount'];
             $match_name=isset($value['match_name'])?$value['match_name']:'';

             $update_query  = "UPDATE tbl_cricket_customer_contests set refund_amount='$update_amount' WHERE customer_id='$customer_id' AND match_contest_id='$match_contest_id' AND customer_team_id='$customer_team_id'";
             $update_query_res  = $this->conn->prepare($update_query);
             $update_query_res->execute();
             $this->closeStatement($update_query_res);


             $notification_data=array();
             $notification_data['noti_type']=($match_or_contest_abondent==0)?'contest_ab':'match_ab';
             $alert_message = "You have received refund ".CURRENCY_SYMBOL.$update_amount." due to ".$match_name." abandoned.";
             if($match_or_contest_abondent==0){
                 $alert_message = "You have received refund ".CURRENCY_SYMBOL.$update_amount." due to contest cancelled.";
             }
             $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);

        }
    }

    public function add_pancard($user_id,$image,$number,$name,$dob,$state){
        
        $binary=base64_decode(str_replace('data:application/pdf;base64,','',str_replace('data:image/jpeg;base64,','',$image)));
        $file_name = time().'pan.jpg';
        $file = fopen(PANCARD_IMAGE_LARGE_PATH.$file_name, 'wb');
        fwrite($file, $binary);
        fclose($file);
        
        $image  = $file_name;
        
        $query_pan  = "SELECT pain_number FROM tbl_customer_paincard WHERE pain_number=? AND status !='R'";
        $query_pan_res  = $this->conn->prepare($query_pan);
        $query_pan_res->bindParam(1,$number);

        if(!$query_pan_res->execute()){
            $this->sql_error($query_pan_res);
        }
        $num_rows_pan = $query_pan_res->rowCount();
        if($num_rows_pan > 0){
            $this->closeStatement($query_pan_res);

            return "ALREADY_EXIST";
        }

        $created_by=0;
        $createdat=time();

        $query = "INSERT INTO tbl_customer_paincard SET customer_id=?, image=?,pain_number=?,name=?, dob=?, state=?,createdat=?,created_by=?";
        $query  = $this->conn->prepare($query);
        $query->bindParam(1,$user_id);
        $query->bindParam(2,$image);
        $query->bindParam(3,$number);
        $query->bindParam(4,$name);
        $query->bindParam(5,$dob);
        $query->bindParam(6,$state);
        $query->bindParam(7,$createdat);
        $query->bindParam(8,$created_by);                
        
        if(!$query->execute()){
             $this->closeStatement($query);
             return "UNABLE_TO_PROCEED";
        }


        $pain_id= $this->conn->lastInsertId();
        $this->closeStatement($query);

        $customer_update  = "UPDATE tbl_customers set paincard_id=?  WHERE id=?";
        $customer_update  = $this->conn->prepare($customer_update);
        $customer_update->bindParam(1,$pain_id);
        $customer_update->bindParam(2,$user_id);

        if(!$customer_update->execute()){
            $this->closeStatement($customer_update);
            return "UNABLE_TO_PROCEED";
        }  
        $this->closeStatement($customer_update);

        return $this->getUpdatedProfileData($user_id);

    }


    public function add_bankdetail($user_id,$account_number,$ifsc,$name,$image){


        $query_pan  = "SELECT id FROM tbl_customer_bankdetail WHERE account_number=? AND status !='R'";
        $query_pan_res  = $this->conn->prepare($query_pan);
        $query_pan_res->bindParam(1,$account_number);

        if(!$query_pan_res->execute()){
            $this->sql_error($query_pan_res);
        }
        $num_rows_pan = $query_pan_res->rowCount();
        if($num_rows_pan > 0){
            $this->closeStatement($query_pan_res);
            return "ALREADY_EXIST";
        }

        $created_by=0;
        $createdat=time();

        $query = "INSERT INTO tbl_customer_bankdetail SET customer_id=?, account_number=?,ifsc=?,name=?,createdat=?,created_by=?, image=?";
        $query  = $this->conn->prepare($query);
        $query->bindParam(1,$user_id);
        $query->bindParam(2,$account_number);
        $query->bindParam(3,$ifsc);
        $query->bindParam(4,$name);
        $query->bindParam(5,$createdat);
        $query->bindParam(6,$created_by); 
        $query->bindParam(7,$image); 
              
        if(!$query->execute()){
            $this->closeStatement($query);
             return "UNABLE_TO_PROCEED";
        }    
        
        $bankdetail_id= $this->conn->lastInsertId();
        $this->closeStatement($query);

        $customer_update  = "UPDATE tbl_customers set bankdetail_id=?  WHERE id=?";
        $customer_update  = $this->conn->prepare($customer_update);
        $customer_update->bindParam(1,$bankdetail_id);
        $customer_update->bindParam(2,$user_id);

        if(!$customer_update->execute()){
            $this->closeStatement($customer_update);
            return "UNABLE_TO_PROCEED";
        }       
        $this->closeStatement($customer_update);

        return $this->getUpdatedProfileData($user_id);        

    }

    public function wallet_recharge($userid, $amount,$paymentmethod,$referrer,$promocode) {

        if(!empty($promocode)){
            $promocodeDetail=$this->apply_promocode($userid,$promocode,$amount);
           
            if(!empty($promocodeDetail['message'])){
                return $promocodeDetail;
            }
        }

        $res['code']=0;
        $res['message']="";


        if($paymentmethod=="PAYTM"){

            $res['data']= $this->walletRechargePaytm($userid, $amount,$referrer,$promocode);

        }else if($paymentmethod=="RAZORPAY"){

            $res['data']= $this->walletRechargerazorpay($userid, $amount,$referrer,$promocode);
        }

        return $res;




    }

    public function includePaytmFiles() {
        $filesArr=get_required_files();
        $searchString=PAYTM_FILES_FIRST;
        if (!in_array($searchString, $filesArr)) {
            require PAYTM_FILES_FIRST;
        }
        $searchString1=PAYTM_FILES_SECOND;
        if (!in_array($searchString1, $filesArr)) {
            require PAYTM_FILES_SECOND;
        }
    }

    public function includerazorpayFiles() {
        $filesArr=get_required_files();
        $searchString=RAZORPAY_FILES;
        if (!in_array($searchString, $filesArr)) {
            require RAZORPAY_FILES;
        }
        
    }
    public function walletRechargerazorpay($user_id, $amount,$referrer,$promocode) {  


        $this->includerazorpayFiles();
        $ORDER_ID = $user_id."_customer_wallet_".time();

        $api = new Api(RAZORPAY_KEY, RAZORPAY_SECRET);

       
        $data = $api->order->create([
                  'receipt'         => $ORDER_ID,
                  // amount in the smallest currency unit
                  'amount'          => $amount*100,
                  'currency'        => DEFAULT_CURRENCY,
                  'payment_capture' =>  '1'
                ])->toArray();


        
        if($data['status'] !="created"){

            echo "Something went wrong please try again.";
            die;
        }

      
        $amount=$this->format_number($amount);
        $customer_detail=$this->getUpdatedProfileData($user_id);


       
        $paramList = array();

       
      

      

        // Create an array having all required parameters for creating checksum.
        $paramList["src"] = "https://checkout.razorpay.com/v1/checkout.js";
        $paramList["data-key"] = RAZORPAY_KEY;
        $paramList["data-amount"] = $amount*100;
        $paramList["data-currency"] = DEFAULT_CURRENCY;
        $paramList["data-buttontext"] = "PAY";
        $paramList["data-name"] = APP_NAME;
        $paramList["data-description"] = "Recharge wallet with amount ".$amount;
        $paramList["data-image"] = WEBSITE_URL."img/payment_logo.png";        
        $paramList["data-theme.color"] = "#3980D1";
        $paramList["data-order_id"] = $data['id'];
        $paramList["user_id"] = $user_id;
        $paramList["notes.return_data"] = $referrer;
        $paramList["notes.promocode"] = $promocode;
       

       
           $paramList["data-prefill.name"] ="";
         if(!empty($customer_detail['firstname'])){
             $paramList["data-prefill.name"] =$customer_detail['firstname']." ".$customer_detail['lastname'];
            
        }

           $paramList["data-prefill.email"] ="";
         if(!empty($customer_detail['email'])){
             $paramList["data-prefill.email"] =$customer_detail['email'];
            
        }
         $paramList["data-prefill.contact"] ="";
         if(!empty($customer_detail['phone'])){
              $paramList["data-prefill.contact"] =$customer_detail['phone'];
            
        }
        

       
        

        $html='<!DOCTYPE html>
                    <html>

                    <head>
                        <meta name="viewport" content="width=device-width">
                    </head>

                    <style>
                    .razorpay-payment-button{margin: 0 auto;
                    background: #3980D1;
                    border: 1px solid #3980D1;
                    color: #fff;
                    width: 165px;
                    padding: 15px;
                    font-size: 20px;
                    border-radius: 5px;
                    margin-top: 20%;
                }
                    form{

                        text-align:center;
                    }

                    h1{font-family: arial;

                    font-size: 25px;

                    margin: 10px 0px;

                    color: #3980D1;

                    border-bottom: 1px solid #ccc;

                    padding-bottom: 10px;}

                    h2{font-family: arial;

                    font-size: 35px;

                    margin:85px 0px 0px 0px;
                    color: #000;
                   }


                   h3{font-family: arial;

                    font-size: 35px;

                    margin: 10px 0px;

                    color: #13874B;
                   }

                    body{margin:0px;}


                    </style>
                    <body>




                    <form action="'.RAZORPAY_PAYMENT_GETWAY_RETURN_URL.'" method="POST">
                      <h1>Payment</h1>

                     <h2>Amount</h2>

                     <h3>₹ '.$amount.'</h3>

                    <script
                        src="'.$paramList["src"].'"
                        data-key="'.$paramList["data-key"].'"
                        data-amount="'.$paramList["data-amount"].'"
                        data-currency="'.$paramList["data-currency"].'"
                        data-buttontext="'.$paramList["data-buttontext"].'"
                        data-name="'.$paramList["data-name"].'"
                        data-description="'.$paramList["data-description"].'"
                        data-order_id="'.$paramList["data-order_id"].'"
                        data-image="'.$paramList["data-image"].'"
                        data-prefill.name="'.$paramList["data-prefill.name"].'"
                        data-prefill.email="'.$paramList["data-prefill.email"].'"
                        data-prefill.contact="'.$paramList["data-prefill.contact"].'"
                        data-theme.color="'.$paramList["data-theme.color"].'"
                        data-notes.return_data="'.$paramList["notes.return_data"].'"
                        data-notes.promocode="'.$paramList["notes.promocode"].'"
                    ></script>
                    <input type="hidden" value="'.$paramList["user_id"].'" name="user_id">
                    <input type="hidden" value="'.RAZORPAY_PAYMENT_GETWAY_RETURN_URL.'" name="noti_url" id="return_url">
                    </form>


                    </body>
                    </html>';

           
        return $html;        
        // exit;
    }

    public function walletRechargerazorpay_new($user_id, $amount,$promocode) {  


        $this->includerazorpayFiles();
        $ORDER_ID = $user_id."_customer_wallet_".time();

        $api = new Api(RAZORPAY_KEY, RAZORPAY_SECRET);

       
        $data = $api->order->create([
                  'receipt'         => $ORDER_ID,
                  // amount in the smallest currency unit
                  'amount'          => $amount*100,
                  'currency'        => DEFAULT_CURRENCY,
                  'payment_capture' =>  '1'
                ])->toArray();


        
        if($data['status'] !="created"){
            $res['code']=1;
            $res['message']="Something went wrong please try again.";
            return $res;
            die;
        }

      
        $amount=$this->format_number($amount);
        $customer_detail=$this->getUpdatedProfileData($user_id);


       
        $paramList = array();

       
      

      

        // Create an array having all required parameters for creating checksum.
        //$paramList["src"] = "https://checkout.razorpay.com/v1/checkout.js";
        $paramList["key"] = RAZORPAY_KEY;
        $paramList["amount"] = $amount*100;
        $paramList["currency"] = DEFAULT_CURRENCY;
        //$paramList["data-buttontext"] = "PAY";
        $paramList["name"] = APP_NAME;
        $paramList["description"] = "Recharge wallet with amount ".$amount;
        //$paramList["data-image"] = WEBSITE_URL."img/payment_logo.png";        
        //$paramList["data-theme.color"] = "#3980D1";
        $paramList["order_id"] = $data['id'];
        $paramList["user_id"] = $user_id;
        //$paramList["notes.return_data"] = $referrer;
        $paramList["notes"] = array("promocode"=>$promocode);
       

        $prefill=null;
           
         if(!empty($customer_detail['firstname'])){
             $prefill["name"] =$customer_detail['firstname']." ".$customer_detail['lastname'];
            
        }

           
         if(!empty($customer_detail['email'])){
             $prefill["email"] =$customer_detail['email'];
            
        }
         
         if(!empty($customer_detail['phone'])){
              $prefill["contact"] =$customer_detail['phone'];
            
        }

        $paramList["prefill"] =$prefill;
        

       
        

        /*$html='<!DOCTYPE html>
                    <html>

                    <head>
                        <meta name="viewport" content="width=device-width">
                    </head>

                    <style>
                    .razorpay-payment-button{margin: 0 auto;
                    background: #3980D1;
                    border: 1px solid #3980D1;
                    color: #fff;
                    width: 165px;
                    padding: 15px;
                    font-size: 20px;
                    border-radius: 5px;
                    margin-top: 20%;
                }
                    form{

                        text-align:center;
                    }

                    h1{font-family: arial;

                    font-size: 25px;

                    margin: 10px 0px;

                    color: #3980D1;

                    border-bottom: 1px solid #ccc;

                    padding-bottom: 10px;}

                    h2{font-family: arial;

                    font-size: 35px;

                    margin:85px 0px 0px 0px;
                    color: #000;
                   }


                   h3{font-family: arial;

                    font-size: 35px;

                    margin: 10px 0px;

                    color: #13874B;
                   }

                    body{margin:0px;}


                    </style>
                    <body>




                    <form action="'.RAZORPAY_PAYMENT_GETWAY_RETURN_URL.'" method="POST">
                      <h1>Payment</h1>

                     <h2>Amount</h2>

                     <h3>₹ '.$amount.'</h3>

                    <script
                        src="'.$paramList["src"].'"
                        data-key="'.$paramList["data-key"].'"
                        data-amount="'.$paramList["data-amount"].'"
                        data-currency="'.$paramList["data-currency"].'"
                        data-buttontext="'.$paramList["data-buttontext"].'"
                        data-name="'.$paramList["data-name"].'"
                        data-description="'.$paramList["data-description"].'"
                        data-order_id="'.$paramList["data-order_id"].'"
                        data-image="'.$paramList["data-image"].'"
                        data-prefill.name="'.$paramList["data-prefill.name"].'"
                        data-prefill.email="'.$paramList["data-prefill.email"].'"
                        data-prefill.contact="'.$paramList["data-prefill.contact"].'"
                        data-theme.color="'.$paramList["data-theme.color"].'"
                        data-notes.return_data="'.$paramList["notes.return_data"].'"
                        data-notes.promocode="'.$paramList["notes.promocode"].'"
                    ></script>
                    <input type="hidden" value="'.$paramList["user_id"].'" name="user_id">
                    <input type="hidden" value="'.RAZORPAY_PAYMENT_GETWAY_RETURN_URL.'" name="noti_url" id="return_url">
                    </form>


                    </body>
                    </html>';*/

           
        return $paramList;        
        // exit;
    }

    public function walletRechargePaytm($user_id, $amount,$referrer,$promocode) {
        
        $this->includePaytmFiles();
        $amount=$this->format_number($amount);
        $customer_detail=$this->getUpdatedProfileData($user_id);

        $checkSum = "";
        $paramList = array();

        $ORDER_ID = $user_id."_customer_wallet_".time();
        $CUST_ID = "C_".$user_id;
        $TXN_AMOUNT = $amount;
        $MERC_UNQ_REF=array('return_data'=>$referrer,'promocode'=>$promocode);
        $MERC_UNQ_REF=json_encode($MERC_UNQ_REF);

        // Create an array having all required parameters for creating checksum.
        $paramList["MID"] = PAYTM_MERCHANT_MID;
        $paramList["MERC_UNQ_REF"] = $MERC_UNQ_REF;
        $paramList["ORDER_ID"] = $ORDER_ID;
        $paramList["CUST_ID"] = $CUST_ID;
        $paramList["INDUSTRY_TYPE_ID"] = INDUSTRY_TYPE_ID;
        $paramList["CHANNEL_ID"] = CHANNEL_ID;
        $paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
        $paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;
        $paramList["CALLBACK_URL"] = PAYTM_PAYMENT_GETWAY_RETURN_URL;
        if(!empty($customer_detail['email'])){
             $paramList["EMAIL"] =$customer_detail['email'];
            
        }
         if(!empty($customer_detail['phone'])){
              $paramList["MOBILE_NO"] =$customer_detail['country_mobile_code'].$customer_detail['phone'];
            
        }
        $checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);
        $paramList["CHECKSUMHASH"] = $checkSum;

        $html='<html><head>
        <title>'.APP_NAME.'</title></head><body>
        <center><h1>Please do not refresh this page...</h1></center>';
        $html.='<form method="post" action="'.PAYTM_TXN_URL.'" name="f1">
        <table border="1">
            <tbody>';
                   
        foreach($paramList as $name => $value) {
            $html.="<input type='hidden' name='$name' value='$value'";
            if($name=="CALLBACK_URL"){
                $html.=" id='return_url'>";
            }else{
                $html.=" >";
            }
            
        }                    
       
        $html.='</tbody></table><script type="text/javascript">setTimeout(function() { document.f1.submit();}, 1000); </script></form></body></html>';
        return $html;        
        // exit;
    }


    


   

    public function razorpay_wallet_callback($dataa) {


       $this->includerazorpayFiles();
       // require RAZORPAY_FILES;
       

        $api = new Api(RAZORPAY_KEY, RAZORPAY_SECRET);
       
        $data = $api->payment->fetch($dataa['razorpay_payment_id'])->toArray();

        $data['referrer']="";
        $data['promocode']="";
        if(isset($data['notes']['promocode'])){
             //$data['referrer']=$data['notes']['return_data'];
             $data['promocode']=$data['notes']['promocode'];
        }
       
        $json_data=json_encode($data);
        
        $customer_id=$dataa['user_id'];

        $status=$data['status'];
        
        $data['STATUS']=$status;
        $data['RESPMSG']="Something went wrong.";
        
        if($status=="captured"){
            
            $data['STATUS']="TXN_SUCCESS";
            $data['TXNAMOUNT']=$data['amount']/100;
            
            
            
           
               
            $txnid=$data['id'];
            $amount=$data['amount']/100;
            $status=$data['status'];
            $refrence_id=$data['id'];

            $query  = "SELECT id FROM tbl_customer_wallet_histories WHERE customer_id=? AND refrence_id=?";
                    $query_res  = $this->conn->prepare($query);

                     $query_res->bindParam(1,$customer_id);
                     $query_res->bindParam(2,$refrence_id);
                    if(!$query_res->execute()){
                        $this->sql_error($query_res);
                    }
                    $num_rows = $query_res->rowCount();
                    if($num_rows == 0){

                        $description=$customer_id." Recharge his wallet.";
                        $transaction_id="WALL".time();
                        $wallet_type="deposit_wallet";
                        $match_contest_id=0;
                        $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,"CREDIT","CUSTOMER_WALLET_RECHARGE",$transaction_id,$description,0,0,true,0,$refrence_id,$json_data,"RAZORPAY",$data['promocode']);
                        $notification_data=array();
                        $notification_data['noti_type']='customer_deposit';
                        $alert_message = "Your deposit of ".CURRENCY_SYMBOL.$amount." successful.";
                        $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);
                    }
                      $wallet_data=$this->getUpdatedWalletData($customer_id);

                      $data['wallet']= $wallet_data['wallet'];
            
        }
        
        
        return $data;

    }

    public function paytm_wallet_callback($data) {

        $status=$data['STATUS'];
        $data['referrer']="";
        $data['promocode']="";
        if(isset($data['MERC_UNQ_REF'])){
            $MERC_UNQ_REF=json_decode($data['MERC_UNQ_REF'],true);
            $data['referrer']=$MERC_UNQ_REF['return_data'];
            $data['promocode']=$MERC_UNQ_REF['promocode'];
        }
    
        if($status=="TXN_SUCCESS"){
            
            
            $data=$this->paytm_status_api($data);
            $data['referrer']="";
            if(isset($data['MERC_UNQ_REF'])){
               $MERC_UNQ_REF=json_decode($data['MERC_UNQ_REF'],true);
                $data['referrer']=$MERC_UNQ_REF['return_data'];
                $data['promocode']=$MERC_UNQ_REF['promocode'];
            }
            
            $user_id=explode('_',$data['ORDERID']);
            $customer_id=$user_id[0];       
            $refrence_id=$data['TXNID'];
            $amount=$data['TXNAMOUNT'];
            $status=$data['STATUS'];
            if($status=="TXN_SUCCESS"){
                $json_data=json_encode($data);

                    $query  = "SELECT id FROM tbl_customer_wallet_histories WHERE customer_id=? AND refrence_id=?";
                    $query_res  = $this->conn->prepare($query);

                     $query_res->bindParam(1,$customer_id);
                     $query_res->bindParam(2,$refrence_id);
                    if(!$query_res->execute()){
                        $this->sql_error($query_res);
                    }
                    $num_rows = $query_res->rowCount();
                    if($num_rows == 0){

                        $description=$customer_id." Recharge his wallet.";
                        $transaction_id="WALL".time();
                        $wallet_type="deposit_wallet";
                        $match_contest_id=0;
                        $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,"CREDIT","CUSTOMER_WALLET_RECHARGE",$transaction_id,$description,0,0,true,0,$refrence_id,$json_data,"PAYTM",$data['promocode']);
                        $notification_data=array();
                        $notification_data['noti_type']='customer_deposit';
                        $alert_message = "Your deposit of ".CURRENCY_SYMBOL.$amount." successful.";
                        $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);
                    }
                      $wallet_data=$this->getUpdatedWalletData($customer_id);

                      $data['wallet']= $wallet_data['wallet']; 
            }
            
        }
        
        return $data;

    }

    public function paytm_status_api($data){
        
                $this->includePaytmFiles();             
                header("Pragma: no-cache");
                header("Cache-Control: no-cache");
                header("Expires: 0");
        
        
                $ORDER_ID = $data['ORDERID'];
                $requestParamList = array();
                $responseParamList = array();
                
                $requestParamList = array("MID" => PAYTM_MERCHANT_MID , "ORDERID" => $ORDER_ID);  
                
                $checkSum = getChecksumFromArray($requestParamList,PAYTM_MERCHANT_KEY);
               //$checkSum = $data['CHECKSUMHASH'];
                $requestParamList['CHECKSUMHASH'] = urlencode($checkSum);
                
                $data_string = "JsonData=".json_encode($requestParamList);
               //echo $data_string;
                
                $ch = curl_init();                    // initiate curl
                $url = PAYTM_STATUS_QUERY_URL; //Paytm server where you want to post data
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, true);  // tell curl you want to post something
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string); // define what you want to post
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the output in string format
                $headers = array();
                $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $output = curl_exec($ch); // execute
                $info = curl_getinfo($ch);
                
                //file_put_contents('paytm_txn_parm.txt', print_r($data_string, true),FILE_APPEND);
                //file_put_contents('paytm_txn_parm.txt', print_r($output, true),FILE_APPEND);
                
                $data = json_decode($output, true);
                
                return $data;
        
    }


   



    public function get_refer_earn($user_id){
        $output=array();
        $cash_bonus_data=$this->get_refer_cashbonus();
        $APKURL=APK_DOWNLOAD_URL;

        $output['title']="Get Rs.100 free for every friend you refer";
        $output['subtitle']="Earn cash bonus is easy, simple and fun just share code";
        $output['image']=empty($cash_bonus_data['REFERRAL_EARN_IMAGE'])?'':REFER_EARN_IMAGE_LARGE_URL.$cash_bonus_data['REFERRAL_EARN_IMAGE'];

        $used_refferal_amount=$cash_bonus_data['REFERRER'];
        $applier_reffrel_amount=$cash_bonus_data['REGISTER_WITH_REFERRAL_CODE_(applier)'];

		$output['title']="Get Rs.".$used_refferal_amount." free for every\nfriends you refer";
		$output['share_content']="Think you can challenge me on Gully11? Tap ".APK_DOWNLOAD_URL." \nto download the app & use my invite code %s to get Cash Bonus of Rs.".$applier_reffrel_amount."! Let the game begin";

		$query = "SELECT count(id) as team_count, IFNULL(sum(used_refferal_amount),0) as team_earn, (SELECT IFNULL(sum(tcwh.amount),0) from tbl_customer_wallet_histories tcwh where tcwh.type='CUSTOMER_RECEIVED_REFCB' AND tcwh.customer_id=?) as total_received_amount, (SELECT IFNULL(sum(amount),0) from tbl_customer_wallet_histories where type='CUSTOMER_RECEIVED_REFCCB' AND customer_id=?) as team_earn_cash FROM tbl_customers WHERE used_referral_user_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);
        $query_res->bindParam(3, $user_id);
        $query_res->execute();
        $array = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);

        $output['team_count']=$array['team_count'];
        $output['team_earn']=$array['team_earn'];
        $output['team_earn_cash']=$array['team_earn_cash'];
        $output['total_received_amount']=$array['total_received_amount'];

        return $output;
    }

    public function get_refer_earn_detail($user_id){

        $referData=$this->get_refer_earn($user_id);

        $query = "SELECT firstname, lastname, team_name, image, external_image, used_refferal_amount,(SELECT IFNULL(sum(tcwh.amount),0) from tbl_customer_wallet_histories tcwh where tcwh.customer_id=? AND tcwh.type='CUSTOMER_RECEIVED_REFCB' AND tcwh.refrence_id=tc.id) as received_referral_amount FROM tbl_customers tc WHERE used_referral_user_id=?";
        /*$query = "SELECT firstname, lastname, team_name, image, external_image, used_refferal_amount,(SELECT IFNULL(sum(tcwh.amount),0) from tbl_customer_wallet_histories tcwh where tcwh.customer_id=? AND tcwh.refrence_id=tc.id) as received_referral_amount FROM tbl_customers tc WHERE used_referral_user_id=?";*/
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);
        if(!$query_res->execute()){
            $this->sql_error($query_res);
        }

        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];

        $user_refer_data=array();
        $total_received_amount=0;
        if ($query_res->rowCount() > 0) {
			  $i=0;
			  while($userData = $query_res->fetch(PDO::FETCH_ASSOC)){

				  $user_refer_data[$i]['firstname']=$userData['firstname'];
				  $user_refer_data[$i]['lastname']=$userData['lastname'];
				  $user_refer_data[$i]['team_name']=$userData['team_name'];
				  $user_refer_data[$i]['image']=!empty($userData['image']) ? CUSTOMER_IMAGE_THUMB_URL.$userData['image'] : $no_imag_url;

                  if(!empty($userData['external_image'])){
                    $user_refer_data[$i]['image']=$userData['external_image'];
                  }

				  $user_refer_data[$i]['used_refferal_amount']=$userData['used_refferal_amount'];
				  $user_refer_data[$i]['received_referral_amount']=$userData['received_referral_amount'];

				  $total_received_amount+=$userData['received_referral_amount'];
				  $i++;
			  }
              $this->closeStatement($query_res);
		}else{
            $this->closeStatement($query_res);
        }

		$referData['total_received_amount']=$total_received_amount;
		$output=array();
		$output['refer_data']=$referData;
		$output['user_refer_data']=$user_refer_data;
		return $output;
	}


    public function get_refer_earn_detail_cash($user_id){
        
        $query = "SELECT tbl_customer_wallet_histories.*, tc.id, tc.firstname, tc.lastname, tc.email, tc.team_name, tc.image, tg.name as sports_type, tgt.name as game_type, state.name as stateName, tccc.name as contest_category, tccm.entry_fees, tcm.name as match_name, tcm.match_date as match_date
FROM (`tbl_customer_wallet_histories`)
LEFT JOIN `tbl_customers` tc ON `tc`.`id` = `tbl_customer_wallet_histories`.`refrence_id`
LEFT JOIN `tbl_states` state ON `state`.`id` = `tc`.`state`
LEFT JOIN `tbl_games` tg ON `tg`.`id` = `tbl_customer_wallet_histories`.`sport_id`
LEFT JOIN `tbl_cricket_matches` tcm ON `tbl_customer_wallet_histories`.`match_unique_id` = `tcm`.`unique_id`
LEFT JOIN `tbl_game_types` tgt ON `tcm`.`game_type_id` = `tgt`.`id`
LEFT JOIN `tbl_cricket_contest_matches` tccm ON `tccm`.`id` = `tbl_customer_wallet_histories`.`match_contest_id`
LEFT JOIN `tbl_cricket_contest_categories` tccc ON `tccc`.`id` = `tccm`.`category_id`
WHERE `tbl_customer_wallet_histories`.`type` = 'CUSTOMER_RECEIVED_REFCCB' AND customer_id = ? ORDER BY `tbl_customer_wallet_histories`.`id` desc";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        if(!$query_res->execute()){
            $this->sql_error($query_res);
        }
        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);

        

        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];
        $user_refer_data=array();
        if ($query_res->rowCount() > 0) {
              $i=0;
              while($userData = $query_res->fetch(PDO::FETCH_ASSOC)){

                  $user_refer_data[$i]['firstname']=$userData['firstname'];
                  $user_refer_data[$i]['lastname']=$userData['lastname'];
                  $user_refer_data[$i]['team_name']=$userData['team_name'];
                  $user_refer_data[$i]['image']=!empty($userData['image']) ? CUSTOMER_IMAGE_THUMB_URL.$userData['image'] : $no_imag_url;

                  $user_refer_data[$i]['match_name']=$userData['match_name'];
                  $user_refer_data[$i]['match_date']=$userData['match_date'];
                  $user_refer_data[$i]['entry_fees']=$userData['entry_fees'];
                  $user_refer_data[$i]['commission']=$userData['commission'];
                  $user_refer_data[$i]['amount']=$userData['amount'];
                  $user_refer_data[$i]['contest_category']=$userData['contest_category'];

                  $i++;
              }
              $this->closeStatement($query_res);
        }else{
            $this->closeStatement($query_res);
        }

        $output=array();
        $output['user_refer_data']=$user_refer_data;
        return $output;
    }


    public function follow_unfollow_customer($user_id,$following_id,$type){

        if($type=="FOLLOW"){
            $created=time();
            $insert_query = "INSERT INTO tbl_follow SET follower_id=?, following_id=?, created=? ON DUPLICATE KEY UPDATE created=?";
            $insert_query_res  = $this->conn->prepare($insert_query);
            $insert_query_res->bindParam(1,$user_id);
            $insert_query_res->bindParam(2,$following_id);
            $insert_query_res->bindParam(3,$created);
            $insert_query_res->bindParam(4,$created);
            if(!$insert_query_res->execute()){
                $this->sql_error($insert_query_res);
                return "UNABLE_TO_PROCEED";
            }
            $this->update_customer_counts($user_id);
            $this->update_customer_counts($following_id);

        }else{

            $delete_query = "DELETE FROM tbl_follow where follower_id=? AND following_id=?";
            $delete_query_res  = $this->conn->prepare($delete_query);
            $delete_query_res->bindParam(1,$user_id);
            $delete_query_res->bindParam(2,$following_id);           
            if(!$delete_query_res->execute()){
                $this->sql_error($delete_query_res);
                return "UNABLE_TO_PROCEED";
            }

            $this->update_customer_counts($user_id);
            $this->update_customer_counts($following_id);

        }

        return $response = $this->get_customer_profile($user_id,$following_id);




    }

    public function update_customer_counts($user_id){
        $follower_count=0;
        $following_count=0;

        $query = "SELECT COUNT(id) AS follower_count  FROM tbl_follow  WHERE following_id='$user_id'";
        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $query_array = $query_res->fetch();
        if(!empty($query_array)){
        $follower_count=$query_array['follower_count'];
            
        }

        $query_f = "SELECT COUNT(id) AS following_count  FROM tbl_follow  WHERE follower_id='$user_id'";
        $query_res_f  = $this->conn->prepare($query_f);
        $query_res_f->execute();
        $query_array_f = $query_res_f->fetch();
        if(!empty($query_array_f)){
         $following_count=$query_array_f['following_count'];            
        }
       


        $update_last_login_query = "UPDATE tbl_customers SET follower_count=?, following_count=? WHERE id=?";
        $update_last_login  = $this->conn->prepare($update_last_login_query);
       
        $update_last_login->bindParam(1,$follower_count);
        $update_last_login->bindParam(2,$following_count);
        $update_last_login->bindParam(3,$user_id);        
        $update_last_login->execute();

        if(!$update_last_login->execute()){
                $this->sql_error($update_last_login);               
        }

        

    }

    public function update_customer_post_counts($user_id){
        $post_count=0;
        

        $query = "SELECT COUNT(id) AS post_count  FROM tbl_posts  WHERE user_id='$user_id'";
        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $query_array = $query_res->fetch();
        if(!empty($query_array)){
        $post_count=$query_array['post_count'];
            
        }

       
       


        $update_last_login_query = "UPDATE tbl_customers SET post_count=? WHERE id=?";
        $update_last_login  = $this->conn->prepare($update_last_login_query);
       
        $update_last_login->bindParam(1,$post_count);
        $update_last_login->bindParam(2,$user_id);        
        $update_last_login->execute();

        if(!$update_last_login->execute()){
                $this->sql_error($update_last_login);               
        }

        

    }


    public function get_customer_profile($self_user_id,$user_id) {

       $query = "SELECT (SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE follower_id='$self_user_id' AND following_id='$user_id') as is_follow,(SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE following_id='$self_user_id' AND follower_id='$user_id') as is_following,tc.team_name,tc.id,tc.follower_count,tc.following_count,tc.post_count, tc.firstname, tc.lastname, tc.email, tc.image, tc.external_image, tc.country_mobile_code, tc.phone,tc.dob, tc.addressline1, tc.addressline2, tc.pincode,tc.city as city_name, IFNULL(tbl_countries.id,0) as country_id, tbl_countries.name as country_name, IFNULL(tbl_states.id,0) as state_id , tbl_states.name as state_name FROM tbl_customers tc LEFT JOIN tbl_countries ON tbl_countries.id = tc.country LEFT JOIN tbl_states ON tbl_states.id=tc.state WHERE tc.id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $output = array();
        if ($query_res->execute()) {
            $profiledata = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);
            if(empty($profiledata)){
                return $output;
            }

            $output['id'] = $profiledata['id'];
            $output['team_name'] = $profiledata['team_name'];
            $output['is_follow'] = $profiledata['is_follow'];
            $output['is_following'] = $profiledata['is_following'];
            $output['firstname'] = $profiledata['firstname'];
            $output['lastname'] = $profiledata['lastname'];
            $output['email'] = $profiledata['email'];
            $output['country_mobile_code'] = $profiledata['country_mobile_code'];               
            $output['phone'] = $profiledata['phone']; 
            $output['follower_count'] = $profiledata['follower_count']; 
            $output['following_count'] = $profiledata['following_count']; 
            $output['post_count'] = $profiledata['post_count'];
            $output['playing_history'] = $this->get_playing_history($user_id);
            $output['series_leaderboard'] = $this->get_customer_recent_series_leaderboard($user_id);
            $output['image'] = !empty($profiledata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;

            if(!empty($profiledata['external_image'])){
                $output['image']=$profiledata['external_image'];
            }

            $output['country']=NULL;
            if(!empty($profiledata['country_id'])){
                $output['country'] = array('id'=>$profiledata['country_id'], 'name'=>$profiledata['country_name']);
            }
            
            $output['state']=NULL;
            if(!empty($profiledata['state_id'])){
                $output['state'] = array('id'=>$profiledata['state_id'], 'name'=>$profiledata['state_name']);
            }
            

            
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;
    }


    public function get_customers($self_user_id,$user_id,$type,$page_no) {

        if($type=="FL"){

            $query = "SELECT (SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE follower_id='$self_user_id' AND following_id=tf.follower_id ) as is_follow,(SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE following_id='$self_user_id' AND follower_id=tf.follower_id ) as is_following,tc.team_name,tc.id,tc.follower_count,tc.following_count,tc.post_count, tc.firstname, tc.lastname, tc.email, tc.image,tc.external_image, tc.country_mobile_code, tc.phone,tc.dob, tc.addressline1, tc.addressline2, tc.pincode,tc.city as city_name, IFNULL(tbl_countries.id,0) as country_id, tbl_countries.name as country_name, IFNULL(tbl_states.id,0) as state_id , tbl_states.name as state_name FROM tbl_follow tf LEFT JOIN tbl_customers tc ON (tc.id=tf.follower_id) LEFT JOIN tbl_countries ON tbl_countries.id = tc.country LEFT JOIN tbl_states ON tbl_states.id=tc.state WHERE tf.following_id=?";

        }else{


            $query = "SELECT (SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE follower_id='$self_user_id' AND following_id=tf.following_id ) as is_follow,(SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE following_id='$self_user_id' AND follower_id=tf.following_id ) as is_following,tc.team_name, tc.id,tc.follower_count,tc.following_count,tc.post_count, tc.firstname, tc.lastname, tc.email, tc.image,tc.external_image, tc.country_mobile_code, tc.phone,tc.dob, tc.addressline1, tc.addressline2, tc.pincode,tc.city as city_name, IFNULL(tbl_countries.id,0) as country_id, tbl_countries.name as country_name, IFNULL(tbl_states.id,0) as state_id , tbl_states.name as state_name FROM tbl_follow tf LEFT JOIN tbl_customers tc ON (tc.id=tf.following_id) LEFT JOIN tbl_countries ON tbl_countries.id = tc.country LEFT JOIN tbl_states ON tbl_states.id=tc.state WHERE tf.follower_id=?";


        }

        if($page_no>0){
            $limit=10;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }

       
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $output = array();
        if ($query_res->execute()) {
            
            /*$this->closeStatement($query_res);
            if(empty($profiledata)){
                return $output;
            }*/
            $i=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){

            $output[$i]['id'] = $profiledata['id'];
            $output[$i]['team_name'] = $profiledata['team_name'];
            $output[$i]['is_follow'] = $profiledata['is_follow'];
            $output[$i]['is_following'] = $profiledata['is_following'];
            $output[$i]['firstname'] = $profiledata['firstname'];
            $output[$i]['lastname'] = $profiledata['lastname'];
            $output[$i]['email'] = $profiledata['email'];
            $output[$i]['country_mobile_code'] = $profiledata['country_mobile_code'];               
            $output[$i]['phone'] = $profiledata['phone']; 
            $output[$i]['follower_count'] = $profiledata['follower_count']; 
            $output[$i]['following_count'] = $profiledata['following_count']; 
            $output[$i]['post_count'] = $profiledata['post_count'];
            $output[$i]['image'] = !empty($profiledata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;

             if(!empty($profiledata['external_image'])){
                $output[$i]['image']=$profiledata['external_image'];
            }
            $i++;
            }
            
            
            

            
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;
    }

    public function create_post($user_id,$customer_team_id,$post_type,$description){



        $query = "SELECT id,match_unique_id FROM tbl_cricket_customer_teams WHERE id = ? AND customer_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_team_id);
        $query_res->bindParam(2, $user_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);

        if($num_rows==0){
            return "NO_TEAM_FOUND";
        }

        $data=$query_res->fetch();
        $match_unique_id=$data['match_unique_id'];

        $get_match_data=$this->get_match_detail_by_match_unique_id($match_unique_id);
        
        if(empty($get_match_data)){
            return "NO_MATCH_FOUND";
        }
        
        $match_name= $get_match_data['name'];
        $sport_id=0;

            $created=time();
            $json=$this->get_customer_match_team_detail($customer_team_id);
            $json=json_encode($json);
            $insert_query = "INSERT INTO tbl_posts SET user_id=?, description=?, post_type=?, sport_id=?, team_id=?, match_unique_id=?, match_name=?, json=?, created=?";
            $insert_query_res  = $this->conn->prepare($insert_query);
            $insert_query_res->bindParam(1,$user_id);
            $insert_query_res->bindParam(2,$description);
            $insert_query_res->bindParam(3,$post_type);
            $insert_query_res->bindParam(4,$sport_id);
            $insert_query_res->bindParam(5,$customer_team_id);
            $insert_query_res->bindParam(6,$match_unique_id);
            $insert_query_res->bindParam(7,$match_name);
            $insert_query_res->bindParam(8,$json);
            $insert_query_res->bindParam(9,$created);
            if(!$insert_query_res->execute()){
                $this->sql_error($insert_query_res);
                return "UNABLE_TO_PROCEED";
            }
            $post_id= $this->conn->lastInsertId();

            $this->update_customer_post_counts($user_id);

            return $this->get_customer_posts($user_id,$post_id,$user_id);
    }

    public function get_reactions(){

        $query = "SELECT * FROM tbl_post_reactions order by id ASC";

        $query_res = $this->conn->prepare($query);
        
        $output = array();
        if ($query_res->execute()) {           
            
            $i=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){

            $output[$i]['id'] = $profiledata['id'];
           
            $output[$i]['name'] = $profiledata['name'];
            $output[$i]['image'] = !empty($profiledata['image']) ? REACTION_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;
            $output[$i]['created'] = $profiledata['created']; 
            $i++;
            }
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;

    }

    public function get_reactions_by_post_reaction($post_id){

        $query = "SELECT tpr.*,(SELECT COUNT(id) FROM tbl_post_user_reactions where post_id='$post_id' AND reaction_id=tpr.id) as reaction_count FROM tbl_post_reactions tpr order by tpr.id ASC";

        $query_res = $this->conn->prepare($query);
        
        $output = array();
        if ($query_res->execute()) { 

            $output[0]['id'] = "-1";           
            $output[0]['name'] = "ALL";
            $output[0]['image'] = NO_IMG_URL;          
            
            $i=1;
            $count=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){

            $output[$i]['id'] = $profiledata['id'];           
            $output[$i]['name'] = $profiledata['name'];
            $output[$i]['reaction_count'] = $profiledata['reaction_count'];
            $output[$i]['image'] = !empty($profiledata['image']) ? REACTION_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;
            $count+=$profiledata['reaction_count'];
            $i++;
            }
            $output[0]['reaction_count'] = $count;

            
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;

    }

    public function get_customer_posts($customer_id,$post_id,$user_id){


        $query = "SELECT tp.*,tc.id as customer_id,tc.firstname,tc.lastname,tc.team_name,tc.image,tc.external_image,(SELECT CONCAT(tpr.id,'---',tpr.name,'---',tpr.image) FROM tbl_post_reactions tpr where tpr.id IN (SELECT tpur.reaction_id FROM tbl_post_user_reactions tpur where tpur.user_id='$user_id' AND tpur.post_id=tp.id)) as reaction ,(SELECT count(id) FROM tbl_post_user_reactions where post_id=tp.id) as reaction_count FROM tbl_posts tp LEFT JOIN tbl_customers tc ON(tp.user_id=tc.id) where 1=1";
        if(!empty($customer_id)){
            $query.= " AND tp.user_id=?";
        }
        if(!empty($post_id)){
            $query.= " AND tp.id=?";
        }
        $query.= " order by id DESC";

        $query_res = $this->conn->prepare($query);
        $count=1;
        if(!empty($customer_id)){
           $query_res->bindParam($count, $customer_id);
           $count++;
        }
        if(!empty($post_id)){
           $query_res->bindParam($count, $post_id);
        }
        
        $output = array();
        if ($query_res->execute()) {           
            
            $i=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){
            $user_detail=null;
            $reaction_detail=null;
            if(!empty($profiledata['team_name'])){
                 $user_detail['id']=$profiledata['customer_id'];
                 $user_detail['firstname']=$profiledata['firstname'];
                 $user_detail['lastname']=$profiledata['lastname'];
                 $user_detail['team_name']=$profiledata['team_name'];
                 $user_detail['image']=!empty($profiledata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;

                 if(!empty($profiledata['external_image'])){
                    $user_detail['image']=$profiledata['external_image'];
                 }
            }
            $reaction_message="";
            if(!empty($profiledata['reaction'])){
                $reaction_data=explode('---', $profiledata['reaction']);
                $reaction_detail['id']=$reaction_data[0];
                $reaction_detail['name']=$reaction_data[1];
                $reaction_detail['image']=!empty($reaction_data[2]) ? REACTION_IMAGE_THUMB_URL.$reaction_data[2] : NO_IMG_URL;
                $reaction_count=$profiledata['reaction_count']-1;
                    if($reaction_count>0){
                            $reaction_message="You and ".$reaction_count." another person reacted.";
                    }else{
                         $reaction_message="You reacted.";
                     
                    }
               
            }else{

                $reaction_count=$profiledata['reaction_count'];
                    if($reaction_count>0){
                            $reaction_message=$reaction_count." person reacted.";
                    }else{
                         $reaction_message="";
                     
                    }

            }
            
           

            $output[$i]['id'] = $profiledata['id'];           
            $output[$i]['title'] = $profiledata['title'];            
            $output[$i]['description'] = $profiledata['description'];            
            $output[$i]['post_type'] = $profiledata['post_type'];

            $sport_array=array(); 
            if($profiledata['sport_id']==0){
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Cricket";
            }else if($profiledata['sport_id']==1){
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Kabaddi";
               
            }else if($profiledata['sport_id']==2){               
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Soccer";;
                
            }
            $output[$i]['sport'] = $sport_array;
            $output[$i]['team_id'] = $profiledata['team_id']; 
            $output[$i]['match_unique_id'] = $profiledata['match_unique_id']; 
            $output[$i]['match_name'] = $profiledata['match_name']; 
            $output[$i]['json'] = json_decode($profiledata['json'],true); 
            $output[$i]['user_detail'] = $user_detail; 
            $output[$i]['reaction_detail'] = $reaction_detail; 
            $output[$i]['reaction_detail_message'] = $reaction_message; 
            $output[$i]['reactions'] = $this->get_reactions_by_post_reaction($profiledata['id']);

            $output[$i]['created'] = $profiledata['created']; 
            $i++;
            }
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;


    }


    public function get_customer_posts_user_reaction($user_id,$post_id,$reaction_id){


        $query = "SELECT (SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE follower_id='$user_id' AND following_id=tpur.user_id ) as is_follow,(SELECT IF(COUNT(id)>0, 'Y', 'N')   FROM tbl_follow  WHERE following_id='$user_id' AND follower_id=tpur.user_id ) as is_following,tpur.*,tc.id as customer_id,tc.firstname,tc.lastname,tc.team_name,tc.image,tc.external_image,tpr.id as reaction_id,tpr.name,tpr.image as reaction_image FROM tbl_post_user_reactions tpur LEFT JOIN tbl_customers tc ON(tc.id=tpur.user_id) LEFT JOIN tbl_post_reactions tpr ON (tpr.id=tpur.reaction_id) where tpur.post_id=?";
        if($reaction_id !='-1'){
            $query.= " AND tpur.reaction_id=?";
        }
        
        $query.= " order by tpur.id DESC";

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $post_id);
          
        if($reaction_id !='-1'){
           $query_res->bindParam(2, $reaction_id);
        }
        
        $output = array();
        if ($query_res->execute()) {           
            
            $i=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){
            $user_detail=null;
            $reaction_detail=null;
            if(!empty($profiledata['team_name'])){
                 $user_detail['id']=$profiledata['customer_id'];
                 $user_detail['firstname']=$profiledata['firstname'];
                 $user_detail['lastname']=$profiledata['lastname'];
                 $user_detail['team_name']=$profiledata['team_name'];
                 $user_detail['image']=!empty($profiledata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;

                 if(!empty($profiledata['external_image'])){
                    $user_detail['image']=$profiledata['external_image'];
                 }
                 $user_detail['is_follow'] = $profiledata['is_follow'];
                 $user_detail['is_following'] = $profiledata['is_following'];
            }
           
            if(!empty($profiledata['reaction_id'])){
               
                $reaction_detail['id']=$profiledata['reaction_id'];
                $reaction_detail['name']=$profiledata['name'];
                $reaction_detail['image']=!empty($profiledata['image']) ? REACTION_IMAGE_THUMB_URL.$profiledata['reaction_image'] : NO_IMG_URL;
                   
               
            }
            
           

            $output[$i]['id'] = $profiledata['id'];           
            $output[$i]['post_id'] = $profiledata['post_id'];
            $output[$i]['user_detail'] = $user_detail; 
            $output[$i]['reaction_detail'] = $reaction_detail; 
            $output[$i]['created'] = $profiledata['created']; 
            $i++;
            }
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;


    }

    public function react_post($user_id,$post_id,$reaction_id){

        $query = "SELECT * FROM tbl_post_user_reactions WHERE post_id = ? AND user_id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $post_id);
        $query_res->bindParam(2, $user_id);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);
        if($num_rows==0){


                $query = "SELECT * FROM tbl_post_user_reactions WHERE post_id = ? AND reaction_id=? AND user_id=?";
                $query_res = $this->conn->prepare($query);
                $query_res->bindParam(1, $post_id);
                $query_res->bindParam(2, $reaction_id);
                $query_res->bindParam(3, $user_id);
                $query_res->execute();
                $num_rows =$query_res->rowCount();
                $this->closeStatement($query_res);

                if($num_rows==0){
                    $created=time();

                    $insert_query = "INSERT INTO tbl_post_user_reactions SET post_id=?, reaction_id=?, user_id=?, created=?";
                    $insert_query_res  = $this->conn->prepare($insert_query);
                    $insert_query_res->bindParam(1,$post_id);
                    $insert_query_res->bindParam(2,$reaction_id);
                    $insert_query_res->bindParam(3,$user_id);
                    $insert_query_res->bindParam(4,$created);           
                    if(!$insert_query_res->execute()){
                        $this->sql_error($insert_query_res);
                        return "UNABLE_TO_PROCEED";
                    }
                    return "INSERTED";
                    
                }else{


                    $insert_query = "DELETE FROM tbl_post_user_reactions where post_id=? AND reaction_id=? AND user_id=?";
                    $insert_query_res  = $this->conn->prepare($insert_query);
                    $insert_query_res->bindParam(1,$post_id);
                    $insert_query_res->bindParam(2,$reaction_id);
                    $insert_query_res->bindParam(3,$user_id);                  
                    if(!$insert_query_res->execute()){
                        $this->sql_error($insert_query_res);
                        return "UNABLE_TO_PROCEED";
                    }
                    return "DELETED";


                }
        }else{

            $query = "SELECT * FROM tbl_post_user_reactions WHERE post_id = ? AND reaction_id=? AND user_id=?";
                $query_res = $this->conn->prepare($query);
                $query_res->bindParam(1, $post_id);
                $query_res->bindParam(2, $reaction_id);
                $query_res->bindParam(3, $user_id);
                $query_res->execute();
                $num_rows =$query_res->rowCount();
                $this->closeStatement($query_res);

                if($num_rows==0){


                    $created=time();

                    $insert_query = "INSERT INTO tbl_post_user_reactions SET post_id=?, reaction_id=?, user_id=?, created=? ON DUPLICATE KEY UPDATE reaction_id=?, created=?";
                    $insert_query_res  = $this->conn->prepare($insert_query);
                    $insert_query_res->bindParam(1,$post_id);
                    $insert_query_res->bindParam(2,$reaction_id);
                    $insert_query_res->bindParam(3,$user_id);
                    $insert_query_res->bindParam(4,$created);           
                    $insert_query_res->bindParam(5,$reaction_id);           
                    $insert_query_res->bindParam(6,$created);           
                    if(!$insert_query_res->execute()){
                        $this->sql_error($insert_query_res);
                        return "UNABLE_TO_PROCEED";
                    }
                    return "INSERTED";

                }else{




                    $insert_query = "DELETE FROM tbl_post_user_reactions where post_id=? AND reaction_id=? AND user_id=?";
                    $insert_query_res  = $this->conn->prepare($insert_query);
                    $insert_query_res->bindParam(1,$post_id);
                    $insert_query_res->bindParam(2,$reaction_id);
                    $insert_query_res->bindParam(3,$user_id);                  
                    if(!$insert_query_res->execute()){
                        $this->sql_error($insert_query_res);
                        return "UNABLE_TO_PROCEED";
                    }
                    return "DELETED";
                }    



        }        

    }


    public function get_customer_feeds($user_id,$page_no){


        $query = "SELECT tp.*,tc.id as customer_id,tc.firstname,tc.lastname,tc.team_name,tc.image,tc.external_image,(SELECT CONCAT(tpr.id,'---',tpr.name,'---',tpr.image) FROM tbl_post_reactions tpr where tpr.id IN (SELECT tpur.reaction_id FROM tbl_post_user_reactions tpur where tpur.user_id='$user_id' AND tpur.post_id=tp.id)) as reaction ,(SELECT count(id) FROM tbl_post_user_reactions where post_id=tp.id) as reaction_count FROM tbl_posts tp LEFT JOIN tbl_customers tc ON(tp.user_id=tc.id) where tp.user_id='$user_id' OR tp.user_id IN (SELECT following_id FROM tbl_follow where follower_id='$user_id')";

       
       
        $query.= " order by tp.id DESC";

         if($page_no>0){
            $limit=10;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";

        }

        

        $query_res = $this->conn->prepare($query);
       
        
        
        $output = array();
        if ($query_res->execute()) {           
            
            $i=0;
            while($profiledata = $query_res->fetch(PDO::FETCH_ASSOC)){
            $user_detail=null;
            $reaction_detail=null;
            if(!empty($profiledata['team_name'])){
                $user_detail['id']=$profiledata['customer_id'];
                 $user_detail['firstname']=$profiledata['firstname'];
                 $user_detail['lastname']=$profiledata['lastname'];
                 $user_detail['team_name']=$profiledata['team_name'];
                 $user_detail['image']=!empty($profiledata['image']) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : NO_IMG_URL;
                 if(!empty($profiledata['external_image'])){
                    $user_detail['image']=$profiledata['external_image'];
                 }
            }
            $reaction_message="";
            if(!empty($profiledata['reaction'])){
                $reaction_data=explode('---', $profiledata['reaction']);
                $reaction_detail['id']=$reaction_data[0];
                $reaction_detail['name']=$reaction_data[1];
                $reaction_detail['image']=!empty($reaction_data[2]) ? REACTION_IMAGE_THUMB_URL.$reaction_data[2] : NO_IMG_URL;
                $reaction_count=$profiledata['reaction_count']-1;
                    if($reaction_count>0){
                            $reaction_message="You and ".$reaction_count." another person reacted.";
                    }else{
                         $reaction_message="You reacted.";
                     
                    }
               
            }else{

                $reaction_count=$profiledata['reaction_count'];
                    if($reaction_count>0){
                            $reaction_message=$reaction_count." person reacted.";
                    }else{
                         $reaction_message="";
                     
                    }

            }
            
           

            $output[$i]['id'] = $profiledata['id'];           
            $output[$i]['title'] = $profiledata['title'];            
            $output[$i]['description'] = $profiledata['description'];            
            $output[$i]['post_type'] = $profiledata['post_type'];

            $sport_array=array(); 
            if($profiledata['sport_id']==0){
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Cricket";
            }else if($profiledata['sport_id']==1){
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Kabaddi";
               
            }else if($profiledata['sport_id']==2){               
                $sport_array['sport_id']=$profiledata['sport_id'];
                $sport_array['name']="Soccer";;
                
            }
            $output[$i]['sport'] = $sport_array;
            $output[$i]['team_id'] = $profiledata['team_id']; 
            $output[$i]['match_unique_id'] = $profiledata['match_unique_id']; 
            $output[$i]['match_name'] = $profiledata['match_name']; 
            $output[$i]['json'] = json_decode($profiledata['json'],true); 
            $output[$i]['user_detail'] = $user_detail; 
            $output[$i]['reaction_detail'] = $reaction_detail; 
            $output[$i]['reaction_detail_message'] = $reaction_message; 
            $output[$i]['reactions'] = $this->get_reactions_by_post_reaction($profiledata['id']);

            $output[$i]['created'] = $profiledata['created']; 
            $i++;
            }
            
        }else{
            $this->sql_error($query_res);
        }
        return $output;


    }


    public function get_series($series_id=0){
        $query = "SELECT * FROM tbl_cricket_series where status='A' AND is_deleted='N'";
        if(!empty($series_id)){
            $query.=" AND id='$series_id'";
        }
        $query.=" ORDER BY id DESC";

        $query_res = $this->conn->prepare($query);
       
        $output = array();
        if ($query_res->execute()) {    
        $i=0;       
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){
                $icon=array();
                $icon['id']=$data['id'];
                $icon['name']=$data['name'];
                $icon['abbr']=$data['abbr'];
                $icon['season']=$data['season'];
                $output[$i]=$icon;
                $i++;

            }

        }

        return $output;
    }


    public function get_series_leaderboard($page_no,$series_id,$user_id){
        $query = "SELECT tcls.*,tc.firstname,tc.lastname,tc.team_name,tc.image,tc.external_image FROM tbl_cricket_leaderboard_series tcls LEFT JOIN tbl_customers tc ON(tcls.customer_id=tc.id) INNER JOIN `tbl_cricket_series` as tcs ON tcs.id = tcls.series_id  where tcls.series_id=? AND tcls.customer_id!=? and tcs.status='A' ORDER BY tcls.new_rank ASC";

        if($page_no>0){
            $limit=30;
            $offset=($page_no-1)*$limit;

            $query .= " LIMIT $limit OFFSET $offset";
        }

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $series_id);
        $query_res->bindParam(2, $user_id);
        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];
        
        $output = array();
        if ($query_res->execute()) {    
            
            $i=0;       
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){

                $user_detail=array();
                $user_detail['id']=$data['customer_id'];
                $user_detail['firstname']=$data['firstname'];
                $user_detail['lastname']=$data['lastname'];
                $user_detail['team_name']=$data['team_name'];
                $user_detail['image']=!empty($data['image']) ? CUSTOMER_IMAGE_THUMB_URL.$data['image'] : $no_imag_url;

                if(!empty($data['external_image'])){
                    $user_detail['image']=$data['external_image'];
                }
                

                $icon=array();
                $icon['id']=$data['id'];
                $icon['old_rank']=$data['old_rank'];
                $icon['new_rank']=$data['new_rank'];
                $icon['old_point']=$data['old_point'];
                $icon['new_point']=$data['new_point'];
                $icon['user_detail']=$user_detail;
                $output[$i]=$icon;
                $i++;
            }
        }
        return $output;
    }

    public function get_series_leaderboard_self($series_id,$user_id){
        $query = "SELECT tcls.*,tc.firstname,tc.lastname,tc.team_name,tc.image,tc.external_image FROM tbl_cricket_leaderboard_series tcls LEFT JOIN tbl_customers tc ON(tcls.customer_id=tc.id) where tcls.series_id=? AND tcls.customer_id=? ORDER BY tcls.new_rank ASC";

        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $series_id);
        $query_res->bindParam(2, $user_id);
        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];
        $output = NULL;
        if ($query_res->execute()) {    
            $data = $query_res->fetch(PDO::FETCH_ASSOC);

            if(!empty($data)){
                $user_detail=array();
                $user_detail['id']=$data['customer_id'];
                $user_detail['firstname']=$data['firstname'];
                $user_detail['lastname']=$data['lastname'];
                $user_detail['team_name']=$data['team_name'];
                $user_detail['image']=!empty($data['image']) ? CUSTOMER_IMAGE_THUMB_URL.$data['image'] : $no_imag_url;
                if(!empty($data['external_image'])){
                    $user_detail['image']=$data['external_image'];
                }
            
                $icon=array();
                $icon['id']=$data['id'];
                $icon['old_rank']=$data['old_rank'];
                $icon['new_rank']=$data['new_rank'];
                $icon['old_point']=$data['old_point'];
                $icon['new_point']=$data['new_point'];
                $icon['user_detail']=$user_detail;

                $output=$icon;
            }
        }

        return $output;
    }


    public function get_series_leaderboard_customer_matches($series_id,$customer_id){
        $query = "SELECT tclm.*,tcm.name,tcm.short_title,tcm.subtitle,tcm.match_date FROM tbl_cricket_leaderboard_matches tclm LEFT JOIN tbl_cricket_matches tcm ON(tclm.match_unique_id=tcm.unique_id) where tclm.match_unique_id IN(SELECT unique_id from tbl_cricket_matches where series_id=?) AND tclm.customer_id=? ORDER BY tclm.id DESC";


        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $series_id);
        $query_res->bindParam(2, $customer_id);
        $output = array();
        if ($query_res->execute()) {    
            
            $i=0;       
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){

                $match_detail=array();
                $match_detail['id']=$data['match_id'];
                $match_detail['unique_id']=$data['match_unique_id'];
                $match_detail['name']=$data['name'];
                $match_detail['short_title']=$data['short_title'];
                $match_detail['subtitle']=$data['subtitle'];
                $match_detail['match_date']=$data['match_date'];
                
                

                $icon=array();
                $icon['id']=$data['id'];
                $icon['customer_team_id']=$data['customer_team_id'];
                $icon['new_rank']=$data['new_rank'];
                $icon['new_point']=$data['new_point'];
                $icon['match_detail']=$match_detail;
                $output[$i]=$icon;
                $i++;

            }

        }

        return $output;
    }




    public function get_app_custom_icons(){
        $query = "SELECT * FROM tbl_app_icon_customize where status='A'";
        $query_res = $this->conn->prepare($query);
       
        $output = array();
        if ($query_res->execute()) {           
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){
                $icon=array();
                $icon['tag']=$data['tag'];
                $icon['name']=$data['name'];
                $icon['image']= !empty($data['image']) ? APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$data['image'] : NO_IMG_URL;

                $output[$data['tag']]=$icon;

            }

        }

        return $output;
    }

    public function get_quotations(){
        $query = "SELECT * FROM tbl_quotations where status='A'";
        $query_res = $this->conn->prepare($query);
       
        $output = array();
        if ($query_res->execute()) {           
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){
                $icon=array();
                $icon['game_id']=$data['game_id'];
                $icon['width']=$data['width'];
                $icon['height']=$data['height'];
                $icon['link']=$data['link'];
                $icon['image']= !empty($data['image']) ? QUOTATIONS_IMAGE_LARGE_URL.$data['image'] : NO_IMG_URL;

                $output[]=$icon;

            }

        }

        return $output;
    }

    public function get_games(){
        $time=time();
        $query = "SELECT tg.id, tg.name, tg.image, tq.game_id, tq.width, tq.height, tq.image as q_image, tq.link FROM tbl_games tg LEFT JOIN tbl_quotations tq ON (tg.id=tq.game_id AND tq.status='A' AND tq.expiry_date>$time) where tg.status='A' /*and tg.is_deleted='N'*/ ORDER BY tg.orderno asc";
        $query_res = $this->conn->prepare($query);
       
        $output = array();
        if ($query_res->execute()) {           
            while($data = $query_res->fetch(PDO::FETCH_ASSOC)){
                $icon=array();
                $icon['id']=$data['id'];
                $icon['name']=$data['name'];
                $icon['image']= !empty($data['image']) ? GAME_IMAGE_LARGE_URL.$data['image'] : NO_IMG_URL;


                $quotation=NULL;
                if(!empty($data['q_image'])){

                    $quotation['game_id']=$data['game_id'];
                    $quotation['width']=$data['width'];
                    $quotation['height']=$data['height'];
                    $quotation['link']=$data['link'];
                    $quotation['image']= !empty($data['q_image']) ? QUOTATIONS_IMAGE_LARGE_URL.$data['q_image'] : NO_IMG_URL;

                }
                
                $icon['quotation']=$quotation;

                $output[]=$icon;
            }
        }

        return $output;
    }

    public function apply_promocode($customer_id,$promocode,$amount){
        $time=time();
        $res=array();
        $res['code']=0;
        $res['message']="";

        $rcbQuery = "SELECT trcb.id, trcb.recharge, trcb.cach_bonus, trcb.is_use, trcb.is_use_max, trcb.max_recharge, trcb.cash_bonus_type, trcb.code,trcb.start_date,trcb.end_date ,(SELECT count(tcwh.id) from tbl_customer_wallet_histories tcwh where tcwh.rcb_id=trcb.id AND tcwh.customer_id=?) as already_use FROM tbl_recharge_cach_bonus trcb WHERE  trcb.status='A' AND trcb.is_deleted='N' AND trcb.code=? limit 1";
                        $rcbQueryStmt  = $this->conn->prepare($rcbQuery);
                        $rcbQueryStmt->bindParam(1,$customer_id);
                        $rcbQueryStmt->bindParam(2,$promocode);
                        $rcbQueryStmt->execute();
                        if($rcbQueryStmt->rowCount()==0){

                           $res['code']=UNABLE_TO_PROCEED;
                           $res['message']="Invalid Promocode.";
                           return $res;
                           
                        }
                        $rcbData = $rcbQueryStmt->fetch(PDO::FETCH_ASSOC);
                       
                        $this->closeStatement($rcbQueryStmt);
                         if($amount<$rcbData['recharge'] || $amount>$rcbData['max_recharge']){

                            $res['code']=UNABLE_TO_PROCEED;
                           $res['message']="Invalid amount. Amount should be between ".$rcbData['recharge']." to ".$rcbData['max_recharge'];
                           return $res;
                         }

                         if($time<$rcbData['start_date'] || $time>$rcbData['end_date']){

                            

                            if($time>$rcbData['end_date']){

                               $res['code']=UNABLE_TO_PROCEED;
                               $res['message']="Promocode expired.";
                               return $res;

                            }

                           $res['code']=UNABLE_TO_PROCEED;
                           $res['message']="Invalid Promocode.";
                           return $res;

                          
                         }

                            $is_use=$rcbData['is_use'];
                            $is_use_max=$rcbData['is_use_max'];
                            $already_use=$rcbData['already_use'];
                            if(($is_use=="S" && $already_use>0) || ($is_use=="M" && $already_use>=$is_use_max)){

                               $res['code']=UNABLE_TO_PROCEED;
                               $res['message']="Promocode already used.";
                               return $res;
                                
                            }

                            $res['data']=$rcbData;
                            return $res;
    }

    public function create_customer_enquiry($user_id,$subject,$message){

        $time=time();
        $ticketNo="#TICKET-".APP_NAME."-".$time.$user_id;

        $insert_query = "INSERT INTO tbl_customer_quries SET ticket_id=?, customer_id=?, subject=?, message=?, created=?";
        $insert_query_res  = $this->conn->prepare($insert_query);
        $insert_query_res->bindParam(1,$ticketNo);
        $insert_query_res->bindParam(2,$user_id);
        $insert_query_res->bindParam(3,$subject);
        $insert_query_res->bindParam(4,$message);
        $insert_query_res->bindParam(5,$time);


        if(!$insert_query_res->execute()){
            $this->sql_error($insert_query_res);
        }

        $this->closeStatement($insert_query_res);

        $output['ticket_id']=$ticketNo;
        return $output;
    }



 ###################################### CALLED FUNCTION START ############################################

    public function send_notification_and_save($message,$user_id,$alert_message = APP_NAME.' notification center',$dbsave=true){
        ini_set('display_errors',1);
        $this->setGroupConcatLimit();

        $query = "SELECT (SELECT GROUP_CONCAT(device_token)  FROM tbl_customer_logins  WHERE customer_id in ($user_id) and device_type='I') as device_tokens_ios, (SELECT GROUP_CONCAT(device_token)  FROM tbl_customer_logins  WHERE customer_id in ($user_id) and device_type='A') as device_tokens_android";
        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $num_rows = $query_res->rowCount();
   ////     echo $num_rows."<br>";
        
        if ($num_rows > 0) {
            $booking_array = $query_res->fetch();

            $this->closeStatement($query_res);

            $tokens_ios=$booking_array['device_tokens_ios'];
            $tokens_android=$booking_array['device_tokens_android'];

            if(!empty($tokens_ios)){
                
                $ios_tokens_array   =   explode(',',$tokens_ios);
                $ios_chunk_array    =   array_chunk($ios_tokens_array,900);

                if(!empty($ios_chunk_array[0][0])){

                    foreach($ios_chunk_array as $dt){
                        $tokens_ios=$dt; 
                        $this->send_notification($message, $tokens_ios, $alert_message, $message['noti_type'],'I');
                    }
                }
            }

            if(!empty($tokens_android)){
                
                    
                    $android_tokens_array   =   explode(',',$tokens_android);
                    $android_chunk_array    =   array_chunk($android_tokens_array,900);

                    if(!empty($android_chunk_array[0][0])){

                        foreach($android_chunk_array as $dt){
                            $tokens_android=$dt; 
                            $this->send_notification($message, $tokens_android, $alert_message, $message['noti_type'],'A');
                        }
                    }
                
               
            }

            if($dbsave){

                $title=APP_NAME;
                $sender_type="APP";
                $created=time();

                $insert_query = "INSERT INTO tbl_notifications SET users_id=?, title=?, notification=?,sender_type=?, created=?";
                $insert_query_res  = $this->conn->prepare($insert_query);
                $insert_query_res->bindParam(1,$user_id);
                $insert_query_res->bindParam(2,$title);
                $insert_query_res->bindParam(3,$alert_message);
                $insert_query_res->bindParam(4,$sender_type);
                $insert_query_res->bindParam(5,$created);


                if(!$insert_query_res->execute()){
                    $this->sql_error($insert_query_res);
                }

                $this->closeStatement($insert_query_res);
            }

        }else{
            $this->closeStatement($query_res);
        }


    }


    public function send_notification($message, $registration_ids, $alert_message = APP_NAME.' notification center', $noti_type = 'test',$device_type="A") {
    
        if (empty($registration_ids)) {
            return;
        }
        
        $url = FIRE_BASE_URL;
        $API_KEY = FCM_KEY;

        $sound = 'default';

        $noti_title=APP_NAME;
        if($noti_type=="adminalert" || $noti_type=="lineup_out"){
            $noti_title=$message['title'];
        }

        $message['noti_time']=time();
        $message['message']=$alert_message;
        $message['title']=$noti_title;

         /*if($device_type=="A"){
                $fields = array(
                    'registration_ids' => $registration_ids,
                    'data' => $message

                );

        }else{*/

        $fields = array(
                'registration_ids' => $registration_ids,
                'data' => $message,
                'notification' => array(
                        "title" =>  $message['title'],
                        "body" =>  $message['message'],
                        "sound" => $sound
                         )

            );

        if(isset($message['noti_thumb'])&&!empty($message['noti_thumb'])){
            $fields['content_available']=true;
            $fields['mutable_content']=true;
        }



       /* }*/
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
     public function get_customer_wallet_amount($customer_id,$wallet_type) {
        $query  = "SELECT $wallet_type FROM tbl_customers WHERE id=?";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$customer_id);
        $query_res->execute();
        $num_rows = $query_res->rowCount();

        if($num_rows>0){
            $array = $query_res->fetch();
            $this->closeStatement($query_res);
            return $array[$wallet_type];
        }else{
            $this->closeStatement($query_res);
        }  

        return 0;        
            
    }





    public function update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,$transaction_type,$type,$transaction_id,$description,$rcbId=0,$refCwhId=0,$send_sms=true,$team_id=0,$refrence_id=null,$json_data=null,$payment_method=null,$promocode=null){

            $WALLET_TYPE_ARRAY=unserialize(WALLET_TYPE);
            
            $wallet_name=$WALLET_TYPE_ARRAY[$wallet_type];
            
            $time=time();
            $previous_amount=$this->get_customer_wallet_amount($customer_id,$wallet_type);
    

            if($transaction_type=="CREDIT"){    
                $current_amount=$previous_amount+$amount; 
                $wallet_update  = "UPDATE tbl_customers set $wallet_type=$wallet_type+?  WHERE id=?";
            }else if($transaction_type=="DEBIT"){
                $current_amount=$previous_amount-$amount;
                $wallet_update  = "UPDATE tbl_customers set $wallet_type=$wallet_type-?  WHERE id=?";
            }else{
                return;
            }
            $query_res  = $this->conn->prepare($wallet_update);
            $query_res->bindParam(1,$amount);
            $query_res->bindParam(2,$customer_id);
             
            if($query_res->execute()) {

                    $this->closeStatement($query_res);
                
            ///     echo $query = "INSERT INTO tbl_customer_wallet_histories SET sport_id=0, customer_id='$customer_id', match_contest_id='$match_contest_id', wallet_type='$wallet_name',transaction_type='$transaction_type', transaction_id='$transaction_id', type='$type', previous_amount='$previous_amount', amount='$amount', current_amount='$current_amount', description='$description', created=$time, rcb_id=0, ref_cwh_id=0, team_id=0, refrence_id=2, json_data='',payment_method=''";
                 
                 
                     $query = "INSERT INTO tbl_customer_wallet_histories SET sport_id=0, customer_id=?, match_contest_id=?, wallet_type=?,transaction_type=?, transaction_id=?, type=?, previous_amount=?, amount=?, current_amount=?, description=?, created=?, rcb_id=?, ref_cwh_id=?, team_id=?, refrence_id=?, json_data=?,payment_method=?";
                    $query  = $this->conn->prepare($query);
                    $query->bindParam(1,$customer_id);
                    $query->bindParam(2,$match_contest_id);
                    $query->bindParam(3,$wallet_name);
                    $query->bindParam(4,$transaction_type);
                    $query->bindParam(5,$transaction_id);
                    $query->bindParam(6,$type);
                    $query->bindParam(7,$previous_amount);
                    $query->bindParam(8,$amount);
                    $query->bindParam(9,$current_amount);
                    $query->bindParam(10,$description);
                    $query->bindParam(11,$time);
                    $query->bindParam(12,$rcbId);
                    $query->bindParam(13,$refCwhId);
                    $query->bindParam(14,$team_id);
                    $query->bindParam(15,$refrence_id);
                    $query->bindParam(16,$json_data);
                    $query->bindParam(17,$payment_method);



                    if(!$query->execute()){
                        $this->sql_error($query);
                    }
                  ////  var_dump( $query->queryString, $query->_debugQuery() );

                    $newcwhId= $this->conn->lastInsertId();
                    $this->closeStatement($query);

                    if($send_sms){
                            $get_customer_detail_query = "SELECT phone, country_mobile_code FROM tbl_customers WHERE  id=?";
                            $query_res_new  = $this->conn->prepare($get_customer_detail_query);
                            $query_res_new->bindParam(1,$customer_id);      
                            $query_res_new->execute();
                            $num_rows = $query_res_new->rowCount();
                            $output = array();
                            if ($num_rows > 0) {
                                $array = $query_res_new->fetch();
                                $this->closeStatement($query_res_new);

                                $template="Wallet";
                                $data=array();
                                $data['type']=$transaction_type=="CREDIT"?"Credited":"Debited";
                                $data['amount']=$amount;
                                $data['transaction_id']=$transaction_id;
                                $data['current_amount']=$current_amount;
                                $data['wallet_name']=$wallet_name;
                                $this->sendTemplatesInSMS_other($template, $data, $array['phone'], $array['country_mobile_code']);
                            }else{
                                $this->closeStatement($query_res_new);
                            }  
                    }

                    if($type=="CUSTOMER_WALLET_RECHARGE" && !empty($promocode)){

                        $rcbQuery = "SELECT trcb.id, trcb.recharge, trcb.cach_bonus, trcb.is_use, trcb.is_use_max,trcb.max_recharge,trcb.cash_bonus_type,trcb.code,trcb.start_date,trcb.end_date ,(SELECT count(tcwh.id) from tbl_customer_wallet_histories tcwh where tcwh.rcb_id=trcb.id AND tcwh.customer_id=?) as already_use FROM tbl_recharge_cach_bonus trcb WHERE  trcb.status='A' AND trcb.is_deleted='N' AND trcb.code=? limit 1";

                        $rcbQueryStmt  = $this->conn->prepare($rcbQuery);
                        $rcbQueryStmt->bindParam(1,$customer_id);
                        $rcbQueryStmt->bindParam(2,$promocode);
                        $rcbQueryStmt->execute();

                        
                        if($rcbQueryStmt->rowCount()>0){
                            $rcbData = $rcbQueryStmt->fetch();
                            $this->closeStatement($rcbQueryStmt);

                            if($amount>=$rcbData['recharge'] && $amount<=$rcbData['max_recharge']){

                                if($time>=$rcbData['start_date'] && $time<=$rcbData['end_date']){

                                    $is_use=$rcbData['is_use'];
                                    $is_use_max=$rcbData['is_use_max'];
                                    $already_use=$rcbData['already_use'];
                                    if(($is_use=="S" && $already_use==0) || ($is_use=="M" && $already_use<$is_use_max)){
                                        $newrcbId=$rcbData['id'];
                                        $cach_bonus=$rcbData['cach_bonus'];
                                        $cash_bonus_type=$rcbData['cash_bonus_type'];
                                        if($cash_bonus_type=="F"){
                                            $cach_bonus=round($cach_bonus,2);
                                        }else if($cash_bonus_type=="P"){
                                            $cach_bonus=($cach_bonus/100)*$amount;
                                            $cach_bonus=round($cach_bonus,2);
                                        }

                                        $newdescription=$customer_id." Get Cash Bonus ".$cach_bonus." due to recharge ".$amount.".";
                                        $newtransaction_id="CBWALL".time().$customer_id;

                                        $newwallet_type="bonus_wallet";
                                        $this->update_customer_wallet($customer_id,$match_contest_id,$newwallet_type,$cach_bonus,"CREDIT","CUSTOMER_RECEIVED_RCB",$newtransaction_id,$newdescription,$newrcbId,$newcwhId,$send_sms,0,null,null);

                                        $notification_data=array();
                                        $notification_data['noti_type']='recharge_cash_bonus';
                                        $alert_message = "Congratulations! Got ".CURRENCY_SYMBOL.$cach_bonus." Cash Bonus.";
                                        $this->send_notification_and_save($notification_data,$customer_id,$alert_message,true);
                                    }
                                }
                            }

                        }else{
                            $this->closeStatement($rcbQueryStmt);
                        }
                    }

            } else{
                $this->closeStatement($query_res);
            }
    }



    

    public function sendTemplatesInSMS($templateTitle, $otp, $mobileno, $mobile_code) {
        $select_mail_template_query = "SELECT content FROM tbl_templates WHERE type='S' AND title= ? AND status='A'";
        $select_mail_template = $this->conn->prepare($select_mail_template_query);
        $select_mail_template->bindParam(1, $templateTitle);
        $select_mail_template->execute();
        $num_rows = $select_mail_template->rowCount();
        if ($num_rows > 0) {
            $mailTemplate = $select_mail_template->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($select_mail_template);

            $message= str_replace("{OTP}", $otp, $mailTemplate['content']);
            $this->send_sms($message, $mobileno, 'NO', $mobile_code);
        }else{
            $this->closeStatement($select_mail_template);
        }
    }  

    public function sendTemplatesInSMS_other($templateTitle, $data, $mobileno, $mobile_code) {
        $select_mail_template_query = "SELECT content FROM tbl_templates WHERE type='S' AND title= ? AND status='A'";
        $select_mail_template  = $this->conn->prepare($select_mail_template_query);
        $select_mail_template->bindParam(1,$templateTitle);
        $select_mail_template->execute();
        $num_rows =  $select_mail_template->rowCount();
        if ($num_rows > 0) {
            $mailTemplate = $select_mail_template->fetch();
            $this->closeStatement($select_mail_template);

            $message=$mailTemplate['content'];

            
            if(isset($data['amount'])){ 
            $message= str_replace("{AMOUNT}", $data['amount'], $message);
            }            

            if(isset($data['type'])){ 
              $message= str_replace("{TYPE}", $data['type'], $message);
            }

            if(isset($data['transaction_id'])){ 
              $message= str_replace("{TRANSACTIONID}", $data['transaction_id'], $message);
            }

             if(isset($data['current_amount'])){ 
               $message= str_replace("{CURRENTAMOUNT}", $data['current_amount'], $message);
            }
            if(isset($data['wallet_name'])){ 
               $message= str_replace("{WALLETNAME}", $data['wallet_name'], $message);
            }

            $this->send_sms($message, $mobileno, 'NO', $mobile_code);
        }else{
           $this->closeStatement($select_mail_template);
        }
    }


    public function get_match_progress_by_match_unique_id($match_unique_id){ 

            $query = "SELECT match_progress  FROM  tbl_cricket_matches  WHERE unique_id = ? AND status='A' AND is_deleted='N'";
            $query_res = $this->conn->prepare($query);
            $query_res->bindParam(1, $match_unique_id);        
            $query_res->execute();
            $array = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);
            return $array;
        }  




    public function get_match_detail_by_match_unique_id($match_unique_id){ 

        $query = "SELECT *  FROM  tbl_cricket_matches  WHERE unique_id = ? AND status='A' AND is_deleted='N'";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_unique_id);        
        $query_res->execute();
        $array = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);
        return $array;
    }    

    public function get_match_customer_team_count_by_match_unique_id($customer_id,$match_unique_id){ 
        $query = "SELECT id  FROM  tbl_cricket_customer_teams  WHERE customer_id = ? AND match_unique_id = ?";
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_unique_id);
        
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);
        return $num_rows;
    }


    public function get_match_customer_contest_count_by_match_unique_id($customer_id,$match_unique_id){ 
        $query = "SELECT id  FROM  tbl_cricket_customer_contests  WHERE customer_id = ? AND match_unique_id = ? group by match_contest_id";
        
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $customer_id);
        $query_res->bindParam(2, $match_unique_id);
        
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        $this->closeStatement($query_res);
        return $num_rows;
    }
    

    public function getPlayersByMatchandTeam($match_id,$team_ids) {

        $query_series = "SELECT GROUP_CONCAT(unique_id) as match_ids FROM  tbl_cricket_matches where series_id IN(select series_id from tbl_cricket_matches where  unique_id='$match_id')";
        $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_series_res);

        $match_ids=$array_series['match_ids'];


        $query_series = "SELECT playing_squad_updated FROM  tbl_cricket_matches where unique_id='$match_id'";
        $query_series_res = $this->conn->prepare($query_series);
        $query_series_res->execute();
        $array_series = $query_series_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_series_res);

        $playing_squad_updated=$array_series['playing_squad_updated'];

        $query = "SELECT tcmp.team_id,
        tcmp.playing_role,
        tcmp.credits,tcmp.is_in_playing_squad,
        tcmp.image,tcmp.selected_by,
        tcmp.selected_as_caption,
        tcmp.selected_as_vccaption,
        tc.name as country_name,
        tcp.name,tcp.uniqueid,
        tcp.bets,
        tcp.bowls,
        tcp.dob,
        tcp.position,
        (select sum(points) from tbl_cricket_match_players where player_unique_id=tcmp.player_unique_id AND match_unique_id IN ($match_ids) ) as total_points FROM  tbl_cricket_match_players tcmp LEFT JOIN tbl_cricket_players tcp ON (tcmp.player_unique_id=tcp.uniqueid)  LEFT JOIN tbl_countries tc ON(tcp.country_id=tc.id) WHERE tcmp.match_unique_id = ? AND tcmp.team_id IN ($team_ids) AND tcmp.status = 'A' AND tcmp.is_deleted='N' ORDER BY ISNULL(tcmp.playing_role) ASC ,
        FIELD(tcmp.playing_role, 'Wicketkeeper','Batsman','Allrounder','Bowler',' ','') ASC,
        tcp.id ASC,
        total_points DESC";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $match_id);
        //$query_res->bindParam(2, $team_id);
        $query_res->execute();
        $players=array();
        $i=0;
        while($array = $query_res->fetch(PDO::FETCH_ASSOC)){

          $players[$i]['team_id']=$array['team_id'];
          $players[$i]['credits']=$array['credits'];
          $players[$i]['position']=$array['playing_role'];
          $players[$i]['total_points']=$array['total_points'];
          $players[$i]['is_in_playing_squad']=$array['is_in_playing_squad'];
          $players[$i]['playing_squad_updated']=$playing_squad_updated;
          $players[$i]['player_id']=$array['uniqueid'];
          $players[$i]['name']=$array['name'];
          $players[$i]['bat_type'] =$array['bets'];
          $players[$i]['bowl_type'] =$array['bowls'];
          $players[$i]['country'] =$array['country_name'];
          $players[$i]['dob'] = (!empty($array['dob'] ) )?date_format( date_create( $array['dob'] ) ,"d-m-Y" ):"";
          $players[$i]['selected_by'] =$array['selected_by'];
          $players[$i]['selected_as_caption'] =$array['selected_as_caption'];
          $players[$i]['selected_as_vccaption'] =$array['selected_as_vccaption'];
          $players[$i]['image']=!empty($array['image']) ? PLAYER_IMAGE_THUMB_URL.$array['image'] : NO_IMG_URL_PLAYER;

          $i++;

         
        }
        $this->closeStatement($query_res);

        return $players;
        
    }

    public function getCustomerIdByMobileNo($phone, $country_mobile_code) {
          $query = "SELECT id FROM tbl_customers WHERE phone = ? AND country_mobile_code = ? AND is_deleted='N'";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $phone);
        $query_res->bindParam(2, $country_mobile_code);
        $query_res->execute();
        $array = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);
        return $array;
    }

     public function getCustomerIdByEmail($email) {
        $query = "SELECT id,phone,country_mobile_code FROM tbl_customers WHERE email = ? AND is_deleted='N'";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $email);
        $query_res->execute();
        $array = $query_res->fetch(PDO::FETCH_ASSOC);
        $this->closeStatement($query_res);
        return $array;
    }

    
    public function updateCustomerAppStatus($customer_id){


    	$time = time();
        $select_user_query = "UPDATE tbl_customers SET app_status_updated_at=? WHERE id=?";

        $select_user  = $this->conn->prepare($select_user_query);
        $select_user->bindParam(1,$time);
        $select_user->bindParam(2,$customer_id); 
        if ($select_user->execute()) {
            $this->closeStatement($select_user);

            return "1";
        } else {
            $this->closeStatement($select_user);

            return "0";
        }

    }

   public function getUpdatedProfileData($user_id) {

       $query = "SELECT tc.follower_count,tc.following_count,tc.post_count,tc.team_name,tc.team_change,
       tc.winning_wallet, tc.deposit_wallet, tc.bonus_wallet, tc.slug, tc.firstname, tc.lastname, 
       tc.email, tc.is_social, tc.social_type, tc.social_id, tc.image, tc.external_image, 
       tc.country_mobile_code, tc.phone, tc.referral_code, tc.is_phone_verified, 
       tc.is_email_verified ,tc.dob, tc.addressline1, tc.addressline2, 
       tc.pincode,tc.city as city_name, tcp.pain_number, tcp.name as pan_name, tcp.dob as pan_dob,
        tcp.status as pan_status, tcp.image as pan_image, tcp.state as pan_state,tcp.reason as pan_reason,
         IFNULL(tcp.id,0) as paincard_id, 
         tcbd.account_number as bank_account_number, tcbd.name as account_holder_name, 
         tcbd.ifsc as bank_ifsc, tcbd.status as bank_status, 
         tcbd.image as bank_image,tcbd.reason as bank_reason, 
         IFNULL(tcbd.id,0) as bankdetail_id, IFNULL(tbl_countries.id,0) as country_id, 
         tbl_countries.name as country_name, IFNULL(tbl_states.id,0) as state_id , tbl_states.name as state_name,
         (SELECT IFNULL(sum(amount),0) from tbl_withdraw_requests 
         where (status='P' OR status='H' OR status='RP') AND customer_id=?) as pending_wid_amount 
         FROM tbl_customers tc LEFT JOIN tbl_countries ON tbl_countries.id = tc.country 
         LEFT JOIN tbl_states ON tbl_states.id=tc.state 
         LEFT JOIN tbl_customer_paincard tcp ON tcp.id=tc.paincard_id 
         LEFT JOIN tbl_customer_bankdetail tcbd ON tcbd.id=tc.bankdetail_id WHERE tc.id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);

        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];


        $output = array();
        if ($query_res->execute()) {
            $profiledata = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);
            if(empty($profiledata)){
                return $output;
            }
            $output['slug'] = $this->base64_encode($profiledata['slug']);
            $output['id'] = $user_id;
            $output['firstname'] = $profiledata['firstname'];
            $output['lastname'] = $profiledata['lastname'];
            $output['team_name'] = $profiledata['team_name'];
            $output['team_change'] = $profiledata['team_change'];
            $output['email'] = $profiledata['email'];
            $output['is_social'] = $profiledata['is_social'];
            $output['social_type'] = $profiledata['social_type'];
            $output['social_id'] = $profiledata['social_id'];
            $output['country_mobile_code'] = $profiledata['country_mobile_code'];               
            $output['phone'] = $profiledata['phone']; 
            $output['follower_count'] = $profiledata['follower_count']; 
            $output['following_count'] = $profiledata['following_count']; 
            $output['post_count'] = $profiledata['post_count']; 
          ///  echo $profiledata['image'];
            $output['referral_code'] = $profiledata['referral_code'];
            $output['is_phone_verified'] = $profiledata['is_phone_verified'];  
            $output['is_email_verified'] = $profiledata['is_email_verified'];                     
            $output['image'] = (!empty($profiledata['image']) && $profiledata['image']!=null) ? CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : $no_imag_url;

            if(!empty($profiledata['external_image'])){
                $output['image']=$profiledata['external_image'];
            }

            $output['dob'] = $profiledata['dob'];
            $output['addressline1'] = $profiledata['addressline1'];    
            
            $output['addressline2'] = $profiledata['addressline2'];               
            $output['pincode'] = $profiledata['pincode'];  

            $output['country']=NULL;
            if(!empty($profiledata['country_id'])){
                $output['country'] = array('id'=>$profiledata['country_id'], 'name'=>$profiledata['country_name']);
            }
            
            $output['state']=NULL;
            if(!empty($profiledata['state_id'])){
                $output['state'] = array('id'=>$profiledata['state_id'], 'name'=>$profiledata['state_name']);
            }
            
            $output['pancard']=NULL;
            if(!empty($profiledata['paincard_id'])){
                $panDetail=array();
                $panDetail['id']=$profiledata['paincard_id'];
                $panDetail['number']=$profiledata['pain_number'];
                $panDetail['name']=$profiledata['pan_name'];
                $panDetail['dob']=$profiledata['pan_dob'];
                $panDetail['status']=$profiledata['pan_status'];
                $panDetail['reason']=$profiledata['pan_reason'];
                $panDetail['image']=!empty($profiledata['pan_image']) ? PANCARD_IMAGE_LARGE_URL.$profiledata['pan_image'] : NO_IMG_URL;
                $panstate=$profiledata['pan_state'];
                if(empty($panstate)){
                    $panDetail['state']=NULL;
                }else{
                    $panDetail['state']=$this->getStateDetail($panstate);
                }
                
                $output['pancard'] = $panDetail;
            }
            
            $output['bankdetail']=NULL;
            if(!empty($profiledata['bankdetail_id'])){
                $bankDetail=array();
                $bankDetail['id']=$profiledata['bankdetail_id'];
                $bankDetail['account_number']=$profiledata['bank_account_number'];
                $bankDetail['account_holder_name']=$profiledata['account_holder_name'];
                $bankDetail['ifsc']=$profiledata['bank_ifsc'];
                $bankDetail['status']=$profiledata['bank_status'];
                $bankDetail['reason']=$profiledata['bank_reason'];
                $bankDetail['image']=!empty($profiledata['bank_image']) ? BANK_IMAGE_LARGE_URL.$profiledata['bank_image'] : NO_IMG_URL;
                $output['bankdetail'] = $bankDetail;
            }

            $output['city']  = array('id'=>0, 'name'=>$profiledata['city_name']);

            $actual_winning_balance=$profiledata['winning_wallet']-$profiledata['pending_wid_amount'];
            if($actual_winning_balance<0){
                $actual_winning_balance=0;
            }


         $avtarQuery = 'Select IFNULL(SUM(amount),0) as total FROM  tbl_customer_wallet_histories where type="CUSTOMER_REFFER_COMMISSION" AND customer_id ="'.$user_id.'" Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        
        $actual_network_commission = $avtarData['total'];
			$output['wallet']  = array('winning_wallet'=>number_format($actual_winning_balance,2,'.','') ,
            'bonus_wallet'=>$profiledata['bonus_wallet'], 
            'deposit_wallet'=>$profiledata['deposit_wallet'], 
            'pending_wid_amount'=>$profiledata['pending_wid_amount'],'network_commission'=>$actual_network_commission,"current_balance"=>$actual_winning_balance+$profiledata['bonus_wallet']+$profiledata['deposit_wallet']);
			
			
// 			echo $actual_winning_balance;
// 			echo $profiledata['bonus_wallet'];
// 			echo $profiledata['deposit_wallet'];
			//$output['aws']=array('AWS_KEY'=>AWS_KEY, 'AWS_SECRET'=>AWS_SECRET, 'AWS_REGION'=>AWS_REGION, 'AWS_BUCKET'=>AWS_BUCKET, 'PANCARD_IMAGE_PATH'=>PANCARD_IMAGE_PATH, 'BANK_IMAGE_PATH'=>BANK_IMAGE_PATH);

           /// $output['aws']=array('AWS_KEY'=>AWS_KEY, 'AWS_SECRET'=>AWS_SECRET, 'AWS_REGION'=>AWS_REGION, 'AWS_BUCKET'=>AWS_BUCKET, 'PANCARD_IMAGE_PATH'=>PANCARD_IMAGE_PATH, 'BANK_IMAGE_PATH'=>BANK_IMAGE_PATH, 'CUSTOMER_IMAGE_PATH'=>CUSTOMER_IMAGE_PATH, 'CUSTOMERGALLERY_IMAGE_PATH'=>CUSTOMERGALLERY_IMAGE_PATH,'RAZORPAY_KEY'=>RAZORPAY_KEY);
			
			$output['notification_counter']=$this->get_notification_counter($user_id);
			$settingData=$this->get_setting_data();
			$MIN_WITHDRAWALS=empty($settingData['MIN_WITHDRAWALS'])?0:$settingData['MIN_WITHDRAWALS'];
			$MAX_WITHDRAWALS=empty($settingData['MAX_WITHDRAWALS'])?0:$settingData['MAX_WITHDRAWALS'];
			$CASH_BONUS_PERCENTAGES=empty($settingData['CASH_BONUS_PERCENTAGES'])?0:$settingData['CASH_BONUS_PERCENTAGES'];

            $taxData=$this->get_total_tax_percent();
            $totalTax=$taxData['total_tax'];

            $WINNING_BREAKUP_MESSAGE="Note: The actual prize money may be different than the prize money mentionaed above if there is a tie for any of the winning positions. Check FAQs for further details. As per government regulations, a tax of ".$totalTax."% will be deducted if an individual wins more than Rs. 10,000";

            $JOIN_CONTEST_MESSAGE="By joining this contest, you accept Rimms11 T&C and confirm that you are not a resident of Assam, Odisha, Telangana, Nagaland or Sikkim.";

            $PROFILE_UPDATE_MESSAGE="To play in Rimms11 pay-to-play contests, you need to be 18 years or above, and not a resident of Assam, Odisha, Telangana, Nagaland or Sikkim.";

			$output['settings']=array('WITHDRAW_AMOUNT_MIN'=>$MIN_WITHDRAWALS, 'WITHDRAW_AMOUNT_MAX'=>$MAX_WITHDRAWALS, 'CASH_BONUS_PERCENTAGES'=>$CASH_BONUS_PERCENTAGES,'WINNING_BREAKUP_MESSAGE'=>$WINNING_BREAKUP_MESSAGE,'JOIN_CONTEST_MESSAGE'=>$JOIN_CONTEST_MESSAGE,'PROFILE_UPDATE_MESSAGE'=>$PROFILE_UPDATE_MESSAGE);
			$output['history'] = $this->get_cricket_playing_history($user_id);
			
        }else{
			$this->sql_error($query_res);
		}
        return $output;
    }


    
    public function getUpdatedWalletData($user_id) {
       $query = "SELECT winning_wallet, deposit_wallet, bonus_wallet,(SELECT IFNULL(sum(amount),0) from tbl_withdraw_requests where (status='P' OR status='H' OR status='RP') AND customer_id=?) as pending_wid_amount FROM tbl_customers tc where tc.id=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);
        $output = array();
        if ($query_res->execute()) {
            $profiledata = $query_res->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($query_res);

            if(empty($profiledata)){
                return $output;
            }
            $actual_winning_balance=$profiledata['winning_wallet']-$profiledata['pending_wid_amount'];
            if($actual_winning_balance<0){
                $actual_winning_balance=0;
            }

            $output['wallet']  = array('winning_wallet'=>$actual_winning_balance, 'bonus_wallet'=>$profiledata['bonus_wallet'], 'deposit_wallet'=>$profiledata['deposit_wallet'], 'pending_wid_amount'=>$profiledata['pending_wid_amount']);
        }else{
            $this->sql_error($query_res);
        }
        return $output;
    }
    
    public function getMiniUpdatedProfileData($user_id) {

           $query = "SELECT  tc.firstname, tc.lastname, tc.email, tc.is_admin, tc.is_fake FROM tbl_customers tc  WHERE tc.id=?";
            $query_res = $this->conn->prepare($query);
            $query_res->bindParam(1, $user_id);

            $output = array();
            if ($query_res->execute()) {
                $profiledata = $query_res->fetch(PDO::FETCH_ASSOC);
                if(empty($profiledata)){
                    return $output;
                }

                $output['firstname'] = $profiledata['firstname'];
                $output['lastname'] = $profiledata['lastname'];
                $output['email'] = $profiledata['email'];
                $output['is_admin'] = $profiledata['is_admin'];
                $output['is_fake'] = $profiledata['is_fake'];

            }else{
                $this->sql_error($query_res);
            }
            return $output;
        }


    public function updateReferralToCustomer($my_referral_code, $used_referral_code, $used_referral_customer_id, $saved_customer_id,$my_team_name){
            $cash_bonus_data=$this->get_refer_cashbonus();

            $used_refferal_amount=0;
            $applier_reffrel_amount=$cash_bonus_data['NEW_REGISTRATION'];

            if($used_referral_customer_id>0){

                $used_refferal_amount=$cash_bonus_data['REFERRER'];
                $applier_reffrel_amount=$cash_bonus_data['REGISTER_WITH_REFERRAL_CODE_(applier)'];
            }

        $update_last_login_query = "UPDATE tbl_customers SET referral_code=?, used_referral_code=?, used_referral_user_id=?, team_name=?,used_refferal_amount=? WHERE id=?";
        $update_last_login  = $this->conn->prepare($update_last_login_query);
       
        $update_last_login->bindParam(1,$my_referral_code);
        $update_last_login->bindParam(2,$used_referral_code);
        $update_last_login->bindParam(3,$used_referral_customer_id);
        $update_last_login->bindParam(4,$my_team_name);
        $update_last_login->bindParam(5,$used_refferal_amount);
        $update_last_login->bindParam(6,$saved_customer_id);
        $update_last_login->execute();

        $this->closeStatement($update_last_login);

        if($applier_reffrel_amount>0){
            $applier_reffrel_amount=round($applier_reffrel_amount,2);

        $transaction_id="RECBWALL".time().$saved_customer_id;
        $description=$saved_customer_id." Received Register cash bonus amount ".$applier_reffrel_amount.".";
        $this->update_customer_wallet($saved_customer_id,0,"bonus_wallet",$applier_reffrel_amount,"CREDIT","REGISTER_CASH_BONUS",$transaction_id,$description,0,0,false,0,null,null);

                $notification_data=array();
                $notification_data['noti_type']='register_cash_bonus';
                $alert_message = "Woohoo! Got ".CURRENCY_SYMBOL.$applier_reffrel_amount." Cash Bonus.";
                $this->send_notification_and_save($notification_data,$saved_customer_id,$alert_message,true);

        }
    }

    
    public function saveCustomerDetailsToCustomerLogins($customer_id, $device_id, $device_type) {
        $flag = 0;  
        $current_time = time();                        
        $update_customer_logins_query = "INSERT INTO tbl_customer_logins SET customer_id = ?, ip_address=?, device_id = ?, device_type = ?, login_time=?, created=? ON DUPLICATE KEY UPDATE customer_id=?, ip_address=?, login_time=?, created=?";
        $update_customer_logins = $this->conn->prepare($update_customer_logins_query);
        $update_customer_logins->bindParam(1, $customer_id);
        $update_customer_logins->bindParam(2, $_SERVER['REMOTE_ADDR']);
        $update_customer_logins->bindParam(3, $device_id);       
        $update_customer_logins->bindParam(4, $device_type);
        $update_customer_logins->bindParam(5, $current_time);
        $update_customer_logins->bindParam(6, $current_time);        
        $update_customer_logins->bindParam(7, $customer_id);        
        $update_customer_logins->bindParam(8, $_SERVER['REMOTE_ADDR']);        
        $update_customer_logins->bindParam(9, $current_time);        
        $update_customer_logins->bindParam(10, $current_time);        
        if ($update_customer_logins->execute()) {
            $this->closeStatement($update_customer_logins);
                $flag = 1;
        }else{
            $this->sql_error($update_customer_logins);
        }
        return $flag;
    }
    
    public function saveCustomerDetailsToCustomerLogs($customer_id, $device_id, $device_type, $device_info, $app_info) {
        $flag = 0;  
        $current_time = time();                        
        $update_customer_logs_query = "INSERT INTO tbl_customer_logs SET customer_id = ?, ip_address=?, device_id = ?, device_type = ?, device_info=? ,app_info=?, login_time=?, created=?";
        $update_customer_logs = $this->conn->prepare($update_customer_logs_query);
        $update_customer_logs->bindParam(1, $customer_id);
        $update_customer_logs->bindParam(2, $_SERVER['REMOTE_ADDR']);
        $update_customer_logs->bindParam(3, $device_id);        
        $update_customer_logs->bindParam(4, $device_type);
        $update_customer_logs->bindParam(5, $device_info);
        $update_customer_logs->bindParam(6, $app_info);
        $update_customer_logs->bindParam(7, $current_time);
        $update_customer_logs->bindParam(8, $current_time);        
        if ($update_customer_logs->execute()) {
                $flag = 1;  
        }
        $this->closeStatement($update_customer_logs);
        return $flag;
    }


    public function saveCustomer($firstname,$email, $secure_password, $country_mobile_code, $phone){
        $current_time = time();
        $slug=$current_time;
        $save_user_query = "INSERT INTO tbl_customers SET slug=?, firstname=?, email=?, password=?, country_mobile_code=?, phone=?, is_phone_verified='Y', created=?";                
        $save_user  = $this->conn->prepare($save_user_query);       
        $save_user->bindParam(1,$slug);
        $save_user->bindParam(2,$firstname);
        $save_user->bindParam(3,$email);
        $save_user->bindParam(4,$secure_password);
        $save_user->bindParam(5,$country_mobile_code);
        $save_user->bindParam(6,$phone);
        $save_user->bindParam(7,$current_time);
       
        if (!$save_user->execute()) {
            $this->sql_error($save_user);

        }

        $new_customer_id=$this->conn->lastInsertId();
        $this->closeStatement($save_user);

        $slug=$current_time.$new_customer_id;

        $select_user_query = "UPDATE tbl_customers SET slug=? WHERE id=?";

        $select_user  = $this->conn->prepare($select_user_query);
        $select_user->bindParam(1,$slug);
        $select_user->bindParam(2,$new_customer_id); 
        if (!$select_user->execute()) {
           $this->sql_error($select_user);
        }

        $this->closeStatement($select_user);

        return $new_customer_id;
    }
    
    public function saveSocialCustomer($firstname,$lastname,$email, $secure_password, $country_mobile_code, $phone, $social_type,$social_id){
        $current_time = time();
        $slug=$current_time;
        $save_user_query = "INSERT INTO tbl_customers SET slug=?, firstname=?, lastname=?, email=?, password=?, country_mobile_code=?, phone=?, is_email_verified='Y', is_social='Y', social_type=?, created=?, social_id=?";                
        $save_user  = $this->conn->prepare($save_user_query);       
        $save_user->bindParam(1,$slug);
        $save_user->bindParam(2,$firstname);
        $save_user->bindParam(3,$lastname);
        $save_user->bindParam(4,$email);
        $save_user->bindParam(5,$secure_password);
        $save_user->bindParam(6,$country_mobile_code);
        $save_user->bindParam(7,$phone);
        $save_user->bindParam(8,$social_type);
        $save_user->bindParam(9,$current_time);
        $save_user->bindParam(10,$social_id);
       
        if ($save_user->execute()) {

            $new_customer_id=$this->conn->lastInsertId();
            $this->closeStatement($save_user);

            $slug=$current_time.$new_customer_id;

            $select_user_query = "UPDATE tbl_customers SET slug=? WHERE id=?";

            $select_user  = $this->conn->prepare($select_user_query);
            $select_user->bindParam(1,$slug);
            $select_user->bindParam(2,$new_customer_id); 
            if ($select_user->execute()) {
                $this->closeStatement($select_user);
               return $new_customer_id;
            }else{
                $this->closeStatement($select_user);
                return 0;
            }

        }else{
            $this->closeStatement($save_user);
            return 0;
        }
    }


    public function getUsedReferralcustomerIdAndAmount($used_referral_code){
        $customer_id = '';
        if(empty($used_referral_code)){
            return $customer_id;
        }
        $query  = "SELECT id FROM tbl_customers WHERE referral_code=? AND is_deleted='N'";
        $query_res  = $this->conn->prepare($query);
        $query_res->bindParam(1,$used_referral_code);
        $query_res->execute();
        $num_rows =$query_res->rowCount();
        
        if ($num_rows > 0) {
            $array = $query_res->fetch();
            $this->closeStatement($query_res);

            $customer_id = $array['id'];
        }else{
            $this->closeStatement($query_res);
        }
        return $customer_id;
    }

    public function checkEmail($jsonstring){
        if ($jsonstring != '') {
            $json_array = json_decode($jsonstring, true);            
            return (isset($json_array['email']) && !empty($json_array['email'])) ? $json_array['email'] : '';
        }
    }  


     public function checkReferral($jsonstring){
        if ($jsonstring != '') {
            $json_array = json_decode($jsonstring, true);            
            return (isset($json_array['referral_code']) && !empty($json_array['referral_code'])) ? $json_array['referral_code'] : '';
        }
    }

    public function base64_encode($slug){
        $new_slug=base64_encode($slug);
        $new_slug=$new_slug."////AA";
        return $new_slug;
    }


    public function base64_decode($slug){

        $new_slug=explode('////',$slug);
        $decoded_slug=base64_decode($new_slug[0]);
        return $decoded_slug;
        
    }

   
    
    public function getFileExtension($file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        return $extension;
    }

    /**
    * [resize Image resize function]
    * @param  [string] $image_name [image name]
    * @param  [int] $size          [size]
    * @param  [path] $folder_name  [Source Image folder path]
    * @param  [path] $thumbnail    [Destination Image folder path]
    * @return [image]              [image]
    */

    function resize($image_name, $size, $folder_name, $thumbnail) {
        $file_extension = $this->getFileExtension($image_name);
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

    public function moveFiles($filename,$new_file_key,$large_path,$thumb_path,$image_prefix=''){ 
        if (isset($filename['name']) && $filename['name'] != '') {
            $image_prefix = !empty($image_prefix) ? $image_prefix.'_' : ''; 
                $ex = explode(".", $filename['name']);
                $extentation_image = strtolower(end($ex));
                $signature_taxi_image = $image_prefix.time() . $new_file_key.'.' . $extentation_image;
                if (move_uploaded_file($filename['tmp_name'], $large_path . $signature_taxi_image)) {
                    if (in_array($extentation_image, $this->image_extensions)) {
                        $this->resize($signature_taxi_image, 150, $large_path, $thumb_path);
                    }
                    return $signature_taxi_image;    
                }                
        } 
    }

    public function validateImage($filename, $doc_type){
        if (!empty($filename)) {
            $filename   =   $filename['name'];
            $ex = explode(".", $filename);
            $extentation_taxi_image = strtolower(end($ex));
            if (!in_array($extentation_taxi_image, $this->image_extensions)) {
                $response["code"] = UPLOAD_ERROR_IMG;
                $response["error"] = true;
                $response["message"] = "Please upload a " . implode("or ", $this->image_extensions) . " file";
                return $response;
            }
        } else {
            $response["code"] = UPLOAD_ERROR_EMPTY;
            $response["error"] = true;
            $response["message"] = "Please upload a photo of your ".$doc_type;
            return $response;
        }
    }

   

    
    
    public function sendTemplatesInMail($mailTitle, $toName, $toEmail,$data=array()){
        $select_mail_template_query = "SELECT subject, content FROM tbl_templates WHERE type='E' AND title=? AND status='A'";
        $select_mail_template = $this->conn->prepare($select_mail_template_query);
        $select_mail_template->bindParam(1, $mailTitle);
        $select_mail_template->execute();
        $num_rows = $select_mail_template->rowCount();
        if ($num_rows > 0) {
            $mailTemplate = $select_mail_template->fetch(PDO::FETCH_ASSOC);
            $this->closeStatement($select_mail_template);

            $message= str_replace("{CUSTOMER_NAME}", $toName, $mailTemplate['content']);

            if(isset($data['link'])){
                 $message= str_replace("{LINK}", $data['link'], $message); 

            }

            if(isset($data['fee'])){
                 $message= str_replace("{FEE}", $data['fee'], $message); 

            }
            if(isset($data['match_date'])){
                 $message= str_replace("{MATCH_DATE}", $data['match_date'], $message); 

            }
            if(isset($data['match_name'])){
                 $message= str_replace("{MATCH_NAME}", $data['match_name'], $message); 

            }

            if(isset($data['otp'])){
                 $message= str_replace("{OTP}", $data['otp'], $message);

            }

            if(isset($data['message'])){
                 $message= str_replace("{MESSAGE}", $data['message'], $message);

            }


            if(!empty($toEmail)){
            // $this->insert_email($mailTemplate['subject'],$message,$toEmail,$toName,SMTP_FROM_NAME,SMTP_FROM_EMAIL);
            }

            $this->sendSMTPMail($mailTemplate['subject'],$message,$toEmail,$toName,SMTP_FROM_NAME,SMTP_FROM_EMAIL);
        }else{
                     $this->closeStatement($select_mail_template);
        }
    }
    
    
     public function insert_email($subject,$message,$toemail,$toname,$from_name,$from_email){
        $time=time();
        $query = "INSERT INTO tbl_email_cron SET subject= ?, message= ?, toemail= ?, toname= ?, from_name=?, from_email=?, createdat=?, updatedat=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $subject);
        $query_res->bindParam(2, $message);
        $query_res->bindParam(3, $toemail);
        $query_res->bindParam(4, $toname);
        $query_res->bindParam(5, $from_name);
        $query_res->bindParam(6, $from_email);
        $query_res->bindParam(7, $time);
        $query_res->bindParam(8, $time);
        $query_res->execute();


    }



    public function removeMobileFromTemp($mobileno, $mobile_code='') {
        $query = "DELETE FROM tbl_tempcustomers WHERE mobileno = ? AND country_mobile_code = ?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $mobileno);
        $query_res->bindParam(2, $mobile_code);
        $query_res->execute();
        $this->closeStatement($query_res);

    }

    public function removeEmailFromTemp($email) {
        $query = "DELETE FROM tbl_tempcustomers WHERE mobileno = ?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $email);
        $query_res->execute();
        $this->closeStatement($query_res);
    }

    private function sql_error($obj){
        echo '<pre>';
        print_r($obj->errorInfo());
        $this->closeStatement($obj);
        echo '</pre>';
        die;
    }

    public function sendotp($mobileno, $type, $mobile_code, $jsonstring) {
        $this->removeMobileFromTemp($mobileno, $mobile_code);
        $otp = rand(1000, 9999);
        // $otp ='5555';
        if ($type=='V') {
            $template="verification";
        }else if ($type=='L') {
            $template="verification";
        }else if ($type=='F') {
            $template="verification";
        }else if ($type=='SP') {
            $template="verification";
        }
        $query = "INSERT INTO tbl_tempcustomers SET country_mobile_code= ?, mobileno= ?, otp= ?, type= ?, customer_data=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $mobile_code);
        $query_res->bindParam(2, $mobileno);
        $query_res->bindParam(3, $otp);
        $query_res->bindParam(4, $type);
        $query_res->bindParam(5, $jsonstring);
        if ($query_res->execute()) {
            $this->closeStatement($query_res);
            $this->sendTemplatesInSMS($template, $otp, $mobileno, $mobile_code);
            return $otp;
        } else {
            $this->closeStatement($query_res);
            return 0;
        }     
    }

    public function sendotpEmail($email, $type, $jsonstring) {
        $this->removeEmailFromTemp($email);
        $otp = rand(1000, 9999);
        // $otp ='5555';
        $template='';
        if ($type=='FE') {
            $template="forgot_password";
        }

        $query = "INSERT INTO tbl_tempcustomers SET country_mobile_code= '', mobileno= ?, otp= ?, type= ?, customer_data=?";
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $email);
        $query_res->bindParam(2, $otp);
        $query_res->bindParam(3, $type);
        $query_res->bindParam(4, $jsonstring);
        if ($query_res->execute()) {
            $this->closeStatement($query_res);
            $data=array();
            $data['otp']=$otp;
            $full_name='';
            $this->sendTemplatesInMail($template, trim($full_name), $email,$data);
            return $otp;
        } else {
            $this->sql_error($query_res);
        }
    }



    private function isEmailExists($email, $id = null) {
        $is_exist_user_query = "SELECT id FROM tbl_customers WHERE email = ? AND is_deleted='N'";
        if ($id != NULL)
            $is_exist_user_query.= " AND id != ?";
        $save_user = $this->conn->prepare($is_exist_user_query);
        $save_user->bindParam(1, $email);
        if ($id != NULL)
            $save_user->bindParam(2, $id);
        $save_user->execute();
        $num_rows = $save_user->rowCount();
        if(empty($email)){
            $this->closeStatement($save_user);
             return "0";
        }else{
             $this->closeStatement($save_user);
             return $num_rows > 0;
        }
       
    }


    private function isTeamNameExists($team_name, $id = null) {
        $is_exist_user_query = "SELECT id FROM tbl_customers WHERE team_name = ?";
        if ($id != NULL)
            $is_exist_user_query.= " AND id != ?";
        $save_user = $this->conn->prepare($is_exist_user_query);
        $save_user->bindParam(1, $team_name);
        if ($id != NULL)
            $save_user->bindParam(2, $id);
        $save_user->execute();
        $num_rows = $save_user->rowCount();
         $this->closeStatement($save_user);

        return $num_rows > 0;


    }

    private function isCustomerTeamNameExistsInCustomerTeams($customer_team_name,$match_unique_id, $id = null) {
        $is_exist_user_query = "SELECT id FROM tbl_cricket_customer_teams WHERE match_unique_id = ? AND customer_team_name=?";
        if ($id != NULL)
            $is_exist_user_query.= " AND customer_id != ?";
        $save_user = $this->conn->prepare($is_exist_user_query);
        $save_user->bindParam(1, $match_unique_id);
        $save_user->bindParam(2, $customer_team_name);
        if ($id != NULL)
            $save_user->bindParam(3, $id);
        $save_user->execute();
        $num_rows = $save_user->rowCount();
        $this->closeStatement($save_user);

        return $num_rows > 0;


    }

    private function isMoreNameExistsInCustomerTeams($customer_team_name,$match_unique_id,$more_name, $id = null) {
        $is_exist_user_query = "SELECT id FROM tbl_cricket_customer_teams WHERE match_unique_id = ? AND customer_team_name=? AND more_name=?";
        if ($id != NULL)
            $is_exist_user_query.= " AND customer_id = ?";
        $save_user = $this->conn->prepare($is_exist_user_query);
        $save_user->bindParam(1, $match_unique_id);
        $save_user->bindParam(2, $customer_team_name);
        $save_user->bindParam(3, $more_name);
        if ($id != NULL)
            $save_user->bindParam(4, $id);
        $save_user->execute();
        $num_rows = $save_user->rowCount();
        $this->closeStatement($save_user);

        return $num_rows > 0;

       
    }


    

    private function isPhoneExists($phone, $id = null, $mobile_code) {
        $is_exist_user_query = "SELECT id FROM tbl_customers WHERE phone =? AND country_mobile_code=? AND is_deleted='N'";
        if ($id != NULL)
            $is_exist_user_query.= " AND id != ?";
        $save_user = $this->conn->prepare($is_exist_user_query);
        $save_user->bindParam(1, $phone);
        $save_user->bindParam(2, $mobile_code);
        if ($id != NULL) {
            if (empty($mobile_code)) {
                $save_user->bindParam(2, $id);
            } else {
                $save_user->bindParam(3, $id);
            }
        }
        $save_user->execute();
        $num_rows = $save_user->rowCount();
        $this->closeStatement($save_user);

        return $num_rows > 0;
    }

     public function validateUser($slug) {

       $slugg=$this->base64_decode($slug);       
       $sel_user_query = "SELECT id, status, is_deleted FROM tbl_customers WHERE slug=?";
       $sel_user  = $this->conn->prepare($sel_user_query);
       $sel_user->bindParam(1,$slugg);
      
       $sel_user->execute();
       $userdetail = $sel_user->fetch(PDO::FETCH_ASSOC);
       if ($sel_user->rowCount() > 0) {
            $this->closeStatement($sel_user);
            return $userdetail;
       }
       $this->closeStatement($sel_user);
    }

    public function send_sms($text='',$to='',$issos='NO', $country_code=91){
        
        include_once('../../../lib/textlocal.class.php');
       // $country_code = str_replace("+", "", $country_code);
        $to = $country_code.$to;
        $text= strip_tags($text);
        $textlocal = new Textlocal(SMS_USERNAME, SMS_PASSWORD);
        $numbers = array($to);
        $sender = SMS_SENDER_NAME;
        try {
            $result = $textlocal->sendSms($numbers, $text, $sender);
           /* print_r($result);
            die;*/
          
            
        } catch (Exception $e) {
        	/*print_r($e);
            die;*/
            return true;
        }
        return true;
    }
    
    
    
     public function includeSMTPMailerLib() {

            $filesArr=get_required_files();
            $searchString=PHPMAILER_LIB_PATH;

            if(!in_array($searchString, $filesArr)) {
                // echo PHPMAILER_LIB_PATH; die;
                require PHPMAILER_LIB_PATH;
            }
        }

    
    public function sendSMTPMail($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
        if(!empty(SMTP_SERVER)){

                    $this->includeSMTPMailerLib();
                    $mail = new PHPMailer(true);
                           // Passing `true` enables exceptions
                        try {


                              ///  $mail->SMTPDebug = 2;                       // Enable verbose debug output
                          ////      $mail->isSMTP();                            // Set mailer to use SMTP
                               /// $mail->Host = SMTP_SERVER;                  // Specify main and backup SMTP servers
                              ////  $mail->SMTPAuth = true;                     // Enable SMTP authentication
                              ///  $mail->Username = SMTP_USERNAME;            // SMTP username
                              ////  $mail->Password = SMTP_PASSWORD;            // SMTP password
                             ////   $mail->SMTPSecure = SMTP_SECURE;            // Enable TLS encryption, `ssl` also accepted
                            ////    $mail->Port = SMTP_PORT;   
                               //$mail->SMTPDebug  = 1;              // TCP port to connect to

                                //Recipients
                                $mail->setFrom($sender_from_email, $sender_from_name);
                                $mail->addAddress($receiver_email, $receiver_name);     // Add a recipient
                                
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

                                $r=$mail->send();

                        } catch (Exception $e) {

                            //print_r($e);
                             return $e;
                        }

        }
        return "SUCCESS";
    }


    
    public function convertTimeToUserOffset($currenttime, $utc_offset){
        $server_utc_offset = date('Z');
        $utc_offset = $utc_offset*60;
        $new_offset = $utc_offset-$server_utc_offset;
        $currenttime = $currenttime+$new_offset;
        return $currenttime;
    }

    

    public function format_number($number){
        return str_replace(',', '', number_format($number, 2));
    }

   
    public function uploadOnAWS($params, $source){
            $mime_types=unserialize(MIME_TYPES);
            $ext = strtolower(pathinfo($params['file_name'], PATHINFO_EXTENSION));
            // Include the SDK using the Composer autoloader
            //require '../../../lib/s3/vendor/autoload.php';
            //echo AWS_LIB;die;
            require_once AWS_LIB;
            $result = array();
            // Set Amazon s3 credentials
            $client = S3Client::factory(
                        array(
                            'credentials'=>array(
                                'key'    => AWS_KEY,
                                'secret' => AWS_SECRET
                            ),
                            'region' => AWS_REGION,
                            'version' => "latest"
                        )
                    );
            $bucket = AWS_BUCKET;
            $key = $params['upload_path'].$params['file_name'];
            try {
                    $res = $client->putObject(array(
                        'Bucket'=>$bucket,
                        'Key' => $key,
                        'SourceFile' => $source,
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'ACL'    => 'public-read',
                        'ContentType'    => $mime_types[$ext]
                    ));
                $data=$res->toArray();
                $object_url=$data['ObjectURL'];
                $result = array("status"=>"success", "data"=>array("full_path"=>$data['ObjectURL'], "file_name"=>$params['file_name']));
                //return $object_url;
            } catch (S3Exception $e) {
              // Catch an S3 specific exception.
                $filename = ROOT_DIRECTORY.'s3log.txt';
                $text1 = print_r($e, true);
                
                $myfile = fopen($filename, "a") or die("Unable to open file!");
                $txt = "<!---------------------[" . date("Y/m/d h:i:s") . "] ERROR ----------------------->" . PHP_EOL . $text1 . '<!------------------------------End-------------------------------->'. PHP_EOL;
                fwrite($myfile, $txt);
                fclose($myfile);
                //echo $e->getMessage();
                $result = array("status"=>"error", "data"=>"Something went wrong while uploading file! Please try again later!");
            }
            return $result;

    }
############################################ CALLED FUNCTION END ##############################################
}
