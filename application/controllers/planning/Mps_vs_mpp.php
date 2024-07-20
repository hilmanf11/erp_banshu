<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mps_vs_mpp extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mps_vs_mpp');
        } else {
            redirect('error_access');
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            // Select desired columns from the database
            $this->db->select('a.p_month, a.p_year,b.name as customer,a.product_no, a.product_name, 
                SUM(CASE WHEN a.revision = 0 THEN a.prod_plan ELSE 0 END) as revision_0, 
                SUM(CASE WHEN a.revision = 1 THEN a.prod_plan ELSE 0 END) as revision_1, 
                SUM(CASE WHEN a.revision = 2 THEN a.prod_plan ELSE 0 END) as revision_2,
                SUM(CASE WHEN a.revision IN (0,1,2) THEN a.prod_plan ELSE 0 END) as total_mps, 
                SUM(c.prod_plan) as total_mpp, 
                (SUM(CASE WHEN a.revision IN (0,1,2) THEN a.prod_plan ELSE 0 END) - SUM(c.prod_plan)) as balance');
            $this->db->from('generate_mps_detail as a');
            $this->db->join('mst_customer as b', "a.customer_id = b.number", 'left');
            $this->db->join('generate_mpp as c', "a.product_no = c.product_no and a.p_month = c.p_month and a.p_year = c.p_year", "left");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.product_no', $filter_product_no);
            $this->db->like('a.ltpp_month2', "$filter_year-$filter_month-01");
            $this->db->group_by('a.customer_id, a.product_no, a.p_month, a.p_year');
            $this->db->order_by('balance', 'desc');

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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mps_vs_mpp_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        $month =$months[$filter_month];
        //Select Query
        $this->db->select('a.p_month, a.p_year,b.name as customer,a.product_no, a.product_name, 
                SUM(CASE WHEN a.revision = 0 THEN a.prod_plan ELSE 0 END) as revision_0, 
                SUM(CASE WHEN a.revision = 1 THEN a.prod_plan ELSE 0 END) as revision_1, 
                SUM(CASE WHEN a.revision = 2 THEN a.prod_plan ELSE 0 END) as revision_2, 
                SUM(CASE WHEN a.revision IN (0,1,2) THEN a.prod_plan ELSE 0 END) as total_mps, 
                SUM(c.prod_plan) as total_mpp, 
                (SUM(CASE WHEN a.revision IN (0,1,2) THEN a.prod_plan ELSE 0 END) - SUM(c.prod_plan)) as balance');
        $this->db->from('generate_mps_detail as a');
        $this->db->join('mst_customer as b', "a.customer_id = b.number", 'left');
        $this->db->join('generate_mpp as c', "a.product_no = c.product_no and a.p_month = c.p_month and a.p_year = c.p_year", "left");
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->like('a.ltpp_month2', "$filter_year-$filter_month-01");
        $this->db->group_by('a.customer_id, a.product_no, a.p_month, a.p_year');
        $this->db->order_by('balance', 'desc');
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
                            <small>MPS vs MPP</small>
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
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Customer</th>
                <th style="text-align:center;">Product No</th>
                <th style="text-align:center;">Product Name</th>
                <th style="text-align:center;">'.$month.' Rev 0 Prodplan</th>
                <th style="text-align:center;">'.$month.' Rev 1 Prodplan</th>
                <th style="text-align:center;">'.$month.' Rev 2 Prodplan</th>
                <th style="text-align:center;">Total MPS</th>
                <th style="text-align:center;">Total MPP</th>
                <th style="text-align:center;">Balance</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer'] . '</td>
                            <td>' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['revision_0'] . '</td>
                            <td>' . $data['revision_1'] . '</td>
                            <td>' . $data['revision_2'] . '</td>
                            <td style="text-align:right;">' . $data['total_mps'] . '</td>
                            <td style="text-align:right;">' . $data['total_mpp'] . '</td>
                            <td style="text-align:right;">' . $data['balance'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
