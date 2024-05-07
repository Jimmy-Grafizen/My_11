<?php

require_once('base.php');
class Contests extends Base {

    private $limit = 10;
    private $table = 'tbl_cricket_contests';
    private $tccc =  'tbl_cricket_contest_categories';
    private $image = '';
    private $prefixUrl = 'admin/contests/';
    private $name = 'Contest'; // For singular
    private $names = 'Contests'; //plural form 

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
        $joins   = array();
		$joins[1] =	['table'=>"{$this->tccc} tccc", 'condition'=>"tccc.id = {$this->table}.category_id",'jointype'=>'left'];
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
        $condit = "{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND {$this->table}.is_deleted='N' AND {$this->table}.is_beat_the_expert ='N'";
        $select_fields = ",tccc.name as category_name ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
					if($keyword !=null){
						$str[] = "{$this->table}.`total_price` LIKE '%" . $keyword . "%' OR {$this->table}.`total_team` LIKE '%" . $keyword . "%' OR {$this->table}.`total_team` LIKE '%" . $keyword . "%'";
					}
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
          
                if ($val['name'] == 'category_id' and $val['value']) {
                    $condit .= "  AND `category_id` =" . $search;
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
		// echo "<pre>";print_r($data['records']);die;
        $config['per_page'] = $limit;
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_index";
	    $data['table'] = $this->table;
	    $data['tccc'] = $this->tccc;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
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
	    $data['table'] = $this->table;
		$data['tccc'] = $this->tccc;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
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
	    $data['table'] = $this->table;
		$data['tccc'] = $this->tccc;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->names}";

			$this->form_validation->set_rules('category_id', 'Category', "trim|required");
			$this->form_validation->set_rules('total_team', 'total team', "trim|required");
            $this->form_validation->set_message('is_unique', 'This category type is already taken in ' . SITE_TITLE . ". Please try different");
			
			
			// $this->form_validation->set_rules('category_id', 'Country', "trim|required");
            //$this->form_validation->set_message('is_unique', 'This State is already taken in ' . SITE_TITLE . ". Please try different");
			
			
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
			$contest_json = [];
			$contest_json['per_min_p']=	$this->input->post('per_min_p')['I'];
			$contest_json['per_max_p']=	$this->input->post('per_max_p')['I'];
			$contest_json['per_price']=	$this->input->post('per_price')['I'];
            $data = array(
                'category_id' => $this->input->post('category_id'),
                'total_team' => $this->input->post('total_team'),
                'total_price' => $this->input->post('total_price'),
                'entry_fees' => $this->input->post('entry_fees'),
                'more_entry_fees' => $this->input->post('more_entry_fees'),
                'actual_entry_fees' => $this->input->post('actual_entry_fees'),
                'first_prize' => $contest_json['per_price'][0],
                'per_user_team_allowed' => $this->input->post('per_user_team_allowed'),
                'contest_json' => json_encode($contest_json),
                'confirm_win_contest_percentage' => $this->input->post('confirm_win_contest_percentage'),
                'is_duplicate_allow' => ($this->input->post('is_duplicate_allow') =='Y')?'Y':'N',
                'is_compression_allow' => ($this->input->post('is_compression_allow') =='Y')?'Y':'N',
                'duplicate_count' => ($this->input->post('duplicate_count') !='')?$this->input->post('duplicate_count'):'0',
                'confirm_win' => ($this->input->post('confirm_win') =='Y')?'Y':'N',
                'multi_team_allowed' => ($this->input->post('per_user_team_allowed') >1 && $this->input->post('multi_team_allowed') =='Y')?'Y':'N',
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),  
                'cash_bonus_used_type' => $this->input->post('cash_bonus_used_type'),
                'cash_bonus_used_value' => $this->input->post('cash_bonus_used_value')
            );
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $data);

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

// edit user detail
    public function edit() {
	    $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
		$data['tccc'] = $this->tccc;
        $data['prefixUrl'] = $this->prefixUrl;

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

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
			$data['title'] = "Update {this->names} Details";

            $this->form_validation->set_rules('category_id', 'Category type', "trim|required");
            //$this->form_validation->set_message('is_unique_again', 'This Category types is already taken in ' . SITE_TITLE . ". Please try different");
			
			$this->form_validation->set_rules('total_team', 'total team', "trim|required");
			
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
				
				$contest_json = [];
				$contest_json['per_min_p']=	$this->input->post('per_min_p')['I'];
				$contest_json['per_max_p']=	$this->input->post('per_max_p')['I'];
				$contest_json['per_price']=	$this->input->post('per_price')['I'];
				$dataUpdate = array(
						'category_id' => $this->input->post('category_id'),
						'total_team' => $this->input->post('total_team'),
						'total_price' => $this->input->post('total_price'),
						'entry_fees' => $this->input->post('entry_fees'),
                        'more_entry_fees' => $this->input->post('more_entry_fees'),
                        'first_prize' => $contest_json['per_price'][0],
                        'actual_entry_fees' => $this->input->post('actual_entry_fees'),
						'per_user_team_allowed' => $this->input->post('per_user_team_allowed'),
						'contest_json' => json_encode($contest_json),
						'updated_by' => $this->session->userdata('adminId'),
						'updated_at' => time(),                        
                        'confirm_win_contest_percentage' => $this->input->post('confirm_win_contest_percentage'),
                        'is_duplicate_allow' => ($this->input->post('is_duplicate_allow') =='Y')?'Y':'N',
                        'is_compression_allow' => ($this->input->post('is_compression_allow') =='Y')?'Y':'N',
                        'duplicate_count' => ($this->input->post('duplicate_count') !='')?$this->input->post('duplicate_count'):'0',
                        'confirm_win' => ($this->input->post('confirm_win') =='Y')?'Y':'N',
                        'multi_team_allowed' => ($this->input->post('per_user_team_allowed') >1 && $this->input->post('multi_team_allowed') =='Y')?'Y':'N',
                        'cash_bonus_used_type' => $this->input->post('cash_bonus_used_type'),
                        'cash_bonus_used_value' => $this->input->post('cash_bonus_used_value')
				);

                $this->main_model->cruid_update($table, $dataUpdate, $cond);
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
    function getgames($Stateid=null) {
        $opt_all = $this->main_model->cruid_select_array($this->tccc ." tccc", "tccc.id,name,category_id", $joins = array(), $cond = array("is_deleted" => 'N',"category_id" => $Stateid), "", array('field' => 'name', 'type' => 'asc'));//"status" => 'A',
        $opt = array();
        $opt = "<option value=''>Please Select category</option>";
        if (!empty($opt_all)) {
            foreach ($opt_all as $val) {
                $selected = "";
                if ($Stateid == $val['category_id']) {
                    //$selected = "selected = 'selected'";
                }
                $opt.= '<option ' . $selected . ' value="' . $val['id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $opt;
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
//End of  class
}