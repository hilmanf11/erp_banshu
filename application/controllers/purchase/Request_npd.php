<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class request_npd extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Part No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/request_npd');
        } else {
            redirect('error_access');
        }
    }

    public function request_no(){
        $code       = "NPD-".date("Ym");
        $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM request_npd WHERE request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;

        $urutan = (int) substr($kode, -3);
        $urutan++;
        $autoID	= sprintf("%03s", $urutan);

        die(json_encode(array("name" => $code . $autoID)));
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_request_no = base64_decode($this->input->get('filter_request_no'));
            $filter_request_by = trim(base64_decode($this->input->get('filter_request_by')));
            $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, SUM(a.qty) as total_qty, count(a.id) as total_data, 
                (CASE WHEN count(a.id) = COUNT(d.id) THEN 2
                    WHEN count(b.id) = 0 THEN 0
                    ELSE 1
                END) as total_status, count(c.id) as total_confirm');
            $this->db->from('request_npd a');
            $this->db->join("(select * from request_npd where status = 0) b", 'a.id = b.id', 'left');
            $this->db->join("(select * from request_npd where confirm != '') c", 'a.id = c.id', 'left');
            $this->db->join("(select * from request_npd where confirm != '' and confirm != 'CONFIRMED') d", 'a.id = d.id', 'left');
            if ($filter_from != "" and $filter_to != "") {
                $this->db->where("a.request_date >=", $filter_from);
                $this->db->where("a.request_date <=", $filter_to);
            }
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('a.request_by', $filter_request_by);
            $this->db->like('a.item_npd_id', $filter_item_npd_id);
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', "desc");
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

    public function datatableDetails($request_no){
        $request_no = base64_decode($request_no);
        $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

        //Select Query
        $this->db->select('a.id, a.plant, a.receive_status, a.receive_date, a.item_npd_id, a.item_npd_name, a.product_family, a.maker, a.uom, a.qty, a.requirement_date, a.confirm, a.confirm_date, a.confirm_by, a.confirm_reason, b.name as supplier_name, c.number as item_rm_number, a.item_supplier, d.request_no, e.po_no, e.delivery_date');
        $this->db->from('request_npd a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        $this->db->join('purchase_requests d', 'a.item_rm_id = d.item_rm_id and a.request_no = d.request_npd', 'left');
        $this->db->join('purchase_orders e', 'd.request_no = e.request_no and d.item_rm_id = e.item_rm_id', 'left');
        $this->db->where('a.request_no', $request_no);
        $this->db->like('a.item_npd_id', $filter_item_npd_id);
        $this->db->order_by('a.item_npd_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        foreach ($records as $record) {
            $total_qty += $record['qty'];
            array_push($data, $record);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_npd_id" => "Grand Total",
                "qty" => $total_qty,
            ])
        ]);
        echo json_encode($result);
    }

    public function datatableUpdates(){
        $request_no = base64_decode($this->input->get('request_no'));

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('request_npd a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
        $this->db->where('a.request_no', $request_no);
        $this->db->where("a.id IN (select id from request_npd where (confirm is null or confirm = ''))");
        $this->db->order_by('a.product_family');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if($this->input->post()){
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $datas = $post['data'];

            $send = "";
            $this->db->trans_start();
            $this->db->delete('request_npd', ["request_no" => $datas[0]['request_no']]);
            foreach ($datas as $data) {
                $this->crud->create('request_npd', $data, "logs");
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array("title" => "Failed", "message" => "Data Saved Failed", "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if($this->input->post()){
            $jsonData = file_get_contents("php://input");
            $post = json_decode($jsonData, true);
            $datas = $post['data'];

            $send = "";
            $this->db->trans_start();
            foreach ($datas as $data) {
                $this->crud->update('request_npd', ["id" => $data['id']], $data, "logs");
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array("title" => "Failed", "message" => "Data Updated Failed", "theme" => "error"));
            } else {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('request_npd', $data);
        echo $send;
    }

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

        $code       = "NPD-".date("Ym");
        $sqlGetID	= $this->db->query("SELECT max(request_no) as kode FROM request_npd WHERE request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;

        $urutan = (int) substr($kode, -3);
        $urutan++;
        $autoID	= sprintf("%03s", $urutan);

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_npd_id' => $data->val($i, 2),
                'item_npd_name' => $data->val($i, 3),
                'product_family' => $data->val($i, 4),
                'maker' => $data->val($i, 5),
                'qty' => $data->val($i, 6),
                'uom' => $data->val($i, 7),
                'requirement_date' => $data->val($i, 8),
                'category' => $data->val(2, 3),
                'request_reason' => $data->val(3, 3),
                "request_no" => $code . $autoID, 
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            if($post['item_npd_id'] != "" && $post['item_npd_name'] != "" && $post['product_family'] != "" && $post['maker'] != "" && $post['qty'] != "" && $post['uom'] != ""){
                $this->crud->create('request_npd', array_merge($post, [
                    "request_date" => date("Y-m-d"), 
                    "request_by" => $this->session->name, 
                ]));
                echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_npd_id'] . " Created Successfully", "theme" => "success"));
            }else{
                echo json_encode(array("title" => "Error", "message" => "Item ID " . $post['item_npd_id'] . " Not Completed data", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/request_npd.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/request_npd.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/request_npd.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/documents/npd/';

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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=request_npd_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_request_no = base64_decode($this->input->get('filter_request_no'));
        $filter_request_by = trim(base64_decode($this->input->get('filter_request_by')));
        $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

        //Select Query
        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, c.name as supplier_name');
        $this->db->from('request_npd a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.supplier_id = c.id', 'left');
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where("a.request_date >=", $filter_from);
            $this->db->where("a.request_date <=", $filter_to);
        }
        $this->db->like('a.request_no', $filter_request_no);
        $this->db->like('a.request_by', $filter_request_by);
        $this->db->like('a.item_npd_id', $filter_item_npd_id);
        $this->db->order_by('a.request_no', "desc");
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>PART REQUEST NPD</small>
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
        <table id="customers" border="1">
            <tr>
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Request No</th>
                <th style="text-align:center;">Request Date</th>
                <th style="text-align:center;">Request By</th>
                <th style="text-align:center;">Category</th>
                <th style="text-align:center;">Remarks</th>
                <th style="text-align:center;">Part NPD No</th>
                <th style="text-align:center;">Part NPD Name</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Maker</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Requirement<br>Date</th>
                <th style="text-align:center;">Confirm</th>
                <th style="text-align:center;">Confirm Date</th>
                <th style="text-align:center;">Confirm Reason</th>
                <th style="text-align:center;">Part No BEI</th>
                <th style="text-align:center;">Supplier Name</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td>' . $data['request_date'] . '</td>
                            <td>' . $data['request_by'] . '</td>
                            <td>' . $data['category'] . '</td>
                            <td>' . $data['request_reason'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_npd_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_npd_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['maker'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['requirement_date'] . '</td>
                            <td style="text-align:center;">' . $data['confirm'] . '</td>
                            <td style="text-align:center;">' . $data['confirm_date'] . '</td>
                            <td style="text-align:left;">' . $data['confirm_reason'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_name'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}