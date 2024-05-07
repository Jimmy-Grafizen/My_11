<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MY_controller extends CI_Controller {

	function setView($page,$data){

      /*  $branchId = $this->input->cookie('BID', TRUE);
        $MenuItemQuery = $this->common_model->getmenuitemsByBid($branchId);
        $data['mainCategory'] =$MenuItemQuery;*/
		  $this->template->write_view('contents', $page, $data);
        $this->template->render();
	}

	function loadView($page,$data){
       /* $branchId = $this->input->cookie('BID', TRUE);
        $MenuItemQuery = $this->common_model->getmenuitemsByBid($branchId);
        $data['mainCategory'] =$MenuItemQuery;*/
		$this->load->view($page,$data);
	}
	
		/*
	@upload_files
	@params config [type=array] [This variable contains the list of file configurations]
	@params file_name [type=string] [This variable contains the key of $_FILES]
	@return [type=array]
	*/
	public function upload_files($config, $file_name){
		$result = array();
		if(IMAGE_UPLOAD_TYPE=="LOCAL"){
			$config['file_ext_tolower']	= TRUE;
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
		////	print_r($config);
			if(!$this->upload->do_upload($file_name))
			{
				$result = array("status"=>"error", "data"=>$this->upload->display_errors("<p>", "</p>"));
			}
			else
			{
				$result = array("status"=>"success", "data"=>$this->upload->data());
			}
		}
		else{
			//validating files first
			$allowed_extensions = explode("|", $config['allowed_types']);
			$allowed_size = $config['max_size'];
			$ext = strtolower(pathinfo($config['file_name'], PATHINFO_EXTENSION));
			if(!in_array($ext, $allowed_extensions)){
				$result = array("status"=>"failed", "data"=>"Please select a file with following extensions only: ".implode(', ', $allowed_extensions));
			}
			else if($_FILES[$file_name]['size']>($allowed_size*1024)){
				$result = array("status"=>"failed", "data"=>"Please select a file with size of size $allowed_size KB or less only.");
			}
			else{
				$result = $this->uploadOnAWS($config, $_FILES[$file_name]['tmp_name']);
			}
		}
		return $result;
	}
	
	protected function uploadOnAWS($params, $source){
		$ext = strtolower(pathinfo($params['file_name'], PATHINFO_EXTENSION));
		// Include the SDK using the Composer autoloader
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
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
		    $mime = finfo_file($finfo, $source);

				$res = $client->putObject(array(
					'Bucket'		=>	$bucket,
					'Key' 			=> 	$key,
					'SourceFile' 	=> 	$source,
					'StorageClass' 	=> 	'STANDARD',//'REDUCED_REDUNDANCY',
					'ACL'    		=> 	'public-read',
					'ContentType'   => 	$mime,
					//'ContentType'   => 	isset($this->mime_types[$ext])?$this->mime_types[$ext]:'',
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
	
	protected function removeFiles($files){
		if(!empty($files)){
			if(IMAGE_UPLOAD_TYPE=="LOCAL"){			
				foreach($files as $file){
					if(!empty($file) && is_file($file)){
						unlink($file);
					}
				}
			}
			else{
				// Include the SDK using the Composer autoloader
				require_once AWS_LIB;
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
				foreach($files as $file){
					if(!empty($file)){
						try{
							$result = $client->deleteObject(array(
								'Bucket' => $bucket,
								'Key'    => $file
							));
						}
						catch (S3Exception $e){
							//do nothing
						}
					}
				}
			}
		}
		return true;
	}
	
	public function resize_save_image($file_name, $file_path, $current_path, $save_path){
		$res = array("file_name"=>"", "full_path"=>"");
		if(IMAGE_UPLOAD_TYPE=="BUCKET"){
			$this->resize_from_url($file_path, 150, 150, LOCAL_THUMB_PATH);
			
			$params = array("file_name"=>$file_name, "upload_path"=>$save_path);
			
			$response = $this->uploadOnAWS($params, LOCAL_THUMB_PATH.$file_name);
			if($response["status"]=="success"){
				$r = is_file(LOCAL_THUMB_PATH.$file_name)?unlink(LOCAL_THUMB_PATH.$file_name):true;
				$res = array("file_name"=>$file_name, "full_path"=>$response["data"]["full_path"]);
			}
		}
		else{
			$this->resize($file_name, 150, 150, $current_path, $save_path);
			$res = array("file_name"=>$file_name, "full_path"=>$save_path.$file_name);
		}
		return $res;
	}
	
	public function resize($image_name, $width, $height = '', $folder_name, $thumb_folder) {

        $file_extension = $this->getFileExtension($image_name);
        switch ($file_extension) {
            case 'jpg':
            case 'jpeg':
                $image_src = imagecreatefromjpeg($folder_name . DS . $image_name);
                break;
            case 'png':
                $image_src = imagecreatefrompng($folder_name . DS . $image_name);
                break;
            case 'gif':
                $image_src = imagecreatefromgif($folder_name . DS . $image_name);
                break;
        }
        $true_width = imagesx($image_src);
        $true_height = imagesy($image_src);

        if ($true_width > $true_height) {
            $height = ($true_height * $width) / $true_width;
        } else {
            if ($height == '')
                $height = ($true_height * $width) / $true_width;

            $width = ($true_width * $height) / $true_height;
        }
        $image_des = imagecreatetruecolor($width, $height);

        if ($file_extension == 'png') {
            $nWidth = intval($true_width / 4);
            $nHeight = intval($true_height / 4);
            imagealphablending($image_des, false);
            imagesavealpha($image_des, true);
            $transparent = imagecolorallocatealpha($image_des, 255, 255, 255, 127);
            imagefilledrectangle($image_des, 0, 0, $nWidth, $nHeight, $transparent);
        }

        imagecopyresampled($image_des, $image_src, 0, 0, 0, 0, $width, $height, $true_width, $true_height);

        switch ($file_extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image_des, $thumb_folder . DS . $image_name, 100);
                break;
            case 'png':
                imagepng($image_des, $thumb_folder . DS . $image_name, 5);
                break;
            case 'gif':
                imagegif($image_des, $thumb_folder . DS . $image_name, 100);
                break;
        }
		
        return $image_des;
    }
	
	public function resize_from_url($image_url, $width, $height = '', $thumb_folder) {
		$image_src = ImageCreateFromString(file_get_contents($image_url));
		$image_name = basename($image_url);
		$file_extension = $this->getFileExtension($image_name);
		
		$true_width = imagesx($image_src);
		$true_height = imagesy($image_src);

		if ($true_width > $true_height) {
			$height = ($true_height * $width) / $true_width;
		} else {
			if ($height == '')
				$height = ($true_height * $width) / $true_width;

			$width = ($true_width * $height) / $true_height;
		}
		$image_des = imagecreatetruecolor($width, $height);

		if ($file_extension == 'png') {
			$nWidth = intval($true_width / 4);
			$nHeight = intval($true_height / 4);
			imagealphablending($image_des, false);
			imagesavealpha($image_des, true);
			$transparent = imagecolorallocatealpha($image_des, 255, 255, 255, 127);
			imagefilledrectangle($image_des, 0, 0, $nWidth, $nHeight, $transparent);
		}

		imagecopyresampled($image_des, $image_src, 0, 0, 0, 0, $width, $height, $true_width, $true_height);

		switch ($file_extension) {
			case 'jpg':
			case 'jpeg':
				imagejpeg($image_des, $thumb_folder . DS . $image_name, 100);
				break;
			case 'png':
				imagepng($image_des, $thumb_folder . DS . $image_name, 5);
				break;
			case 'gif':
				imagegif($image_des, $thumb_folder . DS . $image_name, 100);
				break;
		}
		return $image_des;
	}

    function getFileExtension($file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        return $extension;
    }

    public function send_notification($message, $registration_ids, $alert_message = APP_NAME.' notification center', $noti_type = 'test',$device_type="A") {

    	
        if (empty($registration_ids)) {
            return;
        }        
        $url = FIRE_BASE_URL;
        $API_KEY = FCM_KEY;
        
       
        
            $sound = 'default';
       
        $noti_title=APP_NAME;
        if($noti_type=="adminalert"){
			
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
					$fields['data']['image'] = $data['noti_large'];
					$fields['notification']['image'] = $data['noti_large'];
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
        /*echo "<pre>";
        print_r($result);
        die;*/
        return $result;
    }

     public function sendSMTPMail($subject, $message, $receiver_email, $receiver_name="", $sender_from_name, $sender_from_email, $filepath=array()) {
        if(!empty(SMTP_SERVER)){
                    $this->includeSMTPMailerLib();
                    $mail = new PHPMailer(true);                    // Passing `true` enables exceptions

					    try {
                                /* $mail->SMTPDebug = 0;                       // Enable verbose debug output
                                $mail->isSMTP();                            // Set mailer to use SMTP
                                $mail->Host = SMTP_SERVER;                  // Specify main and backup SMTP servers
                                $mail->SMTPAuth = true;                     // Enable SMTP authentication
                                $mail->Username = SMTP_USERNAME;            // SMTP username
                                $mail->Password = SMTP_PASSWORD;            // SMTP password
                                $mail->SMTPSecure = SMTP_SECURE;            // Enable TLS encryption, `ssl` also accepted
                                $mail->Port = SMTP_PORT;   
                               //$mail->SMTPDebug  = 1;              // TCP port to connect to
 */
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

                                $mail->send();
                        } catch (Exception $e) {
                             return false;
                        }

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


     public function sendTemplatesInMail($mailTitle, $toName, $toEmail,$data=array()){
        $select_mail_template_query = "SELECT subject, content FROM tbl_templates WHERE type='E' AND title='".$mailTitle."' AND status='A'";
        $query = $this->db->query($select_mail_template_query);
        
        if ($query->num_rows()>0) {
            $mailTemplate = $query->result_array();  
            $subject= (isset($data['subject']) && !empty($data['subject']))?$data['subject']:$mailTemplate[0]['subject']; 


            $message= str_replace("{CUSTOMER_NAME}", $toName, $mailTemplate[0]['content']);           
            $message= str_replace("{MESSAGE}",$data['message'],$message);            

            $this->sendSMTPMail($subject,$message,$toEmail,$toName,SMTP_FROM_NAME,SMTP_FROM_EMAIL);
        }
    }
    
    public function generateRandomString($length = 10) {
    	// $slug=$this->generateRandomString(12).$id."_";
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
    }
    
}