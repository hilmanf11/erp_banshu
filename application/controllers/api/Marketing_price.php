<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Marketing_price extends CI_Controller

{

    public function __construct()

    {

        parent::__construct();

        $this->load->helper('url');

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');

        $this->load->library('session');

        $this->load->model('crud');

        $this->load->library('Ciqrcode');
    }



    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);

            $this->load->view('planning/marketing_price');
        } else {

            redirect('error_access');
        }
    }

    public function readTotalPrice()
    {
        $filter_plant = base64_decode($this->input->get('plant'));
        $filter_suppliers = base64_decode($this->input->get('suppliers'));
        $filter_category = base64_decode($this->input->get('category'));
        $filter_maker  = base64_decode($this->input->get('maker'));
        $filter_item_rm = base64_decode($this->input->get('items'));
        $filter_item_vendor = base64_decode($this->input->get('item_vendor'));
        $filter_share_order = base64_decode($this->input->get('share_order'));
        $filter_check = $this->input->get('check');
        $result = array();

        $this->db->select('a.*, c.number as item_rm_number, (case when g.uom_po is not null then g.uom_po else c.uom end) as uom,
            c.name as item_rm_name, a.item_supplier, b.number as supplier_number, b.origin, b.name as supplier_name,
            d.name as item_family_name, b.type, b.supplier_po, e.name as maker_name, e.type as maker_type, e.number as maker_code, 
            (case when i.currency_to is not null then i.currency_to else a.currency end) as currency_converted, 
            a.currency as currency_original');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('item_rm c', 'c.id = a.item_rm_id');
        $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
        $this->db->join('makers e', 'a.maker_id = e.id', 'left');
        $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->join("exchange_rates i", 'i.start_date <= a.valid_date AND i.end_date >= a.valid_date and i.currency_from = a.currency', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_rm_id FROM supplier_items GROUP BY item_rm_id HAVING COUNT(*) > 1) h", 'a.item_rm_id = h.item_rm_id');
        }
        if ($filter_category != "") {
            $this->db->like('c.item_family_id', $filter_category);
        }
        if ($filter_suppliers != "") {
            $this->db->like('a.supplier_id', $filter_suppliers);
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_item_rm != "") {
            $this->db->like('c.number', $filter_item_rm);
        }
        if ($filter_maker != "") {
            $this->db->like('e.number', $filter_maker);
        }
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('c.number', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Filter: share_order > 0, ambil share_order tertinggi & valid_date terbaru per item_rm_id
        $filtered = [];
        foreach ($records as $record) {
            if ($record['share_order'] <= 0) continue;
            $key = $record['item_rm_id'];
            if (!isset($filtered[$key])) {
                $filtered[$key] = ['records' => [$record], 'max_share_order' => $record['share_order'], 'max_valid_date' => $record['valid_date']];
            } else {
                $existing = $filtered[$key];
                if ($record['share_order'] > $existing['max_share_order']) {
                    $filtered[$key] = ['records' => [$record], 'max_share_order' => $record['share_order'], 'max_valid_date' => $record['valid_date']];
                } elseif ($record['share_order'] == $existing['max_share_order']) {
                    if ($record['valid_date'] > $existing['max_valid_date']) {
                        $filtered[$key] = ['records' => [$record], 'max_share_order' => $record['share_order'], 'max_valid_date' => $record['valid_date']];
                    } elseif ($record['valid_date'] == $existing['max_valid_date']) {
                        $filtered[$key]['records'][] = $record;
                    }
                }
            }
        }
        // Flatten and average total_price for tied records, output one record per item_rm_id
        $records = [];
        foreach ($filtered as $group) {
            $count = count($group['records']);
            // Use first record as base
            $base = $group['records'][0];
            if ($count > 1) {
                $base['total_price'] = number_format((float)$base['total_price'] / $count, 4, ".", "");
            }
            $records[] = $base;
        }
        $totalRows = count($records);
        //Mapping Data
        $arr = [];
        foreach ($records as $record) {
            if ($record['type'] == 'IMPORT') {
                $assurance = $this->crud->read('mst_assurance', [], ["type" => 'IMPORT']);
                $transportation = $this->crud->read('mst_transportation', [], ["type" => 'IMPORT']);
            } else {
                $assurance = $this->crud->read('mst_assurance', [], ["type" => 'LOCAL']);
                $transportation = $this->crud->read('mst_transportation', [], ["type" => 'LOCAL']);
            }

            $mst_import_duty = $this->crud->read('mst_import_duty', [], ["plant" => $record['plant'], "supplier_id" => $record['supplier_id'], "item_id" => $record['item_rm_id']]);
            if (!empty(@$mst_import_duty->percentage)) {
                $import_duty_price = $record['price'] * (@$mst_import_duty->percentage / 100);
            } else {
                $import_duty_price = 0;
            }

            $assurance_price = $record['price'] * @$assurance->percentage;
            $transportation_price = $record['price'] * @$transportation->percentage;
            $total_price = $record['price'] + $assurance_price + $transportation_price + $import_duty_price;

            $arr[] = array(
                "plant" => $record['plant'],
                "supplier_number" => $record['supplier_number'],
                "supplier_name" => $record['supplier_name'],
                "item_supplier" => $record['item_supplier'],
                "item_id" => $record['item_rm_number'],
                "item_name" => $record['item_rm_name'],
                "pfm_name" => $record['item_family_name'],
                "type" => $record['type'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "leadtime" => $record['leadtime'],
                "maker_code" => $record['maker_code'],
                "maker_name" => $record['maker_name'],
                "maker_type" => $record['maker_type'],
                "price" => $record['price'],
                "share_order" => $record['share_order'],
                "currency" => $record['currency_converted'],
                "currency_original" => $record['currency_original'],
                "start_date" => $record['start_date'],
                "valid_date" => $record['valid_date'],
                "assurance_price" => $assurance_price,
                "transportation_price" => $transportation_price,
                "import_duty_percentage" => @$mst_import_duty->percentage,
                "import_duty_price" => $import_duty_price,
                "total_price" => number_format($total_price, 4, ".", ""),
            );
        }
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);

        echo json_encode($result);
    }
}
