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

    public function index($param = '')
    {
        $data = [];

        $this->db->distinct()->select(
            "a.*,"
            ."COALESCE(TRIM(b.name),'') AS supp_name,"
            ."COALESCE(TRIM(c.name),'') AS maker_name,"
            ."COALESCE(TRIM(d.name),'') AS item_name,"
            ."COALESCE(TRIM(e.name),'') AS family_name"
        )->from("marketing_price_wire a")
            ->join("suppliers b", "a.supp_id = b.number")
            ->join("makers c", "a.maker_code = c.number")
            ->join("item_rm d", "a.item_id = d.number")
            ->join("item_familys e", "d.item_family_id = e.id");
        if (!empty($param)) { $this->db->like('a.item_id', base64_decode($param)); }
        $get = $this->db->get()->result_array();
        foreach ($get as $index => $row) {
            $row['price'] = floatval($row['price']);
            $row['duty_price'] = (floatval($row['import_duty']) / 100) * $row['price'];
            $row['insurance_price'] = floatval($row['insurance']) * $row['price'];
            $row['transport_price'] = (floatval($row['transportation']) / 100) * $row['price'];
            $row['total_price'] = floatval(number_format($row['price'] + $row['duty_price'] + $row['insurance_price'] + $row['transport_price'], 4));
            array_push($data, $row);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
