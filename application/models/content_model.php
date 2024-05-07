<?php

class Content_model extends CI_MOdel {

    public function __construct() {
        parent::__construct();
    }

    public function contentList() {
        $this->db->select('id, title, slug,content');
        $query = $this->db->get('content');
        return $query->result();
    }

    public function contentDetail($id) {
        $query = $this->db->get_where('content', "slug = '" . $id . "'");
        return $query->result_array();
    }

    public function contentDetail_front($id) {
        $query = $this->db->get_where('content', "slug = '" . $id . "'");
        return $query->result();
    }

    public function updateContent($data, $id) {
        $this->db->where("slug = '" . $id . "'");
        $this->db->update('content', $data);
    }

}
