<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_other extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_other');
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
        $this->db->where_not_in('id', ['P51','P61','P67','P69','P71']);
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
        $filter_to = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

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
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0)) AS qty_in,
            (COALESCE(d.qty,0) + COALESCE(f.qty_rgp_out,0) + COALESCE(i.sto_qty_out, 0)) AS qty_out,
            (COALESCE(h.begin_stock,0) + COALESCE(c.qty_receipt,0) + COALESCE(g.sto_qty_in, 0) + COALESCE(e.qty_rgp_in,0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date between '$filter_from' and '$filter_to' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date between '$filter_from' and '$filter_to' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date between '$filter_from' and '$filter_to' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT
            a.id,
            (COALESCE(c.qty_receipt,0) + COALESCE(e.qty_rgp_in,0) + COALESCE(g.sto_qty_in, 0) - COALESCE(i.sto_qty_out, 0) - COALESCE(f.qty_rgp_out,0) - COALESCE(d.qty,0)) AS begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != '006'
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty_receipt), 0) as qty_receipt FROM purchase_order_receipt_others WHERE receipt_date < '$filter_from' and store != 0 GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_others WHERE trans_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_in FROM rgp_transactions WHERE type = 'IN' and transaction_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) e ON a.id = e.item_id
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_rgp_out FROM rgp_transactions WHERE type = 'OUT' and transaction_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_id) f ON a.id = f.item_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_in FROM sto_ot WHERE type = 'IN' and trans_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as sto_qty_out FROM sto_ot WHERE type = 'OUT' and trans_date < '$filter_from' and (approved_to = '' or approved_to is null) GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            GROUP BY a.id) h on a.id = h.id
        JOIN item_categories j ON a.item_category_id = j.id 
        WHERE a.id like '%$filter_items%' and b.number like '%$filter_item_family%' and j.id NOT IN ('C01','C08','C02') and a.item_family_id NOT IN ('P51','P61','P67','P69','P71')
        GROUP BY a.id
        ORDER BY a.number");

        // die($this->db->last_query());

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
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION OTHER</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="2">Part No</th>
                    <th>Part No Supplier</th>
                    <th colspan="3">Description</th>
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
                            <td colspan="3" style="mso-number-format:\@;">' . $record->name . '</td>
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

                    //RECEIPT
                    $receipts = $this->crud->query("SELECT
                        a.receipt_date, 
                        a.receipt_no,
                        a.bc_document, 
                        a.qty_receipt,
                        c.name as username,
                        d.id as supplier_id,
                        d.name as supplier_name
                    FROM purchase_order_receipt_others a 
                    LEFT JOIN users c ON a.created_by = c.username
                    LEFT JOIN suppliers d ON a.supplier_id = d.id
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date = '$working_date' and a.store != 0
                    GROUP BY a.id");

                    //ISSUED
                    $issueds = $this->crud->query("SELECT * FROM issued_others WHERE item_rm_id = '$item_rm_id' and trans_date = '$working_date'");

                    //STO
                    $sto_rms = $this->crud->query("SELECT
                        a.*,
                        c.name as username
                    FROM sto_ot a 
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.trans_date = '$working_date' and (a.approved_to = '' or a.approved_to is null)
                    GROUP BY a.id");

                    //RGP TRANSACTION
                    $rgps = $this->crud->query("SELECT 
                    a.*, case when b.name <> null then b.name else c.name end as company_name
                    FROM rgp_transactions a 
                    LEFT JOIN suppliers b ON a.company_id = b.id 
                    LEFT JOIN customers c ON a.company_id = c.id 
                    WHERE a.item_id = '$item_rm_id' and a.transaction_date = '$working_date' and (a.approved_to = '' or a.approved_to is null)");

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

                    //RGP
                    foreach ($rgps as $rgp) {
                        $user = $this->crud->read("users", [], ["username" => $rgp->created_by]);
                        if ($rgp->type == "IN") {
                            $balance = ($begin + $rgp->qty);
                            $rgp_status = "RGP IN";
                            $rgp_qty_in = $rgp->qty;
                            $rgp_qty_out = 0;
                        } else {
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
                                        <td>' . $rgp->number . '</td>
                                        <td>' . $rgp->reference_number . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($rgp_qty_in) . '</td>
                                        <td style="text-align:right;">' . number_format($rgp_qty_out, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';

                        if ($rgp->type == "IN") {
                            $begin += $rgp_qty_in;
                        } else {
                            $begin -= $rgp_qty_out;
                        }

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
                                        <td>' . $issued->trans_date . '</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>' . $issued->request_no . '</td>
                                        <td>' . $issued->por_no . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $issued->qty;
                        $nod++;
                    }

                    //STO
                    foreach ($sto_rms as $sto_rm) {
                        if ($sto_rm->type == "IN") {
                            $type = "STO ADJUSTMENT IN";
                            $qty_in = $sto_rm->qty;
                            $qty_out = 0;
                            $balance = ($begin + $sto_rm->qty);
                        } else {
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
                                        <td>-</td>
                                        <td>' . $sto_rm->document_no . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($qty_in, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($qty_out)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        if ($sto_rm->type == "IN") {
                            $begin += $sto_rm->qty;
                        } else {
                            $begin -= $sto_rm->qty;
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
