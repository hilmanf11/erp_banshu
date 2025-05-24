<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_trial_balances_2 extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_trial_balances_2');
        } else {
            redirect('error_access');
        }
    }

    public function getData(){
        $begin_date = $this->input->post('filter_from');
        
        $filter_from = date("Y-m-01", strtotime($begin_date));
        $filter_to   = date("Y-m-t", strtotime($begin_date));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));
        $period_before = date("Ym", strtotime("-1 month", strtotime($filter_from)));

        $this->db->select('a.*');
        $this->db->from('account_coa a');
        $this->db->where("a.cutoff <= '$begin_date'");
        $this->db->order_by('a.account_number', 'asc');
        $accounts = $this->db->get()->result_array();

        if(count($accounts) == 0){
            show_error("Error");
            die();
        }
        
        $this->db->select('*');
        $this->db->from('account_group_details');
        // $this->db->where("number", "19000000");
        $this->db->order_by('number', 'asc');
        $account_groups = $this->db->get()->result_array();

        foreach ($account_groups as $account_group) {
            $this->db->select('a.*');
            $this->db->from('account_coa a');
            $this->db->where('a.account_group_detail_id', $account_group['id']);
            $this->db->order_by('a.account_number', 'asc');
            $accounts = $this->db->get()->result_array();

            $local_debit = 0;
            $local_credit = 0;
            $begin_debit = 0;
            $begin_credit = 0;
            $ending_debit = 0;
            $ending_credit = 0;
            $begin_balance = 0;
            $text = "";
            foreach ($accounts as $account) {
                $account_number = $account['account_number'];
                $this->db->select('account_number, account_name, 
                    COALESCE(SUM(local_debit), 0) as local_debit,
                    COALESCE(SUM(local_credit), 0) as local_credit');
                $this->db->from('journal_postings');
                $this->db->where('account_number', $account_number);
                $this->db->where("journal_date BETWEEN '$filter_from' and '$filter_to'");
                $this->db->group_by('account_number');
                $journal = $this->db->get()->row();

                $trial_balances = $this->crud->read('trial_balances', [], ["account_number" => $account_number, "period" => $period_before]);

                if(!empty($trial_balances->id)){
                    if($trial_balances->header == 1){
                        $begin_debit += $trial_balances->ending_debit;
                        $begin_credit += $trial_balances->ending_credit;
                    }
                }else{
                    $begin_debit += $account['local_debit'];
                    $begin_credit += $account['local_kredit'];
                }

                // if((@$journal->local_debit - @$journal->local_credit) > 0){
                //     $journal_debit = (@$journal->local_debit - @$journal->local_credit);
                //     $journal_credit = 0;
                // }else{
                //     $journal_debit = 0;
                //     $journal_credit = abs(@$journal->local_debit - @$journal->local_credit);
                // }

                $journal_debit = @$journal->local_debit;
                $journal_credit = @$journal->local_credit;
                
                if($account_group['number'] != $account['account_number']){
                    $local_debit += $journal_debit;
                    $local_credit += $journal_credit;

                    if(!empty($trial_balances->id)){
                        $journal_end_debit = (@$trial_balances->ending_debit + $journal_debit);
                        $journal_end_credit = (@$trial_balances->ending_credit + $journal_credit);

                        if(($journal_end_debit - $journal_end_credit) > 0){
                            $ending_debit += ($journal_end_debit - $journal_end_credit);
                            $journal_end_debit_f = ($journal_end_debit - $journal_end_credit);
                            $journal_end_credit_f = 0;
                        }else{
                            $ending_credit += abs($journal_end_debit - $journal_end_credit);
                            $journal_end_debit_f = 0;
                            $journal_end_credit_f = abs($journal_end_debit - $journal_end_credit);
                        }

                        $data[] = array(
                            "period" => $period,
                            "account_number" => $account['account_number'],
                            "account_name" => $account['account_name'],
                            "begin_debit" => @$trial_balances->ending_debit,
                            "begin_credit" => @$trial_balances->ending_credit,
                            "local_debit" => $journal_debit,
                            "local_credit" => $journal_credit,
                            "ending_debit" => $journal_end_debit_f,
                            "ending_credit" => $journal_end_credit_f,
                            "header" => 1,
                        );
                    }else{
                        $journal_end_debit = (@$account['local_debit'] + $journal_debit);
                        $journal_end_credit = (@$account['local_kredit'] + $journal_credit);

                        if(($journal_end_debit - $journal_end_credit) > 0){
                            $ending_debit += ($journal_end_debit - $journal_end_credit);
                            $journal_end_debit_f = ($journal_end_debit - $journal_end_credit);
                            $journal_end_credit_f = 0;
                        }else{
                            $ending_credit += abs($journal_end_debit - $journal_end_credit);
                            $journal_end_debit_f = 0;
                            $journal_end_credit_f = abs($journal_end_debit - $journal_end_credit);
                        }

                        $data[] = array(
                            "period" => $period,
                            "account_number" => $account['account_number'],
                            "account_name" => $account['account_name'],
                            "begin_debit" => $account['local_debit'],
                            "begin_credit" => $account['local_kredit'],
                            "local_debit" => @$journal_debit,
                            "local_credit" => @$journal_credit,
                            "ending_debit" => $journal_end_debit_f,
                            "ending_credit" => $journal_end_credit_f,
                            "header" => 1,
                        );
                    }
                }
            }

            $data[] = array(
                "period" => $period,
                "account_number" => $account_group['number'],
                "account_name" => $account_group['name'],
                "begin_debit" => $begin_debit,
                "begin_credit" => $begin_credit,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
                "ending_debit" => $ending_debit,
                "ending_credit" => $ending_credit,
                "header" => 0,
            );
        }

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->post('filter_from');
            $filter_to = $this->input->post('filter_to');

            $period = date("Ym", strtotime($filter_from));
            $period_to = date("Ym", strtotime($filter_to));

            $this->db->select('account_number, account_name, header,
                (begin_debit) as begin_debit, 
                (begin_credit) as begin_credit, 
                (local_debit) as local_debit, 
                (local_credit) as local_credit,
                (ending_debit) as ending_debit,
                (ending_credit) as ending_credit');
            $this->db->from('trial_balances');
            $this->db->where('period >=', $period);
            $this->db->where('period <=', $period_to);
            $this->db->order_by('account_number', 'asc');
            // $this->db->group_by('account_number', 'asc');
            $trial_balances = $this->db->get()->result_array();

            $grand_total_begin_debit = 0;
            $grand_total_begin_credit = 0;
            $grand_total_local_debit = 0;
            $grand_total_local_credit = 0;
            $grand_total_ending_debit = 0;
            $grand_total_ending_credit = 0;
            $grand_total_final_balance = 0;
            $datas = [];
            foreach ($trial_balances as $trial_balance) {
                $final_balance = (($trial_balance['begin_debit'] + $trial_balance['local_debit']) - ($trial_balance['begin_credit'] + $trial_balance['local_credit']));

                $datas[] = array(
                    "header" => $trial_balance['header'],
                    "account_number" => $trial_balance['account_number'],
                    "account_name" => $trial_balance['account_name'],
                    "begin_debit" => $trial_balance['begin_debit'],
                    "begin_credit" => $trial_balance['begin_credit'],
                    "local_debit" => $trial_balance['local_debit'],
                    "local_credit" => $trial_balance['local_credit'],
                    "ending_debit" => $trial_balance['ending_debit'],
                    "ending_credit" => $trial_balance['ending_credit'],
                    "final_balance" => $final_balance,
                );

                if($trial_balance['header'] != 0){
                    $grand_total_begin_debit += $trial_balance['begin_debit'];
                    $grand_total_begin_credit += $trial_balance['begin_credit'];
                    $grand_total_local_debit += $trial_balance['local_debit'];
                    $grand_total_local_credit += $trial_balance['local_credit'];
                    $grand_total_ending_debit += $trial_balance['ending_debit'];
                    $grand_total_ending_credit += $trial_balance['ending_credit'];
                    $grand_total_final_balance += $final_balance;
                }
            }

            $footer[] = array(
                "account_number" => "GRAND TOTAL",
                "begin_debit" => $grand_total_begin_debit,
                "begin_credit" => $grand_total_begin_credit,
                "local_debit" => $grand_total_local_debit,
                "local_credit" => $grand_total_local_credit,
                "ending_debit" => $grand_total_ending_debit,
                "ending_credit" => $grand_total_ending_credit,
                "final_balance" => $grand_total_final_balance,
            );

            //Mapping Data
            $result['total'] = count($datas);
            $result = array_merge($result, ['rows' => $datas, "footer" => $footer]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $trial_balances = $this->crud->reads("trial_balances", [], [
                "period" => $post['period'], 
                "account_number" => $post['account_number']
            ]);

            if(count($trial_balances) > 0){
                $send = $this->db->update('trial_balances', $post, [
                    "period" => $post['period'], 
                    "account_number" => $post['account_number']
                ]);

                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            }else{
                $send = $this->crud->create('trial_balances', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=trial_balances_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));
        $period_to = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('account_number, account_name, header,
            (begin_debit) as begin_debit, 
            (begin_credit) as begin_credit, 
            (local_debit) as local_debit, 
            (local_credit) as local_credit,
            (ending_debit) as ending_debit,
            (ending_credit) as ending_credit');
        $this->db->from('trial_balances');
        $this->db->where('period >=', $period);
        $this->db->where('period <=', $period_to);
        $this->db->order_by('account_number', 'asc');
        // $this->db->group_by('account_number', 'asc');
        $trial_balances = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <h3 style="margin:0;">TRIAL BALANCE</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="3" width="20">No</th>
                <th rowspan="3">Account No</th>
                <th rowspan="3">Account Name</th>
                <th colspan="6">LOCAL CURRENCY</th>
                <th rowspan="3">Final Balance</th>
            </tr>
            <tr>
                <th colspan="2">Begin Balance</th>
                <th colspan="2">Transaction</th>
                <th colspan="2">End Balance</th>
            </tr>
            <tr>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';

        $no = 1;
        $grand_total_begin_debit = 0;
        $grand_total_begin_credit = 0;
        $grand_total_local_debit = 0;
        $grand_total_local_credit = 0;
        $grand_total_ending_debit = 0;
        $grand_total_ending_credit = 0;
        $grand_total_final_balance = 0;
        foreach ($trial_balances as $trial_balance) {
            if($trial_balance['header'] == 0){
                $style = 'style="background:#CAFFB3;"';
                $font = 'font-weight:bold;';
            }else{
                $style = '';
                $font = '';
            }

            $final_balance = (($trial_balance['begin_debit'] + $trial_balance['local_debit']) - ($trial_balance['begin_credit'] + $trial_balance['local_credit']));

            $html .= '  <tr '.$style.'>
                            <td style="'.$font.'">' . $no . '</td>
                            <td style="'.$font.'mso-number-format:\@;">' . $trial_balance['account_number'] . '</td>
                            <td style="'.$font.'">' . $trial_balance['account_name'] . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['begin_debit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['begin_credit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['local_debit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['local_credit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['ending_debit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($trial_balance['ending_credit'], 2) . '</td>
                            <td style="text-align:right;'.$font.'">' . number_format($final_balance, 2) . '</td>
                        </tr>';

            if($trial_balance['header'] != 0){
                $grand_total_begin_debit += $trial_balance['begin_debit'];
                $grand_total_begin_credit += $trial_balance['begin_credit'];
                $grand_total_local_debit += $trial_balance['local_debit'];
                $grand_total_local_credit += $trial_balance['local_credit'];
                $grand_total_ending_debit += $trial_balance['ending_debit'];
                $grand_total_ending_credit += $trial_balance['ending_credit'];
                $grand_total_final_balance += $final_balance;
            }
            $no++;
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td colspan="3"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_begin_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_local_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_debit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_ending_credit, 2) . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_final_balance, 2) . '</b></td>
                    </tr>';


        
        $html .= '</table></body></html>';
        echo $html;
    }
}
