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
        $this->load->model('api');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/delivery_schedule_days');
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
            $this->db->from('working_calendar');
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

    public function getData(){
        $this->dummy = $this->load->database('dummy', TRUE);

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $period_start = date("Y-m-01", strtotime($filter_year."-".$filter_month."-10"));
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-10"));

        $period_bf_start = date("Y-m-01", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-10")));
        $period_bf_end = date("Y-m-t", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-10")));

        $this->db->select('a.purchase_order_no, a.item_id, a.item_name, a.supplier_id, a.product_family, a.qty');
        $this->db->from('purchase_orders a');
        $this->db->join('supplier_items b', 'a.supplier_id = b.supplier_id and a.item_id = b.item_id');
        $this->db->join('mst_maker c', 'b.maker_code = c.number');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.qty >', 0);
        $this->db->where('b.share_order >', 0);
        $this->db->where('c.type', 'LOCAL');
        $this->db->group_by('a.item_id');
        $this->db->group_by('a.supplier_id');
        // $this->db->limit(10);
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            $firstDate = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));

            $item_id = $record['item_id'];
            $supplier_id = $record['supplier_id'];
            $qty = (int) $record['qty'];
            $hkw = $this->hkw($filter_month, $filter_year);
            $total_qty = round($qty / $hkw);

            $ithBf = $this->dummy->query("SELECT SUM(ith_qty) as total from ith where ith_form like '%IN - %' and ith_item_id = '$item_id' and ith_date between '$period_bf_start' and '$period_bf_end'");
            $rowIth = $ithBf->row();

            $poBf = $this->api->query("SELECT SUM(qty) as total FROM purchase_orders WHERE item_id = '$item_id' and purchase_order_date between '$period_bf_start' and '$period_bf_end'");

            $no = 1;
            $arrDate = array();
            $final_qty = $qty;
            while (strtotime($firstDate) <= strtotime($endDate)) {
                $working_date = date("Y-m-d", strtotime($firstDate));

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('deleted_is', 0);
                $this->db->where('working_date', $working_date);
                $calendar = $this->db->get()->row();

                if (@$calendar->remarks != "") {
                    $arrDate["del_".$no] = 0;
                }else if (date("l", strtotime($working_date)) == "Sunday") {
                    $arrDate["del_".$no] = 0;
                }else{
                    if($final_qty > $total_qty){
                        $arrDate["del_".$no] = $total_qty;
                    } else {
                        $arrDate["del_".$no] = $final_qty;
                    }

                    $final_qty -= $total_qty;
                }
                
                $no++;
                $firstDate = date("d M", strtotime("+1 day", strtotime($firstDate)));
            }

            $arr[] = array_merge($arrDate, array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision,
                "po_no" => $record['purchase_order_no'],
                "supplier_id" => $record['supplier_id'],
                "part_no" => $record['item_id'],
                "part_name" => $record['item_name'],
                "product_family" => $record['product_family'],
                "qty" => $qty,
                "qty_bf" => (@$rowIth->total - @$poBf[0]->total),
            ));
        }

        $this->db->delete('delivery_schedule_days', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function create(){
        if ($this->input->post()) {
            $post = $this->input->post('data');

            if ($this->api->create('delivery_schedule_days', $post, "JUTA", "JUTA")) {
                echo json_encode(array("title" => "Good Job", "message" => $post['part_no'] . " | Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Error", "message" => $post['part_no'] . " | Data Unsaved", "theme" => "error"));
            }
        }
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
        $this->dummy = $this->load->database('dummy', TRUE);

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        $firstDate = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));
        $monthBf = date('F', strtotime("-1 month", strtotime($filter_year . "-" . $filter_month . "-01")));

        $this->db->select("a.*, c.name as supplier_name, b.name as product_family_name");
        $this->db->from('delivery_schedule_days a');
        $this->db->join('mst_item_family b', 'a.product_family = b.number');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.product_family', $filter_item_family);
        $this->db->like('a.part_no', $filter_item_id);
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('a.part_no', 'asc');
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
            $this->db->from('working_calendar');
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
            $firstDate = date("d M", strtotime("+1 day", strtotime($firstDate)));
        }

        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';

        foreach ($records as $record) {
            $day = 1;
            $firstDate2 = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $item_id = $record['part_no'];

            $content .= "<tr>
                            <td>" . $no . "</td>
                            <td style='mso-number-format:\@;'>" . $record['part_no'] . "</td>
                            <td>" . $record['part_name'] . "</td>
                            <td>" . $record['product_family_name'] . "</td>
                            <td>" . $record['supplier_name'] . "</td>
                            <td style='text-align:right;'>" . $record['qty'] . "</td>
                            <td style='text-align:right;'>" . round($record['qty_bf']) . "</td>";

            $styles2 = "";
            $balance = @$record['qty_bf'];
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date2 = date("Y-m-d", strtotime($firstDate2));
                $date_qty = $record["del_".$day];

                $ithBf = $this->dummy->query("SELECT SUM(ith_qty) as total from ith where ith_form like '%IN - %' and ith_item_id = '$item_id' and ith_date = '$working_date2'");
                $rowIth = $ithBf->row();

                $balance = (($balance + @$date_qty) - round(@$rowIth->total));

                $this->db->select('remarks');
                $this->db->from('working_calendar');
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

                $content .= "<td style='text-align:right; ".$styles2."'>".$date_qty."</td>
                            <td style='text-align:right; ".$styles2."'>".round(@$rowIth->total)."</td>
                            <td style='text-align:right; ".$styles2."'>".round($balance, 2)."</td>";

                $firstDate2 = date("d M", strtotime("+1 day", strtotime($firstDate2)));
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

        <table id="customers" border="1" style="width:450%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">NO</th>
                <th style="text-align:center;" width="150" rowspan="3">Part No</th>
                <th style="text-align:center;" width="80" rowspan="3">Part NAME</th>
                <th style="text-align:center;" width="80" rowspan="3">Product Family</th>
                <th style="text-align:center;" width="200" rowspan="3">Supplier</th>
                <th style="text-align:center;" width="50" rowspan="3">Qty PO</th>
                <th style="text-align:center;" width="50" rowspan="3">OS Delivery<br>('.$monthBf.')</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan * 3).'">Quantitiy Order From Purchase Order</th>
            </tr>' . $cut . $date . $content;
        echo $html;
    }
}
