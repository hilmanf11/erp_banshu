<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requests extends CI_Controller
{
    public $menu_id;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        $this->load->model('mrp');

        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_requests');
        } else {
            redirect('error_access');
        }
    }

    public function reads(){
        $query = $this->db->query("SELECT DISTINCT request_no FROM purchase_requests ORDER BY request_no DESC LIMIT 1000");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readprodfam($supplier_id){
        $supplier_id = base64_decode($supplier_id);
        $query = $this->db->query("SELECT DISTINCT a.id, a.number, a.name 
            FROM item_familys a 
            JOIN item_rm b ON a.id = b.item_family_id
            JOIN supplier_items c ON b.id = c.item_rm_id
            WHERE a.item_category_id IN ('C01') and c.supplier_id = '$supplier_id'");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readParts($supplier_id, $item_famiy_id, $plant){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $item_famiy_id = base64_decode($item_famiy_id);
        $plant = base64_decode($plant);
        
        $query = $this->db->query("SELECT b.id, b.number, b.name, a.mpq, a.moq, a.share_order, a.leadtime
            FROM supplier_items a 
            JOIN item_rm b ON a.item_rm_id = b.id
            WHERE a.supplier_id = '$supplier_id' and b.item_family_id = '$item_famiy_id' and a.plant = '$plant' and (a.approved_to = '' or a.approved_to is null) and (b.number like '%$post%' or b.id like '%$post%')
            ORDER BY b.number ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readPr($supplier_id, $item_family_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $item_family_id = base64_decode($item_family_id);
        
        $query = $this->db->query("SELECT a.request_no, b.name as supplier_name
            FROM purchase_requests a 
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.supplier_id = '$supplier_id' and a.item_family_id = '$item_family_id' and a.request_no like '%$post%'
            GROUP BY a.request_no
            ORDER BY a.request_no ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    function forecast($item_rm_id,$year,$month,$revision){
        $period = date("Y-m-d", strtotime($year ."-". $month ."-01"));
        $periods = date("mY", strtotime($period));

        $q_forecast = $this->db->query("SELECT *
            FROM generate_mrp_finals 
            WHERE CONCAT(p_month, p_year) = '$periods' and revision = '$revision'
            AND item_rm_id ='$item_rm_id'
            GROUP BY item_rm_id");
        $forecast = $q_forecast->result_array();
        
        return array(
            "fc1" => @$forecast[0]['need_2'],
            "fc2" => @$forecast[0]['need_3'],
            "fc3" => @$forecast[0]['need_4'],
            "fc4" => @$forecast[0]['need_5'],
            "fc5" => @$forecast[0]['need_6'],
            "fc6" => @$forecast[0]['avg_need'],
        );
    }

    function history_po($plant,$year,$month,$item_rm_id){
        $period = date("Y-m-d", strtotime($year ."-". $month ."-01"));
        $periodmin1 = date("Ym", strtotime("-1 month", strtotime($period)));
        $periodmin2 = date("Ym", strtotime("-2 month", strtotime($period)));
        $periodmin3 = date("Ym", strtotime("-3 month", strtotime($period)));

        $q_po = $this->db->query("SELECT CONCAT(p_year, '', p_month) as period, item_rm_id, SUM(qty) as qty 
            FROM purchase_orders 
            WHERE CONCAT(p_year, '', p_month) IN ('$periodmin1','$periodmin2','$periodmin3') and plant = '$plant'
            AND item_rm_id ='$item_rm_id'
            GROUP BY item_rm_id, CONCAT(p_year, '', p_month)");
        $dpo = $q_po->result_array();
        
        return array(
            "po1" => @$dpo[0]['qty'],
            "po2" => @$dpo[1]['qty'],
            "po3" => @$dpo[2]['qty']
        );
    }

    public function readMonths()
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');

        $start = strtotime($year . "-" . $month . "-01");
        $begin =  strtotime(date('Y-m-d', strtotime('+6 month', $start)));
        $end =  strtotime(date('Y-m-d', strtotime('-3 month', $start)));

        $monthName = array();
        while ($end < $begin) {
            $monthName[] = date('M Y', $end);
            $end = strtotime("+1 month", $end);
        }

        die(json_encode($monthName));
    }

    public function purchaseRequestNo($code, $add = ""){
        if($add != ""){
            $code = base64_decode($code);
        }

        $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;
        
        if ($kode == NULL) {
            $autoID = sprintf("%02s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -2);
            $urutan++;
            $autoID	= sprintf("%02s", $urutan);
        }

        if($add == ""){
            return $code.$autoID;
        }else{
            echo $code.$autoID;
        }
    }

    // public function purchaseRequestNo($id_menu, $add = ""){
    //     $datenow = date("Y-m-d");
    //     $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '$id_menu' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
    //     $row_menu = $sql_menu->row();

    //     if(@$row_menu->number == ""){
    //         //
    //     }else{
    //         $number = @$row_menu->number;
    //         $yearnow = date("y", strtotime($row_menu->start_date));
    //         $yearend = date("y", strtotime($row_menu->end_date));

    //         $whereDate = $number.$yearnow.$yearend."1";

    //         $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$whereDate%'");
    //         $rowID = $sqlGetID->row();
    //         $kode = $rowID->kode;
            
    //         if ($kode == NULL) {
    //             $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
    //             $autoNo = $whereDate.$autoID;
    //         } else {
    //             $urutan = (int) substr($kode, -6);
    //             $urutan++;
    //             $autoID	= sprintf("%0".$row_menu->sequence."s", $urutan);
    //             $autoNo = $whereDate.$autoID;
    //         }

    //         if($add == ""){
    //             return $autoNo;
    //         }else{
    //             echo $autoNo;
    //         }
    //     }
    // }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $id_menu = base64_decode($this->input->get('id_menu'));

            if($filter_item_family != ""){
                if($filter_item_rm != ""){
                    $array_pfm = array("item_family_id" => $filter_item_family, "item_rm_id" => $filter_item_rm);
                }else{
                    $array_pfm = array("item_family_id" => $filter_item_family);
                }
            }else{
                $array_pfm = array();
            }

            // $this->crud->delete("purchase_requests", array_merge(["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "plant" => $filter_plant], $array_pfm));

            //Select Query
            $this->db->select('a.p_month, a.p_year, a.revision, a.cutoff, a.plant, a.item_rm_id, a.item_rm_alias, b.number as item_rm_number, c.id as supplier_id, f.id as item_family_id, 
                c.mpq, c.moq, c.leadtime, a.need_1, a.balance_1, a.balance_2, a.balance_3, a.balance_4, a.balance_5, a.balance_6, 
                a.avg_balance, a.total_need, a.ito, a.purchase_order, a.item_rm_id, b.item_family_id, f.number as item_family_number, c.share_order, 
                c.supplier_id, e.number as supplier_no, g.type as supplier_type, h.id as status_pr');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM b.number) = ia.number");
            $this->db->join('supplier_items c', 'ia.id = c.item_rm_id and a.plant = c.plant and c.share_order > 0');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            $this->db->join('item_familys f', 'b.item_family_id = f.id');
            $this->db->join('makers g', 'c.maker_id = g.id', 'left');
            $this->db->join("(select * from purchase_requests WHERE status_data IN ('GENERATE','EDIT')) h", "a.supplier_id = h.supplier_id and a.item_rm_id = h.item_rm_id and a.plant = h.plant and a.p_month = h.p_month and a.p_year = h.p_year and a.revision = h.revision", 'left');
            $this->db->where("a.item_rm_id NOT IN (SELECT item_rm_alias FROM generate_mrp_finals WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' and plant='$filter_plant' GROUP BY item_rm_alias)");
            $this->db->where("(a.purchase_order > 0 or a.item_rm_alias != '')");
            $this->db->where("(c.mpq > 0 and c.moq > 0 and c.leadtime > 0)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.plant', $filter_plant);
            $this->db->where('h.id is null');
            if($filter_supplier != ""){
                $this->db->where('a.supplier_id', $filter_supplier);
            }
            if($filter_item_family != ""){
                $this->db->where('b.item_family_id', $filter_item_family);
            }
            if($filter_item_rm != ""){
                $this->db->where('b.id', $filter_item_rm);
            }
            $this->db->group_by('c.supplier_id');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('e.name');
            $this->db->order_by('b.item_family_id');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            $end_supplier = "";
            $end_product_family = "";
            $end_request_no = "";
            $no = 0;
            foreach ($mpsDetails as $generate) {
                $supplier_no = $generate['supplier_no'];
                $item_rm_id = $generate['item_rm_id'];

                $date_start = strtotime($filter_year."-".$filter_month."-01");
                $date_finish = strtotime(date("Y-m-d", strtotime($generate['cutoff'])));

                if($date_start > $date_finish){
                    $cutoffDate = date("j", strtotime($filter_year."-".$filter_month."-01"));
                }else{
                    $cutoffDate = date("j", strtotime(date("Y-m-d", strtotime($generate['cutoff']))));
                }

                if($generate['item_rm_alias'] != "" && $generate['need_1'] > 0){
                    $this->db->select('a.*, CONCAT(a.p_year, a.p_month) as period, b.number as item_rm_number, b.name as item_rm_name, e.id as supplier_number, e.name as supplier_name, c.name as product_family, d.class, d.composition, d.safety');
                    $this->db->from('generate_mrp_finals a');
                    $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                    $this->db->join('item_familys c', 'b.item_family_id = c.id');
                    $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision and a.plant = d.plant', 'left');
                    $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
                    $this->db->where('a.plant', $filter_plant);
                    $this->db->where('a.p_month', $filter_month);
                    $this->db->where('a.p_year', $filter_year);
                    $this->db->where('a.revision', $filter_revision);
                    $this->db->where('a.item_rm_id', $generate['item_rm_alias']);
                    $this->db->group_by('a.item_rm_id');
                    $this->db->group_by('a.supplier_id');
                    $this->db->order_by('c.name', 'asc');
                    $this->db->order_by('d.class', 'asc');
                    $this->db->order_by('b.number', 'asc');
                    $records2 = $this->db->get()->result_array();

                    $balance_1 = 0;
                    $balance_2 = 0;
                    $balance_3 = 0;
                    $balance_4 = 0;
                    $balance_5 = 0;
                    $balance_6 = 0;
                    $avg_balance = 0;
                    foreach ($records2 as $record2) {
                        $balance_1 += round($record2['balance_1']);
                        $balance_2 += round($record2['balance_2']);
                        $balance_3 += round($record2['balance_3']);
                        $balance_4 += round($record2['balance_4']);
                        $balance_5 += round($record2['balance_5']);
                        $balance_6 += round($record2['balance_6']);
                        $avg_balance += round($record2['avg_balance']);
                    }

                    if(($generate['leadtime'] + $cutoffDate - 1) <= 30){
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_1'] + $balance_1);
                        }else{
                            $total_need = round($generate['balance_2'] + $balance_2);
                        }
                    }elseif(($generate['leadtime'] + $cutoffDate - 1) > 30 && ($generate['leadtime'] + $cutoffDate - 1) <= 60){
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_2'] + $balance_2);
                        }else{
                            $total_need = round($generate['balance_3'] + $balance_3);
                        }
                    }elseif(($generate['leadtime'] + $cutoffDate - 1) > 60 && ($generate['leadtime'] + $cutoffDate - 1) <= 90){
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_3'] + $balance_3);
                        }else{
                            $total_need = round($generate['balance_4'] + $balance_4);
                        }
                    }elseif(($generate['leadtime'] + $cutoffDate - 1) > 90 && ($generate['leadtime'] + $cutoffDate - 1) <= 120){
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_4'] + $balance_4);
                        }else{
                            $total_need = round($generate['balance_5'] + $balance_5);
                        }
                    }elseif(($generate['leadtime'] + $cutoffDate - 1) > 120 && ($generate['leadtime'] + $cutoffDate - 1) <= 150){
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_5'] + $balance_5);
                        }else{
                            $total_need = round($generate['balance_6'] + $balance_6);
                        }
                    }else{
                        if($generate['supplier_type'] == "LOCAL"){
                            $total_need = round($generate['balance_6'] + $balance_6);
                        }else{
                            $total_need = round($generate['avg_balance'] + $avg_balance);
                        }
                    }

                    if($total_need > 0){
                        $total_need = 0;
                    }
                }else{
                    $total_need = $generate['total_need'];
                }


                if($supplier_no == $end_supplier && $generate['item_family_id'] == $end_product_family){
                    $request_no = $end_request_no;
                    $no++;
                }else{
                    $request_no = $this->purchaseRequestNo("PR".@trim($supplier_no.date("Ym")). "-" .trim($generate['item_family_number']));
                    // $request_no = $this->purchaseRequestNo($id_menu);
                    $no = 1;
                }

                // $fcmin = $this->mrp->history_po($generate['plant'], $generate['p_year'].$generate['p_month'], $generate['item_rm_number']);
                // $forecast_min = $this->forecast($item_rm_id, $filter_month, $filter_year, @$forecast_row_min->revision);
                $fcplus = $this->forecast($generate['item_rm_id'], $generate['p_year'], $generate['p_month'], @$generate['revision']);
                
                if(abs($total_need) > 0 && $generate['moq'] > 0){
                    $final = abs($total_need);
                    if($final > $generate['moq']){
                        $purchase_order = (ceil($final / $generate['mpq']) * $generate['mpq']);
                    }else{
                        $purchase_order = (ceil($final / $generate['moq']) * $generate['moq']);
                    }
                }else{
                    $purchase_order = 0;
                }

                $split = ($purchase_order * ($generate['share_order'] / 100));
                if($split > 0){
                    $purchase_request = (ceil($split / $generate['moq']) * $generate['moq']);
                }else{
                    $purchase_request = 0;
                }

                if($purchase_order > 0){
                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "plant" => $filter_plant,
                        "item_rm_id" => $generate['item_rm_id'],
                        "supplier_id" => $generate['supplier_id'],
                        "item_family_id" => $generate['item_family_id'],
                        "request_no" => $request_no,
                        "request_date" => date("Y-m-d"),
                        "request_name" => $this->session->name,
                        "expected_date" => date("Y-m-d"),
                        "need" => abs($total_need),
                        "mrp" => $purchase_order,
                        "qty" => $purchase_request,
                        "ito" => $generate['ito'],
                        "forecast_m1" => @$fcplus['fc1'],
                        "forecast_m2" => @$fcplus['fc2'],
                        "forecast_m3" => @$fcplus['fc3'],
                        "forecast_plus_1" => @$fcplus['fc4'],
                        "forecast_plus_2" => @$fcplus['fc5'],
                        "forecast_plus_3" => @$fcplus['fc6'],
                        "forecast_avg" => round((@$fcplus['fc1'] + @$fcplus['fc2'] + @$fcplus['fc3'] + @$fcplus['fc4'] + @$fcplus['fc5'] + @$fcplus['fc6']) / 6),
                        "status_data" => "GENERATE",
                    );
                }

                $end_supplier = $supplier_no;
                $end_product_family = $generate['item_family_id'];
                $end_request_no = $request_no;
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_request_no = base64_decode($this->input->get('filter_request_no'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name, c.name as item_family_name, SUM(a.qty) as total_pr, SUM(a.mrp) as total_mrp, COALESCE(e.total, 0) as total_po, COUNT(a.id) as total_data');
            $this->db->from("(select * from purchase_requests where qty > 0 and flag = 'OK' order by approved_to desc) a");
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            // $this->db->join("(select request_no, COUNT(id) as total FROM purchase_requests WHERE approved_to = '' GROUP BY request_no) d", 'a.request_no = d.request_no', 'left');
            $this->db->join("(select request_no, COUNT(id) as total FROM purchase_orders GROUP BY request_no) e", 'a.request_no = e.request_no', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_item_family != ""){
                $this->db->like('a.item_family_id', $filter_item_family);
            }
            if($filter_item_rm != ""){
                $this->db->like('a.item_rm_id', $filter_item_rm);
            }
            if($filter_supplier != ""){
                $this->db->like('a.supplier_id', $filter_supplier);
            }
            if($filter_request_no != ""){
                $this->db->like('a.request_no', $filter_request_no);
            }
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'ASC');
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

    public function datatableDetails($request_no, $revision)
    {
        $request_no = base64_decode($request_no);
        $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
        $filter_month = trim(base64_decode($this->input->get('filter_month')));
        $filter_year = trim(base64_decode($this->input->get('filter_year')));

        $ending = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $po_date_1 = date('Y-m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_2 = date('Y-m', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_3 = date('Y-m', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.*, d.number as item_rm_number, d.name as item_rm_name, d.uom, b.name as supplier_name, c.share_order, c.moq, c.mpq, c.leadtime, c.item_supplier, e.po_no');
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm d', 'a.item_rm_id = d.id');
        $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM d.number) = ia.number");
        $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and ia.id = c.item_rm_id and a.plant = c.plant');
        $this->db->join('purchase_orders e', 'a.request_no = e.request_no and a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id', 'left');
        // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.revision', $revision);
        $this->db->where('a.request_no', $request_no);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->order_by('d.number', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $productTotals = [];
        foreach ($records as $record) {
            $product = $record['item_rm_id'];
            $shareOrder = $record['share_order'];
            $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
        }

        $finals = array();
        $total_m1 = 0;
        $total_m2 = 0;
        $total_m3 = 0;
        $total_avg = 0;
        $total_prodplan_1 = 0;
        $total_prodplan_2 = 0;
        $total_prodplan_3 = 0;
        $total_forecast_m1 = 0;
        $total_forecast_m2 = 0;
        $total_forecast_m3 = 0;
        $total_forecast_plus_1 = 0;
        $total_forecast_plus_2 = 0;
        $total_forecast_plus_3 = 0;
        $total_avg_forecast = 0;
        $total_purchase_request = 0;
        $total_purchase_order = 0;
        $total_need = 0;
        $total_mrp = 0;
        foreach ($records as $data) {
            $item_id = $data['item_rm_id'];
            // $leadtime = date('Y-m-01', strtotime('+'.$data['leadtime'].' days', strtotime($ending)));
            $leadtime = date('Y-m-01', strtotime($ending));
            $hpo = $this->history_po($data['plant'], $data['p_year'],$data['p_month'], $item_id);
            
            $period = date("Y-m-d", strtotime($filter_year ."-". $filter_month ."-01"));
            $periodmin1 = date("mY", strtotime("-1 month", strtotime($period)));
            $periodmin2 = date("mY", strtotime("-2 month", strtotime($period)));
            $periodmin3 = date("mY", strtotime("-3 month", strtotime($period)));

            $q_forecast = $this->db->query("SELECT item_rm_id, SUM(prod_plan_1) as qty 
                FROM mrp_ltpp 
                WHERE CONCAT(p_month, p_year) IN ('$periodmin1','$periodmin2','$periodmin3')
                AND item_rm_id ='$item_id'
                GROUP BY item_rm_id, p_month");
            $forecast = $q_forecast->result_array();

            $m1 = date('mY', strtotime($leadtime));

            $qmps1 = $this->db->query("SELECT * FROM mrp_ltpp WHERE CONCAT(p_month, p_year) = '$m1' and item_rm_id = '$item_id' and revision = '$revision'");
            $rpms1 = $qmps1->row();

            $class = ($productTotals[$data['item_rm_id']] >= 100) ? 'red' : '';
            $finals[] = array_merge($data, [
                "color" => $class,
                "m1" => empty(@$hpo['po1']) ? 0 : @$hpo['po1'],
                "m2" => empty(@$hpo['po2']) ? 0 : @$hpo['po2'],
                "m3" => empty(@$hpo['po3']) ? 0 : @$hpo['po3'],
                "avg" => empty((@$hpo['po1'] + @$hpo['po2'] + @$hpo['po3'])) ? 0 : ((@$hpo['po1'] + @$hpo['po2'] + @$hpo['po3']) / 3),
                "prodplan_1" => @$forecast[2]['qty'],
                "prodplan_2" => @$forecast[1]['qty'],
                "prodplan_3" => @$forecast[0]['qty'],
                "avg_forecast" => round(($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3']) / 6)
            ]);

            $total_m1 += empty(@$rpo1->qty) ? 0 : @$rpo1->qty;
            $total_m2 += empty(@$rpo2->qty) ? 0 : @$rpo2->qty;
            $total_m3 += empty(@$rpo3->qty) ? 0 : @$rpo3->qty;
            $total_avg += empty((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty)) ? 0 : ((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty) / 3);
            $total_prodplan_1 += @$forecast[2]['qty'];
            $total_prodplan_2 += @$forecast[1]['qty'];
            $total_prodplan_3 += @$forecast[0]['qty'];
            $total_forecast_m1 += $data['forecast_m1'];
            $total_forecast_m2 += $data['forecast_m2'];
            $total_forecast_m3 += $data['forecast_m3'];
            $total_forecast_plus_1 += $data['forecast_plus_1'];
            $total_forecast_plus_2 += $data['forecast_plus_2'];
            $total_forecast_plus_3 += $data['forecast_plus_3'];
            $total_avg_forecast += (($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3']) / 6);
            $total_purchase_request += $data['qty'];
            $total_need += $data['need'];
            $total_mrp += $data['mrp'];
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
        'rows' => $finals, 
        'footer' => array([
                "item_rm_number" => "Grand Total",
                "qty" => $total_purchase_request,
                "need" => $total_need,
                "mrp" => $total_mrp,
                "m1" => $total_m1,
                "m2" => $total_m2,
                "m3" => $total_m3,
                "avg" => $total_avg,
                "prodplan_1" => $total_prodplan_1,
                "prodplan_2" => $total_prodplan_2,
                "prodplan_3" => $total_prodplan_3,
                "forecast_m1" => $total_forecast_m1,
                "forecast_m2" => $total_forecast_m2,
                "forecast_m3" => $total_forecast_m3,
                "forecast_plus_1" => $total_forecast_plus_1,
                "forecast_plus_2" => $total_forecast_plus_2,
                "forecast_plus_3" => $total_forecast_plus_3,
                "avg_forecast" => $total_avg_forecast
            ])
        ]);
        echo json_encode($result);
    }

    public function createAdds()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $fcplus = $this->forecast($post['item_rm_id'], $post['p_year'], $post['p_month'], @$post['revision']);
            $fc1 = empty($fcplus['fc1']) ? 0 : $fcplus['fc1'];
            $fc2 = empty($fcplus['fc2']) ? 0 : $fcplus['fc2'];
            $fc3 = empty($fcplus['fc3']) ? 0 : $fcplus['fc3'];
            $fc4 = empty($fcplus['fc4']) ? 0 : $fcplus['fc4'];
            $fc5 = empty($fcplus['fc5']) ? 0 : $fcplus['fc5'];
            $fc6 = empty($fcplus['fc6']) ? 0 : $fcplus['fc6'];

            $records = $this->crud->reads('purchase_requests', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id'],
                "supplier_id" => $post['supplier_id'],
                "status_data" => "MANUAL",
            ]);

            if(count($records) > 0){
                show_error("Duplicate Item ID");
            }else{
                $send = $this->crud->create('purchase_requests', array_merge($post, ["forecast_m1" => $fc1,
                    "forecast_m2" => @$fc2,
                    "forecast_m3" => @$fc3,
                    "forecast_plus_1" => @$fc4,
                    "forecast_plus_2" => @$fc5,
                    "forecast_plus_3" => @$fc6,
                    "forecast_avg" => round((@$fc1 + @$fc2 + @$fc3 + @$fc4 + @$fc5 + @$fc6) / 6)]), "logs");
                echo $send;
            }
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/documents/purchase_requests/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Size File Maximal 2 Mb.']);
                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
                }

                // Pastikan tidak ada error dalam proses upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Buat nama unik untuk file yang diunggah
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // File berhasil diunggah
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        // Gagal menyimpan file
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    // Ada error dalam proses upload
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                // File tidak ditemukan dalam permintaan
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            // Metode request yang diperlukan adalah POST
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }

    public function updateConfirm(){
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_requests"]);
            $this->db->update('purchase_requests', ["approved" => 1, "approved_to" => @$approval->user_approval_1, "approved_by" => $this->session->username, "confirm" => 1, "confirm_date" => date("Y-m-d H:i:s")], ["request_no" => $request_no]);
            echo json_encode(array("message" => "success"));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $purchase_requests = $this->crud->reads("purchase_requests", [], ["item_rm_id" => $post['item_rm_id'], "request_no" => $post['request_no']]);
            if(count($purchase_requests) > 0){
                echo json_encode(array("message" => "Duplicate", "title" => "Double", "theme" => "error"));
            }else{
                $send = $this->crud->create('purchase_requests', $post, "", "approval");
                echo $send;
            }
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $postFinal = array(
                "flag" => $post['flag'],
                "qty" => $post['qty'],
                "request_no" => $post['request_no'],
                "remarks" => $post['remarks'],
                "status_data" => $post['status_data'],
            );

            $send = $this->crud->update('purchase_requests', ["id" => $id], $postFinal, "", "approval");
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_requests', $data);
        echo $send;
    }

     //UPLOAD DATA
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

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'request_no' => $data->val($i, 2),
                'item_rm_number' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'remarks' => $data->val($i, 5),
                'flag' => $data->val($i, 6)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_requests.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_requests.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_requests.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $cleaned = ltrim($data['item_rm_number'], '+');
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['item_rm_number']]);
            $item_rm_clean = $this->crud->read('item_rm', [], ["number" => $cleaned]);
            $purchase_request = $this->crud->read('purchase_requests', [], ["request_no" => $data['request_no'], "item_rm_id" => @$item_rm->id]);

            if (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_number'] . " is Not Found in Master RM", "theme" => "error"));
            }elseif(empty($purchase_request->id)){
                echo json_encode(array("title" => "Not Found", "message" => "PR No ". $data['request_no'] ." and Part No " . $data['item_rm_number'] . " is Not Found in Purchase Request", "theme" => "error"));
            } else {
                $supplier_items = $this->crud->read('supplier_items', [], ["item_rm_id" => $item_rm_clean->id, "supplier_id" => @$purchase_request->supplier_id, "plant" => $purchase_request->plant]);
                
                if($data['qty'] > 0 && @$supplier_items->moq > 0){
                    $final = $data['qty'];
                    if($final > $supplier_items->moq){
                        $qty = (ceil($final / $supplier_items->mpq) * $supplier_items->mpq);
                    }else{
                        $qty = (ceil($final / $supplier_items->moq) * $supplier_items->moq);
                    }
                }else{
                    $qty = 0;
                }

                if($purchase_request->approved_to == "" && $purchase_request->approved != 0){
                    die(json_encode(array("title" => "Failed", "message" => "Part No " . $data['item_rm_number'] . " has been Approved, Cannot Change", "theme" => "error")));
                }
                
                $dataFinal = array(
                    "qty" => $qty,
                    "remarks" => $data['remarks'],
                    "status_data" => "UPLOAD",
                    "flag" => $data['flag'],
                );

                $this->crud->update('purchase_requests', ["id" => $purchase_request->id], $dataFinal, "", "approval");
                echo json_encode(array("title" => "Updated", "message" => "Part No " . $data['item_rm_number'] . " Update Successfully", "theme" => "success"));
            }
        }
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_request_no = base64_decode($this->input->get('filter_request_no'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_3 =  date('M Y', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_2 =  date('M Y', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_1 =  date('M Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $this->db->select('a.*, b.name as supplier_name, c.name as item_family_name, d.number as item_rm_number, d.name as item_rm_name, e.share_order, e.mpq, e.moq, e.leadtime, e.item_supplier');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            $this->db->join('item_rm d', "a.item_rm_id = d.id");
            $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM d.number) = ia.number");
            $this->db->join('supplier_items e', 'ia.id = e.item_rm_id and a.supplier_id = e.supplier_id and a.plant = e.plant and e.share_order > 0');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('a.item_family_id', $filter_item_family);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->order_by('a.request_no', 'ASC');
            $this->db->order_by('d.number', 'ASC');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
            .box-green{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #D6FFCF;
            }

            .box-red{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #FFCFCF;
            }
            </style>
            <body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' .  $config->logo . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>PURCHASE REQUEST</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br><br>
                </center>

                <table id="customers" border="1">
                    <tr>
                        <th rowspan="2" style="text-align:center;" width="20">No</th>
                        <th rowspan="2" style="text-align:center;">STATUS</th>
                        <th rowspan="2" style="text-align:center;">PLANT</th>
                        <th rowspan="2" style="text-align:center;">PERIOD</th>
                        <th rowspan="2" style="text-align:center;">REVISION</th>
                        <th rowspan="2" style="text-align:center;">PR NO</th>
                        <th rowspan="2" style="text-align:center;">VENDOR</th>
                        <th rowspan="2" style="text-align:center;">PART NO BEI</th>
                        <th rowspan="2" style="text-align:center;">PART NO VENDOR</th>
                        <th rowspan="2" style="text-align:center;">PART NAME</th>
                        <th rowspan="2" style="text-align:center;">PRODFAM</th>
                        <th rowspan="2" style="text-align:center;">SHARE<br>ORDER</th>
                        <th rowspan="2" style="text-align:center;">MPQ</th>
                        <th rowspan="2" style="text-align:center;">MOQ</th>
                        <th rowspan="2" style="text-align:center;">LEADTIME</th>
                        <th rowspan="2" style="text-align:center;">NEED</th>
                        <th rowspan="2" style="text-align:center;">MRP</th>
                        <th rowspan="2" style="text-align:center;">QTY</th>
                        <th colspan="3" style="text-align:center;">PURCHASE HISTORY</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th colspan="6" style="text-align:center;">FORECAST VENDOR</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th rowspan="2" style="text-align:center;">REASON</th>
                    </tr>
                    <tr>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_1.'</th>
                        <th style="text-align:center;">'.$month_2.'</th>
                        <th style="text-align:center;">'.$month_3.'</th>
                    </tr>';

                    $no = 1;

                    $productTotals = [];
                    foreach ($records as $record) {
                        $product = $record['item_rm_id'];
                        $shareOrder = $record['share_order'];
                        $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
                    }
                    foreach ($records as $record) {
                        $class = ($productTotals[$record['item_rm_id']] >= 100) ? 'red' : '';
                        if($class == ""){
                            $style = "style='background:#FFD2D2;'";
                        }else{
                            $style = "";
                        }

                        $html .= "  <tr ".$style.">
                                        <td>".$no."</td>
                                        <td>".$record['flag']."</td>
                                        <td>".$record['plant']."</td>
                                        <td>".$record['p_year'] . $record['p_month']."</td>
                                        <td>".$record['revision']."</td>
                                        <td>".$record['request_no']."</td>
                                        <td>".$record['supplier_name']."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['item_rm_number'])."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['item_supplier'])."</td>
                                        <td>".$record['item_rm_name']."</td>
                                        <td>".$record['item_family_name']."</td>
                                        <td>".$record['share_order']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td style='text-align:right;'>".round($record['need'])."</td>
                                        <td style='text-align:right;'>".round($record['mrp'])."</td>
                                        <td style='text-align:right;'>".round($record['qty'])."</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>".round($record['forecast_m3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_avg'])."</td>
                                        <td style='text-align:right;'>".$record['remarks']."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }

    public function print_request($request_no)
    {
        $request_no = base64_decode($request_no);
        $purchase_request_total = $this->crud->reads('purchase_requests', [], ["request_no" => $request_no]);
        $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $request_no]);
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_requests->supplier_id]);

        $config = $this->db->get('config')->row();
        // $config_iso = $this->db->get('config_iso')->row();

        $approval = $this->crud->read("approvals", [], ["table_name" => "purchase_requests"]);
        $user_1 = $this->crud->read("users", [], ["username" => @$approval->user_approval_1]);
        $user_2 = $this->crud->read("users", [], ["username" => @$approval->user_approval_2]);

        $qr_user_1 = "";
        $qr_user_2 = "";

        if($purchase_requests->approved_by == @$approval->user_approval_1){
            $qr_user_1 = "src='" .base_url('assets/image/qrcode/' . @$user_1->name . '.png')."'";
        }elseif($purchase_requests->approved_by == @$approval->user_approval_2){
            $qr_user_1 = "src='" .base_url('assets/image/qrcode/' . @$user_1->name . '.png')."'";
            $qr_user_2 = "src='" .base_url('assets/image/qrcode/' . @$user_2->name . '.png')."'";
        }

        $this->createQrcode(@$user_1->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_2->name, "assets/image/qrcode/");
        
        //Config Page
        $rows = 35;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        // $this->createQrcode($request_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $purchase_requests->request_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 15 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 98</p>
                        </center>
                    </div>
                    <div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.uom, c.leadtime, c.price, currency, c.item_supplier');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM b.number) = ia.number");
            $this->db->join('supplier_items c', 'ia.id = c.item_rm_id and a.supplier_id = c.supplier_id and a.plant = c.plant');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(35, ($i * 35));
            $records = $this->db->get()->result_array();

            $html .= '  <div style="text-align:right;">
                            FRM-EXP-MCL-003
                        </div>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>PURCHASE REQUISITION</u></h3>
                                </center>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"><b>To</b></td>
                                            <td width="30" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>'.@$supplier->address.'</td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Order No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$purchase_requests->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->request_date . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Revision</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$purchase_requests->revision . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Page</td>
                                            <td width="10">:</td>
                                            <td><b>' .$i.'/'.$page.'</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Part No BEI</th>
                                        <th width="120">Part No Vendor</th>
                                        <th width="120">Part Name</th>
                                        <th width="100">Delivery Date <br>(ETA)</th>
                                        <th width="50">Qty</th>
                                        <th width="50">Uom</th>
                                        <th width="50">Currency</th>
                                        <th width="50">Unit Price</th>
                                        <th width="50">Amount</th>
                                        <th width="100">Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_supplier'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . date("Y-m-d", strtotime("+".$record['leadtime']." day", strtotime($record['request_date']))) . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 0) . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right">' . number_format($record['price'], 4) . '</td>
                                <td style="text-align:right">' . number_format(($record['price'] * $record['qty']), 2) . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            
            if (($i + 1) < $page) {
                $html .= '<div style="page-break-after:always;"></div></div>';
            }

            if (($i + 1) == $page) {
                $html .= '  <div style="width:25%; float:left; margin-bottom:20px;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th width="200" style="text-align:center;">ACCEPTED</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">' . @$supplier->name . '</th>
                                    </tr>
                                </table>
                            </div>
                            <div style="width:30%; float:left;">
                                <br>
                            </div>
                            <div style="width:40%; float:right;">
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th colspan="2" style="text-align:center;">'.$config->name.'</th>
                                    </tr>
                                    <tr>
                                            <th style="height:80px;"><img '.$qr_user_1.' width="80"/></th>
                                            <th style="height:80px;"><img '.$qr_user_2.' width="80"/></th>
                                        </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">'.@$user_1->name.'</th>
                                        <th style="height:20px; text-align:center;">'.@$user_2->name.'</th>
                                    </tr>
                                </table>
                            </div>';
            }
            
            $html .= '</div>';
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }
}
