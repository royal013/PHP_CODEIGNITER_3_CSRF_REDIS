<?php
class Main_Model extends CI_Model
{
    public function verify_coupon($state, $coupon)
    {
        $query = $this->db->select("*")->from($state)->where('coupon', $coupon)->where('is_used', '0')->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function update_coupon_card($state, $coupon, $user_id)
    {
        date_default_timezone_set('Asia/Kolkata');
        $time = date('H:i:s');
        $date = date('Y:m:d');
        $this->db->where('coupon', $coupon)->update($state, array('user_id' => $user_id, 'is_used' => '1', 'used_date' => $date, 'used_time' => $time));
    }
    public function get_coupen_type($state, $coupon)
    {
        $query = $this->db->select('type')->from($state)->where('coupon', $coupon)->get();
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
        $this->db->insert('redeemed_details', array('user_id' => $user_id, 'redeemed_coupon/batchcode' => $coupon, 'redeemed_giftcard_id' => $gift_card_id, 'redeemed_date' => $date, 'redeemed_time' => $time));
    }
    public function check_user_phone_for_daily_limit($phone, $state)
    {
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y-m-d');
        $query = $this->db->select('*')->from('register_user')->where(array('phone_number' => $phone, 'state' => $state, 'created_date' => $date))->get();
        return $query->num_rows();
    }
    public function check_user_phone_for_campaign_limit($state, $phone)
    {
        $query = $this->db->select('*')->from('register_user')->where(array('phone_number' => $phone, 'state' => $state))->get();
        return $query->num_rows();
    }

    public function get_daily_limit($state)
    {
        $query = $this->db->select("daily_limit_per_user")->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->daily_limit_per_user;
    }
    public function get_campaign_limit($state)
    {
        $query = $this->db->select('campaign_limit_per_user')->from("state_detail")->where("state", $state)->get();
        $res = $query->row();
        return $res->campaign_limit_per_user;
    }
    public function update_total_registration($state)
    {
        $query = $this->db->set('active_registration', 'active_registration + 1', false)->where('state', $state)->update('state_detail');
    }
    public function getThresholdValue($state)
    {
        $query = $this->db->select('threshold')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->threshold;

    }
    public function getTotalActiveRegistration($state)
    {
        $query = $this->db->select('active_registration')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->active_registration;
    }
    public function get_campaign_start_date($state)
    {
        $query = $this->db->select('campaign_start')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->campaign_start;
    }
    public function get_campaign_end_date($state)
    {
        $query = $this->db->select('campaign_end')->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->campaign_end;
    }
    public function update_user_case1($user_id, $coupon, $coupen_type, $giftcard_name, $gift_card_pin)
    {
        $this->db->where('id', $user_id)->update('register_user', array('redeem_coupon' => $coupon, 'coupon_type' => $coupen_type, 'redeem_giftcard_name' => $giftcard_name, 'redeem_giftcard_pin' => $gift_card_pin));
    }
    public function update_user_case2($user_id, $batchcode, $giftcard_name, $gift_card_pin)
    {
        $this->db->where('id', $user_id)->update('register_user', array('redeem_batchcode' => $batchcode, 'redeem_giftcard_name' => $giftcard_name, 'redeem_giftcard_pin' => $gift_card_pin));
    }
    public function update_user_case3($user_id, $coupon, $giftcard_name, $gift_card_pin)
    {
        $this->db->where('id', $user_id)->update('register_user', array('redeem_coupon' => $coupon, 'redeem_giftcard_name' => $giftcard_name, 'redeem_giftcard_pin' => $gift_card_pin));
    }
    public function update_user_case4($user_id, $coupon)
    {
        $this->db->where('id', $user_id)->update('register_user', array('redeem_coupon' => $coupon));
    }
    public function validate_coupon($state, $coupon)
    {
        $query = $this->db->select('*')->from($state)->where('coupon', $coupon)->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function get_threshold($threshold_type, $state)
    {
        $query = $this->db->select($threshold_type)->from('state_detail')->where('state', $state)->get();
        $row = $query->row();
        return $row->$threshold_type;
    }

    public function get_coupon_type_count($state, $coupon_type, $threshold_type)
    {
        $query = $this->db->select($threshold_type)->from('state_detail')->where('state', $state)->get();
        $res = $query->row();
        return $res->$threshold_type;
    }
    public function update_coupon_type_count($state, $coupen_type)
    {
        $threshold_type = '';
        if ($coupen_type === 'quart') {
            $threshold_type = 'quart_redeemed_count';
        } else if ($coupen_type === 'pint') {
            $threshold_type = 'pint_redeemed_count';
        } else if ($coupen_type === 'nip') {
            $threshold_type = 'nip_redeemed_count';
        }
        $query = $this->db->set($threshold_type, $threshold_type . '+ 1', false)->where('state', $state)->update('state_detail');


    }
}
?>