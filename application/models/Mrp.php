<?php
ini_set('memory_limit', '-1');
defined('BASEPATH') or exit('No direct script access allowed');

class Mrp extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        show_404();
    }

    public function login()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{"username":"proapi","password":"proapi"}',
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

    public function prodfam()
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/master_prodfam',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);
        } else {
            $data = [];
            return $data;
        }
    }

    public function master_item()
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/master_item',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);

            // $token = $login->token;
            // $url = 'http://erp.banshu-ind.com/pro-api/master_item';

            // $ch = curl_init($url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //     'Content-Type:application/json',
            //     "Authorization: Bearer $token",
            // ));
            // curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            // curl_setopt($ch, CURLOPT_POST, true);
            // $response = curl_exec($ch);

            // curl_close($ch);

            // return json_decode($response);
        } else {
            $data = [];
            return $data;
        }
    }

    public function master_item_fg()
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/master_item_fg',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);
        } else {
            $data = [];
            return $data;
        }
    }

    public function master_customer()
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/master_customer',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response);
        } else {
            $data = [];
            return $data;
        }
    }

    public function master_bom($assy_no)
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;
            $databom = [];

            // foreach ($assy_no as $assy) {
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/master_bom',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS => '{"assy":"' . $assy_no . '"}',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        "Authorization: Bearer $token",
                    ),
                ));

                $result = curl_exec($curl);
                curl_close($curl);

                $decoded = json_decode($result, true);

                // Gabungkan langsung ke $databom
                if (is_array($decoded)) {
                    $databom = array_merge($databom, $decoded);
                }
            // }

            return $databom;
        } else {
            $data = [];
            return $data;
        }
    }

    public function lttp($period)
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/mps_ltpp',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{"period":"' . $period . '"}',
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

    public function ospo()
    {
        $login = $this->login();
        
        if ($login->information == 'Success') {
            $token = $login->token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/os_supplier',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                // CURLOPT_POSTFIELDS => '{"period":"' . $period . '"}',
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

    public function wip($period)
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/os_supply_wip',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '"}',
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

    public function mpp($period)
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/os_mpp',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '"}',
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

    public function stock($period)
    {
        $login = $this->login();

        if ($login->information == 'Success') {
            $token = $login->token;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api/ith',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '"}',
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
