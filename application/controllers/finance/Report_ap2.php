<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap2 extends CI_Controller
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
        $query_1 = $this->db->query("SELECT *, 
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_ex_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_ex_credit 
            FROM journal_postings
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008')
            AND journal_date < '$filter_to' and company_id = '$supplier_id'
            GROUP BY number, document_no, account_number
            ORDER BY journal_date asc");
        $purchase_invoices = $query_1->result_array();

        $local_debit = 0;
        $local_credit = 0;
        $datass = [];
        foreach ($purchase_invoices as $purchase_invoice) {
            if($currency != "INR"){
                $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_ex_debit']);
                $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_ex_credit']);
            }else{
                $ex_local_debit = $purchase_invoice['local_ex_debit'];
                $ex_local_credit = $purchase_invoice['local_ex_credit'];
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
        $this->db->join('journal_postings d', 'a.id = d.company_id');
        $this->db->where('a.status', 0);
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

            //Purchase invoicing
            $query_1 = $this->db->query("SELECT *, 
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_debit) ELSE SUM(original_debit) END) as local_ex_debit,
                (CASE WHEN '$currency' = 'INR' THEN SUM(local_credit) ELSE SUM(original_credit) END) as local_ex_credit 
            FROM journal_postings
            WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005','16000006','16000007','16000008')
            AND `number` like '%$filter_posting_no%' AND document_no like '%$filter_document_no%' AND invoice_no like '%$filter_invoice_no%'
            AND journal_date BETWEEN '$filter_from' and '$filter_to' and company_id = '$supplier_id'
            GROUP BY number, document_no, account_number
            ORDER BY journal_date asc");
            $purchase_invoices = $query_1->result_array();

            $local_debit = 0;
            $local_credit = 0;

            if($supplier['currency'] == $filter_currency || $filter_currency == ""){
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, @$supplier['balance_local']);
            }else{
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_from, $filter_to, $filter_currency, $filter_status, 0);
            }

            foreach ($purchase_invoices as $purchase_invoice) {
                if($currency != "INR"){
                    $ex_local_debit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_debit']);
                    $ex_local_credit = $this->exchangeRates($purchase_invoice['trans_date'], $purchase_invoice['currency'], $currency, $purchase_invoice['local_credit']);
                }else{
                    $ex_local_debit = $purchase_invoice['local_debit'];
                    $ex_local_credit = $purchase_invoice['local_credit'];
                }

                $local_debit += $ex_local_debit;
                $local_credit += $ex_local_credit;
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);
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
        echo $html . $summary . $htmlend;
    }
}
