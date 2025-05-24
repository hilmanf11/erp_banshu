<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Ap_schedules extends CI_Controller
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
            $this->load->view('finance/ap_schedules');
        } else {
            redirect('error_access');
        }
    }

    function readExchangeRate($currency, $payment_date)
    {
        $search_date = date("d", strtotime($payment_date));
        if($search_date == "31"){
          $payment_date = date("Y-m-d", strtotime('-10 days', strtotime($payment_date)));
        }
        
        $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "INR"]);

        if ($exchange) {
            $amount = $exchange->middle;
        } else {
            $amount = 1;
        }

        return $amount;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_aging_schedules_$format.xls");
        }
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_supplier = $this->input->get("filter_supplier");
        $period = date("Ym", strtotime($filter_to));

        $start  = new DateTime($filter_from);
        $end = new DateTime($filter_to);
        $diff  = $start->diff($end);

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
                                <small>REPORT ACCOUNT PAYABLE AGING</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
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
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Supplier Name</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Current</th>
                <th colspan="4">Amount Due</th>
                <th rowspan="2">Grand Total</th>
            </tr>
            <tr>
                <th>1-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>Over 90 Days</th>
            </tr>';

        $no = 1;
        $grand_total_begin = 0;
        $grand_total_current = 0;
        $grand_total_week_1 = 0;
        $grand_total_week_2 = 0;
        $grand_total_week_3 = 0;
        $grand_total_week_4 = 0;
        $grand_total_all = 0;
        $header = "";

        foreach ($suppliers as $supplier) {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];

            $query = $this->db->query("SELECT a.*
                FROM (
                    (SELECT datediff('$filter_to', a.due_date) as difference, 'PI' as `status`, a.trans_date, a.due_date, a.invoice_no, a.number as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                    FROM purchase_invoices a
                    JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                        journal_postings WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005') GROUP BY number, document_no, account_number) c ON a.number = c.document_no
                    WHERE a.trans_date between '$filter_from' and '$filter_to' and a.supplier_id = '$supplier_id'
                    ) 
                UNION 
                    (SELECT '' as difference, 'AP' as `status`, a.payment_date as trans_date, '-' as due_date, CONCAT(a.purchase_invoice, ' | ', a.supplier_invoice) as invoice_no, a.payment_no as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                    FROM ap_payments a
                    JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                        journal_postings WHERE account_number IN ('16000001','16000002','16000003','16000004','16000005') GROUP BY number, document_no, account_number) c ON a.payment_no = c.document_no
                    WHERE a.payment_date between '$filter_from' and '$filter_to' and a.supplier_id = '$supplier_id' GROUP BY a.payment_no
                    )
                UNION 
                    (SELECT '' as difference, 'CR' as `status`, a.journal_date as trans_date, '-' as due_date, a.invoice_no, a.document_no, a.account_number, a.number as voucher_no, a.currency, SUM(a.original_debit) as original_debit, SUM(a.original_credit) as original_credit, SUM(a.local_debit) as local_debit, SUM(a.local_credit) as local_credit, a.rates
                    FROM journal_postings a
                    WHERE a.journal_date between '$filter_from' and '$filter_to' and (a.company_name like '%$supplier_name%') and a.modul in ('CURRENCY REVALUATION','ADJUSTMENT') and a.account_number IN ('16000001','16000002','16000003','16000004','16000005')
                    )
                ) a GROUP BY a.voucher_no, a.document_no ORDER BY a.trans_date ASC");
            $purchase_invoices = $query->result_array();

            $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_pi, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_pi');
            $this->db->from('(SELECT DISTINCT supplier_id, number, trans_date FROM purchase_invoices) a');
            $this->db->join("journal_postings b", "a.number = b.document_no and b.account_number IN ('16000001','16000002','16000003','16000004','16000005')");
            $this->db->where('a.supplier_id', $supplier_id);
            $this->db->where('a.trans_date <', $filter_from);
            $pi_begin = $this->db->get()->row();

            $this->db->select('COALESCE(SUM(a.local_credit) - SUM(a.local_debit), 0) as local_re, COALESCE(SUM(a.original_credit) - SUM(a.original_debit), 0) as original_re');
            $this->db->from('journal_postings a');
            $this->db->where("a.journal_date <", $filter_from);
            $this->db->where_in("a.modul", array('CURRENCY REVALUATION','ADJUSTMENT'));
            $this->db->where_in("a.account_number", array('16000001','16000002','16000003','16000004','16000005'));
            // $this->db->group_start();
            $this->db->like("a.company_name", $supplier_name);
            // $this->db->or_like("a.company_name", $supplier_name);
            // $this->db->group_end();
            $revaluation_begin = $this->db->get()->row();
            
            $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_ap, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_ap');
            $this->db->from('(SELECT DISTINCT supplier_id, payment_no, payment_date FROM ap_payments) a');
            $this->db->join("journal_postings b", "a.payment_no = b.document_no and b.account_number IN ('16000001','16000002','16000003','16000004','16000005')");
            $this->db->where('a.supplier_id', $supplier_id);
            $this->db->where('a.payment_date <', $filter_from);
            $ap_begin = $this->db->get()->row();

            if(@$supplier['balance'] > 0 || $pi_begin->local_pi > 0 || $ap_begin->local_ap > 0 || $revaluation_begin->local_re > 0){
                $begin_balance_local = (@$supplier['balance_local'] + ($pi_begin->local_pi + $revaluation_begin->local_re - $ap_begin->local_ap));
            }else{
                $begin_balance_local = 0;
            }
            
            $current = @$begin_balance_local;
            // $current = 0;
            $week_1 = 0;
            $week_2 = 0;
            $week_3 = 0;
            $week_4 = 0;
            $arr = [];

            $total_pi = 0;
            $total_cr = 0;
            $total_ap = 0;

            foreach ($purchase_invoices as $purchase_invoice) {
                $this->db->select("a.purchase_invoice, a.payment_no as document_no, a.account_number, a.currency, COALESCE(b.local_debit, 0) as local_debit, COALESCE(b.local_credit, 0) as local_credit");
                $this->db->from('ap_payments a');
                $this->db->join('ap_payment_journals b', "a.payment_no = b.payment_no and b.account_number in ('16000001','16000002','16000003','16000004','16000005')");
                $this->db->where_in('a.account_number', ['16000001','16000002','16000003','16000004','16000005']);
                $this->db->where('a.purchase_invoice', $purchase_invoice['document_no']);
                $this->db->where('a.supplier_id', $supplier_id);
                $this->db->where("a.payment_date BETWEEN '$filter_from' and '$filter_to'");
                $ap = $this->db->get()->row();

                if($purchase_invoice['status'] == "PI"){
                    $total_pi = ($purchase_invoice['local_debit'] + $purchase_invoice['local_credit']);
                }else{
                    $total_pi = 0;
                }

                if($purchase_invoice['status'] == "AP"){
                    $total_ap = ($purchase_invoice['local_debit'] + $purchase_invoice['local_credit']);
                }else{
                    $total_ap = 0;
                }

                if($purchase_invoice['status'] == "CR"){
                    $total_cr = ($purchase_invoice['local_credit'] - $purchase_invoice['local_debit']);
                }else{
                    $total_cr = 0;
                }

                $total_pi_ap = (($total_pi + $total_cr) - $total_ap);

                if(empty(@$ap->document_no)){
                    if(@$purchase_invoice['difference'] <= 0){
                        $current += $total_pi_ap;
                        $week_1 += 0;
                        $week_2 += 0;
                        $week_3 += 0;
                        $week_4 += 0;
                    } elseif (@$purchase_invoice['difference'] >= 1 && @$purchase_invoice['difference'] <= 30) {
                        $current += 0;
                        $week_1 += $total_pi_ap;
                        $week_2 += 0;
                        $week_3 += 0;
                        $week_4 += 0;
                    } elseif (@$purchase_invoice['difference'] >= 31 && @$purchase_invoice['difference'] <= 60) {
                        $current += 0;
                        $week_1 += 0;
                        $week_2 += $total_pi_ap;
                        $week_3 += 0;
                        $week_4 += 0;
                    } elseif (@$purchase_invoice['difference'] >= 61 && @$purchase_invoice['difference'] <= 90) {
                        $current += 0;
                        $week_1 += 0;
                        $week_2 += 0;
                        $week_3 += $total_pi_ap;
                        $week_4 += 0;
                    } else {
                        $current += 0;
                        $week_1 += 0;
                        $week_2 += 0;
                        $week_3 += 0;
                        $week_4 += $total_pi_ap;
                    }
                }else{
                    $current += $total_pi_ap;
                    $week_1 += 0;
                    $week_2 += 0;
                    $week_3 += 0;
                    $week_4 += 0;
                }

                $arr[] = array(
                    "document_no" => @$purchase_invoice['document_no'],
                    "trans_date" => @$purchase_invoice['trans_date'],
                    "due_date" => @$purchase_invoice['due_date'],
                    "difference" => @$purchase_invoice['difference'],
                    "total" => @$total_pi_ap,
                );
            }

            // die($total_ap);

            if($current >= 0){
                $t_current = number_format(abs($current), 2);
            }else{
                $t_current = "<span style='color:red;'>(".number_format(abs($current), 2).")</span>";
            }

            if($week_1 >= 0){
                $t_week_1 = number_format(abs($week_1), 2);
            }else{
                $t_week_1 = "<span style='color:red;'>(".number_format(abs($week_1), 2).")</span>";
            }

            if($week_2 >= 0){
                $t_week_2 = number_format(abs($week_2), 2);
            }else{
                $t_week_2 = "<span style='color:red;'>(".number_format(abs($week_2), 2).")</span>";
            }

            if($week_3 >= 0){
                $t_week_3 = number_format(abs($week_3), 2);
            }else{
                $t_week_3 = "<span style='color:red;'>(".number_format(abs($week_3), 2).")</span>";
            }

            if($week_4 >= 0){
                $t_week_4 = number_format(abs($week_4), 2);
            }else{
                $t_week_4 = "<span style='color:red;'>(".number_format(abs($week_4), 2).")</span>";
            }

            if(abs($current + $week_1 + $week_2 + $week_3 + $week_4) > 0){
                $header .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td style="text-align:center;">INR</td>
                                <td style="text-align:right;">' . $t_current . '</td>
                                <td style="text-align:right;">' . $t_week_1 . '</td>
                                <td style="text-align:right;">' . $t_week_2 . '</td>
                                <td style="text-align:right;">' . $t_week_3 . '</td>
                                <td style="text-align:right;">' . $t_week_4 . '</td>
                                <td style="text-align:right;">' . number_format($current + $week_1 + $week_2 + $week_3 + $week_4, 2) . '</td>
                            </tr>';
                $no++;
            }

            $grand_total_current += $current;
            $grand_total_week_1 += $week_1;
            $grand_total_week_2 += $week_2;
            $grand_total_week_3 += $week_3;
            $grand_total_week_4 += $week_4;
            $grand_total_all += ($current + $week_1 + $week_2 + $week_3 + $week_4);
        }

        $header .= '  <tr>
                        <td style="text-align:right;" colspan="3"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;">' . number_format($grand_total_current, 2) . '</td>
                        <td style="text-align:right;">' . number_format($grand_total_week_1, 2) . '</td>
                        <td style="text-align:right;">' . number_format($grand_total_week_2, 2) . '</td>
                        <td style="text-align:right;">' . number_format($grand_total_week_3, 2) . '</td>
                        <td style="text-align:right;">' . number_format($grand_total_week_4, 2) . '</td>
                        <td style="text-align:right;">' . number_format($grand_total_all, 2) . '</td>
                    </tr>';

        $htmlend = '</table></body></html>';
        echo $html . $header . $htmlend;
    }
}
