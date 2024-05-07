<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../lib/dompdf/dompdf_config.inc.php';
use mikehaertl\wkhtmlto\Pdf;
class api{ 
    
    private $conn;
    
    function __construct() {
        require_once  '../global_constants.php'; 
        require_once  'entity.php'; 
        require_once  'connection.php'; 
        $this->conn = $conn;
    }
    
    function setpassword($intuserid,$intuserpass)
    {
        
       $newpassword =md5($intuserpass); 
           $update_user = "UPDATE tbl_customers SET password=? WHERE id = ? and is_deleted-'N'";
                $update_user = $this->conn->prepare($update_user);
                $update_user->bindParam(1,$newpassword);
                $update_user->bindParam(2,$intuserid);
             $update_user->execute();
             echo json_encode(array('message'=>'ok'));
             exit;
     }
        function getlevels($intUserId)
    {
        
                     $aryPostData['message'] ="ok";

        $update_user = "Select IFNULL(SUM(amount),0) as total From tbl_customer_wallet_histories WHERE 1 AND type='CUSTOMER_REFFER_COMMISSION' AND customer_id=".$intUserId;
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
                     
            
     $aryPostData['total_income'] =$out['total'];
     
     $startData = date('Y-m-01');
     $endDate = date('Y-m-t');
     
        $update_user = "Select IFNULL(SUM(amount),0) as total From tbl_customer_wallet_histories WHERE 1 AND DATE(created_date) BETWEEN '$startData' AND '$endDate' AND type='CUSTOMER_REFFER_COMMISSION' AND customer_id=".$intUserId;
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
                     
                     
                     
      $aryPostData['total_monthly_income'] =$out['total'];
      
      
           $update_user = "Select * From tbl_commission";
                $update_user = $this->conn->prepare($update_user);
             $update_user->execute();
                     $out = $update_user->fetchAll(PDO::FETCH_ASSOC);
                     
                     
            $aryData =array();
            foreach($out as $row)
            {
                ////print_r($row);
                
                $aryLevelUsers = $this->getdata($row['c_level'],$intUserId);
               $aryData[] =array('title'=>"Level ".$row['c_level'],"member"=>(is_array($aryLevelUsers)?count($aryLevelUsers):0),"yesterday"=>0,"yesterdayearning"=>0); 
                
            }
            $aryPostData['result'] =$aryData;
            return $aryPostData;
     }
     
     
     function getdata($intLevel,$intUserId)
     {
         
         
         
        $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id=".$intUserId;
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==1)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
                  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==2)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
                
                 $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==3)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
                    $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==4)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==5)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
/*****************************************Level 6*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==6)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
/*****************************************Level 7*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==7)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                


/*****************************************Level 8*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==8)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
/*****************************************Level 9*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==9)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
/*****************************************Level 10*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==10)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
/*****************************************Level 11*/
  $update_user = "Select GROUP_CONCAT(id) as total From tbl_customers WHERE 1 AND used_referral_user_id IN(".$out['total'].')';
        $update_user = $this->conn->prepare($update_user);
        $update_user->execute();
        $out = $update_user->fetch(PDO::FETCH_ASSOC);
        
        $aryUser =array();
        if($out['total']!="")
        {
            
            if($intLevel==11)
            {
            $aryUser= explode(',',$out['total']);
            return $aryUser;
            }else{
                
                     return $aryUser;
       
            }
             } else{
                  return $aryUser;
          
            }       
            }
             } else{
                  return $aryUser;
          
            }       
            }
             } else{
                  return $aryUser;
          
            }       
            }
             } else{
                  return $aryUser;
          
            }
            
            
            }
             } else{
                  return $aryUser;
          
            }

            }
             } else{
                  return $aryUser;
          
            }

            }
        } else{
                  return $aryUser;
          
            }

            }
        } else{
                  return $aryUser;
          
            }
        
            }
        } else{
                  return $aryUser;
          
            }
            
                   ///  return $aryUser;
       
            }
        } else{
                  return $aryUser;
          
            }
                
            }
        } 
     }
    function setRAmount($customer,$code){
        
        $selc = 'SELECT * FROM tbl_customer_wallet_histories where wallet_type="Bonus Wallet" AND transaction_type="CREDIT" AND type="CUSTOMER_WALLET_RECHARGE" AND description = "'.$code.'" ';
        $sel_userc = $this->conn->prepare($selc);
        $sel_userc->execute();
        $usedCount = $sel_userc->rowCount();
        
        $time = time();
        $sel_user_query = "SELECT * FROM tbl_recharge_cach_bonus where code='".$code."' AND ".$time." BETWEEN start_date AND end_date AND is_use_max > '".$usedCount."'";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();
        $out = $sel_user->fetch(PDO::FETCH_ASSOC);
        if(isset($out['id'])){
            $customer_id = $customer;
            $transtype = "CREDIT";
            $description = $code;
            //$wallet_type = "bonus_wallet";
            $wallet_type = $out['amt_type'];
            $type = "CUSTOMER_WALLET_RECHARGE";
            $amount = $out['amount'];
            $response = array();
            if($this->customer_deposit_amount($customer_id, $amount,$transtype,$description,$wallet_type,$type,$code)){
                $response['status'] = 'ok';
                $response['msg'] = "Amount Redeemed Successfully!";
            }else{
                $response['status'] = 'failed';
                $response['msg'] = "";
            }
        }else{
           $response['status'] = 'failed';
            $response['msg'] = "Code Does Not Exist"; 
        }
        echo json_encode($response);
        exit;
    }
    function checkpaymentstatus($aryPostData)
{
      $sel_user_query = "SELECT * FROM tbl_tem_payment WHERE tp_id = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $aryPostData['payment_id']);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
        $response =array();
         if($user['tp_status']==1 || $user['tp_status']=='PAYMENT_SUCCESS')
        {
          $response['status']='completed';  
        }
       echo json_encode($response);
        exit;
}
    public function generate_pdf_cron($match_unique_id=0, $match_contest_id=0) {

        $this->includeDomPdfLib();
        $this->includeWKPdfLib();

        $this->includepdfTemplate();
        $ContestPdf=new contest_pdf();

          $query="SELECT id,unique_id,name FROM tbl_cricket_matches";
        /*if(empty($match_unique_id)){
            $query.=" where match_progress='L'";
        }else{
            $query.=" where unique_id='$match_unique_id'";
        }*/
         
        $query_res = $this->conn->prepare($query);
        $output=array();
        if($query_res->execute()){
            if ($query_res->rowCount() > 0) {

                while($matches = $query_res->fetch(PDO::FETCH_ASSOC)){
                        $match_id=$matches['id'];

                          $query_contest="SELECT tccm.id,tccm.total_team,tccm.entry_fees,tccm.total_price,tccm.slug  FROM tbl_cricket_contest_matches tccm  where tccm.match_id='$match_id' and status='A' and is_deleted='N'";
                            
                        /*if(empty($match_contest_id)){
                            $query_contest.=" AND tccm.pdf_process='N' LIMIT 1";
                        }else{
                            $query_contest.=" AND tccm.id='$match_contest_id'";
                        }*/

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
                            $htmlContest=$ContestPdf->genrate_pdf($contests[$i]);
                            ////    ini_set("memory_limit", "20489999M");
                             ////   ini_set("max_execution_time", "180000000");
                            //// echo $html;   
                                $pdfname=$matches['unique_id']."_".$contests[$i]['match_contest_id']."_cricket.pdf";
                                
                                $path=ROOT_DIRECTORY."cpdfs/".$pdfname;
                                $dompdf = new DOMPDF();
                                $dompdf->load_html($htmlContest);
                                $dompdf->render();
                                 $ret = file_put_contents($path, $dompdf->output());
                               /// echo $dompdf->output();
                                if($ret){
                                    $params=array();
                                    $params['upload_path']=PDF_PATH;
                                    $params['file_name']=$pdfname;
                                    $key = $params['upload_path'].$params['file_name'];

                                    $query_up_pdf  = "UPDATE tbl_cricket_contest_matches set pdf='".$pdfname."', pdf_process='S'  WHERE id='".$contests[$i]['match_contest_id']."'";
                                    $query_up_pdf_res  = $this->conn->prepare($query_up_pdf);
                                    if(!$query_up_pdf_res->execute()) {
                                        $this->sql_error($query_up_pdf_res);
                                    }
                                    $this->closeStatement($query_up_pdf_res);

                                    //unlink($path);
                                }

                            $i++;
                        }

                        $this->closeStatement($query_contest_res);
                      /*  echo "<pre>";
                        print_r($contests);*/
                   ///   $output[$match_id]=$contests;
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
            require '../apis/mobile/include/pdf.php';
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
        public function login($mobileno,$jsonstring){
        $cntryCode = '+91'; ///country_mobile_code
        $sel_user_query = "SELECT * FROM tbl_customers WHERE phone = ? AND is_deleted='N'";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $mobileno);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
        $response = array();
        if(!empty($user)) {
            if($user['status'] != 'A'){
                $response['status'] = 'failed';
                $response['msg'] = 'USER_ACCOUNT_DEACTVATED';
            }else{
                $mobile_code = '+91';
                $user_id = $user['id'];
                $otp = $this->sendotp($mobileno, 'L', $mobile_code, $jsonstring,$user_id);
                $response['status'] = 'ok';
                $response['msg'] = $user; //otp
            }
        }else{
            $response['status'] = 'failed';
            $response['msg'] = 'INVALID_USER';
        }
        echo json_encode($response);
        exit;
    }
  public function sendotp($mobileno, $type, $mobile_code, $jsonstring,$user_id=0) {
        $otp = rand(1000, 9999);
        $query = "INSERT INTO tbl_tempcustomers SET country_mobile_code= ?, mobileno= ?, otp= ?, type= ?, customer_data=?";
        if($user_id>0){
            $query .=", user_id = ?";
        }
        $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $mobile_code);
        $query_res->bindParam(2, $mobileno);
        $query_res->bindParam(3, $otp);
        $query_res->bindParam(4, $type);
        $query_res->bindParam(5, $jsonstring);
        if($user_id>0){
            $query_res->bindParam(6, $user_id);
        }
        if ($query_res->execute()) {
            $this->send_sms($text='Requested Login OTP '.$otp,$mobileno,$issos='NO', $country_code=+91);
            return $otp;
        } else {
            //$this->closeStatement($query_res);
            return 0;
        }     
    }
    
    public function send_sms($text='',$to='',$issos='NO', $country_code=+91){
        
        include_once('../lib/textlocal.class.php');
        $to = $country_code.$to;
        $text= strip_tags($text);
        $textlocal = new Textlocal(SMS_USERNAME, SMS_PASSWORD);
        $numbers = array($to);
        $sender = SMS_SENDER_NAME;
        try {
            $result = $textlocal->sendSms($numbers, $text, $sender);
            print_r($result);
            die;
            
        } catch (Exception $e) {
            print_r($e);
            die;
        	//return true;
        }
        return true;
    }
    
    function getCountry($country){
        $sel_user =  $this->conn->prepare("Select * FROM tbl_countries where name = ? ");
        $sel_user->bindParam(1, $country);
        $sel_user->execute();
        $user = $sel_user->fetch();
        if(!isset($user['id']))
        {
            $sel_user_query = "INSERT INTO tbl_countries (name,status) VALUES (?,?)";
                        $status = 1;
                        $sel_user = $this->conn->prepare($sel_user_query);
                        $sel_user->bindParam(1, $country);
                        $sel_user->bindParam(2, $status);
                        $sel_user->execute(); 
            return $this->conn->lastInsertId();
        }else{
            return $user['id'];
        }
    }
    
    function pushUpcomingMatch($data,$type=1){
        if(!empty($data)){
            foreach($data['matches'] as $key=>$upc){
               $intSeriesId = $this->insertseries($upc); 
              
               if($intSeriesId>0)
               {
                   $teamId =0;
                   $teamId2 = 0;
                   $teamAray = array();
                   if(isset($upc['teams_data']['teama']))
                   {
                      $teamId =  $this->insertteams($upc['teams_data']['teama']);
                      $teamAray[$upc['teams_data']['teama']['name']] = $teamId;
                   }
                    if(isset($upc['teams_data']['teamb']))
                   {
                      $teamId2 =  $this->insertteams($upc['teams_data']['teamb']);
                      $teamAray[$upc['teams_data']['teamb']['name']] = $teamId2;
                   }
                  
                  if($teamId>0 && $teamId2>0)
                  {
                    //   echo '<pre>';
                    //   print_r($upc);die;
                                         $insertMatch = $this->insertMatch($upc,$intSeriesId,$teamId,$teamId2,$type);
                                                       $this->insertPlayers($upc,$teamAray);
                    }
               }
            }
        }
    }
    
    function insertPlayers($upc,$teamAray){
        //foreach($upc as $k=>$p){
            foreach($upc['squad_data']['squad'] as $squad){
                foreach($squad['players'] as $player){
                    
                    $sel_user =  $this->conn->prepare("Select * FROM tbl_cricket_players where uniqueid = ? ");
                    $sel_user->bindParam(1, $player['pid']);
                    $sel_user->execute();
                    $user = $sel_user->fetch();
                    if(!isset($user['id']))
                    {
                        $sel_user_query = "INSERT INTO tbl_cricket_players (position,name,short_name,country_id,bets,bowls,dob,uniqueid,status,summary) VALUES (?,?,?,?,?,?,?,?,?,?)";
                        $status = 'A';
                        $summery = json_encode($player);
                        $country = $this->getCountry($player['detail_data']['country']);
                        $sel_user = $this->conn->prepare($sel_user_query);
                        $sel_user->bindParam(1, $player['detail_data']['playingRole']);
                        $sel_user->bindParam(2, $player['detail_data']['name']);
                        $sel_user->bindParam(3, $player['detail_data']['short_name']);
                        $sel_user->bindParam(4, $country);
                        $sel_user->bindParam(5, $player['detail_data']['battingStyle']);
                        $sel_user->bindParam(6, $player['detail_data']['bowlingStyle']);
                        $sel_user->bindParam(7, $player['detail_data']['born']);
                        $sel_user->bindParam(8, $player['detail_data']['pid']);
                        $sel_user->bindParam(9, $status);
                        $sel_user->bindParam(10, $summery);
                        $sel_user->execute(); 
                        //return $this->conn->lastInsertId();
                    }
                    
                    $sel_user = $this->conn->prepare("Select * FROM tbl_cricket_match_players where match_unique_id = ? AND player_unique_id = ?");
                    $sel_user->bindParam(1, $upc['unique_id']);
                    $sel_user->bindParam(2, $player['pid']);
                    $sel_user->execute();
                    $user = $sel_user->fetch();
                    ///print_r($user);
                    if(!isset($user['id']))
                    {
                        $teamId = $teamAray[$squad['name']];
                        $sel_user_query = "INSERT INTO tbl_cricket_match_players (match_unique_id,player_unique_id,playing_role,team_id,credits) VALUES (?,?,?,?,?)";
                        $sel_user = $this->conn->prepare($sel_user_query);
                        $dataPlayingRole = strtolower($player['detail_data']['playingRole']);
                        $pid = $player['pid'];
                        $uniqe = $upc['unique_id'];
                        $credit = $player['detail_data']['credits'];
                        $sel_user->bindParam(1, $uniqe);
                        $sel_user->bindParam(2, $pid);
                        $sel_user->bindParam(3, $dataPlayingRole);
                        $sel_user->bindParam(4, $teamId);
                        $sel_user->bindParam(5, $credit);
                        $sel_user->execute();
                    }
                    
                }
            }
        //}
    }

    function insertseries($upc){
        if(!empty($upc)){
                 $sel_user_query = "SELECT * FROM tbl_cricket_series WHERE uniqueid = ?";
                $sel_user = $this->conn->prepare($sel_user_query);
                $sel_user->bindParam(1, $upc['series_data']['unique_id']);
                $sel_user->execute();
                $user = $sel_user->fetch();
                if(!isset($user['id']))
                {
 $strCurrentDate =strtotime(date('Y-m-d h:i:s'));
    $sel_user_query = "INSERT INTO tbl_cricket_series (name,abbr,season,type,uniqueid,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)";
                    $status = 1;
                    $sel_user = $this->conn->prepare($sel_user_query);
                    $sel_user->bindParam(1, $upc['series_data']['title']);
                    $sel_user->bindParam(2, $upc['series_data']['abbr']);
                    $sel_user->bindParam(3, $upc['series_data']['season']);
                    $sel_user->bindParam(4, $upc['series_data']['type']);
                    $sel_user->bindParam(5, $upc['series_data']['unique_id']);
                    $sel_user->bindParam(6, $status);
                    $sel_user->bindParam(7, $strCurrentDate);
                    $sel_user->bindParam(8, $strCurrentDate);
                    $sel_user->execute(); 
                    return $this->conn->lastInsertId();
                }else{
                    return $user['id'];
                }
        }
    }
    
    function insertteams($upc){
        if(!empty($upc)){
            
                   if(isset($upc['tid']))
                   {
                        $upc['name'] =$upc['tname']; 
                        $upc['short_name'] =$upc['tname']; 
                        $upc['team_id'] =$upc['tid']; 
                        $upc['logo_url'] =$upc['logo']; 
                   } 
                //   print_r($upc);
                //     die;
                 $sel_user_query = "SELECT * FROM tbl_cricket_teams WHERE unique_id = ?";
                $sel_user = $this->conn->prepare($sel_user_query);
                $sel_user->bindParam(1, $upc['team_id']);
                $sel_user->execute();
                $user = $sel_user->fetch();
                if(!isset($user['id']))
                {
                   
                    $sel_user_query = "INSERT INTO tbl_cricket_teams (name,sort_name,unique_id,logo,status,is_deleted,created_at,updated_at,created_by) VALUES (?,?,?,?,?,?,?,?,?)";
                    $status = 1;
                    $deleted = 2;
                    $cby = 1;
                    $strCurrentDate =strtotime(date('Y-m-d h:i:s'));
                    $sel_user = $this->conn->prepare($sel_user_query);
                    $sel_user->bindParam(1, $upc['name']);
                    $sel_user->bindParam(2, $upc['short_name']);
                    $sel_user->bindParam(3, $upc['team_id']);
                    $sel_user->bindParam(4, $upc['logo_url']);
                    $sel_user->bindParam(5, $status);
                    $sel_user->bindParam(6, $deleted);
                      $sel_user->bindParam(7, $strCurrentDate);
                    $sel_user->bindParam(8, $strCurrentDate);
                    $sel_user->bindParam(9, $cby);
           $sel_user->execute(); 
                    return $this->conn->lastInsertId();
                }else{
                    return $user['id'];
                }
        }
    }
    
    function insertMatch($upc,$intSeriesId,$teamId,$teamId2,$game_id){
        if(!empty($upc)){
                $sel_user_query = "SELECT * FROM tbl_cricket_matches WHERE unique_id = ?";
                $sel_user = $this->conn->prepare($sel_user_query);
                $sel_user->bindParam(1, $upc['unique_id']);
                $sel_user->execute();
                $user = $sel_user->fetch();

                $format  = $upc['format'];
                $format_str  = $upc['format_str'];

                $game_query = "SELECT * FROM tbl_game_types WHERE name = ?";
                $game_user = $this->conn->prepare($game_query);
                $game_user->bindParam(1, $format_str);
                $game_user->execute();
                $user_game = $game_user->fetch();

                if(!isset($user_game['id'])){
                    $gamei_query = "INSERT INTO tbl_game_types (name,status,is_deleted,created_at,created_by,updated_at,updated_by) VALUES (?,?,?,?,?,?,?)";
                    $statusGame = 'A';
                    $deletedGame = 'N';
                    $gametime = time();
                    $cuby = 1;

                    $gamei_user = $this->conn->prepare($gamei_query);
                    $gamei_user->bindParam(1, $format_str);
                    $gamei_user->bindParam(2, $statusGame);
                    $gamei_user->bindParam(3, $deletedGame);
                    $gamei_user->bindParam(4, $gametime);
                    $gamei_user->bindParam(5, $cuby);
                    $gamei_user->bindParam(6, $gametime);
                    $gamei_user->bindParam(7, $cuby);
                    $gamei_user->execute();
                }
                
                $t20Array  = array('T20I','Woman T20','T20');
                $oDIArray  = array('ODI','Women ODI');
                
                if(in_array($format_str,$t20Array)){
                    $format_str = 'T20';
                }elseif(in_array($format_str,$oDIArray)){
                    $format_str = 'ODI';
                }

                $game_format_query = "SELECT * FROM tbl_game_types WHERE name = ?";
                $game_format_user = $this->conn->prepare($game_format_query);
                $game_format_user->bindParam(1, $format_str);
                $game_format_user->execute();
                $user_format_game = $game_format_user->fetch();

                if(!isset($user['id']))
                {
                    $sel_user_query = "INSERT INTO tbl_cricket_matches (unique_id,name,short_title,subtitle,game_type_id,series_id,team_1_id,team_2_id,match_date,status,match_limit,game_id,created_by,created_at,close_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    $status = 1;
                    $deleted = 4;
                    $match_limit = '1000';
                    $cby = '1';
                    $cat = time();
                    
                    $strCurrentDate = strtotime(date('Y-m-d H:i:s',$upc['date']));
                    //$strEndDate = strtotime(date('Y-m-d H:i:s',$upc['enddate']));
                    $strEndDate = strtotime(date('Y-m-d H:i:s',$upc['date']));
                    
                    $sel_user = $this->conn->prepare($sel_user_query);
                    $sel_user->bindParam(1, $upc['unique_id']);
                    $sel_user->bindParam(2, $upc['title']);
                    $sel_user->bindParam(3, $upc['short_title']);
                    $sel_user->bindParam(4, $upc['subtitle']);
                    $sel_user->bindParam(5, $user_format_game['id']);
                    $sel_user->bindParam(6, $intSeriesId);
                    $sel_user->bindParam(7, $teamId);
                    $sel_user->bindParam(8, $teamId2);
                    $sel_user->bindParam(9, $strCurrentDate);
                    $sel_user->bindParam(10, $status);
                    $sel_user->bindParam(11, $match_limit);
                    $sel_user->bindParam(12, $game_id);
                    $sel_user->bindParam(13, $cby);
                    $sel_user->bindParam(14, $cat);
                    $sel_user->bindParam(15, $strEndDate);
                    //$sel_user->bindParam(16, $format);
                    
                    $sel_user->execute(); 
                    return $this->conn->lastInsertId();
                }else{
                    return $user['id'];
                }
        }
    }
    
    function setCompletedStatus(){
        $en = new Entitysport();
        $sel_user_query = "SELECT * FROM tbl_cricket_matches WHERE match_progress ='L' AND game_id=1";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        foreach($user as $match){
            $result = $en->getMatchStatus($match['unique_id']);
            if($result['status'] == 'ok' && !empty($result['response'])){
                $status = $result['response']['status'];
                if($status == '2'){
                    $query_match  = "UPDATE tbl_cricket_match_players set is_in_playing_squad = 0 WHERE match_unique_id =".$match['unique_id'];
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                    
                    $query_match  = "UPDATE tbl_cricket_matches set lineup_announced = 0, match_progress = 'IR',close_date=".time()." WHERE unique_id =".$match['unique_id'];
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                }
            }
        }
    }
    
    
        function setCompletedStatusfootball(){
        $en = new Entitysport();
     echo    $sel_user_query = "SELECT * FROM tbl_cricket_matches WHERE match_progress ='L' AND game_id=2";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        foreach($user as $match){
            $result = $en->getmatchfootballstatus($match['unique_id']);
            
            print_r($result);
            // if($result['status'] == 'ok' && !empty($result['response'])){
            //     $status = $result['response']['status'];
            //     if($status == '2'){
            //         $query_match  = "UPDATE tbl_cricket_match_players set is_in_playing_squad = 0 WHERE match_unique_id =".$match['unique_id'];
            //         $query_match_res  = $this->conn->prepare($query_match);
            //         $query_match_res->execute();
                    
            //         $query_match  = "UPDATE tbl_cricket_matches set lineup_announced = 0, match_progress = 'IR',close_date=".time()." WHERE unique_id =".$match['unique_id'];
            //         $query_match_res  = $this->conn->prepare($query_match);
            //         $query_match_res->execute();
            //     }
            // }
        }
    }

    
    function getsharetext($userSlug)
    {
        
          $sel_user_query = "SELECT * FROM tbl_customers WHERE slug = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
          $decodedId = $this->base64_decode($userSlug);
         $sel_user->bindParam(1, $decodedId);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
         $response = array();
        if(!empty($user)){
            $reffralCode = $user['referral_code'];
            $response['user_share_code'] = $reffralCode;

            $sel_user_query = "SELECT * FROM tbl_settings where tbl_settings.key = ?";
             
            $sel_user = $this->conn->prepare($sel_user_query);
            $type = 'text_share_screen_1';
            $sel_user->bindParam(1, $type);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
            $response['text_share_screen_1'] = $user['value'];
             $sel_user = $this->conn->prepare($sel_user_query);
             $type2 = "text_share_screen_2";
            $sel_user->bindParam(1, $type2);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
            
            $response['text_share_screen_2'] = $user['value'];
            
            
            $sel_user = $this->conn->prepare($sel_user_query);
            $type3 = "text_share_message";
            $sel_user->bindParam(1, $type3);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
            
            $response['text_share_message'] = str_replace(array("{{INVITATION_CODE}}","{{APP_URL}}"),array($reffralCode,APKURL),$user['value']);
            
            $sel_user = $this->conn->prepare($sel_user_query);
            $type4 = "share_image";
            $sel_user->bindParam(1, $type4);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
            
            $response['share_image'] = ($user['value']!="")?APP_ICON_CUSTOMIZE_IMAGE_LARGE_URL.$user['value']:"";
            $response['message'] ="ok";
            
            
            $sel_user_query ='  SELECT * FROM tbl_referral_cash_bonus where 1 and tbl_referral_cash_bonus.key = ? ';
              $sel_user = $this->conn->prepare($sel_user_query);
            $type4 = "REFERRAL_EARN_IMAGE";
            $sel_user->bindParam(1, $type4);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
                      $response['share_image_2'] = ($user['value']!="")?REFER_EARN_IMAGE_LARGE_URL.$user['value']:"";
  
             
             
            
        }else{
             $response['message'] ="failed";
        }
        echo json_encode($response);
        exit;
    }
    
    function getUser($user_id){
        $sel_user_query = "SELECT * FROM tbl_customers WHERE id = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $user_id);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
        $response = array();
        if(!empty($user)){
            return $user;
        }else{
            return array();
        }
    }
    
    function sendEmailVerificationLink($user_id){
        $data=array();
        $customer_detail = $this->getUser($user_id);
        $strToken = md5($customer_detail['email'].''.time());
        $sqlUpdate = 'UPDATE tbl_customers SET email_token=\''.$strToken.'\' WHERE 1 AND id='.$customer_detail['id'];
        $this->conn->query($sqlUpdate);
        $customer_detail['email_token'] = $strToken;
        $data['link']=APP_URL."email_verification/index/".$customer_detail['email_token'];
        $full_name=$customer_detail['firstname']." ".$customer_detail['lastname'];
        $response = array();
        if(!empty($customer_detail)){
            $email = $customer_detail['email'];
            $full_name = $customer_detail['firstname'].' '.$customer_detail['lastname'];
            $this->sendTemplatesInMail('email_verification_link', trim($full_name), $email,$data);
            $response['message'] = 'ok';
        }else{
            $response['message'] = 'failed';
            $response['error'] = 'User not found!';
        }
        echo json_encode($response);
        exit;
    }
    
    function sendTemplatesInMail($mailTitle, $toName, $toEmail,$data=array()){
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
    
    public function sendSMTPMail($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
        if(!empty(SMTP_SERVER)){

                    $this->includeSMTPMailerLib();
                    die;
                    $mail = new PHPMailer(true);
            try {


                    $mail->SMTPDebug = 0;                       // Enable verbose debug output
                    $mail->isSMTP();                            // Set mailer to use SMTP
                    $mail->Host = SMTP_SERVER;                  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                     // Enable SMTP authentication
                    $mail->Username = SMTP_USERNAME;            // SMTP username
                    $mail->Password = SMTP_PASSWORD;            // SMTP password
                    $mail->SMTPSecure = SMTP_SECURE;            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = SMTP_PORT;   
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

                    /*print_r($r);
                    die;*/
            } catch (Exception $e) {

                //print_r($e);
                 return $e;
            }

        }
        return "SUCCESS";
    }
    
     public function includeSMTPMailerLib() {

            $filesArr=get_required_files();
            $searchString=PHPMAILER_LIB_PATH;
            if(!in_array($searchString, $filesArr)) {
                // echo PHPMAILER_LIB_PATH; die;
                require PHPMAILER_LIB_PATH;
                require PHPMAILER_LIB_PATH_URL;
               
            }
    }
    
    function customer_deposit_amount($customer_id, $amount,$transtype,$description,$wallet_type,$type,$promocode=""){    
        $amount=round($amount,2);
        $transaction_id=$type."".time();
        $match_contest_id=0;
       return  $this->update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,$transtype,$type,$transaction_id,$description,0,0,true,0,null,null,null,$promocode);
    }
    
    function update_customer_wallet($customer_id,$match_contest_id,$wallet_type,$amount,$transaction_type,$type,$transaction_id,$description,$rcbId=0,$refCwhId=0,$send_sms=true,$team_id=0,$refrence_id=null,$json_data=null,$payment_method=null,$promocode=null){

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
                return false;
            }
            $query_res  = $this->conn->prepare($wallet_update);
            $query_res->bindParam(1,$amount);
            $query_res->bindParam(2,$customer_id);
             
            if($query_res->execute()) {

                    $this->closeStatement($query_res);
                
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
                return true;

            } else{
                $this->closeStatement($query_res);
                return false;
            }
    }
    
    function sendTemplatesInSMS_other($templateTitle, $data, $mobileno, $mobile_code) {
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
    
    function getDepositAmount($details){
         $sel_user_query = "SELECT * FROM tbl_customers WHERE phone = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $details['mobile_number']);
        $sel_user->execute();
        $userDetails = $sel_user->fetch(PDO::FETCH_ASSOC);
        $response = array();
        if(!empty($userDetails)){
            $response['message'] = 'ok';
            $response['user_id'] = $userDetails['id'];
            $response['user_name'] = trim($userDetails['firstname'].' '.$userDetails['lastname']);
            $user = $this->getUpdatedWalletData($details['user_id']);
            if(!empty($user)){
                $userAmount = $user['wallet']['winning_wallet']+$user['wallet']['deposit_wallet'];
                if($userAmount>=$details['transfer_amount']){
                    $response['amount'] = $userAmount;   
                }else{
                   $response['error'] = 'Amount must be less than wallet balance!';  
                }
            }else{
               $response['error'] = 'Something went wrong!'; 
            }
        }else{
            $response['message'] = 'failed';    
             $response['error'] = 'User not found!';  
        }
        echo json_encode($response);
        exit;
    }
    
    function getBankList($user){
        $response = array();
        $sel_user_query = "SELECT * FROM tbl_customer_bankdetail WHERE customer_id = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $user);
        $sel_user->execute();
        $userDetails = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($userDetails)){
            $response['message'] ='ok';
            $response['user_bank_list'] = $userDetails;
        }else{
            $response['message'] = 'failed';
            $response['user_bank_list'] = array();
            $response['error'] = 'No banklist found!';
        }
        echo json_encode($response);
        exit;
    }
    
    function deleteBankList($details){
        $response = array();
        $sel_user_query = "DELETE FROM tbl_customer_bankdetail WHERE customer_id = ? AND id= ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $details['user_id']);
        $sel_user->bindParam(2, $details['bankid']);
        $sel_user->execute();
        $response['message'] ='ok';
        
         $sel_user_query = "SELECT * FROM tbl_customer_bankdetail WHERE customer_id = ?";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->bindParam(1, $details['user_id']);
        $sel_user->execute();
        $userDetails = $sel_user->fetchAll(PDO::FETCH_ASSOC);
             $response['user_bank_list'] = $userDetails;
            
        echo json_encode($response);
        exit;
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
            //$this->sql_error($query_res);
        }
        return $output;
    }
    
    function apply_promo($pdata){
        $promo = $pdata['code'];
        $time = time();
        $query  = "SELECT * FROM tbl_recharge_cach_bonus WHERE code='".$promo."' AND ".$time." between start_date and end_date";
        $query_res  = $this->conn->prepare($query);       
        $query_res->execute();
        $result = $query_res->fetch(PDO::FETCH_ASSOC);
        $respo = array();
        if($result){
            $respo['message'] = 'ok';
            $respo['data'] = $result;
        }else{
            $respo['message'] = 'failed';
            $respo['data'] = '';
        }
        echo json_encode($respo);
        exit;
    }
    
    function getPlayerInfo($id,$teamid=0){
         $query  = "SELECT *,(select sum(points) from tbl_cricket_match_players where player_unique_id=tcmp.uniqueid) as total_points FROM tbl_cricket_players as tcmp  WHERE uniqueid='".$id."'";
         
        $query_res  = $this->conn->prepare($query);       
        $query_res->execute();
        $result = $query_res->fetch(PDO::FETCH_ASSOC);
        $response = array();
        $pJson = json_decode($result['summary']);
        $result['json'] = $pJson;
        $result['credits'] = $pJson->credits;
            $result['country'] = $pJson->detail_data->country;
              $result['image'] = ($result['cp_image']!='')?$result['cp_image']:NO_IMG_URL_PLAYER;
               $result['total_points'] = $result['total_points'];
 $response['message'] = 'ok';
        $response['data'] = $result;
        //$response['json'] = $pJson;
        if($teamid>0){
            $strCondition = "1 AND match_progress IN ('R','IR') AND (team_1_id ='".$teamid."' OR team_2_id='".$teamid."')";
            $query  = "SELECT * FROM tbl_cricket_matches WHERE ".$strCondition;
            $query_res  = $this->conn->prepare($query);       
            $query_res->execute();
            $matches = $query_res->fetchAll(PDO::FETCH_ASSOC);
            $dt = array();
            foreach($matches as $match){
                $vdTeam = ($match['team_1_id'] == $teamid)?$match['team_2_id']:$match['team_1_id'];
                
                $vquery  = "SELECT * FROM tbl_cricket_teams WHERE id='".$vdTeam."'";
                $vquery_res  = $this->conn->prepare($vquery);       
                $vquery_res->execute();
                $vresult = $vquery_res->fetch(PDO::FETCH_ASSOC);
                
                $squery  = "SELECT * FROM tbl_cricket_match_players WHERE match_unique_id='".$match['unique_id']."' AND player_unique_id='".$id."' AND team_id='".$teamid."'";
                $squery_res  = $this->conn->prepare($squery);       
                $squery_res->execute();
                $sresult = $squery_res->fetch(PDO::FETCH_ASSOC);
                
                $pquery  = "SELECT * FROM tbl_cricket_match_players_stats WHERE match_unique_id='".$match['unique_id']."' AND player_unique_id='".$id."'";
                $pquery_res  = $this->conn->prepare($pquery);       
                $pquery_res->execute();
                $presult = $pquery_res->fetch(PDO::FETCH_ASSOC);
                
                $ent = array();
                $ent['vs'] =  $vresult['sort_name'];
                $ent['date_time'] =  date('M d, Y',$match['match_date']);
                $ent['selected_by'] =  isset($sresult['selected_by'])?$sresult['selected_by']:0;
                $ent['points'] =  isset($sresult['points'])?$sresult['points']:0;
                $ent['credits'] =  isset($sresult['credits'])?$sresult['credits']:0;
                $a =[];
                $query_num_rows = $pquery_res->rowCount();
                if($query_num_rows>0){
                     $aryData = array('id','player_unique_id','match_unique_id','updated','Being_Part_Of_Eleven_Value','Every_Run_Scored_Value','Dismiss_For_A_Duck_Value','Every_Boundary_Hit_Value','Every_Boundary_Hit_Value','Every_Six_Hit_Value','Half_Century_Value','Thirty_Runs_Value','Thirty_Runs_Value','Maiden_Over_Value','Four_Wicket_Value','Four_Wicket_Value','Five_Wicket_Value','Three_Wicket_Value','Two_Wicket_Value','Catch_Value','Catch_And_Bowled_Value','Stumping_Value','Run_Out_Value','Run_Out_Value','Run_Out_Catcher_Value','Run_Out_Thrower_Value','Run_Out_Thrower_Value','Strike_Rate_Value','Economy_Rate_Value','Century_Value','Wicket_Value');
                    $abc =array(); 
                       foreach($presult as $Key=>$Label)
                    {
                         if(in_array($Key,$aryData) )
                        {
                       $abc[$Key] = $Label;
                        }
                    }
                    
                    
                    /*foreach($presult as $Key=>$Label)
                    {
                         if(!in_array($Key,$aryData) )
                        {
                       $ent['breakup'][] =  array('key'=>str_replace('_',' ',$Key),'value'=>$abc[$Key.'_Value'],'label'=>$Label);
                        }
                    }*/
                                  $ent['breakup'] = array();
  }else{
                    $ent['breakup'] = array();
                }

                $dt[] = $ent;
            }
            $response['inof'] = $dt;
        }
        echo json_encode($response);
        exit;
    }
    
    function reffleaderboard($type='weekly',$apiType)
    {
        $response = array();
        $response['message'] = 'ok';
        $currentYear = date('Y');
        $dateStr = date('Y-m-d');
        $weekNumber = date("W", strtotime($dateStr));
        $first_day_this_month = date('Y-m-01'); 
        $last_day_this_month  = date('Y-m-t');
        $dates = $this->Start_End_Date_of_a_week($weekNumber,$currentYear);
        $sqlFindDatas ='';
        if($type == 'weekly'){
            $sqlFindDatas = " AND td.created BETWEEN '".strtotime($dates[0])."' AND '".strtotime($dates[1])."'";
        }

        if($type == 'Monthly'){
            $sqlFindDatas = " AND td.created BETWEEN '".strtotime($first_day_this_month)."' AND '".strtotime($last_day_this_month)."'";
        }

        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];
        
        $sqlFindData = "SELECT tcc.firstname as full_name,IFNULL(image,'".$no_imag_url."') as profile_image,(SELECT count(*) as total FROM `tbl_customers` as td WHERE td.`used_referral_user_id` = tcc.id ".$sqlFindDatas.") as totalreffer FROM `tbl_customers` as tcc   HAVING  totalreffer>0 ORDER BY totalreffer DESC";
        $sel_user = $this->conn->prepare($sqlFindData);
        $sel_user->execute();
        $userReffral = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        return $userReffral;

    }

    function risingleaderboard($type,$apiType){
        $response = array();
        $response['message'] = 'ok';
        $currentYear = date('Y');
        $dateStr = date('Y-m-d');
        $weekNumber = date("W", strtotime($dateStr));
        $first_day_this_month = date('Y-m-01'); 
        $last_day_this_month  = date('Y-m-t');
        $dates = $this->Start_End_Date_of_a_week($weekNumber,$currentYear);
        $sqlFindDatas ='';
        if($type == 'weekly'){
            $sqlFindDatas = " AND td.created BETWEEN '".strtotime($dates[0])."' AND '".strtotime($dates[1])."'";
        }

        if($type == 'Monthly'){
            $sqlFindDatas = " AND td.created BETWEEN '".strtotime($first_day_this_month)."' AND '".strtotime($last_day_this_month)."'";
        }

        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
       /// print_r($avtarData);
         $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];

        $sqlFindData = "SELECT tc.firstname as full_name,IFNULL(tc.image,'".$no_imag_url."') as profile_image,sum(td.amount) as totalreffer FROM `tbl_customer_wallet_histories` as td INNER JOIN `tbl_customers` as tc ON tc.id= td.customer_id WHERE type='CUSTOMER_WIN_CONTEST' ".$sqlFindDatas." group by td.customer_id ORDER BY totalreffer DESC";
        
        $sel_user = $this->conn->prepare($sqlFindData);
        $sel_user->execute();
        $userReffral = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        return $userReffral;
    }

    function getRefLeaderboard($type='weekly',$apiType){
        $response = array();
        $response['message'] = 'ok';
        $response['data'] = array();
        if($apiType == 'refferal'){
            $response['data']  = self::reffleaderboard($type,$apiType);
        }

        if($apiType == 'rising11'){
            $response['data']  = self::risingleaderboard($type,$apiType);
        }
        
        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);
        $no_imag_url = CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];

        $response['image_url'] = CUSTOMER_IMAGE_THUMB_URL;
        $response['no_image_url'] = $no_imag_url;
        echo json_encode($response);
        exit; 
    }
    
    public function send_notification_and_save($message,$user_id,$alert_message = APP_NAME.' notification center',$dbsave=true){

        //$this->setGroupConcatLimit();
        
        $query = "SELECT (SELECT GROUP_CONCAT(device_token)  FROM tbl_customer_logins  WHERE customer_id in ($user_id) and device_type='I') as device_tokens_ios, (SELECT GROUP_CONCAT(device_token)  FROM tbl_customer_logins  WHERE customer_id in ($user_id) and device_type='A') as device_tokens_android";
        $query_res  = $this->conn->prepare($query);
        $query_res->execute();
        $num_rows = $query_res->rowCount();
            
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
        $message['body']=$alert_message;
        $message['title']=$noti_title;
        $device_type ='A';
        if($device_type=="A"){
                $fields = array(
                    'registration_ids' => $registration_ids,
                    'data' => $message
                );

        }else{

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


        
         } 
         
        $fields = json_encode($fields);
        
        $headersY = array(
            'Authorization: key=' . $API_KEY,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($fields)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $result = curl_exec($ch);
        $err = curl_error($ch);
           /*  echo "<pre>";
         print_r($result);
         die;
   */   
        curl_close($ch);
        return $result;
     }
    
    function sendNotificationForLineUps($array){
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
        if(!empty($notifyToAllQueryResData)){
            
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
    
    function setStatus(){
        $en = new Entitysport();
        $date1 = strtotime(date('Y/m/d',strtotime("-1 days")));
        $date2 = strtotime(date('Y/m/d',strtotime("+1 days")));
        $sel_user_query = "SELECT tcm.*,tcs.name as series_name FROM tbl_cricket_matches as tcm INNER JOIN tbl_cricket_series as tcs ON tcs.id= tcm.series_id WHERE match_date BETWEEN ".$date1." AND ".$date2." AND (match_progress ='F' OR lineup_announced =0) AND tcm.status='A' and tcm.is_deleted='N'";
        $dy = array('1'=>'F','2'=>'R','3'=>'L','4'=>'A');
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
         /* foreach($user as $match){
            echo "<pre>";
            print_r(date('Y-m-d h:i:s A',$match['match_date']));
            echo "<br>";
        }
        die; */
 
        foreach($user as $match){
                $result2  = $en->fantasy_squade($match['unique_id']);
                $ppIds = implode(",",$result2);
                if(!empty($result2)){
                    $noti = array();
                    $noti['series_name'] = $match['series_name'];
                    $noti['match_name'] = $match['name'];
                    $query_match  = "UPDATE tbl_cricket_match_players set is_in_playing_squad = 1 WHERE player_unique_id IN (".$ppIds.") AND match_unique_id ='".$match['unique_id']."'";
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                    
                    $query_match  = "UPDATE tbl_cricket_matches set lineup_announced = 1 ,playing_squad_updated = 'Y', updated_at = '".time()."' WHERE unique_id =".$match['unique_id'];
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                    if($match['lineup_announced']==0){
                        $this->sendNotificationForLineUps($noti);
                    }
                }
                
                $result = $en->getMatchStatus($match['unique_id']);
                
                if($result['status'] == 'ok' && !empty($result['response'])){
                    $status = $result['response']['status'];
                    $sNote = $result['response']['status_note'];
                    if($status == '3' && time()>=$match['match_date']){
                        $sStatus = $dy[$status];
                        $sel_user_query = "UPDATE tbl_cricket_matches SET match_progress = ?,score_board_notes = ? where unique_id = ?";
                        $sel_user = $this->conn->prepare($sel_user_query);
                        $sel_user->bindParam(1, $sStatus);
                        $sel_user->bindParam(2, $sNote);
                        $sel_user->bindParam(3, $match['unique_id']);
                        $sel_user->execute();    
                    }
                }
        }
    }
    
    
        function setStatusfootball(){
        $en = new Entitysport();
        $date1 = strtotime(date('Y/m/d',strtotime("-1 days")));
        $date2 = strtotime(date('Y/m/d',strtotime("+1 days")));
        $sel_user_query = "SELECT tcm.*,tcs.name as series_name FROM tbl_cricket_matches as tcm INNER JOIN tbl_cricket_series as tcs ON tcs.id= tcm.series_id WHERE match_date BETWEEN ".$date1." AND ".$date2." AND (match_progress ='F' OR lineup_announced =0) AND tcm.status='A' and tcm.is_deleted='N' AND tcm.game_id=2";
        $dy = array('1'=>'F','2'=>'R','3'=>'L','4'=>'A');
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
         /* foreach($user as $match){
            echo "<pre>";
            print_r(date('Y-m-d h:i:s A',$match['match_date']));
            echo "<br>";
        }
        die; */
        foreach($user as $match){
                $result2data  = $en->fantasy_squade_football($match['unique_id']);
                
                $result2 = $result2data['player'];
                
                $ppIds = implode(",",$result2);
                if(!empty($result2)){
                    $noti = array();
                    $noti['series_name'] = $match['series_name'];
                    $noti['match_name'] = $match['name'];
                    $query_match  = "UPDATE tbl_cricket_match_players set is_in_playing_squad = 1 WHERE player_unique_id IN (".$ppIds.") AND match_unique_id ='".$match['unique_id']."'";
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                    
                    $query_match  = "UPDATE tbl_cricket_matches set lineup_announced = 1 ,playing_squad_updated = 'Y', updated_at = '".time()."' WHERE unique_id =".$match['unique_id'];
                    $query_match_res  = $this->conn->prepare($query_match);
                    $query_match_res->execute();
                    if($match['lineup_announced']==0){
                        $this->sendNotificationForLineUps($noti);
                    }
                }
                
                
                $result = $result2data['matchinfo'];
                echo '<pre>';
                              print_r($result);
  if(!empty($result)){
                    $status = $result['status'];
                    $sNote = $result['status_str'];
                    if($status == '3' && time()>=$match['match_date']){
                        $sStatus = $dy[$status];
                        $sel_user_query = "UPDATE tbl_cricket_matches SET match_progress = ?,score_board_notes = ? where unique_id = ?";
                        $sel_user = $this->conn->prepare($sel_user_query);
                        $sel_user->bindParam(1, $sStatus);
                        $sel_user->bindParam(2, $sNote);
                        $sel_user->bindParam(3, $match['unique_id']);
                        $sel_user->execute();    
                    }
                }
        }
    }

    public function live_match_cron() {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");
        
        $query_points  = "SELECT * FROM tbl_cricket_matches where match_progress='L' AND game_id=1";
        $query_points_res  = $this->conn->prepare($query_points);       
        $query_points_res->execute();
        $array_points = $query_points_res->fetchAll(PDO::FETCH_ASSOC);
        foreach($array_points as $queryRow){
           
        $time=time();
         
        $query_points  = "SELECT * FROM tbl_cricket_points where status='A' AND is_deleted='N' AND game_id=1 GROUP BY meta_key";
        $query_points_res  = $this->conn->prepare($query_points);       
        $query_points_res->execute();
        $array_points = $query_points_res->fetchAll(PDO::FETCH_ASSOC);
        $points=array();
        foreach($array_points as $key=>$l){
           $points[25][str_replace(" ","_",$l['meta_key'])]= $l['meta_value'];
        }
        
        $array = $queryRow; 
        $i=0;
        $output=array();
 
        $output[$i]['unique_id']= $array['unique_id'];
        $game_type_point =  $points[25];
    
        $Entity_object=new Entitysport();
        $api_data_array=$Entity_object->fantasy_summary($array['unique_id'],$game_type_point);
                        
                          if(!empty($api_data_array['innings'])){
                              $match_json = json_encode($api_data_array['innings']);
                              $query_match  = "UPDATE tbl_cricket_matches set match_scorecard=? WHERE unique_id=? ";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$match_json);
                            $query_match_res->bindParam(2,$array['unique_id']);
                            $query_match_res->execute(); 
                          }
                          $palyers=$api_data_array['players'];

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

                                $score_board_update=",team1_run='$team1_run', team1_wicket='$team1_wicket', team1_overs='$team1_overs',team2_run='$team2_run', team2_wicket='$team2_wicket',team2_overs='$team2_overs',score_board_notes='$score_board_notes'";
                            }
 $strmatch ='';
                                if($api_data_array['scorecard_data']['match_status']==2)
                                {
                                    
                                      $strmatch =',match_live_status=2';
                                }

                            $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=?".$match_progress_update.$score_board_update.$strmatch."  WHERE unique_id=? ";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$time);
                            $query_match_res->bindParam(2,$array['unique_id']);

                            if(!$query_match_res->execute()){
                                $this->sql_error($query_match_res);
                            }
                          }

                        if(!empty($palyers)){
                              
                              $deleteSql = 'Delete from tbl_cricket_match_players_stats where match_unique_id = ?';
                                $delete_data_query_res  = $this->conn->prepare($deleteSql);
                                $delete_data_query_res->bindParam(1,$array['unique_id']);
                                $delete_data_query_res->execute();
                              
                              $sqlInsert = 'INSERT INTO `tbl_cricket_match_players_stats` ( `match_unique_id`, `player_unique_id`, `Being_Part_Of_Eleven`, `Being_Part_Of_Eleven_Value`, `Every_Run_Scored`, `Every_Run_Scored_Value`, `Dismiss_For_A_Duck`, `Dismiss_For_A_Duck_Value`, `Every_Boundary_Hit`, `Every_Boundary_Hit_Value`, `Every_Six_Hit`, `Every_Six_Hit_Value`, `Half_Century`, `Half_Century_Value`, `Century`, `Century_Value`, `Thirty_Runs`, `Thirty_Runs_Value`, `Wicket`, `Wicket_Value`, `Maiden_Over`, `Maiden_Over_Value`, `Four_Wicket`, `Four_Wicket_Value`, `Five_Wicket`, `Five_Wicket_Value`, `Three_Wicket`, `Three_Wicket_Value`, `Two_Wicket`, `Two_Wicket_Value`, `Catch`, `Catch_Value`, `Catch_And_Bowled`, `Catch_And_Bowled_Value`, `Stumping`, `Stumping_Value`, `Run_Out`, `Run_Out_Value`, `Run_Out_Catcher`, `Run_Out_Catcher_Value`, `Run_Out_Thrower`, `Run_Out_Thrower_Value`, `Strike_Rate`, `Strike_Rate_Value`, `Economy_Rate`, `Economy_Rate_Value`, `updated`) VALUES ';
  $insert_values ='';
   foreach($palyers as $player_key=>$palyers_insert){
	   $ard =array();
	  $ard['match_unique_id']   = $array['unique_id'];
	$ard['player_unique_id']   = $player_key;
	
	$ard['Being_Part_Of_Eleven']   = $palyers_insert['Being_Part_Of_Eleven'];
	$ard['Being_Part_Of_Eleven_Value']   = $palyers_insert['Being_Part_Of_Eleven_Value'];
	$ard['Every_Run_Scored']   = $palyers_insert['Every_Run_Scored'];
	$ard['Every_Run_Scored_Value']   = $palyers_insert['Every_Run_Scored_Value'];
	$ard['Dismiss_For_A_Duck']   = $palyers_insert['Dismiss_For_A_Duck'];
	$ard['Dismiss_For_A_Duck_Value']   = $palyers_insert['Dismiss_For_A_Duck_Value'];
	$ard['Every_Boundary_Hit']   = $palyers_insert['Every_Boundary_Hit'];
	$ard['Every_Boundary_Hit_Value']   = $palyers_insert['Every_Boundary_Hit_Value'];
	$ard['Every_Six_Hit']   = $palyers_insert['Every_Six_Hit'];
	$ard['Every_Six_Hit_Value']   = $palyers_insert['Every_Six_Hit_Value'];
	$ard['Half_Century']   = $palyers_insert['Half_Century'];
	$ard['Half_Century_Value']   = $palyers_insert['Half_Century_Value'];
	$ard['Century']   = $palyers_insert['Century'];
	$ard['Century_Value']   = $palyers_insert['Century_Value'];
	$ard['Thirty_Runs']   = $palyers_insert['Thirty_Runs'];
	$ard['Thirty_Runs_Value']   = $palyers_insert['Thirty_Runs_Value'];
	$ard['Wicket']   = $palyers_insert['Wicket'];
	$ard['Wicket_Value']   = $palyers_insert['Wicket_Value'];
	$ard['Maiden_Over']   = $palyers_insert['Maiden_Over'];
	$ard['Maiden_Over_Value']   = $palyers_insert['Maiden_Over_Value'];
	$ard['Four_Wicket']   = $palyers_insert['Four_Wicket'];
	$ard['Four_Wicket_Value']   = $palyers_insert['Four_Wicket_Value'];
	$ard['Five_Wicket']   = $palyers_insert['Five_Wicket'];
	$ard['Five_Wicket_Value']   = $palyers_insert['Five_Wicket_Value'];
	$ard['Three_Wicket']   = $palyers_insert['Three_Wicket'];
	$ard['Three_Wicket_Value']   = $palyers_insert['Three_Wicket_Value'];
	$ard['Two_Wicket']   = $palyers_insert['Two_Wicket'];
	$ard['Two_Wicket_Value']   = $palyers_insert['Two_Wicket_Value'];
	$ard['Catch']   = $palyers_insert['Catch'];
	$ard['Catch_Value']   = $palyers_insert['Catch_Value'];
	$ard['Catch_And_Bowled']   = $palyers_insert['Catch_And_Bowled'];
	$ard['Catch_And_Bowled_Value']   = $palyers_insert['Catch_And_Bowled_Value'];
	$ard['Stumping']   = $palyers_insert['Stumping'];
	$ard['Stumping_Value']   = $palyers_insert['Stumping_Value'];
	$ard['Run_Out']   = $palyers_insert['Run_Out'];
	$ard['Run_Out_Value']   = $palyers_insert['Run_Out_Value'];
	$ard['Run_Out_Catcher']   = $palyers_insert['Run_Out_Catcher'];
	$ard['Run_Out_Catcher_Value']   = $palyers_insert['Run_Out_Catcher_Value'];
	$ard['Run_Out_Thrower']   = $palyers_insert['Run_Out_Thrower'];
	$ard['Run_Out_Thrower_Value']   = $palyers_insert['Run_Out_Thrower_Value'];
	$ard['Strike_Rate']   = $palyers_insert['Strike_Rate'];
	$ard['Strike_Rate_Value']   = $palyers_insert['Strike_Rate_Value'];
	$ard['Economy_Rate']   = $palyers_insert['Economy_Rate'];
	$ard['Economy_Rate_Value']   = $palyers_insert['Economy_Rate_Value'];
	$ard['updated']   = '\''.time().'\'';
	//$ard['dataupdate']   = '\''.date('Y-m-d h:i:s').'\'';
	//$ard['total_points']   = $palyers_insert['total_points'];
	
	$insert_values .='('.implode(',',array_values($ard)).'),';
	
 		$update_data_query  = "UPDATE tbl_cricket_match_players set points=?  WHERE player_unique_id=? AND match_unique_id=?";
		$update_data_query_res  = $this->conn->prepare($update_data_query);
		$update_data_query_res->bindParam(1,$palyers_insert['total_points']);
		$update_data_query_res->bindParam(2,$player_key);
		$update_data_query_res->bindParam(3,$array['unique_id']);
		if(!$update_data_query_res->execute()){
		    //$this->sql_error($update_data_query_res);
		}
		//$this->closeStatement($update_data_query_res);
    }


  $sqlInsert  .=rtrim($insert_values,',');
  
 $sqlInsertService =$this->conn->query($sqlInsert);
 
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

                                    $score_board_update=",team1_run='$team1_run',team1_wicket='$team1_wicket', team1_overs='$team1_overs',team2_run='$team2_run',team2_wicket='$team2_wicket',team2_overs='$team2_overs',score_board_notes='$score_board_notes'";
                                }
                                
                                $strmatch ='';
                                if(isset($api_data_array['scorecard_data']['match_status']) && $api_data_array['scorecard_data']['match_status']==2)
                                {
                                    
                                      $strmatch =',match_live_status=2';
                                }
                                $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=?".$match_progress_update.$score_board_update.$strmatch."  WHERE unique_id=? ";
                                $query_match_res  = $this->conn->prepare($query_match);
                                $query_match_res->bindParam(1,$time);
                                $query_match_res->bindParam(2,$array['unique_id']);
                                if(!$query_match_res->execute()){
                                    //print_r($query_match_res->errorInfo());
                                    $this->sql_error($query_match_res);
                                }

                              $this->closeStatement($query_match_res);

                            $this->update_point_and_rank($array['unique_id'],$array['id']);
                            }

                            $output[$i]['api_data_array']= $api_data_array;

                            $i++;
        }
    }




    public function live_match_cron_football() {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");
        
        $query_points  = "SELECT * FROM tbl_cricket_matches where match_progress='L' AND game_id=2";
        $query_points_res  = $this->conn->prepare($query_points);       
        $query_points_res->execute();
        $array_points = $query_points_res->fetchAll(PDO::FETCH_ASSOC);
        foreach($array_points as $queryRow){
           
        $time=time();
         
        $query_points  = "SELECT * FROM tbl_cricket_points where status='A' AND is_deleted='N' AND game_id=2 GROUP BY meta_key";
        $query_points_res  = $this->conn->prepare($query_points);       
        $query_points_res->execute();
        $array_points = $query_points_res->fetchAll(PDO::FETCH_ASSOC);
        $points=array();
        foreach($array_points as $key=>$l){
           $points[25][str_replace(" ","_",$l['meta_key'])]= $l['meta_value'];
        }
        
        $array = $queryRow; 
        $i=0;
        $output=array();
 
        $output[$i]['unique_id']= $array['unique_id'];
        $game_type_point =  $points[25];
    
        $Entity_object=new Entitysport();
        $api_data_array=$Entity_object->fantasy_summary_football($array['unique_id'],$game_type_point);

                          if(!empty($api_data_array['innings'])){
                              $match_json = json_encode($api_data_array['innings']);
                              $query_match  = "UPDATE tbl_cricket_matches set match_scorecard=? WHERE unique_id=? ";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$match_json);
                            $query_match_res->bindParam(2,$array['unique_id']);
                            $query_match_res->execute(); 
                          }
                          
                         
                          $palyers=$api_data_array['players'];
                     

                          if(empty($palyers)){

                            $output[$i]['players']="players is empty";
                            $output[$i]['api_data_array']=$api_data_array;
                            $i++; 
                          }
                            $match_progress_update= '';
                            if(!empty($api_data_array['man-of-the-match']) && is_array($api_data_array['man-of-the-match'])){
                                $match_progress_update=", match_progress='IR'";
                            }

                            $score_board_update='';
                        ///    print_r($api_data_array['scorecard_data']);
                            if(!empty($api_data_array['scorecard_data'])){
                                $team1_run=$api_data_array['scorecard_data']['team1_run'];
                                $team2_run=$api_data_array['scorecard_data']['team2_run'];
                                    $score_board_notes=$api_data_array['scorecard_data']['score_board_notes'];

                                $score_board_update=",team1_run='$team1_run',team2_run='$team2_run',score_board_notes='$score_board_notes'";
                            }
 $strmatch ='';
                                // if($api_data_array['scorecard_data']['match_status']==2)
                                // {
                                    
                                //       $strmatch =',match_live_status=2';
                                // }

                             $query_match  = "UPDATE tbl_cricket_matches set points_updated_at=?".$match_progress_update.$score_board_update.$strmatch."  WHERE unique_id=? ";

                            $query_match_res  = $this->conn->prepare($query_match);
                            $query_match_res->bindParam(1,$time);
                            $query_match_res->bindParam(2,$array['unique_id']);

                            if(!$query_match_res->execute()){
                                $this->sql_error($query_match_res);
                            }
                          

                        if(!empty($palyers)){
                              
                              $deleteSql = 'Delete from tbl_cricket_match_players_stats where match_unique_id = ?';
                                $delete_data_query_res  = $this->conn->prepare($deleteSql);
                                $delete_data_query_res->bindParam(1,$array['unique_id']);
                                $delete_data_query_res->execute();
                              
                              $sqlInsert = 'INSERT INTO `tbl_cricket_match_players_stats` ( `match_unique_id`, `player_unique_id`, `Being_Part_Of_Eleven`, `Being_Part_Of_Eleven_Value`, `Every_Run_Scored`, `Every_Run_Scored_Value`, `Dismiss_For_A_Duck`, `Dismiss_For_A_Duck_Value`, `Every_Boundary_Hit`, `Every_Boundary_Hit_Value`, `Every_Six_Hit`, `Every_Six_Hit_Value`, `Half_Century`, `Half_Century_Value`, `Century`, `Century_Value`, `Thirty_Runs`, `Thirty_Runs_Value`, `Wicket`, `Wicket_Value`, `Maiden_Over`, `Maiden_Over_Value`, `Four_Wicket`, `Four_Wicket_Value`, `Five_Wicket`, `Five_Wicket_Value`, `Three_Wicket`, `Three_Wicket_Value`, `Two_Wicket`, `Two_Wicket_Value`, `Catch`, `Catch_Value`, `Catch_And_Bowled`, `Catch_And_Bowled_Value`, `Stumping`, `Stumping_Value`, `Run_Out`, `Run_Out_Value`, `Run_Out_Catcher`, `Run_Out_Catcher_Value`, `Run_Out_Thrower`, `Run_Out_Thrower_Value`, `Strike_Rate`, `Strike_Rate_Value`, `Economy_Rate`, `Economy_Rate_Value`, `updated`) VALUES ';
  $insert_values ='';
   foreach($palyers as $player_key=>$palyers_insert){
	   $ard =array();
	  $ard['match_unique_id']   = $array['unique_id'];
	$ard['player_unique_id']   = $player_key;
	
	if(isset($palyers_insert['Being_Part_Of_Eleven']))
	{
	$ard['Being_Part_Of_Eleven']   = $palyers_insert['Being_Part_Of_Eleven'];
	$ard['Being_Part_Of_Eleven_Value']   = $palyers_insert['Being_Part_Of_Eleven_Value'];
	}else{
	 	$ard['Being_Part_Of_Eleven']   = $palyers_insert['Coming_on_as_a_substitute'];
	$ard['Being_Part_Of_Eleven_Value']   = $palyers_insert['Coming_on_as_a_substitute_value'];   
	}
	$ard['Every_Run_Scored']   = $palyers_insert['Goal_Scored'];
	$ard['Every_Run_Scored_Value']   = $palyers_insert['Goal_Scored_Value'];
	
	
	$ard['Dismiss_For_A_Duck']   = $palyers_insert['Yellow_card'];
	$ard['Dismiss_For_A_Duck_Value']   = $palyers_insert['Yellow_card_Value'];
	
	
	$ard['Every_Boundary_Hit']   = $palyers_insert['Red_card'];
	$ard['Every_Boundary_Hit_Value']   = $palyers_insert['Red_card_Value'];
	
	
	$ard['Every_Six_Hit']   = $palyers_insert['Own_Goal'];
	$ard['Every_Six_Hit_Value']   = $palyers_insert['Own_Goal_Value'];
	$ard['Half_Century']   = 0;
	$ard['Half_Century_Value']   = 0;
	
	$ard['Century']   = 0;
	$ard['Century_Value']   = 0;
	$ard['Thirty_Runs']   = 0;
	$ard['Thirty_Runs_Value']   = 0;
	$ard['Wicket']   = 0;
	$ard['Wicket_Value']   =0;
	$ard['Maiden_Over']   =0;
	$ard['Maiden_Over_Value']   = 0;
	$ard['Four_Wicket']   = 0;
	$ard['Four_Wicket_Value']   =0;
	$ard['Five_Wicket']   = 0;
	$ard['Five_Wicket_Value']   =0;
	$ard['Three_Wicket']   =0;
	$ard['Three_Wicket_Value']   =0;
	$ard['Two_Wicket']   =0;
	$ard['Two_Wicket_Value']   = 0;
	$ard['Catch']   = 0;
	$ard['Catch_Value']   =0;
	$ard['Catch_And_Bowled']   =0;
	$ard['Catch_And_Bowled_Value']   =0;
	$ard['Stumping']   = 0;
	$ard['Stumping_Value']   = 0;
	$ard['Run_Out']   = 0;
	$ard['Run_Out_Value']   =0;
	$ard['Run_Out_Catcher']   = 0;
	$ard['Run_Out_Catcher_Value']   = 0;
	$ard['Run_Out_Thrower']   = 0;
	$ard['Run_Out_Thrower_Value']   = 0;
	$ard['Strike_Rate']   = 0;
	$ard['Strike_Rate_Value']   = 0;
	$ard['Economy_Rate']   = 0;
	$ard['Economy_Rate_Value']   = 0;
	$ard['updated']   = '\''.time().'\'';
	//$ard['dataupdate']   = '\''.date('Y-m-d h:i:s').'\'';
	//$ard['total_points']   = $palyers_insert['total_points'];
	
	$insert_values .='('.implode(',',array_values($ard)).'),';
	
 		$update_data_query  = "UPDATE tbl_cricket_match_players set points=?  WHERE player_unique_id=? AND match_unique_id=?";
		$update_data_query_res  = $this->conn->prepare($update_data_query);
		$update_data_query_res->bindParam(1,$palyers_insert['total_points']);
		$update_data_query_res->bindParam(2,$player_key);
		$update_data_query_res->bindParam(3,$array['unique_id']);
		if(!$update_data_query_res->execute()){
		    //$this->sql_error($update_data_query_res);
		}
		//$this->closeStatement($update_data_query_res);
    }


  $sqlInsert  .=rtrim($insert_values,',');
  
 $sqlInsertService =$this->conn->query($sqlInsert);
 
                              

                                
                               

                            $this->update_point_and_rank($array['unique_id'],$array['id']);
                            }

                            $output[$i]['api_data_array']= $api_data_array;

                            $i++;
        }
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
        
    public function closeStatement($statement){
        if(!empty($statement)){
            $statement=null;
        }
    }
  public function gettimercdata($dates)
  {
    $date1 = time();
    $date2 = strtotime($dates);
    $mins = ($date2 - $date1) / 60;
    return $mins;
  }
  
  function getteamsettingdata($arypostdata)
  {
      $intGameType = $arypostdata['match_type'];
       $query = "SELECT * from tbl_cricket_team_setting where 1 AND game_id= $intGameType order by id asc";

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
 
    function getHomedata($user){
        $response = array();
        $strToken = isset($user['userFirebaseToken'])?$user['userFirebaseToken']:0;
        $strdevice = isset($user['userDeviceId'])?$user['userDeviceId']:"";
        if($strToken!=""){
             $sqlCheckBefore = 'Select * FROM tbl_customer_logins where customer_id='.$user['user_id'];
            $checkUser = $this->conn->prepare($sqlCheckBefore);
            $type = '1';
            $checkUser->execute();
            $checkUserRes = $checkUser->fetch(PDO::FETCH_ASSOC);
            if(isset($checkUserRes['id'])){
                 $sqlUpdate = 'DELETE FROM  tbl_customer_logins WHERE 1 AND customer_id='.$user['user_id'];
                $this->conn->query($sqlUpdate);
				 $sqlUserInsert = 'INSERT INTO tbl_customer_logins SET device_type="A",device_token=\''.$strToken.'\',device_id=\''.$strdevice.'\',customer_id=\''.$user['user_id'].'\'';
                $this->conn->query($sqlUserInsert);
            }else{
                $sqlUserInsert = 'INSERT INTO tbl_customer_logins SET device_type="A",device_token=\''.$strToken.'\',device_id=\''.$strdevice.'\',customer_id=\''.$user['user_id'].'\'';
                $this->conn->query($sqlUserInsert);
            }
            
            $response['tokenFoundOrNot'] = $strToken;
        }else{
            $response['tokenFoundOrNot'] = 'no';
        }
        $response['message'] = 'ok';
        $user_id = isset($user['user_id'])?$user['user_id']:0;
        $sel_user_query = "SELECT * FROM tbl_quotations where id = ? AND status='A' AND is_deleted='N'";
        $sel_user = $this->conn->prepare($sel_user_query);
        $type = '1';
        $sel_user->bindParam(1, $type);
        $sel_user->execute();
        $user = $sel_user->fetch(PDO::FETCH_ASSOC);
        if(!empty($user)){
            $response['discount_banner'] = ($user['image']!="")?QUOTATIONS_IMAGE_LARGE_URL.$user['image']:'';
        }else{
            $response['discount_banner'] = '';
        }
        
          $sel_user_query = "SELECT *,tcm.id as match_id,tcm.score_board_notes as score_board_notes,
        tcm.unique_id as unique_id,tcs.name as category,tcm.match_progress as status,tcm.match_date as matchdate,
        tctb.id as team2id,tct.id as team1id,tct.name as team1name,tctb.name as team2name,
        tct.sort_name as team1short,tctb.sort_name as team2short,tctb.logo as teab2logo,
        tct.logo as team1logo,(select count(*) from tbl_cricket_contest_matches as tccm where tcm.id=tccm.match_id) as totalcontest FROM tbl_cricket_matches as tcm 
        INNER JOIN tbl_cricket_series tcs ON tcm.series_id= tcs.id 
        INNER JOIN tbl_cricket_teams as tct ON tct.id = tcm.team_1_id 
        INNER JOIN tbl_cricket_teams as tctb ON tctb.id = tcm.team_2_id 
        where tcm.match_date > ? AND tcm.status = 'A'  and tcm.is_deleted='N' and tcm.game_id=1 order by match_date ASC";
        $sel_user = $this->conn->prepare($sel_user_query);
      $type = time();
        
        $sel_user->bindParam(1, $type);
        
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($user)){
            $matches = array();
            foreach($user as $match){
                $entity = array();
                $entity['category'] = $match['category'];
                 $entity['status'] = $match['status'];
                $entity['time'] = date('Y-m-d H:i:s',$match['matchdate']);
                $entity['match_id'] = $match['match_id'];
                $entity['unique_id'] = $match['unique_id'];
                $entity['lineup_announced'] = $match['lineup_announced'];
                $entity['totalcontest'] = $match['totalcontest'];
                $entity['lineup_expected'] = $match['lineup_expected'];
                       $entity['match_type'] = $match['game_id'];
         $entity['highest_winning'] = $match['highest_winning'];
                $entity['score_board_notes'] = $match['score_board_notes'];
                $entity['tag_category'] = $match['tag_category'];
                $entity['minute'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']));
                $entity['seconds'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']))*60;
 
                $entity['match_time'] = $match['matchdate'];
                $team1Name = (strlen($match['team1name']) >10)?substr($match['team1name'],0,10).'.':$match['team1name'];
                $team2Name = (strlen($match['team2name']) >10)?substr($match['team2name'],0,10).'.':$match['team2name'];
                
                $entity['teama'] = array('id'=>$match['team1id'],'name'=>$team1Name,'short_name'=>$match['team1short'],'logo'=>$match['team1logo']);
                $entity['teamb'] = array('id'=>$match['team2id'],'name'=>$team2Name,'short_name'=>$match['team2short'],'logo'=>$match['teab2logo']);
                $matches[] = $entity;
            }
            $response['matches'] = $matches;
        }else{
            $response['matches'] = array();
        }
        
        
        $sel_user_query = "SELECT *,tcm.id as match_id,tcm.score_board_notes as score_board_notes,
        tcm.unique_id as unique_id,tcs.name as category,tcm.match_progress as status,tcm.match_date as matchdate,
        tctb.id as team2id,tct.id as team1id,tct.name as team1name,tctb.name as team2name,
        tct.sort_name as team1short,tctb.sort_name as team2short,tctb.logo as teab2logo,
        tct.logo as team1logo,(select count(*) from tbl_cricket_contest_matches as tccm where tcm.id=tccm.match_id) as totalcontest FROM tbl_cricket_matches as tcm 
        INNER JOIN tbl_cricket_series tcs ON tcm.series_id= tcs.id 
        INNER JOIN tbl_cricket_teams as tct ON tct.id = tcm.team_1_id 
        INNER JOIN tbl_cricket_teams as tctb ON tctb.id = tcm.team_2_id 
        where tcm.match_date > ? AND tcm.status = 'A'  and tcm.is_deleted='N' and tcm.game_id=2 order by match_date ASC";
        $sel_user = $this->conn->prepare($sel_user_query);
      $type = time();
        
        $sel_user->bindParam(1, $type);
        
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($user)){
            $matches = array();
            foreach($user as $match){
                $entity = array();
                $entity['category'] = $match['category'];
                 $entity['status'] = $match['status'];
                $entity['time'] = date('Y-m-d H:i:s',$match['matchdate']);
                $entity['match_id'] = $match['match_id'];
                $entity['unique_id'] = $match['unique_id'];
                $entity['lineup_announced'] = $match['lineup_announced'];
                $entity['totalcontest'] = $match['totalcontest'];
                $entity['lineup_expected'] = $match['lineup_expected'];
                                    $entity['match_type'] = $match['game_id'];
   $entity['highest_winning'] = $match['highest_winning'];
                $entity['score_board_notes'] = $match['score_board_notes'];
                $entity['tag_category'] = $match['tag_category'];
                $entity['minute'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']));
                $entity['seconds'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']))*60;
 
                $entity['match_time'] = $match['matchdate'];
                $team1Name = (strlen($match['team1name']) >10)?substr($match['team1name'],0,10).'.':$match['team1name'];
                $team2Name = (strlen($match['team2name']) >10)?substr($match['team2name'],0,10).'.':$match['team2name'];
                
                $entity['teama'] = array('id'=>$match['team1id'],'name'=>$team1Name,'short_name'=>$match['team1short'],'logo'=>$match['team1logo']);
                $entity['teamb'] = array('id'=>$match['team2id'],'name'=>$team2Name,'short_name'=>$match['team2short'],'logo'=>$match['teab2logo']);
                $matches[] = $entity;
            }
            $response['football'] = $matches;
        }else{
            $response['football'] = array();
        }
        
        
        
        
        
         $sel_user_query = "SELECT *,tcm.id as match_id,tcm.score_board_notes as score_board_notes,
        tcm.unique_id as unique_id,tcs.name as category,tcm.match_progress as status,tcm.match_date as matchdate,
        tctb.id as team2id,tct.id as team1id,tct.name as team1name,tctb.name as team2name,
        tct.sort_name as team1short,tctb.sort_name as team2short,tctb.logo as teab2logo,
        tct.logo as team1logo,(select count(*) from tbl_cricket_contest_matches as tccm where tcm.id=tccm.match_id) as totalcontest FROM tbl_cricket_matches as tcm 
        INNER JOIN tbl_cricket_series tcs ON tcm.series_id= tcs.id 
        INNER JOIN tbl_cricket_teams as tct ON tct.id = tcm.team_1_id 
        INNER JOIN tbl_cricket_teams as tctb ON tctb.id = tcm.team_2_id 
        where tcm.match_date > ? AND tcm.status = 'A'  and tcm.is_deleted='N' and tcm.game_id=3 order by match_date ASC";
        $sel_user = $this->conn->prepare($sel_user_query);
      $type = time();
        
        $sel_user->bindParam(1, $type);
        
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($user)){
            $matches = array();
            foreach($user as $match){
                $entity = array();
                $entity['category'] = $match['category'];
                 $entity['status'] = $match['status'];
                $entity['time'] = date('Y-m-d H:i:s',$match['matchdate']);
                $entity['match_id'] = $match['match_id'];
                $entity['unique_id'] = $match['unique_id'];
                $entity['lineup_announced'] = $match['lineup_announced'];
                $entity['totalcontest'] = $match['totalcontest'];
                $entity['lineup_expected'] = $match['lineup_expected'];
                            $entity['match_type'] = $match['game_id'];
           $entity['highest_winning'] = $match['highest_winning'];
                $entity['score_board_notes'] = $match['score_board_notes'];
                $entity['tag_category'] = $match['tag_category'];
                $entity['minute'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']));
                $entity['seconds'] = self::gettimercdata(date('Y-m-d H:i:s',$match['matchdate']))*60;
 
                $entity['match_time'] = $match['matchdate'];
                $team1Name = (strlen($match['team1name']) >10)?substr($match['team1name'],0,10).'.':$match['team1name'];
                $team2Name = (strlen($match['team2name']) >10)?substr($match['team2name'],0,10).'.':$match['team2name'];
                
                $entity['teama'] = array('id'=>$match['team1id'],'name'=>$team1Name,'short_name'=>$match['team1short'],'logo'=>$match['team1logo']);
                $entity['teamb'] = array('id'=>$match['team2id'],'name'=>$team2Name,'short_name'=>$match['team2short'],'logo'=>$match['teab2logo']);
                $matches[] = $entity;
            }
            $response['basketball'] = $matches;
        }else{
            $response['basketball'] = array();
        }
        
   /*     $myMatches = array();
        $liveMathces = $this->get_customer_matches($user_id,'L');
        $upcoming = $this->get_customer_matches($user_id,'F');
        if(is_array($liveMathces) && !empty($liveMathces)){
            $myMatches = $liveMathces;
            if(is_array($upcoming) && !empty($upcoming)){
                $myMatches = array_merge($upcoming,$liveMathces);
            }
        }elseif(is_array($upcoming) && !empty($upcoming)){
            $myMatches = $upcoming;
        }
        */
        $slider = array();
        //SLIDER_IMAGE_THUMB_URL
        $sel_user_query = "SELECT * FROM tbl_sliders where is_deleted = ? and status='A'";
        $sel_user = $this->conn->prepare($sel_user_query);
        $type = 'N';
        $sel_user->bindParam(1, $type);
        $sel_user->execute();
        $user = $sel_user->fetchAll(PDO::FETCH_ASSOC);
 
        $response['slider'] = $user;
        $response['slider_url'] = SLIDER_IMAGE_LARGE_URL;
        
     ///   $response['mymatch'] = $myMatches;
        
        $insta = "";$face = "";$you = "";
        $yoArray = array('facebook','instagram','youtube');
        foreach($yoArray as $keyType){
            $sel_user_query = "SELECT * FROM tbl_settings where tbl_settings.key = ?";
            $sel_user = $this->conn->prepare($sel_user_query);
            $type = $keyType;
            $sel_user->bindParam(1, $type);
            $sel_user->execute();
            $user = $sel_user->fetch(PDO::FETCH_ASSOC);
            if(!empty($user)){
                if($keyType == 'facebook'){
                    $face = $user['value'];
                }
                if($keyType == 'instagram'){
                    $insta = $user['value'];
                }
                if($keyType == 'youtube'){
                    $you = $user['value'];
                }
            }
        }
        $response['facebook'] = $face;
        $response['instagram'] = $insta;
        $response['youtube'] = $you;
        $response['user'] = $this->getUpdatedProfileData($user_id);
        if(empty( $response['user']))
        {
            $response['user'] =null;
        }
 
        
               $sel_user_query = "SELECT * FROM tbl_states where status = 'A' ORDER BY name";
            $resStateList = $this->conn->prepare($sel_user_query);
               $resStateList->execute();
            $user = $resStateList->fetchAll(PDO::FETCH_ASSOC);
           $response['statelist'] = $user;
         
           $sel_user_query = "SELECT * FROM tbl_notifications where is_seen = '0' AND is_promotional='0' AND users_id=$user_id";
           $resStateList = $this->conn->prepare($sel_user_query);
              $resStateList->execute();
           $user = $resStateList->rowCount(PDO::FETCH_ASSOC);
          $response['notificationcount'] = $user;

          
           

       echo json_encode($response);
        exit;
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
    
    public function getUpdatedProfileData($user_id) {

           $query = "SELECT tc.follower_count,
            tc.following_count,
            tc.post_count,
            tc.team_name,
            tc.team_change,
            tc.winning_wallet,
            tc.deposit_wallet,
            tc.bonus_wallet,
            tc.slug, 
            tc.firstname,
            tc.lastname,
            tc.email, 
            tc.is_social,
            tc.social_type,
            tc.social_id,
            tc.image,
            tc.is_deleted,
            tc.external_image,
            tc.country_mobile_code,
            tc.phone,
            tc.referral_code,
            tc.is_phone_verified,
            tc.is_email_verified ,
            tc.dob,
             tc.gender,
            tc.addressline1,
            tc.addressline2,
            tc.pincode,
            tc.city as city_name,
            tcp.pain_number,
            tcp.name as pan_name,
            tcp.dob as pan_dob,
            tcp.status as pan_status,
            tcp.image as pan_image,
            tcp.state as pan_state,
            tcp.reason as pan_reason,
            IFNULL(tcp.id,0) as paincard_id,
            tcbd.account_number as bank_account_number,
            tcbd.name as account_holder_name,
            tcbd.ifsc as bank_ifsc,
            tcbd.status as bank_status,
            tcbd.image as bank_image,
            tcbd.reason as bank_reason,
            IFNULL(tcbd.id,0) as bankdetail_id,
            IFNULL(tbl_countries.id,0) as country_id,
            tbl_countries.name as country_name,
            IFNULL(tbl_states.id,0) as state_id ,
            tbl_states.name as state_name,
            (SELECT IFNULL(sum(amount),0) from tbl_withdraw_requests where (status='P' OR status='H' OR status='RP') AND customer_id=?) as pending_wid_amount 
            FROM tbl_customers tc 
            LEFT JOIN tbl_countries ON tbl_countries.id = tc.country 
            LEFT JOIN tbl_states ON tbl_states.id=tc.state 
            LEFT JOIN tbl_customer_paincard tcp ON tcp.id=tc.paincard_id 
            LEFT JOIN tbl_customer_bankdetail tcbd ON tcbd.id=tc.bankdetail_id 
            WHERE tc.id=? and tc.is_deleted='N'";
         $query_res = $this->conn->prepare($query);
        $query_res->bindParam(1, $user_id);
        $query_res->bindParam(2, $user_id);

        $avtarQuery = 'Select * FROM tbl_customer_avatars where is_deleted="N" AND status ="A" Order by id DESC Limit 1';
        $avtar_res = $this->conn->prepare($avtarQuery);
        $avtar_res->execute();
        $avtarData = $avtar_res->fetch(PDO::FETCH_ASSOC);

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
            
            $output['referral_code'] = $profiledata['referral_code'];
            $output['is_phone_verified'] = $profiledata['is_phone_verified'];  
            $output['is_email_verified'] = $profiledata['is_email_verified'];                     
            $output['image'] = !empty($profiledata['image']) ?CUSTOMER_IMAGE_THUMB_URL.$profiledata['image'] : CUSTOMER_IMAGE_THUMB_URL.$avtarData['image'];

            if(!empty($profiledata['external_image'])){
                $output['image']=$profiledata['external_image'];
            }

            $output['dob'] = $profiledata['dob'];
                  $output['gender'] = $profiledata['gender'];
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

			$output['wallet']  = array('winning_wallet'=>$actual_winning_balance, 'bonus_wallet'=>$profiledata['bonus_wallet'], 'deposit_wallet'=>$profiledata['deposit_wallet'], 'pending_wid_amount'=>$profiledata['pending_wid_amount']);
			
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

            $JOIN_CONTEST_MESSAGE="By joining this contest, you accept gully11's T&C and confirm that you are not a resident of Assam, Odisha, Telangana, Nagaland or Sikkim.";

            $PROFILE_UPDATE_MESSAGE="To play in gully11's pay-to-play contests, you need to be 18 years or above, and not a resident of Assam, Odisha, Telangana, Nagaland or Sikkim.";

			$output['settings']=array('WITHDRAW_AMOUNT_MIN'=>$MIN_WITHDRAWALS, 'WITHDRAW_AMOUNT_MAX'=>$MAX_WITHDRAWALS, 'CASH_BONUS_PERCENTAGES'=>$CASH_BONUS_PERCENTAGES,'WINNING_BREAKUP_MESSAGE'=>$WINNING_BREAKUP_MESSAGE,'JOIN_CONTEST_MESSAGE'=>$JOIN_CONTEST_MESSAGE,'PROFILE_UPDATE_MESSAGE'=>$PROFILE_UPDATE_MESSAGE);
			
			
        }else{
			$this->sql_error($query_res);
		}
        return $output;
    }
    
    function getWeeklyLeaderboard(){
        $currentYear = date('Y');
        $totalWeeks = date("W",strtotime('28th December '.$currentYear));
        $satrtEndDates = array();
        $output = array();
        for($i=1;$i<$totalWeeks;$i++){
            $satrtEndDates[$i] = $this->Start_End_Date_of_a_week($i,$currentYear);
        }
        foreach($satrtEndDates as $weekCount=>$weekDates){
            $start = strtotime($weekDates[0]);
            $end = strtotime($weekDates[1]);
            $query = "SELECT * FROM tbl_cricket_customer_contests as tccc INNER JOIN tbl_customers as tc on tc.id=tccc.customer_id INNER JOIN tbl_cricket_matches as tcm ON tcm.id = tccc.match_unique_id WHERE tccc.created BETWEEN '".$start."' AND '".$end."' ORDER BY new_rank ASC";

            $query_res = $this->conn->prepare($query);
            //$query_res->bindParam(1, $start);
            //$query_res->bindParam(2, $end);
            $ent = array();
            if ($query_res->execute()) {
                $data = $query_res->fetchAll(PDO::FETCH_ASSOC);
                $ent['dates'] = $weekDates;
                $ent['result'] = $data;
            }
            $output[$weekCount] = $ent;
        }
        $respo = array('message'=>'ok','data'=>$output);
        echo json_encode($respo);
        exit;
        
    }
    
    
    function getWeeks($month,$year){
	$month = intval($month);				//force month to single integer if '0x'
	$suff = array('st','nd','rd','th','th','th'); 		//week suffixes
	$end = date('t',mktime(0,0,0,$month,1,$year)); 		//last date day of month: 28 - 31
	$start = date('w',mktime(0,0,0,$month,1,$year)); 	//1st day of month: 0 - 6 (Sun - Sat)
	$last = 7 - $start; 					//get last day date (Sat) of first week
	$noweeks = ceil((($end - ($last + 1))/7) + 1);		//total no. weeks in month
	$output = array();						//initialize string		
	$monthlabel = str_pad($month, 2, '0', STR_PAD_LEFT);
	for($x=1;$x<$noweeks+1;$x++){	
		if($x == 1){
			$startdate = "$year-$monthlabel-01";
			$day = $last - 6;
		}else{
			$day = $last + 1 + (($x-2)*7);
			$day = str_pad($day, 2, '0', STR_PAD_LEFT);
			$startdate = "$year-$monthlabel-$day";
		}
		if($x == $noweeks){
			$enddate = "$year-$monthlabel-$end";
		}else{
			$dayend = $day + 6;
			$dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
			$enddate = "$year-$monthlabel-$dayend";
		}
		$output[] = array($startdate,$enddate);	
	}
	return $output;
}
    
    function Start_End_Date_of_a_week($week, $year)
    {
        $time = strtotime("1 January $year", time());
    	$day = date('w', $time);
    	$time += ((7*$week)+1-$day)*24*3600;
    	$dates[0] = date('Y-n-j', $time);
    	$time += 6*24*3600;
    	$dates[1] = date('Y-n-j', $time);
    	return $dates;
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
    
    public function get_customer_matches($customer_id,$match_progress) {

        if($match_progress=="R"){

            $string=" AND tcm.match_progress IN ('R','AB')";

        }elseif($match_progress=="L"){

            $string=" AND tcm.match_progress IN ('L','IR')";

        }else{
              $string=" AND tcm.match_progress IN ('$match_progress')";
        }

       $query = "SELECT tcm.playing_squad_updated as playing_squad_updated,tcm.match_progress as match_progress, tcm.unique_id as match_unique_id, tcm.name as match_name, tcm.match_date as match_date, tcm.close_date as close_date, tcm.match_limit as match_limit, tcm.id as id, tcs.name as series_name, tcs.id as series_id, tgt.name as gametype_name, tgt.id as gametype_id, tct_one.name as team_name_one,tct_one.sort_name as team_sort_name_one, tct_one.id as team_id_one, tct_one.logo as team_image_one, tct_two.name as team_name_two,tct_two.sort_name as team_sort_name_two, tct_two.id as team_id_two, tct_two.logo as team_image_two,(SELECT count(DISTINCT match_contest_id) FROM tbl_cricket_customer_contests where match_unique_id=tcm.unique_id AND customer_id=?) as contest_count FROM tbl_cricket_matches tcm LEFT JOIN tbl_cricket_series tcs ON (tcm.series_id=tcs.id) LEFT JOIN tbl_game_types tgt ON (tcm.game_type_id=tgt.id) LEFT JOIN tbl_cricket_teams tct_one ON (tcm.team_1_id=tct_one.id) LEFT JOIN tbl_cricket_teams tct_two ON (tcm.team_2_id=tct_two.id) WHERE tcm.status='A' AND tcm.is_deleted='N' $string AND tcm.unique_id IN (select match_unique_id FROM tbl_cricket_customer_contests WHERE customer_id=?)";
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
                            $match['category'] = $matchdata['series_name'];
                            $match['match_date'] = $matchdata['match_date'];
                            $match['close_date'] = $matchdata['close_date'];
                            $match['match_progress'] = $matchdata['match_progress'];
                            $match['server_date'] = $current_time;
                            $match['match_limit'] = $matchdata['match_limit'];
                            $match['contest_count'] = $matchdata['contest_count'];
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
                            $team1['image']=!empty($matchdata['team_image_one']) ? $matchdata['team_image_one'] : NO_IMG_URL_TEAM;
                            $match['team1'] = $team1;



                            $team2=array();
                            $team2['id']=$matchdata['team_id_two'];
                            $team2['name']=$matchdata['team_name_two'];
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

    public function base64_encode($slug){
        $new_slug=base64_encode($slug);
        $new_slug=$new_slug."////AA";
        return $new_slug;
    }
    
    public function paymenttoken($aryPostData){
            
$response =array();
$sel_user_query = "INSERT INTO tbl_tem_payment (tp_user_id,tp_amount,tp_status) VALUES (?,?,?)";
$status = 0;
$sel_user = $this->conn->prepare($sel_user_query);
$sel_user->bindParam(1, $aryPostData['user_id']);
$sel_user->bindParam(2, $aryPostData['amount']);
$sel_user->bindParam(3, $status);
$sel_user->execute(); 
$orderId =  $this->conn->lastInsertId();
    
    
    
    /* $vars['orderId'] = 1001;
        $vars['orderAmount'] = 10;
         $vars['orderCurrency'] = 'INR';
         
         $js = json_encode($vars);
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://test.cashfree.com/api/v2/cftoken/order");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$js);  //Post Fields
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = [
'Content-Type: application/json',
'x-client-id: 543431ec601fe848b5451e54d34345',
'x-client-secret: e67cc25f835593e5c0ef28d17a04ac742e151613'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$server_output = curl_exec ($ch);

curl_close ($ch);

print  $server_output ;*/
	        $customer_detail = $this->getUser($aryPostData['user_id']);

if($aryPostData['paymentmethod']==='RAZORPAYN' || $aryPostData['paymentmethod']==='RAZORPAY')
{
   $responsenew =  self::phonepeupi($aryPostData,$orderId,$aryPostData['user_id'],$customer_detail['phone']);
   $datajson = json_decode($responsenew);
   if($datajson->success)
   {
       
       if(isset($datajson->data->instrumentResponse->redirectInfo->url))
       {
$response['message']='ok';
$response['ORDER_ID']=$orderId;
$response['user_id']=$aryPostData['user_id'];
$response['amount']=$aryPostData['amount'];
$response['url']=$datajson->data->instrumentResponse->redirectInfo->url;
    }else{
        $response['message']='failed';
$response['ORDER_ID']=$orderId;
$response['user_id']=$aryPostData['user_id'];
$response['amount']=$aryPostData['amount'];
$response['url']="";
    }
   }else{
       
       
$response['message']='failed';
$response['ORDER_ID']=$orderId;
$response['user_id']=$aryPostData['user_id'];
$response['amount']=$aryPostData['amount'];
$response['url']="";
   }
}else{
    
$response['message']='ok';
$response['ORDER_ID']=$orderId;
$response['user_id']=$aryPostData['user_id'];
$response['amount']=$aryPostData['amount'];
	$response['url']=APP_URL.'payment/request.php?amount='.$response['amount'].'&user_id='.$response['user_id'].'&id='.$orderId;
		 $responsenew =  self::createpaymentrequest($customer_detail['firstname'],$customer_detail['email'],$customer_detail['phone'],$orderId,
$aryPostData['amount']);
	$data = json_decode($responsenew);
if($data->status==1)
{
$response['url']=$data->data->payment_url;
}else{
 $response['message']='failed';
$response['ORDER_ID']=$orderId;
$response['user_id']=$aryPostData['user_id'];
$response['amount']=$aryPostData['amount'];
$response['url']="";
}
	

}

        echo json_encode($response);
        exit;
    }
    
    
	 function updatepaymentstatus()
    {
        $postData =$_POST;
       //    $sel_user_query = "UPDATE tbl_tem_payment SET payment_data='".json_encode($postData)."' WHERE 1 AND tp_id=1";
       /// $sel_user = $this->conn->prepare($sel_user_query);
       /// $sel_user->execute();
            if($postData['client_txn_id'] && $postData['client_txn_id']>0 && $postData['status']=='success')
                {
				$orderId = $postData['client_txn_id'];
                    $sel_user_query = "UPDATE tbl_tem_payment SET tp_status=1 WHERE 1 AND tp_id=$orderId";
        $sel_user = $this->conn->prepare($sel_user_query);
        $sel_user->execute();


                }

        die;
    }
	 function createpaymentrequest($customername,$customeremail,$customermobile,
    $client_txn_id,$amount)
    {
        $strJson ='{
  "key": "ca004657-bac8-4ebc-abb3-a2d4aa5f4e23",
  "client_txn_id": "'.$client_txn_id.'",
  "amount": "'.$amount.'",
  "p_info": "Add Balance",
  "customer_name": "'.$customername.'",
  "customer_email": "'.$customeremail.'",
  "customer_mobile": "'.$customermobile.'",
  "redirect_url": "https://my11option.com/payment/message.php",
  "udf1": "",
  "udf2": "",
  "udf3": ""
}';
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://merchant.upigateway.com/api/create_order',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$strJson,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
return $response;
    }
    
	
	
   public function phonepeupi($aryPostData,$orderId,$userid,$phone)
    {
      
          $strRedirect = "https://my11option.com/payment/phoneperedirect.php?order=".$orderId;

       $strWebbase64 ='{
  "merchantId": "'.MID.'",
  "merchantTransactionId": "'.$orderId.'",
  "merchantUserId": "'.$userid.'",
  "amount": '.($aryPostData['amount']*100).',
  "redirectUrl": "'.$strRedirect.'",
  "redirectMode": "POST",
  "callbackUrl": "https://my11option.com/payment/phoneresponse.php",
  "mobileNumber": "'.$phone.'",
  "paymentInstrument": {
    "type": "PAY_PAGE"
  }
}';
		
//echo API_HOST.'pg/v1/pay';
    $strBase64 = base64_encode($strWebbase64);
    $strKey = MID_KEY;
    $data = $strBase64."/pg/v1/pay".$strKey;
$hash =  hash('sha256', $data). "###" . 1;
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => API_HOST.'/pg/v1/pay',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "request":"'.$strBase64.'"
}',
  CURLOPT_HTTPHEADER => array(
    'X-VERIFY: '.$hash,
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
	   
return $response;
    }
    public function base64_decode($slug){

        $new_slug=explode('////',$slug);
        $decoded_slug=base64_decode($new_slug[0]);
        return $decoded_slug;
        
    }
    
    public function pr($slug){

       echo '<pre>';
       print_r($slug);
        echo '</pre>';
      
    }
    
    function seconds2human($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
    
    }
    
}