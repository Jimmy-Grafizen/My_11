<?php  require_once('base.php');
class Testimonial extends Base {
      private $limit = 10;
    private $table = 'tbl_testimonial';
    private $tcm = '';
    private $image = '';
    private $prefixUrl = 'admin/testimonial/';
    private $name = 'Testimonial'; // For singular
    private $names = 'Testimonials'; //plural form 

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
     /// error_reporting(E_ALL);
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
    ///    $joins[1] = ['table'=>"{$this->tcm} tcm", 'condition'=>"{$this->table}.match_unique_id=tcm.unique_id",'jointype'=>'left'];

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
                'type' => 'asc',
            );
        }
        $table = $this->table;
        $condit = "";
        $select_fields = "";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str = "`name` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (`created_at` LIKE '%" . $search . "%')";
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
//echo $this->prefixUrl;
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

        $redirect = HTTP_PATH . 'admin/testimonial/index';
        $data['title'] = "Add New {$this->names}";
        $data['table'] = $this->table;
        $data['name'] = $this->name;
        $data['names'] = $this->names;
		
			if(isset($_FILES["image"]) && !empty($_FILES['image']['name'])){
              
              //echo  '<pre>';print_r($_POST); die;
                $content         = $this->input->post('content');
                $designation         = $this->input->post('designation');
                $name         = $this->input->post('name');
              

				$insert_data = array(                
                        'content'           => $content,
                        'designation'           => $designation,
                        'name'           => $name,
                        'status'            => 'Y',
                        'created_at'        => time(),               
                        'updated_at'        => time(),               
                    );

				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(SLIDER_IMAGE_LARGE_PATH)) {
					mkdir(SLIDER_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(SLIDER_IMAGE_THUMB_PATH)) {
					mkdir(SLIDER_IMAGE_THUMB_PATH, 0777, true);
				 } 
				} 
				$config['upload_path']          = SLIDER_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;
				
				/*************************************************************************/
					$fileinfo 	= @getimagesize($_FILES["image"]["tmp_name"]);
					$width 		= $fileinfo[0];
					$height 	= $fileinfo[1];
					$ratio      = $height/$width;
					$ratio      = round($ratio,2);
					
				   // Validate image file dimension
				  /* if($width > "800" || $height > "328"){
					    $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
						$this->template->write_view('contents', $this->prefixUrl.'add', $data);
						$this->template->render();
						return;
				   }else if($ratio!="0.41" && $ratio!="0.42"){
					    $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
						$this->template->write_view('contents', $this->prefixUrl.'add', $data);
						$this->template->render();
						return;
				   }*/

				/*************************************************************************/
				
				//changing file name for selected
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_slider.$ext";
				$file_upload = $this->upload_files($config, "image");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], SLIDER_IMAGE_LARGE_PATH,SLIDER_IMAGE_THUMB_PATH);
					$insert_data['image'] = $file_upload['data']['file_name'];
				}
				else{
					if(!empty($file_upload['data'])){
						$data['validation_errors'] = $file_upload['data'];
					}else{
						$data['validation_errors'] = "<p>There was an error while uploading image.</p>";
					}					
					$this->template->write_view('contents', $this->prefixUrl.'add', $data);
					$this->template->render();
					$imageError = 'Y';
				}
			

            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $insert_data);

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
		else{
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
			 }
    }

    // edit user detail
    public function edit() {
        //echo '<pre>'; print_r($_FILES); die;

        $user_name = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = HTTP_PATH . 'admin/testimonial/index';
        $table = $this->table;
        $cond = "id ='" . $user_name . "'";
        $select_fields = "$table.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
		 //echo '<pre>';print_r($user_detail); 
		$old_files_to_remove = array();
		$selected_image_old = $user_detail['image'];
        if (!empty($user_detail)) {
         
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Admins Details";
			$data['table'] = $this->table;
			$data['name'] = $this->name;
			$data['names'] = $this->names;
			
            $content  = $this->input->post('content');
            $designation         = $this->input->post('designation');
            $name         = $this->input->post('name');
            $image         = $this->input->post('name');

                $Update_data = array(                
                        'content'       => $content,
                        'designation'   => $designation,
                        'name'          => $name,
                        'status'        => 'D',
                        'created_at'    => time(),               
                        'updated_at'    => time(),                    
                );

			if(isset($_FILES["image"]) ){
				
			/*************************************************************************/
				$fileinfo 	= @getimagesize($_FILES["image"]["tmp_name"]);
				$width 		= $fileinfo[0];
				$height 	= $fileinfo[1];
				$ratio      = $height/$width;
				$ratio      = round($ratio,2);
					
			    // Validate image file dimension
			/*    if($width > "800" || $height > "328"){
					$data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
					$this->template->write_view('contents', $this->prefixUrl.'add', $data);
					$this->template->render();
					return;
			    }else if($ratio!="0.41" && $ratio!="0.42"){
					    $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
						$this->template->write_view('contents', $this->prefixUrl.'add', $data);
						$this->template->render();
						return;
				}*/

			/*************************************************************************/

				//validating files first
				if (IMAGE_UPLOAD_TYPE!="BUCKET") {
				 if (!is_dir(SLIDER_IMAGE_LARGE_PATH)) {
					mkdir(SLIDER_IMAGE_LARGE_PATH, 0777, true);
				 }if (!is_dir(SLIDER_IMAGE_THUMB_PATH)) {
					mkdir(SLIDER_IMAGE_THUMB_PATH, 0777, true);
				 } 
				}
				$config['upload_path']          = SLIDER_IMAGE_LARGE_PATH;
				$config['allowed_types']        = ALLOWED_IMAGE_TYPES;
				$config['max_size']             = ALLOWED_FILE_SIZE;

				//changing file name for selected
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				$config['file_name']  = rand()."_".time()."_context_cat.$ext";
				$file_upload = $this->upload_files($config, "image");
				

				if($file_upload['status']=="success"){
					//$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
					$res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], SLIDER_IMAGE_LARGE_PATH, SLIDER_IMAGE_THUMB_PATH);
					$Update_data['image'] = $file_upload['data']['file_name'];
					
					$old_selected = $selected_image_old;								
					$old_files_to_remove[] = SLIDER_IMAGE_LARGE_PATH.$old_selected;
					$old_files_to_remove[] = SLIDER_IMAGE_THUMB_PATH.$old_selected;
				}
				/*else{
					if(!empty($file_upload['data'])){
						$data['validation_errors'] = $file_upload['data'];
					}else{
						$data['validation_errors'] = "<p>There was an error while uploading image.</p>";
					}					
					$this->template->write_view('contents', $this->prefixUrl.'edit', $data);
					$this->template->render();
					$imageError = 'Y';
				}*/
			}
                if($this->input->post()){
                    $this->main_model->cruid_update($table, $Update_data, $cond);
    				//$this->removeFiles($old_files_to_remove);
                    $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                    redirect($url);
               }else{
				    $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                    $this->template->render();
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
       // echo $id; die;
        $this->loginCheck($this->prefixUrl.'delete/' . $id);
        $this->checkUser();
		/*$data = array(
			'is_deleted' => "Y",
		);*/
		$cond = "id ='" . $id . "'";
		$this->main_model->cruid_delete($this->table, $cond);
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
            'status' => '2',
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
            'status' => '1',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', "Selected {$this->name} successfully deactivated");
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
//  Slider position order all 
    public function position_order() {
        $this->checkUser();
        $position = $this->input->post('position');
		
        if (empty($position)) {
			echo false;
           // $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {  
			$table = $this->table;
			$i=1;
			foreach($position as $k=>$v){
				$cond = array(
                    'id' => $v,
                );
                $data = array(
                    'slider_order' => $i,
                );
                $this->main_model->cruid_update($table, $data, $cond);
			$i++;
			}
        }
       // $this->session->set_userdata('smessage', "Selected {$this->name} successfully deactivated");
		echo true;
    }
//End of Countries class
}
?>