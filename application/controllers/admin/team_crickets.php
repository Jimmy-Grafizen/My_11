<?php

require_once('base.php');
class Team_crickets extends Base {

    private $limit = 10;
    private $table = 'tbl_cricket_teams';
    private $image = '';
    private $prefixUrl = 'admin/team_crickets/';
    private $name = 'Team Crickets'; // For singular
    private $names = 'Team Crickets'; //plural form 

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
        $joins[1] = ['table'=>'tbl_users', 'condition'=>"{$this->table}.created_by = tbl_users.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"tbl_cricket_team_player_galleries tctpg", 'condition'=>"{$this->table}.id = tctpg.team_id",'jointype'=>'left'];
        $order_by = array(
            'field' => 'updated_at',
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
                'field' => 'updated_at',
                'type' => 'desc',
            );
        }
        $table = $this->table;
        $condit = "{$this->table}.is_deleted ='N'";
        $select_fields = " ,firstname,GROUP_CONCAT(`tctpg`.`file_name` ORDER BY `tctpg`.`team_id`) as file_name";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`name` LIKE '" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (`created_at` LIKE '%" . $search . "%')";
                }
            }
        }
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, "tbl_cricket_teams.id", $order_by_other);
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

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->names}";

         $this->form_validation->set_rules('name', 'Name', "trim|required|is_unique[tbl_cricket_teams.name]");
         $this->form_validation->set_rules('sort_name', 'Sort Name', "trim|required|is_unique[tbl_cricket_teams.sort_name]");
         $this->form_validation->set_message('is_unique', 'This %s is already taken in ' . SITE_TITLE . ". Please try different");
         //$this->form_validation->set_message('sort_name.is_unique', 'This Sort name is already taken in ' . SITE_TITLE . ". Please try different");
        
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
				/********************************************************/
			 // If file upload form submitted
	
        if(!empty($_FILES['images']['name'])){				
				$config['upload_path']          = TEAMCRICKET_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;           
				//validating files first
				$_FILES['image']['name']     = $_FILES['images']['name'];
                $_FILES['image']['type']     = $_FILES['images']['type'];
                $_FILES['image']['tmp_name'] = $_FILES['images']['tmp_name'];
                $_FILES['image']['error']     = $_FILES['images']['error'];
                $_FILES['image']['size']     = $_FILES['images']['size'];
                /*************************************************************************/
                $redirectimg = $this->prefixUrl.'add';
                    $fileinfo   = @getimagesize($_FILES["images"]["tmp_name"]);
                    $width      = $fileinfo[0];
                    $height     = $fileinfo[1];
                    $ratio      = $height/$width;
                    $ratio      = round($ratio,2);
                    //dd($fileinfo);die();
                   // Validate image file dimension
                   if($width != "300" && $height != "300"){
                        $data['validation_errors'] = "<p>Image dimension should be within 300 X 300.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        $this->session->set_userdata('message', "<p>Image dimension should be within 300 X 300.</p>");
                        redirect($redirectimg);
                       
                   }else if($ratio!="1" && $ratio!="1"){
                        $data['validation_errors'] = "<p>Image dimension should be within 300 X 300.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        $this->session->set_userdata('message', "<p>Image dimension should be within 300 X 300.</p>");
                        redirect($redirectimg);
                   }
                /*************************************************************************/

					//changing file name for selected
					$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
					$config['file_name']  = rand()."_".time()."_teamcricket.$ext";
					$file_upload = $this->upload_files($config, "image");
					
					if($file_upload['status']=="success"){						
						$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], TEAMCRICKET_IMAGE_LARGE_PATH, TEAMCRICKET_IMAGE_THUMB_PATH);
						$data_image = $file_upload['data']['file_name'];
						$data = array(
								'name' => $this->input->post('name'),
                                'sort_name' => $this->input->post('sort_name'),
								'status' => 'A',
								'logo' => $data_image,
								'created_by' => $this->session->userdata('adminId'),
								'updated_by' => $this->session->userdata('adminId'),
								'created_at' => time(),               
								'updated_at' => time(),               
							);
						$table = $this->table;
						$business_id = $this->main_model->cruid_insert($table, $data);				
						$data = [];
					}
				else{
					if(!empty($file_upload['data'])){
						$data['validation_errors'] = $file_upload['data'];
					}else{
						$data['validation_errors'] = "<p>There was an error while uploading image.</p>";
					}
				}
		} 
            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

// edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "id ='" . $user_name . "'";
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
	
		$old_files_to_remove = array();
		$selected_image_old = $user_detail['logo'];
		
        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Admins Details";

            $this->form_validation->set_rules('name', 'Name', "trim|required|is_unique_again[tbl_cricket_teams.name.$id]");
            $this->form_validation->set_rules('sort_name', 'Sort Name', "trim|required|is_unique_again[tbl_cricket_teams.sort_name.$id]");
            $this->form_validation->set_message('is_unique_again', 'This %s is already taken in ' . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
				
				$Update_data = array(
					'name' => $this->input->post('name'),
                    'sort_name' => $this->input->post('sort_name'),
					'updated_by' => $this->session->userdata('adminId'),
					'updated_at' => time(),               
				);
			if(!empty($_FILES['images']['name'])){
				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(TEAMCRICKET_IMAGE_LARGE_PATH)) {
					mkdir(TEAMCRICKET_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(TEAMCRICKET_IMAGE_THUMB_PATH)) {
					mkdir(TEAMCRICKET_IMAGE_THUMB_PATH, 0777, true);
				 } 
				}
				$config['upload_path']          = TEAMCRICKET_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;
                /*************************************************************************/
                $redirectimg = $this->prefixUrl.'edit/'.$user_name."?return=".$url;
                    $fileinfo   = @getimagesize($_FILES["images"]["tmp_name"]);
                    $width      = $fileinfo[0];
                    $height     = $fileinfo[1];
                    $ratio      = $height/$width;
                    $ratio      = round($ratio,2);
                    //dd($fileinfo);die();
                   // Validate image file dimension
                   if($width != "300" && $height != "300"){
                        $data['validation_errors'] = "<p>Image dimension should be within 300 X 300.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        $this->session->set_userdata('message', "<p>Image dimension should be within 300 X 300.</p>");
                        redirect($redirectimg);
                       
                   }else if($ratio!="1" && $ratio!="1"){
                        $data['validation_errors'] = "<p>Image dimension should be within 300 X 300.</p>";
                        $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                        $this->template->render();
                        $this->session->set_userdata('message', "<p>Image dimension should be within 300 X 300.</p>");
                        redirect($redirectimg);
                   }
                /*************************************************************************/

				//changing file name for selected
				$ext = pathinfo($_FILES['images']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_game.$ext";
				$file_upload = $this->upload_files($config, "images");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, TEAMCRICKET_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], TEAMCRICKET_IMAGE_LARGE_PATH, TEAMCRICKET_IMAGE_THUMB_PATH);
					$Update_data['logo'] = $file_upload['data']['file_name'];
					
					$old_selected = $selected_image_old;								
					$old_files_to_remove[] = TEAMCRICKET_IMAGE_LARGE_PATH.$old_selected;
					$old_files_to_remove[] = TEAMCRICKET_IMAGE_THUMB_PATH.$old_selected;
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

    // Save image user profile
    function image_change_save() {
        $table = $this->table;

        $save_image         = $this->input->post("save_image");
        $apply_all          = $this->input->post("apply_all");
        $player_unique_id   = $this->input->post('player_unique_id');
        $match_players_id   = $this->input->post('match_players_id');
        $match_unique_id    = $this->input->post('match_unique_id');
        $team_id            = $this->input->post('team_id');

        if(!empty($team_id) && !empty($match_unique_id) && $apply_all !=null && $apply_all =='on' ){
            $imgedata = explode("_with_",$save_image);
            $cond = array(
                'match_unique_id' => $match_unique_id,
                'team_id' => $team_id,
            );
            $data = array(
                'image' => end($imgedata),
            );
            $this->main_model->cruid_update($table, $data, $cond);
            echo PLAYER_IMAGE_THUMB_URL.end($imgedata);
        }
        elseif(!empty($save_image)){
            $imgedata = explode("_with_",$save_image);
            $cond = array(
                'id' => current($imgedata),
            );
            $data = array(
                'image' => end($imgedata),
            );
            $this->main_model->cruid_update($table, $data, $cond);
            echo PLAYER_IMAGE_THUMB_URL.end($imgedata);
        }else{
            echo 0;
        }
        //$this->session->set_userdata('smessage', "Selected {$this->name} successfully activated");
    }

    function image_upload_save(){

        $team_id =  $this->input->post('team_id');
        $table = "tbl_cricket_teams";
        $cond = "id ='" . $team_id . "'";
        $select_fields = "$table.id";
        $joins = array();
        $data = [];
        $data['html'] = '';
        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

                    /********************************************************/
             // If file upload form submitted
        if(!empty($_FILES['player_image']['name']) && !empty($user_detail['id'])){
            $team_id = $user_detail['id'];
            if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                if (!is_dir(PLAYER_IMAGE_LARGE_PATH)) {
                    mkdir(PLAYER_IMAGE_LARGE_PATH, 0777, true);
                }if (!is_dir(PLAYER_IMAGE_THUMB_PATH)) {
                    mkdir(PLAYER_IMAGE_THUMB_PATH, 0777, true);
                }
            }
                $config['upload_path']          = PLAYER_IMAGE_LARGE_PATH;
                $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                $config['max_size']             = ALLOWED_FILE_SIZE;

            $filesCount = count($_FILES['player_image']['name']);
            for($i = 0; $i < $filesCount; $i++){
                if(!empty($_FILES['player_image']['name'][$i])){
                //validating files first
                $_FILES['player_image']['name']     = $_FILES['player_image']['name'][$i];
                $_FILES['player_image']['type']     = $_FILES['player_image']['type'][$i];
                $_FILES['player_image']['tmp_name'] = $_FILES['player_image']['tmp_name'][$i];
                $_FILES['player_image']['error']    = $_FILES['player_image']['error'][$i];
                $_FILES['player_image']['size']     = $_FILES['player_image']['size'][$i];

                    //changing file name for selected
                    $ext = pathinfo($_FILES['player_image']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_player.$ext";
                    $file_upload = $this->upload_files($config, "player_image");
                    

                    if($file_upload['status']=="success"){
                        
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], PLAYER_IMAGE_LARGE_PATH, PLAYER_IMAGE_THUMB_PATH);
                        $data_image = $file_upload['data']['file_name'];
                        $cruid_insert = array(
                            'team_id' => $team_id,
                            'file_name' => $data_image,
                            'status' => 'A',
                            'created_by' => $this->session->userdata('adminId'),
                            'updated_by' => $this->session->userdata('adminId'),
                            'created_at' => time(),               
                            'updated_at' => time(),               
                        );
                        $table = "tbl_cricket_team_player_galleries";
                        $lastId = $this->main_model->cruid_insert($table, $cruid_insert);
                       
                        $data['html'] = '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'.PLAYER_IMAGE_THUMB_URL.$data_image.'"><input type="radio" name="image_radio" value="'.$team_id.'_with_'.$data_image.'"><i class="fa fa-check hidden"></i></label></div>';
                    }
                else{
                    if(!empty($file_upload['data'])){
                        $data['html'] = '';
                        $data['validation_errors'] = $file_upload['data'];
                    }else{
                        $data['html']='';
                        $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                    }
                }
              }
            }
        }
        echo ($data['html']);
            /********************************************************/
    }
//End of Countries class
}
