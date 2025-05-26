<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_store extends CI_Controller
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
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_history_transactions_store');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where('item_category_id !=', 'C01');
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');
        $filter_location_other_number = $this->input->get('filter_location_other_number');
        if (!empty($filter_location_other_number)) {
            $whereLocationSummary = "and location_other_number='$filter_location_other_number'";
        } else {
            $whereLocationSummary = "";
        }

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom, 
            COALESCE(h.begin_stock,0) AS begin_stock,
            COALESCE(c.qty_in,0) AS qty_in,
            COALESCE(d.qty_out,0) AS qty_out,
            (COALESCE(h.begin_stock,0) + COALESCE(c.qty_in,0) - COALESCE(d.qty_out,0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN (select item_rm_id, sum(qty) as qty_in from issued_others_store where trans_date between '$filter_from' and '$filter_to' and `type` = 'IN' and status <> '2'  $whereLocationSummary GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (select item_rm_id, sum(qty) as qty_out from issued_others_store where trans_date between '$filter_from' and '$filter_to' and `type` = 'OUT' and status <> '2' $whereLocationSummary GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            (COALESCE(c.qty_in,0) - COALESCE(d.qty_out,0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            JOIN (select item_rm_id, sum(qty) as qty_in from issued_others_store where trans_date < '$filter_from' and '$filter_to' and `type` = 'IN' and status <> '2' $whereLocationSummary GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (select item_rm_id, sum(qty) as qty_out from issued_others_store where trans_date < '$filter_from' and `type` = 'OUT' and status <> '2' $whereLocationSummary GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            GROUP BY a.id) h on a.id = h.id
        JOIN item_categories j ON a.item_category_id = j.id 
        WHERE a.id like '%$filter_items%' and b.number like '%$filter_item_family%' and j.id NOT IN ('C01','C08','C02')
        GROUP BY a.id
        ORDER BY a.number");

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION STORE</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Part No</th>
                    <th>Part No Supplier</th>
                    <th>Description</th>
                    <th>Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->id . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->end_stock, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="14" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Doc No</th>
                                <th>Location</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = @$record->begin_stock;

                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);
                    //ISSUED
                    if (!empty($filter_location_other_number)) {
                        $whereLocationDetail = "and a.location_other_number='$filter_location_other_number'";
                    } else {
                        $whereLocationDetail = "";
                    }
                    // //RECEIPT
                    // $receipts = $this->crud->query("SELECT a.*, b.name as location_other_name FROM issued_others_store a LEFT JOIN warehouse_location_others b on a.location_other_number=b.number WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date' and a.type = 'IN' $whereLocationDetail");

                    // $issueds = $this->crud->query("SELECT a.*, b.name as location_other_name FROM issued_others_store a LEFT JOIN warehouse_location_others b on a.location_other_number=b.number WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date' and a.type='OUT' $whereLocationDetail");

                    // //Purchase Order Receipt
                    // foreach ($receipts as $receipt) {
                    //     $userReceipt = $this->crud->read("users", [], ["username" => $receipt->created_by], "", "id", "asc");
                    //     $balance = ($begin + ($receipt->qty - $end_qty));
                    //     $html .= '  <tr>
                    //                     <td></td>
                    //                     <td style="text-align:center">' . $nod . '</td>
                    //                     <td>RECEIPT</td>
                    //                     <td>' . $userReceipt->name . '</td>
                    //                     <td>' . $receipt->trans_date . '</td>
                    //                     <td>' . $receipt->document_in . '</td>
                    //                     <td>' . $receipt->location_other_name . '</td>
                    //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                    //                     <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                    //                     <td style="text-align:right;">' . number_format(0)  . '</td>
                    //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                    //                 </tr>';
                    //     $begin += $receipt->qty;
                    //     $nod++;
                    // }

                    // //Issued Material
                    // foreach ($issueds as $issued) {
                    //     $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                    //     $balance = ($begin - $issued->qty);
                    //     $html .= '  <tr>
                    //                     <td></td>
                    //                     <td style="text-align:center">' . $nod . '</td>
                    //                     <td>ISSUED</td>
                    //                     <td>' . $user->name . '</td>
                    //                     <td>' . $issued->out_date . '</td>
                    //                     <td>' . $issued->request_no . '</td>
                    //                     <td>' . $issued->location_other_name . '</td>
                    //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                    //                     <td style="text-align:right;">' . number_format(0) . '</td>
                    //                     <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                    //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                    //                 </tr>';
                    //     $begin -= $issued->qty;
                    //     $nod++;
                    // }


                    //Issued Material
                    $issueds = $this->crud->query("SELECT a.*, b.name as location_other_name FROM issued_others_store a LEFT JOIN warehouse_location_others b on a.location_other_number=b.number WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date' and a.status <> '2'  $whereLocationDetail");

                    foreach ($issueds as $issued) {
                        $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                        if ($issued->type == "IN") {
                            $trans_type = "RECEIPT";
                            $trans_date = $issued->trans_date;
                            $document = $issued->document_in;
                            $in = $issued->qty;
                            $out = 0;
                            $balance = ($begin + $issued->qty);
                        } else {
                            $trans_type = "ISSUED";
                            $trans_date = $issued->out_date;
                            $document = $issued->request_no;
                            $in = 0;
                            $out = $issued->qty;
                            $balance = ($begin - $issued->qty);
                        }
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $trans_type . '</td>
                                        <td>' . $user->name . '</td>
                                        <td>' . $trans_date . '</td>
                                        <td>' . $document . '</td>
                                        <td>' . $issued->location_other_name . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($in, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($out, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        if ($issued->type == "IN") {
                            $begin += $issued->qty;
                        } else {
                            $begin -= $issued->qty;
                        }
                        $nod++;
                    }
                }
            }
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
