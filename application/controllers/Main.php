<?php

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

  public function check_state_type()
  {
    $type = $this->input->post("state");
    $val = $this->Main_Model->getStateType($type);
    echo $val;
  }


  public function register_with_coupon()
  {
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $stateType = $this->Main_Model->getStateType($state);
    $min_age = $this->Main_Model->get_minimum_age($state);
    if (strlen($phone) != 10) {
      $this->session->set_flashdata('error', 'Phone Number Invalid');
      redirect(base_url('register'));
      exit();
    }
    if ($age < $min_age) {
      $this->session->set_flashdata('error', 'Invalid Age');
      redirect(base_url('register'));
      exit();
    }
    if (!$fname || !$lname || !$phone || !$age || !$coupon || !$state) {
      $this->session->set_flashdata('error', 'All fields are required');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata('phone' . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($phone)) {
      $this->session->set_userdata($phone, 0);
    }
    if ($this->Main_Model->check_state_status($state) === '0') {
      $this->session->set_flashdata('error', 'state inactive');
      redirect(base_url('register'));
      exit();
    }
    if (!($validate === $state)) {
      $this->session->set_flashdata('error', 'Invalid coupon for this state');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->verify_coupon($coupon)) {
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      //INCREMENTING THE SESSION DATA
      $count = $this->session->userdata('phone' . $phone);
      $this->session->set_userdata('phone' . $phone, $count + 1);
      $coupen_type = $this->Main_Model->get_coupen_type($coupon);
      $val = 0;
      switch ($coupen_type) {
        case 'Quart':
          $val = $this->Main_Model->get_quart_val($state);
          break;
        case 'Pint':
          $val = $this->Main_Model->get_pint_val($state);
          break;
        case 'Nip':
          $val = $this->Main_Model->get_nip_val($state);
          break;
        default:
          break;
      }
      $output = $this->Main_Model->get_all($state, $val);
      if ($output) {
        $this->Main_Model->update_gift_card_val($output['id'], $user_id);
        $this->Main_Model->update_coupon_card($coupon, $user_id);
        $this->Main_Model->redeemed_details($user_id, $coupon, $output['id']);
        echo $this->session->userdata('phone' . $phone);
        echo '<pre>';
        print_r($output);
        echo '</pre>';
      } else {
        echo 'Gift Card Exhausted';
        exit();
      }
    } else {
      $this->session->set_flashdata('error', 'invalid coupon');
      redirect(base_url('register'));
      exit();
    }
  }

  public function register_with_batchcode()
  {
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $batchcode = $this->input->post('batchcode', TRUE);
    $state = $this->input->post('state', TRUE);
    $stateType = $this->Main_Model->getStateType($state);
    $min_age = $this->Main_Model->get_minimum_age($state);
    if (strlen($phone) != 10) {
      $this->session->set_flashdata('error', 'Phone Number Invalid');
      redirect(base_url('register'));
      exit();
    }
    if ($age < $min_age) {
      $this->session->set_flashdata('error', 'Invalid Age');
      redirect(base_url('register'));
      exit();
    }
    if (!$fname || !$lname || !$phone || !$age || !$batchcode || !$state) {
      $this->session->set_flashdata('error', 'All fields are required');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata('phone' . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($phone)) {
      $this->session->set_userdata($phone, 0);
    }
    if ($this->Main_Model->check_state_status($state) === '0') {
      $this->session->set_flashdata('error', 'state inactive');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->Main_Model->check_batchCode($batchcode)) {
      $this->session->set_flashdata('error', 'Invalid BatchCode');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->Main_Model->batchCode_state($batchcode, $state)) {
      $this->session->set_flashdata('error', 'Invalid BatchCode for this state');
      redirect(base_url('register'));
      exit();
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
      echo 'Gift Card Exhausted';
      exit();
    }
  }


  public function direct_giftcard()
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
    $min_age = $this->Main_Model->get_minimum_age($state);
    if (strlen($phone) != 10) {
      $this->session->set_flashdata('error', 'Phone Number Invalid');
      redirect(base_url('register'));
      exit();
    }
    if ($age < $min_age) {
      $this->session->set_flashdata('error', 'Invalid Age');
      redirect(base_url('register'));
      exit();
    }
    if (!$fname || !$lname || !$phone || !$age || !$coupon || !$state) {
      $this->session->set_flashdata('error', 'All fields are required');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata('phone' . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($phone)) {
      $this->session->set_userdata($phone, 0);
    }
    if ($this->Main_Model->check_state_status($state) === '0') {
      $this->session->set_flashdata('error', 'state inactive');
      redirect(base_url('register'));
      exit();
    }
    if (!($validate === $state)) {
      $this->session->set_flashdata('error', 'Invalid coupon for this state');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->verify_coupon($coupon)) {
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      $count = $this->session->userdata('phone' . $phone);
      $this->session->set_userdata('phone' . $phone, $count + 1);
      $output = $this->Main_Model->get_random_giftCard($state);
      if ($output) {
        $this->Main_Model->update_gift_card_val($output['id'], $user_id);
        $this->Main_Model->update_coupon_card($coupon, $user_id);
        echo $this->session->userdata('phone' . $phone);
        echo '<pre>';
        print_r($output);
        echo '</pre>';
      } else {
        $this->session->set_flashdata('error', 'Gift Card Exausted');
        redirect(base_url('register'));
        exit();
      }
    } else {
      echo 'Gift Card Exhausted';
      exit();
    }
  }
}
?>