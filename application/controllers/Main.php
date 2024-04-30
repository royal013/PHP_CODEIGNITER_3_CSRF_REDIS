<?php
use Predis\Client;

class Main extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('session');
    $this->load->model('Main_Model');
    $this->load->library('redis');
  }
  public function load_register_page()
  {
    $this->load->view("Register");
  }
  public function handle_register()
  {

    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $phone = $_POST['phone'];
    $age = $_POST['age'];
    $coupon = $_POST['coupon'];
    $validate = substr($coupon, 0, 2);
    $state = $_POST['state'];



    if (!$this->redis->exists($phone)) {
      $result = $this->redis->set($phone, 1);
      if ($result !== 'OK') {
        die("Error: Failed to set count for phone number $phone");
      }
    } else {
      $count = $this->redis->incr($phone);
      if ($count > 3) {
        die("Error: Limit exceeded for phone number $phone");
      }
    }
    echo 'Val' . $count;
    exit();
    // if ($this->redis->exists($phone)) {
    //   $count = $this->redis->incr($phone);
    // } else {
    //   $count = $this->redis->set($phone, 1);
    // }
    // if ($count > 3) {
    //   die('Error: Limit exceeded');
    // }





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
        echo '<pre>';
        print_r($output);
        // echo $output['id'];
        echo '</pre>';
      } else {
        echo "Gift Card Exausted";
      }
    } else {
      echo "invalid";
    }
  }
}
?>