<?php

class customer_model extends CI_Model {
    public $current_model_table = "tbl_customers";
    function __construct() {
         $this->load->model('main_model');
        parent::__construct();
       
    }

function get_user_data($id)
{

    $query = $this->db->query("SELECT * FROM tbl_customers WHERE id='$id'");
    $result = $query->row();
   /* echo "<pre>";
    print_r($result); die;*/
    $count = count($result);

    if(empty($count) || $count > 1)
    {
        $log = 0;
        return $log ;
    }
    else
    {
        return $result;
    }
}


 public function getBasicCustomerInfo($customer_id, $extra_conditions=array()){     
        $table = $this->current_model_table;
        $select = "$table.*";
        $joins = array();
        $extra_conditions["$table.id"] = $customer_id;
        return $this->main_model->cruid_select($table, $select, $joins, $extra_conditions);
    }

function get_customer_paincard($id)
{
    $query = $this->db->query("SELECT tcp.*,ts.name as state FROM tbl_customer_paincard tcp LEFT JOIN tbl_states ts on tcp.state=ts.id WHERE tcp.id='$id'");
    $result = $query->row();
    
    if($query->num_rows() > 0)
    {
       
        return $result;
    }
    else
    {
        $log = 0;
        return $log ;
    }
}

function get_bankdetail($id)
{

    $query = $this->db->query("SELECT * FROM tbl_customer_bankdetail WHERE id='$id'");
    $result = $query->row();
        
    if($query->num_rows() > 0)
    {
       
        return $result;
    }
    else
    {
        $log = 0;
        return $log ;
    }
}
function IsDonebankdetailnPaincard($id)
{

    $query = $this->db->query("SELECT tc.id FROM `tbl_customers` tc LEFT JOIN tbl_customer_paincard tcp ON ( tcp.id=tc.paincard_id) LEFT JOIN tbl_customer_bankdetail tcb ON ( tcb.id=tc.bankdetail_id) WHERE (tcb.status='A' AND tcp.status='A' AND tc.bankdetail_id>0 AND tc.paincard_id>0 AND tcp.customer_id='$id' AND tcb.customer_id='$id')");
    //$result = $query->row();
   // $count = count($result);
    if( $query->num_rows() > 0 )
    {      
        return true ;
    }
    else
    {
        return false;
    }
}

}
