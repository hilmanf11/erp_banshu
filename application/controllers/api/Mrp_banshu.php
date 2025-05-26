<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Mrp_banshu extends CI_Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('mrp');
    }

    public function index()
    {
        redirect('error_access');
    }

    public function getDataFamilys()
    {

        $send = $this->mrp->prodfam();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $arr[] = array(
                    "pfm_id" => $send[$i]->pfm_id,
                    "pfm_name" => $send[$i]->pfm_name,
                );
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataItems()
    {

        $send = $this->mrp->master_item();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                if (trim($send[$i]->pfm_id) != '06' && trim($send[$i]->stscode_id) == '01') { // type raw material dan yang aktif
                    $arr[] = array(
                        "item_family_id" => trim($send[$i]->pfm_id),
                        "number" => trim($send[$i]->item_id),
                        "name" => trim($send[$i]->item_name),
                        "um_id" => trim($send[$i]->um_id),
                        "maker_id" => trim($send[$i]->maker_id),
                    );
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }
}
