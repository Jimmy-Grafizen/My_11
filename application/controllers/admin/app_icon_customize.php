<?php
require_once('base.php');
class App_icon_customize extends Base {

    private $limit = 20;
    private $table = 'tbl_app_icon_customize';
    private $image = '';
    private $prefixUrl = 'admin/app_icon_customize/';
    private $name = 'App Icon'; // For singular
    private $names = 'App Icons'; //plural form 
    public $NotChange = [1,2,3,4,17];
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
        $config['base_url'] = base_url() . "admin/app_icon_customize/ajax_index";
        $config['base_parent_url'] = base_url() . "admin/app_icon_customize/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $order_by_other = array();
        $order_by = array(
            'field' => 'orderno',
            'type' => 'asc',
        );

        $table = "tbl_app_icon_customize";
        $condit = "tbl_app_icon_customize.id > 0";
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
                
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (from_unixtime(created, '%Y-%m-%d') LIKE '%" . $search . "%')";
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

        $data['current_url'] = base_url() . "admin/app_icon_customize/index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . "admin/app_icon_customize/ajax_index";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type'] = $order;
        $data['notchange'] = $this->NotChange;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['table'] = $this->table;

        $data['field'] = $this->input->get('field');
        $this->load->view('admin/app_icon_customize/ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck('admin/app_icon_customize/index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> App Icon Customize Management", site_url('/admin/app_icon_customize'), false);
        $this->breadcrumbs->push("App Icon Customize list", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "App Icon Customize List";
        //print_r($initial_content); die;
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . "admin/app_icon_customize/ajax_index";
        $data['this_url'] = base_url() . "admin/app_icon_customize/index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', 'admin/app_icon_customize/index', $data);
        $this->template->render();
    }

    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck('admin/app_icon_customize/edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> App Icon Customize Management", site_url('/admin/app_icon_customize'));
        $this->breadcrumbs->push('Edit Icon', site_url('section'));

        $url = $this->input->get("return");
        $table = "tbl_app_icon_customize";
        $cond = "id ='" . $user_name . "'";
        $select_fields = "tbl_app_icon_customize.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update App Icon Customize";
            $this->form_validation->set_rules('name', 'Name', "trim|required");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', 'admin/app_icon_customize/edit', $data);
                $this->template->render();
            } else {

                $dataInasert = array(
                    'updated_by' => $this->session->userdata('adminId'),
                    'name' => $this->input->post('name'),
                    'updated_at' => time()
                );
            /**********/
            if(!empty($_FILES['image']['name'])){
                //validating files first
                if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                 if (!is_dir(APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH)) {
                    mkdir(APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH, 0777, true);
                 }if (!is_dir(APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH)) {
                    mkdir(APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH, 0777, true);
                 } 
                } 
                $config['upload_path']          = APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH;
                $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                $config['max_size']             = ALLOWED_FILE_SIZE;
                    /*************************************************************************/
                    $redirectimg = $this->prefixUrl.'edit/'.$id."?return=".$url;
                        $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                        $width      = $fileinfo[0];
                        $height     = $fileinfo[1];
                        $ratio      = $height/$width;
                        $ratio      = round($ratio,2);
                        
                      // Validate image file dimension
                       if($width > "300" && $height > "300"){
                            $data['validation_errors'] = "<p>Image dimension should be within max width=300px and max height=300px.</p>";
                            //$this->template->write_view('contents', $this->prefixUrl.'add', $data);
                            //$this->template->render();
                            $this->session->set_userdata('message', "<p>Image dimension should be within max width=300px and max height=300px.</p>");
                            redirect($redirectimg);
                           
                       }
                       /*
                       else if($ratio!="0.41" && $ratio!="0.42"){
                            $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
                            $this->template->render();
                            $this->session->set_userdata('message', "<p>Image dimension should be within 800 X 328.</p>");
                            redirect($redirectimg);
                       }*/
                    /*************************************************************************/

                //changing file name for selected
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $config['file_name']  = rand()."_".time()."_icon.$ext";
                $file_upload = $this->upload_files($config, "image");
                

                if($file_upload['status']=="success"){
                    //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                    $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH,APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH);
                    $dataInasert['image'] = $file_upload['data']['file_name'];
                }
                else{
                    if(!empty($file_upload['data'])){
                        $data['validation_errors'] = $file_upload['data'];
                    }else{
                        $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                    }                   
                    $this->template->write_view('contents', $this->prefixUrl.'index', $data);
                    $this->template->render();
                    $imageError = 'Y';
                }
            }
            /**********/
                $this->main_model->cruid_update($table, $dataInasert, $cond);

                $this->session->set_userdata('smessage', 'App Icon Customize updated successfully');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', 'Sorry, this record not available');
            redirect($url);
        }
    }

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
    // activate user profile
    public function activate($slug) {
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
    public function deactivate($slug) {
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
/* End of Class */
}