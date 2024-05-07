<?php

require_once('base.php');
class Cricket_points extends Base {

    private $limit = 22;
    private $table = 'tbl_cricket_points';
    private $image = '';
    private $prefixUrl = 'admin/cricket_points/';
    private $name = 'Point'; // For singular
    private $names = 'Points'; //plural form 

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
        $joins = array();
        $joins[1] = ['table'=>'tbl_game_types', 'condition'=>'tbl_cricket_points.game_type_id = tbl_game_types.id','jointype'=>'left'];
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
        $condit = "{$this->table}.is_deleted ='N' AND `meta_key` NOT IN ('Catch And Bowled','Century')";
        //$select_fields = " ";
        $select_fields = ", tbl_game_types.name as game_type";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`meta_key` LIKE '%" . $keyword . "%'";
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'game_type_id' and $val['value']) {
                    $condit .= "  AND `game_type_id` = '" . $search . "'";
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
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

    // add new user
    public function add() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();
        $table = $this->table;
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->names}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->names}";

        $this->form_validation->set_rules('game_type_id', 'Game Type', "trim|required|is_unique[tbl_cricket_points.game_type_id]");
        $this->form_validation->set_message('is_unique', 'This Game Type is already taken in ' . SITE_TITLE . ". Please try different");
        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', $this->prefixUrl.'add', $data);
            $this->template->render();
        } else {

          $CommonData = array(
                            'game_type_id' => $this->input->post('game_type_id'),
                            'status' => 'A',
                            'created_by' => $this->session->userdata('adminId'),
                            'updated_by' => $this->session->userdata('adminId'),
                            'created_at' => time(),               
                            'updated_at' => time(),               
                        );
            $table = $this->table;
            $points = unserialize(CRICKETPOINTS);
                foreach($points as $key=>$value)
                {
                    if(in_array($key, ["strike_rate","economy_rate"])){
                        continue;
                    }
                    $data = array_merge( $CommonData, array(
                            'meta_key' =>$this->input->post($key.'_key'),
                            'meta_value' => $this->input->post($key.'_value')
                        ) );
                 $business_id = $this->main_model->cruid_insert($table, $data);
                }
                $per_min = $this->input->post('per_min_p');
                $per_max = $this->input->post('per_max_p');
                $per_point = $this->input->post('per_price');

                $strike_rate  = [];
                $economy_rate = [];
                foreach ($per_min as $key => $value) {
                    $maxArr = $per_max[$key];
                    $pointArr = $per_point[$key];
                    if($key == "I"){
                        foreach ($value as $keyin => $valuein) {
                            $strike_rate[] = array("min"=>$valuein,"max"=>$maxArr[$keyin],"val"=>$pointArr[$keyin]);
                        }
                    }else if($key == "K"){
                         foreach ($value as $keyinE => $valueinE) {
                            $economy_rate[] = array("min"=>$valueinE,"max"=>$maxArr[$keyinE],"val"=>$pointArr[$keyinE]);
                        }

                    }
                }

               $strike_rate_data = array_merge( $CommonData, array(
                            'meta_key' =>"Strike Rate",
                            'meta_value' => json_encode( $strike_rate )
                        ) );
               $economy_rate_data = array_merge( $CommonData, array(
                            'meta_key' =>"Economy Rate",
                            'meta_value' => json_encode( $economy_rate )
                        ) );

                $this->main_model->cruid_insert($table, $strike_rate_data);

                $this->main_model->cruid_insert($table, $economy_rate_data);

            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    // edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "id =" . $user_name . "";
        // $cond = "game_type_id =" . $user_name . "";
        $select_fields = "$table.*";
        $joins = array();
        $all_data = array();
        $game_type_id = '';

        $user_detail = $this->main_model->cruid_select_array($table, $select_fields, $joins, $cond);        
        foreach($user_detail as $key=>$value)
            {
                $user_details[str_replace(' ','_',strtolower($value['meta_key']))]=$value['meta_value'];
                $all_data=$value;
                $game_type_id=$value['game_type_id'];
            }
        /* echo "<pre>";
        print_r($user_details);
        print_r($user_detail);
        die; */
        if (!empty($user_detail)) {
            $data['game_type'] = $game_type_id;
            $data['user_detail'] = $user_details;
            $data['all_data'] = $all_data;
            $data['title'] = "Update ";

            $this->form_validation->set_rules('game_type_id', 'Game Type', "trim|required");
            $this->form_validation->set_message('is_unique', 'This Game Type is already taken in ' . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {    
            $table = $this->table;          
            /*if(!empty($this->input->post('game_type_id')) && count($this->input->post('game_type_id')) >0 )
            {
                //$this->main_model->cruid_delete($table, $cond);
            }*/

             /*   $points = unserialize(CRICKETPOINTS);
                foreach($points as $key=>$value)
                {
                    $data = array(
                        //'meta_key' =>$this->input->post($key.'_key'),
                        'meta_value' => $this->input->post($key.'_value'),
                        //'game_type_id' => $this->input->post('game_type_id'),
                        //'status' => 'A',
                        //'created_by' => $this->session->userdata('adminId'),
                        'updated_by' => $this->session->userdata('adminId'),
                        //'created_at' => time(),               
                        'updated_at' => time(),               
                       );
                }*/


                if($this->input->post('json_as') && $this->input->post('json_as') !=null ){
                    $sa_json = $this->input->post('json_as');
                    $per_min = $this->input->post('per_min_p');
                    $per_max = $this->input->post('per_max_p');
                    $per_point = $this->input->post('per_price');

                    $strike_rate  = [];
                    $economy_rate = [];
                    foreach ($per_min as $key => $value) {
                        $maxArr = $per_max[$key];
                        $pointArr = $per_point[$key];
                        if($key == "I"){
                            foreach ($value as $keyin => $valuein) {
                                $strike_rate[] = array("min"=>$valuein,"max"=>$maxArr[$keyin],"val"=>$pointArr[$keyin]);
                            }
                        }else if($key == "K"){
                             foreach ($value as $keyinE => $valueinE) {
                                $economy_rate[] = array("min"=>$valueinE,"max"=>$maxArr[$keyinE],"val"=>$pointArr[$keyinE]);
                            }

                        }
                    }
                    if($sa_json == 'economy_rate'){
                       $economy_rate_data = array('meta_value' => json_encode( $economy_rate ),
                                                    'updated_by' => $this->session->userdata('adminId'),
                                                    'updated_at' => time()
                                                );
                        $this->main_model->cruid_update($table, $economy_rate_data, $cond);
                    }else if($sa_json == 'strike_rate' ){
                        $strike_rate_data = array('meta_value' => json_encode( $strike_rate ), 
                                                    'updated_by' => $this->session->userdata('adminId'),
                                                    'updated_at' => time());
                        $this->main_model->cruid_update($table, $strike_rate_data, $cond);
                    }
                }else{
                    $data = array(
                            //'meta_key' =>$this->input->post($key.'_key'),
                            'meta_value' => $this->input->post('meta_value'),
                            //'game_type_id' => $this->input->post('game_type_id'),
                            //'status' => 'A',
                            //'created_by' => $this->session->userdata('adminId'),
                            'updated_by' => $this->session->userdata('adminId'),
                            //'created_at' => time(),               
                            'updated_at' => time(),               
                           );
                    //print_r($data);die;
                     $business_id = $this->main_model->cruid_update($table, $data, $cond);
                }
                $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                redirect($url);
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
// activate all Admins
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

//  deactivate all Admins
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

    public function get_exists_gametype_htp_sports(){
         $sports_query =  $this->db->query("SELECT * FROM `tbl_games` WHERE `status` = 'A' AND `is_deleted` = 'N' ORDER BY `tbl_games`.`orderno` ASC ");
        $sports_results = $sports_query->result();
        $sports_tab =  '<ul class="nav nav-tabs faq-cat-tabs">';
        foreach ($sports_results as $sports_key => $sports_value) {
            $activeCls = ($sports_key === 0)?'active':'';
            $pageName="how_to_play";
            if($sports_value->id  =='0'){
                $pageName = "how_to_play";
            }elseif($sports_value->id  =='1'){
                $pageName = "how_to_play_kabaddi";
            }elseif($sports_value->id  =='2'){
                $pageName = "how_to_play_soccer";
            }else if($sports_value->id =='3'){
                $pageName = "how_to_play_basketball";
            }else if($sports_value->id =='4'){
                $pageName = "how_to_play_hockey";
            }else if($sports_value->id =='5'){
                $pageName = "how_to_play_volleyball";
            }
            $sports_tab .=  '<li><a href="'.base_url('admin/content_management/get_page_contents/'.$pageName).'" class="'.$activeCls.'" data-toggle="htp_tab_sports">'.$sports_value->name.'</a></li>';
        }
        $sports_tab .=  '</ul>';
        echo $sports_tab;
        echo '<script> $(document).ready(function(){
                $("[data-toggle=\'htp_tab_sports\']:first").click();
            });
            </script>';
    }
    public function get_exists_gametype_sports(){
        $sports_query =  $this->db->query("SELECT * FROM `tbl_games` WHERE `status` = 'A' AND `is_deleted` = 'N' ORDER BY `tbl_games`.`orderno` ASC ");
        $sports_results = $sports_query->result();
        $sports_tab =  '<ul class="nav nav-tabs nav-tab-game-type">';
        foreach ($sports_results as $sports_key => $sports_value) {
            $activeCls = ($sports_key === 0)?'active':'';
            $sports_tab .=  '<li><a href="'.base_url($this->prefixUrl.'get_exists_gametype/'.$sports_value->id).'" class="'.$activeCls.' game-type" data-toggle="tab_sports">'.$sports_value->name.'</a></li>';
        }
        $sports_tab .=  '</ul>';
        echo $sports_tab;
        echo '<script> $(document).ready(function(){
                $(".nav-tab-game-type li:first").trigger("click");
            });
            </script>';
    }

    public function get_exists_gametype($sports=0){

        if($sports ==0 ){
            $query =  $this->db->query("SELECT * FROM `tbl_game_types` WHERE `status` = 'A' AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");
        }elseif($sports ==1 ){
            $query =  $this->db->query("SELECT * FROM `tbl_kabaddi_game_types` WHERE `status` = 'A' AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_kabaddi_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");
        }elseif($sports ==2 ){
            $query =  $this->db->query("SELECT * FROM `tbl_soccer_game_types` WHERE `status` = 'A' AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_soccer_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");
        }

        $results = $query->result();
        $tabscreate = '<ul class="nav nav-tabs faq-cat-tabs">';
        foreach ($results as $key => $value) {
            $activeCls = ($key === 0)?'active':'';
           $tabscreate.= ' <li><a href="'.base_url($this->prefixUrl.'show_points/'.$value->id.'/'.$sports).'" class="'.$activeCls.'" data-toggle="tab">'.$value->name.'</a></li>';
        }
        $tabscreate. "</ul>";
        echo $tabscreate;

        echo '<script> $(document).ready(function(){
                $(".faq-cat-tabs li").first().click();
            }); tabscreateOneMore();
            </script>';
        }

    public function show_points($id =0, $sports=0){
        
        if($sports ==0 ){
            $query =  $this->db->query("SELECT * FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_type_id`='$id'");

           $results = $query->result();
           $data    = array();
           $data['batting_key']  =  array('Every Run Scored','Every Boundary Hit','Every Six Hit','Thirty Runs','Half Century','Century','Dismiss For A Duck');
           $data['bowling_key']  =  array('Wicket',"Two Wicket","Three Wicket",'Four Wicket','Five Wicket','Maiden Over');
           $data['fielding_key'] =  array('Run Out','Run Out Thrower','Run Out Catcher','Catch','Stumping');
           $data['others_key']   =  array('Captain'=>"2x",'Vice-Captain'=>"1.5x",'Being Part Of Eleven');
           $data['economy_rate'] =  array('Economy Rate');
           $data['strike_rate']  =  array('Strike Rate');
            
            $arrayCompine =   array();
            foreach ($results as $key => $value) {         
                $arrayCompine[$value->meta_key] = $value->meta_value;
            }
            $data['results'] = $arrayCompine;
            $data['sports'] = $sports;
            $this->load->view($this->prefixUrl.'point_system', $data);

        }elseif($sports ==1 ){
            $query =  $this->db->query("SELECT * FROM `tbl_kabaddi_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_type_id`='$id'");

            $results = $query->result();
            $data    = array();           
            $data['results'] = $results;
            $data['sports']  = $sports;
            $this->load->view($this->prefixUrl.'point_system', $data);

        }elseif($sports ==2 ){
            $query =  $this->db->query("SELECT * FROM `tbl_soccer_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_type_id`='$id'");

           $results = $query->result();
           $data    = array();

           $data['batting_key']  =  array('Played_55_minutes_Or_More','Played_Less_Than_55_Minutes');
           $data['bowling_key']  =  array('Goal_Gk_Defender','Goal_Midfielder','Goal_Forward','For_Every_Assist','For_Every_10_Passes_Completed','For_every_2_shots_On_Target');
           $data['fielding_key'] =  array('Clean_Sheet_Midfielder','Clean_Sheet_Gk_Defender','For_Every_3_Shots_Saved_Gk','For_Every_Panalty_Saved_Gk','For_Every_3_Successful_Tackles_Made');
           $data['others_key']   =  array('Yellow_Card','Red_Card','For_every_Own_goal','For_Every_2_Goals_Conceded_Gk_Defender','For_Every_Penalty_Missed');
            
            $arrayCompine =   array();
            foreach ($results as $key => $value) {         
                $arrayCompine[str_ireplace(" ", "_", $value->meta_key )] = $value->meta_value;
            }
            $data['results'] = $arrayCompine;
            $data['sports'] = $sports;
            $this->load->view($this->prefixUrl.'point_system', $data);

        }

    }
    //End of Countries class

}