<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_request_npd extends CI_Controller
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
            $this->load->view('purchase/purchase_request_npd');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $query = $this->db->query("SELECT DISTINCT request_no FROM purchase_requests WHERE type = 'NPD' ORDER BY request_no DESC LIMIT 1000");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readprodfam($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $query = $this->db->query("SELECT DISTINCT a.id, a.number, a.name 
            FROM item_familys a 
            JOIN item_rm b ON a.id = b.item_family_id
            JOIN supplier_items c ON b.id = c.item_rm_id
            WHERE a.item_category_id IN ('C01') and c.supplier_id = '$supplier_id'");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readParts($supplier_id, $item_famiy_id, $plant)
    {
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

    public function readPr($supplier_id, $item_family_id)
    {
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

    public function purchaseRequestNo($code, $add = "")
    {
        if ($add != "") {
            $code = base64_decode($code);
        }

        $sqlGetID    = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode         = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%02s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -2);
            $urutan++;
            $autoID    = sprintf("%02s", $urutan);
        }

        if ($add == "") {
            return $code . $autoID;
        } else {
            echo $code . $autoID;
        }
    }

    public function getDataNpd()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_plant = base64_decode($this->input->get('filter_plant'));

            //Select Query
            $this->db->select('a.item_rm_id, a.supplier_id, b.item_family_id, a.plant, a.request_no, a.request_date, a.request_reason, a.requirement_date, a.qty, e.number as supplier_no, d.number as item_family_number, c.moq');
            $this->db->from('request_npd a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id and c.share_order > 0');
            $this->db->join('item_familys d', 'b.item_family_id = d.id');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            $this->db->where('a.status', 0);
            $this->db->where('a.confirm', "CONFIRMED");
            $this->db->where("a.request_date BETWEEN '$filter_from' and '$filter_to'");
            $this->db->where('a.plant', $filter_plant);
            if ($filter_item_family != "") {
                $this->db->where('b.item_family_id', $filter_item_family);
            }
            if ($filter_supplier != "") {
                $this->db->where('a.supplier_id', $filter_supplier);
            }
            if ($filter_item_rm != "") {
                $this->db->where('b.id', $filter_item_rm);
            }
            $this->db->group_by('c.supplier_id');
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('c.supplier_id');
            $this->db->order_by('a.item_rm_id');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            $end_supplier = "";
            $end_product_family = "";
            $end_request_no = "";
            foreach ($mpsDetails as $generate) {
                $supplier_no = $generate['supplier_no'];
                $request_date = $generate['request_date'];

                if ($supplier_no == $end_supplier && $generate['item_family_number'] == $end_product_family) {
                    $request_no = $end_request_no;
                    $no++;
                } else {
                    $request_no = $this->purchaseRequestNo("NPD" . @trim($supplier_no . date("Ym")) . "-" . trim($generate['item_family_number']));
                    $no = 1;
                }

                $qty = ceil($generate['qty'] / $generate['moq']) * $generate['moq'];

                $arr[] = array(
                    // "p_month" => date("m", strtotime($request_date)),
                    // "p_year" => date("Y", strtotime($request_date)),
                    "p_month" => date("m"),
                    "p_year" => date("Y"),
                    "plant" => $generate['plant'],
                    "request_npd" => $generate['request_no'],
                    "request_no" => $request_no,
                    "request_date" => date("Y-m-d"),
                    "request_name" => $this->session->name,
                    "expected_date" => $generate['requirement_date'],
                    "supplier_id" => $generate['supplier_id'],
                    "item_rm_id" => $generate['item_rm_id'],
                    "item_family_id" => $generate['item_family_id'],
                    "need" => $generate['qty'],
                    "mrp" => $generate['qty'],
                    // "qty" => $generate['qty'],
                    "qty" => $qty,
                    "remarks" => $generate['request_reason'],
                    "type" => "NPD",
                    "status_data" => "GENERATE",
                );

                $end_supplier = $supplier_no;
                $end_product_family = $generate['item_family_number'];
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
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
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
            $this->db->from("(select * from purchase_requests where qty > 0 order by approved_to desc) a");
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            $this->db->join("(select request_no, COUNT(id) as total FROM purchase_orders GROUP BY request_no) e", 'a.request_no = e.request_no', 'left');
            $this->db->where('a.type', "NPD");
            $this->db->where('a.request_date >=', $filter_from);
            $this->db->where('a.request_date <=', $filter_to);
            if ($filter_item_family != "") {
                $this->db->like('a.item_family_id', $filter_item_family);
            }
            if ($filter_item_rm != "") {
                $this->db->like('a.item_rm_id', $filter_item_rm);
            }
            if ($filter_supplier != "") {
                $this->db->like('a.supplier_id', $filter_supplier);
            }
            if ($filter_request_no != "") {
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
        $this->db->where('a.request_no', $request_no);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->order_by('d.number', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $finals = array();
        $total_purchase_request = 0;
        $total_need = 0;
        $total_mrp = 0;
        foreach ($records as $data) {

            $total_purchase_request += $data['qty'];
            $total_need += $data['need'];
            $total_mrp += $data['mrp'];
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $records,
            'footer' => array([
                "item_rm_number" => "Grand Total",
                "qty" => $total_purchase_request,
                "need" => $total_need,
                "mrp" => $total_mrp,
            ])
        ]);
        echo json_encode($result);
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

    public function updateConfirm()
    {
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
            if (count($purchase_requests) > 0) {
                echo json_encode(array("message" => "Duplicate", "title" => "Double", "theme" => "error"));
            } else {
                $send = $this->crud->create('purchase_requests', $post, "", "approval");
                $this->db->update('request_npd', ["status" => 1], ["request_no" => $post['request_npd'], "item_rm_id" => $post['item_rm_id']]);
                echo json_encode(array("message" => "Covert NPD to Purchase Request has been Successfully", "title" => "Successfull", "theme" => "success"));
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
        $send = $this->crud->delete('purchase_requests', ["request_no" => $data['request_no']]);
        $this->db->update('request_npd', ["status" => 0], ["request_no" => $data['request_npd']]);
        echo $send;
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

            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_request_no = base64_decode($this->input->get('filter_request_no'));
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));

            $this->db->select('a.*, b.name as supplier_name, c.name as item_family_name, d.number as item_rm_number, d.name as item_rm_name, e.share_order, e.mpq, e.moq, e.leadtime, e.item_supplier');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_familys c', "a.item_family_id = c.id and c.item_category_id IN ('C01')");
            $this->db->join('item_rm d', "a.item_rm_id = d.id");
            $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM d.number) = ia.number");
            $this->db->join('supplier_items e', 'ia.id = e.item_rm_id and a.supplier_id = e.supplier_id and a.plant = e.plant and e.share_order > 0');
            $this->db->where('a.type', "NPD");
            $this->db->where('a.request_date >=', $filter_from);
            $this->db->where('a.request_date <=', $filter_to);
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
                        <th style="text-align:center;" width="20">No</th>
                        <th style="text-align:center;">STATUS</th>
                        <th style="text-align:center;">PLANT</th>
                        <th style="text-align:center;">PERIOD</th>
                        <th style="text-align:center;">REVISION</th>
                        <th style="text-align:center;">PR NO</th>
                        <th style="text-align:center;">VENDOR</th>f
                        <th style="text-align:center;">PART NO BEI</th>
                        <th style="text-align:center;">PART NO SUPPLIER</th>
                        <th style="text-align:center;">PART NAME</th>
                        <th style="text-align:center;">PRODFAM</th>
                        <th style="text-align:center;">SHARE<br>ORDER</th>
                        <th style="text-align:center;">MPQ</th>
                        <th style="text-align:center;">MOQ</th>
                        <th style="text-align:center;">LEADTIME</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">MRP</th>
                        <th style="text-align:center;">QTY</th>
                        <th style="text-align:center;">REASON</th>
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
                if ($class == "") {
                    $style = "style='background:#FFD2D2;'";
                } else {
                    $style = "";
                }

                $html .= "  <tr " . $style . ">
                                        <td>" . $no . "</td>
                                        <td>" . $record['flag'] . "</td>
                                        <td>" . $record['plant'] . "</td>
                                        <td>" . $record['p_year'] . $record['p_month'] . "</td>
                                        <td>" . $record['revision'] . "</td>
                                        <td>" . $record['request_no'] . "</td>
                                        <td>" . $record['supplier_name'] . "</td>
                                        <td style='mso-number-format:\@;'>" . trim($record['item_rm_number']) . "</td>
                                        <td style='mso-number-format:\@;'>" . trim($record['item_supplier']) . "</td>
                                        <td>" . $record['item_rm_name'] . "</td>
                                        <td>" . $record['item_family_name'] . "</td>
                                        <td>" . $record['share_order'] . "</td>
                                        <td>" . $record['mpq'] . "</td>
                                        <td>" . $record['moq'] . "</td>
                                        <td>" . $record['leadtime'] . "</td>
                                        <td style='text-align:right;'>" . round($record['need']) . "</td>
                                        <td style='text-align:right;'>" . round($record['mrp']) . "</td>
                                        <td style='text-align:right;'>" . round($record['qty']) . "</td>
                                        <td style='text-align:right;'>" . $record['remarks'] . "</td>
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

        if ($purchase_requests->approved_by == @$approval->user_approval_1) {
            $qr_user_1 = "src='" . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . "'";
        } elseif ($purchase_requests->approved_by == @$approval->user_approval_2) {
            $qr_user_1 = "src='" . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . "'";
            $qr_user_2 = "src='" . base_url('assets/image/qrcode/' . @$user_2->name . '.png') . "'";
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
                                    <h3><u>PURCHASE REQUISITION NPD</u></h3>
                                </center>
                                <div style="float:left; width:50%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="50" style="vertical-align: top;"><b>To</b></td>
                                            <td width="30" style="vertical-align: top;">:</td>
                                            <td><b>' . @$supplier->name . '</b><br>' . @$supplier->address . '</td>
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
                                            <td><b>' . $i . '/' . $page . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Part No BEI</th>
                                        <th width="120">Part No Supplier</th>
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
                                <td>' . date("Y-m-d", strtotime("+" . $record['leadtime'] . " day", strtotime($record['request_date']))) . '</td>
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
                                        <th colspan="2" style="text-align:center;">' . $config->name . '</th>
                                    </tr>
                                    <tr>
                                            <th style="height:80px;"><img ' . $qr_user_1 . ' width="80"/></th>
                                            <th style="height:80px;"><img ' . $qr_user_2 . ' width="80"/></th>
                                        </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">' . @$user_1->name . '</th>
                                        <th style="height:20px; text-align:center;">' . @$user_2->name . '</th>
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
