<?php

  use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

class Common_model extends CI_Model {

    function __construct() {
        parent::__construct();

      
       
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

    
    function getBranchId($lat, $lng){

        $radius=BRANCH_SEARCH_AREA;
        $sql = $this->db->query(" SELECT * , (3956 * 2 * ASIN(SQRT( POWER(SIN((".$lat." - `latitude`) *  pi()/180 / 2), 2) +COS( 26.9286704 * pi()/180) * COS(`latitude` * pi()/180) * POWER(SIN((".$lng." - `longitude`) * pi()/180 / 2), 2) ))) as distance  
            from   tbl_branches
            having  distance <= ".$radius." 
            order by distance");
               // echo $this->db->last_query(); die;       
            if($sql->num_rows()>0){
               
                return $sql->row()->id;
            }
    }

 /* function getBId($lat, $lng){
            
        $radius=BRANCH_SEARCH_AREA;
        $sql = $this->db->query(" SELECT * , (3956 * 2 * ASIN(SQRT( POWER(SIN((".$lat." - `latitude`) *  pi()/180 / 2), 2) +COS( 26.9286704 * pi()/180) * COS(`latitude` * pi()/180) * POWER(SIN((".$lng." - `longitude`) * pi()/180 / 2), 2) ))) as distance  
            from   tbl_branches
            having  distance <= ".$radius." 
            order by distance");
                 
            if($sql->num_rows()>0){
                return $sql->row()->id;
                
            }
    }*/


    function select_db($type = 'array', $table = null, $fields = '*', $cond = array(), $joins = array(), $order = NULL, $limit = NULL, $groupBy = NULL) {
        $this->db->select($fields);
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $this->db->join($join['table'], $join['cond'], $join['type']);
            }
        }

        if (!empty($cond)) {
            if (is_array($cond) && count($cond) > 0) {
                foreach ($cond as $condKey => $condValue) {
                    $this->db->where($condKey, $condValue);
                }
            } else {
                $this->db->where($cond);
            }
        }
        if (!empty($order)) {
            $explodeOrder = explode(',', $order);
            $this->db->order_by($explodeOrder[0], $explodeOrder[1]);
        }
        if (!empty($groupBy)) {
            $this->db->group_by($groupBy);
        }
        if (!empty($limit)) {
            $explodeLimit = explode(',', $limit);
            $this->db->limit($explodeLimit[1], $explodeLimit[0]);
        }

        $query = $this->db->get($table); //echo $this->db->last_query(); die;
        if ($query->num_rows() > 0) {
            if ($type == 'countRow') {
                $data = $query->num_rows();
            } elseif ($type == 'row') {
                $data = $query->row();
            } else {
                $data = $query->result_array();
            }
            return $data;
        } else {
            return;
        }
        
    }

    function update_db($table = null, $data = array(), $cond = array()) {
        $this->load->database();
        $this->db->where($cond);
        $this->db->update($table, $data);
    }

    function insert_db($table = null, $data = array()) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    function delete_db($table, $cond) {
        $this->db->delete($table, $cond);
        return true;
    }

    function singleFeaturedByBid($branch_id){ 
         /*$categoryQuery = "select tc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc 
            JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' 
            AND tbc.branch_id='".$branch_id."' AND tc.is_catering='Y'";

            $query = $this->db->query($categoryQuery);
                if($query->num_rows()>0) {
                    $catering_list =$query->result_array();
*/
                    $categoryQuery = "select ti.id, ti.branch_id, ti.category_id,ti.name, ti.description,tii.image from tbl_items ti 
                    LEFT JOIN tbl_item_images tii ON tii.item_id= ti.id
                    where   ti.status='A' AND ti.is_featured='Y' AND ti.branch_id='".$branch_id."' AND tii.status='A'";
                    $query = $this->db->query($categoryQuery);


                    if($query->num_rows()>0) {
                        return $query->result_array();
                    }

               /* }*/
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

    public function isReferCodeDuplicate($token) {
        $customerQuery = "SELECT id from tbl_customers WHERE referral_code='".$token."'";

         $query = $this->db->query($customerQuery);
        
        // print_r($customer_data->errorInfo());
        if($query->num_rows()>0) {
            return true;
        } else {
            return false;
        }
    }

    public function includeSMTPMailerLib() {
        $filesArr=get_required_files();
        $searchString=PHPMAILER_LIB_PATH;
        if(!in_array($searchString, $filesArr)) {
            require PHPMAILER_LIB_PATH;
        }
    }

        public function sendSMTPMail($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
               $this->includeSMTPMailerLib();
                
                
                
                $mail = new PHPMailer(true);                    // Passing `true` enables exceptions
                try {
                    $mail->SMTPDebug = 0;                       // Enable verbose debug output
                    $mail->isSMTP();                            // Set mailer to use SMTP
                    $mail->Host = SMTP_SERVER;                  // Specify main and backup SMTP servers
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
                    /*echo "<pre>";
                    print_r($e);
                    die;*/
                    return false;
                }
            }

     function sendTemplatesInMail($mailTitle, $toName, $toEmail, $invoice_id=''){

         $templateQuery = "select subject, content from tbl_templates where type='E' AND title='".$mailTitle."' AND status='A'"; 
        $query = $this->db->query($templateQuery);
       
       
        if($query->num_rows()>0) {
            $mailTemplate =  $query->result_array();
       
            $subject= $mailTemplate[0]['subject'];
            $message= str_replace("{LINK}", $invoice_id,$mailTemplate[0]['content']);
            $this->sendSMTPMail($subject, $message, $toEmail, $toName, SMTP_FROM_NAME, SMTP_FROM_EMAIL);
        }
    }


    function sendTemplatesInMail_order($mailTitle, $toName, $toEmail, $invoice_id=''){

         $templateQuery = "select subject, content from tbl_templates where type='E' AND title='".$mailTitle."' AND status='A'"; 
        $query = $this->db->query($templateQuery);
       
       
        if($query->num_rows()>0) {
            $mailTemplate =  $query->result_array();
       
            $subject= $mailTemplate[0]['subject'];
            $message= str_replace("{CUSTOMER}", $invoice_id['customer'],$mailTemplate[0]['content']);
            $message= str_replace("{ORDER_NUMBER}", $invoice_id['order_number'],$message);
            $message= str_replace("{AMOUNT}", $invoice_id['amount'],$message);
            $message= str_replace("{link}", $invoice_id['link'],$message);
            $message= str_replace("{DELEVERY_TYPE}", $invoice_id['delevery_type'],$message);

            $this->sendSMTPMail($subject, $message, $toEmail, $toName, SMTP_FROM_NAME, SMTP_FROM_EMAIL);
        }
    }
          
    function sendTemplatesInMail_eventBook($mailTitle, $toName, $toEmail, $invoice_id='')
    {
         $templateQuery = "select subject, content from tbl_templates where type='E' AND title='".$mailTitle."' AND status='A'"; 
        $query = $this->db->query($templateQuery);
       
       
        if($query->num_rows()>0) {
            $mailTemplate =  $query->result_array();
       
            $subject= $mailTemplate[0]['subject'];
            $message= str_replace("{NAME}", $invoice_id['name'],$mailTemplate[0]['content']);
            $message= str_replace("{MOBILE}", $invoice_id['mobile'],$message);
            $message= str_replace("{ADDRESS}", $invoice_id['address'],$message);
            $message= str_replace("{OCCASION}", $invoice_id['occasion'],$message);
            $message= str_replace("{EVENT}", $invoice_id['event'],$message);

            $this->sendSMTPMail($subject, $message, $toEmail, $toName, SMTP_FROM_NAME, SMTP_FROM_EMAIL);
        }
    }


    function removeMobileFromTemp($mobile, $mobile_code='') {
        $userQuery = "Delete FROM tbl_temp_customers WHERE mobile ='".$mobile."' AND mobile_code = '".$mobile_code."'";
        $query = $this->db->query($userQuery);
        
    }


    function sendotp($mobile, $type, $mobile_code) {
        $this->removeMobileFromTemp($mobile, $mobile_code);
        $otp = rand(1000, 9999);
        if ($type=='V') {
            $template="customer_verification";
        }
        if ($type=='F') {
            $template="customer_forgot_password";
        }
        $addedat=time();
        $insertQuery = "INSERT INTO tbl_temp_customers SET mobile_code='".$mobile_code."', mobile='".$mobile."', otp='".$otp."', type='".$type."', created='".$addedat."'";

        $query = $this->db->query($insertQuery);
        
        //print_r($user_insert->errorInfo());
        if($query) {
            $this->sendTemplatesInSMS($template, $otp, $mobile, $mobile_code, '');
            return $otp;
        } else {
            return 0;
        }     
    }


     function sendTemplatesInSMS($templateTitle, $otp, $mobile, $mobile_code, $orderid='') {
        $templateQuery = "select content from tbl_templates where type='S' AND title='".$templateTitle."' AND status='A' AND for_user='C'";
        $query = $this->db->query($templateQuery);
       
       
        if($query->num_rows()>0) {
            $mailTemplate =  $query->result_array();

            

           
            $message= str_replace("{ORDER_ID}", $orderid, (str_replace("{OTP}", $otp, $mailTemplate[0]['content'])));
            //echo $message;
            $this->send_sms($message, $mobile, $mobile_code);
        }
    }


   


    public function send_sms($text='', $to='', $country_code='+91') {
        //$this->includeGlobalSiteSetting();
        $this->includeTwilioLib();
        
        $to = $country_code.$to;
        $sender = GLOBAL_TWILIO_NUMBER;
        $sid = GLOBAL_TWILIO_SID;
        $token = GLOBAL_TWILIO_TOKEN;

        $client = new Twilio\Rest\Client($sid, $token);
        try {
            @$resp = $client->messages->create($to, array( 'from' => "$sender", 'body' => "$text" ));
           // print_r($resp);die;
        }  catch(Twilio\Exceptions\RestException $e) {
           // print_r($e); die;
        }
    }

     public function includeTwilioLib() {
        $filesArr=get_required_files();
        $searchString=TWILIO_LIB_PATH;
        if(!in_array($searchString, $filesArr)) {
            require TWILIO_LIB_PATH;
        }
    }


    function getFeaturedByBid($branch_id){ 
        
        $customer_id = null;
        if(isset($this->session->userdata['user_id'])){
             $customer_id = $this->session->userdata['user_id'];
        }

        
        $featuredQuery = "select ti.id,ti.branch_id,tc.id as catid, tc.name as catname, tc.description as catdesc, tc.logo as catimage, 
        ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, (select group_concat(image SEPARATOR '-++-') 
        from tbl_item_images tii where tii.item_id=ti.id AND tii.status='A' AND tii.isdefault='Y') as images, 
        (select group_concat(concat(name, '--', price) SEPARATOR '-++-') from tbl_item_prices tip where tip.item_id=ti.id 
        AND tip.status='A' AND tip.isdefault='Y') as prices, if((select id from tbl_favourite_items tfi where tfi.customer_id='".$customer_id."' 
        AND item_id=ti.id) >0, 'Y', 'N') as is_fav from tbl_items ti JOIN tbl_categories tc ON tc.id=ti.category_id 
        where ti.branch_id='".$branch_id."' AND ti.is_featured='Y' AND ti.status='A' order by ti.id desc";

        $query = $this->db->query($featuredQuery);
         
         if($query->num_rows()>0) {
            return $query->result_array();
         }

    }

        function getcomboitemsByBid($branch_id){
            
            $categoryQuery = "select tc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc 
            JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' 
            AND tbc.branch_id='".$branch_id."' AND tc.is_catering='Y'";

            $query = $this->db->query($categoryQuery);
                if($query->num_rows()>0) {
                    $catering_list =$query->result_array();

                    $categoryQuery = "select ti.id, ti.branch_id, ti.category_id,ti.name, ti.description,tii.image,tip.price from tbl_items ti 
                    LEFT JOIN tbl_item_prices tip ON tip.item_id=ti.id LEFT JOIN tbl_item_images tii ON tii.item_id= ti.id
                    where ti.category_id='".$catering_list[0]['id']."' AND ti.status='A' AND ti.branch_id='".$branch_id."' AND tii.status='A' limit 6";
                    $query = $this->db->query($categoryQuery);


                    if($query->num_rows()>0) {
                        return $query->result_array();
                    }

                }
                //echo $this->db->last_query();
            
        }

        function getmenuitemsByBid($branch_id){

            $categoryQuery = "select tbc.category_id as b_cat_id , tbc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc
             JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' AND tbc.branch_id='".$branch_id."' 
             AND tc.is_catering='N'";
          
            $query = $this->db->query($categoryQuery);
           
          
                if($query->num_rows()>0) {
                   return $query->result_array();
                }
        }


        function getcategoryid($branch_id){

            $categoryQuery = "select tbc.category_id as b_cat_id , tbc.id, tc.name, tc.description, tc.logo from tbl_branch_categories tbc
             JOIN tbl_categories tc ON tc.id=tbc.category_id where tc.status='A' AND tbc.status='A' AND tbc.branch_id='".$branch_id."' 
             AND tc.is_catering='N''";
          
            $query = $this->db->query($categoryQuery);
           
          
                if($query->num_rows()>0) {
                   return $query->result_array();
                }
        }

         function getSinglecatid($cat_id){

            $categoryQuery = "select * from tbl_categories where id ='".$cat_id."' 
             AND is_catering='N' AND status='A' ";
          
            $query = $this->db->query($categoryQuery);
           
          
                if($query->num_rows()>0) {
                   return $query->row();
                }
        }

        function item_detail($item_id,$customer_id=''){

            $menuUqery = "select ti.id,ti.branch_id as item_branch_id, ti.category_id, ti.name, ti.avg_rating, ti.is_nonveg, ti.is_new, ti.description, ti.is_featured,
             (select group_concat(concat(image,'--',isdefault) SEPARATOR '-++-') from tbl_item_images tii where tii.item_id=ti.id 
                AND tii.status='A') as images, (select group_concat(concat(name, '--', price, '--', isdefault) SEPARATOR '-++-') 
                from tbl_item_prices tip where tip.item_id=ti.id AND tip.status='A') as prices, if((select id from tbl_favourite_items
                 tfi where tfi.customer_id='".$customer_id."' AND item_id=ti.id) >0, 'Y', 'N') as is_fav from tbl_items ti
                  where ti.status='A' AND ti.id='".$item_id."'";

                $query = $this->db->query($menuUqery);
                    //echo $this->db->last_query(); die;
          
                if($query->num_rows()>0) {
                   return $query->row();
                }
        }

        function getItemExtras($itemid){

          $extraQuery = "select tie.id, te.name, te.image, te.description, tie.price from tbl_item_extras tie JOIN tbl_extras te ON te.id=tie.extra_id WHERE tie.item_id='".$itemid."' AND tie.status='A' AND te.status='A' order by tie.priority asc";
            $query = $this->db->query($extraQuery);
            //echo $this->db->last_query(); die;
            if($query->num_rows()>0) {
               return $query->result_array();
            }
        }

        function item_attributes($itemid){
            
            $attb="select tia.id, ta.name, ta.description, ta.type from tbl_item_attributes tia JOIN tbl_attributes ta ON ta.id=tia.attribute_id where tia.status='A' and ta.status='A' and tia.item_id='".$itemid."' order by priority asc";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->result_array();
            }
        }


   
        function getAttributeOptions($item_attid,$isDefault=''){

            $optionQuery="select tao.id, tao.name, tiao.price, tiao.default_selected 
            FROM tbl_item_attribute_options tiao JOIN tbl_attribute_options tao ON tao.id=tiao.attribute_option_id 
            WHERE tiao.item_attribute_id='".$item_attid."' AND tiao.status='A' AND tao.status='A'";

            /*if($isDefault!=''){
                $optionQuery .= " AND default_selected= 'Y' ";    
            }*/
            
            $optionQuery .= "order by tiao.priority asc";

            //echo $optionQuery; die;

            $query = $this->db->query($optionQuery);
            
             if($query->num_rows()>0) {
               return $query->result_array();
            }
        }


        function mainBannerImages(){

            $images="select * FROM `tbl_banner_images` where start_date <= '".now()."' and end_date >= '".now()."'
             and plateform='W' and status='A' order by priority asc";
             
            $query = $this->db->query($images);
            
             if($query->num_rows()>0) {
               return $query->result_array();
            }
        }
       


        function companyAddresses(){

            $address="select * FROM `tbl_companies` where  address='COMPANY' and status='A'";
            $query = $this->db->query($address);
            
             if($query->num_rows()>0) {
               return $query->result_array();
            }
        }


        function getLatLong($branch_id){

            $attb="select latitude,longitude from tbl_branches where id='".$branch_id."'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->row();
            }
        }

        function branchsetting($branch_id,$setting_name){

            $attb="select value from tbl_branch_settings where `key`='$setting_name' AND branch_id='$branch_id'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->row();
            }
        }

           //A->APPROVED, D->DECLINED, CB->CANCELLED BY BRANCH, CC->CANCELLED BY CUSTOMER, P->PREPARED, AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED, PU->PICKEDUP BY CUSTOMER
        function runningOrder(){

            $status="N,A,P,AD,OD";
            $current_time=time();
            $customer_id = $this->session->userdata('user_id');
            $orderQuery = "select o.*, todm.firstname as del_firstname, todm. lastname as del_lastname, 
                           todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, 
                           tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, 
                           tb.latitude as blatitude, tb.longitude as blongitude 
                           from tbl_orders o LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id 
                           AND todm.delivery_man_id =o.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=o.branch_id
                           where o.customer_id='".$customer_id."' AND find_in_set(cast(o.status as char),'".$status."')";

            /*if ($type!='A'){
                $orderQuery .="  AND o.order_type='C' ";
            }*/
            $orderQuery .=" ORDER BY o.id desc";
            $query = $this->db->query($orderQuery);
            if($query->num_rows()>0) {
               return $query->result_array();
            }
        }

        
        //A->APPROVED, D->DECLINED, CB->CANCELLED BY BRANCH, CC->CANCELLED BY CUSTOMER, P->PREPARED, AD->ASSIGNED TO DRIVER, OD->OUT FOR DELIVERY, DL->DELIVERED, PU->PICKEDUP BY CUSTOMER

        function orderhistory(){

            $status="DL,PU,CC";
            $current_time=time();
            $customer_id = $this->session->userdata('user_id');
            $orderQuery = "select o.*, todm.firstname as del_firstname, todm. lastname as del_lastname, 
                           todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, 
                           tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, 
                           tb.latitude as blatitude, tb.longitude as blongitude 
                           from tbl_orders o LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id 
                           AND todm.delivery_man_id =o.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=o.branch_id
                           where o.customer_id='$customer_id' AND find_in_set(cast(o.status as char),'$status')";

            /*if ($type!='A'){
                $orderQuery .="  AND o.order_type='C' ";
            }*/
            $orderQuery .=" ORDER BY o.id desc";
            $query = $this->db->query($orderQuery);
            if($query->num_rows()>0) {
               return $query->result_array();
            }
        }

        function getAlladdress($user_id){
            $attb="select * from tbl_customer_addresses where customer_id='$user_id'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->result_array();
            }

        }

        function getSingleaddress($add_id){
            $attb="select * from tbl_customer_addresses where id='$add_id'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->row();
            }
        }
        
        function getSingleCard($card_id){
            $attb="select id, isdefault, card_number,name as card_holder, expiry_month, expiry_year from tbl_credit_cards where id='$card_id'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->row();
            }

        }
        
        function getOrderDetails($order_id){
            /*$attb="select * from tbl_orders where id=$order_id";*/

            $attb = "select o.*,toca.address as toca_address,toca.firstname as toca_firstname,toca.lastname as toca_lastname,toca.profile_pic as toca_profile_pic,toca.mobile_code as toca_mobile_code,toca.mobile as toca_mobile, todm.firstname as del_firstname, todm. lastname as del_lastname, 
                           todm.mobile as del_mobile, todm.mobile_code as del_mobile_code, todm.profile_pic as del_profile_pic, 
                           tb.name as bname, tb.mobile_code as bmobile_code, tb.mobile as bmobile, tb.address as baddress, 
                           tb.latitude as blatitude, tb.longitude as blongitude,tc.firstname as c_firstname,tc.lastname  as c_lastname,tc.mobile_code  as c_mobile_code,tc.mobile  as c_mobile,tc.profile_pic  as c_profile_pic
                           from tbl_orders o LEFT JOIN tbl_order_delivery_men todm ON todm.order_id=o.id 
                           AND todm.delivery_man_id =o.delivery_man_id LEFT JOIN tbl_branches tb ON tb.id=o.branch_id LEFT JOIN tbl_customers tc ON tc.id=o.customer_id LEFT JOIN tbl_order_customer_addresses toca ON toca.order_id=o.id
                           where o.id='$order_id'";
            $query = $this->db->query($attb);
             if($query->num_rows()>0) {
               return $query->row();
            }
        }

        public function isDefaultCard($userid) {
           $cardQuery = "SELECT id FROM tbl_credit_cards WHERE customer_id =$userid";
            $query = $this->db->query($cardQuery);
            return $query->num_rows();
        }

        public function getOrderMobileData($orderid) {
        $mobileQuery = "SELECT mobile_code, mobile, firstname, email FROM tbl_order_customer_addresses WHERE order_id=$orderid";
        $query = $this->db->query($mobileQuery);
        
        if($query->num_rows()>0) {
            return $query->row();
            
        }
    }

       

    public function saveOrderAddress($user_id, $address_id, $order_id) {
        if ($address_id > 0) {
            $selectField = 'tbl_customer_addresses.*, tbl_customers.profile_pic';
            $joinProjArray[] = array(
            'table' => 'tbl_customers',
            'cond' => 'tbl_customers.id = tbl_customer_addresses.customer_id',
            'type' => 'LEFT'
            );
            $condProj = array(
                'tbl_customer_addresses.customer_id' => $user_id,
                'tbl_customer_addresses.id' => $address_id
            );

            $address = $this->select_db('row', 'tbl_customer_addresses', $selectField, $condProj, $joinProjArray);
            //echo $this->db->last_query(); die;
        } else {
            //$addressQuery = "select * from tbl_customers where id=?";
            $cond=array(
                'id' => $user_id
            );
            $address = $this->common_model->select_db('row','tbl_customers','*',$cond);
            //echo $this->db->last_query(); die;
        }
        $total = count((array)$address);

        if($total > 0) {
            $created=time();

            $insertQuery = "INSERT INTO tbl_order_customer_addresses SET address_id='$address_id', order_id='$order_id', firstname='".$address->firstname."',lastname='".$address->lastname."', profile_pic='".$address->profile_pic."', 
             mobile_code='".$address->mobile_code."', mobile='".$address->mobile."', created='$created', email='".$address->email."'";
            //echo $this->db->last_query(); 

            if ($address_id>0)
            $insertQuery .= ", address='".$address->address."', latitude='".$address->latitude."', longitude='".$address->longitude."'";
            $query = $this->db->query($insertQuery);
            //echo $this->db->last_query(); die;
            //print_r($address_insert->errorInfo());
        } 
    }


    public function saveCreditCard($user_id, $card_id, $order_id) {
        $cardQuery = "select * from tbl_credit_cards where customer_id='".$user_id."' AND id='".$card_id."'";
        $card_data = $this->db->query($cardQuery);

        $card_data->num_rows();
        // print_r($card_data->errorInfo());
        if($card_data->num_rows() > 0) {
            $created=time();
            $card = $card_data->row();
           
            $insertQuery = "INSERT INTO tbl_order_credit_cards SET credit_card_id='".$card_id."', order_id='".$order_id."',
            isdefault='".$card->isdefault."', card_token='".$card->card_token."', card_number='".$card->card_number."', 
            name='".$card->name."', expiry_month='".$card->expiry_month."', expiry_year='".$card->expiry_year."', 
            card_type='".$card->card_type."', created='".$created."' ";
            $query = $this->db->query($insertQuery);
           
        }
    }


    public function saveTaxes($branch_id, $order_id, $amount) {
        $taxQuery = "select *, (('".$amount."' * tax) / 100) as tax_amount from tbl_taxes where branch_id='".$branch_id."' AND status='A'";
        $tax_data = $this->db->query($taxQuery);
        $taxes = $tax_data->result_array();

        $total = count((array)$taxes);
        //echo $this->db->last_query(); die('jai');
        // print_r($tax_data->errorInfo());
        if($total > 0) {
            $created=time();
            foreach($taxes as $taxvalue){
                
                $insertQuery = "INSERT INTO tbl_order_taxes SET tax_id='".$taxvalue['id']."', order_id='".$order_id."', 
                title='".$taxvalue['title']."', tax='".$taxvalue['tax']."', tax_amount='".$taxvalue['tax_amount']."',
                created='".$created."'";
                $data = $this->db->query($insertQuery);
                $tax=+$taxvalue['tax'];

            }
            
        } 
    }

     public function BranchTaxes($branch_id,$amount) {

        $taxQuery = "select  IF(ISNULL(($amount * sum(tax) / 100)),'0',($amount * sum(tax) / 100)) as tax_amount,IF(ISNULL(sum(tax)),'0',sum(tax)) as tax from tbl_taxes where branch_id=$branch_id AND status='A'";

        $tax_data = $this->db->query($taxQuery);
        $data = $tax_data->row();
        $res=array();
        $res['tax_amount']=$data->tax_amount;
        $res['tax']=$data->tax;
        return $res;


        } 


    public function getLoyaltyPoints($user_id) {
        $created=time();
        $selectQuery = "SELECT wallet_points from tbl_customers where id='".$user_id."' ";
        $query = $this->db->query($selectQuery);
        $data = $query->row();
      
        $res=array();
        $res['wallet_points']=$data->wallet_points;
        $res['loyalty_point_value']=LOYALTY_POINT_VALUE;
        return $res;
    }


    public function saveItems($order_id, $itemData) {
        $item_id=$itemData['id'];
        $avg_rating=$itemData['avg_rating'];
        $is_nonveg=$itemData['is_nonveg'];
        $is_new=$itemData['is_new'];
        $is_featured=$itemData['is_featured'];
        $name=$itemData['name'];
        $images=$itemData['images'][0]['large'];
        $images=explode('/',$images);
        $image=end($images);
        $price_name=$itemData['prices'][0]['name'];
        $unit_price=$itemData['prices'][0]['price'];
        $extra_price=$itemData['extra_price'];
        $attribute_price=$itemData['attribute_price'];
        $quantity=$itemData['quantity'];
        $total_price=$itemData['quantity']*($itemData['prices'][0]['price']+$itemData['extra_price']+$itemData['attribute_price']);
        $created=time();

        $data=array();

        foreach($itemData as $key=>$value){

            if($key =='attributes'){

                $data['attributes']=$value;
            }

            if($key =='extras'){

                $data['extras']=$value;
            }


        }

        $datadetail=json_encode($data);



     

       $insertQuery = "INSERT INTO tbl_order_items SET order_id='".$order_id."', item_id='".$item_id."', avg_rating='".$avg_rating."',is_nonveg='".$is_nonveg."', is_new='".$is_new."',is_featured='".$is_featured."',
                        name='".$name."', image='".$image."', price_name='".$price_name."', 
                        unit_price='".$unit_price."', extra_price='".$extra_price."', attribute_price='".$attribute_price."',quantity='".$quantity."',total_price='".$total_price."', data='".$datadetail."',
                        created='".$created."'";

       $data = $this->db->query($insertQuery);  
     
    }

    public function getReferralerData() {
        $referralQuery = "SELECT referraler_amount, applier_amount, description FROM tbl_referrals where id=1 AND status='A'";
        $referral_data = $this->db->query($referralQuery);
       
        if($referral_data->num_rows()>0) {
            $referralData = $referral_data->row();
           
            $responseArr['referraler_amount'] = $referralData->referraler_amount;
            $responseArr['applier_amount'] = $referralData->applier_amount;
            $responseArr['description'] = $referralData->description;
        } else {
            $responseArr=NULL;
        }
        return $responseArr;
    }

     public function makeAddressDefault($user_id,$address_id, $actiontype) {
        $created=time();
        if ($actiontype == 'ORDER') {
            $updateQuery = "UPDATE tbl_customer_addresses SET isdefault='Y' where id='".$address_id."'";
            $update_default = $this->db->query($updateQuery);
            $affected_row = $this->db->affected_rows();

            if($affected_row > 0) {
                $updateQuery2 = "UPDATE tbl_customer_addresses SET isdefault='N' where customer_id='".$user_id."'
                                 AND id!='".$address_id."' AND isdefault='Y'";
                $update_default = $this->db->query($updateQuery2);
             }   
            
        } else {
            $updateQuery = "UPDATE tbl_customer_addresses SET 
            isdefault=(select * from (select IF(count(*)>0,'N','Y') as cnt from tbl_customer_addresses tca 
                where tca.customer_id='".$user_id."' AND tca.isdefault='Y') as tt) where customer_id='".$user_id."' AND id='".$address_id."'";
            $update_default = $this->db->query($updateQuery);
          
            if($this->db->affected_rows() >0) {
                return 'SUCCESS';
            } else {
                return 'UNABLE_TO_PROCEED';
            }
        }   
        
    }

    public function saveTransactionData($order_id, $user_id,$status, $action, $device_id, $device_type, $ip_address, $description) {
        $created=time();
        $insertQuery = "INSERT INTO tbl_order_transactions SET order_id='".$order_id."', action='".$action."', status='".$status."',description='".$description."', device_id='".$device_id."', device_type='".$device_type."', ip_address='".$ip_address."',created='".$created."', createdby='".$user_id."'";
        $this->db->query($insertQuery);
        //print_r($insert_transaction->errorInfo());
        if($this->db->affected_rows()>0) {
            return TRUE;
        } else {
            return 'UNABLE_TO_PROCEED';
        }
    }

     public function updateOrderStatus($order_id) {
        $updateQuery = "UPDATE tbl_orders SET status='PP' where id='".$order_id."'";
        $data = $this->db->query($updateQuery);
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


    public function savePromocode($order_id, $promocode) {
       $insertQuery = "INSERT INTO tbl_order_promotions (order_id, company_ids, branch_ids, category_ids, item_ids, title, description, no_of_attempts_user, total_promocode_attempts, tot_user_applied, promotion_applicable_for, start_date, end_date, order_type, delivery_options, promotion_type, loyalty_points, discount_type, discount, min_purchase, max_discount, quantity, customer_ids) SELECT ?, company_ids, branch_ids, category_ids, item_ids, title, description, no_of_attempts_user, total_promocode_attempts, tot_user_applied, promotion_applicable_for, start_date, end_date, order_type, delivery_options, promotion_type, loyalty_points, discount_type, discount, min_purchase, max_discount, quantity, customer_ids FROM tbl_promotions where BINARY promotion_code=?";
        $data = $this->db->query($insertQuery);
    }

   function getItemCrustJson($item_id,$crust_att_id){
       
      $crustQuery = "select tia.id, ta.name, ta.description, ta.type from tbl_item_attributes tia JOIN tbl_attributes ta ON ta.id=tia.attribute_id where tia.status='A' and ta.status='A'  and tia.id='".$crust_att_id."' order by priority asc";
        $query = $this->db->query($crustQuery);
       
        if($query->num_rows()>0) {
           return $query->row();
        }
    }

    public function getAttributeDetailsJson($item_attid){
         $optionQuery="select tiao.item_attribute_id,tao.id, tao.name, tiao.price, tiao.default_selected
            FROM tbl_item_attribute_options tiao JOIN tbl_attribute_options tao ON tao.id=tiao.attribute_option_id 
            WHERE tao.id='".$item_attid."' AND tiao.status='A' AND tao.status='A'";

            /*if($isDefault!=''){
                $optionQuery .= " AND default_selected= 'Y' ";    
            }*/
            
            $optionQuery .= "order by tiao.priority asc";

            //echo $optionQuery;

            $query = $this->db->query($optionQuery);
            
             if($query->num_rows()>0) {
               return $query->row();
            }

    }



     public function getAttributeDetailsJson1($attributes_key,$attributes_values){
        


        $optionQuery="select tiao.item_attribute_id ,tao.id, tao.name, tiao.price, tiao.default_selected,ta.name as attribute_name,ta.description as description,ta.type
            FROM tbl_item_attribute_options tiao JOIN tbl_attribute_options tao ON (tao.id=tiao.attribute_option_id) JOIN tbl_item_attributes tia ON (tia.id=tiao.item_attribute_id ) join  tbl_attributes ta ON (ta.id=tia.attribute_id )
            WHERE tiao.attribute_option_id='".$attributes_values."' AND tiao.item_attribute_id='".$attributes_key."' AND  tiao.status='A' AND tao.status='A'";


            


            

           
            
            $optionQuery .= "order by tiao.priority asc";

            //echo $optionQuery;

            $query = $this->db->query($optionQuery);
            
             if($query->num_rows()>0) {
               return $query->row();
            }

    }

    function getItemExtrasJson($extra_att_id){
           
          $extraQuery = "select tie.id, te.name, te.image, te.description, tie.price from tbl_item_extras tie JOIN tbl_extras te ON te.id=tie.extra_id WHERE tie.id='".$extra_att_id."' AND tie.status='A' AND te.status='A' order by tie.priority asc";
            $query = $this->db->query($extraQuery);
           
            if($query->num_rows()>0) {
               return $query->row();
            }
        }

     function getItemImagesJson($item_id){
           
           $extraQuery = "select image from tbl_item_images WHERE item_id='".$item_id."' order by id asc";
            $query = $this->db->query($extraQuery);
           
            if($query->num_rows()>0) {
               return $query->row();
            }
        }


        function getItemImagesJson1($item_id){
           
           $extraQuery = "select image,isdefault from tbl_item_images WHERE item_id='".$item_id."' order by id asc";
            $query = $this->db->query($extraQuery);
           
            if($query->num_rows()>0) {
               return $query->row();
            }
        }

    function getItemDetailJson($item_id)
    {
           
            $item_details = "select ti.is_featured, ti.is_new, ti.is_nonveg, ti.name as itemname, tip.price,tip.name,tip.isdefault from tbl_items ti JOIN  tbl_item_prices tip ON tip.item_id=ti.id WHERE ti.id='".$item_id."' AND ti.status='A' AND tip.status='A'";
            $query = $this->db->query($item_details);
           
            if($query->num_rows()>0) {
               return $query->row();
            }
    }


    //promocode check

    // function checkPromocode($promocode)
    // {
    //     $this->db->select('*');
    //     $this->db->from('tbl_promotions');
    //     $this->db->where('promotion_code', $promocode);
    //     $this->db->where('status', 'A');
    //     $query = $this->db->get();
    //     $result = $query->row();
    //     if($query->num_rows()>0)
    //     {
    //         //$message = 'Promocode succefully applied';
    //         if($this->checkPromobranch($result))
    //         {

    //         }
    //     }
    //     else
    //     {
    //         $message 'Please enter a valid promocode';
    //     }
    //     return $message;
    // }

    // function checkPromobranch($result)
    // {

    // }

}

?>
