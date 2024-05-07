<?php
require_once('base.php');
class Matches extends Base {

    private $limit = 20;
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
    private $prefixUrl = 'admin/matches/';
    private $name = 'Match'; // For singular
    private $names = 'Matches'; //plural form 

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

    // Upcoming matches listing
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
        $joins[1] = ['table'=>"{$this->tbl_cricket_series} series", 'condition'=>"{$this->table}.series_id=series.id",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_games} game", 'condition'=>"{$this->table}.game_id = game.id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_game_types} game_type", 'condition'=>"{$this->table}.game_type_id = game_type.id",'jointype'=>'left'];
        $joins[4] = ['table'=>"$this->tbl_teams tbl_teams1", 'condition'=>"{$this->table}.team_1_id = tbl_teams1.id",'jointype'=>'left'];
        $joins[5] = ['table'=>"$this->tbl_teams tbl_teams2", 'condition'=>"{$this->table}.team_2_id = tbl_teams2.id",'jointype'=>'left'];
        $joins[6] = ['table'=>"tbl_cricket_team_player_galleries tctpg1", 'condition'=>"tbl_teams1.id = tctpg1.team_id",'jointype'=>'left'];
        $joins[7] = ['table'=>"tbl_cricket_team_player_galleries tctpg2", 'condition'=>"tbl_teams2.id = tctpg2.team_id",'jointype'=>'left'];
        
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
        $condit = "{$this->table}.is_deleted ='N' AND {$this->table}.match_progress='F'";
        $select_fields = ", series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams1.logo as team_1_image ,tbl_teams2.name as team_2_name ,tbl_teams2.logo as team_2_image ,tbl_teams1.sort_name as team_1_sort_name ,tbl_teams2.sort_name as team_2_sort_name ,GROUP_CONCAT(`tctpg1`.`file_name` ORDER BY `tctpg1`.`team_id`) as team_1_file_name ,GROUP_CONCAT(`tctpg2`.`file_name` ORDER BY `tctpg2`.`team_id`) as team_2_file_name";
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
                    $condit .= "  AND `series_id` =". $search;
                }
                
                 if ($val['name'] == 'game_id' and $val['value']) {
                    $condit .= "  AND tbl_cricket_matches.`game_id` =". $search;
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". time();
                //$condit .= "  AND `tbl_cricket_matches`.`match_date` <=". $this->session->userdata('to_date');

            }
        
        $rows = $this->main_model->tabel_list($this->limit, $this->uri->segment(4), $joins, $order_by, $table, $select_fields, $condit, "tbl_cricket_matches.id", $order_by_other);
    
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
        $data['name'] = "Upcoming";
        $data['names'] = "Upcoming";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        
        if($this->input->post('from_veiw')=="dashboard"){
            //print_r($data);die;
            $this->load->view('admin/home/upcomming_n_live_matches', $data);
        }else{
            $this->load->view($this->prefixUrl.'ajax_index', $data);
        }
    }

    public function index($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Upcoming List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = "Upcoming";
        $data['names'] = "Upcoming";
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

    //Live matches listing
    function ajax_live($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_live";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/live/";
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
        $condit = "{$this->table}.is_deleted ='N' AND {$this->table}.match_progress IN('L','IR')";
        $select_fields = ", series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name ,tbl_teams1.sort_name as team_1_sort_name ,tbl_teams2.sort_name as team_2_sort_name ";
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
                    $condit .= "  AND `series_id` =". $search;
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date') && $this->input->get('come')=="dash") {
                $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". $this->session->userdata('from_date');
                $condit .= "  AND `tbl_cricket_matches`.`match_date` <=". $this->session->userdata('to_date');
                
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

        $data['current_url'] = base_url() . $this->prefixUrl."live/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_live";

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
        $data['name'] = "Live";
        $data['names'] = "Live";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;

        if($this->input->post('from_veiw')=="dashboard"){
            //print_r($data);die;
            $this->load->view('admin/home/upcomming_n_live_matches', $data);
        }else{
            $this->load->view($this->prefixUrl.'ajax_live', $data);
        }
    }

    public function live($offset = 0) {
        $this->loginCheck($this->prefixUrl.'live');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Live List", site_url('section'));

        ob_start();
        $this->ajax_live($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = $this->name ." Live";
        $data['names'] = $this->names ." Live";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_live";
        $data['this_url'] = base_url() . $this->prefixUrl."live";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index_live', $data);
        $this->template->render();
    }

    //Compleated matches listing
    function ajax_completed($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_completed";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/completed/";
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
        $condit = "{$this->table}.is_deleted ='N'";
        
        if(isset($_GET['mp']) && ($_GET['mp'] =='r' || $_GET['mp']=='ab')){
            $condit .="AND `{$this->table}`.`match_progress`='".strtoupper($_GET['mp'])."'";
        }else{
            $condit .="AND {$this->table}.match_progress IN('R','AB')";
        }
        $select_fields = ", series.name as series_name ,game.name as game_name ,game_type.name as game_type_name ,tbl_teams1.name as team_1_name ,tbl_teams2.name as team_2_name ,tbl_teams1.sort_name as team_1_sort_name ,tbl_teams2.sort_name as team_2_sort_name ";
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
                    $condit .= "  AND `series_id` =". $search;
                }
                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $condit .= "  AND `tbl_cricket_matches`.`match_date` <=". strtotime($search."23:59:59");
                }
            }
        }elseif (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin/home') !== false && $this->session->userdata('from_date') && $this->session->userdata('to_date')) {
                $condit .= "  AND `tbl_cricket_matches`.`match_date` >=". $this->session->userdata('from_date');
                $condit .= "  AND `tbl_cricket_matches`.`match_date` <=". $this->session->userdata('to_date');
               
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

        $data['current_url'] = base_url() . $this->prefixUrl."completed/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . $this->prefixUrl."ajax_completed";

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
        $data['name'] = "Completed";
        $data['names'] = "Completed";
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        
        $this->load->view($this->prefixUrl.'ajax_completed', $data);
    }

    public function completed($offset = 0) {
        $this->loginCheck($this->prefixUrl.'completed');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl), false);
        $this->breadcrumbs->push("Completed List", site_url('section'));

        ob_start();
        $this->ajax_completed($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['name'] = "Completed";
        $data['names'] = "Completed";
        $data['table'] = $this->table;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        $data['title'] = "Completed List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_completed";
        $data['this_url'] = base_url() . $this->prefixUrl."completed";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index_completed', $data);
        $this->template->render();
    }

    // add new user
    public function add($method = "add") {
        $this->loginCheck($this->prefixUrl.$method);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add {$this->name}", site_url('section'));

        $redirect = $this->prefixUrl.'index';
        $data['title'] = "Add New {$this->name}";
        $data['name'] = $this->name;
        $data['names'] = $this->names;
        $data['table'] = $this->table;
        $data['prefixUrl'] = $this->prefixUrl;
        $data['tbl_cricket_series'] = $this->tbl_cricket_series;
        
        $commonInsert= array(
                'status'     => 'A',
                'created_by' => $this->session->userdata('adminId'),
                'updated_by' => $this->session->userdata('adminId'),
                'created_at' => time(),               
                'updated_at' => time(),               
            );
        if($this->input->post('matche_data') && count($_POST)>0){
            $matche_data     = (array)json_decode($this->input->post('matche_data'));
            $match_date      = strtotime($matche_data['dateTimeGMT']);
            // $close_date   = (strtotime($matche_data['dateTimeGMT'])-300);
            $close_date      = strtotime($matche_data['dateTimeGMT']);
            $team_1          = $matche_data['team-1'];
            $team_1short_name= $matche_data['team-1-short_name'];
            $team_2          = $matche_data['team-2'];
            $team_2short_name= $matche_data['team-2-short_name'];
            $unique_id       = $matche_data['unique_id'];
            $match_name      = $matche_data['title']; 
            $short_title     = $matche_data['short_title'];
            $subtitle        = $matche_data['subtitle'];
            $game_type       = $matche_data['type'];
            $game            = 'Cricket';
            
            /********************/
            $series_data        = $matche_data['series_data']; 
            $series_name        = $series_data->title;
            $series_unique_id   = $series_data->unique_id;
            $series_abbr        = $series_data->abbr;
            $series_type        = $series_data->type;
            $series_season      = $series_data->season;

            $series_data_in = ['name' => $series_name, 'abbr' => $series_abbr, 'type' => $series_type, 'season' => $series_season, 'uniqueid'=> $series_unique_id ];     

            $series_id = $this->main_model->insert_or_id_get('tbl_cricket_series',['uniqueid'=>$series_unique_id], array_merge($series_data_in,$commonInsert));

            /********************/

            $team_1_data = ['name' => $team_1, "sort_name"=>$team_1short_name];             
            $team_1_id = $this->main_model->insert_or_id_get('tbl_cricket_teams',['name'=>$team_1], array_merge($team_1_data,$commonInsert));
        
            $team_2_data = ['name' => $team_2, "sort_name"=>$team_2short_name];             
            $team_2_id = $this->main_model->insert_or_id_get('tbl_cricket_teams',['name'=>$team_2], array_merge($team_2_data,$commonInsert));

            $game_data = ['name' => $game];             
            $game_id = 0; //$this->main_model->insert_or_id_get('tbl_games',['name'=>$game], array_merge($game_data,$commonInsert));
        
            $game_type_data = ['name' => $game_type,'game_id' => $game_id];
            $game_type_id = $this->main_model->insert_or_id_get('tbl_game_types',['name'=>$game_type], array_merge($game_type_data,$commonInsert));
        
        
        
            $_POST['unique_id'] = $unique_id;
            $_POST['series_id'] = $series_id;


        }
        
            $this->form_validation->set_rules('unique_id', 'Match Name', "trim|required|is_unique[{$this->table}.unique_id]");
            $this->form_validation->set_message('is_unique', "This {$this->name} is already taken in " . SITE_TITLE . ". Please try different"); 
            $this->form_validation->set_rules('match_limit', 'Match Limit', "trim|required");
            $this->form_validation->set_rules('series_id', 'Series Name', "trim|required");

        $data['added_ids'] = [];
        if ($this->form_validation->run() == FALSE) {
            $upcoming = $this->db->query("SELECT `unique_id` FROM `tbl_cricket_matches` WHERE `match_progress` = 'F' AND `is_deleted` = 'N'")->result_array();
            $added_ids = array_column($upcoming, 'unique_id');
            $data['added_ids'] = $added_ids;
            $this->template->write_view('contents', $this->prefixUrl.$method, $data);
            $this->template->render();
        } else {
    
            $dataInasert = array(
                'match_date'     => $match_date,
                'close_date'     => $close_date,
                'name'           => $match_name,
                'short_title'    => $short_title,
                'subtitle'       => $subtitle,
                'game_id'        => $game_id,
                'game_type_id'   => $game_type_id,
                'team_1_id'      => $team_1_id,
                'team_2_id'      => $team_2_id,
                'unique_id'      => $unique_id,
                'series_id'      => $this->input->post('series_id'),
                'match_limit'        => $this->input->post('match_limit'),
                'status'         => 'D',
                'created_by'     => $this->session->userdata('adminId'),
                'updated_by'     => $this->session->userdata('adminId'),
                'created_at'     => time(),               
                'updated_at'     => time(),               
            );
            /**********/
            if(!empty($_FILES['image']['name'])){
                //validating files first
                if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                 if (!is_dir(MATCH_IMAGE_LARGE_PATH)) {
                    mkdir(MATCH_IMAGE_LARGE_PATH, 0777, true);
                 }if (!is_dir(MATCH_IMAGE_THUMB_PATH)) {
                    mkdir(MATCH_IMAGE_THUMB_PATH, 0777, true);
                 } 
                } 
                $config['upload_path']          = MATCH_IMAGE_LARGE_PATH;
                $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                $config['max_size']             = ALLOWED_FILE_SIZE;
                    /*************************************************************************/
                    $redirectimg = $this->prefixUrl.$method;
                        $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                        $width      = $fileinfo[0];
                        $height     = $fileinfo[1];
                        $ratio      = $height/$width;
                        $ratio      = round($ratio,2);
                        
                       // Validate image file dimension
                       if($width > "800" || $height > "328"){
                            $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                            $this->template->write_view('contents', $this->prefixUrl.$method, $data);
                            $this->template->render();
                            $this->session->set_userdata('message', "<p>Image dimension should be within 800 X 328.</p>");
                            redirect($redirectimg);
                           
                       }else if($ratio!="0.41" && $ratio!="0.42"){
                            $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                            $this->template->write_view('contents', $this->prefixUrl.$method, $data);
                            $this->template->render();
                            $this->session->set_userdata('message', "<p>Image dimension should be within 800 X 328.</p>");
                            redirect($redirectimg);
                       }
                    /*************************************************************************/

                //changing file name for selected
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $config['file_name']  = rand()."_".time()."_context_cat.$ext";
                $file_upload = $this->upload_files($config, "image");
                

                if($file_upload['status']=="success"){
                    //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                    $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], MATCH_IMAGE_LARGE_PATH,MATCH_IMAGE_THUMB_PATH);
                    $dataInasert['image'] = $file_upload['data']['file_name'];
                }
                else{
                    if(!empty($file_upload['data'])){
                        $data['validation_errors'] = $file_upload['data'];
                    }else{
                        $data['validation_errors'] = "<p>There was an error while uploading image.</p>";
                    }                   
                    $this->template->write_view('contents', $this->prefixUrl.$method, $data);
                    $this->template->render();
                    $imageError = 'Y';
                }
            }
            /**********/
            $table = $this->table;
            $business_id = $this->main_model->cruid_insert($table, $dataInasert);
            $this->db->query("UPDATE `tbl_games` SET `new_match_count` = new_match_count-1 WHERE `tbl_games`.`id` = 0 ");
            /**********Player Add********************************/
            //$get_player_curl = $this->curl_get_method(CRICAPI_MATCHE_PLAYER."&unique_id=".$unique_id);
            // $get_player_curl = $this->curl_get_method(ENTITYSPORT_MATCHE_PLAYERS.$unique_id);
            $get_player_curl = $this->curl_get_method(ENTITYSPORT_MATCHE_PLAYERS.$series_unique_id."/".$unique_id);
            $get_player = json_decode($get_player_curl);
   
            if(array_key_exists("squad",$get_player)){

                if( $get_player->squad[0]->name != $team_1 ){
                    $reversed = array_reverse($get_player->squad);
                    $get_player->squad = $reversed;
                }

                $i=0;
                foreach($get_player->squad as $getTeam){
                    $team_id = $team_1_id;
                    if($i > 0){
                        $team_id      = $team_2_id;
                    }
                
                    // Remove duplicate https://itsolutionstuff.com/post/php-how-to-remove-duplicate-values-from-multidimensional-arrayexample.html
                    $unique_players = array_map('unserialize', array_unique(array_map('serialize',$getTeam->players)));
                    foreach( $unique_players as $player ){
                        $detail_data = [];
                        if( isset($player->detail_data) && !empty($player->detail_data) ){
                            $player_detail = $player->detail_data;

                            $country_name = $player_detail->country;
                            $country_id = $this->main_model->insert_or_id_get('tbl_countries',['name'=>$country_name], array_merge(['name'=>$country_name],$commonInsert));

                            $detail_data = ["summary"=>json_encode($player_detail), "short_name"=>$player_detail->short_name,"bets"=>$player_detail->battingStyle,"bowls"=>$player_detail->bowlingStyle,"position"=>$player_detail->playingRole,"country_id"=>$country_id];
                        }

                        $player_data = array_merge(['name' => $player->name,'uniqueid'=>$player->pid],$detail_data);

                        $our_db_p_unique_id = $this->main_model->insert_or_id_get($this->tbl_cricket_players,['uniqueid'=>$player->pid], array_merge($player_data,$commonInsert));
                            
                        if( $our_db_p_unique_id >0 ){
                            $match_players_data = [ "match_unique_id"=>$unique_id,"player_unique_id"=>$player->pid,"team_id"=>$team_id,"credits"=>$player->credits, "playing_role"=>$player_detail->playingRole ];
                            $match_players_id = $this->main_model->insert_or_id_get($this->tbl_cricket_match_players,$match_players_data, array_merge($match_players_data,$commonInsert));
                        }
                    }
                    $i++;
                }
            }
            /******************************************/
            
            $this->session->set_userdata('smessage', "{$this->name} Successfully added");
            redirect($redirect);
        }
    }

    public function add_direct(){
        $segment = $this->uri->segment(3);
        
        $this->add("add_direct");
    }
    // edit user detail
    public function edit() {
        
        $user_name = $this->uri->segment(4);
        $unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url     = $this->input->get("return");
        $failurl = base_url($this->prefixUrl.'edit/' . $user_name.'?return='.$url);

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
        
            $this->form_validation->set_rules('name', 'Name', "trim|required");
            $this->form_validation->set_message('is_unique_again', "This  {$this->name} is already taken in " . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                $this->template->render();
            } else {
                $credit_players = 0;
                $position_counts= 0;
                $slug           = $id;

                $queryP = $this->db->query("SELECT COUNT(*) exists_points , (SELECT game_type_id FROM `tbl_cricket_matches` WHERE `id` = '$slug') as game_type_id FROM `tbl_cricket_points` WHERE game_type_id= (SELECT game_type_id FROM `tbl_cricket_matches` WHERE `id` = '$slug') AND is_deleted='N' AND status ='A'");
                $rowP = $queryP->row();

                if (isset($rowP->exists_points) && $rowP->exists_points ==0)
                {
                   $this->session->set_userdata('message', "Please add points system .");//<script>window.location.replace('".HTTP_PATH."admin/cricket_points/add?gt=".$rowP->game_type_id."');</script>
                   redirect(HTTP_PATH."admin/cricket_points/add?gt=".$rowP->game_type_id);
                   
                }
                
                $query = $this->db->query("SELECT count(id) as credit_players FROM `tbl_cricket_match_players` WHERE `credits`=0 AND `match_unique_id`=(SELECT unique_id FROM tbl_cricket_matches WHERE id='$slug')");
                $row = $query->row();

                if (isset($row->credit_players) && $row->credit_players >0)
                {
                   $credit_players = $row->credit_players;
                   $this->session->set_userdata('message', "Some players({$row->credit_players}) don't have credit point, So please update credits point before than active match.");
                    redirect($url);
                }
                if (isset($row->credit_players) && $row->credit_players ==0 && $credit_players==0) {
                    $query2 = $this->db->query("SELECT COUNT(id) AS position_counts FROM tbl_cricket_players WHERE FIND_IN_SET(uniqueid, (SELECT GROUP_CONCAT(`player_unique_id`) player_unique_id FROM `tbl_cricket_match_players` WHERE `match_unique_id`=(SELECT unique_id FROM tbl_cricket_matches WHERE id='$slug' AND (position = ' ' OR position = '' OR position IS NULL ))))");
                    $row1 = $query2->row(); //echo $this->db->last_query();
                    if (isset($row1->position_counts) && $row1->position_counts >0)
                    {
                        $position_counts = $row1->position_counts;
                        $this->session->set_userdata('message', "Some players({$row1->position_counts}) don't have Playing Role, So please update Playing Role before than active match.");

                        redirect($url);
                    }
                }

                //$close_date   = date_create_from_format("d-m-Y h:i a",$this->input->post('close_date'));
                $close_date = strtotime( str_ireplace("/", "-", $this->input->post('close_date') ) );
                $match_date = strtotime( str_ireplace("/", "-", $this->input->post('match_date') ) );
                //echo $close_date;
                //die;
                $dataInasert = array(
                    'name'        => $this->input->post('name'),
                    'match_limit' => $this->input->post('match_limit'),
                    'highest_winning' => $this->input->post('highest_winning'),
                    'tag_category' => $this->input->post('tag_category'),
                    'match_progress' => $this->input->post('match_progress'),
                    'match_date'  => $match_date,
                    'close_date'  => $close_date,
                    'series_id'   => $this->input->post('series_id'),
                    'updated_by'  => $this->session->userdata('adminId'),
                    'updated_at'  => time(),               
                );
                
                if(isset($_POST['lineup_expected'])){
                    $dataInasert['lineup_expected'] = $this->input->post('lineup_expected');
                }else{
                    $dataInasert['lineup_expected'] = 0;
                }
                
                /**********/
                if(!empty($_FILES['image']['name'])){
                    //validating files first
                    if (IMAGE_UPLOAD_TYPE!="BUCKET") {
                     if (!is_dir(MATCH_IMAGE_LARGE_PATH)) {
                        mkdir(MATCH_IMAGE_LARGE_PATH, 0777, true);
                     }if (!is_dir(MATCH_IMAGE_THUMB_PATH)) {
                        mkdir(MATCH_IMAGE_THUMB_PATH, 0777, true);
                     } 
                    } 
                    $config['upload_path']          = MATCH_IMAGE_LARGE_PATH;
                    $config['allowed_types']        = ALLOWED_IMAGE_TYPES;
                    $config['max_size']             = ALLOWED_FILE_SIZE;
                        /*************************************************************************/
                           $redirectimg = current_url()."?return=$url";
                            $fileinfo   = @getimagesize($_FILES["image"]["tmp_name"]);
                            $width      = $fileinfo[0];
                            $height     = $fileinfo[1];
                            $ratio      = $height/$width;
                            $ratio      = round($ratio,2);
                            
                           // Validate image file dimension
                           if($width > "800" || $height > "328"){
                                $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                                $this->template->render();
                                $this->session->set_userdata('message', "<p>Image dimension should be within 800 X 328.</p>");
                                redirect($redirectimg);
                               
                           }else if($ratio!="0.41" && $ratio!="0.42"){
                                $data['validation_errors'] = "<p>Image dimension should be within 800 X 328.</p>";
                                $this->template->write_view('contents', $this->prefixUrl.'edit', $data);
                                $this->template->render();
                                $this->session->set_userdata('message', "<p>Image dimension should be within 800 X 328.</p>");
                                redirect($redirectimg);
                           }
                        /*************************************************************************/

                    //changing file name for selected
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $config['file_name']  = rand()."_".time()."_context_cat.$ext";
                    $file_upload = $this->upload_files($config, "image");
                    

                    if($file_upload['status']=="success"){
                        //$this->resize($file_upload['data']['file_name'], 50, 50, GAME_IMAGE_LARGE_PATH, RIDER_IMAGE_THUMB_PATH);
                        $res = $this->resize_save_image($file_upload['data']['file_name'], $file_upload['data']['full_path'], MATCH_IMAGE_LARGE_PATH,MATCH_IMAGE_THUMB_PATH);
                        $dataInasert['image'] = $file_upload['data']['file_name'];
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
                /**********/
                if($this->input->post('match_progress') =="AB"){
                        $match_id = $match_detail['id'];
                        $unique_id = $match_detail['unique_id'];
                        
                        $get_curl = $this->curl_get_method(CRON_SERVER."abodent_match/".$match_id."/".$unique_id);
                        $get_res  = json_decode($get_curl);                    

                    if(isset($get_res->code) && $get_res->code == 0){
                        $this->session->set_userdata('smessage', $get_res->message);
                        redirect($url);
                    }else{
                        $this->session->set_userdata('message', $get_res->message);
                        redirect($failurl);
                    }
                }else {
                    $this->main_model->cruid_update($table, $dataInasert, $cond);
                    $this->session->set_userdata('smessage', $this->name.' Successfully updated');
                    redirect($url);
                }
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }

    // View Player matches live
    public function view_live() {

        $user_name = $this->uri->segment(4);
        $unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl."live"));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $failurl = base_url($this->prefixUrl.'view_live/' . $user_name.'?return='.$url);

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
        
           // $this->form_validation->set_rules('name', 'Name', "trim|required");
           $this->form_validation->set_rules('match_progress', 'match_progress', "trim|required");
            //$this->form_validation->set_message('is_unique_again', "This  {$this->name} is already taken in " . SITE_TITLE . ". Please try different");
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', $this->prefixUrl.'view_live', $data);
                $this->template->render();
            } else {

                ///error_reporting(E_ALL);
                if($this->input->post('match_progress') =="R"){
                    $match_id = $match_detail['id'];
                    $unique_id = $match_detail['unique_id'];
                    //echo CRON_SERVER."declare_match_result/".$match_id."/".$unique_id;
                    $get_curl = $this->curl_get_method(CRON_SERVER."declare_match_result/".$match_id."/".$unique_id);
                    $get_res = json_decode($get_curl);
                    //print_r($get_res);die();
                    $close_date = date_create_from_format("d-m-Y h:i a",$this->input->post('close_date'));
                    $close_date = strtotime(date_format($close_date,"Y-m-d h:i a"));
                    $data = array(
                        //'name' =>         $this->input->post('name'),
                        //'match_limit' => $this->input->post('match_limit'),
                        //'match_progress' => $this->input->post('match_progress'),
                        //'close_date'  => $close_date,
                        //'series_id'   => $this->input->post('series_id'),
                        'updated_by'  => $this->session->userdata('adminId'),
                        'updated_at'  => time(),               
                    );
                
                if(isset($get_res->code) && $get_res->code == 0){
                    //$this->main_model->cruid_update($table, $data, $cond);
                    $this->session->set_userdata('smessage', $get_res->message);
                    redirect($url);
                }else{
                    //$this->main_model->cruid_update($table, $data, $cond);
                    $this->session->set_userdata('message', $get_res->message);
                    redirect($failurl);
                }
            }elseif($this->input->post('match_progress') =="AB"){
                    $match_id = $match_detail['id'];
                    $unique_id = $match_detail['unique_id'];
                    
                    $get_curl = $this->curl_get_method(CRON_SERVER."abodent_match/".$match_id."/".$unique_id);
                    $get_res = json_decode($get_curl);
                    //print_r($get_res->code);die(APIURL."declare_match_result/".$match_id."/".$unique_id);
                    
                    
                    $close_date = date_create_from_format("d-m-Y h:i a",$this->input->post('close_date'));
                    $close_date = strtotime(date_format($close_date,"Y-m-d h:i a"));
                    $data = array(
                        //'name' =>         $this->input->post('name'),
                        //'match_limit' => $this->input->post('match_limit'),
                        //'match_progress' => $this->input->post('match_progress'),
                        //'close_date'  => $close_date,
                        //'series_id'   => $this->input->post('series_id'),
                        'updated_by'  => $this->session->userdata('adminId'),
                        'updated_at'  => time(),               
                    );
                if(isset($get_res->code) && $get_res->code == 0){
                    //$this->main_model->cruid_update($table, $data, $cond);
                    $this->session->set_userdata('smessage', $get_res->message);
                    redirect($url);
                }else{
                    //$this->main_model->cruid_update($table, $data, $cond);
                    $this->session->set_userdata('message', $get_res->message);
                    redirect($failurl);
                }
            }else {
                $this->session->set_userdata('message', "Sorry, Please change Match Progress!");
                redirect($failurl);
            }
          }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($failurl);
        }
    }

    // View Player matches completed
    public function view_completed() {

        $user_name = $this->uri->segment(4);
        $unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl."completed"));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

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
        
            //$this->form_validation->set_rules('name', 'Name', "trim|required");
            //$this->form_validation->set_message('is_unique_again', "This  {$this->name} is already taken in " . SITE_TITLE . ". Please try different");
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

        $credit_players= 0;
        $position_counts= 0;

        $queryP = $this->db->query("SELECT COUNT(*) exists_points , (SELECT game_type_id FROM `tbl_cricket_matches` WHERE `id` = '$slug') as game_type_id FROM `tbl_cricket_points` WHERE game_type_id= (SELECT game_type_id FROM `tbl_cricket_matches` WHERE `id` = '$slug') AND is_deleted='N' AND status ='A'");
        $rowP = $queryP->row();

        if (isset($rowP->exists_points) && $rowP->exists_points ==0)
        {
           $this->session->set_userdata('message', "Please check points system. <script>window.location.replace('".HTTP_PATH."admin/cricket_points/add?gt=".$rowP->game_type_id."');</script>");
           exit();
        }

        $query = $this->db->query("SELECT count(id) as credit_players FROM `tbl_cricket_match_players` WHERE `credits`=0 AND `match_unique_id`=(SELECT unique_id FROM tbl_cricket_matches WHERE id='$slug')");
        $row = $query->row();

        if (isset($row->credit_players) && $row->credit_players >0)
        {
           $credit_players = $row->credit_players;
           $this->session->set_userdata('message', "Some players({$row->credit_players}) don't have credit point, So please update credits point before than active match.");
           exit();
        }
        if (isset($row->credit_players) && $row->credit_players ==0 && $credit_players==0) {
            $query2 = $this->db->query("SELECT COUNT(id) AS position_counts FROM tbl_cricket_players WHERE FIND_IN_SET(uniqueid, (SELECT GROUP_CONCAT(`player_unique_id`) player_unique_id FROM `tbl_cricket_match_players` WHERE `match_unique_id`=(SELECT unique_id FROM tbl_cricket_matches WHERE id='$slug' AND (position = ' ' OR position = '' OR position IS NULL ))))");
            $row1 = $query2->row(); echo $this->db->last_query();
            if (isset($row1->position_counts) && $row1->position_counts >0)
            {
                $position_counts = $row1->position_counts;
                $this->session->set_userdata('message', "Some players({$row1->position_counts}) don't have Playing Role, So please update Playing Role before than active match.");

                exit();
            }
        }
        if($credit_players ==0 && $position_counts ==0){
            $this->main_model->cruid_update($table, $data, $cond);
            $this->session->set_userdata('smessage', "Selected {$this->name} successfully activated");
        }
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

    //  Get new matches Api all 
    public function get_matche_series() {
        $this->checkUser();
        if($get = $this->curl_get_method(ENTITYSPORT_MATCHES_SERIES)){
            echo $get;
        }else{
            echo json_encode(["error"=>'error']);
        }
    }
        
    public function get_matches($series_id=0) {        
        $this->checkUser();
        if($get = $this->curl_get_method(ENTITYSPORT_MATCHES."/$series_id")){
            echo $get;
        }else{
            echo json_encode(["error"=>'error']);
        }
    }
        
    public function get_our_db_playes(){
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

        $select_fields = "tcmp.status, tct.name as team_name,tcmp.id,tcmp.credits, match_unique_id,player_unique_id,tcmp.team_id ,tcp.name,tcmp.playing_role as position,GROUP_CONCAT(`tcpg`.`file_name` ORDER BY `tcpg`.`player_id`) as file_name, tcmp.image";
        $joins = [];
        $joins[1] = ['table'=>"{$this->tbl_cricket_players} tcp", 'condition'=>"tcmp.player_unique_id = tcp.uniqueid",'jointype'=>'left'];
        $joins[2] = ['table'=>"{$this->tbl_cricket_player_galleries} tcpg", 'condition'=>"tcp.id = tcpg.player_id",'jointype'=>'left'];
        $joins[3] = ['table'=>"{$this->tbl_teams} tct", 'condition'=>"tcmp.team_id = tct.id",'jointype'=>'left'];


        $group_by = "tcmp.id ";
        $order_by = array(
            'as_table' => 'tcmp',
            'field' => 'player_unique_id',
            'type' => 'ASC',
        );
        $teamGet11 = $this->main_model->cruid_select_array($table, $select_fields, $joins,$cond ,$group_by,$order_by,'',0,FALSE);
        $teamGet1 = [];
        foreach ($teamGet11 as $key1 => $value1) {
            $teamGimg = $this->db->query("SELECT GROUP_CONCAT(file_name) as file_names FROM `tbl_cricket_team_player_galleries` WHERE `team_id`={$value1['team_id']} AND `is_deleted`='N' AND `status`='A' ")->row();
            if( isset($teamGimg->file_names) ){
                $value1['file_name'] = ( !empty( $value1['file_name'] ) )?$value1['file_name'].",".$teamGimg->file_names:$teamGimg->file_names;
            }
            $teamGet1[] = $value1;
        }
        $get_player['squad'][0]['players'] = $teamGet1;
        $get_player['squad'][0]['name']= $teamGet1[0]['team_name'];
        
        $teamGet22 = $this->main_model->cruid_select_array($table, $select_fields, $joins, $cond2 ,$group_by,$order_by,'',0,FALSE);
        $teamGet2 = [];
        foreach ($teamGet22 as $key12 => $value12) {
            $teamGimg = $this->db->query("SELECT GROUP_CONCAT(file_name) as file_names FROM `tbl_cricket_team_player_galleries` WHERE `team_id`={$value12['team_id']} AND `is_deleted`='N' AND `status`='A' ")->row();
            if( isset($teamGimg->file_names) ){
                $value12['file_name'] = ( !empty( $value12['file_name'] ) )?$value12['file_name'].",".$teamGimg->file_names:$teamGimg->file_names;
            }
            $teamGet2[] = $value12;
        }
        $get_player['squad'][1]['players'] = $teamGet2;
        $get_player['squad'][1]['name']= $teamGet2[0]['team_name'];

        /**********Player Add********************************/

            if(isset($get_player['squad']) && array_key_exists("squad",$get_player)){
                $this->load->view($this->prefixUrl.'our_rec_fetch', $get_player);
            }else{
                echo "not found!";
            }
        }
    }
    
    public function get_our_db_live_playes(){
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
        $select_fields = "tcmp.is_in_playing_squad, tcmp.status, tct.name as team_name,tcmp.id, match_unique_id,player_unique_id,team_id ,tcp.name,GROUP_CONCAT(`tcpg`.`file_name` ORDER BY `tcpg`.`player_id`) as file_name, tcmp.image,tcmp.credits,tcmp.selected_by, tcmp.points, tcp.position";
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
                $this->load->view($this->prefixUrl.'ajax_live_players', $get_player);
            }else{
                echo "not found!";
            }
        }
    }
    
    public function get_our_db_completed_playes(){
        $this->get_our_db_live_playes();
    }
    
    public function get_current_playes(){
        $this->checkUser();
        $unique_id = $this->uri->segment(4);//$this->input->post('unique_id');
        $series_uniqueid = $this->uri->segment(5);//series_uniqueid
        $team_1_id = $this->uri->segment(6);//team_1_id
        
        if (empty($unique_id)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
        /**********Player Add********************************/
            // $get_player_curl = $this->curl_get_method(CRICAPI_MATCHE_PLAYER."&unique_id=".$unique_id);
            $get_player_curl = $this->curl_get_method(ENTITYSPORT_MATCHE_PLAYERS.$series_uniqueid."/".$unique_id);
          
            $get_player = json_decode($get_player_curl);
            
            if(isset($get_player->squad) && array_key_exists("squad",$get_player)){ 
                /********************************/
                $table  = "{$this->tbl_teams}";
                $cond   = ["id"=>$team_1_id];

                $select_fields  = "";
                $joins          = [];
                $group_by       = "id ";
                $order_by = array(
                    'field'  => 'id',
                    'type'   => 'ASC',
                    );
                $teamGet11 = $this->main_model->cruid_select($table, $select_fields, $joins,$cond ,$group_by,$order_by);
                if($get_player->squad[0]->name != $teamGet11['name']){
                    $reversed = array_reverse($get_player->squad);
                    $get_player->squad = $reversed;
                }
                /********************************/

                $get_player->match_unique_id = $unique_id;
                $get_player->prefixUrl = 'admin/match_players/';
                $get_player->table = $this->table;
                $get_player->name = $this->name;
                $get_player->names = $this->names;

                $this->load->view($this->prefixUrl.'third_rec_fetch', $get_player);
            }else{
                 echo '<p style="margin-top: 110px;">Third party api not found players!</p>';
            }
        }
    }    
    
    // edit user detail
    public function add_contest_match() {
        ini_set('max_input_vars', 3000000000);

        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Add Contest IN {$this->name}", site_url('section'));
        $url = $this->input->get("return");

        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $table               = $this->tbl_cricket_contests;
        $tblccc              = $this->tbl_cricket_contest_categories;

        $cond_For_ccc = "is_deleted = 'N' AND is_private = 'N' AND is_beat_the_expert = 'N' AND status='A' AND ( (id IN(SELECT `category_id` FROM `tbl_cricket_contests` WHERE is_deleted = 'N' AND `is_beat_the_expert`='N' GROUP BY `category_id` ) ) OR (id IN( SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `is_beat_the_expert`='N' GROUP BY `category_id` ) ) ) ";

        $cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "'";

        //$cond .= " AND tblccc.status ='A'";

        $confirm_win_cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND {$tbl_contest_matches}.confirm_win='Y'";

        //$confirm_win_cond .= " AND tblccc.status ='A'";

        $select_fields      = "$tblccc.*";
        $CMselect_fields    = "GROUP_CONCAT(`contest_id`) as contest_id";
        $group_by           = "$tbl_contest_matches.match_id ";
        $joins1             = [];
        $joins              = [];
        ///$joins1[0] = ['table'=>"$tblccc tblccc", 'condition'=>"{$tbl_contest_matches}.category_id = tblccc.id",'jointype'=>'inner'];
        $Get_already = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $cond, $group_by);
        $already     = ($Get_already)?explode(",",$Get_already['contest_id']):array();
        
        $Get_confirm_win = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $confirm_win_cond, $group_by);
        $confirm_winalready = ($Get_confirm_win)?explode(",",$Get_confirm_win['contest_id']):array();
        
        $order_by   = array(
                'field' => 'id',
                'type'  => 'DESC',
            );

        $contest_categories = $this->main_model->cruid_select_array($tblccc, $select_fields, $joins, $cond_For_ccc,"",$order_by);

        $match_cond = "id ='$match_id' AND is_deleted='N'";
        $Get_match = $this->main_model->cruid_select($this->table, "id,unique_id,match_limit", [], $match_cond);

        if ($Get_match) {
            $data['id']                 = $match_id;
            $data['already']            = $already;
            $data['confirm_winalready'] = $confirm_winalready;
            $data['contents']           = $contest_categories;
            $data['title']              = "Add Contest {$this->name} Details";
            $data['name']               = $this->name;
            $data['names']              = $this->names;
            $data['table']              = $this->table;
            $data['prefixUrl']          = $this->prefixUrl;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            

            if ($_SERVER['REQUEST_METHOD'] == "GET") {               
                $data['check_error']= 'Add Contest with Match';             
                $this->template->write_view('contents', $this->prefixUrl.'contest_matches', $data);
                $this->template->render();
            } else {
                //print_r($this->input->post());die;
                if($_SERVER['REQUEST_METHOD'] == "POST"){
                    $table = $this->tbl_cricket_contest_matches;
                    // $this->main_model->cruid_delete($table, ['match_id'=> $match_id]);
                    $this->main_model->cruid_update($table, ['is_deleted'=> 'Y'], ['is_private' => 'N','is_beat_the_expert' => 'N','match_id'=> $match_id]);
                if($this->input->post('check')){
                    $c_win_con_per = $this->input->post('confirm_win_contest_percentage');
                    $per_user_team_allowed  = $this->input->post('per_user_team_allowed');
                    $multi_team_allowed     = $this->input->post('multi_team_allowed');
                    $winnersdataa           = $this->input->post('winnersdata');
                    $is_compression_allow     = $this->input->post('is_compression_allow');
                    $is_duplicate_allow     = $this->input->post('is_duplicate_allow');
                    $duplicate_count        = $this->input->post('duplicate_count');
                    $entry_fees             = $this->input->post('entry_fees');
                    $more_entry_fees        = $this->input->post('more_entry_fees');
                    $actual_entry_fees      = $this->input->post('actual_entry_fees');
                    $contest_matches_bonus_type = $this->input->post('cash_bonus_used_type');
                    $contest_matches_bonus_value = $this->input->post('cash_bonus_used_value');
                    $contest_matches_row_id = $this->input->post('contest_matches_row_id');

                    foreach($this->input->post('check') as $val){
                            $winnersdata =   json_decode($winnersdataa[$val]);
                            $per_user_team_allowedWithMetch =  ( $Get_match['match_limit'] < $per_user_team_allowed[$val] ) ? $Get_match['match_limit']:$per_user_team_allowed[$val];

                            $contest_json =  json_decode($winnersdata->contest_json);
                            $firstPrize = $contest_json->per_price[0];
                            $add_data = ['confirm_win_contest_percentage' => $c_win_con_per[$val],
                                         'per_user_team_allowed'          => $per_user_team_allowedWithMetch,
                                         'multi_team_allowed'             => ($per_user_team_allowed[$val] >1 && isset($multi_team_allowed[$val]) && $multi_team_allowed[$val]=='Y')?'Y':'N',
                                         'total_team'                     => $winnersdata->total_team,
                                         'category_id' => ($winnersdata->category_id)?$winnersdata->category_id:null,
                                         'match_unique_id' => ($Get_match['unique_id'])?$Get_match['unique_id']:null,
                                         'total_price'                     => $winnersdata->total_price,
                                         'contest_json'                    => json_encode($contest_json),
                                         'entry_fees'                      => $entry_fees[$val],
                                         'more_entry_fees'                 => $more_entry_fees[$val],
                                         'actual_entry_fees'               => $actual_entry_fees[$val],
                                         'first_prize'                      => $firstPrize,
                                         'is_compression_allow'              => (isset($is_compression_allow[$val]) && $is_compression_allow[$val] =='Y')?'Y':'N',
                                         'is_duplicate_allow'              => (isset($is_duplicate_allow[$val]) && $is_duplicate_allow[$val] =='Y')?'Y':'N',
                                         'duplicate_count'                 => ($duplicate_count[$val] !='')?$duplicate_count[$val]:'0',
                                         'cash_bonus_used_type'             =>$contest_matches_bonus_type[$val],
                                         'cash_bonus_used_value'            =>$contest_matches_bonus_value[$val]
                                        ];
                            $data = array(
                                'contest_id'     => $val,
                                'match_id'       => $match_id,
                                'status'         => 'A',
                                'created_by'     => $this->session->userdata('adminId'),
                                'updated_by'     => $this->session->userdata('adminId'),
                                'created_at'     => time(),               
                                'updated_at'     => time(),               
                            )+$add_data;

                        $conds  = [ 'contest_id'=> $val, 'match_id'=> $match_id ];
                        
                        if($this->main_model->check__exists($table,$conds)){
                            /*if( isset( $contest_matches_row_id[$val] ) && !empty( $contest_matches_row_id[$val] ) ){
                                $conds['id'] = $contest_matches_row_id[$val];
                            }*/
                            $this->main_model->cruid_update($table,['is_deleted'=>'N','updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()]+$add_data, $conds);
                        }else{
                            $id = $this->main_model->cruid_insert($table, $data);
                            $slug = $this->generateRandomString(12).$id."_";
                            $conds_slug  = ['id'=> $id];

                            $this->main_model->cruid_update($table,['slug'=>$slug,'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $conds_slug);
                        }
                    }
                }
                    $this->main_model->cruid_update($table,['confirm_win'=>"N",'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], ['match_id'=> $match_id]);
                    
                    if($this->input->post('confirm_win')){
                        foreach($this->input->post('confirm_win') as $valss){
                         
                            $confirm_wintake = ($this->input->post('confirm_win') !=null && in_array($valss,$this->input->post('confirm_win'))) ? "Y" : "N";
                            $condss = ['contest_id'=> $valss,'match_id'=> $match_id];
                            if($this->main_model->check__exists($table,$condss)){
                                $this->main_model->cruid_update($table,['confirm_win'=>$confirm_wintake,'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $condss);
                            }else{
                                //$business_id = $this->main_model->cruid_insert($table, $data);
                            }
                        }
                    }else{
                            $conds  = ['match_id'=> $match_id];
                            $this->main_model->cruid_update($table,['confirm_win'=>"N",'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $conds);
                        }
                        
                    }
                
                $this->session->set_userdata('smessage',"Add Contest with ". $this->name.' Successfully!');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }

    public function beat_the_expert_contest_match() {
        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Beat the Expert Contest {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $table = $this->tbl_cricket_contests;
        $tblccc = $this->tbl_cricket_contest_categories;
        $cond_For_ccc = "is_deleted = 'N' AND is_beat_the_expert = 'Y'";
        $cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND is_deleted='N' AND is_beat_the_expert = 'Y'";
        $confirm_win_cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND confirm_win='Y'";
        $select_fields = "$tblccc.*";
        $CMselect_fields = "GROUP_CONCAT(`contest_id`) as contest_id";
        $group_by = "$tbl_contest_matches.match_id ";
        $joins1 = [];
        $joins = [];
        //$joins[0] = ['table'=>"$tblccc tblccc", 'condition'=>"{$table}.category_id = tblccc.id",'jointype'=>'left'];

        $Get_already = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $cond, $group_by);
        $already     = ($Get_already)?explode(",",$Get_already['contest_id']):array();
        
        $Get_confirm_win = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $confirm_win_cond, $group_by);
        $confirm_winalready = ($Get_confirm_win)?explode(",",$Get_confirm_win['contest_id']):array();
        
        $order_by = array(
            'field' => 'id',
            'type' => 'DESC',
        );
        $contents = $this->main_model->cruid_select_array($tblccc, $select_fields, $joins, $cond_For_ccc,"",$order_by);

        $match_cond = "id ='$match_id' AND is_deleted='N'";
        $Get_match = $this->main_model->cruid_select($this->table, "id,unique_id,match_limit", [], $match_cond);

        if ($Get_match) {
            $data['id'] = $match_id;
            $data['already'] = $already;
            $data['confirm_winalready'] = $confirm_winalready;
            $data['contents'] = $contents;
            $data['title'] = "Beat the Expert Contest {$this->name} Details";
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;
            $data['prefixUrl'] = $this->prefixUrl;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            
            //$this->form_validation->set_rules('check[]', 'Contest check', "required");
           
            //if ($this->form_validation->run() == FALSE) {

            // Check  Expert Team
            $beatthe = 0;
            $query = $this->db->query("SELECT * FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and `is_beat_the_expert`='Y' and `is_deleted` ='N' and status='A' AND `team_id` >0 AND is_abondant='N' ");
            $row = $query->num_rows();
            if($row > 0 ){
                $beatthe = 1;
            }
            $data['beatthe'] =$beatthe;

            if ($_SERVER['REQUEST_METHOD'] == "GET") {               
                $data['check_error']= 'Add Beat the Expert Contest with Match';             
                $this->template->write_view('contents', $this->prefixUrl.'beat_the_expert_contest_match', $data);
                $this->template->render();
            } else {
                //print_r($this->input->post());die;
                if($_SERVER['REQUEST_METHOD'] == "POST"){
                    $table = $this->tbl_cricket_contest_matches;
                    // $this->main_model->cruid_delete($table, ['match_id'=> $match_id]);
                    $this->main_model->cruid_update($table, ['is_deleted'=> 'Y'], ['is_beat_the_expert' => 'Y','match_id'=> $match_id]);
                if($this->input->post('check')){
                    $c_win_con_per = $this->input->post('confirm_win_contest_percentage');
                    $per_user_team_allowed  = $this->input->post('per_user_team_allowed');
                    $multi_team_allowed     = $this->input->post('multi_team_allowed');
                    $winnersdataa           = $this->input->post('winnersdata');
                    $is_compression_allow     = $this->input->post('is_compression_allow');
                    $is_duplicate_allow     = $this->input->post('is_duplicate_allow');
                    $duplicate_count        = $this->input->post('duplicate_count');

                    $entry_fees             = $this->input->post('entry_fees');
                    $more_entry_fees        = $this->input->post('more_entry_fees');
                    $actual_entry_fees      = $this->input->post('actual_entry_fees');
                    foreach($this->input->post('check') as $val){
                            $winnersdata =   json_decode($winnersdataa[$val]);
                            $per_user_team_allowedWithMetch =  ( $Get_match['match_limit'] < $per_user_team_allowed[$val] ) ? $Get_match['match_limit']:$per_user_team_allowed[$val];
                            
                            $contest_json =  json_decode($winnersdata->contest_json);
                            $add_data = ['confirm_win_contest_percentage' => $c_win_con_per[$val],
                                         'per_user_team_allowed'          => $per_user_team_allowedWithMetch,
                                         'multi_team_allowed'             => ($per_user_team_allowed[$val] >1 && isset($multi_team_allowed[$val]) && $multi_team_allowed[$val] =='Y' ) ? 'Y' : 'N',
                                         'total_team'                     => $winnersdata->total_team,
                                         'category_id' => ($winnersdata->category_id)?$winnersdata->category_id:null,
                                         'match_unique_id' => ($Get_match['unique_id'])?$Get_match['unique_id']:null,
                                         'total_price'                     => $winnersdata->total_price,
                                         'contest_json'                    => json_encode($contest_json),
                                         'entry_fees'                      => $winnersdata->entry_fees,
                                         'more_entry_fees'                 => $winnersdata->more_entry_fees,
                                         'actual_entry_fees'               => $winnersdata->actual_entry_fees,
                                         'entry_fee_multiplier'            => $winnersdata->entry_fee_multiplier,
                                         'max_entry_fees'                  => $winnersdata->max_entry_fees,
                                         'is_beat_the_expert'              => 'Y',
                                         'is_compression_allow'              => (isset($is_compression_allow[$val]) && $is_compression_allow[$val] =='Y')?'Y':'N',
                                         'is_duplicate_allow'              => (isset($is_duplicate_allow[$val]) && $is_duplicate_allow[$val] =='Y')?'Y':'N',
                                         'duplicate_count'                 => ($duplicate_count[$val] !='')?$duplicate_count[$val]:'0',
                                        ];
                            $data = array(
                                'contest_id'     => $val,
                                'match_id'       => $match_id,
                                'status'         => 'A',                                
                                'created_by'     => $this->session->userdata('adminId'),
                                'updated_by'     => $this->session->userdata('adminId'),
                                'created_at'     => time(),               
                                'updated_at'     => time(),               
                            )+$add_data;

                        $conds  = [ 'contest_id'=> $val, 'match_id'=> $match_id ];
                        
                        if($this->main_model->check__exists($table,$conds)){
                            $this->main_model->cruid_update($table,['is_deleted'=>'N','updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()]+$add_data, $conds);
                            //print_r($this->db->last_query());
                            //print_r($data);die;
                        }else{
                            $id = $this->main_model->cruid_insert($table, $data);
                            $slug = $this->generateRandomString(12).$id."_";
                            $conds_slug  = ['id'=> $id];

                            $this->main_model->cruid_update($table,['slug'=>$slug,'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $conds_slug);
                        }
                    }
                }
                    $this->main_model->cruid_update($table,['confirm_win'=>"N",'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], ['match_id'=> $match_id]);
                    
                    if($this->input->post('confirm_win')){
                        foreach($this->input->post('confirm_win') as $valss){
                         
                            $confirm_wintake = ($this->input->post('confirm_win') !=null && in_array($valss,$this->input->post('confirm_win'))) ? "Y" : "N";
                            $condss = ['contest_id'=> $valss,'match_id'=> $match_id];
                            if($this->main_model->check__exists($table,$condss)){
                                $this->main_model->cruid_update($table,['confirm_win'=>$confirm_wintake,'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $condss);
                            }else{
                                //$business_id = $this->main_model->cruid_insert($table, $data);
                            }
                        }
                    }else{
                            $conds  = ['match_id'=> $match_id];
                            $this->main_model->cruid_update($table,['confirm_win'=>"N",'updated_by'=>$this->session->userdata('adminId'),'updated_at'=>time()], $conds);
                        }
                        
                    }
                
                $this->session->set_userdata('smessage',"Beat the Expert Contest with ". $this->name.' Successfully!');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }
    
    public function private_contest_match() {
        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl));
        $this->breadcrumbs->push("Private Contests", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $table = $this->tbl_cricket_contests;
        $tblccc = $this->tbl_cricket_contest_categories;
        $cond_For_ccc = "is_deleted = 'N' AND is_private = 'Y'";
        $cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND is_deleted='N'";
        $confirm_win_cond = "{$tbl_contest_matches}.match_id ='" . $match_id . "' AND confirm_win='Y'";
        $select_fields = "$tblccc.*";
        $CMselect_fields = "GROUP_CONCAT(`contest_id`) as contest_id";
        $group_by = "$tbl_contest_matches.match_id ";
        $joins1 = [];
        $joins = [];
        //$joins[0] = ['table'=>"$tblccc tblccc", 'condition'=>"{$table}.category_id = tblccc.id",'jointype'=>'left'];

        $Get_already = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $cond, $group_by);
        $already     = ($Get_already)?explode(",",$Get_already['contest_id']):array();
        
        $Get_confirm_win = $this->main_model->cruid_select($tbl_contest_matches, $CMselect_fields, $joins1, $confirm_win_cond, $group_by);
        $confirm_winalready = ($Get_confirm_win)?explode(",",$Get_confirm_win['contest_id']):array();
        
        $order_by = array(
            'field' => 'id',
            'type' => 'DESC',
        );
        $contents = $this->main_model->cruid_select_array($tblccc, $select_fields, $joins, $cond_For_ccc,"",$order_by);

        $match_cond = "id ='$match_id' AND is_deleted='N'";
        $Get_match = $this->main_model->cruid_select($this->table, "id,unique_id,match_limit", [], $match_cond);

        if ($Get_match) {
            $data['id'] = $match_id;
            $data['already'] = $already;
            $data['confirm_winalready'] = $confirm_winalready;
            $data['contents'] = $contents;
            $data['title'] = "Private Contests {$this->name} Details";
            $data['name'] = $this->name;
            $data['names'] = $this->names;
            $data['table'] = $this->table;
            $data['prefixUrl'] = $this->prefixUrl;
            $data['tbl_cricket_series'] = $this->tbl_cricket_series;
            
            //$this->form_validation->set_rules('check[]', 'Contest check', "required");
           
            //if ($this->form_validation->run() == FALSE) {
            if ($_SERVER['REQUEST_METHOD'] == "GET") {               
                $data['check_error']= 'Add Contest with Match';             
                $this->template->write_view('contents', $this->prefixUrl.'private_contest_match', $data);
                $this->template->render();
            } 
        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }

    public function winning_breakup_edit_modal() {
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
        $table = $this->tbl_cricket_contests;
        $cond = "{$this->table}.id ='" .  $this->name . "'";
        $select_fields = "$table.*";
        $joins = array();


        //$user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        // print_r( $output );
        
        $get_player['contents']=(array)$unique_id;

       if(!empty($unique_id)) {
            $this->load->view($this->prefixUrl.'winning_breakup_edit_modal', $get_player);
        } else {
            
            echo ('Please select atleast one Admin');
        }
    }

    public function beat_the_expert_winning_breakup_edit_modal() {
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
        $table = $this->tbl_cricket_contests;
        $cond = "{$this->table}.id ='" .  $this->name . "'";
        $select_fields = "$table.*";
        $joins = array();


        //$user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        // print_r( $output );
        
        $get_player['contents']=(array)$unique_id;

       if(!empty($unique_id)) {
            $this->load->view($this->prefixUrl.'beat_the_expert_winning_breakup_edit_modal', $get_player);
        } else {
            
            echo ('Please select atleast one Admin');
        }
    }

    public function beat_the_expert_winning_breakup_edit_modal_after() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();
        
        $output        = $this->input->post('newdatapost');
        parse_str($output, $newdatapost);
        $datapostold        = (array)json_decode($this->input->post('datapostold'));    
        /*******************/
        $contest_json = [];
        $contest_json['per_min_p']= $newdatapost['per_min_p']['I'];
        $contest_json['per_max_p']= $newdatapost['per_max_p']['I'];
        $contest_json['per_price']= $newdatapost['per_price']['I'];
            
            $data['id']                     = $datapostold['id'];
            $data['category_id']            = $datapostold['category_id'];
            $data['total_team']             = $newdatapost['total_team'];
            $data['total_price']            = $newdatapost['total_price'];
            $data['entry_fee_multiplier']   = $newdatapost['entry_fee_multiplier'];
            $data['entry_fees']             = $newdatapost['entry_fees'];
            $data['more_entry_fees']        = $newdatapost['more_entry_fees'];
            $data['actual_entry_fees']      = $newdatapost['actual_entry_fees'];
            $data['max_entry_fees']         = $newdatapost['max_entry_fees'];
            $data['per_user_team_allowed']  = $datapostold['per_user_team_allowed'];
            $data['multi_team_allowed']     = $datapostold['multi_team_allowed'];
            $data['is_compression_allow']     = $datapostold['is_compression_allow'];
            $data['is_duplicate_allow']     = $datapostold['is_duplicate_allow'];
            $data['duplicate_count']        = $datapostold['duplicate_count'];
            $data['is_beat_the_expert']     = $datapostold['is_beat_the_expert'];
            $data['contest_json']           = json_encode($contest_json);
            /*******************/

     
        /**************************************************/
        $get_player['prefixUrl'] = 'admin/matchs/';
        $get_player['table'] = $this->table;
        $get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_id'] = $data;
        

        $url = $this->input->get("return");
        $table = $this->tbl_cricket_contests;
        $cond = "{$this->table}.id ='" .  $this->name . "'";
        $select_fields = "$table.*";
        $joins = array();


        //$user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        // print_r( $output );
        
        $get_player['contents']=$data;

       if(!empty($data)) {
           echo  json_encode($data);
        } else {
            echo 0; 
        }
    }
    
    public function winning_breakup_edit_modal_after() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();
        
        $output        = $this->input->post('newdatapost');
        parse_str($output, $newdatapost);
        $datapostold        = (array)json_decode($this->input->post('datapostold'));    
        /*******************/
        $contest_json = [];
        $contest_json['per_min_p']= $newdatapost['per_min_p']['I'];
        $contest_json['per_max_p']= $newdatapost['per_max_p']['I'];
        $contest_json['per_price']= $newdatapost['per_price']['I'];
            
            $data['id'] = $datapostold['id'];
            $data['category_id'] =$datapostold['category_id'];
            $data['total_team'] =  $newdatapost['total_team'];
            $data['total_price'] = $newdatapost['total_price'];
            $data['entry_fees'] = $datapostold['entry_fees'];
            $data['more_entry_fees'] = $datapostold['more_entry_fees'];
            $data['actual_entry_fees'] = $datapostold['actual_entry_fees'];
            $data['per_user_team_allowed'] = $datapostold['per_user_team_allowed'];
            $data['multi_team_allowed'] = $datapostold['multi_team_allowed'];
            $data['is_compression_allow']     = $datapostold['is_compression_allow'];
            $data['is_duplicate_allow']     = $datapostold['is_duplicate_allow'];
            $data['duplicate_count']        = $datapostold['duplicate_count'];
            $data['contest_json'] = json_encode($contest_json);
            /*******************/

     
        /**************************************************/
        $get_player['prefixUrl'] = 'admin/matchs/';
        $get_player['table'] = $this->table;
        $get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_id'] = $data;
        

        $url = $this->input->get("return");
        $table = $this->tbl_cricket_contests;
        $cond = "{$this->table}.id ='" .  $this->name . "'";
        $select_fields = "$table.*";
        $joins = array();


        //$user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        // print_r( $output );
        
        $get_player['contents']=$data;

       if(!empty($data)) {
           echo  json_encode($data);
        } else {
            echo 0; 
        }
    }

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
        $table = $this->tbl_cricket_contests;
        $cond = "{$this->table}.id ='" .  $this->name . "'";
        $select_fields = "$table.*";
        $joins = array();


        //$user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        // print_r( $output );
        
        $get_player['contents']=(array)$unique_id;

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

    public function live_contest_match() {
        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl."live"));
        $this->breadcrumbs->push("View Contest IN {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $table = $this->tbl_cricket_contests;
        $tblccc = $this->tbl_cricket_contest_categories;

        if($this->input->get('v') == 'private'){
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'Y' AND is_beat_the_expert = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
        }else if($this->input->get('v') == 'beat_the_expert'){
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'N' AND is_beat_the_expert = 'Y' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
        }else{
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'N' AND is_beat_the_expert = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
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

            $this->template->write_view('contents', $this->prefixUrl.'contest_matches_live', $data);
                $this->template->render();
            }
        }
        
        
    public function contest_matches_completed() {
        $match_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $match_id);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl."completed"));
        $this->breadcrumbs->push("View Contest IN {$this->name}", site_url('section'));

        $url = $this->input->get("return");
        $tbl_contest_matches = $this->tbl_cricket_contest_matches;
        $table = $this->tbl_cricket_contests;
        $tblccc = $this->tbl_cricket_contest_categories;

        if($this->input->get('v') == 'private'){
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'Y' AND is_beat_the_expert = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
        }else if($this->input->get('v') == 'beat_the_expert'){
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'N' AND is_beat_the_expert = 'Y' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
        }else{
            $cond_For_ccc = "is_deleted = 'N' AND is_private = 'N' AND is_beat_the_expert = 'N' AND id IN(SELECT `category_id` FROM `tbl_cricket_contest_matches` WHERE `match_id`='$match_id' and is_deleted='N')";
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



    // View Player For create team with customers
    public function add_team_customer() {
        $user_name = $this->uri->segment(4);
        $unique_id = $this->uri->segment(4);
        $this->loginCheck($this->prefixUrl.'edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url($this->prefixUrl."index"));
        $this->breadcrumbs->push("Edit {$this->name}", site_url('section'));

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

        $data['beattheis_admin'] = "";
        $beatthe = 0;
        if($this->input->get('bc') == 'y'){
            $query = $this->db->query("SELECT tccc.customer_id, tccm.id FROM `tbl_cricket_contest_matches` tccm
                left join tbl_cricket_customer_contests tccc on tccm.id=tccc.match_contest_id AND tccm.match_unique_id=tccc.match_unique_id  LEFT JOIN tbl_customers tc on tc.id=tccc.customer_id WHERE `match_id`='$user_name' and `is_beat_the_expert`='Y' and tccm.`is_deleted` ='N' and tccm.status='A' AND `team_id` >0 AND is_abondant='N' AND tc.is_admin='1' AND tc.is_fake='1'");
            $row = $query->num_rows();
            if($row > 0 ){
                $beatthe = 1;
                $existsAdmin = $query->row();
                $data['beattheis_admin'] = $existsAdmin->customer_id;
            }
        }
            $data['beatthe'] =$beatthe;
           
            //dd($data,1);
            $this->template->write_view('contents', $this->prefixUrl.'add_team_customer', $data);
            $this->template->render();

        } else {
            $this->session->set_userdata('message', "Sorry, this {$this->name} not available");
            redirect($url);
        }
    }

    public function add_team_customer_playerslist(){
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
        $cond = ["tcmp.match_unique_id"=>$unique_id , "tcmp.team_id"=>$team_1, "tcmp.status"=>'A', "tcmp.is_deleted"=>'N'];
        $cond2 = ["tcmp.match_unique_id"=>$unique_id , "tcmp.team_id"=>$team_2 , "tcmp.status"=>'A', "tcmp.is_deleted"=>'N'];

        $select_fields = "tcmp.status, tct.logo as team_logo,tct.name as team_name,tct.sort_name as team_sort_name,tcmp.id, match_unique_id,player_unique_id,team_id ,tcp.name,tcmp.playing_role as position,GROUP_CONCAT(`tcpg`.`file_name` ORDER BY `tcpg`.`player_id`) as file_name, tcmp.image, tcmp.points, tcmp.credits, tcmp.is_in_playing_squad, tcmp.dream_team_player";

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

        $TblteamSetting = "tbl_cricket_team_setting";
        $team_seting = $this->main_model->cruid_select_array($TblteamSetting, "", [], "" ,"","");
        

        $team_setings = [];
        foreach ($team_seting as $key => $value) {
            $team_setings[$value['key']]= $value['value'];
            
        }
        $get_player['team_seting'] = $team_setings;
        /**********Player Add********************************/
    $teamdata = [];
    foreach (array_merge($teamGet1, $teamGet2) as $keyt => $valuet) {
       if( ucfirst( $valuet['position'] ) == "Allrounder" ){ 
                $teamdata['Allrounder'][]=$valuet;
            }
            else if( ucfirst( $valuet['position'] ) == "Batsman" ){ 
                 $teamdata['Batsman'][]=$valuet;
            }
            else if( ucfirst( $valuet['position'] ) == "Bowler" ){ 
                 $teamdata['Bowler'][]=$valuet;
            }
            else if( ucfirst( $valuet['position'] ) == "Wicketkeeper" ){ 
                 $teamdata['Wicketkeeper'][]=$valuet;
            }
        }
        $get_player['teamdata'] = $teamdata;
        $get_player['team_1'] = $teamGet['team_1_id'];
        $get_player['team_2'] = $teamGet['team_2_id'];
        $get_player['team_1name']= ($teamGet1[0]['team_sort_name'])?$teamGet1[0]['team_sort_name']:$teamGet1[0]['team_name'];
        $get_player['team_2name']= ($teamGet2[0]['team_sort_name'])?$teamGet2[0]['team_sort_name']:$teamGet2[0]['team_name'];
        $team_1logo = TEAMCRICKET_IMAGE_THUMB_URL.$teamGet1[0]['team_logo'];
        $team_2logo = TEAMCRICKET_IMAGE_THUMB_URL.$teamGet2[0]['team_logo'];
        $get_player['team_1logo']= ($teamGet1[0]['team_logo'])?($team_1logo):"http://placehold.it/100";
        $get_player['team_2logo']= ($teamGet2[0]['team_logo'])?($team_2logo):"http://placehold.it/100";

        $get_player['beatthe'] = $this->input->get('beatthe');
        /**********Player Add********************************/
        //echo "<pre>";  print_r($get_player);die;

            if(isset($get_player['squad']) && array_key_exists("squad",$get_player)){
                $this->load->view($this->prefixUrl.'add_team_customer_players', $get_player);
            }else{
                echo "not found!";
            }
        }
    }

    public function add_players_with_customer(){
        $postData = $this->input->post();

        if(!empty($postData['fields']) && !empty($postData['player_json'])) {
                $fields = $postData['fields'];
               // print_r(json_encode($this->input->post()));die;
                $match_unique_id    = $fields['match_unique_id'];
                $match_contest_id   = $fields['match_contest_id'];
                $user_id            = $fields['user_id'];
                $customer_team_name = $fields['customer_team_name'];
                $team_name          = $fields['team_name'];
                $is_update          = $fields['is_update'];
                $customer_team_id   = $fields['customer_team_id'];
                $postcurl = array(
                        'match_unique_id'=>$match_unique_id,
                        'match_contest_id'=>$match_contest_id,
                        'user_id'=>$user_id,
                        'customer_team_name'=>$customer_team_name,
                        'team_name'=>$team_name,
                        'is_update'=>$is_update,
                        'customer_team_id'=>$customer_team_id,
                        'player_json'=>json_encode($postData['player_json']),
                );

               // print_r($postcurl);die;
                $get_curl = $this->Curlcreate_admin_customer_team_join_contest($postcurl);
                $get_res = json_decode($get_curl);
                //print_r($get_res);
                //die($match_unique_id);
                
            if(isset($get_res->code) && $get_res->code == 0){
                $this->session->set_userdata('smessage', $get_res->message);
                echo $get_curl;
                //redirect($url);
            }else{
                //$this->session->set_userdata('message', $get_res->message);
                echo $get_curl;
               // redirect($url);
            }
        }else {
            $this->session->set_userdata('message', "Sorry, Please add players!");
            redirect(base_url($this->prefixUrl.'/index'));
        }
    }

    public function GetJoinedContest_teams_with_customer($id){
        if($id){
            $beatthe = $this->input->get('beatthe');

            $ids = explode("__", $id);
            $match_unique_id = current($ids);
            $customer_id = end($ids);
            $opt_all = $this->main_model->cruid_select_array_order("tbl_cricket_customer_teams", " * ", $joins = array(), $cond = array("customer_id" => $customer_id,"match_unique_id" =>$match_unique_id ), $order_by = array(), $limit = '', $order_by_other = array());

            $opt[''] = "Please Select Team";
            if($beatthe && $beatthe >0){
               $opt = [];
            }
           if (!empty($opt_all)) {
               foreach ($opt_all as $datass) {
                   $opt[$datass->id] = $datass->customer_team_name ."[".$datass->more_name."]";
               }
                echo('<label for="user_id" class="control-label">Select Customer Team For Update</label><div class="col-sm-12">');
                echo form_dropdown('user_id', $opt,'', 'class="form-control" id="customer_teams_id" ');
                echo('</div>');
           }
        }
    }
    public function GetJoinedContest_teams_with_customer_players($id){
        if($id){
 
            $ids = explode("__", $id);
            $match_unique_id = current($ids);
            $customer_id = $ids[1];
            $customer_team_id = end($ids);
            $joins = array();
            $joins[1] = ['table'=>"tbl_cricket_customer_team_plyers tcctp", 'condition'=>"tcct.id = tcctp.customer_team_id",'jointype'=>'left'];


            $opt_all = $this->main_model->cruid_select("tbl_cricket_customer_teams as tcct", "tcct.*,GROUP_CONCAT(`player_unique_id`) as player_unique_ids ", $joins, $cond = array("tcctp.customer_id" => $customer_id,"tcctp.match_unique_id" =>$match_unique_id,"tcctp.customer_team_id" =>$customer_team_id ), $order_by = array(), $limit = '', $order_by_other = array());

            $opt_allCAPTE = $this->main_model->cruid_select_array_order("tbl_cricket_customer_team_plyers as tcct", " player_unique_id ,multiplier", [], $cond = array("tcct.customer_id" => $customer_id,"tcct.match_unique_id" =>$match_unique_id,"tcct.customer_team_id" =>$customer_team_id ), $order_by = array(), $limit = '', $order_by_other = array());

            $playersdata = [];
            foreach ($opt_allCAPTE as $key => $value) {
                $playersdata[$value->player_unique_id] = $value->multiplier;
            }
            $opt_all['playersdata'] = $playersdata;
            //echo $this->db->last_query();
           if (!empty($opt_all) && $opt_all['id'] !=null) {
                header('Content-Type: application/json');
                echo json_encode($opt_all);
           }
        }
    }

    private function Curlcreate_admin_customer_team_join_contest($postfields){
        $apiUrl = APIURL."create_admin_customer_team_join_contest";
            // print_r(json_encode($postfields));die;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $apiUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($postfields),
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          return $response;
        }
        return true;
    }   
    
    public function credits_save(){
        $id      = $this->input->post('id');
        $credits = $this->input->post('credits');
        $position = $this->input->post('position');
        $player_unique_id = $this->input->post('player_unique_id');
        if($id>0){
            $table  = $this->tbl_cricket_match_players;
            $data = array(
                'credits' => $credits,
                'playing_role' => $position,
            );
            $cond = "id ='" . $id . "'";
            $this->main_model->cruid_update($table, $data, $cond);
           
            /*if(!empty($position) && $player_unique_id){
                $table  = $this->tbl_cricket_players;
                $data = array(
                    'position' => $position,
                );
                $cond = "uniqueid ='" . $player_unique_id . "'";
                $this->main_model->cruid_update($table, $data, $cond);
               
            }*/
             echo 1;
        }
        else{
            echo 0;
        }
        exit();
    }
    
    //End of Matches class
}
