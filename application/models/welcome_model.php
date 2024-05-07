<?php

class Welcome_model extends CI_MOdel {

    public function __construct() {
        parent::__construct();
    }

    public function matchCode($user_id, $code) {
        $this->db->select('id');
        $this->db->from('reset');
        $this->db->where(array('user_id' => $user_id, 'code' => $code));
        $query = $this->db->get();
        $result = $query->num_rows();
        return $result;
    }

    public function matchClaimCode($user_id, $code, $project_id) {
        $query = $this->db->get_where('reset', array('user_id' => $user_id, 'code' => $code, 'project_id' => $project_id));
        $result = $query->row_array();
        if (count($result) == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function countNewMessages() {
        $this->db->select('messages.id');
        $query = $this->db->get_where('messages', array('messages.to_id' => $this->session->userdata('userId'), 'read_status' => '0'));
        return $query->num_rows();
    }

    public function getAdminMail() {
        $this->db->select('email');
        $query = $this->db->get('admin');
        $result = $query->row_array();
        return $result['email'];
    }

    function deleteResetCode($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->delete('reset');
    }

    function deleteClaimResetCode($project_id, $code) {
        $this->db->where('project_id', $project_id);
        $this->db->where('code', $code);
        $this->db->delete('reset');
    }

    public function userLogin($username, $password) {
        $sql = "SELECT id 
                FROM (`tbl_users`) 
                WHERE (`email` = '" . $username . "' OR  `username` = '" . $username . "')
                AND `password` = '" . md5($password) . "' 
                AND `status` = '1'";
        $query = $this->db->query($sql);
        $result = $query->row_array();
        if (count($result) == 0) {
            return false;
        } else {
            if ($this->input->post('remember') == 'remember') {
                $this->session->sess_expiration = 500000;
                $this->session->set_userdata('usernameOrEmail', $username);
                $this->session->set_userdata('password', $password);
            } else {

                $this->session->unset_userdata('username');
                $this->session->unset_userdata('password');
                $this->session->sess_expiration = 3600;
            }
            $this->session->set_userdata('userId', $result['id']);

            $this->changePassword(array('last_login' => time()));
            return true;
        }
    }

    public function getId($username) {
        $this->db->select('id');
        $query = $this->db->get_where('users', array('username' => $username));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['id'];
        } else {
            return 0;
        }
    }

    public function getEmail($username) {
        $this->db->select('email');
        $query = $this->db->get_where('users', array('username' => $username));
        $array = $query->row_array();
        return $array['email'];
    }

    public function getAllUsername($name) {
        $this->db->select('username');
        $this->db->like('username', $name);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function userDetail($email = NULL) {
        $email = $this->input->post('email');
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->row_array();
    }

    public function get_userdetail() {
        $this->db->select('image,username');
        $this->db->from('users');
        $id = $this->session->userdata('userId');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->result();
        $user_name = $result['0']->username;
        $image = $result['0']->image;
        $this->session->set_userdata('username', $user_name);
        $this->session->set_userdata('image', $image);
    }

    public function resetCode($array) {
        $query = $this->db->get_where('tbl_reset', array('user_id' => $array['user_id']));
        $response = $query->row_array();
        if (empty($response)) {
            $this->db->insert('tbl_reset', $array);
        } else {
            $this->db->where('user_id', $array['user_id']);
            $update_array = array('code' => $array['code']);
            $this->db->update('tbl_reset', $update_array);
        }
    }

    public function resetCodeForProject($array) {
        $this->db->insert('tbl_reset', $array);
    }

    public function getUser($array = NULL) {
        $query = $this->db->get_where('users', $array);
        return $query->row_array();
    }

    public function fullUserDetail($id = NULL) {
        $this->db->select("users.*,countries.country_name");
        $this->db->from("users");
        $this->db->join("countries", "countries.id=users.country_id");
        $this->db->where('users.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function getUsernameAndEmail($id = NULL) {
        $this->db->select('username, email, firstname, lastname, image, facebook_id');
        $query = $this->db->get_where('users', array('id' => $id));
        return $query->row_array();
    }

    public function userReviews($id = NULL) {
        $this->db->select('reviews.id, users.id as user_id, reviews.review, reviews.rating, reviews.date, users.username');
        $this->db->join('users', 'users.id = reviews.employer_id', 'inner');
        $query = $this->db->get_where('reviews', array('writer_id' => $id));
        return $query->result_array();
    }

    public function userRatings($id, $writer_id) {
        $query = $this->db->get_where('rating', array('from' => $id, 'to' => $writer_id));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['rating'];
        } else {
            return 0;
        }
    }

    public function Avg_userRatings($writer_id) {

        $this->db->select('avg(rating) as avg');
        $query = $this->db->get_where('rating', array('to' => $writer_id));
        $array = $query->row_array();
        if (!empty($array)) {
            return $array['avg'];
        } else {
            return '';
        }
    }

    public function checkPassword($password) {
        $id = $this->session->userdata('userId');
        $query = $this->db->get_where('users', array('id' => $id, 'password' => md5($password)));
        return $query->result_array();
    }

    public function changePassword($data) {
        $id = $this->session->userdata('userId');
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }

    public function changeResetPassword($data, $user_id) {
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
    }

    public function getCountryName($country_id) {
        $this->db->select('country_name');
        $query = $this->db->get_where('countries', array('id' => $country_id));
        $country = $query->row_array();
        if (!empty($country))
            return $country['country_name'];
        else
            return '';
    }

    public function getStateName($state_id) {
        $this->db->select('province_name');
        $query = $this->db->get_where('province', array('id' => $state_id));
        $state = $query->row_array();
        if (!empty($state))
            return $state['province_name'];
        else
            return '';
    }

    public function getCityName($city_id) {
        $this->db->select('city_name');
        $query = $this->db->get_where('cities', array('id' => $city_id));
        $city = $query->row_array();
        if (!empty($city))
            return $city['city_name'];
        else
            return '';
    }

    public function getAllCategories() {
        $this->db->order_by('category_order', 'DESC');
        $query = $this->db->get_where('categories', array('parent_id' => 0, 'status' => '1'));
        return $query->result_array();
    }

    public function getPayment($id) {
        $this->db->select('sum(amount) as amount');
        $query = $this->db->get_where('payment', array('user_id' => $id));
        $array = $query->row_array();
        if ($array['amount']) {
            return $array['amount'];
        } else {
            return 0;
        }
    }

    public function getMessages($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $this->db->where("to", $email);
        $this->db->where("to_spam", '0');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function getSpamMessages($email, $offset, $type, $order) {
        $this->db->order_by('id', 'DESC');
        if ($order == 'read') {
            $this->db->where('to_read', '1');
        }
        if ($order == 'unread') {
            $this->db->where('to_read', '0');
        }
        $this->db->where("to", $email);
        $this->db->where("to_spam", '1');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function countSpamMessages($email, $order) {
        $this->db->select('id');
        if ($order == 'read') {
            $this->db->where('to_read', '1');
        }
        if ($order == 'unread') {
            $this->db->where('to_read', '0');
        }
        $query = $this->db->get_where('messages', array('to' => $email, 'to_spam' => '0'));
        return $query->num_rows();
    }

    public function getMessagesForSent($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get_where('messages', array('from' => $email), $offset, $type);
        return $query->result_array();
    }

    public function countMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('to' => $email, 'to_spam' => '0'));
        return $query->num_rows();
    }

    public function countSentMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('from' => $email, 'from_spam' => '0'));
        return $query->num_rows();
    }

    public function getReadedMessages($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $this->db->where("to", $email);
        $this->db->where("to_read", '1');
        $this->db->where("to_spam", '0');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function countReadedMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('to' => $email, 'to_spam' => '0', 'to_read' => '1'));
        return $query->num_rows();
    }

    public function getReadedSentMessages($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $this->db->where("from", $email);
        $this->db->where("from_read", '1');
        $this->db->where("from_spam", '0');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function countReadedSentMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('from' => $email, 'from_spam' => '0', 'from_read' => '1'));
        return $query->num_rows();
    }

    public function getunReadedMessages($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $this->db->where("to", $email);
        $this->db->where("to_read", '0');
        $this->db->where("to_spam", '0');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function countunReadedMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('to' => $email, 'to_spam' => '0', 'to_read' => '0'));
        return $query->num_rows();
    }

    public function getunReadedSentMessages($email, $offset, $type) {
        $this->db->order_by('id', 'DESC');
        $this->db->where("from", $email);
        $this->db->where("from_read", '0');
        $query = $this->db->get('messages', $offset, $type);
        return $query->result_array();
    }

    public function countunReadedSentMessages($email) {
        $this->db->select('id');
        $query = $this->db->get_where('messages', array('from' => $email, 'from_spam' => '0', 'from_read' => '0'));
        return $query->num_rows();
    }

    public function getSubCategories($id) {
        $this->db->where("parent_id =", $id);
        $this->db->order_by('category_order', 'DESC');
        $query = $this->db->get_where('categories', array('status' => '1'));
        return $query->result_array();
    }

    public function mailDetail($id) {
        $query = $this->db->get_where('messages', array('id' => $id));
        return $query->row_array();
    }

    public function deleteMsg($id) {
        $this->db->delete('messages', array('id' => $id));
    }

    public function updateMsg($data, $id) {
        $this->db->where('id', $id);
        $this->db->update('messages', $data);
    }

    public function subscribeNewsletter($array) {
        $query = $this->db->get_where('newsletter', array('email' => $array['email']));
        $response = $query->row_array();
        if (empty($response)) {
            $this->db->insert('newsletter', $array);
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    public function giveRate($array) {
        $this->db->insert('rating', $array);
    }

    public function postReview($array) {
        $this->db->insert('reviews', $array);
    }

    public function checkRate($user_id, $id) {
        $this->db->select('rating');
        $query = $this->db->get_where('rating', array('from' => $id, 'to' => $user_id));
        $rate = $query->row_array();
        if (!empty($rate)) {
            return $rate['rating'];
        } else {
            return false;
        }
    }

    public function get_user_type($id) {

        $this->db->select('type');
        $query = $this->db->get_where('tbl_users', array('id' => $id));
        $rate = $query->row_array();
        return $rate['type'];
    }

    function getcategories() {
        $category_array = array('' => 'Select');
        $query = $this->db->get_where('categories', array('parent_id' => '0'));
        $categories = $query->result_array();
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category_array[$category['category_id']] = $category['category_name'];
                $query = $this->db->get_where('categories', array('parent_id' => $category['category_id']));
                $subcategories = $query->result_array();

                if (!empty($subcategories)) {
                    foreach ($subcategories as $subcategory) {
                        $category_array[$subcategory['category_id']] = "--" . $subcategory['category_name'];

                        $query = $this->db->get_where('categories', array('parent_id' => $subcategory['category_id']));
                        $subcategories1 = $query->result_array();


                        if (!empty($subcategories1)) {
                            foreach ($subcategories1 as $subcategory1) {
                                $category_array[$subcategory1['category_id']] = "------" . $subcategory1['category_name'];
                            }
                        }
                    }
                }
            }
        }
        return $category_array;
    }

    function getCatgeryname($category_id) {
        $my_array = array();
        $query = $this->db->get_where('categories', array('category_id' => $category_id));
        $categories = $query->row_array();
        if (!empty($categories) and $categories['parent_id']) {
            $my_array[] = $categories['category_name'];
            // get parent category
            $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
            $categories = $query->row_array();
            if (!empty($categories) and $categories['parent_id']) {

                $my_array[] = $categories['category_name'];
                // get parent category
                $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                $categories = $query->row_array();
                if (!empty($categories) and $categories['parent_id']) {

                    $my_array[] = $categories['category_name'];
                    // get parent category
                    $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                    $categories = $query->row_array();
                    if (!empty($categories) and $categories['parent_id']) {
                        
                    } else {
                        $my_array[] = $categories['category_name'];
                    }
                } else {
                    $my_array[] = $categories['category_name'];
                }
            } else {
                $my_array[] = $categories['category_name'];
            }
        }
        array_reverse($my_array);
        return implode("<span style='color:black'> &raquo; &nbsp;</span>", array_reverse($my_array));
    }

    function getCatgerynamelink($category_id) {
        $my_array = array();
        $my_array_ids = array();
        $query = $this->db->get_where('categories', array('category_id' => $category_id));
        $categories = $query->row_array();
        if (!empty($categories) and $categories['parent_id']) {
            $my_array[] = $categories['category_name'];
            $my_array_ids[] = $categories['category_id'];
            // get parent category
            $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
            $categories = $query->row_array();
            if (!empty($categories) and $categories['parent_id']) {

                $my_array[] = $categories['category_name'];
                $my_array_ids[] = $categories['category_id'];
                // get parent category
                $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                $categories = $query->row_array();
                if (!empty($categories) and $categories['parent_id']) {

                    $my_array[] = $categories['category_name'];
                    $my_array_ids[] = $categories['category_id'];
                    // get parent category
                    $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                    $categories = $query->row_array();
                    if (!empty($categories) and $categories['parent_id']) {
                        
                    } else {
                        $my_array[] = $categories['category_name'];
                        $my_array_ids[] = $categories['category_id'];
                    }
                } else {
                    $my_array[] = $categories['category_name'];
                    $my_array_ids[] = $categories['category_id'];
                }
            } else {
                $my_array[] = $categories['category_name'];
                $my_array_ids[] = $categories['category_id'];
            }
        }
        $my_array = array_reverse($my_array);
        $my_array_ids = array_reverse($my_array_ids);
        $string_array = array();
        if (!empty($my_array)) {
            $counter = 0;
            foreach ($my_array as $key => $val) {
                $counter++;
                if ($counter == 1) {
                    $url = HTTP_PATH . "classified/ads?category=" . $my_array_ids[$key];
                }
                if ($counter == 2) {
                    $url = HTTP_PATH . "classified/ads?category=" . $my_array_ids[$key - 1] . "&subcategory=" . $my_array_ids[$key];
                }
                if ($counter == 3) {
                    $url = HTTP_PATH . "classified/ads?category=" . $my_array_ids[$key - 2] . "&subcategory=" . $my_array_ids[$key - 1] . "&subcategory_1=" . $my_array_ids[$key];
                }
//                if ($counter == 4) {
//                    $url = HTTP_PATH . "ads?category=" . $my_array_ids[$key - 3] . "&subcategory=" . $my_array_ids[$key - 2] . "&subcategory_1=" . $my_array_ids[$key - 1] . "&subcategory_2=" . $my_array_ids[$key];
//                }
                if ($counter == 4)
                    $string_array[] = $val;
                else
                    $string_array[] = "<a href='$url'>$val</a>";
            }
        }



        return implode("<span style='color:black'> &raquo; &nbsp;</span>", ($string_array));
    }

    function getPayStatus($category_id) {
        $query = $this->db->get_where('categories', array('category_id' => $category_id));
        $categories = $query->row_array();
        if (!empty($categories) and $categories['parent_id']) {
            // get parent category
            $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
            $categories = $query->row_array();
            if (!empty($categories) and $categories['parent_id']) {

                // get parent category
                $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                $categories = $query->row_array();
                if (!empty($categories) and $categories['parent_id']) {

                    // get parent category
                    $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                    $categories = $query->row_array();
                    if (!empty($categories) and $categories['parent_id']) {
                        
                    } else {
                        return $categories['pay_type'];
                    }
                } else {
                    return $categories['pay_type'];
                }
            } else {
                return $categories['pay_type'];
            }
        } else {
            return $categories['pay_type'];
        }
    }

    function getcategorylist($category_id) {
        $my_array = array();
        $query = $this->db->get_where('categories', array('category_id' => $category_id));
        $categories = $query->row_array();
        if (!empty($categories) and $categories['parent_id']) {
            $my_array[] = $categories['category_id'];
            // get parent category
            $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
            $categories = $query->row_array();
            if (!empty($categories) and $categories['parent_id']) {

                $my_array[] = $categories['category_id'];
                // get parent category
                $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                $categories = $query->row_array();
                if (!empty($categories) and $categories['parent_id']) {

                    $my_array[] = $categories['category_id'];
                    // get parent category
                    $query = $this->db->get_where('categories', array('category_id' => $categories['parent_id']));
                    $categories = $query->row_array();
                    if (!empty($categories) and $categories['parent_id']) {
                        
                    } else {
                        $my_array[] = $categories['category_id'];
                    }
                } else {
                    $my_array[] = $categories['category_id'];
                }
            } else {
                $my_array[] = $categories['category_id'];
            }
        } else {
//            $my_array[] = $categories['category_id'];
        }
        return array_reverse($my_array);
    }

    // get custom value
    function getcustomvalue($ad_id, $group_id, $field_id) {
        $query = $this->db->get_where('tbl_custom_field_values', array('ad_id' => $ad_id, 'group_id' => $group_id, 'field_id' => $field_id));
        $values = $query->row_array();
        if (!empty($values)) {
            return $values['field_value'];
        } else {
            return "";
        }
    }

}
