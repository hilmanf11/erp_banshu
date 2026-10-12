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
            "TRIM(a.plant_id) AS plant,"
                . "TRIM(a.item_id) AS item,"
                . "TRIM(a.type) AS type_range,"
                . "TRIM(a.ven_id) AS supplier,"
                . "COALESCE(TRIM(b.name),'') AS supplier_name,"
                . "COALESCE(TRIM(b.type),'') AS type,"
                . "TRIM(a.maker_code) AS maker,"
                . "COALESCE(TRIM(g.name),'') AS maker_name,"
                . "TRIM(a.data_lme) AS lme,"
                . "COALESCE(TRIM(d.percentage),0) AS duty,"
                . "COALESCE(TRIM(e.percentage),0) AS insurance,"
                . "TRIM(a.currency) AS currency,"
                . "COALESCE(TRIM(f.percentage),0) AS transport"
        )->from("lme_prices a")
            ->join("suppliers b", "a.ven_id = b.number")
            ->join("mst_import_duty d", "a.plant_id = d.plant AND b.id = d.supplier_id AND a.item_id = d.item_id", "left")
            ->join("mst_assurance e", "b.type = e.type")
            ->join("mst_transportation f", "b.type = f.type")
            ->join("makers g", "a.maker_code = g.number");
        if ($param != '') {
            $this->db->where("a.plant_id", $param);
        }
        $this->db->order_by('plant, item');
        $get = $this->db->get()->result_object();
        // die(var_dump($get));
        foreach ($get as $index => $row) {
            $ranges = json_decode($row->lme);
            foreach ($ranges as $key => $value) {
                $price = floatval($value->price);
                $duty_price = (floatval($row->duty) / 100) * $price;
                $insurance_price = floatval($row->insurance) * $price;
                $transport_price = floatval($row->transport) * $price;
                $total_price = floatval(number_format($price + $duty_price + $insurance_price + $transport_price, 4));
                array_push($data, [
                    "plant_id" => $row->plant,
                    "item_id" => $row->item,
                    "maker_code" => $row->maker,
                    "maker_name" => $row->maker_name,
                    "type" => $row->type,
                    "type_range" => $row->type_range,
                    "ranges" => $value->range,
                    "supp_id" => $row->supplier,
                    "price" => $price,
                    // "supplier_name" => $row->supplier_name,
                    "duty" => $row->duty,
                    "insurance" => $row->insurance,
                    "transport" => $row->transport,
                    "duty_price" => $duty_price,
                    "insurance_price" => $insurance_price,
                    "transport_price" => $transport_price,
                    "currency" => $row->currency,
                    "total_price" => $total_price
                ]);
            }
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
