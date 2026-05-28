<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class dashboard_resume_material_shortages extends CI_Controller
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
            $data['button'] = $this->getbutton($this->id_menu());
            $data['base_url'] = base_url('planning/dashboard_resume_material_shortages/');

            $months = [];
            $revisions = [];
            $this->db->distinct()->select("TRIM(p_month) AS month, revision")->from("generate_item_calculation_2")
            ->where(["p_year" => date("Y")])->order_by("month");
            foreach ($this->db->get()->result() as $key => $value) {
                $month = $value->month;
                $rev = $value->revision;

                if (!in_array($month, $months)) { array_push($months, $month); }
                if ($month == date("m") && !in_array($rev, $revisions)) { array_push($revisions, $rev); }
            }

            $data['months'] = [];
            foreach ($months as $key => $value) {
                $data['months'][] = '<option value="'.$value.'" '.(($value == date("m")) ? 'selected' : '').'>'.
                    date('F', strtotime(date("Y-$value-01")))
                .'</option>';
            }

            $data["revisions"] = [];
            foreach ($revisions as $key => $value) {
                $data['revisions'][] = '<option value="'.$value.'" '.(($key == 0) ? 'selected' : '').'>'.$value.'</option>';
            }

            $data["prodfams"] = ['TUBE','ACCESSORIES','VTA','CONNECTOR','TERMINAL','WIRE'];

            $this->load->view('template/header', $data);
            $this->load->view('planning/dashboard_resume_material_shortages');
        } else { redirect('error_access'); }
    }

    public function revision()
    {
        $this->db->distinct()->select("revision")->from("generate_item_calculation_2")
        ->where(["p_year" => date("Y"), "p_month" => base64_decode($this->input->post('periode'))])->order_by("revision");
        echo json_encode($this->db->get()->result());
    }

    public function datatables()
    {
        $revision = base64_decode($this->input->post('revision'));
        $month = base64_decode($this->input->post('periode'));
        $needs = ['need'];

        $format = date("Y")."-$month-01";
        $last = explode("-", date("Y-m", strtotime("-1 month", strtotime($format))));
        $last_year = $last[0];
        $last_month = $last[1];

        $end = date("Y-m-t", strtotime($format));
        $start = date("Y-m-01", strtotime($format));
        $this->dummy = $this->load->database('export', TRUE);

        $pors = [];
        $q = "SELECT TRIM(item_id) AS item, SUM(por_receiveqty) as qty FROM por ".
        "WHERE por_date between '$start' and '$end' GROUP BY item_id ORDER BY item_id";
        foreach ($this->dummy->query($q)->result() as $index => $row) { $pors[$row->item] = $row->qty; }

        $supplys = [];
        $q = "SELECT TRIM(a.item_id) AS item, SUM(abs(b.ith_qty)) as qty ".
        "FROM serial_detail_kanbanrm a JOIN ith b ON a.kanbanrm_id = b.ith_docno and a.item_id = b.ith_item_id ".
        "WHERE b.ith_date between '$start' and '$end' ".
        "GROUP BY a.item_id ORDER BY a.item_id";
        foreach ($this->dummy->query($q)->result() as $index => $row) { $supplys[$row->item] = $row->qty; }

        $datas = [];
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
            "a.need_3 AS need3,".
            "SUM(a.qty) as total,".
            "a.item_rm_id,".
            "b.number AS item_rm_number,".
            "b.name AS item_rm_name,".
            "c.name AS prodfam,".
            "a.balance_wip,".
            "a.stock_whs,".
            "0 AS total_whs,".
            "a.os_supply,".
            "a.stock_hand,".
            "0 AS total_incoming,".
            "0 AS total_supply,".
            "SUM(a.qty) AS total,".
            "a.lttp,".
            "0 AS lttp_bal,".
            "0 AS total_bal,".
            "CONCAT('{',GROUP_CONCAT(CONCAT('\"',a.wp,'\":',a.qty) SEPARATOR ','),'}') AS wps"
        )->from("generate_item_calculation_2 a")
            ->join("item_rm b", "a.item_rm_id = b.id")
            ->join("item_familys c", "b.item_family_id = c.id")
        ->where([
            "a.plant" => "GE",
            "a.p_year" => date("Y"),
            "a.p_month" => $month,
            "a.revision" => $revision
        ])->group_by("c.name, a.item_rm_id")->order_by("prod_name, item");
        // die($this->db->get_compiled_select());
        foreach ($this->db->get()->result() as $index => $row) {
            $prodname = $row->prod_name;
            $arr_prodfam = array_column($datas, 'prodfam');
            if (!in_array($prodname, $arr_prodfam, true)) {
                $set = [];
                foreach ($needs as $key => $value) {
                    $set[$value] = [
                        'count_item' => 0,
                        'count_bal' => [0, 0, 0, 0, 0],
                        'total_item' => 0,
                        'total_bal' => [0, 0, 0, 0, 0],
                        'percent_count' => [0, 0, 0, 0, 0],
                        'percent_total' => [0, 0, 0, 0, 0],
                        'items' => [[],[],[],[],[]]
                    ];
                }
                array_push($datas, array_replace(['prodfam' => $prodname], $set));
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
                $datas[$idx]['need']['count_item']++;
                $datas[$idx]['need']['total_item'] += $need_all;
            }

            $wps = json_decode($row->wps, true);
            $incoming = $pors[$row->item_rm_number] ?? 0;
            $supply = $supplys[$row->item_rm_number] ?? 0;
            $items = [
                "item_rm_id" => $row->item_rm_id,
                "item_rm_number" => $row->item_rm_number,
                "item_rm_name" => $row->item_rm_name,
                "prodfam" => $row->prodfam,
                "balance_wip" => $row->balance_wip,
                "stock_whs" => $row->stock_whs,
                "total_whs" => floatval($row->stock_whs) + floatval($row->balance_wip),
                "os_supply" => $row->os_supply,
                "stock_hand" => $row->stock_hand,
                "total_incoming" => $incoming,
                "total_supply" => $supply,
                "total" => round($row->total),
                "lttp" => round($row->lttp),
                "lttp_bal" => round($row->total) - round($row->lttp),
                "wp_need" => 0,
                "wp_bal" => 0
            ];

            if ($bal_w1 < 0) {
                $items["wp_need"] = $need_w1;
                $items["wp_bal"] = $bal_w1;
                $datas[$idx]['need']['count_bal'][0]++;
                $datas[$idx]['need']['total_bal'][0] += abs($bal_w1);
                array_push($datas[$idx]['need']['items'][0], $items);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $items["wp_need"] = $need_w2;
                $items["wp_bal"] = $bal_w2;
                $datas[$idx]['need']['count_bal'][1]++;
                $datas[$idx]['need']['total_bal'][1] += abs($bal_w2);
                array_push($datas[$idx]['need']['items'][1], $items);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $items["wp_need"] = $need_w3;
                $items["wp_bal"] = $bal_w3;
                $datas[$idx]['need']['count_bal'][2]++;
                $datas[$idx]['need']['total_bal'][2] += abs($bal_w3);
                array_push($datas[$idx]['need']['items'][2], $items);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $items["wp_need"] = $need_w4;
                $items["wp_bal"] = $bal_w4;
                $datas[$idx]['need']['count_bal'][3]++;
                $datas[$idx]['need']['total_bal'][3] += abs($bal_w4);
                array_push($datas[$idx]['need']['items'][3], $items);
            }
        }

        foreach ($datas as $index => $row) {
            foreach ($needs as $key => $need) {
                $count_item = $row[$need]['count_item'];
                $total_item = $row[$need]['total_item'];
                $sum_count = 0;
                $sum_total = 0;
                for ($i=0; $i < 5; $i++) {
                    $get_count = ($i != 4) ? $row[$need]['count_bal'][$i] : $sum_count;
                    $get_total = ($i != 4) ? $row[$need]['total_bal'][$i] : $sum_total;
                    if ($i != 4) { $sum_count += $get_count; $sum_total += $get_total; }
                    else {
                        $datas[$index][$need]['count_bal'][$i] = $sum_count;
                        $datas[$index][$need]['total_bal'][$i] = $sum_total;
                    }

                    $datas[$index][$need]['percent_count'][$i] = ($count_item == 0) ? 0 : intval((($count_item - $get_count) / $count_item) * 100);
                    $datas[$index][$need]['percent_total'][$i] = ($total_item == 0) ? 0 : intval((($total_item - $get_total) / $total_item) * 100);
                }
            }
        }

        $last_data = [];
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
        ->where([
            "a.plant" => "GE",
            "a.p_year" => $last_year,
            "a.p_month" => $last_month,
            "a.revision" => $revision
        ])->group_by("c.name, a.item_rm_id")->order_by("prod_name, item");
        // die($this->db->get_compiled_select());
        foreach ($this->db->get()->result() as $index => $row) {
            $prodname = $row->prod_name;
            $arr_prodfam = array_column($last_data, 'prodfam');
            if (!in_array($prodname, $arr_prodfam, true)) {
                $set = [];
                foreach ($needs as $key => $value) {
                    $set[$value] = [
                        'count_item' => 0,
                        'count_bal' => [0, 0, 0, 0, 0],
                        'total_item' => 0,
                        'total_bal' => [0, 0, 0, 0, 0],
                        'percent_count' => [0, 0, 0, 0, 0],
                        'percent_total' => [0, 0, 0, 0, 0],
                        'items' => [[],[],[],[],[]]
                    ];
                }
                array_push($last_data, array_replace(['prodfam' => $prodname], $set));
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
                $last_data[$idx]['need']['count_item']++;
                $last_data[$idx]['need']['total_item'] += $need_all;
            }

            if ($bal_w1 < 0) {
                $last_data[$idx]['need']['count_bal'][0]++;
                $last_data[$idx]['need']['total_bal'][0] += abs($bal_w1);
                array_push($last_data[$idx]['need']['items'][0], $row->item_id);
            }
            if ($bal_w2 < 0 && $bal_w1 > 0) {
                $last_data[$idx]['need']['count_bal'][1]++;
                $last_data[$idx]['need']['total_bal'][1] += abs($bal_w2);
                array_push($last_data[$idx]['need']['items'][1], $row->item_id);
            }
            if ($bal_w3 < 0 && $bal_w2 > 0) {
                $last_data[$idx]['need']['count_bal'][2]++;
                $last_data[$idx]['need']['total_bal'][2] += abs($bal_w3);
                array_push($last_data[$idx]['need']['items'][2], $row->item_id);
            }
            if ($bal_w4 < 0 && $bal_w3 > 0) {
                $last_data[$idx]['need']['count_bal'][3]++;
                $last_data[$idx]['need']['total_bal'][3] += abs($bal_w4);
                array_push($last_data[$idx]['need']['items'][3], $row->item_id);
            }
        }

        foreach ($last_data as $index => $row) {
            foreach ($needs as $key => $need) {
                $count_item = $row[$need]['count_item'];
                $total_item = $row[$need]['total_item'];
                $sum_count = 0;
                $sum_total = 0;
                for ($i=0; $i < 5; $i++) {
                    $get_count = ($i != 4) ? $row[$need]['count_bal'][$i] : $sum_count;
                    $get_total = ($i != 4) ? $row[$need]['total_bal'][$i] : $sum_total;
                    if ($i != 4) { $sum_count += $get_count; $sum_total += $get_total; }
                    else {
                        $last_data[$index][$need]['count_bal'][$i] = $sum_count;
                        $last_data[$index][$need]['total_bal'][$i] = $sum_total;
                    }

                    $last_data[$index][$need]['percent_count'][$i] = ($count_item == 0) ? 0 : intval((($count_item - $get_count) / $count_item) * 100);
                    $last_data[$index][$need]['percent_total'][$i] = ($total_item == 0) ? 0 : intval((($total_item - $get_total) / $total_item) * 100);
                }
            }
        }

        echo json_encode(["now" => $datas, "last" => $last_data]);
    }
}
