<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap extends CI_Controller
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
            $this->load->view('finance/report_ap');
        } else {
            redirect('error_access');
        }
    }

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('suppliers', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPostingNo(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT number, journal_date FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and number like '%$post%' ORDER BY number DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readDocumentNo(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT document_no FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and document_no like '%$post%' ORDER BY document_no DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readInvoiceNo(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT invoice_no FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') and invoice_no like '%$post%' ORDER BY invoice_no DESC LIMIT 500");
        echo json_encode($send);
    }

    public function readClno(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT number FROM journal_clearings WHERE number like '%$post%' ORDER BY number DESC LIMIT 500");
        echo json_encode($send);
    }

    public function begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status = "", $balance_local){
        if($filter_currency == "INR" || $filter_currency == ""){
            $filter_currency = "";
            $currency = "INR";
        }else{
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

        if($filter_status == "0"){
            $wpistatus = "and a.status = '1'";
            $wapstatus = "and (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) = '1'";
            $wjlstatus = "and a.status_cl = '1'";
            $wpidate = "and b.journal_date <= '$filter_to'";
            $wjldate = "and a.journal_date <= '$filter_to'";
        }elseif($filter_status == "1"){
            $wpistatus = "and a.status = '0'";
            $wapstatus = "and (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) = '0'";
            $wjlstatus = "and a.status_cl = '0'";
            $wpidate = "and b.journal_date <= '$filter_to'";
            $wjldate = "and a.journal_date <= '$filter_to'";
        }else{
            $wpistatus = "";
            $wapstatus = "";
            $wjlstatus = "";
            $wpidate = "and b.journal_date < '$filter_from'";
            $wjldate = "and a.journal_date < '$filter_from'";
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
            c.number as cl_no,
            c.journal_date as cl_date,
            a.currency,
            b.local_debit,
            b.local_credit,
            a.status
        FROM purchase_invoices a
        JOIN (SELECT journal_date, number, document_no, account_number, description,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
            FROM journal_postings 
            WHERE modul != 'CLEARING' and account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY number, document_no, account_number) b ON a.number = b.document_no
        LEFT JOIN journal_clearings c ON a.number = c.document_no
        WHERE a.supplier_id = '$supplier_id' $wpistatus $wpidate
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
            b.description,
            b.journal_date AS voucher_date,
            b.number AS voucher_no,
            b.account_number,
            d.number as cl_no,
            d.journal_date as cl_date,
            a.currency,
            b.local_debit,
            b.local_credit,
            (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) as status
        FROM ap_payments a
        LEFT JOIN (SELECT journal_date, number, description, document_no, account_number, 
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
            FROM journal_postings 
            WHERE modul != 'CLEARING' and account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
            GROUP BY number, document_no, account_number) b ON a.payment_no = b.document_no and a.account_number = b.account_number
        LEFT JOIN purchase_invoices c ON a.purchase_invoice = c.number
        LEFT JOIN journal_clearings d ON a.payment_no = d.document_no
        WHERE a.supplier_id = '$supplier_id' $wapstatus $wpidate
        GROUP BY a.payment_no, a.account_number");
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
            b.number as cl_no,
            b.journal_date cl_date,
            a.currency,
            (CASE WHEN '$currency' = 'INR' THEN SUM(a.tlocal_debit) ELSE SUM(a.toriginal_debit) END) as local_debit,
            (CASE WHEN '$currency' = 'INR' THEN SUM(a.tlocal_credit) ELSE SUM(a.toriginal_credit) END) as local_credit,
            a.status_cl as status
        FROM (SELECT *, SUM(local_debit) AS tlocal_debit, SUM(local_credit) AS tlocal_credit, SUM(original_debit) AS toriginal_debit, SUM(original_credit) AS toriginal_credit FROM journal_postings GROUP BY id) a
        LEFT JOIN (SELECT * FROM journal_clearings GROUP BY document_no, company_id, description) b ON a.document_no = b.document_no and a.company_id = b.company_id and a.description = b.description
        WHERE a.company_id = '$supplier_id' 
        AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
        AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006')
        $wjldate $wjlstatus
        GROUP BY a.number
        ORDER BY a.journal_date ASC");
        $data_3 = $query_3->result_array();

        $query_4 = $this->db->query("SELECT
            'CL' AS source,
            a.trans_date,
            '-' AS due_date,
            a.document_no,
            a.invoice_no,
            a.description,
            a.journal_date AS voucher_date,
            a.number AS voucher_no,
            a.account_number,
            (CASE WHEN b.number is null and a.status_cl = 1 THEN a.number ELSE b.number END) as cl_no,
            (CASE WHEN b.journal_date is null and a.status_cl = 1 THEN a.journal_date ELSE b.journal_date END) as cl_date,
            a.currency,
            (CASE WHEN '$currency' = 'INR' THEN SUM(a.local_debit) ELSE SUM(a.original_debit) END) as local_debit,
            (CASE WHEN '$currency' = 'INR' THEN SUM(a.local_credit) ELSE SUM(a.original_credit) END) as local_credit,
            a.status_cl as status
        FROM journal_postings a
        LEFT JOIN journal_clearings b ON a.id = b.document_no and a.number = b.invoice_no and a.company_id = b.company_id
        WHERE a.company_id = '$supplier_id' 
        AND a.modul IN ('CLEARING')
        $wjldate $wjlstatus
        GROUP BY a.id
        ORDER BY a.journal_date ASC");
        $data_4 = $query_4->result_array();

        $purchase_invoices = array_merge($data_1, $data_2, $data_3, $data_4);

        $local_debit = 0;
        $local_credit = 0;
        $datass = [];
        foreach ($purchase_invoices as $purchase_invoice) {
            if($currency != "INR"){
                $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_debit']);
                $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_credit']);
            }else{
                $ex_local_debit = $purchase_invoice['local_debit'];
                $ex_local_credit = $purchase_invoice['local_credit'];
            }

            // if($purchase_invoice['cl_no'] == ""){
                $local_debit += $ex_local_debit;
                $local_credit += $ex_local_credit;
            // }
        }

        // die($balance_local ." + ". $local_credit . " - " . $local_debit);
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

    public function exchangeRates($trans_date, $currency_from, $currency_to, $amount = 0){
        $query_exchange = $this->db->query("SELECT * FROM exchange_rates WHERE start_date <= '$trans_date' and end_date >= '$trans_date' and currency_from = '$currency_to' and currency_to = '$currency_from'");
        $row_exchange = $query_exchange->row();

        if($currency_to == "INR"){
            return $amount;
        }else{
            if($row_exchange){
                $total = ($amount / $row_exchange->middle);
            }else{
                $total = $amount;
            }
    
            return $total;
        }
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
        if($filter_from == "" && $filter_to == ""){
            $filter_from = "2024-11-01";
            $filter_to = date("Y-m-d");
        }

        $filter_supplier = base64_decode($this->input->get("filter_supplier"));
        $filter_posting_no = base64_decode($this->input->get("filter_posting_no"));
        $filter_document_no = base64_decode($this->input->get("filter_document_no"));
        $filter_invoice_no = base64_decode($this->input->get("filter_invoice_no"));
        $filter_cl_no = base64_decode($this->input->get("filter_cl_no"));
        $filter_currency = $this->input->get("filter_currency");
        $filter_display = $this->input->get("filter_display");
        $filter_status = $this->input->get("filter_status");
        $period = date("Ym", strtotime($filter_to));

        if($filter_currency == "INR"){
            $filter_currency = "";
            $currency = "INR";
        }else{
            $filter_currency = $filter_currency;
            $currency = $filter_currency;
        }

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

        $this->db->select("a.*, b.account_number, c.account_name, (CASE WHEN '$currency' = 'INR' THEN b.balance_local ELSE b.balance END) as balance_local");
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        // $this->db->join('journal_postings d', 'a.id = d.company_id', 'left');
        // $this->db->where('a.status', 0);
        $this->db->like("a.id", $filter_supplier);
        // $this->db->where_in("a.id", ["SUP-01-01","SUP-01-02","SUP-01-03","SUP-01-04"]);
        // if($filter_posting_no != ""){
        //     $this->db->like("d.number", $filter_posting_no);
        // }
        // if($filter_document_no != ""){
        //     $this->db->like("d.document_no", $filter_document_no);
        // }
        // if($filter_invoice_no != ""){
        //     $this->db->like("d.invoice_no", $filter_invoice_no);
        // }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="font-size: 12px; text-align: left; margin-bottom: 20px;">
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
            </center>
            <center style="margin-bottom: 20px;">
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>(' . $filter_display . ')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>';

        $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Account No</th>
                                <th>Account Name</th>
                                <th>Currency</th>
                                <th>Amount</th>
                            </tr>';

        $invoice = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Supplier ID</th>
                            <th rowspan="2">Supplier Name</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Clearing No</th>
                            <th rowspan="2">Clearing Date</th>
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
                            <th rowspan="2">Supplier ID</th>
                            <th rowspan="2">Supplier Name</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Clearing No</th>
                            <th rowspan="2">Clearing Date</th>
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
            $supplier_name = $supplier['name'];

            if($filter_display != "Invoice"){
                $where_inv = "";
            }else{
                $where_inv = "AND a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices)";
            }

            if($filter_cl_no != ""){
                $where_cl_no = "and c.number like '%$filter_cl_no%'";
                $where_cl_no2 = "and d.number like '%$filter_cl_no%'";
                $where_cl_no3 = "and b.number like '%$filter_cl_no%'";
            }else{
                $where_cl_no = "";
                $where_cl_no2 = "";
                $where_cl_no3 = "";
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
                c.number as cl_no,
                c.journal_date as cl_date,
                a.currency,
                b.local_debit,
                b.local_credit,
                a.status
            FROM purchase_invoices a
            JOIN (SELECT journal_date, number, document_no, account_number, description,
                    (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                    (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
                FROM journal_postings 
                WHERE modul != 'CLEARING' and number like '%$filter_posting_no%' 
                and document_no like '%$filter_document_no%' and invoice_no like '%$filter_invoice_no%'
                and account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                GROUP BY number, document_no, account_number) b ON a.number = b.document_no
            LEFT JOIN journal_clearings c ON a.number = c.document_no
            WHERE a.supplier_id = '$supplier_id' and b.journal_date BETWEEN '$filter_from' and '$filter_to' and a.status like '%$filter_status%' $where_cl_no
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
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                (CASE WHEN c.status = 1 THEN a.payment_no ELSE d.number END) as cl_no,
                (CASE WHEN c.status = 1 THEN a.payment_date ELSE d.journal_date END) as cl_date,
                a.currency,
                b.local_debit,
                b.local_credit,
                (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) as status
            FROM ap_payments a
            LEFT JOIN (SELECT journal_date, number, description, document_no, account_number, 
                    (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_debit,
                    (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_credit
                FROM journal_postings 
                WHERE modul != 'CLEARING' and account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008') 
                AND `number` like '%$filter_posting_no%' and document_no like '%$filter_document_no%' and invoice_no like '%$filter_invoice_no%'
                GROUP BY number, document_no, account_number) b ON a.payment_no = b.document_no and a.account_number = b.account_number
            LEFT JOIN purchase_invoices c ON a.purchase_invoice = c.number
            LEFT JOIN journal_clearings d ON a.payment_no = d.document_no
            WHERE a.supplier_id = '$supplier_id' $where_inv $where_cl_no2 and b.journal_date between '$filter_from' and '$filter_to' and (CASE WHEN c.status IS NULL THEN a.status_cl ELSE c.status END) LIKE '%$filter_status%'
            GROUP BY a.payment_no, a.account_number");
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
                b.number as cl_no,
                b.journal_date cl_date,
                a.currency,
                (CASE WHEN '$currency' = 'INR' THEN SUM(a.tlocal_debit) ELSE SUM(a.toriginal_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(a.tlocal_credit) ELSE SUM(a.toriginal_credit) END) as local_credit,
                a.status_cl as status
            FROM (SELECT *, SUM(local_debit) AS tlocal_debit, SUM(local_credit) AS tlocal_credit, SUM(original_debit) AS toriginal_debit, SUM(original_credit) AS toriginal_credit FROM journal_postings GROUP BY id) a
            LEFT JOIN (SELECT * FROM journal_clearings GROUP BY document_no, company_id, description) b ON a.document_no = b.document_no and a.company_id = b.company_id and a.description = b.description
            WHERE a.company_id = '$supplier_id' 
            AND a.modul IN ('CURRENCY REVALUATION','ADJUSTMENT') 
            AND a.account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008','28000003','28000006')
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to' and a.status_cl LIKE '%$filter_status%' $where_cl_no3
            AND a.number like '%$filter_posting_no%' and a.document_no like '%$filter_document_no%' and a.invoice_no like '%$filter_invoice_no%'
            GROUP BY a.number
            ORDER BY a.journal_date ASC");
            $data_3 = $query_3->result_array();

            $query_4 = $this->db->query("SELECT
                'CL' AS source,
                a.trans_date,
                '-' AS due_date,
                a.document_no,
                a.invoice_no,
                a.description,
                a.journal_date AS voucher_date,
                a.number AS voucher_no,
                a.account_number,
                (CASE WHEN b.number is null and a.status_cl = 1 THEN a.number ELSE b.number END) as cl_no,
                (CASE WHEN b.journal_date is null and a.status_cl = 1 THEN a.journal_date ELSE b.journal_date END) as cl_date,
                a.currency,
                (CASE WHEN '$currency' = 'INR' THEN SUM(a.local_debit) ELSE SUM(a.original_debit) END) as local_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(a.local_credit) ELSE SUM(a.original_credit) END) as local_credit,
                a.status_cl as status
            FROM journal_postings a
            LEFT JOIN journal_clearings b ON a.id = b.document_no and a.number = b.invoice_no and a.company_id = b.company_id
            WHERE a.company_id = '$supplier_id' 
            AND a.modul IN ('CLEARING')
            $where_cl_no3
            AND a.journal_date BETWEEN '$filter_from' and '$filter_to'
            AND a.number like '%$filter_posting_no%' and a.document_no like '%$filter_document_no%'
            GROUP BY a.id
            ORDER BY a.journal_date ASC");
            $data_4 = $query_4->result_array();

            $purchase_invoices = array_merge($data_1, $data_2, $data_3, $data_4);

            usort($purchase_invoices, 'compare_trans_date');

            $local_debit = 0;
            $local_credit = 0;

            if($supplier['currency'] == $filter_currency || $filter_currency == ""){
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, @$supplier['balance_local']);
            }else{
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, 0);
            }

            if (count($purchase_invoices) > 0) {
                $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="15">BEGINING BALANCE - '.$supplier['name'].'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';

                $invoice .= '    <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="15">BEGINING BALANCE - '.$supplier['name'].'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">' . number_format(@$begin_balance_local, 2) . '</td>
                                </tr>';
            }

            $accumulated = $begin_balance_local;
            foreach ($purchase_invoices as $purchase_invoice) {
                $document_no = $purchase_invoice['document_no'];
                if ($filter_display == "Invoice") {
                    $payments = $this->db->query("SELECT a.*, b.number as voucher_no, b.trans_date as voucher_date, a.account_number,
                        (CASE WHEN a.account_type = 'DEBIT' THEN (CASE WHEN '$currency' = 'INR' THEN (a.payment * a.rate) ELSE a.payment END) ELSE 0 END) AS local_debit,
                        (CASE WHEN a.account_type = 'CREDIT' THEN (CASE WHEN '$currency' = 'INR' THEN (a.payment * a.rate) ELSE a.payment END) ELSE 0 END) AS local_credit
                        FROM ap_payments a
                        LEFT JOIN journal_postings b ON a.payment_no = b.document_no and b.modul != 'CLEARING'
                        JOIN purchase_invoices c ON a.purchase_invoice = c.number
                        WHERE a.purchase_invoice = '$document_no'
                        GROUP BY a.payment_no")->result_array();
                }

                $cl_no = $purchase_invoice['cl_no'];
                $cl_date = $purchase_invoice['cl_date'];
                $total_payment = 0;
                if ($filter_display == "Detail") {
                    if($purchase_invoice['source'] == "PI"){
                        $payments = $this->db->query("SELECT payment_no, payment_date, payment, account_type FROM ap_payments WHERE purchase_invoice = '$document_no'")->result_array();

                        $cl_no_array = [];
                        $cl_date_array = [];
                        foreach ($payments as $payment) {
                            $cl_no_array[] = $payment['payment_no'];
                            $cl_date_array[] = $payment['payment_date'];
                            if($payment['account_type'] == "DEBIT"){
                                $total_payment += $payment['payment'];
                            }else{
                                $total_payment -= $payment['payment'];
                            }
                        }
                        
                        $cl_no = implode(", ", $cl_no_array);
                        $cl_date = implode(", ", $cl_date_array);
                    }
                }

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

                if($currency != "INR"){
                    $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_debit']);
                    $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_credit']);
                }else{
                    $ex_local_debit = $purchase_invoice['local_debit'];
                    $ex_local_credit = $purchase_invoice['local_credit'];
                }

                if($cl_no == ""){
                    $balance_local = ($ex_local_credit - $ex_local_debit);
                }elseif($total_payment != 0){
                    $balance_local = ($ex_local_credit - $ex_local_debit - $total_payment);
                }else{
                    $balance_local = 0;
                }
                
                $accumulated += ($ex_local_credit - $ex_local_debit);

                $detail .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                <td>' . $supplier_id . '</td>
                                <td>' . $supplier_name . '</td>
                                <td>' . $purchase_invoice['source'] . '</td>
                                <td>' . $purchase_invoice['trans_date'] . '</td>
                                <td>' . $purchase_invoice['due_date'] . '</td>
                                <td>' . $purchase_invoice['document_no'] . '</td>
                                <td>' . $purchase_invoice['invoice_no'] . '</td>
                                <td>' . $purchase_invoice['voucher_date'] . '</td>
                                <td>' . $purchase_invoice['voucher_no'] . '</td>
                                <td>' . $cl_no . '</td>
                                <td>' . $cl_date . '</td>
                                <td>' . $purchase_invoice['account_number'] . '</td>
                                <td>' . $currency . '</td>
                                <td style="text-align:right;">' . number_format($ex_local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($ex_local_credit, 2) . '</td>
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

                    if($purchase_invoice['source'] == "PI"){
                        $payments = $this->db->query("SELECT payment_no, payment_date, payment, account_type FROM ap_payments WHERE purchase_invoice = '$document_no'")->result_array();

                        $cl_no_array = [];
                        $cl_date_array = [];
                        foreach ($payments as $payment) {
                            $cl_no_array[] = $payment['payment_no'];
                            $cl_date_array[] = $payment['payment_date'];
                            if($payment['account_type'] == "DEBIT"){
                                $total_payment += $payment['payment'];
                            }else{
                                $total_payment -= $payment['payment'];
                            }
                        }
                        
                        $cl_no = implode(", ", $cl_no_array);
                        $cl_date = implode(", ", $cl_date_array);
                    }

                    if($payment_total == 0){
                        if($currency != "INR"){
                            $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_debit']);
                        }else{
                            $ex_local_debit = $purchase_invoice['local_debit'];
                        }

                        $payment_total = $ex_local_debit;
                    }

                    if($purchase_invoice['cl_no'] == ""){
                        $balance_invoice = ($payment_total - $purchase_invoice['local_credit']);
                    }else{
                        $balance_invoice = 0;
                    }

                    $invoice .= '  <tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $supplier_id . '</td>
                                    <td>' . $supplier_name . '</td>
                                    <td>' . $purchase_invoice['source'] . '</td>
                                    <td>' . $purchase_invoice['trans_date'] . '</td>
                                    <td>' . $purchase_invoice['due_date'] . '</td>
                                    <td>' . $purchase_invoice['document_no'] . '</td>
                                    <td>' . $purchase_invoice['invoice_no'] . '</td>
                                    <td>' . $purchase_invoice['voucher_date'] . '</td>
                                    <td>' . $purchase_invoice['voucher_no'] . '</td>
                                    <td>' . $cl_no . '</td>
                                    <td>' . $cl_date . '</td>
                                    <td>' . $purchase_invoice['account_number'] . '</td>
                                    <td>' . $currency . '</td>
                                    <td style="text-align:right;">' . number_format($purchase_invoice['local_credit'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($payment_total, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_invoice) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
                }

                $no++;
                $local_debit += $ex_local_debit;
                $local_credit += $ex_local_credit;
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);

            if (count($purchase_invoices) > 0) {
                $detail .= '    <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>SUB TOTAL</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>
                                <tr>
                                    <td style="height:20px;"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>';

                $invoice .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>SUB TOTAL</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:right;">' . number_format($local_debit, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($local_credit, 2) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated) . '</td>
                                </tr>';
            }

            $summary .= '   <tr>
                                <td>' . $noid . '</td>
                                <td>' . $supplier['id'] . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td>' . $supplier['account_number'] . '</td>
                                <td>' . $supplier['account_name'] . '</td>
                                <td>' . $currency . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local) . '</td>
                            </tr>';
            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
        }

        $detail .= '    <tr style="background: #C3FFB4; font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' .$this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">-</td>
                        </tr>';

        $invoice .= '   <tr style="background: #C3FFB4; font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">' . number_format($grand_local_credit, 2) . '</td>
                            <td style="text-align:right;">' . number_format($grand_local_debit, 2) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_local_balance) . '</td>
                            <td style="text-align:right;">-</td>
                        </tr>';

        $summary .= '   <tr style="font-weight:bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>GRAND TOTAL</td>
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
