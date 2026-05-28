<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Bom extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('mrp');
        
        //VALIDASI FORM
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[bom.item_rm_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/bom');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $this->dummy = $this->load->database('export', TRUE);

        $post = isset($_POST['q']) ? $_POST['q'] : "";


        $this->dummy->select("*");
        $this->dummy->from("mst_bom");
        $this->dummy->where("bom_maker is not null");
        $this->dummy->where("bom_maker != 'undefined'");
        $this->dummy->where("bom_maker != ' '");
        $this->dummy->limit("10");
        // $this->dummy->where("schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
        
        // $this->dummy->select("tablename");
        // $this->dummy->from("pg_catalog.pg_tables");
        // $this->dummy->where("schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
        $boms = $this->dummy->get()->result_array();

        echo json_encode($boms);
    }

    public function readCustomer()
    {
        $this->dummy = $this->load->database('export', TRUE);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->dummy->select("*");
        $this->dummy->from("r_customer");
        // $this->dummy->where("cust_id = '25'");
        $this->dummy->order_by('cust_id', 'asc');
        $boms = $this->dummy->get()->result_array();

        echo json_encode($boms);
    }

    public function readPart()
    {
        $this->dummy = $this->load->database('export', TRUE);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->dummy->select("item_id, item_name");
        $this->dummy->from("mst_item");
        $this->dummy->where("stscode_id", "01");
        $this->dummy->where("pfm_id !=", "06");
        $this->dummy->like("item_id", $post);
        $this->dummy->limit('100');
        $this->dummy->order_by('item_id', 'asc');
        $boms = $this->dummy->get()->result_array();

        echo json_encode($boms);
    }

    public function readProduct()
    {
        $this->dummy = $this->load->database('export', TRUE);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->dummy->select("item_id, item_name");
        $this->dummy->from("mst_item");
        $this->dummy->where("stscode_id", "01");
        $this->dummy->where("pfm_id", "06");
        $this->dummy->like("item_id", $post);
        $this->dummy->limit('100');
        $this->dummy->order_by('item_id', 'asc');
        $boms = $this->dummy->get()->result_array();

        echo json_encode($boms);
    }

    public function readMaker()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $this->db->select("*");
        $this->db->from("makers");
        $this->db->order_by('name', 'asc');
        $boms = $this->db->get()->result_array();

        echo json_encode($boms);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $this->dummy = $this->load->database('export', TRUE);

            $get = $this->input->get();
            $filter_item_id = @base64_decode($get['filter_item_id']);
            $filter_part_id = @base64_decode($get['filter_part_id']);
            $filter_customer = @base64_decode($get['filter_customer']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->dummy->select('a.bom_par_item, b.item_name, COUNT(a.bom_com_item) as total_part, c.cust_name as customer_name, d.um_name');
            $this->dummy->from("mst_bom a");
            $this->dummy->join('mst_item b', 'a.bom_par_item = b.item_id');
            $this->dummy->join('r_customer c', 'b.cust_id = c.cust_id', 'left');
            $this->dummy->join('r_unit_measure d', 'b.um_id = d.um_id', 'left');
            $this->dummy->where('b.pfm_id', "06");
            $this->dummy->where('b.stscode_id', "01");
            $this->dummy->like('a.bom_par_item', trim($filter_item_id));
            $this->dummy->like('a.bom_com_item', trim($filter_part_id));
            $this->dummy->like('b.cust_id', $filter_customer);
            $this->dummy->group_by('a.bom_par_item');
            $this->dummy->group_by('b.item_name');
            $this->dummy->group_by('c.cust_name');
            $this->dummy->group_by('d.um_name');
            $this->dummy->order_by('a.bom_par_item', 'ASC');
            //Total Data
            $totalRows = $this->dummy->count_all_results('', false);
            //Limit 1 - 10
            $this->dummy->limit($rows, $offset);
            //Get Data Array
            $records = $this->dummy->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $this->dummy = $this->load->database('export', TRUE);

            $item_id = base64_decode($this->input->get('item_id'));
            $filter_part_id = base64_decode($this->input->get('filter_part_id'));

            $this->dummy->select('a.bom_com_item, a.bom_qty_perassy, a.bom_effdate, a.bom_routid, a.bom_user, a.bom_date, b.item_name, d.um_name, a.bom_maker');
            $this->dummy->from("mst_bom a");
            $this->dummy->join('mst_item b', 'a.bom_com_item = b.item_id');
            // $this->dummy->join('r_customer c', 'b.cust_id = c.cust_id', 'left');
            $this->dummy->join('r_unit_measure d', 'b.um_id = d.um_id', 'left');
            $this->dummy->where('b.stscode_id', "01");
            $this->dummy->where('a.bom_par_item', trim($item_id));
            $this->dummy->like('a.bom_com_item', trim($filter_part_id));
            $this->dummy->order_by('a.bom_com_item', 'ASC');
            $records = $this->dummy->get()->result_array();

            echo json_encode($records);
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bom_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_id = @base64_decode($get['filter_item_id']);
        $filter_part_id = @base64_decode($get['filter_part_id']);
        $filter_customer = @base64_decode($get['filter_customer']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->dummy = $this->load->database('export', TRUE);

        $this->dummy->select('a.bom_par_item, a.bom_com_item, a.bom_qty_perassy, a.bom_effdate, a.bom_routid, a.bom_user, a.bom_date, b.item_name, c.cust_name, d.um_name, e.pfm_name, a.bom_maker');
        $this->dummy->from("mst_bom a");
        $this->dummy->join('mst_item b', 'a.bom_com_item = b.item_id');
        $this->dummy->join('mst_item ba', 'a.bom_par_item = ba.item_id');
        $this->dummy->join('r_customer c', 'ba.cust_id = c.cust_id', 'left');
        $this->dummy->join('r_unit_measure d', 'b.um_id = d.um_id', 'left');
        $this->dummy->join('r_prodfam e', 'b.pfm_id = e.pfm_id', 'left');
        $this->dummy->where('b.stscode_id', "01");
        $this->dummy->like('a.bom_par_item', trim($filter_item_id));
        $this->dummy->like('a.bom_com_item', trim($filter_part_id));
        $this->dummy->like('ba.cust_id', $filter_customer);
        $this->dummy->order_by('a.bom_par_item', 'ASC');
        $this->dummy->order_by('a.bom_com_item', 'ASC');
        $records = $this->dummy->get()->result_array();
        
        $sqlMaker = $this->db->query("SELECT b.number, c.name 
            FROM supplier_items a
            JOIN item_rm b ON a.item_rm_id = b.id
            LEFT JOIN makers c ON a.maker_id = c.id
            GROUP BY b.number");
        $rowMakers = $sqlMaker->result_array();

        foreach ($rowMakers as $rowMaker) {
            $makerItem[$rowMaker['number']] = $rowMaker['name'];
        }

        // die(json_encode($records));

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#bom {border-collapse: collapse;width: 100%;font-size: 12px;}#bom td, #bom th {border: 1px solid #ddd;padding: 2px;}#bom tr:nth-child(even){background-color: #f2f2f2;}#bom tr:hover {background-color: #ddd;}#bom th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="bom" border="1">
            <tr>
                <th width="20">No</th>
                <th>Assy No</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Customer</th>
                <th>Maker</th>
                <th>Product Family</th>
                <th>Unit Of Measure</th>
                <th>QPA</th>
                <th>Eff Date</th>
                <th>Routid</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $item_id = trim($data['bom_com_item']);
            $bom_maker = @$makerItem[$item_id];

            // if(trim($data['bom_maker']) == "undefined"){
            //     $bom_maker = "";
            // }else{
            //     $bom_maker = $data['bom_maker'];
            // }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['bom_par_item'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['bom_com_item'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['cust_name'] . '</td>
                            <td>' . $bom_maker . '</td>
                            <td>' . $data['pfm_name'] . '</td>
                            <td>' . $data['um_name'] . '</td>
                            <td>' . $data['bom_qty_perassy'] . '</td>
                            <td>' . $data['bom_effdate'] . '</td>
                            <td>' . $data['bom_routid'] . '</td>
                            <td>' . $data['bom_user'] . '</td>
                            <td>' . $data['bom_date'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
