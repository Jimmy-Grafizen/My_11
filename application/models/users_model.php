<?php

Class Users_model extends CI_Model {

   

    
    public function adminLogin() {
        $username = $this->input->post('username');
        $pasword = $this->input->post('password');
        $this->db->select('tbl_users.*, tbl_groups.permissions');
        $this->db->join('tbl_groups', "tbl_groups.id=tbl_users.usergroup", 'inner');
        $query = $this->db->get_where('tbl_users', array('email' => $username, 'password' => md5($pasword), 'status' => 'A'));
        $result = $query->row_array();
        if (count($result) == 0) {
            return false;
        } else {
//            echo $this->input->post('keep-logged') ;die;
            if ($this->input->post('keep-logged') == '1') {
                $this->session->sess_expiration = 500000;
                $this->session->set_userdata('username', $username);
                $this->session->set_userdata('password', $pasword);
            } else {
                $this->session->unset_userdata('username');
                $this->session->unset_userdata('password');
                $this->session->sess_expiration = 500000;
            }
            $this->session->set_userdata('adminId', $result['id']);
            $this->session->set_userdata('loginUser', $result);


            // Get user IP Address
            if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }


            // Update IP address for every login
            $data = array('lastlogin' => time(), 'ip' => $ip);
            $cond = array(
                'id' => $result['id'],
            );
            $this->main_model->cruid_update("tbl_users", $data, $cond);


            return true;
        }
    }

    public function admincheckactive() {
        $username = $this->input->post('username');
        $pasword = $this->input->post('password');
        $query = $this->db->get_where('users', array('email' => $username, 'password' => md5($pasword)));
        $result = $query->row_array();
        return $result;
    }

    public function admin_check_active() {
        $this->db->select('inactive_time');
        $this->db->from('users');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function adminDetail_mail() {

        $this->db->select('email');
        $query = $this->db->get_where('users', array('type' => 'users'));
        return $query->row_array();
    }

    public function inactive_admin() {
        $data = array(
            'inactive_time' => time(),
        );
        $this->db->update('users', $data);
    }

    public function update_check_active() {
        $data = array(
            'inactive_time' => '',
        );
        $this->db->update('users', $data);
    }

    public function adminDetail($email = NULL) {
        $email = $this->input->post('email');
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->row_array();
    }

    public function adminfulldetail() {
        $query = $this->db->get_where('users', array('type' => 'users'));
        return $query->row_array();
    }

    public function checkPassword($password) {
        $id = $this->session->userdata('adminId');
        $query = $this->db->get_where('users', array('id' => $id, 'password' => $password));
        return $query->result_array();
    }

    public function changePassword($data) {
        $id = $this->session->userdata('adminId');
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    public function changePasswordFromId($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    public function paypalEmailDetail() {
        $query = $this->db->get('paypal_email');
        return $query->result_array();
    }

    public function updatePaypalEmail($data) {
        $this->db->update('paypal_email', $data);
    }

    public function checkEmail() {
        $id = $this->session->userdata('adminId');
        $this->db->select('email');
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->result_array();
    }

    public function changeEmail($data) {
        $id = $this->session->userdata('adminId');
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }
     public function changeCounter($data) {
        //$id = $this->session->userdata('adminId');
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    ////*Common functions*/////

    public function cruid_update($table, $data = array(), $cond = array()) {
        $this->db->where($cond);
        $this->db->update($table, $data);
    }

    public function cruid_delete($table, $cond = array()) {
        $this->db->where($cond);
        $this->db->delete($table);
    }

    public function cruid_insert($table, $data = array()) {
        $query = $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function cruid_select($table, $select_fields, $joins = array(), $cond = array()) {
        $this->db->select($select_fields);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        $sql = $this->db->get();
        return $sql->row_array();
    }

    public function cruid_select_array($table, $select_fields, $joins = array(), $cond = array()) {
        $this->db->select($select_fields);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        $sql = $this->db->get();
        // echo $this->db->last_query(); exit;
        return $sql->result_array();
    }

    public function tabel_list($num, $offset, $joins = array(), $order_by = array(), $table, $select_fields = '', $condit = array()) {
        $this->db->select("SQL_CALC_FOUND_ROWS $table.* $select_fields", FALSE);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($order_by)) {
            $this->db->order_by($table . '.' . $order_by['field'], $order_by['type']);
        }
        if (!empty($condit)) {
            $this->db->where($condit);
        }
        $this->db->limit($num, $offset);
        $sql = $this->db->get();
        //echo $this->db->last_query(); exit; 
        $result['list'] = $sql->result();
        $result['rows'] = $this->db->query('SELECT FOUND_ROWS() as total')->row_array();

        return $result;
    }

    function getEvents($date) {
        $this->db->select("tbl_events.*, tbl_event_type.event_name, tbl_employees.first_name, tbl_employees.last_name, tbl_employees.id as employee_id, tbl_employees.anonymous_code");
        $this->db->join('tbl_event_type', "tbl_event_type.id=tbl_events.event_type_id", 'inner');
        $this->db->join('tbl_package', "tbl_package.id=tbl_events.package_id", 'inner');
        $this->db->join('tbl_employees', "tbl_employees.id=tbl_events.employee_id", 'left');
        $this->db->join('tbl_users', "tbl_users.id=tbl_events.client_id", 'inner');
        $query = $this->db->get_where('tbl_events', array('tbl_events.date_of_event' => $date, 'tbl_events.type_stand' => 'N'));
        return $query->result_array();
    }

    function getEventsEmployees($date) {
        $this->db->select("tbl_employees.first_name, tbl_employees.client_id, tbl_employees.id as employee_id, tbl_employees.last_name, tbl_employees.anonymous_code");
        $this->db->where("tbl_employees.birthday like '" . $date . "%'");
        $this->db->join('tbl_users', "tbl_users.id=tbl_employees.client_id", 'inner');
        $query = $this->db->get("tbl_employees");
        return $query->result_array();
    }

    function getStandardEvent($date) {
        $this->db->select("standard_events.name");
        $this->db->where("standard_events.date like '" . $date . "%'");
        $query = $this->db->get('standard_events');
        return $query->result_array();
    }

    function getEventsE($date) {
        $this->db->select("tbl_events.*, tbl_event_type.event_name, tbl_employees.first_name, tbl_employees.last_name, tbl_employees.id as employee_id, tbl_employees.anonymous_code");
        $this->db->join('tbl_event_type', "tbl_event_type.id=tbl_events.event_type_id", 'inner');
        $this->db->join('tbl_package', "tbl_package.id=tbl_events.package_id", 'inner');
        $this->db->join('tbl_employees', "tbl_employees.id=tbl_events.employee_id", 'left');
        $this->db->join('tbl_users', "tbl_users.id=tbl_events.client_id", 'inner');
        $query = $this->db->get_where('tbl_events', array('tbl_events.date_of_event' => $date, 'tbl_events.client_id' => $this->session->userdata('adminId'), 'tbl_events.type_stand' => 'N'));
        return $query->result_array();
    }

    function getEventsEmployeesE($date) {
        $this->db->select("tbl_employees.first_name, tbl_employees.client_id, tbl_employees.id as employee_id, tbl_employees.last_name, tbl_employees.anonymous_code");
        $this->db->where("tbl_employees.birthday like '" . $date . "%'");
//        $this->db->like('tbl_employees.birthday', $date);
        $this->db->join('tbl_users', "tbl_users.id=tbl_employees.client_id", 'inner');
        $query = $this->db->get_where("tbl_employees", array('tbl_employees.client_id' => $this->session->userdata('adminId')));
        return $query->result_array();
    }

    ////*Common functions*/////

    function getCompanyList() {
        $this->db->select('name, id');
        $query = $this->db->get_where('users', array('user_type' => 'Client'));
        $array = $query->result_array();
        $new = array();
        $new[''] = "Select";
        if (!empty($array)) {
            for ($i = 0; $i < count($array); $i++) {
                $new[$array[$i]['id']] = $array[$i]['name'];
            }
        }
        return $new;
    }

    function getEventType() {
        $this->db->select('event_name, id');
        $query = $this->db->get_where('tbl_event_type', array('status' => 'A'));
        $array = $query->result_array();
        $new = array();
        $new[''] = "Select";
        if (!empty($array)) {
            for ($i = 0; $i < count($array); $i++) {
                $new[$array[$i]['id']] = $array[$i]['event_name'];
            }
        }
        return $new;
    }

    function FBUsers($userData = array())
    {

        if(!empty($userData)){
            //check whether user data already exists in database with same oauth info
            $this->db->select($this->primaryKey);
            $this->db->from($this->tableName);
            $this->db->where(array('oauth_provider'=>$userData['oauth_provider'],'oauth_uid'=>$userData['oauth_uid']));
            $prevQuery = $this->db->get();
            $prevCheck = $prevQuery->num_rows();
            
            if($prevCheck > 0){
                $prevResult = $prevQuery->row_array();
                
                //update user data
                $userData['modified'] = date("Y-m-d H:i:s");
                $update = $this->db->update($this->tableName,$userData,array('id'=>$prevResult['id']));
                
                //get user ID
                $userID = $prevResult['id'];
            }else{
                //insert user data
                $userData['created']  = date("Y-m-d H:i:s");
                $userData['modified'] = date("Y-m-d H:i:s");
                $insert = $this->db->insert($this->tableName,$userData);
                
                //get user ID
                $userID = $this->db->insert_id();
            }
        }
        
        //return user ID
        return $userID?$userID:FALSE;        
    }


}

?>