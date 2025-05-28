<?php
error_reporting(0);
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mrp extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        // $this->dummy = $this->load->database('dummy', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mrp.item_rm_id]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mrp');
        } else {
            redirect('error_access');
        }
    }

    public function readRevisions(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        //Select Query
        $this->db->select('revision');
        $this->db->from('generate_mrp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function checkMps()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mrp_ltpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOspo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        // $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mrp_ospo');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkWip()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        // $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mrp_wip');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        // $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mrp_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkRm()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        // $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mrp_stock');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        // $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function datalttp(){
        $post = $this->input->get();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("a.*, b.number_customer as item_fg_number, b.name as item_fg_name, c.name as customer_name");
        $this->db->from("mrp_ltpp a");
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where("a.p_month", $filter_month);
        $this->db->where("a.p_year", $filter_year);
        $this->db->where("a.revision", $filter_revision);
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function dataospo(){
        $post = $this->input->get();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name");
        $this->db->from("mrp_ospo a");
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where("a.p_month", $filter_month);
        $this->db->where("a.p_year", $filter_year);
        $this->db->where("a.revision", $filter_revision);
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datawip(){
        $post = $this->input->get();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name");
        $this->db->from("mrp_wip a");
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where("a.p_month", $filter_month);
        $this->db->where("a.p_year", $filter_year);
        $this->db->where("a.revision", $filter_revision);
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datampp(){
        $post = $this->input->get();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name");
        $this->db->from("mrp_mpp a");
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where("a.p_month", $filter_month);
        $this->db->where("a.p_year", $filter_year);
        $this->db->where("a.revision", $filter_revision);
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datastock(){
        $post = $this->input->get();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("a.*, b.number as item_rm_number, b.name as item_rm_name");
        $this->db->from("mrp_stock a");
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where("a.p_month", $filter_month);
        $this->db->where("a.p_year", $filter_year);
        $this->db->where("a.revision", $filter_revision);
        $this->db->order_by('a.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatableException(){
        $post = $this->input->post();
        $filter_month = $post['filter_month'];
        $filter_year = $post['filter_year'];
        $filter_revision = $post['filter_revision'];
        
        $this->db->select("b.*, 
            COALESCE(c.mpq, 0) AS mpq, 
            COALESCE(c.moq, 0) AS moq, 
            COALESCE(c.leadtime, 0) AS leadtime, 
            COALESCE(c.share_order, 0) AS share_order, 
            d.number AS item_rm_number, 
            d.name AS item_rm_name,
            d.hsn_code");
        $this->db->from("(SELECT item_fg_id, prod_plan FROM generate_mps_details WHERE prod_plan > 0 AND p_month = '$filter_month' AND p_year = '$filter_year' GROUP BY item_fg_id) a");
        $this->db->join('bom b', 'a.item_fg_id = b.item_fg_id');
        $this->db->join('supplier_items c', "b.item_rm_id = c.item_rm_id", 'left');
        $this->db->join('item_rm d', 'b.item_rm_id = d.id');
        $this->db->where("b.item_rm_id NOT IN (SELECT item_rm_id FROM generate_mrp_finals WHERE p_month = '$filter_month' AND p_year = '$filter_year' AND revision = '$filter_revision' GROUP BY item_rm_id)");
        $this->db->group_by('d.id');
        $this->db->order_by('d.id', 'asc');
        $records = $this->db->get()->result_array();
        
        $result['total'] = count($records);
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function getDataMps()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            $this->db->select('max(revision) as revision');
            $this->db->from('generate_mrp');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev = $this->db->get()->row();

            if($filter_revision == ""){
                if(@$rev->revision == ""){
                    $revision = 0;
                }else{
                    $revision = ($rev->revision + 1);
                }
            }else{
                $revision = $filter_revision;
            }

            if($filter_part_no != ""){
                $where_del_part_no = "AND item_rm_id = '$filter_part_no'";
            }else{
                $where_del_part_no = "";
            }

            $this->db->query("DELETE FROM generate_mrp WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$revision' $where_del_part_no");
            
            //Select Query
            $this->db->select("b.p_month, b.p_year, b.revision,
                a.item_rm_id,
                a.composition as qpa,
                b.prod_plan_1,
                b.prod_plan_2,
                b.prod_plan_3,
                b.prod_plan_4,
                b.prod_plan_5,
                b.prod_plan_6");
            $this->db->from('bom a');
            $this->db->join('mrp_ltpp b', 'a.item_fg_id = b.item_fg_id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->group_start();
            $this->db->where('b.prod_plan_1 > 0');
            $this->db->or_where('b.prod_plan_2 > 0');
            $this->db->or_where('b.prod_plan_3 > 0');
            $this->db->or_where('b.prod_plan_4 > 0');
            $this->db->or_where('b.prod_plan_5 > 0');
            $this->db->or_where('b.prod_plan_6 > 0');
            $this->db->group_end();
            $this->db->where('b.p_month', $filter_month);
            $this->db->where('b.p_year', $filter_year);
            $this->db->where('b.revision', $revision);
            if($filter_product_family != ""){
                $this->db->where('c.item_family_id', $filter_product_family);
            }
            if($filter_product_family != ""){
                $this->db->where('c.id', $filter_part_no);
            }
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_rm_id', 'asc');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            foreach ($mpsDetails as $mps) {
                $period = date("Y-m-01", strtotime($filter_year."-".$filter_month."-01"));
                for ($i=1; $i < 7; $i++) { 
                    $arr[] = array(
                        "p_month" => $mps['p_month'],
                        "p_year" => $mps['p_year'],
                        "revision" => $mps['revision'],
                        "cutoff" => $filter_cutoff,
                        "period" => $period,
                        "prodplan" => $mps['prod_plan_'.$i],
                        "item_rm_id" => $mps['item_rm_id'],
                        "qpa" => $mps['qpa'],
                        "qty" => ($mps['prod_plan_'.$i] * $mps['qpa'])
                    );

                    $period = date("Y-m-01", strtotime("+1 month", strtotime($period)));
                }
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $month1 = date("Y-m-01", strtotime("-1 month", strtotime($filter_cutoff)));
            $month3 = date("Y-m-01", strtotime("-3 month", strtotime($filter_cutoff)));
            $month_3 = date("m", strtotime("-3 month", strtotime($filter_year."-".$filter_month."-01")));
            $year_3 = date("Y", strtotime("-3 month", strtotime($filter_year."-".$filter_month."-01")));
            $month_2 = date("m", strtotime("-2 month", strtotime($filter_year."-".$filter_month."-01")));
            $year_2 = date("Y", strtotime("-2 month", strtotime($filter_year."-".$filter_month."-01")));
            $month_1 = date("m", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
            $year_1 = date("Y", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));

            $cutoffDate = date("j", strtotime($filter_cutoff));

            $this->db->select('max(revision) as revision');
            $this->db->from('generate_mrp_finals');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev = $this->db->get()->row();

            if($revision == ""){
                if(@$rev->revision == ""){
                    $filter_revision = 0;
                }else{
                    $filter_revision = ($rev->revision + 1);
                }
            }else{
                $filter_revision = $revision;
            }

            if($filter_part_no != ""){
                $where_del_part_no = "AND item_rm_id = '$filter_part_no'";
            }else{
                $where_del_part_no = "";
            }

            $this->db->query("DELETE FROM generate_mrp_finals WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' $where_del_part_no");

            //Select Query
            $this->db->select('a.item_rm_id, b.supplier_id, b.share_order, b.safety_stock,
                coalesce(b.mpq, 0) as mpq, 
                coalesce(b.moq, 0) as moq, 
                h.type as supplier_type,
                coalesce((b.leadtime + b.transit), 0) as leadtime,
                coalesce(c.qty, 0) as qty_rm, 
                coalesce(abs(d.qty), 0) as qty_wip, 
                coalesce(f.qty, 0) as qty_supply, 
                coalesce(g.qty, 0) as qty_wo, 
                coalesce(e.qty, 0) as qty_vendor');
            $this->db->from('generate_mrp a');
            $this->db->join('item_rm i', "a.item_rm_id = i.id");
            $this->db->join('supplier_items b', "a.item_rm_id = b.item_rm_id and (b.mpq > 0 and b.moq > 0 and b.leadtime > 0 and b.share_order > 0) and b.plant = '$filter_plant'");
            $this->db->join("(SELECT * FROM mrp_stock WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision') c", 'a.item_rm_id = c.item_rm_id', 'left');
            $this->db->join("(SELECT * FROM mrp_ospo WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision') e", 'a.item_rm_id = e.item_rm_id', 'left');
            $this->db->join("(SELECT * FROM mrp_wip WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' and qty < 0) d", 'a.item_rm_id = d.item_rm_id', 'left');
            $this->db->join("(SELECT * FROM mrp_wip WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' and qty > 0) f", 'a.item_rm_id = f.item_rm_id', 'left');
            $this->db->join("(SELECT * FROM mrp_mpp WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision') g", 'a.item_rm_id = g.item_rm_id', 'left');
            $this->db->join('suppliers h', 'b.supplier_id = h.id and h.status = 0');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('b.share_order >', 0);
            if($filter_product_family != ""){
                $this->db->where('i.item_family_id', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->where('a.item_rm_id', $filter_part_no);
            }
            $this->db->group_by('a.item_rm_id');
            $this->db->group_by('b.supplier_id');
            $this->db->order_by('i.number', 'asc');
            $generates = $this->db->get()->result_array();

            die(json_encode($generates));

            foreach ($generates as $generate) {
                $this->db->select('item_rm_id, period, SUM(qty) as need');
                $this->db->from('generate_mrp');
                $this->db->where('item_rm_id', $generate['item_rm_id']);
                $this->db->where('p_month', $filter_month);
                $this->db->where('p_year', $filter_year);
                $this->db->where('revision', $filter_revision);
                $this->db->group_by('item_rm_id');
                $this->db->group_by('period');
                $this->db->order_by('period', 'asc');
                $periods = $this->db->get()->result_array();

                $total_stock = ($generate['qty_rm'] + $generate['qty_wip'] + $generate['qty_vendor']);
                $total_wo = ($generate['qty_supply'] + $generate['qty_wo']);

                $no = 1;
                $need_1 = 0;
                $need_2 = 0;
                $need_3 = 0;
                $need_4 = 0;
                $need_5 = 0;
                $need_6 = 0;
                $need_11 = 0;

                $balance_1 = 0;
                $balance_2 = 0;
                $balance_3 = 0;
                $balance_4 = 0;
                $balance_5 = 0;
                $balance_6 = 0;
                foreach ($periods as $period) {
                    $item_rm_id = $generate['item_rm_id'];
                    
                    if($no == 1){
                        $need_1 += $period['need'];

                        if($filter_revision == 0){
                            $need_11 += $period['need'];
                        }else{
                            $need_11 += 0;
                        }
                        
                        $no = 2;
                    }elseif($no == 2){
                        $need_2 += $period['need'];
                        $no = 3;
                    }elseif($no == 3){
                        $need_3 += $period['need'];
                        $no = 4;
                    }elseif($no == 4){
                        $need_4 += $period['need'];
                        $no = 5;
                    }elseif($no == 5){
                        $need_5 += $period['need'];
                        $no = 6;
                    }elseif($no == 6){
                        $need_6 += $period['need'];
                        $no = 1;
                    }
                }

                $balance_1 = ($total_stock - ($total_wo + $need_11));
                $balance_2 = ($balance_1 - $need_2);
                $balance_3 = ($balance_2 - $need_3);
                $balance_4 = ($balance_3 - $need_4);
                $balance_5 = ($balance_4 - $need_5);
                $balance_6 = ($balance_5 - $need_6);
                
                if(($generate['leadtime'] + $cutoffDate - 1) <= 30){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_1 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_1);
                        }
                    }else{
                        if($balance_2 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_2);
                        }
                    }
                }elseif(($generate['leadtime'] + $cutoffDate - 1) > 30 && ($generate['leadtime'] + $cutoffDate - 1) <= 60){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_2 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_2);
                        }
                    }else{
                        if($balance_3 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_3);
                        }
                    }
                }elseif(($generate['leadtime'] + $cutoffDate - 1) > 60 && ($generate['leadtime'] + $cutoffDate - 1) <= 90){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_3 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_3);
                        }
                    }else{
                        if($balance_4 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_4);
                        }
                    }
                }elseif(($generate['leadtime'] + $cutoffDate - 1) > 90 && ($generate['leadtime'] + $cutoffDate - 1) <= 120){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_4 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_4);
                        }
                    }else{
                        if($balance_5 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_5);
                        }
                    }
                }elseif(($generate['leadtime'] + $cutoffDate - 1) > 120 and ($generate['leadtime'] + $cutoffDate - 1) <= 150){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_5 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_5);
                        }
                    }else{
                        if($balance_6 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_6);
                        }
                    }
                }else{
                    if($balance_6 > 0){
                        $total_need = 0;
                    }else{
                        $total_need = abs($balance_6);
                    }
                }

                $leadtimeMonth = ceil(($generate['leadtime'] + $cutoffDate - 1) / 30);

                switch ($leadtimeMonth) {
                    case 6:
                        $avg_need = (($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 7:
                        $avg_need = (($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 8:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 2);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 9:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 3);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 10:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 4);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 11:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 5);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 12:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    default:
                        $avg_need = 0;
                        $avg_balance = ($balance_6 - $avg_need);
                }

                $share_order_qty = ($total_need * ($generate['share_order'] / 100));
                $safety_stock = round($share_order_qty * ($generate['safety_stock'] / 100));
                $total_need = ($share_order_qty + $safety_stock);

                if($total_need > 0 && $generate['moq'] > 0){
                    if($total_need > $generate['moq']){
                        $purchase_order = (ceil($total_need / $generate['mpq']) * $generate['mpq']);
                    }else{
                        $purchase_order = (ceil($total_need / $generate['moq']) * $generate['moq']);
                    }
                }else{
                    $purchase_order = 0;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "plant" => $filter_plant,
                    "cutoff" => $filter_cutoff . " " . date("H:i:s"),
                    "item_rm_id" => $generate['item_rm_id'],
                    "supplier_id" => $generate['supplier_id'],
                    "share_order" => $generate['share_order'],
                    "mpq" => $generate['mpq'],
                    "moq" => $generate['moq'],
                    "leadtime" => $generate['leadtime'],
                    "qty_rm" => $generate['qty_rm'],
                    "qty_wip" => $generate['qty_wip'],
                    "qty_vendor" => $generate['qty_vendor'],
                    "total_stock" => $total_stock,
                    "qty_supply" => $generate['qty_supply'],
                    "qty_wo" => $generate['qty_wo'],
                    "total_wo" => $total_wo,
                    "need_1" => $need_1,
                    "need_2" => $need_2,
                    "need_3" => $need_3,
                    "need_4" => $need_4,
                    "need_5" => $need_5,
                    "need_6" => $need_6,
                    "balance_1" => $balance_1,
                    "balance_2" => $balance_2,
                    "balance_3" => $balance_3,
                    "balance_4" => $balance_4,
                    "balance_5" => $balance_5,
                    "balance_6" => $balance_6,
                    "avg_need" => $avg_need,
                    "avg_balance" => $avg_balance,
                    "safety_stock" => $safety_stock,
                    "safety_stock_persen" => $generate['safety_stock'],
                    "share_order_qty" => $share_order_qty,
                    "total_need" => $total_need,
                    "purchase_order" => $purchase_order,
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getDataMrpFinals(){
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));

            $this->db->select('max(revision) as revision');
            $this->db->from('generate_mrp_abcclass');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev = $this->db->get()->row();

            if($revision == ""){
                if(@$rev->revision == ""){
                    $filter_revision = 0;
                }else{
                    $filter_revision = ($rev->revision + 1);
                }
            }else{
                $filter_revision = $revision;
            }

            if($filter_part_no != ""){
                $where_del_part_no = "AND item_rm_id = '$filter_part_no'";
            }else{
                $where_del_part_no = "";
            }

            $this->db->query("DELETE FROM generate_mrp_abcclass WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' $where_del_part_no");

            //Select Query
            $this->db->select('a.*, b.item_family_id');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_product_family != ""){
                $this->db->where('b.item_family_id', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->where('a.item_rm_id', $filter_part_no);
            }
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('a.`total_need`', 'asc');
            $generates = $this->db->get()->result_array();

            $total_need = 0;
            $product_family = "";
            $arr = array();
            foreach ($generates as $generate) {
                if($generate['item_family_id'] != $product_family){
                    $total_need = $generate['total_need'];
                }else{
                    $total_need += $generate['total_need'];
                }

                $this->db->select('c.number, SUM(a.total_need) as total');
                $this->db->from('generate_mrp_finals a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
                $this->db->where('a.revision', $filter_revision);
                $this->db->where('b.item_family_id', $generate['item_family_id']);
                $this->db->group_by('b.item_family_id');
                $mrp = $this->db->get()->row();

                if(@$total_need > 0 || @$mrp->total > 0){
                    $composition = round(($total_need / @$mrp->total) * 100);
                }else{
                    $composition = 0;
                }

                $this->db->select('*');
                $this->db->from('safety_stock');
                $this->db->where("start <= '$composition' and ending >= '$composition'");
                $safety_stock = $this->db->get()->row();

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "plant" => $filter_plant,
                    "item_rm_id" => $generate['item_rm_id'],
                    "supplier_id" => $generate['supplier_id'],
                    "need" => $generate['total_need'],
                    "total" => @$mrp->total,
                    "composition" => $composition,
                    "class" => $safety_stock->name,
                    "safety" => $safety_stock->safety,
                    "safety_stock" => round($generate['total_need'] * (@$safety_stock->safety / 100)),
                );

                $product_family = $generate['item_family_id'];
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        }
    }
    
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $send = $this->crud->create('generate_mrp', $post, "logs");
            echo $send;
        }
    }

    public function createMrp()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $item_rm_id = $post['item_rm_id'];

            $item_rm = $this->crud->read("item_rm", [], ["id"=>$item_rm_id]);
            $part_no = @$item_rm->number;

            //Select Query
            // $this->db->select('*');
            // $this->db->from('generate_mrp_finals');
            // $this->db->where('p_month', $post['p_month']);
            // $this->db->where('p_year', $post['p_year']);
            // $this->db->where('revision', $post['revision']);
            // $this->db->where('item_rm_id', $post['item_rm_id']);
            // $records = $this->db->get()->result_array();

            // $this->dummy = $this->load->database('dummy', TRUE);

            // $period_1 = date("Ym", strtotime("-1 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            // $period_2 = date("Ym", strtotime("-2 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            // $period_3 = date("Ym", strtotime("-3 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            $period_1 = date("Y-m", strtotime("-1 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            $period_2 = date("Y-m", strtotime("-2 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            $period_3 = date("Y-m", strtotime("-3 month", strtotime($post['p_year']."-".$post['p_month']."-01")));

            // $qissued = $this->dummy->query("SELECT b.item_id, SUM(b.qty_need) as need from wip_trx_mpp a
            //     join serial_detail_kanbanrm b ON a.temp_woc_id = b.kanbanrm_woc_id
            //     where a.periode IN ('$period_1','$period_2','$period_3') and b.item_id = '$part_no'
            //     group by b.item_id, a.periode
            //     order by b.item_id, a.periode");

            // $maxRevQuery1 = $this->dummy->query("SELECT max(rev) as revision FROM wip_trx_mpp WHERE periode = '$period_1'");
            // $maxRev1 = $maxRevQuery1->row();
            // $maxRevQuery2 = $this->dummy->query("SELECT max(rev) as revision FROM wip_trx_mpp WHERE periode = '$period_2'");
            // $maxRev2 = $maxRevQuery2->row();
            // $maxRevQuery3 = $this->dummy->query("SELECT max(rev) as revision FROM wip_trx_mpp WHERE periode = '$period_3'");
            // $maxRev3 = $maxRevQuery3->row();
            // $rev1 = @$maxRev1->revision;
            // $rev2 = @$maxRev2->revision;
            // $rev3 = @$maxRev3->revision;

            // $qissued1 = $this->dummy->query("SELECT z.periode, z.bom_com_item, SUM(z.need) as need FROM(
            //     SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need from wip_trx_mpp a
            //     JOIN mst_bom b ON b.bom_par_item = a.assy_no
            //     where a.periode = '$period_1' and b.bom_com_item = '$part_no' and a.rev = '$rev1'
            //     order by b.bom_com_item, a.periode) z GROUP BY z.periode, z.bom_com_item ORDER by z.periode asc");
            // $missued1 = $qissued1->result_array();

            // $qissued2 = $this->dummy->query("SELECT z.periode, z.bom_com_item, SUM(z.need) as need FROM(
            //     SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need from wip_trx_mpp a
            //     JOIN mst_bom b ON b.bom_par_item = a.assy_no
            //     where a.periode = '$period_2' and b.bom_com_item = '$part_no' and a.rev = '$rev2'
            //     order by b.bom_com_item, a.periode) z GROUP BY z.periode, z.bom_com_item ORDER by z.periode asc");
            // $missued2 = $qissued2->result_array();

            // $qissued3 = $this->dummy->query("SELECT z.periode, z.bom_com_item, SUM(z.need) as need FROM(
            //     SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need from wip_trx_mpp a
            //     JOIN mst_bom b ON b.bom_par_item = a.assy_no
            //     where a.periode = '$period_3' and b.bom_com_item = '$part_no' and a.rev = '$rev3'
            //     order by b.bom_com_item, a.periode) z GROUP BY z.periode, z.bom_com_item ORDER by z.periode asc");
            // $missued3 = $qissued3->result_array();

            $qissued1 = $this->db->query("SELECT item_rm_id, COALESCE(SUM(qty_act), 0) AS need FROM supply_sheets WHERE request_date like '%$period_1%' and item_rm_id = '$item_rm_id' GROUP BY item_rm_id");
            $missued1 = $qissued1->result_array();

            $qissued2 = $this->db->query("SELECT item_rm_id, COALESCE(SUM(qty_act), 0) AS need FROM supply_sheets WHERE request_date like '%$period_2%' and item_rm_id = '$item_rm_id' GROUP BY item_rm_id");
            $missued2 = $qissued2->result_array();

            $qissued3 = $this->db->query("SELECT item_rm_id, COALESCE(SUM(qty_act), 0) AS need FROM supply_sheets WHERE request_date like '%$period_3%' and item_rm_id = '$item_rm_id' GROUP BY item_rm_id");
            $missued3 = $qissued3->result_array();

            //Select Query
            $this->db->select('*');
            $this->db->from('generate_mrp_finals');
            $this->db->where('p_month', $post['p_month']);
            $this->db->where('p_year', $post['p_year']);
            $this->db->where('revision', $post['revision']);
            $this->db->where('item_rm_id', $post['item_rm_id']);
            $this->db->where('supplier_id', $post['supplier_id']);
            $records = $this->db->get()->result_array();

            $postFinal = array_merge($post, array(
                "issued_1" => @$missued1[0]['need'],
                "issued_2" => @$missued2[0]['need'],
                "issued_3" => @$missued3[0]['need'],
                "issued_avg" => ((@$missued1[0]['need'] + @$missued2[0]['need'] + @$missued3[0]['need']) / 3),
            ));

            $postFinal = array_merge($post, array(
                "issued_1" => 0,
                "issued_2" => 0,
                "issued_3" => 0,
                "issued_avg" => 0,
            ));

            if (count($records) > 0) {
                $send = $this->crud->update('generate_mrp_finals', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                    "supplier_id" => $post['supplier_id'],
                ], $postFinal);

                echo $send;
            } else {
                $send = $this->crud->create('generate_mrp_finals', $postFinal);
                echo $send;
            }
        }
    }

    public function createAbc()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('generate_mrp_abcclass', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id'],
                "supplier_id" => $post['supplier_id'],
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('generate_mrp_abcclass', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                    "supplier_id" => $post['supplier_id'],
                ], $post);

                echo $send;
            } else {
                $send = $this->crud->create('generate_mrp_abcclass', $post);
                echo $send;
            }
        }
    }

    public function createLttp(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('mrp_ltpp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "item_fg_id" => $post['item_fg_id'],
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('mrp_ltpp', $post, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "item_fg_id" => $post['item_fg_id'],
                ], "logs");

                echo $send;
            } else {
                $send = $this->crud->create('mrp_ltpp', $post, "log");
                echo $send;
            }
        }
    }

    public function createOsPo(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('mrp_ospo', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id']
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('mrp_ospo', $post, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                ], "logs");

                echo $send;
            } else {
                $send = $this->crud->create('mrp_ospo', $post, "log");
                echo $send;
            }
        }
    }

    public function createWip(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('mrp_wip', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id']
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('mrp_wip', $post, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                ], "logs");

                echo $send;
            } else {
                $send = $this->crud->create('mrp_wip', $post, "log");
                echo $send;
            }
        }
    }

    public function createMpp(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('mrp_mpp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id']
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('mrp_mpp', $post, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                ], "logs");

                echo $send;
            } else {
                $send = $this->crud->create('mrp_mpp', $post, "log");
                echo $send;
            }
        }
    }

    public function createStock(){
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->crud->reads('mrp_stock', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_rm_id" => $post['item_rm_id']
            ]);

            if (count($records) > 0) {
                $send = $this->crud->update('mrp_stock', $post, [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_rm_id" => $post['item_rm_id'],
                ], "logs");

                echo $send;
            } else {
                $send = $this->crud->create('mrp_stock', $post, "log");
                echo $send;
            }
        }
    }

    public function print($option = "", $approved_to = "", $approved_by = "")
    {
        // if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=mrp_$format.xls");
            }

            $approved_to = base64_decode($approved_to);
            $approved_by = base64_decode($approved_by);

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
            $filter_plant = trim(base64_decode($this->input->get('filter_plant')));

            $period_1 = date("F Y", strtotime($filter_year."-".$filter_month."-01"));
            $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));

            $need_1 = date("m/Y",  strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
            $need_2 = date("m/Y",  strtotime("-2 month", strtotime($filter_year."-".$filter_month."-01")));
            $need_3 = date("m/Y",  strtotime("-3 month", strtotime($filter_year."-".$filter_month."-01")));

            $this->db->select('a.*, CONCAT(a.p_year, a.p_month) as period, b.number as item_rm_number, b.name as item_rm_name, e.id as supplier_number, e.name as supplier_name, f.leadtime as leadtime2, f.transit, c.name as product_family, d.class, d.composition, d.safety');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            $this->db->join('suppliers e', 'a.supplier_id = e.id');
            $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id');
            $this->db->where('a.plant', $filter_plant);
            if($approved_to != ""){
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
                $this->db->where('a.revision', $filter_revision);
                $this->db->where('a.approved_to', $approved_to);
                $this->db->where('a.approved_by', $approved_by);
            }else{
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
                $this->db->where('a.revision', $filter_revision);
                $this->db->like('b.item_family_id', $filter_product_family);
                $this->db->like('a.item_rm_id', $filter_part_no);
            }
            // $this->db->where('leadtime >', 0);
            $this->db->group_by('a.item_rm_id');
            $this->db->group_by('a.supplier_id');
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('d.class', 'asc');
            $this->db->order_by('b.number', 'asc');
            // $this->db->limit(10);
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 250%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
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
                                    <img src="' . $config->logo . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>GENERATE MRP</small><br>
                                    <small>CUTOFF DATE : '.@$records[0]['cutoff'].'</small>
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
                        <th rowspan="2" style="text-align:center;" width="80">APPROVE</th>
                        <th rowspan="2" style="text-align:center;">APPROVED BY</th>
                        <th rowspan="2" style="text-align:center;">PERIOD</th>
                        <th rowspan="2" style="text-align:center;">ORIGINAL PART NO</th>
                        <th rowspan="2" style="text-align:center;">PART NO SUPPLIER</th>
                        <th rowspan="2" style="text-align:center;">DESCRIPTION</th>
                        <th rowspan="2" style="text-align:center;">PRODUCT FAMILY</th>
                        <th rowspan="2" style="text-align:center;">SUPPLIER CODE</th>
                        <th rowspan="2" style="text-align:center;">SUPPLIER NAME</th>
                        <th rowspan="2" style="text-align:center;">CLASS<br>A/B/C</th>
                        <th rowspan="2" style="text-align:center;">MFG L/T</th>
                        <th rowspan="2" style="text-align:center;">TRANSIT</th>
                        <th rowspan="2" style="text-align:center;">PO L/T</th>
                        <th rowspan="2" style="text-align:center;">MPQ</th>
                        <th rowspan="2" style="text-align:center;">MOQ</th>
                        <th colspan="3" style="text-align:center;">WORKORDER</th>
                        <th rowspan="2" style="text-align:center;">Xbar</th>
                        <th colspan="3" style="text-align:center;">STOCK RAW MATERIAL</th>
                        <th rowspan="2" style="text-align:center;">TOTAL<br>STOCK</th>
                        <th rowspan="2" style="text-align:center;">OS<br>SUPPLY</th>
                        <th rowspan="2" style="text-align:center;">OS<br>WO</th>
                        <th rowspan="2" style="text-align:center;">TOTAL<br>OS</th>
                        <th colspan="2" style="text-align:center;">'.$period_1.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_2.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_3.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_4.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_5.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_6.'</th>
                        <th colspan="2" style="text-align:center;"> 6 > Month </th>
                        <th rowspan="2" style="text-align:center;">SHARE<br>ORDER %</th>
                        <th rowspan="2" style="text-align:center;">SAFETY<br>STOCK %</th>
                        <th rowspan="2" style="text-align:center;">SAFETY<br>STOCK</th>
                        <th rowspan="2" style="text-align:center;">SHARE ORDER<br>QTY</th>
                        <th rowspan="2" style="text-align:center;">TOTAL<br>NEED</th>
                        <th rowspan="2" style="text-align:center;">MRP<br>RESULT</th>
                        <th rowspan="2" style="text-align:center;" width="80">STATUS</th>
                        <th rowspan="2" style="text-align:center;" width="80">PO</th>
                    </tr>
                    <tr>
                        <th style="text-align:center;">'.$need_3.'</th>
                        <th style="text-align:center;">'.$need_2.'</th>
                        <th style="text-align:center;">'.$need_1.'</th>
                        <th style="text-align:center;">WHS</th>
                        <th style="text-align:center;">WIP</th>
                        <th style="text-align:center;">OS SUPPLIER</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                    </tr>';

                    $no = 1;
                    foreach ($records as $record) {
                        if($record['balance_1'] > 0){ $style_1 = ""; }else{ $style_1 = "color:red;"; }
                        if($record['balance_2'] > 0){ $style_2 = ""; }else{ $style_2 = "color:red;"; }
                        if($record['balance_3'] > 0){ $style_3 = ""; }else{ $style_3 = "color:red;"; }
                        if($record['balance_4'] > 0){ $style_4 = ""; }else{ $style_4 = "color:red;"; }
                        if($record['balance_5'] > 0){ $style_5 = ""; }else{ $style_5 = "color:red;"; }
                        if($record['balance_6'] > 0){ $style_6 = ""; }else{ $style_6 = "color:red;"; }
                        if($record['avg_balance'] > 0){ $style_avg = ""; }else{ $style_avg = "color:red;"; }
                        if($record['purchase_order'] > 0){ $status = "ORDER"; $style_7 = "background:green; color:white;"; }else{ $status = "NOT ORDER"; $style_7 = "background:red; color:white;"; }
                        if($record['status'] == 0){ $po = "OPEN"; $style_8 = "background:red; color:white;"; }else{ $po = "CREATED"; $style_8 = "background:green; color:white;"; }

                        if(round($record['total_need']) > 0 && $record['moq'] > 0){
                            $final = ($record['total_need']);
                            if($final > $record['moq']){
                                $mrp_result = (ceil($final / $record['mpq']) * $record['mpq']);
                            }else{
                                $mrp_result = (ceil($final / $record['moq']) * $record['moq']);
                            }
                        }else{
                            $mrp_result = 0;
                        }

                        if($record['approved_to'] == "" || $record['approved_to'] == null){
                            $approved = "APPROVED";
                            $styleApp = "background:green; color:white;";
                        }else{
                            $approved = "CHECKING";
                            $styleApp = "background:orange; color:white;";
                        }

                        $html .= "  <tr>
                                        <td>".$no."</td>
                                        <td style='text-align:center; $styleApp'>".$approved."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['approved_by'])."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['period'])."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['item_rm_id'])."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['item_rm_number'])."</td>
                                        <td style='mso-number-format:\@;'>".$record['item_rm_name']."</td>
                                        <td>".$record['product_family']."</td>
                                        <td>".$record['supplier_number']."</td>
                                        <td>".$record['supplier_name']."</td>
                                        <td>".$record['class']."</td>
                                        <td>".$record['transit']."</td>
                                        <td>".$record['leadtime2']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td style='text-align:right;'>".round($record['issued_1'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_2'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_3'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_avg'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_rm'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_wip'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_vendor'])."</td>
                                        <td style='text-align:right;'>".round($record['total_stock'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_supply'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_wo'])."</td>
                                        <td style='text-align:right;'>".round($record['total_wo'])."</td>
                                        <td style='text-align:right;'>".round($record['need_1'])."</td>
                                        <td style='text-align:right;$style_1'>".round($record['balance_1'])."</td>
                                        <td style='text-align:right;'>".round($record['need_2'])."</td>
                                        <td style='text-align:right;$style_2'>".round($record['balance_2'])."</td>
                                        <td style='text-align:right;'>".round($record['need_3'])."</td>
                                        <td style='text-align:right;$style_3'>".round($record['balance_3'])."</td>
                                        <td style='text-align:right;'>".round($record['need_4'])."</td>
                                        <td style='text-align:right;$style_4'>".round($record['balance_4'])."</td>
                                        <td style='text-align:right;'>".round($record['need_5'])."</td>
                                        <td style='text-align:right;$style_5'>".round($record['balance_5'])."</td>
                                        <td style='text-align:right;'>".round($record['need_6'])."</td>
                                        <td style='text-align:right;$style_6'>".round($record['balance_6'])."</td>
                                        <td style='text-align:right;'>".round($record['avg_need'])."</td>
                                        <td style='text-align:right;$style_avg'>".round($record['avg_balance'])."</td>
                                        <td style='text-align:center;'>".$record['share_order']."</td>
                                        <td style='text-align:right;'>".round($record['safety_stock_persen'])."</td>
                                        <td style='text-align:right;'>".round($record['safety_stock'])."</td>
                                        <td style='text-align:right;'>".round($record['share_order_qty'])."</td>
                                        <td style='text-align:right;'>".round($record['total_need'])."</td>
                                        <td style='text-align:right;'>".round($mrp_result)."</td>
                                        <td style='text-align:center;".$style_7."'>".$status."</td>
                                        <td style='text-align:center;".$style_8."'>".$po."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        // }
    }
}
