<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

   class Home extends CI_Controller {
      function __construct() {
        parent::__construct();

	 
        $this->load->model('main_model');
       
     }
    public function index() 
    {
        $data['title'] = 'HOME PAGE';
        $data['page_name']='home';
        $table = "tbl_sliders";
        $cond = "status ='A'";
        $select_fields = "tbl_sliders.*";
        $joins = array();
        $rowSliderInfo =  $this->main_model->cruid_select_array($table, $select_fields, $joins = array(), $cond);
        $data['rowSliderInfo']=$rowSliderInfo;
        $tableSettings = "tbl_settings";
        $select_fields_Settings = "tbl_settings.*";
        $rowSettingsInfo =  $this->main_model->cruid_select_array($tableSettings, $select_fields_Settings);
        //echo "<pre>"; print_r($rowSettingsInfo);die;
        $data['rowSettingsInfo']=$rowSettingsInfo;
        $tableTestimonial = "tbl_testimonial";
        $select_fields_Testimonial = "tbl_testimonial.*";
        $rowTestimonialInfo =  $this->main_model->cruid_select_array($tableTestimonial, $select_fields_Testimonial);
        $data['rowTestimonialInfo']=$rowTestimonialInfo;
        /*echo '<pre>';
        print_r();
        echo '</pre>';*/
        $this->load->view('front/layout',$data);
    } 
	   
	  public  function contentapp($strUrl)
	   {
		  
		  if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
       if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
        
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }
		  
	   $strPage = $strUrl;
 		  
		   $table = "tbl_page_contents";
        $cond = "page_url ='" . $strPage . "'";
        $select_fields = "tbl_page_contents.*";
        $joins = array();
        $rowPageInfo =  $this->main_model->cruid_select($table, $select_fields, $joins = array(), $cond);
		  
		  
		  
$aryResponse =array();
$aryResponse['message'] = "ok";
$aryResponse['result'] =$rowPageInfo['content'];
echo json_encode($aryResponse);
die;
	   }
      
        public function pages($strPageName='') {
            
           //// echo $strPageName;
        $table = "tbl_page_contents";
        $cond = "page_url ='" . $strPageName . "'";
        $select_fields = "tbl_page_contents.*";
        $joins = array();
        $rowPageInfo =  $this->main_model->cruid_select($table, $select_fields, $joins = array(), $cond);
        $data['title'] = 'HOME PAGE';
        $data['page_name']='commoncontent';
        $data['rowPageInfo']=$rowPageInfo;
        $this->load->view('front/layout',$data);
      }
      public function faq($strPageName='faq.html') {
            
           //// echo $strPageName;
        $table = "tbl_page_contents";
        $cond = "page_url ='" . $strPageName . "'";
        $select_fields = "tbl_page_contents.*";
        $joins = array();
        $rowPageInfo =  $this->main_model->cruid_select($table, $select_fields, $joins = array(), $cond);
        $data['title'] = 'HOME PAGE';
        $data['page_name']='faq';
        $data['rowPageInfo']=$rowPageInfo;
        
        /*****FAQ CATEGORY****/
        $table = "tbl_faq_category";
        $cond = "is_deleted ='N'";
        $select_fields = "tbl_faq_category.*";
        $joins = array();
        $rowFaqCatInfo =  $this->main_model->cruid_select_array($table, $select_fields,$joins = array(), $cond);
        $data['rowFaqCatInfo']=$rowFaqCatInfo;
       // echo '<pre>'; print_r($rowFaqCatInfo);die();
        $this->load->view('front/layout',$data);
      }
      public function faqList($strFaqCarId='') {
            
           //// echo $strPageName;
        $data['title'] = 'HOME PAGE';
        $data['page_name']='faq_list';
        $table = "tbl_faq";
        $cond = "category_id ='" . $strFaqCarId . "'";
        $select_fields = "tbl_faq.*";
        $joins = array();
        $rowFaqInfo =  $this->main_model->cruid_select_array($table, $select_fields, $joins = array(), $cond);
        $data['rowFaqInfo']=$rowFaqInfo;
        //echo '<pre>';print_r($rowFaqInfo);die;
        $table = "tbl_faq_category";
        $cond = "id ='" . $strFaqCarId . "'";
        $select_fields = "tbl_faq_category.*";
        $joins = array();
        $rowFaqCatInfo =  $this->main_model->cruid_select($table, $select_fields, $joins = array(), $cond);
        $data['rowFaqCatInfo']=$rowFaqCatInfo;
        $this->load->view('front/layout',$data);
      }
       public function contact($strPageName='contact-us.html') {
            
           //// echo $strPageName;
        $table = "tbl_page_contents";
        $cond = "page_url ='" . $strPageName . "'";
        $select_fields = "tbl_page_contents.*";
        $joins = array();
        $rowPageInfo =  $this->main_model->cruid_select($table, $select_fields, $joins = array(), $cond);
        $data['title'] = 'HOME PAGE';
        $data['page_name']='contact';
        $data['rowPageInfo']=$rowPageInfo;
        $this->load->view('front/layout',$data);
      }
      function fantasypointsystem(){
        $data    = array();
        $data['batting_key']  =  array('Every Run Scored','Every Boundary Hit','Every Six Hit','Thirty Runs','Half Century','Century','Dismiss For A Duck');
        $data['bowling_key']  =  array('Wicket',"Two Wicket","Three Wicket",'Four Wicket','Five Wicket','Maiden Over');
        $data['fielding_key'] =  array('Run Out','Run Out Thrower','Run Out Catcher','Catch','Stumping');
        $data['others_key']   =  array('Captain'=>"2x",'Vice-Captain'=>"1.5x",'Being Part Of Eleven');
        $data['economy_rate'] =  array('Economy Rate');
        $data['strike_rate']  =  array('Strike Rate');
        $data['title'] = 'HOME PAGE';
        $data['page_name']='fantasy-point-system';
        $data['tabs']= $this->getCricketTabs();
        $data['tabsContent']= $this->getTabContent();
		  
		 $data['dbref']=  $this->db;
        $this->load->view('front/layout',$data);
      }
      
      function getTabContent(){
        $query =  $this->db->query("SELECT * FROM `tbl_game_types` WHERE `status` = 'A' AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");   
        $results = $query->result();
        $tabscreate = array();
        foreach ($results as $key => $value) {
            $activeCls = ($key === 0)?'active':'';
            $query =  $this->db->query("SELECT * FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' AND  `game_type_id`='.$value->id.'");

            $resultsTab = $query->result();
            $arrayCompine =   array();
            foreach ($resultsTab as $keyp => $valuep) {
                $arrayCompine[$valuep->meta_key] = $valuep->meta_value;
            }
           $tabscreate[$value->name] = $arrayCompine;
        } 
        return $tabscreate;
      }
      
      function getCricketTabs(){
        $query =  $this->db->query("SELECT * FROM `tbl_game_types` WHERE `status` = 'A' AND `id` NOT IN (8,9,10) AND `is_deleted` = 'N' AND `id` in (SELECT `game_type_id` FROM `tbl_cricket_points` WHERE `status` = 'A' AND `is_deleted` = 'N' GROUP by `game_type_id` ORDER BY `game_type_id` DESC )");   
        
        $results = $query->result();
        //echo "<pre>"; print_r($results);die;
        $tabscreate = '';
        foreach ($results as $key => $value) {
            $activeCls = ($key === 0)?'active':'';
            $tabscreate .='<li role="presentation" class="'.$activeCls.'"><a href="javascript:void(0)" onclick="showtab($(this))" data-id="cricket_'.str_replace(' ','_',$value->name).'" data-toggle="tab">'.$value->name.'</a></li>';
        }
        return $tabscreate;
      }
   } 
?>