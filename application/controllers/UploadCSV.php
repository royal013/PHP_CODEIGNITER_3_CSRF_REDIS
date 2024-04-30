<?php
class UploadCSV extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('csv_model');
        $this->load->library('csvimport');
        $this->load->library('session');
    }
    public function index()
    {
        $this->load->view('UploadCSV');
    }
    function uploadUser()
    {
        $data['addressbook'] = $this->csv_model->get_addressbook();
        $data['error'] = '';
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '10000';

        $this->load->library('upload', $config);


        if (!$this->upload->do_upload('userfile')) {
            echo 'here';
            exit();

        } else {
            $file_data = $this->upload->data();
            $file_path = './uploads/' . $file_data['file_name'];

            if ($this->csvimport->get_array($file_path)) {
                $csv_array = $this->csvimport->get_array($file_path);

                $expected_headers = array('Partner_Name', 'Partner_Email_ID', 'Customer_Type', 'Sales_Rep_Name', 'PO_Date_Order_Booked_Date', 'PO_Number', 'Order_Value', 'Location', 'Point', 'Other_Point', 'Category', 'Equipment_Location_cust_name');

                $csv_headers = array_keys($csv_array[0]);
                if ($csv_headers !== $expected_headers) {
                    $this->session->set_flashdata('error', 'CSV Headers Not Matched');
                    redirect(base_url() . 'uploadcsv');
                    return;
                }

                foreach ($csv_array as $row) {
                    $insert_data = array(
                        'Partner_Name' => $row['Partner_Name'],
                        'Partner_Email_ID' => $row['Partner_Email_ID'],
                        'Customer_Type' => $row['Customer_Type'],
                        'Sales_Rep_Name' => $row['Sales_Rep_Name'],
                        'PO_Date_Order_Booked_Date' => $row['PO_Date_Order_Booked_Date'],
                        'PO_Number' => $row['PO_Number'],
                        'Order_Value' => abs($row['Order_Value']),
                        'Location' => $row['Location'],
                        'Point' => $row['Point'],
                        'Other_Point' => $row['Other_Point'],
                        'Category' => $row['Category'],
                        'Equipment_Location_cust_name' => $row['Equipment_Location_cust_name']

                    );
                    $this->csv_model->insert_csv($insert_data);
                }
                $this->session->set_flashdata('success', 'Csv Data Imported Succesfully');
                redirect(base_url() . 'uploadcsv');
            } else {
                $data['error'] = "Error occurred while reading CSV file";
                $this->load->view('csvindex', $data);
            }
        }

    }
    function uploadCoupon()
    {
        // $data['addressbook'] = $this->csv_model->get_addressbook();
        $data['error'] = '';
        $config['upload_path'] = './uploads/coupons/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '10000';

        
        $this->load->library('upload', $config);


        if (!$this->upload->do_upload('couponfile')) {
            $data['error'] = $this->upload->display_errors();
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            exit();

        } else {
            $file_data = $this->upload->data();
            $file_path = './uploads/coupons/' . $file_data['file_name'];
            

            if ($this->csvimport->get_array($file_path)) {
                $csv_array = $this->csvimport->get_array($file_path);

                $expected_headers = array('coupon', 'type');

                $csv_headers = array_keys($csv_array[0]);
                if ($csv_headers !== $expected_headers) {
                    unlink($file_path);
                    $this->session->set_flashdata('error', 'CSV Headers Not Matched');
                    redirect(base_url() . 'uploadcsv');
                    return;
                }

                foreach ($csv_array as $row) {
                    $insert_data = array(
                        'coupon' => $row['coupon'],
                        'type' => $row['type']
                    );
                    $this->csv_model->insert_coupon($insert_data);
                }
                unlink($file_path);
                $this->session->set_flashdata('success', 'Csv Data Imported Succesfully');
                redirect(base_url() . '/');
            } else {
                // $data['error'] = "Error occurred while reading CSV file";
                // $this->load->view('csvindex', $data);
                unlink($file_path);
                echo 'error uploading coupon csv';
            }
        }

    }
    function uploadGiftCard()
    {
        // $data['addressbook'] = $this->csv_model->get_addressbook();
        $data['error'] = '';
        $config['upload_path'] = './uploads/giftcard/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '10000';

        
        $this->load->library('upload', $config);


        if (!$this->upload->do_upload('giftCardFile')) {
            $data['error'] = $this->upload->display_errors();
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            exit();

        } else {
            $file_data = $this->upload->data();
            $file_path = './uploads/giftcard/' . $file_data['file_name'];
            

            if ($this->csvimport->get_array($file_path)) {
                $csv_array = $this->csvimport->get_array($file_path);

                $expected_headers = array('product_name', 'amount','Gift_card_number','validity','card_pin','terms_condition','currency_code','is_used','State');

                $csv_headers = array_keys($csv_array[0]);
                if ($csv_headers !== $expected_headers) {
                    unlink($file_path);
                    $this->session->set_flashdata('error', 'CSV Headers Not Matched');
                    redirect(base_url() . 'uploadcsv');
                    return;
                }

                foreach ($csv_array as $row) {
                    $validity_date = DateTime::createFromFormat('n/j/Y', $row['validity'])->format('Y-m-d');
                    $insert_data = array(
                        'product_name' => $row['product_name'],
                        'amount' => $row['amount'],
                        'Gift_card_number' => $row['Gift_card_number'],
                        'State'=>$row['State'],
                        'validity' => $validity_date,
                        'card_pin' => $row['card_pin'],
                        'terms_condition' => $row['terms_condition'],
                        'currency_code' => $row['currency_code'],
                    );
                    $this->csv_model->insert_gift_card($insert_data);
                }
                unlink($file_path);
                $this->session->set_flashdata('success', 'Csv Data Imported Succesfully');
                redirect(base_url() . '/');
            } else {
                // $data['error'] = "Error occurred while reading CSV file";
                // $this->load->view('csvindex', $data);
                unlink($file_path);
                echo 'error uploading coupon csv';
            }
        }

    }
    function uploadBatchCode()
    {
       
        $data['error'] = '';
        $config['upload_path'] = './uploads/batchcode/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '10000';

        
        $this->load->library('upload', $config);


        if (!$this->upload->do_upload('batchcode')) {
            $data['error'] = $this->upload->display_errors();
            echo '<pre>';
            print_r($data);
            echo '</pre>';
            exit();

        } else {
            $file_data = $this->upload->data();
            $file_path = './uploads/batchcode/' . $file_data['file_name'];
            

            if ($this->csvimport->get_array($file_path)) {
                $csv_array = $this->csvimport->get_array($file_path);

                $expected_headers = array('batch_code','state');

                $csv_headers = array_keys($csv_array[0]);
                if ($csv_headers !== $expected_headers) {
                    unlink($file_path);
                    $this->session->set_flashdata('error', 'CSV Headers Not Matched');
                    redirect(base_url() . 'uploadcsv');
                    return;
                }

                foreach ($csv_array as $row) {
                    $insert_data = array(
                        'batch_code' => $row['batch_code'],
                        'state' => $row['state']
                    );
                    $this->csv_model->insert_batch_code($insert_data);
                }
                unlink($file_path);
                $this->session->set_flashdata('success', 'Csv Data Imported Succesfully');
                redirect(base_url() . '/');
            } else {
                // $data['error'] = "Error occurred while reading CSV file";
                // $this->load->view('csvindex', $data);
                unlink($file_path);
                echo 'error uploading coupon csv';
            }
        }

    }
}

?>