<?php
require_once('base.php');
class Settings extends Base {

    private $limit = 10;
    private $table = 'tbl_settings';
    private $image = '';
    private $prefixUrl = 'admin/settings/';
    private $name = 'Settings'; // For singular
    private $names = 'Settings'; //plural form 

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        $this->template->set_master_template('admin');
        $this->load->library('breadcrumbs');

        $this->load->model('main_model');
        $this->load->helper('text');

        if ($this->session->userdata('condit'))
            $this->session->unset_userdata('condit');

        $this->load->library('Jquery_pagination');
    }

    function loginCheck($str) {
        if (!$this->session->userdata('adminId')) {
            $this->session->set_userdata('returnURL', $str);
            redirect('admin');
        }
    }

    function checkUser() {
        return true;
    }

    public function edit() {
        $this->loginCheck($this->prefixUrl.'edit');
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
                    foreach($setting as $key => $val){
                        $this->main_model->cruid_update($table, array('value'=>$val), array('id'=>$key));
                    }

                 /*******Image Area ****************************/
            
                if(!empty($_FILES['DISCOUNTED_IMAGE']['name'])){
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
                        $fileinfo   = @getimagesize($_FILES["DISCOUNTED_IMAGE"]["tmp_name"]);
                        $width      = $fileinfo[0];
                        $height     = $fileinfo[1];
                        $ratio      = $height/$width;
                        $ratio      = round($ratio,2);
                        
                       // Validate image file dimension
                       /*if($width > "400" || $height > "400"){
                            $data['validation_errors'] = "<p>Image dimension should be within 400 X 400.</p>";
                            $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                            $this->template->render();
                            $this->session->set_userdata('message',"<p>Image dimension should be within 400 X 400.</p>");
                            redirect($redirect);
                           
                       }*/
                    //changing file name for selected
                    $ext = pathinfo($_FILES['DISCOUNTED_IMAGE']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_discounted_image.$ext";
                    $file_upload = $this->upload_files($config, "DISCOUNTED_IMAGE");
                    

                    if($file_upload['status']=="success"){
                        //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH,APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH);
                        $insert_data = [];
                        $insert_data['value']  = $file_upload['data']['file_name'];
                        $insert_data['width']  = $width;
                        $insert_data['height'] = $height;
                        $this->main_model->cruid_update($table,$insert_data, array('key'=>'DISCOUNTED_IMAGE'));
                    }else{
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
                
                if(!empty($_FILES['share_image']['name'])){
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
                        $fileinfo   = @getimagesize($_FILES["share_image"]["tmp_name"]);
                        $width      = $fileinfo[0];
                        $height     = $fileinfo[1];
                        $ratio      = $height/$width;
                        $ratio      = round($ratio,2);
                        
                       
                    $ext = pathinfo($_FILES['share_image']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_share_image.$ext";
                    $file_upload = $this->upload_files($config, "share_image");
                    

                    if($file_upload['status']=="success"){
                        //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], APP_ICON_CUSTOMIZE_IMAGE_LARGE_PATH,APP_ICON_CUSTOMIZE_IMAGE_THUMB_PATH);
                        $insert_data = [];
                        $insert_data['value']  = $file_upload['data']['file_name'];
                        $insert_data['width']  = $width;
                        $insert_data['height'] = $height;
                        $this->main_model->cruid_update($table,$insert_data, array('key'=>'share_image'));
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
                    $this->session->set_userdata('smessage', 'Settings Successfully Saved');
                    redirect($redirect);
                }
            

            if(!empty($this->session->userdata('smessage'))){
                //$data['smessage'] = $this->session->userdata('smessage');
              //  $this->session->unset_userdata('smessage');
            }

            $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
            $this->template->render();
    }

    public function customer_avatars()
    {
        $this->loginCheck($this->prefixUrl.'customer_avatars');
        $this->checkUser();

        $this->table = "tbl_customer_avatars";

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Customer Avatars", site_url($this->prefixUrl),false);
        $this->breadcrumbs->push("Update Customer Avatars", site_url('section'));

        $redirect = $this->prefixUrl.'customer_avatars';
        $data['title'] = "Customer Avatars";
        $data['table'] = $this->table;
        $data['name'] = "Customer Avatars";
        $data['names'] = "Customer Avatars";        
        $table = $this->table;

        /*******Image Area ****************************/
            
        if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
            //validating files first
            if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                if (!is_dir(CUSTOMER_IMAGE_LARGE_PATH)) {
                    mkdir(CUSTOMER_IMAGE_LARGE_PATH, 0777, true);
                }if (!is_dir(CUSTOMER_IMAGE_THUMB_PATH)) {
                    mkdir(CUSTOMER_IMAGE_THUMB_PATH, 0777, true);
                } 
            } 
            $config['upload_path']          = CUSTOMER_IMAGE_LARGE_PATH;
            $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
            $config['max_size']             = ALLOWED_FILE_SIZE;

            /*************************************************************************/
                $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                $width      = $fileinfo[0];
                $height     = $fileinfo[1];
                $ratio      = $height/$width;
                $ratio      = round($ratio,2);
                
               // Validate image file dimension
               /* if($width > "500" && $height > "500"){
                    $data['validation_errors'] = "<p>Image dimension should be within max width=500px and max height=500px.</p>";
                    $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                    $this->template->render();
                    $this->session->set_userdata('message',"<p>Image dimension should be within max width=500px and max height=500px.</p>");
                    redirect($redirect);
                   
               } if($width != $height){
                    $data['validation_errors'] = "<p>Image upload square dimension 300*300.</p>";
                    $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                    $this->template->render();
                    $this->session->set_userdata('message', "<p>Image upload square dimension 300*300.</p>");
                    redirect($redirect);
               } */
            /*************************************************************************/

            //changing file name for selected
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $config['file_name']  = rand()."_".time()."_image.$ext";
            $file_upload = $this->upload_files($config, "image");
            

            if($file_upload['status']=="success"){
                //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], CUSTOMER_IMAGE_LARGE_PATH,CUSTOMER_IMAGE_THUMB_PATH);
                
                $insert_data = array(
                        'image' => $file_upload['data']['file_name'],
                        'width'  => $width,
                        'height' => $height,
                        'status' => 'A',
                        'created_by' => $this->session->userdata('adminId'),
                        'updated_by' => $this->session->userdata('adminId'),
                        'created_at' => time(),               
                        'updated_at' => time(),               
                    );
                    $table      = $this->table;
                    $last_id    = $this->main_model->cruid_insert($table, $insert_data);

            }else{
                if(!empty($file_upload['data'])){
                    $data['validation_errors'] = $file_upload['data'];
                }else{
                    $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                }                   
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
                $imageError = 'Y';
            }
            $this->session->set_userdata('smessage', 'Image Successfully Saved');
            redirect($redirect);
        }
            $profile_pictures = $this->main_model->cruid_select_array($this->table, "*", array(), "is_deleted ='N'",NULL,array('field'=>'id','type'=>"DESC"));
            $data['profile_pictures'] = $profile_pictures;

            $this->template->write_view('contents', $this->prefixUrl.'customer_avatars', $data);
            $this->template->render();
    }

    public function customer_avatars_delete() {
        $this->table = "tbl_customer_avatars";

        $id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'customer_avatars_delete/' . $id);
        $this->checkUser();
        $data = array(
            'is_deleted' => "Y",
            'updated_by' => $this->session->userdata('adminId'),
            'updated_at' => time(),  
        );
        $cond = "id ='" . $id . "'";
        $this->main_model->cruid_update($this->table, $data, $cond);
        //$this->main_model->cruid_delete($this->table, array('id' => $id));
        $this->session->set_userdata('smessage', "Successfully deleted");
    }

    //End Class
}
