<?php

require_once('base.php');
class cash_bonus_recharge extends Base {

    private $limit = 20;
    private $table = 'tbl_recharge_cach_bonus';
    private $image = '';
    private $prefixUrl = 'admin/cash_bonus_recharge/';
    private $name = 'Recharge'; // For singular
    private $names = 'Recharges'; //plural form 

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
		$joins[1] = ['table'=>"tbl_customer_wallet_histories tcwh", 'condition'=>"{$this->table}.id=tcwh.rcb_id",'jointype'=>'left'];

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
		$group_by = "{$this->table}.id";

        $table = $this->table;
        $condit = "{$this->table}.is_deleted = 'N'";//{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND 
        //$select_fields = " ,COUNT(tcwh.rcb_id) AS total_used";
        $select_fields = " ,(SELECT count(*) FROM tbl_customer_wallet_histories as tcwh2 where tcwh2.wallet_type='Bonus Wallet' AND tcwh2.transaction_type='CREDIT' AND tcwh2.type='CUSTOMER_WALLET_RECHARGE' AND tbl_recharge_cach_bonus.code=tcwh2.description) AS total_used";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
					if($keyword !=null){
						$str[] = "{$this->table}.`recharge` LIKE '%" . $keyword . "%' OR {$this->table}.`cach_bonus` LIKE '%" . $keyword . "%'";
					}
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
          
                if ($val['name'] == 'is_use' and $val['value']) {
                    $condit .= "  AND `is_use` ='" . $search."'";
                }
            }
        }
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);
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
        $data['name'] = $this->name;
        $data['names'] = $this->names;
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
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $this->table;

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
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $this->table;

			$this->form_validation->set_rules('code', 'Code', "trim|required|is_unique[{$this->table}.code]",array(
                'required'      => 'You have not provided %s.',
                'is_unique'     => 'This %s already exists.'
                ));
            $max_recharge =  $this->input->post('max_recharge');
            
            if($this->input->post('rc_type') == 0){
                $this->form_validation->set_rules('max_recharge', 'Max Recharge', "trim|required");
                $this->form_validation->set_rules('cash_bonus_type', 'Cash bonus Type', "trim|required");
                $this->form_validation->set_rules('recharge', 'Min Recharge', "trim|required|numeric|less_than_equal_to[$max_recharge]");
                if($this->input->post('cash_bonus_type') == "P"){
			        $this->form_validation->set_rules('cach_bonus', 'Cash bonus ', "trim|required|numeric|less_than_equal_to[100]");
                }else{
                    $this->form_validation->set_rules('cach_bonus', 'Cash bonus ', "trim|required");
                }
            }
            
            $this->form_validation->set_rules('start_date', 'Start Date ', "trim|required");
            $this->form_validation->set_rules('end_date', 'End Date ', "trim|required|callback_compareDate");
	        
            
			
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {
              $start_date = strtotime( str_ireplace("/", "-", $this->input->post('start_date') ) );
              $end_date = strtotime( str_ireplace("/", "-", $this->input->post('end_date') ) );
                $data = array(
                'code' => $this->input->post('code'),
                'recharge' => ($this->input->post('recharge')>0)?$this->input->post('recharge'):0,
                'max_recharge' => ($this->input->post('max_recharge'))?$this->input->post('max_recharge'):0,
                'cach_bonus' => ($this->input->post('cach_bonus'))?$this->input->post('cach_bonus'):0,
                'cash_bonus_type' => $this->input->post('cash_bonus_type'),
                'is_use' => $this->input->post('is_use'),                
                'status' => 'A',
				'created_by' => $this->session->userdata('adminId'),
				'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
                'start_date' => $start_date,               
                'end_date'   => $end_date,
				'rc_type' =>$this->input->post('rc_type'),
				'amount' =>$this->input->post('amount'),
                'amt_type'=>$this->input->post('amt_type')
            );
            $data['is_use_max'] = 1;
            if($this->input->post('is_use') == "M"){
                $data['is_use_max'] = $this->input->post('is_use_max');
            }
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $data);

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    public function compareDate() {
        $start_date = strtotime( str_ireplace("/", "-", $this->input->post('start_date') ) );
        $end_date = strtotime( str_ireplace("/", "-", $this->input->post('end_date') ) );

          if ($end_date >= $start_date)
            return True;
          else {
            $this->form_validation->set_message('compareDate', '%s should be greater than Start Date.');
            return False;
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

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
			$data['title'] = "Update {this->names} Details";
            $data['prefixUrl'] = $this->prefixUrl;
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;


            $this->form_validation->set_rules('code', 'Code', "trim|required||is_unique_again[{$this->table}.code.$id]",array(
                'required'      => 'You have not provided %s.',
                'is_unique_again'=> 'This %s already exists.'
                ));
            if($this->input->post('rc_type') == 0){
                $this->form_validation->set_rules('max_recharge', 'Max Recharge', "trim|required");
                $this->form_validation->set_rules('cash_bonus_type', 'Cash bonus Type', "trim|required");
                $this->form_validation->set_rules('recharge', 'Min Recharge', "trim|required|numeric|less_than_equal_to[$max_recharge]");
    
                if($this->input->post('cash_bonus_type') == "P"){
                     $this->form_validation->set_rules('cach_bonus', 'Cash bonus ', "trim|required|numeric|less_than_equal_to[100]");
                }else{
                    $this->form_validation->set_rules('cach_bonus', 'Cash bonus ', "trim|required");
                }
            }
            $max_recharge =  $this->input->post('max_recharge');
            
            $this->form_validation->set_rules('start_date', 'Start Date ', "trim|required");
            $this->form_validation->set_rules('end_date', 'End Date ', "trim|required|callback_compareDate");
            
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
                $start_date = strtotime( str_ireplace("/", "-", $this->input->post('start_date') ) );
                $end_date = strtotime( str_ireplace("/", "-", $this->input->post('end_date') ) );
				$data = array(    
                    'code' => $this->input->post('code'),
                    'recharge' => $this->input->post('recharge'),
                    'max_recharge' => $this->input->post('max_recharge'),
                    'cach_bonus' => $this->input->post('cach_bonus'),
                    'cash_bonus_type' => $this->input->post('cash_bonus_type'),
                    'start_date' => $start_date,               
                    'end_date'   => $end_date,
                    'is_use' => $this->input->post('is_use'),
					'updated_by' => $this->session->userdata('adminId'),
					'updated_at' => time(),
					'rc_type' =>$this->input->post('rc_type'),
					'amount' =>$this->input->post('amount'),
                    'amt_type'=>$this->input->post('amt_type')
				);

                $data['is_use_max'] = 1;
                if($this->input->post('is_use') == "M"){
                    $data['is_use_max'] = $this->input->post('is_use_max');
                }
                
                
                
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
    function getstates($Stateid=null) {
        $opt_all = $this->main_model->cruid_select_array("tbl_states", "tbl_states.id,name,country_id", $joins = array(), $cond = array("is_deleted" => 'N',"country_id" => $Stateid), "", array('field' => 'name', 'type' => 'asc'));//"status" => 'A',
        $opt = array();
        $opt = "<option value=''>Please Select State</option>";
        if (!empty($opt_all)) {
            foreach ($opt_all as $val) {
                $selected = "";
                if ($Stateid == $val['country_id']) {
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


    function ajax_index_customers($rcb_id ,$offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $table = "tbl_customer_wallet_histories";
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_index_customers/$rcb_id/";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/customers/$rcb_id/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 5;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins    = array();
        $joins[1] = ['table'=>"tbl_recharge_cach_bonus trcb", 'condition'=>"{$table}.rcb_id=trcb.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"tbl_customers tc", 'condition'=>"{$table}.customer_id=tc.id",'jointype'=>'left'];
        $joins[3] = ['table'=>"$table tcwh", 'condition'=>"{$table}.ref_cwh_id=tcwh.id",'jointype'=>'left'];

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
        $group_by = "";
        
        $condit = "{$table}.rcb_id = '".$rcb_id."'" ;
        $select_fields = " ,trcb.code,tc.firstname,tc.lastname,tc.email,tc.country_mobile_code,tc.phone,tcwh.amount AS Recharges_Amount";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if($keyword !=null){
                        $str[] = "{$this->table}.`recharge` LIKE '%" . $keyword . "%' OR {$this->table}.`cach_bonus` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'status' and $val['value']) {
                    $condit .= "  AND {$table}.`status` ='".$search."'";
                }
                if ($val['name'] == 'wallet_type' and $val['value']) {
                    $condit .= "  AND {$table}.`type` ='".$search."'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$table}.`created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND {$table}.`created` <=". strtotime($search."23:59:59");
                }
            }
        }
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(5), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);
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
        $data['export'] = "no";
        // echo "<pre>";print_r($data['records']);die;
        $config['per_page'] = $limit;
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."customers/$rcb_id/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_index_customers/$rcb_id";

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
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $table;
        
        $this->load->view($this->prefixUrl.'ajax_index_customers', $data);
    }

    public function customers($rcb_id, $offset = 0) {
        $this->loginCheck($this->prefixUrl.'customers');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("{$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_index_customers($rcb_id, $offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index_customers/$rcb_id";
        $data['this_url'] = base_url() . $this->prefixUrl."customers/$rcb_id";
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = "tbl_customer_wallet_histories";

        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'customers', $data);
        $this->template->render();
    }

//End of  class
}