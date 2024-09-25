<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Simulation_mps extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        // $this->load->model('banshu');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/simulation_mps');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily(){
        $prodfam = $this->api->query("SELECT * FROM mst_item_family WHERE `number` != '06'");
        echo json_encode($prodfam);
    }

    public function readProducts($pfm_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->api->query("SELECT * FROM mst_item_raw WHERE pfm_id = '$pfm_id' and item_id like '%$post%'");
        echo json_encode($items);
    }

    public function getData(){
        $post = $this->input->post();

        $this->db->select("a.*, b.item_name, c.name as product_family");
        $this->db->from('mst_bom a');
        $this->db->join('mst_item_raw b', 'a.component_id = b.item_id');
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->where('a.item_id', $post['product_no']);
        $this->db->order_by('a.item_id');
        $this->db->group_by('a.component_id');
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        $data = array();
        foreach ($records as $record) {
            $data[] = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                "item_id" => $record['component_id'],
                "item_name" => $record['item_name'],
                "product_family" => $record['product_family'],
                "qty" => $record['qty'],
                "qtyperassy" => ($post['qty'] * $record['qty']),
            );
        }

        $data['total'] = $totalRows;
        die(json_encode($data));
    }

    public function getDataMpp($month, $year, $revision, $item_id){
        $query = $this->db->query("SELECT z.p_month, z.p_year, z.revision, z.component_id as product_no, 
            SUM(total_date_1) as date_1,
            SUM(total_date_2) as date_2,
            SUM(total_date_3) as date_3,
            SUM(total_date_4) as date_4,
            SUM(total_date_5) as date_5,
            SUM(total_date_6) as date_6,
            SUM(total_date_7) as date_7,
            SUM(total_date_8) as date_8,
            SUM(total_date_9) as date_9,
            SUM(total_date_10) as date_10,
            SUM(total_date_11) as date_11,
            SUM(total_date_12) as date_12,
            SUM(total_date_13) as date_13,
            SUM(total_date_14) as date_14,
            SUM(total_date_15) as date_15,
            SUM(total_date_16) as date_16,
            SUM(total_date_17) as date_17,
            SUM(total_date_18) as date_18,
            SUM(total_date_19) as date_19,
            SUM(total_date_20) as date_20,
            SUM(total_date_21) as date_21,
            SUM(total_date_22) as date_22,
            SUM(total_date_23) as date_23,
            SUM(total_date_24) as date_24,
            SUM(total_date_25) as date_25,
            SUM(total_date_26) as date_26,
            SUM(total_date_27) as date_27,
            SUM(total_date_28) as date_28,
            SUM(total_date_29) as date_29,
            SUM(total_date_30) as date_30,
            SUM(total_date_31) as date_31
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.product_no, b.component_id,
            (a.date_1 * b.qty) as total_date_1,
            (a.date_2 * b.qty) as total_date_2,
            (a.date_3 * b.qty) as total_date_3,
            (a.date_4 * b.qty) as total_date_4,
            (a.date_5 * b.qty) as total_date_5,
            (a.date_6 * b.qty) as total_date_6,
            (a.date_7 * b.qty) as total_date_7,
            (a.date_8 * b.qty) as total_date_8,
            (a.date_9 * b.qty) as total_date_9,
            (a.date_10 * b.qty) as total_date_10,
            (a.date_11 * b.qty) as total_date_11,
            (a.date_12 * b.qty) as total_date_12,
            (a.date_13 * b.qty) as total_date_13,
            (a.date_14 * b.qty) as total_date_14,
            (a.date_15 * b.qty) as total_date_15,
            (a.date_16 * b.qty) as total_date_16,
            (a.date_17 * b.qty) as total_date_17,
            (a.date_18 * b.qty) as total_date_18,
            (a.date_19 * b.qty) as total_date_19,
            (a.date_20 * b.qty) as total_date_20,
            (a.date_21 * b.qty) as total_date_21,
            (a.date_22 * b.qty) as total_date_22,
            (a.date_23 * b.qty) as total_date_23,
            (a.date_24 * b.qty) as total_date_24,
            (a.date_25 * b.qty) as total_date_25,
            (a.date_26 * b.qty) as total_date_26,
            (a.date_27 * b.qty) as total_date_27,
            (a.date_28 * b.qty) as total_date_28,
            (a.date_29 * b.qty) as total_date_29,
            (a.date_30 * b.qty) as total_date_30,
            (a.date_31 * b.qty) as total_date_31
        FROM generate_mpp a
        JOIN mst_bom b ON a.product_no = b.item_id
        WHERE a.p_month = '$month' and a.p_year = '$year' and a.revision = '$revision' and b.component_id = '$item_id'
        GROUP BY a.product_no, b.component_id
        ORDER BY a.product_no, b.component_id asc) z
        GROUP BY z.component_id");
        $records = $query->result_array();

        $total = 0;
        foreach ($records as $record) {
            $total += ($record['date_1'] + $record['date_2'] + $record['date_3'] + $record['date_4'] + $record['date_5'] + $record['date_6'] +
            $record['date_7'] + $record['date_8'] + $record['date_9'] + $record['date_10'] + $record['date_11'] + $record['date_12'] +
            $record['date_13'] + $record['date_14'] + $record['date_15'] + $record['date_16'] + $record['date_17'] + $record['date_18'] + $record['date_19'] + 
            $record['date_20'] + $record['date_21'] + $record['date_22'] + $record['date_23'] + $record['date_24'] + $record['date_25'] + $record['date_26'] +
            $record['date_27'] + $record['date_28'] + $record['date_29'] + $record['date_30'] + $record['date_31']);
        }

        return $total;
    }

    public function getDataMrp($month, $year, $revision, $item_id){
        $query = $this->db->query("SELECT need_2 FROM generate_mrp_finals WHERE p_month = '$month' and p_year = '$year' and revision = '$revision' and part_no = '$item_id'");
        $records = $query->row();

        return empty($records) ? 0 : $records->need_2;
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
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_id = trim(base64_decode($this->input->get('filter_item_id')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            //Select Query
            $this->db->select('a.*');
            $this->db->from('simulation_mps a');
            $this->db->join('mst_item_raw b', "b.item_id = a.item_no");
            $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if ($filter_item_id != "") {
                $this->db->where('b.item_id', $filter_item_id);
            }
            if ($filter_item_family != "") {
                $this->db->where('c.number', $filter_item_family);
            }
            if($filter_status == "OK"){
                $this->db->where('a.balance > 0');
            }elseif($filter_status == "SHORTAGE"){
                $this->db->where('a.balance < 0');
            }
            $this->db->order_by('b.item_id', 'asc');

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
            //Database Server MRP
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $item_id = $post['item_id'];
            $cutoff = $post['cutoff'];
            $period_start = $post['p_year']."-".$post['p_month']."-01";
            $period_end = date("Y-m-t", strtotime($post['p_year']."-".$post['p_month']."-01"));
            // $cutoff_start = date("Y-m-01", strtotime("-1 month", strtotime($cutoff)));
            // $cutoff_end = date("Y-m-t", strtotime("-1 month", strtotime($cutoff)));
            $p_month = $post['p_month'];
            $p_year = $post['p_year'];

            // $maxRev = $this->api->query("SELECT MAX(revision) as rev FROM generate_mpp WHERE p_month = '$p_month' and p_year = '$p_year'");
            $revision = $post['revision'];
            // $revision = $maxRev[0]->rev;

            //Stock Warehouse
            $ith = $this->dummy->query("SELECT sum(ith_qty) as qty from ith where ith_item_id = '$item_id' and ith_date <= '$cutoff'");
            $ithRow = $ith->row();
            
            //ETA Qty
            $queryEta = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_date between '$period_start' and '$period_end'");
            $totalEta = $queryEta->row();

            $os_supply = $this->api->read("os_supply", [], ["product_no" => $item_id, "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            $os_mpp = $this->api->read("os_mpp", [], ["product_no" => $item_id, "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            // $balance_wip = $this->api->read("balance_wip", [], ["product_no" => $item_id, "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            
            $stock_whs = round(@$ithRow->qty);
            $etaTotal = empty(@$totalEta->total) ? 0 : @$totalEta->total;
            $os_wo = empty(@$os_mpp->actual) ? 0 : @$os_mpp->actual;
            $os_supply = empty(@$os_supply->actual) ? 0 : @$os_supply->actual;
            // $balance_wip = empty(@$balance_wip->qty) ? 0 : @$balance_wip->qty;
            $totalNeed = $this->getDataMpp($p_month, $p_year, $revision, $item_id);
            $totalNeed2 = $this->getDataMrp($p_month, $p_year, $revision, $item_id);
            $freestock = (($stock_whs + $etaTotal) - ($os_wo + $os_supply + $totalNeed + $totalNeed2));

            $mps = $this->api->reads('simulation_mps', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_no" => $item_id
            ]);

            if (count($mps) > 0) {
                if($freestock <= 0){
                    $freestock = 0;
                }

                $dataFinal = array(
                    "qtyperassy" => ($mps[0]->qtyperassy + $post['qtyperassy']),
                    "qty" => $post['qty'],
                    "stock_whs" => $stock_whs,
                    "eta_total" => $etaTotal,
                    "os_wo" => $os_wo,
                    "os_supply" => $os_supply,
                    "need_1" => $totalNeed,
                    "need_2" => $totalNeed2,
                    "freestock" => $freestock,
                    "balance" => ($freestock - $post['qtyperassy']),
                    "status" => "INPUT"
                );

                $this->db->update('simulation_mps', $dataFinal, ["p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                "item_no" => $item_id]);

                echo json_encode(array("title" => "Good Job", "message" => "Part No " . $item_id . " for Qty (".$post['qtyperassy'].") Updated", "theme" => "success"));
            } else {

                if($freestock <= 0){
                    $freestock = 0;
                }

                $dataFinal = array(
                    "p_month" => $p_month,
                    "p_year" => $p_year,
                    "revision" => $post['revision'],
                    "cutoff" => $post['cutoff'],
                    "item_no" => $item_id,
                    "item_name" => $post['item_name'],
                    "product_family" => $post['product_family'],
                    "qtyperassy" => $post['qtyperassy'],
                    "qty" => $post['qty'],
                    "stock_whs" => $stock_whs,
                    "eta_total" => $etaTotal,
                    "os_wo" => $os_wo,
                    "os_supply" => $os_supply,
                    "need_1" => $totalNeed,
                    "need_2" => $totalNeed2,
                    "freestock" => $freestock,
                    "balance" => ($freestock - $post['qtyperassy']),
                    "status" => "INPUT"
                );

                $this->api->create('simulation_mps', $dataFinal, "SM", "simulation_mps");
                echo json_encode(array("title" => "Good Job", "message" => "Part No " . $item_id . " for Qty (".$post['qtyperassy'].") Created", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('simulation_mps', $data);
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

        $this->api->delete('simulation_mps', [
            "p_month" => $data->val(2, 3),
            "p_year" => $data->val(2, 4),
            "revision" => $data->val(3, 3),
        ]);

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'cutoff' => $data->val(4, 3),
                'product_no' => $data->val($i, 2),
                'qty' => $data->val($i, 3),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/simulation_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/simulation_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/simulation_mps.txt";

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
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=simulation_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = trim(base64_decode($this->input->get('filter_item_id')));

        //Select Query
        $this->db->select('a.*');
        $this->db->from('simulation_mps a');
        $this->db->join('mst_item_raw b', "b.item_id = a.item_no");
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if ($filter_item_id != "") {
            $this->db->where('b.item_id', $filter_item_id);
        }
        if ($filter_item_family != "") {
            $this->db->where('c.number', $filter_item_family);
        }
        $this->db->order_by('b.item_id', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; }</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER SIMULATION MPS</small>
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
                <th style="text-align:center;" width="20">No</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Free Stock</th>
                <th style="text-align:center;">Need</th>
                <th style="text-align:center;">Balance</th>
                <th style="text-align:center;">OTW</th>
                <th style="text-align:center;">ETA</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['freestock'] . '</td>
                            <td>' . $data['qtyperassy'] . '</td>
                            <td>' . $data['balance'] . '</td>
                            <td>' . $data['otw'] . '</td>
                            <td>' . $data['eta'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
