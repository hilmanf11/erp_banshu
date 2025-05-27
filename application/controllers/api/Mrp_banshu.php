<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Mrp_banshu extends CI_Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('mrp');
    }

    public function index()
    {
        redirect('error_access');
    }

    function read($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [])
    {
        $this->db->select('*');
        $this->db->from($table);
        if ($where != []) {
            $this->db->where($where);
        }
        if ($like != []) {
            $this->db->like($like);
        }
        if ($limit != "") {
            $this->db->limit($limit);
        }
        if ($orderby != "") {
            $this->db->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->db->group_by($groupby);
        }
        $records = $this->db->get()->row();
        return $records;
    }

    public function getDataFamilys()
    {

        $send = $this->mrp->prodfam();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $arr[] = array(
                    "pfm_id" => $send[$i]->pfm_id,
                    "pfm_name" => $send[$i]->pfm_name,
                );
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataItems()
    {

        $send = $this->mrp->master_item();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                if (trim($send[$i]->pfm_id) != '06' && trim($send[$i]->stscode_id) == '01') { // type raw material dan yang aktif

                    $item_rm = $this->read('item_rm', [], ["number" => trim($send[$i]->item_id)]);
                    $item_familys = $this->read('item_familys', [], ["number" => trim($send[$i]->pfm_id)]);
                    $uom = $this->read('uom', [], ["number" => trim($send[$i]->um_id)]);
                    if (
                        @$item_rm->item_category_id == @$item_familys->item_category_id &&
                        @$item_rm->item_family_id == @$item_familys->id &&
                        @$item_rm->number == trim($send[$i]->item_id) &&
                        @$item_rm->name == trim($send[$i]->item_name) &&
                        @$item_rm->uom == @$uom->name &&
                        @$item_rm->maker_id == trim($send[$i]->maker_id)
                    ) {
                    } else {
                        $arr[] = array(
                            "item_family_id" => trim($send[$i]->pfm_id),
                            "number" => trim($send[$i]->item_id),
                            "name" => trim($send[$i]->item_name),
                            "um_id" => trim($send[$i]->um_id),
                            "maker_id" => trim($send[$i]->maker_id),
                        );
                    }
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }


    public function getDataCustomers()
    {

        $send = $this->mrp->master_customer();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $customers = $this->read('customers', [], ["number" => trim($send[$i]->cust_id)]);
                if (@$customers->number == trim($send[$i]->cust_id) && @$customers->name == trim($send[$i]->cust_name)) {
                    continue;
                } else {
                    $arr[] = array(
                        "number" => $send[$i]->cust_id,
                        "name" => $send[$i]->cust_name,
                    );
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataItemFgs()
    {

        $send = $this->mrp->master_item_fg();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                if (trim($send[$i]->pfm_id) == '06' && trim($send[$i]->stscode_id) == '01') { // type finishgoods dan yang aktif
                    $item_fg = $this->read('item_fg', [], ["number_customer" => trim($send[$i]->item_id)]);
                    $customers = $this->read('customers', [], ["number" => trim($send[$i]->cust_id)]);
                    $uom = $this->read('uom', [], ["number" => trim($send[$i]->um_id)]);
                    if (
                        @$item_fg->number_customer == trim($send[$i]->item_id) &&
                        @$item_fg->name == trim($send[$i]->item_name) &&
                        @$item_fg->uom == @$uom->name &&
                        @$item_fg->cct == trim($send[$i]->item_cct) &&
                        @$item_fg->mpq == trim($send[$i]->item_muloq) &&
                        @$item_fg->moq == trim($send[$i]->item_minoq) &&
                        @$item_fg->leadtime == trim($send[$i]->item_prcldtm) &&
                        @$item_fg->customer_id == @$customers->id &&
                        @$item_fg->weight == trim($send[$i]->weight) &&
                        @$item_fg->lot == trim($send[$i]->lotqty)
                    ) {
                    } else {
                        $arr[] = array(
                            "number_customer" => trim($send[$i]->item_id),
                            "cct" => trim($send[$i]->item_cct),
                            "revision" => 00,
                            "mpq" => trim($send[$i]->item_muloq),
                            "moq" => trim($send[$i]->item_minoq),
                            "leadtime" => trim($send[$i]->item_prcldtm),
                            "customer_id" => trim($send[$i]->cust_id),
                            "weight" => trim($send[$i]->weight),
                            "lot" => trim($send[$i]->lotqty),
                            "name" => trim($send[$i]->item_name),
                            "um_id" => trim($send[$i]->um_id),
                        );
                    }
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataBoms()
    {
        $item_fg_id = $this->input->post('sync_item_fg_id');
        $customer = $this->input->post('sync_customer_id');
        $this->db->select('number_customer');
        $this->db->from('item_fg');
        $this->db->where('deleted', 0);
        if (!empty($item_fg_id)) {
            $this->db->where('id', $item_fg_id);
        }
        if (!empty($customer)) {
            $this->db->where('customer_id', $customer);
        }
        $this->db->order_by('id', 'ASC');
        $assy = $this->db->get()->result_array();

        $send = $this->mrp->master_bom($assy);
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $bom = $this->read('bom', [], ["item_fg_id" => trim($send[$i]['bom_par_item']), "item_rm_id" => trim($send[$i]['bom_com_item'])]);
                $item_rm = $this->read('item_rm', [], ["number" => trim($send[$i]['bom_com_item'])]);
                $item_fg = $this->read('item_fg', [], ["number_customer" => trim($send[$i]['bom_par_item'])]);
                if (
                    @$bom->item_fg_id == @$item_fg->id &&
                    @$bom->item_rm_id == @$item_rm->id &&
                    @$bom->composition == trim($send[$i]['bom_qty_perassy']) &&
                    @$bom->revision == trim($send[$i]['bom_revision'])
                ) {
                } else {
                    $arr[] = array(
                        "bom_par_item" => trim($send[$i]['bom_par_item']),
                        "bom_com_item" => trim($send[$i]['bom_com_item']),
                        "bom_qty_perassy" => trim($send[$i]['bom_qty_perassy']),
                        "bom_revision" => trim($send[$i]['bom_revision']),

                    );
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }
}
