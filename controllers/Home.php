<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setting_model');
        $this->load->model('Banner_model');
    }

    public function index()
    {
        $data = [
            'title' => 'Home',
            'banner' => $this->Banner_model->view(),
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/index', $data);
    }

    public function download()
    {
        $data = [
            'title' => 'Downlaod',
            'banner' => $this->Banner_model->view(),
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/download', $data);
    }

    public function faq()
    {
        $data = [
            'title' => 'FAQ',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/faq', $data);
    }

    public function about_us()
    {
        $data = [
            'title' => 'About Us',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/about-us', $data);
    }

    public function refund_policy()
    {
        $data = [
            'title' => 'Refund Policy',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/refund-policy', $data);
    }

    public function privacy_policy()
    {
        $data = [
            'title' => 'Privacy Policy',
            'Setting' => $this->Setting_model->Setting(),
        ];

        website('website/privacy', $data);
    }

    public function terms_conditions()
    {
        $data = [
            'title' => 'Terms & Conditions',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/t-and-c', $data);
    }

    public function security()
    {
        $data = [
            'title' => 'Security',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/security', $data);
    }

    public function contact_us()
    {
        $data = [
            'title' => 'Contact us',
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/Contact', $data);
    }

    public function download2()
    {
        $data = [
            'title' => 'Download',
            'banner' => $this->Banner_model->view(),
            'Setting' => $this->Setting_model->Setting(),
        ];
        website('website/download-2', $data);
    }
	
	//Added by khemit
	public function redirect(){
		
		if (isset($_GET['client_txn_id'])) {
			$upi_payment_api_key = $this->Setting_model->Setting('upi_payment_api_key');
			$key = $upi_payment_api_key->upi_payment_api_key;	// Your Api Token https://merchant.upigateway.com/user/api_credentials
			$post_data = new stdClass();
			$post_data->key = $key;
			$post_data->client_txn_id = $_GET['client_txn_id']; // you will get client_txn_id in GET Method
			$post_data->txn_date = date("d-m-Y"); // date of transaction
			
			

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://api.ekqr.in/api/check_order_status',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($post_data),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			));
			$response = curl_exec($curl);
			curl_close($curl);

			$result = json_decode($response, true);
			
			
			if ($result['status'] == true) {
				// Txn Status = 'created', 'scanning', 'success','failure'

				if ($result['data']['status'] == 'success') {
					$echo = '<div class="alert alert-danger"> Transaction Status : Success</div>';
					$txn_data = $result['data'];
					// All the Process you want to do after successfull payment
					// Please also check the txn is already success in your database.
				}
				$txn_data = $result['data'];
				$echo = '<div class="alert alert-danger"> Transaction Status : ' . $result['data']['status'] . '</div>';
				echo $echo;
			}
		}
	}
}
