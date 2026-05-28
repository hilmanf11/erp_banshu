<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Where_used extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/where_used');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=where_used_$format.xls");
        }

        if ($this->input->get()) {
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));
            $filter_part_id = base64_decode($this->input->get('filter_part_id'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_maker = base64_decode($this->input->get('filter_maker'));
            
            $whereMaker = empty($filter_maker) ? "" : "WHERE c.name = '$filter_maker'";

            $this->dummy = $this->load->database('export', TRUE);

            $sqlMaker = $this->db->query("SELECT b.number, c.name 
                FROM supplier_items a
                JOIN item_rm b ON a.item_rm_id = b.id
                LEFT JOIN makers c ON a.maker_id = c.id
                $whereMaker
                GROUP BY b.number");
            $rowMakers = $sqlMaker->result_array();

            $wherePart = [];
            foreach ($rowMakers as $rowMaker) {
                $makerItem[$rowMaker['number']] = $rowMaker['name'];
                $wherePart[] = $rowMaker['number'];
            }

            $chunks = array_chunk($wherePart, 500);

            $this->dummy->select('a.bom_par_item, a.bom_com_item, a.bom_qty_perassy, a.bom_effdate, a.bom_routid, a.bom_user, a.bom_date, b.item_name, c.cust_name, d.um_name, e.pfm_name, a.bom_maker');
            $this->dummy->from("mst_bom a");
            $this->dummy->join('mst_item b', 'a.bom_com_item = b.item_id');
            $this->dummy->join('mst_item ba', 'a.bom_par_item = ba.item_id');
            $this->dummy->join('r_customer c', 'ba.cust_id = c.cust_id', 'left');
            $this->dummy->join('r_unit_measure d', 'b.um_id = d.um_id', 'left');
            $this->dummy->join('r_prodfam e', 'b.pfm_id = e.pfm_id', 'left');
            $this->dummy->where('b.stscode_id', "01");
            $this->dummy->group_start(); 
            foreach ($chunks as $index => $chunk) {
                if ($index === 0) {
                    $this->dummy->where_in('TRIM(a.bom_com_item)', $chunk);
                } else {
                    $this->dummy->or_where_in('TRIM(a.bom_com_item)', $chunk);
                }
            }
            $this->dummy->group_end();
            $this->dummy->like('a.bom_par_item', trim($filter_item_id));
            $this->dummy->like('a.bom_com_item', trim($filter_part_id));
            $this->dummy->like('ba.cust_id', $filter_customer);
            $this->dummy->order_by('a.bom_par_item', 'ASC');
            $this->dummy->order_by('a.bom_com_item', 'ASC');
            $records = $this->dummy->get()->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                    <style> .str{ mso-number-format:\@; } </style>
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
                                        <small>WHERE USED</small>
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
                    <center>
                        <b style="font-size:20px;">WHERE USED</b><br><small>Part No '.$filter_part_id.'</small><br><br>
                    </center>
                    <table id="customers" border="1">
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

                        if($filter_maker == ""){
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
                                    </tr>';
                        }elseif($filter_maker == $bom_maker){
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
                                    </tr>';
                        }
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
