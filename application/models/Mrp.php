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

    public function login($plant = "GE")
    {

        $curl = curl_init();
        if($plant == "GE"){
            $url = 'http://erp.banshu-ind.com/pro-api/login';
        }else{
            $url = 'http://erp.banshu-ind.com/pro-api-hedom/login';
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
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
        $login = $this->login('GH');

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://erp.banshu-ind.com/pro-api-hedom/master_item_fg',
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

    public function master_item_alias($plant = "GE")
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;
            $curl = curl_init();

            if($plant == "GE"){
                $url = "http://erp.banshu-ind.com/pro-api/master_item_alias";
            }else{
                $url = "http://erp.banshu-ind.com/pro-api-hedom/master_item_alias";
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
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

    public function master_bom($assy_no, $plant)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;
            $databom = [];

            if($plant == "GE"){
                $url = "http://erp.banshu-ind.com/pro-api/master_bom";
            }else{
                $url = "http://erp.banshu-ind.com/pro-api-hedom/master_bom";
            }

            // foreach ($assy_no as $assy) {
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

    public function lttp($period, $plant, $revision)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/mps_ltpp';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/mps_ltpp';
            }

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
                CURLOPT_POSTFIELDS => '{"period":"' . $period . '","rev":"' . $revision . '"}',
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

    public function ospo($period, $plant, $item_id = "")
    {
        $login = $this->login($plant);
        
        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/os_supplier';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/os_supplier';
            }

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
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '", "item_id":"' . $item_id . '"}',
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

    public function wip($period, $plant, $item_id = "")
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/os_supply_wip';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/os_supply_wip';
            }

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
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '", "item_id":"'.$item_id.'"}',
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

    public function mpp($period, $plant)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/os_mpp';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/os_mpp';
            }

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
                CURLOPT_POSTFIELDS => '{"periode":"' . $period . '"}',
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

    public function stock($period, $plant, $item_id = "")
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/ith';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/ith';
            }

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
                CURLOPT_POSTFIELDS => '{"cutoff":"' . $period . '", "item_id":"'.$item_id.'"}',
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

    public function wo($period_1, $period_2, $period_3, $plant)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/work_order';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/work_order';
            }

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
                CURLOPT_POSTFIELDS => '{"period_1":"' . $period_1 . '","period_2":"' . $period_2 . '","period_3":"' . $period_3 . '"}',
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

    public function history_po($plant, $period, $item_id)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/history_po';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/history_po';
            }

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
                CURLOPT_POSTFIELDS => '{"periode":"' . $period . '","item_id":"' . $item_id . '"}',
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

    public function forecast($plant, $period, $item_id)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/forecast';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/forecast';
            }

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
                CURLOPT_POSTFIELDS => '{"periode":"' . $period . '","item_id":"' . $item_id . '"}',
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

    public function material_calculation($plant, $period)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/material_calculation';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/material_calculation';
            }

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
                CURLOPT_POSTFIELDS => '{"periode":"' . $period . '"}',
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

    public function schedule_lc_in($plant, $item_id, $date_from, $date_to)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/schedule_lc_in';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/schedule_lc_in';
            }

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
                CURLOPT_POSTFIELDS => '{"item_id":"' . $item_id . '","date_from":"' . $date_from . '","date_to":"' . $date_to . '"}',
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

    public function schedule_lc_now($plant, $item_id, $today)
    {
        $login = $this->login($plant);

        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/schedule_lc_now';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/schedule_lc_now';
            }

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
                CURLOPT_POSTFIELDS => '{"item_id":"' . $item_id . '","start_date":"' . $today . '"}',
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

    public function schedule_lc_eta($plant, $item_id, $working_date)
    {
        $login = $this->login($plant);

        $decoded = array();
        if ($login->information == 'Success') {
            $token = $login->token;

            if($plant == "GE"){
                $url = 'http://erp.banshu-ind.com/pro-api/schedule_lc_eta';
            }else{
                $url = 'http://erp.banshu-ind.com/pro-api-hedom/schedule_lc_eta';
            }

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
                CURLOPT_POSTFIELDS => '{"item_id":"' . $item_id . '","working_date":"' . $working_date . '"}',
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
