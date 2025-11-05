<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Material_calculation extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.item_rm_id]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/material_calculation');
        } else {
            redirect('error_access');
        }
    }
    public function cutoffDate()
    {
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        echo date("Y-m-d", strtotime("-1 days", strtotime($year . "-" . $month . "-01")));
    }
    public function readItemFamily()
    {
        $prodfam = $this->crud->query("SELECT * FROM item_familys WHERE `item_category_id` = 'C01'");
        echo json_encode($prodfam);
    }
    public function readProducts($pfm_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->crud->query("SELECT * FROM item_rm WHERE item_family_id like '%$pfm_id%' and item_category_id = 'C01' and (`number` like '%$post%' or id like '%$post%') ORDER BY number asc limit 100");
        echo json_encode($items);
    }
    public function datatables()
    {
        //Database Server MRP
        $this->dummy = $this->load->database('export', TRUE);
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
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));
            $filter_status = $this->input->get('filter_status');
            $filter_date = base64_decode($this->input->get('filter_date'));
            $end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
            $start = date("Y-m-01", strtotime($filter_year . "-" . $filter_month . "-01"));
            $this->db->select("a.*, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name, d.prod_plan_1, (d.prod_plan_1 - a.total) as total_ltpp_vs_mpp");
            $this->db->from('generate_item_calculation a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys b', 'c.item_family_id = b.id');
            $this->db->join('mrp_ltpp d', 'a.plant = d.plant and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision and a.item_rm_id = d.item_rm_id', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('c.item_family_id', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_id);
            $this->db->order_by('b.number');
            $this->db->group_by('a.item_rm_id');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Receipt atau Scan In
            $sqlPor = $this->dummy->query("SELECT item_id, por_date, SUM(por_receiveqty) as qty
                FROM por
                WHERE por_date between '$start' and '$end'
                GROUP BY item_id, por_date
                ORDER BY item_id, por_date ASC");
            $dPor = $sqlPor->result_array();
            $dPorMap = [];
            foreach ($dPor as $por) {
                $dPorMap[$por['item_id']][$por['por_date']] = $por['qty'];
            }
            //Supply atau Scan out
            $sqlSupply = $this->dummy->query("SELECT a.item_id, b.ith_date, SUM(abs(b.ith_qty)) as qty_out 
                FROM serial_detail_kanbanrm a 
                JOIN ith b ON a.kanbanrm_id = b.ith_docno and a.item_id = b.ith_item_id 
                WHERE b.ith_date between '$start' and '$end'
                GROUP BY a.item_id, b.ith_date
                ORDER BY a.item_id, b.ith_date");
            $dSupply = $sqlSupply->result_array();
            $dSupplyMap = [];
            foreach ($dSupply as $supply) {
                $dSupplyMap[$supply['item_id']][$supply['ith_date']] = $supply['qty_out'];
            }
            $dataFinal = array();
            foreach ($records as $record) {
                $item_rm_id = $record['item_rm_id'];
                $item_rm_number = $record['item_rm_number'];
                $balances = ($record['stock_hand'] + $record['eta'] - $record['total']);
                $firstDate = date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                $day = 1;
                $balance = $record['stock_hand'];
                $total_eta = 0;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date("Y-m-d", strtotime($firstDate));
                    $etaqty = ($dPorMap[$item_rm_number][$working_date] ?? 0);
                    $supplyqty = ($dSupplyMap[$item_rm_number][$working_date] ?? 0);
                    $date_qty = $record["date_" . $day];
                    $balance += round($etaqty - $date_qty);
                    $arr["eta_" . $day] = (int) $etaqty;
                    $arr["date_" . $day] = round($date_qty);
                    $arr["supply_" . $day] = abs(round(@$supplyqty));
                    $arr["bal_" . $day] = round($balance);
                    $total_eta += (int) $etaqty;
                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $day++;
                }
                if ($balances > 0) {
                    $free_stock = $balances;
                } else {
                    $free_stock = 0;
                }
                // $ospo = $this->dummy->query("SELECT SUM(z.balance) as balance FROM (
                //     SELECT a.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
                //     FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$item_rm_number' GROUP BY item_id, poc_id, poc_reqdate) b 
                //     LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$item_rm_number' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
                //     WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0
                //     ORDER BY b.poc_reqdate ASC) z");
                // $ospoRow = $ospo->row();

                $data = array_merge($arr, [
                    "item_rm_id" => $item_rm_id,
                    "item_rm_number" => $record['item_rm_number'],
                    "item_rm_name" => $record['item_rm_name'],
                    "product_family_name" => $record['item_family_name'],
                    "stock_whs" => $record['stock_whs'],
                    "os_supply" => $record['os_supply'],
                    "os_wo" => $record['os_wo'],
                    "stock_hand" => $record['stock_hand'],
                    "eta" => $total_eta,
                    "total" => round($record['total']),
                    "total_ltpp" => round($record['prod_plan_1']),
                    "total_ltpp_vs_mpp" => round($record['total_ltpp_vs_mpp']),
                    "total_bal" => round($balances),
                    "free_stock" => round($free_stock),
                    "next_month_need1" => round($record['need_2'] / 4),
                    "next_month_need2" => round($record['need_2'] / 4),
                    "next_month_need3" => round($record['need_2'] / 4),
                    "next_month_need4" => round($record['need_2'] / 4),
                    "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                    "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "balance_wip" => $record['balance_wip'],
                    "total_whs" => ($record['stock_whs'] + $record['balance_wip']),
                    // "os_po" => @$ospoRow->balance,
                    "os_po" => $record['os_po']
                ]);
                if ($filter_status == "OK") {
                    if (round($free_stock) > 0) {
                        $dataFinal[] = $data;
                    }
                } elseif ($filter_status == "SHORTAGE") {
                    if (round($free_stock) == 0) {
                        $dataFinal[] = $data;
                    }
                } else {
                    $dataFinal[] = $data;
                }
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }
    public function getData()
    {
        $this->dummy = $this->load->database('export', TRUE);
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_date = base64_decode($this->input->get('filter_date'));
        $end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $start = date("Y-m-01", strtotime($filter_year . "-" . $filter_month . "-01"));
        $dwip = $this->mrp->wip($filter_date, $filter_plant);
        $dwipMap = [];
        foreach ($dwip as $wip) {
            $dwipMap[$wip['item_id']] = $wip['stock'];
        }
        $dStock = $this->mrp->stock($filter_date, $filter_plant);
        $dStockMap = [];
        foreach ($dStock as $stock) {
            $dStockMap[$stock['item']] = $stock['qty'];
        }
        $sqlPor = $this->dummy->query("SELECT item_id, SUM(por_receiveqty) as qty
            FROM por
            WHERE por_date between '$start' and '$end'
            GROUP BY item_id");
        $dPor = $sqlPor->result_array();
        $dPorMap = [];
        foreach ($dPor as $por) {
            $dPorMap[$por['item_id']] = $por['qty'];
        }
        $data = $this->mrp->material_calculation($filter_plant, $filter_year . $filter_month);
        $arr = array();
        for ($i = 0; $i < count($data); $i++) {
            $item_rm = $this->crud->read('item_rm', [], ["number" => trim($data[$i]['item_id'])]);
            $dataWip = ($dwipMap[$data[$i]['item_id']] ?? 0);
            $dataStock = ($dStockMap[$data[$i]['item_id']] ?? 0);
            $dataEta = ($dPorMap[$data[$i]['item_id']] ?? 0);
            if (@$dataWip > 0) {
                $qty_wip = 0;
                $os_supply = abs($dataWip);
                $os_wo = 0;
            } else {
                $qty_wip = abs($dataWip);
                $os_supply = 0;
                $os_wo = 0;
            }
            $stock_hand = round(@$dataStock + $qty_wip - $os_supply - $os_wo);
            if (@$item_rm->id != "") {
                $arr[] = array(
                    "item_rm_id" => @$item_rm->id,
                    "plant" => $filter_plant,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "os_supply" => $os_supply,
                    "os_wo" => $os_wo,
                    "balance_wip" => $qty_wip,
                    "stock_whs" => $dataStock,
                    "stock_hand" => $stock_hand,
                    "eta" => $dataEta,
                    "date_1" => $data[$i]['date_1'],
                    "date_2" => $data[$i]['date_2'],
                    "date_3" => $data[$i]['date_3'],
                    "date_4" => $data[$i]['date_4'],
                    "date_5" => $data[$i]['date_5'],
                    "date_6" => $data[$i]['date_6'],
                    "date_7" => $data[$i]['date_7'],
                    "date_8" => $data[$i]['date_8'],
                    "date_9" => $data[$i]['date_9'],
                    "date_10" => $data[$i]['date_10'],
                    "date_11" => $data[$i]['date_11'],
                    "date_12" => $data[$i]['date_12'],
                    "date_13" => $data[$i]['date_13'],
                    "date_14" => $data[$i]['date_14'],
                    "date_15" => $data[$i]['date_15'],
                    "date_16" => $data[$i]['date_16'],
                    "date_17" => $data[$i]['date_17'],
                    "date_18" => $data[$i]['date_18'],
                    "date_19" => $data[$i]['date_19'],
                    "date_20" => $data[$i]['date_20'],
                    "date_21" => $data[$i]['date_21'],
                    "date_22" => $data[$i]['date_22'],
                    "date_23" => $data[$i]['date_23'],
                    "date_24" => $data[$i]['date_24'],
                    "date_25" => $data[$i]['date_25'],
                    "date_26" => $data[$i]['date_26'],
                    "date_27" => $data[$i]['date_27'],
                    "date_28" => $data[$i]['date_28'],
                    "date_29" => $data[$i]['date_29'],
                    "date_30" => $data[$i]['date_30'],
                    "date_31" => $data[$i]['date_31']
                );
            }
        }
        $this->db->delete('generate_item_calculation', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "plant" => $filter_plant]);
        echo json_encode($arr);
    }
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $cutoff = $this->input->post('cutoff');
            $end = date("Y-m-t", strtotime($post['p_year'] . "-" . $post['p_month'] . "-01"));
            $start = date("Y-m-01", strtotime($post['p_year'] . "-" . $post['p_month'] . "-01"));
            $next = date("Y-m-01", strtotime("+1 month", strtotime($start)));
            $item_rm_id = $post['item_rm_id'];
            $postFinal = array_merge($post, array(
                "created_date" => date("Y-m-d H:i:s"),
                "created_by" => $this->session->username,
                "cutoff" => $cutoff,
                "total" => ($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] +
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] +
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] +
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] +
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] +
                    $post['date_31']),
                "balance" => (($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] +
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] +
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] +
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] +
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] +
                    $post['date_31']))
            ));
            if ($this->db->insert('generate_item_calculation', $postFinal)) {
                echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_id'] . " | Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Error", "message" => $post['item_rm_id'] . " | Data Unsaved", "theme" => "error"));
            }
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_calculation_$format.xls");
        }
        $this->dummy = $this->load->database('export', TRUE);
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        // select a.periode, a.wp, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, REGEXP_REPLACE(a.wp, '([0-9]{2})[^0-9]([0-9]{2})', '\1/\2') as final
        // from wip_trx_mpp a
        // join wip_trx_wds b ON a.serial_mpp = b.serial_mpp
        // where b.precut_sts = '1' and a.periode = '202510'
        // GROUP BY a.periode, a.wp, a.wp_date
        // ORDER BY a.wp_date asc
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $filter_status = $this->input->get('filter_status');
        $filter_date = base64_decode($this->input->get('filter_date'));
        $end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $start = date("Y-m-01", strtotime($filter_year . "-" . $filter_month . "-01"));
        $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
        $period_start = $filter_year . "-" . $filter_month . "-01";
        $period_end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $next_month = date("F", strtotime("+1 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $this->db->select("a.*, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name, d.prod_plan_1, (d.prod_plan_1 - a.total) as total_ltpp_vs_mpp");
        $this->db->from('generate_item_calculation a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys b', 'c.item_family_id = b.id');
        $this->db->join('mrp_ltpp d', 'a.plant = d.plant and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision and a.item_rm_id = d.item_rm_id', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('c.item_family_id', $filter_item_family);
        $this->db->like('a.item_rm_id', $filter_item_id);
        $this->db->order_by('b.number');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();
        //Receipt atau Scan In
        $sqlPor = $this->dummy->query("SELECT item_id, por_date, SUM(por_receiveqty) as qty
            FROM por
            WHERE por_date between '$start' and '$end'
            GROUP BY item_id, por_date
            ORDER BY item_id, por_date ASC");
        $dPor = $sqlPor->result_array();
        $dPorMap = [];
        foreach ($dPor as $por) {
            $dPorMap[trim($por['item_id'])][$por['por_date']] = $por['qty'];
        }
        //Supply atau Scan out
        $sqlSupply = $this->dummy->query("SELECT a.item_id, b.ith_date, SUM(abs(b.ith_qty)) as qty_out 
            FROM serial_detail_kanbanrm a 
            JOIN ith b ON a.kanbanrm_id = b.ith_docno and a.item_id = b.ith_item_id 
            WHERE b.ith_date between '$start' and '$end'
            GROUP BY a.item_id, b.ith_date
            ORDER BY a.item_id, b.ith_date");
        $dSupply = $sqlSupply->result_array();
        $dSupplyMap = [];
        foreach ($dSupply as $supply) {
            $dSupplyMap[trim($supply['item_id'])][$supply['ith_date']] = $supply['qty_out'];
        }
        //Setting Header
        $styles = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $no = 1;
        $colspan = 0;
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));
            $this->db->select('remarks');
            $this->db->from('calendars');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();
            if (date('w', strtotime($firstDate)) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $styles = 'background:#FFD974;';
                    $cut_no += 1;
                    $cut_content = "W/P " . $cut_no;
                } else {
                    $styles = "";
                    $cut_no += 1;
                    $cut_content = "W/P " . $cut_no;
                }
            } else {
                $styles = 'background:#FFD974;';
                $cut_no += 1;
                $cut_content = "W/P " . $cut_no;
            }
            //Setting Header
            // $cut .= '<th width="50" colspan="4" style="text-align:center; ' . $styles . '">' . $cut_content . ' (' . $firstDate . ')</th>';
            $cut .= '<th width="50" colspan="4" style="text-align:center; ' . $styles . '">' . $cut_content . '</th>';
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">SCAN IN</th>
                      <th width="50" style="text-align:center; ' . $styles . '">NEED</th>
                      <th width="50" style="text-align:center; ' . $styles . '">SUPPLY</th>
                      <th width="50" style="text-align:center; ' . $styles . '">BAL</th>';
            $colspan++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }
        $date .= '  <th width="50" style="text-align:center;">WEEK 1</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 2</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 3</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 4</th>
                    <th width="50" style="text-align:center;">BALANCE</th>';
        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';
        $content2 = "";
        foreach ($records as $record) {
            $item_rm_id = $record['item_rm_id'];
            $item_rm_number = $record['item_rm_number'];
            $content = '';
            $day = 1;
            $i = 0;
            $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            $balances = ($record['stock_hand'] + $record['eta'] - $record['total']);
            if ($balances > 0) {
                $free_stock = $balances;
            } else {
                $free_stock = 0;
            }
            $wp = "";
            $styles2 = "";
            $balance = $record['stock_hand'];
            $total_eta = 0;
            $contentisi = "";
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date2 = date("Y-m-d", strtotime($firstDate2));
                $etaqty = isset($dPorMap[$item_rm_number][$working_date2]) ? $dPorMap[$item_rm_number][$working_date2] : 0;
                $supplyqty = isset($dSupplyMap[$item_rm_number][$working_date2]) ? $dSupplyMap[$item_rm_number][$working_date2] : 0;
                $date_qty = $record["date_" . $day];
                $balance += round($etaqty - $date_qty);
                $this->db->select('remarks');
                $this->db->from('calendars');
                $this->db->where('working_date', $working_date2);
                $holiday = $this->db->get()->row();
                if (date('w', strtotime($firstDate2)) !== '0') {
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $styles2 = 'background:#FFD974;';
                    } else {
                        if ($balance < 0) {
                            $styles2 = 'background:#FFC2C2;';
                        } else {
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }
                $contentisi .= "<td style='text-align:right; " . $styles2 . "'>" . @$etaqty . "</td>
                            <td style='text-align:right; " . $styles2 . "'>" . round($date_qty) . "</td>
                            <td style='text-align:right; " . $styles2 . "'>" . abs(round(@$supplyqty)) . "</td>
                            <td style='text-align:right; " . $styles2 . "'>" . round($balance) . "</td>";
                $total_eta += $etaqty;
                $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $content .= "<tr>
                        <td>" . $no . "</td>
                        <td style='mso-number-format:\@;'>" . $record['item_rm_id'] . "</td>
                        <td style='mso-number-format:\@;'>" . $item_rm_number . "</td>
                        <td style='mso-number-format:\@;'>" . $record['item_rm_name'] . "</td>
                        <td>" . $record['item_family_name'] . "</td>
                        <td style='text-align:right;'>" . round($record['balance_wip']) . "</td>
                        <td style='text-align:right;'>" . round($record['stock_whs']) . "</td>
                        <td style='text-align:right;'>" . round($record['stock_whs'] + $record['balance_wip']) . "</td>
                        <td style='text-align:right;'>" . round($record['os_supply']) . "</td>
                        <td style='text-align:right;'>" . round($record['stock_hand']) . "</td>
                        <td style='text-align:right;'>" . round($total_eta) . "</td>
                        <td style='text-align:right;'>" . round($record['total']) . "</td>
                        <td style='text-align:right;'>" . round($record['prod_plan_1']) . "</td>
                        <td style='text-align:right;'>" . round($record['total_ltpp_vs_mpp']) . "</td>
                        <td style='text-align:right;'>" . number_format(@$balances) . "</td>";
            $content .= $contentisi;
            $content .= "   <td style='text-align:right; " . $styles . "'>" . round($free_stock) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                        </tr>";
            if ($filter_status == "OK") {
                if ($free_stock > 0) {
                    $content2 .= $content;
                    $no++;
                }
            } elseif ($filter_status == "SHORTAGE") {
                if ($free_stock == 0) {
                    $content2 .= $content;
                    $no++;
                }
            } else {
                $content2 .= $content;
                $no++;
            }
        }
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
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
        <table id="customers" border="1" style="width:150%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">NO</th>
                <th style="text-align:center;" width="150" rowspan="3">PART NO ORIGINAL</th>
                <th style="text-align:center;" width="150" rowspan="3">PART NO SUPPLIER</th>
                <th style="text-align:center;" width="80" rowspan="3">DESCRIPTION</th>
                <th style="text-align:center;" width="80" rowspan="3">PRODUCT FAMILY</th>
                <th style="text-align:center;" width="50" rowspan="3">BALANCE WIP</th>
                <th style="text-align:center;" width="50" rowspan="3">STOCK WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">OS SUPPLY</th>
                <th style="text-align:center;" width="50" rowspan="3">ON HAND<br>STOCK</th>
                <th style="text-align:center;" width="50" rowspan="3">ETA</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(MPP)</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(LTPP)</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(LTPP VS MPP)</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL BAL</th>
                <th style="text-align:center;" width="50" colspan="' . ($colspan * 4) . '">QUANTITY NEED FROM WORKORDER</th>
                <th style="text-align:center;" width="50" rowspan="3">FREE STOCK</th>
                <th style="text-align:center;" width="50" colspan="8" rowspan="2">' . $next_month . '</th>
            </tr>' . $cut . $date . $content2;
        echo $html;
    }
    public function printOsPo()
    {
        $this->db = $this->load->database('db', TRUE);
        $item_rm_id = base64_decode($this->input->get("item_rm_id"));
        $ospo = $this->db->query("SELECT b.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
            FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$item_rm_id' GROUP BY item_id, poc_id, poc_reqdate) b 
            LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$item_rm_id' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
            WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0");
        $records = $ospo->result_array();
        $balance = 0;
        $request = 0;
        $receive = 0;
        foreach ($records as $record) {
            $request += $record['request'];
            $receive += $record['receive'];
            $balance += $record['balance'];
        }
        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_id" => "Grand Total", "balance" => $balance, "receive" => $receive, "request" => $request, "plan" => 0]]])));
    }
    public function printBalance()
    {
        $this->dummy = $this->load->database('export', TRUE);
        $item_rm_id = base64_decode($this->input->get("item_rm_id"));
        $filter_month = base64_decode($this->input->get("filter_month"));
        $filter_year = base64_decode($this->input->get("filter_year"));
        $filter_revision = base64_decode($this->input->get("filter_revision"));
        $period = $filter_year . $filter_month;
        $item_rm = $this->crud->read("item_rm", [], ["id" => $item_rm_id]);
        $item_rm_number = trim($item_rm->number);
        $ospo = $this->dummy->query("SELECT trim(z.assy_no) as product_no, COALESCE(SUM(z.total), 0) as total
        FROM (
            select a.periode, a.wp, a.assy_no, c.bom_com_item, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, (SUM(a.qty) * c.bom_qty_perassy) as total
            from wip_trx_mpp a
            join wip_trx_wds b ON a.serial_mpp = b.serial_mpp
            join mst_bom c ON a.assy_no = c.bom_par_item
            where b.precut_sts = '1' and a.periode = '$period' and c.bom_com_item = '$item_rm_number'
            GROUP BY a.periode, a.wp, a.assy_no, c.bom_com_item, c.bom_qty_perassy, wp_date
        ) z
        GROUP BY z.assy_no
        ORDER BY z.assy_no asc");
        $records = $ospo->result_array();
        $balance = 0;
        foreach ($records as $record) {
            $balance += $record['total'];
        }
        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_rm_id" => "Grand Total", "total" => $balance]]])));
    }
    public function printBalanceDate()
    {
        $this->dummy = $this->load->database('export', TRUE);
        $item_rm_id = base64_decode($this->input->get("item_rm_id"));
        $filter_month = base64_decode($this->input->get("filter_month"));
        $filter_year = base64_decode($this->input->get("filter_year"));
        $filter_revision = base64_decode($this->input->get("filter_revision"));
        $date = base64_decode($this->input->get("date"));
        $period = $filter_year . $filter_month;
        $item_rm = $this->crud->read("item_rm", [], ["id" => $item_rm_id]);
        $item_rm_number = trim($item_rm->number);
        $ospo = $this->dummy->query("SELECT trim(z.assy_no) as product_no, COALESCE(SUM(z.total), 0) as total
        FROM (
            select a.periode, a.wp, a.assy_no, c.bom_com_item, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, (SUM(a.qty) * c.bom_qty_perassy) as total
            from wip_trx_mpp a
            join wip_trx_wds b ON a.serial_mpp = b.serial_mpp
            join mst_bom c ON a.assy_no = c.bom_par_item
            where b.precut_sts = '1' and a.periode = '$period' and c.bom_com_item = '$item_rm_number' and TO_CHAR(a.wp_date, 'YYYY-MM-DD') = '$date'
            GROUP BY a.periode, a.wp, a.assy_no, c.bom_com_item, c.bom_qty_perassy, wp_date
        ) z
        GROUP BY z.assy_no
        ORDER BY z.assy_no asc");
        $records = $ospo->result_array();
        $balance = 0;
        foreach ($records as $record) {
            $balance += $record['total'];
        }
        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_rm_id" => "Grand Total", "total" => $balance]]])));
    }
}
