<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_schedule_days extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('mrp');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/delivery_schedule_days');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily(){
        $prodfam = $this->crud->query("SELECT * FROM mst_item_family WHERE `number` != '06'");
        echo json_encode($prodfam);
    }

    public function readProducts($pfm_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->crud->query("SELECT * FROM mst_item_raw WHERE pfm_id = '$pfm_id' and item_id like '%$post%'");
        echo json_encode($items);
    }

    public function hkw($bulan, $tahun)
    {
        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }

        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        $hkw = 0;
        for ($d = 1; $d <= $jumlahhari; $d++) {
            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('deleted_is', 0);
            $this->db->where('working_date', $tanggal);
            $data = $this->db->get()->result_array();

            $hkw += 1;

            if (@$data[0]['remarks'] != "") {
                $hkw -= 1;
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $hkw -= 1;
            }
        }

        return $hkw;
    }

    public function calendars()
    {
        $bulan = $this->input->post('month');
        $tahun = $this->input->post('year');
        $id = $this->input->post('id');
        $mpq = $this->input->post('mpq');

        if ($bulan == "" or $tahun == "") {
            $bulan = date('m');
            $tahun = date('Y');
        }

        $hari = "01";
        $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));

        $html = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }

                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 10px;
                    }

                    #customers td,
                    #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                        height:50px;
                    }

                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }

                    #customers tr:hover {
                        background-color: #ddd;
                    }

                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }
                </style>
                <table id="customers" style="width: 100%;">
                    <tr>
                        <td align=center width="100">
                            <font color="#FF0000">Sunday</font>
                        </td>
                        <td align=center width="100">Monday</td>
                        <td align=center width="100">Tuesday</td>
                        <td align=center width="100">Wednesday</td>
                        <td align=center width="100">Thursday</td>
                        <td align=center width="100">Friday</td>
                        <td align=center width="100">Saturday</td>
                    </tr>';

        $s = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));

        for ($ds = 1; $ds <= $s; $ds++) {
            $html .= "<td></td>";
        }

        for ($d = 1; $d <= $jumlahhari; $d++) {
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 0) {
                $html .= "<tr>";
            }

            $tanggal = $tahun . "-" . $bulan . "-" . $d;
            $this->db->select('*');
            $this->db->from('delivery_schedule_days');
            $this->db->where('id', $id);
            $data = $this->db->get()->result_array();

            //Jika Hari Minggu
            $style = "background:white !important;";
            // $checkbox = "<input hidden checked class='checked' type='checkbox' value='" . $d . "' name='days[]' style='float: left; width: 20px;'/>";
            $note = "<table style='100%'>
                        <tr>
                            <td style='background:#bcd8ff;'><small>Plan</small><br><input class='plan' step='".$mpq."' onchange='planTotal(this.value, $d)' type='number' style='width:80px;' id='plan_".$d."' name='plan_".$d."' value='" . @$data[0]['plan_'.$d] . "'/></td>
                            <td style='background:#c4ffbc;'><small>Actual</small><br><input class='del' step='".$mpq."' onchange='delTotal(this.value, $d)' type='number' style='width:80px;' id='del_".$d."' name='del_".$d."' value='" . @$data[0]['del_'.$d] . "'/></td>
                        </tr> 
                    </table>";

            $html .= "  <td align=center style='" . $style . "' valign=middle>
                            <b style='font-size: 20px;'>$d</b><br>
                            $note
                        </td>";

            //Jika Sudah seminggu
            if (date("w", mktime(0, 0, 0, $bulan, $d, $tahun)) == 6) {
                $html .= "</tr>";
            }
        }
        $html .= '</table>';

        echo $html;
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

            $filter_plant = base64_decode($this->input->get('filter_plant'));
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_po = base64_decode($this->input->get('filter_po'));
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));

            //Select Query
            $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name, (case when f.mpq is not null then f.mpq else d.mpq end) as mpq, (case when f.moq is not null then f.moq else d.moq end) as moq, d.leadtime, c.name as supplier_name, e.name as product_family_name");
            $this->db->from('delivery_schedule_days a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id', 'left');
            $this->db->join('item_familys e', 'b.item_family_id = e.id');
            $this->db->join('convertions f', 'a.item_rm_id = f.item_rm_id', 'left');
            $this->db->where('a.plant', $filter_plant);
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_item_family != ""){
                $this->db->where('e.id', $filter_item_family);
            }
            if($filter_supplier != ""){
                $this->db->where('a.supplier_id', $filter_supplier);
            }
            if($filter_po != ""){
                $this->db->where('a.po_no', $filter_po);
            }
            $this->db->like('b.id', $filter_item_id);
            $this->db->group_by('a.id');
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('b.number', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $dataFinal = array();
            foreach ($records as $record) {
                $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

                $balance = $record['qty_bf'];
                $day = 1;
                $arr = array();
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $actual_qty = $record["del_".$day];
                    $plan_qty = $record["plan_".$day];
                    $balance = (($balance + @$plan_qty) - round(@$actual_qty));

                    $arr = array_merge($arr, [
                        "del_".$day => $actual_qty,
                        "plan_".$day => $plan_qty,
                        "os_".$day => $balance,
                    ]);

                    $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
                    $day++;
                }

                $dataFinal[] = array_merge($arr, [
                    "id" => $record['id'],
                    "item_rm_id" => $record['item_rm_id'],
                    "item_rm_number" => $record['item_rm_number'],
                    "item_rm_name" => $record['item_rm_name'],
                    "plant" => $record['plant'],
                    "p_month" => $record['p_month'],
                    "p_year" => $record['p_year'],
                    "po_no" => $record['po_no'],
                    "product_family_name" => $record['product_family_name'],
                    "supplier_name" => $record['supplier_name'],
                    "mpq" => $record['mpq'],
                    "moq" => $record['moq'],
                    "leadtime" => $record['leadtime'],
                    "qty" => $record['qty'],
                    "qty_bf" => $record['qty_bf'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date']
                ]);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function getData(){
        //Database Server MRP
        // $this->dummy = $this->load->database('export', TRUE);

        //Filter Data
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_po = base64_decode($this->input->get('filter_po'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        $period_start = date("Y-m-01", strtotime($filter_year."-".$filter_month."-10"));
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-10"));

        $period_bf_start = date("Y-m-01", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-10")));
        $period_bf_end = date("Y-m-t", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-10")));

        $this->db->select('a.po_no, a.item_rm_id, d.number as item_rm_number, a.supplier_id, a.qty');
        $this->db->from('purchase_orders a');
        $this->db->join('supplier_items b', 'a.supplier_id = b.supplier_id and a.item_rm_id = b.item_rm_id and a.plant = b.plant');
        $this->db->join('suppliers c', 'b.supplier_id = c.id');
        $this->db->join('item_rm d', 'b.item_rm_id = d.id');
        // $this->db->join('makers e', 'b.maker_id = e.id');
        // $this->db->where('e.type', "LOCAL");
        $this->db->where('a.plant', $filter_plant);
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if($filter_supplier != ""){
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if($filter_po != ""){
            $this->db->where('a.po_no', $filter_po);
        }
        if($filter_item_family != ""){
            $this->db->where('d.item_family_id', $filter_item_family);
        }
        if($filter_item_id != ""){
            $this->db->where('a.item_rm_id', $filter_item_id);
        }
        // $this->db->where('a.qty >', 0);
        // $this->db->where('b.share_order >', 0);
        // $this->db->where('c.type', 'LOCAL');
        $this->db->group_by('a.item_rm_id');
        $this->db->group_by('a.supplier_id');
        $this->db->group_by('a.po_no');
        $records = $this->db->get()->result_array();

        // $ithQuery = $this->dummy->query("SELECT ith_item_id, COALESCE(SUM(ith_qty), 0) as total
        //     FROM ith 
        //     where ith_form like '%IN - %' and ith_form NOT IN ('IN - Adjust IN', 'IN - ADJ IN IDM')
        //     and ith_date between '$period_bf_start' and '$period_bf_end'
        //     GROUP BY ith_item_id");
        // $ithDatas = $ithQuery->result_array();

        // $ithMap = [];
        // foreach ($ithDatas as $ithData) {
        //     $ithMap[$ithData['ith_item_id']] = $ithData['total'];
        // }

        $arr = array();
        foreach ($records as $record) {
            $item_rm_id = $record['item_rm_id'];
            $item_rm_number = $record['item_rm_number'];
            $qty = (int) $record['qty'];

            // $poBf = $this->crud->query("SELECT SUM(qty) as total FROM purchase_orders WHERE item_rm_id = '$item_rm_id' and po_date between '$period_bf_start' and '$period_bf_end'");

            $arr[] = array(
                "plant" => $filter_plant,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision,
                "po_no" => $record['po_no'],
                "supplier_id" => $record['supplier_id'],
                "item_rm_id" => $record['item_rm_id'],
                "item_rm_number" => $record['item_rm_number'],
                "qty" => $qty,
                // "qty_bf" => ($ithMap[$item_rm_number] ?? 0 - @$poBf[0]->total),
            );
        }

        // $this->db->delete('delivery_schedule_days', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function create(){
        // $this->dummy = $this->load->database('export', TRUE);

        if ($this->input->post()) {
            $post = $this->input->post('data');

            $item_rm_id = $post['item_rm_id'];
            $item_rm_number = $post['item_rm_number'];
            $po_no = $post['po_no'];
            $filter_plant = $post['plant'];
            $filter_month = $post['p_month'];
            $filter_year = $post['p_year'];
            $filter_revision = $post['revision'];
            unset($post['item_rm_number']);

            $query_mpp = $this->db->query("SELECT * FROM generate_item_calculation WHERE item_rm_id = '$item_rm_id' and p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision'");
            $mpp = $query_mpp->result_array();

            $supp = $this->crud->read("supplier_items", [], ["item_rm_id" => $post['item_rm_id'], "supplier_id" => $post['supplier_id']]);

            $today = date("Y-m-d");
            // $ithRow = $this->mrp->schedule_lc_now($filter_plant, $item_rm_number, $today);
            // $ithQuery = $this->dummy->query("SELECT sum(ith_qty) as qty from ith where ith_item_id = '$item_rm_number' and ith_date between '2025-01-01' and '$today'");
            // $ithDatas = $ithQuery->result_array();

            $day = 1;
            $arrDay = array();
            $firstDate = date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            
            $plan_qty = ((@$post['qty'] >= 0) ? round(@$post['qty']) : 0);
            // $balance = (@$ithDatas[0]['qty'] - @$mpp[0]['os_supply'] - @$mpp[0]['os_wo']);
            $balance = (@$mpp[0]['os_supply'] - @$mpp[0]['os_wo']);
            while (strtotime($firstDate) <= strtotime($endDate)) {
                $plan_qtys = ((@$mpp[0]['date_'.$day] >= 0) ? round(@$mpp[0]['date_'.$day]) : 0);
                
                if($plan_qty > @$supp->mpq){
                    if($plan_qtys > @$supp->mpq){
                        // $plan_qty_round = (ceil($plan_qtys / @$supp->mpq) * @$supp->mpq);
                        $plan_qty_round = $plan_qtys;
                    }else{
                        $plan_qty_round = @$supp->mpq;
                    }
                }else{
                    $plan_qty_round = $plan_qtys;
                }

                $working_date = date("Y-m-d", strtotime($firstDate));

                //ETA
                // $totalRow = $this->mrp->schedule_lc_eta($filter_plant, $item_rm_number, $working_date);
                // $etaqty = empty(@$totalRow[0]['total']) ? 0 : @$totalRow[0]['total'];

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                // $balance += round($etaqty - $plan_qty_round);
                $balance += round(0 - $plan_qty_round);

                if($balance <= 0){
                    if (date('w', strtotime($firstDate)) !== '0') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            $arrDay["plan_".$day] = 0;
                            $plan_qty -= 0;
                        }else{
                            $arrDay["plan_".$day] = $plan_qty_round;
                            $plan_qty -= $plan_qty_round;
                        }
                    }else{
                        $arrDay["plan_".$day] = 0;
                        $plan_qty -= 0;
                    }
                }

                $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                $day++;
            }

            $delivery_schedule_days = $this->crud->read("delivery_schedule_days", [], ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "item_rm_id" => $item_rm_id, 'po_no' => $po_no]);

            if(!empty($delivery_schedule_days)){
                $this->crud->update('delivery_schedule_days', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "item_rm_id" => $item_rm_id, 'po_no' => $po_no], array_merge($post, $arrDay), "logs");
                echo json_encode(array("title" => "Good Job", "message" => $item_rm_number . " | Data Update Successfully", "theme" => "success"));
                // echo json_encode(array("title" => "Error", "message" => $post['part_no'] . " | Duplicate", "theme" => "error"));
            }else{
                if ($this->crud->create('delivery_schedule_days', array_merge($post, $arrDay), "logs")) {
                    echo json_encode(array("title" => "Good Job", "message" => $item_rm_number . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $item_rm_number . " | Data Unsaved", "theme" => "error"));
                }
            }
        }
    }

    public function update()
    {
        if ($this->input->get()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            // $arr = array();
            // for ($i = 0; $i < count($days); $i++) {
            //     $del = $this->input->post('del_'.$i);
            //     $arr["del_$i"] = $del;
            // }

            $send = $this->crud->update('delivery_schedule_days', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
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

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_rm_number' => $data->val($i, 2),
                'po_no' => $data->val($i, 3),
                'qty_bf' => $data->val($i, 4),
                'plan_1' => $data->val($i, 5),
                'del_1' => $data->val($i, 6),
                'plan_2' => $data->val($i, 7),
                'del_2' => $data->val($i, 8),
                'plan_3' => $data->val($i, 9),
                'del_3' => $data->val($i, 10),
                'plan_4' => $data->val($i, 11),
                'del_4' => $data->val($i, 12),
                'plan_5' => $data->val($i, 13),
                'del_5' => $data->val($i, 14),
                'plan_6' => $data->val($i, 15),
                'del_6' => $data->val($i, 16),
                'plan_7' => $data->val($i, 17),
                'del_7' => $data->val($i, 18),
                'plan_8' => $data->val($i, 19),
                'del_8' => $data->val($i, 20),
                'plan_9' => $data->val($i, 21),
                'del_9' => $data->val($i, 22),
                'plan_10' => $data->val($i, 23),
                'del_10' => $data->val($i, 24),
                'plan_11' => $data->val($i, 25),
                'del_11' => $data->val($i, 26),
                'plan_12' => $data->val($i, 27),
                'del_12' => $data->val($i, 28),
                'plan_13' => $data->val($i, 29),
                'del_13' => $data->val($i, 30),
                'plan_14' => $data->val($i, 31),
                'del_14' => $data->val($i, 32),
                'plan_15' => $data->val($i, 33),
                'del_15' => $data->val($i, 34),
                'plan_16' => $data->val($i, 35),
                'del_16' => $data->val($i, 36),
                'plan_17' => $data->val($i, 37),
                'del_17' => $data->val($i, 38),
                'plan_18' => $data->val($i, 39),
                'del_18' => $data->val($i, 40),
                'plan_19' => $data->val($i, 41),
                'del_19' => $data->val($i, 42),
                'plan_20' => $data->val($i, 43),
                'del_20' => $data->val($i, 44),
                'plan_21' => $data->val($i, 45),
                'del_21' => $data->val($i, 46),
                'plan_22' => $data->val($i, 47),
                'del_22' => $data->val($i, 48),
                'plan_23' => $data->val($i, 49),
                'del_23' => $data->val($i, 50),
                'plan_24' => $data->val($i, 51),
                'del_24' => $data->val($i, 52),
                'plan_25' => $data->val($i, 53),
                'del_25' => $data->val($i, 54),
                'plan_26' => $data->val($i, 55),
                'del_26' => $data->val($i, 56),
                'plan_27' => $data->val($i, 57),
                'del_27' => $data->val($i, 58),
                'plan_28' => $data->val($i, 59),
                'del_28' => $data->val($i, 60),
                'plan_29' => $data->val($i, 61),
                'del_29' => $data->val($i, 62),
                'plan_30' => $data->val($i, 63),
                'del_30' => $data->val($i, 64),
                'plan_31' => $data->val($i, 65),
                'del_31' => $data->val($i, 66),
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

            $item_rm = $this->crud->read("item_rm", [], ["number" => trim($data['item_rm_number'])]);
            $delivery_schedule_days = $this->crud->read('delivery_schedule_days', [], ["item_rm_id" => @$item_rm->id, "po_no" => $data['po_no']]);

            if (empty($delivery_schedule_days->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_number'] . " and PO No " . $data['po_no'] . " Not Found", "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Good Job", "message" => "Part No" . $data['item_rm_number'] . " Updated Successfully", "theme" => "success"));
                unset($data['item_rm_number']);
                $this->crud->update('delivery_schedule_days', ["item_rm_id" => @$item_rm->id, "po_no" => $data['po_no']], $data);
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/delivery_schedule_days.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/delivery_schedule_days.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/delivery_schedule_days.txt";

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
            header("Content-Disposition: attachment; filename=delivery_schedule_days_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Database Server MRP
        $this->dummy = $this->load->database('export', TRUE);

        //Filter Data
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_po = base64_decode($this->input->get('filter_po'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
        $monthBf = date('F', strtotime("-1 month", strtotime($filter_year . "-" . $filter_month . "-01")));

        //Select Query
        $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name, (case when f.mpq is not null then f.mpq else d.mpq end) as mpq, (case when f.moq is not null then f.moq else d.moq end) as moq, d.leadtime, c.name as supplier_name, e.name as product_family_name");
        $this->db->from('delivery_schedule_days a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id', 'left');
        $this->db->join('item_familys e', 'b.item_family_id = e.id');
        $this->db->join('convertions f', 'a.item_rm_id = f.item_rm_id', 'left');
        $this->db->where('a.plant', $filter_plant);
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if($filter_item_family != ""){
            $this->db->where('e.id', $filter_item_family);
        }
        if($filter_supplier != ""){
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if($filter_po != ""){
            $this->db->where('a.po_no', $filter_po);
        }
        $this->db->like('b.id', $filter_item_id);
        $this->db->group_by('a.id');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $content = '';
        $no = 1;
        $colspan = 0;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $styles = 'background:#FFD974;';
                    $cut_no += 0;
                    // $cut_content = "";
                } else{
                    $styles = "";
                    $cut_no += 1;
                    // $cut_content = "W/P ". $cut_no;
                }
            } else {
                $styles = 'background:#FFD974;';
                $cut_no += 0;
                // $cut_content = "";
            }

            //Setting Header
            $cut .= '<th width="50" colspan="3" style="text-align:center; ' . $styles . '">'.$firstDate.'</th>';
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">Plan</th>
                      <th width="50" style="text-align:center; ' . $styles . '">Actual</th>
                      <th width="50" style="text-align:center; ' . $styles . '">OS</th>';

            $colspan++;
            $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
        }

        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';

        foreach ($records as $record) {
            $day = 1;
            $firstDate2 = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

            $content .= "<tr>
                            <td>" . $no . "</td>
                            <td>" . $record['plant'] . "</td>
                            <td>" . $record['p_year'] . $record['p_month'] . "</td>
                            <td>" . $record['revision'] . "</td>
                            <td style='mso-number-format:\@;'>" . $record['item_rm_number'] . "</td>
                            <td>" . $record['item_rm_name'] . "</td>
                            <td>" . $record['po_no'] . "</td>
                            <td>" . $record['product_family_name'] . "</td>
                            <td>" . $record['supplier_name'] . "</td>
                            <td style='text-align:right;'>" . $record['mpq'] . "</td>
                            <td style='text-align:right;'>" . $record['moq'] . "</td>
                            <td style='text-align:right;'>" . $record['leadtime'] . "</td>
                            <td style='text-align:right;'>" . $record['qty'] . "</td>
                            <td style='text-align:right;'>" . round($record['qty_bf']) . "</td>";

            $styles2 = "";
            $balance = @$record['qty_bf'];
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date2 = date("Y-m-d", strtotime($firstDate2));
                $actual_qty = $record["del_".$day];
                $plan_qty = $record["plan_".$day];

                $balance = (($balance + @$plan_qty) - round(@$actual_qty));

                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date2);
                $holiday = $this->db->get()->row();

                if (date('w', strtotime($firstDate2)) !== '0' && date('w', strtotime($firstDate2)) !== '6') {
                    if (@$holiday->remarks != null or @$holiday->remarks != ""){
                        $styles2 = 'background:#FFD974;';
                    }else{
                        if($balance < 0){
                            $styles2 = 'background:#FFC2C2;';
                        }else{
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".$plan_qty."</td>
                            <td style='text-align:right; ".$styles2."'>".round(@$actual_qty)."</td>
                            <td style='text-align:right; ".$styles2."'>".round($balance, 2)."</td>";

                $firstDate2 = date("d M Y", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $content .= "   
                        </tr>";
            $no++;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MATERIAL CALCULATION</small>
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

        <table id="customers" border="1" style="width:100%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">No</th>
                <th style="text-align:center;" rowspan="3" width="50">Plant</th>
                <th style="text-align:center;" rowspan="3" width="50">Period</th>
                <th style="text-align:center;" rowspan="3" width="20">Revision</th>
                <th style="text-align:center;" width="150" rowspan="3">Part No</th>
                <th style="text-align:center;" width="80" rowspan="3">Part Name</th>
                <th style="text-align:center;" width="80" rowspan="3">PO No</th>
                <th style="text-align:center;" width="80" rowspan="3">Product Family</th>
                <th style="text-align:center;" width="200" rowspan="3">Supplier</th>
                <th style="text-align:center;" width="50" rowspan="3">MPQ</th>
                <th style="text-align:center;" width="50" rowspan="3">MOQ</th>
                <th style="text-align:center;" width="50" rowspan="3">Leadtime</th>
                <th style="text-align:center;" width="50" rowspan="3">Qty PO</th>
                <th style="text-align:center;" width="50" rowspan="3">OS Delivery<br>('.$monthBf.')</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan * 3).'">Quantitiy Order From Purchase Order</th>
            </tr>' . $cut . $date . $content;
        echo $html;
    }
}
