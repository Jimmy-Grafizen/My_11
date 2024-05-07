<?php

require_once('base.php');
class Groups extends Base {

    private $limit = 10;
    private $image = '';
    private $groups = '';

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

        $this->groups = parent::start_require();
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
        $config['base_url'] = base_url() . "admin/groups/ajax_index";
        $config['base_parent_url'] = base_url() . "admin/groups/index/";
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
        $table = "tbl_groups";
        $condit = "tbl_groups.user_id = '".$this->session->userdata('adminId')."'";
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
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (`created` LIKE '%" . $search . "%')";
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

        $data['current_url'] = base_url() . "admin/groups/index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . "admin/groups/ajax_index";

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
        $this->load->view('admin/groups/ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck('admin/groups/index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Groups", site_url('/admin/groups'), false);
        $this->breadcrumbs->push("Groups List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Groups List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . "admin/groups/ajax_index";
        $data['this_url'] = base_url() . "admin/groups/index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', 'admin/groups/index', $data);
        $this->template->render();
    }


// generate random password here
    function generate_password($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

// add new user
    public function add() {
        $this->loginCheck('admin/groups/add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Groups", site_url('/admin/groups'));
        $this->breadcrumbs->push('Add Group', site_url('section'));

        $redirect = '/admin/groups/index';
        $data['title'] = "Add New Group";

        $this->form_validation->set_rules('name', 'Name', 'trim|required|is_unique[tbl_groups.name]');
        $this->form_validation->set_message('is_unique', 'Group already registered in ' . SITE_TITLE);

        if ($this->form_validation->run() == FALSE) {
            $data['groups'] = $this->groups;
            $this->template->write_view('contents', 'admin/groups/add', $data);
            $this->template->render();
        } else {

            $data = array(
                'user_id' => $this->session->userdata('adminId'),
                'name' => $this->input->post('name'),
                'permissions' => $this->input->post('permissions'),
                'created' => date('Y-m-d H:i:s'),
            );
            $table = "tbl_groups";
            $business_id = $this->main_model->cruid_insert($table, $data);
            
            $this->session->set_userdata('smessage', 'Group Successfully added');
            redirect($redirect);
        }
    }

// edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck('admin/groups/edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Groups", site_url('/admin/groups'));
        $this->breadcrumbs->push('Edit Group', site_url('section'));

        $url = $this->input->get("return");
        $table = "tbl_groups";
        $cond = "id ='" . $user_name . "'";
        $select_fields = "tbl_groups.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Groups Details";
            $this->form_validation->set_rules('name', 'Name', "trim|required|is_unique_again[tbl_groups.name.$id]");
            $this->form_validation->set_message('is_unique_again', 'Group is already registered in ' . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $data['groups'] = $this->groups;
                $this->template->write_view('contents', 'admin/groups/edit', $data);
                $this->template->render();
            } else {
                $data = array(
                    'name' => $this->input->post('name'),
                    'permissions' => $this->input->post('permissions'),
                );

                $this->main_model->cruid_update($table, $data, $cond);
                $this->session->set_userdata('smessage', 'Group Details Successfully updated');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', 'Sorry, this Group not available');
            redirect($url);
        }
    }

// actions fro Groups
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
        $this->loginCheck('admin/groups/deleteuser/' . $id);
        $this->checkUser();

        $this->main_model->cruid_delete('tbl_groups', array('id' => $id));
        $this->session->set_userdata('smessage', 'Groups Successfully deleted');
    }

// activate all Groups
    public function activateall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Group');
        } else {
            for ($i = 0; $i < count($checked); $i++) {

                $table = "tbl_groups";
                $cond = "id ='" . $checked[$i] . "'";
                $select_fields = "tbl_groups.*";
                $joins = array();
                $uset_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
                $table = "tbl_groups";
                $cond = array(
                    'id' => $checked[$i],
                );
                $data = array(
                    'status' => '1',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', 'Selected Groups successfully activated');
    }

//  deactivate all Groups
    public function deactivateall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Group');
        } else {
            for ($i = 0; $i < count($checked); $i++) {
                $table = "tbl_groups";
                $cond = array(
                    'id' => $checked[$i],
                );
                $data = array(
                    'status' => '0',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', 'Selected Groups successfully deactivated');
    }

// activate user profile
    function activate($id) {
        $url = $this->input->get("return");
        $table = "tbl_groups";
        $cond = array(
            'id' => $id,
        );
        $data = array(
            'status' => '1',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', 'Selected Group successfully activated');
    }

// deactivate user profile
    function deactivate($id) {
        $url = $this->input->get("return");
        $table = "tbl_groups";
        $cond = array(
            'id' => $id,
        );
        $data = array(
            'status' => '0',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', 'Selected Group successfully deactivated');
    }

// delete all Groups
    public function deleteall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Group');
        } else {
            for ($i = 0; $i < count($checked); $i++) {
                $this->main_model->cruid_delete('tbl_groups', array('id' => $checked[$i]));
            }
        }
        $this->session->set_userdata('smessage', 'Selected Groups successfully deleted');
    }
}
/* End of file groups.php */
/* Location: ./application/controllers/admin/groups.php */