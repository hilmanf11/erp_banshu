<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Forecasts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->load->model('banshu');

        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[2]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['nik'] = $this->crud->read('users', [], ['username' => $this->session->username])->number;

            $this->load->view('template/header', $data);
            $this->load->view('planning/forecasts');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get('month'); 
        $year = $this->input->get('year');

        $this->db->distinct()->select("revision")->from("forecasts")->where(["p_month" => $month, "p_year" => $year]);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readMonths()
    {
        $month = base64_decode($this->input->post('month'));
        $year = base64_decode($this->input->post('year'));

        $start = strtotime($year . "-" . $month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('+12 month', strtotime($year . "-" . $month . "-01"))));

        while ($start < $end) {
            $monthName[] = date('M Y', $start);
            $start = strtotime("+1 month", $start);
        }

        die(json_encode($monthName));
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_item_fg_id = trim(base64_decode($this->input->get('filter_item_fg_id')));

            $customers = [];
            if (empty($filter_customer_id)) {
                $nik = $this->crud->read("users", [], ["username" => $this->session->username])->number;
                $this->db->distinct()->select("customer_id")->from("setting_customer")->where(["user_id" => $nik]);
                $wheres = explode(',', $this->db->get()->row()->customer_id);

                $this->db->distinct()->select("customer_id")
                ->from("forecasts")
                ->where_in("customer_id", $wheres);
                
                foreach ($this->db->get()->result() as $index => $row){ 
                    array_push($customers, $row->customer_id); 
                }
            }

            //Select Query
            $this->db->select("a.*, b.number_customer, b.name as item_fg_name, c.name as customer_name");
            $this->db->from('forecasts a');
            $this->db->join('item_fg b', "a.item_fg_id = b.id");
            $this->db->join('customers c', "a.customer_id = c.id");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if ($filter_customer_id != 'all') {
                if (empty($customers)) { 
                    $this->db->where('a.customer_id', $filter_customer_id); 
                } else { 
                    $this->db->where_in('a.customer_id', $customers); 
                }
            }
            
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->order_by('b.number_customer');
            // die($this->db->get_compiled_select());
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $customer = $this->crud->read('customers', [], ["id" => $post['customer_id']], "", "name", "asc");
                $forecasts = $this->crud->reads('forecasts', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_fg_id" => $post['item_fg_id']
                ]);

                $nik = $this->crud->read('users', [], ['username' => $this->session->username])->number;
                $this->db->distinct()->select("customer_id")
                ->from("setting_customer")
                ->where("user_id", $nik)->order_by("customer_id");
                $datas = $this->db->get()->row_array();

                $nicks = [];
                foreach (explode(",", $datas['customer_id']) as $val => $row) { 
                    array_push($nicks, $val); 
                }
                // die(var_dump($customer->nickname));
                if (count($forecasts) > 0) {
                    show_error("Duplicated Data");
                } elseif (!in_array($customer->id, $nicks)) {
                    show_error("You Not Allowed to Entry This Customer, ".$customer->name);
                } else {
                    $send = $this->crud->create('forecasts', $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
        	$p_month = $this->input->get('p_month');
        	$p_year = $this->input->get('p_year');
        	$revision = $this->input->get('revision');
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            $post = $this->input->post();
            $send = $this->crud->update('forecasts', ["p_month" => $p_month, "p_year" => $p_year, "revision" => $revision, "item_fg_id" => $item_fg_id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('forecasts', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        // $send = $this->crud->delete('forecasts', [
        //     "p_month" => $data->val(2, 3), 
        //     "p_year" => $data->val(2, 4), 
        //     "revision" => $data->val(3, 3)
        // ]);

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'number_customer' => $data->val($i, 2),
                'customer_id' => $data->val($i, 3),
                'month_1' => $data->val($i, 4),
                'month_2' => $data->val($i, 5),
                'month_3' => $data->val($i, 6),
                'month_4' => $data->val($i, 7),
                'month_5' => $data->val($i, 8),
                'month_6' => $data->val($i, 9),
                'month_7' => $data->val($i, 10),
                'month_8' => $data->val($i, 11),
                'month_9' => $data->val($i, 12),
                'month_10' => $data->val($i, 13),
                'month_11' => $data->val($i, 14),
                'month_12' => $data->val($i, 15)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Data
            $item = $this->crud->read('item_fg', [], ["number_customer" => $data['number_customer']]);
            $customer = $this->crud->read('customers', [], ["id" => $data['customer_id']], "", "name", "asc");
            $forecasts = $this->crud->reads('forecasts', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "item_fg_id" => @$item->id
            ]);

            unset($data['number_customer']);

            $nik = $this->crud->read('users', [], ['username' => $this->session->username])->number;
            $this->db->distinct()->select("customer_id")
            ->from("setting_customer")
            ->where(["user_id" => $nik])->order_by("customer_id");
            $nicks = [];
            foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->customer_id); }
            // die(var_dump($customer->nickname));
            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $item->number_customer . " Not Found", "theme" => "error"));
            } elseif (empty($customer->id)) {
                echo json_encode(array("title" => "Failed", "message" => "Customer ID ".$data['customer_id'] . " Not Found", "theme" => "error"));
            } elseif (!in_array($customer->id, $nicks)) {
                echo json_encode(array("title" => "Failed", "message" => "You Not Allowed to Entry This Customer, ".$customer->name, "theme" => "error"));
            } elseif (count($forecasts) > 0) {
            	$send = $this->crud->update('forecasts', [
	                "p_month" => $data['p_month'],
	                "p_year" => $data['p_year'],
	                "revision" => $data['revision'],
	                "item_fg_id" => $item->id], $data);

                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $item->number_customer . " Update Successfully", "theme" => "success"));
            } else {
                $dataFinal = array_merge($data, array(
                    "item_fg_id" => @$item->id
                ));

                $send = $this->crud->create('forecasts', $dataFinal);
                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $item->number_customer . " Saved Successfully", "theme" => "success"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/forecasts.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/forecasts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/forecasts.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=forecasts_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_item_fg_id = trim(base64_decode($this->input->get('filter_item_fg_id')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_6 =  date('M Y', strtotime('+6 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_7 =  date('M Y', strtotime('+7 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_8 =  date('M Y', strtotime('+8 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_9 =  date('M Y', strtotime('+9 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_10 =  date('M Y', strtotime('+10 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_11 =  date('M Y', strtotime('+11 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $customers = [];
            if (empty($filter_customer_id)) {
                $nik = $this->crud->read("users", [], ["id" => $this->session->username])->number;
                $this->db->distinct()->select("customer_id")->from("setting_customer")->where(["user_id" => $nik]);
                $wheres = explode(',', $this->db->get()->row()->customer_id);
                $this->db->distinct()->select("customer_id")->from("forecasts")->where_in("customer_id", $wheres);
                foreach ($this->db->get()->result() as $index => $row)
                    { array_push($customers, $row->nick); }
            }

            //Select Query
            $this->db->select("a.*, b.number_customer, b.name as item_fg_name, c.name as customer_name");
            $this->db->from('forecasts a');
            $this->db->join('item_fg b', "a.item_fg_id = b.id");
            $this->db->join('customers c', "a.customer_id = c.id");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if ($filter_customer_id != 'all') {
                if (empty($customers)) { 
                    $this->db->where('a.customer_id', $filter_customer_id); 
                } else { 
                    $this->db->where_in('a.customer_id', $customers); 
                }
            }
            
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->order_by('b.number_customer');
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
                                <small>MASTER FORECAST</small>
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
                <th style="text-align:center;" rowspan="2" width="20">No</th>
                <th style="text-align:center;" rowspan="2">Customer</th>
                <th style="text-align:center;" rowspan="2">Product No</th>
                <th style="text-align:center;" rowspan="2">Product Name</th>
                <th style="text-align:center;" colspan="12">Month</th>
            </tr>
            <tr>
                <th style="text-align:center;">' . $month_0 . '</th>
                <th style="text-align:center;">' . $month_1 . '</th>
                <th style="text-align:center;">' . $month_2 . '</th>
                <th style="text-align:center;">' . $month_3 . '</th>
                <th style="text-align:center;">' . $month_4 . '</th>
                <th style="text-align:center;">' . $month_5 . '</th>
                <th style="text-align:center;">' . $month_6 . '</th>
                <th style="text-align:center;">' . $month_7 . '</th>
                <th style="text-align:center;">' . $month_8 . '</th>
                <th style="text-align:center;">' . $month_9 . '</th>
                <th style="text-align:center;">' . $month_10 . '</th>
                <th style="text-align:center;">' . $month_11 . '</th>
            </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td style="text-align:center;">' . $data['month_1'] . '</td>
                            <td style="text-align:center;">' . $data['month_2'] . '</td>
                            <td style="text-align:center;">' . $data['month_3'] . '</td>
                            <td style="text-align:center;">' . $data['month_4'] . '</td>
                            <td style="text-align:center;">' . $data['month_5'] . '</td>
                            <td style="text-align:center;">' . $data['month_6'] . '</td>
                            <td style="text-align:center;">' . $data['month_7'] . '</td>
                            <td style="text-align:center;">' . $data['month_8'] . '</td>
                            <td style="text-align:center;">' . $data['month_9'] . '</td>
                            <td style="text-align:center;">' . $data['month_10'] . '</td>
                            <td style="text-align:center;">' . $data['month_11'] . '</td>
                            <td style="text-align:center;">' . $data['month_12'] . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
