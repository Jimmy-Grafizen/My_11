<?php

require_once('base.php');
class Commission extends Base {

    private $limit      = 11;
    private $table      = 'tbl_commission';
    private $image      = '';
    private $prefixUrl  = 'admin/commission/';
    private $name       = 'Commission'; // For singular
    private $names      = 'Commission'; // For plural  

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
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
					if($keyword !=null){
						$str[] = "{$this->table}.`name` LIKE '%" . $keyword . "%'";
					}
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
          
                if ($val['name'] == 'game_id' and $val['value']) {
                    $condit .= "  AND `game_id` =" . $search;
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

 
    // add new user
    public function index() {
        $this->loginCheck($this->prefixUrl.'index');
        $this->checkUser();
	    $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
                 $table = $this->table;
    
        if($_SERVER['REQUEST_METHOD']=="POST")
        {
            foreach($_POST['c_commssion'] as $key=>$label)
            {
        $cond = "c_id ='" . ($key+1) . "'";
            $data = array(
					'c_commssion' =>$label
  				);

                $this->main_model->cruid_update($table, $data, $cond);
            }
                $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                    $url = $this->input->get("return");
    redirect(base_url()."admin/commission/index");
        }
        $data['title'] = " {$this->names}";
           $joins   = array();
            $order_by   = array();
            
             $table = $this->table;
                $order_by_other = array();
    $condit = "";
        $select_fields = " ";
  $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, "", $order_by_other);
           $data['result'] = $rows;
         $this->template->write_view('contents', $this->prefixUrl.'index', $data);
            $this->template->render();
       
    }

  
  

//End of  class
}