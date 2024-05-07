<?php
require_once('base.php');
class Notifications extends Base {

    private $limit = 10;
    private $table = 'tbl_notifications';
    private $tbl_customers = 'tbl_customers';
    private $image = '';
    private $prefixUrl = 'admin/notifications/';
    private $name = 'Notification'; // For singular
    private $names = 'Notifications'; //plural form 

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

// se t master template
        $this->template->set_master_template('admin');
        $this->load->library('breadcrumbs');

// load all models
        $this->load->model('main_model');
        $this->load->helper('text');

        if ($this->session->userdata('condit'))
            $this->session->unset_userdata('condit');

        $this->load->library('Jquery_pagination');
    }

// admin login check
    function loginCheck($str) {
        if (!$this->session->userdata('adminId')) {
            $this->session->set_userdata('returnURL', $str);
            redirect('admin');
        }
    }

    function checkUser() {
        return true;
    }

    function ajax_index($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_index";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();

        $order_by = array(
            'field' => 'id',
            'type' => 'desc',
        );
        if ($this->input->get('field')) {
            $order_by = array();
            $order_by_other = array(
                'field' => $this->input->get('field'),
                'type' => $this->input->get('sort'),
            );
        } else {
            $order_by_other = array();
            $order_by = array(
                'field' => 'id',
                'type' => 'desc',
            );
        }
        $table = $this->table;
        $condit = "{$this->table}.sender_type ='ADMIN' AND {$this->table}.is_deleted='N'";
        $select_fields = " ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $keyword =  $search;
               
                $str[] = "`title` LIKE '%" . $keyword . "%' OR `notification` LIKE '%" . $keyword . "%'";
                
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
				
				if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` >=". strtotime($search."00:00:00");
                }
				if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'showing' and $val['value']) {
                    $this->limit = $val['value'];
                    $config['per_page'] = $this->limit;
                    $limit = $this->limit;
                }
            }
        }
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, "", $order_by_other);
        $config['total_rows'] = $rows['rows']['total'];
        $records = $rows['list'];
        $data['total_rows'] = $rows['rows']['total'];
        $data["per_page"] = $this->limit;
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';
        $config['prev_link'] = '← Previous';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = ' Next → ';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';

        $data['records'] = $records;

        $config['per_page'] = $limit;
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_index";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type'] = $order;
        $data['field'] = $this->input->get('field');
        $data['prefixUrl'] = $this->prefixUrl;
        $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
		
        $this->load->view($this->prefixUrl.'ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("{$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index";
        $data['this_url'] = base_url() . $this->prefixUrl."index";
		$data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

    // add new Notifications
    public function add() {

        //$this->db->simple_query("SET GLOBAL group_concat_max_len = 555555555555555555555");
        $this->db->simple_query('SET SESSION group_concat_max_len=5555555555555555');

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->names}";
        $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
		$data['tbl_customers'] = $this->tbl_customers;
        $data['users_ids'] = ($this->input->post('users')!=null)?explode(",",$this->input->post('users')):array();           
        
         $this->form_validation->set_rules('users', 'Customers ', "required");
         $this->form_validation->set_rules('body', 'Body', "trim|required");
         $this->form_validation->set_rules('title', 'Title', "trim|required");
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
			$postedData = $this->input->post();

            $is_promotional = isset($postedData['is_promotional'])?'1':'0';

          	$users_ids = $postedData['users'];
			$insert_data = array(
				'users_id'=>$users_ids,
                'is_promotional'=>$is_promotional,
				'title'=>$postedData['title'],
				'notification'=>$postedData['body'],
				'created'=>time(),
				'sender_id'=>$this->session->userdata('adminId'),
				'sender_ip'=>$_SERVER['REMOTE_ADDR']
			);
		/*******Image Area ****************************/
		
			if(!empty($_FILES['image']['name'])){
				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(NOTIFICATION_IMAGE_LARGE_PATH)) {
					mkdir(NOTIFICATION_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(NOTIFICATION_IMAGE_THUMB_PATH)) {
					mkdir(NOTIFICATION_IMAGE_THUMB_PATH, 0777, true);
				 } 
				} 
				$config['upload_path']          = NOTIFICATION_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;

                /*************************************************************************/
                    $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                    $width      = $fileinfo[0];
                    $height     = $fileinfo[1];
                    $ratio      =$height/$width;
                    $ratio      =round($ratio,2);
                    
                   // Validate image file dimension
                   if($width > "800" || $height > "328"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }else if($ratio!="0.41" && $ratio!="0.42"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }
                /*************************************************************************/

				//changing file name for selected
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_context_cat.$ext";
				$file_upload = $this->upload_files($config, "image");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], NOTIFICATION_IMAGE_LARGE_PATH,NOTIFICATION_IMAGE_THUMB_PATH);
					$insert_data['image'] = $file_upload['data']['file_name'];
				}
				else{
					if(!empty($file_upload['data'])){
						$data['validation_errors'] = $file_upload['data'];
					}else{
						$data['validation_errors'] = "<p>There was an error while uploading image.</p>";
					}					
					$this->template->write_view('contents', $this->prefixUrl.'add', $data);
					$this->template->render();
					$imageError = 'Y';
				}
			}

		/***********************************/
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);

           /* $select_user_field = 'tbl_customer_logins.device_token,tbl_customer_logins.device_type';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field,'', "tbl_customer_logins.customer_id IN (".$users_ids.")", "", "", "", "");*/



            $select_user_field = 'GROUP_CONCAT(tbl_customer_logins.device_token) as android_tokens';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field,'', "tbl_customer_logins.customer_id IN (".$users_ids.") AND tbl_customer_logins.device_type='A' ", "", "", "", ""); 


            $select_user_field1 = 'GROUP_CONCAT(tbl_customer_logins.device_token) as ios_tokens';
            $user_records1 = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field1,'', "tbl_customer_logins.customer_id IN (".$users_ids.") AND tbl_customer_logins.device_type='I' ", "", "", "", ""); 


            foreach ($user_records as $dtt) 
                        {

                        $android_tokens= $dtt['android_tokens'];
                        $android_tokens_array=explode(',',$android_tokens);
                        $android_chunk_array=array_chunk($android_tokens_array,900);

                        if(!empty($android_chunk_array[0][0])){

                            foreach($android_chunk_array as $dt){



                            $user_tokens=$dt;                        
                            $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                            if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                                $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                                $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                            }                       


                       

                        $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'A');

                       


                            }


                        }

                            
                                

                            
                        }


                        foreach ($user_records1 as $dtt) 
                        {

                        $ios_tokens= $dtt['ios_tokens'];
                        $ios_tokens_array=explode(',',$ios_tokens);
                        $ios_chunk_array=array_chunk($ios_tokens_array,900);

                        if(!empty($ios_chunk_array[0][0])){

                            foreach($ios_chunk_array as $dt){



                            $user_tokens=$dt;                        
                            $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                            if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                                $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                                $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                            }                       


                       

                        $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'I');

                       


                            }


                        }

                            
                                

                            
                        }

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    // Resend  Notifications
    public function resend() {
        //$this->db->simple_query("SET GLOBAL group_concat_max_len = 555555555555555555555");
        $this->db->simple_query('SET SESSION group_concat_max_len=5555555555555555');
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'resend');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Resend {$this->names}", site_url('section'));

        $table = $this->table;
        $cond = "id ='" . $id . "'";
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        $redirect       = $this->prefixUrl.'index';
        $data['title']  = "Resend New {$this->names}";
        $data['table']  = $this->table;
        $data['name']   = $this->name;
        $data['names']  = $this->names;
        $data['tbl_customers']  = $this->tbl_customers;
        $data['user_detail']  = $user_detail;

        if($this->input->post('users')){
            $data['users_ids'] = ($this->input->post('users')!=null)?explode(",",$this->input->post('users')):array();
        }else{
            $data['users_ids']      = explode(",", $user_detail['users_id']);       
        }
       
        
         $this->form_validation->set_rules('users', 'Customers ', "required");
         $this->form_validation->set_rules('body', 'Body', "trim|required");
         $this->form_validation->set_rules('title', 'Title', "trim|required");
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
            $postedData = $this->input->post();//print_r($postedData);die;
            $users_ids = $postedData['users'];
			$is_promotional = isset($postedData['is_promotional'])?'1':'0';
            $insert_data = array(
                'users_id'=>$users_ids,
                'title'=>$postedData['title'],
                'notification'=>$postedData['body'],
                'is_promotional'=>$is_promotional,
                'created'=>time(),
                'sender_id'=>$this->session->userdata('adminId'),
                'sender_ip'=>$_SERVER['REMOTE_ADDR'],
                'image'=>$user_detail['image'],
            );

        /*******Image Area ****************************/
        
            if(!empty($_FILES['image']['name'])){
                //validating files first
                if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                 if (!is_dir(NOTIFICATION_IMAGE_LARGE_PATH)) {
                    mkdir(NOTIFICATION_IMAGE_LARGE_PATH, 0777, true);
                 }if (!is_dir(NOTIFICATION_IMAGE_THUMB_PATH)) {
                    mkdir(NOTIFICATION_IMAGE_THUMB_PATH, 0777, true);
                 } 
                } 
                $config['upload_path']          = NOTIFICATION_IMAGE_LARGE_PATH;
                $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                $config['max_size']             = ALLOWED_FILE_SIZE;

                /*************************************************************************/
                    $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                    $width      = $fileinfo[0];
                    $height     = $fileinfo[1];
                    $ratio      = $height/$width;
                    $ratio      = round($ratio,2);
                    
                   // Validate image file dimension
                   if($width > "800" || $height > "328"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }else if($ratio!="0.41" && $ratio!="0.42"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }
                /*************************************************************************/

                //changing file name for selected
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $config['file_name']  = rand()."_".time()."_context_cat.$ext";
                $file_upload = $this->upload_files($config, "image");
                

                if($file_upload['status']=="success"){
                    //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                    $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], NOTIFICATION_IMAGE_LARGE_PATH,NOTIFICATION_IMAGE_THUMB_PATH);
                    $insert_data['image'] = $file_upload['data']['file_name'];
                }
                else{
                    if(!empty($file_upload['data'])){
                        $data['validation_errors'] = $file_upload['data'];
                    }else{
                        $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                    }                   
                    $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                    $this->template->render();
                    $imageError = 'Y';
                }
            }

        /***********************************/
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);

           /* $select_user_field = 'tbl_customer_logins.device_token,tbl_customer_logins.device_type';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field,'', "tbl_customer_logins.customer_id IN (".$users_ids.")", "", "", "", "");*/



            $select_user_field = 'GROUP_CONCAT(tbl_customer_logins.device_token) as android_tokens';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field,'', "tbl_customer_logins.customer_id IN (".$users_ids.") AND tbl_customer_logins.device_type='A' ", "", "", "", ""); 


            $select_user_field1 = 'GROUP_CONCAT(tbl_customer_logins.device_token) as ios_tokens';
            $user_records1 = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field1,'', "tbl_customer_logins.customer_id IN (".$users_ids.") AND tbl_customer_logins.device_type='I' ", "", "", "", ""); 


            foreach ($user_records as $dtt) 
                {

                    $android_tokens= $dtt['android_tokens'];
                    $android_tokens_array=explode(',',$android_tokens);
                    $android_chunk_array=array_chunk($android_tokens_array,900);

                        if(!empty($android_chunk_array[0][0])){

                            foreach($android_chunk_array as $dt){



                            $user_tokens=$dt;                        
                            $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                            if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                                $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                                $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                            }                       


                       

                        $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'A');

                            }
                        }                                                    
                    }


                        foreach ($user_records1 as $dtt) 
                        {

                            $ios_tokens= $dtt['ios_tokens'];
                            $ios_tokens_array=explode(',',$ios_tokens);
                            $ios_chunk_array=array_chunk($ios_tokens_array,900);

                            if(!empty($ios_chunk_array[0][0])){
                                foreach($ios_chunk_array as $dt){
                                $user_tokens=$dt;                        
                                $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                                if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                                    $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                                    $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                                }                       

                                $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'I');

                                }
                            }  
                        }


            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

	public function view_users(){
		$postedData = $this->input->post();
		if(!empty($postedData)){
			$rec_id 	= $postedData['id'];
			$notification_records = $this->main_model->cruid_select($this->table, 'users_id','', "{$this->table}.id = '$rec_id'", "", "", "", "");
			$users_ids = $notification_records['users_id'];
			$select_user_field = "{$this->tbl_customers}.firstname,{$this->tbl_customers}.lastname";
			$user_records = $this->main_model->cruid_select_array_limit($this->tbl_customers, $select_user_field,'', "{$this->tbl_customers}.id IN (".$users_ids.")", "", "", "", "");
			$str = '<div class="col-sm-12">';
			if(!empty($user_records)){
					foreach($user_records as $ur){
						$str .= ucfirst($ur['firstname']).' '.ucfirst($ur['lastname']).', ';
					}
				echo trim($str,', ')."</div>";
			}else{
				echo 'No rider found';
			}			
		}else{
			echo 'No rider found';
		}
		
	}


    // Send all new Notifications
    public function send_all()
    {
        $this->db->simple_query('SET SESSION group_concat_max_len=5555555555555555');

        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "1800");

        $this->loginCheck($this->prefixUrl.'send_all');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->names}";
        $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['tbl_customers'] = $this->tbl_customers;
        //$data['users_ids'] = ($this->input->post('users')!=null)?explode(",",$this->input->post('users')):array();           
        
        //$this->form_validation->set_rules('users', 'Customers ', "required");
        $this->form_validation->set_rules('body', 'Body', "trim|required");
        $this->form_validation->set_rules('title', 'Title', "trim|required");
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'send_all', $data);
            $this->template->render();
        }
        else
        {
            $postedData = $this->input->post(); //print_r($postedData);die;
            $is_promotional = isset($postedData['is_promotional'])?'1':'0';
            $users_ids = 0;
            $insert_data = array(
                'users_id'=>$users_ids,
                'is_promotional'=>$is_promotional,
                'title'=>$postedData['title'],
                'notification'=>$postedData['body'],
                'created'=>time(),
                'sender_id'=>$this->session->userdata('adminId'),
                'sender_ip'=>$_SERVER['REMOTE_ADDR']
            );

            /*******Image Area ****************************/
            if(!empty($_FILES['image']['name'])){
                //validating files first
                if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                    if (!is_dir(NOTIFICATION_IMAGE_LARGE_PATH)) {
                        mkdir(NOTIFICATION_IMAGE_LARGE_PATH, 0777, true);
                    }if (!is_dir(NOTIFICATION_IMAGE_THUMB_PATH)) {
                        mkdir(NOTIFICATION_IMAGE_THUMB_PATH, 0777, true);
                    } 
                } 
                $config['upload_path']          = NOTIFICATION_IMAGE_LARGE_PATH;
                $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                $config['max_size']             = ALLOWED_FILE_SIZE;
                /*************************************************************************/
                    $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                    $width      = $fileinfo[0];
                    $height     = $fileinfo[1];
                    $ratio      =$height/$width;
                    $ratio      =round($ratio,2);
                    
                   // Validate image file dimension
                   if($width > "800" || $height > "328"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }else if($ratio!="0.41" && $ratio!="0.42"){
                        $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        return;
                   }
                /*************************************************************************/

                //changing file name for selected
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $config['file_name']  = rand()."_".time()."_context_cat.$ext";
                $file_upload = $this->upload_files($config, "image");
                

                if($file_upload['status']=="success"){
                    //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                    $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], NOTIFICATION_IMAGE_LARGE_PATH,NOTIFICATION_IMAGE_THUMB_PATH);
                    $insert_data['image'] = $file_upload['data']['file_name'];
                }
                else{
                    if(!empty($file_upload['data'])){
                        $data['validation_errors'] = $file_upload['data'];
                    }else{
                        $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                    }                   
                    $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                    $this->template->render();
                    $imageError = 'Y';
                }
            }

            $table = $this->table;
            
            $select_user_field = 'GROUP_CONCAT(tbl_customer_logins.device_token) as android_tokens, GROUP_CONCAT(DISTINCT tbl_customer_logins.customer_id) as android_customer_ids';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field,'', "tbl_customer_logins.device_type ='A' ", "", "", "", ""); 

            $select_user_field1 = 'GROUP_CONCAT(tbl_customer_logins.device_token) as ios_tokens, GROUP_CONCAT(DISTINCT tbl_customer_logins.customer_id) as ios_customer_ids';
            $user_records1 = $this->main_model->cruid_select_array_limit('tbl_customer_logins', $select_user_field1,'', "tbl_customer_logins.device_type ='I' ", "", "", "", ""); 

            $android_customer_ids=[];
            $ios_customer_ids    =[];

            foreach ($user_records as $dtt) 
            {

                $android_tokens= $dtt['android_tokens'];
                $android_customer_ids = explode(',',$dtt['android_customer_ids']);

                $android_tokens_array   =   explode(',',$android_tokens);
                $android_chunk_array    =   array_chunk($android_tokens_array,900);

                if(!empty($android_chunk_array[0][0])){

                    foreach($android_chunk_array as $dt){
                        $user_tokens=$dt;                        
                        $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                        if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                            $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                            $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                        }                       
                        $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'A');
                    }
                }
            }

            foreach ($user_records1 as $dtt) 
            {
                $ios_tokens       = $dtt['ios_tokens'];
                $ios_customer_ids = explode(',',$dtt['ios_customer_ids']);

                $ios_tokens_array=explode(',',$ios_tokens);
                $ios_chunk_array=array_chunk($ios_tokens_array,900);

                if(!empty($ios_chunk_array[0][0])){
                    foreach($ios_chunk_array as $dt){
                        $user_tokens=$dt;                        
                        $notiData =array('title'=> $postedData['title'], 'message'=>  $postedData['body'], 'noti_type'=>'adminalert');

                        if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                            $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                            $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                        }
                        $this->send_notification($notiData, $user_tokens,$postedData['body'], $notiData['noti_type'] ,'I');
                    }
                }  
            }

            $customer_idsAry = array_merge($android_customer_ids,$ios_customer_ids);
            $customer_ids = array_filter($customer_idsAry);
            $users_ids    = implode(",", $customer_ids);

            $insert_data['users_id'] =  $users_ids;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }



    // actions fro Referral
    public function action() {
        $this->checkUser();
        $this->session->unset_userdata('client_id');
        $action = $this->input->post('action');
        $current_url = $this->input->post('current_url');
        if ($action) {
            if ($action == 'Activate') {
                //$this->activateall($current_url);
            } else if ($action == 'Deactivate') {
               // $this->deactivateall($current_url);
            } else if ($action == 'Delete') {
                $this->deleteall($current_url);
            }
        } else {
            $this->session->set_userdata('message', 'Please select atleast one action');
        }
    }

    // delete user detail
    public function delete() {
        $id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'delete/' . $id);
        $this->checkUser();

        // get photo details
        $table = $this->table;
        $select_fields = "$table.id";
        $cond = array(
            'id' => $id,
        );
        $data = array(
            'is_deleted' => 'Y',
        );
        // delete from db
        $this->main_model->cruid_update($table, $data,$cond);
        $this->session->set_userdata('smessage', 'Successfully deleted');
    }

    // delete all Referral
    public function deleteall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one User');
        } else {
            for ($i = 0; $i < count($checked); $i++) {

                // get photo details
                $id = $checked[$i];
                $table = $this->table;
                $select_fields = "$table.id";
                $cond = array(
                    'id' => $id,
                );
                $data = array(
                    'is_deleted' => 'Y',
                );
                $details = $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', 'Selected successfully deleted');
    }


//End of Countries class
}