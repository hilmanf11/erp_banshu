<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supplier_items extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('supplier_id', 'Vendor', 'required|min_length[1]|max_length[20]|is_unique[supplier_items.supplier_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[supplier_items.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/supplier_items');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('supplier_items', ["supplier_id" => $post]);
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_family_id = explode(",", $this->input->get('item_family_id'));
        $supplier_id = $this->input->get('supplier_id');

        $this->db->select('a.*, b.currency, c.number as item_number, c.name as item_name, c.uom'); //c.specification
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where_in('c.item_family_id', $item_family_id);
        $this->db->like('a.supplier_id', $supplier_id);
        $this->db->like('c.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readVendor()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT item_supplier FROM supplier_items WHERE item_supplier != '' ORDER BY item_supplier ASC");
        echo json_encode($send);
    }

    public function readShareOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT share_order FROM supplier_items ORDER BY share_order ASC");
        echo json_encode($send);
    }

    public function readItem()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = $this->input->get('supplier_id');

        $this->db->select('a.*, b.currency, c.number as item_number, c.name as item_name');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->like('c.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_number = $this->input->get('item_number');
        $item_id = $this->input->get('item_rm_id');
        $item_family_id = $this->input->get('item_family_id');

        $this->db->select('b.*, c.number as item_number, a.mpq, a.moq, a.price, a.share_order, b.supplier_po');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->like("c.number", $item_number);
        $this->db->like("c.id", $item_id);
        $this->db->like("d.id", $item_family_id);
        $this->db->like("b.name", $post);
        $this->db->group_by('b.number');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_plant = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers = base64_decode($this->input->get('filter_suppliers'));
        $filter_category = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_item_rm = base64_decode($this->input->get('filter_item_rm'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_status_price = base64_decode($this->input->get('filter_status_price'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_check = $this->input->get('filter_check');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select("a.*, c.number as item_rm_number, c.uom, g.uom_soft as uom_convertion,
            c.name as item_rm_name, a.item_supplier, b.number as supplier_number, b.origin, b.name as supplier_name, e.origin as maker_origin,
            d.name as item_family_name, b.type, b.supplier_po, e.name as maker_name, e.type as maker_type,
            j.name AS incoterm, 
            case when a.currency = 'IDR' then 1 else i.middle end as exchange_rate , 
            k.country_name, k.total_ddp,
            coalesce((a.price * (case when a.currency = 'IDR' then 1 else i.middle end))+ (a.price * (case when a.currency = 'IDR' then 1 else i.middle end) * COALESCE(k.total_ddp, 0)), 0) AS price_procurement");
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('item_rm c', 'c.id = a.item_rm_id');
        $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
        $this->db->join('makers e', 'a.maker_id = e.id', 'left');
        $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->join("exchange_rates i", 'i.start_date <= a.valid_date AND i.end_date >= a.valid_date and i.currency_from = a.currency', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_rm_id FROM supplier_items GROUP BY item_rm_id HAVING COUNT(*) > 1) h", 'a.item_rm_id = h.item_rm_id');
        }
        $this->db->join("incoterms j", "a.incoterm_code = j.number", "LEFT");
        $this->db->join("(
                        select ja.*, jb.name as country_name from setup_incoterms ja 
                        left join origins jb on ja.country_number = jb.number
                        ) k", "a.incoterm_code = k.incoterm_number and b.origin=k.country_name", "LEFT");
        if ($filter_category != "") {
            $this->db->like('c.item_family_id', $filter_category);
        }
        if ($filter_type != "") {
            $this->db->like('b.type', $filter_type);
        }
        if ($filter_suppliers != "") {
            $this->db->like('a.supplier_id', $filter_suppliers);
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_item_rm != "") {
            $this->db->like('a.item_rm_id', $filter_item_rm);
        }
        if ($filter_maker != "") {
            $this->db->like('e.number', $filter_maker);
        }
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_status_price == "Approved") {
            $this->db->where("a.approved_to is null or a.approved_to = ''");
        } elseif ($filter_status_price == "Checking") {
            $this->db->where("a.approved_to != ''");
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('c.number', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data

        $sortedRecords = $records;
        usort($sortedRecords, function ($a, $b) {
            $cmp = strcmp($a['item_rm_id'], $b['item_rm_id']);
            if ($cmp !== 0) {
                return $cmp;
            }

            $plantCmp = strcmp((string) $a['plant'], (string) $b['plant']);
            if ($plantCmp !== 0) {
                return $plantCmp;
            }

            return $a['price_procurement'] <=> $b['price_procurement'];
        });

        $cheaperMap = [];
        $lastGroupKey = null;
        $cheaperRank = 0;
        foreach ($sortedRecords as $row) {
            $groupKey = $row['item_rm_id'] . '|' . $row['plant'];
            if ($lastGroupKey !== $groupKey) {
                $lastGroupKey = $groupKey;
                $cheaperRank = 1;
            } else {
                $cheaperRank++;
            }
            $lookupKey = $row['supplier_id'] . '|' . $row['item_rm_id'] . '|' . $row['plant'];
            $cheaperMap[$lookupKey] = 'CHEAPER ' . $cheaperRank;
        }

        foreach ($records as $key => $value) {
            $lookupKey = $value['supplier_id'] . '|' . $value['item_rm_id'] . '|' . $value['plant'];
            $records[$key]['cheaper'] = isset($cheaperMap[$lookupKey]) ? $cheaperMap[$lookupKey] : '';
        }
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    public function datatableHistory($price = "")
    {
        $item_rm_id = base64_decode($this->input->get('item_rm_id'));
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select('a.*');
        $this->db->from('(SELECT * FROM supplier_item_histories ORDER BY created_date DESC) a');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('a.item_rm_id', $item_rm_id);
        // if($price == "yes"){
        //     $this->db->group_by('a.price');
        //     $this->db->group_by('a.price');
        // }else{
        //     $this->db->group_by('a.spq');
        //     $this->db->group_by('a.mpq');
        //     $this->db->group_by('a.moq');
        //     $this->db->group_by('a.leadtime');
        //     $this->db->group_by('a.share_order');
        // }
        $this->db->order_by('a.created_date', 'DESC');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $item_rm_id = $post['item_rm_id'];
            $plant = $post['plant'];
            $supplier_items = $this->crud->read('supplier_items', [], ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id'], "plant" => $plant]);
            $sqlSupItm = $this->db->query("SELECT SUM(share_order) as share_order FROM supplier_items WHERE item_rm_id = '$item_rm_id' and plant = '$plant' GROUP BY item_rm_id");
            $rowSupItm = $sqlSupItm->row();

            if (empty(@$post['valid_date'])) {
                $valid_date = "";
            } else {
                $valid_date = $post['valid_date'];
            }

            $history = array(
                "supplier_id" => $post['supplier_id'],
                "item_rm_id" => $post['item_rm_id'],
                "item_supplier" => $post['item_supplier'],
                "maker_id" => $post['maker_id'],
                "plant" => $post['plant'],
                "mpq" => $post['mpq'],
                "moq" => $post['moq'],
                "share_order" => $post['share_order'],
                "transit" => $post['transit'],
                "leadtime" => $post['leadtime'],
                "safety_stock" => $post['safety_stock'],
                "price" => $post['price'],
                "currency" => $post['currency'],
                "start_date" => $post['start_date'],
                "valid_date" => $valid_date,
                "calculate" => $post['calculate'],
                "remarks" => $post['remarks'],
            );

            if ((@$rowSupItm->share_order + $post['share_order']) <= 100 && (@$rowSupItm->share_order + $post['share_order']) != 0) {
                if ($post['mpq'] > 0 && $post['moq'] > 0 && $post['leadtime'] > 0) {
                    if (!$supplier_items) {
                        $attachment = $this->crud->upload('attachment', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items/', [], "supplier_items", "attachment");
                        $attachment2 = $this->crud->upload('attachment2', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items2/', [], "supplier_items", "attachment2");
                        $attachment3 = $this->crud->upload('attachment3', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items3/', [], "supplier_items", "attachment3");
                        $postFinal = array_merge($post, ["attachment" => $attachment, "attachment2" => $attachment2, "attachment3" => $attachment3]);

                        $send = $this->crud->create('supplier_items', $postFinal);
                        $this->crud->create("supplier_item_histories", $history);
                        echo $send;
                    } else {
                        // if($supplier_items->price == $post['price']){
                        //     $this->db->update('supplier_items', $post, ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id']]);
                        //     echo json_encode(array("title" => "Good Job", "message" => "Change Data Success, By Pass Approval", "theme" => "success"));
                        // }else{
                        $send = $this->crud->update('supplier_items', ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id']], $post);
                        echo $send;

                        $this->crud->create("supplier_item_histories", $history);
                        // }
                    }
                } else {
                    echo json_encode(array("title" => "MPQ 0", "message" => "MPQ, MOQ, Leadtime, Transit for Part No " . $post['item_rm_id'] . " Cannot <= 0", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Share Order", "message" => "Share Order for Part No " . $post['item_rm_id'] . " Cannot >= 100", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $read = $this->crud->read("supplier_items", ["id" => $id]);

            $history = array(
                "supplier_id" => $read->supplier_id,
                "item_rm_id" => $read->item_rm_id,
                "item_supplier" => $post['item_supplier'],
                "plant" => $read->plant,
                "maker_id" => $post['maker_id'],
                "type" => $read->type,
                "mpq" => $post['mpq'],
                "moq" => $post['moq'],
                "share_order" => $post['share_order'],
                "transit" => $post['transit'],
                "leadtime" => $post['leadtime'],
                "safety_stock" => $post['safety_stock'],
                "price" => $post['price'],
                "currency" => $post['currency'],
                "start_date" => $post['start_date'],
                "valid_date" => $post['valid_date'],
                "calculate" => $post['calculate'],
                "remarks" => $post['remarks'],
            );

            $sqlSupItm = $this->db->query("SELECT SUM(share_order) as share_order FROM supplier_items WHERE item_rm_id = '$read->item_rm_id' and plant = '$read->plant' and id != '$id' GROUP BY item_rm_id");
            $rowSupItm = $sqlSupItm->row();

            $attachment = $this->crud->upload('attachment', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items/', ["id" => $id], "supplier_items", "attachment");
            $attachment2 = $this->crud->upload('attachment2', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items2/', ["id" => $id], "supplier_items", "attachment2");
            $attachment3 = $this->crud->upload('attachment3', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items3/', ["id" => $id], "supplier_items", "attachment3");
            $postFinal = array_merge($post, ["attachment" => $attachment, "attachment2" => $attachment2, "attachment3" => $attachment3]);

            if ((@$rowSupItm->share_order + $post['share_order']) <= 100) {
                if ($post['mpq'] > 0 && $post['moq'] > 0 && $post['leadtime'] > 0) {
                    // if(@$read->price == $post['price']){
                    //     $this->db->update('supplier_items', $postFinal, ["id" => $id]);
                    //     echo json_encode(array("title" => "Good Job", "message" => "Change Data Success, By Pass Approval", "theme" => "success"));
                    // }else{
                    $send = $this->crud->update('supplier_items', ["id" => $id], $postFinal);
                    echo $send;
                    // }

                    $this->crud->create("supplier_item_histories", $history);
                } else {
                    echo json_encode(array("title" => "MPQ 0", "message" => "MPQ, MOQ, Leadtime, Transit for Part No " . $post['item_rm_id'] . " Cannot <= 0", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Share Order", "message" => "Share Order for Part No " . $read->item_rm_id . " Cannot >= 100", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $po = $this->crud->reads("purchase_orders", [], ["supplier_id" => $data['supplier_id'], "item_rm_id" => $data['item_rm_id']]);
        if (empty($po)) {
            $this->crud->delete('supplier_item_histories', ["supplier_id" => $data['supplier_id'], "item_rm_id" => $data['item_rm_id']]);
            $send = $this->crud->delete('supplier_items', $data);
            echo $send;
        } else {
            echo json_encode(array("title" => "PO Register", "message" => "Data Cannot Delete, because the data already created in PO", "theme" => "error"));
        }
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];

        require_once 'assets/vendors/excel_reader2.php';
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'plant' => $data->val($i, 2),
                'supplier_id' => $data->val($i, 3),
                'item_rm_number' => $data->val($i, 4),
                'item_supplier' => $data->val($i, 5),
                'coo_name' => $data->val($i, 6),
                'spq' => $data->val($i, 7),
                'mpq' => $data->val($i, 8),
                'moq' => $data->val($i, 9),
                'currency' => $data->val($i, 10),
                'price' => $data->val($i, 11),
                'maker_code' => $data->val($i, 12),
                'share_order' => $data->val($i, 13),
                'start_date' => $data->val($i, 14),
                'valid_date' => $data->val($i, 15),
                'leadtime' => $data->val($i, 16),
                'landed_cost' => $data->val($i, 17),
                'import_duty' => $data->val($i, 18),
                'safety_stock' => $data->val($i, 19),
                'remarks' => $data->val($i, 20),
                'quotation_number' => $data->val($i, 21),
                'quotation_date' => $data->val($i, 22),
                'incoterm_code' => $data->val($i, 23),
                'action' => $data->val($i, 24)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/vendor_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/vendor_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/vendor_items.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        error_reporting(0);
        if ($this->input->post()) {
            $data = $this->input->post('data');
            if ($data['action'] == "") {
                die(json_encode(array("title" => "Action", "message" => "Action Still Empty please fill NEW or UPDATE", "theme" => "error")));
            }

            $supplier = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);
            $plant = $this->crud->read('plants', [], ["number" => $data['plant']]);
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['item_rm_number']]);
            $maker = $this->crud->read('makers', [], ["number" => $data['maker_code']]);
            $incoterm = $this->crud->read('incoterms', [], ["number" => $data['incoterm_code']]);
            $item_rm_id = @$item_rm->id;
            $supplier_id = @$supplier->id;
            $plant_id = $data['plant'];
            $supplier_items = $this->crud->read('supplier_items', [], ["supplier_id" => $supplier_id, "item_rm_id" => $item_rm_id, "plant" => $plant_id]);
            $sqlSupItm = $this->db->query("SELECT SUM(share_order) as share_order FROM supplier_items WHERE item_rm_id = '$item_rm_id' and plant = '$plant_id' and supplier_id != '$supplier_id' GROUP BY item_rm_id");
            $rowSupItm = $sqlSupItm->row();

            if (empty($supplier->id)) {
                die(json_encode(array("title" => "Not Found", "message" => "Vendor Code " . $data['supplier_id'] . " Not Found", "theme" => "error")));
            }

            if (empty($item_rm->id)) {
                die(json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_rm_number'] . " Not Found", "theme" => "error")));
            }

            if (empty($plant->id)) {
                die(json_encode(array("title" => "Not Found", "message" => "Plant " . $data['plant'] . " Not Found", "theme" => "error")));
            }

            if (strtolower($data['action']) == "new") {
                if (empty($maker->id)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Maker Code " . $data['maker_code'] . " Not Found", "theme" => "error"));
                } elseif ($data['price'] <= 0) {
                    echo json_encode(array("title" => "Failed", "message" => "Price Cannot Input 0", "theme" => "error"));
                } elseif ((@$rowSupItm->share_order + $data['share_order']) > 100) {
                    echo json_encode(array("title" => "Share Order", "message" => "Share Order for Part No " . $data['item_rm_number'] . " Cannot >= 100", "theme" => "error"));
                    // }elseif($data['mpq'] <= 0 || $data['moq'] <= 0 || $data['share_order'] <= 0 || $data['leadtime'] <= 0){
                    //     echo json_encode(array("title" => "MPQ 0", "message" => "MPQ, MOQ, Share Order, Leadtime for Part No " . $data['item_rm_number'] . " Cannot <= 0", "theme" => "error"));
                } elseif (@$supplier_items->supplier_id != "") {
                    echo json_encode(array("title" => "Duplicate", "message" => "Vendor " . $data['supplier_id'] . " and Part No " . $data['item_rm_number'] . " Has been Created", "theme" => "error"));
                } elseif (empty($incoterm->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Incoterm " . $data['incoterm_code'] . " Not Found", "theme" => "error"));
                } else {
                    if ($this->isValidDate($data['start_date'])) {
                        //
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Start Date " . $data['start_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }

                    if ($this->isValidDate($data['valid_date'])) {
                        //
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Valid Date " . $data['valid_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }
                    if ($this->isValidDate($data['quotation_date'])) {
                        //
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Quotation Date " . $data['quotation_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }

                    $dataFinal = array(
                        //field
                        "supplier_id" => $data['supplier_id'],
                        "plant" => $data['plant'],
                        "item_rm_id" => @$item_rm->id,
                        "maker_id" => @$maker->id,
                        "item_supplier" => $data['item_supplier'],
                        "spq" => $data['spq'],
                        "mpq" => $data['mpq'],
                        "moq" => $data['moq'],
                        "coo_name" => $data['coo_name'],
                        "landed_cost" => $data['landed_cost'],
                        "import_duty" => $data['import_duty'],
                        "share_order" => $data['share_order'],
                        "leadtime" => $data['leadtime'],
                        "currency" => $data['currency'],
                        "price" => $data['price'],
                        "start_date" => $data['start_date'],
                        "valid_date" => $data['valid_date'],
                        "safety_stock" => $data['safety_stock'],
                        "remarks" => $data['remarks'],
                        "quotation_number" => $data['quotation_number'],
                        "quotation_date" => $data['quotation_date'],
                        "incoterm_code" => $data['incoterm_code'],
                    );

                    $send = $this->crud->create('supplier_items', $dataFinal, "logs");
                    $this->crud->create('supplier_item_histories', $dataFinal, "logs");
                    echo $send;
                }
            } elseif (strtolower($data['action']) == "update") {
                if (@$supplier_items->supplier_id != "") {
                    $maker_id = empty($data['maker_code']) ? @$supplier_items->maker_id : @$maker->id;
                    $item_supplier = empty($data['item_supplier']) ? @$supplier_items->item_supplier : $data['item_supplier'];
                    $spq = empty($data['spq']) ? @$supplier_items->spq : @$data['spq'];
                    $mpq = empty($data['mpq']) ? @$supplier_items->mpq : @$data['mpq'];
                    $moq = empty($data['moq']) ? @$supplier_items->moq : @$data['moq'];
                    $coo_name = empty($data['coo_name']) ? @$supplier_items->coo_name : @$data['coo_name'];
                    $landed_cost = empty($data['landed_cost']) ? @$supplier_items->landed_cost : @$data['landed_cost'];
                    $import_duty = empty($data['import_duty']) ? @$supplier_items->import_duty : @$data['import_duty'];
                    $share_order = !is_numeric($data['share_order']) ? @$supplier_items->share_order : @$data['share_order'];
                    $leadtime = empty($data['leadtime']) ? @$supplier_items->leadtime : @$data['leadtime'];
                    $currency = empty($data['currency']) ? @$supplier_items->currency : @$data['currency'];
                    $price = empty($data['price']) ? @$supplier_items->price : @$data['price'];
                    $start_date = empty($data['start_date']) ? @$supplier_items->start_date : @$data['start_date'];
                    $valid_date = empty($data['valid_date']) ? @$supplier_items->valid_date : @$data['valid_date'];
                    $safety_stock = empty($data['safety_stock']) ? @$supplier_items->safety_stock : @$data['safety_stock'];
                    $remarks = empty($data['remarks']) ? @$supplier_items->remarks : @$data['remarks'];
                    $quotation_number = ($data['quotation_number'] == '') ? @$supplier_items->quotation_number : @$data['quotation_number'];
                    $quotation_date = ($data['quotation_date'] == '') ? @$supplier_items->quotation_date : @$data['quotation_date'];
                    $incoterm_code = ($data['incoterm_code'] == '') ? @$supplier_items->incoterm_code : @$data['incoterm_code'];

                    $dataFinal = array(
                        "item_rm_id" => @$item_rm->id,
                        "item_supplier" => $item_supplier,
                        "supplier_id" => @$supplier->id,
                        "plant" => $data['plant'],
                        "maker_id" => $maker_id,
                        "spq" => $spq,
                        "mpq" => $mpq,
                        "moq" => $moq,
                        "coo_name" => $coo_name,
                        "landed_cost" => $landed_cost,
                        "import_duty" => $import_duty,
                        "share_order" => $share_order,
                        "leadtime" => $leadtime,
                        "currency" => $currency,
                        "price" => $price,
                        "start_date" => $start_date,
                        "valid_date" => $valid_date,
                        "safety_stock" => $safety_stock,
                        "remarks" => $remarks,
                        "quotation_number" => $quotation_number,
                        "quotation_date" => $quotation_date,
                        "incoterm_code" => $incoterm_code,
                    );

                    if ($this->isValidDate($start_date)) {
                        //
                    } else {
                        if (!empty($data['start_date'])) {
                            die(json_encode(array("title" => "Not Found", "message" => "Start Date " . $start_date . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    if ($this->isValidDate($valid_date)) {
                        //
                    } else {
                        if (!empty($data['valid_date'])) {
                            die(json_encode(array("title" => "Not Found", "message" => "Valid Date " . $valid_date . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    if ($this->isValidDate($quotation_date)) {
                        //
                    } else {
                        if (!empty($data['quotation_date'])) {
                            die(json_encode(array("title" => "Not Found", "message" => "Quotation Date " . $quotation_date . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    if ((@$rowSupItm->share_order + $share_order) > 100) {
                        echo json_encode(array("title" => "Share Order", "message" => "Share Order for Part No " . $data['item_rm_number'] . " Cannot >= 100", "theme" => "error"));
                    } elseif ($mpq <= 0 || $moq <= 0 || $leadtime <= 0) {
                        echo json_encode(array("title" => "MPQ 0", "message" => "MPQ, MOQ, and Leadtime for Part No " . $data['item_rm_number'] . " Cannot <= 0", "theme" => "error"));
                        // }elseif(@$supplier_items->price == $price){
                        //     $this->db->update('supplier_items', $dataFinal, ["supplier_id" => @$supplier->id, "item_rm_id" => @$item_rm->id, "plant" => $data['plant']]);
                        //     echo json_encode(array("title" => "Good Job", "message" => "Change Data Success, By Pass Approval", "theme" => "success"));
                    } else {
                        $send = $this->crud->update('supplier_items', ["supplier_id" => @$supplier->id, "item_rm_id" => @$item_rm->id, "plant" => $data['plant']], $dataFinal, "logs");
                        echo $send;
                    }

                    $this->crud->create('supplier_item_histories', $dataFinal, "logs");
                } else {
                    echo json_encode(array("title" => "Not Found", "message" => "Vendor " . $data['supplier_id'] . " and Part No " . $data['item_rm_number'] . " Not Found in Vendor Items", "theme" => "error"));
                }
            } else {
                die(json_encode(array("title" => "Unknown", "message" => "Action " . $data['action'], "theme" => "error")));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=vendor_items_$format.xls");
        }

        $filter_plant  = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_item_rm = base64_decode($this->input->get('filter_item_rm'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_status_price = base64_decode($this->input->get('filter_status_price'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_check = $this->input->get('filter_check');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, c.number as item_rm_number, c.uom, g.uom_soft as uom_convertion,
            c.name as item_rm_name, a.item_supplier, b.number as supplier_number, b.origin, b.name as supplier_name, e.origin as maker_origin,
            d.name as item_family_name, b.type, e.name as maker_name, e.type as maker_type,
            j.name AS incoterm, 
            case when a.currency = 'IDR' then 1 else i.middle end as exchange_rate , 
            k.country_name, k.total_ddp,
            coalesce((a.price * (case when a.currency = 'IDR' then 1 else i.middle end))+ (a.price * (case when a.currency = 'IDR' then 1 else i.middle end) * COALESCE(k.total_ddp, 0)), 0) AS price_procurement");
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('item_rm c', 'c.id = a.item_rm_id');
        $this->db->join('item_familys d', "c.item_family_id = d.id and d.item_category_id IN ('C01')");
        $this->db->join('makers e', 'a.maker_id = e.id', 'left');
        $this->db->join('convertions g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->join("exchange_rates i", 'i.start_date <= a.valid_date AND i.end_date >= a.valid_date and i.currency_from = a.currency', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_rm_id FROM supplier_items GROUP BY item_rm_id HAVING COUNT(*) > 1) h", 'a.item_rm_id = h.item_rm_id');
        }
        $this->db->join("incoterms j", "a.incoterm_code = j.number", "LEFT");
        $this->db->join("(
                        select ja.*, jb.name as country_name from setup_incoterms ja 
                        left join origins jb on ja.country_number = jb.number
                        ) k", "a.incoterm_code = k.incoterm_number and b.origin=k.country_name", "LEFT");
        if ($filter_category != "") {
            $this->db->like('c.item_family_id', $filter_category);
        }
        if ($filter_suppliers != "") {
            $this->db->like('a.supplier_id', $filter_suppliers);
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_item_rm != "") {
            $this->db->like('a.item_rm_id', $filter_item_rm);
        }
        if ($filter_maker != "") {
            $this->db->like('e.number', $filter_maker);
        }
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_status_price == "Approved") {
            $this->db->where("a.approved_to is null or a.approved_to = ''");
        } elseif ($filter_status_price == "Checking") {
            $this->db->where("a.approved_to != ''");
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        if ($filter_type != "") {
            $this->db->like('b.type', $filter_type);
        }
        $this->db->order_by('c.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER VENDOR ITEM</h3>
            </div>
        </center>
        
        <table id="supplier_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Approved</th>
                <th>Approved By</th>
                <th>Approved Date</th>
                <th>Plant</th>
                <th>Vendor ID</th>
                <th>Vendor Name</th>
                <th>Quotation No</th>
                <th>Original Part No</th>
                <th>Part No BEI</th>
                <th>Part No Vendor</th>
                <th>Part Name</th>
                <th>Vendor Type</th>
                <th>Vendor Origin</th>
                <th>Product Family</th>
                <th>Maker</th>
                <th>Maker Type</th>
                <th>Country Of Origin</th>
                <th>COO Name</th>
                <th>SPQ</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Uom</th>
                <th>Uom<br>Convertion</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Start Date</th>
                <th>Valid Date</th>
                <th>Exchange Rate</th>
                <th>Incoterm</th>
                <th>Total DDP %</th>
                <th>Procurement<br>Price (IDR)</th>
                <th>Cheaper</th>
                <th>Landed Cost</th>
                <th>Import Duty</th>
                <th>Mfg L/T<br>(Days)</th>
                <th>Transit<br>(Days)</th>
                <th>Share Order</th>
                <th>Safety<br>Stock (%)</th>
                <th>Calculate MPQ</th>
                <th>Account COA</th>
                <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Updated By</th>
                <th>Updated Date</th>
            </tr>';
        $no = 1;

        $sortedRecords = $records;
        usort($sortedRecords, function ($a, $b) {
            $cmp = strcmp($a['item_rm_id'], $b['item_rm_id']);
            if ($cmp !== 0) {
                return $cmp;
            }

            $plantCmp = strcmp((string) $a['plant'], (string) $b['plant']);
            if ($plantCmp !== 0) {
                return $plantCmp;
            }

            return $a['price_procurement'] <=> $b['price_procurement'];
        });

        $cheaperMap = [];
        $lastGroupKey = null;
        $cheaperRank = 0;
        foreach ($sortedRecords as $row) {
            $groupKey = $row['item_rm_id'] . '|' . $row['plant'];
            if ($lastGroupKey !== $groupKey) {
                $lastGroupKey = $groupKey;
                $cheaperRank = 1;
            } else {
                $cheaperRank++;
            }
            $lookupKey = $row['supplier_id'] . '|' . $row['item_rm_id'] . '|' . $row['plant'];
            $cheaperMap[$lookupKey] = 'CHEAPER ' . $cheaperRank;
        }

        foreach ($records as $data) {
            if ($data['approved_to'] == "") {
                $approved = "APPROVED";
            } else {
                $approved = "CHECKING";
            }
            $lookupKey = $data['supplier_id'] . '|' . $data['item_rm_id'] . '|' . $data['plant'];
            $cheaper = isset($cheaperMap[$lookupKey]) ? $cheaperMap[$lookupKey] : '';
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $approved . '</td>
                    <td>' . $data['approved_by'] . '</td>
                    <td>' . $data['approved_date'] . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['supplier_id'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td>' . $data['quotation_number'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['origin'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['maker_name'] . '</td>
                    <td>' . $data['maker_type'] . '</td>
                    <td>' . $data['maker_origin'] . '</td>
                    <td>' . $data['coo_name'] . '</td>
                    <td>' . $data['spq'] . '</td>
                    <td>' . $data['mpq'] . '</td>
                    <td>' . $data['moq'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['uom_convertion'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td style="mso-number-format:0.0000;">' . $data['price'] . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['exchange_rate'] . '</td>
                    <td>' . $data['incoterm'] . '</td>
                    <td>' . $data['total_ddp'] . '</td>
                    <td>' . $data['price_procurement'] . '</td>
                    <td>' . $cheaper . '</td>
                    <td>' . $data['landed_cost'] . '</td>
                    <td>' . $data['import_duty'] . '</td>
                    <td>' . $data['leadtime'] . '</td>
                    <td>' . $data['transit'] . '</td>
                    <td>' . $data['share_order'] . '</td>
                    <td>' . $data['safety_stock'] . '</td>
                    <td>' . $data['calculate'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['remarks'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>
                    <td>' . $data['updated_by'] . '</td>
                    <td>' . $data['updated_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function excel_price($option = "")
    {
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=vendor_items_price_history_$format.xls");

        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_item_rm = base64_decode($this->input->get('filter_item_rm'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_status_price = base64_decode($this->input->get('filter_status_price'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_check = $this->input->get('filter_check');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $sql = $this->db->query("SELECT z.* FROM 
            (SELECT a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, a.supplier_id, b.name as supplier_name, b.type, b.currency, a.price, a.valid_date, a.remarks, a.created_by, a.created_date, a.plant, a.start_date
             FROM supplier_item_histories a 
             JOIN suppliers b ON a.supplier_id = b.id
             JOIN item_rm c ON a.item_rm_id = c.id
             JOIN item_familys d ON c.item_family_id = d.id and d.item_category_id IN ('C01')
             WHERE c.item_family_id like '%$filter_category%' AND a.supplier_id like '%$filter_suppliers%' AND a.item_rm_id like '%$filter_item_rm%' and b.type like '%$filter_type%'
             UNION ALL
             SELECT a.item_rm_id, c.number as item_rm_number, c.name as item_rm_name, d.name as item_family_name, a.supplier_id, b.name as supplier_name, b.type, b.currency, a.price, a.valid_date, a.remarks, (CASE WHEN a.updated_by is null THEN a.created_by ELSE a.updated_by END) as created_by, (CASE WHEN a.updated_date is null THEN a.created_date ELSE a.updated_date END) as created_date, a.plant, a.start_date
             FROM supplier_items a 
             JOIN suppliers b ON a.supplier_id = b.id
             JOIN item_rm c ON a.item_rm_id = c.id
             JOIN item_familys d ON c.item_family_id = d.id and d.item_category_id IN ('C01')
             WHERE c.item_family_id like '%$filter_category%' AND a.supplier_id like '%$filter_suppliers%' AND a.item_rm_id like '%$filter_item_rm%' and b.type like '%$filter_type%') z
             GROUP BY z.supplier_id, z.item_rm_number, z.price
             ORDER BY z.supplier_id, z.item_rm_number, z.created_date DESC");
        $records = $sql->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>HISTORY SUPPLIER CONTRACT PRICE RM</h3>
            </div>
        </center>
        
        <table id="supplier_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Plant</th>
                <th>Vendor ID</th>
                <th>Vendor Name</th>
                <th>Original Part No</th>
                <th>Part No Vendor</th>
                <th>Description</th>
                <th>Vendor Type</th>
                <th>Product Family</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Start Date</th>
                <th>Valid Date</th>
                <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['supplier_id'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['remarks'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
