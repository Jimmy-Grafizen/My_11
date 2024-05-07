<?php

require_once('base.php');
class Referal_commission extends Base {

    private $limit = 20;
    private $table = 'tbl_referal_commission';
    private $image = '';
    private $prefixUrl = 'admin/referal_commission/';
    private $name = 'Referal Commission'; // For singular
    private $names = 'Referal Commission'; //plural form 
    private $state = 'tbl_states';
    private $country = 'tbl_countries';
    private $tcwh = 'tbl_customer_wallet_histories';
    private $tg = 'tbl_games';

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
        $joins[1] = ['table'=>'tbl_games as tg', 'condition'=>'tbl_referal_commission.game_id = tg.id','jointype'=>'left'];
        //$joins[2] = ['table'=>'tbl_game_types', 'condition'=>'tbl_referal_commission.game_type_id = tbl_game_types.id','jointype'=>'left'];

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
        $select_fields = ",tg.name as game_name,
						(CASE tg.id
						  WHEN 0 THEN (SELECT name FROM tbl_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
						  WHEN 1 THEN (SELECT name FROM tbl_kabaddi_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
						  WHEN 2 THEN (SELECT name FROM tbl_soccer_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
						END) AS game_type_name ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "{$this->table}.`name` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'game_id' and $val['value'] >=0 and $val['value']!="") {
                    $condit .= "  AND `game_id` ='". $search."'";
                }
                if ($val['name'] == 'game_type_id' and $val['value']) {
                    $condit .= "  AND `game_type_id` =". $search;
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

    // add new user
    public function add() {
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

        $this->form_validation->set_rules('commission', 'Commission', "trim|required|numeric|callback_is_unique_commission");
        $this->form_validation->set_message('is_unique_commission', 'This %s is already taken in ' . SITE_TITLE . ". Please try different");
        $this->form_validation->set_rules('game_id', 'Sports', "trim|required");
        $this->form_validation->set_rules('game_type_id', 'Game type', "trim|required");

        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {

            $data = array(
                'commission'    => $this->input->post('commission'),
                'game_id'       => $this->input->post('game_id'),
                'game_type_id'  => $this->input->post('game_type_id'),
                'status'        => 'A',
                'created_by'    => $this->session->userdata('adminId'),
                'updated_by'    => $this->session->userdata('adminId'),
                'created_at'    => time(),               
                'updated_at'    => time(),               
            );
            $table      = $this->table;

            $last_id= $this->main_model->cruid_insert($table, $data);
            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    // edit user detail
    public function edit() {

        $id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "tbl_referal_commission.id ='" . $id . "'";
      
        $joins[1] = ['table'=>'tbl_games as tg', 'condition'=>'tbl_referal_commission.game_id = tg.id','jointype'=>'left'];
        $select_fields = "{$this->table}.*, tg.name as game_name,
                        (CASE tg.id
                          WHEN 0 THEN (SELECT name FROM tbl_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
                          WHEN 1 THEN (SELECT name FROM tbl_kabaddi_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
                          WHEN 2 THEN (SELECT name FROM tbl_soccer_game_types tcgt WHERE {$this->table}.game_type_id = tcgt.id) 
                        END) AS game_type_name ";

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update {$this->names} Details";

            $data['table'] = $this->table;
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            
            $this->form_validation->set_rules('commission', 'Commission', "trim|required|numeric|callback_is_unique_again_commission[$id.tbl_referal_commission]");
            $this->form_validation->set_message('is_unique_again_commission', 'This %s is already taken in ' . SITE_TITLE . ". Please try different");
            $this->form_validation->set_rules('game_id', 'Sports', "trim|required");
            $this->form_validation->set_rules('game_type_id', 'Game type', "trim|required");

            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
                $data = array(
                    'commission'    => $this->input->post('commission'),
                    'game_id'       => $this->input->post('game_id'),
                    'game_type_id'  => $this->input->post('game_type_id'),
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

    function game_type($Stateid=null, $game_type_id=0) {
        $opt = "<option value=''>Please Select Game Type</option>";
        $table = null;
		if($Stateid == "0"){
			$table = "tbl_game_types";
		}elseif ($Stateid == "1") {
			$table = "tbl_kabaddi_game_types";
		}elseif ($Stateid == "2") {
			$table = "tbl_soccer_game_types";
		}

		if($table){
	        $opt_all = $this->main_model->cruid_select_array($table, "id,name,", $joins = array(), $cond = array("is_deleted" => 'N'), "", array('field' => 'name', 'type' => 'asc'));
	        
	        if (!empty($opt_all)) {
	            foreach ($opt_all as $val) {
	                $selected = "";
	                if ($game_type_id == $val['id']) {
	                    $selected = "selected = 'selected'";
	                }
	                $opt.= '<option ' . $selected . ' value="' . $val['id'] . '">' . $val['name'] . '</option>';
	            }
	        }
	    }

        echo $opt;
    }

    public function is_unique_commission($str) {
        $game_id        = $this->input->post('game_id');
        $game_type_id   = $this->input->post('game_type_id');
        $is_deleted = [];
        $table = $this->table;
        if ($this->db->field_exists('is_deleted',$table)){
            $is_deleted = ['is_deleted' => 'N'];
        }
        if (isset($this->db)) { //"commission" => $str,
            $query = $this->db->limit(1)->get_where($table, array( 'game_id' => $game_id, 'game_type_id' => $game_type_id)+$is_deleted);
            return $query->num_rows() === 0;
        }

        return FALSE;
    }

    public function is_unique_again_commission($str, $field) {
        list($id) = explode('.', $field);

        $game_id        = $this->input->post('game_id');
        $game_type_id   = $this->input->post('game_type_id');
        $is_deleted     = [];
        $table          = $this->table;
        if ($this->db->field_exists('is_deleted',$table)){
            $is_deleted = ['is_deleted' => 'N'];
        }
        if (isset($this->db)) { 
            $query = $this->db->limit(1)->get_where($table, array( 'game_id' => $game_id, 'game_type_id' => $game_type_id, 'id <> ' => $id)+$is_deleted);
            return $query->num_rows() === 0;
        }

        return FALSE;
    }


    public function ajax_customers($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }

        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $this->table = "tbl_customers";
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_customers";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/customers/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tcwh} tcwh", 'condition'=>"tcwh.customer_id = {$this->table}.id AND tcwh.type = 'CUSTOMER_RECEIVED_REFCCB'",'jointype'=>'left'];
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
        
        $select_fields = ",country.name as countryName, state.name as stateName, (SELECT COUNT(id) FROM `tbl_customers` inTC WHERE inTC.used_referral_user_id ={$this->table}.id) AS used_referral_count, sum(tcwh.amount) total_earnings";
        
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
                        $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%' OR  `referral_code` LIKE '%" . $keyword . "%' OR  {$this->table}.id LIKE '%" . $keyword . "%'";
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
                    $condit .= "  AND {$this->table}.`created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$this->table}.`created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'user_type' and $val['value']) {
                    if( $search == 'is_fake' ){
                        $condit .= "  AND `$search` ='1' AND `is_admin` ='0'";
                    }elseif( $search == 'is_affiliate' ){
                        $condit .= "  AND `$search` ='1' ";
                    }else{
                        $condit .= "  AND `$search` ='1'";
                    }
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `created` >=". $this->session->userdata('from_date');
                $condit .= "  AND `created` <=". $this->session->userdata('to_date');
               
            }
        
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, "{$this->table}.id", $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."customers/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_customers";

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
            $var = $this->load->view($this->prefixUrl.'ajax_customers', $data,true);
            $file = date("M_d_Y") . "_".$this->names."_customers.xls";
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
            $this->load->view($this->prefixUrl.'ajax_customers', $data);
        }
    }

    public function customers($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'customers');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Customers List", site_url('section'));

        ob_start();
        $this->ajax_customers($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Customers List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_customers";
        $data['this_url'] = base_url() . $this->prefixUrl."customers";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'customers', $data);
        $this->template->render();
    }

    public function ajax_referrals($referral_user_id,$offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }

        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $this->table = "tbl_customers";
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_referrals/$referral_user_id";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/referrals/$referral_user_id/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 5;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->table}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->table}.state",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tcwh} tcwh", 'condition'=>"tcwh.customer_id = {$this->table}.id AND tcwh.type = 'CUSTOMER_RECEIVED_REFCCB'",'jointype'=>'left'];
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
        
        $condit = "{$this->table}.is_deleted = 'N' AND used_referral_user_id='$referral_user_id'";
        
        $select_fields = ",country.name as countryName, state.name as stateName, (SELECT COUNT(id) FROM `tbl_customers` inTC WHERE inTC.used_referral_user_id ={$this->table}.id) AS used_referral_count, sum(tcwh.amount) total_earnings";
        
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
                        $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%' OR  `referral_code` LIKE '%" . $keyword . "%' OR  {$this->table}.id LIKE '%" . $keyword . "%'";
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
                    $condit .= "  AND {$this->table}.`created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$this->table}.`created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'user_type' and $val['value']) {
                    if( $search == 'is_fake' ){
                        $condit .= "  AND `$search` ='1' AND `is_admin` ='0'";
                    }elseif( $search == 'is_affiliate' ){
                        $condit .= "  AND `$search` ='1' ";
                    }else{
                        $condit .= "  AND `$search` ='1'";
                    }
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `created` >=". $this->session->userdata('from_date');
                $condit .= "  AND `created` <=". $this->session->userdata('to_date');
               
            }
        
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(5), $joins, $order_by, $table, $select_fields, $condit, "{$this->table}.id", $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."referrals/$referral_user_id/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_referrals/$referral_user_id";

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
            $var = $this->load->view($this->prefixUrl.'ajax_customers', $data,true);
            $file = date("M_d_Y") . "_".$this->names."_referrals.xls";
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
            $this->load->view($this->prefixUrl.'ajax_customers', $data);
        }
    }

    public function referrals($referral_user_id, $offset = 0) { 
        $this->loginCheck($this->prefixUrl.'referrals');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Customers", site_url($this->prefixUrl."customers") );
        $this->breadcrumbs->push("Referrals List", site_url('section'));

        ob_start();
        $this->ajax_referrals($referral_user_id, $offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "referrals List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_referrals/$referral_user_id";
        $data['this_url'] = base_url() . $this->prefixUrl."referrals/$referral_user_id";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'customers', $data);
        $this->template->render();
    }

    public function ajax_reports($customer_id=0,$offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }

        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }

        $this->table = "tbl_customers";
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_reports/$customer_id";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/reports/$customer_id/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 5;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->table} tc", 'condition'=>"tc.id = {$this->tcwh}.refrence_id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = tc.state",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tg} tg", 'condition'=>"tg.id = {$this->tcwh}.sport_id",'jointype'=>'left'];
        $joins[4] = ['table'=>"tbl_cricket_matches tcm", 'condition'=>"{$this->tcwh}.match_unique_id = tcm.unique_id",'jointype'=>'left'];
        $joins[5] = ['table'=>"tbl_game_types tgt", 'condition'=>"tcm.game_type_id = tgt.id",'jointype'=>'left'];
        $joins[6] = ['table'=>"tbl_cricket_contest_matches tccm", 'condition'=>"tccm.id = {$this->tcwh}.match_contest_id",'jointype'=>'left'];
        $joins[7]=['table'=>"tbl_cricket_contest_categories tccc", 'condition'=>"tccc.id = tccm.category_id",'jointype'=>'left'];

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
        $table = $this->tcwh;
        
        $condit = "{$this->tcwh}.type = 'CUSTOMER_RECEIVED_REFCCB' AND customer_id = '$customer_id'";
        
        $select_fields = " ,tc.id,tc.firstname,tc.lastname,tc.email,tg.name as sports_type, tgt.name as game_type, state.name as stateName,tccc.name as contest_category,tccm.entry_fees ,tcm.name as match_name ,tcm.match_date as match_date";
        
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
                        $str[] = "tcm.name LIKE '%" . $keyword . "%' OR `team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%' OR  `state`.`name` LIKE '%" . $keyword . "%' OR  `tccc`.`name` LIKE '%" . $keyword . "%' OR  `tg`.`name` LIKE '%" . $keyword . "%' OR  `tgt`.`name` LIKE '%" . $keyword . "%'";
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
                    $condit .= "  AND `tcm`.`match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tcm`.`match_date` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'user_type' and $val['value']) {
                    if( $search == 'is_fake' ){
                        $condit .= "  AND `$search` ='1' AND `is_admin` ='0'";
                    }elseif( $search == 'is_affiliate' ){
                        $condit .= "  AND `$search` ='1' ";
                    }else{
                        $condit .= "  AND `$search` ='1'";
                    }
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `created` >=". $this->session->userdata('from_date');
                $condit .= "  AND `created` <=". $this->session->userdata('to_date');
               
            }
        
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(5), $joins, $order_by, $table, $select_fields, $condit, "", $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."reports/$customer_id/" . ($offset ? $offset : "");
        $data['base_url']    = base_url() . $this->prefixUrl."ajax_reports/$customer_id";

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
            $var = $this->load->view($this->prefixUrl.'ajax_reports', $data,true);
            $file = date("M_d_Y") . "_reports_".$this->names.".xls";
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
            $this->load->view($this->prefixUrl.'ajax_reports', $data);
        }
    }

    public function reports($customer_id, $offset = 0) { 
        $this->loginCheck($this->prefixUrl.'reports');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Customers", site_url($this->prefixUrl."customers"));
        $this->breadcrumbs->push("Reports List", site_url('section'));

        ob_start();
        $this->ajax_reports($customer_id, $offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Reports List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_reports/$customer_id";
        $data['this_url'] = base_url() . $this->prefixUrl."reports/$customer_id";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'reports', $data);
        $this->template->render();
    }

//End of Countries class
}