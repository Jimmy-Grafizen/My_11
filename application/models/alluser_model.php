<?php

class User_model extends CI_MOdel {

    public function __construct() {
        parent::__construct();
    }

    public function userRegistration($data) {
        $query = $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function checkUsername($username) {
        $query = $this->db->get_where('users', array('username' => $username));
        return $query->num_rows();
    }

    public function checkUser($id) {
        $this->db->select('id');
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->num_rows();
    }

    public function checkEmail($email) {
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->num_rows();
    }

    public function usersList($num, $offset) {
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('users', $num, $offset);
        return $query->result();
    }

    public function get_users() {
        $query = $this->db->get('users');
        $users = $query->result_array();
        $new = array();
        for ($i = 0; $i < count($users); $i++) {
            $key = $users[$i]['id'];
            $value = $users[$i]['username'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getCountries() {
        $this->db->order_by('country_name', 'asc');
        $query = $this->db->get('countries');

        $countries = $query->result_array();
        $new = array();
        $new[''] = 'Select Country';
        for ($i = 0; $i < count($countries); $i++) {
            $key = $countries[$i]['id'];
            $value = $countries[$i]['country_name'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getCountrieswithcode() {
        $this->db->order_by('country_name', 'asc');
        $query = $this->db->get('countries');

        $countries = $query->result_array();
        $new = array();
        $new[''] = 'Select Country';
        for ($i = 0; $i < count($countries); $i++) {
            $key = $countries[$i]['iso2'];
            $value = $countries[$i]['country_name'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getStates() {
        $query = $this->db->get('states');
        $states = $query->result_array();
        $new = array();
        $new[''] = 'Select State';
        for ($i = 0; $i < count($states); $i++) {
            $key = $states[$i]['id'];
            $value = $states[$i]['state_name'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getStatesSpecific($country_id) {
        $query = $this->db->get_where('states', array('country_code' => $country_id));
        $states = $query->result_array();
        $new = array();
        $new[''] = 'Select State';
        for ($i = 0; $i < count($states); $i++) {
            $key = $states[$i]['id'];
            $value = $states[$i]['state_name'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getCities() {
        $query = $this->db->get('cities');
        $cities = $query->result_array();
        $new = array();
        $new[''] = 'Select City';
        for ($i = 0; $i < count($cities); $i++) {
            $key = $cities[$i]['id'];
            $value = $cities[$i]['city_name'];
            $new[$key] = $value;
        }
        return $new;
    }

    public function getCountryName($id) {
        $this->db->select('country_name');
        $query = $this->db->get_where('countries', array('id' => $id));
        $country = $query->row_array();
        if (!empty($country)) {
            return $country['country_name'];
        } else {
            return 'N/A';
        }
    }
    public function getCountryfromCode($id) {
        $this->db->select('country_name');
        $query = $this->db->get_where('countries', array('iso2' => $id));
        $country = $query->row_array();
        if (!empty($country)) {
            return $country['country_name'];
        } else {
            return 'N/A';
        }
    }

    public function getCountryCode($id) {
        $this->db->select('iso2');
        $query = $this->db->get_where('countries', array('id' => $id));
        $country = $query->row_array();
        if (!empty($country)) {
            return $country['iso2'];
        } else {
            return '';
        }
    }

    public function getStateName($id) {
        $this->db->select('state_name');
        $query = $this->db->get_where('states', array('id' => $id));
        $country = $query->row_array();
        if (!empty($country)) {
            return $country['state_name'];
        } else {
            return 'N/A';
        }
    }

    public function getUsernameAndEmail($id = NULL) {
        $this->db->select('username, email, firstname, lastname, image');
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->row_array();
    }

    public function userDetail($user_name) {
        $query = $this->db->get_where('users', array('username' => $user_name));
        return $query->result_array();
    }

    public function userName($id) {
        $this->db->select('firstname, username, lastname');
        $query = $this->db->get_where('users', array('id' => $id));
        $array = $query->row_array();
        if ($array['firstname'] <> NULL) {
            return $array['firstname'] . ' ' . $array['lastname'];
        } else {
            return $array['username'];
        }
    }

    public function editUser($data, $user_name) {
        $this->db->where('username', $user_name);
        $this->db->update('users', $data);
    }

    public function editUser_front($data, $id) {
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    public function deleteUser($id) {
        $this->db->delete('users', array('id' => $id));
    }

    public function activateUser($id) {
        $this->db->where('username', $id);
        $this->db->update('users', array('status' => '1'));
    }

    public function deactivateUser($id) {
        $this->db->where('username', $id);
        $this->db->update('users', array('status' => '0'));
    }

    public function searchUsers($search, $num, $offset) {

        $q = "SELECT * FROM (`tbl_users`)";
        $search = addslashes($search);
        if ($search <> 'all') {
            $q .= " WHERE (`firstname` LIKE '%" . $search . "%' OR `email` LIKE '%" . $search . "%' OR `lastname` LIKE '%" . $search . "%' )";
        }
        $q .= " LIMIT " . $num;
        if ($offset) {
            $q .= ',' . $offset;
        }
        $result1 = $this->db->query($q);
        return $result1->result();
    }

    public function countUsers() {
        $this->db->select('id');
        $query = $this->db->get_where('users');
        return $query->num_rows();
    }

    public function getUserName($id) {
        $this->db->select('username');
        $query = $this->db->get_where('users', array('id' => $id));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['username'];
        } else {
            return '';
        }
    }

    public function getUserImage($id) {
        $this->db->select('image');
        $query = $this->db->get_where('users', array('id' => $id));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['image'];
        } else {
            return '';
        }
    }

    public function get_user_id($name) {
        $this->db->select('id');
        $query = $this->db->get_where('users', array('username' => $name));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['id'];
        } else {
            return '';
        }
    }

    public function countsearchUsers($search) {

        $q = "SELECT id FROM (`tbl_users`) ";
        $search = addslashes($search);
        if ($search <> 'all') {
            $q .= " WHERE (`firstname` LIKE '%" . $search . "%' OR `email` LIKE '%" . $search . "%' OR `lastname` LIKE '%" . $search . "%' )";
        }
        $result1 = $this->db->query($q);
        return $result1->num_rows();
    }

}
