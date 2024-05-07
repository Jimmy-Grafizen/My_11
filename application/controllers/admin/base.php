<?php

class Base extends MY_Controller {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        $login_user = $this->session->userdata('loginUser');
        error_reporting(0);
    }

    function start_require() {
        $menu = array(           
            'Role' => array(
                            array('url' => '/admin/groups/index', 'label'=>'List Role', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/groups/add', 'label'=>'Add Role',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/groups/edit', 'label'=>'Edit Role',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/groups/delete', 'label'=>'Delete Role', 'menu'=>false, 'level'=>0),
                               array('url' => '/admin/admins/index', 'label'=>'List Admins', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/admins/add', 'label'=>'Add Admin',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/admins/edit', 'label'=>'Edit Admin',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/admins/delete', 'label'=>'Delete Admin', 'menu'=>false, 'level'=>0)
                        ),
          
            'Matches' => array(
                            array('url' => '/admin/matches/index', 'label'=>'Upcoming', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/matches/live', 'label'=>'Live', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/matches/completed', 'label'=>'Completed', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/matches/add', 'label'=>'Add Match',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/matches/edit', 'label'=>'Edit Match',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/matches/delete', 'label'=>'Delete Match', 'menu'=>false, 'level'=>0)
                        ),	
            'Series' => array(
                            array('url' => '/admin/cricket_series/index', 'label'=>'List Series', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cricket_series/add', 'label'=>'Add Series',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cricket_series/edit', 'label'=>'Edit Series',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/cricket_series/delete', 'label'=>'Delete Series', 'menu'=>false, 'level'=>0)
                        ),
		 
			'Contests' => array(
						array('url' => '/admin/contests/index','label'=>'List Contests','menu'=>true, 'level'=>0),
						array('url' => '/admin/contests/add', 'label'=>'Add Contest','menu'=>true, 'level'=>0),
						array('url' => '/admin/contests/edit','label'=>'Edit Contest','menu'=>false, 'level'=>0),
						array('url' => '/admin/contests/delete', 'label'=>'Delete Contest','menu'=>false, 'level'=>0),
							array('url' => '/admin/cricket_contest_categories/index', 'label'=>'List Categories', 'menu'=>true, 'level'=>0),
						array('url' => '/admin/cricket_contest_categories/add', 'label'=>'Add Category',  'menu'=>true, 'level'=>0),
						array('url' => '/admin/cricket_contest_categories/edit', 'label'=>'Edit Category',  'menu'=>false, 'level'=>0),
						array('url' => '/admin/cricket_contest_categories/delete', 'label'=>'Delete Category', 'menu'=>false, 'level'=>0)
                      ),	
			'Points' => array(
                            array('url' => '/admin/cricket_points/index', 'label'=>'List Points', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cricket_points/add', 'label'=>'Add Point',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cricket_points/edit', 'label'=>'Edit Point',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/cricket_points/delete', 'label'=>'Delete Point', 'menu'=>false, 'level'=>0)
                        ),
            'Games' => array(
                            array('url' => '/admin/games/index', 'label'=>'List Games', 'menu'=>true, 'level'=>0),
                            //array('url' => '/admin/games/add', 'label'=>'Add Game',  'menu'=>false, 'level'=>0),
                            //array('url' => '/admin/games/edit', 'label'=>'Edit Game',  'menu'=>false, 'level'=>0),
                            //array('url' => '/admin/games/delete', 'label'=>'Delete Game', 'menu'=>false, 'level'=>0)
                        ),
            'Game Types' => array(
                            array('url' => '/admin/game_types/index', 'label'=>'List Game Types', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/game_types/add', 'label'=>'Add Game Type',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/game_types/edit', 'label'=>'Edit Game Type',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/game_types/delete', 'label'=>'Delete Game Type', 'menu'=>false, 'level'=>0)
                        ),
            'Players' => array(
                            array('url' => '/admin/players/index', 'label'=>'List Players', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/players/add', 'label'=>'Add Player',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/players/edit', 'label'=>'Edit Player',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/players/delete', 'label'=>'Delete Player', 'menu'=>false, 'level'=>0)
                        ),
		 
			'Notifications' => array(
						array('url' => '/admin/notifications/index','label'=>'List Notifications','menu'=>true, 'level'=>0),
						array('url' => '/admin/notifications/add', 'label'=>'Add Notifications','menu'=>true, 'level'=>0),
                        array('url' => '/admin/notifications/send_all', 'label'=>'Notifications To All','menu'=>true, 'level'=>0),
                        array('url' => '/admin/notifications/delete', 'label'=>'Delete Notifications','menu'=>false, 'level'=>0),
                        		array('url' => '/admin/email_notifications/index','label'=>'List Email Notifications','menu'=>true, 'level'=>0),
						array('url' => '/admin/email_notifications/add', 'label'=>'Add Email Notifications','menu'=>true, 'level'=>0),
                        array('url' => '/admin/email_notifications/delete', 'label'=>'Delete Email Notifications','menu'=>false, 'level'=>0),
                        	array('url' => '/admin/templates/index','label'=>'List Templates','menu'=>true, 'level'=>0),
						//array('url' => '/admin/templates/add', 'label'=>'Add Template','menu'=>true, 'level'=>0),
						array('url' => '/admin/templates/edit','label'=>'Edit Template','menu'=>false, 'level'=>0),
						array('url' => '/admin/templates/delete', 'label'=>'Delete Template','menu'=>false, 'level'=>0)
                      ),
		 
			'Sliders' => array(
						array('url' => '/admin/sliders/index','label'=>'List Sliders','menu'=>true, 'level'=>0),
						array('url' => '/admin/sliders/add', 'label'=>'Add Sliders','menu'=>true, 'level'=>0),
						array('url' => '/admin/sliders/edit','label'=>'Edit Sliders','menu'=>false, 'level'=>0),
						array('url' => '/admin/sliders/delete', 'label'=>'Delete Sliders','menu'=>false, 'level'=>0)
                      ),			
			'Teams' => array(
                            array('url' => '/admin/team_crickets/index', 'label'=>'List Teams', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/team_crickets/add', 'label'=>'Add Team',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/team_crickets/edit', 'label'=>'Edit Team',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/team_crickets/delete', 'label'=>'Delete Team', 'menu'=>false, 'level'=>0)
                        ),          
           
			'Customers' => array(
						array('url' => '/admin/customers/index','label'=>'List Customers','menu'=>true, 'level'=>0),
						array('url' => '/admin/customers/document_approval_pending','label'=>'Pending Verifications','menu'=>true, 'level'=>0),
						array('url' => '/admin/customers/pending_withdrawals','label'=>'Pending Withdrawals','menu'=>true, 'level'=>0),
						array('url' => '/admin/customers/add', 'label'=>'Add Customer','menu'=>true, 'level'=>0),
						array('url' => '/admin/customers/edit','label'=>'Edit Customer','menu'=>false, 'level'=>0),
						array('url' => '/admin/customers/password','label'=>'Change Password','menu'=>false, 'level'=>0),
						array('url' => '/admin/customers/delete', 'label'=>'Delete Customer','menu'=>false, 'level'=>0)
                      ),	
			'Reports' => array(
						array('url' => '/admin/reports/index','label'=>'Matches Reports','menu'=>true, 'level'=>0),
						array('url' => '/admin/reports/customers', 'label'=>'Customers Reports','menu'=>true, 'level'=>0),
						array('url' => '/admin/reports/earnings', 'label'=>'Earning Reports','menu'=>true, 'level'=>0),
						array('url' => '/admin/reports/withdrawals', 'label'=>'Withdrawal Reports','menu'=>true, 'level'=>0),
						 ),	
            'Cash bonus' => array(
							array('url' => '/admin/referral_cash_bonus/edit', 'label'=>'Referral Cash Bonus', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cash_bonus_recharge/index', 'label'=>'List Recharges', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cash_bonus_recharge/add', 'label'=>'Add Recharge',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cash_bonus_recharge/edit', 'label'=>'Edit Recharge',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/cash_bonus_recharge/delete', 'label'=>'Delete Recharge', 'menu'=>false, 'level'=>0)
                        ),	
            /*
            'Referral Commission' => array(
                            array('url' => '/admin/referal_commission/index', 'label'=>'List', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/referal_commission/add', 'label'=>'Add',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/referal_commission/edit', 'label'=>'Edit',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/referal_commission/delete', 'label'=>'Delete', 'menu'=>false, 'level'=>0),
                            array('url' => '/admin/referal_commission/customers', 'label'=>'Reports', 'menu'=>true, 'level'=>0),
                        ),*/	
            /* 'Countries' => array(
                            array('url' => '/admin/countries/index', 'label'=>'List Countries', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/countries/add', 'label'=>'Add Country',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/countries/edit', 'label'=>'Edit Country',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/countries/delete', 'label'=>'Delete Country', 'menu'=>false, 'level'=>0)
                        ), */
             'States' => array(
                            array('url' => '/admin/states/index', 'label'=>'List States', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/states/add', 'label'=>'Add State',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/states/edit', 'label'=>'Edit State',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/states/delete', 'label'=>'Delete State', 'menu'=>false, 'level'=>0)
                        ), 
          /*  'Cities' => array(
                            array('url' => '/admin/cities/index', 'label'=>'List Cities', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cities/add', 'label'=>'Add City',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/cities/edit', 'label'=>'Edit City',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/cities/delete', 'label'=>'Delete City', 'menu'=>false, 'level'=>0)
                        ), */
            'Pages' => array(
                            array('url' => '/admin/content_management/index', 'label' => 'List Pages', 'menu' => true, 'level' => 0)
                        ),		  
            'Settings' => array(
                            array('url' => '/admin/settings/edit', 'label' => 'Settings', 'menu' => true, 'level' => 0),
                            array('url' => '/admin/settings/customer_avatars', 'label' => 'Customer Avatar', 'menu' => true, 'level' => 0),
                            array('url' => '/admin/quotations/index', 'label' => 'Quotations', 'menu' => true, 'level' => 0),
                             array('url' => '/admin/team_settings/edit', 'label'=>'Teams Settings', 'menu'=>true, 'level'=>0),
                          ),
            'Taxes' => array(
                            array('url' => '/admin/taxes/index', 'label'=>'List Taxes', 'menu'=>true, 'level'=>0),
                            array('url' => '/admin/taxes/add', 'label'=>'Add Tax',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/taxes/edit', 'label'=>'Edit Tax',  'menu'=>false, 'level'=>0),
                            array('url' => '/admin/taxes/delete', 'label'=>'Delete Tax', 'menu'=>false, 'level'=>0)
                        ),

            'Customer Queries' => array(                            
                            array('url' => '/admin/enquiry/customer_quries', 'label'=>'Customer Queries',  'menu'=>true, 'level'=>0),
                            array('url' => '/admin/enquiry/delete', 'label'=>'Delete',  'menu'=>false, 'level'=>0),
                        ),         
            );
        return $menu;
    }

    public function curl_get_method($url){
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_FOLLOWLOCATION=> true,
		  CURLOPT_SSL_VERIFYPEER=>false,
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  //echo "cURL Error #:" . $err;
		  return false;
		} else {
		  return $response;
		}
	}

}

/* End of file base.php */
/* Location: ./application/controllers/admin/base.php */
?>
