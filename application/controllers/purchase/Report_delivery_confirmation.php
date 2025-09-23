<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_delivery_confirmation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/report_delivery_confirmation');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=outstanding_po_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status = base64_decode($this->input->get('filter_status'));
        $filter_po_no = base64_decode($this->input->get('filter_po_no'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));
        $filter_po_date = base64_decode($this->input->get('filter_po_date'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));

        $this->db->select('a.*, b.name as supplier_name, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, e.receipt_no');
        $this->db->from('purchase_confirm a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('purchase_order_receipts e', 'a.invoice_no = e.bc_document and a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id', 'left');
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') >=", $filter_from);
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m-%d') <=", $filter_to);
        }
        if($filter_po_date != ""){
            $this->db->where('a.po_date', $filter_po_date);
        }
        if($filter_invoice_no != ""){
            $this->db->where('a.invoice_no', $filter_invoice_no);
        }
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('c.item_family_id', $filter_product_family);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.delivery_no', 'ASC');
        $this->db->order_by('a.item_rm_id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>DELIVERY CONFIRMATION REPORT</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Status</th>
                <th>Receipt No</th>
                <th>Supplier Name</th>
                <th>Delivery No</th>
                <th>Invoice No</th>
                <th>PO No</th>
                <th>PO Date</th>
                <th>Part No Original</th>
                <th>Part No Supplier</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>Qty PO</th>
                <th>Qty<br>Delivery</th>
                <th>ETD</th>
                <th>ETA</th>
                <th>Shipping</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == "0"){
                $status = "OPEN";
                $styleStatus = "style='background:#53D636;color:white;'";
            }else{
                $status = "CLOSE";
                $styleStatus = "style='background:#FF5F5F;color:white;'";
            }
            
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td '.$styleStatus.'>' . $status . '</td>
                            <td>' . $data['receipt_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['delivery_no'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['po_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td>' . $data['item_family_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['delivery'] . '</td>
                            <td>' . $data['etd'] . '</td>
                            <td>' . $data['eta'] . '</td>
                            <td>' . $data['shipping'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
