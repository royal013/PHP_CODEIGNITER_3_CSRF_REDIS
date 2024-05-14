<?php

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
  public function check_campaign_dates($state)
  {
    date_default_timezone_set('Asia/Kolkata');
    $time = date('H:i:s');
    $date = date('Y-m-d');
    $startDate = $this->Main_Model->get_campaign_start_date($state);
    $endDate = $this->Main_Model->get_campaign_end_date($state);
    if ($date < $startDate) {
      return 0;
    }
    if ($date > $endDate) {
      return 1;
    }
    return 2;
  }

  public function register_with_coupon()
  {
    if (!$this->input->method(TRUE) === 'POST') {
      show_error('Only POST requests are allowed for this method.', 400);
      return;
    }
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $min_age = $this->Main_Model->get_minimum_age($state);
    $daily_limit = $this->Main_Model->get_daily_limit($state);
    $campaign_limit = $this->Main_Model->get_campaign_limit($state);
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
    if ($this->check_campaign_dates($state) === 0) {
      $this->session->set_flashdata('error', 'Campaign not started');
      redirect(base_url('register'));
      exit();
    }
    if ($this->check_campaign_dates($state) === 1) {
      $this->session->set_flashdata('error', 'Campaign ended');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata($state . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_daily_limit($phone, $state) >= $daily_limit) {
      $this->session->set_flashdata('error', 'Daily Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }

    if ($this->Main_Model->check_user_phone_for_campaign_limit($state, $phone) >= $campaign_limit) {
      $this->session->set_flashdata('error', 'Campaign Limit Exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($state . $phone)) {
      $this->session->set_userdata($state . $phone, 0);
    }
    if ($this->Main_Model->check_state_status($state) === '0') {
      $this->session->set_flashdata('error', 'state inactive');
      redirect(base_url('register'));
      exit();
    }
    // if (!($validate === $state)) {
    //   $this->Main_Model->validate_coupon($state, $coupon);
    //   $this->session->set_flashdata('error', 'Invalid coupon for this state');
    //   redirect(base_url('register'));
    //   exit();
    // }
    if (!$this->Main_Model->validate_coupon($state, $coupon)) {
      $this->session->set_flashdata('error', 'Invalid coupon for this state');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->verify_coupon($state, $coupon)) {
      $thresholdValue = $this->Main_Model->getThresholdValue($state);
      $totalActiveRegistration = $this->Main_Model->getTotalActiveRegistration($state);
      if ($totalActiveRegistration >= $thresholdValue) {
        $this->session->set_flashdata('error', 'Threshold Value for this state reached');
        redirect(base_url('register'));
        exit();
      }
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      $this->Main_Model->update_total_registration($state);
      //INCREMENTING THE SESSION DATA
      $count = $this->session->userdata($state . $phone);
      $this->session->set_userdata($state . $phone, $count + 1);
      $coupen_type = $this->Main_Model->get_coupen_type($state, $coupon);
      $val = 0;
      switch ($coupen_type) {
        case 'quart':
          $val = $this->Main_Model->get_quart_val($state);
          break;
        case 'pint':
          $val = $this->Main_Model->get_pint_val($state);
          break;
        case 'nip':
          $val = $this->Main_Model->get_nip_val($state);
          break;
        default:
          break;
      }
      $output = $this->Main_Model->get_all($state, $val);
      if ($output) {
        $this->Main_Model->update_gift_card_val($output['id'], $user_id);
        $this->Main_Model->update_coupon_card($state, $coupon, $user_id);
        $this->Main_Model->update_user_case1($user_id, $coupon, $coupen_type, $output['product_name'], $output['card_pin']);
        // $this->Main_Model->redeemed_details($user_id, $coupon, $output['id']);
        $this->load->view('ThankYou');

      } else {
        $this->session->set_flashdata('error', 'Gift Card Exhausted');
        redirect(base_url('register'));
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

    if (!$this->input->method(TRUE) === 'POST') {
      show_error('Only POST requests are allowed for this method.', 400);
      return;
    }
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $batchcode = $this->input->post('batchcode', TRUE);
    $state = $this->input->post('state', TRUE);
    $min_age = $this->Main_Model->get_minimum_age($state);
    $daily_limit = $this->Main_Model->get_daily_limit($state);
    $campaign_limit = $this->Main_Model->get_campaign_limit($state);
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
    if ($this->check_campaign_dates($state) === 0) {
      $this->session->set_flashdata('error', 'Campaign not started');
      redirect(base_url('register'));
      exit();
    }
    if ($this->check_campaign_dates($state) === 1) {
      $this->session->set_flashdata('error', 'Campaign ended');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata($state . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_daily_limit($phone, $state) >= $daily_limit) {
      $this->session->set_flashdata('error', 'Daily Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_campaign_limit($state, $phone) >= $campaign_limit) {
      $this->session->set_flashdata('error', 'Campaign Limit Exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($state . $phone)) {
      $this->session->set_userdata($state . $phone, 0);
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
    $thresholdValue = $this->Main_Model->getThresholdValue($state);
    $totalActiveRegistration = $this->Main_Model->getTotalActiveRegistration($state);
    if ($totalActiveRegistration >= $thresholdValue) {
      $this->session->set_flashdata('error', 'Threshold Value for this state reached');
      redirect(base_url('register'));
      exit();
    }
    $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
    $this->Main_Model->update_total_registration($state);
    $count = $this->session->userdata($state . $phone);
    $this->session->set_userdata($state . $phone, $count + 1);
    $output = $this->Main_Model->getGiftCardForBatchCode($state);
    if ($output) {
      $this->Main_Model->update_gift_card_val($output['id'], $user_id);
      $this->Main_Model->update_user_case2($user_id, $batchcode, $output['product_name'], $output['card_pin']);
      // $this->Main_Model->redeemed_details($user_id, $batchcode, $output['id']);
      $this->load->view('ThankYou');
      // echo $output['id'] . '<br>';
      // echo $user_id . '<br>';
      // echo '<pre>';
      // print_r($output);
      // echo '</pre>';
    } else {
      $this->session->set_flashdata('error', 'Gift Card Exhausted');
      redirect(base_url('register'));
      exit();
    }
  }
  public function direct_giftcard()
  {
    if (!$this->input->method(TRUE) === 'POST') {
      show_error('Only POST requests are allowed for this method.', 400);
      return;
    }
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $min_age = $this->Main_Model->get_minimum_age($state);
    $daily_limit = $this->Main_Model->get_daily_limit($state);
    $campaign_limit = $this->Main_Model->get_campaign_limit($state);
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
    if ($this->check_campaign_dates($state) === 0) {
      $this->session->set_flashdata('error', 'Campaign not started');
      redirect(base_url('register'));
      exit();
    }
    if ($this->check_campaign_dates($state) === 1) {
      $this->session->set_flashdata('error', 'Campaign ended');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata($state . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_daily_limit($phone, $state) >= $daily_limit) {
      $this->session->set_flashdata('error', 'Daily Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_campaign_limit($state, $phone) >= $campaign_limit) {
      $this->session->set_flashdata('error', 'Campaign Limit Exceeded');
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
      $thresholdValue = $this->Main_Model->getThresholdValue($state);
      $totalActiveRegistration = $this->Main_Model->getTotalActiveRegistration($state);
      if ($totalActiveRegistration >= $thresholdValue) {
        $this->session->set_flashdata('error', 'Threshold Value for this state reached');
        redirect(base_url('register'));
        exit();
      }
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      $this->Main_Model->update_total_registration($state);
      $count = $this->session->userdata($state . $phone);
      $this->session->set_userdata($state . $phone, $count + 1);
      $output = $this->Main_Model->get_random_giftCard($state);
      if ($output) {
        $this->Main_Model->update_gift_card_val($output['id'], $user_id);
        $this->Main_Model->update_coupon_card($coupon, $user_id);
        $this->Main_Model->update_user_case3($user_id, $coupon, $output['product_name'], $output['card_pin']);
        $this->load->view('ThankYou');
        // echo $this->session->userdata($state . $phone);
        // echo '<pre>';
        // print_r($output);
        // echo '</pre>';
      } else {
        $this->session->set_flashdata('error', 'Gift Card Exausted');
        redirect(base_url('register'));
        exit();
      }
    } else {
      $this->session->set_flashdata('error', 'Invalid Coupon');
      redirect(base_url('register'));
      exit();
    }
  }
  public function no_giftcard()  //take registration but no giftcard using coupon
  {
    if (!$this->input->method(TRUE) === 'POST') {
      show_error('Only POST requests are allowed for this method.', 400);
      return;
    }
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $min_age = $this->Main_Model->get_minimum_age($state);
    $daily_limit = $this->Main_Model->get_daily_limit($state);
    $campaign_limit = $this->Main_Model->get_campaign_limit($state);
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
    if ($this->check_campaign_dates($state) === 0) {
      $this->session->set_flashdata('error', 'Campaign not started');
      redirect(base_url('register'));
      exit();
    }
    if ($this->check_campaign_dates($state) === 1) {
      $this->session->set_flashdata('error', 'Campaign ended');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata($state . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_daily_limit($phone, $state) >= $daily_limit) {
      $this->session->set_flashdata('error', 'Daily Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }

    if ($this->Main_Model->check_user_phone_for_campaign_limit($state, $phone) >= $campaign_limit) {
      $this->session->set_flashdata('error', 'Campaign Limit Exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($state . $phone)) {
      $this->session->set_userdata($state . $phone, 0);
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
      $this->Main_Model->update_total_registration($state);
      $count = $this->session->userdata($state . $phone);
      $this->session->set_userdata($state . $phone, $count + 1);
      $this->Main_Model->update_coupon_card($coupon, $user_id);
      $this->Main_Model->update_user_case4($user_id, $coupon);
      $this->load->view('ThankYou');

    } else {
      $this->session->set_flashdata('error', 'Invalid Coupon');
      redirect(base_url('register'));
      exit();
    }
  }
  public function coupon_giftcard_ifnot_giftcard_continue_registration()
  {
    if (!$this->input->method(TRUE) === 'POST') {
      show_error('Only POST requests are allowed for this method.', 400);
      return;
    }
    $fname = $this->input->post('fname', TRUE);
    $lname = $this->input->post('lname', TRUE);
    $phone = $this->input->post('phone', TRUE);
    $age = $this->input->post('age', TRUE);
    $coupon = $this->input->post('coupon', TRUE);
    $validate = substr($coupon, 0, 2);
    $state = $this->input->post('state', TRUE);
    $min_age = $this->Main_Model->get_minimum_age($state);
    $daily_limit = $this->Main_Model->get_daily_limit($state);
    $campaign_limit = $this->Main_Model->get_campaign_limit($state);
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
    if ($this->check_campaign_dates($state) === 0) {
      $this->session->set_flashdata('error', 'Campaign not started');
      redirect(base_url('register'));
      exit();
    }
    if ($this->check_campaign_dates($state) === 1) {
      $this->session->set_flashdata('error', 'Campaign ended');
      redirect(base_url('register'));
      exit();
    }
    if ($this->session->userdata($state . $phone) >= 1) {
      $this->session->set_flashdata('error', 'Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }
    if ($this->Main_Model->check_user_phone_for_daily_limit($phone, $state) >= $daily_limit) {
      $this->session->set_flashdata('error', 'Daily Limit from this phone number exceeded');
      redirect(base_url('register'));
      exit();
    }

    if ($this->Main_Model->check_user_phone_for_campaign_limit($state, $phone) >= $campaign_limit) {
      $this->session->set_flashdata('error', 'Campaign Limit Exceeded');
      redirect(base_url('register'));
      exit();
    }
    if (!$this->session->userdata($state . $phone)) {
      $this->session->set_userdata($state . $phone, 0);
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
      $thresholdValue = $this->Main_Model->getThresholdValue($state);
      $totalActiveRegistration = $this->Main_Model->getTotalActiveRegistration($state);
      if ($totalActiveRegistration >= $thresholdValue) {
        $this->session->set_flashdata('error', 'Threshold Value for this state reached');
        redirect(base_url('register'));
        exit();
      }
      $user_id = $this->Main_Model->register_user($fname, $lname, $phone, $age, $state);
      $this->Main_Model->update_total_registration($state);
      //INCREMENTING THE SESSION DATA
      $count = $this->session->userdata($state . $phone);
      $this->session->set_userdata($state . $phone, $count + 1);
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
        $this->Main_Model->update_user_case1($user_id, $coupon, $coupen_type, $output['product_name'], $output['card_pin']);
        $this->load->view('ThankYou');
        // echo $this->session->userdata($state . $phone);
        // echo '<pre>';
        // print_r($output);
        // echo '</pre>';
      } else {
        $this->Main_Model->update_coupon_card($coupon, $user_id);
        $this->Main_Model->update_user_case4($user_id, $coupon);
        $this->load->view('ThankYou');
      }
    } else {
      $this->session->set_flashdata('error', 'invalid coupon');
      redirect(base_url('register'));
      exit();
    }
  }
}
?>