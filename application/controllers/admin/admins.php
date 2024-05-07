<?php

require_once('base.php');
class Admins extends Base {

    private $limit = 10;
    private $image = '';

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
        $config['base_url'] = base_url() . "admin/admins/ajax_index";
        $config['base_parent_url'] = base_url() . "admin/admins/index/";
        $config['div'] = '#middle-content';
        $config['per_page'] = $this->limit;
        $config['uri_segment'] = 4;
        $config['sort'] = "?field=" . $this->input->get('field') . "&sort=" . $this->input->get('sort');
        $joins = array();

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
        $table = "tbl_users";
        $condit = "tbl_users.parent_id = '".$this->session->userdata('adminId')."'";
        $select_fields = " ";
        //end parameter
        $search_string = $this->input->post('fields');
        if (!empty($search_string)) {
            $str = "";
            foreach ($search_string as $key => $val) {
                $search = addslashes(trim($val['value']));
                $array = explode(" ", $search);
                foreach ($array as $keyword) {
                    $str[] = "`firstname` LIKE '%" . $keyword . "%' OR `lastname` LIKE '%" . $keyword . "%' OR  `email` LIKE '%" . $keyword . "%'";
                }


                if ($val['name'] == 'search' and $val['value']) {
                    $condit .= "  AND (" . implode(" OR ", $str) . ")";
                }
                if ($val['name'] == 'date' and $val['value']) {
                    $condit .= "  AND (`created` LIKE '%" . $search . "%')";
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

        $data['current_url'] = base_url() . "admin/admins/index/" . ($offset ? $offset : "");
        $data['base_url'] = base_url() . "admin/admins/ajax_index";

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
        $this->load->view('admin/admins/ajax_index', $data);
    }

    public function index($offset = 0) {
        $this->loginCheck('admin/admins/index');

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Admins", site_url('/admin/admins'), false);
        $this->breadcrumbs->push("Admins List", site_url('section'));

        ob_start();
        $this->ajax_index($offset);
        $initial_content = ob_get_contents();
        ob_end_clean();
        $data['title'] = "Admins List";
        $data['ajax_content'] = $initial_content;
        $data['ajax_url'] = base_url() . "admin/admins/ajax_index";
        $data['this_url'] = base_url() . "admin/admins/index";
        $this->template->load('front', 'user/usersList', $data);

        $this->template->write_view('contents', 'admin/admins/index', $data);
        $this->template->render();
    }

// create slug for security purpose
    public function createSlug($string) {
        $old_pattern = array("/[^a-zA-Z0-9]/", "/_+/", "/_$/");
        $new_pattern = array("-", "-", "");
        return strtolower(preg_replace($old_pattern, $new_pattern, $string)) . rand(9999, 9999999) . time();
    }

// generate random password here
    function generate_password($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

// add new user
    public function add() {
        $this->loginCheck('admin/admins/add');
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Admins", site_url('/admin/admins'));
        $this->breadcrumbs->push('Add Admin', site_url('section'));

        $redirect = '/admin/admins/index';
        $data['title'] = "Add New Admin";

        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
        $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email Address', 'trim|valid_email|required|is_unique[tbl_users.email]');
        $this->form_validation->set_message('is_unique', 'The e-mail address is already registered in ' . SITE_TITLE);
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]|max_length[15]|matches[cpassword]|callback_valid_pass');
        $this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|required');
        $this->form_validation->set_rules('mobile', 'Phone Number', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->template->write_view('contents', 'admin/admins/add', $data);
            $this->template->render();
        } else {

            $name = $this->input->post('firstname');
            $password = $this->input->post('password');
            $email = $this->input->post('email');
            $data = array(
                'parent_id' => $this->session->userdata('adminId'),
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
                'email' => trim($email),
                'slug' => $this->createSlug($this->input->post('firstname')),
                'password' => md5($password),
                'mobile' => $this->input->post('mobile'),
                'status' => 'A',
                'created' => time(),               
                'usergroup' => $this->input->post('usergroup'),
            );
            $table = "tbl_users";
            $business_id = $this->main_model->cruid_insert($table, $data);

            $config['protocol'] = 'sendmail';
            $config['mailuser_type'] = 'html';
            $config['charset'] = 'utf-8';
            $config['newline'] = "\r\n";
            $config['crlf'] = "\r\n";
            $config['send_multipart'] = FALSE;
            $this->load->library('email', $config);
            $this->email->set_mailtype("html");

            $mail_data['name'] = $this->input->post('firstname');
            $mail_data['email'] = $this->input->post('email');
            $mail_data['password'] = $password;
            $mail_data['text'] = "Your account has been created by site admin";

            $this->load->library('parser');
            $msg = $this->parser->parse('email/template', $mail_data, TRUE);
            $this->email->from(FORM_EMAIL, SITE_TITLE);
            $this->email->to($email);
            $this->email->subject("Your account has been created successfully by site admin");
            $this->email->message($msg);
            $this->email->send();
            $this->session->set_userdata('smessage', 'Admin Successfully added');
            redirect($redirect);
        }
    }

    function valid_pass($password) {
        $Count = 0;
        if (preg_match("/[0-9]/", $password) > 0) {
            $Count++;
        }
        if (preg_match("/[A-Z]/", $password) > 0) {
            $Count++;
        }
        if (preg_match("/[a-z]/", $password) > 0) {
            $Count++;
        }
        if (preg_match("/[@#$%^&*()\-_=+{};:,<.>]/", $password) > 0) {
            $Count++;
        }

        if ($Count < 3) {
            $this->form_validation->set_message('valid_pass', 'Password must be 8 to 15 characters and contain at least one special character,one uppercase, one lowercase and one number.');
            return FALSE;
        } else {
            return TRUE;
        }
    }

// edit user detail
    public function edit() {

        $user_name = $this->uri->segment(4);
        $this->loginCheck('admin/admins/edit/' . $user_name);
        $this->checkUser();

        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> Dashboard', site_url("/admin"));
        $this->breadcrumbs->push("<i class='fa fa-user'></i> Admins", site_url('/admin/admins'));
        $this->breadcrumbs->push('Edit Admin', site_url('section'));

        $url = $this->input->get("return");
        $table = "tbl_users";
        $cond = "slug ='" . $user_name . "'";
        $select_fields = "tbl_users.*";
        $joins = array();

        $user_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);

        if (!empty($user_detail)) {
            $id = $user_detail['id'];
            $data['user_detail'] = $user_detail;
            $data['title'] = "Update Admins Details";
            $this->form_validation->set_rules('firstname', 'First Name', 'trim|required');
            $this->form_validation->set_rules('lastname', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', "trim|required|is_unique_again[tbl_users.email.$id]");
            $this->form_validation->set_message('is_unique_again', 'The e-mail address is already registered in ' . SITE_TITLE . ". Please try different");
            $this->form_validation->set_rules('password', 'Password', 'trim|matches[cpassword]');
            $this->form_validation->set_rules('cpassword', 'Confirm password', 'trim');
            $this->form_validation->set_rules('mobile', 'Phone Number', 'trim|required|numeric');
            if ($this->form_validation->run() == FALSE) {
                $this->template->write_view('contents', 'admin/admins/edit', $data);
                $this->template->render();
            } else {
                $email = $this->input->post('email');
                $password = $this->input->post('password');
                $data = array(
                    'firstname' => $this->input->post('firstname'),
                    'lastname' => $this->input->post('lastname'),
                    'email' => trim($email),
                    'password' => md5($password),
                    'mobile' => $this->input->post('mobile'),
                    //'city' => $this->input->post('city'),
                    'usergroup' => $this->input->post('usergroup'),
                );
                if ($user_detail['password'] <> md5($password) and $password) {

// send email to Admins
                    $data['password'] = md5($password);
                    $config['protocol'] = 'sendmail';
                    $config['mailuser_type'] = 'html';
                    $config['charset'] = 'utf-8';
                    $config['newline'] = "\r\n";
                    $config['crlf'] = "\r\n";
                    $config['send_multipart'] = FALSE;
                    $this->load->library('email', $config);
                    $this->email->set_mailtype("html");

                    $mail_data['name'] = $this->input->post('firstname');
                    $mail_data['email'] = $this->input->post('email');
                    $mail_data['new_password'] = $password;

                    $mail_data['text'] = "Your password has been changed by Admin";
                    $this->load->library('parser');
                    $msg = $this->parser->parse('email/template', $mail_data, TRUE);
                    $this->email->from(FORM_EMAIL, SITE_TITLE);
                    $this->email->to($user_detail['email']);
                    $this->email->subject("Your password has been changed. ");
                    $this->email->message($msg);
                    $this->email->send();
                }
                if ($user_detail['email'] <> $email) {

// send email to Admins
                    $data['email'] = $email;
                    $config['protocol'] = 'sendmail';
                    $config['mailuser_type'] = 'html';
                    $config['charset'] = 'utf-8';
                    $config['newline'] = "\r\n";
                    $config['crlf'] = "\r\n";
                    $config['send_multipart'] = FALSE;
                    $this->load->library('email', $config);
                    $this->email->set_mailtype("html");
                    $mail_data['name'] = $this->input->post('firstname');
                    $mail_data['new_email'] = $this->input->post('email');
                    $mail_data['password'] = $this->input->post('password');
                    $mail_data['text'] = "Your login email address has been changed by Admin";
                    $this->load->library('parser');
                    $msg = $this->parser->parse('email/template', $mail_data, TRUE);
                    $this->email->from(FORM_EMAIL, SITE_TITLE);
                    $this->email->to(array($user_detail['email'], $email));
                    $this->email->subject("Your login email address has been changed. ");
                    $this->email->message($msg);
//echo $msg;exit;
                    $this->email->send();
                }

                $this->main_model->cruid_update($table, $data, $cond);
                $this->session->set_userdata('smessage', 'Admin Details Successfully updated');
                redirect($url);
            }
        } else {
            $this->session->set_userdata('message', 'Sorry, this Admin not available');
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
        $this->loginCheck('admin/admins/deleteuser/' . $id);
        $this->checkUser();

        $this->main_model->cruid_delete('tbl_users', array('slug' => $id));
        $this->session->set_userdata('smessage', 'Admins Successfully deleted');
    }

// activate all Admins
    public function activateall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
            for ($i = 0; $i < count($checked); $i++) {

                $table = "tbl_users";
                $cond = "slug ='" . $checked[$i] . "'";
                $select_fields = "tbl_users.*";
                $joins = array();
                $uset_detail = $this->main_model->cruid_select($table, $select_fields, $joins, $cond);
                $table = "tbl_users";
                $cond = array(
                    'slug' => $checked[$i],
                );
                $data = array(
                    'status' => 'A',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', 'Selected Admins successfully activated');
    }

//  deactivate all Admins
    public function deactivateall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
            for ($i = 0; $i < count($checked); $i++) {
                $table = "tbl_users";
                $cond = array(
                    'slug' => $checked[$i],
                );
                $data = array(
                    'status' => 'D',
                );
                $this->main_model->cruid_update($table, $data, $cond);
            }
        }
        $this->session->set_userdata('smessage', 'Selected Admins successfully deactivated');
    }

// activate user profile
    function activate($slug) {
        $url = $this->input->get("return");
        $table = "tbl_users";
        $cond = array(
            'slug' => $slug,
        );
        $data = array(
            'status' => 'A',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', 'Selected Admin successfully activated');
    }

// deactivate user profile
    function deactivate($slug) {
        $url = $this->input->get("return");
        $table = "tbl_users";
        $cond = array(
            'slug' => $slug,
        );
        $data = array(
            'status' => 'D',
        );
        $this->main_model->cruid_update($table, $data, $cond);
        $this->session->set_userdata('smessage', 'Selected Admin successfully deactivated');
    }

// delete all Admins
    public function deleteall($current_url) {
        $this->checkUser();
        $checked = $this->input->post('check');
        if (empty($checked)) {
            $this->session->set_userdata('message', 'Please select atleast one Admin');
        } else {
            for ($i = 0; $i < count($checked); $i++) {
                $this->main_model->cruid_delete('tbl_users', array('slug' => $checked[$i]));
            }
        }
        $this->session->set_userdata('smessage', 'Selected Admins successfully deleted');
    }

    public function export() {

        $table = "tbl_users";
        $cond = array();
        $admins = $this->main_model->cruid_select_array($table, "", $joins_2 = array(), $cond);

        $this->load->helper('download');
        $this->load->library('PHPExcel');
        $this->load->library('PHPExcel/IOFactory');

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("title")
                ->setDescription("description");

        $objPHPExcel->getSheet(0)->setTitle('Admins database');
        $objPHPExcel->createSheet(1);

        $styleArray = array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => '95B3D7'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->applyFromArray(
                array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'size' => 12,
                    'italic' => false,
                    'color' => array(
                        'rgb' => '1F4981'
                    )
                )
        );

        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(6)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(9)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(10)->setRowHeight(20);

        $row = 1;
        $objPHPExcel->getActiveSheet()
                ->setCellValue('A' . $row, 'id')
                ->setCellValue('B' . $row, 'First Name')
                ->setCellValue('C' . $row, 'Last Name')
                ->setCellValue('D' . $row, 'Email Address')
                ->setCellValue('E' . $row, 'Phone Number')
                ->setCellValue('F' . $row, 'Post code')
                ->setCellValue('G' . $row, 'Gender')
                ->setCellValue('H' . $row, 'Date of Birth')
                ->setCellValue('I' . $row, 'Marital Status')
                ->setCellValue('J' . $row, 'Created');
        $row++;
        $i = 1;
        foreach ($admins as $value) {

            // count no of photos
            $i++;
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $row, $value['id'])
                    ->setCellValue('B' . $row, $value['firstname'])
                    ->setCellValue('C' . $row, $value['lastname'])
                    ->setCellValue('D' . $row, $value['email'])
                    ->setCellValue('E' . $row, $value['mobile'])
                    ->setCellValue('G' . $row, $value['gender'])
                    ->setCellValue('H' . $row, $value['dob'])
                    ->setCellValue('J' . $row, date("M d, Y h:i a", strtotime($value['created'])));
            $row++;

            $styleArray0 = array(
                'borders' => array(
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ffffff')
                )
            );
            $styleArray = array(
                'borders' => array(
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'E4E4DC')
                )
            );
            $styleArray1 = array(
                'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            if ($i % 2 == 0) {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->applyFromArray($styleArray0);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $i)->applyFromArray($styleArray0);
            } else {
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $i)->applyFromArray($styleArray);
            }
        }

        $date = date('F_d_Y_h_i_a');
        $filename = 'Company_List_' . $date . '.xlsx'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save("php://output");
    }

    function test() {
        $data = array(
            'name' => 'test'
        );
        $this->load->view('views/test', $data);
    }

}

/* End of file admins.php */
/* Location: ./application/controllers/admin/admins.php */