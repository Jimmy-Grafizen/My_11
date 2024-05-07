<?php

Class Main_model extends CI_Model {

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

    public function cruid_select($table, $select_fields, $joins = array(), $cond = array(), $group_by = "", $order_by = array(),$protect_identifiers = 0) {
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
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }
        if (!empty($order_by)) {
            $this->db->order_by($table . '.' . $order_by['field'], $order_by['type']);
        }

        if($protect_identifiers){
            $this->db->_protect_identifiers=false;
        }


        $sql = $this->db->get();
        return $sql->row_array();
    }

    public function cruid_select_array($table, $select_fields, $joins = array(), $cond = array(), $group_by = "", $order_by = "", $limit = '', $protect_identifiers = 0) {
        $this->db->select($select_fields);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }

        if ($limit)
            $this->db->limit($limit);
        if (!empty($order_by)) {
            $this->db->order_by($table . '.' . $order_by['field'], $order_by['type']);
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }

        if($protect_identifiers){
            $this->db->_protect_identifiers=false;
        }

        $sql = $this->db->get();
        // echo $this->db->last_query(); exit;
        return $sql->result_array();
    }

    public function tabel_list($num, $offset, $joins = array(), $order_by = array(), $table, $select_fields = '', $condit = array(), $group_by = "", $order_by_other = array()) {
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
        if (!empty($order_by_other)) {
            $this->db->order_by($order_by_other['field'], $order_by_other['type']);
        }
        if (!empty($condit)) {
            $this->db->where($condit);
        }
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }
        $this->db->limit($num, $offset);
        $sql = $this->db->get();
//        echo $this->db->last_query();
        $result['list'] = $sql->result();
        $result['rows'] = $this->db->query('SELECT FOUND_ROWS() as total')->row_array();

        return $result;
    }

    public function cruid_select_array_order($table, $select_fields, $joins = array(), $cond = array(), $order_by = array(), $limit = '', $order_by_other = array(), $group_by = "", $order_by_other1 = array()) {
        $this->db->select($select_fields);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($order_by)) {
            $this->db->order_by($table . '.' . $order_by['field'], $order_by['type']);
        }
        if (!empty($order_by_other)) {
            $this->db->order_by($table . '.' . $order_by_other['field'], $order_by_other['type']);
        }
        if (!empty($order_by_other1)) {
            $this->db->order_by($order_by_other1['field'], $order_by_other1['type']);
        }
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        if ($limit)
            $this->db->limit($limit);

        $sql = $this->db->get();
        // echo $this->db->last_query(); exit;
        return $sql->result();
    }

    public function cruid_select_array_order_2($table, $select_fields, $joins = array(), $cond = array(), $order_by = array(), $limit = '', $group_by = '', $table2 = '') {
        $this->db->select($select_fields);
        $this->db->from($table);
        if (!empty($joins)) {
            foreach ($joins as $k => $v) {
                $this->db->join($v['table'], $v['condition'], $v['jointype']);
            }
        }
        if (!empty($order_by)) {
            $this->db->order_by($table2 . '.' . $order_by['field'], $order_by['type']);
        }
        if (!empty($cond)) {
            $this->db->where($cond);
        }
        if (!empty($group_by)) {
            $this->db->group_by($group_by);
        }
        if ($limit)
            $this->db->limit($limit);

        $sql = $this->db->get();
        // echo $this->db->last_query(); exit;
        return $sql->result();
    }

    public function getstates($type = "") {
        $this->db->select('id, name');
        $query = $this->db->get('tbl_geo_states');
        $states = array(
            '' => 'Please Select'
        );
        $record = $query->result_array();
        if (!empty($record)) {
            foreach ($record as $key) {
                $states[$key['name']] = $key['name'];
            }
        }
        return $states;
    }

    function getsortclass($field, $compare_field, $sort_type) {
        if ($field == $compare_field) {
            if ($sort_type == 'asc')
                return 'sorting_desc';
            else
                return 'sorting_asc';
        } else {
            return "sorting";
        }
    }

    public function getcountryname($id = "") {
        $this->db->select('name');
        $query = $this->db->get_where('tbl_geo_countries', array('id' => $id));
        $record = $query->row_array();
        if (!empty($record)) {
            return strtolower($record['name']);
        } else {
            return "";
        }
    }

    public function checkstocknumber($stock = "") {
        $this->db->select('count(id) as counter');
        $query = $this->db->get_where('tbl_diamond', array('stock_number' => $stock));
        $record = $query->row_array();
        return $record['counter'];
    }

    public function getcountryId($id = "") {
        $this->db->select('id');
        $query = $this->db->get_where('tbl_geo_countries', array('name' => $id));
        $record = $query->row_array();
        if (!empty($record)) {
            return $record['id'];
        } else
            return 0;
    }

    public function getstatename($id = "") {
        $this->db->select('name');
        $query = $this->db->get_where('tbl_geo_states', array('id' => $id));
        $record = $query->row_array();
        if (!empty($record)) {
            return strtolower($record['name']);
        } else
            return "";
    }


    public function selectFcmKey($send_to) {
        if ($send_to=='DELIVERYMAN') {
            $fcmKey=FCM_KEY_FOR_DELIVERYMAN;
        } else if ($send_to=='CUSTOMER') {
            $fcmKey=FCM_KEY_FOR_CUSTOMER;
        } else {
            $fcmKey=FCM_KEY_FOR_BRANCH;
        }
        return $fcmKey;
    }
    
   /* public function sendFCMPushNotification($message, $registration_ids, $alert_message='EL-GURU NOTIFICATIONS', $noti_type, $send_to, $order_id=0) {
        $send_to=DELIVERYMAN / BRANCH / CUSTOMER 
        $noti_message=json_encode(array('message'=>$message, 'noti_type'=>$noti_type));
        $url = FIRE_BASE_URL;       
        $API_KEY = $this->selectFcmKey($send_to);
        $fcmurl = FIRE_BASE_URL;
        $sound = "default";
        $message = json_decode($message, TRUE);
        $fields = array(
            "registration_ids" => array(
                $registration_ids
            ),
            "data" => array(
                "message" => $noti_message,
                "noti_type" => $noti_type
            ),
            "notification" => array(
                "title" => "EL-GUERO",
                "body" => $message,
                "sound" => $sound,
                "priority"=>"high"
            ),
            "priority"=>"high"
        );
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }*/

    public function sendFCMPushNotification($message, $registration_ids, $alert_message='EL-GUERO2 NOTIFICATIONS', $noti_type, $send_to, $order_id=0) {
        /* $send_to=DELIVERYMAN / BRANCH / CUSTOMER */
        $url = FIRE_BASE_URL;       
        $API_KEY = $this->selectFcmKey($send_to);
        $fcmurl = FIRE_BASE_URL;
        $sound = "default";
        //$message = json_decode($message, TRUE);
        $fields = array(
            "registration_ids" => $registration_ids,
            "data" => array(
                "title" => "EL-GUERO2",
                "body" => $message,
                "noti_type" => $noti_type,
                'order_id' => $order_id
            ),
            "notification" => array(
                "title" => "EL-GUERO2",
                "body" => $message,
                "sound" => $sound,
                "priority" => "high"
            ),
            "priority" => "high"
        );
        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    function timeago($time_ago) {
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds = $time_elapsed;
        $minutes = round($time_elapsed / 60);
        $hours = round($time_elapsed / 3600);
        $days = round($time_elapsed / 86400);
        $weeks = round($time_elapsed / 604800);
        $months = round($time_elapsed / 2600640);
        $years = round($time_elapsed / 31207680);
// Seconds
        if ($seconds <= 60) {
            echo "$seconds seconds ago";
        }
//Minutes
        else if ($minutes <= 60) {
            if ($minutes == 1) {
                echo "one minute ago";
            } else {
                echo "$minutes minutes ago";
            }
        }
//Hours
        else if ($hours <= 24) {
            if ($hours == 1) {
                echo "an hour ago";
            } else {
                echo "$hours hours ago";
            }
        }
//Days
        else if ($days <= 7) {
            if ($days == 1) {
                echo "yesterday";
            } else {
                echo "$days days ago";
            }
        }
//Weeks
        else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                echo "a week ago";
            } else {
                echo "$weeks weeks ago";
            }
        }
//Months
        else if ($months <= 12) {
            if ($months == 1) {
                echo "a month ago";
            } else {
                echo "$months months ago";
            }
        }
//Years
        else {
            if ($years == 1) {
                echo "one year ago";
            } else {
                echo "$years years ago";
            }
        }
    }

}

?>
