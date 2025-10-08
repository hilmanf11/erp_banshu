<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Marketing_price_wire extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        $data = [];
        $get = $this->db->get("marketing_price_wire")->result_array();
        foreach ($get as $index => $row) {
            $row['price'] = floatval($row['price']);
            $row['duty_price'] = floatval($row['import_duty']) * $row['price'];
            $row['insurance_price'] = floatval($row['insurance']) * $row['price'];
            $row['transport_price'] = floatval($row['transportation']) * $row['price'];
            $row['total_price'] = floatval(number_format($row['price'] + $row['duty_price'] + $row['insurance_price'] + $row['transport_price'], 4));
            array_push($data, $row);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
