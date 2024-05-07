<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

   class Email_verification extends CI_Controller {
  
      public function index($token) {
        $data = array();

      	 $query = $this->db->query("SELECT id,firstname,lastname FROM tbl_customers WHERE email_token='$token'");
			
			if($query->num_rows()>0){

				$results = $query->row(); 
				$id = $results->id;
				$data['name'] = ucfirst($results->firstname) . $results->lastname;
				$data['message'] = "Your Email Succusessfully Verified.";
				$this->db->query("UPDATE tbl_customers SET is_email_verified='Y',email_token=null,email_token_at=0  WHERE id=$id" );

			
			}else{
				$data['message'] = "Invalid Token.";
			}

			$this->load->view('email_verification/verification',$data);
			

	
      } 
   } 
?>