<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mst_import_duty extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Item Supplier', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_import_duty');
        } else {
            redirect('error_access');
        }
    }
    public function reads($name = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM mst_import_duty WHERE number like '%$post%' or name like '%$post%'");
        echo json_encode($send);
    }
    public function readesc($name = "")
    {
        $send = $this->crud->query("SELECT DISTINCT description FROM mst_import_duty");
        echo json_encode($send);
    }
    public function readPartVendor()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT item_id FROM supplier_items WHERE item_id != '' ORDER BY item_id ASC");
        echo json_encode($send);
    }
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select('a.*, b.currency, c.number as item_number, c.name as item_name, c.uom'); //c.specification
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->like('c.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, d.number as plant, e.name as supplier_name, b.name as created_name, c.name as updated_name, f.number as item_number, f.name as item_name, g.name as item_family_name');
            $this->db->from('mst_import_duty a');
            $this->db->join('users b', 'a.created_by = b.username', 'left');
            $this->db->join('users c', 'a.updated_by = c.username', 'left');
            $this->db->join('plants d', 'a.plant = d.number', 'left');
            $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
            $this->db->join('item_rm f', 'a.item_id = f.id', 'left');
            $this->db->join('item_familys g', 'f.item_family_id = g.id', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "plant") {
                        $this->db->like("d.number", $filter->value);
                    } elseif ($filter->field == "supplier_name") {
                        $this->db->like("e.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("g.name", $filter->value);
                    } elseif ($filter->field == "item_number") {
                        $this->db->like("f.number", $filter->value);
                    } elseif ($filter->field == "item_name") {
                        $this->db->like("f.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.plant', 'ASC');
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
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $mst_import_duty = $this->crud->read('mst_import_duty', [], ["supplier_id" => $post['supplier_id'], "item_id" => $post['item_id'], "plant" => $post['plant']]);
                if (!$mst_import_duty) {
                    $send   = $this->crud->create('mst_import_duty', $post);
                    echo $send;
                } else {
                    show_error("Duplicate Data");
                }
            } else {
                show_error(validation_errors());
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
            $mst_import_duty = $this->crud->read('mst_import_duty', [], ["supplier_id" => $post['supplier_id'], "item_id" => $post['item_id'], "plant" => $post['plant']]);
            if (!empty(@$mst_import_duty->id)) {
                $send = $this->crud->update('mst_import_duty', ["id" => $id], $post);
                echo $send;
            } else {
                $send   = $this->crud->create('mst_import_duty', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('mst_import_duty', $data);
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'plant' => $data->val($i, 2),
                'supplier_number' => $data->val($i, 3),
                'item_id' => $data->val($i, 4),
                'percentage' => $data->val($i, 5),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');
            //Cek Process Number
            $plant = $this->crud->read('plants', [], ["number" => $data['plant']]);
            $supplier = $this->crud->read('suppliers', [], ["number" => $data['supplier_number']]);
            $vendor_contract = $this->crud->read('supplier_items', [], ["item_rm_id" => $data['item_id'], "supplier_id" => @$supplier->id, "plant" => $data['plant']]);
            $mst_import_duty = $this->crud->read('mst_import_duty', [], ["item_id" => @$vendor_contract->item_rm_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']]);
            if (empty($vendor_contract->item_rm_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Vendor " . $data['supplier_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($plant->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Plant " . $data['plant'] . " Not Found", "theme" => "error"));
            } elseif (!empty($mst_import_duty->item_id)) {
                $postFinal = array(
                    "plant" => $data['plant'],
                    "item_id" => $vendor_contract->item_id,
                    "supplier_id" => $supplier->id,
                    "percentage" => $data['percentage']
                );
                $send = $this->crud->update('mst_import_duty', ["item_id" => @$vendor_contract->item_rm_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']], $postFinal);
                echo $send;
            } else {
                $postFinal = array(
                    "plant" => $data['plant'],
                    "item_id" => $vendor_contract->item_rm_id,
                    "supplier_id" => $supplier->id,
                    "percentage" => $data['percentage']
                );
                $send   = $this->crud->create('mst_import_duty', $postFinal);
                echo $send;
            }
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_import_duty_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, d.number as plant, e.name as supplier_name, b.name as created_name, c.name as updated_name, f.number as item_number, f.name as item_name, g.name as item_family_name');
        $this->db->from('mst_import_duty a');
        $this->db->join('users b', 'a.created_by = b.username', 'left');
        $this->db->join('users c', 'a.updated_by = c.username', 'left');
        $this->db->join('plants d', 'a.plant = d.number', 'left');
        $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
        $this->db->join('item_rm f', 'a.item_id = f.id', 'left');
        $this->db->join('item_familys g', 'f.item_family_id = g.id', 'left');
        $this->db->where('a.deleted_is', 0);
        $this->db->order_by('a.plant', 'ASC');
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
                            <small>MASTER IMPORT DUTY</small>
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
                <th width="20">No</th>
                <th>Plant</th>
                <th>Vendor Name</th>
                <th>Item Vendor</th>
                <th>Item Name</th>
                <th>Product Family</th>
                <th>Import Duty (%)</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="mso-number-format:\@">' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['item_family_name'] . '</td>
                            <td align="right">' . number_format($data['percentage'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
