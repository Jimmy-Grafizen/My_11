<?php
require_once('base.php');
class Team_settings extends Base {

    private $limit = 10;
    private $table = 'tbl_cricket_team_setting';
    private $image = '';
    private $prefixUrl = 'admin/team_settings/';
    private $name = 'Team Settings'; // For singular
    private $names = 'Team settings'; //plural form 

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
                    foreach($setting as $key => $val){
                        $this->main_model->cruid_update($table, array('value'=>$val), array('id'=>$key));
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
   }
