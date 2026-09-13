<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_mcl extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->library('Ciqrcode');
        $this->load->library('email');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['id_menu'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_mcl');
        } else {
            redirect('error_access');
        }
    }

    // public function pushData(){
    //     if ($this->input->post()) {
    //         $this->dummy = $this->load->database('dummy', TRUE);

    //         $post = $this->input->post('data');

    //         $this->dummy->select('*');
    //         $this->dummy->from("mst_item");
    //         $this->dummy->where("item_id", $post['item_rm_number']);
    //         $mst_item = $this->dummy->get()->row();

    //         $this->dummy->select('*');
    //         $this->dummy->from("poc");
    //         $this->dummy->where("poc_id", $post['po_no']);
    //         $this->dummy->where("item_id", $post['item_rm_number']);
    //         $poc = $this->dummy->get()->row();

    //         // $gpai = $this->crudSupplier($post['supplier_no']);
    //         // $supplier = $gpai['data'];

    //         $postFinal = array(
    //             "poc_id" => $post['po_no'],
    //             "ven_id" => @$mst_item->ven_id,
    //             // "ven_id" => @$supplier[0]['alias_code'],
    //             "poc_date" => $post['po_date'],
    //             "poc_emp" => $this->session->name,
    //             "item_id" => $post['item_rm_number'],
    //             "poc_reqdate" => date("Y-m-d"),
    //             "poc_reqqty" => $post['qty'],
    //             "currency" => $post['currency'],
    //             "periode" => date("Ym"),
    //             // "poc_remark" => $post['reason'],
    //         );

    //         if($poc){
    //             $this->dummy->where("poc_id", $post['po_no']);
    //             $this->dummy->where("item_id", $post['item_rm_number']);
    //             if ($this->dummy->update('poc', ["poc_reqqty" => $post['qty']])) {
    //                 echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_number'] . " | Data Update Successfully", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Error", "message" => $post['item_rm_number'] . " | Data Unsaved", "theme" => "error"));
    //             }
    //         }else{
    //             if ($this->dummy->insert('poc', $postFinal)) {
    //                 echo json_encode(array("title" => "Good Job", "message" => $post['item_rm_number'] . " | Data Saved Successfully", "theme" => "success"));
    //             } else {
    //                 echo json_encode(array("title" => "Error", "message" => $post['item_rm_number'] . " | Data Unsaved", "theme" => "error"));
    //             }
    //         }

    //         $this->db->where("item_rm_id", $post['item_rm_id']);
    //         $this->db->where("po_no", $post['po_no']);
    //         $this->db->update('purchase_orders', ["push_date" => date("Y-m-d H:i:s"), "push" => "OK"]);
    //     }
    // }

    public function print_po($po_no, $export = "")
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_orders->supplier_id]);
        $approval = $this->crud->read('approvals', [], ["table_name" => "purchase_orders"]);

        $user_3 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        $user_4 = $this->crud->read('users', [], ["username" => $approval->user_approval_3]);
        
        $qr_user_3 = "";
        $qr_user_4 = "";
        
        if($purchase_orders->approved == 3){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
        }elseif($purchase_orders->approved == 4){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
            $qr_user_4 = "src='" .base_url('assets/image/qrcode/' . @$user_4->name . '.png')."'";
        }

        // $month_1 =  date('M Y', strtotime('+1 month', strtotime(@$purchase_orders->po_date)));
        // $month_2 =  date('M Y', strtotime('+2 month', strtotime(@$purchase_orders->po_date)));
        // $month_3 =  date('M Y', strtotime('+3 month', strtotime(@$purchase_orders->po_date)));

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_4->name, "assets/image/qrcode/");

        if($export == ""){
            $media = "@media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }";
            $media2 = ' <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>';
        }else{
            $media = ".print {
                        zoom: 0.5;
                    }";
            $media2 = "";
        }

        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        .customers {
                            border-collapse: collapse;
                            font-size: 11px;
                        }
                        .customers td, .customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        .customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        '.$media.'
                    </style>
                    <body>
                        '.$media2.'
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*,
                b.number as item_rm_number, 
                b.name as item_rm_name,
                c.item_supplier,
                d.type as maker_type,
                d.name as maker_name');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM b.number) = ia.number");
            $this->db->join('supplier_items c', "ia.id = c.item_rm_id and a.supplier_id = c.supplier_id and a.plant = c.plant", 'left');
            $this->db->join('makers d', 'c.maker_id = d.id', 'left');
            $this->db->where('a.po_no', base64_decode($po_no));
            // $this->db->where('a.qty >', 0);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="40" />
                                </th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER (MCL)</u></h3>
                                    <small>NO : ' . @$purchase_orders->po_no . '</small>
                                </center>
                                <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                    <tr>
                                        <td width="50">Vendor</td>
                                        <td width="10">:</td>
                                        <td width="65%">
                                            <b>' . @$supplier->name . '</b>
                                        </td>
                                        <td style="text-align:left; padding-right: 20px;" rowspan="7">
                                            PO Date: <b>' . date("d F Y", strtotime($purchase_orders->po_date)) . '</b><br>
                                            Revision: <b>' . $purchase_orders->rev . '</b><br>
                                            Revision Date: <b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms: <b>' . @$supplier->payment_term . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->contact_person . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Page</td>
                                        <td width="10">:</td>
                                        <td><b>' . $hal  . '</b> of <b> ' . $page . '</b></td>
                                    </tr>
                                </table>
                                <table class="customers" style="width:100%;">
                                    <tr>
                                        <th rowspan="2" style="text-align:center;">No</th>
                                        <th rowspan="2" style="text-align:center;">Part No Original</th>
                                        <th rowspan="2" style="text-align:center;">Part No BEI</th>
                                        <th rowspan="2" style="text-align:center;">Part No Vendor</th>
                                        <th rowspan="2" style="text-align:center;">Description</th>
                                        <th rowspan="2" style="text-align:center;">Maker</th>
                                        <th rowspan="2" style="text-align:center;">ETD</th>
                                        <th rowspan="2" style="text-align:center;">ETA</th>
                                        <th rowspan="2" style="text-align:center;">Qty</th>
                                        <th rowspan="2" style="text-align:center;">Unit</th>
                                        <th colspan="3" style="text-align:center;">Forecast</th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">M1</th>
                                        <th style="text-align:center;">M2</th>
                                        <th style="text-align:center;">M3</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $item_rm_id = $record['item_rm_id'];
                $revision = $record['rev'];
                $p_month = $record['p_month'];
                $p_year = $record['p_year'];
                $ending = date("Y-m-t", strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
                $leadtime = date('Y-m-01', strtotime('+'.$record['leadtime'].' days', strtotime($ending)));

                if(empty($record['etd'])){
                    if($record['maker_type'] == "LOCAL"){
                        $etd = date("Y-m-d", strtotime("-14 days", strtotime($record['delivery_date'])));
                    }else{
                        $etd = date("Y-m-d", strtotime("-21 days", strtotime($record['delivery_date'])));
                    }
                }else{
                    $etd = $record['etd'];
                }
                
                $m1 = date('Y-m-01', strtotime($leadtime));
                $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
                $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
                $m11 = "m".date('n', strtotime($leadtime));
                $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
                $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

                $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and item_rm_id = '$item_rm_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms1 = $qmps1->row();

                $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and item_rm_id = '$item_rm_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms2 = $qmps2->row();

                $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and item_rm_id = '$item_rm_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms3 = $qmps3->row();

                // $forecast = $this->forecast($item_rm_id, $record['p_month'], $record['p_year'], @$revision);

                // if(@$rpms1->qty > 0){
                    $prod1 = @$rpms1->qty;
                // }else{
                //     $prod1 = @$forecast->$m11;
                // }

                // if(@$rpms2->qty > 0){
                    $prod2 = @$rpms2->qty;
                // }else{
                //     $prod2 = @$forecast->$m22;
                // }

                // if(@$rpms3->qty > 0){
                    $prod3 = @$rpms3->qty;
                // }else{
                //     $prod3 = @$forecast->$m33;
                // }

                $subtotal += ($record['total']);
                if ($record['currency'] == "USD") {
                    $digits = 2;
                } elseif ($record['currency'] == "JPY") {
                    $digits = 2;
                } else {
                    $digits = 0;
                }

                if(!empty($record['reason'])){
                    $styles = "background:#FFDD9E;";
                }else{
                    $styles = "";
                }
                
                $html .= '  <tr style="'.$styles.'">
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td>' . $record['item_rm_number'] . '</td>
                                <td>' . $record['item_supplier'] . '</td>
                                <td>' . $record['item_rm_name'] . '</td>
                                <td>' . $record['maker_name'] . '</td>
                                <td style="text-align:center;">' . $etd . '</td>
                                <td style="text-align:center;">' . $record['delivery_date'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($prod1) . '</td>
                                <td style="text-align:right;">' . number_format($prod2) . '</td>
                                <td style="text-align:right;">' . number_format($prod3) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="13" rowspan="5">
                                    <b>Note :</b> <br>';
                $html .= '      </td>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }
            
            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                                <div style="width:50%;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th width="200" style="text-align:center;">Vendor Approval</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">(...................................)</th>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:50%; position: absolute; right: 50px;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th colspan="2" width="800" style="text-align:center;">Approved By</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"><img '.$qr_user_3.' width="80"/></th>
                                            <th style="height:80px;"><img '.$qr_user_4.' width="80"/></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">'.@$user_3->name.'<br>'.@$user_3->position.'</th>
                                            <th style="height:20px; text-align:center;">'.@$user_4->name.'<br>'.@$user_4->position.'</th>
                                        </tr>
                                    </table>
                                    <br>
                                    <i style="font-size:8px;">Electronic Auto Generating Approval No Need Signature</i>
                                </div>
                            </div>

                        <br>
                        <b style="font-size:12px;">Term & Condition</b>
                        <br>
                        <table style="width:100%; font-size:12px; margin-top:20px;">
                            <tr>
                                <td width="20">1.</td>
                                <td>Please Acknowledge us in one week of time upon Receipt of PO <br>email to : marui.tatsuhiko@tb-banshu.com, procurement01@banshu-ind.com, procurement02@banshu-ind.com</td>
                            </tr>
                            <tr>
                                <td width="20">2.</td>
                                <td>We will buy the next month confirmed FC in this PO</td>
                            </tr>
                            <tr>
                                <td width="20">3.</td>
                                <td>The PO quantity and forecast quantity is as per our customer demand</td>
                            </tr>
                        </table><br>
                        PLEASE PROCESS OUR PO FORECAST
                    </div>
                </div>';
            }else{
                $html .= "</div></div>";
            }

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;

            // <img '.$qr_user_4.' width="80"/>
        }
        
        if($export == ""){
            $html .= '<script>window.print()</script>';
            die($html);
        }else{
            // Load HTML content
            $this->dompdf_gen->loadHtml($html);

            // (Optional) Set paper size and orientation
            $this->dompdf_gen->setPaper('A4', 'potrait');

            // Render the HTML as PDF
            $this->dompdf_gen->render();

            // Output the generated PDF (1 = download and 0 = preview)
            // $this->dompdf_gen->stream("sample.pdf", array("Attachment" => 0));

            $filePath = FCPATH . 'assets/po/'.base64_decode($po_no).'.pdf'; // FCPATH ensures the path is relative to the project root
            $this->dompdf_gen->save($filePath);
        }
    }

    public function print_delivery($po_no)
    {
        $po_no = base64_decode($po_no);
        $purchase_order = $this->crud->read('purchase_orders', [], ["po_no" => $po_no], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => @$purchase_order->supplier_id]);

        $filter_year = $purchase_order->p_year;
        $filter_month = $purchase_order->p_month;
        $revision = $purchase_order->revision;

        $period_1 = date("F Y", strtotime($filter_year."-".$filter_month."-01"));
        $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));

        $config = $this->db->get('config')->row();

        $html = '<html>
                    <head>
                        <title>Schedule Delivery '.$po_no.'</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 10px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                        <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>
                        <div class="print">';

        $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, c.mpq, c.moq, c.leadtime, c.item_supplier');
        $this->db->from('delivery_schedules a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_rm ia', "TRIM(LEADING '+' FROM b.number) = ia.number");
        $this->db->join('supplier_items c', "ia.id = c.item_rm_id and a.supplier_id = c.supplier_id and a.plant = c.plant", 'left');
        $this->db->where('a.po_no', $po_no);
        $this->db->group_by('a.id');
        $this->db->order_by('b.number');
        $records = $this->db->get()->result_array();

        $html .= '  <table style="width:100%;">
                        <tr>
                            <th width="10">
                                <img src="' . $config->favicon . '" width="40" />
                            </th>
                            <td width="300" style="padding:10px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                            <th width="100" style="text-align:right;"></th>
                        </tr>
                    </table>
                        <div style="padding:10px;">
                            <center>
                                <br>
                                <h3 style="margin:0;"><u>SCHEDULE DELIVERY</u></h3>
                                <small>NO : ' . @$po_no . '</small>
                            </center>
                            <table style="width:50%; font-size:12px; margin-bottom:10px;">
                                <tr>
                                    <td width="20">Period</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . date("F Y", strtotime($purchase_order->p_year."-".$purchase_order->p_month."-01")) . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Revision</td>
                                    <td width="10">:</td>
                                    <td><b>' . $purchase_order->revision . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Vendor</td>
                                    <td width="10">:</td>
                                    <td><b>' . @$supplier->name . '</b></td>
                                </tr>
                            </table>
                            <table id="customers">
                                <tr>
                                    <th rowspan="2" width="10" style="text-align:center;">No</th>
                                    <th rowspan="2" width="100" style="text-align:center;">Part No BEI</th>
                                    <th rowspan="2" width="100" style="text-align:center;">Part No Vendor</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Part Name</th>
                                    <th rowspan="2" width="30" style="text-align:center;">LT</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MPQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MOQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_1.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_2.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_3.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_4.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_5.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_6.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">> 6 Month</th>
                                </tr>
                                <tr>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                </tr>';
        $no = 1;
        foreach ($records as $record) {
            if($record['delivey_date_1'] == "0000-00-00"){ $eta_1 = "-"; }else{ $eta_1 = $record['delivey_date_1']; }
            if($record['delivey_date_2'] == "0000-00-00"){ $eta_2 = "-"; }else{ $eta_2 = $record['delivey_date_2']; }
            if($record['delivey_date_3'] == "0000-00-00"){ $eta_3 = "-"; }else{ $eta_3 = $record['delivey_date_3']; }
            if($record['delivey_date_4'] == "0000-00-00"){ $eta_4 = "-"; }else{ $eta_4 = $record['delivey_date_4']; }
            if($record['delivey_date_5'] == "0000-00-00"){ $eta_5 = "-"; }else{ $eta_5 = $record['delivey_date_5']; }
            if($record['delivey_date_6'] == "0000-00-00"){ $eta_6 = "-"; }else{ $eta_6 = $record['delivey_date_6']; }
            if($record['delivey_date_7'] == "0000-00-00"){ $eta_7 = "-"; }else{ $eta_7 = $record['delivey_date_7']; }
            if($record['etd_1'] == "0000-00-00"){ $etd_1 = "-"; }else{ $etd_1 = $record['etd_1']; }
            if($record['etd_2'] == "0000-00-00"){ $etd_2 = "-"; }else{ $etd_2 = $record['etd_2']; }
            if($record['etd_3'] == "0000-00-00"){ $etd_3 = "-"; }else{ $etd_3 = $record['etd_3']; }
            if($record['etd_4'] == "0000-00-00"){ $etd_4 = "-"; }else{ $etd_4 = $record['etd_4']; }
            if($record['etd_5'] == "0000-00-00"){ $etd_5 = "-"; }else{ $etd_5 = $record['etd_5']; }
            if($record['etd_6'] == "0000-00-00"){ $etd_6 = "-"; }else{ $etd_6 = $record['etd_6']; }
            if($record['etd_7'] == "0000-00-00"){ $etd_7 = "-"; }else{ $etd_7 = $record['etd_7']; }

            $html .= '  <tr>
                            <td  style="text-align:center;">' . $no . '</td>
                            <td>' . $record['item_rm_number'] . '</td>
                            <td>' . $record['item_supplier'] . '</td>
                            <td>' . $record['item_rm_name'] . '</td>
                            <td style="text-align:right;">' . number_format($record['leadtime']) . '</td>
                            <td style="text-align:right;">' . number_format($record['mpq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['moq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_1']) . '</td>
                            <td style="text-align:center;">'.$etd_1.'</td>
                            <td style="text-align:center;">'.$eta_1.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_2']) . '</td>
                            <td style="text-align:center;">'.$etd_2.'</td>
                            <td style="text-align:center;">'.$eta_2.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_3']) . '</td>
                            <td style="text-align:center;">'.$etd_3.'</td>
                            <td style="text-align:center;">'.$eta_3.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_4']) . '</td>
                            <td style="text-align:center;">'.$etd_4.'</td>
                            <td style="text-align:center;">'.$eta_4.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_5']) . '</td>
                            <td style="text-align:center;">'.$etd_5.'</td>
                            <td style="text-align:center;">'.$eta_5.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_6']) . '</td>
                            <td style="text-align:center;">'.$etd_6.'</td>
                            <td style="text-align:center;">'.$eta_6.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_7']) . '</td>
                            <td style="text-align:center;">'.$etd_7.'</td>
                            <td style="text-align:center;">'.$eta_7.'</td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = trim(base64_decode($this->input->get('filter_item_family')));
        $filter_item_rm = trim(base64_decode($this->input->get('filter_item_rm')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));
        $filter_type = trim(base64_decode($this->input->get('filter_type')));
        $filter_status = trim(base64_decode($this->input->get('filter_status')));

        //Select Query
        $this->db->select('a.*, b.name as item_rm_name, b.number as item_rm_number, c.number as supplier_no, 
            c.name as supplier_name,
            c.supplier_po,
            c.type,
            c.state,
            d.name as item_family_name,
            e.item_supplier,
            f.type as maker_type,
            f.name as maker_name');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id and a.plant = e.plant');
        $this->db->join('makers f', 'e.maker_id = f.id', 'left');
        $this->db->group_by('a.id');
        $this->db->where('a.type', "PO");
        $this->db->like('a.p_month', $filter_month);
        $this->db->like('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        if($filter_item_family != ""){
            $this->db->where('d.id', $filter_item_family);
        }
        if($filter_item_rm != ""){
            $this->db->where('a.item_rm_id', $filter_item_rm);
        }
        if($filter_supplier != ""){
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if($filter_po != ""){
            $this->db->where('a.po_no', $filter_po);
        }
        if($filter_type != ""){
            $this->db->where('c.type', $filter_type);
        }
        if($filter_status != ""){
            $this->db->where('a.status', $filter_status);
        }
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('d.name', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->logo . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>PURCHASE ORDER</small>
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
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Approved</th>
                <th style="text-align:center;">Approved By</th>
                <th style="text-align:center;">Approved Date</th>
                <th style="text-align:center;">PR No</th>
                <th style="text-align:center;">PO No</th>
                <th style="text-align:center;">PO Date</th>
                <th style="text-align:center;">Part No Original</th>
                <th style="text-align:center;">Part No BEI</th>
                <th style="text-align:center;">Part No Vendor</th>
                <th style="text-align:center;">Part Name</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Vendor No</th>
                <th style="text-align:center;">Vendor Name</th>
                <th style="text-align:center;">Vendor Type</th>
                <th style="text-align:center;">Maker Name</th>
                <th style="text-align:center;">Maker Type</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">ETD</th>
                <th style="text-align:center;">ETA</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Leadtime</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Created By</th>
                <th style="text-align:center;">Created Date</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == "0"){
                $status = "OPEN";
            }else{
                $status = "CLOSE";
            }
            
            if($data['complete'] == 1){
                $status = "COMPLETE";
            }

            if($data['approved_to'] == ""){
                $approved = "APPROVED";
            }else{
                $approved = "CHECKING";
            }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $approved . '</td>
                            <td>' . $data['approved_by'] . '</td>
                            <td>' . $data['approved_date'] . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['po_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['item_rm_name'] . '</td>
                            <td>' . $data['item_family_name'] . '</td>
                            <td>' . $data['supplier_id'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:center;">' . $etd . '</td>
                            <td style="text-align:center;">' . $data['delivery_date'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $status . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}