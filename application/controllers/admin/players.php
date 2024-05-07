<?php

require_once('base.php');
class Players extends Base {

    private $limit = 10;
    private $table = 'tbl_cricket_players';
    private $tbl_cricket_match_players = 'tbl_cricket_match_players';
    private $tbl_cricket_matches = 'tbl_cricket_matches';
    private $tbl_cricket_teams = 'tbl_cricket_teams';
    private $tbl_player_galleries = 'tbl_cricket_player_galleries';
    private $image = '';
    private $prefixUrl = 'admin/players/';
    private $name = 'Player'; // For singular
    private $names = 'Players'; //plural form 
	private $positions = null;
	private $player_bets = null;
	private $player_bowls = null;
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
		$this->positions = unserialize(PLAYER_POSITIONS);
		$this->player_bets = unserialize(PLAYER_BETS);
		$this->player_bowls = unserialize(PLAYER_BOWLS);
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
		//$joins[1] = ['table'=>'tbl_games', 'condition'=>'tbl_cricket_players.game_id = tbl_games.id','jointype'=>'left'];
		//$joins[2] = ['table'=>'tbl_game_types', 'condition'=>'tbl_cricket_players.game_type_id = tbl_game_types.id','jointype'=>'left'];
		$joins[1] = ['table'=>'tbl_countries', 'condition'=>'tbl_cricket_players.country_id = tbl_countries.id','jointype'=>'left'];

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
        $condit = "{$this->table}.is_deleted ='N'";
        $select_fields = ",tbl_countries.name as country_name ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "tbl_cricket_players.name LIKE '" . $keyword . "%'";
                    // $str[] = "tbl_cricket_players.name LIKE '%" . $keyword . "%' OR tbl_countries.name LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'position' and $val['value']) {
                    $condit .= "  AND position ='$search' ";
                }
                if ($val['name'] == 'bets' and $val['value']) {
                    $condit .= "  AND bets ='$search'" ;
                }
                if ($val['name'] == 'bowls' and $val['value']) {
                    $condit .= "  AND bowls ='$search'" ;
                }
                if ($val['name'] == 'country_id' and $val['value']) {
                    $condit .= "  AND country_id ='$search'" ;
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
		// echo"<per>";print_r($records);die;
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
		$data['positions'] = $this->positions;
		$data['bets'] = $this->player_bets;
		$data['bowls'] = $this->player_bowls;
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
		$data['name'] = "{$this->name}";
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
        $this->breadcrumbs->push("Add {$this->name}", site_url('section'));

        $redirect = $this->prefixUrl.'index';

        $data['name'] = "{$this->name}";
        $data['positions'] = $this->positions;
		$data['bets'] = $this->player_bets;
		$data['bowls'] = $this->player_bowls;
        $data['title'] = "Add New {$this->name}";
		
		$player="";
		if($this->input->post('name')){
			$player=explode('@',$this->input->post('name'));
			
			$_POST['uniqueid']  = $player[0];
			$_POST['name'] 		= $player[1];
			
			$this->form_validation->set_rules('uniqueid', 'Player Name', "trim|required|is_unique[{$this->table}.uniqueid]");
			$this->form_validation->set_message('is_unique', "This {$this->name} is already taken in " . SITE_TITLE . ". Please try different"); 
			$this->form_validation->set_rules('name', 'Name', "trim|required");
		}		
         
        if ($this->form_validation->run() == FALSE) {
			
			if(isset($_POST['name']) && $this->form_validation->run() == FALSE){
				if($this->form_validation->error_array()){
					 $ERROR = $this->form_validation->error_array();
					 if(isset($ERROR['name'])){
						 $this->session->set_userdata('message',$ERROR['name']);
					 }else{
						$this->session->set_userdata('message',$ERROR['uniqueid']);
					 }
				}
				echo APP_URL."admin/players/add";
				//print_r($this->form_validation->error_array());
				//die;
			}else{				
				$this->template->write_view('contents', $this->prefixUrl.'add', $data);
				$this->template->render();
			}
        } else {	
			
			
			// $get_player_curl = $this->curl_get_method(CRICAPI_PLAYER_STATISTICS."&pid=".$player[0]);
            $get_player_curl = $this->curl_get_method(ENTITYSPORT_PLAYER_DETAIL.$player[0]);
			$get_player = json_decode($get_player_curl);
			$countryId = $this->get_country($get_player->country);

            $position= !empty($get_player->playingRole)? $get_player->playingRole:" "; 
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
            
            $dob = !empty($get_player->born)? $get_player->born:"";
            $dob =  explode(',',$dob);
            if(count($dob)>=2){
                $date = date_create(trim($dob[0])." ".trim($dob[1]));
                $dob  = date_format($date,"Y-m-d");
            } else{
                $dob ="";
            } 

            $data = array(
				'name' => $player[1],
				'uniqueid' =>$player[0],
				//'game_id' => $this->input->post('game_id'),
				//'game_type_id' => $this->input->post('game_type_id'),
				'position' =>$position,
				'bets' =>$get_player->battingStyle,
				'bowls' => $get_player->bowlingStyle,
				'summary'=>$get_player_curl,
				'country_id' =>$countryId,
				'dob' =>$dob,
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
            $table = $this->table;
            $player_id = $this->main_model->cruid_insert($table, $data);
			/********************************************************/
			 // If file upload form submitted
        if(!empty($_FILES['images']['name'])){
			
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

            $filesCount = count($_FILES['images']['name']);
            for($i = 0; $i < $filesCount; $i++){
				if(!empty($_FILES['images']['name'][$i])){
				//validating files first
				$_FILES['image']['name']     = $_FILES['images']['name'][$i];
                $_FILES['image']['type']     = $_FILES['images']['type'][$i];
                $_FILES['image']['tmp_name'] = $_FILES['images']['tmp_name'][$i];
                $_FILES['image']['error']     = $_FILES['images']['error'][$i];
                $_FILES['image']['size']     = $_FILES['images']['size'][$i];

					//changing file name for selected
					$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
					$config['file_name']  = rand()."_".time()."_player.$ext";
					$file_upload = $this->upload_files($config, "image");
					

					if($file_upload['status']=="success"){
						
						$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], PLAYER_IMAGE_LARGE_PATH, PLAYER_IMAGE_THUMB_PATH);
						$data_image = $file_upload['data']['file_name'];
						$data = array(
							'player_id' => $player_id,
							'file_name' => $data_image,
							'status' => 'A',
							'created_by' => $this->session->userdata('adminId'),
							'updated_by' => $this->session->userdata('adminId'),
							'created_at' => time(),               
							'updated_at' => time(),               
						);
						$table = $this->tbl_player_galleries;
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
			}
		}
			/********************************************************/
            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            //redirect($redirect);
			echo APP_URL.$redirect;
        }
    }
/*
** Get Country
*/	
function get_country($country){
	$table = 'tbl_countries';
    $this->db->select('id');
    $this->db->from($table);
    $this->db->where('name', $country);
    $this->db->where('status', 'A');
    $query = $this->db->get();
    $num = $query->num_rows();
    if ($num > 0) {
        $row = $query->row(); 
		return $row->id;
    } else {
			$data = array(
                'name' => $country,
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
            $table = 'tbl_countries';
            return  $this->main_model->cruid_insert($table, $data);
    }
}
/*
** Check Player Unique
*/	
function check_player() {
	$table = $this->table;
    $player=explode('@',$this->input->post('name'));
    $this->db->select('id');
    $this->db->from($this->table);
    $this->db->where('uniqueid', $player[0]);
    $query = $this->db->get();
    $num = $query->num_rows();
    if ($num > 0) {
        return FALSE;
    } else {
        return TRUE;
    }
}
/*
** Check Player Unique
*/	
function is_unique_again() {
	$table = $this->table;
    $player=explode('@',$this->input->post('name'));
    $this->db->select('id');
    $this->db->from($this->table);
    $this->db->where('uniqueid', $player[0]);
    $query = $this->db->get();
    $num = $query->num_rows();
    if ($num > 1) {
        return FALSE;
    } else {
        return TRUE;
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
		$redirect = $this->prefixUrl.'index';
        $table = $this->table;
        $cond = "id ='" . $user_name . "'";
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $player_id = $user_detail['id'];
            $uniqueid = $user_detail['uniqueid'];
            $name = $user_detail['name'];
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Admins Details";
			$data['name'] = $this->name;
			$data['positions'] = $this->positions;
			$data['bets'] = $this->player_bets;
			$data['bowls'] = $this->player_bowls;
            /* $this->form_validation->set_rules('name', 'Name', "trim|required|is_unique_again[tbl_cricket_players.name.$id]");
            $this->form_validation->set_message('is_unique_again', 'This Player name is already taken in ' . SITE_TITLE . ". Please try different"); */
			$this->form_validation->set_rules('name', 'Name', "trim|required|callback_is_unique_again");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
				$player=explode('@',$this->input->post('name'));
				// $get_player_curl = $this->curl_get_method(CRICAPI_PLAYER_STATISTICS."&pid=".$player[0]);
                $get_player_curl = $this->curl_get_method(ENTITYSPORT_PLAYER_DETAIL.$player[0]);
				$get_player = json_decode($get_player_curl);
				$countryId = $this->get_country($get_player->country);

                $position = !empty($this->input->post('position'))? $this->input->post('position'):" ";

                $dob = !empty($get_player->born)? $get_player->born:"";
                $dob =  explode(',',$dob);
                if(count($dob)>=2){
                    $date = date_create(trim($dob[0])." ".trim($dob[1]));
                    $dob  = date_format($date,"Y-m-d");
                } else{
                    $dob ="";
                } 
                $dobchange = $this->input->post('dob');
                $dob       = date("Y-m-d", strtotime( str_ireplace("/", "-", $dobchange) ) );
        		$data = array(
					'name' =>$player[1],
					'uniqueid' =>$player[0],
					'position' =>$position,
					'bets' =>$get_player->battingStyle,
					'bowls' => $get_player->bowlingStyle,
					'summary'=>$get_player_curl,
					'country_id' =>$countryId,
                    'dob' => $dob,
					'updated_by' => $this->session->userdata('adminId'),
					'updated_at' => time(),               
				);
			$this->main_model->cruid_update($table, $data, $cond);
				
        /********************************************************/
			 // If file upload form submitted
        if(!empty($_FILES['images']['name'])){
			if (IMAGE_UPLOAD_TYPE!="BUCKET"){
				if (!is_dir(PLAYER_IMAGE_LARGE_PATH)) {
					mkdir(PLAYER_IMAGE_LARGE_PATH, 0777, true);
				}if (!is_dir(PLAYER_IMAGE_THUMB_PATH)) {
					mkdir(PLAYER_IMAGE_THUMB_PATH, 0777, true);
				} 
			}
				$config['upload_path']          = PLAYER_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;

            $filesCount = count($_FILES['images']['name']);
            for($i = 0; $i < $filesCount; $i++){
				if(!empty($_FILES['images']['name'][$i])){
				//validating files first
				$_FILES['image']['name']     = $_FILES['images']['name'][$i];
                $_FILES['image']['type']     = $_FILES['images']['type'][$i];
                $_FILES['image']['tmp_name'] = $_FILES['images']['tmp_name'][$i];
                $_FILES['image']['error']     = $_FILES['images']['error'][$i];
                $_FILES['image']['size']     = $_FILES['images']['size'][$i];

					//changing file name for selected
					$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
					$config['file_name']  = rand()."_".time()."_player.$ext";
					$file_upload = $this->upload_files($config, "image");
					

					if($file_upload['status']=="success"){
						
						$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], PLAYER_IMAGE_LARGE_PATH, PLAYER_IMAGE_THUMB_PATH);
						$data_image = $file_upload['data']['file_name'];
						$data = array(
							'player_id' => $player_id,
							'file_name' => $data_image,
							'status' => 'A',
							'created_by' => $this->session->userdata('adminId'),
							'updated_by' => $this->session->userdata('adminId'),
							'created_at' => time(),               
							'updated_at' => time(),               
						);
						$table = $this->tbl_player_galleries;
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
			}
		}
			/********************************************************/
                $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                //redirect($url);
				echo APP_URL.$redirect;
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
			echo APP_URL.$redirect;
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
	
	
	
// Player Image delete 
    public function removefileplayer() {
        $user_name = $this->input->post('id');
        $this->loginCheck($this->prefixUrl.'delete/' . $user_name);
        $this->checkUser();

        $table = $this->tbl_player_galleries;
        $cond = "id ='" . $user_name . "'";
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
				
		$old_files_to_remove = array();
		$selected_image_old = $user_detail['file_name'];
		
        if (!empty($user_detail)) {
					$old_selected = $selected_image_old;								
					$old_files_to_remove[] = PLAYER_IMAGE_LARGE_PATH.$old_selected;
					$old_files_to_remove[] = PLAYER_IMAGE_THUMB_PATH.$old_selected;
				
			
			$Update_data = array(
				'is_deleted' => "Y",
			);
                $this->main_model->cruid_update($table, $Update_data, $cond);
				//$this->removeFiles($old_files_to_remove);
                //$this->session->set_userdata('smessage', $this->name.' Successfully updated');
                //redirect($url);
				echo 1;
            }
         else {
            //$this->session->set_userdata('message', "Sorry, this {$this->name} not available");
           // redirect($url);
		   echo 0;
        }
    }
// activate all 
    public function activateall($current_url) {
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

//  deactivate all 
    public function deactivateall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
		
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
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

    function get_matches_and_teams($id){


        $table = "{$this->tbl_cricket_matches} tcm";

        $cond = "tcm.match_progress = 'F' AND tcm.is_deleted='N' AND tcm.unique_id NOT IN(SELECT `match_unique_id` FROM {$this->tbl_cricket_match_players} WHERE player_unique_id = '$id' )";

        $select_fields = "tcm.*,tct1.name team1_name ,tct2.name team2_name";

        $joins = [];
        $joins[1] = ['table'=>"{$this->tbl_cricket_teams} tct1", 'condition'=>"team_1_id = tct1.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_cricket_teams} tct2", 'condition'=>"team_2_id = tct2.id",'jointype'=>'left'];

        $group_by = "tcm.id";
        $order_by = array(
            'as_table' => 'tcm',
            'field' => 'match_date',
            'type' => 'ASC',
        );
        $matches['matches'] = $this->main_model->cruid_select_array($table, $select_fields, $joins, $cond ,$group_by,$order_by);

        $matches['player_unique_id']=$id;

        if(!empty($matches) ){
                $this->load->view($this->prefixUrl.'add_player_in_match', $matches);
            }else{
                echo "not found!";
            }

    }

    // activate user profile
    public function add_matche_player() {

        if($this->input->post() !=null){

            $match_unique_id     = $this->input->post('match_unique_id');
            $player_unique_id    = $this->input->post('player_unique_id');
            $team_id             = $this->input->post('team_id');
            $is_in_playing_squad = $this->input->post('is_in_playing_squad');

            $commonInsert= array(
                    'status'             => 'A',
                    'is_in_playing_squad'=> $is_in_playing_squad,
                    'created_by'         => $this->session->userdata('adminId'),
                    'updated_by'         => $this->session->userdata('adminId'),
                    'created_at'         => time(),               
                    'updated_at'         => time(),               
                );
            if(empty(trim($match_unique_id))){
                echo json_encode(["status"=>0,"msg"=>"Please select Match."]);
                exit();
            }
            if(empty(trim($team_id))){
                echo json_encode(["status"=>0,"msg"=>"Please select team."]);
                exit();
            }
            if(empty(trim($is_in_playing_squad))){
                echo json_encode(["status"=>0,"msg"=>"Please select playing squad."]);
                exit();
            }

            $match_players_data = ["match_unique_id"=>$match_unique_id,"player_unique_id"=>$player_unique_id,"team_id"=>$team_id];

            $match_players_id = $this->main_model->insert_or_id_get("tbl_cricket_match_players", $match_players_data, array_merge($match_players_data,$commonInsert));
            
            if($match_players_id){
                echo json_encode(["status"=>1,"msg"=>"Player successfully added"]);
            }else{
                 echo json_encode(["status"=>0,"msg"=>"Error: Something went wrong while saving the request!"]);
            }
        }else{
           echo json_encode(["status"=>0,"msg"=>"Error: Something went wrong while saving the request method!"]);
        }

    }
//End class
}