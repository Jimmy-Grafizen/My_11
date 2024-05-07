<?php

class Content_management extends CI_Controller {

    private $limit = 10;
    private $image = '';

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
        $config['base_url'] = base_url() . "admin/content_management/ajax_index";
        $config['base_parent_url'] = base_url() . "admin/content_management/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $order_by_other = array();
        $order_by = array(
            'field' => 'id',
            'type' => 'asc',
        );
		//        if ($this->input->get('field')) {
		//            $order_by = array();
		//            $order_by_other = array(
		//                'field' => $this->input->get('field'),
		//                'type' => $this->input->get('sort'),
		//            );
		//        } else {
		//            $order_by_other = array();
		//            $order_by = array(
		//                'field' => 'id',
		//                'type' => 'desc',
		//            );
		//        }
        $table = "tbl_page_contents";
        $condit = "tbl_page_contents.id > 0";
        $select_fields = " ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`name` LIKE '%" . $keyword . "%'";
                }


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'page_name' and $val['value']) {
                    $condit .= "  AND page_name = '" . $search . "'";
                }
                if ($val['name'] == 'app_type' and $val['value']) {
                    $condit .= "  AND app_type = '" . $search . "'";
                }
                if ($val['name'] == 'platform' and $val['value']) {
                    $condit .= "  AND platform = '" . $search . "'";
                }
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (from_unixtime(created, '%Y-%m-%d') LIKE '%" . $search . "%')";
                }
            }
        }
/*
        $company_data = $this->session->userdata('loginUser');

        $condit .= "  AND company_id =" . $company_data['info_id'] . "";*/
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

        $data['current_url'] = base_url() . "admin/content_management/index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . "admin/content_management/ajax_index";

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
        $this->load->view('admin/content_management/ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck('admin/content_management/index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Content Management", site_url('/admin/content_management'), false);
        $this->breadcrumbs->push("Pages list", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Pages List";
        //print_r($initial_content); die;
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . "admin/content_management/ajax_index";
        $data['this_url'] = base_url() . "admin/content_management/index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', 'admin/content_management/index', $data);
        $this->template->render();
    }

// create slug for security purpose
    public function createSlug($string) {
        $old_pattern = array("/[^a-zA-Z0-9]/", "/_+/", "/_$/");
        $new_pattern = array("-", "-", "");
        return strtolower(preg_replace($old_pattern, $new_pattern, $string)) . rand(9999, 9999999) . time();
    }

// generate random password here
    function generate_password($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }


// edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck('admin/content_management/edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Content Management", site_url('/admin/content_management'));
        $this->breadcrumbs->push('Edit Page Content', site_url('section'));

        $url = $this->input->get("return");
        $table = "tbl_page_contents";
        $cond = "id ='" . $user_name . "'";
        $select_fields = "tbl_page_contents.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Page Content";
            $this->form_validation->set_rules('title', 'Title', "trim|required"/* |is_unique_again[tbl_categories.name.$id]" */);
            $this->form_validation->set_rules('content', 'Page Content', "trim|required"/* |is_unique_again[tbl_categories.name.$id]" */);
            /*$this->form_validation->set_message('is_unique_again', 'The Page is already registered in ' . SITE_TITLE . ". Please try different");*/
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', 'admin/content_management/edit', $data);
                $this->template->render();
            } else {
                if(!empty($_FILES['image']['name'])){
                    $filename =time().str_replace(' ','',$_FILES['image']['name']);
                    $filename  =str_replace('&','',$filename);
                    $acctualfilepath = CONTEXTCATEGORY_IMAGE_THUMB_PATH.$filename;
                    move_uploaded_file($_FILES['image']['tmp_name'], $acctualfilepath);
                    $insert_data['image'] = $filename;
                }
                $data = array(
                    'updatedby' => $this->session->userdata('adminId'),
                    'meta_title' => $this->input->post('meta_title'),
                    'meta_keywords' => $this->input->post('meta_keywords'),
                    'meta_description' => $this->input->post('meta_description'),
                    'title' => $this->input->post('title'),
                    'page_url' => $this->input->post('page_url'),
                    'content' => $this->input->post('content'),
                    'image' => $insert_data['image'],
                    'updated' => time()
                );
                
                
                
                $this->main_model->cruid_update($table, $data, $cond);

                $this->session->set_userdata('smessage', 'Page Content updated successfully');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', 'Sorry, this record not available');
            redirect($url);
        }
    }

    public function get_page_contents($page_contentsName){
        $page_contentsName = explode("__", $page_contentsName);
        $page_contentsName = '"'.implode('","', $page_contentsName).'"';
       // print_r(array(array(3, 6), 'live', 'Rick'));die;
        $sql = "SELECT * FROM tbl_page_contents WHERE page_name IN ($page_contentsName) AND platform = 'M'";
        $query =  $this->db->query($sql);
        $results = $query->result();
        //$this->db->last_query();
        echo json_encode($results);

    }

/* End of Class */
}