<?php
require_once('base.php');
class Referral_cash_bonus extends Base {

    private $limit = 10;
    private $table = 'tbl_referral_cash_bonus';
    private $image = '';
    private $prefixUrl = 'admin/referral_cash_bonus/';
    private $name = 'Referral Cash Bonus'; // For singular
    private $names = 'Referrals Cash Bonus'; //plural form 

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

// add new user
    public function edit() {
        
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl),false);
        $this->breadcrumbs->push("Update {$this->names}", site_url('section'));

        $settings = $this->main_model->cruid_select_array($this->table, "*", array(), NULL,NULL,array());

        $redirect = $this->prefixUrl.'edit';
        $data['title'] = "Update {$this->names}";
        $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['settings'] = $settings;
        
        $table = $this->table;






        //fetching users information
            if($this->input->post('setting')){ 

                $setting = $this->input->post('setting');
                $data = array('created_by' => $this->session->userdata('adminId'),
                		'updated_by' => $this->session->userdata('adminId'),
                		'created_at' => time(),               
                		'updated_at' => time(),               
                		);
                foreach($setting as $key => $val){ 
                	$data['value']	=	$val;
                    $this->main_model->cruid_update($table,$data, array('id'=>$key));
                }

                if($this->input->post('CONTEST_BASED')){
                    $CONTEST_BASED = "Y";
                }else{
                    $CONTEST_BASED = "N";  
                }
                    $data['value']  =   $CONTEST_BASED;
                    $this->main_model->cruid_update($table,$data, array('key'=>'CONTEST_BASED'));
                   

                 /*******Image Area ****************************/
            
                if(!empty($_FILES['REFERRAL_EARN_IMAGE']['name'])){
                    //validating files first
                    if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                     if (!is_dir(REFER_EARN_IMAGE_LARGE_PATH)) {
                        mkdir(REFER_EARN_IMAGE_LARGE_PATH, 0777, true);
                     }if (!is_dir(REFER_EARN_IMAGE_THUMB_PATH)) {
                        mkdir(REFER_EARN_IMAGE_THUMB_PATH, 0777, true);
                     } 
                    } 
                    $config['upload_path']          = REFER_EARN_IMAGE_LARGE_PATH;
                    $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                    //$config['max_size']             = ALLOWED_FILE_SIZE;

                    /*************************************************************************/
                        $fileinfo   = @getimagesize($_FILES["REFERRAL_EARN_IMAGE"]["tmp_name"]);
                        $width      = $fileinfo[0];
                        $height     = $fileinfo[1];
                        $ratio      = $height/$width;
                        $ratio      = round($ratio,2);
                    /*************************************************************************/

                    //changing file name for selected
                    $ext = pathinfo($_FILES['REFERRAL_EARN_IMAGE']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_context_cat.$ext";
                    $file_upload = $this->upload_files($config, "REFERRAL_EARN_IMAGE");
                     if($file_upload['status']=="success"){
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], REFER_EARN_IMAGE_LARGE_PATH,REFER_EARN_IMAGE_THUMB_PATH);
                        
                        $insert_data['value'] = $file_upload['data']['file_name'];
                        $this->main_model->cruid_update($table,$insert_data, array('key'=>'REFERRAL_EARN_IMAGE'));
                    }
                    else{
                        if(!empty($file_upload['data'])){
                            $data['validation_errors'] = $file_upload['data'];
                        }else{
                            $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                        }                   
                        $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                        $this->template->render();
                        $imageError = 'Y';
                    }
                }

                 /***********************************/
                $this->session->set_userdata('smessage', $this->names.' Successfully Saved');
                redirect($redirect);
            }

            $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
            $this->template->render();
    }
}
