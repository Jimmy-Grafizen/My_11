<?php
require_once('base.php');
class Reports extends Base {

    private $limit = 15;
    private $table = 'tbl_cricket_matches';
    private $tbl_cricket_series = 'tbl_cricket_series';
    private $tbl_games = 'tbl_games';
    private $tbl_game_types = 'tbl_game_types';
    private $tbl_teams = 'tbl_cricket_teams';
    private $tbl_cricket_players = 'tbl_cricket_players';
    private $tbl_cricket_player_galleries = 'tbl_cricket_player_galleries';
    private $tbl_cricket_match_players = 'tbl_cricket_match_players';
    private $tbl_cricket_contests = 'tbl_cricket_contests';
    private $tbl_cricket_contest_categories = 'tbl_cricket_contest_categories';
    private $tbl_cricket_contest_matches = 'tbl_cricket_contest_matches';
    private $image = '';
    private $prefixUrl = 'admin/reports/';
    private $name = 'Match '; // For singular
    private $names = 'Matches'; //plural form 
    private $tbl_withdraw_requests =  'tbl_withdraw_requests';
    private $ctable = 'tbl_customers';
    private $country =  'tbl_countries';
    private $state =  'tbl_states';
    private $cname = 'Customer Report'; // For singular
    private $cnames = 'Customer reports'; //plural form 
    private $tname = 'Tax'; // For singular
    private $tnames = 'Tax'; //plural form 
    
    private $tccc = 'tbl_cricket_customer_contests'; 

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
    
    function tax($offset=0){
      $this->loginCheck($this->prefixUrl.'tax');
 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->tnames} Report", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Tax List", site_url('section'));

        ob_start();
        $this->ajax_tax($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = $this->tname." Report";
        $data['names'] = $this->tnames." Reports";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->tnames} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_tax";
        $data['this_url'] = base_url() . $this->prefixUrl."tax";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'tax', $data);
        $this->template->render();  
    }
    
    function ajax_tax($offset = 0,$from_eraning =0,$from_contest_Detail =0){
        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_tax";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/tax/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->tbl_cricket_series} series", 'condition'=>"{$this->table}.series_id=series.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_games} game", 'condition'=>"{$this->table}.game_id = game.id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_game_types} game_type", 'condition'=>"{$this->table}.game_type_id = game_type.id",'jointype'=>'left'];
        $joins[4] = ['table'=>"$this->tbl_teams tbl_teams1", 'condition'=>"{$this->table}.team_1_id = tbl_teams1.id",'jointype'=>'left'];
        $joins[5] = ['table'=>"$this->tbl_teams tbl_teams2", 'condition'=>"{$this->table}.team_2_id = tbl_teams2.id",'jointype'=>'left'];
        $joins[6] = ['table'=>"tbl_cricket_customer_contests tccc", 'condition'=>"{$this->table}.unique_id = tccc.match_unique_id",'jointype'=>'left'];
        $joins[7] = ['table'=>"tbl_cricket_contest_matches tccm", 'condition'=>"tccm.id = tccc.match_contest_id",'jointype'=>'join'];
        $joins[8] = ['table'=>"(SELECT match_id,count(*) as contest_count from tbl_cricket_contest_matches GROUP BY match_id) tccm1", 'condition'=>"{$this->table}.id = tccm1.match_id",'jointype'=>'left'];
        
        $group_by = "tccc.match_unique_id";

        $order_by = array(
            'field' => 'match_date',
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
                'field' => 'match_date',
                'type' => 'desc',
            );
        }
        $table = $this->table;
        $condit = "{$this->table}.is_deleted ='N'"; //{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND 
        $condit .= " AND tax_amount>0 "; //{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND 
        if($from_eraning){
            $condit .= "  AND {$this->table}.`unique_id` IN(". $from_eraning.") ";
        }
        
        $select_fields = " ,series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name, contest_count,count(tccc.match_unique_id) as joined_total_teams ,SUM(tccm.entry_fees) as spendamount, SUM(tccc.refund_amount) as refund_amount, SUM(tccc.`win_amount`) AS winamount, SUM(tccc.`tax_amount`) AS tax_amount,(SUM(tccm.entry_fees) - SUM(tccc.`win_amount`) - SUM(tccc.refund_amount)) AS earnings ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "{$this->table}.`unique_id` LIKE '%" . $keyword . "%' OR {$this->table}.`name` LIKE '%" . $keyword . "%' OR series.`name` LIKE '%" . $keyword . "%' OR game.`name` LIKE '%" . $keyword . "%' OR game_type.`name` LIKE '%" . $keyword . "%' OR tbl_teams1.`name` LIKE '%" . $keyword . "%' OR tbl_teams2.`name` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'series_id' and $val['value']) {
                    $condit .= "  AND `series_id` ='". $search."'";
                }
                if ($val['name'] == 'match_progress' and $val['value']) {
                    $condit .= "  AND `match_progress` ='". $search."'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `match_date` >=". $this->session->userdata('from_date');
                $condit .= "  AND `match_date` <=". $this->session->userdata('to_date');

            }
        /*********************/
        if($from_contest_Detail >0 ){
            $cond = "{$table}.id ='" .  $from_contest_Detail . "'";
            $user_detail = $this->main_model->cruid_select($table, "{$table}.*" . $select_fields, $joins, $cond);
            return $user_detail;
        }
        /*********************/
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);


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

        $data['current_url'] = base_url() . $this->prefixUrl."tax/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_tax";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type']  = $order;
        $data['field']      = $this->input->get('field');
        $data['prefixUrl']  = $this->prefixUrl;
        $data['table']      = $this->table;
        $data['name']       = $this->tname." report";
        $data['names']      = $this->tnames." Reports";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        
        $data['export'] = "no";
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'export_matches_table_view', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_tax', $data);
        }
    }
    
    // Matches Report listing
    function ajax_index($offset = 0,$from_eraning =0,$from_contest_Detail =0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_index";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->tbl_cricket_series} series", 'condition'=>"{$this->table}.series_id=series.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_games} game", 'condition'=>"{$this->table}.game_id = game.id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_game_types} game_type", 'condition'=>"{$this->table}.game_type_id = game_type.id",'jointype'=>'left'];
        $joins[4] = ['table'=>"$this->tbl_teams tbl_teams1", 'condition'=>"{$this->table}.team_1_id = tbl_teams1.id",'jointype'=>'left'];
        $joins[5] = ['table'=>"$this->tbl_teams tbl_teams2", 'condition'=>"{$this->table}.team_2_id = tbl_teams2.id",'jointype'=>'left'];
        $joins[6] = ['table'=>"tbl_cricket_customer_contests tccc", 'condition'=>"{$this->table}.unique_id = tccc.match_unique_id",'jointype'=>'left'];
        $joins[7] = ['table'=>"tbl_cricket_contest_matches tccm", 'condition'=>"tccm.id = tccc.match_contest_id",'jointype'=>'join'];
        $joins[8] = ['table'=>"(SELECT match_id,count(*) as contest_count from tbl_cricket_contest_matches GROUP BY match_id) tccm1", 'condition'=>"{$this->table}.id = tccm1.match_id",'jointype'=>'left'];
        
        $group_by = "tccc.match_unique_id";

        $order_by = array(
            'field' => 'match_date',
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
                'field' => 'match_date',
                'type' => 'desc',
            );
        }
        $table = $this->table;
        $condit = "{$this->table}.is_deleted ='N'"; //{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND 
        if($from_eraning){
            $condit .= "  AND {$this->table}.`unique_id` IN(". $from_eraning.") ";
        }
        
        $select_fields = " ,series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name, contest_count,count(tccc.match_unique_id) as joined_total_teams ,SUM(tccm.entry_fees) as spendamount, SUM(tccc.refund_amount) as refund_amount, SUM(tccc.`win_amount`) AS winamount, SUM(tccc.`tax_amount`) AS tax_amount,(SUM(tccm.entry_fees) - SUM(tccc.`win_amount`) - SUM(tccc.refund_amount)) AS earnings ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "{$this->table}.`unique_id` LIKE '%" . $keyword . "%' OR {$this->table}.`name` LIKE '%" . $keyword . "%' OR series.`name` LIKE '%" . $keyword . "%' OR game.`name` LIKE '%" . $keyword . "%' OR game_type.`name` LIKE '%" . $keyword . "%' OR tbl_teams1.`name` LIKE '%" . $keyword . "%' OR tbl_teams2.`name` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'series_id' and $val['value']) {
                    $condit .= "  AND `series_id` ='". $search."'";
                }
                if ($val['name'] == 'match_progress' and $val['value']) {
                    $condit .= "  AND `match_progress` ='". $search."'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `match_date` >=". $this->session->userdata('from_date');
                $condit .= "  AND `match_date` <=". $this->session->userdata('to_date');

            }
        /*********************/
        if($from_contest_Detail >0 ){
            $cond = "{$table}.id ='" .  $from_contest_Detail . "'";
            $user_detail = $this->main_model->cruid_select($table, "{$table}.*" . $select_fields, $joins, $cond);
            return $user_detail;
        }
        /*********************/

        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);


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
        $data['sort_type']  = $order;
        $data['field']      = $this->input->get('field');
        $data['prefixUrl']  = $this->prefixUrl;
        $data['table']      = $this->table;
        $data['name']       = $this->name." report";
        $data['names']      = $this->names." Reports";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        
        $data['export'] = "no";
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'export_matches_table_view', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_index', $data);
        }
    }

    public function index($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');
 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names} Report", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Matches List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = $this->name." Report";
        $data['names'] = $this->names." Reports";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index";
        $data['this_url'] = base_url() . $this->prefixUrl."index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

    // View Player matches completed
    public function view_completed() {

        $user_name = $this->uri->segment(4);
        $unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names} Report", site_url($this->prefixUrl));
        $this->breadcrumbs->push("View {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $table = $this->table;
        $cond = "{$this->table}.id ='" . $user_name . "'";
        $select_fields = "$table.*, {$this->tbl_cricket_series}.id as series_id";
        $joins = array(['table'=>$this->tbl_cricket_series, 'condition'=>"{$this->table}.series_id = {$this->tbl_cricket_series}.id",'jointype'=>'left']);


        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update {$this->name} Details";
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            
            /********************************/
            

        $tbl_match = $this->table;
        $cond_series = "{$tbl_match}.id ='" . $unique_id . "'";
        $select_fieldsseries = "$tbl_match.*, tblseries.name as series_name";
        $joins = array(['table'=>$this->tbl_cricket_series." tblseries", 'condition'=>"{$tbl_match}.series_id = tblseries.id",'jointype'=>'left']);
        $match_detail = $this->main_model->cruid_select($tbl_match, $select_fieldsseries, $joins, $cond_series);
        $data['match_detail'] = $match_detail;

            /********************************/

            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'view_completed', $data);
                $this->template->render();
            } else {
                    
                $close_date = date_create_from_format("d-m-Y h:i a",$this->input->post('close_date'));
                $close_date = strtotime(date_format($close_date,"Y-m-d h:i a"));
                $data = array(
                    //'name' =>         $this->input->post('name'),
                    // 'match_limit' => $this->input->post('match_limit'),
                    //'match_progress' => $this->input->post('match_progress'),
                    //'close_date'  => $close_date,
                    // 'series_id'   => $this->input->post('series_id'),
                    'updated_by'  => $this->session->userdata('adminId'),
                    'updated_at'  => time(),               
                );

                $this->main_model->cruid_update($table, $data, $cond);
                $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }
                    
    public function get_our_db_completed_playes(){
        $this->checkUser();
        $unique_id = $this->uri->segment(4);//$this->input->post('unique_id');
        /**************************************************/
        
        $get_player['prefixUrl'] = 'admin/match_players/';
        $get_player['table'] = $this->table;
        $get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_id'] = $unique_id;
        if (empty($unique_id)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {

        $cond = "{$this->table}.unique_id ='" . $unique_id . "'  AND {$this->table}.is_deleted='N'";
        $teamGet = $this->main_model->cruid_select($this->table, "id,team_1_id,team_2_id", [], $cond);
        $team_1 = $teamGet['team_1_id'];
        $team_2 = $teamGet['team_2_id'];
        
        $get_player['current_url'] = base_url() . $this->prefixUrl."index/" . ($teamGet['id'] ? $teamGet['id'] : "");
        
        $table = "{$this->tbl_cricket_match_players} tcmp";
        $cond = ["tcmp.match_unique_id"=>$unique_id , "tcmp.team_id"=>$team_1];
        $cond2 = ["tcmp.match_unique_id"=>$unique_id , "tcmp.team_id"=>$team_2];
        $select_fields = "tcmp.status, tct.name as team_name,tcmp.id, match_unique_id,player_unique_id,team_id ,tcp.name,GROUP_CONCAT(`tcpg`.`file_name` ORDER BY `tcpg`.`player_id`) as file_name, tcmp.image";
        $joins = [];
        $joins[1] = ['table'=>"{$this->tbl_cricket_players} tcp", 'condition'=>"tcmp.player_unique_id = tcp.uniqueid",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_cricket_player_galleries} tcpg", 'condition'=>"tcp.id = tcpg.player_id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_teams} tct", 'condition'=>"tcmp.team_id = tct.id",'jointype'=>'left'];

        $group_by = "tcmp.id ";
        $order_by = array(
            'as_table' => 'tcmp',
            'field' => 'id',
            'type' => 'ASC',
        );
        $teamGet1 = $this->main_model->cruid_select_array($table, $select_fields, $joins, $cond ,$group_by,$order_by);
        $get_player['squad'][0]['players'] = $teamGet1;
        $get_player['squad'][0]['name']= $teamGet1[0]['team_name'];
        $teamGet2 = $this->main_model->cruid_select_array($table, $select_fields, $joins, $cond2 ,$group_by,$order_by);
        $get_player['squad'][1]['players'] = $teamGet2;
        $get_player['squad'][1]['name']= $teamGet2[0]['team_name'];

        /**********Player Add********************************/

            if(isset($get_player['squad']) && array_key_exists("squad",$get_player)){
                $this->load->view($this->prefixUrl.'ajax_completed_players', $get_player);
            }else{
                echo "not found!";
            }
        }
    }
    
    public function get_current_playes(){
        $this->checkUser();
        $unique_id = $this->uri->segment(4);//$this->input->post('unique_id');
        
        if (empty($unique_id)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
        /**********Player Add********************************/
            $get_player_curl             = $this->curl_get_method(CRICAPI_MATCHE_PLAYER."&unique_id=".$unique_id);
            $get_player                  = json_decode($get_player_curl);
            
            if(isset($get_player->squad) && array_key_exists("squad",$get_player)){
                $get_player->match_unique_id = $unique_id;
                $get_player->prefixUrl       = 'admin/match_players/';
                $get_player->table           = $this->table;
                $get_player->name            = $this->name;
                $get_player->names           = $this->names;
        
                $this->load->view($this->prefixUrl.'third_rec_fetch', $get_player);
            }else{
                 echo '<p style="margin-top: 110px;">Third party api not found players!</p>';
            }
        }
    }   
    
    // add new user
    public function view_price_pool() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();
        
        $unique_id = json_decode($this->input->post('datapost'));
        //print_r($unique_id);die;
        /**************************************************/
        $get_player['prefixUrl'] = 'admin/matchs/';
        $get_player['table'] = $this->table;
        $get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_id'] = $unique_id;
        

        $url = $this->input->get("return");

        $get_player['contents']=(array)$unique_id;
        // echo "<pre>"; print_r( $get_player );die();

       if(!empty($unique_id)) {
            $this->load->view($this->prefixUrl.'price_poll_model', $get_player);
        } else {
            
            echo ('Please select atleast one Admin');
        }
    }

    public function formatDatepickerToMySql($date) {
        if ($date != FALSE) {
            $dateArr = explode("-", $date);
            
            $years = explode(" ",$dateArr[2]);
            $year = current($years);unset($years[0]);
            $newDate = $year . '-' . $dateArr[1] . '-' . $dateArr[0] . ' ' . implode(" ",$years);
            
            return $newDate;
        }
        return FALSE;
    }

        
    public function contest_matches_completed() {
        $match_id = $this->uri->segment(4);

        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("View Contest IN {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $tblccc = $this->tbl_cricket_contest_categories;

        if($this->input->get('v') == 'private'){
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'Y' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N' AND is_private = 'Y')";
        }else if($this->input->get('v') == 'beat_the_expert'){
            $cond_For_ccc = "is_deleted = 'N' AND is_beat_the_expert = 'Y' AND is_private = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N' AND is_beat_the_expert = 'Y')";
        }else{
            $cond_For_ccc = "is_deleted = 'N' AND is_beat_the_expert = 'N' AND is_private = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N' AND is_beat_the_expert = 'N' AND is_private = 'N')";
        }

        $cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND is_deleted='N'";
        $select_fields = "$tblccc.*";
        $CMselect_fields = "GROUP_CONCAT(`contest_id`) as contest_id";
        $group_by = "$tbl_contest_matches.match_id ";

        $joins = [];
        
        $order_by = array(
            'field' => 'id',
            'type' => 'ASC',
        );
        $contents = $this->main_model->cruid_select_array($tblccc, $select_fields, $joins, $cond_For_ccc,"",$order_by);
        //echo $this->db->last_query(); exit;
        //dd($contents,1);

        $match_cond = "id ='$match_id'";
        $Get_match = $this->main_model->cruid_select($this->table, "id", [], $match_cond);

        if ($Get_match) {
            $data['id'] = $match_id;
            $data['contents'] = $contents;
            $data['title'] = "Add Contest {$this->name} Details";
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;
            $data['prefixUrl'] = $this->prefixUrl;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            $data['sort_type'] = "";
            $data['field'] = "";
            $data['current_url'] = base_url() . $this->prefixUrl."index/";
            $data['records'][] = (object)$this->ajax_index(0,0,$match_id);
            $this->template->write_view('contents', $this->prefixUrl.'contest_matches_completed', $data);
                $this->template->render();
            }
    }
        
        
    public function get_player_statistics(){
        $this->checkUser();
        $unique_data = $this->input->post('postjson');
        
        /**************************************************/
        $table  = "tbl_cricket_match_players_stats";
        $get_player['prefixUrl'] = 'admin/match_players/';
        $get_player['table'] = $table;
        $get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_data'] = $unique_data;
         
       if (empty($unique_data)) {
           echo'Please select atleast one ';
          die;
        } else {
            $match_unique_id = $unique_data['unique_id'];
            $player_unique_id = $unique_data['player_unique_id'];

        /*************/
            $tcmps = $table;
            $select_fields = "{$tcmps}.*";
            $joins = [];
            $cond = "player_unique_id = $player_unique_id AND match_unique_id = $match_unique_id";
            $group_by = "{$tcmps}.id ";
            $order_by = array(
                'field' => 'id',
                'type' => 'DESC',
            );
            $contents_cat_take =$this->main_model->cruid_select_array($tcmps,$select_fields,$joins,$cond,$group_by,$order_by);
            //echo print_r($contents_cat_take);die;
        /*************/

        
                $get_player["breckup_points"] = $contents_cat_take;
                $this->load->view($this->prefixUrl.'ajax_player_statistics', $get_player);
            }
    }

    
    public function format_number($number){
        return str_replace(',', '', number_format($number, 2));
    }
    
    /***************************Customer Sections**************************************************/
    public function ajax_customers($offset = 0) {
        $tccc = $this->tccc;
        if ($this->input->post('action')) {
            $this->action();
        }

        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_customers";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/customers/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->ctable}.country",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->ctable}.state",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$tccc} tccc", 'condition'=>"tccc.customer_id = {$this->ctable}.id",'jointype'=>'left'];
        $joins[4] = ['table'=>"{$this->tbl_cricket_contest_matches} tccm", 'condition'=>"tccm.id = tccc.match_contest_id",'jointype'=>'left'];

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
        $table = $this->ctable;
        $group_by = "$table.id";

        $condit = "{$this->ctable}.is_deleted = 'N'";
        
        $select_fields = ",country.name as countryName, state.name as stateName,count( DISTINCT( tccc.match_contest_id ) ) as customer_contests,( SELECT count(DISTINCT(series_id)) FROM `tbl_cricket_matches` where unique_id IN ( SELECT match_unique_id FROM `tbl_cricket_customer_contests` where customer_id=tbl_customers.id ) ) as played_series_counts, count( DISTINCT( tccm.match_unique_id ) ) as played_match_counts,  sum(tccm.entry_fees) as spendamount,sum(tccc.win_amount) as winamount,  sum(tccc.refund_amount) as refund_amount,  sum(tccc.tax_amount) as tax_amount,  (sum(tccm.entry_fees)-sum(tccc.win_amount) - sum(tccc.refund_amount)) as earnings ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = [];
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                }


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$table}`.`created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$table}`.`created` <=". strtotime($search."23:59:59");
                }
                if ($val['name'] == 'earnings' and $val['value']) {
                    //$condit .= "  AND `earnings` <>". $search;
                }
            }
        }
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."customers/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_customers";

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
        $data['table'] = $this->ctable;
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'export_customers_matches_table_view', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_customers', $data);
        }
    }

    public function customers($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->cnames}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("{$this->cnames} List", site_url('section'));

        ob_start();
            $this->ajax_customers($offset);
            $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->cnames} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_customers";
        $data['this_url'] = base_url() . $this->prefixUrl."customers";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'customers', $data);
        $this->template->render();
    }
    /*****************************************************************************/
    
    // Customer matches listing
    
    
    public function customers_set($id){

        // Create session array
        $sess_array = array(
            'customer_id' => $id
        );
        echo json_encode($sess_array);
        // Add user value in session
        $this->session->set_userdata('customer_id', $sess_array['customer_id']);
    }
    
    public function ajax_customers_matches($offset = 0,$from_contest_Detail = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $customer_id  = $this->session->userdata('customer_id');
        if($customer_id == "")
        {
            $customer_id=0;
        }
        
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_customers_matches";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/customers_matches/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();

        $joins[1] = ['table'=>"{$this->tbl_cricket_series} series", 'condition'=>"{$this->table}.series_id=series.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_games} game", 'condition'=>"{$this->table}.game_id = game.id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_game_types} game_type", 'condition'=>"{$this->table}.game_type_id = game_type.id",'jointype'=>'left'];
        $joins[4] = ['table'=>"$this->tbl_teams tbl_teams1", 'condition'=>"{$this->table}.team_1_id = tbl_teams1.id",'jointype'=>'left'];
        $joins[5] = ['table'=>"$this->tbl_teams tbl_teams2", 'condition'=>"{$this->table}.team_2_id = tbl_teams2.id",'jointype'=>'left'];
        $joins[6] = ['table'=>"tbl_cricket_customer_contests tccc", 'condition'=>"{$this->table}.unique_id = tccc.match_unique_id AND tccc.customer_id = $customer_id",'jointype'=>'left'];
        $joins[7] = ['table'=>"tbl_cricket_contest_matches tccm", 'condition'=>"tccm.id = tccc.match_contest_id",'jointype'=>'join'];


        $group_by = "tccc.match_unique_id";

        $order_by = array(
            'field' => 'match_date',
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
                'field' => 'match_date',
                'type' => 'desc',
            );
        }
        $table = $this->table;
    
        $condit = "{$this->table}.is_deleted ='N' AND tbl_cricket_matches.unique_id IN (SELECT match_unique_id FROM `tbl_cricket_customer_contests` where customer_id=".$customer_id." GROUP BY match_unique_id) AND tccc.customer_id = $customer_id"; //{$this->table}.created_by = '".$this->session->userdata('adminId')."' AND
        $select_fields = ",series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name,COUNT(DISTINCT(tccc.match_contest_id)) AS  contest_count,count(tccc.match_unique_id) as joined_total_teams ,SUM(tccc.entry_fees) as spendamount, SUM(tccc.refund_amount) as refund_amount, SUM(tccc.`win_amount`) AS winamount, SUM(tccc.`tax_amount`) AS tax_amount,(SUM(tccc.entry_fees) - SUM(tccc.`win_amount`) - SUM(tccc.refund_amount)) AS earnings ";

        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "{$this->table}.`unique_id` LIKE '%" . $keyword . "%' OR {$this->table}.`name` LIKE '%" . $keyword . "%' OR series.`name` LIKE '%" . $keyword . "%' OR game.`name` LIKE '%" . $keyword . "%' OR game_type.`name` LIKE '%" . $keyword . "%' OR tbl_teams1.`name` LIKE '%" . $keyword . "%' OR tbl_teams2.`name` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'series_id' and $val['value']) {
                    $condit .= "  AND `series_id` ='". $search."'";
                }
                if ($val['name'] == 'match_progress' and $val['value']) {
                    $condit .= "  AND `match_progress` ='". $search."'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `created` <=". strtotime($search."23:59:59");
                }
            }
        }
        /*********************/
        if($from_contest_Detail >0 ){
            $cond = "{$table}.id ='" .  $from_contest_Detail . "'";
            $user_detail = $this->main_model->cruid_select($table, "{$table}.*" . $select_fields, $joins, $cond);
            return $user_detail;
        }
        /*********************/
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, $group_by, $order_by_other);
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

        $data['current_url'] = base_url() . $this->prefixUrl."customers_matches/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_customers_matches";

        // calculate sort type
        $order = "";
        if ($this->input->get('sort') == 'asc') {
            $order = "desc";
        }
        if ($this->input->get('sort') == 'desc') {
            $order = "asc";
        }
        $data['sort_type']  = $order;
        $data['field']      = $this->input->get('field');
        $data['prefixUrl']  = $this->prefixUrl;
        $data['table']      = $this->table;
        $data['name']       = $this->name." report";
        $data['names']      = $this->names." Reports";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;

        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'export_matches_table_view', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_customers_matches', $data);
        }
        
    }

    public function customers_matches($offset = 0) {
        
        $this->loginCheck($this->prefixUrl.'index');
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Customer {$this->names} Report", site_url($this->prefixUrl."customers"), true);
        $this->breadcrumbs->push("Matches List", site_url('section'));

        ob_start();
        $this->ajax_customers_matches($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = $this->name." Report";
        $data['names'] = $this->names." Reports";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_customers_matches";
        $data['this_url'] = base_url() . $this->prefixUrl."customers_matches";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }
        
    public function customer_contest_matches() {
        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("View Contest IN {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $tblccc = $this->tbl_cricket_contest_categories;
        
        $customer_id  = $this->session->userdata('customer_id');
            if($customer_id == "")
            {
                $customer_id=0;
            }

        if($this->input->get('v') == 'private'){
            $cond_For_ccc = "`is_deleted` = 'N' AND is_private = 'Y'  AND is_beat_the_expert = 'N' AND FIND_IN_SET(id,(SELECT GROUP_CONCAT(`category_id`) from tbl_cricket_contest_matches WHERE is_private = 'Y'  AND is_beat_the_expert = 'N' AND FIND_IN_SET (id,(SELECT GROUP_CONCAT(`match_contest_id`) as ids FROM `tbl_cricket_customer_contests` WHERE `customer_id` =$customer_id AND match_unique_id = (SELECT unique_id FROM tbl_cricket_matches WHERE id=$match_id ) ) ) ) )";    
        }else if($this->input->get('v') == 'beat_the_expert'){
            $cond_For_ccc = "`is_deleted` = 'N' AND is_private = 'N' AND is_beat_the_expert = 'Y' AND FIND_IN_SET(id,(SELECT GROUP_CONCAT(`category_id`) from tbl_cricket_contest_matches WHERE is_private = 'N' AND is_beat_the_expert = 'Y' AND FIND_IN_SET (id,(SELECT GROUP_CONCAT(`match_contest_id`) as ids FROM `tbl_cricket_customer_contests` WHERE `customer_id` =$customer_id AND match_unique_id = (SELECT unique_id FROM tbl_cricket_matches WHERE id=$match_id ) ) ) ) )";
        }else{
            $cond_For_ccc = "`is_deleted` = 'N' AND is_private = 'N'  AND is_beat_the_expert = 'N' AND FIND_IN_SET(id,(SELECT GROUP_CONCAT(`category_id`) from tbl_cricket_contest_matches WHERE is_private = 'N'  AND is_beat_the_expert = 'N' AND FIND_IN_SET (id,(SELECT GROUP_CONCAT(`match_contest_id`) as ids FROM `tbl_cricket_customer_contests` WHERE `customer_id` =$customer_id AND match_unique_id = (SELECT unique_id FROM tbl_cricket_matches WHERE id=$match_id ) ) ) ) )";
        }

        $cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND is_deleted='N'";
        $select_fields = "$tblccc.*";
        $CMselect_fields = "GROUP_CONCAT(`contest_id`) as contest_id";
        $group_by = "$tbl_contest_matches.match_id ";

        $joins = [];
        
        $order_by = array(
            'field' => 'id',
            'type' => 'ASC',
        );
        $contents = $this->main_model->cruid_select_array($tblccc, $select_fields, $joins, $cond_For_ccc,"",$order_by);
        //echo $this->db->last_query(); exit;
        //dd($contents,1);

        $match_cond = "id ='$match_id'";
        $Get_match = $this->main_model->cruid_select($this->table, "id", [], $match_cond);

        if ($Get_match) {
            $data['id'] = $match_id;
            $data['contents'] = $contents;
            $data['title'] = "Add Contest {$this->name} Details";
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;
            $data['prefixUrl'] = $this->prefixUrl;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            $data['sort_type'] = "";
            $data['field'] = "";
            $data['current_url'] = base_url() . $this->prefixUrl."index/";
            $data['records'][] = (object)$this->ajax_customers_matches(0,$match_id);
            $this->template->write_view('contents', $this->prefixUrl.'customer_contest_matches', $data);
                $this->template->render();
            }
    }
        
   
    //Earninig Report 
    
    public function ajax_index_earnings($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $group_by = "tccc.match_unique_id";

        $order_by = array(
            'field' => 'match_date',
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
                'field' => 'match_date',
                'type' => 'desc',
            );
        }
        $table = $this->table;
        
        $condit = "tcm.is_deleted= 'N'"; //tcm.created_by = '".$this->session->userdata('adminId')."' AND 
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                        $str[] = "{$this->table}.`unique_id` LIKE '%" . $keyword . "%' OR {$this->table}.`name` LIKE '%" . $keyword . "%' OR series.`name` LIKE '%" . $keyword . "%' OR game.`name` LIKE '%" . $keyword . "%' OR game_type.`name` LIKE '%" . $keyword . "%' OR tbl_teams1.`name` LIKE '%" . $keyword . "%' OR tbl_teams2.`name` LIKE '%" . $keyword . "%'";
                    }
                }
                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'series_id' and $val['value']) {
                    $condit .= "  AND `series_id` ='". $search."'";
                }
                if ($val['name'] == 'match_progress' and $val['value']) {
                    $condit .= "  AND `match_progress` ='". $search."'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `match_date` <=". strtotime($search."23:59:59");
                }
            }
        }else{
            $SevenDay = strtotime("-7 day");
            $toDay    = time();
            $condit .= "  AND `match_date` >=". $SevenDay;
            $condit .= "  AND `match_date` <=". $toDay;
        }
        /***************/
        $where =" WHERE $condit ";
        
        /***************/
        $query = $this->db->query("
                                    SELECT   tbl_m.match_date as unxtime_match_date,
                                      GROUP_CONCAT(tbl_m.unique_id) as matchesids,
                                      tbl_m.group_match_date,
                                                SUM(A.amount) as spendamount,
                                                SUM(tbl_m.total_win) as final_win,
                                      SUM(tbl_m.refund_amount) as refund_amounts,
                                      SUM(tbl_m.tax_amount) as tax_amounts,
                                                SUM(A.amount) - sum(tbl_m.total_win) - sum(tbl_m.refund_amount) as earnings,
                                      COUNT(tbl_m.unique_id) as total_matches
                                    FROM 
                                    (
                                    Select   tcm.match_date,
                                      tcm.unique_id,
                                      FROM_UNIXTIME(tcm.match_date, '".DATE_FORMAT_ADMIN_SQL."') as group_match_date,
                                      sum(tccc.win_amount) as total_win,
                                      sum(tccc.refund_amount) as refund_amount,
                                      sum(tccc.tax_amount) as tax_amount,
                                                tccc.match_contest_id
                                    FROM   tbl_cricket_matches tcm
                                    inner JOIN tbl_cricket_customer_contests tccc on tccc.match_unique_id =tcm.unique_id 
                                    $where  
                                    GROUP by  match_unique_id
                                    )tbl_m
                                    LEFT JOIN (
                                    SELECT SUM(tccm.entry_fees) as amount ,tccc.match_unique_id 
                                    FROM tbl_cricket_customer_contests tccc 
                                    JOIN tbl_cricket_contest_matches tccm ON tccm.id = tccc.match_contest_id 
                                    
                                    GROUP by  tccc.match_unique_id 
                                    )A on A.match_unique_id =tbl_m.unique_id 
                                    group BY group_match_date 
                                    ORDER BY tbl_m.group_match_date ASC
                                    ");
        $matches = $query->result_array();  
          
        /***************/
        $data['records']  = $matches;
        $data['field']      = $this->input->get('field');
        $data['prefixUrl']  = $this->prefixUrl;
        $data['table']      = $this->table;
        $data['name']       = "Earnings report";
        $data['names']      = "Earnings Reports";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['export'] = "no";

        if($this->input->post('from_veiw')=="dashboard"){
                $jsondata = array();
                $total_matches = array();
                $total_cr = array();
                $total_dr = array();
                $total_ra = array();
                $total_ta = array();
                $total_earnings = array();

                foreach ($data['records'] as $row) { 
                                          
                     
                     $spendamount    = $row['spendamount'];                                        
                     $winamountTotal = $row['final_win'];
                     $earning        = $row['earnings'];
                     $refund_amounts = $row['refund_amounts'];
                     $tax_amounts    = $row['tax_amounts'];
                    
                     $jsondata[] = array('date'     => $row['unxtime_match_date'],
                                        'Credited'  => $spendamount,
                                        'Debited'   => $winamountTotal,
                                        'Earnings'  => $earning,
                                        'Refund_amounts' => $refund_amounts,
                                        'Tax_amounts' => $tax_amounts,
                                    );

                    
                    $total_matches[]    = $row['total_matches'];
                    $total_cr[]         = $spendamount;
                    $total_dr[]         = $winamountTotal;
                    $total_ra[]         = $refund_amounts;
                    $total_ta[]         = $tax_amounts;
                    $total_earnings[]   = $earning;
                }
                $endofdata['totals'] = ["total_matches"=>array_sum($total_matches),
                                        "total_cr"=>number_format(array_sum($total_cr),2),
                                        "total_dr"=>number_format(array_sum($total_dr),2),
                                        "total_ra"=>number_format(array_sum($total_ra),2),
                                        "total_ta"=>number_format(array_sum($total_ta),2),
                                        "earnings"=>number_format(array_sum($total_earnings),2),
                                    ];
                $endofdata['data'] = $jsondata;
                header("Content-type:application/json");
                print_r(json_encode($endofdata));die;
            
        }elseif ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'ajax_index_earnings', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_index_earnings', $data);
        }
       
    }

    public function earnings($offset = 0) {
        $this->loginCheck($this->prefixUrl.'earnings');
 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Earnings Report", site_url($this->prefixUrl."earnings"));
        $this->breadcrumbs->push("Earings", site_url('section'));

       /** ob_start();
        $this->ajax_index_earnings($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();**/
        $data['name'] = "Earning Report";
        $data['names'] = "Earnings Report";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = "";
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index_earnings";
        $data['this_url'] = base_url() . $this->prefixUrl."earnings";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'earnings', $data);
        $this->template->render();
    }

   // From earning report to view match tha set match ids in session
   
   
    public function from_eraning_match_set($id){

        // Create session array
        $sess_array = array(
            'from_eraning_match_id' => $id
        );
        echo json_encode($sess_array);
        // Add user value in session
        $this->session->set_userdata('from_eraning_match_id', $sess_array['from_eraning_match_id']);
    }
    
    public function from_earning_matches($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');
 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names} Report", site_url($this->prefixUrl."earnings"));
        $this->breadcrumbs->push("Matches List", site_url('section'));
        
        $customer_id  = str_ireplace("-",",",$this->session->userdata('from_eraning_match_id'));
            if($customer_id == "")
            {
                $customer_id=0;
            }
            
        ob_start();
        $this->ajax_index($offset,$customer_id);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = $this->name." Report";
        $data['names'] = $this->names." Reports";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index";
        $data['this_url'] = base_url() . $this->prefixUrl."index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }
    
    // Approvel pending customer documents 

    public  function ajax_pending_withdrawals($offset = 0) {
        $twr = $this->tbl_withdraw_requests;
        if ($this->input->post('action')) {
            $this->action();
        }
        if( $this->input->post('filter') =='filtered' && $this->input->post('action_export') =='export' ){
            $this->limit = EXCEL_LIMIT;
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_pending_withdrawals";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/withdrawals/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
        $joins[1] = ['table'=>"{$this->ctable}", 'condition'=>"{$twr}.customer_id = {$this->ctable}.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->country} country", 'condition'=>"country.id = {$this->ctable}.country",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->state} state", 'condition'=>"state.id = {$this->ctable}.state",'jointype'=>'left'];
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
        $condit = "{$this->ctable}.status IN ('A','D') AND {$twr}.status!='P' ";
        
        $select_fields = " ,firstname,lastname,email,phone,country_mobile_code,,country.name as countryName, state.name as stateName ";
        
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    if ($val['name'] == 'search' and $val['value']) {
                    $str[] = "`team_name` LIKE '%" . $keyword . "%' OR `phone` LIKE '%" . $keyword . "%' OR `firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                    }
                }


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'state_id' and $val['value']) {
                    $condit .= "  AND `state` ='". $search . "'";
                }
                if ($val['name'] == 'withrawals_status' and $val['value']) {
                    $condit .= "  AND {$twr}.status='". $search . "'";
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$twr}`.`created_at` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `{$twr}`.`created_at` <=". strtotime($search."23:59:59");
                }
            }
        }
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $twr, $select_fields, $condit, "", $order_by_other);
    
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

        $data['current_url'] = base_url() . $this->prefixUrl."withdrawals/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_pending_withdrawals";

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
        $data['table'] = $this->ctable;
        
        $data['export'] = "no";
        if ($this->input->post('action_export') =='export' && $data['records']) {
            $data['export'] = "yes";
            $var = $this->load->view($this->prefixUrl.'ajax_pending_withdrawals', $data,true);
            $file = date("M_d_Y") . "_".$this->names.".xls";
            header("Content-type: application/x-msdownload");
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Pragma: no-cache");
            header("Expires: 0");
            file_put_contents(EXCEL_PATH.$file,$var);
            //header("Location: ".EXCEL_URL.$file);
            echo EXCEL_URL.$file;
            die;
            exit();
        }else{
            $this->load->view($this->prefixUrl.'ajax_pending_withdrawals', $data);
        }
    }

    public function withdrawals($offset = 0) { 
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Withdrawals Report List", site_url('section'));

        ob_start();
        $this->ajax_pending_withdrawals($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_pending_withdrawals";
        $data['this_url'] = base_url() . $this->prefixUrl."withdrawals";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'pending_withdrawals', $data);
        $this->template->render();
    }

    public function customers_setForNotifiEmails(){

        // Create session array
        $sess_array = array(
            'notifi_ids' => $this->input->post('ids')
        );
        $type  = $this->input->post('type');
        echo base_url("admin/$type/add");
        // Add user value in session
        $this->session->set_userdata('notifi_ids', $sess_array['notifi_ids']);
       // echo json_encode($sess_array);
    }
    //End of Matches class  
}
