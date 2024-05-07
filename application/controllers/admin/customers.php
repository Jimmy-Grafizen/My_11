<?php
require_once('base.php');
class Customers extends Base {
	private $limit = 20;
    private $table = 'tbl_customers';
	private $country =  'tbl_countries';
	private $state =  'tbl_states';
	private $tbl_withdraw_requests =  'tbl_withdraw_requests';
    private $image = '';
    private $prefixUrl = 'admin/customers/';
    private $name = 'Customers'; // For singular
    private $names = 'Customers'; //plural form 

    /**
     * Constructor
     */
    private $country_id = null;
    function __construct() {
        parent::__construct();

        // se t master template
        $this->template->set_master_template('admin');
        $this->load->library('breadcrumbs');

        // load all models
        $this->load->model('main_model');
        $this->load->model('customer_model');
        $this->load->helper('text');

        if ($this->session->userdata('condit'))
            $this->session->unset_userdata('condit');

        $this->load->library('Jquery_pagination');
        $commonInsert= array(
                'status'     => 'A',
                'created_by' => $this->session->userdata('adminId'),
                'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
        $country_id = $this->main_model->insert_or_id_get('tbl_countries',['name'=>"India"], array_merge(['name'=>"India"],$commonInsert));
        $this->country_id = $country_id;
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
 
	public function createSlug($string) {
        $old_pattern = array("/[^a-zA-Z0-9]/", "/_+/", "/_$/");
        $new_pattern = array("-", "-", "");
        return strtolower(preg_replace($old_pattern, $new_pattern, $string)) . rand(9999, 9999999) . time();
    }
	
    function ajax_index($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_index";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
		$joins[1] =	['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
		$joins[2] =	['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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
		
        $condit = "{$this->table}.is_deleted = 'N'";
        
        $select_fields = ",country.name as countryName, state.name as stateName ";
		
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
					if ($val['name'] == 'search' and $val['value']) {
						$str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
					}
               // }


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
				if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` >=". strtotime($search."00:00:00");
                }
				if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'user_type' and $val['value']) {
                    if( $search == 'is_fake' ){
                        $condit .= "  AND `$search` ='1' AND `is_admin` ='0'";
                    }else{
                        $condit .= "  AND `$search` ='1'";
                    }
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `created` >=". $this->session->userdata('from_date');
                $condit .= "  AND `created` <=". $this->session->userdata('to_date');
               
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
        
        $data['export'] = "no";
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'export_customers', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_index', $data);
        }
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
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

    // add new user
    public function add() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect           = $this->prefixUrl.'index';
        $data['title']      = "Add New {$this->names}";
        $data['country_id'] = $this->country_id;
        $data['profile_pictures'] = $this->get_profile_pictures();

         $this->form_validation->set_rules('email', 'Email', "trim|required|is_unique[{$this->table}.email]");
            $this->form_validation->set_message('is_unique', 'This Email is already taken in ' . SITE_TITLE . ". Please try different");
			$this->form_validation->set_rules('phone', 'Phone', "trim|required|is_unique[{$this->table}.phone]");
            $this->form_validation->set_message('is_unique', 'This Phone is already taken in ' . SITE_TITLE . ". Please try different");

         if($this->input->post('is_admin') ){
            //$this->form_validation->set_rules('is_admin', 'Admin', "trim|callback_is_unique_admin[{$this->table}.id.$id]");
            //$this->form_validation->set_message('is_unique_admin', 'This Admin is already taken in ' . SITE_TITLE . ". Please try different");
        }
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
			$name = $this->input->post('firstname');
			$firstname = $name;
			$password = $this->input->post('password');
            $email = $this->input->post('email');
            $insert_data = array(
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
                'image' => $this->input->post('image_radio'),
                'email' => trim($email),
                'country_mobile_code' => $this->input->post('country_mobile_code'),
                'phone' => $this->input->post('phone'),
                'pincode' => $this->input->post('pincode'),
                'dob' => strtotime($this->input->post('dob')),
                'addressline1' => $this->input->post('addressline1'),
                'addressline2' => $this->input->post('addressline2'),
                'country' => $this->input->post('country'),
                'state' => $this->input->post('state'),
                'city' => $this->input->post('city'),
                'password' => md5($password),
				'slug' => $this->createSlug($this->input->post('firstname')),
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'modified_by' => $this->session->userdata('adminId'),
                'created' => time(),               
                'modified' => time(),               
                'is_admin' => $this->input->post('is_admin')?'1':'0',   
                'is_fake'  => ( $this->input->post('is_admin') || $this->input->post('is_fake'))?'1':'0',   
            );
			
			if(!empty($_FILES['image']['name'])){
				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(CUSTOMER_IMAGE_LARGE_PATH)) {
					mkdir(CUSTOMER_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(CUSTOMER_IMAGE_THUMB_PATH)) {
					mkdir(CUSTOMER_IMAGE_THUMB_PATH, 0777, true);
				 } 
				} 
				$config['upload_path']          = CUSTOMER_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;

				//changing file name for selected
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_context_cat.$ext";
				$file_upload = $this->upload_files($config, "image");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], CUSTOMER_IMAGE_LARGE_PATH,CUSTOMER_IMAGE_THUMB_PATH);
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
            $business_id = $this->main_model->cruid_insert($table, $insert_data);
			/*********/
			if($business_id){
				$my_team_name = (strlen($firstname)>5)?strtoupper(substr($firstname,0,5)):strtoupper($firstname);
                $my_referral_code   = strtoupper( $this->generateRandomString(8) ).$business_id;
                $Update_data        = array('team_name' => $my_team_name.$business_id,"referral_code"=>$my_referral_code);
				$Update_data = array('team_name' => $my_team_name.$business_id);
				$cond = "id =".$business_id;
				$this->main_model->cruid_update($table, $Update_data, $cond);

			}
			/*********/
            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    // edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();
		$data['profile_pictures'] = $this->get_profile_pictures();
        $data['country_id'] = $this->country_id;
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "id =".$user_name;
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Customer Details";

			$this->form_validation->set_rules('email', 'Email', "trim|required|is_unique_again[{$this->table}.email.$id]");
            $this->form_validation->set_message('is_unique_again', 'This Email is already taken in ' . SITE_TITLE . ". Please try different");
			$this->form_validation->set_rules('phone', 'Phone', "trim|required|is_unique_again[{$this->table}.phone.$id]");
            $this->form_validation->set_message('is_unique_again', 'This Phone is already taken in ' . SITE_TITLE . ". Please try different");

            if($this->input->post('is_admin') ){
                //$this->form_validation->set_rules('is_admin', 'Admin', "trim|callback_is_unique_admin_again[{$this->table}.id.$id]");
                //$this->form_validation->set_message('is_unique_admin_again', 'This Admin is already taken in ' . SITE_TITLE . ". Please try different");
            }

            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
						
			$name = $this->input->post('firstname');
            $email = $this->input->post('email');
			$Update_data = array(
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
                //'image' => $this->input->post('image_radio'),
                 'dob' => strtotime($this->input->post('dob')),
                'addressline1' => $this->input->post('addressline1'),
                'addressline2' => $this->input->post('addressline2'),
                'country' => $this->input->post('country'),
                'state' => $this->input->post('state'),
                'city' => $this->input->post('city'),
                'pincode' => $this->input->post('pincode'),
                'email' => trim($email),
                'country_mobile_code' => $this->input->post('country_mobile_code'),
                'phone' => $this->input->post('phone'),
                'status' => 'A',
				'modified_by' => $this->session->userdata('adminId'),
                'modified' => time(), 
                'is_admin' => $this->input->post('is_admin')?'1':'0',               
                'is_fake' => ($this->input->post('is_admin') || $this->input->post('is_fake'))?'1':'0',
            );

		if(!empty($_FILES['image']['name'])){
				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(CUSTOMER_IMAGE_LARGE_PATH)) {
					mkdir(CUSTOMER_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(CUSTOMER_IMAGE_THUMB_PATH)) {
					mkdir(CUSTOMER_IMAGE_THUMB_PATH, 0777, true);
				 } 
				}
				$config['upload_path']          = CUSTOMER_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;

				//changing file name for selected
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_customer.$ext";
				$file_upload = $this->upload_files($config, "image");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], CUSTOMER_IMAGE_LARGE_PATH, CUSTOMER_IMAGE_THUMB_PATH);
					$Update_data['image'] = $file_upload['data']['file_name'];
					
					$old_selected = $selected_image_old;								
					$old_files_to_remove[] = CUSTOMER_IMAGE_LARGE_PATH.$old_selected;
					$old_files_to_remove[] = CUSTOMER_IMAGE_THUMB_PATH.$old_selected;
				}
				else{
					if(!empty($file_upload['data'])){
						$data['validation_errors'] = $file_upload['data'];
					}else{
						$data['validation_errors'] = "<p>There was an error while uploading image.</p>";
					}					
					$this->template->write_view('contents', $this->prefixUrl.'edit', $data);
					$this->template->render();
					$imageError = 'Y';
				}
			}
                $this->main_model->cruid_update($table, $Update_data, $cond);
                $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }
    // password user detail
    public function password() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'password/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Password {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "id =".$user_name;
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Customer Details";

			$this->form_validation->set_rules('password', 'Password', "trim|required");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'password', $data);
                $this->template->render();
            } else {
				
			$name = $this->input->post('password');
            $email = $this->input->post('email');
			$Update_data = array(
                'password' => md5($this->input->post('password')),                
				'modified_by' => $this->session->userdata('adminId'),
                'modified' => time(),               
            );		
                $this->main_model->cruid_update($table, $Update_data, $cond);
                $this->session->set_userdata('smessage', $this->name.' Password Successfully updated');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }

    // actions fro Admins
    public function action() {
        $this->checkUser();
        $this->session->unset_userdata('client_id');
        $action = $this->input->post('action');
        $current_url = $this->input->post('current_url');
        if ($action) {
            if ($action == 'Activate') {
                $this->activateall($current_url);
            } else if ($action == 'Deactivate') {
                $this->deactivateall($current_url);
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
		$data = array(
			'is_deleted' => "Y",
		);
		$cond = "id ='" . $id . "'";
		$this->main_model->cruid_update($this->table, $data, $cond);
        //$this->main_model->cruid_delete($this->table, array('id' => $id));
        $this->session->set_userdata('smessage', "{$this->name} Successfully deleted");
    }

    // delete all Admins
    public function deleteall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
			$data = array(
				'is_deleted' => "Y",
			);
            for ($i = 0; $i < count($checked); $i++) {
				$cond = "id ='" . $checked[$i] . "'";
				$this->main_model->cruid_update($this->table, $data, $cond);
                // $this->main_model->cruid_delete($this->table, array('id' => $checked[$i]));
            }
        }
        $this->session->set_userdata('smessage', "Selected {$this->names} successfully deleted");
    }

	// activate user profile
    function activate($slug) {
        $url = $this->input->get("return");
        $table = $this->table;
        $cond = array(
            'id' => $slug,
        );
        $data = array(
            'status' => 'A',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', "Selected {$this->name} successfully activated");
    }

    // deactivate user profile
    function deactivate($slug) {
        $url = $this->input->get("return");
        $table = $this->table;
        $cond = array(
            'id' => $slug,
        );
        $data = array(
            'status' => 'D',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', "Selected {$this->name} successfully deactivated");
    }
    // activate all Admins
    public function activateall($current_url) {
		//echo "dfsadf";die;
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
            for ($i = 0; $i < count($checked); $i++) {

                $table = $this->table;
                $cond = "id ='" . $checked[$i] . "'";
                $select_fields = $this->table.".*";
                $joins = array();
                $uset_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
                $table = $this->table;
                $cond = array(
                    'id' => $checked[$i],
                );
                $data = array(
                    'status' => 'A',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', "Selected {$this->name} successfully activated");
    }

    //  deactivate all Admins
    public function deactivateall($current_url) {
		 $this->checkUser();
        $checked = $this->input->post('check');
		
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one User');
        } else {
            for ($i = 0; $i < count($checked); $i++) {
                $table = $this->table;
                $cond = array(
                    'id' => $checked[$i],
                );
                $data = array(
                    'status' => 'D',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', "Selected {$this->name} successfully deactivated");
    }
	
	protected function get_profile_pictures() {
		//$profile_pictures=unserialize(PROFILE_PICTURES);    
        $profile_pictures = $this->main_model->cruid_select_array("tbl_customer_avatars", "*", array(), "is_deleted ='N'",NULL,array('field'=>'id','type'=>"DESC"));
		return $profile_pictures;
    }
	
	
	public function edit_pan_bank($ids =null) {
            
        $id = $ids; 
        $get_customer = $this->customer_model->getBasicCustomerInfo($id);
        
        $paincard_id =   $get_customer['paincard_id'];
        $bankdetailId =  $get_customer['bankdetail_id'];

        if(!empty($paincard_id) || !empty($paincard_id)){
            $customerId_docs = $this->customer_model->get_customer_paincard($paincard_id);
            $data['detail']['pan'] = ($customerId_docs)?(array)$customerId_docs:null;
            $data['paincard_id'] = $paincard_id;
            $bankdetails = $this->customer_model->get_bankdetail($bankdetailId);
            $data['detail']['bank'] = ($bankdetails)?(array)$bankdetails:null;
            $data['bankdetailId']  = $bankdetailId;
            $data['customer_id']  = (isset($data['detail']['bank']['customer_id'] ) )?$data['detail']['bank']['customer_id']:$data['detail']['pan']['customer_id'];

            $this->form_validation->set_rules('pan[pain_number]', 'PAN card Number', "trim|required");
            $this->form_validation->set_rules('pan[name]', 'Name', "trim|required");
            $this->form_validation->set_rules('pan[id]', 'id', "trim|required");
            $this->form_validation->set_rules('bank[account_number]', 'Account Number', "trim|required");
            $this->form_validation->set_rules('bank[ifsc]', 'IFSC', "trim|required");
            $this->form_validation->set_rules('bank[name]', 'Name', "trim|required");
            $this->form_validation->set_rules('bank[id]', 'ID', "trim|required");
           
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit_pan_bank', $data);
                $this->template->render();
            }else{
         
            $panDetail = $this->input->post('pan');
            $bankDetail = $this->input->post('bank');

                if( !empty($panDetail) ){
                        $painUpdate_data = array(
                            'pain_number'   => $panDetail['pain_number'],
                            'name'          => $panDetail['name'],
                            'dob'           => date('Y-m-d',strtotime(str_replace('/','-',$panDetail['dob']))),
                            'updated_by'    => $this->session->userdata('adminId'),
                            'updatedat'     => time(), 
                        );

                    if(!empty($_FILES['pan_image']['name'])){
                        //validating files first
                        if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                         if (!is_dir(PANCARD_IMAGE_LARGE_PATH)) {
                            mkdir(PANCARD_IMAGE_LARGE_PATH, 0777, true);
                         }if (!is_dir(PANCARD_IMAGE_THUMB_PATH)) {
                            mkdir(PANCARD_IMAGE_THUMB_PATH, 0777, true);
                         } 
                        }
                        $config['upload_path']          = PANCARD_IMAGE_LARGE_PATH;
                        $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                        $config['max_size']             = ALLOWED_FILE_SIZE;

                        //changing file name for selected
                        $ext = pathinfo($_FILES['pan_image']['name'], PATHINFO_EXTENSION);
                        $config['file_name']  = rand()."_".time()."pan_image.$ext";
                        $file_upload = $this->upload_files($config, "pan_image");
                        

                        if($file_upload['status']=="success"){
                            //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                            $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], PANCARD_IMAGE_LARGE_PATH, PANCARD_IMAGE_THUMB_PATH);
                            $painUpdate_data['image'] = $file_upload['data']['file_name'];
                            
                            $old_selected = $selected_image_old;                                
                            $old_files_to_remove[] = PANCARD_IMAGE_LARGE_PATH.$old_selected;
                            $old_files_to_remove[] = PANCARD_IMAGE_THUMB_PATH.$old_selected;
                        }
                        else{
                            if(!empty($file_upload['data'])){
                                $data['validation_errors'] = $file_upload['data'];
                            }else{
                                $data['validation_errors'] = "<p>There was an error while uploading pan image.</p>";
                            }                   
                            $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                            $this->template->render();
                            $imageError = 'Y';
                        }
                    }
                }

                $pan_cond = "id =".$panDetail['id'];
                $this->main_model->cruid_update("tbl_customer_paincard", $painUpdate_data, $pan_cond);

                if( !empty($bankDetail) ){
                        $bankUpdate_data = array(
                            'account_number' => $bankDetail['account_number'],
                            'name' =>        $bankDetail['name'],
                            'ifsc' =>        $bankDetail['ifsc'],
                            'updated_by' => $this->session->userdata('adminId'),
                            'updatedat' => time(), 
                        );

                    if(!empty($_FILES['bank_image']['name'])){
                        //validating files first
                        if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                         if (!is_dir(BANK_IMAGE_LARGE_PATH)) {
                            mkdir(BANK_IMAGE_LARGE_PATH, 0777, true);
                         }if (!is_dir(BANK_IMAGE_THUMB_PATH)) {
                            mkdir(BANK_IMAGE_THUMB_PATH, 0777, true);
                         } 
                        }
                        $config['upload_path']          = BANK_IMAGE_LARGE_PATH;
                        $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                        $config['max_size']             = ALLOWED_FILE_SIZE;

                        //changing file name for selected
                        $ext = pathinfo($_FILES['bank_image']['name'], PATHINFO_EXTENSION);
                        $config['file_name']  = rand()."_".time()."bank_image.$ext";
                        $file_upload = $this->upload_files($config, "bank_image");
                        

                        if($file_upload['status']=="success"){
                            //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                            $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], BANK_IMAGE_LARGE_PATH, BANK_IMAGE_THUMB_PATH);
                            $bankUpdate_data['image'] = $file_upload['data']['file_name'];
                            
                            $old_selected = $selected_image_old;                                
                            $old_files_to_remove[] = BANK_IMAGE_LARGE_PATH.$old_selected;
                            $old_files_to_remove[] = BANK_IMAGE_THUMB_PATH.$old_selected;
                        }
                        else{
                            if(!empty($file_upload['data'])){
                                $data['validation_errors'] = $file_upload['data'];
                            }else{
                                $data['validation_errors'] = "<p>There was an error while uploading pan image.</p>";
                            }                   
                            $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                            $this->template->render();
                            $imageError = 'Y';
                        }
                    }
                }
                $pan_cond = "id =".$bankDetail['id'];
                $this->main_model->cruid_update("tbl_customer_bankdetail", $bankUpdate_data, $pan_cond);

               $this->session->set_userdata('smessage', 'Successfully updated');

                $url = $this->input->get("return");
                redirect($url);
            }
            //$this->load->view($this->prefixUrl.'edit_pan_bank', $data); 
        }
        else{
            echo "<div class='col-sm-12'><p style='color:#FF0000'>Not found!</p></div>";
        }
    }

    public function view_documet($ids =null) {
        
		if($ids){
			$ids = explode(",",$ids);
			$paincard_id = current($ids);
			$bankdetailId = end($ids);
		}else{
			// $id = explode(",",$this->input->post('id'));
			$id = $this->input->post('id');
			$get_customer = $this->customer_model->getBasicCustomerInfo($id);
			
			$paincard_id =   $get_customer['paincard_id'];
			$bankdetailId =  $get_customer['bankdetail_id'];
		}
		if(!empty($paincard_id) || !empty($paincard_id)){
            $customerId_docs = $this->customer_model->get_customer_paincard($paincard_id);
			$data['pain_card'] = $customerId_docs;
			$data['paincard_id'] = $paincard_id;
			$bankdetails = $this->customer_model->get_bankdetail($bankdetailId);
			$data['bankdetails'] = $bankdetails;
			$data['bankdetailId'] = $bankdetailId;
            $data['customer_id'] = ($bankdetails)?$bankdetails->customer_id:$customerId_docs->customer_id;
            
            $data['return'] = HTTP_PATH.'admin/customers/index';

            if ($this->agent->is_referral()){
                $data['return'] = $this->agent->referrer();
            }
			$this->load->view($this->prefixUrl.'ajax_document', $data);	
		}
		else{
			echo "<div class='col-sm-12'><p style='color:#FF0000'>Not found!</p></div>";
		}
    }
    
    
    public function ajax_document_verify() {
		$res = array();
		$action 	    = $this->input->post("action");
		$document_type 	= $this->input->post("document_type");
		$statas 		= $this->input->post("statas");
		$reason 	    = $this->input->post("reason");
		
		$ids = explode(",",$this->input->post('document_id'));
    	$paincard_id = current($ids);
    	$bankdetailId = end($ids);
    	
			if($document_type=="pain_card" && $paincard_id>0){
				$table = "tbl_customer_paincard";
				$Update_data = array('status' =>$statas ,'reason' =>$reason );
				$cond = "id =".$paincard_id;
				$this->main_model->cruid_update($table, $Update_data, $cond);

                /********************/
                $customerId_docs = $this->customer_model->get_customer_paincard($paincard_id);

                if($action =="Approve"){
                    $alert_message  = "Congratulations! Your PAN card has been Approved";  
                    $noti_type      = "pan_card_approved";  
                }else{
                    $alert_message  = $reason; 
                    $noti_type      = "pan_card_rejected";   
                }
             
                $customer_id    = $customerId_docs->customer_id;
                $postData       = array("noti_type"=>$noti_type,"alert_message"=>$alert_message,"customer_id"=>$customer_id,"dbsave"=>true);

                $this->RegardingDocumentsSendNotifs($postData);
                /********************/

			}elseif($document_type=="bankdetails" && $bankdetailId>0){
			    $table = "tbl_customer_bankdetail";
				$Update_data = array('status' =>$statas ,'reason' =>$reason );
				$cond = "id =".$bankdetailId;
				$this->main_model->cruid_update($table, $Update_data, $cond);

                /********************/
                $bankdetails = $this->customer_model->get_bankdetail($bankdetailId);
                if($action =="Approve"){
                    $alert_message  = "Congratulations! Your bank details has been Approved";  
                    $noti_type      = "bank_details_approved";  
                }else{
                    $alert_message  = $reason; 
                    $noti_type      = "bank_details_rejected";   
                }
             
                $customer_id    = $bankdetails->customer_id;
                $postData       = array("noti_type"=>$noti_type,"alert_message"=>$alert_message,"customer_id"=>$customer_id,"dbsave"=>true);

                $this->RegardingDocumentsSendNotifs($postData);
                /********************/

			}

			if($paincard_id>0 ||$paincard_id>0){			
				$this->view_documet($this->input->post('document_id'));
            }else{
				$res = array("status"=>"success", "message"=>$action." Somthing wrong try!");	
				echo json_encode($res);	
			}
	}
    //  Pending Approvel Documents customer documents 

    function ajax_document_approval_pending($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_approval_pending";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/document_approval_pending/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
		$joins[1] =	['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
		$joins[2] =	['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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
	    $condit = "{$this->table}.is_deleted = 'N' ";
        $tccreated =null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
		
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));

                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
					if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
					}
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $from_date = strtotime($search."00:00:00");
                    $tccreated    .= "  AND `createdat` >=". $from_date;
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $to_date = strtotime($search."23:59:59");
                    $tccreated    .= "  AND `createdat` <=". $to_date;    
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
               
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
		    if($tccreated){
                $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id ) WHERE ( tcb.status='P') AND (tc.bankdetail_id>0) $tccreated GROUP BY tcb.customer_id ORDER BY tcb.id DESC";
                $condit .= " AND {$this->table}.id IN ($wherin) ";
            }else{
                $wherin = 'SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id ) LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id ) WHERE ( tcb.status!="A" OR tcp.status!="A") AND (tc.bankdetail_id>0 OR tc.paincard_id) GROUP BY tcp.customer_id ORDER BY tcp.id DESC';
                 $condit .= " AND {$this->table}.id IN ($wherin) ";
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

        $data['current_url'] = base_url() . $this->prefixUrl."document_approval_pending/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_approval_pending";

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
		
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function document_approval_pending($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_approval_pending($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_approval_pending";
        $data['this_url'] = base_url() . $this->prefixUrl."document_approval_pending";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    // Approvel pending customer documents 

    function ajax_pending_withdrawals($offset = 0) {
		$twr = $this->tbl_withdraw_requests;
        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_pending_withdrawals";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/pending_withdrawals/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
		$joins[1] =	['table'=>"{$this->table}", 'condition'=>"{$twr}.customer_id = {$this->table}.id",'jointype'=>'left'];
		$joins[2] =	['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
		$joins[3] =	['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
        $joins[4] = ['table'=>"tbl_customer_bankdetail tcbd", 'condition'=>"tcbd.id = {$this->table}.bankdetail_id AND `tcbd`.customer_id ={$this->table}.`id`",'jointype'=>'left'];
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
		$condit = "{$twr}.status = 'P' ";
        
        $select_fields = " ,tcbd.account_number,tcbd.name,tcbd.ifsc,firstname,lastname,email,phone,country_mobile_code,country.name as countryName, state.name as stateName ";
		
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
					if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
					}
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
				if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$twr}`.`created_at` >=". strtotime($search."00:00:00");
                }
				if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$twr}`.`created_at` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `{$twr}`.`created_at` >=". $this->session->userdata('from_date');
                $condit .= "  AND `{$twr}`.`created_at` <=". $this->session->userdata('to_date');
               
            }
		
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $twr, $select_fields, $condit, "", $order_by_other);
	
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

        $data['current_url'] = base_url() . $this->prefixUrl."pending_withdrawals/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_pending_withdrawals";

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
        
        if ($this->input->post('action') =='export' && $data['records']) {
            $var = $this->load->view($this->prefixUrl.'ajax_index_download', $data,true);
            $file = date("M_d_Y") . "_pending_withdrawals.xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            if ($this->input->post('action') =='export' ){
                echo base_url() . $this->prefixUrl."pending_withdrawals";
             }else{
                $this->load->view($this->prefixUrl.'ajax_pending_withdrawals', $data);
            }
           
        }
    }

    public function pending_withdrawals($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Pending Withdrawals {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_pending_withdrawals($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_pending_withdrawals";
        $data['this_url'] = base_url() . $this->prefixUrl."pending_withdrawals";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'pending_withdrawals', $data);
        $this->template->render();
    }


    public function ajax_change_status_pending_withdrawals() {
        
        $res 			= array();
        $action         = $this->input->post("action");
        $action_type 	= $this->input->post("action_type");
        $statas         = $this->input->post("statas");
        $reason         = $this->input->post("reason");
        
        $id = $this->input->post('id');
        $postData=array();
        $sendStatus='';
        if($statas == 'C'){
            $sendStatus='A';
        }else
        {
           $sendStatus=$statas; 
        }
        $postData['entry_id']=$id;
        $postData['action']=$sendStatus;
        $postData['reason']=$reason;

    
        $apiUrl = CUSTOMER_WITHDRAW_REQUEST_URL;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $apiUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($postData),
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
          ),
        ));

       echo  $response = curl_exec($curl);die;
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
            $responseMeg = json_decode($response);
            if($responseMeg->error == "true"){
                $this->session->set_userdata('message', $responseMeg->message );
            }else{
                $this->session->set_userdata('smessage', $responseMeg->message );
            }
            print_r($response);
            return true;
        }
        
        $res = array("status"=>"success", "message"=>$action." Somthing wrong try!"); 
         echo json_encode($res); 
        return true;
               
        /*if($id>0){
			$table = "tbl_withdraw_requests";
			$select_fields = "$table.*";
			$joins = array();
			$cond = "id =".$id;
			$detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
            $customer_id    = $detail['customer_id'];

			$wallet_withdraw = array("withdraw_amount"=>$detail['amount'],"withdraw_description"=>"Paid By Admin","withdraw_customer_id"=>$detail['customer_id']);
				
                if($action =="Approve"){
					$respons = $this->wallet_withdraw($wallet_withdraw);
                    /********************
                    if($respons["status"] == "success"){
                        if($respons["status"] == "success" && $action =="Approve"){
                            $Update_data = array('status' =>$statas ,'reason' =>$reason ,'updated_at' =>time() );
                            $this->main_model->cruid_update( $table, $Update_data, $cond );
                            $alert_message  = "Congratulations! Your withdraw request has been approved";  
                            $noti_type      = "withdraw_request_approved";

                            $postData       = array("noti_type"=>$noti_type,"alert_message"=>$alert_message,"customer_id"=>$customer_id,"dbsave"=>true);
                            $this->RegardingDocumentsSendNotifs($postData);  
                        }
                    /********************
					echo true;
				}else{
					$res = array("status"=>"success", "message"=>$respons['message']);   
					echo json_encode($res); 
				}
            }else{
                    $alert_message  = $reason; 
                    $noti_type      = "withdraw_request_rejected";   
                    $postData       = array("noti_type"=>$noti_type,"alert_message"=>$alert_message,"customer_id"=>$customer_id,"dbsave"=>true);
                    $this->RegardingDocumentsSendNotifs($postData);
                    
                    $Update_data = array('status' =>$statas ,'reason' =>$reason ,'updated_at' =>time() );
                    $this->main_model->cruid_update( $table, $Update_data, $cond );

                    $res = array("status"=>"success", "message"=>$action." Somthing wrong try!");   
                    echo json_encode($res); 
                }
               
            }else{
                $res = array("status"=>"success", "message"=>$action." Somthing wrong try!");   
                echo json_encode($res); 
            }*/
    }


    // Particuler customer showing withdrawals-history customer documents 
    function withdrawals_set_id($id){

        // Create session array
        $sess_array = array(
            'withdrawals_customers_id' => $id
        );
        // Add user value in session
        $this->session->set_userdata('withdrawals_customers_id', $sess_array['withdrawals_customers_id']);
        echo json_encode($sess_array);
    }

    function ajax_withdrawals_history($offset = 0) {
        $twr = $this->tbl_withdraw_requests;
        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_withdrawals_history";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/withdrawals_history/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->table}", 'condition'=>"{$twr}.customer_id = {$this->table}.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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
        $condit = "";
        $customers_id = $set_data = $this->session->userdata('withdrawals_customers_id');
        if($customers_id != "")
        {
            $condit .="{$twr}.customer_id = '".$customers_id."'";
        }
        $select_fields = " ,firstname,lastname,email,phone,country_mobile_code,,country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'withrawals_status' and $val['value']) {
                    $condit .= "  AND {$twr}.`status` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$twr}.`created_at` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$twr}.`created_at` <=". strtotime($search."23:59:59");
                }
            }
        }
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $twr, $select_fields, $condit, "", $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."withdrawals_history/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_withdrawals_history";

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
        
        $this->load->view($this->prefixUrl.'ajax_withdrawals_history', $data);
    }

    public function withdrawals_history($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Withdrawals History List", site_url('section'));

        ob_start();
        $this->ajax_withdrawals_history($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_withdrawals_history";
        $data['this_url'] = base_url() . $this->prefixUrl."withdrawals_history";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'withdrawals_history', $data);
        $this->template->render();
    }


    public function wallet_withdraw($data=array())
    {

        $WALLET_TYPE=unserialize(WALLET_TYPE);
        $res = array();
        $withdraw_amount        = $data["withdraw_amount"];
        $withdraw_amount        = round(floatval($withdraw_amount), 2);
        $withdraw_description   = $data["withdraw_description"];        
        $wallet_type   			= "winning_wallet";       
        $customer_id            = $data["withdraw_customer_id"];


        if(!empty($withdraw_description) && !empty($wallet_type) && $withdraw_amount>0 && $customer_id>0){
            $currentAdmin = $this->session->userdata("adminId");
             $basic_customer_info = $this->customer_model->getBasicCustomerInfo($customer_id);
            if(empty($basic_customer_info)){
                $res = array("status"=>"error", "message"=>"No such customer exists!");
            }else if($basic_customer_info[$wallet_type ]<$withdraw_amount){
                $res = array("status"=>"error", "message"=>"Withdraw amount should be less then or equal to ".$basic_customer_info[$wallet_type].".");
            }
            else{
                $time = time();
                $table = "tbl_customer_wallet_histories";
                $final_amount = $basic_customer_info[$wallet_type]-$withdraw_amount;
                 $insert_data = array(
                                "customer_id"=>$customer_id,
                                "wallet_type"=>$WALLET_TYPE[$wallet_type],
                                "transaction_type"=>"DEBIT",
                                "transaction_id"=>"ADMIN-C-$time",
                                "type"=>"WALLET_WITHDRAW_ADMIN",
                                "previous_amount"=>$basic_customer_info[$wallet_type],
                                "amount"=>$withdraw_amount,
                                "current_amount"=>$final_amount,
                                "description"=>$withdraw_description,
                                "status"=>"S",
                                "created_by"=>$currentAdmin,
                                "created"=>$time
                            );
                $wallet_history_id = $this->main_model->cruid_insert($table, $insert_data);
                if($wallet_history_id>0){
                    $this->main_model->cruid_update($this->customer_model->current_model_table, array($wallet_type=>$final_amount), array("id"=>$customer_id));
                    $res = array("status"=>"success", "message"=>"Wallet has been Withdraw successfully!");
                }
                else{
                    $res = array("status"=>"error", "message"=>"Something went wrong while Withdraw the wallet!");
                }
            }
        }
        else{
            $res = array("status"=>"error", "message"=>"Input data is not valid!");
        }
        return $res;
        // echo json_encode($res);exit;
    }

    private function RegardingDocumentsSendNotifs($postfields){
        $apiUrl = APIURL."send_notification_to_customer";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $apiUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($postfields),
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          //echo "cURL Error #:" . $err;
        } else {
          //echo $response;
        }
        return true;
    }
    


    function export_pending_withdrawals($condit) {
       

        $twr = $this->tbl_withdraw_requests;
        ob_clean();

        $joins = array();
        $joins[1] = ['table'=>"{$this->table}", 'condition'=>"{$twr}.customer_id = {$this->table}.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
        $joins[4] = ['table'=>"tbl_customer_bankdetail tcbd", 'condition'=>"tcbd.id={$this->table}.bankdetail_id AND tcbd.customer_id = {$this->table}.id ",'jointype'=>'left'];
        $order_by = array(
            'field' => 'id',
            'type' => 'desc',
        );
        $order_by_other = array();

        
        
        $select_fields = " ,firstname,lastname,email,phone,country_mobile_code,,country.name as countryName, state.name as stateName, tcbd.account_number, tcbd.name, tcbd.ifsc ";
             
        $rows = $this->main_model->tabel_list(1000, $this->uri->segment(4), $joins, $order_by, $twr, $select_fields, $condit,"", $order_by_other);
        $records = $rows['list'];
        $data['records'] = $records;
        $var = $this->load->view($this->prefixUrl.'ajax_index_download', $data,true);


            $file = date("M_d_Y") . "_pending_withdrawals.xls";
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Cache-Control: max-age=0");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            //header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            header("Location: ".EXCEL_URL.$file);
            //echo EXCEL_URL.$file;die;
            
        
    }
    /***********************************************************************************************************************/
    
    function ajax_document_pan_requested($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_pan_requested";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/pan_requested/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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

        // LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )
        
        $condit = "{$this->table}.is_deleted = 'N' ";
        $tccreated =null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_customers`.`created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_customers`.`created` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {             
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
                           
                $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id )  WHERE ( tcp.status='A' OR tcp.status='P') AND (tc.paincard_id >0) $tccreated GROUP BY tcp.customer_id ORDER BY tcp.id DESC";
                $condit .= " AND {$this->table}.id IN ($wherin) ";
            
        
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

        $data['current_url'] = base_url() . $this->prefixUrl."pan_requested/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_pan_requested";

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
        
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function pan_requested($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_pan_requested($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_pan_requested";
        $data['this_url'] = base_url() . $this->prefixUrl."pan_requested";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    function ajax_document_verified_pan($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_verified_pan";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/verified_pan/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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

        // LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )
        
        $condit = "{$this->table}.is_deleted = 'N'";
        $tccreated =null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $from_date = strtotime($search."00:00:00");
                    $tccreated    .= "  AND `createdat` >=". $from_date;
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $to_date = strtotime($search."23:59:59");
                    $tccreated    .= "  AND `createdat` <=". $to_date;    
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {                
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
                  
        $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id )  WHERE ( tcp.status='A') AND (tc.paincard_id >0) $tccreated GROUP BY tcp.customer_id ORDER BY tcp.id DESC";
        $condit .= " AND {$this->table}.id IN ($wherin) ";
          
 

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

        $data['current_url'] = base_url() . $this->prefixUrl."verified_pan/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_verified_pan";

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
        
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function verified_pan($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_verified_pan($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_verified_pan";
        $data['this_url'] = base_url() . $this->prefixUrl."verified_pan";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    function ajax_document_pending_pan($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_pending_pan";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/pending_pan/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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

        // LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )
        
        $condit = "{$this->table}.is_deleted = 'N'";
        $tccreated = null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $from_date = strtotime($search."00:00:00");
                    $tccreated    .= "  AND `createdat` >=". $from_date;
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $to_date = strtotime($search."23:59:59");
                    $tccreated    .= "  AND `createdat` <=". $to_date;    
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {           
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
                           
               $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id )  WHERE ( tcp.status='P') AND (tc.paincard_id >0) $tccreated GROUP BY tcp.customer_id ORDER BY tcp.id DESC";
                $condit .= " AND {$this->table}.id IN ($wherin) ";
            
 
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

        $data['current_url'] = base_url() . $this->prefixUrl."pending_pan/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_pending_pan";

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
        
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function pending_pan($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_pending_pan($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_pending_pan";
        $data['this_url'] = base_url() . $this->prefixUrl."pending_pan";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    /*******************************************************************************************************************/
           
    function ajax_document_bank_requested($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_bank_requested";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/bank_requested/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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

        // LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )
        
        $condit = "{$this->table}.is_deleted = 'N'";
        $tccreated =null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $from_date = strtotime($search."00:00:00");
                    $tccreated    .= "  AND `createdat` >=". $from_date;
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $to_date = strtotime($search."23:59:59");
                    $tccreated    .= "  AND `createdat` <=". $to_date;    
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {               
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
                           
               $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )  WHERE ( tcb.status='A' OR tcb.status='P') AND (tc.bankdetail_id >0) $tccreated GROUP BY tcb.customer_id ORDER BY tcb.id DESC";
                $condit .= " AND {$this->table}.id IN ($wherin) ";
            
        
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

        $data['current_url'] = base_url() . $this->prefixUrl."bank_requested/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_bank_requested";

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
        
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function bank_requested($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_bank_requested($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_bank_requested";
        $data['this_url'] = base_url() . $this->prefixUrl."bank_requested";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    function ajax_document_verified_bank($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_document_verified_bank";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/verified_bank/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
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

        // LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )
       
        $condit = "{$this->table}.is_deleted = 'N'";
        $tccreated = null;
        $select_fields = ",country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $keyword = addslashes(trim($val['value']));
                $search = addslashes(trim($val['value']));
                //$array = explode(" ", $search);
                //foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                //}


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $from_date = strtotime($search."00:00:00");
                    $tccreated    .= "  AND `createdat` >=". $from_date;
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $to_date = strtotime($search."23:59:59");
                    $tccreated    .= "  AND `createdat` <=". $to_date;    
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {          
                $from_date  = $this->session->userdata('from_date');
                $to_date    = $this->session->userdata('to_date');
                $tccreated    .= "  AND `createdat` >=". $from_date;
                $tccreated    .= "  AND `createdat` <=". $to_date;          
        }
                           
                $wherin = "SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id )  WHERE ( tcb.status='A') AND (tc.bankdetail_id >0) $tccreated GROUP BY tcb.customer_id ORDER BY tcb.id DESC";
                $condit .= " AND {$this->table}.id IN ($wherin) ";
            
        
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

        $data['current_url'] = base_url() . $this->prefixUrl."verified_bank/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_document_verified_bank";

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
        
        $this->load->view($this->prefixUrl.'ajax_document_approval_pending', $data);
    }

    public function verified_bank($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Document Approval Pending {$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_document_verified_bank($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_document_verified_bank";
        $data['this_url'] = base_url() . $this->prefixUrl."verified_bank";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'document_approval_pending', $data);
        $this->template->render();
    }

    public function is_unique_admin() {
        $table="tbl_customers";

        $is_deleted = [];
        if ($this->db->field_exists('is_deleted',$table)){
            $is_deleted = ['is_deleted' => 'N'];
        }
        $query = $this->db->limit(1)->get_where($table, array('is_fake'=>'1', 'is_admin'=>'1')+$is_deleted );

        return $query->num_rows() === 0;
    }

    public function is_unique_admin_again($str, $field) {
        list($table, $field, $id) = explode('.', $field);

        $is_deleted = [];
        if ($this->db->field_exists('is_deleted',$table)){
            $is_deleted = ['is_deleted' => 'N'];
        }
        if (isset($this->db)) {
            $query = $this->db->limit(1)->get_where($table, array("id <>" => $id,'is_fake'=>'1', 'is_admin'=>'1') + $is_deleted );
            return $query->num_rows() === 0;
        }

        return FALSE;
    }
//End of class
}
