<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once('base.php');
class Enquiry extends Base {

    private $limit = 10;
    private $image = '';
    private $table = 'tbl_register_restaurant';
    private $customer_quries = 'tbl_customer_quries';
    private $tbl_register_delivery_boy = 'tbl_register_delivery_boy';
    private $tbl_contact_us = 'tbl_contact_us';
    private $prefixUrl = 'admin/enquiry/';
    private $name = 'Enquiry'; // For singular
    private $names = 'Enquiries'; //plural form 
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

    function loginCheck($str,$haveid=null) {
        if (!$this->session->userdata('adminId')) {
            $this->session->set_userdata('returnURL', $str.$haveid);
            redirect('admin');
        }
        /*if(!CheckPermission('/'.$str,"Enquiry Management")){
            $this->session->set_userdata('message', 'Sorry, You do not have access to this page permissions!');
            redirect('admin');
        }*/
    }

    function checkUser() {
        return true;
    }

    public function ajax_restaurant($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."ajax_restaurant";
        $data['table'] = $this->table;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $config['base_parent_url'] = base_url() . $this->prefixUrl."restaurant/";
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
        $condit = "$table.id > 0 AND $table.is_deleted = 'N'";
        $select_fields = " , $table.phone";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }

                if ($val['name'] == 'from_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->table}.created, '".FROM_UNIXTIME_SQL_FORMAT."') >=  '" . $search . "')";
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->table}.created, '".FROM_UNIXTIME_SQL_FORMAT."') <= '" . $search . "')";
                }
                
                if ($val['name'] == 'showing' and $val['value']) {
                    $this->limit = $val['value'];
                    $config['per_page'] = $this->limit;
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

        
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."restaurant/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_restaurant";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['act'] = "t_r_r";
        $data['sort_type'] = $order;
        $data['index_rpls'] = "restaurant";
        $data['ajax_index_rpls'] = "ajax_restaurant";

        $data['field'] = $this->input->get('field');
        $this->load->view($this->prefixUrl.'ajax_restaurant', $data);
    }

    public function restaurant($offset = 0) {
        $this->loginCheck($this->prefixUrl.'restaurant');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Restaurant", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Restaurant List", site_url('section'));

        ob_start();
        $this->ajax_restaurant($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Restaurant List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl. "ajax_restaurant";
        $data['this_url'] = base_url() . $this->prefixUrl."restaurant";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'restaurant', $data);
        $this->template->render();
    }

    public function ajax_deliverymen($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."ajax_deliverymen";
        $data['table'] = $this->tbl_register_delivery_boy;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $config['base_parent_url'] = base_url() . $this->prefixUrl."deliverymen/";
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
        $table = $this->tbl_register_delivery_boy;
        $condit = "$table.id > 0 AND $table.is_deleted = 'N'";
        $select_fields = " , $table.phone";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }

                if ($val['name'] == 'from_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->tbl_register_delivery_boy}.created, '".FROM_UNIXTIME_SQL_FORMAT."') >=  '" . $search . "')";
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->tbl_register_delivery_boy}.created, '".FROM_UNIXTIME_SQL_FORMAT."') <= '" . $search . "')";
                }
                
                if ($val['name'] == 'showing' and $val['value']) {
                    $this->limit = $val['value'];
                    $config['per_page'] = $this->limit;
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

        
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."deliverymen/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_deliverymen";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type'] = $order;
        $data['act'] = "t_r_d_b";
        $data['index_rpls'] = "deliverymen";
        $data['ajax_index_rpls'] = "ajax_deliverymen";
        $data['field'] = $this->input->get('field');
        $this->load->view($this->prefixUrl.'ajax_deliverymen', $data);
    }

    public function deliverymen($offset = 0) {
        $this->loginCheck($this->prefixUrl.'deliverymen');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Deliverymen", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Deliverymen List", site_url('section'));

        ob_start();
        $this->ajax_deliverymen($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Deliverymen List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl. "ajax_deliverymen";
        $data['this_url'] = base_url() . $this->prefixUrl."deliverymen";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'deliverymen', $data);
        $this->template->render();
    }

    public function ajax_contact_us($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."ajax_contact_us";
        $data['table'] = $this->tbl_contact_us;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $config['base_parent_url'] = base_url() . $this->prefixUrl."contact_us/";
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
        $table = $this->tbl_contact_us;
        $condit = "$table.id > 0 AND $table.is_deleted = 'N'";
        $select_fields = "";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`name` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }

                if ($val['name'] == 'from_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->tbl_contact_us}.created, '".FROM_UNIXTIME_SQL_FORMAT."') >=  '" . $search . "')";
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $condit .= "  AND (from_unixtime({$this->tbl_contact_us}.created, '".FROM_UNIXTIME_SQL_FORMAT."') <= '" . $search . "')";
                }
                
                if ($val['name'] == 'showing' and $val['value']) {
                    $this->limit = $val['value'];
                    $config['per_page'] = $this->limit;
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

        
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."contact_us/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_contact_us";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type'] = $order;
        $data['act'] = "t_c_u";
        $data['index_rpls'] = "contact_us";
        $data['ajax_index_rpls'] = "ajax_contact_us";
        $data['field'] = $this->input->get('field');
        $this->load->view($this->prefixUrl.'ajax_contact_us', $data);
    }

    public function contact_us($offset = 0) {
        $this->loginCheck($this->prefixUrl.'contact_us');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Contact Us", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Contact Us List", site_url('section'));

        ob_start();
        $this->ajax_contact_us($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Contact Us List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl. "ajax_contact_us";
        $data['this_url'] = base_url() . $this->prefixUrl."contact_us";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'contact_us', $data);
        $this->template->render();
    }

    public function ajax_customer_quries($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."ajax_customer_quries";
        $data['table'] = $this->customer_quries;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $config['base_parent_url'] = base_url() . $this->prefixUrl."customer_quries/";
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
        $table = $this->customer_quries;

        $joins['1'] = array(
            "table" => "tbl_customers",
            "condition" => "tbl_customers.id = {$table}.customer_id",
            "jointype" => "LEFT"
        );
        $condit = "$table.id > 0 AND $table.is_deleted = 'N'";
        $select_fields = " , tbl_customers.firstname,tbl_customers.lastname,tbl_customers.email,tbl_customers.phone,tbl_customers.country_mobile_code";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $keyword =  $search;
               
                $str[] = "`firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%' OR  `phone` LIKE '%" . $keyword . "%'";
                
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                
                if ( $val['name'] == 'statuschnage' and $val['value'] ) {
                    $search = $search;
                    $condit .= "  AND $table.`status` ='$search'";
                }
                
                if ( $val['name'] == 'ticket_id' and $val['value'] ) {
                    $search = $search;
                    $condit .= "  AND `ticket_id` ='$search'";
                }
                
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND $table.`created` >=". strtotime($search."00:00:00");
                }
                if ( $val['name'] == 'to_date' and $val['value'] ) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND $table.`created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'showing' and $val['value']) {
                    $this->limit = $val['value'];
                    $config['per_page'] = $this->limit;
                    $limit = $this->limit;
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

        
        $config['loadingId'] = 'loading-image';
        $this->jquery_pagination->initialize($config);

        $data['current_url'] = base_url() . $this->prefixUrl."customer_quries/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_customer_quries";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['act'] = "t_recom_r";
        $data['sort_type'] = $order;
        $data['index_rpls'] = "customer_quries";
        $data['ajax_index_rpls'] = "ajax_customer_quries";

        $data['field'] = $this->input->get('field');
        $this->load->view($this->prefixUrl.'ajax_customer_quries', $data);
    }

    public function customer_quries($offset = 0) {
        $this->loginCheck($this->prefixUrl.'customer_quries');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i>Customer Queries", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("List", site_url('section'));

        ob_start();
        $this->ajax_customer_quries($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Customer Queries List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl. "ajax_customer_quries";
        $data['this_url'] = base_url() . $this->prefixUrl."customer_quries";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'customer_quries', $data);
        $this->template->render();
    }

    // delete 
    public function delete() {
        $id = $this->uri->segment(4);

        $act = $this->input->get('act'); 
        $table = null;
        if($act =="t_r_r"){
            $table ="tbl_register_restaurant";
        }else if($act =="t_c_u"){
            $table ="tbl_contact_us";
        }else if($act =="t_r_d_b"){
            $table ="tbl_register_delivery_boy";
        }else if($act =="t_recom_r"){
            $table ="tbl_customer_quries";
        }
        if($table){
            $cond = array(
                'id' => $id,
            );
            $data = array(
                'is_deleted' => 'Y',
            );
            $this->main_model->cruid_update($table, $data, $cond);
            $this->session->set_userdata('smessage', 'Successfully deleted');
        }
    }

        // Actions 
    public function action() {
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

    // Delete selected 
    public function deleteall($current_url) {
        $this->checkUser();
        $act    = $this->input->post('act'); 
        $table  = null;
        if($act =="t_r_r"){
            $table ="tbl_register_restaurant";
        }else if($act =="t_c_u"){
            $table ="tbl_contact_us";
        }else if($act =="t_r_d_b"){
            $table ="tbl_register_delivery_boy";
        }else if($act =="t_recom_r"){
            $table ="tbl_customer_quries";
        }
        if($table){
            $checked = $this->input->post('check');
            if (empty($checked)) {
                $this->session->set_userdata('message', 'Please select atleast one.');
            } else {
                $data = array(
                              'is_deleted' => 'Y',
                            );
                for ($i = 0; $i < count($checked); $i++) {
                    $cond = array(
                                'id' =>  $checked[$i],
                            );
            
                    $this->main_model->cruid_update($table, $data, $cond);
                   // $this->main_model->cruid_delete('tbl_items', array('id' => $checked[$i]));
                }
            }
            $this->session->set_userdata('smessage', 'Selected successfully deleted');
        }
    }


    // Change Status 
    function change_status($slug) {
        $url = $this->input->get("return");
        $table = $this->table;
        $cond = array(
            'id' => $slug,
        );
        $data = array(
            'status' => 'A',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', "Change status successfully.");
    }

    public function ajax_change_status() {
        $res            = array();
        $action         = $this->input->post("action");
        $action_type    = $this->input->post("action_type");
        $statas         = $this->input->post("statas");
        $reason         = $this->input->post("reason");

        $id = $this->input->post('acid_t');
               
        if($id>0){
            $url = $this->input->get("return");
            $table = $this->customer_quries;
            $cond = array(
                'id' => $id,
            );
            $data = array(
                'status' => $action,
                'response_message' => $reason,
                'response_time' => time(),
            );
            $this->main_model->cruid_update($table, $data, $cond);
            $this->session->set_userdata('smessage', "Change status successfully.");
        }else{
            $res = array("status"=>"success", "message"=>$action." Somthing wrong try!");   
            echo json_encode($res); 
        }
               
           
    }

    //End Class
}