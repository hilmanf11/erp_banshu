<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions');
        } else {
            redirect('error_access');
        }
    }

    public function readEndingStock()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');

            if (@$trans_date == "") {
                $date = date("Y-m-d");
            } else {
                $date = $trans_date;
            }

            $records = $this->crud->query("SELECT
                a.id,
                a.number, 
                a.name, 
                b.name as prodfam, 
                a.uom, 
                COALESCE(0,0) as begin_stock,
                (COALESCE(SUM(c.qty_sto),0) + COALESCE(d.sto_qty_in, 0) + COALESCE(e.qty_receipt, 0)) as qty_in,
                (f.qty + COALESCE(d.sto_qty_out, 0) + COALESCE(h.qty_reject, 0)) as qty_out,
                ((COALESCE(SUM(c.qty_sto),0) + COALESCE(e.qty_receipt, 0)  + COALESCE(d.sto_qty_in, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(h.qty_reject, 0)) as end_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date <= '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, (CASE WHEN a.type = 'IN' THEN COALESCE(SUM(a.qty), 0) ELSE 0 END) as sto_qty_in, (CASE WHEN a.type = 'OUT' THEN COALESCE(SUM(a.qty), 0) ELSE 0 END) as sto_qty_out FROM sto_rm a WHERE a.trans_date < '$date' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date <= '$date' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date <= '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id = '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            echo json_encode($records);
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where('item_category_id', 'C01');
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

        if($filter_item_family != ""){
            $whereProdfam = "and b.number = '$filter_item_family'";
        }else{
            $whereProdfam = "";

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
            (COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in,0) + COALESCE(e.qty_receipt, 0) + COALESCE(j.qty_rgp_in, 0)) AS qty_in,
            (COALESCE(f.qty, 0) + COALESCE(i.qty_reject, 0) + COALESCE(k.qty_rgp_out, 0) + COALESCE(d.sto_qty_out, 0)) AS qty_out,
            ((COALESCE(h.begin_stock,0) + COALESCE(SUM(c.qty_sto),0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(SUM(dd.sto_qty_in),0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(i.qty_reject, 0) - COALESCE(k.qty_rgp_out, 0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.item_category_id = 'C01'
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date between '$filter_from' and '$filter_to' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date between '$filter_from' and '$filter_to' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date between '$filter_from' and '$filter_to' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date between '$filter_from' and '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) as qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date between '$filter_from' and '$filter_to' GROUP BY item_id) j ON a.id = j.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date between '$filter_from' and '$filter_to' GROUP BY item_id) k ON a.id = k.item_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            ((COALESCE(SUM(c.qty_sto),0) + COALESCE(dd.sto_qty_in, 0) + COALESCE(j.qty_rgp_in, 0) + COALESCE(e.qty_receipt, 0) + COALESCE(g.return_qty, 0)) - COALESCE(f.qty, 0) - COALESCE(d.sto_qty_out, 0) - COALESCE(k.qty_rgp_out, 0) - COALESCE(h.qty_reject, 0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_sto FROM os_rm a JOIN scan_item_receipts b ON a.id  = b.receipt_id WHERE a.trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_out FROM sto_rm a WHERE a.type = 'OUT' and a.trans_date < '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as sto_qty_in FROM sto_rm a WHERE a.type = 'IN' and a.trans_date < '$filter_from' and a.approved_to = '' GROUP BY item_rm_id) dd ON a.id = dd.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_receipt FROM purchase_order_receipts a JOIN scan_item_receipts b ON a.receipt_id  = b.receipt_id WHERE a.receipt_date < '$filter_from' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) AS qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, COALESCE(SUM(b.qty), 0) AS qty_reject FROM purchase_order_receipts a JOIN scan_item_rejections b ON a.receipt_id  = b.receipt_id WHERE DATE_FORMAT(b.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date < '$filter_from' GROUP BY item_id) j ON a.id = j.item_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date < '$filter_from' GROUP BY item_id) k ON a.id = k.item_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) AS return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
            WHERE a.return_date < '$filter_from'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            GROUP BY a.id) h ON a.id = h.id
        WHERE a.id like '%$filter_items%' $whereProdfam
        GROUP BY a.id
        ORDER BY b.number, a.number");

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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Part No</th>
                    <th colspan="2">Part No Supplier</th>
                    <th colspan="2">Description</th>
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
                            <td colspan="2" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->end_stock, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Doc No</th>
                                <th>Invoice No</th>
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

                    //BEGIN STOCK
                    $os_rms = $this->crud->query("SELECT
                        a.*,
                        SUM(b.qty) as qty,
                        c.name as username
                    FROM os_rm a 
                    JOIN scan_item_receipts b ON a.id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date'
                    GROUP BY a.id");

                    //STO
                    $sto_rms = $this->crud->query("SELECT
                        a.*,
                        c.name as username
                    FROM sto_rm a 
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date'
                    GROUP BY a.id");

                    //RECEIPT
                    $receipts = $this->crud->query("SELECT
                        a.receipt_date, 
                        a.receipt_no, 
                        a.bc_document, 
                        SUM(b.qty) as qty_receipt,
                        c.name as username,
                        d.id as supplier_id,
                        d.name as supplier_name
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    JOIN suppliers d ON a.supplier_id = d.id
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                    GROUP BY a.receipt_no, a.bc_document");

                    //REJECTION
                    $rejections = $this->crud->query("SELECT
                        b.created_date, 
                        a.bc_document, 
                        b.label_no, 
                        SUM(b.qty) as qty,
                        c.name as username,
                        d.id as supplier_id,
                        d.name as supplier_name
                    FROM purchase_order_receipts a 
                    JOIN scan_item_rejections b ON a.receipt_id = b.receipt_id
                    JOIN users c ON b.created_by = c.username
                    JOIN suppliers d ON a.supplier_id = d.id
                    WHERE a.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.created_date, '%Y-%m-%d') = '$working_date'
                    GROUP BY b.id");

                    //ISSUED
                    $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                    //RETURN
                    $returns = $this->crud->query("SELECT
                        a.return_no,
                        a.return_id,
                        a.return_name,
                        a.return_date,
                        b.label_no,
                        b.qty,
                        d.name as username
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$working_date' and '$working_date'
                    GROUP BY b.label_no");

                    //RGP TRANSACTION
                    $rgps = $this->crud->query("SELECT a.*, b.name as company_name FROM rgp_transactions a JOIN suppliers b ON a.company_id = b.id WHERE a.item_id = '$item_rm_id' and a.transaction_date = '$working_date'");

                    //BEGIN STOCK
                    foreach ($os_rms as $os_rm) {
                        $balance = ($begin + $os_rm->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>STO</td>
                                        <td>' . $os_rm->username . '</td>
                                        <td>' . $os_rm->trans_date . '</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>' . $os_rm->document_no . '</td>
                                        <td>' . $os_rm->id . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($os_rm->qty) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $os_rm->qty;
                        $nod++;
                    }

                    //STO
                    foreach ($sto_rms as $sto_rm) {
                        if($sto_rm->type == "IN"){
                            $type = "STO ADJUSTMENT IN";
                            $qty_in = $sto_rm->qty;
                            $qty_out = 0;
                            $balance = ($begin + $sto_rm->qty);
                        }else{
                            $type = "STO ADJUSTMENT OUT";
                            $qty_in = 0;
                            $qty_out = $sto_rm->qty;
                            $balance = ($begin - $sto_rm->qty);
                        }

                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $type . '</td>
                                        <td>' . $sto_rm->username . '</td>
                                        <td>' . $sto_rm->trans_date . '</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>' . $sto_rm->document_no . '</td>
                                        <td>-</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($qty_in, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($qty_out)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        if($sto_rm->type == "IN"){
                            $begin += $sto_rm->qty;
                        }else{
                            $begin -= $sto_rm->qty;
                        }

                        $nod++;
                    }

                    //Purchase Order Receipt
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>RECEIPT</td>
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->receipt_date . '</td>
                                        <td>' . $receipt->supplier_id . '</td>
                                        <td>' . $receipt->supplier_name . '</td>
                                        <td>' . $receipt->receipt_no . '</td>
                                        <td>' . $receipt->bc_document . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $receipt->qty_receipt;
                        $nod++;
                    }

                    //Rejections
                    foreach ($rejections as $rejection) {
                        $balance = ($begin - $rejection->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>REJECTION</td>
                                        <td>' . $rejection->username . '</td>
                                        <td>' . $rejection->created_date . '</td>
                                        <td>' . $rejection->supplier_id . '</td>
                                        <td>' . $rejection->supplier_name . '</td>
                                        <td>' . $rejection->label_no . '</td>
                                        <td>' . $rejection->bc_document . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($rejection->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $rejection->qty;
                        $nod++;
                    }
                    
                    //Issued Material
                    foreach ($issueds as $issued) {
                        $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                        $balance = ($begin - $issued->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>ISSUED</td>
                                        <td>' . $user->name . '</td>
                                        <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>' . $issued->request_no . '</td>
                                        <td>-</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $issued->qty;
                        $nod++;
                    }

                    //Return Material
                    foreach ($returns as $return) {
                        $balance = ($begin + $return->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>RETURN</td>
                                        <td>' . $return->username . '</td>
                                        <td>' . date("Y-m-d", strtotime($return->return_date)) . '</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>' . $return->return_no . '</td>
                                        <td>-</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $return->qty;
                        $nod++;
                    }

                    //RGP
                    foreach ($rgps as $rgp) {
                        $user = $this->crud->read("users", [], ["username" => $rgp->created_by]);
                        if($rgp->type == "IN"){
                            $balance = ($begin + $rgp->qty);
                            $rgp_status = "RGP IN";
                            $rgp_qty_in = $rgp->qty;
                            $rgp_qty_out = 0;
                        }else{
                            $rgp_status = "RGP OUT";
                            $balance = ($begin - $rgp->qty);
                            $rgp_qty_in = 0;
                            $rgp_qty_out = $rgp->qty;
                        }

                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $rgp_status . '</td>
                                        <td>' . $user->name . '</td>
                                        <td>' . $rgp->transaction_date . '</td>
                                        <td>' . $rgp->company_id . '</td>
                                        <td>' . $rgp->company_name . '</td>
                                        <td>' . $rgp->reference_number . '</td>
                                        <td>' . $rgp->rgp_number . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($rgp_qty_in) . '</td>
                                        <td style="text-align:right;">' . number_format($rgp_qty_out, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        
                        if($rgp->type == "IN"){
                            $begin += $rgp_qty_in;
                        }else{
                            $begin -= $rgp_qty_out;
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
