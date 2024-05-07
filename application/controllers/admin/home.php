<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once('base.php');
class Home extends Base {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        // load all models
        $this->load->model('users_model');
        $this->load->model('main_model');
        $this->load->library('breadcrumbs');
        $this->template->set_master_template('admin');
    }

    // admin login check
    function loginCheck($str) {
        if (!$this->session->userdata('adminId')) {
            $this->session->set_userdata('returnURL', $str);
            redirect('admin');
        }
    }

    // admin dashboard page
    function index() {
        $this->dashboard();
    }

    // admin dashboard page
    function dashboard() {
        $this->loginCheck('admin/home/dashboard');
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $data['title'] = "Dashboard";

        $dates = array();
        $year = date('Y');
        $select_fields = "count(tbl_users.id) as counter";
        $joins = array();
        $table = "tbl_users";

        for ($i = 1; $i <= date("m"); $i++) {
            $cond = 'DATE_FORMAT(tbl_users.created, "%Y-%m") = "' . $year . '-' . str_pad($i, 2, 0, STR_PAD_LEFT) . '"';
            $record = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
            $dates[] = $record['counter'];
        }
        $data['dates'] = $dates;

        $cond = 'date_sub(curdate(), INTERVAL 7 DAY) <= tbl_users.created  AND NOW() >= UNIX_TIMESTAMP(tbl_users.created)';
        $record = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
        $data['last_seven_days'] = $record['counter'];


        $this->template->write_view('contents', 'admin/home/dashboard', $data);
        $this->template->render();
    }

    // update admin profile
    function updateprofile() {
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push('<i class="fa fa-cogs"></i> Configuration', site_url('section'), false);
        $this->breadcrumbs->push('Manage Profile', site_url('section'));
        if ($this->session->userdata('adminId')) {
            $table = "tbl_users";
            $id = $this->session->userdata('adminId');
            $cond = "id ='" . $this->session->userdata('adminId') . "'";
            $select_fields = "tbl_users.*";
            $joins = array();
            $record = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
            $data['title'] = "Manage Profile";
            $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
            $this->form_validation->set_message('is_unique_again1', 'The username is already registered in ' . SITE_TITLE . ". Please try different username");

            if ($this->session->userdata('type') == 'Admin') {
                $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
                // $this->form_validation->set_rules('contactus_email', 'Contact Us Email', 'trim|required|valid_mail');
                // $this->form_validation->set_rules('address', 'Address', 'trim|required');
            }

            if ($this->form_validation->run() == FALSE) {

                $data['record'] = $record;

                $this->template->write_view('contents', 'admin/home/changeProfile', $data);
                $this->template->render();
            } else {
                $data = array(
                    'firstname' => $this->input->post('firstname'),
                    'lastname' => $this->input->post('lastname'),
                    // 'contactus_email' => $this->input->post('contactus_email'),
                    'mobile' => $this->input->post('mobile'),
                    // 'address' => $this->input->post('address'),
                );

                $this->main_model->cruid_update($table, $data, $cond);

                $this->session->set_flashdata('smessage', 'Profile Information is successfully updated.');
                redirect('/admin/home/updateprofile/');
            }
        } else {
            redirect("admin/home/login");
        }
    }

    // update website homepage content
    function updatecontent() {
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push('<i class="fa fa-cogs"></i> Configuration', site_url('section'), false);
        $this->breadcrumbs->push('Manage Home Page Contents', site_url('section'));
        if ($this->session->userdata('adminId')) {
            $table = "tbl_homecontent";
            $cond = "id > '0'";
            $select_fields = "tbl_homecontent.*";
            $joins = array();
            $record = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
            $data['title'] = "Manage Home Page Contents";
            $this->form_validation->set_rules('top', 'Top Text', "trim|required");
            $this->form_validation->set_rules('bottom', 'Bottom Text', "trim|required");
            if ($this->form_validation->run() == FALSE) {
                $data['record'] = $record;
                $this->template->write_view('contents', 'admin/home/updatecontent', $data);
                $this->template->render();
            } else {
                $data = array(
                    'top' => $this->input->post('top'),
                    'bottom' => $this->input->post('bottom'),
                );
                $this->main_model->cruid_update($table, $data);
                $this->session->set_flashdata('smessage', 'Content is successfully updated.');
                redirect('/admin/home/updatecontent/');
            }
        } else {
            redirect("admin/home/login");
        }
    }

    // admin login page
    function login() {

        if ($this->session->userdata('adminId')) {
            redirect('admin/home');
        }
        if ($this->session->userdata('check_login')) {
            $check_login = $this->session->userdata('check_login');
        } else {
            $check_login = 0;
        }
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($check_login != 0) {
            $this->form_validation->set_rules('captcha', 'Security', 'trim|required|matches1[image]');
            $this->form_validation->set_message('matches1', "Security code does not match with image text");
        }
        if ($this->form_validation->run() == TRUE or form_error('captcha')) {
            if (form_error('captcha')) {
                $message = form_error('captcha');
                $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                echo json_encode($data);
                die;
            }
            $admincheckstatus = $this->users_model->admincheckactive();
            if (!empty($admincheckstatus) and !$admincheckstatus['status']) {
                $message = "Your account deactivated from admin, please contact to admin to activate your account.";
                $data = array('valid' => 0, 'error' => $message);
                echo json_encode($data);
                die;
            }


            $checkAdminLoginDeatils = $this->users_model->adminLogin();
            if (empty($checkAdminLoginDeatils)) {
                $check_login = $check_login + 1;

                $this->session->set_userdata('check_login', $check_login);
                if ($check_login >= 3) {
                    $check_active = $this->users_model->admin_check_active();
                    $var_time = $check_active['0']->inactive_time;
                    if ($var_time == '')
                        $this->users_model->inactive_admin();
                    $message = "Invalid username and/or password. Your account got temporary disabled. Please login after 2 Minutes.";
                } else {
                    $message = "Invalid username or password You have " . (3 - $check_login) . " more attempts now";
                }

                $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                echo json_encode($data);
                die;
            } else {
                if ($checkAdminLoginDeatils) {
                    $check_active = $this->users_model->admin_check_active();
                    $current_time = time();
                    $difference = (int)$current_time - (int)$check_active['0']->inactive_time;
                    if ($difference < 120) {
                        $this->session->unset_userdata('adminId');
                        $message = "Invalid username and/or password. Your account got temporary disabled. Please login after 2 Minutes.";
                        $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                        echo json_encode($data);
                        die;
                    } else {
                        $this->users_model->update_check_active();
                        $this->session->unset_userdata('check_login');
                        $data = array('redirect' => HTTP_PATH . 'admin/home/dashboard', 'valid' => 1, 'captcha' => 0);
                        echo json_encode($data);
                        die;
                    }
                } else if (!$checkAdminLoginDeatils) {
                    $this->session->unset_userdata('adminId');
                    $this->session->unset_userdata('adminType');
                    $message = "Your account deactivated by admin";
                    $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                    echo json_encode($data);
                    die;
                }
            }

            $var_captcha = form_error('captcha');
            $var = strlen($var_captcha);
            $check_login = $this->session->userdata('check_login');
            if ($var == 45) {
                $check_login = $check_login + 1;
                $this->session->set_userdata('check_login', $check_login);
            }
            if ($check_login >= 3) {
                $check_active = $this->users_model->admin_check_active();
                $var_time = $check_active['0']->inactive_time;
                if ($var_time == '') {
                    $this->users_model->inactive_admin();
                }
                $check_active = $this->users_model->admin_check_active();
                $var_time = $check_active['0']->inactive_time;
                if ($var_time != '') {
                    $message = "Invalid username and/or password. Your account got temporary disabled. Please login after 2 Minutes.";
                    $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                    echo json_encode($data);
                    die;
                }
            } else {
                if ($check_login < 3 && $check_login != 0) {
                    $message = "Invalid username or password You have " . (3 - $check_login) . " more attempts now";
                    $data = array('valid' => 0, 'error' => $message, 'captcha' => 1);
                    echo json_encode($data);
                    die;
                }
            }
        } else {
            $data['check_login'] = $check_login;
            $data['title'] = "Login";
            $this->template->set_master_template('adminlogin');
            $this->template->write_view('contents', 'admin/home/login', $data);
            $this->template->render();
        }
    }

    // admin forgot password
    function forgotPassword() {

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

        $config['protocol'] = 'mail';
        $config['wordwrap'] = FALSE;
        $config['mailtype'] = 'html';
        $config['charset'] = 'utf-8';
        $config['crlf'] = "\r\n";
        $config['newline'] = "\r\n";
        $this->load->library('email', $config);
        $this->email->set_mailtype("html");

        if ($this->form_validation->run() == TRUE) {
            $adminDetail = $this->users_model->adminDetail();
            $data['adminDetail'] = $adminDetail;
            if (count($adminDetail) == 0) {
                echo json_encode(array('message' => 'Your Email not match please re-enter.', 'valid' => 0));
            } else {
                $this->load->library('parser');
                $password = rand(18973824, 989721389);
                $this->users_model->changePasswordFromId($adminDetail['id'], array('password' => md5($password)));
                $mail_data['text'] = "Your password has been retrived successfully";
                $mail_data['email'] = $adminDetail['email'];
                $mail_data['username'] = $adminDetail['username'];
                $mail_data['password'] = $password;
                if ($adminDetail['type'] == 'Admin')
                    $mail_data['firstname'] = "Administrator";
                else
                    $mail_data['firstname'] = "Sub Admin";

                $msg = $this->parser->parse('email/template_password', $mail_data, TRUE);

                $this->email->from(FORM_EMAIL, SITE_TITLE);
                $this->email->to($adminDetail['email']);
                $this->email->subject('Forgot Password');
                $this->email->message($msg);
                $this->email->send();
                // echo $msg; exit;

                echo json_encode(array('message' => 'Your password has been sent on your email id.', 'valid' => 1));
            }
        }
    }

    // admin logout
    function logout() {
        $this->session->unset_userdata('adminId');
        $this->session->unset_userdata('userType');
        redirect('admin/home/');
    }

    // admin change password
    function changepassword() {
        $this->loginCheck('admin/home/changepassword');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push('<i class="fa fa-cogs"></i> Configuration', site_url('section'), false);
        $this->breadcrumbs->push('Change Password', site_url('section'));

        $this->form_validation->set_rules('opassword', 'Old Password', 'trim|required|md5');
        $this->form_validation->set_rules('password', 'password', 'trim|required|min_length[8]|matches[cpassword]');
        $this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $data['title'] = "Change Password";

            $this->template->write_view('contents', 'admin/home/changePassword', $data);
            $this->template->render();
        } else {
            $response = $this->users_model->checkPassword($this->input->post('opassword'));
            if (empty($response)) {
                $this->session->set_flashdata('pass', $this->input->post('password'));
                $this->session->set_flashdata('cpass', $this->input->post('cpassword'));
                $this->session->set_flashdata('message', 'Please enter correct old password');
                redirect('/admin/home/changepassword/');
            } else {

                if ($response[0]['password'] == md5($this->input->post('password'))) {
                    $this->session->set_flashdata('pass', $this->input->post('password'));
                    $this->session->set_flashdata('cpass', $this->input->post('cpassword'));
                    $this->session->set_flashdata('message', 'You cannot put your old password for the  password!');
                    redirect('/admin/home/changepassword/');
                } else {

                    $data = array('password' => md5($this->input->post('password')));
                    $this->users_model->changepassword($data);
                    $this->session->set_flashdata('smessage', 'Password successfully changed');
                    redirect('/admin/home/changepassword/');
                }
            }
        }
    }

    // admin change email
    function changeemail() {
        $this->loginCheck('admin/home/changeemail');
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push('<i class="fa fa-cogs"></i> Configuration', site_url('section'), false);
        $this->breadcrumbs->push('Change Email', site_url('section'));
        $id = $this->session->userdata('adminId');
        $this->form_validation->set_rules('email', 'New Email', "trim|required|matches[cemail]|valid_email|is_unique_again[tbl_users.email.$id]");
        $this->form_validation->set_message('is_unique_again', ' Email address is already registered in ' . SITE_TITLE);
        $this->form_validation->set_rules('cemail', 'Confirm Email', 'trim|required|valid_email');
        $this->form_validation->set_message('matches', 'The New Email does not match with Confirm email');
        $response = $this->users_model->checkEmail();
        if ($this->form_validation->run() == FALSE) {
            $data['old_email'] = $response[0]['email'];
            $data['title'] = "Change Email";

            $this->template->write_view('contents', 'admin/home/changeEmail', $data);
            $this->template->render();
        } else {
            if ($response[0]['email'] == $this->input->post('email')) {
                $this->session->set_flashdata('message', 'New Email should be different from old email');
                redirect('/admin/home/changeemail/');
            }
            $data = array('email' => $this->input->post('email'));
            $this->users_model->changeEmail($data);
            $this->session->set_flashdata('smessage', 'New Email successfully changed');
            redirect('/admin/home/changeemail/');
        }
    }

    function menupgrade() {
        $id = $this->session->userdata('adminId');
        $cond = "id ='" . $this->session->userdata('adminId') . "'";
        $select_fields = "tbl_users.*";
        $joins = array();
        $table = "tbl_users";
        $record = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
        if ($record['flag'])
            $data = array('flag' => '0');
        else
            $data = array('flag' => '1');

        $this->main_model->cruid_update($table, $data, $cond);
    }

    function getstate($country_id = "", $state_id = "") {
        $opt_all = $this->main_model->cruid_select_array("tbl_geo_states", "tbl_geo_states.id,name", $joins = array(), $cond = array("status" => 'A', 'country_id' => $country_id), "", array('field' => 'name', 'type' => 'asc'));
        $opt = array();
        $opt = "<option value=''>Please Select</option>";
        if (!empty($opt_all)) {
            foreach ($opt_all as $states) {
                $selected = "";
                if ($state_id == $states['id']) {
                    $selected = "selected = 'selected'";
                }
                $opt.= '<option ' . $selected . ' value="' . $states['id'] . '">' . $states['name'] . '</option>';
            }
        }
        echo $opt;
    }

    
    // admin dashboard page
    function dashboard_counters() {
        $this->loginCheck('admin/home/dashboard');

        $search_string = $this->input->post('fields');
        $match_date = null;
        $created = null;
        $created_at = null;
        $tccreated = null;

        if (!empty($search_string)) {
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));

                if ($val['name'] == 'from_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $match_date .= "  AND `match_date` >=". strtotime($search."00:00:00");
                    $created .= "  AND `created` >=". strtotime($search."00:00:00");
                    $tccreated .= "  AND `createdat` >=". strtotime($search."00:00:00");
                    $created_at .= "  AND `created_at` >=". strtotime($search."00:00:00");
                    $from_date =strtotime($search."00:00:00");
                }
                if ($val['name'] == 'to_date' and $val['value']) {
                    $search = str_ireplace("/", "-", $search);
                    $match_date .= "  AND `match_date` <=". strtotime($search."23:59:59");
                    $created .= "  AND `created` <=". strtotime($search."23:59:59");
                    $tccreated .= "  AND `createdat` <=". strtotime($search."23:59:59");
                    $created_at .= "  AND `created_at` <=". strtotime($search."23:59:59");
                    $to_date = strtotime($search."23:59:59");
                }
            }
        }else{
            $onemonth  = date('Y-m-d', strtotime('-1 month'));
            $toDay     = time();
            $from_date = $onemonth;
            $to_date   = $toDay;
            $match_date .= "  AND `match_date` >=". $onemonth;
            $match_date .= "  AND `match_date` <=". $toDay;
            $created    .= "  AND `created` >=". $onemonth;
            $created    .= "  AND `created` <=". $toDay;
            $tccreated    .= "  AND `createdat` >=". $onemonth;
            $tccreated    .= "  AND `createdat` <=". $toDay;
            $created_at .= "  AND `created_at` >=". $onemonth;
            $created_at .= "  AND `created_at` <=". $toDay;
        }
            $Up_match_date = " AND `match_date` >=". time();
            $newdata = array(
                'from_date'  => $from_date,
                'to_date'    => $to_date,
            );
            $this->session->set_userdata($newdata);
            //$this->session->set_flashdata($newdata);
        
        $data = array();
        $data['title'] = "Dashboard";
        $year = date('Y');


        $Up_m_record = $this->db->query("SELECT COUNT(IF(match_progress='F',1, NULL)) 'F' FROM `tbl_cricket_matches` WHERE is_deleted='N' $Up_match_date ")->row();

        $m_record = $this->db->query("SELECT COUNT(id) 'TNM', COUNT(IF(match_progress='L' OR match_progress='IR',1, NULL)) 'L', COUNT(IF(match_progress='R',1, NULL)) 'R', COUNT(IF(match_progress='AB',1, NULL)) 'AB' FROM `tbl_cricket_matches` WHERE is_deleted='N' $match_date ")->row();//`status`='A' AND

        $pending_pan_verification = $this->db->query("SELECT COUNT(IF(tcp.status='P' OR tcp.status='A',1, NULL)) 'pending_approved', COUNT(IF(tcp.status='P',1, NULL)) 'pending',COUNT(IF(tcp.status='A',1, NULL)) 'approved' FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id ) WHERE tc.paincard_id>0 AND `tc`.`is_deleted`='N' $tccreated ")->row();

        $pending_bank_verification = $this->db->query("SELECT COUNT(IF(tcb.status='P' OR tcb.status='A',1, NULL)) 'pending_approved', COUNT(IF(tcb.status='P',1, NULL)) 'pending',COUNT(IF(tcb.status='A',1, NULL)) 'approved' FROM `tbl_customers` tc LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id ) WHERE tc.bankdetail_id>0 AND `tc`.`is_deleted`='N' $tccreated ")->row();

        $withdrawdata = $this->db->query("SELECT SUM(IF(status='C',amount, 0)) 'withdraw_approved_amount',COUNT(IF(status='P' OR status='C',1, NULL)) 'pending_approved', COUNT(IF(status='P',1, NULL)) 'pending',COUNT(IF(status='C',1, NULL)) 'approved' FROM (`tbl_withdraw_requests`) WHERE customer_id>0  $created_at")->row();
        
        $total_customers = $this->db->query("SELECT COUNT(id) AS total_customers FROM `tbl_customers` WHERE `is_deleted`='N' $created")->row();
        $total_team = $this->db->query("SELECT COUNT(*) as totalteam FROM `tbl_cricket_teams` WHERE is_deleted='N' $created_at")->row();
        $totalplayers = $this->db->query("SELECT COUNT(*) as total_players FROM `tbl_cricket_players` WHERE is_deleted='N' $created_at")->row();

        $totalcontests = $this->db->query("SELECT COUNT(*) as total_contests FROM `tbl_cricket_contests` WHERE is_deleted='N' AND is_beat_the_expert = 'N' $created_at")->row();

        $data['matche_counters'] = array_merge( (array)$m_record,(array)$Up_m_record );
        $data['customers'] = array(
                                    'pan_pending_approved'  => $pending_pan_verification->pending_approved,
                                    'pan_pending'           => $pending_pan_verification->pending,
                                    'pan_approved'          => $pending_pan_verification->approved,
                                    'bank_pending_approved' => $pending_bank_verification->pending_approved,
                                    'bank_pending'          => $pending_bank_verification->pending,
                                    'bank_approved'         => $pending_bank_verification->approved,
                                    'withdraw_approved_amount'  => ($withdrawdata->withdraw_approved_amount)?$withdrawdata->withdraw_approved_amount:0,
                                    'withdraw_pending_approved' => $withdrawdata->pending_approved,
                                    'withdraw_pending'          => $withdrawdata->pending,
                                    'withdraw_approved'         => $withdrawdata->approved,
                                    'total_customers'           => $total_customers->total_customers,
                                    'total_team'                => $total_team->totalteam,
                                    'total_players'             => $totalplayers->total_players,
                                    'total_contests'            => $totalcontests->total_contests,
                                    'total_amount'              => 0,
                                    'pending_winner_declare'    => 0,
                                    );
            
            header("Content-type:application/json");
            echo json_encode($data);
    }
    
    public function ajax_new_match_get(){
        $query = $this->db->query("SELECT new_match_count FROM `tbl_games` WHERE `id` = 0 AND `status` = 'A' AND `is_deleted` = 'N'");
        if($query->num_rows() >0 ){
            $row = $query->row();
            echo $row->new_match_count;
        }else{
            echo 0;
        }
    }
    //End of the Class
}
