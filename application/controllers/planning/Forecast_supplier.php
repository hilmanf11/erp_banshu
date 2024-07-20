<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Forecast_supplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecast_supplier');
        } else {
            redirect('error_access');
        }
    }

    function readPoSearch(){
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $datas = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE supplier_id = '$supplier_id'");
        echo json_encode($datas);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.forecast_1 as forecast1, a.forecast_2 as forecast2, a.forecast_3 as forecast3, b.number as vendor_id, b.name as vendor_name, b.type as vendor_type, b.origin as vendor_origin, c.item_id as supplier_item_id, a.item_name as item_name, c.leadtime as supplier_item_leadtime, c.mpq as supplier_item_mpq, c.moq as supplier_item_moq, d.name as maker_name, d.type as maker_type');
            $this->db->from('purchase_orders a');
            $this->db->join('supplier_items c', "a.item_id = c.item_id AND a.supplier_id = c.supplier_id", 'left');
            $this->db->join('suppliers b', "c.supplier_id = b.id", 'left');
            $this->db->join('mst_maker d', "c.maker_code = d.number", 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('c.share_order >',0);
            $this->db->like('a.product_family_name', $filter_product_family);
            $this->db->like('a.item_id', $filter_part_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.purchase_order_no', $filter_po);
            $this->db->order_by('b.name', 'asc');
            $this->db->order_by('a.product_family_name', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_supplier_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        //Select Query
        $this->db->select('a.forecast_1 as forecast1, a.forecast_2 as forecast2, a.forecast_3 as forecast3, b.number as vendor_id, b.name as vendor_name, b.type as vendor_type, b.origin as vendor_origin, c.item_id as supplier_item_id, a.item_name as item_name, c.leadtime as supplier_item_leadtime, c.mpq as supplier_item_mpq, c.moq as supplier_item_moq, d.name as maker_name, d.type as maker_type');
        $this->db->from('purchase_orders a');
        $this->db->join('supplier_items c', "a.item_id = c.item_id AND a.supplier_id = c.supplier_id", 'left');
        $this->db->join('suppliers b', "c.supplier_id = b.id", 'left');
        $this->db->join('mst_maker d', "c.maker_code = d.number", 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('c.share_order >',0);
        $this->db->like('a.product_family_name', $filter_product_family);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_order_no', $filter_po);
        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('a.product_family_name', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST SUPPLIER</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Vendor ID</th>
                <th style="text-align:center;">Vendor Name</th>
                <th style="text-align:center;">Vendor Type</th>
                <th style="text-align:center;">Origin</th>
                <th style="text-align:center;">Item ID</th>
                <th style="text-align:center;">Item Name</th>
                <th style="text-align:center;">LeadTime</th>
                <th style="text-align:center;">MPQ</th>
                <th style="text-align:center;">MOQ</th>
                <th style="text-align:center;">Maker Name</th>
                <th style="text-align:center;">Maker Type</th>
                <th style="text-align:center;">Forecast 1</th>
                <th style="text-align:center;">Forecast 2</th>
                <th style="text-align:center;">Forecast 3</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['vendor_id'] . '</td>
                            <td>' . $data['vendor_name'] . '</td>
                            <td>' . $data['vendor_type'] . '</td>
                            <td>' . $data['vendor_origin'] . '</td>
                            <td>' . $data['supplier_item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['supplier_item_leadtime'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_item_mpq'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_item_moq'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td style="text-align:right;">' . $data['forecast1'] . '</td>
                            <td style="text-align:right;">' . $data['forecast2'] . '</td>
                            <td style="text-align:right;">' . $data['forecast3'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
