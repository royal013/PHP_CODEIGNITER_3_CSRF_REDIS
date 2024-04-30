<?php

class csv_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();

    }

    function get_addressbook()
    {
        $query = $this->db->get('sale');
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return FALSE;
        }
    }

    function serialNumberCheck($num)
    {
        $query = $this->db->select('*')->from("sale")->where('S_No', $num)->get();
        if ($query->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    function insert_csv($data)
    {
        $this->db->insert('sale', $data);
    }
    function insert_coupon($data)
    {
        $this->db->insert('coupons', $data);
    }
    function insert_gift_card($data)
    {
        $this->db->insert('gift_card', $data);
    }
    function insert_batch_code($data)
    {
        $this->db->insert('gift_card', $data);
    }
    
}
/*END OF FILE*/
