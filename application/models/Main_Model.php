<?php
class Main_Model extends CI_Model
{
    public function verify_coupon($coupon)
    {
        $query = $this->db->select("*")->from("coupons")->where('coupon', $coupon)->where('is_used', '0')->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function update_coupon_card($coupon, $user_id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $time = date('H:i:s');
        $date = date('Y:m:d');
        $this->db->where('coupon', $coupon)->update('coupons', array('user_id' => $user_id, 'is_used' => '1', 'used_date' => $date, 'used_time' => $time));
    }
    public function get_coupen_type($coupon)
    {
        $query = $this->db->select('type')->from('coupons')->where('coupon', $coupon)->get();
        $row = $query->row();
        return $row->type;
    }

    public function get_all($state, $val)
    {
        $query = $this->db->select('*')->from('gift_card')->where(array('State' => $state, 'amount' => $val, 'is_used' => '0'))->order_by('RAND()')->limit(1)->get();
        if ($query->num_rows() > 0) {
            $val = $query->row_array();
            return $val;
        } else {
            return false;
        }
    }
    public function get_random_giftCard($state)
    {
        $query = $this->db->select('*')->from('gift_card')->where(array('State' => $state, 'is_used' => '0'))->order_by('RAND()')->limit(1)->get();
        if ($query->num_rows() > 0) {
            $val = $query->row_array();
            return $val;
        } else {
            return false;
        }
    }
    public function update_gift_card_val($id, $user_id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $time = date('H:i:s');
        $date = date('Y:m:d');
        $this->db->where('id', $id)->update('gift_card', array('user_id' => $user_id, 'is_used' => '1', 'redemeed_date' => $date, 'redemmed_time' => $time));
    }

    public function register_user($fname, $lname, $phone, $age, $state)
    {
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $data = array(
            'first_name' => $fname,
            'last_name' => $lname,
            'phone_number' => $phone,
            'age' => $age,
            'state' => $state,
            'created_date' => $date,
            'created_time' => $time
        );
        $this->db->insert('register_user', $data);
        return $this->db->insert_id();
    }

    public function check_state_status($state)
    {
        $query = $this->db->select('isActive')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->isActive;
    }
    public function get_quart_val($state)
    {
        $query = $this->db->select('quart')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->quart;
    }
    public function get_pint_val($state)
    {
        $query = $this->db->select('pint')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->pint;
    }
    public function get_nip_val($state)
    {
        $query = $this->db->select('nip')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->nip;
    }
    public function check_batchCode($val)
    {
        $query = $this->db->select('*')->from('batchcode')->where('batch_code', $val)->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function batchCode_state($batchcode, $state)
    {
        $query = $this->db->select('*')->from('batchcode')->where(array('batch_code' => $batchcode, 'state' => $state))->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getStateType($state)
    {
        $query = $this->db->select('type')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->type;
    }


    public function getGiftCardForBatchCode($state)
    {
        $query = $this->db->select('*')->from('gift_card')->where(array('state' => $state, 'is_used' => '0'))->order_by('RAND ()')->Limit(1)->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return false;
        }
    }
    public function get_minimum_age($state)
    {
        $query = $this->db->select('min_age')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->min_age;
    }
    public function redeemed_details($user_id, $coupon, $gift_card_id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y:m:d');
        $time = date('H:i:s');
        $this->db->insert('redeemed_details', array('user_id' => $user_id, 'redeemed_coupon' => $coupon, 'redeemed_giftcard' => $gift_card_id, 'redeemed_date' => $date, 'redeemed_time' => $time));
    }

}
?>