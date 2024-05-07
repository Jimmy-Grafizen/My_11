<?php

require_once('base.php');
class Match_players extends Base {

    private $limit = 10;
    private $table = 'tbl_cricket_match_players';
    private $tbl_cricket_matches = 'tbl_cricket_matches';
    private $tbl_cricket_series = 'tbl_cricket_series';
    private $tbl_games = 'tbl_games';
    private $tbl_game_types = 'tbl_game_types';
    private $tbl_teams = 'tbl_cricket_teams';
    private $image = '';
    private $prefixUrl = 'admin/match_players/';
    private $name = 'Match player'; // For singular
    private $names = 'Match Players'; //plural form 

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
		$joins[1] = ['table'=>"{$this->tbl_cricket_series} series", 'condition'=>"{$this->table}.series_id=series.id",'jointype'=>'left'];
		$joins[2] = ['table'=>"{$this->tbl_games} game", 'condition'=>"{$this->table}.game_id = game.id",'jointype'=>'left'];
		$joins[3] = ['table'=>"{$this->tbl_game_types} game_type", 'condition'=>"{$this->table}.game_type_id = game_type.id",'jointype'=>'left'];
		$joins[4] = ['table'=>"$this->tbl_teams tbl_teams1", 'condition'=>"{$this->table}.team_1_id = tbl_teams1.id",'jointype'=>'left'];
		$joins[5] = ['table'=>"$this->tbl_teams tbl_teams2", 'condition'=>"{$this->table}.team_2_id = tbl_teams2.id",'jointype'=>'left'];

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
        $select_fields = ", series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
					if ($val['name'] == 'search' and $val['value']) {
						$str[] = "{$this->table}.`unique_id` LIKE '%" . $keyword . "%' OR {$this->table}.`name` LIKE '%" . $keyword . "%' OR series.`name` LIKE '%" . $keyword . "%' OR game.`name` LIKE '%" . $keyword . "%' OR game_type.`name` LIKE '%" . $keyword . "%' OR tbl_teams1.`name` LIKE '%" . $keyword . "%' OR tbl_teams2.`name` LIKE '%" . $keyword . "%'";
					}
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'series_id' and $val['value']) {
                    $condit .= "  AND `series_id` =". $search;
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
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
		
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
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
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
		$unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->name}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->name}";
		$data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
		/****************************/
		

		$tbl_match = $this->tbl_cricket_matches;
        $cond = "{$tbl_match}.id ='" . $unique_id . "'";
        $select_fields = "$tbl_match.*, tblseries.name as series_name";
        $joins = array(['table'=>$this->tbl_cricket_series." tblseries", 'condition'=>"{$tbl_match}.series_id = tblseries.id",'jointype'=>'left']);
        $match_detail = $this->main_model->cruid_select($tbl_match, $select_fields, $joins, $cond);
		$data['match_detail'] = $match_detail;

		$commonInsert= array(
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
		if($this->input->post('matche_data') && count($_POST)>0){
			$matche_data = (array)json_decode($this->input->post('matche_data'));
			$match_date = strtotime($matche_data['dateTimeGMT']);
			$team_1 = $matche_data['team-1'];
			$team_2 = $matche_data['team-2'];
			$match_name = $team_1. ' vs '.$team_2; 
			$unique_id = $matche_data['unique_id'];
			$game_type = $matche_data['type'];
			$game 		= 'Cricket';
			
			$team_1_data = ['name' => $team_1];				
			$team_1_id = $this->main_model->insert_or_id_get('tbl_cricket_teams',['name'=>$team_1], array_merge($team_1_data,$commonInsert));
		
			$team_2_data = ['name' => $team_2];				
			$team_2_id = $this->main_model->insert_or_id_get('tbl_cricket_teams',['name'=>$team_2], array_merge($team_2_data,$commonInsert));

			$game_data = ['name' => $game];				
			$game_id = $this->main_model->insert_or_id_get('tbl_games',['name'=>$game], array_merge($game_data,$commonInsert));
		
			$game_type_data = ['name' => $game_type,'game_id' => $game_id];
			$game_type_id = $this->main_model->insert_or_id_get('tbl_game_types',['name'=>$game_type], array_merge($game_type_data,$commonInsert));
		
		
		
			$_POST['unique_id'] = $unique_id;


		}
		
         $this->form_validation->set_rules('unique_id', 'Match Name', "trim|required|is_unique[{$this->table}.unique_id]");
            $this->form_validation->set_message('is_unique', "This {$this->name} is already taken in " . SITE_TITLE . ". Please try different"); 
			$this->form_validation->set_rules('series_id', 'Series Name', "trim|required");

        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
	
            $data = array(
                'match_date' => $match_date,
                'name' => $match_name,
                'game_id' => $game_id,
                'game_type_id' => $game_type_id,
                'team_1_id' => $team_1_id,
                'team_2_id' => $team_2_id,
                'unique_id' => $unique_id,
                'series_id' => $this->input->post('series_id'),
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $data);

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
        $cond = "{$this->table}.id ='" . $user_name . "'";
        $select_fields = "$table.*, tbl_match.unique_id";
        $joins = array(['table'=>$this->tbl_cricket_matches." tbl_match", 'condition'=>"{$this->table}.match_unique_id = tbl_match.id",'jointype'=>'left']);


        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update {$this->name} Details";
			$data['name'] = $this->name;
			$data['names'] = $this->names;
			$data['table'] = $this->table;
			$data['tbl_cricket_series'] = $this->tbl_cricket_series;
		
            $this->form_validation->set_rules('name', 'Name', "trim|required|is_unique_again[{$this->table}.name.$id]");
            $this->form_validation->set_message('is_unique_again', 'This City is already taken in ' . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
				$data = array(
					'name' => $this->input->post('name'),
					'state_id' => $this->input->post('state_id'),
					'updated_by' => $this->session->userdata('adminId'),
					'updated_at' => time(),               
				);

                $this->main_model->cruid_update($table, $data, $cond);
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
        //$this->session->set_userdata('smessage', "Selected {$this->name} successfully activated");
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
       // $this->session->set_userdata('smessage', "Selected {$this->name} successfully deactivated");
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
	
	// activate user profile
    public function add_mache_player() {
		if($this->input->post('fields') !=null){
		$fields = json_decode($this->input->post('fields'));
		$commonInsert= array(
                'status' 	 => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
		$team_name = $fields->team_name;
		$team_data = ['name' => $team_name];				
		$team_id = $this->main_model->insert_or_id_get('tbl_cricket_teams',['name'=>$team_name], array_merge($team_data,$commonInsert));
		
		
		
		$player_data = ['name' => $fields->name,'uniqueid'=>$fields->pid];
		
		$our_db_p_unique_id = $this->main_model->insert_or_id_get("tbl_cricket_players",['uniqueid'=>$fields->pid], array_merge($player_data,$commonInsert));
		
	       $match_players_data = ["match_unique_id"=>$fields->unique_id,"player_unique_id"=>$fields->pid,"team_id"=>$team_id,"credits"=>$fields->credits, "playing_role"=>$fields->playingRole ];
           
			$match_players_id = $this->main_model->insert_or_id_get("tbl_cricket_match_players", $match_players_data, array_merge($match_players_data,$commonInsert));
			
			if($match_players_id){
				echo "Player successfully added";
			}else{
				echo 0;
			}
		}

	}
	
	// Save image user profile
    function image_change_save() {
		$table = $this->table;

        $save_image         = $this->input->post("save_image");
        $apply_all          = $this->input->post("apply_all");
        $player_unique_id   = $this->input->post('player_unique_id');
        $match_players_id   = $this->input->post('match_players_id');
        $match_unique_id   = $this->input->post('match_unique_id');
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
        $user_name =  $this->input->post('player_unique_id');
        $match_players =  $this->input->post('match_players_id');
        $table = "tbl_cricket_players";
        $cond = "uniqueid ='" . $user_name . "'";
        $select_fields = "$table.id";
        $joins = array();
        $data = [];
        $data['html'] = '';
        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

                    /********************************************************/
             // If file upload form submitted
        if(!empty($_FILES['player_image']['name']) && !empty($user_detail['id'])){
            $player_id = $user_detail['id'];
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
                $_FILES['player_image']['error']     = $_FILES['player_image']['error'][$i];
                $_FILES['player_image']['size']     = $_FILES['player_image']['size'][$i];

                    //changing file name for selected
                    $ext = pathinfo($_FILES['player_image']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_player.$ext";
                    $file_upload = $this->upload_files($config, "player_image");
                    

                    if($file_upload['status']=="success"){
                        
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], PLAYER_IMAGE_LARGE_PATH, PLAYER_IMAGE_THUMB_PATH);
                        $data_image = $file_upload['data']['file_name'];
                        $cruid_insert = array(
                            'player_id' => $player_id,
                            'file_name' => $data_image,
                            'status' => 'A',
                            'created_by' => $this->session->userdata('adminId'),
                            'updated_by' => $this->session->userdata('adminId'),
                            'created_at' => time(),               
                            'updated_at' => time(),               
                        );
                        $table = "tbl_cricket_player_galleries";
                        $business_id = $this->main_model->cruid_insert($table, $cruid_insert);
                       
                        $data['html'] = '<div class="col-xs-4 col-sm-3 col-md-3 nopad text-center"><label class="image-radio"><img class="img-responsive" src="'.PLAYER_IMAGE_THUMB_URL.$data_image.'"><input type="radio" name="image_radio" value="'.$match_players.'_with_'.$data_image.'"><i class="fa fa-check hidden"></i></label></div>';
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
	//End of Matches class
}
