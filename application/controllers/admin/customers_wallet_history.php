<?php
require_once('base.php');

class Customers_wallet_history extends Base {

    private $limit = 10;
    private $table = 'tbl_customer_wallet_histories';
    private $image = '';
    private $prefixUrl = 'admin/customers_wallet_history/';
    private $name = 'Customers wallet history'; // For singular
    private $names = 'Customers wallet history'; //plural form 

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
        $this->load->model('customer_model');

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
	
	function set_id($id){

		// Create session array
		$sess_array = array(
			'customers_id' => $id
		);
		echo json_encode($sess_array);
		// Add user value in session
		$this->session->set_userdata('customers_id', $sess_array['customers_id']);
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
        //$condit = "{$this->table}.created_by = '".$this->session->userdata('adminId')."'";
        $condit = "";
        $customers_id = $set_data = $this->session->userdata('customers_id');
		if($customers_id != "")
		{
			$condit .="{$this->table}.customer_id = '".$customers_id."'";
		}
        $select_fields = " ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`name` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
				if ($val['name'] == 'status' and $val['value']) {
                    $condit .= "  AND `status` ='".$search."'";
                }
                if ($val['name'] == 'wallet_type' and $val['value']) {
                    $condit .= "  AND `type` ='".$search."'";
                }
				if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` >=". strtotime($search."00:00:00");
                }
				if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` <=". strtotime($search."23:59:59");
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
		$data['name'] 		= $this->name;
        $data['names'] 		= $this->names;
        
        $data['export'] = "no";
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'ajax_index', $data,true);
            $file = date("M_d_Y_h_i_s") . "_".$this->names.".xls";
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
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url("/admin/customers/index"));
        $this->breadcrumbs->push("{$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
               
        $data['prefixUrl']  = $this->prefixUrl;
        $data['table'] 		= $this->table;
		$data['name'] 		= $this->name;
        $data['names'] 		= $this->names;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index";
        $data['this_url'] = base_url() . $this->prefixUrl."index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }




    public function wallet_recharge()
    {
        $WALLET_TYPE=unserialize(WALLET_TYPE);
        $res = array();
        $recharge_amount        = $this->input->post("recharge_amount");
        $recharge_amount        = round(floatval($recharge_amount), 2);
        $recharge_description   = $this->input->post("recharge_description");
        $wallet_type   = $this->input->post("recharge_wallet_type");        
        $customer_id              = $this->input->post("recharge_customer_id");
        if(!empty($recharge_description) && !empty($wallet_type) && $recharge_amount>0 && $customer_id>0){
            $currentAdmin = $this->session->userdata("adminId");
            $basic_customer_info = $this->customer_model->getBasicCustomerInfo($customer_id);

            $user_token_info = getRecordOnId("tbl_customer_logins",['customer_id'=>$customer_id]);

            if(empty($basic_customer_info)){
                $res = array("status"=>"success", "message"=>"No such customer exists!");
            }
            else{
                $time = time();
                $table = "tbl_customer_wallet_histories";
                $final_amount = $basic_customer_info[$wallet_type]+$recharge_amount;
                $insert_data = array(
                                "customer_id"=>$customer_id,
                                "wallet_type"=>$WALLET_TYPE[$wallet_type],
                                "transaction_type"=>"CREDIT",
                                "transaction_id"=>"ADMIN-C-$time",
                                "type"=>"WALLET_RECHARGE_ADMIN",
                                "previous_amount"=>$basic_customer_info[$wallet_type],
                                "amount"=>$recharge_amount,
                                "current_amount"=>$final_amount,
                                "description"=>$recharge_description,
                                "status"=>"S",
                                "created_by"=>$currentAdmin,
                                "created"=>$time
                            );
                $wallet_history_id = $this->main_model->cruid_insert($table, $insert_data);
                if($wallet_history_id>0){
                    $this->main_model->cruid_update($this->customer_model->current_model_table, array($wallet_type=>$final_amount), array("id"=>$customer_id));

                    /**********************/
                    if( isset( $user_token_info->device_token ) && !empty( $user_token_info->device_token ) ){
                        $user_tokens= array( $user_token_info->device_token );
                        $device_type= $user_token_info->device_type;
                        $notiData =array('title'=> "Wallet Recharge ".CURRENCY_SYMBOL.$recharge_amount." successfully." , 'message'=>  $recharge_description, 'noti_type'=>'adminalert');

                        //$notification_data['noti_type']='customer_deposit';
                        //$alert_message = "Your deposit of ".CURRENCY_SYMBOL.$amount." successful.";
                        /*if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                            $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                            $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                        }*/                       
                        $this->send_notification($notiData, $user_tokens,$recharge_description, $notiData['noti_type'] ,$device_type);

                        // Notify data  inserte into the table
                        $table = "tbl_notifications";
                        $insert_data = ["users_id"=>$customer_id,"title"=>$notiData['title'],"notification"=>$notiData['message'],"sender_type"=>"ADMIN","created"=>time()];                        
                        $this->main_model->cruid_insert($table, $insert_data);

                    }
                    /**********************/

                    $res = array("status"=>"success", "message"=>"Wallet has been recharged successfully!");
                }
                else{
                    $res = array("status"=>"error", "message"=>"Something went wrong while recharging the wallet!");
                }
            }
        }
        else{
            $res = array("status"=>"error", "message"=>"Input data is not valid!");
        }
        echo json_encode($res);exit;
    }


    public function wallet_withdraw()
    {

        $WALLET_TYPE=unserialize(WALLET_TYPE);
        $res = array();
        $withdraw_amount        = $this->input->post("withdraw_amount");
        $withdraw_amount        = round(floatval($withdraw_amount), 2);
        $withdraw_description   = $this->input->post("withdraw_description");        
        $wallet_type   = $this->input->post("withdraw_wallet_type");        
        $customer_id              = $this->input->post("withdraw_customer_id");


        if(!empty($withdraw_description) && !empty($wallet_type) && $withdraw_amount>0 && $customer_id>0){
            $currentAdmin = $this->session->userdata("adminId");
             $basic_customer_info = $this->customer_model->getBasicCustomerInfo($customer_id);
             $user_token_info = getRecordOnId("tbl_customer_logins",['customer_id'=>$customer_id]);

            if(empty($basic_customer_info)){
                $res = array("status"=>"success", "message"=>"No such customer exists!");
            }else if($basic_customer_info[$wallet_type ]<$withdraw_amount){
                $res = array("status"=>"success", "message"=>"Withdraw amount should be less then or equal to ".$basic_customer_info[$wallet_type].".");
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

                    /**********************/
                    if( isset( $user_token_info->device_token ) && !empty( $user_token_info->device_token ) ){
                        $user_tokens= array( $user_token_info->device_token );
                        $device_type= $user_token_info->device_type;
                        $notiData =array('title'=> "Wallet Withdraw ".CURRENCY_SYMBOL.$withdraw_amount." successfully." , 'message'=>  $withdraw_description, 'noti_type'=>'adminalert');

                        //$notification_data['noti_type']='customer_deposit';
                        //$alert_message = "Your deposit of ".CURRENCY_SYMBOL.$amount." successful.";
                        /*if(isset($insert_data["image"]) && !empty($insert_data["image"])){

                            $notiData['noti_thumb']=NOTIFICATION_IMAGE_THUMB_URL.$insert_data["image"];
                            $notiData['noti_large']=NOTIFICATION_IMAGE_LARGE_URL.$insert_data["image"];
                        }*/                       
                        $this->send_notification($notiData, $user_tokens,$withdraw_description, $notiData['noti_type'] ,$device_type);
                    }
                    /**********************/

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
        echo json_encode($res);exit;
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


//End of Countries class
}
