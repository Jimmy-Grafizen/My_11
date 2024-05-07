<?php

require_once('base.php');
class Joined_teams extends Base {

    private $limit = 10;
    private $table = 'tbl_countries';

	private $tblccc = 'tbl_cricket_customer_contests';
	private $tbl_c = 'tbl_customers';
	private $tbl_cm = 'tbl_cricket_matches';
	private $tbl_ct = 'tbl_cricket_customer_teams';

    private $image = '';
    private $prefixUrl = 'admin/joined_teams/';
    private $name = 'Joined team'; // For singular
    private $names = 'Joined teams'; //plural form 

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
	
	function sets(){

		// Create session array
		$sess_array = array(
			'match_contest_id' => $this->input->get('ccm')
		);
		echo json_encode($sess_array);
		// Add user value in session
		$this->session->set_userdata('match_contest_id', $sess_array['match_contest_id']);
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
		$tblccc = $this->tblccc;
		$joins[1] = ['table'=>"{$this->tbl_c} tbl_c", 'condition'=>"$tblccc.customer_id=tbl_c.id",'jointype'=>'left'];
		$joins[2] = ['table'=>"{$this->tbl_cm} tbl_cm", 'condition'=>"$tblccc.match_unique_id = tbl_cm.unique_id",'jointype'=>'left'];
		$joins[3] = ['table'=>"{$this->tbl_ct} tbl_ct", 'condition'=>"$tblccc.customer_team_id = tbl_ct.id",'jointype'=>'left'];

		$order_by = array(
            'field' => 'new_rank',
            'type' => 'ASC',
			);
        if ($this->input->get('field')) {
            $order_by = array();
            $order_by_other = array(
                'field' => $this->input->get('field'),
                'type' => $this->input->get('sort'),
            );
        } else {
            $order_by = array();
            $order_by_other = array(
                'field' => 'new_rank',
                'type' => 'asc',
            );
        }
        $table = $this->tblccc;
		$match_contest_id = $set_data = $this->session->userdata('match_contest_id');
		if($match_contest_id == "")
		{
			$match_contest_id=0;
		}
        $condit = "$table.match_contest_id = $match_contest_id ";
        $select_fields = ", $table.customer_team_id,$table.match_contest_id,$table.match_unique_id,$table.customer_id,$table.id,tbl_cm.name as match_name,tbl_ct.name,tbl_c.team_name as customer_team_name,tbl_ct.name as team_name,IF(tbl_c.is_admin = '1',tbl_ct.more_name,'') as team_more_name,IF(tbl_c.is_admin = '1',tbl_ct.customer_team_name,'') as customer_team_name_in_tcct,tbl_c.firstname,tbl_c.lastname,tbl_c.image";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`tbl_c`.`firstname` LIKE '%" . $keyword . "%'";
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
        $data['table'] = 'tbl_c';
		$data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->load->view($this->prefixUrl.'ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');
        $match_contest_id = $set_data = $this->session->userdata('match_contest_id');
		if($match_contest_id == "")
		{
			$match_contest_id=0;
		}
		$this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url("/admin/matches/"));
        $this->breadcrumbs->push("{$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index";
        $data['this_url'] = base_url() . $this->prefixUrl."index";
		$data['prefixUrl'] = $this->prefixUrl;
        $data['table'] = $this->table;
		$data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

    public function view_team_player() {
        $this->loginCheck($this->prefixUrl.'add');
        $this->checkUser();
  		
        $unique_id = json_decode($this->input->post('datapost'));
		
		$customer_id= $unique_id->customer_id;
		$match_unique_id=$unique_id->match_unique_id;
		$customer_team_id=$unique_id->customer_team_id;
		/**************************************************/
		$get_player['prefixUrl'] = 'admin/match_players/';
        $get_player['table'] = $this->table;
		$get_player['name'] = $this->name;
        $get_player['names'] = $this->names;
        $get_player['unique_id'] = $unique_id;
		
		$query = $this->db->query("SELECT tcct.id,tcct.name as team_name,tbl_c.team_name as tbl_c_team_name, (SELECT GROUP_CONCAT(CONCAT(tcctp.player_unique_id,'----',tcctp.position,'----',tcctp.multiplier,'----',IFNULL(tcmp.image, '0'),'----',IFNULL(tcp.name,'0'),'----',tcctp.team_id,'----',tcmp.points,'----',IFNULL(tcp. bets,''), '----',IFNULL(tcp.bowls,'') ,'----',IFNULL(tcp.dob,''), '----',IFNULL(tc.name,''), '----',IFNULL(tcp.position,'')) ORDER BY tcctp.multiplier DESC SEPARATOR '--++--' ) from tbl_cricket_customer_team_plyers tcctp LEFT JOIN tbl_cricket_match_players tcmp ON (tcctp.player_unique_id=tcmp.player_unique_id AND tcctp.match_unique_id=tcmp.match_unique_id) LEFT JOIN tbl_cricket_players tcp ON (tcctp.player_unique_id=tcp.uniqueid) LEFT JOIN tbl_countries tc ON (tcp.country_id=tc.id) where tcctp.customer_team_id = tcct.id ) as players_data FROM `tbl_cricket_customer_teams` tcct LEFT JOIN tbl_customers tbl_c ON (tcct.customer_id=tbl_c.id) WHERE tcct.customer_id=$customer_id AND tcct.match_unique_id= $match_unique_id AND tcct.id=$customer_team_id");

			$output = array();
			$i=0;
		foreach ($query->result_array() as $row)
		{
	   
					$team=array();

					$team['id']=$row['id'];
					$team['name']=$row['tbl_c_team_name']."(".$row['team_name'].")";
					
					$players_array=explode("--++--",$row['players_data']);

					$j=0;
					$players=array();
					foreach($players_array as $players_array_s){

						$per_player=explode("----", $players_array_s);
						$player=array();
						$player['player_id']=$per_player[0];
						$player['player_pos']=$per_player[1];
						$player['player_multiplier']=$per_player[2];
						$player['image']=!empty($per_player[3]) ? PLAYER_IMAGE_THUMB_URL.$per_player[3] : NO_IMG_URL_PLAYER;
						$player['name']=$per_player[4];
						$player['player_name']=@$per_player[4];
						$player['team_id']=@$per_player[5];
						$player['points']=@$per_player[6];
						$player['bat_type']=@$per_player[7];
						$player['bowl_type']=@$per_player[8];
						$player['dob']=@$per_player[9];
						$player['country']=@$per_player[10];
                        $player['position']=@$per_player[11];
						$player['player_unique_id']=$per_player[0];
						$player['unique_id']=$match_unique_id;
						$player['customer_team_id']=$customer_team_id;
						$player['customer_id']=$customer_id;
						
						$players[$j]=$player;
						$j++;

					}

					$team['players']=$players;

					$output[$i]=$team;
					$i++;
		}
	   
		  //dd( $output ,1);
		
		$get_player['get_teams']=$output;

	   if(!empty($unique_id)  && $output) {
		   	$this->load->view($this->prefixUrl.'our_rec_fetch', $get_player);
        } else {
			
            echo ('Please select atleast one Admin');
		}
    }

	
		
	public function get_player_statistics(){
		$this->checkUser();
        $unique_data = $this->input->post('postjson');
		
		/**************************************************/
		$table 	= "tbl_cricket_match_players_stats";
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
		
			$query = "Select tcmp.points, (Select count(tcct.id) from tbl_cricket_customer_teams tcct where tcct.match_unique_id=$match_unique_id) as match_team_count, (Select count(tcctp.id) from tbl_cricket_customer_team_plyers tcctp where tcctp.match_unique_id=$match_unique_id AND tcctp.player_unique_id=tcmp.player_unique_id) as player_team_count from tbl_cricket_match_players tcmp where tcmp.player_unique_id=".$player_unique_id." AND tcmp.match_unique_id=$match_unique_id";

			$queryRun  	= $this->db->query($query);
			$statsdata  = $queryRun->row();
			if( isset($statsdata->match_team_count) && $statsdata->match_team_count > 0 ){
				$get_player['unique_data']['selected'] = $this->format_number(($statsdata->player_team_count/$statsdata->match_team_count)*100) . "%";
			}else{
				$get_player['unique_data']['selected'] =0.00;
			}
				$get_player['unique_data']['points'] = isset($statsdata->points)?$statsdata->points:0;

				$get_player["breckup_points"] = $contents_cat_take;
				
				$this->load->view($this->prefixUrl.'ajax_player_statistics', $get_player);
			}
	}
	
    public function format_number($number){
        return str_replace(',', '', number_format($number, 2));
    }
	
	
	
	function ajax_index_customer($offset = 0) {

        if ($this->input->post('action')) {
            $this->action();
        }
        $limit = $this->limit;
        $config['base_url'] = base_url() . $this->prefixUrl."/ajax_index_customer";
        $config['base_parent_url'] = base_url() . $this->prefixUrl."/customers/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();
		$tblccc = $this->tblccc;
		$joins[1] = ['table'=>"{$this->tbl_c} tbl_c", 'condition'=>"$tblccc.customer_id=tbl_c.id",'jointype'=>'left'];
		$joins[2] = ['table'=>"{$this->tbl_cm} tbl_cm", 'condition'=>"$tblccc.match_unique_id = tbl_cm.unique_id",'jointype'=>'left'];
		$joins[3] = ['table'=>"{$this->tbl_ct} tbl_ct", 'condition'=>"$tblccc.customer_team_id = tbl_ct.id",'jointype'=>'left'];

		$order_by = array(
            'field' => 'match_contest_id',
            'type' => 'desc',
			);
        if ($this->input->get('field')) {
            $order_by = array();
            $order_by_other = array(
                'field' => $this->input->get('field'),
                'type' => $this->input->get('sort'),
            );
        } else {
            $order_by = array();
            $order_by_other = array(
                'field' => 'team_name',
                'type' => 'asc',
            );
        }
        $table = $this->tblccc;
		$match_contest_id = $set_data = $this->session->userdata('match_contest_id');
		if($match_contest_id == "")
		{
			$match_contest_id=0;
		}
		$customer_id  = $this->session->userdata('customer_id');
			if($customer_id == "")
			{
				$customer_id=0;
			}
        $condit = "$table.match_contest_id = $match_contest_id AND tbl_c.id=$customer_id";
        $select_fields = ", $table.customer_team_id,$table.match_contest_id,$table.match_unique_id,$table.customer_id,$table.id,tbl_cm.name as match_name,tbl_ct.name,tbl_c.team_name as customer_team_name,tbl_ct.name as team_name,tbl_c.firstname,tbl_c.lastname,tbl_c.image";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`tbl_c`.`firstname` LIKE '%" . $keyword . "%'";
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
        $data['table'] = 'tbl_c';
		$data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->load->view($this->prefixUrl.'ajax_index', $data);
    }

    public function customers($offset = 0) {
        $this->loginCheck($this->prefixUrl.'index');
		$customer_id  = $this->session->userdata('customer_id');
			if($customer_id == "")
			{
				$customer_id=0;
			}
		$this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> {$this->names}", site_url("/admin/reports/customer_contest_matches/".$customer_id));
        $this->breadcrumbs->push("{$this->names} List", site_url('section'));

        ob_start();
        $this->ajax_index_customer($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "{$this->names} List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . $this->prefixUrl."ajax_index_customer";
        $data['this_url'] = base_url() . $this->prefixUrl."customers";
		$data['prefixUrl'] = $this->prefixUrl;
        $data['table'] = $this->table;
		$data['name'] = $this->name;
        $data['names'] = $this->names;
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', $this->prefixUrl.'index', $data);
        $this->template->render();
    }

//End of class
}
