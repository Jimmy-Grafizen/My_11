<?php
require_once('base.php');
class Email_notifications extends Base {

    private $limit = 10;
    private $table = 'tbl_email_notifications';
    private $tbl_customers = 'tbl_customers';
    private $image = '';
    private $prefixUrl ='admin/email_notifications/';
    private $name 	   ='Email Notification'; // For singular
    private $names 	   ='Email Notifications'; //plural form 

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
        $condit = "{$this->table}.is_deleted ='N'";
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
                    $this->limit        = $val['value'];
                    $config['per_page'] = $this->limit;
                    $limit              = $this->limit;
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

        $config['per_page']  =  $limit;
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

    // add new Email
    public function add() {

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
          	$users_ids = $postedData['users'];
			$insert_data = array(
				'users_id'=>$users_ids,
				'title'=>$postedData['title'],
				'notification'=>$postedData['body'],
				'created'=>time(),
				'sender_id'=>$this->session->userdata('adminId'),
				'sender_ip'=>$_SERVER['REMOTE_ADDR']
			);
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);

            $select_user_field = 'tbl_customers.email,tbl_customers.firstname';
            $user_records = $this->main_model->cruid_select_array_limit('tbl_customers', $select_user_field,'', "tbl_customers.id IN (".$users_ids.")", "", "", "", ""); 


             foreach ($user_records as $dt) {
                        $data=array();
                        $data['message']=$postedData['body']; 
                        $data['subject']=$postedData['title']; 
                        $this->sendTemplatesInMail("admin_email",$dt['firstname'], $dt['email'],$data);
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