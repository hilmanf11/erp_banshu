<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends CI_Controller
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
        redirect('error_access');
    }

    // public function readSummary()
    // {
    //     if($this->input->get()){
    //         $filter_month = $this->input->get('pmonth');
    //         $filter_year = $this->input->get('pyear');
    //         $filter_revision = $this->input->get('rev');
    //         $filter_supplier = $this->input->get('supplier');
    //         $filter_product_family = $this->input->get('prodfam');
    //         $filter_po_no = $this->input->get('pono');
    //         $filter_part_no = base64_decode($this->input->get('partno'));

    //         //Select Query
    //         $this->db->select('a.approved_by, a.approved_date, a.approved_to, a.purchase_order_no, a.purchase_order_date, a.product_family, 
    //             a.product_family_name, b.number as supplier_no, b.name as supplier_name, a.currency, SUM(a.total) as total_amount,
    //             SUM(a.qty) as total_qty, COUNT(c.po_no) as print_sd');
    //         $this->db->from('purchase_orders a');
    //         $this->db->join('suppliers b', "a.supplier_id = b.id", 'left');
    //         $this->db->join('(select DISTINCT po_no from delivery_schedules) c', "a.purchase_order_no = c.po_no", 'left');
    //         // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
    //         $this->db->where('a.p_month', $filter_month);
    //         $this->db->where('a.p_year', $filter_year);
    //         $this->db->where('a.revision', $filter_revision);
    //         if($filter_supplier != ""){
    //             $this->db->where('b.number', $filter_supplier);
    //         }
    //         if($filter_product_family != ""){
    //             $this->db->where('a.product_family', $filter_product_family);
    //         }
    //         if($filter_po_no != ""){
    //             $this->db->where('a.purchase_order_no', $filter_po_no);
    //         }
    //         if($filter_part_no != ""){
    //             $this->db->where('a.item_id', $filter_part_no);
    //         }
    //         // $this->db->group_by('a.product_family');
    //         $this->db->group_by('a.purchase_order_no');
    //         $this->db->order_by('b.number');
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();

    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['data' => $records]);
    //         die(json_encode($result));
    //     }else{
    //         show_error("Cannot Process your Request");
    //     }
    // }

    // public function readDetails(){
    //     if($this->input->get()){
    //         $filter_month = $this->input->get('pmonth');
    //         $filter_year = $this->input->get('pyear');
    //         $filter_revision = $this->input->get('rev');
    //         $filter_supplier = $this->input->get('supplier');
    //         $filter_product_family = $this->input->get('prodfam');
    //         $filter_po_no = $this->input->get('pono');
    //         $filter_part_no = base64_decode($this->input->get('partno'));

    //         //Select Query
    //         $this->db->select('a.*, b.number as supplier_no, b.name as supplier_name');
    //         $this->db->from('purchase_orders a');
    //         $this->db->join('suppliers b', "a.supplier_id = b.id", 'left');
    //         // $this->db->where('a.p_month', $filter_month);
    //         // $this->db->where('a.p_year', $filter_year);
    //         // $this->db->where('a.revision', $filter_revision);
    //         $this->db->where('a.purchase_order_no', $filter_po_no);
    //         if($filter_supplier != ""){
    //             $this->db->where('b.number', $filter_supplier);
    //         }
    //         if($filter_product_family != ""){
    //             $this->db->where('a.product_family', $filter_product_family);
    //         }
    //         if($filter_part_no != ""){
    //             $this->db->where('a.item_id', $filter_part_no);
    //         }
    //         $this->db->order_by('a.item_id');
    //         //Total Data
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();

    //         $data = array();
    //         $total_qty = 0;
    //         $total_amount = 0;
    //         foreach ($records as $record) {
    //             $total_qty += $record['qty'];
    //             $total_amount += $record['total'];
    //             array_push($data, $record);
    //         }

    //         if($total_qty > 0){
    //             $total_price = ($total_amount / $total_qty);
    //         }else{
    //             $total_price = 0;
    //         }

    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, [
    //             'rows' => $data,
    //             'footer' => array([
    //                 "item_id" => "Grand Total",
    //                 "qty" => $total_qty,
    //                 "price" => $total_price,
    //                 "total" => $total_amount
    //             ])
    //         ]);

    //         echo json_encode($result);
    //     }else{
    //         show_error("Cannot Process your Request");
    //     }
    // }

    // public function readPoNo()
    // {
    //     if($this->input->get()){
    //         $get = $this->input->get();

    //         //Select Query
    //         $this->db->select('purchase_order_no, purchase_order_date');
    //         $this->db->from('purchase_orders');
    //         if($get != ""){
    //             $this->db->like($get);
    //         }
    //         $this->db->group_by('purchase_order_no');
    //         $this->db->order_by('purchase_order_no', 'asc');
    //         $totalRows = $this->db->count_all_results('', false);
    //         //Get Data Array
    //         $records = $this->db->get()->result_array();

    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['data' => $records]);
    //         die(json_encode($result));
    //     }else{
    //         show_error("Cannot Process your Request");
    //     }
    // }

    public function readCountPoSita()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $this->db->distinct()->select("
            po_no as purchase_order_no, 
            po_date as purchase_order_date, 
            'REGULAR' as type")
        ->from("purchase_orders")->where(["supplier_id" => "SUP-01-01"]);
        echo json_encode(["status" => "success", "data" => ["count" => count($this->db->get()->result())]]);
    }

    public function readAllPoSita()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $result = [];
        $this->db->distinct()->select("po_no as purchase_order_no, 
            po_date as purchase_order_date, 
            'REGULAR' as type")
        ->from("purchase_orders")->where(["supplier_id" => "SUP-01-01"]);
        foreach ($this->db->get()->result() as $index => $row) {
            array_push($result, [
                "po_no" => $row->purchase_order_no,
                "po_date" => $row->purchase_order_date,
                "category" => "RAW MATERIAL",
                "sub_category" => "",
                "po_type" => "KB",
                "type_part" => strtoupper($row->type)
            ]);
        }
        echo json_encode(["status" => "success", "data" => $result]);
    }

    public function readPoSita()
    {
        header('Content-Type: application/json; charset=UTF-8');
        
        $result = ["status" => "success", "data" => []];
        $po = $this->input->get("po_no");
        if (!empty($po)) {
            $suppliers = [];
            foreach ($this->crud->reads("suppliers") as $index => $row) { $suppliers[$row->id] = $row->name; }

            $this->db->distinct()->select("
                a.po_no as purchase_order_no, 
                a.po_date as purchase_order_date, 
                a.supplier_id, 
                a.item_rm_id, 
                d.number as item_rm_number, 
                d.name as item_rm_name, 
                b.item_supplier, 
                a.uom, 
                a.qty, 
                a.price, 
                e.forecast_m1, 
                e.forecast_m2, 
                e.forecast_m3, 
                a.delivery_date, 
                a.revision,
                a.note, 
                a.reason, 
                c.type as maker_type")->from("purchase_orders a")
            ->join("("."SELECT * from supplier_items WHERE (approved_to = '' OR approved_to IS NULL) AND share_order > 0".") b", "a.supplier_id = b.supplier_id and a.item_rm_id = b.item_rm_id", "LEFT")
            ->join("item_rm d", "a.item_rm_id = d.id")
            ->join("makers c", "b.maker_id = c.id", "LEFT")
            ->join("purchase_requests e", "a.request_no = e.request_no and a.item_rm_id = e.item_rm_id", "LEFT")
            ->where(["a.supplier_id" => "SUP-01-01", "a.po_no" => $po]);
            foreach ($this->db->get()->result() as $index => $row) {
                array_push($result["data"], [
                    "company" => "BEI",
                    "po_no" => $row->purchase_order_no,
                    "po_date" => $row->purchase_order_date,
                    "supplier_name" => strtoupper($suppliers[$row->supplier_id] ?? ""),
                    "category" => "RAW MATERIAL",
                    "part_id" => $row->item_rm_id,
                    "part_name" => $row->item_rm_name,
                    "part_number" => $row->item_rm_number,
                    "uom_name" => $row->uom,
                    "supplier_product" => $row->item_supplier,
                    "qty" => intval($row->qty),
                    "price" => floatval($row->price),
                    "forecast_1" => intval($row->forecast_m1),
                    "forecast_2" => intval($row->forecast_m2),
                    "forecast_3" => intval($row->forecast_m3),
                    "forecast_4" => 0,
                    "eta" => $row->delivery_date,
                    "etd" => date("Y-m-d", strtotime("-".(($row->maker_type == "LOCAL") ? 14 : 21)." days", strtotime($row->delivery_date))),
                    "revision" => $row->revision,
                    "remark" => $row->note,
                    "remark_revision" => $row->reason
                ]);
            }
        } else { $result["status"] = "Po No is undefined!"; }
        echo json_encode($result);
    }
}