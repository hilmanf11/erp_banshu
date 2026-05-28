<?php
ini_set('memory_limit', '-1');
defined('BASEPATH') or exit('No direct script access allowed');

class Mrp_push extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        show_404();
    }

    public function login($plant = "1")
    {
        $url = 'http://erp.banshu-ind.com/api_umh/login';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{"username":"astek2","password":"astek2","plant":"'.$plant.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (!$response) {
            $response = '{"information":"Connection Failure"}';
        }
        curl_close($curl);
        return json_decode($response);
    }

    public function push_purchase_orders($data)
    {
        $login = $this->login();
        $decoded = array();
        if ($login->information == 'Success') {
            $token = $login->token;

            $url = "http://erp.banshu-ind.com/api_umh/insert_poc";
            $json_data = json_encode($data);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json_data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $result = curl_exec($curl);
            curl_close($curl);
            $decoded = json_decode($result, true);
        }

        return $decoded;
    }

    public function push_update_poc($data)
    {
        $login = $this->login();
        $decoded = array();
        if ($login->information == 'Success') {
            $token = $login->token;

            $url = "http://erp.banshu-ind.com/api_umh/update_poc";
            $json_data = json_encode($data);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json_data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $result = curl_exec($curl);
            curl_close($curl);
            $decoded = json_decode($result, true);
        }

        return $decoded;
    }
}
