<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LME_prices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['cols'] = [];

            $f_type = $this->input->get('f_type');

            $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
                ->from('lme_ranges');
            if (!empty($f_type)) {
                $this->db->where('type', $f_type);
            }
            $this->db->order_by('CAST(lme_start AS UNSIGNED)', 'ASC');
            foreach ($this->db->get()->result_object() as $key => $value) {
                $data['cols']['lme' . ($key + 1)] = $value->ranges;
            }
            $data['colspan'] = count($data['cols']);

            $this->load->view('template/header', $data);
            $this->load->view('planning/lme_prices');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $lme_prices = $this->crud->reads('lme_prices');
        echo json_encode($lme_prices);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(a.item_id) AS item")
            ->from("supplier_items a")->join("item_rm b", "a.item_rm_id = b.id")
            ->where("b.item_family_id", "P01");
        if (!empty($post)) {
            $this->db->like('a.item_id', $post);
        }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readPlants()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(number) AS number, TRIM(name) AS name")
            ->from("plants a")->where("number <> 'GS'");
        if (!empty($post)) {
            $this->db->like('a.number', $post);
        }
        $this->db->order_by("name");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readMakers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(c.number) AS code, TRIM(c.name) AS maker")
            ->from("supplier_items a")
            ->join("item_rm b", "a.item_rm_id = b.id")
            ->join("makers c", "a.maker_id = c.id")
            ->where("b.item_family_id", "P01");
        if (!empty($post)) {
            $this->db->group_start()
                ->like("c.number", $post)
                ->or_like("c.name", $post)
                ->group_end();
        }
        $this->db->order_by("maker");
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());

        echo json_encode($get);
    }

    public function readLMERanges()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
            ->from("lme_ranges");
        if (!empty($post)) {
            $this->db->like("CONCAT(lme_start,'-',lme_end)", $post);
        }
        $this->db->order_by("id");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readPartNoBEI()
    {
        $maker = isset($_POST['maker']) ? base64_decode($_POST['maker']) : "";
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("number as item")
            ->from("item_rm")
            ->where("item_family_id", "P01");
        // if (!empty($maker)) {
        //     $this->db->where("maker_id", $maker);
        // }
        if (!empty($post)) {
            $this->db->like("number", $post);
        }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());

        echo json_encode($get);
    }

    public function readItemsSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(c.number) AS id, TRIM(c.name) AS name")
            ->from("supplier_items a")
            ->join("item_rm b", "a.item_rm_id = b.id")
            ->join("suppliers c", "a.supplier_id = c.id")
            ->where("b.item_family_id", "P01");
        if (!empty($post)) {
            $this->db->group_start()
                ->like("c.number", $post)
                ->or_like("c.name", $post)
                ->group_end();
        }
        $this->db->order_by("name");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readExchangeRates()
    {
        $today = date('Y-m-d');
        $this->db->select("*")
            ->from('exchange_rates');
        // ->where('start_date <=', $today)
        // ->where('end_date >=', $today);
        $get = $this->db->get()->result_object();
        echo json_encode($get);
    }

    public function historys()
    {
        $id = base64_decode($this->input->post('id'));
        $f_exchange_rate = base64_decode($this->input->post('f_exchange_rate'));
        $f_display = base64_decode($this->input->post('f_display'));
        $result = ['span' => [], 'data' => []];
        $ranges = [];

        $this->db->select(
            "TRIM(a.id) AS id,"
                . "TRIM(a.data_lme) AS prices,"
                . "TRIM(a.maker_code) AS maker_code,"
                . "TRIM(a.currency) AS currency,"
                . "TRIM(a.ven_id) AS ven_id,"
                . "TRIM(a.type) AS type,"
                . "TRIM(g.uom) AS uom,"
                . "TRIM(a.item_id) AS part_no,"
                . "COALESCE(TRIM(d.name), '') AS supplier,"
                . "COALESCE(TRIM(f.name), '') AS maker_name,"
                . "COALESCE(TRIM(h.name), '') AS plant_name,"
                . "COALESCE(TRIM(a.plant_id), '') AS plant_id,"
                . "a.created_date AS u_date,"
                . "TRIM(b.name) AS u_by"
        )->from('lme_price_logs a')
            ->join('users b', 'a.created_by = b.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_rm_id', 'left')
            ->join('makers f', 'a.maker_code = f.number', 'left')
            ->join('item_rm g', 'a.item_id = g.number', 'left')
            ->join('plants h', 'a.plant_id = h.number', 'left')
            ->where("price_id", $id)->order_by("id", 'DESC');
        $get = $this->db->get()->result_object();
        foreach ($get as $index => $column) {
            $range = json_decode($column->prices);
            foreach ($range as $key => $value) {
                $idx = intval(explode('-', $value->range)[0]);
                if (!isset($ranges[$idx])) {
                    $ranges[$idx] = $value->range;
                }
            }
            array_push($result['data'], json_decode(json_encode($column), true));
        }
        ksort($ranges, SORT_NUMERIC);
        $ranges = array_values($ranges);

        foreach ($ranges as $key => $value) {
            array_push($result['span'], [
                'field' => 'lme_' . $key,
                'title' => $value,
                'width' => '100',
                'halign' => 'center'
            ]);
        }

        foreach ($result['data'] as $index => $column) {
            $range = json_decode($column['prices'], true);
            foreach ($ranges as $key => $value) {
                $idx = array_search($value, array_column($range, 'range'));
                $price = (gettype($idx) == 'boolean') ? 0 : $range[$idx]['price'];

                if (!empty($f_display) && $f_display == 'USD CURRENCY' && !empty($f_exchange_rate) && $column['currency'] != 'USD') {
                    $price = $price * floatval($f_exchange_rate);
                }

                $result['data'][$index]['lme_' . $key] = round($price, 4);
            }
        }

        echo json_encode($result);
    }

    public function datatables()
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        $f_lme_range = base64_decode($this->input->get('f_lme_range'));
        $f_supplier = base64_decode($this->input->get('f_supplier'));
        $f_part_no = base64_decode($this->input->get('f_part_no'));
        $f_maker = base64_decode($this->input->get('f_maker'));
        $f_plant = base64_decode($this->input->get('f_plant'));
        $f_type = base64_decode($this->input->get('f_type'));
        $f_exchange_rate = base64_decode($this->input->get('f_exchange_rate'));
        $f_display = base64_decode($this->input->get('f_display'));

        $get = $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
            ->from("lme_ranges");
        if (!empty($f_type)) {
            $this->db->where("type", $f_type);
        }
        $get = $get->order_by("CAST(lme_start AS UNSIGNED)", "ASC")->get()->result_object();
        $ranges = [];
        $cols = "";
        foreach ($get as $key => $value) {
            $id = "lme" . ($key + 1);
            $cols .= "0 AS $id,";
            $ranges[$value->ranges] = $id;
        }

        //Select Query
        $this->db->select(
            "TRIM(a.id) AS id,"
                . "TRIM(a.data_lme) AS prices,"
                . "TRIM(a.currency) AS currency,"
                . "TRIM(a.maker_code) AS maker_code,"
                . "TRIM(a.ven_id) AS ven_id,"
                . "TRIM(a.item_id) AS part_no,"
                . "TRIM(a.type) AS type,"
                . "TRIM(g.uom) AS uom,"
                . "COALESCE(TRIM(d.name), '') AS supplier,"
                . "COALESCE(TRIM(f.name), '') AS maker_name,"
                . "COALESCE(TRIM(h.name), '') AS plant_name,"
                . "COALESCE(TRIM(a.plant_id), '') AS plant_id,"
                . $cols
                . "COALESCE(a.updated_date, a.created_date) AS u_date,"
                . "COALESCE(TRIM(c.name), TRIM(b.name)) AS u_by"
        )->from('lme_prices a')
            ->join('users b', 'a.created_by = b.username', 'left')
            ->join('users c', 'a.updated_by = c.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_rm_id', 'left')
            ->join('makers f', 'a.maker_code = f.number', 'left')
            ->join('item_rm g', 'a.item_id = g.number', 'left')
            ->join('plants h', 'a.plant_id = h.number', 'left');
        if (!empty($f_lme_range)) {
            $this->db->like("a.data_lme", $f_lme_range);
        }
        if (!empty($f_supplier)) {
            $this->db->where("a.ven_id", $f_supplier);
        }
        if (!empty($f_part_no)) {
            $this->db->where("a.item_id", $f_part_no);
        }
        if (!empty($f_maker)) {
            $this->db->where("a.maker_code", $f_maker);
        }
        if (!empty($f_plant)) {
            $this->db->where("a.plant_id", $f_plant);
        }
        if (!empty($f_type)) {
            $this->db->where("a.type", $f_type);
        }
        $this->db->order_by('id');
        $result = $this->db->get()->result_array();
        // die($this->db->last_query());
        foreach ($result as $key => $value) {
            foreach (json_decode($value['prices']) as $key2 => $value2) {
                if (isset($ranges[$value2->range])) {
                    $price = floatval($value2->price);
                    if (!empty($f_display) && $f_display == 'USD CURRENCY' && !empty($f_exchange_rate) && $value['currency'] != 'USD') {
                        $price = $price * floatval($f_exchange_rate);
                    }
                    $result[$key][$ranges[$value2->range]] = $price;
                }
            }
        }

        echo json_encode(array('total' => count($result), 'rows' => array_splice($result, $offset, $rows)));
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $plant = $post['plant'];
            $maker = $post['maker'];
            $part_no = $post['part_no'];
            $supp_id = $post['supp_id'];
            $currency = $post['currency'];
            $type = $post['type'];
            // die(var_dump($post));

            $exists = $this->db->where('maker_code', $maker)->where('ven_id', $supp_id)->where('item_id', $part_no)->where('plant_id', $plant)
                ->get('lme_prices')->row();
            if ($exists) {
                show_error("Data already available!");
            } else {
                $send = $this->crud->create('lme_prices', [
                    'maker_code' => $maker,
                    'ven_id' => $supp_id,
                    'item_id' => $part_no,
                    'plant_id' => $plant,
                    'currency' => $currency,
                    'type' => $type,
                    'data_lme' => $post['datas']
                ], "LMEP", "lme_prices");
                $get = json_decode($send, true);
                if ($get['theme'] == 'success') {
                    $this->crud->create('lme_price_logs', [
                        'price_id' => $get['id'],
                        'maker_code' => $maker,
                        'ven_id' => $supp_id,
                        'item_id' => $part_no,
                        'plant_id' => $plant,
                        'currency' => $currency,
                        'type' => $type,
                        'data_lme' => $post['datas']
                    ], "LMEH", "lme_price_logs");
                }
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $data = [];

            $get = $this->db->select(
                "TRIM(maker_code) AS maker,"
                    . "TRIM(item_id) AS part_no,"
                    . "TRIM(ven_id) AS supp_id,"
                    . "TRIM(plant_id) AS plant,"
                    . "TRIM(type) AS type,"
                    . "TRIM(currency) AS currency,"
                    . "TRIM(data_lme) AS datas"
            )->from('lme_prices')->where('id', $id)->get()->row();
            // die($this->db->last_query());
            // die(var_dump($get));
            $maker = $get->maker;
            $part_no = $get->part_no;
            $supp_id = $get->supp_id;
            $plant = $get->plant;
            $type = $get->type;
            $currency = $get->currency;
            $datas = json_decode($get->datas);

            $merge = array_merge($datas, json_decode($post['datas']));
            // die(var_dump($merge));
            foreach ($merge as $key => $value) {
                $data[$value->range] = $value;
            }
            $data = json_encode(array_values($data));

            $send = $this->crud->update('lme_prices', ["id" => $id], ['data_lme' => $data]);
            $get = json_decode($send, true);
            if ($get['theme'] == 'success') {
                $this->crud->create('lme_price_logs', [
                    'price_id' => $get['id'],
                    'maker_code' => $maker,
                    'ven_id' => $supp_id,
                    'item_id' => $part_no,
                    'plant_id' => $plant,
                    'currency' => 'USD',
                    'data_lme' => $data
                ], "LMEH", "lme_price_logs");
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('lme_prices', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $datas = [];
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);

        $data->setOutputEncoding('UTF-8');
        $data = $data->sheets[0]['cells'];
        unset($data[1]);
        // die(var_dump($data));

        foreach ($data as $key => $value) {
            array_push($datas, [
                'plant' => $value[1],
                'item' => $value[2],
                'maker' => $value[3],
                'supp' => $value[4],
                'range' => $value[5],
                'price' => number_format($value[6], 4, '.', ''),
                'type' => $value[7],
                'currency' => $value[8],
            ]);
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $result = ['title' => '', 'message' => '', 'theme' => 'error'];
            $act = '';
            $data = $this->input->post('data');
            $range = $data['range'];
            $maker = $data['maker'];
            $plant = $data['plant'];
            $supp = $data['supp'];
            $item = $data['item'];
            $price = $data['price'];
            $range_arr = explode('-', $range);
            $type = $data['type'];
            $currency = $data['currency'];

            $makers = $this->crud->read('makers', [], ['number' => $maker]);
            $plants = $this->crud->read('plants', [], ['number' => $plant]);
            $suppliers = $this->crud->read('suppliers', [], ['number' => $supp]);
            $currency = $this->crud->read('currencies', [], ['name' => $currency]);
            // $items = $this->crud->read('item_rm', [], ['item_id' => $item]);
            $ranges = $this->crud->read('lme_ranges', [], [
                "lme_start" => $range_arr[0],
                "lme_end" => $range_arr[1]
            ]);

            // if (empty($items)) {
            //     $result['title'] = 'Undefine';
            //     $result['message'] = "Item ID $item no found!";
            // } else
            if (empty($makers)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Maker Code $maker no found!";
            } elseif (empty($plants)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Plant Code $plant no found!";
            } elseif (empty($suppliers)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Supplier Code $supp no found!";
            } elseif (empty($ranges)) {
                $result['title'] = 'Undefine';
                $result['message'] = "LME Range $range no found!";
            } elseif (empty($currency)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Currency $currency no found!";
            } elseif (empty($type) || ! in_array($type, ['LME', 'SHMET'])) {
                $result['title'] = 'Undefine';
                $result['message'] = "Type $type no found!";
            } else {
                $id = 'in';
                $exists = $this->db->where('maker_code', $maker)->where('ven_id', $supp)->where('item_id', $item)->where('plant_id', $plant)
                    ->get('lme_prices')->row();
                if ($exists) {
                    $id = $exists->id;
                }
            }

            if (!empty($result['title'])) {
                echo json_encode($result);
            } else {
                $datas = json_decode(json_encode([['range' => $range, 'price' => $price]]));
                $price_id = $id;

                if ($id == 'in') {
                    $lme = json_encode($datas);
                    $data = [
                        'maker_code' => $maker,
                        'plant_id' => $plant,
                        'ven_id' => $supp,
                        'item_id' => $item,
                        'type' => $data['type'],
                        'currency' => $data['currency'],
                        'data_lme' => $lme
                    ];

                    $send = $this->crud->create('lme_prices', $data, "LMEP", "lme_prices");
                    $price_id = $this->crud->read("lme_prices", [], $data)->id;
                } else {
                    $this->db->select("TRIM(data_lme) AS datas")->from('lme_prices')->where('id', $id);
                    // die($this->db->last_query());
                    $get = json_decode($this->db->get()->row()->datas);

                    $lme = [];
                    $merge = array_merge($get, $datas);
                    foreach ($merge as $key => $value) {
                        $lme[$value->range] = $value;
                    }
                    $lme = json_encode(array_values($lme));

                    $send = $this->crud->update('lme_prices', ["id" => $id], [
                        'data_lme' => $lme,
                        'type' => $data['type'],
                        'currency' => $data['currency']
                    ]);
                }
                $get = json_decode($send, true);
                if ($get['theme'] == 'success') {
                    $this->crud->create('lme_price_logs', [
                        'price_id' => $price_id,
                        'maker_code' => $maker,
                        'plant_id' => $plant,
                        'ven_id' => $supp,
                        'item_id' => $item,
                        'type' => $data['type'],
                        'currency' => $data['currency'],
                        'data_lme' => $lme
                    ], "LMEH", "lme_price_logs");
                }

                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Y-m-d H:i:s");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=LME Range $format.xls");
        }

        $get = $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
            ->from("lme_ranges")->order_by("id")->get()->result_object();
        $ranges = [];
        $cols = "";
        $ths = "";
        foreach ($get as $key => $value) {
            $id = "lme" . ($key + 1);
            $cols .= "0 AS $id,";
            $ths .= '<th>' . $value->ranges . '</th>';
            $ranges[$value->ranges] = $id;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $f_lme_range = base64_decode($this->input->get('f_lme_range'));
        $f_supplier = base64_decode($this->input->get('f_supplier'));
        $f_part_no = base64_decode($this->input->get('f_part_no'));
        $f_maker = base64_decode($this->input->get('f_maker'));
        $f_plant = base64_decode($this->input->get('f_plant'));
        $f_type = base64_decode($this->input->get('f_type'));
        $f_exchange_rate = base64_decode($this->input->get('f_exchange_rate'));
        $f_display = base64_decode($this->input->get('f_display'));
        $this->db->select(
            "TRIM(a.id) AS id,"
                . "TRIM(a.data_lme) AS prices,"
                . "TRIM(a.currency) AS currency,"
                . "TRIM(a.maker_code) AS maker_code,"
                . "TRIM(a.ven_id) AS ven_id,"
                . "TRIM(a.item_id) AS part_no,"
                . "TRIM(a.type) AS type,"
                . "TRIM(g.uom) AS uom,"
                . "COALESCE(TRIM(a.plant_id), '') AS plant_id,"
                . "COALESCE(TRIM(h.name), '') AS plant_name,"
                . "COALESCE(TRIM(d.name), '') AS supplier,"
                . "COALESCE(TRIM(f.name), '') AS maker_name,"
                . $cols
                . "COALESCE(a.updated_date, a.created_date) AS u_date,"
                . "COALESCE(TRIM(c.name), TRIM(b.name)) AS u_by"
        )->from('lme_prices a')
            ->join('users b', 'a.created_by = b.username', 'left')
            ->join('users c', 'a.updated_by = c.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_rm_id', 'left')
            ->join('makers f', 'a.maker_code = f.number', 'left')
            ->join('item_rm g', 'a.item_id = g.number', 'left')
            ->join('plants h', 'a.plant_id = h.number', 'left');
        if (!empty($f_lme_range)) {
            $this->db->like("a.data_lme", $f_lme_range);
        }
        if (!empty($f_supplier)) {
            $this->db->where("a.ven_id", $f_supplier);
        }
        if (!empty($f_part_no)) {
            $this->db->where("a.item_id", $f_part_no);
        }
        if (!empty($f_maker)) {
            $this->db->where("a.maker_code", $f_maker);
        }
        if (!empty($f_plant)) {
            $this->db->where("a.plant_id", $f_plant);
        }
        $this->db->order_by('id');

        $records = $this->db->get()->result_array();
        foreach ($records as $key => $value) {
            foreach (json_decode($value['prices']) as $key2 => $value2) {
                if (isset($ranges[$value2->range])) {
                    $records[$key][$ranges[$value2->range]] = floatval($value2->price);
                }
            }
        }

        if ($f_type == 'LME') {
            $type = 'TYPE : LME';
        } else {
            $type = 'TYPE : SHMET';
        }

        if ($f_display == 'ORIGINAL CURRENCY') {
            $currency_head = 'ORIGINAL CURRENCY';
        } else {
            $currency_head = 'USD CURRENCY';
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#lme_prices {border-collapse: collapse;width: 100%;font-size: 12px;}#lme_prices td, #lme_prices th {border: 1px solid #ddd;padding: 2px;}#lme_prices tr:nth-child(even){background-color: #f2f2f2;}#lme_prices tr:hover {background-color: #ddd;}#lme_prices th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>Setup Price LME</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $fullname . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="lme_prices" border="1">
            <tr>
                <th width="20" rowspan="2">No</th>
                <th rowspan="2">Plant ID</th>
                <th rowspan="2">Item ID</th>
                <th rowspan="2">Supplier</th>
                <th rowspan="2">WIRE Name Maker</th>
                <th rowspan="2">Part No</th>
                <th rowspan="2">' . $currency_head . '</th>
                <th rowspan="2">UoM</th>
                <th colspan="' . count($ranges) . '">' . $type . '</th>
                <th rowspan="2">Last Date</th>
                <th rowspan="2">By</th>
            </tr>
            <tr>
                ' . $ths . '
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                <td align="center">' . $no . '</td>
                <td>' . $data['plant_id'] . '</td>
                <td>' . $data['part_no'] . '</td>
                <td>' . $data['supplier'] . '</td>
                <td>' . $data['maker_name'] . '</td>
                <td>' . $data['part_no'] . '</td>
                <td>' . $data['currency'] . '</td>
                <td>' . $data['uom'] . '</td>';
            foreach ($ranges as $key => $value) {
                $price = floatval($data[$value]);
                if (!empty($f_display) && $f_display == 'USD CURRENCY' && !empty($f_exchange_rate) && $data['currency'] != 'USD') {
                    $price = $price * floatval($f_exchange_rate);
                }
                $html .= '<td>' . round($price, 4) . '</td>';
            }
            $html .= '<td align="center">' . $data['u_date'] . '</td>
                <td>' . $data['u_by'] . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
