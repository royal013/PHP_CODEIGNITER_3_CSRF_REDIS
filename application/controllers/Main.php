<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class Main extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('session');
    $this->load->model('Main_Model');
    $this->load->helper('security');
    $this->allowed_methods = array('check_state_type', 'post');

  }
  public function load_register_page()
  {
    $this->load->view("Register");
  }
  public function handle_register()
  {
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $batchcode = $this->input->post('batchcode', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $stateType = $this->Main_Model->getStateType($state);
    if ($stateType === 'coupon') {
      if (!$fname || !$lname || !$phone || !$age || !$coupon || !$state) {
        echo 'All fields are required';
      }
      if ($this->session->userdata('phone' . $phone) >= 1) {
        echo "Limit from this phone number exceeded";
        exit();
      }
      if (!$this->session->userdata($phone)) {
        $this->session->set_userdata($phone, 0);
      }
      if ($this->Main_Model->check_state_status($state) === '0') {
        echo 'state inactive';
        exit();
      }
      if (!($validate === $state)) {
        echo 'Invalid coupon';
        exit();
      }
      if ($this->Main_Model->verify_coupon($coupon)) {
        $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
        //INCREMENTING THE SESSION DATA
        $count = $this->session->userdata('phone' . $phone);
        $this->session->set_userdata('phone' . $phone, $count + 1);
        $this->Main_Model->update_coupon_card($coupon, $user_id);
        $coupen_type = $this->Main_Model->get_coupen_type($coupon);
        $val = 0;
        if ($coupen_type === 'Quart') {
          $val = $this->Main_Model->get_quart_val($state);
        } else if ($coupen_type === 'Pint') {
          $val = $this->Main_Model->get_pint_val($state);
        } else if ($coupen_type === 'Nip') {
          $val = $this->Main_Model->get_nip_val($state);
        }
        $output = $this->Main_Model->get_all($state, $val);
        if ($output) {
          $this->Main_Model->update_gift_card_val($output['id'], $user_id);
          echo $this->session->userdata('phone' . $phone);
          echo '<pre>';
          print_r($output);
          echo '</pre>';
        } else {
          echo "Gift Card Exausted";
        }
      } else {
        echo "invalid coupon";
      }
    } else {
      if (!$fname || !$lname || !$phone || !$age || !$batchcode || !$state) {
        echo 'All fields are required';
      }
      if ($this->session->userdata('phone' . $phone) >= 1) {
        echo "Limit from this phone number exceeded";
        exit();
      }
      if (!$this->session->userdata($phone)) {
        $this->session->set_userdata($phone, 0);
      }
      if ($this->Main_Model->check_state_status($state) === '0') {
        echo 'state inactive';
        exit();
      }
      if (!$this->Main_Model->check_batchCode($batchcode)) {
        echo 'Invalid BatchCode';
      }
      if (!$this->Main_Model->batchCode_state($batchcode, $state)) {
        echo 'Invalid BatchCode for this state';
      }
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      $count = $this->session->userdata('phone' . $phone);
      $this->session->set_userdata('phone' . $phone, $count + 1);
      $output = $this->Main_Model->getGiftCardForBatchCode($state);
      if ($output) {
        echo $output['id'] . '<br>';
        echo $user_id . '<br>';
        $this->Main_Model->update_gift_card_val($output['id'], $user_id);
        echo '<pre>';
        print_r($output);
        echo '</pre>';
      } else {
        echo 'gift_card exausted';
      }
    }
  }
  public function check_state_type()
  {
    $type = $this->input->post("state");
    $val = $this->Main_Model->getStateType($type);
    echo $val;
  }
}
?>