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

            $p_year = base64_decode($this->input->get("filter_year"));
            $p_month = base64_decode($this->input->get("filter_month"));
            $revision = base64_decode($this->input->get('filter_revision'));
            $plant = base64_decode($this->input->get("filter_plant"));

            $sqlWp = $this->db->query("SELECT DISTINCT wp FROM generate_item_calculation_2 WHERE p_year = '$p_year' and p_month = '$p_month' and revision = '$revision' and plant = '$plant' ORDER BY wp ASC");
            $dWp = $sqlWp->result_array();
            $data['wps'] = $dWp;

            $this->load->view('template/header', $data);
            $this->load->view('planning/material_calculation');
        } else {
            redirect('error_access');
        }
    }

    public function cutoffDate(){
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        echo date("Y-m-d", strtotime("-1 days", strtotime($year."-".$month."-01")));
    }

    public function readItemFamily(){
        $prodfam = $this->crud->query("SELECT * FROM item_familys WHERE `item_category_id` = 'C01'");
        echo json_encode($prodfam);
    }

    public function readProducts($pfm_id = ""){
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

            $this->db->select("a.*, SUM(a.qty) as total, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name");
            $this->db->from('generate_item_calculation_2 a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys b', 'c.item_family_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('b.number', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_id);
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('b.number');
            $this->db->order_by('c.number');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            //Receipt atau Scan In
            $sqlPor = $this->dummy->query("SELECT item_id, SUM(por_receiveqty) as qty
                FROM por
                WHERE por_date between '$start' and '$end'
                GROUP BY item_id
                ORDER BY item_id");
            $dPor = $sqlPor->result_array();

            $dPorMap = [];
            foreach ($dPor as $por) {
                $dPorMap[trim($por['item_id'])] = $por['qty'];
            }
            
            //Supply atau Scan out
            $sqlSupply = $this->dummy->query("SELECT a.item_id, SUM(abs(b.ith_qty)) as qty_out 
                FROM serial_detail_kanbanrm a 
                JOIN ith b ON a.kanbanrm_id = b.ith_docno and a.item_id = b.ith_item_id 
                WHERE b.ith_date between '$start' and '$end'
                GROUP BY a.item_id
                ORDER BY a.item_id");
            $dSupply = $sqlSupply->result_array();

            $dSupplyMap = [];
            foreach ($dSupply as $supply) {
                $dSupplyMap[trim($supply['item_id'])] = $supply['qty_out'];
            }

            $sqlWp = $this->db->query("SELECT wp FROM generate_item_calculation_2 WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' GROUP BY wp ORDER BY wp ASC");
            $dWp = $sqlWp->result_array();

            $sqlWp2 = $this->db->query("SELECT wp, item_rm_id, SUM(qty) as qty FROM generate_item_calculation_2 WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' GROUP BY item_rm_id, wp ORDER BY item_rm_id, wp asc");
            $dWp2 = $sqlWp2->result_array();
            $dWpMap = [];
            foreach ($dWp2 as $data2) {
                $dWpMap[$data2['item_rm_id']."_".$data2['wp']] = $data2['qty'];
            }

            $dataFinal = array();
            foreach ($records as $record) {
                $item_rm_id = $record['item_rm_id'];
                $item_rm_number = $record['item_rm_number'];

                $etaqty = ($dPorMap[$item_rm_number] ?? 0);
                $supplyqty = ($dSupplyMap[$item_rm_number] ?? 0);

                $day = 1;
                $balance = ($record['stock_hand'] + $etaqty - $supplyqty);
                $total_eta = 0;
                $arr = [];
                foreach ($dWp as $data) {
                    $wp = $data['wp'];
    
                    $date_qty = ($dWpMap[$item_rm_id."_".$wp] ?? 0);
                    $balance -= $date_qty;
                    
                    // $arr["eta_".$day] = (int) $etaqty;
                    $arr["date_".intval($data['wp'])] = round($date_qty);
                    // $arr["supply_".$day] = abs(round(@$supplyqty));
                    $arr["bal_".intval($data['wp'])] = round($balance);
                    
                    // $total_eta += (int) $etaqty;

                    $day++;
                }
                    
                if($balance > 0){
                    $free_stock = $balance;
                }else{
                    $free_stock = 0;
                }

                $data = array_merge($arr, [
                    "item_rm_id" => $item_rm_id,
                    "item_rm_number" => $record['item_rm_number'],
                    "item_rm_name" => $record['item_rm_name'],
                    "product_family_name" => $record['item_family_name'],
                    "stock_whs" => $record['stock_whs'],
                    "os_supply" => $record['os_supply'],
                    "os_wo" => $record['os_wo'],
                    "stock_hand" => $record['stock_hand'],
                    "total_incoming" => $etaqty,
                    "total_supply" => $supplyqty,
                    "total" => round($record['total']),
                    "total_bal" => round($balance),
                    "free_stock" => round($free_stock),
                    "lttp" => round($record['lttp']),
                    "lttp_bal" => (round($record['total']) - round($record['lttp'])),
                    "next_month_need1" => round($record['need_2'] / 4),
                    "next_month_need2" => round($record['need_2'] / 4),
                    "next_month_need3" => round($record['need_2'] / 4),
                    "next_month_need4" => round($record['need_2'] / 4),
                    "next_month_need5" => round($record['need_3'] / 4),
                    "next_month_need6" => round($record['need_3'] / 4),
                    "next_month_need7" => round($record['need_3'] / 4),
                    "next_month_need8" => round($record['need_3'] / 4),
                    "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                    "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                    "next_month_bal5" => ($balance - round($record['need_3'] / 4)),
                    "next_month_bal6" => ($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4)),
                    "next_month_bal7" => ($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4)),
                    "next_month_bal8" => ($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4)),
                    "balance_wip" => $record['balance_wip'],
                    "total_whs" => ($record['stock_whs'] + $record['balance_wip']),
                    // "os_po" => @$ospoRow->balance,
                    "os_po" => $record['os_po']
                ]);

                if($filter_status == "OK"){
                    if(round($free_stock) > 0){
                        $dataFinal[] = $data;
                    }
                }elseif($filter_status == "SHORTAGE"){
                    if(round($free_stock) == 0){
                        $dataFinal[] = $data;
                    }
                }else{
                    $dataFinal[] = $data;
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function getData(){
        $this->dummy = $this->load->database('export', TRUE);

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        // $filter_date = base64_decode($this->input->get('filter_date'));
        $filter_date = date("Y-m-t", strtotime("-1 month", strtotime($filter_year . "-" . $filter_month . "-01")));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $start = date("Y-m-01", strtotime($filter_year . "-" . $filter_month . "-01"));
        $period = $filter_year . $filter_month;

        $where_item_family = "";
        $where_item_id = "";
        $arrdelitemfam = [];
        $arrdelitem = [];
        if($filter_item_family != ""){
            $where_item_family = "and d.pfm_id = '$filter_item_family'";
            $arrdelitemfam = ["item_family_id" => $filter_item_family];
        }
        
        if($filter_item_id != ""){
            $item_rm = $this->crud->read("item_rm", [], ["id" => $filter_item_id]);
            $item_rm_number = trim($item_rm->number);
            $where_item_id = "and c.bom_com_item = '$item_rm_number'";
            $arrdelitem = ["item_rm_id" => $filter_item_id];
        }

        $generateMrps = $this->crud->reads("generate_mrp_finals", ["item_rm_id" => $filter_item_id], ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "plant" => $filter_plant]);
        $dwipMap = [];
        $dSupplyMap = [];
        // $dStockMap = [];
        $dNeedMap = [];
        $dNeed2Map = [];
        $dNeed3Map = [];
        $dWoMap = [];
        foreach ($generateMrps as $mrp) {
            $dwipMap[$mrp->item_rm_id] = $mrp->qty_wip;
            $dSupplyMap[$mrp->item_rm_id] = $mrp->qty_supply;
            // $dStockMap[$mrp->item_rm_id] = $mrp->qty_rm;
            $dNeedMap[$mrp->item_rm_id] = $mrp->need_1;
            $dNeed2Map[$mrp->item_rm_id] = $mrp->need_2;
            $dNeed3Map[$mrp->item_rm_id] = $mrp->need_3;
            $dWoMap[$mrp->item_rm_id] = $mrp->qty_wo;
        }

        $dStock = $this->mrp->stock($filter_date, $filter_plant);
        $dStockMap = [];
        foreach ($dStock as $stock) {
            $dStockMap[$stock['item']] = $stock['qty'];
        }

        // $data = $this->mrp->material_calculation($filter_plant, $filter_year.$filter_month);
        $this->dummy = $this->load->database('export', TRUE);
        $sqlMpp = $this->dummy->query("SELECT trim(z.bom_com_item) as item_id, z.final_wp, z.wp_date, COALESCE(ROUND(SUM(z.total)), 0) as total from (
            select a.periode, REGEXP_REPLACE(a.wp, '([0-9]{2})[^0-9]([0-9]{2})', '\\1/\\2') as final_wp, a.assy_no, c.bom_com_item, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, (SUM(a.qty) * c.bom_qty_perassy) as total
            from wip_trx_mpp a
            -- join wip_trx_wds b ON a.serial_mpp = b.serial_mpp
            join mst_bom c ON a.assy_no = c.bom_par_item
            join mst_item d ON c.bom_com_item = d.item_id
            where a.periode = '$period' $where_item_family $where_item_id
            -- where b.precut_sts = '1' and a.periode = '$period' $where_item_family $where_item_id
            GROUP BY a.periode, final_wp, a.assy_no, c.bom_com_item, c.bom_qty_perassy, wp_date
            ORDER BY a.wp_date asc) z
            GROUP BY z.bom_com_item, z.final_wp, z.wp_date
            ORDER BY z.bom_com_item, z.final_wp, z.wp_date asc");
        $dataMpp = $sqlMpp->result_array();

        foreach ($dataMpp as $data) {
            $item_rm = $this->crud->read('item_rm', [], ["number" => trim($data['item_id'])]);
            $item_family = $this->crud->read('item_familys', [], ["id" => @$item_rm->item_family_id]);
            $dataStock = ($dStockMap[trim($data['item_id'])] ?? 0);
            $qty_wip = ($dwipMap[@$item_rm->id] ?? 0);
            $os_supply = ($dSupplyMap[@$item_rm->id] ?? 0);
            $os_wo = ($dSupplyMap[@$item_rm->id] ?? 0);
            $lttp = ($dNeedMap[@$item_rm->id] ?? 0);
            $need_2 = ($dNeed2Map[@$item_rm->id] ?? 0);
            $need_3 = ($dNeed3Map[@$item_rm->id] ?? 0);

            $stock_hand = round(@$dataStock + $qty_wip - $os_supply);

            if (@$item_rm->id != "") {
                $arr[] = array(
                    "item_rm_id" => @$item_rm->id,
                    "item_family_id" => @$item_family->number,
                    "plant" => $filter_plant,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "os_supply" => $os_supply,
                    "os_wo" => $os_wo,
                    "balance_wip" => $qty_wip,
                    "stock_whs" => $dataStock,
                    "stock_hand" => $stock_hand,
                    "trans_date" => $data['wp_date'],
                    "wp" => $data['final_wp'],
                    "qty" => $data['total'],
                    "lttp" => $lttp,
                    "need_2" => $need_2,
                    "need_3" => $need_3,
                );
            }
        }

        $this->db->delete('generate_item_calculation_2', array_merge(["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "plant" => $filter_plant], $arrdelitemfam, $arrdelitem));
        echo json_encode($arr);
    }

    public function create(){
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
                "cutoff" => $cutoff
            ));

            if ($this->db->insert('generate_item_calculation_2', $postFinal)) {
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
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $filter_status = $this->input->get('filter_status');
        $filter_date = base64_decode($this->input->get('filter_date'));
        $end = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $start = date("Y-m-01", strtotime($filter_year . "-" . $filter_month . "-01"));

        $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $period_start = $filter_year."-".$filter_month."-01";
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-01"));
        
        $next_month = date("F", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));
        $next_month2 = date("F", strtotime("+2 month", strtotime($filter_year."-".$filter_month."-01")));

        $this->db->select("a.*, SUM(a.qty) as total, c.number as item_rm_number, b.name as item_rm_name, b.name as item_family_name");
        $this->db->from('generate_item_calculation_2 a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys b', 'c.item_family_id = b.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('b.number', $filter_item_family);
        $this->db->like('a.item_rm_id', $filter_item_id);
        $this->db->group_by('a.item_rm_id');
        $this->db->order_by('b.number');
        $this->db->order_by('c.number');
        $records = $this->db->get()->result_array();

        //Receipt atau Scan In
        $sqlPor = $this->dummy->query("SELECT item_id, SUM(por_receiveqty) as qty
            FROM por
            WHERE por_date between '$start' and '$end'
            GROUP BY item_id
            ORDER BY item_id");
        $dPor = $sqlPor->result_array();

        $dPorMap = [];
        foreach ($dPor as $por) {
            $dPorMap[$por['item_id']] = $por['qty'];
        }
        
        //Supply atau Scan out
        $sqlSupply = $this->dummy->query("SELECT a.item_id, SUM(abs(b.ith_qty)) as qty_out 
            FROM serial_detail_kanbanrm a 
            JOIN ith b ON a.kanbanrm_id = b.ith_docno and a.item_id = b.ith_item_id 
            WHERE b.ith_date between '$start' and '$end'
            GROUP BY a.item_id
            ORDER BY a.item_id");
        $dSupply = $sqlSupply->result_array();

        $dSupplyMap = [];
        foreach ($dSupply as $supply) {
            $dSupplyMap[$supply['item_id']] = $supply['qty_out'];
        }

        //Setting Header
        $cut = '<tr>';
        $date = '<tr>';

        $sqlWp = $this->db->query("SELECT DISTINCT wp FROM generate_item_calculation_2 WHERE p_year = '$filter_year' and p_month = '$filter_month' and revision = '$filter_revision' and plant = '$filter_plant' ORDER BY wp ASC");
        $dWps = $sqlWp->result_array();
        $colspan = 0;
        foreach ($dWps as $dWp) {
            $cut .= '<th width="50" colspan="2" style="text-align:center;">(WP '.$dWp['wp'].')</th>';
            $date .= '<th width="50" style="text-align:center;">NEED</th>
                      <th width="50" style="text-align:center;">BAL</th>';
            $colspan++;
        }

        $date .= '  <th width="50" style="text-align:center;">WEEK 1</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 2</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 3</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 4</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 1</th>
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

        $sqlWp2 = $this->db->query("SELECT wp, item_rm_id, SUM(qty) as qty FROM generate_item_calculation_2 WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' GROUP BY item_rm_id, wp ORDER BY item_rm_id, wp asc");
        $dWp2 = $sqlWp2->result_array();
        $dWpMap = [];
        foreach ($dWp2 as $data2) {
            $dWpMap[$data2['item_rm_id']."_".$data2['wp']] = $data2['qty'];
        }

        $content2 = "";
        $content = "";
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record['item_rm_id'];
            $item_rm_number = $record['item_rm_number'];

            $etaqty = ($dPorMap[$item_rm_number] ?? 0);
            $supplyqty = ($dSupplyMap[$item_rm_number] ?? 0);

            $day = 1;
            $balance = ($record['stock_hand'] + $etaqty - $supplyqty);
            $total_eta = 0;
            $contentisi = "";
            foreach ($dWps as $data) {
                $wp = $data['wp'];
                $date_qty = ($dWpMap[$item_rm_id."_".$wp] ?? 0);
                $balance -= $date_qty;
                
                // $arr["eta_".$day] = (int) $etaqty;
                $arr["date_".intval($data['wp'])] = round($date_qty);
                // $arr["supply_".$day] = abs(round(@$supplyqty));
                $arr["bal_".intval($data['wp'])] = round($balance);
                
                // $total_eta += (int) $etaqty;

                $contentisi .= "<td style='text-align:right;'>".round($date_qty)."</td>
                                <td style='text-align:right;'>".round($balance)."</td>";

                $day++;
            }
                
            if($balance > 0){
                $free_stock = $balance;
            }else{
                $free_stock = 0;
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
                            <td style='text-align:right;'>" . round($etaqty) . "</td>
                            <td style='text-align:right;'>" . round($supplyqty) . "</td>
                            <td style='text-align:right;'>" . round($record['total']) . "</td>
                            <td style='text-align:right;'>" . round($record['lttp']) . "</td>
                            <td style='text-align:right;'>" . round(($record['total'] - $record['lttp'])) . "</td>";
            $content .= $contentisi;
            $content .= "   <td style='text-align:right;'>" . round($free_stock) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_3'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_3'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_3'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_3'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_3'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4) - round($record['need_3'] / 4)) . "</td>
                        </tr>";

            // if($filter_status == "OK"){
            //     if($free_stock > 0){
            //         $content2 .= $content;
            //         $no++;
            //     }
            // }elseif($filter_status == "SHORTAGE"){
            //     if($free_stock == 0){
            //         $content2 .= $content;
            //         $no++;
            //     }
            // }else{
            //     $content2 .= $content;
                $no++;
            // }
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
                <th style="text-align:center;" width="150" rowspan="3">PART NO</th>
                <th style="text-align:center;" width="80" rowspan="3">DESCRIPTION</th>
                <th style="text-align:center;" width="80" rowspan="3">PRODUCT FAMILY</th>
                <th style="text-align:center;" width="50" rowspan="3">BALANCE WIP</th>
                <th style="text-align:center;" width="50" rowspan="3">STOCK WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">OS SUPPLY</th>
                <th style="text-align:center;" width="50" rowspan="3">ON HAND<br>STOCK</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL<br>INCOMING</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL<br>SUPPPLY</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(MPP)</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(LTPP)</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED<br>(LTPP VS MPP)</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan * 2).'">QUANTITY NEED FROM WORKORDER</th>
                <th style="text-align:center;" width="50" rowspan="3">FREE STOCK</th>
                <th style="text-align:center;" width="50" colspan="8" rowspan="2">'.$next_month.'</th>
                <th style="text-align:center;" width="50" colspan="8" rowspan="2">'.$next_month2.'</th>
            </tr>' . $cut . $date . $content;
        echo $html;
    }

    public function printBalance(){
        $this->dummy = $this->load->database('export', TRUE);

        $item_rm_id = base64_decode($this->input->get("item_rm_id"));
        $filter_month = base64_decode($this->input->get("filter_month"));
        $filter_year = base64_decode($this->input->get("filter_year"));
        $filter_revision = base64_decode($this->input->get("filter_revision"));
        $period = $filter_year.$filter_month;
        $item_rm = $this->crud->read("item_rm", [], ["id" => $item_rm_id]);
        $item_rm_number = trim($item_rm->number);
        
        $ospo = $this->dummy->query("SELECT 
	        trim(z.assy_no) as product_no,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '01/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '02/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '03/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '04/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '05/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '06/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '07/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '08/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '09/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '10/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '11/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '12/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '13/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '14/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '15/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '16/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '17/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '18/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '19/$filter_month'), 0), 2) +
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '20/$filter_month'), 0), 2) as total,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '01/$filter_month'), 0), 2) AS wp_1,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '02/$filter_month'), 0), 2) AS wp_2,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '03/$filter_month'), 0), 2) AS wp_3,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '04/$filter_month'), 0), 2) AS wp_4,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '05/$filter_month'), 0), 2) AS wp_5,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '06/$filter_month'), 0), 2) AS wp_6,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '07/$filter_month'), 0), 2) AS wp_7,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '08/$filter_month'), 0), 2) AS wp_8,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '09/$filter_month'), 0), 2) AS wp_9,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '10/$filter_month'), 0), 2) AS wp_10,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '11/$filter_month'), 0), 2) AS wp_11,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '12/$filter_month'), 0), 2) AS wp_12,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '13/$filter_month'), 0), 2) AS wp_13,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '14/$filter_month'), 0), 2) AS wp_14,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '15/$filter_month'), 0), 2) AS wp_15,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '16/$filter_month'), 0), 2) AS wp_16,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '17/$filter_month'), 0), 2) AS wp_17,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '18/$filter_month'), 0), 2) AS wp_18,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '19/$filter_month'), 0), 2) AS wp_19,
            ROUND(COALESCE(SUM(z.total) FILTER (WHERE z.final_wp = '20/$filter_month'), 0), 2) AS wp_20
        FROM (
            select a.periode, a.wp, REGEXP_REPLACE(a.wp, '([0-9]{2})[^0-9]([0-9]{2})', '\\1/\\2') as final_wp, a.assy_no, c.bom_com_item, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, (SUM(a.qty) * c.bom_qty_perassy) as total
            from wip_trx_mpp a
            -- join wip_trx_wds b ON a.serial_mpp = b.serial_mpp
            join mst_bom c ON a.assy_no = c.bom_par_item
            -- where b.precut_sts = '1' and a.periode = '$period' and c.bom_com_item = '$item_rm_number'
            where a.periode = '$period' and c.bom_com_item = '$item_rm_number'
            GROUP BY a.periode, a.wp, a.assy_no, c.bom_com_item, c.bom_qty_perassy, wp_date
        ) z
        GROUP BY z.assy_no
        ORDER BY z.assy_no asc");
        $records = $ospo->result_array();

        $balance = 0;
        foreach ($records as $record) {
            $balance += round($record['total'], 2);
        }

        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_rm_id" => "Grand Total", "total" => $balance]]])));
    }

    public function testingQuery(){
        $this->dummy = $this->load->database('export', TRUE);

        $ospo = $this->dummy->query("SELECT a.periode, a.wp, REGEXP_REPLACE(a.wp, '([0-9]{2})[^0-9]([0-9]{2})', '\\1/\\2') as final_wp, a.assy_no, c.bom_com_item, TO_CHAR(a.wp_date, 'YYYY-MM-DD') as wp_date, (SUM(a.qty) * c.bom_qty_perassy) as total
            from wip_trx_mpp a
            join mst_bom c ON a.assy_no = c.bom_par_item
            where a.periode = '202602' and c.bom_com_item = '14789 0.75 4C'
            GROUP BY a.periode, a.wp, a.assy_no, c.bom_com_item, c.bom_qty_perassy, wp_date");
        $records = $ospo->result_array();
        
        // foreach ($records as $record) {
        //     echo $record['wp'];
        // }
        die(json_encode($records));
    }
}
