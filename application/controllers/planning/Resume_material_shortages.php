<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Resume_material_shortages extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) { redirect('error_session'); }
        elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data = [
                "button" => $this->getbutton($this->id_menu()),
                "month" => [],
                "year" => [],
                "revision" => [
                    ["id" => "0", "name" => "Revision 0", "selected" => true],
                    ["id" => "1", "name" => "Revision 1"],
                    ["id" => "2", "name" => "Revision 2"],
                    ["id" => "3", "name" => "Revision 3"],
                    ["id" => "4", "name" => "Revision 4"],
                    ["id" => "5", "name" => "Revision 5"]
                ],
                "plant" => [],
                "prodfam" => []
            ];

            $year = intval(date("Y"));
            for ($i = ($year + 1); $i >= ($year - 7); $i--) {
                $arr = ["id" => "$i", "name" => $i];
                if ($i == $year) { $arr['selected'] = true; }
                array_push($data["year"], $arr);
            }

            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            foreach ($months as $key => $value) {
                $month = ["id" => str_pad($key + 1, 2, "0", STR_PAD_LEFT), "name" => $value];
                if ($value == date('F')) { $month['selected'] = true; }
                array_push($data["month"], $month);
            }

            $this->db->distinct()->select("number, name")->from("plants")->order_by("name");
            foreach ($this->db->get()->result() as $index => $row) { array_push($data["plant"], $row); }

            $this->db->distinct()->select("number, name")->from("item_familys")->where("item_category_id", "C01")->order_by("name");
            foreach ($this->db->get()->result() as $index => $row) { array_push($data["prodfam"], $row); }

            $this->load->view("template/header", $data);
            $this->load->view("planning/resume_material_shortages");
        } else { redirect("error_access"); }
    }

    public function print()
    {
        $period = base64_decode($this->input->get('year')).'-'.base64_decode($this->input->get('month')).'-01';
        $plant = base64_decode($this->input->get('plant'));
        $revision = base64_decode($this->input->get('revision'));
        $prodfam = json_decode(base64_decode($this->input->get('prodfam')));
        $needs = ['need_1','need_2','need_3'];
        $result = [];

        $this->db->distinct()->select(
            "c.name as prod_name,".
            "b.number AS item_id,".
            "a.item_rm_id AS item,".
            "a.stock_hand AS stock_manual,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '01' AND '03' THEN a.qty ELSE 0 END) AS need1_w1,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '04' AND '05' THEN a.qty ELSE 0 END) AS need1_w2,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '06' AND '08' THEN a.qty ELSE 0 END) AS need1_w3,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) > '09' THEN a.qty ELSE 0 END) AS need1_w4,".
            "a.need_2 AS need2,".
            "a.need_3 AS need3"
        )->from("generate_item_calculation_2 a")
            ->join("item_rm b", "a.item_rm_id = b.id")
            ->join("item_familys c", "b.item_family_id = c.id")
        ->where("CONCAT(a.p_year,LPAD(a.p_month,2,'0'))", date("Ym", strtotime($period)))
        ->where("a.revision", $revision)->where("a.plant", $plant);
        if (!empty($prodfam)) { $this->db->where_in("c.number", $prodfam); }
        $this->db->group_by("c.name, a.item_rm_id, a.need_2, a.need_3")->order_by("prod_name, item");
        // die($this->db->get_compiled_select());
        foreach ($this->db->get()->result() as $index => $row) {
            $prodname = $row->prod_name;
            $arr_prodfam = array_column($result, 'prodfam');
            if (!in_array($prodname, $arr_prodfam, true)) {
                $set = [];
                foreach ($needs as $key => $value) {
                    $set[$value] = [
                        'count_item' => 0,
                        'count_bal' => [0, 0, 0, 0],
                        'total_item' => 0,
                        'total_bal' => [0, 0, 0, 0],
                        'percent_count' => [0, 0, 0, 0],
                        'percent_total' => [0, 0, 0, 0]
                    ];
                }
                array_push($result, array_replace(['prodfam' => $prodname], $set));
            }
            $idx = array_search($prodname, $arr_prodfam);

            $need_w1 = floatval($row->need1_w1);
            $bal_w1 = round(floatval($row->stock_manual) - $need_w1);
            $need_w2 = floatval($row->need1_w2);
            $bal_w2 = round($bal_w1 - $need_w2);
            $need_w3 = floatval($row->need1_w3);
            $bal_w3 = round($bal_w2 - $need_w3);
            $need_w4 = floatval($row->need1_w4);
            $bal_w4 = round($bal_w3 - $need_w4);
            $need_all = $need_w1 + $need_w2 + $need_w3 + $need_w4;

            if ($need_all > 0) {
                $result[$idx]['need_1']['count_item']++;
                $result[$idx]['need_1']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0) {
                $result[$idx]['need_1']['count_bal'][0]++;
                $result[$idx]['need_1']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_1']['count_bal'][1]++;
                $result[$idx]['need_1']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_1']['count_bal'][2]++;
                $result[$idx]['need_1']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_1']['count_bal'][3]++;
                $result[$idx]['need_1']['total_bal'][3] += abs($bal_w4);
            }

            $bal_bf = $bal_w4;
            $need_all = floatval($row->need2);
            $need_week = round($need_all / 4);
            $bal_w1 = round($bal_bf - $need_week);
            $bal_w2 = round($bal_w1 - $need_week);
            $bal_w3 = round($bal_w2 - $need_week);
            $bal_w4 = round($bal_w3 - $need_week);

            if ($need_all > 0) {
                $result[$idx]['need_2']['count_item']++;
                $result[$idx]['need_2']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0 && $bal_bf > 0) {
                $result[$idx]['need_2']['count_bal'][0]++;
                $result[$idx]['need_2']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_2']['count_bal'][1]++;
                $result[$idx]['need_2']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_2']['count_bal'][2]++;
                $result[$idx]['need_2']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_2']['count_bal'][3]++;
                $result[$idx]['need_2']['total_bal'][3] += abs($bal_w4);
            }

            $bal_bf = $bal_w4;
            $need_all = floatval($row->need3);
            $need_week = round($need_all / 4);
            $bal_w1 = round($bal_bf - $need_week);
            $bal_w2 = round($bal_w1 - $need_week);
            $bal_w3 = round($bal_w2 - $need_week);
            $bal_w4 = round($bal_w3 - $need_week);

            if ($need_all > 0) {
                $result[$idx]['need_3']['count_item']++;
                $result[$idx]['need_3']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0 && $bal_bf > 0) {
                $result[$idx]['need_3']['count_bal'][0]++;
                $result[$idx]['need_3']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_3']['count_bal'][1]++;
                $result[$idx]['need_3']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_3']['count_bal'][2]++;
                $result[$idx]['need_3']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_3']['count_bal'][3]++;
                $result[$idx]['need_3']['total_bal'][3] += abs($bal_w4);
            }
        }
        // die(json_encode($result));

        foreach ($result as $index => $row) {
            foreach ($needs as $key => $need) {
                $count_item = $row[$need]['count_item'];
                $total_item = $row[$need]['total_item'];
                for ($i=0; $i < 4; $i++) {
                    $get_count = $row[$need]['count_bal'][$i];
                    $get_total = $row[$need]['total_bal'][$i];

                    $result[$index][$need]['percent_count'][$i] = (empty($count_item)) ? 0 : intval((($count_item - $get_count) / $count_item) * 100);
                    $result[$index][$need]['percent_total'][$i] = (empty($total_item)) ? 0 : intval((($total_item - $get_total) / $total_item) * 100);
                }
            }
        }
        // die(json_encode($result));

        $this->db->select("*")->from("config");
        $config = $this->db->get()->row();

        $this->db->select("TRIM(name) AS name")->from("users")->where("username", $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $print_date = date("d M Y H:m:s");
        $head_period1 = date("M-y", strtotime($period));
        $head_period2 = date("M-y", strtotime($period.' +1 month'));
        $head_period3 = date("M-y", strtotime($period.' +2 month'));

        $periode = date('F Y', strtotime($period));

        $this->db->distinct()->select("TRIM(name) AS name")->from("plants")->where("number", $plant);
        $plant_name = $this->db->get()->row()->name;

        $prod_name = 'ALL';
        if (!empty($prodfam)) {
            $prod_name = '';
            $this->db->distinct()->select("TRIM(name) AS name")->from("item_familys")
            ->where_in("number", $prodfam)->order_by("name");
            foreach ($this->db->get()->result() as $key => $value) {
                if (!empty($prod_name)) { $prod_name .= ', '; }
                $prod_name .= $value->name;
            }
        }

        $html = <<<HTML
            <html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#datatables {border-collapse: collapse;width: 100%;font-size: 12px;}#datatables td, #datatables th {border: 1px solid #ddd;padding: 2px;}#datatables tr:nth-child(even){background-color: #f2f2f2;}#datatables tr:hover {background-color: #ddd;}#datatables th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="{$config->logo}" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>{$config->name}</b><br>
                                <small></small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date $print_date<br>
                    Print By $fullname
                </div>
            </center>
            <br><br><br><br>
            <center><table>
                <tr>
                    <td style="font-weight: bold;">Period</td>
                    <td>: $periode</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Plant</td>
                    <td>: $plant_name</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Revision</td>
                    <td>: $revision</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Product Family</td>
                    <td>: $prod_name</td>
                </tr>
            </table></center>
            <br><br>
            <table id="datatables" border="1">
                <tr>
                    <th style="text-align: center;" rowspan="2" width="20">No</th>
                    <th style="text-align: center;" rowspan="2">Product Family</th>
                    <th style="text-align: center;" rowspan="2">Detail</th>
                    <th style="text-align: center;" colspan="4">$head_period1</th>
                    <th style="text-align: center;" colspan="4">$head_period2</th>
                    <th style="text-align: center;" colspan="4">$head_period3</th>
                </tr>
                <tr>
                    <th style="text-align: center;">Week 1</th>
                    <th style="text-align: center;">Week 2</th>
                    <th style="text-align: center;">Week 3</th>
                    <th style="text-align: center;">Week 4</th>
                    <th style="text-align: center;">Week 1</th>
                    <th style="text-align: center;">Week 2</th>
                    <th style="text-align: center;">Week 3</th>
                    <th style="text-align: center;">Week 4</th>
                    <th style="text-align: center;">Week 1</th>
                    <th style="text-align: center;">Week 2</th>
                    <th style="text-align: center;">Week 3</th>
                    <th style="text-align: center;">Week 4</th>
                </tr>
        HTML;
        $details = [
            "count_item" => "Total item",
            "count_bal" => "Item SH",
            "total_item" => "Total Qty",
            "total_bal" => "Qty SH",
            "percent_count" => "%-tag Av (ITEM)",
            "percent_total" => "%-tag Av (Qty)"
        ];
        foreach ($result as $index => $row) {
            $periods = $row;
            unset($periods['prodfam']);
            // die(json_encode($periods));

            $no = $index + 1;
            $html .= <<<HTML
                <tr>
                    <td rowspan="6" align="center">$no</td>
                    <td rowspan="6" align="center">{$row['prodfam']}</td>
            HTML;
            foreach ($details as $key => $value) {
                if ($key != "count_item") {
                    $html .= <<<HTML
                        <tr>
                    HTML;
                }
                $html .= <<<HTML
                    <td>$value</td>
                HTML;
                foreach ($periods as $week => $data) {
                    $get = $data[$key];
                    for ($i=0; $i < 4; $i++) {
                        $val = intval((is_array($get)) ? $get[$i] : $get);
                        $style = '';

                        if (in_array($key, ['percent_count','percent_total'])) {
                            $style = 'style="background-color: ';
                            if ($val >= 100) { $style .= 'green; color: white'; }
                            elseif ($val >= 90) { $style .= '#FFFF99'; }
                            else { $style .= '#FF5050; color: white'; }
                            $style .= ';"';
                            $val .= '%';
                        } else { $val = number_format($val, 0, '.', ','); }

                        $html .= <<<HTML
                            <td align="right" $style>$val</td>
                        HTML;
                    }
                }
                $html .= <<<HTML
                    </tr>
                HTML;
            }
        }

        echo $html.'</table></body></html>';
    }

    public function excel()
    {
        $period = base64_decode($this->input->get('year')).'-'.base64_decode($this->input->get('month')).'-01';
        $plant = base64_decode($this->input->get('plant'));
        $revision = base64_decode($this->input->get('revision'));
        $prodfam = json_decode(base64_decode($this->input->get('prodfam')));
        $needs = ['need_1','need_2','need_3'];
        $result = [];

        $this->db->distinct()->select(
            "c.name as prod_name,".
            "b.number AS item_id,".
            "a.item_rm_id AS item,".
            "a.stock_hand AS stock_manual,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '01' AND '03' THEN a.qty ELSE 0 END) AS need1_w1,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '04' AND '05' THEN a.qty ELSE 0 END) AS need1_w2,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) BETWEEN '06' AND '08' THEN a.qty ELSE 0 END) AS need1_w3,".
            "SUM(CASE WHEN SUBSTRING(a.wp, 1, 2) > '09' THEN a.qty ELSE 0 END) AS need1_w4,".
            "a.need_2 AS need2,".
            "a.need_3 AS need3"
        )->from("generate_item_calculation_2 a")
            ->join("item_rm b", "a.item_rm_id = b.id")
            ->join("item_familys c", "b.item_family_id = c.id")
        ->where("CONCAT(a.p_year,LPAD(a.p_month,2,'0'))", date("Ym", strtotime($period)))
        ->where("a.revision", $revision)->where("a.plant", $plant);
        if (!empty($prodfam)) { $this->db->where_in("c.number", $prodfam); }
        $this->db->group_by("c.name, a.item_rm_id, a.need_2, a.need_3")->order_by("prod_name, item");
        // die($this->db->get_compiled_select());
        foreach ($this->db->get()->result() as $index => $row) {
            $prodname = $row->prod_name;
            $arr_prodfam = array_column($result, 'prodfam');
            if (!in_array($prodname, $arr_prodfam, true)) {
                $set = [];
                foreach ($needs as $key => $value) {
                    $set[$value] = [
                        'count_item' => 0,
                        'count_bal' => [0, 0, 0, 0],
                        'total_item' => 0,
                        'total_bal' => [0, 0, 0, 0],
                        'percent_count' => [0, 0, 0, 0],
                        'percent_total' => [0, 0, 0, 0]
                    ];
                }
                array_push($result, array_replace(['prodfam' => $prodname], $set));
            }
            $idx = array_search($prodname, $arr_prodfam);

            $need_w1 = floatval($row->need1_w1);
            $bal_w1 = round(floatval($row->stock_manual) - $need_w1);
            $need_w2 = floatval($row->need1_w2);
            $bal_w2 = round($bal_w1 - $need_w2);
            $need_w3 = floatval($row->need1_w3);
            $bal_w3 = round($bal_w2 - $need_w3);
            $need_w4 = floatval($row->need1_w4);
            $bal_w4 = round($bal_w3 - $need_w4);
            $need_all = $need_w1 + $need_w2 + $need_w3 + $need_w4;

            if ($need_all > 0) {
                $result[$idx]['need_1']['count_item']++;
                $result[$idx]['need_1']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0) {
                $result[$idx]['need_1']['count_bal'][0]++;
                $result[$idx]['need_1']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_1']['count_bal'][1]++;
                $result[$idx]['need_1']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_1']['count_bal'][2]++;
                $result[$idx]['need_1']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_1']['count_bal'][3]++;
                $result[$idx]['need_1']['total_bal'][3] += abs($bal_w4);
            }

            $bal_bf = $bal_w4;
            $need_all = floatval($row->need2);
            $need_week = round($need_all / 4);
            $bal_w1 = round($bal_bf - $need_week);
            $bal_w2 = round($bal_w1 - $need_week);
            $bal_w3 = round($bal_w2 - $need_week);
            $bal_w4 = round($bal_w3 - $need_week);

            if ($need_all > 0) {
                $result[$idx]['need_2']['count_item']++;
                $result[$idx]['need_2']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0 && $bal_bf > 0) {
                $result[$idx]['need_2']['count_bal'][0]++;
                $result[$idx]['need_2']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_2']['count_bal'][1]++;
                $result[$idx]['need_2']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_2']['count_bal'][2]++;
                $result[$idx]['need_2']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_2']['count_bal'][3]++;
                $result[$idx]['need_2']['total_bal'][3] += abs($bal_w4);
            }

            $bal_bf = $bal_w4;
            $need_all = floatval($row->need3);
            $need_week = round($need_all / 4);
            $bal_w1 = round($bal_bf - $need_week);
            $bal_w2 = round($bal_w1 - $need_week);
            $bal_w3 = round($bal_w2 - $need_week);
            $bal_w4 = round($bal_w3 - $need_week);

            if ($need_all > 0) {
                $result[$idx]['need_3']['count_item']++;
                $result[$idx]['need_3']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0 && $bal_bf > 0) {
                $result[$idx]['need_3']['count_bal'][0]++;
                $result[$idx]['need_3']['total_bal'][0] += abs($bal_w1);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $result[$idx]['need_3']['count_bal'][1]++;
                $result[$idx]['need_3']['total_bal'][1] += abs($bal_w2);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $result[$idx]['need_3']['count_bal'][2]++;
                $result[$idx]['need_3']['total_bal'][2] += abs($bal_w3);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $result[$idx]['need_3']['count_bal'][3]++;
                $result[$idx]['need_3']['total_bal'][3] += abs($bal_w4);
            }
        }
        // die(json_encode($result));

        foreach ($result as $index => $row) {
            foreach ($needs as $key => $need) {
                $count_item = $row[$need]['count_item'];
                $total_item = $row[$need]['total_item'];
                for ($i=0; $i < 4; $i++) {
                    $get_count = $row[$need]['count_bal'][$i];
                    $get_total = $row[$need]['total_bal'][$i];

                    $result[$index][$need]['percent_count'][$i] = intval((($count_item - $get_count) / $count_item) * 100);
                    $result[$index][$need]['percent_total'][$i] = intval((($total_item - $get_total) / $total_item) * 100);
                }
            }
        }
        // die(json_encode($result));

        $sheet = 0;
        $excel = $this->spreadsheet_lib->newSpreadsheet();
        $spreadsheet = $excel->getActiveSheet($sheet);
        $excel->getProperties()->setCreator('Pixelphy')
            ->setLastModifiedBy('Pixelphy')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('PT ASKARA TEHNO INDONESIA');

        $bold = ['font' => ['bold' => true]];
        $alignment = ['alignment' => ['horizontal' => 'left', 'vertical' => 'center']];
        $alignment_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];
        $alignment_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];

        $style_row = array_merge([
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ], $alignment);
        $style_row_right = array_merge($style_row, $alignment_right);
        $style_row_center = array_merge($style_row, $alignment_center);
        $style_col = array_merge(
            $bold,
            $style_row_center,
            [
                'font' => ['color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '215C98']
                ]
            ]
        );

        //SHEET 1 ------------------------------------------------------------------------------------------------------------
        //Coloumn
        $columns = [[
            'No',
            'Product Family',
            'Detail',
            date("M-y", strtotime($period)), '', '', '',
            date("M-y", strtotime($period.' +1 month')), '', '', '',
            date("M-y", strtotime($period.' +2 month')), '', '', ''
        ], ['', '', '', 'Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 1', 'Week 2', 'Week 3', 'Week 4']];
        $last_index = count($columns[0]) - 1;
        $title = 'Resume Material Shortage';

        $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
        $drawing->setWidth(50);
        $drawing->setHeight(50);
        // $drawing->setOffsetX(-11);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($spreadsheet);
        $spreadsheet->getColumnDimension('A')->setWidth(8);
        $spreadsheet->getRowDimension(1)->setRowHeight(40);
        $spreadsheet->setCellValue('B1', "PT. BANSHU ELECTRIC INDONESIA");
        $spreadsheet->getStyle('B1')->applyFromArray($alignment);

        $spreadsheet->setCellValue(
            'L1',
            "Print Date : ".date("d M Y H:m:s")."\n".
            "Print By : ".$fullname
        );
        $spreadsheet->getStyle('L1')->applyFromArray($alignment_right);
        $spreadsheet->getStyle('L1')->getAlignment()->setWrapText(true);
        $spreadsheet->mergeCells('L1:O1');

        $spreadsheet->setCellValue('B4', "Period");
        $spreadsheet->getStyle('B4')->applyFromArray($bold);
        $spreadsheet->setCellValue('C4', ": ".date('F Y', strtotime($period)));
        $spreadsheet->getStyle('C4')->applyFromArray($bold);

        $spreadsheet->setCellValue('B5', "Revision");
        $spreadsheet->getStyle('B5')->applyFromArray($bold);
        $spreadsheet->setCellValue('C5', ": ".$revision);
        $spreadsheet->getStyle('C5')->applyFromArray($bold);

        $this->db->distinct()->select("TRIM(name) AS name")
        ->from("plants")->where("number", $plant);
        $spreadsheet->setCellValue('B6', "Plant");
        $spreadsheet->getStyle('B6')->applyFromArray($bold);
        $spreadsheet->setCellValue('C6', ": ".$this->db->get()->row()->name);
        $spreadsheet->getStyle('C6')->applyFromArray($bold);

        $prod_name = 'ALL';
        if (!empty($prodfam)) {
            $prod_name = '';
            $this->db->distinct()->select("TRIM(name) AS name")->from("item_familys")
            ->where_in("number", $prodfam)->order_by("name");
            foreach ($this->db->get()->result() as $key => $value) {
                if (!empty($prod_name)) { $prod_name .= ', '; }
                $prod_name .= $value->name;
            }
        }

        $spreadsheet->setCellValue('B7', "Product Family");
        $spreadsheet->getStyle('B7')->applyFromArray($bold);
        $spreadsheet->setCellValue('C7', ": ".$prod_name);
        $spreadsheet->getStyle('C7')->applyFromArray($bold);

        $row_no = 9;
        foreach ($columns as $index => $column) {
            $alfa = 'A';
            $bf_alfa = '';
            foreach ($column as $key => $value) {
                if ($index == 0) {
                    if ($key == $last_index) {
                        $spreadsheet->setCellValue('A2', strtoupper($title));
                        $spreadsheet->mergeCells('A2:'.$alfa.'2');
                        $spreadsheet->getStyle('A2')->applyFromArray($alignment_center);
                        $spreadsheet->getStyle('A2:'.$alfa.'2')->getFont()->setSize(20);
                    }

                    if (in_array($key, [0,1,2])) { $spreadsheet->mergeCells($alfa.$row_no.':'.$alfa.($row_no + 1)); }
                    elseif (in_array($key, [3, 7, 11])) { $bf_alfa = $alfa; }
                    elseif (in_array($key, [6, 10, 14])) { $spreadsheet->mergeCells($bf_alfa.$row_no.':'.$alfa.$row_no); }
                } elseif ($row_no == 9) { $row_no++; }

                $spreadsheet->setCellValue($alfa.$row_no, $value);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_col);
                if ($key != 0) { $spreadsheet->getColumnDimension($alfa)->setAutoSize(true); }

                $alfa++;
            }
        }
        $row_no++;

        $details = [
            "count_item" => "Total item",
            "count_bal" => "Item SH",
            "total_item" => "Total Qty",
            "total_bal" => "Qty SH",
            "percent_count" => "%-tag Av (ITEM)",
            "percent_total" => "%-tag Av (Qty)"
        ];
        foreach ($result as $index => $row) {
            // if ($index == 1) { die(var_dump($row_no)); }
            $row_no_merge = ($row_no + 5);
            $periods = $row;
            $alfa = 'A';

            unset($periods['prodfam']);
            // die(json_encode($periods));

            $spreadsheet->setCellValue($alfa.$row_no, $index + 1);
            $spreadsheet->mergeCells($alfa.$row_no.':'.$alfa.$row_no_merge);
            $spreadsheet->getStyle($alfa.$row_no.':'.$alfa.$row_no_merge)->applyFromArray($style_row_center); $alfa++;

            $spreadsheet->setCellValue($alfa.$row_no, $row['prodfam']);
            $spreadsheet->mergeCells($alfa.$row_no.':'.$alfa.$row_no_merge);
            $spreadsheet->getStyle($alfa.$row_no.':'.$alfa.$row_no_merge)->applyFromArray($style_row_center);
            $spreadsheet->getColumnDimension($alfa)->setAutoSize(true); $alfa++;

            $set_alfa = $alfa;
            $set_row_no = $row_no;
            foreach ($details as $key => $value) {
                $get_alfa = $set_alfa;
                $spreadsheet->setCellValue($get_alfa.$set_row_no, $value);
                $spreadsheet->getStyle($get_alfa.$set_row_no)->applyFromArray($style_row);
                $spreadsheet->getColumnDimension($get_alfa)->setAutoSize(true); $get_alfa++;

                foreach ($periods as $week => $data) {
                    $get = $data[$key];
                    for ($i=0; $i < 4; $i++) {
                        $val = intval((is_array($get)) ? $get[$i] : $get);
                        $style = [];

                        if (in_array($key, ['percent_count','percent_total'])) {
                            $style = [
                                'font' => ['color' => ['rgb' => 'FFFFFF']],
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF5050']
                                ]
                            ];
                            if ($val >= 100) { $style['fill']['startColor']['rgb'] = '00B050'; }
                            elseif ($val >= 90) {
                                $style['fill']['startColor']['rgb'] = 'FFFF99';
                                unset($style['font']);
                            }
                            $val .= '%';
                        }

                        $spreadsheet->setCellValue($get_alfa.$set_row_no, $val);
                        $spreadsheet->getColumnDimension($get_alfa)->setAutoSize(true);
                        $spreadsheet->getStyle($get_alfa.$set_row_no)->applyFromArray(array_merge($style_row_right, $style));

                        $get_alfa++;
                    }
                }
                $set_row_no++;
            }

            $row_no = $set_row_no;
        }

        // Rename worksheet
        $spreadsheet->setTitle($title);

        $excel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = IOFactory::createWriter($excel, 'Xls');
        $writer->save('php://output');
        exit;
    }
}
