<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap_v2 extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_ap_v2');
        } else {
            redirect('error_access');
        }
    }

    public function readPi($supplier_id)
    {
        $query = $this->crud->query("SELECT DISTINCT `number` FROM purchase_invoices WHERE supplier_id = '$supplier_id' ORDER BY `number` ASC");
        die(json_encode($query));
    }

    public function begin_balance($supplier_id, $filter_from, $filter_status = "", $balance_local){
        //Purchase invoicing
        $query_1 = $this->db->query("SELECT 
            'PI' AS source,
            a.invoice_date as trans_date,
            a.due_date,
            a.number AS document_no,
            a.invoice_no AS invoice_no,
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            a.currency,
            b.local_debit,
            b.local_credit,
            a.status
        FROM purchase_invoices a
        JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY number, document_no, account_number) b ON a.number = b.document_no
        WHERE a.supplier_id = '$supplier_id' and b.journal_date < '$filter_from'
        GROUP BY a.number
        ORDER BY b.journal_date asc");
        $data_1 = $query_1->result_array();

        //AP Payments
        $query_2 = $this->db->query("SELECT
            'AP' AS source,
            a.payment_date AS trans_date,
            '-' AS due_date,
            a.payment_no AS document_no,
            a.supplier_invoice AS invoice_no,
            a.remarks AS description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            a.currency,
            b.local_debit,
            b.local_credit,
            a.status
        FROM ap_payments a
        LEFT JOIN (SELECT journal_date, number, document_no, account_number, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
            FROM journal_postings 
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY number, document_no, account_number) b ON a.payment_no = b.document_no
        WHERE a.supplier_id = '$supplier_id' and b.journal_date < '$filter_from' 
        GROUP BY a.payment_no");
        $data_2 = $query_2->result_array();

        //Journal Entry
        $query_3 = $this->db->query("SELECT
            'AD' AS source,
            a.trans_date,
            '-' AS due_date,
            a.document_no,
            a.invoice_no,
            a.description,
            a.journal_date AS voucher_date,
            a.number AS voucher_no,
            a.account_number,
            a.currency,
            SUM(a.local_debit) AS local_debit,
            SUM(a.local_credit) AS local_credit,
            (CASE WHEN SUM(a.local_debit) > 0 THEN 1 ELSE 0 END) as status
        FROM journal_postings a
        WHERE a.company_id = '$supplier_id' 
        AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
        AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006')
        AND a.journal_date < '$filter_from'
        GROUP BY a.number
        ORDER BY a.journal_date ASC");
        $data_3 = $query_3->result_array();
        $purchase_invoices = array_merge($data_1, $data_2, $data_3);

        $local_debit = 0;
        $local_credit = 0;
        foreach ($purchase_invoices as $purchase_invoice) {
            $local_debit += $purchase_invoice['local_debit'];
            $local_credit += $purchase_invoice['local_credit'];
        }

        $begin_balance = ($balance_local + $local_credit - $local_debit);
        return $begin_balance;
    }

    public function formatNo($amount){
        if ($amount >= 0) {
            return number_format(@$amount, 2);
        } else {
            return "<span style='color:red;'>(" . number_format(abs($amount), 2) . ")</span>";
        }
    }

    public function datatables($option = "")
    {
        $post = $this->input->post();
        $filter_from = $post['filter_from'];
        $filter_to = $post['filter_to'];
        $filter_supplier = $post['filter_supplier'];
        $filter_display = $post['filter_display'];
        $filter_status = $post['filter_status'];
        $period = date("Ym", strtotime($filter_to));

        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->like("a.id", $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();
        
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_local_balance = 0;

        function compare_trans_date2($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }

        $arr = array();
        foreach ($suppliers as $supplier) {
            $supplier_id = $supplier['id'];

            if($filter_display != "Invoice"){
                $where_inv = "";
            }else{
                $where_inv = "AND a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices)";
            }

            //Purchase invoicing
            $query_1 = $this->db->query("SELECT 
                'PI' AS source,
                a.invoice_date as trans_date,
                a.due_date,
                a.number AS document_no,
                a.invoice_no AS invoice_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                b.local_debit,
                b.local_credit,
                a.status
            FROM purchase_invoices a
            JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY number, document_no, account_number) b ON a.number = b.document_no
            WHERE a.supplier_id = '$supplier_id' and b.journal_date BETWEEN '$filter_from' and '$filter_to' and a.status like '%$filter_status%'
            GROUP BY a.number
            ORDER BY b.journal_date asc");
            $data_1 = $query_1->result_array();

            //AP Payments
            $query_2 = $this->db->query("SELECT
                'AP' AS source,
                a.payment_date AS trans_date,
                '-' AS due_date,
                a.payment_no AS document_no,
                a.supplier_invoice AS invoice_no,
                a.remarks AS description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                b.local_debit,
                b.local_credit,
                a.status
            FROM ap_payments a
            LEFT JOIN (SELECT journal_date, number, document_no, account_number, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY number, document_no, account_number) b ON a.payment_no = b.document_no
            WHERE a.supplier_id = '$supplier_id' $where_inv and b.journal_date between '$filter_from' and '$filter_to'
            GROUP BY a.payment_no");
            $data_2 = $query_2->result_array();

            //Journal Entry
            $query_3 = $this->db->query("SELECT
                'AD' AS source,
                a.trans_date,
                '-' AS due_date,
                a.document_no,
                a.invoice_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                a.currency,
                SUM(a.local_debit) AS local_debit,
                SUM(a.local_credit) AS local_credit,
                (CASE WHEN SUM(a.local_debit) > 0 THEN 1 ELSE 0 END) as status
            FROM journal_postings a
            WHERE a.company_id = '$supplier_id' 
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006')
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to'
            GROUP BY a.number
            ORDER BY a.journal_date ASC");
            $data_3 = $query_3->result_array();
            $purchase_invoices = array_merge($data_1, $data_2, $data_3);

            usort($purchase_invoices, 'compare_trans_date2');

            $local_debit = 0;
            $local_credit = 0;

            $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_status, @$supplier['balance_local']);

            $accumulated = $begin_balance_local;
            foreach ($purchase_invoices as $purchase_invoice) {
                $balance_local = round($purchase_invoice['local_credit'] - $purchase_invoice['local_debit'], 2);
                $accumulated += $balance_local;

                $arr[] = array(
                    "supplier_name" => $supplier['name'], 
                    "status" => $purchase_invoice['status'],
                    "source" => $purchase_invoice['source'],
                    "trans_date" => $purchase_invoice['trans_date'],
                    "due_date" => $purchase_invoice['due_date'],
                    "document_no" => $purchase_invoice['document_no'],
                    "invoice_no" => $purchase_invoice['invoice_no'],
                    "description" => $purchase_invoice['description'],
                    "voucher_date" => $purchase_invoice['voucher_date'],
                    "voucher_no" => $purchase_invoice['voucher_no'],
                    "account_number" => $purchase_invoice['account_number'],
                    "currency" => $purchase_invoice['currency'],
                    "begin" => $begin_balance_local,
                    "local_debit" => $purchase_invoice['local_debit'],
                    "local_credit" => $purchase_invoice['local_credit'],
                    "balance" => $balance_local,
                    "accumulated" => $accumulated,
                );

                $local_debit += $purchase_invoice['local_debit'];
                $local_credit += $purchase_invoice['local_credit'];
            }

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
            "balance" => $grand_local_balance,
        );

        $result['total'] = count($arr);
        $result = array_merge($result, ['rows' => $arr, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_currency = $this->input->get("filter_currency");
        $filter_payment = $this->input->get("filter_payment");
        $filter_display = $this->input->get("filter_display");
        $filter_purchase_invoice = base64_decode($this->input->get("filter_purchase_invoice"));
        $filter_status = $this->input->get("filter_status");
        $period = date("Ym", strtotime($filter_to));

        if($filter_status == "0"){
            $ap_status = "1";
        }elseif($filter_status == "1"){
            $ap_status = "0";
        }else{
            $ap_status = "";
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->like("a.id", $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            <center>
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>(' . $filter_display . ')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

        $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Currency</th>
                                <th>Amount</th>
                            </tr>';

        $invoice = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Descripstion</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Invoice</th>
                            <th>Payment</th>
                            <th>Remain</th>
                            <th>Accumulated</th>
                        </tr>';

        $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Descripstion</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                            <th>Accumulated</th>
                        </tr>';
        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_local_balance = 0;

        function compare_trans_date($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }

        foreach ($suppliers as $supplier) {
            $supplier_id = $supplier['id'];

            if($filter_display != "Invoice"){
                $where_inv = "";
            }else{
                $where_inv = "AND a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices)";
            }

            //Purchase invoicing
            $query_1 = $this->db->query("SELECT 
                'PI' AS source,
                a.invoice_date as trans_date,
                a.due_date,
                a.number AS document_no,
                a.invoice_no AS invoice_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                b.local_debit,
                b.local_credit,
                a.status
            FROM purchase_invoices a
            JOIN (SELECT journal_date, number, document_no, account_number, description, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY number, document_no, account_number) b ON a.number = b.document_no
            WHERE a.supplier_id = '$supplier_id' and b.journal_date BETWEEN '$filter_from' and '$filter_to' and a.status like '%$filter_status%'
            GROUP BY a.number
            ORDER BY b.journal_date asc");
            $data_1 = $query_1->result_array();

            // 63,533.69

            //AP Payments
            $query_2 = $this->db->query("SELECT
                'AP' AS source,
                a.payment_date AS trans_date,
                '-' AS due_date,
                a.payment_no AS document_no,
                a.supplier_invoice AS invoice_no,
                a.remarks AS description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                b.local_debit,
                b.local_credit,
                a.status
            FROM ap_payments a
            LEFT JOIN (SELECT journal_date, number, document_no, account_number, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY number, document_no, account_number) b ON a.payment_no = b.document_no
            WHERE a.supplier_id = '$supplier_id' $where_inv and b.journal_date between '$filter_from' and '$filter_to'
            GROUP BY a.payment_no");
            $data_2 = $query_2->result_array();

            //Journal Entry
            $query_3 = $this->db->query("SELECT
                'AD' AS source,
                a.trans_date,
                '-' AS due_date,
                a.document_no,
                a.invoice_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                a.currency,
                SUM(a.local_debit) AS local_debit,
                SUM(a.local_credit) AS local_credit,
                (CASE WHEN SUM(a.local_debit) > 0 THEN 1 ELSE 0 END) as status
            FROM journal_postings a
            WHERE a.company_id = '$supplier_id' 
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006')
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to'
            GROUP BY a.number
            ORDER BY a.journal_date ASC");
            $data_3 = $query_3->result_array();
            $purchase_invoices = array_merge($data_1, $data_2, $data_3);

            usort($purchase_invoices, 'compare_trans_date');

            $local_debit = 0;
            $local_credit = 0;

            $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_status, @$supplier['balance_local']);

            if (count($purchase_invoices) > 0) {
                $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="12">BEGINING BALANCE - '.$supplier['name'].'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';

                $invoice .= '    <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="12">BEGINING BALANCE - '.$supplier['name'].'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';
            }

            $accumulated = $begin_balance_local;
            foreach ($purchase_invoices as $purchase_invoice) {
                $document_no = $purchase_invoice['document_no'];
                $payments = $this->db->query("SELECT a.*, b.number as voucher_no, b.trans_date as voucher_date, a.account_number, 
                    (CASE WHEN a.account_type = 'DEBIT' THEN (a.payment * a.rate) ELSE 0 END) AS local_debit,
                    (CASE WHEN a.account_type = 'CREDIT' THEN (a.payment * a.rate) ELSE 0 END) AS local_credit
                    FROM ap_payments a
                    LEFT JOIN journal_postings b ON a.payment_no = b.document_no
                    JOIN purchase_invoices c ON a.purchase_invoice = c.number
                    WHERE a.purchase_invoice = '$document_no'
                    GROUP BY a.payment_no")->result_array();

                if ($purchase_invoice['status'] == '0') {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#C8FFCC;";
                } else if ($purchase_invoice['status'] == '1') {
                    $status_purchase = "CLOSE";
                    $style_status_purchase = "background-color:#FFC8C8;";
                } else {
                    $status_purchase = "-";
                    $style_status_purchase = "";
                }
                
                $balance_local = ($purchase_invoice['local_credit'] - $purchase_invoice['local_debit']);
                $accumulated += $balance_local;
                $detail .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $purchase_invoice['source'] . '</td>
                                    <td>' . $purchase_invoice['trans_date'] . '</td>
                                    <td>' . $purchase_invoice['due_date'] . '</td>
                                    <td>' . $purchase_invoice['document_no'] . '</td>
                                    <td>' . $purchase_invoice['invoice_no'] . '</td>
                                    <td>' . $purchase_invoice['description'] . '</td>
                                    <td>' . $purchase_invoice['voucher_date'] . '</td>
                                    <td>' . $purchase_invoice['voucher_no'] . '</td>
                                    <td>' . $purchase_invoice['account_number'] . '</td>
                                    <td>INR</td>
                                    <td style="text-align:right;">' . number_format($purchase_invoice['local_debit'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($purchase_invoice['local_credit'], 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
                
                if ($filter_display == "Invoice") {
                    $payment_total = 0;
                    foreach ($payments as $payment) {
                        $balance_local += ($payment['local_credit'] - $payment['local_debit']);
                        $accumulated += ($payment['local_credit'] - $payment['local_debit']);
                        $payment_total += $payment['local_debit'];
                        $local_debit += $payment['local_debit'];
                        $local_credit += $payment['local_credit'];
                    }

                    if($purchase_invoice['local_debit'] > 0){
                        $payment_total = $purchase_invoice['local_debit'];
                    }

                    $balance_invoice = ($payment_total - $purchase_invoice['local_credit']);
                    $invoice .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $purchase_invoice['source'] . '</td>
                                    <td>' . $purchase_invoice['trans_date'] . '</td>
                                    <td>' . $purchase_invoice['due_date'] . '</td>
                                    <td>' . $purchase_invoice['document_no'] . '</td>
                                    <td>' . $purchase_invoice['invoice_no'] . '</td>
                                    <td>' . $purchase_invoice['description'] . '</td>
                                    <td>' . $purchase_invoice['voucher_date'] . '</td>
                                    <td>' . $purchase_invoice['voucher_no'] . '</td>
                                    <td>' . $purchase_invoice['account_number'] . '</td>
                                    <td>INR</td>
                                    <td style="text-align:right;">' . number_format($purchase_invoice['local_credit'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($payment_total, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_invoice) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
                }

                $no++;
                $local_debit += $purchase_invoice['local_debit'];
                $local_credit += $purchase_invoice['local_credit'];
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);

            if (count($purchase_invoices) > 0) {
                $detail .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="11">SUB TOTAL</td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>
                                <tr>
                                    <td colspan="17" style="height:20px;"></td>
                                </tr>';

                $invoice .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="11">SUB TOTAL</td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>
                                <tr>
                                    <td colspan="17" style="height:20px;"></td>
                                </tr>';
            }

            $summary .= '   <tr>
                                <td>' . $noid . '</td>
                                <td>' . $supplier['id'] . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td>' . $supplier['currency'] . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                            </tr>';
            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
        }

        $detail .= '    <tr style="background: #C3FFB4; font-weight:bold;">
                            <td colspan="11">GRAND TOTAL</td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' .$this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">-</td>
                        </tr>';

        $invoice .= '   <tr style="background: #C3FFB4; font-weight:bold;">
                            <td colspan="11">GRAND TOTAL</td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">-</td>
                        </tr>';

        $summary .= '   <tr style="font-weight:bold;">
                            <td colspan="4">GRAND TOTAL</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                        </tr>';

        $htmlend = '</table></body></html>';

        if ($filter_display == "Summary") {
            echo $html . $summary . $htmlend;
        }elseif ($filter_display == "Invoice") {
            echo $html . $invoice . $htmlend;
        } else {
            echo $html . $detail . $htmlend;
        }
    }
}
